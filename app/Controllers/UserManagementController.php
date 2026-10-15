<?php

declare(strict_types=1);

namespace App\Controllers;

use CodeIgniter\Exceptions\PageNotFoundException;

/**
 * Decommissioned Legacy Controller
 *
 * SEC-02 / SEC-03: Denied completely to eliminate parallel identity/role mutation bypasses.
 */
class UserManagementController extends BaseController
{
    public function _remap($method, ...$params)
    {
        throw PageNotFoundException::forPageNotFound('Legacy user management endpoint is decommissioned.');
    }

    public function index()
    {
        throw PageNotFoundException::forPageNotFound();
    }
}
