<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class ClinicSeeder extends Seeder
{
    public function run()
    {
        $db = \Config\Database::connect();
        
        // 1. Ensure Roles Exist
        $roles = [
            'clinic_admin' => 'Clinic Admin',
            'doctor' => 'Doctor',
            'staff' => 'Staff'
        ];
        
        $roleIds = [];
        foreach ($roles as $slug => $name) {
            $existing = $db->table('roles')->where('slug', $slug)->get()->getRow();
            if (!$existing) {
                $db->table('roles')->insert([
                    'slug' => $slug,
                    'name' => $name,
                    'description' => $name . ' Role',
                    'is_active' => 1,
                    'is_system' => 0
                ]);
                $roleIds[$slug] = $db->insertID();
            } else {
                $roleIds[$slug] = $existing->id;
            }
        }

        // 2. Create Clinics
        $clinics = [
            [
                'name' => 'Dental One',
                'slug' => 'dental-one',
                'status' => 'active'
            ],
            [
                'name' => 'Dental Two',
                'slug' => 'dental-two',
                'status' => 'active'
            ]
        ];

        $clinicIds = [];
        foreach ($clinics as $data) {
            $existing = $db->table('clinics')->where('slug', $data['slug'])->get()->getRow();
            if (!$existing) {
                $db->table('clinics')->insert($data);
                $clinicIds[$data['slug']] = $db->insertID();
            } else {
                $clinicIds[$data['slug']] = $existing->id;
            }
        }

        // 3. Assign Memberships
        
        // Ensure User 2 exists for testing "User B in one clinic"
        $user2 = $db->table('users')->where('email', 'staff@dental.com')->get()->getRow();
        if (!$user2) {
             $db->table('users')->insert([
                 'username' => 'staff',
                 'email' => 'staff@dental.com',
                 'password' => 'hashed_placeholder',
                 'active' => 1,
                 'created_on' => time(),
             ]);
             $user2Id = $db->insertID();
        } else {
            $user2Id = $user2->id;
        }

        $memberships = [
            // User 1 (Admin) -> Clinic One (Admin)
            [
                'clinic_id' => $clinicIds['dental-one'],
                'user_id' => 1,
                'role_id' => $roleIds['clinic_admin'],
                'status' => 'active'
            ],
            // User 1 (Admin) -> Clinic Two (Doctor)
            [
                'clinic_id' => $clinicIds['dental-two'],
                'user_id' => 1,
                'role_id' => $roleIds['doctor'],
                'status' => 'active'
            ],
             // User 2 (Staff) -> Clinic One (Staff)
            [
                'clinic_id' => $clinicIds['dental-one'],
                'user_id' => $user2Id,
                'role_id' => $roleIds['staff'],
                'status' => 'active'
            ],
        ];

        foreach ($memberships as $m) {
            // Check existence (composite unique)
            $exists = $db->table('clinic_users')
                ->where('user_id', $m['user_id'])
                ->where('clinic_id', $m['clinic_id'])
                ->countAllResults();
            
            if (!$exists) {
                $db->table('clinic_users')->insert($m);
            }
        }
    }
}
