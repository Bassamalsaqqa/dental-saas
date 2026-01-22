<?= $this->extend('layouts/main_control_plane') ?>

<?= $this->section('content') ?>
<div class="max-w-6xl mx-auto">
    <div class="flex items-center justify-between mb-8">
        <div>
            <p class="text-xs uppercase tracking-widest text-slate-400 font-semibold">Control Plane</p>
            <h1 class="text-2xl font-bold text-slate-900">Plans</h1>
            <p class="text-sm text-slate-500">Define pricing, duration, and limits for clinics.</p>
        </div>
        <button class="px-4 py-2 rounded-lg bg-emerald-600 text-white text-sm font-semibold shadow cursor-not-allowed" disabled>
            + Create Plan
        </button>
    </div>

    <div class="bg-white border border-slate-200 rounded-2xl p-8 text-center text-slate-500">
        <i class="fas fa-layer-group text-3xl text-slate-300 mb-3"></i>
        <p class="text-sm font-semibold text-slate-700">Plan management UI is staged.</p>
        <p class="text-xs text-slate-500 mt-1">Next: plan cards + configurator form + duration/limits.</p>
    </div>
</div>
<?= $this->endSection() ?>
