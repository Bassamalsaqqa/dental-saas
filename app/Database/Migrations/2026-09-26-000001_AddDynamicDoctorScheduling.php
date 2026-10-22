<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/** Expansion only: existing appointments and historical labels are never rewritten. */
class AddDynamicDoctorScheduling extends Migration
{
    private const INDEX = 'idx_appointments_provider_sched';
    private const INDEX_COLUMNS = ['clinic_id','provider_user_id','appointment_date','status'];

    private function table(string $name): string
    {
        return $this->db->escapeIdentifiers($this->db->prefixTable($name));
    }

    private function columns(string $table): array
    {
        $query = $this->db->query('SELECT COLUMN_NAME,COLUMN_TYPE,IS_NULLABLE,COLUMN_DEFAULT,COLUMN_KEY,EXTRA FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME=?', [$this->db->prefixTable($table)]);
        if ($query === false) {
            throw new \RuntimeException('Scheduling column inspection failed.');
        }
        return array_column($query->getResultArray(), null, 'COLUMN_NAME');
    }

    private function type(array $column): string
    {
        return preg_replace('/\b(bigint|int|tinyint)\([0-9]+\)/i', '$1', strtolower((string)($column['COLUMN_TYPE'] ?? '')));
    }

    private function requireEngine(string $table): void
    {
        $row = $this->db->query('SELECT ENGINE FROM information_schema.TABLES WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME=?', [$this->db->prefixTable($table)]);
        if ($row === false || strtoupper((string)($row->getRowArray()['ENGINE'] ?? '')) !== 'INNODB') {
            throw new \RuntimeException('Scheduling requires InnoDB table: ' . $table);
        }
    }

    private function indexes(string $table): array
    {
        $query = $this->db->query('SHOW INDEX FROM ' . $this->table($table));
        if ($query === false) {
            throw new \RuntimeException('Scheduling index inspection failed.');
        }
        $indexes = [];
        foreach ($query->getResultArray() as $row) {
            $indexes[$row['Key_name']][(int)$row['Seq_in_index']] = $row;
        }
        foreach ($indexes as &$parts) {
            ksort($parts);
            $parts = array_values($parts);
        } unset($parts);
        return $indexes;
    }

    private function exactIndex(array $parts, array $columns, bool $unique): bool
    {
        if (array_column($parts, 'Column_name') !== $columns) {
            return false;
        }
        foreach ($parts as $part) {
            if ((int)$part['Non_unique'] !== ($unique ? 0 : 1) || $part['Sub_part'] !== null || strtoupper($part['Index_type']) !== 'BTREE' || ($part['Collation'] ?? 'A') !== 'A' || strtoupper((string)($part['Ignored'] ?? 'NO')) !== 'NO' || strtoupper((string)($part['Visible'] ?? 'YES')) !== 'YES') {
                return false;
            }
        }
        return true;
    }

