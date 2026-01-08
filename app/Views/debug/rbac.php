<?= $this->extend('layouts/main_auth') ?>

<?= $this->section('content') ?>
<div class="min-h-screen bg-gradient-to-br from-blue-50 via-white to-purple-50">
    <div class="container mx-auto px-4 py-8">
        <!-- Header -->
        <div class="mb-8">
            <h1 class="text-4xl font-bold text-gray-800 mb-2">🔍 RBAC Debug Information</h1>
            <p class="text-gray-600">Comprehensive analysis of your RBAC system status</p>
        </div>

        <!-- Debug Information -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            
            <!-- User Information -->
            <div class="bg-white/70 backdrop-blur-sm rounded-2xl p-6 shadow-lg border border-white/20">
                <h2 class="text-2xl font-semibold text-gray-800 mb-4 flex items-center">
                    👤 User Information
                </h2>
                
                <?php if (isset($debug_info['logged_in']) && $debug_info['logged_in']): ?>
                    <div class="space-y-3">
                        <div class="flex justify-between">
                            <span class="font-medium text-gray-600">User ID:</span>
                            <span class="text-gray-800"><?= $debug_info['user']['id'] ?></span>
                        </div>
                        <div class="flex justify-between">
                            <span class="font-medium text-gray-600">Username:</span>
                            <span class="text-gray-800"><?= $debug_info['user']['username'] ?></span>
                        </div>
                        <div class="flex justify-between">
                            <span class="font-medium text-gray-600">Email:</span>
                            <span class="text-gray-800"><?= $debug_info['user']['email'] ?></span>
                        </div>
                        <div class="flex justify-between">
                            <span class="font-medium text-gray-600">Name:</span>
                            <span class="text-gray-800"><?= $debug_info['user']['first_name'] ?> <?= $debug_info['user']['last_name'] ?></span>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="text-red-600">❌ User not logged in</div>
                <?php endif; ?>
            </div>

            <!-- IonAuth Status -->
            <div class="bg-white/70 backdrop-blur-sm rounded-2xl p-6 shadow-lg border border-white/20">
                <h2 class="text-2xl font-semibold text-gray-800 mb-4 flex items-center">
                    🔐 IonAuth Status
                </h2>
                
                <div class="space-y-3">
                    <div class="flex justify-between">
                        <span class="font-medium text-gray-600">Is Admin:</span>
                        <span class="<?= $debug_info['ionauth_admin'] ? 'text-green-600' : 'text-red-600' ?>">
                            <?= $debug_info['ionauth_admin'] ? '✅ Yes' : '❌ No' ?>
                        </span>
                    </div>
                    
                    <?php if (isset($debug_info['ionauth_groups']) && !empty($debug_info['ionauth_groups'])): ?>
                        <div>
                            <span class="font-medium text-gray-600">Groups:</span>
                            <div class="mt-2 space-y-1">
                                <?php foreach ($debug_info['ionauth_groups'] as $group): ?>
                                    <div class="text-sm bg-blue-100 text-blue-800 px-2 py-1 rounded">
                                        <?= $group['name'] ?> (ID: <?= $group['id'] ?>)
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php else: ?>
                        <div class="text-red-600">❌ No groups assigned</div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- RBAC Database Status -->
            <div class="bg-white/70 backdrop-blur-sm rounded-2xl p-6 shadow-lg border border-white/20">
                <h2 class="text-2xl font-semibold text-gray-800 mb-4 flex items-center">
                    🗄️ RBAC Database Status
                </h2>
                
                <div class="space-y-3">
                    <div class="flex justify-between">
                        <span class="font-medium text-gray-600">Permissions:</span>
                        <span class="<?= $debug_info['rbac_permissions_count'] > 0 ? 'text-green-600' : 'text-red-600' ?>">
                            <?= $debug_info['rbac_permissions_count'] ?> records
                        </span>
                    </div>
                    <div class="flex justify-between">
                        <span class="font-medium text-gray-600">Roles:</span>
                        <span class="<?= $debug_info['rbac_roles_count'] > 0 ? 'text-green-600' : 'text-red-600' ?>">
                            <?= $debug_info['rbac_roles_count'] ?> records
                        </span>
                    </div>
                    <div class="flex justify-between">
                        <span class="font-medium text-gray-600">User-Role Assignments:</span>
                        <span class="<?= $debug_info['rbac_user_roles_count'] > 0 ? 'text-green-600' : 'text-red-600' ?>">
                            <?= $debug_info['rbac_user_roles_count'] ?> records
                        </span>
                    </div>
                </div>
            </div>

            <!-- User Roles -->
            <div class="bg-white/70 backdrop-blur-sm rounded-2xl p-6 shadow-lg border border-white/20">
                <h2 class="text-2xl font-semibold text-gray-800 mb-4 flex items-center">
                    👥 User Roles
                </h2>
                
                <?php if (isset($debug_info['user_roles']) && !empty($debug_info['user_roles'])): ?>
                    <div class="space-y-2">
                        <?php foreach ($debug_info['user_roles'] as $role): ?>
                            <div class="bg-green-100 text-green-800 px-3 py-2 rounded-lg">
                                <div class="font-medium"><?= $role['name'] ?></div>
                                <div class="text-sm"><?= $role['description'] ?></div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="text-red-600">❌ No roles assigned</div>
                <?php endif; ?>
            </div>

            <!-- Super Admin Status -->
            <div class="bg-white/70 backdrop-blur-sm rounded-2xl p-6 shadow-lg border border-white/20">
                <h2 class="text-2xl font-semibold text-gray-800 mb-4 flex items-center">
                    🦸 Super Admin Status
                </h2>
                
                <div class="space-y-3">
                    <div class="flex justify-between">
                        <span class="font-medium text-gray-600">Has Super Admin Role:</span>
                        <span class="<?= $debug_info['has_super_admin_role'] ? 'text-green-600' : 'text-red-600' ?>">
                            <?= $debug_info['has_super_admin_role'] ? '✅ Yes' : '❌ No' ?>
                        </span>
                    </div>
                    <div class="flex justify-between">
                        <span class="font-medium text-gray-600">Is Super Admin:</span>
                        <span class="<?= $debug_info['is_super_admin'] ? 'text-green-600' : 'text-red-600' ?>">
                            <?= $debug_info['is_super_admin'] ? '✅ Yes' : '❌ No' ?>
                        </span>
                    </div>
                    
                    <?php if (isset($debug_info['super_admin_role'])): ?>
                        <div class="mt-3 p-3 bg-blue-50 rounded-lg">
                            <div class="font-medium text-blue-800">Super Admin Role Found:</div>
                            <div class="text-sm text-blue-600"><?= $debug_info['super_admin_role']['name'] ?></div>
                        </div>
                    <?php else: ?>
                        <div class="text-red-600">❌ Super Admin role not found in database</div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Permission Checks -->
            <div class="bg-white/70 backdrop-blur-sm rounded-2xl p-6 shadow-lg border border-white/20">
                <h2 class="text-2xl font-semibold text-gray-800 mb-4 flex items-center">
                    🔑 Permission Checks
                </h2>
                
                <div class="space-y-3">
                    <div class="flex justify-between">
                        <span class="font-medium text-gray-600">Can Manage Users:</span>
                        <span class="<?= $debug_info['can_manage_users'] ? 'text-green-600' : 'text-red-600' ?>">
                            <?= $debug_info['can_manage_users'] ? '✅ Yes' : '❌ No' ?>
                        </span>
                    </div>
                    <div class="flex justify-between">
                        <span class="font-medium text-gray-600">Can Manage Roles:</span>
                        <span class="<?= $debug_info['can_manage_roles'] ? 'text-green-600' : 'text-red-600' ?>">
                            <?= $debug_info['can_manage_roles'] ? '✅ Yes' : '❌ No' ?>
                        </span>
                    </div>
                    <div class="flex justify-between">
                        <span class="font-medium text-gray-600">Users Create:</span>
                        <span class="<?= $debug_info['has_user_create'] ? 'text-green-600' : 'text-red-600' ?>">
                            <?= $debug_info['has_user_create'] ? '✅ Yes' : '❌ No' ?>
                        </span>
                    </div>
                    <div class="flex justify-between">
                        <span class="font-medium text-gray-600">Users Edit:</span>
                        <span class="<?= $debug_info['has_user_edit'] ? 'text-green-600' : 'text-red-600' ?>">
                            <?= $debug_info['has_user_edit'] ? '✅ Yes' : '❌ No' ?>
                        </span>
                    </div>
                    <div class="flex justify-between">
                        <span class="font-medium text-gray-600">Users Delete:</span>
                        <span class="<?= $debug_info['has_user_delete'] ? 'text-green-600' : 'text-red-600' ?>">
                            <?= $debug_info['has_user_delete'] ? '✅ Yes' : '❌ No' ?>
                        </span>
                    </div>
                    <div class="flex justify-between">
                        <span class="font-medium text-gray-600">Users Roles:</span>
                        <span class="<?= $debug_info['has_user_roles'] ? 'text-green-600' : 'text-red-600' ?>">
                            <?= $debug_info['has_user_roles'] ? '✅ Yes' : '❌ No' ?>
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Actions -->
        <div class="mt-8 flex flex-wrap gap-4">
            <a href="<?= base_url('rbac/setup') ?>" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-3 rounded-lg font-medium transition-colors">
                🔄 Go to RBAC Setup
            </a>
            <a href="<?= base_url('user-management') ?>" class="bg-green-600 hover:bg-green-700 text-white px-6 py-3 rounded-lg font-medium transition-colors">
                👥 User Management
            </a>
            <a href="<?= base_url('roles') ?>" class="bg-purple-600 hover:bg-purple-700 text-white px-6 py-3 rounded-lg font-medium transition-colors">
                🎭 Role Management
            </a>
            <a href="<?= base_url('dashboard') ?>" class="bg-gray-600 hover:bg-gray-700 text-white px-6 py-3 rounded-lg font-medium transition-colors">
                🏠 Dashboard
            </a>
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
