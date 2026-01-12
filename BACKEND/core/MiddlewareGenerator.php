<?php

namespace Core;

class MiddlewareGenerator
{
    public static function make(string $name): void
    {
        $directory = BASE_PATH . '/app/middlewares';
        
        // Asegurar que el directorio existe
        if (!is_dir($directory)) {
            mkdir($directory, 0755, true);
        }

        $fileName = "{$name}.php";
        if (!str_ends_with($name, 'Middleware')) {
            $fileName = "{$name}Middleware.php";
            $className = "{$name}Middleware";
        } else {
            $className = $name;
        }

        $filePath = "{$directory}/{$fileName}";

        if (file_exists($filePath)) {
            echo "❌ El middleware ya existe: {$fileName}\n";
            return;
        }

        $template = "<?php

namespace App\Middlewares;
use Core\Request;

class {$className}
{
    /**
     * Constructor para recibir parámetros desde la ruta
     */
    public function __construct(...\$params)
    {
        // Lógica de inicialización
       


    }

    /**
     * Método principal de ejecución
     */
    public function handle(): void
    {
        // Lógica del middleware
         // Capturamos la instancia única del Request (Singleton)
        \$request = Request::getInstance();

        // Ejemplo de inyección de datos:
        // \$request->setAttribute('key', 'value');
    }
}
";

        if (file_put_contents($filePath, $template)) {
            echo "✅ Middleware creado con éxito en: app/middlewares/{$fileName}\n";
        } else {
            echo "❌ Error al crear el archivo.\n";
        }
    }
}