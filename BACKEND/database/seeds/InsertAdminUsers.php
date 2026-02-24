<?php

use App\Models\UsersModel;
use App\Models\RolesModel;
use App\Models\UserInRolModel;

class InsertAdminUsers
{
    public static function run(PDO $pdo): void
    {
        // 1. Limpiamos la tabla de usuarios antes de sembrar
        UsersModel::truncate();

        $general_password = password_hash('Admin1234', PASSWORD_BCRYPT);

        $dev_users = [
            [
                'names'             => 'Diego',
                'surnames'          => 'Rubio',
                'phone'            => '3219874491',
                'document_type_id' => 1,
                'id_card'          => '1234567890',
                'email'            => 'diegorubiovarela967@gmail.com',
                'password'         => $general_password,
                'state'           => 'Activo'
            ],
            [
                'names'             => 'Andres',
                'surnames'          => 'Mantilla',
                'phone'            => '3232884772',
                'document_type_id' => 1,
                'id_card'          => '1234567891',
                'email'            => 'andresmantilla0506@outlook.com',
                'password'         => $general_password,
                'state'           => 'Activo'
            ],
            [
                'names'             => 'Deyvid',
                'surnames'          => 'Bedoya',
                'phone'            => '3219318780',
                'document_type_id' => 1,
                'id_card'          => '1234567892',
                'email'            => 'deyvidbedoya@gmail.com',
                'password'         => $general_password,
                'state'           => 'Activo'
            ]
        ];

        // 2. Insertar cada usuario usando el modelo
        foreach ($dev_users as $user) {
            UsersModel::create($user);
        }

        // 3. obtener la lista de roles
        $roles = RolesModel::query()->whereIn('role_name', ['admin', 'nutritionist'])->get();

        // 4. Asignar roles a los usuarios creados
        foreach (UsersModel::all() as $user) {
            foreach ($roles as $role) {
                UserInRolModel::create([
                    'user_id' => $user['id'],
                    'rol_id'  => $role['id']
                ]);
            }
        }

        echo "🌱 Usuarios administradores creados con éxito.\n";
    }
}