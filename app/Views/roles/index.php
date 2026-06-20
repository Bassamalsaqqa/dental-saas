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
        border-radius: 1.35rem;
        border: 1px solid rgba(148, 163, 184, 0.42);
        background: #ffffff;
        box-shadow: 0 22px 58px -42px rgba(15, 23, 42, 0.62);
        transition: transform 0.2s ease, box-shadow 0.2s ease, border-color 0.2s ease;
        display: flex;
        flex-direction: column;
        min-height: 18.5rem;
    }
    .roles-card:hover {
        transform: translateY(-2px);
        border-color: rgba(99, 102, 241, 0.28);
        box-shadow: 0 30px 70px -46px rgba(67, 56, 202, 0.58);
    }
    .roles-card-header {
        position: relative;
        overflow: hidden;
        padding: 1rem;
        color: #fff;
    }
    .roles-card-header::after {
        content: '';
        position: absolute;
        inset: 0;
        background:
            linear-gradient(120deg, rgba(255,255,255,0.16), transparent 42%),
            repeating-linear-gradient(135deg, rgba(255,255,255,0.08) 0 1px, transparent 1px 18px);
        pointer-events: none;
    }
    .roles-card-header > * {
        position: relative;
        z-index: 1;
    }
    .roles-card-header-system {
        background:
            radial-gradient(circle at 96% 0%, rgba(96, 165, 250, 0.44), transparent 10rem),
            linear-gradient(135deg, #1e1b4b 0%, #1d4ed8 62%, #0891b2 100%);
    }
    .roles-card-header-clinic {
        background:
            radial-gradient(circle at 96% 0%, rgba(45, 212, 191, 0.45), transparent 10rem),
            linear-gradient(135deg, #064e3b 0%, #0f766e 58%, #2563eb 100%);
    }
    .roles-card-body {
        flex: 1;
        display: flex;
        flex-direction: column;
        background:
            radial-gradient(circle at 100% 0%, rgba(59, 130, 246, 0.08), transparent 12rem),
            linear-gradient(180deg, #ffffff, #f8fafc);
        padding: 1rem;
    }
    .roles-status-strip {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 0.55rem;
        margin-top: 0.7rem;
    }
    .roles-status-item {
        border-radius: 0.95rem;
        background: rgba(255, 255, 255, 0.14);
        border: 1px solid rgba(255, 255, 255, 0.20);
        padding: 0.55rem 0.65rem;
        min-width: 0;
    }
    .roles-status-label {
        display: block;
        font-size: 0.62rem;
        font-weight: 900;
        letter-spacing: 0.12em;
        text-transform: uppercase;
        color: rgba(219, 234, 254, 0.78);
    }
    .roles-status-value {
        margin-top: 0.18rem;
        display: flex;
        align-items: center;
        gap: 0.35rem;
        min-width: 0;
        font-size: 0.78rem;
        font-weight: 900;
        color: #fff;
    }
    .roles-metric {
        border-radius: 1rem;
        border: 1px solid rgba(203, 213, 225, 0.78);
        background:
            linear-gradient(180deg, rgba(255,255,255,0.96), rgba(241,245,249,0.86));
        box-shadow: inset 0 1px 0 rgba(255,255,255,0.9), 0 14px 32px -28px rgba(15,23,42,0.35);
        padding: 0.7rem 0.85rem;
    }
    .roles-card-description {
        border-radius: 1rem;
        background: rgba(255,255,255,0.74);
        border: 1px solid rgba(226,232,240,0.82);
        padding: 0.7rem 0.85rem;
    }
    .roles-pill {
        display: inline-flex;
        align-items: center;
        gap: 0.35rem;
        border-radius: 9999px;
        padding: 0.3rem 0.55rem;
        font-size: 0.68rem;
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
        padding: 0.5rem 0.7rem;
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
                        <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl border border-white/25 bg-white/20 text-white shadow-lg shadow-black/10">
                            <i class="fas <?= $isSystemRole ? 'fa-shield-alt' : 'fa-user-tag' ?> text-lg"></i>
                        </div>
                        <div class="min-w-0">
                            <h3 class="truncate text-lg font-black text-white"><?= esc($role['name']) ?></h3>
                            <p class="mt-1 truncate text-xs font-bold text-blue-50/80" dir="ltr"><?= esc($role['slug']) ?></p>
                        </div>
                    </div>
                    <div class="roles-status-strip">
                        <div class="roles-status-item">
                            <span class="roles-status-label"><?= esc($tr('active')) ?></span>
                            <span class="roles-status-value">
                                <span class="h-2 w-2 rounded-full <?= $isActiveRole ? 'bg-emerald-200' : 'bg-rose-200' ?>"></span>
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
                    <p class="roles-card-description min-h-[3.4rem] text-sm font-semibold leading-5 text-slate-700">
                        <?= esc($role['description']) ?>
                    </p>

                    <div class="mt-3 grid grid-cols-2 gap-2.5">
                        <div class="roles-metric">
                            <div class="flex items-center justify-between gap-3">
                                <p class="text-[11px] font-black uppercase tracking-[0.12em] text-slate-500"><?= esc($tr('users')) ?></p>
                                <i class="fas fa-users text-blue-500"></i>
                            </div>
                            <p class="mt-1 text-xl font-black text-slate-950"><?= $roleUserCount ?></p>
                        </div>
                        <div class="roles-metric">
                            <div class="flex items-center justify-between gap-3">
                                <p class="text-[11px] font-black uppercase tracking-[0.12em] text-slate-500"><?= esc($tr('permissions')) ?></p>
                                <i class="fas fa-key text-teal-500"></i>
                            </div>
                            <p class="mt-1 text-xl font-black text-slate-950"><?= $rolePermissionCount ?></p>
                        </div>
                    </div>

                    <div class="mt-auto flex flex-col gap-2.5 border-t border-slate-200/80 pt-3 sm:flex-row sm:items-center sm:justify-between xl:flex-col xl:items-stretch 2xl:flex-row 2xl:items-center">
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
