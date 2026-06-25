<?= $this->extend('layouts/main_auth') ?>

<?= $this->section('content') ?>
<?php
    $totalUsers = (int) ($total_users ?? 0);
    $activeUsers = (int) ($active_users ?? 0);
    $inactiveUsers = (int) ($inactive_users ?? 0);
    $assignedRoleRows = (int) ($diagnostics['total_user_roles'] ?? 0);
?>
<style>
    .users-hero {
        position: relative;
        overflow: hidden;
        border-radius: 2rem;
        background:
            radial-gradient(circle at 96% 0%, rgba(45, 212, 191, 0.42), transparent 15rem),
            radial-gradient(circle at 12% 0%, rgba(96, 165, 250, 0.42), transparent 18rem),
            linear-gradient(135deg, #0f172a 0%, #1d4ed8 56%, #0f766e 100%);
        box-shadow: 0 30px 75px -44px rgba(15, 23, 42, 0.72);
    }
    .users-hero::before {
        content: '';
        position: absolute;
        inset: 0;
        background:
            linear-gradient(120deg, rgba(255,255,255,0.18), transparent 40%),
            repeating-linear-gradient(135deg, rgba(255,255,255,0.08) 0 1px, transparent 1px 20px);
        pointer-events: none;
    }
    .users-hero > * { position: relative; z-index: 1; }
    .users-hero-chip {
        border: 1px solid rgba(255,255,255,0.2);
        background: rgba(255,255,255,0.14);
        box-shadow: inset 0 1px 0 rgba(255,255,255,0.18);
        backdrop-filter: blur(14px);
    }
    .users-stat-card {
        position: relative;
        overflow: hidden;
        min-height: 8.5rem;
        border-radius: 1.5rem;
        box-shadow: 0 24px 58px -42px rgba(15, 23, 42, 0.62);
    }
    .users-stat-card::after {
        content: '';
        position: absolute;
        inset: auto -2.5rem -3rem auto;
        width: 10rem;
        height: 10rem;
        border-radius: 9999px;
        background: rgba(255,255,255,0.16);
    }
    .users-stat-card > * { position: relative; z-index: 1; }
    .users-filter-panel,
    .users-section-panel {
        border: 1px solid rgba(203, 213, 225, 0.72);
        background:
            radial-gradient(circle at 100% 0%, rgba(59, 130, 246, 0.08), transparent 12rem),
            linear-gradient(180deg, rgba(255,255,255,0.96), rgba(248,250,252,0.92));
        box-shadow: 0 22px 58px -44px rgba(15, 23, 42, 0.55);
        backdrop-filter: blur(18px);
    }
    .user-card {
        position: relative;
        overflow: hidden;
        border-radius: 1.5rem;
        border: 1px solid rgba(203, 213, 225, 0.78);
        background:
            radial-gradient(circle at 100% 0%, rgba(37, 99, 235, 0.10), transparent 11rem),
            linear-gradient(180deg, #ffffff, #f8fafc);
        box-shadow: 0 22px 58px -44px rgba(15, 23, 42, 0.58);
        transition: transform 0.2s ease, box-shadow 0.2s ease, border-color 0.2s ease;
    }
    .user-card:hover {
        transform: translateY(-2px);
        border-color: rgba(37, 99, 235, 0.26);
        box-shadow: 0 30px 70px -48px rgba(37, 99, 235, 0.56);
    }
    .user-avatar {
        background:
            radial-gradient(circle at 30% 20%, rgba(255,255,255,0.32), transparent 35%),
            linear-gradient(135deg, #1d4ed8, #0f766e);
    }
    .user-info-tile {
        border-radius: 1rem;
        border: 1px solid rgba(226, 232, 240, 0.8);
        background: rgba(255,255,255,0.78);
        padding: 0.85rem 1rem;
    }
    .user-action-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 2.35rem;
        height: 2.35rem;
        border-radius: 0.9rem;
        transition: all 0.2s ease;
    }
    [dir="rtl"] .users-workspace .mr-2,
    [dir="rtl"] .users-workspace .mr-3 {
        margin-right: 0;
        margin-left: 0.5rem;
    }
    [dir="rtl"] .users-workspace .ml-3 {
        margin-left: 0;
        margin-right: 0.75rem;
    }
</style>

<div class="users-workspace min-h-screen">
    <div class="mx-auto max-w-7xl px-6 py-8">
        <section class="users-hero mb-6 p-5 text-white sm:p-6 lg:p-7">
            <div class="flex flex-col gap-6 xl:flex-row xl:items-center xl:justify-between">
                <div class="flex items-start gap-4">
                    <div class="flex h-14 w-14 shrink-0 items-center justify-center rounded-2xl border border-white/25 bg-white/15 text-white shadow-xl shadow-black/10">
                        <i class="fas fa-users-cog text-2xl"></i>
                    </div>
                    <div class="min-w-0">
                        <p class="text-xs font-black uppercase tracking-[0.18em] text-cyan-100/90"><?= lang('UI.menuSettings') ?></p>
                        <h1 class="mt-1 text-3xl font-black tracking-tight text-white sm:text-4xl"><?= lang('UserRole.userManagement') ?></h1>
                    </div>
                </div>
                <div class="grid grid-cols-1 gap-3 sm:grid-cols-3 xl:min-w-[31rem]">
                    <div class="users-hero-chip rounded-2xl px-4 py-3">
                        <p class="text-[11px] font-black uppercase tracking-[0.14em] text-blue-100/80"><?= lang('UserRole.totalUsers') ?></p>
                        <p class="mt-1 text-2xl font-black text-white"><?= $totalUsers ?></p>
                    </div>
                    <div class="users-hero-chip rounded-2xl px-4 py-3">
                        <p class="text-[11px] font-black uppercase tracking-[0.14em] text-blue-100/80"><?= lang('UserRole.activeUsers') ?></p>
                        <p class="mt-1 text-2xl font-black text-white"><?= $activeUsers ?></p>
                    </div>
                    <div class="users-hero-chip rounded-2xl px-4 py-3">
                        <p class="text-[11px] font-black uppercase tracking-[0.14em] text-blue-100/80"><?= lang('UserRole.roles') ?></p>
                        <p class="mt-1 text-2xl font-black text-white"><?= $assignedRoleRows ?></p>
                    </div>
                </div>
            </div>
            <div class="mt-6 flex flex-wrap gap-3">
                    <?php if (has_permission('settings', 'edit')): ?>
                        <a href="<?= base_url('rbac/setup') ?>" class="inline-flex items-center justify-center gap-2 rounded-xl border border-white/25 bg-white/15 px-4 py-2.5 text-sm font-black text-white shadow-lg shadow-black/10 transition hover:bg-white/25">
                            <i class="fas fa-cog mr-2"></i><?= lang('UserRole.rbacSetup') ?>
                        </a>
                    <?php endif; ?>
                    <?php if (has_permission('users', 'create')): ?>
                        <a href="<?= workflow_context_url('users/create') ?>" data-user-create-modal-open class="inline-flex items-center justify-center gap-2 rounded-xl bg-white px-4 py-2.5 text-sm font-black text-blue-700 shadow-xl shadow-black/10 transition hover:-translate-y-0.5 hover:bg-blue-50">
                            <i class="fas fa-plus mr-2"></i><?= lang('UserRole.createUser') ?>
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
                        <h3 class="text-sm font-medium text-yellow-800"><?= lang('UserRole.rbacSystemNotReady') ?></h3>
                        <div class="mt-2 text-sm text-yellow-700">
                            <p><?= lang('UserRole.rbacNotInitializedUsers') ?></p>
                            <div class="mt-3">
                                <a href="<?= base_url('rbac/setup') ?>" class="inline-flex items-center px-3 py-2 bg-yellow-600 text-white text-sm font-medium rounded-lg hover:bg-yellow-700 transition-colors">
                                    <i class="fas fa-cog mr-2"></i><?= lang('UserRole.initializeRbacSystem') ?>
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
                        <h3 class="text-sm font-medium text-green-800"><?= lang('UserRole.success') ?></h3>
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
                        <h3 class="text-sm font-medium text-red-800"><?= lang('UserRole.error') ?></h3>
                        <div class="mt-2 text-sm text-red-700">
                            <p><?= session()->getFlashdata('error') ?></p>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <!-- Error Message -->
        <?php if (isset($error)): ?>
            <div class="mb-6 bg-red-50 border border-red-200 rounded-xl p-4 shadow-sm">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <i class="fas fa-exclamation-triangle text-red-400 text-xl"></i>
                    </div>
                    <div class="ml-3">
                        <h3 class="text-sm font-medium text-red-800"><?= lang('UserRole.errorLoadingUsers') ?></h3>
                        <div class="mt-2 text-sm text-red-700">
                            <p><?= $error ?></p>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <section class="mb-7 grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-4">
            <div class="users-stat-card bg-gradient-to-br from-blue-700 via-indigo-700 to-cyan-600 p-5 text-white">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <p class="text-xs font-black uppercase tracking-[0.14em] text-blue-100/80"><?= lang('UserRole.totalUsers') ?></p>
                        <p class="mt-3 text-4xl font-black"><?= $totalUsers ?></p>
                    </div>
                    <div class="rounded-2xl bg-white/20 p-3"><i class="fas fa-users text-xl"></i></div>
                </div>
            </div>
            <div class="users-stat-card bg-gradient-to-br from-emerald-700 via-teal-700 to-cyan-700 p-5 text-white">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <p class="text-xs font-black uppercase tracking-[0.14em] text-emerald-100/80"><?= lang('UserRole.activeUsers') ?></p>
                        <p class="mt-3 text-4xl font-black"><?= $activeUsers ?></p>
                    </div>
                    <div class="rounded-2xl bg-white/20 p-3"><i class="fas fa-check-circle text-xl"></i></div>
                </div>
            </div>
            <div class="users-stat-card bg-gradient-to-br from-amber-600 via-orange-600 to-rose-600 p-5 text-white">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <p class="text-xs font-black uppercase tracking-[0.14em] text-amber-100/80"><?= lang('UserRole.inactiveUsers') ?></p>
                        <p class="mt-3 text-4xl font-black"><?= $inactiveUsers ?></p>
                    </div>
                    <div class="rounded-2xl bg-white/20 p-3"><i class="fas fa-user-slash text-xl"></i></div>
                </div>
            </div>
            <div class="users-stat-card bg-gradient-to-br from-fuchsia-700 via-purple-700 to-indigo-700 p-5 text-white">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <p class="text-xs font-black uppercase tracking-[0.14em] text-fuchsia-100/80"><?= lang('UserRole.adminUsers') ?></p>
                        <p class="mt-3 text-4xl font-black"><?= $assignedRoleRows ?></p>
                    </div>
                    <div class="rounded-2xl bg-white/20 p-3"><i class="fas fa-user-shield text-xl"></i></div>
                </div>
            </div>
        </section>

        <?php if (!empty($users)): ?>
            <section class="users-filter-panel mb-5 rounded-2xl p-5">
                <div class="mb-4 flex flex-col gap-2 md:flex-row md:items-center md:justify-between">
                    <div>
                        <h3 class="flex items-center text-lg font-black text-slate-950">
                            <i class="fas fa-search text-blue-600 <?= !empty($is_rtl) ? 'ml-2' : 'mr-2' ?>"></i>
                            <?= lang('UserRole.searchFilterUsers') ?>
                        </h3>
                        <p class="mt-1 text-sm font-semibold text-slate-500">
                            <?= lang('UserRole.showing') ?> <span id="filteredCount"><?= count($users) ?></span> <?= lang('UserRole.of') ?> <span id="totalCount"><?= count($users) ?></span> <?= lang('UserRole.users') ?>
                        </p>
                    </div>
                </div>
                <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
                    <div class="relative">
                        <input type="text" id="userSearchInput" placeholder="<?= lang('UserRole.searchUsers') ?>" class="w-full rounded-xl border-2 border-slate-200 bg-white px-4 py-3 <?= !empty($is_rtl) ? 'pr-10 text-right' : 'pl-10' ?> font-semibold text-slate-700 shadow-sm transition focus:border-blue-500 focus:outline-none focus:ring-4 focus:ring-blue-500/10">
                        <i class="fas fa-search absolute top-1/2 -translate-y-1/2 text-slate-400 <?= !empty($is_rtl) ? 'right-3' : 'left-3' ?>"></i>
                    </div>
                    <select id="groupFilter" class="w-full rounded-xl border-2 border-slate-200 bg-white px-4 py-3 font-semibold text-slate-700 shadow-sm transition focus:border-blue-500 focus:outline-none focus:ring-4 focus:ring-blue-500/10">
                        <option value=""><?= lang('UserRole.allRoles') ?></option>
                        <?php if (!empty($roles)): ?>
                            <?php foreach ($roles as $role): ?>
                                <option value="<?= strtolower($role['name']) ?>"><?= esc(ucfirst($role['name'])) ?></option>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <option value="super admin"><?= lang('UserRole.superAdmin') ?></option>
                            <option value="doctor"><?= lang('UserRole.doctor') ?></option>
                            <option value="receptionist"><?= lang('UserRole.receptionist') ?></option>
                        <?php endif; ?>
                    </select>
                    <select id="statusFilter" class="w-full rounded-xl border-2 border-slate-200 bg-white px-4 py-3 font-semibold text-slate-700 shadow-sm transition focus:border-blue-500 focus:outline-none focus:ring-4 focus:ring-blue-500/10">
                        <option value=""><?= lang('UserRole.allStatus') ?></option>
                        <option value="1"><?= lang('UserRole.active') ?></option>
                        <option value="0"><?= lang('UserRole.inactive') ?></option>
                    </select>
                </div>
            </section>
        <?php endif; ?>

        <!-- Users Grid -->
        <div class="grid grid-cols-1 gap-4 xl:grid-cols-2">
            <?php if (!empty($users)): ?>
                <?php foreach ($users as $user): ?>
                    <?php
                        $userName = trim((string) (($user->first_name ?? '') . ' ' . ($user->last_name ?? '')));
                        $userName = $userName !== '' ? $userName : (string) ($user->email ?? lang('UserRole.unknownRole'));
                        $userStatusText = $user->active == 1 ? lang('UserRole.active') : lang('UserRole.inactive');
                        $userStatusClass = $user->active == 1 ? 'bg-emerald-100 text-emerald-800 ring-1 ring-emerald-200' : 'bg-slate-100 text-slate-700 ring-1 ring-slate-200';
                        $roleNames = [];
                        if (!empty($user->display_roles)) {
                            foreach ($user->display_roles as $role) {
                                $roleNames[] = is_array($role)
                                    ? ($role['name'] ?? lang('UserRole.unknownRole'))
                                    : ($role->name ?? lang('UserRole.unknownRole'));
                            }
                        }
                    ?>
                    <article class="user-card group" data-user-card data-user-name="<?= esc(strtolower($userName), 'attr') ?>" data-user-email="<?= esc(strtolower((string) $user->email), 'attr') ?>" data-user-status="<?= (int) $user->active ?>" data-user-roles="<?= esc(strtolower(implode(' ', $roleNames)), 'attr') ?>">
                        <div class="p-5">
                            <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
                                <div class="flex min-w-0 items-start gap-4">
                                    <div class="user-avatar flex h-14 w-14 shrink-0 items-center justify-center rounded-2xl text-lg font-black text-white shadow-lg shadow-blue-500/20 transition group-hover:scale-105">
                                        <?= esc(ui_initials($user->first_name ?? '', $user->last_name ?? '', 'U')) ?>
                                    </div>
                                    <div class="min-w-0">
                                        <h3 class="truncate text-lg font-black text-slate-950"><?= esc($userName) ?></h3>
                                        <p class="mt-1 truncate text-sm font-bold text-slate-500" dir="ltr"><?= esc($user->email) ?></p>
                                        <span class="mt-3 inline-flex items-center gap-2 rounded-full px-3 py-1 text-xs font-black <?= $userStatusClass ?>">
                                            <span class="h-1.5 w-1.5 rounded-full <?= $user->active == 1 ? 'bg-emerald-500' : 'bg-slate-500' ?>"></span>
                                            <?= esc($userStatusText) ?>
                                        </span>
                                    </div>
                                </div>
                                <div class="flex flex-wrap gap-2 sm:justify-end">
                                        <?php if (has_permission('users', 'view')): ?>
                                            <a href="<?= workflow_context_url('users/' . $user->id) ?>" class="user-action-btn bg-blue-50 text-blue-700 ring-1 ring-blue-100 hover:bg-blue-100" title="<?= lang('UserRole.viewUser') ?>">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                        <?php endif; ?>
                                        <?php if (has_permission('users', 'edit')): ?>
                                            <a href="<?= workflow_context_url('users/' . $user->id . '/edit') ?>" class="user-action-btn bg-amber-50 text-amber-700 ring-1 ring-amber-100 hover:bg-amber-100" title="<?= lang('UserRole.editUser') ?>">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <a href="<?= workflow_context_url('users/' . $user->id . '/change-password') ?>" class="user-action-btn bg-emerald-50 text-emerald-700 ring-1 ring-emerald-100 hover:bg-emerald-100" title="<?= lang('UserRole.changePassword') ?>">
                                                <i class="fas fa-key"></i>
                                            </a>
                                            <button onclick="toggleStatus(<?= $user->id ?>)" class="user-action-btn bg-purple-50 text-purple-700 ring-1 ring-purple-100 hover:bg-purple-100" title="<?= lang('UserRole.toggleStatus') ?>">
                                                <i class="fas fa-toggle-on"></i>
                                            </button>
                                        <?php endif; ?>
                                        <?php if (has_permission('users', 'delete') && $user->id != 1): ?>
                                            <button onclick="deleteUser(<?= $user->id ?>)" class="user-action-btn bg-rose-50 text-rose-700 ring-1 ring-rose-100 hover:bg-rose-100" title="<?= lang('UserRole.deleteUser') ?>">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        <?php endif; ?>
                                </div>
                            </div>

                            <div class="mt-5 grid grid-cols-1 gap-3 md:grid-cols-3">
                                <div class="user-info-tile md:col-span-1">
                                    <h4 class="mb-3 text-xs font-black uppercase tracking-[0.12em] text-slate-500"><?= lang('UserRole.rolesAndPermissions') ?></h4>
                                    <?php if (!empty($user->display_roles)): ?>
                                        <div class="flex flex-wrap gap-2">
                                            <?php foreach ($user->display_roles as $role): ?>
                                                <span class="inline-flex items-center rounded-full bg-teal-100 px-2.5 py-1 text-xs font-black text-teal-800 ring-1 ring-teal-200">
                                                    <i class="fas fa-user-tag mr-2"></i>
                                                    <?php 
                                                    // Handle both array and object formats
                                                    $roleName = '';
                                                    if (is_array($role)) {
                                                        $roleName = $role['name'] ?? lang('UserRole.unknownRole');
                                                    } else {
                                                        $roleName = $role->name ?? lang('UserRole.unknownRole');
                                                    }
                                                    echo esc(ucfirst($roleName));
                                                    ?>
                                                </span>
                                            <?php endforeach; ?>
                                        </div>
                                    <?php else: ?>
                                        <p class="text-sm font-semibold text-slate-500"><?= lang('UserRole.noRolesAssigned') ?></p>
                                    <?php endif; ?>
                                </div>

                                <div class="user-info-tile">
                                    <h4 class="mb-3 text-xs font-black uppercase tracking-[0.12em] text-slate-500"><?= lang('UserRole.contactInformation') ?></h4>
                                    <div class="space-y-2">
                                        <div class="flex min-w-0 items-center text-sm font-semibold text-slate-700">
                                            <i class="fas fa-phone w-4 text-blue-500 <?= !empty($is_rtl) ? 'ml-2' : 'mr-2' ?>"></i>
                                            <span class="truncate" dir="ltr"><?= esc($user->phone ?? lang('UserRole.notProvided')) ?></span>
                                        </div>
                                        <div class="flex min-w-0 items-center text-sm font-semibold text-slate-700">
                                            <i class="fas fa-envelope w-4 text-teal-500 <?= !empty($is_rtl) ? 'ml-2' : 'mr-2' ?>"></i>
                                            <span class="truncate" dir="ltr"><?= esc($user->email) ?></span>
                                        </div>
                                    </div>
                                </div>

                                <div class="user-info-tile">
                                    <h4 class="mb-3 text-xs font-black uppercase tracking-[0.12em] text-slate-500"><?= lang('UserRole.activity') ?></h4>
                                    <div class="space-y-2">
                                        <div class="flex items-center text-sm font-semibold text-slate-700">
                                            <i class="fas fa-calendar w-4 text-indigo-500 <?= !empty($is_rtl) ? 'ml-2' : 'mr-2' ?>"></i>
                                            <span><?= lang('UserRole.createdColon') ?> <?= $user->created_on ? date('M j, Y', strtotime($user->created_on)) : lang('UserRole.na') ?></span>
                                        </div>
                                        <div class="flex items-center text-sm font-semibold text-slate-700">
                                            <i class="fas fa-clock w-4 text-purple-500 <?= !empty($is_rtl) ? 'ml-2' : 'mr-2' ?>"></i>
                                            <span><?= lang('UserRole.lastLoginColon') ?> <?= $user->last_login ? date('M j, Y g:i A', strtotime($user->last_login)) : lang('UserRole.never') ?></span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </article>
                <?php endforeach; ?>
            <?php else: ?>
                <!-- Empty State -->
                <div class="users-section-panel rounded-[2rem] border border-dashed border-slate-300 px-6 py-14 text-center xl:col-span-2">
                    <div class="mx-auto mb-5 flex h-20 w-20 items-center justify-center rounded-3xl bg-gradient-to-br from-blue-600 to-teal-700 text-white shadow-lg shadow-blue-500/25">
                        <i class="fas fa-users text-3xl"></i>
                    </div>
                    <h3 class="mb-3 text-2xl font-black text-slate-950"><?= lang('UserRole.noUsersFound') ?></h3>
                    <p class="mx-auto mb-8 max-w-md text-sm font-semibold text-slate-500"><?= lang('UserRole.noUsersFoundDescription') ?></p>
                    <a href="<?= workflow_context_url('users/create') ?>" data-user-create-modal-open class="inline-flex items-center rounded-xl bg-blue-600 px-6 py-3 text-base font-black text-white shadow-lg shadow-blue-500/20 transition hover:bg-blue-700">
                        <i class="fas fa-plus mr-3"></i><?= lang('UserRole.createFirstUser') ?>
                    </a>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?= view('users/partials/quick_create_modal', [
    'roles' => $roles ?? [],
    'departments' => $departments ?? [],
    'return_to' => workflow_current_target() ?? 'users',
    'is_rtl' => !empty($is_rtl),
]) ?>
<?= view('components/email_check_script', ['emailCheckUrl' => base_url('users/check-email')]) ?>

<script>
// User Filter Management Class
class UserFilterManager {
    constructor() {
        this.searchValue = '';
        this.groupFilter = '';
        this.statusFilter = '';
        this.allUsers = [];
        this.filteredUsers = [];
        
        this.init();
    }
    
    init() {
        this.bindEvents();
        this.loadAllUsers();
    }
    
    bindEvents() {
        // Search functionality
        const searchInput = document.getElementById('userSearchInput');
        if (searchInput) {
            let searchTimeout;
            searchInput.addEventListener('input', (e) => {
                clearTimeout(searchTimeout);
                searchTimeout = setTimeout(() => {
                    this.searchValue = e.target.value.toLowerCase();
                    this.applyFilters();
                }, 300);
            });
        }
        
        // Group filter
        const groupFilter = document.getElementById('groupFilter');
        if (groupFilter) {
            groupFilter.addEventListener('change', (e) => {
                this.groupFilter = e.target.value;
                this.applyFilters();
            });
        }
        
        // Status filter
        const statusFilter = document.getElementById('statusFilter');
        if (statusFilter) {
            statusFilter.addEventListener('change', (e) => {
                this.statusFilter = e.target.value;
                this.applyFilters();
            });
        }
    }
    
    loadAllUsers() {
        const userCards = document.querySelectorAll('[data-user-card]');
        this.allUsers = Array.from(userCards).map(card => {
            const name = card.dataset.userName || '';
            const email = card.dataset.userEmail || '';
            const status = card.dataset.userStatus || '';
            const rolesText = card.dataset.userRoles || '';
            
            return {
                element: card,
                name: name,
                email: email,
                roles: rolesText,
                status: status,
                searchText: `${name} ${email}`.toLowerCase()
            };
        }).filter(user => user !== null);
        
        this.filteredUsers = [...this.allUsers];
    }
    
    applyFilters() {
        this.filteredUsers = this.allUsers.filter(user => {
            // Search filter
            if (this.searchValue && !user.searchText.includes(this.searchValue)) {
                return false;
            }
            
            // Role filter
            if (this.groupFilter && !user.roles.includes(this.groupFilter)) {
                return false;
            }
            
            // Status filter
            if (this.statusFilter && user.status !== this.statusFilter) {
                return false;
            }
            
            return true;
        });
        
        this.updateDisplay();
    }
    
    updateDisplay() {
        // Hide all cards first
        this.allUsers.forEach(user => {
            user.element.style.display = 'none';
        });
        
        // Show filtered cards
        this.filteredUsers.forEach(user => {
            user.element.style.display = '';
        });
        
        // Update counter
        const filteredCountElement = document.getElementById('filteredCount');
        const totalCountElement = document.getElementById('totalCount');
        if (filteredCountElement) {
            filteredCountElement.textContent = this.filteredUsers.length;
        }
        if (totalCountElement) {
            totalCountElement.textContent = this.allUsers.length;
        }
    }
}

// Initialize the filter manager when the page loads
document.addEventListener('DOMContentLoaded', function() {
    new UserFilterManager();
});

function toggleStatus(id) {
    if (confirm(<?= json_encode(lang('UserRole.confirmToggleUserStatus')) ?>)) {
        fetch(`<?= base_url('users') ?>/${id}/toggle-status`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                '<?= csrf_header() ?>': '<?= csrf_hash() ?>'
            }
        })
        .then(response => response.json())
        .then(data => {
            // Handle CSRF token refresh if provided in response
            if (data.csrf_token && window.refreshCsrfToken) {
                window.refreshCsrfToken(data.csrf_token);
            }
            
            if (data.success) {
                location.reload();
            } else {
                alert(<?= json_encode(lang('UserRole.errorPrefix')) ?> + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert(<?= json_encode(lang('UserRole.errorUpdatingUserStatus')) ?>);
        });
    }
}

function deleteUser(id) {
    if (confirm(<?= json_encode(lang('UserRole.confirmDeleteUser')) ?>)) {
        fetch(`<?= base_url('users') ?>/${id}`, {
            method: 'DELETE',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                '<?= csrf_header() ?>': '<?= csrf_hash() ?>'
            }
        })
        .then(response => response.json())
        .then(data => {
            // Handle CSRF token refresh if provided in response
            if (data.csrf_token && window.refreshCsrfToken) {
                window.refreshCsrfToken(data.csrf_token);
            }
            
            if (data.success) {
                location.reload();
            } else {
                alert(<?= json_encode(lang('UserRole.errorPrefix')) ?> + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert(<?= json_encode(lang('UserRole.errorDeletingUser')) ?>);
        });
    }
}
</script>
<?= $this->endSection() ?>

