<?= $this->extend('layouts/main_control_plane') ?>

<?= $this->section('content') ?>
<div class="max-w-5xl mx-auto space-y-8">
    <div class="text-center">
        <p class="text-xs uppercase tracking-widest text-gray-400 font-semibold mb-2">Control Plane</p>
        <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-6">
            <i class="fas fa-cogs text-gray-400 text-2xl"></i>
        </div>
        <h1 class="text-2xl font-bold text-slate-900 mb-2">Global Settings</h1>
        <p class="text-slate-500">
            Centralized configuration for retention policies, feature flags, and system limits is not yet implemented.
        </p>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div class="bg-white border border-gray-200 rounded-2xl p-6 shadow-sm">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-xs font-bold text-slate-500 uppercase tracking-widest">Retention & Audit</h3>
                <span class="text-[10px] text-slate-400 uppercase tracking-widest">Planned</span>
            </div>
            <p class="text-sm text-slate-600">
                Configure audit retention windows, export policies, and log sampling for control plane events.
            </p>
        </div>

        <div class="bg-white border border-gray-200 rounded-2xl p-6 shadow-sm">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-xs font-bold text-slate-500 uppercase tracking-widest">Feature Flags</h3>
                <span class="text-[10px] text-slate-400 uppercase tracking-widest">Planned</span>
            </div>
            <p class="text-sm text-slate-600">
                Roll out platform features safely and gate risky capabilities in controlled waves.
            </p>
        </div>

        <div class="bg-white border border-gray-200 rounded-2xl p-6 shadow-sm">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-xs font-bold text-slate-500 uppercase tracking-widest">System Limits</h3>
                <span class="text-[10px] text-slate-400 uppercase tracking-widest">Planned</span>
            </div>
            <p class="text-sm text-slate-600">
                Centralize tenant quotas, thresholds, and enforcement policies without manual overrides.
            </p>
        </div>

        <div class="bg-white border border-gray-200 rounded-2xl p-6 shadow-sm">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-xs font-bold text-slate-500 uppercase tracking-widest">Notifications</h3>
                <span class="text-[10px] text-slate-400 uppercase tracking-widest">Planned</span>
            </div>
            <p class="text-sm text-slate-600">
                Define global delivery policies and provider defaults for outbound notifications.
            </p>
        </div>
    </div>

    <div class="bg-blue-50 border border-blue-100 rounded-2xl p-4">
        <h4 class="text-xs font-bold text-blue-800 uppercase tracking-widest mb-2">Policy Note</h4>
        <p class="text-xs text-blue-700 leading-relaxed">
            Global settings remain code‑controlled to prevent configuration drift until this control plane surface is
            fully specified and audited.
        </p>
    </div>
</div>
<?= $this->endSection() ?>
