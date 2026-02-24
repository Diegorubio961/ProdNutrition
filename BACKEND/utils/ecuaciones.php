<?php

# -------------------------------+ DATOS DE ENTRADA +------------------------------- #
# ----------------------------------------------+ PESTAÑA PACIENTES INFORMACION GENERAL +---------------------------------------------- #

$fecha = "15/06/2025";  // EL DATO SE TRAE DE LA HISTORIA CLINICA 
$fecha_nacimiento = "21/04/1998";
$nombre_paciente = "Camila"; // EL DATO SE TRAE DE LA HISTORIA CLINICA
$apellido_paciente = "Álvares Giraldo"; // EL DATO SE TRAE DE LA HISTORIA CLINICA
$ocupacion_deporte = "Gym"; // EL DATO SE TRAE DE LA HISTORIA CLINICA
$categoria_modalidad_especialidad = "Adulto"; // SE INGRESA MANUALMENTE
$antropometria = "Si"; // SE INGRESA MANUALMENTE
$control = ""; // SE INGRESA MANUALMENTE
$edad = 27; // EL DATO SE TRAE DE LA HISTORIA CLINICA
$sexo = "F"; // EL DATO SE TRAE DE LOS DATOS DEL PACIENTE
$etnia = "B"; // SE INGRESA MANUALMENTE SEGÚN TABLAS
$lineadeportiva = "";

// Medidas básicas
$peso_kg = 58.2; // SE INGRESA MANUALMENTE EN KG
$talla_cm = 163.8; // SE INGRESA MANUALMENTE EN CM
$talla_sentado_cm = 0; // SE INGRESA MANUALMENTE EN CM
$altura_banco_cm = 43.0; // SE INGRESA MANUALMENTE EN CM
$talla_sentado_corregida_cm = $talla_sentado_cm - $altura_banco_cm; // SE CALCULA AUTOMATICAMENTE EN CM RESTANDO ALTURA SENTADO - ALTURA BANCO
// echo "Talla sentado corregida: " . $talla_sentado_corregida_cm . PHP_EOL;
$envergadura_cm = 164; // SE INGRESA MANUALMENTE EN CM

// Pliegues (mm)
$pliegue_triceps = 9.0; // SE INGRESA MANUALMENTE EN MM
$pliegue_subescapular = 14.0; // SE INGRESA MANUALMENTE EN MM|
$pliegue_biceps = 8.0; // SE INGRESA MANUALMENTE EN MM
$pliegue_pectoral = 0; // SE INGRESA MANUALMENTE EN MM
$pliegue_axilar = 0; // SE INGRESA MANUALMENTE EN MM
$pliegue_suprailiaco = 11.0; // SE INGRESA MANUALMENTE EN MM
$pliegue_supraespinal = 7.0; // SE INGRESA MANUALMENTE EN MM
$pliegue_abdominal = 15.0; // SE INGRESA MANUALMENTE EN MM|
$pliegue_muslo = 34.5; // SE INGRESA MANUALMENTE EN MM
$pliegue_pierna = 17.0; // SE INGRESA MANUALMENTE EN MM

// Perímetros (cm)
$perimetro_cabeza = 0; // SE INGRESA MANUALMENTE EN CM
$perimetro_cuello = 0; // SE INGRESA MANUALMENTE EN CM
$perimetro_brazo_relajado = 23.8; // SE INGRESA MANUALMENTE EN CM
$perimetro_brazo_tenso = 24.1; // SE INGRESA MANUALMENTE EN CM
$perimetro_antebrazo = 0; // SE INGRESA MANUALMENTE EN CM
$perimetro_muneca = 0; // SE INGRESA MANUALMENTE EN CM
$perimetro_mesoesternal = 0; // SE INGRESA MANUALMENTE EN CM
$perimetro_cintura = 67.2; // SE INGRESA MANUALMENTE EN CM
$perimetro_abdominal = 0; // SE INGRESA MANUALMENTE EN CM
$perimetro_cadera = 98.5; // SE INGRESA MANUALMENTE EN CM
$perimetro_muslo_maximo = 0; // SE INGRESA MANUALMENTE EN CM
$perimetro_muslo_medio = 51.0; // SE INGRESA MANUALMENTE EN CM
$perimetro_pantorrilla_maxima = 34.5; // SE INGRESA MANUALMENTE EN CM
$perimetro_tobillo_minimo = 0; // SE INGRESA MANUALMENTE EN CM

// Longitudes (cm)
$longitud_acromial_radial = 0; // SE INGRESA MANUALMENTE EN CM
$longitud_radial_estiloidea = 0; // SE INGRESA MANUALMENTE EN CM
$longitud_medial_estiloidea_dactilar = 0; // SE INGRESA MANUALMENTE EN CM
$longitud_ileoespinal = 0; // SE INGRESA MANUALMENTE EN CM
$longitud_trocanterea = 0; // SE INGRESA MANUALMENTE EN CM
$longitud_trocanterea_tibial_lateral; // SE INGRESA MANUALMENTE EN CM
$longitud_tibial_lateral; // SE INGRESA MANUALMENTE EN CM
$longitud_tibial_medial_maleolar_medial; // SE INGRESA MANUALMENTE EN CM
$longitud_pie; // SE INGRESA MANUALMENTE EN CM

// Diámetros (cm)
$diametro_biacromial = 0;  // SE INGRESA MANUALMENTE EN CM|
$diametro_bilieocrestal = 0; // SE INGRESA MANUALMENTE EN CM
$diametro_anteroposterior_abdominal = 0; // SE INGRESA MANUALMENTE EN CM
$diametro_torax_transverso = 0; // SE INGRESA MANUALMENTE EN CM
$diametro_torax_anteroposterior = 0; // SE INGRESA MANUALMENTE EN CM
$diametro_humero_biepicondilar = 6.0; // SE INGRESA MANUALMENTE EN CM
$diametro_muneca_biestiloidea = 4.8; // SE INGRESA MANUALMENTE EN CM
$diametro_mano = 0; // SE INGRESA MANUALMENTE EN CM
$diametro_femoral_biepicondilar = 8.8; // SE INGRESA MANUALMENTE EN CM
$diametro_tobillo_bimaleolar = 0; // SE INGRESA MANUALMENTE EN CM
$diametro_pie = 0; // SE INGRESA MANUALMENTE EN CM



# ------------------------------------------------------+ ECUACIONES Y CALCULOS +------------------------------------------------------- #
# ------------------------------------------------------+ IAKS +------------------------------------------------------- #

// AKS HOMBRES
$porcentaje_grasa_pyb_hombres = round((2.745 + (0.0008 * $pliegue_triceps) + (0.002 * $pliegue_subescapular) + (0.637 * $pliegue_suprailiaco) + (0.809 * $pliegue_biceps)), 2);
$kg_grasa_pyb = round((($porcentaje_grasa_pyb_hombres * $peso_kg) / 100), 2);
$mca_pyb = round(($peso_kg - $kg_grasa_pyb), 2);
$aks_hombres = ($porcentaje_grasa_pyb_hombres == " ") ? " " : round((($mca_pyb * 100000) / ($talla_cm ** 3)), 2);
$clasificacion_pyb = ($aks_hombres >= 1.01) ? "Adecuado" : (($aks_hombres < 1.01) ? "Deficiente" : (($aks_hombres > 1.55) ? "Muy Buena" : null));

