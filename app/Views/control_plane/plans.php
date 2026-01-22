<?= $this->extend('layouts/main_control_plane') ?>

<?= $this->section('content') ?>
<div class="space-y-8">
    <div class="cp-soft-card rounded-3xl p-6 md:p-8">
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
            ?>
            <div class="cp-soft-card rounded-3xl p-6">
                <div class="flex items-start justify-between">
                    <div>
                        <h3 class="text-lg font-semibold text-slate-900"><?= esc($plan['name'] ?? 'Plan') ?></h3>
                        <p class="text-xs text-slate-500"><?= esc($duration) ?></p>
                    </div>
                    <span class="text-xs font-semibold px-2 py-1 rounded-full <?= ($plan['status'] ?? 'inactive') === 'active' ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-100 text-slate-600' ?>">
                        <?= esc(ucfirst($plan['status'] ?? 'inactive')) ?>
                    </span>
                </div>
                <div class="mt-4 text-3xl font-semibold text-slate-900">$<?= number_format((float) $price, 2) ?></div>
                <p class="text-xs text-slate-500 mt-1">Trial: <?= esc($trial) ?> days</p>

                <div class="mt-4">
                    <p class="text-xs uppercase tracking-widest text-slate-400 font-semibold mb-2">Limits</p>
                    <ul class="space-y-1 text-sm text-slate-600">
                        <?php foreach ($limitLines as $limit): ?>
                            <li class="flex items-center gap-2"><i class="fas fa-check text-emerald-500 text-xs"></i><?= esc($limit) ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>

                <div class="mt-4">
                    <p class="text-xs uppercase tracking-widest text-slate-400 font-semibold mb-2">Features</p>
                    <ul class="space-y-1 text-sm text-slate-600">
                        <?php foreach ($featureLabels as $label => $enabled): ?>
                            <?php if ($enabled): ?>
                                <li class="flex items-center gap-2"><i class="fas fa-star text-amber-400 text-xs"></i><?= esc($label) ?></li>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </ul>
                </div>

                <div class="mt-5 flex gap-2">
                    <a href="<?= base_url('controlplane/plans/' . $plan['id'] . '/edit') ?>" class="px-3 py-1.5 text-xs font-semibold rounded-lg bg-slate-100 text-slate-600 hover:bg-slate-200">
                        Edit Plan
                    </a>
                    <form action="<?= base_url('controlplane/plans/' . $plan['id'] . '/toggle') ?>" method="post">
                        <?= csrf_field() ?>
                        <button class="px-3 py-1.5 text-xs font-semibold rounded-lg <?= ($plan['status'] ?? 'inactive') === 'active' ? 'bg-rose-50 text-rose-700' : 'bg-emerald-50 text-emerald-700' ?>">
                            <?= ($plan['status'] ?? 'inactive') === 'active' ? 'Deactivate' : 'Activate' ?>
                        </button>
                    </form>
                </div>
            </div>
        <?php endforeach; ?>
        <?php if (empty($plans)): ?>
            <div class="cp-soft-card rounded-3xl p-6 text-center text-slate-400">
                No plans yet.
            </div>
        <?php endif; ?>
    </div>
</div>
<?= $this->endSection() ?>
