<?= $this->extend('layouts/main_auth') ?>

<?= $this->section('content') ?>
<!-- Enhanced Prescription Details with Advanced Design -->
<div class="min-h-screen bg-gradient-to-br from-slate-50 via-teal-50 to-emerald-100 relative overflow-hidden">
    <!-- Animated Background Elements -->
    <div class="absolute inset-0 overflow-hidden pointer-events-none">
        <div class="absolute -top-40 -right-40 w-80 h-80 bg-gradient-to-br from-teal-400/20 to-emerald-600/20 rounded-full blur-3xl animate-pulse"></div>
        <div class="absolute -bottom-40 -left-40 w-80 h-80 bg-gradient-to-br from-blue-400/20 to-cyan-600/20 rounded-full blur-3xl animate-pulse delay-1000"></div>
        <div class="absolute top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2 w-96 h-96 bg-gradient-to-br from-indigo-400/10 to-purple-600/10 rounded-full blur-3xl animate-pulse delay-2000"></div>
    </div>

    <div class="container mx-auto relative z-10 px-4 py-6">
        <!-- Enhanced Prescription Details Card -->
        <div class="group relative">
            <div class="absolute inset-0 bg-gradient-to-br from-teal-500/10 to-emerald-600/10 rounded-3xl blur-xl group-hover:blur-2xl transition-all duration-500 opacity-0 group-hover:opacity-100"></div>
            <div class="relative backdrop-blur-xl bg-white/80 border border-white/30 rounded-2xl shadow-2xl shadow-teal-500/10 group-hover:shadow-teal-500/20 transition-all duration-500 overflow-hidden">
                <!-- Header -->
                <div class="p-8 border-b border-white/20">
                    <div class="flex flex-col lg:flex-row lg:justify-between lg:items-center space-y-4 lg:space-y-0">
                        <div class="flex items-center space-x-4">
                            <div class="relative">
                                <div class="absolute inset-0 bg-gradient-to-br from-teal-500 to-emerald-600 rounded-2xl blur-lg opacity-75 group-hover:opacity-100 transition-opacity duration-300"></div>
                                <div class="relative w-12 h-12 bg-gradient-to-br from-teal-500 to-emerald-600 rounded-2xl flex items-center justify-center shadow-xl group-hover:scale-110 group-hover:rotate-3 transition-all duration-300">
                                    <i class="fas fa-prescription-bottle-alt text-white text-lg"></i>
                                </div>
                            </div>
                            <div>
                                <h1 class="text-3xl font-black text-gray-900 group-hover:text-teal-900 transition-colors duration-300">Prescription Details</h1>
                                <p class="text-gray-600 font-medium">Prescription ID: <?= $prescription['prescription_id'] ?? 'N/A' ?></p>
                            </div>
                        </div>
                        
                        <div class="flex items-center space-x-3">
                            <a href="<?= base_url('prescription') ?>" class="group/btn relative inline-flex items-center px-6 py-3 border-2 border-gray-300 text-gray-700 text-sm font-bold rounded-xl hover:bg-gradient-to-r hover:from-gray-50 hover:to-slate-50 hover:border-gray-400 transition-all duration-300 hover:scale-105">
                                <div class="absolute inset-0 bg-gradient-to-r from-gray-500/10 to-slate-500/10 rounded-xl blur opacity-0 group-hover/btn:opacity-100 transition-opacity duration-300"></div>
                                <i class="fas fa-arrow-left mr-2 relative z-10"></i>
                                <span class="relative z-10">Back to Prescriptions</span>
                            </a>
                            <?php if (has_permission('prescriptions', 'edit')): ?>
                                <a href="<?= base_url('prescription/' . $prescription['id'] . '/edit') ?>" class="group/btn relative inline-flex items-center px-6 py-3 bg-gradient-to-r from-blue-500 to-cyan-600 text-white text-sm font-bold rounded-xl hover:from-blue-600 hover:to-cyan-700 transition-all duration-300 hover:scale-105">
                                    <div class="absolute inset-0 bg-gradient-to-r from-blue-500/20 to-cyan-600/20 rounded-xl blur opacity-0 group-hover/btn:opacity-100 transition-opacity duration-300"></div>
                                    <i class="fas fa-edit mr-2 relative z-10"></i>
                                    <span class="relative z-10">Edit Prescription</span>
                                </a>
                            <?php endif; ?>
                            <?php if (has_permission('prescriptions', 'view')): ?>
                                <a href="<?= base_url('prescription/' . $prescription['id'] . '/print') ?>" class="group/btn relative inline-flex items-center px-6 py-3 bg-gradient-to-r from-teal-500 to-emerald-600 text-white text-sm font-bold rounded-xl hover:from-teal-600 hover:to-emerald-700 transition-all duration-300 hover:scale-105">
                                    <div class="absolute inset-0 bg-gradient-to-r from-teal-500/20 to-emerald-600/20 rounded-xl blur opacity-0 group-hover/btn:opacity-100 transition-opacity duration-300"></div>
                                    <i class="fas fa-print mr-2 relative z-10"></i>
                                    <span class="relative z-10">Print Prescription</span>
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- Content -->
                <div class="p-8">
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                        <!-- Patient Information -->
                        <div class="space-y-6">
                            <div class="group/section relative">
                                <div class="absolute inset-0 bg-gradient-to-br from-blue-500/5 to-cyan-500/5 rounded-2xl blur opacity-0 group-hover/section:opacity-100 transition-opacity duration-300"></div>
                                <div class="relative p-6 rounded-2xl border border-blue-100 bg-gradient-to-br from-blue-50/50 to-cyan-50/50">
                                    <div class="flex items-center space-x-3 mb-4">
                                        <div class="w-10 h-10 bg-gradient-to-br from-blue-500 to-cyan-600 rounded-xl flex items-center justify-center">
                                            <i class="fas fa-user text-white"></i>
                                        </div>
                                        <h3 class="text-xl font-bold text-gray-900">Patient Information</h3>
                                    </div>
                                    <div class="space-y-3">
                                        <div class="flex justify-between">
                                            <span class="text-gray-600 font-medium">Name:</span>
                                            <span class="text-gray-900 font-semibold"><?= $prescription['first_name'] . ' ' . $prescription['last_name'] ?></span>
                                        </div>
                                        <div class="flex justify-between">
                                            <span class="text-gray-600 font-medium">Phone:</span>
                                            <span class="text-gray-900 font-semibold"><?= $prescription['phone'] ?></span>
                                        </div>
                                        <div class="flex justify-between">
                                            <span class="text-gray-600 font-medium">Email:</span>
                                            <span class="text-gray-900 font-semibold"><?= $prescription['email'] ?? 'N/A' ?></span>
                                        </div>
                                        <div class="flex justify-between">
                                            <span class="text-gray-600 font-medium">Patient ID:</span>
                                            <span class="text-gray-900 font-semibold"><?= $prescription['patient_id'] ?? 'N/A' ?></span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Medication Details -->
                            <div class="group/section relative">
                                <div class="absolute inset-0 bg-gradient-to-br from-green-500/5 to-emerald-500/5 rounded-2xl blur opacity-0 group-hover/section:opacity-100 transition-opacity duration-300"></div>
                                <div class="relative p-6 rounded-2xl border border-green-100 bg-gradient-to-br from-green-50/50 to-emerald-50/50">
                                    <div class="flex items-center space-x-3 mb-4">
                                        <div class="w-10 h-10 bg-gradient-to-br from-green-500 to-emerald-600 rounded-xl flex items-center justify-center">
                                            <i class="fas fa-pills text-white"></i>
                                        </div>
                                        <h3 class="text-xl font-bold text-gray-900">Medication Details</h3>
                                    </div>
                                    <div class="space-y-3">
                                        <div class="flex justify-between">
                                            <span class="text-gray-600 font-medium">Medication:</span>
                                            <span class="text-gray-900 font-semibold"><?= $prescription['medication_name'] ?></span>
                                        </div>
                                        <div class="flex justify-between">
                                            <span class="text-gray-600 font-medium">Dosage:</span>
                                            <span class="text-gray-900 font-semibold"><?= $prescription['dosage'] ?></span>
                                        </div>
                                        <div class="flex justify-between">
                                            <span class="text-gray-600 font-medium">Frequency:</span>
                                            <span class="text-gray-900 font-semibold"><?= $prescription['frequency'] ?></span>
                                        </div>
                                        <div class="flex justify-between">
                                            <span class="text-gray-600 font-medium">Duration:</span>
                                            <span class="text-gray-900 font-semibold"><?= $prescription['duration'] ?></span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Prescription Information -->
                        <div class="space-y-6">
                            <!-- Dates and Status -->
                            <div class="group/section relative">
                                <div class="absolute inset-0 bg-gradient-to-br from-purple-500/5 to-pink-500/5 rounded-2xl blur opacity-0 group-hover/section:opacity-100 transition-opacity duration-300"></div>
                                <div class="relative p-6 rounded-2xl border border-purple-100 bg-gradient-to-br from-purple-50/50 to-pink-50/50">
                                    <div class="flex items-center space-x-3 mb-4">
                                        <div class="w-10 h-10 bg-gradient-to-br from-purple-500 to-pink-600 rounded-xl flex items-center justify-center">
                                            <i class="fas fa-calendar text-white"></i>
                                        </div>
                                        <h3 class="text-xl font-bold text-gray-900">Prescription Information</h3>
                                    </div>
                                    <div class="space-y-3">
                                        <div class="flex justify-between">
                                            <span class="text-gray-600 font-medium">Prescribed Date:</span>
                                            <span class="text-gray-900 font-semibold"><?= date('M j, Y', strtotime($prescription['prescribed_date'])) ?></span>
                                        </div>
                                        <div class="flex justify-between">
                                            <span class="text-gray-600 font-medium">Expiry Date:</span>
                                            <span class="text-gray-900 font-semibold"><?= date('M j, Y', strtotime($prescription['expiry_date'])) ?></span>
                                        </div>
                                        <div class="flex justify-between">
                                            <span class="text-gray-600 font-medium">Status:</span>
                                            <?php
                                            $statusConfig = [
                                                'active' => ['bg' => 'bg-gradient-to-r from-green-100 to-emerald-100', 'text' => 'text-green-800', 'icon' => 'fas fa-check-circle', 'border' => 'border-green-200'],
                                                'expired' => ['bg' => 'bg-gradient-to-r from-amber-100 to-orange-100', 'text' => 'text-amber-800', 'icon' => 'fas fa-exclamation-triangle', 'border' => 'border-amber-200'],
                                                'cancelled' => ['bg' => 'bg-gradient-to-r from-gray-100 to-slate-100', 'text' => 'text-gray-800', 'icon' => 'fas fa-times-circle', 'border' => 'border-gray-200'],
                                                'pending' => ['bg' => 'bg-gradient-to-r from-blue-100 to-cyan-100', 'text' => 'text-blue-800', 'icon' => 'fas fa-clock', 'border' => 'border-blue-200']
                                            ];
                                            $status = $statusConfig[$prescription['status']] ?? $statusConfig['pending'];
                                            ?>
                                            <span class="inline-flex items-center px-3 py-1 text-xs font-bold rounded-full <?= $status['bg'] ?> <?= $status['text'] ?> border <?= $status['border'] ?>">
                                                <i class="<?= $status['icon'] ?> mr-1.5 text-xs"></i>
                                                <?= ucfirst($prescription['status']) ?>
                                            </span>
                                        </div>
                                        <div class="flex justify-between">
                                            <span class="text-gray-600 font-medium">Created:</span>
                                            <span class="text-gray-900 font-semibold"><?= date('M j, Y g:i A', strtotime($prescription['created_at'])) ?></span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Instructions -->
                            <div class="group/section relative">
                                <div class="absolute inset-0 bg-gradient-to-br from-amber-500/5 to-orange-500/5 rounded-2xl blur opacity-0 group-hover/section:opacity-100 transition-opacity duration-300"></div>
                                <div class="relative p-6 rounded-2xl border border-amber-100 bg-gradient-to-br from-amber-50/50 to-orange-50/50">
                                    <div class="flex items-center space-x-3 mb-4">
                                        <div class="w-10 h-10 bg-gradient-to-br from-amber-500 to-orange-600 rounded-xl flex items-center justify-center">
                                            <i class="fas fa-sticky-note text-white"></i>
                                        </div>
                                        <h3 class="text-xl font-bold text-gray-900">Instructions</h3>
                                    </div>
                                    <div class="bg-white/80 rounded-xl p-4 border border-amber-200">
                                        <p class="text-gray-800 leading-relaxed"><?= nl2br(htmlspecialchars($prescription['instructions'])) ?></p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Action Buttons -->
                    <div class="flex flex-col sm:flex-row justify-center gap-4 sm:gap-12 pt-8 border-t border-white/20">
                        <a href="<?= base_url('prescription/' . $prescription['id'] . '/print') ?>" target="_blank" class="group/btn relative inline-flex items-center justify-center px-8 py-4 bg-gradient-to-r from-green-500 to-emerald-600 text-white text-sm font-bold rounded-2xl shadow-2xl shadow-green-500/25 hover:shadow-green-500/40 transition-all duration-500 hover:scale-105 hover:-translate-y-1">
                            <div class="absolute inset-0 bg-gradient-to-r from-green-600 to-emerald-700 rounded-2xl blur opacity-0 group-hover/btn:opacity-100 transition-opacity duration-300"></div>
                            <i class="fas fa-print mr-3 relative z-10 group-hover/btn:scale-110 transition-transform duration-300"></i>
                            <span class="relative z-10">Print Prescription</span>
                        </a>
                        <button onclick="deletePrescription(<?= $prescription['id'] ?>)" class="group/btn relative inline-flex items-center justify-center px-8 py-4 bg-gradient-to-r from-red-500 to-pink-600 text-white text-sm font-bold rounded-2xl shadow-2xl shadow-red-500/25 hover:shadow-red-500/40 transition-all duration-500 hover:scale-105 hover:-translate-y-1">
                            <div class="absolute inset-0 bg-gradient-to-r from-red-600 to-pink-700 rounded-2xl blur opacity-0 group-hover/btn:opacity-100 transition-opacity duration-300"></div>
                            <i class="fas fa-trash mr-3 relative z-10 group-hover/btn:scale-110 transition-transform duration-300"></i>
                            <span class="relative z-10">Delete Prescription</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Enhanced delete prescription function with better UX
