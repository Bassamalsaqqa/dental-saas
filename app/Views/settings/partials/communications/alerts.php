                <div id="sub-alerts" class="sub-tab-panel hidden">
                    <div class="bg-white/90 backdrop-blur-xl rounded-2xl shadow-xl shadow-blue-500/10 border border-white/30 overflow-hidden">
                        <div class="bg-gradient-to-r from-slate-700 to-slate-900 p-8"><div class="flex items-center space-x-4"><div class="w-12 h-12 bg-white/20 rounded-xl flex items-center justify-center backdrop-blur-sm"><i class="fas fa-user-cog text-amber-400 text-xl"></i></div><div><h3 class="text-2xl font-bold text-white"><?= lang('UI.settingsPersonalAlerts') ?></h3><p class="text-slate-300"><?= lang('UI.settingsPersonalAlertsDesc') ?></p></div></div></div>
                        <div class="p-8">
                            <form action="<?= base_url('settings/notifications/update') ?>" method="POST" class="space-y-8">
                                <?= csrf_field() ?>
                                <div>
                                    <h4 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                                        <i class="fas fa-bell text-blue-500 mr-2"></i><?= lang('UI.settingsSystemAlerts') ?>
                                    </h4>
                                    <div class="space-y-4">
                                        <div class="flex items-center justify-between p-4 bg-gray-50 rounded-2xl border border-gray-100">
                                            <div>
                                                <p class="font-bold text-gray-800">Email Alerts</p>
                                                <p class="text-xs text-gray-500 mt-1">Receive updates via email.</p>
                                            </div>
                                            <input type="checkbox" name="preferences[email_notifications]" value="1" class="ui-toggle" <?= !empty($notification_settings['email_notifications']) ? 'checked' : '' ?>>
                                        </div>
                                        <div class="flex items-center justify-between p-4 bg-gray-50 rounded-2xl border border-gray-100">
                                            <div>
                                                <p class="font-bold text-gray-800">SMS Alerts</p>
                                                <p class="text-xs text-gray-500 mt-1">Receive short critical updates by SMS.</p>
                                            </div>
                                            <input type="checkbox" name="preferences[sms_notifications]" value="1" class="ui-toggle" <?= !empty($notification_settings['sms_notifications']) ? 'checked' : '' ?>>
                                        </div>
                                    </div>
                                </div>

                                <div>
                                    <h4 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                                        <i class="fas fa-tasks text-green-500 mr-2"></i>Operational Alerts
                                    </h4>
                                    <div class="space-y-4">
                                        <div class="flex items-center justify-between p-4 bg-gray-50 rounded-2xl border border-gray-100">
                                            <div>
                                                <p class="font-bold text-gray-800">New Appointments</p>
                                                <p class="text-xs text-gray-500 mt-1">Get notified when appointments are created.</p>
                                            </div>
                                            <input type="checkbox" name="preferences[appointment_created]" value="1" class="ui-toggle" <?= !empty($notification_settings['appointment_created']) ? 'checked' : '' ?>>
                                        </div>
                                        <div class="flex items-center justify-between p-4 bg-gray-50 rounded-2xl border border-gray-100">
                                            <div>
                                                <p class="font-bold text-gray-800">Low Stock Alerts</p>
                                                <p class="text-xs text-gray-500 mt-1">Get notified when inventory items are low.</p>
                                            </div>
                                            <input type="checkbox" name="preferences[low_stock_alerts]" value="1" class="ui-toggle" <?= !empty($notification_settings['low_stock_alerts']) ? 'checked' : '' ?>>
                                        </div>
                                    </div>
                                </div>
                                <div class="mt-8 flex justify-end"><button type="submit" class="px-8 py-3 bg-gradient-to-r from-amber-500 to-orange-600 text-white font-bold rounded-xl shadow-lg transition-all"><?= lang('UI.settingsSavePreferences') ?></button></div>
                            </form>
                        </div>
                    </div>
                </div>
