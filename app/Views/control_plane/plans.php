<?php helper('asset'); ?>
<?= $this->extend('layouts/studio_control_plane') ?>
<?= $this->section('styles') ?>
<link rel="stylesheet" href="<?= base_url('assets/css/studio-forms.css') ?>?v=<?= esc(asset_version('assets/css/studio-forms.css')) ?>">
<link rel="stylesheet" href="<?= base_url('assets/css/studio-management.css') ?>?v=<?= esc(asset_version('assets/css/studio-management.css')) ?>">
<?= $this->endSection() ?>
<?= $this->section('content') ?>
<header class="page-title-row">
<div>
<h1>
<?= esc(lang('UI.cpPlans')) ?>
</h1>
</div>
<a class="button primary" href="<?= base_url('controlplane/plans/create') ?>">
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
<label for="plan-status">
<?= esc(lang('UserRole.status')) ?>
</label>
<select id="plan-status" name="status">
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
<label for="plan-duration">
<?= esc(lang('Studio.planDuration')) ?>
</label>
<select id="plan-duration" name="duration">
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
<div class="platform-table-actions">
<a class="button secondary" href="<?= base_url('controlplane/plans/'.$id.'/edit') ?>">
<?= esc(lang('UserRole.edit')) ?>
</a>
<button type="button" class="button secondary" data-management-action data-url="<?= base_url('controlplane/plans/'.$id.'/toggle') ?>" data-method="POST" data-encoding="form" data-confirm="<?= esc(lang('UserRole.confirmToggleRoleStatus',['action'=>lang('UserRole.'.($active?'deactivate':'activate'))]),'attr') ?>">
<?= esc(lang('UserRole.'.($active?'deactivate':'activate'))) ?>
</button>
<button type="button" class="button destructive" data-management-action data-url="<?= base_url('controlplane/plans/'.$id.'/delete') ?>" data-method="POST" data-encoding="form" data-require-typing="DELETE" data-confirm="<?= esc($plan['name'].' — '.lang('Studio.planDeleteHelp'),'attr') ?>">
<?= esc(lang('UserRole.delete')) ?>
</button>
</div>
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
<?= $this->endSection() ?>
<?= $this->section('scripts') ?>
<script src="<?= base_url('assets/js/studio-management.js') ?>?v=<?= esc(asset_version('assets/js/studio-management.js')) ?>" defer>
</script>
<?= $this->endSection() ?>
