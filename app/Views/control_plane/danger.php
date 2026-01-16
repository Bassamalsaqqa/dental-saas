<?= $this->extend('layouts/control_plane') ?>

<?= $this->section('content') ?>
<div class="max-w-2xl space-y-12">
    <!-- Header -->
    <section>
        <h3 class="text-[10px] font-black text-rose-600 uppercase tracking-[0.3em] mb-6">04. Destructive Actions</h3>
        <div class="bg-rose-50 border border-rose-200 p-8 rounded">
            <h1 class="text-xl font-black text-rose-900 uppercase tracking-tighter mb-4">Danger Zone: Session Termination</h1>
            <p class="text-sm text-rose-800 leading-relaxed">
                You are about to terminate the Global Authority session. This action has the following consequences:
            </p>
            <ul class="mt-4 space-y-2 text-xs text-rose-700 font-bold uppercase tracking-tight">
                <li>&gt; Restoration of strict tenant context requirements</li>
                <li>&gt; Immediate redirection to the clinic selection wall</li>
                <li>&gt; All Control Plane routes will become inaccessible</li>
            </ul>
        </div>
    </section>

    <!-- High-Friction Form -->
    <section>
        <div class="bg-white border border-slate-200 p-8">
            <form action="<?= base_url('controlplane/danger/exit') ?>" method="post" class="space-y-8">
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
                    <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest">
                        Type <span class="text-rose-600">EXIT GLOBAL MODE</span> to confirm
                    </label>
                    <input type="text" name="termination_phrase" required autocomplete="off"
                           class="w-full px-4 py-3 bg-slate-50 border border-slate-200 font-mono text-sm uppercase tracking-widest focus:border-rose-500 focus:bg-white focus:outline-none transition-all"
                           placeholder="Type the phrase here">
                </div>

                <div class="pt-4">
                    <button type="submit" class="text-[11px] font-black text-white bg-rose-600 px-8 py-4 rounded uppercase tracking-widest hover:bg-rose-700 transition-all shadow-lg shadow-rose-500/20">
                        [ End Authority Session ]
                    </button>
                </div>
            </form>
        </div>
    </section>

    <!-- Footer Note -->
    <div class="p-6 border border-slate-100 text-slate-400">
        <p class="text-[10px] uppercase font-bold tracking-widest leading-relaxed">
            Note: Session termination is an integrity requirement. Operators should exit global mode immediately after completing administrative tasks to maintain least-privilege hygiene.
        </p>
    </div>
</div>
<?= $this->endSection() ?>
