<?php
declare(strict_types=1);
namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/** Additive theme-only storage. No legacy settings or business rows are updated. */
class CreateStudioThemeHistory extends Migration
{
    public function up()
    {
        $this->db->resetDataCache();
        foreach (['studio_theme_current','studio_theme_revisions'] as $table) {
            if ($this->db->tableExists($table)) throw new \RuntimeException('Theme schema already or partially exists; inspect ownership before continuing.');
        }
        $this->forge->addField([
            'scope_key'=>['type'=>'VARCHAR','constraint'=>64],
            'version'=>['type'=>'BIGINT','unsigned'=>true,'default'=>0],
            'payload_json'=>['type'=>'TEXT','null'=>true],
            'updated_at'=>['type'=>'DATETIME'],
        ]);
        $this->forge->addKey('scope_key', true);
        if (!$this->forge->createTable('studio_theme_current', false, ['ENGINE'=>'InnoDB'])) throw new \RuntimeException('Cannot create theme current state.');
        $this->forge->addField([
            'scope_key'=>['type'=>'VARCHAR','constraint'=>64],
            'version'=>['type'=>'BIGINT','unsigned'=>true],
            'actor_id'=>['type'=>'INT','unsigned'=>true],
            'action'=>['type'=>'VARCHAR','constraint'=>16],
            'reason'=>['type'=>'VARCHAR','constraint'=>500],
            'before_json'=>['type'=>'TEXT','null'=>true],
            'payload_json'=>['type'=>'TEXT','null'=>true],
            'created_at'=>['type'=>'DATETIME'],
        ]);
        $this->forge->addKey(['scope_key','version'], true);
        if (!$this->forge->createTable('studio_theme_revisions', false, ['ENGINE'=>'InnoDB'])) throw new \RuntimeException('Cannot create theme history.');
        if (!$this->db->table('studio_theme_current')->insert(['scope_key'=>'platform','version'=>0,'payload_json'=>null,'updated_at'=>gmdate('Y-m-d H:i:s')])) throw new \RuntimeException('Cannot initialize theme lock anchor.');
        $this->db->resetDataCache();
    }

    public function down()
    {
        // History and explicit clinic preferences survive source-only feature rollback.
        // Even partial failed-up DDL requires verified ownership before manual removal.
        throw new \RuntimeException('Theme configuration/history is preserved; automatic schema deletion is not supported.');
    }
}
