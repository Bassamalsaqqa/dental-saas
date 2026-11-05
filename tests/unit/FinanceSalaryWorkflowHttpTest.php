<?php
declare(strict_types=1);
namespace Tests\unit;

require_once dirname(__DIR__).'/_support/PhaseBFixture.php';
require_once dirname(__DIR__,2).'/scripts/TestDatabasePreflight.php';

/** Real registered web routes; run only via the isolated safe_test_runner. */
final class FinanceSalaryWorkflowHttpTest extends \Tests\Support\PhaseBFixture
{
    protected function setUp(): void
    {
        // These routed tests require the real settings service, even when a
        // preceding pure unit suite injected a settings mock in this process.
        \Config\Services::resetSingle('settings');
        $config = new \Config\Database();
        $errors = \Scripts\TestDatabasePreflight::validateConfigBeforeConnection($config->tests,$config->default);
        if ($errors || $config->defaultGroup!=='tests') throw new \RuntimeException('Isolated tests configuration required.');
        $db = \Config\Database::connect('tests');
        $liveErrors=\Scripts\TestDatabasePreflight::validateLiveConnection($db);
        if ($liveErrors) throw new \RuntimeException(implode('; ',$liveErrors).' [configured tests port '.(string)$config->tests['port'].']');
        $ownership=\Scripts\TestDatabasePreflight::verifyOwnershipForReset($db);
        if (!$db->tableExists('_test_db_marker') || !$ownership['allowed']) throw new \RuntimeException($ownership['reason']);
        parent::setUp();
        foreach (['view','create','edit','reports'] as $action) {
            $permission=$this->db->table('permissions')->where(['module'=>'finance','action'=>$action])->get()->getRowArray();
            self::assertNotEmpty($permission);
            $this->db->table('role_permissions')->insert(['role_id'=>$this->role,'permission_id'=>$permission['id'],'granted'=>1]);
        }
        $this->setting('currency','ILS');
        $this->setting('dynamic_doctor_scheduling_enabled',false);
        $this->login();
    }

    protected function tearDown(): void
    {
        if (isset($this->clinic)) {
            foreach (['finance_settlement_requests','provider_payout_payments','provider_payouts','finance_liquidity_transfers','finance_payments','finances','plan_audits','plan_usage'] as $table) {
                if ($this->db->tableExists($table) && $this->db->fieldExists('clinic_id',$table)) $this->db->table($table)->whereIn('clinic_id',[$this->clinic,$this->otherClinic])->delete();
            }
        }
        parent::tearDown();
    }

    private function postFinance(string $url,array $data)
    {
        $security=service('security');$config=config('Security');$hash=$security->getHash();
        return $this->withSession(session()->get())->withHeaders(['Cookie'=>$config->cookieName.'='.$hash,'X-CSRF-TOKEN'=>$hash,'X-Requested-With'=>'XMLHttpRequest'])->post($url,$data+[$config->tokenName=>$hash]);
    }

    private function salary(string $name,string $status,string $paid): array
    {
        $response=$this->postFinance('/finance/expenses/store',['counterparty_name'=>$name,'expense_category'=>'salaries','amount_original'=>'1000.00','currency'=>'ILS','exchange_rate'=>'1','payment_method'=>'cash','payment_status'=>$status,'amount_paid'=>$paid,'transaction_date'=>'2026-11-01','paid_date'=>'2026-11-02','due_date'=>'2026-11-30']);
        $response->assertRedirect();
        $row=$this->db->table('finances')->where(['clinic_id'=>$this->clinic,'counterparty_name'=>$name])->get()->getRowArray();
        self::assertNotEmpty($row,'Registered expense writer must persist the synthetic salary.');
        return $row;
    }

    public function testSalaryInitialStatesPersistWithDatedReceipts(): void
    {
        foreach ([['pending','0.00',1000.0,0],['partial','400.00',600.0,1],['paid','1000.00',0.0,1]] as [$status,$paid,$due,$count]) {
            $row=$this->salary('Synthetic '.$status,$status,$paid);
            self::assertSame($status,$row['payment_status']);
            self::assertSame($due,(float)$row['balance_due']);
            $payments=$this->db->table('finance_payments')->where(['clinic_id'=>$this->clinic,'finance_id'=>$row['id']])->get()->getResultArray();
            self::assertCount($count,$payments);
            if ($count) {self::assertSame((float)$paid,(float)$payments[0]['amount']);self::assertSame('2026-11-02',$payments[0]['payment_date']);}
        }
    }

