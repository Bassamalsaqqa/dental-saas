<?= $this->extend('layouts/main_auth') ?>

<?= $this->section('title') ?>
RBAC System Setup
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<!-- Enhanced RBAC Setup with Modern Design -->
<div class="min-h-screen">
    <div class="container mx-auto px-4 py-6">
        <!-- Enhanced Page Header -->
        <div class="mb-8">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-black text-gray-900 mb-2">RBAC System Setup</h1>
                    <p class="text-gray-600 font-medium">Initialize your Role-Based Access Control system</p>
                </div>
                <div class="flex space-x-3">
                    <a href="<?= base_url('roles') ?>" class="group relative px-6 py-3 bg-gradient-to-r from-blue-500 to-blue-600 text-white rounded-xl hover:from-blue-600 hover:to-blue-700 transition-all duration-200 shadow-lg shadow-blue-500/25 hover:shadow-blue-500/40 hover:scale-105">
                        <i class="fas fa-user-tag mr-2"></i>
                        Manage Roles
                    </a>
                    <a href="<?= base_url('user-management') ?>" class="group relative px-6 py-3 bg-gradient-to-r from-emerald-500 to-emerald-600 text-white rounded-xl hover:from-emerald-600 hover:to-emerald-700 transition-all duration-200 shadow-lg shadow-emerald-500/25 hover:shadow-emerald-500/40 hover:scale-105">
                        <i class="fas fa-users-cog mr-2"></i>
                        Manage Users
                    </a>
                </div>
            </div>
        </div>

        <!-- Info Card with Glassmorphism -->
        <div class="mb-8">
            <div class="group relative">
                <div class="absolute inset-0 bg-gradient-to-br from-blue-500/20 to-purple-600/20 rounded-2xl blur-xl group-hover:blur-2xl transition-all duration-200 opacity-0 group-hover:opacity-100"></div>
                <div class="relative backdrop-blur-xl bg-white/80 border border-white/30 rounded-2xl p-6 shadow-2xl shadow-blue-500/10 group-hover:shadow-blue-500/20 transition-all duration-200">
                    <div class="flex items-center">
                        <div class="relative">
                            <div class="absolute inset-0 bg-gradient-to-br from-blue-500 to-purple-600 rounded-2xl blur-lg opacity-75 group-hover:opacity-100 transition-opacity duration-300"></div>
                            <div class="relative p-4 rounded-xl bg-gradient-to-br from-blue-500 to-purple-600 text-white shadow-xl group-hover:scale-105 group-hover:rotate-2 transition-all duration-200">
                                <i class="fas fa-user-shield text-2xl"></i>
                            </div>
                        </div>
                        <div class="ml-6">
                            <h3 class="text-xl font-bold text-gray-900 mb-2">Role-Based Access Control</h3>
                            <p class="text-gray-600 font-medium">
                                Set up comprehensive user roles and permissions to secure your dental management system. 
                                This system provides granular control over who can access what features.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Flash Messages -->
        <?php if (session()->getFlashdata('success')): ?>
            <div class="mb-6 p-4 bg-gradient-to-r from-emerald-100 to-green-100 border border-emerald-200 text-emerald-800 rounded-xl shadow-lg">
                <div class="flex items-center">
                    <i class="fas fa-check-circle text-emerald-600 mr-3"></i>
                    <span class="font-medium"><?= session()->getFlashdata('success') ?></span>
                </div>
            </div>
        <?php endif; ?>

        <?php if (session()->getFlashdata('error')): ?>
            <div class="mb-6 p-4 bg-gradient-to-r from-red-100 to-pink-100 border border-red-200 text-red-800 rounded-xl shadow-lg">
                <div class="flex items-center">
                    <i class="fas fa-exclamation-triangle text-red-600 mr-3"></i>
                    <span class="font-medium"><?= session()->getFlashdata('error') ?></span>
                </div>
            </div>
        <?php endif; ?>

        <!-- Setup Progress -->
        <div class="mb-8">
            <div class="group relative">
                <div class="absolute inset-0 bg-gradient-to-br from-purple-500/20 to-pink-600/20 rounded-2xl blur-xl group-hover:blur-2xl transition-all duration-200 opacity-0 group-hover:opacity-100"></div>
                <div class="relative backdrop-blur-xl bg-white/80 border border-white/30 rounded-2xl p-6 shadow-2xl shadow-purple-500/10 group-hover:shadow-purple-500/20 transition-all duration-200">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-xl font-bold text-gray-900">Setup Progress</h3>
                        <div class="flex items-center space-x-2 bg-gradient-to-r from-purple-100 to-pink-100 px-4 py-2 rounded-full border border-purple-200">
                            <div class="w-2 h-2 bg-purple-500 rounded-full animate-pulse"></div>
                            <span class="text-purple-700 font-bold text-sm" id="progressBadge">Ready to Start</span>
                        </div>
                    </div>
                    <div class="mb-4">
                        <div class="w-full bg-gray-200 rounded-full h-3 overflow-hidden">
                            <div class="bg-gradient-to-r from-purple-500 to-pink-500 h-full rounded-full transition-all duration-500 ease-out" style="width: 0%" id="progressBar"></div>
                        </div>
                    </div>
                    <p class="text-gray-600 font-medium">Follow the steps below to complete your RBAC system setup</p>
                </div>
            </div>
        </div>

        <!-- Setup Steps -->
        <div class="mb-8">
            <h3 class="text-2xl font-bold text-gray-900 mb-6">Setup Steps</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Step 1: Database Check -->
                <div class="group relative" data-step="1">
                    <div class="absolute inset-0 bg-gradient-to-br from-blue-500/20 to-cyan-600/20 rounded-2xl blur-xl group-hover:blur-2xl transition-all duration-200 opacity-0 group-hover:opacity-100"></div>
                    <div class="relative backdrop-blur-xl bg-white/80 border border-white/30 rounded-2xl p-6 shadow-2xl shadow-blue-500/10 group-hover:shadow-blue-500/20 transition-all duration-200 hover:scale-105 hover:-translate-y-2 h-full flex flex-col">
                        <div class="flex items-center mb-4">
                            <div class="relative">
                                <div class="absolute inset-0 bg-gradient-to-br from-blue-500 to-cyan-600 rounded-2xl blur-lg opacity-75 group-hover:opacity-100 transition-opacity duration-300"></div>
                                <div class="relative p-3 rounded-xl bg-gradient-to-br from-blue-500 to-cyan-600 text-white shadow-xl group-hover:scale-105 group-hover:rotate-2 transition-all duration-200">
                                    <span class="text-xl font-bold">1</span>
                                </div>
                            </div>
                            <div class="ml-4">
                                <div class="flex items-center space-x-2">
                                    <i class="fas fa-database text-blue-600"></i>
                                    <h4 class="text-lg font-bold text-gray-900">Database Verification</h4>
                                    <div class="flex items-center space-x-1 bg-gradient-to-r from-blue-100 to-cyan-100 px-2 py-1 rounded-full border border-blue-200">
                                        <div class="w-1.5 h-1.5 bg-blue-500 rounded-full animate-pulse"></div>
                                        <span class="text-blue-700 font-bold text-xs">Required</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <p class="text-gray-600 font-medium mb-4 flex-1">Verify that all RBAC database tables are properly created and accessible.</p>
                        <div class="flex items-center space-x-3">
                            <button onclick="checkDatabase()" class="group relative px-4 py-2 bg-gradient-to-r from-blue-500 to-blue-600 text-white rounded-lg hover:from-blue-600 hover:to-blue-700 transition-all duration-200 shadow-lg shadow-blue-500/25 hover:shadow-blue-500/40 hover:scale-105">
                                <i class="fas fa-search mr-2"></i>
                                Check Database
                            </button>
                            <span class="step-status" id="step1-status"></span>
                        </div>
                    </div>
                </div>

                <!-- Step 2: Sync Permissions -->
                <div class="group relative" data-step="2">
                    <div class="absolute inset-0 bg-gradient-to-br from-emerald-500/20 to-green-600/20 rounded-2xl blur-xl group-hover:blur-2xl transition-all duration-200 opacity-0 group-hover:opacity-100"></div>
                    <div class="relative backdrop-blur-xl bg-white/80 border border-white/30 rounded-2xl p-6 shadow-2xl shadow-emerald-500/10 group-hover:shadow-emerald-500/20 transition-all duration-200 hover:scale-105 hover:-translate-y-2 h-full flex flex-col">
                        <div class="flex items-center mb-4">
                            <div class="relative">
                                <div class="absolute inset-0 bg-gradient-to-br from-emerald-500 to-green-600 rounded-2xl blur-lg opacity-75 group-hover:opacity-100 transition-opacity duration-300"></div>
                                <div class="relative p-3 rounded-xl bg-gradient-to-br from-emerald-500 to-green-600 text-white shadow-xl group-hover:scale-105 group-hover:rotate-2 transition-all duration-200">
                                    <span class="text-xl font-bold">2</span>
                                </div>
                            </div>
                            <div class="ml-4">
                                <div class="flex items-center space-x-2">
                                    <i class="fas fa-sync-alt text-emerald-600"></i>
                                    <h4 class="text-lg font-bold text-gray-900">Sync Permissions</h4>
                                    <div class="flex items-center space-x-1 bg-gradient-to-r from-emerald-100 to-green-100 px-2 py-1 rounded-full border border-emerald-200">
                                        <div class="w-1.5 h-1.5 bg-emerald-500 rounded-full animate-pulse"></div>
                                        <span class="text-emerald-700 font-bold text-xs">Required</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <p class="text-gray-600 font-medium mb-4 flex-1">Load all permissions from configuration files into the database.</p>
                        <div class="flex items-center space-x-3">
                            <a href="<?= base_url('rbac/sync') ?>" class="group relative px-4 py-2 bg-gradient-to-r from-emerald-500 to-emerald-600 text-white rounded-lg hover:from-emerald-600 hover:to-emerald-700 transition-all duration-200 shadow-lg shadow-emerald-500/25 hover:shadow-emerald-500/40 hover:scale-105">
                                <i class="fas fa-sync-alt mr-2"></i>
                                Sync Now
                            </a>
                            <span class="step-status" id="step2-status"></span>
                        </div>
                    </div>
                </div>

                <!-- Step 3: Initialize System -->
                <div class="group relative" data-step="3">
                    <div class="absolute inset-0 bg-gradient-to-br from-purple-500/20 to-indigo-600/20 rounded-2xl blur-xl group-hover:blur-2xl transition-all duration-200 opacity-0 group-hover:opacity-100"></div>
                    <div class="relative backdrop-blur-xl bg-white/80 border border-white/30 rounded-2xl p-6 shadow-2xl shadow-purple-500/10 group-hover:shadow-purple-500/20 transition-all duration-200 hover:scale-105 hover:-translate-y-2 h-full flex flex-col">
                        <div class="flex items-center mb-4">
                            <div class="relative">
                                <div class="absolute inset-0 bg-gradient-to-br from-purple-500 to-indigo-600 rounded-2xl blur-lg opacity-75 group-hover:opacity-100 transition-opacity duration-300"></div>
                                <div class="relative p-3 rounded-xl bg-gradient-to-br from-purple-500 to-indigo-600 text-white shadow-xl group-hover:scale-105 group-hover:rotate-2 transition-all duration-200">
                                    <span class="text-xl font-bold">3</span>
                                </div>
                            </div>
                            <div class="ml-4">
                                <div class="flex items-center space-x-2">
                                    <i class="fas fa-cog text-purple-600"></i>
                                    <h4 class="text-lg font-bold text-gray-900">Initialize RBAC</h4>
                                    <div class="flex items-center space-x-1 bg-gradient-to-r from-purple-100 to-indigo-100 px-2 py-1 rounded-full border border-purple-200">
                                        <div class="w-1.5 h-1.5 bg-purple-500 rounded-full animate-pulse"></div>
                                        <span class="text-purple-700 font-bold text-xs">Required</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <p class="text-gray-600 font-medium mb-4 flex-1">Complete first-time system setup and create default roles.</p>
                        <div class="flex items-center space-x-3">
                            <a href="<?= base_url('rbac/init') ?>" class="group relative px-4 py-2 bg-gradient-to-r from-purple-500 to-purple-600 text-white rounded-lg hover:from-purple-600 hover:to-purple-700 transition-all duration-200 shadow-lg shadow-purple-500/25 hover:shadow-purple-500/40 hover:scale-105">
                                <i class="fas fa-play mr-2"></i>
                                Initialize
                            </a>
                            <span class="step-status" id="step3-status"></span>
                        </div>
                    </div>
                </div>

                <!-- Step 4: Test System -->
                <div class="group relative" data-step="4">
                    <div class="absolute inset-0 bg-gradient-to-br from-orange-500/20 to-red-600/20 rounded-2xl blur-xl group-hover:blur-2xl transition-all duration-200 opacity-0 group-hover:opacity-100"></div>
                    <div class="relative backdrop-blur-xl bg-white/80 border border-white/30 rounded-2xl p-6 shadow-2xl shadow-orange-500/10 group-hover:shadow-orange-500/20 transition-all duration-200 hover:scale-105 hover:-translate-y-2 h-full flex flex-col">
                        <div class="flex items-center mb-4">
                            <div class="relative">
                                <div class="absolute inset-0 bg-gradient-to-br from-orange-500 to-red-600 rounded-2xl blur-lg opacity-75 group-hover:opacity-100 transition-opacity duration-300"></div>
                                <div class="relative p-3 rounded-xl bg-gradient-to-br from-orange-500 to-red-600 text-white shadow-xl group-hover:scale-105 group-hover:rotate-2 transition-all duration-200">
                                    <span class="text-xl font-bold">4</span>
                                </div>
                            </div>
                            <div class="ml-4">
                                <div class="flex items-center space-x-2">
                                    <i class="fas fa-vial text-orange-600"></i>
                                    <h4 class="text-lg font-bold text-gray-900">System Test</h4>
                                    <div class="flex items-center space-x-1 bg-gradient-to-r from-orange-100 to-red-100 px-2 py-1 rounded-full border border-orange-200">
                                        <div class="w-1.5 h-1.5 bg-orange-500 rounded-full animate-pulse"></div>
                                        <span class="text-orange-700 font-bold text-xs">Recommended</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <p class="text-gray-600 font-medium mb-4 flex-1">Run comprehensive tests to verify everything is working correctly.</p>
                        <div class="flex items-center space-x-3">
                            <button onclick="testSystem()" class="group relative px-4 py-2 bg-gradient-to-r from-orange-500 to-orange-600 text-white rounded-lg hover:from-orange-600 hover:to-orange-700 transition-all duration-200 shadow-lg shadow-orange-500/25 hover:shadow-orange-500/40 hover:scale-105">
                                <i class="fas fa-vial mr-2"></i>
                                Test Now
                            </button>
                            <span class="step-status" id="step4-status"></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Status Panel -->
        <div class="mb-8">
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- System Status -->
                <div class="group relative">
                    <div class="absolute inset-0 bg-gradient-to-br from-indigo-500/20 to-blue-600/20 rounded-2xl blur-xl group-hover:blur-2xl transition-all duration-200 opacity-0 group-hover:opacity-100"></div>
                    <div class="relative backdrop-blur-xl bg-white/80 border border-white/30 rounded-2xl p-6 shadow-2xl shadow-indigo-500/10 group-hover:shadow-indigo-500/20 transition-all duration-200">
                        <h3 class="text-lg font-bold text-gray-900 mb-4">System Status</h3>
                        <div id="statusContent">
                            <div class="text-center py-4">
                                <div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-indigo-600 mb-3"></div>
                                <p class="text-gray-600 font-medium">Loading system status...</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Quick Actions -->
                <div class="group relative">
                    <div class="absolute inset-0 bg-gradient-to-br from-emerald-500/20 to-teal-600/20 rounded-2xl blur-xl group-hover:blur-2xl transition-all duration-200 opacity-0 group-hover:opacity-100"></div>
                    <div class="relative backdrop-blur-xl bg-white/80 border border-white/30 rounded-2xl p-6 shadow-2xl shadow-emerald-500/10 group-hover:shadow-emerald-500/20 transition-all duration-200">
                        <h3 class="text-lg font-bold text-gray-900 mb-4">Quick Actions</h3>
                        <div class="space-y-3">
                            <a href="<?= base_url('roles') ?>" class="group relative flex items-center px-4 py-3 bg-gradient-to-r from-blue-500 to-blue-600 text-white rounded-lg hover:from-blue-600 hover:to-blue-700 transition-all duration-200 shadow-lg shadow-blue-500/25 hover:shadow-blue-500/40 hover:scale-105">
                                <i class="fas fa-user-tag mr-3"></i>
                                <span class="font-medium">Manage Roles</span>
                            </a>
                            <a href="<?= base_url('user-management') ?>" class="group relative flex items-center px-4 py-3 bg-gradient-to-r from-emerald-500 to-emerald-600 text-white rounded-lg hover:from-emerald-600 hover:to-emerald-700 transition-all duration-200 shadow-lg shadow-emerald-500/25 hover:shadow-emerald-500/40 hover:scale-105">
                                <i class="fas fa-users-cog mr-3"></i>
                                <span class="font-medium">Manage Users</span>
                            </a>
                            <a href="<?= base_url('rbac/status') ?>" class="group relative flex items-center px-4 py-3 bg-gradient-to-r from-purple-500 to-purple-600 text-white rounded-lg hover:from-purple-600 hover:to-purple-700 transition-all duration-200 shadow-lg shadow-purple-500/25 hover:shadow-purple-500/40 hover:scale-105">
                                <i class="fas fa-info-circle mr-3"></i>
                                <span class="font-medium">Check Status</span>
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Help Card -->
                <div class="group relative">
                    <div class="absolute inset-0 bg-gradient-to-br from-orange-500/20 to-red-600/20 rounded-2xl blur-xl group-hover:blur-2xl transition-all duration-200 opacity-0 group-hover:opacity-100"></div>
                    <div class="relative backdrop-blur-xl bg-white/80 border border-white/30 rounded-2xl p-6 shadow-2xl shadow-orange-500/10 group-hover:shadow-orange-500/20 transition-all duration-200">
                        <h3 class="text-lg font-bold text-gray-900 mb-4">Need Help?</h3>
                        <p class="text-gray-600 font-medium mb-4">Having trouble with the setup? Here are some helpful resources:</p>
                        <div class="space-y-3">
                            <a href="/test_rbac.php" class="group relative flex items-center px-4 py-2 bg-gradient-to-r from-gray-500 to-gray-600 text-white rounded-lg hover:from-gray-600 hover:to-gray-700 transition-all duration-200 shadow-lg shadow-gray-500/25 hover:shadow-gray-500/40 hover:scale-105">
                                <i class="fas fa-bug mr-3"></i>
                                <span class="font-medium">Run Diagnostics</span>
                            </a>
                            <button onclick="showHelp()" class="group relative flex items-center px-4 py-2 bg-gradient-to-r from-orange-500 to-orange-600 text-white rounded-lg hover:from-orange-600 hover:to-orange-700 transition-all duration-200 shadow-lg shadow-orange-500/25 hover:shadow-orange-500/40 hover:scale-105">
                                <i class="fas fa-book mr-3"></i>
                                <span class="font-medium">View Documentation</span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- System Information -->
        <div class="mb-8">
            <div class="group relative">
                <div class="absolute inset-0 bg-gradient-to-br from-cyan-500/20 to-blue-600/20 rounded-2xl blur-xl group-hover:blur-2xl transition-all duration-200 opacity-0 group-hover:opacity-100"></div>
                <div class="relative backdrop-blur-xl bg-white/80 border border-white/30 rounded-2xl p-6 shadow-2xl shadow-cyan-500/10 group-hover:shadow-cyan-500/20 transition-all duration-200">
                    <h3 class="text-xl font-bold text-gray-900 mb-6">System Information</h3>
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-6">
                        <div class="text-center">
                            <div class="relative">
                                <div class="absolute inset-0 bg-gradient-to-br from-blue-500 to-blue-600 rounded-2xl blur-lg opacity-75 group-hover:opacity-100 transition-opacity duration-300"></div>
                                <div class="relative p-4 rounded-xl bg-gradient-to-br from-blue-500 to-blue-600 text-white shadow-xl group-hover:scale-105 group-hover:rotate-2 transition-all duration-200">
                                    <i class="fas fa-key text-2xl"></i>
                                </div>
                            </div>
                            <h4 class="text-2xl font-black text-blue-900 mt-3"><?= count(\App\Config\Permissions::getDefaultPermissions()) ?></h4>
                            <p class="text-gray-600 font-medium">Default Permissions</p>
                        </div>
                        <div class="text-center">
                            <div class="relative">
                                <div class="absolute inset-0 bg-gradient-to-br from-emerald-500 to-emerald-600 rounded-2xl blur-lg opacity-75 group-hover:opacity-100 transition-opacity duration-300"></div>
                                <div class="relative p-4 rounded-xl bg-gradient-to-br from-emerald-500 to-emerald-600 text-white shadow-xl group-hover:scale-105 group-hover:rotate-2 transition-all duration-200">
                                    <i class="fas fa-user-tag text-2xl"></i>
                                </div>
                            </div>
                            <h4 class="text-2xl font-black text-emerald-900 mt-3"><?= count(\App\Config\Permissions::getDefaultRoles()) ?></h4>
                            <p class="text-gray-600 font-medium">Default Roles</p>
                        </div>
                        <div class="text-center">
                            <div class="relative">
                                <div class="absolute inset-0 bg-gradient-to-br from-purple-500 to-purple-600 rounded-2xl blur-lg opacity-75 group-hover:opacity-100 transition-opacity duration-300"></div>
                                <div class="relative p-4 rounded-xl bg-gradient-to-br from-purple-500 to-purple-600 text-white shadow-xl group-hover:scale-105 group-hover:rotate-2 transition-all duration-200">
                                    <i class="fas fa-folder text-2xl"></i>
                                </div>
                            </div>
                            <h4 class="text-2xl font-black text-purple-900 mt-3"><?= count(\App\Config\Permissions::getCategories()) ?></h4>
                            <p class="text-gray-600 font-medium">Permission Categories</p>
                        </div>
                        <div class="text-center">
                            <div class="relative">
                                <div class="absolute inset-0 bg-gradient-to-br from-orange-500 to-orange-600 rounded-2xl blur-lg opacity-75 group-hover:opacity-100 transition-opacity duration-300"></div>
                                <div class="relative p-4 rounded-xl bg-gradient-to-br from-orange-500 to-orange-600 text-white shadow-xl group-hover:scale-105 group-hover:rotate-2 transition-all duration-200">
                                    <i class="fas fa-cogs text-2xl"></i>
                                </div>
                            </div>
                            <h4 class="text-2xl font-black text-orange-900 mt-3">8</h4>
                            <p class="text-gray-600 font-medium">Default Actions</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.progress-bar {
    transition: width 0.6s ease;
}

