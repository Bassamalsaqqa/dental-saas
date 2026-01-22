<?= $this->extend('layouts/main_control_plane') ?>

<?= $this->section('content') ?>
<div class="max-w-6xl mx-auto">
    <div class="mb-8">
        <p class="text-xs uppercase tracking-widest text-slate-400 font-semibold">Control Plane</p>
        <h1 class="text-2xl font-bold text-slate-900">Plan Requests</h1>
        <p class="text-sm text-slate-500">Approve or reject clinic subscription changes.</p>
    </div>

    <div class="cp-soft-card rounded-3xl p-6 mb-6">
        <h2 class="text-sm font-semibold text-slate-800">Create plan request</h2>
        <form action="<?= base_url('controlplane/plan-requests') ?>" method="post" class="mt-4 grid grid-cols-1 md:grid-cols-4 gap-4 items-end">
            <?= csrf_field() ?>
            <div>
                <label class="text-xs uppercase tracking-widest text-slate-400 font-semibold">Clinic</label>
                <select name="clinic_id" class="mt-2 w-full rounded-xl border border-slate-200 px-3 py-2 text-sm" required>
                    <option value="">Select clinic</option>
                    <?php foreach (($clinics ?? []) as $clinic): ?>
                        <option value="<?= esc($clinic['id']) ?>"><?= esc($clinic['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <label class="text-xs uppercase tracking-widest text-slate-400 font-semibold">Plan</label>
                <select name="requested_plan_id" class="mt-2 w-full rounded-xl border border-slate-200 px-3 py-2 text-sm" required>
                    <option value="">Select plan</option>
                    <?php foreach (($plans ?? []) as $plan): ?>
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
            <button class="px-4 py-2 rounded-xl bg-emerald-600 text-white text-sm font-semibold">Queue Request</button>
        </form>
    </div>

    <div class="bg-white border border-slate-200 rounded-2xl overflow-hidden shadow-sm">
        <div class="flex items-center justify-between px-6 py-4 border-b border-slate-200">
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
                <thead class="bg-slate-50 text-xs uppercase tracking-widest text-slate-500">
                    <tr>
                        <th class="px-6 py-3 text-left">Clinic</th>
                        <th class="px-6 py-3 text-left">Plan</th>
                        <th class="px-6 py-3 text-left">Duration</th>
                        <th class="px-6 py-3 text-left">Requested</th>
                        <th class="px-6 py-3 text-right">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    <?php if (!empty($rows)): ?>
                        <?php foreach ($rows as $row): ?>
                            <?php $request = $row['request']; ?>
                            <tr>
                                <td class="px-6 py-4 text-slate-700 font-semibold"><?= esc($row['clinic_name']) ?></td>
                                <td class="px-6 py-4 text-slate-600"><?= esc($row['plan_name']) ?></td>
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
                            <td class="px-6 py-6 text-center text-slate-400" colspan="5">No plan requests yet.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?= $this->endSection() ?>
