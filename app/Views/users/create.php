<?= $this->extend('layouts/main_auth') ?>

<?= $this->section('content') ?>
<!-- Enhanced User Creation with Advanced Design -->
<div class="min-h-screen bg-gradient-to-br from-slate-50 via-blue-50 to-indigo-100 relative overflow-hidden">
    <!-- Animated Background Elements -->
    <div class="absolute inset-0 overflow-hidden pointer-events-none">
        <div class="absolute -top-40 -right-40 w-80 h-80 bg-gradient-to-br from-blue-400/20 to-purple-600/20 rounded-full blur-3xl animate-pulse"></div>
        <div class="absolute -bottom-40 -left-40 w-80 h-80 bg-gradient-to-br from-emerald-400/20 to-cyan-600/20 rounded-full blur-3xl animate-pulse delay-1000"></div>
        <div class="absolute top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2 w-96 h-96 bg-gradient-to-br from-amber-400/10 to-orange-600/10 rounded-full blur-3xl animate-pulse delay-2000"></div>
    </div>

    <div class="container mx-auto relative z-10 px-4 py-6">

        <!-- Enhanced User Form with Glassmorphism -->
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
                                    <i class="fas fa-user-plus text-white text-lg"></i>
                                </div>
                            </div>
                            <div>
                                <h3 class="text-2xl font-black text-gray-900 group-hover:text-blue-900 transition-colors duration-300">Create New User</h3>
                                <p class="text-gray-600 font-medium">Add a new user to the system with appropriate access permissions</p>
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

                    <form action="<?= base_url('users/store') ?>" method="POST" class="space-y-8">
                        <!-- Personal Information -->
                        <div class="group/section relative">
                            <div class="absolute inset-0 bg-gradient-to-br from-blue-500/5 to-purple-600/5 rounded-2xl blur-lg opacity-0 group-hover/section:opacity-100 transition-opacity duration-500"></div>
                            <div class="relative backdrop-blur-sm bg-white/60 border border-white/40 rounded-2xl p-8 shadow-lg">
                                <div class="flex items-center space-x-3 mb-6">
                                    <div class="w-8 h-8 bg-gradient-to-br from-blue-500 to-purple-600 rounded-lg flex items-center justify-center">
                                        <i class="fas fa-user text-white text-sm"></i>
                                    </div>
                                    <h4 class="text-xl font-bold text-gray-800">Personal Information</h4>
                                </div>
                                
                                <div class="space-y-8">
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                                        <div class="group/field relative">
                                            <label class="block text-sm font-bold text-gray-700 mb-3 flex items-center space-x-2">
                                                <i class="fas fa-user text-blue-600"></i>
                                                <span>First Name *</span>
                                            </label>
                                            <div class="relative">
                                                <input type="text" id="first_name" name="first_name" 
                                                       class="w-full px-4 py-3 bg-white/80 border-2 border-gray-200 rounded-xl shadow-lg focus:outline-none focus:ring-4 focus:ring-blue-500/20 focus:border-blue-500 transition-all duration-300 hover:shadow-xl <?= (session()->getFlashdata('validation') && session()->getFlashdata('validation')->hasError('first_name')) ? 'border-red-500 ring-red-500/20' : '' ?>"
                                                       value="<?= old('first_name') ?>" placeholder="Enter first name" required>
                                                <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
                                                    <i class="fas fa-user text-gray-400"></i>
                                                </div>
                                            </div>
                                            <?php if (session()->getFlashdata('validation') && session()->getFlashdata('validation')->hasError('first_name')): ?>
                                                <p class="text-red-500 text-sm mt-2 flex items-center space-x-1">
                                                    <i class="fas fa-exclamation-circle"></i>
                                                    <span><?= session()->getFlashdata('validation')->getError('first_name') ?></span>
                                                </p>
                                            <?php endif; ?>
                                        </div>

                                        <div class="group/field relative">
                                            <label class="block text-sm font-bold text-gray-700 mb-3 flex items-center space-x-2">
                                                <i class="fas fa-user text-blue-600"></i>
                                                <span>Last Name *</span>
                                            </label>
                                            <div class="relative">
                                                <input type="text" id="last_name" name="last_name" 
                                                       class="w-full px-4 py-3 bg-white/80 border-2 border-gray-200 rounded-xl shadow-lg focus:outline-none focus:ring-4 focus:ring-blue-500/20 focus:border-blue-500 transition-all duration-300 hover:shadow-xl <?= (session()->getFlashdata('validation') && session()->getFlashdata('validation')->hasError('last_name')) ? 'border-red-500 ring-red-500/20' : '' ?>"
                                                       value="<?= old('last_name') ?>" placeholder="Enter last name" required>
                                                <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
                                                    <i class="fas fa-user text-gray-400"></i>
                                                </div>
                                            </div>
                                            <?php if (session()->getFlashdata('validation') && session()->getFlashdata('validation')->hasError('last_name')): ?>
                                                <p class="text-red-500 text-sm mt-2 flex items-center space-x-1">
                                                    <i class="fas fa-exclamation-circle"></i>
                                                    <span><?= session()->getFlashdata('validation')->getError('last_name') ?></span>
                                                </p>
                                            <?php endif; ?>
                                        </div>
                                    </div>

                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                                        <div class="group/field relative">
                                            <label class="block text-sm font-bold text-gray-700 mb-3 flex items-center space-x-2">
                                                <i class="fas fa-envelope text-purple-600"></i>
                                                <span>Email *</span>
                                            </label>
                                            <div class="relative">
                                                <input type="email" id="email" name="email" 
                                                       class="w-full px-4 py-3 bg-white/80 border-2 border-gray-200 rounded-xl shadow-lg focus:outline-none focus:ring-4 focus:ring-purple-500/20 focus:border-purple-500 transition-all duration-300 hover:shadow-xl <?= (session()->getFlashdata('validation') && session()->getFlashdata('validation')->hasError('email')) ? 'border-red-500 ring-red-500/20' : '' ?>"
                                                       value="<?= old('email') ?>" placeholder="Enter email address" required>
                                                <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
                                                    <i class="fas fa-envelope text-gray-400"></i>
                                                </div>
                                            </div>
                                            <?php if (session()->getFlashdata('validation') && session()->getFlashdata('validation')->hasError('email')): ?>
                                                <p class="text-red-500 text-sm mt-2 flex items-center space-x-1">
                                                    <i class="fas fa-exclamation-circle"></i>
                                                    <span><?= session()->getFlashdata('validation')->getError('email') ?></span>
                                                </p>
                                            <?php endif; ?>
                                        </div>

                                        <div class="group/field relative">
                                            <label class="block text-sm font-bold text-gray-700 mb-3 flex items-center space-x-2">
                                                <i class="fas fa-at text-indigo-600"></i>
                                                <span>Username *</span>
                                            </label>
                                            <div class="relative">
                                                <input type="text" id="username" name="username" 
                                                       class="w-full px-4 py-3 bg-white/80 border-2 border-gray-200 rounded-xl shadow-lg focus:outline-none focus:ring-4 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all duration-300 hover:shadow-xl <?= (session()->getFlashdata('validation') && session()->getFlashdata('validation')->hasError('username')) ? 'border-red-500 ring-red-500/20' : '' ?>"
                                                       value="<?= old('username') ?>" placeholder="Enter username" required>
                                                <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
                                                    <i class="fas fa-at text-gray-400"></i>
                                                </div>
                                            </div>
                                            <?php if (session()->getFlashdata('validation') && session()->getFlashdata('validation')->hasError('username')): ?>
                                                <p class="text-red-500 text-sm mt-2 flex items-center space-x-1">
                                                    <i class="fas fa-exclamation-circle"></i>
                                                    <span><?= session()->getFlashdata('validation')->getError('username') ?></span>
                                                </p>
                                            <?php endif; ?>
                                        </div>
                                    </div>

                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                                        <div class="group/field relative">
                                            <label class="block text-sm font-bold text-gray-700 mb-3 flex items-center space-x-2">
                                                <i class="fas fa-phone text-emerald-600"></i>
                                                <span>Phone</span>
                                            </label>
                                            <div class="relative">
                                                <input type="tel" id="phone" name="phone" 
                                                       class="w-full px-4 py-3 bg-white/80 border-2 border-gray-200 rounded-xl shadow-lg focus:outline-none focus:ring-4 focus:ring-emerald-500/20 focus:border-emerald-500 transition-all duration-300 hover:shadow-xl <?= (session()->getFlashdata('validation') && session()->getFlashdata('validation')->hasError('phone')) ? 'border-red-500 ring-red-500/20' : '' ?>"
                                                       value="<?= old('phone') ?>" placeholder="Enter phone number">
                                                <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
                                                    <i class="fas fa-phone text-gray-400"></i>
                                                </div>
                                            </div>
                                            <?php if (session()->getFlashdata('validation') && session()->getFlashdata('validation')->hasError('phone')): ?>
                                                <p class="text-red-500 text-sm mt-2 flex items-center space-x-1">
                                                    <i class="fas fa-exclamation-circle"></i>
                                                    <span><?= session()->getFlashdata('validation')->getError('phone') ?></span>
                                                </p>
                                            <?php endif; ?>
                                        </div>

                                        <div class="group/field relative">
                                            <label class="block text-sm font-bold text-gray-700 mb-3 flex items-center space-x-2">
                                                <i class="fas fa-calendar text-amber-600"></i>
                                                <span>Hire Date *</span>
                                            </label>
                                            <div class="relative">
                                                <input type="date" id="hire_date" name="hire_date" 
                                                       class="w-full px-4 py-3 bg-white/80 border-2 border-gray-200 rounded-xl shadow-lg focus:outline-none focus:ring-4 focus:ring-amber-500/20 focus:border-amber-500 transition-all duration-300 hover:shadow-xl <?= (session()->getFlashdata('validation') && session()->getFlashdata('validation')->hasError('hire_date')) ? 'border-red-500 ring-red-500/20' : '' ?>"
                                                       value="<?= old('hire_date', date('Y-m-d')) ?>" required>
                                                <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
                                                    <i class="fas fa-calendar text-gray-400"></i>
                                                </div>
                                            </div>
                                            <?php if (session()->getFlashdata('validation') && session()->getFlashdata('validation')->hasError('hire_date')): ?>
                                                <p class="text-red-500 text-sm mt-2 flex items-center space-x-1">
                                                    <i class="fas fa-exclamation-circle"></i>
                                                    <span><?= session()->getFlashdata('validation')->getError('hire_date') ?></span>
                                                </p>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
            </div>
        </div>

                        <!-- Account Security -->
                        <div class="group/section relative">
                            <div class="absolute inset-0 bg-gradient-to-br from-red-500/5 to-pink-600/5 rounded-2xl blur-lg opacity-0 group-hover/section:opacity-100 transition-opacity duration-500"></div>
                            <div class="relative backdrop-blur-sm bg-white/60 border border-white/40 rounded-2xl p-8 shadow-lg">
                                <div class="flex items-center space-x-3 mb-6">
                                    <div class="w-8 h-8 bg-gradient-to-br from-red-500 to-pink-600 rounded-lg flex items-center justify-center">
                                        <i class="fas fa-shield-alt text-white text-sm"></i>
                                    </div>
                                    <h4 class="text-xl font-bold text-gray-800">Account Security</h4>
                                </div>
                                
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                                    <div class="group/field relative">
                                        <label class="block text-sm font-bold text-gray-700 mb-3 flex items-center space-x-2">
                                            <i class="fas fa-lock text-red-600"></i>
                                            <span>Password *</span>
                                        </label>
                                        <div class="relative">
                                            <input type="password" id="password" name="password" 
                                                   class="w-full px-4 py-3 bg-white/80 border-2 border-gray-200 rounded-xl shadow-lg focus:outline-none focus:ring-4 focus:ring-red-500/20 focus:border-red-500 transition-all duration-300 hover:shadow-xl <?= (session()->getFlashdata('validation') && session()->getFlashdata('validation')->hasError('password')) ? 'border-red-500 ring-red-500/20' : '' ?>" 
                                                   placeholder="Enter password" required>
                                            <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
                                                <i class="fas fa-lock text-gray-400"></i>
                                            </div>
                                        </div>
                                        <?php if (session()->getFlashdata('validation') && session()->getFlashdata('validation')->hasError('password')): ?>
                                            <p class="text-red-500 text-sm mt-2 flex items-center space-x-1">
                                                <i class="fas fa-exclamation-circle"></i>
                                                <span><?= session()->getFlashdata('validation')->getError('password') ?></span>
                                            </p>
                                        <?php endif; ?>
                                    </div>

                                    <div class="group/field relative">
                                        <label class="block text-sm font-bold text-gray-700 mb-3 flex items-center space-x-2">
                                            <i class="fas fa-lock text-red-600"></i>
                                            <span>Confirm Password *</span>
                                        </label>
                                        <div class="relative">
                                            <input type="password" id="password_confirm" name="password_confirm" 
                                                   class="w-full px-4 py-3 bg-white/80 border-2 border-gray-200 rounded-xl shadow-lg focus:outline-none focus:ring-4 focus:ring-red-500/20 focus:border-red-500 transition-all duration-300 hover:shadow-xl <?= (session()->getFlashdata('validation') && session()->getFlashdata('validation')->hasError('password_confirm')) ? 'border-red-500 ring-red-500/20' : '' ?>" 
                                                   placeholder="Confirm password" required>
                                            <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
                                                <i class="fas fa-lock text-gray-400"></i>
                                            </div>
                                        </div>
                                        <?php if (session()->getFlashdata('validation') && session()->getFlashdata('validation')->hasError('password_confirm')): ?>
                                            <p class="text-red-500 text-sm mt-2 flex items-center space-x-1">
                                                <i class="fas fa-exclamation-circle"></i>
                                                <span><?= session()->getFlashdata('validation')->getError('password_confirm') ?></span>
                                            </p>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Role & Permissions -->
                        <div class="group/section relative">
                            <div class="absolute inset-0 bg-gradient-to-br from-green-500/5 to-teal-600/5 rounded-2xl blur-lg opacity-0 group-hover/section:opacity-100 transition-opacity duration-500"></div>
                            <div class="relative backdrop-blur-sm bg-white/60 border border-white/40 rounded-2xl p-8 shadow-lg">
                                <div class="flex items-center space-x-3 mb-6">
                                    <div class="w-8 h-8 bg-gradient-to-br from-green-500 to-teal-600 rounded-lg flex items-center justify-center">
                                        <i class="fas fa-user-shield text-white text-sm"></i>
                                    </div>
                                    <h4 class="text-xl font-bold text-gray-800">Role & Permissions</h4>
                                </div>
                                
                                <div class="space-y-8">
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                                        <div class="group/field relative">
                                            <label class="block text-sm font-bold text-gray-700 mb-3 flex items-center space-x-2">
                                                <i class="fas fa-user-tag text-green-600"></i>
                                                <span>Role *</span>
                                            </label>
                                            <div class="relative">
                                                <select id="role" name="role" 
                                                        class="w-full px-4 py-3 bg-white/80 border-2 border-gray-200 rounded-xl shadow-lg focus:outline-none focus:ring-4 focus:ring-green-500/20 focus:border-green-500 transition-all duration-300 hover:shadow-xl <?= (session()->getFlashdata('validation') && session()->getFlashdata('validation')->hasError('role')) ? 'border-red-500 ring-red-500/20' : '' ?>" 
                                                        data-searchable-select 
                                                        data-search-url="<?= base_url('api/search/roles') ?>"
                                                        data-placeholder="Search roles..."
                                                        required>
                                                    <option value="">Select role</option>
                                                    <?php foreach ($roles as $key => $role): ?>
                                                        <option value="<?= $key ?>" <?= (old('role') == $key) ? 'selected' : '' ?>>
                                                            <?= $role ?>
                                                        </option>
                                                    <?php endforeach; ?>
                                                </select>
                                                <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
                                                    <i class="fas fa-chevron-down text-gray-400"></i>
                                                </div>
                                            </div>
                                            <?php if (session()->getFlashdata('validation') && session()->getFlashdata('validation')->hasError('role')): ?>
                                                <p class="text-red-500 text-sm mt-2 flex items-center space-x-1">
                                                    <i class="fas fa-exclamation-circle"></i>
                                                    <span><?= session()->getFlashdata('validation')->getError('role') ?></span>
                                                </p>
                                            <?php endif; ?>
                                        </div>

                                        <div class="group/field relative">
                                            <label class="block text-sm font-bold text-gray-700 mb-3 flex items-center space-x-2">
                                                <i class="fas fa-building text-green-600"></i>
                                                <span>Department</span>
                                            </label>
                                            <div class="relative">
                                                <select id="department" name="department" 
                                                        class="w-full px-4 py-3 bg-white/80 border-2 border-gray-200 rounded-xl shadow-lg focus:outline-none focus:ring-4 focus:ring-green-500/20 focus:border-green-500 transition-all duration-300 hover:shadow-xl"
                                                        data-searchable-select 
                                                        data-search-url="<?= base_url('api/search/departments') ?>"
                                                        data-placeholder="Search departments...">
                                                    <option value="">Select department</option>
                                                    <?php foreach ($departments as $key => $department): ?>
                                                        <option value="<?= $key ?>" <?= (old('department') == $key) ? 'selected' : '' ?>>
                                                            <?= $department ?>
                                                        </option>
                                                    <?php endforeach; ?>
                                                </select>
                                                <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
                                                    <i class="fas fa-chevron-down text-gray-400"></i>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="group/field relative">
                                        <label class="block text-sm font-bold text-gray-700 mb-3 flex items-center space-x-2">
                                            <i class="fas fa-toggle-on text-green-600"></i>
                                            <span>Status *</span>
                                        </label>
                                        <div class="relative">
                                            <select id="status" name="status" 
                                                    class="w-full px-4 py-3 bg-white/80 border-2 border-gray-200 rounded-xl shadow-lg focus:outline-none focus:ring-4 focus:ring-green-500/20 focus:border-green-500 transition-all duration-300 hover:shadow-xl <?= (session()->getFlashdata('validation') && session()->getFlashdata('validation')->hasError('status')) ? 'border-red-500 ring-red-500/20' : '' ?>" required>
                                                <option value="active" <?= (old('status') == 'active') ? 'selected' : '' ?>>Active</option>
                                                <option value="inactive" <?= (old('status') == 'inactive') ? 'selected' : '' ?>>Inactive</option>
                                                <option value="suspended" <?= (old('status') == 'suspended') ? 'selected' : '' ?>>Suspended</option>
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
                                </div>
                            </div>
                        </div>

                        <!-- Address Information -->
                        <div class="group/section relative">
                            <div class="absolute inset-0 bg-gradient-to-br from-orange-500/5 to-amber-600/5 rounded-2xl blur-lg opacity-0 group-hover/section:opacity-100 transition-opacity duration-500"></div>
                            <div class="relative backdrop-blur-sm bg-white/60 border border-white/40 rounded-2xl p-8 shadow-lg">
                                <div class="flex items-center space-x-3 mb-6">
                                    <div class="w-8 h-8 bg-gradient-to-br from-orange-500 to-amber-600 rounded-lg flex items-center justify-center">
                                        <i class="fas fa-map-marker-alt text-white text-sm"></i>
                                    </div>
                                    <h4 class="text-xl font-bold text-gray-800">Address Information</h4>
                                </div>
                                
                                <div class="group/field relative">
                                    <label class="block text-sm font-bold text-gray-700 mb-3 flex items-center space-x-2">
                                        <i class="fas fa-map-marker-alt text-orange-600"></i>
                                        <span>Address</span>
                                    </label>
                                    <div class="relative">
                                        <textarea id="address" name="address" rows="3" 
                                                  class="w-full px-4 py-3 bg-white/80 border-2 border-gray-200 rounded-xl shadow-lg focus:outline-none focus:ring-4 focus:ring-orange-500/20 focus:border-orange-500 transition-all duration-300 hover:shadow-xl resize-none <?= (session()->getFlashdata('validation') && session()->getFlashdata('validation')->hasError('address')) ? 'border-red-500 ring-red-500/20' : '' ?>"
                                                  placeholder="User's address..."><?= old('address') ?></textarea>
                                        <div class="absolute bottom-3 right-3 text-gray-400 text-xs">
                                            <i class="fas fa-edit"></i>
                                        </div>
                                    </div>
                                    <?php if (session()->getFlashdata('validation') && session()->getFlashdata('validation')->hasError('address')): ?>
                                        <p class="text-red-500 text-sm mt-2 flex items-center space-x-1">
                                            <i class="fas fa-exclamation-circle"></i>
                                            <span><?= session()->getFlashdata('validation')->getError('address') ?></span>
                                        </p>
                                    <?php endif; ?>
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
                            <button type="submit" class="group/btn relative inline-flex items-center justify-center px-8 py-3 bg-gradient-to-r from-blue-500 to-purple-600 text-white text-sm font-bold rounded-xl hover:from-blue-600 hover:to-purple-700 transition-all duration-300 hover:scale-105 hover:shadow-2xl hover:shadow-blue-500/25">
                                <div class="absolute inset-0 bg-gradient-to-r from-blue-500/20 to-purple-600/20 rounded-xl blur opacity-0 group-hover/btn:opacity-100 transition-opacity duration-300"></div>
                                <i class="fas fa-user-plus mr-2 relative z-10"></i>
                                <span class="relative z-10">Create User</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>