// IMPRIMIR EN PANTALLA PARA VERIFICAR CÁLCULOS
// echo "\$porcentaje_grasa_pyb_hombres: " . $porcentaje_grasa_pyb_hombres . PHP_EOL;
// echo "\$kg_grasa_pyb: " . $kg_grasa_pyb . PHP_EOL;
// echo "\$mca_pyb: " . $mca_pyb . PHP_EOL;
// echo "\$aks_hombres: " . $aks_hombres . PHP_EOL;
// echo "\$clasificacion_pyb: " . $clasificacion_pyb . PHP_EOL;

# --------------------------------+ +-------------------------------- #

// AKS MUJERES
$densidad_corporal = (1.1581 - (0.072 * log10($pliegue_triceps + $pliegue_subescapular + $pliegue_biceps + $pliegue_suprailiaco)));
$porcentaje_grasa_dyr = (((4.95 / $densidad_corporal) - 4.5) * 100);
$kg_grasa_dyr = (($porcentaje_grasa_dyr * $peso_kg) / 100);
$mca_dyr = ($peso_kg - $kg_grasa_dyr);
$aks_mujeres = ($porcentaje_grasa_dyr == " ") ? " " : (($mca_dyr * 100000) / ($talla_cm ** 3));
$clasificacion_dyr = ($aks_mujeres >= 0.93) ? "Adecuado" : (($aks_mujeres < 0.93) ? "Deficiente" : (($aks_mujeres > 1.24) ? "Muy Buena" : null));

// IMPRIMIR EN PANTALLA PARA VERIFICAR CÁLCULOS
// echo "\$aks_mujeres: " . (($aks_mujeres === " ") ? " " : number_format($aks_mujeres, 2, '.', '')) . PHP_EOL;
// echo "\$mca_dyr: " . number_format($mca_dyr, 2, '.', '') . PHP_EOL;
// echo "\$kg_grasa_dyr: " . number_format($kg_grasa_dyr, 2, '.', '') . PHP_EOL;
// echo "\$porcentaje_grasa_dyr: " . number_format($porcentaje_grasa_dyr, 2, '.', '') . PHP_EOL;
// echo "\$densidad_corporal: " . number_format($densidad_corporal, 2, '.', '') . PHP_EOL;
// echo "\$clasificacion_dyr: " . $clasificacion_dyr . PHP_EOL;

# --------------------------------+ +-------------------------------- #

// ÍNDICES
$indice_masa_magra_imm_kg_m2 = round(($mca_pyb / (($talla_cm / 100) ** 2)), 1);
$interpretacion_fat_free_mass_index = ($indice_masa_magra_imm_kg_m2 == " ")
    ? " "
    : (($sexo == "M")
        ? (($indice_masa_magra_imm_kg_m2 < 16.7) ? "BAJO" : (($indice_masa_magra_imm_kg_m2 < 19.8) ? "ADECUADO" : (($indice_masa_magra_imm_kg_m2 > 19.8) ? "MUY BUENO" : null)))
        : (($sexo == "F")
            ? (($indice_masa_magra_imm_kg_m2 < 14.6) ? "BAJO" : (($indice_masa_magra_imm_kg_m2 < 16.8) ? "ADECUADO" : (($indice_masa_magra_imm_kg_m2 > 16.8) ? "MUY BUENO" : null)))
            : null
        )
    );

// IMPRIMIR EN PANTALLA PARA VERIFICAR CÁLCULOS
// echo "\$indice_masa_magra_imm_kg_m2: " . $indice_masa_magra_imm_kg_m2 . PHP_EOL;
// echo "\$interpretacion_fat_free_mass_index: " . $interpretacion_fat_free_mass_index . PHP_EOL;

# SOLO SE QUIERE MOSTRAR EN PANTALLA LAS ISGUIENTES COLUMNAS (AKS_HOMBRES - CLASIFICACION, AKS_MUJERES - CLASIFICACION, INDICE DE MASA MAGRA y INTERPRETATION OF THE FAT FREE MASS)
# RECORDAR QUE EL SISTEMA DEBE IDENTIFICAR QUE SI ES MUJER MOSTRARIA SOLO (AKS_HOMBRES - CLASIFICACION,  INDICE DE MASA MAGRA y INTERPRETATION OF THE FAT FREE MASS) y viceverso si es mujer




# ------------------------------------------------------+ YUHASZ +------------------------------------------------------- #


$imc = ($talla_cm == 0) ? " " : (($peso_kg == " ") ? " " : ($peso_kg / (($talla_cm / 100) ** 2)));
$suma_yuhasz = ($pliegue_triceps == 0) ? " " : ($pliegue_triceps + $pliegue_subescapular + $pliegue_supraespinal + $pliegue_abdominal + $pliegue_muslo + $pliegue_pierna);
$suma_yuhasz_mas_biceps_7_pliegues = ($pliegue_triceps == 0) ? " " : (($pliegue_triceps + $pliegue_subescapular + $pliegue_biceps) + ($pliegue_supraespinal + $pliegue_abdominal + $pliegue_muslo + $pliegue_pierna));
$porcentaje_grasa_mujeres = ($pliegue_triceps == 0) ? " " : (($suma_yuhasz == " ") ? " " : (($suma_yuhasz * 0.1548) + 3.5803));
$porcentaje_grasa_hombres = ($pliegue_triceps == 0) ? " " : (($suma_yuhasz == " ") ? " " : (($suma_yuhasz * 0.1051) + 2.585));
$porcentaje_grasa_final = ($pliegue_triceps == 0)
    ? " "
    : (($sexo == "M")
        ? $porcentaje_grasa_hombres
        : (($sexo == "F")
            ? $porcentaje_grasa_mujeres
            : null
        )
    );
$grasa_kg = ($pliegue_triceps == 0) ? " " : (($peso_kg == " " || $porcentaje_grasa_final == " ") ? " " : (($peso_kg * $porcentaje_grasa_final) / 100));
$mlg_kg = ($pliegue_triceps == 0) ? " " : (($peso_kg == " " || $grasa_kg == " ") ? " " : ($peso_kg - $grasa_kg));
$iaks_mujeres = $aks_mujeres; //OJO estos datos dependen del archivo de iaks
$iaks_hombres = $aks_hombres; //OJO estos datos dependen del archivo de iaks
$porcentaje_grasa_adecuado = 0; // SE INGRESA MANUALMENTE SEGÚN TABLAS
$mca = ($mlg_kg == " " || $porcentaje_grasa_adecuado == " ") ? " " : ($mlg_kg / (1 - ($porcentaje_grasa_adecuado / 100)));
$kg_a_perder = ($peso_kg == " " || $mca == " ") ? " " : ($peso_kg - $mca);
$clasificacion_yuhasz ; // FALTAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAa

