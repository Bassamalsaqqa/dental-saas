                <div id="tab-platform" class="settings-content">
                    <div id="section-platform-profile" data-section-title="<?= esc(lang('V3Platform.platformProfile'), 'attr') ?>" class="control-settings-card">
                        <div class="control-settings-header">
                            <div class="flex items-center space-x-4">
                                <div class="w-12 h-12 cp-surface cp-radius flex items-center justify-center">
                                    <i class="fas fa-id-badge cp-accent text-xl"></i>
                                </div>
                                <div>
                                    <h3 class="text-xl font-bold text-white"><?= esc(lang('V3Platform.platformProfile')) ?></h3>
                                    <p class="text-slate-300 text-sm"><?= esc(lang('V3Platform.supportIdentityUsedAcrossControlPlaneSurfaces')) ?></p>
                                </div>
                            </div>
                        </div>
                        <div class="cp-panel-padding">
                            <form action="<?= base_url('controlplane/settings/platform') ?>" method="POST" class="space-y-6" data-settings-form="true" data-saving-label="Saving profile...">
                                <?= csrf_field() ?>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                                    <div class="md:col-span-2">
                                        <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2"><?= esc(lang('V3Platform.platformName')) ?></label>
                                        <input type="text" name="platform_name" value="<?= esc($settings['platform_name'] ?? '') ?>" class="control-settings-field" required>
                                        <p class="text-xs text-slate-400 mt-1"><?= esc(lang('V3Platform.shownInTheControlPlaneHeaderAndSupportEmailFooters')) ?></p>
                                    </div>
                                    <div>
                                        <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2"><?= esc(lang('V3Platform.supportEmail')) ?></label>
                                        <input type="email" name="platform_support_email" value="<?= esc($settings['platform_support_email'] ?? '') ?>" placeholder="support@yourdomain.com" class="control-settings-field">
                                        <p class="text-xs text-slate-400 mt-1"><?= esc(lang('V3Platform.usedAsTheReplyToAddressInSystemGeneratedEmailsToClinicAdmins')) ?></p>
                                    </div>
                                    <div>
                                        <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2"><?= esc(lang('V3Platform.supportPhone')) ?></label>
                                        <input type="text" name="platform_support_phone" value="<?= esc($settings['platform_support_phone'] ?? '') ?>" placeholder="+970 000 000 000" class="control-settings-field">
                                        <p class="text-xs text-slate-400 mt-1"><?= esc(lang('V3Platform.displayedOnThePlatformSupportPageAccessibleFromTheClinicDashboard')) ?></p>
                                    </div>
                                    <div>
                                        <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2"><?= esc(lang('V3Platform.whatsappPrimary')) ?></label>
                                        <input type="text" name="platform_support_whatsapp_primary" value="<?= esc($settings['platform_support_whatsapp_primary'] ?? '') ?>" placeholder="+970 000 000 000" class="control-settings-field">
                                        <p class="text-xs text-slate-400 mt-1"><?= esc(lang('V3Platform.primaryWhatsappNumberForSupportRequestsFromClinics')) ?></p>
                                    </div>
                                    <div>
                                        <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2"><?= esc(lang('V3Platform.whatsappSecondary')) ?></label>
                                        <input type="text" name="platform_support_whatsapp_secondary" value="<?= esc($settings['platform_support_whatsapp_secondary'] ?? '') ?>" placeholder="+972 000 000 000" class="control-settings-field">
                                        <p class="text-xs text-slate-400 mt-1"><?= esc(lang('V3Platform.fallbackContactShownWhenThePrimaryNumberIsUnreachable')) ?></p>
                                    </div>
                                    <div class="md:col-span-2">
                                        <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2"><?= esc(lang('V3Platform.supportWebsite')) ?></label>
                                        <input type="url" name="platform_support_website" value="<?= esc($settings['platform_support_website'] ?? '') ?>" placeholder="https://yourdomain.com" class="control-settings-field">
                                        <p class="text-xs text-slate-400 mt-1"><?= esc(lang('V3Platform.linkToYourSupportPortalOrDocumentationSiteIncludeHttps')) ?></p>
                                    </div>
                                </div>
                                <div class="flex justify-end">
                                    <button type="submit" class="control-settings-submit">
                                        <i class="fas fa-save mr-2"></i><?= esc(lang('V3Platform.saveProfile')) ?>
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
