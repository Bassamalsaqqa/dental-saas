<?= $this->extend('layouts/main_control_plane') ?>

<?= $this->section('content') ?>
<div class="min-h-screen bg-gradient-to-br from-slate-50 via-gray-50 to-zinc-100 relative overflow-hidden">
    <div class="absolute inset-0 overflow-hidden pointer-events-none">
        <div class="absolute -top-40 -right-40 w-80 h-80 bg-gradient-to-br from-slate-400/10 to-gray-600/10 rounded-full blur-3xl animate-pulse"></div>
        <div class="absolute -bottom-40 -left-40 w-80 h-80 bg-gradient-to-br from-slate-400/10 to-zinc-600/10 rounded-full blur-3xl animate-pulse delay-1000"></div>
    </div>

    <div class="container mx-auto relative z-10 px-4 py-6">
        <div class="mb-8">
            <div class="bg-white/80 backdrop-blur-sm border border-white/30 rounded-xl p-6 shadow-lg">
                <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-6">
                    <div class="flex items-center space-x-4">
                        <div class="w-12 h-12 bg-gradient-to-br from-slate-700 to-gray-800 rounded-xl flex items-center justify-center text-white shadow-lg">
                            <i class="fas fa-cogs text-xl"></i>
                        </div>
                        <div>
                            <h1 class="text-3xl font-bold text-gray-900">Global Configuration</h1>
                            <p class="text-gray-600">Control plane policies and system-wide defaults</p>
                        </div>
                    </div>
                    <div class="flex flex-wrap gap-3">
                        <span class="px-3 py-1 rounded-full bg-emerald-100 text-emerald-700 text-xs font-semibold">Global Mode</span>
                        <span class="px-3 py-1 rounded-full bg-slate-100 text-slate-600 text-xs font-semibold">Live Settings</span>
                    </div>
                </div>
            </div>
        </div>

        <nav class="mb-4 flex items-center space-x-1 text-sm text-gray-500" id="control-settings-breadcrumb">
            <span class="text-gray-700 font-medium">Control Plane</span>
            <i class="fas fa-chevron-right text-xs text-gray-300"></i>
            <span class="text-gray-700 font-medium">Settings</span>
            <i class="fas fa-chevron-right text-xs text-gray-300"></i>
            <span id="control-breadcrumb-tab" class="text-gray-900 font-semibold"></span>
        </nav>

        <div class="flex flex-col lg:flex-row gap-8">
            <div class="lg:w-72 flex-shrink-0">
                <div class="bg-white/90 backdrop-blur-xl rounded-2xl shadow-xl border border-white/50 p-4 sticky top-6">
                    <h3 class="text-xs font-bold text-slate-400 uppercase tracking-widest mb-4 px-2">Settings Menu</h3>
                    <nav class="space-y-1">
                        <button onclick="showTab('platform')" id="nav-platform" class="w-full flex items-center px-4 py-3 text-left text-sm font-medium rounded-xl transition-all duration-200 bg-gradient-to-r from-slate-700 to-gray-800 text-white shadow-md">
                            <i class="fas fa-id-badge w-5 h-5 mr-3 text-slate-300"></i>
                            Platform Profile
                        </button>
                        <button onclick="showTab('clinic-defaults')" id="nav-clinic-defaults" class="w-full flex items-center px-4 py-3 text-left text-sm font-medium rounded-xl transition-all duration-200 text-slate-600 hover:bg-slate-50 hover:text-slate-900">
                            <i class="fas fa-clinic-medical w-5 h-5 mr-3 text-slate-400"></i>
                            Clinic Defaults
                        </button>
                        <button onclick="showTab('governance')" id="nav-governance" class="w-full flex items-center px-4 py-3 text-left text-sm font-medium rounded-xl transition-all duration-200 text-slate-600 hover:bg-slate-50 hover:text-slate-900">
                            <i class="fas fa-shield-alt w-5 h-5 mr-3 text-slate-400"></i>
                            Governance
                        </button>
                    </nav>
                </div>
            </div>

            <div class="flex-1">
                <div id="tab-platform" class="settings-content">
                    <div class="bg-white rounded-2xl shadow-xl border border-slate-200 overflow-hidden">
                        <div class="bg-gradient-to-r from-slate-700 to-gray-800 p-8">
                            <div class="flex items-center space-x-4">
                                <div class="w-12 h-12 bg-white/10 rounded-xl flex items-center justify-center backdrop-blur-sm">
                                    <i class="fas fa-id-badge text-blue-400 text-xl"></i>
                                </div>
                                <div>
                                    <h3 class="text-xl font-bold text-white">Platform Profile</h3>
                                    <p class="text-slate-300 text-sm">Support identity used across control plane surfaces</p>
                                </div>
                            </div>
                        </div>
                        <div class="p-8">
                            <form action="<?= base_url('controlplane/settings/platform') ?>" method="POST" class="space-y-6">
                                <?= csrf_field() ?>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <div class="md:col-span-2">
                                        <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Platform Name</label>
                                        <input type="text" name="platform_name" value="<?= esc($settings['platform_name'] ?? '') ?>" class="w-full px-4 py-3 bg-white border border-slate-200 rounded-xl text-slate-700 font-medium focus:border-slate-500 focus:ring-4 focus:ring-slate-200/60 transition-all" required>
                                        <p class="text-xs text-slate-400 mt-1">Shown in the control plane header and support email footers.</p>
                                    </div>
                                    <div>
                                        <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Support Email</label>
                                        <input type="email" name="platform_support_email" value="<?= esc($settings['platform_support_email'] ?? '') ?>" placeholder="support@yourdomain.com" class="w-full px-4 py-3 bg-white border border-slate-200 rounded-xl text-slate-700 font-medium focus:border-slate-500 focus:ring-4 focus:ring-slate-200/60 transition-all">
                                        <p class="text-xs text-slate-400 mt-1">Used as the reply-to address in system-generated emails to clinic admins.</p>
                                    </div>
                                    <div>
                                        <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Support Phone</label>
                                        <input type="text" name="platform_support_phone" value="<?= esc($settings['platform_support_phone'] ?? '') ?>" placeholder="+970 000 000 000" class="w-full px-4 py-3 bg-white border border-slate-200 rounded-xl text-slate-700 font-medium focus:border-slate-500 focus:ring-4 focus:ring-slate-200/60 transition-all">
                                        <p class="text-xs text-slate-400 mt-1">Displayed on the platform support page accessible from the clinic dashboard.</p>
                                    </div>
                                    <div>
                                        <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">WhatsApp (Primary)</label>
                                        <input type="text" name="platform_support_whatsapp_primary" value="<?= esc($settings['platform_support_whatsapp_primary'] ?? '') ?>" placeholder="+970 000 000 000" class="w-full px-4 py-3 bg-white border border-slate-200 rounded-xl text-slate-700 font-medium focus:border-slate-500 focus:ring-4 focus:ring-slate-200/60 transition-all">
                                        <p class="text-xs text-slate-400 mt-1">Primary WhatsApp number for support requests from clinics.</p>
                                    </div>
                                    <div>
                                        <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">WhatsApp (Secondary)</label>
                                        <input type="text" name="platform_support_whatsapp_secondary" value="<?= esc($settings['platform_support_whatsapp_secondary'] ?? '') ?>" placeholder="+972 000 000 000" class="w-full px-4 py-3 bg-white border border-slate-200 rounded-xl text-slate-700 font-medium focus:border-slate-500 focus:ring-4 focus:ring-slate-200/60 transition-all">
                                        <p class="text-xs text-slate-400 mt-1">Fallback contact shown when the primary number is unreachable.</p>
                                    </div>
                                    <div class="md:col-span-2">
                                        <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Support Website</label>
                                        <input type="url" name="platform_support_website" value="<?= esc($settings['platform_support_website'] ?? '') ?>" placeholder="https://yourdomain.com" class="w-full px-4 py-3 bg-white border border-slate-200 rounded-xl text-slate-700 font-medium focus:border-slate-500 focus:ring-4 focus:ring-slate-200/60 transition-all">
                                        <p class="text-xs text-slate-400 mt-1">Link to your support portal or documentation site. Include https://.</p>
                                    </div>
                                </div>
                                <div class="flex justify-end">
                                    <button type="submit" class="px-6 py-3 bg-gradient-to-r from-slate-700 to-gray-800 text-white font-semibold rounded-xl shadow-lg hover:from-slate-800 hover:to-gray-900 transition-all">
                                        <i class="fas fa-save mr-2"></i>Save Profile
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <div id="tab-clinic-defaults" class="settings-content hidden">
                    <form action="<?= base_url('controlplane/settings/tenant-defaults') ?>" method="POST" class="space-y-8">
                        <?= csrf_field() ?>
                        <div class="bg-white/90 backdrop-blur-xl rounded-2xl shadow-xl border border-white/50 overflow-hidden">
                            <div class="bg-gradient-to-r from-slate-700 to-slate-900 p-8">
                                <div class="flex items-center space-x-4">
                                    <div class="w-12 h-12 bg-white/20 rounded-xl flex items-center justify-center backdrop-blur-sm">
                                        <i class="fas fa-cog text-emerald-400 text-xl"></i>
                                    </div>
                                    <div>
                                        <h3 class="text-2xl font-bold text-white">System Preference</h3>
                                        <p class="text-slate-300 text-sm">Defaults applied when a new clinic is onboarded</p>
                                    </div>
                                </div>
                            </div>

                            <div class="p-8">
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <div>
                                        <label class="block text-sm font-semibold text-gray-700 mb-2">Timezone</label>
                                        <select name="default_timezone" class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:border-green-500 focus:ring-4 focus:ring-green-100 transition-all duration-200" required>
                                            <?php $defaultTimezone = $settings['default_timezone'] ?? 'Asia/Jerusalem'; ?>
                                            <option value="UTC" <?= $defaultTimezone === 'UTC' ? 'selected' : '' ?>>UTC</option>
                                            <option value="America/New_York" <?= $defaultTimezone === 'America/New_York' ? 'selected' : '' ?>>Eastern Time (ET)</option>
                                            <option value="America/Chicago" <?= $defaultTimezone === 'America/Chicago' ? 'selected' : '' ?>>Central Time (CT)</option>
                                            <option value="America/Denver" <?= $defaultTimezone === 'America/Denver' ? 'selected' : '' ?>>Mountain Time (MT)</option>
                                            <option value="America/Los_Angeles" <?= $defaultTimezone === 'America/Los_Angeles' ? 'selected' : '' ?>>Pacific Time (PT)</option>
                                            <option value="America/Anchorage" <?= $defaultTimezone === 'America/Anchorage' ? 'selected' : '' ?>>Alaska Time (AKT)</option>
                                            <option value="Pacific/Honolulu" <?= $defaultTimezone === 'Pacific/Honolulu' ? 'selected' : '' ?>>Hawaii Time (HST)</option>
                                            <option value="Europe/London" <?= $defaultTimezone === 'Europe/London' ? 'selected' : '' ?>>London (GMT/BST)</option>
                                            <option value="Europe/Paris" <?= $defaultTimezone === 'Europe/Paris' ? 'selected' : '' ?>>Paris (CET/CEST)</option>
                                            <option value="Europe/Berlin" <?= $defaultTimezone === 'Europe/Berlin' ? 'selected' : '' ?>>Berlin (CET/CEST)</option>
                                            <option value="Europe/Rome" <?= $defaultTimezone === 'Europe/Rome' ? 'selected' : '' ?>>Rome (CET/CEST)</option>
                                            <option value="Europe/Madrid" <?= $defaultTimezone === 'Europe/Madrid' ? 'selected' : '' ?>>Madrid (CET/CEST)</option>
                                            <option value="Europe/Amsterdam" <?= $defaultTimezone === 'Europe/Amsterdam' ? 'selected' : '' ?>>Amsterdam (CET/CEST)</option>
                                            <option value="Europe/Zurich" <?= $defaultTimezone === 'Europe/Zurich' ? 'selected' : '' ?>>Zurich (CET/CEST)</option>
                                            <option value="Europe/Vienna" <?= $defaultTimezone === 'Europe/Vienna' ? 'selected' : '' ?>>Vienna (CET/CEST)</option>
                                            <option value="Europe/Stockholm" <?= $defaultTimezone === 'Europe/Stockholm' ? 'selected' : '' ?>>Stockholm (CET/CEST)</option>
                                            <option value="Europe/Oslo" <?= $defaultTimezone === 'Europe/Oslo' ? 'selected' : '' ?>>Oslo (CET/CEST)</option>
                                            <option value="Europe/Copenhagen" <?= $defaultTimezone === 'Europe/Copenhagen' ? 'selected' : '' ?>>Copenhagen (CET/CEST)</option>
                                            <option value="Europe/Helsinki" <?= $defaultTimezone === 'Europe/Helsinki' ? 'selected' : '' ?>>Helsinki (EET/EEST)</option>
                                            <option value="Europe/Warsaw" <?= $defaultTimezone === 'Europe/Warsaw' ? 'selected' : '' ?>>Warsaw (CET/CEST)</option>
                                            <option value="Europe/Prague" <?= $defaultTimezone === 'Europe/Prague' ? 'selected' : '' ?>>Prague (CET/CEST)</option>
                                            <option value="Europe/Budapest" <?= $defaultTimezone === 'Europe/Budapest' ? 'selected' : '' ?>>Budapest (CET/CEST)</option>
                                            <option value="Europe/Athens" <?= $defaultTimezone === 'Europe/Athens' ? 'selected' : '' ?>>Athens (EET/EEST)</option>
                                            <option value="Europe/Istanbul" <?= $defaultTimezone === 'Europe/Istanbul' ? 'selected' : '' ?>>Istanbul (TRT)</option>
                                            <option value="Europe/Moscow" <?= $defaultTimezone === 'Europe/Moscow' ? 'selected' : '' ?>>Moscow (MSK)</option>
                                            <option value="Asia/Tokyo" <?= $defaultTimezone === 'Asia/Tokyo' ? 'selected' : '' ?>>Tokyo (JST)</option>
                                            <option value="Asia/Shanghai" <?= $defaultTimezone === 'Asia/Shanghai' ? 'selected' : '' ?>>Shanghai (CST)</option>
                                            <option value="Asia/Hong_Kong" <?= $defaultTimezone === 'Asia/Hong_Kong' ? 'selected' : '' ?>>Hong Kong (HKT)</option>
                                            <option value="Asia/Singapore" <?= $defaultTimezone === 'Asia/Singapore' ? 'selected' : '' ?>>Singapore (SGT)</option>
                                            <option value="Asia/Seoul" <?= $defaultTimezone === 'Asia/Seoul' ? 'selected' : '' ?>>Seoul (KST)</option>
                                            <option value="Asia/Taipei" <?= $defaultTimezone === 'Asia/Taipei' ? 'selected' : '' ?>>Taipei (CST)</option>
                                            <option value="Asia/Bangkok" <?= $defaultTimezone === 'Asia/Bangkok' ? 'selected' : '' ?>>Bangkok (ICT)</option>
                                            <option value="Asia/Jakarta" <?= $defaultTimezone === 'Asia/Jakarta' ? 'selected' : '' ?>>Jakarta (WIB)</option>
                                            <option value="Asia/Kolkata" <?= $defaultTimezone === 'Asia/Kolkata' ? 'selected' : '' ?>>Kolkata (IST)</option>
                                            <option value="Asia/Dubai" <?= $defaultTimezone === 'Asia/Dubai' ? 'selected' : '' ?>>Dubai (GST)</option>
                                            <option value="Asia/Riyadh" <?= $defaultTimezone === 'Asia/Riyadh' ? 'selected' : '' ?>>Riyadh (AST)</option>
                                            <option value="Asia/Jerusalem" <?= $defaultTimezone === 'Asia/Jerusalem' ? 'selected' : '' ?>>Jerusalem (IST/IDT)</option>
                                            <option value="Asia/Gaza" <?= $defaultTimezone === 'Asia/Gaza' ? 'selected' : '' ?>>Gaza (EET/EEST)</option>
                                            <option value="Asia/Tehran" <?= $defaultTimezone === 'Asia/Tehran' ? 'selected' : '' ?>>Tehran (IRST)</option>
                                            <option value="Asia/Karachi" <?= $defaultTimezone === 'Asia/Karachi' ? 'selected' : '' ?>>Karachi (PKT)</option>
                                            <option value="Asia/Dhaka" <?= $defaultTimezone === 'Asia/Dhaka' ? 'selected' : '' ?>>Dhaka (BST)</option>
                                            <option value="Australia/Sydney" <?= $defaultTimezone === 'Australia/Sydney' ? 'selected' : '' ?>>Sydney (AEST/AEDT)</option>
                                            <option value="Australia/Melbourne" <?= $defaultTimezone === 'Australia/Melbourne' ? 'selected' : '' ?>>Melbourne (AEST/AEDT)</option>
                                            <option value="Australia/Brisbane" <?= $defaultTimezone === 'Australia/Brisbane' ? 'selected' : '' ?>>Brisbane (AEST)</option>
                                            <option value="Australia/Perth" <?= $defaultTimezone === 'Australia/Perth' ? 'selected' : '' ?>>Perth (AWST)</option>
                                            <option value="Australia/Adelaide" <?= $defaultTimezone === 'Australia/Adelaide' ? 'selected' : '' ?>>Adelaide (ACST/ACDT)</option>
                                            <option value="Australia/Darwin" <?= $defaultTimezone === 'Australia/Darwin' ? 'selected' : '' ?>>Darwin (ACST)</option>
                                            <option value="Pacific/Auckland" <?= $defaultTimezone === 'Pacific/Auckland' ? 'selected' : '' ?>>Auckland (NZST/NZDT)</option>
                                            <option value="Pacific/Fiji" <?= $defaultTimezone === 'Pacific/Fiji' ? 'selected' : '' ?>>Fiji (FJT)</option>
                                            <option value="America/Sao_Paulo" <?= $defaultTimezone === 'America/Sao_Paulo' ? 'selected' : '' ?>>S�o Paulo (BRT)</option>
                                            <option value="America/Buenos_Aires" <?= $defaultTimezone === 'America/Buenos_Aires' ? 'selected' : '' ?>>Buenos Aires (ART)</option>
                                            <option value="America/Lima" <?= $defaultTimezone === 'America/Lima' ? 'selected' : '' ?>>Lima (PET)</option>
                                            <option value="America/Bogota" <?= $defaultTimezone === 'America/Bogota' ? 'selected' : '' ?>>Bogot� (COT)</option>
                                            <option value="America/Mexico_City" <?= $defaultTimezone === 'America/Mexico_City' ? 'selected' : '' ?>>Mexico City (CST/CDT)</option>
                                            <option value="America/Toronto" <?= $defaultTimezone === 'America/Toronto' ? 'selected' : '' ?>>Toronto (EST/EDT)</option>
                                            <option value="America/Vancouver" <?= $defaultTimezone === 'America/Vancouver' ? 'selected' : '' ?>>Vancouver (PST/PDT)</option>
                                            <option value="America/Montreal" <?= $defaultTimezone === 'America/Montreal' ? 'selected' : '' ?>>Montreal (EST/EDT)</option>
                                            <option value="Africa/Cairo" <?= $defaultTimezone === 'Africa/Cairo' ? 'selected' : '' ?>>Cairo (EET)</option>
                                            <option value="Africa/Johannesburg" <?= $defaultTimezone === 'Africa/Johannesburg' ? 'selected' : '' ?>>Johannesburg (SAST)</option>
                                            <option value="Africa/Lagos" <?= $defaultTimezone === 'Africa/Lagos' ? 'selected' : '' ?>>Lagos (WAT)</option>
                                            <option value="Africa/Casablanca" <?= $defaultTimezone === 'Africa/Casablanca' ? 'selected' : '' ?>>Casablanca (WET)</option>
                                        </select>
                                        <p class="text-xs text-slate-400 mt-1">Applied to all new clinics at onboarding. Existing clinics are unaffected.</p>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-semibold text-gray-700 mb-2">Date Format</label>
                                        <?php $defaultDateFormat = $settings['default_date_format'] ?? 'd/m/Y'; ?>
                                        <select name="default_date_format" class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:border-green-500 focus:ring-4 focus:ring-green-100 transition-all duration-200" required>
                                            <option value="Y-m-d" <?= $defaultDateFormat === 'Y-m-d' ? 'selected' : '' ?>>YYYY-MM-DD</option>
                                            <option value="m/d/Y" <?= $defaultDateFormat === 'm/d/Y' ? 'selected' : '' ?>>MM/DD/YYYY</option>
                                            <option value="d/m/Y" <?= $defaultDateFormat === 'd/m/Y' ? 'selected' : '' ?>>DD/MM/YYYY</option>
                                        </select>
                                        <p class="text-xs text-slate-400 mt-1">Controls how dates appear in patient lists, invoices, and reminders.</p>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-semibold text-gray-700 mb-2">Time Format</label>
                                        <?php $defaultTimeFormat = (string) ($settings['default_time_format'] ?? '12'); ?>
                                        <select name="default_time_format" class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:border-green-500 focus:ring-4 focus:ring-green-100 transition-all duration-200" required>
                                            <option value="24" <?= $defaultTimeFormat === '24' ? 'selected' : '' ?>>24 Hour</option>
                                            <option value="12" <?= $defaultTimeFormat === '12' ? 'selected' : '' ?>>12 Hour</option>
                                        </select>
                                        <p class="text-xs text-slate-400 mt-1">12-hour (AM/PM) or 24-hour display across all clinic interfaces.</p>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-semibold text-gray-700 mb-2">Currency</label>
                                        <?php $defaultCurrency = $settings['default_currency'] ?? 'ILS'; ?>
                                        <select name="default_currency" class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:border-green-500 focus:ring-4 focus:ring-green-100 transition-all duration-200" required>
                                            <option value="ILS" <?= $defaultCurrency == 'ILS' ? 'selected' : '' ?>>ILS - Israeli Shekel (ILS)</option>
                                            <option value="USD" <?= $defaultCurrency == 'USD' ? 'selected' : '' ?>>USD - US Dollar (USD)</option>
                                            <option value="JOD" <?= $defaultCurrency == 'JOD' ? 'selected' : '' ?>>JOD - Jordanian Dinar (JOD)</option>
                                            <option value="EUR" <?= $defaultCurrency == 'EUR' ? 'selected' : '' ?>>EUR - Euro (EUR)</option>
                                            <option value="GBP" <?= $defaultCurrency == 'GBP' ? 'selected' : '' ?>>GBP - British Pound (GBP)</option>
                                        </select>
                                        <p class="text-xs text-slate-400 mt-1">Currency used on invoices and financial reports for new clinics.</p>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-semibold text-gray-700 mb-2">Currency Position</label>
                                        <?php $defaultCurrencyPosition = $settings['default_currency_position'] ?? 'after'; ?>
                                        <select name="default_currency_position" class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:border-green-500 focus:ring-4 focus:ring-green-100 transition-all duration-200" required>
                                            <option value="before" <?= $defaultCurrencyPosition === 'before' ? 'selected' : '' ?>>Before Amount (e.g., $100)</option>
                                            <option value="after" <?= $defaultCurrencyPosition === 'after' ? 'selected' : '' ?>>After Amount (e.g., 100$)</option>
                                        </select>
                                        <p class="text-xs text-slate-400 mt-1">Whether the symbol appears before or after the amount (e.g. ₪120 vs 120 ₪).</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="bg-white/90 backdrop-blur-xl rounded-2xl shadow-xl border border-white/50 overflow-hidden">
                            <div class="bg-gradient-to-r from-slate-700 to-slate-900 p-8">
                                <div class="flex items-center space-x-4">
                                    <div class="w-12 h-12 bg-white/20 rounded-xl flex items-center justify-center backdrop-blur-sm">
                                        <i class="fas fa-clock text-emerald-400 text-xl"></i>
                                    </div>
                                    <div>
                                        <h3 class="text-2xl font-bold text-white">Working Hours</h3>
                                        <p class="text-slate-300 text-sm">Set default operating hours and working days</p>
                                    </div>
                                </div>
                            </div>

                            <div class="p-8">
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <div>
                                        <label class="block text-sm font-semibold text-gray-700 mb-2">Start Time</label>
                                        <input type="time" name="default_working_hours_start" data-clinic-time="1" value="<?= esc($settings['default_working_hours_start'] ?? '09:00') ?>" class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:border-orange-500 focus:ring-4 focus:ring-orange-100 transition-all duration-200" required>
                                        <p class="text-xs text-slate-400 mt-1">Default calendar open/close times applied to new clinics.</p>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-semibold text-gray-700 mb-2">End Time</label>
                                        <input type="time" name="default_working_hours_end" data-clinic-time="1" value="<?= esc($settings['default_working_hours_end'] ?? '17:00') ?>" class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:border-orange-500 focus:ring-4 focus:ring-orange-100 transition-all duration-200" required>
                                        <p class="text-xs text-slate-400 mt-1">Default calendar open/close times applied to new clinics.</p>
                                    </div>
                                    <div class="md:col-span-2">
                                        <label class="block text-sm font-semibold text-gray-700 mb-2">Working Days</label>
                                        <p class="text-xs text-slate-400 mt-1 mb-3">Days the clinic is open by default. Clinics can override their own schedule.</p>
                                        <?php
                                            $workingDefaults = $settings['default_working_days'] ?? ['monday', 'tuesday', 'wednesday', 'thursday', 'friday'];
                                            $days = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'];
                                            $dayLabels = ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'];
                                        ?>
                                        <div class="grid grid-cols-7 gap-2">
                                            <?php foreach ($days as $index => $day): ?>
                                                <label class="flex flex-col items-center p-3 border-2 border-gray-200 rounded-xl cursor-pointer hover:border-orange-300 transition-all duration-200 <?= in_array($day, $workingDefaults, true) ? 'border-orange-500 bg-orange-50' : '' ?>">
                                                    <input type="checkbox" name="default_working_days[]" value="<?= esc($day) ?>" <?= in_array($day, $workingDefaults, true) ? 'checked' : '' ?> class="sr-only">
                                                    <span class="text-sm font-medium text-gray-700"><?= esc($dayLabels[$index]) ?></span>
                                                </label>
                                            <?php endforeach; ?>
                                        </div>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-semibold text-gray-700 mb-2">Appointment Duration (minutes)</label>
                                        <input type="number" name="default_appointment_duration" value="<?= esc((int) ($settings['default_appointment_duration'] ?? 30)) ?>" min="15" max="480" class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:border-purple-500 focus:ring-4 focus:ring-purple-100 transition-all duration-200" required>
                                        <p class="text-xs text-slate-400 mt-1">Initial slot length for the clinic calendar in minutes. Clinics can override after onboarding.</p>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-semibold text-gray-700 mb-2">Clinic Max Chairs</label>
                                        <input type="number" name="default_booking_chair_max" value="<?= esc((int) ($settings['default_booking_chair_max'] ?? 3)) ?>" min="1" max="3" class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:border-purple-500 focus:ring-4 focus:ring-purple-100 transition-all duration-200" required>
                                        <p class="text-xs text-slate-400 mt-1">Platform-level cap on concurrent appointment slots per time slot. Cannot exceed 3.</p>
                                    </div>
                                </div>

                                <div class="mt-8 flex justify-end">
                                    <button type="submit" class="px-8 py-3 bg-gradient-to-r from-orange-500 to-amber-600 text-white font-semibold rounded-xl hover:from-orange-600 hover:to-amber-700 transition-all duration-200 shadow-lg hover:shadow-xl">
                                        <i class="fas fa-save mr-2"></i>Save Clinic Defaults
                                    </button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>

                <div id="tab-governance" class="settings-content hidden">
                    <div class="space-y-8">
                        <div class="bg-white rounded-2xl shadow-xl border border-slate-200 overflow-hidden">
                            <div class="bg-gradient-to-r from-slate-800 to-slate-900 p-8">
                                <div class="flex items-center space-x-4">
                                    <div class="w-12 h-12 bg-white/10 rounded-xl flex items-center justify-center backdrop-blur-sm">
                                        <i class="fas fa-file-shield text-white text-xl"></i>
                                    </div>
                                    <div>
                                        <h3 class="text-xl font-bold text-white">Export Retention Policy</h3>
                                        <p class="text-slate-300 text-sm">Global policy for export artifacts across all clinics</p>
                                    </div>
                                </div>
                            </div>
                            <div class="p-8 space-y-6">
                                <form action="<?= base_url('controlplane/settings/retention') ?>" method="POST" class="space-y-6">
                                    <?= csrf_field() ?>
                                    <?php $retentionMode = $settings['retention_mode'] ?? 'latest'; ?>
                                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                        <label class="relative flex flex-col p-4 border-2 rounded-xl cursor-pointer hover:bg-slate-50 transition-all <?= $retentionMode === 'latest' ? 'border-slate-700 bg-slate-50' : 'border-slate-200' ?>">
                                            <input type="radio" name="retention_mode" value="latest" class="sr-only" <?= $retentionMode === 'latest' ? 'checked' : '' ?>>
                                            <span class="font-semibold text-slate-800">Keep Latest Only</span>
                                            <span class="text-xs text-slate-500 mt-1">Supersede previous exports for each purpose.</span>
                                        </label>
                                        <label class="relative flex flex-col p-4 border-2 rounded-xl cursor-pointer hover:bg-slate-50 transition-all <?= $retentionMode === 'keep_last_n' ? 'border-slate-700 bg-slate-50' : 'border-slate-200' ?>">
                                            <input type="radio" name="retention_mode" value="keep_last_n" class="sr-only" <?= $retentionMode === 'keep_last_n' ? 'checked' : '' ?>>
                                            <span class="font-semibold text-slate-800">Keep Last N</span>
                                            <span class="text-xs text-slate-500 mt-1">Retain a fixed number of recent exports.</span>
                                        </label>
                                        <label class="relative flex flex-col p-4 border-2 rounded-xl cursor-pointer hover:bg-slate-50 transition-all <?= $retentionMode === 'keep_x_days' ? 'border-slate-700 bg-slate-50' : 'border-slate-200' ?>">
                                            <input type="radio" name="retention_mode" value="keep_x_days" class="sr-only" <?= $retentionMode === 'keep_x_days' ? 'checked' : '' ?>>
                                            <span class="font-semibold text-slate-800">Keep for X Days</span>
                                            <span class="text-xs text-slate-500 mt-1">Delete exports older than the time window.</span>
                                        </label>
                                    </div>

                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                        <div id="retention-last-n" class="<?= $retentionMode === 'keep_last_n' ? '' : 'hidden' ?>">
                                            <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Number of exports to keep</label>
                                            <input type="number" name="retention_last_n" value="<?= esc((int) ($settings['retention_last_n'] ?? 5)) ?>" min="1" max="100" class="w-full px-4 py-3 bg-white border border-slate-200 rounded-xl text-slate-700 font-medium focus:border-slate-500 focus:ring-4 focus:ring-slate-200/60 transition-all">
                                            <p class="text-xs text-slate-400 mt-1">How many export files to keep per clinic per export type.</p>
                                        </div>
                                        <div id="retention-days" class="<?= $retentionMode === 'keep_x_days' ? '' : 'hidden' ?>">
                                            <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Number of days to keep</label>
                                            <input type="number" name="retention_days" value="<?= esc((int) ($settings['retention_days'] ?? 30)) ?>" min="1" max="3650" class="w-full px-4 py-3 bg-white border border-slate-200 rounded-xl text-slate-700 font-medium focus:border-slate-500 focus:ring-4 focus:ring-slate-200/60 transition-all">
                                            <p class="text-xs text-slate-400 mt-1">Exports older than this number of days are eligible for pruning.</p>
                                        </div>
                                    </div>

                                    <div class="flex flex-wrap items-center justify-between gap-4 pt-4 border-t border-slate-100">
                                        <button type="button" id="prune-exports-btn" class="px-6 py-3 bg-white border border-slate-200 text-slate-700 font-semibold rounded-xl hover:bg-slate-50 transition-all">
                                            <i class="fas fa-broom mr-2"></i>Prune Export Artifacts
                                        </button>
                                        <button type="submit" class="px-6 py-3 bg-gradient-to-r from-slate-700 to-gray-800 text-white font-semibold rounded-xl shadow-lg hover:from-slate-800 hover:to-gray-900 transition-all">
                                            <i class="fas fa-save mr-2"></i>Save Retention Policy
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>

                        <div class="bg-white rounded-2xl shadow-xl border border-slate-200 overflow-hidden">
                            <div class="bg-gradient-to-r from-slate-700 to-slate-900 p-8">
                                <div class="flex items-center space-x-4">
                                    <div class="w-12 h-12 bg-white/10 rounded-xl flex items-center justify-center backdrop-blur-sm">
                                        <i class="fas fa-user-shield text-violet-400 text-xl"></i>
                                    </div>
                                    <div>
                                        <h3 class="text-xl font-bold text-white">Control Plane Audit Retention</h3>
                                        <p class="text-slate-300 text-sm">Retention policy for operator activity logs</p>
                                    </div>
                                </div>
                            </div>
                            <div class="p-8">
                                <form action="<?= base_url('controlplane/settings/audit-retention') ?>" method="POST" class="space-y-6">
                                    <?= csrf_field() ?>
                                    <div>
                                        <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Audit log retention window (days)</label>
                                        <input type="number" name="control_plane_audit_retention_days" value="<?= esc((int) ($settings['control_plane_audit_retention_days'] ?? 365)) ?>" min="30" max="3650" class="w-full px-4 py-3 bg-white border border-slate-200 rounded-xl text-slate-700 font-medium focus:border-indigo-500 focus:ring-4 focus:ring-indigo-200/60 transition-all">
                                        <p class="text-xs text-slate-400 mt-1">Audit log entries older than this window are pruned when you run 'Prune Audit Logs'. Minimum 30 days recommended.</p>
                                    </div>
                                    <div class="flex flex-wrap items-center justify-between gap-4 pt-4 border-t border-slate-100">
                                        <button type="button" id="prune-audits-btn" class="px-6 py-3 bg-white border border-slate-200 text-slate-700 font-semibold rounded-xl hover:bg-slate-50 transition-all">
                                            <i class="fas fa-broom mr-2"></i>Prune Audit Logs
                                        </button>
                                        <button type="submit" class="px-6 py-3 bg-gradient-to-r from-slate-700 to-gray-800 text-white font-semibold rounded-xl shadow-lg hover:from-slate-800 hover:to-gray-900 transition-all">
                                            <i class="fas fa-save mr-2"></i>Save Audit Policy
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>

                        <div class="bg-white rounded-2xl shadow-xl border border-slate-200 p-8">
                            <div class="flex items-start justify-between flex-wrap gap-6">
                                <div>
                                    <h4 class="text-lg font-bold text-slate-900">Feature Governance</h4>
                                    <p class="text-sm text-slate-600 mt-2 max-w-2xl">Feature availability is managed through Plans to avoid duplication. Use plan settings to enable or disable tenant-facing capabilities.</p>
                                </div>
                                <div class="flex flex-wrap gap-3">
                                    <a href="<?= base_url('controlplane/plans') ?>" class="px-5 py-2.5 rounded-xl bg-slate-900 text-white font-semibold hover:bg-slate-800 transition-all">
                                        <i class="fas fa-layer-group mr-2"></i>Manage Plans
                                    </a>
                                    <a href="<?= base_url('controlplane/email-templates') ?>" class="px-5 py-2.5 rounded-xl bg-slate-100 text-slate-700 font-semibold hover:bg-slate-200 transition-all">
                                        <i class="fas fa-envelope mr-2"></i>Email Templates
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div id="confirm-modal" class="fixed inset-0 bg-black/50 backdrop-blur-sm z-50 hidden items-center justify-center p-4">
    <div class="bg-white rounded-2xl shadow-2xl max-w-md w-full p-8">
        <div class="flex items-start space-x-4 mb-6">
            <div class="w-12 h-12 bg-red-100 rounded-xl flex items-center justify-center flex-shrink-0">
                <i class="fas fa-exclamation-triangle text-red-600 text-xl"></i>
            </div>
            <div>
                <h3 id="confirm-modal-title" class="text-xl font-bold text-gray-900 mb-1">Are you sure?</h3>
                <p id="confirm-modal-message" class="text-gray-500 text-sm">This action cannot be undone.</p>
            </div>
        </div>
        <div class="flex justify-end space-x-3">
            <button onclick="closeConfirmModal()"
                    class="px-6 py-2.5 bg-white border border-gray-200 text-gray-700 font-semibold rounded-xl hover:bg-gray-50 transition-all">
                Cancel
            </button>
            <button id="confirm-modal-action"
                    class="px-6 py-2.5 bg-gradient-to-r from-red-500 to-rose-600 text-white font-semibold rounded-xl shadow-lg transition-all">
                Confirm
            </button>
        </div>
    </div>
