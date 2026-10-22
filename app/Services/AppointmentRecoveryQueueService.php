<?php
namespace App\Services;
use CodeIgniter\Database\BaseConnection;
use Config\Database;
/** Read-only operational attention list, not a recall or contact-attempt ledger. */
final class AppointmentRecoveryQueueService
{
    public function __construct(private ?BaseConnection $db = null) { $this->db ??= Database::connect(); }
    public function summary(int $clinicId, int $limit = 4, ?\DateTimeImmutable $now = null): array
    {
        if ($clinicId <= 0) { return ['count'=>0,'items'=>[]]; }
        $tz=(new ClinicSchedulingSettings($this->db))->timezone($clinicId);
        $today=($now ?? ClinicTemporalPolicyService::now($tz))->setTimezone($tz)->setTime(0,0);
        $from=$today->modify('-29 days')->format('Y-m-d');$through=$today->format('Y-m-d');
        $base=$this->db->table('appointments a')->join('patients p','p.id=a.patient_id AND p.clinic_id=a.clinic_id','inner')
            ->where('a.clinic_id',$clinicId)->whereIn('a.status',['cancelled','no_show'])
            ->where('a.appointment_date >=',$from)->where('a.appointment_date <=',$through);
        // Legacy schemas differ; apply soft-delete predicates only where supported.
        if ($this->db->fieldExists('deleted_at', 'appointments')) { $base->where('a.deleted_at', null); }
        if ($this->db->fieldExists('deleted_at', 'patients')) { $base->where('p.deleted_at', null); }
        $count=(clone $base)->countAllResults();
        $items=$base->select('a.id,a.patient_id,a.appointment_date,a.appointment_time,a.status,p.first_name,p.last_name')
            ->orderBy('a.appointment_date','DESC')->orderBy('a.appointment_time','DESC')->orderBy('a.id','DESC')->limit(min(100, max(1,$limit)))->get()->getResultArray();
        return ['count'=>$count,'items'=>$items,'from'=>$from,'through'=>$through];
    }
}