// IMPRIMIR EN PANTALLA PARA VERIFICAR CÁLCULOS
// echo "\$imc: " . (($imc === " ") ? " " : number_format($imc, 1, '.', '')) . PHP_EOL;
// echo "\$mlg_kg: " . (($mlg_kg === " ") ? " " : number_format($mlg_kg, 1, '.', '')) . PHP_EOL;
// echo "\$grasa_kg: " . (($grasa_kg === " ") ? " " : number_format($grasa_kg, 1, '.', '')) . PHP_EOL;
// echo "\$porcentaje_grasa_final: " . (($porcentaje_grasa_final === " ") ? " " : number_format($porcentaje_grasa_final, 1, '.', '')) . PHP_EOL;
// echo "\$porcentaje_grasa_mujeres: " . (($porcentaje_grasa_mujeres === " ") ? " " : number_format($porcentaje_grasa_mujeres, 1, '.', '')) . PHP_EOL;
// echo "\$iaks_mujeres: " . (($iaks_mujeres === " ") ? " " : number_format($iaks_mujeres, 2, '.', '')) . PHP_EOL;
// echo "\$porcentaje_grasa_hombres: " . (($porcentaje_grasa_hombres === " ") ? " " : number_format($porcentaje_grasa_hombres, 1, '.', '')) . PHP_EOL;
// echo "\$iaks_hombres: " . (($iaks_hombres === " ") ? " " : number_format($iaks_hombres, 2, '.', '')) . PHP_EOL;
// echo "\$porcentaje_grasa_adecuado: " . (($porcentaje_grasa_adecuado === " ") ? " " : number_format($porcentaje_grasa_adecuado, 1, '.', '')) . PHP_EOL;
// echo "\$mca: " . (($mca === " ") ? " " : number_format($mca, 1, '.', '')) . PHP_EOL;
// echo "\$kg_a_perder: " . (($kg_a_perder === " ") ? " " : number_format($kg_a_perder, 1, '.', '')) . PHP_EOL;
// echo "\$suma_yuhasz: " . (($suma_yuhasz === " ") ? " " : number_format($suma_yuhasz, 1, '.', '')) . PHP_EOL;
// echo "\$suma_yuhasz_mas_biceps_7_pliegues: " . (($suma_yuhasz_mas_biceps_7_pliegues === " ") ? " " : number_format($suma_yuhasz_mas_biceps_7_pliegues, 1, '.', '')) . PHP_EOL;


# !!SE QUIERE MOSTRAR EN PANTALLA TODOS LOS INDICADORES!!




# ------------------------------------------------------+ DURNING Y RAMAHAN +------------------------------------------------------- #
// $sumatoria_4_pliegues = $pliegue_triceps + $pliegue_biceps + $pliegue_subescapular + $pliegue_suprailiaco;
// $densidad_hombres_durning = ((1.1533-0.0643*(log10($sumatoria_4_pliegues))));
// $porcentaje_grasa_hombres_durning = (((4.95/$densidad_hombres_durning) - 4.5) * 100); ;
// $grasa_hombres_durning_kg = ($porcentaje_grasa_hombres_durning * $peso_kg )/100;
// $mlg_hombres_durning_kg = $peso_kg - $grasa_hombres_durning_kg;
// $imc_durning = ($talla_cm==0) ? " " : ($peso_kg / (($talla_cm / 100) ** 2));
// $iaks_hombres = $aks_hombres;
// $porcentaje_grasa_adecuado_durning = 0; // SE INGRESA MANUALMENTE
// $mca_durning = $mlg_hombres_durning_kg / (1-$porcentaje_grasa_adecuado_durning/100);
// $kg_a_perder_durning = $peso_kg - $mca_durning;

// $densidad_mujeres_durning = (1.1369 - 0.0598 * log10($sumatoria_4_pliegues));
// $porcentaje_grasa_mujeres_durning = (((4.95 / $densidad_mujeres_durning) - 4.5) * 100);
// $grasa_mujeres_durning_kg = ($porcentaje_grasa_mujeres_durning * $peso_kg) / 100;
// $mlg_mujeres_kg_durning = $peso_kg - $grasa_mujeres_durning_kg;
// $iaks_mujeres_durning = $aks_mujeres;
// $mca_mujeres_durning = $mlg_mujeres_kg_durning / (1-$porcentaje_grasa_adecuado_durning/100);
// $kg_a_perder = $peso_kg - $mca_mujeres_durning;
// $porcentaje_grasa_final_durning = ($sexo == "F") ? $porcentaje_grasa_mujeres_durning : (($sexo == "M") ? $porcentaje_grasa_hombres_durning : null);


// echo "imc_durning: " . $imc_durning . PHP_EOL;
// echo "mlg_hombres_durning_kg: " . $mlg_hombres_durning_kg . PHP_EOL;
// echo "grasa_hombres_durning_kg: " . $grasa_hombres_durning_kg . PHP_EOL;
// echo "porcentaje_grasa_hombres_durning: " . $porcentaje_grasa_hombres_durning . PHP_EOL;
// echo "densidad_hombres_durning: " . $densidad_hombres_durning . PHP_EOL;
// echo "sumatoria_4_pliegues: " . $sumatoria_4_pliegues . PHP_EOL;
// echo "porcentaje_grasa_adecuado_durning: " . $porcentaje_grasa_adecuado_durning . PHP_EOL;
// echo "mca_durning: " . $mca_durning . PHP_EOL;
// echo "kg_a_perder_durning: " . $kg_a_perder_durning . PHP_EOL;

// echo "mlg_mujeres_kg_durning: " . $mlg_mujeres_kg_durning . PHP_EOL;
// echo "grasa_mujeres_durning_kg: " . $grasa_mujeres_durning_kg . PHP_EOL;
// echo "porcentaje_grasa_mujeres_durning: " . $porcentaje_grasa_mujeres_durning . PHP_EOL;
// echo "densidad_mujeres_durning: " . $densidad_mujeres_durning . PHP_EOL;
// echo "iaks_mujeres_durning: " . $iaks_mujeres_durning . PHP_EOL;
// echo "mca_mujeres_durning: " . $mca_mujeres_durning . PHP_EOL;
// echo "kg_a_perder: " . $kg_a_perder . PHP_EOL;
// echo "porcentaje_grasa_final_durning: " . $porcentaje_grasa_final_durning . PHP_EOL;


#ESTA PESTAÑA YA NO VA; EVITARLA




# ------------------------------------------------------+ 5 COMPONENTES +------------------------------------------------------- #

