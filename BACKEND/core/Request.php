<?php

namespace Core;

class Request
{
    /**
     * Instancia única de la clase (Singleton)
     */
    private static ?Request $instance = null;

    public string $method;
    public string $uri;
    public array $query;
    public array $body;
    public array $headers;
    public array $files;

    /**
     * Almacén de datos inyectados (por Middlewares, etc.)
     */
    protected array $attributes = [];

    /**
     * El constructor es privado para evitar múltiples instancias con 'new'
     * desde fuera, pero lo mantendremos público si prefieres flexibilidad, 
     * aunque getInstance() es la forma correcta de acceder.
     */
    public function __construct()
    {
        $this->method  = $_SERVER['REQUEST_METHOD'] ?? 'GET';
        $this->uri     = $_SERVER['REQUEST_URI'] ?? '/';
        $this->query   = $_GET ?? [];
        $this->files   = $_FILES ?? [];
        $this->headers = getallheaders();

        // Detectar y decodificar el cuerpo (JSON o Formularios)
        $input = file_get_contents('php://input');
        $this->body = json_decode($input, true) ?? $_POST ?? [];

        // Guardamos la primera instancia creada
        if (self::$instance === null) {
            self::$instance = $this;
        }
    }

    /**
     * Obtiene un archivo subido envuelto en la clase Upload
     */
    public function file(string $key): ?Upload
    {
        if (!isset($this->files[$key]) || empty($this->files[$key]['name'])) {
            return null;
        }

        return new Upload($this->files[$key]);
    }

    /**
     * Retorna todos los archivos
     */
    public function allFiles(): array
    {
        return $this->files;
    }

    /**
     * Obtiene la instancia compartida de la petición
     */
    public static function getInstance(): Request
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Obtiene un valor del body o query (Post o Get)
     */
    public function input(string $key, mixed $default = null): mixed
    {
        return $this->body[$key] ?? $this->query[$key] ?? $default;
    }

    /**
     * Inyecta un dato personalizado en la petición (Útil para Middlewares)
     */
    public function setAttribute(string $key, mixed $value): void
    {
        $this->attributes[$key] = $value;
    }

    /**
     * Recupera un dato inyectado previamente
     */
    public function getAttribute(string $key, mixed $default = null): mixed
    {
        return $this->attributes[$key] ?? $default;
    }

    /**
     * Retorna todos los datos combinados
     */
    public function all(): array
    {
        return array_merge($this->query, $this->body, $this->attributes);
    }

    /**
     * Obtiene una cabecera específica
     */
    public function header(string $key): ?string
    {
        // Soporte para buscar cabeceras sin importar mayúsculas/minúsculas
        $key = strtolower($key);
        foreach ($this->headers as $name => $value) {
            if (strtolower($name) === $key) {
                return $value;
            }
        }
        return null;
    }

    /**
     * Verifica si la petición espera una respuesta JSON
     */
    public function wantsJson(): bool
    {
        $accept = $this->header('Accept');
        return ($accept && (str_contains($accept, 'application/json') || $accept === '*/*'));
    }
}
