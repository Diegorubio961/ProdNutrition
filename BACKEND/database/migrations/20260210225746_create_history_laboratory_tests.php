<?php

use Core\Schema;
use Core\Blueprint;

class CreateHistoryLaboratoryTests
{
    public function up(PDO $pdo): void
    {
        Schema::create('history_laboratory_tests', function (Blueprint $table) {
            $table->id();

            $table->foreignId('patient_id');

            $table->string('indicator_laboratory');
            $table->decimal('value');
            $table->string('unit_laboratory');
            $table->string('interpretation');

            $table->timestamps();
            $table->softDeletes();

            $table->foreign('patient_id')
                ->references('id')->on('users')
                ->onDelete('RESTRICT');
        });
    }

    public function down(PDO $pdo): void
    {
        Schema::dropIfExists('history_laboratory_tests');
    }
}
