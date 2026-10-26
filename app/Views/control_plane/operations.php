<?php helper('asset');include APPPATH.'Views/layouts/partials/studio_icons.php';
$healthChecks = is_array($healthChecks ?? null) ? $healthChecks : [];
$planAudits = is_array($planAudits ?? null) ? $planAudits : [];
$clinicDirectory = is_array($clinicDirectory ?? null) ? $clinicDirectory : [];
$healthyCount = count(array_filter($healthChecks, static fn ($check): bool => !empty($check['status'])));
$attentionCount = max(0, count($healthChecks) - $healthyCount);
$environmentLabel = (string) ($environment ?? ENVIRONMENT);
$phpVersionLabel = (string) ($phpVersion ?? PHP_VERSION);
$opsTab=service('request')->getGet('tab')==='audit'?'audit':'health';$healthLabels=['Database'=>'platformDatabase','Writable Root'=>'platformWritable','Uploads'=>'platformUploads','Sessions'=>'platformSessions','Cache'=>'platformCache'];$healthDetails=['Connected'=>'platformConnected','Writable'=>'platformWritableOk','Not writable'=>'platformNotWritable','Missing directory'=>'platformMissingDirectory']; ?>
<?= $this->extend('layouts/studio_control_plane') ?>
<?= $this->section('styles') ?>
<link rel="stylesheet" href="<?= base_url('assets/css/studio-forms.css') ?>?v=<?= esc(asset_version('assets/css/studio-forms.css')) ?>">
<link rel="stylesheet" href="<?= base_url('assets/css/studio-platform-operations.css') ?>?v=<?= esc(asset_version('assets/css/studio-platform-operations.css')) ?>">
<?= $this->endSection() ?>
<?= $this->section('content') ?>
<header class="page-title-row">
<h1>
<?= esc(lang('UI.cpOperations')) ?>
</h1>
<a class="button secondary" href="<?= base_url('controlplane/settings') ?>">
<?= esc(lang('UI.cpSettings')) ?>
</a>
</header>
<section class="platform-task-counts" aria-label="<?= esc(lang('Studio.platformRuntime'),'attr') ?>">
<div>
<span>
<?= esc(lang('Studio.platformPassing')) ?>
</span>
<strong>
<?= $healthyCount ?>
</strong>
</div>
<div>
<span>
<?= esc(lang('Studio.platformAttention')) ?>
</span>
<strong>
<?= $attentionCount ?>
</strong>
</div>
<div>
<span>
<?= esc(lang('Studio.platformEnvironment')) ?>
</span>
<strong>
<?= esc($environmentLabel) ?>
</strong>
</div>
<div>
<span>PHP</span>
<strong dir="ltr">
<?= esc($phpVersionLabel) ?>
</strong>
</div>
</section>
<nav class="platform-task-actions platform-ops-tabs" aria-label="<?= esc(lang('UI.cpOperations'),'attr') ?>">
<a class="button <?= $opsTab==='health'?'primary':'secondary' ?>" href="<?= base_url('controlplane/operations?tab=health') ?>" <?= $opsTab==='health'?'aria-current="page"':'' ?>>
<?= esc(lang('Studio.platformRuntime')) ?>
</a>
<a class="button <?= $opsTab==='audit'?'primary':'secondary' ?>" href="<?= base_url('controlplane/operations?tab=audit') ?>" <?= $opsTab==='audit'?'aria-current="page"':'' ?>>
<?= esc(lang('Studio.platformPlanEnforcement')) ?>
</a>
</nav>
<?php if($opsTab==='health'): ?>
<div class="platform-ops-layout">
<section class="form-section">
<header>
<h2>
<?= esc(lang('Studio.platformRuntime')) ?>
</h2>
</header>
<div class="platform-health-list">
<?php foreach($healthChecks as $check):$ok=!empty($check['status']);$label=(string)($check['label']??'');$detail=(string)($check['detail']??'');$probeUnavailable=!$ok&&str_contains($detail,'Call to undefined method'); ?>
<article class="platform-health-item <?= $ok?'':'platform-health-item--attention' ?>">
<span class="platform-health-symbol">
<?= $studioIcon($ok?'check':'help') ?>
</span>
<div>
<h3>
<?= esc(isset($healthLabels[$label])?lang('Studio.'.$healthLabels[$label]):$label) ?>
</h3>
<?php if($probeUnavailable): ?>
<p>
<?= esc(lang('Studio.platformProbeUnavailable')) ?>
</p>
<details class="platform-probe-detail">
<summary>
<?= esc(lang('Studio.platformTechnicalDetail')) ?>
</summary>
<p>
<?= esc($detail) ?>
</p>
</details>
<?php else: ?>
<p>
<?= esc(isset($healthDetails[$detail])?lang('Studio.'.$healthDetails[$detail]):$detail) ?>
</p>
<?php endif; ?>
</div>
<strong>
<?= esc(lang('Studio.'.($ok?'platformCheckPassed':'platformCheckFailed'))) ?>
</strong>
</article>
<?php endforeach; ?>
</div>
</section>
<section class="form-section">
<header>
<h2>
<?= esc(lang('Studio.platformTools')) ?>
</h2>
</header>
<nav class="platform-tool-links">
<?php foreach([['controlplane/onboarding/clinic/create','UI.cpOnboarding'],['controlplane/clinics','UI.cpClinics'],['controlplane/plan-requests','UI.cpRequests'],['controlplane/activity-log','UI.cpSystemActivity'],['controlplane/settings','UI.cpSettings'],['controlplane/settings?tab=whatsapp-templates','Studio.platformWhatsAppPolicies']] as [$url,$label]): ?>
<a href="<?= base_url($url) ?>">
<?= esc(lang($label)) ?>
</a>
<?php endforeach; ?>
</nav>
<details class="platform-maintenance">
<summary>
<?= esc(lang('Studio.platformMaintenance')) ?>
</summary>
<p>
<?= esc(lang('Studio.platformMaintenanceHelp')) ?>
</p>
<nav class="platform-tool-links">
<a href="<?= base_url('rbac/status') ?>">
<?= esc(lang('Studio.platformRbacStatus')) ?>
</a>
<?php foreach([['rbac/sync','platformSyncPermissions'],['rbac/init','platformInitializeRbac'],['rbac/setup','platformRbacSetup']] as [$url,$key]): ?>
<a href="<?= base_url($url) ?>" data-review-maintenance>
<?= esc(lang('Studio.'.$key)) ?>
</a>
<?php endforeach; ?>
</nav>
</details>
</section>
</div>
<?php else: ?>
<section class="form-section">
<header class="platform-task-actions">
<h2>
<?= esc(lang('Studio.platformPlanEnforcement')) ?>
</h2>
<span class="platform-request-status">
<?= count($planAudits) ?> <?= esc(lang('Studio.platformLoadedEvents')) ?>
</span>
</header>
<div class="form-field platform-audit-search">
<label for="planEnforcementSearch">
<?= esc(lang('Studio.platformSearchEnforcement')) ?>
</label>
<input id="planEnforcementSearch" type="search">
</div>
<table class="studio-grid" id="planEnforcementTable" data-grid-widths="20,28,23,29" aria-label="<?= esc(lang('Studio.platformPlanEnforcement'),'attr') ?>">
<thead>
<tr>
<?php foreach(['platformTimestamp','platformClinic','platformBlockedAction','platformReasonCode'] as $key): ?>
<th scope="col">
<?= esc(lang('Studio.'.$key)) ?>
</th>
<?php endforeach; ?>
</tr>
</thead>
<tbody>
<?php foreach($planAudits as $audit):$clinic=$clinicDirectory[$audit['clinic_id']]??null; ?>
<tr class="plan-enforcement-row">
<td>
<?= esc($audit['created_at']??'') ?>
</td>
<td>
<a class="platform-audit-clinic" href="<?= base_url('controlplane/clinics/'.(int)($audit['clinic_id']??0)) ?>">
<?= esc($clinic['name']??('#'.(int)($audit['clinic_id']??0))) ?>
</a>
<small>
<?= esc($clinic['slug']??'') ?> · #<?= (int)($audit['clinic_id']??0) ?>
</small>
</td>
<td>
<?= esc($audit['action_key']??'') ?>
</td>
<td>
<?= esc($audit['reason_code']??'') ?>
</td>
</tr>
<?php endforeach; ?>
</tbody>
</table>
<p class="platform-audit-empty" <?= $planAudits?'hidden':'' ?>>
<?= esc(lang('Studio.platformNoEnforcement')) ?>
</p>
<p class="platform-audit-search-empty" hidden>
<?= esc(lang('Studio.emptyList')) ?>
</p>
<p class="platform-ops-scope">
<?= esc(lang('Studio.platformAuditScope')) ?>
</p>
</section>
<?php endif; ?>
<dialog id="platform-maintenance-confirm" class="help-dialog" aria-labelledby="maintenance-confirm-title">
<header>
<h2 id="maintenance-confirm-title">
<?= esc(lang('Studio.platformMaintenance')) ?>
</h2>
<button class="icon-button" type="button" data-close aria-label="<?= esc(lang('Studio.close'),'attr') ?>">
<?= $studioIcon('close') ?>
</button>
</header>
<div class="platform-maintenance-warning">
<h3>
</h3>
<p>
<?= esc(lang('Studio.platformMaintenanceConfirm')) ?>
</p>
</div>
<footer class="platform-task-actions">
<button class="button secondary" type="button" data-close>
<?= esc(lang('Studio.cancel')) ?>
</button>
<a class="button primary" data-maintenance-proceed href="#">
<?= esc(lang('Studio.confirm')) ?>
</a>
</footer>
</dialog>
<?= $this->endSection() ?>
<?= $this->section('scripts') ?>
<script>
(()=>{'use strict';const search=document.getElementById('planEnforcementSearch'),rows=[...document.querySelectorAll('.plan-enforcement-row')];search?.addEventListener('input',()=>{const q=search.value.trim().toLowerCase();rows.forEach(row=>row.hidden=!row.textContent.toLowerCase().includes(q));document.querySelector('.platform-audit-search-empty').hidden=!rows.length||rows.some(row=>!row.hidden);});document.querySelectorAll('.plan-enforcement-row').forEach(row=>row.addEventListener('click',e=>{if(e.defaultPrevented||e.button!==0||e.target.closest('a,button,input,select,textarea,label')||String(getSelection()).length)return;const link=row.querySelector('.platform-audit-clinic');if(e.ctrlKey||e.metaKey)window.open(link.href,'_blank','noopener');else location.href=link.href;}));const dialog=document.getElementById('platform-maintenance-confirm');let opener;dialog.addEventListener('close',()=>opener?.focus());document.querySelectorAll('[data-review-maintenance]').forEach(link=>link.addEventListener('click',e=>{if(e.ctrlKey||e.metaKey||e.shiftKey||e.altKey)return;e.preventDefault();opener=link;dialog.querySelector('h3').textContent=link.textContent;dialog.querySelector('[data-maintenance-proceed]').href=link.href;dialog.showModal();}));})();
</script>
<?= $this->endSection() ?>
