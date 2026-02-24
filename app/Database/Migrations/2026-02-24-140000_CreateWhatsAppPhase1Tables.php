<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateWhatsAppPhase1Tables extends Migration
{
    public function up()
    {
        $this->createNotificationAttemptsTable();
        $this->createWhatsAppDeliveryEventsTable();
        $this->createPatientWhatsAppTable();
        $this->createWhatsAppOptoutsTable();
        $this->createWhatsAppTenantPoliciesTable();
        $this->createPlatformSecretsTable();
    }

    public function down()
    {
        $tables = [
            'platform_secrets',
            'whatsapp_tenant_policies',
            'whatsapp_optouts',
            'patient_whatsapp',
            'whatsapp_delivery_events',
            'notification_attempts',
        ];

        foreach ($tables as $table) {
            if ($this->db->tableExists($table)) {
                $this->forge->dropTable($table, true);
            }
        }
    }

    private function createNotificationAttemptsTable(): void
    {
        if ($this->db->tableExists('notification_attempts')) {
            return;
        }

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
            'notification_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
            ],
            'channel_type' => [
                'type' => 'ENUM',
                'constraint' => ['email', 'sms', 'whatsapp'],
            ],
            'status' => [
                'type' => 'ENUM',
                'constraint' => ['queued', 'sending', 'sent', 'delivered', 'read', 'failed', 'rate_limited', 'failed_permanent'],
                'default' => 'queued',
            ],
            'provider_message_id' => [
                'type' => 'VARCHAR',
                'constraint' => 191,
                'null' => true,
            ],
            'error_code' => [
                'type' => 'VARCHAR',
                'constraint' => 64,
                'null' => true,
            ],
            'error_message' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'idempotency_key' => [
                'type' => 'VARCHAR',
                'constraint' => 191,
            ],
            'retry_count' => [
                'type' => 'INT',
                'constraint' => 10,
                'unsigned' => true,
                'default' => 0,
            ],
            'max_retries' => [
                'type' => 'INT',
                'constraint' => 10,
                'unsigned' => true,
                'default' => 3,
            ],
            'next_retry_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addKey(['clinic_id', 'status', 'next_retry_at']);
        $this->forge->addKey(['clinic_id', 'notification_id']);
        $this->forge->addKey(['clinic_id', 'provider_message_id']);
        $this->forge->addKey('idempotency_key', false, true);
        $this->forge->addForeignKey('clinic_id', 'clinics', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('notification_id', 'notifications', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('notification_attempts');
    }

    private function createWhatsAppDeliveryEventsTable(): void
    {
        if ($this->db->tableExists('whatsapp_delivery_events')) {
            return;
        }

        $this->forge->addField([
            'id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'auto_increment' => true,
            ],
            'provider_event_id' => [
                'type' => 'VARCHAR',
                'constraint' => 191,
            ],
            'event_type' => [
                'type' => 'VARCHAR',
                'constraint' => 64,
            ],
            'payload_json' => [
                'type' => 'JSON',
            ],
            'processed_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addKey(['provider_event_id', 'event_type'], false, true);
        $this->forge->addKey('processed_at');
        $this->forge->createTable('whatsapp_delivery_events');
    }

    private function createPatientWhatsAppTable(): void
    {
        if ($this->db->tableExists('patient_whatsapp')) {
            return;
        }

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
            ],
            'wa_number' => [
                'type' => 'VARCHAR',
                'constraint' => 32,
                'null' => true,
            ],
            'bsuid' => [
                'type' => 'VARCHAR',
                'constraint' => 128,
                'null' => true,
            ],
            'resolution_status' => [
                'type' => 'ENUM',
                'constraint' => ['pending', 'verified', 'ambiguous', 'failed'],
                'default' => 'pending',
            ],
            'verified_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'last_error_code' => [
                'type' => 'VARCHAR',
                'constraint' => 64,
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
        $this->forge->addKey(['clinic_id', 'patient_id'], false, true);
        $this->forge->addKey(['clinic_id', 'wa_number']);
        $this->forge->addKey(['clinic_id', 'bsuid']);
        $this->forge->addForeignKey('clinic_id', 'clinics', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('patient_id', 'patients', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('patient_whatsapp');
    }

    private function createWhatsAppOptoutsTable(): void
    {
        if ($this->db->tableExists('whatsapp_optouts')) {
            return;
        }

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
                'null' => true,
            ],
            'wa_number' => [
                'type' => 'VARCHAR',
                'constraint' => 32,
                'null' => true,
            ],
            'bsuid' => [
                'type' => 'VARCHAR',
                'constraint' => 128,
                'null' => true,
            ],
            'source' => [
                'type' => 'ENUM',
                'constraint' => ['webhook', 'manual'],
                'default' => 'webhook',
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addKey(['clinic_id', 'wa_number']);
        $this->forge->addKey(['clinic_id', 'patient_id']);
        $this->forge->addKey(['clinic_id', 'bsuid'], false, false, 'idx_bsuid');
        $this->forge->addForeignKey('clinic_id', 'clinics', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('patient_id', 'patients', 'id', 'CASCADE', 'SET NULL');
        $this->forge->createTable('whatsapp_optouts');
    }

    private function createWhatsAppTenantPoliciesTable(): void
    {
        if ($this->db->tableExists('whatsapp_tenant_policies')) {
            return;
        }

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
            'daily_limit' => [
                'type' => 'INT',
                'constraint' => 10,
                'unsigned' => true,
                'default' => 50,
            ],
            'monthly_limit' => [
                'type' => 'INT',
                'constraint' => 10,
                'unsigned' => true,
                'default' => 1000,
            ],
            'per_patient_daily_limit' => [
                'type' => 'INT',
                'constraint' => 10,
                'unsigned' => true,
                'default' => 3,
            ],
            'per_hour_limit' => [
                'type' => 'INT',
                'constraint' => 10,
                'unsigned' => true,
                'default' => 20,
            ],
            'suspended' => [
                'type' => 'TINYINT',
                'constraint' => 1,
                'default' => 0,
            ],
            'suspended_reason' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => true,
            ],
            'trigger_toggles_json' => [
                'type' => 'JSON',
                'null' => true,
            ],
            'reminder_lead_time_minutes' => [
                'type' => 'INT',
                'constraint' => 10,
                'unsigned' => true,
                'default' => 1440,
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
        $this->forge->addKey('clinic_id', false, true);
        $this->forge->addForeignKey('clinic_id', 'clinics', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('whatsapp_tenant_policies');
    }

    private function createPlatformSecretsTable(): void
    {
        if ($this->db->tableExists('platform_secrets')) {
            return;
        }

        $this->forge->addField([
            'id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'auto_increment' => true,
            ],
            'secret_key' => [
                'type' => 'VARCHAR',
                'constraint' => 100,
            ],
            'secret_encrypted' => [
                'type' => 'TEXT',
            ],
            'rotated_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'rotated_by' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'null' => true,
            ],
            'rotation_note' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
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
        $this->forge->addKey('secret_key', false, true);
        $this->forge->createTable('platform_secrets');
    }
}
