<?php

namespace Core;
use Core\Database;
use Throwable;
use PDOException;

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

        $template = <<<PHP
        <?php

        class {$className}
        {
            public static function run(PDO \$pdo): void
            {
                // \$pdo->exec("INSERT INTO ...");
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
            require_once $file;
            $className = pathinfo($file, PATHINFO_FILENAME);

            if (!class_exists($className)) {
                echo "⚠️ Clase {$className} no encontrada.\n";
                continue;
            }

            try {
                $className::run($pdo);
                echo "✅ Seeder ejecutado: {$className}\n";
            } catch (Throwable $ex) {
                echo "❌ Error en {$className}: " . $ex->getMessage() . "\n";
                exit(1);
            }
        }
    }

    public static function run(string $seederName): void
    {
        $pdo = Database::pdo();

        // Crea la tabla de control si no existe
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS seeders (
                id INT AUTO_INCREMENT PRIMARY KEY,
                seeder VARCHAR(255) UNIQUE,
                run_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
        ");

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
            $seederName::run($pdo);

            $insert = $pdo->prepare("INSERT INTO seeders (seeder) VALUES (:s)");
            $insert->execute(['s' => $seederName]);

            echo "✅ Seeder ejecutado: {$seederName}\n";
        } catch (PDOException $e) {
            echo "❌ Error ejecutando seeder: " . $e->getMessage() . "\n";
        }
    }
}
