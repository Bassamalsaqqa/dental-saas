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
                                    <i class="fas fa-id-badge text-white text-xl"></i>
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
                                    </div>
                                    <div>
                                        <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Support Email</label>
                                        <input type="email" name="platform_support_email" value="<?= esc($settings['platform_support_email'] ?? '') ?>" placeholder="support@yourdomain.com" class="w-full px-4 py-3 bg-white border border-slate-200 rounded-xl text-slate-700 font-medium focus:border-slate-500 focus:ring-4 focus:ring-slate-200/60 transition-all">
                                    </div>
                                    <div>
                                        <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Support Phone</label>
                                        <input type="text" name="platform_support_phone" value="<?= esc($settings['platform_support_phone'] ?? '') ?>" placeholder="+970 000 000 000" class="w-full px-4 py-3 bg-white border border-slate-200 rounded-xl text-slate-700 font-medium focus:border-slate-500 focus:ring-4 focus:ring-slate-200/60 transition-all">
                                    </div>
                                    <div>
                                        <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">WhatsApp (Primary)</label>
                                        <input type="text" name="platform_support_whatsapp_primary" value="<?= esc($settings['platform_support_whatsapp_primary'] ?? '') ?>" placeholder="+970 000 000 000" class="w-full px-4 py-3 bg-white border border-slate-200 rounded-xl text-slate-700 font-medium focus:border-slate-500 focus:ring-4 focus:ring-slate-200/60 transition-all">
                                    </div>
                                    <div>
                                        <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">WhatsApp (Secondary)</label>
                                        <input type="text" name="platform_support_whatsapp_secondary" value="<?= esc($settings['platform_support_whatsapp_secondary'] ?? '') ?>" placeholder="+972 000 000 000" class="w-full px-4 py-3 bg-white border border-slate-200 rounded-xl text-slate-700 font-medium focus:border-slate-500 focus:ring-4 focus:ring-slate-200/60 transition-all">
                                    </div>
                                    <div class="md:col-span-2">
                                        <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Support Website</label>
                                        <input type="url" name="platform_support_website" value="<?= esc($settings['platform_support_website'] ?? '') ?>" placeholder="https://yourdomain.com" class="w-full px-4 py-3 bg-white border border-slate-200 rounded-xl text-slate-700 font-medium focus:border-slate-500 focus:ring-4 focus:ring-slate-200/60 transition-all">
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
                            <div class="bg-gradient-to-r from-green-500 via-emerald-600 to-teal-600 p-8">
                                <div class="flex items-center space-x-4">
                                    <div class="w-12 h-12 bg-white/20 rounded-xl flex items-center justify-center backdrop-blur-sm">
                                        <i class="fas fa-cog text-white text-xl"></i>
                                    </div>
                                    <div>
                                        <h3 class="text-2xl font-bold text-white">System Preference</h3>
                                        <p class="text-green-100">Defaults applied when a new clinic is onboarded</p>
                                    </div>
                                </div>
                            </div>

                            <div class="p-8">
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <div>
                                        <label class="block text-sm font-semibold text-gray-700 mb-2">Timezone</label>
                                        <select name="default_timezone" class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:border-green-500 focus:ring-4 focus:ring-green-100 transition-all duration-200" required>
                                            <?php $defaultTimezone = $settings['default_timezone'] ?? 'America/New_York'; ?>
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
                                    </div>
                                    <div>
                                        <label class="block text-sm font-semibold text-gray-700 mb-2">Date Format</label>
                                        <?php $defaultDateFormat = $settings['default_date_format'] ?? 'Y-m-d'; ?>
                                        <select name="default_date_format" class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:border-green-500 focus:ring-4 focus:ring-green-100 transition-all duration-200" required>
                                            <option value="Y-m-d" <?= $defaultDateFormat === 'Y-m-d' ? 'selected' : '' ?>>YYYY-MM-DD</option>
                                            <option value="m/d/Y" <?= $defaultDateFormat === 'm/d/Y' ? 'selected' : '' ?>>MM/DD/YYYY</option>
                                            <option value="d/m/Y" <?= $defaultDateFormat === 'd/m/Y' ? 'selected' : '' ?>>DD/MM/YYYY</option>
                                        </select>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-semibold text-gray-700 mb-2">Time Format</label>
                                        <?php $defaultTimeFormat = $settings['default_time_format'] ?? '12'; ?>
                                        <select name="default_time_format" class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:border-green-500 focus:ring-4 focus:ring-green-100 transition-all duration-200" required>
                                            <option value="24" <?= $defaultTimeFormat === '24' ? 'selected' : '' ?>>24 Hour</option>
                                            <option value="12" <?= $defaultTimeFormat === '12' ? 'selected' : '' ?>>12 Hour</option>
                                        </select>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-semibold text-gray-700 mb-2">Currency</label>
                                        <?php $defaultCurrency = $settings['default_currency'] ?? 'USD'; ?>
                                        <select name="default_currency" class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:border-green-500 focus:ring-4 focus:ring-green-100 transition-all duration-200" required>
                                            <option value="USD" <?= $defaultCurrency == 'USD' ? 'selected' : '' ?>>USD - US Dollar ($)</option>
                                            <option value="EUR" <?= $defaultCurrency == 'EUR' ? 'selected' : '' ?>>EUR - Euro (€)</option>
                                            <option value="GBP" <?= $defaultCurrency == 'GBP' ? 'selected' : '' ?>>GBP - British Pound (£)</option>
                                            <option value="JPY" <?= $defaultCurrency == 'JPY' ? 'selected' : '' ?>>JPY - Japanese Yen (¥)</option>
                                            <option value="CAD" <?= $defaultCurrency == 'CAD' ? 'selected' : '' ?>>CAD - Canadian Dollar (C$)</option>
                                            <option value="AUD" <?= $defaultCurrency == 'AUD' ? 'selected' : '' ?>>AUD - Australian Dollar (A$)</option>
                                            <option value="CHF" <?= $defaultCurrency == 'CHF' ? 'selected' : '' ?>>CHF - Swiss Franc (CHF)</option>
                                            <option value="CNY" <?= $defaultCurrency == 'CNY' ? 'selected' : '' ?>>CNY - Chinese Yuan (¥)</option>
                                            <option value="SEK" <?= $defaultCurrency == 'SEK' ? 'selected' : '' ?>>SEK - Swedish Krona (kr)</option>
                                            <option value="NOK" <?= $defaultCurrency == 'NOK' ? 'selected' : '' ?>>NOK - Norwegian Krone (kr)</option>
                                            <option value="DKK" <?= $defaultCurrency == 'DKK' ? 'selected' : '' ?>>DKK - Danish Krone (kr)</option>
                                            <option value="PLN" <?= $defaultCurrency == 'PLN' ? 'selected' : '' ?>>PLN - Polish Zloty (zł)</option>
                                            <option value="CZK" <?= $defaultCurrency == 'CZK' ? 'selected' : '' ?>>CZK - Czech Koruna (Kč)</option>
                                            <option value="HUF" <?= $defaultCurrency == 'HUF' ? 'selected' : '' ?>>HUF - Hungarian Forint (Ft)</option>
                                            <option value="RUB" <?= $defaultCurrency == 'RUB' ? 'selected' : '' ?>>RUB - Russian Ruble (₽)</option>
                                            <option value="INR" <?= $defaultCurrency == 'INR' ? 'selected' : '' ?>>INR - Indian Rupee (₹)</option>
                                            <option value="BRL" <?= $defaultCurrency == 'BRL' ? 'selected' : '' ?>>BRL - Brazilian Real (R$)</option>
                                            <option value="MXN" <?= $defaultCurrency == 'MXN' ? 'selected' : '' ?>>MXN - Mexican Peso ($)</option>
                                            <option value="KRW" <?= $defaultCurrency == 'KRW' ? 'selected' : '' ?>>KRW - South Korean Won (₩)</option>
                                            <option value="SGD" <?= $defaultCurrency == 'SGD' ? 'selected' : '' ?>>SGD - Singapore Dollar (S$)</option>
                                            <option value="HKD" <?= $defaultCurrency == 'HKD' ? 'selected' : '' ?>>HKD - Hong Kong Dollar (HK$)</option>
                                            <option value="NZD" <?= $defaultCurrency == 'NZD' ? 'selected' : '' ?>>NZD - New Zealand Dollar (NZ$)</option>
                                            <option value="TRY" <?= $defaultCurrency == 'TRY' ? 'selected' : '' ?>>TRY - Turkish Lira (₺)</option>
                                            <option value="ZAR" <?= $defaultCurrency == 'ZAR' ? 'selected' : '' ?>>ZAR - South African Rand (R)</option>
                                            <option value="AED" <?= $defaultCurrency == 'AED' ? 'selected' : '' ?>>AED - UAE Dirham (د.إ)</option>
                                            <option value="SAR" <?= $defaultCurrency == 'SAR' ? 'selected' : '' ?>>SAR - Saudi Riyal (ر.س)</option>
                                            <option value="QAR" <?= $defaultCurrency == 'QAR' ? 'selected' : '' ?>>QAR - Qatari Riyal (ر.ق)</option>
                                            <option value="KWD" <?= $defaultCurrency == 'KWD' ? 'selected' : '' ?>>KWD - Kuwaiti Dinar (د.ك)</option>
                                            <option value="BHD" <?= $defaultCurrency == 'BHD' ? 'selected' : '' ?>>BHD - Bahraini Dinar (د.ب)</option>
                                            <option value="OMR" <?= $defaultCurrency == 'OMR' ? 'selected' : '' ?>>OMR - Omani Rial (ر.ع.)</option>
                                            <option value="JOD" <?= $defaultCurrency == 'JOD' ? 'selected' : '' ?>>JOD - Jordanian Dinar (د.ا)</option>
                                            <option value="LBP" <?= $defaultCurrency == 'LBP' ? 'selected' : '' ?>>LBP - Lebanese Pound (ل.ل)</option>
                                            <option value="EGP" <?= $defaultCurrency == 'EGP' ? 'selected' : '' ?>>EGP - Egyptian Pound (ج.م)</option>
                                            <option value="ILS" <?= $defaultCurrency == 'ILS' ? 'selected' : '' ?>>ILS - Israeli Shekel (₪)</option>
                                            <option value="THB" <?= $defaultCurrency == 'THB' ? 'selected' : '' ?>>THB - Thai Baht (฿)</option>
                                            <option value="MYR" <?= $defaultCurrency == 'MYR' ? 'selected' : '' ?>>MYR - Malaysian Ringgit (RM)</option>
                                            <option value="IDR" <?= $defaultCurrency == 'IDR' ? 'selected' : '' ?>>IDR - Indonesian Rupiah (Rp)</option>
                                            <option value="PHP" <?= $defaultCurrency == 'PHP' ? 'selected' : '' ?>>PHP - Philippine Peso (₱)</option>
                                            <option value="VND" <?= $defaultCurrency == 'VND' ? 'selected' : '' ?>>VND - Vietnamese Dong (₫)</option>
                                            <option value="PKR" <?= $defaultCurrency == 'PKR' ? 'selected' : '' ?>>PKR - Pakistani Rupee (₨)</option>
                                            <option value="BDT" <?= $defaultCurrency == 'BDT' ? 'selected' : '' ?>>BDT - Bangladeshi Taka (৳)</option>
                                            <option value="LKR" <?= $defaultCurrency == 'LKR' ? 'selected' : '' ?>>LKR - Sri Lankan Rupee (₨)</option>
                                            <option value="NPR" <?= $defaultCurrency == 'NPR' ? 'selected' : '' ?>>NPR - Nepalese Rupee (₨)</option>
                                            <option value="MMK" <?= $defaultCurrency == 'MMK' ? 'selected' : '' ?>>MMK - Myanmar Kyat (K)</option>
                                            <option value="KHR" <?= $defaultCurrency == 'KHR' ? 'selected' : '' ?>>KHR - Cambodian Riel (៛)</option>
                                            <option value="LAK" <?= $defaultCurrency == 'LAK' ? 'selected' : '' ?>>LAK - Lao Kip (₭)</option>
                                            <option value="BND" <?= $defaultCurrency == 'BND' ? 'selected' : '' ?>>BND - Brunei Dollar (B$)</option>
                                            <option value="FJD" <?= $defaultCurrency == 'FJD' ? 'selected' : '' ?>>FJD - Fijian Dollar (FJ$)</option>
                                            <option value="TOP" <?= $defaultCurrency == 'TOP' ? 'selected' : '' ?>>TOP - Tongan Pa'anga (T$)</option>
                                            <option value="WST" <?= $defaultCurrency == 'WST' ? 'selected' : '' ?>>WST - Samoan Tala (WS$)</option>
                                            <option value="VUV" <?= $defaultCurrency == 'VUV' ? 'selected' : '' ?>>VUV - Vanuatu Vatu (Vt)</option>
                                            <option value="SBD" <?= $defaultCurrency == 'SBD' ? 'selected' : '' ?>>SBD - Solomon Islands Dollar (SI$)</option>
                                            <option value="PGK" <?= $defaultCurrency == 'PGK' ? 'selected' : '' ?>>PGK - Papua New Guinea Kina (K)</option>
                                            <option value="ARS" <?= $defaultCurrency == 'ARS' ? 'selected' : '' ?>>ARS - Argentine Peso ($)</option>
                                            <option value="CLP" <?= $defaultCurrency == 'CLP' ? 'selected' : '' ?>>CLP - Chilean Peso ($)</option>
                                            <option value="COP" <?= $defaultCurrency == 'COP' ? 'selected' : '' ?>>COP - Colombian Peso ($)</option>
                                            <option value="PEN" <?= $defaultCurrency == 'PEN' ? 'selected' : '' ?>>PEN - Peruvian Sol (S/)</option>
                                            <option value="UYU" <?= $defaultCurrency == 'UYU' ? 'selected' : '' ?>>UYU - Uruguayan Peso ($U)</option>
                                            <option value="BOB" <?= $defaultCurrency == 'BOB' ? 'selected' : '' ?>>BOB - Bolivian Boliviano (Bs)</option>
                                            <option value="PYG" <?= $defaultCurrency == 'PYG' ? 'selected' : '' ?>>PYG - Paraguayan Guarani (₲)</option>
                                            <option value="VES" <?= $defaultCurrency == 'VES' ? 'selected' : '' ?>>VES - Venezuelan Bolívar (Bs.S)</option>
                                            <option value="GYD" <?= $defaultCurrency == 'GYD' ? 'selected' : '' ?>>GYD - Guyanese Dollar (G$)</option>
                                            <option value="SRD" <?= $defaultCurrency == 'SRD' ? 'selected' : '' ?>>SRD - Surinamese Dollar (Sr$)</option>
                                            <option value="TTD" <?= $defaultCurrency == 'TTD' ? 'selected' : '' ?>>TTD - Trinidad and Tobago Dollar (TT$)</option>
                                            <option value="JMD" <?= $defaultCurrency == 'JMD' ? 'selected' : '' ?>>JMD - Jamaican Dollar (J$)</option>
                                            <option value="BBD" <?= $defaultCurrency == 'BBD' ? 'selected' : '' ?>>BBD - Barbadian Dollar (Bds$)</option>
                                            <option value="BZD" <?= $defaultCurrency == 'BZD' ? 'selected' : '' ?>>BZD - Belize Dollar (BZ$)</option>
                                            <option value="XCD" <?= $defaultCurrency == 'XCD' ? 'selected' : '' ?>>XCD - East Caribbean Dollar (EC$)</option>
                                            <option value="DOP" <?= $defaultCurrency == 'DOP' ? 'selected' : '' ?>>DOP - Dominican Peso (RD$)</option>
                                            <option value="HTG" <?= $defaultCurrency == 'HTG' ? 'selected' : '' ?>>HTG - Haitian Gourde (G)</option>
                                            <option value="CUP" <?= $defaultCurrency == 'CUP' ? 'selected' : '' ?>>CUP - Cuban Peso ($)</option>
                                            <option value="CRC" <?= $defaultCurrency == 'CRC' ? 'selected' : '' ?>>CRC - Costa Rican Colón (₡)</option>
                                            <option value="GTQ" <?= $defaultCurrency == 'GTQ' ? 'selected' : '' ?>>GTQ - Guatemalan Quetzal (Q)</option>
                                            <option value="HNL" <?= $defaultCurrency == 'HNL' ? 'selected' : '' ?>>HNL - Honduran Lempira (L)</option>
                                            <option value="NIO" <?= $defaultCurrency == 'NIO' ? 'selected' : '' ?>>NIO - Nicaraguan Córdoba (C$)</option>
                                            <option value="PAB" <?= $defaultCurrency == 'PAB' ? 'selected' : '' ?>>PAB - Panamanian Balboa (B/.)</option>
                                            <option value="SVC" <?= $defaultCurrency == 'SVC' ? 'selected' : '' ?>>SVC - Salvadoran Colón (₡)</option>
                                            <option value="BMD" <?= $defaultCurrency == 'BMD' ? 'selected' : '' ?>>BMD - Bermudian Dollar (BD$)</option>
                                            <option value="KYD" <?= $defaultCurrency == 'KYD' ? 'selected' : '' ?>>KYD - Cayman Islands Dollar (CI$)</option>
                                            <option value="AWG" <?= $defaultCurrency == 'AWG' ? 'selected' : '' ?>>AWG - Aruban Florin (ƒ)</option>
                                            <option value="ANG" <?= $defaultCurrency == 'ANG' ? 'selected' : '' ?>>ANG - Netherlands Antillean Guilder (ƒ)</option>
                                            <option value="BSD" <?= $defaultCurrency == 'BSD' ? 'selected' : '' ?>>BSD - Bahamian Dollar (B$)</option>
                                            <option value="RON" <?= $defaultCurrency == 'RON' ? 'selected' : '' ?>>RON - Romanian Leu (lei)</option>
                                            <option value="BGN" <?= $defaultCurrency == 'BGN' ? 'selected' : '' ?>>BGN - Bulgarian Lev (лв)</option>
                                            <option value="HRK" <?= $defaultCurrency == 'HRK' ? 'selected' : '' ?>>HRK - Croatian Kuna (kn)</option>
                                            <option value="RSD" <?= $defaultCurrency == 'RSD' ? 'selected' : '' ?>>RSD - Serbian Dinar (дин)</option>
                                            <option value="MKD" <?= $defaultCurrency == 'MKD' ? 'selected' : '' ?>>MKD - Macedonian Denar (ден)</option>
                                            <option value="BAM" <?= $defaultCurrency == 'BAM' ? 'selected' : '' ?>>BAM - Bosnia and Herzegovina Mark (КМ)</option>
                                            <option value="ALL" <?= $defaultCurrency == 'ALL' ? 'selected' : '' ?>>ALL - Albanian Lek (L)</option>
                                            <option value="ISK" <?= $defaultCurrency == 'ISK' ? 'selected' : '' ?>>ISK - Icelandic Krona (kr)</option>
                                            <option value="MDL" <?= $defaultCurrency == 'MDL' ? 'selected' : '' ?>>MDL - Moldovan Leu (L)</option>
                                            <option value="UAH" <?= $defaultCurrency == 'UAH' ? 'selected' : '' ?>>UAH - Ukrainian Hryvnia (₴)</option>
                                            <option value="BYN" <?= $defaultCurrency == 'BYN' ? 'selected' : '' ?>>BYN - Belarusian Ruble (Br)</option>
                                            <option value="GEL" <?= $defaultCurrency == 'GEL' ? 'selected' : '' ?>>GEL - Georgian Lari (₾)</option>
                                            <option value="AMD" <?= $defaultCurrency == 'AMD' ? 'selected' : '' ?>>AMD - Armenian Dram (֏)</option>
                                            <option value="AZN" <?= $defaultCurrency == 'AZN' ? 'selected' : '' ?>>AZN - Azerbaijani Manat (₼)</option>
                                            <option value="KZT" <?= $defaultCurrency == 'KZT' ? 'selected' : '' ?>>KZT - Kazakhstani Tenge (₸)</option>
                                            <option value="KGS" <?= $defaultCurrency == 'KGS' ? 'selected' : '' ?>>KGS - Kyrgyzstani Som (с)</option>
                                            <option value="TJS" <?= $defaultCurrency == 'TJS' ? 'selected' : '' ?>>TJS - Tajikistani Somoni (SM)</option>
                                            <option value="TMT" <?= $defaultCurrency == 'TMT' ? 'selected' : '' ?>>TMT - Turkmenistani Manat (T)</option>
                                            <option value="UZS" <?= $defaultCurrency == 'UZS' ? 'selected' : '' ?>>UZS - Uzbekistani Som (сўм)</option>
                                            <option value="MNT" <?= $defaultCurrency == 'MNT' ? 'selected' : '' ?>>MNT - Mongolian Tugrik (₮)</option>
                                            <option value="AFN" <?= $defaultCurrency == 'AFN' ? 'selected' : '' ?>>AFN - Afghan Afghani (؋)</option>
                                            <option value="IRR" <?= $defaultCurrency == 'IRR' ? 'selected' : '' ?>>IRR - Iranian Rial (﷼)</option>
                                            <option value="IQD" <?= $defaultCurrency == 'IQD' ? 'selected' : '' ?>>IQD - Iraqi Dinar (د.ع)</option>
                                            <option value="SYP" <?= $defaultCurrency == 'SYP' ? 'selected' : '' ?>>SYP - Syrian Pound (ل.س)</option>
                                            <option value="YER" <?= $defaultCurrency == 'YER' ? 'selected' : '' ?>>YER - Yemeni Rial (﷼)</option>
                                            <option value="LYD" <?= $defaultCurrency == 'LYD' ? 'selected' : '' ?>>LYD - Libyan Dinar (ل.د)</option>
                                            <option value="TND" <?= $defaultCurrency == 'TND' ? 'selected' : '' ?>>TND - Tunisian Dinar (د.ت)</option>
                                            <option value="DZD" <?= $defaultCurrency == 'DZD' ? 'selected' : '' ?>>DZD - Algerian Dinar (د.ج)</option>
                                            <option value="MAD" <?= $defaultCurrency == 'MAD' ? 'selected' : '' ?>>MAD - Moroccan Dirham (د.م.)</option>
                                            <option value="ETB" <?= $defaultCurrency == 'ETB' ? 'selected' : '' ?>>ETB - Ethiopian Birr (Br)</option>
                                            <option value="KES" <?= $defaultCurrency == 'KES' ? 'selected' : '' ?>>KES - Kenyan Shilling (KSh)</option>
                                            <option value="UGX" <?= $defaultCurrency == 'UGX' ? 'selected' : '' ?>>UGX - Ugandan Shilling (USh)</option>
                                            <option value="TZS" <?= $defaultCurrency == 'TZS' ? 'selected' : '' ?>>TZS - Tanzanian Shilling (TSh)</option>
                                            <option value="RWF" <?= $defaultCurrency == 'RWF' ? 'selected' : '' ?>>RWF - Rwandan Franc (RF)</option>
                                            <option value="MWK" <?= $defaultCurrency == 'MWK' ? 'selected' : '' ?>>MWK - Malawian Kwacha (MK)</option>
                                            <option value="ZMW" <?= $defaultCurrency == 'ZMW' ? 'selected' : '' ?>>ZMW - Zambian Kwacha (ZK)</option>
                                            <option value="BWP" <?= $defaultCurrency == 'BWP' ? 'selected' : '' ?>>BWP - Botswanan Pula (P)</option>
                                            <option value="SZL" <?= $defaultCurrency == 'SZL' ? 'selected' : '' ?>>SZL - Swazi Lilangeni (L)</option>
                                            <option value="LSL" <?= $defaultCurrency == 'LSL' ? 'selected' : '' ?>>LSL - Lesotho Loti (L)</option>
                                            <option value="NAD" <?= $defaultCurrency == 'NAD' ? 'selected' : '' ?>>NAD - Namibian Dollar (N$)</option>
                                            <option value="AOA" <?= $defaultCurrency == 'AOA' ? 'selected' : '' ?>>AOA - Angolan Kwanza (Kz)</option>
                                            <option value="MZN" <?= $defaultCurrency == 'MZN' ? 'selected' : '' ?>>MZN - Mozambican Metical (MT)</option>
                                            <option value="MGA" <?= $defaultCurrency == 'MGA' ? 'selected' : '' ?>>MGA - Malagasy Ariary (Ar)</option>
                                            <option value="MUR" <?= $defaultCurrency == 'MUR' ? 'selected' : '' ?>>MUR - Mauritian Rupee (₨)</option>
                                            <option value="SCR" <?= $defaultCurrency == 'SCR' ? 'selected' : '' ?>>SCR - Seychellois Rupee (₨)</option>
                                            <option value="KMF" <?= $defaultCurrency == 'KMF' ? 'selected' : '' ?>>KMF - Comorian Franc (CF)</option>
                                            <option value="DJF" <?= $defaultCurrency == 'DJF' ? 'selected' : '' ?>>DJF - Djiboutian Franc (Fdj)</option>
                                            <option value="SOS" <?= $defaultCurrency == 'SOS' ? 'selected' : '' ?>>SOS - Somali Shilling (S)</option>
                                            <option value="ERN" <?= $defaultCurrency == 'ERN' ? 'selected' : '' ?>>ERN - Eritrean Nakfa (Nfk)</option>
                                            <option value="STN" <?= $defaultCurrency == 'STN' ? 'selected' : '' ?>>STN - São Tomé and Príncipe Dobra (Db)</option>
                                            <option value="CVE" <?= $defaultCurrency == 'CVE' ? 'selected' : '' ?>>CVE - Cape Verdean Escudo ($)</option>
                                            <option value="GMD" <?= $defaultCurrency == 'GMD' ? 'selected' : '' ?>>GMD - Gambian Dalasi (D)</option>
                                            <option value="GHS" <?= $defaultCurrency == 'GHS' ? 'selected' : '' ?>>GHS - Ghanaian Cedi (₵)</option>
                                            <option value="NGN" <?= $defaultCurrency == 'NGN' ? 'selected' : '' ?>>NGN - Nigerian Naira (₦)</option>
                                            <option value="XOF" <?= $defaultCurrency == 'XOF' ? 'selected' : '' ?>>XOF - West African CFA Franc (CFA)</option>
                                            <option value="XAF" <?= $defaultCurrency == 'XAF' ? 'selected' : '' ?>>XAF - Central African CFA Franc (FCFA)</option>
                                        </select>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-semibold text-gray-700 mb-2">Currency Position</label>
                                        <?php $defaultCurrencyPosition = $settings['default_currency_position'] ?? 'before'; ?>
                                        <select name="default_currency_position" class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:border-green-500 focus:ring-4 focus:ring-green-100 transition-all duration-200" required>
                                            <option value="before" <?= $defaultCurrencyPosition === 'before' ? 'selected' : '' ?>>Before Amount (e.g., $100)</option>
                                            <option value="after" <?= $defaultCurrencyPosition === 'after' ? 'selected' : '' ?>>After Amount (e.g., 100$)</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="bg-white/90 backdrop-blur-xl rounded-2xl shadow-xl border border-white/50 overflow-hidden">
                            <div class="bg-gradient-to-r from-orange-500 via-amber-600 to-yellow-600 p-8">
                                <div class="flex items-center space-x-4">
                                    <div class="w-12 h-12 bg-white/20 rounded-xl flex items-center justify-center backdrop-blur-sm">
                                        <i class="fas fa-clock text-white text-xl"></i>
                                    </div>
                                    <div>
                                        <h3 class="text-2xl font-bold text-white">Working Hours</h3>
                                        <p class="text-white/90">Set default operating hours and working days</p>
                                    </div>
                                </div>
                            </div>

                            <div class="p-8">
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <div>
                                        <label class="block text-sm font-semibold text-gray-700 mb-2">Start Time</label>
                                        <input type="time" name="default_working_hours_start" value="<?= esc($settings['default_working_hours_start'] ?? '09:00') ?>" class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:border-orange-500 focus:ring-4 focus:ring-orange-100 transition-all duration-200" required>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-semibold text-gray-700 mb-2">End Time</label>
                                        <input type="time" name="default_working_hours_end" value="<?= esc($settings['default_working_hours_end'] ?? '17:00') ?>" class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:border-orange-500 focus:ring-4 focus:ring-orange-100 transition-all duration-200" required>
                                    </div>
                                    <div class="md:col-span-2">
                                        <label class="block text-sm font-semibold text-gray-700 mb-2">Working Days</label>
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
                                        </div>
                                        <div id="retention-days" class="<?= $retentionMode === 'keep_x_days' ? '' : 'hidden' ?>">
                                            <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Number of days to keep</label>
                                            <input type="number" name="retention_days" value="<?= esc((int) ($settings['retention_days'] ?? 30)) ?>" min="1" max="3650" class="w-full px-4 py-3 bg-white border border-slate-200 rounded-xl text-slate-700 font-medium focus:border-slate-500 focus:ring-4 focus:ring-slate-200/60 transition-all">
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
                            <div class="bg-gradient-to-r from-indigo-600 to-violet-600 p-8">
                                <div class="flex items-center space-x-4">
                                    <div class="w-12 h-12 bg-white/10 rounded-xl flex items-center justify-center backdrop-blur-sm">
                                        <i class="fas fa-user-shield text-white text-xl"></i>
                                    </div>
                                    <div>
                                        <h3 class="text-xl font-bold text-white">Control Plane Audit Retention</h3>
                                        <p class="text-indigo-100 text-sm">Retention policy for operator activity logs</p>
                                    </div>
                                </div>
                            </div>
                            <div class="p-8">
                                <form action="<?= base_url('controlplane/settings/audit-retention') ?>" method="POST" class="space-y-6">
                                    <?= csrf_field() ?>
                                    <div>
                                        <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Audit log retention window (days)</label>
                                        <input type="number" name="control_plane_audit_retention_days" value="<?= esc((int) ($settings['control_plane_audit_retention_days'] ?? 365)) ?>" min="30" max="3650" class="w-full px-4 py-3 bg-white border border-slate-200 rounded-xl text-slate-700 font-medium focus:border-indigo-500 focus:ring-4 focus:ring-indigo-200/60 transition-all">
                                        <p class="text-xs text-slate-500 mt-2">Retention applies to control plane audit events only. Tenant audit policies remain unchanged.</p>
                                    </div>
                                    <div class="flex flex-wrap items-center justify-between gap-4 pt-4 border-t border-slate-100">
                                        <button type="button" id="prune-audits-btn" class="px-6 py-3 bg-white border border-slate-200 text-slate-700 font-semibold rounded-xl hover:bg-slate-50 transition-all">
                                            <i class="fas fa-broom mr-2"></i>Prune Audit Logs
                                        </button>
                                        <button type="submit" class="px-6 py-3 bg-gradient-to-r from-indigo-600 to-violet-600 text-white font-semibold rounded-xl shadow-lg hover:from-indigo-700 hover:to-violet-700 transition-all">
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

