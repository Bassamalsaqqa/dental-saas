<?= $this->extend('layouts/main_auth') ?>

<?= $this->section('content') ?>
<div class="min-h-screen bg-gradient-to-br from-blue-50 via-white to-purple-50">
    <div class="container mx-auto px-4 py-8">
        <!-- Header -->
        <div class="mb-8">
            <h1 class="text-4xl font-bold text-gray-800 mb-2">🔍 Role Permissions Debug</h1>
            <p class="text-gray-600">Detailed analysis of role permissions and assignments</p>
        </div>

        <!-- Database Status -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
            <div class="bg-white/70 backdrop-blur-sm rounded-2xl p-6 shadow-lg border border-white/20">
                <h2 class="text-xl font-semibold text-gray-800 mb-4">📊 Database Status</h2>
                <div class="space-y-3">
                    <div class="flex justify-between">
                        <span class="font-medium text-gray-600">Total Permissions:</span>
                        <span class="text-gray-800"><?= $debug_info['total_permissions'] ?></span>
                    </div>
                    <div class="flex justify-between">
                        <span class="font-medium text-gray-600">Total Roles:</span>
                        <span class="text-gray-800"><?= count($debug_info['roles']) ?></span>
                    </div>
                    <div class="flex justify-between">
                        <span class="font-medium text-gray-600">Role-Permission Assignments:</span>
                        <span class="text-gray-800"><?= $debug_info['total_role_permissions'] ?></span>
                    </div>
                    <div class="flex justify-between">
                        <span class="font-medium text-gray-600">User-Role Assignments:</span>
                        <span class="text-gray-800"><?= $debug_info['total_user_roles'] ?></span>
                    </div>
                </div>
            </div>

            <div class="bg-white/70 backdrop-blur-sm rounded-2xl p-6 shadow-lg border border-white/20">
                <h2 class="text-xl font-semibold text-gray-800 mb-4">🎭 Role Summary</h2>
                <div class="space-y-2">
                    <?php foreach ($debug_info['roles'] as $role): ?>
                        <div class="flex justify-between items-center p-2 bg-gray-50 rounded-lg">
                            <span class="font-medium text-gray-700"><?= esc($role['name']) ?></span>
                            <div class="flex space-x-2 text-sm">
                                <span class="text-blue-600"><?= $role['permission_count'] ?> perms</span>
                                <span class="text-green-600"><?= $role['user_count'] ?> users</span>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <div class="bg-white/70 backdrop-blur-sm rounded-2xl p-6 shadow-lg border border-white/20">
                <h2 class="text-xl font-semibold text-gray-800 mb-4">🔧 Quick Actions</h2>
                <div class="space-y-3">
                    <a href="<?= base_url('rbac/setup') ?>" class="block w-full text-center bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg transition-colors">
                        🔄 Sync RBAC System
                    </a>
                    <a href="<?= base_url('debug/rbac') ?>" class="block w-full text-center bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-lg transition-colors">
                        👤 User RBAC Debug
                    </a>
                    <a href="<?= base_url('roles') ?>" class="block w-full text-center bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg transition-colors">
                        🎭 Back to Roles
                    </a>
                </div>
            </div>
        </div>

        <!-- Detailed Role Information -->
        <div class="space-y-6">
            <?php foreach ($debug_info['roles'] as $role): ?>
                <div class="bg-white/70 backdrop-blur-sm rounded-2xl p-6 shadow-lg border border-white/20">
                    <div class="flex items-center justify-between mb-4">
                        <div class="flex items-center space-x-3">
                            <div class="p-3 rounded-xl bg-gradient-to-br from-indigo-500 to-purple-600 text-white shadow-lg">
                                <i class="fas fa-user-tag text-lg"></i>
                            </div>
                            <div>
                                <h3 class="text-xl font-bold text-gray-800"><?= esc($role['name']) ?></h3>
                                <p class="text-sm text-gray-500"><?= esc($role['slug']) ?></p>
                            </div>
                        </div>
                        <div class="flex items-center space-x-4">
                            <div class="text-center">
                                <div class="text-2xl font-bold text-blue-600"><?= $role['permission_count'] ?></div>
                                <div class="text-sm text-gray-500">Permissions</div>
                            </div>
                            <div class="text-center">
                                <div class="text-2xl font-bold text-green-600"><?= $role['user_count'] ?></div>
                                <div class="text-sm text-gray-500">Users</div>
                            </div>
                        </div>
                    </div>

                    <!-- Permissions -->
                    <div class="mb-4">
                        <h4 class="text-lg font-semibold text-gray-700 mb-3">Permissions:</h4>
                        <?php if (!empty($role['permissions'])): ?>
                            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3">
                                <?php foreach ($role['permissions'] as $module => $modulePermissions): ?>
                                    <div class="border border-gray-200 rounded-lg p-3">
                                        <h5 class="font-medium text-gray-800 mb-2"><?= esc(ucwords(str_replace('_', ' ', $module))) ?></h5>
                                        <div class="space-y-1">
                                            <?php foreach ($modulePermissions as $permission): ?>
                                                <div class="text-sm text-gray-600 flex items-center">
                                                    <i class="fas fa-check text-green-500 mr-2"></i>
                                                    <?= esc($permission['name']) ?>
                                                </div>
                                            <?php endforeach; ?>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php else: ?>
                            <div class="text-center py-4 text-gray-500">
                                <i class="fas fa-exclamation-triangle text-2xl mb-2"></i>
                                <p>No permissions assigned to this role</p>
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- Users -->
                    <div>
                        <h4 class="text-lg font-semibold text-gray-700 mb-3">Users:</h4>
                        <?php if (!empty($role['users'])): ?>
                            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3">
                                <?php foreach ($role['users'] as $user): ?>
                                    <div class="border border-gray-200 rounded-lg p-3">
                                        <div class="font-medium text-gray-800"><?= esc($user['first_name'] . ' ' . $user['last_name']) ?></div>
                                        <div class="text-sm text-gray-500"><?= esc($user['email']) ?></div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php else: ?>
                            <div class="text-center py-4 text-gray-500">
                                <i class="fas fa-users text-2xl mb-2"></i>
                                <p>No users assigned to this role</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <!-- Raw Data -->
        <div class="mt-8 bg-white/70 backdrop-blur-sm rounded-2xl p-6 shadow-lg border border-white/20">
            <h2 class="text-xl font-semibold text-gray-800 mb-4">📋 Raw Database Data</h2>
            
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <!-- Role-Permission Assignments -->
                <div>
                    <h3 class="text-lg font-medium text-gray-700 mb-3">Role-Permission Assignments:</h3>
                    <div class="bg-gray-50 rounded-lg p-4 max-h-64 overflow-y-auto">
                        <pre class="text-xs text-gray-600"><?= json_encode($debug_info['role_permissions'], JSON_PRETTY_PRINT) ?></pre>
                    </div>
                </div>

                <!-- User-Role Assignments -->
                <div>
                    <h3 class="text-lg font-medium text-gray-700 mb-3">User-Role Assignments:</h3>
                    <div class="bg-gray-50 rounded-lg p-4 max-h-64 overflow-y-auto">
                        <pre class="text-xs text-gray-600"><?= json_encode($debug_info['all_user_roles'], JSON_PRETTY_PRINT) ?></pre>
                    </div>
                </div>
            </div>
        </div>

        <!-- Error Information -->
        <?php if (isset($debug_info['error'])): ?>
            <div class="mt-8 bg-red-50 border border-red-200 rounded-lg p-4">
                <h3 class="text-red-800 font-medium mb-2">❌ Error Information</h3>
                <div class="text-red-700"><?= $debug_info['error'] ?></div>
                <?php if (isset($debug_info['trace'])): ?>
                    <details class="mt-2">
                        <summary class="text-red-600 cursor-pointer">Show Stack Trace</summary>
                        <pre class="text-xs text-red-600 mt-2 overflow-auto"><?= htmlspecialchars($debug_info['trace']) ?></pre>
                    </details>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>
</div>
<?= $this->endSection() ?>
