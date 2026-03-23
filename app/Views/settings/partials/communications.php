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
                        <div class="border-b border-gray-100 px-8 py-5">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div class="rounded-2xl border border-sky-100 bg-sky-50 px-5 py-4">
                                    <p class="text-[11px] font-black uppercase tracking-widest text-sky-600">Direct Access</p>
                                    <p class="mt-2 text-sm font-semibold text-slate-900">Each channel now lives in its own sidebar menu item</p>
                                    <p class="mt-1 text-xs text-slate-500">Open Channels, Email, SMS, WhatsApp, or Alerts directly from the left menu without extra tabs.</p>
                                </div>
                                <div class="rounded-2xl border border-violet-100 bg-violet-50 px-5 py-4">
                                    <p class="text-[11px] font-black uppercase tracking-widest text-violet-600">Clinic Identity</p>
                                    <p class="mt-2 text-sm font-semibold text-slate-900">Shared senders still use clinic-specific profile data</p>
                                    <p class="mt-1 text-xs text-slate-500">WhatsApp templates inherit clinic name, phone, and address from the Clinic Profile page.</p>
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
