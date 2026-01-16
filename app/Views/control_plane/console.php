<?= $this->extend('layouts/control_plane') ?>

<?= $this->section('content') ?>
<div class="max-w-7xl mx-auto space-y-8">
    <!-- Header -->
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-slate-900">Operator Console</h1>
            <p class="text-sm text-slate-500">ReadOnly Governance Telemetry</p>
        </div>
        <div class="flex items-center space-x-2 bg-green-50 text-green-700 px-3 py-1 rounded-full text-xs font-bold uppercase tracking-wide border border-green-200">
            <div class="w-2 h-2 bg-green-500 rounded-full animate-pulse"></div>
            <span>Global Mode: ON</span>
        </div>
    </div>

    <!-- Navigation Quick Links (Non-Destructive) -->
    <div class="flex flex-wrap gap-3">
        <a href="<?= base_url('controlplane/operations') ?>" class="inline-flex items-center px-3 py-2 border border-slate-200 shadow-sm text-xs font-medium rounded text-slate-700 bg-white hover:bg-slate-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
            Operations
        </a>
        <a href="<?= base_url('controlplane/plans') ?>" class="inline-flex items-center px-3 py-2 border border-slate-200 shadow-sm text-xs font-medium rounded text-slate-700 bg-white hover:bg-slate-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
            Plans
        </a>
        <a href="<?= base_url('controlplane/onboarding/clinic/create') ?>" class="inline-flex items-center px-3 py-2 border border-slate-200 shadow-sm text-xs font-medium rounded text-slate-700 bg-white hover:bg-slate-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
            Onboard Clinic
        </a>
        <a href="<?= base_url('controlplane/settings') ?>" class="inline-flex items-center px-3 py-2 border border-slate-200 shadow-sm text-xs font-medium rounded text-slate-700 bg-white hover:bg-slate-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
            Settings
        </a>
    </div>

    <!-- 1. Governance KPIs -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <!-- Plans -->
        <div class="bg-white p-6 rounded-lg border border-slate-200 shadow-sm">
            <h3 class="text-xs font-bold text-slate-400 uppercase tracking-widest mb-4">Plans</h3>
            <div class="flex items-baseline space-x-6">
                <div>
                    <span class="text-2xl font-black text-slate-900"><?= esc($plansActive) ?></span>
                    <span class="text-xs text-slate-500 ml-1">Active</span>
                </div>
                <div>
                    <span class="text-2xl font-black text-slate-400"><?= esc($plansInactive) ?></span>
                    <span class="text-xs text-slate-400 ml-1">Inactive</span>
                </div>
            </div>
        </div>
        
        <!-- Clinics -->
        <div class="bg-white p-6 rounded-lg border border-slate-200 shadow-sm">
            <h3 class="text-xs font-bold text-slate-400 uppercase tracking-widest mb-4">Clinics</h3>
            <div>
                <span class="text-2xl font-black text-slate-900"><?= esc($clinicsTotal) ?></span>
                <span class="text-xs text-slate-500 ml-1">Total Registered</span>
            </div>
        </div>

        <!-- Subscriptions -->
        <div class="bg-white p-6 rounded-lg border border-slate-200 shadow-sm">
            <h3 class="text-xs font-bold text-slate-400 uppercase tracking-widest mb-4">Subscriptions</h3>
            <div>
                <span class="text-2xl font-black text-emerald-600"><?= esc($subscriptionsActive) ?></span>
                <span class="text-xs text-emerald-600 ml-1">Active</span>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
        <!-- 2. Recent Governance Events -->
        <div class="bg-white border border-slate-200 rounded-lg shadow-sm overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-100 bg-slate-50/50">
                <h3 class="text-xs font-bold text-slate-700 uppercase tracking-widest">Recent Governance Events</h3>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead class="bg-slate-50 text-slate-500 font-medium border-b border-slate-100">
                        <tr>
                            <th class="px-6 py-3">Timestamp</th>
                            <th class="px-6 py-3">Action</th>
                            <th class="px-6 py-3">Reason</th>
                            <th class="px-6 py-3">Clinic ID</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        <?php if (empty($recentAudits)): ?>
                            <tr><td colspan="4" class="px-6 py-4 text-slate-400 italic">No audit records found.</td></tr>
                        <?php else: ?>
                            <?php foreach ($recentAudits as $audit): ?>
                                <tr class="hover:bg-slate-50/50">
                                    <td class="px-6 py-3 text-slate-500 font-mono"><?= esc($audit['created_at']) ?></td>
                                    <td class="px-6 py-3 font-medium text-slate-700"><?= esc($audit['action_key']) ?></td>
                                    <td class="px-6 py-3 text-slate-500"><?= esc($audit['reason_code']) ?></td>
                                    <td class="px-6 py-3 text-slate-400 font-mono"><?= esc($audit['clinic_id']) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- 3. Notifications Observability -->
        <div class="bg-white border border-slate-200 rounded-lg shadow-sm overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-100 bg-slate-50/50">
                <h3 class="text-xs font-bold text-slate-700 uppercase tracking-widest">Notifications Ledger</h3>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead class="bg-slate-50 text-slate-500 font-medium border-b border-slate-100">
                        <tr>
                            <th class="px-6 py-3">Created</th>
                            <th class="px-6 py-3">Channel</th>
                            <th class="px-6 py-3">Status</th>
                            <th class="px-6 py-3">Clinic</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        <?php if (empty($recentNotifications)): ?>
                            <tr><td colspan="4" class="px-6 py-4 text-slate-400 italic">No notifications found.</td></tr>
                        <?php else: ?>
                            <?php foreach ($recentNotifications as $notif): ?>
                                <tr class="hover:bg-slate-50/50">
                                    <td class="px-6 py-3 text-slate-500 font-mono"><?= esc($notif['created_at']) ?></td>
                                    <td class="px-6 py-3 font-medium text-slate-700"><?= esc($notif['channel_type']) ?></td>
                                    <td class="px-6 py-3">
                                        <?php if ($notif['status'] === 'sent'): ?>
                                            <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-bold bg-green-100 text-green-700">SENT</span>
                                        <?php elseif ($notif['status'] === 'failed'): ?>
                                            <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-bold bg-red-100 text-red-700">FAIL</span>
                                        <?php else: ?>
                                            <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-bold bg-slate-100 text-slate-600"><?= esc(strtoupper($notif['status'])) ?></span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="px-6 py-3 text-slate-400 font-mono"><?= esc($notif['clinic_id']) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>
