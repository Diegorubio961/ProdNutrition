<?php

use Core\Schema;
use Core\Blueprint;

class CreateMeasureGeneralTable
{
    public function up(PDO $pdo): void
    {
        Schema::create('measure_general', function (Blueprint $table) {
            $table->id();

            $table->foreignId('patient_id');

            $table->string('occupation_sport', 255, false, true);
            $table->string('category_modality', 255, false, true);
            $table->string('anthropometry', 255, false, true);
            $table->string('control', 255, false, true);
            $table->string('sex', 255, false, true);
            $table->decimal('weight_kg', 8, 2);
            $table->decimal('height_cm', 8, 2);
            $table->decimal('sitting_height_cm', 8, 2);
            $table->decimal('bench_height_cm', 8, 2);
            $table->decimal('corrected_sitting_height_cm', 8, 2);
            $table->decimal('wingspan_cm', 8, 2);

            $table->timestamps();
            $table->softDeletes();

            $table->foreign('patient_id')
                ->references('id')->on('users')
                ->onDelete('RESTRICT');
        });
    }

    public function down(PDO $pdo): void
    {
        Schema::dropIfExists('measure_general');
    }
}
