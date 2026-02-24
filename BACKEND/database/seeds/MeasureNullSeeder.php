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

class MeasureNullSeeder
{
    public static function run(): void
    {
        self::createSpecificNullPatient();
        self::createRandomNullPatient();
        echo "Null Seeding completed.\n";
    }

    private static function createSpecificNullPatient()
    {
        $patientIdCard = self::createOrGetPatient('999999999', 'Null', 'Test Patient', 'null.test@example.com');
        
        // 1. History - All Valid now
        HistoryGeneralModel::create([
            'patient_id' => $patientIdCard,
            'birth_date' => '1990-01-01', 
            'care_date' => date('Y-m-d H:i:s'),
            'social_stratum' => 3,
            'cohabiting_people' => 1,
            'health_provider' => 'EPS',
            'education_level' => 'Universitario',
            'occupation' => 'Empleado'
        ]);

        // 2. Measure General - All Valid
        self::upsert(MeasureGeneralModel::class, $patientIdCard, [
            'patient_id' => $patientIdCard,
            'occupation_sport' => 'None',
            'category_modality' => 'Sedentary',
            'anthropometry' => 'Si',
            'sex' => 'M', // Need Male for some specific formulas or F? User didn't specify, default to M or F. M is fine.
            'weight_kg' => 70.0,
            'height_cm' => 175.0,
            'sitting_height_cm' => 90.0,
            'bench_height_cm' => 45.0,
            'wingspan_cm' => 176.0,
            'ethnicity' => 'M',
            'sport_line' => 'None',
            'control' => 'No'
        ]);

        // 3. Folds - ONLY TRICEPS NULL
        self::upsert(MeasureFoldsModel::class, $patientIdCard, [
            'patient_id' => $patientIdCard,
            'triceps' => 8, // <--- THE ONLY NULL
            'subspcapular' => 12.0,
            'biceps' => 5.0,
            'pectoral' => 10.0,
            'axillary' => 8.0,
            'suprailiac' => 15.0,
            'supraspinal' => 8.0,
            'abdominal' => 20.0,
            'thigh' => 15.0,
            'leg' => 10.0
        ]);

        // 4. Perimeters - Valid
        self::upsert(MeasurePerimetersModel::class, $patientIdCard, [
            'patient_id' => $patientIdCard,
            'head' => null,
            'neck' => 38.0,
            'arm_relaxed' => 30.0,
            'arm_tensed' => 32.0,
            'forearm' => 28.0,
            'wrist' => 17.0,
            'mesosternal' => 95.0,
            'waist' => 85.0,
            'abdominal' => 88.0,
            'hip' => 100.0,
            'thigh_max' => 55.0,
            'thigh_mid' => 50.0,
            'calf_max' => 38.0,
            'ankle_min' => 22.0
        ]);

        // 5. Lengths - Valid
        self::upsert(MeasureLenghtsModel::class, $patientIdCard, [
            'patient_id' => $patientIdCard,
            'acromial_radial' => 32.0,
            'radial_styloid' => 25.0,
            'medial_styloid_dactilar' => 19.0,
            'ileospinal' => 90.0,
            'trochanteric' => 92.0,
            'trochanteric_tibial_lateral' => 45.0,
            'tibial_lateral' => 40.0,
            'tibial_medial_malleolar_medial' => 38.0,
            'foot' => 26.0
        ]);

        // 6. Diameters - Valid
        self::upsert(MeasureDiametersModel::class, $patientIdCard, [
            'patient_id' => $patientIdCard,
            'biacromial' => 40.0,
            'biileocrestal' => 28.0,
            'antero_posterior_abdominal' => 20.0,
            'thorax_transverse' => 28.0,
            'thorax_antero_posterior' => 22.0,
            'humerus_biepicondylar' => 7.0,
            'wrist_bistyloid' => 5.5,
            'hand' => 19.0,
            'femur_biepicondylar' => 9.5,
            'ankle_bimalleolar' => 7.0,
            'foot' => 10.0 // Breadth? Usually smaller.
        ]);

        echo "Specific Null Patient (999999999) created with ONLY triceps = NULL.\n";
    }

