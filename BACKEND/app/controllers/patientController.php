<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\UsersModel;
use App\Models\UserInRolModel;
use App\Models\HistoryGeneralModel;
use App\Models\HistoryInfoHealthModel;
use App\Models\HistoryPsychosocialConditionsModel;
use App\Models\HistoryFeedingModel;
use App\Models\HistoryPhysicalActivityModel;
use App\Models\MeasureGeneralModel;
use App\Models\MeasureFoldsModel;
use App\Models\MeasurePerimetersModel;
use App\Models\MeasureLenghtsModel;
use App\Models\MeasureDiametersModel;
use Core\Request;

class patientController extends BaseController
{
    public function __construct()
    {
        // Constructor del controlador
        parent::__construct();
    }

    private function initClinicalHistorySingleTables(int $patientId): array
    {
        $singleTables = [
            HistoryGeneralModel::class,
            HistoryInfoHealthModel::class,
            HistoryPsychosocialConditionsModel::class,
            HistoryFeedingModel::class,
            HistoryPhysicalActivityModel::class,
            MeasureGeneralModel::class,
            MeasureFoldsModel::class,
            MeasurePerimetersModel::class,
            MeasureLenghtsModel::class,
            MeasureDiametersModel::class
        ];

        $created = [];
        $skipped = [];

        foreach ($singleTables as $modelClass) {
            if (!method_exists($modelClass, 'query') || !method_exists($modelClass, 'create')) {
                continue;
            }

            $exists = $modelClass::query()
                ->where('patient_id', '=', $patientId)
                ->where('deleted_at', 'IS', null)
                ->count();

            if ((int)$exists > 0) {
                $skipped[] = $modelClass;
                continue;
            }

            $modelClass::create([
                'patient_id' => $patientId
            ]);

            $created[] = $modelClass;
        }

        return [
            'created' => $created,
            'skipped' => $skipped
        ];
    }

    public function createPatient()
    {
        $request = Request::getInstance();
        $payload = $request->all();

        $schema = [
            'names' => 'string',
            'surnames' => 'string',
            'email' => 'string',
            'password' => 'string',
            'phone' => 'string',
            'id_card' => 'string',
            'document_type_id' => 'int',
            'nutritionist_id' => 'int' // New required field
        ];

        $result = \Utils\validate_keys::validateTypes($payload, $schema);

        if (!$result['ok']) {
            $this->json(['message' => 'Validación fallida en campos requeridos'], 422);
            return;
        }

        // Validate nutritionist existence (optional but recommended)
        $nutritionist = UsersModel::query()
            ->select('users.id')
            ->join('user_in_rol', 'user_in_rol.user_id = users.id')
            ->where('users.id', '=', $payload['nutritionist_id'])
            ->where('user_in_rol.rol_id', '=', 2) // Assuming 2 is Nutritionist
            ->first();
        if (!$nutritionist) {
             $this->json(['message' => 'Nutricionista no encontrado'], 404);
             return;
        }

        $exists_id = UsersModel::query()
            // ->where('deleted_at', 'IS', null)
            ->where('id_card', '=', $payload['id_card'])
            ->count();

        if ((int)$exists_id > 0) {
            $this->json(['message' => 'El id_card ya existe'], 409);
            return;
        }

        $exists_email = UsersModel::query()
            ->where('deleted_at', 'IS', null)
            ->where('email', '=', $payload['email'])
            ->count();

        if ((int)$exists_email > 0) {
            $this->json(['message' => 'El email ya existe'], 409);
            return;
        }

        $newUser = UsersModel::create([
            'names' => $payload['names'],
            'surnames' => $payload['surnames'],
            'phone' => $payload['phone'],
            'id_card' => $payload['id_card'],
            'email' => $payload['email'],
            'password' => password_hash($payload['password'], PASSWORD_BCRYPT),
            'state' => 'Activo',
            'document_type_id' => $payload['document_type_id'],
            'email_verified' => 0
        ]);

        $userId = $newUser['id'] ?? null;

        if (!$userId) {
            $this->json(['message' => 'Error creando usuario'], 500);
            return;
        }

        UserInRolModel::create([
            'user_id' => (int)$userId,
            'rol_id' => 3
        ]);

        // Link to Nutritionist
        \App\Models\NutritionistPatientModel::create([
            'nutritionist_id' => (int)$payload['nutritionist_id'],
            'patient_id' => (int)$userId,
            'start_at' => date('Y-m-d H:i:s'),
            'status' => 'active'
        ]);

        $init = $this->initClinicalHistorySingleTables((int)$userId);

        $this->json(['message' => 'Paciente creado correctamente'], 201);
    }

    public function readPatients()
    {
        $rows = UserInRolModel::query()
            ->select('user_id')
            ->where('rol_id', '=', 3)
            ->get();

        $ids = [];

        if (is_array($rows)) {
            foreach ($rows as $r) {
                if (isset($r['user_id'])) $ids[] = (int)$r['user_id'];
            }
        }

        if (!$ids) {
            $this->json(['ok' => true, 'patients' => []], 200);
            return;
        }

        $patients = [];

        foreach ($ids as $userId) {
            $u = UsersModel::query()
                ->select('id', 'names', 'surnames', 'email', 'phone', 'id_card', 'state', 'document_type_id', 'plan_id', 'email_verified', 'date_active_plan', 'last_update_password')
                ->where('deleted_at', 'IS', null)
                ->where('id', '=', $userId)
                ->first();

            if ($u && empty($u['deleted_at'])) {
                $patients[] = $u;
            }
        }

        $this->json($patients, 200);
    }

