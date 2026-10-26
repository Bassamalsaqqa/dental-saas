<?= $this->extend('layouts/studio_access') ?>
<?= $this->section('content') ?>
<header>
<h2>
<?= esc(lang('Studio.noClinicTitle')) ?>
</h2>
<p>
<?= esc(lang('Studio.noClinicHelp')) ?>
</p>
</header>
<div class="access-actions">
<a class="access-button secondary" href="<?= base_url('auth/logout') ?>">
<?= esc(lang('Studio.signOut')) ?>
</a>
<?php if(!empty($is_super_admin)): ?>
<form action="<?= base_url('controlplane/enter') ?>" method="post">
<?= csrf_field() ?>
<button type="submit" class="access-button">
<?= esc(lang('Studio.enterControlPlane')) ?>
</button>
</form>
<?php endif; ?>
</div>
<?= $this->endSection() ?>
