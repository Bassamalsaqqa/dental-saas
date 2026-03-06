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
