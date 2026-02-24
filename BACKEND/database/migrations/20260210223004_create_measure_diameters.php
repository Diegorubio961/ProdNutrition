<?php

use Core\Schema;
use Core\Blueprint;

class CreateMeasureDiameters
{
    public function up(PDO $pdo): void
    {
        Schema::create('measure_diameters', function (Blueprint $table) {
            $table->id();

            $table->foreignId('patient_id');

            $table->decimal('biacromial');
            $table->decimal('biileocrestal');
            $table->decimal('antero_posterior_abdominal');
            $table->decimal('thorax_transverse');
            $table->decimal('thorax_antero_posterior');
            $table->decimal('humerus_biepicondylar');
            $table->decimal('wrist_bistyloid');
            $table->decimal('hand');
            $table->decimal('femur_biepicondylar');
            $table->decimal('ankle_bimalleolar');
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
        Schema::dropIfExists('measure_diameters');
    }
}
