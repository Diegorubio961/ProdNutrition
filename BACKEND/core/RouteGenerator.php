<?php

namespace Core;

class RouteGenerator
{
    public static function make(string $uri, string $controller, string $method, string $httpMethod = 'get', string $path = 'web'): void
    {
        $directory = BASE_PATH . '/app/routes';
        
        // "admin/users" -> folder: admin, file: users
        $parts = explode('/', $path);
        $fileName = array_pop($parts); 
        $subFolder = implode('/', $parts);

        $finalDir = $subFolder ? $directory . '/' . $subFolder : $directory;
        if (!is_dir($finalDir)) mkdir($finalDir, 0755, true);

        $filePath = "{$finalDir}/{$fileName}.php";
        $httpMethod = strtolower($httpMethod);
        $cleanUri = ($uri === '/' || empty($uri)) ? '/' : '/' . ltrim($uri, '/');
        $controllerNamespace = str_replace('/', '\\', $controller);

        // 1. CREAR O ACTUALIZAR EL ARCHIVO OBJETIVO (users.php)
        if (!file_exists($filePath)) {
            $prefix = "/{$fileName}";
            $content = "<?php\n\n/** @var \\Core\\Router \$router */\n\n";
            $content .= "\$router->group(['prefix' => '{$prefix}', 'middleware' => []], function(\$router) {\n";
            $content .= "    \$router->{$httpMethod}('{$cleanUri}', [\n        'action' => ['App\\Controllers\\{$controllerNamespace}', '{$method}']\n    ]);\n";
            $content .= "});\n";
            file_put_contents($filePath, $content);
            echo "✅ Archivo creado: app/routes/{$path}.php\n";
        } else {
            self::appendRoute($filePath, $httpMethod, $cleanUri, $controllerNamespace);
            echo "✅ Ruta anexada a: {$path}.php\n";
        }

        // 2. AUTO-VÍNCULO: Si está en una carpeta, lo vinculamos al padre
        if ($subFolder) {
            self::linkToParent($directory, $subFolder, $fileName);
        }
    }

    private static function appendRoute($path, $method, $uri, $ns) {
        $lines = file($path);
        $routeLine = "    \$router->{$method}('{$uri}', [\n        'action' => ['App\\Controllers\\{$ns}', '{$method}']\n    ]);\n";
        $added = false;
        for ($i = count($lines) - 1; $i >= 0; $i--) {
            if (trim($lines[$i]) === '});') {
                array_splice($lines, $i, 0, $routeLine);
                $added = true; break;
            }
        }
        file_put_contents($path, implode('', $lines));
    }

    private static function linkToParent($baseDir, $parentName, $childName) {
        $parentPath = "{$baseDir}/{$parentName}.php";
        if (!file_exists($parentPath)) return;

        $parentContent = file_get_contents($parentPath);
        $requireLine = "    require_once __DIR__ . '/{$parentName}/{$childName}.php';";

        // Si ya está vinculado, no hacer nada
        if (str_contains($parentContent, "/{$childName}.php'")) return;

        // Insertar el require_once al inicio del grupo del padre
        $pattern = "/function\s*\(\s*\\\$router\s*\)\s*\{/";
        $parentContent = preg_replace($pattern, "$0\n{$requireLine}", $parentContent, 1);
        
        file_put_contents($parentPath, $parentContent);
        echo "🔗 Vinculado automáticamente en: app/routes/{$parentName}.php\n";
    }
}