<?php

namespace App\Services;

use CodeIgniter\Database\BaseConnection;

/** Read-only candidate keys for the existing workspace presenter, never a partial timeline. */
final class PatientWorkspaceHistoryService
{
    public const TYPES = ['examination', 'appointment', 'treatment', 'prescription', 'contract', 'imaging', 'finance', 'finance_payment', 'provider_payout', 'inventory_usage'];

    public function __construct(private BaseConnection $db)
    {
    }

    /**
     * The authenticated caller supplies plan eligibility; this method does not grant access.
     * Dates retain the old presenter's event source (not financial transaction date).
     * Equal dates preserve source rank and explicit legacy secondary order; previously
     * unspecified ties are stabilized by id DESC for complete traversal.
     * Returns at most limit+1 SQL rows; search work budgets belong to the presenter caller.
     * @param null|array{event_at:string,source_rank:int,source_order:int,id:int} $before
     */
    public function keys(
        int $clinicId,
        int $patientId,
        int $limit = 25,
        ?array $before = null,
        bool $contractsEnabled = false,
        bool $attachmentsEnabled = false,
        array $imagingCategories = [],
        ?string $section = null
    ): array {
        if ($clinicId <= 0 || $patientId <= 0 || $limit < 1 || $limit > 100
            || ($section !== null && !in_array($section, ['examination', 'appointment', 'treatment'], true))) {
            throw new \InvalidArgumentException('Invalid workspace history selection.');
        }
        if ($before !== null && (array_keys($before) !== ['event_at', 'source_rank', 'source_order', 'id']
            || !is_string($before['event_at']) || !preg_match('/^[0-9]{4}-[0-9]{2}-[0-9]{2} [0-9]{2}:[0-9]{2}:[0-9]{2}$/D', $before['event_at'])
            || !is_int($before['source_rank']) || $before['source_rank'] < 0 || $before['source_rank'] >= count(self::TYPES)
            || !is_int($before['source_order']) || !is_int($before['id']) || $before['id'] <= 0)) {
            throw new \InvalidArgumentException('Invalid workspace history cursor.');
        }
        $parent = $this->db->table('patients')->select('id')->where('clinic_id', $clinicId)->where('id', $patientId);
        if ($this->db->fieldExists('deleted_at', 'patients')) {
            $parent->where('deleted_at', null);
        }
        if (!$parent->get()->getRowArray()) {
            throw new \DomainException('Patient not found.');
        }
        $table = fn (string $name): string => $this->db->escapeIdentifiers($this->db->prefixTable($name));
        $patientJoin = ' JOIN ' . $table('patients') . ' p ON p.id=h.patient_id AND p.clinic_id=h.clinic_id';
        $where = 'h.clinic_id = ? AND h.patient_id = ?';
        if ($this->db->fieldExists('deleted_at', 'patients')) {
            $where .= ' AND p.deleted_at IS NULL';
        }
        $definitions = [
            'examination' => [$table('examinations') . ' h' . $patientJoin, 'h.examination_date', $where, []],
            'appointment' => [$table('appointments') . ' h' . $patientJoin, 'h.appointment_date', $where, []],
            'treatment' => [$table('treatments') . ' h' . $patientJoin, 'h.start_date', $where, []],
        ];
        if ($section === null) {
            $definitions['prescription'] = [$table('prescriptions') . ' h' . $patientJoin, 'h.prescribed_date', $where, []];
            if ($contractsEnabled) {
                $definitions['contract'] = [$table('treatment_contracts') . ' h' . $patientJoin, 'h.created_at', $where . ' AND h.deleted_at IS NULL', []];
            }
            if ($attachmentsEnabled && $imagingCategories !== []) {
                foreach ($imagingCategories as $category) {
                    if (!is_string($category)) {
                        throw new \InvalidArgumentException('Invalid imaging category.');
                    }
                }
                $definitions['imaging'] = [$table('file_attachments') . ' h JOIN ' . $table('patients') . ' p ON p.id=h.entity_id AND p.clinic_id=h.clinic_id', 'h.created_at',
                    "h.clinic_id=? AND h.entity_id=? AND h.entity_type='patient' AND h.purpose='attachment' AND h.deleted_at IS NULL"
                    . ($this->db->fieldExists('deleted_at', 'patients') ? ' AND p.deleted_at IS NULL' : '')
                    . ' AND h.category IN (' . implode(',', array_fill(0, count($imagingCategories), '?')) . ')', $imagingCategories];
            }
            $definitions['finance'] = [$table('finances') . ' h' . $patientJoin, 'h.created_at', $where . " AND (h.expense_category IS NULL OR h.expense_category!='inventory_cogs')", []];
            $financeJoin = ' JOIN ' . $table('finances') . ' f ON f.id=h.finance_id AND f.clinic_id=h.clinic_id JOIN ' . $table('patients') . ' p ON p.id=f.patient_id AND p.clinic_id=f.clinic_id';
            $financeWhere = 'h.clinic_id=? AND f.patient_id=?' . ($this->db->fieldExists('deleted_at', 'patients') ? ' AND p.deleted_at IS NULL' : '');
            $definitions['finance_payment'] = [$table('finance_payments') . ' h' . $financeJoin, 'COALESCE(h.payment_date,h.created_at)', $financeWhere, []];
            $definitions['provider_payout'] = [$table('provider_payouts') . ' h' . $financeJoin, 'h.created_at', $financeWhere . ' AND h.patient_id=f.patient_id', []];
            $definitions['inventory_usage'] = [$table('inventory_usage') . ' h JOIN ' . $table('treatments') . ' t ON t.id=h.treatment_id AND t.clinic_id=h.clinic_id JOIN ' . $table('patients') . ' p ON p.id=t.patient_id AND p.clinic_id=t.clinic_id',
                'COALESCE(h.usage_date,h.created_at)', 'h.clinic_id=? AND t.patient_id=?' . ($this->db->fieldExists('deleted_at', 'patients') ? ' AND p.deleted_at IS NULL' : ''), []];
        }
        $parts = [];
        $binds = [];
        foreach ($definitions as $type => [$from, $date, $predicate, $extra]) {
            if ($section !== null && $type !== $section) {
                continue;
            }
            $rank = array_search($type, self::TYPES, true);
            $sourceOrder = match ($type) {
                'appointment' => 'COALESCE(TIME_TO_SEC(h.appointment_time),-1)',
                'treatment' => '-COALESCE((SELECT MAX(c.id) FROM ' . $table('clinical_charge_lines') . " c WHERE c.treatment_id=h.id AND c.clinic_id=h.clinic_id AND c.source_entity_type='treatment' AND c.status!='voided'),0)",
                'finance' => '-COALESCE(TO_DAYS(h.transaction_date),0)',
                default => '0',
            };
            $parts[] = "SELECT h.id, '" . $type . "' AS event_type, " . $rank . " AS source_rank, " . $sourceOrder . " AS source_order, COALESCE(CAST(" . $date . " AS DATETIME),'1970-01-01 00:00:00') AS event_at FROM " . $from . ' WHERE ' . $predicate;
            array_push($binds, $clinicId, $patientId, ...$extra);
        }
        $sql = 'SELECT * FROM (' . implode(' UNION ALL ', $parts) . ') workspace_events';
        if ($before !== null) {
            $sql .= ' WHERE (event_at < ? OR (event_at = ? AND source_rank > ?) OR (event_at = ? AND source_rank = ? AND source_order > ?) OR (event_at = ? AND source_rank = ? AND source_order = ? AND id < ?))';
            array_push($binds, $before['event_at'], $before['event_at'], $before['source_rank'], $before['event_at'], $before['source_rank'], $before['source_order'], $before['event_at'], $before['source_rank'], $before['source_order'], $before['id']);
        }
        $result = $this->db->query($sql . ' ORDER BY event_at DESC, source_rank ASC, source_order ASC, id DESC LIMIT ' . ($limit + 1), $binds);
        if ($result === false) {
            throw new \RuntimeException('Workspace history read failed.');
        }
        $items = $result->getResultArray();
        $more = count($items) > $limit;
        if ($more) {
            array_pop($items);
        }
        foreach ($items as &$item) {
            $item['id'] = (int)$item['id'];
            $item['source_rank'] = (int)$item['source_rank'];
            $item['source_order'] = (int)$item['source_order'];
        } unset($item);
        $last = $items === [] ? null : $items[count($items) - 1];
        return ['items' => $items, 'next' => $more && $last !== null ? ['event_at' => $last['event_at'],'source_rank' => $last['source_rank'],'source_order' => $last['source_order'],'id' => $last['id']] : null];
    }
    private function clinicalQuery(int $clinicId, int $patientId, string $type, ?string $projection = null)
    {
        $table = ['examination' => 'examinations', 'appointment' => 'appointments', 'treatment' => 'treatments'][$type];
        $fields = [
            'examination' => 'h.id,h.examination_id,h.examination_date,h.examination_type,h.chief_complaint,h.status',
            'appointment' => 'h.id,h.appointment_date,h.appointment_time,h.appointment_type,h.chair_name,h.duration,h.notes,h.status',
            'treatment' => 'h.id,h.start_date,h.treatment_name,h.treatment_type,h.tooth_number,h.treatment_description,h.status,ccl.id AS clinical_charge_line_id,ccl.status AS clinical_charge_status,ccl.net_production_amount AS clinical_charge_amount,ccl.currency AS clinical_charge_currency,ccl.finance_id AS clinical_charge_finance_id,f.transaction_id AS clinical_charge_invoice_number,f.payment_status AS clinical_charge_invoice_status,f.total_amount AS clinical_charge_invoice_total,f.amount_paid AS clinical_charge_invoice_paid,f.balance_due AS clinical_charge_invoice_balance',
        ][$type];
        $query = $this->db->table($table . ' h')->select($projection ?? ($fields . ',u.first_name AS doctor_first_name,u.last_name AS doctor_last_name'), $projection === null)
            ->join('patients p', 'p.id=h.patient_id AND p.clinic_id=h.clinic_id')
            ->join('users u', 'u.id=h.created_by', 'left')->where('h.clinic_id', $clinicId)->where('h.patient_id', $patientId);
        if ($this->db->fieldExists('deleted_at', 'patients')) {
            $query->where('p.deleted_at', null);
        }
        if ($type === 'treatment') {
            $latest = $this->db->escapeIdentifiers($this->db->prefixTable('clinical_charge_lines'));
            $query->join($latest . ' ccl', "ccl.id=(SELECT cl.id FROM " . $latest . " cl WHERE cl.treatment_id=h.id AND cl.clinic_id=h.clinic_id AND cl.source_entity_type='treatment' AND cl.status!='voided' ORDER BY cl.id DESC LIMIT 1)", 'left', false)
                ->join('finances f', 'f.id=ccl.finance_id AND f.clinic_id=ccl.clinic_id', 'left');
        }
        return $query;
    }

