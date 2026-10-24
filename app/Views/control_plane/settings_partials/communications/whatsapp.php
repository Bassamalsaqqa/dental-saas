
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
        color: var(--denta-muted);
        font-size: .875rem;
        font-weight: 700;
        transition: all .2s ease;
        white-space: nowrap;
    }
    .wa-settings-segment:hover { background:var(--denta-alabaster); color: var(--denta-forest); border-color: rgba(59, 130, 246, .2); }
    .wa-settings-segment.is-active { background:var(--denta-alabaster); color:var(--denta-ivory-input); border-color: transparent; box-shadow:none; }
    .wa-settings-pane.hidden { display: none !important; }
    .wa-template-card { border: 1px solid var(--denta-taupe-border); border-radius: 1.25rem; background:var(--denta-alabaster); box-shadow:none; overflow: hidden; }
    .wa-template-card-header { padding: 1.25rem 1.5rem; background:var(--denta-alabaster); border-bottom: 1px solid var(--denta-taupe-border); }
    .wa-template-preview { min-height: 10rem; direction: rtl; text-align: right; line-height: 1.9; font-family: inherit; }
    .wa-stat-card { border: 1px solid var(--denta-taupe-border); border-radius: 1.25rem; background:var(--denta-alabaster); padding: 1.25rem; box-shadow:none; }
    .wa-template-nav-btn {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: .75rem;
        width: 100%;
        padding: .95rem 1rem;
        border-radius: 1rem;
        border: 1px solid var(--denta-taupe-border);
        background:var(--denta-alabaster);
        text-align: left;
        transition: all .2s ease;
    }
    .wa-template-nav-btn:hover {
        background: var(--denta-alabaster);
        border-color: var(--denta-taupe-border);
    }
    .wa-template-nav-btn.is-active {
        background: #eff6ff;
        border-color: #bfdbfe;
        box-shadow:none;
    }
    .wa-template-nav-btn.is-active .wa-template-nav-title {
        color: #1d4ed8;
    }
    .wa-template-editor-pane.hidden { display: none !important; }
</style>

