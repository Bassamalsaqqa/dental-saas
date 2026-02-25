<?= $this->extend('layouts/main_auth') ?>

<?= $this->section('content') ?>
<!-- Select2 CSS -->
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<style>
    .select2-container--default .select2-selection--single {
        height: 56px !important;
        border: 2px solid #e5e7eb !important;
        border-radius: 12px !important;
        padding: 0 16px !important;
        background-color: rgba(249, 250, 251, 0.5) !important;
    }
    
    .select2-container--default .select2-selection--single .select2-selection__rendered {
        line-height: 52px !important;
        padding-left: 0 !important;
        color: #374151 !important;
        font-weight: 500 !important;
    }
    
    .select2-container--default .select2-selection--single .select2-selection__arrow {
        height: 52px !important;
        right: 16px !important;
    }
    
    .select2-container--default.select2-container--focus .select2-selection--single {
        border-color: #3b82f6 !important;
        box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.1) !important;
    }
    
    .select2-dropdown {
        border: 2px solid #e5e7eb !important;
        border-radius: 12px !important;
        box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05) !important;
    }
    
    .select2-container--default .select2-search--dropdown .select2-search__field {
        border: 1px solid #d1d5db !important;
        border-radius: 8px !important;
        padding: 8px 12px !important;
        margin: 8px !important;
        width: calc(100% - 16px) !important;
    }
    
    .select2-container--default .select2-results__option--highlighted[aria-selected] {
        background-color: #3b82f6 !important;
        color: white !important;
    }
    
    .select2-container--default .select2-results__option[aria-selected=true] {
        background-color: #dbeafe !important;
        color: #1e40af !important;
    }
    
    .select2-container--default .select2-results__group {
        background-color: #f3f4f6 !important;
        color: #374151 !important;
        font-weight: 600 !important;
        padding: 8px 12px !important;
        border-bottom: 1px solid #e5e7eb !important;
    }
</style>

