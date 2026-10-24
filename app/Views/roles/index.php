<?php $layout = session()->get('global_mode') ? 'layouts/main_control_plane' : 'layouts/main_auth'; ?>
<?= $this->extend($layout) ?>

<?= $this->section('content') ?>
<div class="v3-operational-view">
<?php
    $tr = static fn(string $key, array $params = []) => lang('UserRole.' . $key, $params);
    $totalRoles = count($roles);
    $activeRoles = count(array_filter($roles, static fn($role) => (int) ($role['is_active'] ?? 0) === 1));
    $systemRoles = count(array_filter($roles, static fn($role) => (int) ($role['is_system'] ?? 0) === 1));
    $totalPermissions = isset($permissions) ? count($permissions) : 0;
    $totalAssignedUsers = array_sum(array_map(static fn($role) => (int) ($role['user_count'] ?? 0), $roles));
    $contextLabel = $global_mode ? lang('UI.cpControlPlane') : lang('UI.menuSettings');
?>


<div class="roles-workspace min-h-screen">
    <div class="mx-auto max-w-7xl px-6 py-8">
        <section class="roles-hero mb-6 sm:p-6 lg:p-7">
            <div class="flex flex-col xl:flex-row xl:items-center xl:justify-between">
                <div class="flex items-start gap-4">
                    <div class="flex h-14 w-14 shrink-0 items-center justify-center border">
                        <i class="fas fa-user-shield v3-section-title"></i>
                    </div>
                    <div class="min-w-0">
                        <p class="text-xs uppercase tracking-[0.18em]"><?= esc($contextLabel) ?></p>
                        <h1 class="mt-1 v3-title tracking-tight v3-title"><?= esc($tr('roleManagement')) ?></h1>
                    </div>
                </div>
                <div class="grid grid-cols-1 gap-3 sm:grid-cols-3 xl:min-w-[31rem]">
                    <div class="roles-hero-chip px-4 py-3">
                        <p class="text-[11px] uppercase tracking-[0.14em]"><?= esc($tr('totalRoles')) ?></p>
                        <p class="mt-1 v3-section-title"><?= $totalRoles ?></p>
                    </div>
                    <div class="roles-hero-chip px-4 py-3">
                        <p class="text-[11px] uppercase tracking-[0.14em]"><?= esc($tr('activeRoles')) ?></p>
                        <p class="mt-1 v3-section-title"><?= $activeRoles ?></p>
                    </div>
                    <div class="roles-hero-chip px-4 py-3">
                        <p class="text-[11px] uppercase tracking-[0.14em]"><?= esc($tr('totalPermissions')) ?></p>
                        <p class="mt-1 v3-section-title"><?= $totalPermissions ?></p>
                    </div>
                </div>
            </div>
            <div class="mt-6 flex flex-wrap gap-3">
                <?php if ($global_mode): ?>
                    <?php if (has_permission('settings', 'edit')): ?>
                        <a href="<?= base_url($rolesRoute . '/sync') ?>" class="inline-flex items-center justify-center gap-2 border px-4 py-2.5 text-sm transition">
                            <i class="fas fa-sync-alt mr-2"></i><?= esc($tr('syncPermissions')) ?>
                        </a>
                    <?php endif; ?>
                <?php endif; ?>

                <?php if (has_permission('users', 'create') || has_permission('users', 'roles')): ?>
                    <a href="<?= workflow_context_url($rolesRoute . '/create') ?>" class="inline-flex items-center justify-center gap-2 px-4 py-2.5 text-sm transition hover:-translate-y-0.5">
                        <i class="fas fa-plus mr-2"></i><?= esc($tr('createRole')) ?>
                    </a>
                <?php endif; ?>
            </div>
        </section>

    <!-- RBAC Status -->
    <?php if (isset($diagnostics) && !$diagnostics['rbac_ready']): ?>
        <div class="mb-6 bg-yellow-50 border border-yellow-200 p-4">
            <div class="flex">
                <div class="flex-shrink-0">
                    <i class="fas fa-exclamation-triangle text-yellow-400 text-xl"></i>
                </div>
                <div class="ml-3">
                    <h3 class="text-sm font-medium text-yellow-800"><?= esc($tr('rbacSystemNotReady')) ?></h3>
                    <div class="mt-2 text-sm text-yellow-700">
                        <p><?= esc($tr('rbacNotInitializedUsers')) ?></p>
                        <div class="mt-3">
                            <a href="<?= base_url('rbac/setup') ?>" class="inline-flex items-center px-3 py-2 bg-yellow-600 text-sm font-medium rounded-lg hover:bg-yellow-700 transition-colors">
                                <i class="fas fa-cog mr-2"></i><?= esc($tr('initializeRbacSystem')) ?>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <!-- Flash Messages -->
    <?php if (session()->getFlashdata('success')): ?>
        <div class="mb-6 bg-green-50 border border-green-200 p-4">
            <div class="flex">
                <div class="flex-shrink-0">
                    <i class="fas fa-check-circle text-green-400 text-xl"></i>
                </div>
                <div class="ml-3">
                    <h3 class="text-sm font-medium text-green-800"><?= esc($tr('success')) ?></h3>
                    <div class="mt-2 text-sm text-green-700">
                        <p><?= session()->getFlashdata('success') ?></p>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <?php if (session()->getFlashdata('error')): ?>
        <div class="mb-6 bg-red-50 border border-red-200 p-4">
            <div class="flex">
                <div class="flex-shrink-0">
                    <i class="fas fa-exclamation-circle text-red-400 text-xl"></i>
                </div>
                <div class="ml-3">
                    <h3 class="text-sm font-medium text-red-800"><?= esc($tr('error')) ?></h3>
                    <div class="mt-2 text-sm text-red-700">
                        <p><?= session()->getFlashdata('error') ?></p>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <section class="mb-7 grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-4">
        <div class="roles-stat-card">
            <div class="flex items-start justify-between gap-4">
                <div>
                    <p class="text-xs uppercase tracking-[0.14em]"><?= esc($tr('totalRoles')) ?></p>
                    <p class="mt-3 v3-title"><?= $totalRoles ?></p>
                </div>
                <div class="p-3"><i class="fas fa-user-tag text-xl"></i></div>
            </div>
        </div>
        <div class="roles-stat-card">
            <div class="flex items-start justify-between gap-4">
                <div>
                    <p class="text-xs uppercase tracking-[0.14em] text-emerald-100/80"><?= esc($tr('activeRoles')) ?></p>
                    <p class="mt-3 v3-title"><?= $activeRoles ?></p>
                </div>
                <div class="p-3"><i class="fas fa-check-circle text-xl"></i></div>
            </div>
        </div>
        <div class="roles-stat-card">
            <div class="flex items-start justify-between gap-4">
                <div>
                    <p class="text-xs uppercase tracking-[0.14em]"><?= esc($tr('systemRoles')) ?></p>
                    <p class="mt-3 v3-title"><?= $systemRoles ?></p>
                </div>
                <div class="p-3"><i class="fas fa-shield-alt text-xl"></i></div>
            </div>
        </div>
        <div class="roles-stat-card">
            <div class="flex items-start justify-between gap-4">
                <div>
                    <p class="text-xs uppercase tracking-[0.14em] text-fuchsia-100/80"><?= esc($tr('totalPermissions')) ?></p>
                    <p class="mt-3 v3-title"><?= $totalPermissions ?></p>
                    <p class="mt-1 text-xs font-bold text-fuchsia-100/80"><?= esc($tr('usersCount', ['count' => $totalAssignedUsers])) ?></p>
                </div>
                <div class="p-3"><i class="fas fa-key text-xl"></i></div>
            </div>
        </div>
    </section>

    <section class="mb-5 flex flex-col gap-2 border px-5 py-4 md:flex-row md:items-center md:justify-between">
        <div>
            <h2 class="text-lg"><?= esc($tr('roleManagement')) ?></h2>
            <p class="mt-1 text-sm font-semibold"><?= esc($tr('manageSystemRolesPermissions')) ?></p>
        </div>
        <div class="flex flex-wrap gap-2 text-xs">
            <span class="px-3 py-1.5 ring-1"><?= esc($tr('systemRoles')) ?>: <?= $systemRoles ?></span>
            <span class="bg-emerald-50 px-3 py-1.5 text-emerald-700 ring-1 ring-emerald-100"><?= esc($tr('activeRoles')) ?>: <?= $activeRoles ?></span>
        </div>
    </section>

    <!-- Roles Grid -->
    <div class="grid grid-cols-1 gap-4 xl:grid-cols-3">
        <?php foreach ($roles as $role): ?>
            <?php
                $isSystemRole = (int) ($role['is_system'] ?? 0) === 1 || $role['clinic_id'] === null;
                $isActiveRole = (int) ($role['is_active'] ?? 0) === 1;
                $roleUserCount = (int) ($role['user_count'] ?? 0);
                $rolePermissionCount = (int) ($role['permission_count'] ?? 0);
                $isEditable = false;
                if ($global_mode) {
                    if ($role['clinic_id'] === null) $isEditable = true;
                } else {
                    if ($role['clinic_id'] !== null) $isEditable = true;
                }
                if ($role['slug'] === 'super_admin') $isEditable = false;
            ?>
            <article class="roles-card">
                <div class="roles-card-header <?= $isSystemRole ? 'roles-card-header-system' : 'roles-card-header-clinic' ?>">
                    <div class="flex min-w-0 items-start gap-3">
                        <div class="flex h-11 w-11 shrink-0 items-center justify-center border">
                            <i class="fas <?= $isSystemRole ? 'fa-shield-alt' : 'fa-user-tag' ?> text-lg"></i>
                        </div>
                        <div class="min-w-0">
                            <h3 class="truncate text-lg"><?= esc($role['name']) ?></h3>
                            <p class="mt-1 truncate text-xs font-bold" dir="ltr"><?= esc($role['slug']) ?></p>
                        </div>
                    </div>
                    <div class="roles-status-strip">
                        <div class="roles-status-item">
                            <span class="roles-status-label"><?= esc($tr('active')) ?></span>
                            <span class="roles-status-value">
                                <span class="h-2 w-2 <?= $isActiveRole ? 'bg-emerald-200' : 'bg-rose-200' ?>"></span>
                                <?= $isActiveRole ? esc($tr('active')) : esc($tr('inactive')) ?>
                            </span>
                        </div>
                        <div class="roles-status-item">
                            <span class="roles-status-label"><?= esc($tr('system')) ?></span>
                            <span class="roles-status-value">
                                <i class="fas <?= $isSystemRole ? 'fa-shield-alt' : 'fa-user-tag' ?>"></i>
                                <span class="truncate"><?= $isSystemRole ? esc($tr('system')) : esc($tr('role')) ?></span>
                            </span>
                        </div>
                        <div class="roles-status-item">
                            <span class="roles-status-label"><?= esc($tr('medical')) ?></span>
                            <span class="roles-status-value">
                                <i class="fas <?= (isset($role['is_medical']) && $role['is_medical']) ? 'fa-stethoscope' : 'fa-minus' ?>"></i>
                                <span class="truncate"><?= (isset($role['is_medical']) && $role['is_medical']) ? esc($tr('medical')) : '-' ?></span>
                            </span>
                        </div>
                    </div>
                </div>

                <div class="roles-card-body">
                    <p class="roles-card-description min-h-[3.4rem] text-sm font-semibold leading-5">
                        <?= esc($role['description']) ?>
                    </p>

                    <div class="mt-3 grid grid-cols-2 gap-2.5">
                        <div class="roles-metric">
                            <div class="flex items-center justify-between gap-3">
                                <p class="text-[11px] uppercase tracking-[0.12em]"><?= esc($tr('users')) ?></p>
                                <i class="fas fa-users"></i>
                            </div>
                            <p class="mt-1 text-xl"><?= $roleUserCount ?></p>
                        </div>
                        <div class="roles-metric">
                            <div class="flex items-center justify-between gap-3">
                                <p class="text-[11px] uppercase tracking-[0.12em]"><?= esc($tr('permissions')) ?></p>
                                <i class="fas fa-key text-teal-500"></i>
                            </div>
                            <p class="mt-1 text-xl"><?= $rolePermissionCount ?></p>
                        </div>
                    </div>

                    <div class="mt-auto flex flex-col gap-2.5 border-t pt-3 sm:flex-row sm:items-center sm:justify-between xl:flex-col xl:items-stretch 2xl:flex-row 2xl:items-center">
                        <div class="flex flex-wrap gap-2">
                            <?php if ($isEditable): ?>
                                <?php if (has_permission('users', 'edit')): ?>
                                    <a href="<?= workflow_context_url($rolesRoute . '/' . $role['id'] . '/edit') ?>" class="roles-action">
                                        <i class="fas fa-edit"></i>
                                        <?= esc($tr('edit')) ?>
                                    </a>
                                <?php endif; ?>
                                <?php if (has_permission('users', 'delete')): ?>
                                    <button onclick="deleteRole(<?= $role['id'] ?>)" class="roles-action bg-rose-50 text-rose-700 ring-1 ring-rose-200 hover:bg-rose-100">
                                        <i class="fas fa-trash"></i>
                                        <?= esc($tr('delete')) ?>
                                    </button>
                                <?php endif; ?>
                            <?php else: ?>
                                <span class="roles-action ring-1">
                                    <i class="fas fa-lock"></i>
                                    <?= $isSystemRole ? esc($tr('systemRoles')) : esc($tr('readOnly')) ?>
                                </span>
                            <?php endif; ?>
                        </div>

                        <?php if ($isEditable && has_permission('users', 'edit')): ?>
                            <button onclick="toggleRoleStatus(<?= $role['id'] ?>, <?= $isActiveRole ? 0 : 1 ?>)" class="roles-action <?= $isActiveRole ? 'bg-amber-50 text-amber-800 ring-1 ring-amber-200 hover:bg-amber-100' : 'bg-emerald-50 text-emerald-800 ring-1 ring-emerald-200 hover:bg-emerald-100' ?>">
                                <i class="fas <?= $isActiveRole ? 'fa-pause' : 'fa-play' ?>"></i>
                                <?= $isActiveRole ? esc($tr('deactivate')) : esc($tr('activate')) ?>
                            </button>
                        <?php endif; ?>
                    </div>
                </div>
            </article>
        <?php endforeach; ?>
    </div>

    <!-- Empty State -->
    <?php if (empty($roles)): ?>
        <div class="rounded-[2rem] border border-dashed px-6 py-14 text-center">
            <div class="mx-auto mb-4 flex h-20 w-20 items-center justify-center">
                <i class="fas fa-user-tag v3-title"></i>
            </div>
            <h3 class="text-xl"><?= esc($tr('noRolesFound')) ?></h3>
            <p class="mx-auto mt-2 max-w-md text-sm font-semibold"><?= esc($tr('getStartedCreateFirstRole')) ?></p>
            <a href="<?= workflow_context_url($rolesRoute . '/create') ?>" class="mt-6 inline-flex items-center justify-center gap-2 px-5 py-3 text-sm transition">
                <i class="fas fa-plus"></i><?= esc($tr('createRole')) ?>
            </a>
        </div>
    <?php endif; ?>
    </div>
