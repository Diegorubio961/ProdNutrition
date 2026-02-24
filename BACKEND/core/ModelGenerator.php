<?php

namespace Core;

class ModelGenerator
{
    public static function make($name)
    {
        $path = BASE_PATH . '/app/models/';

        // 1. Asegurar que el nombre de la clase termine en "Model"
        // Si escribes "User", se convierte en "UserModel"
        // Si escribes "UserModel", se queda igual.
        $className = ucfirst($name);
        if (substr($className, -5) !== 'Model') {
            $className .= 'Model';
        }

        // 2. Definir el nombre del archivo
        $filename = $path . $className . '.php';

        if (file_exists($filename)) {
            echo "❌ El modelo {$className} ya existe.\n";
            return;
        }

        // 3. Generar el nombre de la tabla
        // Quitamos la palabra 'Model' para procesar la tabla: UserModel -> User
        $baseName = substr($className, 0, -5);

        // Convertimos PascalCase a snake_case (Ej: DocumentsType -> documents_type)
        $tableName = strtolower(preg_replace('/(?<!^)[A-Z]/', '_$0', $baseName));

        // 4. Contenido del archivo (Basado en tu ejemplo)
        $content = "<?php

namespace App\Models;

class {$className} extends BaseModel
{
    protected static string \$table = '{$tableName}';
}
";

        // 5. Guardar archivo
        if (file_put_contents($filename, $content)) {
            echo "✅ Modelo creado: app/models/{$className}.php\n";
            echo "📊 Tabla sugerida: '{$tableName}'\n";
        } else {
            echo "❌ Error al escribir el archivo.\n";
        }
    }
}
