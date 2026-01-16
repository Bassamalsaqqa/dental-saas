<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Control Plane — <?= $title ?? 'Dashboard' ?></title>
    <link rel="stylesheet" href="<?= base_url('assets/css/tailwind.css') ?>?v=<?= time() ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <meta name="theme-color" content="#0f172a">
</head>
<body class="bg-slate-50 min-h-screen">
    <div class="flex h-screen">
        <!-- Control Plane Sidebar (Dark Mode) -->
        <aside class="w-64 bg-slate-900 border-r border-slate-800 shadow-2xl z-40 transition-all duration-300 transform flex-shrink-0 flex flex-col" id="sidebar">
            <!-- Sidebar Header -->
            <div class="p-4 border-b border-slate-800">
                <div class="flex items-center justify-between">
                    <div class="flex items-center space-x-3">
                        <div class="w-10 h-10 bg-gradient-to-br from-indigo-500 to-violet-600 rounded-xl flex items-center justify-center shadow-lg">
                            <i class="fas fa-network-wired text-white text-lg"></i>
                        </div>
                        <div>
                            <h1 class="text-sm font-black text-white uppercase tracking-wider">Control Plane</h1>
                            <p class="text-[10px] text-slate-400 font-medium">Global Admin</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Sidebar Navigation -->
            <nav class="flex-1 overflow-y-auto py-4">
                <div class="px-4 mb-2">
                    <h3 class="text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Platform</h3>
                    <ul class="space-y-1">
                        <li>
                            <a href="<?= base_url('controlplane/dashboard') ?>" class="group flex items-center px-3 py-2 text-sm font-medium rounded-lg transition-colors <?= (strpos(uri_string(), 'dashboard') !== false) ? 'bg-indigo-600 text-white' : 'text-slate-300 hover:bg-slate-800 hover:text-white' ?>">
                                <i class="fas fa-tachometer-alt w-5 h-5 mr-2 <?= (strpos(uri_string(), 'dashboard') !== false) ? 'text-white' : 'text-slate-400 group-hover:text-white' ?>"></i>
                                Dashboard
                            </a>
                        </li>
                        <li>
                            <a href="<?= base_url('controlplane/console') ?>" class="group flex items-center px-3 py-2 text-sm font-medium rounded-lg transition-colors <?= (strpos(uri_string(), 'console') !== false) ? 'bg-indigo-600 text-white' : 'text-slate-300 hover:bg-slate-800 hover:text-white' ?>">
                                <i class="fas fa-terminal w-5 h-5 mr-2 <?= (strpos(uri_string(), 'console') !== false) ? 'text-white' : 'text-slate-400 group-hover:text-white' ?>"></i>
                                Console
                            </a>
                        </li>
                        <li>
                            <a href="<?= base_url('controlplane/operations') ?>" class="group flex items-center px-3 py-2 text-sm font-medium rounded-lg transition-colors <?= (strpos(uri_string(), 'operations') !== false) ? 'bg-indigo-600 text-white' : 'text-slate-300 hover:bg-slate-800 hover:text-white' ?>">
                                <i class="fas fa-cogs w-5 h-5 mr-2 <?= (strpos(uri_string(), 'operations') !== false) ? 'text-white' : 'text-slate-400 group-hover:text-white' ?>"></i>
                                Operations
                            </a>
                        </li>
                    </ul>
                </div>

                <div class="px-4 mt-6">
                    <h3 class="text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">System</h3>
                    <ul class="space-y-1">
                        <li>
                            <a href="<?= base_url('controlplane/settings') ?>" class="group flex items-center px-3 py-2 text-sm font-medium rounded-lg transition-colors <?= (strpos(uri_string(), 'settings') !== false) ? 'bg-indigo-600 text-white' : 'text-slate-300 hover:bg-slate-800 hover:text-white' ?>">
                                <i class="fas fa-sliders-h w-5 h-5 mr-2 <?= (strpos(uri_string(), 'settings') !== false) ? 'text-white' : 'text-slate-400 group-hover:text-white' ?>"></i>
                                Settings
                            </a>
                        </li>
                        <li>
                            <a href="<?= base_url('controlplane/danger') ?>" class="group flex items-center px-3 py-2 text-sm font-medium rounded-lg transition-colors text-red-400 hover:bg-red-900/20 hover:text-red-300">
                                <i class="fas fa-exclamation-triangle w-5 h-5 mr-2"></i>
                                Danger Zone
                            </a>
                        </li>
                    </ul>
                </div>
            </nav>

            <!-- Sidebar Footer -->
            <div class="p-4 border-t border-slate-800 bg-slate-900">
                <div class="flex items-center space-x-3">
                    <div class="w-8 h-8 bg-indigo-600 rounded-full flex items-center justify-center">
                        <i class="fas fa-user-shield text-white text-xs"></i>
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-xs font-bold text-white">Super Admin</p>
                        <p class="text-[10px] text-slate-400">Global Mode Active</p>
                    </div>
                    <a href="<?= base_url('controlplane/danger/exit') ?>" class="text-slate-400 hover:text-white" title="Exit Global Mode">
                        <i class="fas fa-sign-out-alt"></i>
                    </a>
                </div>
            </div>
        </aside>

        <!-- Main Content -->
        <div class="flex-1 min-h-screen bg-slate-50 transition-all duration-200 flex flex-col overflow-hidden">
            <!-- Top Navigation -->
            <header class="bg-white border-b border-slate-200 shadow-sm z-30 px-6 py-3 flex justify-between items-center">
                <h2 class="text-xl font-bold text-slate-800"><?= $title ?? 'Dashboard' ?></h2>
                <div class="flex items-center space-x-4">
                    <span class="px-3 py-1 bg-indigo-100 text-indigo-700 rounded-full text-xs font-semibold uppercase tracking-wide">
                        Control Plane Environment
                    </span>
                </div>
            </header>

            <!-- Content Body -->
            <main class="flex-1 px-6 py-8 overflow-y-auto">
                <!-- Flash Messages -->
                <?php if (session()->getFlashdata('success')): ?>
                    <div class="mb-6 p-4 bg-green-50 border border-green-200 rounded-lg flex items-start">
                        <i class="fas fa-check-circle mr-3 text-lg text-green-600"></i>
                        <span class="text-green-800"><?= session()->getFlashdata('success') ?></span>
                    </div>
                <?php endif; ?>
                
                <?php if (session()->getFlashdata('error')): ?>
                    <div class="mb-6 p-4 bg-red-50 border border-red-200 rounded-lg flex items-start">
                        <i class="fas fa-exclamation-circle mr-3 text-lg text-red-600"></i>
                        <span class="text-red-800"><?= session()->getFlashdata('error') ?></span>
                    </div>
                <?php endif; ?>

                <?= $this->renderSection('content') ?>
            </main>
        </div>
    </div>
</body>
</html>
