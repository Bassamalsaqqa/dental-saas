                <div id="communications-tab" class="settings-tab hidden">
                    <div class="bg-white/90 backdrop-blur-xl rounded-2xl shadow-xl shadow-blue-500/10 border border-white/30 overflow-hidden">
                        <div class="bg-gradient-to-r from-slate-700 to-slate-900 p-8">
                            <div class="flex items-center space-x-4">
                                <div class="w-12 h-12 bg-white/20 rounded-xl flex items-center justify-center backdrop-blur-sm">
                                    <i class="fas fa-satellite-dish text-sky-400 text-xl"></i>
                                </div>
                                <div>
                                    <h3 class="text-2xl font-bold text-white"><?= lang('UI.settingsCommunications') ?></h3>
                                    <p class="text-slate-300">Delivery channels and notification preferences</p>
                                </div>
                            </div>
                        </div>
                        <div class="border-b border-gray-100 px-8 pt-4">
                            <div class="flex space-x-1 overflow-x-auto pb-0">
                                <button onclick="showSubTab('channels')" id="subtab-channels" class="subtab-btn px-4 py-2 text-sm font-semibold rounded-t-lg whitespace-nowrap">
                                    <i class="fas fa-th-large mr-1"></i><?= lang('UI.settingsChannels') ?>
                                </button>
                                <button onclick="showSubTab('email')" id="subtab-email" class="subtab-btn px-4 py-2 text-sm font-semibold rounded-t-lg whitespace-nowrap">
                                    <i class="fas fa-envelope mr-1"></i><?= lang('UI.settingsEmail') ?>
                                </button>
                                <button onclick="showSubTab('sms')" id="subtab-sms" class="subtab-btn px-4 py-2 text-sm font-semibold rounded-t-lg whitespace-nowrap">
                                    <i class="fas fa-sms mr-1"></i><?= lang('UI.settingsSms') ?>
                                </button>
                                <button onclick="showSubTab('whatsapp')" id="subtab-whatsapp" class="subtab-btn px-4 py-2 text-sm font-semibold rounded-t-lg whitespace-nowrap">
                                    <i class="fab fa-whatsapp mr-1"></i><?= lang('UI.settingsWhatsApp') ?>
                                </button>
                                <button onclick="showSubTab('alerts')" id="subtab-alerts" class="subtab-btn px-4 py-2 text-sm font-semibold rounded-t-lg whitespace-nowrap">
                                    <i class="fas fa-user-cog mr-1"></i><?= lang('UI.settingsMyAlerts') ?>
                                </button>
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
