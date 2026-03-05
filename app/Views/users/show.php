<?= $this->extend('layouts/main_auth') ?>

<?= $this->section('content') ?>
<div class="px-6 py-8">
    <!-- Page Header -->
    <div class="mb-8">
        <div class="flex flex-col lg:flex-row lg:justify-between lg:items-center space-y-6 lg:space-y-0">
            <div>
                <h1 class="text-4xl font-bold text-gray-800 mb-3"><?= lang('UserRole.userDetails') ?></h1>
                <p class="text-gray-600 text-lg"><?= lang('UserRole.viewUserInformationPermissions') ?></p>
            </div>
            <div class="flex flex-col sm:flex-row space-y-3 sm:space-y-0 sm:space-x-4">
                <a href="<?= base_url('users') ?>" class="inline-flex items-center px-4 py-2 bg-gray-600 text-white text-sm font-medium rounded-md hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition-colors">
                    <i class="fas fa-arrow-left mr-2"></i><?= lang('UserRole.backToUsers') ?>
                </a>
                <?php if (has_permission('users', 'edit')): ?>
                    <a href="<?= base_url('users/' . $user->id . '/edit') ?>" class="inline-flex items-center px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition-colors">
                        <i class="fas fa-edit mr-2"></i><?= lang('UserRole.editUser') ?>
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- User Information Card -->
    <div class="bg-white rounded-lg border border-gray-200 shadow-sm mb-8">
        <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-xl font-bold text-gray-800"><?= lang('UserRole.userInformation') ?></h3>
        </div>
        <div class="p-6">
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                <!-- Left Column -->
                <div class="space-y-6">
                    <!-- Profile Picture and Basic Info -->
                    <div class="flex items-center space-x-6">
                        <div class="w-20 h-20 bg-gradient-to-br from-blue-500 to-blue-600 rounded-full flex items-center justify-center text-white font-bold text-2xl">
                            <?= esc(ui_initials($user->first_name ?? '', $user->last_name ?? '', 'U')) ?>
                        </div>
                        <div>
                            <h2 class="text-2xl font-bold text-gray-900">
                                <?= ($user->first_name ?? '') . ' ' . ($user->last_name ?? '') ?>
                            </h2>
                            <p class="text-gray-600 text-lg"><?= $user->email ?></p>
                        </div>
                    </div>

                    <!-- Contact Information -->
                    <div class="space-y-4">
                        <h4 class="text-lg font-semibold text-gray-800"><?= lang('UserRole.contactInformation') ?></h4>
                        <div class="space-y-3">
                            <div class="flex items-center space-x-3">
                                <i class="fas fa-phone text-blue-500 w-5"></i>
                                <span class="text-gray-700"><?= $user->phone ?? lang('UserRole.notProvided') ?></span>
                            </div>
                            <div class="flex items-center space-x-3">
                                <i class="fas fa-envelope text-green-500 w-5"></i>
                                <span class="text-gray-700"><?= $user->email ?></span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Right Column -->
                <div class="space-y-6">
                    <!-- Status and Groups -->
                    <div class="space-y-4">
                        <h4 class="text-lg font-semibold text-gray-800"><?= lang('UserRole.statusAndGroups') ?></h4>
                        
                        <!-- Status -->
                        <div class="flex items-center space-x-3">
                            <span class="text-gray-600 font-medium"><?= lang('UserRole.statusColon') ?></span>
                            <?php
                            $statusColors = [
                                1 => 'bg-green-100 text-green-800',
                                0 => 'bg-gray-100 text-gray-800'
                            ];
                            $statusColor = $statusColors[$user->active] ?? 'bg-gray-100 text-gray-800';
                            $statusText = $user->active == 1 ? lang('UserRole.active') : lang('UserRole.inactive');
                            ?>
                            <span class="px-3 py-1 text-sm font-medium rounded-full <?= $statusColor ?>">
                                <?= $statusText ?>
                            </span>
                        </div>

                        <!-- Roles -->
                        <div class="space-y-2">
                            <span class="text-gray-600 font-medium"><?= lang('UserRole.rolesColon') ?></span>
                            <div class="flex flex-wrap gap-2">
                                <?php if (!empty($user->display_roles)): ?>
                                    <?php foreach ($user->display_roles as $role): ?>
                                        <span class="px-3 py-1 text-sm font-medium rounded-full bg-green-100 text-green-800">
                                            <?php 
                                            // Handle both array and object formats
                                            $roleName = '';
                                            if (is_array($role)) {
                                                $roleName = $role['name'] ?? lang('UserRole.unknownRole');
                                            } else {
                                                $roleName = $role->name ?? lang('UserRole.unknownRole');
                                            }
                                            echo ucfirst($roleName);
                                            ?>
                                        </span>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <span class="px-3 py-1 text-sm font-medium rounded-full bg-gray-100 text-gray-800">
                                        <?= lang('UserRole.noRolesAssigned') ?>
                                    </span>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <!-- Account Information -->
                    <div class="space-y-4">
                        <h4 class="text-lg font-semibold text-gray-800"><?= lang('UserRole.accountInformation') ?></h4>
                        <div class="space-y-3">
                            <div class="flex justify-between">
                                <span class="text-gray-600 font-medium"><?= lang('UserRole.userIdColon') ?></span>
                                <span class="text-gray-900">#<?= $user->id ?></span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600 font-medium"><?= lang('UserRole.createdColon') ?></span>
                                <span class="text-gray-900">
                                    <?= $user->created_on ? date('M j, Y g:i A', strtotime($user->created_on)) : lang('UserRole.na') ?>
                                </span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600 font-medium"><?= lang('UserRole.lastLoginColon') ?></span>
                                <span class="text-gray-900">
                                    <?= $user->last_login ? date('M j, Y g:i A', strtotime($user->last_login)) : lang('UserRole.never') ?>
                                </span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600 font-medium"><?= lang('UserRole.lastIpColon') ?></span>
                                <span class="text-gray-900"><?= $user->ip_address ?? lang('UserRole.na') ?></span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Actions Card -->
    <div class="bg-white rounded-lg border border-gray-200 shadow-sm">
        <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-xl font-bold text-gray-800"><?= lang('UserRole.actions') ?></h3>
        </div>
        <div class="p-6">
            <div class="flex flex-wrap gap-4">
                <?php if (has_permission('users', 'edit')): ?>
                    <a href="<?= base_url('users/' . $user->id . '/edit') ?>" class="inline-flex items-center px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition-colors">
                        <i class="fas fa-edit mr-2"></i><?= lang('UserRole.editUser') ?>
                    </a>
                    <a href="<?= base_url('users/' . $user->id . '/change-password') ?>" class="inline-flex items-center px-4 py-2 bg-green-600 text-white text-sm font-medium rounded-md hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 transition-colors">
                        <i class="fas fa-key mr-2"></i><?= lang('UserRole.changePassword') ?>
                    </a>
                    <button onclick="toggleStatus(<?= $user->id ?>)" class="inline-flex items-center px-4 py-2 bg-purple-600 text-white text-sm font-medium rounded-md hover:bg-purple-700 focus:outline-none focus:ring-2 focus:ring-purple-500 focus:ring-offset-2 transition-colors">
                        <i class="fas fa-toggle-on mr-2"></i>
                        <?= $user->active == 1 ? lang('UserRole.deactivate') : lang('UserRole.activate') ?> <?= lang('UserRole.user') ?>
                    </button>
                <?php endif; ?>
                <?php if (has_permission('users', 'delete') && $user->id != 1): ?>
                    <button onclick="deleteUser(<?= $user->id ?>)" class="inline-flex items-center px-4 py-2 bg-red-600 text-white text-sm font-medium rounded-md hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 transition-colors">
                        <i class="fas fa-trash mr-2"></i><?= lang('UserRole.deleteUser') ?>
                    </button>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<script>
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
            if (data.success) {
                window.location.href = '<?= base_url('users') ?>';
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
