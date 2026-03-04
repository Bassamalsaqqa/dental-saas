<?= $this->extend('layouts/main_auth') ?>

<?= $this->section('content') ?>
<!-- Enhanced Appointment Edit with Advanced Design -->
<div class="min-h-screen bg-slate-50">
    <!-- Animated Background Elements -->
    <div class="container mx-auto relative z-10 px-4 py-6">

        <!-- Enhanced Appointment Form with Glassmorphism -->
        <div class="group relative">
            <div class="relative bg-white rounded-2xl shadow-2xl shadow-blue-500/10 group-hover:shadow-blue-500/20 transition-all duration-500 overflow-hidden">
                <!-- Form Header -->
                <div class="p-8 border-b border-white/20">
                    <div class="flex flex-col lg:flex-row lg:justify-between lg:items-center space-y-4 lg:space-y-0">
                        <div class="flex items-center space-x-4">
                            <div class="relative">
                                <div class="absolute inset-0 bg-gradient-to-br from-blue-500 to-purple-600 rounded-2xl blur-lg opacity-75 group-hover:opacity-100 transition-opacity duration-300"></div>
                                <div class="relative w-12 h-12 bg-gradient-to-br from-blue-500 to-purple-600 rounded-2xl flex items-center justify-center shadow-xl group-hover:scale-110 group-hover:rotate-3 transition-all duration-300">
                                    <i class="fas fa-calendar-edit text-white text-lg"></i>
                                </div>
                            </div>
                            <div>
                                <h3 class="text-2xl font-black text-gray-900 group-hover:text-blue-900 transition-colors duration-300">Edit Appointment</h3>
                                <p class="text-gray-600 font-medium">Update appointment details for <?= $appointment['first_name'] . ' ' . $appointment['last_name'] ?></p>
                            </div>
                        </div>
                        
                        <div class="flex items-center space-x-3">
                            <a href="<?= !empty($return_to) ? base_url($return_to) : base_url('appointment') ?>" class="group/btn relative inline-flex items-center px-6 py-3 border-2 border-gray-300 text-gray-700 text-sm font-bold rounded-xl hover:bg-gradient-to-r hover:from-gray-50 hover:to-slate-50 hover:border-gray-400 transition-all duration-300 hover:scale-105">
                                <div class="absolute inset-0 bg-gradient-to-r from-gray-500/10 to-slate-500/10 rounded-xl blur opacity-0 group-hover/btn:opacity-100 transition-opacity duration-300"></div>
                                <i class="fas fa-arrow-left mr-2 relative z-10"></i>
                                <span class="relative z-10">Back</span>
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Form Content -->
                <div class="p-8">
                    <form action="<?= base_url('appointment/' . $appointment['id'] . '/update') ?>" method="POST" class="space-y-8">
                        <?= csrf_field() ?>
                        <?php if (!empty($return_to)): ?>
                            <input type="hidden" name="return_to" value="<?= esc($return_to) ?>">
                        <?php endif; ?>
                        <!-- Patient Selection -->
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                            <div class="group/field relative">
                                <label class="block text-sm font-bold text-gray-700 mb-3 flex items-center space-x-2">
                                    <i class="fas fa-user text-blue-600"></i>
                                    <span>Patient *</span>
                                </label>
                                <div class="relative">
                                    <select name="patient_id" id="patientSelect" class="w-full px-4 py-3 bg-white/80 border-2 border-gray-200 rounded-xl shadow-lg focus:outline-none focus:ring-4 focus:ring-blue-500/20 focus:border-blue-500 transition-all duration-300 hover:shadow-xl" 
                                            data-searchable-select 
                                            data-minimum-input-length="0"
                                            data-limit="5"
                                            data-search-url="<?= base_url('api/v1/search/patients') ?>"
                                            data-placeholder="Search patients by name, phone, or ID..."
                                            required>
                                        <option value="">Select a patient</option>
                                        <?php if (!empty($patients)): ?>
                                            <?php foreach ($patients as $patient): ?>
                                                <option value="<?= $patient['id'] ?>" selected>
                                                    <?= $patient['first_name'] . ' ' . $patient['last_name'] ?> (<?= $patient['phone'] ?>)
                                                </option>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
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
                                        <option value="consultation" <?= (old('appointment_type', $appointment['appointment_type']) == 'consultation') ? 'selected' : '' ?>>Consultation</option>
                                        <option value="treatment" <?= (old('appointment_type', $appointment['appointment_type']) == 'treatment') ? 'selected' : '' ?>>Treatment</option>
                                        <option value="follow_up" <?= (old('appointment_type', $appointment['appointment_type']) == 'follow_up') ? 'selected' : '' ?>>Follow-up</option>
                                        <option value="emergency" <?= (old('appointment_type', $appointment['appointment_type']) == 'emergency') ? 'selected' : '' ?>>Emergency</option>
                                        <option value="cleaning" <?= (old('appointment_type', $appointment['appointment_type']) == 'cleaning') ? 'selected' : '' ?>>Cleaning</option>
                                        <option value="checkup" <?= (old('appointment_type', $appointment['appointment_type']) == 'checkup') ? 'selected' : '' ?>>Checkup</option>
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

                            <div class="group/field relative">
                                <label class="block text-sm font-bold text-gray-700 mb-3 flex items-center space-x-2">
                                    <i class="fas fa-door-open text-indigo-600"></i>
                                    <span>Chair / Room *</span>
                                </label>
                                <div class="relative">
                                    <?php $selectedChair = old('chair_name', $appointment['chair_name'] ?? ($chair_options[0] ?? '')); ?>
                                    <select name="chair_name" id="chairName" class="w-full px-4 py-3 bg-white/80 border-2 border-gray-200 rounded-xl shadow-lg focus:outline-none focus:ring-4 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all duration-300 hover:shadow-xl" required>
                                        <option value="">Select chair / room</option>
                                        <?php if (!empty($selectedChair) && !in_array($selectedChair, ($chair_options ?? []), true)): ?>
                                            <option value="<?= esc($selectedChair) ?>" selected><?= esc($selectedChair) ?></option>
                                        <?php endif; ?>
                                        <?php foreach (($chair_options ?? []) as $chairOption): ?>
                                            <option value="<?= esc($chairOption) ?>" <?= ($selectedChair === $chairOption) ? 'selected' : '' ?>><?= esc($chairOption) ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                    <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
                                        <i class="fas fa-chevron-down text-gray-400"></i>
                                    </div>
                                </div>
                                <?php if (session()->getFlashdata('validation') && session()->getFlashdata('validation')->hasError('chair_name')): ?>
                                    <p class="text-red-500 text-sm mt-2 flex items-center space-x-1">
                                        <i class="fas fa-exclamation-circle"></i>
                                        <span><?= session()->getFlashdata('validation')->getError('chair_name') ?></span>
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
                                    <input type="text" name="appointment_date" id="appointmentDate" data-clinic-date="1" value="<?= old('appointment_date', $appointment['appointment_date']) ?>" class="w-full px-4 py-3 bg-white/80 border-2 border-gray-200 rounded-xl shadow-lg focus:outline-none focus:ring-4 focus:ring-emerald-500/20 focus:border-emerald-500 transition-all duration-300 hover:shadow-xl" required>
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
                                        <option value="30" <?= (old('duration', $appointment['duration'] ?? $defaultDuration) == '30') ? 'selected' : '' ?>>30 minutes</option>
                                        <option value="45" <?= (old('duration', $appointment['duration'] ?? $defaultDuration) == '45') ? 'selected' : '' ?>>45 minutes</option>
                                        <option value="60" <?= (old('duration', $appointment['duration'] ?? $defaultDuration) == '60') ? 'selected' : '' ?>>60 minutes</option>
                                        <option value="90" <?= (old('duration', $appointment['duration'] ?? $defaultDuration) == '90') ? 'selected' : '' ?>>90 minutes</option>
                                        <option value="120" <?= (old('duration', $appointment['duration'] ?? $defaultDuration) == '120') ? 'selected' : '' ?>>120 minutes</option>
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

                        <!-- Status Selection -->
                        <div class="group/field relative">
                            <label class="block text-sm font-bold text-gray-700 mb-3 flex items-center space-x-2">
                                <i class="fas fa-info-circle text-cyan-600"></i>
                                <span>Appointment Status *</span>
                            </label>
                            <div class="relative">
                                <select name="status" class="w-full px-4 py-3 bg-white/80 border-2 border-gray-200 rounded-xl shadow-lg focus:outline-none focus:ring-4 focus:ring-cyan-500/20 focus:border-cyan-500 transition-all duration-300 hover:shadow-xl" required>
                                    <option value="scheduled" <?= (old('status', $appointment['status']) == 'scheduled') ? 'selected' : '' ?>>Scheduled</option>
                                    <option value="confirmed" <?= (old('status', $appointment['status']) == 'confirmed') ? 'selected' : '' ?>>Confirmed</option>
                                    <option value="completed" <?= (old('status', $appointment['status']) == 'completed') ? 'selected' : '' ?>>Completed</option>
                                    <option value="cancelled" <?= (old('status', $appointment['status']) == 'cancelled') ? 'selected' : '' ?>>Cancelled</option>
                                    <option value="no_show" <?= (old('status', $appointment['status']) == 'no_show') ? 'selected' : '' ?>>No Show</option>
                                </select>
                                <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
                                    <i class="fas fa-chevron-down text-gray-400"></i>
                                </div>
                            </div>
                            <?php if (session()->getFlashdata('validation') && session()->getFlashdata('validation')->hasError('status')): ?>
                                <p class="text-red-500 text-sm mt-2 flex items-center space-x-1">
                                    <i class="fas fa-exclamation-circle"></i>
                                    <span><?= session()->getFlashdata('validation')->getError('status') ?></span>
                                </p>
                            <?php endif; ?>
                        </div>

                        <!-- Notes Section -->
                        <div class="group/field relative">
                            <label class="block text-sm font-bold text-gray-700 mb-3 flex items-center space-x-2">
                                <i class="fas fa-sticky-note text-indigo-600"></i>
                                <span>Additional Notes</span>
                            </label>
                            <div class="relative">
                                <textarea name="notes" rows="4" class="w-full px-4 py-3 bg-white/80 border-2 border-gray-200 rounded-xl shadow-lg focus:outline-none focus:ring-4 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all duration-300 hover:shadow-xl resize-none" placeholder="Additional notes about the appointment..."><?= old('notes', $appointment['notes']) ?></textarea>
                                <div class="absolute bottom-3 right-3 text-gray-400 text-xs">
                                    <i class="fas fa-edit"></i>
                                </div>
                            </div>
                        </div>

                        <!-- Form Actions -->
                        <div class="flex flex-col sm:flex-row justify-end gap-4 sm:gap-12 pt-6 border-t border-white/20">
                            <a href="<?= !empty($return_to) ? base_url($return_to) : base_url('appointment') ?>" class="group/btn relative inline-flex items-center justify-center px-8 py-3 border-2 border-gray-300 text-gray-700 text-sm font-bold rounded-xl hover:bg-gradient-to-r hover:from-gray-50 hover:to-slate-50 hover:border-gray-400 transition-all duration-300 hover:scale-105">
                                <div class="absolute inset-0 bg-gradient-to-r from-gray-500/10 to-slate-500/10 rounded-xl blur opacity-0 group-hover/btn:opacity-100 transition-opacity duration-300"></div>
                                <i class="fas fa-times mr-2 relative z-10"></i>
                                <span class="relative z-10">Cancel</span>
                            </a>
                            <button type="submit" class="group/btn relative inline-flex items-center justify-center px-8 py-3 bg-gradient-to-r from-blue-500 to-purple-600 text-white text-sm font-bold rounded-xl hover:from-blue-600 hover:to-purple-700 transition-all duration-300 hover:scale-105 hover:shadow-2xl hover:shadow-blue-500/25">
                                <div class="absolute inset-0 bg-gradient-to-r from-blue-500/20 to-purple-600/20 rounded-xl blur opacity-0 group-hover/btn:opacity-100 transition-opacity duration-300"></div>
                                <i class="fas fa-save mr-2 relative z-10"></i>
                                <span class="relative z-10">Update Appointment</span>
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
    const chairSelect = document.getElementById('chairName');
    const currentTime = '<?= $appointment['appointment_time'] ?>';
    const appointmentId = '<?= $appointment['id'] ?>';
    const apiBase = '<?= base_url('appointment/available-time-slots') ?>';

    function setTimeOptions(options, selectedValue = '') {
        timeSelect.replaceChildren();
        options.forEach((item) => {
            const option = document.createElement('option');
            option.value = item.value ?? '';
            option.textContent = item.label ?? '';
            if (selectedValue && option.value === selectedValue) {
                option.selected = true;
            }
            timeSelect.appendChild(option);
        });
    }

    function normalizeSlotsPayload(data) {
        if (data && data.csrf_token && window.refreshCsrfToken) {
            window.refreshCsrfToken(data.csrf_token);
        }

        if (Array.isArray(data)) {
            return { slots: data, error: null };
        }

        if (data && typeof data === 'object') {
            const directSlots = Array.isArray(data.slots) ? data.slots : [];
            if (directSlots.length > 0) {
                return { slots: directSlots, error: null };
            }

            const extractedSlots = Object.values(data).filter((item) =>
                item && typeof item === 'object' && Object.prototype.hasOwnProperty.call(item, 'value') && Object.prototype.hasOwnProperty.call(item, 'display')
            );

            return {
                slots: extractedSlots,
                error: typeof data.error === 'string' ? data.error : null,
            };
        }

        return { slots: [], error: 'Invalid response format.' };
    }

    function appendCurrentTimeIfMissing(slots) {
        if (!currentTime) {
            return;
        }
        const hasCurrent = slots.some((slot) => slot && slot.value === currentTime);
        if (!hasCurrent) {
            const currentOption = document.createElement('option');
            currentOption.value = currentTime;
            currentOption.textContent = `${currentTime} (Current)`;
            currentOption.selected = true;
            timeSelect.appendChild(currentOption);
        }
    }

    function loadAvailableTimeSlots() {
        const rawDate = (dateInput.value || '').trim();
        const date = window.parseClinicDateToIso ? (window.parseClinicDateToIso(rawDate) || rawDate) : rawDate;
        const duration = durationSelect.value;
        const chairName = (chairSelect?.value || '').trim();

        if (date && duration && chairName) {
            if (!/^\d{4}-\d{2}-\d{2}$/.test(date)) {
                setTimeOptions([{ value: '', label: 'Enter a valid date first' }], '');
                appendCurrentTimeIfMissing([]);
                return;
            }

            setTimeOptions([{ value: '', label: 'Loading time slots...' }], '');

            fetch(`${apiBase}?date=${date}&duration=${duration}&exclude_id=${appointmentId}&chair_name=${encodeURIComponent(chairName)}`)
                .then(response => {
                    if (!response.ok) {
                        throw new Error(`HTTP ${response.status}`);
                    }
                    return response.json();
                })
                .then(data => {
                    const normalized = normalizeSlotsPayload(data);
                    if (normalized.slots.length > 0) {
                        const options = [{ value: '', label: 'Select time' }].concat(
                            normalized.slots.map((slot) => ({ value: slot.value, label: slot.display }))
                        );
                        setTimeOptions(options, currentTime);
                        appendCurrentTimeIfMissing(normalized.slots);
                        return;
                    }

                    if (normalized.error) {
                        setTimeOptions([{ value: '', label: normalized.error }], '');
                    } else {
                        setTimeOptions([{ value: '', label: 'No available time slots' }], '');
                    }
                    appendCurrentTimeIfMissing(normalized.slots);
                })
                .catch(error => {
                    console.error('Error loading time slots:', error);
                    setTimeOptions([{ value: '', label: 'Error loading times' }], '');
                    appendCurrentTimeIfMissing([]);
                });
        } else {
            setTimeOptions([{ value: '', label: 'Select chair, date, and duration first' }], '');
            appendCurrentTimeIfMissing([]);
        }
    }

    dateInput.addEventListener('change', loadAvailableTimeSlots);
    durationSelect.addEventListener('change', loadAvailableTimeSlots);
    chairSelect.addEventListener('change', loadAvailableTimeSlots);

    // Load initial time slots (this will also set the current time)
    loadAvailableTimeSlots();
});
</script>
<?= $this->endSection() ?>


