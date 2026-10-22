<?php
namespace App\Database\Migrations;
use CodeIgniter\Database\Migration;
/** Derived artifacts only; original attachment rows/files are never rewritten. */
class CreateAttachmentVariants extends Migration
{
    public function up()
    {
        $this->db->resetDataCache();
        foreach (['clinics'=>['id'], 'file_attachments'=>['id','clinic_id','file_path','mime_type','file_size','deleted_at']] as $table=>$fields) {
            if (!$this->db->tableExists($table)) { throw new \RuntimeException('Derivative parent schema unavailable: ' . $table); }
            foreach ($fields as $field) { if (!$this->db->fieldExists($field,$table)) { throw new \RuntimeException('Derivative parent field unavailable: ' . $table . '.' . $field); } }
            $engine = $this->db->query('SELECT ENGINE FROM information_schema.TABLES WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ?', [$this->db->prefixTable($table)]);
            if ($engine === false || strtoupper((string) ($engine->getRowArray()['ENGINE'] ?? '')) !== 'INNODB') { throw new \RuntimeException('Derivatives require transactional InnoDB parents: ' . $table); }
        }
        if ($this->db->tableExists('attachment_variants')) { throw new \RuntimeException('Variant table already exists; verify its exact schema before continuing.'); }
        $this->forge->addField([
            'id'=>['type'=>'BIGINT','unsigned'=>true,'auto_increment'=>true],
            'clinic_id'=>['type'=>'INT','unsigned'=>true],
            'attachment_id'=>['type'=>'INT','unsigned'=>true],
            'kind'=>['type'=>'VARCHAR','constraint'=>16],
            'source_sha256'=>['type'=>'CHAR','constraint'=>64],
            'file_sha256'=>['type'=>'CHAR','constraint'=>64],
            'relative_path'=>['type'=>'VARCHAR','constraint'=>255],
            'mime_type'=>['type'=>'VARCHAR','constraint'=>50],
            'file_size'=>['type'=>'INT','unsigned'=>true],
            'width'=>['type'=>'INT','unsigned'=>true],
            'height'=>['type'=>'INT','unsigned'=>true],
            'created_at'=>['type'=>'DATETIME'],
        ]);
        $this->forge->addKey('id',true);
        $this->forge->addUniqueKey(['clinic_id','attachment_id','kind','source_sha256'],'uq_attachment_variant_source');
        if (!$this->forge->createTable('attachment_variants',false,['ENGINE'=>'InnoDB'])) { throw new \RuntimeException('Variant schema creation failed.'); }
        $this->db->resetDataCache();
        foreach (['id','clinic_id','attachment_id','kind','source_sha256','file_sha256','relative_path','mime_type','file_size','width','height','created_at'] as $field) {
            if (!$this->db->fieldExists($field,'attachment_variants')) { throw new \RuntimeException('Variant schema verification failed.'); }
        }
    }
    public function down()
    {
        if ($this->db->tableExists('attachment_variants') && $this->db->table('attachment_variants')->countAllResults() > 0) {
            throw new \RuntimeException('Variant metadata is retained evidence; no automatic destructive rollback.');
        }
        if ($this->db->tableExists('attachment_variants') && !$this->forge->dropTable('attachment_variants')) { throw new \RuntimeException('Empty variant schema removal failed.'); }
    }
}
