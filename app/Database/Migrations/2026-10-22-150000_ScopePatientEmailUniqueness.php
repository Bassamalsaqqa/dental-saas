<?php
namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/** Add tenant uniqueness before removing the obsolete global constraint. No row writes. */
class ScopePatientEmailUniqueness extends Migration
{
    private function table(): string { return $this->db->escapeIdentifiers($this->db->prefixTable('patients')); }
    private function indexes(): array
    {
        $result=$this->db->query('SHOW INDEX FROM '.$this->table());
        if($result===false)throw new \RuntimeException('Patient indexes unavailable.');
        $indexes=[];
        foreach($result->getResultArray() as$row){$indexes[$row['Key_name']][]=$row;}
        foreach($indexes as&$rows){usort($rows,static fn($a,$b)=>(int)$a['Seq_in_index']<=>(int)$b['Seq_in_index']);}
        unset($rows);return$indexes;
    }
    private function exactUnique(array $rows,array $columns): bool
    {
        if(count($rows)!==count($columns))return false;
        foreach($rows as$i=>$row){if((int)$row['Non_unique']!==0||$row['Column_name']!==$columns[$i]||$row['Sub_part']!==null)return false;}
        return true;
    }
    public function up()
    {
        if(!$this->db->tableExists('patients')||!$this->db->fieldExists('clinic_id','patients')||!$this->db->fieldExists('email','patients'))throw new \RuntimeException('Canonical patient schema unavailable.');
        $table=$this->table();
        $invalid=$this->db->query("SELECT id FROM {$table} WHERE clinic_id IS NULL OR clinic_id<=0 LIMIT 1");
        $duplicates=$this->db->query("SELECT clinic_id,email FROM {$table} WHERE email IS NOT NULL GROUP BY clinic_id,email HAVING COUNT(*)>1 LIMIT 1");
        if($invalid===false||$duplicates===false||$invalid->getRowArray()||$duplicates->getRowArray())throw new \RuntimeException('Patient tenant/email history requires reconciliation; no rows were changed.');
        $indexes=$this->indexes();$scoped=false;
        foreach($indexes as$rows){if($this->exactUnique($rows,['clinic_id','email']))$scoped=true;}
        if(!$scoped){
            if(isset($indexes['uq_patients_clinic_email']))throw new \RuntimeException('Patient scoped-email index name conflicts.');
            if($this->db->query("ALTER TABLE {$table} ADD UNIQUE KEY uq_patients_clinic_email (clinic_id,email)")===false)throw new \RuntimeException('Unable to establish tenant-scoped patient email uniqueness.');
        }
        // Re-read and verify the new constraint before dropping any old one.
        $indexes=$this->indexes();$scoped=false;
        foreach($indexes as$rows){if($this->exactUnique($rows,['clinic_id','email']))$scoped=true;}
        if(!$scoped)throw new \RuntimeException('Tenant-scoped patient email constraint not verified.');
        foreach($indexes as$name=>$rows){
            if($name!=='PRIMARY'&&$this->exactUnique($rows,['email'])){
                if($this->db->query('ALTER TABLE '.$table.' DROP INDEX '.$this->db->escapeIdentifiers($name))===false)throw new \RuntimeException('Global patient email constraint removal failed; scoped constraint retained.');
            }
        }
        $indexes=$this->indexes();$scoped=false;
        foreach($indexes as$rows){if($this->exactUnique($rows,['email']))throw new \RuntimeException('Global patient email uniqueness remains.');if($this->exactUnique($rows,['clinic_id','email']))$scoped=true;}
        if(!$scoped)throw new \RuntimeException('Tenant-scoped patient email postcondition failed.');
    }
    public function down()
    {
        // Cross-clinic equal emails are legitimate after this change. Reinstating
        // global uniqueness would reject preserved business data; never undo it.
        throw new \RuntimeException('Patient tenant email uniqueness is preservation-only and cannot be automatically reversed.');
    }
}
