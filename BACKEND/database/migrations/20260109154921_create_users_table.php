<?php

class CreateUsersTable
{
    public function up(PDO $pdo): void
    {
        $pdo->exec("
        CREATE TABLE users (
            id BIGINT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(100) NOT NULL,
            phone VARCHAR(10) NOT NULL,
            document_type_id INT NOT NULL,
            id_card VARCHAR(20) NOT NULL UNIQUE,
            profile_image VARCHAR(255) DEFAULT NULL,
            email VARCHAR(100) NOT NULL UNIQUE,
            password VARCHAR(255) NOT NULL,
            role_id INT DEFAULT NULL,
            last_update_password TIMESTAMP DEFAULT (CURRENT_TIMESTAMP - INTERVAL 90 DAY),
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            deleted_at TIMESTAMP NULL DEFAULT NULL,
            CONSTRAINT fk_document_type FOREIGN KEY (document_type_id) REFERENCES documents_type(id) ON DELETE RESTRICT ON UPDATE CASCADE,
            CONSTRAINT fk_role FOREIGN KEY (role_id) REFERENCES roles(id) ON DELETE SET NULL ON UPDATE CASCADE
        );
        ");
    }

    public function down(PDO $pdo): void
    {
        $pdo->exec("SET FOREIGN_KEY_CHECKS=0;");
        $pdo->exec("DROP TABLE IF EXISTS users;");
        $pdo->exec("SET FOREIGN_KEY_CHECKS=1;");
    }
}
