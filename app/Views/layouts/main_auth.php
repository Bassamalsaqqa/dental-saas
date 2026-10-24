<!DOCTYPE html>
<?php
    helper('asset');
    $rawPageTitle = $pageTitle ?? $title ?? ($clinic['name'] ?? 'Denta+');
    $uri = service('uri');
    $segments = $uri->getSegments();
    $segment1 = strtolower((string) ($segments[0] ?? 'dashboard'));
    $segment2 = strtolower((string) ($segments[1] ?? ''));
    $segment3 = strtolower((string) ($segments[2] ?? ''));

    $explicitTitleMap = [
        'Dashboard' => lang('UI.menuDashboard'),
        'Patients' => lang('UI.menuPatients'),
        'Examinations' => lang('UI.menuExaminations'),
        'Appointments' => lang('UI.menuAppointments'),
        'Treatments' => lang('UI.menuTreatments'),
        'Prescriptions' => lang('UI.menuPrescriptions'),
        'Finance' => lang('UI.menuFinance'),
        'Reports' => lang('UI.menuReports'),
        'Inventory' => lang('UI.menuInventory'),
        'Users' => lang('UI.menuUserManagement'),
        'Roles' => lang('UI.menuRoleManagement'),
        'Activity Log' => lang('UI.menuActivityLog'),
        'Settings' => lang('UI.menuSettings'),
        'Create New Treatment' => lang('Treatment.create_new_treatment'),
        'Edit Treatment' => lang('Treatment.edit_treatment'),
        'Treatment Management' => lang('UI.menuTreatments'),
        'Create New Patient' => lang('UI.patientCreateTitle'),
        'Register New Patient' => lang('UI.patientCreateTitle'),
        'New Appointment' => lang('UI.appointmentNew'),
        'Edit Appointment' => lang('UI.appointmentEdit'),
        'New Examination' => lang('UI.newExamination'),
    ];

    $routeTitleMap = [
        'dashboard' => lang('UI.menuDashboard'),
        'patients' => lang('UI.menuPatients'),
        'patient' => $segment2 === 'create' ? lang('UI.patientCreateTitle') : lang('UI.menuPatients'),
        'examinations' => $segment2 === 'create' ? lang('UI.newExamination') : lang('UI.menuExaminations'),
        'examination' => $segment2 === 'create' ? lang('UI.newExamination') : lang('UI.menuExaminations'),
        'appointments' => $segment2 === 'create' ? lang('UI.appointmentNew') : lang('UI.menuAppointments'),
        'appointment' => $segment2 === 'create'
            ? lang('UI.appointmentNew')
            : (($segment3 === 'edit' || $segment2 === 'edit') ? lang('UI.appointmentEdit') : lang('UI.menuAppointments')),
        'treatments' => $segment2 === 'create' ? lang('Treatment.create_new_treatment') : lang('UI.menuTreatments'),
        'treatment' => $segment2 === 'create'
            ? lang('Treatment.create_new_treatment')
            : (($segment3 === 'edit' || $segment2 === 'edit') ? lang('Treatment.edit_treatment') : lang('UI.menuTreatments')),
        'prescriptions' => lang('UI.menuPrescriptions'),
        'prescription' => lang('UI.menuPrescriptions'),
        'providers' => lang('UI.menuProviders'),
        'finance' => lang('UI.menuFinance'),
        'reports' => lang('UI.menuReports'),
        'inventory' => lang('UI.menuInventory'),
        'users' => lang('UI.menuUserManagement'),
        'roles' => lang('UI.menuRoleManagement'),
        'activity-log' => lang('UI.menuActivityLog'),
        'settings' => lang('UI.menuSettings'),
        'notifications' => lang('UI.notifications'),
        'profile' => lang('UI.profileMenu'),
        'contracts' => lang('Contracts.contractTitle'),
    ];

    // Client-side exports are used only by these existing registry views.
    $v3ExportRegistry = ($segment2 === '' && in_array($segment1, ['patient', 'patients', 'examination', 'examinations', 'treatment', 'treatments', 'prescription', 'prescriptions'], true))
        || ($segment1 === 'finance' && $segment2 === 'payables');
    $resolvedPageTitle = $explicitTitleMap[$rawPageTitle] ?? ($routeTitleMap[$segment1] ?? $rawPageTitle);
?>
<html lang="<?= esc($locale ?? 'en') ?>" dir="<?= !empty($is_rtl) ? 'rtl' : 'ltr' ?>" translate="no">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= esc($resolvedPageTitle) ?></title>
    <link rel="icon" type="image/png" href="<?= base_url('assets/images/favicon.png') ?>">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=Noto+Sans+Arabic:wght@400;500;600;700;800&family=JetBrains+Mono:wght@400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= base_url('assets/css/tailwind.css') ?>?v=<?= esc(asset_version('assets/css/tailwind.css')) ?>">
    <link rel="stylesheet" href="<?= base_url('assets/css/app-drawer.css') ?>?v=<?= esc(asset_version('assets/css/app-drawer.css')) ?>">
    <link rel="stylesheet" media="print" href="<?= base_url('assets/vendor/fontawesome/css/all.min.css') ?>?v=<?= esc(asset_version('assets/vendor/fontawesome/css/all.min.css')) ?>">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <!-- DataTables Buttons CSS - Only load for pages that need DataTables -->
    <?php if (!isset($disableDataTables) || !$disableDataTables): ?>
    <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.2/css/buttons.dataTables.min.css">
    <?php endif; ?>
    <?php if ($loadChartJs ?? true): ?>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <?php endif; ?>
    <?php if (($loadFullCalendar ?? true) && !($segment1 === 'appointment' && $segment2 === 'calendar')): ?>
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/index.global.min.js"></script>
    <?php endif; ?>
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
    <?php if ($v3ExportRegistry): ?>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/pdfmake.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/vfs_fonts.js"></script>
    <?php endif; ?>
    <meta name="description" content="Professional dental management system with comprehensive patient care, examination tracking, and appointment scheduling.">
    <meta name="theme-color" content="#162421">
    <meta name="csrf-name" content="<?= csrf_token() ?>">
    <meta name="csrf-token" content="<?= csrf_hash() ?>">
    <meta name="csrf-header" content="<?= csrf_header() ?>">
    <meta name="active-clinic-id" content="<?= (int)session('active_clinic_id') ?>">
    <meta name="api-session-token-url" content="<?= base_url('api/session-token') ?>">
    <meta name="google" content="notranslate">

<link rel="stylesheet" media="screen" href="<?= base_url('assets/css/warm-clinical.css') ?>?v=<?= esc(asset_version('assets/css/warm-clinical.css')) ?>">
<?php if (in_array($segment1, ['user','users','role','roles','providers','provider','treatment','treatments','reports','finance'], true)): ?>
<link rel="stylesheet" media="screen" href="<?= base_url('assets/css/v3-support-views.css') ?>?v=<?= esc(asset_version('assets/css/v3-support-views.css')) ?>">
<?php endif; ?>
    <?php if (in_array($segment1, ['dashboard', 'patient', 'patients', 'appointment', 'appointments', 'finance'], true)): ?>
    <link rel="stylesheet" media="screen" href="<?= base_url('assets/css/warm-workspaces.css') ?>?v=<?= esc(asset_version('assets/css/warm-workspaces.css')) ?>">
    <?php endif; ?>
    <?php if (in_array($segment1, ['contracts', 'examination', 'examinations', 'prescription', 'prescriptions', 'inventory', 'settings'], true)): ?>
    <link rel="stylesheet" media="screen" href="<?= base_url('assets/css/warm-secondary-layout.css') ?>?v=<?= esc(asset_version('assets/css/warm-secondary-layout.css')) ?>">
    <link rel="stylesheet" media="screen" href="<?= base_url('assets/css/warm-secondary.css') ?>?v=<?= esc(asset_version('assets/css/warm-secondary.css')) ?>">
    <?php endif; ?>
