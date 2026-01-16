<?php
$hide_nav = true;
$title = 'SYSTEM STATUS';
?>
<?= $this->extend('layouts/control_plane') ?>

<?= $this->section('content') ?>
<div class="max-w-3xl space-y-8">
    <div>
        <h1 class="text-xs font-black text-slate-500 uppercase tracking-[0.3em]">Control Plane — System Status</h1>
        <p class="mt-3 text-sm text-slate-700">
            Read-only status surface. No actions, forms, or links are available on this page.
        </p>
    </div>

    <div class="border border-slate-200 bg-white p-6">
        <h2 class="text-[11px] font-bold text-slate-500 uppercase tracking-widest">Session State</h2>
        <p class="mt-3 text-sm text-slate-800">
            Global Mode: <span class="font-semibold"><?= session()->get('global_mode') ? 'Active' : 'Inactive' ?></span>
        </p>
    </div>

    <div class="text-xs text-slate-500">
        Operational actions are accessible only through dedicated control-plane surfaces.
    </div>
</div>
<?= $this->endSection() ?>
