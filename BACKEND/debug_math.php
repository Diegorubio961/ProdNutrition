<?php

// Hardcoded values from MeasureSeeder
$sexo = 'F';
$peso_kg = 58.2;
$talla_cm = 163.8;
$talla_sentado_corregida_cm = -43.0; // From Seeder
$perimetro_cintura = 67.2;
$pliegue_abdominal = 15.0;
$diametro_torax_transverso = 0;
$diametro_torax_anteroposterior = 0;

// Logic from MeasureCalculationsController / ecuaciones.php
$perimetro_cintura_corregido = $perimetro_cintura - ($pliegue_abdominal * 0.3141);
$suma_torax = $diametro_torax_transverso + $diametro_torax_anteroposterior + $perimetro_cintura_corregido;

$scale_factor_sentado = ($talla_sentado_corregida_cm != 0) ? (89.92 / $talla_sentado_corregida_cm) : 0;
// Note: In PHP, division happens before subtraction if not parenthesized differently.
// (($suma_torax * $scale_factor_sentado) - 109.35)
$score_z_residual = ($scale_factor_sentado != 0) ? (($suma_torax * $scale_factor_sentado) - 109.35) / 7.08 : 0;

$masa_residual_kg = ($scale_factor_sentado != 0) ? (($score_z_residual * 1.24) + 6.1) / ($scale_factor_sentado ** 3) : 0; // Using ** 3

echo "Inputs:\n";
echo "Suam Torax: $suma_torax\n";
echo "Talla Sentado Corregida: $talla_sentado_corregida_cm\n";
echo "Scale Factor: $scale_factor_sentado\n";
echo "Score Z Residual: $score_z_residual\n";
echo "Masa Residual KG: $masa_residual_kg\n";
