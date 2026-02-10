<?php

use Core\Schema;
use Core\Blueprint;

class CreateHistoryMedicationsSupplements
{
    public function up(PDO $pdo): void
    {
        Schema::create('history_medications_supplements', function (Blueprint $table) {
            $table->id();

            $table->foreignId('patient_id');

            $table->string('indicator');
            $table->string('objective');
            $table->decimal('dose');
            $table->string('unit');
            $table->decimal('frequency_hours');
            $table->string('prescribed');

            $table->timestamps();
            $table->softDeletes();

            $table->foreign('patient_id')
                ->references('id')->on('users')
                ->onDelete('RESTRICT');
        });
    }

    public function down(PDO $pdo): void
    {
        Schema::dropIfExists('history_medications_supplements');
    }
}
