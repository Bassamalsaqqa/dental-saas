<?php

declare(strict_types=1);
namespace App\Services;
use CodeIgniter\Database\BaseConnection;

/** Checked clinic-first settings writes serialize with bookings and provider profiles. */
class ClinicSchedulingConfigurationService
{
    protected BaseConnection $db;
    protected ?ScheduleImpactService $impactService;
    public function __construct(?BaseConnection $db = null, ?ScheduleImpactService $impactService = null) { $this->db = $db ?? \Config\Database::connect(); $this->impactService=$impactService; }

    public function configureHours(int $clinicId, array $input, int $actorId, ?array $impactProof = null): array
    {
        $db = $this->db;
        $fail = static fn(string $key): array => ['success' => false, 'message' => lang('DoctorScheduleSettings.' . $key)];
        if ($db->transDepth !== 0) {
            return $fail('saveFailed');
        }
        try {
            if (!$db->transBegin()) return $fail('saveFailed');
            $clinic = $db->query('SELECT id, status FROM clinics WHERE id = ? FOR UPDATE', [$clinicId])->getRowArray();
            if (!$clinic || $clinic['status'] !== 'active' || !$this->canEditScheduling($db, $clinicId, $actorId)) {
                throw new \DomainException('forbidden');
            }
            $impactService = $this->impactService ?? new ScheduleImpactService($db);
            $impactService->lockState($clinicId);
            $settings = $db->table('settings')->where('clinic_id', $clinicId)->get()->getResultArray();
            $values = [];
            foreach ($settings as $row) $values[$row['setting_key']] = $row;
            $wasEnabled = in_array($values['dynamic_doctor_scheduling_enabled']['setting_value'] ?? '0', ['1', 'true'], true);

            // Shared DTO validation and normalization
            unset($input[config('Security')->tokenName]);
            $data = $impactService->validateProposedHoursDto($input, $clinicId);
            $enabled = ($data['dynamic_doctor_scheduling_enabled'] === '1');

            $schemaReady = $db->tableExists('clinic_provider_profiles') && $db->fieldExists('provider_user_id', 'appointments');
            if ($enabled && !$schemaReady) throw new \DomainException('schemaMissing');
            if (!$enabled && $schemaReady && $db->table('appointments')->where('clinic_id', $clinicId)->where('provider_user_id IS NOT NULL', null, false)->countAllResults() > 0) {
                throw new \DomainException('cannotDisable');
            }

            // Schedule Impact Verification Hook under the clinic lock
            $report = $impactService->generateReport($db, $clinicId, 'clinic_hours', $data, null, true);

            $token = $impactProof['token'] ?? ($input['impact_proof_token'] ?? null);
            $acknowledged = $impactProof['acknowledged'] ?? ($input['acknowledged'] ?? null);
            $dispositions = (array) ($impactProof['dispositions'] ?? ($input['dispositions'] ?? []));
            $hasProofInput = ($token !== null) || ($acknowledged !== null) || !empty($dispositions);

            if ($report['after_conflicts_count'] > 0 || $hasProofInput) {
                $validationError = $impactService->validateProofAndDispositions(
                    $db,
                    $clinicId,
                    $actorId,
                    'clinic_hours',
                    $data,
                    null,
                    $token,
                    $acknowledged,
                    $dispositions,
                    $report
                );
                if ($validationError !== null) {
                    throw new \DomainException($validationError);
                }
            }

            foreach ($data as $key => $value) {
                $payload = ['clinic_id' => $clinicId, 'setting_key' => $key, 'setting_value' => is_array($value) ? json_encode($value, JSON_THROW_ON_ERROR) : $value,
                    'setting_type' => is_array($value) ? 'json' : 'string', 'updated_at' => date('Y-m-d H:i:s')];
                if (isset($values[$key])) {
                    $saved = $db->table('settings')->where('id', $values[$key]['id'])->where('clinic_id', $clinicId)->update($payload);
                } else {
                    $payload['created_at'] = $payload['updated_at'];
                    $saved = $db->table('settings')->insert($payload);
                }
                if (!$saved) throw new \RuntimeException('Settings write failed');
            }

            $beforeConfig = [
                'working_hours_start' => $values['working_hours_start']['setting_value'] ?? '09:00',
                'working_hours_end' => $values['working_hours_end']['setting_value'] ?? '17:00',
                'appointment_duration' => $values['appointment_duration']['setting_value'] ?? '30',
                'working_days' => isset($values['working_days']) ? json_decode((string) $values['working_days']['setting_value'], true) : ['monday', 'tuesday', 'wednesday', 'thursday', 'friday'],
                'dynamic_doctor_scheduling_enabled' => $wasEnabled ? '1' : '0',
            ];
            if (!$wasEnabled) {
                $beforeConfig['booking_chair_count'] = $values['booking_chair_count']['setting_value'] ?? '1';
                $beforeConfig['booking_chair_names'] = isset($values['booking_chair_names']) ? json_decode((string) $values['booking_chair_names']['setting_value'], true) : ['Chair 1'];
            }

            $impactService->recordAuditEvidence(
                $db,
                $clinicId,
                $actorId,
                'clinic_hours',
                null,
                $beforeConfig,
                $data,
                $report,
                $dispositions
            );

            $audit = new \App\Services\ControlPlaneAuditService(new \App\Models\ControlPlaneAuditModel($db));
            if (!$audit->logEvent('doctor_scheduling_settings_update', ['actor_user_id' => $actorId, 'route' => '/settings/updateWorkingHours', 'method' => 'POST',
                'metadata' => ['clinic_id' => $clinicId, 'dynamic_enabled' => $enabled]], true)) throw new \RuntimeException('Audit failed');
            if ($db->transDepth !== 1 || !$db->transStatus() || !$db->transCommit()) throw new \RuntimeException('Commit failed');

            // Consume proof ONLY AFTER checked transCommit succeeds
            $impactService->consumeProof($clinicId, 'clinic_hours', null);

            return ['success' => true, 'message' => lang('DoctorScheduleSettings.saved')];
        } catch (\Throwable $e) {
            if ($db->transDepth > 0) $db->transRollback();
            if ($e instanceof \DomainException) return $fail($e->getMessage());
            log_message('error', 'Scheduling settings transaction failed.');
            return $fail('saveFailed');
        }
    }

