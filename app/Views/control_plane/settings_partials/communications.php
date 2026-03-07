                <div id="tab-communications" class="settings-content hidden">
                    <div class="bg-white/90 backdrop-blur-xl rounded-2xl shadow-xl shadow-blue-500/10 border border-white/50 overflow-hidden">
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
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 pb-6">
                                <a href="<?= base_url('controlplane/whatsapp') ?>" class="group rounded-2xl border border-emerald-100 bg-emerald-50 px-5 py-4 transition-all hover:shadow-md hover:border-emerald-200">
                                    <div class="flex items-center justify-between gap-4">
                                        <div>
                                            <p class="text-[11px] font-black uppercase tracking-widest text-emerald-600">Live Operations</p>
                                            <p class="mt-2 text-sm font-semibold text-slate-900">Open WhatsApp command center</p>
                                            <p class="mt-1 text-xs text-slate-500">Use the operational dashboard for journal monitoring, health status, and reactive actions.</p>
                                        </div>
                                        <i class="fas fa-arrow-up-right-from-square text-emerald-600 group-hover:translate-x-0.5 transition-transform"></i>
                                    </div>
                                </a>
                                <a href="<?= base_url('controlplane/operations') ?>" class="group rounded-2xl border border-amber-100 bg-amber-50 px-5 py-4 transition-all hover:shadow-md hover:border-amber-200">
                                    <div class="flex items-center justify-between gap-4">
                                        <div>
                                            <p class="text-[11px] font-black uppercase tracking-widest text-amber-600">Operations</p>
                                            <p class="mt-2 text-sm font-semibold text-slate-900">Open operations console</p>
                                            <p class="mt-1 text-xs text-slate-500">Use operations for runtime health, audits, and other control-plane tooling outside pure configuration.</p>
                                        </div>
                                        <i class="fas fa-arrow-up-right-from-square text-amber-600 group-hover:translate-x-0.5 transition-transform"></i>
                                    </div>
                                </a>
                            </div>
                            <div class="flex space-x-1 overflow-x-auto pb-0">
                                <button onclick="showCommsSubTab('email-templates')" id="comms-subtab-email-templates" class="comms-subtab px-5 py-2.5 text-sm font-semibold rounded-t-lg whitespace-nowrap border-b-2 border-transparent text-slate-500 hover:text-slate-700 transition-all">
                                    <i class="fas fa-envelope-open-text mr-2"></i>Email Templates
                                </button>
                                <button onclick="showCommsSubTab('whatsapp')" id="comms-subtab-whatsapp" class="comms-subtab px-5 py-2.5 text-sm font-semibold rounded-t-lg whitespace-nowrap border-b-2 border-transparent text-slate-500 hover:text-slate-700 transition-all">
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
