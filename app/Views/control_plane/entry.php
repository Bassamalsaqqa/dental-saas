<?php $hide_cp_nav = true; ?>
<?= $this->extend('layouts/main_control_plane') ?>

<?= $this->section('content') ?>
<div class="flex min-h-[70vh] items-center justify-center">
    <div class="w-full max-w-md bg-white border border-gray-200 shadow-xl rounded-2xl p-8 text-center">
        <div class="mb-6">
            <div class="w-16 h-16 bg-indigo-50 rounded-full flex items-center justify-center mx-auto mb-4">
                <i class="fas fa-lock text-indigo-500 text-2xl"></i>
            </div>
            <p class="text-xs uppercase tracking-widest text-gray-400 font-semibold mb-2">Control Plane</p>
            <h1 class="text-xl font-bold text-gray-900 mb-2">Restricted Access</h1>
            <p class="text-sm text-gray-500">Enter Global Mode to unlock control plane tools.</p>
        </div>

        <form action="<?= base_url('controlplane/enter') ?>" method="post">
            <?= csrf_field() ?>
            <button type="submit" class="w-full flex justify-center items-center px-4 py-3 text-sm font-semibold rounded-lg text-white bg-slate-900 hover:bg-slate-800 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-slate-900 transition-all">
                Enter Global Mode
            </button>
        </form>
    </div>
</div>
<?= $this->endSection() ?>
