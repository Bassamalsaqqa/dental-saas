<?= $this->extend('layouts/main_auth') ?>

<?= $this->section('content') ?>
<!-- Enhanced Appointment Details with Advanced Design -->
<div class="min-h-screen bg-gradient-to-br from-slate-50 via-blue-50 to-indigo-100 relative overflow-hidden">
    <!-- Animated Background Elements -->
    <div class="absolute inset-0 overflow-hidden pointer-events-none">
        <div class="absolute -top-40 -right-40 w-80 h-80 bg-gradient-to-br from-blue-400/20 to-purple-600/20 rounded-full blur-3xl animate-pulse"></div>
        <div class="absolute -bottom-40 -left-40 w-80 h-80 bg-gradient-to-br from-emerald-400/20 to-cyan-600/20 rounded-full blur-3xl animate-pulse delay-1000"></div>
        <div class="absolute top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2 w-96 h-96 bg-gradient-to-br from-amber-400/10 to-orange-600/10 rounded-full blur-3xl animate-pulse delay-2000"></div>
    </div>

    <div class="container mx-auto relative z-10 px-4 py-6">

        <!-- Enhanced Appointment Details with Glassmorphism -->
        <div class="group relative">
            <div class="absolute inset-0 bg-gradient-to-br from-blue-500/10 to-purple-600/10 rounded-3xl blur-xl group-hover:blur-2xl transition-all duration-500 opacity-0 group-hover:opacity-100"></div>
            <div class="relative backdrop-blur-xl bg-white/80 border border-white/30 rounded-2xl shadow-2xl shadow-blue-500/10 group-hover:shadow-blue-500/20 transition-all duration-500 overflow-hidden">
                <!-- Header -->
                <div class="p-8 border-b border-white/20">
                    <div class="flex flex-col lg:flex-row lg:justify-between lg:items-center space-y-4 lg:space-y-0">
                        <div class="flex items-center space-x-4">
                            <div class="relative">
                                <div class="absolute inset-0 bg-gradient-to-br from-blue-500 to-purple-600 rounded-2xl blur-lg opacity-75 group-hover:opacity-100 transition-opacity duration-300"></div>
                                <div class="relative w-12 h-12 bg-gradient-to-br from-blue-500 to-purple-600 rounded-2xl flex items-center justify-center shadow-xl group-hover:scale-110 group-hover:rotate-3 transition-all duration-300">
                                    <i class="fas fa-calendar-check text-white text-lg"></i>
                                </div>
                            </div>
                            <div>
                                <h3 class="text-2xl font-black text-gray-900 group-hover:text-blue-900 transition-colors duration-300">Appointment Details</h3>
                                <p class="text-gray-600 font-medium">Appointment ID: <?= $appointment['appointment_id'] ?></p>
                            </div>
                        </div>
                        
                        <div class="flex items-center space-x-3">
                            <a href="<?= base_url('appointment') ?>" class="group/btn relative inline-flex items-center px-6 py-3 border-2 border-gray-300 text-gray-700 text-sm font-bold rounded-xl hover:bg-gradient-to-r hover:from-gray-50 hover:to-slate-50 hover:border-gray-400 transition-all duration-300 hover:scale-105">
                                <div class="absolute inset-0 bg-gradient-to-r from-gray-500/10 to-slate-500/10 rounded-xl blur opacity-0 group-hover/btn:opacity-100 transition-opacity duration-300"></div>
                                <i class="fas fa-arrow-left mr-2 relative z-10"></i>
                                <span class="relative z-10">Back to Appointments</span>
                            </a>
                            <a href="<?= base_url('appointment/' . $appointment['id'] . '/print') ?>" target="_blank" class="group/btn relative inline-flex items-center px-6 py-3 bg-gradient-to-r from-green-500 to-emerald-600 text-white text-sm font-bold rounded-xl hover:from-green-600 hover:to-emerald-700 transition-all duration-300 hover:scale-105 hover:shadow-2xl hover:shadow-green-500/25">
                                <div class="absolute inset-0 bg-gradient-to-r from-green-500/20 to-emerald-600/20 rounded-xl blur opacity-0 group-hover/btn:opacity-100 transition-opacity duration-300"></div>
                                <i class="fas fa-print mr-2 relative z-10"></i>
                                <span class="relative z-10">Print</span>
                            </a>
                            <a href="<?= base_url('appointment/' . $appointment['id'] . '/edit') ?>" class="group/btn relative inline-flex items-center px-6 py-3 bg-gradient-to-r from-blue-500 to-purple-600 text-white text-sm font-bold rounded-xl hover:from-blue-600 hover:to-purple-700 transition-all duration-300 hover:scale-105 hover:shadow-2xl hover:shadow-blue-500/25">
                                <div class="absolute inset-0 bg-gradient-to-r from-blue-500/20 to-purple-600/20 rounded-xl blur opacity-0 group-hover/btn:opacity-100 transition-opacity duration-300"></div>
                                <i class="fas fa-edit mr-2 relative z-10"></i>
                                <span class="relative z-10">Edit Appointment</span>
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Content -->
                <div class="p-8">
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                        <!-- Patient Information -->
                        <div class="space-y-6">
                            <div class="group/card relative">
                                <div class="absolute inset-0 bg-gradient-to-br from-blue-500/5 to-purple-600/5 rounded-2xl blur opacity-0 group-hover/card:opacity-100 transition-opacity duration-300"></div>
                                <div class="relative bg-white/60 border border-white/30 rounded-2xl p-6 shadow-lg group-hover/card:shadow-xl transition-all duration-300">
                                    <h4 class="text-lg font-bold text-gray-900 mb-4 flex items-center space-x-2">
                                        <i class="fas fa-user text-blue-600"></i>
                                        <span>Patient Information</span>
                                    </h4>
                                    <div class="space-y-3">
                                        <div class="flex justify-between items-center">
                                            <span class="text-gray-600 font-medium">Name:</span>
                                            <span class="text-gray-900 font-bold"><?= $appointment['first_name'] . ' ' . $appointment['last_name'] ?></span>
                                        </div>
                                        <div class="flex justify-between items-center">
                                            <span class="text-gray-600 font-medium">Patient ID:</span>
                                            <span class="text-gray-900 font-bold"><?= $appointment['patient_number'] ?></span>
                                        </div>
                                        <div class="flex justify-between items-center">
                                            <span class="text-gray-600 font-medium">Phone:</span>
                                            <span class="text-gray-900 font-bold"><?= $appointment['phone'] ?></span>
                                        </div>
                                        <div class="flex justify-between items-center">
                                            <span class="text-gray-600 font-medium">Email:</span>
                                            <span class="text-gray-900 font-bold"><?= $appointment['email'] ?></span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Appointment Details -->
                            <div class="group/card relative">
                                <div class="absolute inset-0 bg-gradient-to-br from-emerald-500/5 to-cyan-600/5 rounded-2xl blur opacity-0 group-hover/card:opacity-100 transition-opacity duration-300"></div>
                                <div class="relative bg-white/60 border border-white/30 rounded-2xl p-6 shadow-lg group-hover/card:shadow-xl transition-all duration-300">
                                    <h4 class="text-lg font-bold text-gray-900 mb-4 flex items-center space-x-2">
                                        <i class="fas fa-calendar-alt text-emerald-600"></i>
                                        <span>Appointment Details</span>
                                    </h4>
                                    <div class="space-y-3">
                                        <div class="flex justify-between items-center">
                                            <span class="text-gray-600 font-medium">Type:</span>
                                            <span class="text-gray-900 font-bold capitalize"><?= str_replace('_', ' ', $appointment['appointment_type']) ?></span>
                                        </div>
                                        <div class="flex justify-between items-center">
                                            <span class="text-gray-600 font-medium">Date:</span>
                                            <span class="text-gray-900 font-bold"><?= date('M d, Y', strtotime($appointment['appointment_date'])) ?></span>
                                        </div>
                                        <div class="flex justify-between items-center">
                                            <span class="text-gray-600 font-medium">Time:</span>
                                            <span class="text-gray-900 font-bold"><?= date('g:i A', strtotime($appointment['appointment_time'])) ?></span>
                                        </div>
                                        <div class="flex justify-between items-center">
                                            <span class="text-gray-600 font-medium">Duration:</span>
                                            <span class="text-gray-900 font-bold"><?= $appointment['duration'] ?> minutes</span>
                                        </div>
                                        <div class="flex justify-between items-center">
                                            <span class="text-gray-600 font-medium">Status:</span>
                                            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-bold <?php
                                                switch($appointment['status']) {
                                                    case 'scheduled':
                                                        echo 'bg-blue-100 text-blue-800';
                                                        break;
                                                    case 'confirmed':
                                                        echo 'bg-green-100 text-green-800';
                                                        break;
                                                    case 'completed':
                                                        echo 'bg-emerald-100 text-emerald-800';
                                                        break;
                                                    case 'cancelled':
                                                        echo 'bg-red-100 text-red-800';
                                                        break;
                                                    case 'no_show':
                                                        echo 'bg-orange-100 text-orange-800';
                                                        break;
                                                    default:
                                                        echo 'bg-gray-100 text-gray-800';
                                                }
                                            ?>">
                                                <?= ucfirst(str_replace('_', ' ', $appointment['status'])) ?>
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Notes and Actions -->
                        <div class="space-y-6">
                            <!-- Notes -->
                            <div class="group/card relative">
                                <div class="absolute inset-0 bg-gradient-to-br from-indigo-500/5 to-purple-600/5 rounded-2xl blur opacity-0 group-hover/card:opacity-100 transition-opacity duration-300"></div>
                                <div class="relative bg-white/60 border border-white/30 rounded-2xl p-6 shadow-lg group-hover/card:shadow-xl transition-all duration-300">
                                    <h4 class="text-lg font-bold text-gray-900 mb-4 flex items-center space-x-2">
                                        <i class="fas fa-sticky-note text-indigo-600"></i>
                                        <span>Notes</span>
                                    </h4>
                                    <div class="text-gray-700">
                                        <?php if (!empty($appointment['notes'])): ?>
                                            <p class="whitespace-pre-wrap"><?= $appointment['notes'] ?></p>
                                        <?php else: ?>
                                            <p class="text-gray-500 italic">No notes available</p>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>

                            <!-- Quick Actions -->
                            <div class="group/card relative">
                                <div class="absolute inset-0 bg-gradient-to-br from-amber-500/5 to-orange-600/5 rounded-2xl blur opacity-0 group-hover/card:opacity-100 transition-opacity duration-300"></div>
                                <div class="relative bg-white/60 border border-white/30 rounded-2xl p-6 shadow-lg group-hover/card:shadow-xl transition-all duration-300">
                                    <h4 class="text-lg font-bold text-gray-900 mb-4 flex items-center space-x-2">
                                        <i class="fas fa-bolt text-amber-600"></i>
                                        <span>Quick Actions</span>
                                    </h4>
                                    <div class="space-y-3">
                                        <?php if ($appointment['status'] == 'scheduled'): ?>
                                            <button onclick="confirmAppointment(<?= $appointment['id'] ?>)" class="w-full group/btn relative inline-flex items-center justify-center px-4 py-2 bg-gradient-to-r from-green-500 to-emerald-600 text-white text-sm font-bold rounded-xl hover:from-green-600 hover:to-emerald-700 transition-all duration-300 hover:scale-105">
                                                <div class="absolute inset-0 bg-gradient-to-r from-green-500/20 to-emerald-600/20 rounded-xl blur opacity-0 group-hover/btn:opacity-100 transition-opacity duration-300"></div>
                                                <i class="fas fa-check mr-2 relative z-10"></i>
                                                <span class="relative z-10">Confirm Appointment</span>
                                            </button>
                                        <?php endif; ?>
                                        
                                        <?php if (in_array($appointment['status'], ['scheduled', 'confirmed'])): ?>
                                            <button onclick="completeAppointment(<?= $appointment['id'] ?>)" class="w-full group/btn relative inline-flex items-center justify-center px-4 py-2 bg-gradient-to-r from-blue-500 to-purple-600 text-white text-sm font-bold rounded-xl hover:from-blue-600 hover:to-purple-700 transition-all duration-300 hover:scale-105">
                                                <div class="absolute inset-0 bg-gradient-to-r from-blue-500/20 to-purple-600/20 rounded-xl blur opacity-0 group-hover/btn:opacity-100 transition-opacity duration-300"></div>
                                                <i class="fas fa-check-circle mr-2 relative z-10"></i>
                                                <span class="relative z-10">Mark as Completed</span>
                                            </button>
                                        <?php endif; ?>
                                        
                                        <?php if (in_array($appointment['status'], ['scheduled', 'confirmed'])): ?>
                                            <button onclick="cancelAppointment(<?= $appointment['id'] ?>)" class="w-full group/btn relative inline-flex items-center justify-center px-4 py-2 bg-gradient-to-r from-red-500 to-pink-600 text-white text-sm font-bold rounded-xl hover:from-red-600 hover:to-pink-700 transition-all duration-300 hover:scale-105">
                                                <div class="absolute inset-0 bg-gradient-to-r from-red-500/20 to-pink-600/20 rounded-xl blur opacity-0 group-hover/btn:opacity-100 transition-opacity duration-300"></div>
                                                <i class="fas fa-times mr-2 relative z-10"></i>
                                                <span class="relative z-10">Cancel Appointment</span>
                                            </button>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function confirmAppointment(id) {
    if (confirm('Are you sure you want to confirm this appointment?')) {
        fetch(`<?= base_url('appointment') ?>/${id}/confirm`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                [window.csrfConfig.header]: window.getCsrfToken()
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.csrf_token) {
                window.refreshCsrfToken(data.csrf_token);
            }
            if (data.success) {
                location.reload();
            } else {
                alert('Error: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred while confirming the appointment.');
        });
    }
}

function completeAppointment(id) {
    if (confirm('Are you sure you want to mark this appointment as completed?')) {
        fetch(`<?= base_url('appointment') ?>/${id}/complete`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                [window.csrfConfig.header]: window.getCsrfToken()
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.csrf_token) {
                window.refreshCsrfToken(data.csrf_token);
            }
            if (data.success) {
                location.reload();
            } else {
                alert('Error: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred while completing the appointment.');
        });
    }
}

function cancelAppointment(id) {
    if (confirm('Are you sure you want to cancel this appointment?')) {
        fetch(`<?= base_url('appointment') ?>/${id}/cancel`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                [window.csrfConfig.header]: window.getCsrfToken()
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.csrf_token) {
                window.refreshCsrfToken(data.csrf_token);
            }
            if (data.success) {
                location.reload();
            } else {
                alert('Error: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred while cancelling the appointment.');
        });
    }
}
</script>
<?= $this->endSection() ?>
