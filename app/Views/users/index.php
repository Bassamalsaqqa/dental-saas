<?php include APPPATH.'Views/layouts/partials/view_permissions.php'; helper('asset'); ?>
<?= $this->extend('layouts/studio') ?>
<?= $this->section('styles') ?>
<link rel="stylesheet" href="<?= base_url('assets/css/studio-forms.css') ?>?v=<?= esc(asset_version('assets/css/studio-forms.css')) ?>">
<link rel="stylesheet" href="<?= base_url('assets/css/studio-management.css') ?>?v=<?= esc(asset_version('assets/css/studio-management.css')) ?>">
<link rel="stylesheet" href="<?= base_url('assets/css/studio-create-dialog.css') ?>?v=<?= esc(asset_version('assets/css/studio-create-dialog.css')) ?>">
<?= $this->endSection() ?>
<?= $this->section('content') ?>
<?php include APPPATH.'Views/layouts/partials/studio_icons.php';$currentUserId=(int)($current_user->id??session('user_id')??0); ?>
<header class="page-title-row">
<div>
<h1>
<?= esc(lang('UI.menuUserManagement')) ?>
</h1>
</div>
<div class="management-actions">
<?php if($v4Can('users','roles')): ?>
<a class="button secondary" href="<?= base_url('roles') ?>">
<?= esc(lang('UI.menuRoleManagement')) ?>
</a>
<?php endif; ?>
<?php if($v4Can('users','create')): ?>
<a class="button primary" data-native-create="users-create-dialog" href="<?= esc(workflow_context_url('users/create')) ?>">
<?= $studioIcon('plus') ?>
<?= esc(lang('UserRole.createNewUser')) ?>
</a>
<?php endif; ?>
</div>
</header>
<?php $managementErrors=is_array(session('errors'))?session('errors'):[];$managementValidation=session('validation');if($managementValidation instanceof \CodeIgniter\Validation\Validation)$managementErrors=array_merge($managementErrors,$managementValidation->getErrors());if(session('error')||!empty($error)||$managementErrors): ?>
<section class="form-notice form-notice--error" role="alert">
<h2>
<?= esc(lang('Studio.checkFields')) ?>
</h2>
<?php if(session('error')||!empty($error)): ?>
<p>
<?= esc(strip_tags((string)(session('error')?:($error??'')))) ?>
</p>
<?php endif; ?>
<ul>
<?php foreach($managementErrors as $field=>$error): ?>
<li>
<a href="#<?= esc($field,'attr') ?>">
<?= esc($error) ?>
</a>
</li>
<?php endforeach; ?>
</ul>
</section>
<?php endif; ?>

