<?php

namespace App\Controllers;

class UsersTest extends BaseController
{
    public function index()
    {
        $data = [
            'title' => 'User Management - Test',
            'users' => [],
            'total_users' => 0,
            'active_users' => 0,
            'inactive_users' => 0
        ];

        return view('users/index', $data);
    }
}