<div class="min-h-screen bg-gradient-to-br from-slate-50 via-blue-50 to-indigo-100">
    <div class="container mx-auto px-6 py-8">
        <!-- Page Header -->
        <div class="mb-8">
            <div class="flex items-center space-x-4">
                <div class="w-12 h-12 bg-gradient-to-br from-blue-500 to-indigo-600 rounded-xl flex items-center justify-center text-white shadow-lg">
                    <i class="fas fa-cog text-xl"></i>
                    </div>
                <div>
                    <h1 class="text-3xl font-bold text-gray-900">System Settings</h1>
                    <p class="text-gray-600">Configure your dental management system</p>
                </div>
            </div>
        </div>

        <!-- Settings Layout with Sidebar -->
        <div class="flex flex-col lg:flex-row gap-8">
            <!-- Left Sidebar -->
            <div class="lg:w-80 flex-shrink-0">
                <div class="bg-white/90 backdrop-blur-xl rounded-2xl shadow-xl shadow-blue-500/10 border border-white/30 p-6">
                    <h3 class="text-lg font-bold text-gray-900 mb-6">Settings Menu</h3>
                    <nav class="space-y-2">
                        <button onclick="showSettingsTab('clinic')" id="tab-clinic" class="w-full flex items-center px-4 py-3 text-left text-sm font-medium rounded-xl transition-all duration-200 bg-gradient-to-r from-blue-500 to-blue-600 text-white shadow-lg">
                            <i class="fas fa-building w-5 h-5 mr-3"></i>
                            Clinic Information
                        </button>
                        <button onclick="showSettingsTab('subscription')" id="tab-subscription" class="w-full flex items-center px-4 py-3 text-left text-sm font-medium rounded-xl transition-all duration-200 text-gray-700 hover:bg-gray-100 hover:text-gray-900">
                            <i class="fas fa-layer-group w-5 h-5 mr-3"></i>
                            Subscription Plan
                        </button>
                        <button onclick="showSettingsTab('system')" id="tab-system" class="w-full flex items-center px-4 py-3 text-left text-sm font-medium rounded-xl transition-all duration-200 text-gray-700 hover:bg-gray-100 hover:text-gray-900">
                            <i class="fas fa-cog w-5 h-5 mr-3"></i>
                            System Preference
                        </button>
                        <button onclick="showSettingsTab('hours')" id="tab-hours" class="w-full flex items-center px-4 py-3 text-left text-sm font-medium rounded-xl transition-all duration-200 text-gray-700 hover:bg-gray-100 hover:text-gray-900">
                            <i class="fas fa-clock w-5 h-5 mr-3"></i>
                            Working Hours
                        </button>
                        <button onclick="showSettingsTab('kiosk')" id="tab-kiosk" class="w-full flex items-center px-4 py-3 text-left text-sm font-medium rounded-xl transition-all duration-200 text-gray-700 hover:bg-gray-100 hover:text-gray-900">
                            <i class="fas fa-tablet-alt w-5 h-5 mr-3"></i>
                            Kiosk Check-In
                        </button>
                        <button onclick="showSettingsTab('backup')" id="tab-backup" class="w-full flex items-center px-4 py-3 text-left text-sm font-medium rounded-xl transition-all duration-200 text-gray-700 hover:bg-gray-100 hover:text-gray-900">
                            <i class="fas fa-database w-5 h-5 mr-3"></i>
                            DB Backup
                        </button>
                        <a href="<?= base_url('settings/channels') ?>" class="w-full flex items-center px-4 py-3 text-left text-sm font-medium rounded-xl transition-all duration-200 text-gray-700 hover:bg-gray-100 hover:text-gray-900">
                            <i class="fas fa-broadcast-tower w-5 h-5 mr-3"></i>
                            Notification Channels
                        </a>
                        <?php if (session()->get('global_mode')): ?>
                        <button onclick="showSettingsTab('retention')" id="tab-retention" class="w-full flex items-center px-4 py-3 text-left text-sm font-medium rounded-xl transition-all duration-200 text-gray-700 hover:bg-gray-100 hover:text-gray-900">
                            <i class="fas fa-history w-5 h-5 mr-3"></i>
                            Export Retention
                        </button>
                        <?php endif; ?>
                    </nav>
                </div>
            </div>

            <!-- Main Content Area -->
            <div class="flex-1">
                <!-- Clinic Information Tab -->
                <div id="clinic-tab" class="settings-tab">
                    <div class="bg-white/90 backdrop-blur-xl rounded-2xl shadow-xl shadow-blue-500/10 border border-white/30 overflow-hidden">
                        <div class="bg-gradient-to-r from-blue-500 via-indigo-600 to-purple-600 p-8">
                            <div class="flex items-center space-x-4">
                                <div class="w-12 h-12 bg-white/20 rounded-xl flex items-center justify-center backdrop-blur-sm">
                                    <i class="fas fa-building text-white text-xl"></i>
                                </div>
                                <div>
                                    <h3 class="text-2xl font-bold text-white">Clinic Information</h3>
                                    <p class="text-blue-100">Manage your clinic details and contact information</p>
                                </div>
                            </div>
                        </div>
                        
                        <div class="p-8">
                            <form action="<?= base_url('settings/updateClinic') ?>" method="POST" enctype="multipart/form-data">
                                <?= csrf_field() ?>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <div>
                                        <label class="block text-sm font-semibold text-gray-700 mb-2">Clinic Name</label>
                                        <input type="text" name="clinic_name" value="<?= $settings['clinic_name'] ?? '' ?>" class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:border-blue-500 focus:ring-4 focus:ring-blue-100 transition-all duration-200" required>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-semibold text-gray-700 mb-2">Clinic Phone</label>
                                        <input type="tel" name="clinic_phone" value="<?= $settings['clinic_phone'] ?? '' ?>" class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:border-blue-500 focus:ring-4 focus:ring-blue-100 transition-all duration-200" required>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-semibold text-gray-700 mb-2">Clinic Email</label>
                                        <input type="email" name="clinic_email" value="<?= $settings['clinic_email'] ?? '' ?>" class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:border-blue-500 focus:ring-4 focus:ring-blue-100 transition-all duration-200" required>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-semibold text-gray-700 mb-2">Clinic Website</label>
                                        <input type="url" name="clinic_website" value="<?= esc($settings['clinic_website'] ?? '') ?>" class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:border-blue-500 focus:ring-4 focus:ring-blue-100 transition-all duration-200">
                                    </div>
                                    <div>
                                        <label class="block text-sm font-semibold text-gray-700 mb-2">Clinic Tagline</label>
                                        <input type="text" name="clinic_tagline" value="<?= esc($settings['clinic_tagline'] ?? '') ?>" class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:border-blue-500 focus:ring-4 focus:ring-blue-100 transition-all duration-200" placeholder="Clinic tagline">
                                    </div>
                                    <div>
                                        <label class="block text-sm font-semibold text-gray-700 mb-2">Clinic Logo Upload</label>
                                        <input type="file" name="clinic_logo" accept=".png,.jpg,.jpeg,.webp" class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:border-blue-500 focus:ring-4 focus:ring-blue-100 transition-all duration-200">
                                        <p class="text-xs text-gray-500 mt-1">Allowed: PNG, JPG, WEBP. Max size: 512 KB. Recommended: 40x40 px (square).</p>
                                    </div>
                                    <div class="flex items-center">
                                        <label class="flex items-center space-x-3 cursor-pointer">
                                            <input type="checkbox" name="clinic_logo_remove" value="1" class="w-5 h-5 text-blue-600 rounded border-gray-300 focus:ring-blue-500 transition-colors">
                                            <span class="text-sm font-semibold text-gray-700">Remove logo (use default)</span>
                                        </label>
                                    </div>
                                    <div class="md:col-span-2">
                                        <label class="block text-sm font-semibold text-gray-700 mb-2">Clinic Address</label>
                                        <textarea name="clinic_address" rows="3" class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:border-blue-500 focus:ring-4 focus:ring-blue-100 transition-all duration-200" required><?= esc($settings['clinic_address'] ?? '') ?></textarea>
                                    </div>
                                </div>

                                <div class="mt-8 flex justify-end">
                                    <button type="submit" class="px-8 py-3 bg-gradient-to-r from-blue-500 to-indigo-600 text-white font-semibold rounded-xl hover:from-blue-600 hover:to-indigo-700 transition-all duration-200 shadow-lg hover:shadow-xl">
                                        <i class="fas fa-save mr-2"></i>Save Changes
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Subscription Plan Tab -->
                <div id="subscription-tab" class="settings-tab hidden">
                    <div class="bg-white/90 backdrop-blur-xl rounded-2xl shadow-xl shadow-blue-500/10 border border-white/30 overflow-hidden">
                        <div class="bg-gradient-to-r from-emerald-500 via-teal-600 to-cyan-600 p-8">
                            <div class="flex items-center space-x-4">
                                <div class="w-12 h-12 bg-white/20 rounded-xl flex items-center justify-center backdrop-blur-sm">
                                    <i class="fas fa-layer-group text-white text-xl"></i>
                                </div>
                                <div>
                                    <h3 class="text-2xl font-bold text-white">Subscription Plan</h3>
                                    <p class="text-emerald-100">Review current plan and request upgrades</p>
                                </div>
                            </div>
                        </div>

                        <div class="p-8 space-y-8">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div class="bg-white border border-slate-200 rounded-2xl p-6">
                                    <h4 class="text-sm font-bold text-slate-800">Current Plan</h4>
                                    <p class="text-2xl font-bold text-slate-900 mt-3">
                                        <?= esc($subscription_plan['name'] ?? 'Unassigned') ?>
                                    </p>
                                    <p class="text-sm text-slate-500 mt-2">
                                        Status: <?= esc($subscription['status'] ?? 'none') ?>
                                    </p>
                                    <p class="text-sm text-slate-500">Start: <?= esc($subscription['start_at'] ?? '--') ?></p>
                                    <p class="text-sm text-slate-500">End: <?= esc($subscription['end_at'] ?? '--') ?></p>
                                </div>
                                <div class="bg-white border border-slate-200 rounded-2xl p-6">
                                    <h4 class="text-sm font-bold text-slate-800">Latest Request</h4>
                                    <?php if (!empty($latest_plan_request)): ?>
                                        <p class="text-sm text-slate-500 mt-2">Plan ID: <?= esc($latest_plan_request['requested_plan_id']) ?></p>
                                        <p class="text-sm text-slate-500">Duration: <?= esc(ucfirst($latest_plan_request['requested_duration'] ?? 'monthly')) ?></p>
                                        <p class="text-sm text-slate-500">Status: <?= esc(ucfirst($latest_plan_request['status'] ?? 'pending')) ?></p>
                                        <p class="text-xs text-slate-400 mt-2">Requested: <?= esc($latest_plan_request['created_at'] ?? '--') ?></p>
                                    <?php else: ?>
                                        <p class="text-sm text-slate-500 mt-2">No plan requests yet.</p>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <form action="<?= base_url('settings/plan-request') ?>" method="POST" class="bg-white border border-slate-200 rounded-2xl p-6 space-y-4">
                                <?= csrf_field() ?>
                                <h4 class="text-sm font-bold text-slate-800">Request a new plan</h4>
                                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                    <div>
                                        <label class="block text-xs font-semibold text-gray-700 mb-2">Plan</label>
                                        <select name="plan_id" class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:border-emerald-500 focus:ring-4 focus:ring-emerald-100 transition-all duration-200" required>
                                            <option value="">Select plan</option>
                                            <?php foreach (($plans ?? []) as $plan): ?>
                                                <option value="<?= esc($plan['id']) ?>"><?= esc($plan['name']) ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <div>
                                        <label class="block text-xs font-semibold text-gray-700 mb-2">Duration</label>
                                        <select name="duration" class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:border-emerald-500 focus:ring-4 focus:ring-emerald-100 transition-all duration-200">
                                            <option value="monthly">Monthly</option>
                                            <option value="yearly">Yearly</option>
                                            <option value="lifetime">Lifetime</option>
                                        </select>
                                    </div>
                                    <div>
                                        <label class="block text-xs font-semibold text-gray-700 mb-2">Reason (optional)</label>
                                        <input type="text" name="reason" class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:border-emerald-500 focus:ring-4 focus:ring-emerald-100 transition-all duration-200" placeholder="Upgrade request reason">
                                    </div>
                                </div>
                                <div class="flex justify-end">
                                    <button type="submit" class="px-6 py-3 bg-gradient-to-r from-emerald-500 to-teal-600 text-white font-semibold rounded-xl hover:from-emerald-600 hover:to-teal-700 transition-all shadow-lg">
                                        <i class="fas fa-paper-plane mr-2"></i>Submit Request
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
                                    
                <!-- Kiosk Check-In Tab -->
                <div id="kiosk-tab" class="settings-tab hidden">
                    <div class="bg-white/90 backdrop-blur-xl rounded-2xl shadow-xl shadow-blue-500/10 border border-white/30 overflow-hidden">
                        <div class="bg-gradient-to-r from-indigo-500 via-blue-600 to-cyan-600 p-8">
                            <div class="flex items-center space-x-4">
                                <div class="w-12 h-12 bg-white/20 rounded-xl flex items-center justify-center backdrop-blur-sm">
                                    <i class="fas fa-tablet-alt text-white text-xl"></i>
                                </div>
                                <div>
                                    <h3 class="text-2xl font-bold text-white">Kiosk Check-In</h3>
                                    <p class="text-blue-100">Configure the in-clinic self check-in experience</p>
                                </div>
                            </div>
                        </div>

                        <div class="p-8 space-y-8">
                            <?php $kioskFeatureEnabled = !empty($kiosk_feature_enabled); ?>
                            <?php $kioskLink = !empty($clinic_uuid) ? base_url('kiosk/' . $clinic_uuid . '/check-in') : ''; ?>

                            <?php if (!$kioskFeatureEnabled): ?>
                                <div class="bg-amber-50 border border-amber-200 rounded-2xl p-5 text-amber-700 text-sm">
                                    Kiosk check-in is not enabled in your plan. Ask the clinic administrator to enable the feature.
                                </div>
                            <?php endif; ?>

                            <div class="bg-white border border-slate-200 rounded-2xl p-6 space-y-4">
                                <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                                    <div>
                                        <h4 class="text-sm font-bold text-slate-800">Kiosk Link</h4>
                                        <p class="text-sm text-slate-500">Open this link on the clinic tablet or kiosk device.</p>
                                    </div>
                                    <div class="flex items-center gap-2">
                                        <span class="px-3 py-1 rounded-full text-xs font-semibold <?= ($kiosk_pin_set ?? false) ? 'bg-emerald-100 text-emerald-700' : 'bg-amber-100 text-amber-700' ?>">
                                            <?= ($kiosk_pin_set ?? false) ? 'PIN Set' : 'PIN Not Set' ?>
                                        </span>
                                    </div>
                                </div>
                                <div class="flex flex-col md:flex-row gap-3">
                                    <input id="kioskLinkInput" type="text" readonly value="<?= esc($kioskLink) ?>" class="flex-1 px-4 py-3 border-2 border-gray-200 rounded-xl bg-gray-50 text-sm text-gray-600" />
                                    <button type="button" onclick="copyKioskLink()" class="px-5 py-3 bg-white border-2 border-blue-200 text-blue-600 font-semibold rounded-xl hover:bg-blue-50 transition-all" <?= $kioskFeatureEnabled ? '' : 'disabled' ?>>
                                        <i class="fas fa-copy mr-2"></i>Copy Link
                                    </button>
                                </div>
                            </div>

                            <form action="<?= base_url('settings/updateKiosk') ?>" method="POST" class="bg-white border border-slate-200 rounded-2xl p-6 space-y-6 <?= $kioskFeatureEnabled ? '' : 'opacity-60' ?>">
                                <?= csrf_field() ?>
                                <div class="flex items-center justify-between">
                                    <div>
                                        <h4 class="text-sm font-bold text-slate-800">Enable Kiosk Check-In</h4>
                                        <p class="text-sm text-slate-500">Allow patients to confirm appointments from a clinic kiosk.</p>
                                    </div>
                                      <label class="inline-flex items-center cursor-pointer">
                                          <input type="checkbox" name="kiosk_enabled" value="1" class="sr-only kiosk-toggle" <?= !empty($settings['kiosk_enabled']) ? 'checked' : '' ?> <?= $kioskFeatureEnabled ? '' : 'disabled' ?>>
                                          <span class="kiosk-toggle-track w-11 h-6 rounded-full relative transition-all duration-200 <?= !empty($settings['kiosk_enabled']) ? 'bg-blue-500' : 'bg-gray-200' ?>">
                                              <span class="kiosk-toggle-knob absolute left-1 top-1 w-4 h-4 bg-white rounded-full transition-all duration-200 <?= !empty($settings['kiosk_enabled']) ? 'translate-x-5 bg-blue-500' : '' ?>"></span>
                                          </span>
                                      </label>
                                </div>

                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <div>
                                        <label class="block text-sm font-semibold text-gray-700 mb-2">Kiosk Header</label>
                                        <input type="text" name="kiosk_header" value="<?= esc($settings['kiosk_header'] ?? 'Self Check-In') ?>" class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:border-blue-500 focus:ring-4 focus:ring-blue-100 transition-all duration-200" <?= $kioskFeatureEnabled ? '' : 'disabled' ?>>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-semibold text-gray-700 mb-2">Kiosk Subheader</label>
                                        <input type="text" name="kiosk_subheader" value="<?= esc($settings['kiosk_subheader'] ?? 'Confirm your appointment in seconds.') ?>" class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:border-blue-500 focus:ring-4 focus:ring-blue-100 transition-all duration-200" <?= $kioskFeatureEnabled ? '' : 'disabled' ?>>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-semibold text-gray-700 mb-2">Default Language</label>
                                        <select name="kiosk_default_language" class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:border-blue-500 focus:ring-4 focus:ring-blue-100 transition-all duration-200" <?= $kioskFeatureEnabled ? '' : 'disabled' ?>>
                                            <option value="ar" <?= (($settings['kiosk_default_language'] ?? 'ar') === 'ar') ? 'selected' : '' ?>>Arabic (Default)</option>
                                            <option value="en" <?= (($settings['kiosk_default_language'] ?? 'ar') === 'en') ? 'selected' : '' ?>>English</option>
                                        </select>
                                    </div>
                                    <div class="md:col-span-2">
                                        <label class="block text-sm font-semibold text-gray-700 mb-2">Footer Note (optional)</label>
                                        <input type="text" name="kiosk_footer_note" value="<?= esc($settings['kiosk_footer_note'] ?? 'Need help? Please ask the reception desk.') ?>" class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:border-blue-500 focus:ring-4 focus:ring-blue-100 transition-all duration-200" <?= $kioskFeatureEnabled ? '' : 'disabled' ?>>
                                    </div>
                                </div>

                                <div class="border-t border-gray-100 pt-6">
                                    <h5 class="text-sm font-bold text-slate-800 mb-3">Kiosk PIN</h5>
                                    <p class="text-xs text-slate-500 mb-4">PIN is required to unlock the kiosk screen. Leave blank to keep the current PIN.</p>
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                        <div>
                                            <label class="block text-sm font-semibold text-gray-700 mb-2">New PIN</label>
                                            <input type="password" inputmode="numeric" name="kiosk_pin" class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:border-blue-500 focus:ring-4 focus:ring-blue-100 transition-all duration-200" placeholder="4 to 8 digits" <?= $kioskFeatureEnabled ? '' : 'disabled' ?>>
                                        </div>
                                        <div>
                                            <label class="block text-sm font-semibold text-gray-700 mb-2">Confirm PIN</label>
                                            <input type="password" inputmode="numeric" name="kiosk_pin_confirm" class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:border-blue-500 focus:ring-4 focus:ring-blue-100 transition-all duration-200" placeholder="Re-enter PIN" <?= $kioskFeatureEnabled ? '' : 'disabled' ?>>
                                        </div>
                                    </div>
                                </div>

                                <div class="flex justify-end">
                                    <button type="submit" class="px-8 py-3 bg-gradient-to-r from-indigo-500 to-blue-600 text-white font-semibold rounded-xl hover:from-indigo-600 hover:to-blue-700 transition-all duration-200 shadow-lg" <?= $kioskFeatureEnabled ? '' : 'disabled' ?>>
                                        <i class="fas fa-save mr-2"></i>Save Kiosk Settings
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- System Preference Tab -->
                <div id="system-tab" class="settings-tab hidden">
                    <div class="bg-white/90 backdrop-blur-xl rounded-2xl shadow-xl shadow-blue-500/10 border border-white/30 overflow-hidden">
                        <div class="bg-gradient-to-r from-green-500 via-emerald-600 to-teal-600 p-8">
                            <div class="flex items-center space-x-4">
                                <div class="w-12 h-12 bg-white/20 rounded-xl flex items-center justify-center backdrop-blur-sm">
                                    <i class="fas fa-cog text-white text-xl"></i>
                                            </div>
                                <div>
                                    <h3 class="text-2xl font-bold text-white">System Preference</h3>
                                    <p class="text-green-100">Configure system settings and preferences</p>
                                    </div>
                                </div>
                            </div>

                        <div class="p-8">
                            <form action="<?= base_url('settings/updateSystem') ?>" method="POST">
                                <?= csrf_field() ?>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <div>
                                        <label class="block text-sm font-semibold text-gray-700 mb-2">Timezone</label>
                                        <select name="timezone" class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:border-green-500 focus:ring-4 focus:ring-green-100 transition-all duration-200" required>
                                            <option value="UTC" <?= ($settings['timezone'] ?? '') == 'UTC' ? 'selected' : '' ?>>UTC</option>
                                            <option value="America/New_York" <?= ($settings['timezone'] ?? '') == 'America/New_York' ? 'selected' : '' ?>>Eastern Time (ET)</option>
                                            <option value="America/Chicago" <?= ($settings['timezone'] ?? '') == 'America/Chicago' ? 'selected' : '' ?>>Central Time (CT)</option>
                                            <option value="America/Denver" <?= ($settings['timezone'] ?? '') == 'America/Denver' ? 'selected' : '' ?>>Mountain Time (MT)</option>
                                            <option value="America/Los_Angeles" <?= ($settings['timezone'] ?? '') == 'America/Los_Angeles' ? 'selected' : '' ?>>Pacific Time (PT)</option>
                                            <option value="America/Anchorage" <?= ($settings['timezone'] ?? '') == 'America/Anchorage' ? 'selected' : '' ?>>Alaska Time (AKT)</option>
                                            <option value="Pacific/Honolulu" <?= ($settings['timezone'] ?? '') == 'Pacific/Honolulu' ? 'selected' : '' ?>>Hawaii Time (HST)</option>
                                            <option value="Europe/London" <?= ($settings['timezone'] ?? '') == 'Europe/London' ? 'selected' : '' ?>>London (GMT/BST)</option>
                                            <option value="Europe/Paris" <?= ($settings['timezone'] ?? '') == 'Europe/Paris' ? 'selected' : '' ?>>Paris (CET/CEST)</option>
                                            <option value="Europe/Berlin" <?= ($settings['timezone'] ?? '') == 'Europe/Berlin' ? 'selected' : '' ?>>Berlin (CET/CEST)</option>
                                            <option value="Europe/Rome" <?= ($settings['timezone'] ?? '') == 'Europe/Rome' ? 'selected' : '' ?>>Rome (CET/CEST)</option>
                                            <option value="Europe/Madrid" <?= ($settings['timezone'] ?? '') == 'Europe/Madrid' ? 'selected' : '' ?>>Madrid (CET/CEST)</option>
                                            <option value="Europe/Amsterdam" <?= ($settings['timezone'] ?? '') == 'Europe/Amsterdam' ? 'selected' : '' ?>>Amsterdam (CET/CEST)</option>
                                            <option value="Europe/Zurich" <?= ($settings['timezone'] ?? '') == 'Europe/Zurich' ? 'selected' : '' ?>>Zurich (CET/CEST)</option>
                                            <option value="Europe/Vienna" <?= ($settings['timezone'] ?? '') == 'Europe/Vienna' ? 'selected' : '' ?>>Vienna (CET/CEST)</option>
                                            <option value="Europe/Stockholm" <?= ($settings['timezone'] ?? '') == 'Europe/Stockholm' ? 'selected' : '' ?>>Stockholm (CET/CEST)</option>
                                            <option value="Europe/Oslo" <?= ($settings['timezone'] ?? '') == 'Europe/Oslo' ? 'selected' : '' ?>>Oslo (CET/CEST)</option>
                                            <option value="Europe/Copenhagen" <?= ($settings['timezone'] ?? '') == 'Europe/Copenhagen' ? 'selected' : '' ?>>Copenhagen (CET/CEST)</option>
                                            <option value="Europe/Helsinki" <?= ($settings['timezone'] ?? '') == 'Europe/Helsinki' ? 'selected' : '' ?>>Helsinki (EET/EEST)</option>
                                            <option value="Europe/Warsaw" <?= ($settings['timezone'] ?? '') == 'Europe/Warsaw' ? 'selected' : '' ?>>Warsaw (CET/CEST)</option>
                                            <option value="Europe/Prague" <?= ($settings['timezone'] ?? '') == 'Europe/Prague' ? 'selected' : '' ?>>Prague (CET/CEST)</option>
                                            <option value="Europe/Budapest" <?= ($settings['timezone'] ?? '') == 'Europe/Budapest' ? 'selected' : '' ?>>Budapest (CET/CEST)</option>
                                            <option value="Europe/Athens" <?= ($settings['timezone'] ?? '') == 'Europe/Athens' ? 'selected' : '' ?>>Athens (EET/EEST)</option>
                                            <option value="Europe/Istanbul" <?= ($settings['timezone'] ?? '') == 'Europe/Istanbul' ? 'selected' : '' ?>>Istanbul (TRT)</option>
                                            <option value="Europe/Moscow" <?= ($settings['timezone'] ?? '') == 'Europe/Moscow' ? 'selected' : '' ?>>Moscow (MSK)</option>
                                            <option value="Asia/Tokyo" <?= ($settings['timezone'] ?? '') == 'Asia/Tokyo' ? 'selected' : '' ?>>Tokyo (JST)</option>
                                            <option value="Asia/Shanghai" <?= ($settings['timezone'] ?? '') == 'Asia/Shanghai' ? 'selected' : '' ?>>Shanghai (CST)</option>
                                            <option value="Asia/Hong_Kong" <?= ($settings['timezone'] ?? '') == 'Asia/Hong_Kong' ? 'selected' : '' ?>>Hong Kong (HKT)</option>
                                            <option value="Asia/Singapore" <?= ($settings['timezone'] ?? '') == 'Asia/Singapore' ? 'selected' : '' ?>>Singapore (SGT)</option>
                                            <option value="Asia/Seoul" <?= ($settings['timezone'] ?? '') == 'Asia/Seoul' ? 'selected' : '' ?>>Seoul (KST)</option>
                                            <option value="Asia/Taipei" <?= ($settings['timezone'] ?? '') == 'Asia/Taipei' ? 'selected' : '' ?>>Taipei (CST)</option>
                                            <option value="Asia/Bangkok" <?= ($settings['timezone'] ?? '') == 'Asia/Bangkok' ? 'selected' : '' ?>>Bangkok (ICT)</option>
                                            <option value="Asia/Jakarta" <?= ($settings['timezone'] ?? '') == 'Asia/Jakarta' ? 'selected' : '' ?>>Jakarta (WIB)</option>
                                            <option value="Asia/Kolkata" <?= ($settings['timezone'] ?? '') == 'Asia/Kolkata' ? 'selected' : '' ?>>Kolkata (IST)</option>
                                            <option value="Asia/Dubai" <?= ($settings['timezone'] ?? '') == 'Asia/Dubai' ? 'selected' : '' ?>>Dubai (GST)</option>
                                            <option value="Asia/Riyadh" <?= ($settings['timezone'] ?? '') == 'Asia/Riyadh' ? 'selected' : '' ?>>Riyadh (AST)</option>
                                            <option value="Asia/Jerusalem" <?= ($settings['timezone'] ?? '') == 'Asia/Jerusalem' ? 'selected' : '' ?>>Jerusalem (IST/IDT)</option>
                                            <option value="Asia/Gaza" <?= ($settings['timezone'] ?? '') == 'Asia/Gaza' ? 'selected' : '' ?>>Gaza (EET/EEST)</option>
                                            <option value="Asia/Tehran" <?= ($settings['timezone'] ?? '') == 'Asia/Tehran' ? 'selected' : '' ?>>Tehran (IRST)</option>
                                            <option value="Asia/Karachi" <?= ($settings['timezone'] ?? '') == 'Asia/Karachi' ? 'selected' : '' ?>>Karachi (PKT)</option>
                                            <option value="Asia/Dhaka" <?= ($settings['timezone'] ?? '') == 'Asia/Dhaka' ? 'selected' : '' ?>>Dhaka (BST)</option>
                                            <option value="Australia/Sydney" <?= ($settings['timezone'] ?? '') == 'Australia/Sydney' ? 'selected' : '' ?>>Sydney (AEST/AEDT)</option>
                                            <option value="Australia/Melbourne" <?= ($settings['timezone'] ?? '') == 'Australia/Melbourne' ? 'selected' : '' ?>>Melbourne (AEST/AEDT)</option>
                                            <option value="Australia/Brisbane" <?= ($settings['timezone'] ?? '') == 'Australia/Brisbane' ? 'selected' : '' ?>>Brisbane (AEST)</option>
                                            <option value="Australia/Perth" <?= ($settings['timezone'] ?? '') == 'Australia/Perth' ? 'selected' : '' ?>>Perth (AWST)</option>
                                            <option value="Australia/Adelaide" <?= ($settings['timezone'] ?? '') == 'Australia/Adelaide' ? 'selected' : '' ?>>Adelaide (ACST/ACDT)</option>
                                            <option value="Australia/Darwin" <?= ($settings['timezone'] ?? '') == 'Australia/Darwin' ? 'selected' : '' ?>>Darwin (ACST)</option>
                                            <option value="Pacific/Auckland" <?= ($settings['timezone'] ?? '') == 'Pacific/Auckland' ? 'selected' : '' ?>>Auckland (NZST/NZDT)</option>
                                            <option value="Pacific/Fiji" <?= ($settings['timezone'] ?? '') == 'Pacific/Fiji' ? 'selected' : '' ?>>Fiji (FJT)</option>
                                            <option value="America/Sao_Paulo" <?= ($settings['timezone'] ?? '') == 'America/Sao_Paulo' ? 'selected' : '' ?>>São Paulo (BRT)</option>
                                            <option value="America/Buenos_Aires" <?= ($settings['timezone'] ?? '') == 'America/Buenos_Aires' ? 'selected' : '' ?>>Buenos Aires (ART)</option>
                                            <option value="America/Lima" <?= ($settings['timezone'] ?? '') == 'America/Lima' ? 'selected' : '' ?>>Lima (PET)</option>
                                            <option value="America/Bogota" <?= ($settings['timezone'] ?? '') == 'America/Bogota' ? 'selected' : '' ?>>Bogotá (COT)</option>
                                            <option value="America/Mexico_City" <?= ($settings['timezone'] ?? '') == 'America/Mexico_City' ? 'selected' : '' ?>>Mexico City (CST/CDT)</option>
                                            <option value="America/Toronto" <?= ($settings['timezone'] ?? '') == 'America/Toronto' ? 'selected' : '' ?>>Toronto (EST/EDT)</option>
                                            <option value="America/Vancouver" <?= ($settings['timezone'] ?? '') == 'America/Vancouver' ? 'selected' : '' ?>>Vancouver (PST/PDT)</option>
                                            <option value="America/Montreal" <?= ($settings['timezone'] ?? '') == 'America/Montreal' ? 'selected' : '' ?>>Montreal (EST/EDT)</option>
                                            <option value="Africa/Cairo" <?= ($settings['timezone'] ?? '') == 'Africa/Cairo' ? 'selected' : '' ?>>Cairo (EET)</option>
                                            <option value="Africa/Johannesburg" <?= ($settings['timezone'] ?? '') == 'Africa/Johannesburg' ? 'selected' : '' ?>>Johannesburg (SAST)</option>
                                            <option value="Africa/Lagos" <?= ($settings['timezone'] ?? '') == 'Africa/Lagos' ? 'selected' : '' ?>>Lagos (WAT)</option>
                                            <option value="Africa/Casablanca" <?= ($settings['timezone'] ?? '') == 'Africa/Casablanca' ? 'selected' : '' ?>>Casablanca (WET)</option>
                                        </select>
                                </div>
                                    <div>
                                        <label class="block text-sm font-semibold text-gray-700 mb-2">Date Format</label>
                                        <select name="date_format" class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:border-green-500 focus:ring-4 focus:ring-green-100 transition-all duration-200" required>
                                            <option value="Y-m-d" <?= ($settings['date_format'] ?? '') == 'Y-m-d' ? 'selected' : '' ?>>YYYY-MM-DD</option>
                                            <option value="m/d/Y" <?= ($settings['date_format'] ?? '') == 'm/d/Y' ? 'selected' : '' ?>>MM/DD/YYYY</option>
                                            <option value="d/m/Y" <?= ($settings['date_format'] ?? '') == 'd/m/Y' ? 'selected' : '' ?>>DD/MM/YYYY</option>
                                            </select>
                                            </div>
                                    <div>
                                        <label class="block text-sm font-semibold text-gray-700 mb-2">Time Format</label>
                                        <select name="time_format" class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:border-green-500 focus:ring-4 focus:ring-green-100 transition-all duration-200" required>
                                            <option value="24" <?= ($settings['time_format'] ?? '') == '24' ? 'selected' : '' ?>>24 Hour</option>
                                            <option value="12" <?= ($settings['time_format'] ?? '') == '12' ? 'selected' : '' ?>>12 Hour</option>
                                            </select>
                                        </div>
                                    <div>
                                        <label class="block text-sm font-semibold text-gray-700 mb-2">Currency</label>
                                        <select name="currency" class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:border-green-500 focus:ring-4 focus:ring-green-100 transition-all duration-200" required>
                                            <option value="ILS" <?= ($settings['currency'] ?? '') == 'ILS' ? 'selected' : '' ?>>ILS - Israeli Shekel (ILS)</option>
                                            <option value="USD" <?= ($settings['currency'] ?? '') == 'USD' ? 'selected' : '' ?>>USD - US Dollar (USD)</option>
                                            <option value="JOD" <?= ($settings['currency'] ?? '') == 'JOD' ? 'selected' : '' ?>>JOD - Jordanian Dinar (JOD)</option>
                                            <option value="EUR" <?= ($settings['currency'] ?? '') == 'EUR' ? 'selected' : '' ?>>EUR - Euro (EUR)</option>
                                            <option value="GBP" <?= ($settings['currency'] ?? '') == 'GBP' ? 'selected' : '' ?>>GBP - British Pound (GBP)</option>
                                        </select>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-semibold text-gray-700 mb-2">Currency Position</label>
                                        <select name="currency_position" class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:border-green-500 focus:ring-4 focus:ring-green-100 transition-all duration-200" required>
                                            <option value="before" <?= ($settings['currency_position'] ?? 'before') == 'before' ? 'selected' : '' ?>>Before Amount (e.g., $100)</option>
                                            <option value="after" <?= ($settings['currency_position'] ?? 'before') == 'after' ? 'selected' : '' ?>>After Amount (e.g., 100$)</option>
                                            </select>
                                        </div>
                                    </div>
                                    
                                <div class="mt-8 flex justify-end">
                                    <button type="submit" class="px-8 py-3 bg-gradient-to-r from-green-500 to-emerald-600 text-white font-semibold rounded-xl hover:from-green-600 hover:to-emerald-700 transition-all duration-200 shadow-lg hover:shadow-xl">
                                        <i class="fas fa-save mr-2"></i>Save Changes
                                    </button>
                                            </div>
                            </form>
                                        </div>
                                    </div>
                                </div>

                <!-- Working Hours Tab -->
                <div id="hours-tab" class="settings-tab hidden">
                    <div class="bg-white/90 backdrop-blur-xl rounded-2xl shadow-xl shadow-blue-500/10 border border-white/30 overflow-hidden">
                        <div class="bg-gradient-to-r from-orange-500 via-amber-600 to-yellow-600 p-8">
                            <div class="flex items-center space-x-4">
                                <div class="w-12 h-12 bg-white/20 rounded-xl flex items-center justify-center backdrop-blur-sm">
                                    <i class="fas fa-clock text-white text-xl"></i>
                                </div>
                                <div>
                                    <h3 class="text-2xl font-bold text-white">Working Hours</h3>
                                    <p class="text-white !important" style="color: white !important;">Set your clinic's operating hours and days</p>
                                            </div>
                                        </div>
                                    </div>
                                    
                        <div class="p-8">
                            <form action="<?= base_url('settings/updateWorkingHours') ?>" method="POST">
                                <?= csrf_field() ?>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <div>
                                        <label class="block text-sm font-semibold text-gray-700 mb-2">Start Time</label>
                                        <input type="time" name="working_hours_start" data-clinic-time="1" value="<?= $settings['working_hours_start'] ?? '09:00' ?>" class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:border-orange-500 focus:ring-4 focus:ring-orange-100 transition-all duration-200" required>
                                            </div>
                                    <div>
                                        <label class="block text-sm font-semibold text-gray-700 mb-2">End Time</label>
                                        <input type="time" name="working_hours_end" data-clinic-time="1" value="<?= $settings['working_hours_end'] ?? '17:00' ?>" class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:border-orange-500 focus:ring-4 focus:ring-orange-100 transition-all duration-200" required>
                                        </div>
                                    <div class="md:col-span-2">
                                        <label class="block text-sm font-semibold text-gray-700 mb-2">Working Days</label>
                                        <div class="grid grid-cols-7 gap-2">
                                                <?php
                                            $workingDays = $settings['working_days'] ?? ['monday', 'tuesday', 'wednesday', 'thursday', 'friday'];
                                                $days = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'];
                                            $dayLabels = ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'];
                                            foreach ($days as $index => $day): ?>
                                                <label class="flex flex-col items-center p-3 border-2 border-gray-200 rounded-xl cursor-pointer hover:border-orange-300 transition-all duration-200 <?= in_array($day, $workingDays) ? 'border-orange-500 bg-orange-50' : '' ?>">
                                                    <input type="checkbox" name="working_days[]" value="<?= $day ?>" <?= in_array($day, $workingDays) ? 'checked' : '' ?> class="sr-only">
                                                    <span class="text-sm font-medium text-gray-700"><?= $dayLabels[$index] ?></span>
                                                    </label>
                                                <?php endforeach; ?>
                                            </div>
                                        </div>
                                        <div>
                                            <label class="block text-sm font-semibold text-gray-700 mb-2">Appointment Duration (minutes)</label>
                                            <input type="number" name="appointment_duration" value="<?= $settings['appointment_duration'] ?? 30 ?>" min="15" max="480" class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:border-purple-500 focus:ring-4 focus:ring-purple-100 transition-all duration-200" required>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-semibold text-gray-700 mb-2">Number of Chairs</label>
                                        <?php $maxChairCount = max(1, min(3, (int) ($max_booking_chair_count ?? 3))); ?>
                                        <input type="number" name="booking_chair_count" id="bookingChairCount" value="<?= min($maxChairCount, max(1, (int)($settings['booking_chair_count'] ?? 1))) ?>" min="1" max="<?= $maxChairCount ?>" class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:border-purple-500 focus:ring-4 focus:ring-purple-100 transition-all duration-200" required>
                                        <p class="mt-2 text-xs text-gray-500">Allows parallel bookings up to this chair capacity (max <?= esc((string) $maxChairCount) ?>).</p>
                                    </div>
                                    <div class="md:col-span-2">
                                        <label class="block text-sm font-semibold text-gray-700 mb-2">Chair / Room Names</label>
                                        <?php
                                            $chairNames = $settings['booking_chair_names'] ?? ['Chair 1'];
                                            if (!is_array($chairNames)) {
                                                $chairNames = ['Chair 1'];
                                            }
                                        ?>
                                        <div id="chairNameFields" class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                            <?php for ($i = 0; $i < $maxChairCount; $i++): ?>
                                                <?php $defaultName = $chairNames[$i] ?? ('Chair ' . ($i + 1)); ?>
                                                <div class="chair-name-field" data-chair-index="<?= $i + 1 ?>">
                                                    <label class="block text-xs font-semibold text-gray-600 mb-2">Chair <?= $i + 1 ?> Name</label>
                                                    <input type="text" name="booking_chair_names[]" value="<?= esc($defaultName) ?>" maxlength="100" class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:border-purple-500 focus:ring-4 focus:ring-purple-100 transition-all duration-200" placeholder="Chair <?= $i + 1 ?>">
                                                </div>
                                            <?php endfor; ?>
                                        </div>
                                        <p class="mt-2 text-xs text-gray-500">Example: Dr. Bassam Room. Only active chair fields are saved.</p>
                                    </div>
                                </div>

                                <div class="mt-8 flex justify-end">
                                    <button type="submit" class="px-8 py-3 bg-gradient-to-r from-orange-500 to-amber-600 text-white font-semibold rounded-xl hover:from-orange-600 hover:to-amber-700 transition-all duration-200 shadow-lg hover:shadow-xl">
                                        <i class="fas fa-save mr-2"></i>Save Changes
                                </button>
                            </div>
            </form>
        </div>
    </div>
            </div>

                <!-- DB Backup Tab -->
                <div id="backup-tab" class="settings-tab hidden">
                    <div class="bg-white/90 backdrop-blur-xl rounded-2xl shadow-xl shadow-blue-500/10 border border-white/30 overflow-hidden">
                        <div class="bg-gradient-to-r from-purple-500 via-indigo-600 to-blue-600 p-8">
                        <div class="flex items-center space-x-4">
                                <div class="w-12 h-12 bg-white/20 rounded-xl flex items-center justify-center backdrop-blur-sm">
                                    <i class="fas fa-database text-white text-xl"></i>
                            </div>
                            <div class="flex-1">
                                    <h3 class="text-2xl font-bold text-white">Database Backup</h3>
                                    <p class="text-purple-100">Manage database backups and system restore</p>
                            </div>
                            <?php if ($demo): ?>
                            <div class="bg-yellow-500/20 border border-yellow-400/30 rounded-xl px-4 py-2">
                                <div class="flex items-center space-x-2">
                                    <i class="fas fa-exclamation-triangle text-yellow-300 text-sm"></i>
                                    <span class="text-yellow-200 text-sm font-medium">Demo Mode - Disabled</span>
                                </div>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <div class="p-8">
                        <?php if ($demo): ?>
                        <!-- Demo Mode Notice -->
                        <div class="mb-6 p-4 bg-yellow-50 border border-yellow-200 rounded-xl">
                            <div class="flex items-start space-x-3">
                                <div class="flex-shrink-0">
                                    <i class="fas fa-exclamation-triangle text-yellow-600 text-lg"></i>
                                </div>
                                <div>
                                    <h4 class="text-sm font-bold text-yellow-800">Demo Mode Active</h4>
                                    <p class="text-sm text-yellow-700 mt-1">Database backup and restore functionality is disabled in demo mode for security reasons.</p>
                                </div>
                            </div>
                        </div>
                        <?php endif; ?>
                        
                        <div class="space-y-8">
                            <!-- Create Backup Section -->
                            <div class="space-y-6">
                                <div class="flex items-center space-x-3 mb-6">
                                        <div class="w-1 h-8 bg-gradient-to-b from-purple-500 to-indigo-600 rounded-full"></div>
                                    <h4 class="text-xl font-bold text-gray-800">Create New Backup</h4>
                                </div>
                                
                                <div class="relative group">
                                        <div class="absolute inset-0 bg-gradient-to-r from-purple-500/10 to-indigo-500/10 rounded-2xl blur opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
                                        <div class="relative bg-gradient-to-r from-purple-50 to-indigo-50 rounded-2xl border-2 border-purple-200 p-8 group-hover:border-purple-300 transition-all duration-300">
                                        <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between space-y-4 lg:space-y-0">
                                            <div class="flex items-center space-x-4">
                                                <div class="relative">
                                                        <div class="absolute inset-0 bg-gradient-to-r from-purple-500 to-indigo-600 rounded-xl blur opacity-75"></div>
                                                        <div class="relative w-16 h-16 bg-gradient-to-r from-purple-500 to-indigo-600 rounded-xl flex items-center justify-center shadow-lg">
                                                        <i class="fas fa-database text-white text-2xl"></i>
                                                    </div>
                                                </div>
                                                <div>
                                                    <h5 class="text-2xl font-bold text-gray-800 mb-2">Create New Backup</h5>
                                                    <p class="text-gray-600 text-lg">Create a complete backup of your database</p>
                                                    <div class="flex items-center space-x-2 mt-2">
                                                            <div class="w-2 h-2 bg-purple-500 rounded-full animate-pulse"></div>
                                                        <span class="text-sm text-gray-500 font-medium">Ready to backup</span>
                                                    </div>
                                                </div>
                                            </div>
                                            
                                            <?php if ($demo): ?>
                                            <button type="button" disabled class="group/btn relative inline-flex items-center px-8 py-4 bg-gray-400 text-gray-600 text-lg font-bold rounded-2xl shadow-xl cursor-not-allowed opacity-60">
                                                <i class="fas fa-ban w-5 h-5 mr-3 relative z-10"></i>
                                                <span class="relative z-10">Create Backup (Disabled in Demo)</span>
                                            </button>
                                            <?php else: ?>
                                            <form action="<?= base_url('settings/create-backup') ?>" method="POST" class="inline">
                                                    <?= csrf_field() ?>
                                                    <button type="submit" class="group/btn relative inline-flex items-center px-8 py-4 bg-gradient-to-r from-purple-500 to-indigo-600 text-white text-lg font-bold rounded-2xl shadow-xl shadow-purple-500/25 hover:shadow-2xl hover:shadow-purple-500/30 focus:outline-none focus:ring-4 focus:ring-purple-500/20 transition-all duration-300 hover:scale-105">
                                                        <div class="absolute inset-0 bg-gradient-to-r from-purple-600 to-indigo-700 rounded-2xl blur opacity-0 group-hover/btn:opacity-100 transition-opacity duration-300"></div>
                                                    <i class="fas fa-download w-5 h-5 mr-3 relative z-10"></i>
                                                    <span class="relative z-10">Create Backup</span>
                                                    <div class="absolute -top-1 -right-1 w-4 h-4 bg-white/30 rounded-full animate-pulse"></div>
                                                </button>
                                            </form>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Available Backups Section -->
                            <div class="space-y-6">
                                <div class="flex items-center space-x-3 mb-6">
                                    <div class="w-1 h-8 bg-gradient-to-b from-teal-500 to-cyan-600 rounded-full"></div>
                                    <h4 class="text-xl font-bold text-gray-800">Available Backups</h4>
                                </div>
                                
                                <div class="space-y-4">
                                    <?php if (!empty($backups)): ?>
                                        <?php foreach ($backups as $backup): ?>
                                            <div class="group relative bg-gradient-to-r from-gray-50 to-slate-50 rounded-2xl border-2 border-gray-200 hover:border-gray-300 transition-all duration-300 hover:shadow-lg">
                                                <div class="p-6">
                                                    <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between space-y-4 lg:space-y-0">
                                                        <div class="flex items-center space-x-4">
                                                            <div class="relative">
                                                                <div class="absolute inset-0 bg-gradient-to-r from-blue-500 to-indigo-600 rounded-xl blur opacity-75 group-hover:opacity-100 transition-opacity duration-300"></div>
                                                                <div class="relative w-12 h-12 bg-gradient-to-r from-blue-500 to-indigo-600 rounded-xl flex items-center justify-center shadow-lg">
                                                                    <i class="fas fa-file-archive text-white text-lg"></i>
                                                                </div>
                                                            </div>
                                                            <div>
                                                                <h5 class="text-lg font-bold text-gray-800"><?= $backup['name'] ?></h5>
                                                                <p class="text-sm text-gray-600"><?= $backup['size'] ?> • <?= $backup['created_at'] ?></p>
                                                            </div>
                                                        </div>
                                                        
                                                        <div class="flex space-x-3">
                                                            <?php if ($demo): ?>
                                                            <button type="button" disabled class="group/btn relative inline-flex items-center px-6 py-3 bg-gray-300 text-gray-500 text-sm font-bold rounded-xl border border-gray-300 cursor-not-allowed opacity-60">
                                                                <i class="fas fa-ban w-4 h-4 mr-2 relative z-10"></i>
                                                                <span class="relative z-10">Download (Disabled)</span>
                                                            </button>
                                                            
                                                            <button type="button" disabled class="group/btn relative inline-flex items-center px-6 py-3 bg-gray-400 text-gray-500 text-sm font-bold rounded-xl shadow-lg cursor-not-allowed opacity-60">
                                                                <i class="fas fa-ban w-4 h-4 mr-2 relative z-10"></i>
                                                                <span class="relative z-10">Restore (Disabled)</span>
                                                            </button>
                                                            <?php else: ?>
                                                            <button onclick="downloadBackup('<?= $backup['name'] ?>')" class="group/btn relative inline-flex items-center px-6 py-3 bg-gradient-to-r from-gray-200 to-gray-300 hover:from-gray-300 hover:to-gray-400 text-gray-700 hover:text-gray-800 text-sm font-bold rounded-xl border border-gray-300 hover:border-gray-400 transition-all duration-300 hover:scale-105">
                                                                <div class="absolute inset-0 bg-gradient-to-r from-gray-300 to-gray-400 rounded-xl blur opacity-0 group-hover/btn:opacity-100 transition-opacity duration-300"></div>
                                                                <i class="fas fa-download w-4 h-4 mr-2 relative z-10"></i>
                                                                <span class="relative z-10">Download</span>
                                                            </button>
                                                            
                                                            <button onclick="restoreBackup('<?= $backup['name'] ?>')" class="group/btn relative inline-flex items-center px-6 py-3 bg-gradient-to-r from-blue-500 to-indigo-600 hover:from-blue-600 hover:to-indigo-700 text-white text-sm font-bold rounded-xl shadow-lg shadow-blue-500/25 hover:shadow-xl hover:shadow-blue-500/30 transition-all duration-300 hover:scale-105">
                                                                <div class="absolute inset-0 bg-gradient-to-r from-blue-600 to-indigo-700 rounded-xl blur opacity-0 group-hover/btn:opacity-100 transition-opacity duration-300"></div>
                                                                <i class="fas fa-upload w-4 h-4 mr-2 relative z-10"></i>
                                                                <span class="relative z-10">Restore</span>
                                                            </button>
                                                            <?php endif; ?>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <div class="text-center py-12">
                                            <div class="relative mx-auto w-24 h-24 mb-6">
                                                <div class="absolute inset-0 bg-gradient-to-r from-gray-400 to-gray-500 rounded-full blur opacity-75"></div>
                                                <div class="relative w-24 h-24 bg-gradient-to-r from-gray-400 to-gray-500 rounded-full flex items-center justify-center shadow-lg">
                                                    <i class="fas fa-database text-white text-3xl"></i>
                                                </div>
                                            </div>
                                            <h5 class="text-xl font-bold text-gray-600 mb-2">No Backups Available</h5>
                                            <p class="text-gray-500">Create your first backup to get started</p>
                                        </div>
                                    <?php endif; ?>
                                                        </div>
                                                    </div>
                                    
                                                    <!-- Export Retention Tab (Superadmin Only) -->
                                                    <?php if (session()->get('global_mode')): ?>
                                                    <div id="retention-tab" class="settings-tab hidden">
                                                        <div class="bg-white/90 backdrop-blur-xl rounded-2xl shadow-xl shadow-blue-500/10 border border-white/30 overflow-hidden">
                                                            <div class="bg-gradient-to-r from-red-500 via-rose-600 to-pink-600 p-8">
                                                                <div class="flex items-center space-x-4">
                                                                    <div class="w-12 h-12 bg-white/20 rounded-xl flex items-center justify-center backdrop-blur-sm">
                                                                        <i class="fas fa-history text-white text-xl"></i>
                                                                    </div>
                                                                    <div>
                                                                        <h3 class="text-2xl font-bold text-white">Export Retention Policy</h3>
                                                                        <p class="text-red-100">Configure how long generated exports and reports are kept</p>
                                                                    </div>
                                                                </div>
                                                            </div>
                                    
                                                            <div class="p-8">
                                                                <form action="<?= base_url('settings/updateRetention') ?>" method="POST">
                                                                    <?= csrf_field() ?>
                                                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                                                                        <div>
                                                                            <label class="block text-sm font-semibold text-gray-700 mb-2">Retention Mode</label>
                                                                            <select name="retention_mode" onchange="toggleRetentionOptions(this.value)" class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:border-red-500 focus:ring-4 focus:ring-red-100 transition-all duration-200" required>
                                                                                <option value="latest" <?= ($settings['retention_mode'] ?? 'latest') == 'latest' ? 'selected' : '' ?>>Keep Only Latest (Default)</option>
                                                                                <option value="keep_last_n" <?= ($settings['retention_mode'] ?? '') == 'keep_last_n' ? 'selected' : '' ?>>Keep Last N Exports</option>
                                                                                <option value="keep_x_days" <?= ($settings['retention_mode'] ?? '') == 'keep_x_days' ? 'selected' : '' ?>>Keep for X Days</option>
                                                                            </select>
                                                                            <p class="text-xs text-gray-500 mt-2">Determines the rule for superseding previous exports of the same type.</p>
                                                                        </div>
                                    
                                                                        <div id="retention-n-group" class="<?= ($settings['retention_mode'] ?? '') == 'keep_last_n' ? '' : 'hidden' ?>">
                                                                            <label class="block text-sm font-semibold text-gray-700 mb-2">Number of Exports to Keep (N)</label>
                                                                            <input type="number" name="retention_last_n" value="<?= $settings['retention_last_n'] ?? 5 ?>" min="1" max="100" class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:border-red-500 focus:ring-4 focus:ring-red-100 transition-all duration-200">
                                                                        </div>
                                    
                                                                        <div id="retention-days-group" class="<?= ($settings['retention_mode'] ?? '') == 'keep_x_days' ? '' : 'hidden' ?>">
                                                                            <label class="block text-sm font-semibold text-gray-700 mb-2">Number of Days to Keep (X)</label>
                                                                            <input type="number" name="retention_days" value="<?= $settings['retention_days'] ?? 30 ?>" min="1" max="365" class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:border-red-500 focus:ring-4 focus:ring-red-100 transition-all duration-200">
                                                                        </div>
                                                                    </div>
                                    
                                                                    <div class="mt-8 flex justify-end">
                                                                        <button type="submit" class="px-8 py-3 bg-gradient-to-r from-red-500 to-rose-600 text-white font-semibold rounded-xl hover:from-red-600 hover:to-rose-700 transition-all duration-200 shadow-lg hover:shadow-xl">
                                                                            <i class="fas fa-save mr-2"></i>Update Policy
                                                                        </button>
                                                                    </div>
                                                                </form>
                                    
                                                                <div class="mt-12 pt-8 border-t border-gray-100">
                                                                    <h4 class="text-lg font-bold text-gray-800 mb-4">Maintenance Operations</h4>
                                                                    <div class="bg-gray-50 rounded-2xl p-6 border-2 border-gray-100">
                                                                        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                                                                            <div>
                                                                                <h5 class="font-bold text-gray-800">Physical Cleanup</h5>
                                                                                <p class="text-sm text-gray-600">Permanently delete files from disk that have been soft-deleted by retention policies.</p>
                                                                            </div>
                                                                            <form action="<?= base_url('settings/pruneExports') ?>" method="POST">
                                                                                <?= csrf_field() ?>
                                                                                <button type="submit" onclick="return confirm('Are you sure you want to permanently delete all soft-deleted export files from disk? This cannot be undone.')" class="px-6 py-2 bg-white border-2 border-red-200 text-red-600 font-bold rounded-xl hover:bg-red-50 transition-all">
                                                                                    <i class="fas fa-broom mr-2"></i>Prune Disk Files
                                                                                </button>
                                                                            </form>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                <!-- Export Retention Tab (Superadmin Only) -->
                <?php if (session()->get('global_mode')): ?>
                <div id="retention-tab" class="settings-tab hidden">
                    <div class="bg-white/90 backdrop-blur-xl rounded-2xl shadow-xl shadow-blue-500/10 border border-white/30 overflow-hidden">
                        <div class="bg-gradient-to-r from-red-500 via-rose-600 to-pink-600 p-8">
                            <div class="flex items-center space-x-4">
                                <div class="w-12 h-12 bg-white/20 rounded-xl flex items-center justify-center backdrop-blur-sm">
                                    <i class="fas fa-history text-white text-xl"></i>
                                </div>
                                <div>
                                    <h3 class="text-2xl font-bold text-white">Export Retention Policy</h3>
                                    <p class="text-red-100">Global policy for persisting and pruning export artifacts</p>
                                </div>
                            </div>
                        </div>

                        <div class="p-8">
                            <form action="<?= base_url('settings/updateRetention') ?>" method="POST">
                                <?= csrf_field() ?>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                                    <div class="md:col-span-2">
                                        <label class="block text-sm font-semibold text-gray-700 mb-4">Retention Mode</label>
                                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                            <label class="relative flex flex-col p-4 border-2 rounded-xl cursor-pointer hover:bg-gray-50 transition-all <?= ($settings['retention_mode'] ?? 'latest') == 'latest' ? 'border-red-500 bg-red-50' : 'border-gray-200' ?>">
                                                <input type="radio" name="retention_mode" value="latest" class="sr-only" <?= ($settings['retention_mode'] ?? 'latest') == 'latest' ? 'checked' : '' ?>>
                                                <span class="font-bold text-gray-900">Latest Only</span>
                                                <span class="text-xs text-gray-500 mt-1">Keep only the most recent export per purpose.</span>
                                            </label>
                                            <label class="relative flex flex-col p-4 border-2 rounded-xl cursor-pointer hover:bg-gray-50 transition-all <?= ($settings['retention_mode'] ?? '') == 'keep_last_n' ? 'border-red-500 bg-red-50' : 'border-gray-200' ?>">
                                                <input type="radio" name="retention_mode" value="keep_last_n" class="sr-only" <?= ($settings['retention_mode'] ?? '') == 'keep_last_n' ? 'checked' : '' ?>>
                                                <span class="font-bold text-gray-900">Keep Last N</span>
                                                <span class="text-xs text-gray-500 mt-1">Keep a specific number of previous exports.</span>
                                            </label>
                                            <label class="relative flex flex-col p-4 border-2 rounded-xl cursor-pointer hover:bg-gray-50 transition-all <?= ($settings['retention_mode'] ?? '') == 'keep_x_days' ? 'border-red-500 bg-red-50' : 'border-gray-200' ?>">
                                                <input type="radio" name="retention_mode" value="keep_x_days" class="sr-only" <?= ($settings['retention_mode'] ?? '') == 'keep_x_days' ? 'checked' : '' ?>>
                                                <span class="font-bold text-gray-900">Time Based</span>
                                                <span class="text-xs text-gray-500 mt-1">Keep exports for a set number of days.</span>
                                            </label>
                                        </div>
                                    </div>

                                    <div id="n-config" class="<?= ($settings['retention_mode'] ?? 'latest') == 'keep_last_n' ? '' : 'hidden' ?>">
                                        <label class="block text-sm font-semibold text-gray-700 mb-2">Number of exports to keep (N)</label>
                                        <input type="number" name="retention_last_n" value="<?= $settings['retention_last_n'] ?? 5 ?>" min="1" max="100" class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:border-red-500 focus:ring-4 focus:ring-red-100 transition-all">
                                    </div>

                                    <div id="days-config" class="<?= ($settings['retention_mode'] ?? 'latest') == 'keep_x_days' ? '' : 'hidden' ?>">
                                        <label class="block text-sm font-semibold text-gray-700 mb-2">Number of days to keep</label>
                                        <input type="number" name="retention_days" value="<?= $settings['retention_days'] ?? 30 ?>" min="1" max="365" class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:border-red-500 focus:ring-4 focus:ring-red-100 transition-all">
                                    </div>
                                </div>

                                <div class="mt-8 pt-8 border-t border-gray-100 flex items-center justify-between">
                                    <div class="flex items-center space-x-4">
                                        <button type="button" onclick="pruneExports()" class="px-6 py-3 bg-white border-2 border-red-200 text-red-600 font-bold rounded-xl hover:bg-red-50 transition-all">
                                            <i class="fas fa-broom mr-2"></i>Prune Exports Now
                                        </button>
                                        <p class="text-xs text-gray-500 max-w-xs italic">
                                            Pruning will physically delete files from disk for all soft-deleted or expired attachments.
                                        </p>
                                    </div>
                                    <button type="submit" class="px-8 py-3 bg-gradient-to-r from-red-500 to-rose-600 text-white font-semibold rounded-xl hover:from-red-600 hover:to-rose-700 transition-all shadow-lg">
                                        <i class="fas fa-save mr-2"></i>Save Policy
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<script>
function showSettingsTab(tabName) {
    console.log('Switching to settings tab:', tabName);
    
    // Hide all tab contents
    document.querySelectorAll('.settings-tab').forEach(tab => {
        console.log('Hiding tab:', tab.id);
        tab.classList.add('hidden');
    });
    
    // Reset all tab buttons to inactive state
    document.querySelectorAll('[id^="tab-"]').forEach(btn => {
        btn.classList.remove('bg-gradient-to-r', 'from-blue-500', 'to-blue-600', 'text-white', 'shadow-lg');
        btn.classList.add('text-gray-700', 'hover:bg-gray-100', 'hover:text-gray-900');
    });
    
    // Show selected tab
    const targetTab = document.getElementById(tabName + '-tab');
    console.log('Target tab element:', targetTab);
    if (targetTab) {
        targetTab.classList.remove('hidden');
        console.log('Tab shown successfully');
    } else {
        console.error('Tab element not found:', tabName + '-tab');
    }
    
    // Add active styling to clicked button
    const activeButton = document.getElementById('tab-' + tabName);
    if (activeButton) {
        activeButton.classList.remove('text-gray-700', 'hover:bg-gray-100', 'hover:text-gray-900');
        activeButton.classList.add('bg-gradient-to-r', 'from-blue-500', 'to-blue-600', 'text-white', 'shadow-lg');
    }
}

