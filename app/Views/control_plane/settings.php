<?= $this->extend('layouts/control_plane') ?>

<?= $this->section('content') ?>
<div class="max-w-4xl space-y-12">
    <section>
        <h3 class="text-[10px] font-black text-slate-400 uppercase tracking-[0.3em] mb-6">Global Configuration</h3>
        
        <div class="bg-white border border-slate-200 overflow-hidden">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-50 border-b border-slate-200">
                        <th class="px-6 py-3 text-[9px] font-black text-slate-500 uppercase tracking-widest">Resource</th>
                        <th class="px-6 py-3 text-[9px] font-black text-slate-500 uppercase tracking-widest">Scope</th>
                        <th class="px-6 py-3 text-[9px] font-black text-slate-500 uppercase tracking-widest text-right">Access</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    <tr>
                        <td class="px-6 py-4">
                            <p class="text-xs font-black text-slate-800">Plan Engines</p>
                            <p class="text-[9px] text-slate-500 uppercase font-bold mt-1">Tier definitions and feature logic.</p>
                        </td>
                        <td class="px-6 py-4 text-[10px] font-bold text-slate-400">GLOBAL</td>
                        <td class="px-6 py-4 text-right">
                            <a href="<?= base_url('controlplane/plans') ?>" class="text-[10px] font-black text-slate-400 hover:text-slate-900 uppercase tracking-widest">[ Manage ]</a>
                        </td>
                    </tr>
                    <tr>
                        <td class="px-6 py-4">
                            <p class="text-xs font-black text-slate-800">Notification Channels</p>
                            <p class="text-[9px] text-slate-500 uppercase font-bold mt-1">Platform-wide channel registry.</p>
                        </td>
                        <td class="px-6 py-4 text-[10px] font-bold text-slate-400">TENANT_REGISTRY</td>
                        <td class="px-6 py-4 text-right">
                            <a href="<?= base_url('settings/channels') ?>" class="text-[10px] font-black text-slate-400 hover:text-slate-900 uppercase tracking-widest">[ Manage ]</a>
                        </td>
                    </tr>
                    <tr>
                        <td class="px-6 py-4">
                            <p class="text-xs font-black text-slate-800">System Retention</p>
                            <p class="text-[9px] text-slate-500 uppercase font-bold mt-1">Artifact and audit purge policies.</p>
                        </td>
                        <td class="px-6 py-4 text-[10px] font-bold text-slate-400">SYSTEM</td>
                        <td class="px-6 py-4 text-right">
                            <a href="<?= base_url('settings') ?>" class="text-[10px] font-black text-slate-400 hover:text-slate-900 uppercase tracking-widest">[ Manage ]</a>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </section>

    <div class="p-6 bg-slate-900 text-slate-400 rounded">
        <h4 class="text-[10px] font-black text-white uppercase tracking-widest mb-2">Authority Note</h4>
        <p class="text-xs leading-relaxed">
            This interface provides direct access to system-level configuration. Modifications are persistent and affect all tenant contexts. Ensure all changes are documented in the system change log.
        </p>
    </div>
</div>
<?= $this->endSection() ?>
