<?php helper('asset');$wm=static fn($key)=>esc(lang('Studio.wo'.$key)); ?>
<?= $this->extend('layouts/studio_control_plane') ?>
<?= $this->section('styles') ?>
<link rel="stylesheet" href="<?= base_url('assets/css/studio-forms.css') ?>?v=<?= esc(asset_version('assets/css/studio-forms.css')) ?>">
<link rel="stylesheet" href="<?= base_url('assets/css/studio-whatsapp-ops.css') ?>?v=<?= esc(asset_version('assets/css/studio-whatsapp-ops.css')) ?>">
<?= $this->endSection() ?>
<?= $this->section('content') ?>
<?php
$stats = $stats ?? [];
$failureRate = (float) ($stats['failure_rate_24h'] ?? 0);
$statusBreakdown = is_array($status_breakdown ?? null) ? $status_breakdown : [];
$recentStream = is_array($recent_stream ?? null) ? $recent_stream : [];
$needsAttention = is_array($needs_attention ?? null) ? $needs_attention : [];
$recentAttempts = is_array($recent_attempts ?? null) ? $recent_attempts : [];
$recentEvents = is_array($recent_events ?? null) ? $recent_events : [];
$recentOptouts = is_array($recent_optouts ?? null) ? $recent_optouts : [];
$operatorGuidance = is_array($operator_guidance ?? null) ? $operator_guidance : [];
$triggerCatalog = is_array($trigger_catalog ?? null) ? $trigger_catalog : [];
$testSendClinics = is_array($test_send_clinics ?? null) ? $test_send_clinics : [];
$sendMode=(string)old('send_mode','text');$templateSource=(string)old('template_source','explicit');

$initialTab = 'overview';
if (old('send_mode') !== null || old('target_number') !== null || old('clinic_id') !== null) {
    $initialTab = 'send';
} elseif (is_string(session('success')) && str_contains(session('success'), 'WhatsApp test send')) {
    $initialTab = 'activity';
}

$formatCount = static fn ($value): string => number_format((int) ($value ?? 0));
$formatPercent = static fn ($value): string => number_format((float) ($value ?? 0), 1) . '%';
$formatDate = static function ($value): string {
    if (empty($value)) {
        return '-';
    }

    try {
        return (new DateTime((string) $value))->format('Y-m-d H:i');
    } catch (Throwable) {
        return (string) $value;
    }
};



$guidanceText = static function ($item): string {
    if (is_array($item)) {
        $title = trim((string) ($item['title'] ?? $item['label'] ?? $item['name'] ?? ''));
        $message = trim((string) ($item['message'] ?? $item['description'] ?? $item['body'] ?? ''));
        return trim($title . ($title !== '' && $message !== '' ? ': ' : '') . $message);
    }

    if (is_object($item)) {
        $vars = get_object_vars($item);
        $title = trim((string) ($vars['title'] ?? $vars['label'] ?? $vars['name'] ?? ''));
        $message = trim((string) ($vars['message'] ?? $vars['description'] ?? $vars['body'] ?? ''));
        return trim($title . ($title !== '' && $message !== '' ? ': ' : '') . $message);
    }

    return trim((string) $item);
};
?>