</div>

<script>
function showTab(tabName, options = {}) {
    const tabNames = {
        platform: 'Platform Profile',
        'clinic-defaults': 'Clinic Defaults',
        governance: 'Governance',
    };
    if (!document.getElementById('tab-' + tabName)) {
        tabName = 'platform';
    }

    document.querySelectorAll('.settings-content').forEach(el => el.classList.add('hidden'));

    const target = document.getElementById('tab-' + tabName);
    if (target) {
        target.classList.remove('hidden');
    }

    const navButtons = document.querySelectorAll('[id^="nav-"]');
    navButtons.forEach(btn => {
        btn.className = 'w-full flex items-center px-4 py-3 text-left text-sm font-medium rounded-xl transition-all duration-200 text-slate-600 hover:bg-slate-50 hover:text-slate-900';
        const icon = btn.querySelector('i');
        if (icon) {
            icon.classList.remove('text-slate-300', 'text-white/80');
            icon.classList.add('text-slate-400');
        }
    });

    const activeBtn = document.getElementById('nav-' + tabName);
    const activeClass = 'bg-gradient-to-r from-blue-500 to-blue-600 text-white shadow-lg';

    if (activeBtn) {
        activeBtn.className = `w-full flex items-center px-4 py-3 text-left text-sm font-medium rounded-xl transition-all duration-200 ${activeClass}`;
        const activeIcon = activeBtn.querySelector('i');
        if (activeIcon) {
            activeIcon.classList.remove('text-slate-400');
            activeIcon.classList.add('text-white/80');
        }
    }

    const breadcrumbTab = document.getElementById('control-breadcrumb-tab');
    if (breadcrumbTab) {
        breadcrumbTab.textContent = tabNames[tabName] || '';
    }

    if (options.updateUrl === false) {
        return;
    }

    const url = new URL(window.location.href);
    url.searchParams.set('tab', tabName);
    history.pushState(null, '', url.toString());
}

