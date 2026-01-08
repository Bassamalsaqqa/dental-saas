<?= $this->extend('layouts/main_auth') ?>

<?= $this->section('content') ?>
<!-- Enhanced Treatment Creation with Advanced Design -->
<div class="min-h-screen bg-gradient-to-br from-slate-50 via-amber-50 to-orange-100 relative overflow-hidden">
    <!-- Animated Background Elements -->
    <div class="absolute inset-0 overflow-hidden pointer-events-none">
        <div class="absolute -top-40 -right-40 w-80 h-80 bg-gradient-to-br from-amber-400/20 to-orange-600/20 rounded-full blur-3xl animate-pulse"></div>
        <div class="absolute -bottom-40 -left-40 w-80 h-80 bg-gradient-to-br from-red-400/20 to-pink-600/20 rounded-full blur-3xl animate-pulse delay-1000"></div>
        <div class="absolute top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2 w-96 h-96 bg-gradient-to-br from-blue-400/10 to-cyan-600/10 rounded-full blur-3xl animate-pulse delay-2000"></div>
    </div>

    <div class="container mx-auto relative z-10 px-4 py-6">

        <!-- Enhanced Treatment Form with Glassmorphism -->
        <div class="group relative">
            <div class="absolute inset-0 bg-gradient-to-br from-amber-500/10 to-orange-600/10 rounded-3xl blur-xl group-hover:blur-2xl transition-all duration-500 opacity-0 group-hover:opacity-100"></div>
            <div class="relative backdrop-blur-xl bg-white/80 border border-white/30 rounded-2xl shadow-2xl shadow-amber-500/10 group-hover:shadow-amber-500/20 transition-all duration-500 overflow-hidden">
                <!-- Form Header -->
                <div class="p-8 border-b border-white/20">
                    <div class="flex flex-col lg:flex-row lg:justify-between lg:items-center space-y-4 lg:space-y-0">
                        <div class="flex items-center space-x-4">
                            <div class="relative">
                                <div class="absolute inset-0 bg-gradient-to-br from-amber-500 to-orange-600 rounded-2xl blur-lg opacity-75 group-hover:opacity-100 transition-opacity duration-300"></div>
                                <div class="relative w-12 h-12 bg-gradient-to-br from-amber-500 to-orange-600 rounded-2xl flex items-center justify-center shadow-xl group-hover:scale-110 group-hover:rotate-3 transition-all duration-300">
                                    <i class="fas fa-tooth text-white text-lg"></i>
                                </div>
                            </div>
                            <div>
                                <h3 class="text-2xl font-black text-gray-900 group-hover:text-amber-900 transition-colors duration-300">Create New Treatment</h3>
                                <p class="text-gray-600 font-medium">Enter treatment details to create a new procedure record</p>
                            </div>
                        </div>
                        
                        <div class="flex items-center space-x-3">
                            <a href="<?= base_url('treatment') ?>" class="group/btn relative inline-flex items-center px-6 py-3 border-2 border-gray-300 text-gray-700 text-sm font-bold rounded-xl hover:bg-gradient-to-r hover:from-gray-50 hover:to-slate-50 hover:border-gray-400 transition-all duration-300 hover:scale-105">
                                <div class="absolute inset-0 bg-gradient-to-r from-gray-500/10 to-slate-500/10 rounded-xl blur opacity-0 group-hover/btn:opacity-100 transition-opacity duration-300"></div>
                                <i class="fas fa-arrow-left mr-2 relative z-10"></i>
                                <span class="relative z-10">Back to Treatments</span>
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Form Content -->
                <div class="p-8"> 
                    <form action="<?= base_url('treatment/store') ?>" method="POST" class="space-y-8">
                        <?= csrf_field() ?>
                        <!-- Patient Selection -->
                        <div class="group/field relative">
                            <label class="block text-sm font-bold text-gray-700 mb-3 flex items-center space-x-2">
                                <i class="fas fa-user text-blue-600"></i>
                                <span>Select Patient *</span>
                            </label>
                            <div class="relative">
                                <select id="patient_id" name="patient_id" 
                                        class="w-full px-4 py-3 bg-white/80 border-2 border-gray-200 rounded-xl shadow-lg focus:outline-none focus:ring-4 focus:ring-blue-500/20 focus:border-blue-500 transition-all duration-300 hover:shadow-xl" 
                                        data-searchable-select 
                                        data-search-url="<?= base_url('api/search/patients') ?>"
                                        data-placeholder="Search patients by name, phone, or ID..."
                                        required>
                                    <option value="">Choose a patient...</option>
                                    <?php if (!empty($patients)): ?>
                                        <?php foreach ($patients as $patient): ?>
                                            <option value="<?= $patient['id'] ?>" <?= old('patient_id') == $patient['id'] ? 'selected' : '' ?>>
                                                <?= $patient['first_name'] . ' ' . $patient['last_name'] ?> 
                                                (<?= $patient['phone'] ?>)
                                            </option>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </select>
                                <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
                                    <i class="fas fa-chevron-down text-gray-400"></i>
                                </div>
                            </div>
                            <?php if ($validation && $validation->hasError('patient_id')): ?>
                                <p class="text-red-500 text-sm mt-2 flex items-center space-x-1">
                                    <i class="fas fa-exclamation-circle"></i>
                                    <span><?= $validation->getError('patient_id') ?></span>
                                </p>
                            <?php endif; ?>
                        </div>

                        <!-- Treatment Type and Tooth Number -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                            <div class="group/field relative">
                                <label class="block text-sm font-bold text-gray-700 mb-3 flex items-center space-x-2">
                                    <i class="fas fa-stethoscope text-purple-600"></i>
                                    <span>Treatment Type *</span>
                                </label>
                                <div class="relative">
                                    <select id="treatment_type" name="treatment_type" 
                                            class="w-full px-4 py-3 bg-white/80 border-2 border-gray-200 rounded-xl shadow-lg focus:outline-none focus:ring-4 focus:ring-purple-500/20 focus:border-purple-500 transition-all duration-300 hover:shadow-xl" 
                                            data-searchable-select 
                                            data-search-url="<?= base_url('api/search/treatment-types') ?>"
                                            data-placeholder="Search treatment types..."
                                            required>
                                        <option value="">Select Treatment Type</option>
                                        <?php if (!empty($treatment_types)): ?>
                                            <?php foreach ($treatment_types as $key => $value): ?>
                                                <option value="<?= $key ?>" <?= old('treatment_type') == $key ? 'selected' : '' ?>>
                                                    <?= $value ?>
                                                </option>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                    </select>
                                    <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
                                        <i class="fas fa-chevron-down text-gray-400"></i>
                                    </div>
                                </div>
                                <?php if ($validation && $validation->hasError('treatment_type')): ?>
                                    <p class="text-red-500 text-sm mt-2 flex items-center space-x-1">
                                        <i class="fas fa-exclamation-circle"></i>
                                        <span><?= $validation->getError('treatment_type') ?></span>
                                    </p>
                                <?php endif; ?>
                            </div>

                            <div class="group/field relative">
                                <label class="block text-sm font-bold text-gray-700 mb-3 flex items-center space-x-2">
                                    <i class="fas fa-tooth text-cyan-600"></i>
                                    <span>Tooth Number</span>
                                </label>
                                <div class="relative">
                                    <input type="text" id="tooth_number" name="tooth_number" 
                                           class="w-full px-4 py-3 bg-white/80 border-2 border-gray-200 rounded-xl shadow-lg focus:outline-none focus:ring-4 focus:ring-cyan-500/20 focus:border-cyan-500 transition-all duration-300 hover:shadow-xl"
                                           value="<?= old('tooth_number') ?>" maxlength="10" placeholder="e.g., 11, 12, 21, 22, etc.">
                                    <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
                                        <i class="fas fa-tooth text-gray-400"></i>
                                    </div>
                                </div>
                                <?php if ($validation && $validation->hasError('tooth_number')): ?>
                                    <p class="text-red-500 text-sm mt-2 flex items-center space-x-1">
                                        <i class="fas fa-exclamation-circle"></i>
                                        <span><?= $validation->getError('tooth_number') ?></span>
                                    </p>
                                <?php endif; ?>
                                <p class="text-gray-500 text-sm mt-2 flex items-center space-x-1">
                                    <i class="fas fa-info-circle"></i>
                                    <span>Leave empty if not applicable</span>
                                </p>
                            </div>
                        </div>

                        <!-- Treatment Description -->
                        <div class="group/field relative">
                            <label class="block text-sm font-bold text-gray-700 mb-3 flex items-center space-x-2">
                                <i class="fas fa-align-left text-indigo-600"></i>
                                <span>Treatment Description *</span>
                            </label>
                            <div class="relative">
                                <textarea id="description" name="description" rows="4" 
                                          class="w-full px-4 py-3 bg-white/80 border-2 border-gray-200 rounded-xl shadow-lg focus:outline-none focus:ring-4 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all duration-300 hover:shadow-xl resize-none"
                                          placeholder="Provide detailed description of the treatment procedure..." required><?= old('description') ?></textarea>
                                <div class="absolute bottom-3 right-3 text-gray-400 text-xs">
                                    <i class="fas fa-edit"></i>
                                </div>
                            </div>
                            <?php if ($validation && $validation->hasError('description')): ?>
                                <p class="text-red-500 text-sm mt-2 flex items-center space-x-1">
                                    <i class="fas fa-exclamation-circle"></i>
                                    <span><?= $validation->getError('description') ?></span>
                                </p>
                            <?php endif; ?>
                        </div>

                        <!-- Cost and Duration -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                            <div class="group/field relative">
                                <label class="block text-sm font-bold text-gray-700 mb-3 flex items-center space-x-2">
                                    <i class="fas fa-dollar-sign text-green-600"></i>
                                    <span>Treatment Cost *</span>
                                </label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                                        <span class="text-gray-500 font-bold"><?= getCurrencySymbol() ?></span>
                                    </div>
                                    <input type="number" id="cost" name="cost" step="0.01" min="0"
                                           class="w-full px-4 py-3 pl-8 bg-white/80 border-2 border-gray-200 rounded-xl shadow-lg focus:outline-none focus:ring-4 focus:ring-green-500/20 focus:border-green-500 transition-all duration-300 hover:shadow-xl"
                                           value="<?= old('cost') ?>" placeholder="0.00" required>
                                    <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
                                        <i class="fas fa-dollar-sign text-gray-400"></i>
                                    </div>
                                </div>
                                <?php if ($validation && $validation->hasError('cost')): ?>
                                    <p class="text-red-500 text-sm mt-2 flex items-center space-x-1">
                                        <i class="fas fa-exclamation-circle"></i>
                                        <span><?= $validation->getError('cost') ?></span>
                                    </p>
                                <?php endif; ?>
                            </div>

                            <div class="group/field relative">
                                <label class="block text-sm font-bold text-gray-700 mb-3 flex items-center space-x-2">
                                    <i class="fas fa-clock text-amber-600"></i>
                                    <span>Estimated Duration (days) *</span>
                                </label>
                                <div class="relative">
                                    <input type="number" id="estimated_duration" name="estimated_duration" 
                                           class="w-full px-4 py-3 bg-white/80 border-2 border-gray-200 rounded-xl shadow-lg focus:outline-none focus:ring-4 focus:ring-amber-500/20 focus:border-amber-500 transition-all duration-300 hover:shadow-xl"
                                           value="<?= old('estimated_duration') ?>" min="1" placeholder="e.g., 7" required>
                                    <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
                                        <i class="fas fa-clock text-gray-400"></i>
                                    </div>
                                </div>
                                <?php if ($validation && $validation->hasError('estimated_duration')): ?>
                                    <p class="text-red-500 text-sm mt-2 flex items-center space-x-1">
                                        <i class="fas fa-exclamation-circle"></i>
                                        <span><?= $validation->getError('estimated_duration') ?></span>
                                    </p>
                                <?php endif; ?>
                            </div>
                        </div>

                        <!-- Start Date -->
                        <div class="group/field relative">
                            <label class="block text-sm font-bold text-gray-700 mb-3 flex items-center space-x-2">
                                <i class="fas fa-calendar text-rose-600"></i>
                                <span>Start Date *</span>
                            </label>
                            <div class="relative">
                                <input type="date" id="start_date" name="start_date" 
                                       class="w-full px-4 py-3 bg-white/80 border-2 border-gray-200 rounded-xl shadow-lg focus:outline-none focus:ring-4 focus:ring-rose-500/20 focus:border-rose-500 transition-all duration-300 hover:shadow-xl"
                                       value="<?= old('start_date') ?: date('Y-m-d') ?>" required>
                                <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
                                    <i class="fas fa-calendar text-gray-400"></i>
                                </div>
                            </div>
                            <?php if ($validation && $validation->hasError('start_date')): ?>
                                <p class="text-red-500 text-sm mt-2 flex items-center space-x-1">
                                    <i class="fas fa-exclamation-circle"></i>
                                    <span><?= $validation->getError('start_date') ?></span>
                                </p>
                            <?php endif; ?>
                        </div>

                        <!-- Form Actions -->
                        <div class="flex flex-col sm:flex-row justify-end gap-4 sm:gap-12 pt-6 border-t border-white/20">
                            <a href="<?= base_url('treatment') ?>" class="group/btn relative inline-flex items-center justify-center px-8 py-3 border-2 border-gray-300 text-gray-700 text-sm font-bold rounded-xl hover:bg-gradient-to-r hover:from-gray-50 hover:to-slate-50 hover:border-gray-400 transition-all duration-300 hover:scale-105">
                                <div class="absolute inset-0 bg-gradient-to-r from-gray-500/10 to-slate-500/10 rounded-xl blur opacity-0 group-hover/btn:opacity-100 transition-opacity duration-300"></div>
                                <i class="fas fa-times mr-2 relative z-10"></i>
                                <span class="relative z-10">Cancel</span>
                            </a>
                            <button type="submit" class="group/btn relative inline-flex items-center justify-center px-8 py-3 bg-gradient-to-r from-amber-500 to-orange-600 text-white text-sm font-bold rounded-xl hover:from-amber-600 hover:to-orange-700 transition-all duration-300 hover:scale-105 hover:shadow-2xl hover:shadow-amber-500/25">
                                <div class="absolute inset-0 bg-gradient-to-r from-amber-500/20 to-orange-600/20 rounded-xl blur opacity-0 group-hover/btn:opacity-100 transition-opacity duration-300"></div>
                                <i class="fas fa-tooth mr-2 relative z-10"></i>
                                <span class="relative z-10">Create Treatment</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Auto-fill today's date if start_date is empty
document.addEventListener('DOMContentLoaded', function() {
    const startDateInput = document.getElementById('start_date');
    if (!startDateInput.value) {
        startDateInput.value = new Date().toISOString().split('T')[0];
    }
    
    // Add some basic form validation
    const form = document.querySelector('form');
    form.addEventListener('submit', function(e) {
        const cost = parseFloat(document.getElementById('cost').value);
        const duration = parseInt(document.getElementById('estimated_duration').value);
        
        if (cost <= 0) {
            alert('Please enter a valid cost greater than 0.');
            e.preventDefault();
            return false;
        }
        
        if (duration <= 0) {
            alert('Please enter a valid duration greater than 0 days.');
            e.preventDefault();
            return false;
        }
    });
});
</script>
<?= $this->endSection() ?>
