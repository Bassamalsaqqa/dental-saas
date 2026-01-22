<?= $this->extend('layouts/main_control_plane') ?>

<?= $this->section('content') ?>
<div class="space-y-8">
    <div class="glass-card rounded-3xl p-6 md:p-8">
        <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-6">
            <div>
                <p class="text-xs uppercase tracking-[0.3em] text-emerald-700 font-semibold">Clinics</p>
                <h1 class="text-3xl font-semibold text-slate-900 mt-2">Manage clinics and subscriptions</h1>
                <p class="mt-3 text-sm text-slate-600 max-w-2xl">
                    Provision new clinics, inspect status, and prepare lifecycle actions without leaving the control plane.
                </p>
            </div>
            <a href="<?= base_url('controlplane/onboarding/clinic/create') ?>"
               class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-emerald-600 text-white text-sm font-semibold shadow hover:bg-emerald-700">
                <i class="fas fa-plus"></i>
                Onboard Clinic
            </a>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div class="glass-card rounded-2xl p-4">
            <p class="text-xs uppercase tracking-widest text-slate-400 font-semibold">Total Clinics</p>
            <p class="text-2xl font-semibold text-slate-900 mt-1"><?= number_format($stats['total'] ?? 0) ?></p>
        </div>
        <div class="glass-card rounded-2xl p-4">
            <p class="text-xs uppercase tracking-widest text-slate-400 font-semibold">Active Clinics</p>
            <p class="text-2xl font-semibold text-emerald-600 mt-1"><?= number_format($stats['active'] ?? 0) ?></p>
        </div>
        <div class="glass-card rounded-2xl p-4">
            <p class="text-xs uppercase tracking-widest text-slate-400 font-semibold">Plan Coverage</p>
            <p class="text-sm text-slate-600 mt-2">Ensure every clinic has an active plan.</p>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6">
        <?php foreach ($cards as $card): ?>
            <?php
            $clinic = $card['clinic'];
            $subscription = $card['subscription'];
            $status = $clinic['status'] ?? 'inactive';
            $statusLabel = ucfirst($status);
            $statusClasses = $status === 'active'
                ? 'bg-emerald-100 text-emerald-700'
                : ($status === 'suspended' ? 'bg-amber-100 text-amber-700' : 'bg-slate-100 text-slate-600');
            ?>
            <div class="glass-card rounded-3xl p-6">
                <div class="flex items-start justify-between">
                    <div>
                        <h3 class="text-lg font-semibold text-slate-900"><?= esc($clinic['name']) ?></h3>
                        <p class="text-xs text-slate-500">Clinic ID: <?= esc($clinic['id']) ?></p>
                    </div>
                    <span class="text-xs font-semibold px-2 py-1 rounded-full <?= $statusClasses ?>"><?= esc($statusLabel) ?></span>
                </div>

                <div class="mt-4 space-y-2 text-sm text-slate-600">
                    <div class="flex items-center justify-between">
                        <span class="text-slate-400">Plan</span>
                        <span class="font-semibold text-slate-800"><?= esc($card['plan_name']) ?></span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-slate-400">Start Date</span>
                        <span><?= esc($subscription['start_at'] ?? '--') ?></span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-slate-400">End Date</span>
                        <span><?= esc($subscription['end_at'] ?? '--') ?></span>
                    </div>
                </div>

                <div class="mt-5 flex flex-wrap gap-2">
                    <a href="<?= base_url('controlplane/clinics/' . $clinic['id']) ?>"
                       class="px-3 py-1.5 text-xs font-semibold rounded-lg bg-slate-100 text-slate-600 hover:bg-slate-200">
                        View Details
                    </a>
                    <button class="px-3 py-1.5 text-xs font-semibold rounded-lg bg-emerald-50 text-emerald-700 cursor-not-allowed" disabled>
                        Upgrade Plan
                    </button>
                    <button class="px-3 py-1.5 text-xs font-semibold rounded-lg bg-amber-50 text-amber-700 cursor-not-allowed" disabled>
                        Suspend
                    </button>
                    <button class="px-3 py-1.5 text-xs font-semibold rounded-lg bg-rose-50 text-rose-700 cursor-not-allowed" disabled>
                        Terminate
                    </button>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>
<?= $this->endSection() ?>
