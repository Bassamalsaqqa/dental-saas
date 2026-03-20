                <div id="sub-whatsapp" class="sub-tab-panel hidden">
                    <?php
                    $decode = static fn(string $value): string => json_decode('"' . $value . '"') ?? '';
                    $isArabic = service('request')->getLocale() === 'ar';
                    $templateLabels = [
                        'appointment_reminder' => $isArabic ? $decode('\u062a\u0630\u0643\u064a\u0631 \u0628\u0627\u0644\u0645\u0648\u0639\u062f') : 'Appointment Reminder',
                        'appointment_confirmation' => $isArabic ? $decode('\u062a\u0623\u0643\u064a\u062f \u0627\u0644\u0645\u0648\u0639\u062f') : 'Appointment Confirmation',
                        'follow_up' => $isArabic ? $decode('\u0631\u0633\u0627\u0644\u0629 \u0645\u062a\u0627\u0628\u0639\u0629') : 'Follow-Up Message',
                        'payment_reminder' => $isArabic ? $decode('\u062a\u0630\u0643\u064a\u0631 \u0628\u0627\u0644\u062f\u0641\u0639') : 'Payment Reminder',
                    ];
                    $toggleCopy = $isArabic
                        ? [
                            'section' => $decode('\u0623\u062a\u0645\u062a\u0629 \u0648\u0627\u062a\u0633\u0627\u0628'),
                            'section_help' => $decode('\u0627\u062e\u062a\u0631 \u0623\u064a \u0623\u0646\u0648\u0627\u0639 \u0627\u0644\u0631\u0633\u0627\u0626\u0644 \u0627\u0644\u0645\u0631\u0636\u064a\u0629 \u0627\u0644\u062a\u064a \u064a\u0645\u0643\u0646 \u0625\u0631\u0633\u0627\u0644\u0647\u0627 \u062a\u0644\u0642\u0627\u0626\u064a\u064b\u0627 \u0644\u0647\u0630\u0647 \u0627\u0644\u0639\u064a\u0627\u062f\u0629.'),
                            'save' => $decode('\u062d\u0641\u0638 \u0623\u062a\u0645\u062a\u0629 \u0648\u0627\u062a\u0633\u0627\u0628'),
                            'phone_label' => $decode('\u0645\u0639\u0631\u0641 \u0631\u0642\u0645 \u0627\u0644\u0647\u0627\u062a\u0641'),
                            'phone_help' => $decode('\u064a\u0645\u0643\u0646 \u0627\u0644\u0639\u062b\u0648\u0631 \u0639\u0644\u064a\u0647 \u0641\u064a Meta Business Manager > WhatsApp > Phone Number ID.'),
                            'templates' => $decode('\u0627\u0644\u0642\u0648\u0627\u0644\u0628 \u0627\u0644\u0639\u0631\u0628\u064a\u0629'),
                            'reset' => $decode('\u0625\u0639\u0627\u062f\u0629 \u0627\u0644\u0627\u0641\u062a\u0631\u0627\u0636\u064a'),
                            'toggle_card_title' => $decode('\u0645\u062d\u0631\u0643\u0627\u062a \u0627\u0644\u0631\u0633\u0627\u0626\u0644'),
                            'toggle_card_help' => $decode('\u0623\u0648\u0642\u0641 \u0623\u064a \u062a\u062f\u0641\u0642 \u063a\u064a\u0631 \u0645\u0631\u063a\u0648\u0628 \u0641\u064a\u0647 \u0628\u062f\u0648\u0646 \u062a\u0639\u0637\u064a\u0644 \u0648\u0627\u062a\u0633\u0627\u0628 \u0628\u0627\u0644\u0643\u0627\u0645\u0644.'),
                            'appointment_reminder_label' => $decode('\u062a\u0630\u0643\u064a\u0631\u0627\u062a \u0627\u0644\u0645\u0648\u0627\u0639\u064a\u062f'),
                            'appointment_reminder_help' => $decode('\u0625\u0631\u0633\u0627\u0644 \u062a\u0630\u0643\u064a\u0631 \u0642\u0628\u0644 \u0627\u0644\u0645\u0648\u0639\u062f \u062d\u0633\u0628 \u0648\u0642\u062a \u0627\u0644\u062a\u0646\u0628\u064a\u0647 \u0627\u0644\u0645\u0636\u0628\u0648\u0637.'),
                            'appointment_confirmation_label' => $decode('\u062a\u0623\u0643\u064a\u062f\u0627\u062a \u0627\u0644\u0645\u0648\u0627\u0639\u064a\u062f'),
                            'appointment_confirmation_help' => $decode('\u0625\u0631\u0633\u0627\u0644 \u0631\u0633\u0627\u0644\u0629 \u062a\u0623\u0643\u064a\u062f \u0639\u0646\u062f \u0625\u0646\u0634\u0627\u0621 \u0627\u0644\u0645\u0648\u0639\u062f \u0623\u0648 \u062a\u062d\u062f\u064a\u062b\u0647.'),
                            'follow_up_label' => $decode('\u0631\u0633\u0627\u0626\u0644 \u0627\u0644\u0645\u062a\u0627\u0628\u0639\u0629'),
                            'follow_up_help' => $decode('\u0625\u0631\u0633\u0627\u0644 \u0631\u0633\u0627\u0644\u0629 \u0645\u062a\u0627\u0628\u0639\u0629 \u0639\u0646\u062f \u0627\u0643\u062a\u0645\u0627\u0644 \u0627\u0644\u0641\u062d\u0635.'),
                            'payment_reminder_label' => $decode('\u062a\u0630\u0643\u064a\u0631\u0627\u062a \u0627\u0644\u062f\u0641\u0639'),
                            'payment_reminder_help' => $decode('\u0625\u0631\u0633\u0627\u0644 \u062a\u0630\u0643\u064a\u0631 \u0639\u0646\u062f \u0648\u062c\u0648\u062f \u0631\u0635\u064a\u062f \u0645\u0633\u062a\u062d\u0642 \u0639\u0644\u0649 \u0627\u0644\u0641\u0627\u062a\u0648\u0631\u0629.'),
                        ]
                        : [
                            'section' => 'WhatsApp Automations',
                            'section_help' => 'Choose which patient-facing WhatsApp flows are allowed to run automatically for this clinic.',
                            'save' => 'Save WhatsApp Automations',
                            'phone_label' => 'Phone Number ID',
                            'phone_help' => 'Found in Meta Business Manager > WhatsApp > Phone Number ID.',
                            'templates' => 'Arabic Templates',
                            'reset' => 'RESET',
                            'toggle_card_title' => 'Message Flows',
                            'toggle_card_help' => 'Turn individual workflow types on or off without disabling WhatsApp for the whole clinic.',
                            'appointment_reminder_label' => 'Appointment Reminders',
                            'appointment_reminder_help' => 'Send reminders before appointments based on the configured lead time.',
                            'appointment_confirmation_label' => 'Appointment Confirmations',
                            'appointment_confirmation_help' => 'Send a confirmation when an appointment is created or updated.',
                            'follow_up_label' => 'Examination Follow-Ups',
                            'follow_up_help' => 'Send a follow-up message when an examination is completed.',
                            'payment_reminder_label' => 'Payment Reminders',
                            'payment_reminder_help' => 'Send a reminder when an invoice still has an outstanding balance.',
                        ];
                    $triggerToggles = is_array($wa_policy['trigger_toggles_json'] ?? null) ? $wa_policy['trigger_toggles_json'] : [];
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
                                <label class="block text-sm font-semibold text-gray-700 mb-2"><?= esc($toggleCopy['phone_label']) ?></label>
                                <input type="text" id="wa_phone_number_id" value="<?= esc($wa_config['phone_number_id'] ?? '') ?>" dir="ltr" style="text-align: left;" class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl font-mono" placeholder="1234567890">
                                <p class="text-xs text-gray-500 mt-1"><?= esc($toggleCopy['phone_help']) ?></p>
                            </div>

                            <div class="pt-6 border-t border-gray-100">
                                <h4 class="font-bold text-gray-900 mb-4 flex justify-between items-center">
                                    <span><?= esc($toggleCopy['templates']) ?></span>
                                    <button onclick="resetTemplatesToDefault()" class="text-[10px] text-blue-600 font-bold hover:underline"><?= esc($toggleCopy['reset']) ?></button>
                                </h4>
                                <div class="grid grid-cols-1 gap-4" dir="<?= $isArabic ? 'rtl' : 'ltr' ?>">
                                    <?php foreach ($templateLabels as $key => $label): ?>
                                    <div class="space-y-1">
                                        <label class="block text-xs font-bold text-gray-600"><?= esc($label) ?></label>
                                        <textarea id="tmpl_<?= esc($key) ?>" rows="2" class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl transition-all <?= $isArabic ? 'text-right' : 'text-left' ?>"><?= esc($wa_config['message_templates'][$key] ?? '') ?></textarea>
                                    </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>

                            <form action="<?= base_url('settings/updateWhatsAppAutomationSettings') ?>" method="POST" class="pt-6 border-t border-gray-100 space-y-4">
                                <?= csrf_field() ?>
                                <div>
                                    <h4 class="font-bold text-gray-900"><?= esc($toggleCopy['section']) ?></h4>
                                    <p class="mt-1 text-xs text-gray-500"><?= esc($toggleCopy['section_help']) ?></p>
                                </div>

                                <div class="rounded-2xl border border-emerald-100 bg-emerald-50/60 p-5">
                                    <div class="mb-4">
                                        <p class="text-sm font-semibold text-slate-900"><?= esc($toggleCopy['toggle_card_title']) ?></p>
                                        <p class="mt-1 text-xs text-slate-500"><?= esc($toggleCopy['toggle_card_help']) ?></p>
                                    </div>

                                    <div class="space-y-4">
                                        <div class="flex items-center justify-between rounded-2xl border border-white/70 bg-white px-4 py-4">
                                            <div>
                                                <p class="font-bold text-gray-800"><?= esc($toggleCopy['appointment_reminder_label']) ?></p>
                                                <p class="text-xs text-gray-500 mt-1"><?= esc($toggleCopy['appointment_reminder_help']) ?></p>
                                            </div>
                                            <input type="checkbox" name="trigger_toggles[appointment_reminder]" value="1" class="ui-toggle" <?= !empty($triggerToggles['appointment_reminder']) ? 'checked' : '' ?>>
                                        </div>
                                        <div class="flex items-center justify-between rounded-2xl border border-white/70 bg-white px-4 py-4">
                                            <div>
                                                <p class="font-bold text-gray-800"><?= esc($toggleCopy['appointment_confirmation_label']) ?></p>
                                                <p class="text-xs text-gray-500 mt-1"><?= esc($toggleCopy['appointment_confirmation_help']) ?></p>
                                            </div>
                                            <input type="checkbox" name="trigger_toggles[appointment_confirmation]" value="1" class="ui-toggle" <?= !empty($triggerToggles['appointment_confirmation']) ? 'checked' : '' ?>>
                                        </div>
                                        <div class="flex items-center justify-between rounded-2xl border border-white/70 bg-white px-4 py-4">
                                            <div>
                                                <p class="font-bold text-gray-800"><?= esc($toggleCopy['follow_up_label']) ?></p>
                                                <p class="text-xs text-gray-500 mt-1"><?= esc($toggleCopy['follow_up_help']) ?></p>
                                            </div>
                                            <input type="checkbox" name="trigger_toggles[follow_up]" value="1" class="ui-toggle" <?= !empty($triggerToggles['follow_up']) ? 'checked' : '' ?>>
                                        </div>
                                        <div class="flex items-center justify-between rounded-2xl border border-white/70 bg-white px-4 py-4">
                                            <div>
                                                <p class="font-bold text-gray-800"><?= esc($toggleCopy['payment_reminder_label']) ?></p>
                                                <p class="text-xs text-gray-500 mt-1"><?= esc($toggleCopy['payment_reminder_help']) ?></p>
                                            </div>
                                            <input type="checkbox" name="trigger_toggles[payment_reminder]" value="1" class="ui-toggle" <?= !empty($triggerToggles['payment_reminder']) ? 'checked' : '' ?>>
                                        </div>
                                    </div>
                                </div>

                                <div class="flex justify-end">
                                    <button type="submit" onclick="setButtonLoading(this, true)" class="px-6 py-3 bg-gradient-to-r from-emerald-500 to-teal-600 text-white font-bold rounded-xl shadow-lg transition-all">
                                        <i class="fas fa-toggle-on mr-2"></i><?= esc($toggleCopy['save']) ?>
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
