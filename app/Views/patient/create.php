<?php helper('asset'); include APPPATH.'Views/layouts/partials/studio_icons.php'; ?>
<?= $this->extend('layouts/studio') ?>
<?= $this->section('styles') ?>
<link rel="stylesheet" href="<?= base_url('assets/css/studio-forms.css') ?>?v=<?= esc(asset_version('assets/css/studio-forms.css')) ?>">
<?= $this->endSection() ?>
<?= $this->section('content') ?>
<?php
$validation=$validation??null;
$studioFormErrors=is_array(session('errors'))?session('errors'):[];
if($validation instanceof \CodeIgniter\Validation\Validation)$studioFormErrors=array_merge($studioFormErrors,$validation->getErrors());
?>
<header class="page-title-row registration-heading"><div><h1><?= esc(lang('UI.patientCreateTitle')) ?></h1></div><a class="button secondary" href="<?= esc(workflow_cancel('patient',$return_to??null)) ?>"><?= esc(lang('Studio.registry')) ?></a></header>
<?php if(session('error') || $studioFormErrors): ?><section class="form-notice form-notice--error" role="alert" aria-labelledby="form-error-title"><h2 id="form-error-title"><?= esc(lang('Studio.checkFields')) ?></h2><?php if(session('error')): ?><p><?= esc(session('error')) ?></p><?php endif; ?><?php if($studioFormErrors): ?><ul><?php foreach($studioFormErrors as $key=>$message): ?><li><a href="#<?= esc($key,'attr') ?>"><?= esc($message) ?></a></li><?php endforeach; ?></ul><?php endif; ?></section><?php endif; ?>
<form action="<?= base_url('patient/store') ?>" method="POST" class="studio-form" data-patient-create-form data-unsaved-form>
<?= csrf_field() ?>
<?php if(!empty($return_to)): ?><input type="hidden" name="return_to" value="<?= esc($return_to,'attr') ?>"><?php endif; ?>
<?= view('patient/partials/studio_create_fields',['validation'=>$validation,'studioFormErrors'=>$studioFormErrors,'is_rtl'=>$is_rtl??false]) ?>
<footer class="form-actions"><a class="button secondary" href="<?= esc(workflow_cancel('patient',$return_to??null)) ?>" data-form-cancel><?= esc(lang('UI.cancel')) ?></a><div><button class="button secondary" type="submit" name="post_submit_action" value="save_only"><?= esc(lang('UI.settingsSave')) ?></button><button class="button primary" type="submit" name="post_submit_action" value="save_and_book"><?= esc(lang('UI.patientSaveAndBookAppointment')) ?></button></div></footer>
</form>
<dialog id="form-discard-dialog" class="help-dialog" aria-labelledby="discard-title"><header><div><h2 id="discard-title"><?= esc(lang('Studio.discardTitle')) ?></h2><p><?= esc(lang('Studio.discardHelp')) ?></p></div><button class="icon-button" data-close aria-label="<?= esc(lang('Studio.close')) ?>"><?= $studioIcon('close') ?></button></header><div class="discard-actions"><button class="button secondary" type="button" data-discard-stay><?= esc(lang('Studio.keepEditing')) ?></button><button class="button primary" type="button" data-discard-confirm><?= esc(lang('Studio.discardChanges')) ?></button></div></dialog>
<?= view('patient/partials/create_form_script') ?>
<?= $this->endSection() ?>
<?= $this->section('scripts') ?><script src="<?= base_url('assets/js/studio-forms.js') ?>?v=<?= esc(asset_version('assets/js/studio-forms.js')) ?>" defer></script><?= $this->endSection() ?>
