<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class UpdateControlPlaneLocaleCurrencyDefaults extends Migration
{
    public function up()
    {
        $db = $this->db;
        if (!$db->tableExists('settings')) {
            return;
        }

        $now = date('Y-m-d H:i:s');
        $defaults = [
            'default_timezone' => ['value' => 'Asia/Jerusalem', 'type' => 'string'],
            'default_date_format' => ['value' => 'd/m/Y', 'type' => 'string'],
            'default_time_format' => ['value' => '12', 'type' => 'string'],
            'default_currency' => ['value' => 'ILS', 'type' => 'string'],
            'default_currency_position' => ['value' => 'after', 'type' => 'string'],
        ];

        foreach ($defaults as $key => $meta) {
            $exists = $db->table('settings')
                ->where('clinic_id', null)
                ->where('setting_key', $key)
                ->countAllResults();

            if ($exists) {
                $db->table('settings')
                    ->where('clinic_id', null)
                    ->where('setting_key', $key)
                    ->update([
                        'setting_value' => $meta['value'],
                        'setting_type' => $meta['type'],
                        'updated_at' => $now,
                    ]);
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
        $db = $this->db;
        if (!$db->tableExists('settings')) {
            return;
        }

        $now = date('Y-m-d H:i:s');
        $revert = [
            'default_timezone' => ['value' => 'America/New_York', 'type' => 'string'],
            'default_date_format' => ['value' => 'Y-m-d', 'type' => 'string'],
            'default_time_format' => ['value' => '12', 'type' => 'string'],
            'default_currency' => ['value' => 'USD', 'type' => 'string'],
            'default_currency_position' => ['value' => 'before', 'type' => 'string'],
        ];

        foreach ($revert as $key => $meta) {
            $db->table('settings')
                ->where('clinic_id', null)
                ->where('setting_key', $key)
                ->update([
                    'setting_value' => $meta['value'],
                    'setting_type' => $meta['type'],
                    'updated_at' => $now,
                ]);
        }
    }
}

