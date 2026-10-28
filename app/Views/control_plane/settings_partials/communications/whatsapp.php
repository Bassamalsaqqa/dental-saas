
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

<section id="tab-whatsapp-templates" class="settings-content" hidden aria-labelledby="nav-whatsapp-templates">
<nav class="settings-subnav" aria-label="<?= $st('WhatsAppAreas') ?>">
<?php foreach(['overview'=>'Overview','templates'=>'Templates','rollout'=>'Rollout','credentials'=>'Credentials'] as $pane=>$label): ?>
<button type="button" class="button secondary" data-wa-tab-button="<?= esc($pane) ?>" aria-controls="wa-settings-pane-<?= esc($pane) ?>">
<?= $st($label) ?>
</button>
<?php endforeach; ?>
</nav>
<section id="wa-settings-pane-overview" class="wa-settings-pane form-section" hidden>
<header>
<h2>
<?= $st('WhatsApp') ?>
</h2>
</header>
<p>
<?= $st('WAScope') ?>
</p>
<div class="settings-template-facts">
<span>
<?= $st('Configured') ?>
<strong>
<?= $configuredCount ?> / <?= count($waTriggerCatalog) ?>
</strong>
</span>
<span>
<?= $st('Enabled') ?>
<strong>
<?= $enabledCount ?>
</strong>
</span>
<span>
<?= $st('Ready') ?>
<strong>
<?= $readyCount ?>
</strong>
</span>
</div>
<details class="settings-context"><summary><?= $st('OperatingModel') ?></summary><p><?= $st('OperatingModelNote') ?></p></details><a class="button secondary" href="<?= base_url('controlplane/whatsapp') ?>">
<?= $st('Operations') ?>
</a>
</section>
<div id="wa-settings-pane-templates" class="wa-settings-pane" hidden>
<?php $settingOpen('controlplane/settings/whatsapp-templates','Templates'); ?>
<p class="settings-context">
<?= $st('MetaNote') ?>
</p>
<div class="settings-template-layout">
<nav class="settings-library" aria-label="<?= $st('Templates') ?>">
<button class="settings-template-link" type="button" data-wa-template-button="fallback">
<?= $st('Fallback') ?>
</button>
<?php foreach($waTriggerCatalog as $triggerKey=>$triggerMeta): ?>
<button class="settings-template-link" type="button" data-wa-template-button="<?= esc($triggerKey) ?>">
<?= esc(lang('Studio.psTrigger_'.$triggerKey) === 'Studio.psTrigger_'.$triggerKey ? ($triggerMeta['label']??$triggerKey) : lang('Studio.psTrigger_'.$triggerKey)) ?>
</button>
<?php endforeach; ?>
</nav>
<div>
<section id="wa-template-editor-fallback" class="wa-template-editor-pane" hidden>
<h3>
<?= $st('Fallback') ?>
</h3>
<div class="settings-fields">
<div class="form-field settings-wide">
<label for="wa-fallback-name">
<?= $st('MetaName') ?>
</label>
<input id="wa-fallback-name" name="fallback_template.name" value="<?= esc(($waGlobalFallback['name']??'')!==''?$waGlobalFallback['name']:($waGlobalFallback['suggested_name']??'general_reengagement_ar_v1'),'attr') ?>">
</div>
<div class="form-field settings-wide">
<label for="wa-fallback-body">
<?= $st('ReferenceBody') ?>
</label>
<textarea id="wa-fallback-body" name="fallback_template.reference_body_text" rows="7" dir="rtl">
<?= esc($waGlobalFallback['reference_body_text']??'') ?>
</textarea>
</div>
</div>
</section>
<?php foreach($waTriggerCatalog as $triggerKey=>$triggerMeta): ?>
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
<section class="wa-template-editor-pane" id="wa-template-editor-<?= esc($triggerKey) ?>" hidden>
<header>
<h3>
<?= esc(lang('Studio.psTrigger_'.$triggerKey) === 'Studio.psTrigger_'.$triggerKey ? ($triggerMeta['label']??$triggerKey) : lang('Studio.psTrigger_'.$triggerKey)) ?>
</h3>
<p>
<?= esc($triggerMeta['description']??'') ?>
</p>
</header>
<p class="settings-context">
<?= $st(!empty($readiness['ready'])?'Ready':'NeedsConfig') ?>
</p>
<?php if(!empty($readiness['errors'])&&is_array($readiness['errors'])): ?>
<ul class="form-notice">
<?php foreach($readiness['errors'] as $error): ?>
<li>
<?= esc($error) ?>
</li>
<?php endforeach; ?>
</ul>
<?php endif; ?>
<div class="settings-fields">
<div class="form-field ">
<label for="wa-<?= esc($triggerKey) ?>-name">
<?= $st('MetaName') ?>
</label>
<input type="text" id="wa-<?= esc($triggerKey) ?>-name" name="trigger_templates.<?= esc($triggerKey) ?>.name" value="<?= esc($displayTemplateName) ?>" placeholder="<?= esc($suggestedTemplateName) ?>">
</div>
<div class="form-field ">
<label for="wa-<?= esc($triggerKey) ?>-header_param_key">
<?= $st('HeaderKey') ?>
</label>
<input type="text" id="wa-<?= esc($triggerKey) ?>-header_param_key" name="trigger_templates.<?= esc($triggerKey) ?>.header_param_key" value="<?= esc($triggerConfig['header_param_key'] ?? '') ?>">
</div>
<div class="form-field ">
<label for="wa-<?= esc($triggerKey) ?>-reference_header_text">
<?= $st('ReferenceHeader') ?>
</label>
<input type="text" id="wa-<?= esc($triggerKey) ?>-reference_header_text" name="trigger_templates.<?= esc($triggerKey) ?>.reference_header_text" value="<?= esc($referenceHeader) ?>">
</div>
<div class="form-field ">
<label for="wa-<?= esc($triggerKey) ?>-body_param_keys">
<?= $st('BodyKeys') ?>
</label>
<input type="text" id="wa-<?= esc($triggerKey) ?>-body_param_keys" name="trigger_templates.<?= esc($triggerKey) ?>.body_param_keys" value="<?= esc(implode(', ', $bodyParamKeys)) ?>" placeholder="<?= esc(implode(', ', $triggerMeta['body_param_keys'] ?? [])) ?>">
</div>
<div class="form-field settings-wide">
<label for="wa-<?= esc($triggerKey) ?>-reference_body_text">
<?= $st('ReferenceBody') ?>
</label>
<textarea id="wa-<?= esc($triggerKey) ?>-reference_body_text" name="trigger_templates.<?= esc($triggerKey) ?>.reference_body_text" rows="7" dir="rtl">
<?= esc($referenceBody) ?>
</textarea>
</div>
<div class="form-field ">
<label for="wa-<?= esc($triggerKey) ?>-button_type">
<?= $st('ButtonType') ?>
</label>
<select id="wa-<?= esc($triggerKey) ?>-button_type" name="trigger_templates.<?= esc($triggerKey) ?>.button_type">
<option value="" <?= ($triggerConfig['button_type'] ?? '') === '' ? 'selected' : '' ?>>
<?= $st('NoButton') ?>
</option>
<option value="quick_reply" <?= ($triggerConfig['button_type'] ?? '') === 'quick_reply' ? 'selected' : '' ?>>
<?= $st('QuickReply') ?>
</option>
<option value="url" <?= ($triggerConfig['button_type'] ?? '') === 'url' ? 'selected' : '' ?>>
<?= $st('URL') ?>
</option>
</select>
</div>
<div class="form-field ">
<label for="wa-<?= esc($triggerKey) ?>-button_index">
<?= $st('ButtonIndex') ?>
</label>
<input type="text" id="wa-<?= esc($triggerKey) ?>-button_index" name="trigger_templates.<?= esc($triggerKey) ?>.button_index" value="<?= esc($triggerConfig['button_index'] ?? '0') ?>" placeholder="0">
</div>
<div class="form-field ">
<label for="wa-<?= esc($triggerKey) ?>-button_param_key">
<?= $st('ButtonKey') ?>
</label>
<input type="text" id="wa-<?= esc($triggerKey) ?>-button_param_key" name="trigger_templates.<?= esc($triggerKey) ?>.button_param_key" value="<?= esc($triggerConfig['button_param_key'] ?? '') ?>">
</div>
<div class="form-field ">
<label for="wa-<?= esc($triggerKey) ?>-reference_button_text">
<?= $st('ReferenceButton') ?>
</label>
<input type="text" id="wa-<?= esc($triggerKey) ?>-reference_button_text" name="trigger_templates.<?= esc($triggerKey) ?>.reference_button_text" value="<?= esc($referenceButton) ?>">
</div>
</div>
<details class="settings-context">
<summary>
<?= $st('Variables') ?>
</summary>
<p>
<?= esc($triggerMeta['operator_notes']??'') ?>
</p>
<dl>
<?php foreach($availableVars as $varKey=>$help): ?>
<dt>
<code>
<?= esc($varKey) ?>
</code>
</dt>
<dd>
<?= esc($help) ?>
</dd>
<?php endforeach; ?>
</dl>
</details>
</section>
<?php endforeach; ?>
</div>
</div>
<?php $settingClose(); ?>
</div>
<div id="wa-settings-pane-rollout" class="wa-settings-pane" hidden>
<?php $settingOpen('controlplane/settings/whatsapp-templates','Rollout','data-sensitive-settings="true"'); ?>
<p class="settings-context">
<?= $st('RolloutNote') ?>
</p>
<div class="settings-rollout">
<?php foreach($waTriggerCatalog as $triggerKey=>$triggerMeta):$platformEnabled=(int)($waGlobalTriggerToggles[$triggerKey]??($triggerMeta['default_enabled']??1))===1;$readiness=$waTriggerReadiness[$triggerKey]??[]; ?>
<label>
<input type="checkbox" role="switch" class="studio-switch-input" name="global_trigger_toggles[<?= esc($triggerKey) ?>]" value="1" <?= $platformEnabled?'checked':'' ?>>
<span>
<strong>
<?= esc(lang('Studio.psTrigger_'.$triggerKey) === 'Studio.psTrigger_'.$triggerKey ? ($triggerMeta['label']??$triggerKey) : lang('Studio.psTrigger_'.$triggerKey)) ?>
</strong>
<span>
<?= $st(!empty($readiness['ready'])?'Ready':'NeedsConfig') ?>
</span>
</span>
</label>
<?php endforeach; ?>
</div>
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

                    <?php $settingClose(); ?>
