<?php

use Core\Schema;
use Core\Blueprint;

class CreateDocumentsTypeTable
{
    public function up(PDO $pdo): void
    {
        Schema::create('documents_type', function (Blueprint $table) {
            $table->id(); // id INT AUTO_INCREMENT PRIMARY KEY
            
            $table->string('type_name', 100);
            $table->string('short_name', 255); // Por defecto en nuestro Blueprint es VARCHAR
            
            $table->timestamps(); // created_at, updated_at
            
            // Para el deleted_at (ver paso 2 para que esto funcione)
            $table->softDeletes(); 
        });
    }

    public function down(PDO $pdo): void
    {
        // Schema::dropIfExists maneja la eliminación segura en ambos motores
        Schema::dropIfExists('documents_type');
    }
}