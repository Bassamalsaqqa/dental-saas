                <div id="clinic-tab" class="settings-tab">
                    <div class="v4-settings-panel">
                        <header class="v4-settings-panel-heading"><h2><?= lang('UI.settingsClinicProfile') ?></h2><p><?= lang('UI.settingsClinicProfileDesc') ?></p></header>
                        <div class="v4-settings-panel-body">
                            <form class="v4-form" action="<?= base_url('settings/updateClinic') ?>" method="POST" enctype="multipart/form-data">
                                <?= csrf_field() ?>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <div><label class="block text-sm font-semibold text-gray-700 mb-2"><?= lang('UI.settingsClinicName') ?></label><input type="text" name="clinic_name" value="<?= esc($settings['clinic_name'] ?? '') ?>" class="v4-field w-full" required><p class="text-xs text-gray-500 mt-1"><?= lang('UI.settingsClinicNameHelp') ?></p></div>
                                    <div><label class="block text-sm font-semibold text-gray-700 mb-2"><?= lang('UI.settingsClinicPhone') ?></label><input type="tel" name="clinic_phone" value="<?= esc($settings['clinic_phone'] ?? '') ?>" class="v4-field w-full" required><p class="text-xs text-gray-500 mt-1"><?= lang('UI.settingsClinicPhoneHelp') ?></p></div>
                                    <div><label class="block text-sm font-semibold text-gray-700 mb-2"><?= lang('UI.settingsClinicEmail') ?></label><input type="email" name="clinic_email" value="<?= esc($settings['clinic_email'] ?? '') ?>" class="v4-field w-full" required><p class="text-xs text-gray-500 mt-1"><?= lang('UI.settingsClinicEmailHelp') ?></p></div>
                                    <div><label class="block text-sm font-semibold text-gray-700 mb-2"><?= lang('UI.settingsClinicWebsite') ?></label><input type="url" name="clinic_website" value="<?= esc($settings['clinic_website'] ?? '') ?>" class="v4-field w-full"><p class="text-xs text-gray-500 mt-1"><?= lang('UI.settingsClinicWebsiteHelp') ?></p></div>
                                    <div><label class="block text-sm font-semibold text-gray-700 mb-2"><?= lang('UI.settingsClinicTagline') ?></label><input type="text" name="clinic_tagline" value="<?= esc($settings['clinic_tagline'] ?? '') ?>" class="v4-field w-full"><p class="text-xs text-gray-500 mt-1"><?= lang('UI.settingsClinicTaglineHelp') ?></p></div>
                                    <div><label class="block text-sm font-semibold text-gray-700 mb-2"><?= lang('UI.settingsClinicLogo') ?></label><input type="file" name="clinic_logo" class="v4-field w-full"><p class="text-xs text-gray-500 mt-1"><?= lang('UI.settingsClinicLogoHelp') ?></p></div>
                                    <div class="md:col-span-2"><label class="block text-sm font-semibold text-gray-700 mb-2"><?= lang('UI.settingsClinicAddress') ?></label><textarea name="clinic_address" rows="3" class="v4-field w-full" required><?= esc($settings['clinic_address'] ?? '') ?></textarea><p class="text-xs text-gray-500 mt-1"><?= lang('UI.settingsClinicAddressHelp') ?></p></div>
                                </div>
                                <div class="mt-8 flex justify-end"><button type="submit" class="v4-button v4-button--primary"><i class="fas fa-save mr-2"></i><?= lang('UI.settingsSaveChanges') ?></button></div>
                            </form>
                        </div>
                    </div>
                </div>
