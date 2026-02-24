<?php

use Core\Schema;
use Core\Blueprint;

class CreateMeasureFolds
{
    /**
     * Ejecutar las migraciones.
     */
    public function up(PDO $pdo): void
    {
        Schema::create('measure_folds', function (Blueprint $table) {
            $table->id();

            $table->foreignId('patient_id');
            
            $table->decimal('triceps');
            $table->decimal('subspcapular');
            $table->decimal('biceps');
            $table->decimal('pectoral');
            $table->decimal('axillary');
            $table->decimal('suprailiac');
            $table->decimal('supraspinal');
            $table->decimal('abdominal');
            $table->decimal('thigh');
            $table->decimal('leg');
            
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
        Schema::dropIfExists('measure_folds');
    }
}