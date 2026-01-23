<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddClinicUuidAndDomain extends Migration
{
    public function up()
    {
        $this->forge->addColumn('clinics', [
            'uuid' => [
                'type' => 'CHAR',
                'constraint' => 36,
                'null' => true,
                'after' => 'slug',
            ],
            'domain' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => true,
                'after' => 'uuid',
            ],
        ]);
        $this->db->query('CREATE UNIQUE INDEX clinics_uuid_unique ON clinics (uuid)');
        $this->db->query('CREATE UNIQUE INDEX clinics_domain_unique ON clinics (domain)');

        $rows = $this->db->table('clinics')->select('id, uuid')->get()->getResultArray();
        foreach ($rows as $row) {
            if (!empty($row['uuid'])) {
                continue;
            }
            $this->db->table('clinics')
                ->where('id', $row['id'])
                ->update(['uuid' => $this->generateUuid()]);
        }

        $this->forge->modifyColumn('clinics', [
            'uuid' => [
                'type' => 'CHAR',
                'constraint' => 36,
                'null' => false,
            ],
        ]);
    }

    public function down()
    {
        $this->db->query('DROP INDEX clinics_uuid_unique ON clinics');
        $this->db->query('DROP INDEX clinics_domain_unique ON clinics');
        $this->forge->dropColumn('clinics', ['uuid', 'domain']);
    }

    private function generateUuid(): string
    {
        $data = random_bytes(16);
        $data[6] = chr((ord($data[6]) & 0x0f) | 0x40);
        $data[8] = chr((ord($data[8]) & 0x3f) | 0x80);
        $hex = bin2hex($data);

        return sprintf(
            '%s-%s-%s-%s-%s',
            substr($hex, 0, 8),
            substr($hex, 8, 4),
            substr($hex, 12, 4),
            substr($hex, 16, 4),
            substr($hex, 20, 12)
        );
    }
}
