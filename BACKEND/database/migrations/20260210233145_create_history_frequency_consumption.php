<?php

use Core\Schema;
use Core\Blueprint;

class CreateHistoryFrequencyConsumption
{
    public function up(PDO $pdo): void
    {
        Schema::create('history_frequency_consumption', function (Blueprint $table) {
            $table->id();

            $table->foreignId('patient_id');
            $table->foreignId('unit_frequency_id');

            $table->string('food');
            $table->decimal('frequency');

            $table->timestamps();
            $table->softDeletes();

            $table->foreign('patient_id')
                ->references('id')->on('users')
                ->onDelete('RESTRICT');

            $table->foreign('unit_frequency_id')
                ->references('id')->on('history_unit_frequency')
                ->onDelete('RESTRICT');
        });
    }

    public function down(PDO $pdo): void
    {
        Schema::dropIfExists('history_frequency_consumption');
    }
}
