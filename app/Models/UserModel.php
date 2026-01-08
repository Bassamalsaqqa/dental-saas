<?php

namespace App\Models;

use CodeIgniter\Model;

class UserModel extends Model
{
    protected $table = 'users';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = false;
    protected $protectFields = true;
    protected $allowedFields = [
        'first_name',
        'last_name',
        'email',
        'username',
        'password',
        'role',
        'department',
        'phone',
        'address',
        'hire_date',
        'active',
        'last_login',
        // Doctor-specific fields
        'license_number',
        'specialization',
        'years_experience',
        'consultation_fee',
        'medical_qualifications',
        'availability_schedule',
        'created_on',
        'created_at',
        'updated_at'
    ];

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat = 'datetime';
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    // Validation
    protected $validationRules = [
        'first_name' => 'required|min_length[2]|max_length[50]',
        'last_name' => 'required|min_length[2]|max_length[50]',
        'email' => 'required|valid_email|is_unique[users.email,id,{id}]',
        'username' => 'required|min_length[3]|max_length[30]|is_unique[users.username,id,{id}]',
        'password' => 'permit_empty|min_length[8]|max_length[255]',
        'role' => 'permit_empty|integer',
        'department' => 'permit_empty|in_list[dental,orthodontics,oral_surgery,periodontics,pediatrics,general]',
        'phone' => 'permit_empty|min_length[10]|max_length[20]',
        'address' => 'permit_empty|max_length[500]',
        'hire_date' => 'permit_empty|valid_date',
        'active' => 'required|in_list[0,1]',
    ];

    protected $validationMessages = [
        'first_name' => [
            'required' => 'First name is required',
            'min_length' => 'First name must be at least 2 characters long',
            'max_length' => 'First name cannot exceed 50 characters'
        ],
        'last_name' => [
            'required' => 'Last name is required',
            'min_length' => 'Last name must be at least 2 characters long',
            'max_length' => 'Last name cannot exceed 50 characters'
        ],
        'email' => [
            'required' => 'Email is required',
            'valid_email' => 'Please enter a valid email address',
            'is_unique' => 'This email is already registered'
        ],
        'username' => [
            'required' => 'Username is required',
            'min_length' => 'Username must be at least 3 characters long',
            'max_length' => 'Username cannot exceed 30 characters',
            'is_unique' => 'This username is already taken'
        ],
        'password' => [
            'min_length' => 'Password must be at least 8 characters long',
            'max_length' => 'Password cannot exceed 255 characters'
        ],
        'role' => [
            'required' => 'Role is required',
            'in_list' => 'Invalid role selected'
        ],
        'hire_date' => [
            'required' => 'Hire date is required',
            'valid_date' => 'Invalid date format'
        ],
        'status' => [
            'required' => 'Status is required',
            'in_list' => 'Invalid status selected'
        ]
    ];

    protected $skipValidation = false;
    protected $cleanValidationRules = true;

    // Callbacks
    protected $allowCallbacks = true;
    protected $beforeInsert = ['hashPassword'];
    protected $afterInsert = [];
    protected $beforeUpdate = ['hashPassword'];
    protected $afterUpdate = [];
    protected $beforeFind = [];
    protected $afterFind = [];
    protected $beforeDelete = [];
    protected $afterDelete = [];

    protected function hashPassword(array $data)
    {
        if (isset($data['data']['password']) && !empty($data['data']['password'])) {
            $data['data']['password'] = password_hash($data['data']['password'], PASSWORD_DEFAULT);
        }
        return $data;
    }

