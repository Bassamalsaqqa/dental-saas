<?= $this->extend('layouts/main_auth') ?>

<?= $this->section('content') ?>
<!-- Enhanced Appointment Creation with Advanced Design -->
<div class="min-h-screen bg-gradient-to-br from-slate-50 via-blue-50 to-indigo-100 relative overflow-hidden">
    <!-- Animated Background Elements -->
    <div class="absolute inset-0 overflow-hidden pointer-events-none">
        <div class="absolute -top-40 -right-40 w-80 h-80 bg-gradient-to-br from-blue-400/20 to-purple-600/20 rounded-full blur-3xl animate-pulse"></div>
        <div class="absolute -bottom-40 -left-40 w-80 h-80 bg-gradient-to-br from-emerald-400/20 to-cyan-600/20 rounded-full blur-3xl animate-pulse delay-1000"></div>
        <div class="absolute top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2 w-96 h-96 bg-gradient-to-br from-amber-400/10 to-orange-600/10 rounded-full blur-3xl animate-pulse delay-2000"></div>
    </div>

    <div class="container mx-auto relative z-10 px-4 py-6">

        <!-- Enhanced Appointment Form with Glassmorphism -->
        <div class="group relative">
            <div class="absolute inset-0 bg-gradient-to-br from-blue-500/10 to-purple-600/10 rounded-3xl blur-xl group-hover:blur-2xl transition-all duration-500 opacity-0 group-hover:opacity-100"></div>
            <div class="relative backdrop-blur-xl bg-white/80 border border-white/30 rounded-2xl shadow-2xl shadow-blue-500/10 group-hover:shadow-blue-500/20 transition-all duration-500 overflow-hidden">
                <!-- Form Header -->
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
                                <h3 class="text-2xl font-black text-gray-900 group-hover:text-blue-900 transition-colors duration-300">New Appointment</h3>
                                <p class="text-gray-600 font-medium">Fill in the details to schedule the appointment</p>
                            </div>
                        </div>
                        
                        <div class="flex items-center space-x-3">
                            <a href="<?= base_url('appointment') ?>" class="group/btn relative inline-flex items-center px-6 py-3 border-2 border-gray-300 text-gray-700 text-sm font-bold rounded-xl hover:bg-gradient-to-r hover:from-gray-50 hover:to-slate-50 hover:border-gray-400 transition-all duration-300 hover:scale-105">
                                <div class="absolute inset-0 bg-gradient-to-r from-gray-500/10 to-slate-500/10 rounded-xl blur opacity-0 group-hover/btn:opacity-100 transition-opacity duration-300"></div>
                                <i class="fas fa-arrow-left mr-2 relative z-10"></i>
                                <span class="relative z-10">Back to Appointments</span>
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Form Content -->
                <div class="p-8">
                    <form action="<?= base_url('appointment/store') ?>" method="POST" class="space-y-8">
                        <?= csrf_field() ?>
                        <!-- Patient Selection -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                            <div class="group/field relative">
                                <label class="block text-sm font-bold text-gray-700 mb-3 flex items-center space-x-2">
                                    <i class="fas fa-user text-blue-600"></i>
                                    <span>Patient *</span>
                                </label>
                                <div class="relative">
                                    <select name="patient_id" id="patientSelect" class="w-full px-4 py-3 bg-white/80 border-2 border-gray-200 rounded-xl shadow-lg focus:outline-none focus:ring-4 focus:ring-blue-500/20 focus:border-blue-500 transition-all duration-300 hover:shadow-xl" 
                                            data-searchable-select 
                                            data-search-url="<?= base_url('api/search/patients') ?>"
                                            data-placeholder="Search patients by name, phone, or ID..."
                                            required>
                                        <option value="">Select a patient</option>
                                        <?php foreach ($patients as $patient): ?>
                                            <option value="<?= $patient['id'] ?>" <?= (old('patient_id') == $patient['id']) ? 'selected' : '' ?>>
                                                <?= $patient['first_name'] . ' ' . $patient['last_name'] ?> (<?= $patient['phone'] ?>)
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                    <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
                                        <i class="fas fa-chevron-down text-gray-400"></i>
                                    </div>
                                </div>
                                <?php if (session()->getFlashdata('validation') && session()->getFlashdata('validation')->hasError('patient_id')): ?>
                                    <p class="text-red-500 text-sm mt-2 flex items-center space-x-1">
                                        <i class="fas fa-exclamation-circle"></i>
                                        <span><?= session()->getFlashdata('validation')->getError('patient_id') ?></span>
                                    </p>
                                <?php endif; ?>
                            </div>

                            <div class="group/field relative">
                                <label class="block text-sm font-bold text-gray-700 mb-3 flex items-center space-x-2">
                                    <i class="fas fa-calendar-alt text-purple-600"></i>
                                    <span>Appointment Type *</span>
                                </label>
                                <div class="relative">
                                    <select name="appointment_type" class="w-full px-4 py-3 bg-white/80 border-2 border-gray-200 rounded-xl shadow-lg focus:outline-none focus:ring-4 focus:ring-purple-500/20 focus:border-purple-500 transition-all duration-300 hover:shadow-xl" required>
                                        <option value="">Select type</option>
                                        <option value="consultation" <?= (old('appointment_type') == 'consultation') ? 'selected' : '' ?>>Consultation</option>
                                        <option value="treatment" <?= (old('appointment_type') == 'treatment') ? 'selected' : '' ?>>Treatment</option>
                                        <option value="follow_up" <?= (old('appointment_type') == 'follow_up') ? 'selected' : '' ?>>Follow-up</option>
                                        <option value="emergency" <?= (old('appointment_type') == 'emergency') ? 'selected' : '' ?>>Emergency</option>
                                        <option value="cleaning" <?= (old('appointment_type') == 'cleaning') ? 'selected' : '' ?>>Cleaning</option>
                                        <option value="checkup" <?= (old('appointment_type') == 'checkup') ? 'selected' : '' ?>>Checkup</option>
                                    </select>
                                    <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
                                        <i class="fas fa-chevron-down text-gray-400"></i>
                                    </div>
                                </div>
                                <?php if (session()->getFlashdata('validation') && session()->getFlashdata('validation')->hasError('appointment_type')): ?>
                                    <p class="text-red-500 text-sm mt-2 flex items-center space-x-1">
                                        <i class="fas fa-exclamation-circle"></i>
                                        <span><?= session()->getFlashdata('validation')->getError('appointment_type') ?></span>
                                    </p>
                                <?php endif; ?>
                            </div>
                        </div>

                        <!-- Date, Time & Duration -->
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                            <div class="group/field relative">
                                <label class="block text-sm font-bold text-gray-700 mb-3 flex items-center space-x-2">
                                    <i class="fas fa-calendar text-emerald-600"></i>
                                    <span>Appointment Date *</span>
                                </label>
                                <div class="relative">
                                    <input type="date" name="appointment_date" id="appointmentDate" value="<?= old('appointment_date', date('Y-m-d')) ?>" class="w-full px-4 py-3 bg-white/80 border-2 border-gray-200 rounded-xl shadow-lg focus:outline-none focus:ring-4 focus:ring-emerald-500/20 focus:border-emerald-500 transition-all duration-300 hover:shadow-xl" required>
                                    <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
                                        <i class="fas fa-calendar text-gray-400"></i>
                                    </div>
                                </div>
                                <?php if (session()->getFlashdata('validation') && session()->getFlashdata('validation')->hasError('appointment_date')): ?>
                                    <p class="text-red-500 text-sm mt-2 flex items-center space-x-1">
                                        <i class="fas fa-exclamation-circle"></i>
                                        <span><?= session()->getFlashdata('validation')->getError('appointment_date') ?></span>
                                    </p>
                                <?php endif; ?>
                            </div>

                            <div class="group/field relative">
                                <label class="block text-sm font-bold text-gray-700 mb-3 flex items-center space-x-2">
                                    <i class="fas fa-clock text-amber-600"></i>
                                    <span>Appointment Time *</span>
                                </label>
                                <div class="relative">
                                    <select name="appointment_time" id="appointmentTime" class="w-full px-4 py-3 bg-white/80 border-2 border-gray-200 rounded-xl shadow-lg focus:outline-none focus:ring-4 focus:ring-amber-500/20 focus:border-amber-500 transition-all duration-300 hover:shadow-xl" required>
                                        <option value="">Select time</option>
                                    </select>
                                    <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
                                        <i class="fas fa-chevron-down text-gray-400"></i>
                                    </div>
                                </div>
                                <?php if (session()->getFlashdata('validation') && session()->getFlashdata('validation')->hasError('appointment_time')): ?>
                                    <p class="text-red-500 text-sm mt-2 flex items-center space-x-1">
                                        <i class="fas fa-exclamation-circle"></i>
                                        <span><?= session()->getFlashdata('validation')->getError('appointment_time') ?></span>
                                    </p>
                                <?php endif; ?>
                            </div>

                            <div class="group/field relative">
                                <label class="block text-sm font-bold text-gray-700 mb-3 flex items-center space-x-2">
                                    <i class="fas fa-hourglass-half text-rose-600"></i>
                                    <span>Duration (minutes) *</span>
                                </label>
                                <div class="relative">
                                    <select name="duration" id="duration" class="w-full px-4 py-3 bg-white/80 border-2 border-gray-200 rounded-xl shadow-lg focus:outline-none focus:ring-4 focus:ring-rose-500/20 focus:border-rose-500 transition-all duration-300 hover:shadow-xl" required>
                                        <?php $defaultDuration = getAppointmentDuration(); ?>
                                        <option value="30" <?= (old('duration', $defaultDuration) == '30') ? 'selected' : '' ?>>30 minutes</option>
                                        <option value="45" <?= (old('duration', $defaultDuration) == '45') ? 'selected' : '' ?>>45 minutes</option>
                                        <option value="60" <?= (old('duration', $defaultDuration) == '60') ? 'selected' : '' ?>>60 minutes</option>
                                        <option value="90" <?= (old('duration', $defaultDuration) == '90') ? 'selected' : '' ?>>90 minutes</option>
                                        <option value="120" <?= (old('duration', $defaultDuration) == '120') ? 'selected' : '' ?>>120 minutes</option>
                                    </select>
                                    <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
                                        <i class="fas fa-chevron-down text-gray-400"></i>
                                    </div>
                                </div>
                                <?php if (session()->getFlashdata('validation') && session()->getFlashdata('validation')->hasError('duration')): ?>
                                    <p class="text-red-500 text-sm mt-2 flex items-center space-x-1">
                                        <i class="fas fa-exclamation-circle"></i>
                                        <span><?= session()->getFlashdata('validation')->getError('duration') ?></span>
                                    </p>
                                <?php endif; ?>
                            </div>
                        </div>

                        <!-- Notes Section -->
                        <div class="group/field relative">
                            <label class="block text-sm font-bold text-gray-700 mb-3 flex items-center space-x-2">
                                <i class="fas fa-sticky-note text-indigo-600"></i>
                                <span>Additional Notes</span>
                            </label>
                            <div class="relative">
                                <textarea name="notes" rows="4" class="w-full px-4 py-3 bg-white/80 border-2 border-gray-200 rounded-xl shadow-lg focus:outline-none focus:ring-4 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all duration-300 hover:shadow-xl resize-none" placeholder="Additional notes about the appointment..."><?= old('notes') ?></textarea>
                                <div class="absolute bottom-3 right-3 text-gray-400 text-xs">
                                    <i class="fas fa-edit"></i>
                                </div>
                            </div>
                        </div>

                        <!-- Form Actions -->
                        <div class="flex flex-col sm:flex-row justify-end gap-4 sm:gap-12 pt-6 border-t border-white/20">
                            <a href="<?= base_url('appointment') ?>" class="group/btn relative inline-flex items-center justify-center px-8 py-3 border-2 border-gray-300 text-gray-700 text-sm font-bold rounded-xl hover:bg-gradient-to-r hover:from-gray-50 hover:to-slate-50 hover:border-gray-400 transition-all duration-300 hover:scale-105">
                                <div class="absolute inset-0 bg-gradient-to-r from-gray-500/10 to-slate-500/10 rounded-xl blur opacity-0 group-hover/btn:opacity-100 transition-opacity duration-300"></div>
                                <i class="fas fa-times mr-2 relative z-10"></i>
                                <span class="relative z-10">Cancel</span>
                            </a>
                            <button type="submit" class="group/btn relative inline-flex items-center justify-center px-8 py-3 bg-gradient-to-r from-blue-500 to-purple-600 text-white text-sm font-bold rounded-xl hover:from-blue-600 hover:to-purple-700 transition-all duration-300 hover:scale-105 hover:shadow-2xl hover:shadow-blue-500/25">
                                <div class="absolute inset-0 bg-gradient-to-r from-blue-500/20 to-purple-600/20 rounded-xl blur opacity-0 group-hover/btn:opacity-100 transition-opacity duration-300"></div>
                                <i class="fas fa-calendar-check mr-2 relative z-10"></i>
                                <span class="relative z-10">Schedule Appointment</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const dateInput = document.getElementById('appointmentDate');
    const timeSelect = document.getElementById('appointmentTime');
    const durationSelect = document.getElementById('duration');

    function loadAvailableTimeSlots() {
        const date = dateInput.value;
        const duration = durationSelect.value;

        console.log('Loading time slots for date:', date, 'duration:', duration);

        if (date && duration) {
            // Show loading state
            timeSelect.innerHTML = '<option value="">Loading time slots...</option>';
            timeSelect.disabled = true;

            const url = `<?= base_url('appointment/available-time-slots') ?>?date=${date}&duration=${duration}`;
            console.log('Fetching URL:', url);

            fetch(url)
                .then(response => {
                    console.log('Response status:', response.status);
                    if (!response.ok) {
                        throw new Error(`HTTP error! status: ${response.status}`);
                    }
                    return response.json();
                })
                .then(data => {
                    console.log('Received data:', data);
                    timeSelect.innerHTML = '<option value="">Select time</option>';
                    
                    // Handle CSRF token if present
                    if (data.csrf_token && window.refreshCsrfToken) {
                        window.refreshCsrfToken(data.csrf_token);
                    }

                    let slots = [];
                    if (Array.isArray(data)) {
                        slots = data;
                    } else if (typeof data === 'object' && data !== null) {
                        // Filter out csrf_token and keep only slot objects
                        slots = Object.values(data).filter(item => 
                            typeof item === 'object' && item !== null && item.hasOwnProperty('value') && item.hasOwnProperty('display')
                        );
                    }

                    if (slots.length > 0) {
                        slots.forEach(timeSlot => {
                            const option = document.createElement('option');
                            option.value = timeSlot.value;
                            option.textContent = timeSlot.display;
                            timeSelect.appendChild(option);
                        });
                        console.log('Added', slots.length, 'time slots');
                    } else {
                        timeSelect.innerHTML = '<option value="">No available time slots</option>';
                        console.log('No time slots available');
                    }
                    
                    timeSelect.disabled = false;
                })
                .catch(error => {
                    console.error('Error loading time slots:', error);
                    timeSelect.innerHTML = '<option value="">Error loading time slots</option>';
                    timeSelect.disabled = false;
                    
                    // Show error message to user
                    const errorDiv = document.createElement('div');
                    errorDiv.className = 'text-red-500 text-sm mt-2';
                    errorDiv.innerHTML = '<i class="fas fa-exclamation-circle mr-1"></i>Failed to load time slots. Please try again.';
                    
                    // Remove any existing error message
                    const existingError = timeSelect.parentNode.querySelector('.text-red-500');
                    if (existingError) {
                        existingError.remove();
                    }
                    
                    timeSelect.parentNode.appendChild(errorDiv);
                });
        } else {
            timeSelect.innerHTML = '<option value="">Select date and duration first</option>';
            timeSelect.disabled = true;
            console.log('Date or duration missing - date:', date, 'duration:', duration);
        }
    }

    dateInput.addEventListener('change', loadAvailableTimeSlots);
    durationSelect.addEventListener('change', loadAvailableTimeSlots);

    // Load initial time slots if date is already set
    setTimeout(() => {
        loadAvailableTimeSlots();
    }, 100);
});
</script>
<?= $this->endSection() ?>
