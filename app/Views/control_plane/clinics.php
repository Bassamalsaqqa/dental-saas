<?php helper('asset'); ?>
<?= $this->extend('layouts/studio_control_plane') ?>
<?= $this->section('styles') ?><link rel="stylesheet" href="<?= base_url('assets/css/studio-forms.css') ?>?v=<?= esc(asset_version('assets/css/studio-forms.css')) ?>"><link rel="stylesheet" href="<?= base_url('assets/css/studio-platform-directory.css') ?>?v=<?= esc(asset_version('assets/css/studio-platform-directory.css')) ?>"><?= $this->endSection() ?>
<?= $this->section('content') ?><?= view('components/studio_platform_directory',array_merge($this->getData(),['directory_mode'=>'clinics'])) ?><?= $this->endSection() ?>
<?= $this->section('scripts') ?><script src="<?= base_url('assets/js/studio-platform-directory.js') ?>?v=<?= esc(asset_version('assets/js/studio-platform-directory.js')) ?>" defer></script><?= $this->endSection() ?>