function downloadBackup(filename) {
    // Create a more modern confirmation dialog
    const modal = document.createElement('div');
    modal.className = 'fixed inset-0 bg-black/50 backdrop-blur-sm flex items-center justify-center z-50';
    
    const card = document.createElement('div');
    card.className = 'bg-white rounded-2xl shadow-2xl p-8 max-w-md mx-4';
    
    const textCenter = document.createElement('div');
    textCenter.className = 'text-center';
    
    const iconContainer = document.createElement('div');
    iconContainer.className = 'w-16 h-16 bg-gradient-to-r from-blue-500 to-indigo-600 rounded-full flex items-center justify-center mx-auto mb-4';
    
    const icon = document.createElement('i');
    icon.className = 'fas fa-download text-white text-2xl';
    iconContainer.appendChild(icon);
    
    const title = document.createElement('h3');
    title.className = 'text-xl font-bold text-gray-800 mb-2';
    title.textContent = 'Download Backup';
    
    const message = document.createElement('p');
    message.className = 'text-gray-600 mb-6';
    message.textContent = 'Are you sure you want to download "' + filename + '"?';
    
    const buttonContainer = document.createElement('div');
    buttonContainer.className = 'flex space-x-3';
    
    const cancelBtn = document.createElement('button');
    cancelBtn.className = 'px-6 py-3 bg-gray-200 text-gray-700 rounded-xl hover:bg-gray-300 transition-colors';
    cancelBtn.textContent = 'Cancel';
    cancelBtn.onclick = function() {
        modal.remove();
    };
    
    const downloadBtn = document.createElement('button');
    downloadBtn.className = 'px-6 py-3 bg-gradient-to-r from-blue-500 to-indigo-600 text-white rounded-xl hover:from-blue-600 hover:to-indigo-700 transition-all';
    downloadBtn.textContent = 'Download';
    downloadBtn.onclick = function() {
        confirmDownload(filename);
        modal.remove();
    };
    
    buttonContainer.appendChild(cancelBtn);
    buttonContainer.appendChild(downloadBtn);
    
    textCenter.appendChild(iconContainer);
    textCenter.appendChild(title);
    textCenter.appendChild(message);
    textCenter.appendChild(buttonContainer);
    
    card.appendChild(textCenter);
    modal.appendChild(card);
    
    document.body.appendChild(modal);
}

