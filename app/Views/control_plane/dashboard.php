<?= $this->extend('layouts/main_control_plane') ?>

<?= $this->section('content') ?>
<div class="mb-8">
    <p class="text-xs uppercase tracking-widest text-gray-400 font-semibold">Control Plane</p>
    <h1 class="text-2xl font-bold text-gray-900">Platform Overview</h1>
    <p class="text-sm text-gray-600 mt-1">Health and governance signals across tenants.</p>
</div>
<div class="mb-8 space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <p class="text-xs uppercase tracking-widest text-slate-400 font-semibold">Key Metrics</p>
            <h2 class="text-lg font-bold text-slate-800">KPI Snapshot</h2>
            <p class="text-xs text-slate-500 mt-1">Grouped signals for tenant health and billing posture.</p>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h3 class="text-xs uppercase tracking-widest text-slate-400 font-bold">Tenant Health</h3>
                    <p class="text-xs text-slate-500 mt-1">Adoption and active usage.</p>
                </div>
                <span class="w-9 h-9 rounded-full bg-indigo-50 flex items-center justify-center text-indigo-600">
                    <i class="fas fa-heartbeat text-sm"></i>
                </span>
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div class="bg-slate-50 rounded-xl border border-slate-100 p-4 flex items-center">
                    <div class="p-2 bg-indigo-100 rounded-lg mr-3">
                        <i class="fas fa-hospital text-indigo-600 text-lg"></i>
                    </div>
                    <div>
                        <p class="text-xs font-semibold text-slate-500 uppercase tracking-wide">Active Clinics</p>
                        <h3 class="text-xl font-bold text-slate-800"><?= number_format($stats['active_clinics'] ?? 0) ?></h3>
                    </div>
                </div>
                <div class="bg-slate-50 rounded-xl border border-slate-100 p-4 flex items-center">
                    <div class="p-2 bg-blue-100 rounded-lg mr-3">
                        <i class="fas fa-users text-blue-600 text-lg"></i>
                    </div>
                    <div>
                        <p class="text-xs font-semibold text-slate-500 uppercase tracking-wide">Active Users</p>
                        <h3 class="text-xl font-bold text-slate-800"><?= number_format($stats['active_users'] ?? 0) ?></h3>
                    </div>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h3 class="text-xs uppercase tracking-widest text-slate-400 font-bold">Billing & Plans</h3>
                    <p class="text-xs text-slate-500 mt-1">Subscription health and coverage.</p>
                </div>
                <span class="w-9 h-9 rounded-full bg-emerald-50 flex items-center justify-center text-emerald-600">
                    <i class="fas fa-receipt text-sm"></i>
                </span>
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div class="bg-slate-50 rounded-xl border border-slate-100 p-4 flex items-center">
                    <div class="p-2 bg-emerald-100 rounded-lg mr-3">
                        <i class="fas fa-file-invoice-dollar text-emerald-600 text-lg"></i>
                    </div>
                    <div>
                        <p class="text-xs font-semibold text-slate-500 uppercase tracking-wide">Active Subscriptions</p>
                        <h3 class="text-xl font-bold text-slate-800"><?= number_format($stats['active_subscriptions'] ?? 0) ?></h3>
                    </div>
                </div>
                <div class="bg-slate-50 rounded-xl border border-slate-100 p-4 flex items-center">
                    <div class="p-2 bg-indigo-100 rounded-lg mr-3">
                        <i class="fas fa-layer-group text-indigo-600 text-lg"></i>
                    </div>
                    <div>
                        <p class="text-xs font-semibold text-slate-500 uppercase tracking-wide">Active Plans</p>
                        <h3 class="text-xl font-bold text-slate-800"><?= number_format($stats['active_plans'] ?? 0) ?></h3>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Recent Activity / Quick Actions Section -->
