<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class RemoveDeprecatedPatientContactColumns extends Migration
{
    public function up()
    {
        $columnsToDrop = [
            'address',
            'state',
            'zip_code',
            'country',
            'emergency_contact_name',
            'emergency_contact_phone',
        ];

        foreach ($columnsToDrop as $column) {
            if ($this->db->fieldExists($column, 'patients')) {
                $this->forge->dropColumn('patients', $column);
            }
        }
    }

    public function down()
    {
        $fields = [];

        if (! $this->db->fieldExists('address', 'patients')) {
            $fields['address'] = ['type' => 'TEXT', 'null' => true, 'after' => 'gender'];
        }
        if (! $this->db->fieldExists('state', 'patients')) {
            $fields['state'] = ['type' => 'VARCHAR', 'constraint' => 100, 'null' => true, 'after' => 'city'];
        }
        if (! $this->db->fieldExists('zip_code', 'patients')) {
            $fields['zip_code'] = ['type' => 'VARCHAR', 'constraint' => 20, 'null' => true, 'after' => 'state'];
        }
        if (! $this->db->fieldExists('country', 'patients')) {
            $fields['country'] = ['type' => 'VARCHAR', 'constraint' => 100, 'null' => true, 'after' => 'zip_code'];
        }
        if (! $this->db->fieldExists('emergency_contact_name', 'patients')) {
            $fields['emergency_contact_name'] = ['type' => 'VARCHAR', 'constraint' => 100, 'null' => true, 'after' => 'country'];
        }
        if (! $this->db->fieldExists('emergency_contact_phone', 'patients')) {
            $fields['emergency_contact_phone'] = ['type' => 'VARCHAR', 'constraint' => 20, 'null' => true, 'after' => 'emergency_contact_name'];
        }

        if ($fields !== []) {
            $this->forge->addColumn('patients', $fields);
        }
    }
}

