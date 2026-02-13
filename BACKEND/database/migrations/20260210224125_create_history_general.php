<?php

use Core\Schema;
use Core\Blueprint;

class CreateHistoryGeneral
{
    public function up(PDO $pdo): void
    {
        Schema::create('history_general', function (Blueprint $table) {
            $table->id();

            $table->foreignId('patient_id');

            $table->timestamp('birth_date');
            $table->timestamp('care_date');
            $table->integer('social_stratum');
            $table->string('health_provider', 255, false, true);
            $table->string('education_level', 255, false, true);
            $table->integer('cohabiting_people');
            $table->string('occupation', 255, false, true);

            $table->timestamps();
            $table->softDeletes();

            $table->foreign('patient_id')
                ->references('id')->on('users')
                ->onDelete('RESTRICT');
        });
    }

    public function down(PDO $pdo): void
    {
        Schema::dropIfExists('history_general');
    }
}