    public function testSalaryRemainingPaymentAndReplayUseRegisteredRoutes(): void
    {
        $row=$this->salary('Synthetic partial settlement','partial','400.00');$id=(int)$row['id'];
        $this->login();
        $context=$this->withSession(session()->get())->get('/finance/settlement-context?operation=payment&targets='.$id);
        $context->assertOK();$wire=json_decode($context->getJSON(),true,512,JSON_THROW_ON_ERROR)['context'];
        $terms=['amount'=>'600.00','currency'=>'ILS','exchange_rate'=>'1','payment_method'=>'cash','liquidity_account'=>'safe_cash','payment_date'=>'2026-11-03','notes'=>'Synthetic salary final']+array_intersect_key($wire,array_flip(['request_key','settlement_revision']));
        $first=$this->postFinance('/finance/'.$id.'/payments',$terms);
        self::assertTrue(json_decode($first->getJSON(),true)['success']??false);
        $retry=$this->postFinance('/finance/'.$id.'/payments',$terms);
        self::assertTrue(json_decode($retry->getJSON(),true)['request_replayed']??false);
        $paid=$this->db->table('finances')->where('id',$id)->get()->getRowArray();
        self::assertSame('paid',$paid['payment_status']);self::assertSame(0.0,(float)$paid['balance_due']);
        $payments=$this->db->table('finance_payments')->where(['clinic_id'=>$this->clinic,'finance_id'=>$id])->get()->getResultArray();
        self::assertCount(2,$payments);self::assertSame(1000.0,array_sum(array_map('floatval',array_column($payments,'amount'))));
    }

    public function testSalaryCreationRejectsMissingPermissionWithoutWriting(): void
    {
        $permission=$this->db->table('permissions')->where(['module'=>'finance','action'=>'create'])->get()->getRowArray();
        $this->db->table('role_permissions')->where(['role_id'=>$this->role,'permission_id'=>$permission['id']])->delete();
        $this->login();
        $response=$this->postFinance('/finance/expenses/store',['counterparty_name'=>'Forbidden salary','expense_category'=>'salaries','amount_original'=>'1000.00','currency'=>'ILS','payment_method'=>'cash']);
        $response->assertStatus(403);
        self::assertSame(0,$this->db->table('finances')->where('clinic_id',$this->clinic)->countAllResults());
    }

    public function testUnexpectedExpenseFailureKeepsDiagnosticsOutOfOperatorMessage(): void
    {
        $trigger=$this->prefix.'_expense_failure';
        $safeName=$this->db->escapeIdentifiers($trigger);
        $this->db->query('CREATE TRIGGER '.$safeName.' BEFORE INSERT ON finances FOR EACH ROW BEGIN IF NEW.clinic_id = '.(int)$this->clinic." THEN SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'SYNTHETIC_RESTRICTED_DATABASE_DETAIL'; END IF; END");
        try {
            $result=$this->postFinance('/finance/expenses/store',['counterparty_name'=>'Synthetic failure payee','expense_category'=>'salaries','amount_original'=>'1000.00','currency'=>'ILS','exchange_rate'=>'1','payment_method'=>'cash','payment_status'=>'pending']);
            $result->assertRedirect();
            self::assertSame(lang('Finance.expenseSaveFailed'),session()->getFlashdata('error'));
            self::assertStringNotContainsString('SYNTHETIC_RESTRICTED_DATABASE_DETAIL',(string)$result->getBody());
            self::assertSame(0,$this->db->table('finances')->where('clinic_id',$this->clinic)->countAllResults());
        } finally {
            $this->db->query('DROP TRIGGER '.$safeName);
        }
    }

    public function testSalaryCreationRejectsInvalidCsrfWithoutWriting(): void
    {
        $config=config('Security');
        $this->expectException(\CodeIgniter\Security\Exceptions\SecurityException::class);
        try {
            $this->withSession(session()->get())->withHeaders(['X-Requested-With'=>'XMLHttpRequest','X-CSRF-TOKEN'=>'invalid-synthetic-token'])->post('/finance/expenses/store',[$config->tokenName=>'invalid-synthetic-token','counterparty_name'=>'Invalid CSRF salary','expense_category'=>'salaries','amount_original'=>'1000.00','currency'=>'ILS','payment_method'=>'cash']);
        } finally {
            self::assertSame(0,$this->db->table('finances')->where('clinic_id',$this->clinic)->countAllResults());
        }
    }

    public function testSettlementContextConcealsAnotherClinicsSalary(): void
    {
        $row=$this->salary('Synthetic owned salary','pending','0.00');
        unset($row['id']);$row['clinic_id']=$this->otherClinic;$row['transaction_id']='FOREIGN-'.bin2hex(random_bytes(4));
        $this->db->table('finances')->insert($row);$foreignId=(int)$this->db->insertID();
        $this->login();
        $result=$this->withSession(session()->get())->withHeaders(['X-Requested-With'=>'XMLHttpRequest'])->get('/finance/settlement-context?operation=payment&targets='.$foreignId);
        $result->assertStatus(404);
        self::assertSame(0,$this->db->table('finance_payments')->where('finance_id',$foreignId)->countAllResults());
    }

