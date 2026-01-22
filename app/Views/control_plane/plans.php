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

    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6">
        <?php
        $plans = [
            [
                'name' => 'Starter',
                'price' => '$0',
                'duration' => 'Monthly',
                'status' => 'active',
                'limits' => ['Patients: 50', 'Storage: 5 GB', 'Users: 3'],
                'features' => ['Appointments', 'Basic Reports', 'Email Support'],
            ],
            [
                'name' => 'Growth',
                'price' => '$199',
                'duration' => 'Monthly',
                'status' => 'active',
                'limits' => ['Patients: 500', 'Storage: 50 GB', 'Users: 15'],
                'features' => ['Scheduling', 'Finance Module', 'Priority Support'],
            ],
            [
                'name' => 'Enterprise',
                'price' => '$499',
                'duration' => 'Yearly',
                'status' => 'inactive',
                'limits' => ['Patients: Unlimited', 'Storage: 200 GB', 'Users: Unlimited'],
                'features' => ['Custom SLA', 'Audit Exports', 'Dedicated Success'],
            ],
        ];
        ?>
        <?php foreach ($plans as $plan): ?>
            <div class="bg-white border border-slate-200 rounded-2xl p-6 shadow-sm">
                <div class="flex items-center justify-between mb-4">
                    <div>
                        <h3 class="text-lg font-bold text-slate-900"><?= esc($plan['name']) ?></h3>
                        <p class="text-xs text-slate-500"><?= esc($plan['duration']) ?></p>
                    </div>
                    <span class="text-xs font-semibold px-2 py-1 rounded-full <?= $plan['status'] === 'active' ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-100 text-slate-600' ?>">
                        <?= esc(ucfirst($plan['status'])) ?>
                    </span>
                </div>
                <div class="text-3xl font-bold text-slate-900 mb-4"><?= esc($plan['price']) ?></div>
                <div class="mb-4">
                    <p class="text-xs uppercase tracking-widest text-slate-400 font-semibold mb-2">Limits</p>
                    <ul class="space-y-1 text-sm text-slate-600">
                        <?php foreach ($plan['limits'] as $limit): ?>
                            <li class="flex items-center gap-2"><i class="fas fa-check text-emerald-500 text-xs"></i><?= esc($limit) ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
                <div class="mb-4">
                    <p class="text-xs uppercase tracking-widest text-slate-400 font-semibold mb-2">Features</p>
                    <ul class="space-y-1 text-sm text-slate-600">
                        <?php foreach ($plan['features'] as $feature): ?>
                            <li class="flex items-center gap-2"><i class="fas fa-star text-amber-400 text-xs"></i><?= esc($feature) ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
                <div class="flex gap-2">
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
