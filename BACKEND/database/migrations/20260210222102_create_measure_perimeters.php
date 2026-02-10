<?php

use Core\Schema;
use Core\Blueprint;

class CreateMeasurePerimeters
{
    public function up(PDO $pdo): void
    {
        Schema::create('measure_perimeters', function (Blueprint $table) {
            $table->id();

            $table->foreignId('patient_id');

            $table->decimal('head');
            $table->decimal('neck');
            $table->decimal('arm_relaxed');
            $table->decimal('arm_tensed');
            $table->decimal('forearm');
            $table->decimal('wrist');
            $table->decimal('mesosternal');
            $table->decimal('waist');
            $table->decimal('abdominal');
            $table->decimal('hip');
            $table->decimal('thigh_max');
            $table->decimal('thigh_mid');
            $table->decimal('calf_max');
            $table->decimal('ankle_min');

            $table->timestamps();
            $table->softDeletes();

            $table->foreign('patient_id')
                ->references('id')->on('users')
                ->onDelete('RESTRICT');
        });
    }

    public function down(PDO $pdo): void
    {
        Schema::dropIfExists('measure_perimeters');
    }
}
