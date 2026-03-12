<!DOCTYPE html>
<html lang="<?= esc($locale ?? 'en') ?>" dir="<?= !empty($is_rtl) ? 'rtl' : 'ltr' ?>" translate="no">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?? esc($clinic['name']) ?></title>
    <link rel="icon" type="image/png" href="<?= base_url('assets/images/favicon.png') ?>">
    <link rel="stylesheet" href="<?= base_url('assets/css/tailwind.css') ?>?v=<?= time() ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <!-- DataTables Buttons CSS - Only load for pages that need DataTables -->
    <?php if (!isset($disableDataTables) || !$disableDataTables): ?>
    <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.2/css/buttons.dataTables.min.css">
    <?php endif; ?>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/index.global.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <!-- DataTables Core - Only load for pages that need DataTables -->
    <?php if (!isset($disableDataTables) || !$disableDataTables): ?>
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/jquery.dataTables.min.css">
    <script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.2/js/dataTables.buttons.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.html5.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.print.min.js"></script>
    <?php endif; ?>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/pdfmake.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/vfs_fonts.js"></script>
    <meta name="description" content="Professional dental management system with comprehensive patient care, examination tracking, and appointment scheduling.">
    <meta name="theme-color" content="#0284c7">
    <meta name="csrf-name" content="<?= csrf_token() ?>">
    <meta name="csrf-token" content="<?= csrf_hash() ?>">
    <meta name="csrf-header" content="<?= csrf_header() ?>">
    <meta name="active-clinic-id" content="<?= (int)session('active_clinic_id') ?>">
    <meta name="api-session-token-url" content="<?= base_url('api/session-token') ?>">
    <meta name="google" content="notranslate">

    <style>
        /* Modern Sidebar Transitions */
        #sidebar:not(.no-transition) {
            transition: width 0.3s cubic-bezier(0.4, 0, 0.2, 1), transform 0.3s cubic-bezier(0.4, 0, 0.2, 1) !important;
        }
        
        .no-transition {
            transition: none !important;
        }
        
        #sidebar-nav, #sidebar-header, #sidebar-footer {
            transition: padding 0.3s ease;
        }

        /* Collapsed State Styles */
        .sidebar-collapsed {
            width: 5rem !important; /* 80px */
        }

        .sidebar-collapsed #sidebar-title,
        .sidebar-collapsed #sidebar-nav h3,
        .sidebar-collapsed #sidebar-nav span,
        .sidebar-collapsed #sidebar-footer .flex-1,
        .sidebar-collapsed #sidebar-footer i.fa-external-link-alt {
            display: none !important;
        }

        .sidebar-collapsed #sidebar-header .flex {
            justify-content: center !important;
        }

        .sidebar-collapsed #sidebar-nav ul li a {
            justify-content: center !important;
            padding-left: 0 !important;
            padding-right: 0 !important;
            margin-left: 0.5rem !important;
            margin-right: 0.5rem !important;
        }

        .sidebar-collapsed #sidebar-nav ul li a i {
            margin-right: 0 !important;
            font-size: 1.25rem;
        }

        .sidebar-collapsed #sidebar-footer .group {
            padding: 0.75rem !important;
            display: flex !important;
            justify-content: center !important;
        }

        .sidebar-collapsed #sidebar-footer .flex-shrink-0 {
            margin-right: 0 !important;
        }

        /* Custom Scrollbar */
        .custom-scrollbar::-webkit-scrollbar { width: 4px; }
        .custom-scrollbar::-webkit-scrollbar-thumb { background: rgba(0, 0, 0, 0.05); border-radius: 10px; }

        /* RTL parity: keep icon/text spacing identical to LTR rhythm in sidebar/menu clusters */
        [dir="rtl"] #sidebar-nav ul li a {
            text-align: right;
        }
        [dir="rtl"] #sidebar-nav ul li a i {
            margin-right: 0 !important;
            margin-left: 0.75rem !important;
        }
        [dir="rtl"] #sidebar .space-x-1 > :not([hidden]) ~ :not([hidden]),
        [dir="rtl"] #sidebar .space-x-2 > :not([hidden]) ~ :not([hidden]),
        [dir="rtl"] #sidebar .space-x-3 > :not([hidden]) ~ :not([hidden]),
        [dir="rtl"] #sidebar .space-x-4 > :not([hidden]) ~ :not([hidden]) {
            --tw-space-x-reverse: 1;
        }
        [dir="rtl"] #main-header .space-x-1 > :not([hidden]) ~ :not([hidden]),
        [dir="rtl"] #main-header .space-x-2 > :not([hidden]) ~ :not([hidden]),
        [dir="rtl"] #main-header .space-x-3 > :not([hidden]) ~ :not([hidden]),
        [dir="rtl"] #main-header .space-x-4 > :not([hidden]) ~ :not([hidden]),
        [dir="rtl"] #main-content .space-x-1 > :not([hidden]) ~ :not([hidden]),
        [dir="rtl"] #main-content .space-x-2 > :not([hidden]) ~ :not([hidden]),
        [dir="rtl"] #main-content .space-x-3 > :not([hidden]) ~ :not([hidden]),
        [dir="rtl"] #main-content .space-x-4 > :not([hidden]) ~ :not([hidden]),
        [dir="rtl"] #main-content .space-x-5 > :not([hidden]) ~ :not([hidden]),
        [dir="rtl"] #main-content .space-x-6 > :not([hidden]) ~ :not([hidden]) {
            --tw-space-x-reverse: 1;
        }
        [dir="rtl"] button i.mr-1,
        [dir="rtl"] a i.mr-1 { margin-right: 0 !important; margin-left: 0.25rem !important; }
        [dir="rtl"] button i.mr-2,
        [dir="rtl"] a i.mr-2 { margin-right: 0 !important; margin-left: 0.5rem !important; }
        [dir="rtl"] button i.mr-3,
        [dir="rtl"] a i.mr-3 { margin-right: 0 !important; margin-left: 0.75rem !important; }
        [dir="rtl"] button i.mr-4,
        [dir="rtl"] a i.mr-4 { margin-right: 0 !important; margin-left: 1rem !important; }
        [dir="rtl"] #main-header i.mr-1, [dir="rtl"] #main-content i.mr-1 { margin-right: 0 !important; margin-left: 0.25rem !important; }
        [dir="rtl"] #main-header i.mr-2, [dir="rtl"] #main-content i.mr-2 { margin-right: 0 !important; margin-left: 0.5rem !important; }
        [dir="rtl"] #main-header i.mr-3, [dir="rtl"] #main-content i.mr-3 { margin-right: 0 !important; margin-left: 0.75rem !important; }
        [dir="rtl"] #main-header i.mr-4, [dir="rtl"] #main-content i.mr-4 { margin-right: 0 !important; margin-left: 1rem !important; }
        [dir="rtl"] #main-header i.mr-5, [dir="rtl"] #main-content i.mr-5 { margin-right: 0 !important; margin-left: 1.25rem !important; }
        [dir="rtl"] #main-header i.mr-6, [dir="rtl"] #main-content i.mr-6 { margin-right: 0 !important; margin-left: 1.5rem !important; }
        /* Keep technical fields LTR in RTL locales */
        [dir="rtl"] input[type="tel"],
        [dir="rtl"] input[type="email"],
        [dir="rtl"] input[type="url"],
        [dir="rtl"] input[data-force-ltr="1"],
        [dir="rtl"] textarea[data-force-ltr="1"] {
            direction: ltr !important;
            text-align: left !important;
            unicode-bidi: plaintext;
        }
    </style>