$constante_masa_piel = ($sexo == "M") ? 68.308 : (($sexo == "F") ? 73.074 : (($edad < 12) ? 70.691 : ""));
$grosor_piel = ($sexo == "M") ? 2.07 : (($sexo == "F") ? 1.96 : "");
$suma_diametros_masa_osea = ($diametro_biacromial + $diametro_bilieocrestal) + ($diametro_humero_biepicondilar * 2) + ($diametro_femoral_biepicondilar * 2);
$score_z_oseo_cuerpo = (($suma_diametros_masa_osea * (170.18 / $talla_cm)) - 98.88) / 5.33;
$masa_osea_cuerpo_kg = (($score_z_oseo_cuerpo * 1.34) + 6.7) / (170.18 / $talla_cm) ** 3;
$score_z_cabeza = ($perimetro_cabeza - 56) / 1.44;
$masa_osea_cabeza_kg = ($score_z_cabeza * 0.18) + 1.2;
$masa_osea_total_kg = $masa_osea_cabeza_kg + $masa_osea_cuerpo_kg;
$perimetro_brazo_corregido = $perimetro_brazo_relajado - ($pliegue_triceps * 3.141 / 10);
$perimetro_antebrazo = $perimetro_antebrazo;
$perimetro_muslo_corregido = $perimetro_muslo_maximo - ($pliegue_muslo * 3.141 / 10);
$perimetro_pantorrilla_corregido = $perimetro_pantorrilla_maxima - ($pliegue_pierna * 3.141 / 10);
$perimetro_torax_corregido = $perimetro_mesoesternal - ($pliegue_subescapular * 3.141 / 10 );
$suma_perimetros_corregidos = $perimetro_brazo_corregido + $perimetro_antebrazo + $perimetro_muslo_corregido + $perimetro_pantorrilla_corregido + $perimetro_torax_corregido;
$score_z_muscular = (($suma_perimetros_corregidos * (170.18 / $talla_cm) - 207.21) / 13.74);
$masa_muscular_kg = (($score_z_muscular * 5.4) + 24.5) / (170.18 / $talla_cm) ** 3;
$perimetro_cintura_corregido = $perimetro_cintura - ($pliegue_abdominal * 0.3141);
$suma_torax = $diametro_torax_transverso + $diametro_torax_anteroposterior + $perimetro_cintura_corregido;
$score_z_residual = (($suma_torax * (89.92 / $talla_sentado_corregida_cm) - 109.35) / 7.08);
$masa_residual_kg = (($score_z_residual * 1.24) + 6.1) / ((89.92 / $talla_sentado_corregida_cm) ** 3);
$sumatoria_seis_pliegues = $pliegue_triceps + $pliegue_subescapular + $pliegue_supraespinal + $pliegue_abdominal + $pliegue_muslo + $pliegue_pierna;
$score_z_adiposa =(($sumatoria_seis_pliegues * (170.18 / $talla_cm) - 116.41)/34.79);
$masa_adiposa_kg = (($score_z_adiposa * 5.85) + 25.6) / (170.18 / $talla_cm) **3;
$area_superficial = ($constante_masa_piel * $peso_kg ** 0.425 * $talla_cm ** 0.725) / 10000;
$masa_piel_kg = $area_superficial * $grosor_piel * 1.05;
$peso_estructurado_kg = $masa_piel_kg + $masa_adiposa_kg + $masa_muscular_kg + $masa_residual_kg + $masa_osea_total_kg;
$diferencia_pe_peso_bruto = $peso_estructurado_kg - $peso_kg;
$piel_porcentaje = $masa_piel_kg / $peso_estructurado_kg;
$adiposa_porcentaje = $masa_adiposa_kg / $peso_estructurado_kg;
$muscular_porcentaje = $masa_muscular_kg / $peso_estructurado_kg;
$residual_porcentaje = $masa_residual_kg / $peso_estructurado_kg;
$osea_porcentaje = $masa_osea_total_kg / $peso_estructurado_kg;
$ajustes_masa_piel = $diferencia_pe_peso_bruto * $piel_porcentaje;
$ajuste_adiposa = $diferencia_pe_peso_bruto * $adiposa_porcentaje;
$ajustes_masa_muscular = $diferencia_pe_peso_bruto * $muscular_porcentaje;
$ajustes_masa_residual = $diferencia_pe_peso_bruto * $residual_porcentaje;
$ajustes_masa_osea = $diferencia_pe_peso_bruto * $osea_porcentaje;
$total_masa_porcentaje = $piel_porcentaje + $adiposa_porcentaje + $muscular_porcentaje + $residual_porcentaje + $osea_porcentaje;
$fraccion_masa_lipidica_porcentaje = 0.327 + (0.0124 * $adiposa_porcentaje);
$porcentaje_diferencia_peso_estructura = $diferencia_pe_peso_bruto / $peso_kg;
$ajustes_peso_estructurado = $ajustes_masa_piel + $ajuste_adiposa + $ajustes_masa_muscular + $ajustes_masa_residual + $ajustes_masa_osea;
$masa_total_adiposa_kg = $masa_adiposa_kg - $ajuste_adiposa;
$masa_total_muscular_kg = $masa_muscular_kg - $ajustes_masa_muscular;
// $masa_residual_kg = $masa_residual_kg - $ajustes_masa_residual;
$masa_osea_kg = $masa_osea_total_kg - $ajustes_masa_osea;
$total_masa_kg = $masa_piel_kg + $masa_total_adiposa_kg + $masa_total_muscular_kg + $masa_residual_kg + $masa_osea_kg;
$piel_kg = $masa_piel_kg - $ajustes_masa_piel;