function toastMessage(type, message) {
    if (window.showToast) {
        window.showToast(type === 'success' ? 'success' : 'error', type === 'success' ? 'Success' : 'Error', message);
        return;
    }
    alert(message);
}

function initRetentionControls() {
    const retentionRadios = document.querySelectorAll('input[name="retention_mode"]');
    const lastN = document.getElementById('retention-last-n');
    const days = document.getElementById('retention-days');

    retentionRadios.forEach(radio => {
        radio.addEventListener('change', () => {
            if (lastN) lastN.classList.add('hidden');
            if (days) days.classList.add('hidden');

            if (radio.value === 'keep_last_n' && lastN) {
                lastN.classList.remove('hidden');
            }
            if (radio.value === 'keep_x_days' && days) {
                days.classList.remove('hidden');
            }
        });
    });
}

function bindPruneActions() {
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
    const csrfHeader = document.querySelector('meta[name="csrf-header"]')?.getAttribute('content') || 'X-CSRF-TOKEN';

    const pruneExportsBtn = document.getElementById('prune-exports-btn');
    if (pruneExportsBtn) {
        pruneExportsBtn.addEventListener('click', () => {
            openConfirmModal(
                'Prune Export Artifacts',
                'This will permanently delete all expired and superseded export files across all clinics. Only files outside the retention window are affected. This cannot be undone.',
                () => {
                    fetch('<?= base_url('controlplane/settings/retention/prune') ?>', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            [csrfHeader]: csrfToken,
                            'X-Requested-With': 'XMLHttpRequest'
                        },
                        body: JSON.stringify({})
                    })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                toastMessage('success', data.message || 'Export artifacts pruned.');
                                return;
                            }
                            toastMessage('error', data.message || 'Pruning failed.');
                        })
                        .catch(() => toastMessage('error', 'Pruning failed.'));
                },
                'Prune Now'
            );
        });
    }

    const pruneAuditsBtn = document.getElementById('prune-audits-btn');
    if (pruneAuditsBtn) {
        pruneAuditsBtn.addEventListener('click', () => {
            openConfirmModal(
                'Prune Audit Log Entries',
                'This will permanently delete all control plane audit log entries older than the configured retention window. Deleted entries cannot be recovered.',
                () => {
                    fetch('<?= base_url('controlplane/settings/audit-retention/prune') ?>', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            [csrfHeader]: csrfToken,
                            'X-Requested-With': 'XMLHttpRequest'
                        },
                        body: JSON.stringify({})
                    })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                toastMessage('success', data.message || 'Audit logs pruned.');
                                return;
                            }
                            toastMessage('error', data.message || 'Pruning failed.');
                        })
                        .catch(() => toastMessage('error', 'Pruning failed.'));
                },
                'Prune Now'
            );
        });
    }
}

