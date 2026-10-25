<?= $this->extend('layouts/main_auth') ?>
<?= $this->section('content') ?>
<?php $validation=$validation??null; ?>
<div class="v4-task-page">
<header class="v4-page-heading"><div><h1><?= esc(lang('UI.patientCreateTitle')) ?></h1><p><?= esc(lang('UI.patientCreateSubtitle')) ?></p></div><a class="v4-button" href="<?= esc(workflow_back('patient')) ?>"><i class="fas fa-arrow-left rtl-back-icon" aria-hidden="true"></i><?= esc(lang('UI.patientBack')) ?></a></header>
<?php if(session('error')): ?><div class="v4-form-error" role="alert"><?= esc(session('error')) ?></div><?php endif; ?>
<form action="<?= base_url('patient/store') ?>" method="POST" class="v4-form v4-task-form" data-patient-create-form>
<?= csrf_field() ?><?php if(!empty($return_to)): ?><input type="hidden" name="return_to" value="<?= esc($return_to) ?>"><?php endif; ?>
<?= view('patient/partials/create_form_fields',['validation'=>$validation,'is_rtl'=>$is_rtl??false,'sectionClass'=>'v4-form-section']) ?>
<footer class="v4-task-footer"><a class="v4-button" href="<?= esc(workflow_cancel('patient',$return_to??null)) ?>"><?= esc(lang('UI.cancel')) ?></a><button type="submit" name="post_submit_action" value="save_only" class="v4-button"><?= esc(lang('UI.settingsSave')) ?></button><button type="submit" name="post_submit_action" value="save_and_book" class="v4-button v4-button--primary"><i class="fas fa-calendar-plus" aria-hidden="true"></i><?= esc(lang('UI.patientSaveAndBookAppointment')) ?></button></footer>
</form></div>
<?= view('patient/partials/create_form_script') ?>
<?= $this->endSection() ?>
