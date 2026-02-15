<?php

namespace App\Controllers;

use App\Models\UserInRolModel;
use App\Models\UsersModel;
use App\Models\AppointmentsModel;
use App\Models\NutritionistPatientModel;
use Core\Request;

class DashboardController extends BaseController
{
    public function getNutritionistDashboard()
    {
        $request = Request::getInstance();
        $payload = $request->all();

        if (!isset($payload['nutritionist_id'])) {
            $this->json(['message' => 'nutritionist_id es requerido'], 422);
            return;
        }

        $nutritionistId = $payload['nutritionist_id'];

        // 1. CARDS DATA
        // Active Patients count from pivot
        $activeRelations = NutritionistPatientModel::query()
                        ->where('nutritionist_id', '=', $nutritionistId)
                        ->where('status', '=', 'active')
                        ->get();
        
        $activePatientsCount = count($activeRelations);
        
        // Inactive Patients count
        // Assuming 'inactive' status in pivot, OR patient state is inactive? 
        // Let's stick to pivot status 'inactive' or users who are not active.
        // Let's query all relations for this nutritionist
        $allRelations = NutritionistPatientModel::query()
                        ->where('nutritionist_id', '=', $nutritionistId)
                        ->get();

        $inactivePatientsCount = 0;
        foreach ($allRelations as $rel) {
            if ($rel['status'] !== 'active') {
                $inactivePatientsCount++;
            }
        }
        
        $newRegistrationsCount = 0;
        $currentMonth = date('m');
        $currentYear = date('Y');

        // New Assignments Count (using pivot start_at)
        foreach ($allRelations as $rel) {
            $startAt = strtotime($rel['start_at']);
            if (date('m', $startAt) == $currentMonth && date('Y', $startAt) == $currentYear) {
                $newRegistrationsCount++;
            }
        }

        // Appointments Stats (Current Month) - Filter by nutritionist_id
        $appointments = AppointmentsModel::query()
                        ->where('nutritionist_id', '=', $nutritionistId)
                        ->get();
        
        $appointmentsMonthCount = 0;
        $appointmentsCancelledCount = 0;
        
        // Charts Data
        $appointmentsStatus = [
            'canceladas' => 0,
            'sin_confirmar' => 0,
            'pendiente' => 0,
            'completadas' => 0
        ];

        // For Evolution Chart
        $patientsEvolution = []; 
        for ($i = 5; $i >= 0; $i--) {
             $month = date('M', strtotime("-$i months"));
             $patientsEvolution[$month] = 0;
        }

        // Processing Appointments
        foreach ($appointments as $appt) {
             $apptDate = strtotime($appt['date']);
             $apptMonth = date('m', $apptDate);
             $apptYear = date('Y', $apptDate);
             
             // Count for "Citas del Mes"
             if ($apptMonth == $currentMonth && $apptYear == $currentYear) {
                 $appointmentsMonthCount++;
                 if (strtolower($appt['status']) === 'cancelada') {
                     $appointmentsCancelledCount++;
                 }
             }

             // Status Distribution (All time)
             $status = strtolower($appt['status']); 
             if (isset($appointmentsStatus[$status])) {
                 $appointmentsStatus[$status]++;
             } elseif ($status == 'sin confirmar') { 
                  $appointmentsStatus['sin_confirmar']++; 
             } elseif ($status == 'confirmada') { 
                  if (!isset($appointmentsStatus[$status])) $appointmentsStatus[$status] = 0;
                  $appointmentsStatus[$status]++;
             } else {
                  if (!isset($appointmentsStatus[$status])) $appointmentsStatus[$status] = 0;
                  $appointmentsStatus[$status]++;
             }
        }

        // Processing Evolution (using pivot start_at)
        foreach ($allRelations as $rel) {
             $startAt = strtotime($rel['start_at']);
             $relYm = date('Y-m', $startAt);
             for ($i = 0; $i <= 5; $i++) {
                  $checkYm = date('Y-m', strtotime("-$i months"));
                  if ($relYm === $checkYm) {
                      $mName = date('M', strtotime("-$i months"));
                      if (isset($patientsEvolution[$mName])) {
                          $patientsEvolution[$mName]++;
                      }
                  }
             }
        }
        
        $chartLabels = array_keys($patientsEvolution);
        $chartValues = array_values($patientsEvolution);

        // Sort appointments for table (Recent 5)
        usort($appointments, function($a, $b) {
            return strtotime($b['date']) - strtotime($a['date']);
        });
        $last5Appointments = array_slice($appointments, 0, 5);
        $recentTable = [];
        
        foreach ($last5Appointments as $appt) {
             $patient = UsersModel::query()->where('id', '=', $appt['patient_id'])->first();
             $recentTable[] = [
                 'patient_name' => $patient ? $patient['names'] . ' ' . $patient['surnames'] : 'Unknown',
                 'photo' => $patient['profile_image'] ?? null,
                 'date' => $appt['date'], 
                 'mode' => $appt['visit_type'], 
                 'status' => $appt['status']
             ];
        }

        // RESPONSE STRUCTURE
        $response = [
            'cards' => [
                'active_patients' => [
                    'value' => $activePatientsCount,
                    'trend' => '+0%' 
                ],
                'inactive_patients' => [
                     'value' => $inactivePatientsCount,
                     'trend' => '-0%'
                ],
                'new_registrations' => [
                     'value' => $newRegistrationsCount,
                     'trend' => '+0%'
                ],
                'appointments_cancelled' => [
                     'value' => $appointmentsCancelledCount
                ],
                'appointments_month' => [
                     'value' => $appointmentsMonthCount
                ]
            ],
            'charts' => [
                'evolution_patients' => [
                    'labels' => $chartLabels,
                    'data' => $chartValues
                ],
                'appointments_status' => $appointmentsStatus
            ],
            'table' => [
                'recent_appointments' => $recentTable,
                'recent_patients' => $this->getRecentPatients($nutritionistId)
            ]
        ];

        $this->json($response, 200);
    }

    # Recientes es agregados desde hace 30 dias hacia la actualidad
    private function getRecentPatients($nutritionistId) {
        $relations = NutritionistPatientModel::query()
            ->where('nutritionist_id', '=', $nutritionistId)
            ->where('status', '=', 'active')
            ->where('start_at', '>=', date('Y-m-d', strtotime('-30 days')))
            // ->orderBy('start_at', 'DESC') // QueryBuilder might not support orderBy on relation yet, let's sort PHP side if needed or check QB
            ->get();
        
        // Sort by start_at desc
        usort($relations, function($a, $b) {
            return strtotime($b['start_at']) - strtotime($a['start_at']);
        });

        $recent = array_slice($relations, 0, 5);
        $data = [];

        foreach ($recent as $rel) {
            $u = UsersModel::query()->where('id', '=', $rel['patient_id'])->first();
            if ($u) {
                $data[] = [
                    'id' => $u['id'],
                    'names' => $u['names'],
                    'surnames' => $u['surnames'],
                    'email' => $u['email'],
                    'photo' => $u['profile_image'] ?? null,
                    'start_at' => $rel['start_at']
                ];
            }
        }

        return $data;
    }
}