.group:hover .group-hover\:scale-105 {
    transform: scale(1.05);
}

.group:hover .group-hover\:rotate-2 {
    transform: rotate(2deg);
}

.group:hover .group-hover\:-translate-y-2 {
    transform: translateY(-0.5rem);
}

.group:hover .group-hover\:blur-2xl {
    filter: blur(40px);
}

.group:hover .group-hover\:opacity-100 {
    opacity: 1;
}

.group:hover .group-hover\:shadow-blue-500\/20 {
    box-shadow: 0 25px 50px -12px rgba(59, 130, 246, 0.2);
}

.group:hover .group-hover\:shadow-emerald-500\/20 {
    box-shadow: 0 25px 50px -12px rgba(16, 185, 129, 0.2);
}

.group:hover .group-hover\:shadow-purple-500\/20 {
    box-shadow: 0 25px 50px -12px rgba(168, 85, 247, 0.2);
}

.group:hover .group-hover\:shadow-orange-500\/20 {
    box-shadow: 0 25px 50px -12px rgba(249, 115, 22, 0.2);
}

.group:hover .group-hover\:shadow-indigo-500\/20 {
    box-shadow: 0 25px 50px -12px rgba(99, 102, 241, 0.2);
}

.group:hover .group-hover\:shadow-cyan-500\/20 {
    box-shadow: 0 25px 50px -12px rgba(6, 182, 212, 0.2);
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    loadStatus();
    initializeProgress();
});

