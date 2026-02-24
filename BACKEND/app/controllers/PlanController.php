<?php

namespace App\Controllers;
use App\Controllers\BaseController;
use App\Models\PlansModel;

class PlanController extends BaseController
{
    public function __construct()
    {
        // Constructor del controlador
        parent::__construct();
    }

    public function createPlan()
    {
        $request = \Core\Request::getInstance();
        $payload = $request->all();

        $schema = [
            'name' => 'string',
            'description' => 'string',
            'customer_count' => 'int',
            'details' => 'string',
            'price' => 'string',
            'duration_days' => 'int'
        ];

        $result = \Utils\validate_keys::validateTypes($payload, $schema);

        if (!$result['ok']) {
            $this->json(['error' => 'Validation failed', 'details' => $result['errors']], 422);
            return;
        }

        $newPlan = PlansModel::create([
            'name' => $payload['name'],
            'description' => $payload['description'],
            'customer_count' => $payload['customer_count'],
            'details' => $payload['details'],
            'price' => $payload['price'],
            'duration_days' => $payload['duration_days']
        ]);

        $this->json(['ok' => true, 'plan' => $newPlan], 201);
    }

    public function readPlans()
    {
        $plans = PlansModel::query()
            ->select('id', 'name', 'description', 'customer_count', 'details', 'price', 'duration_days', 'created_at')
            ->where('deleted_at', 'IS', null)
            ->get();

        $this->json(['ok' => true, 'plans' => $plans], 200);
    }

    public function updatePlan()
    {
        $request = \Core\Request::getInstance();
        $payload = $request->all();

        if (!isset($payload['id']) || !is_int($payload['id'])) {
            $this->json(['error' => 'Validation failed', 'details' => ['id' => 'missing_or_type_int']], 422);
            return;
        }

        $plan = PlansModel::query()
            ->where('id', '=', $payload['id'])
            ->first();

        if (!$plan || !empty($plan['deleted_at'])) {
            $this->json(['error' => 'plan not found'], 404);
            return;
        }

        $optionalSchema = [
            'name' => 'string',
            'description' => 'string',
            'customer_count' => 'int',
            'details' => 'string',
            'price' => 'string',
            'duration_days' => 'int'
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

        $updates = [];
        foreach (array_keys($optionalSchema) as $field) {
            if (!array_key_exists($field, $payload)) continue;
            $newVal = $payload[$field];
            $oldVal = $plan[$field] ?? null;

            if ($newVal !== $oldVal) {
                $updates[$field] = $newVal;
            }
        }

        if (empty($updates)) {
            $this->json(['ok' => true, 'message' => 'No changes detected', 'plan' => $plan], 200);
            return;
        }

        PlansModel::update($plan['id'], $updates);

        $updatedPlan = PlansModel::query()
            ->where('id', '=', $plan['id'])
            ->first();

        $this->json(['ok' => true, 'message' => 'Plan updated', 'updated_fields' => array_keys($updates), 'plan' => $updatedPlan], 200);
    }

    public function deletePlan()
    {
        $request = \Core\Request::getInstance();
        $payload = $request->all();

        $schema = [
            'id' => 'int'
        ];

        $result = \Utils\validate_keys::validateTypes($payload, $schema);

        if (!$result['ok']) {
            $this->json(['error' => 'Validation failed', 'details' => $result['errors']], 422);
            return;
        }

        $plan = PlansModel::query()
            ->where('id', '=', $payload['id'])
            ->first();

        if (!$plan) {
            $this->json(['error' => 'plan not found'], 404);
            return;
        }

        if (!empty($plan['deleted_at'])) {
            $this->json(['error' => 'plan already deleted'], 409);
            return;
        }

        PlansModel::update($plan['id'], [
            'deleted_at' => date('Y-m-d H:i:s')
        ]);

        $this->json(['message' => 'Plan deleted'], 200);
    }

}