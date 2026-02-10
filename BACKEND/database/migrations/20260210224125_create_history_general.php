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
            $table->decimal('social_stratum');
            $table->string('health_provider');
            $table->decimal('education_level_id');
            $table->decimal('cohabiting_people');
            $table->string('occupation');

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
