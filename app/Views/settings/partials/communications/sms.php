                <div id="sub-sms" class="sub-tab-panel hidden">
                    <div class="bg-white/90 backdrop-blur-xl rounded-2xl shadow-xl shadow-blue-500/10 border border-white/30 overflow-hidden">
                        <div class="bg-gradient-to-r from-slate-700 to-slate-900 p-8 flex justify-between items-center"><div class="flex items-center space-x-4"><div class="w-12 h-12 bg-white/20 rounded-xl flex items-center justify-center backdrop-blur-sm"><i class="fas fa-sms text-orange-400 text-xl"></i></div><div><h3 class="text-2xl font-bold text-white"><?= lang('UI.settingsSmsGateway') ?></h3><p class="text-slate-300"><?= lang('UI.settingsSmsGatewayDesc') ?></p></div></div><button onclick="saveSmsConfig(event)" class="px-6 py-2.5 bg-white text-orange-600 font-bold rounded-xl shadow-lg transition-all"><i class="fas fa-save mr-2"></i><?= lang('UI.settingsSaveGateway') ?></button></div>
                        <div class="p-8 space-y-6">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <label class="block text-sm font-semibold text-gray-700 mb-2"><?= lang('TenantMisc.smsProvider') ?></label>
                                    <select id="sms_provider" class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl">
                                        <option value=""><?= lang('TenantMisc.selectProvider') ?></option>
                                        <option value="unifonic">Unifonic</option>
                                        <option value="twilio">Twilio</option>
                                        <option value="msegat">Msegat</option>
                                        <option value="custom"><?= lang('TenantMisc.customApi') ?></option>
                                    </select>
                                    <p class="text-xs text-gray-500 mt-1"><?= lang('TenantMisc.smsProviderHelp') ?></p>
                                </div>
                                <div>
                                    <label class="block text-sm font-semibold text-gray-700 mb-2"><?= lang('TenantMisc.apiKeyToken') ?></label>
                                    <input type="password" id="sms_api_key" class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl" placeholder="********" autocomplete="new-password">
                                    <p class="text-xs text-gray-500 mt-1"><?= lang('TenantMisc.leaveBlankKeepKey') ?></p>
                                </div>
                                <div>
                                    <label class="block text-sm font-semibold text-gray-700 mb-2"><?= lang('TenantMisc.senderIdFrom') ?></label>
                                    <input type="text" id="sms_sender_id" class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl" placeholder="ClinicName">
                                    <p class="text-xs text-gray-500 mt-1"><?= lang('TenantMisc.senderIdHelp') ?></p>
                                </div>
                                <div id="sms_base_url_wrap">
                                    <label class="block text-sm font-semibold text-gray-700 mb-2"><?= lang('TenantMisc.baseApiUrl') ?></label>
                                    <input type="url" id="sms_base_url" class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl" placeholder="https://api.provider.com/v1">
                                    <p class="text-xs text-gray-500 mt-1"><?= lang('TenantMisc.baseApiUrlHelp') ?></p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
