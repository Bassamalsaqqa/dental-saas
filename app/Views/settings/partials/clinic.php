                <div id="clinic-tab" class="settings-tab">
                    <div class="v3-surface v3-radius border v3-border overflow-hidden">
                        <div class="p-4 v3-form-section-header">
                            <div class="flex items-center space-x-4">
                                <div class="w-12 h-12 v3-surface v3-radius flex items-center justify-center"><i class="fas fa-building v3-accent-text text-xl"></i></div>
                                <div><h3 class="text-2xl font-bold v3-text-on-primary"><?= lang('UI.settingsClinicProfile') ?></h3><p class="v3-text-muted"><?= lang('UI.settingsClinicProfileDesc') ?></p></div>
                            </div>
                        </div>
                        <div class="p-4">
                            <form action="<?= base_url('settings/updateClinic') ?>" method="POST" enctype="multipart/form-data">
                                <?= csrf_field() ?>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <div><label class="block text-sm font-semibold v3-text-ink mb-2"><?= lang('UI.settingsClinicName') ?></label><input type="text" name="clinic_name" value="<?= $settings['clinic_name'] ?? '' ?>" class="w-full px-4 py-3 border-2 v3-border v3-radius v3-focus-control focus:ring-4 v3-focus-control transition-all v3-input" required><p class="text-xs v3-text-muted mt-1"><?= lang('UI.settingsClinicNameHelp') ?></p></div>
                                    <div><label class="block text-sm font-semibold v3-text-ink mb-2"><?= lang('UI.settingsClinicPhone') ?></label><input type="tel" name="clinic_phone" value="<?= $settings['clinic_phone'] ?? '' ?>" class="w-full px-4 py-3 border-2 v3-border v3-radius v3-focus-control focus:ring-4 v3-focus-control transition-all v3-input" required><p class="text-xs v3-text-muted mt-1"><?= lang('UI.settingsClinicPhoneHelp') ?></p></div>
                                    <div><label class="block text-sm font-semibold v3-text-ink mb-2"><?= lang('UI.settingsClinicEmail') ?></label><input type="email" name="clinic_email" value="<?= $settings['clinic_email'] ?? '' ?>" class="w-full px-4 py-3 border-2 v3-border v3-radius v3-focus-control focus:ring-4 v3-focus-control transition-all v3-input" required><p class="text-xs v3-text-muted mt-1"><?= lang('UI.settingsClinicEmailHelp') ?></p></div>
                                    <div><label class="block text-sm font-semibold v3-text-ink mb-2"><?= lang('UI.settingsClinicWebsite') ?></label><input type="url" name="clinic_website" value="<?= esc($settings['clinic_website'] ?? '') ?>" class="w-full px-4 py-3 border-2 v3-border v3-radius v3-focus-control focus:ring-4 v3-focus-control transition-all v3-input"><p class="text-xs v3-text-muted mt-1"><?= lang('UI.settingsClinicWebsiteHelp') ?></p></div>
                                    <div><label class="block text-sm font-semibold v3-text-ink mb-2"><?= lang('UI.settingsClinicTagline') ?></label><input type="text" name="clinic_tagline" value="<?= esc($settings['clinic_tagline'] ?? '') ?>" class="w-full px-4 py-3 border-2 v3-border v3-radius v3-focus-control focus:ring-4 v3-focus-control transition-all v3-input"><p class="text-xs v3-text-muted mt-1"><?= lang('UI.settingsClinicTaglineHelp') ?></p></div>
                                    <div><label class="block text-sm font-semibold v3-text-ink mb-2"><?= lang('UI.settingsClinicLogo') ?></label><input type="file" name="clinic_logo" class="w-full px-4 py-3 border-2 v3-border v3-radius v3-input"><p class="text-xs v3-text-muted mt-1"><?= lang('UI.settingsClinicLogoHelp') ?></p></div>
                                    <div class="md:col-span-2"><label class="block text-sm font-semibold v3-text-ink mb-2"><?= lang('UI.settingsClinicAddress') ?></label><textarea name="clinic_address" rows="3" class="w-full px-4 py-3 border-2 v3-border v3-radius v3-focus-control focus:ring-4 v3-focus-control transition-all v3-textarea" required><?= esc($settings['clinic_address'] ?? '') ?></textarea><p class="text-xs v3-text-muted mt-1"><?= lang('UI.settingsClinicAddressHelp') ?></p></div>
                                </div>
                                <div class="mt-8 flex justify-end"><button type="submit" class="px-8 py-3 v3-text-on-primary font-semibold v3-radius transition-all v3-form-section-header v3-btn v3-btn--primary"><i class="fas fa-save mr-2"></i><?= lang('UI.settingsSaveChanges') ?></button></div>
                            </form>
                        </div>
                    </div>
                </div>
