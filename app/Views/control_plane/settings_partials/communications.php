                <div id="tab-communications" class="settings-content hidden">
                    <div class="bg-white rounded-2xl shadow-xl border border-slate-200 overflow-hidden">
                        <div class="bg-gradient-to-r from-slate-700 to-slate-900 p-8">
                            <div class="flex items-center space-x-4">
                                <div class="w-12 h-12 bg-white/10 rounded-xl flex items-center justify-center backdrop-blur-sm">
                                    <i class="fas fa-satellite-dish text-sky-400 text-xl"></i>
                                </div>
                                <div>
                                    <h3 class="text-xl font-bold text-white">Communications</h3>
                                    <p class="text-slate-300 text-sm">Email templates and WhatsApp platform configuration</p>
                                </div>
                            </div>
                        </div>

                        <div class="border-b border-slate-100 px-8 pt-4">
                            <div class="flex space-x-1 overflow-x-auto pb-0">
                                <button onclick="showCommsSubTab('email-templates')" id="comms-subtab-email-templates" class="comms-subtab px-5 py-2.5 text-sm font-bold rounded-t-lg whitespace-nowrap border-b-2 border-transparent text-slate-500 hover:text-slate-700 transition-all">
                                    <i class="fas fa-envelope-open-text mr-2"></i>Email Templates
                                </button>
                                <button onclick="showCommsSubTab('whatsapp')" id="comms-subtab-whatsapp" class="comms-subtab px-5 py-2.5 text-sm font-bold rounded-t-lg whitespace-nowrap border-b-2 border-transparent text-slate-500 hover:text-slate-700 transition-all">
                                    <i class="fab fa-whatsapp mr-2"></i>WhatsApp
                                </button>
                            </div>
                        </div>

                        <div class="p-8">
                            <?= $this->include('control_plane/settings_partials/communications/email_templates') ?>
                            <?= $this->include('control_plane/settings_partials/communications/whatsapp') ?>
                        </div>
                    </div>
                </div>
