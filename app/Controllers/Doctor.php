<?php

namespace App\Controllers;

use App\Models\UserModel;
use App\Models\RoleModel;
use App\Models\UserRoleModel;
use App\Models\ClinicUserModel;

class Doctor extends BaseController
{
    protected $userModel;
    protected $roleModel;
    protected $userRoleModel;
    protected $clinicUserModel;

    public function __construct()
    {
        $this->userModel = new UserModel();
        $this->roleModel = new RoleModel();
        $this->userRoleModel = new UserRoleModel();
        $this->clinicUserModel = new ClinicUserModel();
    }

    /**
     * Display doctors list
     */
    public function index()
    {
        $clinicId = session()->get('active_clinic_id');
        if (!$clinicId) {
            return redirect()->to('/clinic/select');
        }

        $data = [
            'title' => 'Doctor Management',
            'doctors' => $this->userModel->getDoctorsWithDetails($clinicId)
        ];

        return $this->view('doctor/index', $data);
    }

    /**
     * Show doctor creation form
     */
    public function create()
    {
        $data = [
            'title' => 'Add New Doctor',
            'medical_roles' => $this->getMedicalRoles(),
            'validation' => \Config\Services::validation()
        ];

        return $this->view('doctor/create', $data);
    }

    /**
     * Store new doctor
     */
    public function store()
    {
        $rules = [
            'first_name' => 'required|min_length[2]|max_length[50]',
            'last_name' => 'required|min_length[2]|max_length[50]',
            'email' => 'required|valid_email|is_unique[users.email]',
            'username' => 'required|min_length[3]|max_length[30]|is_unique[users.username]',
            'password' => 'required|min_length[8]|max_length[255]',
            'phone' => 'required|min_length[10]|max_length[20]',
            'role_id' => 'required|integer',
            'license_number' => 'required|min_length[5]|max_length[50]',
            'specialization' => 'required|in_list[general,orthodontics,oral_surgery,periodontics,pediatrics,endodontics,prosthodontics]',
            'years_experience' => 'required|integer|greater_than[0]',
            'consultation_fee' => 'required|decimal|greater_than[0]'
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('validation', $this->validator);
        }

        // Create user data
        $userData = [
            'first_name' => $this->request->getPost('first_name'),
            'last_name' => $this->request->getPost('last_name'),
            'email' => $this->request->getPost('email'),
            'username' => $this->request->getPost('username'),
            'password' => password_hash($this->request->getPost('password'), PASSWORD_DEFAULT),
            'phone' => $this->request->getPost('phone'),
            'address' => $this->request->getPost('address'),

            'active' => 1,
            'created_on' => time(),
            // Doctor-specific fields
            'license_number' => $this->request->getPost('license_number'),
            'specialization' => $this->request->getPost('specialization'),
            'years_experience' => $this->request->getPost('years_experience'),
            'consultation_fee' => $this->request->getPost('consultation_fee'),
            'medical_qualifications' => $this->request->getPost('medical_qualifications'),
            'department' => $this->request->getPost('department')
        ];

        if ($userId = $this->userModel->insert($userData)) {
            // Assign medical role
            $this->userRoleModel->assignRole(
                $userId, 
                $this->request->getPost('role_id'), 
                $this->getCurrentUser()->id
            );

            // Assign to current clinic
            $clinicId = session()->get('active_clinic_id');
            if ($clinicId) {
                $this->clinicUserModel->insert([
                    'clinic_id' => $clinicId,
                    'user_id' => $userId,
                    'role_id' => $this->request->getPost('role_id'),
                    'status' => 'active',
                    'created_at' => date('Y-m-d H:i:s')
                ]);
            }

            return redirect()->to('/doctors')->with('success', 'Doctor added successfully!');
        } else {
            return redirect()->back()->withInput()->with('error', 'Failed to create doctor: ' . implode(', ', $this->userModel->errors()));
        }
    }

    /**
     * Show doctor details
     */
    public function show($id)
    {
        $doctor = $this->userModel->getDoctorWithDetails($id);
        
        if (!$doctor) {
            throw new \CodeIgniter\Exceptions\PageNotFoundException('Doctor not found');
        }

        $data = [
            'title' => 'Doctor Details - ' . $doctor['first_name'] . ' ' . $doctor['last_name'],
            'doctor' => $doctor
        ];

        return $this->view('doctor/show', $data);
    }

    /**
     * Show doctor edit form
     */
    public function edit($id)
    {
        $doctor = $this->userModel->getDoctorWithDetails($id);
        
        if (!$doctor) {
            throw new \CodeIgniter\Exceptions\PageNotFoundException('Doctor not found');
        }

        $data = [
            'title' => 'Edit Doctor - ' . $doctor['first_name'] . ' ' . $doctor['last_name'],
            'doctor' => $doctor,
            'medical_roles' => $this->getMedicalRoles(),
            'validation' => \Config\Services::validation()
        ];

        return $this->view('doctor/edit', $data);
    }