<link rel="stylesheet" media="screen" href="<?= base_url('assets/css/v3-flow-forms.css') ?>?v=<?= esc(asset_version('assets/css/v3-flow-forms.css')) ?>">
<?php if ($segment1 === 'odontogram'): ?>
<link rel="stylesheet" media="screen" href="<?= base_url('assets/css/v3-chart-chrome.css') ?>?v=<?= esc(asset_version('assets/css/v3-chart-chrome.css')) ?>">
<?php endif; ?>
<link rel="stylesheet" media="screen" href="<?= base_url('assets/css/v3-components.css') ?>?v=<?= @filemtime(FCPATH . 'assets/css/v3-components.css') ?: '1' ?>">
<link rel="stylesheet" media="screen" href="<?= base_url('assets/css/v3-icons.css') ?>?v=<?= @filemtime(FCPATH . 'assets/css/v3-icons.css') ?: '1' ?>">
</head>
<body class="denta-v3 min-h-screen" data-v3-module="<?= esc($segment1) ?>">
    <a class="v3-skip-link" href="#workspace-content"><?= esc(lang('V3.skipToContent')) ?></a>
    <!-- Toast Notification System -->
    <?= view('components/toast') ?>
    <div class="flex min-h-screen">
        <!-- V3 workspace rail: existing permission-filtered navigation -->
        <aside class="tenant-sidebar sidebar-mobile-hidden no-transition" id="sidebar" aria-label="<?= esc(lang('UI.mainMenu')) ?>">
        <script>
            // Immediate state restoration to prevent FOUS (Flash of Uncollapsed Sidebar)
            (function() {
                const wasCollapsed = localStorage.getItem('sidebar-collapsed') !== 'false';
                const isMobile = window.innerWidth < 768;
                const sidebar = document.getElementById('sidebar');
                if (wasCollapsed && !isMobile) {
                    sidebar.classList.add('sidebar-collapsed');
                }
            })();
        </script>
        <!-- Enhanced Sidebar Header -->
        <div class="px-3 pb-2.5 pt-3 border-b v3-border-default" id="sidebar-header">
            <div class="flex items-center">
                <a href="<?= base_url('dashboard') ?>" class="tenant-sidebar-brand flex items-center space-x-3 group cursor-pointer v3-radius p-2 transition-all duration-200 v3-hover-soft">
                    <div class="relative group">
                            <div class="absolute inset-0 v3-radius opacity-60 group-hover:opacity-90 transition-opacity duration-200"></div>
                            <div class="relative w-10 h-10 v3-radius flex items-center justify-center v3-elevation-none group-hover:scale-105 transition-all duration-200">
                            <?php if (!empty($clinic['logo_path'])): ?>
                                <?php
                                    $logoSrc = (strpos($clinic['logo_path'], 'http://') === 0 || strpos($clinic['logo_path'], 'https://') === 0)
                                        ? $clinic['logo_path']
                                        : base_url(ltrim($clinic['logo_path'], '/'));
                                ?>
                                <img src="<?= esc($logoSrc) ?>" alt="<?= esc($clinic['name']) ?>" class="w-full h-full object-contain">
                            <?php else: ?>
                                <i class="fas fa-tooth v3-text-on-primary text-lg"></i>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div id="sidebar-title" class="min-w-0 flex-shrink-0">
                        <h1 class="text-base font-black v3-text-on-primary whitespace-nowrap"><?= esc($clinic['name']) ?></h1>
                        <div class="flex items-center space-x-1">
                            <div class="w-1.5 h-1.5 v3-bg-primary rounded-full animate-pulse"></div>
                            <p class="text-xs v3-text-primary font-bold whitespace-nowrap"><?= esc($clinic['tagline']) ?></p>
                        </div>
                    </div>
                </a>
            </div>
        </div>

        <!-- Enhanced Sidebar Navigation -->
        <nav class="flex-1 min-h-0 overflow-y-auto custom-scrollbar overscroll-contain md:overflow-visible" id="sidebar-nav">
            <!-- Main Menu -->
            <div class="p-2.5">
                <div class="flex items-center space-x-2 mb-3 px-2">
                    <div class="w-2 h-2 from-cyan-300 to-emerald-300 rounded-full animate-pulse"></div>
                    <h3 class="tenant-sidebar-section-label text-xs font-black uppercase tracking-wider"><?= lang('UI.mainMenu') ?></h3>
                </div>
                <ul class="space-y-1.5">
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
                                'active_urls' => ['patients', 'patient'],
                            ],
                            'providers' => [
                                'title' => lang('UI.menuProviders'),
                                'url' => 'providers',
                                'icon' => 'fas fa-handshake',
                                'color' => 'cyan',
                                'active_urls' => ['providers', 'inventory/suppliers'],
                            ],
                            'examinations' => [
                                'title' => lang('UI.menuExaminations'),
                                'url' => 'examinations',
                                'icon' => 'fas fa-stethoscope',
                                'color' => 'orange',
                                'active_urls' => ['examinations', 'examination'],
                            ],
                            'appointments' => [
                                'title' => lang('UI.menuAppointments'),
                                'url' => 'appointments',
                                'icon' => 'fas fa-calendar-alt',
                                'color' => 'purple',
                                'active_urls' => ['appointments', 'appointment'],
                            ],
                            'treatments' => [
                                'title' => lang('UI.menuTreatments'),
                                'url' => 'treatments',
                                'icon' => 'fas fa-tooth',
                                'color' => 'pink',
                                'active_urls' => ['treatments', 'treatment'],
                            ],
                            'prescriptions' => [
                                'title' => lang('UI.menuPrescriptions'),
                                'url' => 'prescriptions',
                                'icon' => 'fas fa-prescription-bottle-alt',
                                'color' => 'indigo',
                                'active_urls' => ['prescriptions', 'prescription'],
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
                        'providers' => [
                            ['module' => 'inventory', 'action' => 'view'],
                            ['module' => 'finance', 'action' => 'view'],
                        ],
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
                        if (isset($permission[0]) && is_array($permission[0])) {
                            $hasAccess = false;
                            foreach ($permission as $p) {
                                if (has_permission($p['module'], $p['action'])) {
                                    $hasAccess = true;
                                    break;
                                }
                            }
                            if (! $hasAccess) {
                                continue;
                            }
                        } else {
                            if (!has_permission($permission['module'], $permission['action'])) {
                                continue;
                            }
                        }

                        if (! empty($item['active_urls'])) {
                            $isActive = false;
                            foreach ($item['active_urls'] as $activeUrl) {
                                if (strpos(uri_string(), $activeUrl) === 0) {
                                    $isActive = true;
                                    break;
                                }
                            }
                        } else {
                            $isActive = (strpos(uri_string(), $item['url']) === 0);
                        }
                    ?>
                    <li>
                        <a href="<?= base_url($item['url']) ?>" class="tenant-nav-link <?= $isActive ? 'is-active' : '' ?>" title="<?= esc($item['title']) ?>" <?= $isActive ? 'aria-current="page"' : '' ?>>
                            <i class="<?= $item['icon'] ?> tenant-nav-icon" aria-hidden="true"></i>
                            <span class="relative z-10 truncate"><?= $item['title'] ?></span>
                        </a>
                    </li>
                    <?php endforeach; ?>
                </ul>
            </div>
        </nav>

        <!-- Enhanced Sidebar Footer -->
        <div class="p-2.5 border-t v3-border-default" id="sidebar-footer">
            <a href="<?= base_url('profile') ?>" class="tenant-sidebar-footer-card group relative block cursor-pointer v3-radius p-2.5 transition-all duration-200 v3-hover-soft">
                <div class="relative z-10 flex items-center gap-2.5">
                    <div class="relative shrink-0">
                        <div class="absolute inset-0 rounded-full opacity-70 group-hover:opacity-95 transition-opacity duration-200"></div>
                        <div class="relative w-10 h-10 rounded-full flex items-center justify-center v3-elevation-none group-hover:scale-105 transition-all duration-200">
                            <i class="fas fa-user v3-text-on-primary text-sm"></i>
                        </div>
                    </div>
                    <div class="min-w-0 flex-1">
                        <div class="truncate text-sm font-black leading-5 v3-text-on-primary">
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
                    </div>
                </div>
            </a>
        </div>
    </aside>

    <div id="sidebar-backdrop" class="fixed inset-0 v3-backdrop z-30 hidden md:hidden" onclick="closeMobileSidebar()"></div>

    <!-- Main Content Area -->
    <div class="flex-1 flex flex-col min-h-screen transition-all duration-300 min-w-0" id="main-content">

        <!-- Smart Top Navigation Bar -->
        <header class="tenant-app-header" id="main-header">
            <!-- Animated Background Elements -->
            <div class="absolute inset-x-0 bottom-0 h-px from-transparent to-transparent pointer-events-none"></div>

            <div class="relative z-10 flex flex-wrap items-center justify-between gap-3 transition-all duration-300" id="header-content">
                <!-- Left Section: Sidebar Toggle + Page Title -->
                <div class="flex items-center space-x-3 flex-1 min-w-0" id="header-left">
                    <button id="sidebar-toggle" type="button" class="tenant-header-button" aria-controls="sidebar" aria-expanded="false" onclick="toggleSidebar()" title="<?= esc(lang('UI.mainMenu')) ?> (Ctrl+B)" aria-label="<?= esc(lang('UI.mainMenu')) ?>">
                        <i class="fas fa-bars text-lg relative z-10 transition-colors duration-200"></i>
                    </button>

                    <!-- Page Title -->
                    <div class="flex items-center space-x-4 min-w-0">
                        <h2 class="text-lg sm:text-xl font-black v3-text-ink leading-tight truncate"><?= esc($resolvedPageTitle) ?></h2>
                    </div>
                </div>

                <!-- Right Section: User Info + Actions -->
                <div class="flex items-center gap-2 sm:gap-4 flex-shrink-0" id="header-right">
                    <span class="v3-clinic-context"><i class="fas fa-clinic-medical" aria-hidden="true"></i><span class="truncate"><?= esc($clinic['name'] ?? 'Denta+') ?></span></span>
                    <?php $activeLocale = strtolower((string) ($locale ?? 'en')); ?>
                    <?php $returnToUrl = current_url(true)->__toString(); ?>
                    <div class="hidden sm:flex items-center">
                        <nav class="tenant-language-switcher" aria-label="<?= esc(lang('UI.settingsLanguage'), 'attr') ?>">
                            <span class="tenant-language-switcher__icon" title="<?= esc(lang('UI.settingsLanguage'), 'attr') ?>">
                                <i class="fas fa-language text-[13px]"></i>
                            </span>
                            <a href="<?= base_url('language/switch/en?return_to=' . urlencode($returnToUrl)) ?>"
                               lang="en"
                               hreflang="en"
                               aria-current="<?= $activeLocale === 'en' ? 'page' : 'false' ?>"
                               title="<?= esc(lang('UI.settingsLanguageEn'), 'attr') ?>"
                               class="tenant-language-option <?= $activeLocale === 'en' ? 'is-active' : '' ?>">
                                <span>EN</span>
                            </a>
                            <a href="<?= base_url('language/switch/ar?return_to=' . urlencode($returnToUrl)) ?>"
                               lang="ar"
                               hreflang="ar"
                               aria-current="<?= $activeLocale === 'ar' ? 'page' : 'false' ?>"
                               title="<?= esc(lang('UI.settingsLanguageAr'), 'attr') ?>"
                               class="tenant-language-option <?= $activeLocale === 'ar' ? 'is-active' : '' ?>">
                                <span lang="ar" dir="rtl"><?= esc(lang('UI.settingsLanguageAr')) ?></span>
                            </a>
                        </nav>
                    </div>
                    <div class="sm:hidden">
                        <a href="<?= base_url('language/switch/' . ($activeLocale === 'ar' ? 'en' : 'ar') . '?return_to=' . urlencode($returnToUrl)) ?>"
                                lang="<?= $activeLocale === 'ar' ? 'en' : 'ar' ?>"
                                hreflang="<?= $activeLocale === 'ar' ? 'en' : 'ar' ?>"
                                aria-label="<?= esc(lang('UI.settingsLanguage'), 'attr') ?>"
                                class="tenant-language-switcher">
                            <span class="tenant-language-switcher__icon !h-7 !w-7">
                                <i class="fas fa-language text-[13px]"></i>
                            </span>
                            <span class="tenant-language-option !min-h-7 !px-2"><?= $activeLocale === 'ar' ? 'EN' : esc(lang('UI.settingsLanguageAr')) ?></span>
                        </a>
                    </div>
                    <?php if (session()->get('is_impersonating')): ?>
                    <form action="<?= base_url('controlplane/impersonation/exit') ?>" method="post" class="hidden sm:flex items-center space-x-2 v3-radius border v3-border-default v3-bg-alert px-3 py-1.5">
                        <?= csrf_field() ?>
                        <span class="text-xs font-semibold v3-text-danger"><?= esc(lang('V3.impersonating')) ?></span>
                        <button type="submit" class="text-xs font-semibold v3-text-danger v3-hover-text-danger">
                            <?= esc(lang('V3.exitImpersonation')) ?>
                        </button>
                    </form>
                    <?php endif; ?>
                    <!-- Clinic Switcher (Moved here) -->
                    <?php if (isset($user_memberships) && count($user_memberships) > 1): ?>
                    <div class="relative group mobile-hide-compact" id="clinic-switcher">
                        <button onclick="toggleClinicSwitcher()" class="tenant-header-button">
                            <i class="fas fa-hospital text-sm"></i>
                        </button>

                        <!-- Tooltip -->
                        <div class="absolute top-full mt-2 left-1/2 transform -translate-x-1/2 v3-bg-forest v3-text-on-primary text-xs font-medium px-2 py-1 rounded-lg opacity-0 group-hover:opacity-100 transition-opacity duration-200 whitespace-nowrap pointer-events-none z-50">
                            <?= lang('UI.switchClinic') ?> (<?= esc($clinic['name']) ?>)
                            <div class="absolute -top-1 left-1/2 transform -translate-x-1/2 w-2 h-2 v3-bg-forest rotate-45"></div>
                        </div>

                        <div id="clinicSwitcherMenu" class="tenant-header-dropdown absolute <?= !empty($is_rtl) ? 'left-0 origin-top-left' : 'right-0 origin-top-right' ?> mt-3 hidden w-72 max-w-[calc(100vw-2rem)] z-50 transform transition-all">
                            <div class="tenant-header-dropdown__header p-4">
                                <p class="text-[10px] font-black v3-text-primary uppercase tracking-[0.2em]"><?= lang('UI.switchClinic') ?></p>
                                <p class="mt-1 truncate text-sm font-black v3-text-ink"><?= esc($clinic['name']) ?></p>
                            </div>
                            <div class="p-2 max-h-64 overflow-y-auto">
                                <?php foreach ($user_memberships as $m): ?>
                                    <a href="<?= base_url('clinic/switch/' . $m['clinic_id']) ?>" class="tenant-menu-row justify-between mb-1 <?= ($m['clinic_id'] == session()->get('active_clinic_id')) ? '!v3-bg-scheduled !v3-text-primary' : '' ?>">
                                        <span class="flex min-w-0 items-center gap-3">
                                            <span class="tenant-menu-row__icon <?= ($m['clinic_id'] == session()->get('active_clinic_id')) ? '!v3-bg-primary !v3-text-on-primary' : '' ?>">
                                                <i class="fas fa-hospital text-sm"></i>
                                            </span>
                                            <span class="flex min-w-0 flex-col overflow-hidden">
                                                <span class="truncate text-sm font-black"><?= esc($m['clinic_name']) ?></span>
                                                <span class="text-[11px] font-bold opacity-65"><?= esc($m['clinic_slug'] ?? ('ID ' . $m['clinic_id'])) ?></span>
                                            </span>
                                        </span>
                                        <?php if ($m['clinic_id'] == session()->get('active_clinic_id')): ?>
                                            <span class="w-5 h-5 v3-bg-primary rounded-full flex items-center justify-center flex-shrink-0 v3-elevation-none">
                                                <i class="fas fa-check v3-text-on-primary text-[8px]"></i>
                                            </span>
                                        <?php endif; ?>
                                    </a>
                                <?php endforeach; ?>
                            </div>
                            <div class="tenant-header-dropdown__footer p-3 text-center">
                                <a href="<?= base_url('clinic/select') ?>" class="inline-flex items-center space-x-1 text-[11px] v3-text-primary v3-hover-text-primary font-black uppercase tracking-wider transition-colors">
                                    <i class="fas fa-th-large text-[10px]"></i>
                                    <span><?= lang('UI.allClinics') ?></span>
                                </a>
                            </div>
                        </div>
                    </div>
                    <div class="hidden sm:block h-8 w-px v3-bg-sand mx-1 mobile-hide-compact"></div>
                    <?php endif; ?>

                    <!-- System Action Icons -->
                    <div class="lg:flex items-center space-x-2 hidden">
                        <!-- New Patient -->
                        <div class="relative group">
                            <a href="<?= base_url('patient/create') ?>" data-patient-create-modal-open class="tenant-header-button">
                                <i class="fas fa-user-plus text-sm"></i>
                            </a>
                            <div class="absolute top-full mt-2 left-1/2 transform -translate-x-1/2 v3-bg-forest v3-text-on-primary text-xs font-medium px-2 py-1 rounded-lg opacity-0 group-hover:opacity-100 transition-opacity duration-200 whitespace-nowrap pointer-events-none z-50">
                                <?= lang('UI.newPatient') ?>
                                <div class="absolute -top-1 left-1/2 transform -translate-x-1/2 w-2 h-2 v3-bg-forest rotate-45"></div>
                            </div>
                        </div>

                        <!-- New Appointment -->
                        <div class="relative group">
                            <a href="<?= base_url('appointment/create') ?>" data-appointment-create-modal-open class="tenant-header-button">
                                <i class="fas fa-calendar-plus text-sm"></i>
                            </a>
                            <div class="absolute top-full mt-2 left-1/2 transform -translate-x-1/2 v3-bg-forest v3-text-on-primary text-xs font-medium px-2 py-1 rounded-lg opacity-0 group-hover:opacity-100 transition-opacity duration-200 whitespace-nowrap pointer-events-none z-50">
                                <?= lang('UI.newAppointment') ?>
                                <div class="absolute -top-1 left-1/2 transform -translate-x-1/2 w-2 h-2 v3-bg-forest rotate-45"></div>
                            </div>
                        </div>

                        <!-- New Examination -->
                        <div class="relative group">
                            <a href="<?= base_url('examination/create') ?>" data-examination-create-modal-open class="tenant-header-button">
                                <i class="fas fa-stethoscope text-sm"></i>
                            </a>
                            <div class="absolute top-full mt-2 left-1/2 transform -translate-x-1/2 v3-bg-forest v3-text-on-primary text-xs font-medium px-2 py-1 rounded-lg opacity-0 group-hover:opacity-100 transition-opacity duration-200 whitespace-nowrap pointer-events-none z-50">
                                <?= lang('UI.newExamination') ?>
                                <div class="absolute -top-1 left-1/2 transform -translate-x-1/2 w-2 h-2 v3-bg-forest rotate-45"></div>
                            </div>
                        </div>

                        <!-- Notifications -->
                        <div class="relative group">
                            <button id="notifications-trigger" class="tenant-header-button" type="button" onclick="toggleNotifications()" aria-haspopup="dialog" aria-expanded="false" aria-controls="notifications-dropdown" aria-label="<?= esc(lang('UI.notificationOpenCenter')) ?>">
                                <i class="fas fa-bell text-sm"></i>
                                <span id="notification-count" class="absolute -top-1 <?= !empty($is_rtl) ? '-left-1' : '-right-1' ?> v3-bg-danger v3-text-on-primary text-xs rounded-full min-w-[1rem] h-4 px-1 inline-flex items-center justify-center font-bold v3-elevation-none hidden text-[10px]">0</span>
                            </button>
                            <div class="absolute top-full mt-2 left-1/2 transform -translate-x-1/2 v3-bg-forest v3-text-on-primary text-xs font-medium px-2 py-1 rounded-lg opacity-0 group-hover:opacity-100 transition-opacity duration-200 whitespace-nowrap pointer-events-none z-50">
                                <?= lang('UI.notifications') ?>
                                <div class="absolute -top-1 left-1/2 transform -translate-x-1/2 w-2 h-2 v3-bg-forest rotate-45"></div>
                            </div>
                        </div>

                    </div>

                    <!-- User Profile Dropdown -->
                    <div class="relative group">
                        <button id="user-menu-trigger" aria-label="<?= esc(lang('UI.profileMenu')) ?>" type="button" onclick="toggleUserMenu()" class="tenant-header-button" aria-haspopup="menu" aria-expanded="false" aria-controls="userMenu">
                            <i class="fas fa-user text-sm"></i>
                        </button>
                        <div class="absolute top-full mt-2 left-1/2 transform -translate-x-1/2 v3-bg-forest v3-text-on-primary text-xs font-medium px-2 py-1 rounded-lg opacity-0 group-hover:opacity-100 transition-opacity duration-200 whitespace-nowrap pointer-events-none z-50">
                            <?= lang('UI.profileMenu') ?>
                            <div class="absolute -top-1 left-1/2 transform -translate-x-1/2 w-2 h-2 v3-bg-forest rotate-45"></div>
                        </div>

                        <!-- User Menu Dropdown -->
                        <div id="userMenu" class="tenant-header-dropdown absolute <?= !empty($is_rtl) ? 'left-0 origin-top-left' : 'right-0 origin-top-right' ?> mt-3 hidden w-72 max-w-[calc(100vw-2rem)] z-50">
                            <div class="tenant-header-dropdown__header p-4">
                                <div class="flex items-center space-x-3">
                                    <div class="w-12 h-12 v3-radius flex items-center justify-center v3-elevation-none">
                                        <i class="fas fa-user v3-text-on-primary text-lg"></i>
                                    </div>
                                    <div class="min-w-0">
                                        <div class="truncate text-sm font-black v3-text-ink">
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
                                        <div class="truncate text-xs font-bold v3-text-muted">
                                            <?= isset($user) && $user && property_exists($user, 'email') ? htmlspecialchars($user->email) : 'user@example.com' ?>
                                        </div>
                                        <div class="mt-2 inline-flex rounded-full border v3-border-default v3-bg-scheduled px-2 py-1 text-[11px] font-black v3-text-primary">
                                            <?= isset($user_groups) && !empty($user_groups) ? $user_groups[0]->name : 'User' ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="p-2">
                                <a href="<?= base_url('profile') ?>" class="tenant-menu-row">
                                    <span class="tenant-menu-row__icon"><i class="fas fa-user-cog text-sm"></i></span>
                                    <span><?= lang('UI.profileSettings') ?></span>
                                </a>
                                <a href="<?= base_url('settings') ?>" class="tenant-menu-row">
                                    <span class="tenant-menu-row__icon"><i class="fas fa-cog text-sm"></i></span>
                                    <span><?= lang('UI.systemSettings') ?></span>
                                </a>
                                <a href="<?= base_url('clinic/select') ?>" class="tenant-menu-row">
                                    <span class="tenant-menu-row__icon"><i class="fas fa-exchange-alt text-sm"></i></span>
                                    <span><?= lang('UI.switchClinic') ?></span>
                                </a>

                                <?php if (!empty($is_super_admin) && empty($global_mode)): ?>
                                    <div class="border-t v3-border-default my-2"></div>
                                    <form action="<?= base_url('controlplane/enter') ?>" method="post">
                                        <?= csrf_field() ?>
                                        <button type="submit" class="tenant-menu-row w-full text-start !v3-text-chair hover:!v3-bg-chair">
                                            <span class="tenant-menu-row__icon !v3-text-chair !v3-bg-chair"><i class="fas fa-rocket text-sm"></i></span>
                                            <span><?= lang('UI.enterControlPlane') ?></span>
                                        </button>
                                    </form>
                                <?php endif; ?>

                                <div class="border-t v3-border-default my-2"></div>
                                <a href="<?= base_url('auth/logout') ?>" class="tenant-menu-row tenant-menu-row--danger">
                                    <span class="tenant-menu-row__icon"><i class="fas fa-sign-out-alt text-sm"></i></span>
                                    <span><?= lang('UI.signOut') ?></span>
                                </a>
                            </div>
                        </div>
                    </div>

                    <!-- Logout Button -->
                    <div class="relative group mobile-hide-compact">
                        <a href="<?= base_url('auth/logout') ?>" class="tenant-header-button hover:!v3-text-danger">
                            <i class="fas fa-sign-out-alt text-sm"></i>
                        </a>
                        <div class="absolute top-full mt-2 left-1/2 transform -translate-x-1/2 v3-bg-forest v3-text-on-primary text-xs font-medium px-2 py-1 rounded-lg opacity-0 group-hover:opacity-100 transition-opacity duration-200 whitespace-nowrap pointer-events-none z-50">
                            <?= lang('UI.logout') ?>
                            <div class="absolute -top-1 left-1/2 transform -translate-x-1/2 w-2 h-2 v3-bg-forest rotate-45"></div>
                        </div>
                    </div>
                </div>
            </div>
        </header>

        <!-- Main Content -->
        <main id="workspace-content" tabindex="-1" class="v3-workspace-canvas">
            <!-- Flash Messages -->
            <?php if (session()->getFlashdata('message')): ?>
                <noscript><div class="mb-4 p-4 v3-bg-paid border v3-border-default v3-text-primary rounded-lg" role="status" aria-live="polite">
                    <?= session()->getFlashdata('message') ?>
                </div></noscript>
            <?php endif; ?>

            <?php if (session()->getFlashdata('error')): ?>
                <noscript><div class="mb-4 p-4 v3-bg-alert border v3-border-default v3-text-danger rounded-lg" role="alert" aria-live="assertive">
                    <?= session()->getFlashdata('error') ?>
                </div></noscript>
            <?php endif; ?>

            <!-- Breadcrumb Bar -->
            <?php
            try {
                $breadcrumbTrail = service('navigation')->trail();
            } catch (\Throwable $e) {
                $breadcrumbTrail = [];
            }
            ?>
            <?php if (count($breadcrumbTrail) > 1): ?>
            <div class="mb-4 -mt-2">
                <nav class="flex items-center gap-1.5 text-[11px] v3-text-muted font-medium overflow-x-auto whitespace-nowrap">
                    <?php foreach ($breadcrumbTrail as $i => $step): ?>
                        <?php if ($i > 0): ?><span class="v3-text-muted">/</span><?php endif; ?>
                        <a href="<?= esc($step['url']) ?>"
                           class="v3-hover-text-ink transition-colors <?= $i === count($breadcrumbTrail) - 1 ? 'v3-text-muted font-semibold' : '' ?>">
                            <?= esc($step['label']) ?>
                        </a>
                    <?php endforeach; ?>
                </nav>
            </div>
            <?php endif; ?>

            <!-- Page Content -->
            <?= $this->renderSection('content') ?>
        </main>
    </div>

    <?php
    $layoutQuickCreateUri = trim(uri_string(), '/');
    $layoutRendersQuickCreateModals = (
        (has_permission('patients', 'create') && !in_array($layoutQuickCreateUri, ['patient', 'patients', 'patient/create', 'patients/create'], true) && !preg_match('#^patients?/\d+/edit$#', $layoutQuickCreateUri))
        || (has_permission('appointments', 'create') && !in_array($layoutQuickCreateUri, ['appointment', 'appointments', 'appointment/create', 'appointments/create'], true) && !preg_match('#^examinations?/\d+$#', $layoutQuickCreateUri))
        || (has_permission('examinations', 'create') && !in_array($layoutQuickCreateUri, ['examination', 'examinations', 'examination/create', 'examinations/create'], true))
    );
    ?>
    <?php if ($layoutRendersQuickCreateModals): ?>
        <?= view('components/global_quick_create_modals', ['is_rtl' => !empty($is_rtl)]) ?>
    <?php endif; ?>

    <!-- Notifications Dropdown - Outside main content to avoid overflow issues -->
    <div id="notifications-dropdown" dir="<?= !empty($is_rtl) ? 'rtl' : 'ltr' ?>" class="tenant-header-dropdown absolute hidden flex-col" role="dialog" aria-modal="false" aria-labelledby="notifications-title" style="z-index: 2147483647 !important; width: min(18rem, calc(100vw - 1rem)); max-width: calc(100vw - 1rem); height: min(23rem, calc(100vh - 5rem));">
        <div class="tenant-header-dropdown__header shrink-0 px-4 py-4">
            <div class="flex items-start justify-between gap-3">
                <div class="min-w-0">
                    <h3 id="notifications-title" class="text-sm font-black v3-text-ink"><?= lang('UI.notificationCenterTitle') ?></h3>
                    <p id="notifications-summary" class="mt-1 text-xs font-bold v3-text-muted"><?= lang('UI.notificationCenterDesc') ?></p>
                </div>
                <button type="button" onclick="markAllAsRead()" class="shrink-0 v3-radius border v3-border-default v3-bg-panel px-3 py-1.5 text-xs font-black v3-text-primary v3-elevation-none transition v3-hover-primary"><?= lang('UI.markAllAsRead') ?></button>
            </div>
        </div>
        <div id="notifications-list" class="min-h-0 flex-1 overflow-y-auto px-2 py-2.5" role="list" style="overscroll-behavior: contain; -webkit-overflow-scrolling: touch;"></div>
        <div class="tenant-header-dropdown__footer shrink-0 px-4 py-3">
            <a href="<?= base_url('notifications') ?>" class="inline-flex items-center text-sm font-black v3-text-primary v3-hover-text-primary"><?= lang('UI.viewAllNotifications') ?></a>
        </div>
    </div>
</div>


<!-- JavaScript -->
<script>
    // Global CSRF configuration
    window.appBaseUrl = '<?= rtrim(base_url(), '/') ?>/';
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

    // V3 navigation accessibility shares the existing sidebar state.
    function syncSidebarAccessibility() {
        const sidebar = document.getElementById('sidebar');
        const toggle = document.getElementById('sidebar-toggle');
        const mobile = window.innerWidth < 768;
        const hidden = mobile && sidebar.classList.contains('sidebar-mobile-hidden');
        sidebar.inert = hidden;
        if (hidden) sidebar.setAttribute('aria-hidden', 'true');
        else sidebar.removeAttribute('aria-hidden');
        toggle?.setAttribute('aria-expanded', String(mobile ? !hidden : !sidebar.classList.contains('sidebar-collapsed')));
    }
    document.addEventListener('keydown', function(event) {
        const sidebar = document.getElementById('sidebar');
        if (event.key !== 'Tab' || window.innerWidth >= 768 || sidebar.classList.contains('sidebar-mobile-hidden')) return;
        const links = Array.from(sidebar.querySelectorAll('a[href],button:not([disabled])')).filter(el => el.getClientRects().length);
        if (!links.length) return;
        const first = links[0], last = links[links.length - 1];
        if (event.shiftKey && document.activeElement === first) {event.preventDefault();last.focus();}
        else if (!event.shiftKey && document.activeElement === last) {event.preventDefault();first.focus();}
    });
    // Sidebar toggle functionality
    function toggleSidebar() {
        const sidebar = document.getElementById('sidebar');
        const backdrop = document.getElementById('sidebar-backdrop');
        const isMobile = window.innerWidth < 768;

        if (isMobile) {
            sidebar.classList.toggle('sidebar-mobile-hidden');
            sidebar.classList.remove('sidebar-collapsed');
            backdrop.classList.toggle('hidden', sidebar.classList.contains('sidebar-mobile-hidden'));
            document.body.classList.toggle('overflow-hidden', !sidebar.classList.contains('sidebar-mobile-hidden'));
        } else {
            sidebar.classList.toggle('sidebar-collapsed');
            sidebar.classList.remove('sidebar-mobile-hidden');

            // Persist state
            const isCollapsed = sidebar.classList.contains('sidebar-collapsed');
            localStorage.setItem('sidebar-collapsed', isCollapsed);
        }
        syncSidebarAccessibility();
        if (isMobile && !sidebar.inert) sidebar.querySelector('a')?.focus();
    }

    function closeMobileSidebar() {
        if (window.innerWidth >= 768) return;
        const sidebar = document.getElementById('sidebar');
        const backdrop = document.getElementById('sidebar-backdrop');
        sidebar.classList.add('sidebar-mobile-hidden');
        backdrop.classList.add('hidden');
        document.body.classList.remove('overflow-hidden');
        syncSidebarAccessibility();
        document.getElementById('sidebar-toggle')?.focus();
    }

    function bindSidebarScrollBridge() {
        const sidebar = document.getElementById('sidebar');
        const nav = document.getElementById('sidebar-nav');
        if (!sidebar || !nav || sidebar.dataset.scrollBridgeBound === 'true') return;

        const isScrollableTarget = (target) => {
            if (!(target instanceof Element)) return false;
            return !!target.closest('#sidebar-nav, #sidebar-nav *');
        };

        sidebar.addEventListener('wheel', function(event) {
            if (isScrollableTarget(event.target)) return;
            if (nav.scrollHeight <= nav.clientHeight) return;

            nav.scrollTop += event.deltaY;
            event.preventDefault();
        }, { passive: false });

        sidebar.dataset.scrollBridgeBound = 'true';
    }

    // Initialize sidebar state on page load
    document.addEventListener('DOMContentLoaded', function() {
        const sidebar = document.getElementById('sidebar');
        const backdrop = document.getElementById('sidebar-backdrop');
        const isMobile = window.innerWidth < 768;

        bindSidebarScrollBridge();
        syncSidebarAccessibility();

        if (isMobile) {
            sidebar.classList.add('sidebar-mobile-hidden');
            sidebar.classList.remove('sidebar-collapsed');
            backdrop.classList.add('hidden');
            document.body.classList.remove('overflow-hidden');
        }

        syncSidebarAccessibility();
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
        const backdrop = document.getElementById('sidebar-backdrop');
        const isMobile = window.innerWidth < 768;

        if (isMobile) {
            sidebar.classList.add('sidebar-mobile-hidden');
            sidebar.classList.remove('sidebar-collapsed');
            backdrop.classList.add('hidden');
            document.body.classList.remove('overflow-hidden');
        } else {
            sidebar.classList.remove('sidebar-mobile-hidden');
            backdrop.classList.add('hidden');
            document.body.classList.remove('overflow-hidden');
        }
        syncSidebarAccessibility();
    });


    const notificationsApiUrl = '<?= base_url('api/notifications') ?>';
    const notificationsListUrl = '<?= base_url('notifications') ?>';
    const notificationsMarkReadUrl = '<?= base_url('notifications/mark-read') ?>';
    const notificationsUi = {
        isRtl: document.documentElement.dir === 'rtl',
        empty: <?= json_encode(lang('UI.notificationEmpty')) ?>,
        emptyDesc: <?= json_encode(lang('UI.notificationEmptyDesc')) ?>,
        loadFailed: <?= json_encode(lang('UI.notificationLoadFailed')) ?>,
        retry: <?= json_encode(lang('UI.notificationRetry')) ?>,
        by: <?= json_encode(lang('UI.notificationByUser')) ?>,
        unreadCount: <?= json_encode(lang('UI.notificationUnreadCount')) ?>,
        allCaughtUp: <?= json_encode(lang('UI.notificationAllCaughtUp')) ?>,
        justNow: <?= json_encode(lang('UI.notificationJustNow')) ?>,
        minuteAgo: <?= json_encode(lang('UI.notificationMinuteAgo')) ?>,
        minutesAgo: <?= json_encode(lang('UI.notificationMinutesAgo')) ?>,
        hourAgo: <?= json_encode(lang('UI.notificationHourAgo')) ?>,
        hoursAgo: <?= json_encode(lang('UI.notificationHoursAgo')) ?>,
        dayAgo: <?= json_encode(lang('UI.notificationDayAgo')) ?>,
        daysAgo: <?= json_encode(lang('UI.notificationDaysAgo')) ?>,
    };
    const notificationsState = {
        items: [],
        count: 0,
        loaded: false,
        loading: false,
    };

    function setNotificationTriggerExpanded(expanded) {
        const trigger = document.getElementById('notifications-trigger');
        if (trigger) {
            trigger.setAttribute('aria-expanded', expanded ? 'true' : 'false');
        }
    }

    function positionNotificationsDropdown() {
        const dropdown = document.getElementById('notifications-dropdown');
        const trigger = document.getElementById('notifications-trigger');
        if (!dropdown || !trigger) {
            return;
        }

        const viewportPadding = 8;
        const triggerRect = trigger.getBoundingClientRect();
        const preferredWidth = Math.min(288, window.innerWidth - (viewportPadding * 2));
        const availableHeight = Math.max(240, window.innerHeight - triggerRect.bottom - 16);
        const dropdownHeight = Math.min(368, availableHeight);

        dropdown.style.width = `${preferredWidth}px`;
        dropdown.style.maxWidth = `calc(100vw - ${viewportPadding * 2}px)`;
        dropdown.style.height = `${dropdownHeight}px`;
        dropdown.style.maxHeight = `${dropdownHeight}px`;
        dropdown.style.top = `${window.scrollY + Math.max(triggerRect.bottom + 8, viewportPadding)}px`;

        const isRtl = document.documentElement.dir === 'rtl';
        const unclampedLeft = isRtl
            ? triggerRect.left
            : triggerRect.right - preferredWidth;
        const left = Math.min(
            Math.max(unclampedLeft, viewportPadding),
            window.innerWidth - preferredWidth - viewportPadding
        );

        dropdown.style.left = `${window.scrollX + left}px`;
        dropdown.style.right = 'auto';
    }

    function setUserMenuExpanded(expanded) {
        const trigger = document.getElementById('user-menu-trigger');
        if (trigger) {
            trigger.setAttribute('aria-expanded', expanded ? 'true' : 'false');
        }
    }

    function closeHeaderMenus(exceptId = null) {
        const notificationsDropdown = document.getElementById('notifications-dropdown');
        const userMenu = document.getElementById('userMenu');
        const clinicMenu = document.getElementById('clinicSwitcherMenu');

        if (notificationsDropdown && exceptId !== 'notifications-dropdown') {
            notificationsDropdown.classList.add('hidden');
            notificationsDropdown.classList.remove('flex');
            setNotificationTriggerExpanded(false);
        }

        if (userMenu && exceptId !== 'userMenu') {
            userMenu.classList.add('hidden');
            setUserMenuExpanded(false);
        }

        if (clinicMenu && exceptId !== 'clinicSwitcherMenu') {
            clinicMenu.classList.add('hidden');
        }
    }

    function toggleNotifications() {
        const dropdown = document.getElementById('notifications-dropdown');
        const willShow = dropdown.classList.contains('hidden');
        closeHeaderMenus(willShow ? 'notifications-dropdown' : null);

        dropdown.classList.toggle('hidden', !willShow);
        dropdown.classList.toggle('flex', willShow);
        setNotificationTriggerExpanded(willShow);

        if (willShow) {
            positionNotificationsDropdown();
            loadNotifications({ showList: true });
        }
    }

    function toggleUserProfile() {
        toggleUserMenu();
    }

    function loadNotifications({ showList = false } = {}) {
        if (notificationsState.loading) {
            return Promise.resolve();
        }

        notificationsState.loading = true;

        return fetch(notificationsApiUrl, {
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
            .then((response) => {
                if (!response.ok) {
                    throw new Error(`HTTP ${response.status}`);
                }

                return response.text().then((text) => {
                    const normalized = text.replace(/^\uFEFF/, '').trim();
                    return normalized ? JSON.parse(normalized) : {};
                });
            })
            .then((data) => {
                if (data.csrf_token && window.refreshCsrfToken) {
                    window.refreshCsrfToken(data.csrf_token);
                }

                if (!data.success) {
                    throw new Error(data.message || notificationsUi.loadFailed);
                }

                notificationsState.items = Array.isArray(data.notifications) ? data.notifications : [];
                notificationsState.count = Number(data.count || 0);
                notificationsState.loaded = true;
                updateNotificationCount(notificationsState.count);

                if (showList || !document.getElementById('notifications-dropdown').classList.contains('hidden')) {
                    displayNotifications(notificationsState.items);
                } else {
                    renderNotificationSummary();
                }
            })
            .catch((error) => {
                console.error('Error loading notifications:', error);
                notificationsState.loaded = false;
                updateNotificationCount(0);

                if (showList || !document.getElementById('notifications-dropdown').classList.contains('hidden')) {
                    displayNotificationError();
                } else {
                    renderNotificationSummary();
                }
            })
            .finally(() => {
                notificationsState.loading = false;
            });
    }

    function updateNotificationCount(count) {
        const countElement = document.getElementById('notification-count');
        const unreadCount = Number(count || 0);

        if (unreadCount > 0) {
            countElement.textContent = unreadCount > 99 ? '99+' : String(unreadCount);
            countElement.classList.remove('hidden');
        } else {
            countElement.classList.add('hidden');
            countElement.textContent = '0';
        }

        renderNotificationSummary();
    }

    function renderNotificationSummary() {
        const summary = document.getElementById('notifications-summary');
        if (!summary) {
            return;
        }

        if (!notificationsState.loaded) {
            summary.textContent = notificationsUi.loadFailed;
            return;
        }

        summary.textContent = notificationsState.count > 0
            ? notificationsUi.unreadCount.replace('{count}', notificationsState.count)
            : notificationsUi.allCaughtUp;
    }

    function displayNotifications(notifications) {
        const listElement = document.getElementById('notifications-list');
        listElement.textContent = '';

        if (!notifications.length) {
            const emptyState = document.createElement('div');
            emptyState.className = 'px-4 py-8 text-center';

            const iconWrap = document.createElement('div');
            iconWrap.className = 'mx-auto flex h-12 w-12 items-center justify-center rounded-full v3-bg-soft v3-text-muted';

            const icon = document.createElement('i');
            icon.className = 'fas fa-bell-slash text-lg';
            iconWrap.appendChild(icon);

            const title = document.createElement('p');
            title.className = 'mt-4 text-sm font-semibold v3-text-ink';
            title.textContent = notificationsUi.empty;

            const description = document.createElement('p');
            description.className = 'mt-1 text-sm v3-text-muted';
            description.textContent = notificationsUi.emptyDesc;

            emptyState.appendChild(iconWrap);
            emptyState.appendChild(title);
            emptyState.appendChild(description);
            listElement.appendChild(emptyState);
            return;
        }

        const fragment = document.createDocumentFragment();

        notifications.forEach((notification) => {
            const item = document.createElement('button');
            item.type = 'button';
            item.className = 'block w-full v3-radius border v3-border-default v3-bg-panel px-3.5 py-3 text-start v3-elevation-none transition hover:-translate-y-0.5 v3-hover-primary v3-focus-control focus:outline-none';
            item.setAttribute('role', 'listitem');
            item.onclick = async function () {
                if (!notification.is_read) {
                    await markNotificationAsRead(notification.id, { silent: true });
                }

                navigateToNotificationTarget(notification);
            };

            const row = document.createElement('div');
            row.className = `flex items-start gap-3 ${notificationsUi.isRtl ? 'text-right' : 'text-left'}`;

            const iconWrap = document.createElement('div');
            iconWrap.className = `mt-0.5 flex h-9 w-9 shrink-0 items-center justify-center v3-radius ${getNotificationIconBgClass(notification.color)}`;

            const icon = document.createElement('i');
            icon.className = `${notification.icon} ${getNotificationIconClass(notification.color)} text-sm`;
            iconWrap.appendChild(icon);

            const content = document.createElement('div');
            content.className = 'min-w-0 flex-1';

            const header = document.createElement('div');
            header.className = 'flex items-start justify-between gap-3';

            const textWrap = document.createElement('div');
            textWrap.className = 'min-w-0 flex-1';

            const title = document.createElement('p');
            title.className = 'text-[13px] font-black leading-5 v3-text-ink';
            title.textContent = notification.title || '';

            const message = document.createElement('p');
            message.className = 'mt-1 line-clamp-2 text-[13px] font-semibold leading-5 v3-text-muted';
            message.textContent = notification.message || '';

            textWrap.appendChild(title);
            textWrap.appendChild(message);
            header.appendChild(textWrap);

            if (!notification.is_read) {
                const unread = document.createElement('span');
                unread.className = 'mt-1 inline-flex h-2.5 w-2.5 shrink-0 rounded-full v3-bg-primary';
                unread.setAttribute('aria-hidden', 'true');
                header.appendChild(unread);
            }

            const meta = document.createElement('div');
            meta.className = `mt-2 flex flex-wrap items-center gap-x-3 gap-y-1 text-xs font-bold v3-text-muted ${notificationsUi.isRtl ? 'justify-start' : ''}`;

            const time = document.createElement('span');
            time.textContent = formatTime(notification.created_at);
            meta.appendChild(time);

            if (notification.user_name) {
                const user = document.createElement('span');
                user.textContent = notificationsUi.by.replace('{name}', notification.user_name);
                meta.appendChild(user);
            }

            if (notification.type_label) {
                const badge = document.createElement('span');
                badge.className = 'inline-flex items-center rounded-full border v3-border-default v3-bg-soft px-2 py-0.5 font-black v3-text-muted';
                badge.textContent = notification.type_label;
                meta.appendChild(badge);
            }

            content.appendChild(header);
            content.appendChild(meta);

            row.appendChild(iconWrap);
            row.appendChild(content);
            item.appendChild(row);
            fragment.appendChild(item);
        });

        listElement.appendChild(fragment);

        const listScroller = document.getElementById('notifications-list');
        if (listScroller) {
            listScroller.scrollTop = 0;
        }
    }

    function displayNotificationError() {
        const listElement = document.getElementById('notifications-list');
        listElement.textContent = '';

        const state = document.createElement('div');
        state.className = 'px-4 py-8 text-center';

        const iconWrap = document.createElement('div');
        iconWrap.className = 'mx-auto flex h-12 w-12 items-center justify-center rounded-full v3-bg-alert v3-text-danger';

        const icon = document.createElement('i');
        icon.className = 'fas fa-bell-slash text-lg';
        iconWrap.appendChild(icon);

        const title = document.createElement('p');
        title.className = 'mt-4 text-sm font-semibold v3-text-ink';
        title.textContent = notificationsUi.loadFailed;

        const retry = document.createElement('button');
        retry.type = 'button';
        retry.className = 'mt-4 inline-flex items-center rounded-lg border v3-border-default px-3 py-2 text-sm font-semibold v3-text-ink transition v3-hover-soft';
        retry.textContent = notificationsUi.retry;
        retry.onclick = function () {
            loadNotifications({ showList: true });
        };

        state.appendChild(iconWrap);
        state.appendChild(title);
        state.appendChild(retry);
        listElement.appendChild(state);
    }

    function getNotificationIconBgClass(color) {
        const colorMap = {
            blue: 'v3-bg-scheduled',
            green: 'v3-bg-paid',
            yellow: 'v3-bg-waiting',
            purple: 'v3-bg-chair',
            red: 'v3-bg-alert',
            gray: 'v3-bg-soft',
        };

        return colorMap[color] || 'v3-bg-soft';
    }

    function getNotificationIconClass(color) {
        const colorMap = {
            blue: 'v3-text-primary',
            green: 'v3-text-primary',
            yellow: 'v3-text-warning',
            purple: 'v3-text-chair',
            red: 'v3-text-danger',
            gray: 'v3-text-muted',
        };

        return colorMap[color] || 'v3-text-muted';
    }

    function formatTime(dateString) {
        if (!dateString) {
            return notificationsUi.justNow;
        }

        const date = new Date(dateString);
        const now = new Date();
        const diffInMinutes = Math.floor((now - date) / (1000 * 60));

        if (diffInMinutes < 1) {
            return notificationsUi.justNow;
        }

        if (diffInMinutes < 60) {
            return diffInMinutes === 1
                ? notificationsUi.minuteAgo
                : notificationsUi.minutesAgo.replace('{count}', diffInMinutes);
        }

        if (diffInMinutes < 1440) {
            const hours = Math.floor(diffInMinutes / 60);
            return hours === 1
                ? notificationsUi.hourAgo
                : notificationsUi.hoursAgo.replace('{count}', hours);
        }

        const days = Math.floor(diffInMinutes / 1440);
        return days === 1
            ? notificationsUi.dayAgo
            : notificationsUi.daysAgo.replace('{count}', days);
    }

    function markNotificationAsRead(id, { silent = false } = {}) {
        if (!id) {
            return Promise.resolve();
        }

        return fetch(`${notificationsMarkReadUrl}/${id}`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': typeof csrfToken !== 'undefined' ? csrfToken : ''
            },
            body: JSON.stringify({
                [csrfTokenName]: typeof csrfToken !== 'undefined' ? csrfToken : ''
            })
        })
            .then((response) => response.json())
            .then((data) => {
                if (data.csrf_token && window.refreshCsrfToken) {
                    window.refreshCsrfToken(data.csrf_token);
                }

                if (!data.success) {
                    throw new Error(data.message || 'mark-read-failed');
                }

                notificationsState.items = notificationsState.items.map((item) => {
                    if (Number(item.id) === Number(id)) {
                        return { ...item, is_read: true };
                    }

                    return item;
                });
                notificationsState.count = notificationsState.items.filter((item) => !item.is_read).length;
                updateNotificationCount(notificationsState.count);

                if (!silent) {
                    displayNotifications(notificationsState.items);
                }
            })
            .catch((error) => {
                console.error('Error marking notification as read:', error);
            });
    }

    function markAllAsRead() {
        return fetch(notificationsMarkReadUrl, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': typeof csrfToken !== 'undefined' ? csrfToken : ''
            },
            body: JSON.stringify({
                [csrfTokenName]: typeof csrfToken !== 'undefined' ? csrfToken : ''
            })
        })
            .then((response) => response.json())
            .then((data) => {
                if (data.csrf_token && window.refreshCsrfToken) {
                    window.refreshCsrfToken(data.csrf_token);
                }

                if (!data.success) {
                    throw new Error(data.message || 'mark-all-read-failed');
                }

                notificationsState.items = notificationsState.items.map((item) => ({
                    ...item,
                    is_read: true
                }));
                notificationsState.count = 0;
                updateNotificationCount(0);

                if (!document.getElementById('notifications-dropdown').classList.contains('hidden')) {
                    displayNotifications(notificationsState.items);
                }
            })
            .catch((error) => {
                console.error('Error marking all notifications as read:', error);
            });
    }

    window.workflowContextUrl = function(target, returnTo) {
        if (!target || target === '#') {
            return target;
        }

        try {
            const url = new URL(target, window.location.origin);
            if (url.origin !== window.location.origin || url.searchParams.has('return_to')) {
                return url.toString();
            }

            const context = returnTo || <?= json_encode(workflow_current_target() ?? '') ?>;
            if (!context) {
                return url.toString();
            }

            const basePrefix = <?= json_encode(parse_url(base_url(), PHP_URL_PATH) ? trim((string) parse_url(base_url(), PHP_URL_PATH), '/') . '/' : '') ?>;
            let currentPath = (window.location.pathname + window.location.search).replace(/^\/+/, '');
            if (basePrefix && currentPath.startsWith(basePrefix)) {
                currentPath = currentPath.slice(basePrefix.length);
            }
            if (currentPath === context.replace(/^\/+/, '')) {
                return url.toString();
            }

            url.searchParams.set('return_to', context);
            return url.toString();
        } catch (error) {
            return target;
        }
    };

    function navigateToNotificationTarget(notification) {
        const url = notification && notification.entity_url ? notification.entity_url : notificationsListUrl;
        window.location.href = window.workflowContextUrl(url);
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

    function initializeClinicDateInputs(root) {
        const scope = root || document;
        const fields = scope.querySelectorAll('input[data-clinic-date="1"]');
        const formatHint = clinicDateFormatLabel(clinicDateFormat);
        const isRtl = document.documentElement.getAttribute('dir') === 'rtl';

        fields.forEach((field) => {
            if (field.dataset.clinicDateInputBound === '1') {
                return;
            }
            field.dataset.clinicDateInputBound = '1';
            const current = field.value ? field.value.trim() : '';
            if (current && /^\d{4}-\d{2}-\d{2}$/.test(current)) {
                field.value = formatClinicDateFromIso(current);
            }

            const syncDateFieldChrome = () => {
                const nativePickerActive = field.type === 'date';
                if (nativePickerActive) {
                    if (isRtl) {
                        field.style.paddingLeft = '2.75rem';
                        field.style.paddingRight = '2.75rem';
                    } else {
                        field.style.paddingLeft = '1rem';
                        field.style.paddingRight = '2.75rem';
                    }
                } else {
                    field.style.removeProperty('padding-left');
                    field.style.removeProperty('padding-right');
                }
            };

            field.type = 'text';
            field.setAttribute('placeholder', formatHint);
            field.setAttribute('autocomplete', 'off');
            field.inputMode = 'numeric';
            field.setAttribute('title', `${formatHint} (type manually or press Alt+Down for picker)`);
            syncDateFieldChrome();

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
                syncDateFieldChrome();
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
                syncDateFieldChrome();
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

        scope.querySelectorAll('form').forEach((form) => {
            if (form.dataset.clinicDateBound === '1') {
                return;
            }
            form.dataset.clinicDateBound = '1';
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

    function initializeClinicTimeInputs(root) {
        const scope = root || document;
        const fields = scope.querySelectorAll('input[data-clinic-time="1"]');
        if (!fields.length) {
            return;
        }

        const formatHint = clinicTimeFormat === '24' ? 'HH:MM' : 'h:mm AM/PM';

        fields.forEach((field) => {
            if (field.dataset.clinicTimeInputBound === '1') {
                return;
            }
            field.dataset.clinicTimeInputBound = '1';
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

        scope.querySelectorAll('form').forEach((form) => {
            if (form.dataset.clinicTimeBound === '1') {
                return;
            }
            form.dataset.clinicTimeBound = '1';
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
    window.initializeClinicDateInputs = initializeClinicDateInputs;
    window.initializeClinicTimeInputs = initializeClinicTimeInputs;

    let isSyncingRequiredFieldMarkers = false;

    function runRequiredFieldMarkerSync() {
        if (isSyncingRequiredFieldMarkers) {
            return;
        }

        isSyncingRequiredFieldMarkers = true;
        try {
            syncRequiredFieldMarkers();
        } finally {
            isSyncingRequiredFieldMarkers = false;
        }
    }

    function isRequiredMarkerMutation(mutation) {
        if (mutation.type === 'attributes') {
            return false;
        }

        const nodes = [
            ...Array.from(mutation.addedNodes || []),
            ...Array.from(mutation.removedNodes || []),
        ];

        return nodes.length > 0 && nodes.every((node) => {
            return node.nodeType === Node.ELEMENT_NODE
                && node.classList
                && node.classList.contains('required-indicator');
        });
    }

    function getToggleCheckedTransform() {
        return document.documentElement.dir === 'rtl' ? 'translateX(-20px)' : 'translateX(20px)';
    }

    function applyToggleDotState(dot, checked) {
        if (!dot) {
            return;
        }

        dot.style.transform = checked ? getToggleCheckedTransform() : 'translateX(0)';
    }

    window.applyToggleDotState = applyToggleDotState;

    // Load notifications on page load
    document.addEventListener('DOMContentLoaded', function() {
        initializeClinicDateInputs();
        initializeClinicTimeInputs();
        runRequiredFieldMarkerSync();
        loadNotifications();

        // Handle URL search parameters
        handleUrlSearchParams();

        // Hidden tabs do not poll; returning to the clinic workspace refreshes immediately.
        setInterval(() => {
            if (!document.hidden) { loadNotifications(); }
        }, 30000);
        document.addEventListener('visibilitychange', () => {
            if (!document.hidden) { loadNotifications(); }
        });

        const requiredObserver = new MutationObserver((mutations) => {
            if (isSyncingRequiredFieldMarkers) {
                return;
            }

            const hasRelevantMutation = mutations.some((mutation) => {
                if (mutation.type === 'attributes') {
                    return true;
                }

                return !isRequiredMarkerMutation(mutation);
            });

            if (hasRelevantMutation) {
                runRequiredFieldMarkerSync();
            }
        });

        requiredObserver.observe(document.body, {
            childList: true,
            subtree: true,
            attributes: true,
            attributeFilter: ['required']
        });
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
        return loadNotifications();
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

    function normalizeRequiredLabelText(label) {
        const walker = document.createTreeWalker(label, NodeFilter.SHOW_TEXT);
        const replacements = [];

        while (walker.nextNode()) {
            const node = walker.currentNode;
            const original = node.nodeValue || '';
            const updated = original.replace(/\s*(Required|Ù…Ø·Ù„ÙˆØ¨)\s*$/u, '');
            if (updated !== original) {
                replacements.push([node, updated]);
            }
        }

        replacements.forEach(([node, value]) => {
            node.nodeValue = value;
        });
    }

    function stripExistingRequiredMarkers(label) {
        label.querySelectorAll('.required-indicator').forEach((marker) => marker.remove());

        label.querySelectorAll('span, strong, b, em, small').forEach((node) => {
            if (node.classList.contains('required-indicator')) {
                return;
            }

            if (node.children.length === 0 && node.textContent.trim() === '*') {
                node.remove();
            }
        });

        Array.from(label.childNodes).forEach((node) => {
            if (node.nodeType !== Node.TEXT_NODE) {
                return;
            }

            const updated = (node.nodeValue || '').replace(/\s*\*+\s*$/u, '');
            if (updated !== node.nodeValue) {
                node.nodeValue = updated;
            }
        });
    }

    function getLabelControl(label) {
        if (!label) {
            return null;
        }

        if (label.htmlFor) {
            return document.getElementById(label.htmlFor);
        }

        return label.querySelector('input, select, textarea');
    }

    function syncRequiredFieldMarkers() {
        document.querySelectorAll('form label').forEach((label) => {
            const control = getLabelControl(label);
            if (!control || control.type === 'hidden') {
                return;
            }

            normalizeRequiredLabelText(label);
            stripExistingRequiredMarkers(label);

            if (!control.required) {
                return;
            }

            const marker = document.createElement('span');
            marker.className = 'required-indicator notranslate';
            marker.setAttribute('aria-hidden', 'true');
            marker.textContent = '*';
            label.appendChild(marker);
        });
    }


    // Toggle User Menu Dropdown
    function toggleUserMenu() {
        const menu = document.getElementById('userMenu');
        if (!menu) {
            return;
        }

        const willShow = menu.classList.contains('hidden');
        closeHeaderMenus(willShow ? 'userMenu' : null);
        menu.classList.toggle('hidden', !willShow);
        setUserMenuExpanded(willShow);
    }

    // Toggle Clinic Switcher Dropdown
    function toggleClinicSwitcher() {
        const menu = document.getElementById('clinicSwitcherMenu');
        if (menu) {
            const willShow = menu.classList.contains('hidden');
            closeHeaderMenus(willShow ? 'clinicSwitcherMenu' : null);
            menu.classList.toggle('hidden', !willShow);
        }
    }

    // Close dropdowns when clicking outside
    document.addEventListener('click', function(e) {
        if (!e.target.closest('#notifications-dropdown') && !e.target.closest('#notifications-trigger')) {
            const notificationsDropdown = document.getElementById('notifications-dropdown');
            if (notificationsDropdown && !notificationsDropdown.classList.contains('hidden')) {
                notificationsDropdown.classList.add('hidden');
                notificationsDropdown.classList.remove('flex');
                setNotificationTriggerExpanded(false);
            }
        }

        // Handle User Menu
        if (!e.target.closest('#userMenu') && !e.target.closest('#user-menu-trigger')) {
            const userMenu = document.getElementById('userMenu');
            if (userMenu && !userMenu.classList.contains('hidden')) {
                userMenu.classList.add('hidden');
                setUserMenuExpanded(false);
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

        if (e.key === 'Escape') {
            closeHeaderMenus();
            const sidebar = document.getElementById('sidebar');
            if (window.innerWidth < 768 && !sidebar.classList.contains('sidebar-mobile-hidden')) closeMobileSidebar();
        }
    });

    window.addEventListener('resize', function() {
        const notificationsDropdown = document.getElementById('notifications-dropdown');
        if (notificationsDropdown && !notificationsDropdown.classList.contains('hidden')) {
            positionNotificationsDropdown();
        }
    });

</script>

<!-- Select2 Initialization - Only load on pages that need it -->
<?php if ((isset($loadSelect2) && $loadSelect2) || !empty($layoutRendersQuickCreateModals)): ?>
<script src="<?= base_url('assets/js/select2-init.js') ?>?v=<?= esc(asset_version('assets/js/select2-init.js')) ?>"></script>
<?php endif; ?>
<script src="<?= base_url('assets/js/app-modal.js') ?>?v=<?= esc(asset_version('assets/js/app-modal.js')) ?>"></script>
<script src="<?= base_url('assets/js/navigation-context.js') ?>?v=<?= esc(asset_version('assets/js/navigation-context.js')) ?>"></script>
<script src="<?= base_url('assets/js/app-drawer.js') ?>?v=<?= esc(asset_version('assets/js/app-drawer.js')) ?>"></script>
<script src="<?= base_url('assets/js/contextual-workspace.js') ?>?v=<?= esc(asset_version('assets/js/contextual-workspace.js')) ?>"></script>

</body>
</html>
