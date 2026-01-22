<?= $this->extend('layouts/main_control_plane') ?>

<?= $this->section('content') ?>
<div class="space-y-8">
    <div class="cp-soft-card rounded-3xl p-6 md:p-8">
        <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-6">
            <div>
                <p class="text-xs uppercase tracking-[0.3em] text-emerald-700 font-semibold">Plan Configurator</p>
                <h1 class="text-3xl font-semibold text-slate-900 mt-2">Build your own plan</h1>
                <p class="mt-3 text-sm text-slate-600 max-w-2xl">
                    Draft pricing, duration, and entitlement caps. Save is disabled until the plan engine is wired.
                </p>
            </div>
            <span class="text-xs font-semibold px-3 py-1 rounded-full bg-slate-100 text-slate-600">Draft</span>
        </div>
    </div>

    <form class="cp-soft-card rounded-3xl p-6 space-y-8">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <label class="text-xs font-semibold text-slate-500 uppercase tracking-widest">Plan Name</label>
                <input type="text" class="mt-2 w-full rounded-xl border border-slate-200 px-3 py-2 text-sm" placeholder="Enter plan name" disabled>
            </div>
            <div>
                <label class="text-xs font-semibold text-slate-500 uppercase tracking-widest">Price</label>
                <input type="text" class="mt-2 w-full rounded-xl border border-slate-200 px-3 py-2 text-sm" placeholder="Enter price" disabled>
            </div>
            <div>
                <label class="text-xs font-semibold text-slate-500 uppercase tracking-widest">Duration</label>
                <select class="mt-2 w-full rounded-xl border border-slate-200 px-3 py-2 text-sm" disabled>
                    <option>Monthly</option>
                    <option>Yearly</option>
                    <option>Lifetime</option>
                </select>
            </div>
            <div>
                <label class="text-xs font-semibold text-slate-500 uppercase tracking-widest">Trial Days</label>
                <input type="number" class="mt-2 w-full rounded-xl border border-slate-200 px-3 py-2 text-sm" placeholder="0" disabled>
            </div>
            <div>
                <label class="text-xs font-semibold text-slate-500 uppercase tracking-widest">Patient License</label>
                <input type="number" class="mt-2 w-full rounded-xl border border-slate-200 px-3 py-2 text-sm" placeholder="Max patients" disabled>
            </div>
            <div>
                <label class="text-xs font-semibold text-slate-500 uppercase tracking-widest">User Seats</label>
                <input type="number" class="mt-2 w-full rounded-xl border border-slate-200 px-3 py-2 text-sm" placeholder="Max users" disabled>
            </div>
            <div>
                <label class="text-xs font-semibold text-slate-500 uppercase tracking-widest">Storage (GB)</label>
                <input type="number" class="mt-2 w-full rounded-xl border border-slate-200 px-3 py-2 text-sm" placeholder="Storage limit" disabled>
            </div>
            <div>
                <label class="text-xs font-semibold text-slate-500 uppercase tracking-widest">Status</label>
                <select class="mt-2 w-full rounded-xl border border-slate-200 px-3 py-2 text-sm" disabled>
                    <option>Active</option>
                    <option>Inactive</option>
                </select>
            </div>
        </div>

        <div>
            <label class="text-xs font-semibold text-slate-500 uppercase tracking-widest">Feature Toggles</label>
            <div class="mt-3 grid grid-cols-1 md:grid-cols-2 gap-3 text-sm text-slate-600">
                <?php foreach (['Appointments', 'Finance', 'Inventory', 'Reports', 'Email Templates', 'Audit Exports', 'Chairside Notes', 'Claims Hub'] as $feature): ?>
                    <label class="flex items-center gap-2">
                        <input type="checkbox" disabled>
                        <?= esc($feature) ?>
                    </label>
                <?php endforeach; ?>
            </div>
        </div>

        <div>
            <label class="text-xs font-semibold text-slate-500 uppercase tracking-widest">Description</label>
            <textarea class="mt-2 w-full rounded-xl border border-slate-200 px-3 py-2 text-sm" rows="4" placeholder="Plan description" disabled></textarea>
        </div>

        <div class="flex justify-end gap-3">
            <a href="<?= base_url('controlplane/plans') ?>" class="px-4 py-2 rounded-lg border border-slate-200 text-sm font-semibold text-slate-600">
                Back
            </a>
            <button class="px-4 py-2 rounded-lg bg-emerald-600 text-white text-sm font-semibold cursor-not-allowed" disabled>
                Save Plan
            </button>
        </div>
    </form>
</div>
<?= $this->endSection() ?>