<header class="page-title-row">
<h1>
<?= $wm('Title') ?>
</h1>
<a class="button secondary" href="<?= base_url('controlplane/settings?tab=whatsapp-templates') ?>">
<?= $wm('Settings') ?>
</a>
</header>
<?php foreach(['error','success'] as $kind):if(session($kind)): ?>
<p class="form-notice <?= $kind==='error'?'form-notice--error':'' ?>" role="status">
<?= esc(session($kind)) ?>
</p>
<?php endif;endforeach; ?>
<?php if(session('errors')): ?>
<ul class="form-notice form-notice--error" role="alert">
<?php foreach((array)session('errors') as $error): ?>
<li>
<?= esc($error) ?>
</li>
<?php endforeach; ?>
</ul>
<?php endif; ?>
<nav class="messaging-tabs" aria-label="<?= $wm('Areas') ?>">
<?php foreach(['overview'=>'Overview','activity'=>'Activity','webhooks'=>'Webhooks','optouts'=>'Optouts','send'=>'Send','reference'=>'Reference'] as $tab=>$label): ?>
<button type="button" class="button secondary" data-message-tab="<?= $tab ?>" aria-controls="pane-<?= $tab ?>">
<?= $wm($label) ?>
</button>
<?php endforeach; ?>
</nav>
<section id="pane-overview" class="messaging-pane" data-message-pane hidden>
<div class="messaging-facts">
<?php foreach(['attempts_24h_total'=>'Attempts24','attempts_24h_sentish'=>'Sent24','attempts_24h_failed'=>'Failed24','attempts_24h_read'=>'Read24','events_24h'=>'Events24','optouts_24h'=>'Optouts24'] as $field=>$label): ?>
<span>
<?= $wm($label) ?>
<strong>
<?= esc($formatCount($stats[$field]??0)) ?>
</strong>
</span>
<?php endforeach; ?>
<span>
<?= $wm('FailureRate') ?>
<strong>
<?= esc($formatPercent($failureRate)) ?>
</strong>
</span>
</div>
<div class="messaging-overview-grid">
<section class="form-section messaging-attention">
<header>
<h2>
<?= $wm('Attention') ?>
</h2>
<span>
<?= count($needsAttention) ?>
</span>
</header>
<?php if(!$needsAttention): ?>
<p>
<?= $wm('NoAttention') ?>
</p>
<?php endif; ?>
<?php foreach($needsAttention as $warning):$content=is_array($warning['content']??null)?$warning['content']:[];$recipient=is_array($warning['recipient']??null)?$warning['recipient']:[];$failure=is_array($warning['failure']??null)?$warning['failure']:[]; ?>
<article>
<h3>
<?= esc($content['title']??lang('Studio.woAttention')) ?>
</h3>
<p>
<?= esc($warning['clinic_name']??'') ?> · <?= esc($recipient['label']??'') ?>
</p>
<p>
<?= esc($failure['detail']??$failure['title']??$warning['error_message']??$warning['failure_reason']??'') ?>
</p>
<button class="button secondary" type="button" data-message-go="activity">
<?= $wm('ReviewAttempts') ?>
</button>
</article>
<?php endforeach; ?>
</section>
<section class="form-section">
<header>
<h2>
<?= $wm('RecentStream') ?>
</h2>
<button class="button secondary" type="button" data-message-go="activity">
<?= $wm('Activity') ?>
</button>
</header>
<?php if(!$recentStream): ?>
<p>
<?= $wm('NoActivity') ?>
</p>
<?php endif; ?>
<?php foreach(array_slice($recentStream,0,8) as $item):$content=is_array($item['content']??null)?$item['content']:[];$recipient=is_array($item['recipient']??null)?$item['recipient']:[];$statusMeta=is_array($item['status_meta']??null)?$item['status_meta']:[]; ?>
<article class="messaging-stream-item">
<strong>
<?= esc($content['title']??lang('Studio.woAttempt')) ?>
</strong>
<span>
<?= esc($statusMeta['label']??ucfirst((string)($item['effective_status']??'pending'))) ?>
</span>
<p>
<?= esc(trim((string)($recipient['label']??'').' · '.(string)($content['preview']??''))) ?>
</p>
<time>
<?= esc($item['created_label']??$formatDate($item['created_at']??null)) ?>
</time>
</article>
<?php endforeach; ?>
</section>
</div>
<section class="form-section">
<header>
<h2>
<?= $wm('DeliveryMix') ?>
</h2>
</header>
<?php if(!$statusBreakdown): ?>
<p>
<?= $wm('NoActivity') ?>
</p>
<?php else: ?>
<dl class="messaging-mix">
<?php foreach($statusBreakdown as $label=>$count): ?>
<div>
<dt>
<?= esc(ucfirst($label)) ?>
</dt>
<dd>
<?= esc($formatCount($count)) ?>
</dd>
</div>
<?php endforeach; ?>
</dl>
<?php endif; ?>
</section>
</section>
<section id="pane-activity" class="messaging-pane form-section" data-message-pane hidden>
<header>
<h2>
<?= $wm('Activity') ?>
</h2>
<span>
<?= count($recentAttempts) ?> <?= $wm('Loaded') ?>
</span>
</header>
<?php if(!$recentAttempts): ?>
<p>
<?= $wm('NoActivity') ?>
</p>
<?php else: ?>
<table class="studio-grid" data-grid-widths="13,18,18,31,20" aria-label="<?= $wm('Activity') ?>">
<thead>
<tr>
<?php foreach(['Status','Clinic','Recipient','Content','Delivery'] as $label): ?>
<th scope="col">
<?= $wm($label) ?>
</th>
<?php endforeach; ?>
</tr>
</thead>
<tbody>
<?php foreach($recentAttempts as $index=>$attempt):$content=is_array($attempt['content']??null)?$attempt['content']:[];$recipient=is_array($attempt['recipient']??null)?$attempt['recipient']:[];$delivery=is_array($attempt['delivery_meta']??null)?$attempt['delivery_meta']:[];$statusMeta=is_array($attempt['status_meta']??null)?$attempt['status_meta']:[]; ?>
<tr data-message-dialog="attempt-<?= $index ?>">
<td>
<button class="message-record-button" type="button" data-dialog="attempt-<?= $index ?>">
<?= esc($statusMeta['label']??$attempt['effective_status']??'pending') ?>
</button>
</td>
<td>
<?= esc($attempt['clinic_name']??'—') ?>
<small>#<?= esc($attempt['attempt_id']??'—') ?>
</small>
</td>
<td>
<?= esc($recipient['label']??'—') ?>
<small>
<?= esc($recipient['secondary']??'') ?>
</small>
</td>
<td>
<?= esc($content['title']??'—') ?>
<small>
<?= esc($content['preview']??'') ?>
</small>
</td>
<td>
<?= esc($attempt['created_label']??$formatDate($attempt['created_at']??null)) ?>
<small>
<?= esc($delivery['label']??('Notification #'.($attempt['notification_id']??'—'))) ?>
</small>
</td>
</tr>
<?php endforeach; ?>
</tbody>
</table>
<?php endif; ?>
</section>
<section id="pane-webhooks" class="messaging-pane form-section" data-message-pane hidden>
<header>
<h2>
<?= $wm('Webhooks') ?>
</h2>
<span>
<?= count($recentEvents) ?> <?= $wm('Loaded') ?>
</span>
</header>
<?php if(!$recentEvents): ?>
<p>
<?= $wm('NoEvents') ?>
</p>
<?php endif; ?>
<?php foreach($recentEvents as $index=>$event):$summary=is_array($event['summary']??null)?$event['summary']:[]; ?>
<article class="messaging-webhook">
<h3>
<?= esc($summary['title']??$event['event_type']??'Webhook') ?>
</h3>
<p>
<?= esc($summary['detail']??'') ?>
</p>
<dl>
<dt>
<?= $wm('Type') ?>
</dt>
<dd>
<?= esc($event['event_type']??'webhook') ?>
</dd>
<dt>
<?= $wm('Subject') ?>
</dt>
<dd>
<?= esc($summary['subject']??'—') ?>
</dd>
<dt>
<?= $wm('Code') ?>
</dt>
<dd>
<?= esc($summary['code']??'—') ?>
</dd>
<dt>
<?= $wm('Processed') ?>
</dt>
<dd>
<?= esc($event['processed_label']??$formatDate($event['processed_at']??null)) ?>
</dd>
</dl>
<details>
<summary>
<?= $wm('RawPayload') ?>
</summary>
<button class="button secondary" type="button" data-copy-payload="payload-<?= $index ?>">
<?= $wm('Copy') ?>
</button>
<pre id="payload-<?= $index ?>">
<?= esc((string)($event['payload_pretty']??$event['payload_json']??'{}')) ?>
</pre>
</details>
</article>
<?php endforeach; ?>
</section>
<section id="pane-optouts" class="messaging-pane form-section" data-message-pane hidden>
<header>
<h2>
<?= $wm('Optouts') ?>
</h2>
</header>
<?php if(!$recentOptouts): ?>
<p>
<?= $wm('NoOptouts') ?>
</p>
<?php else: ?>
<table class="studio-grid" data-grid-widths="28,23,29,20" aria-label="<?= $wm('Optouts') ?>">
<thead>
<tr>
<?php foreach(['Clinic','Phone','Identity','Recorded'] as $label): ?>
<th scope="col">
<?= $wm($label) ?>
</th>
<?php endforeach; ?>
</tr>
</thead>
<tbody>
<?php foreach($recentOptouts as $optout): ?>
<tr>
<td>
<?= esc($optout['clinic_name']??'—') ?>
</td>
<td>
<?= esc($optout['wa_number']??'—') ?>
</td>
<td>
<?= esc(trim((string)($optout['bsuid']??'').' '.(string)($optout['parent_bsuid']??''))?:'—') ?>
</td>
<td>
<?= esc($optout['created_label']??$formatDate($optout['created_at']??null)) ?>
</td>
</tr>
<?php endforeach; ?>
</tbody>
</table>
<?php endif; ?>
</section>
<section id="pane-send" class="messaging-pane" data-message-pane hidden>
<div class="messaging-send-layout">
<form class="studio-form form-section" id="messaging-test-form" method="post" action="<?= base_url('controlplane/whatsapp/send-test') ?>">
<?= csrf_field() ?>
<header>
<h2>
<?= $wm('Send') ?>
</h2>
</header>
<p class="form-notice">
<?= $wm('SendWarning') ?>
</p><?php if(!$testSendClinics): ?><p class="form-notice" role="status"><?= $wm('NoEligibleClinic') ?></p><?php endif; ?>
<div class="messaging-fields">
<div class="form-field ">
<label for="message-clinic_id">
<?= $wm('Clinic') ?>
</label>
<select id="message-clinic_id" name="clinic_id" required>
                                <option value="">
