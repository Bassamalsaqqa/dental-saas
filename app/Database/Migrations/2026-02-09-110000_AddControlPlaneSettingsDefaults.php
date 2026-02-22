<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddControlPlaneSettingsDefaults extends Migration
{
    public function up()
    {
        $db = \Config\Database::connect();

        if (!$db->tableExists('settings')) {
            return;
        }

        $now = date('Y-m-d H:i:s');
        $defaults = [
            'platform_name' => ['value' => 'DentaCare Cloud', 'type' => 'string'],
            'platform_support_email' => ['value' => '', 'type' => 'string'],
            'platform_support_phone' => ['value' => '', 'type' => 'string'],
            'platform_support_whatsapp_primary' => ['value' => '', 'type' => 'string'],
            'platform_support_whatsapp_secondary' => ['value' => '', 'type' => 'string'],
            'platform_support_website' => ['value' => '', 'type' => 'string'],
            'retention_mode' => ['value' => 'latest', 'type' => 'string'],
            'retention_last_n' => ['value' => '5', 'type' => 'number'],
            'retention_days' => ['value' => '30', 'type' => 'number'],
            'control_plane_audit_retention_days' => ['value' => '365', 'type' => 'number'],
            'default_timezone' => ['value' => 'Asia/Jerusalem', 'type' => 'string'],
            'default_date_format' => ['value' => 'd/m/Y', 'type' => 'string'],
            'default_time_format' => ['value' => '12', 'type' => 'string'],
            'default_currency' => ['value' => 'ILS', 'type' => 'string'],
            'default_currency_position' => ['value' => 'after', 'type' => 'string'],
            'default_appointment_duration' => ['value' => '30', 'type' => 'number'],
            'default_working_hours_start' => ['value' => '09:00', 'type' => 'string'],
            'default_working_hours_end' => ['value' => '17:00', 'type' => 'string'],
            'default_working_days' => ['value' => '["monday","tuesday","wednesday","thursday","friday"]', 'type' => 'json'],
        ];

        foreach ($defaults as $key => $meta) {
            $exists = $db->table('settings')
                ->where('clinic_id', null)
                ->where('setting_key', $key)
                ->countAllResults();

            if ($exists) {
                continue;
            }

            $db->table('settings')->insert([
                'clinic_id' => null,
                'setting_key' => $key,
                'setting_value' => $meta['value'],
                'setting_type' => $meta['type'],
                'description' => 'Control plane default',
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }
    }

    public function down()
    {
        $db = \Config\Database::connect();
        if (!$db->tableExists('settings')) {
            return;
        }

        $keys = [
            'platform_name',
            'platform_support_email',
            'platform_support_phone',
            'platform_support_whatsapp_primary',
            'platform_support_whatsapp_secondary',
            'platform_support_website',
            'retention_mode',
            'retention_last_n',
            'retention_days',
            'control_plane_audit_retention_days',
            'default_timezone',
            'default_date_format',
            'default_time_format',
            'default_currency',
            'default_currency_position',
            'default_appointment_duration',
            'default_working_hours_start',
            'default_working_hours_end',
            'default_working_days',
        ];

        $db->table('settings')
            ->where('clinic_id', null)
            ->whereIn('setting_key', $keys)
            ->delete();
    }
}
