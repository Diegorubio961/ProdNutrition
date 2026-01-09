📦 Documentación CLI – Migraciones y Seeders

Este script CLI te permite gestionar tus migraciones y seeders directamente desde consola. Asegúrate de tener el archivo `.env` configurado y haber corrido `composer install` para autoload.

🧪 Requisitos
- PHP instalado en tu máquina
- Archivo `.env` con tu configuración de base de datos
- Carpeta `core/` con clases `Migration`, `Seeder`, `Env`

📚 Comandos Disponibles

📌 1. Crear una migración
php scripts/cli.php make:migration nombre_tabla
> Crea un archivo de migración vacío con el nombre indicado (ej: create_users_table.php) en la carpeta de migraciones.
Ejemplo:
php scripts/cli.php make:migration create_products_table

📌 2. Ejecutar todas las migraciones
php scripts/cli.php migrate
> Ejecuta todas las migraciones pendientes (aún no registradas en la base de datos).

📌 3. Hacer rollback de la última migración
php scripts/cli.php rollback
> Revierte la última migración aplicada (según el registro en la tabla de migraciones).

📌 4. Hacer rollback hasta una migración específica
php scripts/cli.php rollback nombre_archivo_migracion
> Revierte hasta el archivo indicado (inclusive).
Ejemplo:
php scripts/cli.php rollback 20250713003810_create_roles_table.php

📌 5. Crear un Seeder
php scripts/cli.php make:seed NombreSeeder
> Crea un archivo en la carpeta seeds/ con una clase base para insertar datos iniciales en la DB.
Ejemplo:
php scripts/cli.php make:seed UserSeeder

📌 6. Ejecutar un Seeder
php scripts/cli.php seed NombreSeeder
> Ejecuta un Seeder específico y carga datos en la base de datos.
Ejemplo:
php scripts/cli.php seed UserSeeder

✅ Estructura esperada del proyecto

