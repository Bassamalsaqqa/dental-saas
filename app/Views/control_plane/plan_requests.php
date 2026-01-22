<?= $this->extend('layouts/main_control_plane') ?>

<?= $this->section('content') ?>
<div class="max-w-6xl mx-auto">
    <div class="mb-8">
        <p class="text-xs uppercase tracking-widest text-slate-400 font-semibold">Control Plane</p>
        <h1 class="text-2xl font-bold text-slate-900">Plan Requests</h1>
        <p class="text-sm text-slate-500">Approve or reject clinic subscription changes.</p>
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
                    <tr>
                        <td class="px-6 py-4 text-slate-700 font-semibold">Clinic Alpha</td>
                        <td class="px-6 py-4 text-slate-600">Growth</td>
                        <td class="px-6 py-4 text-slate-600">Monthly</td>
                        <td class="px-6 py-4 text-slate-500">2026-01-22</td>
                        <td class="px-6 py-4 text-right">
                            <div class="flex justify-end gap-2">
                                <button class="px-3 py-1.5 rounded-lg bg-emerald-50 text-emerald-700 text-xs font-semibold cursor-not-allowed" disabled>Approve</button>
                                <button class="px-3 py-1.5 rounded-lg bg-rose-50 text-rose-700 text-xs font-semibold cursor-not-allowed" disabled>Reject</button>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td class="px-6 py-6 text-center text-slate-400" colspan="5">No additional requests queued.</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?= $this->endSection() ?>