    private function verifyProfiles(): void
    {
        $this->requireEngine('clinic_provider_profiles');
        $columns = $this->columns('clinic_provider_profiles');
        $expected = ['id' => 'int unsigned','clinic_id' => 'int unsigned','user_id' => 'int unsigned','is_bookable' => 'tinyint','is_clinician' => 'tinyint','weekly_hours_json' => 'text','exceptions_json' => 'text','created_at' => 'datetime','updated_at' => 'datetime'];
        if (count($columns) !== count($expected)) {
            throw new \RuntimeException('Provider profile column set differs; explicit inspection required.');
        }
        foreach ($expected as $name => $type) {
            $column = $columns[$name] ?? [];
            $nullable = in_array($name, ['weekly_hours_json','exceptions_json','created_at','updated_at'], true);
            if ($this->type($column) !== $type || ($column['IS_NULLABLE'] ?? '') !== ($nullable ? 'YES' : 'NO')) {
                throw new \RuntimeException('Provider profile field differs: ' . $name);
            }
            $default = $column['COLUMN_DEFAULT'] ?? null;
            $normalized = $default === null || strtoupper((string)$default) === 'NULL' ? null : trim((string)$default, "'");
            $wanted = ['is_bookable' => '1','is_clinician' => '0'][$name] ?? null;
            if ($normalized !== $wanted) {
                throw new \RuntimeException('Provider profile default differs: ' . $name);
            }
            if ($name === 'id') {
                if (!str_contains($column['EXTRA'] ?? '', 'auto_increment')) {
                    throw new \RuntimeException('Provider profile identity differs.');
                }
            } elseif (($column['EXTRA'] ?? '') !== '') {
                throw new \RuntimeException('Provider profile has unexpected generated behavior.');
            }
        }
        $indexes = $this->indexes('clinic_provider_profiles');
        if (!$this->exactIndex($indexes['PRIMARY'] ?? [], ['id'], true)) {
            throw new \RuntimeException('Provider profile primary key differs.');
        }
        $tenantUnique = false;
        foreach ($indexes as $name => $parts) {
            if ((int)$parts[0]['Non_unique'] !== 0 || $name === 'PRIMARY') {
                continue;
            }
            if (!$this->exactIndex($parts, ['clinic_id','user_id'], true)) {
                throw new \RuntimeException('Provider profile has incompatible uniqueness.');
            }
            $tenantUnique = true;
        }
        if (!$tenantUnique) {
            throw new \RuntimeException('Provider profile clinic/user uniqueness is missing.');
        }
        $foreign = $this->db->query('SELECT CONSTRAINT_NAME FROM information_schema.TABLE_CONSTRAINTS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME=? AND CONSTRAINT_TYPE=?', [$this->db->prefixTable('clinic_provider_profiles'),'FOREIGN KEY']);
        if ($foreign === false || $foreign->getResultArray() !== []) {
            throw new \RuntimeException('Unexpected provider profile foreign keys require explicit review.');
        }
    }

    /** Inspect every preexisting object before MariaDB can implicitly commit any DDL. */
    private function preflight(): array
    {
        $this->db->resetDataCache();
        foreach (['clinics','users','appointments'] as $parent) {
            if (!$this->db->tableExists($parent)) {
                throw new \RuntimeException('Scheduling parent table missing: ' . $parent);
            }
            $this->requireEngine($parent);
            $columns = $this->columns($parent);
            $idType = $this->type($columns['id'] ?? []);
            if (!in_array($idType, $parent === 'appointments' ? ['int','int unsigned'] : ['int unsigned'], true)
                || ($columns['id']['IS_NULLABLE'] ?? '') !== 'NO'
                || !$this->exactIndex($this->indexes($parent)['PRIMARY'] ?? [], ['id'], true)) {
                throw new \RuntimeException('Scheduling parent ID is incompatible: ' . $parent);
            }
        }
        $appointments = $this->columns('appointments');
        foreach (['clinic_id','patient_id','appointment_date','status'] as $field) {
            if (!isset($appointments[$field])) {
                throw new \RuntimeException('Scheduling appointment field missing: ' . $field);
            }
        }
        if ($this->type($appointments['clinic_id']) !== 'int unsigned' || $appointments['clinic_id']['IS_NULLABLE'] !== 'NO') {
            throw new \RuntimeException('Scheduling clinic reference is incompatible.');
        }
        if (!in_array($this->type($appointments['patient_id']), ['int','int unsigned'], true)
            || $appointments['patient_id']['IS_NULLABLE'] !== 'NO'
            || $this->type($appointments['appointment_date']) !== 'date' || $appointments['appointment_date']['IS_NULLABLE'] !== 'NO') {
            throw new \RuntimeException('Scheduling appointment identity/date fields are incompatible.');
        }
        $provider = isset($appointments['provider_user_id']);
        if ($provider && ($this->type($appointments['provider_user_id']) !== 'int unsigned'
            || $appointments['provider_user_id']['IS_NULLABLE'] !== 'YES'
            || !in_array($appointments['provider_user_id']['COLUMN_DEFAULT'], [null,'NULL'], true)
            || ($appointments['provider_user_id']['EXTRA'] ?? '') !== '')) {
            throw new \RuntimeException('Existing provider reference differs; no automatic rewrite.');
        }
        $indexes = $this->indexes('appointments');
        $indexed = isset($indexes[self::INDEX]);
        if ($indexed && !$this->exactIndex($indexes[self::INDEX], self::INDEX_COLUMNS, false)) {
            throw new \RuntimeException('Scheduling index name collision; no automatic replacement.');
        }
        $profiles = $this->db->tableExists('clinic_provider_profiles');
        if ($profiles) {
            $this->verifyProfiles();
        }
        return ['provider' => $provider,'indexed' => $indexed,'profiles' => $profiles];
    }

