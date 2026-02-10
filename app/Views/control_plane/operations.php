<?= $this->extend('layouts/main_control_plane') ?>

<?= $this->section('content') ?>
<div class="min-h-screen bg-gradient-to-br from-slate-50 via-blue-50 to-indigo-100 relative overflow-hidden">
    <div class="absolute inset-0 overflow-hidden pointer-events-none">
        <div class="absolute -top-40 -right-40 w-80 h-80 bg-gradient-to-br from-amber-400/20 to-orange-600/20 rounded-full blur-3xl animate-pulse"></div>
        <div class="absolute -bottom-40 -left-40 w-80 h-80 bg-gradient-to-br from-emerald-400/20 to-cyan-600/20 rounded-full blur-3xl animate-pulse delay-1000"></div>
        <div class="absolute top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2 w-96 h-96 bg-gradient-to-br from-indigo-400/10 to-blue-600/10 rounded-full blur-3xl animate-pulse delay-2000"></div>
    </div>

    <div class="container mx-auto relative z-10 px-4 py-6 max-w-6xl">
        <div class="mb-6">
            <div class="bg-white/80 backdrop-blur-xl border border-white/30 rounded-xl p-6 shadow-lg">
                <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-6">
                    <div class="flex items-center space-x-4">
                        <div class="w-12 h-12 bg-gradient-to-br from-amber-500 to-orange-600 rounded-xl flex items-center justify-center text-white shadow-lg">
                            <i class="fas fa-cogs text-xl"></i>
                        </div>
                        <div>
                            <h1 class="text-3xl font-bold text-gray-900">Operations Console</h1>
                            <p class="text-gray-600">Control plane health, tooling, and governance shortcuts</p>
                        </div>
                    </div>
                    <div class="flex items-center gap-3">
                        <span class="px-3 py-1 rounded-full bg-emerald-100 text-emerald-700 text-xs font-semibold border border-emerald-200">
                            <i class="fas fa-globe mr-1"></i> Global Mode
                        </span>
                        <span class="px-3 py-1 rounded-full bg-slate-100 text-slate-600 text-xs font-semibold border border-slate-200">
                            <?= esc($environment ?? 'production') ?>
                        </span>
                        <span class="px-3 py-1 rounded-full bg-indigo-100 text-indigo-700 text-xs font-semibold border border-indigo-200">
                            PHP <?= esc($phpVersion ?? '-') ?>
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
            <div class="bg-white/80 backdrop-blur-xl border border-white/30 rounded-2xl shadow-lg overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100 bg-white/50 flex items-center justify-between">
                    <div class="flex items-center space-x-3">
                        <div class="w-8 h-8 bg-gradient-to-br from-emerald-500 to-teal-600 rounded-lg flex items-center justify-center text-white shadow-md">
                            <i class="fas fa-heartbeat text-sm"></i>
                        </div>
                        <div>
                            <h3 class="text-sm font-bold text-gray-900 uppercase tracking-wide">System Health</h3>
                            <p class="text-xs text-gray-500">Runtime checks</p>
                        </div>
                    </div>
                </div>
                <div class="p-6 space-y-4">
                    <?php foreach ($healthChecks as $check): ?>
                        <div class="flex items-center justify-between p-4 rounded-xl border border-gray-100 bg-white shadow-sm">
                            <div>
                                <p class="text-sm font-semibold text-gray-900"><?= esc($check['label']) ?></p>
                                <p class="text-xs text-gray-500"><?= esc($check['detail']) ?></p>
                            </div>
                            <?php if ($check['status']): ?>
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-emerald-100 text-emerald-700 border border-emerald-200">
                                    <i class="fas fa-check-circle mr-1"></i> OK
                                </span>
                            <?php else: ?>
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-rose-100 text-rose-700 border border-rose-200">
                                    <i class="fas fa-exclamation-triangle mr-1"></i> Attention
                                </span>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <div class="bg-white/80 backdrop-blur-xl border border-white/30 rounded-2xl shadow-lg overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100 bg-white/50 flex items-center justify-between">
                    <div class="flex items-center space-x-3">
                        <div class="w-8 h-8 bg-gradient-to-br from-indigo-500 to-purple-600 rounded-lg flex items-center justify-center text-white shadow-md">
                            <i class="fas fa-clipboard-list text-sm"></i>
                        </div>
                        <div>
                            <h3 class="text-sm font-bold text-gray-900 uppercase tracking-wide">Operational Tools</h3>
                            <p class="text-xs text-gray-500">Operator shortcuts</p>
                        </div>
                    </div>
                </div>
                <div class="p-4 space-y-3">
                    <a href="<?= base_url('controlplane/onboarding/clinic/create') ?>" class="group flex items-center justify-between w-full px-4 py-3 rounded-xl border border-gray-100 hover:border-indigo-200 bg-white hover:bg-indigo-50/50 transition-all hover:shadow-md">
                        <div class="flex items-center gap-4">
                            <div class="w-10 h-10 rounded-xl bg-indigo-100 text-indigo-600 flex items-center justify-center group-hover:bg-indigo-600 group-hover:text-white transition-colors duration-300">
                                <i class="fas fa-plus"></i>
                            </div>
                            <div>
                                <span class="block text-sm font-bold text-gray-900 group-hover:text-indigo-900 transition-colors">Onboard New Clinic</span>
                                <span class="text-xs text-gray-500">Create tenant & admin</span>
                            </div>
                        </div>
                        <i class="fas fa-chevron-right text-xs text-gray-300 group-hover:text-indigo-600"></i>
                    </a>
                    <a href="<?= base_url('controlplane/clinics') ?>" class="group flex items-center justify-between w-full px-4 py-3 rounded-xl border border-gray-100 hover:border-emerald-200 bg-white hover:bg-emerald-50/50 transition-all hover:shadow-md">
                        <div class="flex items-center gap-4">
                            <div class="w-10 h-10 rounded-xl bg-emerald-100 text-emerald-600 flex items-center justify-center group-hover:bg-emerald-600 group-hover:text-white transition-colors duration-300">
                                <i class="fas fa-hospital-user"></i>
                            </div>
                            <div>
                                <span class="block text-sm font-bold text-gray-900 group-hover:text-emerald-900 transition-colors">Review Clinics</span>
                                <span class="text-xs text-gray-500">Status checks & audits</span>
                            </div>
                        </div>
                        <i class="fas fa-chevron-right text-xs text-gray-300 group-hover:text-emerald-600"></i>
                    </a>
                    <a href="<?= base_url('controlplane/plan-requests') ?>" class="group flex items-center justify-between w-full px-4 py-3 rounded-xl border border-gray-100 hover:border-amber-200 bg-white hover:bg-amber-50/50 transition-all hover:shadow-md">
                        <div class="flex items-center gap-4">
                            <div class="w-10 h-10 rounded-xl bg-amber-100 text-amber-600 flex items-center justify-center group-hover:bg-amber-600 group-hover:text-white transition-colors duration-300">
                                <i class="fas fa-inbox"></i>
                            </div>
                            <div>
                                <span class="block text-sm font-bold text-gray-900 group-hover:text-amber-900 transition-colors">Process Requests</span>
                                <span class="text-xs text-gray-500">Subscription changes</span>
                            </div>
                        </div>
                        <i class="fas fa-chevron-right text-xs text-gray-300 group-hover:text-amber-600"></i>
                    </a>
                    <a href="<?= base_url('controlplane/activity-log') ?>" class="group flex items-center justify-between w-full px-4 py-3 rounded-xl border border-gray-100 hover:border-blue-200 bg-white hover:bg-blue-50/50 transition-all hover:shadow-md">
                        <div class="flex items-center gap-4">
                            <div class="w-10 h-10 rounded-xl bg-blue-100 text-blue-600 flex items-center justify-center group-hover:bg-blue-600 group-hover:text-white transition-colors duration-300">
                                <i class="fas fa-history"></i>
                            </div>
                            <div>
                                <span class="block text-sm font-bold text-gray-900 group-hover:text-blue-900 transition-colors">System Activity</span>
                                <span class="text-xs text-gray-500">Audit logs & events</span>
                            </div>
                        </div>
                        <i class="fas fa-chevron-right text-xs text-gray-300 group-hover:text-blue-600"></i>
                    </a>
                    <a href="<?= base_url('rbac/sync') ?>" class="group flex items-center justify-between w-full px-4 py-3 rounded-xl border border-gray-100 hover:border-slate-200 bg-white hover:bg-slate-50/50 transition-all hover:shadow-md">
                        <div class="flex items-center gap-4">
                            <div class="w-10 h-10 rounded-xl bg-slate-100 text-slate-600 flex items-center justify-center group-hover:bg-slate-700 group-hover:text-white transition-colors duration-300">
                                <i class="fas fa-sync-alt"></i>
                            </div>
                            <div>
                                <span class="block text-sm font-bold text-gray-900 group-hover:text-slate-900 transition-colors">Sync Permissions</span>
                                <span class="text-xs text-gray-500">RBAC inventory</span>
                            </div>
                        </div>
                        <i class="fas fa-chevron-right text-xs text-gray-300 group-hover:text-slate-600"></i>
                    </a>
                </div>
            </div>
        </div>

        <div class="bg-white/80 backdrop-blur-xl border border-white/30 rounded-2xl shadow-lg overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-100 bg-white/50 flex items-center justify-between">
                <div class="flex items-center space-x-3">
                    <div class="w-8 h-8 bg-gradient-to-br from-rose-500 to-red-600 rounded-lg flex items-center justify-center text-white shadow-md">
                        <i class="fas fa-tools text-sm"></i>
                    </div>
                    <div>
                        <h3 class="text-sm font-bold text-gray-900 uppercase tracking-wide">Maintenance</h3>
                        <p class="text-xs text-gray-500">Safeguarded operations</p>
                    </div>
                </div>
            </div>
            <div class="p-4 grid grid-cols-1 md:grid-cols-2 gap-4">
                <a href="<?= base_url('rbac/status') ?>" class="group flex items-center justify-between w-full px-4 py-3 rounded-xl border border-gray-100 hover:border-emerald-200 bg-white hover:bg-emerald-50/50 transition-all hover:shadow-md">
                    <div class="flex items-center gap-3">
                        <i class="fas fa-heartbeat text-emerald-500"></i>
                        <span class="text-sm font-semibold text-gray-800">RBAC Status</span>
                    </div>
                    <i class="fas fa-chevron-right text-xs text-gray-300 group-hover:text-emerald-600"></i>
                </a>
                <a href="<?= base_url('rbac/init') ?>" class="group flex items-center justify-between w-full px-4 py-3 rounded-xl border border-gray-100 hover:border-indigo-200 bg-white hover:bg-indigo-50/50 transition-all hover:shadow-md">
                    <div class="flex items-center gap-3">
                        <i class="fas fa-seedling text-indigo-500"></i>
                        <span class="text-sm font-semibold text-gray-800">Initialize RBAC</span>
                    </div>
                    <i class="fas fa-chevron-right text-xs text-gray-300 group-hover:text-indigo-600"></i>
                </a>
                <a href="<?= base_url('rbac/setup') ?>" class="group flex items-center justify-between w-full px-4 py-3 rounded-xl border border-gray-100 hover:border-amber-200 bg-white hover:bg-amber-50/50 transition-all hover:shadow-md">
                    <div class="flex items-center gap-3">
                        <i class="fas fa-tools text-amber-500"></i>
                        <span class="text-sm font-semibold text-gray-800">RBAC Setup</span>
                    </div>
                    <i class="fas fa-chevron-right text-xs text-gray-300 group-hover:text-amber-600"></i>
                </a>
                <a href="<?= base_url('controlplane/settings') ?>" class="group flex items-center justify-between w-full px-4 py-3 rounded-xl border border-gray-100 hover:border-slate-200 bg-white hover:bg-slate-50/50 transition-all hover:shadow-md">
                    <div class="flex items-center gap-3">
                        <i class="fas fa-sliders-h text-slate-500"></i>
                        <span class="text-sm font-semibold text-gray-800">Global Settings</span>
                    </div>
                    <i class="fas fa-chevron-right text-xs text-gray-300 group-hover:text-slate-600"></i>
                </a>
            </div>
        </div>

        <div id="planEnforcementTableView" class="bg-white rounded-xl border border-gray-200 shadow-lg mt-8">
            <div class="px-6 py-4 border-b border-gray-200 bg-gray-50 rounded-t-xl">
                <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
                    <div class="flex items-center space-x-3">
                        <div class="w-2 h-2 bg-rose-500 rounded-full"></div>
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900">Plan Enforcement Violations</h3>
                            <p class="text-xs text-gray-500">Blocked actions triggered by plan guardrails</p>
                        </div>
                    </div>
                    <div class="flex items-center gap-3">
                        <div class="relative">
                            <i class="fas fa-search text-gray-400 absolute left-3 top-2.5 text-sm"></i>
                            <input id="planEnforcementSearch" type="text" placeholder="Search clinic, action, reason..."
                                   class="pl-9 pr-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-rose-500 focus:border-rose-500 w-64 bg-white">
                        </div>
                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-rose-100 text-rose-700 border border-rose-200">
                            <?= count($planAudits ?? []) ?> events
                        </span>
                    </div>
                </div>
            </div>
            <div class="overflow-x-auto">
                <table id="planEnforcementTable" class="w-full min-w-full">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Timestamp</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Clinic</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Action Blocked</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Reason</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        <?php if (empty($planAudits)): ?>
                            <tr>
                                <td colspan="4" class="px-6 py-12 text-center text-gray-500">
                                    <div class="flex flex-col items-center justify-center">
                                        <div class="w-16 h-16 bg-emerald-50 rounded-2xl flex items-center justify-center mb-4 border border-emerald-100">
                                            <i class="fas fa-shield-check text-emerald-500 text-2xl"></i>
                                        </div>
                                        <p class="font-bold text-gray-900 text-sm">All systems compliant</p>
                                        <p class="text-xs text-gray-500 mt-1">No plan enforcement actions triggered recently.</p>
                                    </div>
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($planAudits as $audit): ?>
                                <?php $clinic = $clinicDirectory[$audit['clinic_id']] ?? null; ?>
                                <tr class="hover:bg-gray-50 transition-colors plan-enforcement-row">
                                    <td class="px-6 py-4 text-xs text-gray-500 font-mono whitespace-nowrap">
                                        <?= esc($audit['created_at']) ?>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="flex items-center space-x-3">
                                            <div class="w-8 h-8 rounded-lg bg-blue-100 text-blue-600 flex items-center justify-center">
                                                <i class="fas fa-hospital text-sm"></i>
                                            </div>
                                            <div>
                                                <p class="text-sm font-semibold text-gray-900">
                                                    <?= esc($clinic['name'] ?? ('Clinic #' . $audit['clinic_id'])) ?>
                                                </p>
                                                <p class="text-xs text-gray-500">
                                                    <?= $clinic ? esc($clinic['slug'] ?? '') : '' ?>
                                                    <?= $clinic ? (' • ID ' . esc($audit['clinic_id'])) : ('ID ' . esc($audit['clinic_id'])) ?>
                                                </p>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-amber-100 text-amber-700 border border-amber-200">
                                            <?= esc($audit['action_key']) ?>
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-sm text-rose-600 font-medium">
                                        <?= esc($audit['reason_code']) ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
<script>
    (function () {
        const searchInput = document.getElementById('planEnforcementSearch');
        const rows = document.querySelectorAll('.plan-enforcement-row');
        if (!searchInput || !rows.length) return;

        const filterRows = () => {
            const query = searchInput.value.trim().toLowerCase();
            rows.forEach((row) => {
                const text = row.textContent.toLowerCase();
                row.style.display = text.includes(query) ? '' : 'none';
            });
        };

        searchInput.addEventListener('input', filterRows);
    })();
</script>
<?= $this->endSection() ?>
