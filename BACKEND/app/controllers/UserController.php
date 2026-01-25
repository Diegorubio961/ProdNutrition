<?php

namespace App\Controllers;
use App\Controllers\BaseController;
use Core\Request;
use App\Models\UsersModel;
use App\Models\UserInRolModel;

class UserController extends BaseController
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
            $this->json(['error' => 'Validation failed', 'details' => $result['errors']], 422);
            return;
        }

        $exists_id = UsersModel::query()
            ->where('deleted_at', 'IS', null)
            ->where('id_card', '=', $payload['id_card'])
            ->count();

        if ($exists_id > 0) {
            $this->json(['error' => 'id_card already exists'], 409);
            return;
        }

        $exists_email = UsersModel::query()
            ->where('deleted_at', 'IS', null)
            ->where('email', '=', $payload['email'])
            ->count();

        if ($exists_email > 0) {
            $this->json(['error' => 'email already exists'], 409);
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
            $this->json(['error' => 'User created but id not returned'], 500);
            return;
        }

        $userRol = UserInRolModel::create([
            'user_id' => $userId,
            'rol_id' => 1
        ]);

        $this->json(['ok' => true, 'user' => $newUser, 'role' => $userRol], 201);
    }


    public function readUsers()
    {
        $users = UsersModel::query()
            ->select('id', 'names', 'surnames', 'email', 'phone', 'id_card', 'state', 'document_type_id', 'plan_id', 'email_verified', 'date_active_plan', 'last_update_password')
            ->where('deleted_at', 'IS', null)
            ->get();

        $this->json(['ok' => true, 'users' => $users], 200);
    }

    public function updateUser()
    {
        $request = Request::getInstance();
        $payload = $request->all();

        if (!isset($payload['id_card']) || !is_string($payload['id_card'])) {
            $this->json(['error' => 'Validation failed', 'details' => ['id_card' => 'missing_or_type_string']], 422);
            return;
        }

        $user = UsersModel::query()
            ->where('id_card', '=', $payload['id_card'])
            ->first();

        if (!$user) {
            $this->json(['error' => 'id_card not found'], 404);
            return;
        }

        if (!empty($user['deleted_at'])) {
            $this->json(['error' => 'user already deleted'], 409);
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
            $this->json(['error' => 'Validation failed', 'details' => $typeErrors], 422);
            return;
        }

        if (array_key_exists('email', $payload) && $payload['email'] !== null && $payload['email'] !== $user['email']) {
            $emailExists = UsersModel::query()
                ->where('email', '=', $payload['email'])
                ->where('deleted_at', 'IS', null)
                ->count();

            if ($emailExists > 0) {
                $this->json(['error' => 'email already exists'], 409);
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
            $this->json(['ok' => true, 'message' => 'No changes detected', 'user' => $user], 200);
            return;
        }

        UsersModel::update($user['id'], $updates);

        $updatedUser = UsersModel::query()
            ->where('id_card', '=', $payload['id_card'])
            ->first();

        $this->json(['ok' => true, 'message' => 'User updated', 'updated_fields' => array_keys($updates), 'user' => $updatedUser], 200);
    }

    public function deleteUser()
    {
        $request = Request::getInstance();
        $payload = $request->all();

        $schema = [
            'id_card' => 'string'
        ];

        $result = \Utils\validate_keys::validateTypes($payload, $schema);

        if (!$result['ok']) {
            $this->json(['error' => 'Validation failed', 'details' => $result['errors']], 422);
            return;
        }

        $user = UsersModel::query()
            ->where('id_card', '=', $payload['id_card'])
            ->first();

        if (!$user) {
            $this->json(['error' => 'user not found'], 404);
            return;
        }

        if (!empty($user['deleted_at'])) {
            $this->json(['error' => 'user already deleted'], 409);
            return;
        }

        UsersModel::update($user['id'], [
            'deleted_at' => date('Y-m-d H:i:s')
        ]);

        $this->json(['ok' => true, 'message' => 'User deleted'], 200);
    }
        

}