function confirmDownload(filename) {
    // Implementation for downloading backup
    window.location.href = `<?= base_url('settings/download-backup/') ?>${filename}`;
}

function restoreBackup(filename) {
    // Create a more modern confirmation dialog
    const modal = document.createElement('div');
    modal.className = 'fixed inset-0 bg-black/50 backdrop-blur-sm flex items-center justify-center z-50';
    
    const card = document.createElement('div');
    card.className = 'bg-white rounded-2xl shadow-2xl p-8 max-w-md mx-4';
    
    const textCenter = document.createElement('div');
    textCenter.className = 'text-center';
    
    const iconContainer = document.createElement('div');
    iconContainer.className = 'w-16 h-16 bg-gradient-to-r from-red-500 to-pink-600 rounded-full flex items-center justify-center mx-auto mb-4';
    
    const icon = document.createElement('i');
    icon.className = 'fas fa-exclamation-triangle text-white text-2xl';
    iconContainer.appendChild(icon);
    
    const title = document.createElement('h3');
    title.className = 'text-xl font-bold text-gray-800 mb-2';
    title.textContent = 'Restore Backup';
    
    const message = document.createElement('p');
    message.className = 'text-gray-600 mb-2';
    message.textContent = 'Are you sure you want to restore from "' + filename + '"?';
    
    const warning = document.createElement('p');
    warning.className = 'text-red-600 text-sm font-medium mb-6';
    warning.textContent = '⚠️ This will overwrite your current data!';
    
    const buttonContainer = document.createElement('div');
    buttonContainer.className = 'flex space-x-3';
    
    const cancelBtn = document.createElement('button');
    cancelBtn.className = 'px-6 py-3 bg-gray-200 text-gray-700 rounded-xl hover:bg-gray-300 transition-colors';
    cancelBtn.textContent = 'Cancel';
    cancelBtn.onclick = function() {
        modal.remove();
    };
    
    const restoreBtn = document.createElement('button');
    restoreBtn.className = 'px-6 py-3 bg-gradient-to-r from-red-500 to-pink-600 text-white rounded-xl hover:from-red-600 hover:to-pink-700 transition-all';
    restoreBtn.textContent = 'Restore';
    restoreBtn.onclick = function() {
        confirmRestore(filename);
        modal.remove();
    };
    
    buttonContainer.appendChild(cancelBtn);
    buttonContainer.appendChild(restoreBtn);
    
    textCenter.appendChild(iconContainer);
    textCenter.appendChild(title);
    textCenter.appendChild(message);
    textCenter.appendChild(warning);
    textCenter.appendChild(buttonContainer);
    
    card.appendChild(textCenter);
    modal.appendChild(card);
    
    document.body.appendChild(modal);
}

