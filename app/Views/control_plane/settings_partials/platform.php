                <div id="tab-platform" class="settings-content">
                    <div class="bg-white rounded-2xl shadow-xl border border-slate-200 overflow-hidden">
                        <div class="bg-gradient-to-r from-slate-700 to-gray-800 p-8">
                            <div class="flex items-center space-x-4">
                                <div class="w-12 h-12 bg-white/10 rounded-xl flex items-center justify-center backdrop-blur-sm">
                                    <i class="fas fa-id-badge text-blue-400 text-xl"></i>
                                </div>
                                <div>
                                    <h3 class="text-xl font-bold text-white">Platform Profile</h3>
                                    <p class="text-slate-300 text-sm">Support identity used across control plane surfaces</p>
                                </div>
                            </div>
                        </div>
                        <div class="p-8">
                            <form action="<?= base_url('controlplane/settings/platform') ?>" method="POST" class="space-y-6">
                                <?= csrf_field() ?>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <div class="md:col-span-2">
                                        <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Platform Name</label>
                                        <input type="text" name="platform_name" value="<?= esc($settings['platform_name'] ?? '') ?>" class="w-full px-4 py-3 bg-white border border-slate-200 rounded-xl text-slate-700 font-medium focus:border-slate-500 focus:ring-4 focus:ring-slate-200/60 transition-all" required>
                                        <p class="text-xs text-slate-400 mt-1">Shown in the control plane header and support email footers.</p>
                                    </div>
                                    <div>
                                        <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Support Email</label>
                                        <input type="email" name="platform_support_email" value="<?= esc($settings['platform_support_email'] ?? '') ?>" placeholder="support@yourdomain.com" class="w-full px-4 py-3 bg-white border border-slate-200 rounded-xl text-slate-700 font-medium focus:border-slate-500 focus:ring-4 focus:ring-slate-200/60 transition-all">
                                        <p class="text-xs text-slate-400 mt-1">Used as the reply-to address in system-generated emails to clinic admins.</p>
                                    </div>
                                    <div>
                                        <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Support Phone</label>
                                        <input type="text" name="platform_support_phone" value="<?= esc($settings['platform_support_phone'] ?? '') ?>" placeholder="+970 000 000 000" class="w-full px-4 py-3 bg-white border border-slate-200 rounded-xl text-slate-700 font-medium focus:border-slate-500 focus:ring-4 focus:ring-slate-200/60 transition-all">
                                        <p class="text-xs text-slate-400 mt-1">Displayed on the platform support page accessible from the clinic dashboard.</p>
                                    </div>
                                    <div>
                                        <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">WhatsApp (Primary)</label>
                                        <input type="text" name="platform_support_whatsapp_primary" value="<?= esc($settings['platform_support_whatsapp_primary'] ?? '') ?>" placeholder="+970 000 000 000" class="w-full px-4 py-3 bg-white border border-slate-200 rounded-xl text-slate-700 font-medium focus:border-slate-500 focus:ring-4 focus:ring-slate-200/60 transition-all">
                                        <p class="text-xs text-slate-400 mt-1">Primary WhatsApp number for support requests from clinics.</p>
                                    </div>
                                    <div>
                                        <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">WhatsApp (Secondary)</label>
                                        <input type="text" name="platform_support_whatsapp_secondary" value="<?= esc($settings['platform_support_whatsapp_secondary'] ?? '') ?>" placeholder="+972 000 000 000" class="w-full px-4 py-3 bg-white border border-slate-200 rounded-xl text-slate-700 font-medium focus:border-slate-500 focus:ring-4 focus:ring-slate-200/60 transition-all">
                                        <p class="text-xs text-slate-400 mt-1">Fallback contact shown when the primary number is unreachable.</p>
                                    </div>
                                    <div class="md:col-span-2">
                                        <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Support Website</label>
                                        <input type="url" name="platform_support_website" value="<?= esc($settings['platform_support_website'] ?? '') ?>" placeholder="https://yourdomain.com" class="w-full px-4 py-3 bg-white border border-slate-200 rounded-xl text-slate-700 font-medium focus:border-slate-500 focus:ring-4 focus:ring-slate-200/60 transition-all">
                                        <p class="text-xs text-slate-400 mt-1">Link to your support portal or documentation site. Include https://.</p>
                                    </div>
                                </div>
                                <div class="flex justify-end">
                                    <button type="submit" class="px-6 py-3 bg-gradient-to-r from-slate-700 to-gray-800 text-white font-semibold rounded-xl shadow-lg hover:from-slate-800 hover:to-gray-900 transition-all">
                                        <i class="fas fa-save mr-2"></i>Save Profile
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
