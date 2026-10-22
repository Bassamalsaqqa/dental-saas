<?php
namespace App\Services;

use CodeIgniter\Database\BaseConnection;
use Config\Database;

/** Bounded read-only module history; authorization remains with the canonical App API. */
final class PatientHistoryReadService
{
    public const SECTIONS = [
        'appointments' => ['table'=>'appointments', 'columns'=>'id,appointment_date,appointment_time,appointment_type,status'],
        'examinations' => ['table'=>'examinations', 'columns'=>'id,examination_date,examination_type,status'],
        'treatments' => ['table'=>'treatments', 'columns'=>'id,start_date,treatment_name,status'],
    ];

    public function __construct(private ?BaseConnection $db = null) { $this->db ??= Database::connect(); }

    public function page(int $clinicId, int $patientId, string $section, int $perPage = 25, ?int $beforeId = null): array
    {
        if ($clinicId <= 0 || $patientId <= 0 || !isset(self::SECTIONS[$section]) || $perPage < 1 || $perPage > 100 || ($beforeId !== null && $beforeId <= 0)) {
            throw new \InvalidArgumentException('Invalid patient history selection.');
        }
        $parent = $this->db->table('patients')->select('id')->where('clinic_id', $clinicId)->where('id', $patientId);
        if ($this->db->fieldExists('deleted_at', 'patients')) { $parent->where('deleted_at', null); }
        if (!$parent->get()->getRowArray()) { throw new \DomainException('Patient not found.'); }
        $definition = self::SECTIONS[$section];
        $columns = implode(',', array_map(static fn(string $column): string => 'h.' . $column, explode(',', $definition['columns'])));
        $query = $this->db->table($definition['table'] . ' h')->select($columns)
            ->join('patients p', 'p.id=h.patient_id AND p.clinic_id=h.clinic_id', 'inner')
            ->where('h.clinic_id', $clinicId)->where('h.patient_id', $patientId);
        if ($this->db->fieldExists('deleted_at', 'patients')) { $query->where('p.deleted_at', null); }
        if ($this->db->fieldExists('deleted_at', $definition['table'])) { $query->where('h.deleted_at', null); }
        if ($beforeId !== null) { $query->where('h.id <', $beforeId); }
        $items = $query->orderBy('h.id', 'DESC')->limit($perPage + 1)->get()->getResultArray();
        $hasMore = count($items) > $perPage;
        if ($hasMore) { array_pop($items); }
        foreach ($items as &$item) { $item['id'] = (int) $item['id']; }
        unset($item);
        return ['items'=>$items, 'pagination'=>[
            'per_page'=>$perPage, 'has_more'=>$hasMore,
            'next_before_id'=>$hasMore ? $items[count($items)-1]['id'] : null,
            'order'=>'id_desc',
        ]];
    }
}
