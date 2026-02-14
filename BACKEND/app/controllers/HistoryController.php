<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\UsersModel;
use App\Models\HistoryGeneralModel;
use App\Models\HistoryInfoHealthModel;
use App\Models\HistoryPsychosocialConditionsModel;
use App\Models\HistoryClinicalSignsModel;
use App\Models\HistoryLaboratoryTestsModel;
use App\Models\HistoryMedicationsSupplementsModel;
use App\Models\HistoryPhysicalActivityModel;
use App\Models\HistoryFeedingModel;
use App\Models\HistoryReminderModel;
use App\Models\HistoryFrequencyConsumptionModel;

class HistoryController extends BaseController
{
    /**
     * Tabla de configuración:
     * - model: clase del modelo que ejecuta el create()
     * - schema: definición de tipos permitidos por campo
     * - required: llaves mínimas obligatorias para crear
     */
    private array $tables;

    public function __construct()
    {
        parent::__construct();

        // Mapa: nombre de tabla (string que llega en payload) => configuración
        $this->tables = [
            'history_general' => [
                'model' => HistoryGeneralModel::class,
                'mode' => 'single',
                'schema' => [
                    'table' => 'string',
                    'patient_id' => 'int',
                    'birth_date' => 'string',
                    'care_date' => 'string',
                    'social_stratum' => 'int',
                    'health_provider' => 'string',
                    'education_level' => 'string',
                    'cohabiting_people' => 'int',
                    'occupation' => 'string'
                ],
                'required' => ['table', 'patient_id']
            ],
            'history_info_health' => [
                'model' => HistoryInfoHealthModel::class,
                'mode' => 'single',
                'schema' => [
                    'table' => 'string',
                    'patient_id' => 'int',
                    'consult_reason' => 'string',
                    'previous_treatment' => 'string',
                    'family_history' => 'string',
                    'personal_history' => 'string',
                    'pubertal_maturation' => 'string',
                    'menarche' => 'decimal',
                    'regular_menstruation' => 'decimal',
                    'additional_pregnancy_data' => 'string',
                    'surgeries' => 'string',
                    'gastrointestinal_symptoms' => 'string'
                ],
                'required' => ['table', 'patient_id']
            ],
            'history_psychosocial_conditions' => [
                'model' => HistoryPsychosocialConditionsModel::class,
                'mode' => 'single',
                'schema' => [
                    'table' => 'string',
                    'patient_id' => 'int',
                    'conditions' => 'string',
                    'sleep_hours' => 'decimal',
                    'sleep_quality' => 'string'
                ],
                'required' => ['table', 'patient_id']
            ],
            'history_clinical_signs' => [
                'model' => HistoryClinicalSignsModel::class,
                'mode' => 'multiple',
                'schema' => [
                    'table' => 'string',
                    'patient_id' => 'int',
                    'parameter' => 'string',
                    'assessment' => 'string'
                ],
                'required' => ['table', 'patient_id']
            ],
            'history_laboratory_tests' => [
                'model' => HistoryLaboratoryTestsModel::class,
                'mode' => 'multiple',
                'schema' => [
                    'table' => 'string',
                    'patient_id' => 'int',
                    'indicator_laboratory' => 'string',
                    'value' => 'decimal',
                    'unit_laboratory' => 'string',
                    'interpretation' => 'string'
                ],
                'required' => ['table', 'patient_id']
            ],
            'history_medications_supplements' => [
                'model' => HistoryMedicationsSupplementsModel::class,
                'mode' => 'multiple',
                'schema' => [
                    'table' => 'string',
                    'patient_id' => 'int',
                    'indicator_supplement' => 'string',
                    'objective' => 'string',
                    'dose' => 'decimal',
                    'unit_medication' => 'string',
                    'frequency_hours' => 'decimal',
                    'prescribed' => 'string'
                ],
                'required' => ['table', 'patient_id']
            ],
            'history_physical_activity' => [
                'model' => HistoryPhysicalActivityModel::class,
                'mode' => 'single',
                'schema' => [
                    'table' => 'string',
                    'patient_id' => 'int',
                    'activity' => 'string',
                    'frequency_days' => 'decimal',
                    'training_schedule' => 'string',
                    'intensity_hours' => 'decimal'
                ],
                'required' => ['table', 'patient_id']
            ],
            'history_feeding' => [
                'model' => HistoryFeedingModel::class,
                'mode' => 'single',
                'schema' => [
                    'table' => 'string',
                    'patient_id' => 'int',
                    'appetite' => 'string',
                    'preferences' => 'string',
                    'rejections' => 'string',
                    'intolerances_allergies' => 'string',
                    'general_observations' => 'string'
                ],
                'required' => ['table', 'patient_id']
            ],
            'history_reminder' => [
                'model' => HistoryReminderModel::class,
                'mode' => 'multiple',
                'schema' => [
                    'table' => 'string',
                    'patient_id' => 'int',
                    'meal_type' => 'string',
                    'time_reminder' => 'string',
                    'meal_place' => 'string',
                    'preparation' => 'string',
                    'meal_quantity' => 'decimal',
                    'meal_quantity_unit' => 'string'
                ],
                'required' => ['table', 'patient_id']
            ],
            'history_frequency_consumption' => [
                'model' => HistoryFrequencyConsumptionModel::class,
                'mode' => 'multiple',
                'schema' => [
                    'table' => 'string',
                    'patient_id' => 'int',
                    'unit_frequency_id' => 'int',
                    'food_name' => 'string',
                    'consumption_frequency' => 'decimal'
                ],
                'required' => ['table', 'patient_id', 'unit_frequency_id']
            ]
        ];
    }

