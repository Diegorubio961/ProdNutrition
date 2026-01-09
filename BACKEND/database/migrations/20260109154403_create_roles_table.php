<?php

class CreateRolesTable
{
    public function up(PDO $pdo): void
    {
        $pdo->exec("
        CREATE TABLE roles (
            id INT AUTO_INCREMENT PRIMARY KEY,
            role_name VARCHAR(100) NOT NULL,
            description VARCHAR(255) DEFAULT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            deleted_at TIMESTAMP NULL DEFAULT NULL
        );
        ");
    }

    public function down(PDO $pdo): void
    {
        $pdo->exec("SET FOREIGN_KEY_CHECKS=0;");
        $pdo->exec("DROP TABLE IF EXISTS roles;");
        $pdo->exec("SET FOREIGN_KEY_CHECKS=1;");
    }
}
