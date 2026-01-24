<?= $this->extend('layouts/main_control_plane') ?>

<?= $this->section('content') ?>
<style>
    .toggle-switch {
        position: relative;
        display: inline-flex;
        width: 44px;
        height: 24px;
        align-items: center;
    }
    .toggle-switch input {
        opacity: 0;
        width: 0;
        height: 0;
    }
    .toggle-track {
        position: absolute;
        inset: 0;
        border-radius: 9999px;
        background: #e2e8f0;
        border: 1px solid rgba(15, 23, 42, 0.15);
        transition: background 0.2s ease;
    }
    .toggle-thumb {
        position: absolute;
        top: 2px;
        left: 2px;
        width: 18px;
        height: 18px;
        border-radius: 9999px;
        background: #ffffff;
        box-shadow: 0 2px 6px rgba(15, 23, 42, 0.2);
        transition: transform 0.2s ease;
    }
    .toggle-switch input:checked + .toggle-track {
        background: #10b981;
    }
    .toggle-switch input:checked + .toggle-track .toggle-thumb {
        transform: translateX(20px);
    }
</style>
<div class="space-y-8">
    <div class="glass-card rounded-3xl p-6 md:p-8">
        <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-6">
            <div>
                <p class="text-xs uppercase tracking-[0.3em] text-emerald-700 font-semibold">Plans</p>
                <h1 class="text-3xl font-semibold text-slate-900 mt-2">Plan catalog</h1>
                <p class="mt-3 text-sm text-slate-600 max-w-2xl">
                    Define pricing, duration, and entitlement caps for clinics.
                </p>
            </div>
            <a href="<?= base_url('controlplane/plans/create') ?>"
               class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-emerald-600 text-white text-sm font-semibold shadow">
                <i class="fas fa-plus"></i>
                Create Plan
            </a>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div class="glass-card rounded-2xl p-4">
            <p class="text-xs uppercase tracking-widest text-slate-400 font-semibold">Total Plans</p>
            <p class="text-2xl font-semibold text-slate-900 mt-1"><?= number_format($stats['total'] ?? 0) ?></p>
        </div>
        <div class="glass-card rounded-2xl p-4">
            <p class="text-xs uppercase tracking-widest text-slate-400 font-semibold">Active Plans</p>
            <p class="text-2xl font-semibold text-emerald-600 mt-1"><?= number_format($stats['active'] ?? 0) ?></p>
        </div>
        <div class="glass-card rounded-2xl p-4">
            <p class="text-xs uppercase tracking-widest text-slate-400 font-semibold">In View</p>
            <p class="text-2xl font-semibold text-slate-900 mt-1"><?= number_format($stats['filtered'] ?? 0) ?></p>
            <p class="text-xs text-slate-500 mt-1">Inactive: <?= number_format($stats['inactive'] ?? 0) ?></p>
        </div>
    </div>

    <form method="get" class="glass-card rounded-2xl p-5 flex flex-col lg:flex-row lg:items-end gap-4">
        <div class="flex-1">
            <label class="text-xs uppercase tracking-widest text-slate-400 font-semibold">Search</label>
            <input type="text" name="q" value="<?= esc($filters['q'] ?? '') ?>" placeholder="Plan name"
                   class="mt-2 w-full rounded-xl border border-slate-200 px-3 py-2 text-sm">
        </div>
        <div class="w-full lg:w-56">
            <label class="text-xs uppercase tracking-widest text-slate-400 font-semibold">Status</label>
            <?php $currentStatus = $filters['status'] ?? ''; ?>
            <select name="status" class="mt-2 w-full rounded-xl border border-slate-200 px-3 py-2 text-sm">
                <option value="" <?= $currentStatus === '' ? 'selected' : '' ?>>All statuses</option>
                <option value="active" <?= $currentStatus === 'active' ? 'selected' : '' ?>>Active</option>
                <option value="inactive" <?= $currentStatus === 'inactive' ? 'selected' : '' ?>>Inactive</option>
            </select>
        </div>
        <div class="w-full lg:w-56">
            <label class="text-xs uppercase tracking-widest text-slate-400 font-semibold">Duration</label>
            <?php $currentDuration = $filters['duration'] ?? ''; ?>
            <select name="duration" class="mt-2 w-full rounded-xl border border-slate-200 px-3 py-2 text-sm">
                <option value="" <?= $currentDuration === '' ? 'selected' : '' ?>>All durations</option>
                <option value="monthly" <?= $currentDuration === 'monthly' ? 'selected' : '' ?>>Monthly</option>
                <option value="yearly" <?= $currentDuration === 'yearly' ? 'selected' : '' ?>>Yearly</option>
                <option value="lifetime" <?= $currentDuration === 'lifetime' ? 'selected' : '' ?>>Lifetime</option>
            </select>
        </div>
        <div class="flex items-center gap-2">
            <button class="px-4 py-2 rounded-xl bg-slate-900 text-white text-sm font-semibold">
                Apply Filters
            </button>
            <a href="<?= base_url('controlplane/plans') ?>"
               class="px-4 py-2 rounded-xl bg-slate-100 text-slate-600 text-sm font-semibold">
                Reset
            </a>
        </div>
    </form>

    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6">
        <?php foreach (($plans ?? []) as $row): ?>
            <?php
                $plan = $row['plan'] ?? [];
                $limits = $row['limits'] ?? [];
                $features = $row['features'] ?? [];
                $price = $limits['price'] ?? 0;
                $duration = ucfirst($limits['duration'] ?? 'monthly');
                $trial = (int) ($limits['trial_days'] ?? 0);
                $patients = $limits['patients_active_max'] ?? -1;
                $users = $limits['users_max'] ?? -1;
                $storage = $limits['storage_gb'] ?? -1;
                $limitLines = [
                    'Patients: ' . ($patients === -1 ? 'Unlimited' : $patients),
                    'Users: ' . ($users === -1 ? 'Unlimited' : $users),
                    'Storage: ' . ($storage === -1 ? 'Unlimited' : $storage . ' GB'),
                ];
                $featureLabels = [
                    'Appointments' => !empty($features['appointments']),
                    'Finance' => !empty($features['finance']),
                    'Inventory' => !empty($features['inventory']),
                    'Reports' => !empty($features['reports']),
                    'Email Templates' => !empty($features['email_templates']),
                    'Audit Exports' => !empty($features['audit_exports']),
                    'Chairside Notes' => !empty($features['chairside_notes']),
                    'Claims Hub' => !empty($features['claims_hub']),
                    'Export tooling' => !empty($features['exports']['enabled']),
                    'Email notifications' => !empty($features['notifications']['email']['enabled']),
                ];
                $isActive = ($plan['status'] ?? 'inactive') === 'active';
            ?>
            <div class="glass-card rounded-3xl p-6">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <h3 class="text-lg font-semibold text-slate-900"><?= esc($plan['name'] ?? 'Plan') ?></h3>
                        <p class="text-xs text-slate-500"><?= esc($duration) ?> plan</p>
                    </div>
                    <div class="flex items-center gap-3">
                        <span class="text-xs font-semibold px-2 py-1 rounded-full <?= $isActive ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-100 text-slate-600' ?>">
                            <?= esc($isActive ? 'Active' : 'Inactive') ?>
                        </span>
                        <form action="<?= base_url('controlplane/plans/' . $plan['id'] . '/toggle') ?>" method="post" class="plan-toggle-form">
                            <?= csrf_field() ?>
                            <label class="toggle-switch" title="Toggle plan status">
                                <input type="checkbox" data-plan-toggle <?= $isActive ? 'checked' : '' ?> aria-label="Toggle plan status">
                                <span class="toggle-track"><span class="toggle-thumb"></span></span>
                                <span class="sr-only">Toggle plan status</span>
                            </label>
                        </form>
                    </div>
                </div>

                <div class="mt-5">
                    <div class="flex items-end gap-2">
                        <div class="text-4xl font-semibold text-slate-900">$<?= number_format((float) $price, 2) ?></div>
                        <div class="text-sm text-slate-500">/<?= esc(strtolower($duration)) ?></div>
                    </div>
                    <p class="text-xs text-slate-500 mt-1">Trial: <?= esc($trial) ?> days</p>
                </div>

                <div class="mt-5">
                    <p class="text-xs uppercase tracking-widest text-slate-400 font-semibold mb-2">Limits</p>
                    <ul class="space-y-1 text-sm text-slate-600">
                        <?php foreach ($limitLines as $limit): ?>
                            <li class="flex items-center gap-2"><i class="fas fa-check text-emerald-500 text-xs"></i><?= esc($limit) ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>

                <div class="mt-5">
                    <p class="text-xs uppercase tracking-widest text-slate-400 font-semibold mb-2">Features</p>
                    <ul class="space-y-1 text-sm">
                        <?php foreach ($featureLabels as $label => $enabled): ?>
                            <li class="flex items-center gap-2">
                                <span class="inline-flex items-center justify-center w-5 h-5 rounded-full <?= $enabled ? 'bg-emerald-100 text-emerald-600' : 'bg-slate-100 text-slate-400' ?>">
                                    <i class="fas <?= $enabled ? 'fa-check' : 'fa-xmark' ?> text-[10px]"></i>
                                </span>
                                <span class="<?= $enabled ? 'text-slate-700' : 'text-slate-400' ?>"><?= esc($label) ?></span>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </div>

                <?php if (!empty($limits['description'])): ?>
                    <div class="mt-4 text-xs text-slate-500">
                        <?= esc($limits['description']) ?>
                    </div>
                <?php endif; ?>

                <div class="mt-6 flex items-center gap-2">
                    <a href="<?= base_url('controlplane/plans/' . $plan['id'] . '/edit') ?>" class="px-3 py-1.5 text-xs font-semibold rounded-lg bg-slate-100 text-slate-600 hover:bg-slate-200">
                        Edit Plan
                    </a>
                </div>
            </div>
        <?php endforeach; ?>
        <?php if (empty($plans)): ?>
            <div class="glass-card rounded-3xl p-6 text-center text-slate-400">
                No plans yet.
            </div>
        <?php endif; ?>
    </div>
</div>

<script>
    document.querySelectorAll('[data-plan-toggle]').forEach((toggle) => {
        toggle.addEventListener('change', (event) => {
            const form = event.target.closest('form');
            if (form) {
                form.submit();
            }
        });
    });
</script>
<?= $this->endSection() ?>
