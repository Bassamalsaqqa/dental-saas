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
            <button onclick="showWATab('journal')" id="btn-journal" class="px-6 py-2.5 rounded-xl text-sm font-bold transition-all duration-200">Journal</button>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 mb-6">
        <div class="p-4 bg-slate-50 border border-slate-200 rounded-xl flex items-center justify-between">
            <div>
                <h4 class="font-bold text-slate-700 text-sm">Secrets &amp; Policies moved to Settings</h4>
                <p class="text-xs text-slate-500 mt-0.5">API credentials and tenant rate limits are now in Global Configuration</p>
            </div>
            <a href="<?= base_url('controlplane/settings?tab=communications&subtab=whatsapp') ?>" class="px-4 py-2 bg-slate-700 text-white text-sm font-semibold rounded-xl hover:bg-slate-800 transition-all">
                <i class="fas fa-cog mr-1.5"></i>Go to Settings
            </a>
        </div>
        <div class="p-4 bg-amber-50 border border-amber-200 rounded-xl flex items-center justify-between">
            <div>
                <h4 class="font-bold text-amber-800 text-sm">Operations Console</h4>
                <p class="text-xs text-amber-700 mt-0.5">Open shared runtime tooling and platform health checks</p>
            </div>
            <a href="<?= base_url('controlplane/operations') ?>" class="px-4 py-2 bg-amber-600 text-white text-sm font-semibold rounded-xl hover:bg-amber-700 transition-all">
                <i class="fas fa-arrow-up-right-from-square mr-1.5"></i>Open Ops
            </a>
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
function showWATab(tabName) {
    document.querySelectorAll('.wa-tab').forEach(el => el.classList.add('hidden'));
    const target = document.getElementById(tabName + '-tab');
    if(target) target.classList.remove('hidden');

    document.querySelectorAll('#btn-dashboard, #btn-journal').forEach(btn => {
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
