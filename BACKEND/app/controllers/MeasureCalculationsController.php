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

        if (!isset($payload['patient_id'])) {
            $this->json(['message' => 'patient_id es requerido'], 422);
            return;
        }

        // Find user by id (patient_id)
        $user = \App\Models\UsersModel::query()->where('id', '=', $payload['patient_id'])->first();
        
        if (!$user) {
            $this->json(['message' => 'Paciente no encontrado con el ID proporcionado'], 404);
            return;
        }

        $patientId = $user['id'];

        // Verify if user is Admin (1) or Patient (3)
        $roleRecord = \App\Models\UserInRolModel::query()
            ->where('user_id', '=', $patientId)
            ->first();

        $rolId = isset($roleRecord['rol_id']) ? (int)$roleRecord['rol_id'] : 0;
        
        // Allow Admin (1) or Patient (3)
        if ($rolId !== 1 && $rolId !== 3) {
             $this->json(['message' => 'El usuario no tiene permisos para realizar esta acción (Rol no válido)'], 403);
             return;
        }

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

        // General Variables
        $talla_m = $talla_cm / 100;
        $talla_m_sq = ($talla_m > 0) ? ($talla_m ** 2) : 1;
        $imc = ($talla_cm > 0) ? ($peso_kg / $talla_m_sq) : 0;

        // --- IAKS ---
        $aks = 0;
        $clasificacion_aks = "";
        $mca_aks = 0;

        // Calculate PyB unconditionally (Penroe, Nelson & Fisher)
        $porcentaje_grasa_pyb = round((2.745 + (0.0008 * $pliegue_triceps) + (0.002 * $pliegue_subescapular) + (0.637 * $pliegue_suprailiaco) + (0.809 * $pliegue_biceps)), 2);
        $kg_grasa_pyb = round((($porcentaje_grasa_pyb * $peso_kg) / 100), 2);
        $mca_pyb = round(($peso_kg - $kg_grasa_pyb), 2);

        $porcentaje_grasa_dyr = 0;
        $kg_grasa_dyr = 0;
        $mca_dyr = 0;

        if ($sexo == "M") {
            $mca_aks = $mca_pyb;
            
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
             $mca_dyr = ($peso_kg - $kg_grasa_dyr);
             $mca_aks = $mca_dyr; // This is mca_dyr
             
             $talla_pow_3 = ($talla_cm > 0) ? ($talla_cm ** 3) : 1;
             $aks = ($mca_aks * 100000) / $talla_pow_3;
             $clasificacion_aks = ($aks >= 0.93) ? "Adecuado" : (($aks < 0.93) ? "Deficiente" : (($aks > 1.24) ? "Muy Buena" : ""));
             $porcentaje_grasa_iaks = $porcentaje_grasa_dyr;
             $kg_grasa_iaks = $kg_grasa_dyr;
        }

        // Fat Free Mass Index
        $indice_masa_magra = round(($mca_pyb / $talla_m_sq), 1);
        $interpretacion_ffmi = "";
        
        if ($sexo == "M") {
             $interpretacion_ffmi = (($indice_masa_magra < 16.7) ? "BAJO" : (($indice_masa_magra < 19.8) ? "ADECUADO" : (($indice_masa_magra > 19.8) ? "MUY BUENO" : "")));
        } else {
             $interpretacion_ffmi = (($indice_masa_magra < 14.6) ? "BAJO" : (($indice_masa_magra < 16.8) ? "ADECUADO" : (($indice_masa_magra > 16.8) ? "MUY BUENO" : "")));
        }

        // $results['generals'] = [
        //     'imc' => $imc,
        //     'mca_pyb' => $mca_pyb,
        //     'porcentaje_grasa_pyb' => $porcentaje_grasa_pyb,
        //     'kg_grasa_pyb' => $kg_grasa_pyb,
        //     'aks' => $aks,
        //     'clasificacion_aks' => $clasificacion_aks, // Also general? IAKS is general indicators?
        //     'indice_masa_magra' => $indice_masa_magra,
        //     'interpretacion_ffmi' => $interpretacion_ffmi,
        //     'densidad_corporal' => $densidad_corporal,
        // ];
        
        // if ($sexo != 'M') {
        //     $results['generals']['mca_dyr'] = $mca_dyr;
        //     $results['generals']['porcentaje_grasa_dyr'] = $porcentaje_grasa_dyr;
        //     $results['generals']['kg_grasa_dyr'] = $kg_grasa_dyr;
        // }

        // IAKS (or General Indicators) Group? 
        // Keeping IAKS group as per previous request, but maybe user wants it merged?
        // User asked for "another key called generals". I'll keep IAKS separately if needed or just alias it.
        // I'll keep IAKS group as duplicate/specific for now to avoid breaking anything if UI expects IAKS.
        
        $iaks_results = [
            'aks' => $aks,
            'clasificacion' => $clasificacion_aks,
            'indice_masa_magra' => $indice_masa_magra,
            'interpretacion_ffmi' => $interpretacion_ffmi,
        ];

        // if ($sexo != "M") {
        //     $iaks_results['mca_dyr'] = $mca_aks;
        // }

        $results['IAKS'] = $iaks_results;


        // --- YUHASZ ---
        // $imc is already calculated globally
        // $talla_m_sq is already calculated globally

        $suma_yuhasz = ($pliegue_triceps + $pliegue_subescapular + $pliegue_supraespinal + $pliegue_abdominal + $pliegue_muslo + $pliegue_pierna);
        $suma_yuhasz_mas_biceps_7_pliegues = ($pliegue_triceps + $pliegue_subescapular + $pliegue_biceps + $pliegue_supraespinal + $pliegue_abdominal + $pliegue_muslo + $pliegue_pierna);
        
        $clasificacion_yuhasz = "";

        if ($sexo == "M") {
            $porcentaje_grasa_final = ($suma_yuhasz * 0.1051) + 2.585;

            // Classification Table Logic (Men)
            if ($porcentaje_grasa_final <= 5.0) {
                $clasificacion_yuhasz = "Muy Delgado";
            } elseif ($porcentaje_grasa_final <= 6.5) {
                $clasificacion_yuhasz = "Delgado";
            } elseif ($porcentaje_grasa_final <= 8.0) {
                $clasificacion_yuhasz = "Ideal";
            } elseif ($porcentaje_grasa_final <= 9.5) {
                $clasificacion_yuhasz = "Promedio";
            } elseif ($porcentaje_grasa_final <= 11.0) {
                $clasificacion_yuhasz = "Leve alto";
            } elseif ($porcentaje_grasa_final <= 12.5) {
                $clasificacion_yuhasz = "Alto";
            } else {
                $clasificacion_yuhasz = "Muy alto - Obesidad";
            }
        } else {
            $porcentaje_grasa_final = ($suma_yuhasz * 0.1548) + 3.5803;

            // Classification Table Logic (Women)
            if ($porcentaje_grasa_final <= 8.3) {
                $clasificacion_yuhasz = "Muy Delgado";
            } elseif ($porcentaje_grasa_final <= 11.9) {
                $clasificacion_yuhasz = "Delgado";
            } elseif ($porcentaje_grasa_final <= 15.5) {
                $clasificacion_yuhasz = "Ideal";
            } elseif ($porcentaje_grasa_final <= 19.1) {
                $clasificacion_yuhasz = "Promedio";
            } elseif ($porcentaje_grasa_final <= 22.6) {
                $clasificacion_yuhasz = "Leve alto";
            } elseif ($porcentaje_grasa_final <= 26.2) {
                $clasificacion_yuhasz = "Alto";
            } else {
                $clasificacion_yuhasz = "Muy alto - Obesidad";
            }
        }

        $grasa_kg = ($peso_kg * $porcentaje_grasa_final) / 100;
        $mlg_kg = $peso_kg - $grasa_kg;
        $mca = (1 - ($porcentaje_grasa_adecuado / 100)) != 0 ? ($mlg_kg / (1 - ($porcentaje_grasa_adecuado / 100))) : 0;
        $kg_a_perder = $peso_kg - $mca;

        $results['Yuhasz'] = [
            'imc' => $imc,
            'porcentaje_grasa' => $porcentaje_grasa_final, // Consolidated
            'clasificacion' => $clasificacion_yuhasz,
            'grasa_kg' => $grasa_kg,
            'mlg_kg' => $mlg_kg,
            'porcentaje_grasa_adecuado' => $porcentaje_grasa_adecuado,
            'mca' => $mca,
            'kg_a_perder' => $kg_a_perder,
            'suma_yuhasz' => $suma_yuhasz,
            'suma_7_pliegues' => $suma_yuhasz_mas_biceps_7_pliegues,
            'iaks' => $aks, // "iaks_mujeres/hombres" in code
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
        
        $piel_porcentaje = ($peso_estructurado_kg != 0) ? $masa_piel_kg / $peso_estructurado_kg : 0;
        $adiposa_porcentaje = ($peso_estructurado_kg != 0) ? $masa_adiposa_kg / $peso_estructurado_kg : 0;
        $muscular_porcentaje = ($peso_estructurado_kg != 0) ? $masa_muscular_kg / $peso_estructurado_kg : 0;
        $residual_porcentaje = ($peso_estructurado_kg != 0) ? $masa_residual_kg / $peso_estructurado_kg : 0;
        $osea_porcentaje = ($peso_estructurado_kg != 0) ? $masa_osea_total_kg / $peso_estructurado_kg : 0;
        
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
            // Suma de todos los porcentajes
            'porcentaje_total' => ($adiposa_porcentaje + $muscular_porcentaje + $osea_porcentaje + $piel_porcentaje + $residual_porcentaje) * 100,
            'porcentaje_fraccion_lipidica' => (0.327 + 0.0124 * $adiposa_porcentaje) * 100,
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
            'porcentaje_grasa' => $porcentaje_grasa_final_jyp, // Consolidated
            'grasa_kg' => $grasa_kg_jyp,
            'mlg_kg' => $mlg_jyp_kg,
            'mca' => $mca_jyp,
            'kg_a_perder' => $kg_a_perder_jyp,
            'clasificacion' => $clasificacion_jyp,
            'sumatoria_7_pliegues' => $sumatoria_7_pliegues_jyp,
            'porcentaje_grasa_adecuado' => $porcentaje_grasa_adecuado_jyp 
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
            // 'imc' => $imc,
            'clasificacion' => $clasificacion_dur,
            'sumatoria_4_pliegues' => $sumatoria_4_pliegues,
            'sumatoria_7_pliegues' => $sumatoria_7_pliegues_dur,
            'porcentaje_grasa_adecuado' => $porcentaje_grasa_adecuado_dur
        ];

        // --- SOMATOTIPO (User asked for 5 groups, but Somatotipo often goes with them) ---
        $s3plieg = ($talla_cm > 0) ? ($pliegue_triceps + $pliegue_subescapular + $pliegue_supraespinal) * 170.18 / $talla_cm : 0;
        $hwr = ($peso_kg > 0) ? $talla_cm / ($peso_kg ** 0.3333) : 0;
        
        $endo = -0.7182 + (0.1451 * $s3plieg) - (0.00068 * ($s3plieg ** 2)) + (0.0000014 * ($s3plieg ** 3));
        $meso = (0.858 * $diametro_humero_biepicondilar) + (0.601 * $diametro_femoral_biepicondilar) + (0.188 * ($perimetro_brazo_tenso - ($pliegue_triceps / 10))) + (0.161 * ($perimetro_pantorrilla_maxima - ($pliegue_pierna / 10))) - ($talla_cm * 0.131) + 4.5;
        $ecto = ($hwr >= 40.75) ? ((0.732 * $hwr) - 28.58) : (($hwr > 38.25) ? ((0.463 * $hwr) - 17.63) : 0.1);
        
        $x = $ecto - $endo;
        $y = 2 * $meso - ($endo + $ecto);

        // s6pi is sum of 6 folds: triceps + subscap + suprailiac + abdominal + thigh + calf? 
        // Wait, ecuaciones.php line 454: $s6pi = $pliegue_triceps + $pliegue_subescapular + $pliegue_supraespinal + $pliegue_abdominal + $pliegue_muslo + $pliegue_pierna;
        // This is exactly $suma_yuhasz. Let's reuse it or recalculate for clarity. Recalculate is safer if code moves.
        $s6pl = $pliegue_triceps + $pliegue_subescapular + $pliegue_supraespinal + $pliegue_abdominal + $pliegue_muslo + $pliegue_pierna;

        $results['Somatotipo'] = [
             's6pl' => $s6pl,
             'imc' => $imc,
             's3plieg' => $s3plieg,
             'hwr' => $hwr,
             'endo' => $endo,
             'meso' => $meso,
             'ecto' => $ecto,
             'x' => $x,
             'y' => $y,
        ];

        // --- PROPORCIONALIDAD ---
        $porcentaje_envergadura_relativa = ($talla_cm != 0) ? ($envergadura_cm / $talla_cm * 100) : 0;
        $clasificacion_envergadura = ($porcentaje_envergadura_relativa >= 100) ? "Mayor" : (($porcentaje_envergadura_relativa < 100) ? "Menor" : (($porcentaje_envergadura_relativa == 100) ? "Igual" : ""));
        
        $T_E = 0; // Placeholder as in equations
        $clasificacion_t_e = ($T_E >= -1) ? "Talla adecuada para la edad" : (($T_E < -2) ? "Talla baja para la edad" : ((($T_E >= -2) && ($T_E < -1)) ? "Riesgo de retraso en talla" : ""));
        
        $imc_e = 0; // Placeholder as in equations
        $clasificacion_imc_e = ($imc_e > 2) ? "Obesidad" : (($imc_e < -2) ? "Delgadez" : ((($imc_e > 1) && ($imc_e <= 2)) ? "Sobrepeso" : ((($imc_e >= -1) && ($imc_e <= 1)) ? "Adecuado" : ((($imc_e >= -2) && ($imc_e < -1)) ? "Riesgo Delgadez" : ""))));
        
        $indice_cormico = ($talla_cm > 0) ? ($talla_sentado_corregida_cm / $talla_cm) * 100 : 0;
        $clasificacion_indice_cormico = (($sexo === "F") && ($indice_cormico <= 52)) ? "Braquicormico" : ((($sexo === "M") && ($indice_cormico <= 51)) ? "Braquicormico" : ((($sexo === "F") && ($indice_cormico > 52) && ($indice_cormico <= 54)) ? "Metrocormico" : ((($sexo === "M") && ($indice_cormico > 51) && ($indice_cormico <= 53)) ? "Metrocormico" : ((($sexo === "F") && ($indice_cormico > 54)) ? "Macrocormico" : ((($sexo === "M") && ($indice_cormico > 53)) ? "Macrocormico" : "")))));
        
        $irmi = ($talla_sentado_corregida_cm > 0) ? (($talla_cm - $talla_sentado_corregida_cm) / $talla_sentado_corregida_cm) * 100 : 0;
        $clasificacion_irmi = ($irmi < 84.9) ? "Braquiesquelico" : ((($irmi >= 85) && ($irmi <= 89.9)) ? "Metroesquelico" : (($irmi > 89.9) ? "Macroesquelico" : ""));
        
        $lres = ($talla_cm > 0) ? (($longitud_acromial_radial + $longitud_radial_estiloidea + $longitud_medial_estiloidea_dactilar) / $talla_cm) * 100 : 0;
        $clasificacion_lres = ($lres < 45) ? "Braquibraquial" : ((($lres >= 45) && ($lres < 47)) ? "Metrobraquial" : (($lres >= 47) ? "Macrobraquial" : ""));
        
        $indice_muslo_oseo = ($masa_osea_kg_final != 0) ? ($masa_total_muscular_kg_final / $masa_osea_kg_final) : 0;
        $clasificacion_imo = (($sexo === "F") && ($indice_muslo_oseo <= 2.9)) ? "Desnutricion Calorico Proteica" : ((($sexo === "M") && ($indice_muslo_oseo <= 3.7)) ? "Desnutricion Calorico Proteica" : ((($sexo === "F") && ($indice_muslo_oseo >= 3) && ($indice_muslo_oseo <= 4.2)) ? "Normal" : ((($sexo === "M") && ($indice_muslo_oseo >= 3.8) && ($indice_muslo_oseo <= 4.9)) ? "Normal" : ((($sexo === "F") && ($indice_muslo_oseo > 4.3)) ? "Alterado" : ((($sexo === "M") && ($indice_muslo_oseo > 5)) ? "Alterado" : "")))));

        $results['Proporcionalidad'] = [
            'porcentaje_envergadura_relativa' => $porcentaje_envergadura_relativa,
            'clasificacion_envergadura' => $clasificacion_envergadura,
            'indice_cormico' => $indice_cormico,
            'clasificacion_indice_cormico' => $clasificacion_indice_cormico,
            'irmi' => $irmi,
            'clasificacion_irmi' => $clasificacion_irmi,
            'lres' => $lres,
            'clasificacion_lres' => $clasificacion_lres,
            'indice_muslo_oseo' => $indice_muslo_oseo,
            'clasificacion_imo' => $clasificacion_imo
        ];

        // --- MADURACION ---
        $long_piernas = $talla_cm - $talla_sentado_corregida_cm;
        $i_cormico_mad = ($talla_cm > 0) ? ($talla_sentado_corregida_cm / $talla_cm) * 100 : 0; // Rename to avoid conflict if any, though scope is safe
        $imc_maduracion = ($talla_cm > 0) ? $peso_kg / (($talla_cm/100) ** 2) : 0;
        
        $indice_maduracion = ($sexo == "M") ? 
            (-9.236 + (0.0002708 * $long_piernas * $talla_sentado_corregida_cm) - (0.001663 * $edad * $long_piernas) + (0.007216 * $edad * $talla_sentado_corregida_cm) + (0.02292 * ($peso_kg / (($talla_cm > 0) ? ($talla_cm / 100) : 1)))) : 
            (-9.376 + (0.0001882 * $long_piernas * $talla_sentado_corregida_cm) + (0.0022 * $edad * $long_piernas) + (0.005841 * $edad * $talla_sentado_corregida_cm) - (0.002658 * $edad * $peso_kg) + (0.07693 * ($peso_kg / (($talla_cm > 0) ? ($talla_cm / 100) : 1))));
            
        $edad_phv = $edad - $indice_maduracion;
        $clasificacion_maduracion = ($edad_phv < 13) ? "TEMP" : (($edad_phv < 15) ? "NORM" : "TARD");
        $falta_cm = (float)($additional['growth_remaining_cm'] ?? 0);
        $est_adult_est = $talla_cm + $falta_cm;

        $results['Maduracion'] = [
            'indice_maduracion' => $indice_maduracion,
            'edad_phv' => $edad_phv,
            'clasificacion_maduracion' => $clasificacion_maduracion,
            'est_adult_est' => $est_adult_est,
            'falta_cm' => $falta_cm
        ];

        // --- COMPOSICION CORPORAL (Called MADURACION 2 in prompt likely) ---
        $icc = ($perimetro_cadera > 0) ? $perimetro_cintura / $perimetro_cadera : 0;
        $riesgo = ($icc == 0) ? " " : (($sexo == "M") ? (($edad < 20) ? "N/A" : (($edad < 30) ? (($icc < 0.83) ? "Bajo" : (($icc < 0.89) ? "Moderado" : (($icc <= 0.94) ? "Alto" : "Muy alto"))) : (($edad < 40) ? (($icc < 0.84) ? "Bajo" : (($icc < 0.92) ? "Moderado" : (($icc <= 0.96) ? "Alto" : "Muy alto"))) : (($edad < 50) ? (($icc < 0.88) ? "Bajo" : (($icc < 0.96) ? "Moderado" : (($icc <= 1) ? "Alto" : "Muy alto"))) : (($edad < 60) ? (($icc < 0.9) ? "Bajo" : (($icc < 0.97) ? "Moderado" : (($icc <= 1.02) ? "Alto" : "Muy alto"))) : (($edad < 70) ? (($icc < 0.91) ? "Bajo" : (($icc < 0.99) ? "Moderado" : (($icc <= 1.03) ? "Alto" : "Muy alto"))) : (($edad >= 70) ? "N/A" : "N/A"))))))) : (($sexo == "F") ? (($edad < 20) ? "N/A" : (($edad < 30) ? (($icc < 0.71) ? "Bajo" : (($icc < 0.78) ? "Moderado" : (($icc <= 0.82) ? "Alto" : "Muy alto"))) : (($edad < 40) ? (($icc < 0.72) ? "Bajo" : (($icc < 0.79) ? "Moderado" : (($icc <= 0.84) ? "Alto" : "Muy alto"))) : (($edad < 50) ? (($icc < 0.73) ? "Bajo" : (($icc < 0.8) ? "Moderado" : (($icc <= 0.87) ? "Alto" : "Muy alto"))) : (($edad < 60) ? (($icc < 0.74) ? "Bajo" : (($icc < 0.82) ? "Moderado" : (($icc <= 0.88) ? "Alto" : "Muy alto"))) : (($edad < 70) ? (($icc < 0.76) ? "Bajo" : (($icc < 0.84) ? "Moderado" : (($icc <= 0.9) ? "Alto" : "Muy alto"))) : (($edad >= 70) ? "N/A" : "N/A"))))))) : null));
        
        $complexion = ($perimetro_muneca > 0) ? $talla_cm / $perimetro_muneca : 0;
        $clasificacion_complexion = ($complexion == 0) ? " " : (($sexo == "M") ? (($complexion > 10.39) ? "Pequeña" : (($complexion > 9.59) ? "Mediana" : (($complexion < 9.6) ? "Recia" : null))) : (($sexo == "F") ? (($complexion > 10.99) ? "Pequeña" : (($complexion > 10.09) ? "Mediana" : (($complexion < 10.1) ? "Recia" : null))) : null));

        $results['Composicion_Corporal'] = [
            'icc' => $icc,
            'riesgo' => $riesgo,
            'complexion' => $complexion,
            'clasificacion_complexion' => $clasificacion_complexion
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
