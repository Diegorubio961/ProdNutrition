<?php

namespace Core;

class Router
{
    private array $routes = [];
    
    // Propiedades para el manejo de estados de grupo
    private string $groupPrefix = '';
    private array $groupMiddleware = [];

    /* ======================== REGISTRO DE GRUPOS ======================== */

    public function group(array $attributes, callable $callback): void
    {
        $previousPrefix = $this->groupPrefix;
        $previousMiddleware = $this->groupMiddleware;

        $this->groupPrefix .= '/' . trim($attributes['prefix'] ?? '', '/');
        if (isset($attributes['middleware'])) {
            $this->groupMiddleware = array_merge($this->groupMiddleware, (array)$attributes['middleware']);
        }

        $callback($this);

        $this->groupPrefix = $previousPrefix;
        $this->groupMiddleware = $previousMiddleware;
    }

    /* ======================== REGISTRO DE MÉTODOS ======================== */

    public function get(string $uri, callable|array $action): void { $this->add('GET', $uri, $action); }
    public function post(string $uri, callable|array $action): void { $this->add('POST', $uri, $action); }
    public function put(string $uri, callable|array $action): void { $this->add('PUT', $uri, $action); }
    public function delete(string $uri, callable|array $action): void { $this->add('DELETE', $uri, $action); }

    private function add(string $method, string $uri, callable|array $action): void
    {
        $fullUri = $this->groupPrefix . '/' . trim($uri, '/');
        $fullUri = '/' . trim($fullUri, '/');

        if (is_array($action) && isset($action['action'])) {
            $routeData = $action;
        } else {
            $routeData = ['action' => $action];
        }

        $routeMiddleware = $routeData['middleware'] ?? [];
        $routeData['middleware'] = array_merge($this->groupMiddleware, (array)$routeMiddleware);

        $this->routes[$method][$fullUri] = $routeData;
    }

    /* ======================== DESPACHO (DISPATCH) ======================== */

    public function dispatch(string $method, string $uri): void
    {
        // 1. INICIALIZAR REQUEST SINGLETON
        // Al llamar a getInstance, capturamos headers, body y query una sola vez.
        $request = \Core\Request::getInstance();

        $uri = parse_url($uri, PHP_URL_PATH);
        $scriptName = dirname($_SERVER['SCRIPT_NAME']);
        if ($scriptName !== '/' && str_starts_with($uri, $scriptName)) {
            $uri = substr($uri, strlen($scriptName));
        }

        $uri = '/' . trim($uri, '/');
        $route = $this->routes[$method][$uri] ?? null;

        if (!$route) {
            $this->errorResponse("Not found", 404);
            return;
        }

        // 2. EJECUCIÓN DE MIDDLEWARES
        if (!empty($route['middleware'])) {
            foreach ($route['middleware'] as $middlewareDef) {
                $className = $middlewareDef;
                $params = [];
                $handlerMethod = 'handle';

                if (is_string($middlewareDef)) {
                    if (str_contains($middlewareDef, '@')) {
                        [$beforeAt, $handlerMethod] = explode('@', $middlewareDef);
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
                    $this->errorResponse("Clase de middleware {$className} no válida", 500);
                    return;
                }

                // Los parámetros del constructor (si existen) se pasan aquí
                $middlewareInstance = new $className(...$params);

                if (!method_exists($middlewareInstance, $handlerMethod)) {
                    $this->errorResponse("Método {$handlerMethod} no existe en {$className}", 500);
                    return;
                }

                /**
                 * EJECUCIÓN DEL MIDDLEWARE
                 * Internamente, el middleware hará: $request = \Core\Request::getInstance();
                 * Cualquier $request->setAttribute() afectará a la misma instancia que recibirá el controlador.
                 */
                $middlewareInstance->$handlerMethod();
            }
        }

        // 3. EJECUCIÓN DEL CONTROLADOR
        $action = $route['action'];

        if (is_callable($action)) {
            $action();
            return;
        }

        [$controllerClass, $methodName] = $action;

        if (!class_exists($controllerClass)) {
            $this->errorResponse("Clase controlador {$controllerClass} no encontrada", 500);
            return;
        }

        /**
         * El controlador se instancia. 
         * Si hereda de BaseController, su constructor hará: $this->request = Request::getInstance();
         * obteniendo así el objeto enriquecido por los middlewares.
         */
        $controllerInstance = new $controllerClass();

        if (!method_exists($controllerInstance, $methodName)) {
            $this->errorResponse("El método {$methodName} no existe en el controlador", 500);
            return;
        }

        $controllerInstance->$methodName();
    }

    private function errorResponse(string $message, int $code = 400): void
    {
        http_response_code($code);
        header('Content-Type: application/json');
        echo json_encode(['error' => $message]);
    }
}