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
    .settings-tab.hidden { display: none !important; }
    .ui-toggle {
        appearance: none;
        width: 44px;
        height: 24px;
        border-radius: 9999px;
        background: #e5e7eb;
        position: relative;
        cursor: pointer;
        transition: background 0.2s ease;
        flex-shrink: 0;
    }
    .ui-toggle::before {
        content: '';
        position: absolute;
        width: 18px;
        height: 18px;
        border-radius: 50%;
        background: #fff;
        top: 3px;
        left: 3px;
        transition: transform 0.2s ease;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.2);
    }
    .ui-toggle:checked {
        background: #3b82f6;
    }
    .ui-toggle:checked::before {
        transform: translateX(20px);
    }
    .ui-toggle:disabled {
        opacity: 0.5;
        cursor: not-allowed;
    }
    [dir="rtl"] #settings-breadcrumb {
        direction: rtl;
    }
    [dir="rtl"] #settings-breadcrumb .fa-chevron-right::before {
        content: "\f053";
    }
    [dir="rtl"] .settings-menu-btn {
        text-align: right;
    }
    [dir="rtl"] .settings-menu-btn i {
        margin-right: 0 !important;
        margin-left: 0.75rem !important;
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
                    <h1 class="text-3xl font-bold text-gray-900"><?= lang('UI.systemSettings') ?></h1>
                    <p class="text-gray-600"><?= lang('UI.settingsConfigureSystem') ?></p>
                </div>
            </div>
        </div>

        <nav class="mb-4 flex items-center space-x-1 text-sm text-gray-500" id="settings-breadcrumb">
            <a href="<?= base_url('dashboard') ?>" class="hover:text-blue-600 transition-colors"><?= lang('UI.home') ?></a>
            <i class="fas fa-chevron-right text-xs text-gray-300"></i>
            <span class="text-gray-700 font-medium"><?= lang('UI.menuSettings') ?></span>
            <i class="fas fa-chevron-right text-xs text-gray-300" id="breadcrumb-sep-2" style="display:none"></i>
            <span id="breadcrumb-tab" class="text-gray-900 font-semibold"></span>
            <i class="fas fa-chevron-right text-xs text-gray-300" id="breadcrumb-sep-3" style="display:none"></i>
            <span id="breadcrumb-subtab" class="text-gray-900 font-semibold"></span>
        </nav>

        <!-- Settings Layout with Sidebar -->
        <div class="flex flex-col lg:flex-row gap-8">
            <!-- Left Sidebar -->
            <div class="lg:w-80 flex-shrink-0">
                <div class="bg-white/90 backdrop-blur-xl rounded-2xl shadow-xl shadow-blue-500/10 border border-white/30 p-6 sticky top-8">
                    <h3 class="text-lg font-bold text-gray-900 mb-6"><?= lang('UI.settingsMenu') ?></h3>
                    <nav class="space-y-2">
                        <button onclick="showSettingsTab('clinic')" id="tab-clinic" class="settings-menu-btn w-full flex items-center px-4 py-3 text-left text-sm font-medium rounded-xl transition-all duration-200">
                            <i class="fas fa-building w-5 h-5 mr-3"></i> <?= lang('UI.settingsClinicProfile') ?>
                        </button>
                        <button onclick="showSettingsTab('subscription')" id="tab-subscription" class="settings-menu-btn w-full flex items-center px-4 py-3 text-left text-sm font-medium rounded-xl transition-all duration-200">
                            <i class="fas fa-layer-group w-5 h-5 mr-3"></i> <?= lang('UI.settingsSubscription') ?>
                        </button>
                        <button onclick="showSettingsTab('system')" id="tab-system" class="settings-menu-btn w-full flex items-center px-4 py-3 text-left text-sm font-medium rounded-xl transition-all duration-200">
                            <i class="fas fa-cog w-5 h-5 mr-3"></i> <?= lang('UI.settingsSystemTab') ?>
                        </button>
                        <button onclick="showSettingsTab('hours')" id="tab-hours" class="settings-menu-btn w-full flex items-center px-4 py-3 text-left text-sm font-medium rounded-xl transition-all duration-200">
                            <i class="fas fa-clock w-5 h-5 mr-3"></i> <?= lang('UI.settingsOperations') ?>
                        </button>
                        <button onclick="showSettingsTab('kiosk')" id="tab-kiosk" class="settings-menu-btn w-full flex items-center px-4 py-3 text-left text-sm font-medium rounded-xl transition-all duration-200">
                            <i class="fas fa-tablet-alt w-5 h-5 mr-3"></i> <?= lang('UI.settingsKiosk') ?>
                        </button>
                        <button onclick="showSettingsTab('backup')" id="tab-backup" class="settings-menu-btn w-full flex items-center px-4 py-3 text-left text-sm font-medium rounded-xl transition-all duration-200">
                            <i class="fas fa-database w-5 h-5 mr-3"></i> <?= lang('UI.settingsBackupRestore') ?>
                        </button>
                        
                        <div class="pt-4 pb-2 border-t border-gray-100 mt-4">
                            <h4 class="px-4 text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-2"><?= lang('UI.settingsCommunications') ?></h4>
                            <button onclick="showSettingsTab('communications')" id="tab-communications" class="settings-menu-btn w-full flex items-center px-4 py-3 text-left text-sm font-medium rounded-xl transition-all duration-200">
                                <i class="fas fa-satellite-dish w-5 h-5 mr-3"></i> <?= lang('UI.settingsCommunications') ?>
                            </button>
                        </div>

                    </nav>
                </div>
            </div>

            <!-- Main Content Area -->
            <div class="flex-1">
                <!-- Clinic Information Tab -->
                <div id="clinic-tab" class="settings-tab">
                    <div class="bg-white/90 backdrop-blur-xl rounded-2xl shadow-xl shadow-blue-500/10 border border-white/30 overflow-hidden">
                        <div class="bg-gradient-to-r from-slate-700 to-slate-900 p-8">
                            <div class="flex items-center space-x-4">
                                <div class="w-12 h-12 bg-white/20 rounded-xl flex items-center justify-center backdrop-blur-sm"><i class="fas fa-building text-blue-400 text-xl"></i></div>
                                <div><h3 class="text-2xl font-bold text-white"><?= lang('UI.settingsClinicProfile') ?></h3><p class="text-slate-300"><?= lang('UI.settingsClinicProfileDesc') ?></p></div>
                            </div>
                        </div>
                        <div class="p-8">
                            <form action="<?= base_url('settings/updateClinic') ?>" method="POST" enctype="multipart/form-data">
                                <?= csrf_field() ?>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <div><label class="block text-sm font-semibold text-gray-700 mb-2"><?= lang('UI.settingsClinicName') ?></label><input type="text" name="clinic_name" value="<?= $settings['clinic_name'] ?? '' ?>" class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:border-blue-500 focus:ring-4 focus:ring-blue-100 transition-all" required><p class="text-xs text-gray-500 mt-1"><?= lang('UI.settingsClinicNameHelp') ?></p></div>
                                    <div><label class="block text-sm font-semibold text-gray-700 mb-2"><?= lang('UI.settingsClinicPhone') ?></label><input type="tel" name="clinic_phone" value="<?= $settings['clinic_phone'] ?? '' ?>" class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:border-blue-500 focus:ring-4 focus:ring-blue-100 transition-all" required><p class="text-xs text-gray-500 mt-1"><?= lang('UI.settingsClinicPhoneHelp') ?></p></div>
                                    <div><label class="block text-sm font-semibold text-gray-700 mb-2"><?= lang('UI.settingsClinicEmail') ?></label><input type="email" name="clinic_email" value="<?= $settings['clinic_email'] ?? '' ?>" class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:border-blue-500 focus:ring-4 focus:ring-blue-100 transition-all" required><p class="text-xs text-gray-500 mt-1"><?= lang('UI.settingsClinicEmailHelp') ?></p></div>
                                    <div><label class="block text-sm font-semibold text-gray-700 mb-2"><?= lang('UI.settingsClinicWebsite') ?></label><input type="url" name="clinic_website" value="<?= esc($settings['clinic_website'] ?? '') ?>" class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:border-blue-500 focus:ring-4 focus:ring-blue-100 transition-all"><p class="text-xs text-gray-500 mt-1"><?= lang('UI.settingsClinicWebsiteHelp') ?></p></div>
                                    <div><label class="block text-sm font-semibold text-gray-700 mb-2"><?= lang('UI.settingsClinicTagline') ?></label><input type="text" name="clinic_tagline" value="<?= esc($settings['clinic_tagline'] ?? '') ?>" class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:border-blue-500 focus:ring-4 focus:ring-blue-100 transition-all"><p class="text-xs text-gray-500 mt-1"><?= lang('UI.settingsClinicTaglineHelp') ?></p></div>
                                    <div><label class="block text-sm font-semibold text-gray-700 mb-2"><?= lang('UI.settingsClinicLogo') ?></label><input type="file" name="clinic_logo" class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl"><p class="text-xs text-gray-500 mt-1"><?= lang('UI.settingsClinicLogoHelp') ?></p></div>
                                    <div class="md:col-span-2"><label class="block text-sm font-semibold text-gray-700 mb-2"><?= lang('UI.settingsClinicAddress') ?></label><textarea name="clinic_address" rows="3" class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:border-blue-500 focus:ring-4 focus:ring-blue-100 transition-all" required><?= esc($settings['clinic_address'] ?? '') ?></textarea><p class="text-xs text-gray-500 mt-1"><?= lang('UI.settingsClinicAddressHelp') ?></p></div>
                                </div>
                                <div class="mt-8 flex justify-end"><button type="submit" onclick="setButtonLoading(this, true)" class="px-8 py-3 bg-gradient-to-r from-blue-500 to-indigo-600 text-white font-semibold rounded-xl shadow-lg hover:shadow-xl transition-all"><i class="fas fa-save mr-2"></i><?= lang('UI.settingsSaveChanges') ?></button></div>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Subscription Plan Tab -->
                <div id="subscription-tab" class="settings-tab hidden">
                    <div class="bg-white/90 backdrop-blur-xl rounded-2xl shadow-xl shadow-blue-500/10 border border-white/30 overflow-hidden">
                        <div class="bg-gradient-to-r from-slate-700 to-slate-900 p-8">
                            <div class="flex items-center space-x-4">
                                <div class="w-12 h-12 bg-white/20 rounded-xl flex items-center justify-center backdrop-blur-sm"><i class="fas fa-layer-group text-emerald-400 text-xl"></i></div>
                                <div><h3 class="text-2xl font-bold text-white"><?= lang('UI.settingsSubscription') ?></h3><p class="text-slate-300"><?= lang('UI.settingsSubscriptionDesc') ?></p></div>
                            </div>
                        </div>
                        <div class="p-8 space-y-8">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div class="bg-white border border-slate-200 rounded-2xl p-6">
                                    <h4 class="text-sm font-bold text-slate-800"><?= lang('UI.settingsCurrentPlan') ?></h4>
                                    <p class="text-2xl font-bold text-slate-900 mt-3"><?= esc($subscription_plan['name'] ?? 'Unassigned') ?></p>
                                    <p class="text-sm text-slate-500 mt-2">Status: <?= esc($subscription['status'] ?? 'none') ?></p>
                                </div>
                                <div class="bg-white border border-slate-200 rounded-2xl p-6">
                                    <h4 class="text-sm font-bold text-slate-800"><?= lang('UI.settingsLatestRequest') ?></h4>
                                    <?php if (!empty($latest_plan_request)): ?>
                                        <p class="text-sm text-slate-500 mt-2">Status: <?= esc(ucfirst($latest_plan_request['status'] ?? 'pending')) ?></p>
                                        <p class="text-xs text-slate-400">Requested: <?= esc($latest_plan_request['created_at'] ?? '--') ?></p>
                                    <?php else: ?>
                                        <p class="text-sm text-slate-500 mt-2"><?= lang('UI.settingsNoPlanRequestsYet') ?></p>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <form action="<?= base_url('settings/plan-request') ?>" method="POST" class="bg-white border border-slate-200 rounded-2xl p-6 space-y-4">
                                <?= csrf_field() ?>
                                <h4 class="text-sm font-bold text-slate-800"><?= lang('UI.settingsRequestNewPlan') ?></h4>
                                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                    <div><label class="block text-xs font-semibold text-gray-700 mb-2"><?= lang('UI.settingsPlan') ?></label><select name="plan_id" class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:border-emerald-500 transition-all" required><option value=""><?= lang('UI.settingsSelectPlan') ?></option><?php foreach (($plans ?? []) as $plan): ?><option value="<?= esc($plan['id']) ?>"><?= esc($plan['name']) ?></option><?php endforeach; ?></select></div>
                                    <div><label class="block text-xs font-semibold text-gray-700 mb-2"><?= lang('UI.settingsDuration') ?></label><select name="duration" class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:border-emerald-500 transition-all"><option value="monthly"><?= lang('UI.settingsDurationMonthly') ?></option><option value="yearly"><?= lang('UI.settingsDurationYearly') ?></option><option value="lifetime"><?= lang('UI.settingsDurationLifetime') ?></option></select></div>
                                    <div><label class="block text-xs font-semibold text-gray-700 mb-2"><?= lang('UI.settingsReason') ?></label><input type="text" name="reason" class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:border-emerald-500 transition-all" placeholder="<?= lang('UI.settingsOptionalReason') ?>"></div>
                                </div>
                                <div class="flex justify-end"><button type="submit" onclick="setButtonLoading(this, true)" class="px-6 py-3 bg-gradient-to-r from-emerald-500 to-teal-600 text-white font-semibold rounded-xl shadow-lg transition-all"><i class="fas fa-paper-plane mr-2"></i><?= lang('UI.settingsSubmitRequest') ?></button></div>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- System Preference Tab -->
                <div id="system-tab" class="settings-tab hidden">
                    <div class="bg-white/90 backdrop-blur-xl rounded-2xl shadow-xl shadow-blue-500/10 border border-white/30 overflow-hidden">
                        <div class="bg-gradient-to-r from-slate-700 to-slate-900 p-8">
                            <div class="flex items-center space-x-4">
                                <div class="w-12 h-12 bg-white/20 rounded-xl flex items-center justify-center backdrop-blur-sm"><i class="fas fa-cog text-violet-400 text-xl"></i></div>
                                <div><h3 class="text-2xl font-bold text-white"><?= lang('UI.settingsSystemTab') ?></h3><p class="text-slate-300"><?= lang('UI.settingsSystemDesc') ?></p></div>
                            </div>
                        </div>
                        <div class="p-8">
                            <form id="system-settings-form" action="<?= base_url('settings/updateSystem') ?>" method="POST">
                                <?= csrf_field() ?>
                                <input type="hidden" name="redirect_tab" value="system">
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <div><label class="block text-sm font-semibold text-gray-700 mb-2">Timezone</label><select name="timezone" class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:border-green-500 transition-all" required><?php $tzs = DateTimeZone::listIdentifiers(); foreach($tzs as $tz): ?><option value="<?= $tz ?>" <?= ($settings['timezone'] ?? '') == $tz ? 'selected' : '' ?>><?= $tz ?></option><?php endforeach; ?></select><p class="text-xs text-gray-500 mt-1">Affects appointment times, report dates, and scheduled messages.</p></div>
                                    <div><label class="block text-sm font-semibold text-gray-700 mb-2">Date Format</label><select name="date_format" class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:border-green-500 transition-all" required><option value="Y-m-d" <?= ($settings['date_format'] ?? '') == 'Y-m-d' ? 'selected' : '' ?>>YYYY-MM-DD</option><option value="m/d/Y" <?= ($settings['date_format'] ?? '') == 'm/d/Y' ? 'selected' : '' ?>>MM/DD/YYYY</option><option value="d/m/Y" <?= ($settings['date_format'] ?? '') == 'd/m/Y' ? 'selected' : '' ?>>DD/MM/YYYY</option></select><p class="text-xs text-gray-500 mt-1">Controls how dates appear across the system.</p></div>
                                    <div><label class="block text-sm font-semibold text-gray-700 mb-2">Time Format</label><select name="time_format" class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:border-green-500 transition-all" required><option value="24" <?= ($settings['time_format'] ?? '') == '24' ? 'selected' : '' ?>>24 Hour</option><option value="12" <?= ($settings['time_format'] ?? '') == '12' ? 'selected' : '' ?>>12 Hour</option></select><p class="text-xs text-gray-500 mt-1">24-hour or 12-hour (AM/PM) display.</p></div>
                                    <div>
                                        <label class="block text-sm font-semibold text-gray-700 mb-2">UI Language</label>
                                        <select name="ui_language" class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:border-green-500 transition-all" required>
                                            <option value="en" <?= ($settings['ui_language'] ?? 'en') == 'en' ? 'selected' : '' ?>>English</option>
                                            <option value="ar" <?= ($settings['ui_language'] ?? 'en') == 'ar' ? 'selected' : '' ?>>Arabic</option>
                                        </select>
                                        <p class="text-xs text-gray-500 mt-1">Changes the interface language.</p>
                                    </div>
                                </div>
                                <div class="mt-8 flex justify-end"><button type="button" onclick="submitSystemSettings(this)" class="px-8 py-3 bg-gradient-to-r from-green-500 to-emerald-600 text-white font-semibold rounded-xl shadow-lg transition-all"><i class="fas fa-save mr-2"></i><?= lang('UI.settingsSaveChanges') ?></button></div>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Working Hours Tab -->
                <div id="hours-tab" class="settings-tab hidden">
                    <div class="bg-white/90 backdrop-blur-xl rounded-2xl shadow-xl shadow-blue-500/10 border border-white/30 overflow-hidden">
                        <div class="bg-gradient-to-r from-slate-700 to-slate-900 p-8">
                            <div class="flex items-center space-x-4">
                                <div class="w-12 h-12 bg-white/20 rounded-xl flex items-center justify-center backdrop-blur-sm"><i class="fas fa-clock text-orange-400 text-xl"></i></div>
                                <div><h3 class="text-2xl font-bold text-white"><?= lang('UI.settingsOperations') ?></h3><p class="text-slate-300"><?= lang('UI.settingsOperationsDesc') ?></p></div>
                            </div>
                        </div>
                        <div class="p-8">
                            <form action="<?= base_url('settings/updateWorkingHours') ?>" method="POST">
                                <?= csrf_field() ?>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <div><label class="block text-sm font-semibold text-gray-700 mb-2">Start Time</label><input type="time" name="working_hours_start" value="<?= $settings['working_hours_start'] ?? '09:00' ?>" dir="ltr" style="text-align: left;" class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:border-orange-500 transition-all" required><p class="text-xs text-gray-500 mt-1">The earliest time a new appointment can be booked.</p></div>
                                    <div><label class="block text-sm font-semibold text-gray-700 mb-2">End Time</label><input type="time" name="working_hours_end" value="<?= $settings['working_hours_end'] ?? '17:00' ?>" dir="ltr" style="text-align: left;" class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:border-orange-500 transition-all" required><p class="text-xs text-gray-500 mt-1">The latest time a new appointment can be booked.</p></div>
                                    <div class="md:col-span-2"><label class="block text-sm font-semibold text-gray-700 mb-2">Number of Chairs</label><input type="number" name="booking_chair_count" value="<?= $settings['booking_chair_count'] ?? 1 ?>" min="1" max="3" dir="ltr" style="text-align: left;" class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:border-orange-500 transition-all" required><p class="text-xs text-gray-500 mt-1">Number of simultaneous appointment slots available (1-3).</p></div>
                                </div>
                                <div class="mt-8 flex justify-end"><button type="submit" onclick="setButtonLoading(this, true)" class="px-8 py-3 bg-gradient-to-r from-orange-500 to-amber-600 text-white font-semibold rounded-xl shadow-lg transition-all"><i class="fas fa-save mr-2"></i><?= lang('UI.settingsSaveChanges') ?></button></div>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Kiosk Tab -->
                <div id="kiosk-tab" class="settings-tab hidden">
                    <div class="bg-white/90 backdrop-blur-xl rounded-2xl shadow-xl shadow-blue-500/10 border border-white/30 overflow-hidden">
                        <div class="bg-gradient-to-r from-slate-700 to-slate-900 p-8">
                            <div class="flex items-center space-x-4">
                                <div class="w-12 h-12 bg-white/20 rounded-xl flex items-center justify-center backdrop-blur-sm"><i class="fas fa-tablet-alt text-cyan-400 text-xl"></i></div>
                                <div><h3 class="text-2xl font-bold text-white"><?= lang('UI.settingsKiosk') ?></h3><p class="text-slate-300"><?= lang('UI.settingsKioskDesc') ?></p></div>
                            </div>
                        </div>

                        <div class="p-8 space-y-8">
                            <?php $kioskFeatureEnabled = !empty($kiosk_feature_enabled); ?>
                            <?php $kioskLink = !empty($clinic_uuid) ? base_url('kiosk/' . $clinic_uuid . '/check-in') : ''; ?>

                            <?php if (!$kioskFeatureEnabled): ?>
                                <div class="bg-amber-50 border border-amber-200 rounded-2xl p-5 text-amber-700 text-sm">
                                    <?= lang('UI.settingsKioskDisabledByPlan') ?>
                                </div>
                            <?php endif; ?>

                            <div class="bg-slate-50 border border-slate-200 rounded-2xl p-5 space-y-4">
                                <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                                    <div>
                                        <h4 class="text-sm font-bold text-slate-800"><?= lang('UI.settingsKioskLink') ?></h4>
                                        <p class="text-sm text-slate-500">Open this link on the clinic tablet or kiosk device.</p>
                                    </div>
                                    <div class="flex items-center gap-2">
                                        <span class="px-3 py-1 rounded-full text-xs font-semibold <?= ($kiosk_pin_set ?? false) ? 'bg-emerald-100 text-emerald-700' : 'bg-amber-100 text-amber-700' ?>">
                                            <?= ($kiosk_pin_set ?? false) ? lang('UI.settingsPinSet') : lang('UI.settingsPinNotSet') ?>
                                        </span>
                                    </div>
                                </div>
                                <div class="flex flex-col md:flex-row gap-3">
                                    <input id="kioskLinkInput" type="text" readonly value="<?= esc($kioskLink) ?>" class="flex-1 px-4 py-3 border-2 border-gray-200 rounded-xl bg-gray-50 text-sm text-gray-600" />
                                    <button type="button" onclick="copyKioskLink()" class="px-5 py-3 bg-white border-2 border-blue-200 text-blue-600 font-semibold rounded-xl hover:bg-blue-50 transition-all" <?= $kioskFeatureEnabled ? '' : 'disabled' ?>>
                                        <i class="fas fa-copy mr-2"></i><?= lang('UI.settingsCopyLink') ?>
                                    </button>
                                </div>
                            </div>

                            <form action="<?= base_url('settings/updateKiosk') ?>" method="POST" class="bg-white border border-slate-200 rounded-2xl p-6 space-y-6 <?= $kioskFeatureEnabled ? '' : 'opacity-60' ?>">
                                <?= csrf_field() ?>
                                <div class="flex items-center justify-between">
                                    <div>
                                        <h4 class="text-sm font-bold text-slate-800"><?= lang('UI.settingsEnableKioskCheckin') ?></h4>
                                        <p class="text-sm text-slate-500">Allow patients to confirm appointments from a clinic kiosk.</p>
                                    </div>
                                    <label class="inline-flex items-center cursor-pointer">
                                        <input type="checkbox" name="kiosk_enabled" value="1" class="ui-toggle kiosk-toggle" <?= !empty($settings['kiosk_enabled']) ? 'checked' : '' ?> <?= $kioskFeatureEnabled ? '' : 'disabled' ?>>
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
                                            <input type="password" inputmode="numeric" name="kiosk_pin" dir="ltr" style="text-align: left;" class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:border-blue-500 focus:ring-4 focus:ring-blue-100 transition-all duration-200" placeholder="4 to 8 digits" <?= $kioskFeatureEnabled ? '' : 'disabled' ?>>
                                        </div>
                                        <div>
                                            <label class="block text-sm font-semibold text-gray-700 mb-2">Confirm PIN</label>
                                            <input type="password" inputmode="numeric" name="kiosk_pin_confirm" dir="ltr" style="text-align: left;" class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:border-blue-500 focus:ring-4 focus:ring-blue-100 transition-all duration-200" placeholder="Re-enter PIN" <?= $kioskFeatureEnabled ? '' : 'disabled' ?>>
                                        </div>
                                    </div>
                                </div>

                                <div class="flex justify-end">
                                    <button type="submit" onclick="setButtonLoading(this, true)" class="px-8 py-3 bg-gradient-to-r from-indigo-500 to-blue-600 text-white font-semibold rounded-xl hover:from-indigo-600 hover:to-blue-700 transition-all duration-200 shadow-lg" <?= $kioskFeatureEnabled ? '' : 'disabled' ?>>
                                        <i class="fas fa-save mr-2"></i><?= lang('UI.settingsSaveKioskSettings') ?>
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- DB Backup Tab -->
                <div id="backup-tab" class="settings-tab hidden">
                    <div class="bg-white/90 backdrop-blur-xl rounded-2xl shadow-xl shadow-blue-500/10 border border-white/30 overflow-hidden">
                        <div class="bg-gradient-to-r from-slate-700 to-slate-900 p-8">
                            <div class="flex items-center space-x-4">
                                <div class="w-12 h-12 bg-white/20 rounded-xl flex items-center justify-center backdrop-blur-sm"><i class="fas fa-database text-amber-400 text-xl"></i></div>
                                <div><h3 class="text-2xl font-bold text-white">Backup &amp; Restore</h3><p class="text-slate-300">Manage data safety</p></div>
                            </div>
                        </div>
                        <div class="p-8">
                            <div class="flex justify-between items-center mb-8 bg-gray-50 p-6 rounded-2xl border-2 border-dashed border-gray-200">
                                <div><h4 class="text-lg font-bold text-gray-900"><?= lang('UI.settingsManualBackup') ?></h4><p class="text-sm text-gray-500"><?= lang('UI.settingsManualBackupDesc') ?></p></div>
                                <form action="<?= base_url('settings/create-backup') ?>" method="POST"><?= csrf_field() ?><button type="submit" onclick="setButtonLoading(this, true)" class="px-6 py-3 bg-purple-600 text-white font-bold rounded-xl shadow-lg hover:bg-purple-700 transition-all"><i class="fas fa-download mr-2"></i><?= lang('UI.settingsCreateBackup') ?></button></form>
                            </div>
                            <div class="space-y-4">
                                <?php if (!empty($backups)): foreach ($backups as $backup): ?>
                                <div class="flex items-center justify-between p-4 bg-white border border-gray-100 rounded-xl shadow-sm">
                                    <div><h5 class="font-bold text-gray-800"><?= $backup['name'] ?></h5><p class="text-xs text-gray-500"><?= $backup['size'] ?> • <?= $backup['created_at'] ?></p></div>
                                    <div class="flex space-x-2"><button onclick="downloadBackup('<?= $backup['name'] ?>')" class="p-2 text-blue-600 hover:bg-blue-50 rounded-lg"><i class="fas fa-download"></i></button><button onclick="restoreBackup('<?= $backup['name'] ?>')" class="p-2 text-red-600 hover:bg-red-50 rounded-lg"><i class="fas fa-undo"></i></button></div>
                                </div>
                                <?php endforeach; else: ?>
                                <div class="flex flex-col items-center justify-center py-16 text-center">
                                    <div class="w-16 h-16 bg-gray-100 rounded-2xl flex items-center justify-center mb-4">
                                        <i class="fas fa-database text-gray-300 text-2xl"></i>
                                    </div>
                                    <h4 class="text-lg font-bold text-gray-800 mb-1"><?= lang('UI.settingsNoBackupsYet') ?></h4>
                                    <p class="text-sm text-gray-500 mb-6 max-w-xs">
                                        Create your first backup to protect your clinic's data.
                                        Backups can be downloaded or used to restore the system.
                                    </p>
                                </div>
                                <?php endif; ?>
                            </div>

                            <div class="mt-8 pt-8 border-t border-gray-100">
                                <h4 class="text-lg font-bold text-gray-900 mb-2 flex items-center">
                                    <i class="fas fa-history text-red-500 mr-2"></i>
                                    Export Retention Policy
                                </h4>
                                <p class="text-sm text-gray-500 mb-6">
                                    Control how long exported files are kept before automatic pruning.
                                </p>

                                <?php if (!session()->get('global_mode')): ?>
                                <div class="bg-amber-50 border border-amber-200 rounded-xl p-4 text-amber-700 text-sm">
                                    <i class="fas fa-lock mr-2"></i>
                                    Export retention policy is managed by your platform administrator.
                                </div>
                                <?php else: ?>
                                <form action="<?= base_url('settings/updateRetention') ?>" method="POST" class="space-y-6">
                                    <?= csrf_field() ?>
                                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                        <label class="p-4 border-2 rounded-xl cursor-pointer text-center <?= ($settings['retention_mode']??'latest')==='latest'?'border-red-500 bg-red-50':'border-gray-200' ?>">
                                            <input type="radio" name="retention_mode" value="latest" class="sr-only" <?= ($settings['retention_mode']??'latest')==='latest'?'checked':'' ?>>
                                            <div class="font-bold">Latest Only</div>
                                        </label>
                                        <label class="p-4 border-2 rounded-xl cursor-pointer text-center <?= ($settings['retention_mode']??'')==='keep_last_n'?'border-red-500 bg-red-50':'border-gray-200' ?>">
                                            <input type="radio" name="retention_mode" value="keep_last_n" class="sr-only" <?= ($settings['retention_mode']??'')==='keep_last_n'?'checked':'' ?>>
                                            <div class="font-bold">Keep Last N</div>
                                        </label>
                                        <label class="p-4 border-2 rounded-xl cursor-pointer text-center <?= ($settings['retention_mode']??'')==='keep_x_days'?'border-red-500 bg-red-50':'border-gray-200' ?>">
                                            <input type="radio" name="retention_mode" value="keep_x_days" class="sr-only" <?= ($settings['retention_mode']??'')==='keep_x_days'?'checked':'' ?>>
                                            <div class="font-bold">Time Based</div>
                                        </label>
                                    </div>
                                    <div class="flex justify-end">
                                        <button type="submit" onclick="setButtonLoading(this, true)" class="px-8 py-3 bg-gradient-to-r from-red-500 to-rose-600 text-white font-semibold rounded-xl shadow-lg transition-all">
                                            Save Policy
                                        </button>
                                    </div>
                                </form>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Communications Tab -->
                <div id="communications-tab" class="settings-tab hidden">
                    <div class="bg-white/90 backdrop-blur-xl rounded-2xl shadow-xl shadow-blue-500/10 border border-white/30 overflow-hidden">
                        <div class="bg-gradient-to-r from-slate-700 to-slate-900 p-8">
                            <div class="flex items-center space-x-4">
                                <div class="w-12 h-12 bg-white/20 rounded-xl flex items-center justify-center backdrop-blur-sm">
                                    <i class="fas fa-satellite-dish text-sky-400 text-xl"></i>
                                </div>
                                <div>
                                    <h3 class="text-2xl font-bold text-white"><?= lang('UI.settingsCommunications') ?></h3>
                                    <p class="text-slate-300">Delivery channels and notification preferences</p>
                                </div>
                            </div>
                        </div>
                        <div class="border-b border-gray-100 px-8 pt-4">
                            <div class="flex space-x-1 overflow-x-auto pb-0">
                                <button onclick="showSubTab('channels')" id="subtab-channels" class="subtab-btn px-4 py-2 text-sm font-semibold rounded-t-lg whitespace-nowrap">
                                    <i class="fas fa-th-large mr-1"></i><?= lang('UI.settingsChannels') ?>
                                </button>
                                <button onclick="showSubTab('email')" id="subtab-email" class="subtab-btn px-4 py-2 text-sm font-semibold rounded-t-lg whitespace-nowrap">
                                    <i class="fas fa-envelope mr-1"></i><?= lang('UI.settingsEmail') ?>
                                </button>
                                <button onclick="showSubTab('sms')" id="subtab-sms" class="subtab-btn px-4 py-2 text-sm font-semibold rounded-t-lg whitespace-nowrap">
                                    <i class="fas fa-sms mr-1"></i><?= lang('UI.settingsSms') ?>
                                </button>
                                <button onclick="showSubTab('whatsapp')" id="subtab-whatsapp" class="subtab-btn px-4 py-2 text-sm font-semibold rounded-t-lg whitespace-nowrap">
                                    <i class="fab fa-whatsapp mr-1"></i><?= lang('UI.settingsWhatsApp') ?>
                                </button>
                                <button onclick="showSubTab('alerts')" id="subtab-alerts" class="subtab-btn px-4 py-2 text-sm font-semibold rounded-t-lg whitespace-nowrap">
                                    <i class="fas fa-user-cog mr-1"></i><?= lang('UI.settingsMyAlerts') ?>
                                </button>
                            </div>
                        </div>
                        <div class="p-8">
                            <div id="sub-channels" class="sub-tab-panel">
                            <?php if ($is_global): ?>
                                <div class="overflow-x-auto"><table class="w-full text-left"><thead><tr class="text-xs font-bold text-gray-500 uppercase border-b"><th class="pb-4">Clinic</th><th class="pb-4">Email</th><th class="pb-4">SMS</th><th class="pb-4">WhatsApp</th></tr></thead><tbody class="divide-y divide-gray-50">
                                    <?php foreach ($clinics as $clinic): ?>
                                    <tr><td class="py-4 font-bold"><?= esc($clinic['name']) ?></td>
                                        <?php foreach (['email', 'sms', 'whatsapp'] as $type): 
                                            $ch = null; foreach ($clinic['channels'] as $c) if ($c['channel_type'] === $type) { $ch = $c; break; }
                                            $enabled = $ch && $ch['enabled_by_superadmin'] == 1; ?>
                                        <td class="py-4"><button onclick="toggleChannel(<?= $clinic['id'] ?>, '<?= $type ?>', <?= $enabled ? 0 : 1 ?>, this)" class="w-10 h-6 rounded-full transition-all <?= $enabled ? 'bg-green-500' : 'bg-gray-300' ?>"><div class="w-4 h-4 bg-white rounded-full transition-all ml-1 <?= $enabled ? 'translate-x-4' : '' ?>"></div></button></td>
                                        <?php endforeach; ?>
                                    </tr><?php endforeach; ?>
                                </tbody></table></div>
                            <?php else: ?>
                                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                                    <?php foreach ($channels as $channel): 
                                        $type = $channel['channel_type']; 
                                        $locked = empty($channel['enabled_by_superadmin']); ?>
                                    <div class="p-6 bg-gray-50 rounded-2xl border-2 border-gray-100 text-center">
                                        <div class="w-12 h-12 mx-auto bg-white rounded-xl flex items-center justify-center text-blue-600 shadow-sm mb-4"><i class="fas fa-<?= $type==='whatsapp'?'fab fa-whatsapp':($type==='sms'?'sms':'envelope') ?> text-2xl"></i></div>
                                        <h4 class="font-bold text-gray-900 uppercase"><?= $type ?></h4>
                                        <div class="my-2"><span class="px-2 py-1 text-[10px] font-bold rounded-lg <?= $locked ? 'bg-red-100 text-red-700' : 'bg-green-100 text-green-700' ?>"><?= $locked ? 'DISABLED' : 'ACTIVE' ?></span></div>
                                        <button onclick="showSettingsTab('<?= $type ?>')" class="mt-4 px-4 py-2 bg-white border border-gray-200 text-xs font-bold rounded-lg hover:bg-gray-100 transition-all">CONFIGURE</button>
                                    </div>
                                    <?php endforeach; ?>
                                    <?php if (empty($channels)): ?>
                                    <div class="md:col-span-3 flex flex-col items-center justify-center py-16 text-center">
                                        <div class="w-16 h-16 bg-gray-100 rounded-2xl flex items-center justify-center mb-4">
                                            <i class="fas fa-satellite-dish text-gray-300 text-2xl"></i>
                                        </div>
                                        <h4 class="text-lg font-bold text-gray-800 mb-1">No channels configured</h4>
                                        <p class="text-sm text-gray-500 max-w-xs">
                                            Contact your platform administrator to enable communication channels
                                            for your clinic.
                                        </p>
                                    </div>
                                    <?php endif; ?>
                                </div>
                            <?php endif; ?>
                            </div>

                <!-- Email Tab -->
                <div id="sub-email" class="sub-tab-panel hidden">
                    <div class="bg-white/90 backdrop-blur-xl rounded-2xl shadow-xl shadow-blue-500/10 border border-white/30 overflow-hidden">
                        <div class="bg-gradient-to-r from-slate-700 to-slate-900 p-8 flex justify-between items-center"><div class="flex items-center space-x-4"><div class="w-12 h-12 bg-white/20 rounded-xl flex items-center justify-center backdrop-blur-sm"><i class="fas fa-envelope text-blue-400 text-xl"></i></div><div><h3 class="text-2xl font-bold text-white"><?= lang('UI.settingsEmailSettings') ?></h3><p class="text-slate-300"><?= lang('UI.settingsSmtpConfiguration') ?></p></div></div><button onclick="saveEmailConfig(event)" class="px-6 py-2.5 bg-white text-blue-600 font-bold rounded-xl shadow-lg transition-all"><i class="fas fa-save mr-2"></i><?= lang('UI.settingsSaveSmtp') ?></button></div>
                        <div class="p-8 space-y-6">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div class="md:col-span-2"><label class="block text-sm font-semibold text-gray-700 mb-2">SMTP Host</label><input type="text" id="smtp_host" value="<?= esc($email_config['smtp_host'] ?? '') ?>" dir="ltr" style="text-align: left;" class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl transition-all"><p class="text-xs text-gray-500 mt-1">Your email provider's SMTP server address (e.g. smtp.gmail.com).</p></div>
                                <div><label class="block text-sm font-semibold text-gray-700 mb-2">Port</label><input type="number" id="smtp_port" value="<?= esc($email_config['smtp_port'] ?? '587') ?>" dir="ltr" style="text-align: left;" class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl transition-all"><p class="text-xs text-gray-500 mt-1">Standard ports: 587 (TLS), 465 (SSL), 25 (unencrypted - avoid).</p></div>
                                <div><label class="block text-sm font-semibold text-gray-700 mb-2">Encryption</label><select id="smtp_crypto" class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl"><option value="tls" <?= ($email_config['smtp_crypto']??'')==='tls'?'selected':'' ?>>TLS</option><option value="ssl" <?= ($email_config['smtp_crypto']??'')==='ssl'?'selected':'' ?>>SSL</option></select><p class="text-xs text-gray-500 mt-1">Use TLS (port 587) for most providers. SSL for legacy setups.</p></div>
                                <div><label class="block text-sm font-semibold text-gray-700 mb-2">User</label><input type="text" id="smtp_user" value="<?= esc($email_config['smtp_user'] ?? '') ?>" dir="ltr" style="text-align: left;" class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl transition-all"><p class="text-xs text-gray-500 mt-1">Usually your full email address.</p></div>
                                <div><label class="block text-sm font-semibold text-gray-700 mb-2">Pass</label><input type="password" id="smtp_pass" class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl transition-all" placeholder="••••••••"><p class="text-xs text-gray-500 mt-1">Leave blank to keep the current password.</p></div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- SMS Tab -->
                <div id="sub-sms" class="sub-tab-panel hidden">
                    <div class="bg-white/90 backdrop-blur-xl rounded-2xl shadow-xl shadow-blue-500/10 border border-white/30 overflow-hidden">
                        <div class="bg-gradient-to-r from-slate-700 to-slate-900 p-8 flex justify-between items-center"><div class="flex items-center space-x-4"><div class="w-12 h-12 bg-white/20 rounded-xl flex items-center justify-center backdrop-blur-sm"><i class="fas fa-sms text-orange-400 text-xl"></i></div><div><h3 class="text-2xl font-bold text-white"><?= lang('UI.settingsSmsGateway') ?></h3><p class="text-slate-300"><?= lang('UI.settingsSmsGatewayDesc') ?></p></div></div><button onclick="saveSmsConfig(event)" class="px-6 py-2.5 bg-white text-orange-600 font-bold rounded-xl shadow-lg transition-all"><i class="fas fa-save mr-2"></i><?= lang('UI.settingsSaveGateway') ?></button></div>
                        <div class="p-8 space-y-6">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <label class="block text-sm font-semibold text-gray-700 mb-2">SMS Provider</label>
                                    <select id="sms_provider" class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl">
                                        <option value="">Select provider</option>
                                        <option value="unifonic">Unifonic</option>
                                        <option value="twilio">Twilio</option>
                                        <option value="msegat">Msegat</option>
                                        <option value="custom">Custom (API)</option>
                                    </select>
                                    <p class="text-xs text-gray-500 mt-1">Your SMS gateway provider.</p>
                                </div>
                                <div>
                                    <label class="block text-sm font-semibold text-gray-700 mb-2">API Key / Token</label>
                                    <input type="password" id="sms_api_key" class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl" placeholder="********" autocomplete="new-password">
                                    <p class="text-xs text-gray-500 mt-1">Leave blank to keep current key.</p>
                                </div>
                                <div>
                                    <label class="block text-sm font-semibold text-gray-700 mb-2">Sender ID / From</label>
                                    <input type="text" id="sms_sender_id" class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl" placeholder="ClinicName">
                                    <p class="text-xs text-gray-500 mt-1">Alphanumeric sender name (max 11 chars for most providers).</p>
                                </div>
                                <div id="sms_base_url_wrap">
                                    <label class="block text-sm font-semibold text-gray-700 mb-2">Base API URL</label>
                                    <input type="url" id="sms_base_url" class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl" placeholder="https://api.provider.com/v1">
                                    <p class="text-xs text-gray-500 mt-1">Required for Custom provider only.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- WhatsApp Tab -->
                <div id="sub-whatsapp" class="sub-tab-panel hidden">
                    <div class="bg-white/90 backdrop-blur-xl rounded-2xl shadow-xl shadow-blue-500/10 border border-white/30 overflow-hidden">
                        <div class="bg-gradient-to-r from-slate-700 to-slate-900 p-8 flex justify-between items-center"><div class="flex items-center space-x-4"><div class="w-12 h-12 bg-white/20 rounded-xl flex items-center justify-center backdrop-blur-sm"><i class="fab fa-whatsapp text-emerald-400 text-xl"></i></div><div><h3 class="text-2xl font-bold text-white"><?= lang('UI.settingsWhatsApp') ?></h3><p class="text-slate-300"><?= lang('UI.settingsWhatsAppMetaDesc') ?></p></div></div><div class="flex space-x-2"><button onclick="testWhatsAppChannel()" class="px-4 py-2 bg-white/20 text-white font-bold rounded-xl hover:bg-white/30 transition-all"><?= lang('UI.settingsTest') ?></button><button onclick="saveWhatsAppConfig(event)" class="px-6 py-2 bg-white text-green-700 font-bold rounded-xl shadow-lg transition-all"><i class="fas fa-save mr-2"></i><?= lang('UI.settingsSave') ?></button></div></div>
                        <div class="p-8 space-y-8">
                            <div><label class="block text-sm font-semibold text-gray-700 mb-2">Phone Number ID</label><input type="text" id="wa_phone_number_id" value="<?= esc($wa_config['phone_number_id'] ?? '') ?>" dir="ltr" style="text-align: left;" class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl font-mono" placeholder="1234567890"><p class="text-xs text-gray-500 mt-1">Found in your Meta Business Manager > WhatsApp > Phone Number ID.</p></div>
                            <div class="pt-6 border-t border-gray-100">
                                <h4 class="font-bold text-gray-900 mb-4 flex justify-between items-center"><span>Arabic Templates</span><button onclick="resetTemplatesToDefault()" class="text-[10px] text-blue-600 font-bold hover:underline">RESET</button></h4>
                                <div class="grid grid-cols-1 gap-4" dir="rtl">
                                    <?php foreach (['appointment_reminder' => 'تذكير بالموعد', 'appointment_confirmation' => 'تأكيد الحجز', 'follow_up' => 'متابعة', 'payment_reminder' => 'تذكير بالدفع'] as $k => $l): ?>
                                    <div class="space-y-1"><label class="block text-xs font-bold text-gray-600"><?= $l ?></label><textarea id="tmpl_<?= $k ?>" rows="2" class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl transition-all text-right"><?= esc($wa_config['message_templates'][$k] ?? '') ?></textarea></div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Notifications Preference Tab -->
                <div id="sub-alerts" class="sub-tab-panel hidden">
                    <div class="bg-white/90 backdrop-blur-xl rounded-2xl shadow-xl shadow-blue-500/10 border border-white/30 overflow-hidden">
                        <div class="bg-gradient-to-r from-slate-700 to-slate-900 p-8"><div class="flex items-center space-x-4"><div class="w-12 h-12 bg-white/20 rounded-xl flex items-center justify-center backdrop-blur-sm"><i class="fas fa-user-cog text-amber-400 text-xl"></i></div><div><h3 class="text-2xl font-bold text-white"><?= lang('UI.settingsPersonalAlerts') ?></h3><p class="text-slate-300"><?= lang('UI.settingsPersonalAlertsDesc') ?></p></div></div></div>
                        <div class="p-8">
                            <form action="<?= base_url('settings/updateNotifications') ?>" method="POST" class="space-y-8">
                                <?= csrf_field() ?>
                                <div>
                                    <h4 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                                        <i class="fas fa-bell text-blue-500 mr-2"></i><?= lang('UI.settingsSystemAlerts') ?>
                                    </h4>
                                    <div class="space-y-4">
                                        <div class="flex items-center justify-between p-4 bg-gray-50 rounded-2xl border border-gray-100">
                                            <div>
                                                <p class="font-bold text-gray-800">Email Alerts</p>
                                                <p class="text-xs text-gray-500 mt-1">Receive updates via email.</p>
                                            </div>
                                            <input type="checkbox" name="preferences[email_notifications]" value="1" class="ui-toggle" <?= !empty($notification_settings['email_notifications']) ? 'checked' : '' ?>>
                                        </div>
                                        <div class="flex items-center justify-between p-4 bg-gray-50 rounded-2xl border border-gray-100">
                                            <div>
                                                <p class="font-bold text-gray-800">SMS Alerts</p>
                                                <p class="text-xs text-gray-500 mt-1">Receive short critical updates by SMS.</p>
                                            </div>
                                            <input type="checkbox" name="preferences[sms_notifications]" value="1" class="ui-toggle" <?= !empty($notification_settings['sms_notifications']) ? 'checked' : '' ?>>
                                        </div>
                                    </div>
                                </div>

                                <div>
                                    <h4 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                                        <i class="fas fa-tasks text-green-500 mr-2"></i>Operational Alerts
                                    </h4>
                                    <div class="space-y-4">
                                        <div class="flex items-center justify-between p-4 bg-gray-50 rounded-2xl border border-gray-100">
                                            <div>
                                                <p class="font-bold text-gray-800">New Appointments</p>
                                                <p class="text-xs text-gray-500 mt-1">Get notified when appointments are created.</p>
                                            </div>
                                            <input type="checkbox" name="preferences[appointment_created]" value="1" class="ui-toggle" <?= !empty($notification_settings['appointment_created']) ? 'checked' : '' ?>>
                                        </div>
                                        <div class="flex items-center justify-between p-4 bg-gray-50 rounded-2xl border border-gray-100">
                                            <div>
                                                <p class="font-bold text-gray-800">Low Stock Alerts</p>
                                                <p class="text-xs text-gray-500 mt-1">Get notified when inventory items are low.</p>
                                            </div>
                                            <input type="checkbox" name="preferences[low_stock_alerts]" value="1" class="ui-toggle" <?= !empty($notification_settings['low_stock_alerts']) ? 'checked' : '' ?>>
                                        </div>
                                    </div>
                                </div>
                                <div class="mt-8 flex justify-end"><button type="submit" onclick="setButtonLoading(this, true)" class="px-8 py-3 bg-gradient-to-r from-amber-500 to-orange-600 text-white font-bold rounded-xl shadow-lg transition-all"><?= lang('UI.settingsSavePreferences') ?></button></div>
                            </form>
                        </div>
                    </div>
                </div>
                        </div>
                    </div>
                </div>

            </div> <!-- End Main Content Area -->
        </div>
    </div>
</div>

<div id="confirm-modal" class="fixed inset-0 bg-black/50 backdrop-blur-sm z-50 hidden items-center justify-center p-4">
    <div class="bg-white rounded-2xl shadow-2xl max-w-md w-full p-8">
        <div class="flex items-start space-x-4 mb-6">
            <div id="confirm-modal-icon" class="w-12 h-12 bg-red-100 rounded-xl flex items-center justify-center flex-shrink-0">
                <i class="fas fa-exclamation-triangle text-red-600 text-xl"></i>
            </div>
            <div>
                <h3 id="confirm-modal-title" class="text-xl font-bold text-gray-900 mb-1"><?= lang('UI.confirmAreYouSure') ?></h3>
                <p id="confirm-modal-message" class="text-gray-500 text-sm"><?= lang('UI.confirmCannotUndo') ?></p>
            </div>
        </div>
        <div class="flex justify-end space-x-3">
            <button type="button" onclick="closeConfirmModal()" class="px-6 py-2.5 bg-white border border-gray-200 text-gray-700 font-semibold rounded-xl hover:bg-gray-50 transition-all">
                <?= lang('UI.cancel') ?>
            </button>
            <button id="confirm-modal-confirm-btn" type="button" class="px-6 py-2.5 bg-red-600 text-white font-semibold rounded-xl hover:bg-red-700 transition-all" onclick="confirmModalProceed()">
                <?= lang('UI.confirm') ?>
            </button>
        </div>
    </div>
</div>

<script>
const csrfHeader = '<?= config('Security')->headerName ?>';
const csrfToken = '<?= csrf_hash() ?>';
let confirmModalAction = null;
const settingsI18n = {
    saving: '<?= esc(lang('UI.saving'), 'js') ?>',
    fieldRequired: '<?= esc(lang('UI.validationFieldRequired'), 'js') ?>',
    validationEmail: '<?= esc(lang('UI.validationEmail'), 'js') ?>',
    validationUrl: '<?= esc(lang('UI.validationUrl'), 'js') ?>',
    tabClinic: '<?= esc(lang('UI.settingsClinicProfile'), 'js') ?>',
    tabSubscription: '<?= esc(lang('UI.settingsSubscription'), 'js') ?>',
    tabSystem: '<?= esc(lang('UI.settingsSystemTab'), 'js') ?>',
    tabHours: '<?= esc(lang('UI.settingsOperations'), 'js') ?>',
    tabKiosk: '<?= esc(lang('UI.settingsKiosk'), 'js') ?>',
    tabBackup: '<?= esc(lang('UI.settingsBackupRestore'), 'js') ?>',
    tabCommunications: '<?= esc(lang('UI.settingsCommunications'), 'js') ?>',
    subChannels: '<?= esc(lang('UI.settingsChannels'), 'js') ?>',
    subEmail: '<?= esc(lang('UI.settingsEmail'), 'js') ?>',
    subSms: '<?= esc(lang('UI.settingsSms'), 'js') ?>',
    subWhatsapp: '<?= esc(lang('UI.settingsWhatsApp'), 'js') ?>',
    subAlerts: '<?= esc(lang('UI.settingsMyAlerts'), 'js') ?>',
    confirmAreYouSure: '<?= esc(lang('UI.confirmAreYouSure'), 'js') ?>',
    confirmText: '<?= esc(lang('UI.confirm'), 'js') ?>',
    toastSaved: '<?= esc(lang('UI.toastSaved'), 'js') ?>',
    toastError: '<?= esc(lang('UI.toastError'), 'js') ?>',
    toastSomethingWrong: '<?= esc(lang('UI.toastSomethingWrong'), 'js') ?>',
    toastKioskLinkNotAvailable: '<?= esc(lang('UI.toastKioskLinkNotAvailable'), 'js') ?>',
    toastKioskLinkCopied: '<?= esc(lang('UI.toastKioskLinkCopied'), 'js') ?>',
    toastTestSent: '<?= esc(lang('UI.toastTestSent'), 'js') ?>',
    toastTestFailed: '<?= esc(lang('UI.toastTestFailed'), 'js') ?>',
    toastUpdated: '<?= esc(lang('UI.toastUpdated'), 'js') ?>',
    pinMismatch: '<?= esc(lang('UI.validationPinMismatch'), 'js') ?>',
};

function setButtonLoading(btn, loading) {
    if (!btn) return;
    if (loading) {
        // Prevent "stuck on Saving..." when browser validation blocks submit.
        if (btn.form && !btn.form.checkValidity()) {
            btn.form.reportValidity();
            return;
        }
        btn.dataset.originalText = btn.innerHTML;
        btn.innerHTML = `<i class="fas fa-spinner fa-spin mr-2"></i>${settingsI18n.saving}`;
        btn.disabled = true;
        btn.classList.add('opacity-75', 'cursor-not-allowed');
    } else {
        btn.innerHTML = btn.dataset.originalText || btn.innerHTML;
        btn.disabled = false;
        btn.classList.remove('opacity-75', 'cursor-not-allowed');
    }
}

function submitSystemSettings(btn) {
    const form = document.getElementById('system-settings-form');
    if (!form) {
        return;
    }
    if (!form.checkValidity()) {
        form.reportValidity();
        return;
    }
    setButtonLoading(btn, true);
    if (typeof form.requestSubmit === 'function') {
        form.requestSubmit();
    } else {
        form.submit();
    }
}

function getFieldErrorElement(input) {
    if (!input || !input.parentNode) return null;
    let errorEl = input.parentNode.querySelector(`.field-error[data-for="${input.id || input.name}"]`);
    if (!errorEl) {
        errorEl = document.createElement('p');
        errorEl.className = 'field-error text-xs text-red-600 mt-1 hidden';
        errorEl.dataset.for = input.id || input.name || '';
        input.parentNode.insertBefore(errorEl, input.nextSibling);
    }
    return errorEl;
}

function setFieldError(input, message) {
    if (!input) return false;
    const errorEl = getFieldErrorElement(input);
    if (message) {
        input.classList.add('border-red-400');
        input.classList.remove('border-gray-200', 'border-green-400');
        if (errorEl) {
            errorEl.textContent = message;
            errorEl.classList.remove('hidden');
        }
        return false;
    }

    input.classList.remove('border-red-400');
    if ((input.value || '').trim()) {
        input.classList.add('border-green-400');
        input.classList.remove('border-gray-200');
    } else {
        input.classList.remove('border-green-400');
        input.classList.add('border-gray-200');
    }
    if (errorEl) {
        errorEl.textContent = '';
        errorEl.classList.add('hidden');
    }
    return true;
}

function validateField(input, rules = {}) {
    if (!input) return true;
    const value = (input.value || '').trim();
    let error = '';

    if (rules.required && !value) {
        error = settingsI18n.fieldRequired;
    } else if (rules.email && value && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(value)) {
        error = settingsI18n.validationEmail;
    } else if (rules.url && value && !/^https?:\/\/.+/i.test(value)) {
        error = settingsI18n.validationUrl;
    } else if (rules.port && value) {
        const port = parseInt(value, 10);
        if (Number.isNaN(port) || port < 1 || port > 65535) {
            error = 'Port must be a number between 1 and 65535.';
        }
    } else if (rules.minLength && value && value.length < rules.minLength) {
        error = `Must be at least ${rules.minLength} characters.`;
    } else if (rules.maxLength && value && value.length > rules.maxLength) {
        error = `Must be no more than ${rules.maxLength} characters.`;
    }

    return setFieldError(input, error);
}

function showSettingsTab(tabName, options = {}) {
    console.log('Switching to settings tab:', tabName);
    const communicationTabs = ['channels', 'email', 'sms', 'whatsapp', 'notifications', 'alerts'];
    const isLegacyCommunicationTab = communicationTabs.includes(tabName);
    let communicationSubTab = null;
    if (isLegacyCommunicationTab) {
        communicationSubTab = (tabName === 'notifications' || tabName === 'alerts') ? 'alerts' : tabName;
        tabName = 'communications';
    }

    if (!document.getElementById(tabName + '-tab')) {
        tabName = 'clinic';
    }

    document.querySelectorAll('.settings-tab').forEach(tab => {
        tab.classList.add('hidden');
    });

    document.querySelectorAll('[id^="tab-"]').forEach(btn => {
        btn.classList.remove('bg-gradient-to-r', 'from-blue-500', 'to-blue-600', 'text-white', 'shadow-lg');
        btn.classList.add('text-gray-700', 'hover:bg-gray-100', 'hover:text-gray-900');
    });

    const targetTab = document.getElementById(tabName + '-tab');
    if (targetTab) {
        targetTab.classList.remove('hidden');
    }

    const activeButton = document.getElementById('tab-' + tabName);
    if (activeButton) {
        activeButton.classList.remove('text-gray-700', 'hover:bg-gray-100', 'hover:text-gray-900');
        activeButton.classList.add('bg-gradient-to-r', 'from-blue-500', 'to-blue-600', 'text-white', 'shadow-lg');
    }

    const tabNames = {
        clinic: settingsI18n.tabClinic,
        subscription: settingsI18n.tabSubscription,
        system: settingsI18n.tabSystem,
        hours: settingsI18n.tabHours,
        kiosk: settingsI18n.tabKiosk,
        backup: settingsI18n.tabBackup,
        communications: settingsI18n.tabCommunications,
    };
    const breadcrumbTab = document.getElementById('breadcrumb-tab');
    const breadcrumbSubTab = document.getElementById('breadcrumb-subtab');
    const sep2 = document.getElementById('breadcrumb-sep-2');
    const sep3 = document.getElementById('breadcrumb-sep-3');
    if (breadcrumbTab) {
        breadcrumbTab.textContent = tabNames[tabName] || '';
    }
    if (sep2) {
        sep2.style.display = tabNames[tabName] ? '' : 'none';
    }
    if (tabName !== 'communications') {
        if (breadcrumbSubTab) breadcrumbSubTab.textContent = '';
        if (sep3) sep3.style.display = 'none';
    }

    if (tabName === 'communications') {
        const params = new URLSearchParams(window.location.search);
        const requestedSubtab = communicationSubTab || params.get('subtab') || 'channels';
        showSubTab(requestedSubtab, { updateUrl: false });
    }

    if (options.updateUrl === false) {
        return;
    }

    const url = new URL(window.location.href);
    if (tabName === 'communications') {
        url.searchParams.set('tab', 'communications');
        url.searchParams.set('subtab', communicationSubTab || url.searchParams.get('subtab') || 'channels');
    } else {
        url.searchParams.set('tab', tabName);
        url.searchParams.delete('subtab');
    }
    history.pushState(null, '', url.toString());
}

function showSubTab(name, options = {}) {
    const normalizedName = (name === 'notifications' || name === 'alerts') ? 'alerts' : name;
    document.querySelectorAll('.sub-tab-panel').forEach(panel => panel.classList.add('hidden'));
    document.querySelectorAll('.subtab-btn').forEach(btn => {
        btn.classList.remove('bg-blue-50', 'text-blue-600', 'border-b-2', 'border-blue-600');
        btn.classList.add('text-gray-500', 'hover:text-gray-700');
    });

    document.getElementById('sub-' + normalizedName)?.classList.remove('hidden');
    const activeBtn = document.getElementById('subtab-' + normalizedName);
    if (activeBtn) {
        activeBtn.classList.add('bg-blue-50', 'text-blue-600', 'border-b-2', 'border-blue-600');
        activeBtn.classList.remove('text-gray-500', 'hover:text-gray-700');
    }

    const subNames = {
        channels: settingsI18n.subChannels,
        email: settingsI18n.subEmail,
        sms: settingsI18n.subSms,
        whatsapp: settingsI18n.subWhatsapp,
        alerts: settingsI18n.subAlerts,
    };
    const breadcrumbSubTab = document.getElementById('breadcrumb-subtab');
    const sep3 = document.getElementById('breadcrumb-sep-3');
    if (breadcrumbSubTab) {
        breadcrumbSubTab.textContent = subNames[normalizedName] || '';
    }
    if (sep3) {
        sep3.style.display = subNames[normalizedName] ? '' : 'none';
    }

    if (options.updateUrl === false) {
        return;
    }

    const url = new URL(window.location.href);
    url.searchParams.set('tab', 'communications');
    url.searchParams.set('subtab', normalizedName);
    history.pushState(null, '', url.toString());
}

function toggleChannel(clinicId, type, enabled, buttonElement = null) {
    fetch('<?= base_url('settings/updateChannelStatus') ?>', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded', 'X-Requested-With': 'XMLHttpRequest', [csrfHeader]: csrfToken },
        body: `clinic_id=${clinicId}&channel_type=${type}&enabled=${enabled}`
    }).then(res => res.json()).then(data => {
        if (!data.success) {
            window.showToast?.('error', 'Error', data.message || 'Operation failed. Please try again.');
            return;
        }

        const isNowEnabled = enabled === 1;
        if (buttonElement) {
            buttonElement.className = `w-10 h-6 rounded-full transition-all ${isNowEnabled ? 'bg-green-500' : 'bg-gray-300'}`;
            const knob = buttonElement.querySelector('div');
            if (knob) {
                knob.className = `w-4 h-4 bg-white rounded-full transition-all ml-1 ${isNowEnabled ? 'translate-x-4' : ''}`;
            }
            buttonElement.setAttribute('onclick', `toggleChannel(${clinicId}, '${type}', ${isNowEnabled ? 0 : 1}, this)`);
        }
        window.showToast?.('success', 'Updated', 'Channel status updated successfully.');
    }).catch(() => {
        window.showToast?.('error', 'Error', 'Operation failed. Please try again.');
    });
}

