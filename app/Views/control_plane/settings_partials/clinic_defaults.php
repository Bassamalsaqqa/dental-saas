                    <form action="<?= base_url('controlplane/settings/tenant-defaults') ?>" method="POST" class="space-y-8" data-settings-form="true" data-saving-label="Saving defaults...">
                        <?= csrf_field() ?>
                        <div id="section-clinic-defaults-system" data-section-title="System Preferences" class="control-settings-card">
                            <div class="control-settings-header">
                                <div class="flex items-center space-x-4">
                                    <div class="w-12 h-12 bg-white/20 rounded-xl flex items-center justify-center backdrop-blur-sm">
                                        <i class="fas fa-cog text-blue-400 text-xl"></i>
                                    </div>
                                    <div>
                                        <h3 class="text-xl font-bold text-white">System Preferences</h3>
                                        <p class="text-slate-300 text-sm">Defaults applied when a new clinic is onboarded</p>
                                    </div>
                                </div>
                            </div>

                            <div class="p-8">
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <div>
                                        <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Timezone</label>
                                        <select name="default_timezone" class="control-settings-field" required>
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
                                            <option value="America/Sao_Paulo" <?= $defaultTimezone === 'America/Sao_Paulo' ? 'selected' : '' ?>>Sao Paulo (BRT)</option>
                                            <option value="America/Buenos_Aires" <?= $defaultTimezone === 'America/Buenos_Aires' ? 'selected' : '' ?>>Buenos Aires (ART)</option>
                                            <option value="America/Lima" <?= $defaultTimezone === 'America/Lima' ? 'selected' : '' ?>>Lima (PET)</option>
                                            <option value="America/Bogota" <?= $defaultTimezone === 'America/Bogota' ? 'selected' : '' ?>>Bogota (COT)</option>
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
                                        <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Date Format</label>
                                        <?php $defaultDateFormat = $settings['default_date_format'] ?? 'd/m/Y'; ?>
                                        <select name="default_date_format" class="control-settings-field" required>
                                            <option value="Y-m-d" <?= $defaultDateFormat === 'Y-m-d' ? 'selected' : '' ?>>YYYY-MM-DD</option>
                                            <option value="m/d/Y" <?= $defaultDateFormat === 'm/d/Y' ? 'selected' : '' ?>>MM/DD/YYYY</option>
                                            <option value="d/m/Y" <?= $defaultDateFormat === 'd/m/Y' ? 'selected' : '' ?>>DD/MM/YYYY</option>
                                        </select>
                                        <p class="text-xs text-slate-400 mt-1">Controls how dates appear in patient lists, invoices, and reminders.</p>
                                    </div>
                                    <div>
                                        <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Time Format</label>
                                        <?php $defaultTimeFormat = (string) ($settings['default_time_format'] ?? '12'); ?>
                                        <select name="default_time_format" class="control-settings-field" required>
                                            <option value="24" <?= $defaultTimeFormat === '24' ? 'selected' : '' ?>>24 Hour</option>
                                            <option value="12" <?= $defaultTimeFormat === '12' ? 'selected' : '' ?>>12 Hour</option>
                                        </select>
                                        <p class="text-xs text-slate-400 mt-1">12-hour (AM/PM) or 24-hour display across all clinic interfaces.</p>
                                    </div>
                                    <div>
                                        <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Currency</label>
                                        <?php $defaultCurrency = $settings['default_currency'] ?? 'ILS'; ?>
                                        <select name="default_currency" class="control-settings-field" required>
                                            <option value="ILS" <?= $defaultCurrency == 'ILS' ? 'selected' : '' ?>>ILS - Israeli Shekel (ILS)</option>
                                            <option value="USD" <?= $defaultCurrency == 'USD' ? 'selected' : '' ?>>USD - US Dollar (USD)</option>
                                            <option value="JOD" <?= $defaultCurrency == 'JOD' ? 'selected' : '' ?>>JOD - Jordanian Dinar (JOD)</option>
                                            <option value="EUR" <?= $defaultCurrency == 'EUR' ? 'selected' : '' ?>>EUR - Euro (EUR)</option>
                                            <option value="GBP" <?= $defaultCurrency == 'GBP' ? 'selected' : '' ?>>GBP - British Pound (GBP)</option>
                                        </select>
                                        <p class="text-xs text-slate-400 mt-1">Currency used on invoices and financial reports for new clinics.</p>
                                    </div>
                                    <div>
                                        <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Currency Position</label>
                                        <?php $defaultCurrencyPosition = $settings['default_currency_position'] ?? 'after'; ?>
                                        <select name="default_currency_position" class="control-settings-field" required>
                                            <option value="before" <?= $defaultCurrencyPosition === 'before' ? 'selected' : '' ?>>Before Amount (e.g., $100)</option>
                                            <option value="after" <?= $defaultCurrencyPosition === 'after' ? 'selected' : '' ?>>After Amount (e.g., 100$)</option>
                                        </select>
                                        <p class="text-xs text-slate-400 mt-1">Whether the symbol appears before or after the amount (for example, $120 vs 120 ILS).</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div id="section-clinic-defaults-hours" data-section-title="Working Hours" class="control-settings-card">
                            <div class="control-settings-header">
                                <div class="flex items-center space-x-4">
                                    <div class="w-12 h-12 bg-white/20 rounded-xl flex items-center justify-center backdrop-blur-sm">
                                        <i class="fas fa-clock text-blue-400 text-xl"></i>
                                    </div>
                                    <div>
                                        <h3 class="text-xl font-bold text-white">Working Hours</h3>
                                        <p class="text-slate-300 text-sm">Set default operating hours and working days</p>
                                    </div>
                                </div>
                            </div>

                            <div class="p-8">
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <div>
                                        <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Start Time</label>
                                        <input type="time" name="default_working_hours_start" data-clinic-time="1" value="<?= esc($settings['default_working_hours_start'] ?? '09:00') ?>" class="control-settings-field" required>
                                        <p class="text-xs text-slate-400 mt-1">Default calendar open/close times applied to new clinics.</p>
                                    </div>
                                    <div>
                                        <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">End Time</label>
                                        <input type="time" name="default_working_hours_end" data-clinic-time="1" value="<?= esc($settings['default_working_hours_end'] ?? '17:00') ?>" class="control-settings-field" required>
                                        <p class="text-xs text-slate-400 mt-1">Default calendar open/close times applied to new clinics.</p>
                                    </div>
                                    <div class="md:col-span-2">
                                        <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Working Days</label>
                                        <p class="text-xs text-slate-400 mt-1 mb-3">Days the clinic is open by default. Clinics can override their own schedule.</p>
                                        <?php
                                            $workingDefaults = $settings['default_working_days'] ?? ['monday', 'tuesday', 'wednesday', 'thursday', 'friday'];
                                            $days = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'];
                                            $dayLabels = ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'];
                                        ?>
                                        <div class="grid grid-cols-7 gap-2">
                                            <?php foreach ($days as $index => $day): ?>
                                                <label class="flex flex-col items-center p-3 border-2 border-slate-200 rounded-xl cursor-pointer hover:border-blue-300 transition-all duration-200 <?= in_array($day, $workingDefaults, true) ? 'border-blue-500 bg-blue-50' : '' ?>">
                                                    <input type="checkbox" name="default_working_days[]" value="<?= esc($day) ?>" <?= in_array($day, $workingDefaults, true) ? 'checked' : '' ?> class="sr-only">
                                                    <span class="text-sm font-medium text-gray-700"><?= esc($dayLabels[$index]) ?></span>
                                                </label>
                                            <?php endforeach; ?>
                                        </div>
                                    </div>
                                    <div>
                                        <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Appointment Duration (minutes)</label>
                                        <input type="number" name="default_appointment_duration" value="<?= esc((int) ($settings['default_appointment_duration'] ?? 30)) ?>" min="15" max="480" class="control-settings-field" required>
                                        <p class="text-xs text-slate-400 mt-1">Initial slot length for the clinic calendar in minutes. Clinics can override after onboarding.</p>
                                    </div>
                                    <div>
                                        <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Clinic Max Chairs</label>
                                        <input type="number" name="default_booking_chair_max" value="<?= esc((int) ($settings['default_booking_chair_max'] ?? 3)) ?>" min="1" max="3" class="control-settings-field" required>
                                        <p class="text-xs text-slate-400 mt-1">Platform-level cap on concurrent appointment slots per time slot. Cannot exceed 3.</p>
                                    </div>
                                </div>

                                <div class="mt-8 flex justify-end">
                                    <button type="submit" class="control-settings-submit">
                                        <i class="fas fa-save mr-2"></i>Save Clinic Defaults
                                    </button>
                                </div>
                            </div>
                        </div>
                    </form>
