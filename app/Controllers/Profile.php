<?php

namespace App\Controllers;

use App\Controllers\BaseController;

class Profile extends BaseController
{
    public function index()
    {
        // Check if user is logged in
        if (!$this->isLoggedIn()) {
            return redirect()->to('auth/login');
        }

        $user = $this->getCurrentUser();
        $user_groups = $this->getUserGroups();

        $data = [
            'title' => 'Profile',
            'user' => $user,
            'user_groups' => $user_groups,
            'validation' => \Config\Services::validation()
        ];

        return view('profile/index', $data);
    }

    public function update()
    {
        // Check if user is logged in
        if (!$this->isLoggedIn()) {
            return redirect()->to('auth/login');
        }

        $user = $this->getCurrentUser();
        
        // Validation rules
        $rules = [
            'first_name' => 'required|min_length[2]|max_length[50]',
            'last_name' => 'required|min_length[2]|max_length[50]',
            'email' => 'required|valid_email|is_unique[users.email,id,' . $user->id . ']',
            'phone' => 'permit_empty|min_length[10]|max_length[15]'
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('validation', $this->validator);
        }

        $data = [
            'first_name' => $this->request->getPost('first_name'),
            'last_name' => $this->request->getPost('last_name'),
            'email' => $this->request->getPost('email'),
            'phone' => $this->request->getPost('phone')
        ];

        if ($this->ionAuth->update($user->id, $data)) {
            $this->session->setFlashdata('success', 'Your profile has been updated successfully!');
        } else {
            $errors = $this->ionAuth->errors();
            $errorMessage = !empty($errors) ? implode(', ', $errors) : 'Failed to update profile. Please try again.';
            $this->session->setFlashdata('error', $errorMessage);
        }

        return redirect()->to('profile');
    }

    public function changePassword()
    {
        // Check if user is logged in
        if (!$this->isLoggedIn()) {
            return redirect()->to('auth/login');
        }

        $user = $this->getCurrentUser();
        
        // Validation rules
        $rules = [
            'current_password' => 'required',
            'new_password' => 'required|min_length[8]',
            'confirm_password' => 'required|matches[new_password]'
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('validation', $this->validator);
        }

        $currentPassword = $this->request->getPost('current_password');
        $newPassword = $this->request->getPost('new_password');

        // Verify current password
        if (!$this->ionAuth->verifyPassword($currentPassword, $user->password)) {
            $this->session->setFlashdata('error', 'The current password you entered is incorrect. Please try again.');
            return redirect()->to('profile');
        }

        // Update password
        if ($this->ionAuth->update($user->id, ['password' => $newPassword])) {
            $this->session->setFlashdata('success', 'Your password has been changed successfully!');
        } else {
            $errors = $this->ionAuth->errors();
            $errorMessage = !empty($errors) ? implode(', ', $errors) : 'Failed to change password. Please try again.';
            $this->session->setFlashdata('error', $errorMessage);
        }

        return redirect()->to('profile');
    }
}
