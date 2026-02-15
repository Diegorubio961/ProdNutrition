<?php

use App\Models\UsersModel;
use App\Models\UserInRolModel;
use App\Models\MeasureGeneralModel;
use App\Models\MeasureFoldsModel;
use App\Models\MeasurePerimetersModel;
use App\Models\MeasureLenghtsModel;
use App\Models\MeasureDiametersModel;
use App\Models\MeasureAdditionalVariablesModel;
use App\Models\HistoryGeneralModel;

class MeasureSeeder
{
    public static function run(): void
    {
        // 1. Create Patient (User) - Logic similar to patientController
        $patientData = [
            'names' => 'Camila',
            'surnames' => 'Álvares Giraldo',
            'email' => 'camila.alvares@example.com',
            'password' => 'password123', // Plain text as requested
            'phone' => '3001234567',
            'id_card' => '1000123456', // Dummy ID
            'document_type_id' => 1, // CC
            'state' => 'Activo',
            'email_verified' => 0
        ];

        // Check availability
        $exists = UsersModel::query()->where('email', '=', $patientData['email'])->first();
        
        if (!$exists) {
            $createdUser = UsersModel::create($patientData);
            $patientId = $createdUser['id'];
            $patientIdCard = $createdUser['id_card'];
            echo "Patient created with ID Card: $patientIdCard\n";

            // Assign Role 3 (Patient)
            UserInRolModel::create([
                'user_id' => $patientId,
                'rol_id' => 3
            ]);

            // Create HistoryGeneral (Init tables logic)
            // '21/04/1998' -> 1998-04-21
            HistoryGeneralModel::create([
                'patient_id' => $patientId,
                'birth_date' => '1998-04-21',
                'care_date' => date('Y-m-d H:i:s'),
                'social_stratum' => 3,
                'cohabiting_people' => 2,
                'health_provider' => 'EPS',
                'education_level' => 'Universitario',
                'occupation' => 'Empleado'
            ]);

        } else {
            $patientId = $exists['id'];
            $patientIdCard = $exists['id_card'];
            echo "Patient already exists with ID Card: $patientIdCard\n";
            // Ensure role exists
             $hasRole = UserInRolModel::query()->where('user_id', '=', $patientId)->where('rol_id', '=', 3)->count();
             if ($hasRole == 0) {
                 UserInRolModel::create(['user_id' => $patientId, 'rol_id' => 3]);
             }
        }

        // 3. Insert Measure Data (Hardcoded from ecuaciones.php)

        // Measure General
        $measureGeneral = [
            'patient_id' => $patientId,
            'occupation_sport' => 'Gym',
            'category_modality' => 'Adulto',
            'anthropometry' => 'Si',
            'control' => '',
            'sex' => 'F',
            'ethnicity' => 'B',
            'sport_line' => '',
            'weight_kg' => 58.2,
            'height_cm' => 163.8,
            'sitting_height_cm' => 0,
            'bench_height_cm' => 43.0,
            'corrected_sitting_height_cm' => -43.0,
            'wingspan_cm' => 164.0
        ];
        self::upsert(MeasureGeneralModel::class, $patientId, $measureGeneral);


        // Measure Folds
        $measureFolds = [
            'patient_id' => $patientId,
            'triceps' => 9.0,
            'subspcapular' => 14.0, 
            'biceps' => 8.0,
            'pectoral' => 0,
            'axillary' => 0,
            'suprailiac' => 11.0,
            'supraspinal' => 7.0,
            'abdominal' => 15.0,
            'thigh' => 34.5,
            'leg' => 17.0
        ];
        self::upsert(MeasureFoldsModel::class, $patientId, $measureFolds);


        // Measure Perimeters
        $measurePerimeters = [
            'patient_id' => $patientId,
            'head' => 0,
            'neck' => 0,
            'arm_relaxed' => 23.8,
            'arm_tensed' => 24.1,
            'forearm' => 0,
            'wrist' => 0, 
            'mesosternal' => 0,
            'waist' => 67.2,
            'abdominal' => 0,
            'hip' => 98.5,
            'thigh_max' => 0,
            'thigh_mid' => 51.0,
            'calf_max' => 34.5,
            'ankle_min' => 0
        ];
        self::upsert(MeasurePerimetersModel::class, $patientId, $measurePerimeters);


        // Measure Lengths
        $measureLengths = [
            'patient_id' => $patientId,
            'acromial_radial' => 0,
            'radial_styloid' => 0,
            'medial_styloid_dactilar' => 0,
            'ileospinal' => 0,
            'trochanteric' => 0,
            'trochanteric_tibial_lateral' => 0,
            'tibial_lateral' => 0,
            'tibial_medial_malleolar_medial' => 0,
            'foot' => 0
        ];
        self::upsert(MeasureLenghtsModel::class, $patientId, $measureLengths);


        // Measure Diameters
        $measureDiameters = [
            'patient_id' => $patientId,
            'biacromial' => 0,
            'biileocrestal' => 0,
            'antero_posterior_abdominal' => 0,
            'thorax_transverse' => 0,
            'thorax_antero_posterior' => 0,
            'humerus_biepicondylar' => 6.0,
            'wrist_bistyloid' => 4.8,
            'hand' => 0,
            'femur_biepicondylar' => 8.8,
            'ankle_bimalleolar' => 0,
            'foot' => 0
        ];
        self::upsert(MeasureDiametersModel::class, $patientId, $measureDiameters);


        // Measure Additional Variables
        $measureAdditional = [
            'patient_id' => $patientId,
            'ideal_fat_percentage' => 0,
            'ideal_fat_percentage_jyp' => 0,
            'ideal_fat_percentage_durning' => 0,
            'height_age_sd' => 0,
            'bmi_age_sd' => 0,
            'growth_remaining_cm' => 0
        ];
        self::upsert(MeasureAdditionalVariablesModel::class, $patientId, $measureAdditional);

        echo "Seeding completed successfully.\n";
    }

    private static function upsert($modelClass, $patientId, $data)
    {
        $exists = $modelClass::query()->where('patient_id', '=', $patientId)->first();
        if ($exists) {
            $modelClass::update($exists['id'], $data);
        } else {
            $modelClass::create($data);
        }
    }
}
