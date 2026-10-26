<?php helper('asset'); ?>
<?= $this->extend('layouts/studio') ?>
<?= $this->section('styles') ?>
<link rel="stylesheet" href="<?= base_url('assets/css/studio-forms.css') ?>?v=<?= esc(asset_version('assets/css/studio-forms.css')) ?>">
<link rel="stylesheet" href="<?= base_url('assets/css/studio-management.css') ?>?v=<?= esc(asset_version('assets/css/studio-management.css')) ?>">
<?= $this->endSection() ?>
<?= $this->section('content') ?>
<header class="page-title-row">
<div>
<h1>
<?= esc(lang('TenantMisc.profileManagement')) ?>
</h1>
</div>
</header>
<?php $managementErrors=is_array(session('errors'))?session('errors'):[];$managementValidation=session('validation');if($managementValidation instanceof \CodeIgniter\Validation\Validation)$managementErrors=array_merge($managementErrors,$managementValidation->getErrors());if(session('error')||$managementErrors): ?>
<section class="form-notice form-notice--error" role="alert">
<h2>
<?= esc(lang('Studio.checkFields')) ?>
</h2>
<?php if(session('error')): ?>
<p>
<?= esc(strip_tags((string)session('error'))) ?>
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
<div class="account-layout">
<div class="account-main">
<form action="<?= base_url('profile/update') ?>" method="POST" class="studio-form">
<?= csrf_field() ?>
<section class="form-section">
<header>
<h2>
<?= esc(lang('TenantMisc.personalInformation')) ?>
</h2>
</header>
<div class="account-identity-grid">
<?php foreach([['first_name','firstName','text'],['last_name','lastName','text'],['email','emailAddress','email'],['phone','phoneNumber','tel']] as [$field,$label,$type]): ?>
<div class="form-field">
<label for="<?= $field ?>">
<?= esc(lang('TenantMisc.'.$label)) ?>
</label>
<input type="<?= $type ?>" id="<?= $field ?>" name="<?= $field ?>" value="<?= esc(old($field,$user->$field??''),'attr') ?>" <?= $field==='phone'?'':'required' ?> <?= in_array($type,['email','tel'],true)?'dir="ltr"':'' ?> autocomplete="<?= ['first_name'=>'given-name','last_name'=>'family-name','email'=>'email','phone'=>'tel'][$field] ?>">
</div>
<?php endforeach; ?>
</div>
</section>
<footer class="form-actions">
<button type="submit" class="button primary">
<?= esc(lang('TenantMisc.updateProfile')) ?>
</button>
</footer>
</form>
<section class="form-section account-section">
<header>
<h2>
<?= esc(lang('Studio.accountGroups')) ?>
</h2>
</header>
<div class="account-groups">
<?php foreach(($user_groups??[]) as $group): ?>
<span>
<?= esc(ucfirst($group->name)) ?>
</span>
<?php endforeach; ?>
<?php if(empty($user_groups)): ?>
<p>
<?= esc(lang('TenantMisc.noSpecificPermissions')) ?>
</p>
<?php endif; ?>
</div>
</section>
<section class="form-section account-section">
<header>
<h2>
<?= esc(lang('TenantMisc.accountInformation')) ?>
</h2>
</header>
<dl class="account-facts">
<div>
<dt>
<?= esc(lang('TenantMisc.userIdLabel')) ?>
</dt>
<dd>#<?= (int)$user->id ?>
</dd>
</div>
<div>
<dt>
<?= esc(lang('TenantMisc.statusLabel')) ?>
</dt>
<dd>
<?= esc(lang('TenantMisc.'.($user->active?'statusActive':'statusInactive'))) ?>
</dd>
</div>
<div>
<dt>
<?= esc(lang('TenantMisc.lastLoginLabel')) ?>
</dt>
<dd>
<?= esc($user->last_login?date('M j, Y g:i A',$user->last_login):lang('TenantMisc.never')) ?>
</dd>
</div>
<div>
<dt>
<?= esc(lang('TenantMisc.createdLabel')) ?>
</dt>
<dd>
<?= esc(formatDate($user->created_on)) ?>
</dd>
</div>
</dl>
</section>
</div>
<form action="<?= base_url('profile/change-password') ?>" method="POST" class="studio-form account-security" data-password-form>
<?= csrf_field() ?>
<section class="form-section">
<header>
<h2>
<?= esc(lang('TenantMisc.changePassword')) ?>
</h2>
</header>
<div class="security-fields">
<?php foreach([['current_password','currentPassword','current-password'],['new_password','newPassword','new-password'],['confirm_password','confirmPassword','new-password']] as [$field,$label,$autocomplete]): ?>
<div class="form-field">
<label for="<?= $field ?>">
<?= esc(lang('TenantMisc.'.$label)) ?>
</label>
<input id="<?= $field ?>" name="<?= $field ?>" type="password" autocomplete="<?= $autocomplete ?>" dir="ltr" required>
</div>
<?php endforeach; ?>
</div>
<div class="account-save">
<button type="submit" class="button primary">
<?= esc(lang('TenantMisc.changePassword')) ?>
</button>
</div>
</section>
</form>
</div>
<?= $this->endSection() ?>
<?= $this->section('scripts') ?>
<script src="<?= base_url('assets/js/studio-management.js') ?>?v=<?= esc(asset_version('assets/js/studio-management.js')) ?>" defer>
</script>
<?= $this->endSection() ?>