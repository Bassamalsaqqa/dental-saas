<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreatePatientOdontogramSnapshotsTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'auto_increment' => true,
            ],
            'clinic_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
            ],
            'patient_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
            ],
            'source_type' => [
                'type' => 'VARCHAR',
                'constraint' => 32,
            ],
            'source_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'null' => true,
            ],
            'label' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => true,
            ],
            'snapshot_json' => [
                'type' => 'LONGTEXT',
                'null' => true,
            ],
            'snapshot_svg' => [
                'type' => 'LONGTEXT',
                'null' => true,
            ],
            'created_by' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'null' => true,
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'updated_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addKey(['clinic_id', 'patient_id']);
        $this->forge->addKey(['source_type', 'source_id']);
        $this->forge->createTable('patient_odontogram_snapshots');
    }

    public function down()
    {
        $this->forge->dropTable('patient_odontogram_snapshots');
    }
}
