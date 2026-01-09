<?php

class InsertAdminUser
{
    public static function run(PDO $pdo): void
    {
        $general_password = password_hash('Admin1234', PASSWORD_BCRYPT);

        $stmt = $pdo->prepare("
            INSERT INTO users (name, phone, document_type_id, id_card, email, password, role_id)
            VALUES (:name, :phone, :document_type_id, :id_card, :email, :password, :role_id)
        ");

        $stmt->execute([
            ':name' => 'Diego Rubio',
            ':phone' => '3219874491',
            ':document_type_id' => 1,
            ':id_card' => '1032507856',
            ':email' => 'admin@example.com',
            ':password' => $general_password,
            ':role_id' => 1
        ]);
    }
}