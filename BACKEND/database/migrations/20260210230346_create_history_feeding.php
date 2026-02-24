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

            $table->string('appetite', 255, false, true);
            $table->string('preferences', 255, false, true);
            $table->string('rejections', 255, false, true);
            $table->string('intolerances_allergies', 255, false, true);
            $table->string('general_observations', 255, false, true);

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
