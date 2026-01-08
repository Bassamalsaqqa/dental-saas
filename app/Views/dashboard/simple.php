<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="container mx-auto px-6 py-8">
<div class="space-y-8">
    <!-- Page Header -->
    <div class="flex flex-col lg:flex-row lg:justify-between lg:items-center space-y-6 lg:space-y-0">
        <div>
            <h1 class="text-4xl font-bold text-gray-800 mb-3">Dental Management Dashboard</h1>
            <p class="text-gray-600 text-lg">Welcome to your dental practice management system</p>
        </div>
    </div>

    <!-- Simple Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
        <div class="bg-white rounded-lg border border-gray-200 shadow-sm group hover:shadow-xl transition-all duration-300">
            <div class="flex items-center justify-between p-1">
                <div class="flex items-center space-x-4">
                    <div class="p-4 rounded-2xl bg-gradient-to-br from-blue-500 to-blue-600 text-white shadow-lg">
                        <i class="fas fa-users text-2xl"></i>
                    </div>
                    <div>
                        <p class="text-sm font-medium text-gray-600 mb-1">Total Patients</p>
                        <p class="text-3xl font-bold text-gray-900">0</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg border border-gray-200 shadow-sm group hover:shadow-xl transition-all duration-300">
            <div class="flex items-center justify-between p-1">
                <div class="flex items-center space-x-4">
                    <div class="p-4 rounded-2xl bg-gradient-to-br from-green-500 to-green-600 text-white shadow-lg">
                        <i class="fas fa-calendar-check text-2xl"></i>
                    </div>
                    <div>
                        <p class="text-sm font-medium text-gray-600 mb-1">Today's Appointments</p>
                        <p class="text-3xl font-bold text-gray-900">0</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg border border-gray-200 shadow-sm group hover:shadow-xl transition-all duration-300">
            <div class="flex items-center justify-between p-1">
                <div class="flex items-center space-x-4">
                    <div class="p-4 rounded-2xl bg-gradient-to-br from-yellow-500 to-yellow-600 text-white shadow-lg">
                        <i class="fas fa-clipboard-list text-2xl"></i>
                    </div>
                    <div>
                        <p class="text-sm font-medium text-gray-600 mb-1">Examinations</p>
                        <p class="text-3xl font-bold text-gray-900">0</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg border border-gray-200 shadow-sm group hover:shadow-xl transition-all duration-300">
            <div class="flex items-center justify-between p-1">
                <div class="flex items-center space-x-4">
                    <div class="p-4 rounded-2xl bg-gradient-to-br from-purple-500 to-purple-600 text-white shadow-lg">
                        <i class="fas fa-dollar-sign text-2xl"></i>
                    </div>
                    <div>
                        <p class="text-sm font-medium text-gray-600 mb-1">Revenue</p>
                        <p class="text-3xl font-bold text-gray-900"><?= formatCurrency(0) ?></p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Simple Content -->
    <div class="bg-white rounded-lg border border-gray-200 shadow-sm group hover:shadow-lg transition-all duration-300">
        <div class="mb-6">
            <h3 class="text-xl font-bold text-gray-800 mb-2">Dashboard Status</h3>
            <p class="text-sm text-gray-600">System is running normally</p>
        </div>
        
        <div class="text-center py-12">
            <div class="w-16 h-16 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-4">
                <i class="fas fa-check text-green-600 text-2xl"></i>
            </div>
            <h3 class="text-lg font-medium text-gray-900 mb-2">Dashboard is working!</h3>
            <p class="text-gray-500">The basic dashboard structure is functioning correctly.</p>
        </div>
    </div>
</div>
</div>
<?= $this->endSection() ?>
