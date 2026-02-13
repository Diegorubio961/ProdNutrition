<?php

use Core\Schema;
use Core\Blueprint;

class CreateHistoryPhysicalActivity
{
    public function up(PDO $pdo): void
    {
        Schema::create('history_physical_activity', function (Blueprint $table) {
            $table->id();

            $table->foreignId('patient_id');

            $table->string('activity', 255, false, true);
            $table->decimal('frequency_days');
            $table->string('training_schedule', 255, false, true);
            $table->decimal('intensity_hours');

            $table->timestamps();
            $table->softDeletes();

            $table->foreign('patient_id')
                ->references('id')->on('users')
                ->onDelete('RESTRICT');
        });
    }

    public function down(PDO $pdo): void
    {
        Schema::dropIfExists('history_physical_activity');
    }
}
