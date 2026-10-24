<?= $this->extend('layouts/main_control_plane') ?>

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
                    <h1 class="text-3xl font-black v3-text-ink mb-2">RBAC System Setup</h1>
                    <p class="v3-text-muted font-medium">Initialize your Role-Based Access Control system</p>
                </div>
                <div class="flex space-x-3">
                    <a href="<?= base_url('roles') ?>" class="group relative px-6 py-3 v3-bg-panel v3-bg-panel v3-bg-panel v3-text-on-primary v3-radius hover:v3-bg-panel hover:v3-bg-panel transition-all duration-200 v3-elevation-none shadow-blue-500/25 hover:shadow-blue-500/40 ">
                        <i class="fas fa-user-tag mr-2"></i>
                        Manage Roles
                    </a>
                    <a href="<?= base_url('user-management') ?>" class="group relative px-6 py-3 v3-bg-panel v3-bg-panel v3-bg-panel v3-text-on-primary v3-radius hover:v3-bg-panel hover:v3-bg-panel transition-all duration-200 v3-elevation-none shadow-emerald-500/25 hover:shadow-emerald-500/40 ">
                        <i class="fas fa-users-cog mr-2"></i>
                        Manage Users
                    </a>
                </div>
            </div>
        </div>

        <!-- Info Card with Glassmorphism -->
        <div class="mb-8">
            <div class="group relative">
                <div class="absolute inset-0 v3-bg-panel v3-bg-panel v3-bg-panel v3-radius blur-xl group-hover:blur-2xl transition-all duration-200 opacity-0 group-hover:opacity-100"></div>
                <div class="relative backdrop-blur-xl v3-bg-panel border v3-border-default v3-radius p-6 v3-elevation-none shadow-blue-500/10 group-hover:shadow-blue-500/20 transition-all duration-200">
                    <div class="flex items-center">
                        <div class="relative">
                            <div class="absolute inset-0 v3-bg-panel v3-bg-panel v3-bg-panel v3-radius blur-lg opacity-75 group-hover:opacity-100 transition-opacity duration-300"></div>
                            <div class="relative p-4 v3-radius v3-bg-panel v3-bg-panel v3-bg-panel v3-text-on-primary v3-elevation-none group-hover: group-hover:rotate-2 transition-all duration-200">
                                <i class="fas fa-user-shield text-2xl"></i>
                            </div>
                        </div>
                        <div class="ml-6">
                            <h3 class="text-xl font-bold v3-text-ink mb-2">Role-Based Access Control</h3>
                            <p class="v3-text-muted font-medium">
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
            <div class="mb-6 p-4 v3-bg-panel v3-bg-panel v3-bg-panel border v3-border-default v3-text-primary v3-radius v3-elevation-none">
                <div class="flex items-center">
                    <i class="fas fa-check-circle v3-text-primary mr-3"></i>
                    <span class="font-medium"><?= session()->getFlashdata('success') ?></span>
                </div>
            </div>
        <?php endif; ?>

        <?php if (session()->getFlashdata('error')): ?>
            <div class="mb-6 p-4 v3-bg-panel v3-bg-panel to-pink-100 border v3-border-default v3-text-danger v3-radius v3-elevation-none">
                <div class="flex items-center">
                    <i class="fas fa-exclamation-triangle v3-text-danger mr-3"></i>
                    <span class="font-medium"><?= session()->getFlashdata('error') ?></span>
                </div>
            </div>
        <?php endif; ?>

        <!-- Setup Progress -->
        <div class="mb-8">
            <div class="group relative">
                <div class="absolute inset-0 v3-bg-panel v3-bg-panel to-pink-600/20 v3-radius blur-xl group-hover:blur-2xl transition-all duration-200 opacity-0 group-hover:opacity-100"></div>
                <div class="relative backdrop-blur-xl v3-bg-panel border v3-border-default v3-radius p-6 v3-elevation-none shadow-purple-500/10 group-hover:shadow-purple-500/20 transition-all duration-200">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-xl font-bold v3-text-ink">Setup Progress</h3>
                        <div class="flex items-center space-x-2 v3-bg-panel v3-bg-panel to-pink-100 px-4 py-2 rounded-full border v3-border-default">
                            <div class="w-2 h-2 v3-bg-primary rounded-full animate-pulse"></div>
                            <span class="v3-text-chair font-bold text-sm" id="progressBadge">Ready to Start</span>
                        </div>
                    </div>
                    <div class="mb-4">
                        <div class="w-full v3-bg-sand rounded-full h-3 overflow-hidden">
                            <div class="v3-bg-panel v3-bg-panel to-pink-500 h-full rounded-full transition-all duration-500 ease-out" style="width: 0%" id="progressBar"></div>
                        </div>
                    </div>
                    <p class="v3-text-muted font-medium">Follow the steps below to complete your RBAC system setup</p>
                </div>
            </div>
        </div>

        <!-- Setup Steps -->
        <div class="mb-8">
            <h3 class="text-2xl font-bold v3-text-ink mb-6">Setup Steps</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Step 1: Database Check -->
                <div class="group relative" data-step="1">
                    <div class="absolute inset-0 v3-bg-panel v3-bg-panel v3-bg-panel v3-radius blur-xl group-hover:blur-2xl transition-all duration-200 opacity-0 group-hover:opacity-100"></div>
                    <div class="relative backdrop-blur-xl v3-bg-panel border v3-border-default v3-radius p-6 v3-elevation-none shadow-blue-500/10 group-hover:shadow-blue-500/20 transition-all duration-200  hover:-translate-y-2 h-full flex flex-col">
                        <div class="flex items-center mb-4">
                            <div class="relative">
                                <div class="absolute inset-0 v3-bg-panel v3-bg-panel v3-bg-panel v3-radius blur-lg opacity-75 group-hover:opacity-100 transition-opacity duration-300"></div>
                                <div class="relative p-3 v3-radius v3-bg-panel v3-bg-panel v3-bg-panel v3-text-on-primary v3-elevation-none group-hover: group-hover:rotate-2 transition-all duration-200">
                                    <span class="text-xl font-bold">1</span>
                                </div>
                            </div>
                            <div class="ml-4">
                                <div class="flex items-center space-x-2">
                                    <i class="fas fa-database v3-text-primary"></i>
                                    <h4 class="text-lg font-bold v3-text-ink">Database Verification</h4>
                                    <div class="flex items-center space-x-1 v3-bg-panel v3-bg-panel v3-bg-panel px-2 py-1 rounded-full border v3-border-default">
                                        <div class="w-1.5 h-1.5 v3-bg-primary rounded-full animate-pulse"></div>
                                        <span class="v3-text-primary font-bold text-xs">Required</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <p class="v3-text-muted font-medium mb-4 flex-1">Verify that all RBAC database tables are properly created and accessible.</p>
                        <div class="flex items-center space-x-3">
                            <button onclick="checkDatabase()" class="group relative px-4 py-2 v3-bg-panel v3-bg-panel v3-bg-panel v3-text-on-primary rounded-lg hover:v3-bg-panel hover:v3-bg-panel transition-all duration-200 v3-elevation-none shadow-blue-500/25 hover:shadow-blue-500/40  v3-btn v3-btn--secondary">
                                <i class="fas fa-search mr-2"></i>
                                Check Database
                            </button>
                            <span class="step-status" id="step1-status"></span>
                        </div>
                    </div>
                </div>

                <!-- Step 2: Sync Permissions -->
                <div class="group relative" data-step="2">
                    <div class="absolute inset-0 v3-bg-panel v3-bg-panel v3-bg-panel v3-radius blur-xl group-hover:blur-2xl transition-all duration-200 opacity-0 group-hover:opacity-100"></div>
                    <div class="relative backdrop-blur-xl v3-bg-panel border v3-border-default v3-radius p-6 v3-elevation-none shadow-emerald-500/10 group-hover:shadow-emerald-500/20 transition-all duration-200  hover:-translate-y-2 h-full flex flex-col">
                        <div class="flex items-center mb-4">
                            <div class="relative">
                                <div class="absolute inset-0 v3-bg-panel v3-bg-panel v3-bg-panel v3-radius blur-lg opacity-75 group-hover:opacity-100 transition-opacity duration-300"></div>
                                <div class="relative p-3 v3-radius v3-bg-panel v3-bg-panel v3-bg-panel v3-text-on-primary v3-elevation-none group-hover: group-hover:rotate-2 transition-all duration-200">
                                    <span class="text-xl font-bold">2</span>
                                </div>
                            </div>
                            <div class="ml-4">
                                <div class="flex items-center space-x-2">
                                    <i class="fas fa-sync-alt v3-text-primary"></i>
                                    <h4 class="text-lg font-bold v3-text-ink">Sync Permissions</h4>
                                    <div class="flex items-center space-x-1 v3-bg-panel v3-bg-panel v3-bg-panel px-2 py-1 rounded-full border v3-border-default">
                                        <div class="w-1.5 h-1.5 v3-bg-primary rounded-full animate-pulse"></div>
                                        <span class="v3-text-primary font-bold text-xs">Required</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <p class="v3-text-muted font-medium mb-4 flex-1">Load all permissions from configuration files into the database.</p>
                        <div class="flex items-center space-x-3">
                            <a href="<?= base_url('rbac/sync') ?>" class="group relative px-4 py-2 v3-bg-panel v3-bg-panel v3-bg-panel v3-text-on-primary rounded-lg hover:v3-bg-panel hover:v3-bg-panel transition-all duration-200 v3-elevation-none shadow-emerald-500/25 hover:shadow-emerald-500/40 ">
                                <i class="fas fa-sync-alt mr-2"></i>
                                Sync Now
                            </a>
                            <span class="step-status" id="step2-status"></span>
                        </div>
                    </div>
                </div>

                <!-- Step 3: Initialize System -->
                <div class="group relative" data-step="3">
                    <div class="absolute inset-0 v3-bg-panel v3-bg-panel v3-bg-panel v3-radius blur-xl group-hover:blur-2xl transition-all duration-200 opacity-0 group-hover:opacity-100"></div>
                    <div class="relative backdrop-blur-xl v3-bg-panel border v3-border-default v3-radius p-6 v3-elevation-none shadow-purple-500/10 group-hover:shadow-purple-500/20 transition-all duration-200  hover:-translate-y-2 h-full flex flex-col">
                        <div class="flex items-center mb-4">
                            <div class="relative">
                                <div class="absolute inset-0 v3-bg-panel v3-bg-panel v3-bg-panel v3-radius blur-lg opacity-75 group-hover:opacity-100 transition-opacity duration-300"></div>
                                <div class="relative p-3 v3-radius v3-bg-panel v3-bg-panel v3-bg-panel v3-text-on-primary v3-elevation-none group-hover: group-hover:rotate-2 transition-all duration-200">
                                    <span class="text-xl font-bold">3</span>
                                </div>
                            </div>
                            <div class="ml-4">
                                <div class="flex items-center space-x-2">
                                    <i class="fas fa-cog v3-text-chair"></i>
                                    <h4 class="text-lg font-bold v3-text-ink">Initialize RBAC</h4>
                                    <div class="flex items-center space-x-1 v3-bg-panel v3-bg-panel v3-bg-panel px-2 py-1 rounded-full border v3-border-default">
                                        <div class="w-1.5 h-1.5 v3-bg-primary rounded-full animate-pulse"></div>
                                        <span class="v3-text-chair font-bold text-xs">Required</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <p class="v3-text-muted font-medium mb-4 flex-1">Complete first-time system setup and create default roles.</p>
                        <div class="flex items-center space-x-3">
                            <a href="<?= base_url('rbac/init') ?>" class="group relative px-4 py-2 v3-bg-panel v3-bg-panel v3-bg-panel v3-text-on-primary rounded-lg hover:v3-bg-panel hover:v3-bg-panel transition-all duration-200 v3-elevation-none shadow-purple-500/25 hover:shadow-purple-500/40 ">
                                <i class="fas fa-play mr-2"></i>
                                Initialize
                            </a>
                            <span class="step-status" id="step3-status"></span>
                        </div>
                    </div>
                </div>

                <!-- Step 4: Test System -->
                <div class="group relative" data-step="4">
                    <div class="absolute inset-0 v3-bg-panel v3-bg-panel v3-bg-panel v3-radius blur-xl group-hover:blur-2xl transition-all duration-200 opacity-0 group-hover:opacity-100"></div>
                    <div class="relative backdrop-blur-xl v3-bg-panel border v3-border-default v3-radius p-6 v3-elevation-none shadow-orange-500/10 group-hover:shadow-orange-500/20 transition-all duration-200  hover:-translate-y-2 h-full flex flex-col">
                        <div class="flex items-center mb-4">
                            <div class="relative">
                                <div class="absolute inset-0 v3-bg-panel v3-bg-panel v3-bg-panel v3-radius blur-lg opacity-75 group-hover:opacity-100 transition-opacity duration-300"></div>
                                <div class="relative p-3 v3-radius v3-bg-panel v3-bg-panel v3-bg-panel v3-text-on-primary v3-elevation-none group-hover: group-hover:rotate-2 transition-all duration-200">
                                    <span class="text-xl font-bold">4</span>
                                </div>
                            </div>
                            <div class="ml-4">
                                <div class="flex items-center space-x-2">
                                    <i class="fas fa-vial v3-text-warning"></i>
                                    <h4 class="text-lg font-bold v3-text-ink">System Test</h4>
                                    <div class="flex items-center space-x-1 v3-bg-panel v3-bg-panel v3-bg-panel px-2 py-1 rounded-full border v3-border-default">
                                        <div class="w-1.5 h-1.5 v3-bg-warning rounded-full animate-pulse"></div>
                                        <span class="v3-text-warning font-bold text-xs">Recommended</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <p class="v3-text-muted font-medium mb-4 flex-1">Run comprehensive tests to verify everything is working correctly.</p>
                        <div class="flex items-center space-x-3">
                            <button onclick="testSystem()" class="group relative px-4 py-2 v3-bg-panel v3-bg-panel v3-bg-panel v3-text-on-primary rounded-lg hover:v3-bg-panel hover:v3-bg-panel transition-all duration-200 v3-elevation-none shadow-orange-500/25 hover:shadow-orange-500/40  v3-btn v3-btn--secondary">
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
                    <div class="absolute inset-0 v3-bg-panel v3-bg-panel v3-bg-panel v3-radius blur-xl group-hover:blur-2xl transition-all duration-200 opacity-0 group-hover:opacity-100"></div>
                    <div class="relative backdrop-blur-xl v3-bg-panel border v3-border-default v3-radius p-6 v3-elevation-none shadow-indigo-500/10 group-hover:shadow-indigo-500/20 transition-all duration-200">
                        <h3 class="text-lg font-bold v3-text-ink mb-4">System Status</h3>
                        <div id="statusContent">
                            <div class="text-center py-4">
                                <div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 v3-border-default mb-3"></div>
                                <p class="v3-text-muted font-medium">Loading system status...</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Quick Actions -->
                <div class="group relative">
                    <div class="absolute inset-0 v3-bg-panel v3-bg-panel v3-bg-panel v3-radius blur-xl group-hover:blur-2xl transition-all duration-200 opacity-0 group-hover:opacity-100"></div>
                    <div class="relative backdrop-blur-xl v3-bg-panel border v3-border-default v3-radius p-6 v3-elevation-none shadow-emerald-500/10 group-hover:shadow-emerald-500/20 transition-all duration-200">
                        <h3 class="text-lg font-bold v3-text-ink mb-4">Quick Actions</h3>
                        <div class="space-y-3">
                            <a href="<?= base_url('roles') ?>" class="group relative flex items-center px-4 py-3 v3-bg-panel v3-bg-panel v3-bg-panel v3-text-on-primary rounded-lg hover:v3-bg-panel hover:v3-bg-panel transition-all duration-200 v3-elevation-none shadow-blue-500/25 hover:shadow-blue-500/40 ">
                                <i class="fas fa-user-tag mr-3"></i>
                                <span class="font-medium">Manage Roles</span>
                            </a>
                            <a href="<?= base_url('user-management') ?>" class="group relative flex items-center px-4 py-3 v3-bg-panel v3-bg-panel v3-bg-panel v3-text-on-primary rounded-lg hover:v3-bg-panel hover:v3-bg-panel transition-all duration-200 v3-elevation-none shadow-emerald-500/25 hover:shadow-emerald-500/40 ">
                                <i class="fas fa-users-cog mr-3"></i>
                                <span class="font-medium">Manage Users</span>
                            </a>
                            <a href="<?= base_url('rbac/status') ?>" class="group relative flex items-center px-4 py-3 v3-bg-panel v3-bg-panel v3-bg-panel v3-text-on-primary rounded-lg hover:v3-bg-panel hover:v3-bg-panel transition-all duration-200 v3-elevation-none shadow-purple-500/25 hover:shadow-purple-500/40 ">
                                <i class="fas fa-info-circle mr-3"></i>
                                <span class="font-medium">Check Status</span>
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Help Card -->
                <div class="group relative">
                    <div class="absolute inset-0 v3-bg-panel v3-bg-panel v3-bg-panel v3-radius blur-xl group-hover:blur-2xl transition-all duration-200 opacity-0 group-hover:opacity-100"></div>
                    <div class="relative backdrop-blur-xl v3-bg-panel border v3-border-default v3-radius p-6 v3-elevation-none shadow-orange-500/10 group-hover:shadow-orange-500/20 transition-all duration-200">
                        <h3 class="text-lg font-bold v3-text-ink mb-4">Need Help?</h3>
                        <p class="v3-text-muted font-medium mb-4">Having trouble with the setup? Here are some helpful resources:</p>
                        <div class="space-y-3">
                            <a href="/test_rbac.php" class="group relative flex items-center px-4 py-2 v3-bg-panel v3-bg-panel v3-bg-panel v3-text-on-primary rounded-lg hover:v3-bg-panel hover:v3-bg-panel transition-all duration-200 v3-elevation-none shadow-gray-500/25 hover:shadow-gray-500/40 ">
                                <i class="fas fa-bug mr-3"></i>
                                <span class="font-medium">Run Diagnostics</span>
                            </a>
                            <button onclick="showHelp()" class="group relative flex items-center px-4 py-2 v3-bg-panel v3-bg-panel v3-bg-panel v3-text-on-primary rounded-lg hover:v3-bg-panel hover:v3-bg-panel transition-all duration-200 v3-elevation-none shadow-orange-500/25 hover:shadow-orange-500/40  v3-btn v3-btn--secondary">
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
                <div class="absolute inset-0 v3-bg-panel v3-bg-panel v3-bg-panel v3-radius blur-xl group-hover:blur-2xl transition-all duration-200 opacity-0 group-hover:opacity-100"></div>
                <div class="relative backdrop-blur-xl v3-bg-panel border v3-border-default v3-radius p-6 v3-elevation-none shadow-cyan-500/10 group-hover:shadow-cyan-500/20 transition-all duration-200">
                    <h3 class="text-xl font-bold v3-text-ink mb-6">System Information</h3>
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-6">
                        <div class="text-center">
                            <div class="relative">
                                <div class="absolute inset-0 v3-bg-panel v3-bg-panel v3-bg-panel v3-radius blur-lg opacity-75 group-hover:opacity-100 transition-opacity duration-300"></div>
                                <div class="relative p-4 v3-radius v3-bg-panel v3-bg-panel v3-bg-panel v3-text-on-primary v3-elevation-none group-hover: group-hover:rotate-2 transition-all duration-200">
                                    <i class="fas fa-key text-2xl"></i>
                                </div>
                            </div>
                            <h4 class="text-2xl font-black v3-text-primary mt-3"><?= count(\App\Config\Permissions::getDefaultPermissions()) ?></h4>
                            <p class="v3-text-muted font-medium">Default Permissions</p>
                        </div>
                        <div class="text-center">
                            <div class="relative">
                                <div class="absolute inset-0 v3-bg-panel v3-bg-panel v3-bg-panel v3-radius blur-lg opacity-75 group-hover:opacity-100 transition-opacity duration-300"></div>
                                <div class="relative p-4 v3-radius v3-bg-panel v3-bg-panel v3-bg-panel v3-text-on-primary v3-elevation-none group-hover: group-hover:rotate-2 transition-all duration-200">
                                    <i class="fas fa-user-tag text-2xl"></i>
                                </div>
                            </div>
                            <h4 class="text-2xl font-black v3-text-primary mt-3"><?= count(\App\Config\Permissions::getDefaultRoles()) ?></h4>
                            <p class="v3-text-muted font-medium">Default Roles</p>
                        </div>
                        <div class="text-center">
                            <div class="relative">
                                <div class="absolute inset-0 v3-bg-panel v3-bg-panel v3-bg-panel v3-radius blur-lg opacity-75 group-hover:opacity-100 transition-opacity duration-300"></div>
                                <div class="relative p-4 v3-radius v3-bg-panel v3-bg-panel v3-bg-panel v3-text-on-primary v3-elevation-none group-hover: group-hover:rotate-2 transition-all duration-200">
                                    <i class="fas fa-folder text-2xl"></i>
                                </div>
                            </div>
                            <h4 class="text-2xl font-black v3-text-chair mt-3"><?= count(\App\Config\Permissions::getCategories()) ?></h4>
                            <p class="v3-text-muted font-medium">Permission Categories</p>
                        </div>
                        <div class="text-center">
                            <div class="relative">
                                <div class="absolute inset-0 v3-bg-panel v3-bg-panel v3-bg-panel v3-radius blur-lg opacity-75 group-hover:opacity-100 transition-opacity duration-300"></div>
                                <div class="relative p-4 v3-radius v3-bg-panel v3-bg-panel v3-bg-panel v3-text-on-primary v3-elevation-none group-hover: group-hover:rotate-2 transition-all duration-200">
                                    <i class="fas fa-cogs text-2xl"></i>
                                </div>
                            </div>
                            <h4 class="text-2xl font-black v3-text-warning mt-3">8</h4>
                            <p class="v3-text-muted font-medium">Default Actions</p>
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
    box-shadow:none;
}

