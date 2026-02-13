<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use Core\Request;
use App\Models\UsersModel;
use App\Models\UserInRolModel;

class NutritionistController extends BaseController
{
    public function __construct()
    {
        // Constructor del controlador
        parent::__construct();
    }

    public function index()
    {
        $this->json(['message' => 'User index method called']);
    }

    /////////////////////////////////////////

    public function createNutritionist()
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

        if ($exists_id > 0) {
            $this->json(['message' => 'id_card ya existe'], 409);
            return;
        }

        $exists_email = UsersModel::query()
            ->where('deleted_at', 'IS', null)
            ->where('email', '=', $payload['email'])
            ->count();

        if ($exists_email > 0) {
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
            'user_id' => $userId,
            'rol_id' => 2
        ]);

        $this->json(['message' => 'Nutricionista creado correctamente'], 201);
    }

    public function readNutritionist()
    {
        // 1) Traer los user_id que tengan rol nutricionista (rol_id = 2)
        $nutritionistRows = UserInRolModel::query()
            ->select('user_id')
            ->where('rol_id', '=', 2)
            ->get();

        $ids = [];

        if (is_array($nutritionistRows)) {
            foreach ($nutritionistRows as $r) {
                if (isset($r['user_id'])) $ids[] = (int)$r['user_id'];
            }
        }

        if (!$ids) {
            $this->json(['ok' => true, 'users' => []], 200);
            return;
        }

        // 2) Como el QueryBuilder NO soporta IN con arrays, consultamos 1 a 1
        $users = [];

        foreach ($ids as $userId) {
            $u = UsersModel::query()
                ->select('id', 'names', 'surnames', 'email', 'phone', 'id_card', 'state', 'document_type_id', 'plan_id', 'email_verified', 'date_active_plan', 'last_update_password')
                ->where('deleted_at', 'IS', null)
                ->where('id', '=', $userId)
                ->first();

            if ($u && empty($u['deleted_at'])) {
                $users[] = $u;
            }
        }

        $this->json($users, 200);
    }


    public function updateNutritionist()
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

        $isNutritionist = UserInRolModel::query()
            ->where('user_id', '=', $user['id'])
            ->where('rol_id', '=', 2)
            ->count();

        if ((int)$isNutritionist <= 0) {
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

            if ($emailExists > 0) {
                $this->json(['message' => 'email ya existe'], 409);
                return;
            }
        }

        $updatableFields = array_keys($optionalSchema);
        $updates = [];

        foreach ($updatableFields as $field) {
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
            $this->json(['ok' => true, 'message' => 'No se detectaron cambios'], 200);
            return;
        }

        UsersModel::update($user['id'], $updates);

        $this->json(['message' => 'Nutricionista actualizado correctamente'], 200);
    }

    public function deleteNutritionist()
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

        $isNutritionist = UserInRolModel::query()
            ->where('user_id', '=', $user['id'])
            ->where('rol_id', '=', 2)
            ->count();

        if ((int)$isNutritionist <= 0) {
            $this->json(['message' => 'Operación no permitida'], 403);
            return;
        }

        UsersModel::update($user['id'], [
            'deleted_at' => date('Y-m-d H:i:s')
        ]);

        $this->json(['message' => 'Nutricionista eliminado correctamente'], 200);
    }
}
