<?php

declare(strict_types=1);
namespace App\Services;

use CodeIgniter\Database\BaseConnection;
use RuntimeException;

/** Ordered global authority locks after the caller acquires the clinic mutex. */
final class SchedulingAuthorityLocks
{
    public static function acquire(BaseConnection $db, int $clinicId, ?array $userIds = null, array $extraUsers = [], bool $platform = false): void
    {
        if ($clinicId <= 0 || $db->transDepth !== 1) {
            throw new RuntimeException('Owned clinic-first scheduling transaction required.');
        }
        $bindings = [$clinicId];
        $sql = 'SELECT user_id, role_id FROM clinic_users WHERE clinic_id = ?';
        if ($userIds !== null) {
            $userIds = self::ids($userIds);
            if ($userIds === []) return;
            $sql .= ' AND user_id IN (' . implode(',', array_fill(0, count($userIds), '?')) . ')';
            $bindings = array_merge($bindings, $userIds);
        }
        // Current clinic-owned reads do not establish an RR snapshot before global locks.
        // Membership writers share the clinic mutex; no joined global-table locks here.
        $members = $db->query($sql . ' ORDER BY user_id FOR UPDATE', $bindings)->getResultArray();
        $users = self::ids(array_merge($userIds ?? array_column($members, 'user_id'), $extraUsers));
        foreach ($users as $id) {
            $db->query('SELECT id FROM users WHERE id = ? FOR UPDATE', [$id])->getRowArray();
        }
        $roles = array_column($members, 'role_id');
        if ($platform) {
            foreach ($users as $id) {
                $assignments = $db->query('SELECT id, role_id FROM user_roles WHERE user_id = ? ORDER BY id FOR UPDATE', [$id])->getResultArray();
                $roles = array_merge($roles, array_column($assignments, 'role_id'));
            }
        }
        $roles = self::ids($roles);
        // Point reads guarantee acquisition order without relying on join/IN optimization.
        foreach ($roles as $id) {
            $db->query('SELECT id FROM roles WHERE id = ? FOR UPDATE', [$id])->getRowArray();
        }
        foreach ($roles as $id) {
            $db->query('SELECT id FROM role_permissions WHERE role_id = ? ORDER BY id FOR UPDATE', [$id])->getResultArray();
        }
    }

    private static function ids(array $values): array
    {
        $ids = array_values(array_unique(array_filter(array_map('intval', $values), static fn(int $id): bool => $id > 0)));
        sort($ids, SORT_NUMERIC);
        return $ids;
    }
}