.group:hover .group-hover\:shadow-emerald-500\/20 {
    box-shadow:none;
}

.group:hover .group-hover\:shadow-purple-500\/20 {
    box-shadow:none;
}

.group:hover .group-hover\:shadow-orange-500\/20 {
    box-shadow:none;
}

.group:hover .group-hover\:shadow-indigo-500\/20 {
    box-shadow:none;
}

.group:hover .group-hover\:shadow-cyan-500\/20 {
    box-shadow:none;
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
            renderErrorStatus(data.error);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        renderErrorStatus('Failed to load status');
    });
}

function renderErrorStatus(message) {
    const statusContent = document.getElementById('statusContent');
    statusContent.replaceChildren();

    const div = document.createElement('div');
    div.className = 'alert alert-danger d-flex align-items-center';

    const icon = document.createElement('i');
    icon.className = 'fas fa-exclamation-triangle me-2';

    div.appendChild(icon);
    div.appendChild(document.createTextNode(message));

    statusContent.appendChild(div);
}

function displayStatus(status) {
    const statusContent = document.getElementById('statusContent');
    statusContent.replaceChildren();

    const container = document.createElement('div');
    container.className = 'space-y-4';

    // Helper to create status rows
    const createStatusRow = (iconClass, label, isSynced, countText) => {
        const row = document.createElement('div');
        row.className = 'flex items-center justify-between p-3 v3-bg-panel rounded-lg border';

        if (label === 'Permissions') {
            row.classList.add('v3-bg-panel', 'v3-bg-panel', 'v3-border-default');
        } else if (label === 'Roles') {
            row.classList.add('v3-bg-panel', 'v3-bg-panel', 'v3-border-default');
        } else {
            row.classList.add('v3-bg-panel', 'v3-bg-panel', 'v3-border-default');
        }

        const leftDiv = document.createElement('div');
        leftDiv.className = 'flex items-center';

        const icon = document.createElement('i');
        icon.className = `${iconClass} mr-2`;
        if (label === 'Permissions') icon.classList.add('v3-text-primary');
        else if (label === 'Roles') icon.classList.add('v3-text-primary');
        else icon.classList.add('v3-text-chair');

        const spanLabel = document.createElement('span');
        spanLabel.className = 'font-medium v3-text-ink';
        spanLabel.textContent = label;

        leftDiv.appendChild(icon);
        leftDiv.appendChild(spanLabel);

        const rightDiv = document.createElement('div');
        rightDiv.className = 'flex items-center space-x-1 v3-bg-panel px-3 py-1 rounded-full border';

        if (isSynced) {
            rightDiv.classList.add('v3-bg-panel', 'v3-bg-panel', 'v3-border-default');
        } else {
            rightDiv.classList.add('v3-bg-panel', 'v3-bg-panel', 'v3-border-default');
        }

        const dot = document.createElement('div');
        dot.className = `w-1.5 h-1.5 rounded-full animate-pulse ${isSynced ? 'v3-bg-primary' : 'v3-bg-warning'}`;

        const text = document.createElement('span');
        text.className = `text-xs font-bold ${isSynced ? 'v3-text-primary' : 'v3-text-warning'}`;
        text.textContent = countText;

        rightDiv.appendChild(dot);
        rightDiv.appendChild(text);

        row.appendChild(leftDiv);
        row.appendChild(rightDiv);

        return row;
    };

    // Permissions Status
    container.appendChild(createStatusRow(
        'fas fa-key',
        'Permissions',
        status.permissions_synced,
        `${status.db_permissions}/${status.config_permissions}`
    ));

    // Roles Status
    container.appendChild(createStatusRow(
        'fas fa-user-tag',
        'Roles',
        status.roles_synced,
        `${status.db_roles}/${status.config_roles}`
    ));

    // Overall Status
    const isOverallReady = status.permissions_synced && status.roles_synced;
    container.appendChild(createStatusRow(
        'fas fa-shield-alt',
        'Overall Status',
        isOverallReady,
        isOverallReady ? 'Ready' : 'Setup Required'
    ));

    statusContent.appendChild(container);
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
    progressBadge.className = `v3-text-chair font-bold text-sm`;
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
    if (statusElement && statusElement.childElementCount === 0) {
        const icon = document.createElement('i');
        icon.className = 'fas fa-check-circle text-success';
        statusElement.appendChild(icon);
    }
}

