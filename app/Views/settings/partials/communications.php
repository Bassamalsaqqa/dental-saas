                <div id="communications-tab" class="settings-tab hidden">
                    <div class="bg-white/90 backdrop-blur-xl rounded-2xl shadow-xl shadow-blue-500/10 border border-white/30 overflow-hidden">
                        <div class="bg-gradient-to-r from-slate-700 to-slate-900 p-8">
                            <div class="flex items-center space-x-4">
                                <div class="w-12 h-12 bg-white/20 rounded-xl flex items-center justify-center backdrop-blur-sm">
                                    <i class="fas fa-satellite-dish text-sky-400 text-xl"></i>
                                </div>
                                <div>
                                    <h3 class="text-2xl font-bold text-white"><?= lang('UI.settingsCommunications') ?></h3>
                                    <p class="text-slate-300"><?= lang('UI.settingsConfigureSystem') ?></p>
                                </div>
                            </div>
                        </div>
                        <div class="p-8">
                            <?= $this->include('settings/partials/communications/channels') ?>
                            <?= $this->include('settings/partials/communications/email') ?>
                            <?= $this->include('settings/partials/communications/sms') ?>
                            <?= $this->include('settings/partials/communications/whatsapp') ?>
                            <?= $this->include('settings/partials/communications/alerts') ?>
                        </div>
                    </div>
                </div>
