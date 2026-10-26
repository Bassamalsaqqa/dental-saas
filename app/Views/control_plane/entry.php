<?= $this->extend('layouts/studio_access') ?>
<?= $this->section('accessIntroduction') ?>
<?php include APPPATH.'Views/layouts/partials/studio_icons.php'; ?>
<div class="access-emblem">
<?= $studioIcon('shield') ?>
</div>
<h1>
<?= esc(lang('UI.cpControlPlane')) ?>
</h1>
<p>
<?= esc(lang('UI.cpGlobalAdmin')) ?>
</p>
<div class="access-capabilities">
<?php foreach([['clinic','UI.cpClinics'],['people','UI.cpUsers'],['chart','UI.cpPlans']] as [$icon,$label]): ?>
<div>
<?= $studioIcon($icon) ?>
<span>
<?= esc(lang($label)) ?>
</span>
</div>
<?php endforeach; ?>
</div>
<?= $this->endSection() ?>
<?= $this->section('content') ?>
<header>
<h2>
<?= esc(lang('Studio.platformEnterTitle')) ?>
</h2>
<p>
<?= esc(lang('Studio.platformEnterHelp')) ?>
</p>
</header>
<form action="<?= base_url('controlplane/enter') ?>" method="post" class="access-form">
<?= csrf_field() ?>
<button type="submit" class="access-button">
<?= esc(lang('Studio.platformEnter')) ?>
</button>
</form>
<a class="access-back" href="<?= base_url('auth/logout') ?>">
<?= esc(lang('Studio.signOut')) ?>
</a>
<?= $this->endSection() ?>