function confirmRestore(filename) {
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = '<?= base_url('settings/restore') ?>';
    
    const csrfInput = document.createElement('input');
    csrfInput.type = 'hidden';
    csrfInput.name = '<?= csrf_token() ?>';
    csrfInput.value = '<?= csrf_hash() ?>';
    form.appendChild(csrfInput);

    const input = document.createElement('input');
    input.type = 'hidden';
    input.name = 'backup_file';
    input.value = filename;
    
    form.appendChild(input);
    document.body.appendChild(form);
    form.submit();
}

// Retention Tab Logic
document.addEventListener('change', function(e) {
    if (e.target && e.target.name === 'retention_mode') {
        const mode = e.target.value;
        const nConfig = document.getElementById('n-config');
        const daysConfig = document.getElementById('days-config');
        
        // Hide all configs
        nConfig?.classList.add('hidden');
        daysConfig?.classList.add('hidden');
        
        // Show target config
        if (mode === 'keep_last_n') nConfig?.classList.remove('hidden');
        if (mode === 'keep_x_days') daysConfig?.classList.remove('hidden');
        
        // Update visual selection
        document.querySelectorAll('input[name="retention_mode"]').forEach(radio => {
            const label = radio.closest('label');
            if (radio.checked) {
                label.classList.add('border-red-500', 'bg-red-50');
                label.classList.remove('border-gray-200');
            } else {
                label.classList.remove('border-red-500', 'bg-red-50');
                label.classList.add('border-gray-200');
            }
        });
    }
});

