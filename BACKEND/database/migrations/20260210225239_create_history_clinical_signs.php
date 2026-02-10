<?php

use Core\Schema;
use Core\Blueprint;

class CreateHistoryClinicalSigns
{
    public function up(PDO $pdo): void
    {
        Schema::create('history_clinical_signs', function (Blueprint $table) {
            $table->id();

            $table->foreignId('patient_id');

            $table->string('parameter');
            $table->string('assessment');

            $table->timestamps();
            $table->softDeletes();

            $table->foreign('patient_id')
                ->references('id')->on('users')
                ->onDelete('RESTRICT');
        });
    }

    public function down(PDO $pdo): void
    {
        Schema::dropIfExists('history_clinical_signs');
    }
}
