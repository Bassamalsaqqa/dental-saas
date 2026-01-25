<?= $this->extend('layouts/main_control_plane') ?>

<?= $this->section('content') ?>
<div class="min-h-screen bg-gradient-to-br from-slate-50 via-emerald-50 to-teal-100 relative overflow-hidden">
    <!-- Animated Background Elements -->
    <div class="absolute inset-0 overflow-hidden pointer-events-none">
        <div class="absolute -top-40 -right-40 w-80 h-80 bg-gradient-to-br from-emerald-400/20 to-teal-600/20 rounded-full blur-3xl animate-pulse"></div>
        <div class="absolute -bottom-40 -left-40 w-80 h-80 bg-gradient-to-br from-emerald-400/20 to-cyan-600/20 rounded-full blur-3xl animate-pulse delay-1000"></div>
        <div class="absolute top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2 w-96 h-96 bg-gradient-to-br from-teal-400/10 to-emerald-600/10 rounded-full blur-3xl animate-pulse delay-2000"></div>
    </div>

    <div class="container mx-auto relative z-10 px-4 py-6">
        <!-- Page Header -->
        <div class="mb-8">
            <div class="bg-white/80 backdrop-blur-sm border border-white/30 rounded-xl p-6 shadow-lg">
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
                    <div class="flex flex-wrap gap-3">
                        <span class="px-3 py-1 rounded-full bg-emerald-100 text-emerald-700 text-xs font-semibold">Global Mode</span>
                        <span class="px-3 py-1 rounded-full bg-slate-100 text-slate-600 text-xs font-semibold">Operator</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Stats Grid -->
        <div class="mb-8">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 items-stretch">
                <!-- Plans -->
                <div class="group relative">
                    <div class="absolute inset-0 bg-gradient-to-br from-indigo-500/20 to-purple-600/20 rounded-3xl blur-xl group-hover:blur-2xl transition-all duration-500 opacity-0 group-hover:opacity-100"></div>
                    <div class="relative backdrop-blur-xl bg-white/80 border border-white/30 rounded-2xl p-6 shadow-2xl shadow-indigo-500/10 group-hover:shadow-indigo-500/20 transition-all duration-500 hover:scale-105 hover:-translate-y-2 h-full flex flex-col">
                        <div class="flex items-center justify-between mb-4">
                            <div class="relative p-3 rounded-xl text-white shadow-xl" style="background: linear-gradient(to bottom right, #6366f1, #8b5cf6);">
                                <i class="fas fa-layer-group text-xl"></i>
                            </div>
                        </div>
                        <div class="space-y-2 flex-1 flex flex-col justify-end">
                            <p class="text-xs font-semibold text-gray-600 uppercase tracking-wider">Plans</p>
                            <div class="flex items-baseline gap-2">
                                <span class="text-3xl font-black text-slate-900"><?= number_format($plansActive ?? 0) ?></span>
                                <span class="text-xs text-slate-500 font-medium">Active</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Clinics -->
                <div class="group relative">
                    <div class="absolute inset-0 bg-gradient-to-br from-emerald-500/20 to-teal-600/20 rounded-3xl blur-xl group-hover:blur-2xl transition-all duration-500 opacity-0 group-hover:opacity-100"></div>
                    <div class="relative backdrop-blur-xl bg-white/80 border border-white/30 rounded-2xl p-6 shadow-2xl shadow-emerald-500/10 group-hover:shadow-emerald-500/20 transition-all duration-500 hover:scale-105 hover:-translate-y-2 h-full flex flex-col">
                        <div class="flex items-center justify-between mb-4">
                            <div class="relative p-3 rounded-xl text-white shadow-xl" style="background: linear-gradient(to bottom right, #10b981, #14b8a6);">
                                <i class="fas fa-hospital text-xl"></i>
                            </div>
                        </div>
                        <div class="space-y-2 flex-1 flex flex-col justify-end">
                            <p class="text-xs font-semibold text-gray-600 uppercase tracking-wider">Clinics</p>
                            <div class="flex items-baseline gap-2">
                                <span class="text-3xl font-black text-slate-900"><?= number_format($clinicsTotal ?? 0) ?></span>
                                <span class="text-xs text-slate-500 font-medium">Registered</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Subscriptions -->
                <div class="group relative">
                    <div class="absolute inset-0 bg-gradient-to-br from-blue-500/20 to-cyan-600/20 rounded-3xl blur-xl group-hover:blur-2xl transition-all duration-500 opacity-0 group-hover:opacity-100"></div>
                    <div class="relative backdrop-blur-xl bg-white/80 border border-white/30 rounded-2xl p-6 shadow-2xl shadow-blue-500/10 group-hover:shadow-blue-500/20 transition-all duration-500 hover:scale-105 hover:-translate-y-2 h-full flex flex-col">
                        <div class="flex items-center justify-between mb-4">
                            <div class="relative p-3 rounded-xl text-white shadow-xl" style="background: linear-gradient(to bottom right, #3b82f6, #06b6d4);">
                                <i class="fas fa-receipt text-xl"></i>
                            </div>
                        </div>
                        <div class="space-y-2 flex-1 flex flex-col justify-end">
                            <p class="text-xs font-semibold text-gray-600 uppercase tracking-wider">Subscriptions</p>
                            <div class="flex items-baseline gap-2">
                                <span class="text-3xl font-black text-emerald-600" style="color: #059669 !important;"><?= number_format($subscriptionsActive ?? 0) ?></span>
                                <span class="text-xs text-emerald-600 font-medium" style="color: #059669 !important;">Active</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-8">
        <!-- Workflows -->
        <div class="bg-white rounded-xl border border-gray-200 shadow-lg overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200 bg-gray-50 flex items-center justify-between">
                <div class="flex items-center space-x-3">
                    <div class="w-2 h-2 bg-indigo-500 rounded-full"></div>
                    <div>
                        <h3 class="text-sm font-bold text-gray-900 uppercase tracking-wide">Operator Workflows</h3>
                        <p class="text-xs text-gray-500 mt-0.5">Daily tasks to keep tenants healthy</p>
                    </div>
                </div>
                <div class="w-8 h-8 bg-indigo-100 rounded-lg flex items-center justify-center text-indigo-600">
                    <i class="fas fa-clipboard-list"></i>
                </div>
            </div>
            <div class="p-4 space-y-3">
                <a href="<?= base_url('controlplane/onboarding/clinic/create') ?>" class="flex items-center justify-between w-full px-4 py-3 rounded-xl border border-gray-100 hover:border-indigo-200 bg-white hover:bg-indigo-50/50 transition-all group">
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 rounded-lg bg-indigo-100 text-indigo-600 flex items-center justify-center group-hover:bg-indigo-600 group-hover:text-white transition-colors">
                            <i class="fas fa-plus text-xs"></i>
                        </div>
                        <div>
                            <span class="block text-sm font-semibold text-gray-900">Onboard New Clinic</span>
                            <span class="text-xs text-gray-500">Create tenant & admin</span>
                        </div>
                    </div>
                    <i class="fas fa-chevron-right text-xs text-gray-300 group-hover:text-indigo-400"></i>
                </a>
                <a href="<?= base_url('controlplane/clinics') ?>" class="flex items-center justify-between w-full px-4 py-3 rounded-xl border border-gray-100 hover:border-emerald-200 bg-white hover:bg-emerald-50/50 transition-all group">
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 rounded-lg bg-emerald-100 text-emerald-600 flex items-center justify-center group-hover:bg-emerald-600 group-hover:text-white transition-colors">
                            <i class="fas fa-hospital-user text-xs"></i>
                        </div>
                        <div>
                            <span class="block text-sm font-semibold text-gray-900">Review Clinics</span>
                            <span class="text-xs text-gray-500">Status checks & audits</span>
                        </div>
                    </div>
                    <i class="fas fa-chevron-right text-xs text-gray-300 group-hover:text-emerald-400"></i>
                </a>
                <a href="<?= base_url('controlplane/plan-requests') ?>" class="flex items-center justify-between w-full px-4 py-3 rounded-xl border border-gray-100 hover:border-amber-200 bg-white hover:bg-amber-50/50 transition-all group">
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 rounded-lg bg-amber-100 text-amber-600 flex items-center justify-center group-hover:bg-amber-600 group-hover:text-white transition-colors">
                            <i class="fas fa-inbox text-xs"></i>
                        </div>
                        <div>
                            <span class="block text-sm font-semibold text-gray-900">Process Requests</span>
                            <span class="text-xs text-gray-500">Subscription changes</span>
                        </div>
                    </div>
                    <i class="fas fa-chevron-right text-xs text-gray-300 group-hover:text-amber-400"></i>
                </a>
            </div>
        </div>

        <!-- Identity -->
        <div class="bg-white rounded-xl border border-gray-200 shadow-lg overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200 bg-gray-50 flex items-center justify-between">
                <div class="flex items-center space-x-3">
                    <div class="w-2 h-2 bg-emerald-500 rounded-full"></div>
                    <div>
                        <h3 class="text-sm font-bold text-gray-900 uppercase tracking-wide">Access & Identity</h3>
                        <p class="text-xs text-gray-500 mt-0.5">RBAC visibility and controls</p>
                    </div>
                </div>
                <div class="w-8 h-8 bg-emerald-100 rounded-lg flex items-center justify-center text-emerald-600">
                    <i class="fas fa-shield-alt"></i>
                </div>
            </div>
            <div class="p-4 space-y-2">
                <a href="<?= base_url('rbac/status') ?>" class="flex items-center justify-between w-full px-4 py-3 rounded-xl hover:bg-gray-50 transition-colors group">
                    <span class="flex items-center gap-3 text-sm font-medium text-gray-700 group-hover:text-gray-900">
                        <i class="fas fa-heartbeat text-slate-400 w-5 text-center"></i> RBAC Status
                    </span>
                    <span class="text-xs font-semibold bg-emerald-100 text-emerald-700 px-2 py-0.5 rounded">Healthy</span>
                </a>
                <a href="<?= base_url('rbac/sync') ?>" class="flex items-center justify-between w-full px-4 py-3 rounded-xl hover:bg-gray-50 transition-colors group">
                    <span class="flex items-center gap-3 text-sm font-medium text-gray-700 group-hover:text-gray-900">
                        <i class="fas fa-sync-alt text-slate-400 w-5 text-center group-hover:animate-spin"></i> Sync Permissions
                    </span>
                    <i class="fas fa-arrow-right text-xs text-gray-300 group-hover:text-gray-500"></i>
                </a>
                <a href="<?= base_url('rbac/init') ?>" class="flex items-center justify-between w-full px-4 py-3 rounded-xl hover:bg-gray-50 transition-colors group">
                    <span class="flex items-center gap-3 text-sm font-medium text-gray-700 group-hover:text-gray-900">
                        <i class="fas fa-seedling text-slate-400 w-5 text-center"></i> Initialize RBAC
                    </span>
                    <i class="fas fa-arrow-right text-xs text-gray-300 group-hover:text-gray-500"></i>
                </a>
                <a href="<?= base_url('rbac/setup') ?>" class="flex items-center justify-between w-full px-4 py-3 rounded-xl hover:bg-gray-50 transition-colors group">
                    <span class="flex items-center gap-3 text-sm font-medium text-gray-700 group-hover:text-gray-900">
                        <i class="fas fa-tools text-slate-400 w-5 text-center"></i> RBAC Setup
                    </span>
                    <i class="fas fa-arrow-right text-xs text-gray-300 group-hover:text-gray-500"></i>
                </a>
            </div>
        </div>
    </div>

    <div class="space-y-8 mb-12">
        <!-- Activity & Notifications Grid -->
        <div class="grid grid-cols-1 xl:grid-cols-2 gap-8">
            <!-- Control Plane Activity -->
            <div class="bg-white rounded-xl border border-gray-200 shadow-lg overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200 bg-gray-50 flex items-center justify-between">
                    <div class="flex items-center space-x-3">
                        <div class="w-2 h-2 bg-indigo-500 rounded-full"></div>
                        <h3 class="text-sm font-bold text-gray-900 uppercase tracking-wide">Activity Log</h3>
                    </div>
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-indigo-100 text-indigo-800">
                        <?= count($controlPlaneAudits ?? []) ?> events
                    </span>
                </div>
                <div class="overflow-x-auto max-h-96">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50 sticky top-0 z-10">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Timestamp</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Event</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Details</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <?php if (empty($controlPlaneAudits)): ?>
                                <tr>
                                    <td colspan="3" class="px-6 py-8 text-center text-gray-500 text-sm">
                                        No recent activity recorded.
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($controlPlaneAudits as $audit): ?>
                                    <tr class="hover:bg-gray-50 transition-colors">
                                        <td class="px-6 py-3 text-xs text-gray-500 font-mono whitespace-nowrap">
                                            <?= esc($audit['created_at']) ?>
                                        </td>
                                        <td class="px-6 py-3">
                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-slate-100 text-slate-800">
                                                <?= esc($audit['event_type']) ?>
                                            </span>
                                        </td>
                                        <td class="px-6 py-3 text-xs text-gray-600">
                                            <div class="flex flex-col">
                                                <span class="font-medium"><?= esc($audit['route']) ?></span>
                                                <span class="text-gray-400">User: <?= esc($audit['actor_user_id'] ?? '-') ?></span>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Notifications Ledger -->
            <div class="bg-white rounded-xl border border-gray-200 shadow-lg overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200 bg-gray-50 flex items-center justify-between">
                    <div class="flex items-center space-x-3">
                        <div class="w-2 h-2 bg-amber-500 rounded-full"></div>
                        <h3 class="text-sm font-bold text-gray-900 uppercase tracking-wide">Notifications</h3>
                    </div>
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-amber-100 text-amber-800">
                        <?= count($recentNotifications ?? []) ?> sent
                    </span>
                </div>
                <div class="overflow-x-auto max-h-96">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50 sticky top-0 z-10">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Time</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Channel</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Status</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <?php if (empty($recentNotifications)): ?>
                                <tr>
                                    <td colspan="3" class="px-6 py-8 text-center text-gray-500 text-sm">
                                        No recent notifications.
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($recentNotifications as $notif): ?>
                                    <tr class="hover:bg-gray-50 transition-colors">
                                        <td class="px-6 py-3 text-xs text-gray-500 font-mono whitespace-nowrap">
                                            <?= esc($notif['created_at']) ?>
                                        </td>
                                        <td class="px-6 py-3 text-xs font-medium text-gray-700">
                                            <?= esc(ucfirst($notif['channel_type'])) ?>
                                        </td>
                                        <td class="px-6 py-3">
                                            <?php if ($notif['status'] === 'sent'): ?>
                                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-green-100 text-green-700">
                                                    <i class="fas fa-check mr-1"></i> SENT
                                                </span>
                                            <?php elseif ($notif['status'] === 'failed'): ?>
                                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-red-100 text-red-700">
                                                    <i class="fas fa-exclamation-triangle mr-1"></i> FAIL
                                                </span>
                                            <?php else: ?>
                                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-gray-100 text-gray-700">
                                                    <?= esc(strtoupper($notif['status'])) ?>
                                                </span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Plan Enforcement Audits -->
        <div class="bg-white rounded-xl border border-gray-200 shadow-lg overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200 bg-gray-50 flex items-center justify-between">
                <div class="flex items-center space-x-3">
                    <div class="w-2 h-2 bg-rose-500 rounded-full"></div>
                    <h3 class="text-sm font-bold text-gray-900 uppercase tracking-wide">Plan Enforcement</h3>
                </div>
                <div class="flex items-center gap-2">
                    <span class="text-xs text-gray-500">Subscription guardrails</span>
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-rose-100 text-rose-800">
                        <?= count($planAudits ?? []) ?> violations
                    </span>
                </div>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Timestamp</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Clinic</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Action Blocked</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Reason</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php if (empty($planAudits)): ?>
                            <tr>
                                <td colspan="4" class="px-6 py-12 text-center text-gray-500">
                                    <div class="flex flex-col items-center justify-center">
                                        <div class="w-12 h-12 bg-gray-100 rounded-full flex items-center justify-center mb-3">
                                            <i class="fas fa-shield-check text-emerald-500 text-xl"></i>
                                        </div>
                                        <p class="font-medium text-gray-900">All systems compliant</p>
                                        <p class="text-xs text-gray-500 mt-1">No plan enforcement actions triggered recently.</p>
                                    </div>
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($planAudits as $audit): ?>
                                <tr class="hover:bg-gray-50 transition-colors">
                                    <td class="px-6 py-4 text-xs text-gray-500 font-mono whitespace-nowrap">
                                        <?= esc($audit['created_at']) ?>
                                    </td>
                                    <td class="px-6 py-4">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-50 text-blue-700">
                                            Clinic #<?= esc($audit['clinic_id']) ?>
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-sm font-medium text-gray-900">
                                        <?= esc($audit['action_key']) ?>
                                    </td>
                                    <td class="px-6 py-4 text-sm text-rose-600">
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
    <div class="bg-white rounded-xl border border-rose-200 shadow-lg overflow-hidden mb-12">
        <div class="px-6 py-4 border-b border-rose-100 bg-rose-50 flex items-center justify-between">
            <div class="flex items-center space-x-3">
                <div class="w-2 h-2 bg-rose-600 rounded-full animate-pulse"></div>
                <h3 class="text-sm font-bold text-rose-900 uppercase tracking-wide">Danger Zone</h3>
            </div>
            <i class="fas fa-radiation text-rose-400"></i>
        </div>
        <div class="p-6 md:p-8">
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-6 mb-8">
                <div>
                    <h2 class="text-xl font-bold text-gray-900">Terminate Global Session</h2>
                    <p class="mt-1 text-sm text-gray-500">End your high-privilege session immediately. You will be returned to your default tenant context.</p>
                </div>
                <div class="p-3 bg-rose-50 rounded-lg border border-rose-100 text-xs text-rose-700 max-w-sm">
                    <i class="fas fa-exclamation-triangle mr-1"></i>
                    <strong>Warning:</strong> Any unsaved changes in the Control Plane will be lost.
                </div>
            </div>

            <form action="<?= base_url('controlplane/exit') ?>" method="post" class="max-w-2xl">
                <?= csrf_field() ?>
                
                <div class="space-y-6">
                    <div class="flex items-start">
                        <div class="flex items-center h-5">
                            <input id="confirm_terminate" name="confirm_terminate" type="checkbox" value="1" required
                                   class="h-4 w-4 text-rose-600 border-gray-300 rounded focus:ring-rose-500">
                        </div>
                        <div class="ml-3 text-sm">
                            <label for="confirm_terminate" class="font-medium text-gray-700">I confirm I want to exit Global Mode</label>
                            <p class="text-gray-500">This action logs logout events for audit purposes.</p>
                        </div>
                    </div>

                    <div>
                        <label for="termination_phrase" class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">
                            Type <span class="text-rose-600 select-all">EXIT GLOBAL MODE</span> to confirm
                        </label>
                        <div class="flex gap-3">
                            <input type="text" name="termination_phrase" id="termination_phrase" required autocomplete="off"
                                   class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-rose-500 focus:ring-rose-500 sm:text-sm"
                                   placeholder="EXIT GLOBAL MODE">
                            <button type="submit" class="inline-flex items-center justify-center px-6 py-2 border border-transparent text-sm font-bold rounded-lg shadow-sm text-white bg-rose-600 hover:bg-rose-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-rose-500 whitespace-nowrap transition-colors">
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
