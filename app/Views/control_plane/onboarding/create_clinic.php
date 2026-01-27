<?= $this->extend('layouts/main_control_plane') ?>

<?= $this->section('content') ?>
<div class="min-h-screen bg-gradient-to-br from-slate-50 via-blue-50 to-indigo-100 relative overflow-hidden">
    <!-- Animated Background Elements -->
    <div class="absolute inset-0 overflow-hidden pointer-events-none">
        <div class="absolute -top-40 -right-40 w-80 h-80 bg-gradient-to-br from-blue-400/20 to-purple-600/20 rounded-full blur-3xl animate-pulse"></div>
        <div class="absolute -bottom-40 -left-40 w-80 h-80 bg-gradient-to-br from-emerald-400/20 to-cyan-600/20 rounded-full blur-3xl animate-pulse delay-1000"></div>
    </div>

    <div class="container mx-auto relative z-10 px-4 py-6">
        <!-- Header -->
        <div class="mb-8">
            <div class="bg-white/80 backdrop-blur-sm border border-white/30 rounded-xl p-6 shadow-lg">
                <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-6">
                    <div class="flex items-center space-x-4">
                        <div class="w-12 h-12 bg-gradient-to-br from-blue-500 to-indigo-600 rounded-xl flex items-center justify-center text-white shadow-lg">
                            <i class="fas fa-rocket text-xl"></i>
                        </div>
                        <div>
                            <h1 class="text-3xl font-bold text-gray-900">Onboarding Studio</h1>
                            <p class="text-gray-600">Provision clinic, admin, and plan in one flow.</p>
                        </div>
                    </div>
                    <div class="flex items-center gap-3">
                        <div class="bg-white/50 rounded-lg px-3 py-1.5 border border-white/60 shadow-sm text-center">
                            <p class="text-[10px] uppercase tracking-widest text-slate-500 font-bold">Step</p>
                            <p class="text-sm font-bold text-slate-800">1 of 3</p>
                        </div>
                        <div class="bg-white/50 rounded-lg px-3 py-1.5 border border-white/60 shadow-sm text-center">
                            <p class="text-[10px] uppercase tracking-widest text-slate-500 font-bold">Status</p>
                            <p class="text-sm font-bold text-emerald-600">Ready</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <form action="<?= base_url('controlplane/onboarding/clinic/create') ?>" method="POST" class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <?= csrf_field() ?>
            
            <!-- Main Form -->
            <div class="lg:col-span-2 space-y-8">
                <!-- Clinic Details -->
                <div class="group/section relative">
                    <div class="absolute inset-0 bg-gradient-to-br from-blue-500/5 to-cyan-600/5 rounded-2xl blur-lg opacity-0 group-hover/section:opacity-100 transition-opacity duration-500"></div>
                    <div class="relative backdrop-blur-sm bg-white/60 border border-white/40 rounded-2xl p-8 shadow-lg">
                        <div class="flex items-center space-x-3 mb-6">
                            <div class="w-8 h-8 bg-gradient-to-br from-blue-500 to-cyan-600 rounded-lg flex items-center justify-center">
                                <i class="fas fa-hospital-alt text-white text-sm"></i>
                            </div>
                            <h4 class="text-xl font-bold text-gray-800">Clinic Details</h4>
                        </div>
                        
                        <div class="group/field relative">
                            <label class="block text-sm font-bold text-gray-700 mb-3 flex items-center space-x-2">
                                <i class="fas fa-signature text-blue-600"></i>
                                <span>Clinic Name</span>
                            </label>
                            <div class="relative">
                                <input type="text" name="clinic_name" value="<?= old('clinic_name') ?>" 
                                       class="w-full px-4 py-3 bg-white/80 border-2 border-gray-200 rounded-xl shadow-lg focus:outline-none focus:ring-4 focus:ring-blue-500/20 focus:border-blue-500 transition-all duration-300 hover:shadow-xl"
                                       placeholder="e.g. Springfield Dental" required>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Admin User -->
                <div class="group/section relative">
                    <div class="absolute inset-0 bg-gradient-to-br from-purple-500/5 to-pink-600/5 rounded-2xl blur-lg opacity-0 group-hover/section:opacity-100 transition-opacity duration-500"></div>
                    <div class="relative backdrop-blur-sm bg-white/60 border border-white/40 rounded-2xl p-8 shadow-lg">
                        <div class="flex items-center space-x-3 mb-6">
                            <div class="w-8 h-8 bg-gradient-to-br from-purple-500 to-pink-600 rounded-lg flex items-center justify-center">
                                <i class="fas fa-user-shield text-white text-sm"></i>
                            </div>
                            <h4 class="text-xl font-bold text-gray-800">Initial Admin User</h4>
                        </div>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div class="group/field relative">
                                <label class="block text-sm font-bold text-gray-700 mb-3 flex items-center space-x-2">
                                    <i class="fas fa-user text-purple-600"></i>
                                    <span>Admin Name</span>
                                </label>
                                <input type="text" name="admin_name" value="<?= old('admin_name') ?>" 
                                       class="w-full px-4 py-3 bg-white/80 border-2 border-gray-200 rounded-xl shadow-lg focus:outline-none focus:ring-4 focus:ring-purple-500/20 focus:border-purple-500 transition-all duration-300 hover:shadow-xl"
                                       placeholder="e.g. John Doe" required>
                            </div>
                            <div class="group/field relative">
                                <label class="block text-sm font-bold text-gray-700 mb-3 flex items-center space-x-2">
                                    <i class="fas fa-envelope text-purple-600"></i>
                                    <span>Admin Email</span>
                                </label>
                                <input type="email" name="admin_email" value="<?= old('admin_email') ?>"
                                       data-user-email-check
                                       data-password-targets="#admin_password"
                                       data-existing-note-target="#onboarding-email-exists-note"
                                       class="w-full px-4 py-3 bg-white/80 border-2 border-gray-200 rounded-xl shadow-lg focus:outline-none focus:ring-4 focus:ring-purple-500/20 focus:border-purple-500 transition-all duration-300 hover:shadow-xl"
                                       placeholder="admin@clinic.com" required>
                            </div>
                            <div class="md:col-span-2 group/field relative">
                                <label class="block text-sm font-bold text-gray-700 mb-3 flex items-center space-x-2">
                                    <i class="fas fa-key text-purple-600"></i>
                                    <span>Password</span>
                                </label>
                                <input type="password" name="admin_password" id="admin_password"
                                       class="w-full px-4 py-3 bg-white/80 border-2 border-gray-200 rounded-xl shadow-lg focus:outline-none focus:ring-4 focus:ring-purple-500/20 focus:border-purple-500 transition-all duration-300 hover:shadow-xl"
                                       placeholder="Minimum 8 characters" required minlength="8">
                                <p class="mt-2 text-xs text-slate-500 flex items-center gap-1">
                                    <i class="fas fa-info-circle"></i> Share this securely with the clinic owner.
                                </p>
                                <p id="onboarding-email-exists-note" class="mt-2 text-xs text-amber-600 hidden flex items-center gap-2" role="status">
                                    <i class="fas fa-info-circle"></i>
                                    <span data-note-text></span>
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Subscription Plan -->
                <div class="group/section relative">
                    <div class="absolute inset-0 bg-gradient-to-br from-emerald-500/5 to-green-600/5 rounded-2xl blur-lg opacity-0 group-hover/section:opacity-100 transition-opacity duration-500"></div>
                    <div class="relative backdrop-blur-sm bg-white/60 border border-white/40 rounded-2xl p-8 shadow-lg">
                        <div class="flex items-center space-x-3 mb-6">
                            <div class="w-8 h-8 bg-gradient-to-br from-emerald-500 to-green-600 rounded-lg flex items-center justify-center">
                                <i class="fas fa-layer-group text-white text-sm"></i>
                            </div>
                            <h4 class="text-xl font-bold text-gray-800">Subscription Plan</h4>
                        </div>
                        
                        <div class="grid grid-cols-1 gap-4">
                            <?php $selectedPlan = (int) (old('plan_id') ?? 0); ?>
                            <?php foreach ($plans as $plan): ?>
                                <?php 
                                    $limits = json_decode($plan['limits_json'] ?? '{}', true) ?? [];
                                    $price = $limits['price'] ?? null;
                                    $duration = $limits['duration'] ?? null;
                                ?>
                                <label class="relative flex items-start p-4 bg-white/80 border-2 border-gray-200 rounded-xl cursor-pointer hover:border-emerald-400 hover:shadow-lg transition-all duration-200 group/plan">
                                    <div class="flex items-center h-5 mt-1">
                                        <input type="radio" name="plan_id" value="<?= esc($plan['id']) ?>" 
                                               class="w-5 h-5 text-emerald-600 border-gray-300 focus:ring-emerald-500 transition-colors"
                                               <?= $selectedPlan === (int) $plan['id'] ? 'checked' : '' ?> required>
                                    </div>
                                    <div class="ml-4 flex-1">
                                        <div class="flex justify-between items-center">
                                            <span class="font-bold text-gray-900 text-lg group-hover/plan:text-emerald-700 transition-colors"><?= esc($plan['name']) ?></span>
                                            <span class="text-sm font-semibold text-emerald-600 bg-emerald-50 px-2 py-1 rounded-lg border border-emerald-100">
                                                <?= $price !== null ? '$' . esc($price) : 'Custom' ?><?= $duration ? '/' . esc(substr($duration, 0, 2)) : '' ?>
                                            </span>
                                        </div>
                                        <div class="mt-2 flex flex-wrap gap-2 text-xs text-slate-500">
                                            <span class="bg-slate-100 px-2 py-1 rounded border border-slate-200">
                                                <i class="fas fa-users mr-1"></i><?= esc($limits['patients_active_max'] ?? '0') ?> Pts
                                            </span>
                                            <span class="bg-slate-100 px-2 py-1 rounded border border-slate-200">
                                                <i class="fas fa-user-shield mr-1"></i><?= esc($limits['users_max'] ?? '0') ?> Users
                                            </span>
                                            <span class="bg-slate-100 px-2 py-1 rounded border border-slate-200">
                                                <i class="fas fa-hdd mr-1"></i><?= esc($limits['storage_gb'] ?? '0') ?> GB
                                            </span>
                                        </div>
                                    </div>
                                </label>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Sidebar -->
            <div class="space-y-6">
                <div class="bg-white rounded-xl border border-gray-200 shadow-lg p-6">
                    <h3 class="text-sm font-bold text-gray-900 uppercase tracking-wide mb-4">Checklist</h3>
                    <ul class="space-y-3 text-sm">
                        <li class="flex items-center gap-3 text-slate-600">
                            <i class="fas fa-check-circle text-emerald-500"></i> Clinic profile creation
                        </li>
                        <li class="flex items-center gap-3 text-slate-600">
                            <i class="fas fa-check-circle text-emerald-500"></i> Admin account setup
                        </li>
                        <li class="flex items-center gap-3 text-slate-600">
                            <i class="fas fa-check-circle text-emerald-500"></i> Plan activation
                        </li>
                        <li class="flex items-center gap-3 text-slate-400">
                            <i class="far fa-circle"></i> Welcome email (Manual)
                        </li>
                    </ul>
                </div>

                <div class="bg-blue-50/80 border border-blue-100 rounded-xl p-6 backdrop-blur-sm">
                    <div class="flex items-center gap-2 mb-3">
                        <i class="fas fa-shield-alt text-blue-500"></i>
                        <h4 class="font-bold text-blue-900 text-sm">Default Policy</h4>
                    </div>
                    <p class="text-xs text-blue-800 leading-relaxed">
                        The new clinic inherits global security defaults, billing cadence, and audit retention policies.
                        <br><br>
                        <strong>Mode:</strong> Production
                    </p>
                </div>

                <div class="pt-4 border-t border-slate-200">
                    <button type="submit" class="w-full group/btn relative inline-flex items-center justify-center px-8 py-4 bg-gradient-to-r from-blue-600 to-indigo-600 text-white text-sm font-bold rounded-xl hover:from-blue-700 hover:to-indigo-700 transition-all duration-300 hover:scale-105 hover:shadow-2xl hover:shadow-blue-500/25">
                        <div class="absolute inset-0 bg-gradient-to-r from-blue-500/20 to-indigo-600/20 rounded-xl blur opacity-0 group-hover/btn:opacity-100 transition-opacity duration-300"></div>
                        <i class="fas fa-magic mr-2 relative z-10"></i>
                        <span class="relative z-10">Create Clinic</span>
                    </button>
                    <a href="<?= base_url('controlplane/dashboard') ?>" class="mt-3 w-full group/btn relative inline-flex items-center justify-center px-8 py-3 bg-white border-2 border-slate-200 text-slate-600 text-sm font-bold rounded-xl hover:border-slate-300 hover:bg-slate-50 transition-all duration-300">
                        Cancel
                    </a>
                </div>
            </div>
        </form>
    </div>
</div>
<?= view('components/email_check_script', ['emailCheckUrl' => base_url('users/check-email')]) ?>
<?= $this->endSection() ?>


