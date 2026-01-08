<?= $this->extend('layouts/main_auth') ?>

<?= $this->section('content') ?>
<div class="px-6 py-8">
    <!-- Page Header -->
    <div class="mb-8">
        <div class="flex flex-col lg:flex-row lg:justify-between lg:items-center space-y-6 lg:space-y-0">
            <div>
                <h1 class="text-4xl font-bold text-gray-800 mb-3">Doctor Details</h1>
                <p class="text-gray-600 text-lg">Dr. <?= esc($doctor['first_name'] . ' ' . $doctor['last_name']) ?></p>
            </div>
            <div class="flex flex-col sm:flex-row space-y-3 sm:space-y-0 sm:space-x-4">
                <a href="<?= base_url('doctors') ?>" class="inline-flex items-center px-4 py-2 bg-gray-600 text-white text-sm font-medium rounded-lg hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition-all duration-200 shadow-lg hover:shadow-xl">
                    <i class="fas fa-arrow-left mr-2"></i>Back to Doctors
                </a>
                <?php if (has_permission('users', 'edit')): ?>
                    <a href="<?= base_url('doctors/' . $doctor['id'] . '/edit') ?>" class="inline-flex items-center px-4 py-2 bg-gradient-to-r from-yellow-500 to-orange-600 text-white text-sm font-medium rounded-lg hover:from-yellow-600 hover:to-orange-700 focus:outline-none focus:ring-2 focus:ring-yellow-500 focus:ring-offset-2 transition-all duration-200 shadow-lg hover:shadow-xl">
                        <i class="fas fa-edit mr-2"></i>Edit Doctor
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

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Doctor Profile Card -->
        <div class="lg:col-span-1">
            <div class="bg-white rounded-xl shadow-lg border border-gray-200 overflow-hidden">
                <!-- Doctor Header -->
                <div class="bg-gradient-to-r from-green-500 to-emerald-600 p-8 text-white">
                    <div class="text-center">
                        <div class="w-24 h-24 bg-white/20 rounded-full flex items-center justify-center backdrop-blur-sm mx-auto mb-4">
                            <i class="fas fa-user-md text-white text-3xl"></i>
                        </div>
                        <h2 class="text-2xl font-bold">Dr. <?= esc($doctor['first_name'] . ' ' . $doctor['last_name']) ?></h2>
                        <p class="text-green-100 text-lg"><?= esc($doctor['role_name']) ?></p>
                    </div>
                </div>

                <!-- Doctor Info -->
                <div class="p-6">
                    <div class="space-y-4">
                        <div class="flex items-center space-x-3">
                            <i class="fas fa-envelope text-blue-600 w-5"></i>
                            <span class="text-gray-700"><?= esc($doctor['email']) ?></span>
                        </div>
                        
                        <div class="flex items-center space-x-3">
                            <i class="fas fa-phone text-green-600 w-5"></i>
                            <span class="text-gray-700"><?= esc($doctor['phone']) ?></span>
                        </div>
                        
                        <div class="flex items-center space-x-3">
                            <i class="fas fa-user text-purple-600 w-5"></i>
                            <span class="text-gray-700">@<?= esc($doctor['username']) ?></span>
                        </div>
                        
                        <?php if (!empty($doctor['address'])): ?>
                            <div class="flex items-start space-x-3">
                                <i class="fas fa-map-marker-alt text-red-600 w-5 mt-1"></i>
                                <span class="text-gray-700"><?= esc($doctor['address']) ?></span>
                            </div>
                        <?php endif; ?>
                        
                        <?php if (!empty($doctor['hire_date'])): ?>
                            <div class="flex items-center space-x-3">
                                <i class="fas fa-calendar text-indigo-600 w-5"></i>
                                <span class="text-gray-700">Hired: <?= formatDate($doctor['hire_date']) ?></span>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Doctor Details -->
        <div class="lg:col-span-2">
            <div class="space-y-6">
                <!-- Medical Information -->
                <div class="bg-white rounded-xl shadow-lg border border-gray-200 p-6">
                    <h3 class="text-xl font-bold text-gray-800 mb-6 flex items-center">
                        <i class="fas fa-user-md text-green-600 mr-3"></i>
                        Medical Information
                    </h3>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-600 mb-2">License Number</label>
                            <p class="text-lg font-semibold text-gray-800"><?= esc($doctor['license_number']) ?></p>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-600 mb-2">Specialization</label>
                            <p class="text-lg font-semibold text-gray-800"><?= ucfirst(str_replace('_', ' ', $doctor['specialization'])) ?></p>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-600 mb-2">Years of Experience</label>
                            <p class="text-lg font-semibold text-gray-800"><?= $doctor['years_experience'] ?> years</p>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-600 mb-2">Consultation Fee</label>
                            <p class="text-lg font-semibold text-gray-800">$<?= number_format($doctor['consultation_fee'], 2) ?></p>
                        </div>
                        
                        <?php if (!empty($doctor['department'])): ?>
                            <div>
                                <label class="block text-sm font-medium text-gray-600 mb-2">Department</label>
                                <p class="text-lg font-semibold text-gray-800"><?= esc($doctor['department']) ?></p>
                            </div>
                        <?php endif; ?>
                    </div>
                    
                    <?php if (!empty($doctor['medical_qualifications'])): ?>
                        <div class="mt-6">
                            <label class="block text-sm font-medium text-gray-600 mb-2">Medical Qualifications</label>
                            <p class="text-gray-800"><?= esc($doctor['medical_qualifications']) ?></p>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Account Status -->
                <div class="bg-white rounded-xl shadow-lg border border-gray-200 p-6">
                    <h3 class="text-xl font-bold text-gray-800 mb-6 flex items-center">
                        <i class="fas fa-info-circle text-blue-600 mr-3"></i>
                        Account Information
                    </h3>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-600 mb-2">Account Status</label>
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium <?= $doctor['active'] ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' ?>">
                                <i class="fas fa-circle w-2 h-2 mr-2 <?= $doctor['active'] ? 'text-green-500' : 'text-red-500' ?>"></i>
                                <?= $doctor['active'] ? 'Active' : 'Inactive' ?>
                            </span>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-600 mb-2">Role</label>
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-blue-100 text-blue-800">
                                <?= esc($doctor['role_name']) ?>
                            </span>
                        </div>
                        
                        <?php if (!empty($doctor['last_login'])): ?>
                            <div>
                                <label class="block text-sm font-medium text-gray-600 mb-2">Last Login</label>
                                <p class="text-gray-800"><?= formatDateTime($doctor['last_login']) ?></p>
                            </div>
                        <?php endif; ?>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-600 mb-2">Member Since</label>
                            <p class="text-gray-800"><?= formatDate($doctor['created_on']) ?></p>
                        </div>
                    </div>
                </div>

                <!-- Quick Actions -->
                <div class="bg-white rounded-xl shadow-lg border border-gray-200 p-6">
                    <h3 class="text-xl font-bold text-gray-800 mb-6 flex items-center">
                        <i class="fas fa-bolt text-yellow-600 mr-3"></i>
                        Quick Actions
                    </h3>
                    
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                        <a href="<?= base_url('examinations/create?doctor_id=' . $doctor['id']) ?>" 
                           class="flex items-center justify-center px-4 py-3 bg-blue-500 text-white rounded-lg hover:bg-blue-600 transition-colors">
                            <i class="fas fa-stethoscope mr-2"></i>
                            New Examination
                        </a>
                        
                        <a href="<?= base_url('treatments/create?doctor_id=' . $doctor['id']) ?>" 
                           class="flex items-center justify-center px-4 py-3 bg-green-500 text-white rounded-lg hover:bg-green-600 transition-colors">
                            <i class="fas fa-tooth mr-2"></i>
                            New Treatment
                        </a>
                        
                        <a href="<?= base_url('prescriptions/create?doctor_id=' . $doctor['id']) ?>" 
                           class="flex items-center justify-center px-4 py-3 bg-purple-500 text-white rounded-lg hover:bg-purple-600 transition-colors">
                            <i class="fas fa-prescription mr-2"></i>
                            New Prescription
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>
