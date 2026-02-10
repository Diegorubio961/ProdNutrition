<?php

use Core\Schema;
use Core\Blueprint;

class CreateMeasureGeneral
{
    /**
     * Ejecutar las migraciones.
     */
    public function up(PDO $pdo): void
    {
        Schema::create('measure_general', function (Blueprint $table) {
            $table->id();
            
            $table->foreignId('patient_id');

            $table->string('occupation_sports');
            $table->string('category_mode');
            $table->string('anthropometry');
            $table->string('control');
            $table->string('sex');
            $table->decimal('weigth_kg');
            $table->decimal('size_cm');
            $table->decimal('size_sitting_cm');
            $table->decimal('height_chair_cm');
            $table->decimal('wingspan_cm');


            $table->timestamps();
            $table->softDeletes();

            // Foreign Keys
            $table->foreign('patient_id')
                  ->references('id')->on('users')
                  ->onDelete('RESTRICT');
        });
    }

    /**
     * Revertir las migraciones.
     */
    public function down(PDO $pdo): void
    {
        Schema::dropIfExists('measure_general');
    }
}