<div id="tab-whatsapp-templates" data-section-title="<?= esc(lang('V3Platform.whatsappTemplates'), 'attr') ?>" class="settings-content hidden space-y-6">
    <div id="section-whatsapp-overview" data-section-title="<?= esc(lang('V3Platform.whatsappOverview'), 'attr') ?>" class="space-y-6">
        <div class="rounded-[1.75rem] border v3-border-default p-6">
            <div class="flex flex-col gap-3 xl:flex-row xl:items-start xl:justify-between">
                <div class="max-w-3xl">
                    <div class="flex items-center gap-3">
                        <div class="flex h-12 w-12 items-center justify-center cp-radius cp-primary-surface v3-text-on-primary">
                            <i class="fab fa-whatsapp text-xl"></i>
                        </div>
                        <div>
                            <h4 class="text-xl font-black v3-text-ink"><?= esc(lang('V3Platform.whatsappPlatformSettings')) ?></h4>
                            <p class="mt-1 text-sm v3-text-muted"><?= esc(lang('V3Platform.oneSharedWhatsappSenderFiveSharedArabicTemplatesAndClinicLevelRolloutControl')) ?></p>
                        </div>
                    </div>
                    <div class="mt-4 grid grid-cols-1 gap-3 md:grid-cols-3">
                        <div class="wa-stat-card"><p class="text-[11px] font-black uppercase tracking-[0.16em] v3-text-muted"><?= esc(lang('V3Platform.configuredTemplates')) ?></p><p class="mt-2 cp-title font-black v3-text-ink"><?= (int) $configuredCount ?>/<?= count($waTriggerCatalog) ?></p><p class="mt-2 text-xs v3-text-muted"><?= esc(lang('V3Platform.approvedMetaTemplateNamesSavedInControlPlane')) ?></p></div>
                        <div class="wa-stat-card"><p class="text-[11px] font-black uppercase tracking-[0.16em] v3-text-muted"><?= esc(lang('V3Platform.platformEnabled')) ?></p><p class="mt-2 cp-title font-black v3-text-ink"><?= (int) $enabledCount ?>/<?= count($waTriggerCatalog) ?></p><p class="mt-2 text-xs v3-text-muted"><?= esc(lang('V3Platform.triggersCurrentlyAllowedAtThePlatformLayer')) ?></p></div>
                        <div class="wa-stat-card"><p class="text-[11px] font-black uppercase tracking-[0.16em] v3-text-muted"><?= esc(lang('V3Platform.readyToSend')) ?></p><p class="mt-2 cp-title font-black v3-text-ink"><?= (int) $readyCount ?>/<?= count($waTriggerCatalog) ?></p><p class="mt-2 text-xs v3-text-muted"><?= esc(lang('V3Platform.triggersWithAValidApprovedMetaTemplateNameAndValidVariableMapping')) ?></p></div>
                    </div>
                </div>
                <div class="flex flex-col gap-3 min-w-[260px]">
                    <a href="<?= base_url('controlplane/whatsapp') ?>" class="control-settings-submit justify-center" style="background:linear-gradient(135deg,#10b981,#059669); box-shadow:0 14px 32px rgba(16,185,129,.18);"><i class="fas mr-2"></i><?= esc(lang('V3Platform.openWhatsappOps')) ?></a>
                    <a href="<?= base_url('controlplane/operations') ?>" class="control-settings-secondary justify-center"><i class="fas fa-gauge-high mr-2"></i><?= esc(lang('V3Platform.openOperationsConsole')) ?></a>
                </div>
            </div>
        </div>

        <div class="overflow-x-auto"><div class="inline-flex gap-3">
            <button type="button" class="wa-settings-segment is-active v3-btn v3-btn--secondary" data-wa-tab-button="overview" onclick="showWhatsAppSettingsTab('overview')"><i class="fas fa-compass"></i><span><?= esc(lang('V3Platform.overview')) ?></span></button>
            <button type="button" class="wa-settings-segment v3-btn v3-btn--secondary" data-wa-tab-button="templates" onclick="showWhatsAppSettingsTab('templates')"><i class="fas fa-file-signature"></i><span><?= esc(lang('V3Platform.templates')) ?></span></button>
            <button type="button" class="wa-settings-segment v3-btn v3-btn--secondary" data-wa-tab-button="rollout" onclick="showWhatsAppSettingsTab('rollout')"><i class="fas fa-toggle-on"></i><span><?= esc(lang('V3Platform.rollout')) ?></span></button>
            <button type="button" class="wa-settings-segment v3-btn v3-btn--secondary" data-wa-tab-button="credentials" onclick="showWhatsAppSettingsTab('credentials')"><i class="fas fa-key"></i><span><?= esc(lang('V3Platform.credentials')) ?></span></button>
        </div></div>

        <div id="wa-settings-pane-overview" class="wa-settings-pane space-y-6">
            <div class="grid grid-cols-1 gap-4 xl:grid-cols-3">
                <div class="cp-radius border cp-border cp-surface p-5 xl:col-span-2">
                    <p class="text-[11px] font-black uppercase tracking-[0.16em] v3-text-muted"><?= esc(lang('V3Platform.operatingModel')) ?></p>
                    <div class="mt-4 grid gap-4 md:grid-cols-3">
                        <div class="cp-radius border cp-border cp-surface p-4"><p class="text-sm font-black v3-text-ink"><?= esc(lang('V3Platform.label1SharedTemplates')) ?></p><p class="mt-2 text-xs leading-6 v3-text-muted"><?= esc(lang('V3Platform.allFivePatientNotificationTemplatesAreManagedOnceInControlPlaneLanguageIsFixedToArabic')) ?></p></div>
                        <div class="cp-radius border cp-border cp-surface p-4"><p class="text-sm font-black v3-text-ink"><?= esc(lang('V3Platform.label2ClinicIdentity')) ?></p><p class="mt-2 text-xs leading-6 v3-text-muted"><?= esc(lang('V3Platform.eachClinicSuppliesItsOwnNamePhoneAndAddressFromTenantSettingsThoseValuesAreInjectedIntoTheSharedTemplatesAtSendTime')) ?></p></div>
                        <div class="cp-radius border cp-border cp-surface p-4"><p class="text-sm font-black v3-text-ink"><?= esc(lang('V3Platform.label3RolloutLayers')) ?></p><p class="mt-2 text-xs leading-6 v3-text-muted"><?= esc(lang('V3Platform.aTriggerRunsOnlyIfTheClinicPlanAllowsWhatsappThePlatformSwitchIsOnAndTheClinicLevelSwitchIsEnabled')) ?></p></div>
                    </div>
                </div>
                <div class="cp-radius border cp-border cp-surface p-5">
                    <p class="text-[11px] font-black uppercase tracking-[0.16em] v3-text-muted"><?= esc(lang('V3Platform.templateEditingRule')) ?></p>
                    <p class="mt-3 text-sm font-semibold v3-text-ink"><?= esc(lang('V3Platform.editTheStructureHereThenMakeSureMetaHasMatchingApprovedTemplates')) ?></p>
                    <ul class="mt-3 space-y-2 text-xs leading-6 v3-text-muted"><li><?= esc(lang('V3Platform.useTheStarterArabicCopyAsYourSourceDraft')) ?></li><li><?= esc(lang('V3Platform.keepTheVariableOrderExactlyAlignedWithMetaApprovals')) ?></li><li><?= esc(lang('V3Platform.useClinicDetailPagesForPerClinicRolloutNotForTemplateContent')) ?></li></ul>
                </div>
            </div>
        </div>

        <div id="wa-settings-pane-templates" class="wa-settings-pane hidden space-y-6">
            <form action="<?= base_url('controlplane/settings/whatsapp-templates') ?>" method="post" class="space-y-6" data-settings-form="true" data-saving-label="Saving WhatsApp templates...">
                <?= csrf_field() ?>
                <div class="grid grid-cols-1 gap-5 xl:grid-cols-[280px,minmax(0,1fr)]">
                    <div class="cp-radius border cp-border cp-surface p-4 xl:sticky xl:top-6">
                        <p class="px-1 text-[11px] font-black uppercase tracking-[0.16em] v3-text-muted"><?= esc(lang('V3Platform.templateList')) ?></p>
                        <div class="mt-3 space-y-2">
                            <button type="button" class="wa-template-nav-btn is-active v3-btn v3-btn--secondary" data-wa-template-button="fallback" onclick="showWhatsAppTemplateEditor('fallback')">
                                <div>
                                    <p class="wa-template-nav-title text-sm font-black v3-text-ink"><?= esc(lang('V3Platform.fallback')) ?></p>
                                    <p class="mt-1 text-xs v3-text-muted"><?= esc(lang('V3Platform.outOfSessionRecovery')) ?></p>
                                </div>
                                <span class="rounded-full v3-bg-soft px-2.5 py-1 text-[10px] font-black uppercase tracking-[0.16em] v3-text-muted"><?= esc(lang('V3Platform.arabic')) ?></span>
                            </button>
                            <?php foreach ($waTriggerCatalog as $triggerKey => $triggerMeta): ?>
                                <?php
                                $triggerConfig = is_array($waGlobalTriggerTemplates[$triggerKey] ?? null) ? $waGlobalTriggerTemplates[$triggerKey] : [];
                                $platformEnabled = (int) ($waGlobalTriggerToggles[$triggerKey] ?? ($triggerMeta['default_enabled'] ?? 1)) === 1;
                                $displayTemplateName = trim((string) ($triggerConfig['name'] ?? '')) !== '' ? (string) ($triggerConfig['name'] ?? '') : (string) ($triggerMeta['suggested_template_name'] ?? ($triggerKey . '_ar_v1'));
                                $readiness = is_array($waTriggerReadiness[$triggerKey] ?? null) ? $waTriggerReadiness[$triggerKey] : ['ready' => false, 'errors' => []];
                                ?>
                                <button type="button" class="wa-template-nav-btn v3-btn v3-btn--secondary" data-wa-template-button="<?= esc($triggerKey) ?>" onclick="showWhatsAppTemplateEditor('<?= esc($triggerKey) ?>')">
                                    <div>
                                        <p class="wa-template-nav-title text-sm font-black v3-text-ink"><?= esc($triggerMeta['label'] ?? $triggerKey) ?></p>
                                        <p class="mt-1 text-xs v3-text-muted"><?= esc($displayTemplateName) ?></p>
                                    </div>
                                    <div class="flex flex-col items-end gap-1">
                                        <span class="rounded-full px-2.5 py-1 text-[10px] font-black uppercase tracking-[0.16em] <?= $platformEnabled ? 'v3-bg-paid v3-text-primary' : 'v3-bg-alert v3-text-danger' ?>">
                                            <?= $platformEnabled ? 'On' : 'Off' ?>
                                        </span>
                                        <span class="rounded-full px-2.5 py-1 text-[10px] font-black uppercase tracking-[0.16em] <?= !empty($readiness['ready']) ? 'v3-bg-scheduled v3-text-primary' : 'v3-bg-waiting v3-text-warning' ?>">
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
                                <div class="wa-template-card-header"><div class="flex items-center justify-between gap-4"><div><p class="text-[11px] font-black uppercase tracking-[0.16em] v3-text-primary"><?= esc(lang('V3Platform.fallbackTemplate')) ?></p><h5 class="mt-2 text-lg font-black v3-text-ink"><?= esc(lang('V3Platform.generalReEngagementOutOfSessionFallback')) ?></h5><p class="mt-1 text-sm v3-text-muted"><?= esc(lang('V3Platform.useOnlyWhenAnOperatorExplicitlyChoosesTheFallbackTemplateInTheWhatsappOpsConsole')) ?></p></div><span class="inline-flex items-center rounded-full cp-surface px-3 py-1 text-[10px] font-black uppercase tracking-[0.16em] v3-text-ink"><?= esc(lang('V3Platform.arabicOnly')) ?></span></div></div>
                                <div class="p-6 grid grid-cols-1 gap-5 xl:grid-cols-[1.1fr,0.9fr]">
                                    <div class="space-y-4">
                                        <div><label class="block text-xs font-black uppercase tracking-[0.14em] v3-text-muted"><?= esc(lang('V3Platform.approvedMetaTemplateName')) ?></label><input type="text" name="fallback_template.name" value="<?= esc(($waGlobalFallback['name'] ?? '') !== '' ? ($waGlobalFallback['name'] ?? '') : ($waGlobalFallback['suggested_name'] ?? 'general_reengagement_ar_v1')) ?>" class="control-settings-field mt-2 v3-input" placeholder="general_reengagement_ar_v1"></div>
                                        <div><label class="block text-xs font-black uppercase tracking-[0.14em] v3-text-muted"><?= esc(lang('V3Platform.arabicReferenceCopy')) ?></label><textarea name="fallback_template.reference_body_text" rows="6" class="control-settings-field wa-template-preview mt-2 v3-textarea" placeholder="?????? {{patient_name}} ..."><?= esc($waGlobalFallback['reference_body_text'] ?? '') ?></textarea><p class="mt-2 text-xs v3-text-muted"><?= esc(lang('V3Platform.thisIsYourEditableReferenceCopyForOperationsAndMetaAlignmentTheAppStillSendsTheApprovedMetaTemplateByName')) ?></p></div>
                                    </div>
                                    <div class="cp-radius border border-dashed cp-border cp-surface p-5"><p class="text-[11px] font-black uppercase tracking-[0.16em] v3-text-muted"><?= esc(lang('V3Platform.howToUse')) ?></p><ul class="mt-3 space-y-2 text-sm leading-6 v3-text-muted"><li><?= esc(lang('V3Platform.keepThisSimpleAndReusable')) ?></li><li><?= esc(lang('V3Platform.useItOnlyOutsideThe24HourSessionWindow')) ?></li><li><?= esc(lang('V3Platform.includeClinicIdentityVariablesLike')) ?> <code class="font-mono text-[11px]">clinic_name</code> <?= esc(lang('V3Platform.and')) ?> <code class="font-mono text-[11px]">clinic_phone</code>.</li></ul></div>
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
                                                <p class="text-[11px] font-black uppercase tracking-[0.16em] v3-text-primary"><?= esc($triggerMeta['label'] ?? $triggerKey) ?></p>
                                                <h5 class="mt-2 text-lg font-black v3-text-ink"><?= esc($displayTemplateName) ?></h5>
                                                <p class="mt-1 text-sm v3-text-muted"><?= esc($triggerMeta['description'] ?? '') ?></p>
                                            </div>
                                            <div class="flex flex-wrap gap-2">
                                                <span class="inline-flex items-center rounded-full cp-surface px-3 py-1 text-[10px] font-black uppercase tracking-[0.16em] v3-text-ink"><?= esc(lang('V3Platform.templateRequired')) ?></span>
                                                <span class="inline-flex items-center rounded-full px-3 py-1 text-[10px] font-black uppercase tracking-[0.16em] <?= $platformEnabled ? 'v3-bg-primary v3-text-on-primary' : 'v3-bg-alert v3-text-danger' ?>">
                                                    <?= $platformEnabled ? 'Platform enabled' : 'Platform disabled' ?>
                                                </span>
                                                <span class="inline-flex items-center rounded-full px-3 py-1 text-[10px] font-black uppercase tracking-[0.16em] <?= !empty($readiness['ready']) ? 'v3-bg-primary v3-text-on-primary' : 'v3-bg-waiting v3-text-warning' ?>">
                                                    <?= !empty($readiness['ready']) ? 'Ready' : 'Needs config' ?>
                                                </span>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="p-6 grid grid-cols-1 gap-3 xl:grid-cols-[1.05fr,0.95fr]">
                                        <div class="space-y-4">
                                            <?php if (!empty($readiness['errors']) && is_array($readiness['errors'])): ?>
                                                <div class="cp-radius border v3-border-default v3-bg-waiting px-4 py-3 v3-text-warning">
                                                    <p class="text-xs font-black uppercase tracking-[0.16em]"><?= esc(lang('V3Platform.templateReadinessIssues')) ?></p>
                                                    <ul class="mt-2 list-disc space-y-1 ps-5 text-sm">
                                                        <?php foreach ($readiness['errors'] as $error): ?>
                                                            <li><?= esc($error) ?></li>
                                                        <?php endforeach; ?>
                                                    </ul>
                                                </div>
                                            <?php endif; ?>
                                            <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                                                <div><label class="block text-xs font-black uppercase tracking-[0.14em] v3-text-muted"><?= esc(lang('V3Platform.approvedMetaTemplateName')) ?></label><input type="text" name="trigger_templates.<?= esc($triggerKey) ?>.name" value="<?= esc($displayTemplateName) ?>" class="control-settings-field mt-2 v3-input" placeholder="<?= esc($suggestedTemplateName) ?>"></div>
                                                <div><label class="block text-xs font-black uppercase tracking-[0.14em] v3-text-muted"><?= esc(lang('V3Platform.headerVariableKey')) ?></label><input type="text" name="trigger_templates.<?= esc($triggerKey) ?>.header_param_key" value="<?= esc($triggerConfig['header_param_key'] ?? '') ?>" class="control-settings-field mt-2 v3-input" placeholder="<?= esc(lang('V3Platform.optional'), 'attr') ?>"></div>
                                            </div>

                                            <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                                                <div><label class="block text-xs font-black uppercase tracking-[0.14em] v3-text-muted"><?= esc(lang('V3Platform.referenceHeaderText')) ?></label><input type="text" name="trigger_templates.<?= esc($triggerKey) ?>.reference_header_text" value="<?= esc($referenceHeader) ?>" class="control-settings-field mt-2 v3-input" placeholder="<?= esc(lang('V3Platform.optional'), 'attr') ?>"></div>
                                                <div><label class="block text-xs font-black uppercase tracking-[0.14em] v3-text-muted"><?= esc(lang('V3Platform.bodyVariableKeys')) ?></label><input type="text" name="trigger_templates.<?= esc($triggerKey) ?>.body_param_keys" value="<?= esc(implode(', ', $bodyParamKeys)) ?>" class="control-settings-field mt-2 v3-input" placeholder="<?= esc(implode(', ', $triggerMeta['body_param_keys'] ?? [])) ?>"></div>
                                            </div>

                                            <div><label class="block text-xs font-black uppercase tracking-[0.14em] v3-text-muted"><?= esc(lang('V3Platform.arabicReferenceBody')) ?></label><textarea name="trigger_templates.<?= esc($triggerKey) ?>.reference_body_text" rows="7" class="control-settings-field wa-template-preview mt-2 v3-textarea" placeholder="???? ???? ?????? ??????? ???? ??????"><?= esc($referenceBody) ?></textarea></div>

                                            <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
                                                <div><label class="block text-xs font-black uppercase tracking-[0.14em] v3-text-muted"><?= esc(lang('V3Platform.buttonType')) ?></label><select name="trigger_templates.<?= esc($triggerKey) ?>.button_type" class="control-settings-field mt-2 v3-select"><option value="" <?= ($triggerConfig['button_type'] ?? '') === '' ? 'selected' : '' ?>><?= esc(lang('V3Platform.noButton')) ?></option><option value="quick_reply" <?= ($triggerConfig['button_type'] ?? '') === 'quick_reply' ? 'selected' : '' ?>><?= esc(lang('V3Platform.quickReply')) ?></option><option value="url" <?= ($triggerConfig['button_type'] ?? '') === 'url' ? 'selected' : '' ?>>URL</option></select></div>
                                                <div><label class="block text-xs font-black uppercase tracking-[0.14em] v3-text-muted"><?= esc(lang('V3Platform.buttonIndex')) ?></label><input type="text" name="trigger_templates.<?= esc($triggerKey) ?>.button_index" value="<?= esc($triggerConfig['button_index'] ?? '0') ?>" class="control-settings-field mt-2 v3-input" placeholder="0"></div>
                                                <div><label class="block text-xs font-black uppercase tracking-[0.14em] v3-text-muted"><?= esc(lang('V3Platform.buttonVariableKey')) ?></label><input type="text" name="trigger_templates.<?= esc($triggerKey) ?>.button_param_key" value="<?= esc($triggerConfig['button_param_key'] ?? '') ?>" class="control-settings-field mt-2 v3-input" placeholder="<?= esc(lang('V3Platform.optional'), 'attr') ?>"></div>
                                            </div>

                                            <div><label class="block text-xs font-black uppercase tracking-[0.14em] v3-text-muted"><?= esc(lang('V3Platform.referenceButtonText')) ?></label><input type="text" name="trigger_templates.<?= esc($triggerKey) ?>.reference_button_text" value="<?= esc($referenceButton) ?>" class="control-settings-field mt-2 v3-input" placeholder="<?= esc(lang('V3Platform.optional'), 'attr') ?>"></div>
                                        </div>

                                        <div class="space-y-4">
                                            <div class="cp-radius border cp-border cp-surface p-5"><p class="text-[11px] font-black uppercase tracking-[0.16em] v3-text-muted"><?= esc(lang('V3Platform.operatorNotes')) ?></p><p class="mt-3 text-sm leading-6 v3-text-ink"><?= esc($triggerMeta['operator_notes'] ?? 'Keep the Meta template name and variable order aligned with your approved template.') ?></p></div>
                                            <?php if ($availableVars !== []): ?>
                                                <div class="cp-radius border border-dashed cp-border cp-surface p-5"><p class="text-[11px] font-black uppercase tracking-[0.16em] v3-text-muted"><?= esc(lang('V3Platform.availableVariables')) ?></p><div class="mt-3 flex flex-wrap gap-2"><?php foreach ($availableVars as $varKey => $varHelp): ?><span class="inline-flex items-center rounded-full border cp-border cp-surface px-3 py-1.5 text-xs v3-text-ink"><span class="mr-1 font-black v3-text-ink"><?= esc($varKey) ?></span><?= esc($varHelp) ?></span><?php endforeach; ?></div></div>
                                            <?php endif; ?>
                                            <div class="cp-radius border cp-border cp-surface p-5"><p class="text-[11px] font-black uppercase tracking-[0.16em] v3-text-muted"><?= esc(lang('V3Platform.readyToUseStructure')) ?></p><ol class="mt-3 space-y-2 text-sm leading-6 v3-text-muted list-decimal ps-5"><li><?= esc(lang('V3Platform.keepTheTemplateNameEqualToTheApprovedMetaName')) ?></li><li><?= esc(lang('V3Platform.keepTheVariableOrderExactlyAsApproved')) ?></li><li><?= esc(lang('V3Platform.letClinicsSupplyTheirOwnIdentityValuesFromTenantClinicSettings')) ?></li></ol></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <div class="flex justify-end"><button type="submit" class="control-settings-submit v3-btn v3-btn--primary"><i class="fas fa-save mr-2"></i><?= esc(lang('V3Platform.saveGlobalWhatsappTemplates')) ?></button></div>
            </form>
        </div>
        <div id="wa-settings-pane-rollout" class="wa-settings-pane hidden space-y-6">
            <div class="cp-radius border cp-border cp-surface p-6">
                <div class="flex items-start justify-between gap-4 mb-5">
                    <div>
                        <h5 class="text-base font-black v3-text-ink"><?= esc(lang('V3Platform.platformTriggerMasterSwitches')) ?></h5>
                        <p class="mt-1 text-sm v3-text-muted"><?= esc(lang('V3Platform.useThisLayerToEnableOrDisableEachWhatsappWorkflowGloballyBeforeClinicLevelRolloutIsConsidered')) ?></p>
                    </div>
                    <span class="inline-flex items-center rounded-full cp-soft-surface px-3 py-1 text-[10px] font-black uppercase tracking-[0.16em] cp-accent"><?= esc(lang('V3Platform.platformLayer')) ?></span>
                </div>

                <form action="<?= base_url('controlplane/settings/whatsapp-templates') ?>" method="post" class="space-y-5" data-settings-form="true" data-saving-label="Saving rollout switches...">
                    <?= csrf_field() ?>
                    <?php foreach ($waTriggerCatalog as $triggerKey => $triggerMeta): ?>
                        <?php $platformEnabled = (int) ($waGlobalTriggerToggles[$triggerKey] ?? ($triggerMeta['default_enabled'] ?? 1)) === 1; $readiness = is_array($waTriggerReadiness[$triggerKey] ?? null) ? $waTriggerReadiness[$triggerKey] : ['ready' => false]; ?>
                        <label class="flex items-center justify-between cp-radius border cp-border cp-surface px-4 py-4 gap-4">
                            <div>
                                <p class="font-bold v3-text-ink"><?= esc($triggerMeta['label'] ?? $triggerKey) ?></p>
                                <p class="mt-1 text-xs v3-text-muted"><?= esc($triggerMeta['description'] ?? '') ?></p>
                                <div class="mt-2 flex flex-wrap gap-2">
                                    <span class="rounded-full px-2.5 py-1 text-[10px] font-black uppercase tracking-[0.16em] <?= !empty($readiness['ready']) ? 'v3-bg-scheduled v3-text-primary' : 'v3-bg-waiting v3-text-warning' ?>">
                                        <?= !empty($readiness['ready']) ? 'Ready to send' : 'Needs template config' ?>
                                    </span>
                                </div>
                            </div>
                            <input type="checkbox" name="global_trigger_toggles[<?= esc($triggerKey) ?>]" value="1" class="ui-toggle v3-switch" <?= $platformEnabled ? 'checked' : '' ?>>
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

                    <div class="flex justify-end"><button type="submit" class="control-settings-submit v3-btn v3-btn--primary"><i class="fas fa-save mr-2"></i><?= esc(lang('V3Platform.savePlatformRollout')) ?></button></div>
                </form>
            </div>

            <div class="cp-radius border cp-border cp-surface p-6">
                <div class="flex items-start justify-between gap-4 mb-5">
                    <div>
                        <h5 class="text-base font-black v3-text-ink"><?= esc(lang('V3Platform.clinicRolloutAndRateControls')) ?></h5>
                        <p class="mt-1 text-sm v3-text-muted"><?= esc(lang('V3Platform.clinicSpecificTogglesLimitsAndEmergencyPauseNowLiveOnEachClinicDetailPageSoOperatorsCanManageRolloutInContext')) ?></p>
                    </div>
                    <span class="inline-flex items-center rounded-full v3-bg-soft px-3 py-1 text-[10px] font-black uppercase tracking-[0.16em] v3-text-ink"><?= esc(lang('V3Platform.clinicLayer')) ?></span>
                </div>
                <div class="grid grid-cols-1 xl:grid-cols-2 gap-4">
                    <?php foreach ($waPolicies as $pol): ?>
                        <div class="cp-radius border cp-border cp-surface p-5">
                            <div class="flex items-start justify-between gap-4">
                                <div>
                                    <p class="text-base font-black v3-text-ink"><?= esc($pol['clinic_name']) ?></p>
                                    <p class="mt-1 text-xs v3-text-muted"><?= esc(lang('V3Platform.openTheClinicDetailPageToManageTheFiveTemplateTogglesAndPolicyLimitsForThisClinic')) ?></p>
                                </div>
                                <span class="inline-flex items-center rounded-full px-3 py-1 text-[10px] font-black uppercase tracking-[0.16em] <?= !empty($pol['suspended']) ? 'v3-bg-alert v3-text-danger' : 'v3-bg-paid v3-text-primary' ?>">
                                    <?= !empty($pol['suspended']) ? 'Paused' : 'Active' ?>
                                </span>
                            </div>
                            <div class="mt-4 flex items-center justify-between gap-4 text-xs v3-text-muted">
                                <span>Daily <?= (int) $pol['daily_limit'] ?> / Monthly <?= (int) $pol['monthly_limit'] ?> / Hourly <?= (int) $pol['per_hour_limit'] ?></span>
                                <a href="<?= base_url('controlplane/clinics/' . (int) $pol['clinic_id']) ?>" class="inline-flex items-center cp-radius border cp-border cp-surface px-4 py-2 text-sm font-black v3-text-ink v3-border-default v3-hover-soft v3-btn v3-btn--secondary"><?= esc(lang('V3Platform.openClinic')) ?></a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <div id="wa-settings-pane-credentials" class="wa-settings-pane hidden space-y-6">
            <div class="cp-radius border cp-border cp-surface p-6">
                <div class="flex items-start justify-between gap-4 mb-5">
                    <div><h5 class="text-base font-black v3-text-ink"><?= esc(lang('V3Platform.apiCredentials')) ?></h5><p class="mt-1 text-sm v3-text-muted"><?= esc(lang('V3Platform.rotateWhatsappPlatformSecretsHereChangesAffectTheSharedSenderForAllClinics')) ?></p></div>
                    <span class="inline-flex items-center rounded-full v3-bg-soft px-3 py-1 text-[10px] font-black uppercase tracking-[0.16em] v3-text-ink"><?= esc(lang('V3Platform.sensitive')) ?></span>
                </div>
                <div class="space-y-4">
                    <?php foreach (($wa_secrets ?? []) as $s): ?>
                        <div class="cp-radius border cp-border cp-surface p-4">
                            <div class="flex items-center justify-between mb-3"><code class="text-xs font-black cp-accent"><?= esc($s['secret_key']) ?></code><span class="text-[10px] font-black <?= !empty($s['missing']) ? 'v3-text-danger' : 'v3-text-primary' ?>"><?= !empty($s['missing']) ? 'MISSING' : 'READY' ?></span></div>
                            <form action="<?= base_url('controlplane/whatsapp/rotate-secret') ?>" method="post" class="grid grid-cols-1 md:grid-cols-3 gap-3" data-settings-form="true" data-saving-label="Rotating key..."><?= csrf_field() ?><input type="hidden" name="secret_key" value="<?= esc($s['secret_key']) ?>"><div class="relative md:col-span-1"><input type="password" name="new_value" required class="control-settings-field pr-10 text-sm font-mono v3-input" placeholder="<?= esc(lang('V3Platform.pasteNewSecretValue'), 'attr') ?>"><button type="button" onclick="toggleSecretVis(this)" class="absolute right-3 top-2.5 v3-text-muted v3-btn v3-btn--secondary"><i class="fas fa-eye text-sm"></i></button></div><input type="text" name="rotation_note" class="control-settings-field text-sm md:col-span-1 v3-input" placeholder="<?= esc(lang('V3Platform.rotationNote'), 'attr') ?>"><button type="submit" class="control-settings-submit md:col-span-1 v3-btn v3-btn--primary"><?= esc(lang('V3Platform.rotateKey')) ?></button></form>
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