function deletePrescription(id) {
    // Create a custom confirmation modal
    const modal = document.createElement('div');
    modal.className = 'fixed inset-0 bg-black/50 backdrop-blur-sm z-50 flex items-center justify-center p-4';
    
    const container = document.createElement('div');
    container.className = 'bg-white rounded-2xl shadow-2xl max-w-md w-full p-6';
    
    const header = document.createElement('div');
    header.className = 'flex items-center space-x-4 mb-4';
    
    const iconContainer = document.createElement('div');
    iconContainer.className = 'w-12 h-12 bg-gradient-to-br from-red-500 to-pink-600 rounded-2xl flex items-center justify-center';
    const icon = document.createElement('i');
    icon.className = 'fas fa-exclamation-triangle text-white text-xl';
    iconContainer.appendChild(icon);
    
    const titleContainer = document.createElement('div');
    const title = document.createElement('h3');
    title.className = 'text-lg font-bold text-gray-900';
    title.textContent = 'Delete Prescription';
    const subtitle = document.createElement('p');
    subtitle.className = 'text-sm text-gray-600';
    subtitle.textContent = 'This action cannot be undone';
    
    titleContainer.appendChild(title);
    titleContainer.appendChild(subtitle);
    header.appendChild(iconContainer);
    header.appendChild(titleContainer);
    
    const message = document.createElement('p');
    message.className = 'text-gray-700 mb-6';
    message.textContent = 'Are you sure you want to delete this prescription? All associated data will be permanently removed.';
    
    const actions = document.createElement('div');
    actions.className = 'flex space-x-3';
    
    const cancelBtn = document.createElement('button');
    cancelBtn.className = 'flex-1 px-4 py-2 bg-gray-100 text-gray-700 rounded-xl font-semibold hover:bg-gray-200 transition-colors';
    cancelBtn.textContent = 'Cancel';
    cancelBtn.onclick = () => modal.remove();
    
    const deleteBtn = document.createElement('button');
    deleteBtn.className = 'flex-1 px-4 py-2 bg-gradient-to-r from-red-500 to-pink-600 text-white rounded-xl font-semibold hover:from-red-600 hover:to-pink-700 transition-all duration-300';
    deleteBtn.textContent = 'Delete';
    deleteBtn.onclick = () => confirmDelete(id);
    
    actions.appendChild(cancelBtn);
    actions.appendChild(deleteBtn);
    
    container.appendChild(header);
    container.appendChild(message);
    container.appendChild(actions);
    modal.appendChild(container);
    
    document.body.appendChild(modal);
}

