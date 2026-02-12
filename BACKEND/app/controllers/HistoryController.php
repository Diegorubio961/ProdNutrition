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

    /**
     * CREATE genérico:
     * - Recibe: table + patient_id + campos del step
     * - Valida:
     *   1) table exista en configuración
     *   2) llaves mínimas requeridas y tipos (validate_keys)
     *   3) patient_id exista en users (evita error FK)
     *   4) tipos de opcionales presentes
     * - Inserta usando el modelo asociado a la tabla
     */

    public function create()
    {
        // 1) Obtener el payload completo de la request (body + query + attributes)
        $request = \Core\Request::getInstance();
        $payload = $request->all();

        // 2) Validación básica: debe venir "table" y ser string
        if (!isset($payload['table']) || !is_string($payload['table'])) {
            $this->json(['error' => 'Validación fallida', 'details' => ['table' => 'faltante_o_tipo_string']], 422);
            return;
        }

        // 3) Validación: la tabla debe estar registrada en $this->tables
        $table = $payload['table'];

        if (!array_key_exists($table, $this->tables)) {
            $this->json(['error' => 'Validación fallida', 'details' => ['table' => 'tabla_invalida']], 422);
            return;
        }

        // 4) Tomar configuración: schema (tipos), required (obligatorios) y model (clase modelo)
        $cfg = $this->tables[$table];
        $schema = $cfg['schema'];
        $required = $cfg['required'];

        // 5) Construir un schema SOLO con los campos requeridos para validarlos con validate_keys
        $requiredSchema = [];
        foreach ($required as $k) {
            $requiredSchema[$k] = $schema[$k];
        }

        // 6) Validación formal de requeridos (llaves y tipos) usando util existente
        $result = \Utils\validate_keys::validateTypes($payload, $requiredSchema);

        if (!$result['ok']) {
            $this->json(['error' => 'Validación fallida', 'details' => $result['errors']], 422);
            return;
        }

        // 7) Validación de integridad referencial: patient_id debe existir en users
        //    Esto evita que MySQL lance el error FK (1452)
        $patientId = $payload['patient_id'];

        $user = UsersModel::query()
            ->where('id', '=', $patientId)
            ->where('deleted_at', 'IS', null)
            ->first();

        if (!$user) {
            $this->json([
                'error' => 'paciente_no_encontrado',
                'details' => ['patient_id' => 'no_existe_en_usuarios']
            ], 404);
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

            if (!$ok) $typeErrors[$key] = "tipo_{$type}_invalido";
        }

        if ($typeErrors) {
            $this->json(['error' => 'Validación fallida', 'details' => $typeErrors], 422);
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

        // 10) Insertar usando el modelo asociado a la tabla seleccionada
        $modelClass = $cfg['model'];
        $created = $modelClass::create($data);

        // 11) Responder OK con el registro creado
        $this->json(['ok' => true, 'tabla' => $table, 'registro_creado' => $created], 201);
    }
}