    public function create()
    {
        // 1) Obtener el payload completo de la request (body + query + attributes)
        $request = \Core\Request::getInstance();
        $payload = $request->all();

        // 2) Validación básica: debe venir "table" y ser string
        if (!isset($payload['table']) || !is_string($payload['table'])) {
            $this->json(['message' => 'Validación fallida: tabla faltante o tipo incorrecto'], 422);
            return;
        }

        // 3) Validación: la tabla debe estar registrada en $this->tables
        $table = $payload['table'];

        if (!array_key_exists($table, $this->tables)) {
            $this->json(['message' => 'Tabla inválida'], 422);
            return;
        }

        // 4) Tomar configuración: schema (tipos), required (obligatorios) y model (clase modelo)
        $cfg = $this->tables[$table];
        $schema = $cfg['schema'];
        $required = $cfg['required'];
        $mode = $cfg['mode'] ?? 'multiple';
        $modelClass = $cfg['model'];

        // 5) Construir un schema SOLO con los campos requeridos para validarlos con validate_keys
        $requiredSchema = [];
        foreach ($required as $k) {
            $requiredSchema[$k] = $schema[$k];
        }

        // 6) Validación formal de requeridos (llaves y tipos) usando util existente
        $result = \Utils\validate_keys::validateTypes($payload, $requiredSchema);

        if (!$result['ok']) {
            $this->json(['message' => 'Validación fallida en campos requeridos'], 422);
            return;
        }

        // 7) Validación de integridad referencial: patient_id debe existir en users
        //    Esto evita que MySQL lance el error FK (1452)
        $patientId = $payload['patient_id'];

        $user = UsersModel::find($patientId);

        if (!$user) {
            $this->json(['message' => 'Paciente no encontrado'], 404);
            return;
        }

        // 8) Validación de tipos para opcionales:
        //    - se valida SOLO lo que venga en payload (y no sea null)
        //    - se excluye "table" porque no es columna de DB
        $optionalSchema = $schema;
        unset($optionalSchema['table']);

        $typeErrors = [];
        foreach ($optionalSchema as $key => $type) {
            if (!array_key_exists($key, $payload)) continue;
            if ($payload[$key] === null) continue;

            // Validación por tipo (decimal permite número o string numérico)
            $ok = match ($type) {
                'string' => is_string($payload[$key]),
                'int' => is_int($payload[$key]),
                'bool' => is_bool($payload[$key]),
                'decimal' => is_int($payload[$key]) || is_float($payload[$key]) || (is_string($payload[$key]) && is_numeric($payload[$key])),
                default => false
            };

            if (!$ok) $typeErrors[$key] = true;
        }

        if ($typeErrors) {
            $this->json(['message' => 'Error de tipo en los datos enviados'], 422);
            return;
        }

        // 9) Construir el arreglo final $data para insertar:
        //    - solo columnas definidas en schema
        //    - solo campos presentes y no null
        //    - ignora timestamps y soft delete (no se reciben desde el cliente)
        $data = [];
        foreach (array_keys($optionalSchema) as $field) {
            if (!array_key_exists($field, $payload)) continue;
            if ($payload[$field] === null) continue;
            if (in_array($field, ['created_at', 'updated_at', 'deleted_at'], true)) continue;
            $data[$field] = $payload[$field];
        }

        // Check for existing record in 'single' mode tables
        if ($mode === 'single') {
            if (method_exists($modelClass, 'query')) {
                 $existing = $modelClass::query()
                    ->where('patient_id', '=', $patientId)
                    ->where('deleted_at', 'IS', null)
                    ->first();

                if ($existing) {
                    $this->json(['message' => 'Ya existe un registro para este paciente'], 409);
                    return;
                }
            }
        }

        // 9.1) Debe venir al menos un campo para crear (aparte de patient_id)
        $dataWithoutPatient = $data;
        unset($dataWithoutPatient['patient_id']);

        if (count($dataWithoutPatient) === 0 && $mode === 'multiple') {
            $this->json(['message' => 'Debe enviar al menos un campo para crear'], 422);
            return;
        }

        // 10) Insertar usando el modelo asociado a la tabla seleccionada
        $created = $modelClass::create($data);

        // 11) Responder OK con el registro creado
        $this->json(['message' => 'Registro creado correctamente'], 201);
    }