    /**
     * Update doctor
     */
    public function update($id)
    {
        $clinicId = session()->get('active_clinic_id');
        if (!$clinicId) {
            return redirect()->to('/clinic/select');
        }

        // Verify doctor belongs to clinic
        $doctor = $this->userModel->getDoctorWithDetails($id, $clinicId);
        
        if (!$doctor) {
            throw new \CodeIgniter\Exceptions\PageNotFoundException('Doctor not found');
        }

        $rules = [
            'first_name' => 'required|min_length[2]|max_length[50]',
            'last_name' => 'required|min_length[2]|max_length[50]',
            'email' => "required|valid_email|is_unique[users.email,id,{$id}]",
            'username' => "required|min_length[3]|max_length[30]|is_unique[users.username,id,{$id}]",
            'phone' => 'required|min_length[10]|max_length[20]',
            'role_id' => 'required|integer',
            'license_number' => 'required|min_length[5]|max_length[50]',
            'specialization' => 'required|in_list[general,orthodontics,oral_surgery,periodontics,pediatrics,endodontics,prosthodontics]',
            'years_experience' => 'required|integer|greater_than[0]',
            'consultation_fee' => 'required|decimal|greater_than[0]'
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('validation', $this->validator);
        }

        // Update user data
        $userData = [
            'first_name' => $this->request->getPost('first_name'),
            'last_name' => $this->request->getPost('last_name'),
            'email' => $this->request->getPost('email'),
            'username' => $this->request->getPost('username'),
            'phone' => $this->request->getPost('phone'),
            'address' => $this->request->getPost('address'),
            // Doctor-specific fields
            'license_number' => $this->request->getPost('license_number'),
            'specialization' => $this->request->getPost('specialization'),
            'years_experience' => $this->request->getPost('years_experience'),
            'consultation_fee' => $this->request->getPost('consultation_fee'),
            'medical_qualifications' => $this->request->getPost('medical_qualifications'),
            'department' => $this->request->getPost('department')
        ];

        // Update password if provided
        if ($this->request->getPost('password')) {
            $userData['password'] = password_hash($this->request->getPost('password'), PASSWORD_DEFAULT);
        }

        // Update validation rules to exclude current doctor from uniqueness check
        $validationRules = [
            'first_name' => 'required|min_length[2]|max_length[50]',
            'last_name' => 'required|min_length[2]|max_length[50]',
            'email' => "required|valid_email|is_unique[users.email,id,{$id}]",
            'username' => "required|min_length[3]|max_length[30]|is_unique[users.username,id,{$id}]",
            'phone' => 'required|min_length[10]|max_length[20]',
            'address' => 'permit_empty|max_length[500]',
            'active' => 'required|in_list[0,1]',
            'license_number' => 'permit_empty|min_length[5]|max_length[50]',
            'specialization' => 'permit_empty|in_list[general,orthodontics,oral_surgery,periodontics,pediatrics,endodontics,prosthodontics]',
            'years_experience' => 'permit_empty|integer|greater_than_equal_to[0]',
            'consultation_fee' => 'permit_empty|decimal|greater_than_equal_to[0]',
            'medical_qualifications' => 'permit_empty',
            'department' => 'permit_empty|in_list[dental,orthodontics,oral_surgery,periodontics,pediatrics,general]'
        ];
        
        $this->userModel->setValidationRules($validationRules);
        
        if ($this->userModel->update($id, $userData)) {
            // Update role if changed
            $newRoleId = $this->request->getPost('role_id');
            $currentRole = $this->userRoleModel->getUserRoles($id);
            
            if (!empty($currentRole) && $currentRole[0]['role_id'] != $newRoleId) {
                // Remove old role and assign new one
                $this->userRoleModel->removeRole($id, $currentRole[0]['role_id']);
                $this->userRoleModel->assignRole($id, $newRoleId, $this->getCurrentUser()->id);
            }

            return redirect()->to('/doctors/' . $id)->with('success', 'Doctor updated successfully!');
        } else {
            return redirect()->back()->withInput()->with('error', 'Failed to update doctor: ' . implode(', ', $this->userModel->errors()));
        }
    }

    /**
     * Delete doctor
     */
    public function delete($id)
    {
        $clinicId = session()->get('active_clinic_id');
        if (!$clinicId) {
            return $this->response->setStatusCode(403)->setJSON(['error' => 'TENANT_CONTEXT_REQUIRED']);
        }

        // Verify doctor belongs to clinic
        $doctor = $this->userModel->getDoctorWithDetails($id, $clinicId);
        
        if (!$doctor) {
            return $this->response->setJSON(['success' => false, 'message' => 'Doctor not found']);
        }

        // Soft delete by setting active to 0
        if ($this->userModel->update($id, ['active' => 0])) {
            return $this->response->setJSON(['success' => true, 'message' => 'Doctor deactivated successfully']);
        } else {
            return $this->response->setJSON(['success' => false, 'message' => 'Failed to deactivate doctor']);
        }
    }

    /**
     * Get medical roles only
     */
    private function getMedicalRoles()
    {
        return $this->roleModel->where('is_medical', 1)
                              ->where('is_active', 1)
                              ->orderBy('name', 'ASC')
                              ->findAll();
    }
}