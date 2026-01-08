<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class RBACSeeder extends Seeder
{
    public function run()
    {
        // Insert initial permissions
        $permissions = [
            // Dashboard permissions
            ['name' => 'View Dashboard', 'module' => 'dashboard', 'action' => 'read', 'description' => 'Can view the main dashboard'],
            
            // Patient permissions
            ['name' => 'View Patients', 'module' => 'patients', 'action' => 'read', 'description' => 'Can view patient list and details'],
            ['name' => 'Create Patients', 'module' => 'patients', 'action' => 'create', 'description' => 'Can create new patients'],
            ['name' => 'Edit Patients', 'module' => 'patients', 'action' => 'update', 'description' => 'Can edit patient information'],
            ['name' => 'Delete Patients', 'module' => 'patients', 'action' => 'delete', 'description' => 'Can delete patients'],
            
            // Examination permissions
            ['name' => 'View Examinations', 'module' => 'examinations', 'action' => 'read', 'description' => 'Can view examination records'],
            ['name' => 'Create Examinations', 'module' => 'examinations', 'action' => 'create', 'description' => 'Can create new examinations'],
            ['name' => 'Edit Examinations', 'module' => 'examinations', 'action' => 'update', 'description' => 'Can edit examination records'],
            ['name' => 'Delete Examinations', 'module' => 'examinations', 'action' => 'delete', 'description' => 'Can delete examinations'],
            
            // Appointment permissions
            ['name' => 'View Appointments', 'module' => 'appointments', 'action' => 'read', 'description' => 'Can view appointment schedule'],
            ['name' => 'Create Appointments', 'module' => 'appointments', 'action' => 'create', 'description' => 'Can create new appointments'],
            ['name' => 'Edit Appointments', 'module' => 'appointments', 'action' => 'update', 'description' => 'Can edit appointments'],
            ['name' => 'Delete Appointments', 'module' => 'appointments', 'action' => 'delete', 'description' => 'Can delete appointments'],
            
            // Treatment permissions
            ['name' => 'View Treatments', 'module' => 'treatments', 'action' => 'read', 'description' => 'Can view treatment records'],
            ['name' => 'Create Treatments', 'module' => 'treatments', 'action' => 'create', 'description' => 'Can create new treatments'],
            ['name' => 'Edit Treatments', 'module' => 'treatments', 'action' => 'update', 'description' => 'Can edit treatment records'],
            ['name' => 'Delete Treatments', 'module' => 'treatments', 'action' => 'delete', 'description' => 'Can delete treatments'],
            
            // Prescription permissions
            ['name' => 'View Prescriptions', 'module' => 'prescriptions', 'action' => 'read', 'description' => 'Can view prescription records'],
            ['name' => 'Create Prescriptions', 'module' => 'prescriptions', 'action' => 'create', 'description' => 'Can create new prescriptions'],
            ['name' => 'Edit Prescriptions', 'module' => 'prescriptions', 'action' => 'update', 'description' => 'Can edit prescriptions'],
            ['name' => 'Delete Prescriptions', 'module' => 'prescriptions', 'action' => 'delete', 'description' => 'Can delete prescriptions'],
            
            // Finance permissions
            ['name' => 'View Finance', 'module' => 'finance', 'action' => 'read', 'description' => 'Can view financial records'],
            ['name' => 'Create Finance', 'module' => 'finance', 'action' => 'create', 'description' => 'Can create financial transactions'],
            ['name' => 'Edit Finance', 'module' => 'finance', 'action' => 'update', 'description' => 'Can edit financial records'],
            ['name' => 'Delete Finance', 'module' => 'finance', 'action' => 'delete', 'description' => 'Can delete financial records'],
            
            // Reports permissions
            ['name' => 'View Reports', 'module' => 'reports', 'action' => 'read', 'description' => 'Can view reports'],
            ['name' => 'Export Reports', 'module' => 'reports', 'action' => 'export', 'description' => 'Can export reports'],
            
            // Inventory permissions
            ['name' => 'View Inventory', 'module' => 'inventory', 'action' => 'read', 'description' => 'Can view inventory'],
            ['name' => 'Create Inventory', 'module' => 'inventory', 'action' => 'create', 'description' => 'Can add inventory items'],
            ['name' => 'Edit Inventory', 'module' => 'inventory', 'action' => 'update', 'description' => 'Can edit inventory items'],
            ['name' => 'Delete Inventory', 'module' => 'inventory', 'action' => 'delete', 'description' => 'Can delete inventory items'],
            
            // User management permissions
            ['name' => 'View Users', 'module' => 'users', 'action' => 'read', 'description' => 'Can view user list'],
            ['name' => 'Create Users', 'module' => 'users', 'action' => 'create', 'description' => 'Can create new users'],
            ['name' => 'Edit Users', 'module' => 'users', 'action' => 'update', 'description' => 'Can edit user information'],
            ['name' => 'Delete Users', 'module' => 'users', 'action' => 'delete', 'description' => 'Can delete users'],
            
            // Role management permissions
            ['name' => 'View Roles', 'module' => 'roles', 'action' => 'read', 'description' => 'Can view roles'],
            ['name' => 'Create Roles', 'module' => 'roles', 'action' => 'create', 'description' => 'Can create new roles'],
            ['name' => 'Edit Roles', 'module' => 'roles', 'action' => 'update', 'description' => 'Can edit roles'],
            ['name' => 'Delete Roles', 'module' => 'roles', 'action' => 'delete', 'description' => 'Can delete roles'],
            
            // Settings permissions
            ['name' => 'View Settings', 'module' => 'settings', 'action' => 'read', 'description' => 'Can view system settings'],
            ['name' => 'Edit Settings', 'module' => 'settings', 'action' => 'update', 'description' => 'Can modify system settings'],
        ];

        $this->db->table('permissions')->insertBatch($permissions);

        // Insert default roles
        $roles = [
            ['name' => 'Super Admin', 'description' => 'Full system access with all permissions', 'is_active' => 1],
            ['name' => 'Doctor', 'description' => 'Medical staff with patient care permissions', 'is_active' => 1],
            ['name' => 'Receptionist', 'description' => 'Front desk staff with appointment and patient management', 'is_active' => 1],
            ['name' => 'Dental Assistant', 'description' => 'Support staff with limited patient access', 'is_active' => 1],
            ['name' => 'Manager', 'description' => 'Management staff with reporting and user management', 'is_active' => 1],
            ['name' => 'View Only', 'description' => 'Read-only access to most modules', 'is_active' => 1],
        ];

        $this->db->table('roles')->insertBatch($roles);

        // Assign permissions to roles
        $this->assignRolePermissions();
    }

    private function assignRolePermissions()
    {
        // Get all permissions
        $permissions = $this->db->table('permissions')->get()->getResultArray();
        $permissionMap = [];
        foreach ($permissions as $perm) {
            $permissionMap[$perm['module'] . '.' . $perm['action']] = $perm['id'];
        }

        // Get roles
        $roles = $this->db->table('roles')->get()->getResultArray();
        $roleMap = [];
        foreach ($roles as $role) {
            $roleMap[$role['name']] = $role['id'];
        }

        // Super Admin - All permissions
        $superAdminPermissions = array_values($permissionMap);
        foreach ($superAdminPermissions as $permId) {
            $this->db->table('role_permissions')->insert([
                'role_id' => $roleMap['Super Admin'],
                'permission_id' => $permId,
                'granted' => 1
            ]);
        }

        // Doctor permissions
        $doctorPermissions = [
            'dashboard.read',
            'patients.read', 'patients.create', 'patients.update',
            'examinations.read', 'examinations.create', 'examinations.update',
            'appointments.read', 'appointments.create', 'appointments.update',
            'treatments.read', 'treatments.create', 'treatments.update',
            'prescriptions.read', 'prescriptions.create', 'prescriptions.update',
            'finance.read',
            'reports.read',
            'inventory.read',
        ];

        foreach ($doctorPermissions as $permKey) {
            if (isset($permissionMap[$permKey])) {
                $this->db->table('role_permissions')->insert([
                    'role_id' => $roleMap['Doctor'],
                    'permission_id' => $permissionMap[$permKey],
                    'granted' => 1
                ]);
            }
        }

        // Receptionist permissions
        $receptionistPermissions = [
            'dashboard.read',
            'patients.read', 'patients.create', 'patients.update',
            'examinations.read',
            'appointments.read', 'appointments.create', 'appointments.update', 'appointments.delete',
            'treatments.read',
            'prescriptions.read',
            'finance.read', 'finance.create', 'finance.update',
            'reports.read',
            'inventory.read',
        ];

        foreach ($receptionistPermissions as $permKey) {
            if (isset($permissionMap[$permKey])) {
                $this->db->table('role_permissions')->insert([
                    'role_id' => $roleMap['Receptionist'],
                    'permission_id' => $permissionMap[$permKey],
                    'granted' => 1
                ]);
            }
        }

        // Dental Assistant permissions
        $assistantPermissions = [
            'dashboard.read',
            'patients.read',
            'examinations.read', 'examinations.create', 'examinations.update',
            'appointments.read',
            'treatments.read',
            'prescriptions.read',
            'finance.read',
            'reports.read',
            'inventory.read',
        ];

        foreach ($assistantPermissions as $permKey) {
            if (isset($permissionMap[$permKey])) {
                $this->db->table('role_permissions')->insert([
                    'role_id' => $roleMap['Dental Assistant'],
                    'permission_id' => $permissionMap[$permKey],
                    'granted' => 1
                ]);
            }
        }

        // Manager permissions
        $managerPermissions = [
            'dashboard.read',
            'patients.read', 'patients.create', 'patients.update',
            'examinations.read', 'examinations.create', 'examinations.update',
            'appointments.read', 'appointments.create', 'appointments.update', 'appointments.delete',
            'treatments.read', 'treatments.create', 'treatments.update',
            'prescriptions.read', 'prescriptions.create', 'prescriptions.update',
            'finance.read', 'finance.create', 'finance.update', 'finance.delete',
            'reports.read', 'reports.export',
            'inventory.read', 'inventory.create', 'inventory.update',
            'users.read', 'users.create', 'users.update',
        ];

        foreach ($managerPermissions as $permKey) {
            if (isset($permissionMap[$permKey])) {
                $this->db->table('role_permissions')->insert([
                    'role_id' => $roleMap['Manager'],
                    'permission_id' => $permissionMap[$permKey],
                    'granted' => 1
                ]);
            }
        }

        // View Only permissions
        $viewOnlyPermissions = [
            'dashboard.read',
            'patients.read',
            'examinations.read',
            'appointments.read',
            'treatments.read',
            'prescriptions.read',
            'finance.read',
            'reports.read',
            'inventory.read',
        ];

        foreach ($viewOnlyPermissions as $permKey) {
            if (isset($permissionMap[$permKey])) {
                $this->db->table('role_permissions')->insert([
                    'role_id' => $roleMap['View Only'],
                    'permission_id' => $permissionMap[$permKey],
                    'granted' => 1
                ]);
            }
        }
    }
}
