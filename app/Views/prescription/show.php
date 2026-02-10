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
                            <div class="p-3 bg-gradient-to-br from-teal-500 to-emerald-600 rounded-xl shadow-lg">
                                <i class="fas fa-prescription-bottle-alt text-white text-xl"></i>
                            </div>
                            <div>
                                <h1 class="text-3xl font-bold bg-gradient-to-r from-gray-800 to-gray-600 bg-clip-text text-transparent">
                                    Prescription Details
                                </h1>
                                <p class="text-gray-600">Prescription ID: <?= $prescription['prescription_id'] ?? 'N/A' ?></p>
                            </div>
                        </div>
                    </div>
                    <div class="flex flex-col sm:flex-row space-y-3 sm:space-y-0 sm:space-x-3">
                        <?php if (!empty($prescription['patient_id'])): ?>
                        <a href="<?= base_url('patient/' . $prescription['patient_id']) ?>" class="inline-flex items-center px-4 py-2 bg-gradient-to-r from-indigo-500 to-blue-600 text-white text-sm font-medium rounded-xl hover:from-indigo-600 hover:to-blue-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition-all duration-300 shadow-lg hover:shadow-xl">
                            <i class="fas fa-user mr-2"></i>Back to Patient
                        </a>
                        <?php endif; ?>
                        <a href="<?= base_url('prescription') ?>" class="inline-flex items-center px-4 py-2 bg-gradient-to-r from-gray-600 to-gray-700 text-white text-sm font-medium rounded-xl hover:from-gray-700 hover:to-gray-800 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition-all duration-300 shadow-lg hover:shadow-xl">
                            <i class="fas fa-arrow-left mr-2"></i>Back to Prescriptions
                        </a>
                        <?php if (has_permission('prescriptions', 'edit')): ?>
                        <a href="<?= base_url('prescription/' . $prescription['id'] . '/edit') ?>" class="inline-flex items-center px-4 py-2 bg-gradient-to-r from-blue-600 to-blue-700 text-white text-sm font-medium rounded-xl hover:from-blue-700 hover:to-blue-800 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition-all duration-300 shadow-lg hover:shadow-xl">
                            <i class="fas fa-edit mr-2"></i>Edit Prescription
                        </a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Prescription Details -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Main Information -->
            <div class="lg:col-span-2 space-y-6">
                <!-- Prescription Overview -->
                <div class="bg-white/80 backdrop-blur-lg rounded-2xl border border-white/20 shadow-xl p-6">
                    <h2 class="text-xl font-bold text-gray-800 mb-4 flex items-center">
                        <i class="fas fa-info-circle text-teal-600 mr-2"></i>
                        Prescription Overview
                    </h2>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-600 mb-1">Medication Name</label>
                            <p class="text-gray-800 font-semibold"><?= $prescription['medication_name'] ?? 'N/A' ?></p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-600 mb-1">Dosage</label>
                            <p class="text-gray-800"><?= $prescription['dosage'] ?? 'N/A' ?></p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-600 mb-1">Frequency</label>
                            <p class="text-gray-800"><?= $prescription['frequency'] ?? 'N/A' ?></p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-600 mb-1">Duration</label>
                            <p class="text-gray-800"><?= $prescription['duration'] ?? 'N/A' ?></p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-600 mb-1">Status</label>
                            <?php
                            $statusConfig = [
                                'active' => ['bg' => 'bg-green-100 text-green-800', 'icon' => 'fas fa-check-circle'],
                                'expired' => ['bg' => 'bg-amber-100 text-amber-800', 'icon' => 'fas fa-exclamation-triangle'],
                                'cancelled' => ['bg' => 'bg-gray-100 text-gray-800', 'icon' => 'fas fa-times-circle'],
                                'pending' => ['bg' => 'bg-blue-100 text-blue-800', 'icon' => 'fas fa-clock']
                            ];
                            $status = $statusConfig[$prescription['status']] ?? $statusConfig['pending'];
                            ?>
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium <?= $status['bg'] ?>">
                                <i class="<?= $status['icon'] ?> mr-1.5 text-xs"></i>
                                <?= ucfirst($prescription['status']) ?>
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Instructions -->
                <div class="bg-white/80 backdrop-blur-lg rounded-2xl border border-white/20 shadow-xl p-6">
                    <h2 class="text-xl font-bold text-gray-800 mb-4 flex items-center">
                        <i class="fas fa-sticky-note text-yellow-600 mr-2"></i>
                        Instructions
                    </h2>
                    <div class="prose max-w-none">
                        <p class="text-gray-700 leading-relaxed"><?= nl2br(htmlspecialchars($prescription['instructions'] ?? 'No instructions provided')) ?></p>
                    </div>
                </div>
            </div>

            <!-- Sidebar Information -->
            <?php $doctorName = trim(($prescription['creator_first_name'] ?? '') . ' ' . ($prescription['creator_last_name'] ?? '')); ?>
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
                            <p class="text-gray-800 font-semibold"><?= htmlspecialchars($prescription['first_name'] . ' ' . $prescription['last_name']) ?></p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-600 mb-1">Phone</label>
                            <p class="text-gray-800"><?= htmlspecialchars($prescription['phone'] ?? 'N/A') ?></p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-600 mb-1">Email</label>
                            <p class="text-gray-800"><?= htmlspecialchars($prescription['email'] ?? 'N/A') ?></p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-600 mb-1">Patient ID</label>
                            <p class="text-gray-800"><?= htmlspecialchars($prescription['patient_id'] ?? 'N/A') ?></p>
                        </div>
                    </div>
                </div>

                <!-- Dates -->
                <div class="bg-white/80 backdrop-blur-lg rounded-2xl border border-white/20 shadow-xl p-6">
                    <h2 class="text-xl font-bold text-gray-800 mb-4 flex items-center">
                        <i class="fas fa-calendar text-indigo-600 mr-2"></i>
                        Timeline
                    </h2>
                    <div class="space-y-3">
                        <div>
                            <label class="block text-sm font-medium text-gray-600 mb-1">Prescribed Date</label>
                            <p class="text-gray-800"><?= date('M d, Y', strtotime($prescription['prescribed_date'])) ?></p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-600 mb-1">Expiry Date</label>
                            <p class="text-gray-800"><?= date('M d, Y', strtotime($prescription['expiry_date'])) ?></p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-600 mb-1">Created At</label>
                            <p class="text-gray-800"><?= date('M d, Y H:i', strtotime($prescription['created_at'])) ?></p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-600 mb-1">Created By</label>
                            <p class="text-gray-800"><?= $doctorName !== '' ? 'Dr. ' . esc($doctorName) : 'Unknown' ?></p>
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
                        <?php if (has_permission('prescriptions', 'view')): ?>
                        <a href="<?= base_url('prescription/' . $prescription['id'] . '/print') ?>" target="_blank" class="w-full inline-flex items-center justify-center px-4 py-2 bg-teal-600 text-white text-sm font-bold rounded-xl hover:bg-teal-700 focus:outline-none focus:ring-2 focus:ring-teal-500 focus:ring-offset-2 transition-all duration-300 shadow-lg hover:shadow-xl border border-teal-500">
                            <i class="fas fa-print mr-2 text-white"></i>
                            <span class="text-white font-bold">Print Prescription</span>
                        </a>
                        <?php endif; ?>
                        
                        <?php if (has_permission('prescriptions', 'delete')): ?>
                        <button onclick="deletePrescription(<?= $prescription['id'] ?>)" class="w-full inline-flex items-center justify-center px-4 py-2 bg-red-600 text-white text-sm font-bold rounded-xl hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 transition-all duration-300 shadow-lg hover:shadow-xl border border-red-500">
                            <i class="fas fa-trash mr-2 text-white"></i>
                            <span class="text-white font-bold">Delete Prescription</span>
                        </button>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function deletePrescription(id) {
    if (confirm('Are you sure you want to delete this prescription? All associated data will be permanently removed.')) {
        const csrfHeader = window.csrfConfig?.header || 'X-CSRF-TOKEN';
        const csrfToken = window.getCsrfToken ? window.getCsrfToken() : '';
        fetch(`<?= base_url() ?>prescription/${id}`, {
            method: 'DELETE',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                [csrfHeader]: csrfToken
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data && data.csrf_token && window.refreshCsrfToken) {
                window.refreshCsrfToken(data.csrf_token);
            }
            if (data.success) {
                window.location.href = '<?= base_url('prescription') ?>';
            } else {
                alert('Error: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred while deleting the prescription.');
        });
    }
}
</script>
<?= $this->endSection() ?>
