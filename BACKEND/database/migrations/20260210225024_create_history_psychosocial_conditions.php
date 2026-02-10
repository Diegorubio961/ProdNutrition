<?php

use Core\Schema;
use Core\Blueprint;

class CreateHistoryPsychosocialConditions
{
    public function up(PDO $pdo): void
    {
        Schema::create('history_psychosocial_conditions', function (Blueprint $table) {
            $table->id();

            $table->foreignId('patient_id');

            $table->string('conditions');
            $table->decimal('sleep_hours');
            $table->string('sleep_quality');

            $table->timestamps();
            $table->softDeletes();

            $table->foreign('patient_id')
                ->references('id')->on('users')
                ->onDelete('RESTRICT');
        });
    }

    public function down(PDO $pdo): void
    {
        Schema::dropIfExists('history_psychosocial_conditions');
    }
}
