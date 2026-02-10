<?php

use Core\Schema;
use Core\Blueprint;

class CreateAppointmentsTable
{
    /**
     * Run the migrations.
     */
    public function up(PDO $pdo): void
    {
        Schema::create('appointments', function (Blueprint $table) {
            $table->id(); // BIGINT AUTO_INCREMENT

            $table->foreignId('patient_id');
            $table->foreignId('nutritionist_id');

            $table->string('visit_type', 100, false, true);
            $table->timestamp('date');
            $table->integer('duration_minutes', false, true);

            $table->string('additional_notes', 255, false, true);
            $table->string('reminder_method', 100, false, true);
            $table->string('repeat_visit', 50, false, true);
            $table->string('status', 50, false, true);

            // These already create the last columns by default (created_at, updated_at, deleted_at)
            $table->timestamps();
            $table->softDeletes();

            // Foreign Keys
            $table->foreign('patient_id')
                  ->references('id')->on('users')
                  ->onDelete('RESTRICT');

            $table->foreign('nutritionist_id')
                  ->references('id')->on('users')
                  ->onDelete('RESTRICT');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(PDO $pdo): void
    {
        Schema::dropIfExists('appointments');
    }
}
