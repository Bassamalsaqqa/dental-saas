<!DOCTYPE html>
<html lang="en">
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

</head>
<body class="bg-gray-50 min-h-screen">
    <!-- Toast Notification System -->
    <?= view('components/toast') ?>  
    <div class="flex min-h-screen">
        <!-- Enhanced Sidebar with Glassmorphism -->
        <aside class="w-64 backdrop-blur-xl bg-white/80 border-r border-white/30 shadow-2xl shadow-blue-500/10 z-40 transition-all duration-300 transform flex-shrink-0 lg:translate-x-0 -translate-x-full flex flex-col" id="sidebar">
        <!-- Enhanced Sidebar Header -->
        <div class="px-2 pb-2  pt-3 border-b border-white/30" id="sidebar-header">
            <div class="flex items-center justify-between">
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
                <button class="group relative p-2 text-gray-600 hover:text-gray-900 hover:bg-white/50 rounded-xl transition-all duration-200 hover:scale-105 lg:block hidden" onclick="toggleSidebar()" title="Toggle Sidebar (Ctrl+B)">
                    <div class="absolute inset-0 bg-gradient-to-r from-blue-500/10 to-purple-500/10 rounded-xl blur opacity-0 group-hover:opacity-100 transition-opacity duration-200"></div>
                    <i class="fas fa-bars relative z-10"></i>
                </button>
            </div>
        </div>

        <!-- Enhanced Sidebar Navigation -->
        <nav class="flex-1" id="sidebar-nav">
            <!-- Main Menu -->
            <div class="p-4">
                <div class="flex items-center space-x-2 mb-4">
                    <div class="w-2 h-2 bg-gradient-to-r from-blue-500 to-purple-600 rounded-full animate-pulse"></div>
                    <h3 class="text-xs font-bold text-gray-600 uppercase tracking-wider">Main Menu</h3>
                </div>
                <ul class="space-y-2">
                    <?php 
                    // Simple static menu - no authentication required
                    $userMenu = [];
                    $menu = [
                            'dashboard' => [
                                'title' => 'Dashboard',
                                'url' => 'dashboard',
                                'icon' => 'fas fa-tachometer-alt',
                                'color' => 'blue',
                            ],
                            'patients' => [
                                'title' => 'Patients',
                                'url' => 'patients',
                                'icon' => 'fas fa-users',
                                'color' => 'emerald',
                            ],
                            'examinations' => [
                                'title' => 'Examinations',
                                'url' => 'examinations',
                                'icon' => 'fas fa-stethoscope',
                                'color' => 'orange',
                            ],
                            'appointments' => [
                                'title' => 'Appointments',
                                'url' => 'appointments',
                                'icon' => 'fas fa-calendar-alt',
                                'color' => 'purple',
                            ],
                            'treatments' => [
                                'title' => 'Treatments',
                                'url' => 'treatments',
                                'icon' => 'fas fa-tooth',
                                'color' => 'pink',
                            ],
                            'prescriptions' => [
                                'title' => 'Prescriptions',
                                'url' => 'prescriptions',
                                'icon' => 'fas fa-prescription-bottle-alt',
                                'color' => 'indigo',
                            ],
                            'finance' => [
                                'title' => 'Finance',
                                'url' => 'finance',
                                'icon' => 'fas fa-dollar-sign',
                                'color' => 'green',
                            ],
                            'reports' => [
                                'title' => 'Reports',
                                'url' => 'reports',
                                'icon' => 'fas fa-chart-bar',
                                'color' => 'amber',
                            ],
                            'inventory' => [
                                'title' => 'Inventory',
                                'url' => 'inventory',
                                'icon' => 'fas fa-boxes',
                                'color' => 'teal',
                            ],
                            'doctors' => [
                                'title' => 'Doctors',
                                'url' => 'doctors',
                                'icon' => 'fas fa-user-md',
                                'color' => 'green',
                            ],
                            'users' => [
                                'title' => 'User Management',
                                'url' => 'users',
                                'icon' => 'fas fa-users-cog',
                                'color' => 'violet',
                            ],
                            'roles' => [
                                'title' => 'Role Management',
                                'url' => 'roles',
                                'icon' => 'fas fa-user-tag',
                                'color' => 'indigo',
                            ],
                            'activity_log' => [
                                'title' => 'Activity Log',
                                'url' => 'activity-log',
                                'icon' => 'fas fa-history',
                                'color' => 'teal',
                            ],
                            'settings' => [
                                'title' => 'Settings',
                                'url' => 'settings',
                                'icon' => 'fas fa-cog',
                                'color' => 'gray',
                            ]
                        ];

                    // Permission mapping for menu items
                    $permissionMap = [
                        'dashboard' => ['module' => 'dashboard', 'action' => 'read'],
                        'patients' => ['module' => 'patients', 'action' => 'view'],
                        'examinations' => ['module' => 'examinations', 'action' => 'view'],
                        'appointments' => ['module' => 'appointments', 'action' => 'view'],
                        'treatments' => ['module' => 'treatments', 'action' => 'view'],
                        'prescriptions' => ['module' => 'prescriptions', 'action' => 'view'],
                        'finance' => ['module' => 'finance', 'action' => 'view'],
                        'reports' => ['module' => 'reports', 'action' => 'view'],
                        'inventory' => ['module' => 'inventory', 'action' => 'view'],
                        'doctors' => ['module' => 'users', 'action' => 'view'], // Doctors are part of user management
                        'users' => ['module' => 'users', 'action' => 'view'],
                        'roles' => ['module' => 'users', 'action' => 'view'], // Roles are part of user management
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
                                // Fallback to username
                                elseif (property_exists($user, 'username') and !empty($user->username)) {
                                    $displayName = esc($user->username);
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
                <!-- Left Section: Mobile Menu + Page Title -->
                <div class="flex items-center space-x-3 flex-1" id="header-left">
                    <!-- Mobile Menu Toggle -->
                    <button class="group relative lg:hidden p-2.5 text-gray-600 hover:text-gray-800 transition-all duration-200 rounded-xl hover:bg-gradient-to-r hover:from-blue-50 hover:to-indigo-50 hover:shadow-lg hover:shadow-blue-500/10" onclick="toggleSidebar()">
                        <div class="absolute inset-0 bg-gradient-to-r from-blue-500/10 to-indigo-500/10 rounded-xl blur opacity-0 group-hover:opacity-100 transition-opacity duration-200"></div>
                        <i class="fas fa-bars text-lg relative z-10 group-hover:text-blue-600 transition-colors duration-200"></i>
                    </button>
                    
                    <!-- Page Title -->
                    <div class="flex items-center">
                        <h2 class="text-xl font-bold text-gray-900 leading-tight"><?= $pageTitle ?? $title ?? esc($clinic['name']) ?></h2>
                    </div>
                </div>
                
                <!-- Right Section: User Info + Actions -->
                <div class="flex items-center space-x-4" id="header-right">
                    <!-- System Action Icons -->
                    <div class="lg:flex items-center space-x-2 hidden">
                        <!-- New Patient -->
                        <div class="relative group">
                            <a href="<?= base_url('patients/create') ?>" class="relative flex items-center justify-center w-10 h-10 bg-gradient-to-br from-blue-50 to-cyan-50 hover:from-blue-50 hover:to-blue-100 border border-blue-200 hover:border-blue-300 text-blue-600 hover:text-blue-700 rounded-xl transition-all duration-200 hover:scale-105 hover:shadow-md hover:shadow-blue-500/10">
                                <i class="fas fa-user-plus text-sm"></i>
                            </a>
                            <div class="absolute top-full mt-2 left-1/2 transform -translate-x-1/2 bg-gray-900 text-white text-xs font-medium px-2 py-1 rounded-lg opacity-0 group-hover:opacity-100 transition-opacity duration-200 whitespace-nowrap pointer-events-none z-50">
                                New Patient
                                <div class="absolute -top-1 left-1/2 transform -translate-x-1/2 w-2 h-2 bg-gray-900 rotate-45"></div>
                            </div>
                        </div>
                        
                        <!-- New Appointment -->
                        <div class="relative group">
                            <a href="<?= base_url('appointments/create') ?>" class="relative flex items-center justify-center w-10 h-10 bg-gradient-to-br from-purple-50 to-violet-50 hover:from-purple-50 hover:to-purple-100 border border-purple-200 hover:border-purple-300 text-purple-600 hover:text-purple-700 rounded-xl transition-all duration-200 hover:scale-105 hover:shadow-md hover:shadow-purple-500/10">
                                <i class="fas fa-calendar-plus text-sm"></i>
                            </a>
                            <div class="absolute top-full mt-2 left-1/2 transform -translate-x-1/2 bg-gray-900 text-white text-xs font-medium px-2 py-1 rounded-lg opacity-0 group-hover:opacity-100 transition-opacity duration-200 whitespace-nowrap pointer-events-none z-50">
                                New Appointment
                                <div class="absolute -top-1 left-1/2 transform -translate-x-1/2 w-2 h-2 bg-gray-900 rotate-45"></div>
                            </div>
                        </div>
                        
                        <!-- New Examination -->
                        <div class="relative group">
                            <a href="<?= base_url('examinations/create') ?>" class="relative flex items-center justify-center w-10 h-10 bg-gradient-to-br from-emerald-50 to-green-50 hover:from-emerald-50 hover:to-emerald-100 border border-emerald-200 hover:border-emerald-300 text-emerald-600 hover:text-emerald-700 rounded-xl transition-all duration-200 hover:scale-105 hover:shadow-md hover:shadow-emerald-500/10">
                                <i class="fas fa-stethoscope text-sm"></i>
                            </a>
                            <div class="absolute top-full mt-2 left-1/2 transform -translate-x-1/2 bg-gray-900 text-white text-xs font-medium px-2 py-1 rounded-lg opacity-0 group-hover:opacity-100 transition-opacity duration-200 whitespace-nowrap pointer-events-none z-50">
                                New Examination
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
                                Notifications
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
                            Profile Menu
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
                                                // Fallback to username
                                                elseif (property_exists($user, 'username') and !empty($user->username)) {
                                                    $displayName = htmlspecialchars($user->username);
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
                                    <span class="font-medium">Profile Settings</span>
                                </a>
                                <a href="<?= base_url('settings') ?>" class="flex items-center space-x-3 px-3 py-2.5 text-gray-700 hover:bg-gray-50 rounded-lg transition-colors duration-200">
                                    <i class="fas fa-cog text-gray-500 w-5"></i>
                                    <span class="font-medium">System Settings</span>
                                </a>
                                <a href="<?= base_url('clinic/select') ?>" class="flex items-center space-x-3 px-3 py-2.5 text-gray-700 hover:bg-gray-50 rounded-lg transition-colors duration-200">
                                    <i class="fas fa-exchange-alt text-gray-500 w-5"></i>
                                    <span class="font-medium">Switch Clinic</span>
                                </a>
                                <div class="border-t border-gray-100 my-2"></div>
                                <a href="<?= base_url('auth/logout') ?>" class="flex items-center space-x-3 px-3 py-2.5 text-red-600 hover:bg-red-50 rounded-lg transition-colors duration-200">
                                    <i class="fas fa-sign-out-alt text-red-500 w-5"></i>
                                    <span class="font-medium">Sign Out</span>
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
                            Logout
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
                <h3 class="text-lg font-bold text-gray-900">Notifications</h3>
                <button onclick="markAllAsRead()" class="text-sm text-blue-600 hover:text-blue-800">Mark all as read</button>
            </div>
        </div>
        <div id="notifications-list" class="max-h-64 overflow-y-auto">
            <!-- Notifications will be loaded here -->
        </div>
        <div class="p-4 border-t border-gray-200">
            <a href="<?= base_url('activity-log') ?>" class="text-sm text-blue-600 hover:text-blue-800">View all notifications</a>
        </div>
    </div>
</div>


<!-- JavaScript -->
<script>
    // Global CSRF configuration
    window.csrfConfig = {
        name: document.querySelector('meta[name="csrf-name"]')?.getAttribute('content') || '<?= csrf_token() ?>',
        header: 'X-CSRF-TOKEN',
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

    // Sidebar toggle functionality
    function toggleSidebar() {
        const sidebar = document.getElementById('sidebar');
        const mainContent = document.getElementById('main-content');
        const headerLeft = document.getElementById('header-left');
        const sidebarNav = document.getElementById('sidebar-nav');
        const sidebarFooter = document.getElementById('sidebar-footer');
        
        const isHidden = sidebar.classList.contains('-translate-x-full');
        
        if (isHidden) {
            // Show sidebar
            sidebar.classList.remove('-translate-x-full');
            sidebar.classList.remove('w-0');
            sidebar.classList.add('w-64');
            sidebar.style.width = '16rem'; // 256px
            headerLeft.style.marginLeft = '0';
            sidebarNav.style.display = 'block';
            sidebarFooter.style.display = 'block';
        } else {
            // Hide sidebar
            sidebar.classList.add('-translate-x-full');
            sidebar.classList.remove('w-64');
            sidebar.classList.add('w-0');
            sidebar.style.width = '0';
            headerLeft.style.marginLeft = '12rem'; // 192px margin
            sidebarNav.style.display = 'none';
            sidebarFooter.style.display = 'none';
        }
    }

    // Initialize sidebar state on page load
    document.addEventListener('DOMContentLoaded', function() {
        const sidebar = document.getElementById('sidebar');
        const headerLeft = document.getElementById('header-left');
        const sidebarNav = document.getElementById('sidebar-nav');
        const sidebarFooter = document.getElementById('sidebar-footer');
        const sidebarTitle = document.getElementById('sidebar-title');
        const isMobile = window.innerWidth < 1024; // lg breakpoint
        
        if (isMobile) {
            // On mobile, sidebar should be hidden by default but keep title visible
            sidebar.classList.add('-translate-x-full');
            sidebar.classList.remove('w-64');
            sidebar.classList.add('w-0');
            sidebar.style.width = '0';
            headerLeft.style.marginLeft = '12rem'; // 192px margin
            sidebarTitle.style.display = 'block'; // Keep title visible
            sidebarNav.style.display = 'none';
            sidebarFooter.style.display = 'none';
        } else {
            // On desktop, sidebar should be visible by default
            sidebar.classList.remove('-translate-x-full');
            sidebar.classList.remove('w-0');
            sidebar.classList.add('w-64');
            sidebar.style.width = '16rem';
            headerLeft.style.marginLeft = '0';
            sidebarTitle.style.display = 'block';
            sidebarNav.style.display = 'block';
            sidebarFooter.style.display = 'block';
        }
    });

    // Handle window resize
    window.addEventListener('resize', function() {
        const sidebar = document.getElementById('sidebar');
        const headerLeft = document.getElementById('header-left');
        const sidebarTitle = document.getElementById('sidebar-title');
        const sidebarNav = document.getElementById('sidebar-nav');
        const sidebarFooter = document.getElementById('sidebar-footer');
        const isMobile = window.innerWidth < 1024; // lg breakpoint
        
        if (isMobile) {
            // On mobile, hide sidebar but keep title visible
            sidebar.classList.add('-translate-x-full');
            sidebar.classList.remove('w-64');
            sidebar.classList.add('w-0');
            sidebar.style.width = '0';
            headerLeft.style.marginLeft = '12rem'; // 192px margin
            sidebarTitle.style.display = 'block'; // Keep title visible
            sidebarNav.style.display = 'none';
            sidebarFooter.style.display = 'none';
        } else {
            // On desktop, show sidebar
            sidebar.classList.remove('-translate-x-full');
            sidebar.classList.remove('w-0');
            sidebar.classList.add('w-64');
            sidebar.style.width = '16rem';
            headerLeft.style.marginLeft = '0';
            sidebarTitle.style.display = 'block';
            sidebarNav.style.display = 'block';
            sidebarFooter.style.display = 'block';
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

    // Load notifications on page load
    document.addEventListener('DOMContentLoaded', function() {
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

    // Close dropdowns when clicking outside
    document.addEventListener('click', function(e) {
        if (!e.target.closest('#userMenu') && !e.target.closest('button[onclick*="toggle"]')) {
            const userMenu = document.getElementById('userMenu');
            
            if (userMenu && !userMenu.classList.contains('hidden')) {
                userMenu.classList.add('hidden');
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
