# 🚀 Documentación del Framework Core (CLI, ORM, Routes & Middlewares)

Este documento describe el ecosistema de desarrollo por consola (CLI), la interacción con la base de datos mediante el ORM (Active Record + QueryBuilder) y el sistema de enrutamiento jerárquico automatizado con soporte para **Middlewares** y **Request Singleton**.

---

## 📚 Tabla de Contenido
1. [Interfaz de Línea de Comandos (CLI)](#-1-interfaz-de-línea-de-comandos-cli)
   - [Gestión de Base de Datos (Migrations & Seeders)](#-gestión-de-base-de-datos-migrations--seeders)
   - [Generadores de Código y Seguridad](#-generadores-de-código-y-seguridad)
2. [Sistema de Rutas y Middlewares](#-2-sistema-de-rutas-y-middlewares)
   - [Jerarquía y Auto-vínculo del CLI](#-jerarquía-y-auto-vínculo-del-cli)
   - [Registro de Middlewares](#-registro-de-middlewares)
   - [Middlewares con Parámetros](#-middlewares-con-parámetros)
3. [Gestión de Peticiones (Request Singleton)](#-3-gestión-de-peticiones-request-singleton)
   - [Estructura y Ciclo de Vida](#-estructura-y-ciclo-de-vida)
   - [Lectura de Datos (GET/POST/JSON)](#-lectura-de-datos-getpostjson)
   - [Headers y Negociación de Contenido](#-headers-y-negociación-de-contenido)
   - [Attributes Inyectados por Middlewares](#-attributes-inyectados-por-middlewares)
   - [Subida de Archivos (Upload)](#-subida-de-archivos-upload)
4. [Arquitectura del ORM (Active Record)](#-4-arquitectura-del-orm-active-record)
   - [Definición de Modelos](#-definición-de-modelos)
   - [Operaciones CRUD](#-operaciones-crud)
   - [Consulta Avanzada (QueryBuilder)](#-consulta-avanzada-querybuilder)
5. [Estructura del Proyecto](#-5-estructura-del-proyecto)

---

## 💻 1. Interfaz de Línea de Comandos (CLI)

El archivo `scripts/cli.php` es el motor de automatización del proyecto.

**Uso:**
```bash
php scripts/cli.php <comando> [parámetros]
```

### 🏗️ Gestión de Base de Datos (Migrations & Seeders)

El comando `db:fresh` actúa de forma inteligente según el motor configurado en el archivo `.env`:

- **En SQLite:** cierra la conexión, reinicia el archivo `.sqlite` a 0 bytes y regenera la estructura.
- **En MySQL:** ejecuta `DROP DATABASE` y `CREATE DATABASE` para un reset absoluto de la estructura.

#### Comandos disponibles

| Comando | Parámetro | Descripción |
| :--- | :--- | :--- |
| `make:migration` | `nombre` | Genera una migración en `database/migrations/`. |
| `migrate` | *(ninguno)* | Ejecuta todas las migraciones pendientes. |
| `rollback` | `archivo?` | Revierte la última migración o una específica. |
| `db:fresh` | *(ninguno)* | **Reset total**: recrea la DB y corre migraciones. |
| `db:fresh --seed` | `--seed` | Reinicia la DB y ejecuta **todos** los seeders (`runAll`). |
| `make:seed` | `Nombre` | Crea un archivo de semilla en `database/seeds/`. |
| `seed` | `Nombre` | Ejecuta un seeder específico. |

---

### 🛠️ Generadores de Código y Seguridad

| Comando | Parámetros | Descripción |
| :--- | :--- | :--- |
| `make:model` | `Nombre` | Genera un modelo en `app/models/`. |
| `make:controller` | `Ruta/Nombre` | Crea un controlador (soporta subcarpetas). |
| `make:middleware` | `Nombre` | Crea un middleware en `app/middlewares/`. |
| `make:route` | `uri ctrl@metodo [verb] [path]` | Genera rutas con **auto-vínculo** jerárquico. |
| `gen:rsa` | *(ninguno)* | Genera llaves RSA (pública/privada) para cifrado. |

---

## 🛣️ 2. Sistema de Rutas y Middlewares

El framework organiza las rutas en la carpeta `app/routes/`. El despacho utiliza un **Singleton** para el objeto `Request`, permitiendo que los datos fluyan y se transformen a través de las capas de seguridad.

### 🔗 Jerarquía y Auto-vínculo del CLI

El CLI gestiona automáticamente la estructura de archivos y el vínculo entre rutas padre/hijo:

1. **Grupo maestro**
   ```bash
   php scripts/cli.php make:route / Health@index get admin
   ```
   - Crea `app/routes/admin.php` con el bloque:
     ```php
     $router->group(['prefix' => '/admin' /* ... */], function($router) {
         // sub-archivos inyectados aquí
     });
     ```

2. **Sub-módulo (carpeta)**
   ```bash
   php scripts/cli.php make:route /list User@index get admin/users
   ```
   - Crea el archivo `app/routes/admin/users.php`.
   - **Auto-vínculo:** el CLI inyecta automáticamente en el archivo padre la línea:
     ```php
     require_once __DIR__ . '/admin/users.php';
     ```

---

### 🛡️ Registro de Middlewares

Puedes aplicar filtros de seguridad en tres niveles de jerarquía:

#### A) A nivel de grupo (protección de módulos)

```php
$router->group([
    'prefix' => '/admin',
    'middleware' => [\App\Middlewares\AuthMiddleware::class]
], function($router) {
    // El CLI inyecta sub-archivos aquí que heredan la protección
    require_once __DIR__ . '/admin/users.php';
});
```

#### B) A nivel de ruta individual (GET, POST, etc.)

```php
$router->post('/settings/update', [
    'action' => [\App\Controllers\SettingsController::class, 'update'],
    'middleware' => [
        \App\Middlewares\AuthMiddleware::class,
        \App\Middlewares\LogActivityMiddleware::class
    ]
]);
```

#### C) Middlewares con parámetros y métodos

Soporta el formato:

- `Clase:param1,param2`
- `Clase:param1,param2@metodo`

Ejemplos:

```php
$router->get('/debug', [
    'action' => 'HomeController@index',
    'middleware' => ['App\Middlewares\DumpMiddleware:prueba']
]);

$router->get('/delete-post', [
    'action' => 'PostController@destroy',
    'middleware' => ['App\Middlewares\RoleMiddleware:admin,editor@checkRole']
]);
```

---

### 🧩 Middlewares con Parámetros

Los parámetros se declaran concatenados a la clase usando `:` y separados por coma `,`.

- Un solo parámetro:
  ```php
  'App\Middlewares\DumpMiddleware:prueba'
  ```

- Varios parámetros:
  ```php
  'App\Middlewares\RoleMiddleware:admin,editor'
  ```

- Varios parámetros + método:
  ```php
  'App\Middlewares\RoleMiddleware:admin,editor@checkRole'
  ```

> Nota: Los parámetros se interpretan como strings. Si requieres tipos (bool/int), el middleware debe parsearlos/validarlos.

---

## 📥 3. Gestión de Peticiones (Request Singleton)

La clase `Core\Request` centraliza los datos de la petición actual y provee una única instancia compartida (**Singleton**). Esto permite que:
- El **Router** y los **Controladores** consuman datos coherentes.
- Los **Middlewares** inyecten información (ej. usuario autenticado) y el controlador la reutilice.

### 🧱 Estructura y Ciclo de Vida

La petición se inicializa leyendo valores del entorno PHP:

- `method` → `$_SERVER['REQUEST_METHOD']` (GET, POST, PUT, DELETE, etc.)
- `uri` → `$_SERVER['REQUEST_URI']`
- `query` → `$_GET`
- `files` → `$_FILES`
- `headers` → `getallheaders()`
- `body` → intenta decodificar JSON desde `php://input`; si no, usa `$_POST`

**Implementación base:**

```php
<?php

namespace Core;

class Request
{
    private static ?Request $instance = null;

    public string $method;
    public string $uri;
    public array $query;
    public array $body;
    public array $headers;
    public array $files;

    protected array $attributes = [];

    public function __construct()
    {
        $this->method  = $_SERVER['REQUEST_METHOD'] ?? 'GET';
        $this->uri     = $_SERVER['REQUEST_URI'] ?? '/';
        $this->query   = $_GET ?? [];
        $this->files   = $_FILES ?? [];
        $this->headers = getallheaders();

        $input = file_get_contents('php://input');
        $this->body = json_decode($input, true) ?? $_POST ?? [];

        if (self::$instance === null) {
            self::$instance = $this;
        }
    }

    public static function getInstance(): Request
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function input(string $key, mixed $default = null): mixed
    {
        return $this->body[$key] ?? $this->query[$key] ?? $default;
    }

    public function setAttribute(string $key, mixed $value): void
    {
        $this->attributes[$key] = $value;
    }

    public function getAttribute(string $key, mixed $default = null): mixed
    {
        return $this->attributes[$key] ?? $default;
    }

    public function all(): array
    {
        return array_merge($this->query, $this->body, $this->attributes);
    }

    public function header(string $key): ?string
    {
        $key = strtolower($key);
        foreach ($this->headers as $name => $value) {
            if (strtolower($name) === $key) {
                return $value;
            }
        }
        return null;
    }

    public function wantsJson(): bool
    {
        $accept = $this->header('Accept');
        return ($accept && (str_contains($accept, 'application/json') || $accept === '*/*'));
    }

    public function allFiles(): array
    {
        return $this->files;
    }

    public function file(string $key): ?Upload
    {
        if (!isset($this->files[$key]) || empty($this->files[$key]['name'])) {
            return null;
        }
        return new Upload($this->files[$key]);
    }
}
```

---

### 🧾 Lectura de Datos (GET/POST/JSON)

#### `input()` (unifica GET + POST + JSON)

`input()` consulta primero el `body` (POST o JSON) y luego el `query` (GET). Si no existe, retorna el `default`.

```php
$email = $this->request->input('email');
$page  = $this->request->input('page', 1);
```

#### Ejemplo: Endpoint que recibe JSON

**Request JSON:**
```json
{
  "email": "user@demo.com",
  "password": "123456"
}
```

**Controller:**
```php
public function login()
{
    $email = $this->request->input('email');
    $pass  = $this->request->input('password');

    // ...
}
```

#### Ejemplo: Endpoint que recibe QueryString

**URL:**
```
GET /reports?from=2026-01-01&to=2026-01-31
```

**Controller:**
```php
public function report()
{
    $from = $this->request->input('from');
    $to   = $this->request->input('to');
}
```

#### `all()` (datos combinados)

`all()` retorna un array combinado de:

- `query` + `body` + `attributes`

```php
$payload = $this->request->all();
```

---

### 🧠 Headers y Negociación de Contenido

#### `header()`

Permite leer cabeceras sin importar mayúsculas/minúsculas.

```php
$auth = $this->request->header('Authorization');
$accept = $this->request->header('Accept');
```

#### `wantsJson()`

Retorna `true` si el cliente indica que espera JSON (por ejemplo: `Accept: application/json` o `*/*`).

```php
if ($this->request->wantsJson()) {
    // Responder como JSON
} else {
    // Responder HTML / texto / redirect, etc.
}
```

---

### 🧷 Attributes Inyectados por Middlewares

Los middlewares pueden validar, consultar DB, transformar datos e **inyectar resultados** dentro de la Request con `setAttribute()`, para que el controlador los consuma después.

#### Ejemplo: Middleware inyecta usuario autenticado

```php
public function handle(): void
{
    $request = \Core\Request::getInstance();

    $token = $request->header('Authorization');
    // Validar token...
    $user = \App\Models\User::find(1);

    $request->setAttribute('auth_user', $user);
}
```

#### Ejemplo: Controlador consume el attribute

```php
public function profile()
{
    $user = $this->request->getAttribute('auth_user');

    if (!$user) {
        // manejar no autenticado...
    }

    // ...
}
```

#### `getAttribute()` con default

```php
$role = $this->request->getAttribute('role', 'guest');
```

---

### 📎 Subida de Archivos (Upload)

El framework envuelve los archivos de `$_FILES` en un objeto `Core\Upload`, para facilitar:

- Validar extensiones permitidas
- Validar tamaño máximo
- Guardar el archivo en disco (y auto-crear carpetas)

#### Clase `Core\Upload`

```php
<?php

namespace Core;

class Upload
{
    private array $file;
    private array $allowedExtensions = [];
    private int $maxSize = 2097152; // 2MB por defecto

    public function __construct(array $file)
    {
        $this->file = $file;
    }

    public function allowed(array $extensions): self
    {
        $this->allowedExtensions = array_map('strtolower', $extensions);
        return $this;
    }

    public function maxSize(int $bytes): self
    {
        $this->maxSize = $bytes;
        return $this;
    }

    public function store(string $path, ?string $customName = null): string|bool
    {
        if ($this->file['error'] !== UPLOAD_ERR_OK) return false;

        if ($this->file['size'] > $this->maxSize) return false;

        $ext = strtolower(pathinfo($this->file['name'], PATHINFO_EXTENSION));
        if (!empty($this->allowedExtensions) && !in_array($ext, $this->allowedExtensions)) {
            return false;
        }

        $name = $customName ?? bin2hex(random_bytes(8)) . "." . $ext;
        $fullPath = rtrim($path, '/') . '/' . $name;

        if (!is_dir($path)) mkdir($path, 0755, true);

        if (move_uploaded_file($this->file['tmp_name'], $fullPath)) {
            return $name;
        }

        return false;
    }
}
```

---

#### Obtener un archivo desde `Request`

`Request::file($key)` retorna un `Upload` o `null`.

```php
$upload = $this->request->file('document');
if (!$upload) {
    // No llegó archivo
}
```

#### Validar y guardar (extensiones + tamaño)

```php
public function uploadDocument()
{
    $upload = $this->request->file('document');

    if (!$upload) {
        // return response error: "Archivo requerido"
    }

    $fileName = $upload
        ->allowed(['pdf', 'png', 'jpg', 'jpeg'])
        ->maxSize(5 * 1024 * 1024) // 5MB
        ->store(__DIR__ . '/../../storage/uploads');

    if ($fileName === false) {
        // return response error: "Archivo inválido (extensión/tamaño/error)"
    }

    // return response ok con $fileName
}
```

#### Guardar con nombre personalizado

```php
$fileName = $upload
    ->allowed(['pdf'])
    ->maxSize(10 * 1024 * 1024)
    ->store(__DIR__ . '/../../storage/uploads', 'contrato_2026.pdf');
```

> Recomendación: si el nombre viene del usuario, sanitízalo o genera uno interno para evitar caracteres especiales/colisiones.

#### Obtener todos los archivos

```php
$files = $this->request->allFiles();
```

---

## 🏗️ 4. Arquitectura del ORM (Active Record)

Interactúa con la base de datos de forma fluida mediante el patrón **Active Record** y el **QueryBuilder**.

### 🧱 Definición de Modelos

```php
namespace App\Models;

use App\Models\BaseModel;

class User extends BaseModel {
    protected static string $table = 'users';
}
```

### 📄 Operaciones CRUD

- `User::all()` → lista todos los registros.
- `User::find($id)` → busca por ID primario.
- `User::create([...])` → inserta y retorna el objeto creado.
- `User::update($id, [...])` → actualiza campos específicos.
- `User::delete($id)` → eliminación física.

### 🔍 Consulta Avanzada (QueryBuilder)

```php
$activeAdmins = User::query()
    ->select('u.name', 'r.name as role')
    ->join('roles r', 'u.role_id = r.id')
    ->where('u.status', '=', 'active')
    ->orderBy('u.created_at', 'DESC')
    ->limit(10)
    ->get();
```

---

## 📂 5. Estructura del Proyecto

- `app/controllers/` → lógica de negocio (controladores).
- `app/models/` → clases vinculadas a tablas (modelos).
- `app/middlewares/` → capas de seguridad y transformación del `Request`.
- `app/routes/` → archivos de rutas jerarquizados automáticamente.
- `core/` → motores internos (Request Singleton, Router, ORM, Generators).
- `database/` → almacén de archivos de migraciones y seeders.

---

✨ **Framework diseñado para escalabilidad, automatización y código limpio.**
