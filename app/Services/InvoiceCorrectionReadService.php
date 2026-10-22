<?php
namespace App\Services;

use CodeIgniter\Database\BaseConnection;

/** Set-based, base-currency read projection. No mutation and no settlement-history hydration. */
final class InvoiceCorrectionReadService
{
    public function __construct(private BaseConnection $db) {}
    private array $clinicTimezones = [];
    public function currentDate(int $clinic): string
    {
        $zone = $this->clinicTimezones[$clinic] ??= (new ClinicSchedulingSettings($this->db))->timezone($clinic);
        return (new \DateTimeImmutable('now', $zone))->format('Y-m-d');
    }
    private function table(string $name): string { return $this->db->escapeIdentifiers($this->db->prefixTable($name)); }
    private function day(?string $day): ?string
    {
        if ($day === null) { return null; }
        $date = \DateTimeImmutable::createFromFormat('!Y-m-d', $day);
        if (!$date || $date->format('Y-m-d') !== $day) { throw new \DomainException('Invalid accounting report date.'); }
        return $day;
    }
    /** Caller-supplied dates are validated and escaped; identifiers are fixed source constants. */
    private function sql(int $clinic, ?string $asOf = null, ?string $start = null, ?string $end = null, array $ids = []): string
    {
        if ($clinic <= 0) { throw new \DomainException('Invalid accounting clinic.'); }
        $asOf = $this->day($asOf); $start = $this->day($start); $end = $this->day($end);
        $f = $this->table('finances'); $p = $this->table('finance_payments');
        $c = $this->table('invoice_corrections'); $a = $this->table('invoice_credit_applications'); $r = $this->table('invoice_credit_application_reversals');
        $cut = $asOf === null ? null : $this->db->escape($asOf . ' 23:59:59');
        $paymentCut = $cut === null ? '' : ' AND payment_date <= ' . $cut;
        $creditCut = $cut === null ? '' : ' AND issued_at <= ' . $cut;
        $appCut = $cut === null ? '' : ' AND a.created_at <= ' . $cut;
        $returnCut = $cut === null ? '' : ' AND r.created_at <= ' . $cut;
        $transactionDate = $this->db->fieldExists('transaction_date', 'finances') ? 'f.transaction_date' : 'NULL';
        $paidDate = $this->db->fieldExists('paid_date', 'finances') ? 'f.paid_date' : 'NULL';
        $docDate = 'COALESCE(' . $transactionDate . ', ' . $paidDate . ', DATE(f.created_at))';
        $where = "f.clinic_id={$clinic} AND f.transaction_type='invoice'";
        // Settlement dates select report periods, but never establish invoice existence.
        $issuanceDate = 'COALESCE(' . $transactionDate . ', DATE(f.created_at))';
        if ($asOf !== null) { $where .= ' AND ' . $issuanceDate . ' <= ' . $this->db->escape($asOf); }
        if ($start !== null) { $where .= ' AND ' . $docDate . ' >= ' . $this->db->escape($start); }
        if ($end !== null) { $where .= ' AND ' . $docDate . ' <= ' . $this->db->escape($end); }
        $financeSelection = ''; $applicationSelection = '';
        if ($ids !== []) {
            $ids = array_values(array_unique(array_filter(array_map('intval', $ids), static fn($id) => $id > 0)));
            if ($ids === []) { throw new \DomainException('Invalid accounting invoice selection.'); }
            $selection = implode(',', $ids);
            $where .= ' AND f.id IN (' . $selection . ')';
            $financeSelection = ' AND finance_id IN (' . $selection . ')';
            $applicationSelection = ' AND (a.source_finance_id IN (' . $selection . ') OR a.target_finance_id IN (' . $selection . '))';
        }
        $gross = $this->db->fieldExists('total_amount','finances') ? 'COALESCE(f.total_amount, f.amount-COALESCE(f.discount_amount,0)+COALESCE(f.tax_amount,0))' : '(f.amount-COALESCE(f.discount_amount,0)+COALESCE(f.tax_amount,0))';
        // All amount columns below are persisted base-currency amounts, never original currencies.
        $historyAppCut = $cut === null ? '' : ' AND history_a.created_at <= ' . $cut;
        $activeApps = "SELECT a.* FROM {$a} a WHERE a.clinic_id={$clinic}{$applicationSelection}{$appCut} AND NOT EXISTS (SELECT 1 FROM {$r} r WHERE r.clinic_id=a.clinic_id AND r.application_id=a.id{$returnCut})";
        $raw = "SELECT f.id,f.patient_id,f.transaction_id,f.payment_status,f.due_date,{$transactionDate} transaction_date,f.created_at,COALESCE(f.balance_due,0) cached_balance,COALESCE(f.amount_paid,0) cached_paid,{$gross} gross,
            COALESCE(p.n,0) payment_count,COALESCE(allp.n,0) all_payment_count,COALESCE(p.cash,0) cash,COALESCE(p.adjustments,0) adjustments,COALESCE(p.invalid,0) payment_invalid,
            COALESCE(c.n,0) + EXISTS(SELECT 1 FROM {$a} history_a WHERE history_a.clinic_id=f.clinic_id AND history_a.source_finance_id=f.id{$historyAppCut}) + EXISTS(SELECT 1 FROM {$a} history_a WHERE history_a.clinic_id=f.clinic_id AND history_a.target_finance_id=f.id{$historyAppCut}) affected,COALESCE(c.credits,0) credits,COALESCE(c.invalid,0) credit_invalid,
            COALESCE(o.amount,0) outgoing,COALESCE(i.amount,0) incoming,COALESCE(o.invalid,0)+COALESCE(i.invalid,0) application_invalid
            FROM {$f} f
            LEFT JOIN (SELECT finance_id,COUNT(*) n,SUM(CASE WHEN type='payment' THEN amount WHEN type IN ('refund','void') THEN -amount ELSE 0 END) cash,SUM(CASE WHEN type='adjustment' THEN amount ELSE 0 END) adjustments,SUM(amount<0 OR amount>9999999999.99 OR type NOT IN ('payment','refund','void','adjustment')) invalid FROM {$p} WHERE clinic_id={$clinic}{$financeSelection}{$paymentCut} GROUP BY finance_id) p ON p.finance_id=f.id
            LEFT JOIN (SELECT finance_id,COUNT(*) n FROM {$p} WHERE clinic_id={$clinic}{$financeSelection} GROUP BY finance_id) allp ON allp.finance_id=f.id
            LEFT JOIN (SELECT finance_id,COUNT(*) n,SUM(amount_base) credits,SUM(amount_base<0 OR amount_base>9999999999.99) invalid FROM {$c} WHERE clinic_id={$clinic}{$financeSelection}{$creditCut} GROUP BY finance_id) c ON c.finance_id=f.id
            LEFT JOIN (SELECT source_finance_id,COUNT(*) n,SUM(amount_base) amount,SUM(amount_base<0 OR amount_base>9999999999.99) invalid FROM ({$activeApps}) a GROUP BY source_finance_id) o ON o.source_finance_id=f.id
            LEFT JOIN (SELECT target_finance_id,COUNT(*) n,SUM(amount_base) amount,SUM(amount_base<0 OR amount_base>9999999999.99) invalid FROM ({$activeApps}) a GROUP BY target_finance_id) i ON i.target_finance_id=f.id
            WHERE {$where}";
        $legacy = $asOf === null ? "CASE WHEN all_payment_count=0 AND cached_paid>0 AND cached_paid>=gross AND payment_status='paid' THEN cached_paid ELSE 0 END" : '0';
        $legacyInvalid = $asOf === null
            ? "(all_payment_count=0 AND NOT (cached_paid=0 AND payment_status IN ('pending','overdue','unpaid','cancelled')) AND NOT (cached_paid>0 AND cached_paid>=gross AND payment_status='paid') AND NOT (cached_paid=0 AND payment_status='paid' AND affected>0))"
            : '(all_payment_count=0 AND cached_paid>0)';
        $project = $asOf === null ? 'affected>0' : '1=1';
        $normalized = "SELECT x.*,adjustments+({$legacy}) effective_adjustments,({$project}) projected,
            CASE WHEN ({$project}) THEN (gross IS NULL OR gross<0 OR gross>9999999999.99 OR cached_paid<0 OR cached_paid>9999999999.99 OR incoming>9999999999.99 OR outgoing>9999999999.99 OR cash<0 OR cash+adjustments>gross OR credits<0 OR credits>gross OR outgoing>cash OR payment_invalid>0 OR credit_invalid>0 OR application_invalid>0 OR ({$legacyInvalid})) ELSE 0 END invalid
            FROM ({$raw}) x";
        return "SELECT n.*,CASE WHEN projected THEN GREATEST(0,gross-credits-cash-effective_adjustments+outgoing-incoming) ELSE cached_balance END remaining,
            CASE WHEN projected THEN LEAST(GREATEST(0,cash-outgoing+incoming),GREATEST(0,cash+effective_adjustments-outgoing+incoming-gross+credits)) ELSE 0 END patient_credit,
            cash+effective_adjustments ledger_settled FROM ({$normalized}) n";
    }
    private function assertValid(array $row): void
    {
        if ((int)($row['invalid'] ?? 0)>0) { throw new \DomainException('Invoice correction report requires reconciliation; historical unledgered settlements cannot be dated implicitly.'); }
    }
    public function positions(int $clinic, array $ids, ?string $asOf = null): array
    {
        $result=[];
        foreach (array_chunk(array_values(array_unique(array_map('intval',$ids))),500) as $chunk) {
            foreach ($this->db->query($this->sql($clinic,$asOf,null,null,$chunk))->getResultArray() as $row) {
                $this->assertValid($row);
                if (!(int)$row['projected']) { continue; }
                $pos=InvoiceCorrectionPosition::calculate(InvoiceCorrectionMoney::nonNegative($row['gross']),InvoiceCorrectionMoney::nonNegative($row['credits']),InvoiceCorrectionMoney::nonNegative($row['cash']),InvoiceCorrectionMoney::nonNegative($row['effective_adjustments']),InvoiceCorrectionMoney::nonNegative($row['outgoing']),InvoiceCorrectionMoney::nonNegative($row['incoming']));
                foreach(['total_amount'=>'gross','remaining_base'=>'outstanding','credited_base'=>'credits','incoming_base'=>'incoming_allocations','outgoing_base'=>'outgoing_allocations','patient_credit_base'=>'patient_credit','net_cash_base'=>'cash_net'] as $alias=>$key) { $pos[$alias]=$pos[$key];$pos[$alias.'_cents']=$pos[$key.'_cents']; }
                $pos['ledger_settled']=InvoiceCorrectionMoney::decimal(InvoiceCorrectionMoney::nonNegative($row['ledger_settled']));
                $result[(int)$row['id']]=$pos;
            }
        }
        return $result;
    }
    /** Database aggregate over the entire eligible set, never a capped detail page. */
    public function summary(int $clinic, ?string $asOf = null, ?string $start = null, ?string $end = null): array
    {
        $today=$this->db->escape($this->day($asOf) ?? $this->currentDate($clinic));
        $sql=$this->sql($clinic,$asOf,$start,$end);
        $row=$this->db->query("SELECT COALESCE(SUM(invalid),0) invalid,COALESCE(SUM(credits),0) credited_invoice_total,COALESCE(SUM(patient_credit),0) patient_credit_liability,
            COALESCE(SUM(CASE WHEN payment_status<>'cancelled' AND remaining>0 THEN remaining ELSE 0 END),0) pending_amount,
            COALESCE(SUM(CASE WHEN payment_status<>'cancelled' AND remaining>0 AND due_date<{$today} THEN remaining ELSE 0 END),0) overdue_amount,
            COALESCE(SUM(payment_status<>'cancelled' AND remaining>0),0) invoice_count,
            COALESCE(SUM(CASE WHEN payment_status<>'cancelled' AND remaining>0 AND COALESCE(due_date,transaction_date,DATE(created_at))>={$today} THEN remaining ELSE 0 END),0) current,
            COALESCE(SUM(CASE WHEN payment_status<>'cancelled' AND remaining>0 AND DATEDIFF({$today},COALESCE(due_date,transaction_date,DATE(created_at))) BETWEEN 1 AND 30 THEN remaining ELSE 0 END),0) days_1_30,
            COALESCE(SUM(CASE WHEN payment_status<>'cancelled' AND remaining>0 AND DATEDIFF({$today},COALESCE(due_date,transaction_date,DATE(created_at))) BETWEEN 31 AND 60 THEN remaining ELSE 0 END),0) days_31_60,
            COALESCE(SUM(CASE WHEN payment_status<>'cancelled' AND remaining>0 AND DATEDIFF({$today},COALESCE(due_date,transaction_date,DATE(created_at))) BETWEEN 61 AND 90 THEN remaining ELSE 0 END),0) days_61_90,
            COALESCE(SUM(CASE WHEN payment_status<>'cancelled' AND remaining>0 AND DATEDIFF({$today},COALESCE(due_date,transaction_date,DATE(created_at)))>90 THEN remaining ELSE 0 END),0) days_91_plus
            FROM ({$sql}) q")->getRowArray();
        $this->assertValid($row);return $row;
    }
    public function agingDetail(int $clinic, ?string $asOf, int $limit): array
    {
        $sql=$this->sql($clinic,$asOf);$limit=max(1,min($limit,10000));
        // Invalid history is ordered ahead of valid rows, so LIMIT cannot hide a reconciliation error.
        $rows=$this->db->query("SELECT q.*,p.first_name,p.last_name,p.patient_id patient_number FROM ({$sql}) q LEFT JOIN ".$this->table('patients')." p ON p.clinic_id={$clinic} AND p.id=q.patient_id WHERE q.invalid>0 OR (q.payment_status<>'cancelled' AND q.remaining>0) ORDER BY q.invalid DESC,q.due_date ASC,q.id ASC LIMIT {$limit}")->getResultArray();
        foreach($rows as &$row){$this->assertValid($row);$row['balance_due']=$row['remaining'];$row['total_amount']=$row['gross'];}
        unset($row);return $rows;
    }
}
