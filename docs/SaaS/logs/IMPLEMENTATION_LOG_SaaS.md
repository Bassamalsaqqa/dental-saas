
### Task: S1-01b Hotfix Super Admin Check
- **Date:** 2026-01-13
- **Status:** Completed
- **Description:** Corrected the Super Admin check in `ControlPlaneFilter` and `ControlPlane` controller. Replaced invalid `hasPermission('system', 'super_admin')` with canonical `$permissionService->isSuperAdmin($userId)`.
- **Files Changed:**
    - `app/Filters/ControlPlaneFilter.php`
    - `app/Controllers/ControlPlane.php`
- **Verification:** Updated `docs/SaaS/verification/S1-01.md`. `rg` scan confirms removal of `system.super_admin`.
