<?php

use App\Models\DocumentsTypeModel;

class InsertDocumentsTypes
{
    public static function run(): void
    {
        // 1. Limpiamos datos viejos (opcional)
        DocumentsTypeModel::truncate();

        $types = [
            ['type_name' => 'Cédula de Ciudadanía', 'short_name' => 'CC'],
            ['type_name' => 'Tarjeta de Identidad', 'short_name' => 'TI'],
            ['type_name' => 'Cédula de Extranjería', 'short_name' => 'CE'],
            ['type_name' => 'Pasaporte', 'short_name' => 'PA'],
        ];

        // 2. Usamos el método CREATE de tu BaseModel
        foreach ($types as $type) {
            DocumentsTypeModel::create($type);
        }
        
        echo "🌱 Semillas de Documentos plantadas usando el Modelo.\n";
    }
}