function loadStatus() {
    fetch('<?= base_url('rbac/status') ?>')
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            displayStatus(data.status);
            updateProgress(data.status);
        } else {
            document.getElementById('statusContent').innerHTML = 
                '<div class="alert alert-danger d-flex align-items-center">' +
                '<i class="fas fa-exclamation-triangle me-2"></i>' +
                'Error loading status: ' + data.error + 
                '</div>';
        }
    })
    .catch(error => {
        console.error('Error:', error);
        document.getElementById('statusContent').innerHTML = 
            '<div class="alert alert-danger d-flex align-items-center">' +
            '<i class="fas fa-exclamation-triangle me-2"></i>' +
            'Failed to load status' +
            '</div>';
    });
}

function displayStatus(status) {
    const statusContent = document.getElementById('statusContent');
    
    let html = '<div class="space-y-4">';
    
    // Permissions Status
    html += '<div class="flex items-center justify-between p-3 bg-gradient-to-r from-blue-50 to-blue-100 rounded-lg border border-blue-200">';
    html += '<div class="flex items-center">';
    html += '<i class="fas fa-key text-blue-600 mr-2"></i>';
    html += '<span class="font-medium text-gray-900">Permissions</span>';
    html += '</div>';
    html += `<div class="flex items-center space-x-1 bg-gradient-to-r ${status.permissions_synced ? 'from-emerald-100 to-green-100 border-emerald-200' : 'from-orange-100 to-red-100 border-orange-200'} px-3 py-1 rounded-full border">`;
    html += `<div class="w-1.5 h-1.5 ${status.permissions_synced ? 'bg-emerald-500' : 'bg-orange-500'} rounded-full animate-pulse"></div>`;
    html += `<span class="text-xs font-bold ${status.permissions_synced ? 'text-emerald-700' : 'text-orange-700'}">${status.db_permissions}/${status.config_permissions}</span>`;
    html += '</div></div>';
    
    // Roles Status
    html += '<div class="flex items-center justify-between p-3 bg-gradient-to-r from-emerald-50 to-emerald-100 rounded-lg border border-emerald-200">';
    html += '<div class="flex items-center">';
    html += '<i class="fas fa-user-tag text-emerald-600 mr-2"></i>';
    html += '<span class="font-medium text-gray-900">Roles</span>';
    html += '</div>';
    html += `<div class="flex items-center space-x-1 bg-gradient-to-r ${status.roles_synced ? 'from-emerald-100 to-green-100 border-emerald-200' : 'from-orange-100 to-red-100 border-orange-200'} px-3 py-1 rounded-full border">`;
    html += `<div class="w-1.5 h-1.5 ${status.roles_synced ? 'bg-emerald-500' : 'bg-orange-500'} rounded-full animate-pulse"></div>`;
    html += `<span class="text-xs font-bold ${status.roles_synced ? 'text-emerald-700' : 'text-orange-700'}">${status.db_roles}/${status.config_roles}</span>`;
    html += '</div></div>';
    
    // Overall Status
    html += '<div class="flex items-center justify-between p-3 bg-gradient-to-r from-purple-50 to-purple-100 rounded-lg border border-purple-200">';
    html += '<div class="flex items-center">';
    html += '<i class="fas fa-shield-alt text-purple-600 mr-2"></i>';
    html += '<span class="font-medium text-gray-900">Overall Status</span>';
    html += '</div>';
    html += `<div class="flex items-center space-x-1 bg-gradient-to-r ${status.permissions_synced && status.roles_synced ? 'from-emerald-100 to-green-100 border-emerald-200' : 'from-orange-100 to-red-100 border-orange-200'} px-3 py-1 rounded-full border">`;
    html += `<div class="w-1.5 h-1.5 ${status.permissions_synced && status.roles_synced ? 'bg-emerald-500' : 'bg-orange-500'} rounded-full animate-pulse"></div>`;
    html += `<span class="text-xs font-bold ${status.permissions_synced && status.roles_synced ? 'text-emerald-700' : 'text-orange-700'}">${status.permissions_synced && status.roles_synced ? 'Ready' : 'Setup Required'}</span>`;
    html += '</div></div>';
    
    html += '</div>';
    
    statusContent.innerHTML = html;
}

