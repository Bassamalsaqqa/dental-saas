<?php helper('asset'); ?>
<?= $this->extend('layouts/studio_control_plane') ?>
<?= $this->section('styles') ?>
<link rel="stylesheet" href="<?= base_url('assets/css/studio-forms.css') ?>?v=<?= esc(asset_version('assets/css/studio-forms.css')) ?>">
<link rel="stylesheet" href="<?= base_url('assets/css/studio-management.css') ?>?v=<?= esc(asset_version('assets/css/studio-management.css')) ?>">
<?= $this->endSection() ?>
<?= $this->section('content') ?>
<?= view('components/studio_activity_workspace',array_merge($this->getData(),['activity_plane'=>'platform'])) ?>
<?= $this->endSection() ?>
<?= $this->section('scripts') ?>
<script src="<?= base_url('assets/js/studio-activity.js') ?>?v=<?= esc(asset_version('assets/js/studio-activity.js')) ?>" defer>
</script>
<?= $this->endSection() ?>
