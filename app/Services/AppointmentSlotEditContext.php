<?php

declare(strict_types=1);

namespace App\Services;

use App\Libraries\ApiRuntimeContext;
use App\Libraries\IonAuth;
use CodeIgniter\Database\BaseConnection;
use Config\Database;

/** Session-owned edit proof for advisory slots; never an authority for booking writes. */
final class AppointmentSlotEditContext
{
    private const SESSION_KEY = 'appointment_slot_edit_contexts';
    private const TTL = 900;
    private BaseConnection $db;

    public function __construct(?BaseConnection $db = null)
    {
        $this->db = $db ?? Database::connect();
    }

    public function issue(int $clinicId, int $appointmentId): ?string
    {
        $actor = $this->authorizedActor($clinicId);
        if ($actor === null || !$this->ownsAppointment($clinicId, $appointmentId)) return null;
        try { if (!AppointmentReschedulabilityService::isReschedulable($this->db, $clinicId, $appointmentId)) return null; }
        catch (\Throwable $e) { return null; }
        $contexts = $this->contexts();
        $contexts = array_filter($contexts, static fn($item): bool => is_array($item) && (int)($item['expires_at']??0) > time());
        $key = $clinicId . ':' . $appointmentId;
        unset($contexts[$key]);
        // Bound session size and retained edit tabs; older tabs fail closed and can be reloaded.
        $contexts = array_slice($contexts, -15, null, true);
        $token = bin2hex(random_bytes(32));
        $contexts[$key] = ['actor_id'=>$actor, 'expires_at'=>time()+self::TTL, 'digest'=>hash('sha256', $token)];
        session()->set(self::SESSION_KEY, $contexts);
        return $token;
    }

    public function resolve(int $clinicId, ?int $appointmentId, ?string $token): ?int
    {
        if ($appointmentId === null || $appointmentId <= 0 || $token === null
            || preg_match('/^[a-f0-9]{64}$/D', $token) !== 1) return null;
        $actor = $this->authorizedActor($clinicId);
        if ($actor === null) return null;
        $context = $this->contexts()[$clinicId . ':' . $appointmentId] ?? null;
        if (!is_array($context) || (int)($context['actor_id']??0) !== $actor
            || (int)($context['expires_at']??0) <= time()
            || !hash_equals((string)($context['digest']??''), hash('sha256',$token))) return null;
        try { if (!AppointmentReschedulabilityService::isReschedulable($this->db, $clinicId, $appointmentId)) return null; }
        catch (\Throwable $e) { return null; }
        return $this->ownsAppointment($clinicId,$appointmentId) ? $appointmentId : null;
    }

    private function contexts(): array
    {
        $value = session()->get(self::SESSION_KEY);
        return is_array($value) ? $value : [];
    }

    private function ownsAppointment(int $clinicId, int $appointmentId): bool
    {
        return $clinicId > 0 && $appointmentId > 0 && $this->db->table('appointments')
            ->where(['id'=>$appointmentId,'clinic_id'=>$clinicId])->countAllResults() === 1;
    }

    private function authorizedActor(int $clinicId): ?int
    {
        // A public Bearer request cannot borrow browser edit state, even with matching IDs.
        if (ApiRuntimeContext::getUserId() !== null || ApiRuntimeContext::getClinicId() !== null
            || $clinicId <= 0 || (int)session('active_clinic_id') !== $clinicId
            || session('global_mode') === true) return null;
        $auth = new IonAuth();
        $actor = (int)$auth->getUserId();
        if (!$auth->loggedIn() || $actor <= 0) return null;
        $permissions = new PermissionService($this->db);
        $impersonating = session('is_impersonating') === true || (int)session('impersonated_clinic_id') > 0;
        if ($impersonating) {
            $clinic=$this->db->table('clinics')->where(['id'=>$clinicId,'status'=>'active'])->get()->getRowArray();
            if (!$clinic || session('is_impersonating') !== true || (int)session('impersonated_clinic_id') !== $clinicId
                || !$permissions->isSuperAdmin($actor)) return null;
        } elseif ($permissions->getTenantRoleId($actor,$clinicId) === null) return null;
        return $permissions->hasPermission($actor,'appointments','edit') ? $actor : null;
    }
}