    private function canEditScheduling(\CodeIgniter\Database\BaseConnection $db, int $clinicId, int $actorId): bool
    {
        $auth = new \App\Libraries\IonAuth();
        if ($clinicId <= 0 || $clinicId !== (int) session()->get('active_clinic_id') || $actorId <= 0 || !$auth->loggedIn() || (int) $auth->getUserId() !== $actorId
            || session()->get('global_mode') === true || session()->get('is_impersonating') === true || (int) session()->get('impersonated_clinic_id') > 0 || \App\Libraries\ApiRuntimeContext::getUserId() !== null
            || \App\Libraries\ApiRuntimeContext::getClinicId() !== null) return false;
        $actor = $db->query('SELECT id, active FROM users WHERE id = ? FOR UPDATE', [$actorId])->getRowArray();
        if (!$actor || (int) $actor['active'] !== 1) return false;
        $membership=$db->query('SELECT cu.role_id, cu.status, r.is_active, r.slug, r.clinic_id AS role_clinic_id FROM clinic_users cu JOIN roles r ON r.id=cu.role_id WHERE cu.clinic_id=? AND cu.user_id=? FOR UPDATE',[$clinicId,$actorId])->getRowArray();
        if(!$membership || $membership['status']!=='active' || (int)$membership['is_active']!==1 || $membership['slug']==='super_admin'
            || ($membership['role_clinic_id']!==null && (int)$membership['role_clinic_id']!==$clinicId))return false;
        return $db->query("SELECT rp.permission_id FROM role_permissions rp JOIN permissions p ON p.id=rp.permission_id WHERE rp.role_id=? AND rp.granted=1 AND p.module='settings' AND p.action IN ('edit','update') FOR UPDATE",[(int)$membership['role_id']])->getRowArray() !== null;
    }

