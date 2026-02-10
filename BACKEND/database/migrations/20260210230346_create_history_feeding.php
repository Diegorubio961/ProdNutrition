<?php

use Core\Schema;
use Core\Blueprint;

class CreateHistoryFeeding
{
    public function up(PDO $pdo): void
    {
        Schema::create('history_feeding', function (Blueprint $table) {
            $table->id();

            $table->foreignId('patient_id');

            $table->string('appetite');
            $table->string('preferences');
            $table->string('rejections');
            $table->string('intolerances_allergies');
            $table->string('general_observations');

            $table->timestamps();
            $table->softDeletes();

            $table->foreign('patient_id')
                ->references('id')->on('users')
                ->onDelete('RESTRICT');
        });
    }

    public function down(PDO $pdo): void
    {
        Schema::dropIfExists('history_feeding');
    }
}
