<?php
$layout = session()->get('global_mode') ? 'layouts/main_control_plane' : 'layouts/main_auth';
$isRtl = !empty($is_rtl);
?>
<?= $this->extend($layout) ?>

<?= $this->section('content') ?>
<div class="v3-operational-view">
<div class="v3-operational-body">
    <!-- Page Header -->
    <div class="mb-8">
        <div class="flex flex-col lg:flex-row lg:justify-between lg:items-center lg:space-y-0">
            <div>
                <h1 class="v3-title font-bold mb-3"><?= lang('UserRole.createNewRoleTitle') ?></h1>
                <p class="text-lg"><?= lang('UserRole.createNewRoleSubtitle') ?></p>
            </div>
            <div class="flex flex-col gap-3 sm:flex-row sm:gap-4">
                <a href="<?= workflow_back($rolesRoute ?? 'roles', $return_to ?? null) ?>" class="inline-flex items-center px-4 py-2 text-sm font-medium rounded-lg focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 duration-200">
                    <i class="fas fa-arrow-left rtl-back-icon"></i>
                    <span class="<?= $isRtl ? 'mr-2' : 'ml-2' ?>"><?= lang('UserRole.backToRoles') ?></span>
                </a>
            </div>
        </div>
    </div>

    <!-- Flash Messages -->
    <?php if (session()->getFlashdata('validation')): ?>
        <div class="mb-6 bg-red-50 border border-red-200 p-4">
            <div class="flex">
                <div class="flex-shrink-0">
                    <i class="fas fa-exclamation-circle text-red-400 text-xl"></i>
                </div>
                <div class="<?= $isRtl ? 'mr-3' : 'ml-3' ?>">
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
        <div class="mb-6 bg-red-50 border border-red-200 p-4">
            <div class="flex">
                <div class="flex-shrink-0">
                    <i class="fas fa-exclamation-circle text-red-400 text-xl"></i>
                </div>
                <div class="<?= $isRtl ? 'mr-3' : 'ml-3' ?>">
                    <h3 class="text-sm font-medium text-red-800"><?= lang('UserRole.error') ?></h3>
                    <div class="mt-2 text-sm text-red-700">
                        <p><?= session()->getFlashdata('error') ?></p>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <form action="<?= base_url($rolesRoute . '/store') ?>" method="post" id="roleForm" class="v3-flow-form ">
        <?= csrf_field() ?>
        <?php if (!empty($return_to)): ?>
            <input type="hidden" name="return_to" value="<?= esc($return_to) ?>">
        <?php endif; ?>
        <div class="grid grid-cols-1 lg:grid-cols-3">
            <!-- Role Information -->
            <div class="lg:col-span-1">
                <div class="border sticky top-8">
                    <div class="flex items-center gap-3 mb-6">
                        <div class="p-3">
                            <i class="fas fa-user-tag text-lg"></i>
                        </div>
                        <h2 class="text-xl font-bold"><?= lang('UserRole.roleInformation') ?></h2>
                    </div>

                    <div class="">
                        <!-- Role Name -->
                        <div>
                            <label for="name" class="block text-sm font-medium mb-2">
                                <?= lang('UserRole.roleName') ?> <span class="text-red-500">*</span>
                            </label>
                            <input type="text"
                                   id="name"
                                   name="name"
                                   value="<?= old('name') ?>"
                                   class="w-full px-4 py-3 border rounded-lg focus:ring-2 transition-colors duration-200"
                                   placeholder="<?= lang('UserRole.roleNamePlaceholder') ?>"
                                   required>
                        </div>

                        <!-- Role Slug -->
                        <div>
                            <label for="slug" class="block text-sm font-medium mb-2">
                                <?= lang('UserRole.roleSlug') ?> <span class="text-red-500">*</span>
                            </label>
                            <input type="text"
                                   id="slug"
                                   name="slug"
                                   value="<?= old('slug') ?>"
                                   class="w-full px-4 py-3 border rounded-lg focus:ring-2 transition-colors duration-200"
                                   placeholder="<?= lang('UserRole.roleSlugPlaceholder') ?>"
                                   required>
                            <p class="text-xs mt-1"><?= lang('UserRole.roleSlugHint') ?></p>
                        </div>

                        <!-- Description -->
                        <div>
                            <label for="description" class="block text-sm font-medium mb-2">
                                <?= lang('UserRole.description') ?>
                            </label>
                            <textarea id="description"
                                      name="description"
                                      rows="4"
                                      class="w-full px-4 py-3 border rounded-lg focus:ring-2 transition-colors duration-200"
                                      placeholder="<?= lang('UserRole.roleDescriptionPlaceholder') ?>"><?= old('description') ?></textarea>
                        </div>

                        <!-- Status -->
                        <div>
                            <label class="block text-sm font-medium mb-2"><?= lang('UserRole.status') ?></label>
                            <div class="flex flex-wrap items-center gap-x-4 gap-y-2">
                                <label class="flex items-center">
                                    <input type="radio"
                                           name="is_active"
                                           value="1"
                                           <?= old('is_active', '1') == '1' ? 'checked' : '' ?>
                                           class="h-4 w-4">
                                    <span class="<?= $isRtl ? 'mr-2' : 'ml-2' ?> text-sm"><?= lang('UserRole.active') ?></span>
                                </label>
                                <label class="flex items-center">
                                    <input type="radio"
                                           name="is_active"
                                           value="0"
                                           <?= old('is_active') == '0' ? 'checked' : '' ?>
                                           class="h-4 w-4">
                                    <span class="<?= $isRtl ? 'mr-2' : 'ml-2' ?> text-sm"><?= lang('UserRole.inactive') ?></span>
                                </label>
                            </div>
                        </div>

                        <!-- Medical Role -->
                        <div>
                            <label class="block text-sm font-medium mb-2"><?= lang('UserRole.roleType') ?></label>
                            <div class="flex flex-wrap items-center gap-x-4 gap-y-2">
                                <label class="flex items-center">
                                    <input type="radio"
                                           name="is_medical"
                                           value="1"
                                           <?= old('is_medical') == '1' ? 'checked' : '' ?>
                                           class="h-4 w-4 text-green-600 focus:ring-green-500">
                                    <span class="<?= $isRtl ? 'mr-2' : 'ml-2' ?> text-sm"><?= lang('UserRole.medicalRoleDoctor') ?></span>
                                </label>
                                <label class="flex items-center">
                                    <input type="radio"
                                           name="is_medical"
                                           value="0"
                                           <?= old('is_medical', '0') == '0' ? 'checked' : '' ?>
                                           class="h-4 w-4 text-green-600 focus:ring-green-500">
                                    <span class="<?= $isRtl ? 'mr-2' : 'ml-2' ?> text-sm"><?= lang('UserRole.nonMedicalRole') ?></span>
                                </label>
                            </div>
                            <p class="text-xs mt-1"><?= lang('UserRole.medicalRoleHint') ?></p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Permissions -->
            <div class="lg:col-span-2">
                <div class="border">
                    <div class="flex items-center gap-3 mb-6">
                        <div class="p-3">
                            <i class="fas fa-shield-alt text-lg"></i>
                        </div>
                        <h2 class="text-xl font-bold"><?= lang('UserRole.permissions') ?></h2>
                    </div>

                    <!-- Permission Categories -->
                    <div class="">
                        <?php if (isset($permissions) && !empty($permissions)): ?>
                            <?php
                            $categories = \App\Config\Permissions::getCategories();
                            $actionDescriptions = \App\Config\Permissions::getActionDescriptions();
                            $actionLabels = \App\Config\Permissions::getActionLabels();
                            ?>
                            <?php foreach ($permissions as $module => $modulePermissions): ?>
                                <div class="border">
                                    <!-- Module Header -->
                                    <div class="flex items-center justify-between mb-6">
                                        <div class="flex items-center gap-3">
                                            <div class="p-3">
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
                                                <h3 class="text-xl font-bold">
                                                    <?= esc($categories[$module] ?? ucwords(str_replace('_', ' ', $module))) ?>
                                                </h3>
                                                <p class="text-sm">
                                                    <?php if (count($modulePermissions) === 1): ?>
                                                        <?= lang('UserRole.singlePermissionAvailable') ?>
                                                    <?php else: ?>
                                                        <?= lang('UserRole.permissionsAvailableCount', [count($modulePermissions)]) ?>
                                                    <?php endif; ?>
                                                </p>
                                            </div>
                                        </div>
                                        <div class="flex items-center gap-3">
                                            <button type="button"
                                                    onclick="selectAllPermissions('<?= $module ?>')"
                                                    class="px-4 py-2 text-sm font-medium rounded-lg transition-colors duration-200">
                                                <i class="fas fa-check-double"></i>
                                                <span class="<?= $isRtl ? 'mr-1' : 'ml-1' ?>"><?= lang('UserRole.selectAll') ?></span>
                                            </button>
                                            <button type="button"
                                                    onclick="deselectAllPermissions('<?= $module ?>')"
                                                    class="px-4 py-2 text-sm font-medium rounded-lg transition-colors duration-200">
                                                <i class="fas fa-times"></i>
                                                <span class="<?= $isRtl ? 'mr-1' : 'ml-1' ?>"><?= lang('UserRole.deselectAll') ?></span>
                                            </button>
                                        </div>
                                    </div>

                                    <!-- Permissions Grid -->
                                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                                        <?php foreach ($modulePermissions as $permission): ?>
                                            <?php $permissionAction = $permission['action'] ?? null; ?>
                                            <label class="flex items-start p-4 border cursor-pointer duration-200">
                                                <input type="checkbox"
                                                       name="permissions[]"
                                                       value="<?= $permission['id'] ?>"
                                                       <?= in_array($permission['id'], old('permissions', [])) ? 'checked' : '' ?>
                                                       class="h-5 w-5 rounded mt-0.5 permission-checkbox"
                                                       data-module="<?= $module ?>">
                                                <div class="<?= $isRtl ? 'mr-3' : 'ml-3' ?> flex-1">
                                                    <div class="flex items-center gap-2">
                                                        <span class="text-sm font-semibold">
                                                            <?= esc($actionLabels[$permissionAction] ?? $permission['name']) ?>
                                                        </span>
                                                    </div>
                                                    <?php if ($permissionAction && isset($actionDescriptions[$permissionAction])): ?>
                                                        <p class="text-xs mt-1">
                                                            <?= esc($actionDescriptions[$permissionAction]) ?>
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
                                <div class="mx-auto w-16 h-16 flex items-center justify-center mb-4">
                                    <i class="fas fa-exclamation-triangle v3-section-title"></i>
                                </div>
                                <h3 class="text-lg font-medium mb-2"><?= lang('UserRole.noPermissionsAvailable') ?></h3>
                                <p class="mb-6"><?= lang('UserRole.syncPermissionsFirstCreate') ?></p>
                                <a href="<?= base_url($rolesRoute . '/sync') ?>" class="inline-flex items-center px-4 py-2 text-sm font-medium rounded-lg transition-colors">
                                    <i class="fas fa-sync-alt"></i>
                                    <span class="<?= $isRtl ? 'mr-2' : 'ml-2' ?>"><?= lang('UserRole.syncPermissions') ?></span>
                                </a>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Form Actions -->
        <div class="flex flex-col justify-end gap-3 pt-6 border-t sm:flex-row sm:gap-4">
            <a href="<?= workflow_back($rolesRoute ?? 'roles', $return_to ?? null) ?>" class="inline-flex items-center justify-center px-6 py-3 border text-sm font-medium rounded-lg focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition-colors duration-200">
                <?= lang('UserRole.cancel') ?>
            </a>
            <button type="submit" class="inline-flex items-center justify-center px-6 py-3 text-sm font-medium rounded-lg focus:outline-none focus:ring-2 focus:ring-offset-2 duration-200">
                <i class="fas fa-save"></i>
                <span class="<?= $isRtl ? 'mr-2' : 'ml-2' ?>"><?= lang('UserRole.createRole') ?></span>
            </button>
        </div>
    </form>
</div>

<!-- JavaScript for Role Creation -->
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
    const checkboxes = document.querySelectorAll(`input[data-module="${module}"]`);
    checkboxes.forEach(checkbox => {
        checkbox.checked = true;
    });
}

// Deselect all permissions for a module
function deselectAllPermissions(module) {
    const checkboxes = document.querySelectorAll(`input[data-module="${module}"]`);
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

    // Check if at least one permission is selected
    const selectedPermissions = document.querySelectorAll('input[name="permissions[]"]:checked');
    if (selectedPermissions.length === 0) {
        e.preventDefault();
        alert('<?= esc(lang('UserRole.selectAtLeastOnePermissionAlert'), 'js') ?>');
        return;
    }
});
</script>

</div>
<?= $this->endSection() ?>
