<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="container mx-auto px-4 py-8">
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-900">Control Plane</h1>
        <p class="text-gray-600">System Administration & Tenant Governance</p>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <!-- Status Card -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <h2 class="text-lg font-semibold text-gray-900 mb-4">Global Mode Status</h2>
            <div class="flex items-center space-x-4 mb-6">
                <div class="flex items-center">
                    <div class="w-4 h-4 rounded-full <?= session()->get('global_mode') ? 'bg-green-500 animate-pulse' : 'bg-gray-300' ?> mr-2"></div>
                    <span class="text-sm font-medium text-gray-700"><?= session()->get('global_mode') ? 'Active' : 'Inactive' ?></span>
                </div>
            </div>
            
            <div class="flex space-x-3">
                <?php if (session()->get('global_mode')): ?>
                    <form action="<?= base_url('controlplane/exit') ?>" method="post">
                        <?= csrf_field() ?>
                        <button type="submit" class="px-4 py-2 bg-red-100 text-red-700 rounded-lg hover:bg-red-200 font-medium transition-colors">
                            Exit Global Mode
                        </button>
                    </form>
                <?php else: ?>
                    <form action="<?= base_url('controlplane/enter') ?>" method="post">
                        <?= csrf_field() ?>
                        <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 font-medium transition-colors">
                            Enter Global Mode
                        </button>
                    </form>
                <?php endif; ?>
            </div>
        </div>

        <!-- Actions Card -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <h2 class="text-lg font-semibold text-gray-900 mb-4">Operations</h2>
            <div class="space-y-3">
                <a href="<?= base_url('controlplane/onboarding/clinic/create') ?>" class="block p-4 border border-gray-100 rounded-lg hover:bg-gray-50 transition-colors group">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center space-x-3">
                            <div class="w-10 h-10 bg-indigo-100 text-indigo-600 rounded-lg flex items-center justify-center">
                                <i class="fas fa-plus"></i>
                            </div>
                            <div>
                                <h3 class="font-medium text-gray-900 group-hover:text-indigo-600">Onboard New Clinic</h3>
                                <p class="text-xs text-gray-500">Create clinic, admin, and subscription</p>
                            </div>
                        </div>
                        <i class="fas fa-chevron-right text-gray-300 group-hover:text-indigo-400"></i>
                    </div>
                </a>
                
                <a href="<?= base_url('settings') ?>" class="block p-4 border border-gray-100 rounded-lg hover:bg-gray-50 transition-colors group">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center space-x-3">
                            <div class="w-10 h-10 bg-blue-100 text-blue-600 rounded-lg flex items-center justify-center">
                                <i class="fas fa-cog"></i>
                            </div>
                            <div>
                                <h3 class="font-medium text-gray-900 group-hover:text-blue-600">Global Settings</h3>
                                <p class="text-xs text-gray-500">Manage retention policies & channels</p>
                            </div>
                        </div>
                        <i class="fas fa-chevron-right text-gray-300 group-hover:text-blue-400"></i>
                    </div>
                </a>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>
