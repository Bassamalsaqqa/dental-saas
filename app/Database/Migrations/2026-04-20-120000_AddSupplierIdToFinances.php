<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddSupplierIdToFinances extends Migration
{
    public function up()
    {
        if (!$this->db->fieldExists('supplier_id', 'finances')) {
            $this->forge->addColumn('finances', [
                'supplier_id' => [
                    'type' => 'INT',
                    'constraint' => 11,
                    'unsigned' => true,
                    'null' => true,
                    'after' => 'counterparty_name',
                ],
            ]);
            $this->db->query('CREATE INDEX idx_finances_supplier_id ON finances (supplier_id)');
        }
    }

    public function down()
    {
        if ($this->db->fieldExists('supplier_id', 'finances')) {
            $this->forge->dropColumn('finances', 'supplier_id');
        }
    }
}
