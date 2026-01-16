<?= $this->extend('layouts/main_control_plane') ?>

<?= $this->section('content') ?>
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
    <!-- Active Clinics Widget -->
    <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6 flex items-center">
        <div class="p-3 bg-indigo-100 rounded-full mr-4">
            <i class="fas fa-hospital text-indigo-600 text-xl"></i>
        </div>
        <div>
            <p class="text-sm font-medium text-slate-500 uppercase tracking-wide">Active Clinics</p>
            <h3 class="text-2xl font-bold text-slate-800"><?= number_format($stats['active_clinics'] ?? 0) ?></h3>
        </div>
    </div>

    <!-- Placeholder Widgets -->
    <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6 flex items-center">
        <div class="p-3 bg-green-100 rounded-full mr-4">
            <i class="fas fa-dollar-sign text-green-600 text-xl"></i>
        </div>
        <div>
            <p class="text-sm font-medium text-slate-500 uppercase tracking-wide">Total MRR</p>
            <h3 class="text-2xl font-bold text-slate-800">$0.00</h3>
        </div>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6 flex items-center">
        <div class="p-3 bg-blue-100 rounded-full mr-4">
            <i class="fas fa-users text-blue-600 text-xl"></i>
        </div>
        <div>
            <p class="text-sm font-medium text-slate-500 uppercase tracking-wide">Total Users</p>
            <h3 class="text-2xl font-bold text-slate-800">--</h3>
        </div>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6 flex items-center">
        <div class="p-3 bg-red-100 rounded-full mr-4">
            <i class="fas fa-exclamation-circle text-red-600 text-xl"></i>
        </div>
        <div>
            <p class="text-sm font-medium text-slate-500 uppercase tracking-wide">System Alerts</p>
            <h3 class="text-2xl font-bold text-slate-800">0</h3>
        </div>
    </div>
</div>

<!-- Recent Activity / Quick Actions Section -->
<div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
    <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-100 bg-slate-50 flex justify-between items-center">
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
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-100 bg-slate-50">
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
</div>
<?= $this->endSection() ?>