</head>
<body class="bg-gray-50 min-h-screen">
    <!-- Toast Notification System -->
    <?= view('components/toast') ?>  
    <div class="flex min-h-screen">
        <!-- Enhanced Sidebar with Glassmorphism -->
        <aside class="w-56 backdrop-blur-xl bg-white/80 border-r border-white/30 shadow-2xl shadow-blue-500/10 z-40 transition-all duration-300 transform flex-shrink-0 lg:translate-x-0 -translate-x-full flex flex-col no-transition" id="sidebar">
        <script>
            // Immediate state restoration to prevent FOUS (Flash of Uncollapsed Sidebar)
            (function() {
                const wasCollapsed = localStorage.getItem('sidebar-collapsed') === 'true';
                const isMobile = window.innerWidth < 1024;
                const sidebar = document.getElementById('sidebar');
                if (wasCollapsed && !isMobile) {
                    sidebar.classList.add('sidebar-collapsed');
                }
            })();
        </script>
        <!-- Enhanced Sidebar Header -->
        <div class="px-2 pb-2  pt-3 border-b border-white/30" id="sidebar-header">
            <div class="flex items-center">
                <a href="<?= base_url('dashboard') ?>" class="flex items-center space-x-3 group cursor-pointer hover:bg-white/30 rounded-xl p-2 -m-2 transition-all duration-200">
                    <div class="relative group">
                            <div class="absolute inset-0 bg-gradient-to-br from-blue-500 to-purple-600 rounded-xl blur-lg opacity-75 group-hover:opacity-100 transition-opacity duration-200"></div>
                            <div class="relative w-10 h-10 bg-gradient-to-br from-blue-500 to-purple-600 rounded-xl flex items-center justify-center shadow-xl group-hover:scale-105 group-hover:rotate-2 transition-all duration-200">
                            <?php if (!empty($clinic['logo_path'])): ?>
                                <?php 
                                    $logoSrc = (strpos($clinic['logo_path'], 'http://') === 0 || strpos($clinic['logo_path'], 'https://') === 0) 
                                        ? $clinic['logo_path'] 
                                        : base_url(ltrim($clinic['logo_path'], '/'));
                                ?>
                                <img src="<?= esc($logoSrc) ?>" alt="<?= esc($clinic['name']) ?>" class="w-full h-full object-contain">
                            <?php else: ?>
                                <i class="fas fa-tooth text-white text-lg"></i>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div id="sidebar-title" class="min-w-0 flex-shrink-0">
                        <h1 class="text-lg font-black text-gray-900 whitespace-nowrap"><?= esc($clinic['name']) ?></h1>
                        <div class="flex items-center space-x-1">
                            <div class="w-1.5 h-1.5 bg-green-500 rounded-full animate-pulse"></div>
                            <p class="text-xs text-gray-500 font-medium whitespace-nowrap"><?= esc($clinic['tagline']) ?></p>
                        </div>
                    </div>
                </a>
            </div>
        </div>

        <!-- Enhanced Sidebar Navigation -->
        <nav class="flex-1" id="sidebar-nav">
            <!-- Main Menu -->
            <div class="p-4">
                <div class="flex items-center space-x-2 mb-4">
                    <div class="w-2 h-2 bg-gradient-to-r from-blue-500 to-purple-600 rounded-full animate-pulse"></div>
                    <h3 class="text-xs font-bold text-gray-600 uppercase tracking-wider"><?= lang('UI.mainMenu') ?></h3>
                </div>
                <ul class="space-y-2">
                    <?php 
                    // Simple static menu - no authentication required
                    $userMenu = [];
                    $menu = [
                            'dashboard' => [
                                'title' => lang('UI.menuDashboard'),
                                'url' => 'dashboard',
                                'icon' => 'fas fa-tachometer-alt',
                                'color' => 'blue',
                            ],
                            'patients' => [
                                'title' => lang('UI.menuPatients'),
                                'url' => 'patients',
                                'icon' => 'fas fa-users',
                                'color' => 'emerald',
                            ],
                            'examinations' => [
                                'title' => lang('UI.menuExaminations'),
                                'url' => 'examinations',
                                'icon' => 'fas fa-stethoscope',
                                'color' => 'orange',
                            ],
                            'appointments' => [
                                'title' => lang('UI.menuAppointments'),
                                'url' => 'appointments',
                                'icon' => 'fas fa-calendar-alt',
                                'color' => 'purple',
                            ],
                            'treatments' => [
                                'title' => lang('UI.menuTreatments'),
                                'url' => 'treatments',
                                'icon' => 'fas fa-tooth',
                                'color' => 'pink',
                            ],
                            'prescriptions' => [
                                'title' => lang('UI.menuPrescriptions'),
                                'url' => 'prescriptions',
                                'icon' => 'fas fa-prescription-bottle-alt',
                                'color' => 'indigo',
                            ],
                            'finance' => [
                                'title' => lang('UI.menuFinance'),
                                'url' => 'finance',
                                'icon' => 'fas fa-dollar-sign',
                                'color' => 'green',
                            ],
                            'reports' => [
                                'title' => lang('UI.menuReports'),
                                'url' => 'reports',
                                'icon' => 'fas fa-chart-bar',
                                'color' => 'amber',
                            ],
                            'inventory' => [
                                'title' => lang('UI.menuInventory'),
                                'url' => 'inventory',
                                'icon' => 'fas fa-boxes',
                                'color' => 'teal',
                            ],
                            'users' => [
                                'title' => lang('UI.menuUserManagement'),
                                'url' => 'users',
                                'icon' => 'fas fa-users-cog',
                                'color' => 'violet',
                            ],
                            'roles' => [
                                'title' => lang('UI.menuRoleManagement'),
                                'url' => 'roles',
                                'icon' => 'fas fa-user-tag',
                                'color' => 'indigo',
                            ],
                            'activity_log' => [
                                'title' => lang('UI.menuActivityLog'),
                                'url' => 'activity-log',
                                'icon' => 'fas fa-history',
                                'color' => 'teal',
                            ],
                            'settings' => [
                                'title' => lang('UI.menuSettings'),
                                'url' => 'settings',
                                'icon' => 'fas fa-cog',
                                'color' => 'gray',
                            ]
                        ];

                    // Permission mapping for menu items
                    $permissionMap = [
                        'dashboard' => ['module' => 'dashboard', 'action' => 'view'],
                        'patients' => ['module' => 'patients', 'action' => 'view'],
                        'examinations' => ['module' => 'examinations', 'action' => 'view'],
                        'appointments' => ['module' => 'appointments', 'action' => 'view'],
                        'treatments' => ['module' => 'treatments', 'action' => 'view'],
                        'prescriptions' => ['module' => 'prescriptions', 'action' => 'view'],
                        'finance' => ['module' => 'finance', 'action' => 'view'],
                        'reports' => ['module' => 'reports', 'action' => 'view'],
                        'inventory' => ['module' => 'inventory', 'action' => 'view'],
                        'users' => ['module' => 'users', 'action' => 'view'],
                        'roles' => ['module' => 'users', 'action' => 'roles'], // Roles are part of user management
                        'activity_log' => ['module' => 'activity_log', 'action' => 'view'],
                        'settings' => ['module' => 'settings', 'action' => 'view']
                    ];

                    // Display menu items with permission filtering
                    foreach ($menu as $key => $item): 
                        // Check permission for this menu item
                        $permission = $permissionMap[$key] ?? ['module' => $key, 'action' => 'view'];
                        if (!has_permission($permission['module'], $permission['action'])) {
                            continue; // Skip this menu item if user doesn't have permission
                        }

                        $isActive = (strpos(uri_string(), $item['url']) === 0);
                        $colorClasses = [
                            'blue' => ['from-blue-500 to-blue-600', 'shadow-blue-500/25', 'from-blue-50 to-cyan-50', 'text-blue-800', 'shadow-blue-500/10', 'text-blue-600', 'text-blue-700'],
                            'emerald' => ['from-emerald-500 to-emerald-600', 'shadow-emerald-500/25', 'from-emerald-50 to-green-50', 'text-emerald-800', 'shadow-emerald-500/10', 'text-emerald-600', 'text-emerald-700'],
                            'orange' => ['from-orange-500 to-orange-600', 'shadow-orange-500/25', 'from-orange-50 to-amber-50', 'text-orange-800', 'shadow-orange-500/10', 'text-orange-600', 'text-orange-700'],
                            'purple' => ['from-purple-500 to-purple-600', 'shadow-purple-500/25', 'from-purple-50 to-violet-50', 'text-purple-800', 'shadow-purple-500/10', 'text-purple-600', 'text-purple-700'],
                            'pink' => ['from-pink-500 to-pink-600', 'shadow-pink-500/25', 'from-pink-50 to-rose-50', 'text-pink-800', 'shadow-pink-500/10', 'text-pink-600', 'text-pink-700'],
                            'indigo' => ['from-indigo-500 to-indigo-600', 'shadow-indigo-500/25', 'from-indigo-50 to-blue-50', 'text-indigo-800', 'shadow-indigo-500/10', 'text-indigo-600', 'text-indigo-700'],
                            'green' => ['from-green-500 to-green-600', 'shadow-green-500/25', 'from-green-50 to-emerald-50', 'text-green-800', 'shadow-green-500/10', 'text-green-600', 'text-green-700'],
                            'amber' => ['from-amber-500 to-amber-600', 'shadow-amber-500/25', 'from-amber-50 to-yellow-50', 'text-amber-800', 'shadow-amber-500/10', 'text-amber-600', 'text-amber-700'],
                            'teal' => ['from-teal-500 to-teal-600', 'shadow-teal-500/25', 'from-teal-50 to-cyan-50', 'text-teal-800', 'shadow-teal-500/10', 'text-teal-600', 'text-teal-700'],
                            'violet' => ['from-violet-500 to-violet-600', 'shadow-violet-500/25', 'from-violet-50 to-purple-50', 'text-violet-800', 'shadow-violet-500/10', 'text-violet-600', 'text-violet-700'],
                            'gray' => ['from-gray-500 to-gray-600', 'shadow-gray-500/25', 'from-gray-50 to-slate-50', 'text-gray-800', 'shadow-gray-500/10', 'text-gray-600', 'text-gray-700']
                        ];
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
        </nav>

        <!-- Enhanced Sidebar Footer -->
        <div class="p-4 border-t border-white/30 backdrop-blur-xl bg-white/80" id="sidebar-footer">
            <a href="<?= base_url('profile') ?>" class="group relative cursor-pointer hover:bg-white/50 rounded-xl p-3 transition-all duration-200 hover:shadow-lg hover:shadow-blue-500/10 block">
                <div class="absolute inset-0 bg-gradient-to-r from-blue-500/10 to-purple-500/10 rounded-xl blur opacity-0 group-hover:opacity-100 transition-opacity duration-200"></div>
                <div class="flex items-center space-x-3 relative z-10">
                    <div class="relative">
                        <div class="absolute inset-0 bg-gradient-to-br from-blue-500 to-purple-600 rounded-full blur-lg opacity-75 group-hover:opacity-100 transition-opacity duration-200"></div>
                        <div class="relative w-10 h-10 bg-gradient-to-br from-blue-500 to-purple-600 rounded-full flex items-center justify-center shadow-lg group-hover:scale-105 group-hover:rotate-2 transition-all duration-200">
                            <i class="fas fa-user text-white text-sm"></i>
                        </div>
                    </div>
                    <div class="flex-1 min-w-0">
                        <div class="text-sm font-bold text-gray-900 truncate">
                            <?php 
                            $user = $user ?? null;
                            $displayName = esc($clinic['name']);
                            if (isset($user) and $user) {
                                // Try first_name + last_name combination
                                if (property_exists($user, 'first_name') and property_exists($user, 'last_name') and 
                                    !empty($user->first_name) and !empty($user->last_name)) {
                                    $displayName = esc(trim($user->first_name . ' ' . $user->last_name));
                                }
                                // Fallback to email
                                elseif (property_exists($user, 'email') and !empty($user->email)) {
                                    $displayName = esc($user->email);
                                }
                            }
                            echo $displayName;
                            ?>
                        </div>
                        <div class="flex items-center space-x-1">
                            <div class="w-1.5 h-1.5 bg-green-500 rounded-full animate-pulse"></div>
                            <div class="text-xs text-gray-500 font-medium">
                                <?php 
                                if ($user && isset($user_groups) && !empty($user_groups)) {
                                    $groupNames = array_map(function($group) { return $group->name; }, $user_groups);
                                    echo esc(ucfirst(implode(', ', $groupNames)));
                                } else {
                                    echo 'System Admin';
                                }
                                ?>
                            </div>
                        </div>
                    </div>
                    <i class="fas fa-external-link-alt text-gray-400 text-xs group-hover:text-gray-600 transition-colors duration-200"></i>
                </div>
            </a>
        </div>
    </aside>

    <!-- Main Content Area -->
    <div class="flex-1 flex flex-col min-h-screen transition-all duration-300 min-w-0" id="main-content">
        
        <!-- Smart Top Navigation Bar -->
        <header class="bg-white/90 backdrop-blur-xl border-b border-white/40 shadow-xl shadow-blue-500/10 px-6 py-2 transition-all duration-300 relative z-50" id="main-header">
            <!-- Animated Background Elements -->
            <div class="absolute inset-0 overflow-hidden pointer-events-none">
                <div class="absolute -top-10 -right-10 w-32 h-32 bg-gradient-to-br from-blue-400/5 to-purple-600/5 rounded-full blur-2xl animate-pulse"></div>
                <div class="absolute -bottom-10 -left-10 w-32 h-32 bg-gradient-to-br from-emerald-400/5 to-cyan-600/5 rounded-full blur-2xl animate-pulse delay-1000"></div>
            </div>
            
            <div class="relative z-10 flex items-center justify-between transition-all duration-300" id="header-content">
                <!-- Left Section: Sidebar Toggle + Page Title -->
                <div class="flex items-center space-x-3 flex-1" id="header-left">
                    <button class="group relative p-2.5 text-gray-600 hover:text-gray-800 transition-all duration-200 rounded-xl hover:bg-white/50 hover:shadow-lg hover:shadow-blue-500/10" onclick="toggleSidebar()" title="Toggle Sidebar (Ctrl+B)">
                        <div class="absolute inset-0 bg-gradient-to-r from-blue-500/10 to-indigo-500/10 rounded-xl blur opacity-0 group-hover:opacity-100 transition-opacity duration-200"></div>
                        <i class="fas fa-bars text-lg relative z-10 group-hover:text-blue-600 transition-colors duration-200"></i>
                    </button>
                    
                    <!-- Page Title -->
                    <div class="flex items-center space-x-4">
                        <h2 class="text-xl font-bold text-gray-900 leading-tight"><?= $pageTitle ?? $title ?? esc($clinic['name']) ?></h2>
                    </div>
                </div>
                
                <!-- Right Section: User Info + Actions -->
                <div class="flex items-center space-x-4" id="header-right">
                    <?php $activeLocale = strtolower((string) ($locale ?? 'en')); ?>
                    <?php $returnToUrl = current_url(true)->__toString(); ?>
                    <div class="hidden sm:flex items-center">
                        <div class="group flex items-center gap-2 rounded-2xl border border-white/50 bg-white/80 p-1.5 shadow-xl shadow-blue-500/10 backdrop-blur-xl transition-all duration-200 hover:shadow-blue-500/15">
                            <div class="flex h-11 items-center rounded-xl bg-gradient-to-br from-slate-50 to-blue-50 px-3 text-gray-600">
                                <span class="inline-flex h-7 w-7 items-center justify-center rounded-xl bg-gradient-to-br from-blue-500 to-indigo-600 text-white shadow-md shadow-blue-500/25">
                                    <i class="fas fa-language text-[13px]"></i>
                                </span>
                                <span class="<?= !empty($is_rtl) ? 'mr-2' : 'ml-2' ?> text-[11px] font-black uppercase tracking-[0.18em] text-gray-500">
                                    <?= lang('UI.settingsLanguage') ?>
                                </span>
                            </div>
                            <div class="flex items-center rounded-xl bg-slate-100/90 p-1">
                                <a href="<?= base_url('language/switch/en?return_to=' . urlencode($returnToUrl)) ?>"
                                        class="inline-flex items-center rounded-lg px-3 py-2 text-xs font-bold transition-all duration-200 <?= $activeLocale === 'en' ? 'bg-gradient-to-r from-blue-600 to-indigo-600 text-white shadow-lg shadow-blue-500/25' : 'text-gray-600 hover:bg-white hover:text-gray-900 hover:shadow-sm' ?>">
                                    <span class="inline-flex h-6 w-6 items-center justify-center rounded-full <?= $activeLocale === 'en' ? 'bg-white/20 text-white' : 'bg-white text-blue-600 shadow-sm' ?>">EN</span>
                                    <span class="<?= !empty($is_rtl) ? 'mr-2' : 'ml-2' ?>"><?= lang('UI.settingsLanguageEn') ?></span>
                                    <?php if ($activeLocale === 'en'): ?>
                                        <span class="<?= !empty($is_rtl) ? 'mr-2' : 'ml-2' ?> inline-flex h-2 w-2 rounded-full bg-white/80"></span>
                                    <?php endif; ?>
                                </a>
                                <a href="<?= base_url('language/switch/ar?return_to=' . urlencode($returnToUrl)) ?>"
                                        class="inline-flex items-center rounded-lg px-3 py-2 text-xs font-bold transition-all duration-200 <?= $activeLocale === 'ar' ? 'bg-gradient-to-r from-blue-600 to-indigo-600 text-white shadow-lg shadow-blue-500/25' : 'text-gray-600 hover:bg-white hover:text-gray-900 hover:shadow-sm' ?>">
                                    <span class="inline-flex h-6 w-6 items-center justify-center rounded-full <?= $activeLocale === 'ar' ? 'bg-white/20 text-white' : 'bg-white text-blue-600 shadow-sm' ?>">AR</span>
                                    <span class="<?= !empty($is_rtl) ? 'mr-2' : 'ml-2' ?>"><?= lang('UI.settingsLanguageAr') ?></span>
                                    <?php if ($activeLocale === 'ar'): ?>
                                        <span class="<?= !empty($is_rtl) ? 'mr-2' : 'ml-2' ?> inline-flex h-2 w-2 rounded-full bg-white/80"></span>
                                    <?php endif; ?>
                                </a>
                            </div>
                        </div>
                    </div>
                    <div class="sm:hidden">
                        <a href="<?= base_url('language/switch/' . ($activeLocale === 'ar' ? 'en' : 'ar') . '?return_to=' . urlencode($returnToUrl)) ?>"
                                class="inline-flex h-11 items-center rounded-2xl border border-white/50 bg-white/80 px-3 text-xs font-bold text-gray-700 shadow-xl shadow-blue-500/10 backdrop-blur-xl transition-all duration-200 hover:bg-white hover:text-gray-900">
                            <span class="inline-flex h-7 w-7 items-center justify-center rounded-xl bg-gradient-to-br from-blue-500 to-indigo-600 text-white shadow-md shadow-blue-500/25">
                                <i class="fas fa-language text-[13px]"></i>
                            </span>
                            <span class="<?= !empty($is_rtl) ? 'mr-2' : 'ml-2' ?>"><?= $activeLocale === 'ar' ? lang('UI.settingsLanguageEn') : lang('UI.settingsLanguageAr') ?></span>
                        </a>
                    </div>
                    <?php if (session()->get('is_impersonating')): ?>
                    <form action="<?= base_url('controlplane/impersonation/exit') ?>" method="post" class="flex items-center space-x-2 rounded-xl border border-rose-200 bg-rose-50 px-3 py-1.5">
                        <?= csrf_field() ?>
                        <span class="text-xs font-semibold text-rose-600">Impersonating</span>
                        <button type="submit" class="text-xs font-semibold text-rose-700 hover:text-rose-800">
                            Exit
                        </button>
                    </form>
                    <?php endif; ?>
                    <!-- Clinic Switcher (Moved here) -->
                    <?php if (isset($user_memberships) && count($user_memberships) > 1): ?>
                    <div class="relative group" id="clinic-switcher">
                        <button onclick="toggleClinicSwitcher()" class="relative flex items-center justify-center w-10 h-10 bg-gradient-to-br from-indigo-50 to-blue-50 hover:from-indigo-50 hover:to-indigo-100 border border-indigo-200 hover:border-indigo-300 text-indigo-600 hover:text-indigo-700 rounded-xl transition-all duration-200 hover:scale-105 hover:shadow-md hover:shadow-indigo-500/10">
                            <i class="fas fa-hospital text-sm"></i>
                        </button>
                        
                        <!-- Tooltip -->
                        <div class="absolute top-full mt-2 left-1/2 transform -translate-x-1/2 bg-gray-900 text-white text-xs font-medium px-2 py-1 rounded-lg opacity-0 group-hover:opacity-100 transition-opacity duration-200 whitespace-nowrap pointer-events-none z-50">
                            <?= lang('UI.switchClinic') ?> (<?= esc($clinic['name']) ?>)
                            <div class="absolute -top-1 left-1/2 transform -translate-x-1/2 w-2 h-2 bg-gray-900 rotate-45"></div>
                        </div>

                        <div id="clinicSwitcherMenu" class="absolute right-0 mt-3 w-64 bg-white rounded-2xl shadow-2xl border border-gray-100 hidden z-50 overflow-hidden transform origin-top-right transition-all">
                            <div class="p-4 border-b border-gray-50 bg-gray-50/50">
                                <p class="text-[10px] font-black text-gray-400 uppercase tracking-[0.2em]"><?= lang('UI.switchClinic') ?></p>
                            </div>
                            <div class="p-2 max-h-64 overflow-y-auto">
                                <?php foreach ($user_memberships as $m): ?>
                                    <a href="<?= base_url('clinic/switch/' . $m['clinic_id']) ?>" class="flex items-center justify-between px-3 py-3 rounded-xl transition-all mb-1 <?= ($m['clinic_id'] == session()->get('active_clinic_id')) ? 'bg-blue-50 text-blue-700' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' ?>">
                                        <div class="flex items-center space-x-3 overflow-hidden">
                                            <div class="w-9 h-9 rounded-xl flex items-center justify-center flex-shrink-0 <?= ($m['clinic_id'] == session()->get('active_clinic_id')) ? 'bg-gradient-to-br from-blue-500 to-indigo-600 text-white shadow-lg shadow-blue-500/30' : 'bg-gray-100 text-gray-400' ?>">
                                                <i class="fas fa-hospital text-sm"></i>
                                            </div>
                                            <div class="flex flex-col overflow-hidden">
                                                <span class="truncate font-bold text-sm"><?= esc($m['clinic_name']) ?></span>
                                                <span class="text-[10px] opacity-60"><?= esc($m['clinic_slug'] ?? ('ID ' . $m['clinic_id'])) ?></span>
                                                <span class="text-[10px] opacity-60 block"><?= ($m['clinic_id'] == session()->get('active_clinic_id')) ? 'Active Now' : 'Click to switch' ?></span>
                                            </div>
                                        </div>
                                        <?php if ($m['clinic_id'] == session()->get('active_clinic_id')): ?>
                                            <div class="w-5 h-5 bg-blue-500 rounded-full flex items-center justify-center flex-shrink-0 shadow-sm">
                                                <i class="fas fa-check text-white text-[8px]"></i>
                                            </div>
                                        <?php endif; ?>
                                    </a>
                                <?php endforeach; ?>
                            </div>
                            <div class="p-3 border-t border-gray-50 bg-gray-50/30 text-center">
                                <a href="<?= base_url('clinic/select') ?>" class="inline-flex items-center space-x-1 text-[11px] text-blue-600 hover:text-blue-700 font-bold uppercase tracking-wider transition-colors">
                                    <i class="fas fa-th-large text-[10px]"></i>
                                    <span><?= lang('UI.allClinics') ?></span>
                                </a>
                            </div>
                        </div>
                    </div>
                    <div class="h-8 w-px bg-gray-200 mx-2"></div>
                    <?php endif; ?>

                    <!-- System Action Icons -->
                    <div class="lg:flex items-center space-x-2 hidden">
                        <!-- New Patient -->
                        <div class="relative group">
                            <a href="<?= base_url('patients/create') ?>" class="relative flex items-center justify-center w-10 h-10 bg-gradient-to-br from-blue-50 to-cyan-50 hover:from-blue-50 hover:to-blue-100 border border-blue-200 hover:border-blue-300 text-blue-600 hover:text-blue-700 rounded-xl transition-all duration-200 hover:scale-105 hover:shadow-md hover:shadow-blue-500/10">
                                <i class="fas fa-user-plus text-sm"></i>
                            </a>
                            <div class="absolute top-full mt-2 left-1/2 transform -translate-x-1/2 bg-gray-900 text-white text-xs font-medium px-2 py-1 rounded-lg opacity-0 group-hover:opacity-100 transition-opacity duration-200 whitespace-nowrap pointer-events-none z-50">
                                <?= lang('UI.newPatient') ?>
                                <div class="absolute -top-1 left-1/2 transform -translate-x-1/2 w-2 h-2 bg-gray-900 rotate-45"></div>
                            </div>
                        </div>
                        
                        <!-- New Appointment -->
                        <div class="relative group">
                            <a href="<?= base_url('appointments/create') ?>" class="relative flex items-center justify-center w-10 h-10 bg-gradient-to-br from-purple-50 to-violet-50 hover:from-purple-50 hover:to-purple-100 border border-purple-200 hover:border-purple-300 text-purple-600 hover:text-purple-700 rounded-xl transition-all duration-200 hover:scale-105 hover:shadow-md hover:shadow-purple-500/10">
                                <i class="fas fa-calendar-plus text-sm"></i>
                            </a>
                            <div class="absolute top-full mt-2 left-1/2 transform -translate-x-1/2 bg-gray-900 text-white text-xs font-medium px-2 py-1 rounded-lg opacity-0 group-hover:opacity-100 transition-opacity duration-200 whitespace-nowrap pointer-events-none z-50">
                                <?= lang('UI.newAppointment') ?>
                                <div class="absolute -top-1 left-1/2 transform -translate-x-1/2 w-2 h-2 bg-gray-900 rotate-45"></div>
                            </div>
                        </div>
                        
                        <!-- New Examination -->
                        <div class="relative group">
                            <a href="<?= base_url('examinations/create') ?>" class="relative flex items-center justify-center w-10 h-10 bg-gradient-to-br from-emerald-50 to-green-50 hover:from-emerald-50 hover:to-emerald-100 border border-emerald-200 hover:border-emerald-300 text-emerald-600 hover:text-emerald-700 rounded-xl transition-all duration-200 hover:scale-105 hover:shadow-md hover:shadow-emerald-500/10">
                                <i class="fas fa-stethoscope text-sm"></i>
                            </a>
                            <div class="absolute top-full mt-2 left-1/2 transform -translate-x-1/2 bg-gray-900 text-white text-xs font-medium px-2 py-1 rounded-lg opacity-0 group-hover:opacity-100 transition-opacity duration-200 whitespace-nowrap pointer-events-none z-50">
                                <?= lang('UI.newExamination') ?>
                                <div class="absolute -top-1 left-1/2 transform -translate-x-1/2 w-2 h-2 bg-gray-900 rotate-45"></div>
                            </div>
                        </div>
                        
                        <!-- Notifications -->
                        <div class="relative group">
                            <button class="relative flex items-center justify-center w-10 h-10 bg-gradient-to-br from-amber-50 to-orange-50 hover:from-amber-50 hover:to-amber-100 border border-amber-200 hover:border-amber-300 text-amber-600 hover:text-amber-700 rounded-xl transition-all duration-200 hover:scale-105 hover:shadow-md hover:shadow-amber-500/10" onclick="toggleNotifications()">
                                <i class="fas fa-bell text-sm"></i>
                                <span id="notification-count" class="absolute -top-1 -right-1 bg-red-500 text-white text-xs rounded-full w-4 h-4 flex items-center justify-center font-bold shadow-sm hidden text-[10px]">0</span>
                            </button>
                            <div class="absolute top-full mt-2 left-1/2 transform -translate-x-1/2 bg-gray-900 text-white text-xs font-medium px-2 py-1 rounded-lg opacity-0 group-hover:opacity-100 transition-opacity duration-200 whitespace-nowrap pointer-events-none z-50">
                                <?= lang('UI.notifications') ?>
                                <div class="absolute -top-1 left-1/2 transform -translate-x-1/2 w-2 h-2 bg-gray-900 rotate-45"></div>
                            </div>
                        </div>
                        
                    </div>
                    
                    <!-- User Profile Dropdown -->
                    <div class="relative group">
                        <button onclick="toggleUserMenu()" class="relative flex items-center justify-center w-10 h-10 bg-gradient-to-br from-slate-50 to-gray-50 hover:from-slate-100 hover:to-gray-100 border border-slate-200 hover:border-slate-300 text-slate-600 hover:text-slate-700 rounded-xl transition-all duration-200 hover:scale-105 hover:shadow-md hover:shadow-slate-500/10">
                            <i class="fas fa-user text-sm"></i>
                        </button>
                        <div class="absolute top-full mt-2 left-1/2 transform -translate-x-1/2 bg-gray-900 text-white text-xs font-medium px-2 py-1 rounded-lg opacity-0 group-hover:opacity-100 transition-opacity duration-200 whitespace-nowrap pointer-events-none z-50">
                            <?= lang('UI.profileMenu') ?>
                            <div class="absolute -top-1 left-1/2 transform -translate-x-1/2 w-2 h-2 bg-gray-900 rotate-45"></div>
                        </div>
                        
                        <!-- User Menu Dropdown -->
                        <div id="userMenu" class="absolute right-0 mt-2 w-64 bg-white rounded-xl shadow-xl border border-gray-200 hidden z-50">
                            <div class="p-4 border-b border-gray-100">
                                <div class="flex items-center space-x-3">
                                    <div class="w-12 h-12 bg-gradient-to-br from-blue-500 to-purple-600 rounded-xl flex items-center justify-center">
                                        <i class="fas fa-user text-white text-lg"></i>
                                    </div>
                                    <div>
                                        <div class="font-semibold text-gray-900">
                                            <?php 
                                            $displayName = 'User';
                                            if (isset($user) and $user) {
                                                // Try first_name + last_name combination
                                                if (property_exists($user, 'first_name') and property_exists($user, 'last_name') and 
                                                    !empty($user->first_name) and !empty($user->last_name)) {
                                                    $displayName = htmlspecialchars(trim($user->first_name . ' ' . $user->last_name));
                                                }
                                                // Fallback to email
                                                elseif (property_exists($user, 'email') and !empty($user->email)) {
                                                    $displayName = htmlspecialchars($user->email);
                                                }
                                            }
                                            echo $displayName;
                                            ?>
                                        </div>
                                        <div class="text-sm text-gray-500">
                                            <?= isset($user) && $user && property_exists($user, 'email') ? htmlspecialchars($user->email) : 'user@example.com' ?>
                                        </div>
                                        <div class="text-xs text-blue-600 bg-blue-50 px-2 py-1 rounded-full inline-block mt-1">
                                            <?= isset($user_groups) && !empty($user_groups) ? $user_groups[0]->name : 'User' ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="p-2">
                                <a href="<?= base_url('profile') ?>" class="flex items-center space-x-3 px-3 py-2.5 text-gray-700 hover:bg-gray-50 rounded-lg transition-colors duration-200">
                                    <i class="fas fa-user-cog text-gray-500 w-5"></i>
                                    <span class="font-medium"><?= lang('UI.profileSettings') ?></span>
                                </a>
                                <a href="<?= base_url('settings') ?>" class="flex items-center space-x-3 px-3 py-2.5 text-gray-700 hover:bg-gray-50 rounded-lg transition-colors duration-200">
                                    <i class="fas fa-cog text-gray-500 w-5"></i>
                                    <span class="font-medium"><?= lang('UI.systemSettings') ?></span>
                                </a>
                                <a href="<?= base_url('clinic/select') ?>" class="flex items-center space-x-3 px-3 py-2.5 text-gray-700 hover:bg-gray-50 rounded-lg transition-colors duration-200">
                                    <i class="fas fa-exchange-alt text-gray-500 w-5"></i>
                                    <span class="font-medium"><?= lang('UI.switchClinic') ?></span>
                                </a>
                                
                                <?php if (!empty($is_super_admin) && empty($global_mode)): ?>
                                    <div class="border-t border-gray-100 my-2"></div>
                                    <form action="<?= base_url('controlplane/enter') ?>" method="post">
                                        <?= csrf_field() ?>
                                        <button type="submit" class="w-full flex items-center space-x-3 px-3 py-2.5 text-indigo-700 hover:bg-indigo-50 rounded-lg transition-colors duration-200 text-left">
                                            <i class="fas fa-rocket text-indigo-500 w-5"></i>
                                            <span class="font-medium"><?= lang('UI.enterControlPlane') ?></span>
                                        </button>
                                    </form>
                                <?php endif; ?>

                                <div class="border-t border-gray-100 my-2"></div>
                                <a href="<?= base_url('auth/logout') ?>" class="flex items-center space-x-3 px-3 py-2.5 text-red-600 hover:bg-red-50 rounded-lg transition-colors duration-200">
                                    <i class="fas fa-sign-out-alt text-red-500 w-5"></i>
                                    <span class="font-medium"><?= lang('UI.signOut') ?></span>
                                </a>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Logout Button -->
                    <div class="relative group ml-2">
                        <a href="<?= base_url('auth/logout') ?>" class="relative flex items-center justify-center w-10 h-10 bg-gradient-to-br from-red-50 to-rose-50 hover:from-red-50 hover:to-red-100 border border-red-200 hover:border-red-300 text-red-600 hover:text-red-700 rounded-xl transition-all duration-200 hover:scale-105 hover:shadow-md hover:shadow-red-500/10">
                            <i class="fas fa-sign-out-alt text-sm"></i>
                        </a>
                        <div class="absolute top-full mt-2 left-1/2 transform -translate-x-1/2 bg-gray-900 text-white text-xs font-medium px-2 py-1 rounded-lg opacity-0 group-hover:opacity-100 transition-opacity duration-200 whitespace-nowrap pointer-events-none z-50">
                            <?= lang('UI.logout') ?>
                            <div class="absolute -top-1 left-1/2 transform -translate-x-1/2 w-2 h-2 bg-gray-900 rotate-45"></div>
                        </div>
                    </div>
                </div>
            </div>
        </header>

        <!-- Main Content -->
        <main class="flex-1 bg-gray-50 p-6">
            <!-- Flash Messages -->
            <?php if (session()->getFlashdata('message')): ?>
                <div class="mb-4 p-4 bg-green-100 border border-green-400 text-green-700 rounded-lg">
                    <?= session()->getFlashdata('message') ?>
                </div>
            <?php endif; ?>
            
            <?php if (session()->getFlashdata('error')): ?>
                <div class="mb-4 p-4 bg-red-100 border border-red-400 text-red-700 rounded-lg">
                    <?= session()->getFlashdata('error') ?>
                </div>
            <?php endif; ?>

            <!-- Page Content -->
            <?= $this->renderSection('content') ?>
        </main>
    </div>

    <!-- Notifications Dropdown - Outside main content to avoid overflow issues -->
    <div id="notifications-dropdown" class="fixed top-20 right-6 w-80 bg-white rounded-xl shadow-2xl border border-gray-200 hidden" style="z-index: 2147483647 !important;">
        <div class="p-4 border-b border-gray-200">
            <div class="flex items-center justify-between">
                <h3 class="text-lg font-bold text-gray-900"><?= lang('UI.notifications') ?></h3>
                <button onclick="markAllAsRead()" class="text-sm text-blue-600 hover:text-blue-800"><?= lang('UI.markAllAsRead') ?></button>
            </div>
        </div>
        <div id="notifications-list" class="max-h-64 overflow-y-auto">
            <!-- Notifications will be loaded here -->
        </div>
        <div class="p-4 border-t border-gray-200">
            <a href="<?= base_url('activity-log') ?>" class="text-sm text-blue-600 hover:text-blue-800"><?= lang('UI.viewAllNotifications') ?></a>
        </div>
    </div>