function saveEmailConfig(event) {
    const btn = event?.currentTarget || null;
    setButtonLoading(btn, true);
    const config = { smtp_host: document.getElementById('smtp_host').value, smtp_port: document.getElementById('smtp_port').value, smtp_crypto: document.getElementById('smtp_crypto').value, smtp_user: document.getElementById('smtp_user').value };
    const pass = document.getElementById('smtp_pass').value; if (pass) config.smtp_pass = pass;
    fetch('<?= base_url('settings/updateChannelConfig') ?>', { method: 'POST', headers: { 'Content-Type': 'application/x-www-form-urlencoded', 'X-Requested-With': 'XMLHttpRequest', [csrfHeader]: csrfToken }, body: `channel_type=email&config_json=${encodeURIComponent(JSON.stringify(config))}&action=save_config` })
    .then(res => res.json()).then(data => { if(data.success) window.showToast?.('success', 'Saved', 'SMTP settings updated'); else window.showToast?.('error', 'Error', data.message || 'Something went wrong.'); })
    .finally(() => setButtonLoading(btn, false));
}

function saveSmsConfig(event) {
    const btn = event?.currentTarget || null;
    setButtonLoading(btn, true);
    const config = {
        provider: document.getElementById('sms_provider')?.value || '',
        sender_id: document.getElementById('sms_sender_id')?.value || '',
        base_url: document.getElementById('sms_base_url')?.value || '',
    };
    const apiKey = document.getElementById('sms_api_key')?.value || '';
    if (apiKey) {
        config.api_key = apiKey;
    }

    fetch('<?= base_url('settings/updateChannelConfig') ?>', { method: 'POST', headers: { 'Content-Type': 'application/x-www-form-urlencoded', 'X-Requested-With': 'XMLHttpRequest', [csrfHeader]: csrfToken }, body: `channel_type=sms&config_json=${encodeURIComponent(JSON.stringify(config))}&action=save_config` })
    .then(res => res.json()).then(data => { if(data.success) window.showToast?.('success', 'Saved', 'SMS settings updated'); else window.showToast?.('error', 'Error', data.message || 'Something went wrong.'); })
    .finally(() => setButtonLoading(btn, false));
}

