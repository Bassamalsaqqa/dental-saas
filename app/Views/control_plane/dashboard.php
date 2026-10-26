<?php helper('asset');include APPPATH.'Views/layouts/partials/studio_icons.php';$stats=is_array($stats??null)?$stats:[];$chart_data=is_array($chart_data??null)?$chart_data:[];$cpNumber=static fn($key)=>array_key_exists($key,$stats)?number_format((float)$stats[$key]):'—';$cpStatus=static function($value){$key='Studio.platformStatus_'.(string)$value;$label=lang($key);return $label===$key?(string)$value:$label;}; ?>
<?= $this->extend('layouts/studio_control_plane') ?>
<?= $this->section('styles') ?>
<link rel="stylesheet" href="<?= base_url('assets/css/studio-forms.css') ?>?v=<?= esc(asset_version('assets/css/studio-forms.css')) ?>">
<link rel="stylesheet" href="<?= base_url('assets/css/studio-platform-overview.css') ?>?v=<?= esc(asset_version('assets/css/studio-platform-overview.css')) ?>">
<?= $this->endSection() ?>
<?= $this->section('content') ?>
<header class="page-title-row">
<h1>
<?= esc(lang('Studio.platformOverview')) ?>
</h1>
<nav class="platform-overview-actions">
<a class="button secondary" href="<?= base_url('controlplane/operations') ?>">
<?= esc(lang('UI.cpOperations')) ?>
</a>
<a class="button primary" href="<?= base_url('controlplane/onboarding/clinic/create') ?>">
<?= $studioIcon('plus') ?>
<?= esc(lang('UI.cpOnboarding')) ?>
</a>
</nav>
</header>
<section class="platform-overview-band" aria-label="<?= esc(lang('Studio.platformFootprint'),'attr') ?>">
<a href="<?= base_url('controlplane/clinics') ?>">
<span>
<?= esc(lang('UI.cpClinics')) ?>
</span>
<strong>
<?= $cpNumber('total_clinics') ?>
</strong>
<small>
<?= esc(lang('Studio.platformActive')) ?> <?= $cpNumber('active_clinics') ?>
</small>
</a>
<a href="<?= base_url('controlplane/plans') ?>">
<span>
<?= esc(lang('Studio.platformSubscriptions')) ?>
</span>
<strong>
<?= $cpNumber('active_subscriptions') ?>
</strong>
<small>
<?= $cpNumber('active_plans') ?> <?= esc(lang('Studio.platformPlansActive')) ?>
</small>
</a>
<a href="<?= base_url('controlplane/users') ?>">
<span>
<?= esc(lang('Studio.platformUsersActive')) ?>
</span>
<strong>
<?= $cpNumber('active_users') ?>
</strong>
</a>
<div>
<span>
<?= esc(lang('Studio.platformMrr')) ?>
</span>
<strong dir="ltr">$<?= array_key_exists('mrr',$stats)?number_format((float)$stats['mrr'],2):'—' ?>
</strong>
</div>
</section>
<div class="platform-overview-layout">
<div class="platform-overview-column">
<section class="platform-overview-panel">
<header>
<h2>
<?= esc(lang('Studio.platformWorkQueue')) ?>
</h2>
</header>
<div class="platform-overview-queue">
<a href="<?= base_url('controlplane/plan-requests?request_status=pending') ?>" class="platform-queue-item <?= !empty($stats['pending_requests'])?'platform-queue-item--attention':'' ?>">
<span class="platform-queue-icon">
<?= $studioIcon('report') ?>
</span>
<div>
<h3>
<?= esc(lang('Studio.platformRequestsPending')) ?>
</h3>
<p>
<?= esc(lang('Studio.platformRequestsHelp')) ?>
</p>
</div>
<strong>
<?= $cpNumber('pending_requests') ?>
</strong>
</a>
<a href="<?= base_url('controlplane/clinics') ?>" class="platform-queue-item <?= !empty($stats['expiring_subscriptions'])?'platform-queue-item--attention':'' ?>">
<span class="platform-queue-icon">
<?= $studioIcon('clock') ?>
</span>
<div>
<h3>
<?= esc(lang('Studio.platformRenewalWatch')) ?>
</h3>
<p>
<?= esc(lang('Studio.platformRenewalHelp')) ?>
</p>
</div>
<strong>
<?= $cpNumber('expiring_subscriptions') ?>
</strong>
</a>
<a href="<?= base_url('controlplane/operations') ?>" class="platform-queue-item">
<span class="platform-queue-icon">
<?= $studioIcon('settings') ?>
</span>
<div>
<h3>
<?= esc(lang('Studio.platformRuntimeReview')) ?>
</h3>
<p>
<?= esc(lang('Studio.platformRuntimeHelp')) ?>
</p>
</div>
</a>
</div>
</section>
<section class="platform-overview-panel">
<header>
<h2>
<?= esc(lang('Studio.platformRecentRequests')) ?>
</h2>
<a class="button secondary" href="<?= base_url('controlplane/plan-requests') ?>">
<?= esc(lang('Studio.platformViewAll')) ?>
</a>
</header>
<div class="platform-recent-requests">
<?php foreach(($recent_plan_requests??[]) as $row): ?>
<a href="<?= base_url('controlplane/plan-requests') ?>">
<div>
<h3>
<?= esc($row['clinic_name']??'') ?>
</h3>
<p>
<?= esc($row['summary']??'') ?>
</p>
</div>
<span class="platform-overview-status">
<?= esc($cpStatus($row['status']??'')) ?>
</span>
</a>
<?php endforeach; ?>
<?php if(empty($recent_plan_requests)): ?>
<p class="platform-overview-empty">
<?= esc(lang('Studio.emptyList')) ?>
</p>
<?php endif; ?>
</div>
</section>
</div>
<div class="platform-overview-column">
<section class="platform-overview-panel">
<header>
<h2>
<?= esc(lang('Studio.platformRecentClinics')) ?>
</h2>
<a class="button secondary" href="<?= base_url('controlplane/clinics') ?>">
<?= esc(lang('Studio.platformViewAll')) ?>
</a>
</header>
<div class="platform-recent-clinics">
<?php foreach(($recent_clinics??[]) as $row): ?>
<a href="<?= base_url('controlplane/clinics/'.(int)$row['id']) ?>">
<span class="avatar">
<?= esc($studioInitials($row['name']??'')) ?>
</span>
<div>
<h3>
<?= esc($row['name']??'') ?>
</h3>
<small>#<?= (int)$row['id'] ?>
</small>
</div>
<span class="platform-overview-status">
<?= esc($cpStatus($row['status']??'')) ?>
</span>
</a>
<?php endforeach; ?>
<?php if(empty($recent_clinics)): ?>
<p class="platform-overview-empty">
<?= esc(lang('Studio.emptyList')) ?>
</p>
<?php endif; ?>
</div>
</section>
<section class="platform-overview-panel">
<header>
<h2>
<?= esc(lang('Studio.platformActivityStorage')) ?>
</h2>
<a class="button secondary" href="<?= base_url('controlplane/activity-log') ?>">
<?= esc(lang('UI.cpSystemActivity')) ?>
</a>
</header>
<dl class="platform-overview-facts">
<div>
<dt>
<?= esc(lang('Studio.platformEvents7')) ?>
</dt>
<dd>
<?= $cpNumber('events_7d') ?>
</dd>
</div>
<div>
<dt>
<?= esc(lang('Studio.platformEvents30')) ?>
</dt>
<dd>
<?= $cpNumber('events_30d') ?>
</dd>
</div>
<div>
<dt>
<?= esc(lang('Studio.platformStorage')) ?>
</dt>
<dd dir="ltr">
<?= isset($stats['storage_total_bytes'])?number_format((int)$stats['storage_total_bytes']/1024/1024/1024,2).' GB':'—' ?>
</dd>
</div>
<div>
<dt>
<?= esc(lang('Studio.platformLastEvent')) ?>
</dt>
<dd>
<?= esc($stats['last_event_at']??lang('ActivityLog.notAvailable')) ?>
</dd>
</div>
</dl>
<?php if(!empty($storage_top)): ?>
<h3 class="platform-overview-subtitle">
<?= esc(lang('Studio.platformLargestStorage')) ?>
</h3>
<dl class="platform-storage-list">
<?php foreach($storage_top as $row): ?>
<div>
<dt>
<?= esc($row['clinic_name']??'') ?>
</dt>
<dd dir="ltr">
<?= number_format((int)($row['total_size']??0)/1024/1024/1024,2) ?> GB</dd>
</div>
<?php endforeach; ?>
</dl>
<?php endif; ?>
</section>
</div>
</div>
<section class="platform-overview-panel platform-overview-trends">
<header>
<h2>
<?= esc(lang('Studio.platformTrends')) ?>
</h2>
</header>
<div class="platform-trend-grid">
<?php foreach(['mrr_series'=>['platformMrr','label','value','money'],'clinic_growth'=>['platformClinicGrowth','label','value','count'],'activity_series'=>['platformAuditVolume','day','total','count'],'plan_distribution'=>['platformPlanDistribution','label','value','count']] as $key=>[$label,$dateKey,$amountKey,$format]): ?>
<details>
<summary>
<?= esc(lang('Studio.'.$label)) ?>
</summary>
<table class="studio-grid" data-grid-widths="55,45" aria-label="<?= esc(lang('Studio.'.$label),'attr') ?>">
<thead>
<tr>
<th scope="col">
<?= esc(lang($key==='plan_distribution'?'Studio.planName':'Studio.platformPeriod')) ?>
</th>
<th scope="col">
<?= esc(lang('Studio.'.($format==='money'?'platformMrr':'platformCount'))) ?>
</th>
</tr>
</thead>
<tbody>
<?php foreach(($chart_data[$key]??[]) as $point): ?>
<tr>
<td>
<?= esc($point[$dateKey]??'') ?>
</td>
<td dir="ltr">
<?= $format==='money'?'$':'' ?>
<?= number_format((float)($point[$amountKey]??0),$format==='money'?2:0) ?>
</td>
</tr>
<?php endforeach; ?>
</tbody>
</table>
<?php if(empty($chart_data[$key])): ?>
<p class="platform-overview-empty">
<?= esc(lang('Studio.emptyList')) ?>
</p>
<?php endif; ?>
</details>
<?php endforeach; ?>
<details>
<summary>
<?= esc(lang('Studio.platformClinicStatus')) ?>
</summary>
<dl class="platform-overview-facts">
<?php foreach(($chart_data['status_breakdown']??[]) as $status=>$count): ?>
<div>
<dt>
<?= esc($cpStatus($status)) ?>
</dt>
<dd>
<?= number_format((int)$count) ?>
</dd>
</div>
<?php endforeach; ?>
</dl>
</details>
</div>
</section>
<?= $this->endSection() ?>
