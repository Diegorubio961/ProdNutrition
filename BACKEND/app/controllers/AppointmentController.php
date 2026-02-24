<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\AppointmentsModel;
use App\Models\UsersModel;
use utils\validate_keys;

class AppointmentController extends BaseController
{

    private const ALLOWED_STATUSES = [
        'scheduled',
        'confirmed',
        'canceled',
        'completed',
        'no_show'
    ];

    public function __construct()
    {
        parent::__construct();
    }

    public function createAppointment()
    {
        $request = \Core\Request::getInstance();
        $payload = $request->all(); // Quitar 

        $schema = [
            'patient_id' => 'int',
            'nutritionist_id' => 'int',
            'date' => 'string',
            'duration_minutes' => 'int'
        ];

        $result = \Utils\validate_keys::validateTypes($payload, $schema);

        if (!$result['ok']) {
            $this->json(['error' => 'Validation failed', 'details' => $result['errors']], 422);
            return;
        }

        $patientId = (int)$payload['patient_id'];
        $nutritionistId = (int)$payload['nutritionist_id'];
        $durationMinutes = (int)$payload['duration_minutes'];

        if ($patientId === $nutritionistId) {
            $this->json(['error' => 'Validation failed', 'details' => ['patient_id' => 'patient_id and nutritionist_id cannot be the same user']], 422);
            return;
        }

        if ($durationMinutes <= 0) {
            $this->json(['error' => 'Validation failed', 'details' => ['duration_minutes' => 'duration_minutes must be greater than 0']], 422);
            return;
        }

        $startAt = $this->parseDateTime((string)$payload['date']);
        if ($startAt === null) {
            $this->json(['error' => 'Validation failed', 'details' => ['date' => 'date must be a valid datetime string']], 422);
            return;
        }

        $endAt = clone $startAt;
        $endAt->modify('+' . $durationMinutes . ' minutes');

        $startTs = strtotime($this->formatDateTime($startAt));
        $endTs = strtotime($this->formatDateTime($endAt));

        if ($startTs === false || $endTs === false || $endTs <= $startTs) {
            $this->json(['error' => 'Validation failed', 'details' => ['date' => 'invalid_datetime_range']], 422);
            return;
        }

        $patient = UsersModel::find($patientId);
        if (!$patient || (isset($patient['deleted_at']) && $patient['deleted_at'] !== null)) {
            $this->json(['error' => 'Validation failed', 'details' => ['patient_id' => 'patient_id does not exist']], 422);
            return;
        }

        $nutritionist = UsersModel::find($nutritionistId);
        if (!$nutritionist || (isset($nutritionist['deleted_at']) && $nutritionist['deleted_at'] !== null)) {
            $this->json(['error' => 'Validation failed', 'details' => ['nutritionist_id' => 'nutritionist_id does not exist']], 422);
            return;
        }

        $status = $payload['status'] ?? 'scheduled';
        if ($status === null || $status === '') {
            $status = 'scheduled';
        }

        if (!in_array((string)$status, self::ALLOWED_STATUSES, true)) {
            $this->json(['error' => 'Validation failed', 'details' => ['status' => 'status is invalid']], 422);
            return;
        }

        $nutritionistOverlap = $this->hasOverlap('nutritionist_id', $nutritionistId, $startTs, $endTs, null);
        if ($nutritionistOverlap) {
            $this->json(['error' => 'Validation failed', 'details' => ['nutritionist_id' => 'overlapping_appointment']], 422);
            return;
        }

        $patientOverlap = $this->hasOverlap('patient_id', $patientId, $startTs, $endTs, null);
        if ($patientOverlap) {
            $this->json(['error' => 'Validation failed', 'details' => ['patient_id' => 'overlapping_appointment']], 422);
            return;
        }

        $newAppointment = AppointmentsModel::create([
            'patient_id' => $patientId,
            'nutritionist_id' => $nutritionistId,
            'visit_type' => $payload['visit_type'] ?? null,
            'date' => $this->formatDateTime($startAt),
            'duration_minutes' => $durationMinutes,
            'additional_notes' => $payload['additional_notes'] ?? null,
            'reminder_method' => $payload['reminder_method'] ?? null,
            'repeat_visit' => $payload['repeat_visit'] ?? null,
            'status' => (string)$status
        ]);

        $this->json([
            'ok' => true,
            'appointment' => $newAppointment,
            'end_at' => $this->formatDateTime($endAt)
        ], 201);
    }

