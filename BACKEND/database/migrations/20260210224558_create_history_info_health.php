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

            $table->string('consult_reason', 255, false, true);
            $table->string('previous_treatment', 255, false, true);
            $table->string('family_history', 255, false, true);
            $table->string('personal_history', 255, false, true);
            $table->string('pubertal_maturation', 255, false, true);
            $table->decimal('menarche');
            $table->decimal('regular_menstruation');
            $table->string('additional_pregnancy_data', 255, false, true);
            $table->string('surgeries', 255, false, true);
            $table->string('gastrointestinal_symptoms', 255, false, true);

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