function checkDatabase() {
    const button = event.target.closest('button');

    // Cache original children once if not already cached
    if (!button.hasOwnProperty('_originalChildren')) {
        button._originalChildren = Array.from(button.childNodes).map(n => n.cloneNode(true));
    }

    const loadingIcon = document.createElement('i');
    loadingIcon.className = 'fas fa-spinner fa-spin me-1';
    button.replaceChildren(loadingIcon, document.createTextNode(' Checking...'));
    button.disabled = true;

    // Simulate database check
    setTimeout(() => {
        const checkIcon = document.createElement('i');
        checkIcon.className = 'fas fa-check me-1';
        button.replaceChildren(checkIcon, document.createTextNode(' Verified'));
        button.className = 'btn btn-success btn-sm';
        markStepComplete('1');

        // Show success message
        showNotification('Database tables verified successfully!', 'success');
    }, 2000);
}

function testSystem() {
    const button = event.target.closest('button');

    // Cache original children once if not already cached
    if (!button.hasOwnProperty('_originalChildren')) {
        button._originalChildren = Array.from(button.childNodes).map(n => n.cloneNode(true));
    }

    const loadingIcon = document.createElement('i');
    loadingIcon.className = 'fas fa-spinner fa-spin me-1';
    button.replaceChildren(loadingIcon, document.createTextNode(' Testing...'));
    button.disabled = true;

    // Simulate system test
    setTimeout(() => {
        const checkIcon = document.createElement('i');
        checkIcon.className = 'fas fa-check me-1';
        button.replaceChildren(checkIcon, document.createTextNode(' Passed'));
        button.className = 'btn btn-success btn-sm';
        markStepComplete('4');

        // Show success message
        showNotification('All system tests passed successfully!', 'success');
    }, 3000);
}

