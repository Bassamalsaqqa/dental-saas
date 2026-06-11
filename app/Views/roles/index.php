<?php $layout = session()->get('global_mode') ? 'layouts/main_control_plane' : 'layouts/main_auth'; ?>
<?= $this->extend($layout) ?>

<?= $this->section('content') ?>
<?php
    $tr = static fn(string $key, array $params = []) => lang('UserRole.' . $key, $params);
    $totalRoles = count($roles);
    $activeRoles = count(array_filter($roles, static fn($role) => (int) ($role['is_active'] ?? 0) === 1));
    $systemRoles = count(array_filter($roles, static fn($role) => (int) ($role['is_system'] ?? 0) === 1));
    $totalPermissions = isset($permissions) ? count($permissions) : 0;
    $totalAssignedUsers = array_sum(array_map(static fn($role) => (int) ($role['user_count'] ?? 0), $roles));
    $contextLabel = $global_mode ? lang('UI.cpControlPlane') : lang('UI.menuSettings');
?>
<style>
    .roles-workspace {
        background:
            radial-gradient(circle at 92% 0%, rgba(79, 70, 229, 0.18), transparent 24rem),
            radial-gradient(circle at 8% 10%, rgba(14, 165, 233, 0.16), transparent 22rem),
            linear-gradient(135deg, #f8fafc 0%, #eef6ff 46%, #f8fafc 100%);
    }
    .roles-hero {
        position: relative;
        overflow: hidden;
        border-radius: 2rem;
        background:
            radial-gradient(circle at 96% 0%, rgba(56, 189, 248, 0.44), transparent 15rem),
            radial-gradient(circle at 12% 0%, rgba(168, 85, 247, 0.42), transparent 18rem),
            linear-gradient(135deg, #111827 0%, #3730a3 52%, #0f766e 100%);
        box-shadow: 0 30px 75px -44px rgba(15, 23, 42, 0.75);
    }
    .roles-hero::before {
        content: '';
        position: absolute;
        inset: 0;
        background:
            linear-gradient(120deg, rgba(255,255,255,0.18), transparent 38%),
            repeating-linear-gradient(135deg, rgba(255,255,255,0.08) 0 1px, transparent 1px 20px);
        pointer-events: none;
    }
    .roles-hero > * {
        position: relative;
        z-index: 1;
    }
    .roles-hero-chip {
        border: 1px solid rgba(255,255,255,0.2);
        background: rgba(255,255,255,0.14);
        box-shadow: inset 0 1px 0 rgba(255,255,255,0.18);
        backdrop-filter: blur(14px);
    }
    .roles-stat-card {
        position: relative;
        overflow: hidden;
        min-height: 9rem;
        border-radius: 1.5rem;
        box-shadow: 0 24px 58px -42px rgba(15, 23, 42, 0.62);
    }
    .roles-stat-card::after {
        content: '';
        position: absolute;
        inset: auto -2.5rem -3rem auto;
        width: 10rem;
        height: 10rem;
        border-radius: 9999px;
        background: rgba(255,255,255,0.16);
    }
    .roles-stat-card > * {
        position: relative;
        z-index: 1;
    }
    .roles-card {
        position: relative;
        overflow: hidden;
        border-radius: 1.75rem;
        border: 1px solid rgba(203, 213, 225, 0.72);
        background:
            radial-gradient(circle at 100% 0%, rgba(59, 130, 246, 0.12), transparent 10rem),
            linear-gradient(180deg, rgba(255,255,255,0.98), rgba(248,250,252,0.94));
        box-shadow: 0 24px 62px -48px rgba(15, 23, 42, 0.62);
        transition: transform 0.2s ease, box-shadow 0.2s ease, border-color 0.2s ease;
    }
    .roles-card:hover {
        transform: translateY(-2px);
        border-color: rgba(99, 102, 241, 0.28);
        box-shadow: 0 30px 70px -46px rgba(67, 56, 202, 0.58);
    }
    .roles-card-system {
        background:
            radial-gradient(circle at 100% 0%, rgba(37, 99, 235, 0.16), transparent 10rem),
            linear-gradient(180deg, #ffffff, #eff6ff);
    }
    .roles-card-clinic {
        background:
            radial-gradient(circle at 100% 0%, rgba(20, 184, 166, 0.18), transparent 10rem),
            linear-gradient(180deg, #ffffff, #ecfdf5);
    }
    .roles-pill {
        display: inline-flex;
        align-items: center;
        gap: 0.35rem;
        border-radius: 9999px;
        padding: 0.35rem 0.65rem;
        font-size: 0.72rem;
        font-weight: 800;
        line-height: 1;
        white-space: nowrap;
    }
    .roles-action {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 0.4rem;
        border-radius: 0.85rem;
        padding: 0.55rem 0.8rem;
        font-size: 0.75rem;
        font-weight: 800;
        transition: all 0.2s ease;
    }
    [dir="rtl"] .roles-hero,
    [dir="rtl"] .roles-card,
    [dir="rtl"] .roles-stat-card {
        text-align: right;
    }
    [dir="rtl"] .roles-workspace .mr-1,
    [dir="rtl"] .roles-workspace .mr-2 {
        margin-right: 0;
        margin-left: 0.5rem;
    }
    [dir="rtl"] .roles-workspace .ml-3 {
        margin-left: 0;
        margin-right: 0.75rem;
    }
</style>

<div class="roles-workspace min-h-screen">
    <div class="mx-auto max-w-7xl px-6 py-8">
        <section class="roles-hero mb-6 p-5 text-white sm:p-6 lg:p-7">
            <div class="flex flex-col gap-6 xl:flex-row xl:items-center xl:justify-between">
                <div class="flex items-start gap-4">
                    <div class="flex h-14 w-14 shrink-0 items-center justify-center rounded-2xl border border-white/25 bg-white/15 text-white shadow-xl shadow-black/10">
                        <i class="fas fa-user-shield text-2xl"></i>
                    </div>
                    <div class="min-w-0">
                        <p class="text-xs font-black uppercase tracking-[0.18em] text-cyan-100/90"><?= esc($contextLabel) ?></p>
                        <h1 class="mt-1 text-3xl font-black tracking-tight text-white sm:text-4xl"><?= esc($tr('roleManagement')) ?></h1>
                        <p class="mt-2 max-w-2xl text-sm font-semibold leading-6 text-blue-50/90"><?= esc($tr('manageSystemRolesPermissions')) ?></p>
                    </div>
                </div>
                <div class="grid grid-cols-1 gap-3 sm:grid-cols-3 xl:min-w-[31rem]">
                    <div class="roles-hero-chip rounded-2xl px-4 py-3">
                        <p class="text-[11px] font-black uppercase tracking-[0.14em] text-blue-100/80"><?= esc($tr('totalRoles')) ?></p>
                        <p class="mt-1 text-2xl font-black text-white"><?= $totalRoles ?></p>
                    </div>
                    <div class="roles-hero-chip rounded-2xl px-4 py-3">
                        <p class="text-[11px] font-black uppercase tracking-[0.14em] text-blue-100/80"><?= esc($tr('activeRoles')) ?></p>
                        <p class="mt-1 text-2xl font-black text-white"><?= $activeRoles ?></p>
                    </div>
                    <div class="roles-hero-chip rounded-2xl px-4 py-3">
                        <p class="text-[11px] font-black uppercase tracking-[0.14em] text-blue-100/80"><?= esc($tr('totalPermissions')) ?></p>
                        <p class="mt-1 text-2xl font-black text-white"><?= $totalPermissions ?></p>
                    </div>
                </div>
            </div>
            <div class="mt-6 flex flex-wrap gap-3">
                <?php if ($global_mode): ?>
                    <?php if (has_permission('settings', 'edit')): ?>
                        <a href="<?= base_url($rolesRoute . '/sync') ?>" class="inline-flex items-center justify-center gap-2 rounded-xl border border-white/25 bg-white/15 px-4 py-2.5 text-sm font-black text-white shadow-lg shadow-black/10 transition hover:bg-white/25">
                            <i class="fas fa-sync-alt mr-2"></i><?= esc($tr('syncPermissions')) ?>
                        </a>
                    <?php endif; ?>
                <?php endif; ?>
                
                <?php if (has_permission('users', 'create') || has_permission('users', 'roles')): ?>
                    <a href="<?= workflow_context_url($rolesRoute . '/create') ?>" class="inline-flex items-center justify-center gap-2 rounded-xl bg-white px-4 py-2.5 text-sm font-black text-indigo-700 shadow-xl shadow-black/10 transition hover:-translate-y-0.5 hover:bg-blue-50">
                        <i class="fas fa-plus mr-2"></i><?= esc($tr('createRole')) ?>
                    </a>
                <?php endif; ?>
            </div>
        </section>

    <!-- RBAC Status -->
    <?php if (isset($diagnostics) && !$diagnostics['rbac_ready']): ?>
        <div class="mb-6 bg-yellow-50 border border-yellow-200 rounded-xl p-4 shadow-sm">
            <div class="flex">
                <div class="flex-shrink-0">
                    <i class="fas fa-exclamation-triangle text-yellow-400 text-xl"></i>
                </div>
                <div class="ml-3">
                    <h3 class="text-sm font-medium text-yellow-800"><?= esc($tr('rbacSystemNotReady')) ?></h3>
                    <div class="mt-2 text-sm text-yellow-700">
                        <p><?= esc($tr('rbacNotInitializedUsers')) ?></p>
                        <div class="mt-3">
                            <a href="<?= base_url('rbac/setup') ?>" class="inline-flex items-center px-3 py-2 bg-yellow-600 text-white text-sm font-medium rounded-lg hover:bg-yellow-700 transition-colors">
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
        <div class="mb-6 bg-green-50 border border-green-200 rounded-xl p-4 shadow-sm">
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
        <div class="mb-6 bg-red-50 border border-red-200 rounded-xl p-4 shadow-sm">
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
        <div class="roles-stat-card bg-gradient-to-br from-indigo-700 via-blue-700 to-cyan-600 p-5 text-white">
            <div class="flex items-start justify-between gap-4">
                <div>
                    <p class="text-xs font-black uppercase tracking-[0.14em] text-blue-100/80"><?= esc($tr('totalRoles')) ?></p>
                    <p class="mt-3 text-4xl font-black"><?= $totalRoles ?></p>
                </div>
                <div class="rounded-2xl bg-white/20 p-3"><i class="fas fa-user-tag text-xl"></i></div>
            </div>
        </div>
        <div class="roles-stat-card bg-gradient-to-br from-emerald-700 via-teal-700 to-cyan-700 p-5 text-white">
            <div class="flex items-start justify-between gap-4">
                <div>
                    <p class="text-xs font-black uppercase tracking-[0.14em] text-emerald-100/80"><?= esc($tr('activeRoles')) ?></p>
                    <p class="mt-3 text-4xl font-black"><?= $activeRoles ?></p>
                </div>
                <div class="rounded-2xl bg-white/20 p-3"><i class="fas fa-check-circle text-xl"></i></div>
            </div>
        </div>
        <div class="roles-stat-card bg-gradient-to-br from-slate-900 via-slate-800 to-indigo-800 p-5 text-white">
            <div class="flex items-start justify-between gap-4">
                <div>
                    <p class="text-xs font-black uppercase tracking-[0.14em] text-slate-200/80"><?= esc($tr('systemRoles')) ?></p>
                    <p class="mt-3 text-4xl font-black"><?= $systemRoles ?></p>
                </div>
                <div class="rounded-2xl bg-white/20 p-3"><i class="fas fa-shield-alt text-xl"></i></div>
            </div>
        </div>
        <div class="roles-stat-card bg-gradient-to-br from-fuchsia-700 via-purple-700 to-indigo-700 p-5 text-white">
            <div class="flex items-start justify-between gap-4">
                <div>
                    <p class="text-xs font-black uppercase tracking-[0.14em] text-fuchsia-100/80"><?= esc($tr('totalPermissions')) ?></p>
                    <p class="mt-3 text-4xl font-black"><?= $totalPermissions ?></p>
                    <p class="mt-1 text-xs font-bold text-fuchsia-100/80"><?= esc($tr('usersCount', ['count' => $totalAssignedUsers])) ?></p>
                </div>
                <div class="rounded-2xl bg-white/20 p-3"><i class="fas fa-key text-xl"></i></div>
            </div>
        </div>
    </section>

    <section class="mb-5 flex flex-col gap-2 rounded-2xl border border-slate-200/80 bg-white/80 px-5 py-4 shadow-xl shadow-slate-900/5 backdrop-blur md:flex-row md:items-center md:justify-between">
        <div>
            <h2 class="text-lg font-black text-slate-950"><?= esc($tr('roleManagement')) ?></h2>
            <p class="mt-1 text-sm font-semibold text-slate-500"><?= esc($tr('manageSystemRolesPermissions')) ?></p>
        </div>
        <div class="flex flex-wrap gap-2 text-xs font-black text-slate-600">
            <span class="rounded-full bg-blue-50 px-3 py-1.5 text-blue-700 ring-1 ring-blue-100"><?= esc($tr('systemRoles')) ?>: <?= $systemRoles ?></span>
            <span class="rounded-full bg-emerald-50 px-3 py-1.5 text-emerald-700 ring-1 ring-emerald-100"><?= esc($tr('activeRoles')) ?>: <?= $activeRoles ?></span>
        </div>
    </section>

    <!-- Roles Grid -->
    <div class="grid grid-cols-1 gap-5 lg:grid-cols-2 2xl:grid-cols-3">
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
            <article class="roles-card <?= $isSystemRole ? 'roles-card-system' : 'roles-card-clinic' ?>">
                <div class="p-5">
                    <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
                        <div class="flex min-w-0 items-start gap-3">
                            <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl text-white shadow-lg <?= $isSystemRole ? 'bg-gradient-to-br from-indigo-700 to-blue-700 shadow-blue-500/20' : 'bg-gradient-to-br from-teal-600 to-emerald-700 shadow-emerald-500/20' ?>">
                                <i class="fas <?= $isSystemRole ? 'fa-shield-alt' : 'fa-user-tag' ?> text-lg"></i>
                            </div>
                            <div class="min-w-0">
                                <h3 class="truncate text-lg font-black text-slate-950"><?= esc($role['name']) ?></h3>
                                <p class="mt-1 truncate text-xs font-bold text-slate-500" dir="ltr"><?= esc($role['slug']) ?></p>
                            </div>
                        </div>
                        <div class="flex flex-wrap gap-2 sm:justify-end">
                            <span class="roles-pill <?= $isActiveRole ? 'bg-emerald-100 text-emerald-800 ring-1 ring-emerald-200' : 'bg-rose-100 text-rose-800 ring-1 ring-rose-200' ?>">
                                <span class="h-1.5 w-1.5 rounded-full <?= $isActiveRole ? 'bg-emerald-500' : 'bg-rose-500' ?>"></span>
                                <?= $isActiveRole ? esc($tr('active')) : esc($tr('inactive')) ?>
                            </span>
                            <?php if ($isSystemRole): ?>
                                <span class="roles-pill bg-blue-100 text-blue-800 ring-1 ring-blue-200"><i class="fas fa-shield-alt"></i><?= esc($tr('system')) ?></span>
                            <?php endif; ?>
                            <?php if (isset($role['is_medical']) && $role['is_medical']): ?>
                                <span class="roles-pill bg-teal-100 text-teal-800 ring-1 ring-teal-200"><i class="fas fa-stethoscope"></i><?= esc($tr('medical')) ?></span>
                            <?php endif; ?>
                        </div>
                    </div>

                    <p class="mt-4 min-h-[2.5rem] text-sm font-semibold leading-6 text-slate-600">
                        <?= esc($role['description']) ?>
                    </p>

                    <div class="mt-5 grid grid-cols-2 gap-3">
                        <div class="rounded-2xl bg-white/80 px-4 py-3 shadow-sm ring-1 ring-slate-200/80">
                            <p class="text-[11px] font-black uppercase tracking-[0.12em] text-slate-500"><?= esc($tr('users')) ?></p>
                            <p class="mt-1 text-xl font-black text-slate-950"><?= $roleUserCount ?></p>
                        </div>
                        <div class="rounded-2xl bg-white/80 px-4 py-3 shadow-sm ring-1 ring-slate-200/80">
                            <p class="text-[11px] font-black uppercase tracking-[0.12em] text-slate-500"><?= esc($tr('permissions')) ?></p>
                            <p class="mt-1 text-xl font-black text-slate-950"><?= $rolePermissionCount ?></p>
                        </div>
                    </div>

                    <div class="mt-5 flex flex-col gap-3 border-t border-slate-200/80 pt-4 sm:flex-row sm:items-center sm:justify-between">
                        <div class="flex flex-wrap gap-2">
                            <?php if ($isEditable): ?>
                                <?php if (has_permission('users', 'edit')): ?>
                                    <a href="<?= workflow_context_url($rolesRoute . '/' . $role['id'] . '/edit') ?>" class="roles-action bg-blue-600 text-white shadow-lg shadow-blue-500/20 hover:bg-blue-700">
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
                                <span class="roles-action bg-slate-100 text-slate-500 ring-1 ring-slate-200">
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
        <div class="rounded-[2rem] border border-dashed border-slate-300 bg-white/80 px-6 py-14 text-center shadow-xl shadow-slate-900/5">
            <div class="mx-auto mb-4 flex h-20 w-20 items-center justify-center rounded-3xl bg-gradient-to-br from-indigo-600 to-blue-700 text-white shadow-lg shadow-blue-500/25">
                <i class="fas fa-user-tag text-3xl"></i>
            </div>
            <h3 class="text-xl font-black text-slate-950"><?= esc($tr('noRolesFound')) ?></h3>
            <p class="mx-auto mt-2 max-w-md text-sm font-semibold text-slate-500"><?= esc($tr('getStartedCreateFirstRole')) ?></p>
            <a href="<?= workflow_context_url($rolesRoute . '/create') ?>" class="mt-6 inline-flex items-center justify-center gap-2 rounded-xl bg-blue-600 px-5 py-3 text-sm font-black text-white shadow-lg shadow-blue-500/20 transition hover:bg-blue-700">
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

<?= $this->endSection() ?>
