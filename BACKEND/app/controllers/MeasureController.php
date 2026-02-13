<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\UsersModel;
use App\Models\MeasureGeneralModel;
use App\Models\MeasureFoldsModel;
use App\Models\MeasurePerimetersModel;
use App\Models\MeasureLenghtsModel;
use App\Models\MeasureDiametersModel;

class MeasureController extends BaseController
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
            'measure_general' => [
                'model' => MeasureGeneralModel::class,
                'mode' => 'single',
                'schema' => [
                    'table' => 'string',
                    'patient_id' => 'int',
                    'occupation_sport' => 'string',
                    'category_modality' => 'string',
                    'anthropometry' => 'string',
                    'control' => 'string',
                    'sex' => 'string',
                    'weight_kg' => 'decimal',
                    'height_cm' => 'decimal',
                    'sitting_height_cm' => 'decimal',
                    'bench_height_cm' => 'decimal',
                    'corrected_sitting_height_cm' => 'decimal',
                    'wingspan_cm' => 'decimal'
                ],
                'required' => ['table', 'patient_id']
            ],
            'measure_folds' => [
                'model' => MeasureFoldsModel::class,
                'mode' => 'single',
                'schema' => [
                    'table' => 'string',
                    'patient_id' => 'int',
                    'triceps' => 'decimal',
                    'subspcapular' => 'decimal',
                    'biceps' => 'decimal',
                    'pectoral' => 'decimal',
                    'axillary' => 'decimal',
                    'suprailiac' => 'decimal',
                    'supraspinal' => 'decimal',
                    'abdominal' => 'decimal',
                    'thigh' => 'decimal',
                    'leg' => 'decimal'
                ],
                'required' => ['table', 'patient_id']
            ],
            'measure_perimeters' => [
                'model' => MeasurePerimetersModel::class,
                'mode' => 'single',
                'schema' => [
                    'table' => 'string',
                    'patient_id' => 'int',
                    'head' => 'decimal',
                    'neck' => 'decimal',
                    'arm_relaxed' => 'decimal',
                    'arm_tensed' => 'decimal',
                    'forearm' => 'decimal',
                    'wrist' => 'decimal',
                    'mesosternal' => 'decimal',
                    'waist' => 'decimal',
                    'abdominal' => 'decimal',
                    'hip' => 'decimal',
                    'thigh_max' => 'decimal',
                    'thigh_mid' => 'decimal',
                    'calf_max' => 'decimal',
                    'ankle_min' => 'decimal'
                ],
                'required' => ['table', 'patient_id']
            ],
            'measure_lenghts' => [
                'model' => MeasureLenghtsModel::class,
                'mode' => 'single',
                'schema' => [
                    'table' => 'string',
                    'patient_id' => 'int',
                    'acromial_radial' => 'decimal',
                    'radial_styloid' => 'decimal',
                    'medial_styloid_dactilar' => 'decimal',
                    'ileospinal' => 'decimal',
                    'trochanteric' => 'decimal',
                    'trochanteric_tibial_lateral' => 'decimal',
                    'tibial_lateral' => 'decimal',
                    'tibial_medial_malleolar_medial' => 'decimal',
                    'foot' => 'decimal'
                ],
                'required' => ['table', 'patient_id']
            ],
            'measure_diameters' => [
                'model' => MeasureDiametersModel::class,
                'mode' => 'single',
                'schema' => [
                    'table' => 'string',
                    'patient_id' => 'int',
                    'biacromial' => 'decimal',
                    'biileocrestal' => 'decimal',
                    'antero_posterior_abdominal' => 'decimal',
                    'thorax_transverse' => 'decimal',
                    'thorax_antero_posterior' => 'decimal',
                    'humerus_biepicondylar' => 'decimal',
                    'wrist_bistyloid' => 'decimal',
                    'hand' => 'decimal',
                    'femur_biepicondylar' => 'decimal',
                    'ankle_bimalleolar' => 'decimal',
                    'foot' => 'decimal'
                ],
                'required' => ['table', 'patient_id']
            ]
        ];
    }

    // Create method removed as initialization is handled by PatientController

    public function update()
    {
        $request = \Core\Request::getInstance();
        $payload = $request->all();

        if (!isset($payload['table']) || !is_string($payload['table'])) {
            $this->json(['error' => 'Validación fallida', 'details' => ['table' => 'faltante_o_tipo_string']], 422);
            return;
        }

        $table = $payload['table'];

        if (!array_key_exists($table, $this->tables)) {
            $this->json(['error' => 'Validación fallida', 'details' => ['table' => 'tabla_invalida']], 422);
            return;
        }

        $cfg = $this->tables[$table];
        $schema = $cfg['schema'];
        $modelClass = $cfg['model'];
        $mode = $cfg['mode'] ?? 'single';

        $requiredSchema = [
            'table' => 'string',
            'patient_id' => 'int'
        ];

        if ($mode === 'multiple') {
            $requiredSchema['id'] = 'int';
        }

        $result = \Utils\validate_keys::validateTypes($payload, $requiredSchema);

        if (!$result['ok']) {
            $this->json(['error' => 'Validación fallida', 'details' => $result['errors']], 422);
            return;
        }

        $patientId = $payload['patient_id'];
        $id = $payload['id'] ?? null;

        $user = UsersModel::find($patientId);

        if (!$user) {
            $this->json([
                'error' => 'paciente_no_encontrado',
                'details' => ['patient_id' => 'no_existe_en_usuarios']
            ], 404);
            return;
        }

        if (!method_exists($modelClass, 'query') || !method_exists($modelClass, 'update')) {
            $this->json([
                'error' => 'configuracion_invalida',
                'details' => ['model' => 'metodo_query_o_update_no_disponible']
            ], 500);
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
            $this->json([
                'error' => 'registro_no_encontrado',
                'details' => $mode === 'single'
                    ? ['patient_id' => 'no_existe_registro_en_tabla_single']
                    : ['id' => 'no_existe_en_tabla']
            ], 404);
            return;
        }

        if ($mode === 'multiple') {
             if (array_key_exists('patient_id', $schema)) {
                $currentPatientId = $record['patient_id'] ?? null;
                if ($currentPatientId !== null && (int)$currentPatientId !== (int)$patientId) {
                    $this->json([
                        'error' => 'registro_no_corresponde_al_paciente',
                        'details' => ['patient_id' => 'no_coincide_con_el_registro']
                    ], 403);
                    return;
                }
            }
        }

        $updatableSchema = $schema;
        unset($updatableSchema['table']);
        unset($updatableSchema['patient_id']);
        unset($updatableSchema['id']);

        $blocked = ['created_at', 'updated_at', 'deleted_at'];
        foreach ($blocked as $b) {
            if (array_key_exists($b, $updatableSchema)) unset($updatableSchema[$b]);
        }

        $unknownFields = [];
        foreach ($payload as $k => $v) {
            if (in_array($k, ['table', 'patient_id', 'id'], true)) continue;
            if (!array_key_exists($k, $updatableSchema)) $unknownFields[] = $k;
        }

        if ($unknownFields) {
            $this->json([
                'error' => 'Validación fallida',
                'details' => ['campos_no_permitidos' => $unknownFields]
            ], 422);
            return;
        }

        $providedFields = [];
        foreach (array_keys($updatableSchema) as $field) {
            if (!array_key_exists($field, $payload)) continue;
            if ($payload[$field] === null) continue;
            $providedFields[] = $field;
        }

        if (!$providedFields) {
            $this->json([
                'error' => 'Validación fallida',
                'details' => ['update' => 'debe_enviar_al_menos_un_campo_actualizable']
            ], 422);
            return;
        }

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

            if (!$ok) $typeErrors[$key] = "tipo_{$type}_invalido";
        }

        if ($typeErrors) {
            $this->json(['error' => 'Validación fallida', 'details' => $typeErrors], 422);
            return;
        }

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
            $this->json([
                'mensaje' => 'No se detectaron cambios para actualizar'
            ], 200);
            return;
        }

        $recordId = $record['id'];
        $modelClass::update($recordId, $updates);

        $this->json([
            'message' => 'Registro actualizado correctamente'
        ], 200);
    }

    public function delete()
    {
        $request = \Core\Request::getInstance();
        $payload = $request->all();

        if (!isset($payload['table']) || !is_string($payload['table'])) {
            $this->json(['error' => 'Validación fallida', 'details' => ['table' => 'faltante_o_tipo_string']], 422);
            return;
        }

        $table = $payload['table'];

        if (!array_key_exists($table, $this->tables)) {
            $this->json(['error' => 'Validación fallida', 'details' => ['table' => 'tabla_invalida']], 422);
            return;
        }

        $cfg = $this->tables[$table];
        $modelClass = $cfg['model'];
        $schema = $cfg['schema'];
        $mode = $cfg['mode'] ?? 'single';

        $requiredSchema = [
            'table' => 'string',
            'patient_id' => 'int'
        ];

        if ($mode === 'multiple') {
            $requiredSchema['id'] = 'int';
        }

        $result = \Utils\validate_keys::validateTypes($payload, $requiredSchema);

        if (!$result['ok']) {
            $this->json(['error' => 'Validación fallida', 'details' => $result['errors']], 422);
            return;
        }

        $patientId = $payload['patient_id'];
        $id = $payload['id'] ?? null;

        $user = UsersModel::find($patientId);

        if (!$user) {
            $this->json([
                'error' => 'paciente_no_encontrado',
                'details' => ['patient_id' => 'no_existe_en_usuarios']
            ], 404);
            return;
        }

        if (!method_exists($modelClass, 'query')) {
             $this->json([
                'error' => 'configuracion_invalida',
                'details' => ['model' => 'metodo_query_no_disponible']
            ], 500);
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
            $this->json([
                'error' => 'registro_no_encontrado',
                'details' => $mode === 'single'
                    ? ['patient_id' => 'no_existe_registro_en_tabla_single']
                    : ['id' => 'no_existe_en_tabla']
            ], 404);
            return;
        }

        if ($mode === 'multiple') {
            if (array_key_exists('patient_id', $schema)) {
                $currentPatientId = $record['patient_id'] ?? null;
                if ($currentPatientId !== null && (int)$currentPatientId !== (int)$patientId) {
                    $this->json([
                        'error' => 'registro_no_corresponde_al_paciente',
                        'details' => ['patient_id' => 'no_coincide_con_el_registro']
                    ], 403);
                    return;
                }
            }
        }

        // Soft delete
        if (array_key_exists('deleted_at', $record)) {
            if (!method_exists($modelClass, 'update')) {
                $this->json([
                    'error' => 'configuracion_invalida',
                    'details' => ['model' => 'metodo_update_no_disponible_para_soft_delete']
                ], 500);
                return;
            }

            $modelClass::update($record['id'], ['deleted_at' => date('Y-m-d H:i:s')]);
        } else {
            // Fallback: delete físico
            if (method_exists($modelClass::query(), 'delete')) {
                $modelClass::query()
                    ->where('id', '=', $record['id'])
                    ->delete();
            } else {
                $this->json([
                    'error' => 'configuracion_invalida',
                    'details' => ['model' => 'metodo_delete_no_disponible']
                ], 500);
                return;
            }
        }

        $this->json(['message' => 'El registro se eliminó correctamente'], 200);
    }

    public function read()
    {
        $request = \Core\Request::getInstance();
        $payload = $request->all();

        if (!isset($payload['patient_id']) || !is_int($payload['patient_id'])) {
            $this->json(['message' => 'Validación fallida: patient_id requerido y entero'], 422);
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
            $mode = $cfg['mode'] ?? 'single';

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