<section class="staff-directory" data-management-directory>
<form class="staff-filters staff-filters--users" role="search" onsubmit="return false">
<div class="form-field">
<label for="staff-search">
<?= esc(lang('Studio.searchRecords')) ?>
</label>
<input type="search" id="staff-search" data-management-search autocomplete="off">
</div>
<div class="form-field"><label for="groupFilter"><?= esc(lang('UserRole.role')) ?></label><select id="groupFilter" data-management-role><option value=""><?= esc(lang('UserRole.allRoles')) ?></option><?php foreach(($roles??[]) as $availableRole): ?><option value="<?= (int)$availableRole['id'] ?>"><?= esc($availableRole['name']) ?></option><?php endforeach; ?></select></div>
<div class="form-field">
<label for="staff-status">
<?= esc(lang('UserRole.membershipStatus')) ?>
</label>
<select id="staff-status" data-management-status>
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
<div class="staff-grid-wrap"><table class="studio-grid staff-table" data-grid-widths="25,21,22,20,12"><thead><tr><?php foreach(['Studio.staffIdentity','Studio.contactDetails','UserRole.role','UserRole.membershipStatus','Studio.actions'] as $label): ?><th scope="col"><?= esc(lang($label)) ?></th><?php endforeach; ?></tr></thead><tbody class="staff-rows">
<?php foreach($users as $staff): ?>
<?php $name=trim(($staff->first_name??'').' '.($staff->last_name??''))?:$staff->email;$active=($staff->membership_status??'active')==='active';$globallyDisabled=(int)($staff->active??0)!==1;$self=(int)$staff->id===$currentUserId;$roleNames=[];$roleIds=[];foreach(($staff->display_roles??[]) as $displayRole){$roleNames[]=is_array($displayRole)?($displayRole['name']??lang('UserRole.unknownRole')):($displayRole->name??lang('UserRole.unknownRole'));$roleIds[]=(string)(is_array($displayRole)?($displayRole['id']??$displayRole['role_id']??0):($displayRole->id??0));} ?>
<tr class="staff-row" data-management-row data-search="<?= esc(mb_strtolower($name.' '.$staff->email.' '.($staff->phone??'').' '.implode(' ',$roleNames)),'attr') ?>" data-roles="<?= esc(json_encode($roleIds),'attr') ?>" data-status="<?= $active?'active':'inactive' ?>">
<td class="staff-person">
<?php if($v4Can('users','view')): ?>
<a class="staff-primary" href="<?= esc(workflow_context_url('users/'.(int)$staff->id)) ?>">
<?= esc($name) ?>
</a>
<?php else: ?>
<strong>
<?= esc($name) ?>
</strong>
<?php endif; ?>
</td><td class="staff-contact"><span dir="ltr">
<?= esc($staff->email) ?>
</span>
<span dir="ltr">
<?= esc($staff->phone??'') ?>
</span>
</td>
<td class="staff-role">
<?php foreach($roleNames as $roleName): ?>
<span>
<?= esc($roleName) ?>
</span>
<?php endforeach; ?>
<?php if(!$roleNames): ?>
<span>
<?= esc(lang('UserRole.noRolesAssigned')) ?>
</span>
<?php endif; ?>
</td>
<td class="staff-state">
<span class="staff-badge <?= $active?'':'inactive' ?>">
<?= esc(lang('UserRole.'.($active?'activeMembership':'inactiveMembership'))) ?>
</span>
<?php if($globallyDisabled): ?>
<span class="staff-badge inactive" title="<?= esc(lang('UserRole.globallyDisabledWarning'),'attr') ?>">
<?= esc(lang('UserRole.globallyDisabled')) ?>
</span>
<?php endif; ?>
</td>
<td class="staff-row-actions"><div class="workspace-action-menu"><?= view('components/studio_row_menu_trigger',['rowMenuLabel'=>lang('Studio.actions').': '.$name,'rowMenuId'=>'staff-menu-'.(int)$staff->id]) ?><nav id="<?= esc('staff-menu-'.(int)$staff->id,'attr') ?>" popover="auto" data-studio-menu-panel>
<?php if($v4Can('users','edit')): ?>
<a class="button secondary" href="<?= esc(workflow_context_url('users/'.(int)$staff->id.'/edit')) ?>">
<?= esc(lang('UserRole.edit')) ?>
</a>
<?php if(!$self): ?>
<button type="button" class="button secondary" data-management-action data-url="<?= base_url('users/'.(int)$staff->id.'/toggle-status') ?>" data-method="POST" data-confirm="<?= esc(lang('UserRole.confirmToggleUserStatus'),'attr') ?>" <?= $globallyDisabled&&!$active?'disabled':'' ?>>
<?= esc(lang('UserRole.'.($active?'deactivate':'activate'))) ?>
</button>
<?php endif; ?>
<?php endif; ?>
<?php if(!$self&&$v4Can('users','delete')): ?>
<button type="button" class="button destructive" data-management-action data-url="<?= base_url('users/'.(int)$staff->id) ?>" data-method="DELETE" data-confirm="<?= esc(lang('UserRole.confirmRemoveClinicAccess'),'attr') ?>">
<?= esc(lang('UserRole.removeClinicAccess')) ?>
</button>
<?php endif; ?>
</nav></div></td>
</tr>
<?php endforeach; ?>
</tbody></table></div>
<div class="staff-empty" data-management-empty hidden>
<?= esc(lang('Studio.noMatchingRecords')) ?>
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
<?php if($v4Can('users','create')): ?><?= view('components/studio_native_create_dialog',['studioIcon'=>$studioIcon,'createId'=>'users-create-dialog','createFormId'=>'users-create-form','createUserForm'=>true,'createTitle'=>lang('UserRole.createNewUser'),'createAction'=>base_url('users/store'),'createSource'=>'studio_users_create','createReturn'=>'users','createFields'=>'users/partials/studio_form','createData'=>['userEditing'=>false,'editedUser'=>null,'roles'=>$roles??[],'departments'=>$departments??[]],'createFullPage'=>base_url('users/create?return_to=users'),'createSubmit'=>lang('UserRole.createUser')]) ?><?= view('components/email_check_script',['emailCheckUrl'=>base_url('users/check-email')]) ?><?php endif ?>
<?= $this->endSection() ?>
<?= $this->section('scripts') ?>
<script src="<?= base_url('assets/js/studio-management.js') ?>?v=<?= esc(asset_version('assets/js/studio-management.js')) ?>" defer>
</script>
<?= $this->endSection() ?>
