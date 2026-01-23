<?= $this->extend('layouts/main_control_plane') ?>

<?= $this->section('content') ?>
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
            ?>
            <div class="glass-card rounded-3xl p-6">
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

                <?php if (!empty($limits['description'])): ?>
                    <div class="mt-4 text-xs text-slate-500">
                        <?= esc($limits['description']) ?>
                    </div>
                <?php endif; ?>

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
            <div class="glass-card rounded-3xl p-6 text-center text-slate-400">
                No plans yet.
            </div>
        <?php endif; ?>
    </div>

    <div class="glass-card rounded-3xl p-6 md:p-8">
        <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
            <div>
                <p class="text-xs uppercase tracking-[0.3em] text-blue-700 font-semibold">Plan Requests</p>
                <h2 class="text-2xl font-semibold text-slate-900 mt-2">Clinic change queue</h2>
                <p class="mt-2 text-sm text-slate-600">Approve or reject subscription change requests from clinics.</p>
            </div>
        </div>

        <div class="mt-6 grid grid-cols-1 md:grid-cols-4 gap-4">
            <div class="rounded-2xl border border-white/60 bg-white/70 p-4">
                <p class="text-xs uppercase tracking-widest text-slate-400 font-semibold">Total</p>
                <p class="text-2xl font-semibold text-slate-900 mt-1"><?= number_format($requestStats['total'] ?? 0) ?></p>
            </div>
            <div class="rounded-2xl border border-white/60 bg-white/70 p-4">
                <p class="text-xs uppercase tracking-widest text-slate-400 font-semibold">Pending</p>
                <p class="text-2xl font-semibold text-amber-600 mt-1"><?= number_format($requestStats['pending'] ?? 0) ?></p>
            </div>
            <div class="rounded-2xl border border-white/60 bg-white/70 p-4">
                <p class="text-xs uppercase tracking-widest text-slate-400 font-semibold">Approved</p>
                <p class="text-2xl font-semibold text-emerald-600 mt-1"><?= number_format($requestStats['approved'] ?? 0) ?></p>
            </div>
            <div class="rounded-2xl border border-white/60 bg-white/70 p-4">
                <p class="text-xs uppercase tracking-widest text-slate-400 font-semibold">Rejected</p>
                <p class="text-2xl font-semibold text-rose-600 mt-1"><?= number_format($requestStats['rejected'] ?? 0) ?></p>
            </div>
        </div>

        <form method="get" class="mt-6 grid grid-cols-1 lg:grid-cols-[2fr,1fr,auto] gap-4 items-end">
            <div>
                <label class="text-xs uppercase tracking-widest text-slate-400 font-semibold">Search</label>
                <input type="text" name="q" value="<?= esc($requestFilters['q'] ?? '') ?>" placeholder="Clinic or plan name"
                       class="mt-2 w-full rounded-xl border border-slate-200 px-3 py-2 text-sm">
            </div>
            <div>
                <label class="text-xs uppercase tracking-widest text-slate-400 font-semibold">Status</label>
                <?php $currentReqStatus = $requestFilters['status'] ?? ''; ?>
                <select name="status" class="mt-2 w-full rounded-xl border border-slate-200 px-3 py-2 text-sm">
                    <option value="" <?= $currentReqStatus === '' ? 'selected' : '' ?>>All</option>
                    <option value="pending" <?= $currentReqStatus === 'pending' ? 'selected' : '' ?>>Pending</option>
                    <option value="approved" <?= $currentReqStatus === 'approved' ? 'selected' : '' ?>>Approved</option>
                    <option value="rejected" <?= $currentReqStatus === 'rejected' ? 'selected' : '' ?>>Rejected</option>
                </select>
            </div>
            <div class="flex items-center gap-2">
                <button class="px-4 py-2 rounded-xl bg-slate-900 text-white text-sm font-semibold">Filter</button>
                <a href="<?= base_url('controlplane/plan-requests') ?>" class="px-4 py-2 rounded-xl bg-slate-100 text-slate-600 text-sm font-semibold">Reset</a>
            </div>
        </form>

        <form action="<?= base_url('controlplane/plan-requests') ?>" method="post" class="mt-6 grid grid-cols-1 lg:grid-cols-4 gap-4 items-end">
            <?= csrf_field() ?>
            <div>
                <label class="text-xs uppercase tracking-widest text-slate-400 font-semibold">Clinic</label>
                <select name="clinic_id" class="mt-2 w-full rounded-xl border border-slate-200 px-3 py-2 text-sm" required>
                    <option value="">Select clinic</option>
                    <?php foreach (($requestClinics ?? []) as $clinic): ?>
                        <option value="<?= esc($clinic['id']) ?>"><?= esc($clinic['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <label class="text-xs uppercase tracking-widest text-slate-400 font-semibold">Plan</label>
                <select name="requested_plan_id" class="mt-2 w-full rounded-xl border border-slate-200 px-3 py-2 text-sm" required>
                    <option value="">Select plan</option>
                    <?php foreach (($requestPlans ?? []) as $plan): ?>
                        <option value="<?= esc($plan['id']) ?>"><?= esc($plan['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <label class="text-xs uppercase tracking-widest text-slate-400 font-semibold">Duration</label>
                <select name="requested_duration" class="mt-2 w-full rounded-xl border border-slate-200 px-3 py-2 text-sm">
                    <option value="monthly">Monthly</option>
                    <option value="yearly">Yearly</option>
                    <option value="lifetime">Lifetime</option>
                </select>
            </div>
            <button class="px-4 py-2 rounded-xl bg-blue-600 text-white text-sm font-semibold">Queue Request</button>
        </form>

        <div class="mt-6 glass-muted rounded-2xl overflow-hidden">
            <div class="flex items-center justify-between px-6 py-4 border-b border-white/40">
                <div class="flex items-center gap-3">
                    <span class="text-xs text-slate-500">Show</span>
                    <select class="rounded-lg border border-slate-200 text-xs px-2 py-1" disabled>
                        <option>10</option>
                    </select>
                    <span class="text-xs text-slate-500">entries</span>
                </div>
                <input type="text" class="rounded-lg border border-slate-200 text-xs px-3 py-2 w-56" placeholder="Search" disabled>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead class="bg-white/70 text-xs uppercase tracking-widest text-slate-500">
                        <tr>
                            <th class="px-6 py-3 text-left">Clinic</th>
                            <th class="px-6 py-3 text-left">Current Plan</th>
                            <th class="px-6 py-3 text-left">Requested Plan</th>
                            <th class="px-6 py-3 text-left">Duration</th>
                            <th class="px-6 py-3 text-left">Requested</th>
                            <th class="px-6 py-3 text-right">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-white/50">
                        <?php if (!empty($planRequests)): ?>
                            <?php foreach ($planRequests as $request): ?>
                                <tr>
                                    <td class="px-6 py-4 text-slate-700 font-semibold"><?= esc($request['clinic_name'] ?? 'Clinic') ?></td>
                                    <td class="px-6 py-4 text-slate-600"><?= esc($request['current_plan_name'] ?? '—') ?></td>
                                    <td class="px-6 py-4 text-slate-600"><?= esc($request['requested_plan_name'] ?? '—') ?></td>
                                    <td class="px-6 py-4 text-slate-600"><?= esc(ucfirst($request['requested_duration'] ?? 'monthly')) ?></td>
                                    <td class="px-6 py-4 text-slate-500"><?= esc($request['created_at'] ?? '-') ?></td>
                                    <td class="px-6 py-4 text-right">
                                        <?php if (($request['status'] ?? '') === 'pending'): ?>
                                            <div class="flex justify-end gap-2">
                                                <form action="<?= base_url('controlplane/plan-requests/' . $request['id'] . '/approve') ?>" method="post">
                                                    <?= csrf_field() ?>
                                                    <button class="px-3 py-1.5 rounded-lg bg-emerald-50 text-emerald-700 text-xs font-semibold">Approve</button>
                                                </form>
                                                <form action="<?= base_url('controlplane/plan-requests/' . $request['id'] . '/reject') ?>" method="post">
                                                    <?= csrf_field() ?>
                                                    <input type="hidden" name="decision_reason" value="">
                                                    <button class="px-3 py-1.5 rounded-lg bg-rose-50 text-rose-700 text-xs font-semibold">Reject</button>
                                                </form>
                                            </div>
                                        <?php else: ?>
                                            <span class="text-xs font-semibold <?= ($request['status'] ?? '') === 'approved' ? 'text-emerald-700' : 'text-rose-700' ?>">
                                                <?= esc(ucfirst($request['status'] ?? '')) ?>
                                            </span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td class="px-6 py-6 text-center text-slate-400" colspan="6">No plan requests yet.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>
