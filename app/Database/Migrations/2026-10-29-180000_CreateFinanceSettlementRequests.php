<?php

namespace App\Database\Migrations;
use CodeIgniter\Database\Migration;
/** Additive operation evidence. Never rewrites a historical finance or receipt. */
class CreateFinanceSettlementRequests extends Migration  {
    private bool $createdHere = false;
    public function up()  {
        if((int)$this->db->transDepth!==0)throw new \RuntimeException('Settlement DDL must run outside a financial transaction.');
        $this->db->resetDataCache();
        foreach (['clinics','finances','finance_payments','provider_payouts','provider_payout_payments','activity_logs'] as $parent)  {
            $row=$this->db->query('SELECT ENGINE FROM information_schema.TABLES WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME=?',[$this->db->prefixTable($parent)])->getRowArray();
            if(strtoupper((string)($row['ENGINE']??''))!=='INNODB')throw new \RuntimeException('Settlement replay requires existing InnoDB parents: '.$parent);
        }
        if($this->db->tableExists('finance_settlement_requests'))throw new \RuntimeException('Settlement request table already exists; do not adopt or overwrite it.');
        $this->forge->addField([
            'id'=>['type'=>'BIGINT','unsigned'=>true,'auto_increment'=>true],
            'clinic_id'=>['type'=>'INT','unsigned'=>true],
            'actor_id'=>['type'=>'INT','unsigned'=>true],
            'operation'=>['type'=>'VARCHAR','constraint'=>32],
            'target_hash'=>['type'=>'CHAR','constraint'=>64],
            'request_key'=>['type'=>'VARCHAR','constraint'=>191],
            'input_hash'=>['type'=>'CHAR','constraint'=>64],
            'expected_revision'=>['type'=>'CHAR','constraint'=>64],
            'result_json'=>['type'=>'LONGTEXT','null'=>true],
            'terms_hash'=>['type'=>'CHAR','constraint'=>64,'null'=>true],
            'created_at'=>['type'=>'DATETIME'],
            'completed_at'=>['type'=>'DATETIME','null'=>true],
        ]);
        $this->forge->addKey('id',true);
        $this->forge->addUniqueKey(['clinic_id','request_key'],'uq_settlement_clinic_request');
        $this->forge->addKey(['clinic_id','actor_id','created_at'],false,false,'ix_settlement_actor_time');
        if(!$this->forge->createTable('finance_settlement_requests',false,['ENGINE'=>'InnoDB']))throw new \RuntimeException('Unable to create settlement identities.');
        $this->createdHere = true;
        $this->db->resetDataCache();
        (new \App\Services\FinanceSettlementRequestService($this->db))->requireSchema();
    }
    public function down()  {
        if((int)$this->db->transDepth!==0)throw new \RuntimeException('Settlement DDL must run outside a financial transaction.');
        $this->db->resetDataCache();
        if(!$this->db->tableExists('finance_settlement_requests'))return;
        if(!$this->createdHere)throw new \RuntimeException('Rollback requires proven table ownership; retain operation evidence.');
        (new \App\Services\FinanceSettlementRequestService($this->db))->requireSchema();
        if($this->db->table('finance_settlement_requests')->countAllResults()>0)throw new \RuntimeException('Settlement identities are retained financial evidence; rollback refused.');
        if(!$this->forge->dropTable('finance_settlement_requests'))throw new \RuntimeException('Unable to remove empty settlement identity table.');
    }
}
