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
                        <button onclick="showSettingsTab('system')" id="tab-system" class="w-full flex items-center px-4 py-3 text-left text-sm font-medium rounded-xl transition-all duration-200 text-gray-700 hover:bg-gray-100 hover:text-gray-900">
                            <i class="fas fa-cog w-5 h-5 mr-3"></i>
                            System Preference
                        </button>
                        <button onclick="showSettingsTab('hours')" id="tab-hours" class="w-full flex items-center px-4 py-3 text-left text-sm font-medium rounded-xl transition-all duration-200 text-gray-700 hover:bg-gray-100 hover:text-gray-900">
                            <i class="fas fa-clock w-5 h-5 mr-3"></i>
                            Working Hours
                        </button>
                        <button onclick="showSettingsTab('backup')" id="tab-backup" class="w-full flex items-center px-4 py-3 text-left text-sm font-medium rounded-xl transition-all duration-200 text-gray-700 hover:bg-gray-100 hover:text-gray-900">
                            <i class="fas fa-database w-5 h-5 mr-3"></i>
                            DB Backup
                        </button>
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
                            <form action="<?= base_url('settings/updateClinic') ?>" method="POST">
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
                                        <input type="text" name="clinic_tagline" value="<?= esc($settings['clinic_tagline'] ?? '') ?>" class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:border-blue-500 focus:ring-4 focus:ring-blue-100 transition-all duration-200" placeholder="e.g., Professional Suite">
                                    </div>
                                    <div class="md:col-span-2">
                                        <label class="block text-sm font-semibold text-gray-700 mb-2">Clinic Logo Path (URL or Relative Path)</label>
                                        <input type="text" name="clinic_logo_path" value="<?= esc($settings['clinic_logo_path'] ?? '') ?>" class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:border-blue-500 focus:ring-4 focus:ring-blue-100 transition-all duration-200" placeholder="e.g., assets/images/logo.png or https://example.com/logo.png">
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
                                            <option value="USD" <?= ($settings['currency'] ?? '') == 'USD' ? 'selected' : '' ?>>USD - US Dollar ($)</option>
                                            <option value="EUR" <?= ($settings['currency'] ?? '') == 'EUR' ? 'selected' : '' ?>>EUR - Euro (€)</option>
                                            <option value="GBP" <?= ($settings['currency'] ?? '') == 'GBP' ? 'selected' : '' ?>>GBP - British Pound (£)</option>
                                            <option value="JPY" <?= ($settings['currency'] ?? '') == 'JPY' ? 'selected' : '' ?>>JPY - Japanese Yen (¥)</option>
                                            <option value="CAD" <?= ($settings['currency'] ?? '') == 'CAD' ? 'selected' : '' ?>>CAD - Canadian Dollar (C$)</option>
                                            <option value="AUD" <?= ($settings['currency'] ?? '') == 'AUD' ? 'selected' : '' ?>>AUD - Australian Dollar (A$)</option>
                                            <option value="CHF" <?= ($settings['currency'] ?? '') == 'CHF' ? 'selected' : '' ?>>CHF - Swiss Franc (CHF)</option>
                                            <option value="CNY" <?= ($settings['currency'] ?? '') == 'CNY' ? 'selected' : '' ?>>CNY - Chinese Yuan (¥)</option>
                                            <option value="SEK" <?= ($settings['currency'] ?? '') == 'SEK' ? 'selected' : '' ?>>SEK - Swedish Krona (kr)</option>
                                            <option value="NOK" <?= ($settings['currency'] ?? '') == 'NOK' ? 'selected' : '' ?>>NOK - Norwegian Krone (kr)</option>
                                            <option value="DKK" <?= ($settings['currency'] ?? '') == 'DKK' ? 'selected' : '' ?>>DKK - Danish Krone (kr)</option>
                                            <option value="PLN" <?= ($settings['currency'] ?? '') == 'PLN' ? 'selected' : '' ?>>PLN - Polish Zloty (zł)</option>
                                            <option value="CZK" <?= ($settings['currency'] ?? '') == 'CZK' ? 'selected' : '' ?>>CZK - Czech Koruna (Kč)</option>
                                            <option value="HUF" <?= ($settings['currency'] ?? '') == 'HUF' ? 'selected' : '' ?>>HUF - Hungarian Forint (Ft)</option>
                                            <option value="RUB" <?= ($settings['currency'] ?? '') == 'RUB' ? 'selected' : '' ?>>RUB - Russian Ruble (₽)</option>
                                            <option value="INR" <?= ($settings['currency'] ?? '') == 'INR' ? 'selected' : '' ?>>INR - Indian Rupee (₹)</option>
                                            <option value="BRL" <?= ($settings['currency'] ?? '') == 'BRL' ? 'selected' : '' ?>>BRL - Brazilian Real (R$)</option>
                                            <option value="MXN" <?= ($settings['currency'] ?? '') == 'MXN' ? 'selected' : '' ?>>MXN - Mexican Peso ($)</option>
                                            <option value="KRW" <?= ($settings['currency'] ?? '') == 'KRW' ? 'selected' : '' ?>>KRW - South Korean Won (₩)</option>
                                            <option value="SGD" <?= ($settings['currency'] ?? '') == 'SGD' ? 'selected' : '' ?>>SGD - Singapore Dollar (S$)</option>
                                            <option value="HKD" <?= ($settings['currency'] ?? '') == 'HKD' ? 'selected' : '' ?>>HKD - Hong Kong Dollar (HK$)</option>
                                            <option value="NZD" <?= ($settings['currency'] ?? '') == 'NZD' ? 'selected' : '' ?>>NZD - New Zealand Dollar (NZ$)</option>
                                            <option value="TRY" <?= ($settings['currency'] ?? '') == 'TRY' ? 'selected' : '' ?>>TRY - Turkish Lira (₺)</option>
                                            <option value="ZAR" <?= ($settings['currency'] ?? '') == 'ZAR' ? 'selected' : '' ?>>ZAR - South African Rand (R)</option>
                                            <option value="AED" <?= ($settings['currency'] ?? '') == 'AED' ? 'selected' : '' ?>>AED - UAE Dirham (د.إ)</option>
                                            <option value="SAR" <?= ($settings['currency'] ?? '') == 'SAR' ? 'selected' : '' ?>>SAR - Saudi Riyal (ر.س)</option>
                                            <option value="QAR" <?= ($settings['currency'] ?? '') == 'QAR' ? 'selected' : '' ?>>QAR - Qatari Riyal (ر.ق)</option>
                                            <option value="KWD" <?= ($settings['currency'] ?? '') == 'KWD' ? 'selected' : '' ?>>KWD - Kuwaiti Dinar (د.ك)</option>
                                            <option value="BHD" <?= ($settings['currency'] ?? '') == 'BHD' ? 'selected' : '' ?>>BHD - Bahraini Dinar (د.ب)</option>
                                            <option value="OMR" <?= ($settings['currency'] ?? '') == 'OMR' ? 'selected' : '' ?>>OMR - Omani Rial (ر.ع.)</option>
                                            <option value="JOD" <?= ($settings['currency'] ?? '') == 'JOD' ? 'selected' : '' ?>>JOD - Jordanian Dinar (د.ا)</option>
                                            <option value="LBP" <?= ($settings['currency'] ?? '') == 'LBP' ? 'selected' : '' ?>>LBP - Lebanese Pound (ل.ل)</option>
                                            <option value="EGP" <?= ($settings['currency'] ?? '') == 'EGP' ? 'selected' : '' ?>>EGP - Egyptian Pound (ج.م)</option>
                                            <option value="ILS" <?= ($settings['currency'] ?? '') == 'ILS' ? 'selected' : '' ?>>ILS - Israeli Shekel (₪)</option>
                                            <option value="THB" <?= ($settings['currency'] ?? '') == 'THB' ? 'selected' : '' ?>>THB - Thai Baht (฿)</option>
                                            <option value="MYR" <?= ($settings['currency'] ?? '') == 'MYR' ? 'selected' : '' ?>>MYR - Malaysian Ringgit (RM)</option>
                                            <option value="IDR" <?= ($settings['currency'] ?? '') == 'IDR' ? 'selected' : '' ?>>IDR - Indonesian Rupiah (Rp)</option>
                                            <option value="PHP" <?= ($settings['currency'] ?? '') == 'PHP' ? 'selected' : '' ?>>PHP - Philippine Peso (₱)</option>
                                            <option value="VND" <?= ($settings['currency'] ?? '') == 'VND' ? 'selected' : '' ?>>VND - Vietnamese Dong (₫)</option>
                                            <option value="PKR" <?= ($settings['currency'] ?? '') == 'PKR' ? 'selected' : '' ?>>PKR - Pakistani Rupee (₨)</option>
                                            <option value="BDT" <?= ($settings['currency'] ?? '') == 'BDT' ? 'selected' : '' ?>>BDT - Bangladeshi Taka (৳)</option>
                                            <option value="LKR" <?= ($settings['currency'] ?? '') == 'LKR' ? 'selected' : '' ?>>LKR - Sri Lankan Rupee (₨)</option>
                                            <option value="NPR" <?= ($settings['currency'] ?? '') == 'NPR' ? 'selected' : '' ?>>NPR - Nepalese Rupee (₨)</option>
                                            <option value="MMK" <?= ($settings['currency'] ?? '') == 'MMK' ? 'selected' : '' ?>>MMK - Myanmar Kyat (K)</option>
                                            <option value="KHR" <?= ($settings['currency'] ?? '') == 'KHR' ? 'selected' : '' ?>>KHR - Cambodian Riel (៛)</option>
                                            <option value="LAK" <?= ($settings['currency'] ?? '') == 'LAK' ? 'selected' : '' ?>>LAK - Lao Kip (₭)</option>
                                            <option value="BND" <?= ($settings['currency'] ?? '') == 'BND' ? 'selected' : '' ?>>BND - Brunei Dollar (B$)</option>
                                            <option value="FJD" <?= ($settings['currency'] ?? '') == 'FJD' ? 'selected' : '' ?>>FJD - Fijian Dollar (FJ$)</option>
                                            <option value="TOP" <?= ($settings['currency'] ?? '') == 'TOP' ? 'selected' : '' ?>>TOP - Tongan Pa'anga (T$)</option>
                                            <option value="WST" <?= ($settings['currency'] ?? '') == 'WST' ? 'selected' : '' ?>>WST - Samoan Tala (WS$)</option>
                                            <option value="VUV" <?= ($settings['currency'] ?? '') == 'VUV' ? 'selected' : '' ?>>VUV - Vanuatu Vatu (Vt)</option>
                                            <option value="SBD" <?= ($settings['currency'] ?? '') == 'SBD' ? 'selected' : '' ?>>SBD - Solomon Islands Dollar (SI$)</option>
                                            <option value="PGK" <?= ($settings['currency'] ?? '') == 'PGK' ? 'selected' : '' ?>>PGK - Papua New Guinea Kina (K)</option>
                                            <option value="ARS" <?= ($settings['currency'] ?? '') == 'ARS' ? 'selected' : '' ?>>ARS - Argentine Peso ($)</option>
                                            <option value="CLP" <?= ($settings['currency'] ?? '') == 'CLP' ? 'selected' : '' ?>>CLP - Chilean Peso ($)</option>
                                            <option value="COP" <?= ($settings['currency'] ?? '') == 'COP' ? 'selected' : '' ?>>COP - Colombian Peso ($)</option>
                                            <option value="PEN" <?= ($settings['currency'] ?? '') == 'PEN' ? 'selected' : '' ?>>PEN - Peruvian Sol (S/)</option>
                                            <option value="UYU" <?= ($settings['currency'] ?? '') == 'UYU' ? 'selected' : '' ?>>UYU - Uruguayan Peso ($U)</option>
                                            <option value="BOB" <?= ($settings['currency'] ?? '') == 'BOB' ? 'selected' : '' ?>>BOB - Bolivian Boliviano (Bs)</option>
                                            <option value="PYG" <?= ($settings['currency'] ?? '') == 'PYG' ? 'selected' : '' ?>>PYG - Paraguayan Guarani (₲)</option>
                                            <option value="VES" <?= ($settings['currency'] ?? '') == 'VES' ? 'selected' : '' ?>>VES - Venezuelan Bolívar (Bs.S)</option>
                                            <option value="GYD" <?= ($settings['currency'] ?? '') == 'GYD' ? 'selected' : '' ?>>GYD - Guyanese Dollar (G$)</option>
                                            <option value="SRD" <?= ($settings['currency'] ?? '') == 'SRD' ? 'selected' : '' ?>>SRD - Surinamese Dollar (Sr$)</option>
                                            <option value="TTD" <?= ($settings['currency'] ?? '') == 'TTD' ? 'selected' : '' ?>>TTD - Trinidad and Tobago Dollar (TT$)</option>
                                            <option value="JMD" <?= ($settings['currency'] ?? '') == 'JMD' ? 'selected' : '' ?>>JMD - Jamaican Dollar (J$)</option>
                                            <option value="BBD" <?= ($settings['currency'] ?? '') == 'BBD' ? 'selected' : '' ?>>BBD - Barbadian Dollar (Bds$)</option>
                                            <option value="BZD" <?= ($settings['currency'] ?? '') == 'BZD' ? 'selected' : '' ?>>BZD - Belize Dollar (BZ$)</option>
                                            <option value="XCD" <?= ($settings['currency'] ?? '') == 'XCD' ? 'selected' : '' ?>>XCD - East Caribbean Dollar (EC$)</option>
                                            <option value="DOP" <?= ($settings['currency'] ?? '') == 'DOP' ? 'selected' : '' ?>>DOP - Dominican Peso (RD$)</option>
                                            <option value="HTG" <?= ($settings['currency'] ?? '') == 'HTG' ? 'selected' : '' ?>>HTG - Haitian Gourde (G)</option>
                                            <option value="CUP" <?= ($settings['currency'] ?? '') == 'CUP' ? 'selected' : '' ?>>CUP - Cuban Peso ($)</option>
                                            <option value="CRC" <?= ($settings['currency'] ?? '') == 'CRC' ? 'selected' : '' ?>>CRC - Costa Rican Colón (₡)</option>
                                            <option value="GTQ" <?= ($settings['currency'] ?? '') == 'GTQ' ? 'selected' : '' ?>>GTQ - Guatemalan Quetzal (Q)</option>
                                            <option value="HNL" <?= ($settings['currency'] ?? '') == 'HNL' ? 'selected' : '' ?>>HNL - Honduran Lempira (L)</option>
                                            <option value="NIO" <?= ($settings['currency'] ?? '') == 'NIO' ? 'selected' : '' ?>>NIO - Nicaraguan Córdoba (C$)</option>
                                            <option value="PAB" <?= ($settings['currency'] ?? '') == 'PAB' ? 'selected' : '' ?>>PAB - Panamanian Balboa (B/.)</option>
                                            <option value="SVC" <?= ($settings['currency'] ?? '') == 'SVC' ? 'selected' : '' ?>>SVC - Salvadoran Colón (₡)</option>
                                            <option value="BMD" <?= ($settings['currency'] ?? '') == 'BMD' ? 'selected' : '' ?>>BMD - Bermudian Dollar (BD$)</option>
                                            <option value="KYD" <?= ($settings['currency'] ?? '') == 'KYD' ? 'selected' : '' ?>>KYD - Cayman Islands Dollar (CI$)</option>
                                            <option value="AWG" <?= ($settings['currency'] ?? '') == 'AWG' ? 'selected' : '' ?>>AWG - Aruban Florin (ƒ)</option>
                                            <option value="ANG" <?= ($settings['currency'] ?? '') == 'ANG' ? 'selected' : '' ?>>ANG - Netherlands Antillean Guilder (ƒ)</option>
                                            <option value="BSD" <?= ($settings['currency'] ?? '') == 'BSD' ? 'selected' : '' ?>>BSD - Bahamian Dollar (B$)</option>
                                            <option value="RON" <?= ($settings['currency'] ?? '') == 'RON' ? 'selected' : '' ?>>RON - Romanian Leu (lei)</option>
                                            <option value="BGN" <?= ($settings['currency'] ?? '') == 'BGN' ? 'selected' : '' ?>>BGN - Bulgarian Lev (лв)</option>
                                            <option value="HRK" <?= ($settings['currency'] ?? '') == 'HRK' ? 'selected' : '' ?>>HRK - Croatian Kuna (kn)</option>
                                            <option value="RSD" <?= ($settings['currency'] ?? '') == 'RSD' ? 'selected' : '' ?>>RSD - Serbian Dinar (дин)</option>
                                            <option value="MKD" <?= ($settings['currency'] ?? '') == 'MKD' ? 'selected' : '' ?>>MKD - Macedonian Denar (ден)</option>
                                            <option value="BAM" <?= ($settings['currency'] ?? '') == 'BAM' ? 'selected' : '' ?>>BAM - Bosnia and Herzegovina Mark (КМ)</option>
                                            <option value="ALL" <?= ($settings['currency'] ?? '') == 'ALL' ? 'selected' : '' ?>>ALL - Albanian Lek (L)</option>
                                            <option value="ISK" <?= ($settings['currency'] ?? '') == 'ISK' ? 'selected' : '' ?>>ISK - Icelandic Krona (kr)</option>
                                            <option value="MDL" <?= ($settings['currency'] ?? '') == 'MDL' ? 'selected' : '' ?>>MDL - Moldovan Leu (L)</option>
                                            <option value="UAH" <?= ($settings['currency'] ?? '') == 'UAH' ? 'selected' : '' ?>>UAH - Ukrainian Hryvnia (₴)</option>
                                            <option value="BYN" <?= ($settings['currency'] ?? '') == 'BYN' ? 'selected' : '' ?>>BYN - Belarusian Ruble (Br)</option>
                                            <option value="GEL" <?= ($settings['currency'] ?? '') == 'GEL' ? 'selected' : '' ?>>GEL - Georgian Lari (₾)</option>
                                            <option value="AMD" <?= ($settings['currency'] ?? '') == 'AMD' ? 'selected' : '' ?>>AMD - Armenian Dram (֏)</option>
                                            <option value="AZN" <?= ($settings['currency'] ?? '') == 'AZN' ? 'selected' : '' ?>>AZN - Azerbaijani Manat (₼)</option>
                                            <option value="KZT" <?= ($settings['currency'] ?? '') == 'KZT' ? 'selected' : '' ?>>KZT - Kazakhstani Tenge (₸)</option>
                                            <option value="KGS" <?= ($settings['currency'] ?? '') == 'KGS' ? 'selected' : '' ?>>KGS - Kyrgyzstani Som (с)</option>
                                            <option value="TJS" <?= ($settings['currency'] ?? '') == 'TJS' ? 'selected' : '' ?>>TJS - Tajikistani Somoni (SM)</option>
                                            <option value="TMT" <?= ($settings['currency'] ?? '') == 'TMT' ? 'selected' : '' ?>>TMT - Turkmenistani Manat (T)</option>
                                            <option value="UZS" <?= ($settings['currency'] ?? '') == 'UZS' ? 'selected' : '' ?>>UZS - Uzbekistani Som (сўм)</option>
                                            <option value="MNT" <?= ($settings['currency'] ?? '') == 'MNT' ? 'selected' : '' ?>>MNT - Mongolian Tugrik (₮)</option>
                                            <option value="AFN" <?= ($settings['currency'] ?? '') == 'AFN' ? 'selected' : '' ?>>AFN - Afghan Afghani (؋)</option>
                                            <option value="IRR" <?= ($settings['currency'] ?? '') == 'IRR' ? 'selected' : '' ?>>IRR - Iranian Rial (﷼)</option>
                                            <option value="IQD" <?= ($settings['currency'] ?? '') == 'IQD' ? 'selected' : '' ?>>IQD - Iraqi Dinar (د.ع)</option>
                                            <option value="SYP" <?= ($settings['currency'] ?? '') == 'SYP' ? 'selected' : '' ?>>SYP - Syrian Pound (ل.س)</option>
                                            <option value="YER" <?= ($settings['currency'] ?? '') == 'YER' ? 'selected' : '' ?>>YER - Yemeni Rial (﷼)</option>
                                            <option value="LYD" <?= ($settings['currency'] ?? '') == 'LYD' ? 'selected' : '' ?>>LYD - Libyan Dinar (ل.د)</option>
                                            <option value="TND" <?= ($settings['currency'] ?? '') == 'TND' ? 'selected' : '' ?>>TND - Tunisian Dinar (د.ت)</option>
                                            <option value="DZD" <?= ($settings['currency'] ?? '') == 'DZD' ? 'selected' : '' ?>>DZD - Algerian Dinar (د.ج)</option>
                                            <option value="MAD" <?= ($settings['currency'] ?? '') == 'MAD' ? 'selected' : '' ?>>MAD - Moroccan Dirham (د.م.)</option>
                                            <option value="ETB" <?= ($settings['currency'] ?? '') == 'ETB' ? 'selected' : '' ?>>ETB - Ethiopian Birr (Br)</option>
                                            <option value="KES" <?= ($settings['currency'] ?? '') == 'KES' ? 'selected' : '' ?>>KES - Kenyan Shilling (KSh)</option>
                                            <option value="UGX" <?= ($settings['currency'] ?? '') == 'UGX' ? 'selected' : '' ?>>UGX - Ugandan Shilling (USh)</option>
                                            <option value="TZS" <?= ($settings['currency'] ?? '') == 'TZS' ? 'selected' : '' ?>>TZS - Tanzanian Shilling (TSh)</option>
                                            <option value="RWF" <?= ($settings['currency'] ?? '') == 'RWF' ? 'selected' : '' ?>>RWF - Rwandan Franc (RF)</option>
                                            <option value="MWK" <?= ($settings['currency'] ?? '') == 'MWK' ? 'selected' : '' ?>>MWK - Malawian Kwacha (MK)</option>
                                            <option value="ZMW" <?= ($settings['currency'] ?? '') == 'ZMW' ? 'selected' : '' ?>>ZMW - Zambian Kwacha (ZK)</option>
                                            <option value="BWP" <?= ($settings['currency'] ?? '') == 'BWP' ? 'selected' : '' ?>>BWP - Botswanan Pula (P)</option>
                                            <option value="SZL" <?= ($settings['currency'] ?? '') == 'SZL' ? 'selected' : '' ?>>SZL - Swazi Lilangeni (L)</option>
                                            <option value="LSL" <?= ($settings['currency'] ?? '') == 'LSL' ? 'selected' : '' ?>>LSL - Lesotho Loti (L)</option>
                                            <option value="NAD" <?= ($settings['currency'] ?? '') == 'NAD' ? 'selected' : '' ?>>NAD - Namibian Dollar (N$)</option>
                                            <option value="AOA" <?= ($settings['currency'] ?? '') == 'AOA' ? 'selected' : '' ?>>AOA - Angolan Kwanza (Kz)</option>
                                            <option value="MZN" <?= ($settings['currency'] ?? '') == 'MZN' ? 'selected' : '' ?>>MZN - Mozambican Metical (MT)</option>
                                            <option value="MGA" <?= ($settings['currency'] ?? '') == 'MGA' ? 'selected' : '' ?>>MGA - Malagasy Ariary (Ar)</option>
                                            <option value="MUR" <?= ($settings['currency'] ?? '') == 'MUR' ? 'selected' : '' ?>>MUR - Mauritian Rupee (₨)</option>
                                            <option value="SCR" <?= ($settings['currency'] ?? '') == 'SCR' ? 'selected' : '' ?>>SCR - Seychellois Rupee (₨)</option>
                                            <option value="KMF" <?= ($settings['currency'] ?? '') == 'KMF' ? 'selected' : '' ?>>KMF - Comorian Franc (CF)</option>
                                            <option value="DJF" <?= ($settings['currency'] ?? '') == 'DJF' ? 'selected' : '' ?>>DJF - Djiboutian Franc (Fdj)</option>
                                            <option value="SOS" <?= ($settings['currency'] ?? '') == 'SOS' ? 'selected' : '' ?>>SOS - Somali Shilling (S)</option>
                                            <option value="ERN" <?= ($settings['currency'] ?? '') == 'ERN' ? 'selected' : '' ?>>ERN - Eritrean Nakfa (Nfk)</option>
                                            <option value="STN" <?= ($settings['currency'] ?? '') == 'STN' ? 'selected' : '' ?>>STN - São Tomé and Príncipe Dobra (Db)</option>
                                            <option value="CVE" <?= ($settings['currency'] ?? '') == 'CVE' ? 'selected' : '' ?>>CVE - Cape Verdean Escudo ($)</option>
                                            <option value="GMD" <?= ($settings['currency'] ?? '') == 'GMD' ? 'selected' : '' ?>>GMD - Gambian Dalasi (D)</option>
                                            <option value="GHS" <?= ($settings['currency'] ?? '') == 'GHS' ? 'selected' : '' ?>>GHS - Ghanaian Cedi (₵)</option>
                                            <option value="NGN" <?= ($settings['currency'] ?? '') == 'NGN' ? 'selected' : '' ?>>NGN - Nigerian Naira (₦)</option>
                                            <option value="XOF" <?= ($settings['currency'] ?? '') == 'XOF' ? 'selected' : '' ?>>XOF - West African CFA Franc (CFA)</option>
                                            <option value="XAF" <?= ($settings['currency'] ?? '') == 'XAF' ? 'selected' : '' ?>>XAF - Central African CFA Franc (FCFA)</option>
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
                                        <input type="time" name="working_hours_start" value="<?= $settings['working_hours_start'] ?? '09:00' ?>" class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:border-orange-500 focus:ring-4 focus:ring-orange-100 transition-all duration-200" required>
                                            </div>
                                    <div>
                                        <label class="block text-sm font-semibold text-gray-700 mb-2">End Time</label>
                                        <input type="time" name="working_hours_end" value="<?= $settings['working_hours_end'] ?? '17:00' ?>" class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:border-orange-500 focus:ring-4 focus:ring-orange-100 transition-all duration-200" required>
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
                            </div>
                        </div>
                    </div>
                </div>
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

// Initialize Select2 for all select elements
document.addEventListener('DOMContentLoaded', function() {
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
});
</script>

<!-- Select2 JavaScript -->
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<?= $this->endSection() ?>
