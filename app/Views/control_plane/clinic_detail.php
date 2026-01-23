<?= $this->extend('layouts/main_control_plane') ?>

<?= $this->section('content') ?>
<div class="space-y-8">
    <div class="glass-card rounded-3xl p-6 md:p-8">
        <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-6">
            <div>
                <p class="text-xs uppercase tracking-[0.3em] text-emerald-700 font-semibold">Clinic Profile</p>
                <h1 class="text-3xl font-semibold text-slate-900 mt-2"><?= esc($clinic['name']) ?></h1>
                <p class="mt-3 text-sm text-slate-600">Clinic overview, subscription status, and lifecycle controls.</p>
            </div>
            <?php
                $clinicStatus = $clinic['status'] ?? 'inactive';
                $clinicStatusLabel = ucfirst($clinicStatus);
                $clinicStatusClass = $clinicStatus === 'active'
                    ? 'bg-emerald-100 text-emerald-700'
                    : ($clinicStatus === 'suspended' ? 'bg-amber-100 text-amber-700' : 'bg-slate-100 text-slate-600');
            ?>
            <div class="flex flex-wrap items-center gap-3">
                <span class="text-xs font-semibold px-3 py-1 rounded-full <?= $clinicStatusClass ?>">
                    Status: <?= esc($clinicStatusLabel) ?>
                </span>
                <form action="<?= base_url('controlplane/impersonate') ?>" method="post">
                    <?= csrf_field() ?>
                    <input type="hidden" name="clinic_id" value="<?= esc($clinic['id']) ?>">
                    <button class="px-3 py-2 rounded-xl bg-indigo-600 text-white text-xs font-semibold hover:bg-indigo-700">
                        Login As
                    </button>
                </form>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-[1.2fr,0.8fr] gap-6">
        <div class="glass-card rounded-3xl p-6">
            <h2 class="text-sm font-semibold text-slate-800 mb-4">Clinic details</h2>
            <div class="space-y-3 text-sm text-slate-600">
                <div class="flex items-center justify-between">
                    <span class="text-slate-400">Clinic ID</span>
                    <span class="font-semibold text-slate-800"><?= esc($clinic['id']) ?></span>
                </div>
                <div class="flex items-center justify-between">
                    <span class="text-slate-400">Slug</span>
                    <span><?= esc($clinic['slug'] ?? '--') ?></span>
                </div>
                <div class="flex items-center justify-between">
                    <span class="text-slate-400">Created</span>
                    <span><?= esc($clinic['created_at'] ?? '--') ?></span>
                </div>
            </div>
        </div>

        <div class="glass-card rounded-3xl p-6">
            <h2 class="text-sm font-semibold text-slate-800 mb-4">Subscription</h2>
            <div class="space-y-3 text-sm text-slate-600">
                <div class="flex items-center justify-between">
                    <span class="text-slate-400">Plan</span>
                    <span class="font-semibold text-slate-800"><?= esc($plan_name ?? 'Unassigned') ?></span>
                </div>
                <div class="flex items-center justify-between">
                    <span class="text-slate-400">Status</span>
                    <span><?= esc($subscription['status'] ?? 'none') ?></span>
                </div>
                <div class="flex items-center justify-between">
                    <span class="text-slate-400">Start</span>
                    <span><?= esc($subscription['start_at'] ?? '--') ?></span>
                </div>
                <div class="flex items-center justify-between">
                    <span class="text-slate-400">End</span>
                    <span><?= esc($subscription['end_at'] ?? '--') ?></span>
                </div>
            </div>
        </div>
    </div>

    <div class="glass-card rounded-3xl p-6 space-y-6">
        <h2 class="text-sm font-semibold text-slate-800">Lifecycle actions</h2>

        <?php
            $startValue = !empty($subscription['start_at']) ? date('Y-m-d\\TH:i', strtotime($subscription['start_at'])) : '';
            $endValue = !empty($subscription['end_at']) ? date('Y-m-d\\TH:i', strtotime($subscription['end_at'])) : '';
        ?>
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 text-sm text-slate-600">
            <form action="<?= base_url('controlplane/clinics/' . $clinic['id'] . '/subscription/start') ?>" method="post" class="rounded-2xl border border-emerald-100 bg-emerald-50/40 p-4 space-y-3">
                <?= csrf_field() ?>
                <h3 class="text-xs uppercase tracking-widest text-emerald-700 font-semibold">Start subscription</h3>
                <div>
                    <label class="text-xs text-slate-500">Plan</label>
                    <select name="plan_id" class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2 text-sm" required>
                        <option value="">Select plan</option>
                        <?php foreach (($plans ?? []) as $plan): ?>
                            <option value="<?= esc($plan['id']) ?>"><?= esc($plan['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label class="text-xs text-slate-500">Duration</label>
                    <select name="duration" class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2 text-sm">
                        <option value="monthly">Monthly</option>
                        <option value="yearly">Yearly</option>
                        <option value="lifetime">Lifetime</option>
                    </select>
                </div>
                <button class="w-full px-3 py-2 rounded-lg bg-emerald-600 text-white text-xs font-semibold">Start</button>
            </form>

            <form action="<?= base_url('controlplane/clinics/' . $clinic['id'] . '/subscription/extend') ?>" method="post" class="rounded-2xl border border-blue-100 bg-blue-50/40 p-4 space-y-3">
                <?= csrf_field() ?>
                <h3 class="text-xs uppercase tracking-widest text-blue-700 font-semibold">Extend subscription</h3>
                <div>
                    <label class="text-xs text-slate-500">Duration</label>
                    <select name="duration" class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2 text-sm">
                        <option value="monthly">Monthly</option>
                        <option value="yearly">Yearly</option>
                        <option value="lifetime">Lifetime</option>
                    </select>
                </div>
                <button class="w-full px-3 py-2 rounded-lg bg-blue-600 text-white text-xs font-semibold">Extend</button>
            </form>

            <form action="<?= base_url('controlplane/clinics/' . $clinic['id'] . '/subscription/update-dates') ?>" method="post" class="rounded-2xl border border-slate-100 bg-slate-50/40 p-4 space-y-3">
                <?= csrf_field() ?>
                <h3 class="text-xs uppercase tracking-widest text-slate-600 font-semibold">Adjust dates</h3>
                <div>
                    <label class="text-xs text-slate-500">Start</label>
                    <input type="datetime-local" name="start_at" value="<?= esc($startValue) ?>" class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2 text-xs">
                </div>
                <div>
                    <label class="text-xs text-slate-500">End</label>
                    <input type="datetime-local" name="end_at" value="<?= esc($endValue) ?>" class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2 text-xs">
                </div>
                <button class="w-full px-3 py-2 rounded-lg bg-slate-700 text-white text-xs font-semibold">Save Dates</button>
            </form>

            <div class="rounded-2xl border border-rose-100 bg-rose-50/40 p-4 space-y-3">
                <h3 class="text-xs uppercase tracking-widest text-rose-700 font-semibold">Terminate clinic</h3>
                <p class="text-xs text-slate-500">Disables clinic access and deactivates subscription.</p>
                <?php if (($clinic['status'] ?? 'inactive') === 'active'): ?>
                    <form action="<?= base_url('controlplane/clinics/' . $clinic['id'] . '/suspend') ?>" method="post">
                        <?= csrf_field() ?>
                        <button class="w-full px-3 py-2 rounded-lg bg-amber-500 text-white text-xs font-semibold">Suspend Clinic</button>
                    </form>
                <?php else: ?>
                    <form action="<?= base_url('controlplane/clinics/' . $clinic['id'] . '/reactivate') ?>" method="post">
                        <?= csrf_field() ?>
                        <button class="w-full px-3 py-2 rounded-lg bg-emerald-600 text-white text-xs font-semibold">Reactivate Clinic</button>
                    </form>
                <?php endif; ?>
                <form action="<?= base_url('controlplane/clinics/' . $clinic['id'] . '/subscription/cancel') ?>" method="post">
                    <?= csrf_field() ?>
                    <button class="w-full px-3 py-2 rounded-lg bg-amber-500 text-white text-xs font-semibold">Cancel Subscription</button>
                </form>
                <button type="button" class="w-full px-3 py-2 rounded-lg bg-rose-600 text-white text-xs font-semibold"
                        onclick="openTerminateModal()">
                    Terminate Clinic
                </button>
            </div>
        </div>

        <p class="text-xs text-slate-500">All actions are audited and applied immediately.</p>
    </div>
</div>

<div id="terminateModal" class="fixed inset-0 z-50 hidden items-center justify-center bg-slate-900/40 px-4">
    <div class="w-full max-w-lg rounded-3xl border border-rose-100 bg-white/95 shadow-2xl backdrop-blur-xl">
        <div class="flex items-center justify-between border-b border-rose-100 px-6 py-4">
            <div>
                <p class="text-xs uppercase tracking-[0.3em] text-rose-400 font-semibold">Danger Zone</p>
                <h3 class="text-lg font-semibold text-slate-900">Terminate clinic</h3>
            </div>
            <button type="button" class="rounded-full p-2 text-slate-400 hover:text-slate-700 hover:bg-slate-100" onclick="closeTerminateModal()">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <div class="px-6 py-4 space-y-4 text-sm text-slate-600">
            <p>
                This will immediately disable access and cancel subscriptions for
                <span id="terminateClinicName" class="font-semibold text-slate-900"><?= esc($clinic['name']) ?></span>.
                Type the clinic name to confirm.
            </p>
            <input type="text" id="terminateConfirmInput" class="w-full rounded-xl border border-rose-200 px-3 py-2 text-sm"
                   placeholder="Type clinic name to confirm" oninput="updateTerminateState()">
        </div>
        <form id="terminateForm" action="<?= base_url('controlplane/clinics/' . $clinic['id'] . '/terminate') ?>" method="post" class="px-6 pb-6 flex items-center justify-between gap-3">
            <?= csrf_field() ?>
            <button type="button" class="px-4 py-2 rounded-xl bg-slate-100 text-slate-600 text-sm font-semibold" onclick="closeTerminateModal()">
                Cancel
            </button>
            <button id="terminateConfirmButton" type="submit" class="px-4 py-2 rounded-xl bg-rose-600 text-white text-sm font-semibold opacity-60 cursor-not-allowed" disabled>
                Terminate Clinic
            </button>
        </form>
    </div>
</div>

<script>
let terminateClinicName = '<?= esc($clinic['name']) ?>';

function openTerminateModal() {
    const modal = document.getElementById('terminateModal');
    const inputEl = document.getElementById('terminateConfirmInput');
    if (inputEl) {
        inputEl.value = '';
    }
    updateTerminateState();
    if (modal) {
        modal.classList.remove('hidden');
        modal.classList.add('flex');
    }
}

function closeTerminateModal() {
    const modal = document.getElementById('terminateModal');
    if (modal) {
        modal.classList.add('hidden');
        modal.classList.remove('flex');
    }
}

function updateTerminateState() {
    const inputEl = document.getElementById('terminateConfirmInput');
    const buttonEl = document.getElementById('terminateConfirmButton');
    if (!inputEl || !buttonEl) {
        return;
    }
    const matches = inputEl.value.trim().toLowerCase() === terminateClinicName.trim().toLowerCase();
    buttonEl.disabled = !matches;
    buttonEl.classList.toggle('opacity-60', !matches);
    buttonEl.classList.toggle('cursor-not-allowed', !matches);
}

document.addEventListener('DOMContentLoaded', function() {
    const modal = document.getElementById('terminateModal');
    if (modal) {
        modal.addEventListener('click', function(event) {
            if (event.target === modal) {
                closeTerminateModal();
            }
        });
    }
});
</script>
<?= $this->endSection() ?>