// echo "constante_masa_piel: " . $constante_masa_piel . PHP_EOL;
// echo "grosor_piel: " . $grosor_piel . PHP_EOL;
// echo "suma_diametros_masa_osea: " . $suma_diametros_masa_osea . PHP_EOL;
// echo "score_z_oseo_cuerpo: " . $score_z_oseo_cuerpo . PHP_EOL;
// echo "masa_osea_cuerpo_kg: " . $masa_osea_cuerpo_kg . PHP_EOL;
// echo "score_z_cabeza: " . $score_z_cabeza . PHP_EOL;
// echo "masa_osea_cabeza_kg: " . $masa_osea_cabeza_kg . PHP_EOL;
// echo "masa_osea_total_kg: " . $masa_osea_total_kg . PHP_EOL;
// echo "perimetro_brazo_corregido: " . $perimetro_brazo_corregido . PHP_EOL;
// echo "perimetro_antebrazo: " . $perimetro_antebrazo . PHP_EOL;
// echo "perimetro_muslo_corregido: " . $perimetro_muslo_corregido . PHP_EOL;
// echo "perimetro_pantorrilla_corregido: " . $perimetro_pantorrilla_corregido . PHP_EOL;
// echo "perimetro_torax_corregido: " . $perimetro_torax_corregido . PHP_EOL;
// echo "suma_perimetros_corregidos: " . $suma_perimetros_corregidos . PHP_EOL;
// echo "score_z_muscular: " . $score_z_muscular . PHP_EOL;
// echo "masa_muscular_kg: " . $masa_muscular_kg . PHP_EOL;
// echo "perimetro_cintura_corregido: " . $perimetro_cintura_corregido . PHP_EOL;
// echo "suma_torax: " . $suma_torax . PHP_EOL;
// echo "score_z_residual: " . $score_z_residual . PHP_EOL;
// echo "masa_residual_kg: " . $masa_residual_kg . PHP_EOL;
// echo "sumatoria_seis_pliegues: " . $sumatoria_seis_pliegues . PHP_EOL;
// echo "score_z_adiposa: " . $score_z_adiposa . PHP_EOL;
// echo "masa_adiposa_kg: " . $masa_adiposa_kg . PHP_EOL;
// echo "area_superficial: " . $area_superficial . PHP_EOL;
// echo "masa_piel_kg: " . $masa_piel_kg . PHP_EOL;
// echo "peso_estructurado_kg: " . $peso_estructurado_kg . PHP_EOL;
// echo "diferencia_pe_peso_bruto: " . $diferencia_pe_peso_bruto . PHP_EOL;
// echo "piel_porcentaje: " . $piel_porcentaje . PHP_EOL;
// echo "adiposa_porcentaje: " . $adiposa_porcentaje . PHP_EOL;
// echo "muscular_porcentaje: " . $muscular_porcentaje . PHP_EOL;
// echo "residual_porcentaje: " . $residual_porcentaje . PHP_EOL;
// echo "osea_porcentaje: " . $osea_porcentaje . PHP_EOL;
// echo "ajustes_masa_piel: " . $ajustes_masa_piel . PHP_EOL;
// echo "ajuste_adiposa: " . $ajuste_adiposa . PHP_EOL;
// echo "ajustes_masa_muscular: " . $ajustes_masa_muscular . PHP_EOL;
// echo "ajustes_masa_residual: " . $ajustes_masa_residual . PHP_EOL;
// echo "ajustes_masa_osea: " . $ajustes_masa_osea . PHP_EOL;
// echo "total_masa_porcentaje: " . $total_masa_porcentaje . PHP_EOL;
// echo "fraccion_masa_lipidica_porcentaje: " . $fraccion_masa_lipidica_porcentaje . PHP_EOL;
// echo "porcentaje_diferencia_peso_estructura: " . $porcentaje_diferencia_peso_estructura . PHP_EOL;
// echo "ajustes_peso_estructurado: " . $ajustes_peso_estructurado . PHP_EOL;
// echo "masa_total_adiposa_kg: " . $masa_total_adiposa_kg . PHP_EOL;
// echo "masa_total_muscular_kg: " . $masa_total_muscular_kg . PHP_EOL;
// echo "masa_osea_kg: " . $masa_osea_kg . PHP_EOL;
// echo "total_masa_kg: " . $total_masa_kg . PHP_EOL;
// echo "piel_kg: " . $piel_kg . PHP_EOL;

# SE DEBEN MOSTRAR TODAS LAS CELDAS




# ------------------------------------------------------+ J&P +------------------------------------------------------- #

$imc_jyp = ($talla_cm == 0) ? " " : ($peso_kg / (($talla_cm / 100) * ($talla_cm / 100)));
$porcentaje_grasa_mujeres_jyp = ($pliegue_triceps == 0) ? " " : ((4.95 / (1.0994921 - (0.0009929 * ($pliegue_triceps + $pliegue_supraespinal + $pliegue_muslo)) + (0.0000023 * (($pliegue_triceps + $pliegue_supraespinal + $pliegue_muslo) ** 2)) - (0.0001392 * $edad))) - 4.5) * 100;
$porcentaje_grasa_hombres_jyp = ($pliegue_triceps == 0) ? " " : ((4.95 / (1.10938 - (0.0008267 * ($pliegue_pectoral + $pliegue_abdominal + $pliegue_muslo)) + (0.0000016 * (($pliegue_pectoral + $pliegue_abdominal + $pliegue_muslo) ** 2)) - (0.0002574 * $edad))) - 4.5) * 100;
$porcentaje_grasa_final_jyp = ($pliegue_triceps == 0) ? " " : ($sexo == "M" ? $porcentaje_grasa_hombres_jyp : $porcentaje_grasa_mujeres_jyp);
$grasa_kg_jyp = ($pliegue_triceps == 0) ? " " : ($peso_kg * $porcentaje_grasa_final_jyp) / 100;
$mlg_jyp_kg = ($porcentaje_grasa_final_jyp == " ") ? " " : ($peso_kg - $grasa_kg_jyp);
$porcentaje_grasa_adecuado_jyp = 0;
$mca_jyp = $mlg_jyp_kg / (1 - ($porcentaje_grasa_adecuado_jyp / 100));
$kg_a_perder_jyp = $peso_kg - $mca_jyp;
$clasificacion_jyp = ($pliegue_triceps == 0) ? " " : (($sexo == "M") ? (($porcentaje_grasa_final_jyp < 15) ? "Bajo" : (($porcentaje_grasa_final_jyp < 20) ? "Adecuado" : (($porcentaje_grasa_final_jyp > 19.99) ? "Exceso" : ""))) : (($sexo == "F") ? (($porcentaje_grasa_final_jyp < 22) ? "Bajo" : (($porcentaje_grasa_final_jyp < 28) ? "Adecuado" : (($porcentaje_grasa_final_jyp > 27.99) ? "Exceso" : ""))) : ""));
$sumatoria_7_pliegues_jyp = ($pliegue_triceps == 0) ? " " : (($pliegue_triceps + $pliegue_subescapular + $pliegue_biceps) + ($pliegue_supraespinal + $pliegue_abdominal + $pliegue_muslo + $pliegue_pierna));


// echo "imc_jyp: " . $imc_jyp . PHP_EOL;
// echo "porcentaje_grasa_mujeres_jyp: " . $porcentaje_grasa_mujeres_jyp . PHP_EOL;
// echo "porcentaje_grasa_hombres_jyp: " . $porcentaje_grasa_hombres_jyp . PHP_EOL;
// echo "porcentaje_grasa_final_jyp: " . $porcentaje_grasa_final_jyp . PHP_EOL;
// echo "grasa_kg_jyp: " . $grasa_kg_jyp . PHP_EOL;
// echo "mlg_jyp_kg: " . $mlg_jyp_kg . PHP_EOL;
// echo "mca_jyp: " . $mca_jyp . PHP_EOL;
// echo "kg_a_perder_jyp: " . $kg_a_perder_jyp . PHP_EOL;
// echo "clasificacion_jyp: " . $clasificacion_jyp . PHP_EOL;
// echo "sumatoria_7_pliegues_jyp: " . $sumatoria_7_pliegues_jyp . PHP_EOL;

# TODOS MENOS (IMC)




# ------------------------------------------------------+ SLAUGHTER +------------------------------------------------------- #

