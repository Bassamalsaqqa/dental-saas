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
<?= esc(lang('UserRole.changePassword')) ?>
</h1>
</div>
<a class="button secondary" href="<?= esc(workflow_back('users',$return_to??null)) ?>">
<?= esc(lang('UserRole.backToUsers')) ?>
</a>
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
<form action="<?= base_url('users/'.(int)$user->id.'/update-password') ?>" method="POST" class="studio-form" data-password-form>
<?= csrf_field() ?>
<?php if(!empty($return_to)): ?>
<input type="hidden" name="return_to" value="<?= esc($return_to,'attr') ?>">
<?php endif; ?>
<section class="form-section">
<header>
<h2>
<?= esc(trim(($user->first_name??'').' '.($user->last_name??''))) ?>
</h2>
<p>
<?= esc($user->email) ?>
</p>
</header>
<div class="password-grid">
<?php foreach([['current_password','currentPasswordRequired','current-password'],['new_password','newPasswordRequired','new-password'],['confirm_password','confirmNewPasswordRequired','new-password']] as [$field,$label,$autocomplete]): ?>
<div class="form-field">
<label for="<?= $field ?>">
<?= esc(lang('UserRole.'.$label)) ?>
</label>
<input id="<?= $field ?>" name="<?= $field ?>" type="password" dir="ltr" required autocomplete="<?= $autocomplete ?>" <?= $field==='new_password'?'minlength="8"':'' ?>>
</div>
<?php endforeach; ?>
</div>
<p class="password-policy">
<?= esc(lang('UserRole.passwordRuleMinLength')) ?>
</p>
</section>
<footer class="form-actions">
<a class="button secondary" href="<?= esc(workflow_back('users',$return_to??null)) ?>">
<?= esc(lang('UserRole.cancel')) ?>
</a>
<button class="button primary" type="submit">
<?= esc(lang('UserRole.changePassword')) ?>
</button>
</footer>
</form>
<?= $this->endSection() ?>
<?= $this->section('scripts') ?>
<script src="<?= base_url('assets/js/studio-management.js') ?>?v=<?= esc(asset_version('assets/js/studio-management.js')) ?>" defer>
</script>
<?= $this->endSection() ?>
