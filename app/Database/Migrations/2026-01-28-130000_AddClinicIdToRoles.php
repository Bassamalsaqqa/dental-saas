<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddClinicIdToRoles extends Migration
{
    public function up()
    {
        if (!$this->db->fieldExists('clinic_id', 'roles')) {
            $this->forge->addColumn('roles', [
                'clinic_id' => [
                    'type'       => 'INT',
                    'constraint' => 11,
                    'unsigned'   => true,
                    'null'       => true,
                    'after'      => 'description',
                ],
            ]);

            $this->forge->addForeignKey('clinic_id', 'clinics', 'id', 'CASCADE', 'CASCADE');
        }

        // Ensure slug is unique per clinic
        $db = \Config\Database::connect();
        $indexName = 'roles_clinic_slug';
        $hasIndex = $db->query("SHOW INDEX FROM roles WHERE Key_name = ?", [$indexName])->getNumRows() > 0;

        if (!$hasIndex) {
            $db->query("CREATE UNIQUE INDEX {$indexName} ON roles (clinic_id, slug)");
        }
    }

    public function down()
    {
        if ($this->db->fieldExists('clinic_id', 'roles')) {
            $this->forge->dropColumn('roles', 'clinic_id');
        }

        $db = \Config\Database::connect();
        $indexName = 'roles_clinic_slug';
        $hasIndex = $db->query("SHOW INDEX FROM roles WHERE Key_name = ?", [$indexName])->getNumRows() > 0;

        if ($hasIndex) {
            $db->query("DROP INDEX {$indexName} ON roles");
        }
    }
}