</div>

<!-- JavaScript for Role Management -->
<script>
function deleteRole(roleId) {
    if (confirm('<?= esc($tr('confirmDeleteRole'), 'js') ?>')) {
        fetch(`<?= base_url($rolesRoute) ?>/${roleId}`, {
            method: 'DELETE',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Content-Type': 'application/json',
                '<?= csrf_header() ?>': '<?= csrf_hash() ?>'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.csrf_token) {
                // Update global token variables if you store them, or just rely on reload
            }
            if (data.success) {
                location.reload();
            } else {
                alert('<?= esc($tr('errorPrefix'), 'js') ?>' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('<?= esc($tr('errorDeletingRole'), 'js') ?>');
        });
    }
}

function toggleRoleStatus(roleId, newStatus) {
    const action = newStatus ? '<?= esc($tr('activate'), 'js') ?>' : '<?= esc($tr('deactivate'), 'js') ?>';
    if (confirm(`<?= esc($tr('confirmToggleRoleStatus', ['action' => '{action}']), 'js') ?>`.replace('{action}', action))) {
        fetch(`<?= base_url($rolesRoute) ?>/${roleId}/toggle-status`, {
            method: 'POST',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Content-Type': 'application/json',
                '<?= csrf_header() ?>': '<?= csrf_hash() ?>'
            },
            body: JSON.stringify({ status: newStatus })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert('<?= esc($tr('errorPrefix'), 'js') ?>' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('<?= esc($tr('errorUpdatingRoleStatus'), 'js') ?>');
        });
    }
}
</script>

</div>
<?= $this->endSection() ?>
