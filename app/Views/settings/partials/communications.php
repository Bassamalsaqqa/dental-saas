                <div id="communications-tab" class="settings-tab hidden">
                    <div class="v3-surface v3-radius border v3-border overflow-hidden">
                        <div class="p-4 v3-form-section-header">
                            <div class="flex items-center space-x-4">
                                <div class="w-12 h-12 v3-surface v3-radius flex items-center justify-center">
                                    <i class="fas fa-satellite-dish v3-accent-text text-xl"></i>
                                </div>
                                <div>
                                    <h3 class="text-2xl font-bold text-white"><?= lang('UI.settingsCommunications') ?></h3>
                                    <p class="text-slate-300"><?= lang('UI.settingsConfigureSystem') ?></p>
                                </div>
                            </div>
                        </div>
                        <div class="p-4">
                            <?= $this->include('settings/partials/communications/channels') ?>
                            <?= $this->include('settings/partials/communications/email') ?>
                            <?= $this->include('settings/partials/communications/sms') ?>
                            <?= $this->include('settings/partials/communications/whatsapp') ?>
                            <?= $this->include('settings/partials/communications/alerts') ?>
                        </div>
                    </div>
                </div>
