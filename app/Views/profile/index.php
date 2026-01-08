<?= $this->extend('layouts/main_auth') ?>

<?= $this->section('content') ?>
<div class="min-h-screen bg-gradient-to-br from-slate-50 via-blue-50 to-indigo-100">
    <div class="px-6 py-8">
        <!-- Enhanced Profile Header -->
        <div class="mb-10">
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-4">
                    <div class="relative group">
                        <div class="absolute inset-0 bg-gradient-to-br from-blue-500 to-purple-600 rounded-2xl blur-lg opacity-75 group-hover:opacity-100 transition-opacity duration-200"></div>
                        <div class="relative w-16 h-16 bg-gradient-to-br from-blue-500 to-purple-600 rounded-2xl flex items-center justify-center shadow-xl group-hover:scale-105 group-hover:rotate-2 transition-all duration-200">
                            <i class="fas fa-user text-white text-2xl"></i>
                        </div>
                    </div>
                    <div>
                        <h1 class="text-3xl font-black text-gray-900 mb-2">Profile Management</h1>
                        <p class="text-gray-600 font-medium">Manage your account information and permissions</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Profile Content -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Profile Information Card -->
            <div class="lg:col-span-2">
                <div class="backdrop-blur-xl bg-white/80 rounded-3xl shadow-2xl shadow-blue-500/10 border border-white/30 p-8">
                    <div class="flex items-center justify-between mb-8">
                        <h2 class="text-2xl font-black text-gray-900 flex items-center">
                            <i class="fas fa-user-edit w-6 h-6 mr-3 text-blue-600"></i>
                            Personal Information
                        </h2>
                    </div>


                    <!-- Profile Form -->
                    <form action="<?= base_url('profile/update') ?>" method="POST" class="space-y-6">
                        <?= csrf_field() ?>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div class="group">
                                <label class="block text-sm font-bold text-gray-700 mb-3 flex items-center">
                                    <i class="fas fa-user w-4 h-4 mr-2 text-blue-600"></i>
                                    First Name
                                </label>
                                <input type="text" name="first_name" value="<?= old('first_name', $user->first_name ?? '') ?>" 
                                       class="w-full px-4 py-4 bg-gray-50/50 border-2 border-gray-200 rounded-xl shadow-sm focus:outline-none focus:ring-4 focus:ring-blue-500/20 focus:border-blue-500 transition-all duration-300 hover:border-gray-300 group-hover:shadow-lg" 
                                       required>
                                <?php if (session('validation') && session('validation')->hasError('first_name')): ?>
                                    <p class="text-red-600 text-sm mt-2"><?= session('validation')->getError('first_name') ?></p>
                                <?php endif; ?>
                            </div>

                            <div class="group">
                                <label class="block text-sm font-bold text-gray-700 mb-3 flex items-center">
                                    <i class="fas fa-user w-4 h-4 mr-2 text-blue-600"></i>
                                    Last Name
                                </label>
                                <input type="text" name="last_name" value="<?= old('last_name', $user->last_name ?? '') ?>" 
                                       class="w-full px-4 py-4 bg-gray-50/50 border-2 border-gray-200 rounded-xl shadow-sm focus:outline-none focus:ring-4 focus:ring-blue-500/20 focus:border-blue-500 transition-all duration-300 hover:border-gray-300 group-hover:shadow-lg" 
                                       required>
                                <?php if (session('validation') && session('validation')->hasError('last_name')): ?>
                                    <p class="text-red-600 text-sm mt-2"><?= session('validation')->getError('last_name') ?></p>
                                <?php endif; ?>
                            </div>
                        </div>

                        <div class="group">
                            <label class="block text-sm font-bold text-gray-700 mb-3 flex items-center">
                                <i class="fas fa-envelope w-4 h-4 mr-2 text-green-600"></i>
                                Email Address
                            </label>
                            <input type="email" name="email" value="<?= old('email', $user->email ?? '') ?>" 
                                   class="w-full px-4 py-4 bg-gray-50/50 border-2 border-gray-200 rounded-xl shadow-sm focus:outline-none focus:ring-4 focus:ring-green-500/20 focus:border-green-500 transition-all duration-300 hover:border-gray-300 group-hover:shadow-lg" 
                                   required>
                            <?php if (session('validation') && session('validation')->hasError('email')): ?>
                                <p class="text-red-600 text-sm mt-2"><?= session('validation')->getError('email') ?></p>
                            <?php endif; ?>
                        </div>

                        <div class="group">
                            <label class="block text-sm font-bold text-gray-700 mb-3 flex items-center">
                                <i class="fas fa-phone w-4 h-4 mr-2 text-purple-600"></i>
                                Phone Number
                            </label>
                            <input type="tel" name="phone" value="<?= old('phone', $user->phone ?? '') ?>" 
                                   class="w-full px-4 py-4 bg-gray-50/50 border-2 border-gray-200 rounded-xl shadow-sm focus:outline-none focus:ring-4 focus:ring-purple-500/20 focus:border-purple-500 transition-all duration-300 hover:border-gray-300 group-hover:shadow-lg">
                            <?php if (session('validation') && session('validation')->hasError('phone')): ?>
                                <p class="text-red-600 text-sm mt-2"><?= session('validation')->getError('phone') ?></p>
                            <?php endif; ?>
                        </div>

                        <div class="flex justify-end pt-6">
                            <button type="submit" class="group relative px-8 py-4 bg-gradient-to-r from-blue-600 to-purple-600 text-white font-bold rounded-xl shadow-lg hover:shadow-xl hover:shadow-blue-500/25 transition-all duration-300 hover:scale-105">
                                <div class="absolute inset-0 bg-gradient-to-r from-blue-600 to-purple-600 rounded-xl blur opacity-75 group-hover:opacity-100 transition-opacity duration-200"></div>
                                <span class="relative z-10 flex items-center">
                                    <i class="fas fa-save mr-2"></i>
                                    Update Profile
                                </span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Sidebar -->
            <div class="space-y-8">
                <!-- User Permissions Card -->
                <div class="backdrop-blur-xl bg-white/80 rounded-3xl shadow-2xl shadow-blue-500/10 border border-white/30 p-6">
                    <h3 class="text-xl font-black text-gray-900 mb-6 flex items-center">
                        <i class="fas fa-shield-alt w-5 h-5 mr-3 text-green-600"></i>
                        User Permissions
                    </h3>
                    
                    <div class="space-y-4">
                        <?php if (!empty($user_groups)): ?>
                            <?php foreach ($user_groups as $group): ?>
                                <div class="flex items-center justify-between p-3 bg-gradient-to-r from-green-50 to-emerald-50 rounded-xl border border-green-200">
                                    <div class="flex items-center">
                                        <i class="fas fa-user-tag text-green-600 mr-3"></i>
                                        <span class="font-semibold text-gray-800"><?= esc(ucfirst($group->name)) ?></span>
                                    </div>
                                    <div class="w-2 h-2 bg-green-500 rounded-full"></div>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="text-center py-4">
                                <i class="fas fa-info-circle text-gray-400 text-2xl mb-2"></i>
                                <p class="text-gray-500">No specific permissions assigned</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Change Password Card -->
                <div class="backdrop-blur-xl bg-white/80 rounded-3xl shadow-2xl shadow-blue-500/10 border border-white/30 p-6">
                    <h3 class="text-xl font-black text-gray-900 mb-6 flex items-center">
                        <i class="fas fa-key w-5 h-5 mr-3 text-orange-600"></i>
                        Change Password
                    </h3>
                    
                    <form action="<?= base_url('profile/change-password') ?>" method="POST" class="space-y-4">
                        <?= csrf_field() ?>
                        
                        <div class="group">
                            <label class="block text-sm font-bold text-gray-700 mb-2">Current Password</label>
                            <input type="password" name="current_password" 
                                   class="w-full px-3 py-3 bg-gray-50/50 border-2 border-gray-200 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-orange-500/20 focus:border-orange-500 transition-all duration-300" 
                                   required>
                        </div>

                        <div class="group">
                            <label class="block text-sm font-bold text-gray-700 mb-2">New Password</label>
                            <input type="password" name="new_password" 
                                   class="w-full px-3 py-3 bg-gray-50/50 border-2 border-gray-200 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-orange-500/20 focus:border-orange-500 transition-all duration-300" 
                                   required>
                        </div>

                        <div class="group">
                            <label class="block text-sm font-bold text-gray-700 mb-2">Confirm Password</label>
                            <input type="password" name="confirm_password" 
                                   class="w-full px-3 py-3 bg-gray-50/50 border-2 border-gray-200 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-orange-500/20 focus:border-orange-500 transition-all duration-300" 
                                   required>
                        </div>

                        <button type="submit" class="w-full group relative px-4 py-3 bg-gradient-to-r from-orange-500 to-red-500 text-white font-bold rounded-lg shadow-lg hover:shadow-xl transition-all duration-300 hover:scale-105">
                            <div class="absolute inset-0 bg-gradient-to-r from-orange-500 to-red-500 rounded-lg blur opacity-75 group-hover:opacity-100 transition-opacity duration-200"></div>
                            <span class="relative z-10 flex items-center justify-center">
                                <i class="fas fa-key mr-2"></i>
                                Change Password
                            </span>
                        </button>
                    </form>
                </div>

                <!-- Account Info Card -->
                <div class="backdrop-blur-xl bg-white/80 rounded-3xl shadow-2xl shadow-blue-500/10 border border-white/30 p-6">
                    <h3 class="text-xl font-black text-gray-900 mb-6 flex items-center">
                        <i class="fas fa-info-circle w-5 h-5 mr-3 text-blue-600"></i>
                        Account Information
                    </h3>
                    
                    <div class="space-y-4">
                        <div class="flex items-center justify-between py-2">
                            <span class="text-gray-600 font-medium">User ID:</span>
                            <span class="text-gray-900 font-semibold">#<?= $user->id ?></span>
                        </div>
                        
                        <div class="flex items-center justify-between py-2">
                            <span class="text-gray-600 font-medium">Status:</span>
                            <span class="px-2 py-1 bg-green-100 text-green-800 text-xs font-semibold rounded-full">
                                <?= $user->active ? 'Active' : 'Inactive' ?>
                            </span>
                        </div>
                        
                        <div class="flex items-center justify-between py-2">
                            <span class="text-gray-600 font-medium">Last Login:</span>
                            <span class="text-gray-900 font-semibold">
                                <?= $user->last_login ? date('M j, Y g:i A', $user->last_login) : 'Never' ?>
                            </span>
                        </div>
                        
                        <div class="flex items-center justify-between py-2">
                            <span class="text-gray-600 font-medium">Created:</span>
                            <span class="text-gray-900 font-semibold">
                                <?= date('M j, Y', strtotime($user->created_on)) ?>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection() ?>
