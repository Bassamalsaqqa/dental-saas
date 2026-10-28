<?php
declare(strict_types=1);
namespace App\Services;

use CodeIgniter\Database\BaseConnection;

/** Theme-scope adapter to canonical RBAC, including current-grant locks during publication. */
final class StudioThemeAccess
{
    public function allows(BaseConnection $db, ?int $clinic, int $actor, bool $locked): bool
    {
        if ($actor <= 0) return false;
        if ($locked && ((int)$db->transDepth < 1 || !$db->transStatus())) throw new \RuntimeException('Theme authorization requires the owned transaction.');
        if ($locked) {
            // The store already owns platform/clinic locks. Lock current identity then membership/role grants.
            $db->query('SELECT id FROM '.$db->escapeIdentifiers($db->prefixTable('users')).' WHERE id=? FOR UPDATE',[$actor]);
            if ($clinic === null) {
                $db->query('SELECT ur.id FROM '.$db->escapeIdentifiers($db->prefixTable('user_roles')).' ur WHERE ur.user_id=? ORDER BY ur.id FOR UPDATE',[$actor]);
                $db->query('SELECT r.id FROM '.$db->escapeIdentifiers($db->prefixTable('roles')).' r JOIN '.$db->escapeIdentifiers($db->prefixTable('user_roles')).' ur ON ur.role_id=r.id WHERE ur.user_id=? ORDER BY r.id FOR UPDATE',[$actor]);
            } else {
                $db->query('SELECT id FROM '.$db->escapeIdentifiers($db->prefixTable('clinic_users')).' WHERE clinic_id=? AND user_id=? ORDER BY id FOR UPDATE',[$clinic,$actor]);
                $db->query('SELECT r.id FROM '.$db->escapeIdentifiers($db->prefixTable('roles')).' r JOIN '.$db->escapeIdentifiers($db->prefixTable('clinic_users')).' cu ON cu.role_id=r.id WHERE cu.clinic_id=? AND cu.user_id=? ORDER BY r.id FOR UPDATE',[$clinic,$actor]);
                $db->query('SELECT rp.role_id,rp.permission_id FROM '.$db->escapeIdentifiers($db->prefixTable('role_permissions')).' rp JOIN '.$db->escapeIdentifiers($db->prefixTable('clinic_users')).' cu ON cu.role_id=rp.role_id WHERE cu.clinic_id=? AND cu.user_id=? ORDER BY rp.role_id,rp.permission_id FOR UPDATE',[$clinic,$actor]);
            }
        }
        $permissions = new PermissionService($db);
        if ($clinic === null) return session('global_mode') === true && !session('is_impersonating') && $permissions->isSuperAdmin($actor);
        if (session('global_mode') === true || (int)session('active_clinic_id') !== $clinic) return false;
        if (session('is_impersonating') === true) {
            // Inspect target appearance without granting implicit tenant publication authority.
            return !$locked && (int)session('impersonated_clinic_id') === $clinic && $permissions->isSuperAdmin($actor);
        }
        $role = $permissions->getTenantRoleId($actor,$clinic);
        if (!$role) return false;
        $row = $db->table('roles')->select('slug')->where('id',$role)->get()->getRowArray();
        return in_array($row['slug'] ?? '',['clinic_admin','practice_manager'],true)
            && $permissions->hasPermission($actor,'settings',$locked?'edit':'view');
    }

}
