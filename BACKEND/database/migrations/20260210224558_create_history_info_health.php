<?php

use Core\Schema;
use Core\Blueprint;

class CreateHistoryInfoHealth
{
    public function up(PDO $pdo): void
    {
        Schema::create('history_info_health', function (Blueprint $table) {
            $table->id();

            $table->foreignId('patient_id');

            $table->string('consult_reason');
            $table->string('previous_treatment');
            $table->string('family_history');
            $table->string('personal_history');
            $table->string('pubertal_maturation');
            $table->decimal('menarche');
            $table->decimal('regular_menstruation');
            $table->string('additional_pregnancy_data');
            $table->string('surgeries');
            $table->string('gastrointestinal_symptoms');

            $table->timestamps();
            $table->softDeletes();

            $table->foreign('patient_id')
                ->references('id')->on('users')
                ->onDelete('RESTRICT');
        });
    }

    public function down(PDO $pdo): void
    {
        Schema::dropIfExists('history_info_health');
    }
}