<section class="form-section">
<header>
<h2>
<?= $st('ClinicPolicies') ?>
</h2>
</header>
<?php foreach($waPolicies as $pol): ?>
<a class="settings-template-link" href="<?= base_url('controlplane/clinics/'.(int)$pol['clinic_id']) ?>">
<strong>
<?= esc($pol['clinic_name']) ?>
</strong>
<span>
<?= $st(!empty($pol['suspended'])?'Paused':'Active') ?> · <?= $st('Daily') ?> <?= (int)$pol['daily_limit'] ?> / <?= $st('Monthly') ?> <?= (int)$pol['monthly_limit'] ?> / <?= $st('Hourly') ?> <?= (int)$pol['per_hour_limit'] ?>
</span>
</a>
<?php endforeach; ?>
</section>
</div>
<div id="wa-settings-pane-credentials" class="wa-settings-pane" hidden>
<section class="form-section">
<header>
<h2>
<?= $st('Credentials') ?>
</h2>
</header>
<p class="settings-context">
<?= $st('SecretNote') ?>
</p>
<?php foreach($wa_secrets??[] as $secret): ?>
<form class="studio-form settings-credential" action="<?= base_url('controlplane/whatsapp/rotate-secret') ?>" method="post" data-settings-form="true" data-sensitive-settings="true">
<?= csrf_field() ?>
<input type="hidden" name="secret_key" value="<?= esc($secret['secret_key'],'attr') ?>">
<h3>
<code>
<?= esc($secret['secret_key']) ?>
</code>
<span>
<?= $st(!empty($secret['missing'])?'Missing':'Stored') ?>
</span>
</h3>
<div class="settings-fields">
<div class="form-field">
<label for="secret-<?= esc($secret['secret_key']) ?>">
<?= $st('NewSecret') ?>
</label>
<input id="secret-<?= esc($secret['secret_key']) ?>" type="password" name="new_value" autocomplete="new-password" required>
<label class="settings-checkbox">
<input type="checkbox" data-secret-show>
<?= $st('ShowSecret') ?>
</label>
</div>
<div class="form-field">
<label for="note-<?= esc($secret['secret_key']) ?>">
<?= $st('RotationNote') ?>
</label>
<input id="note-<?= esc($secret['secret_key']) ?>" name="rotation_note">
</div>
</div>
<button class="button secondary" type="submit">
<?= $st('Rotate') ?>
</button>
</form>
<?php endforeach; ?>
</section>
</div>
</section>