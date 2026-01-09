<?php
/* ------------------------------------------------------------------
 | 1) Constantes base del proyecto
 *-----------------------------------------------------------------*/
define('BASE_PATH', dirname(__DIR__));
define('APP_PATH',  BASE_PATH . '/app');

require_once BASE_PATH . '/autoload.php';

/* ------------------------------------------------------------------
 | 2) Cargar clases base
 *-----------------------------------------------------------------*/

use Core\Router;
use Core\Request;
use Core\Response;
use App\Middlewares\CorsMiddleware;
use Core\Env;
Env::load(); // Cargar .env
/* ------------------------------------------------------------------
 | 3) Configuración global (debug, zona horaria…)
 *-----------------------------------------------------------------*/
$config = require BASE_PATH . '/config/app.php';

if (!empty($config['timezone'])) {
    date_default_timezone_set($config['timezone']);
}

// Mostrar errores si está activado el modo debug
if (!empty($config['debug'])) {
    ini_set('display_errors', '1');
    ini_set('display_startup_errors', '1');
    error_reporting(E_ALL);
} else {
    ini_set('display_errors', '0');
    error_reporting(0);
}

/* ------------------------------------------------------------------
 | 4) Instanciar el router
 *-----------------------------------------------------------------*/
$router = new Router();

/**
 * Recorre recursivamente un directorio e incluye todos los .php encontrados.
 */
function includeRouteFiles(string $dir, Router $router): void
{
    foreach (scandir($dir) as $item) {
        if ($item === '.' || $item === '..') continue;

        $path = $dir . DIRECTORY_SEPARATOR . $item;

        if (is_dir($path)) {
            includeRouteFiles($path, $router);
        } elseif (pathinfo($path, PATHINFO_EXTENSION) === 'php') {
            require $path;
        }
    }
}


// Incluir todas las rutas
includeRouteFiles(APP_PATH . '/routes', $router);


/* ------------------------------------------------------------------
 | 6) Despachar la petición con manejo global de errores
 *-----------------------------------------------------------------*/
try {
    
    (new CorsMiddleware)->handle();

    $router->dispatch($_SERVER['REQUEST_METHOD'], $_SERVER['REQUEST_URI']);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode([
        'error' => $e->getMessage(),
        'file'  => $e->getFile(),
        'line'  => $e->getLine()
    ]);
}
