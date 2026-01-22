<?= $this->extend('layouts/main_control_plane') ?>

<?= $this->section('content') ?>
<div class="max-w-6xl mx-auto">
    <div class="mb-8">
        <p class="text-xs uppercase tracking-widest text-slate-400 font-semibold">Control Plane</p>
        <h1 class="text-2xl font-bold text-slate-900">Email Templates</h1>
        <p class="text-sm text-slate-500">Manage global messaging templates for clinics.</p>
    </div>

    <div class="bg-white border border-slate-200 rounded-2xl p-8 text-center text-slate-500">
        <i class="fas fa-envelope-open-text text-3xl text-slate-300 mb-3"></i>
        <p class="text-sm font-semibold text-slate-700">Template library will be added next.</p>
        <p class="text-xs text-slate-500 mt-1">Include preview + safe editing tools.</p>
    </div>
</div>
<?= $this->endSection() ?>
