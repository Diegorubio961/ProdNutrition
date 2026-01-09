<?php

namespace Core;

class Router
{
    private array $routes = [];

    /* ======================== METHOD REGISTRATION ======================== */
    public function get(string $uri, callable|array $action): void
    {
        $this->add('GET',  $uri, $action);
    }
    public function post(string $uri, callable|array $action): void
    {
        $this->add('POST', $uri, $action);
    }

    private function add(string $method, string $uri, callable|array $action): void
    {
        if (is_array($action) && isset($action['action'])) {
            $this->routes[$method][$uri] = $action; // acción + middleware
        } else {
            $this->routes[$method][$uri] = ['action' => $action]; // solo acción
        }
    }

    /* ======================== DISPATCH ======================== */
    public function dispatch(string $method, string $uri): void
    {
        $uri = parse_url($uri, PHP_URL_PATH);

        // Elimina el path base si es necesario (ej. /API/public)
        $scriptName = dirname($_SERVER['SCRIPT_NAME']);
        if (str_starts_with($uri, $scriptName)) {
            $uri = substr($uri, strlen($scriptName));
        }

        // Asegura que comience con "/"
        $uri = '/' . ltrim($uri, '/');

        $route = $this->routes[$method][$uri] ?? null;

        if (!$route) {
            http_response_code(404);
            echo json_encode(['error' => 'Not found']);
            return;
        }

        // Si es un array plano: ['Controlador', 'metodo'], se normaliza
        if (isset($route[0]) && is_string($route[0])) {
            $route = ['action' => $route];
        }

        // Ejecutar middlewares si existen
        if (!empty($route['middleware'])) {
            foreach ($route['middleware'] as $middlewareDef) {
                $className = $middlewareDef;
                $params = [];
                $method = 'handle'; // método por defecto

                if (is_string($middlewareDef)) {
                    // Ejemplo: App\Middlewares\PermissionMiddleware:admin,ventas@verificarPermisos
                    if (str_contains($middlewareDef, '@')) {
                        [$beforeAt, $method] = explode('@', $middlewareDef);
                    } else {
                        $beforeAt = $middlewareDef;
                    }

                    if (str_contains($beforeAt, ':')) {
                        [$className, $paramStr] = explode(':', $beforeAt, 2);
                        $params = array_map('trim', explode(',', $paramStr));
                    } else {
                        $className = $beforeAt;
                    }
                }

                if (!class_exists($className)) {
                    http_response_code(500);
                    echo json_encode(['error' => "Clase de middleware {$className} no válida"]);
                    return;
                }

                $middleware = new $className(...$params);

                if (!method_exists($middleware, $method)) {
                    http_response_code(500);
                    echo json_encode(['error' => "El método {$method} no existe en {$className}"]);
                    return;
                }

                $middleware->$method();
            }
        }

        // Ejecutar controlador
        [$controller, $methodName] = $route['action'];

        if (!class_exists($controller)) {
            http_response_code(500);
            echo json_encode(['error' => "Clase {$controller} no encontrada"]);
            return;
        }


        $controllerInstance = new $controller();

        if (!method_exists($controllerInstance, $methodName)) {
            echo json_encode(['error' => "Método {$methodName} no existe"]);
            return;
        }

        $controllerInstance->$methodName();
    }
}