    /** At most one detail row per requested candidate key; no clinical narratives beyond the existing summary fields. */
    public function clinicalRows(int $clinicId, int $patientId, array $keys): array
    {
        if ($clinicId <= 0 || $patientId <= 0 || count($keys) > 100) {
            throw new \InvalidArgumentException('Invalid clinical history batch.');
        }
        $result = ['examinations' => [],'appointments' => [],'treatments' => []];
        foreach (['examination' => 'examinations','appointment' => 'appointments','treatment' => 'treatments'] as $type => $plural) {
            $ids = [];
            foreach ($keys as $key) {
                if (($key['event_type'] ?? null) === $type) {
                    if (!is_int($key['id'] ?? null) || $key['id'] <= 0) {
                        throw new \InvalidArgumentException('Invalid history key.');
                    }$ids[] = $key['id'];
                }
            }
            if ($ids !== []) {
                $result[$plural] = $this->clinicalQuery($clinicId, $patientId, $type)->whereIn('h.id', array_values(array_unique($ids)))->get()->getResultArray();
            }
        }
        return $result;
    }

    /** Complete overview summaries do not depend on the currently visible history page. */
    public function summary(int $clinicId, int $patientId, string $clinicToday): array
    {
        if ($clinicId <= 0 || $patientId <= 0 || !ClinicSchedulingSettings::validDate($clinicToday)) {
            throw new \InvalidArgumentException('Invalid workspace summary context.');
        }
        $counts = [];
        $latest = [];
        foreach (['examination' => ['examinations','examination_date'],'appointment' => ['appointments','appointment_date'],'treatment' => ['treatments','start_date']] as $type => [$table,$date]) {
            $query = $this->db->table($table.' h')->join('patients p', 'p.id=h.patient_id AND p.clinic_id=h.clinic_id')->where('h.clinic_id', $clinicId)->where('h.patient_id', $patientId);
            if ($this->db->fieldExists('deleted_at', 'patients')) {
                $query->where('p.deleted_at', null);
            }
            $counts[$table] = $query->countAllResults();
            $latestQuery = $this->clinicalQuery($clinicId, $patientId, $type)->orderBy('h.'.$date, 'DESC');
            if ($type === 'appointment') {
                $latestQuery->orderBy('h.appointment_time', 'ASC');
            }
            if ($type === 'treatment') {
                $latestQuery->orderBy('ccl.id', 'DESC');
            }
            $latest[$table] = $latestQuery->orderBy('h.id', 'DESC')->limit(1)->get()->getRowArray();
        }
        $next = $this->clinicalQuery($clinicId, $patientId, 'appointment')->groupStart()->whereNotIn('h.status', ['cancelled','completed'])->orWhere('h.status', null)->groupEnd()->where('h.appointment_date >=', $clinicToday)->orderBy('h.appointment_date', 'ASC')->orderBy('h.appointment_time', 'ASC')->orderBy('h.id', 'ASC')->limit(1)->get()->getRowArray();
        $active = $this->db->table('treatments h')->join('patients p', 'p.id=h.patient_id AND p.clinic_id=h.clinic_id')->where('h.clinic_id', $clinicId)->where('h.patient_id', $patientId)->groupStart()->whereNotIn('h.status', ['completed','cancelled'])->orWhere('h.status', null)->groupEnd();
        if ($this->db->fieldExists('deleted_at', 'patients')) {
            $active->where('p.deleted_at', null);
        }
        $activeCount = $active->countAllResults();
        $row = $this->clinicalQuery($clinicId, $patientId, 'treatment', "SUM(CASE WHEN NOT(COALESCE(ccl.status,'')='invoiced' AND COALESCE(ccl.finance_id,0)>0) AND ccl.net_production_amount>0 THEN 1 ELSE 0 END) AS open_count,SUM(CASE WHEN NOT(COALESCE(ccl.status,'')='invoiced' AND COALESCE(ccl.finance_id,0)>0) AND ccl.net_production_amount>0 THEN ccl.net_production_amount ELSE 0 END) AS open_amount,SUM(CASE WHEN ccl.status='invoiced' AND ccl.finance_id>0 AND COALESCE(f.balance_due,0)>0.009 THEN 1 ELSE 0 END) AS unpaid_count,SUM(CASE WHEN ccl.status='invoiced' AND ccl.finance_id>0 AND COALESCE(f.balance_due,0)<=0.009 THEN 1 ELSE 0 END) AS paid_count")->get()->getRowArray();
        return ['counts' => $counts,'latest' => $latest,'next_appointment' => $next,'active_treatments' => $activeCount,'clinical_billing' => ['open_count' => (int)($row['open_count'] ?? 0),'open_amount' => (float)($row['open_amount'] ?? 0),'paid_count' => (int)($row['paid_count'] ?? 0),'unpaid_count' => (int)($row['unpaid_count'] ?? 0)]];
    }