function saveWhatsAppConfig(event) {
    const btn = event?.currentTarget || null;
    setButtonLoading(btn, true);
    const messageTemplates = { appointment_reminder: document.getElementById('tmpl_appointment_reminder').value, appointment_confirmation: document.getElementById('tmpl_appointment_confirmation').value, follow_up: document.getElementById('tmpl_follow_up').value, payment_reminder: document.getElementById('tmpl_payment_reminder').value };
    const config = { phone_number_id: document.getElementById('wa_phone_number_id').value, message_templates: messageTemplates };
    fetch('<?= base_url('settings/updateChannelConfig') ?>', { method: 'POST', headers: { 'Content-Type': 'application/x-www-form-urlencoded', 'X-Requested-With': 'XMLHttpRequest', [csrfHeader]: csrfToken }, body: `channel_type=whatsapp&config_json=${encodeURIComponent(JSON.stringify(config))}&action=save_config` })
    .then(res => res.json()).then(data => { if(data.success) window.showToast?.('success', settingsI18n.toastSaved, data.message || ''); else window.showToast?.('error', settingsI18n.toastError, data.message || settingsI18n.toastSomethingWrong); })
    .finally(() => setButtonLoading(btn, false));
}

function resetTemplatesToDefault() {
    const defaults = { appointment_reminder: "تذكير من العيادة: لديكم موعد بتاريخ {{appointment_date}} الساعة {{appointment_time}}.", appointment_confirmation: "تم تأكيد موعدكم بتاريخ {{appointment_date}} الساعة {{appointment_time}}.", follow_up: "نأمل أنكم بخير. هذه متابعة من العيادة بخصوص زيارتكم الأخيرة.", payment_reminder: "تذكير من العيادة: يوجد رصيد مستحق بقيمة {{amount}}." };
    Object.entries(defaults).forEach(([k,v]) => { const el = document.getElementById('tmpl_' + k); if(el) el.value = v; });
    window.showToast?.('success', 'Updated', 'Default templates loaded. Click Save to apply.');
}

