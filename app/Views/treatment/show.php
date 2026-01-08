<?= $this->extend('layouts/main_auth') ?>

<?= $this->section('content') ?>
<div class="min-h-screen bg-gradient-to-br from-slate-50 via-blue-50 to-indigo-100">
    <div class="px-4 sm:px-6 lg:px-8 py-6 sm:py-8">
        <!-- Page Header -->
        <div class="mb-8">
            <div class="bg-white/70 backdrop-blur-lg rounded-2xl border border-white/20 shadow-xl p-6">
                <div class="flex flex-col lg:flex-row lg:justify-between lg:items-center space-y-4 lg:space-y-0">
                    <div class="space-y-3">
                        <div class="flex items-center space-x-3">
                            <div class="p-3 bg-gradient-to-br from-blue-600 to-purple-600 rounded-xl shadow-lg">
                                <i class="fas fa-tooth text-white text-xl"></i>
                            </div>
                            <div>
                                <h1 class="text-3xl font-bold bg-gradient-to-r from-gray-800 to-gray-600 bg-clip-text text-transparent">
                                    Treatment Details
                                </h1>
                                <p class="text-gray-600">Treatment ID: <?= $treatment['treatment_id'] ?? 'N/A' ?></p>
                            </div>
                        </div>
                    </div>
                    <div class="flex flex-col sm:flex-row space-y-3 sm:space-y-0 sm:space-x-3">
                        <a href="<?= base_url('treatment') ?>" class="inline-flex items-center px-4 py-2 bg-gradient-to-r from-gray-600 to-gray-700 text-white text-sm font-medium rounded-xl hover:from-gray-700 hover:to-gray-800 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition-all duration-300 shadow-lg hover:shadow-xl">
                            <i class="fas fa-arrow-left mr-2"></i>Back to Treatments
                        </a>
                        <a href="<?= base_url('treatment/' . $treatment['id'] . '/edit') ?>" class="inline-flex items-center px-4 py-2 bg-gradient-to-r from-blue-600 to-blue-700 text-white text-sm font-medium rounded-xl hover:from-blue-700 hover:to-blue-800 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition-all duration-300 shadow-lg hover:shadow-xl">
                            <i class="fas fa-edit mr-2"></i>Edit Treatment
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Treatment Details -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Main Treatment Information -->
            <div class="lg:col-span-2 space-y-6">
                <!-- Treatment Overview -->
                <div class="bg-white/80 backdrop-blur-lg rounded-2xl border border-white/20 shadow-xl p-6">
                    <h2 class="text-xl font-bold text-gray-800 mb-4 flex items-center">
                        <i class="fas fa-info-circle text-blue-600 mr-2"></i>
                        Treatment Overview
                    </h2>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-600 mb-1">Treatment Name</label>
                            <p class="text-gray-800 font-semibold"><?= $treatment['treatment_name'] ?? 'N/A' ?></p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-600 mb-1">Treatment Type</label>
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-blue-100 text-blue-800">
                                <?= ucfirst(str_replace('_', ' ', $treatment['treatment_type'] ?? 'N/A')) ?>
                            </span>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-600 mb-1">Tooth Number</label>
                            <p class="text-gray-800"><?= $treatment['tooth_number'] ?? 'N/A' ?></p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-600 mb-1">Status</label>
                            <?php
                            $statusColors = [
                                'active' => 'bg-green-100 text-green-800',
                                'completed' => 'bg-blue-100 text-blue-800',
                                'cancelled' => 'bg-red-100 text-red-800',
                                'on_hold' => 'bg-yellow-100 text-yellow-800'
                            ];
                            $statusColor = $statusColors[$treatment['status']] ?? 'bg-gray-100 text-gray-800';
                            ?>
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium <?= $statusColor ?>">
                                <?= ucfirst(str_replace('_', ' ', $treatment['status'] ?? 'N/A')) ?>
                            </span>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-600 mb-1">Cost</label>
                            <p class="text-gray-800 font-semibold text-lg"><?= formatCurrency($treatment['cost'] ?? 0) ?></p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-600 mb-1">Estimated Duration</label>
                            <p class="text-gray-800"><?= $treatment['estimated_duration'] ?? 'N/A' ?> minutes</p>
                        </div>
                    </div>
                </div>

                <!-- Treatment Description -->
                <div class="bg-white/80 backdrop-blur-lg rounded-2xl border border-white/20 shadow-xl p-6">
                    <h2 class="text-xl font-bold text-gray-800 mb-4 flex items-center">
                        <i class="fas fa-file-alt text-green-600 mr-2"></i>
                        Treatment Description
                    </h2>
                    <div class="prose max-w-none">
                        <p class="text-gray-700 leading-relaxed"><?= nl2br(htmlspecialchars($treatment['treatment_description'] ?? 'No description provided')) ?></p>
                    </div>
                </div>

                <!-- Treatment Notes -->
                <?php if (!empty($treatment['treatment_notes'])): ?>
                <div class="bg-white/80 backdrop-blur-lg rounded-2xl border border-white/20 shadow-xl p-6">
                    <h2 class="text-xl font-bold text-gray-800 mb-4 flex items-center">
                        <i class="fas fa-sticky-note text-yellow-600 mr-2"></i>
                        Treatment Notes
                    </h2>
                    <div class="prose max-w-none">
                        <p class="text-gray-700 leading-relaxed"><?= nl2br(htmlspecialchars($treatment['treatment_notes'])) ?></p>
                    </div>
                </div>
                <?php endif; ?>
            </div>

            <!-- Sidebar Information -->
            <div class="space-y-6">
                <!-- Patient Information -->
                <div class="bg-white/80 backdrop-blur-lg rounded-2xl border border-white/20 shadow-xl p-6">
                    <h2 class="text-xl font-bold text-gray-800 mb-4 flex items-center">
                        <i class="fas fa-user text-purple-600 mr-2"></i>
                        Patient Information
                    </h2>
                    <div class="space-y-3">
                        <div>
                            <label class="block text-sm font-medium text-gray-600 mb-1">Patient Name</label>
                            <p class="text-gray-800 font-semibold"><?= htmlspecialchars($treatment['first_name'] . ' ' . $treatment['last_name']) ?></p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-600 mb-1">Phone</label>
                            <p class="text-gray-800"><?= htmlspecialchars($treatment['phone'] ?? 'N/A') ?></p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-600 mb-1">Email</label>
                            <p class="text-gray-800"><?= htmlspecialchars($treatment['email'] ?? 'N/A') ?></p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-600 mb-1">Date of Birth</label>
                            <p class="text-gray-800"><?= $treatment['date_of_birth'] ? date('M d, Y', strtotime($treatment['date_of_birth'])) : 'N/A' ?></p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-600 mb-1">Gender</label>
                            <p class="text-gray-800"><?= ucfirst($treatment['gender'] ?? 'N/A') ?></p>
                        </div>
                    </div>
                </div>

                <!-- Treatment Timeline -->
                <div class="bg-white/80 backdrop-blur-lg rounded-2xl border border-white/20 shadow-xl p-6">
                    <h2 class="text-xl font-bold text-gray-800 mb-4 flex items-center">
                        <i class="fas fa-clock text-indigo-600 mr-2"></i>
                        Treatment Timeline
                    </h2>
                    <div class="space-y-3">
                        <div>
                            <label class="block text-sm font-medium text-gray-600 mb-1">Start Date</label>
                            <p class="text-gray-800"><?= $treatment['start_date'] ? date('M d, Y', strtotime($treatment['start_date'])) : 'N/A' ?></p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-600 mb-1">Completion Date</label>
                            <p class="text-gray-800"><?= $treatment['completion_date'] ? date('M d, Y H:i', strtotime($treatment['completion_date'])) : 'Not completed' ?></p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-600 mb-1">Created At</label>
                            <p class="text-gray-800"><?= $treatment['created_at'] ? date('M d, Y H:i', strtotime($treatment['created_at'])) : 'N/A' ?></p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-600 mb-1">Last Updated</label>
                            <p class="text-gray-800"><?= $treatment['updated_at'] ? date('M d, Y H:i', strtotime($treatment['updated_at'])) : 'N/A' ?></p>
                        </div>
                    </div>
                </div>

                <!-- Quick Actions -->
                <div class="bg-white/80 backdrop-blur-lg rounded-2xl border border-white/20 shadow-xl p-6">
                    <h2 class="text-xl font-bold text-gray-800 mb-4 flex items-center">
                        <i class="fas fa-bolt text-orange-600 mr-2"></i>
                        Quick Actions
                    </h2>
                    <div class="space-y-3">
                        <?php if ($treatment['status'] === 'active'): ?>
                        <button onclick="completeTreatment(<?= $treatment['id'] ?>)" class="w-full inline-flex items-center justify-center px-4 py-2 bg-green-600 text-white text-sm font-bold rounded-xl hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 transition-all duration-300 shadow-lg hover:shadow-xl border border-green-500">
                            <i class="fas fa-check mr-2 text-white"></i>
                            <span class="text-white font-bold">Mark as Completed</span>
                        </button>
                        <?php endif; ?>
                        
                        <button onclick="printTreatment(<?= $treatment['id'] ?>)" class="w-full inline-flex items-center justify-center px-4 py-2 bg-blue-600 text-white text-sm font-bold rounded-xl hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition-all duration-300 shadow-lg hover:shadow-xl border border-blue-500">
                            <i class="fas fa-print mr-2 text-white"></i>
                            <span class="text-white font-bold">Print Treatment</span>
                        </button>
                        
                        <button onclick="deleteTreatment(<?= $treatment['id'] ?>)" class="w-full inline-flex items-center justify-center px-4 py-2 bg-red-600 text-white text-sm font-bold rounded-xl hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 transition-all duration-300 shadow-lg hover:shadow-xl border border-red-500">
                            <i class="fas fa-trash mr-2 text-white"></i>
                            <span class="text-white font-bold">Delete Treatment</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function completeTreatment(id) {
    if (confirm('Are you sure you want to mark this treatment as completed?')) {
        fetch(`<?= base_url('treatment') ?>/${id}/complete`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
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
            alert('An error occurred while completing the treatment.');
        });
    }
}

function printTreatment(id) {
    window.open(`<?= base_url('treatment') ?>/${id}/print`, '_blank');
}

function deleteTreatment(id) {
    if (confirm('Are you sure you want to delete this treatment? This action cannot be undone.')) {
        fetch(`<?= base_url('treatment') ?>/${id}`, {
            method: 'DELETE',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                window.location.href = '<?= base_url('treatment') ?>';
            } else {
                alert('Error: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred while deleting the treatment.');
        });
    }
}
</script>
<?= $this->endSection() ?>
