<?php

use Core\Schema;
use Core\Blueprint;

class CreateMeasureAdditionalVariablesTable
{
    public function up(PDO $pdo): void
    {
        Schema::create('measure_additional_variables', function (Blueprint $table) {
            $table->id();

            $table->foreignId('patient_id');

            // Hardcoded variables from ecuaciones.php
            $table->decimal('ideal_fat_percentage', 8, 2); // porcentaje_grasa_adecuado
            $table->decimal('ideal_fat_percentage_jyp', 8, 2); // porcentaje_grasa_adecuado_jyp
            $table->decimal('ideal_fat_percentage_durning', 8, 2); // porcentaje_grasa_adecuado_dur
            $table->decimal('height_age_sd', 8, 2); // T_E
            $table->decimal('bmi_age_sd', 8, 2); // imc_e
            $table->decimal('growth_remaining_cm', 8, 2); // falta_cm

            $table->timestamps();
            $table->softDeletes();

            $table->foreign('patient_id')
                ->references('id')->on('users')
                ->onDelete('RESTRICT');
        });
    }

    public function down(PDO $pdo): void
    {
        Schema::dropIfExists('measure_additional_variables');
    }
}
