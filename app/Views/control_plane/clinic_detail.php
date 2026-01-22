<?= $this->extend('layouts/main_control_plane') ?>

<?= $this->section('content') ?>
<div class="max-w-5xl mx-auto">
    <div class="flex items-center justify-between mb-8">
        <div>
            <p class="text-xs uppercase tracking-widest text-slate-400 font-semibold">Control Plane</p>
            <h1 class="text-2xl font-bold text-slate-900"><?= esc($clinic['name']) ?></h1>
            <p class="text-sm text-slate-500">Clinic overview, subscription status, and lifecycle actions.</p>
        </div>
        <span class="text-xs font-semibold px-3 py-1 rounded-full bg-slate-100 text-slate-600">
            Status: <?= esc(ucfirst($clinic['status'] ?? 'inactive')) ?>
        </span>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
        <div class="bg-white border border-slate-200 rounded-2xl p-6">
            <h2 class="text-sm font-bold text-slate-700 mb-4">Clinic Details</h2>
            <div class="space-y-3 text-sm text-slate-600">
                <div class="flex items-center justify-between">
                    <span class="text-slate-500">Clinic ID</span>
                    <span class="font-semibold text-slate-800"><?= esc($clinic['id']) ?></span>
                </div>
                <div class="flex items-center justify-between">
                    <span class="text-slate-500">Slug</span>
                    <span><?= esc($clinic['slug'] ?? '—') ?></span>
                </div>
                <div class="flex items-center justify-between">
                    <span class="text-slate-500">Created</span>
                    <span><?= esc($clinic['created_at'] ?? '—') ?></span>
                </div>
            </div>
        </div>

        <div class="bg-white border border-slate-200 rounded-2xl p-6">
            <h2 class="text-sm font-bold text-slate-700 mb-4">Subscription</h2>
            <div class="space-y-3 text-sm text-slate-600">
                <div class="flex items-center justify-between">
                    <span class="text-slate-500">Plan</span>
                    <span class="font-semibold text-slate-800"><?= esc($plan_name ?? 'Unassigned') ?></span>
                </div>
                <div class="flex items-center justify-between">
                    <span class="text-slate-500">Status</span>
                    <span><?= esc($subscription['status'] ?? 'none') ?></span>
                </div>
                <div class="flex items-center justify-between">
                    <span class="text-slate-500">Start</span>
                    <span><?= esc($subscription['start_at'] ?? '—') ?></span>
                </div>
                <div class="flex items-center justify-between">
                    <span class="text-slate-500">End</span>
                    <span><?= esc($subscription['end_at'] ?? '—') ?></span>
                </div>
            </div>
        </div>
    </div>

    <div class="bg-white border border-slate-200 rounded-2xl p-6">
        <h2 class="text-sm font-bold text-slate-700 mb-4">Lifecycle Actions</h2>
        <div class="flex flex-wrap gap-3">
            <button class="px-4 py-2 rounded-lg bg-emerald-50 text-emerald-700 text-sm font-semibold cursor-not-allowed" disabled>
                Start Subscription
            </button>
            <button class="px-4 py-2 rounded-lg bg-blue-50 text-blue-700 text-sm font-semibold cursor-not-allowed" disabled>
                Upgrade Plan
            </button>
            <button class="px-4 py-2 rounded-lg bg-amber-50 text-amber-700 text-sm font-semibold cursor-not-allowed" disabled>
                Suspend Clinic
            </button>
            <button class="px-4 py-2 rounded-lg bg-rose-50 text-rose-700 text-sm font-semibold cursor-not-allowed" disabled>
                Terminate Clinic
            </button>
        </div>
        <p class="text-xs text-slate-500 mt-3">Lifecycle actions will be wired after Phase 1 backend readiness.</p>
    </div>
</div>
<?= $this->endSection() ?>
