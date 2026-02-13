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
            HistoryPhysicalActivityModel::class
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
            'document_type_id' => 'int'
        ];

        $result = \Utils\validate_keys::validateTypes($payload, $schema);

        if (!$result['ok']) {
            $this->json(['message' => 'Validación fallida'], 422);
            return;
        }

        $exists_id = UsersModel::query()
            // ->where('deleted_at', 'IS', null)
            ->where('id_card', '=', $payload['id_card'])
            ->count();

        if ((int)$exists_id > 0) {
            $this->json(['message' => 'id_card ya existe'], 409);
            return;
        }

        $exists_email = UsersModel::query()
            ->where('deleted_at', 'IS', null)
            ->where('email', '=', $payload['email'])
            ->count();

        if ((int)$exists_email > 0) {
            $this->json(['message' => 'email ya existe'], 409);
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

        $init = $this->initClinicalHistorySingleTables((int)$userId);

        $this->json([
            'message' => 'Paciente creado correctamente',
        ], 201);
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

            if (!$ok) $typeErrors[$key] = "type_{$type}";
        }

        if ($typeErrors) {
            $this->json(['message' => 'Validación fallida'], 422);
            return;
        }

        if (array_key_exists('email', $payload) && $payload['email'] !== null && $payload['email'] !== $user['email']) {
            $emailExists = UsersModel::query()
                ->where('email', '=', $payload['email'])
                ->where('deleted_at', 'IS', null)
                ->count();

            if ((int)$emailExists > 0) {
                $this->json(['message' => 'email ya existe'], 409);
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

        $this->json(['message' => 'Paciente eliminado correctamente'], 200);
    }
}
