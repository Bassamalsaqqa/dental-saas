<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= esc($title ?? 'Control Plane') ?></title>
    <link rel="icon" type="image/png" href="<?= base_url('assets/images/favicon.png') ?>">
    <link rel="stylesheet" href="<?= base_url('assets/css/tailwind.css') ?>?v=<?= time() ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <meta name="theme-color" content="#0284c7">
    <meta name="csrf-name" content="<?= csrf_token() ?>">
    <meta name="csrf-token" content="<?= csrf_hash() ?>">
    <meta name="csrf-header" content="<?= csrf_header() ?>">
    <style>
        body {
            font-family: Inter, ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, "Noto Sans", sans-serif;
        }
        .glass-card {
            background: rgba(255, 255, 255, 0.82);
            border: 1px solid rgba(255, 255, 255, 0.35);
            box-shadow: 0 30px 80px -50px rgba(59, 130, 246, 0.4);
            backdrop-filter: blur(24px);
        }
        .glass-muted {
            background: rgba(255, 255, 255, 0.68);
            border: 1px solid rgba(255, 255, 255, 0.3);
            box-shadow: 0 20px 50px -40px rgba(15, 23, 42, 0.4);
            backdrop-filter: blur(20px);
        }
    </style>
</head>
<body class="bg-gray-50 min-h-screen">
    <?= view('components/toast') ?>

    <div class="flex min-h-screen">
        <?php $hideCpNav = $hide_cp_nav ?? false; ?>
        <?php if (!$hideCpNav): ?>
            <aside class="w-64 backdrop-blur-xl bg-white/80 border-r border-white/30 shadow-2xl shadow-blue-500/10 z-40 transition-all duration-300 transform flex-shrink-0 lg:translate-x-0 -translate-x-full flex flex-col" id="sidebar">
                <div class="px-2 pb-2 pt-3 border-b border-white/30">
                    <div class="flex items-center justify-between">
                        <a href="<?= base_url('controlplane/dashboard') ?>" class="flex items-center space-x-3 group cursor-pointer hover:bg-white/30 rounded-xl p-2 -m-2 transition-all duration-200">
                            <div class="relative group">
                                <div class="absolute inset-0 bg-gradient-to-br from-blue-500 to-purple-600 rounded-xl blur-lg opacity-75 group-hover:opacity-100 transition-opacity duration-200"></div>
                                <div class="relative w-10 h-10 bg-gradient-to-br from-blue-500 to-purple-600 rounded-xl flex items-center justify-center shadow-xl group-hover:scale-105 group-hover:rotate-2 transition-all duration-200">
                                    <i class="fas fa-shield-alt text-white text-lg"></i>
                                </div>
                            </div>
                            <div class="min-w-0 flex-shrink-0">
                                <h1 class="text-lg font-black text-gray-900 whitespace-nowrap">Control Plane</h1>
                                <div class="flex items-center space-x-1">
                                    <div class="w-1.5 h-1.5 bg-emerald-500 rounded-full animate-pulse"></div>
                                    <p class="text-xs text-gray-500 font-medium whitespace-nowrap">Global Admin</p>
                                </div>
                            </div>
                        </a>
                        <button class="group relative p-2 text-gray-600 hover:text-gray-900 hover:bg-white/50 rounded-xl transition-all duration-200 hover:scale-105 lg:block hidden" onclick="toggleSidebar()" title="Toggle Sidebar (Ctrl+B)">
                            <div class="absolute inset-0 bg-gradient-to-r from-blue-500/10 to-purple-500/10 rounded-xl blur opacity-0 group-hover:opacity-100 transition-opacity duration-200"></div>
                            <i class="fas fa-bars relative z-10"></i>
                        </button>
                    </div>
                </div>

                <nav class="flex-1" id="sidebar-nav">
                    <div class="p-4">
                        <div class="flex items-center space-x-2 mb-4">
                            <div class="w-2 h-2 bg-gradient-to-r from-blue-500 to-purple-600 rounded-full animate-pulse"></div>
                            <h3 class="text-xs font-bold text-gray-600 uppercase tracking-wider">Core</h3>
                        </div>
                        <?php
                        $menuGroups = [
                            [
                                'label' => 'Platform',
                                'items' => [
                                    ['title' => 'Dashboard', 'url' => 'controlplane/dashboard', 'icon' => 'fas fa-tachometer-alt', 'color' => 'blue'],
                                ],
                            ],
                            [
                                'label' => 'Tenants',
                                'items' => [
                                    ['title' => 'Clinics', 'url' => 'controlplane/clinics', 'icon' => 'fas fa-hospital-user', 'color' => 'emerald'],
                                    ['title' => 'Onboarding', 'url' => 'controlplane/clinic/create', 'icon' => 'fas fa-user-plus', 'color' => 'emerald'],
                                ],
                            ],
                            [
                                'label' => 'Plans & Billing',
                                'items' => [
                                    ['title' => 'Plans', 'url' => 'controlplane/plans', 'icon' => 'fas fa-layer-group', 'color' => 'indigo'],
                                    ['title' => 'Plan Requests', 'url' => 'controlplane/plans#plan-requests', 'icon' => 'fas fa-inbox', 'color' => 'indigo'],
                                ],
                            ],
                            [
                                'label' => 'Templates',
                                'items' => [
                                    ['title' => 'Email Templates', 'url' => 'controlplane/settings#template-editor', 'icon' => 'fas fa-envelope-open-text', 'color' => 'teal'],
                                ],
                            ],
                            [
                                'label' => 'Governance',
                                'items' => [
                                    ['title' => 'Operations', 'url' => 'controlplane/operations', 'icon' => 'fas fa-cogs', 'color' => 'amber'],
                                ],
                            ],
                            [
                                'label' => 'System',
                                'items' => [
                                    ['title' => 'Settings', 'url' => 'controlplane/settings', 'icon' => 'fas fa-sliders-h', 'color' => 'teal'],
                                ],
                            ],
                        ];

                        $colorClasses = [
                            'blue' => ['from-blue-500 to-blue-600', 'shadow-blue-500/25', 'from-blue-50 to-cyan-50', 'text-blue-800', 'shadow-blue-500/10', 'text-blue-600', 'text-blue-700'],
                            'emerald' => ['from-emerald-500 to-emerald-600', 'shadow-emerald-500/25', 'from-emerald-50 to-green-50', 'text-emerald-800', 'shadow-emerald-500/10', 'text-emerald-600', 'text-emerald-700'],
                            'indigo' => ['from-indigo-500 to-indigo-600', 'shadow-indigo-500/25', 'from-indigo-50 to-blue-50', 'text-indigo-800', 'shadow-indigo-500/10', 'text-indigo-600', 'text-indigo-700'],
                            'teal' => ['from-teal-500 to-teal-600', 'shadow-teal-500/25', 'from-teal-50 to-cyan-50', 'text-teal-800', 'shadow-teal-500/10', 'text-teal-600', 'text-teal-700'],
                            'amber' => ['from-amber-500 to-amber-600', 'shadow-amber-500/25', 'from-amber-50 to-yellow-50', 'text-amber-800', 'shadow-amber-500/10', 'text-amber-600', 'text-amber-700'],
                        ];
                        ?>
                        <?php foreach ($menuGroups as $group): ?>
                            <?php if (!empty($group['label'])): ?>
                                <div class="flex items-center space-x-2 mt-6 mb-4">
                                    <div class="w-2 h-2 bg-gradient-to-r from-blue-500 to-purple-600 rounded-full animate-pulse"></div>
                                    <h3 class="text-xs font-bold text-gray-600 uppercase tracking-wider"><?= esc($group['label']) ?></h3>
                                </div>
                            <?php endif; ?>
                            <ul class="space-y-2">
                                <?php foreach ($group['items'] as $item): ?>
                                    <?php
                                    $navUrl = $item['url'];
                                    $navPath = explode('#', $navUrl)[0];
                                    $isActive = (strpos(uri_string(), $navPath) === 0);
                                    $colors = $colorClasses[$item['color']];
                                    ?>
                                    <li>
                                        <a href="<?= base_url($item['url']) ?>" class="group relative flex items-center px-4 py-3 text-sm font-semibold rounded-xl transition-all duration-200 <?= $isActive ? 'bg-gradient-to-r ' . $colors[0] . ' text-white shadow-lg ' . $colors[1] . ' mx-1' : 'text-gray-700 hover:bg-gradient-to-r hover:' . $colors[2] . ' hover:' . $colors[3] . ' hover:shadow-md hover:' . $colors[4] . ' mx-1' ?>">
                                            <div class="absolute inset-0 bg-gradient-to-r <?= $colors[5] . ' ' . $colors[6] ?> rounded-xl blur opacity-0 group-hover:opacity-100 transition-opacity duration-200"></div>
                                            <i class="<?= $item['icon'] ?> w-5 h-5 mr-3 relative z-10 <?= $isActive ? 'text-white' : $colors[5] . ' group-hover:' . $colors[6] ?>"></i>
                                            <span class="relative z-10"><?= esc($item['title']) ?></span>
                                        </a>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        <?php endforeach; ?>
                    </div>
                </nav>

                <div class="p-4 border-t border-white/30 backdrop-blur-xl bg-white/80 text-xs text-gray-500">
                    Control Plane v2
                </div>
            </aside>
        <?php endif; ?>

        <div class="flex-1 flex flex-col min-h-screen">
            <header class="sticky top-0 z-30 backdrop-blur-xl bg-white/70 border-b border-white/30 px-6 py-4 flex items-center justify-between">
                <div>
                    <p class="text-xs uppercase tracking-widest text-gray-400 font-semibold">Control Plane</p>
                    <h1 class="text-xl font-bold text-gray-900"><?= esc($title ?? 'Dashboard') ?></h1>
                </div>
                <?php $userEmail = (isset($user) && $user && property_exists($user, 'email')) ? $user->email : 'Super Admin'; ?>
                <div class="flex items-center gap-3">
                    <span class="text-xs font-semibold bg-emerald-100 text-emerald-700 px-3 py-1 rounded-full">Global Admin</span>
                    <div class="flex items-center gap-2 text-sm text-gray-600">
                        <i class="fas fa-user-circle text-lg"></i>
                        <span><?= esc($userEmail) ?></span>
                    </div>
                    <form action="<?= base_url('controlplane/exit') ?>" method="post">
                        <?= csrf_field() ?>
                        <button class="px-3 py-2 rounded-xl bg-slate-900 text-white text-xs font-semibold hover:bg-slate-800 transition">
                            Exit Control Plane
                        </button>
                    </form>
                </div>
            </header>

            <main class="flex-1 px-6 py-8">
                <?php if (session()->getFlashdata('success')): ?>
                    <div class="mb-6 glass-card rounded-2xl px-4 py-3 text-emerald-800">
                        <?= session()->getFlashdata('success') ?>
                    </div>
                <?php endif; ?>

                <?php if (session()->getFlashdata('error')): ?>
                    <div class="mb-6 glass-card rounded-2xl px-4 py-3 text-rose-800">
                        <?= session()->getFlashdata('error') ?>
                    </div>
                <?php endif; ?>

                <?= $this->renderSection('content') ?>
            </main>
        </div>
    </div>

    <script>
        function toggleSidebar() {
            const sidebar = document.getElementById('sidebar');
            if (!sidebar) return;
            sidebar.classList.toggle('-translate-x-full');
        }

        document.addEventListener('keydown', function(e) {
            if (e.ctrlKey && e.key === 'b') {
                e.preventDefault();
                toggleSidebar();
            }
        });
    </script>
</body>
</html>
