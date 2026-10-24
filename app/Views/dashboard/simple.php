<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>

<div class="dashboard-workspace container mx-auto px-6 py-8">
<div class="space-y-8">
    <!-- Page Header -->
    <div class="flex flex-col lg:flex-row lg:justify-between lg:items-center space-y-6 lg:space-y-0">
        <div>
            <h1 class="text-4xl font-bold text-gray-800 mb-3"><?= lang('TenantMisc.dashboardSimpleTitle') ?></h1>
            <p class="text-gray-600 text-lg"><?= lang('TenantMisc.dashboardSimpleSubtitle') ?></p>
        </div>
    </div>

    <!-- Simple Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
        <div class="bg-[#F3EFE6] rounded-[9px] border border-[#C4B9A8] group transition-all duration-300">
            <div class="flex items-center justify-between p-1">
                <div class="flex items-center space-x-4">
                    <div class="p-4 rounded-2xl v3-warm-surface">
                        <i class="fas fa-users text-2xl"></i>
                    </div>
                    <div>
                        <p class="text-sm font-medium text-gray-600 mb-1"><?= lang('TenantMisc.totalPatients') ?></p>
                        <p class="text-3xl font-bold text-gray-900">0</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="bg-[#F3EFE6] rounded-[9px] border border-[#C4B9A8] group transition-all duration-300">
            <div class="flex items-center justify-between p-1">
                <div class="flex items-center space-x-4">
                    <div class="p-4 rounded-2xl v3-warm-surface">
                        <i class="fas fa-calendar-check text-2xl"></i>
                    </div>
                    <div>
                        <p class="text-sm font-medium text-gray-600 mb-1"><?= lang('TenantMisc.todaysAppointments') ?></p>
                        <p class="text-3xl font-bold text-gray-900">0</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="bg-[#F3EFE6] rounded-[9px] border border-[#C4B9A8] group transition-all duration-300">
            <div class="flex items-center justify-between p-1">
                <div class="flex items-center space-x-4">
                    <div class="p-4 rounded-2xl v3-warm-surface">
                        <i class="fas fa-clipboard-list text-2xl"></i>
                    </div>
                    <div>
                        <p class="text-sm font-medium text-gray-600 mb-1"><?= lang('TenantMisc.examinations') ?></p>
                        <p class="text-3xl font-bold text-gray-900">0</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="bg-[#F3EFE6] rounded-[9px] border border-[#C4B9A8] group transition-all duration-300">
            <div class="flex items-center justify-between p-1">
                <div class="flex items-center space-x-4">
                    <div class="p-4 rounded-2xl v3-warm-surface">
                        <i class="fas fa-dollar-sign text-2xl"></i>
                    </div>
                    <div>
                        <p class="text-sm font-medium text-gray-600 mb-1"><?= lang('TenantMisc.revenue') ?></p>
                        <p class="text-3xl font-bold text-gray-900"><?= formatCurrency(0) ?></p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Simple Content -->
    <div class="bg-[#F3EFE6] rounded-[9px] border border-[#C4B9A8] group transition-all duration-300">
        <div class="mb-6">
            <h3 class="text-xl font-bold text-gray-800 mb-2"><?= lang('TenantMisc.dashboardStatus') ?></h3>
            <p class="text-sm text-gray-600"><?= lang('TenantMisc.dashboardStatusRunning') ?></p>
        </div>
        
        <div class="text-center py-12">
            <div class="w-16 h-16 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-4">
                <i class="fas fa-check text-green-600 text-2xl"></i>
            </div>
            <h3 class="text-lg font-medium text-gray-900 mb-2"><?= lang('TenantMisc.dashboardWorkingTitle') ?></h3>
            <p class="text-gray-500"><?= lang('TenantMisc.dashboardWorkingDesc') ?></p>
        </div>
    </div>
</div>
</div>
<?= $this->endSection() ?>