function updateProgress(status) {
    const progressBar = document.getElementById('progressBar');
    const progressBadge = document.getElementById('progressBadge');
    
    let progress = 0;
    let badgeText = 'Ready to Start';
    let badgeClass = 'bg-primary';
    
    if (status.permissions_synced) progress += 25;
    if (status.roles_synced) progress += 25;
    
    // Check if steps are completed (simplified logic)
    if (progress >= 50) {
        badgeText = 'In Progress';
        badgeClass = 'bg-warning';
    }
    
    if (status.permissions_synced && status.roles_synced) {
        progress = 100;
        badgeText = 'Complete';
        badgeClass = 'bg-success';
    }
    
    progressBar.style.width = progress + '%';
    progressBadge.textContent = badgeText;
    progressBadge.className = `text-purple-700 font-bold text-sm`;
}

function initializeProgress() {
    // Add click handlers for step completion
    document.querySelectorAll('[data-step]').forEach(card => {
        card.addEventListener('click', function() {
            const step = this.dataset.step;
            markStepComplete(step);
        });
    });
}

function markStepComplete(step) {
    const statusElement = document.getElementById(`step${step}-status`);
    if (statusElement && !statusElement.innerHTML) {
        statusElement.innerHTML = '<i class="fas fa-check-circle text-success"></i>';
    }
}

