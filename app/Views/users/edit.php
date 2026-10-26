<?php helper('asset'); ?>
<?= $this->extend('layouts/studio') ?>
<?= $this->section('styles') ?>
<link rel="stylesheet" href="<?= base_url('assets/css/studio-forms.css') ?>?v=<?= esc(asset_version('assets/css/studio-forms.css')) ?>">
<link rel="stylesheet" href="<?= base_url('assets/css/studio-management.css') ?>?v=<?= esc(asset_version('assets/css/studio-management.css')) ?>">
<?= $this->endSection() ?>
<?= $this->section('content') ?>
<?php $userEditing=true; ?>
<header class="page-title-row">
<div>
<h1>
<?= esc(lang('UserRole.'.($userEditing?'editUser':'createNewUser'))) ?>
</h1>
</div>
<a href="<?= esc(workflow_back('users',$return_to??null)) ?>" class="button secondary">
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

<form action="<?= base_url($userEditing?'users/'.(int)$user->id.'/update':'users/store') ?>" method="POST" class="studio-form" <?= $userEditing?'':'data-user-create-form' ?>>
<?= csrf_field() ?>
<?php if(!empty($return_to)): ?>
<input type="hidden" name="return_to" value="<?= esc($return_to,'attr') ?>">
<?php endif; ?>
<?= view('users/partials/studio_form',['userEditing'=>$userEditing,'editedUser'=>$userEditing?$user:null,'roles'=>$roles??[],'departments'=>$departments??[]]) ?>
<footer class="form-actions">
<a href="<?= esc(workflow_back('users',$return_to??null)) ?>" class="button secondary">
<?= esc(lang('UserRole.cancel')) ?>
</a>
<button type="submit" class="button primary">
<?= esc(lang('UserRole.'.($userEditing?'updateUser':'createUser'))) ?>
</button>
</footer>
</form>
<?php if(!$userEditing): ?>
<?= view('components/email_check_script',['emailCheckUrl'=>base_url('users/check-email')]) ?>
<?php endif; ?>
<?= $this->endSection() ?>
