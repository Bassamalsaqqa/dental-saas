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
            <div>
                <h3 class="text-[11px] font-bold text-slate-700 uppercase tracking-widest">Inbox</h3>
                <p class="text-xs text-slate-500 mt-1">Review subscription change requests in order.</p>
            </div>
            <span class="text-[10px] uppercase tracking-widest text-slate-500 bg-white/70 border border-slate-200 px-2 py-1 rounded-full">
                <?= number_format($requestStats['filtered'] ?? 0) ?> shown
            </span>
        </div>
        <div class="divide-y divide-white/50">
            <?php if (!empty($planRequests)): ?>
                <?php foreach ($planRequests as $request): ?>
                    <?php $status = $request['status'] ?? ''; ?>
                    <div class="px-6 py-5 transition duration-300" data-request-row>
                        <div class="flex flex-col lg:flex-row lg:items-center gap-4">
                            <div class="flex-1">
                                <div class="flex flex-wrap items-center gap-2">
                                    <span class="text-sm font-semibold text-slate-900"><?= esc($request['clinic_name'] ?? 'Clinic') ?></span>
                                    <span class="text-[10px] uppercase tracking-widest px-2 py-1 rounded-full <?= $status === 'pending' ? 'bg-amber-100 text-amber-700' : ($status === 'approved' ? 'bg-emerald-100 text-emerald-700' : 'bg-rose-100 text-rose-700') ?>">
                                        <?= esc(ucfirst($status)) ?>
                                    </span>
                                </div>
                                <div class="mt-2 text-sm text-slate-600">
                                    <span class="text-slate-500">Current:</span>
                                    <?= esc($request['current_plan_name'] ?? '--') ?>
                                    <span class="text-slate-400 mx-2">?</span>
                                    <span class="text-slate-500">Requested:</span>
                                    <?= esc($request['requested_plan_name'] ?? '--') ?>
                                    <span class="text-slate-400">(<?= esc(ucfirst($request['requested_duration'] ?? 'monthly')) ?>)</span>
                                </div>
                                <div class="mt-1 text-xs text-slate-500">Requested: <?= esc($request['created_at'] ?? '-') ?></div>
                            </div>
                            <div class="flex items-center gap-2">
                                <?php if ($status === 'pending'): ?>
                                    <form action="<?= base_url('controlplane/plan-requests/' . $request['id'] . '/approve') ?>" method="post" data-approve-form>
                                        <?= csrf_field() ?>
                                        <button type="button" class="inline-flex items-center gap-2 px-3 py-1.5 rounded-lg bg-emerald-50 text-emerald-700 text-xs font-semibold" data-approve-button>
                                            <i class="fas fa-check"></i>
                                            Approve
                                        </button>
                                    </form>
                                    <button type="button"
                                            class="inline-flex items-center gap-2 px-3 py-1.5 rounded-lg bg-rose-50 text-rose-700 text-xs font-semibold"
                                            data-reject-action="<?= base_url('controlplane/plan-requests/' . $request['id'] . '/reject') ?>"
                                            data-request-label="<?= esc($request['clinic_name'] ?? 'Clinic') ?>">
                                        <i class="fas fa-xmark"></i>
                                        Reject
                                    </button>
                                <?php else: ?>
                                    <span class="text-xs font-semibold <?= $status === 'approved' ? 'text-emerald-700' : 'text-rose-700' ?>">
                                        <?= esc(ucfirst($status)) ?>
                                    </span>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="px-6 py-10 text-center text-slate-400">No plan requests yet.</div>
            <?php endif; ?>
        </div>
    </div>
</div>

<div id="reject-modal" class="fixed inset-0 z-50 hidden items-center justify-center bg-slate-900/40 backdrop-blur-sm">
    <div class="glass-card rounded-2xl p-6 w-full max-w-md max-h-[85vh] overflow-y-auto">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-xs uppercase tracking-widest text-rose-500 font-semibold">Reject Request</p>
                <h3 class="text-lg font-semibold text-slate-900 mt-1">Add a rejection reason</h3>
                <p class="text-xs text-slate-500 mt-1" id="reject-label">This request will be closed.</p>
            </div>
            <button type="button" class="text-slate-400 hover:text-slate-600" data-reject-close>
                <i class="fas fa-times"></i>
            </button>
        </div>
        <form id="reject-form" method="post" class="mt-4 space-y-4 pb-10">
            <?= csrf_field() ?>
            <div>
                <label class="text-xs uppercase tracking-widest text-slate-400 font-semibold">Reason</label>
                <textarea name="decision_reason" id="reject-reason" required rows="3"
                          class="mt-2 w-full rounded-xl border border-slate-200 px-3 py-2 text-sm"
                          placeholder="Optional context for the clinic."></textarea>
            </div>
            <div class="flex items-center justify-end gap-2 sticky bottom-0 left-0 right-0 bg-white/80 backdrop-blur border-t border-white/40 pt-3 -mx-6 -mb-6 px-6">
                <button type="button" class="px-4 py-2 rounded-xl bg-slate-100 text-slate-600 text-sm font-semibold" data-reject-close>Cancel</button>
                <button type="submit" class="px-4 py-2 rounded-xl bg-rose-600 text-white text-sm font-semibold">Confirm Reject</button>
            </div>
        </form>
    </div>
</div>

<script>
    const rejectModal = document.getElementById('reject-modal');
    const rejectForm = document.getElementById('reject-form');
    const rejectReason = document.getElementById('reject-reason');
    const rejectLabel = document.getElementById('reject-label');
    let activeRejectRow = null;

    document.querySelectorAll('[data-approve-form]').forEach((form) => {
        const button = form.querySelector('[data-approve-button]');
        if (!button) {
            return;
        }
        button.addEventListener('click', () => {
            const row = form.closest('[data-request-row]');
            if (row) {
                row.classList.add('opacity-40');
                row.classList.add('pointer-events-none');
            }
            form.submit();
        });
    });

    document.querySelectorAll('[data-reject-action]').forEach((button) => {
        button.addEventListener('click', () => {
            activeRejectRow = button.closest('[data-request-row]');
            const action = button.getAttribute('data-reject-action');
            if (action && rejectForm) {
                rejectForm.action = action;
            }
            if (rejectLabel) {
                rejectLabel.textContent = button.getAttribute('data-request-label') || 'This request will be closed.';
            }
            if (rejectReason) {
                rejectReason.value = '';
            }
            if (rejectModal) {
                rejectModal.classList.remove('hidden');
                rejectModal.classList.add('flex');
            }
            if (rejectReason) {
                rejectReason.focus();
            }
        });
    });

    document.querySelectorAll('[data-reject-close]').forEach((button) => {
        button.addEventListener('click', () => {
            if (rejectModal) {
                rejectModal.classList.add('hidden');
                rejectModal.classList.remove('flex');
            }
            activeRejectRow = null;
        });
    });

    if (rejectForm) {
        rejectForm.addEventListener('submit', () => {
            if (activeRejectRow) {
                activeRejectRow.classList.add('opacity-40');
                activeRejectRow.classList.add('pointer-events-none');
            }
        });
    }
</script>
<?= $this->endSection() ?>
