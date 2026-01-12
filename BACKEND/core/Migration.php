<?php

namespace Core;

use Core\Database;
use PDO;
use PDOException;

class Migration
{
    /* ----------------------------------------------------------------- */
    /* 1) Crear un archivo de migración con estructura Schema            */
    /* ----------------------------------------------------------------- */
    public static function make(string $name): void
    {
        // 1. Generar nombre de clase (StudlyCase)
        // create_users_table -> CreateUsersTable
        $className = str_replace(' ', '', ucwords(str_replace(['-', '_'], ' ', $name)));

        // 2. Buscar colisión de clase
        $existing = glob(BASE_PATH . "/database/migrations/*_{$name}.php");
        foreach ($existing as $file) {
            $contents = file_get_contents($file);
            if (str_contains($contents, "class {$className}")) {
                echo "❌ Ya existe una migración con la clase {$className} en:\n→ {$file}\n";
                return;
            }
        }

        // 3. INTENTAR ADIVINAR EL NOMBRE DE LA TABLA
        // Si el nombre es "create_users_table", extraemos "users"
        $tableName = 'nombre_tabla';
        if (preg_match('/create_(\w+)_table/', $name, $matches)) {
            $tableName = $matches[1];
        } else if (preg_match('/create_(\w+)/', $name, $matches)) {
            $tableName = $matches[1];
        }

        $timestamp = date('YmdHis');
        $filePath  = BASE_PATH . "/database/migrations/{$timestamp}_{$name}.php";

        // 4. PLANTILLA ACTUALIZADA (Usando Schema y Blueprint)
        $template = <<<PHP
            <?php

            use Core\Schema;
            use Core\Blueprint;

            class {$className}
            {
                /**
                 * Ejecutar las migraciones.
                 */
                public function up(PDO \$pdo): void
                {
                    Schema::create('{$tableName}', function (Blueprint \$table) {
                        \$table->id();
                        
                        // Agrega tus columnas aquí...
                        // \$table->string('nombre');
                        // \$table->integer('edad');
                        
                        \$table->timestamps();
                    });
                }

                /**
                 * Revertir las migraciones.
                 */
                public function down(PDO \$pdo): void
                {
                    Schema::dropIfExists('{$tableName}');
                }
            }
            PHP;

        file_put_contents($filePath, $template);
        echo "✅ Migración creada: database/migrations/{$timestamp}_{$name}.php\n";
    }