<?= $wm('SelectClinic') ?>
</option>
                                <?php foreach ($testSendClinics as $clinic): ?>
                                    <?php $clinicId = (string) ($clinic['clinic_id'] ?? $clinic['id'] ?? ''); ?>
                                    <option value="<?= esc($clinicId, 'attr') ?>" <?= (string) old('clinic_id') === $clinicId ? 'selected' : '' ?>>
                                        <?= esc($clinic['clinic_name'] ?? ('Clinic #' . $clinicId)) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
</div>
<div class="form-field ">
<label for="message-target_number">
<?= $wm('Target') ?>
</label>
<input type="text" id="message-target_number" name="target_number" value="<?= esc((string) old('target_number'), 'attr') ?>" placeholder="+970..." required>
</div>
<div class="form-field ">
<label for="message-send_mode">
<?= $wm('SendMode') ?>
</label>
<select id="message-send_mode" name="send_mode">
                                <?php $sendMode = (string) old('send_mode', 'text'); ?>
                                <option value="text" <?= $sendMode === 'text' ? 'selected' : '' ?>>
<?= $wm('FreeText') ?>
</option>
                                <option value="template" <?= $sendMode === 'template' ? 'selected' : '' ?>>
<?= $wm('Template') ?>
</option>
                            </select>
</div>
<div class="form-field " data-send-group="template">
<label for="message-template_source">
<?= $wm('TemplateSource') ?>
</label>
<select id="message-template_source" name="template_source">
                                <option value="explicit" <?= $templateSource === 'explicit' ? 'selected' : '' ?>>
