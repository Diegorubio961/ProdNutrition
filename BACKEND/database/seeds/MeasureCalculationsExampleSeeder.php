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

class MeasureCalculationsExampleSeeder
{
    public static function run(): void
    {
        // 1. Create Patient (Camila)
        $patientData = [
            'names' => 'Camila',
            'surnames' => 'Álvares Giraldo',
            'email' => 'camila.alvares.giraldo@example.com', // Unique email
            'password' => 'password123', 
            'phone' => '3009876543',
            'id_card' => '1098765432', // Dummy ID
            'document_type_id' => 1, // CC
            'state' => 'Activo',
            'email_verified' => 0
        ];

        // Check availability
        $exists = UsersModel::query()->where('email', '=', $patientData['email'])->first();
        $patientId = null;

        if (!$exists) {
            $createdUser = UsersModel::create($patientData);
            $patientId = $createdUser['id'];
            echo "Patient Camila created with ID: $patientId\n";

            // Assign Role 3 (Patient)
            UserInRolModel::create([
                'user_id' => $patientId,
                'rol_id' => 3
            ]);

            // Create HistoryGeneral
            HistoryGeneralModel::create([
                'patient_id' => $patientId,
                'birth_date' => '1998-04-21',
                'care_date' => '2025-06-15', // From request
                'social_stratum' => 3,
                'cohabiting_people' => 0,
                'health_provider' => 'EPS',
                'education_level' => 'Universitario',
                'occupation' => 'Gym' // From request
            ]);

        } else {
            $patientId = $exists['id'];
            echo "Patient Camila already exists with ID: $patientId\n";
             // Ensure role exists
             $hasRole = UserInRolModel::query()->where('user_id', '=', $patientId)->where('rol_id', '=', '3')->count();
             if ($hasRole == 0) {
                 UserInRolModel::create(['user_id' => $patientId, 'rol_id' => 3]);
             }
        }

        // 2. Link to Nutritionist (Pivot)
         $nutritionist = UsersModel::query()
            ->select('users.id')
            ->join('user_in_rol', 'user_in_rol.user_id = users.id')
            ->where('user_in_rol.rol_id', '=', 2)
            ->first();

        if ($nutritionist) {
            $nutriId = $nutritionist['id'];
            $pivotExists = \App\Models\NutritionistPatientModel::query()
                ->where('patient_id', '=', $patientId)
                ->where('nutritionist_id', '=', $nutriId)
                ->first();
            
            if (!$pivotExists) {
                \App\Models\NutritionistPatientModel::create([
                    'nutritionist_id' => $nutriId,
                    'patient_id' => $patientId,
                    'start_at' => date('Y-m-d H:i:s'),
                    'status' => 'active'
                ]);
            }
        }

        // 3. Insert Measure Data

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
            'sitting_height_cm' => 82.8,
            'bench_height_cm' => 40.0,
            'corrected_sitting_height_cm' => (82.8 - 40.0), // 42.8
            'wingspan_cm' => 164.0
        ];
        self::upsert(MeasureGeneralModel::class, $patientId, $measureGeneral);

        // Measure Folds
        $measureFolds = [
            'patient_id' => $patientId,
            'triceps' => 9.0,
            'subspcapular' => 14.0, // Typo in model?? Check MeasureSeeder line 121: 'subspcapular'. I'll follow that pattern.
            'biceps' => 8.0,
            'pectoral' => 1.0,
            'axillary' => 1.0,
            'suprailiac' => 11.0,
            'supraspinal' => 7.0,
            'abdominal' => 15.0,
            'thigh' => 34.5,
            'leg' => 17.0
        ];
        // Check if column name in model is 'subscapular' or 'subspcapular'. MeasureSeeder used 'subspcapular'. 
        // I will trust MeasureSeeder used the correct column name, but it looks like a typo.
        // Let me verify model first if I can? No, I'll stick to MeasureSeeder pattern for now or check model file.
        // Actually, looking at MeasureSeeder line 121: 'subspcapular' => 14.0.
        self::upsert(MeasureFoldsModel::class, $patientId, $measureFolds);

        // Measure Perimeters
        $measurePerimeters = [
            'patient_id' => $patientId,
            'head' => 1.0,
            'neck' => 1.0,
            'arm_relaxed' => 23.8,
            'arm_tensed' => 24.1,
            'forearm' => 1.0,
            'wrist' => 1.0, 
            'mesosternal' => 1.0,
            'waist' => 67.2,
            'abdominal' => 1.0,
            'hip' => 98.5,
            'thigh_max' => 1.0,
            'thigh_mid' => 51.0,
            'calf_max' => 34.5,
            'ankle_min' => 1.0
        ];
        self::upsert(MeasurePerimetersModel::class, $patientId, $measurePerimeters);

        // Measure Lengths
        $measureLengths = [
            'patient_id' => $patientId,
            'acromial_radial' => 1.0,
            'radial_styloid' => 1.0,
            'medial_styloid_dactilar' => 1.0,
            'ileospinal' => 1.0,
            'trochanteric' => 1.0,
            'trochanteric_tibial_lateral' => 1.0,
            'tibial_lateral' => 1.0,
            'tibial_medial_malleolar_medial' => 1.0,
            'foot' => 1.0
        ];
        self::upsert(MeasureLenghtsModel::class, $patientId, $measureLengths);

        // Measure Diameters
        $measureDiameters = [
            'patient_id' => $patientId,
            'biacromial' => 1.0,
            'biileocrestal' => 1.0,
            'antero_posterior_abdominal' => 1.0,
            'thorax_transverse' => 1.0,
            'thorax_antero_posterior' => 1.0,
            'humerus_biepicondylar' => 6.0,
            'wrist_bistyloid' => 4.8,
            'hand' => 1.0,
            'femur_biepicondylar' => 8.8,
            'ankle_bimalleolar' => 1.0,
            'foot' => 1.0
        ];
        self::upsert(MeasureDiametersModel::class, $patientId, $measureDiameters);

        // Measure Additional Variables (Set to defaults/0 based on request not specifying them or implying empty)
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

        echo "Seeding MeasureCalculationsExampleSeeder completed.\n";
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
