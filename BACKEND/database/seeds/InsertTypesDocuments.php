<?php

class InsertTypesDocuments
{
    public static function run(PDO $pdo): void
    {
        $types = [
            ['type_name' => 'Cédula de Ciudadanía', 'short_name' => 'CC'],
            ['type_name' => 'Tarjeta de Identidad', 'short_name' => 'TI'],
            ['type_name' => 'Cédula de Extranjería', 'short_name' => 'CE'],
            ['type_name' => 'Pasaporte', 'short_name' => 'PA'],
        ];

        $stmt = $pdo->prepare("
            INSERT INTO documents_type (type_name, short_name)
            VALUES (:type_name, :short_name)
        ");

        foreach ($types as $type) {
            $stmt->execute([
                ':type_name' => $type['type_name'],
                ':short_name' => $type['short_name']
            ]);
        }
    }
}