    public function getUsersWithStats()
    {
        return $this->select('users.*, 
                            CASE 
                                WHEN last_login IS NULL THEN "never"
                                WHEN last_login < DATE_SUB(NOW(), INTERVAL 30 DAY) THEN "inactive"
                                ELSE "active"
                            END as login_status')
                    ->orderBy('first_name', 'ASC')
                    ->findAll();
    }

    public function getActiveUsers()
    {
        return $this->where('active', 1)
                    ->orderBy('first_name', 'ASC')
                    ->findAll();
    }

    public function getUsersByRole($role)
    {
        return $this->where('role', $role)
                    ->where('active', 1)
                    ->orderBy('first_name', 'ASC')
                    ->findAll();
    }

    public function getUsersByDepartment($department)
    {
        return $this->where('department', $department)
                    ->where('active', 1)
                    ->orderBy('first_name', 'ASC')
                    ->findAll();
    }

    public function getUserStats()
    {
        return [
            'total' => $this->countAllResults(),
            'active' => $this->where('active', 1)->countAllResults(),
            'inactive' => $this->where('active', 0)->countAllResults(),
        ];
    }

    public function getRoleStats()
    {
        return $this->select('role, COUNT(*) as count')
                    ->groupBy('role')
                    ->orderBy('count', 'DESC')
                    ->findAll();
    }

    public function getDepartmentStats()
    {
        return $this->select('department, COUNT(*) as count')
                    ->where('department IS NOT NULL')
                    ->groupBy('department')
                    ->orderBy('count', 'DESC')
                    ->findAll();
    }

    public function searchUsers($searchTerm)
    {
        return $this->groupStart()
                    ->like('first_name', $searchTerm)
                    ->orLike('last_name', $searchTerm)
                    ->orLike('email', $searchTerm)
                    ->orLike('username', $searchTerm)
                    ->orLike('role', $searchTerm)
                    ->orLike('department', $searchTerm)
                    ->groupEnd()
                    ->orderBy('first_name', 'ASC')
                    ->findAll();
    }

    public function getRecentUsers($limit = 10)
    {
        return $this->orderBy('created_at', 'DESC')
                    ->limit($limit)
                    ->findAll();
    }

    public function getInactiveUsers($days = 30)
    {
        $cutoffDate = date('Y-m-d H:i:s', strtotime("-{$days} days"));
        return $this->where('last_login <', $cutoffDate)
                    ->orWhere('last_login IS NULL')
                    ->where('status', 'active')
                    ->orderBy('last_login', 'ASC')
                    ->findAll();
    }

    public function updateLastLogin($userId)
    {
        return $this->update($userId, ['last_login' => date('Y-m-d H:i:s')]);
    }

    public function authenticate($username, $password)
    {
        $user = $this->where('username', $username)
                     ->orWhere('email', $username)
                     ->where('status', 'active')
                     ->first();

        if ($user && password_verify($password, $user['password'])) {
            $this->updateLastLogin($user['id']);
            return $user;
        }

        return false;
    }

    public function getUserPermissions($role)
    {
        $permissions = [
            'admin' => [
                'dashboard' => ['read'],
                'patients' => ['read', 'create', 'update', 'delete'],
                'examinations' => ['read', 'create', 'update', 'delete'],
                'appointments' => ['read', 'create', 'update', 'delete'],
                'treatments' => ['read', 'create', 'update', 'delete'],
                'prescriptions' => ['read', 'create', 'update', 'delete'],
                'finance' => ['read', 'create', 'update', 'delete'],
                'reports' => ['read', 'export'],
                'inventory' => ['read', 'create', 'update', 'delete'],
                'settings' => ['read', 'update'],
                'users' => ['read', 'create', 'update', 'delete'],
            ],
            'doctor' => [
                'dashboard' => ['read'],
                'patients' => ['read', 'create', 'update'],
                'examinations' => ['read', 'create', 'update'],
                'appointments' => ['read', 'create', 'update'],
                'treatments' => ['read', 'create', 'update'],
                'prescriptions' => ['read', 'create', 'update'],
                'finance' => ['read'],
                'reports' => ['read'],
                'inventory' => ['read'],
            ],
            'receptionist' => [
                'dashboard' => ['read'],
                'patients' => ['read', 'create', 'update'],
                'examinations' => ['read'],
                'appointments' => ['read', 'create', 'update', 'delete'],
                'treatments' => ['read'],
                'prescriptions' => ['read'],
                'finance' => ['read', 'create', 'update'],
                'reports' => ['read'],
                'inventory' => ['read'],
            ],
            'assistant' => [
                'dashboard' => ['read'],
                'patients' => ['read'],
                'examinations' => ['read', 'create', 'update'],
                'appointments' => ['read'],
                'treatments' => ['read'],
                'prescriptions' => ['read'],
                'finance' => ['read'],
                'reports' => ['read'],
                'inventory' => ['read'],
            ],
            'manager' => [
                'dashboard' => ['read'],
                'patients' => ['read', 'create', 'update'],
                'examinations' => ['read', 'create', 'update'],
                'appointments' => ['read', 'create', 'update', 'delete'],
                'treatments' => ['read', 'create', 'update'],
                'prescriptions' => ['read', 'create', 'update'],
                'finance' => ['read', 'create', 'update', 'delete'],
                'reports' => ['read', 'export'],
                'inventory' => ['read', 'create', 'update'],
                'users' => ['read', 'create', 'update'],
            ],
        ];

        return $permissions[$role] ?? [];
    }

    public function hasPermission($role, $module, $action)
    {
        $permissions = $this->getUserPermissions($role);
        return isset($permissions[$module]) && in_array($action, $permissions[$module]);
    }

    /**
     * Get all users with medical/doctor roles
     */
    public function getDoctors()
    {
        $db = \Config\Database::connect();
        
        return $db->table('users u')
                 ->select('u.id, u.first_name, u.last_name, u.email, u.username, u.phone, u.address, u.hire_date, u.status, u.license_number, u.specialization, u.years_experience, u.consultation_fee, u.medical_qualifications, u.availability_schedule, u.department, r.name as role_name, r.slug as role_slug')
                 ->join('user_roles ur', 'ur.user_id = u.id')
                 ->join('roles r', 'r.id = ur.role_id')
                 ->where('r.is_medical', 1)
                 ->where('ur.is_active', 1)
                 ->where('u.active', 1)
                 ->orderBy('u.first_name', 'ASC')
                 ->get()
                 ->getResultArray();
    }

    /**
     * Get single doctor with role details
     */
    public function getDoctor($id)
    {
        $db = \Config\Database::connect();
        
        $result = $db->table('users u')
                    ->select('u.*, r.name as role_name, r.slug as role_slug')
                    ->join('user_roles ur', 'ur.user_id = u.id')
                    ->join('roles r', 'r.id = ur.role_id')
                    ->where('u.id', $id)
                    ->where('r.is_medical', 1)
                    ->where('ur.is_active', 1)
                    ->get()
                    ->getRowArray();
        
        return $result;
    }
}
