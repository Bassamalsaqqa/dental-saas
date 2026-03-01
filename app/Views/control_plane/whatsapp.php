<?= $this->extend('layouts/main_control_plane') ?>

<?= $this->section('content') ?>
<?php
$stats = $stats ?? [];
$failureRate = (float) ($stats['failure_rate_24h'] ?? 0);
$activeTab = $active_tab ?? 'dashboard';
?>

<style>
    .wa-tab.hidden { display: none !important; }
    .journal-pane.hidden { display: none !important; }
    .glass-pulse { animation: glass-pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite; }
    @keyframes glass-pulse { 0%, 100% { opacity: 1; } 50% { opacity: .7; } }
    
    .cp-input-inline {
        width: 80px;
        padding: 0.4rem 0.6rem;
        border: 2px solid #e5e7eb;
        border-radius: 0.5rem;
        transition: all 0.2s;
        font-size: 0.75rem;
        font-weight: 700;
        text-align: center;
    }
    .cp-input-inline:focus {
        outline: none;
        border-color: #3b82f6;
        box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
    }
    
    .nav-btn-active {
        background: linear-gradient(to right, #3b82f6, #2563eb);
        color: white !important;
        box-shadow: 0 10px 15px -3px rgba(59, 130, 246, 0.2);
    }

    .copy-toast { animation: slide-up 0.3s ease-out; }
    @keyframes slide-up { from { transform: translateY(100%); opacity: 0; } to { transform: translateY(0); opacity: 1; } }
</style>

<div class="space-y-8">
    <!-- Page Header -->
    <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-6">
        <div class="flex items-center space-x-5">
            <div class="w-16 h-16 bg-gradient-to-br from-emerald-500 to-teal-600 rounded-2xl flex items-center justify-center text-white shadow-xl shadow-emerald-500/20">
                <i class="fab fa-whatsapp text-3xl"></i>
            </div>
            <div>
                <h1 class="text-3xl font-black text-slate-900 tracking-tight">WhatsApp Command Center</h1>
                <div class="flex items-center mt-1 space-x-3">
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-black bg-emerald-100 text-emerald-700 uppercase tracking-wider">
                        <span class="w-1.5 h-1.5 bg-emerald-500 rounded-full mr-1.5 glass-pulse"></span>
                        SYSTEM LIVE
                    </span>
                    <p class="text-sm text-slate-500 font-bold opacity-60">Global Governance</p>
                </div>
            </div>
        </div>

        <!-- Tab Navigation -->
        <div class="flex flex-wrap gap-2 p-1.5 bg-white/50 backdrop-blur-md rounded-2xl border border-white/50 shadow-sm">
            <button onclick="showWATab('dashboard')" id="btn-dashboard" class="px-6 py-2.5 rounded-xl text-sm font-bold transition-all duration-200">Dashboard</button>
            <button onclick="showWATab('secrets')" id="btn-secrets" class="px-6 py-2.5 rounded-xl text-sm font-bold transition-all duration-200">Secrets</button>
            <button onclick="showWATab('policies')" id="btn-policies" class="px-6 py-2.5 rounded-xl text-sm font-bold transition-all duration-200">Policies</button>
            <button onclick="showWATab('journal')" id="btn-journal" class="px-6 py-2.5 rounded-xl text-sm font-bold transition-all duration-200">Journal</button>
        </div>
    </div>

    <!-- Tab: Dashboard -->
    <div id="dashboard-tab" class="wa-tab space-y-8">
        <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-6">
            <div class="glass-card rounded-2xl p-6 border-l-4 border-emerald-500">
                <div class="text-[10px] uppercase font-black text-slate-400 mb-1">Delivered (24h)</div>
                <div class="text-3xl font-black text-slate-900"><?= number_format($stats['attempts_24h_sentish'] ?? 0) ?></div>
            </div>
            <div class="glass-card rounded-2xl p-6 border-l-4 border-rose-500">
                <div class="text-[10px] uppercase font-black text-slate-400 mb-1">Failures (24h)</div>
                <div class="text-3xl font-black text-slate-900"><?= number_format($stats['attempts_24h_failed'] ?? 0) ?></div>
            </div>
            <div class="glass-card rounded-2xl p-6 border-l-4 border-blue-500">
                <div class="text-[10px] uppercase font-black text-slate-400 mb-1">Webhook Ingest</div>
                <div class="text-3xl font-black text-slate-900"><?= number_format($stats['events_24h'] ?? 0) ?></div>
            </div>
            <div class="glass-card rounded-2xl p-6 border-l-4 border-amber-500">
                <div class="text-[10px] uppercase font-black text-slate-400 mb-1">Opt-Outs</div>
                <div class="text-3xl font-black text-slate-900"><?= number_format($stats['optouts_24h'] ?? 0) ?></div>
            </div>
        </div>

        <div class="grid grid-cols-1 xl:grid-cols-3 gap-8">
            <div class="xl:col-span-2 glass-card rounded-2xl overflow-hidden">
                <div class="p-6 border-b border-slate-100 flex justify-between items-center">
                    <h3 class="font-bold text-slate-900">Recent Stream</h3>
                    <button onclick="showWATab('journal')" class="text-xs text-blue-600 font-bold hover:underline uppercase">VIEW ALL</button>
                </div>
                <table class="w-full text-left text-sm">
                    <tbody class="divide-y divide-slate-50">
                        <?php foreach (array_slice($recent_attempts ?? [], 0, 6) as $att): ?>
                        <tr class="hover:bg-slate-50/50 transition-colors">
                            <td class="p-4 font-bold text-slate-900">#<?= $att['id'] ?></td>
                            <td class="p-4"><?= esc($att['clinic_name']) ?></td>
                            <td class="p-4"><span class="px-2 py-0.5 rounded-lg text-[10px] font-black border uppercase <?= in_array($att['status'],['delivered','read']) ? 'bg-emerald-50 text-emerald-700 border-emerald-100' : 'bg-rose-50 text-rose-700 border-rose-100' ?>"><?= $att['status'] ?></span></td>
                            <td class="p-4 text-slate-400 text-xs font-bold"><?= date('H:i:s', strtotime($att['created_at'])) ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <div class="xl:col-span-1 space-y-6">
                <div class="glass-card rounded-2xl p-6 bg-slate-800 text-white border-none shadow-xl">
                    <h3 class="font-black mb-4 flex items-center uppercase text-xs tracking-widest text-emerald-400">
                        <i class="fas fa-shield-alt mr-2"></i> System Health
                    </h3>
                    <div class="space-y-4">
                        <div class="flex items-center justify-between p-3 bg-white/5 rounded-xl">
                            <span class="text-xs font-bold text-slate-400 uppercase">Failure Rate</span>
                            <span class="text-xs font-black"><?= $failureRate ?>%</span>
                        </div>
                        <div class="flex items-center justify-between p-3 bg-white/5 rounded-xl">
                            <span class="text-xs font-bold text-slate-400 uppercase">Vault Status</span>
                            <span class="text-[10px] font-black text-emerald-400">READY</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Tab: Secrets -->
    <div id="secrets-tab" class="wa-tab hidden space-y-6">
        <div class="grid grid-cols-1 xl:grid-cols-2 gap-6">
            <?php foreach ($secrets as $s): ?>
            <div class="glass-card rounded-2xl p-6">
                <div class="flex items-center justify-between mb-6">
                    <code class="text-xs font-black text-blue-600"><?= $s['secret_key'] ?></code>
                    <span class="text-[10px] font-black <?= !empty($s['missing']) ? 'text-rose-600' : 'text-emerald-600' ?>"><?= !empty($s['missing']) ? 'MISSING' : 'READY' ?></span>
                </div>
                <form action="<?= base_url('controlplane/whatsapp/rotate-secret') ?>" method="post" class="space-y-4">
                    <?= csrf_field() ?>
                    <input type="hidden" name="secret_key" value="<?= $s['secret_key'] ?>">
                    <input type="password" name="new_value" required class="w-full px-4 py-3 border-2 border-slate-200 rounded-xl focus:border-blue-500 text-sm" placeholder="New Secret Value">
                    <input type="text" name="rotation_note" class="w-full px-4 py-3 border-2 border-slate-200 rounded-xl focus:border-blue-500 text-sm" placeholder="Audit Note">
                    <button type="submit" class="w-full py-3 bg-blue-600 text-white rounded-xl text-xs font-black uppercase hover:bg-blue-700 transition-all">ROTATE KEY</button>
                </form>
            </div>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- Tab: Policies (Restored Table Style) -->
    <div id="policies-tab" class="wa-tab hidden">
        <div class="glass-card rounded-2xl overflow-hidden">
            <div class="p-6 border-b border-slate-100 flex justify-between items-center bg-white/50">
                <h3 class="font-black text-slate-900 uppercase text-xs tracking-widest">Tenant Policy Registry</h3>
                <span class="px-3 py-1 bg-blue-50 text-blue-600 rounded-full text-[10px] font-black"><?= count($policies) ?> TENANTS</span>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left">
                    <thead class="bg-slate-50/50">
                        <tr class="text-[10px] font-black text-slate-400 uppercase tracking-widest border-b">
                            <th class="p-6">Clinic Info</th>
                            <th class="p-6">Status</th>
                            <th class="p-6 text-center">Daily</th>
                            <th class="p-6 text-center">Monthly</th>
                            <th class="p-6 text-center">Hourly</th>
                            <th class="p-6 text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-50">
                        <?php foreach ($policies as $pol): ?>
                        <tr class="hover:bg-blue-50/30 transition-colors">
                            <td class="p-6">
                                <div class="font-bold text-slate-900"><?= esc($pol['clinic_name']) ?></div>
                                <div class="text-[10px] font-bold text-slate-400 uppercase">ID: <?= $pol['clinic_id'] ?></div>
                            </td>
                            <td class="p-6">
                                <?php if(!empty($pol['suspended'])): ?>
                                    <span class="px-2 py-1 bg-rose-100 text-rose-700 rounded text-[10px] font-black">SUSPENDED</span>
                                <?php else: ?>
                                    <span class="px-2 py-1 bg-emerald-100 text-emerald-700 rounded text-[10px] font-black">ACTIVE</span>
                                <?php endif; ?>
                            </td>
                            <td class="p-6 text-center"><input type="number" id="daily-<?= $pol['clinic_id'] ?>" value="<?= $pol['daily_limit'] ?>" class="cp-input-inline"></td>
                            <td class="p-6 text-center"><input type="number" id="monthly-<?= $pol['clinic_id'] ?>" value="<?= $pol['monthly_limit'] ?>" class="cp-input-inline"></td>
                            <td class="p-6 text-center"><input type="number" id="hourly-<?= $pol['clinic_id'] ?>" value="<?= $pol['per_hour_limit'] ?>" class="cp-input-inline"></td>
                            <td class="p-6 text-center">
                                <div class="flex items-center justify-center space-x-2">
                                    <button onclick="updatePolicyLive(<?= $pol['clinic_id'] ?>)" class="p-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-all"><i class="fas fa-save text-xs"></i></button>
                                    <?php if(empty($pol['suspended'])): ?>
                                    <button onclick="emergencyPauseLive(<?= $pol['clinic_id'] ?>, 1)" class="p-2 bg-rose-100 text-rose-600 rounded-lg hover:bg-rose-600 hover:text-white transition-all"><i class="fas fa-pause text-xs"></i></button>
                                    <?php else: ?>
                                    <button onclick="emergencyPauseLive(<?= $pol['clinic_id'] ?>, 0)" class="p-2 bg-emerald-100 text-emerald-600 rounded-lg hover:bg-emerald-600 hover:text-white transition-all"><i class="fas fa-play text-xs"></i></button>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Tab: Journal -->
    <div id="journal-tab" class="wa-tab hidden space-y-8">
        <div class="glass-card rounded-2xl overflow-hidden">
            <div class="p-2 bg-slate-50 border-b flex justify-center space-x-2">
                <button onclick="showJournalSubTab('attempts')" id="btn-j-attempts" class="px-6 py-2 text-xs font-black uppercase tracking-widest rounded-lg">Attempts</button>
                <button onclick="showJournalSubTab('webhooks')" id="btn-j-webhooks" class="px-6 py-2 text-xs font-black uppercase tracking-widest rounded-lg">Webhooks</button>
                <button onclick="showJournalSubTab('optouts')" id="btn-j-optouts" class="px-6 py-2 text-xs font-black uppercase tracking-widest rounded-lg">Opt-Outs</button>
            </div>

            <!-- Attempts -->
            <div id="journal-attempts" class="journal-pane">
                <table class="w-full text-left text-sm">
                    <thead class="bg-slate-50 text-[10px] font-black text-slate-400 uppercase tracking-widest">
                        <tr><th class="p-6">ID / Clinic</th><th class="p-6">Status</th><th class="p-6">Meta ID / Error</th><th class="p-6">Timeline</th></tr>
                    </thead>
                    <tbody class="divide-y divide-slate-50">
                        <?php foreach($recent_attempts as $a): ?>
                        <tr class="hover:bg-slate-50/50 transition-colors">
                            <td class="p-6 font-bold text-slate-900">#<?= $a['id'] ?><div class="text-[10px] text-slate-400"><?= esc($a['clinic_name']) ?></div></td>
                            <td class="p-6"><span class="px-2 py-0.5 rounded text-[10px] font-black border uppercase <?= in_array($a['status'],['sent','delivered','read']) ? 'bg-emerald-50 text-emerald-700 border-emerald-100' : 'bg-rose-50 text-rose-700 border-rose-100' ?>"><?= $a['status'] ?></span></td>
                            <td class="p-6"><div class="text-[10px] font-mono text-slate-500 truncate max-w-[200px]"><?= $a['provider_message_id'] ?: 'NONE' ?></div><div class="text-rose-600 text-[9px] font-bold"><?= $a['error_code'] ?></div></td>
                            <td class="p-6 text-slate-400 text-[10px] font-bold"><?= $a['created_at'] ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <!-- Webhooks -->
            <div id="journal-webhooks" class="journal-pane hidden p-6 space-y-4">
                <?php foreach($recent_events as $ev): ?>
                <div class="bg-slate-50 rounded-xl p-4 border border-slate-100 group">
                    <div class="flex items-center justify-between mb-2">
                        <div class="font-black text-slate-900 text-xs uppercase tracking-tighter"><?= esc($ev['event_type']) ?></div>
                        <button onclick="copyToClipboard('<?= htmlspecialchars($ev['payload_json']) ?>')" class="text-[9px] font-black text-blue-600 uppercase hover:underline">Copy JSON</button>
                    </div>
                    <pre class="p-4 bg-slate-900 rounded-lg text-emerald-400 text-[10px] overflow-x-auto"><?= esc(json_encode(json_decode($ev['payload_json']), JSON_PRETTY_PRINT)) ?></pre>
                </div>
                <?php endforeach; ?>
            </div>

            <!-- Optouts -->
            <div id="journal-optouts" class="journal-pane hidden">
                <table class="w-full text-left text-sm">
                    <thead class="bg-slate-50 text-[10px] font-black text-slate-400 uppercase tracking-widest">
                        <tr><th class="p-6">Clinic</th><th class="p-6">BSUID / Number</th><th class="p-6">Timeline</th></tr>
                    </thead>
                    <tbody class="divide-y divide-slate-50">
                        <?php foreach($recent_optouts as $o): ?>
                        <tr>
                            <td class="p-6 font-bold text-slate-900"><?= esc($o['clinic_name']) ?></td>
                            <td class="p-6"><code class="text-xs font-black text-blue-600"><?= esc($o['wa_number'] ?? $o['bsuid']) ?></code></td>
                            <td class="p-6 text-slate-400 text-[10px] font-bold"><?= $o['created_at'] ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script>
const csrfHeader = '<?= config('Security')->headerName ?>';
const csrfToken = '<?= csrf_hash() ?>';

function showWATab(tabName) {
    document.querySelectorAll('.wa-tab').forEach(el => el.classList.add('hidden'));
    const target = document.getElementById(tabName + '-tab');
    if(target) target.classList.remove('hidden');

    document.querySelectorAll('[id^="btn-"]').forEach(btn => {
        btn.classList.remove('nav-btn-active', 'text-slate-900', 'bg-white', 'shadow-sm');
        btn.classList.add('text-slate-600', 'hover:bg-white/80');
    });
    const activeBtn = document.getElementById('btn-' + tabName);
    if(activeBtn) activeBtn.classList.add('nav-btn-active');
}

function showJournalSubTab(subName) {
    document.querySelectorAll('.journal-pane').forEach(el => el.classList.add('hidden'));
    const target = document.getElementById('journal-' + subName);
    if(target) target.classList.remove('hidden');

    document.querySelectorAll('[id^="btn-j-"]').forEach(btn => {
        btn.classList.remove('bg-white', 'text-blue-600', 'shadow-md');
        btn.classList.add('text-slate-500');
    });
    const activeBtn = document.getElementById('btn-j-' + subName);
    if(activeBtn) activeBtn.classList.add('bg-white', 'text-blue-600', 'shadow-md');
}

function updatePolicyLive(clinicId) {
    const daily = document.getElementById('daily-' + clinicId).value;
    const monthly = document.getElementById('monthly-' + clinicId).value;
    const hourly = document.getElementById('hourly-' + clinicId).value;

    const formData = new FormData();
    formData.append('clinic_id', clinicId);
    formData.append('daily_limit', daily);
    formData.append('monthly_limit', monthly);
    formData.append('per_hour_limit', hourly);
    formData.append(csrfHeader, csrfToken);

    fetch('<?= base_url('controlplane/whatsapp/policy') ?>', {
        method: 'POST',
        headers: { 'X-Requested-With': 'XMLHttpRequest' },
        body: formData
    }).then(res => res.ok ? showToast('Policy Synced') : alert('Failed'));
}

function emergencyPauseLive(clinicId, suspend) {
    const reason = suspend ? prompt('Reason?') : '';
    if(suspend && reason === null) return;

    const formData = new FormData();
    formData.append('clinic_id', clinicId);
    formData.append('suspend', suspend);
    formData.append('suspended_reason', reason);
    formData.append(csrfHeader, csrfToken);

    fetch('<?= base_url('controlplane/whatsapp/emergency-pause') ?>', {
        method: 'POST',
        headers: { 'X-Requested-With': 'XMLHttpRequest' },
        body: formData
    }).then(res => res.ok ? location.reload() : alert('Failed'));
}

function copyToClipboard(text) {
    navigator.clipboard.writeText(text).then(() => showToast('Copied'));
}

function showToast(message) {
    const toast = document.createElement('div');
    toast.className = 'fixed bottom-8 left-1/2 -translate-x-1/2 px-6 py-3 bg-slate-900 text-white text-[10px] font-black rounded-2xl shadow-2xl z-[100] copy-toast border border-white/10 uppercase tracking-widest';
    toast.innerHTML = `<i class="fas fa-check-circle text-emerald-400 mr-2"></i> ${message}`;
    document.body.appendChild(toast);
    setTimeout(() => toast.remove(), 2000);
}

document.addEventListener('DOMContentLoaded', () => {
    const startTab = '<?= $active_tab ?? 'dashboard' ?>';
    showWATab(startTab);
    showJournalSubTab('attempts');
});
</script>

<?= $this->endSection() ?>
