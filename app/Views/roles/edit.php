<?php $layout = session()->get('global_mode') ? 'layouts/main_control_plane' : 'layouts/main_auth'; ?>
<?= $this->extend($layout) ?>

<?= $this->section('content') ?>
<div class="container mx-auto px-6 py-8">
    <!-- Page Header -->
    <div class="mb-8">
        <div class="flex flex-col lg:flex-row lg:justify-between lg:items-center space-y-6 lg:space-y-0">
            <div>
                <h1 class="text-4xl font-bold text-gray-800 mb-3"><?= lang('UserRole.editRole') ?></h1>
                <p class="text-gray-600 text-lg"><?= lang('UserRole.updateRoleInformationPermissions') ?></p>
            </div>
            <div class="flex flex-col sm:flex-row space-y-3 sm:space-y-0 sm:space-x-4">
                <a href="<?= base_url($rolesRoute) ?>" class="inline-flex items-center px-4 py-2 bg-gray-600 text-white text-sm font-medium rounded-lg hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition-all duration-200 shadow-lg hover:shadow-xl">
                    <i class="fas fa-arrow-left mr-2"></i><?= lang('UserRole.backToRoles') ?>
                </a>
            </div>
        </div>
    </div>

    <!-- Flash Messages -->
    <?php if (session()->getFlashdata('validation')): ?>
        <div class="mb-6 bg-red-50 border border-red-200 rounded-xl p-4 shadow-sm">
            <div class="flex">
                <div class="flex-shrink-0">
                    <i class="fas fa-exclamation-circle text-red-400 text-xl"></i>
                </div>
                <div class="ml-3">
                    <h3 class="text-sm font-medium text-red-800"><?= lang('UserRole.validationErrors') ?></h3>
                    <div class="mt-2 text-sm text-red-700">
                        <ul class="list-disc list-inside space-y-1">
                            <?php foreach (session()->getFlashdata('validation')->getErrors() as $error): ?>
                                <li><?= esc($error) ?></li>
                            <?php endforeach; ?>
                        </ul>
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

    <!-- Role Information Card -->
    <div class="mb-8 bg-white/70 backdrop-blur-sm rounded-2xl border border-white/30 shadow-lg p-6">
        <div class="flex items-center space-x-3 mb-6">
            <div class="p-3 rounded-xl bg-gradient-to-br from-indigo-500 to-purple-600 text-white shadow-lg">
                <i class="fas fa-user-tag text-lg"></i>
            </div>
            <div>
                <h2 class="text-xl font-bold text-gray-800"><?= esc($role['name']) ?></h2>
                <p class="text-sm text-gray-500"><?= esc($role['slug']) ?></p>
            </div>
            <div class="ml-auto flex items-center space-x-2">
                <?php if ($role['is_active']): ?>
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                        <div class="w-1.5 h-1.5 bg-green-400 rounded-full mr-1.5"></div>
                        <?= lang('UserRole.active') ?>
                    </span>
                <?php else: ?>
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                        <div class="w-1.5 h-1.5 bg-red-400 rounded-full mr-1.5"></div>
                        <?= lang('UserRole.inactive') ?>
                    </span>
                <?php endif; ?>
                
                <?php if ($role['is_system']): ?>
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                        <i class="fas fa-shield-alt mr-1"></i>
                        <?= lang('UserRole.systemRole') ?>
                    </span>
                <?php endif; ?>
            </div>
        </div>
        
        <?php if ($role['description']): ?>
            <p class="text-gray-600"><?= esc($role['description']) ?></p>
        <?php endif; ?>
    </div>

    <form action="<?= base_url($rolesRoute . '/' . $role['id'] . '/update') ?>" method="post" id="roleForm" class="space-y-8">
        <?= csrf_field() ?>
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Role Information -->
            <div class="lg:col-span-1">
                <div class="bg-white/70 backdrop-blur-sm rounded-2xl border border-white/30 shadow-lg p-6 sticky top-8">
                    <div class="flex items-center space-x-3 mb-6">
                        <div class="p-3 rounded-xl bg-gradient-to-br from-blue-500 to-blue-600 text-white shadow-lg">
                            <i class="fas fa-edit text-lg"></i>
                        </div>
                        <h2 class="text-xl font-bold text-gray-800"><?= lang('UserRole.editInformation') ?></h2>
                    </div>

                    <div class="space-y-6">
                        <!-- Role Name -->
                        <div>
                            <label for="name" class="block text-sm font-medium text-gray-700 mb-2">
                                <?= lang('UserRole.roleName') ?> <span class="text-red-500">*</span>
                            </label>
                            <input type="text" 
                                   id="name" 
                                   name="name" 
                                   value="<?= old('name', $role['name']) ?>"
                                   class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors duration-200"
                                   placeholder="<?= lang('UserRole.roleNamePlaceholder') ?>"
                                   required>
                        </div>

                        <!-- Role Slug -->
                        <div>
                            <label for="slug" class="block text-sm font-medium text-gray-700 mb-2">
                                <?= lang('UserRole.roleSlug') ?> <span class="text-red-500">*</span>
                            </label>
                            <input type="text" 
                                   id="slug" 
                                   name="slug" 
                                   value="<?= old('slug', $role['slug']) ?>"
                                   class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors duration-200"
                                   placeholder="<?= lang('UserRole.roleSlugPlaceholder') ?>"
                                   required>
                            <p class="text-xs text-gray-500 mt-1"><?= lang('UserRole.roleSlugHint') ?></p>
                        </div>

                        <!-- Description -->
                        <div>
                            <label for="description" class="block text-sm font-medium text-gray-700 mb-2">
                                <?= lang('UserRole.description') ?>
                            </label>
                            <textarea id="description" 
                                      name="description" 
                                      rows="4"
                                      class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors duration-200"
                                      placeholder="<?= lang('UserRole.roleDescriptionPlaceholder') ?>"><?= old('description', $role['description']) ?></textarea>
                        </div>

                        <!-- Status -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2"><?= lang('UserRole.status') ?></label>
                            <div class="flex items-center space-x-4">
                                <label class="flex items-center">
                                    <input type="radio" 
                                           name="is_active" 
                                           value="1" 
                                           <?= old('is_active', $role['is_active']) == '1' ? 'checked' : '' ?>
                                           class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300">
                                    <span class="ml-2 text-sm text-gray-700"><?= lang('UserRole.active') ?></span>
                                </label>
                                <label class="flex items-center">
                                    <input type="radio" 
                                           name="is_active" 
                                           value="0" 
                                           <?= old('is_active', $role['is_active']) == '0' ? 'checked' : '' ?>
                                           class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300">
                                    <span class="ml-2 text-sm text-gray-700"><?= lang('UserRole.inactive') ?></span>
                                </label>
                            </div>
                        </div>

                        <!-- Medical Role -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2"><?= lang('UserRole.roleType') ?></label>
                            <div class="flex items-center space-x-4">
                                <label class="flex items-center">
                                    <input type="radio" 
                                           name="is_medical" 
                                           value="1" 
                                           <?= old('is_medical', $role['is_medical'] ?? 0) == '1' ? 'checked' : '' ?>
                                           class="h-4 w-4 text-green-600 focus:ring-green-500 border-gray-300">
                                    <span class="ml-2 text-sm text-gray-700"><?= lang('UserRole.medicalRoleDoctor') ?></span>
                                </label>
                                <label class="flex items-center">
                                    <input type="radio" 
                                           name="is_medical" 
                                           value="0" 
                                           <?= old('is_medical', $role['is_medical'] ?? 0) == '0' ? 'checked' : '' ?>
                                           class="h-4 w-4 text-green-600 focus:ring-green-500 border-gray-300">
                                    <span class="ml-2 text-sm text-gray-700"><?= lang('UserRole.nonMedicalRole') ?></span>
                                </label>
                            </div>
                            <p class="text-xs text-gray-500 mt-1"><?= lang('UserRole.medicalRoleHint') ?></p>
                        </div>

                        <!-- System Role Warning -->
                        <?php if ($role['is_system']): ?>
                            <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                                <div class="flex">
                                    <div class="flex-shrink-0">
                                        <i class="fas fa-info-circle text-blue-400"></i>
                                    </div>
                                    <div class="ml-3">
                                        <h3 class="text-sm font-medium text-blue-800"><?= lang('UserRole.systemRole') ?></h3>
                                        <div class="mt-2 text-sm text-blue-700">
                                            <p><?= lang('UserRole.systemRoleWarningDescription') ?></p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Permissions -->
            <div class="lg:col-span-2">
                <div class="bg-white/70 backdrop-blur-sm rounded-2xl border border-white/30 shadow-lg p-6">
                    <div class="flex items-center space-x-3 mb-6">
                        <div class="p-3 rounded-xl bg-gradient-to-br from-green-500 to-green-600 text-white shadow-lg">
                            <i class="fas fa-shield-alt text-lg"></i>
                        </div>
                        <h2 class="text-xl font-bold text-gray-800"><?= lang('UserRole.permissions') ?></h2>
                    </div>

                    <!-- Permission Categories -->
                    <div class="space-y-8">
                        <?php if (isset($permissions) && !empty($permissions)): ?>
                            <?php 
                            $categories = \App\Config\Permissions::getCategories();
                            $actionDescriptions = \App\Config\Permissions::getActionDescriptions();
                            ?>
                            <?php foreach ($permissions as $module => $modulePermissions): ?>
                                <div class="bg-gradient-to-r from-gray-50 to-white border border-gray-200 rounded-2xl p-6 hover:shadow-lg transition-all duration-300">
                                    <!-- Module Header -->
                                    <div class="flex items-center justify-between mb-6">
                                        <div class="flex items-center space-x-3">
                                            <div class="p-3 rounded-xl bg-gradient-to-br from-blue-500 to-blue-600 text-white shadow-lg">
                                                <?php
                                                $moduleIcons = [
                                                    'dashboard' => 'fas fa-tachometer-alt',
                                                    'patients' => 'fas fa-user-injured',
                                                    'appointments' => 'fas fa-calendar-alt',
                                                    'examinations' => 'fas fa-stethoscope',
                                                    'treatments' => 'fas fa-tooth',
                                                    'prescriptions' => 'fas fa-prescription-bottle-alt',
                                                    'finance' => 'fas fa-dollar-sign',
                                                    'reports' => 'fas fa-chart-bar',
                                                    'inventory' => 'fas fa-boxes',
                                                    'users' => 'fas fa-users',
                                                    'settings' => 'fas fa-cog'
                                                ];
                                                $icon = $moduleIcons[$module] ?? 'fas fa-folder';
                                                ?>
                                                <i class="<?= $icon ?> text-lg"></i>
                                            </div>
                                            <div>
                                                <h3 class="text-xl font-bold text-gray-800">
                                                    <?= esc($categories[$module] ?? ucwords(str_replace('_', ' ', $module))) ?>
                                                </h3>
                                                <p class="text-sm text-gray-600">
                                                    <?php if (count($modulePermissions) === 1): ?>
                                                        <?= lang('UserRole.singlePermissionAvailable') ?>
                                                    <?php else: ?>
                                                        <?= lang('UserRole.permissionsAvailableCount', [count($modulePermissions)]) ?>
                                                    <?php endif; ?>
                                                </p>
                                            </div>
                                        </div>
                                        <div class="flex items-center space-x-3">
                                            <button type="button" 
                                                    onclick="selectAllPermissions('<?= $module ?>')"
                                                    class="px-4 py-2 text-sm font-medium text-blue-600 bg-blue-50 rounded-lg hover:bg-blue-100 transition-colors duration-200">
                                                <i class="fas fa-check-double mr-1"></i><?= lang('UserRole.selectAll') ?>
                                            </button>
                                            <button type="button" 
                                                    onclick="deselectAllPermissions('<?= $module ?>')"
                                                    class="px-4 py-2 text-sm font-medium text-gray-600 bg-gray-50 rounded-lg hover:bg-gray-100 transition-colors duration-200">
                                                <i class="fas fa-times mr-1"></i><?= lang('UserRole.deselectAll') ?>
                                            </button>
                                        </div>
                                    </div>

                                    <!-- Permissions Grid -->
                                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                                        <?php foreach ($modulePermissions as $permission): ?>
                                            <?php 
                                            $isChecked = false;
                                            if (isset($role_permissions)) {
                                                $isChecked = in_array($permission['id'], $role_permissions);
                                            }
                                            $isChecked = old('permissions') ? in_array($permission['id'], old('permissions')) : $isChecked;
                                            $isDisabled = $role['is_system'] && $permission['is_system'];
                                            ?>
                                            <label class="group flex items-start p-4 border border-gray-200 rounded-xl hover:border-blue-300 hover:bg-blue-50 cursor-pointer transition-all duration-200 <?= $isDisabled ? 'opacity-60' : '' ?>">
                                                <input type="checkbox" 
                                                       name="permissions[]" 
                                                       value="<?= $permission['id'] ?>"
                                                       <?= $isChecked ? 'checked' : '' ?>
                                                       class="h-5 w-5 text-blue-600 focus:ring-blue-500 border-gray-300 rounded mt-0.5 permission-checkbox"
                                                       data-module="<?= $module ?>"
                                                       >
                                                <div class="ml-3 flex-1">
                                                    <div class="flex items-center space-x-2">
                                                        <span class="text-sm font-semibold text-gray-800 group-hover:text-blue-800 <?= $isDisabled ? 'text-gray-400' : '' ?>">
                                                            <?= esc($permission['name']) ?>
                                                        </span>
                                                        <?php if (isset($permission['action']) && isset($actionDescriptions[$permission['action']])): ?>
                                                            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-600">
                                                                <?= esc($permission['action']) ?>
                                                            </span>
                                                        <?php endif; ?>
                                                        <?php if ($isDisabled): ?>
                                                            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                                                <i class="fas fa-lock mr-1"></i><?= lang('UserRole.system') ?>
                                                            </span>
                                                        <?php endif; ?>
                                                    </div>
                                                    <?php if (isset($permission['action']) && isset($actionDescriptions[$permission['action']])): ?>
                                                        <p class="text-xs text-gray-500 mt-1">
                                                            <?= esc($actionDescriptions[$permission['action']]) ?>
                                                        </p>
                                                    <?php endif; ?>
                                                </div>
                                            </label>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="text-center py-12">
                                <div class="mx-auto w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mb-4">
                                    <i class="fas fa-exclamation-triangle text-2xl text-gray-400"></i>
                                </div>
                                <h3 class="text-lg font-medium text-gray-900 mb-2"><?= lang('UserRole.noPermissionsAvailable') ?></h3>
                                <p class="text-gray-500 mb-6"><?= lang('UserRole.syncPermissionsFirstEdit') ?></p>
                                <a href="<?= base_url($rolesRoute . '/sync') ?>" class="inline-flex items-center px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 transition-colors">
                                    <i class="fas fa-sync-alt mr-2"></i><?= lang('UserRole.syncPermissions') ?>
                                </a>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Form Actions -->
        <div class="flex flex-col sm:flex-row justify-end space-y-3 sm:space-y-0 sm:space-x-4 pt-6 border-t border-gray-200">
            <a href="<?= base_url($rolesRoute) ?>" class="inline-flex items-center justify-center px-6 py-3 border border-gray-300 text-gray-700 text-sm font-medium rounded-lg hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition-colors duration-200">
                <?= lang('UserRole.cancel') ?>
            </a>
            <button type="submit" class="inline-flex items-center justify-center px-6 py-3 bg-gradient-to-r from-blue-600 to-blue-700 text-white text-sm font-medium rounded-lg hover:from-blue-700 hover:to-blue-800 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition-all duration-200 shadow-lg hover:shadow-xl">
                <i class="fas fa-save mr-2"></i><?= lang('UserRole.updateRole') ?>
            </button>
        </div>
    </form>
