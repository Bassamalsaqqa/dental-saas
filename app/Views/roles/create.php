<?= $this->extend('layouts/main_auth') ?>

<?= $this->section('content') ?>
<div class="container mx-auto px-6 py-8">
    <!-- Page Header -->
    <div class="mb-8">
        <div class="flex flex-col lg:flex-row lg:justify-between lg:items-center space-y-6 lg:space-y-0">
            <div>
                <h1 class="text-4xl font-bold text-gray-800 mb-3">Create New Role</h1>
                <p class="text-gray-600 text-lg">Define a new role with specific permissions</p>
            </div>
            <div class="flex flex-col sm:flex-row space-y-3 sm:space-y-0 sm:space-x-4">
                <a href="<?= base_url('roles') ?>" class="inline-flex items-center px-4 py-2 bg-gray-600 text-white text-sm font-medium rounded-lg hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition-all duration-200 shadow-lg hover:shadow-xl">
                    <i class="fas fa-arrow-left mr-2"></i>Back to Roles
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
                    <h3 class="text-sm font-medium text-red-800">Validation Errors</h3>
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
                    <h3 class="text-sm font-medium text-red-800">Error</h3>
                    <div class="mt-2 text-sm text-red-700">
                        <p><?= session()->getFlashdata('error') ?></p>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <form action="<?= base_url('roles/store') ?>" method="post" id="roleForm" class="space-y-8">
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Role Information -->
            <div class="lg:col-span-1">
                <div class="bg-white/70 backdrop-blur-sm rounded-2xl border border-white/30 shadow-lg p-6 sticky top-8">
                    <div class="flex items-center space-x-3 mb-6">
                        <div class="p-3 rounded-xl bg-gradient-to-br from-blue-500 to-blue-600 text-white shadow-lg">
                            <i class="fas fa-user-tag text-lg"></i>
                        </div>
                        <h2 class="text-xl font-bold text-gray-800">Role Information</h2>
                    </div>

                    <div class="space-y-6">
                        <!-- Role Name -->
                        <div>
                            <label for="name" class="block text-sm font-medium text-gray-700 mb-2">
                                Role Name <span class="text-red-500">*</span>
                            </label>
                            <input type="text" 
                                   id="name" 
                                   name="name" 
                                   value="<?= old('name') ?>"
                                   class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors duration-200"
                                   placeholder="e.g., Practice Manager"
                                   required>
                        </div>

                        <!-- Role Slug -->
                        <div>
                            <label for="slug" class="block text-sm font-medium text-gray-700 mb-2">
                                Role Slug <span class="text-red-500">*</span>
                            </label>
                            <input type="text" 
                                   id="slug" 
                                   name="slug" 
                                   value="<?= old('slug') ?>"
                                   class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors duration-200"
                                   placeholder="e.g., practice_manager"
                                   required>
                            <p class="text-xs text-gray-500 mt-1">Used internally (lowercase, underscores only)</p>
                        </div>

                        <!-- Description -->
                        <div>
                            <label for="description" class="block text-sm font-medium text-gray-700 mb-2">
                                Description
                            </label>
                            <textarea id="description" 
                                      name="description" 
                                      rows="4"
                                      class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors duration-200"
                                      placeholder="Describe the role's purpose and responsibilities"><?= old('description') ?></textarea>
                        </div>

                        <!-- Status -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                            <div class="flex items-center space-x-4">
                                <label class="flex items-center">
                                    <input type="radio" 
                                           name="is_active" 
                                           value="1" 
                                           <?= old('is_active', '1') == '1' ? 'checked' : '' ?>
                                           class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300">
                                    <span class="ml-2 text-sm text-gray-700">Active</span>
                                </label>
                                <label class="flex items-center">
                                    <input type="radio" 
                                           name="is_active" 
                                           value="0" 
                                           <?= old('is_active') == '0' ? 'checked' : '' ?>
                                           class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300">
                                    <span class="ml-2 text-sm text-gray-700">Inactive</span>
                                </label>
                            </div>
                        </div>

                        <!-- Medical Role -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Role Type</label>
                            <div class="flex items-center space-x-4">
                                <label class="flex items-center">
                                    <input type="radio" 
                                           name="is_medical" 
                                           value="1" 
                                           <?= old('is_medical') == '1' ? 'checked' : '' ?>
                                           class="h-4 w-4 text-green-600 focus:ring-green-500 border-gray-300">
                                    <span class="ml-2 text-sm text-gray-700">Medical Role (Doctor)</span>
                                </label>
                                <label class="flex items-center">
                                    <input type="radio" 
                                           name="is_medical" 
                                           value="0" 
                                           <?= old('is_medical', '0') == '0' ? 'checked' : '' ?>
                                           class="h-4 w-4 text-green-600 focus:ring-green-500 border-gray-300">
                                    <span class="ml-2 text-sm text-gray-700">Non-Medical Role</span>
                                </label>
                            </div>
                            <p class="text-xs text-gray-500 mt-1">Medical roles are for doctors and medical staff who can access patient medical records</p>
                        </div>
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
                        <h2 class="text-xl font-bold text-gray-800">Permissions</h2>
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
                                                    <?= count($modulePermissions) ?> permission<?= count($modulePermissions) !== 1 ? 's' : '' ?> available
                                                </p>
                                            </div>
                                        </div>
                                        <div class="flex items-center space-x-3">
                                            <button type="button" 
                                                    onclick="selectAllPermissions('<?= $module ?>')"
                                                    class="px-4 py-2 text-sm font-medium text-blue-600 bg-blue-50 rounded-lg hover:bg-blue-100 transition-colors duration-200">
                                                <i class="fas fa-check-double mr-1"></i>Select All
                                            </button>
                                            <button type="button" 
                                                    onclick="deselectAllPermissions('<?= $module ?>')"
                                                    class="px-4 py-2 text-sm font-medium text-gray-600 bg-gray-50 rounded-lg hover:bg-gray-100 transition-colors duration-200">
                                                <i class="fas fa-times mr-1"></i>Deselect All
                                            </button>
                                        </div>
                                    </div>

                                    <!-- Permissions Grid -->
                                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                                        <?php foreach ($modulePermissions as $permission): ?>
                                            <label class="group flex items-start p-4 border border-gray-200 rounded-xl hover:border-blue-300 hover:bg-blue-50 cursor-pointer transition-all duration-200">
                                                <input type="checkbox" 
                                                       name="permissions[]" 
                                                       value="<?= $permission['id'] ?>"
                                                       <?= in_array($permission['id'], old('permissions', [])) ? 'checked' : '' ?>
                                                       class="h-5 w-5 text-blue-600 focus:ring-blue-500 border-gray-300 rounded mt-0.5 permission-checkbox"
                                                       data-module="<?= $module ?>">
                                                <div class="ml-3 flex-1">
                                                    <div class="flex items-center space-x-2">
                                                        <span class="text-sm font-semibold text-gray-800 group-hover:text-blue-800">
                                                            <?= esc($permission['name']) ?>
                                                        </span>
                                                        <?php if (isset($permission['action']) && isset($actionDescriptions[$permission['action']])): ?>
                                                            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-600">
                                                                <?= esc($permission['action']) ?>
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
                                <h3 class="text-lg font-medium text-gray-900 mb-2">No permissions available</h3>
                                <p class="text-gray-500 mb-6">Please sync permissions first to create roles.</p>
                                <a href="<?= base_url('roles/sync') ?>" class="inline-flex items-center px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 transition-colors">
                                    <i class="fas fa-sync-alt mr-2"></i>Sync Permissions
                                </a>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Form Actions -->
        <div class="flex flex-col sm:flex-row justify-end space-y-3 sm:space-y-0 sm:space-x-4 pt-6 border-t border-gray-200">
            <a href="<?= base_url('roles') ?>" class="inline-flex items-center justify-center px-6 py-3 border border-gray-300 text-gray-700 text-sm font-medium rounded-lg hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition-colors duration-200">
                Cancel
            </a>
            <button type="submit" class="inline-flex items-center justify-center px-6 py-3 bg-gradient-to-r from-blue-600 to-blue-700 text-white text-sm font-medium rounded-lg hover:from-blue-700 hover:to-blue-800 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition-all duration-200 shadow-lg hover:shadow-xl">
                <i class="fas fa-save mr-2"></i>Create Role
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
        alert('Please fill in all required fields.');
        return;
    }
    
    // Check if at least one permission is selected
    const selectedPermissions = document.querySelectorAll('input[name="permissions[]"]:checked');
    if (selectedPermissions.length === 0) {
        e.preventDefault();
        alert('Please select at least one permission for this role.');
        return;
    }
});
</script>

<?= $this->endSection() ?>