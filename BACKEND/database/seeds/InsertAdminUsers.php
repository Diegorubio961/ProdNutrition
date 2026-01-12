<?php

use App\Models\UsersModel;

class InsertAdminUsers
{
    public static function run(PDO $pdo): void
    {
        // 1. Limpiamos la tabla de usuarios antes de sembrar
        UsersModel::truncate();

        $general_password = password_hash('Admin1234', PASSWORD_BCRYPT);

        $dev_users = [
            [
                'name'             => 'Diego Rubio',
                'phone'            => '3219874491',
                'document_type_id' => 1,
                'id_card'          => '1234567890',
                'email'            => 'diegorubiovarela967@gmail.com',
                'password'         => $general_password,
                'role_id'          => 1
            ],
            [
                'name'             => 'Andres Mnatilla',
                'phone'            => '3232884772',
                'document_type_id' => 1,
                'id_card'          => '1234567891',
                'email'            => 'andresmantilla0506@outlook.com',
                'password'         => $general_password,
                'role_id'          => 1
            ],
            [
                'name'             => 'Deyvid Bedoya',
                'phone'            => '3219318780',
                'document_type_id' => 1,
                'id_card'          => '1234567892',
                'email'            => 'deyvidbedoya@gmail.com',
                'password'         => $general_password,
                'role_id'          => 1
            ]
        ];

        // 2. Insertar cada usuario usando el modelo
        foreach ($dev_users as $user) {
            UsersModel::create($user);
        }

        echo "🌱 Usuarios administradores creados con éxito.\n";
    }
}