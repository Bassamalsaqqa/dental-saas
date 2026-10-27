<?= $this->extend('layouts/studio_access') ?>
<?= $this->section('content') ?>
<header>
<h2 id="login-title">
<?= esc(lang('Auth.login_heading')) ?>
</h2>
<p>
<?= esc(lang('Auth.login_subheading')) ?>
</p>
</header>
<?php $accessMessage=$message??session()->getFlashdata('message');$accessSuccess=session()->getFlashdata('success');if($accessMessage||$accessSuccess): ?>
<div class="access-message <?= $accessSuccess?'success':'' ?>" role="<?= $accessSuccess?'status':'alert' ?>">
<?= esc(html_entity_decode(strip_tags(str_replace(['</p>','</li>','<br>','<br />'],"\n",(string)($accessSuccess?:$accessMessage))),ENT_QUOTES,'UTF-8')) ?>
</div>
<?php endif; ?>
<?= form_open('auth/login',['class'=>'access-form','novalidate'=>true,'aria-labelledby'=>'login-title']) ?>
<div class="access-field">
<label for="identity">
<?= esc(lang('Auth.login_identity_label')) ?>
</label>
<?= form_input($identity+['autocomplete'=>'username','autocapitalize'=>'off','spellcheck'=>'false','dir'=>'ltr','inputmode'=>'email']) ?>
</div>
<div class="access-field">
<label for="password">
<?= esc(lang('Auth.login_password_label')) ?>
</label>
<?= form_input($password+['autocomplete'=>'current-password','dir'=>'ltr']) ?>
</div>
<div class="access-options">
<label for="remember">
<input id="remember" name="remember" value="1" type="checkbox">
<?= esc(lang('Auth.login_remember_label')) ?>
</label>
<a href="<?= base_url('auth/forgot-password') ?>">
<?= esc(lang('Auth.login_forgot_short')) ?>
</a>
</div>
<button type="submit" class="access-button">
<?= esc(lang('Auth.login_submit_cta')) ?>
</button>
<?= form_close() ?>
<?= $this->endSection() ?>