    public function readAppointments()
    {
        $request = \Core\Request::getInstance();
        $payload = $request->all();

        $schema = [
            'user_id' => 'int'
        ];

        $result = \Utils\validate_keys::validateTypes($payload, $schema);

        if (!$result['ok']) {
            $this->json(['error' => 'Validation failed', 'details' => $result['errors']], 422);
            return;
        }

        $userId = (int)$payload['user_id'];

        $user = UsersModel::find($userId);
        if (!$user || (isset($user['deleted_at']) && $user['deleted_at'] !== null)) {
            $this->json(['error' => 'Validation failed', 'details' => ['user_id' => 'user_id does not exist']], 422);
            return;
        }

        $asPatient = AppointmentsModel::query()
            ->where('deleted_at', 'IS', null)
            ->where('patient_id', '=', $userId)
            ->get();

        $asNutritionist = AppointmentsModel::query()
            ->where('deleted_at', 'IS', null)
            ->where('nutritionist_id', '=', $userId)
            ->get();

        $map = [];
        foreach ($asPatient as $row) {
            $map[$row['id']] = $row;
        }
        foreach ($asNutritionist as $row) {
            $map[$row['id']] = $row;
        }

        $appointments = array_values($map);

        usort($appointments, function ($a, $b) {
            $ta = strtotime((string)($a['date'] ?? '')) ?: 0;
            $tb = strtotime((string)($b['date'] ?? '')) ?: 0;
            return $tb <=> $ta;
        });

        $this->json(['ok' => true, 'user_id' => $userId, 'appointments' => $appointments], 200);
    }

    public function updateAppointment()
    {
        $request = \Core\Request::getInstance();
        $payload = $request->all();

        $baseSchema = [
            'id' => 'int'
        ];

        $baseResult = \Utils\validate_keys::validateTypes($payload, $baseSchema);

        if (!$baseResult['ok']) {
            $this->json(['error' => 'Validation failed', 'details' => $baseResult['errors']], 422);
            return;
        }

        $id = (int)$payload['id'];

        $appointment = AppointmentsModel::find($id);
        if (!$appointment || (isset($appointment['deleted_at']) && $appointment['deleted_at'] !== null)) {
            $this->json(['error' => 'Not found', 'details' => ['id' => 'appointment not found']], 404);
            return;
        }

        $allowedFields = [
            'patient_id' => 'int',
            'nutritionist_id' => 'int',
            'visit_type' => 'string',
            'date' => 'string',
            'duration_minutes' => 'int',
            'additional_notes' => 'string',
            'reminder_method' => 'string',
            'repeat_visit' => 'string',
            'status' => 'string'
        ];

        $dynamicSchema = [];
        foreach ($allowedFields as $key => $type) {
            if (array_key_exists($key, $payload)) {
                $dynamicSchema[$key] = $type;
            }
        }

        if ($dynamicSchema) {
            $dynResult = \Utils\validate_keys::validateTypes($payload, $dynamicSchema);
            if (!$dynResult['ok']) {
                $this->json(['error' => 'Validation failed', 'details' => $dynResult['errors']], 422);
                return;
            }
        }

        $patientId = array_key_exists('patient_id', $payload) ? (int)$payload['patient_id'] : (int)$appointment['patient_id'];
        $nutritionistId = array_key_exists('nutritionist_id', $payload) ? (int)$payload['nutritionist_id'] : (int)$appointment['nutritionist_id'];

        if ($patientId === $nutritionistId) {
            $this->json(['error' => 'Validation failed', 'details' => ['patient_id' => 'patient_id and nutritionist_id cannot be the same user']], 422);
            return;
        }

        $durationMinutes = array_key_exists('duration_minutes', $payload) ? (int)$payload['duration_minutes'] : (int)$appointment['duration_minutes'];
        if ($durationMinutes <= 0) {
            $this->json(['error' => 'Validation failed', 'details' => ['duration_minutes' => 'duration_minutes must be greater than 0']], 422);
            return;
        }

        $dateValue = array_key_exists('date', $payload) ? (string)$payload['date'] : (string)$appointment['date'];
        $startAt = $this->parseDateTime($dateValue);

        if ($startAt === null) {
            $this->json(['error' => 'Validation failed', 'details' => ['date' => 'date must be a valid datetime string']], 422);
            return;
        }

        $endAt = clone $startAt;
        $endAt->modify('+' . $durationMinutes . ' minutes');

        $startTs = strtotime($this->formatDateTime($startAt));
        $endTs = strtotime($this->formatDateTime($endAt));

        if ($startTs === false || $endTs === false || $endTs <= $startTs) {
            $this->json(['error' => 'Validation failed', 'details' => ['date' => 'invalid_datetime_range']], 422);
            return;
        }

        if (array_key_exists('patient_id', $payload)) {
            $patient = UsersModel::find($patientId);
            if (!$patient || (isset($patient['deleted_at']) && $patient['deleted_at'] !== null)) {
                $this->json(['error' => 'Validation failed', 'details' => ['patient_id' => 'patient_id does not exist']], 422);
                return;
            }
        }

        if (array_key_exists('nutritionist_id', $payload)) {
            $nutritionist = UsersModel::find($nutritionistId);
            if (!$nutritionist || (isset($nutritionist['deleted_at']) && $nutritionist['deleted_at'] !== null)) {
                $this->json(['error' => 'Validation failed', 'details' => ['nutritionist_id' => 'nutritionist_id does not exist']], 422);
                return;
            }
        }

        $status = array_key_exists('status', $payload) ? $payload['status'] : ($appointment['status'] ?? 'scheduled');
        if ($status === null || $status === '') {
            $status = 'scheduled';
        }

        if (!in_array((string)$status, self::ALLOWED_STATUSES, true)) {
            $this->json(['error' => 'Validation failed', 'details' => ['status' => 'status is invalid']], 422);
            return;
        }

        $nutritionistOverlap = $this->hasOverlap('nutritionist_id', $nutritionistId, $startTs, $endTs, $id);
        if ($nutritionistOverlap) {
            $this->json(['error' => 'Validation failed', 'details' => ['nutritionist_id' => 'overlapping_appointment']], 422);
            return;
        }

        $patientOverlap = $this->hasOverlap('patient_id', $patientId, $startTs, $endTs, $id);
        if ($patientOverlap) {
            $this->json(['error' => 'Validation failed', 'details' => ['patient_id' => 'overlapping_appointment']], 422);
            return;
        }

        $updateData = [];

        $updatable = [
            'patient_id',
            'nutritionist_id',
            'visit_type',
            'date',
            'duration_minutes',
            'additional_notes',
            'reminder_method',
            'repeat_visit',
            'status'
        ];

        foreach ($updatable as $col) {
            if (!array_key_exists($col, $payload)) continue;

            if ($col === 'date') {
                $updateData['date'] = $this->formatDateTime($startAt);
                continue;
            }

            $updateData[$col] = $payload[$col];
        }

        if (!$updateData) {
            $this->json(['ok' => true, 'message' => 'No changes detected', 'appointment' => $appointment], 200);
            return;
        }

        $updated = AppointmentsModel::update($id, $updateData);

        $this->json([
            'ok' => true,
            'message' => 'Appointment updated',
            'updated_fields' => array_keys($updateData),
            'appointment' => $updated,
            'end_at' => $this->formatDateTime($endAt)
        ], 200);
    }


