                <div id="system-tab" class="settings-tab hidden">
                    <div class="v4-settings-panel">
                        <header class="v4-settings-panel-heading"><h2><?= lang('UI.settingsSystemTab') ?></h2><p><?= lang('UI.settingsSystemDesc') ?></p></header>
                        <div class="v4-settings-panel-body">
                            <form id="system-settings-form" action="<?= base_url('settings/updateSystem') ?>" method="POST">
                                <?= csrf_field() ?>
                                <input type="hidden" name="redirect_tab" value="system">
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <div><label class="block text-sm font-semibold text-gray-700 mb-2"><?= lang('TenantMisc.timezone') ?></label><select name="timezone" class="v4-field w-full" required><?php $tzs = DateTimeZone::listIdentifiers(); foreach($tzs as $tz): ?><option value="<?= $tz ?>" <?= ($settings['timezone'] ?? '') == $tz ? 'selected' : '' ?>><?= $tz ?></option><?php endforeach; ?></select><p class="text-xs text-gray-500 mt-1"><?= lang('TenantMisc.timezoneHelp') ?></p></div>
                                    <div><label class="block text-sm font-semibold text-gray-700 mb-2"><?= lang('TenantMisc.dateFormat') ?></label><select name="date_format" class="v4-field w-full" required><option value="Y-m-d" <?= ($settings['date_format'] ?? '') == 'Y-m-d' ? 'selected' : '' ?>>YYYY-MM-DD</option><option value="m/d/Y" <?= ($settings['date_format'] ?? '') == 'm/d/Y' ? 'selected' : '' ?>>MM/DD/YYYY</option><option value="d/m/Y" <?= ($settings['date_format'] ?? '') == 'd/m/Y' ? 'selected' : '' ?>>DD/MM/YYYY</option></select><p class="text-xs text-gray-500 mt-1"><?= lang('TenantMisc.dateFormatHelp') ?></p></div>
                                    <div><label class="block text-sm font-semibold text-gray-700 mb-2"><?= lang('TenantMisc.timeFormat') ?></label><select name="time_format" class="v4-field w-full" required><option value="24" <?= ($settings['time_format'] ?? '') == '24' ? 'selected' : '' ?>><?= lang('TenantMisc.hour24') ?></option><option value="12" <?= ($settings['time_format'] ?? '') == '12' ? 'selected' : '' ?>><?= lang('TenantMisc.hour12') ?></option></select><p class="text-xs text-gray-500 mt-1"><?= lang('TenantMisc.timeFormatHelp') ?></p></div>
                                    <div>
                                        <label class="block text-sm font-semibold text-gray-700 mb-2">UI Language</label>
                                        <select name="ui_language" class="v4-field w-full" required>
                                            <option value="en" <?= ($settings['ui_language'] ?? 'en') == 'en' ? 'selected' : '' ?>>English</option>
                                            <option value="ar" <?= ($settings['ui_language'] ?? 'en') == 'ar' ? 'selected' : '' ?>>Arabic</option>
                                        </select>
                                        <p class="text-xs text-gray-500 mt-1">Changes the interface language.</p>
                                    </div>
                                </div>
                                <div class="mt-8 flex justify-end"><button type="button" onclick="submitSystemSettings()" class="px-8 py-3 bg-gradient-to-r from-green-500 to-emerald-600 text-white font-semibold rounded-xl shadow-lg transition-all"><i class="fas fa-save mr-2"></i><?= lang('UI.settingsSaveChanges') ?></button></div>
                            </form>
                        </div>
                    </div>
                </div>
