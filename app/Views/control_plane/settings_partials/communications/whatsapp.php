
<?php
$waGlobalTemplates = is_array($wa_global_templates ?? null) ? $wa_global_templates : [];
$waGlobalTriggerTemplates = is_array($waGlobalTemplates['trigger_templates'] ?? null) ? $waGlobalTemplates['trigger_templates'] : [];
$waGlobalFallback = is_array($waGlobalTemplates['fallback_template'] ?? null) ? $waGlobalTemplates['fallback_template'] : [];
$waGlobalTriggerToggles = is_array($waGlobalTemplates['trigger_toggles'] ?? null) ? $waGlobalTemplates['trigger_toggles'] : [];
$waTriggerCatalog = is_array($wa_trigger_catalog ?? null) ? $wa_trigger_catalog : [];
$waTriggerReadiness = is_array($wa_trigger_readiness ?? null) ? $wa_trigger_readiness : [];
$waPolicies = is_array($wa_policies ?? null) ? $wa_policies : [];

$configuredCount = 0;
$enabledCount = 0;
$readyCount = 0;
foreach ($waTriggerCatalog as $triggerKey => $triggerMeta) {
    $triggerConfig = is_array($waGlobalTriggerTemplates[$triggerKey] ?? null) ? $waGlobalTriggerTemplates[$triggerKey] : [];
    if (trim((string) ($triggerConfig['name'] ?? '')) !== '') {
        $configuredCount++;
    }
    if ((int) ($waGlobalTriggerToggles[$triggerKey] ?? ($triggerMeta['default_enabled'] ?? 1)) === 1) {
        $enabledCount++;
    }
    if (!empty($waTriggerReadiness[$triggerKey]['ready'])) {
        $readyCount++;
    }
}
?>

