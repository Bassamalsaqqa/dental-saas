<?= $this->extend('layouts/main_control_plane') ?>

<?= $this->section('content') ?>
<div class="min-h-screen bg-gradient-to-br from-slate-50 via-blue-50 to-indigo-100 relative overflow-hidden">
    <!-- Animated Background Elements -->
    <div class="absolute inset-0 overflow-hidden pointer-events-none">
        <div class="absolute -top-40 -right-40 w-80 h-80 bg-gradient-to-br from-blue-400/20 to-purple-600/20 rounded-full blur-3xl animate-pulse"></div>
        <div class="absolute -bottom-40 -left-40 w-80 h-80 bg-gradient-to-br from-emerald-400/20 to-cyan-600/20 rounded-full blur-3xl animate-pulse delay-1000"></div>
        <div class="absolute top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2 w-96 h-96 bg-gradient-to-br from-indigo-400/10 to-blue-600/10 rounded-full blur-3xl animate-pulse delay-2000"></div>
    </div>

    <div class="container mx-auto relative z-10 px-4 py-6">
        <!-- Page Header -->
        <div class="mb-8">
            <div class="bg-white/80 backdrop-blur-xl border border-white/30 rounded-xl p-6 shadow-lg">
                <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-6">
                    <div class="flex items-center space-x-4">
                        <div class="w-12 h-12 bg-gradient-to-br from-amber-500 to-orange-600 rounded-xl flex items-center justify-center text-white shadow-lg">
                            <i class="fas fa-cogs text-xl"></i>
                        </div>
                        <div>
                            <h1 class="text-3xl font-bold text-gray-900">Operations</h1>
                            <p class="text-gray-600">Global governance and system health</p>
                        </div>
                    </div>
                    <div class="flex items-center gap-3">
                        <span class="px-3 py-1 rounded-full bg-emerald-100 text-emerald-700 text-xs font-semibold border border-emerald-200">
                            <i class="fas fa-globe mr-1"></i> Global Mode
                        </span>
                        <span class="px-3 py-1 rounded-full bg-slate-100 text-slate-600 text-xs font-semibold border border-slate-200">
                            Operator
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Stats Grid -->
        <div class="mb-8">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 items-stretch">
                <!-- Plans -->
                <div class="group relative">
                    <div class="absolute inset-0 bg-gradient-to-br from-indigo-500/20 to-purple-600/20 rounded-2xl blur-xl group-hover:blur-2xl transition-all duration-200 opacity-0 group-hover:opacity-100"></div>
                    <div class="relative backdrop-blur-xl bg-white/80 border border-white/30 rounded-2xl p-6 shadow-2xl shadow-indigo-500/10 group-hover:shadow-indigo-500/20 transition-all duration-200 hover:scale-105 hover:-translate-y-2 h-full flex flex-col">
                        <div class="flex items-center justify-between mb-4">
                            <div class="relative p-3 rounded-xl bg-gradient-to-br from-indigo-500 to-purple-600 text-white shadow-xl">
                                <i class="fas fa-layer-group text-xl"></i>
                            </div>
                        </div>
                        <div class="space-y-1 flex-1 flex flex-col justify-end">
                            <p class="text-xs font-semibold text-gray-600 uppercase tracking-wider">Plans</p>
                            <div class="flex items-baseline gap-2">
                                <span class="text-3xl font-black text-indigo-900"><?= number_format($plansActive ?? 0) ?></span>
                                <span class="text-xs text-indigo-600 font-medium bg-indigo-50 px-2 py-0.5 rounded-full border border-indigo-100">Active</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Clinics -->
                <div class="group relative">
                    <div class="absolute inset-0 bg-gradient-to-br from-emerald-500/20 to-teal-600/20 rounded-2xl blur-xl group-hover:blur-2xl transition-all duration-200 opacity-0 group-hover:opacity-100"></div>
                    <div class="relative backdrop-blur-xl bg-white/80 border border-white/30 rounded-2xl p-6 shadow-2xl shadow-emerald-500/10 group-hover:shadow-emerald-500/20 transition-all duration-200 hover:scale-105 hover:-translate-y-2 h-full flex flex-col">
                        <div class="flex items-center justify-between mb-4">
                            <div class="relative p-3 rounded-xl bg-gradient-to-br from-emerald-500 to-teal-600 text-white shadow-xl">
                                <i class="fas fa-hospital text-xl"></i>
                            </div>
                        </div>
                        <div class="space-y-1 flex-1 flex flex-col justify-end">
                            <p class="text-xs font-semibold text-gray-600 uppercase tracking-wider">Clinics</p>
                            <div class="flex items-baseline gap-2">
                                <span class="text-3xl font-black text-emerald-900"><?= number_format($clinicsTotal ?? 0) ?></span>
                                <span class="text-xs text-emerald-600 font-medium bg-emerald-50 px-2 py-0.5 rounded-full border border-emerald-100">Registered</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Subscriptions -->
                <div class="group relative">
                    <div class="absolute inset-0 bg-gradient-to-br from-blue-500/20 to-cyan-600/20 rounded-2xl blur-xl group-hover:blur-2xl transition-all duration-200 opacity-0 group-hover:opacity-100"></div>
                    <div class="relative backdrop-blur-xl bg-white/80 border border-white/30 rounded-2xl p-6 shadow-2xl shadow-blue-500/10 group-hover:shadow-blue-500/20 transition-all duration-200 hover:scale-105 hover:-translate-y-2 h-full flex flex-col">
                        <div class="flex items-center justify-between mb-4">
                            <div class="relative p-3 rounded-xl bg-gradient-to-br from-blue-500 to-cyan-600 text-white shadow-xl">
                                <i class="fas fa-receipt text-xl"></i>
                            </div>
                        </div>
                        <div class="space-y-1 flex-1 flex flex-col justify-end">
                            <p class="text-xs font-semibold text-gray-600 uppercase tracking-wider">Subscriptions</p>
                            <div class="flex items-baseline gap-2">
                                <span class="text-3xl font-black text-blue-900"><?= number_format($subscriptionsActive ?? 0) ?></span>
                                <span class="text-xs text-blue-600 font-medium bg-blue-50 px-2 py-0.5 rounded-full border border-blue-100">Active</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-8">
        <!-- Workflows -->
        <div class="bg-white/80 backdrop-blur-xl border border-white/30 rounded-2xl shadow-lg overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-100 bg-white/50 flex items-center justify-between">
                <div class="flex items-center space-x-3">
                    <div class="w-8 h-8 bg-gradient-to-br from-indigo-500 to-purple-600 rounded-lg flex items-center justify-center text-white shadow-md">
                        <i class="fas fa-clipboard-list text-sm"></i>
                    </div>
                    <div>
                        <h3 class="text-sm font-bold text-gray-900 uppercase tracking-wide">Operator Workflows</h3>
                        <p class="text-xs text-gray-500">Daily tasks</p>
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
                    <div class="w-8 h-8 rounded-full bg-gray-50 text-gray-400 flex items-center justify-center group-hover:bg-indigo-100 group-hover:text-indigo-600 transition-colors">
                        <i class="fas fa-chevron-right text-xs"></i>
                    </div>
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
                    <div class="w-8 h-8 rounded-full bg-gray-50 text-gray-400 flex items-center justify-center group-hover:bg-emerald-100 group-hover:text-emerald-600 transition-colors">
                        <i class="fas fa-chevron-right text-xs"></i>
                    </div>
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
                    <div class="w-8 h-8 rounded-full bg-gray-50 text-gray-400 flex items-center justify-center group-hover:bg-amber-100 group-hover:text-amber-600 transition-colors">
                        <i class="fas fa-chevron-right text-xs"></i>
                    </div>
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
                    <div class="w-8 h-8 rounded-full bg-gray-50 text-gray-400 flex items-center justify-center group-hover:bg-blue-100 group-hover:text-blue-600 transition-colors">
                        <i class="fas fa-chevron-right text-xs"></i>
                    </div>
                </a>
            </div>
        </div>

        <!-- Identity -->
        <div class="bg-white/80 backdrop-blur-xl border border-white/30 rounded-2xl shadow-lg overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-100 bg-white/50 flex items-center justify-between">
                <div class="flex items-center space-x-3">
                    <div class="w-8 h-8 bg-gradient-to-br from-emerald-500 to-teal-600 rounded-lg flex items-center justify-center text-white shadow-md">
                        <i class="fas fa-shield-alt text-sm"></i>
                    </div>
                    <div>
                        <h3 class="text-sm font-bold text-gray-900 uppercase tracking-wide">Access & Identity</h3>
                        <p class="text-xs text-gray-500">RBAC Controls</p>
                    </div>
                </div>
            </div>
            <div class="p-4 space-y-2">
                <a href="<?= base_url('rbac/status') ?>" class="group flex items-center justify-between w-full px-4 py-3 rounded-xl hover:bg-gray-50 transition-colors">
                    <div class="flex items-center gap-3">
                        <i class="fas fa-heartbeat text-slate-400 group-hover:text-emerald-500 transition-colors"></i>
                        <span class="text-sm font-medium text-gray-700 group-hover:text-gray-900">RBAC Status</span>
                    </div>
                    <span class="text-[10px] font-bold uppercase tracking-wider bg-emerald-100 text-emerald-700 px-2 py-1 rounded-full border border-emerald-200">Healthy</span>
                </a>
                <a href="<?= base_url('rbac/sync') ?>" class="group flex items-center justify-between w-full px-4 py-3 rounded-xl hover:bg-gray-50 transition-colors">
                    <div class="flex items-center gap-3">
                        <i class="fas fa-sync-alt text-slate-400 group-hover:text-blue-500 group-hover:animate-spin transition-colors"></i>
                        <span class="text-sm font-medium text-gray-700 group-hover:text-gray-900">Sync Permissions</span>
                    </div>
                    <i class="fas fa-arrow-right text-xs text-gray-300 group-hover:text-gray-500"></i>
                </a>
                <a href="<?= base_url('rbac/init') ?>" class="group flex items-center justify-between w-full px-4 py-3 rounded-xl hover:bg-gray-50 transition-colors">
                    <div class="flex items-center gap-3">
                        <i class="fas fa-seedling text-slate-400 group-hover:text-green-500 transition-colors"></i>
                        <span class="text-sm font-medium text-gray-700 group-hover:text-gray-900">Initialize RBAC</span>
                    </div>
                    <i class="fas fa-arrow-right text-xs text-gray-300 group-hover:text-gray-500"></i>
                </a>
                <a href="<?= base_url('rbac/setup') ?>" class="group flex items-center justify-between w-full px-4 py-3 rounded-xl hover:bg-gray-50 transition-colors">
                    <div class="flex items-center gap-3">
                        <i class="fas fa-tools text-slate-400 group-hover:text-orange-500 transition-colors"></i>
                        <span class="text-sm font-medium text-gray-700 group-hover:text-gray-900">RBAC Setup</span>
                    </div>
                    <i class="fas fa-arrow-right text-xs text-gray-300 group-hover:text-gray-500"></i>
                </a>
            </div>
        </div>
    </div>

    <div class="space-y-8 mb-12">
        <!-- Plan Enforcement Audits -->
        <div class="bg-white/80 backdrop-blur-xl border border-white/30 rounded-2xl shadow-lg overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-100 bg-white/50 flex items-center justify-between">
                <div class="flex items-center space-x-3">
                    <div class="w-8 h-8 bg-rose-100 rounded-lg flex items-center justify-center text-rose-600">
                        <i class="fas fa-gavel text-sm"></i>
                    </div>
                    <h3 class="text-sm font-bold text-gray-900 uppercase tracking-wide">Plan Enforcement</h3>
                </div>
                <div class="flex items-center gap-2">
                    <span class="text-xs text-gray-500 font-medium">Subscription guardrails</span>
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-rose-50 text-rose-700 border border-rose-100">
                        <?= count($planAudits ?? []) ?> violations
                    </span>
                </div>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-100">
                    <thead class="bg-gray-50/50 backdrop-blur-sm">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Timestamp</th>
                            <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Clinic</th>
                            <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Action Blocked</th>
                            <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Reason</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 bg-transparent">
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
                                <tr class="hover:bg-rose-50/30 transition-colors">
                                    <td class="px-6 py-4 text-xs text-gray-500 font-mono whitespace-nowrap">
                                        <?= esc($audit['created_at']) ?>
                                    </td>
                                    <td class="px-6 py-4">
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium bg-blue-50 text-blue-700 border border-blue-100">
                                            <i class="fas fa-hospital text-[10px]"></i> Clinic #<?= esc($audit['clinic_id']) ?>
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-sm font-bold text-gray-900">
                                        <?= esc($audit['action_key']) ?>
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

    <!-- Danger Zone -->
    <div class="relative overflow-hidden rounded-2xl border border-rose-200 bg-white/80 backdrop-blur-xl shadow-lg mb-12">
        <div class="absolute top-0 left-0 w-full h-1 bg-gradient-to-r from-rose-400 to-red-500"></div>
        <div class="px-6 py-4 border-b border-rose-100 bg-rose-50/50 flex items-center justify-between">
            <div class="flex items-center space-x-3">
                <div class="w-8 h-8 bg-rose-100 rounded-lg flex items-center justify-center text-rose-600 shadow-sm border border-rose-200">
                    <i class="fas fa-radiation text-sm"></i>
                </div>
                <h3 class="text-sm font-bold text-rose-900 uppercase tracking-wide">Danger Zone</h3>
            </div>
        </div>
        <div class="p-6 md:p-8">
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-6 mb-8">
                <div>
                    <h2 class="text-xl font-bold text-gray-900">Terminate Global Session</h2>
                    <p class="mt-1 text-sm text-gray-500">End your high-privilege session immediately. You will be returned to your default tenant context.</p>
                </div>
                <div class="flex items-start gap-3 p-4 bg-rose-50 rounded-xl border border-rose-100 text-sm text-rose-800 max-w-sm">
                    <i class="fas fa-exclamation-triangle mt-0.5 text-rose-500"></i>
                    <div>
                        <span class="font-bold block mb-1">Warning</span>
                        Any unsaved changes in the Control Plane will be lost upon exit.
                    </div>
                </div>
            </div>

            <form action="<?= base_url('controlplane/exit') ?>" method="post" class="max-w-2xl">
                <?= csrf_field() ?>
                
                <div class="space-y-6">
                    <div class="group flex items-start p-4 rounded-xl border-2 border-transparent hover:border-rose-100 hover:bg-rose-50/30 transition-all cursor-pointer">
                        <div class="flex items-center h-5 mt-0.5">
                            <input id="confirm_terminate" name="confirm_terminate" type="checkbox" value="1" required
                                   class="h-5 w-5 text-rose-600 border-gray-300 rounded focus:ring-rose-500 cursor-pointer">
                        </div>
                        <div class="ml-3 text-sm">
                            <label for="confirm_terminate" class="font-bold text-gray-700 cursor-pointer group-hover:text-rose-800 transition-colors">I confirm I want to exit Global Mode</label>
                            <p class="text-gray-500 mt-1 text-xs">This action logs logout events for audit purposes.</p>
                        </div>
                    </div>

                    <div class="bg-gray-50 p-6 rounded-xl border border-gray-200">
                        <label for="termination_phrase" class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-3">
                            Type <span class="text-rose-600 select-all font-mono bg-rose-50 px-1 rounded">EXIT GLOBAL MODE</span> to confirm
                        </label>
                        <div class="flex gap-3">
                            <input type="text" name="termination_phrase" id="termination_phrase" required autocomplete="off"
                                   class="block w-full rounded-xl border-gray-300 shadow-sm focus:border-rose-500 focus:ring-rose-500 text-sm py-2.5 font-mono placeholder-gray-400"
                                   placeholder="EXIT GLOBAL MODE">
                            <button type="submit" class="inline-flex items-center justify-center px-6 py-2.5 border border-transparent text-sm font-bold rounded-xl shadow-lg shadow-rose-500/30 text-white bg-gradient-to-r from-rose-600 to-red-600 hover:from-rose-700 hover:to-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-rose-500 whitespace-nowrap transition-all hover:scale-105 active:scale-95">
                                <i class="fas fa-sign-out-alt mr-2"></i>
                                Terminate Session
                            </button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
<?= $this->endSection() ?>
