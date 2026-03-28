<!DOCTYPE html>
<html lang="<?= esc($locale ?? 'en') ?>" dir="<?= !empty($is_rtl) ? 'rtl' : 'ltr' ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= esc($title ?? lang('UI.cpControlPlane')) ?></title>
    <link rel="icon" type="image/png" href="<?= base_url('assets/images/favicon.png') ?>">
    <link rel="stylesheet" href="<?= base_url('assets/css/tailwind.css') ?>?v=<?= time() ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <?php if (!isset($disableDataTables) || !$disableDataTables): ?>
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/jquery.dataTables.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.2/css/buttons.dataTables.min.css">
    <?php endif; ?>
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <?php if (!isset($disableDataTables) || !$disableDataTables): ?>
    <script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.2/js/dataTables.buttons.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.html5.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.print.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/pdfmake.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/vfs_fonts.js"></script>
    <?php endif; ?>
    <meta name="theme-color" content="#0f172a">
    <meta name="csrf-name" content="<?= csrf_token() ?>">
    <meta name="csrf-token" content="<?= csrf_hash() ?>">
    <meta name="csrf-header" content="<?= csrf_header() ?>">
    <style>
        body {
            /* Keep control plane typography aligned with tenant plane defaults. */
            font-family: ui-sans-serif, system-ui, -apple-system, "Segoe UI", sans-serif;
        }
        /*
         * Control-plane typography contract:
         * Align all CP pages (and tab content) to the /controlplane/roles hierarchy.
         */
        .cp-content {
            color: #374151;
            line-height: 1.5;
            letter-spacing: 0;
        }
        .cp-content h1,
        .cp-content h2,
        .cp-content h3,
        .cp-content h4,
        .cp-content h5,
        .cp-content h6 {
            color: #1f2937;
            font-weight: 700;
            letter-spacing: 0;
            line-height: 1.3;
            margin-top: 0;
        }
        .cp-content h1 { font-size: 2.25rem; margin-bottom: 0.75rem; }
        .cp-content h2 { font-size: 1.5rem; margin-bottom: 0.5rem; }
        .cp-content h3 { font-size: 1.25rem; margin-bottom: 0.5rem; }
        .cp-content h4 { font-size: 1.125rem; margin-bottom: 0.375rem; }
        .cp-content p,
        .cp-content li,
        .cp-content td,
        .cp-content th,
        .cp-content label,
        .cp-content input,
        .cp-content select,
        .cp-content textarea,
        .cp-content button,
        .cp-content a {
            letter-spacing: 0;
        }
        .cp-content p,
        .cp-content li,
        .cp-content td,
        .cp-content label,
        .cp-content input,
        .cp-content select,
        .cp-content textarea,
        .cp-content button,
        .cp-content a {
            font-size: 0.875rem;
        }
        .cp-content th {
            font-size: 0.75rem;
            font-weight: 500;
            color: #6b7280;
        }
        .cp-content .font-black,
        .cp-content .font-extrabold {
            font-weight: 700 !important;
        }
        .cp-content .font-bold {
            font-weight: 700 !important;
        }
        .cp-content .font-semibold {
            font-weight: 600 !important;
        }
        .cp-content .font-medium {
            font-weight: 500 !important;
        }
        .cp-content [class*="tracking-"] {
            letter-spacing: 0 !important;
        }
        .cp-content .uppercase {
            text-transform: none !important;
        }
        .cp-content .text-4xl { font-size: 2.25rem !important; line-height: 1.2 !important; }
        .cp-content .text-3xl { font-size: 1.875rem !important; line-height: 1.25 !important; }
        .cp-content .text-2xl { font-size: 1.5rem !important; line-height: 1.3 !important; }
        .cp-content .text-xl { font-size: 1.25rem !important; line-height: 1.35 !important; }
        .cp-content .text-lg { font-size: 1.125rem !important; line-height: 1.4 !important; }
        .cp-content .text-base { font-size: 1rem !important; }
        .cp-content .text-sm { font-size: 0.875rem !important; }
        .cp-content .text-xs { font-size: 0.75rem !important; }

        .rtl-back-icon {
            transition: transform 0.2s ease;
        }
        [dir="rtl"] .rtl-back-icon {
            transform: scaleX(-1);
        }

        .glass-card {
            background: rgba(255, 255, 255, 0.82);
            border: 1px solid rgba(255, 255, 255, 0.35);
            box-shadow: 0 30px 80px -50px rgba(15, 23, 42, 0.4);
            backdrop-filter: blur(24px);
        }
        .glass-muted {
            background: rgba(255, 255, 255, 0.68);
            border: 1px solid rgba(255, 255, 255, 0.3);
            box-shadow: 0 20px 50px -40px rgba(15, 23, 42, 0.35);
            backdrop-filter: blur(20px);
        }
        /* RTL spacing parity */
        [dir="rtl"] #sidebar-nav ul li a {
            text-align: right;
        }
        [dir="rtl"] #sidebar-nav ul li a i {
            margin-right: 0 !important;
            margin-left: 0.75rem !important;
        }
        [dir="rtl"] .space-x-1 > :not([hidden]) ~ :not([hidden]),
        [dir="rtl"] .space-x-2 > :not([hidden]) ~ :not([hidden]),
        [dir="rtl"] .space-x-3 > :not([hidden]) ~ :not([hidden]),
        [dir="rtl"] .space-x-4 > :not([hidden]) ~ :not([hidden]),
        [dir="rtl"] .space-x-5 > :not([hidden]) ~ :not([hidden]),
        [dir="rtl"] .space-x-6 > :not([hidden]) ~ :not([hidden]) {
            --tw-space-x-reverse: 1;
        }
        [dir="rtl"] i.mr-1 { margin-right: 0 !important; margin-left: 0.25rem !important; }
        [dir="rtl"] i.mr-2 { margin-right: 0 !important; margin-left: 0.5rem !important; }
        [dir="rtl"] i.mr-3 { margin-right: 0 !important; margin-left: 0.75rem !important; }
        [dir="rtl"] i.mr-4 { margin-right: 0 !important; margin-left: 1rem !important; }
        [dir="rtl"] i.mr-5 { margin-right: 0 !important; margin-left: 1.25rem !important; }
        [dir="rtl"] i.mr-6 { margin-right: 0 !important; margin-left: 1.5rem !important; }
        [dir="rtl"] .text-left {
            text-align: right !important;
        }

        .sidebar-mobile-hidden {
            transform: translateX(-100%);
        }

        [dir="rtl"] .sidebar-mobile-hidden {
            transform: translateX(100%);
        }

        @media (max-width: 1023px) {
            #sidebar {
                position: fixed !important;
                top: 0;
                bottom: 0;
                left: 0;
                width: min(22rem, 86vw) !important;
                max-width: 86vw;
            }
            [dir="rtl"] #sidebar {
                left: auto;
                right: 0;
            }
        }
    </style>
