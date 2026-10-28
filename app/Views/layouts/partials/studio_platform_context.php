<?php
// BaseController injects is_super_admin from PermissionService::isSuperAdmin()
// on this authenticated request. Tenant role names and request data are never authority.
$studioPlatformAdmin = ($is_super_admin ?? false) === true && (int) session('user_id') > 0;
$studioImpersonating = $studioPlatformAdmin
    && session('global_mode') !== true
    && session('is_impersonating') === true
    && (int) session('active_clinic_id') > 0
    && (int) session('active_clinic_id') === (int) session('impersonated_clinic_id');
?>
