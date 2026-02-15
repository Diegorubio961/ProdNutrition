<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\MeasureGeneralModel;
use App\Models\MeasureFoldsModel;
use App\Models\MeasurePerimetersModel;
use App\Models\MeasureLenghtsModel;
use App\Models\MeasureDiametersModel;
use App\Models\MeasureAdditionalVariablesModel;
use App\Models\HistoryGeneralModel;
use Core\Request;

class MeasureCalculationsController extends BaseController
{
    public function getCalculations()
    {
        $request = Request::getInstance();
        $payload = $request->all();

        if (!isset($payload['id_card'])) {
            $this->json(['message' => 'id_card es requerido'], 422);
            return;
        }

        // Find user by id_card
        $user = \App\Models\UsersModel::query()->where('id_card', '=', $payload['id_card'])->first();
        
        if (!$user) {
            $this->json(['message' => 'Paciente no encontrado con el id_card proporcionado'], 404);
            return;
        }

        $patientId = $user['id'];

        // 1. Fetch Data
        // 1. Fetch Data
        $general = MeasureGeneralModel::query()->where('patient_id', '=', $patientId)->first();
        $folds = MeasureFoldsModel::query()->where('patient_id', '=', $patientId)->first() ?: [];
        $perimeters = MeasurePerimetersModel::query()->where('patient_id', '=', $patientId)->first() ?: [];
        $lengths = MeasureLenghtsModel::query()->where('patient_id', '=', $patientId)->first() ?: [];
        $diameters = MeasureDiametersModel::query()->where('patient_id', '=', $patientId)->first() ?: [];
        $additional = MeasureAdditionalVariablesModel::query()->where('patient_id', '=', $patientId)->first() ?: [];
        $history = HistoryGeneralModel::query()->where('patient_id', '=', $patientId)->first() ?: [];


        // print_r([
        //     'general' => $general,
        //     'folds' => $folds,
        //     'perimeters' => $perimeters,
        //     'lengths' => $lengths,
        //     'diameters' => $diameters,
        //     'additional' => $additional,
        //     'history' => $history
        // ]);


        if (!$general) {
            $this->json(['message' => 'Datos generales no encontrados para el paciente'], 404);
            return;
        }

        // 2. Map Variables
        $sexo = $general['sex'] ?? null; 
        $peso_kg = (float)($general['weight_kg'] ?? 0);
        $talla_cm = (float)($general['height_cm'] ?? 0);
        $talla_sentado_cm = (float)($general['sitting_height_cm'] ?? 0);
        $altura_banco_cm = (float)($general['bench_height_cm'] ?? 0); // Need this for correction
        $talla_sentado_corregida_cm = ((float)($general['corrected_sitting_height_cm'] ?? 0) != 0) ? (float)$general['corrected_sitting_height_cm'] : ($talla_sentado_cm - $altura_banco_cm);
        $envergadura_cm = (float)($general['wingspan_cm'] ?? 0);
        
        $missingFields = [];
        
        if (empty($sexo)) {
            $missingFields[] = 'Sexo';
        }
        if ($peso_kg <= 0) {
            $missingFields[] = 'Peso (kg)';
        }
        if ($talla_cm <= 0) {
            $missingFields[] = 'Talla (cm)';
        }

        $edad = null;
        if ($history && !empty($history['birth_date'])) {
            try {
                $dob = new \DateTime($history['birth_date']);
                $now = new \DateTime();
                $edad = $now->diff($dob)->y;
            } catch (\Exception $e) {
                // Invalid date format
            }
        }

        if ($edad === null) {
            $missingFields[] = 'Fecha de Nacimiento';
        }

        if (!empty($missingFields)) {
            $this->json([
                'message' => 'Faltan datos necesarios para realizar los cálculos: ' . implode(', ', $missingFields),
                'missing_fields' => $missingFields
            ], 422);
            return;
        }

        $pliegue_triceps = (float)($folds['triceps'] ?? 0);
        $pliegue_subescapular = (float)($folds['subspcapular'] ?? 0);
        $pliegue_biceps = (float)($folds['biceps'] ?? 0);
        $pliegue_pectoral = (float)($folds['pectoral'] ?? 0);
        $pliegue_axilar = (float)($folds['axillary'] ?? 0);
        $pliegue_suprailiaco = (float)($folds['suprailiac'] ?? 0); 
        $pliegue_supraespinal = (float)($folds['supraspinal'] ?? 0);
        $pliegue_abdominal = (float)($folds['abdominal'] ?? 0);
        $pliegue_muslo = (float)($folds['thigh'] ?? 0);
        $pliegue_pierna = (float)($folds['leg'] ?? 0);

        $perimetro_cabeza = (float)($perimeters['head'] ?? 0);
        $perimetro_brazo_relajado = (float)($perimeters['arm_relaxed'] ?? 0);
        $perimetro_brazo_tenso = (float)($perimeters['arm_tensed'] ?? 0);
        $perimetro_antebrazo = (float)($perimeters['forearm'] ?? 0);
        $perimetro_mesoesternal = (float)($perimeters['mesosternal'] ?? 0);
        $perimetro_cintura = (float)($perimeters['waist'] ?? 0);
        $perimetro_cadera = (float)($perimeters['hip'] ?? 0);
        $perimetro_muslo_maximo = (float)($perimeters['thigh_max'] ?? 0);
        $perimetro_pantorrilla_maxima = (float)($perimeters['calf_max'] ?? 0);
        $perimetro_muneca = (float)($perimeters['wrist'] ?? 0); 

        $diametro_biacromial = (float)($diameters['biacromial'] ?? 0);
        $diametro_bilieocrestal = (float)($diameters['biileocrestal'] ?? 0);
        $diametro_humero_biepicondilar = (float)($diameters['humerus_biepicondylar'] ?? 0);
        $diametro_femoral_biepicondilar = (float)($diameters['femur_biepicondylar'] ?? 0);
        $diametro_torax_transverso = (float)($diameters['thorax_transverse'] ?? 0);
        $diametro_torax_anteroposterior = (float)($diameters['thorax_antero_posterior'] ?? 0);

        $longitud_acromial_radial = (float)($lengths['acromial_radial'] ?? 0);
        $longitud_radial_estiloidea = (float)($lengths['radial_styloid'] ?? 0);
        $longitud_medial_estiloidea_dactilar = (float)($lengths['medial_styloid_dactilar'] ?? 0);

        $porcentaje_grasa_adecuado = (float)($additional['ideal_fat_percentage'] ?? 0);
        $porcentaje_grasa_adecuado_jyp = (float)($additional['ideal_fat_percentage_jyp'] ?? 0);
        $porcentaje_grasa_adecuado_dur = (float)($additional['ideal_fat_percentage_durning'] ?? 0);

        // ---------------- CALCULATIONS ----------------
        $results = [];

        // --- IAKS ---
        $aks = 0;
        $clasificacion_aks = "";
        $mca_aks = 0;

        if ($sexo == "M") {
            $porcentaje_grasa_pyb = round((2.745 + (0.0008 * $pliegue_triceps) + (0.002 * $pliegue_subescapular) + (0.637 * $pliegue_suprailiaco) + (0.809 * $pliegue_biceps)), 2);
            $kg_grasa_pyb = round((($porcentaje_grasa_pyb * $peso_kg) / 100), 2);
            $mca_aks = round(($peso_kg - $kg_grasa_pyb), 2);
            $talla_pow_3 = ($talla_cm > 0) ? ($talla_cm ** 3) : 1;
            $aks = round((($mca_aks * 100000) / $talla_pow_3), 2);
            $clasificacion_aks = ($aks >= 1.01) ? "Adecuado" : (($aks < 1.01) ? "Deficiente" : (($aks > 1.55) ? "Muy Buena" : ""));
            $porcentaje_grasa_iaks = $porcentaje_grasa_pyb;
            $kg_grasa_iaks = $kg_grasa_pyb;
        } else {
             $sum_pliegues_log = $pliegue_triceps + $pliegue_subescapular + $pliegue_biceps + $pliegue_suprailiaco;
             $densidad_corporal = ($sum_pliegues_log > 0) ? (1.1581 - (0.072 * log10($sum_pliegues_log))) : 0;
             $porcentaje_grasa_dyr = ($densidad_corporal > 0) ? (((4.95 / $densidad_corporal) - 4.5) * 100) : 0;
             $kg_grasa_dyr = (($porcentaje_grasa_dyr * $peso_kg) / 100);
             $mca_aks = ($peso_kg - $kg_grasa_dyr);
             $talla_pow_3 = ($talla_cm > 0) ? ($talla_cm ** 3) : 1;
             $aks = ($mca_aks * 100000) / $talla_pow_3;
             $clasificacion_aks = ($aks >= 0.93) ? "Adecuado" : (($aks < 0.93) ? "Deficiente" : (($aks > 1.24) ? "Muy Buena" : ""));
             $porcentaje_grasa_iaks = $porcentaje_grasa_dyr;
             $kg_grasa_iaks = $kg_grasa_dyr;
        }

        // Fat Free Mass Index
        $talla_m = $talla_cm / 100;
        $talla_m_sq = ($talla_m > 0) ? ($talla_m ** 2) : 1;
        $indice_masa_magra = round(($mca_aks / $talla_m_sq), 1);
        $interpretacion_ffmi = "";
        
        if ($sexo == "M") {
             $interpretacion_ffmi = (($indice_masa_magra < 16.7) ? "BAJO" : (($indice_masa_magra < 19.8) ? "ADECUADO" : (($indice_masa_magra > 19.8) ? "MUY BUENO" : "")));
        } else {
             $interpretacion_ffmi = (($indice_masa_magra < 14.6) ? "BAJO" : (($indice_masa_magra < 16.8) ? "ADECUADO" : (($indice_masa_magra > 16.8) ? "MUY BUENO" : "")));
        }

        // IAKS (or General Indicators) Group? 
        // User asked for 5 groups: Yuhasz, 5 Componentes, J&P, Slaughter, Durning.
        // IAKS seems to be part of the general output in 'ecuaciones.php'. 
        // I'll add IAKS as its own group or merge if appropriate. 
        // The image usually shows IAKS as a separate section or part of "Medidas".
        // I will return it as "IAKS".
        
        $iaks_results = [
            'aks' => $aks,
            'clasificacion' => $clasificacion_aks,
            'indice_masa_magra' => $indice_masa_magra,
            'interpretacion_ffmi' => $interpretacion_ffmi,
            'porcentaje_grasa' => $porcentaje_grasa_iaks,
            'grasa_kg' => $kg_grasa_iaks,
        ];

        if ($sexo == "M") {
            $iaks_results['mca_pyb'] = $mca_aks;
        } else {
            $iaks_results['mca_dyr'] = $mca_aks;
        }

        $results['IAKS'] = $iaks_results;


        // --- YUHASZ ---
        $imc = ($talla_cm == 0) ? " " : ($peso_kg / $talla_m_sq);
        $suma_yuhasz = ($pliegue_triceps + $pliegue_subescapular + $pliegue_supraespinal + $pliegue_abdominal + $pliegue_muslo + $pliegue_pierna);
        $suma_yuhasz_mas_biceps_7_pliegues = ($pliegue_triceps + $pliegue_subescapular + $pliegue_biceps + $pliegue_supraespinal + $pliegue_abdominal + $pliegue_muslo + $pliegue_pierna);
        
        $porcentaje_grasa_final = 0;
        if ($sexo == "M") {
            $porcentaje_grasa_final = ($suma_yuhasz * 0.1051) + 2.585;
        } else {
            $porcentaje_grasa_final = ($suma_yuhasz * 0.1548) + 3.5803;
        }

        $grasa_kg = ($peso_kg * $porcentaje_grasa_final) / 100;
        $mlg_kg = $peso_kg - $grasa_kg;
        $mca = (1 - ($porcentaje_grasa_adecuado / 100)) != 0 ? ($mlg_kg / (1 - ($porcentaje_grasa_adecuado / 100))) : 0;
        $kg_a_perder = $peso_kg - $mca;

        $results['Yuhasz'] = [
            'imc' => $imc,
            'porcentaje_grasa' => $porcentaje_grasa_final, // Consolidated
            'grasa_kg' => $grasa_kg,
            'mlg_kg' => $mlg_kg,
            'porcentaje_grasa_adecuado' => $porcentaje_grasa_adecuado,
            'mca' => $mca,
            'kg_a_perder' => $kg_a_perder,
            'suma_6_pliegues' => $suma_yuhasz,
            'suma_7_pliegues' => $suma_yuhasz_mas_biceps_7_pliegues,
            'iaks' => $aks // "iaks_mujeres/hombres" in code
        ];


        // --- 5 COMPONENTES ---
        $constante_masa_piel = ($sexo == "M") ? 68.308 : (($sexo == "F") ? 73.074 : (($edad < 12) ? 70.691 : 0));
        $grosor_piel = ($sexo == "M") ? 2.07 : (($sexo == "F") ? 1.96 : 0);
        $suma_diametros_masa_osea = ($diametro_biacromial + $diametro_bilieocrestal) + ($diametro_humero_biepicondilar * 2) + ($diametro_femoral_biepicondilar * 2);
        
        $scale_factor = ($talla_cm > 0) ? (170.18 / $talla_cm) : 0;
        $score_z_oseo_cuerpo = (($suma_diametros_masa_osea * $scale_factor) - 98.88) / 5.33;
        $masa_osea_cuerpo_kg = ($scale_factor > 0) ? (($score_z_oseo_cuerpo * 1.34) + 6.7) / ($scale_factor ** 3) : 0;
        
        $score_z_cabeza = ($perimetro_cabeza - 56) / 1.44;
        $masa_osea_cabeza_kg = ($score_z_cabeza * 0.18) + 1.2;
        $masa_osea_total_kg = $masa_osea_cabeza_kg + $masa_osea_cuerpo_kg;
        
        $perimetro_brazo_corregido = $perimetro_brazo_relajado - ($pliegue_triceps * 3.141 / 10);
        $perimetro_muslo_corregido = $perimetro_muslo_maximo - ($pliegue_muslo * 3.141 / 10);
        $perimetro_pantorrilla_corregido = $perimetro_pantorrilla_maxima - ($pliegue_pierna * 3.141 / 10);
        $perimetro_torax_corregido = $perimetro_mesoesternal - ($pliegue_subescapular * 3.141 / 10 );
        $suma_perimetros_corregidos = $perimetro_brazo_corregido + $perimetro_antebrazo + $perimetro_muslo_corregido + $perimetro_pantorrilla_corregido + $perimetro_torax_corregido;
        
        $score_z_muscular = (($suma_perimetros_corregidos * $scale_factor) - 207.21) / 13.74;
        $masa_muscular_kg = ($scale_factor > 0) ? (($score_z_muscular * 5.4) + 24.5) / ($scale_factor ** 3) : 0;
        
        $perimetro_cintura_corregido = $perimetro_cintura - ($pliegue_abdominal * 0.3141);
        $suma_torax = $diametro_torax_transverso + $diametro_torax_anteroposterior + $perimetro_cintura_corregido;
        
        $scale_factor_sentado = ($talla_sentado_corregida_cm != 0) ? (89.92 / $talla_sentado_corregida_cm) : 0;
        $score_z_residual = ($scale_factor_sentado != 0) ? (($suma_torax * $scale_factor_sentado) - 109.35) / 7.08 : 0;
        $masa_residual_kg = ($scale_factor_sentado != 0) ? (($score_z_residual * 1.24) + 6.1) / ($scale_factor_sentado ** 3) : 0;
        
        $sumatoria_seis_pliegues = $pliegue_triceps + $pliegue_subescapular + $pliegue_supraespinal + $pliegue_abdominal + $pliegue_muslo + $pliegue_pierna;
        $score_z_adiposa = (($sumatoria_seis_pliegues * $scale_factor) - 116.41) / 34.79;
        $masa_adiposa_kg = ($scale_factor > 0) ? (($score_z_adiposa * 5.85) + 25.6) / ($scale_factor ** 3) : 0;
        
        $area_superficial = ($constante_masa_piel * ($peso_kg ** 0.425) * ($talla_cm ** 0.725)) / 10000;
        $masa_piel_kg = $area_superficial * $grosor_piel * 1.05;
        
        $peso_estructurado_kg = $masa_piel_kg + $masa_adiposa_kg + $masa_muscular_kg + $masa_residual_kg + $masa_osea_total_kg;
        $diferencia_pe_peso_bruto = $peso_estructurado_kg - $peso_kg;
        
        $piel_porcentaje = ($peso_estructurado_kg > 0) ? $masa_piel_kg / $peso_estructurado_kg : 0;
        $adiposa_porcentaje = ($peso_estructurado_kg > 0) ? $masa_adiposa_kg / $peso_estructurado_kg : 0;
        $muscular_porcentaje = ($peso_estructurado_kg > 0) ? $masa_muscular_kg / $peso_estructurado_kg : 0;
        $residual_porcentaje = ($peso_estructurado_kg > 0) ? $masa_residual_kg / $peso_estructurado_kg : 0;
        $osea_porcentaje = ($peso_estructurado_kg > 0) ? $masa_osea_total_kg / $peso_estructurado_kg : 0;
        
        $ajustes_masa_piel = $diferencia_pe_peso_bruto * $piel_porcentaje;
        $ajuste_adiposa = $diferencia_pe_peso_bruto * $adiposa_porcentaje;
        $ajustes_masa_muscular = $diferencia_pe_peso_bruto * $muscular_porcentaje;
        $ajustes_masa_residual = $diferencia_pe_peso_bruto * $residual_porcentaje;
        $ajustes_masa_osea = $diferencia_pe_peso_bruto * $osea_porcentaje;
        
        $masa_total_adiposa_kg_final = $masa_adiposa_kg - $ajuste_adiposa;
        $masa_total_muscular_kg_final = $masa_muscular_kg - $ajustes_masa_muscular;
        $masa_residual_kg_final = $masa_residual_kg - $ajustes_masa_residual;
        $masa_osea_kg_final = $masa_osea_total_kg - $ajustes_masa_osea;
        $masa_piel_kg_final = $masa_piel_kg - $ajustes_masa_piel;
        $total_masa_kg = $masa_piel_kg_final + $masa_total_adiposa_kg_final + $masa_total_muscular_kg_final + $masa_residual_kg_final + $masa_osea_kg_final;

        $results['vars_components'] = [
            'constante_masa_piel' => $constante_masa_piel,
            'grosor_piel' => $grosor_piel,
            'suma_diametros_masa_osea' => $suma_diametros_masa_osea,
            'scale_factor' => $scale_factor,
            'score_z_oseo_cuerpo' => $score_z_oseo_cuerpo,
            'masa_osea_cuerpo_kg' => $masa_osea_cuerpo_kg,
            'score_z_cabeza' => $score_z_cabeza,
            'masa_osea_cabeza_kg' => $masa_osea_cabeza_kg,
            'masa_osea_total_kg' => $masa_osea_total_kg,
            'perimetro_brazo_corregido' => $perimetro_brazo_corregido,
            'perimetro_antebrazo' => $perimetro_antebrazo,
            'perimetro_muslo_corregido' => $perimetro_muslo_corregido,
            'perimetro_pantorrilla_corregido' => $perimetro_pantorrilla_corregido,
            'perimetro_torax_corregido' => $perimetro_torax_corregido,
            'suma_perimetros_corregidos' => $suma_perimetros_corregidos,
            'score_z_muscular' => $score_z_muscular,
            'masa_muscular_kg' => $masa_muscular_kg,
            'perimetro_cintura_corregido' => $perimetro_cintura_corregido,
            'suma_torax' => $suma_torax,
            'scale_factor_sentado' => $scale_factor_sentado,
            'score_z_residual' => $score_z_residual,
            'masa_residual_kg' => $masa_residual_kg,
            'sumatoria_seis_pliegues' => $sumatoria_seis_pliegues,
            'score_z_adiposa' => $score_z_adiposa,
            'masa_adiposa_kg' => $masa_adiposa_kg,
            'area_superficial' => $area_superficial,
            'masa_piel_kg' => $masa_piel_kg,
            'peso_estructurado_kg' => $peso_estructurado_kg,
            'diferencia_pe_peso_bruto' => $diferencia_pe_peso_bruto,
            'ajustes_masa_piel' => $ajustes_masa_piel,
            'ajuste_adiposa' => $ajuste_adiposa,
            'ajustes_masa_muscular' => $ajustes_masa_muscular,
            'ajustes_masa_residual' => $ajustes_masa_residual,
            'ajustes_masa_osea' => $ajustes_masa_osea,
        ];

        $results['5_Componentes'] = [
            'masa_piel_kg' => $masa_piel_kg_final,
            'masa_adiposa_kg' => $masa_total_adiposa_kg_final,
            'masa_muscular_kg' => $masa_total_muscular_kg_final,
            'masa_residual_kg' => $masa_residual_kg_final,
            'masa_osea_kg' => $masa_osea_kg_final,
            'total_masa_kg' => $total_masa_kg,
            'adiposa_porcentaje' => $adiposa_porcentaje * 100, // Usually displayed as %
            'muscular_porcentaje' => $muscular_porcentaje * 100,
            'osea_porcentaje' => $osea_porcentaje * 100,
            'piel_porcentaje' => $piel_porcentaje * 100,
            'residual_porcentaje' => $residual_porcentaje * 100,
        ];


        // --- J&P ---
        $sum_3_pliegues_F = $pliegue_triceps + $pliegue_supraespinal + $pliegue_muslo;
        $sum_3_pliegues_M = $pliegue_pectoral + $pliegue_abdominal + $pliegue_muslo;
        
        $porcentaje_grasa_final_jyp = 0;
        
        if ($sexo == 'F') {
             $denom = 1.0994921 - (0.0009929 * $sum_3_pliegues_F) + (0.0000023 * ($sum_3_pliegues_F ** 2)) - (0.0001392 * $edad);
             if ($denom != 0) $porcentaje_grasa_final_jyp = ((4.95 / $denom) - 4.5) * 100;
        } else {
             $denom = 1.10938 - (0.0008267 * $sum_3_pliegues_M) + (0.0000016 * ($sum_3_pliegues_M ** 2)) - (0.0002574 * $edad);
             if ($denom != 0) $porcentaje_grasa_final_jyp = ((4.95 / $denom) - 4.5) * 100;
        }
        
        $grasa_kg_jyp = ($peso_kg * $porcentaje_grasa_final_jyp) / 100;
        $mlg_jyp_kg = $peso_kg - $grasa_kg_jyp;
        $mca_jyp = (1 - ($porcentaje_grasa_adecuado_jyp / 100)) != 0 ? $mlg_jyp_kg / (1 - ($porcentaje_grasa_adecuado_jyp / 100)) : 0;
        $kg_a_perder_jyp = $peso_kg - $mca_jyp;
        
        $clasificacion_jyp = "";
        if ($sexo == "M") {
            $clasificacion_jyp = ($porcentaje_grasa_final_jyp < 15) ? "Bajo" : (($porcentaje_grasa_final_jyp < 20) ? "Adecuado" : (($porcentaje_grasa_final_jyp > 19.99) ? "Exceso" : ""));
        } else {
             $clasificacion_jyp = ($porcentaje_grasa_final_jyp < 22) ? "Bajo" : (($porcentaje_grasa_final_jyp < 28) ? "Adecuado" : (($porcentaje_grasa_final_jyp > 27.99) ? "Exceso" : ""));
        }
        
        $sumatoria_7_pliegues_jyp = $pliegue_triceps + $pliegue_subescapular + $pliegue_biceps + $pliegue_supraespinal + $pliegue_abdominal + $pliegue_muslo + $pliegue_pierna;

        $results['J_P'] = [
            'imc' => $imc,
            'porcentaje_grasa' => $porcentaje_grasa_final_jyp, // Consolidated
            'grasa_kg' => $grasa_kg_jyp,
            'mlg_kg' => $mlg_jyp_kg,
            'mca' => $mca_jyp,
            'kg_a_perder' => $kg_a_perder_jyp,
            'clasificacion' => $clasificacion_jyp,
            'sumatoria_7_pliegues' => $sumatoria_7_pliegues_jyp
        ];


        // --- SLAUGHTER ---
        $sumatoria_pliegues_sla = $pliegue_triceps + $pliegue_pierna;
        $porcentaje_grasa_final_sla = 0;

        if ($sexo == "F") {
            $porcentaje_grasa_final_sla = ((0.61 * $sumatoria_pliegues_sla) + 5.1);
        } else {
            $porcentaje_grasa_final_sla = ((0.735 * $sumatoria_pliegues_sla) + 1);
        }

        $grasa_kg_sla = ($peso_kg * $porcentaje_grasa_final_sla / 100);
        $mlg_sla = $peso_kg - $grasa_kg_sla;
        
        $clasificacion_sla = "";
        if ($sexo === "M") {
            $clasificacion_sla = ($porcentaje_grasa_final_sla < 6) ? "Muy Bajo" : (($porcentaje_grasa_final_sla < 11) ? "Bajo" : (($porcentaje_grasa_final_sla < 20) ? "Óptimo" : (($porcentaje_grasa_final_sla < 25) ? "Moderadamente Alto" : (($porcentaje_grasa_final_sla < 31) ? "Alto" : (($porcentaje_grasa_final_sla > 31) ? "Muy Alto" : "")))));
        } else {
            $clasificacion_sla = ($porcentaje_grasa_final_sla < 11) ? "Muy Bajo" : (($porcentaje_grasa_final_sla < 15) ? "Bajo" : (($porcentaje_grasa_final_sla < 25) ? "Óptimo" : (($porcentaje_grasa_final_sla < 30) ? "Moderadamente Alto" : (($porcentaje_grasa_final_sla < 35.5) ? "Alto" : (($porcentaje_grasa_final_sla > 35.5) ? "Muy Alto" : "")))));
        }

        $results['Slaughter'] = [
            'imc' => $imc,
            'mlg_kg' => $mlg_sla,
            'grasa_kg' => $grasa_kg_sla,
            'porcentaje_grasa' => $porcentaje_grasa_final_sla,
            'clasificacion' => $clasificacion_sla,
            'sumatoria_pliegues' => $sumatoria_pliegues_sla
        ];


        // --- DURNING ---
        $sumatoria_4_pliegues = $pliegue_triceps + $pliegue_biceps + $pliegue_subescapular + $pliegue_suprailiaco;
        $log_sum = ($sumatoria_4_pliegues > 0) ? log10($sumatoria_4_pliegues) : 0;
        $porcentaje_grasa_final_dur = 0;
        
        if ($sexo == "M") {
            if ($edad < 20) $porcentaje_grasa_final_dur = ((4.95 / (1.162 - (0.063 * $log_sum))) - 4.5) * 100;
            elseif ($edad < 30) $porcentaje_grasa_final_dur = ((4.95 / (1.1631 - (0.0632 * $log_sum))) - 4.5) * 100;
            elseif ($edad < 40) $porcentaje_grasa_final_dur = ((4.95 / (1.1422 - (0.0544 * $log_sum))) - 4.5) * 100;
            elseif ($edad < 50) $porcentaje_grasa_final_dur = ((4.95 / (1.162 - (0.07 * $log_sum))) - 4.5) * 100;
            else $porcentaje_grasa_final_dur = ((4.95 / (1.1715 - (0.0779 * $log_sum))) - 4.5) * 100;
        } else {
             if ($edad < 20) $porcentaje_grasa_final_dur = ((4.95 / (1.1549 - (0.0678 * $log_sum))) - 4.5) * 100;
            elseif ($edad < 30) $porcentaje_grasa_final_dur = ((4.95 / (1.1599 - (0.0717 * $log_sum))) - 4.5) * 100;
            elseif ($edad < 40) $porcentaje_grasa_final_dur = ((4.95 / (1.1423 - (0.0632 * $log_sum))) - 4.5) * 100;
            elseif ($edad < 50) $porcentaje_grasa_final_dur = ((4.95 / (1.1333 - (0.0612 * $log_sum))) - 4.5) * 100;
            else $porcentaje_grasa_final_dur = ((4.95 / (1.1339 - (0.0645 * $log_sum))) - 4.5) * 100;
        }

        $grasa_kg_dur = $peso_kg * $porcentaje_grasa_final_dur / 100;
        $mlg_dur = $peso_kg - $grasa_kg_dur;
        $mca_dur = (1 - ($porcentaje_grasa_adecuado_dur / 100)) != 0 ? $mlg_dur / (1 - ($porcentaje_grasa_adecuado_dur / 100)) : 0;
        $kg_perder_dur = $peso_kg - $mca_dur;
        
        $clasificacion_dur = "";
        if ($sexo === "M") {
            $clasificacion_dur = ($porcentaje_grasa_final_dur < 15) ? "Delgado" : (($porcentaje_grasa_final_dur < 22) ? "Adecuado" : (($porcentaje_grasa_final_dur < 28) ? "Exceso" : (($porcentaje_grasa_final_dur > 27.99) ? "Obeso" : "")));
        } else {
             $clasificacion_dur = ($porcentaje_grasa_final_dur < 20) ? "Delgado" : (($porcentaje_grasa_final_dur < 27) ? "Adecuado" : (($porcentaje_grasa_final_dur < 34) ? "Exceso" : (($porcentaje_grasa_final_dur > 33.99) ? "Obeso" : "")));
        }

        $sumatoria_7_pliegues_dur = $pliegue_triceps + $pliegue_subescapular + $pliegue_biceps + $pliegue_supraespinal + $pliegue_abdominal + $pliegue_muslo + $pliegue_pierna;

        $results['Durning'] = [
            'porcentaje_grasa' => $porcentaje_grasa_final_dur, // Consolidated
            'grasa_kg' => $grasa_kg_dur,
            'mlg_kg' => $mlg_dur,
            'mca' => $mca_dur,
            'kg_perder' => $kg_perder_dur,
            'imc' => $imc,
            'clasificacion' => $clasificacion_dur,
            'sumatoria_4_pliegues' => $sumatoria_4_pliegues,
            'sumatoria_7_pliegues' => $sumatoria_7_pliegues_dur
        ];

        // --- SOMATOTIPO (User asked for 5 groups, but Somatotipo often goes with them) ---
        $s3plieg = ($talla_cm > 0) ? ($pliegue_triceps + $pliegue_subescapular + $pliegue_supraespinal) * 170.18 / $talla_cm : 0;
        $hwr = ($peso_kg > 0) ? $talla_cm / ($peso_kg ** 0.3333) : 0;
        
        $endo = -0.7182 + (0.1451 * $s3plieg) - (0.00068 * ($s3plieg ** 2)) + (0.0000014 * ($s3plieg ** 3));
        $meso = (0.858 * $diametro_humero_biepicondilar) + (0.601 * $diametro_femoral_biepicondilar) + (0.188 * ($perimetro_brazo_tenso - ($pliegue_triceps / 10))) + (0.161 * ($perimetro_pantorrilla_maxima - ($pliegue_pierna / 10))) - ($talla_cm * 0.131) + 4.5;
        $ecto = ($hwr >= 40.75) ? ((0.732 * $hwr) - 28.58) : (($hwr > 38.25) ? ((0.463 * $hwr) - 17.63) : 0.1);
        
        $x = $ecto - $endo;
        $y = 2 * $meso - ($endo + $ecto);

        $results['Somatotipo'] = [
             'endo' => $endo,
             'meso' => $meso,
             'ecto' => $ecto,
             'x' => $x,
             'y' => $y,
             'imc' => $imc
        ];

        $results = $this->formatResponse($results);

        $this->json($results, 200);
    }

    private function formatResponse(array $data): array
    {
        foreach ($data as $key => $value) {
            if (is_array($value)) {
                $data[$key] = $this->formatResponse($value);
            } elseif (is_numeric($value)) {
                $data[$key] = round((float)$value, 2);
            }
        }
        return $data;
    }
}
