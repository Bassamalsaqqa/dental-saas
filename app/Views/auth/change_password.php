<?= $this->extend('layouts/studio_access') ?>
<?= $this->section('content') ?>
<header>
<h2>
<?= esc(lang('UserRole.changePassword')) ?>
</h2>
</header>
<?php $accessMessage=$message??session('message');if($accessMessage): ?>
<div class="access-message" role="alert">
<?= esc(html_entity_decode(strip_tags((string)$accessMessage),ENT_QUOTES,'UTF-8')) ?>
</div>
<?php endif; ?>
<?= form_open('auth/change-password',['class'=>'access-form','data-access-password'=>'']) ?>
<div class="access-field">
<label for="old">
<?= esc(lang('TenantMisc.currentPassword')) ?>
</label>
<?= form_input($old_password+['autocomplete'=>'current-password','dir'=>'ltr']) ?>
</div>
<div class="access-field">
<label for="new">
<?= esc(sprintf(lang('Auth.reset_password_new_password_label'),$minPasswordLength)) ?>
</label>
<?= form_input($new_password+['autocomplete'=>'new-password','dir'=>'ltr']) ?>
</div>
<div class="access-field">
<label for="new_confirm">
<?= esc(lang('TenantMisc.confirmPassword')) ?>
</label>
<?= form_input($new_password_confirm+['autocomplete'=>'new-password','dir'=>'ltr']) ?>
</div>
<?= form_hidden($user_id) ?>
<button type="submit" class="access-button">
<?= esc(lang('UserRole.changePassword')) ?>
</button>
<?= form_close() ?>
<a class="access-back" href="<?= base_url('dashboard') ?>">
<?= esc(lang('Studio.overview')) ?>
</a>
<?= $this->endSection() ?>