</head>
<body class="bg-gradient-to-br from-slate-50 via-indigo-50 to-emerald-50 min-h-screen">
    <?= view('components/toast') ?>

    <div class="flex min-h-screen">
        <?php $hideCpNav = $hide_cp_nav ?? false; ?>
        <?php if (!$hideCpNav): ?>
            <aside class="w-64 backdrop-blur-xl bg-white/80 border-r border-white/30 shadow-2xl shadow-indigo-500/10 z-40 transition-all duration-300 transform flex-shrink-0 lg:translate-x-0 sidebar-mobile-hidden flex flex-col lg:static fixed inset-y-0 left-0" id="sidebar">
                <!-- Sidebar Header -->
                <div class="px-2 pb-2 pt-3 border-b border-white/30" id="sidebar-header">
                    <div class="flex items-center justify-between">
                        <a href="<?= base_url('controlplane/dashboard') ?>" class="flex items-center space-x-3 group cursor-pointer hover:bg-white/30 rounded-xl p-2 -m-2 transition-all duration-200">
                            <div class="relative group">
                                <div class="absolute inset-0 bg-gradient-to-br from-slate-900 via-indigo-700 to-emerald-600 rounded-xl blur-lg opacity-75 group-hover:opacity-100 transition-opacity duration-200"></div>
                                <div class="relative w-10 h-10 bg-gradient-to-br from-slate-900 via-indigo-700 to-emerald-600 rounded-xl flex items-center justify-center shadow-xl group-hover:scale-105 group-hover:rotate-2 transition-all duration-200">
                                    <i class="fas fa-shield-alt text-white text-lg"></i>
                                </div>
                            </div>
                            <div id="sidebar-title" class="min-w-0 flex-shrink-0">
                                <h1 class="text-lg font-black text-gray-900 whitespace-nowrap"><?= lang('UI.cpControlPlane') ?></h1>
                                <div class="flex items-center space-x-1">
                                    <div class="w-1.5 h-1.5 bg-emerald-500 rounded-full animate-pulse"></div>
                                    <p class="text-xs text-gray-500 font-medium whitespace-nowrap"><?= lang('UI.cpGlobalAdmin') ?></p>
                                </div>
                            </div>
                        </a>
                        <button class="group relative p-2 text-gray-600 hover:text-gray-900 hover:bg-white/50 rounded-xl transition-all duration-200 hover:scale-105 lg:block hidden" onclick="toggleSidebar()" title="Toggle Sidebar (Ctrl+B)">
                            <div class="absolute inset-0 bg-gradient-to-r from-indigo-500/10 to-emerald-500/10 rounded-xl blur opacity-0 group-hover:opacity-100 transition-opacity duration-200"></div>
                            <i class="fas fa-bars relative z-10"></i>
                        </button>
                    </div>
                </div>

                <nav class="flex-1 overflow-y-auto" id="sidebar-nav">
                    <!-- Platform -->
                    <div class="p-4">
                        <div class="px-2 pt-5 pb-1">
                            <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">OPERATE</p>
                        </div>
                        <div class="flex items-center space-x-2 mb-4 mt-3">
                            <div class="w-2 h-2 bg-gradient-to-r from-slate-500 to-slate-700 rounded-full animate-pulse"></div>
                            <h3 class="text-xs font-bold text-gray-600 uppercase tracking-wider"><?= lang('UI.cpPlatform') ?></h3>
                        </div>
                        <ul class="space-y-1">
                            <li>
                                <a href="<?= base_url('controlplane/dashboard') ?>" class="group relative flex items-center px-4 py-3 text-sm font-semibold rounded-xl transition-all duration-200 <?= (strpos(uri_string(), 'controlplane/dashboard') === 0) ? 'bg-gradient-to-r from-slate-500 to-slate-600 text-white shadow-lg shadow-slate-500/25 mx-1' : 'text-gray-700 hover:bg-gradient-to-r hover:from-slate-50 hover:to-slate-100 hover:text-slate-900 hover:shadow-md hover:shadow-slate-500/10 mx-1' ?>">
                                    <div class="absolute inset-0 bg-gradient-to-r from-slate-500/10 to-slate-600/10 rounded-xl blur opacity-0 group-hover:opacity-100 transition-opacity duration-200"></div>
                                    <i class="fas fa-tachometer-alt w-5 h-5 mr-3 relative z-10 <?= (strpos(uri_string(), 'controlplane/dashboard') === 0) ? 'text-white' : 'text-slate-500 group-hover:text-slate-700' ?>"></i>
                                    <span class="relative z-10"><?= lang('UI.menuDashboard') ?></span>
                                </a>
                            </li>
                        </ul>
                    </div>

                    <!-- Tenants -->
                    <div class="p-4">
                        <div class="flex items-center space-x-2 mb-4">
                            <div class="w-2 h-2 bg-gradient-to-r from-emerald-500 to-teal-600 rounded-full animate-pulse"></div>
                            <h3 class="text-xs font-bold text-gray-600 uppercase tracking-wider"><?= lang('UI.cpTenants') ?></h3>
                        </div>
                        <ul class="space-y-1">
                            <li>
                                <a href="<?= base_url('controlplane/clinics') ?>" class="group relative flex items-center px-4 py-3 text-sm font-semibold rounded-xl transition-all duration-200 <?= (strpos(uri_string(), 'controlplane/clinics') === 0) ? 'bg-gradient-to-r from-emerald-600 to-teal-600 text-white shadow-lg shadow-emerald-500/25 mx-1' : 'text-gray-700 hover:bg-gradient-to-r hover:from-emerald-50 hover:to-teal-50 hover:text-emerald-900 hover:shadow-md hover:shadow-emerald-500/10 mx-1' ?>">
                                    <div class="absolute inset-0 bg-gradient-to-r from-emerald-500/10 to-teal-500/10 rounded-xl blur opacity-0 group-hover:opacity-100 transition-opacity duration-200"></div>
                                    <i class="fas fa-hospital-user w-5 h-5 mr-3 relative z-10 <?= (strpos(uri_string(), 'controlplane/clinics') === 0) ? 'text-white' : 'text-emerald-600 group-hover:text-emerald-700' ?>"></i>
                                    <span class="relative z-10"><?= lang('UI.cpClinics') ?></span>
                                </a>
                            </li>
                            <li>
                                <a href="<?= base_url('controlplane/users') ?>" class="group relative flex items-center px-4 py-3 text-sm font-semibold rounded-xl transition-all duration-200 <?= (strpos(uri_string(), 'controlplane/users') === 0) ? 'bg-gradient-to-r from-indigo-600 to-violet-600 text-white shadow-lg shadow-indigo-500/25 mx-1' : 'text-gray-700 hover:bg-gradient-to-r hover:from-indigo-50 hover:to-violet-50 hover:text-indigo-900 hover:shadow-md hover:shadow-indigo-500/10 mx-1' ?>">
                                    <div class="absolute inset-0 bg-gradient-to-r from-indigo-500/10 to-violet-500/10 rounded-xl blur opacity-0 group-hover:opacity-100 transition-opacity duration-200"></div>
                                    <i class="fas fa-users w-5 h-5 mr-3 relative z-10 <?= (strpos(uri_string(), 'controlplane/users') === 0) ? 'text-white' : 'text-indigo-600 group-hover:text-indigo-700' ?>"></i>
                                    <span class="relative z-10"><?= lang('UI.cpUsers') ?></span>
                                </a>
                            </li>
                            <li>
                                <a href="<?= base_url('controlplane/onboarding/clinic/create') ?>" class="group relative flex items-center px-4 py-3 text-sm font-semibold rounded-xl transition-all duration-200 <?= (strpos(uri_string(), 'controlplane/onboarding') === 0) ? 'bg-gradient-to-r from-emerald-600 to-teal-600 text-white shadow-lg shadow-emerald-500/25 mx-1' : 'text-gray-700 hover:bg-gradient-to-r hover:from-emerald-50 hover:to-teal-50 hover:text-emerald-900 hover:shadow-md hover:shadow-emerald-500/10 mx-1' ?>">
                                    <div class="absolute inset-0 bg-gradient-to-r from-emerald-500/10 to-teal-500/10 rounded-xl blur opacity-0 group-hover:opacity-100 transition-opacity duration-200"></div>
                                    <i class="fas fa-user-plus w-5 h-5 mr-3 relative z-10 <?= (strpos(uri_string(), 'controlplane/onboarding') === 0) ? 'text-white' : 'text-emerald-600 group-hover:text-emerald-700' ?>"></i>
                                    <span class="relative z-10"><?= lang('UI.cpOnboarding') ?></span>
                                </a>
                            </li>
                        </ul>
                    </div>

                    <!-- Billing -->
                    <div class="p-4">
                        <div class="flex items-center space-x-2 mb-4">
                            <div class="w-2 h-2 bg-gradient-to-r from-indigo-500 to-violet-600 rounded-full animate-pulse"></div>
                            <h3 class="text-xs font-bold text-gray-600 uppercase tracking-wider"><?= lang('UI.cpBilling') ?></h3>
                        </div>
                        <ul class="space-y-1">
                            <li>
                                <a href="<?= base_url('controlplane/plans') ?>" class="group relative flex items-center px-4 py-3 text-sm font-semibold rounded-xl transition-all duration-200 <?= (strpos(uri_string(), 'controlplane/plans') === 0 && strpos(uri_string(), 'plan-requests') === false) ? 'bg-gradient-to-r from-indigo-600 to-violet-600 text-white shadow-lg shadow-indigo-500/25 mx-1' : 'text-gray-700 hover:bg-gradient-to-r hover:from-indigo-50 hover:to-violet-50 hover:text-indigo-900 hover:shadow-md hover:shadow-indigo-500/10 mx-1' ?>">
                                    <div class="absolute inset-0 bg-gradient-to-r from-indigo-500/10 to-violet-500/10 rounded-xl blur opacity-0 group-hover:opacity-100 transition-opacity duration-200"></div>
                                    <i class="fas fa-layer-group w-5 h-5 mr-3 relative z-10 <?= (strpos(uri_string(), 'controlplane/plans') === 0 && strpos(uri_string(), 'plan-requests') === false) ? 'text-white' : 'text-indigo-600 group-hover:text-indigo-700' ?>"></i>
                                    <span class="relative z-10"><?= lang('UI.cpPlans') ?></span>
                                </a>
                            </li>
                            <li>
                                <a href="<?= base_url('controlplane/invoices') ?>" class="group relative flex items-center px-4 py-3 text-sm font-semibold rounded-xl transition-all duration-200 <?= (strpos(uri_string(), 'controlplane/invoices') === 0) ? 'bg-gradient-to-r from-emerald-600 to-teal-600 text-white shadow-lg shadow-emerald-500/25 mx-1' : 'text-gray-700 hover:bg-gradient-to-r hover:from-emerald-50 hover:to-teal-50 hover:text-emerald-900 hover:shadow-md hover:shadow-emerald-500/10 mx-1' ?>">
                                    <div class="absolute inset-0 bg-gradient-to-r from-emerald-500/10 to-teal-500/10 rounded-xl blur opacity-0 group-hover:opacity-100 transition-opacity duration-200"></div>
                                    <i class="fas fa-file-invoice-dollar w-5 h-5 mr-3 relative z-10 <?= (strpos(uri_string(), 'controlplane/invoices') === 0) ? 'text-white' : 'text-emerald-600 group-hover:text-emerald-700' ?>"></i>
                                    <span class="relative z-10"><?= lang('UI.cpInvoices') ?></span>
                                </a>
                            </li>
                            <li>
                                <a href="<?= base_url('controlplane/plan-requests') ?>" class="group relative flex items-center px-4 py-3 text-sm font-semibold rounded-xl transition-all duration-200 <?= (strpos(uri_string(), 'controlplane/plan-requests') === 0) ? 'bg-gradient-to-r from-indigo-600 to-violet-600 text-white shadow-lg shadow-indigo-500/25 mx-1' : 'text-gray-700 hover:bg-gradient-to-r hover:from-indigo-50 hover:to-violet-50 hover:text-indigo-900 hover:shadow-md hover:shadow-indigo-500/10 mx-1' ?>">
                                    <div class="absolute inset-0 bg-gradient-to-r from-indigo-500/10 to-violet-500/10 rounded-xl blur opacity-0 group-hover:opacity-100 transition-opacity duration-200"></div>
                                    <i class="fas fa-inbox w-5 h-5 mr-3 relative z-10 <?= (strpos(uri_string(), 'controlplane/plan-requests') === 0) ? 'text-white' : 'text-indigo-600 group-hover:text-indigo-700' ?>"></i>
                                    <span class="relative z-10"><?= lang('UI.cpRequests') ?></span>
                                </a>
                            </li>
                        </ul>
                    </div>

                    <!-- Governance -->
                    <div class="p-4">
                        <div class="px-2 pt-5 pb-1">
                            <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">MONITOR</p>
                        </div>
                        <div class="flex items-center space-x-2 mb-4 mt-3">
                            <div class="w-2 h-2 bg-gradient-to-r from-slate-500 to-slate-700 rounded-full animate-pulse"></div>
                            <h3 class="text-xs font-bold text-gray-600 uppercase tracking-wider"><?= lang('UI.cpGovernance') ?></h3>
                        </div>
                        <ul class="space-y-1">
                            <li>
                                <a href="<?= base_url('controlplane/operations') ?>" class="group relative flex items-center px-4 py-3 text-sm font-semibold rounded-xl transition-all duration-200 <?= (strpos(uri_string(), 'controlplane/operations') === 0) ? 'bg-gradient-to-r from-amber-500 to-orange-600 text-white shadow-lg shadow-amber-500/25 mx-1' : 'text-gray-700 hover:bg-gradient-to-r hover:from-amber-50 hover:to-orange-50 hover:text-amber-900 hover:shadow-md hover:shadow-amber-500/10 mx-1' ?>">
                                    <div class="absolute inset-0 bg-gradient-to-r from-amber-500/10 to-orange-500/10 rounded-xl blur opacity-0 group-hover:opacity-100 transition-opacity duration-200"></div>
                                    <i class="fas fa-cogs w-5 h-5 mr-3 relative z-10 <?= (strpos(uri_string(), 'controlplane/operations') === 0) ? 'text-white' : 'text-amber-600 group-hover:text-amber-700' ?>"></i>
                                    <span class="relative z-10"><?= lang('UI.cpOperations') ?></span>
                                </a>
                            </li>
                            <li>
                                <a href="<?= base_url('controlplane/whatsapp') ?>" class="group relative flex items-center px-4 py-3 text-sm font-semibold rounded-xl transition-all duration-200 <?= (strpos(uri_string(), 'controlplane/whatsapp') === 0) ? 'bg-gradient-to-r from-emerald-600 to-green-600 text-white shadow-lg shadow-emerald-500/25 mx-1' : 'text-gray-700 hover:bg-gradient-to-r hover:from-emerald-50 hover:to-green-50 hover:text-emerald-900 hover:shadow-md hover:shadow-emerald-500/10 mx-1' ?>">
                                    <div class="absolute inset-0 bg-gradient-to-r from-emerald-500/10 to-green-500/10 rounded-xl blur opacity-0 group-hover:opacity-100 transition-opacity duration-200"></div>
                                    <i class="fab fa-whatsapp w-5 h-5 mr-3 relative z-10 <?= (strpos(uri_string(), 'controlplane/whatsapp') === 0) ? 'text-white' : 'text-emerald-600 group-hover:text-emerald-700' ?>"></i>
                                    <span class="relative z-10"><?= lang('UI.cpWhatsAppOps') ?></span>
                                </a>
                            </li>
                            <li>
                                <a href="<?= base_url('controlplane/activity-log') ?>" class="group relative flex items-center px-4 py-3 text-sm font-semibold rounded-xl transition-all duration-200 <?= (strpos(uri_string(), 'controlplane/activity-log') === 0) ? 'bg-gradient-to-r from-slate-600 to-slate-800 text-white shadow-lg shadow-slate-500/25 mx-1' : 'text-gray-700 hover:bg-gradient-to-r hover:from-slate-50 hover:to-slate-100 hover:text-slate-900 hover:shadow-md hover:shadow-slate-500/10 mx-1' ?>">
                                    <div class="absolute inset-0 bg-gradient-to-r from-slate-500/10 to-slate-700/10 rounded-xl blur opacity-0 group-hover:opacity-100 transition-opacity duration-200"></div>
                                    <i class="fas fa-history w-5 h-5 mr-3 relative z-10 <?= (strpos(uri_string(), 'controlplane/activity-log') === 0) ? 'text-white' : 'text-slate-600 group-hover:text-slate-700' ?>"></i>
                                    <span class="relative z-10"><?= lang('UI.cpSystemActivity') ?></span>
                                </a>
                            </li>
                        </ul>
                    </div>

                    <!-- System -->
                    <div class="p-4">
                        <div class="px-2 pt-5 pb-1">
                            <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">CONFIGURE</p>
                        </div>
                        <div class="flex items-center space-x-2 mb-4 mt-3">
                            <div class="w-2 h-2 bg-gradient-to-r from-slate-500 to-blue-600 rounded-full animate-pulse"></div>
                            <h3 class="text-xs font-bold text-gray-600 uppercase tracking-wider">System</h3>
                        </div>
                    <ul class="space-y-1">
                        <li>
                            <a href="<?= base_url('controlplane/settings') ?>" class="group relative flex items-center px-4 py-3 text-sm font-semibold rounded-xl transition-all duration-200 <?= (strpos(uri_string(), 'controlplane/settings') === 0) ? 'bg-gradient-to-r from-slate-700 to-blue-700 text-white shadow-lg shadow-slate-500/25 mx-1' : 'text-gray-700 hover:bg-gradient-to-r hover:from-slate-50 hover:to-blue-50 hover:text-slate-900 hover:shadow-md hover:shadow-slate-500/10 mx-1' ?>">
                                <div class="absolute inset-0 bg-gradient-to-r from-slate-500/10 to-blue-500/10 rounded-xl blur opacity-0 group-hover:opacity-100 transition-opacity duration-200"></div>
                                <i class="fas fa-sliders-h w-5 h-5 mr-3 relative z-10 <?= (strpos(uri_string(), 'controlplane/settings') === 0) ? 'text-white' : 'text-slate-600 group-hover:text-slate-700' ?>"></i>
                                <span class="relative z-10"><?= lang('UI.cpSettings') ?></span>
                            </a>
                        </li>
                        <?php if (has_role('super_admin')): ?>
                        <li>
                            <a href="<?= base_url('controlplane/roles') ?>" class="group relative flex items-center px-4 py-3 text-sm font-semibold rounded-xl transition-all duration-200 <?= (strpos(uri_string(), 'controlplane/roles') === 0) ? 'bg-gradient-to-r from-indigo-600 to-slate-600 text-white shadow-lg shadow-indigo-500/25 mx-1' : 'text-gray-700 hover:bg-gradient-to-r hover:from-indigo-50 hover:to-slate-50 hover:text-indigo-900 hover:shadow-md hover:shadow-indigo-500/10 mx-1' ?>">
                                <div class="absolute inset-0 bg-gradient-to-r from-indigo-500/10 to-slate-500/10 rounded-xl blur opacity-0 group-hover:opacity-100 transition-opacity duration-200"></div>
                                <i class="fas fa-user-shield w-5 h-5 mr-3 relative z-10 <?= (strpos(uri_string(), 'controlplane/roles') === 0) ? 'text-white' : 'text-indigo-600 group-hover:text-indigo-700' ?>"></i>
                                <span class="relative z-10">System Roles</span>
                            </a>
                        </li>
                        <?php endif; ?>
                    </ul>
                </div>
                </nav>

                <!-- Sidebar Footer -->
                <div class="p-4 border-t border-white/30 backdrop-blur-xl bg-white/40">
                    <div class="group relative cursor-pointer hover:bg-white/60 rounded-xl p-3 transition-all duration-200 border border-transparent hover:border-white/50 hover:shadow-lg hover:shadow-slate-200/50" onclick="toggleUserMenu()">
                        <div class="flex items-center space-x-3 relative z-10">
                            <div class="relative">
                                <div class="absolute inset-0 bg-gradient-to-br from-slate-600 to-slate-800 rounded-lg blur opacity-40 group-hover:opacity-100 transition-opacity duration-200"></div>
                                <div class="relative w-10 h-10 bg-gradient-to-br from-slate-700 to-slate-900 rounded-lg flex items-center justify-center shadow-lg group-hover:scale-105 group-hover:rotate-2 transition-all duration-200">
                                    <span class="text-xs font-bold text-white"><?= esc(ui_badge_text($userEmail ?? '', 2, 'SA')) ?></span>
                                </div>
                            </div>
                            <div class="flex-1 min-w-0">
                                <div class="text-sm font-bold text-slate-900 truncate"><?= esc($userEmail ?? 'Super Admin') ?></div>
                                <div class="flex items-center space-x-1.5">
                                    <div class="w-1.5 h-1.5 bg-emerald-500 rounded-full animate-pulse"></div>
                                    <div class="text-[10px] font-bold text-slate-500 uppercase tracking-wider"><?= lang('UI.cpGlobalAdmin') ?></div>
                                </div>
                            </div>
                            <i class="fas fa-chevron-up text-slate-400 text-xs group-hover:text-slate-600 transition-colors duration-200"></i>
                        </div>
                    </div>
                    
                    <!-- User Menu Dropdown -->
                    <div id="userMenu" class="hidden absolute bottom-24 left-4 w-56 backdrop-blur-2xl bg-white/90 rounded-2xl shadow-2xl shadow-slate-200/50 border border-white/50 p-2 space-y-1 transform origin-bottom-left transition-all duration-200">
                        <a href="<?= base_url('controlplane/settings') ?>" class="group flex items-center px-3 py-2.5 rounded-xl hover:bg-slate-50 transition-all duration-200">
                            <div class="w-8 h-8 rounded-lg bg-slate-100 flex items-center justify-center mr-3 group-hover:bg-white group-hover:shadow-sm transition-all">
                                <i class="fas fa-cog text-xs text-slate-500 group-hover:text-slate-700"></i>
                            </div>
                            <span class="text-sm font-medium text-slate-700 group-hover:text-slate-900"><?= lang('UI.cpSettings') ?></span>
                        </a>
                        <div class="h-px bg-slate-100 my-1"></div>
                        <form action="<?= base_url('controlplane/exit') ?>" method="post">
                            <?= csrf_field() ?>
                            <button class="w-full group flex items-center px-3 py-2.5 rounded-xl hover:bg-rose-50 transition-all duration-200 text-left">
                                <div class="w-8 h-8 rounded-lg bg-rose-50 flex items-center justify-center mr-3 group-hover:bg-white group-hover:shadow-sm transition-all">
                                    <i class="fas fa-sign-out-alt text-xs text-rose-500 group-hover:text-rose-600"></i>
                                </div>
                                <span class="text-sm font-medium text-rose-600 group-hover:text-rose-700"><?= lang('UI.cpExitControlPlane') ?></span>
                            </button>
                        </form>
                    </div>
                </div>
            </aside>
        <?php endif; ?>

        <div id="cp-sidebar-backdrop" class="fixed inset-0 bg-slate-900/40 backdrop-blur-sm z-30 hidden lg:hidden" onclick="closeCpSidebar()"></div>

        <div class="flex-1 flex flex-col min-h-screen min-w-0">
            <header class="sticky top-0 z-30 backdrop-blur-xl bg-white/70 border-b border-white/30 px-4 sm:px-6 py-4 flex flex-wrap items-center justify-between gap-3">
                <div class="flex items-center gap-3 min-w-0">
                    <button type="button" class="lg:hidden inline-flex items-center justify-center h-10 w-10 rounded-xl bg-white/80 border border-white/60 text-slate-600 shadow-sm" onclick="toggleSidebar()">
                        <i class="fas fa-bars"></i>
                    </button>
                    <div class="min-w-0">
                    <p class="text-xs uppercase tracking-widest text-slate-400 font-semibold"><?= lang('UI.cpControlPlane') ?></p>
                    <h1 class="text-lg sm:text-xl font-bold text-gray-900 truncate"><?= esc($title ?? lang('UI.menuDashboard')) ?></h1>
                    </div>
                </div>
                <?php $userEmail = (isset($user) && $user && property_exists($user, 'email')) ? $user->email : 'Super Admin'; ?>
                <div class="flex flex-wrap items-center gap-2 sm:gap-3">
                    <button type="button" id="cp-command-trigger" class="hidden md:flex items-center gap-2 px-3 py-2 rounded-xl bg-white/80 border border-white/60 text-slate-600 text-xs font-semibold shadow-sm hover:bg-white transition">
                        <i class="fas fa-magnifying-glass text-indigo-500"></i>
                        Super Search
                        <span class="ml-1 rounded-lg border border-slate-200 bg-white px-2 py-0.5 text-[10px] font-semibold text-slate-500">Cmd+K</span>
                    </button>
                    <span class="text-xs font-semibold bg-emerald-100 text-emerald-700 px-3 py-1 rounded-full">Global Mode</span>
                    <div class="hidden sm:flex items-center gap-2 text-sm text-gray-600">
                        <i class="fas fa-user-circle text-lg"></i>
                        <span><?= esc($userEmail) ?></span>
                    </div>
                    <form action="<?= base_url('controlplane/exit') ?>" method="post">
                        <?= csrf_field() ?>
                        <button class="px-3 py-2 rounded-xl bg-slate-900 text-white text-xs font-semibold hover:bg-slate-800 transition">
                            <?= lang('UI.cpExitControlPlane') ?>
                        </button>
                    </form>
                </div>
            </header>

            <main class="flex-1 px-4 sm:px-6 py-6 sm:py-8 relative">
                <div class="pointer-events-none absolute inset-0 overflow-hidden">
                    <div class="absolute -top-40 -right-40 w-80 h-80 bg-gradient-to-br from-indigo-400/20 to-emerald-600/20 rounded-full blur-3xl"></div>
                    <div class="absolute -bottom-40 -left-40 w-80 h-80 bg-gradient-to-br from-slate-400/15 to-emerald-500/20 rounded-full blur-3xl"></div>
                </div>

                <?php if (session()->getFlashdata('success')): ?>
                    <div class="relative mb-6 glass-card rounded-2xl px-4 py-3 text-emerald-800">
                        <?= session()->getFlashdata('success') ?>
                    </div>
                <?php endif; ?>

                <?php if (session()->getFlashdata('error')): ?>
                    <div class="relative mb-6 glass-card rounded-2xl px-4 py-3 text-rose-800">
                        <?= session()->getFlashdata('error') ?>
                    </div>
                <?php endif; ?>

                <div class="relative cp-content">
                    <?= $this->renderSection('content') ?>
                </div>
            </main>
        </div>
    </div>

    <div id="cp-command-palette" class="fixed inset-0 z-50 hidden items-start justify-center bg-slate-900/40 px-4 py-10">
        <div class="w-full max-w-2xl rounded-3xl border border-white/30 bg-white/90 shadow-2xl backdrop-blur-xl">
            <div class="flex items-center gap-3 border-b border-slate-200 px-6 py-4">
                <div class="flex h-10 w-10 items-center justify-center rounded-2xl bg-gradient-to-br from-slate-900 via-indigo-700 to-emerald-600 text-white shadow-lg">
                    <i class="fas fa-terminal"></i>
                </div>
                <div class="flex-1">
                    <p class="text-xs uppercase tracking-[0.3em] text-slate-400 font-semibold">Global Command Palette</p>
                    <p class="text-sm font-semibold text-slate-800">Search clinics, users, and navigation</p>
                </div>
                <button type="button" id="cp-command-close" class="rounded-full p-2 text-slate-400 hover:text-slate-700 hover:bg-slate-100">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="px-6 py-4">
                <div class="flex items-center gap-3 rounded-2xl border border-slate-200 bg-white px-4 py-3 shadow-sm">
                    <i class="fas fa-search text-indigo-500"></i>
                    <input id="cp-command-input" type="text" placeholder="<?= esc(lang('UI.cpSearchPlaceholder')) ?>"
                           class="w-full bg-transparent text-sm font-semibold text-slate-700 placeholder:text-slate-400 focus:outline-none">
                    <span class="rounded-lg border border-slate-200 bg-slate-50 px-2 py-1 text-[10px] font-semibold text-slate-500">Esc</span>
                </div>
            </div>
            <div class="px-4 pb-6">
                <div id="cp-command-status" class="px-2 pb-2 text-xs text-slate-500"></div>
                <div id="cp-command-results" class="space-y-3"></div>
            </div>
            <div class="flex flex-wrap items-center gap-2 border-t border-slate-200 bg-white/70 px-6 py-3 text-[11px] font-semibold text-slate-500">
                <span class="rounded-lg border border-slate-200 bg-slate-50 px-2 py-1">↑ ↓</span>
                <span><?= lang('UI.cpNavigate') ?></span>
                <span class="rounded-lg border border-slate-200 bg-slate-50 px-2 py-1">Enter</span>
                <span><?= lang('UI.cpOpen') ?></span>
                <span class="rounded-lg border border-slate-200 bg-slate-50 px-2 py-1">Esc</span>
                <span><?= lang('UI.cpClose') ?></span>
            </div>
        </div>
    </div>

    <script>
        function toggleSidebar() {
            const sidebar = document.getElementById('sidebar');
            const backdrop = document.getElementById('cp-sidebar-backdrop');
            if (!sidebar) return;
            sidebar.classList.toggle('sidebar-mobile-hidden');
            if (window.innerWidth < 1024) {
                backdrop.classList.toggle('hidden', sidebar.classList.contains('sidebar-mobile-hidden'));
                document.body.classList.toggle('overflow-hidden', !sidebar.classList.contains('sidebar-mobile-hidden'));
            }
        }

        function closeCpSidebar() {
            if (window.innerWidth >= 1024) return;
            const sidebar = document.getElementById('sidebar');
            const backdrop = document.getElementById('cp-sidebar-backdrop');
            if (!sidebar) return;
            sidebar.classList.add('sidebar-mobile-hidden');
            backdrop.classList.add('hidden');
            document.body.classList.remove('overflow-hidden');
        }

        document.addEventListener('keydown', function(e) {
            if (e.ctrlKey && e.key === 'b') {
                e.preventDefault();
                toggleSidebar();
            }
        });

        window.addEventListener('resize', function() {
            const sidebar = document.getElementById('sidebar');
            const backdrop = document.getElementById('cp-sidebar-backdrop');
            if (!sidebar) return;
            if (window.innerWidth < 1024) {
                sidebar.classList.add('sidebar-mobile-hidden');
            } else {
                sidebar.classList.remove('sidebar-mobile-hidden');
            }
            backdrop.classList.add('hidden');
            document.body.classList.remove('overflow-hidden');
        });

        const cpPalette = document.getElementById('cp-command-palette');
        const cpTrigger = document.getElementById('cp-command-trigger');
        const cpClose = document.getElementById('cp-command-close');
        const cpInput = document.getElementById('cp-command-input');
        const cpResults = document.getElementById('cp-command-results');
        const cpStatus = document.getElementById('cp-command-status');
        let cpActiveIndex = -1;
        let cpItems = [];
        let cpTimer = null;
        const cpI18n = {
            typeToSearch: <?= json_encode(lang('UI.cpTypeToSearch')) ?>,
            noResults: <?= json_encode(lang('UI.cpNoResults')) ?>,
            searching: <?= json_encode(lang('UI.cpSearching')) ?>,
            searchFailed: <?= json_encode(lang('UI.cpSearchFailed')) ?>,
            clinicsSection: <?= json_encode(lang('UI.cpClinicsSection')) ?>,
            usersSection: <?= json_encode(lang('UI.cpUsersSection')) ?>,
            navigationSection: <?= json_encode(lang('UI.cpNavigationSection')) ?>,
            recentSection: <?= json_encode(lang('UI.cpRecentSection')) ?>,
            recentSearch: <?= json_encode(lang('UI.cpRecentSearch')) ?>,
            controlPlaneUser: <?= json_encode(lang('UI.cpControlPlaneUser')) ?>,
            fallbackLink: <?= json_encode(lang('UI.cpLinkFallback')) ?>,
            fallbackClinic: <?= json_encode(lang('UI.cpClinicFallback')) ?>,
            fallbackUser: <?= json_encode(lang('UI.cpUserFallback')) ?>
        };

        function setCommandStatus(message) {
            if (!cpStatus) {
                return;
            }
            cpStatus.textContent = message || '';
        }

        function resetCommandItems() {
            cpItems = [];
            cpActiveIndex = -1;
            if (cpResults) {
                cpResults.textContent = '';
            }
        }

        function openCommandPalette() {
            if (!cpPalette) {
                return;
            }
            cpPalette.classList.remove('hidden');
            cpPalette.classList.add('flex');
            document.body.classList.add('overflow-hidden');
            setCommandStatus(cpI18n.typeToSearch);
            resetCommandItems();
            renderRecentSearches();
            if (cpInput) {
                cpInput.value = '';
                cpInput.focus();
            }
        }

        function closeCommandPalette() {
            if (!cpPalette) {
                return;
            }
            cpPalette.classList.add('hidden');
            cpPalette.classList.remove('flex');
            document.body.classList.remove('overflow-hidden');
        }

        function updateActiveItem() {
            cpItems.forEach((item, index) => {
                if (index === cpActiveIndex) {
                    item.classList.add('bg-indigo-50');
                } else {
                    item.classList.remove('bg-indigo-50');
                }
            });
        }

        function createSection(title, iconClass) {
            const section = document.createElement('div');
            const heading = document.createElement('div');
            heading.className = 'px-3 text-xs font-semibold uppercase tracking-widest text-slate-400 flex items-center gap-2';
            if (iconClass) {
                const icon = document.createElement('i');
                icon.className = `${iconClass} text-slate-400`;
                heading.appendChild(icon);
            }
            const label = document.createElement('span');
            label.textContent = title;
            heading.appendChild(label);
            section.appendChild(heading);
            return { section, list: document.createElement('div') };
        }

        function createResultButton(label, meta, onClick) {
            const button = document.createElement('button');
            button.type = 'button';
            button.className = 'w-full text-left rounded-2xl border border-slate-100 bg-white px-4 py-3 shadow-sm transition hover:border-indigo-100 hover:bg-indigo-50';
            const title = document.createElement('div');
            title.className = 'text-sm font-semibold text-slate-800';
            title.textContent = label;
            const subtitle = document.createElement('div');
            subtitle.className = 'text-xs text-slate-500';
            subtitle.textContent = meta;
            button.appendChild(title);
            button.appendChild(subtitle);
            button.addEventListener('click', onClick);
            return button;
        }

        function renderResults(payload) {
            resetCommandItems();
            if (!payload || !payload.results) {
                setCommandStatus(cpI18n.noResults);
                return;
            }

            const sections = [];
            const clinics = payload.results.clinics || [];
            const users = payload.results.users || [];
            const links = payload.results.links || [];

            if (clinics.length > 0) {
                const { section, list } = createSection(cpI18n.clinicsSection, 'fas fa-hospital-user');
                clinics.forEach((clinic) => {
                    const label = clinic.name || cpI18n.fallbackClinic;
                    let detail = clinic.domain || '';
                    if (!detail && clinic.uuid) {
                        detail = `UUID ${clinic.uuid}`;
                    }
                    if (!detail) {
                        detail = clinic.slug || 'no-slug';
                    }
                    const meta = `ID ${clinic.id} · ${detail}`;
                    const button = createResultButton(label, meta, () => {
                        window.location.href = `<?= base_url('controlplane/clinics') ?>/${clinic.id}`;
                    });
                    list.appendChild(button);
                    cpItems.push(button);
                });
                list.className = 'mt-2 space-y-2';
                section.appendChild(list);
                sections.push(section);
            }

            if (users.length > 0) {
                const { section, list } = createSection(cpI18n.usersSection, 'fas fa-user');
                users.forEach((user) => {
                    const label = user.email || cpI18n.fallbackUser;
                    const meta = user.name ? user.name : cpI18n.controlPlaneUser;
                    const button = createResultButton(label, meta, () => {
                        window.location.href = `<?= base_url('controlplane/settings') ?>?user=${user.id}`;
                    });
                    list.appendChild(button);
                    cpItems.push(button);
                });
                list.className = 'mt-2 space-y-2';
                section.appendChild(list);
                sections.push(section);
            }

            if (links.length > 0) {
                const { section, list } = createSection(cpI18n.navigationSection, 'fas fa-compass');
                links.forEach((link) => {
                    const label = link.title || cpI18n.fallbackLink;
                    const meta = link.url || '';
                    const button = createResultButton(label, meta, () => {
                        window.location.href = `<?= base_url() ?>${link.url}`;
                    });
                    list.appendChild(button);
                    cpItems.push(button);
                });
                list.className = 'mt-2 space-y-2';
                section.appendChild(list);
                sections.push(section);
            }

            if (sections.length === 0) {
                setCommandStatus(cpI18n.noResults);
                renderRecentSearches();
                return;
            }

            sections.forEach((section) => cpResults.appendChild(section));
            cpActiveIndex = 0;
            updateActiveItem();
            setCommandStatus(`${cpItems.length} results`);
        }

        function getRecentSearches() {
            const raw = localStorage.getItem('cp_command_recent');
            if (!raw) {
                return [];
            }
            try {
                const parsed = JSON.parse(raw);
                return Array.isArray(parsed) ? parsed : [];
            } catch (e) {
                return [];
            }
        }

        function saveRecentSearch(query) {
            if (!query || query.length < 2) {
                return;
            }
            const existing = getRecentSearches().filter((item) => item !== query);
            const next = [query, ...existing].slice(0, 5);
            localStorage.setItem('cp_command_recent', JSON.stringify(next));
        }

        function renderRecentSearches() {
            const recent = getRecentSearches();
            if (!cpResults) {
                return;
            }
            if (recent.length === 0) {
                return;
            }
            const { section, list } = createSection(cpI18n.recentSection, 'fas fa-clock');
            recent.forEach((term) => {
                const button = createResultButton(term, cpI18n.recentSearch, () => {
                    if (cpInput) {
                        cpInput.value = term;
                        handleCommandInput();
                    }
                });
                list.appendChild(button);
                cpItems.push(button);
            });
            list.className = 'mt-2 space-y-2';
            section.appendChild(list);
            cpResults.appendChild(section);
            if (cpItems.length > 0 && cpActiveIndex === -1) {
                cpActiveIndex = 0;
                updateActiveItem();
            }
        }

        function fetchCommandResults(query) {
            if (!query || query.length < 2) {
                resetCommandItems();
                setCommandStatus(cpI18n.typeToSearch);
                renderRecentSearches();
                return;
            }

            setCommandStatus(cpI18n.searching);
            fetch(`<?= base_url('controlplane/search/global') ?>?q=${encodeURIComponent(query)}`)
                .then((response) => response.json())
                .then((data) => {
                    saveRecentSearch(query);
                    renderResults(data);
                })
                .catch(() => {
                    resetCommandItems();
                    setCommandStatus(cpI18n.searchFailed);
                });
        }

        function handleCommandInput() {
            const query = cpInput ? cpInput.value.trim() : '';
            clearTimeout(cpTimer);
            cpTimer = setTimeout(() => fetchCommandResults(query), 200);
        }

        if (cpTrigger) {
            cpTrigger.addEventListener('click', openCommandPalette);
        }

        if (cpClose) {
            cpClose.addEventListener('click', closeCommandPalette);
        }

        if (cpPalette) {
            cpPalette.addEventListener('click', (event) => {
                if (event.target === cpPalette) {
                    closeCommandPalette();
                }
            });
        }

        if (cpInput) {
            cpInput.addEventListener('input', handleCommandInput);
            cpInput.addEventListener('keydown', (event) => {
                if (event.key === 'ArrowDown') {
                    event.preventDefault();
                    if (cpItems.length === 0) {
                        return;
                    }
                    cpActiveIndex = (cpActiveIndex + 1) % cpItems.length;
                    updateActiveItem();
                } else if (event.key === 'ArrowUp') {
                    event.preventDefault();
                    if (cpItems.length === 0) {
                        return;
                    }
                    cpActiveIndex = (cpActiveIndex - 1 + cpItems.length) % cpItems.length;
                    updateActiveItem();
                } else if (event.key === 'Enter') {
                    event.preventDefault();
                    if (cpItems[cpActiveIndex]) {
                        cpItems[cpActiveIndex].click();
                    }
                } else if (event.key === 'Escape') {
                    event.preventDefault();
                    closeCommandPalette();
                }
            });
        }

        document.addEventListener('keydown', (event) => {
            const isMac = navigator.platform.toUpperCase().includes('MAC');
            const cmdKey = isMac ? event.metaKey : event.ctrlKey;
            if (cmdKey && event.key.toLowerCase() === 'k') {
                event.preventDefault();
                openCommandPalette();
            }
            if (event.key === 'Escape' && cpPalette && !cpPalette.classList.contains('hidden')) {
                closeCommandPalette();
            }
        });
    </script>

    <script>
        (function () {
            const clinicDateFormat = '<?= esc(settings()->get('date_format', 'd/m/Y')) ?>';

            function clinicDateFormatLabel(format) {
                return String(format || 'd/m/Y').replace('Y', 'YYYY').replace('m', 'MM').replace('d', 'DD');
            }

            function formatClinicDateFromIso(isoDate) {
                if (!isoDate || !/^\d{4}-\d{2}-\d{2}$/.test(isoDate)) return isoDate || '';
                const [year, month, day] = isoDate.split('-');
                if (clinicDateFormat === 'm/d/Y') return `${month}/${day}/${year}`;
                if (clinicDateFormat === 'd/m/Y') return `${day}/${month}/${year}`;
                return isoDate;
            }

            function parseClinicDateToIso(rawValue) {
                if (!rawValue) return '';
                const value = String(rawValue).trim();
                if (/^\d{4}-\d{2}-\d{2}$/.test(value)) return value;

                const isValidCalendarDate = (year, month, day) => {
                    const y = Number(year), m = Number(month), d = Number(day);
                    if (!Number.isInteger(y) || !Number.isInteger(m) || !Number.isInteger(d)) return false;
                    const probe = new Date(Date.UTC(y, m - 1, d));
                    return probe.getUTCFullYear() === y && (probe.getUTCMonth() + 1) === m && probe.getUTCDate() === d;
                };

                const match = value.match(/^(\d{1,2})\/(\d{1,2})\/(\d{4})$/);
                if (!match) return null;

                let day = match[1], month = match[2], year = match[3];
                if (clinicDateFormat === 'm/d/Y') {
                    [month, day] = [match[1], match[2]];
                }
                month = String(month).padStart(2, '0');
                day = String(day).padStart(2, '0');
                if (!isValidCalendarDate(year, month, day)) return null;
                return `${year}-${month}-${day}`;
            }

            function initializeClinicDateInputs() {
                const fields = document.querySelectorAll('input[data-clinic-date="1"]');
                if (!fields.length) return;
                const formatHint = clinicDateFormatLabel(clinicDateFormat);

                fields.forEach((field) => {
                    const current = (field.value || '').trim();
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
                        if (iso) field.value = iso;
                        if (raw === '') field.value = '';
                        if (typeof field.showPicker === 'function') {
                            try { field.showPicker(); } catch (e) {}
                        }
                    };

                    const switchToDisplayMode = () => {
                        if (field.type !== 'date') return;
                        const iso = (field.value || '').trim();
                        field.type = 'text';
                        field.value = iso ? formatClinicDateFromIso(iso) : '';
                        field.setCustomValidity('');
                    };

                    field.addEventListener('click', () => { if (field.type !== 'date') switchToPickerMode(); });
                    field.addEventListener('dblclick', () => { if (field.type !== 'date') switchToPickerMode(); });
                    field.addEventListener('keydown', (event) => {
                        if (event.altKey && event.key === 'ArrowDown') {
                            event.preventDefault();
                            switchToPickerMode();
                        }
                    });
                    field.addEventListener('input', () => { field.setCustomValidity(''); });
                    field.addEventListener('change', () => { field.setCustomValidity(''); });
                    field.addEventListener('blur', switchToDisplayMode);
                });

                document.querySelectorAll('form').forEach((form) => {
                    form.addEventListener('submit', function (event) {
                        for (const input of form.querySelectorAll('input[data-clinic-date="1"]')) {
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

            window.parseClinicDateToIso = window.parseClinicDateToIso || parseClinicDateToIso;
            document.addEventListener('DOMContentLoaded', initializeClinicDateInputs);
        })();
    </script>

    <?php if (isset($loadSelect2) && $loadSelect2): ?>
    <script src="<?= base_url('assets/js/select2-init.js') ?>?v=<?= time() ?>"></script>
    <?php endif; ?>
</body>
</html>