    public function update()
    {
        $request = \Core\Request::getInstance();
        $payload = $request->all();

        if (!isset($payload['table']) || !is_string($payload['table'])) {
            $this->json(['message' => 'Validación fallida: tabla faltante o tipo incorrecto'], 422);
            return;
        }

        $table = $payload['table'];

        if (!array_key_exists($table, $this->tables)) {
            $this->json(['message' => 'Tabla inválida'], 422);
            return;
        }

        $cfg = $this->tables[$table];
        $schema = $cfg['schema'];
        $mode = $cfg['mode'] ?? 'multiple';
        $modelClass = $cfg['model'];

        $requiredSchema = [
            'table' => 'string',
            'patient_id' => 'int'
        ];

        if ($mode === 'multiple') {
            $requiredSchema['id'] = 'int';
        }

        $result = \Utils\validate_keys::validateTypes($payload, $requiredSchema);

        if (!$result['ok']) {
            $this->json(['message' => 'Validación fallida en campos requeridos'], 422);
            return;
        }

        $patientId = $payload['patient_id'];
        $id = $payload['id'] ?? null;

        $user = UsersModel::find($patientId);

        if (!$user) {
            $this->json(['message' => 'Paciente no encontrado'], 404);
            return;
        }

        if (!method_exists($modelClass, 'query') || !method_exists($modelClass, 'update')) {
            $this->json(['message' => 'Error de configuración: método no disponible'], 500);
            return;
        }

        // Buscar registro
        if ($mode === 'single') {
            $record = $modelClass::query()
                ->where('patient_id', '=', $patientId)
                ->first();
        } else {
            $record = $modelClass::query()
                ->where('id', '=', $id)
                ->first();
        }

        if (!$record || !empty($record['deleted_at'])) {
            $this->json(['message' => 'Registro no encontrado'], 404);
            return;
        }

        // Validar pertenencia
        if ($mode === 'multiple') {
            if (array_key_exists('patient_id', $schema)) {
                $currentPatientId = $record['patient_id'] ?? null;
                if ($currentPatientId !== null && (int)$currentPatientId !== (int)$patientId) {
                    $this->json(['message' => 'El registro no corresponde al paciente'], 403);
                    return;
                }
            }
        }

        // Schema actualizable
        $updatableSchema = $schema;
        unset($updatableSchema['table']);
        unset($updatableSchema['patient_id']);
        unset($updatableSchema['id']);

        $blocked = ['created_at', 'updated_at', 'deleted_at'];
        foreach ($blocked as $b) {
            if (array_key_exists($b, $updatableSchema)) unset($updatableSchema[$b]);
        }

        // Validar campos no permitidos
        $unknownFields = [];
        foreach ($payload as $k => $v) {
            if (in_array($k, ['table', 'patient_id', 'id'], true)) continue;
            if (!array_key_exists($k, $updatableSchema)) $unknownFields[] = $k;
        }

        if ($unknownFields) {
            $this->json(['message' => 'Campos no permitidos en la actualización'], 422);
            return;
        }

        // Debe venir al menos 1 campo actualizable
        $providedFields = [];
        foreach (array_keys($updatableSchema) as $field) {
            if (!array_key_exists($field, $payload)) continue;
            if ($payload[$field] === null) continue;
            $providedFields[] = $field;
        }

        if (!$providedFields) {
            $this->json(['message' => 'Debe enviar al menos un campo actualizable'], 422);
            return;
        }

        // Validar tipos solo de lo enviado
        $typeErrors = [];
        foreach ($providedFields as $key) {
            $type = $updatableSchema[$key];

            $ok = match ($type) {
                'string' => is_string($payload[$key]),
                'int' => is_int($payload[$key]),
                'bool' => is_bool($payload[$key]),
                'decimal' => is_int($payload[$key]) || is_float($payload[$key]) || (is_string($payload[$key]) && is_numeric($payload[$key])),
                default => false
            };

            if (!$ok) $typeErrors[$key] = true;
        }

        if ($typeErrors) {
            $this->json(['message' => 'Error de tipo en los datos enviados'], 422);
            return;
        }

        // Calcular updates por cambios reales
        $updates = [];
        foreach ($providedFields as $field) {
            $newVal = $payload[$field];
            $oldVal = $record[$field] ?? null;

            $schemaType = $updatableSchema[$field] ?? null;

            if ($schemaType === 'decimal' && is_string($newVal) && is_numeric($newVal)) $newVal = (float)$newVal;
            if ($schemaType === 'decimal' && is_string($oldVal) && is_numeric($oldVal)) $oldVal = (float)$oldVal;

            if ($newVal !== $oldVal) {
                $updates[$field] = $payload[$field];
            }
        }

        if (empty($updates)) {
            $this->json(['message' => 'No se detectaron cambios para actualizar'], 200);
            return;
        }

        $recordId = $record['id'];
        $modelClass::update($recordId, $updates);

        $this->json(['message' => 'Registro actualizado correctamente'], 200);
    }

