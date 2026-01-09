#!/usr/bin/env php
<?php

namespace Scripts;

use Core\Migration;
use Core\Seeder;
use Core\Env;
use Core\ControllerGenerator;
use Core\RSAKeyGenerator;

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

    default:
        echo "Comandos disponibles:\n";
        echo "  php scripts/cli.php make:migration nombre_tabla\n";
        echo "  php scripts/cli.php migrate\n";
        echo "  php scripts/cli.php rollback [nombre_migración]\n";
        echo "  php scripts/cli.php make:seed NombreSeeder\n";
        echo "  php scripts/cli.php seed NombreSeeder\n";
        echo "  php scripts/cli.php make:controller Nombre/Controller\n";
        echo "  php scripts/cli.php gen:rsa\n";
        exit(1);
}