function openConfirmModal(title, message, callback, label = 'Confirm') {
    const modal = document.getElementById('confirm-modal');
    const titleEl = document.getElementById('confirm-modal-title');
    const messageEl = document.getElementById('confirm-modal-message');
    const actionBtn = document.getElementById('confirm-modal-action');
    if (!modal || !titleEl || !messageEl || !actionBtn) return;

    titleEl.textContent = title;
    messageEl.textContent = message;
    actionBtn.textContent = label;
    actionBtn.onclick = () => {
        closeConfirmModal();
        callback();
    };
    modal.classList.remove('hidden');
    modal.classList.add('flex');
}

function closeConfirmModal() {
    const modal = document.getElementById('confirm-modal');
    if (!modal) return;
    modal.classList.remove('flex');
    modal.classList.add('hidden');
}

document.addEventListener('DOMContentLoaded', () => {
    const urlTab = new URLSearchParams(window.location.search).get('tab');
    if (urlTab && document.getElementById(`tab-${urlTab}`)) {
        showTab(urlTab, { updateUrl: false });
    } else {
        showTab('platform', { updateUrl: false });
    }

    initRetentionControls();
    bindPruneActions();

    document.querySelectorAll('input[name="default_working_days[]"]').forEach(checkbox => {
        checkbox.addEventListener('change', () => {
            const label = checkbox.closest('label');
            if (!label) return;
            if (checkbox.checked) {
                label.classList.add('border-orange-500', 'bg-orange-50');
                label.classList.remove('border-gray-200');
            } else {
                label.classList.remove('border-orange-500', 'bg-orange-50');
                label.classList.add('border-gray-200');
            }
        });
    });

    document.getElementById('confirm-modal')?.addEventListener('click', (event) => {
        if (event.target === event.currentTarget) {
            closeConfirmModal();
        }
    });
});

window.addEventListener('popstate', () => {
    const urlTab = new URLSearchParams(window.location.search).get('tab') || 'platform';
    showTab(urlTab, { updateUrl: false });
});
</script>
<?= $this->endSection() ?>

