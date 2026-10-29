<?php

namespace App\Services;
use CodeIgniter\Database\BaseConnection;
/** Operation identities only. Existing settlement writers own all monetary calculations. */
class FinanceSettlementRequestService  {
    private bool $schemaChecked=false;
    public const OPERATIONS=['payment','refund','void','close_balance','mark_paid','bulk_settle','provider_payment','provider_mark_paid','update'];
    public function __construct(private BaseConnection $db) {
    }
    private function table(string $name):string {
        return $this->db->escapeIdentifiers($this->db->prefixTable($name));
    }
    public function requireSchema():void  {
        if($this->schemaChecked)return;
        $table=$this->db->prefixTable('finance_settlement_requests');
        $engine=$this->db->query('SELECT ENGINE FROM information_schema.TABLES WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME=?',[$table])->getRowArray();
        if(strtoupper((string)($engine['ENGINE']??''))!=='INNODB')throw new FinanceSettlementConflict('SETTLEMENT_SCHEMA_REQUIRED',503);
        $fields=$this->db->query('SELECT COLUMN_NAME,IS_NULLABLE,COLUMN_TYPE,CHARACTER_MAXIMUM_LENGTH,EXTRA,COLUMN_KEY FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME=?',[$table])->getResultArray();
        $map=array_column($fields,'IS_NULLABLE','COLUMN_NAME');
        foreach(['id','clinic_id','actor_id','operation','target_hash','request_key','input_hash','expected_revision','created_at'] as $field)if(($map[$field]??'')!=='NO')throw new FinanceSettlementConflict('SETTLEMENT_SCHEMA_REQUIRED',503);
        foreach(['result_json','terms_hash','completed_at'] as $field)if(!array_key_exists($field,$map))throw new FinanceSettlementConflict('SETTLEMENT_SCHEMA_REQUIRED',503);
        $definitions=array_column($fields,null,'COLUMN_NAME');
        foreach(['clinic_id','actor_id'] as $scope)if(!preg_match('/^int(?:\([0-9]+\))? unsigned$/i',(string)($definitions[$scope]['COLUMN_TYPE']??'')))throw new FinanceSettlementConflict('SETTLEMENT_SCHEMA_REQUIRED',503);
        foreach(['target_hash'=>64,'input_hash'=>64,'expected_revision'=>64,'terms_hash'=>64,'request_key'=>191,'operation'=>32] as $field=>$length) {
            if((int)($definitions[$field]['CHARACTER_MAXIMUM_LENGTH']??0)!==$length)throw new FinanceSettlementConflict('SETTLEMENT_SCHEMA_REQUIRED',503);
        }
        if(!str_starts_with(strtolower($definitions['id']['COLUMN_TYPE']??''),'bigint')||($definitions['id']['COLUMN_KEY']??'')!=='PRI'||!str_contains($definitions['id']['EXTRA']??'','auto_increment')||strtolower($definitions['result_json']['COLUMN_TYPE']??'')!=='longtext')throw new FinanceSettlementConflict('SETTLEMENT_SCHEMA_REQUIRED',503);
        foreach(['finances','finance_payments','provider_payouts','provider_payout_payments','activity_logs'] as $parent) {
            $parentEngine=$this->db->query('SELECT ENGINE FROM information_schema.TABLES WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME=?',[$this->db->prefixTable($parent)])->getRowArray();
            if(strtoupper((string)($parentEngine['ENGINE']??''))!=='INNODB')throw new FinanceSettlementConflict('SETTLEMENT_SCHEMA_REQUIRED',503);
        }
        $index=$this->db->query('SELECT COLUMN_NAME,NON_UNIQUE,SUB_PART FROM information_schema.STATISTICS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME=? AND INDEX_NAME=? ORDER BY SEQ_IN_INDEX',[$table,'uq_settlement_clinic_request'])->getResultArray();
        if(array_column($index,'COLUMN_NAME')!==['clinic_id','request_key']||array_filter($index,static fn($r)=>(int)$r['NON_UNIQUE']!==0||$r['SUB_PART']!==null))throw new FinanceSettlementConflict('SETTLEMENT_SCHEMA_REQUIRED',503);
        $this->schemaChecked=true;
    }
    private function owned():void {
        if((int)$this->db->transDepth!==1||!$this->db->transStatus())throw new \RuntimeException('Settlement guard requires one healthy owner transaction.');
    }
    private function ids(array $ids):array  {
        if(!$ids||count($ids)>500)throw new FinanceSettlementConflict('SETTLEMENT_CONTEXT_REQUIRED');
        foreach($ids as $id)if(!is_scalar($id)||!preg_match('/^[1-9][0-9]*$/D',(string)$id)||(int)$id>2147483647)throw new FinanceSettlementConflict('SETTLEMENT_CONTEXT_REQUIRED');
        $ids=array_values(array_unique(array_map('intval',$ids)));
        sort($ids,SORT_NUMERIC);
        return $ids;
    }
    private function json(mixed $value):string {
        return json_encode($value,JSON_THROW_ON_ERROR|JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES|JSON_PRESERVE_ZERO_FRACTION);
    }
    private function canonical(array $input):array  {
        if(!array_is_list($input))ksort($input,SORT_STRING);
        foreach($input as $key=>&$value) {
            if(is_array($value))$value=$this->canonical($value);
            elseif(is_object($value)||is_resource($value))throw new FinanceSettlementConflict('SETTLEMENT_CONTEXT_REQUIRED');
            elseif(in_array((string)$key,['amount','amount_original','exchange_rate'],true)&&is_numeric($value)&&is_finite((float)$value))$value=number_format(round((float)$value,$key==='exchange_rate'?6:2),$key==='exchange_rate'?6:2,'.','');
        }
        unset($value);
        return $input;
    }
    /** Only transport fields are excluded. All submitted business fields participate. */
    public function terms(array $input):array  {
        foreach(['request_key','settlement_revision','csrf_test_name','return_to','source_modal'] as $key)unset($input[$key]);
        $csrf=config('Security')->tokenName;
        if($csrf)unset($input[$csrf]);
        return $this->canonical($input);
    }
    private function targetHash(array $ids):string {
        return hash('sha256',$this->json($ids));
    }
    private function prefix(int $clinic,int $actor,string $operation,array $ids):string {
        return 'v1.'.$clinic.'.'.$actor.'.'.$operation.'.'.substr($this->targetHash($ids),0,16).'.';
    }
    /** Locks every parent in ascending order before current ledger reads. */
    private function snapshot(int $clinic,string $operation,array $targets):array  {
        $this->owned();
        $ids=$targets;
        $provider=str_starts_with($operation,'provider_');
        if($provider) {
            $ids=[];
            foreach($targets as $id) {
                $r=$this->db->table('provider_payouts')->where('clinic_id',$clinic)->where('id',$id)->get()->getRowArray();
                if(!$r)throw new FinanceSettlementConflict('SETTLEMENT_NOT_FOUND',404);
                $ids[]=(int)$r['finance_id'];
            }
            $ids=$this->ids($ids);
        }
        $parents=[];
        $state=[];
        $receiptIds=['finance'=>[],'provider'=>[]];
        foreach($ids as $id) {
            $r=$this->db->query('SELECT * FROM '.$this->table('finances').' WHERE clinic_id=? AND id=? FOR UPDATE',[$clinic,$id])->getRowArray();
            if(!$r)throw new FinanceSettlementConflict('SETTLEMENT_NOT_FOUND',404);
            $parents[$id]=$r;
        }
        foreach($parents as $id=>$parent) {
            $payments=$this->db->query('SELECT * FROM '.$this->table('finance_payments').' WHERE clinic_id=? AND finance_id=? ORDER BY id FOR UPDATE',[$clinic,$id])->getResultArray();
            $payouts=$this->db->query('SELECT * FROM '.$this->table('provider_payouts').' WHERE clinic_id=? AND finance_id=? ORDER BY id FOR UPDATE',[$clinic,$id])->getResultArray();
            $providerPayments=[];
            foreach($payouts as $payout) {
                $pp=$this->db->query('SELECT * FROM '.$this->table('provider_payout_payments').' WHERE clinic_id=? AND provider_payout_id=? ORDER BY id FOR UPDATE',[$clinic,$payout['id']])->getResultArray();
                $providerPayments=array_merge($providerPayments,$pp);
            }
            $correction=[];
            foreach([['invoice_corrections','finance_id'],['invoice_credit_applications','source_finance_id'],['invoice_credit_applications','target_finance_id']] as [$table,$column])if($this->db->tableExists($table))$correction[$table.'.'.$column]=$this->db->query('SELECT * FROM '.$this->table($table).' WHERE clinic_id=? AND '.$column.'=? ORDER BY id FOR UPDATE',[$clinic,$id])->getResultArray();
            if($this->db->tableExists('invoice_credit_application_reversals')) {
                $applications=[];
                foreach($correction as $name=>$rows)if(str_starts_with($name,'invoice_credit_applications.'))foreach($rows as $application)$applications[(int)$application['id']]=true;
                ksort($applications,SORT_NUMERIC);
                foreach(array_keys($applications) as $applicationId)$correction['reversals.'.$applicationId]=$this->db->query('SELECT * FROM '.$this->table('invoice_credit_application_reversals').' WHERE clinic_id=? AND application_id=? ORDER BY id FOR UPDATE',[$clinic,$applicationId])->getResultArray();
            }
            $state[]=['parent'=>$parent,'payments'=>$payments,'payouts'=>$payouts,'provider_payments'=>$providerPayments,'corrections'=>$correction];
            $receiptIds['finance']=array_merge($receiptIds['finance'],array_map('intval',array_column($payments,'id')));
            $receiptIds['provider']=array_merge($receiptIds['provider'],array_map('intval',array_column($providerPayments,'id')));
        }
        if($provider) {
            $found=[];
            foreach($state as $s)foreach($s['payouts'] as $p)if(in_array((int)$p['id'],$targets,true))$found[]=(int)$p['id'];
            sort($found);
            if($found!==$targets)throw new FinanceSettlementConflict('SETTLEMENT_NOT_FOUND',404);
        }
        return ['revision'=>hash('sha256',$this->json(['state'=>$state,'base_currency'=>(string)settings()->get('currency','ILS')])),'parents'=>$parents,'state'=>$state,'receipt_ids'=>$receiptIds];
    }
    /** Bind a native edit form to the exact persisted parent it displayed. */
    public static function parentVersion(array $parent): string
    {
        $values=[];
        foreach(['id','clinic_id','patient_id','examination_id','transaction_type','amount','amount_original','currency','exchange_rate','discount_amount','tax_amount','tax_percentage','total_amount','amount_paid','balance_due','payment_status','paid_date','due_date','payment_method','liquidity_account','counterparty_name','supplier_id','description','service_type','service_details','transaction_date','notes','updated_at'] as $field){
            $values[$field]=isset($parent[$field])?(string)$parent[$field]:null;
        }
        return hash('sha256',json_encode($values,JSON_THROW_ON_ERROR|JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES));
    }
    public function issue(int $clinic,int $actor,string $operation,array $targets):array  {
        $this->requireSchema();
        $targets=$this->ids($targets);
        if($clinic<=0||$actor<=0||!in_array($operation,self::OPERATIONS,true)||($operation!=='bulk_settle'&&count($targets)!==1))throw new FinanceSettlementConflict('SETTLEMENT_CONTEXT_REQUIRED');
        if((int)$this->db->transDepth!==0||!$this->db->transBegin())throw new \RuntimeException('Unable to read settlement context.');
        try {
            $snapshot=$this->snapshot($clinic,$operation,$targets);
            $result=['request_key'=>$this->prefix($clinic,$actor,$operation,$targets).bin2hex(random_bytes(16)),'settlement_revision'=>$snapshot['revision']];
            // Expose the current existing canonical state, never a second calculator.
            if(count($targets)===1) {
                $parent=array_values($snapshot['parents'])[0];
                $result['parent_version']=self::parentVersion($parent);
                $result['currency']=str_starts_with($operation,'provider_')?(string)($parent['currency']??''):(string)settings()->get('currency','ILS');
                $result['finance_id']=(int)$parent['id'];
                $result['transaction_id']=$parent['transaction_id'];
                if(str_starts_with($operation,'provider_')) {
                    foreach($snapshot['state'][0]['payouts'] as $p)if((int)$p['id']===$targets[0])$result['facts']=['paid'=>(string)$p['provider_paid_amount'],'due'=>(string)$p['provider_balance_due'],'total'=>(string)$p['provider_share_amount']];
                }
                else {
                    $canonical=(new FinanceSettlementService($this->db))->getLockedSettlementState($clinic,$parent);
                    $result['facts']=['paid'=>number_format((float)$canonical['base_settled_before'],2,'.',''),'due'=>number_format((float)$canonical['remaining_base'],2,'.',''),'total'=>number_format((float)$canonical['total_amount'],2,'.','')];
                }
            }
            if($operation==='refund') {
                $corrections=new InvoiceCorrectionService($this->db);
                $refundable=$corrections->supportsSchema() ? (float)$corrections->position($clinic,(int)$parent['id'],true)['refundable_base'] : (new \App\Models\FinancePaymentModel($this->db))->getNetCashPaidTotal($clinic,(int)$parent['id']);
                $result['facts']['refundable']=number_format($refundable,2,'.','');
            }
            if(!$this->db->transStatus()||!$this->db->transCommit())throw new \RuntimeException('Unable to read settlement context.');
            return $result;
        }
        catch(\Throwable $e) {
            $this->db->transRollback();
            throw $e;
        }
    }
    public function claim(int $clinic,int $actor,string $operation,array $targets,array $wire,array $input):array  {
        $this->owned();
        $this->requireSchema();
        $targets=$this->ids($targets);
        $key=$wire['request_key']??null;
        $revision=$wire['settlement_revision']??null;
        if($clinic<=0||$actor<=0||!in_array($operation,self::OPERATIONS,true)||($operation!=='bulk_settle'&&count($targets)!==1)||!is_string($key)||!str_starts_with($key,$this->prefix($clinic,$actor,$operation,$targets))||!preg_match('/^[a-f0-9]{32}$/D',substr($key,strlen($this->prefix($clinic,$actor,$operation,$targets))))||!is_string($revision)||!preg_match('/^[a-f0-9]{64}$/D',$revision))throw new FinanceSettlementConflict('SETTLEMENT_CONTEXT_REQUIRED');
        $snapshot=$this->snapshot($clinic,$operation,$targets);
        $hash=hash('sha256',$this->json($this->terms($input)));
        $targetHash=$this->targetHash($targets);
        $row=$this->db->query('SELECT * FROM '.$this->table('finance_settlement_requests').' WHERE clinic_id=? AND request_key=? FOR UPDATE',[$clinic,$key])->getRowArray();
        if($row) {
            if((int)$row['actor_id']!==$actor||$row['operation']!==$operation||!hash_equals($row['target_hash'],$targetHash)||!hash_equals($row['input_hash'],$hash)||!hash_equals($row['expected_revision'],$revision))throw new FinanceSettlementConflict('SETTLEMENT_IDENTITY_CONFLICT');
            if(empty($row['completed_at'])||$row['result_json']===null||$row['terms_hash']===null)throw new FinanceSettlementConflict('SETTLEMENT_RECONCILIATION_REQUIRED');
            $result=json_decode($row['result_json'],true,512,JSON_THROW_ON_ERROR);
            if(!hash_equals($row['terms_hash'],hash('sha256',$this->json($result))))throw new FinanceSettlementConflict('SETTLEMENT_RECONCILIATION_REQUIRED');
            return ['id'=>(int)$row['id'],'replayed'=>true,'result'=>$result];
        }
        if(!hash_equals($snapshot['revision'],$revision))throw new FinanceSettlementConflict('SETTLEMENT_STALE');
        if(!$this->db->table('finance_settlement_requests')->insert(['clinic_id'=>$clinic,'actor_id'=>$actor,'operation'=>$operation,'target_hash'=>$targetHash,'request_key'=>$key,'input_hash'=>$hash,'expected_revision'=>$revision,'created_at'=>date('Y-m-d H:i:s')]))throw new \RuntimeException('Unable to persist settlement request.');
        return ['id'=>(int)$this->db->insertID(),'replayed'=>false,'clinic'=>$clinic,'operation'=>$operation,'targets'=>$targets,'snapshot'=>$snapshot];
    }
    public function complete(array $claim,array $outcome=[]):array  {
        $this->owned();
        if($claim['replayed'])throw new \LogicException('Cannot rewrite a completed operation.');
        $snapshot=$this->snapshot($claim['clinic'],$claim['operation'],$claim['targets']);
        $receipts=['finance'=>[],'provider'=>[]];
        foreach($snapshot['state'] as $state) {
            foreach($state['payments'] as $p)if(!in_array((int)$p['id'],$claim['snapshot']['receipt_ids']['finance'],true))$receipts['finance'][]=$p;
            foreach($state['provider_payments'] as $p)if(!in_array((int)$p['id'],$claim['snapshot']['receipt_ids']['provider'],true))$receipts['provider'][]=$p;
        }
        $result=['operation'=>$claim['operation'],'targets'=>$claim['targets'],'receipts'=>$receipts,'outcome'=>$outcome];
        $json=$this->json($result);
        if(!$this->db->table('finance_settlement_requests')->where('id',$claim['id'])->where('clinic_id',$claim['clinic'])->where('completed_at',null)->update(['result_json'=>$json,'terms_hash'=>hash('sha256',$json),'completed_at'=>date('Y-m-d H:i:s')])||$this->db->affectedRows()!==1)throw new \RuntimeException('Unable to complete settlement request.');
        return $result;
    }
}
