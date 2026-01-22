<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= esc($title ?? 'Control Plane') ?></title>
    <link rel="icon" type="image/png" href="<?= base_url('assets/images/favicon.png') ?>">
    <link rel="stylesheet" href="<?= base_url('assets/css/tailwind.css') ?>?v=<?= time() ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <meta name="theme-color" content="#0f172a">
    <meta name="csrf-name" content="<?= csrf_token() ?>">
    <meta name="csrf-token" content="<?= csrf_hash() ?>">
    <meta name="csrf-header" content="<?= csrf_header() ?>">
</head>
<body class="bg-gray-50 min-h-screen">
    <?= view('components/toast') ?>

    <div class="flex min-h-screen">
        <!-- Control Plane Sidebar -->
        <aside class="w-64 backdrop-blur-xl bg-white/80 border-r border-white/30 shadow-2xl shadow-indigo-500/10 z-40 transition-all duration-300 transform flex-shrink-0 lg:translate-x-0 -translate-x-full flex flex-col" id="cp-sidebar">
            <!-- Sidebar Header -->
            <div class="px-2 pb-2 pt-3 border-b border-white/30">
                <div class="flex items-center justify-between">
                    <a href="<?= base_url('controlplane/dashboard') ?>" class="flex items-center space-x-3 group cursor-pointer hover:bg-white/30 rounded-xl p-2 -m-2 transition-all duration-200">
                        <div class="relative group">
                            <div class="absolute inset-0 bg-gradient-to-br from-indigo-500 to-violet-600 rounded-xl blur-lg opacity-75 group-hover:opacity-100 transition-opacity duration-200"></div>
                            <div class="relative w-10 h-10 bg-gradient-to-br from-indigo-500 to-violet-600 rounded-xl flex items-center justify-center shadow-xl group-hover:scale-105 group-hover:rotate-2 transition-all duration-200">
                                <i class="fas fa-network-wired text-white text-lg"></i>
                            </div>
                        </div>
                        <div class="min-w-0 flex-shrink-0">
                            <h1 class="text-lg font-black text-gray-900 whitespace-nowrap">Control Plane</h1>
                            <div class="flex items-center space-x-1">
                                <div class="w-1.5 h-1.5 bg-indigo-500 rounded-full animate-pulse"></div>
                                <p class="text-xs text-gray-500 font-medium whitespace-nowrap">Global Admin</p>
                            </div>
                        </div>
                    </a>
                    <button class="group relative p-2 text-gray-600 hover:text-gray-900 hover:bg-white/50 rounded-xl transition-all duration-200 hover:scale-105 lg:block hidden" onclick="document.getElementById('cp-sidebar').classList.toggle('-translate-x-full')" title="Toggle Sidebar">
                        <div class="absolute inset-0 bg-gradient-to-r from-indigo-500/10 to-violet-500/10 rounded-xl blur opacity-0 group-hover:opacity-100 transition-opacity duration-200"></div>
                        <i class="fas fa-bars relative z-10"></i>
                    </button>
                </div>
            </div>

            <!-- Sidebar Navigation -->
            <nav class="flex-1">
                <div class="p-4">
                    <div class="flex items-center space-x-2 mb-4">
                        <div class="w-2 h-2 bg-gradient-to-r from-indigo-500 to-violet-600 rounded-full animate-pulse"></div>
                        <h3 class="text-xs font-bold text-gray-600 uppercase tracking-wider">Platform</h3>
                    </div>
                    <?php $hideCpNav = $hide_cp_nav ?? false; ?>
                    <?php if ($hideCpNav): ?>
                        <div class="rounded-xl border border-indigo-100 bg-indigo-50/60 p-4 text-sm text-indigo-700">
                            Global mode is required to access control plane tools.
                        </div>
                    <?php else: ?>
                        <?php
                        $sections = [
                            'Platform' => [
                                [
                                    'title' => 'Dashboard',
                                    'url' => 'controlplane/dashboard',
                                    'icon' => 'fas fa-tachometer-alt',
                                    'color' => 'indigo',
                                ],
                            ],
                            'Governance' => [
                                [
                                    'title' => 'Console',
                                    'url' => 'controlplane/console',
                                    'icon' => 'fas fa-terminal',
                                    'color' => 'slate',
                                ],
                                [
                                    'title' => 'Admin Tools',
                                    'url' => 'controlplane/admin-tools',
                                    'icon' => 'fas fa-toolbox',
                                    'color' => 'blue',
                                ],
                                [
                                    'title' => 'Operations',
                                    'url' => 'controlplane/operations',
                                    'icon' => 'fas fa-cogs',
                                    'color' => 'blue',
                                ],
                                [
                                    'title' => 'Onboard Clinic',
                                    'url' => 'controlplane/onboarding/clinic/create',
                                    'icon' => 'fas fa-hospital-user',
                                    'color' => 'emerald',
                                ],
                            ],
                            'System' => [
                                [
                                    'title' => 'Settings',
                                    'url' => 'controlplane/settings',
                                    'icon' => 'fas fa-sliders-h',
                                    'color' => 'gray',
                                ],
                                [
                                    'title' => 'Danger Zone',
                                    'url' => 'controlplane/danger',
                                    'icon' => 'fas fa-exclamation-triangle',
                                    'color' => 'red',
                                ],
                            ],
                        ];

                        $colorClasses = [
                            'indigo' => ['from-indigo-500 to-indigo-600', 'shadow-indigo-500/25', 'from-indigo-50 to-blue-50', 'text-indigo-800', 'shadow-indigo-500/10', 'text-indigo-600', 'text-indigo-700'],
                            'slate' => ['from-slate-600 to-slate-700', 'shadow-slate-500/25', 'from-slate-50 to-slate-100', 'text-slate-700', 'shadow-slate-500/10', 'text-slate-600', 'text-slate-700'],
                            'blue' => ['from-blue-500 to-blue-600', 'shadow-blue-500/25', 'from-blue-50 to-cyan-50', 'text-blue-800', 'shadow-blue-500/10', 'text-blue-600', 'text-blue-700'],
                            'gray' => ['from-gray-500 to-gray-600', 'shadow-gray-500/25', 'from-gray-50 to-slate-50', 'text-gray-800', 'shadow-gray-500/10', 'text-gray-600', 'text-gray-700'],
                            'emerald' => ['from-emerald-500 to-emerald-600', 'shadow-emerald-500/25', 'from-emerald-50 to-green-50', 'text-emerald-800', 'shadow-emerald-500/10', 'text-emerald-600', 'text-emerald-700'],
                            'red' => ['from-red-500 to-red-600', 'shadow-red-500/25', 'from-red-50 to-rose-50', 'text-red-800', 'shadow-red-500/10', 'text-red-600', 'text-red-700'],
                        ];
                        ?>

                        <?php foreach ($sections as $sectionTitle => $items): ?>
                            <div class="mb-6">
                                <h4 class="text-xs font-bold text-gray-500 uppercase tracking-widest mb-2"><?= esc($sectionTitle) ?></h4>
                                <ul class="space-y-2">
                                    <?php foreach ($items as $item): ?>
                                        <?php
                                        $isActive = (strpos(uri_string(), $item['url']) === 0);
                                        $colors = $colorClasses[$item['color']];
                                        ?>
                                        <li>
                                            <a href="<?= base_url($item['url']) ?>" class="group relative flex items-center px-4 py-3 text-sm font-semibold rounded-xl transition-all duration-200 <?= $isActive ? 'bg-gradient-to-r ' . $colors[0] . ' text-white shadow-lg ' . $colors[1] . ' mx-1' : 'text-gray-700 hover:bg-gradient-to-r hover:' . $colors[2] . ' hover:' . $colors[3] . ' hover:shadow-md hover:' . $colors[4] . ' mx-1' ?>">
                                                <div class="absolute inset-0 bg-gradient-to-r <?= $colors[5] . ' ' . $colors[6] ?> rounded-xl blur opacity-0 group-hover:opacity-100 transition-opacity duration-200"></div>
                                                <i class="<?= $item['icon'] ?> w-5 h-5 mr-3 relative z-10 <?= $isActive ? 'text-white' : $colors[5] . ' group-hover:' . $colors[6] ?>"></i>
                                                <span class="relative z-10"><?= $item['title'] ?></span>
                                            </a>
                                        </li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </nav>

            <!-- Sidebar Footer -->
            <div class="p-4 border-t border-white/30 backdrop-blur-xl bg-white/80">
                <div class="group relative cursor-pointer hover:bg-white/50 rounded-xl p-3 transition-all duration-200 hover:shadow-lg hover:shadow-indigo-500/10 block">
                    <div class="absolute inset-0 bg-gradient-to-r from-indigo-500/10 to-violet-500/10 rounded-xl blur opacity-0 group-hover:opacity-100 transition-opacity duration-200"></div>
                    <div class="flex items-center space-x-3 relative z-10">
                        <div class="relative">
                            <div class="absolute inset-0 bg-gradient-to-br from-indigo-500 to-violet-600 rounded-full blur-lg opacity-75 group-hover:opacity-100 transition-opacity duration-200"></div>
                            <div class="relative w-10 h-10 bg-gradient-to-br from-indigo-500 to-violet-600 rounded-full flex items-center justify-center shadow-lg group-hover:scale-105 group-hover:rotate-2 transition-all duration-200">
                                <i class="fas fa-user-shield text-white text-sm"></i>
                            </div>
                        </div>
                        <div class="flex-1 min-w-0">
                            <div class="text-sm font-bold text-gray-900 truncate">
                                <?php
                                $user = $user ?? null;
                                $displayName = 'Super Admin';
                                if ($user) {
                                    if (property_exists($user, 'first_name') && property_exists($user, 'last_name') &&
                                        !empty($user->first_name) && !empty($user->last_name)) {
                                        $displayName = esc(trim($user->first_name . ' ' . $user->last_name));
                                    } elseif (property_exists($user, 'username') && !empty($user->username)) {
                                        $displayName = esc($user->username);
                                    } elseif (property_exists($user, 'email') && !empty($user->email)) {
                                        $displayName = esc($user->email);
                                    }
                                }
                                echo $displayName;
                                ?>
                            </div>
                            <div class="flex items-center space-x-1">
                                <div class="w-1.5 h-1.5 bg-indigo-500 rounded-full animate-pulse"></div>
                                <div class="text-xs text-gray-500 font-medium">
                                    Global Mode Active
                                </div>
                            </div>
                        </div>
                        <a href="<?= base_url('controlplane/danger') ?>" class="text-gray-400 hover:text-gray-900" title="Exit Global Mode">
                            <i class="fas fa-sign-out-alt"></i>
                        </a>
                    </div>
                </div>
            </div>
        </aside>

        <!-- Main Content -->
        <div class="flex-1 min-h-screen bg-gray-50 transition-all duration-200 flex flex-col overflow-hidden">
            <!-- Top Navigation -->
            <header class="bg-white border-b border-gray-200 shadow-sm z-30 px-6 py-3 flex justify-between items-center">
                <div>
                    <p class="text-xs uppercase tracking-widest text-gray-400 font-semibold">Control Plane</p>
                    <h2 class="text-xl font-bold text-gray-900"><?= esc($title ?? 'Dashboard') ?></h2>
                </div>
                <div class="flex items-center space-x-4">
                    <span class="px-3 py-1 bg-indigo-100 text-indigo-700 rounded-full text-xs font-semibold uppercase tracking-wide">
                        Global Admin
                    </span>
                </div>
            </header>

            <!-- Content Body -->
            <main class="flex-1 px-6 py-8 overflow-y-auto">
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