function copyKioskLink() {
    const input = document.getElementById('kioskLinkInput');
    if (!input || !input.value) {
        window.showToast?.('error', settingsI18n.toastError, settingsI18n.toastKioskLinkNotAvailable);
        return;
    }

    if (navigator.clipboard?.writeText) {
        navigator.clipboard.writeText(input.value)
            .then(() => window.showToast?.('success', settingsI18n.toastSaved, settingsI18n.toastKioskLinkCopied))
            .catch(() => window.showToast?.('error', 'Error', 'Unable to copy kiosk link.'));
        return;
    }

    input.focus();
    input.select();
    document.execCommand('copy');
    window.getSelection()?.removeAllRanges();
    window.showToast?.('success', settingsI18n.toastSaved, settingsI18n.toastKioskLinkCopied);
}

function testWhatsAppChannel() {
    fetch('<?= base_url('settings/testWhatsAppChannel') ?>', { method: 'POST', headers: { [csrfHeader]: csrfToken, 'X-Requested-With': 'XMLHttpRequest' } })
    .then(res => res.json()).then(data => {
        if (data.success) {
            window.showToast?.('success', settingsI18n.toastTestSent, data.message || '');
            return;
        }
        window.showToast?.('error', settingsI18n.toastTestFailed, data.message || settingsI18n.toastSomethingWrong);
    }).catch(() => {
        window.showToast?.('error', settingsI18n.toastTestFailed, settingsI18n.toastSomethingWrong);
    });
}