function pruneExports() {
    if (!confirm('Are you sure you want to physically delete all expired/superseded export files from disk? This cannot be undone.')) {
        return;
    }
    
    const btn = event.currentTarget;
    const originalText = btn.textContent;
    const originalIcon = btn.querySelector('i');
    
    // Create spinner
    const spinner = document.createElement('i');
    spinner.className = 'fas fa-spinner fa-spin mr-2';
    
    btn.disabled = true;
    btn.replaceChildren(spinner, document.createTextNode('Pruning...'));

    fetch('<?= base_url('settings/prune-exports') ?>', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': csrfToken,
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showNotification('Success', data.message, 'success');
        } else {
            showNotification('Error', data.message || 'Pruning failed', 'error');
        }
    })
    .catch(err => {
        showNotification('Error', 'An unexpected error occurred.', 'error');
        console.error(err);
    })
    .finally(() => {
        btn.disabled = false;
        btn.replaceChildren();
        if (originalIcon) btn.appendChild(originalIcon);
        btn.appendChild(document.createTextNode(originalText));
    });
}

function copyKioskLink() {
    const input = document.getElementById('kioskLinkInput');
    if (!input || !input.value) {
        if (window.showToast) {
            window.showToast('error', 'Error', 'Kiosk link is not available yet.');
        }
        return;
    }

    if (navigator.clipboard && navigator.clipboard.writeText) {
        navigator.clipboard.writeText(input.value)
            .then(() => {
                if (window.showToast) {
                    window.showToast('success', 'Success', 'Kiosk link copied to clipboard.');
                }
            })
            .catch(() => {
                if (window.showToast) {
                    window.showToast('error', 'Error', 'Unable to copy kiosk link.');
                }
            });
        return;
    }

    input.removeAttribute('readonly');
    input.select();
    input.setSelectionRange(0, input.value.length);
    document.execCommand('copy');
    input.setAttribute('readonly', 'readonly');
    window.getSelection()?.removeAllRanges();
    if (window.showToast) {
        window.showToast('success', 'Success', 'Kiosk link copied to clipboard.');
    }
}