    public function testProviderCutsPaymentAndHistoryUseRegisteredWriters(): void
    {
        $response=$this->postFinance('/finance/store',[
            'patient_id'=>$this->patient,'transaction_type'=>'invoice','amount_original'=>'1000.00','currency'=>'ILS','exchange_rate'=>'1','payment_method'=>'cash','service_type'=>'treatment','transaction_date'=>'2026-11-01','description'=>'Synthetic provider invoice',
            'provider_split_enabled'=>'1','provider_type'=>['user','guest'],'provider_user_id'=>[$this->providers[0],''],'provider_name_snapshot'=>['','Synthetic outside provider'],'provider_split_mode'=>['percent','fixed'],'provider_split_value'=>['20','100'],'provider_mark_paid_now'=>['0','0']
        ]);
        $response->assertRedirect();
        $finance=$this->db->table('finances')->where(['clinic_id'=>$this->clinic,'description'=>'Synthetic provider invoice'])->get()->getRowArray();
        self::assertNotEmpty($finance);
        $payouts=$this->db->table('provider_payouts')->where(['clinic_id'=>$this->clinic,'finance_id'=>$finance['id']])->orderBy('id')->get()->getResultArray();
        self::assertCount(2,$payouts);self::assertSame(200.0,(float)$payouts[0]['provider_share_amount']);self::assertSame(100.0,(float)$payouts[1]['provider_share_amount']);
        $id=(int)$payouts[0]['id'];$this->login();
        $context=$this->withSession(session()->get())->get('/finance/settlement-context?operation=provider_payment&targets='.$id);
        $context->assertOK();$wire=json_decode($context->getJSON(),true)['context'];self::assertSame('ILS',$wire['currency']);
        $terms=['amount'=>'100.00','payment_method'=>'cash','liquidity_account'=>'safe_cash','paid_date'=>'2026-11-03','notes'=>'Synthetic provider partial']+array_intersect_key($wire,array_flip(['request_key','settlement_revision']));
        self::assertTrue(json_decode($this->postFinance('/finance/payouts/'.$id.'/payments',$terms)->getJSON(),true)['success']??false);
        self::assertTrue(json_decode($this->postFinance('/finance/payouts/'.$id.'/payments',$terms)->getJSON(),true)['request_replayed']??false);
        $context=$this->withSession(session()->get())->get('/finance/settlement-context?operation=provider_mark_paid&targets='.$id);
        $wire=json_decode($context->getJSON(),true)['context'];
        $terms=['payment_method'=>'cash','liquidity_account'=>'safe_cash']+array_intersect_key($wire,array_flip(['request_key','settlement_revision']));
        self::assertTrue(json_decode($this->postFinance('/finance/payouts/'.$id.'/mark-paid',$terms)->getJSON(),true)['success']??false);
        $paid=$this->db->table('provider_payouts')->where('id',$id)->get()->getRowArray();self::assertSame('paid',$paid['status']);self::assertSame(0.0,(float)$paid['provider_balance_due']);
        self::assertSame(2,$this->db->table('provider_payout_payments')->where('provider_payout_id',$id)->countAllResults());
        $financeId=(int)$finance['id'];
        foreach ([['payment','500.00','2026-11-03','/payments'],['refund','100.00','2026-11-04','/payments/refund']] as [$operation,$amount,$date,$suffix]) {
            $context=$this->withSession(session()->get())->get('/finance/settlement-context?operation='.$operation.'&targets='.$financeId);
            $context->assertOK();$wire=json_decode($context->getJSON(),true)['context'];
            $terms=['amount'=>$amount,'currency'=>'ILS','exchange_rate'=>'1','payment_method'=>'cash','liquidity_account'=>'safe_cash','payment_date'=>$date,'notes'=>'Synthetic invoice '.$operation]+array_intersect_key($wire,array_flip(['request_key','settlement_revision']));
            $result=json_decode($this->postFinance('/finance/'.$financeId.$suffix,$terms)->getJSON(),true);
            self::assertTrue($result['success']??false);
        }
        self::assertSame($paid,$this->db->table('provider_payouts')->where('id',$id)->get()->getRowArray(),'Receiving/refunding invoice cash does not rewrite existing provider obligations or payments.');
        $this->postFinance('/finance/transfers/store',['from_account'=>'safe_cash','to_account'=>'bank','amount'=>'50.00','transfer_date'=>'2026-11-05','transfer_type'=>'cash_deposit_to_bank','notes'=>'Synthetic transfer'])->assertRedirect();
        self::assertSame(1,$this->db->table('finance_liquidity_transfers')->where('clinic_id',$this->clinic)->countAllResults());
        $controller=(new \ReflectionClass(\App\Controllers\Finance::class))->newInstanceWithoutConstructor();
        (new \ReflectionProperty(\App\Controllers\Finance::class,'db'))->setValue($controller,$this->db);
        $ledger=(new \ReflectionMethod(\App\Controllers\Finance::class,'buildFinancialLedger'))->invoke($controller,$this->clinic,'2026-11-01','2026-11-30');
        self::assertSame(500.0,(float)$ledger['summary']['inflow_total']);self::assertSame(300.0,(float)$ledger['summary']['outflow_total']);self::assertSame(200.0,(float)$ledger['summary']['net_total'],'Refund/provider outflows count once; internal transfer is neutral.');
        $this->login();$history=$this->withSession(session()->get())->get('/finance/'.$finance['id']);$history->assertOK();
        self::assertStringContainsString('id="provider-payouts"',(string)$history->getBody());
        self::assertStringContainsString('Synthetic outside provider',(string)$history->getBody());
    }
}
