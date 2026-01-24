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

    <div class="glass-card rounded-3xl p-6">
        <div class="flex items-center justify-between mb-6">
            <h3 class="text-xs font-bold text-slate-500 uppercase tracking-widest">Configuration overview</h3>
            <span class="text-[10px] text-slate-400 uppercase tracking-widest">Staged</span>
        </div>
        <div class="grid grid-cols-1 lg:grid-cols-[0.4fr,0.6fr] gap-6">
            <div class="space-y-3">
                <button type="button" class="w-full text-left px-4 py-3 rounded-2xl bg-slate-900 text-white text-sm font-semibold">
                    Retention & Audit
                </button>
                <button type="button" class="w-full text-left px-4 py-3 rounded-2xl bg-white text-slate-600 text-sm font-semibold border border-slate-200">
                    Feature Flags
                </button>
                <button type="button" class="w-full text-left px-4 py-3 rounded-2xl bg-white text-slate-600 text-sm font-semibold border border-slate-200">
                    System Limits
                </button>
                <button type="button" class="w-full text-left px-4 py-3 rounded-2xl bg-white text-slate-600 text-sm font-semibold border border-slate-200">
                    Notifications
                </button>
            </div>
            <div class="space-y-6">
                <div class="glass-muted rounded-2xl p-5">
                    <div class="flex items-center justify-between mb-4">
                        <h4 class="text-xs font-bold text-slate-500 uppercase tracking-widest">Retention & Audit</h4>
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

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="glass-muted rounded-2xl p-5">
                        <div class="flex items-center justify-between mb-4">
                            <h4 class="text-xs font-bold text-slate-500 uppercase tracking-widest">Feature Flags</h4>
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

                    <div class="glass-muted rounded-2xl p-5">
                        <div class="flex items-center justify-between mb-4">
                            <h4 class="text-xs font-bold text-slate-500 uppercase tracking-widest">System Limits</h4>
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
                </div>

                <div class="glass-muted rounded-2xl p-5">
                    <div class="flex items-center justify-between mb-4">
                        <h4 class="text-xs font-bold text-slate-500 uppercase tracking-widest">Notifications</h4>
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

                <div class="rounded-2xl border border-emerald-200 bg-emerald-50/70 p-4 text-xs text-emerald-700">
                    Global settings remain code-controlled to prevent configuration drift until this control plane surface is fully specified and audited.
                </div>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>
