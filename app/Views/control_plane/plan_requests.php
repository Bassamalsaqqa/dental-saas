<?= $this->extend('layouts/main_control_plane') ?>

<?= $this->section('content') ?>
<div class="space-y-8">
    <div class="glass-card rounded-3xl p-6 md:p-8">
        <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-6">
            <div>
                <p class="text-xs uppercase tracking-[0.3em] text-blue-700 font-semibold">Plan Requests</p>
                <h1 class="text-3xl font-semibold text-slate-900 mt-2">Clinic change queue</h1>
                <p class="mt-3 text-sm text-slate-600 max-w-2xl">
                    Approve or reject subscription change requests from clinics.
                </p>
            </div>
            <a href="<?= base_url('controlplane/plans') ?>"
               class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-slate-100 text-slate-600 text-sm font-semibold">
                <i class="fas fa-layer-group"></i>
                View Plans
            </a>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <div class="glass-card rounded-2xl p-4">
            <p class="text-xs uppercase tracking-widest text-slate-400 font-semibold">Total</p>
            <p class="text-2xl font-semibold text-slate-900 mt-1"><?= number_format($requestStats['total'] ?? 0) ?></p>
        </div>
        <div class="glass-card rounded-2xl p-4">
            <p class="text-xs uppercase tracking-widest text-slate-400 font-semibold">Pending</p>
            <p class="text-2xl font-semibold text-amber-600 mt-1"><?= number_format($requestStats['pending'] ?? 0) ?></p>
        </div>
        <div class="glass-card rounded-2xl p-4">
            <p class="text-xs uppercase tracking-widest text-slate-400 font-semibold">Approved</p>
            <p class="text-2xl font-semibold text-emerald-600 mt-1"><?= number_format($requestStats['approved'] ?? 0) ?></p>
        </div>
        <div class="glass-card rounded-2xl p-4">
            <p class="text-xs uppercase tracking-widest text-slate-400 font-semibold">Rejected</p>
            <p class="text-2xl font-semibold text-rose-600 mt-1"><?= number_format($requestStats['rejected'] ?? 0) ?></p>
        </div>
    </div>

    <form method="get" class="glass-card rounded-2xl p-5 flex flex-col lg:flex-row lg:items-end gap-4">
        <div class="flex-1">
            <label class="text-xs uppercase tracking-widest text-slate-400 font-semibold">Search</label>
            <input type="text" name="request_q" value="<?= esc($requestFilters['q'] ?? '') ?>" placeholder="Clinic or plan name"
                   class="mt-2 w-full rounded-xl border border-slate-200 px-3 py-2 text-sm">
        </div>
        <div class="w-full lg:w-56">
            <label class="text-xs uppercase tracking-widest text-slate-400 font-semibold">Status</label>
            <?php $currentReqStatus = $requestFilters['status'] ?? ''; ?>
            <select name="request_status" class="mt-2 w-full rounded-xl border border-slate-200 px-3 py-2 text-sm">
                <option value="" <?= $currentReqStatus === '' ? 'selected' : '' ?>>All</option>
                <option value="pending" <?= $currentReqStatus === 'pending' ? 'selected' : '' ?>>Pending</option>
                <option value="approved" <?= $currentReqStatus === 'approved' ? 'selected' : '' ?>>Approved</option>
                <option value="rejected" <?= $currentReqStatus === 'rejected' ? 'selected' : '' ?>>Rejected</option>
            </select>
        </div>
        <div class="flex items-center gap-2">
            <button class="px-4 py-2 rounded-xl bg-slate-900 text-white text-sm font-semibold">Filter</button>
            <a href="<?= base_url('controlplane/plan-requests') ?>"
               class="px-4 py-2 rounded-xl bg-slate-100 text-slate-600 text-sm font-semibold">
                Reset
            </a>
        </div>
    </form>

    <form action="<?= base_url('controlplane/plan-requests') ?>" method="post" class="glass-card rounded-2xl p-5 grid grid-cols-1 lg:grid-cols-4 gap-4 items-end">
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

    <div class="glass-card rounded-3xl overflow-hidden">
        <div class="px-6 py-4 border-b border-white/40 bg-white/70 flex items-center justify-between">
            <h3 class="text-[11px] font-bold text-slate-700 uppercase tracking-widest">Plan requests</h3>
            <span class="text-[10px] uppercase tracking-widest text-slate-500 bg-white/70 border border-slate-200 px-2 py-1 rounded-full">
                <?= number_format($requestStats['filtered'] ?? 0) ?> shown
            </span>
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
                                <td class="px-6 py-4 text-slate-600"><?= esc($request['current_plan_name'] ?? '--') ?></td>
                                <td class="px-6 py-4 text-slate-600"><?= esc($request['requested_plan_name'] ?? '--') ?></td>
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
<?= $this->endSection() ?>
