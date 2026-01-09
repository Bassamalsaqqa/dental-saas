<?= $this->extend('layouts/main_auth') ?>

<?= $this->section('content') ?>
<!-- Enhanced Patient Creation with Advanced Design -->
<div class="min-h-screen bg-gradient-to-br from-slate-50 via-blue-50 to-indigo-100 relative overflow-hidden">
    <!-- Animated Background Elements -->
    <div class="absolute inset-0 overflow-hidden pointer-events-none">
        <div class="absolute -top-40 -right-40 w-80 h-80 bg-gradient-to-br from-blue-400/20 to-purple-600/20 rounded-full blur-3xl animate-pulse"></div>
        <div class="absolute -bottom-40 -left-40 w-80 h-80 bg-gradient-to-br from-emerald-400/20 to-cyan-600/20 rounded-full blur-3xl animate-pulse delay-1000"></div>
        <div class="absolute top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2 w-96 h-96 bg-gradient-to-br from-amber-400/10 to-orange-600/10 rounded-full blur-3xl animate-pulse delay-2000"></div>
    </div>

    <div class="container mx-auto relative z-10 px-4 py-6">

        <!-- Enhanced Patient Form with Glassmorphism -->
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
                                <h3 class="text-2xl font-black text-gray-900 group-hover:text-blue-900 transition-colors duration-300">Add New Patient</h3>
                                <p class="text-gray-600 font-medium">Enter patient information to create a new record</p>
                            </div>
                        </div>
                        
                        <div class="flex items-center space-x-3">
                            <a href="<?= base_url('patient') ?>" class="group/btn relative inline-flex items-center px-6 py-3 border-2 border-gray-300 text-gray-700 text-sm font-bold rounded-xl hover:bg-gradient-to-r hover:from-gray-50 hover:to-slate-50 hover:border-gray-400 transition-all duration-300 hover:scale-105">
                                <div class="absolute inset-0 bg-gradient-to-r from-gray-500/10 to-slate-500/10 rounded-xl blur opacity-0 group-hover/btn:opacity-100 transition-opacity duration-300"></div>
                                <i class="fas fa-arrow-left mr-2 relative z-10"></i>
                                <span class="relative z-10">Back to Patients</span>
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Form Content -->
                <div class="p-8">
                    <form action="<?= base_url('patient/store') ?>" method="POST" class="space-y-8">
                        <?= csrf_field() ?>
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
                                                        class="w-full px-4 py-3 bg-white/80 border-2 border-gray-200 rounded-xl shadow-lg focus:outline-none focus:ring-4 focus:ring-blue-500/20 focus:border-blue-500 transition-all duration-300 hover:shadow-xl <?= ($validation && $validation->hasError('first_name')) ? 'border-red-500 ring-red-500/20' : '' ?>"
                                                        value="<?= old('first_name') ?>" placeholder="Enter first name" required>
                                                 <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
                                                     <i class="fas fa-user text-gray-400"></i>
                                                 </div>
                                             </div>
                                             <?php if ($validation && $validation->hasError('first_name')): ?>
                                                 <p class="text-red-500 text-sm mt-2 flex items-center space-x-1">
                                                     <i class="fas fa-exclamation-circle"></i>
                                                     <span><?= $validation->getError('first_name') ?></span>
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
                                                        class="w-full px-4 py-3 bg-white/80 border-2 border-gray-200 rounded-xl shadow-lg focus:outline-none focus:ring-4 focus:ring-blue-500/20 focus:border-blue-500 transition-all duration-300 hover:shadow-xl <?= ($validation && $validation->hasError('last_name')) ? 'border-red-500 ring-red-500/20' : '' ?>"
                                                        value="<?= old('last_name') ?>" placeholder="Enter last name" required>
                                                 <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
                                                     <i class="fas fa-user text-gray-400"></i>
                                                 </div>
                                             </div>
                                             <?php if ($validation && $validation->hasError('last_name')): ?>
                                                 <p class="text-red-500 text-sm mt-2 flex items-center space-x-1">
                                                     <i class="fas fa-exclamation-circle"></i>
                                                     <span><?= $validation->getError('last_name') ?></span>
                                                 </p>
                                             <?php endif; ?>
                                         </div>
                                     </div>

                                     <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                                         <div class="group/field relative">
                                             <label class="block text-sm font-bold text-gray-700 mb-3 flex items-center space-x-2">
                                                 <i class="fas fa-envelope text-purple-600"></i>
                                                 <span>Email</span>
                                             </label>
                                             <div class="relative">
                                                 <input type="email" id="email" name="email" 
                                                        class="w-full px-4 py-3 bg-white/80 border-2 border-gray-200 rounded-xl shadow-lg focus:outline-none focus:ring-4 focus:ring-purple-500/20 focus:border-purple-500 transition-all duration-300 hover:shadow-xl <?= ($validation && $validation->hasError('email')) ? 'border-red-500 ring-red-500/20' : '' ?>"
                                                        value="<?= old('email') ?>" placeholder="Enter email address">
                                                 <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
                                                     <i class="fas fa-envelope text-gray-400"></i>
                                                 </div>
                                             </div>
                                             <?php if ($validation && $validation->hasError('email')): ?>
                                                 <p class="text-red-500 text-sm mt-2 flex items-center space-x-1">
                                                     <i class="fas fa-exclamation-circle"></i>
                                                     <span><?= $validation->getError('email') ?></span>
                                                 </p>
                                             <?php endif; ?>
                                         </div>

                                         <div class="group/field relative">
                                             <label class="block text-sm font-bold text-gray-700 mb-3 flex items-center space-x-2">
                                                 <i class="fas fa-phone text-emerald-600"></i>
                                                 <span>Phone Number *</span>
                                             </label>
                                             <div class="relative">
                                                 <input type="tel" id="phone" name="phone" 
                                                        class="w-full px-4 py-3 bg-white/80 border-2 border-gray-200 rounded-xl shadow-lg focus:outline-none focus:ring-4 focus:ring-emerald-500/20 focus:border-emerald-500 transition-all duration-300 hover:shadow-xl <?= ($validation && $validation->hasError('phone')) ? 'border-red-500 ring-red-500/20' : '' ?>"
                                                        value="<?= old('phone') ?>" placeholder="Enter phone number" required>
                                                 <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
                                                     <i class="fas fa-phone text-gray-400"></i>
                                                 </div>
                                             </div>
                                             <?php if ($validation && $validation->hasError('phone')): ?>
                                                 <p class="text-red-500 text-sm mt-2 flex items-center space-x-1">
                                                     <i class="fas fa-exclamation-circle"></i>
                                                     <span><?= $validation->getError('phone') ?></span>
                                                 </p>
                                             <?php endif; ?>
                                         </div>
                                     </div>

                                     <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                                         <div class="group/field relative">
                                             <label class="block text-sm font-bold text-gray-700 mb-3 flex items-center space-x-2">
                                                 <i class="fas fa-calendar text-amber-600"></i>
                                                 <span>Date of Birth *</span>
                                             </label>
                                             <div class="relative">
                                                 <input type="date" id="date_of_birth" name="date_of_birth" 
                                                        class="w-full px-4 py-3 bg-white/80 border-2 border-gray-200 rounded-xl shadow-lg focus:outline-none focus:ring-4 focus:ring-amber-500/20 focus:border-amber-500 transition-all duration-300 hover:shadow-xl <?= ($validation && $validation->hasError('date_of_birth')) ? 'border-red-500 ring-red-500/20' : '' ?>"
                                                        value="<?= old('date_of_birth') ?>" required>
                                                 <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
                                                     <i class="fas fa-calendar text-gray-400"></i>
                                                 </div>
                                             </div>
                                             <?php if ($validation && $validation->hasError('date_of_birth')): ?>
                                                 <p class="text-red-500 text-sm mt-2 flex items-center space-x-1">
                                                     <i class="fas fa-exclamation-circle"></i>
                                                     <span><?= $validation->getError('date_of_birth') ?></span>
                                                 </p>
                                             <?php endif; ?>
                                         </div>

                                         <div class="group/field relative">
                                             <label class="block text-sm font-bold text-gray-700 mb-3 flex items-center space-x-2">
                                                 <i class="fas fa-venus-mars text-rose-600"></i>
                                                 <span>Gender *</span>
                                             </label>
                                             <div class="relative">
                                                 <select id="gender" name="gender" 
                                                         class="w-full px-4 py-3 bg-white/80 border-2 border-gray-200 rounded-xl shadow-lg focus:outline-none focus:ring-4 focus:ring-rose-500/20 focus:border-rose-500 transition-all duration-300 hover:shadow-xl <?= ($validation && $validation->hasError('gender')) ? 'border-red-500 ring-red-500/20' : '' ?>" required>
                                                     <option value="">Select Gender</option>
                                                     <option value="male" <?= old('gender') == 'male' ? 'selected' : '' ?>>Male</option>
                                                     <option value="female" <?= old('gender') == 'female' ? 'selected' : '' ?>>Female</option>
                                                     <option value="other" <?= old('gender') == 'other' ? 'selected' : '' ?>>Other</option>
                                                 </select>
                                                 <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
                                                     <i class="fas fa-chevron-down text-gray-400"></i>
                                                 </div>
                                             </div>
                                             <?php if ($validation && $validation->hasError('gender')): ?>
                                                 <p class="text-red-500 text-sm mt-2 flex items-center space-x-1">
                                                     <i class="fas fa-exclamation-circle"></i>
                                                     <span><?= $validation->getError('gender') ?></span>
                                                 </p>
                                             <?php endif; ?>
                                         </div>
                                     </div>
                                 </div>
                            </div>
                        </div>

                        <!-- Address Information -->
                        <div class="group/section relative">
                            <div class="absolute inset-0 bg-gradient-to-br from-green-500/5 to-teal-600/5 rounded-2xl blur-lg opacity-0 group-hover/section:opacity-100 transition-opacity duration-500"></div>
                            <div class="relative backdrop-blur-sm bg-white/60 border border-white/40 rounded-2xl p-8 shadow-lg">
                                <div class="flex items-center space-x-3 mb-6">
                                    <div class="w-8 h-8 bg-gradient-to-br from-green-500 to-teal-600 rounded-lg flex items-center justify-center">
                                        <i class="fas fa-map-marker-alt text-white text-sm"></i>
                                    </div>
                                    <h4 class="text-xl font-bold text-gray-800">Address Information</h4>
                                </div>
                                
                                 <div class="space-y-8">
                                     <div class="group/field relative">
                                         <label class="block text-sm font-bold text-gray-700 mb-3 flex items-center space-x-2">
                                             <i class="fas fa-map-marker-alt text-green-600"></i>
                                             <span>Address</span>
                                         </label>
                                         <div class="relative">
                                             <textarea id="address" name="address" rows="3" 
                                                       class="w-full px-4 py-3 bg-white/80 border-2 border-gray-200 rounded-xl shadow-lg focus:outline-none focus:ring-4 focus:ring-green-500/20 focus:border-green-500 transition-all duration-300 hover:shadow-xl resize-none" 
                                                       placeholder="Enter full address"><?= old('address') ?></textarea>
                                             <div class="absolute bottom-3 right-3 text-gray-400 text-xs">
                                                 <i class="fas fa-edit"></i>
                                             </div>
                                         </div>
                                     </div>

                                     <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                                         <div class="group/field relative">
                                             <label class="block text-sm font-bold text-gray-700 mb-3 flex items-center space-x-2">
                                                 <i class="fas fa-city text-green-600"></i>
                                                 <span>City</span>
                                             </label>
                                             <div class="relative">
                                                 <input type="text" id="city" name="city" 
                                                        class="w-full px-4 py-3 bg-white/80 border-2 border-gray-200 rounded-xl shadow-lg focus:outline-none focus:ring-4 focus:ring-green-500/20 focus:border-green-500 transition-all duration-300 hover:shadow-xl" 
                                                        value="<?= old('city') ?>" placeholder="Enter city">
                                                 <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
                                                     <i class="fas fa-city text-gray-400"></i>
                                                 </div>
                                             </div>
                                         </div>

                                         <div class="group/field relative">
                                             <label class="block text-sm font-bold text-gray-700 mb-3 flex items-center space-x-2">
                                                 <i class="fas fa-map text-green-600"></i>
                                                 <span>State</span>
                                             </label>
                                             <div class="relative">
                                                 <input type="text" id="state" name="state" 
                                                        class="w-full px-4 py-3 bg-white/80 border-2 border-gray-200 rounded-xl shadow-lg focus:outline-none focus:ring-4 focus:ring-green-500/20 focus:border-green-500 transition-all duration-300 hover:shadow-xl" 
                                                        value="<?= old('state') ?>" placeholder="Enter state">
                                                 <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
                                                     <i class="fas fa-map text-gray-400"></i>
                                                 </div>
                                             </div>
                                         </div>

                                         <div class="group/field relative">
                                             <label class="block text-sm font-bold text-gray-700 mb-3 flex items-center space-x-2">
                                                 <i class="fas fa-mail-bulk text-green-600"></i>
                                                 <span>ZIP Code</span>
                                             </label>
                                             <div class="relative">
                                                 <input type="text" id="zip_code" name="zip_code" 
                                                        class="w-full px-4 py-3 bg-white/80 border-2 border-gray-200 rounded-xl shadow-lg focus:outline-none focus:ring-4 focus:ring-green-500/20 focus:border-green-500 transition-all duration-300 hover:shadow-xl" 
                                                        value="<?= old('zip_code') ?>" placeholder="Enter ZIP code">
                                                 <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
                                                     <i class="fas fa-mail-bulk text-gray-400"></i>
                                                 </div>
                                             </div>
                                         </div>
                                     </div>

                                     <div class="group/field relative">
                                         <label class="block text-sm font-bold text-gray-700 mb-3 flex items-center space-x-2">
                                             <i class="fas fa-globe text-green-600"></i>
                                             <span>Country</span>
                                         </label>
                                         <div class="relative">
                                             <input type="text" id="country" name="country" 
                                                    class="w-full px-4 py-3 bg-white/80 border-2 border-gray-200 rounded-xl shadow-lg focus:outline-none focus:ring-4 focus:ring-green-500/20 focus:border-green-500 transition-all duration-300 hover:shadow-xl" 
                                                    value="<?= old('country') ?: 'United States' ?>" placeholder="Enter country">
                                             <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
                                                 <i class="fas fa-globe text-gray-400"></i>
                                             </div>
                                         </div>
                                     </div>
                                 </div>
                            </div>
                        </div>

                        <!-- Emergency Contact -->
                        <div class="group/section relative">
                            <div class="absolute inset-0 bg-gradient-to-br from-orange-500/5 to-red-600/5 rounded-2xl blur-lg opacity-0 group-hover/section:opacity-100 transition-opacity duration-500"></div>
                            <div class="relative backdrop-blur-sm bg-white/60 border border-white/40 rounded-2xl p-8 shadow-lg">
                                <div class="flex items-center space-x-3 mb-6">
                                    <div class="w-8 h-8 bg-gradient-to-br from-orange-500 to-red-600 rounded-lg flex items-center justify-center">
                                        <i class="fas fa-phone text-white text-sm"></i>
                                    </div>
                                    <h4 class="text-xl font-bold text-gray-800">Emergency Contact</h4>
                                </div>
                                
                                 <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                                     <div class="group/field relative">
                                         <label class="block text-sm font-bold text-gray-700 mb-3 flex items-center space-x-2">
                                             <i class="fas fa-user-friends text-orange-600"></i>
                                             <span>Emergency Contact Name</span>
                                         </label>
                                         <div class="relative">
                                             <input type="text" id="emergency_contact_name" name="emergency_contact_name" 
                                                    class="w-full px-4 py-3 bg-white/80 border-2 border-gray-200 rounded-xl shadow-lg focus:outline-none focus:ring-4 focus:ring-orange-500/20 focus:border-orange-500 transition-all duration-300 hover:shadow-xl" 
                                                    value="<?= old('emergency_contact_name') ?>" placeholder="Enter emergency contact name">
                                             <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
                                                 <i class="fas fa-user-friends text-gray-400"></i>
                                             </div>
                                         </div>
                                     </div>

                                     <div class="group/field relative">
                                         <label class="block text-sm font-bold text-gray-700 mb-3 flex items-center space-x-2">
                                             <i class="fas fa-phone text-orange-600"></i>
                                             <span>Emergency Contact Phone</span>
                                         </label>
                                         <div class="relative">
                                             <input type="tel" id="emergency_contact_phone" name="emergency_contact_phone" 
                                                    class="w-full px-4 py-3 bg-white/80 border-2 border-gray-200 rounded-xl shadow-lg focus:outline-none focus:ring-4 focus:ring-orange-500/20 focus:border-orange-500 transition-all duration-300 hover:shadow-xl" 
                                                    value="<?= old('emergency_contact_phone') ?>" placeholder="Enter emergency contact phone">
                                             <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
                                                 <i class="fas fa-phone text-gray-400"></i>
                                             </div>
                                         </div>
                                     </div>
                                 </div>
                            </div>
                        </div>

                        <!-- Medical Information -->
                        <div class="group/section relative">
                            <div class="absolute inset-0 bg-gradient-to-br from-purple-500/5 to-pink-600/5 rounded-2xl blur-lg opacity-0 group-hover/section:opacity-100 transition-opacity duration-500"></div>
                            <div class="relative backdrop-blur-sm bg-white/60 border border-white/40 rounded-2xl p-8 shadow-lg">
                                <div class="flex items-center space-x-3 mb-6">
                                    <div class="w-8 h-8 bg-gradient-to-br from-purple-500 to-pink-600 rounded-lg flex items-center justify-center">
                                        <i class="fas fa-heartbeat text-white text-sm"></i>
                                    </div>
                                    <h4 class="text-xl font-bold text-gray-800">Medical Information</h4>
                                </div>
                                
                                 <div class="space-y-8">
                                     <div class="group/field relative">
                                         <label class="block text-sm font-bold text-gray-700 mb-3 flex items-center space-x-2">
                                             <i class="fas fa-heartbeat text-purple-600"></i>
                                             <span>Medical History</span>
                                         </label>
                                         <div class="relative">
                                             <textarea id="medical_history" name="medical_history" rows="4" 
                                                       class="w-full px-4 py-3 bg-white/80 border-2 border-gray-200 rounded-xl shadow-lg focus:outline-none focus:ring-4 focus:ring-purple-500/20 focus:border-purple-500 transition-all duration-300 hover:shadow-xl resize-none" 
                                                       placeholder="Any relevant medical conditions, medications, etc."><?= old('medical_history') ?></textarea>
                                             <div class="absolute bottom-3 right-3 text-gray-400 text-xs">
                                                 <i class="fas fa-edit"></i>
                                             </div>
                                         </div>
                                     </div>

                                     <div class="group/field relative">
                                         <label class="block text-sm font-bold text-gray-700 mb-3 flex items-center space-x-2">
                                             <i class="fas fa-exclamation-triangle text-red-600"></i>
                                             <span>Allergies</span>
                                         </label>
                                         <div class="relative">
                                             <textarea id="allergies" name="allergies" rows="2" 
                                                       class="w-full px-4 py-3 bg-white/80 border-2 border-gray-200 rounded-xl shadow-lg focus:outline-none focus:ring-4 focus:ring-red-500/20 focus:border-red-500 transition-all duration-300 hover:shadow-xl resize-none" 
                                                       placeholder="List any known allergies"><?= old('allergies') ?></textarea>
                                             <div class="absolute bottom-3 right-3 text-gray-400 text-xs">
                                                 <i class="fas fa-edit"></i>
                                             </div>
                                         </div>
                                     </div>
                                 </div>
                            </div>
                        </div>


                        <!-- Additional Notes -->
                        <div class="group/section relative">
                            <div class="absolute inset-0 bg-gradient-to-br from-gray-500/5 to-slate-600/5 rounded-2xl blur-lg opacity-0 group-hover/section:opacity-100 transition-opacity duration-500"></div>
                            <div class="relative backdrop-blur-sm bg-white/60 border border-white/40 rounded-2xl p-8 shadow-lg">
                                <div class="flex items-center space-x-3 mb-6">
                                    <div class="w-8 h-8 bg-gradient-to-br from-gray-500 to-slate-600 rounded-lg flex items-center justify-center">
                                        <i class="fas fa-sticky-note text-white text-sm"></i>
                                    </div>
                                    <h4 class="text-xl font-bold text-gray-800">Additional Notes</h4>
                                </div>
                                
                                 <div class="group/field relative">
                                     <label class="block text-sm font-bold text-gray-700 mb-3 flex items-center space-x-2">
                                         <i class="fas fa-sticky-note text-gray-600"></i>
                                         <span>Additional Notes</span>
                                     </label>
                                     <div class="relative">
                                         <textarea id="notes" name="notes" rows="4" 
                                                   class="w-full px-4 py-3 bg-white/80 border-2 border-gray-200 rounded-xl shadow-lg focus:outline-none focus:ring-4 focus:ring-gray-500/20 focus:border-gray-500 transition-all duration-300 hover:shadow-xl resize-none" 
                                                   placeholder="Any additional notes about the patient"><?= old('notes') ?></textarea>
                                         <div class="absolute bottom-3 right-3 text-gray-400 text-xs">
                                             <i class="fas fa-edit"></i>
                                         </div>
                                     </div>
                                 </div>
                            </div>
                        </div>

                        <!-- Form Actions -->
                        <div class="flex flex-col sm:flex-row justify-end gap-4 sm:gap-12 pt-6 border-t border-white/20">
                            <a href="<?= base_url('patient') ?>" class="group/btn relative inline-flex items-center justify-center px-8 py-3 border-2 border-gray-300 text-gray-700 text-sm font-bold rounded-xl hover:bg-gradient-to-r hover:from-gray-50 hover:to-slate-50 hover:border-gray-400 transition-all duration-300 hover:scale-105">
                                <div class="absolute inset-0 bg-gradient-to-r from-gray-500/10 to-slate-500/10 rounded-xl blur opacity-0 group-hover/btn:opacity-100 transition-opacity duration-300"></div>
                                <i class="fas fa-times mr-2 relative z-10"></i>
                                <span class="relative z-10">Cancel</span>
                            </a>
                            <button type="submit" class="group/btn relative inline-flex items-center justify-center px-8 py-3 bg-gradient-to-r from-blue-500 to-purple-600 text-white text-sm font-bold rounded-xl hover:from-blue-600 hover:to-purple-700 transition-all duration-300 hover:scale-105 hover:shadow-2xl hover:shadow-blue-500/25">
                                <div class="absolute inset-0 bg-gradient-to-r from-blue-500/20 to-purple-600/20 rounded-xl blur opacity-0 group-hover/btn:opacity-100 transition-opacity duration-300"></div>
                                <i class="fas fa-user-plus mr-2 relative z-10"></i>
                                <span class="relative z-10">Create Patient</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>
