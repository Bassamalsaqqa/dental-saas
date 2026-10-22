<?php
namespace App\Database\Migrations;
use CodeIgniter\Database\Migration;
/** Additive replay identities; no existing financial record is modified. */
class CreateFinanceCheckoutRequests extends Migration
{
    public function up()
    {
        $this->db->resetDataCache();
        foreach (['clinics'=>['id'], 'patients'=>['id','clinic_id'], 'examinations'=>['id','clinic_id','patient_id'], 'finances'=>['id','clinic_id','patient_id','examination_id','transaction_type'], 'finance_payments'=>['id','clinic_id','finance_id','type']] as $table=>$fields) {
            if (!$this->db->tableExists($table)) { throw new \RuntimeException('Checkout parent schema is unavailable: ' . $table); }
            foreach ($fields as $field) { if (!$this->db->fieldExists($field,$table)) { throw new \RuntimeException('Checkout parent field is unavailable: ' . $table . '.' . $field); } }
            $engine = $this->db->query('SELECT ENGINE FROM information_schema.TABLES WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ?', [$this->db->prefixTable($table)]);
            if ($engine === false || strtoupper((string) ($engine->getRowArray()['ENGINE'] ?? '')) !== 'INNODB') { throw new \RuntimeException('Checkout requires transactional InnoDB parent tables: ' . $table); }
        }
        if ($this->db->tableExists('finance_checkout_requests')) { throw new \RuntimeException('Checkout table already exists; verify its exact schema before continuing.'); }
        $this->forge->addField([
            'id'=>['type'=>'BIGINT','unsigned'=>true,'auto_increment'=>true],
            'clinic_id'=>['type'=>'INT','unsigned'=>true],
            'patient_id'=>['type'=>'INT','unsigned'=>true],
            'examination_id'=>['type'=>'INT','unsigned'=>true,'null'=>true],
            'request_key'=>['type'=>'VARCHAR','constraint'=>128],
            'request_hash'=>['type'=>'CHAR','constraint'=>64],
            'finance_id'=>['type'=>'INT','unsigned'=>true,'null'=>true],
            'payment_id'=>['type'=>'INT','unsigned'=>true,'null'=>true],
            'created_by'=>['type'=>'INT','unsigned'=>true],
            'created_at'=>['type'=>'DATETIME'],
            'completed_at'=>['type'=>'DATETIME','null'=>true],
        ]);
        $this->forge->addKey('id',true);
        $this->forge->addUniqueKey(['clinic_id','request_key'],'uq_finance_checkout_request');
        $this->forge->addKey(['clinic_id','finance_id']);
        if (!$this->forge->createTable('finance_checkout_requests',false,['ENGINE'=>'InnoDB'])) { throw new \RuntimeException('Checkout schema creation failed.'); }
        $this->db->resetDataCache();
        foreach (['id','clinic_id','patient_id','examination_id','request_key','request_hash','finance_id','payment_id','created_by','created_at','completed_at'] as $field) {
            if (!$this->db->fieldExists($field,'finance_checkout_requests')) { throw new \RuntimeException('Checkout schema verification failed.'); }
        }
    }
    public function down()
    {
        if ($this->db->tableExists('finance_checkout_requests') && $this->db->table('finance_checkout_requests')->countAllResults() > 0) { throw new \RuntimeException('Checkout replay identities are retained financial evidence; no destructive rollback.'); }
        if ($this->db->tableExists('finance_checkout_requests') && !$this->forge->dropTable('finance_checkout_requests')) { throw new \RuntimeException('Empty checkout schema removal failed.'); }
    }
}
