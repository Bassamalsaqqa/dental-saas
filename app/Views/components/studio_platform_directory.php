<?php include APPPATH.'Views/layouts/partials/studio_icons.php';$directoryClinic=($directory_mode??'clinics')==='clinics';$directoryResource=$directoryClinic?'clinics':'users';$directoryHeaders=$directoryClinic?['platformClinic','status','planName','platformAdministrator','platformMrr','actions']:['name','platformGlobalRole','platformMemberships','status','platformCreatedAt','actions'];$directoryKeys=$directoryClinic?['name','status','plan_name','admin_email','mrr']:['name','role','clinic_names','status','created_on'];$filters=is_array($filters??null)?$filters:[];$stats=is_array($stats??null)?$stats:[]; ?>
<header class="page-title-row">
<h1>
<?= esc(lang($directoryClinic?'UI.cpClinics':'UI.cpUsers')) ?>
</h1>
<?php if($directoryClinic): ?>
<a class="button primary" href="<?= base_url('controlplane/onboarding/clinic/create') ?>">
<?= $studioIcon('plus') ?>
<?= esc(lang('UI.cpOnboarding')) ?>
</a>
<?php endif; ?>
</header>
<section class="platform-directory-summary">
<?php foreach(['total'=>'platformCount','active'=>'platformActive'] as $key=>$label): ?>
<div>
<span>
<?= esc(lang('Studio.'.$label)) ?>
</span>
<strong>
<?= isset($stats[$key])?number_format((int)$stats[$key]):'—' ?>
</strong>
</div>
<?php endforeach; ?>
<?php if(!$directoryClinic): ?>
<div>
<span>
<?= esc(lang('Studio.platformNewThisMonth')) ?>
</span>
<strong>
<?= isset($stats['new'])?number_format((int)$stats['new']):'—' ?>
</strong>
</div>
<?php endif; ?>
</section>
<section class="form-section" id="platform-directory" data-mode="<?= $directoryResource ?>">
<form id="platform-directory-filters" class="platform-directory-filters">
<div class="form-field">
<label for="platform-directory-search">
<?= esc(lang('Studio.'.($directoryClinic?'platformFindClinics':'platformFindUsers'))) ?>
</label>
<input type="search" id="platform-directory-search" name="q" value="<?= esc($filters['q']??'','attr') ?>" autocomplete="off">
</div>
<div class="form-field">
<label for="platform-directory-status">
<?= esc(lang('Studio.status')) ?>
</label>
<select id="platform-directory-status" name="status">
<option value="">
<?= esc(lang('Studio.all')) ?>
</option>
<?php foreach($directoryClinic?['active','inactive','suspended']:['active','inactive'] as $status): ?>
<option value="<?= $status ?>" <?= ($filters['status']??'')===$status?'selected':'' ?>>
<?= esc(lang('Studio.platformStatus_'.$status)) ?>
</option>
<?php endforeach; ?>
</select>
</div>
<?php if($directoryClinic): ?>
<div class="form-field">
<label for="platform-directory-plan">
<?= esc(lang('Studio.planName')) ?>
</label>
<select id="platform-directory-plan" name="plan_id">
<option value="">
<?= esc(lang('Studio.platformAllPlans')) ?>
</option>
<?php foreach(($plans??[]) as $plan): ?>
<option value="<?= (int)$plan['id'] ?>" <?= (int)($filters['plan_id']??0)===(int)$plan['id']?'selected':'' ?>>
<?= esc($plan['name']) ?>
</option>
<?php endforeach; ?>
</select>
</div>
<?php endif; ?>
<button class="button primary" type="submit">
<?= esc(lang('Inventory.filter')) ?>
</button>
<button class="button secondary" type="button" data-directory-reset>
<?= esc(lang('Studio.reset')) ?>
</button>
</form>
<div class="platform-directory-tools">
<div class="form-field">
<label for="platform-directory-order">
<?= esc(lang('Studio.platformSortBy')) ?>
</label>
<select id="platform-directory-order">
<?php foreach($directoryKeys as $i=>$key):foreach(['asc'=>'platformAscending','desc'=>'platformDescending'] as $direction=>$label): ?>
<option value="<?= $i.':'.$direction ?>">
<?= esc(lang('Studio.'.$directoryHeaders[$i]).' · '.lang('Studio.'.$label)) ?>
</option>
<?php endforeach;endforeach; ?>
</select>
</div>
<div class="platform-directory-exports">
<button type="button" class="button secondary" data-directory-csv>
<?= esc(lang('Studio.platformExportPage')) ?>
</button>
<button type="button" class="button secondary" data-directory-print>
<?= esc(lang('Studio.platformPrintPage')) ?>
</button>
<button type="button" class="button secondary" data-directory-refresh>
<?= esc(lang('ActivityLog.refresh')) ?>
</button>
</div>
</div>
<p class="platform-directory-feedback" role="status" data-directory-feedback>
</p>
<button type="button" class="button secondary" data-directory-retry hidden>
<?= esc(lang('Studio.retry')) ?>
</button>
<table class="studio-grid" id="platform-directory-table" data-grid-widths="<?= $directoryClinic?'24,12,15,24,13,12':'25,13,23,12,15,12' ?>" aria-label="<?= esc(lang($directoryClinic?'UI.cpClinics':'UI.cpUsers'),'attr') ?>">
<thead>
<tr>
<?php foreach($directoryHeaders as $label): ?>
<th scope="col">
<?= esc(lang('Studio.'.$label)) ?>
</th>
<?php endforeach; ?>
</tr>
</thead>
<tbody>
</tbody>
</table>
<footer class="platform-directory-pagination">
<span data-directory-range aria-live="polite">
</span>
<nav>
<button class="button secondary" type="button" data-directory-prev>
<?= esc(lang('Studio.previous')) ?>
</button>
<span data-directory-page>
</span>
<button class="button secondary" type="button" data-directory-next>
<?= esc(lang('Studio.next')) ?>
</button>
</nav>
<div class="form-field">
<label for="platform-directory-length">
<?= esc(lang('ActivityLog.show')) ?>
</label>
<select id="platform-directory-length">
<?php foreach([10,25,50,100] as $count): ?>
<option value="<?= $count ?>">
<?= $count ?>
</option>
<?php endforeach; ?>
</select>
</div>
</footer>
</section>
<dialog id="platform-directory-review" class="platform-directory-review" aria-labelledby="platform-directory-title">
<header>
<div>
<h2 id="platform-directory-title">
</h2>
<p data-directory-reference>
</p>
</div>
<button class="icon-button" type="button" data-close aria-label="<?= esc(lang('Studio.close'),'attr') ?>">
<?= $studioIcon('close') ?>
</button>
</header>
<div class="platform-directory-review-body">
<dl>
</dl>
<nav class="platform-directory-review-actions">
</nav>
<p class="platform-directory-lock">
<?= esc(lang('IonAuth.delete_disabled_label')) ?>
</p>
</div>
</dialog>
<dialog id="platform-directory-confirm" class="help-dialog" aria-labelledby="platform-directory-confirm-title">
<header>
<h2 id="platform-directory-confirm-title">
</h2>
<button class="icon-button" type="button" data-close aria-label="<?= esc(lang('Studio.close'),'attr') ?>">
<?= $studioIcon('close') ?>
</button>
</header>
<div class="platform-directory-confirm-body">
<p data-directory-confirm-help>
</p>
<p data-directory-confirm-name>
</p>
<div class="form-field" data-terminate-field hidden>
<label for="platform-terminate-name">
<?= esc(lang('Studio.platformTypeClinicName')) ?>
</label>
<input id="platform-terminate-name" autocomplete="off">
</div>
<p role="alert" data-directory-confirm-error hidden>
</p>
</div>
<footer>
<button class="button secondary" type="button" data-close>
<?= esc(lang('Studio.cancel')) ?>
</button>
<button class="button primary" type="button" data-directory-proceed>
<?= esc(lang('Studio.confirm')) ?>
</button>
</footer>
</dialog>
<script id="platform-directory-config" type="application/json">
<?= json_encode(['mode'=>$directoryResource,'url'=>base_url('controlplane/'.$directoryResource),'base'=>base_url('controlplane'),'columns'=>$directoryKeys,'labels'=>array_combine(['loading','failed','empty','review','open','total','filtered','showing','of','suspend','reactivate','toggleUser','terminate','impersonate','suspendHelp','reactivateHelp','toggleUserHelp','terminateHelp','impersonateHelp','status','name','email','role','clinics','created','plan','mrr','admin','adminState','adminSource'],array_map(static fn($key)=>lang($key),['ActivityLog.loadingActivities','ActivityLog.failedToLoadActivities','Studio.emptyList','Studio.actions','UI.openFullPage','Studio.platformCount','ActivityLog.filtered','Studio.platformShowing','Studio.platformOf','Studio.platformSuspendClinic','Studio.platformReactivateClinic','Studio.platformToggleUser','Studio.platformTerminateClinic','Studio.platformImpersonate','Studio.platformSuspendHelp','Studio.platformReactivateHelp','Studio.platformToggleUserHelp','Studio.platformTerminateHelp','Studio.platformImpersonateHelp','Studio.status','Studio.name','Studio.platformEmail','Studio.platformGlobalRole','Studio.platformMemberships','Studio.platformCreatedAt','Studio.planName','Studio.platformMrr','Studio.platformAdministrator','Studio.platformAdminState','Studio.platformAdminSource'])),'statuses'=>['active'=>lang('Studio.platformStatus_active'),'inactive'=>lang('Studio.platformStatus_inactive'),'suspended'=>lang('Studio.platformStatus_suspended')]],JSON_HEX_TAG|JSON_HEX_AMP|JSON_HEX_APOS|JSON_HEX_QUOT|JSON_UNESCAPED_UNICODE) ?>
</script>
