<?php $hide_nav = true; ?>
<?= $this->extend('layouts/control_plane') ?>

<?= $this->section('content') ?>
<div class="max-w-3xl space-y-6">
    <h1 class="text-lg font-black text-slate-800 uppercase tracking-widest">System Status</h1>
    <p class="text-sm text-slate-600">
        This surface is a read-only snapshot of control-plane status. No operational actions are available here.
    </p>
    <div class="text-xs text-slate-500 uppercase tracking-widest">
        Global Mode: <?= session()->get('global_mode') ? 'Active' : 'Inactive' ?>
    </div>
    <p class="text-xs text-slate-500">
        Operational actions are available only from dedicated control-plane surfaces.
    </p>
</div>
<?= $this->endSection() ?>
