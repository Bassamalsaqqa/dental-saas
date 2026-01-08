<?= $this->extend('layouts/main_auth') ?>

<?= $this->section('content') ?>
<div class="container mx-auto px-6 py-8">
    <!-- Page Header -->
    <div class="mb-8">
        <div class="flex flex-col lg:flex-row lg:justify-between lg:items-center space-y-6 lg:space-y-0">
            <div>
                <h1 class="text-4xl font-bold text-gray-800 mb-3">Doctor Management</h1>
                <p class="text-gray-600 text-lg">Manage medical professionals and their information</p>
            </div>
            <div class="flex flex-col sm:flex-row space-y-3 sm:space-y-0 sm:space-x-4">
                <?php if (has_permission('users', 'create')): ?>
                    <a href="<?= base_url('doctors/create') ?>" class="inline-flex items-center px-6 py-3 bg-gradient-to-r from-green-500 to-emerald-600 text-white text-sm font-medium rounded-lg hover:from-green-600 hover:to-emerald-700 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 transition-all duration-200 shadow-lg hover:shadow-xl">
                        <i class="fas fa-user-md mr-2"></i>Add New Doctor
                    </a>
                <?php endif; ?>
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

    <!-- Doctors Grid -->
    <?php if (!empty($doctors)): ?>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <?php foreach ($doctors as $doctor): ?>
                <div class="bg-white rounded-xl shadow-lg border border-gray-200 hover:shadow-xl transition-all duration-300 overflow-hidden group">
                    <!-- Doctor Header -->
                    <div class="bg-gradient-to-r from-green-500 to-emerald-600 p-6 text-white">
                        <div class="flex items-center space-x-4">
                            <div class="w-16 h-16 bg-white/20 rounded-full flex items-center justify-center backdrop-blur-sm">
                                <i class="fas fa-user-md text-white text-2xl"></i>
                            </div>
                            <div class="flex-1">
                                <h3 class="text-xl font-bold">Dr. <?= esc($doctor['first_name'] . ' ' . $doctor['last_name']) ?></h3>
                                <p class="text-green-100 text-sm"><?= esc($doctor['role_name']) ?></p>
                            </div>
                        </div>
                    </div>

                    <!-- Doctor Details -->
                    <div class="p-6">
                        <div class="space-y-3">
                            <div class="flex items-center space-x-3">
                                <i class="fas fa-certificate text-green-600 w-4"></i>
                                <span class="text-sm text-gray-600">License: <?= esc($doctor['license_number']) ?></span>
                            </div>
                            
                            <div class="flex items-center space-x-3">
                                <i class="fas fa-stethoscope text-blue-600 w-4"></i>
                                <span class="text-sm text-gray-600"><?= ucfirst(str_replace('_', ' ', $doctor['specialization'])) ?></span>
                            </div>
                            
                            <div class="flex items-center space-x-3">
                                <i class="fas fa-clock text-purple-600 w-4"></i>
                                <span class="text-sm text-gray-600"><?= $doctor['years_experience'] ?> years experience</span>
                            </div>
                            
                            <div class="flex items-center space-x-3">
                                <i class="fas fa-dollar-sign text-yellow-600 w-4"></i>
                                <span class="text-sm text-gray-600">$<?= number_format($doctor['consultation_fee'], 2) ?> consultation fee</span>
                            </div>
                            
                            <?php if ($doctor['department']): ?>
                                <div class="flex items-center space-x-3">
                                    <i class="fas fa-building text-indigo-600 w-4"></i>
                                    <span class="text-sm text-gray-600"><?= esc($doctor['department']) ?></span>
                                </div>
                            <?php endif; ?>
                        </div>

                        <!-- Action Buttons -->
                        <div class="mt-6 flex space-x-2">
                            <a href="<?= base_url('doctors/' . $doctor['id']) ?>" 
                               class="flex-1 text-center px-4 py-2 bg-blue-500 text-white text-sm font-medium rounded-lg hover:bg-blue-600 transition-colors">
                                <i class="fas fa-eye mr-1"></i>View
                            </a>
                            
                            <?php if (has_permission('users', 'edit')): ?>
                                <a href="<?= base_url('doctors/' . $doctor['id'] . '/edit') ?>" 
                                   class="flex-1 text-center px-4 py-2 bg-yellow-500 text-white text-sm font-medium rounded-lg hover:bg-yellow-600 transition-colors">
                                    <i class="fas fa-edit mr-1"></i>Edit
                                </a>
                            <?php endif; ?>
                            
                            <?php if (has_permission('users', 'delete')): ?>
                                <button onclick="deleteDoctor(<?= $doctor['id'] ?>)" 
                                        class="flex-1 px-4 py-2 bg-red-500 text-white text-sm font-medium rounded-lg hover:bg-red-600 transition-colors">
                                    <i class="fas fa-trash mr-1"></i>Delete
                                </button>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <!-- Empty State -->
        <div class="text-center py-12">
            <div class="w-24 h-24 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-6">
                <i class="fas fa-user-md text-gray-400 text-3xl"></i>
            </div>
            <h3 class="text-xl font-semibold text-gray-600 mb-2">No Doctors Found</h3>
            <p class="text-gray-500 mb-6">Get started by adding your first medical professional.</p>
            <?php if (has_permission('users', 'create')): ?>
                <a href="<?= base_url('doctors/create') ?>" class="inline-flex items-center px-6 py-3 bg-gradient-to-r from-green-500 to-emerald-600 text-white text-sm font-medium rounded-lg hover:from-green-600 hover:to-emerald-700 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 transition-all duration-200 shadow-lg hover:shadow-xl">
                    <i class="fas fa-user-md mr-2"></i>Add First Doctor
                </a>
            <?php endif; ?>
        </div>
    <?php endif; ?>
</div>

<!-- Delete Confirmation Modal -->
<div id="deleteModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden z-50">
    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="bg-white rounded-lg shadow-xl max-w-md w-full">
            <div class="p-6">
                <div class="flex items-center space-x-3 mb-4">
                    <div class="w-10 h-10 bg-red-100 rounded-full flex items-center justify-center">
                        <i class="fas fa-exclamation-triangle text-red-600"></i>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-800">Confirm Deletion</h3>
                </div>
                <p class="text-gray-600 mb-6">Are you sure you want to deactivate this doctor? This action can be undone later.</p>
                <div class="flex space-x-3">
                    <button onclick="closeDeleteModal()" class="flex-1 px-4 py-2 bg-gray-300 text-gray-700 rounded-lg hover:bg-gray-400 transition-colors">
                        Cancel
                    </button>
                    <button id="confirmDelete" class="flex-1 px-4 py-2 bg-red-500 text-white rounded-lg hover:bg-red-600 transition-colors">
                        Deactivate
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
let doctorToDelete = null;

function deleteDoctor(doctorId) {
    doctorToDelete = doctorId;
    document.getElementById('deleteModal').classList.remove('hidden');
}

function closeDeleteModal() {
    doctorToDelete = null;
    document.getElementById('deleteModal').classList.add('hidden');
}

document.getElementById('confirmDelete').addEventListener('click', function() {
    if (doctorToDelete) {
        fetch(`<?= base_url('doctors/') ?>${doctorToDelete}`, {
            method: 'DELETE',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Content-Type': 'application/json'
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
            alert('An error occurred while deleting the doctor.');
        });
    }
    closeDeleteModal();
});
</script>
<?= $this->endSection() ?>
