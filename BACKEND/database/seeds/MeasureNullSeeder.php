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
        $patientId = self::createOrGetPatient('999999999', 'Null', 'Test Patient', 'null.test@example.com');
        
        // 1. History - Explicit Nulls
        HistoryGeneralModel::create([
            'patient_id' => $patientId,
            'birth_date' => null, 
            'care_date' => date('Y-m-d H:i:s'),
            'social_stratum' => null,
            'cohabiting_people' => 1
        ]);

        // 2. Measure General
        self::upsert(MeasureGeneralModel::class, $patientId, [
            'patient_id' => $patientId,
            'occupation_sport' => null,
            'category_modality' => 'Sedentary',
            'anthropometry' => 'Si',
            'sex' => 'M',
            'weight_kg' => 70.0,
            'height_cm' => 175.0,
            'wingspan_cm' => null 
        ]);

        // 3. Folds - Partial
        self::upsert(MeasureFoldsModel::class, $patientId, [
            'patient_id' => $patientId,
            'triceps' => 10.0,
            'subspcapular' => null,
            'biceps' => 5.0,
            'pectoral' => null
        ]);

        // 4. Perimeters - Partial
        self::upsert(MeasurePerimetersModel::class, $patientId, [
            'patient_id' => $patientId,
            'waist' => null,
            'hip' => 90.0
        ]);

        // 5. Lengths - Partial
        self::upsert(MeasureLenghtsModel::class, $patientId, [
            'patient_id' => $patientId,
            'acromial_radial' => 30.0
        ]);

        // 6. Diameters - All Null
        self::upsert(MeasureDiametersModel::class, $patientId, ['patient_id' => $patientId]);

        echo "Specific Null Patient (999999999) created.\n";
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
        $exists = UsersModel::query()->where('email', '=', $email)->first();
        if (!$exists) {
            $user = UsersModel::create([
                'names' => $name, 'surnames' => $surname, 'email' => $email, 'password' => 'password123',
                'phone' => '3000000000', 'id_card' => $idCard, 'document_type_id' => 1, 'state' => 'Activo', 'email_verified' => 0
            ]);
            $id = $user['id'];
            UserInRolModel::create(['user_id' => $id, 'rol_id' => 3]);
            return $id;
        } else {
            $id = $exists['id'];
             $hasRole = UserInRolModel::query()->where('user_id', '=', $id)->where('rol_id', '=', 3)->count();
             if ($hasRole == 0) UserInRolModel::create(['user_id' => $id, 'rol_id' => 3]);
            return $id;
        }
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
