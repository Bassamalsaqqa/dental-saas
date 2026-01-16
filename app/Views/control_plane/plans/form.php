<?= $this->extend('layouts/control_plane') ?>

<?= $this->section('content') ?>
<div class="max-w-4xl mx-auto space-y-10">
    <!-- Page Header -->
    <div class="flex items-center justify-between">
        <div class="flex items-center space-x-6">
            <div class="w-16 h-16 bg-gradient-to-br from-indigo-500 to-purple-600 rounded-[1.5rem] flex items-center justify-center text-white shadow-2xl shadow-indigo-500/30">
                <i class="fas fa-layer-group text-3xl"></i>
            </div>
            <div>
                <h1 class="text-3xl font-black text-slate-900 tracking-tight"><?= $title ?></h1>
                <p class="text-slate-500 font-medium">Configure tier properties, constraints and operational features.</p>
            </div>
        </div>
    </div>

    <!-- Plan Editor Form -->
    <div class="backdrop-blur-xl bg-white/80 border border-white/40 rounded-[2.5rem] shadow-2xl shadow-slate-200 overflow-hidden">
        <form action="<?= base_url('controlplane/plans/store') ?>" method="POST">
            <?= csrf_field() ?>
            <?php if (isset($plan)): ?>
                <input type="hidden" name="id" value="<?= $plan['id'] ?>">
            <?php endif; ?>
            
            <div class="p-10 space-y-12">
                <!-- Section 1: Basic Identity -->
                <div class="space-y-6">
                    <div class="flex items-center space-x-3 mb-2">
                        <span class="w-8 h-8 bg-indigo-50 text-indigo-600 rounded-full flex items-center justify-center font-black text-xs">01</span>
                        <h3 class="text-xs font-black text-slate-400 uppercase tracking-[0.2em]">Tier Identity</h3>
                    </div>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                        <div class="space-y-2">
                            <label class="text-sm font-black text-slate-700 uppercase tracking-wider ml-1">Plan Name</label>
                            <input type="text" name="name" value="<?= old('name', $plan['name'] ?? '') ?>" required
                                   class="w-full px-6 py-4 bg-slate-50 border-2 border-slate-100 rounded-2xl focus:border-indigo-500 focus:bg-white focus:ring-4 focus:ring-indigo-100 transition-all font-bold text-slate-800 placeholder-slate-300"
                                   placeholder="e.g. Enterprise Tier">
                        </div>
                        <div class="space-y-2">
                            <label class="text-sm font-black text-slate-700 uppercase tracking-wider ml-1">Operational Status</label>
                            <select name="status" class="w-full px-6 py-4 bg-slate-50 border-2 border-slate-100 rounded-2xl focus:border-indigo-500 focus:bg-white focus:ring-4 focus:ring-indigo-100 transition-all font-bold text-slate-800">
                                <option value="active" <?= old('status', $plan['status'] ?? '') == 'active' ? 'selected' : '' ?>>Active (Enabled for Onboarding)</option>
                                <option value="inactive" <?= old('status', $plan['status'] ?? '') == 'inactive' ? 'selected' : '' ?>>Inactive (Hidden from Onboarding)</option>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Section 2: Quota Architecture -->
                <div class="space-y-6">
                    <div class="flex items-center space-x-3 mb-2">
                        <span class="w-8 h-8 bg-indigo-50 text-indigo-600 rounded-full flex items-center justify-center font-black text-xs">02</span>
                        <h3 class="text-xs font-black text-slate-400 uppercase tracking-[0.2em]">Quota Configuration</h3>
                    </div>
                    
                    <div class="space-y-2">
                        <label class="text-sm font-black text-slate-700 uppercase tracking-wider ml-1">Limits Definition (JSON)</label>
                        <div class="relative group">
                            <textarea name="limits_json" rows="6" required
                                      class="w-full px-6 py-5 bg-slate-900 border-2 border-slate-800 rounded-[1.5rem] font-mono text-sm text-indigo-300 focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/10 transition-all placeholder-slate-700"
                                      placeholder='{"patients_active_max": 100, "exports": 50, "notifications_email": 1000}'><?= old('limits_json', $plan['limits_json'] ?? '') ?></textarea>
                            <div class="absolute top-4 right-4 opacity-0 group-hover:opacity-100 transition-opacity">
                                <span class="px-2 py-1 bg-white/5 border border-white/10 rounded-lg text-[8px] font-black uppercase text-slate-500">RAW JSON</span>
                            </div>
                        </div>
                        <div class="p-4 bg-indigo-50 rounded-xl border border-indigo-100 flex items-start space-x-3 mt-2">
                            <i class="fas fa-exclamation-circle text-indigo-400 mt-0.5"></i>
                            <p class="text-[10px] text-indigo-700 leading-relaxed uppercase font-black tracking-wider">
                                Required keys: "patients_active_max" and "exports". Use -1 for unlimited.
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Section 3: Feature Architecture -->
                <div class="space-y-6">
                    <div class="flex items-center space-x-3 mb-2">
                        <span class="w-8 h-8 bg-indigo-50 text-indigo-600 rounded-full flex items-center justify-center font-black text-xs">03</span>
                        <h3 class="text-xs font-black text-slate-400 uppercase tracking-[0.2em]">Feature Availability</h3>
                    </div>
                    
                    <div class="space-y-2">
                        <label class="text-sm font-black text-slate-700 uppercase tracking-wider ml-1">Logic Flags (JSON)</label>
                        <div class="relative group">
                            <textarea name="features_json" rows="6" required
                                      class="w-full px-6 py-5 bg-slate-900 border-2 border-slate-800 rounded-[1.5rem] font-mono text-sm text-emerald-400 focus:border-emerald-500 focus:ring-4 focus:ring-emerald-500/10 transition-all placeholder-slate-700"
                                      placeholder='{"exports": {"enabled": true}, "notifications": {"email": {"enabled": true}}}'><?= old('features_json', $plan['features_json'] ?? '') ?></textarea>
                            <div class="absolute top-4 right-4 opacity-0 group-hover:opacity-100 transition-opacity">
                                <span class="px-2 py-1 bg-white/5 border border-white/10 rounded-lg text-[8px] font-black uppercase text-slate-500">RAW JSON</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Footer Actions -->
            <div class="p-10 bg-slate-50 border-t border-slate-100 flex items-center justify-between">
                <a href="<?= base_url('controlplane/plans') ?>" class="text-sm font-black text-slate-400 uppercase tracking-widest hover:text-slate-600 transition-colors">Cancel Changes</a>
                <button type="submit" class="group relative flex items-center space-x-3 px-10 py-4 bg-indigo-600 text-white rounded-[1.5rem] font-black uppercase tracking-widest text-xs hover:bg-indigo-700 transition-all shadow-2xl shadow-indigo-500/40">
                    <span>Deploy Plan Engine</span>
                    <i class="fas fa-save text-[10px] group-hover:scale-110 transition-transform"></i>
                </button>
            </div>
        </form>
    </div>
</div>
<?= $this->endSection() ?>