<?= $wm('Explicit') ?>
</option>
                                <option value="fallback" <?= $templateSource === 'fallback' ? 'selected' : '' ?>>
<?= $wm('Fallback') ?>
</option>
                                <option value="trigger_template" <?= $templateSource === 'trigger_template' ? 'selected' : '' ?>>
<?= $wm('Trigger') ?>
</option>
                            </select>
</div>
<div class="form-field messaging-wide" data-send-group="text">
<label for="message-message">
<?= $wm('Message') ?>
</label>
<textarea id="message-message" name="message" rows="5">
<?= esc((string) old('message')) ?>
</textarea>
</div>
<div class="form-field " data-send-group="template" data-template-choice="manual">
<label for="message-template_name">
<?= $wm('TemplateName') ?>
</label>
<input type="text" id="message-template_name" name="template_name" value="<?= esc((string) old('template_name'), 'attr') ?>" placeholder="appointment_reminder">
</div>
<div class="form-field " data-send-group="template" data-template-choice="manual">
<label for="message-template_language">
<?= $wm('TemplateLanguage') ?>
</label>
<input type="text" id="message-template_language" name="template_language" value="<?= esc((string) old('template_language', 'en_US'), 'attr') ?>" placeholder="en_US">
</div>
<div class="form-field " data-send-group="template" data-template-choice="trigger">
<label for="message-trigger_key">
<?= $wm('Trigger') ?>
</label>
<select id="message-trigger_key" name="trigger_key">
                                <option value="">