function confirmDelete(id) {
    // Remove modal
    const modal = document.querySelector('.fixed.inset-0.bg-black\/50');
    if (modal) modal.remove();
    
    // Show loading state
    const loadingToast = document.createElement('div');
    loadingToast.className = 'fixed top-4 right-4 bg-blue-500 text-white px-6 py-3 rounded-xl shadow-lg z-50';
    
    const loadingIcon = document.createElement('i');
    loadingIcon.className = 'fas fa-spinner fa-spin mr-2';
    loadingToast.appendChild(loadingIcon);
    
    const loadingText = document.createTextNode('Deleting prescription...');
    loadingToast.appendChild(loadingText);
    
    document.body.appendChild(loadingToast);

    // Get current token from global mechanism
    const csrfHeader = '<?= csrf_header() ?>';
    const csrfToken = window.csrfHash || '<?= csrf_hash() ?>';

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
        loadingToast.remove();
        
        // Refresh CSRF token if present
        if (data.csrf_token && window.refreshCsrfToken) {
            window.refreshCsrfToken(data.csrf_token);
        }

        if (data.success) {
            // Show success toast
            const successToast = document.createElement('div');
            successToast.className = 'fixed top-4 right-4 bg-green-500 text-white px-6 py-3 rounded-xl shadow-lg z-50';
            
            const successIcon = document.createElement('i');
            successIcon.className = 'fas fa-check mr-2';
            successToast.appendChild(successIcon);
            
            const successText = document.createTextNode('Prescription deleted successfully');
            successToast.appendChild(successText);
            
            document.body.appendChild(successToast);
            
            // Redirect to prescriptions list after a short delay
            setTimeout(() => {
                window.location.href = '<?= base_url('prescription') ?>';
            }, 1500);
        } else {
            throw new Error(data.message || 'Failed to delete prescription');
        }
    })
    .catch(error => {
        loadingToast.remove();
        console.error('Error:', error);
        
        // Show error toast
        const errorToast = document.createElement('div');
        errorToast.className = 'fixed top-4 right-4 bg-red-50 text-red-600 px-6 py-3 rounded-xl shadow-lg z-50 border border-red-200';
        
        const errorIcon = document.createElement('i');
        errorIcon.className = 'fas fa-exclamation-circle mr-2';
        errorToast.appendChild(errorIcon);
        
        const errorText = document.createElement('span');
        errorText.textContent = 'Error: ' + error.message;
        errorToast.appendChild(errorText);
        
        document.body.appendChild(errorToast);
        
        // Auto remove error toast
        setTimeout(() => errorToast.remove(), 5000);
    });
}
</script>
<?= $this->endSection() ?>
