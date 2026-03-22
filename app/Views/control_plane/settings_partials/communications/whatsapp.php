<?php
$waGlobalTemplates = is_array($wa_global_templates ?? null) ? $wa_global_templates : [];
$waGlobalTriggerTemplates = is_array($waGlobalTemplates['trigger_templates'] ?? null) ? $waGlobalTemplates['trigger_templates'] : [];
$waGlobalFallback = is_array($waGlobalTemplates['fallback_template'] ?? null) ? $waGlobalTemplates['fallback_template'] : [];
$waGlobalTriggerToggles = is_array($waGlobalTemplates['trigger_toggles'] ?? null) ? $waGlobalTemplates['trigger_toggles'] : [];
$waTriggerCatalog = is_array($wa_trigger_catalog ?? null) ? $wa_trigger_catalog : [];
?>

<div id="comms-panel-whatsapp" data-section-title="WhatsApp Configuration" class="comms-panel hidden space-y-6">
    <div id="section-whatsapp-live-operations" data-section-title="Live Operations" class="p-4 bg-emerald-50 border border-emerald-200 rounded-2xl flex items-center justify-between gap-4">
        <div>
            <h4 class="font-bold text-emerald-800 text-sm">WhatsApp Live Operations</h4>
            <p class="text-xs text-emerald-600 mt-0.5">Use the command center for delivery status, webhook flow, live testing, and operator audit history.</p>
        </div>
        <a href="<?= base_url('controlplane/whatsapp') ?>" class="control-settings-submit" style="background:linear-gradient(135deg,#10b981,#059669); box-shadow:0 14px 32px rgba(16,185,129,0.20);">
            <i class="fas fa-external-link-alt mr-1.5"></i>Open Dashboard
        </a>
    </div>

    <div id="section-whatsapp-global-templates" data-section-title="Global Arabic Templates" class="pt-2 border-t border-slate-100">
        <div class="flex items-start justify-between gap-4 mb-4">
            <div>
                <h4 class="text-sm font-bold text-slate-800">Global Arabic Templates</h4>
                <p class="text-xs text-slate-500 mt-1">One platform WhatsApp number serves every clinic. Templates live here once, in Arabic, then clinic-specific values like clinic name and phone are merged at send time.</p>
            </div>
            <span class="inline-flex items-center rounded-full bg-slate-100 px-3 py-1 text-[10px] font-black uppercase tracking-[0.16em] text-slate-600">Shared Across All Clinics</span>
        </div>

        <form action="<?= base_url('controlplane/settings/whatsapp-templates') ?>" method="post" class="space-y-5" data-settings-form="true" data-saving-label="Saving WhatsApp templates...">
            <?= csrf_field() ?>

            <div class="rounded-2xl border border-slate-200 bg-slate-50/70 p-5">
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 items-start">
                    <div>
                        <label class="block text-xs font-bold text-slate-600 uppercase tracking-[0.12em]">Fallback template</label>
                        <input type="text" name="fallback_template.name" value="<?= esc(($waGlobalFallback['name'] ?? '') !== '' ? ($waGlobalFallback['name'] ?? '') : ($waGlobalFallback['suggested_name'] ?? 'general_reengagement_ar_v1')) ?>" class="control-settings-field mt-2" placeholder="general_reengagement_ar_v1">
                        <p class="text-xs text-slate-500 mt-2">Starter value is pre-filled so you can see the structure immediately. Replace it if your approved Meta template name differs.</p>
                    </div>
                    <div class="rounded-2xl border border-dashed border-slate-200 bg-white px-4 py-4">
                        <p class="text-[11px] font-black uppercase tracking-[0.14em] text-slate-500">Language policy</p>
                        <p class="mt-2 text-sm font-semibold text-slate-900">Arabic only</p>
                        <p class="mt-1 text-xs text-slate-500">The system forces template language to <code class="font-mono text-[11px]">ar</code>. Clinics can enable or disable triggers, but they do not own template content.</p>
                    </div>
                </div>
            </div>

            <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                <div class="flex items-start justify-between gap-4 mb-4">
                    <div>
                        <h5 class="text-sm font-bold text-slate-900">Platform trigger master switches</h5>
                        <p class="mt-1 text-xs text-slate-500">This is the platform-wide rollout layer. A trigger must be enabled here, allowed by the clinic plan, and enabled for the individual clinic before automated sends can run.</p>
                    </div>
                    <span class="inline-flex items-center rounded-full bg-indigo-50 px-3 py-1 text-[10px] font-black uppercase tracking-[0.16em] text-indigo-700">Master Layer</span>
                </div>

                <div class="grid grid-cols-1 xl:grid-cols-2 gap-4">
                    <?php foreach ($waTriggerCatalog as $triggerKey => $triggerMeta): ?>
                        <?php $platformEnabled = (int) ($waGlobalTriggerToggles[$triggerKey] ?? ($triggerMeta['default_enabled'] ?? 1)) === 1; ?>
                        <label class="flex items-center justify-between rounded-2xl border border-slate-200 bg-slate-50 px-4 py-4 gap-4">
                            <div>
                                <p class="font-bold text-slate-900"><?= esc($triggerMeta['label'] ?? $triggerKey) ?></p>
                                <p class="mt-1 text-xs text-slate-500"><?= esc($triggerMeta['description'] ?? '') ?></p>
                            </div>
                            <input type="checkbox" name="global_trigger_toggles[<?= esc($triggerKey) ?>]" value="1" class="ui-toggle" <?= $platformEnabled ? 'checked' : '' ?>>
                        </label>
                    <?php endforeach; ?>
                </div>
            </div>

            <?php foreach ($waTriggerCatalog as $triggerKey => $triggerMeta): ?>
                <?php
                $triggerConfig = is_array($waGlobalTriggerTemplates[$triggerKey] ?? null) ? $waGlobalTriggerTemplates[$triggerKey] : [];
                $bodyParamKeys = is_array($triggerConfig['body_param_keys'] ?? null) ? $triggerConfig['body_param_keys'] : [];
                $availableVars = is_array($triggerMeta['available_vars'] ?? null) ? $triggerMeta['available_vars'] : [];
                $platformEnabled = (int) ($waGlobalTriggerToggles[$triggerKey] ?? ($triggerMeta['default_enabled'] ?? 1)) === 1;
                $suggestedTemplateName = (string) ($triggerMeta['suggested_template_name'] ?? ($triggerKey . '_ar_v1'));
                $displayTemplateName = trim((string) ($triggerConfig['name'] ?? '')) !== '' ? (string) $triggerConfig['name'] : $suggestedTemplateName;
                ?>
                <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                    <div class="flex flex-col gap-3 lg:flex-row lg:items-start lg:justify-between">
                        <div>
                            <p class="font-bold text-slate-900"><?= esc($triggerMeta['label'] ?? $triggerKey) ?></p>
                            <p class="mt-1 text-xs text-slate-500"><?= esc($triggerMeta['description'] ?? '') ?></p>
                        </div>
                        <div class="flex flex-wrap gap-2">
                            <span class="inline-flex items-center rounded-full bg-amber-50 px-3 py-1 text-[10px] font-black uppercase tracking-[0.16em] text-amber-700">Template Required</span>
                            <span class="inline-flex items-center rounded-full px-3 py-1 text-[10px] font-black uppercase tracking-[0.16em] <?= $platformEnabled ? 'bg-emerald-50 text-emerald-700' : 'bg-rose-50 text-rose-700' ?>">
                                <?= $platformEnabled ? 'Platform Enabled' : 'Platform Disabled' ?>
                            </span>
                        </div>
                    </div>

                    <div class="mt-4 grid grid-cols-1 xl:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-slate-600 uppercase tracking-[0.12em]">Approved Meta template name</label>
                            <input type="text" name="trigger_templates.<?= esc($triggerKey) ?>.name" value="<?= esc($displayTemplateName) ?>" class="control-settings-field mt-2" placeholder="<?= esc($suggestedTemplateName) ?>">
                            <p class="text-xs text-slate-500 mt-2">Starter value is pre-filled to show the expected naming shape. Keep it only if it matches the approved Meta template exactly.</p>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-600 uppercase tracking-[0.12em]">Header variable key</label>
                            <input type="text" name="trigger_templates.<?= esc($triggerKey) ?>.header_param_key" value="<?= esc($triggerConfig['header_param_key'] ?? '') ?>" class="control-settings-field mt-2" placeholder="Optional">
                        </div>
                        <div class="xl:col-span-2">
                            <label class="block text-xs font-bold text-slate-600 uppercase tracking-[0.12em]">Body variable keys</label>
                            <input type="text" name="trigger_templates.<?= esc($triggerKey) ?>.body_param_keys" value="<?= esc(implode(', ', $bodyParamKeys)) ?>" class="control-settings-field mt-2" placeholder="<?= esc(implode(', ', $triggerMeta['body_param_keys'] ?? [])) ?>">
                            <p class="text-xs text-slate-500 mt-2">Comma-separated. Keep the exact placeholder order approved in Meta.</p>
                        </div>
                    </div>

                    <div class="mt-4 grid grid-cols-1 xl:grid-cols-3 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-slate-600 uppercase tracking-[0.12em]">Button type</label>
                            <select name="trigger_templates.<?= esc($triggerKey) ?>.button_type" class="control-settings-field mt-2">
                                <option value="" <?= ($triggerConfig['button_type'] ?? '') === '' ? 'selected' : '' ?>>No button binding</option>
                                <option value="quick_reply" <?= ($triggerConfig['button_type'] ?? '') === 'quick_reply' ? 'selected' : '' ?>>Quick reply</option>
                                <option value="url" <?= ($triggerConfig['button_type'] ?? '') === 'url' ? 'selected' : '' ?>>URL</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-600 uppercase tracking-[0.12em]">Button index</label>
                            <input type="text" name="trigger_templates.<?= esc($triggerKey) ?>.button_index" value="<?= esc($triggerConfig['button_index'] ?? '0') ?>" class="control-settings-field mt-2" placeholder="0">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-600 uppercase tracking-[0.12em]">Button variable key</label>
                            <input type="text" name="trigger_templates.<?= esc($triggerKey) ?>.button_param_key" value="<?= esc($triggerConfig['button_param_key'] ?? '') ?>" class="control-settings-field mt-2" placeholder="Optional">
                        </div>
                    </div>

                    <?php if ($availableVars !== []): ?>
                        <div class="mt-4 rounded-2xl border border-dashed border-slate-200 bg-slate-50 px-4 py-3">
                            <p class="text-[11px] font-black uppercase tracking-[0.14em] text-slate-500">Available clinic and workflow variables</p>
                            <div class="mt-2 flex flex-wrap gap-2">
                                <?php foreach ($availableVars as $varKey => $varHelp): ?>
                                    <span class="inline-flex items-center rounded-full border border-slate-200 bg-white px-3 py-1 text-xs text-slate-600">
                                        <span class="font-black text-slate-800 mr-1"><?= esc($varKey) ?></span><?= esc($varHelp) ?>
                                    </span>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>

            <div class="flex justify-end">
                <button type="submit" class="control-settings-submit">
                    <i class="fas fa-save mr-2"></i>Save Global Templates
                </button>
            </div>
        </form>
    </div>

    <div id="section-whatsapp-api-credentials" data-section-title="API Credentials" class="pt-2 border-t border-slate-100">
        <h4 class="text-sm font-bold text-slate-800 mb-3">API Credentials</h4>
        <div class="space-y-4">
            <?php foreach (($wa_secrets ?? []) as $s): ?>
                <div class="p-4 border border-slate-200 rounded-2xl bg-white">
                    <div class="flex items-center justify-between mb-3">
                        <code class="text-xs font-black text-blue-600"><?= esc($s['secret_key']) ?></code>
                        <span class="text-[10px] font-black <?= !empty($s['missing']) ? 'text-rose-600' : 'text-emerald-600' ?>"><?= !empty($s['missing']) ? 'MISSING' : 'READY' ?></span>
                    </div>
                    <form action="<?= base_url('controlplane/whatsapp/rotate-secret') ?>" method="post" class="grid grid-cols-1 md:grid-cols-3 gap-3" data-settings-form="true" data-saving-label="Rotating key...">
                        <?= csrf_field() ?>
                        <input type="hidden" name="secret_key" value="<?= esc($s['secret_key']) ?>">
                        <div class="relative md:col-span-1">
                            <input type="password" name="new_value" required class="control-settings-field pr-10 text-sm font-mono" placeholder="Paste new secret value">
                            <button type="button" onclick="toggleSecretVis(this)" class="absolute right-3 top-2.5 text-slate-400"><i class="fas fa-eye text-sm"></i></button>
                        </div>
                        <input type="text" name="rotation_note" class="control-settings-field text-sm md:col-span-1" placeholder="Rotation note">
                        <button type="submit" class="control-settings-submit md:col-span-1">Rotate Key</button>
                    </form>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <div id="section-whatsapp-rate-limits" data-section-title="Tenant Rate Limits" class="pt-2 border-t border-slate-100">
        <div class="flex items-start justify-between gap-4 mb-4">
            <div>
                <h4 class="text-sm font-bold text-slate-800">Clinic rollout management</h4>
                <p class="text-xs text-slate-500 mt-1">Clinic-specific rollout controls now live inside each clinic page in control plane. Open a clinic to manage its trigger toggles, reminder lead time, pause state, and rate limits together.</p>
            </div>
            <span class="inline-flex items-center rounded-full bg-slate-100 px-3 py-1 text-[10px] font-black uppercase tracking-[0.16em] text-slate-600">Moved To Clinic Pages</span>
        </div>

        <div class="grid grid-cols-1 xl:grid-cols-2 gap-4">
            <?php foreach (($wa_policies ?? []) as $pol): ?>
                <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                    <div class="flex items-start justify-between gap-4">
                        <div>
                            <p class="text-base font-bold text-slate-900"><?= esc($pol['clinic_name']) ?></p>
                            <p class="mt-1 text-xs text-slate-500">Open the clinic detail page to manage clinic-specific WhatsApp rollout, rate limits, and emergency pause from one place.</p>
                        </div>
                        <span class="inline-flex items-center rounded-full px-3 py-1 text-[10px] font-black uppercase tracking-[0.16em] <?= !empty($pol['suspended']) ? 'bg-rose-50 text-rose-700' : 'bg-emerald-50 text-emerald-700' ?>">
                            <?= !empty($pol['suspended']) ? 'Paused' : 'Active' ?>
                        </span>
                    </div>
                    <div class="mt-4 flex items-center justify-between text-xs text-slate-500">
                        <span>Daily: <?= (int) $pol['daily_limit'] ?> • Monthly: <?= (int) $pol['monthly_limit'] ?> • Per hour: <?= (int) $pol['per_hour_limit'] ?></span>
                        <a href="<?= base_url('controlplane/clinics/' . (int) $pol['clinic_id']) ?>" class="inline-flex items-center rounded-xl border border-slate-200 bg-slate-50 px-4 py-2 text-sm font-black text-slate-800 hover:border-slate-300 hover:bg-slate-100">
                            Open Clinic
                        </a>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>