<?= $wm('SelectTrigger') ?>
</option>
                                <?php foreach ($triggerCatalog as $key => $trigger): ?>
                                    <option value="<?= esc((string) $key, 'attr') ?>" <?= (string) old('trigger_key') === (string) $key ? 'selected' : '' ?>>
                                        <?= esc(lang('Studio.psTrigger_'.$key)==='Studio.psTrigger_'.$key?($trigger['label']??$key):lang('Studio.psTrigger_'.$key)) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
</div>
<div class="form-field messaging-wide" data-send-group="template">
<label for="message-template_components_json">
<?= $wm('Components') ?>
</label>
<textarea id="message-template_components_json" name="template_components_json" rows="5" placeholder='[{"type":"body","parameters":[...]}]'>
<?= esc((string) old('template_components_json')) ?>
</textarea>
</div>
<div class="form-field " data-send-group="template">
<label for="message-template_header_text">
<?= $wm('Header') ?>
</label>
<input type="text" id="message-template_header_text" name="template_header_text" value="<?= esc((string) old('template_header_text'), 'attr') ?>">
</div>
<div class="form-field " data-send-group="template">
<label for="message-template_body_params">
<?= $wm('BodyParams') ?>
</label>
<input type="text" id="message-template_body_params" name="template_body_params" value="<?= esc((string) old('template_body_params'), 'attr') ?>">
</div>
<div class="form-field " data-send-group="template">
<label for="message-template_button_type">
<?= $wm('ButtonType') ?>
</label>
<input type="text" id="message-template_button_type" name="template_button_type" value="<?= esc((string) old('template_button_type'), 'attr') ?>" placeholder="url">
</div>
<div class="form-field " data-send-group="template">
<label for="message-template_button_index">
<?= $wm('ButtonIndex') ?>
</label>
<input type="text" id="message-template_button_index" name="template_button_index" value="<?= esc((string) old('template_button_index'), 'attr') ?>" placeholder="0">
</div>
<div class="form-field " data-send-group="template">
<label for="message-template_button_value">
<?= $wm('ButtonValue') ?>
</label>
<input type="text" id="message-template_button_value" name="template_button_value" value="<?= esc((string) old('template_button_value'), 'attr') ?>">
</div>
</div>
<div class="messaging-actions">
<a class="button secondary" href="<?= base_url('controlplane/whatsapp') ?>">
<?= $wm('Reset') ?>
</a>
<button class="button primary" type="submit" <?= empty($testSendClinics)?'disabled':'' ?>>
<?= $wm('ReviewSend') ?>
</button>
</div>
</form>
<aside class="form-section">
<h2>
<?= $wm('OperatorRules') ?>
</h2>
<p>
<?= $wm('DeliveryRules') ?>
</p>
<a class="button secondary" href="<?= base_url('controlplane/settings?tab=whatsapp-templates') ?>">
<?= $wm('Settings') ?>
</a>
<div class="messaging-trigger-summary" hidden>
<h3 data-trigger-title>
</h3>
<p data-trigger-description>
</p>
<ul data-trigger-variables>
</ul>
</div>
</aside>
</div>
</section>
<section id="pane-reference" class="messaging-pane form-section" data-message-pane hidden>
<header>
<h2>
<?= $wm('Reference') ?>
</h2>
</header>
<p>
<?= $wm('WorkflowOwnership') ?>
</p>
<p>
<?= $wm('DeliveryRules') ?>
</p>
<?php foreach($operatorGuidance as $item):$line=$guidanceText($item);if($line!==''): ?>
<p class="messaging-guidance">
<?= esc($line) ?>
</p>
<?php endif;endforeach; ?>
</section>
<?php foreach($recentAttempts as $index=>$attempt):$content=is_array($attempt['content']??null)?$attempt['content']:[];$recipient=is_array($attempt['recipient']??null)?$attempt['recipient']:[];$delivery=is_array($attempt['delivery_meta']??null)?$attempt['delivery_meta']:[];$failure=is_array($attempt['failure']??null)?$attempt['failure']:[]; ?>
<dialog class="messaging-dialog" id="attempt-<?= $index ?>" aria-labelledby="attempt-title-<?= $index ?>">
<header>
<h2 id="attempt-title-<?= $index ?>">
<?= $wm('Attempt') ?> #<?= esc($attempt['attempt_id']??'—') ?>
</h2>
<button type="button" class="button secondary" data-close>
<?= $wm('Close') ?>
</button>
</header>
<dl>
<?php foreach(['Clinic'=>$attempt['clinic_name']??'—','Recipient'=>trim(($recipient['label']??'').' '.($recipient['secondary']??'')),'Status'=>$attempt['status_meta']['label']??$attempt['effective_status']??'pending','Content'=>trim(($content['title']??'').'
'.($content['subtitle']??'').'
'.($content['preview']??'')),'Delivery'=>$delivery['label']??('Notification #'.($attempt['notification_id']??'—')),'ProviderId'=>$attempt['provider_message_id']??'—','Recorded'=>$attempt['created_label']??$formatDate($attempt['created_at']??null),'Failure'=>$failure['detail']??$failure['title']??'—'] as $key=>$value): ?>
<dt>
<?= $wm($key) ?>
</dt>
<dd>
<?= esc($value) ?>
</dd>
<?php endforeach; ?>
</dl>
</dialog>
<?php endforeach; ?>
<dialog id="message-send-review" class="messaging-dialog" aria-labelledby="message-send-title">
<h2 id="message-send-title">
<?= $wm('ReviewSend') ?>
</h2>
<p>
<?= $wm('SendWarning') ?>
</p>
<dl data-send-values>
</dl>
<div class="messaging-actions">
<button class="button secondary" type="button" data-send-cancel>
<?= $wm('KeepEditing') ?>
</button>
<button class="button primary" type="button" data-send-confirm>
<?= $wm('ConfirmSend') ?>
</button>
</div>
</dialog>
<p id="messaging-feedback" role="status" class="form-notice" hidden>
</p>
<script id="messaging-ui" type="application/json">
<?= json_encode(['initial'=>$initialTab,'catalog'=>array_map(static fn($trigger)=>['label'=>$trigger['label']??'','description'=>$trigger['description']??'','required_variables'=>$trigger['required_variables']??[]],$triggerCatalog),'copied'=>lang('Studio.woCopied'),'copyFailed'=>lang('Studio.woCopyFailed')],JSON_HEX_TAG|JSON_HEX_AMP|JSON_HEX_APOS|JSON_HEX_QUOT|JSON_UNESCAPED_UNICODE) ?>
</script>
<script src="<?= base_url('assets/js/studio-whatsapp-ops.js') ?>?v=<?= esc(asset_version('assets/js/studio-whatsapp-ops.js')) ?>" defer>
</script>
<?= $this->endSection() ?>