<?= $this->extend('layouts/control_plane') ?>

<?= $this->section('content') ?>
<div class="max-w-5xl space-y-16">
    <!-- 01. OBSERVABILITY -->
    <section>
        <h3 class="text-[10px] font-black text-slate-400 uppercase tracking-[0.3em] mb-6">01. System Status</h3>
        <div class="bg-white border border-slate-200 p-8">
            <p class="text-sm font-bold text-slate-700 uppercase tracking-tighter mb-4">Authority Console: Active</p>
            <p class="text-xs text-slate-500 leading-relaxed max-w-2xl">
                The platform is operating under nominal conditions. Global authority is active. 
                System metrics and security audit logs are being streamed to the primary observability cluster.
            </p>
        </div>
    </section>

    <!-- 02. GOVERNANCE -->
    <section>
        <h3 class="text-[10px] font-black text-slate-400 uppercase tracking-[0.3em] mb-6">02. Governance Posture</h3>
        <div class="bg-white border border-slate-200 p-8">
            <p class="text-xs text-slate-500 leading-relaxed max-w-2xl">
                Tenant isolation is strictly enforced. Resource quotas and feature availability are governed 
                by active Plan Engines. All state changes require high-privilege authorization.
            </p>
        </div>
    </section>

    <!-- 03. SYSTEM NOTE -->
    <section>
        <h3 class="text-[10px] font-black text-slate-400 uppercase tracking-[0.3em] mb-6">03. Operational Note</h3>
        <div class="bg-slate-900 rounded p-10 text-white">
            <h3 class="text-lg font-black uppercase tracking-tight mb-4">Read-Only View</h3>
            <p class="text-slate-400 text-xs leading-relaxed max-w-xl">
                This dashboard provides status information only. To perform administrative tasks, 
                navigate to the System Operations interface using the primary navigation menu. 
                Actions performed there are logged to the persistent system audit ledger.
            </p>
        </div>
    </section>
</div>
<?= $this->endSection() ?>