<script>
function showTab(tabName) {
    document.querySelectorAll('.settings-content').forEach(el => el.classList.add('hidden'));

    const target = document.getElementById('tab-' + tabName);
    if (target) {
        target.classList.remove('hidden');
    }

    const navButtons = document.querySelectorAll('[id^="nav-"]');
    navButtons.forEach(btn => {
        btn.className = 'w-full flex items-center px-4 py-3 text-left text-sm font-medium rounded-xl transition-all duration-200 text-slate-600 hover:bg-slate-50 hover:text-slate-900';
        const icon = btn.querySelector('i');
        if (icon) icon.className = icon.className.replace('text-slate-300', 'text-slate-400');
    });

    const activeBtn = document.getElementById('nav-' + tabName);
    let activeClass = 'bg-gradient-to-r from-slate-700 to-gray-800 text-white shadow-md';
    if (tabName === 'governance') activeClass = 'bg-gradient-to-r from-indigo-600 to-violet-600 text-white shadow-md';
    if (tabName === 'clinic-defaults') activeClass = 'bg-gradient-to-r from-green-500 to-emerald-600 text-white shadow-md';

    if (activeBtn) {
        activeBtn.className = `w-full flex items-center px-4 py-3 text-left text-sm font-medium rounded-xl transition-all duration-200 ${activeClass}`;
        const activeIcon = activeBtn.querySelector('i');
        if (activeIcon) {
            activeIcon.classList.remove('text-slate-400');
            activeIcon.classList.add('text-white/80');
        }
    }
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
            if (!confirm('Prune all expired/superseded export artifacts? This cannot be undone.')) return;

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
        });
    }

    const pruneAuditsBtn = document.getElementById('prune-audits-btn');
    if (pruneAuditsBtn) {
        pruneAuditsBtn.addEventListener('click', () => {
            if (!confirm('Prune control plane audit logs beyond the retention window?')) return;

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
        });
    }
}

document.addEventListener('DOMContentLoaded', () => {
    const urlTab = new URLSearchParams(window.location.search).get('tab');
    if (urlTab && document.getElementById(`tab-${urlTab}`)) {
        showTab(urlTab);
    } else {
        showTab('platform');
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
});
</script>
<?= $this->endSection() ?>