    public function deleteAppointment()
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

        $appointment = AppointmentsModel::query()
            ->where('id', '=', (int)$payload['id'])
            ->first();

        if (!$appointment) {
            $this->json(['error' => 'appointment not found'], 404);
            return;
        }

        if (!empty($appointment['deleted_at'])) {
            $this->json(['error' => 'appointment already deleted'], 409);
            return;
        }

        AppointmentsModel::update((int)$appointment['id'], [
            'deleted_at' => date('Y-m-d H:i:s')
        ]);

        $this->json(['ok' => true, 'message' => 'Appointment deleted'], 200);
    }

    private function parseDateTime(string $value): ?\DateTime
    {
        try {
            return new \DateTime($value);
        } catch (\Throwable $e) {
            return null;
        }
    }

    private function formatDateTime(\DateTime $dt): string
    {
        return $dt->format('Y-m-d H:i:s');
    }

    private function hasOverlap(string $column, int $userId, int $newStartTs, int $newEndTs, ?int $ignoreAppointmentId): bool
    {
        $windowStart = date('Y-m-d H:i:s', $newStartTs - (24 * 60 * 60));
        $windowEnd = date('Y-m-d H:i:s', $newEndTs + (24 * 60 * 60));

        $baseQuery = AppointmentsModel::query()
            ->select('id', 'date', 'duration_minutes', 'status')
            ->where($column, '=', $userId)
            ->where('deleted_at', 'IS', null)
            ->where('date', '>=', $windowStart)
            ->where('date', '<=', $windowEnd);

        if ($ignoreAppointmentId !== null) {
            $baseQuery = $baseQuery->where('id', '!=', $ignoreAppointmentId);
        }

        $scheduled = (clone $baseQuery)
            ->where('status', '=', 'scheduled')
            ->get();

        $confirmed = (clone $baseQuery)
            ->where('status', '=', 'confirmed')
            ->get();

        $map = [];
        foreach ($scheduled as $row) $map[$row['id']] = $row;
        foreach ($confirmed as $row) $map[$row['id']] = $row;

        $candidates = array_values($map);

        foreach ($candidates as $appt) {
            $existingStartTs = strtotime((string)($appt['date'] ?? ''));
            if ($existingStartTs === false) continue;

            $existingDuration = (int)($appt['duration_minutes'] ?? 0);
            if ($existingDuration <= 0) continue;

            $existingEndTs = $existingStartTs + ($existingDuration * 60);

            if (($existingStartTs < $newEndTs) && ($existingEndTs > $newStartTs)) {
                return true;
            }
        }

        return false;
    }
}
