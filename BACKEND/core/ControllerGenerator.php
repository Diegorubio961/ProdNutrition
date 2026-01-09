<?php

namespace Core;

class ControllerGenerator
{
    public static function make(string $input): void
    {
        $baseDir = BASE_PATH . '/app/controllers';

        // Normalizar ruta y extraer clase
        $input = str_replace(['\\', '//'], '/', $input); // soporte para \
        $parts = explode('/', $input);
        $className = array_pop($parts);  // LoginController
        $subPath   = implode('/', $parts); // Auth
        $namespace = 'App\\Controllers' . ($subPath ? '\\' . str_replace('/', '\\', $subPath) : '');

        $fullDir = $baseDir . ($subPath ? '/' . $subPath : '');
        $fullPath = $fullDir . '/' . $className . '.php';

        if (file_exists($fullPath)) {
            echo "⚠️ Ya existe el controlador en: {$fullPath}\n";
            return;
        }

        if (!is_dir($fullDir)) {
            mkdir($fullDir, 0777, true);
        }

        $template = <<<PHP
        <?php

        namespace {$namespace};
        use App\Controllers\BaseController;

        class {$className} extends BaseController
        {
            public function __construct()
            {
                // Constructor del controlador
            }

            public function index()
            {
                // Método por defecto
            }
        }
        PHP;

        file_put_contents($fullPath, $template);
        echo "✅ Controlador creado: {$fullPath}\n";
    }
}
