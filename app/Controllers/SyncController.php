<?php

namespace App\Controllers;

use App\Services\PermissionSyncService;

class SyncController extends BaseController
{
    private function checkPlatformAccess(): bool
    {
        if (!$this->isLoggedIn()) {
            return false;
        }

        $user = $this->getCurrentUser();
        if (!$user) {
            return false;
        }

        $this->initPermissionService();
        if (!$this->permissionService || !$this->permissionService->isSuperAdmin($user->id)) {
            return false;
        }

        return session()->get('global_mode') === true;
    }

    /**
     * Sync permissions from config to database
     */
    public function sync()
    {
        if (!$this->checkPlatformAccess()) {
            return redirect()->to('/dashboard')->with('error', 'You must be a platform administrator in global mode to sync permissions.');
        }

        try {
            $syncService = new PermissionSyncService();
            $result = $syncService->fullSync();

            $message = "Successfully synced {$result['permissions_synced']} permissions and {$result['roles_synced']} roles.";
            
            if ($result['admin_assigned'] > 0) {
                $message .= " Assigned {$result['admin_assigned']} admin user(s) to Super Admin role.";
            }
            
            return redirect()->to('/rbac/setup')->with('success', $message);
            
        } catch (\Exception $e) {
            log_message('error', 'Permission sync failed: ' . $e->getMessage());
            return redirect()->to('/rbac/setup')->with('error', 'Failed to sync permissions: ' . $e->getMessage());
        }
    }

    /**
     * Get sync status
     */
    public function status()
    {
        if (!$this->checkPlatformAccess()) {
            return $this->response->setStatusCode(403)->setJSON(['error' => 'Permission denied']);
        }

        try {
            $syncService = new PermissionSyncService();
            $status = $syncService->getSyncStatus();

            return $this->response->setJSON([
                'success' => true,
                'status' => $status
            ]);
            
        } catch (\Exception $e) {
            return $this->response->setJSON([
                'error' => 'Failed to get sync status: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Initialize RBAC system (first time setup)
     */
    public function init()
    {
        if (!$this->checkPlatformAccess()) {
            return redirect()->to('/dashboard')->with('error', 'You must be a platform administrator in global mode to initialize RBAC.');
        }

        try {
            $syncService = new PermissionSyncService();
            
            // Sync permissions
            $permissionResult = $syncService->syncPermissions();
            
            // Sync roles
            $roleResult = $syncService->syncRoles();

            $message = "RBAC system initialized successfully! Synced {$permissionResult} permissions and {$roleResult} roles.";
            
            return redirect()->to('/rbac/setup')->with('success', $message);
            
        } catch (\Exception $e) {
            log_message('error', 'RBAC initialization failed: ' . $e->getMessage());
            return redirect()->to('/rbac/setup')->with('error', 'Failed to initialize RBAC: ' . $e->getMessage());
        }
    }
}
