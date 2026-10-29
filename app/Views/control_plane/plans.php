<?php helper('asset');include APPPATH.'Views/layouts/partials/studio_icons.php'; ?>
<?= $this->extend('layouts/studio_control_plane') ?>
<?= $this->section('styles') ?>
<link rel="stylesheet" href="<?= base_url('assets/css/studio-forms.css') ?>?v=<?= esc(asset_version('assets/css/studio-forms.css')) ?>">
<link rel="stylesheet" href="<?= base_url('assets/css/studio-management.css') ?>?v=<?= esc(asset_version('assets/css/studio-management.css')) ?>">
<link rel="stylesheet" href="<?= base_url('assets/css/studio-create-dialog.css') ?>?v=<?= esc(asset_version('assets/css/studio-create-dialog.css')) ?>"><?= $this->endSection() ?>
<?= $this->section('content') ?>
<header class="page-title-row">
<div>
<h1>
<?= esc(lang('UI.cpPlans')) ?>
</h1>
</div>
<a class="button primary" data-native-create="platform-plan-create-dialog" href="<?= base_url('controlplane/plans/create') ?>">
<?= esc(lang('Studio.planCreate')) ?>
</a>
</header>
<section class="staff-directory">
<form method="get" action="<?= base_url('controlplane/plans') ?>" class="platform-plan-filters">
<div class="form-field">
<label for="plan-search">
<?= esc(lang('Studio.planName')) ?>
</label>
<input id="plan-search" type="search" name="q" value="<?= esc($filters['q']??'','attr') ?>">
</div>
<div class="form-field">
<label for="plan-filter-status">
<?= esc(lang('UserRole.status')) ?>
</label>
<select id="plan-filter-status" name="status">
<option value="">
<?= esc(lang('Studio.all')) ?>
</option>
<?php foreach(['active','inactive'] as $status): ?>
<option value="<?= $status ?>" <?= ($filters['status']??'')===$status?'selected':'' ?>>
<?= esc(lang('UserRole.'.$status)) ?>
</option>
<?php endforeach; ?>
</select>
</div>
<div class="form-field">
<label for="plan-filter-duration">
<?= esc(lang('Studio.planDuration')) ?>
</label>
<select id="plan-filter-duration" name="duration">
<option value="">
<?= esc(lang('Studio.chooseOption')) ?>
</option>
<?php foreach(['monthly','yearly','lifetime'] as $duration): ?>
<option value="<?= $duration ?>" <?= ($filters['duration']??'')===$duration?'selected':'' ?>>
<?= esc(lang('Studio.'.$duration)) ?>
</option>
<?php endforeach; ?>
</select>
</div>
<button type="submit" class="button primary">
<?= esc(lang('Studio.filter')) ?>
</button>
<a class="button secondary" href="<?= base_url('controlplane/plans') ?>">
<?= esc(lang('Studio.reset')) ?>
</a>
</form>
<div class="platform-table-scroll">
<table class="studio-grid platform-table">
<thead>
<tr>
<?php foreach(['planName','planPrice','planLimits','planSubscriptions'] as $key): ?>
<th scope="col">
<?= esc(lang('Studio.'.$key)) ?>
</th>
<?php endforeach; ?>
<th scope="col">
<?= esc(lang('UserRole.status')) ?>
</th>
<th scope="col">
<?= esc(lang('Studio.actions')) ?>
</th>
</tr>
</thead>
<tbody>
<?php foreach($plans as $row):$plan=$row['plan']??[];$limits=$row['limits']??[];$active=($plan['status']??'inactive')==='active';$id=(int)$plan['id']; ?>
<tr data-platform-row>
<td data-label="<?= esc(lang('Studio.planName'),'attr') ?>">
<a class="platform-primary" href="<?= base_url('controlplane/plans/'.$id.'/edit') ?>">
<?= esc($plan['name']) ?>
</a>
<small>#<?= $id ?>
</small>
</td>
<td data-label="<?= esc(lang('Studio.planPrice'),'attr') ?>">
<strong dir="ltr">$<?= number_format((float)($limits['price']??0),2) ?>
</strong>
<small>
<?= esc(lang('Studio.'.($limits['duration']??'monthly'))) ?>
</small>
</td>
<td data-label="<?= esc(lang('Studio.planLimits'),'attr') ?>">
<?php foreach([['patients_active_max','planPatients'],['users_max','planSeats'],['storage_gb','planStorage']] as [$key,$label]): ?>
<small>
<?= esc(lang('Studio.'.$label)) ?>: <?= (float)($limits[$key]??-1)===-1.0?esc(lang('Studio.unlimited')):esc($limits[$key]??-1) ?>
</small>
<?php endforeach; ?>
</td>
<td data-label="<?= esc(lang('Studio.planSubscriptions'),'attr') ?>">
<?= (int)($row['usage_count']??0) ?>
</td>
<td data-label="<?= esc(lang('UserRole.status'),'attr') ?>">
<span class="staff-badge <?= $active?'':'inactive' ?>">
<?= esc(lang('UserRole.'.($active?'active':'inactive'))) ?>
</span>
</td>
<td data-label="<?= esc(lang('Studio.actions'),'attr') ?>">
<div class="workspace-action-menu"><?= view('components/studio_row_menu_trigger',['rowMenuLabel'=>lang('Studio.actions').': '.($plan['name']??''),'rowMenuId'=>'plan-menu-'.(int)$id]) ?><nav id="<?= esc('plan-menu-'.(int)$id,'attr') ?>" popover="auto" data-studio-menu-panel>
<a class="button secondary" href="<?= base_url('controlplane/plans/'.$id.'/edit') ?>">
<?= esc(lang('UserRole.edit')) ?>
</a>
<button type="button" class="button secondary" data-management-action data-url="<?= base_url('controlplane/plans/'.$id.'/toggle') ?>" data-method="POST" data-encoding="form" data-confirm="<?= esc(lang('UserRole.confirmToggleRoleStatus',['action'=>lang('UserRole.'.($active?'deactivate':'activate'))]),'attr') ?>">
<?= esc(lang('UserRole.'.($active?'deactivate':'activate'))) ?>
</button>
<button type="button" class="button destructive" data-management-action data-url="<?= base_url('controlplane/plans/'.$id.'/delete') ?>" data-method="POST" data-encoding="form" data-require-typing="DELETE" data-confirm="<?= esc($plan['name'].' — '.lang('Studio.planDeleteHelp'),'attr') ?>">
<?= esc(lang('UserRole.delete')) ?>
</button>
</nav></div>
</td>
</tr>
<?php endforeach; ?>
</tbody>
</table>
</div>
<?php if(empty($plans)): ?>
<div class="staff-empty">
<?= esc(lang('Studio.noMatchingRecords')) ?>
</div>
<?php endif; ?>
</section>
<?= view('users/partials/studio_action_dialog') ?>
<?= view('components/studio_native_create_dialog',['studioIcon'=>$studioIcon,'createId'=>'platform-plan-create-dialog','createFormId'=>'platform-plan-form','createTitle'=>lang('Studio.planCreate'),'createAction'=>base_url('controlplane/plans'),'createSource'=>'studio_platform_plan_create','createReturn'=>'controlplane/plans','createFields'=>'control_plane/partials/studio_plan_fields','createData'=>['plan'=>[],'limits'=>$plan_create_limits??[],'features'=>$plan_create_features??[]],'createFullPage'=>base_url('controlplane/plans/create'),'createSubmit'=>lang('Studio.planCreate')]) ?>
<?= $this->endSection() ?>
<?= $this->section('scripts') ?>
<script src="<?= base_url('assets/js/studio-management.js') ?>?v=<?= esc(asset_version('assets/js/studio-management.js')) ?>" defer>
</script>
<?= $this->endSection() ?>