function downloadBackup(filename) { window.location.href = `<?= base_url('settings/download-backup/') ?>${filename}`; }
function restoreBackup(filename) {
    openConfirmModal({
        title: 'Restore Backup?',
        message: 'This will overwrite your current data with the selected backup. This action cannot be undone.',
        confirmText: 'Restore',
        onConfirm: () => {
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = '<?= base_url('settings/restore') ?>';
            const csrfInput = document.createElement('input');
            csrfInput.type = 'hidden';
            csrfInput.name = csrfHeader;
            csrfInput.value = csrfToken;
            form.appendChild(csrfInput);
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'backup_file';
            input.value = filename;
            form.appendChild(input);
            document.body.appendChild(form);
            form.submit();
        },
    });
}

function openConfirmModal(options = {}) {
    const modal = document.getElementById('confirm-modal');
    if (!modal) return;

    const title = document.getElementById('confirm-modal-title');
    const message = document.getElementById('confirm-modal-message');
    const confirmBtn = document.getElementById('confirm-modal-confirm-btn');
    const iconWrap = document.getElementById('confirm-modal-icon');
    const icon = iconWrap?.querySelector('i');

    if (title) title.textContent = options.title || settingsI18n.confirmAreYouSure;
    if (message) message.textContent = options.message || 'This action cannot be undone.';
    if (confirmBtn) confirmBtn.textContent = options.confirmText || settingsI18n.confirmText;
    if (iconWrap) iconWrap.className = 'w-12 h-12 bg-red-100 rounded-xl flex items-center justify-center flex-shrink-0';
    if (icon) icon.className = 'fas fa-exclamation-triangle text-red-600 text-xl';

    confirmModalAction = typeof options.onConfirm === 'function' ? options.onConfirm : null;
    modal.classList.remove('hidden');
    modal.classList.add('flex');
}

