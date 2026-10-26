<?= $this->extend('layouts/studio_access') ?>
<?= $this->section('content') ?>
<header>
<h2 id="reset-title">
<?= esc(lang('Auth.reset_password_heading')) ?>
</h2>
<p>
<?= esc(lang('Studio.newPasswordHelp')) ?>
</p>
</header>
<?php $accessMessage=$message??session()->getFlashdata('message');$accessSuccess=session()->getFlashdata('success');if($accessMessage||$accessSuccess): ?>
<div class="access-message <?= $accessSuccess?'success':'' ?>" role="<?= $accessSuccess?'status':'alert' ?>">
<?= esc(html_entity_decode(strip_tags(str_replace(['</p>','</li>','<br>','<br />'],"\n",(string)($accessSuccess?:$accessMessage))),ENT_QUOTES,'UTF-8')) ?>
</div>
<?php endif; ?>
<?= form_open('auth/reset-password/'.$code,['class'=>'access-form','aria-labelledby'=>'reset-title']) ?>
<div class="access-field">
<label for="new">
<?= esc(sprintf(lang('Auth.reset_password_new_password_label'),$minPasswordLength)) ?>
</label>
<?= form_input($new_password+['autocomplete'=>'new-password','dir'=>'ltr']) ?>
</div>
<div class="access-field">
<label for="new_confirm">
<?= esc(lang('Auth.reset_password_new_password_confirm_label')) ?>
</label>
<?= form_input($new_password_confirm+['autocomplete'=>'new-password','dir'=>'ltr']) ?>
</div>
<?= form_input($user_id) ?>
<button type="submit" name="submit" class="access-button">
<?= esc(lang('Auth.reset_password_submit_btn')) ?>
</button>
<?= form_close() ?>
<?= $this->endSection() ?>