// Initialize Select2 for all select elements
document.addEventListener('DOMContentLoaded', function() {
    const kioskToggle = document.querySelector('.kiosk-toggle');
    if (kioskToggle) {
        const label = kioskToggle.closest('label');
        const knob = label?.querySelector('.kiosk-toggle-knob');
        const track = label?.querySelector('.kiosk-toggle-track');
        const kioskForm = kioskToggle.closest('form');
        const restoreToggle = () => {
            kioskToggle.checked = !kioskToggle.checked;
            if (kioskToggle.checked) {
                knob?.classList.add('translate-x-5', 'bg-blue-500');
                track?.classList.remove('bg-gray-200');
                track?.classList.add('bg-blue-500');
            } else {
                knob?.classList.remove('translate-x-5', 'bg-blue-500');
                track?.classList.remove('bg-blue-500');
                track?.classList.add('bg-gray-200');
            }
        };

        kioskToggle.addEventListener('change', function() {
            if (!knob) return;
            if (kioskToggle.checked) {
                knob.classList.add('translate-x-5', 'bg-blue-500');
                track?.classList.remove('bg-gray-200');
                track?.classList.add('bg-blue-500');
            } else {
                knob.classList.remove('translate-x-5', 'bg-blue-500');
                track?.classList.remove('bg-blue-500');
                track?.classList.add('bg-gray-200');
            }

            if (!kioskForm) return;
            const formData = new FormData(kioskForm);
            formData.set('kiosk_enabled', kioskToggle.checked ? '1' : '0');

            fetch(kioskForm.action, {
                method: 'POST',
                headers: { 'X-Requested-With': 'XMLHttpRequest' },
                body: formData
            }).then(async (response) => {
                if (response.ok) {
                    window.showToast?.('success', 'Updated', 'Kiosk status updated.');
                    return;
                }
                restoreToggle();
                const payload = await response.json().catch(() => null);
                const message = payload?.message || 'Failed to update kiosk status.';
                window.showToast?.('error', 'Error', message);
            }).catch(() => {
                restoreToggle();
                window.showToast?.('error', 'Error', 'Failed to update kiosk status.');
            });
        });
    }

    // Initialize Select2
    if (typeof $ !== 'undefined' && $.fn.select2) {
        $('select').select2({
            theme: 'default',
            width: '100%'
        });
    }
    
    // Handle working days checkboxes
    const dayCheckboxes = document.querySelectorAll('input[name="working_days[]"]');
    dayCheckboxes.forEach(checkbox => {
        checkbox.addEventListener('change', function() {
            const label = this.closest('label');
            if (this.checked) {
                label.classList.add('border-orange-500', 'bg-orange-50');
                label.classList.remove('border-gray-200');
            } else {
                label.classList.remove('border-orange-500', 'bg-orange-50');
                label.classList.add('border-gray-200');
            }
        });
    });

    const chairCountInput = document.getElementById('bookingChairCount');
    const chairFields = document.querySelectorAll('.chair-name-field');
    const syncChairFields = () => {
        if (!chairCountInput || chairFields.length === 0) return;

        const max = Number.parseInt(chairCountInput.max || '3', 10);
        const min = Number.parseInt(chairCountInput.min || '1', 10);
        let value = Number.parseInt(chairCountInput.value || String(min), 10);
        if (Number.isNaN(value)) value = min;
        value = Math.min(max, Math.max(min, value));
        chairCountInput.value = String(value);

        chairFields.forEach((field, idx) => {
            const enabled = idx < value;
            const input = field.querySelector('input');
            field.classList.toggle('hidden', !enabled);
            if (input) {
                input.disabled = !enabled;
                if (enabled && input.value.trim() === '') {
                    input.value = `Chair ${idx + 1}`;
                }
            }
        });
    };

    if (chairCountInput) {
        chairCountInput.addEventListener('input', syncChairFields);
        chairCountInput.addEventListener('change', syncChairFields);
        syncChairFields();
    }
});
</script>

<!-- Select2 JavaScript -->
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<?= $this->endSection() ?>
