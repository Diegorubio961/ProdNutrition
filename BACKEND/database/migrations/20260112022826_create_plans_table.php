<?php

use Core\Schema;
use Core\Blueprint;

class CreatePlansTable
{
    /**
     * Ejecutar las migraciones.
     */
    public function up(PDO $pdo): void
    {
        Schema::create('plans', function (Blueprint $table) {
            $table->id();
            
            $table->string('name', 255);
            $table->text('description'); // Ahora soportado
            $table->decimal('price', 10, 2); // Ahora soportado
            $table->integer('duration_days');
            
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Revertir las migraciones.
     */
    public function down(PDO $pdo): void
    {
        Schema::dropIfExists('plans');
    }
}