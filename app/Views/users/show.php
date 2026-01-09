<?= $this->extend('layouts/main_auth') ?>

<?= $this->section('content') ?>
<div class="px-6 py-8">
    <!-- Page Header -->
    <div class="mb-8">
        <div class="flex flex-col lg:flex-row lg:justify-between lg:items-center space-y-6 lg:space-y-0">
            <div>
                <h1 class="text-4xl font-bold text-gray-800 mb-3">User Details</h1>
                <p class="text-gray-600 text-lg">View user information and permissions</p>
            </div>
            <div class="flex flex-col sm:flex-row space-y-3 sm:space-y-0 sm:space-x-4">
                <a href="<?= base_url('users') ?>" class="inline-flex items-center px-4 py-2 bg-gray-600 text-white text-sm font-medium rounded-md hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition-colors">
                    <i class="fas fa-arrow-left mr-2"></i>Back to Users
                </a>
                <?php if (has_permission('users', 'edit')): ?>
                    <a href="<?= base_url('users/' . $user->id . '/edit') ?>" class="inline-flex items-center px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition-colors">
                        <i class="fas fa-edit mr-2"></i>Edit User
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- User Information Card -->
    <div class="bg-white rounded-lg border border-gray-200 shadow-sm mb-8">
        <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-xl font-bold text-gray-800">User Information</h3>
        </div>
        <div class="p-6">
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                <!-- Left Column -->
                <div class="space-y-6">
                    <!-- Profile Picture and Basic Info -->
                    <div class="flex items-center space-x-6">
                        <div class="w-20 h-20 bg-gradient-to-br from-blue-500 to-blue-600 rounded-full flex items-center justify-center text-white font-bold text-2xl">
                            <?= strtoupper(substr($user->first_name ?? '', 0, 1) . substr($user->last_name ?? '', 0, 1)) ?>
                        </div>
                        <div>
                            <h2 class="text-2xl font-bold text-gray-900">
                                <?= ($user->first_name ?? '') . ' ' . ($user->last_name ?? '') ?>
                            </h2>
                            <p class="text-gray-600 text-lg"><?= $user->email ?></p>
                            <p class="text-gray-500">@<?= $user->username ?? $user->email ?></p>
                        </div>
                    </div>

                    <!-- Contact Information -->
                    <div class="space-y-4">
                        <h4 class="text-lg font-semibold text-gray-800">Contact Information</h4>
                        <div class="space-y-3">
                            <div class="flex items-center space-x-3">
                                <i class="fas fa-phone text-blue-500 w-5"></i>
                                <span class="text-gray-700"><?= $user->phone ?? 'Not provided' ?></span>
                            </div>
                            <div class="flex items-center space-x-3">
                                <i class="fas fa-envelope text-green-500 w-5"></i>
                                <span class="text-gray-700"><?= $user->email ?></span>
                            </div>
                            <div class="flex items-center space-x-3">
                                <i class="fas fa-map-marker-alt text-red-500 w-5"></i>
                                <span class="text-gray-700"><?= $user->address ?? 'Not provided' ?></span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Right Column -->
                <div class="space-y-6">
                    <!-- Status and Groups -->
                    <div class="space-y-4">
                        <h4 class="text-lg font-semibold text-gray-800">Status & Groups</h4>
                        
                        <!-- Status -->
                        <div class="flex items-center space-x-3">
                            <span class="text-gray-600 font-medium">Status:</span>
                            <?php
                            $statusColors = [
                                1 => 'bg-green-100 text-green-800',
                                0 => 'bg-gray-100 text-gray-800'
                            ];
                            $statusColor = $statusColors[$user->active] ?? 'bg-gray-100 text-gray-800';
                            $statusText = $user->active == 1 ? 'Active' : 'Inactive';
                            ?>
                            <span class="px-3 py-1 text-sm font-medium rounded-full <?= $statusColor ?>">
                                <?= $statusText ?>
                            </span>
                        </div>

                        <!-- Roles -->
                        <div class="space-y-2">
                            <span class="text-gray-600 font-medium">Roles:</span>
                            <div class="flex flex-wrap gap-2">
                                <?php if (!empty($user->display_roles)): ?>
                                    <?php foreach ($user->display_roles as $role): ?>
                                        <span class="px-3 py-1 text-sm font-medium rounded-full bg-green-100 text-green-800">
                                            <?php 
                                            // Handle both array and object formats
                                            $roleName = '';
                                            if (is_array($role)) {
                                                $roleName = $role['name'] ?? 'Unknown Role';
                                            } else {
                                                $roleName = $role->name ?? 'Unknown Role';
                                            }
                                            echo ucfirst($roleName);
                                            ?>
                                        </span>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <span class="px-3 py-1 text-sm font-medium rounded-full bg-gray-100 text-gray-800">
                                        No Roles Assigned
                                    </span>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <!-- Account Information -->
                    <div class="space-y-4">
                        <h4 class="text-lg font-semibold text-gray-800">Account Information</h4>
                        <div class="space-y-3">
                            <div class="flex justify-between">
                                <span class="text-gray-600 font-medium">User ID:</span>
                                <span class="text-gray-900">#<?= $user->id ?></span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600 font-medium">Created:</span>
                                <span class="text-gray-900">
                                    <?= $user->created_on ? date('M j, Y g:i A', strtotime($user->created_on)) : 'N/A' ?>
                                </span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600 font-medium">Last Login:</span>
                                <span class="text-gray-900">
                                    <?= $user->last_login ? date('M j, Y g:i A', strtotime($user->last_login)) : 'Never' ?>
                                </span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600 font-medium">Last IP:</span>
                                <span class="text-gray-900"><?= $user->ip_address ?? 'N/A' ?></span>
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
            <h3 class="text-xl font-bold text-gray-800">Actions</h3>
        </div>
        <div class="p-6">
            <div class="flex flex-wrap gap-4">
                <?php if (has_permission('users', 'edit')): ?>
                    <a href="<?= base_url('users/' . $user->id . '/edit') ?>" class="inline-flex items-center px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition-colors">
                        <i class="fas fa-edit mr-2"></i>Edit User
                    </a>
                    <a href="<?= base_url('users/' . $user->id . '/change-password') ?>" class="inline-flex items-center px-4 py-2 bg-green-600 text-white text-sm font-medium rounded-md hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 transition-colors">
                        <i class="fas fa-key mr-2"></i>Change Password
                    </a>
                    <button onclick="toggleStatus(<?= $user->id ?>)" class="inline-flex items-center px-4 py-2 bg-purple-600 text-white text-sm font-medium rounded-md hover:bg-purple-700 focus:outline-none focus:ring-2 focus:ring-purple-500 focus:ring-offset-2 transition-colors">
                        <i class="fas fa-toggle-on mr-2"></i>
                        <?= $user->active == 1 ? 'Deactivate' : 'Activate' ?> User
                    </button>
                <?php endif; ?>
                <?php if (has_permission('users', 'delete') && $user->id != 1): ?>
                    <button onclick="deleteUser(<?= $user->id ?>)" class="inline-flex items-center px-4 py-2 bg-red-600 text-white text-sm font-medium rounded-md hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 transition-colors">
                        <i class="fas fa-trash mr-2"></i>Delete User
                    </button>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<script>
function toggleStatus(id) {
    if (confirm('Are you sure you want to toggle this user\'s status?')) {
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
                alert('Error: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred while updating the user status.');
        });
    }
}

function deleteUser(id) {
    if (confirm('Are you sure you want to delete this user? This action cannot be undone.')) {
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
                alert('Error: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred while deleting the user.');
        });
    }
}
</script>
<?= $this->endSection() ?>
