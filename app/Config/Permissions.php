<?php

namespace App\Config;

class Permissions
{
    /**
     * Get all default permissions
     */
    public static function getDefaultPermissions()
    {
        return [
            // Dashboard
            'dashboard' => [
                'view' => 'View Dashboard'
            ],
            
            // Patient Management
            'patients' => [
                'view' => 'View Patients',
                'create' => 'Create Patients',
                'edit' => 'Edit Patients',
                'delete' => 'Delete Patients',
                'export' => 'Export Patient Data'
            ],
            
            // Appointment Management
            'appointments' => [
                'view' => 'View Appointments',
                'create' => 'Create Appointments',
                'edit' => 'Edit Appointments',
                'delete' => 'Delete Appointments',
                'calendar' => 'Manage Calendar'
            ],
            
            // Examination Management
            'examinations' => [
                'view' => 'View Examinations',
                'create' => 'Create Examinations',
                'edit' => 'Edit Examinations',
                'delete' => 'Delete Examinations'
            ],
            
            // Treatment Management
            'treatments' => [
                'view' => 'View Treatments',
                'create' => 'Create Treatments',
                'edit' => 'Edit Treatments',
                'delete' => 'Delete Treatments'
            ],
            
            // Prescription Management
            'prescriptions' => [
                'view' => 'View Prescriptions',
                'create' => 'Create Prescriptions',
                'edit' => 'Edit Prescriptions',
                'delete' => 'Delete Prescriptions',
                'print' => 'Print Prescriptions'
            ],
            
            // Financial Management
            'finance' => [
                'view' => 'View Financial Data',
                'create' => 'Create Transactions',
                'edit' => 'Edit Transactions',
                'delete' => 'Delete Transactions',
                'reports' => 'Financial Reports'
            ],
            
            // Reports
            'reports' => [
                'view' => 'View Reports',
                'export' => 'Export Reports'
            ],
            
            // Inventory Management
            'inventory' => [
                'view' => 'View Inventory',
                'create' => 'Add Inventory',
                'edit' => 'Edit Inventory',
                'delete' => 'Delete Inventory'
            ],
            
            // User Management
            'users' => [
                'view' => 'View Users',
                'create' => 'Create Users',
                'edit' => 'Edit Users',
                'delete' => 'Delete Users',
                'roles' => 'Manage Roles'
            ],
            
            // Settings
            'settings' => [
                'view' => 'View Settings',
                'edit' => 'Edit Settings'
            ]
        ];
    }
    
    /**
     * Get default roles with their permissions
     */
    public static function getDefaultRoles()
    {
        return [
            'super_admin' => [
                'name' => 'Super Admin',
                'description' => 'Full system access with all permissions',
                'permissions' => '*' // All permissions
            ],
            'practice_manager' => [
                'name' => 'Practice Manager',
                'description' => 'Management level access with most permissions',
                'permissions' => [
                    'dashboard' => ['read'],
                    'patients' => ['view', 'create', 'edit'],
                    'appointments' => ['view', 'create', 'edit', 'delete', 'calendar'],
                    'examinations' => ['view', 'create', 'edit'],
                    'treatments' => ['view', 'create', 'edit'],
                    'prescriptions' => ['view', 'create', 'edit'],
                    'finance' => ['view', 'create', 'edit', 'reports'],
                    'reports' => ['view', 'export'],
                    'inventory' => ['view', 'create', 'edit'],
                    'users' => ['view', 'create', 'edit'],
                    'settings' => ['view', 'edit']
                ]
            ],
            'senior_doctor' => [
                'name' => 'Senior Doctor',
                'description' => 'Full medical access with management capabilities',
                'permissions' => [
                    'dashboard' => ['read'],
                    'patients' => ['view', 'create', 'edit'],
                    'appointments' => ['view', 'create', 'edit'],
                    'examinations' => ['view', 'create', 'edit', 'delete'],
                    'treatments' => ['view', 'create', 'edit', 'delete'],
                    'prescriptions' => ['view', 'create', 'edit', 'print'],
                    'finance' => ['view'],
                    'reports' => ['view'],
                    'inventory' => ['view']
                ]
            ],
            'doctor' => [
                'name' => 'Doctor',
                'description' => 'Standard medical access for patient care',
                'permissions' => [
                    'dashboard' => ['read'],
                    'patients' => ['view', 'create', 'edit'],
                    'appointments' => ['view', 'create', 'edit'],
                    'examinations' => ['view', 'create', 'edit'],
                    'treatments' => ['view', 'create', 'edit'],
                    'prescriptions' => ['view', 'create', 'edit', 'print'],
                    'finance' => ['view'],
                    'reports' => ['view'],
                    'inventory' => ['view']
                ]
            ],
            'receptionist' => [
                'name' => 'Receptionist',
                'description' => 'Patient management and appointment scheduling',
                'permissions' => [
                    'dashboard' => ['read'],
                    'patients' => ['view', 'create', 'edit'],
                    'appointments' => ['view', 'create', 'edit', 'delete', 'calendar'],
                    'examinations' => ['view'],
                    'treatments' => ['view'],
                    'prescriptions' => ['view'],
                    'finance' => ['view', 'create', 'edit'],
                    'reports' => ['view'],
                    'inventory' => ['view']
                ]
            ],
            'dental_assistant' => [
                'name' => 'Dental Assistant',
                'description' => 'Limited medical access for assistance',
                'permissions' => [
                    'dashboard' => ['read'],
                    'patients' => ['view'],
                    'appointments' => ['view'],
                    'examinations' => ['view', 'create', 'edit'],
                    'treatments' => ['view'],
                    'prescriptions' => ['view'],
                    'finance' => ['view'],
                    'reports' => ['view'],
                    'inventory' => ['view']
                ]
            ],
            'accountant' => [
                'name' => 'Accountant',
                'description' => 'Financial management and reporting',
                'permissions' => [
                    'dashboard' => ['read'],
                    'patients' => ['view'],
                    'finance' => ['view', 'create', 'edit', 'reports'],
                    'reports' => ['view', 'export']
                ]
            ],
            'read_only' => [
                'name' => 'Read Only',
                'description' => 'View-only access to most features',
                'permissions' => [
                    'dashboard' => ['read'],
                    'patients' => ['view'],
                    'appointments' => ['view'],
                    'examinations' => ['view'],
                    'treatments' => ['view'],
                    'prescriptions' => ['view'],
                    'finance' => ['view'],
                    'reports' => ['view'],
                    'inventory' => ['view']
                ]
            ]
        ];
    }

    /**
     * Get permission categories
     */
    public static function getCategories()
    {
        return [
            'dashboard' => 'Dashboard',
            'patients' => 'Patient Management',
            'appointments' => 'Appointment Management',
            'examinations' => 'Examination Management',
            'treatments' => 'Treatment Management',
            'prescriptions' => 'Prescription Management',
            'finance' => 'Financial Management',
            'reports' => 'Reports & Analytics',
            'inventory' => 'Inventory Management',
            'users' => 'User Management',
            'settings' => 'System Settings'
        ];
    }

    /**
     * Get action descriptions
     */
    public static function getActionDescriptions()
    {
        return [
            'view' => 'View and read data',
            'create' => 'Create new records',
            'edit' => 'Edit existing records',
            'delete' => 'Delete records',
            'export' => 'Export data to files',
            'print' => 'Print documents',
            'calendar' => 'Manage calendar',
            'reports' => 'Generate reports',
            'roles' => 'Manage user roles'
        ];
    }
}