    private static function createRandomNullPatient()
    {
        $patientId = self::createOrGetPatient('888888888', 'Random', 'Null Patient', 'random.null@example.com');
        
        // Base Data
        $history = ['patient_id' => $patientId, 'birth_date' => '1995-05-20', 'care_date' => date('Y-m-d H:i:s'), 'social_stratum' => 3, 'cohabiting_people' => 2];
        $general = ['patient_id' => $patientId, 'sex' => 'F', 'weight_kg' => 60.0, 'height_cm' => 165.0, 'sitting_height_cm' => 85.0, 'bench_height_cm' => 45.0];
        $folds = ['patient_id' => $patientId, 'triceps' => 12.0, 'subspcapular' => 14.0, 'biceps' => 8.0, 'suprailiac' => 15.0];
        $perimeters = ['patient_id' => $patientId, 'arm_relaxed' => 28.0, 'waist' => 70.0, 'hip' => 95.0];

        // Randomly Nullify One Field
        $targets = [
            'history' => ['birth_date'], // Required
            'general' => ['weight_kg', 'height_cm', 'sex'], // Required
            'folds' => ['triceps', 'subspcapular'], // Optional
            'perimeters' => ['waist', 'hip'] // Optional
        ];

        $sectionKey = array_rand($targets);
        $fieldKey = $targets[$sectionKey][array_rand($targets[$sectionKey])];

        echo "Randomly selected field to be NULL: $sectionKey -> $fieldKey\n";

        // Apply Null
        if ($sectionKey === 'history') $history[$fieldKey] = null;
        if ($sectionKey === 'general') $general[$fieldKey] = null;
        if ($sectionKey === 'folds') $folds[$fieldKey] = null;
        if ($sectionKey === 'perimeters') $perimeters[$fieldKey] = null;

        // Insert
        HistoryGeneralModel::create($history);
        self::upsert(MeasureGeneralModel::class, $patientId, $general);
        self::upsert(MeasureFoldsModel::class, $patientId, $folds);
        self::upsert(MeasurePerimetersModel::class, $patientId, $perimeters);

        echo "Random Null Patient (888888888) created.\n";
    }

    private static function createOrGetPatient($idCard, $name, $surname, $email)
    {
        $id = null;
        $exists = UsersModel::query()->where('email', '=', $email)->first();
        
        if (!$exists) {
            $user = UsersModel::create([
                'names' => $name, 'surnames' => $surname, 'email' => $email, 'password' => 'password123',
                'phone' => '3000000000', 'id_card' => $idCard, 'document_type_id' => 1, 'state' => 'Activo', 'email_verified' => 0
            ]);
            $id = $user['id'];
            UserInRolModel::create(['user_id' => $id, 'rol_id' => 3]);
        } else {
            $id = $exists['id'];
            $hasRole = UserInRolModel::query()->where('user_id', '=', $id)->where('rol_id', '=', 3)->count();
            if ($hasRole == 0) UserInRolModel::create(['user_id' => $id, 'rol_id' => 3]);
        }

        // Link to Nutritionist (Pivot)
        $nutritionist = UsersModel::query()
            ->select('users.id')
            ->join('user_in_rol', 'user_in_rol.user_id = users.id')
            ->where('user_in_rol.rol_id', '=', 2)
            ->first();

        if ($nutritionist) {
            $nutriId = $nutritionist['id'];
            $pivotExists = \App\Models\NutritionistPatientModel::query()
                ->where('patient_id', '=', $id)
                ->where('nutritionist_id', '=', $nutriId)
                ->first();
            
            if (!$pivotExists) {
                \App\Models\NutritionistPatientModel::create([
                    'nutritionist_id' => $nutriId,
                    'patient_id' => $id,
                    'start_at' => date('Y-m-d H:i:s'),
                    'status' => 'active'
                ]);
            }
        }

        return $id;
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