$sumatoria_pliegues_sla = $pliegue_triceps + $pliegue_pierna;
$porcentaje_grasa_mujeres_sla = ((0.61 * $sumatoria_pliegues_sla) + 5.1);
$porcentaje_grasa_hombres_sla = ((0.735 * $sumatoria_pliegues_sla) + 1);
$porcentaje_grasa_final_sla = ($sexo == "F") ? $porcentaje_grasa_mujeres_sla : (($sexo == "M") ? $porcentaje_grasa_hombres_sla : " ");
$grasa_kg_sla = ($porcentaje_grasa_final_sla == " " ? " " : ($peso_kg * $porcentaje_grasa_final_sla / 100));
$mlg_sla = $porcentaje_grasa_final_sla == " " ? " " : ($peso_kg - $grasa_kg_sla);
$imc_sla = $peso_kg / (($talla_cm/100)*($talla_cm/100));
$clasificacion_sla = ($sumatoria_pliegues_sla === " ") ? " " : (($sexo === "M") ? (($porcentaje_grasa_final_sla < 6) ? "Muy Bajo" : (($porcentaje_grasa_final_sla < 11) ? "Bajo" : (($porcentaje_grasa_final_sla < 20) ? "Óptimo" : (($porcentaje_grasa_final_sla < 25) ? "Moderadamente Alto" : (($porcentaje_grasa_final_sla < 31) ? "Alto" : (($porcentaje_grasa_final_sla > 31) ? "Muy Alto" : "")))))) : (($sexo === "F") ? (($porcentaje_grasa_final_sla < 11) ? "Muy Bajo" : (($porcentaje_grasa_final_sla < 15) ? "Bajo" : (($porcentaje_grasa_final_sla < 25) ? "Óptimo" : (($porcentaje_grasa_final_sla < 30) ? "Moderadamente Alto" : (($porcentaje_grasa_final_sla < 35.5) ? "Alto" : (($porcentaje_grasa_final_sla > 35.5) ? "Muy Alto" : "")))))) : " "));


// echo "imc_sla: " . $imc_sla . PHP_EOL;
// echo "mlg_sla: " . $mlg_sla . PHP_EOL;
// echo "grasa_kg_sla: " . $grasa_kg_sla . PHP_EOL;
// echo "porcentaje_grasa_final_sla: " . $porcentaje_grasa_final_sla . PHP_EOL;
// echo "clasificacion_sla: " . $clasificacion_sla . PHP_EOL;
// echo "porcentaje_grasa_mujeres_sla: " . $porcentaje_grasa_mujeres_sla . PHP_EOL;
// echo "porcentaje_grasa_hombres_sla: " . $porcentaje_grasa_hombres_sla . PHP_EOL;
// echo "sumatoria_pliegues_sla: " . $sumatoria_pliegues_sla . PHP_EOL;

# TODOS MENOS IMC




# ------------------------------------------------------+ DURNING +------------------------------------------------------- #

$sumatoria_pliegues_dur = $pliegue_triceps + $pliegue_biceps + $pliegue_subescapular + $pliegue_suprailiaco;
$sumatoria_7_pliegues_dur = ($pliegue_triceps == 0) ? " " : $pliegue_triceps + $pliegue_subescapular + $pliegue_biceps + $pliegue_supraespinal + $pliegue_abdominal + $pliegue_muslo + $pliegue_pierna;

# PROCENTAJE GRASA HOMBRES / EDAD
$diesiceis_diecinueve_hombre = ($pliegue_triceps == 0) ? " " : ((4.95 / (1.162 - (0.063 * log10($sumatoria_pliegues_dur)))) - 4.5) * 100;
$veinte_veintinueve_hombre = ($pliegue_triceps == 0) ? " " : ((4.95 / (1.1631 - (0.0632 * log10($sumatoria_pliegues_dur)))) - 4.5) * 100;
$treinta_treintanueve_hombre = ($pliegue_triceps == 0) ? " " : ((4.95 / (1.1422 - (0.0544 * log10($sumatoria_pliegues_dur)))) - 4.5) * 100;
$cuarenta_cuarentanueve_hombre = ($pliegue_triceps == 0) ? " " : ((4.95 / (1.162 - (0.07 * log10($sumatoria_pliegues_dur)))) - 4.5) * 100;
$mayor_cincuenta_hombre = ($pliegue_triceps == 0) ? " " : ((4.95 / (1.1715 - (0.0779 * log10($sumatoria_pliegues_dur)))) - 4.5) * 100;

# PROCENTAJE GRASA MUJERES / EDAD
$diesiceis_diecinueve_mujer = ($pliegue_triceps == 0) ? " " : ((4.95 / (1.1549 - (0.0678 * log10($sumatoria_pliegues_dur)))) - 4.5) * 100;
$veinte_veintinueve_mujer = ($pliegue_triceps == 0) ? " " : ((4.95 / (1.1599 - (0.0717 * log10($sumatoria_pliegues_dur)))) - 4.5) * 100;
$treinta_treintanueve_mujer = ($pliegue_triceps == 0) ? " " : ((4.95 / (1.1423 - (0.0632 * log10($sumatoria_pliegues_dur)))) - 4.5) * 100;
$cuarenta_cuarentanueve_mujer = ($pliegue_triceps == 0) ? " " : ((4.95 / (1.1333 - (0.0612 * log10($sumatoria_pliegues_dur)))) - 4.5) * 100;
$mayor_cincuenta_mujer = ($pliegue_triceps == 0) ? " " : ((4.95 / (1.1339 - (0.0645 * log10($sumatoria_pliegues_dur)))) - 4.5) * 100;

$porcentaje_grasa_final_dur = ($pliegue_triceps == 0) ? " " : (($sexo == "M") ? (($edad < 20) ? $diesiceis_diecinueve_hombre : (($edad < 30) ? $veinte_veintinueve_hombre : (($edad < 40) ? $treinta_treintanueve_hombre : (($edad < 50) ? $cuarenta_cuarentanueve_hombre : (($edad >= 50) ? $mayor_cincuenta_hombre : " "))))) : (($sexo == "F") ? (($edad < 20) ? $diesiceis_diecinueve_mujer : (($edad < 30) ? $veinte_veintinueve_mujer : (($edad < 40) ? $treinta_treintanueve_mujer : (($edad < 50) ? $cuarenta_cuarentanueve_mujer : (($edad >= 50) ? $mayor_cincuenta_mujer : " "))))) : " "));
$grasa_kg_dur = ($pliegue_triceps == 0) ? " " : $peso_kg * $porcentaje_grasa_final_dur / 100;
$mlg_dur = $porcentaje_grasa_final_dur == " " ? " " : ($peso_kg - $grasa_kg_dur);
$porcentaje_grasa_adecuado_dur = 0;
$mca_dur = $mlg_dur / (1 - $porcentaje_grasa_adecuado_dur / 100);
$kg_perder_dur = $peso_kg - $mca_dur; 
$imc_dur = ($talla_cm == 0) ? " " : $peso_kg / (($talla_cm/100) * ($talla_cm/100));
$clasificacion_dur = ($porcentaje_grasa_final_dur === " ") ? " " : (($sexo === "M") ? (($porcentaje_grasa_final_dur < 15) ? "Delgado" : (($porcentaje_grasa_final_dur < 22) ? "Adecuado" : (($porcentaje_grasa_final_dur < 28) ? "Exceso" : (($porcentaje_grasa_final_dur > 27.99) ? "Obeso" : "")))) : (($sexo === "F") ? (($porcentaje_grasa_final_dur < 20) ? "Delgado" : (($porcentaje_grasa_final_dur < 27) ? "Adecuado" : (($porcentaje_grasa_final_dur < 34) ? "Exceso" : (($porcentaje_grasa_final_dur > 33.99) ? "Obeso" : "")))) : " "));

