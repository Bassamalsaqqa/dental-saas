<?php
/** Independent verification helper: load after booting the reviewed candidate. No connection is created here. */
final class F1IndependentSqlFixture
{
    public static function run(\CodeIgniter\Database\BaseConnection $db): array
    {
        if (PHP_SAPI !== 'cli') { throw new RuntimeException('CLI only.'); }
        $identity = $db->query('SELECT DATABASE() db, CURRENT_USER() actor')->getRowArray();
        if (($identity['db'] ?? '') !== 'u556956644_dentstage1017' || !str_starts_with((string)($identity['actor'] ?? ''), 'u556956644_dstage1017@')) { throw new RuntimeException('Dedicated synthetic staging database/user required.'); }
        $marker = $db->query('SELECT guid, domain, synthetic_only FROM _denta_stage_identity WHERE id=1')->getRowArray();
        if (($marker['guid'] ?? '') !== 'b60809432ec1d915b0b975aef8484e43' || ($marker['domain'] ?? '') !== 'dentastaging.palsync.net' || (int)($marker['synthetic_only'] ?? 0) !== 1) { throw new RuntimeException('Synthetic stage ownership marker rejected.'); }
        foreach ($db->query('SHOW GRANTS')->getResultArray() as $entry) {
            $grant = str_replace('\\_', '_', (string)array_values($entry)[0]);
            if (str_contains($grant, 'WITH GRANT OPTION') || (str_contains($grant, ' ON *.* ') && !str_starts_with($grant, 'GRANT USAGE ON *.* '))) { throw new RuntimeException('Overbroad database grant rejected.'); }
            if (preg_match('/ ON `([^`]+)`\./', $grant, $m) && $m[1] !== $identity['db']) { throw new RuntimeException('Foreign schema privilege rejected.'); }
        }
        foreach ($db->query("SELECT setting_value FROM settings WHERE setting_key='dynamic_doctor_scheduling_enabled'")->getResultArray() as $setting) {
            if (!in_array(strtolower((string)$setting['setting_value']), ['0','false','off',''], true)) { throw new RuntimeException('Scheduling must remain disabled.'); }
        }
        if ((int)$db->transDepth !== 0 || $db->transStatus() === false) { throw new RuntimeException('Owned top-level transaction required.'); }
        $date = '2099-06-10'; $year = 2099; $clinic = 1; $otherClinic = 2;
        if ((int)$db->table('finances')->whereIn('clinic_id', [$clinic, $otherClinic])->where('transaction_date >=', '2099-01-01')->where('transaction_date <=', '2099-12-31')->countAllResults() !== 0) { throw new RuntimeException('Golden-fixture year must be empty.'); }
        $patients = [];
        foreach ([$clinic, $otherClinic] as $cid) {
            $p = $db->table('patients')->select('id')->where('clinic_id', $cid)->like('patient_id', 'STG_', 'after')->orderBy('id')->get()->getRowArray();
            if (!$p) { throw new RuntimeException('Owned synthetic patient missing.'); }
            $patients[$cid] = (int)$p['id'];
        }
        $priorClinic = \App\Libraries\ApiRuntimeContext::getClinicId(); $priorUser = \App\Libraries\ApiRuntimeContext::getUserId();
        \App\Libraries\ApiRuntimeContext::setClinicId($clinic);
        $run = 'F1_' . bin2hex(random_bytes(5)); $checks = [];
        $assert = static function (bool $ok, string $label) use (&$checks): void { if (!$ok) { throw new RuntimeException($label); } $checks[] = $label; };
        $equal = static function ($actual, $expected, string $label) use ($assert): void { $assert(abs((float)$actual - (float)$expected) < 0.001, $label . ' (expected ' . $expected . ', actual ' . $actual . ')'); };
        $insert = static function (string $table, array $data) use ($db): int { if (!$db->table($table)->insert($data)) { throw new RuntimeException('Fixture insert failed: ' . $table); } return (int)$db->insertID(); };
        $finance = static function (string $type, float $amount, array $extra = [], ?int $cid = null) use ($insert, $run, $date, $clinic, $patients): int {
            $cid ??= $clinic;
            return $insert('finances', array_merge(['clinic_id'=>$cid,'patient_id'=>$patients[$cid],'transaction_id'=>$run . '_' . bin2hex(random_bytes(2)),'transaction_type'=>$type,'amount'=>$amount,'total_amount'=>$amount,'amount_original'=>$amount,'currency'=>'ILS','exchange_rate'=>1,'payment_method'=>'cash','liquidity_account'=>'safe_cash','payment_status'=>'paid','description'=>'Synthetic F1 golden fixture','service_type'=>'other','discount_amount'=>0,'tax_percentage'=>0,'tax_amount'=>0,'amount_paid'=>$amount,'balance_due'=>0,'transaction_date'=>$date,'paid_date'=>$date,'created_at'=>$date.' 12:00:00','updated_at'=>$date.' 12:00:00'], $extra));
        };
        $payment = static function (int $financeId, string $type, float $amount, array $extra = []) use ($insert, $date, $clinic): int {
            return $insert('finance_payments', array_merge(['clinic_id'=>$clinic,'finance_id'=>$financeId,'amount'=>$amount,'currency'=>'ILS','amount_original'=>$amount,'exchange_rate'=>1,'type'=>$type,'payment_method'=>'cash','liquidity_account'=>'safe_cash','payment_date'=>$date,'created_at'=>$date.' 12:00:00','updated_at'=>$date.' 12:00:00'], $extra));
        };
        $started = false;
        try {
            if (!$db->transBegin()) { throw new RuntimeException('Unable to begin fixture transaction.'); } $started = true;
            $invoice = $finance('invoice', 200, ['amount_paid'=>92,'balance_due'=>108,'payment_status'=>'partial']);
            $receipt = $payment($invoice, 'payment', 100);
            $payment($invoice, 'refund', 10, ['related_payment_id'=>$receipt]);
            $payment($invoice, 'void', 5, ['related_payment_id'=>$receipt]);
            $payment($invoice, 'adjustment', 7, ['liquidity_account'=>null,'payment_method'=>'other']);
            $standalone = $finance('payment', 20);
            $finance('refund', 2);
            $foreignInvoice = $finance('invoice', 35, ['currency'=>'USD','amount_original'=>10,'exchange_rate'=>3.5]);
            $payment($foreignInvoice, 'payment', 35, ['currency'=>'USD','amount_original'=>10,'exchange_rate'=>3.5]);
            $expense = $finance('expense', 30, ['amount_paid'=>25,'balance_due'=>5,'payment_status'=>'partial','expense_category'=>'supplies']);
            $expenseReceipt = $payment($expense, 'payment', 30);
            $payment($expense, 'refund', 5, ['related_payment_id'=>$expenseReceipt]);
            $cogs = $finance('expense', 12, ['expense_category'=>'inventory_cogs','source_entity_type'=>'inventory_usage','source_entity_id'=>null]);
            $otherInvoice = $finance('invoice', 999, [], $otherClinic);
            $payment($otherInvoice, 'payment', 999, ['clinic_id'=>$otherClinic]);
            $payment($otherInvoice, 'payment', 777); // Deliberately malformed parent/clinic pair: compound join must exclude it.
            $insert('finance_liquidity_transfers', ['clinic_id'=>$clinic,'from_account'=>'safe_cash','to_account'=>'bank','amount'=>40,'transfer_date'=>$date,'transfer_type'=>'internal_transfer','notes'=>$run,'created_at'=>$date.' 12:00:00','updated_at'=>$date.' 12:00:00']);
            $model = new \App\Models\FinanceModel($db);
            $stats = $model->getFinanceStatsByClinic($clinic, $date, $date);
            $equal($stats['total_revenue'], 138, 'Patient net collections exclude expense settlements and cross-clinic mismatches');
            $equal($model->getMonthlyRevenueByClinic($clinic, $year)[0]['total_amount'] ?? null, 138, 'Clinic monthly signed base-currency collections');
            $equal($model->getMonthlyRevenueByClinicRange($clinic, '2099-06-01', '2099-06-30')[0]['total_amount'] ?? null, 138, 'Month-range signed base-currency collections');
            $equal($model->getMonthlyRevenue($year, $clinic)[0]['total_amount'] ?? null, 138, 'Legacy monthly method uses signed collections');
            $methods = $model->getPaymentMethodsStats($date, $date, $clinic);
            $equal(array_sum(array_column($methods, 'total_amount')), 138, 'Payment-method totals reconcile with patient net collections');
            $patientIds = array_map('intval', array_column($model->getFinanceByPatient($patients[$clinic]), 'id'));
            $assert(in_array($invoice, $patientIds, true) && in_array($standalone, $patientIds, true), 'Patient-facing ledger retains ordinary invoices and payments with NULL expense category');
            $assert(!in_array($cogs, $patientIds, true), 'Patient-facing ledger excludes accrual inventory COGS');
            $assert($db->table('finances')->where('id',$cogs)->where('patient_id',$patients[$clinic])->countAllResults() === 1, 'COGS row and historical patient reference preserved');
            $reflection = new ReflectionClass(\App\Controllers\Finance::class); $controller = $reflection->newInstanceWithoutConstructor(); $reflection->getProperty('db')->setValue($controller, $db);
            $ledger = $reflection->getMethod('buildFinancialLedger')->invoke($controller, $clinic, $date, $date);
            $equal($ledger['summary']['inflow_total'], 160, 'External gross inflow includes vendor refund but excludes internal transfer');
            $equal($ledger['summary']['outflow_total'], 47, 'External gross outflow includes patient reversals and vendor payment');
            $equal($ledger['summary']['net_total'], 113, 'External net cash conserved');
            $equal($ledger['summary']['count'], 8, 'External cash movement count excludes adjustment, transfers and accrual COGS');
            $transfers = array_filter($ledger['treasury_rows'], static fn($r)=>($r['event_source']??'')==='liquidity_transfer');
            $equal(count($transfers), 2, 'Treasury retains both internal transfer legs');
            $equal(array_sum(array_column($transfers, 'signed_amount_base')), 0, 'Internal transfer conserves total treasury');
            $cash = $reflection->getMethod('summarizeCashReport')->invoke($controller, $ledger['rows']);
            $equal($cash['gross_collections'], 155, 'Patient gross receipts distinct from gross external inflow');
            $equal($cash['refunds_total'], 17, 'Patient reversals are not supplier refunds');
            $equal($cash['expenses_paid'], 25, 'Expense refunds reduce paid expense');
            $equal($cash['net_clinic_cash'], 113, 'Cash-report summary agrees with ledger net');
            $equal($cash['total_transactions'], 8, 'Cash-report count excludes noncash and internal movements');
            $payment($cogs, 'payment', 3);
            $settledCogsLedger = $reflection->getMethod('buildFinancialLedger')->invoke($controller, $clinic, $date, $date);
            $equal($settledCogsLedger['summary']['outflow_total'], 50, 'Genuine COGS cash settlement is included without its accrual fallback');
            $equal($settledCogsLedger['summary']['net_total'], 110, 'Genuine COGS cash settlement reduces external cash once');
            $equal($model->getFinanceStatsByClinic($clinic, $date, $date)['total_revenue'], 138, 'Genuine COGS settlement never becomes patient revenue');
            if (!$db->transRollback()) { throw new RuntimeException('Fixture rollback failed.'); } $started = false;
            $assert((int)$db->table('finances')->like('transaction_id', $run, 'after')->countAllResults() === 0, 'Synthetic fixture rollback confirmed');
            return ['status'=>'PASS','synthetic_only'=>true,'production_modified'=>false,'run'=>$run,'checks'=>$checks,'expected'=>['patient_gross_receipts'=>155,'patient_reversals'=>17,'patient_net_collections'=>138,'gross_external_inflow'=>160,'gross_external_outflow'=>47,'net_external_cash'=>113,'net_expense_cash'=>25,'external_cash_events'=>8,'transfer_amount'=>40,'accrual_cogs_excluded_from_cash'=>12]];
        } finally {
            if ($started) { $db->transRollback(); }
            \App\Libraries\ApiRuntimeContext::clear(); if ($priorClinic !== null) { \App\Libraries\ApiRuntimeContext::setClinicId($priorClinic); } if ($priorUser !== null) { \App\Libraries\ApiRuntimeContext::setUserId($priorUser); }
        }
    }
}