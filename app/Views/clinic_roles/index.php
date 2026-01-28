<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="container mx-auto px-6 py-8">
    <!-- Page Header -->
    <div class="mb-8">
        <div class="flex flex-col lg:flex-row lg:justify-between lg:items-center space-y-6 lg:space-y-0">
            <div>
                <h1 class="text-4xl font-bold text-gray-800 mb-3"><?= esc($title ?? 'Clinic Roles') ?></h1>
                <p class="text-gray-600 text-lg">Manage roles specific to <?= esc(session()->get('active_clinic_name') ?? 'this clinic') ?></p>
            </div>
            <div class="flex flex-col sm:flex-row space-y-3 sm:space-y-0 sm:space-x-4">
                <a href="<?= base_url('clinic/roles/create') ?>" class="inline-flex items-center px-4 py-2 bg-gradient-to-r from-blue-600 to-blue-700 text-white text-sm font-medium rounded-lg hover:from-blue-700 hover:to-blue-800 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition-all duration-200 shadow-lg hover:shadow-xl">
                    <i class="fas fa-plus mr-2"></i>New Role
                </a>
            </div>
        </div>
    </div>

    <!-- Flash Messages -->
    <?php if (session()->getFlashdata('success')): ?>
        <div class="mb-6 bg-green-50 border border-green-200 rounded-xl p-4 shadow-sm">
            <div class="flex">
                <div class="flex-shrink-0">
                    <i class="fas fa-check-circle text-green-400 text-xl"></i>
                </div>
                <div class="ml-3">
                    <h3 class="text-sm font-medium text-green-800">Success</h3>
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
                    <h3 class="text-sm font-medium text-red-800">Error</h3>
                    <div class="mt-2 text-sm text-red-700">
                        <p><?= session()->getFlashdata('error') ?></p>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <!-- Roles Grid -->
    <?php if (!empty($roles)): ?>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <?php foreach ($roles as $role): ?>
                <div class="bg-white/70 backdrop-blur-sm rounded-2xl border border-white/30 shadow-lg hover:shadow-xl transition-all duration-300 group">
                    <div class="p-6">
                        <!-- Role Header -->
                        <div class="flex items-start justify-between mb-4">
                            <div class="flex items-center space-x-3">
                                <div class="p-3 rounded-xl bg-gradient-to-br from-indigo-500 to-purple-600 text-white shadow-lg group-hover:scale-110 transition-transform duration-300">
                                    <i class="fas fa-user-tag text-lg"></i>
                                </div>
                                <div>
                                    <h3 class="text-lg font-bold text-gray-800 group-hover:text-indigo-600 transition-colors duration-200">
                                        <?= esc($role['name']) ?>
                                    </h3>
                                    <p class="text-sm text-gray-500"><?= esc($role['slug']) ?></p>
                                </div>
                            </div>
                            
                            <!-- Status Badge -->
                            <div class="flex items-center space-x-2">
                                <?php if ($role['is_active']): ?>
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                        <div class="w-1.5 h-1.5 bg-green-400 rounded-full mr-1.5"></div>
                                        Active
                                    </span>
                                <?php else: ?>
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                        <div class="w-1.5 h-1.5 bg-red-400 rounded-full mr-1.5"></div>
                                        Inactive
                                    </span>
                                <?php endif; ?>

                                <?php if (isset($role['is_medical']) && $role['is_medical']): ?>
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                        <i class="fas fa-stethoscope mr-1"></i>
                                        Medical
                                    </span>
                                <?php endif; ?>
                            </div>
                        </div>

                        <!-- Role Description -->
                        <p class="text-gray-600 text-sm mb-4 line-clamp-2 min-h-[40px]">
                            <?= esc($role['description'] ?? 'Custom clinic role') ?>
                        </p>

                        <!-- Role Stats -->
                        <div class="flex items-center justify-between mb-4 text-sm text-gray-500">
                            <span class="flex items-center">
                                <i class="fas fa-key mr-2 text-indigo-400"></i>
                                <?= $role['permission_count'] ?? count($role['permissions'] ?? []) ?> permissions
                            </span>
                        </div>

                        <!-- Action Buttons -->
                        <div class="flex items-center justify-between pt-4 border-t border-gray-200">
                            <div class="flex items-center space-x-2">
                                <a href="<?= base_url('clinic/roles/' . $role['id'] . '/edit') ?>" 
                                   class="inline-flex items-center px-3 py-1.5 text-xs font-medium text-blue-600 bg-blue-50 rounded-lg hover:bg-blue-100 transition-colors duration-200">
                                    <i class="fas fa-edit mr-1"></i>
                                    Edit
                                </a>
                                
                                <button onclick="deleteRole(<?= $role['id'] ?>)" 
                                        class="inline-flex items-center px-3 py-1.5 text-xs font-medium text-red-600 bg-red-50 rounded-lg hover:bg-red-100 transition-colors duration-200">
                                    <i class="fas fa-trash mr-1"></i>
                                    Delete
                                </button>
                            </div>
                            
                            <button onclick="toggleStatus(<?= $role['id'] ?>, <?= $role['is_active'] ? 0 : 1 ?>)" 
                                    class="inline-flex items-center px-3 py-1.5 text-xs font-medium <?= $role['is_active'] ? 'text-orange-600 bg-orange-50 hover:bg-orange-100' : 'text-green-600 bg-green-50 hover:bg-green-100' ?> rounded-lg transition-colors duration-200">
                                <i class="fas <?= $role['is_active'] ? 'fa-pause' : 'fa-play' ?> mr-1"></i>
                                <?= $role['is_active'] ? 'Deactivate' : 'Activate' ?>
                            </button>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <div class="text-center py-12">
            <div class="mx-auto w-24 h-24 bg-gray-100 rounded-full flex items-center justify-center mb-4">
                <i class="fas fa-user-tag text-3xl text-gray-400"></i>
            </div>
            <h3 class="text-lg font-medium text-gray-900 mb-2">No roles found</h3>
            <p class="text-gray-500 mb-6">Create your first custom role for this clinic.</p>
            <a href="<?= base_url('clinic/roles/create') ?>" class="inline-flex items-center px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 transition-colors">
                <i class="fas fa-plus mr-2"></i>Create Role
            </a>
        </div>
    <?php endif; ?>
</div>

<script>
function deleteRole(roleId) {
    if (!confirm('Remove this clinic role? This action cannot be undone.')) {
        return;
    }

    fetch(`<?= base_url('clinic/roles') ?>/${roleId}`, {
        method: 'DELETE',
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            '<?= csrf_header() ?>': '<?= csrf_hash() ?>'
        }
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            location.reload();
        } else {
            alert(data.message || 'Unable to delete role');
        }
    })
    .catch(() => alert('Failed to delete role.'));
}

function toggleStatus(roleId, newStatus) {
    fetch(`<?= base_url('clinic/roles') ?>/${roleId}/toggle-status`, {
        method: 'POST',
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Content-Type': 'application/json',
            '<?= csrf_header() ?>': '<?= csrf_hash() ?>'
        },
        body: JSON.stringify({ status: newStatus })
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            location.reload();
        } else {
            alert(data.message || 'Unable to update status');
        }
    })
    .catch(() => alert('Unable to update role status.'));
}
</script>

<?= $this->endSection() ?>