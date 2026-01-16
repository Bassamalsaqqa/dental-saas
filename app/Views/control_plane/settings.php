<?= $this->extend('layouts/control_plane') ?>

<?= $this->section('content') ?>
<div class="max-w-2xl mx-auto text-center py-12">
    <div class="w-16 h-16 bg-slate-100 rounded-full flex items-center justify-center mx-auto mb-6">
        <i class="fas fa-cogs text-slate-400 text-2xl"></i>
    </div>
    <h1 class="text-2xl font-bold text-slate-900 mb-4">Global Settings</h1>
    <p class="text-slate-500 mb-8">
        Centralized configuration for retention policies, feature flags, and system limits is not yet implemented.
    </p>
    <div class="bg-blue-50 border border-blue-100 rounded-lg p-4 text-left">
        <h4 class="text-xs font-bold text-blue-800 uppercase tracking-widest mb-2">Policy Note</h4>
        <p class="text-xs text-blue-700 leading-relaxed">
            All global settings are currently managed via strict code-level configuration or direct database provisioning. 
            No runtime configuration UI is available to prevent configuration drift.
        </p>
    </div>
</div>
<?= $this->endSection() ?>
