<div id="sub-whatsapp" class="sub-tab-panel hidden">
    <?php
    $isArabic = service('request')->getLocale() === 'ar';
    $decode = static fn(string $value): string => json_decode('"' . $value . '"') ?? '';
    $waManaged = !empty($wa_config['managed_by_control_plane']);
    $triggerCatalog = is_array($wa_trigger_catalog ?? null) ? $wa_trigger_catalog : [];
    $triggerTemplates = is_array($wa_config['trigger_templates'] ?? null) ? $wa_config['trigger_templates'] : [];
    $triggerToggles = is_array($wa_policy['trigger_toggles_json'] ?? null) ? $wa_policy['trigger_toggles_json'] : [];
    ?>

    <div class="bg-white/90 backdrop-blur-xl rounded-2xl shadow-xl shadow-blue-500/10 border border-white/30 overflow-hidden">
        <div class="bg-gradient-to-r from-slate-700 to-slate-900 p-8">
            <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                <div class="flex items-center space-x-4">
                    <div class="w-12 h-12 bg-white/20 rounded-xl flex items-center justify-center backdrop-blur-sm">
                        <i class="fab fa-whatsapp text-emerald-400 text-xl"></i>
                    </div>
                    <div>
                        <h3 class="text-2xl font-bold text-white"><?= lang('UI.settingsWhatsApp') ?></h3>
                        <p class="text-slate-300"><?= lang('UI.settingsWhatsAppMetaDesc') ?></p>
                    </div>
                </div>
                <div class="flex gap-2">
                    <button type="button" onclick="testWhatsAppChannel()" class="px-4 py-2 bg-white/20 text-white font-bold rounded-xl hover:bg-white/30 transition-all"><?= lang('UI.settingsTest') ?></button>
                    <span class="px-4 py-2 bg-white/15 text-white font-bold rounded-xl border border-white/20">
                        <?= $isArabic ? $decode('\u0625\u062f\u0627\u0631\u0629 \u0627\u0644\u0642\u0648\u0627\u0644\u0628 \u0645\u0631\u0643\u0632\u064a\u0627') : 'Templates managed centrally' ?>
                    </span>
                </div>
            </div>
        </div>

        <div class="p-8 space-y-8">
            <div class="grid grid-cols-1 xl:grid-cols-3 gap-4">
                <div class="rounded-2xl border border-emerald-100 bg-emerald-50 px-5 py-4">
                    <p class="text-[11px] font-black uppercase tracking-widest text-emerald-600"><?= $isArabic ? $decode('\u0627\u0644\u0625\u0631\u0633\u0627\u0644') : 'Delivery model' ?></p>
                    <p class="mt-2 text-sm font-semibold text-slate-900"><?= $isArabic ? $decode('\u0631\u0642\u0645 \u0648\u0627\u062d\u062f \u0644\u0643\u0644 \u0627\u0644\u0639\u064a\u0627\u062f\u0627\u062a') : 'One sender for all clinics' ?></p>
                    <p class="mt-1 text-xs text-slate-500"><?= $isArabic ? $decode('\u062a\u0633\u062a\u062e\u062f\u0645 \u0627\u0644\u0645\u0646\u0635\u0629 \u0631\u0642\u0645 \u0648\u0627\u062a\u0633\u0627\u0628 \u0645\u0648\u062d\u062f\u060c \u0648\u064a\u062a\u0645 \u0625\u062f\u0631\u0627\u062c \u0628\u064a\u0627\u0646\u0627\u062a \u0639\u064a\u0627\u062f\u062a\u0643 \u062f\u0627\u062e\u0644 \u0627\u0644\u0642\u0648\u0627\u0644\u0628.') : 'The platform sends from one WhatsApp number and injects your clinic details into the approved templates.' ?></p>
                </div>
                <div class="rounded-2xl border border-blue-100 bg-blue-50 px-5 py-4">
                    <p class="text-[11px] font-black uppercase tracking-widest text-blue-600"><?= $isArabic ? $decode('\u0627\u0644\u0642\u0648\u0627\u0644\u0628') : 'Template ownership' ?></p>
                    <p class="mt-2 text-sm font-semibold text-slate-900"><?= $isArabic ? $decode('\u064a\u062f\u064a\u0631\u0647\u0627 \u0627\u0644\u0643\u0648\u0646\u062a\u0631\u0648\u0644 \u0628\u0644\u064a\u0646') : 'Managed by control plane' ?></p>
                    <p class="mt-1 text-xs text-slate-500"><?= $isArabic ? $decode('\u0627\u0644\u0642\u0648\u0627\u0644\u0628 \u0648\u0644\u063a\u062a\u0647\u0627 \u0627\u0644\u0639\u0631\u0628\u064a\u0629 \u064a\u062a\u0645 \u0625\u0639\u062f\u0627\u062f\u0647\u0627 \u0645\u0631\u0643\u0632\u064a\u064b\u0627. \u0627\u0644\u0639\u064a\u0627\u062f\u0629 \u062a\u062f\u064a\u0631 \u0627\u0644\u062a\u0634\u063a\u064a\u0644 \u0641\u0642\u0637.') : 'Arabic templates are configured centrally. This page controls whether each workflow is allowed to run for your clinic.' ?></p>
                </div>
                <div class="rounded-2xl border border-amber-100 bg-amber-50 px-5 py-4">
                    <p class="text-[11px] font-black uppercase tracking-widest text-amber-600"><?= $isArabic ? $decode('\u0627\u0644\u0645\u062a\u063a\u064a\u0631\u0627\u062a') : 'Clinic data used' ?></p>
                    <p class="mt-2 text-sm font-semibold text-slate-900"><?= $isArabic ? $decode('\u0627\u0633\u0645\u060c \u0647\u0627\u062a\u0641\u060c \u0639\u0646\u0648\u0627\u0646') : 'Name, phone, address' ?></p>
                    <p class="mt-1 text-xs text-slate-500"><?= $isArabic ? $decode('\u062d\u062f\u062b \u0628\u064a\u0627\u0646\u0627\u062a \u0627\u0644\u0639\u064a\u0627\u062f\u0629 \u0623\u062f\u0646\u0627\u0647 \u0644\u062a\u0638\u0647\u0631 \u0635\u062d\u064a\u062d\u0629 \u062f\u0627\u062e\u0644 \u0642\u0648\u0627\u0644\u0628 \u0648\u0627\u062a\u0633\u0627\u0628.') : 'Keep your clinic profile accurate so the shared templates show the right clinic identity.' ?></p>
                </div>
            </div>

            <div class="rounded-2xl border border-slate-200 bg-slate-50/70 p-5">
                <div class="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
                    <div>
                        <h4 class="font-bold text-slate-900"><?= $isArabic ? $decode('\u0627\u0644\u0628\u0646\u064a\u0629 \u0627\u0644\u062d\u0627\u0644\u064a\u0629') : 'Current platform setup' ?></h4>
                        <p class="mt-1 text-xs text-slate-500"><?= $isArabic ? $decode('\u0644\u0644\u0639\u0644\u0645 \u0641\u0642\u0637\u060c \u0645\u0639 \u0627\u062e\u062a\u0628\u0627\u0631 \u062c\u0627\u0647\u0632\u064a\u0629 \u0627\u0644\u0642\u0646\u0627\u0629 \u0645\u0646 \u0647\u0630\u0627 \u0627\u0644\u0633\u0637\u062d.') : 'Read-only operational details, plus a readiness test for this clinic.' ?></p>
                    </div>
                    <span class="inline-flex items-center rounded-full <?= $waManaged ? 'bg-emerald-100 text-emerald-700' : 'bg-rose-100 text-rose-700' ?> px-3 py-1 text-[10px] font-black uppercase tracking-[0.16em]">
                        <?= $waManaged ? ($isArabic ? $decode('\u0645\u062f\u0627\u0631 \u0645\u0631\u0643\u0632\u064a\u0627') : 'Control-plane managed') : ($isArabic ? $decode('\u063a\u064a\u0631 \u0645\u062d\u0633\u0648\u0645') : 'Needs review') ?>
                    </span>
                </div>

                <div class="mt-4 grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-600 uppercase tracking-[0.12em]"><?= $isArabic ? $decode('\u0645\u0639\u0631\u0641 \u0631\u0642\u0645 \u0627\u0644\u0647\u0627\u062a\u0641') : 'Phone Number ID' ?></label>
                        <input type="text" value="<?= esc($wa_config['phone_number_id'] ?? '') ?>" readonly class="mt-2 w-full rounded-xl border-2 border-gray-200 bg-white px-4 py-3 text-sm font-mono text-slate-700">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-600 uppercase tracking-[0.12em]"><?= $isArabic ? $decode('\u0642\u0627\u0644\u0628 \u0627\u0644\u0637\u0648\u0627\u0631\u0626') : 'Fallback template' ?></label>
                        <input type="text" value="<?= esc($wa_config['fallback_template']['name'] ?? '') ?>" readonly class="mt-2 w-full rounded-xl border-2 border-gray-200 bg-white px-4 py-3 text-sm font-semibold text-slate-700">
                    </div>
                </div>
            </div>

            <div class="pt-6 border-t border-gray-100">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <h4 class="font-bold text-gray-900"><?= $isArabic ? $decode('\u0627\u0644\u0642\u0648\u0627\u0644\u0628 \u0627\u0644\u0645\u0641\u0639\u0644\u0629 \u0644\u0645\u062d\u0631\u0643\u0627\u062a \u0627\u0644\u0639\u064a\u0627\u062f\u0629') : 'Active workflow templates' ?></h4>
                        <p class="mt-1 text-xs text-gray-500"><?= $isArabic ? $decode('\u0647\u0630\u0647 \u0642\u0648\u0627\u0644\u0628 \u0648\u0627\u062a\u0633\u0627\u0628 \u0627\u0644\u0645\u0634\u062a\u0631\u0643\u0629 \u0627\u0644\u062a\u064a \u0633\u062a\u0633\u062a\u062e\u062f\u0645 \u0644\u0639\u064a\u0627\u062f\u062a\u0643 \u0639\u0646\u062f \u062a\u0645\u0643\u064a\u0646 \u0627\u0644\u0645\u062d\u0631\u0643.') : 'These are the centrally managed Arabic templates that will be used when each workflow is enabled for your clinic.' ?></p>
                    </div>
                </div>

                <div class="mt-4 space-y-4">
                    <?php foreach ($triggerCatalog as $triggerKey => $triggerMeta): ?>
                        <?php
                        $triggerConfig = is_array($triggerTemplates[$triggerKey] ?? null) ? $triggerTemplates[$triggerKey] : [];
                        $bodyKeys = is_array($triggerConfig['body_param_keys'] ?? null) ? $triggerConfig['body_param_keys'] : [];
                        ?>
                        <div class="rounded-2xl border border-slate-200 bg-slate-50/80 p-5">
                            <div class="flex flex-col gap-3 lg:flex-row lg:items-start lg:justify-between">
                                <div>
                                    <p class="font-bold text-slate-900"><?= esc($triggerMeta['label'] ?? $triggerKey) ?></p>
                                    <p class="mt-1 text-xs text-slate-500"><?= esc($triggerMeta['description'] ?? '') ?></p>
                                </div>
                                <span class="inline-flex items-center rounded-full bg-slate-100 px-3 py-1 text-[10px] font-black uppercase tracking-[0.16em] text-slate-600"><?= esc($triggerConfig['name'] ?? 'Not configured') ?></span>
                            </div>

                            <div class="mt-4 grid grid-cols-1 md:grid-cols-3 gap-4 text-sm">
                                <div>
                                    <p class="text-[11px] font-black uppercase tracking-[0.12em] text-slate-500"><?= $isArabic ? $decode('\u0627\u0644\u0644\u063a\u0629') : 'Language' ?></p>
                                    <p class="mt-2 font-semibold text-slate-900"><?= esc($triggerConfig['language'] ?? 'ar') ?></p>
                                </div>
                                <div>
                                    <p class="text-[11px] font-black uppercase tracking-[0.12em] text-slate-500"><?= $isArabic ? $decode('\u0645\u062a\u063a\u064a\u0631 \u0627\u0644\u0631\u0623\u0633') : 'Header variable' ?></p>
                                    <p class="mt-2 font-semibold text-slate-900"><?= esc($triggerConfig['header_param_key'] ?? 'None') ?></p>
                                </div>
                                <div>
                                    <p class="text-[11px] font-black uppercase tracking-[0.12em] text-slate-500"><?= $isArabic ? $decode('\u0645\u062a\u063a\u064a\u0631\u0627\u062a \u0627\u0644\u062c\u0633\u0645') : 'Body variables' ?></p>
                                    <p class="mt-2 font-semibold text-slate-900"><?= esc($bodyKeys !== [] ? implode(', ', $bodyKeys) : 'None') ?></p>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <form action="<?= base_url('settings/updateWhatsAppAutomationSettings') ?>" method="POST" class="pt-6 border-t border-gray-100 space-y-4">
                <?= csrf_field() ?>
                <div>
                    <h4 class="font-bold text-gray-900"><?= $isArabic ? $decode('\u062a\u0634\u063a\u064a\u0644 \u0645\u062d\u0631\u0643\u0627\u062a \u0648\u0627\u062a\u0633\u0627\u0628') : 'WhatsApp workflow switches' ?></h4>
                    <p class="mt-1 text-xs text-gray-500"><?= $isArabic ? $decode('\u0645\u0646 \u0647\u0646\u0627 \u062a\u062d\u062f\u062f \u0623\u064a \u0645\u062d\u0631\u0643\u0627\u062a \u062a\u0633\u0645\u062d \u0644\u0647\u0627 \u0627\u0644\u0639\u064a\u0627\u062f\u0629 \u0628\u0627\u0644\u0639\u0645\u0644. \u0625\u064a\u0642\u0627\u0641 \u0645\u062d\u0631\u0643 \u0645\u0627 \u0644\u0627 \u064a\u062d\u0630\u0641 \u0627\u0644\u0642\u0627\u0644\u0628\u060c \u0628\u0644 \u064a\u0648\u0642\u0641 \u0625\u0631\u0633\u0627\u0644\u0647 \u0644\u0639\u064a\u0627\u062f\u062a\u0643.') : 'Choose exactly which WhatsApp workflows are allowed to run for your clinic. Turning a switch off stops that trigger for your clinic without changing the shared template.' ?></p>
                </div>

                <div class="rounded-2xl border border-emerald-100 bg-emerald-50/60 p-5">
                    <div class="space-y-4">
                        <?php foreach ($triggerCatalog as $triggerKey => $triggerMeta): ?>
                            <?php $triggerEnabled = (int) ($triggerToggles[$triggerKey] ?? ($triggerMeta['default_enabled'] ?? 1)) === 1; ?>
                            <div class="flex items-center justify-between rounded-2xl border border-white/70 bg-white px-4 py-4 gap-4">
                                <div>
                                    <p class="font-bold text-gray-800"><?= esc($triggerMeta['label'] ?? $triggerKey) ?></p>
                                    <p class="mt-1 text-xs text-gray-500"><?= esc($triggerMeta['description'] ?? '') ?></p>
                                </div>
                                <input type="checkbox" name="trigger_toggles[<?= esc($triggerKey) ?>]" value="1" class="ui-toggle" <?= $triggerEnabled ? 'checked' : '' ?>>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <div class="flex justify-end">
                    <button type="submit" onclick="setButtonLoading(this, true)" class="px-6 py-3 bg-gradient-to-r from-emerald-500 to-teal-600 text-white font-bold rounded-xl shadow-lg transition-all">
                        <i class="fas fa-toggle-on mr-2"></i><?= $isArabic ? $decode('\u062d\u0641\u0638 \u062a\u0634\u063a\u064a\u0644 \u0648\u0627\u062a\u0633\u0627\u0628') : 'Save WhatsApp workflow switches' ?>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
