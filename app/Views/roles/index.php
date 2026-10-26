<?php helper('asset');$roleLayout=session('global_mode')?'layouts/studio_control_plane':'layouts/studio';$global_mode=(bool)($global_mode??session('global_mode'));$rolesRoute=$rolesRoute??($global_mode?'controlplane/roles':'roles'); ?>
<?= $this->extend($roleLayout) ?>
<?= $this->section('content') ?>
<link rel="stylesheet" href="<?= base_url('assets/css/studio-forms.css') ?>?v=<?= esc(asset_version('assets/css/studio-forms.css')) ?>">
<link rel="stylesheet" href="<?= base_url('assets/css/studio-management.css') ?>?v=<?= esc(asset_version('assets/css/studio-management.css')) ?>">
<div class="management-workspace">
<header class="page-title-row">
<div>
<h1>
<?= esc(lang('UI.menuRoleManagement')) ?>
</h1>
</div>
<div class="management-actions">
<?php if($global_mode&&has_permission('settings','edit')): ?>
<a class="button secondary" href="<?= base_url($rolesRoute.'/sync') ?>">
<?= esc(lang('UserRole.syncPermissions')) ?>
</a>
<?php endif; ?>
<?php if(has_permission('users','create')&&has_permission('users','roles')): ?>
<a class="button primary" href="<?= esc(workflow_context_url($rolesRoute.'/create')) ?>">
<?= esc(lang('UserRole.createRole')) ?>
</a>
<?php endif; ?>
</div>
</header>
<?php if(session('error')): ?>
<div class="form-notice form-notice--error" role="alert">
<?= esc(strip_tags((string)session('error'))) ?>
</div>
<?php endif; ?>
<section class="staff-directory" data-management-directory>
<form class="staff-filters" role="search" onsubmit="return false">
<div class="form-field">
<label for="roles-search">
<?= esc(lang('Studio.searchRecords')) ?>
</label>
<input id="roles-search" type="search" data-management-search autocomplete="off">
</div>
<div class="form-field">
<label for="roles-status">
<?= esc(lang('UserRole.status')) ?>
</label>
<select id="roles-status" data-management-status>
<option value="">
<?= esc(lang('Studio.all')) ?>
</option>
<option value="active">
<?= esc(lang('UserRole.active')) ?>
</option>
<option value="inactive">
<?= esc(lang('UserRole.inactive')) ?>
</option>
</select>
</div>
</form>
<div class="staff-grid-wrap"><table class="studio-grid staff-table" data-grid-widths="30,16,10,12,18,14"><thead><tr><?php foreach(['UserRole.role','Studio.roleScope','UserRole.users','UserRole.permissions','UserRole.status','Studio.actions'] as $label): ?><th scope="col"><?= esc(lang($label)) ?></th><?php endforeach; ?></tr></thead><tbody class="staff-rows">
<?php foreach($roles as $role):$isSystem=(int)($role['is_system']??0)===1||$role['clinic_id']===null;$active=(int)($role['is_active']??0)===1;$editable=($global_mode?$role['clinic_id']===null:$role['clinic_id']!==null)&&$role['slug']!=='super_admin'; ?>
<tr class="staff-row" data-management-row data-search="<?= esc(mb_strtolower($role['name'].' '.$role['slug'].' '.($role['description']??'')),'attr') ?>" data-status="<?= $active?'active':'inactive' ?>"><td class="staff-person"><?php if($editable&&has_permission('users','edit')): ?><a class="staff-primary" href="<?= esc(workflow_context_url($rolesRoute.'/'.(int)$role['id'].'/edit')) ?>"><?= esc($role['name']) ?></a><?php else: ?><strong><?= esc($role['name']) ?></strong><?php endif; ?><small dir="ltr"><?= esc($role['slug']) ?></small><small><?= esc($role['description']??'') ?></small></td>
<td><?= esc(lang('UserRole.'.($isSystem?'systemRoles':'role'))) ?></td><td><?= (int)($role['user_count']??0) ?></td><td><?= (int)($role['permission_count']??0) ?></td><td class="staff-state"><span class="staff-badge <?= $active?'':'inactive' ?>"><?= esc(lang('UserRole.'.($active?'active':'inactive'))) ?></span><?php if(!empty($role['is_medical'])): ?><span class="staff-badge"><?= esc(lang('UserRole.medical')) ?></span><?php endif; ?></td>
<td><?php if($editable&&(has_permission('users','edit')||has_permission('users','delete'))): ?><details class="workspace-action-menu"><summary class="button secondary"><?= esc(lang('Studio.stockManage')) ?></summary><nav><?php if(has_permission('users','edit')): ?><a class="button secondary" href="<?= esc(workflow_context_url($rolesRoute.'/'.(int)$role['id'].'/edit')) ?>"><?= esc(lang('UserRole.edit')) ?></a><button type="button" class="button secondary" data-management-action data-url="<?= base_url($rolesRoute.'/'.(int)$role['id'].'/toggle-status') ?>" data-method="POST" data-status="<?= $active?0:1 ?>" data-confirm="<?= esc(lang('UserRole.confirmToggleRoleStatus',['action'=>lang('UserRole.'.($active?'deactivate':'activate'))]),'attr') ?>"><?= esc(lang('UserRole.'.($active?'deactivate':'activate'))) ?></button><?php endif; ?><?php if(has_permission('users','delete')): ?><button type="button" class="button destructive" data-management-action data-url="<?= base_url($rolesRoute.'/'.(int)$role['id']) ?>" data-method="DELETE" data-confirm="<?= esc(lang('UserRole.confirmDeleteRole'),'attr') ?>"><?= esc(lang('UserRole.delete')) ?></button><?php endif; ?></nav></details><?php endif; ?></td></tr><?php endforeach; ?></tbody></table></div>
<div class="staff-empty" data-management-empty hidden>
<?= esc(lang('UserRole.noRolesFound')) ?>
</div>
<footer class="staff-pagination">
<span data-management-count aria-live="polite">
</span>
<div>
<button class="button secondary" type="button" data-management-page="-1">
<?= esc(lang('Studio.previous')) ?>
</button>
<button class="button secondary" type="button" data-management-page="1">
<?= esc(lang('Studio.next')) ?>
</button>
</div>
</footer>
</section>
<?= view('users/partials/studio_action_dialog') ?>
</div>
<script src="<?= base_url('assets/js/studio-management.js') ?>?v=<?= esc(asset_version('assets/js/studio-management.js')) ?>" defer>
</script>
<?= $this->endSection() ?>
