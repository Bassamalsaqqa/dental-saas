<?= $this->extend('layouts/control_plane') ?>

<?= $this->section('content') ?>
<div class="max-w-2xl space-y-8">
    <div class="bg-emerald-50 border border-emerald-200 p-8 rounded">
        <h1 class="text-xl font-black text-emerald-900 uppercase tracking-tighter mb-2">Clinic Initialized</h1>
        <p class="text-sm text-emerald-700">Onboarding sequence completed for <span class="font-black"><?= esc($clinic_name) ?></span>.</p>
    </div>

        <div class="p-8">
            <div class="bg-white border border-slate-200 p-8 space-y-6">
                <div>
                    <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">Context ID</p>
                    <p class="text-2xl font-black text-slate-900">#<?= esc($clinic_id) ?></p>
                </div>

                <div class="space-y-4 pt-4 border-t border-slate-100">
                    <p class="text-xs font-bold text-slate-500 uppercase tracking-tight">Post-Onboarding Logic:</p>
                    
                    <p class="text-xs text-slate-600 leading-relaxed">
                        The new tenant environment has been initialized. To access this clinic, you must terminate the current global session via the Danger Zone and select the clinic from the context wall.
                    </p>

                    <a href="<?= base_url('controlplane/danger') ?>" class="inline-block text-[11px] font-black text-rose-600 hover:text-rose-900 uppercase tracking-widest decoration-dotted underline">
                        [ 1. Open Danger Zone ]
                    </a>
                    
                    <div class="block text-[11px] font-black text-slate-300 uppercase tracking-widest">
                        [ 2. Select Clinic Context ]
                    </div>
                </div>
            </div>
        </div>
</div>
<?= $this->endSection() ?>