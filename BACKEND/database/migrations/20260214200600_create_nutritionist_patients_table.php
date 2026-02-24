<?php

use Core\Schema;
use Core\Blueprint;

class CreateNutritionistPatientsTable
{
    /**
     * Run the migrations.
     */
    public function up(PDO $pdo): void
    {
        Schema::create('nutritionist_patients', function (Blueprint $table) {
            $table->id();

            $table->foreignId('nutritionist_id');
            $table->foreignId('patient_id');

            $table->timestamp('start_at');
            $table->timestamp('end_at'); // nullable by default in Blueprint implementation
            
            $table->string('status', 50); // handled in app logic

            $table->timestamps();
            $table->softDeletes();

            // Foreign Keys
            $table->foreign('nutritionist_id')
                  ->references('id')->on('users')
                  ->onDelete('RESTRICT');

            $table->foreign('patient_id')
                  ->references('id')->on('users')
                  ->onDelete('RESTRICT');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(PDO $pdo): void
    {
        Schema::dropIfExists('nutritionist_patients');
    }
}
