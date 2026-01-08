<?= $this->extend('layouts/main_auth') ?>

<?= $this->section('content') ?>
<!-- Enhanced Change Password with Advanced Design -->
<div class="min-h-screen bg-gradient-to-br from-slate-50 via-blue-50 to-indigo-100 relative overflow-hidden">
    <!-- Animated Background Elements -->
    <div class="absolute inset-0 overflow-hidden pointer-events-none">
        <div class="absolute -top-40 -right-40 w-80 h-80 bg-gradient-to-br from-blue-400/20 to-purple-600/20 rounded-full blur-3xl animate-pulse"></div>
        <div class="absolute -bottom-40 -left-40 w-80 h-80 bg-gradient-to-br from-emerald-400/20 to-cyan-600/20 rounded-full blur-3xl animate-pulse delay-1000"></div>
        <div class="absolute top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2 w-96 h-96 bg-gradient-to-br from-amber-400/10 to-orange-600/10 rounded-full blur-3xl animate-pulse delay-2000"></div>
    </div>

    <div class="container mx-auto relative z-10 px-4 py-6">

        <!-- Enhanced Password Change Form with Glassmorphism -->
        <div class="group relative">
            <div class="absolute inset-0 bg-gradient-to-br from-blue-500/10 to-purple-600/10 rounded-3xl blur-xl group-hover:blur-2xl transition-all duration-500 opacity-0 group-hover:opacity-100"></div>
            <div class="relative backdrop-blur-xl bg-white/80 border border-white/30 rounded-2xl shadow-2xl shadow-blue-500/10 group-hover:shadow-blue-500/20 transition-all duration-500 overflow-hidden">
                <!-- Form Header -->
                <div class="p-8 border-b border-white/20">
                    <div class="flex flex-col lg:flex-row lg:justify-between lg:items-center space-y-4 lg:space-y-0">
                        <div class="flex items-center space-x-4">
                            <div class="relative">
                                <div class="absolute inset-0 bg-gradient-to-br from-red-500 to-pink-600 rounded-2xl blur-lg opacity-75 group-hover:opacity-100 transition-opacity duration-300"></div>
                                <div class="relative w-12 h-12 bg-gradient-to-br from-red-500 to-pink-600 rounded-2xl flex items-center justify-center shadow-xl group-hover:scale-110 group-hover:rotate-3 transition-all duration-300">
                                    <i class="fas fa-key text-white text-lg"></i>
                                </div>
                            </div>
                            <div>
                                <h3 class="text-2xl font-black text-gray-900 group-hover:text-red-900 transition-colors duration-300">Change Password</h3>
                                <p class="text-gray-600 font-medium">Update password for <?= ($user->first_name ?? '') . ' ' . ($user->last_name ?? '') ?></p>
                            </div>
                        </div>
                        
                        <div class="flex items-center space-x-3">
                            <a href="<?= base_url('users') ?>" class="group/btn relative inline-flex items-center px-6 py-3 border-2 border-gray-300 text-gray-700 text-sm font-bold rounded-xl hover:bg-gradient-to-r hover:from-gray-50 hover:to-slate-50 hover:border-gray-400 transition-all duration-300 hover:scale-105">
                                <div class="absolute inset-0 bg-gradient-to-r from-gray-500/10 to-slate-500/10 rounded-xl blur opacity-0 group-hover/btn:opacity-100 transition-opacity duration-300"></div>
                                <i class="fas fa-arrow-left mr-2 relative z-10"></i>
                                <span class="relative z-10">Back to Users</span>
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Form Content -->
                <div class="p-8">

                    <form action="<?= base_url('users/' . $user->id . '/update-password') ?>" method="POST" class="space-y-8">
                        <?= csrf_field() ?>
                        
                        <!-- User Information -->
                        <div class="group/section relative">
                            <div class="absolute inset-0 bg-gradient-to-br from-blue-500/5 to-purple-600/5 rounded-2xl blur-lg opacity-0 group-hover/section:opacity-100 transition-opacity duration-500"></div>
                            <div class="relative backdrop-blur-sm bg-white/60 border border-white/40 rounded-2xl p-8 shadow-lg">
                                <div class="flex items-center space-x-3 mb-6">
                                    <div class="w-8 h-8 bg-gradient-to-br from-blue-500 to-purple-600 rounded-lg flex items-center justify-center">
                                        <i class="fas fa-user text-white text-sm"></i>
                                    </div>
                                    <h4 class="text-xl font-bold text-gray-800">User Information</h4>
                                </div>
                                
                                <div class="flex items-center space-x-6">
                                    <div class="w-16 h-16 bg-gradient-to-br from-blue-500 to-blue-600 rounded-full flex items-center justify-center text-white font-bold text-xl">
                                        <?= strtoupper(substr($user->first_name ?? '', 0, 1) . substr($user->last_name ?? '', 0, 1)) ?>
                                    </div>
                                    <div>
                                        <h5 class="text-lg font-bold text-gray-900">
                                            <?= ($user->first_name ?? '') . ' ' . ($user->last_name ?? '') ?>
                                        </h5>
                                        <p class="text-gray-600"><?= $user->email ?></p>
                                        <p class="text-sm text-gray-500">@<?= $user->username ?? $user->email ?></p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Password Change -->
                        <div class="group/section relative">
                            <div class="absolute inset-0 bg-gradient-to-br from-red-500/5 to-pink-600/5 rounded-2xl blur-lg opacity-0 group-hover/section:opacity-100 transition-opacity duration-500"></div>
                            <div class="relative backdrop-blur-sm bg-white/60 border border-white/40 rounded-2xl p-8 shadow-lg">
                                <div class="flex items-center space-x-3 mb-6">
                                    <div class="w-8 h-8 bg-gradient-to-br from-red-500 to-pink-600 rounded-lg flex items-center justify-center">
                                        <i class="fas fa-shield-alt text-white text-sm"></i>
                                    </div>
                                    <h4 class="text-xl font-bold text-gray-800">Password Change</h4>
                                </div>
                                
                                <div class="space-y-8">
                                    <div class="group/field relative">
                                        <label class="block text-sm font-bold text-gray-700 mb-3 flex items-center space-x-2">
                                            <i class="fas fa-lock text-red-600"></i>
                                            <span>Current Password *</span>
                                        </label>
                                        <div class="relative">
                                            <input type="password" id="current_password" name="current_password" 
                                                   class="w-full px-4 py-3 bg-white/80 border-2 border-gray-200 rounded-xl shadow-lg focus:outline-none focus:ring-4 focus:ring-red-500/20 focus:border-red-500 transition-all duration-300 hover:shadow-xl <?= (session()->getFlashdata('validation') && session()->getFlashdata('validation')->hasError('current_password')) ? 'border-red-500 ring-red-500/20' : '' ?>" 
                                                   placeholder="Enter current password" required>
                                            <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
                                                <i class="fas fa-lock text-gray-400"></i>
                                            </div>
                                        </div>
                                        <?php if (session()->getFlashdata('validation') && session()->getFlashdata('validation')->hasError('current_password')): ?>
                                            <p class="text-red-500 text-sm mt-2 flex items-center space-x-1">
                                                <i class="fas fa-exclamation-circle"></i>
                                                <span><?= session()->getFlashdata('validation')->getError('current_password') ?></span>
                                            </p>
                                        <?php endif; ?>
                                    </div>

                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                                        <div class="group/field relative">
                                            <label class="block text-sm font-bold text-gray-700 mb-3 flex items-center space-x-2">
                                                <i class="fas fa-lock text-red-600"></i>
                                                <span>New Password *</span>
                                            </label>
                                            <div class="relative">
                                                <input type="password" id="new_password" name="new_password" 
                                                       class="w-full px-4 py-3 bg-white/80 border-2 border-gray-200 rounded-xl shadow-lg focus:outline-none focus:ring-4 focus:ring-red-500/20 focus:border-red-500 transition-all duration-300 hover:shadow-xl <?= (session()->getFlashdata('validation') && session()->getFlashdata('validation')->hasError('new_password')) ? 'border-red-500 ring-red-500/20' : '' ?>" 
                                                       placeholder="Enter new password" required>
                                                <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
                                                    <i class="fas fa-lock text-gray-400"></i>
                                                </div>
                                            </div>
                                            <?php if (session()->getFlashdata('validation') && session()->getFlashdata('validation')->hasError('new_password')): ?>
                                                <p class="text-red-500 text-sm mt-2 flex items-center space-x-1">
                                                    <i class="fas fa-exclamation-circle"></i>
                                                    <span><?= session()->getFlashdata('validation')->getError('new_password') ?></span>
                                                </p>
                                            <?php endif; ?>
                                        </div>

                                        <div class="group/field relative">
                                            <label class="block text-sm font-bold text-gray-700 mb-3 flex items-center space-x-2">
                                                <i class="fas fa-lock text-red-600"></i>
                                                <span>Confirm New Password *</span>
                                            </label>
                                            <div class="relative">
                                                <input type="password" id="confirm_password" name="confirm_password" 
                                                       class="w-full px-4 py-3 bg-white/80 border-2 border-gray-200 rounded-xl shadow-lg focus:outline-none focus:ring-4 focus:ring-red-500/20 focus:border-red-500 transition-all duration-300 hover:shadow-xl <?= (session()->getFlashdata('validation') && session()->getFlashdata('validation')->hasError('confirm_password')) ? 'border-red-500 ring-red-500/20' : '' ?>" 
                                                       placeholder="Confirm new password" required>
                                                <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
                                                    <i class="fas fa-lock text-gray-400"></i>
                                                </div>
                                            </div>
                                            <?php if (session()->getFlashdata('validation') && session()->getFlashdata('validation')->hasError('confirm_password')): ?>
                                                <p class="text-red-500 text-sm mt-2 flex items-center space-x-1">
                                                    <i class="fas fa-exclamation-circle"></i>
                                                    <span><?= session()->getFlashdata('validation')->getError('confirm_password') ?></span>
                                                </p>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Password Requirements -->
                        <div class="group/section relative">
                            <div class="absolute inset-0 bg-gradient-to-br from-green-500/5 to-teal-600/5 rounded-2xl blur-lg opacity-0 group-hover/section:opacity-100 transition-opacity duration-500"></div>
                            <div class="relative backdrop-blur-sm bg-white/60 border border-white/40 rounded-2xl p-8 shadow-lg">
                                <div class="flex items-center space-x-3 mb-6">
                                    <div class="w-8 h-8 bg-gradient-to-br from-green-500 to-teal-600 rounded-lg flex items-center justify-center">
                                        <i class="fas fa-info-circle text-white text-sm"></i>
                                    </div>
                                    <h4 class="text-xl font-bold text-gray-800">Password Requirements</h4>
                                </div>
                                
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <div class="space-y-3">
                                        <div class="flex items-center space-x-3">
                                            <i class="fas fa-check-circle text-green-500"></i>
                                            <span class="text-sm text-gray-700">At least 8 characters long</span>
                                        </div>
                                        <div class="flex items-center space-x-3">
                                            <i class="fas fa-check-circle text-green-500"></i>
                                            <span class="text-sm text-gray-700">Contains uppercase letter</span>
                                        </div>
                                        <div class="flex items-center space-x-3">
                                            <i class="fas fa-check-circle text-green-500"></i>
                                            <span class="text-sm text-gray-700">Contains lowercase letter</span>
                                        </div>
                                    </div>
                                    <div class="space-y-3">
                                        <div class="flex items-center space-x-3">
                                            <i class="fas fa-check-circle text-green-500"></i>
                                            <span class="text-sm text-gray-700">Contains number</span>
                                        </div>
                                        <div class="flex items-center space-x-3">
                                            <i class="fas fa-check-circle text-green-500"></i>
                                            <span class="text-sm text-gray-700">Contains special character</span>
                                        </div>
                                        <div class="flex items-center space-x-3">
                                            <i class="fas fa-check-circle text-green-500"></i>
                                            <span class="text-sm text-gray-700">Different from current password</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Form Actions -->
                        <div class="flex flex-col sm:flex-row justify-end gap-4 sm:gap-12 pt-6 border-t border-white/20">
                            <a href="<?= base_url('users') ?>" class="group/btn relative inline-flex items-center justify-center px-8 py-3 border-2 border-gray-300 text-gray-700 text-sm font-bold rounded-xl hover:bg-gradient-to-r hover:from-gray-50 hover:to-slate-50 hover:border-gray-400 transition-all duration-300 hover:scale-105">
                                <div class="absolute inset-0 bg-gradient-to-r from-gray-500/10 to-slate-500/10 rounded-xl blur opacity-0 group-hover/btn:opacity-100 transition-opacity duration-300"></div>
                                <i class="fas fa-times mr-2 relative z-10"></i>
                                <span class="relative z-10">Cancel</span>
                            </a>
                            <button type="submit" class="group/btn relative inline-flex items-center justify-center px-8 py-3 bg-gradient-to-r from-red-500 to-pink-600 text-white text-sm font-bold rounded-xl hover:from-red-600 hover:to-pink-700 transition-all duration-300 hover:scale-105 hover:shadow-2xl hover:shadow-red-500/25">
                                <div class="absolute inset-0 bg-gradient-to-r from-red-500/20 to-pink-600/20 rounded-xl blur opacity-0 group-hover/btn:opacity-100 transition-opacity duration-300"></div>
                                <i class="fas fa-key mr-2 relative z-10"></i>
                                <span class="relative z-10">Change Password</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Form validation
document.addEventListener('DOMContentLoaded', function() {
    const form = document.querySelector('form');
    const newPassword = document.getElementById('new_password');
    const confirmPassword = document.getElementById('confirm_password');

    // Password confirmation validation
    function validatePassword() {
        if (newPassword.value && confirmPassword.value) {
            if (newPassword.value !== confirmPassword.value) {
                confirmPassword.setCustomValidity('Passwords do not match');
            } else {
                confirmPassword.setCustomValidity('');
            }
        }
    }

    newPassword.addEventListener('input', validatePassword);
    confirmPassword.addEventListener('input', validatePassword);

    // Form submission
    form.addEventListener('submit', function(e) {
        if (newPassword.value !== confirmPassword.value) {
            e.preventDefault();
            alert('New passwords do not match');
            confirmPassword.focus();
            return false;
        }
        
        if (newPassword.value.length < 8) {
            e.preventDefault();
            alert('New password must be at least 8 characters long');
            newPassword.focus();
            return false;
        }
    });
});
</script>
<?= $this->endSection() ?>
