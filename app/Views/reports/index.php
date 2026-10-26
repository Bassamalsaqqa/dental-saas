<?php helper('asset'); ?>
<?= $this->extend('layouts/studio') ?>
<?= $this->section('styles') ?><link rel="stylesheet" href="<?= base_url('assets/css/studio-forms.css') ?>?v=<?= esc(asset_version('assets/css/studio-forms.css')) ?>"><link rel="stylesheet" href="<?= base_url('assets/css/studio-reports.css') ?>?v=<?= esc(asset_version('assets/css/studio-reports.css')) ?>"><?= $this->endSection() ?>
<?= $this->section('content') ?><?= view('reports/content',$this->getData()) ?><?= $this->endSection() ?>
<?= $this->section('scripts') ?><script src="<?= base_url('assets/js/studio-patient-picker.js') ?>?v=<?= esc(asset_version('assets/js/studio-patient-picker.js')) ?>" defer></script><script src="<?= base_url('assets/js/studio-reports.js') ?>?v=<?= esc(asset_version('assets/js/studio-reports.js')) ?>" defer></script><?= $this->endSection() ?>