// echo "clasificacion" . $clasificacion_dur . PHP_EOL;
// echo "sumatoria_pliegues_dur: " . $sumatoria_pliegues_dur . PHP_EOL;
// echo "sumatoria_7_pliegues_dur: " . $sumatoria_7_pliegues_dur . PHP_EOL;

// echo "diesiceis_diecinueve_hombre: " . $diesiceis_diecinueve_hombre . PHP_EOL;
// echo "veinte_veintinueve_hombre: " . $veinte_veintinueve_hombre . PHP_EOL;
// echo "treinta_treintanueve_hombre: " . $treinta_treintanueve_hombre . PHP_EOL;
// echo "cuarenta_cuarentanueve_hombre: " . $cuarenta_cuarentanueve_hombre . PHP_EOL;
// echo "mayor_cincuenta_hombre: " . $mayor_cincuenta_hombre . PHP_EOL;

// echo "diesiceis_diecinueve_mujer: " . $diesiceis_diecinueve_mujer . PHP_EOL;
// echo "veinte_veintinueve_mujer: " . $veinte_veintinueve_mujer . PHP_EOL;
// echo "treinta_treintanueve_mujer: " . $treinta_treintanueve_mujer . PHP_EOL;
// echo "cuarenta_cuarentanueve_mujer: " . $cuarenta_cuarentanueve_mujer . PHP_EOL;
// echo "mayor_cincuenta_mujer: " . $mayor_cincuenta_mujer . PHP_EOL;

// echo "porcentaje_grasa_final_dur: " . $porcentaje_grasa_final_dur . PHP_EOL;
// echo "grasa_kg_dur: " . $grasa_kg_dur . PHP_EOL;
// echo "mlg_dur: " . $mlg_dur . PHP_EOL;
// echo "porcentaje_grasa_adecuado_dur: " . $porcentaje_grasa_adecuado_dur . PHP_EOL;
// echo "mca_dur: " . $mca_dur . PHP_EOL;
// echo "kg_perder_dur: " . $kg_perder_dur . PHP_EOL;
// echo "imc_dur: " . $imc_dur . PHP_EOL;

# TODOS MENOS IMC




# ------------------------------------------------------+ SOMATOTIPO +------------------------------------------------------- #

# INDICES
$s6pi = $pliegue_triceps + $pliegue_subescapular + $pliegue_supraespinal + $pliegue_abdominal + $pliegue_muslo + $pliegue_pierna;
$imc_somatotipo = $peso_kg / ($talla_cm * $talla_cm * 0.0001);
$s3plieg = ($pliegue_triceps + $pliegue_subescapular + $pliegue_supraespinal) * 170.18 / $talla_cm;
$hwr = $talla_cm / $peso_kg ** 0.3333;

# SOMATOTIPO
$endo = -0.7182 + (0.1451 * $s3plieg) - (0.00068 * ($s3plieg ** 2)) + (0.0000014 * ($s3plieg ** 3));
$meso = (0.858 * $diametro_humero_biepicondilar) + (0.601 * $diametro_femoral_biepicondilar) + (0.188 * ($perimetro_brazo_tenso - ($pliegue_triceps / 10))) + (0.161 * ($perimetro_pantorrilla_maxima - ($pliegue_pierna / 10))) - ($talla_cm * 0.131) + 4.5;
$ecto = ($hwr >= 40.75) ? ((0.732 * $hwr) - 28.58) : (($hwr > 38.25) ? ((0.463 * $hwr) - 17.63) : 0.1);

# COORDENADAS
$x = $ecto - $endo;
$y = 2 * $meso - ($endo + $ecto);

// echo "s6pi: " . $s6pi . PHP_EOL;
// echo "imc: " . $imc_somatotipo . PHP_EOL;
// echo "s3plieg: " . $s3plieg . PHP_EOL;
// echo "hwr: " . $hwr . PHP_EOL;
// echo "meso: " . $meso . PHP_EOL;
// echo "ecto: " . $ecto . PHP_EOL;
// echo "x: " . $x . PHP_EOL;
// echo "y: " . $y . PHP_EOL;

# SE DEBEN MOSTRAR TODOS




# ------------------------------------------------------+ PROPORCIONALIDAD +------------------------------------------------------- #

$porcentaje_envergadura_relativa = $envergadura_cm / $talla_cm * 100;
$clasificacion_envergadura = $resultado = ($porcentaje_envergadura_relativa >= 100) ? "Mayor" : (($porcentaje_envergadura_relativa < 100) ? "Menor" : (($porcentaje_envergadura_relativa == 100) ? "Igual" : ""));
$T_E = 0;
$clasificacion_t_e = $resultado = ($T_E >= -1) ? "Talla adecuada para la edad" : (($T_E < -2) ? "Talla baja para la edad" : ((($T_E >= -2) && ($T_E < -1)) ? "Riesgo de retraso en talla" : ""));
$imc_e = 0;
$clasificacion_imc_e = ($imc_e > 2) ? "Obesidad" : (($imc_e < -2) ? "Delgadez" : ((($imc_e > 1) && ($imc_e <= 2)) ? "Sobrepeso" : ((($imc_e >= -1) && ($imc_e <= 1)) ? "Adecuado" : ((($imc_e >= -2) && ($imc_e < -1)) ? "Riesgo Delgadez" : ""))));
$indice_cormico = ($talla_sentado_corregida_cm / $talla_cm) * 100;
$clasificacion_indice_cormico = (($sexo === "F") && ($indice_cormico <= 52)) ? "Braquicormico" : ((($sexo === "M") && ($indice_cormico <= 51)) ? "Braquicormico" : ((($sexo === "F") && ($indice_cormico > 52) && ($indice_cormico <= 54)) ? "Metrocormico" : ((($sexo === "M") && ($indice_cormico > 51) && ($indice_cormico <= 53)) ? "Metrocormico" : ((($sexo === "F") && ($indice_cormico > 54)) ? "Macrocormico" : ((($sexo === "M") && ($indice_cormico > 53)) ? "Macrocormico" : "")))));
$irmi = (($talla_cm - $talla_sentado_corregida_cm) / $talla_sentado_corregida_cm) * 100;
$clasificacion_irmi = ($irmi < 84.9) ? "Braquiesquelico" : ((($irmi >= 85) && ($irmi <= 89.9)) ? "Metroesquelico" : (($irmi > 89.9) ? "Macroesquelico" : ""));
$lres = (($longitud_acromial_radial + $longitud_radial_estiloidea + $longitud_medial_estiloidea_dactilar) / $talla_cm) * 100;
$clasificacion_lres = ($lres < 45) ? "Braquibraquial" : ((($lres >= 45) && ($lres < 47)) ? "Metrobraquial" : (($lres >= 47) ? "Macrobraquial" : ""));
$indice_muslo_oseo = $masa_total_adiposa_kg / $masa_osea_total_kg;
$clasificacion_imo = (($sexo === "F") && ($indice_muslo_oseo <= 2.9)) ? "Desnutricion Calorico Proteica" : ((($sexo === "M") && ($indice_muslo_oseo <= 3.7)) ? "Desnutricion Calorico Proteica" : ((($sexo === "F") && ($indice_muslo_oseo >= 3) && ($indice_muslo_oseo <= 4.2)) ? "Normal" : ((($sexo === "M") && ($indice_muslo_oseo >= 3.8) && ($indice_muslo_oseo <= 4.9)) ? "Normal" : ((($sexo === "F") && ($indice_muslo_oseo > 4.3)) ? "Alterado" : ((($sexo === "M") && ($indice_muslo_oseo > 5)) ? "Alterado" : "")))));