    public function delete()
    {
        $request = \Core\Request::getInstance();
        $payload = $request->all();

        // 1) Validación básica: table
        if (!isset($payload['table']) || !is_string($payload['table'])) {
            $this->json(['message' => 'Validación fallida: tabla faltante o tipo incorrecto'], 422);
            return;
        }

        $table = $payload['table'];

        if (!array_key_exists($table, $this->tables)) {
            $this->json(['message' => 'Tabla inválida'], 422);
            return;
        }

        // 2) Config
        $cfg = $this->tables[$table];
        $mode = $cfg['mode'] ?? 'multiple';
        $modelClass = $cfg['model'];
        $schema = $cfg['schema'];

        // 3) Requeridos mínimos: table, patient_id, id
        $requiredSchema = [
            'table' => 'string',
            'patient_id' => 'int'
        ];

        if ($mode === 'multiple') {
            $requiredSchema['id'] = 'int';
        }

        $result = \Utils\validate_keys::validateTypes($payload, $requiredSchema);

        if (!$result['ok']) {
            $this->json(['message' => 'Validación fallida en campos requeridos'], 422);
            return;
        }

        $patientId = $payload['patient_id'];
        $id = $payload['id'] ?? null;

        // 4) Validar patient_id exista
        $user = UsersModel::find($patientId);

        if (!$user) {
            $this->json(['message' => 'Paciente no encontrado'], 404);
            return;
        }

        // 5) Validar soporte del modelo
        if (!method_exists($modelClass, 'query')) {
             $this->json(['message' => 'Error de configuración: método no disponible'], 500);
            return;
        }

        // 6) Buscar registro por id
        if ($mode === 'single') {
            $record = $modelClass::query()
                ->where('patient_id', '=', $patientId)
                ->first();
        } else {
            $record = $modelClass::query()
                ->where('id', '=', $id)
                ->first();
        }

        if (!$record || !empty($record['deleted_at'])) {
            $this->json(['message' => 'Registro no encontrado'], 404);
            return;
        }

        // 7) Validar pertenencia al paciente (si aplica)
        if ($mode === 'multiple') {
            if (array_key_exists('patient_id', $schema)) {
                $currentPatientId = $record['patient_id'] ?? null;

                if ($currentPatientId !== null && (int)$currentPatientId !== (int)$patientId) {
                    $this->json(['message' => 'El registro no corresponde al paciente'], 403);
                    return;
                }
            }
        }

        // 8) Soft delete preferido (si existe deleted_at). Si no, delete físico.
        if (array_key_exists('deleted_at', $record)) {
            if (!method_exists($modelClass, 'update')) {
                $this->json(['message' => 'Error de configuración: updated no disponible'], 500);
                return;
            }
            $modelClass::update($record['id'], ['deleted_at' => date('Y-m-d H:i:s')]);
        } else {
            // Fallback: delete físico por query builder si existe
            if (method_exists($modelClass::query(), 'delete')) {
                $modelClass::query()
                    ->where('id', '=', $record['id'])
                    ->delete();
            } else {
                $this->json(['message' => 'Error de configuración: delete no disponible'], 500);
                return;
            }
        }

        // 9) Respuesta OK
        $this->json(['message' => 'El registro se eliminó correctamente'], 200);
    }

