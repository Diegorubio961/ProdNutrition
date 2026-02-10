<?php

use Core\Schema;
use Core\Blueprint;

class CreateHistoryReminder
{
    public function up(PDO $pdo): void
    {
        Schema::create('history_reminder', function (Blueprint $table) {
            $table->id();

            $table->foreignId('patient_id');

            $table->string('meal_type');
            $table->timestamp('time');
            $table->string('place');
            $table->string('preparation');
            $table->decimal('quantity');

            $table->timestamps();
            $table->softDeletes();

            $table->foreign('patient_id')
                ->references('id')->on('users')
                ->onDelete('RESTRICT');
        });
    }

    public function down(PDO $pdo): void
    {
        Schema::dropIfExists('history_reminder');
    }
}
