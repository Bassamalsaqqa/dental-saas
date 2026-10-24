                <div id="sub-sms" class="sub-tab-panel hidden">
                    <div class="v3-surface v3-radius border v3-border overflow-hidden">
                        <div class="p-4 flex justify-between items-center v3-form-section-header"><div class="flex items-center space-x-4"><div class="w-12 h-12 v3-surface v3-radius flex items-center justify-center"><i class="fas fa-sms v3-text-warning text-xl"></i></div><div><h3 class="text-2xl font-bold v3-text-on-primary"><?= lang('UI.settingsSmsGateway') ?></h3><p class="v3-text-muted"><?= lang('UI.settingsSmsGatewayDesc') ?></p></div></div><button onclick="saveSmsConfig(event)" class="px-6 py-2.5 v3-surface v3-text-warning font-bold v3-radius transition-all v3-btn v3-btn--secondary"><i class="fas fa-save mr-2"></i><?= lang('UI.settingsSaveGateway') ?></button></div>
                        <div class="p-4 space-y-6">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <label class="block text-sm font-semibold v3-text-ink mb-2"><?= lang('TenantMisc.smsProvider') ?></label>
                                    <select id="sms_provider" class="w-full px-4 py-3 border-2 v3-border v3-radius v3-select">
                                        <option value=""><?= lang('TenantMisc.selectProvider') ?></option>
                                        <option value="unifonic">Unifonic</option>
                                        <option value="twilio">Twilio</option>
                                        <option value="msegat">Msegat</option>
                                        <option value="custom"><?= lang('TenantMisc.customApi') ?></option>
                                    </select>
                                    <p class="text-xs v3-text-muted mt-1"><?= lang('TenantMisc.smsProviderHelp') ?></p>
                                </div>
                                <div>
                                    <label class="block text-sm font-semibold v3-text-ink mb-2"><?= lang('TenantMisc.apiKeyToken') ?></label>
                                    <input type="password" id="sms_api_key" class="w-full px-4 py-3 border-2 v3-border v3-radius v3-input" placeholder="********" autocomplete="new-password">
                                    <p class="text-xs v3-text-muted mt-1"><?= lang('TenantMisc.leaveBlankKeepKey') ?></p>
                                </div>
                                <div>
                                    <label class="block text-sm font-semibold v3-text-ink mb-2"><?= lang('TenantMisc.senderIdFrom') ?></label>
                                    <input type="text" id="sms_sender_id" class="w-full px-4 py-3 border-2 v3-border v3-radius v3-input" placeholder="ClinicName">
                                    <p class="text-xs v3-text-muted mt-1"><?= lang('TenantMisc.senderIdHelp') ?></p>
                                </div>
                                <div id="sms_base_url_wrap">
                                    <label class="block text-sm font-semibold v3-text-ink mb-2"><?= lang('TenantMisc.baseApiUrl') ?></label>
                                    <input type="url" id="sms_base_url" class="w-full px-4 py-3 border-2 v3-border v3-radius v3-input" placeholder="https://api.provider.com/v1">
                                    <p class="text-xs v3-text-muted mt-1"><?= lang('TenantMisc.baseApiUrlHelp') ?></p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
