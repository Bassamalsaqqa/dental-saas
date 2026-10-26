<?php helper('asset');include APPPATH.'Views/layouts/partials/studio_icons.php';$requestFilters=is_array($requestFilters??null)?$requestFilters:[];$requestStats=is_array($requestStats??null)?$requestStats:[];$planRequests=is_array($planRequests??null)?$planRequests:[];$requestStatus=static function($value){$key='Studio.platformStatus_'.(string)$value;$label=lang($key);return $label===$key?(string)$value:$label;}; ?>
<?= $this->extend('layouts/studio_control_plane') ?>
<?= $this->section('styles') ?>
<link rel="stylesheet" href="<?= base_url('assets/css/studio-forms.css') ?>?v=<?= esc(asset_version('assets/css/studio-forms.css')) ?>">
<link rel="stylesheet" href="<?= base_url('assets/css/studio-platform-operations.css') ?>?v=<?= esc(asset_version('assets/css/studio-platform-operations.css')) ?>">
<?= $this->endSection() ?>
<?= $this->section('content') ?>
<header class="page-title-row">
<h1>
<?= esc(lang('UI.cpRequests')) ?>
</h1>
<nav class="platform-task-actions">
<a class="button secondary" href="<?= base_url('controlplane/plans') ?>">
<?= esc(lang('UI.cpPlans')) ?>
</a>
<button class="button primary" type="button" data-queue-request>
<?= $studioIcon('plus') ?>
<?= esc(lang('Studio.platformQueueRequest')) ?>
</button>
</nav>
</header>
<?php if(session('error')): ?>
<p class="form-notice form-notice--error" role="alert">
<?= esc(session('error')) ?>
</p>
<?php endif; ?>
<?php if(session('success')): ?>
<p class="form-notice" role="status">
<?= esc(session('success')) ?>
</p>
<?php endif; ?>
<section class="platform-task-counts" aria-label="<?= esc(lang('UI.cpRequests'),'attr') ?>">
<?php foreach(['total'=>'platformCount','pending'=>'platformStatus_pending','approved'=>'platformStatus_approved','rejected'=>'platformStatus_rejected'] as $key=>$label): ?>
<div>
<span>
<?= esc(lang('Studio.'.$label)) ?>
</span>
<strong>
<?= isset($requestStats[$key])?number_format((int)$requestStats[$key]):'—' ?>
</strong>
</div>
<?php endforeach; ?>
</section>
<section class="form-section platform-request-workspace">
<form class="platform-request-filters" method="get" action="<?= base_url('controlplane/plan-requests') ?>">
<div class="form-field">
<label for="request-search">
<?= esc(lang('Studio.platformFindRequests')) ?>
</label>
<input id="request-search" type="search" name="request_q" value="<?= esc($requestFilters['q']??'','attr') ?>">
</div>
<div class="form-field">
<label for="request-status">
<?= esc(lang('Studio.status')) ?>
</label>
<select id="request-status" name="request_status">
<option value="">
<?= esc(lang('Studio.all')) ?>
</option>
<?php foreach(['pending','approved','rejected'] as $status): ?>
<option value="<?= $status ?>" <?= ($requestFilters['status']??'')===$status?'selected':'' ?>>
<?= esc($requestStatus($status)) ?>
</option>
<?php endforeach; ?>
</select>
</div>
<button class="button primary">
<?= esc(lang('Inventory.filter')) ?>
</button>
<a class="button secondary" href="<?= base_url('controlplane/plan-requests') ?>">
<?= esc(lang('Studio.reset')) ?>
</a>
</form>
<table class="studio-grid platform-request-table" data-grid-widths="23,13,28,18,18" aria-label="<?= esc(lang('UI.cpRequests'),'attr') ?>">
<thead>
<tr>
<?php foreach(['platformClinic','status','platformRequestedChange','platformRequestedAt','actions'] as $label): ?>
<th scope="col">
<?= esc(lang('Studio.'.$label)) ?>
</th>
<?php endforeach; ?>
</tr>
</thead>
<tbody>
<?php foreach($planRequests as $row):$status=(string)($row['status']??'pending'); ?>
<tr data-request-row>
<td>
<button type="button" class="platform-read-request" data-review-request="<?= (int)$row['id'] ?>">
<?= esc($row['clinic_name']??'') ?>
</button>
<small>#<?= (int)$row['id'] ?>
</small>
</td>
<td>
<span class="platform-request-status <?= $status==='pending'?'platform-request-status--pending':'' ?>">
<?= esc($requestStatus($status)) ?>
</span>
</td>
<td>
<strong>
<?= esc($row['requested_plan_name']??lang('ActivityLog.notAvailable')) ?>
</strong>
<small>
<?= esc(lang('Studio.platformFromPlan')) ?>: <?= esc($row['current_plan_name']??lang('ActivityLog.notAvailable')) ?> · <?= esc(lang('Studio.'.(($row['requested_duration']??'monthly')==='yearly'?'yearly':'monthly'))) ?>
</small>
</td>
<td>
<?= esc($row['created_at']??'') ?>
</td>
<td>
<button type="button" class="button secondary" data-review-request="<?= (int)$row['id'] ?>">
<?= esc(lang('Studio.actions')) ?>
</button>
</td>
</tr>
<?php endforeach; ?>
</tbody>
</table>
<?php if(!$planRequests): ?>
<p class="records-note">
<?= esc(lang('Studio.emptyList')) ?>
</p>
<?php endif; ?>
</section>
<dialog id="platform-request-review" class="platform-request-review" aria-labelledby="request-review-title">
<header>
<h2 id="request-review-title">
</h2>
<button class="icon-button" type="button" data-close aria-label="<?= esc(lang('Studio.close'),'attr') ?>">
<?= $studioIcon('close') ?>
</button>
</header>
<dl id="request-review-facts">
</dl>
<div class="platform-request-forms">
<?php foreach($planRequests as $row):$status=(string)($row['status']??'pending'); ?>
<section data-request-actions="<?= (int)$row['id'] ?>" hidden>
<?php if($status==='pending'): ?>
<form action="<?= base_url('controlplane/plan-requests/'.(int)$row['id'].'/approve') ?>" method="post" data-approve-form>
<?= csrf_field() ?>
<input type="hidden" name="expected_requested_plan_id" value="<?= esc($row['requested_plan_id'],'attr') ?>">
<input type="hidden" name="expected_requested_duration" value="<?= esc($row['requested_duration']??'monthly','attr') ?>">
<button class="button primary" type="button" data-approve-button>
<?= esc(lang('Studio.platformApproveRequest')) ?>
</button>
</form>
<form action="<?= base_url('controlplane/plan-requests/'.(int)$row['id'].'/reject') ?>" method="post" class="studio-form">
<?= csrf_field() ?>
<div class="form-field">
<label for="request-reason-<?= (int)$row['id'] ?>">
<?= esc(lang('Studio.platformDecisionReason')) ?>
</label>
<textarea id="request-reason-<?= (int)$row['id'] ?>" name="decision_reason" required rows="3">
</textarea>
</div>
<button class="button destructive" type="submit">
<?= esc(lang('Studio.platformRejectRequest')) ?>
</button>
</form>
<?php else: ?>
<form action="<?= base_url('controlplane/plan-requests/'.(int)$row['id'].'/delete') ?>" method="post" data-delete-request>
<?= csrf_field() ?>
<button class="button destructive" type="button" data-delete-button>
<?= esc(lang('Studio.platformDeleteRequest')) ?>
</button>
</form>
<?php endif; ?>
</section>
<?php endforeach; ?>
</div>
</dialog>
<dialog id="platform-request-confirm" class="help-dialog" aria-labelledby="request-confirm-title">
<header>
<h2 id="request-confirm-title">
</h2>
<button class="icon-button" type="button" data-close aria-label="<?= esc(lang('Studio.close'),'attr') ?>">
<?= $studioIcon('close') ?>
</button>
</header>
<p id="request-confirm-help">
</p>
<footer class="platform-task-actions">
<button class="button secondary" type="button" data-close>
<?= esc(lang('Studio.cancel')) ?>
</button>
<button class="button primary" type="button" data-confirm-request>
<?= esc(lang('Studio.confirm')) ?>
</button>
</footer>
</dialog>
<dialog id="platform-request-queue" class="help-dialog" aria-labelledby="queue-request-title">
<header>
<h2 id="queue-request-title">
<?= esc(lang('Studio.platformQueueRequest')) ?>
</h2>
<button class="icon-button" type="button" data-close aria-label="<?= esc(lang('Studio.close'),'attr') ?>">
<?= $studioIcon('close') ?>
</button>
</header>
<form class="studio-form platform-queue-form" action="<?= base_url('controlplane/plan-requests') ?>" method="post">
<?= csrf_field() ?>
<div class="form-field">
<label for="queue-clinic">
<?= esc(lang('Studio.platformClinic')) ?>
</label>
<select id="queue-clinic" name="clinic_id" required>
<option value="">
<?= esc(lang('Studio.chooseOption')) ?>
</option>
<?php foreach(($requestClinics??[]) as $clinic): ?>
<option value="<?= (int)$clinic['id'] ?>">#<?= (int)$clinic['id'] ?> · <?= esc($clinic['name']) ?>
</option>
<?php endforeach; ?>
</select>
</div>
<div class="form-field">
<label for="queue-plan">
<?= esc(lang('Studio.platformTargetPlan')) ?>
</label>
<select id="queue-plan" name="requested_plan_id" required>
<option value="">
<?= esc(lang('Studio.chooseOption')) ?>
</option>
<?php foreach(($requestPlans??[]) as $plan): ?>
<option value="<?= (int)$plan['id'] ?>">#<?= (int)$plan['id'] ?> · <?= esc($plan['name']) ?>
</option>
<?php endforeach; ?>
</select>
</div>
<div class="form-field">
<label for="queue-duration">
<?= esc(lang('Studio.platformDuration')) ?>
</label>
<select id="queue-duration" name="requested_duration">
<option value="monthly">
<?= esc(lang('Studio.monthly')) ?>
</option>
<option value="yearly">
<?= esc(lang('Studio.yearly')) ?>
</option>
</select>
</div>
<button class="button primary" type="submit">
<?= esc(lang('Studio.platformQueueRequest')) ?>
</button>
</form>
</dialog>
<script id="platform-requests-config" type="application/json">
<?= json_encode(['rows'=>array_map(static fn($row)=>array_intersect_key($row,array_flip(['id','clinic_name','current_plan_name','requested_plan_name','requested_duration','created_at','status','decision_reason'])),$planRequests),'labels'=>['clinic'=>lang('Studio.platformClinic'),'current_plan_name'=>lang('Studio.platformFromPlan'),'requested_plan_name'=>lang('Studio.platformTargetPlan'),'requested_duration'=>lang('Studio.platformDuration'),'created_at'=>lang('Studio.platformRequestedAt'),'status'=>lang('Studio.status'),'decision_reason'=>lang('Studio.platformDecisionReason')],'status'=>array_combine(['pending','approved','rejected'],array_map($requestStatus,['pending','approved','rejected'])),'monthly'=>lang('Studio.monthly'),'yearly'=>lang('Studio.yearly'),'approve'=>lang('Studio.platformApproveRequest'),'approveHelp'=>lang('Studio.platformApproveHelp'),'delete'=>lang('Studio.platformDeleteRequest'),'deleteHelp'=>lang('Studio.platformDeleteHelp')],JSON_HEX_TAG|JSON_HEX_AMP|JSON_HEX_APOS|JSON_HEX_QUOT|JSON_UNESCAPED_UNICODE) ?>
</script>
<?= $this->endSection() ?>
<?= $this->section('scripts') ?>
<script src="<?= base_url('assets/js/studio-platform-requests.js') ?>?v=<?= esc(asset_version('assets/js/studio-platform-requests.js')) ?>" defer>
</script>
<?= $this->endSection() ?>