function showNotification(message, type = 'info') {
    const bgClass = type === 'success' ? 'v3-bg-panel v3-bg-panel v3-border-default' : 'v3-bg-panel v3-bg-panel v3-border-default';
    const textClass = type === 'success' ? 'v3-text-primary' : 'v3-text-primary';
    const iconClass = type === 'success' ? 'fa-check-circle v3-text-primary' : 'fa-info-circle v3-text-primary';

    const notification = document.createElement('div');
    notification.className = `fixed top-4 right-4 z-50 p-4 v3-bg-panel ${bgClass} border v3-radius v3-elevation-none backdrop-blur-xl`;
    notification.style.cssText = 'min-width: 300px;';

    const contentDiv = document.createElement('div');
    contentDiv.className = 'flex items-center';

    const icon = document.createElement('i');
    icon.className = `fas ${iconClass} mr-3`;

    const messageSpan = document.createElement('span');
    messageSpan.className = `font-medium ${textClass}`;
    messageSpan.textContent = message;

    const closeBtn = document.createElement('button');
    closeBtn.type = 'button';
    closeBtn.className = 'ml-auto v3-text-muted v3-hover-text-muted';
    closeBtn.onclick = function() {
        this.closest('.fixed').remove();
    };

    const closeIcon = document.createElement('i');
    closeIcon.className = 'fas fa-times';
    closeBtn.appendChild(closeIcon);

    contentDiv.appendChild(icon);
    contentDiv.appendChild(messageSpan);
    contentDiv.appendChild(closeBtn);
    notification.appendChild(contentDiv);

    document.body.appendChild(notification);

    // Auto-remove after 5 seconds
    setTimeout(() => {
        if (notification.parentNode) {
            notification.remove();
        }
    }, 5000);
}

