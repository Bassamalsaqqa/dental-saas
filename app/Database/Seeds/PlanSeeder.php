<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class PlanSeeder extends Seeder
{
    public function run()
    {
        $data = [
            [
                'name'          => 'Basic Plan',
                'status'        => 'active',
                'features_json' => json_encode([
                    'exports' => ['enabled' => true],
                    'notifications' => ['email' => ['enabled' => true]]
                ]),
                'limits_json'   => json_encode([
                    'exports' => 10,
                    'patients_active_max' => 50,
                    'notifications_email' => 100
                ]),
                'created_at'    => date('Y-m-d H:i:s'),
                'updated_at'    => date('Y-m-d H:i:s'),
            ],
            [
                'name'          => 'Pro Plan',
                'status'        => 'active',
                'features_json' => json_encode([
                    'exports' => ['enabled' => true],
                    'notifications' => ['email' => ['enabled' => true], 'sms' => ['enabled' => true]]
                ]),
                'limits_json'   => json_encode([
                    'exports' => -1, // Unlimited
                    'patients_active_max' => 500,
                    'notifications_email' => 1000
                ]),
                'created_at'    => date('Y-m-d H:i:s'),
                'updated_at'    => date('Y-m-d H:i:s'),
            ]
        ];

        // Using simple query to check existence to avoid duplicates on re-seed
        $db = \Config\Database::connect();
        foreach ($data as $plan) {
            $exists = $db->table('plans')->where('name', $plan['name'])->countAllResults();
            if ($exists === 0) {
                $db->table('plans')->insert($plan);
            }
        }
    }
}
