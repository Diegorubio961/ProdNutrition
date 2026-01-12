<?php

use App\Models\RolesModel;

class InsertRoles
{
    public static function run(PDO $pdo): void
    {
        // 1. Opcional: Limpiar la tabla antes de insertar para evitar duplicados
        // Si implementaste el método truncate en el BaseModel:
        RolesModel::truncate();

        $roles = [
            [
                'role_name'   => 'admin', 
                'description' => 'Administrador con acceso completo'
            ],
            [
                'role_name'   => 'nutritionist', 
                'description' => 'Nutricionista con acceso limitado'
            ],
            [
                'role_name'   => 'client', 
                'description' => 'Cliente con acceso básico'
            ],
        ];

        // 2. Insertar usando el modelo
        foreach ($roles as $role) {
            RolesModel::create($role);
        }

        echo "🌱 Roles creados correctamente.\n";
    }
}