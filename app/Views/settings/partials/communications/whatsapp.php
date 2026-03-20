                <div id="sub-whatsapp" class="sub-tab-panel hidden">
                    <?php
                    $decode = static fn(string $value): string => json_decode('"' . $value . '"') ?? '';
                    $templateLabels = [
                        'appointment_reminder' => $decode('\\u062a\\u0630\\u0643\\u064a\\u0631 \\u0628\\u0627\\u0644\\u0645\\u0648\\u0639\\u062f'),
                        'appointment_confirmation' => $decode('\\u062a\\u0623\\u0643\\u064a\\u062f \\u0627\\u0644\\u0645\\u0648\\u0639\\u062f'),
                        'follow_up' => $decode('\\u0631\\u0633\\u0627\\u0644\\u0629 \\u0645\\u062a\\u0627\\u0628\\u0639\\u0629'),
                        'payment_reminder' => $decode('\\u062a\\u0630\\u0643\\u064a\\u0631 \\u0628\\u0627\\u0644\\u062f\\u0641\\u0639'),
                    ];
                    ?>
                    <div class="bg-white/90 backdrop-blur-xl rounded-2xl shadow-xl shadow-blue-500/10 border border-white/30 overflow-hidden">
                        <div class="bg-gradient-to-r from-slate-700 to-slate-900 p-8 flex justify-between items-center">
                            <div class="flex items-center space-x-4">
                                <div class="w-12 h-12 bg-white/20 rounded-xl flex items-center justify-center backdrop-blur-sm">
                                    <i class="fab fa-whatsapp text-emerald-400 text-xl"></i>
                                </div>
                                <div>
                                    <h3 class="text-2xl font-bold text-white"><?= lang('UI.settingsWhatsApp') ?></h3>
                                    <p class="text-slate-300"><?= lang('UI.settingsWhatsAppMetaDesc') ?></p>
                                </div>
                            </div>
                            <div class="flex space-x-2">
                                <button onclick="testWhatsAppChannel()" class="px-4 py-2 bg-white/20 text-white font-bold rounded-xl hover:bg-white/30 transition-all"><?= lang('UI.settingsTest') ?></button>
                                <button onclick="saveWhatsAppConfig(event)" class="px-6 py-2 bg-white text-green-700 font-bold rounded-xl shadow-lg transition-all"><i class="fas fa-save mr-2"></i><?= lang('UI.settingsSave') ?></button>
                            </div>
                        </div>
                        <div class="p-8 space-y-8">
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-2">Phone Number ID</label>
                                <input type="text" id="wa_phone_number_id" value="<?= esc($wa_config['phone_number_id'] ?? '') ?>" dir="ltr" style="text-align: left;" class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl font-mono" placeholder="1234567890">
                                <p class="text-xs text-gray-500 mt-1">Found in Meta Business Manager > WhatsApp > Phone Number ID.</p>
                            </div>
                            <div class="pt-6 border-t border-gray-100">
                                <h4 class="font-bold text-gray-900 mb-4 flex justify-between items-center">
                                    <span>Arabic Templates</span>
                                    <button onclick="resetTemplatesToDefault()" class="text-[10px] text-blue-600 font-bold hover:underline">RESET</button>
                                </h4>
                                <div class="grid grid-cols-1 gap-4" dir="rtl">
                                    <?php foreach ($templateLabels as $key => $label): ?>
                                    <div class="space-y-1">
                                        <label class="block text-xs font-bold text-gray-600"><?= esc($label) ?></label>
                                        <textarea id="tmpl_<?= esc($key) ?>" rows="2" class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl transition-all text-right"><?= esc($wa_config['message_templates'][$key] ?? '') ?></textarea>
                                    </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