    /* ----------------------------------------------------------------- */
    /* 2) Ejecutar todas las migraciones pendientes                      */
    /* ----------------------------------------------------------------- */
    public static function run(string $method = 'up'): void
    {
        $pdo = Database::pdo();
        
        // Detectamos qué base de datos estamos usando
        $driver = $pdo->getAttribute(PDO::ATTR_DRIVER_NAME);

        // 1) Crear tabla de control (Sintaxis adaptada al motor)
        if ($driver === 'sqlite') {
            // Versión SQLite:
            // - Usa INTEGER PRIMARY KEY AUTOINCREMENT
            // - Usa UNIQUE(col1, col2) sin la palabra KEY
            // - No tiene ENGINE ni CHARSET
            $pdo->exec("
                CREATE TABLE IF NOT EXISTS migrations (
                    id INTEGER PRIMARY KEY AUTOINCREMENT,
                    migration VARCHAR(255) NOT NULL,
                    method VARCHAR(64) NOT NULL,
                    run_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                    UNIQUE(migration, method)
                )
            ");
        } else {
            // Versión MySQL:
            // - Usa INT AUTO_INCREMENT PRIMARY KEY
            // - Usa UNIQUE KEY ...
            // - Define ENGINE y CHARSET
            $pdo->exec("
                CREATE TABLE IF NOT EXISTS migrations (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    migration VARCHAR(255) NOT NULL,
                    method VARCHAR(64) NOT NULL,
                    run_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    UNIQUE KEY migration_method_unique (migration, method)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
            ");
        }

        // 2) ¿Qué migraciones-métodos ya corrieron?
        // Hacemos el SELECT separado para no depender de la función CONCAT de SQL
        $rows = $pdo->query('SELECT migration, method FROM migrations')
                    ->fetchAll(PDO::FETCH_ASSOC);
        
        // Creamos el array "migration:method" usando PHP
        $ran = array_map(function($row) {
            return $row['migration'] . ':' . $row['method'];
        }, $rows);


        // 3) Recorremos los archivos de migración
        foreach (glob(BASE_PATH . '/database/migrations/*.php') as $file) {
            $filename  = pathinfo($file, PATHINFO_FILENAME);          // Ej: 20250713001457_create_users_table
            $classBase = explode('_', $filename, 2)[1] ?? $filename;  // Ej: create_users_table
            $className = str_replace(' ', '', ucwords(str_replace(['-', '_'], ' ', $classBase))); // Ej: CreateUsersTable

            // Si ya ejecutamos este método para esta migración → continuar
            if (in_array("$filename:$method", $ran, true)) {
                continue;
            }

            require_once $file;

            if (!class_exists($className)) {
                echo "⚠️  Clase {$className} no encontrada en {$file}\n";
                continue;
            }
            if (!method_exists($className, $method)) {
                echo "⚠️  El método {$method} no existe en {$className}\n";
                continue;
            }

            try {
                // Instanciar y ejecutar
                (new $className)->{$method}($pdo);

                // Registrar en la tabla de control
                $stmt = $pdo->prepare("
                    INSERT INTO migrations (migration, method)
                    VALUES (:migration, :method)
                ");
                $stmt->execute([
                    'migration' => $filename,
                    'method'    => $method,
                ]);

                echo "✅  Migración ejecutada: {$filename} → {$method}\n";
            } catch (PDOException $ex) {
                echo "❌  Error en {$filename}: {$ex->getMessage()}\n";
                exit(1);
            }
        }
    }


    public static function rollback(string $method = 'down'): void
    {
        $pdo = Database::pdo();

        // 1) Obtenemos la última migración que fue exitosa (usamos ID DESC)
        // Solo buscamos aquellas que se ejecutaron como 'up'
        $row = $pdo->query(
            "SELECT id, migration, method FROM migrations 
             WHERE method = 'up' 
             ORDER BY id DESC LIMIT 1"
        )->fetch(PDO::FETCH_ASSOC);

        if (!$row) {
            echo "ℹ️  No hay migraciones (método 'up') que revertir.\n";
            return;
        }

        $migrationId = $row['id'];
        $migrationFile = $row['migration'];
        $filePath = BASE_PATH . '/database/migrations/' . $migrationFile . '.php';

        if (!file_exists($filePath)) {
            echo "❌ Archivo físico no encontrado: {$migrationFile}.php\n";
            return;
        }

        require_once $filePath;

        // 2) Extraer nombre de la clase
        $parts = explode('_', $migrationFile, 2);
        $classBase = isset($parts[1]) ? $parts[1] : $migrationFile;
        $className = str_replace(' ', '', ucwords(str_replace(['-', '_'], ' ', $classBase)));

        if (!class_exists($className)) {
            echo "❌ Clase {$className} no definida en el archivo.\n";
            return;
        }

        // Verificamos si existe el método solicitado (por defecto 'down')
        if (!method_exists($className, $method)) {
            echo "❌ El método '{$method}' no existe en la clase {$className}\n";
            return;
        }

        try {
            // 3) Ejecutar el rollback
            echo "⏪ Revirtiendo: {$migrationFile}...\n";
            (new $className)->{$method}($pdo);

            // 4) Eliminar el registro de la tabla de control
            // IMPORTANTE: Borramos por ID para ser precisos
            $stmt = $pdo->prepare("DELETE FROM migrations WHERE id = :id");
            $stmt->execute(['id' => $migrationId]);

            echo "✅  Migración revertida con éxito.\n";

        } catch (PDOException $ex) {
            echo "❌ Error al revertir {$migrationFile}: " . $ex->getMessage() . "\n";
            exit(1);
        }
    }


    public static function rollbackTo(string $targetClass): void
    {
        $pdo = Database::pdo();

        // Cargar todas las migraciones ejecutadas (orden descendente)
        $executed = $pdo->query(
            'SELECT migration, method FROM migrations ORDER BY run_at DESC'
        )->fetchAll(PDO::FETCH_ASSOC);

        if (!$executed) {
            echo "⚠️ No hay migraciones ejecutadas.\n";
            return;
        }

        // Validar que exista la migración objetivo por nombre de archivo
        $migrationFiles = array_column($executed, 'migration');
        if (!in_array($targetClass, $migrationFiles, true)) {
            echo "❌ La migración {$targetClass} no está en la base de datos.\n";
            return;
        }

        // Revertir en orden desde la última hasta la seleccionada (inclusive)
        foreach ($executed as $row) {
            $migrationFile = $row['migration'];
            $method        = $row['method'];
            $filePath      = BASE_PATH . '/database/migrations/' . $migrationFile . '.php';

            if (!file_exists($filePath)) {
                echo "⚠️ Archivo de migración {$migrationFile}.php no encontrado.\n";
                continue;
            }

            require_once $filePath;

            // Convertir nombre de archivo a clase (sin timestamp)
            $parts = explode('_', $migrationFile, 2);
            $classBase = $parts[1] ?? $migrationFile;
            $className = str_replace(' ', '', ucwords(str_replace(['-', '_'], ' ', $classBase)));

            if (!class_exists($className)) {
                echo "⚠️ Clase {$className} no encontrada en {$migrationFile}.php\n";
                continue;
            }

            if (!method_exists($className, 'down')) {
                echo "⚠️ El método down no existe en {$className}\n";
                continue;
            }

            try {
                (new $className)->down($pdo);

                $stmt = $pdo->prepare(
                    'DELETE FROM migrations WHERE migration = :migration AND method = :method'
                );
                $stmt->execute([
                    'migration' => $migrationFile,
                    'method'    => $method,
                ]);

                echo "⏪ Revertida: {$migrationFile} ({$className})\n";
            } catch (PDOException $ex) {
                echo "❌ Error al revertir {$migrationFile}: " . $ex->getMessage() . "\n";
                exit(1);
            }

            if ($migrationFile === $targetClass) {
                echo "🟢 Finalizado rollback hasta {$migrationFile}\n";
                break;
            }
        }
    }
}
