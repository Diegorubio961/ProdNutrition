<?php

namespace Core;

class RouteGenerator
{
    public static function make(string $uri, string $controller, string $method, string $httpMethod = 'get', string $path = 'web'): void
    {
        $directory = BASE_PATH . '/app/routes';
        
        $parts = explode('/', $path);
        $fileName = array_pop($parts); 
        $subFolder = implode('/', $parts);

        $finalDir = $subFolder ? $directory . '/' . $subFolder : $directory;
        if (!is_dir($finalDir)) mkdir($finalDir, 0755, true);

        $filePath = "{$finalDir}/{$fileName}.php";
        $httpMethod = strtolower($httpMethod);
        $cleanUri = ($uri === '/' || empty($uri)) ? '/' : '/' . ltrim($uri, '/');
        $controllerNamespace = str_replace('/', '\\', $controller);

        // 1. CREAR O ACTUALIZAR EL ARCHIVO OBJETIVO
        if (!file_exists($filePath)) {
            $prefix = "/{$fileName}";
            $content = "<?php\n\n/** @var \\Core\\Router \$router */\n\n";
            $content .= "\$router->group(['prefix' => '{$prefix}', 'middleware' => []], function(\$router) {\n";
            // CORRECCIÓN: Usar $method (la acción del controlador) y $httpMethod (get/post) por separado
            $content .= "    \$router->{$httpMethod}('{$cleanUri}', [\n        'action' => ['App\\Controllers\\{$controllerNamespace}', '{$method}']\n    ]);\n";
            $content .= "});\n";
            file_put_contents($filePath, $content);
            echo "✅ Archivo creado: app/routes/{$path}.php\n";
        } else {
            // CORRECCIÓN: Pasar explícitamente el nombre del método del controlador
            self::appendRoute($filePath, $httpMethod, $cleanUri, $controllerNamespace, $method);
            echo "✅ Ruta anexada a: {$path}.php\n";
        }

        if ($subFolder) {
            self::linkToParent($directory, $subFolder, $fileName);
        }
    }

    // CORRECCIÓN: Se añade el parámetro $ctrlMethod para no confundirlo con el $httpMethod
    private static function appendRoute($path, $httpMethod, $uri, $ns, $ctrlMethod) {
        $lines = file($path);
        $routeLine = "    \$router->{$httpMethod}('{$uri}', [\n        'action' => ['App\\Controllers\\{$ns}', '{$ctrlMethod}']\n    ]);\n";
        
        for ($i = count($lines) - 1; $i >= 0; $i--) {
            if (trim($lines[$i]) === '});') {
                array_splice($lines, $i, 0, $routeLine);
                break;
            }
        }
        file_put_contents($path, implode('', $lines));
    }

    private static function linkToParent($baseDir, $parentName, $childName) {
        $parentPath = "{$baseDir}/{$parentName}.php";
        if (!file_exists($parentPath)) return;

        $parentContent = file_get_contents($parentPath);
        $requireLine = "    require_once __DIR__ . '/{$parentName}/{$childName}.php';";

        if (str_contains($parentContent, "/{$childName}.php'")) return;

        $pattern = "/function\s*\(\s*\\\$router\s*\)\s*\{/";
        $parentContent = preg_replace($pattern, "$0\n{$requireLine}", $parentContent, 1);
        
        file_put_contents($parentPath, $parentContent);
        echo "🔗 Vinculado automáticamente en: app/routes/{$parentName}.php\n";
    }
}