function closeConfirmModal() {
    const modal = document.getElementById('confirm-modal');
    if (!modal) return;
    modal.classList.remove('flex');
    modal.classList.add('hidden');
    confirmModalAction = null;
}

function confirmModalProceed() {
    const action = confirmModalAction;
    closeConfirmModal();
    if (typeof action === 'function') {
        action();
    }
}

document.addEventListener('DOMContentLoaded', function() {
    const confirmModal = document.getElementById('confirm-modal');
    if (confirmModal) {
        confirmModal.addEventListener('click', function(event) {
            if (event.target === confirmModal) {
                closeConfirmModal();
            }
        });
    }

    document.addEventListener('keydown', function(event) {
        if (event.key === 'Escape') {
            closeConfirmModal();
        }
    });

    const kioskToggle = document.querySelector('.kiosk-toggle');
    if (kioskToggle) {
        const kioskForm = kioskToggle.closest('form');
        kioskToggle.addEventListener('change', function() {
            if (!kioskForm) {
                return;
            }

            const formData = new FormData(kioskForm);
            formData.set('kiosk_enabled', kioskToggle.checked ? '1' : '0');

            fetch(kioskForm.action, {
                method: 'POST',
                headers: { 'X-Requested-With': 'XMLHttpRequest' },
                body: formData
            }).then(async (response) => {
                if (response.ok) {
                    window.showToast?.('success', settingsI18n.toastUpdated, data.message || '');
                    return;
                }

                kioskToggle.checked = !kioskToggle.checked;
                const payload = await response.json().catch(() => null);
                window.showToast?.('error', 'Error', payload?.message || 'Failed to update kiosk status.');
            }).catch(() => {
                kioskToggle.checked = !kioskToggle.checked;
                window.showToast?.('error', 'Error', 'Failed to update kiosk status.');
            });
        });
    }

    // Keep native selects to preserve built-in required/validity UX for settings forms.
    const smsConfig = <?= json_encode($sms_config ?? [], JSON_UNESCAPED_UNICODE) ?>;
    const smsProvider = document.getElementById('sms_provider');
    const smsSenderId = document.getElementById('sms_sender_id');
    const smsBaseUrl = document.getElementById('sms_base_url');
    const smsBaseUrlWrap = document.getElementById('sms_base_url_wrap');
    if (smsProvider && smsSenderId && smsBaseUrl && smsBaseUrlWrap) {
        if (smsConfig.provider) smsProvider.value = smsConfig.provider;
        if (smsConfig.sender_id) smsSenderId.value = smsConfig.sender_id;
        if (smsConfig.base_url) smsBaseUrl.value = smsConfig.base_url;

        const syncSmsBaseUrlVisibility = () => {
            const isCustom = smsProvider.value === 'custom';
            smsBaseUrlWrap.classList.toggle('hidden', !isCustom);
            smsBaseUrl.disabled = !isCustom;
        };
        syncSmsBaseUrlVisibility();
        smsProvider.addEventListener('change', syncSmsBaseUrlVisibility);
    }

    const clinicEmail = document.querySelector('input[name="clinic_email"]');
    clinicEmail?.addEventListener('blur', () => validateField(clinicEmail, { required: true, email: true }));

    const clinicWebsite = document.querySelector('input[name="clinic_website"]');
    clinicWebsite?.addEventListener('blur', () => validateField(clinicWebsite, { url: true }));

    const clinicName = document.querySelector('input[name="clinic_name"]');
    clinicName?.addEventListener('blur', () => validateField(clinicName, { required: true, minLength: 2 }));

    const smtpHost = document.getElementById('smtp_host');
    smtpHost?.addEventListener('blur', () => validateField(smtpHost, { required: true }));

    const smtpPort = document.getElementById('smtp_port');
    smtpPort?.addEventListener('blur', () => validateField(smtpPort, { required: true, port: true }));

    const smtpUser = document.getElementById('smtp_user');
    smtpUser?.addEventListener('blur', () => validateField(smtpUser, { email: true }));

    const kioskPin = document.querySelector('input[name="kiosk_pin"]');
    kioskPin?.addEventListener('blur', () => validateField(kioskPin, { minLength: 4, maxLength: 8 }));

    const kioskPinConfirm = document.querySelector('input[name="kiosk_pin_confirm"]');
    kioskPinConfirm?.addEventListener('blur', () => {
        const kioskPinValue = (kioskPin?.value || '').trim();
        const confirmValue = (kioskPinConfirm.value || '').trim();
        if (!confirmValue) {
            setFieldError(kioskPinConfirm, '');
            return;
        }
        if (kioskPinValue && confirmValue !== kioskPinValue) {
            setFieldError(kioskPinConfirm, settingsI18n.pinMismatch);
            return;
        }
        validateField(kioskPinConfirm, { minLength: 4, maxLength: 8 });
    });

    const params = new URLSearchParams(window.location.search);
    const requestedTab = params.get('tab');
    const requestedSubTab = params.get('subtab');
    const fallbackTab = '<?= $active_tab ?? 'clinic' ?>';
    let initialTab = requestedTab || fallbackTab;
    if (requestedTab === 'communications' || ['channels', 'email', 'sms', 'whatsapp', 'notifications', 'alerts'].includes(initialTab)) {
        initialTab = 'communications';
    }
    showSettingsTab(initialTab, { updateUrl: false });
    if (initialTab === 'communications') {
        showSubTab(requestedSubTab || (fallbackTab === 'notifications' ? 'alerts' : fallbackTab) || 'channels', { updateUrl: false });
    }
});

window.addEventListener('popstate', function() {
    const params = new URLSearchParams(window.location.search);
    const requestedTab = params.get('tab');
    const requestedSubTab = params.get('subtab');
    let targetTab = requestedTab || 'clinic';
    if (requestedTab === 'communications') {
        targetTab = 'communications';
    }
    showSettingsTab(targetTab, { updateUrl: false });
    if (targetTab === 'communications') {
        showSubTab(requestedSubTab || 'channels', { updateUrl: false });
    }
});
</script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<?= $this->endSection() ?>