</div>

<!-- JavaScript for Role Editing -->
<script>
// Auto-generate slug from name
document.getElementById('name').addEventListener('input', function() {
    const name = this.value;
    const slug = name.toLowerCase()
                   .replace(/[^a-z0-9\s]/g, '')
                   .replace(/\s+/g, '_')
                   .trim();
    document.getElementById('slug').value = slug;
});

// Select all permissions for a module
function selectAllPermissions(module) {
    const checkboxes = document.querySelectorAll(`input[data-module="${module}"]:not([disabled])`);
    checkboxes.forEach(checkbox => {
        checkbox.checked = true;
    });
}

// Deselect all permissions for a module
function deselectAllPermissions(module) {
    const checkboxes = document.querySelectorAll(`input[data-module="${module}"]:not([disabled])`);
    checkboxes.forEach(checkbox => {
        checkbox.checked = false;
    });
}

// Form validation
document.getElementById('roleForm').addEventListener('submit', function(e) {
    const name = document.getElementById('name').value.trim();
    const slug = document.getElementById('slug').value.trim();
    
    if (!name || !slug) {
        e.preventDefault();
        alert('<?= esc(lang('UserRole.requiredFieldsAlert'), 'js') ?>');
        return;
    }
});
// Ensure checkboxes work properly
document.addEventListener('DOMContentLoaded', function() {
    // Add event listeners to all permission checkboxes to ensure they work properly
    const permissionCheckboxes = document.querySelectorAll('input[name="permissions[]"]');
    permissionCheckboxes.forEach(function(checkbox) {
        checkbox.addEventListener('click', function(e) {
            // Don't prevent the default behavior, let the checkbox toggle normally
            console.log('Checkbox clicked:', this.value, 'Checked:', this.checked);
        });
    });
});
</script>

<?= $this->endSection() ?>
