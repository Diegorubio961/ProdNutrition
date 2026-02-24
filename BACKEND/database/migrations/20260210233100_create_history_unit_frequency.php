<?php

use Core\Schema;
use Core\Blueprint;

class CreateHistoryUnitFrequency
{
    /**
     * Ejecutar las migraciones.
     */
    public function up(PDO $pdo): void
    {
        Schema::create('history_unit_frequency', function (Blueprint $table) {
            $table->id();
            
            $table->string('unit_frequency');

            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Revertir las migraciones.
     */
    public function down(PDO $pdo): void
    {
        Schema::dropIfExists('history_unit_frequency');
    }
}