    /** Complete same-day counts with at most one displayed link per clinical type/date. */
    public function sameDay(int $clinicId, int $patientId, array $dates): array
    {
        if ($clinicId <= 0 || $patientId <= 0 || count($dates) > 100) {
            throw new \InvalidArgumentException('Invalid same-day clinical selection.');
        }
        foreach ($dates as $date) {
            if (!ClinicSchedulingSettings::validDate($date)) {
                throw new \InvalidArgumentException('Invalid clinical date.');
            }
        }
        $dates = array_values(array_unique($dates));
        if ($dates === []) {
            return [];
        }
        $result = [];
        foreach (['examination' => ['examination_date','examination_id','examinations'],'treatment' => ['start_date','treatment_name','treatments']] as $type => [$dateField,$labelField,$plural]) {
            $order = $type === 'treatment' ? 'ccl.id DESC,h.id DESC' : 'h.id ASC';
            $amount = $type === 'treatment' ? 'COALESCE(ccl.net_production_amount,0)' : '0';
            $projection = 'h.id,h.'.$dateField.' AS clinical_date,h.'.$labelField.' AS label,COUNT(*) OVER(PARTITION BY h.'.$dateField.') AS record_count,SUM('.$amount.') OVER(PARTITION BY h.'.$dateField.') AS captured_amount,ROW_NUMBER() OVER(PARTITION BY h.'.$dateField.' ORDER BY '.$order.') AS rn';
            $inner = $this->clinicalQuery($clinicId, $patientId, $type, $projection)->whereIn('h.'.$dateField, $dates)->getCompiledSelect();
            $read = $this->db->query('SELECT * FROM ('.$inner.') same_day_records WHERE rn=1');
            if ($read === false) {
                throw new \RuntimeException('Same-day clinical read failed.');
            }
            foreach ($read->getResultArray() as $row) {
                $date = substr($row['clinical_date'], 0, 10);
                $result[$date] ??= ['examination_count' => 0,'treatment_count' => 0,'examinations' => [],'treatments' => [],'captured_amount' => 0.0];
                $result[$date][$type.'_count'] = (int)$row['record_count'];
                $result[$date][$plural] = [['id' => (int)$row['id'],'label' => (string)$row['label']]];
                $result[$date]['captured_amount'] += (float)$row['captured_amount'];
            }
        }
        return $result;
    }

}