<style>
    .wa-settings-segment {
        display: inline-flex;
        align-items: center;
        gap: .55rem;
        padding: .8rem 1rem;
        border-radius: 1rem;
        border: 1px solid rgba(148, 163, 184, .18);
        background: rgba(248, 250, 252, .88);
        color: #475569;
        font-size: .875rem;
        font-weight: 700;
        transition: all .2s ease;
        white-space: nowrap;
    }
    .wa-settings-segment:hover { background: #fff; color: #0f172a; border-color: rgba(59, 130, 246, .2); }
    .wa-settings-segment.is-active { background: linear-gradient(135deg, #0f766e, #059669); color: #fff; border-color: transparent; box-shadow: 0 18px 38px rgba(5, 150, 105, .16); }
    .wa-settings-pane.hidden { display: none !important; }
    .wa-template-card { border: 1px solid #e2e8f0; border-radius: 1.25rem; background: #fff; box-shadow: 0 14px 30px rgba(15, 23, 42, .06); overflow: hidden; }
    .wa-template-card-header { padding: 1.25rem 1.5rem; background: linear-gradient(135deg, rgba(15, 118, 110, .08), rgba(59, 130, 246, .05)); border-bottom: 1px solid #e2e8f0; }
    .wa-template-preview { min-height: 10rem; direction: rtl; text-align: right; line-height: 1.9; font-family: "Segoe UI", Tahoma, sans-serif; }
    .wa-stat-card { border: 1px solid #e2e8f0; border-radius: 1.25rem; background: #fff; padding: 1.25rem; box-shadow: 0 12px 28px rgba(15, 23, 42, .05); }
    .wa-template-nav-btn {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: .75rem;
        width: 100%;
        padding: .95rem 1rem;
        border-radius: 1rem;
        border: 1px solid #e2e8f0;
        background: #fff;
        text-align: left;
        transition: all .2s ease;
    }
    .wa-template-nav-btn:hover {
        background: #f8fafc;
        border-color: #cbd5e1;
    }
    .wa-template-nav-btn.is-active {
        background: #eff6ff;
        border-color: #bfdbfe;
        box-shadow: inset 0 0 0 1px #bfdbfe;
    }
    .wa-template-nav-btn.is-active .wa-template-nav-title {
        color: #1d4ed8;
    }
    .wa-template-editor-pane.hidden { display: none !important; }
</style>

<div id="tab-whatsapp-templates" data-section-title="WhatsApp Templates" class="settings-content hidden space-y-6">
    <div id="section-whatsapp-overview" data-section-title="WhatsApp Overview" class="space-y-6">
        <div class="rounded-[1.75rem] border border-emerald-200/70 bg-gradient-to-br from-emerald-50 via-white to-teal-50 p-6 shadow-sm">
            <div class="flex flex-col gap-6 xl:flex-row xl:items-start xl:justify-between">
                <div class="max-w-3xl">
                    <div class="flex items-center gap-3">
                        <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-emerald-600 text-white shadow-lg">
                            <i class="fab fa-whatsapp text-xl"></i>
                        </div>
                        <div>
                            <h4 class="text-xl font-black text-slate-950">WhatsApp Platform Settings</h4>
                            <p class="mt-1 text-sm text-slate-600">One shared WhatsApp sender, five shared Arabic templates, and clinic-level rollout control.</p>
                        </div>
                    </div>
                    <div class="mt-4 grid grid-cols-1 gap-3 md:grid-cols-3">
                        <div class="wa-stat-card"><p class="text-[11px] font-black uppercase tracking-[0.16em] text-slate-500">Configured Templates</p><p class="mt-2 text-3xl font-black text-slate-950"><?= (int) $configuredCount ?>/<?= count($waTriggerCatalog) ?></p><p class="mt-2 text-xs text-slate-500">Approved Meta template names saved in control plane.</p></div>
                        <div class="wa-stat-card"><p class="text-[11px] font-black uppercase tracking-[0.16em] text-slate-500">Platform Enabled</p><p class="mt-2 text-3xl font-black text-slate-950"><?= (int) $enabledCount ?>/<?= count($waTriggerCatalog) ?></p><p class="mt-2 text-xs text-slate-500">Triggers currently allowed at the platform layer.</p></div>
                        <div class="wa-stat-card"><p class="text-[11px] font-black uppercase tracking-[0.16em] text-slate-500">Ready To Send</p><p class="mt-2 text-3xl font-black text-slate-950"><?= (int) $readyCount ?>/<?= count($waTriggerCatalog) ?></p><p class="mt-2 text-xs text-slate-500">Triggers with a valid approved Meta template name and valid variable mapping.</p></div>
                    </div>
                </div>
                <div class="flex flex-col gap-3 min-w-[260px]">
                    <a href="<?= base_url('controlplane/whatsapp') ?>" class="control-settings-submit justify-center" style="background:linear-gradient(135deg,#10b981,#059669); box-shadow:0 14px 32px rgba(16,185,129,.18);"><i class="fas fa-arrow-up-right-from-square mr-2"></i>Open WhatsApp Ops</a>
                    <a href="<?= base_url('controlplane/operations') ?>" class="control-settings-secondary justify-center"><i class="fas fa-gauge-high mr-2"></i>Open Operations Console</a>
                </div>
            </div>
        </div>

        <div class="overflow-x-auto"><div class="inline-flex gap-3">
            <button type="button" class="wa-settings-segment is-active" data-wa-tab-button="overview" onclick="showWhatsAppSettingsTab('overview')"><i class="fas fa-compass"></i><span>Overview</span></button>
            <button type="button" class="wa-settings-segment" data-wa-tab-button="templates" onclick="showWhatsAppSettingsTab('templates')"><i class="fas fa-file-signature"></i><span>Templates</span></button>
            <button type="button" class="wa-settings-segment" data-wa-tab-button="rollout" onclick="showWhatsAppSettingsTab('rollout')"><i class="fas fa-toggle-on"></i><span>Rollout</span></button>
            <button type="button" class="wa-settings-segment" data-wa-tab-button="credentials" onclick="showWhatsAppSettingsTab('credentials')"><i class="fas fa-key"></i><span>Credentials</span></button>
        </div></div>

        <div id="wa-settings-pane-overview" class="wa-settings-pane space-y-6">
            <div class="grid grid-cols-1 gap-4 xl:grid-cols-3">
                <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm xl:col-span-2">
                    <p class="text-[11px] font-black uppercase tracking-[0.16em] text-slate-500">Operating Model</p>
                    <div class="mt-4 grid gap-4 md:grid-cols-3">
                        <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4"><p class="text-sm font-black text-slate-900">1. Shared Templates</p><p class="mt-2 text-xs leading-6 text-slate-600">All five patient notification templates are managed once in control plane. Language is fixed to Arabic.</p></div>
                        <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4"><p class="text-sm font-black text-slate-900">2. Clinic Identity</p><p class="mt-2 text-xs leading-6 text-slate-600">Each clinic supplies its own name, phone, and address from tenant settings. Those values are injected into the shared templates at send time.</p></div>
                        <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4"><p class="text-sm font-black text-slate-900">3. Rollout Layers</p><p class="mt-2 text-xs leading-6 text-slate-600">A trigger runs only if the clinic plan allows WhatsApp, the platform switch is on, and the clinic-level switch is enabled.</p></div>
                    </div>
                </div>
                <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                    <p class="text-[11px] font-black uppercase tracking-[0.16em] text-slate-500">Template Editing Rule</p>
                    <p class="mt-3 text-sm font-semibold text-slate-900">Edit the structure here, then make sure Meta has matching approved templates.</p>
                    <ul class="mt-3 space-y-2 text-xs leading-6 text-slate-600"><li>Use the starter Arabic copy as your source draft.</li><li>Keep the variable order exactly aligned with Meta approvals.</li><li>Use clinic detail pages for per-clinic rollout, not for template content.</li></ul>
                </div>
            </div>
        </div>

        <div id="wa-settings-pane-templates" class="wa-settings-pane hidden space-y-6">
            <form action="<?= base_url('controlplane/settings/whatsapp-templates') ?>" method="post" class="space-y-6" data-settings-form="true" data-saving-label="Saving WhatsApp templates...">
                <?= csrf_field() ?>
                <div class="grid grid-cols-1 gap-5 xl:grid-cols-[280px,minmax(0,1fr)]">
                    <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm xl:sticky xl:top-6">
                        <p class="px-1 text-[11px] font-black uppercase tracking-[0.16em] text-slate-500">Template List</p>
                        <div class="mt-3 space-y-2">
                            <button type="button" class="wa-template-nav-btn is-active" data-wa-template-button="fallback" onclick="showWhatsAppTemplateEditor('fallback')">
                                <div>
                                    <p class="wa-template-nav-title text-sm font-black text-slate-950">Fallback</p>
                                    <p class="mt-1 text-xs text-slate-500">Out-of-session recovery</p>
                                </div>
                                <span class="rounded-full bg-slate-100 px-2.5 py-1 text-[10px] font-black uppercase tracking-[0.16em] text-slate-600">Arabic</span>
                            </button>
                            <?php foreach ($waTriggerCatalog as $triggerKey => $triggerMeta): ?>
                                <?php
                                $triggerConfig = is_array($waGlobalTriggerTemplates[$triggerKey] ?? null) ? $waGlobalTriggerTemplates[$triggerKey] : [];
                                $platformEnabled = (int) ($waGlobalTriggerToggles[$triggerKey] ?? ($triggerMeta['default_enabled'] ?? 1)) === 1;
                                $displayTemplateName = trim((string) ($triggerConfig['name'] ?? '')) !== '' ? (string) ($triggerConfig['name'] ?? '') : (string) ($triggerMeta['suggested_template_name'] ?? ($triggerKey . '_ar_v1'));
                                $readiness = is_array($waTriggerReadiness[$triggerKey] ?? null) ? $waTriggerReadiness[$triggerKey] : ['ready' => false, 'errors' => []];
                                ?>
                                <button type="button" class="wa-template-nav-btn" data-wa-template-button="<?= esc($triggerKey) ?>" onclick="showWhatsAppTemplateEditor('<?= esc($triggerKey) ?>')">
                                    <div>
                                        <p class="wa-template-nav-title text-sm font-black text-slate-950"><?= esc($triggerMeta['label'] ?? $triggerKey) ?></p>
                                        <p class="mt-1 text-xs text-slate-500"><?= esc($displayTemplateName) ?></p>
                                    </div>
                                    <div class="flex flex-col items-end gap-1">
                                        <span class="rounded-full px-2.5 py-1 text-[10px] font-black uppercase tracking-[0.16em] <?= $platformEnabled ? 'bg-emerald-100 text-emerald-700' : 'bg-rose-100 text-rose-700' ?>">
                                            <?= $platformEnabled ? 'On' : 'Off' ?>
                                        </span>
                                        <span class="rounded-full px-2.5 py-1 text-[10px] font-black uppercase tracking-[0.16em] <?= !empty($readiness['ready']) ? 'bg-blue-100 text-blue-700' : 'bg-amber-100 text-amber-700' ?>">
                                            <?= !empty($readiness['ready']) ? 'Ready' : 'Needs config' ?>
                                        </span>
                                    </div>
                                </button>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <div class="min-w-0 space-y-5">
                        <div class="wa-template-editor-pane" id="wa-template-editor-fallback">
                            <div class="wa-template-card">
                                <div class="wa-template-card-header"><div class="flex items-center justify-between gap-4"><div><p class="text-[11px] font-black uppercase tracking-[0.16em] text-emerald-700">Fallback Template</p><h5 class="mt-2 text-lg font-black text-slate-950">General re-engagement / out-of-session fallback</h5><p class="mt-1 text-sm text-slate-600">Use only when an operator explicitly chooses the fallback template in the WhatsApp ops console.</p></div><span class="inline-flex items-center rounded-full bg-white px-3 py-1 text-[10px] font-black uppercase tracking-[0.16em] text-slate-700 shadow-sm">Arabic only</span></div></div>
                                <div class="p-6 grid grid-cols-1 gap-5 xl:grid-cols-[1.1fr,0.9fr]">
                                    <div class="space-y-4">
                                        <div><label class="block text-xs font-black uppercase tracking-[0.14em] text-slate-600">Approved Meta template name</label><input type="text" name="fallback_template.name" value="<?= esc(($waGlobalFallback['name'] ?? '') !== '' ? ($waGlobalFallback['name'] ?? '') : ($waGlobalFallback['suggested_name'] ?? 'general_reengagement_ar_v1')) ?>" class="control-settings-field mt-2" placeholder="general_reengagement_ar_v1"></div>
                                        <div><label class="block text-xs font-black uppercase tracking-[0.14em] text-slate-600">Arabic reference copy</label><textarea name="fallback_template.reference_body_text" rows="6" class="control-settings-field wa-template-preview mt-2" placeholder="?????? {{patient_name}} ..."><?= esc($waGlobalFallback['reference_body_text'] ?? '') ?></textarea><p class="mt-2 text-xs text-slate-500">This is your editable reference copy for operations and Meta alignment. The app still sends the approved Meta template by name.</p></div>
                                    </div>
                                    <div class="rounded-2xl border border-dashed border-slate-200 bg-slate-50 p-5"><p class="text-[11px] font-black uppercase tracking-[0.16em] text-slate-500">How to use</p><ul class="mt-3 space-y-2 text-sm leading-6 text-slate-600"><li>Keep this simple and reusable.</li><li>Use it only outside the 24-hour session window.</li><li>Include clinic identity variables like <code class="font-mono text-[11px]">clinic_name</code> and <code class="font-mono text-[11px]">clinic_phone</code>.</li></ul></div>
                                </div>
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
                            $referenceHeader = trim((string) ($triggerConfig['reference_header_text'] ?? '')) !== '' ? (string) ($triggerConfig['reference_header_text'] ?? '') : (string) ($triggerMeta['reference_header_text'] ?? '');
                            $referenceBody = trim((string) ($triggerConfig['reference_body_text'] ?? '')) !== '' ? (string) ($triggerConfig['reference_body_text'] ?? '') : (string) ($triggerMeta['reference_body_text'] ?? '');
                            $referenceButton = trim((string) ($triggerConfig['reference_button_text'] ?? '')) !== '' ? (string) ($triggerConfig['reference_button_text'] ?? '') : (string) ($triggerMeta['reference_button_text'] ?? '');
                            $readiness = is_array($waTriggerReadiness[$triggerKey] ?? null) ? $waTriggerReadiness[$triggerKey] : ['ready' => false, 'errors' => []];
                            ?>
                            <div class="wa-template-editor-pane hidden" id="wa-template-editor-<?= esc($triggerKey) ?>">
                                <div class="wa-template-card">
                                    <div class="wa-template-card-header">
                                        <div class="flex flex-col gap-3 xl:flex-row xl:items-start xl:justify-between">
                                            <div>
                                                <p class="text-[11px] font-black uppercase tracking-[0.16em] text-emerald-700"><?= esc($triggerMeta['label'] ?? $triggerKey) ?></p>
                                                <h5 class="mt-2 text-lg font-black text-slate-950"><?= esc($displayTemplateName) ?></h5>
                                                <p class="mt-1 text-sm text-slate-600"><?= esc($triggerMeta['description'] ?? '') ?></p>
                                            </div>
                                            <div class="flex flex-wrap gap-2">
                                                <span class="inline-flex items-center rounded-full bg-white px-3 py-1 text-[10px] font-black uppercase tracking-[0.16em] text-slate-700 shadow-sm">Template required</span>
                                                <span class="inline-flex items-center rounded-full px-3 py-1 text-[10px] font-black uppercase tracking-[0.16em] <?= $platformEnabled ? 'bg-emerald-600 text-white' : 'bg-rose-100 text-rose-700' ?>">
                                                    <?= $platformEnabled ? 'Platform enabled' : 'Platform disabled' ?>
                                                </span>
                                                <span class="inline-flex items-center rounded-full px-3 py-1 text-[10px] font-black uppercase tracking-[0.16em] <?= !empty($readiness['ready']) ? 'bg-blue-600 text-white' : 'bg-amber-100 text-amber-700' ?>">
                                                    <?= !empty($readiness['ready']) ? 'Ready' : 'Needs config' ?>
                                                </span>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="p-6 grid grid-cols-1 gap-6 xl:grid-cols-[1.05fr,0.95fr]">
                                        <div class="space-y-4">
                                            <?php if (!empty($readiness['errors']) && is_array($readiness['errors'])): ?>
                                                <div class="rounded-2xl border border-amber-200 bg-amber-50 px-4 py-3 text-amber-800">
                                                    <p class="text-xs font-black uppercase tracking-[0.16em]">Template readiness issues</p>
                                                    <ul class="mt-2 list-disc space-y-1 ps-5 text-sm">
                                                        <?php foreach ($readiness['errors'] as $error): ?>
                                                            <li><?= esc($error) ?></li>
                                                        <?php endforeach; ?>
                                                    </ul>
                                                </div>
                                            <?php endif; ?>
                                            <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                                                <div><label class="block text-xs font-black uppercase tracking-[0.14em] text-slate-600">Approved Meta template name</label><input type="text" name="trigger_templates.<?= esc($triggerKey) ?>.name" value="<?= esc($displayTemplateName) ?>" class="control-settings-field mt-2" placeholder="<?= esc($suggestedTemplateName) ?>"></div>
                                                <div><label class="block text-xs font-black uppercase tracking-[0.14em] text-slate-600">Header variable key</label><input type="text" name="trigger_templates.<?= esc($triggerKey) ?>.header_param_key" value="<?= esc($triggerConfig['header_param_key'] ?? '') ?>" class="control-settings-field mt-2" placeholder="Optional"></div>
                                            </div>

                                            <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                                                <div><label class="block text-xs font-black uppercase tracking-[0.14em] text-slate-600">Reference header text</label><input type="text" name="trigger_templates.<?= esc($triggerKey) ?>.reference_header_text" value="<?= esc($referenceHeader) ?>" class="control-settings-field mt-2" placeholder="Optional"></div>
                                                <div><label class="block text-xs font-black uppercase tracking-[0.14em] text-slate-600">Body variable keys</label><input type="text" name="trigger_templates.<?= esc($triggerKey) ?>.body_param_keys" value="<?= esc(implode(', ', $bodyParamKeys)) ?>" class="control-settings-field mt-2" placeholder="<?= esc(implode(', ', $triggerMeta['body_param_keys'] ?? [])) ?>"></div>
                                            </div>

                                            <div><label class="block text-xs font-black uppercase tracking-[0.14em] text-slate-600">Arabic reference body</label><textarea name="trigger_templates.<?= esc($triggerKey) ?>.reference_body_text" rows="7" class="control-settings-field wa-template-preview mt-2" placeholder="???? ???? ?????? ??????? ???? ??????"><?= esc($referenceBody) ?></textarea></div>

                                            <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
                                                <div><label class="block text-xs font-black uppercase tracking-[0.14em] text-slate-600">Button type</label><select name="trigger_templates.<?= esc($triggerKey) ?>.button_type" class="control-settings-field mt-2"><option value="" <?= ($triggerConfig['button_type'] ?? '') === '' ? 'selected' : '' ?>>No button</option><option value="quick_reply" <?= ($triggerConfig['button_type'] ?? '') === 'quick_reply' ? 'selected' : '' ?>>Quick reply</option><option value="url" <?= ($triggerConfig['button_type'] ?? '') === 'url' ? 'selected' : '' ?>>URL</option></select></div>
                                                <div><label class="block text-xs font-black uppercase tracking-[0.14em] text-slate-600">Button index</label><input type="text" name="trigger_templates.<?= esc($triggerKey) ?>.button_index" value="<?= esc($triggerConfig['button_index'] ?? '0') ?>" class="control-settings-field mt-2" placeholder="0"></div>
                                                <div><label class="block text-xs font-black uppercase tracking-[0.14em] text-slate-600">Button variable key</label><input type="text" name="trigger_templates.<?= esc($triggerKey) ?>.button_param_key" value="<?= esc($triggerConfig['button_param_key'] ?? '') ?>" class="control-settings-field mt-2" placeholder="Optional"></div>
                                            </div>

                                            <div><label class="block text-xs font-black uppercase tracking-[0.14em] text-slate-600">Reference button text</label><input type="text" name="trigger_templates.<?= esc($triggerKey) ?>.reference_button_text" value="<?= esc($referenceButton) ?>" class="control-settings-field mt-2" placeholder="Optional"></div>
                                        </div>

                                        <div class="space-y-4">
                                            <div class="rounded-2xl border border-slate-200 bg-slate-50 p-5"><p class="text-[11px] font-black uppercase tracking-[0.16em] text-slate-500">Operator Notes</p><p class="mt-3 text-sm leading-6 text-slate-700"><?= esc($triggerMeta['operator_notes'] ?? 'Keep the Meta template name and variable order aligned with your approved template.') ?></p></div>
                                            <?php if ($availableVars !== []): ?>
                                                <div class="rounded-2xl border border-dashed border-slate-200 bg-white p-5"><p class="text-[11px] font-black uppercase tracking-[0.16em] text-slate-500">Available variables</p><div class="mt-3 flex flex-wrap gap-2"><?php foreach ($availableVars as $varKey => $varHelp): ?><span class="inline-flex items-center rounded-full border border-slate-200 bg-slate-50 px-3 py-1.5 text-xs text-slate-700"><span class="mr-1 font-black text-slate-900"><?= esc($varKey) ?></span><?= esc($varHelp) ?></span><?php endforeach; ?></div></div>
                                            <?php endif; ?>
                                            <div class="rounded-2xl border border-slate-200 bg-white p-5"><p class="text-[11px] font-black uppercase tracking-[0.16em] text-slate-500">Ready-to-use structure</p><ol class="mt-3 space-y-2 text-sm leading-6 text-slate-600 list-decimal ps-5"><li>Keep the template name equal to the approved Meta name.</li><li>Keep the variable order exactly as approved.</li><li>Let clinics supply their own identity values from tenant clinic settings.</li></ol></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <div class="flex justify-end"><button type="submit" class="control-settings-submit"><i class="fas fa-save mr-2"></i>Save Global WhatsApp Templates</button></div>
            </form>
        </div>
        <div id="wa-settings-pane-rollout" class="wa-settings-pane hidden space-y-6">
            <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                <div class="flex items-start justify-between gap-4 mb-5">
                    <div>
                        <h5 class="text-base font-black text-slate-950">Platform trigger master switches</h5>
                        <p class="mt-1 text-sm text-slate-600">Use this layer to enable or disable each WhatsApp workflow globally before clinic-level rollout is considered.</p>
                    </div>
                    <span class="inline-flex items-center rounded-full bg-indigo-50 px-3 py-1 text-[10px] font-black uppercase tracking-[0.16em] text-indigo-700">Platform layer</span>
                </div>

                <form action="<?= base_url('controlplane/settings/whatsapp-templates') ?>" method="post" class="space-y-5" data-settings-form="true" data-saving-label="Saving rollout switches...">
                    <?= csrf_field() ?>
                    <?php foreach ($waTriggerCatalog as $triggerKey => $triggerMeta): ?>
                        <?php $platformEnabled = (int) ($waGlobalTriggerToggles[$triggerKey] ?? ($triggerMeta['default_enabled'] ?? 1)) === 1; $readiness = is_array($waTriggerReadiness[$triggerKey] ?? null) ? $waTriggerReadiness[$triggerKey] : ['ready' => false]; ?>
                        <label class="flex items-center justify-between rounded-2xl border border-slate-200 bg-slate-50 px-4 py-4 gap-4">
                            <div>
                                <p class="font-bold text-slate-900"><?= esc($triggerMeta['label'] ?? $triggerKey) ?></p>
                                <p class="mt-1 text-xs text-slate-500"><?= esc($triggerMeta['description'] ?? '') ?></p>
                                <div class="mt-2 flex flex-wrap gap-2">
                                    <span class="rounded-full px-2.5 py-1 text-[10px] font-black uppercase tracking-[0.16em] <?= !empty($readiness['ready']) ? 'bg-blue-100 text-blue-700' : 'bg-amber-100 text-amber-700' ?>">
                                        <?= !empty($readiness['ready']) ? 'Ready to send' : 'Needs template config' ?>
                                    </span>
                                </div>
                            </div>
                            <input type="checkbox" name="global_trigger_toggles[<?= esc($triggerKey) ?>]" value="1" class="ui-toggle" <?= $platformEnabled ? 'checked' : '' ?>>
                        </label>
                    <?php endforeach; ?>

                    <?php foreach ($waTriggerCatalog as $triggerKey => $triggerMeta): ?>
                        <?php $triggerConfig = is_array($waGlobalTriggerTemplates[$triggerKey] ?? null) ? $waGlobalTriggerTemplates[$triggerKey] : []; $displayTemplateName = trim((string) ($triggerConfig['name'] ?? '')) !== '' ? (string) ($triggerConfig['name'] ?? '') : (string) ($triggerMeta['suggested_template_name'] ?? ($triggerKey . '_ar_v1')); ?>
                        <input type="hidden" name="trigger_templates.<?= esc($triggerKey) ?>.name" value="<?= esc($displayTemplateName) ?>">
                        <input type="hidden" name="trigger_templates.<?= esc($triggerKey) ?>.header_param_key" value="<?= esc($triggerConfig['header_param_key'] ?? '') ?>">
                        <input type="hidden" name="trigger_templates.<?= esc($triggerKey) ?>.body_param_keys" value="<?= esc(implode(', ', is_array($triggerConfig['body_param_keys'] ?? null) ? $triggerConfig['body_param_keys'] : ($triggerMeta['body_param_keys'] ?? []))) ?>">
                        <input type="hidden" name="trigger_templates.<?= esc($triggerKey) ?>.button_type" value="<?= esc($triggerConfig['button_type'] ?? '') ?>">
                        <input type="hidden" name="trigger_templates.<?= esc($triggerKey) ?>.button_index" value="<?= esc($triggerConfig['button_index'] ?? '0') ?>">
                        <input type="hidden" name="trigger_templates.<?= esc($triggerKey) ?>.button_param_key" value="<?= esc($triggerConfig['button_param_key'] ?? '') ?>">
                        <input type="hidden" name="trigger_templates.<?= esc($triggerKey) ?>.reference_header_text" value="<?= esc($triggerConfig['reference_header_text'] ?? ($triggerMeta['reference_header_text'] ?? '')) ?>">
                        <input type="hidden" name="trigger_templates.<?= esc($triggerKey) ?>.reference_body_text" value="<?= esc($triggerConfig['reference_body_text'] ?? ($triggerMeta['reference_body_text'] ?? '')) ?>">
                        <input type="hidden" name="trigger_templates.<?= esc($triggerKey) ?>.reference_button_text" value="<?= esc($triggerConfig['reference_button_text'] ?? ($triggerMeta['reference_button_text'] ?? '')) ?>">
                    <?php endforeach; ?>
                    <input type="hidden" name="fallback_template.name" value="<?= esc(($waGlobalFallback['name'] ?? '') !== '' ? ($waGlobalFallback['name'] ?? '') : ($waGlobalFallback['suggested_name'] ?? 'general_reengagement_ar_v1')) ?>">
                    <input type="hidden" name="fallback_template.reference_body_text" value="<?= esc($waGlobalFallback['reference_body_text'] ?? '') ?>">

                    <div class="flex justify-end"><button type="submit" class="control-settings-submit"><i class="fas fa-save mr-2"></i>Save Platform Rollout</button></div>
                </form>
            </div>

            <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                <div class="flex items-start justify-between gap-4 mb-5">
                    <div>
                        <h5 class="text-base font-black text-slate-950">Clinic rollout and rate controls</h5>
                        <p class="mt-1 text-sm text-slate-600">Clinic-specific toggles, limits, and emergency pause now live on each clinic detail page so operators can manage rollout in context.</p>
                    </div>
                    <span class="inline-flex items-center rounded-full bg-slate-100 px-3 py-1 text-[10px] font-black uppercase tracking-[0.16em] text-slate-700">Clinic layer</span>
                </div>
                <div class="grid grid-cols-1 xl:grid-cols-2 gap-4">
                    <?php foreach ($waPolicies as $pol): ?>
                        <div class="rounded-2xl border border-slate-200 bg-slate-50 p-5">
                            <div class="flex items-start justify-between gap-4">
                                <div>
                                    <p class="text-base font-black text-slate-950"><?= esc($pol['clinic_name']) ?></p>
                                    <p class="mt-1 text-xs text-slate-500">Open the clinic detail page to manage the five template toggles and policy limits for this clinic.</p>
                                </div>
                                <span class="inline-flex items-center rounded-full px-3 py-1 text-[10px] font-black uppercase tracking-[0.16em] <?= !empty($pol['suspended']) ? 'bg-rose-100 text-rose-700' : 'bg-emerald-100 text-emerald-700' ?>">
                                    <?= !empty($pol['suspended']) ? 'Paused' : 'Active' ?>
                                </span>
                            </div>
                            <div class="mt-4 flex items-center justify-between gap-4 text-xs text-slate-500">
                                <span>Daily <?= (int) $pol['daily_limit'] ?> / Monthly <?= (int) $pol['monthly_limit'] ?> / Hourly <?= (int) $pol['per_hour_limit'] ?></span>
                                <a href="<?= base_url('controlplane/clinics/' . (int) $pol['clinic_id']) ?>" class="inline-flex items-center rounded-xl border border-slate-200 bg-white px-4 py-2 text-sm font-black text-slate-800 hover:border-slate-300 hover:bg-slate-100">Open Clinic</a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <div id="wa-settings-pane-credentials" class="wa-settings-pane hidden space-y-6">
            <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                <div class="flex items-start justify-between gap-4 mb-5">
                    <div><h5 class="text-base font-black text-slate-950">API credentials</h5><p class="mt-1 text-sm text-slate-600">Rotate WhatsApp platform secrets here. Changes affect the shared sender for all clinics.</p></div>
                    <span class="inline-flex items-center rounded-full bg-slate-100 px-3 py-1 text-[10px] font-black uppercase tracking-[0.16em] text-slate-700">Sensitive</span>
                </div>
                <div class="space-y-4">
                    <?php foreach (($wa_secrets ?? []) as $s): ?>
                        <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                            <div class="flex items-center justify-between mb-3"><code class="text-xs font-black text-blue-600"><?= esc($s['secret_key']) ?></code><span class="text-[10px] font-black <?= !empty($s['missing']) ? 'text-rose-600' : 'text-emerald-600' ?>"><?= !empty($s['missing']) ? 'MISSING' : 'READY' ?></span></div>
                            <form action="<?= base_url('controlplane/whatsapp/rotate-secret') ?>" method="post" class="grid grid-cols-1 md:grid-cols-3 gap-3" data-settings-form="true" data-saving-label="Rotating key..."><?= csrf_field() ?><input type="hidden" name="secret_key" value="<?= esc($s['secret_key']) ?>"><div class="relative md:col-span-1"><input type="password" name="new_value" required class="control-settings-field pr-10 text-sm font-mono" placeholder="Paste new secret value"><button type="button" onclick="toggleSecretVis(this)" class="absolute right-3 top-2.5 text-slate-400"><i class="fas fa-eye text-sm"></i></button></div><input type="text" name="rotation_note" class="control-settings-field text-sm md:col-span-1" placeholder="Rotation note"><button type="submit" class="control-settings-submit md:col-span-1">Rotate Key</button></form>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function showWhatsAppSettingsTab(tabName) {
    document.querySelectorAll('[data-wa-tab-button]').forEach((button) => {
        button.classList.toggle('is-active', button.dataset.waTabButton === tabName);
    });
    document.querySelectorAll('.wa-settings-pane').forEach((pane) => {
        pane.classList.toggle('hidden', pane.id !== `wa-settings-pane-${tabName}`);
    });
}

function showWhatsAppTemplateEditor(templateKey) {
    document.querySelectorAll('[data-wa-template-button]').forEach((button) => {
        button.classList.toggle('is-active', button.dataset.waTemplateButton === templateKey);
    });
    document.querySelectorAll('.wa-template-editor-pane').forEach((pane) => {
        pane.classList.toggle('hidden', pane.id !== `wa-template-editor-${templateKey}`);
    });
}

document.addEventListener('DOMContentLoaded', () => {
    if (document.getElementById('tab-whatsapp-templates')) {
        const requestedPane = new URLSearchParams(window.location.search).get('wa_tab');
        showWhatsAppSettingsTab(['overview', 'templates', 'rollout', 'credentials'].includes(requestedPane) ? requestedPane : 'templates');
        showWhatsAppTemplateEditor('fallback');
    }
});
</script>
