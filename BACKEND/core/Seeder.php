<?php

namespace Core;

use Core\Database;
use Throwable;
use PDO;

class Seeder
{
    public static function make(string $name): void
    {
        $className = str_replace(' ', '', ucwords(str_replace(['-', '_'], ' ', $name)));
        $filePath  = BASE_PATH . "/database/seeds/{$className}.php";

        if (file_exists($filePath)) {
            echo "⚠️ El archivo {$className}.php ya existe.\n";
            return;
        }

        // Template mejorado: Sugerimos usar los Modelos en lugar de SQL manual
        $template = <<<PHP
            <?php

            class {$className}
            {
                public static function run(PDO \$pdo): void
                {
                    // Ejemplo con SQL: \$pdo->exec("INSERT INTO ...");
                    // Ejemplo con Modelo: \App\Models\User::create([...]);
                }
            }
            PHP;

        file_put_contents($filePath, $template);
        echo "✅ Seeder creado: {$filePath}\n";
    }

    public static function runAll(): void
    {
        $pdo = Database::pdo();
        $files = glob(BASE_PATH . '/database/seeds/*.php');

        if (!$files) {
            echo "ℹ️ No se encontraron seeders.\n";
            return;
        }

        foreach ($files as $file) {
            $className = pathinfo($file, PATHINFO_FILENAME);
            self::run($className); // Llamamos a run() para que use la tabla de control
        }
    }

    public static function run(string $seederName): void
    {
        $pdo = Database::pdo();
        $driver = $pdo->getAttribute(PDO::ATTR_DRIVER_NAME);

        // 1. Crear la tabla de control adaptada al motor
        if ($driver === 'sqlite') {
            $pdo->exec("
                CREATE TABLE IF NOT EXISTS seeders (
                    id INTEGER PRIMARY KEY AUTOINCREMENT,
                    seeder VARCHAR(255) NOT NULL,
                    run_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                    UNIQUE(seeder)
                )
            ");
        } else {
            $pdo->exec("
                CREATE TABLE IF NOT EXISTS seeders (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    seeder VARCHAR(255) UNIQUE,
                    run_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
            ");
        }

        // ¿Ya fue ejecutado?
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM seeders WHERE seeder = :s");
        $stmt->execute(['s' => $seederName]);

        if ($stmt->fetchColumn() > 0) {
            echo "⚠️  El seeder '{$seederName}' ya fue ejecutado.\n";
            return;
        }

        $file = BASE_PATH . "/database/seeds/{$seederName}.php";

        if (!file_exists($file)) {
            echo "❌ Archivo {$seederName}.php no encontrado.\n";
            return;
        }

        require_once $file;

        if (!class_exists($seederName)) {
            echo "❌ Clase {$seederName} no encontrada en el archivo.\n";
            return;
        }

        try {
            // Desactivar llaves foráneas temporalmente para evitar errores al limpiar tablas
            if ($driver === 'sqlite') $pdo->exec("PRAGMA foreign_keys = OFF;");
            else $pdo->exec("SET FOREIGN_KEY_CHECKS = 0;");

            $seederName::run($pdo);

            // Reactivar llaves foráneas
            if ($driver === 'sqlite') $pdo->exec("PRAGMA foreign_keys = ON;");
            else $pdo->exec("SET FOREIGN_KEY_CHECKS = 1;");

            $insert = $pdo->prepare("INSERT INTO seeders (seeder) VALUES (:s)");
            $insert->execute(['s' => $seederName]);

            echo "✅ Seeder ejecutado: {$seederName}\n";
        } catch (Throwable $e) {
            echo "❌ Error ejecutando seeder {$seederName}: " . $e->getMessage() . "\n";
        }
    }
}