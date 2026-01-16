<?= $this->extend('layouts/control_plane') ?>

<?= $this->section('content') ?>
<div class="space-y-10">
    <!-- Page Header -->
    <div class="flex items-center justify-between">
        <div class="flex items-center space-x-6">
            <div class="w-16 h-16 bg-gradient-to-br from-indigo-500 to-purple-600 rounded-[1.5rem] flex items-center justify-center text-white shadow-2xl shadow-indigo-500/30">
                <i class="fas fa-layer-group text-3xl"></i>
            </div>
            <div>
                <h1 class="text-3xl font-black text-slate-900 tracking-tight">Plan Engines</h1>
                <p class="text-slate-500 font-medium">Define tier constraints, feature availability, and global quotas.</p>
            </div>
        </div>
        <a href="<?= base_url('controlplane/plans/create') ?>" class="group flex items-center space-x-3 px-8 py-4 bg-white border-2 border-indigo-600 text-indigo-600 rounded-[1.5rem] font-black uppercase tracking-widest text-xs hover:bg-indigo-600 hover:text-white transition-all shadow-xl shadow-indigo-500/10">
            <i class="fas fa-plus transition-transform group-hover:rotate-90"></i>
            <span>Define New Plan</span>
        </a>
    </div>

    <!-- Plans Grid -->
    <div class="grid grid-cols-1 xl:grid-cols-2 gap-8">
        <?php foreach ($plans as $plan): ?>
            <div class="backdrop-blur-xl bg-white/80 border border-white/40 rounded-[2.5rem] shadow-2xl shadow-slate-200 overflow-hidden hover:border-indigo-300 transition-all duration-500 group">
                <div class="p-8 space-y-8">
                    <!-- Plan Identity -->
                    <div class="flex items-start justify-between">
                        <div class="space-y-1">
                            <h3 class="text-2xl font-black text-slate-900 tracking-tight"><?= esc($plan['name']) ?></h3>
                            <div class="flex items-center space-x-3">
                                <span class="px-3 py-1 bg-slate-50 text-slate-500 border border-slate-100 rounded-lg text-[10px] font-black uppercase tracking-widest">Plan ID: #<?= $plan['id'] ?></span>
                                <span class="inline-flex items-center px-3 py-1 rounded-lg text-[10px] font-black uppercase tracking-widest <?= $plan['status'] === 'active' ? 'bg-emerald-50 text-emerald-700 border border-emerald-100' : 'bg-rose-50 text-rose-700 border border-rose-100' ?>">
                                    <div class="w-1.5 h-1.5 rounded-full mr-2 <?= $plan['status'] === 'active' ? 'bg-emerald-500 animate-pulse' : 'bg-rose-500' ?>"></div>
                                    <?= esc($plan['status']) ?>
                                </span>
                            </div>
                        </div>
                        <div class="flex space-x-2">
                            <a href="<?= base_url('controlplane/plans/edit/' . $plan['id']) ?>" class="w-10 h-10 bg-slate-50 text-slate-400 rounded-xl flex items-center justify-center hover:bg-indigo-50 hover:text-indigo-600 transition-all shadow-sm">
                                <i class="fas fa-edit text-xs"></i>
                            </a>
                            <button onclick="toggleStatus(<?= $plan['id'] ?>)" class="w-10 h-10 bg-slate-50 text-slate-400 rounded-xl flex items-center justify-center hover:bg-slate-900 hover:text-white transition-all shadow-sm">
                                <i class="fas fa-power-off text-xs"></i>
                            </button>
                        </div>
                    </div>

                    <!-- Limits & Feature Overview -->
                    <div class="grid grid-cols-2 gap-4">
                        <?php 
                            $limits = json_decode($plan['limits_json'], true) ?? [];
                            foreach (['patients_active_max', 'exports', 'notifications_email'] as $metric):
                                $val = $limits[$metric] ?? 0;
                                $displayVal = $val === -1 ? 'Unlimited' : number_format($val);
                        ?>
                            <div class="p-4 bg-slate-50 rounded-2xl border border-slate-100 group-hover:bg-white transition-colors">
                                <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1"><?= str_replace('_', ' ', $metric) ?></p>
                                <p class="text-lg font-black text-slate-800 tracking-tight"><?= $displayVal ?></p>
                            </div>
                        <?php endforeach; ?>
                    </div>

                    <!-- Raw Config Preview (Collapsible pattern) -->
                    <div class="space-y-3">
                        <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">Logic Configuration</p>
                        <div class="p-5 bg-slate-900 rounded-2xl font-mono text-[10px] text-indigo-300 overflow-x-auto shadow-inner border-t-4 border-indigo-500">
                            <pre><?= esc($plan['features_json']) ?></pre>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<script>
function toggleStatus(id) {
    if(!confirm('Toggle plan engine status? This will immediately affect all subscribers.')) return;
    
    fetch('<?= base_url('controlplane/plans/toggle-status/') ?>' + id, {
        method: 'POST',
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            '<?= config('Security')->headerName ?>': '<?= csrf_hash() ?>'
        }
    }).then(res => res.json())
      .then(data => {
          if(data.success) {
              location.reload();
          } else {
              alert(data.message);
          }
      });
}
</script>
<?= $this->endSection() ?>