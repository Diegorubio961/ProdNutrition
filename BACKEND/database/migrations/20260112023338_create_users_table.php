<?php

use Core\Schema;
use Core\Blueprint;

class CreateUsersTable
{
    /**
     * Ejecutar las migraciones.
     */
    public function up(PDO $pdo): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id(); // BIGINT AUTO_INCREMENT
            
            $table->string('names', 100);
            $table->string('surnames', 100);
            $table->string('phone', 10);
            $table->string('id_card', 20, true); // True activa el UNIQUE
            $table->string('profile_image', 255, false, true); // Último true es nullable
            $table->string('email', 100, true); // Email único
            $table->string('password', 255);

            // Relaciones
            $table->foreignId('document_type_id');
            
            $table->foreignId('plan_id');

            $table->boolean('email_verified', false);

            // Fechas específicas
            $table->timestamp('date_active_plan');
            $table->timestamp('last_update_password');

            // Auditoría
            $table->timestamps();
            $table->softDeletes();

            // Definición de Foreign Keys
            $table->foreign('document_type_id')
                  ->references('id')->on('documents_type')
                  ->onDelete('RESTRICT');

            $table->foreign('plan_id')
                  ->references('id')->on('plans')
                  ->onDelete('SET NULL');
        });
    }

    /**
     * Revertir las migraciones.
     */
    public function down(PDO $pdo): void
    {
        Schema::dropIfExists('users');
    }
}