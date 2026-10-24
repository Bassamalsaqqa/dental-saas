<?= $this->extend('layouts/main_auth') ?>

<?= $this->section('content') ?>
<div class="v3-operational-view">
<?php
    $totalUsers = (int) ($total_users ?? 0);
    $activeUsers = (int) ($active_users ?? 0);
    $inactiveUsers = (int) ($inactive_users ?? 0);
    $assignedRoleRows = (int) ($diagnostics['total_user_roles'] ?? 0);
    $currentUserId = (int) ($current_user->id ?? (session()->get('user_id') ?? 0));
?>


<div class="users-workspace min-h-screen">
    <div class="mx-auto max-w-7xl px-6 py-8">
        <section class="users-hero mb-6 sm:p-6 lg:p-7">
            <div class="flex flex-col xl:flex-row xl:items-center xl:justify-between">
                <div class="flex items-start gap-4">
                    <div class="flex h-14 w-14 shrink-0 items-center justify-center border">
                        <i class="fas fa-users-cog v3-section-title"></i>
                    </div>
                    <div class="min-w-0">
                        <p class="text-xs uppercase tracking-[0.18em]"><?= lang('UI.menuSettings') ?></p>
                        <h1 class="mt-1 v3-title tracking-tight v3-title"><?= lang('UserRole.userManagement') ?></h1>
                    </div>
                </div>
                <div class="grid grid-cols-1 gap-3 sm:grid-cols-3 xl:min-w-[31rem]">
                    <div class="users-hero-chip px-4 py-3">
                        <p class="text-[11px] uppercase tracking-[0.14em]"><?= lang('UserRole.totalUsers') ?></p>
                        <p class="mt-1 v3-section-title"><?= $totalUsers ?></p>
                    </div>
                    <div class="users-hero-chip px-4 py-3">
                        <p class="text-[11px] uppercase tracking-[0.14em]"><?= lang('UserRole.activeUsers') ?></p>
                        <p class="mt-1 v3-section-title"><?= $activeUsers ?></p>
                    </div>
                    <div class="users-hero-chip px-4 py-3">
                        <p class="text-[11px] uppercase tracking-[0.14em]"><?= lang('UserRole.roles') ?></p>
                        <p class="mt-1 v3-section-title"><?= $assignedRoleRows ?></p>
                    </div>
                </div>
            </div>
            <div class="mt-6 flex flex-wrap gap-3">
                    <?php if (has_permission('settings', 'edit')): ?>
                        <a href="<?= base_url('rbac/setup') ?>" class="inline-flex items-center justify-center gap-2 border px-4 py-2.5 text-sm transition v3-btn v3-btn--secondary">
                            <i class="fas fa-cog mr-2"></i><?= lang('UserRole.rbacSetup') ?>
                        </a>
                    <?php endif; ?>
                    <?php if (has_permission('users', 'create')): ?>
                        <a href="<?= workflow_context_url('users/create') ?>" data-user-create-modal-open class="inline-flex items-center justify-center gap-2 px-4 py-2.5 text-sm transition hover:-translate-y-0.5 v3-btn v3-btn--secondary">
                            <i class="fas fa-plus mr-2"></i><?= lang('UserRole.createUser') ?>
                        </a>
                    <?php endif; ?>
            </div>
        </section>

        <!-- RBAC Status -->
        <?php if (isset($diagnostics) && !$diagnostics['rbac_ready']): ?>
            <div class="mb-6 v3-bg-waiting border v3-border-default p-4">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <i class="fas fa-exclamation-triangle v3-text-warning text-xl"></i>
                    </div>
                    <div class="ml-3">
                        <h3 class="text-sm font-medium v3-text-warning"><?= lang('UserRole.rbacSystemNotReady') ?></h3>
                        <div class="mt-2 text-sm v3-text-warning">
                            <p><?= lang('UserRole.rbacNotInitializedUsers') ?></p>
                            <div class="mt-3">
                                <a href="<?= base_url('rbac/setup') ?>" class="inline-flex items-center px-3 py-2 v3-bg-warning text-sm font-medium rounded-lg v3-hover-primary transition-colors v3-btn v3-btn--secondary">
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
            <div class="mb-6 v3-bg-paid border v3-border-default p-4">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <i class="fas fa-check-circle v3-text-primary text-xl"></i>
                    </div>
                    <div class="ml-3">
                        <h3 class="text-sm font-medium v3-text-primary"><?= lang('UserRole.success') ?></h3>
                        <div class="mt-2 text-sm v3-text-primary">
                            <p><?= session()->getFlashdata('success') ?></p>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <?php if (session()->getFlashdata('error')): ?>
            <div class="mb-6 v3-bg-alert border v3-border-default p-4">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <i class="fas fa-exclamation-circle v3-text-danger text-xl"></i>
                    </div>
                    <div class="ml-3">
                        <h3 class="text-sm font-medium v3-text-danger"><?= lang('UserRole.error') ?></h3>
                        <div class="mt-2 text-sm v3-text-danger">
                            <p><?= session()->getFlashdata('error') ?></p>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <!-- Error Message -->
        <?php if (isset($error)): ?>
            <div class="mb-6 v3-bg-alert border v3-border-default p-4">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <i class="fas fa-exclamation-triangle v3-text-danger text-xl"></i>
                    </div>
                    <div class="ml-3">
                        <h3 class="text-sm font-medium v3-text-danger"><?= lang('UserRole.errorLoadingUsers') ?></h3>
                        <div class="mt-2 text-sm v3-text-danger">
                            <p><?= $error ?></p>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <section class="mb-7 grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-4">
            <div class="users-stat-card">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <p class="text-xs uppercase tracking-[0.14em]"><?= lang('UserRole.totalUsers') ?></p>
                        <p class="mt-3 v3-title"><?= $totalUsers ?></p>
                    </div>
                    <div class="p-3"><i class="fas fa-users text-xl"></i></div>
                </div>
            </div>
            <div class="users-stat-card">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <p class="text-xs uppercase tracking-[0.14em] v3-text-primary"><?= lang('UserRole.activeUsers') ?></p>
                        <p class="mt-3 v3-title"><?= $activeUsers ?></p>
                    </div>
                    <div class="p-3"><i class="fas fa-check-circle text-xl"></i></div>
                </div>
            </div>
            <div class="users-stat-card">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <p class="text-xs uppercase tracking-[0.14em] v3-text-warning"><?= lang('UserRole.inactiveUsers') ?></p>
                        <p class="mt-3 v3-title"><?= $inactiveUsers ?></p>
                    </div>
                    <div class="p-3"><i class="fas fa-user-slash text-xl"></i></div>
                </div>
            </div>
            <div class="users-stat-card">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <p class="text-xs uppercase tracking-[0.14em] text-fuchsia-100/80"><?= lang('UserRole.roles') ?></p>
                        <p class="mt-3 v3-title"><?= $assignedRoleRows ?></p>
                    </div>
                    <div class="p-3"><i class="fas fa-user-shield text-xl"></i></div>
                </div>
            </div>
        </section>

        <?php if (!empty($users)): ?>
            <section class="users-filter-panel mb-5">
                <div class="mb-4 flex flex-col gap-2 md:flex-row md:items-center md:justify-between">
                    <div>
                        <h3 class="flex items-center text-lg">
                            <i class="fas fa-search <?= !empty($is_rtl) ? 'ml-2' : 'mr-2' ?>"></i>
                            <?= lang('UserRole.searchFilterUsers') ?>
                        </h3>
                        <p class="mt-1 text-sm font-semibold">
                            <?= lang('UserRole.showing') ?> <span id="filteredCount"><?= count($users) ?></span> <?= lang('UserRole.of') ?> <span id="totalCount"><?= count($users) ?></span> <?= lang('UserRole.users') ?>
                        </p>
                    </div>
                </div>
                <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
                    <div class="relative">
                        <input type="text" id="userSearchInput" placeholder="<?= lang('UserRole.searchUsers') ?>" class="w-full border-2 px-4 py-3 <?= !empty($is_rtl) ? 'pr-10 text-right' : 'pl-10' ?> font-semibold transition focus:outline-none focus:ring-4 v3-input">
                        <i class="fas fa-search absolute top-1/2 /2 <?= !empty($is_rtl) ? 'right-3' : 'left-3' ?>"></i>
                    </div>
                    <select id="groupFilter" class="w-full border-2 px-4 py-3 font-semibold transition focus:outline-none focus:ring-4 v3-select">
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
                    <select id="statusFilter" class="w-full border-2 px-4 py-3 font-semibold transition focus:outline-none focus:ring-4 v3-select">
                        <option value=""><?= lang('UserRole.allStatus') ?></option>
                        <option value="active"><?= lang('UserRole.active') ?></option>
                        <option value="inactive"><?= lang('UserRole.inactive') ?></option>
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
                        $membershipActive = ($user->membership_status ?? 'active') === 'active';
                        $membershipStatusText = $membershipActive ? lang('UserRole.active') : lang('UserRole.inactive');
                        $membershipStatusClass = $membershipActive ? 'v3-bg-paid v3-text-primary border v3-border-default' : 'v3-bg-soft v3-text-ink border v3-border-default';
                        $isGloballyDisabled = (int) ($user->active ?? 0) !== 1;
                        $isSelf = (int) $user->id === $currentUserId;
                        $roleNames = [];
                        if (!empty($user->display_roles)) {
                            foreach ($user->display_roles as $role) {
                                $roleNames[] = is_array($role)
                                    ? ($role['name'] ?? lang('UserRole.unknownRole'))
                                    : ($role->name ?? lang('UserRole.unknownRole'));
                            }
                        }
                    ?>
                    <article class="user-card" data-user-card data-user-name="<?= esc(strtolower($userName), 'attr') ?>" data-user-email="<?= esc(strtolower((string) $user->email), 'attr') ?>" data-user-status="<?= esc($membershipActive ? 'active' : 'inactive') ?>" data-user-roles="<?= esc(strtolower(implode(' ', $roleNames)), 'attr') ?>">
                        <div class="">
                            <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
                                <div class="flex min-w-0 items-start gap-4">
                                    <div class="user-avatar flex h-14 w-14 shrink-0 items-center justify-center text-lg transition">
                                        <?= esc(ui_initials($user->first_name ?? '', $user->last_name ?? '', 'U')) ?>
                                    </div>
                                    <div class="min-w-0">
                                        <h3 class="truncate text-lg"><?= esc($userName) ?></h3>
                                        <p class="mt-1 truncate text-sm font-bold" dir="ltr"><?= esc($user->email) ?></p>
                                        <div class="mt-3 flex flex-wrap items-center gap-2">
                                            <span class="inline-flex items-center gap-1.5 px-3 py-1 text-xs <?= $membershipStatusClass ?>" title="<?= esc(lang('UserRole.membershipStatus')) ?>">
                                                <span class="h-1.5 w-1.5 <?= $membershipActive ? 'v3-bg-primary' : 'v3-bg-canvas' ?>"></span>
                                                <?= esc($membershipStatusText) ?>
                                            </span>
                                            <?php if ($isGloballyDisabled): ?>
                                                <span class="inline-flex items-center gap-1 v3-bg-alert px-2.5 py-1 text-xs v3-text-danger border v3-border-default" title="<?= esc(lang('UserRole.globallyDisabledWarning')) ?>">
                                                    <i class="fas fa-ban text-[10px]"></i>
                                                    <?= esc(lang('UserRole.globallyDisabled')) ?>
                                                </span>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                                <div class="flex flex-wrap gap-2 sm:justify-end">
                                        <?php if (has_permission('users', 'view')): ?>
                                            <a href="<?= workflow_context_url('users/' . $user->id) ?>" class="user-action-btn border" title="<?= lang('UserRole.viewUser') ?>">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                        <?php endif; ?>
                                        <?php if (has_permission('users', 'edit')): ?>
                                            <a href="<?= workflow_context_url('users/' . $user->id . '/edit') ?>" class="user-action-btn v3-bg-waiting v3-text-warning border v3-border-default v3-hover-primary" title="<?= lang('UserRole.editUser') ?>">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <?php if ($isSelf): ?>
                                                <a href="<?= workflow_context_url('users/' . $user->id . '/change-password') ?>" class="user-action-btn v3-bg-paid v3-text-primary border v3-border-default v3-hover-primary" title="<?= lang('UserRole.changePassword') ?>">
                                                    <i class="fas fa-key"></i>
                                                </a>
                                            <?php endif; ?>
                                            <button onclick="toggleStatus(<?= (int) $user->id ?>)" <?= $isGloballyDisabled && !$membershipActive ? 'disabled aria-disabled="true"' : '' ?> class="user-action-btn border v3-btn v3-btn--secondary" title="<?= lang('UserRole.toggleStatus') ?>">
                                                <i class="fas fa-toggle-on"></i>
                                            </button>
                                        <?php endif; ?>
                                        <?php if (has_permission('users', 'delete')): ?>
                                            <button onclick="deleteUser(<?= (int) $user->id ?>)" class="user-action-btn v3-bg-alert v3-text-danger border v3-border-default v3-hover-danger v3-btn v3-btn--danger" title="<?= lang('UserRole.removeClinicAccess') ?>">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        <?php endif; ?>
                                </div>
                            </div>

                            <div class="mt-5 grid grid-cols-1 gap-3 md:grid-cols-3">
                                <div class="user-info-tile md:col-span-1">
                                    <h4 class="mb-3 text-xs uppercase tracking-[0.12em]"><?= lang('UserRole.rolesAndPermissions') ?></h4>
                                    <?php if (!empty($user->display_roles)): ?>
                                        <div class="flex flex-wrap gap-2">
                                            <?php foreach ($user->display_roles as $role): ?>
                                                <span class="inline-flex items-center v3-bg-scheduled px-2.5 py-1 text-xs v3-text-primary border v3-border-default">
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
                                        <p class="text-sm font-semibold"><?= lang('UserRole.noRolesAssigned') ?></p>
                                    <?php endif; ?>
                                </div>

                                <div class="user-info-tile">
                                    <h4 class="mb-3 text-xs uppercase tracking-[0.12em]"><?= lang('UserRole.contactInformation') ?></h4>
                                    <div class="space-y-2">
                                        <div class="flex min-w-0 items-center text-sm font-semibold">
                                            <i class="fas fa-phone w-4 <?= !empty($is_rtl) ? 'ml-2' : 'mr-2' ?>"></i>
                                            <span class="truncate" dir="ltr"><?= esc($user->phone ?? lang('UserRole.notProvided')) ?></span>
                                        </div>
                                        <div class="flex min-w-0 items-center text-sm font-semibold">
                                            <i class="fas fa-envelope w-4 v3-text-primary <?= !empty($is_rtl) ? 'ml-2' : 'mr-2' ?>"></i>
                                            <span class="truncate" dir="ltr"><?= esc($user->email) ?></span>
                                        </div>
                                    </div>
                                </div>

                                <div class="user-info-tile">
                                    <h4 class="mb-3 text-xs uppercase tracking-[0.12em]"><?= lang('UserRole.activity') ?></h4>
                                    <div class="space-y-2">
                                        <div class="flex items-center text-sm font-semibold">
                                            <i class="fas fa-calendar w-4 <?= !empty($is_rtl) ? 'ml-2' : 'mr-2' ?>"></i>
                                            <span><?= lang('UserRole.createdColon') ?> <?= $user->created_on ? date('M j, Y', strtotime($user->created_on)) : lang('UserRole.na') ?></span>
                                        </div>
                                        <div class="flex items-center text-sm font-semibold">
                                            <i class="fas fa-clock w-4 <?= !empty($is_rtl) ? 'ml-2' : 'mr-2' ?>"></i>
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
                <div class="users-section-panel v3-radius border border-dashed px-6 py-14 text-center xl:col-span-2">
                    <div class="mx-auto mb-5 flex h-20 w-20 items-center justify-center">
                        <i class="fas fa-users v3-title"></i>
                    </div>
                    <h3 class="mb-3 v3-section-title"><?= lang('UserRole.noUsersFound') ?></h3>
                    <p class="mx-auto mb-8 max-w-md text-sm font-semibold"><?= lang('UserRole.noUsersFoundDescription') ?></p>
                    <a href="<?= workflow_context_url('users/create') ?>" data-user-create-modal-open class="inline-flex items-center px-6 py-3 text-base transition v3-btn v3-btn--secondary">
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
    if (confirm(<?= json_encode(lang('UserRole.confirmRemoveClinicAccess')) ?>)) {
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
</div>
<?= $this->endSection() ?>