    public function updatePatient()
    {
        $request = Request::getInstance();
        $payload = $request->all();

        if (!isset($payload['id_card']) || !is_string($payload['id_card'])) {
            $this->json(['message' => 'Validación fallida: id_card requerido'], 422);
            return;
        }

        $user = UsersModel::query()
            ->where('id_card', '=', $payload['id_card'])
            ->first();

        if (!$user) {
            $this->json(['message' => 'Usuario no encontrado'], 404);
            return;
        }

        if (!empty($user['deleted_at'])) {
            $this->json(['message' => 'Usuario ya eliminado'], 409);
            return;
        }

        $isPatient = UserInRolModel::query()
            ->where('user_id', '=', $user['id'])
            ->where('rol_id', '=', 3)
            ->count();

        if ((int)$isPatient <= 0) {
            $this->json(['message' => 'Operación no permitida'], 403);
            return;
        }

        $optionalSchema = [
            'names' => 'string',
            'surnames' => 'string',
            'phone' => 'string',
            'profile_image' => 'string',
            'email' => 'string',
            'password' => 'string',
            'state' => 'string',
            'document_type_id' => 'int',
            'plan_id' => 'int',
            'email_verified' => 'bool',
            'date_active_plan' => 'string',
            'last_update_password' => 'string'
        ];

        $typeErrors = [];
        foreach ($optionalSchema as $key => $type) {
            if (!array_key_exists($key, $payload)) continue;
            if ($payload[$key] === null) continue;

            $ok = match ($type) {
                'string' => is_string($payload[$key]),
                'int' => is_int($payload[$key]),
                'bool' => is_bool($payload[$key]),
                default => false
            };

            if (!$ok) $typeErrors[$key] = true;
        }

        if ($typeErrors) {
            $this->json(['message' => 'Error de tipo en los datos'], 422);
            return;
        }

        if (array_key_exists('email', $payload) && $payload['email'] !== null && $payload['email'] !== $user['email']) {
            $emailExists = UsersModel::query()
                ->where('email', '=', $payload['email'])
                ->where('deleted_at', 'IS', null)
                ->count();

            if ((int)$emailExists > 0) {
                $this->json(['message' => 'El email ya existe'], 409);
                return;
            }
        }

        $updates = [];
        foreach (array_keys($optionalSchema) as $field) {
            if (!array_key_exists($field, $payload)) continue;

            if ($field === 'password') {
                if ($payload['password'] === null || $payload['password'] === '') continue;
                $same = !empty($user['password']) && password_verify($payload['password'], $user['password']);
                if (!$same) {
                    $updates['password'] = password_hash($payload['password'], PASSWORD_BCRYPT);
                    $updates['last_update_password'] = date('Y-m-d H:i:s');
                }
                continue;
            }

            $newVal = $payload[$field];
            $oldVal = $user[$field] ?? null;

            if ($newVal !== $oldVal) {
                $updates[$field] = $newVal;
            }
        }

        if (empty($updates)) {
            $this->json(['message' => 'No se detectaron cambios'], 200);
            return;
        }

        UsersModel::update($user['id'], $updates);

        // Sync with NutritionistPatientModel if state changed
        if (isset($updates['state'])) {
            $newStatus = ($updates['state'] === 'Activo') ? 'active' : 'inactive';
            
            // QueryBuilder update might not support joins directly, so we update by patient_id
            // Assuming we update ALL relations for this patient (or just the active ones?)
            // Let's update all relations for this patient where deleted_at is null
            $relations = \App\Models\NutritionistPatientModel::query()
                ->where('patient_id', '=', $user['id'])
                ->get();
            
            foreach ($relations as $rel) {
                 $pivotUpdates = ['status' => $newStatus];
                 if ($newStatus === 'inactive' && empty($rel['end_at'])) {
                     $pivotUpdates['end_at'] = date('Y-m-d H:i:s');
                 } elseif ($newStatus === 'active') {
                     $pivotUpdates['end_at'] = null; // Clear end_at if reactivated
                 }
                 \App\Models\NutritionistPatientModel::update($rel['id'], $pivotUpdates);
            }
        }

        $this->json(['message' => 'Paciente actualizado correctamente'], 200);
    }

    public function deletePatient()
    {
        $request = Request::getInstance();
        $payload = $request->all();

        $schema = [
            'id_card' => 'string'
        ];

        $result = \Utils\validate_keys::validateTypes($payload, $schema);

        if (!$result['ok']) {
            $this->json(['message' => 'Validación fallida'], 422);
            return;
        }

        $user = UsersModel::query()
            ->where('id_card', '=', $payload['id_card'])
            ->first();

        if (!$user) {
            $this->json(['message' => 'Usuario no encontrado'], 404);
            return;
        }

        if (!empty($user['deleted_at'])) {
            $this->json(['message' => 'Usuario ya eliminado'], 409);
            return;
        }

        $isPatient = UserInRolModel::query()
            ->where('user_id', '=', $user['id'])
            ->where('rol_id', '=', 3)
            ->count();

        if ((int)$isPatient <= 0) {
            $this->json(['message' => 'Operación no permitida'], 403);
            return;
        }

        UsersModel::update($user['id'], [
            'deleted_at' => date('Y-m-d H:i:s')
        ]);

        // Sync with NutritionistPatientModel
        $relations = \App\Models\NutritionistPatientModel::query()
            ->where('patient_id', '=', $user['id'])
            ->where('deleted_at', 'IS', null)
            ->get();
        
        foreach ($relations as $rel) {
            \App\Models\NutritionistPatientModel::update($rel['id'], [
                'deleted_at' => date('Y-m-d H:i:s'),
                'status' => 'inactive',
                'end_at' => date('Y-m-d H:i:s')
            ]);
        }

        $this->json(['message' => 'Paciente eliminado correctamente'], 200);
    }
}
