<?php

use Core\Schema;
use Core\Blueprint;

class CreateMeasureLenghts
{
    public function up(PDO $pdo): void
    {
        Schema::create('measure_lenghts', function (Blueprint $table) {
            $table->id();

            $table->foreignId('patient_id');

            $table->decimal('acromial_radial');
            $table->decimal('radial_styloid');
            $table->decimal('medial_styloid_dactilar');
            $table->decimal('ileospinal');
            $table->decimal('trochanteric');
            $table->decimal('trochanteric_tibial_lateral');
            $table->decimal('tibial_lateral');
            $table->decimal('tibial_medial_malleolar_medial');
            $table->decimal('foot');

            $table->timestamps();
            $table->softDeletes();

            $table->foreign('patient_id')
                ->references('id')->on('users')
                ->onDelete('RESTRICT');
        });
    }

    public function down(PDO $pdo): void
    {
        Schema::dropIfExists('measure_lenghts');
    }
}
