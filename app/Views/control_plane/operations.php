<?= $this->extend('layouts/main_control_plane') ?>

<?= $this->section('content') ?>
<div class="space-y-10">
    <div class="glass-card rounded-3xl p-6 md:p-8">
        <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-6">
            <div>
                <p class="text-xs uppercase tracking-[0.3em] text-blue-700 font-semibold">Operations</p>
                <h1 class="text-3xl font-semibold text-slate-900 mt-2">Global governance workspace</h1>
                <p class="mt-3 text-sm text-slate-600 max-w-2xl">
                    Operational controls, audit telemetry, and high‑risk actions live here to keep governance centralized.
                </p>
            </div>
            <div class="flex flex-wrap gap-3">
                <span class="px-3 py-1 rounded-full bg-emerald-100 text-emerald-700 text-xs font-semibold">Global Mode</span>
                <span class="px-3 py-1 rounded-full bg-slate-100 text-slate-600 text-xs font-semibold">Operator tools</span>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div class="glass-card rounded-3xl p-6">
            <p class="text-xs uppercase tracking-widest text-slate-500">Plans</p>
            <div class="mt-3 flex items-baseline gap-4">
                <div>
                    <span class="text-2xl font-black text-slate-900"><?= esc($plansActive ?? 0) ?></span>
                    <span class="text-xs text-slate-500 ml-1">Active</span>
                </div>
                <div>
                    <span class="text-2xl font-black text-slate-400"><?= esc($plansInactive ?? 0) ?></span>
                    <span class="text-xs text-slate-400 ml-1">Inactive</span>
                </div>
            </div>
        </div>
        <div class="glass-card rounded-3xl p-6">
            <p class="text-xs uppercase tracking-widest text-slate-500">Clinics</p>
            <div class="mt-3">
                <span class="text-2xl font-black text-slate-900"><?= esc($clinicsTotal ?? 0) ?></span>
                <span class="text-xs text-slate-500 ml-1">Registered</span>
            </div>
        </div>
        <div class="glass-card rounded-3xl p-6">
            <p class="text-xs uppercase tracking-widest text-slate-500">Subscriptions</p>
            <div class="mt-3">
                <span class="text-2xl font-black text-emerald-600"><?= esc($subscriptionsActive ?? 0) ?></span>
                <span class="text-xs text-emerald-600 ml-1">Active</span>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
        <div class="glass-card rounded-3xl p-6">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h3 class="text-sm font-semibold text-slate-800">Clinic onboarding</h3>
                    <p class="text-xs text-slate-500 mt-1">Provision tenants with audited workflows.</p>
                </div>
                <span class="w-10 h-10 rounded-2xl bg-indigo-100 text-indigo-600 flex items-center justify-center">
                    <i class="fas fa-hospital-user"></i>
                </span>
            </div>
            <a href="<?= base_url('controlplane/onboarding/clinic/create') ?>" class="flex items-center justify-between w-full px-4 py-3 rounded-xl border border-indigo-100 bg-indigo-50 text-sm font-semibold text-indigo-700 hover:bg-indigo-100 transition-colors">
                <span class="flex items-center gap-2">
                    <i class="fas fa-plus"></i> Onboard New Clinic
                </span>
                <i class="fas fa-arrow-right text-xs"></i>
            </a>
        </div>

        <div class="glass-card rounded-3xl p-6">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h3 class="text-sm font-semibold text-slate-800">Access & identity</h3>
                    <p class="text-xs text-slate-500 mt-1">RBAC visibility and lifecycle controls.</p>
                </div>
                <span class="w-10 h-10 rounded-2xl bg-emerald-100 text-emerald-600 flex items-center justify-center">
                    <i class="fas fa-shield-alt"></i>
                </span>
            </div>
            <div class="grid grid-cols-1 gap-2">
                <a href="<?= base_url('rbac/status') ?>" class="flex items-center justify-between w-full px-4 py-3 rounded-xl border border-slate-200 bg-white text-sm font-semibold text-slate-700 hover:bg-slate-50 transition-colors">
                    <span class="flex items-center gap-2"><i class="fas fa-shield-alt"></i> RBAC Status</span>
                    <i class="fas fa-arrow-right text-xs"></i>
                </a>
                <a href="<?= base_url('rbac/sync') ?>" class="flex items-center justify-between w-full px-4 py-3 rounded-xl border border-slate-200 bg-white text-sm font-semibold text-slate-700 hover:bg-slate-50 transition-colors">
                    <span class="flex items-center gap-2"><i class="fas fa-sync-alt"></i> Sync Permissions</span>
                    <i class="fas fa-arrow-right text-xs"></i>
                </a>
                <a href="<?= base_url('rbac/init') ?>" class="flex items-center justify-between w-full px-4 py-3 rounded-xl border border-slate-200 bg-white text-sm font-semibold text-slate-700 hover:bg-slate-50 transition-colors">
                    <span class="flex items-center gap-2"><i class="fas fa-seedling"></i> Initialize RBAC</span>
                    <i class="fas fa-arrow-right text-xs"></i>
                </a>
                <a href="<?= base_url('rbac/setup') ?>" class="flex items-center justify-between w-full px-4 py-3 rounded-xl border border-slate-200 bg-white text-sm font-semibold text-slate-700 hover:bg-slate-50 transition-colors">
                    <span class="flex items-center gap-2"><i class="fas fa-tools"></i> RBAC Setup</span>
                    <i class="fas fa-arrow-right text-xs"></i>
                </a>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
        <div class="glass-card rounded-3xl overflow-hidden">
            <div class="px-6 py-4 border-b border-white/40 bg-white/70 flex items-center justify-between">
                <h3 class="text-[11px] font-bold text-slate-700 uppercase tracking-widest">Recent governance events</h3>
                <span class="text-[10px] uppercase tracking-widest text-slate-500 bg-white/70 border border-slate-200 px-2 py-1 rounded-full">
                    <?= count($recentAudits ?? []) ?> records
                </span>
            </div>
            <?php if (empty($recentAudits)): ?>
                <div class="p-8 text-center text-slate-500">
                    <div class="w-12 h-12 bg-slate-100 rounded-full flex items-center justify-center mx-auto mb-3">
                        <i class="fas fa-clipboard-list text-slate-400"></i>
                    </div>
                    <p class="text-sm font-medium">No audit records found.</p>
                    <p class="text-xs text-slate-400 mt-1">Governance events will appear here.</p>
                </div>
            <?php else: ?>
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs">
                        <thead class="bg-white/70 text-slate-500 font-medium border-b border-white/40 sticky top-0 z-10">
                            <tr>
                                <th class="px-6 py-3">Timestamp</th>
                                <th class="px-6 py-3">Action</th>
                                <th class="px-6 py-3">Reason</th>
                                <th class="px-6 py-3">Clinic ID</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-white/40">
                            <?php foreach ($recentAudits as $audit): ?>
                                <tr class="hover:bg-white/60">
                                    <td class="px-6 py-3 text-slate-500 font-mono"><?= esc($audit['created_at']) ?></td>
                                    <td class="px-6 py-3 font-medium text-slate-700"><?= esc($audit['action_key']) ?></td>
                                    <td class="px-6 py-3 text-slate-500"><?= esc($audit['reason_code']) ?></td>
                                    <td class="px-6 py-3 text-slate-400 font-mono"><?= esc($audit['clinic_id']) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>

        <div class="glass-card rounded-3xl overflow-hidden">
            <div class="px-6 py-4 border-b border-white/40 bg-white/70 flex items-center justify-between">
                <h3 class="text-[11px] font-bold text-slate-700 uppercase tracking-widest">Notifications ledger</h3>
                <span class="text-[10px] uppercase tracking-widest text-slate-500 bg-white/70 border border-slate-200 px-2 py-1 rounded-full">
                    <?= count($recentNotifications ?? []) ?> records
                </span>
            </div>
            <?php if (empty($recentNotifications)): ?>
                <div class="p-8 text-center text-slate-500">
                    <div class="w-12 h-12 bg-slate-100 rounded-full flex items-center justify-center mx-auto mb-3">
                        <i class="fas fa-bell text-slate-400"></i>
                    </div>
                    <p class="text-sm font-medium">No notifications found.</p>
                    <p class="text-xs text-slate-400 mt-1">Delivery events will appear here.</p>
                </div>
            <?php else: ?>
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs">
                        <thead class="bg-white/70 text-slate-500 font-medium border-b border-white/40 sticky top-0 z-10">
                            <tr>
                                <th class="px-6 py-3">Created</th>
                                <th class="px-6 py-3">Channel</th>
                                <th class="px-6 py-3">Status</th>
                                <th class="px-6 py-3">Clinic</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-white/40">
                            <?php foreach ($recentNotifications as $notif): ?>
                                <tr class="hover:bg-white/60">
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
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <div class="glass-card rounded-3xl p-6 md:p-8 border border-rose-100">
        <div class="flex items-center justify-between mb-6">
            <div>
                <p class="text-xs uppercase tracking-[0.3em] text-rose-600 font-semibold">Danger Zone</p>
                <h2 class="text-2xl font-semibold text-slate-900 mt-2">Terminate global session</h2>
                <p class="mt-2 text-sm text-slate-600">End global mode immediately after administrative tasks are complete.</p>
            </div>
        </div>
        <form action="<?= base_url('controlplane/danger/exit') ?>" method="post" class="space-y-6">
            <?= csrf_field() ?>
            <div class="flex items-start space-x-4">
                <div class="flex items-center h-5">
                    <input type="checkbox" name="confirm_terminate" value="1" required
                           class="w-4 h-4 text-rose-600 border-slate-300 rounded focus:ring-rose-500">
                </div>
                <div class="text-xs">
                    <label class="font-bold text-slate-700 uppercase tracking-tight">I understand this will terminate global mode</label>
                    <p class="text-slate-500 mt-1">This action cannot be undone without re-entering the Control Plane.</p>
                </div>
            </div>

            <div class="space-y-3">
                <label class="block text-[10px] font-black text-slate-500 uppercase tracking-widest">
                    Type <span class="text-rose-600">EXIT GLOBAL MODE</span> to confirm
                </label>
                <input type="text" name="termination_phrase" required autocomplete="off"
                       class="w-full px-4 py-3 bg-white/70 border border-slate-200 font-mono text-sm uppercase tracking-widest focus:border-rose-500 focus:bg-white focus:outline-none transition-all rounded-lg"
                       placeholder="Type the phrase here">
            </div>

            <div class="pt-4">
                <button type="submit" class="text-[11px] font-black text-white bg-rose-600 px-8 py-4 rounded-lg uppercase tracking-widest hover:bg-rose-700 transition-all shadow-lg shadow-rose-500/20">
                    End Authority Session
                </button>
            </div>
        </form>
    </div>
</div>
<?= $this->endSection() ?>
