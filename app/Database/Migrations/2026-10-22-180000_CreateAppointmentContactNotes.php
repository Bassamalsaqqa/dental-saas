<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateAppointmentContactNotes extends Migration
{
    public function up()
    {
        $parentUnsigned = [];
        foreach (['clinics','patients','appointments','users'] as $parent) {
            if (!$this->db->tableExists($parent)) {
                throw new \RuntimeException('Contact history parent schema missing.');
            }
            $row = $this->db->query('SELECT ENGINE FROM information_schema.TABLES WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME=?', [$this->db->prefixTable($parent)])->getRowArray();
            if (strtoupper((string)($row['ENGINE'] ?? '')) !== 'INNODB') {
                throw new \RuntimeException('Contact history requires InnoDB parents.');
            }
            $id = $this->db->query('SELECT COLUMN_TYPE FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME=? AND COLUMN_NAME=?', [$this->db->prefixTable($parent),'id'])->getRowArray();
            if (!preg_match('/^int(?:\([0-9]+\))?( unsigned)?$/i', (string)($id['COLUMN_TYPE'] ?? ''))) {
                throw new \RuntimeException('Contact parent ID type must be INT.');
            }
            $parentUnsigned[$parent] = str_contains(strtolower($id['COLUMN_TYPE']), 'unsigned');
        }
        if ($this->db->tableExists('appointment_contact_notes')) {
            $this->verifyExisting($parentUnsigned);
            return;
        }
        $this->forge->addField([
            'id' => ['type' => 'BIGINT','unsigned' => true,'auto_increment' => true],
            'clinic_id' => ['type' => 'INT','unsigned' => $parentUnsigned['clinics']],
            'patient_id' => ['type' => 'INT','unsigned' => $parentUnsigned['patients']],
            'appointment_id' => ['type' => 'INT','unsigned' => $parentUnsigned['appointments']],
            'actor_id' => ['type' => 'INT','unsigned' => $parentUnsigned['users']],
            'request_key' => ['type' => 'VARBINARY','constraint' => 64],
            'request_hash' => ['type' => 'CHAR','constraint' => 64],
            'note' => ['type' => 'TEXT'],
            'created_at' => ['type' => 'DATETIME'],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey(['clinic_id','request_key'], 'uq_contact_request');
        $this->forge->addKey(['clinic_id','appointment_id','id']);
        foreach (['clinic_id' => 'clinics','patient_id' => 'patients','appointment_id' => 'appointments','actor_id' => 'users'] as $column => $parent) {
            $this->forge->addForeignKey($column, $parent, 'id', 'RESTRICT', 'RESTRICT', 'fk_contact_'.substr(hash('sha256', $this->db->prefixTable('appointment_contact_notes')), 0, 10).'_'.$column);
        }
        if (!$this->forge->createTable('appointment_contact_notes', false, ['ENGINE' => 'InnoDB'])) {
            throw new \RuntimeException('Contact schema creation failed.');
        }
        $this->db->resetDataCache();
        $this->verifyExisting($parentUnsigned);
    }
    private function verifyExisting(array $parentUnsigned): void
    {
        $table = $this->db->prefixTable('appointment_contact_notes');
        $rows = $this->db->query('SELECT COLUMN_NAME,COLUMN_TYPE,IS_NULLABLE,COLUMN_KEY,EXTRA FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME=?', [$table])->getResultArray();
        $columns = array_column($rows, null, 'COLUMN_NAME');
        $expected = ['id' => 'bigint unsigned','request_key' => 'varbinary(64)','request_hash' => 'char(64)','note' => 'text','created_at' => 'datetime'];
        foreach (['clinic_id' => 'clinics','patient_id' => 'patients','appointment_id' => 'appointments','actor_id' => 'users'] as $column => $parent) {
            $expected[$column] = 'int'.($parentUnsigned[$parent] ? ' unsigned' : '');
        }
        if (count($columns) !== count($expected)) {
            throw new \RuntimeException('Existing contact schema has unexpected columns.');
        }
        foreach ($expected as $column => $type) {
            $actual = $columns[$column] ?? [];
            $actualType = preg_replace('/\b(bigint|int)\([0-9]+\)/i', '$1', strtolower((string)($actual['COLUMN_TYPE'] ?? '')));
            if ($actualType !== $type || ($actual['IS_NULLABLE'] ?? '') !== 'NO') {
                throw new \RuntimeException('Existing contact schema type/nullability mismatch.');
            }
        }
        if (($columns['id']['COLUMN_KEY'] ?? '') !== 'PRI' || !str_contains($columns['id']['EXTRA'] ?? '', 'auto_increment')) {
            throw new \RuntimeException('Existing contact primary identity mismatch.');
        }
        $engine = $this->db->query('SELECT ENGINE FROM information_schema.TABLES WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME=?', [$table])->getRowArray();
        if (strtoupper($engine['ENGINE'] ?? '') !== 'INNODB') {
            throw new \RuntimeException('Existing contact table is not transactional.');
        }
        $indexes = $this->db->query('SHOW INDEX FROM '.$this->db->escapeIdentifiers($table))->getResultArray();
        $unique = [];
        foreach ($indexes as $index) {
            if ((int)$index['Non_unique'] === 0 && $index['Sub_part'] === null) {
                $unique[$index['Key_name']][(int)$index['Seq_in_index']] = $index['Column_name'];
            }
        }
        $match = false;
        foreach ($unique as $parts) {
            ksort($parts);
            if (array_values($parts) === ['clinic_id','request_key']) {
                $match = true;
            }
        }
        if (!$match) {
            throw new \RuntimeException('Existing contact replay uniqueness mismatch.');
        }
        $fks = $this->db->query('SELECT k.COLUMN_NAME,k.REFERENCED_TABLE_SCHEMA,k.REFERENCED_TABLE_NAME,k.REFERENCED_COLUMN_NAME,r.DELETE_RULE,r.UPDATE_RULE FROM information_schema.KEY_COLUMN_USAGE k JOIN information_schema.REFERENTIAL_CONSTRAINTS r ON r.CONSTRAINT_SCHEMA=k.CONSTRAINT_SCHEMA AND r.TABLE_NAME=k.TABLE_NAME AND r.CONSTRAINT_NAME=k.CONSTRAINT_NAME WHERE k.TABLE_SCHEMA=DATABASE() AND k.TABLE_NAME=? AND k.REFERENCED_TABLE_NAME IS NOT NULL', [$table])->getResultArray();
        if (count($fks) !== 4) {
            throw new \RuntimeException('Existing contact FK count mismatch.');
        }
        $parents = ['clinic_id' => 'clinics','patient_id' => 'patients','appointment_id' => 'appointments','actor_id' => 'users'];
        $schema = $this->db->query('SELECT DATABASE() name')->getRowArray()['name'];
        $seen = [];
        foreach ($fks as $fk) {
            if (isset($seen[$fk['COLUMN_NAME']]) || $fk['REFERENCED_TABLE_SCHEMA'] !== $schema || !isset($parents[$fk['COLUMN_NAME']]) || $fk['REFERENCED_TABLE_NAME'] !== $this->db->prefixTable($parents[$fk['COLUMN_NAME']]) || $fk['REFERENCED_COLUMN_NAME'] !== 'id' || $fk['DELETE_RULE'] !== 'RESTRICT' || $fk['UPDATE_RULE'] !== 'RESTRICT') {
                throw new \RuntimeException('Existing contact preservation FK mismatch.');
            }
            $seen[$fk['COLUMN_NAME']] = true;
        }
    }

    public function down()
    {
        // Compatible pre-existing tables may have been adopted without creating them.
        throw new \RuntimeException('Contact schema is retained evidence; automatic rollback refused because object ownership is unproven.');
    }
}
