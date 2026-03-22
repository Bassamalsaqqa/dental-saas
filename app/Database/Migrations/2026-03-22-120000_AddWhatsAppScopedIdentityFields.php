<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddWhatsAppScopedIdentityFields extends Migration
{
    public function up()
    {
        if ($this->db->tableExists('patient_whatsapp')) {
            if (!$this->db->fieldExists('parent_bsuid', 'patient_whatsapp')) {
                $this->forge->addColumn('patient_whatsapp', [
                    'parent_bsuid' => [
                        'type' => 'VARCHAR',
                        'constraint' => 128,
                        'null' => true,
                        'after' => 'bsuid',
                    ],
                ]);
                $this->db->query('ALTER TABLE `patient_whatsapp` ADD KEY `clinic_id_parent_bsuid` (`clinic_id`, `parent_bsuid`)');
            }

            if (!$this->db->fieldExists('username', 'patient_whatsapp')) {
                $this->forge->addColumn('patient_whatsapp', [
                    'username' => [
                        'type' => 'VARCHAR',
                        'constraint' => 191,
                        'null' => true,
                        'after' => 'parent_bsuid',
                    ],
                ]);
            }
        }

        if ($this->db->tableExists('whatsapp_optouts') && !$this->db->fieldExists('parent_bsuid', 'whatsapp_optouts')) {
            $this->forge->addColumn('whatsapp_optouts', [
                'parent_bsuid' => [
                    'type' => 'VARCHAR',
                    'constraint' => 128,
                    'null' => true,
                    'after' => 'bsuid',
                ],
            ]);
            $this->db->query('ALTER TABLE `whatsapp_optouts` ADD KEY `idx_parent_bsuid` (`clinic_id`, `parent_bsuid`)');
        }
    }

    public function down()
    {
        if ($this->db->tableExists('whatsapp_optouts')) {
            if ($this->db->fieldExists('parent_bsuid', 'whatsapp_optouts')) {
                $this->db->query('ALTER TABLE `whatsapp_optouts` DROP KEY `idx_parent_bsuid`');
                $this->forge->dropColumn('whatsapp_optouts', 'parent_bsuid');
            }
        }

        if ($this->db->tableExists('patient_whatsapp')) {
            if ($this->db->fieldExists('username', 'patient_whatsapp')) {
                $this->forge->dropColumn('patient_whatsapp', 'username');
            }

            if ($this->db->fieldExists('parent_bsuid', 'patient_whatsapp')) {
                $this->db->query('ALTER TABLE `patient_whatsapp` DROP KEY `clinic_id_parent_bsuid`');
                $this->forge->dropColumn('patient_whatsapp', 'parent_bsuid');
            }
        }
    }
}
