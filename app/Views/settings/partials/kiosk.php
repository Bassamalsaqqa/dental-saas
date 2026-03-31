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
                                        <p class="text-sm text-slate-500"><?= lang('TenantMisc.kioskEnableHelp') ?></p>
                                    </div>
                                    <label class="inline-flex items-center cursor-pointer">
                                        <input type="checkbox" name="kiosk_enabled" value="1" class="ui-toggle kiosk-toggle" <?= !empty($settings['kiosk_enabled']) ? 'checked' : '' ?> <?= $kioskFeatureEnabled ? '' : 'disabled' ?>>
                                    </label>
                                </div>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <div>
                                        <label class="block text-sm font-semibold text-gray-700 mb-2"><?= lang('TenantMisc.kioskHeader') ?></label>
                                        <input type="text" name="kiosk_header" value="<?= esc($settings['kiosk_header'] ?? lang('TenantMisc.kioskHeaderDefault')) ?>" class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:border-blue-500 focus:ring-4 focus:ring-blue-100 transition-all duration-200" <?= $kioskFeatureEnabled ? '' : 'disabled' ?>>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-semibold text-gray-700 mb-2"><?= lang('TenantMisc.kioskSubheader') ?></label>
                                        <input type="text" name="kiosk_subheader" value="<?= esc($settings['kiosk_subheader'] ?? lang('TenantMisc.kioskSubheaderDefault')) ?>" class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:border-blue-500 focus:ring-4 focus:ring-blue-100 transition-all duration-200" <?= $kioskFeatureEnabled ? '' : 'disabled' ?>>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-semibold text-gray-700 mb-2"><?= lang('TenantMisc.defaultLanguage') ?></label>
                                        <select name="kiosk_default_language" class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:border-blue-500 focus:ring-4 focus:ring-blue-100 transition-all duration-200" <?= $kioskFeatureEnabled ? '' : 'disabled' ?>>
                                            <option value="ar" <?= (($settings['kiosk_default_language'] ?? 'ar') === 'ar') ? 'selected' : '' ?>><?= lang('TenantMisc.arabicDefault') ?></option>
                                            <option value="en" <?= (($settings['kiosk_default_language'] ?? 'ar') === 'en') ? 'selected' : '' ?>><?= lang('TenantMisc.english') ?></option>
                                        </select>
                                    </div>
                                    <div class="md:col-span-2">
                                        <label class="block text-sm font-semibold text-gray-700 mb-2"><?= lang('TenantMisc.footerNoteOptional') ?></label>
                                        <input type="text" name="kiosk_footer_note" value="<?= esc($settings['kiosk_footer_note'] ?? lang('TenantMisc.kioskFooterDefault')) ?>" class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:border-blue-500 focus:ring-4 focus:ring-blue-100 transition-all duration-200" <?= $kioskFeatureEnabled ? '' : 'disabled' ?>>
                                    </div>
                                </div>

                                <div class="border-t border-gray-100 pt-6">
                                    <h5 class="text-sm font-bold text-slate-800 mb-3"><?= lang('TenantMisc.kioskPin') ?></h5>
                                    <p class="text-xs text-slate-500 mb-4"><?= lang('TenantMisc.kioskPinHelp') ?></p>
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                        <div>
                                            <label class="block text-sm font-semibold text-gray-700 mb-2"><?= lang('TenantMisc.newPin') ?></label>
                                            <input type="password" inputmode="numeric" name="kiosk_pin" dir="ltr" style="text-align: left;" class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:border-blue-500 focus:ring-4 focus:ring-blue-100 transition-all duration-200" placeholder="<?= lang('TenantMisc.pinDigitsPlaceholder') ?>" <?= $kioskFeatureEnabled ? '' : 'disabled' ?>>
                                        </div>
                                        <div>
                                            <label class="block text-sm font-semibold text-gray-700 mb-2"><?= lang('TenantMisc.confirmPin') ?></label>
                                            <input type="password" inputmode="numeric" name="kiosk_pin_confirm" dir="ltr" style="text-align: left;" class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:border-blue-500 focus:ring-4 focus:ring-blue-100 transition-all duration-200" placeholder="<?= lang('TenantMisc.reenterPin') ?>" <?= $kioskFeatureEnabled ? '' : 'disabled' ?>>
                                        </div>
                                    </div>
                                </div>

                                <div class="flex justify-end">
                                    <button type="submit" class="px-8 py-3 bg-gradient-to-r from-indigo-500 to-blue-600 text-white font-semibold rounded-xl hover:from-indigo-600 hover:to-blue-700 transition-all duration-200 shadow-lg" <?= $kioskFeatureEnabled ? '' : 'disabled' ?>>
                                        <i class="fas fa-save mr-2"></i><?= lang('UI.settingsSaveKioskSettings') ?>
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