</div>


<!-- JavaScript -->
<script>
    // Global CSRF configuration
    window.csrfConfig = {
        name: document.querySelector('meta[name="csrf-name"]')?.getAttribute('content') || '<?= csrf_token() ?>',
        header: document.querySelector('meta[name="csrf-header"]')?.getAttribute('content') || '<?= csrf_header() ?>',
        cookieName: '<?= esc(config('Security')->cookieName) ?>'
    };

    // Initialize global hash
    window.csrfHash = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '<?= csrf_hash() ?>';

    // Global CSRF helper functions
    window.getCsrfToken = function() {
        return window.csrfHash;
    };

    window.refreshCsrfToken = function(serverToken) {
        let token = serverToken;
        if (!token) {
            const name = window.csrfConfig.cookieName;
            const value = `; ${document.cookie}`;
            const parts = value.split(`; ${name}=`);
            if (parts.length === 2) {
                token = parts.pop().split(';').shift();
            }
        }

        if (token) {
            window.csrfHash = token;
            const meta = document.querySelector('meta[name="csrf-token"]');
            if (meta) {
                meta.setAttribute('content', token);
                // Also update any hidden CSRF inputs in forms
                document.querySelectorAll(`input[name="${window.csrfConfig.name}"]`).forEach(input => {
                    input.value = token;
                });
            }
            console.log('CSRF token refreshed successfully');
            return token;
        }
        return window.csrfHash;
    };

    function isSafeHttpMethod(method) {
        return ['GET', 'HEAD', 'OPTIONS', 'TRACE'].includes(method);
    }

    function appendCsrfToData(data, name, token, contentType) {
        if (!name || !token) {
            return data;
        }

        if (typeof FormData !== 'undefined' && data instanceof FormData) {
            if (!data.has(name)) {
                data.append(name, token);
            }
            return data;
        }

        if (typeof data === 'string') {
            const encodedName = encodeURIComponent(name);
            const hasToken = new RegExp(`(^|&)${encodedName}=`).test(data);
            if (hasToken) {
                return data;
            }
            if (contentType && contentType.indexOf('application/json') !== -1) {
                try {
                    const parsed = JSON.parse(data);
                    if (parsed && typeof parsed === 'object' && !Array.isArray(parsed)) {
                        parsed[name] = parsed[name] || token;
                        return JSON.stringify(parsed);
                    }
                } catch (e) {
                    return data;
                }
            }
            const separator = data.length ? '&' : '';
            return `${data}${separator}${encodedName}=${encodeURIComponent(token)}`;
        }

        if (data == null) {
            return { [name]: token };
        }

        if (typeof data === 'object') {
            if (typeof data[name] === 'undefined') {
                data[name] = token;
            }
            return data;
        }

        return data;
    }

    if (window.jQuery && $.ajaxPrefilter) {
        $.ajaxPrefilter(function(options, originalOptions, jqXHR) {
            const method = (options.type || options.method || 'GET').toUpperCase();
            if (isSafeHttpMethod(method)) {
                return;
            }

            const name = window.csrfConfig?.name;
            const token = window.getCsrfToken ? window.getCsrfToken() : null;
            if (!name || !token) {
                return;
            }

            const headerName = window.csrfConfig?.header || 'X-CSRF-TOKEN';
            const beforeSend = options.beforeSend;
            options.beforeSend = function(xhr, settings) {
                xhr.setRequestHeader(headerName, token);
                if (beforeSend) {
                    return beforeSend.call(this, xhr, settings);
                }
            };

            if (typeof options.data === 'function') {
                const originalData = options.data;
                options.data = function() {
                    const result = originalData.apply(this, arguments);
                    return appendCsrfToData(result, name, token, options.contentType);
                };
            } else {
                options.data = appendCsrfToData(options.data, name, token, options.contentType);
            }
        });

        $(document).ajaxComplete(function(event, xhr) {
            const headerName = window.csrfConfig?.header || 'X-CSRF-TOKEN';
            let newToken = null;
            try {
                newToken = xhr.getResponseHeader(headerName);
            } catch (e) {
                newToken = null;
            }

            if (!newToken) {
                const json = xhr.responseJSON;
                if (json) {
                    newToken = json.csrf_token || json.csrfToken || json.csrfHash || json.csrf_token_hash;
                } else {
                    const contentType = xhr.getResponseHeader('Content-Type') || '';
                    if (contentType.indexOf('application/json') !== -1 && typeof xhr.responseText === 'string') {
                        try {
                            const parsed = JSON.parse(xhr.responseText);
                            newToken = parsed.csrf_token || parsed.csrfToken || parsed.csrfHash || parsed.csrf_token_hash;
                        } catch (e) {
                            newToken = null;
                        }
                    }
                }
            }

            if (newToken && window.refreshCsrfToken) {
                window.refreshCsrfToken(newToken);
            }
        });
    }

    // Sidebar toggle functionality
    function toggleSidebar() {
        const sidebar = document.getElementById('sidebar');
        const isMobile = window.innerWidth < 1024;
        
        if (isMobile) {
            sidebar.classList.toggle('-translate-x-full');
            sidebar.classList.remove('sidebar-collapsed');
        } else {
            sidebar.classList.toggle('sidebar-collapsed');
            sidebar.classList.remove('-translate-x-full');
            
            // Persist state
            const isCollapsed = sidebar.classList.contains('sidebar-collapsed');
            localStorage.setItem('sidebar-collapsed', isCollapsed);
        }
    }

    // Initialize sidebar state on page load
    document.addEventListener('DOMContentLoaded', function() {
        const sidebar = document.getElementById('sidebar');
        const isMobile = window.innerWidth < 1024;
        
        if (isMobile) {
            sidebar.classList.add('-translate-x-full');
            sidebar.classList.remove('sidebar-collapsed');
        }
        
        // Remove no-transition after initial paint to enable smooth toggling
        requestAnimationFrame(() => {
            setTimeout(() => {
                sidebar.classList.remove('no-transition');
            }, 100);
        });
    });

    // Handle window resize
    window.addEventListener('resize', function() {
        const sidebar = document.getElementById('sidebar');
        const isMobile = window.innerWidth < 1024;
        
        if (isMobile) {
            sidebar.classList.add('-translate-x-full');
            sidebar.classList.remove('sidebar-collapsed');
        } else {
            sidebar.classList.remove('-translate-x-full');
        }
    });


    const notificationsApiUrl = '<?= base_url('api/notifications') ?>';

    // Notifications toggle
    function toggleNotifications() {
        const dropdown = document.getElementById('notifications-dropdown');
        const isHidden = dropdown.classList.contains('hidden');
        
        // Close all other dropdowns
        document.querySelectorAll('[id$="-dropdown"]').forEach(dropdown => {
            if (dropdown.id !== 'notifications-dropdown') {
                dropdown.classList.add('hidden');
            }
        });
        
        // Toggle notifications dropdown
        dropdown.classList.toggle('hidden');
        
        // Load notifications if opening
        if (isHidden) {
            console.log('Opening notifications dropdown...');
            loadNotifications();
        } else {
            // When closing notifications dropdown, mark all as read and hide count
            console.log('Closing notifications dropdown, marking all as read...');
            // Hide count immediately for better UX
            hideNotificationCount();
            markAllAsRead();
        }
    }

    // User profile toggle
    function toggleUserProfile() {
        const dropdown = document.getElementById('user-profile-dropdown');
        const isHidden = dropdown.classList.contains('hidden');
        
        // Close all other dropdowns
        document.querySelectorAll('[id$="-dropdown"]').forEach(dropdown => {
            if (dropdown.id !== 'user-profile-dropdown') {
                dropdown.classList.add('hidden');
            }
        });
        
        // Toggle user profile dropdown
        dropdown.classList.toggle('hidden');
    }

    // Load notifications
    function loadNotifications() {
        fetch(notificationsApiUrl)
            .then(response => {
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                return response.json();
            })
            .then(data => {
                console.log('Notifications API response:', data);
                
                // Handle CSRF token refresh if provided in response
                if (data.csrf_token && window.refreshCsrfToken) {
                    window.refreshCsrfToken(data.csrf_token);
                }

                if (data.success) {
                    console.log('Updating notification count to:', data.count);
                    updateNotificationCount(data.count);
                    displayNotifications(data.notifications);
                } else {
                    console.error('API returned error:', data.message);
                    showSampleNotifications();
                }
            })
            .catch(error => {
                console.error('Error loading notifications:', error);
                // Show sample notifications for demo when API fails
                showSampleNotifications();
            });
    }

    // Update notification count
    function updateNotificationCount(count) {
        const countElement = document.getElementById('notification-count');
        
        // Check if notifications were marked as read locally
        const notificationsRead = localStorage.getItem('notifications_read');
        const lastReadTime = localStorage.getItem('last_notification_read_time');
        
        if (notificationsRead === 'true' && lastReadTime) {
            // Check if there are new activities since last read
            const currentTime = new Date().getTime();
            const timeDiff = currentTime - parseInt(lastReadTime);
            
            // If less than 5 minutes have passed since marking as read, hide count
            if (timeDiff < 5 * 60 * 1000) {
                console.log('Hiding count - notifications recently read');
                countElement.classList.add('hidden');
                return;
            } else {
                // Clear the read status after timeout
                console.log('Clearing read status - timeout reached');
                localStorage.removeItem('notifications_read');
                localStorage.removeItem('last_notification_read_time');
            }
        }
        
        if (count > 0) {
            console.log('Showing notification count:', count);
            countElement.textContent = count;
            countElement.classList.remove('hidden');
        } else {
            console.log('Hiding notification count - no unread notifications');
            countElement.classList.add('hidden');
        }
    }

    // Hide notification count
    function hideNotificationCount() {
        const countElement = document.getElementById('notification-count');
        console.log('Hiding notification count...', countElement);
        countElement.classList.add('hidden');
        
        // Store in localStorage that notifications were read
        localStorage.setItem('notifications_read', 'true');
        localStorage.setItem('last_notification_read_time', new Date().getTime().toString());
    }

    // Display notifications
    function displayNotifications(notifications) {
        const listElement = document.getElementById('notifications-list');
        
        // Clear existing content safely
        listElement.textContent = '';
        
        if (notifications.length === 0) {
            const emptyState = document.createElement('div');
            emptyState.className = 'p-4 text-center text-gray-500';
            
            const icon = document.createElement('i');
            icon.className = 'fas fa-bell-slash text-2xl mb-2';
            
            const message = document.createElement('p');
            message.textContent = 'No new notifications';
            
            emptyState.appendChild(icon);
            emptyState.appendChild(message);
            listElement.appendChild(emptyState);
            return;
        }

        const fragment = document.createDocumentFragment();
        
        notifications.forEach(notification => {
            const itemDiv = document.createElement('div');
            itemDiv.className = 'p-4 border-b border-gray-100 hover:bg-gray-50 transition-colors cursor-pointer';
            itemDiv.onclick = function() {
                navigateToEntityFromNotification(notification.entity_type || 'system', notification.entity_id || 'null');
                return false;
            };
            
            const flexDiv = document.createElement('div');
            flexDiv.className = 'flex items-start space-x-3';
            
            // Icon container
            const iconContainer = document.createElement('div');
            iconContainer.className = 'flex-shrink-0';
            
            const iconBg = document.createElement('div');
            iconBg.className = `w-8 h-8 ${getNotificationIconBgClass(notification.color)} rounded-full flex items-center justify-center`;
            
            const icon = document.createElement('i');
            icon.className = `${notification.icon} ${getNotificationIconClass(notification.color)} text-sm`;
            
            iconBg.appendChild(icon);
            iconContainer.appendChild(iconBg);
            
            // Content container
            const contentDiv = document.createElement('div');
            contentDiv.className = 'flex-1 min-w-0';
            
            const title = document.createElement('p');
            title.className = 'text-sm font-medium text-gray-900';
            title.textContent = notification.title;
            
            const message = document.createElement('p');
            message.className = 'text-sm text-gray-500';
            message.textContent = notification.message;
            
            const metaDiv = document.createElement('div');
            metaDiv.className = 'flex items-center justify-between mt-1';
            
            const time = document.createElement('p');
            time.className = 'text-xs text-gray-400';
            time.textContent = formatTime(notification.created_at);
            metaDiv.appendChild(time);
            
            if (notification.user_name) {
                const user = document.createElement('p');
                user.className = 'text-xs text-gray-500';
                user.textContent = `by ${notification.user_name}`;
                metaDiv.appendChild(user);
            }
            
            contentDiv.appendChild(title);
            contentDiv.appendChild(message);
            contentDiv.appendChild(metaDiv);
            
            flexDiv.appendChild(iconContainer);
            flexDiv.appendChild(contentDiv);
            
            // Unread indicator
            if (!notification.is_read) {
                const unreadDot = document.createElement('div');
                unreadDot.className = 'w-2 h-2 bg-blue-500 rounded-full';
                flexDiv.appendChild(unreadDot);
            }
            
            itemDiv.appendChild(flexDiv);
            fragment.appendChild(itemDiv);
        });
        
        listElement.appendChild(fragment);
    }

    // Clear notification read status (for testing)
    function clearNotificationReadStatus() {
        localStorage.removeItem('notifications_read');
        localStorage.removeItem('last_notification_read_time');
        console.log('Notification read status cleared');
        // Reload notifications to show count
        loadNotifications();
    }

    // Test notification count (for debugging)
    function testNotificationCount() {
        console.log('Testing notification count...');
        console.log('localStorage notifications_read:', localStorage.getItem('notifications_read'));
        console.log('localStorage last_notification_read_time:', localStorage.getItem('last_notification_read_time'));
        
        // Force show count
        updateNotificationCount(5);
    }

    // Show sample notifications for demo
    function showSampleNotifications() {
        console.log('Showing sample notifications...');
        const sampleNotifications = [
            {
                id: 1,
                title: 'New Patient Registered',
                message: 'John Doe has been registered successfully',
                type: 'patient',
                entity_type: 'patient',
                entity_id: 1,
                is_read: false,
                created_at: new Date(Date.now() - 2 * 60 * 1000).toISOString(), // 2 minutes ago
                icon: 'fas fa-user-plus',
                color: 'green',
                user_name: 'Dr. Smith'
            },
            {
                id: 2,
                title: 'New Appointment Scheduled',
                message: 'Appointment scheduled for Sarah Wilson at 2:00 PM',
                type: 'appointment',
                entity_type: 'appointment',
                entity_id: 2,
                is_read: false,
                created_at: new Date(Date.now() - 15 * 60 * 1000).toISOString(), // 15 minutes ago
                icon: 'fas fa-calendar-plus',
                color: 'blue',
                user_name: 'Dr. Smith'
            },
            {
                id: 3,
                title: 'Low Stock Alert',
                message: 'Dental floss is running low (5 items remaining)',
                type: 'inventory',
                entity_type: 'inventory',
                entity_id: 3,
                is_read: true,
                created_at: new Date(Date.now() - 60 * 60 * 1000).toISOString(), // 1 hour ago
                icon: 'fas fa-exclamation-triangle',
                color: 'yellow',
                user_name: 'System'
            },
            {
                id: 4,
                title: 'Payment Received',
                message: 'Payment of $150 received from Jane Wilson',
                type: 'finance',
                entity_type: 'finance',
                entity_id: 4,
                is_read: false,
                created_at: new Date(Date.now() - 90 * 60 * 1000).toISOString(), // 1.5 hours ago
                icon: 'fas fa-dollar-sign',
                color: 'green',
                user_name: 'Dr. Smith'
            },
            {
                id: 5,
                title: 'Treatment Completed',
                message: 'Treatment #T2024001 completed successfully',
                type: 'treatment',
                entity_type: 'treatment',
                entity_id: 5,
                is_read: true,
                created_at: new Date(Date.now() - 120 * 60 * 1000).toISOString(), // 2 hours ago
                icon: 'fas fa-check-circle',
                color: 'green',
                user_name: 'Dr. Smith'
            }
        ];
        
        updateNotificationCount(3); // Show 3 unread notifications
        displayNotifications(sampleNotifications);
    }

    // Helper functions for notification colors
    function getNotificationIconBgClass(color) {
        const colorMap = {
            'blue': 'bg-blue-100',
            'green': 'bg-green-100',
            'yellow': 'bg-yellow-100',
            'purple': 'bg-purple-100',
            'red': 'bg-red-100',
            'gray': 'bg-gray-100'
        };
        return colorMap[color] || 'bg-gray-100';
    }

    function getNotificationIconClass(color) {
        const colorMap = {
            'blue': 'text-blue-600',
            'green': 'text-green-600',
            'yellow': 'text-yellow-600',
            'purple': 'text-purple-600',
            'red': 'text-red-600',
            'gray': 'text-gray-600'
        };
        return colorMap[color] || 'text-gray-600';
    }

    // Format time for display
    function formatTime(dateString) {
        const date = new Date(dateString);
        const now = new Date();
        const diffInMinutes = Math.floor((now - date) / (1000 * 60));
        
        if (diffInMinutes < 1) {
            return 'Just now';
        } else if (diffInMinutes < 60) {
            return `${diffInMinutes} minute${diffInMinutes > 1 ? 's' : ''} ago`;
        } else if (diffInMinutes < 1440) {
            const hours = Math.floor(diffInMinutes / 60);
            return `${hours} hour${hours > 1 ? 's' : ''} ago`;
        } else {
            const days = Math.floor(diffInMinutes / 1440);
            return `${days} day${days > 1 ? 's' : ''} ago`;
        }
    }

    // Mark all notifications as read
    function markAllAsRead() {
        console.log('Calling markAllAsRead...');
        fetch('<?= base_url('notifications/mark-read') ?>', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => {
            console.log('Mark as read response:', response);
            return response.json();
        })
        .then(data => {
            console.log('Mark as read data:', data);
            if (data.success) {
                // Hide notification count immediately
                hideNotificationCount();
                // Update notifications display without reloading count
                updateNotificationsDisplay();
            }
        })
        .catch(error => {
            console.error('Error marking notifications as read:', error);
            hideNotificationCount();
            updateNotificationsDisplay();
        });
    }

    // Update notifications display to show all as read
    function updateNotificationsDisplay() {
        const notifications = document.querySelectorAll('#notifications-list > div');
        notifications.forEach(notification => {
            // Remove unread indicators
            const unreadDot = notification.querySelector('.bg-blue-500');
            if (unreadDot) {
                unreadDot.remove();
            }
            
            // Remove mark as read buttons
            const markButton = notification.querySelector('[onclick*="markAsRead"]');
            if (markButton) {
                markButton.remove();
            }
        });
    }

    // Navigate to entity details page from notification
    function navigateToEntityFromNotification(entityType, entityId) {
        console.log('Notification clicked:', { entityType, entityId });
        
        if (!entityId || entityId === 'null') {
            console.log('No entity ID available for navigation from notification');
            return;
        }
        
        const baseUrl = '<?= base_url() ?>';
        let url = '';
        
        switch (entityType) {
            case 'patient':
                url = `${baseUrl}patient/${entityId}`;
                break;
            case 'appointment':
                url = `${baseUrl}appointment/${entityId}`;
                break;
            case 'treatment':
                url = `${baseUrl}treatment/${entityId}`;
                break;
            case 'examination':
                url = `${baseUrl}examination/${entityId}`;
                break;
            case 'prescription':
                url = `${baseUrl}prescription/${entityId}`;
                break;
            case 'finance':
                url = `${baseUrl}finance/${entityId}`;
                break;
            case 'inventory':
                url = `${baseUrl}inventory/${entityId}`;
                break;
            case 'user':
                url = `${baseUrl}user/${entityId}`;
                break;
            case 'system':
                // For system notifications, go to activity log
                url = `${baseUrl}activity-log`;
                break;
            default:
                console.log(`Unknown entity type: ${entityType}`);
                return;
        }
        
        console.log('Navigating to:', url);
        // Navigate to the entity details page
        window.location.href = url;
    }

    // Test function to verify navigation is working
    function testNotificationNavigation() {
        console.log('Testing notification navigation...');
        navigateToEntityFromNotification('patient', 1);
    }

    const clinicDateFormat = '<?= esc(settings()->get('date_format', 'd/m/Y')) ?>';
    const clinicTimeFormat = '<?= esc(settings()->get('time_format', '12')) ?>';

    function clinicDateFormatLabel(format) {
        return format.replace('Y', 'YYYY').replace('m', 'MM').replace('d', 'DD');
    }

    function formatClinicDateFromIso(isoDate) {
        if (!isoDate || !/^\d{4}-\d{2}-\d{2}$/.test(isoDate)) {
            return isoDate || '';
        }

        const [year, month, day] = isoDate.split('-');
        if (clinicDateFormat === 'm/d/Y') {
            return `${month}/${day}/${year}`;
        }
        if (clinicDateFormat === 'd/m/Y') {
            return `${day}/${month}/${year}`;
        }
        return isoDate;
    }

    function parseClinicDateToIso(rawValue) {
        if (!rawValue) {
            return '';
        }

        const value = rawValue.trim();
        if (/^\d{4}-\d{2}-\d{2}$/.test(value)) {
            return value;
        }

        const isValidCalendarDate = (year, month, day) => {
            const y = Number(year);
            const m = Number(month);
            const d = Number(day);
            if (!Number.isInteger(y) || !Number.isInteger(m) || !Number.isInteger(d)) {
                return false;
            }
            const probe = new Date(Date.UTC(y, m - 1, d));
            return probe.getUTCFullYear() === y && (probe.getUTCMonth() + 1) === m && probe.getUTCDate() === d;
        };

        if (clinicDateFormat === 'm/d/Y') {
            const match = value.match(/^(\d{1,2})\/(\d{1,2})\/(\d{4})$/);
            if (!match) return null;
            const month = match[1].padStart(2, '0');
            const day = match[2].padStart(2, '0');
            const year = match[3];
            if (!isValidCalendarDate(year, month, day)) return null;
            return `${year}-${month}-${day}`;
        }

        if (clinicDateFormat === 'd/m/Y') {
            const match = value.match(/^(\d{1,2})\/(\d{1,2})\/(\d{4})$/);
            if (!match) return null;
            const day = match[1].padStart(2, '0');
            const month = match[2].padStart(2, '0');
            const year = match[3];
            if (!isValidCalendarDate(year, month, day)) return null;
            return `${year}-${month}-${day}`;
        }

        return null;
    }

    function formatClinicTimeFrom24(rawTime) {
        if (!rawTime) return '';
        const match = String(rawTime).trim().match(/^(\d{1,2}):(\d{2})(?::\d{2})?$/);
        if (!match) return rawTime;

        const hour24 = Number(match[1]);
        const minute = match[2];
        if (!Number.isInteger(hour24) || hour24 < 0 || hour24 > 23) return rawTime;

        if (clinicTimeFormat === '24') {
            return `${String(hour24).padStart(2, '0')}:${minute}`;
        }

        const period = hour24 >= 12 ? 'PM' : 'AM';
        const hour12 = hour24 % 12 || 12;
        return `${hour12}:${minute} ${period}`;
    }

    function parseClinicTimeTo24(rawValue) {
        if (!rawValue) return '';
        const value = String(rawValue).trim();

        let match = value.match(/^(\d{1,2}):(\d{2})(?::\d{2})?$/);
        if (match) {
            const hour = Number(match[1]);
            const minute = Number(match[2]);
            if (Number.isInteger(hour) && Number.isInteger(minute) && hour >= 0 && hour <= 23 && minute >= 0 && minute <= 59) {
                return `${String(hour).padStart(2, '0')}:${String(minute).padStart(2, '0')}`;
            }
        }

        match = value.match(/^(\d{1,2}):(\d{2})\s*([AP]M)$/i);
        if (match) {
            let hour = Number(match[1]);
            const minute = Number(match[2]);
            const period = match[3].toUpperCase();
            if (!Number.isInteger(hour) || hour < 1 || hour > 12 || !Number.isInteger(minute) || minute < 0 || minute > 59) {
                return null;
            }
            if (period === 'AM') {
                hour = hour === 12 ? 0 : hour;
            } else {
                hour = hour === 12 ? 12 : hour + 12;
            }
            return `${String(hour).padStart(2, '0')}:${String(minute).padStart(2, '0')}`;
        }

        return null;
    }

    function initializeClinicDateInputs() {
        const fields = document.querySelectorAll('input[data-clinic-date="1"]');
        const formatHint = clinicDateFormatLabel(clinicDateFormat);

        fields.forEach((field) => {
            const current = field.value ? field.value.trim() : '';
            if (current && /^\d{4}-\d{2}-\d{2}$/.test(current)) {
                field.value = formatClinicDateFromIso(current);
            }
            field.type = 'text';
            field.setAttribute('placeholder', formatHint);
            field.setAttribute('autocomplete', 'off');
            field.inputMode = 'numeric';
            field.setAttribute('title', `${formatHint} (type manually or press Alt+Down for picker)`);

            const switchToPickerMode = () => {
                const raw = (field.value || '').trim();
                const iso = parseClinicDateToIso(raw);
                field.setCustomValidity('');
                field.type = 'date';
                if (iso) {
                    field.value = iso;
                } else if (raw === '') {
                    field.value = '';
                }
                if (typeof field.showPicker === 'function') {
                    try {
                        field.showPicker();
                    } catch (e) {
                        // no-op: browser may block showPicker unless user-initiated
                    }
                }
            };

            const switchToDisplayMode = () => {
                if (field.type === 'date') {
                    const iso = (field.value || '').trim();
                    field.type = 'text';
                    field.value = iso ? formatClinicDateFromIso(iso) : '';
                }
                field.setCustomValidity('');
            };

            // Keep text mode by default so users can type dates manually with numpad.
            // Picker remains available via click, Alt+ArrowDown, or double-click.
            field.addEventListener('click', () => {
                if (field.type !== 'date') {
                    switchToPickerMode();
                }
            });
            field.addEventListener('keydown', (event) => {
                if (event.altKey && event.key === 'ArrowDown') {
                    event.preventDefault();
                    switchToPickerMode();
                }
            });
            field.addEventListener('dblclick', () => {
                if (field.type !== 'date') {
                    switchToPickerMode();
                }
            });
            field.addEventListener('input', () => {
                field.setCustomValidity('');
            });
            field.addEventListener('change', () => {
                field.setCustomValidity('');
            });
            field.addEventListener('blur', switchToDisplayMode);
        });

        document.querySelectorAll('form').forEach((form) => {
            form.addEventListener('submit', function (event) {
                const dateInputs = form.querySelectorAll('input[data-clinic-date="1"]');
                for (const input of dateInputs) {
                    input.setCustomValidity('');
                    const raw = (input.value || '').trim();
                    if (raw === '') {
                        continue;
                    }

                    const iso = parseClinicDateToIso(raw);
                    if (!iso) {
                        input.setCustomValidity(`Use date format ${formatHint}`);
                        input.reportValidity();
                        event.preventDefault();
                        return;
                    }

                    input.setCustomValidity('');
                    input.value = iso;
                }
            }, true);
        });
    }

    function initializeClinicTimeInputs() {
        const fields = document.querySelectorAll('input[data-clinic-time="1"]');
        if (!fields.length) {
            return;
        }

        const formatHint = clinicTimeFormat === '24' ? 'HH:MM' : 'h:mm AM/PM';

        fields.forEach((field) => {
            if (clinicTimeFormat === '24') {
                field.type = 'time';
                field.setAttribute('title', 'Use 24-hour format (HH:MM)');
                return;
            }

            const current = (field.value || '').trim();
            if (current) {
                field.value = formatClinicTimeFrom24(current);
            }

            field.type = 'text';
            field.setAttribute('placeholder', formatHint);
            field.setAttribute('autocomplete', 'off');
            field.inputMode = 'numeric';
            field.setAttribute('title', `${formatHint} (type manually or press Alt+Down for picker)`);

            const switchToPickerMode = () => {
                const raw = (field.value || '').trim();
                const time24 = parseClinicTimeTo24(raw);
                field.type = 'time';
                field.value = time24 || '';
                if (typeof field.showPicker === 'function') {
                    try {
                        field.showPicker();
                    } catch (e) {
                        // browser may block without direct interaction
                    }
                }
            };

            const switchToDisplayMode = () => {
                if (field.type === 'time') {
                    const time24 = (field.value || '').trim();
                    field.type = 'text';
                    field.value = time24 ? formatClinicTimeFrom24(time24) : '';
                }
            };

            field.addEventListener('click', () => {
                if (field.type !== 'time') switchToPickerMode();
            });
            field.addEventListener('dblclick', () => {
                if (field.type !== 'time') switchToPickerMode();
            });
            field.addEventListener('keydown', (event) => {
                if (event.altKey && event.key === 'ArrowDown') {
                    event.preventDefault();
                    switchToPickerMode();
                }
            });
            field.addEventListener('blur', switchToDisplayMode);
        });

        document.querySelectorAll('form').forEach((form) => {
            form.addEventListener('submit', function (event) {
                const timeInputs = form.querySelectorAll('input[data-clinic-time="1"]');
                for (const input of timeInputs) {
                    const raw = (input.value || '').trim();
                    if (raw === '') {
                        input.setCustomValidity('');
                        continue;
                    }

                    const time24 = parseClinicTimeTo24(raw);
                    if (!time24) {
                        input.setCustomValidity(`Use time format ${formatHint}`);
                        input.reportValidity();
                        event.preventDefault();
                        return;
                    }

                    input.setCustomValidity('');
                    input.value = time24;
                }
            }, true);
        });
    }

    window.formatClinicDateFromIso = formatClinicDateFromIso;
    window.parseClinicDateToIso = parseClinicDateToIso;
    window.formatClinicTimeFrom24 = formatClinicTimeFrom24;
    window.parseClinicTimeTo24 = parseClinicTimeTo24;

    // Load notifications on page load
    document.addEventListener('DOMContentLoaded', function() {
        initializeClinicDateInputs();
        initializeClinicTimeInputs();
        console.log('Loading notifications on page load...');
        loadNotifications();
        loadNotificationCount();
        
        // Handle URL search parameters
        handleUrlSearchParams();
        
        // Refresh notifications every 30 seconds
        setInterval(() => {
            console.log('Refreshing notifications...');
            loadNotifications();
            loadNotificationCount();
        }, 30000);
    });

    // Handle URL search parameters
    function handleUrlSearchParams() {
        const urlParams = new URLSearchParams(window.location.search);
        const searchParam = urlParams.get('search');
        
        if (searchParam) {
            console.log('URL search parameter found:', searchParam);
            // Pre-populate the global search input
            const globalSearchInput = document.getElementById('globalSearch');
            if (globalSearchInput) {
                globalSearchInput.value = searchParam;
                
                // If we're on patients page, also perform the search
                if (window.location.pathname.includes('/patients')) {
                    setTimeout(() => {
                        performGlobalSearch(searchParam);
                    }, 1000); // Wait a bit for DataTables to initialize
                }
            }
        }
    }

    // Load notification count from API
    function loadNotificationCount() {
        fetch(notificationsApiUrl)
            .then(response => response.json())
            .then(data => {
                // Handle CSRF token refresh if provided in response
                if (data.csrf_token && window.refreshCsrfToken) {
                    window.refreshCsrfToken(data.csrf_token);
                }

                if (data.success) {
                    updateNotificationCount(data.count);
                } else {
                    console.error('Failed to load notification count:', data.message);
                }
            })
            .catch(error => {
                console.error('Error loading notification count:', error);
            });
    }

    // Global search functionality
    function handleGlobalSearch(event) {
        if (event.key === 'Enter') {
            const query = event.target.value.trim();
            if (query.length > 2) {
                performGlobalSearch(query);
            }
        }
    }

    function performGlobalSearch(query) {
        // Try to search on current page first, if it's the patients page
        if (window.location.pathname.includes('/patients')) {
            // If we're on the patients page, try to use DataTables search
            try {
                if (typeof patientsTable !== 'undefined') {
                    patientsTable.search(query).draw();
                    return;
                }
            } catch (e) {
                console.log('DataTables not available or search failed');
            }
        }
        
        // Fallback: redirect to patients page with search parameter
        window.location.href = `<?= base_url('patients') ?>?search=${encodeURIComponent(query)}`;
    }


    // Toggle User Menu Dropdown
    function toggleUserMenu() {
        const menu = document.getElementById('userMenu');
        
        // Toggle user menu
        if (menu) {
            menu.classList.toggle('hidden');
        }
    }

    // Toggle Clinic Switcher Dropdown
    function toggleClinicSwitcher() {
        const menu = document.getElementById('clinicSwitcherMenu');
        if (menu) {
            menu.classList.toggle('hidden');
        }
    }

    // Close dropdowns when clicking outside
    document.addEventListener('click', function(e) {
        // Handle User Menu
        if (!e.target.closest('#userMenu') && !e.target.closest('button[onclick*="toggleUserMenu"]')) {
            const userMenu = document.getElementById('userMenu');
            if (userMenu && !userMenu.classList.contains('hidden')) {
                userMenu.classList.add('hidden');
            }
        }
        
        // Handle Clinic Switcher
        if (!e.target.closest('#clinic-switcher')) {
            const clinicMenu = document.getElementById('clinicSwitcherMenu');
            if (clinicMenu && !clinicMenu.classList.contains('hidden')) {
                clinicMenu.classList.add('hidden');
            }
        }
    });

    // Keyboard shortcuts
    document.addEventListener('keydown', function(e) {
        // Ctrl+B to toggle sidebar
        if (e.ctrlKey && e.key === 'b') {
            e.preventDefault();
            toggleSidebar();
        }
    });

</script>

<!-- Select2 Initialization - Only load on pages that need it -->
<?php if (isset($loadSelect2) && $loadSelect2): ?>
<script src="<?= base_url('assets/js/select2-init.js') ?>?v=<?= time() ?>"></script>
<?php endif; ?>

</body>
</html>
