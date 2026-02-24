<?php

use Core\Schema;
use Core\Blueprint;

class CreateRolesTable
{
    /**
     * Ejecutar las migraciones.
     */
    public function up(PDO $pdo): void
    {
        Schema::create('roles', function (Blueprint $table) {
            $table->id(); // Genera la PK autoincremental universal
            
            $table->string('role_name', 100);
            $table->string('description', 255);
            
            $table->timestamps();  // created_at y updated_at
            $table->softDeletes(); // deleted_at (usando el método que agregamos)
        });
    }

    /**
     * Revertir las migraciones.
     */
    public function down(PDO $pdo): void
    {
        // Eliminamos la tabla de forma segura sin preocuparnos por el driver
        Schema::dropIfExists('roles');
    }
}