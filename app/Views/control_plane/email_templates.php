<?= $this->extend('layouts/main_control_plane') ?>

<?= $this->section('content') ?>
<div class="max-w-6xl mx-auto">
    <div class="mb-8">
        <p class="text-xs uppercase tracking-widest text-slate-400 font-semibold">Control Plane</p>
        <h1 class="text-2xl font-bold text-slate-900">Email Templates</h1>
        <p class="text-sm text-slate-500">Manage global messaging templates for clinics.</p>
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
            <input type="text" class="rounded-lg border border-slate-200 text-xs px-3 py-2 w-56" placeholder="Search templates" disabled>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead class="bg-slate-50 text-xs uppercase tracking-widest text-slate-500">
                    <tr>
                        <th class="px-6 py-3 text-left">Template</th>
                        <th class="px-6 py-3 text-left">Category</th>
                        <th class="px-6 py-3 text-left">Status</th>
                        <th class="px-6 py-3 text-right">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    <tr>
                        <td class="px-6 py-4 font-semibold text-slate-700">New Clinic Welcome</td>
                        <td class="px-6 py-4 text-slate-600">Onboarding</td>
                        <td class="px-6 py-4"><span class="text-xs font-semibold px-2 py-1 rounded-full bg-emerald-100 text-emerald-700">Active</span></td>
                        <td class="px-6 py-4 text-right">
                            <button class="px-3 py-1.5 rounded-lg bg-slate-100 text-slate-600 text-xs font-semibold cursor-not-allowed" disabled>Preview</button>
                        </td>
                    </tr>
                    <tr>
                        <td class="px-6 py-4 font-semibold text-slate-700">Subscription Expiring</td>
                        <td class="px-6 py-4 text-slate-600">Billing</td>
                        <td class="px-6 py-4"><span class="text-xs font-semibold px-2 py-1 rounded-full bg-amber-100 text-amber-700">Draft</span></td>
                        <td class="px-6 py-4 text-right">
                            <button class="px-3 py-1.5 rounded-lg bg-slate-100 text-slate-600 text-xs font-semibold cursor-not-allowed" disabled>Preview</button>
                        </td>
                    </tr>
                    <tr>
                        <td class="px-6 py-6 text-center text-slate-400" colspan="4">Template editor will be wired in Phase 1.</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?= $this->endSection() ?>
