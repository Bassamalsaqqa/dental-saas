<?= $this->extend('layouts/main_control_plane') ?>

<?= $this->section('content') ?>
<div class="max-w-6xl mx-auto space-y-8">
    <div class="glass-card rounded-3xl p-6 md:p-8">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-6">
            <div>
                <p class="text-xs uppercase tracking-[0.3em] text-emerald-700 font-semibold">Global Settings</p>
                <h1 class="text-3xl font-semibold text-slate-900 mt-2">Configure the control plane once.</h1>
                <p class="mt-3 text-sm text-slate-600 max-w-2xl">
                    These settings govern policies that apply across all clinics. Editing is currently staged;
                    enablement will be wired to policy enforcement in Phase 3.
                </p>
            </div>
            <div class="flex flex-wrap gap-3">
                <span class="px-3 py-1 rounded-full bg-emerald-100 text-emerald-700 text-xs font-semibold">Global Mode</span>
                <span class="px-3 py-1 rounded-full bg-slate-100 text-slate-600 text-xs font-semibold">Read-only</span>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-[1fr,1fr] gap-6">
        <div class="glass-card rounded-3xl p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-xs font-bold text-slate-500 uppercase tracking-widest">Retention & Audit</h3>
                <span class="text-[10px] text-slate-400 uppercase tracking-widest">Planned</span>
            </div>
            <div class="space-y-4">
                <div>
                    <label class="block text-xs uppercase tracking-widest text-slate-400 mb-2">Audit retention</label>
                    <div class="flex items-center gap-2">
                        <input type="text" placeholder="365 days" class="w-full rounded-xl border-slate-200 text-sm shadow-sm" disabled>
                        <span class="text-xs text-slate-400">Days</span>
                    </div>
                </div>
                <div>
                    <label class="block text-xs uppercase tracking-widest text-slate-400 mb-2">Log exports</label>
                    <select class="w-full rounded-xl border-slate-200 text-sm shadow-sm" disabled>
                        <option>Manual approval required</option>
                    </select>
                </div>
            </div>
        </div>

        <div class="glass-card rounded-3xl p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-xs font-bold text-slate-500 uppercase tracking-widest">Feature Flags</h3>
                <span class="text-[10px] text-slate-400 uppercase tracking-widest">Planned</span>
            </div>
            <div class="space-y-3 text-sm text-slate-600">
                <div class="flex items-center justify-between">
                    <span>Early access modules</span>
                    <span class="text-xs font-semibold text-slate-400">Disabled</span>
                </div>
                <div class="flex items-center justify-between">
                    <span>Experimental billing</span>
                    <span class="text-xs font-semibold text-slate-400">Disabled</span>
                </div>
                <div class="flex items-center justify-between">
                    <span>Audit vault exports</span>
                    <span class="text-xs font-semibold text-slate-400">Disabled</span>
                </div>
            </div>
        </div>

        <div class="glass-card rounded-3xl p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-xs font-bold text-slate-500 uppercase tracking-widest">System Limits</h3>
                <span class="text-[10px] text-slate-400 uppercase tracking-widest">Planned</span>
            </div>
            <div class="space-y-4">
                <div class="flex items-center justify-between text-sm">
                    <span class="text-slate-600">Default patient cap</span>
                    <span class="font-semibold text-slate-700">1000</span>
                </div>
                <div class="flex items-center justify-between text-sm">
                    <span class="text-slate-600">Default storage</span>
                    <span class="font-semibold text-slate-700">10 GB</span>
                </div>
                <div class="flex items-center justify-between text-sm">
                    <span class="text-slate-600">User seats</span>
                    <span class="font-semibold text-slate-700">25</span>
                </div>
            </div>
        </div>

        <div class="glass-card rounded-3xl p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-xs font-bold text-slate-500 uppercase tracking-widest">Notifications</h3>
                <span class="text-[10px] text-slate-400 uppercase tracking-widest">Planned</span>
            </div>
            <div class="space-y-3 text-sm text-slate-600">
                <div class="flex items-center justify-between">
                    <span>Delivery provider</span>
                    <span class="font-semibold text-slate-700">Primary SMTP</span>
                </div>
                <div class="flex items-center justify-between">
                    <span>Escalation window</span>
                    <span class="font-semibold text-slate-700">15 min</span>
                </div>
                <div class="flex items-center justify-between">
                    <span>Outbound throttle</span>
                    <span class="font-semibold text-slate-700">120/min</span>
                </div>
            </div>
        </div>
    </div>

    <div class="glass-card rounded-3xl p-5 border-l-4 border-emerald-400">
        <h4 class="text-xs font-bold text-emerald-800 uppercase tracking-widest mb-2">Policy note</h4>
        <p class="text-xs text-emerald-700 leading-relaxed">
            Global settings remain code-controlled to prevent configuration drift until this control plane surface is
            fully specified and audited.
        </p>
    </div>

    <div class="glass-card rounded-3xl p-6 md:p-8">
        <div class="flex items-center justify-between mb-6">
            <div>
                <p class="text-xs uppercase tracking-[0.3em] text-blue-700 font-semibold">Email Templates</p>
                <h2 class="text-2xl font-semibold text-slate-900 mt-2">Global messaging library</h2>
                <p class="mt-2 text-sm text-slate-600">Manage templates that clinics inherit by default.</p>
            </div>
        </div>

        <div class="glass-muted rounded-2xl overflow-hidden">
            <div class="flex items-center justify-between px-6 py-4 border-b border-white/40">
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
                    <thead class="bg-white/70 text-xs uppercase tracking-widest text-slate-500">
                        <tr>
                            <th class="px-6 py-3 text-left">Template</th>
                            <th class="px-6 py-3 text-left">Category</th>
                            <th class="px-6 py-3 text-left">Status</th>
                            <th class="px-6 py-3 text-right">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-white/50">
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
</div>
<?= $this->endSection() ?>
