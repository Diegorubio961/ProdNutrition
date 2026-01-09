<?php

namespace Core;

use Core\Database;
use PDO;
use PDOException;

class Migration
{
    /* ----------------------------------------------------------------- */
    /* 1) Crear un archivo de migración                                  */
    /* ----------------------------------------------------------------- */
    public static function make(string $name): void
    {
        // Generar nombre de clase
        $className = str_replace(' ', '', ucwords(str_replace(['-', '_'], ' ', $name)));

        // Buscar colisión de clase
        $existing = glob(BASE_PATH . "/database/migrations/*_{$name}.php");
        foreach ($existing as $file) {
            $contents = file_get_contents($file);
            if (str_contains($contents, "class {$className}")) {
                echo "❌ Ya existe una migración con la clase {$className} en:\n→ {$file}\n";
                return;
            }
        }

        $timestamp = date('YmdHis');
        $filePath  = BASE_PATH . "/database/migrations/{$timestamp}_{$name}.php";

        $template = <<<PHP
            <?php

            class {$className}
            {
                public function up(PDO \$pdo): void
                {
                    // \$pdo->exec("CREATE TABLE ...");
                }

                public function down(PDO \$pdo): void
                {
                    // \$pdo->exec("DROP TABLE ...");
                }
            }
            PHP;

        file_put_contents($filePath, $template);
        echo "✅ Creado: {$filePath}\n";
    }


    /* ----------------------------------------------------------------- */
    /* 2) Ejecutar todas las migraciones pendientes                      */
    /* ----------------------------------------------------------------- */
    public static function run(string $method = 'up'): void
    {
        $pdo = Database::pdo();

        // 1) Tabla de control (migration + method)
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS migrations (
                id         INT AUTO_INCREMENT PRIMARY KEY,
                migration  VARCHAR(255) NOT NULL,
                method     VARCHAR(64)  NOT NULL,
                run_at     TIMESTAMP    DEFAULT CURRENT_TIMESTAMP,
                UNIQUE KEY migration_method_unique (migration, method)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
        ");

        // 2) ¿Qué migraciones-métodos ya corrieron?
        $ran = $pdo
            ->query('SELECT CONCAT(migration, ":", method) FROM migrations')
            ->fetchAll(PDO::FETCH_COLUMN) ?: [];

        // 3) Recorremos los archivos de migración
        foreach (glob(BASE_PATH . '/database/migrations/*.php') as $file) {
            $filename  = pathinfo($file, PATHINFO_FILENAME);          // 20250713001457_create_users_table
            $classBase = explode('_', $filename, 2)[1] ?? $filename;  // create_users_table
            $className = str_replace(' ', '', ucwords(str_replace(['-', '_'], ' ', $classBase))); // CreateUsersTable

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
                (new $className)->{$method}($pdo);

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

        // Obtener la última migración registrada
        $row = $pdo->query(
            'SELECT migration, method FROM migrations ORDER BY run_at DESC LIMIT 1'
        )->fetch(PDO::FETCH_ASSOC);

        if (!$row) {
            echo "ℹ️ No hay migraciones que revertir.\n";
            return;
        }

        $migrationFile = $row['migration'];      // Ej: 20250713001457_create_users_table
        $executedMethod = $row['method'];        // Ej: up (o seed, etc.)

        $filePath = BASE_PATH . '/database/migrations/' . $migrationFile . '.php';

        if (!file_exists($filePath)) {
            echo "⚠️ Archivo de migración {$migrationFile}.php no encontrado.\n";
            return;
        }

        require_once $filePath;

        // Obtener nombre de clase: remove timestamp + convertir a StudlyCase
        $parts = explode('_', $migrationFile, 2);
        $classBase = isset($parts[1]) ? $parts[1] : $migrationFile;
        $className = str_replace(' ', '', ucwords(str_replace(['-', '_'], ' ', $classBase)));

        if (!class_exists($className)) {
            echo "⚠️ Clase {$className} no encontrada en el archivo {$migrationFile}.php\n";
            return;
        }

        if (!method_exists($className, $method)) {
            echo "⚠️ El método '{$method}' no existe en la clase {$className}\n";
            return;
        }

        try {
            (new $className)->{$method}($pdo);

            $stmt = $pdo->prepare(
                'DELETE FROM migrations WHERE migration = :migration AND method = :method'
            );
            $stmt->execute([
                'migration' => $migrationFile,
                'method'    => $executedMethod,  // se borra solo el método realmente ejecutado
            ]);

            echo "⏪ Migración revertida: {$migrationFile} → {$executedMethod}\n";
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
