<?= $this->extend('layouts/control_plane') ?>

<?= $this->section('content') ?>
<div class="max-w-4xl mx-auto">
    <div class="mb-8">
        <h1 class="text-2xl font-bold text-slate-900">Operations</h1>
        <p class="text-sm text-slate-500">Administrative Tasks & Workflows</p>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <!-- Onboarding -->
        <div class="bg-white p-6 rounded-lg border border-slate-200 shadow-sm">
            <h3 class="text-xs font-bold text-slate-400 uppercase tracking-widest mb-4">Clinic Onboarding</h3>
            <ul class="space-y-3">
                <li>
                    <a href="<?= base_url('controlplane/onboarding/clinic/create') ?>" class="text-sm text-indigo-600 hover:text-indigo-800 font-medium flex items-center">
                        <i class="fas fa-plus w-5"></i> Onboard New Clinic
                    </a>
                </li>
            </ul>
        </div>

        <!-- Monitoring -->
        <div class="bg-white p-6 rounded-lg border border-slate-200 shadow-sm">
            <h3 class="text-xs font-bold text-slate-400 uppercase tracking-widest mb-4">Monitoring</h3>
            <ul class="space-y-3">
                <li>
                    <a href="<?= base_url('controlplane/console') ?>" class="text-sm text-slate-600 hover:text-slate-800 font-medium flex items-center">
                        <i class="fas fa-chart-line w-5"></i> Operator Console
                    </a>
                </li>
            </ul>
        </div>

        <!-- System -->
        <div class="bg-white p-6 rounded-lg border border-slate-200 shadow-sm">
            <h3 class="text-xs font-bold text-slate-400 uppercase tracking-widest mb-4">System</h3>
            <ul class="space-y-3">
                <li>
                    <a href="<?= base_url('controlplane/danger') ?>" class="text-sm text-red-600 hover:text-red-800 font-medium flex items-center">
                        <i class="fas fa-exclamation-triangle w-5"></i> Danger Zone
                    </a>
                </li>
            </ul>
        </div>
    </div>
</div>
<?= $this->endSection() ?>
