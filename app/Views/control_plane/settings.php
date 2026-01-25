<?= $this->extend('layouts/main_control_plane') ?>

<?= $this->section('content') ?>
<div class="min-h-screen bg-gradient-to-br from-slate-50 via-gray-50 to-zinc-100 relative overflow-hidden">
    <!-- Animated Background Elements -->
    <div class="absolute inset-0 overflow-hidden pointer-events-none">
        <div class="absolute -top-40 -right-40 w-80 h-80 bg-gradient-to-br from-slate-400/10 to-gray-600/10 rounded-full blur-3xl animate-pulse"></div>
        <div class="absolute -bottom-40 -left-40 w-80 h-80 bg-gradient-to-br from-slate-400/10 to-zinc-600/10 rounded-full blur-3xl animate-pulse delay-1000"></div>
    </div>

    <div class="container mx-auto relative z-10 px-4 py-6">
        <!-- Page Header -->
        <div class="mb-8">
            <div class="bg-white/80 backdrop-blur-sm border border-white/30 rounded-xl p-6 shadow-lg">
                <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-6">
                    <div class="flex items-center space-x-4">
                        <div class="w-12 h-12 bg-gradient-to-br from-slate-700 to-gray-800 rounded-xl flex items-center justify-center text-white shadow-lg">
                            <i class="fas fa-cogs text-xl"></i>
                        </div>
                        <div>
                            <h1 class="text-3xl font-bold text-gray-900">Global Configuration</h1>
                            <p class="text-gray-600">Control plane policies and system-wide defaults</p>
                        </div>
                    </div>
                    <div class="flex flex-wrap gap-3">
                        <span class="px-3 py-1 rounded-full bg-emerald-100 text-emerald-700 text-xs font-semibold">Global Mode</span>
                        <span class="px-3 py-1 rounded-full bg-slate-100 text-slate-600 text-xs font-semibold">Read-only (Staged)</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Settings Layout -->
        <div class="flex flex-col lg:flex-row gap-8">
            <!-- Sidebar -->
            <div class="lg:w-72 flex-shrink-0">
                <div class="bg-white/90 backdrop-blur-xl rounded-2xl shadow-xl border border-white/50 p-4 sticky top-6">
                    <h3 class="text-xs font-bold text-slate-400 uppercase tracking-widest mb-4 px-2">Settings Menu</h3>
                    <nav class="space-y-1">
                        <button onclick="showTab('retention')" id="nav-retention" class="w-full flex items-center px-4 py-3 text-left text-sm font-medium rounded-xl transition-all duration-200 bg-gradient-to-r from-slate-700 to-gray-800 text-white shadow-md">
                            <i class="fas fa-history w-5 h-5 mr-3 text-slate-300"></i>
                            Retention & Audit
                        </button>
                        <button onclick="showTab('features')" id="nav-features" class="w-full flex items-center px-4 py-3 text-left text-sm font-medium rounded-xl transition-all duration-200 text-slate-600 hover:bg-slate-50 hover:text-slate-900">
                            <i class="fas fa-toggle-on w-5 h-5 mr-3 text-slate-400"></i>
                            Feature Flags
                        </button>
                        <button onclick="showTab('limits')" id="nav-limits" class="w-full flex items-center px-4 py-3 text-left text-sm font-medium rounded-xl transition-all duration-200 text-slate-600 hover:bg-slate-50 hover:text-slate-900">
                            <i class="fas fa-tachometer-alt w-5 h-5 mr-3 text-slate-400"></i>
                            System Limits
                        </button>
                        <button onclick="showTab('notifications')" id="nav-notifications" class="w-full flex items-center px-4 py-3 text-left text-sm font-medium rounded-xl transition-all duration-200 text-slate-600 hover:bg-slate-50 hover:text-slate-900">
                            <i class="fas fa-bell w-5 h-5 mr-3 text-slate-400"></i>
                            Notifications
                        </button>
                    </nav>
                </div>
            </div>

            <!-- Content Area -->
            <div class="flex-1">
                <!-- Retention & Audit -->
                <div id="tab-retention" class="settings-content">
                    <div class="bg-white rounded-2xl shadow-xl border border-slate-200 overflow-hidden">
                        <div class="bg-gradient-to-r from-slate-700 to-gray-800 p-8">
                            <div class="flex items-center space-x-4">
                                <div class="w-12 h-12 bg-white/10 rounded-xl flex items-center justify-center backdrop-blur-sm">
                                    <i class="fas fa-history text-white text-xl"></i>
                                </div>
                                <div>
                                    <h3 class="text-xl font-bold text-white">Retention & Audit</h3>
                                    <p class="text-slate-300 text-sm">Configure data lifecycle and compliance logging</p>
                                </div>
                            </div>
                        </div>
                        <div class="p-8 space-y-6">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Audit Log Retention</label>
                                    <div class="relative">
                                        <input type="text" value="365" disabled class="w-full pl-4 pr-12 py-3 bg-slate-50 border border-slate-200 rounded-xl text-slate-500 font-medium cursor-not-allowed">
                                        <span class="absolute right-4 top-3.5 text-sm text-slate-400">Days</span>
                                    </div>
                                    <p class="mt-2 text-xs text-slate-400">Retention period for immutable audit trails.</p>
                                </div>
                                <div>
                                    <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Log Exports</label>
                                    <select disabled class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl text-slate-500 font-medium cursor-not-allowed appearance-none">
                                        <option>Manual approval required</option>
                                    </select>
                                    <p class="mt-2 text-xs text-slate-400">Security requirement for bulk data egress.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Feature Flags -->
                <div id="tab-features" class="settings-content hidden">
                    <div class="bg-white rounded-2xl shadow-xl border border-slate-200 overflow-hidden">
                        <div class="bg-gradient-to-r from-indigo-600 to-violet-600 p-8">
                            <div class="flex items-center space-x-4">
                                <div class="w-12 h-12 bg-white/10 rounded-xl flex items-center justify-center backdrop-blur-sm">
                                    <i class="fas fa-toggle-on text-white text-xl"></i>
                                </div>
                                <div>
                                    <h3 class="text-xl font-bold text-white">Feature Flags</h3>
                                    <p class="text-indigo-100 text-sm">Manage global feature rollouts and experiments</p>
                                </div>
                            </div>
                        </div>
                        <div class="p-8">
                            <div class="space-y-4">
                                <div class="flex items-center justify-between p-4 bg-slate-50 rounded-xl border border-slate-100">
                                    <div class="flex items-center gap-4">
                                        <div class="w-10 h-10 rounded-full bg-white flex items-center justify-center text-slate-400 shadow-sm">
                                            <i class="fas fa-rocket"></i>
                                        </div>
                                        <div>
                                            <h4 class="font-semibold text-slate-700">Early Access Modules</h4>
                                            <p class="text-xs text-slate-500">Enable beta features for flagged tenants.</p>
                                        </div>
                                    </div>
                                    <span class="px-3 py-1 rounded-full bg-slate-200 text-slate-500 text-xs font-bold uppercase tracking-wider">Disabled</span>
                                </div>

                                <div class="flex items-center justify-between p-4 bg-slate-50 rounded-xl border border-slate-100">
                                    <div class="flex items-center gap-4">
                                        <div class="w-10 h-10 rounded-full bg-white flex items-center justify-center text-slate-400 shadow-sm">
                                            <i class="fas fa-credit-card"></i>
                                        </div>
                                        <div>
                                            <h4 class="font-semibold text-slate-700">Experimental Billing</h4>
                                            <p class="text-xs text-slate-500">New Stripe integration testing.</p>
                                        </div>
                                    </div>
                                    <span class="px-3 py-1 rounded-full bg-slate-200 text-slate-500 text-xs font-bold uppercase tracking-wider">Disabled</span>
                                </div>

                                <div class="flex items-center justify-between p-4 bg-slate-50 rounded-xl border border-slate-100">
                                    <div class="flex items-center gap-4">
                                        <div class="w-10 h-10 rounded-full bg-white flex items-center justify-center text-slate-400 shadow-sm">
                                            <i class="fas fa-file-export"></i>
                                        </div>
                                        <div>
                                            <h4 class="font-semibold text-slate-700">Audit Vault Exports</h4>
                                            <p class="text-xs text-slate-500">Allow superadmins to export raw audit logs.</p>
                                        </div>
                                    </div>
                                    <span class="px-3 py-1 rounded-full bg-slate-200 text-slate-500 text-xs font-bold uppercase tracking-wider">Disabled</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- System Limits -->
                <div id="tab-limits" class="settings-content hidden">
                    <div class="bg-white rounded-2xl shadow-xl border border-slate-200 overflow-hidden">
                        <div class="bg-gradient-to-r from-blue-600 to-cyan-600 p-8">
                            <div class="flex items-center space-x-4">
                                <div class="w-12 h-12 bg-white/10 rounded-xl flex items-center justify-center backdrop-blur-sm">
                                    <i class="fas fa-tachometer-alt text-white text-xl"></i>
                                </div>
                                <div>
                                    <h3 class="text-xl font-bold text-white">System Limits</h3>
                                    <p class="text-blue-100 text-sm">Default quotas for new tenants</p>
                                </div>
                            </div>
                        </div>
                        <div class="p-8">
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                                <div class="p-6 bg-slate-50 rounded-2xl border border-slate-100 text-center">
                                    <p class="text-xs font-bold text-slate-400 uppercase tracking-widest mb-2">Patient Cap</p>
                                    <p class="text-3xl font-black text-slate-700">1,000</p>
                                    <p class="text-xs text-slate-400 mt-1">Default records</p>
                                </div>
                                <div class="p-6 bg-slate-50 rounded-2xl border border-slate-100 text-center">
                                    <p class="text-xs font-bold text-slate-400 uppercase tracking-widest mb-2">Storage</p>
                                    <p class="text-3xl font-black text-slate-700">10 GB</p>
                                    <p class="text-xs text-slate-400 mt-1">S3 allocation</p>
                                </div>
                                <div class="p-6 bg-slate-50 rounded-2xl border border-slate-100 text-center">
                                    <p class="text-xs font-bold text-slate-400 uppercase tracking-widest mb-2">Seats</p>
                                    <p class="text-3xl font-black text-slate-700">25</p>
                                    <p class="text-xs text-slate-400 mt-1">User licenses</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Notifications -->
                <div id="tab-notifications" class="settings-content hidden">
                    <div class="bg-white rounded-2xl shadow-xl border border-slate-200 overflow-hidden">
                        <div class="bg-gradient-to-r from-amber-500 to-orange-600 p-8">
                            <div class="flex items-center space-x-4">
                                <div class="w-12 h-12 bg-white/10 rounded-xl flex items-center justify-center backdrop-blur-sm">
                                    <i class="fas fa-bell text-white text-xl"></i>
                                </div>
                                <div>
                                    <h3 class="text-xl font-bold text-white">Notifications</h3>
                                    <p class="text-amber-100 text-sm">Delivery providers and rate limits</p>
                                </div>
                            </div>
                        </div>
                        <div class="p-8 space-y-6">
                            <div class="grid grid-cols-1 gap-6">
                                <div>
                                    <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Delivery Provider</label>
                                    <div class="flex items-center justify-between p-4 bg-white border border-slate-200 rounded-xl">
                                        <div class="flex items-center gap-3">
                                            <div class="w-8 h-8 bg-green-100 rounded-lg flex items-center justify-center text-green-600">
                                                <i class="fas fa-server"></i>
                                            </div>
                                            <span class="font-semibold text-slate-700">Primary SMTP Relay</span>
                                        </div>
                                        <span class="text-xs font-bold text-green-600 uppercase tracking-wide">Active</span>
                                    </div>
                                </div>
                                <div class="grid grid-cols-2 gap-6">
                                    <div>
                                        <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Escalation Window</label>
                                        <input type="text" value="15 min" disabled class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl text-slate-500 font-medium">
                                    </div>
                                    <div>
                                        <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Outbound Throttle</label>
                                        <input type="text" value="120 / min" disabled class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl text-slate-500 font-medium">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function showTab(tabName) {
    // Hide all contents
    document.querySelectorAll('.settings-content').forEach(el => el.classList.add('hidden'));
    
    // Show target content
    document.getElementById('tab-' + tabName).classList.remove('hidden');
    
    // Reset all nav buttons
    const navButtons = document.querySelectorAll('[id^="nav-"]');
    navButtons.forEach(btn => {
        // Reset to default style
        btn.className = 'w-full flex items-center px-4 py-3 text-left text-sm font-medium rounded-xl transition-all duration-200 text-slate-600 hover:bg-slate-50 hover:text-slate-900';
        // Reset icon color
        const icon = btn.querySelector('i');
        if (icon) icon.className = icon.className.replace('text-slate-300', 'text-slate-400');
    });
    
    // Activate target button
    const activeBtn = document.getElementById('nav-' + tabName);
    
    // Determine active color based on tab (matching the content header gradients)
    let activeClass = 'bg-gradient-to-r from-slate-700 to-gray-800 text-white shadow-md'; // Default Retention
    if (tabName === 'features') activeClass = 'bg-gradient-to-r from-indigo-600 to-violet-600 text-white shadow-md';
    if (tabName === 'limits') activeClass = 'bg-gradient-to-r from-blue-600 to-cyan-600 text-white shadow-md';
    if (tabName === 'notifications') activeClass = 'bg-gradient-to-r from-amber-500 to-orange-600 text-white shadow-md';
    
    activeBtn.className = `w-full flex items-center px-4 py-3 text-left text-sm font-medium rounded-xl transition-all duration-200 ${activeClass}`;
    
    // Set icon color to white/light
    const activeIcon = activeBtn.querySelector('i');
    if (activeIcon) {
        activeIcon.classList.remove('text-slate-400');
        activeIcon.classList.add('text-white/80');
    }
}
</script>
<?= $this->endSection() ?>