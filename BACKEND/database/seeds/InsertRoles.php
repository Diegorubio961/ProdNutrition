<?php

class InsertRoles
{
    public static function run(PDO $pdo): void
    {
        $roles = [
            ['role_name' => 'admin', 'description' => 'Administrador con acceso completo'],
            ['role_name' => 'nutritionist', 'description' => 'Nutricionista con acceso limitado'],
            ['role_name' => 'client', 'description' => 'Cliente con acceso básico'],
        ];

        $stmt = $pdo->prepare("
            INSERT INTO roles (role_name, description)
            VALUES (:role_name, :description)
        ");

        foreach ($roles as $role) {
            $stmt->execute([
                ':role_name' => $role['role_name'],
                ':description' => $role['description']
            ]);
        }
    }
}