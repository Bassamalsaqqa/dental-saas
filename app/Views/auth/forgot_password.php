<?= $this->extend('layouts/studio_access') ?>
<?= $this->section('content') ?>
<header>
<h2 id="recover-title">
<?= esc(lang('Studio.recoverAccess')) ?>
</h2>
<p>
<?= esc(lang('Studio.recoverHelp')) ?>
</p>
</header>
<?php $accessMessage=$message??session()->getFlashdata('message');$accessSuccess=session()->getFlashdata('success');if($accessMessage||$accessSuccess): ?>
<div class="access-message <?= $accessSuccess?'success':'' ?>" role="<?= $accessSuccess?'status':'alert' ?>">
<?= esc(html_entity_decode(strip_tags(str_replace(['</p>','</li>','<br>','<br />'],"\n",(string)($accessSuccess?:$accessMessage))),ENT_QUOTES,'UTF-8')) ?>
</div>
<?php endif; ?>
<?= form_open('auth/forgot-password',['class'=>'access-form','novalidate'=>true,'aria-labelledby'=>'recover-title']) ?>
<div class="access-field">
<label for="identity">
<?= esc($identity_label??lang('Auth.forgot_password_identity_label')) ?>
</label>
<?= form_input($identity+['autocomplete'=>'username','dir'=>'ltr']) ?>
</div>
<button type="submit" class="access-button">
<?= esc(lang('Auth.forgot_password_submit_btn')) ?>
</button>
<?= form_close() ?>
<a class="access-back" href="<?= base_url('auth/login') ?>">
<?= esc(lang('Studio.backToLogin')) ?>
</a>
<?= $this->endSection() ?>
