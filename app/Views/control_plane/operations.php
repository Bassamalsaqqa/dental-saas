<?= $this->extend('layouts/control_plane') ?>

<?= $this->section('content') ?>
<div class="max-w-5xl space-y-12">
    <!-- SECTION 1: ADMINISTRATIVE OPERATIONS -->
    <section>
        <h3 class="text-[10px] font-black text-slate-400 uppercase tracking-[0.3em] mb-6">01. Administrative Operations</h3>
        
        <div class="bg-white border border-slate-200 overflow-hidden">
            <table class="w-full text-left border-collapse">
                <tbody class="divide-y divide-slate-100">
                    <tr>
                        <td class="px-8 py-6">
                            <p class="text-xs font-black text-slate-800">Tenant Onboarding</p>
                            <p class="text-[10px] text-slate-500 uppercase font-bold mt-1">Initialize new clinic context and administrator credentials.</p>
                        </td>
                        <td class="px-8 py-6 text-right">
                            <a href="<?= base_url('controlplane/onboarding/clinic/create') ?>" class="text-[10px] font-black text-slate-400 hover:text-slate-900 uppercase tracking-widest decoration-dotted underline">
                                [ Launch ]
                            </a>
                        </td>
                    </tr>
                    <tr>
                        <td class="px-8 py-6">
                            <p class="text-xs font-black text-slate-800">Plan Engine Configuration</p>
                            <p class="text-[10px] text-slate-500 uppercase font-bold mt-1">Modify global subscription tiers and feature logic.</p>
                        </td>
                        <td class="px-8 py-6 text-right">
                            <a href="<?= base_url('controlplane/plans') ?>" class="text-[10px] font-black text-slate-400 hover:text-slate-900 uppercase tracking-widest decoration-dotted underline">
                                [ Configure ]
                            </a>
                        </td>
                    </tr>
                    <tr>
                        <td class="px-8 py-6">
                            <p class="text-xs font-black text-slate-800">Global System Settings</p>
                            <p class="text-[10px] text-slate-500 uppercase font-bold mt-1">Manage platform-wide registries and resource policies.</p>
                        </td>
                        <td class="px-8 py-6 text-right">
                            <a href="<?= base_url('controlplane/settings') ?>" class="text-[10px] font-black text-slate-400 hover:text-slate-900 uppercase tracking-widest decoration-dotted underline">
                                [ Access ]
                            </a>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </section>

    <!-- SECTION 2: OPERATOR CONSOLE -->
    <section>
        <h3 class="text-[10px] font-black text-slate-400 uppercase tracking-[0.3em] mb-6">02. Operator Console</h3>
        <div class="bg-white border border-slate-200 p-8">
            <p class="text-xs text-slate-500 leading-relaxed mb-4">
                Real-time system monitoring and log aggregation interface.
            </p>
            <span class="text-[10px] font-black text-slate-300 uppercase tracking-widest">[ System Console (Implemented in P5-16) ]</span>
        </div>
    </section>

    <!-- SECTION 3: DESTRUCTIVE OPERATIONS -->
    <section>
        <h3 class="text-[10px] font-black text-slate-400 uppercase tracking-[0.3em] mb-6">03. Destructive Operations</h3>
        <div class="bg-white border border-slate-200 p-8">
            <p class="text-xs text-slate-500 leading-relaxed mb-4">
                High-impact operations including session termination and data purging.
            </p>
            <a href="<?= base_url('controlplane/danger') ?>" class="text-[10px] font-black text-slate-400 hover:text-rose-600 uppercase tracking-widest decoration-dotted underline">
                [ Open Danger Zone ]
            </a>
        </div>
    </section>
</div>
<?= $this->endSection() ?>
