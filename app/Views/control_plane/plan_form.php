<?php helper('asset');$plan=$plan??[];$limits=$limits??[];$features=$features??[];$mode=$mode??'create';$actionUrl=$mode==='edit'&&!empty($plan['id'])?base_url('controlplane/plans/'.(int)$plan['id']):base_url('controlplane/plans'); ?>
<?= $this->extend('layouts/studio_control_plane') ?>
<?= $this->section('styles') ?>
<link rel="stylesheet" href="<?= base_url('assets/css/studio-forms.css') ?>?v=<?= esc(asset_version('assets/css/studio-forms.css')) ?>">
<link rel="stylesheet" href="<?= base_url('assets/css/studio-management.css') ?>?v=<?= esc(asset_version('assets/css/studio-management.css')) ?>">
<?= $this->endSection() ?>
<?= $this->section('content') ?>
<header class="page-title-row">
<div>
<h1>
<?= esc(lang('Studio.'.($mode==='edit'?'planEdit':'planCreate'))) ?>
</h1>
</div>
<a class="button secondary" href="<?= base_url('controlplane/plans') ?>">
<?= esc(lang('UI.cpPlans')) ?>
</a>
</header>
<?php if(session('error')||session('errors')): ?>
<section class="form-notice form-notice--error" role="alert">
<h2>
<?= esc(lang('Studio.checkFields')) ?>
</h2>
<p>
<?= esc(strip_tags((string)session('error'))) ?>
</p>
<?php foreach((is_array(session('errors'))?session('errors'):[]) as $error): ?>
<p>
<?= esc($error) ?>
</p>
<?php endforeach; ?>
</section>
<?php endif; ?>
<form action="<?= esc($actionUrl,'attr') ?>" method="post" class="studio-form" id="platform-plan-form">
<?= csrf_field() ?>
<?= view('control_plane/partials/studio_plan_fields',array_merge($this->getData(),['plan'=>$plan,'limits'=>$limits,'features'=>$features])) ?>
<footer class="form-actions">
<a class="button secondary" href="<?= base_url('controlplane/plans') ?>">
<?= esc(lang('UI.cancel')) ?>
</a>
<button type="submit" class="button primary">
<?= esc(lang('Studio.'.($mode==='edit'?'planEdit':'planCreate'))) ?>
</button>
</footer>
</form>
<?= $this->endSection() ?>
