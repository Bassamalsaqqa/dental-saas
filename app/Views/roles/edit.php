<?php helper('asset');$roleEditing=true;$roleLayout=session('global_mode')?'layouts/studio_control_plane':'layouts/studio'; ?>
<?= $this->extend($roleLayout) ?>
<?= $this->section('styles') ?>
<link rel="stylesheet" href="<?= base_url('assets/css/studio-forms.css') ?>?v=<?= esc(asset_version('assets/css/studio-forms.css')) ?>">
<link rel="stylesheet" href="<?= base_url('assets/css/studio-management.css') ?>?v=<?= esc(asset_version('assets/css/studio-management.css')) ?>">
<?= $this->endSection() ?>
<?= $this->section('content') ?>
<?php if(session('global_mode')): ?>
<link rel="stylesheet" href="<?= base_url('assets/css/studio-forms.css') ?>?v=<?= esc(asset_version('assets/css/studio-forms.css')) ?>">
<link rel="stylesheet" href="<?= base_url('assets/css/studio-management.css') ?>?v=<?= esc(asset_version('assets/css/studio-management.css')) ?>">
<?php endif; ?>
<div class="management-workspace">
<header class="page-title-row">
<div>
<h1>
<?= esc(($roleEditing?lang('Studio.editRole'):lang('UserRole.createNewRoleTitle'))) ?>
</h1>
</div>
<a class="button secondary" href="<?= esc(workflow_back($rolesRoute??'roles',$return_to??null)) ?>">
<?= esc(lang('UserRole.backToRoles')) ?>
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
<?= view('roles/partials/studio_form',['roleEditing'=>$roleEditing,'role'=>$role??[],'permissions'=>$permissions??[],'role_permissions'=>$role_permissions??[],'rolesRoute'=>$rolesRoute??'roles','return_to'=>$return_to??null]) ?>
</div>
<script src="<?= base_url('assets/js/studio-management.js') ?>?v=<?= esc(asset_version('assets/js/studio-management.js')) ?>" defer>
</script>
<?= $this->endSection() ?>