    public function read()
    {
        $request = \Core\Request::getInstance();
        $payload = $request->all();

        if (!isset($payload['patient_id']) || !is_int($payload['patient_id'])) {
            $this->json(['message' => 'Validación fallida'], 422);
            return;
        }

        $patientId = $payload['patient_id'];

        $user = UsersModel::find($patientId);

        if (!$user) {
            $this->json(['message' => 'Paciente no encontrado'], 404);
            return;
        }

        $stripDates = function ($row) {
            if (!is_array($row)) return $row;
            unset($row['created_at'], $row['updated_at'], $row['deleted_at']);
            return $row;
        };

        $stripDatesMany = function ($rows) use ($stripDates) {
            if (!is_array($rows)) return [];
            $clean = [];
            foreach ($rows as $r) {
                $clean[] = $stripDates($r);
            }
            return $clean;
        };

        $result = [];

        foreach ($this->tables as $tableName => $cfg) {
            $modelClass = $cfg['model'];
            $mode = $cfg['mode'] ?? 'multiple';

            if (!method_exists($modelClass, 'query')) {
                $result[$tableName] = $mode === 'single' ? (object)[] : [];
                continue;
            }

            if ($mode === 'single') {
                $row = $modelClass::query()
                    ->where('patient_id', '=', $patientId)
                    ->where('deleted_at', 'IS', null)
                    ->first();

                $result[$tableName] = $row ? $stripDates($row) : (object)[];
            } else {
                $rows = $modelClass::query()
                    ->where('patient_id', '=', $patientId)
                    ->where('deleted_at', 'IS', null)
                    ->get();

                $result[$tableName] = $stripDatesMany($rows);
            }
        }

        $this->json($result, 200);
    }
}