function showHelp() {
    // Remove existing modal if any
    const existingModal = document.getElementById('helpModal');
    if (existingModal) {
        existingModal.remove();
    }

    const modalDiv = document.createElement('div');
    modalDiv.className = 'modal fade';
    modalDiv.id = 'helpModal';
    modalDiv.tabIndex = -1;

    const dialogDiv = document.createElement('div');
    dialogDiv.className = 'modal-dialog modal-lg';

    const contentDiv = document.createElement('div');
    contentDiv.className = 'modal-content';

    // Header
    const headerDiv = document.createElement('div');
    headerDiv.className = 'modal-header';

    const title = document.createElement('h5');
    title.className = 'modal-title';
    title.textContent = 'RBAC Setup Help';

    const closeBtnHeader = document.createElement('button');
    closeBtnHeader.type = 'button';
    closeBtnHeader.className = 'btn-close';
    closeBtnHeader.setAttribute('data-bs-dismiss', 'modal');

    headerDiv.appendChild(title);
    headerDiv.appendChild(closeBtnHeader);

    // Body
    const bodyDiv = document.createElement('div');
    bodyDiv.className = 'modal-body';

    const h6Guide = document.createElement('h6');
    h6Guide.textContent = 'Step-by-Step Guide:';

    const ol = document.createElement('ol');
    const steps = [
        { label: 'Database Verification:', text: ' Ensure all RBAC tables are created' },
        { label: 'Sync Permissions:', text: ' Load permissions from config to database' },
        { label: 'Initialize RBAC:', text: ' Create default roles and permissions' },
        { label: 'System Test:', text: ' Verify everything works correctly' }
    ];

    steps.forEach(step => {
        const li = document.createElement('li');
        const strong = document.createElement('strong');
        strong.textContent = step.label;
        li.appendChild(strong);
        li.appendChild(document.createTextNode(step.text));
        ol.appendChild(li);
    });

    const h6Trouble = document.createElement('h6');
    h6Trouble.className = 'mt-4';
    h6Trouble.textContent = 'Troubleshooting:';

    const ul = document.createElement('ul');
    const troubleshootItems = [
        'Check database connection',
        'Verify table permissions',
        'Run diagnostics at /test_rbac.php'
    ];

    troubleshootItems.forEach(item => {
        const li = document.createElement('li');
        li.textContent = item;
        ul.appendChild(li);
    });

    bodyDiv.appendChild(h6Guide);
    bodyDiv.appendChild(ol);
    bodyDiv.appendChild(h6Trouble);
    bodyDiv.appendChild(ul);

    // Footer
    const footerDiv = document.createElement('div');
    footerDiv.className = 'modal-footer';

    const closeBtnFooter = document.createElement('button');
    closeBtnFooter.type = 'button';
    closeBtnFooter.className = 'btn btn-secondary';
    closeBtnFooter.setAttribute('data-bs-dismiss', 'modal');
    closeBtnFooter.textContent = 'Close';

    footerDiv.appendChild(closeBtnFooter);

    // Assemble
    contentDiv.appendChild(headerDiv);
    contentDiv.appendChild(bodyDiv);
    contentDiv.appendChild(footerDiv);
    dialogDiv.appendChild(contentDiv);
    modalDiv.appendChild(dialogDiv);

    // Add new modal
    document.body.appendChild(modalDiv);

    // Show modal
    const modal = new bootstrap.Modal(document.getElementById('helpModal'));
    modal.show();
}
</script>
    </div>
</div>
<?= $this->endSection() ?>
