<?= $this->extend('layouts/main_control_plane') ?>

<?= $this->section('content') ?>
<div class="space-y-8">
    <div class="cp-soft-card rounded-3xl p-6 md:p-8">
        <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-6">
            <div>
                <p class="text-xs uppercase tracking-[0.3em] text-emerald-700 font-semibold">Plans</p>
                <h1 class="text-3xl font-semibold text-slate-900 mt-2">Plan catalog</h1>
                <p class="mt-3 text-sm text-slate-600 max-w-2xl">
                    Define pricing, duration, and entitlement caps for clinics. Editing is staged until backend wiring is complete.
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
        <?php
        $plans = [
            [
                'name' => 'Starter',
                'price' => '$0',
                'duration' => 'Monthly',
                'status' => 'active',
                'trial' => '0 days',
                'limits' => ['Patients: 50', 'Storage: 5 GB', 'Users: 3'],
                'features' => ['Appointments', 'Basic Reports', 'Email Support'],
            ],
            [
                'name' => 'Growth',
                'price' => '$199',
                'duration' => 'Monthly',
                'status' => 'active',
                'trial' => '7 days',
                'limits' => ['Patients: 500', 'Storage: 50 GB', 'Users: 15'],
                'features' => ['Scheduling', 'Finance Module', 'Priority Support'],
            ],
            [
                'name' => 'Enterprise',
                'price' => '$499',
                'duration' => 'Yearly',
                'status' => 'inactive',
                'trial' => '14 days',
                'limits' => ['Patients: Unlimited', 'Storage: 200 GB', 'Users: Unlimited'],
                'features' => ['Custom SLA', 'Audit Exports', 'Dedicated Success'],
            ],
        ];
        ?>
        <?php foreach ($plans as $plan): ?>
            <div class="cp-soft-card rounded-3xl p-6">
                <div class="flex items-start justify-between">
                    <div>
                        <h3 class="text-lg font-semibold text-slate-900"><?= esc($plan['name']) ?></h3>
                        <p class="text-xs text-slate-500"><?= esc($plan['duration']) ?></p>
                    </div>
                    <span class="text-xs font-semibold px-2 py-1 rounded-full <?= $plan['status'] === 'active' ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-100 text-slate-600' ?>">
                        <?= esc(ucfirst($plan['status'])) ?>
                    </span>
                </div>
                <div class="mt-4 text-3xl font-semibold text-slate-900"><?= esc($plan['price']) ?></div>
                <p class="text-xs text-slate-500 mt-1">Trial: <?= esc($plan['trial']) ?></p>

                <div class="mt-4">
                    <p class="text-xs uppercase tracking-widest text-slate-400 font-semibold mb-2">Limits</p>
                    <ul class="space-y-1 text-sm text-slate-600">
                        <?php foreach ($plan['limits'] as $limit): ?>
                            <li class="flex items-center gap-2"><i class="fas fa-check text-emerald-500 text-xs"></i><?= esc($limit) ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>

                <div class="mt-4">
                    <p class="text-xs uppercase tracking-widest text-slate-400 font-semibold mb-2">Features</p>
                    <ul class="space-y-1 text-sm text-slate-600">
                        <?php foreach ($plan['features'] as $feature): ?>
                            <li class="flex items-center gap-2"><i class="fas fa-star text-amber-400 text-xs"></i><?= esc($feature) ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>

                <div class="mt-5 flex gap-2">
                    <a href="<?= base_url('controlplane/plans/create') ?>" class="px-3 py-1.5 text-xs font-semibold rounded-lg bg-slate-100 text-slate-600 hover:bg-slate-200">
                        Edit Plan
                    </a>
                    <button class="px-3 py-1.5 text-xs font-semibold rounded-lg bg-rose-50 text-rose-700 cursor-not-allowed" disabled>
                        Archive
                    </button>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>
<?= $this->endSection() ?>
