<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/** Expansion only: existing appointments and historical labels are never rewritten. */
class AddDynamicDoctorScheduling extends Migration
{
    public function up()
    {
        $this->db->resetDataCache();
        if (!$this->db->fieldExists('provider_user_id', 'appointments')) {
            if (!$this->forge->addColumn('appointments', ['provider_user_id'=>[
                'type'=>'INT','constraint'=>11,'unsigned'=>true,'null'=>true,'after'=>'patient_id',
            ]])) throw new \RuntimeException('Provider column expansion failed.');
        }
        $indexes=$this->db->query("SHOW INDEX FROM appointments WHERE Key_name='idx_appointments_provider_sched'")->getResultArray();
        usort($indexes, static fn(array $a,array $b): int => (int)$a['Seq_in_index'] <=> (int)$b['Seq_in_index']);
        if ($indexes && array_column($indexes,'Column_name') !== ['clinic_id','provider_user_id','appointment_date','status']) {
            if (!$this->db->query('DROP INDEX idx_appointments_provider_sched ON appointments')) throw new \RuntimeException('Scheduling index correction failed.');
            $indexes=[];
        }
        if (!$indexes && !$this->db->query('CREATE INDEX idx_appointments_provider_sched ON appointments (clinic_id, provider_user_id, appointment_date, status)')) {
            throw new \RuntimeException('Provider scheduling index expansion failed.');
        }
        // No cross-tenant foreign-key assumption: eligibility is validated against clinic membership.
        // Nullable IDs retain history even when a provider stops being eligible.
        if (!$this->db->tableExists('clinic_provider_profiles')) {
            $this->forge->addField([
                'id'=>['type'=>'INT','constraint'=>11,'unsigned'=>true,'auto_increment'=>true],
                'clinic_id'=>['type'=>'INT','constraint'=>11,'unsigned'=>true],
                'user_id'=>['type'=>'INT','constraint'=>11,'unsigned'=>true],
                'is_bookable'=>['type'=>'TINYINT','constraint'=>1,'default'=>1],
                'is_clinician'=>['type'=>'TINYINT','constraint'=>1,'default'=>0],
                'weekly_hours_json'=>['type'=>'TEXT','null'=>true],
                'exceptions_json'=>['type'=>'TEXT','null'=>true],
                'created_at'=>['type'=>'DATETIME','null'=>true],
                'updated_at'=>['type'=>'DATETIME','null'=>true],
            ]);
            $this->forge->addKey('id',true);
            $this->forge->addUniqueKey(['clinic_id','user_id'],'uk_clinic_provider_profiles');
            if (!$this->forge->createTable('clinic_provider_profiles',true,['ENGINE'=>'InnoDB'])) {
                throw new \RuntimeException('Provider profile expansion failed.');
            }
        }
        $this->db->resetDataCache();
    }

    public function down()
    {
        $this->db->resetDataCache();
        if ($this->db->fieldExists('provider_user_id','appointments')
            && $this->db->table('appointments')->where('provider_user_id IS NOT NULL',null,false)->countAllResults()>0) {
            throw new \RuntimeException('Refusing rollback: provider-linked appointments exist.');
        }
        if ($this->db->tableExists('clinic_provider_profiles')) {
            if ($this->db->table('clinic_provider_profiles')->countAllResults()>0) {
                throw new \RuntimeException('Refusing rollback: provider profiles exist.');
            }
            if (!$this->forge->dropTable('clinic_provider_profiles',true)) throw new \RuntimeException('Provider profile rollback failed.');
        }
        // Tolerate an earlier optional FK on an empty disposable/staging expansion.
        $fk = $this->db->query("SELECT CONSTRAINT_NAME FROM information_schema.TABLE_CONSTRAINTS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='appointments' AND CONSTRAINT_NAME='fk_appointments_provider_user' AND CONSTRAINT_TYPE='FOREIGN KEY'")->getRowArray();
        if ($fk && !$this->db->query('ALTER TABLE appointments DROP FOREIGN KEY fk_appointments_provider_user')) {
            throw new \RuntimeException('Provider foreign key rollback failed.');
        }
        $index=$this->db->query("SHOW INDEX FROM appointments WHERE Key_name='idx_appointments_provider_sched'")->getResultArray();
        if ($index && !$this->db->query('DROP INDEX idx_appointments_provider_sched ON appointments')) throw new \RuntimeException('Scheduling index rollback failed.');
        if ($this->db->fieldExists('provider_user_id','appointments')
            && !$this->forge->dropColumn('appointments','provider_user_id')) {
            throw new \RuntimeException('Provider column rollback failed.');
        }
        $this->db->resetDataCache();
    }
}