<div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-100 bg-gray-50 flex justify-between items-center">
            <h3 class="font-bold text-slate-700">Quick Actions</h3>
        </div>
        <div class="p-6 grid grid-cols-2 gap-4">
            <a href="<?= base_url('controlplane/onboarding/clinic/create') ?>" class="flex flex-col items-center justify-center p-4 bg-indigo-50 border border-indigo-100 rounded-lg hover:bg-indigo-100 transition-colors group">
                <i class="fas fa-plus-circle text-indigo-500 text-2xl mb-2 group-hover:scale-110 transition-transform"></i>
                <span class="text-sm font-semibold text-indigo-700">Onboard Clinic</span>
            </a>
            <a href="<?= base_url('controlplane/console') ?>" class="flex flex-col items-center justify-center p-4 bg-slate-50 border border-slate-100 rounded-lg hover:bg-slate-100 transition-colors group">
                <i class="fas fa-terminal text-slate-500 text-2xl mb-2 group-hover:scale-110 transition-transform"></i>
                <span class="text-sm font-semibold text-slate-700">System Console</span>
            </a>
        </div>
        <div class="px-6 pb-6">
            <a href="<?= base_url('controlplane/operations') ?>" class="text-xs font-semibold text-indigo-600 hover:text-indigo-800 uppercase tracking-widest">
                View Operations
            </a>
        </div>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-100 bg-gray-50">
            <h3 class="font-bold text-slate-700">System Status</h3>
        </div>
        <div class="p-6">
            <div class="space-y-4">
                <div class="flex items-center justify-between text-sm">
                    <span class="text-slate-600">Database Connection</span>
                    <span class="px-2 py-1 bg-green-100 text-green-700 rounded-full text-xs font-bold">Operational</span>
                </div>
                <div class="flex items-center justify-between text-sm">
                    <span class="text-slate-600">Queue Worker</span>
                    <span class="px-2 py-1 bg-green-100 text-green-700 rounded-full text-xs font-bold">Running</span>
                </div>
                <div class="flex items-center justify-between text-sm">
                    <span class="text-slate-600">Storage Service</span>
                    <span class="px-2 py-1 bg-green-100 text-green-700 rounded-full text-xs font-bold">Healthy</span>
                </div>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-100 bg-gray-50">
            <h3 class="font-bold text-slate-700">Risk Posture</h3>
        </div>
        <div class="p-6 space-y-4">
            <div class="flex items-center justify-between text-sm">
                <span class="text-slate-600">Tenant Isolation</span>
                <span class="px-2 py-1 bg-emerald-100 text-emerald-700 rounded-full text-xs font-bold">Enforced</span>
            </div>
            <div class="flex items-center justify-between text-sm">
                <span class="text-slate-600">Legacy APIs</span>
                <span class="px-2 py-1 bg-amber-100 text-amber-700 rounded-full text-xs font-bold">Monitor</span>
            </div>
            <div class="flex items-center justify-between text-sm">
                <span class="text-slate-600">Global Mode</span>
                <span class="px-2 py-1 bg-indigo-100 text-indigo-700 rounded-full text-xs font-bold">Active</span>
            </div>
        </div>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mt-8">
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-100 bg-gray-50 flex items-center justify-between">
            <h3 class="font-bold text-slate-700">Open Incidents</h3>
            <span class="text-xs font-semibold text-slate-400 uppercase tracking-widest">Last 7 days</span>
        </div>
        <div class="p-8 text-center">
            <div class="w-12 h-12 rounded-full bg-emerald-50 text-emerald-600 flex items-center justify-center mx-auto mb-3">
                <i class="fas fa-check-circle text-lg"></i>
            </div>
            <p class="text-sm font-semibold text-slate-700">No incidents reported</p>
            <p class="text-xs text-slate-500 mt-1">All monitored services are stable.</p>
        </div>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-100 bg-gray-50 flex items-center justify-between">
            <h3 class="font-bold text-slate-700">Review Queue</h3>
            <span class="text-xs font-semibold text-slate-400 uppercase tracking-widest">Governance</span>
        </div>
        <div class="p-8 text-center">
            <div class="w-12 h-12 rounded-full bg-blue-50 text-blue-600 flex items-center justify-center mx-auto mb-3">
                <i class="fas fa-clipboard-check text-lg"></i>
            </div>
            <p class="text-sm font-semibold text-slate-700">Nothing pending</p>
            <p class="text-xs text-slate-500 mt-1">No approvals or escalations waiting.</p>
        </div>
    </div>
</div>
<?= $this->endSection() ?>