    public function legacyChairMax(): int
    {
        $row=$this->db->table('settings')->where('clinic_id',null)->where('setting_key','default_booking_chair_max')->get()->getRowArray();
        return max(1,min(3,(int)($row['setting_value']??3)));
    }

    public function configureSystem(int $clinicId, array $data, int $actorId): array
    {
        $db = $this->db;
        $fail = static fn(string $key): array => ['success' => false, 'message' => lang('DoctorScheduleSettings.' . $key)];
        if ($db->transDepth !== 0) return $fail('saveFailed');
        try {
            if (!$db->transBegin()) return $fail('saveFailed');
            $clinic=$db->query('SELECT id, status FROM clinics WHERE id = ? FOR UPDATE',[$clinicId])->getRowArray();
            if (!$clinic || $clinic['status']!=='active' || !$this->canEditScheduling($db,$clinicId,$actorId)) throw new \DomainException('forbidden');
            $timezone=$data['timezone']??null;
            if (!is_string($timezone) || !in_array($timezone,\DateTimeZone::listIdentifiers(\DateTimeZone::ALL_WITH_BC),true)) throw new \DomainException('invalidInput');
            $oldTimezone=(new ClinicSchedulingSettings($db))->get($clinicId,'timezone','Asia/Jerusalem');
            if ($timezone!==$oldTimezone && $db->table('appointments')->where('clinic_id',$clinicId)->countAllResults()>0) throw new \DomainException('timezoneHasVisits');
            $allowed=['timezone','date_format','time_format','ui_language','currency','currency_position','finance_auto_post_inventory_cogs'];
            if (array_diff(array_keys($data),$allowed)) throw new \DomainException('invalidInput');
            foreach(['date_format'=>['Y-m-d','m/d/Y','d/m/Y'],'time_format'=>['24','12'],'ui_language'=>['en','ar'],
                'currency'=>['ILS','USD','JOD','EUR','GBP'],'currency_position'=>['before','after'],'finance_auto_post_inventory_cogs'=>['0','1',0,1]] as $key=>$options) {
                if(array_key_exists($key,$data) && !in_array($data[$key],$options,true)) throw new \DomainException('invalidInput');
            }
            foreach ($data as $key=>$value) {
                if (!is_scalar($value)) throw new \DomainException('invalidInput');
                $existing=$db->table('settings')->where(['clinic_id'=>$clinicId,'setting_key'=>$key])->get()->getRowArray();
                $payload=['clinic_id'=>$clinicId,'setting_key'=>$key,'setting_value'=>(string)$value,'setting_type'=>'string','updated_at'=>date('Y-m-d H:i:s')];
                if($existing) $saved=$db->table('settings')->where('id',$existing['id'])->where('clinic_id',$clinicId)->update($payload);
                else { $payload['created_at']=$payload['updated_at']; $saved=$db->table('settings')->insert($payload); }
                if(!$saved)throw new \RuntimeException('Settings write failed');
            }
            $audit=new ControlPlaneAuditService(new \App\Models\ControlPlaneAuditModel($db));
            if(!$audit->logEvent('clinic_system_settings_update',['actor_user_id'=>$actorId,'route'=>'/settings/updateSystem','method'=>'POST','metadata'=>['clinic_id'=>$clinicId,'timezone'=>$timezone]],true))throw new \RuntimeException('Audit failed');
            if($db->transDepth!==1||!$db->transStatus()||!$db->transCommit())throw new \RuntimeException('Commit failed');
            return ['success'=>true,'message'=>lang('DoctorScheduleSettings.saved')];
        } catch(\Throwable $e) {
            if($db->transDepth>0)$db->transRollback();
            if($e instanceof \DomainException)return $fail($e->getMessage());
            log_message('error','Clinic system settings transaction failed.');
            return $fail('saveFailed');
        }
    }
}
