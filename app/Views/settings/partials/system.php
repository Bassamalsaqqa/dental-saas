                <div id="system-tab" class="settings-tab hidden">
                    <div class="v3-surface v3-radius border v3-border overflow-hidden">
                        <div class="p-4 v3-form-section-header">
                            <div class="flex items-center space-x-4">
                                <div class="w-12 h-12 v3-surface v3-radius flex items-center justify-center"><i class="fas fa-cog v3-accent-text text-xl"></i></div>
                                <div><h3 class="text-2xl font-bold text-white"><?= lang('UI.settingsSystemTab') ?></h3><p class="text-slate-300"><?= lang('UI.settingsSystemDesc') ?></p></div>
                            </div>
                        </div>
                        <div class="p-4">
                            <form id="system-settings-form" action="<?= base_url('settings/updateSystem') ?>" method="POST">
                                <?= csrf_field() ?>
                                <input type="hidden" name="redirect_tab" value="system">
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <div><label class="block text-sm font-semibold text-gray-700 mb-2"><?= lang('TenantMisc.timezone') ?></label><select name="timezone" class="w-full px-4 py-3 border-2 v3-border v3-radius focus:border-green-500 transition-all" required><?php $tzs = DateTimeZone::listIdentifiers(); foreach($tzs as $tz): ?><option value="<?= $tz ?>" <?= ($settings['timezone'] ?? '') == $tz ? 'selected' : '' ?>><?= $tz ?></option><?php endforeach; ?></select><p class="text-xs text-gray-500 mt-1"><?= lang('TenantMisc.timezoneHelp') ?></p></div>
                                    <div><label class="block text-sm font-semibold text-gray-700 mb-2"><?= lang('TenantMisc.dateFormat') ?></label><select name="date_format" class="w-full px-4 py-3 border-2 v3-border v3-radius focus:border-green-500 transition-all" required><option value="Y-m-d" <?= ($settings['date_format'] ?? '') == 'Y-m-d' ? 'selected' : '' ?>>YYYY-MM-DD</option><option value="m/d/Y" <?= ($settings['date_format'] ?? '') == 'm/d/Y' ? 'selected' : '' ?>>MM/DD/YYYY</option><option value="d/m/Y" <?= ($settings['date_format'] ?? '') == 'd/m/Y' ? 'selected' : '' ?>>DD/MM/YYYY</option></select><p class="text-xs text-gray-500 mt-1"><?= lang('TenantMisc.dateFormatHelp') ?></p></div>
                                    <div><label class="block text-sm font-semibold text-gray-700 mb-2"><?= lang('TenantMisc.timeFormat') ?></label><select name="time_format" class="w-full px-4 py-3 border-2 v3-border v3-radius focus:border-green-500 transition-all" required><option value="24" <?= ($settings['time_format'] ?? '') == '24' ? 'selected' : '' ?>><?= lang('TenantMisc.hour24') ?></option><option value="12" <?= ($settings['time_format'] ?? '') == '12' ? 'selected' : '' ?>><?= lang('TenantMisc.hour12') ?></option></select><p class="text-xs text-gray-500 mt-1"><?= lang('TenantMisc.timeFormatHelp') ?></p></div>
                                    <div>
                                        <label class="block text-sm font-semibold text-gray-700 mb-2">UI Language</label>
                                        <select name="ui_language" class="w-full px-4 py-3 border-2 v3-border v3-radius focus:border-green-500 transition-all" required>
                                            <option value="en" <?= ($settings['ui_language'] ?? 'en') == 'en' ? 'selected' : '' ?>>English</option>
                                            <option value="ar" <?= ($settings['ui_language'] ?? 'en') == 'ar' ? 'selected' : '' ?>>Arabic</option>
                                        </select>
                                        <p class="text-xs text-gray-500 mt-1">Changes the interface language.</p>
                                    </div>
                                </div>
                                <div class="mt-8 flex justify-end"><button type="button" onclick="submitSystemSettings()" class="px-8 py-3 text-white font-semibold v3-radius transition-all v3-form-section-header"><i class="fas fa-save mr-2"></i><?= lang('UI.settingsSaveChanges') ?></button></div>
                            </form>
                        </div>
                    </div>
                </div>
