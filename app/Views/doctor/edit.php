<?= $this->extend('layouts/main_auth') ?>

<?= $this->section('content') ?>
<div class="px-6 py-8">
    <!-- Page Header -->
    <div class="mb-8">
        <div class="flex flex-col lg:flex-row lg:justify-between lg:items-center space-y-6 lg:space-y-0">
            <div>
                <h1 class="text-4xl font-bold text-gray-800 mb-3">Edit Doctor</h1>
                <p class="text-gray-600 text-lg">Update Dr. <?= esc($doctor['first_name'] . ' ' . $doctor['last_name']) ?>'s information</p>
            </div>
            <div class="flex flex-col sm:flex-row space-y-3 sm:space-y-0 sm:space-x-4">
                <a href="<?= base_url('doctors/' . $doctor['id']) ?>" class="inline-flex items-center px-4 py-2 bg-gray-600 text-white text-sm font-medium rounded-lg hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition-all duration-200 shadow-lg hover:shadow-xl">
                    <i class="fas fa-arrow-left mr-2"></i>Back to Doctor
                </a>
            </div>
        </div>
    </div>

    <!-- Flash Messages -->
    <?php if (session()->getFlashdata('validation')): ?>
        <div class="mb-6 bg-red-50 border border-red-200 rounded-xl p-4 shadow-sm">
            <div class="flex">
                <div class="flex-shrink-0">
                    <i class="fas fa-exclamation-circle text-red-400 text-xl"></i>
                </div>
                <div class="ml-3">
                    <h3 class="text-sm font-medium text-red-800">Validation Errors</h3>
                    <div class="mt-2 text-sm text-red-700">
                        <ul class="list-disc list-inside space-y-1">
                            <?php foreach (session()->getFlashdata('validation')->getErrors() as $error): ?>
                                <li><?= esc($error) ?></li>
                            <?php endforeach; ?>
                        </ul>
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

    <form action="<?= base_url('doctors/' . $doctor['id'] . '/update') ?>" method="POST" class="space-y-8">
        <?= csrf_field() ?>
        
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
            <!-- Basic Information -->
            <div class="bg-white rounded-lg shadow-lg p-6">
                <h3 class="text-xl font-bold text-gray-800 mb-6 flex items-center">
                    <i class="fas fa-user text-blue-600 mr-3"></i>
                    Basic Information
                </h3>
                
                <div class="space-y-4">
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">First Name *</label>
                            <input type="text" name="first_name" value="<?= old('first_name', $doctor['first_name']) ?>" 
                                   class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors" required>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Last Name *</label>
                            <input type="text" name="last_name" value="<?= old('last_name', $doctor['last_name']) ?>" 
                                   class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors" required>
                        </div>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Email *</label>
                        <input type="email" name="email" value="<?= old('email', $doctor['email']) ?>" 
                               class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors" required>
                    </div>
                    
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Username *</label>
                            <input type="text" name="username" value="<?= old('username', $doctor['username']) ?>" 
                                   class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors" required>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Phone *</label>
                            <input type="tel" name="phone" value="<?= old('phone', $doctor['phone']) ?>" 
                                   class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors" required>
                        </div>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">New Password</label>
                        <input type="password" name="password" 
                               class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors"
                               placeholder="Leave blank to keep current password">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Address</label>
                        <textarea name="address" rows="3" 
                                  class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors"><?= old('address', $doctor['address'] ?? '') ?></textarea>
                    </div>
                </div>
            </div>

            <!-- Medical Information -->
            <div class="bg-white rounded-lg shadow-lg p-6">
                <h3 class="text-xl font-bold text-gray-800 mb-6 flex items-center">
                    <i class="fas fa-user-md text-green-600 mr-3"></i>
                    Medical Information
                </h3>
                
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Medical Role *</label>
                        <select name="role_id" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 transition-colors" required>
                            <?php if (empty($medical_roles)): ?>
                                <option value="">No Medical Roles Found - Please Sync Permissions</option>
                            <?php else: ?>
                                <option value="">Select Medical Role</option>
                                <?php foreach ($medical_roles as $role): ?>
                                    <option value="<?= $role['id'] ?>" <?= old('role_id', $doctor['role_id'] ?? '') == $role['id'] ? 'selected' : '' ?>>
                                        <?= esc($role['name']) ?>
                                    </option>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </select>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">License Number *</label>
                        <input type="text" name="license_number" value="<?= old('license_number', $doctor['license_number']) ?>" 
                               class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 transition-colors" required>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Specialization *</label>
                        <select name="specialization" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 transition-colors" required>
                            <option value="">Select Specialization</option>
                            <option value="general" <?= old('specialization', $doctor['specialization']) == 'general' ? 'selected' : '' ?>>General Dentistry</option>
                            <option value="orthodontics" <?= old('specialization', $doctor['specialization']) == 'orthodontics' ? 'selected' : '' ?>>Orthodontics</option>
                            <option value="oral_surgery" <?= old('specialization', $doctor['specialization']) == 'oral_surgery' ? 'selected' : '' ?>>Oral Surgery</option>
                            <option value="periodontics" <?= old('specialization', $doctor['specialization']) == 'periodontics' ? 'selected' : '' ?>>Periodontics</option>
                            <option value="pediatrics" <?= old('specialization', $doctor['specialization']) == 'pediatrics' ? 'selected' : '' ?>>Pediatric Dentistry</option>
                            <option value="endodontics" <?= old('specialization', $doctor['specialization']) == 'endodontics' ? 'selected' : '' ?>>Endodontics</option>
                            <option value="prosthodontics" <?= old('specialization', $doctor['specialization']) == 'prosthodontics' ? 'selected' : '' ?>>Prosthodontics</option>
                        </select>
                    </div>
                    
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Years Experience *</label>
                            <input type="number" name="years_experience" value="<?= old('years_experience', $doctor['years_experience']) ?>" min="0" max="50"
                                   class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 transition-colors" required>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Consultation Fee *</label>
                            <input type="number" step="0.01" name="consultation_fee" value="<?= old('consultation_fee', $doctor['consultation_fee']) ?>" min="0"
                                   class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 transition-colors" required>
                        </div>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Department</label>
                        <select name="department" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 transition-colors">
                            <option value="">Select Department</option>
                            <option value="general" <?= old('department', $doctor['department'] ?? '') == 'general' ? 'selected' : '' ?>>General Dentistry</option>
                            <option value="dental" <?= old('department', $doctor['department'] ?? '') == 'dental' ? 'selected' : '' ?>>Dental Clinic</option>
                            <option value="orthodontics" <?= old('department', $doctor['department'] ?? '') == 'orthodontics' ? 'selected' : '' ?>>Orthodontics</option>
                            <option value="oral_surgery" <?= old('department', $doctor['department'] ?? '') == 'oral_surgery' ? 'selected' : '' ?>>Oral Surgery</option>
                            <option value="periodontics" <?= old('department', $doctor['department'] ?? '') == 'periodontics' ? 'selected' : '' ?>>Periodontics</option>
                            <option value="pediatrics" <?= old('department', $doctor['department'] ?? '') == 'pediatrics' ? 'selected' : '' ?>>Pediatrics</option>
                            <option value="endodontics" <?= old('department', $doctor['department'] ?? '') == 'endodontics' ? 'selected' : '' ?>>Endodontics</option>
                            <option value="prosthodontics" <?= old('department', $doctor['department'] ?? '') == 'prosthodontics' ? 'selected' : '' ?>>Prosthodontics</option>
                        </select>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Medical Qualifications</label>
                        <textarea name="medical_qualifications" rows="3" 
                                  class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 transition-colors"
                                  placeholder="e.g., DDS, BDS, MDS, PhD..."><?= old('medical_qualifications', $doctor['medical_qualifications'] ?? '') ?></textarea>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="flex justify-end space-x-4">
            <a href="<?= base_url('doctors/' . $doctor['id']) ?>" class="px-6 py-3 bg-gray-500 text-white rounded-lg hover:bg-gray-600 transition-colors">
                Cancel
            </a>
            <button type="submit" class="px-6 py-3 bg-gradient-to-r from-yellow-500 to-orange-600 text-white rounded-lg hover:from-yellow-600 hover:to-orange-700 transition-all shadow-lg">
                <i class="fas fa-save mr-2"></i>Update Doctor
            </button>
        </div>
    </form>
</div>
<?= $this->endSection() ?>
