<?php

use Core\Schema;
use Core\Blueprint;

class CreateUserInRolTable
{
    /**
     * Ejecutar las migraciones.
     */
    public function up(PDO $pdo): void
    {
        Schema::create('user_in_rol', function (Blueprint $table) {
            $table->foreignId('user_id');
            $table->foreignId('rol_id');

            $table->foreign('user_id')
                ->references('id')->on('users')
                ->onDelete('RESTRICT');

            $table->foreign('rol_id')
                ->references('id')->on('roles')
                ->onDelete('RESTRICT');
        });
    }

    /**
     * Revertir las migraciones.
     */
    public function down(PDO $pdo): void
    {
        Schema::dropIfExists('user_in_rol');
    }
}
