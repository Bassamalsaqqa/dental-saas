<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class HardenNotificationsPhase0 extends Migration
{
    public function up()
    {
        if (!$this->db->tableExists('notifications')) {
            return;
        }

        $this->db->query(
            "ALTER TABLE `notifications` MODIFY COLUMN `status` " .
            "ENUM('pending','processing','blocked','sent','delivered','read','failed','rejected') NOT NULL"
        );

        if (!$this->db->fieldExists('locked_at', 'notifications')) {
            $this->forge->addColumn('notifications', [
                'locked_at' => [
                    'type' => 'DATETIME',
                    'null' => true,
                    'after' => 'failure_reason',
                ],
            ]);
        }

        if (!$this->db->fieldExists('locked_by', 'notifications')) {
            $this->forge->addColumn('notifications', [
                'locked_by' => [
                    'type' => 'VARCHAR',
                    'constraint' => 64,
                    'null' => true,
                    'after' => 'locked_at',
                ],
            ]);
        }
    }

    public function down()
    {
        if (!$this->db->tableExists('notifications')) {
            return;
        }

        if ($this->db->fieldExists('locked_by', 'notifications')) {
            $this->forge->dropColumn('notifications', 'locked_by');
        }

        if ($this->db->fieldExists('locked_at', 'notifications')) {
            $this->forge->dropColumn('notifications', 'locked_at');
        }

        $this->db->query(
            "ALTER TABLE `notifications` MODIFY COLUMN `status` " .
            "ENUM('pending','blocked','failed') NOT NULL"
        );
    }
}
