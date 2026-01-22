<?= $this->extend('layouts/main_control_plane') ?>

<?= $this->section('content') ?>
<div class="max-w-5xl mx-auto">
    <div class="mb-8">
        <p class="text-xs uppercase tracking-widest text-gray-400 font-semibold">Control Plane</p>
        <h1 class="text-2xl font-bold text-slate-900">Admin Tools</h1>
        <p class="text-sm text-slate-500">Centralized utilities for governance, access, and platform safety.</p>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <div class="bg-white p-6 rounded-2xl border border-gray-200 shadow-sm">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h3 class="text-xs font-bold text-slate-400 uppercase tracking-widest">Access & Identity</h3>
                    <p class="text-xs text-slate-500 mt-1">RBAC visibility and lifecycle controls.</p>
                </div>
                <span class="w-9 h-9 rounded-full bg-emerald-50 flex items-center justify-center text-emerald-600">
                    <i class="fas fa-shield-alt text-sm"></i>
                </span>
            </div>
            <div class="grid grid-cols-1 gap-2">
                <a href="<?= base_url('rbac/status') ?>" class="flex items-center justify-between w-full px-4 py-3 rounded-xl border border-slate-200 bg-white text-sm font-semibold text-slate-700 hover:bg-slate-50 transition-colors">
                    <span class="flex items-center gap-2">
                        <i class="fas fa-shield-alt"></i> RBAC Status
                    </span>
                    <i class="fas fa-arrow-right text-xs"></i>
                </a>
                <a href="<?= base_url('rbac/sync') ?>" class="flex items-center justify-between w-full px-4 py-3 rounded-xl border border-slate-200 bg-white text-sm font-semibold text-slate-700 hover:bg-slate-50 transition-colors">
                    <span class="flex items-center gap-2">
                        <i class="fas fa-sync-alt"></i> Sync Permissions
                    </span>
                    <i class="fas fa-arrow-right text-xs"></i>
                </a>
                <a href="<?= base_url('rbac/init') ?>" class="flex items-center justify-between w-full px-4 py-3 rounded-xl border border-slate-200 bg-white text-sm font-semibold text-slate-700 hover:bg-slate-50 transition-colors">
                    <span class="flex items-center gap-2">
                        <i class="fas fa-seedling"></i> Initialize RBAC
                    </span>
                    <i class="fas fa-arrow-right text-xs"></i>
                </a>
                <a href="<?= base_url('rbac/setup') ?>" class="flex items-center justify-between w-full px-4 py-3 rounded-xl border border-slate-200 bg-white text-sm font-semibold text-slate-700 hover:bg-slate-50 transition-colors">
                    <span class="flex items-center gap-2">
                        <i class="fas fa-tools"></i> RBAC Setup
                    </span>
                    <i class="fas fa-arrow-right text-xs"></i>
                </a>
            </div>
        </div>

        <div class="bg-white p-6 rounded-2xl border border-gray-200 shadow-sm">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h3 class="text-xs font-bold text-slate-400 uppercase tracking-widest">Tenant Operations</h3>
                    <p class="text-xs text-slate-500 mt-1">Provisioning and active tenant oversight.</p>
                </div>
                <span class="w-9 h-9 rounded-full bg-indigo-50 flex items-center justify-center text-indigo-600">
                    <i class="fas fa-hospital-user text-sm"></i>
                </span>
            </div>
            <div class="space-y-2">
                <a href="<?= base_url('controlplane/onboarding/clinic/create') ?>" class="flex items-center justify-between w-full px-4 py-3 rounded-xl border border-indigo-100 bg-indigo-50 text-sm font-semibold text-indigo-700 hover:bg-indigo-100 transition-colors">
                    <span class="flex items-center gap-2">
                        <i class="fas fa-plus"></i> Onboard New Clinic
                    </span>
                    <i class="fas fa-arrow-right text-xs"></i>
                </a>
                <a href="<?= base_url('controlplane/operations') ?>" class="flex items-center justify-between w-full px-4 py-3 rounded-xl border border-slate-200 bg-white text-sm font-semibold text-slate-700 hover:bg-slate-50 transition-colors">
                    <span class="flex items-center gap-2">
                        <i class="fas fa-cogs"></i> Operations Queue
                    </span>
                    <i class="fas fa-arrow-right text-xs"></i>
                </a>
            </div>
        </div>

        <div class="bg-white p-6 rounded-2xl border border-gray-200 shadow-sm">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h3 class="text-xs font-bold text-slate-400 uppercase tracking-widest">Observability</h3>
                    <p class="text-xs text-slate-500 mt-1">Audit, console, and runtime visibility.</p>
                </div>
                <span class="w-9 h-9 rounded-full bg-slate-100 flex items-center justify-center text-slate-600">
                    <i class="fas fa-terminal text-sm"></i>
                </span>
            </div>
            <div class="space-y-2">
                <a href="<?= base_url('controlplane/console') ?>" class="flex items-center justify-between w-full px-4 py-3 rounded-xl border border-slate-200 bg-white text-sm font-semibold text-slate-700 hover:bg-slate-50 transition-colors">
                    <span class="flex items-center gap-2">
                        <i class="fas fa-terminal"></i> Operator Console
                    </span>
                    <i class="fas fa-arrow-right text-xs"></i>
                </a>
                <a href="<?= base_url('controlplane/dashboard') ?>" class="flex items-center justify-between w-full px-4 py-3 rounded-xl border border-slate-200 bg-white text-sm font-semibold text-slate-700 hover:bg-slate-50 transition-colors">
                    <span class="flex items-center gap-2">
                        <i class="fas fa-chart-line"></i> KPI Dashboard
                    </span>
                    <i class="fas fa-arrow-right text-xs"></i>
                </a>
            </div>
        </div>

        <div class="bg-white p-6 rounded-2xl border border-gray-200 shadow-sm">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h3 class="text-xs font-bold text-slate-400 uppercase tracking-widest">Platform Policy</h3>
                    <p class="text-xs text-slate-500 mt-1">Configuration and high-impact controls.</p>
                </div>
                <span class="w-9 h-9 rounded-full bg-rose-50 flex items-center justify-center text-rose-600">
                    <i class="fas fa-exclamation-triangle text-sm"></i>
                </span>
            </div>
            <div class="grid grid-cols-1 gap-2">
                <a href="<?= base_url('controlplane/settings') ?>" class="flex items-center justify-between w-full px-4 py-3 rounded-xl border border-slate-200 bg-white text-sm font-semibold text-slate-700 hover:bg-slate-50 transition-colors">
                    <span class="flex items-center gap-2">
                        <i class="fas fa-sliders-h"></i> Global Settings
                    </span>
                    <i class="fas fa-arrow-right text-xs"></i>
                </a>
                <a href="<?= base_url('controlplane/danger') ?>" class="flex items-center justify-between w-full px-4 py-3 rounded-xl border border-rose-100 bg-rose-50 text-sm font-semibold text-rose-700 hover:bg-rose-100 transition-colors">
                    <span class="flex items-center gap-2">
                        <i class="fas fa-exclamation-triangle"></i> Danger Zone
                    </span>
                    <i class="fas fa-arrow-right text-xs"></i>
                </a>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>