    public function up()
    {
        $state = $this->preflight();
        if (!$state['provider'] && !$this->forge->addColumn('appointments', ['provider_user_id' => ['type' => 'INT','unsigned' => true,'null' => true,'after' => 'patient_id']])) {
            throw new \RuntimeException('Provider column expansion failed.');
        }
        if (!$state['indexed'] && !$this->db->query('CREATE INDEX ' . self::INDEX . ' ON ' . $this->table('appointments') . ' (clinic_id,provider_user_id,appointment_date,status)')) {
            throw new \RuntimeException('Provider scheduling index expansion failed.');
        }
        if (!$state['profiles']) {
            $this->forge->addField([
                'id' => ['type' => 'INT','unsigned' => true,'auto_increment' => true],
                'clinic_id' => ['type' => 'INT','unsigned' => true], 'user_id' => ['type' => 'INT','unsigned' => true],
                'is_bookable' => ['type' => 'TINYINT','constraint' => 1,'default' => 1], 'is_clinician' => ['type' => 'TINYINT','constraint' => 1,'default' => 0],
                'weekly_hours_json' => ['type' => 'TEXT','null' => true], 'exceptions_json' => ['type' => 'TEXT','null' => true],
                'created_at' => ['type' => 'DATETIME','null' => true], 'updated_at' => ['type' => 'DATETIME','null' => true],
            ]);
            $this->forge->addKey('id', true);
            $this->forge->addUniqueKey(['clinic_id','user_id'], 'uk_clinic_provider_profiles');
            if (!$this->forge->createTable('clinic_provider_profiles', false, ['ENGINE' => 'InnoDB'])) {
                throw new \RuntimeException('Provider profile expansion failed.');
            }
        }
        $verified = $this->preflight();
        if (in_array(false, $verified, true)) {
            throw new \RuntimeException('Scheduling schema verification failed.');
        }
    }

    public function down()
    {
        $state = $this->preflight();
        if ($state['provider'] && $this->db->table('appointments')->where('provider_user_id IS NOT NULL', null, false)->countAllResults() > 0) {
            throw new \RuntimeException('Refusing rollback: provider-linked appointments exist.');
        }
        if ($state['profiles'] && $this->db->table('clinic_provider_profiles')->countAllResults() > 0) {
            throw new \RuntimeException('Refusing rollback: provider profiles exist.');
        }
        // Unknown provider FKs must be reviewed before any rollback DDL.
        $foreign = $this->db->query('SELECT CONSTRAINT_NAME FROM information_schema.KEY_COLUMN_USAGE WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME=? AND COLUMN_NAME=? AND REFERENCED_TABLE_NAME IS NOT NULL', [$this->db->prefixTable('appointments'),'provider_user_id']);
        if ($foreign === false || $foreign->getResultArray() !== []) {
            throw new \RuntimeException('Provider foreign key requires explicit rollback review.');
        }
        if ($state['profiles'] && !$this->forge->dropTable('clinic_provider_profiles', false)) {
            throw new \RuntimeException('Provider profile rollback failed.');
        }
        if ($state['indexed'] && !$this->db->query('DROP INDEX ' . self::INDEX . ' ON ' . $this->table('appointments'))) {
            throw new \RuntimeException('Scheduling index rollback failed.');
        }
        if ($state['provider'] && !$this->forge->dropColumn('appointments', 'provider_user_id')) {
            throw new \RuntimeException('Provider column rollback failed.');
        }
        $this->db->resetDataCache();
    }
}