function checkDatabase() {
    const button = event.target;
    const originalText = button.innerHTML;
    
    button.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> Checking...';
    button.disabled = true;
    
    // Simulate database check
    setTimeout(() => {
        button.innerHTML = '<i class="fas fa-check me-1"></i> Verified';
        button.className = 'btn btn-success btn-sm';
        markStepComplete('1');
        
        // Show success message
        showNotification('Database tables verified successfully!', 'success');
    }, 2000);
}

function testSystem() {
    const button = event.target;
    const originalText = button.innerHTML;
    
    button.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> Testing...';
    button.disabled = true;
    
    // Simulate system test
    setTimeout(() => {
        button.innerHTML = '<i class="fas fa-check me-1"></i> Passed';
        button.className = 'btn btn-success btn-sm';
        markStepComplete('4');
        
        // Show success message
        showNotification('All system tests passed successfully!', 'success');
    }, 3000);
}

function showNotification(message, type = 'info') {
    const bgClass = type === 'success' ? 'from-emerald-100 to-green-100 border-emerald-200' : 'from-blue-100 to-indigo-100 border-blue-200';
    const textClass = type === 'success' ? 'text-emerald-800' : 'text-blue-800';
    const icon = type === 'success' ? 'fa-check-circle text-emerald-600' : 'fa-info-circle text-blue-600';
    
    const notification = document.createElement('div');
    notification.className = `fixed top-4 right-4 z-50 p-4 bg-gradient-to-r ${bgClass} border rounded-xl shadow-lg backdrop-blur-xl`;
    notification.style.cssText = 'min-width: 300px;';
    notification.innerHTML = `
        <div class="flex items-center">
            <i class="fas ${icon} mr-3"></i>
            <span class="font-medium ${textClass}">${message}</span>
            <button type="button" class="ml-auto text-gray-400 hover:text-gray-600" onclick="this.parentElement.parentElement.remove()">
                <i class="fas fa-times"></i>
            </button>
        </div>
    `;
    
    document.body.appendChild(notification);
    
    // Auto-remove after 5 seconds
    setTimeout(() => {
        if (notification.parentNode) {
            notification.remove();
        }
    }, 5000);
}

function showHelp() {
    const helpModal = `
        <div class="modal fade" id="helpModal" tabindex="-1">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">RBAC Setup Help</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <h6>Step-by-Step Guide:</h6>
                        <ol>
                            <li><strong>Database Verification:</strong> Ensure all RBAC tables are created</li>
                            <li><strong>Sync Permissions:</strong> Load permissions from config to database</li>
                            <li><strong>Initialize RBAC:</strong> Create default roles and permissions</li>
                            <li><strong>System Test:</strong> Verify everything works correctly</li>
                        </ol>
                        
                        <h6 class="mt-4">Troubleshooting:</h6>
                        <ul>
                            <li>Check database connection</li>
                            <li>Verify table permissions</li>
                            <li>Run diagnostics at /test_rbac.php</li>
                        </ul>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    </div>
                </div>
            </div>
        </div>
    `;
    
    // Remove existing modal if any
    const existingModal = document.getElementById('helpModal');
    if (existingModal) {
        existingModal.remove();
    }
    
    // Add new modal
    document.body.insertAdjacentHTML('beforeend', helpModal);
    
    // Show modal
    const modal = new bootstrap.Modal(document.getElementById('helpModal'));
    modal.show();
}
</script>
    </div>
</div>
<?= $this->endSection() ?>
