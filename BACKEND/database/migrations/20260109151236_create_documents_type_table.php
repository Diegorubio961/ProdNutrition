<?php

class CreateDocumentsTypeTable
{
    public function up(PDO $pdo): void
    {
        $pdo->exec("
        CREATE TABLE documents_type (
            id INT AUTO_INCREMENT PRIMARY KEY,
            type_name VARCHAR(100) NOT NULL,
            short_name VARCHAR(255) DEFAULT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            deleted_at TIMESTAMP NULL DEFAULT NULL
        );
        ");
    }

    public function down(PDO $pdo): void
    {
        $pdo->exec("SET FOREIGN_KEY_CHECKS=0;");
        $pdo->exec("DROP TABLE IF EXISTS documents_type;");
        $pdo->exec("SET FOREIGN_KEY_CHECKS=1;");
    }
}
