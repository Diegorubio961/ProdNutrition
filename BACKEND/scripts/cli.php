#!/usr/bin/env php
<?php

namespace Scripts;

use Core\Migration;
use Core\Seeder;
use Core\Env;
use Core\ControllerGenerator;
use Core\RSAKeyGenerator;
use Core\ModelGenerator;
use PDO;
use PDOException;

define('BASE_PATH', dirname(__DIR__));
require_once BASE_PATH . '/autoload.php';

Env::load();

$command = $argv[1] ?? null;
$param   = $argv[2] ?? null;

switch ($command) {
    case 'make:migration':
        if (!$param) exit("Nombre requerido.\n");
        Migration::make($param);
        break;
    case 'migrate':
        Migration::run();
        break;
    case 'rollback':
        if ($param) {
            Migration::rollbackTo($param);
        } else {
            Migration::rollback(); // Última
        }
        break;
    case 'make:seed':
        $name = $argv[2] ?? null;
        if (!$name) {
            echo "❌ Debes proporcionar un nombre. Ej: php cli.php make:seed UserSeeder\n";
            exit(1);
        }
        Seeder::make($name);
        break;
    case 'seed':
        if (!$param) {
            echo "❌ Debes indicar el nombre del seeder. Ej: php cli.php seed UserSeeder\n";
            exit(1);
        }
        Seeder::run($param);
        break;
    case 'make:controller':
        $name = $argv[2] ?? null;
        if (!$name) {
            echo "❌ Debes proporcionar un nombre. Ej: php cli.php make:controller Auth/LoginController\n";
            exit(1);
        }
        ControllerGenerator::make($name);
        break;

    case 'gen:rsa':
        (new RSAKeyGenerator())->generate();
        break;

    case 'make:model':
        $name = $argv[2] ?? null;
        if (!$name) {
            echo "❌ Faltó el nombre. Uso: php scripts/cli.php make:model Nombre\n";
            exit(1);
        }
        ModelGenerator::make($name);
        break;

    case 'db:fresh':
        // 1. Obtener configuración inicial
        $dbName = \Core\Env::get('DB_NAME');
        $connection = \Core\Env::get('DB_CONNECTION');

        echo "🗑️  Reiniciando base de datos ({$connection})...\n";

        if ($connection === 'sqlite') {
            // Construir ruta como en la configuración
            $filename = basename($dbName);
            if (!str_ends_with($filename, '.sqlite')) {
                $filename .= '.sqlite';
            }
            $dbFile = BASE_PATH . '/database/' . $filename;

            // IMPORTANTE: Cerramos la conexión PDO global para liberar el archivo en Windows
            \Core\Database::close();

            if (file_exists($dbFile)) {
                // Intentamos eliminar el archivo
                if (@unlink($dbFile)) {
                    echo "✅ Archivo SQLite eliminado.\n";
                } else {
                    // Si el archivo está bloqueado (ej: por DB Browser), lo vaciamos a 0 bytes
                    file_put_contents($dbFile, "");
                    echo "⚠️  Archivo bloqueado. Se ha vaciado el contenido para resetearlo.\n";
                }
            }

            // Creamos el archivo nuevo vacío
            touch($dbFile);
        } else {
            // Lógica para MySQL
            try {
                // Necesitamos una conexión temporal sin base de datos seleccionada para poder hacer DROP
                $pdo = \Core\Database::pdo();
                $pdo->exec("DROP DATABASE IF EXISTS `{$dbName}`");
                $pdo->exec("CREATE DATABASE `{$dbName}`");
                $pdo->exec("USE `{$dbName}`");
                echo "✅ Base de datos MySQL '{$dbName}' recreada.\n";
            } catch (PDOException $e) {
                echo "❌ Error en MySQL: " . $e->getMessage() . "\n";
                break;
            }
        }

        // 2. IMPORTANTE: Reiniciar la conexión PDO para que Migration y Seeder
        // trabajen sobre la base de datos recién creada
        \Core\Database::pdo();

        // 3. Correr las migraciones
        echo "🚀 Corriendo migraciones...\n";
        \Core\Migration::run();

        // 4. Correr Seeders si se incluye el flag --seed
        if (isset($argv[2]) && $argv[2] === '--seed') {
            echo "🌱 Corriendo Seeders...\n";
            // Usamos runAll para asegurar que se cargue todo el set de datos
            \Core\Seeder::runAll();
        }

        echo "✨ Proceso de DB Fresh terminado con éxito.\n";
        break;

    case 'make:route':
        $uri = $argv[2] ?? null;
        $controllerAction = $argv[3] ?? null; 
        $httpMethod = $argv[4] ?? 'get';
        $fileName = $argv[5] ?? 'web'; // Por defecto irá a web.php

        if (!$uri || !$controllerAction) {
            echo "❌ Uso: php cli.php make:route /uri Controller@metodo [metodo] [archivo]\n";
            exit(1);
        }

        if (str_contains($controllerAction, '@')) {
            [$controller, $method] = explode('@', $controllerAction);
            \Core\RouteGenerator::make($uri, $controller, $method, $httpMethod, $fileName);
        } else {
            echo "❌ Formato inválido. Use Controller@metodo\n";
        }
        break;

    case 'make:middleware':
        $name = $argv[2] ?? null;
        if (!$name) {
            echo "❌ Uso: php cli.php make:middleware NombreMiddleware\n";
            exit(1);
        }
        \Core\MiddlewareGenerator::make($name);
        break;

    default:
        echo "Comandos disponibles:\n";
        echo "  php scripts/cli.php make:migration nombre_tabla\n";
        echo "  php scripts/cli.php migrate\n";
        echo "  php scripts/cli.php rollback [nombre_migración]\n";
        echo "  php scripts/cli.php make:seed NombreSeeder\n";
        echo "  php scripts/cli.php seed NombreSeeder\n";
        echo "  php scripts/cli.php make:controller Nombre/Controller\n";
        echo "  php scripts/cli.php gen:rsa\n";
        echo "  php scripts/cli.php make:model NombreModelo\n";
        echo "  php scripts/cli.php db:fresh [--seed]\n";
        echo "  php scripts/cli.php make:route /uri Controller@metodo [metodo] [archivo]\n";
        echo "  php scripts/cli.php make:middleware NombreMiddleware\n";
        exit(1);
}
