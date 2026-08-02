<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateExaminationClinicalNoteGovernanceTables extends Migration
{
    public function up()
    {
        if (!$this->db->tableExists('examination_note_signatures')) {
            $this->forge->addField([
                'id' => ['type' => 'INT', 'constraint' => 10, 'unsigned' => true, 'auto_increment' => true],
                'clinic_id' => ['type' => 'INT', 'constraint' => 10, 'unsigned' => true],
                'examination_id' => ['type' => 'INT', 'constraint' => 11],
                'patient_id' => ['type' => 'INT', 'constraint' => 11],
                'signed_by_user_id' => ['type' => 'INT', 'constraint' => 11],
                'signed_by_name' => ['type' => 'VARCHAR', 'constraint' => 180],
                'signed_at' => ['type' => 'DATETIME'],
                'attestation_version' => ['type' => 'VARCHAR', 'constraint' => 30, 'default' => 'v1'],
                'snapshot_json' => ['type' => 'LONGTEXT'],
                'content_hash' => ['type' => 'CHAR', 'constraint' => 64],
                'created_at' => ['type' => 'DATETIME', 'null' => true],
            ]);
            $this->forge->addKey('id', true);
            $this->forge->addKey(['clinic_id', 'examination_id'], false, true, 'uq_exam_note_signature');
            $this->forge->addKey(['clinic_id', 'patient_id', 'signed_at'], false, false, 'idx_exam_note_patient');
            $this->forge->createTable('examination_note_signatures');
        }

        if (!$this->db->tableExists('examination_note_addenda')) {
            $this->forge->addField([
                'id' => ['type' => 'INT', 'constraint' => 10, 'unsigned' => true, 'auto_increment' => true],
                'clinic_id' => ['type' => 'INT', 'constraint' => 10, 'unsigned' => true],
                'examination_id' => ['type' => 'INT', 'constraint' => 11],
                'signature_id' => ['type' => 'INT', 'constraint' => 10, 'unsigned' => true],
                'added_by_user_id' => ['type' => 'INT', 'constraint' => 11],
                'added_by_name' => ['type' => 'VARCHAR', 'constraint' => 180],
                'reason' => ['type' => 'VARCHAR', 'constraint' => 255],
                'body' => ['type' => 'TEXT'],
                'previous_hash' => ['type' => 'CHAR', 'constraint' => 64],
                'content_hash' => ['type' => 'CHAR', 'constraint' => 64],
                'created_at' => ['type' => 'DATETIME'],
            ]);
            $this->forge->addKey('id', true);
            $this->forge->addKey(['clinic_id', 'examination_id', 'created_at'], false, false, 'idx_exam_note_addenda');
            $this->forge->addKey(['clinic_id', 'signature_id'], false, false, 'idx_exam_note_signature_addenda');
            $this->forge->createTable('examination_note_addenda');
        }
    }

    public function down()
    {
        $this->forge->dropTable('examination_note_addenda', true);
        $this->forge->dropTable('examination_note_signatures', true);
    }
}