// echo "porcentaje_envergadura_relativa: " . $porcentaje_envergadura_relativa . PHP_EOL;
// echo "clasificacion_envergadura: " . $clasificacion_envergadura . PHP_EOL;
// echo "clasificacion_t_e: " . $clasificacion_t_e . PHP_EOL;
// echo "clasificacion_imc_e: " . $clasificacion_imc_e . PHP_EOL;
// echo "indice_cormico: " . $indice_cormico . PHP_EOL;
// echo "clasificacion_indice_cormico: " . $clasificacion_indice_cormico . PHP_EOL;
// echo "irmi: " . $irmi . PHP_EOL;
// echo "clasificacion_irmi: " . $clasificacion_irmi . PHP_EOL;
// echo "lres: " . $lres . PHP_EOL;
// echo "clasificacion_lres: " . $clasificacion_lres . PHP_EOL;
// echo "indice_muslo_oseo: " . $indice_muslo_oseo . PHP_EOL;
// echo "clasificacion_imo: " . $clasificacion_imo . PHP_EOL;

# TODOS MENOS IMC, IRMI Y CLASIFICACION DE IRMI




# ------------------------------------------------------+ MADURACION +------------------------------------------------------- #

#DERIVADAS DE CALCULO
$long_piernas = $talla_cm - $talla_sentado_cm;
$i_cormico = $talla_sentado_cm / $talla_cm;
$imc_maduracion = $peso_kg / ($talla_cm/100) ** 2;
$indice_maduracion = ($sexo == "M") ? (-9.236 + (0.0002708 * $long_piernas * $talla_sentado_cm) - (0.001663 * $edad * $long_piernas) + (0.007216 * $edad * $talla_sentado_cm) + (0.02292 * $peso_kg / ($talla_cm / 100))) : (-9.376 + (0.0001882 * $long_piernas * $talla_sentado_cm) + (0.0022 * $edad * $long_piernas) + (0.005841 * $edad * $talla_sentado_cm) - (0.002658 * $edad * $peso_kg) + (0.07693 * $peso_kg / ($talla_cm / 100)));
$edad_phv = $edad - $indice_maduracion;
$clasificacion_maduracion = ($edad_phv < 13) ? "TEMP" : (($edad_phv < 15) ? "NORM" : "TARD");
$falta_cm = 0;
$est_adult_est = $talla_cm + $falta_cm;

// echo "long_piernas: " . $long_piernas . PHP_EOL;
// echo "i_cormico: " . $i_cormico . PHP_EOL;
// echo "imc_maduracion: " . $imc_maduracion  . PHP_EOL;
// echo "indice_maduracion: " . $indice_maduracion  . PHP_EOL;
// echo "edad_phv: " . $edad_phv  . PHP_EOL;
// echo "clasificacion_maduracion: " . $clasificacion_maduracion  . PHP_EOL;
// echo "est_adult_est: " . $est_adult_est  . PHP_EOL;

# MOSTRAR TODOS


# ------------------------------------------------------+ MADURACION +------------------------------------------------------- #

$icc = $perimetro_cintura / $perimetro_cadera;
$riesgo = ($icc == " ") ? " " : (($sexo == "M") ? (($edad < 20) ? "N/A" : (($edad < 30) ? (($icc < 0.83) ? "Bajo" : (($icc < 0.89) ? "Moderado" : (($icc <= 0.94) ? "Alto" : "Muy alto"))) : (($edad < 40) ? (($icc < 0.84) ? "Bajo" : (($icc < 0.92) ? "Moderado" : (($icc <= 0.96) ? "Alto" : "Muy alto"))) : (($edad < 50) ? (($icc < 0.88) ? "Bajo" : (($icc < 0.96) ? "Moderado" : (($icc <= 1) ? "Alto" : "Muy alto"))) : (($edad < 60) ? (($icc < 0.9) ? "Bajo" : (($icc < 0.97) ? "Moderado" : (($icc <= 1.02) ? "Alto" : "Muy alto"))) : (($edad < 70) ? (($icc < 0.91) ? "Bajo" : (($icc < 0.99) ? "Moderado" : (($icc <= 1.03) ? "Alto" : "Muy alto"))) : (($edad >= 70) ? "N/A" : "N/A"))))))) : (($sexo == "F") ? (($edad < 20) ? "N/A" : (($edad < 30) ? (($icc < 0.71) ? "Bajo" : (($icc < 0.78) ? "Moderado" : (($icc <= 0.82) ? "Alto" : "Muy alto"))) : (($edad < 40) ? (($icc < 0.72) ? "Bajo" : (($icc < 0.79) ? "Moderado" : (($icc <= 0.84) ? "Alto" : "Muy alto"))) : (($edad < 50) ? (($icc < 0.73) ? "Bajo" : (($icc < 0.8) ? "Moderado" : (($icc <= 0.87) ? "Alto" : "Muy alto"))) : (($edad < 60) ? (($icc < 0.74) ? "Bajo" : (($icc < 0.82) ? "Moderado" : (($icc <= 0.88) ? "Alto" : "Muy alto"))) : (($edad < 70) ? (($icc < 0.76) ? "Bajo" : (($icc < 0.84) ? "Moderado" : (($icc <= 0.9) ? "Alto" : "Muy alto"))) : (($edad >= 70) ? "N/A" : "N/A"))))))) : null));
// $complexion = $talla_cm / $perimetro_muneca;
// $clasificacion_complexion = ($complexion == 0) ? " " : (($sexo == "M") ? (($complexion > 10.39) ? "Pequeña" : (($complexion > 9.59) ? "Mediana" : (($complexion < 9.6) ? "Recia" : null))) : (($sexo == "F") ? (($complexion > 10.99) ? "Pequeña" : (($complexion > 10.09) ? "Mediana" : (($complexion < 10.1) ? "Recia" : null))) : null));


// echo "icc: " . $icc  . PHP_EOL;
// echo "riesgo: " . $riesgo  . PHP_EOL;
// echo "complexion: " . $complexion  . PHP_EOL;
// echo "clasificacion_complexion: " . $clasificacion_complexion  . PHP_EOL;

# MOSTRAR TODOS
