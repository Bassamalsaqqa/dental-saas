<?= $this->extend('layouts/main_auth') ?>

<?= $this->section('content') ?>
<div class="container mx-auto px-6 py-8">
    <!-- Page Header -->
    <div class="mb-8">
        <div class="flex flex-col lg:flex-row lg:justify-between lg:items-center space-y-6 lg:space-y-0">
            <div>
                <h1 class="text-4xl font-bold text-gray-800 mb-3"><?= esc($title ?? 'Clinic Role') ?></h1>
                <p class="text-gray-600 text-lg">Define a new custom role for your clinic</p>
            </div>
            <div class="flex flex-col sm:flex-row space-y-3 sm:space-y-0 sm:space-x-4">
                <a href="<?= base_url('clinic/roles') ?>" class="inline-flex items-center px-4 py-2 bg-gray-600 text-white text-sm font-medium rounded-lg hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition-all duration-200 shadow-lg hover:shadow-xl">
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

    <form action="<?= esc($action) ?>" method="post" id="roleForm" class="space-y-8">
        <?= csrf_field() ?>
        <?php if (isset($method) && strtolower($method) !== 'post'): ?>
            <input type="hidden" name="_method" value="<?= esc($method) ?>">
        <?php endif; ?>

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
                                   value="<?= old('name', $role['name'] ?? '') ?>"
                                   class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors duration-200"
                                   placeholder="e.g., Weekend Staff"
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
                                   value="<?= old('slug', $role['slug'] ?? '') ?>"
                                   class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors duration-200"
                                   placeholder="e.g., weekend_staff"
                                   required>
                            <p class="text-xs text-gray-500 mt-1">Lowercase, underscores only</p>
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
                                      placeholder="Describe the role's purpose"><?= old('description', $role['description'] ?? '') ?></textarea>
                        </div>

                        <!-- Role Type -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Role Type</label>
                            <div class="space-y-3">
                                <label class="flex items-center p-3 border border-gray-200 rounded-xl hover:bg-gray-50 cursor-pointer transition-colors">
                                    <input type="radio" 
                                           name="is_medical" 
                                           value="1" 
                                           <?= old('is_medical', $role['is_medical'] ?? '0') == '1' ? 'checked' : '' ?>
                                           class="h-4 w-4 text-green-600 focus:ring-green-500 border-gray-300">
                                    <div class="ml-3">
                                        <span class="block text-sm font-medium text-gray-900">Medical Role</span>
                                        <span class="block text-xs text-gray-500">Access to patient medical records</span>
                                    </div>
                                </label>
                                <label class="flex items-center p-3 border border-gray-200 rounded-xl hover:bg-gray-50 cursor-pointer transition-colors">
                                    <input type="radio" 
                                           name="is_medical" 
                                           value="0" 
                                           <?= old('is_medical', $role['is_medical'] ?? '0') == '0' ? 'checked' : '' ?>
                                           class="h-4 w-4 text-green-600 focus:ring-green-500 border-gray-300">
                                    <div class="ml-3">
                                        <span class="block text-sm font-medium text-gray-900">Non-Medical Role</span>
                                        <span class="block text-xs text-gray-500">Administrative or support tasks</span>
                                    </div>
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Permissions -->
            <div class="lg:col-span-2">
                <div class="bg-white/70 backdrop-blur-sm rounded-2xl border border-white/30 shadow-lg p-6">
                    <div class="flex items-center justify-between mb-6">
                        <div class="flex items-center space-x-3">
                            <div class="p-3 rounded-xl bg-gradient-to-br from-green-500 to-green-600 text-white shadow-lg">
                                <i class="fas fa-shield-alt text-lg"></i>
                            </div>
                            <h2 class="text-xl font-bold text-gray-800">Permissions</h2>
                        </div>
                    </div>

                    <div class="space-y-6">
                        <?php
                        $selectedPermissions = old('permissions', $selectedPermissions ?? []);
                        ?>
                        <?php foreach ($permissions as $module => $modulePermissions): ?>
                            <div class="border border-gray-200 rounded-xl overflow-hidden">
                                <div class="bg-gray-50 px-4 py-3 border-b border-gray-200 flex justify-between items-center">
                                    <h3 class="font-bold text-gray-700 capitalize">
                                        <?= esc($categories[$module] ?? ucfirst($module)) ?>
                                    </h3>
                                    <div class="space-x-2 text-sm">
                                        <button type="button" class="text-blue-600 hover:text-blue-800 font-medium" onclick="selectAll('<?= $module ?>')">Select All</button>
                                        <span class="text-gray-300">|</span>
                                        <button type="button" class="text-gray-600 hover:text-gray-800 font-medium" onclick="deselectAll('<?= $module ?>')">Clear</button>
                                    </div>
                                </div>
                                <div class="p-4 grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3">
                                    <?php foreach ($modulePermissions as $permission): ?>
                                        <label class="flex items-start p-2 rounded-lg hover:bg-gray-50 cursor-pointer transition-colors">
                                            <input type="checkbox" 
                                                   name="permissions[]" 
                                                   value="<?= $permission['id'] ?>"
                                                   <?= in_array($permission['id'], $selectedPermissions) ? 'checked' : '' ?>
                                                   class="mt-1 h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded permission-checkbox"
                                                   data-module="<?= $module ?>">
                                            <span class="ml-2 text-sm text-gray-700"><?= esc($permission['name']) ?></span>
                                        </label>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Form Actions -->
        <div class="flex flex-col sm:flex-row justify-end space-y-3 sm:space-y-0 sm:space-x-4 pt-6 border-t border-gray-200">
            <a href="<?= base_url('clinic/roles') ?>" class="inline-flex items-center justify-center px-6 py-3 border border-gray-300 text-gray-700 text-sm font-medium rounded-lg hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition-colors duration-200">
                Cancel
            </a>
            <button type="submit" class="inline-flex items-center justify-center px-6 py-3 bg-gradient-to-r from-blue-600 to-blue-700 text-white text-sm font-medium rounded-lg hover:from-blue-700 hover:to-blue-800 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition-all duration-200 shadow-lg hover:shadow-xl">
                <i class="fas fa-save mr-2"></i><?= esc($submitLabel ?? 'Save Role') ?>
            </button>
        </div>
    </form>
</div>

<?= $this->section('scripts') ?>
<script>
    document.getElementById('name').addEventListener('input', function () {
        const slug = this.value.toLowerCase()
            .replace(/[^a-z0-9\s]/g, '')
            .trim()
            .replace(/\s+/g, '_');
        document.getElementById('slug').value = slug;
    });

    function selectAll(module) {
        document.querySelectorAll(`input[data-module="${module}"]`).forEach(el => el.checked = true);
    }

    function deselectAll(module) {
        document.querySelectorAll(`input[data-module="${module}"]`).forEach(el => el.checked = false);
    }
</script>
<?= $this->endSection() ?>

<?= $this->endSection() ?>
