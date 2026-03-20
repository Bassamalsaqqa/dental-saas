<?= $this->extend('layouts/main_control_plane') ?>

<?= $this->section('content') ?>
<?php
    $plan = $plan ?? null;
    $limits = $limits ?? [];
    $features = $features ?? [];
    $mode = $mode ?? 'create';
    $actionUrl = $mode === 'edit' && !empty($plan['id'])
        ? base_url('controlplane/plans/' . $plan['id'])
        : base_url('controlplane/plans');
?>
<div class="min-h-screen bg-gradient-to-br from-slate-50 via-emerald-50 to-teal-100 relative overflow-hidden">
    <!-- Animated Background Elements -->
    <div class="absolute inset-0 overflow-hidden pointer-events-none">
        <div class="absolute -top-40 -right-40 w-80 h-80 bg-gradient-to-br from-emerald-400/20 to-teal-600/20 rounded-full blur-3xl animate-pulse"></div>
        <div class="absolute -bottom-40 -left-40 w-80 h-80 bg-gradient-to-br from-emerald-400/20 to-cyan-600/20 rounded-full blur-3xl animate-pulse delay-1000"></div>
    </div>

    <div class="container mx-auto relative z-10 px-4 py-6">
        <!-- Enhanced Form Container with Glassmorphism -->
        <div class="group relative">
            <div class="absolute inset-0 bg-gradient-to-br from-emerald-500/10 to-teal-600/10 rounded-3xl blur-xl group-hover:blur-2xl transition-all duration-500 opacity-0 group-hover:opacity-100"></div>
            <div class="relative backdrop-blur-xl bg-white/80 border border-white/30 rounded-2xl shadow-2xl shadow-emerald-500/10 group-hover:shadow-emerald-500/20 transition-all duration-500 overflow-hidden">
                
                <!-- Form Header -->
                <div class="p-8 border-b border-white/20">
                    <div class="flex flex-col lg:flex-row lg:justify-between lg:items-center space-y-4 lg:space-y-0">
                        <div class="flex items-center space-x-4">
                            <div class="relative">
                                <div class="absolute inset-0 bg-gradient-to-br from-emerald-500 to-teal-600 rounded-2xl blur-lg opacity-75 group-hover:opacity-100 transition-opacity duration-300"></div>
                                <div class="relative w-12 h-12 bg-gradient-to-br from-emerald-500 to-teal-600 rounded-2xl flex items-center justify-center shadow-xl group-hover:scale-110 group-hover:rotate-3 transition-all duration-300">
                                    <i class="fas fa-sliders-h text-white text-lg"></i>
                                </div>
                            </div>
                            <div>
                                <h3 class="text-2xl font-black text-gray-900 group-hover:text-emerald-900 transition-colors duration-300"><?= $mode === 'edit' ? 'Edit Plan' : 'Plan Configurator' ?></h3>
                                <p class="text-gray-600 font-medium">Define entitlements, limits, and pricing models</p>
                            </div>
                        </div>
                        
                        <div class="flex items-center gap-3">
                            <span class="px-4 py-2 rounded-xl bg-slate-100 border border-slate-200 text-slate-600 text-sm font-bold shadow-sm">
                                <?= esc(ucfirst($mode)) ?> Mode
                            </span>
                            <?php if ($mode === 'edit'): ?>
                                <span class="px-4 py-2 rounded-xl border <?= ($plan['status'] ?? 'active') === 'active' ? 'bg-emerald-50 border-emerald-200 text-emerald-700' : 'bg-slate-50 border-slate-200 text-slate-600' ?> text-sm font-bold shadow-sm">
                                    <?= esc(ucfirst($plan['status'] ?? 'active')) ?>
                                </span>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- Form Content -->
                <div class="p-8">
                    <form action="<?= $actionUrl ?>" method="post" class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                        <?= csrf_field() ?>
                        
                        <!-- Left Column: Core & Limits -->
                        <div class="lg:col-span-2 space-y-8">
                            
                            <!-- Core Details Section -->
                            <div class="group/section relative">
                                <div class="absolute inset-0 bg-gradient-to-br from-blue-500/5 to-indigo-600/5 rounded-2xl blur-lg opacity-0 group-hover/section:opacity-100 transition-opacity duration-500"></div>
                                <div class="relative backdrop-blur-sm bg-white/60 border border-white/40 rounded-2xl p-8 shadow-lg">
                                    <div class="flex items-center space-x-3 mb-6">
                                        <div class="w-8 h-8 bg-gradient-to-br from-blue-500 to-indigo-600 rounded-lg flex items-center justify-center">
                                            <i class="fas fa-tag text-white text-sm"></i>
                                        </div>
                                        <h4 class="text-xl font-bold text-gray-800">Core Details</h4>
                                    </div>

                                    <div class="space-y-6">
                                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                            <div class="md:col-span-2 group/field relative">
                                                <label class="block text-sm font-bold text-gray-700 mb-3 flex items-center space-x-2">
                                                    <i class="fas fa-signature text-blue-600"></i>
                                                    <span>Plan Name</span>
                                                </label>
                                                <div class="relative">
                                                    <input type="text" name="name" value="<?= esc($plan['name'] ?? '') ?>" 
                                                           class="w-full px-4 py-3 bg-white/80 border-2 border-gray-200 rounded-xl shadow-lg focus:outline-none focus:ring-4 focus:ring-blue-500/20 focus:border-blue-500 transition-all duration-300 hover:shadow-xl" 
                                                           placeholder="e.g. Professional Tier" required>
                                                </div>
                                            </div>

                                            <div class="group/field relative">
                                                <label class="block text-sm font-bold text-gray-700 mb-3 flex items-center space-x-2">
                                                    <i class="fas fa-dollar-sign text-blue-600"></i>
                                                    <span>Price</span>
                                                </label>
                                                <div class="relative">
                                                    <input type="number" name="price" value="<?= esc($limits['price'] ?? 0) ?>" min="0" step="0.01"
                                                           class="w-full px-4 py-3 bg-white/80 border-2 border-gray-200 rounded-xl shadow-lg focus:outline-none focus:ring-4 focus:ring-blue-500/20 focus:border-blue-500 transition-all duration-300 hover:shadow-xl pl-10" 
                                                           placeholder="0.00">
                                                    <div class="absolute inset-y-0 left-0 flex items-center pl-4 pointer-events-none">
                                                        <span class="text-gray-500 font-bold">$</span>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="group/field relative">
                                                <label class="block text-sm font-bold text-gray-700 mb-3 flex items-center space-x-2">
                                                    <i class="fas fa-calendar-alt text-blue-600"></i>
                                                    <span>Billing Duration</span>
                                                </label>
                                                <div class="relative">
                                                    <select name="duration" class="w-full px-4 py-3 bg-white/80 border-2 border-gray-200 rounded-xl shadow-lg focus:outline-none focus:ring-4 focus:ring-blue-500/20 focus:border-blue-500 transition-all duration-300 hover:shadow-xl appearance-none">
                                                        <?php $duration = $limits['duration'] ?? 'monthly'; ?>
                                                        <option value="monthly" <?= $duration === 'monthly' ? 'selected' : '' ?>>Monthly</option>
                                                        <option value="yearly" <?= $duration === 'yearly' ? 'selected' : '' ?>>Yearly</option>
                                                        <option value="lifetime" <?= $duration === 'lifetime' ? 'selected' : '' ?>>Lifetime</option>
                                                    </select>
                                                    <div class="absolute inset-y-0 right-0 flex items-center pr-4 pointer-events-none">
                                                        <i class="fas fa-chevron-down text-gray-400"></i>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="md:col-span-2 group/field relative">
                                                <label class="block text-sm font-bold text-gray-700 mb-3 flex items-center space-x-2">
                                                    <i class="fas fa-align-left text-blue-600"></i>
                                                    <span>Description</span>
                                                </label>
                                                <textarea name="description" rows="3" 
                                                          class="w-full px-4 py-3 bg-white/80 border-2 border-gray-200 rounded-xl shadow-lg focus:outline-none focus:ring-4 focus:ring-blue-500/20 focus:border-blue-500 transition-all duration-300 hover:shadow-xl resize-none" 
                                                          placeholder="Public description of this plan..."><?= esc($limits['description'] ?? '') ?></textarea>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Limits & Quotas Section -->
                            <div class="group/section relative">
                                <div class="absolute inset-0 bg-gradient-to-br from-purple-500/5 to-pink-600/5 rounded-2xl blur-lg opacity-0 group-hover/section:opacity-100 transition-opacity duration-500"></div>
                                <div class="relative backdrop-blur-sm bg-white/60 border border-white/40 rounded-2xl p-8 shadow-lg">
                                    <div class="flex items-center space-x-3 mb-6">
                                        <div class="w-8 h-8 bg-gradient-to-br from-purple-500 to-pink-600 rounded-lg flex items-center justify-center">
                                            <i class="fas fa-chart-pie text-white text-sm"></i>
                                        </div>
                                        <h4 class="text-xl font-bold text-gray-800">Limits & Quotas</h4>
                                    </div>

                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                        <div class="group/field relative">
                                            <label class="block text-sm font-bold text-gray-700 mb-3 flex items-center space-x-2">
                                                <i class="fas fa-users text-purple-600"></i>
                                                <span>Patient Limit</span>
                                            </label>
                                            <input type="number" name="patient_limit" value="<?= esc($limits['patients_active_max'] ?? -1) ?>" 
                                                   class="w-full px-4 py-3 bg-white/80 border-2 border-gray-200 rounded-xl shadow-lg focus:outline-none focus:ring-4 focus:ring-purple-500/20 focus:border-purple-500 transition-all duration-300 hover:shadow-xl" 
                                                   placeholder="-1 for unlimited">
                                        </div>

                                        <div class="group/field relative">
                                            <label class="block text-sm font-bold text-gray-700 mb-3 flex items-center space-x-2">
                                                <i class="fas fa-user-shield text-purple-600"></i>
                                                <span>User Seats</span>
                                            </label>
                                            <input type="number" name="user_seats" value="<?= esc($limits['users_max'] ?? -1) ?>" 
                                                   class="w-full px-4 py-3 bg-white/80 border-2 border-gray-200 rounded-xl shadow-lg focus:outline-none focus:ring-4 focus:ring-purple-500/20 focus:border-purple-500 transition-all duration-300 hover:shadow-xl" 
                                                   placeholder="-1 for unlimited">
                                        </div>

                                        <div class="group/field relative">
                                            <label class="block text-sm font-bold text-gray-700 mb-3 flex items-center space-x-2">
                                                <i class="fas fa-hdd text-purple-600"></i>
                                                <span>Storage (GB)</span>
                                            </label>
                                            <input type="number" name="storage_gb" value="<?= esc($limits['storage_gb'] ?? -1) ?>" 
                                                   class="w-full px-4 py-3 bg-white/80 border-2 border-gray-200 rounded-xl shadow-lg focus:outline-none focus:ring-4 focus:ring-purple-500/20 focus:border-purple-500 transition-all duration-300 hover:shadow-xl" 
                                                   placeholder="-1 for unlimited">
                                        </div>

                                        <div class="group/field relative">
                                            <label class="block text-sm font-bold text-gray-700 mb-3 flex items-center space-x-2">
                                                <i class="fas fa-file-upload text-purple-600"></i>
                                                <span>Attachment Max Size (MB)</span>
                                            </label>
                                            <input type="number" name="attachments_max_file_mb" value="<?= esc($limits['attachments_max_file_mb'] ?? 5) ?>" min="-1" step="1"
                                                   class="w-full px-4 py-3 bg-white/80 border-2 border-gray-200 rounded-xl shadow-lg focus:outline-none focus:ring-4 focus:ring-purple-500/20 focus:border-purple-500 transition-all duration-300 hover:shadow-xl" 
                                                   placeholder="5">
                                            <p class="mt-2 text-xs text-slate-400">Use -1 for unlimited.</p>
                                        </div>

                                        <div class="group/field relative">
                                            <label class="block text-sm font-bold text-gray-700 mb-3 flex items-center space-x-2">
                                                <i class="fas fa-hourglass-half text-purple-600"></i>
                                                <span>Trial Period (Days)</span>
                                            </label>
                                            <input type="number" name="trial_days" value="<?= esc($limits['trial_days'] ?? 0) ?>" min="0"
                                                   class="w-full px-4 py-3 bg-white/80 border-2 border-gray-200 rounded-xl shadow-lg focus:outline-none focus:ring-4 focus:ring-purple-500/20 focus:border-purple-500 transition-all duration-300 hover:shadow-xl">
                                        </div>

                                        <div class="group/field relative">
                                            <label class="block text-sm font-bold text-gray-700 mb-3 flex items-center space-x-2">
                                                <i class="fas fa-file-export text-purple-600"></i>
                                                <span>Export Quota</span>
                                            </label>
                                            <input type="number" name="export_quota" value="<?= esc($limits['exports'] ?? -1) ?>" 
                                                   class="w-full px-4 py-3 bg-white/80 border-2 border-gray-200 rounded-xl shadow-lg focus:outline-none focus:ring-4 focus:ring-purple-500/20 focus:border-purple-500 transition-all duration-300 hover:shadow-xl">
                                        </div>

                                        <div class="group/field relative">
                                            <label class="block text-sm font-bold text-gray-700 mb-3 flex items-center space-x-2">
                                                <i class="fas fa-envelope text-purple-600"></i>
                                                <span>Email Quota</span>
                                            </label>
                                            <input type="number" name="email_quota" value="<?= esc($limits['notifications_email'] ?? -1) ?>" 
                                                   class="w-full px-4 py-3 bg-white/80 border-2 border-gray-200 rounded-xl shadow-lg focus:outline-none focus:ring-4 focus:ring-purple-500/20 focus:border-purple-500 transition-all duration-300 hover:shadow-xl">
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Feature Modules -->
                            <div class="group/section relative">
                                <div class="absolute inset-0 bg-gradient-to-br from-orange-500/5 to-amber-600/5 rounded-2xl blur-lg opacity-0 group-hover/section:opacity-100 transition-opacity duration-500"></div>
                                <div class="relative backdrop-blur-sm bg-white/60 border border-white/40 rounded-2xl p-8 shadow-lg">
                                    <div class="flex items-center space-x-3 mb-6">
                                        <div class="w-8 h-8 bg-gradient-to-br from-orange-500 to-amber-600 rounded-lg flex items-center justify-center">
                                            <i class="fas fa-toggle-on text-white text-sm"></i>
                                        </div>
                                        <h4 class="text-xl font-bold text-gray-800">Feature Modules</h4>
                                    </div>

                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                        <?php
                                            $featuresList = [
                                                'appointments' => 'Appointments',
                                                'finance' => 'Finance & Billing',
                                                'inventory' => 'Inventory Management',
                                                'reports' => 'Advanced Reports',
                                                'email_templates' => 'Email Templates',
                                                'audit_exports' => 'Audit Log Exports',
                                                'chairside_notes' => 'Chairside Notes',
                                                'claims_hub' => 'Insurance Claims',
                                            ];
                                            foreach ($featuresList as $key => $label):
                                                $checked = !empty($features[$key]) ? 'checked' : '';
                                        ?>
                                        <label class="relative flex items-center p-4 bg-white/80 border-2 border-gray-200 rounded-xl cursor-pointer hover:border-orange-400 hover:shadow-lg transition-all duration-200 group/toggle">
                                            <div class="flex items-center h-5">
                                                <input type="checkbox" name="features[<?= $key ?>]" <?= $checked ?> 
                                                       class="w-5 h-5 text-orange-600 border-gray-300 rounded focus:ring-orange-500 transition-colors">
                                            </div>
                                            <div class="ml-3 text-sm font-bold text-gray-700 group-hover/toggle:text-orange-700"><?= esc($label) ?></div>
                                        </label>
                                        <?php endforeach; ?>
                                        
                                        <!-- Nested Features -->
                                        <label class="relative flex items-center p-4 bg-white/80 border-2 border-gray-200 rounded-xl cursor-pointer hover:border-orange-400 hover:shadow-lg transition-all duration-200 group/toggle">
                                            <div class="flex items-center h-5">
                                                <input type="checkbox" name="features[exports][enabled]" <?= !empty($features['exports']['enabled']) ? 'checked' : '' ?> 
                                                       class="w-5 h-5 text-orange-600 border-gray-300 rounded focus:ring-orange-500 transition-colors">
                                            </div>
                                            <div class="ml-3 text-sm font-bold text-gray-700 group-hover/toggle:text-orange-700">Data Export Tools</div>
                                        </label>
                                        <label class="relative flex items-center p-4 bg-white/80 border-2 border-gray-200 rounded-xl cursor-pointer hover:border-orange-400 hover:shadow-lg transition-all duration-200 group/toggle">
                                            <div class="flex items-center h-5">
                                                <input type="checkbox" name="features[notifications][email][enabled]" <?= !empty($features['notifications']['email']['enabled']) ? 'checked' : '' ?> 
                                                       class="w-5 h-5 text-orange-600 border-gray-300 rounded focus:ring-orange-500 transition-colors">
                                            </div>
                                            <div class="ml-3 text-sm font-bold text-gray-700 group-hover/toggle:text-orange-700">Email Notifications</div>
                                        </label>
                                        <label class="relative flex items-center p-4 bg-white/80 border-2 border-gray-200 rounded-xl cursor-pointer hover:border-orange-400 hover:shadow-lg transition-all duration-200 group/toggle">
                                            <div class="flex items-center h-5">
                                                <input type="checkbox" name="features[notifications][whatsapp][enabled]" <?= !empty($features['notifications']['whatsapp']['enabled']) ? 'checked' : '' ?> 
                                                       class="w-5 h-5 text-orange-600 border-gray-300 rounded focus:ring-orange-500 transition-colors">
                                            </div>
                                            <div class="ml-3 text-sm font-bold text-gray-700 group-hover/toggle:text-orange-700">WhatsApp Notifications</div>
                                        </label>
                                        <label class="relative flex items-center p-4 bg-white/80 border-2 border-gray-200 rounded-xl cursor-pointer hover:border-orange-400 hover:shadow-lg transition-all duration-200 group/toggle">
                                            <div class="flex items-center h-5">
                                                <input type="checkbox" name="features[attachments][enabled]" <?= !empty($features['attachments']['enabled']) ? 'checked' : '' ?> 
                                                       class="w-5 h-5 text-orange-600 border-gray-300 rounded focus:ring-orange-500 transition-colors">
                                            </div>
                                            <div class="ml-3 text-sm font-bold text-gray-700 group-hover/toggle:text-orange-700">Patient Attachments</div>
                                        </label>
                                        <label class="relative flex items-center p-4 bg-white/80 border-2 border-gray-200 rounded-xl cursor-pointer hover:border-orange-400 hover:shadow-lg transition-all duration-200 group/toggle">
                                            <div class="flex items-center h-5">
                                                <input type="checkbox" name="features[contracts][enabled]" <?= !empty($features['contracts']['enabled']) ? 'checked' : '' ?> 
                                                       class="w-5 h-5 text-orange-600 border-gray-300 rounded focus:ring-orange-500 transition-colors">
                                            </div>
                                            <div class="ml-3 text-sm font-bold text-gray-700 group-hover/toggle:text-orange-700">Treatment Contracts</div>
                                        </label>
                                        <label class="relative flex items-center p-4 bg-white/80 border-2 border-gray-200 rounded-xl cursor-pointer hover:border-orange-400 hover:shadow-lg transition-all duration-200 group/toggle">
                                            <div class="flex items-center h-5">
                                                <input type="checkbox" name="features[contracts][signature][enabled]" <?= !empty($features['contracts']['signature']['enabled']) ? 'checked' : '' ?> 
                                                       class="w-5 h-5 text-orange-600 border-gray-300 rounded focus:ring-orange-500 transition-colors">
                                            </div>
                                            <div class="ml-3 text-sm font-bold text-gray-700 group-hover/toggle:text-orange-700">Contract E-Signatures</div>
                                        </label>
                                        <label class="relative flex items-center p-4 bg-white/80 border-2 border-gray-200 rounded-xl cursor-pointer hover:border-orange-400 hover:shadow-lg transition-all duration-200 group/toggle">
                                            <div class="flex items-center h-5">
                                                <input type="checkbox" name="features[kiosk][enabled]" <?= !empty($features['kiosk']['enabled']) ? 'checked' : '' ?> 
                                                       class="w-5 h-5 text-orange-600 border-gray-300 rounded focus:ring-orange-500 transition-colors">
                                            </div>
                                            <div class="ml-3 text-sm font-bold text-gray-700 group-hover/toggle:text-orange-700">Kiosk Check-In</div>
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Sidebar (Publish & Tips) -->
                        <div class="space-y-8">
                            <div class="group/section relative">
                                <div class="absolute inset-0 bg-gradient-to-br from-slate-500/5 to-gray-600/5 rounded-2xl blur-lg opacity-0 group-hover/section:opacity-100 transition-opacity duration-500"></div>
                                <div class="relative backdrop-blur-sm bg-white/60 border border-white/40 rounded-2xl p-8 shadow-lg">
                                    <div class="flex items-center space-x-3 mb-6">
                                        <div class="w-8 h-8 bg-gradient-to-br from-slate-700 to-gray-800 rounded-lg flex items-center justify-center">
                                            <i class="fas fa-save text-white text-sm"></i>
                                        </div>
                                        <h4 class="text-xl font-bold text-gray-800">Publish</h4>
                                    </div>
                                    
                                    <div class="space-y-6">
                                        <div class="group/field relative">
                                            <label class="block text-sm font-bold text-gray-700 mb-3 flex items-center space-x-2">
                                                <i class="fas fa-power-off text-slate-600"></i>
                                                <span>Status</span>
                                            </label>
                                            <div class="relative">
                                                <select name="status" class="w-full px-4 py-3 bg-white/80 border-2 border-gray-200 rounded-xl shadow-lg focus:outline-none focus:ring-4 focus:ring-slate-500/20 focus:border-slate-500 transition-all duration-300 hover:shadow-xl appearance-none">
                                                    <?php $status = $plan['status'] ?? 'active'; ?>
                                                    <option value="active" <?= $status === 'active' ? 'selected' : '' ?>>Active</option>
                                                    <option value="inactive" <?= $status === 'inactive' ? 'selected' : '' ?>>Inactive</option>
                                                </select>
                                                <div class="absolute inset-y-0 right-0 flex items-center pr-4 pointer-events-none">
                                                    <i class="fas fa-chevron-down text-gray-400"></i>
                                                </div>
                                            </div>
                                        </div>

                                        <button type="submit" class="w-full group/btn relative inline-flex items-center justify-center px-8 py-4 bg-gradient-to-r from-emerald-500 to-teal-600 text-white text-sm font-bold rounded-xl hover:from-emerald-600 hover:to-teal-700 transition-all duration-300 hover:scale-105 hover:shadow-2xl hover:shadow-emerald-500/25">
                                            <div class="absolute inset-0 bg-gradient-to-r from-emerald-500/20 to-teal-600/20 rounded-xl blur opacity-0 group-hover/btn:opacity-100 transition-opacity duration-300"></div>
                                            <i class="fas fa-save mr-2 relative z-10"></i>
                                            <span class="relative z-10"><?= $mode === 'edit' ? 'Update Plan' : 'Create Plan' ?></span>
                                        </button>
                                        
                                        <a href="<?= base_url('controlplane/plans') ?>" class="w-full group/btn relative inline-flex items-center justify-center px-8 py-4 border-2 border-gray-300 text-gray-700 text-sm font-bold rounded-xl hover:bg-gradient-to-r hover:from-gray-50 hover:to-slate-50 hover:border-gray-400 transition-all duration-300 hover:scale-105">
                                            Cancel
                                        </a>
                                    </div>
                                </div>
                            </div>

                            <!-- Tips -->
                            <div class="bg-blue-50/80 border border-blue-100 rounded-2xl p-6 backdrop-blur-sm">
                                <div class="flex items-center gap-2 mb-4">
                                    <i class="fas fa-lightbulb text-blue-500"></i>
                                    <h4 class="font-bold text-blue-900 text-sm">Config Tips</h4>
                                </div>
                                <ul class="space-y-3 text-xs font-medium text-blue-800">
                                    <li class="flex items-start gap-2">
                                        <i class="fas fa-check mt-0.5 text-blue-400"></i>
                                        <span>Use <strong>-1</strong> for unlimited quotas.</span>
                                    </li>
                                    <li class="flex items-start gap-2">
                                        <i class="fas fa-check mt-0.5 text-blue-400"></i>
                                        <span>Price <strong>0.00</strong> allows free trials.</span>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>
