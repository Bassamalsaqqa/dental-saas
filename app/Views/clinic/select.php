<?= $this->extend('layouts/studio_access') ?>
<?= $this->section('content') ?>
<?php include APPPATH.'Views/layouts/partials/studio_icons.php'; ?>
<header>
<h2>
<?= esc(lang('Studio.chooseClinic')) ?>
</h2>
<p>
<?= esc(lang('Studio.chooseClinicHelp')) ?>
</p>
</header>
<?php if(session('error')): ?>
<div class="access-message" role="alert">
<?= esc(session('error')) ?>
</div>
<?php endif; ?>
<?= form_open('clinic/select',['class'=>'access-clinics']) ?>
<?php foreach($clinics as $clinic): ?>
<button type="submit" name="clinic_id" value="<?= esc($clinic['clinic_id'],'attr') ?>" class="access-clinic">
<?= $studioIcon('clinic') ?>
<span>
<strong>
<?= esc($clinic['clinic_name']) ?>
</strong>
<small>
<?= esc(lang('Studio.selectClinicAction')) ?>
</small>
</span>
</button>
<?php endforeach; ?>
<?= form_close() ?>
<a class="access-back" href="<?= base_url('auth/logout') ?>">
<?= esc(lang('Studio.signOut')) ?>
</a>
<?= $this->endSection() ?>
