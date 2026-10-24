<!DOCTYPE html>
<?php
$cpRouteKey = (string) service('uri')->getSegment(2);
$cpTitleKeys = ['dashboard' => 'menuDashboard', 'clinics' => 'cpClinics', 'plans' => 'cpPlans', 'invoices' => 'cpInvoices', 'operations' => 'cpOperations', 'users' => 'cpUsers', 'settings' => 'cpSettings', 'roles' => 'menuRoleManagement'];
$cpDisplayTitle = isset($cpTitleKeys[$cpRouteKey]) ? lang('UI.' . $cpTitleKeys[$cpRouteKey]) : ($title ?? lang('UI.cpControlPlane'));
?>

<html lang="<?= esc($locale ?? 'en') ?>" dir="<?= !empty($is_rtl) ? 'rtl' : 'ltr' ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= esc($cpDisplayTitle) ?></title>
    <link rel="icon" type="image/png" href="<?= base_url('assets/images/favicon.png') ?>">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=Noto+Sans+Arabic:wght@400;500;600;700;800&family=JetBrains+Mono:wght@400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= base_url('assets/css/tailwind.css') ?>?v=<?= @filemtime(FCPATH . 'assets/css/tailwind.css') ?: '1' ?>">
    <link rel="stylesheet" media="print" href="<?= base_url('assets/vendor/fontawesome/css/all.min.css') ?>?v=<?= @filemtime(FCPATH . 'assets/vendor/fontawesome/css/all.min.css') ?: '1' ?>">
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
    <meta name="theme-color" content="#162421">
    <meta name="csrf-name" content="<?= csrf_token() ?>">
    <meta name="csrf-token" content="<?= csrf_hash() ?>">
    <meta name="csrf-header" content="<?= csrf_header() ?>">

    <?php foreach (['warm-clinical.css'] as $themeAsset): ?>
        <link rel="stylesheet" media="screen" href="<?= base_url('assets/css/' . $themeAsset) ?>?v=<?= @filemtime(FCPATH . 'assets/css/' . $themeAsset) ?: '1' ?>">
    <?php endforeach; ?>
    <link rel="stylesheet" media="screen" href="<?= base_url('assets/css/v3-control-plane.css') ?>?v=<?= @filemtime(FCPATH . 'assets/css/v3-control-plane.css') ?: '1' ?>">
<?php if (in_array(service('uri')->getSegment(2), ['roles', 'users'], true)): ?>
<?php foreach (['v3-support-views.css', 'v3-flow-forms.css'] as $flowAsset): ?>
<link rel="stylesheet" media="screen" href="<?= base_url('assets/css/' . $flowAsset) ?>?v=<?= @filemtime(FCPATH . 'assets/css/' . $flowAsset) ?: '1' ?>">
<?php endforeach; ?>
<?php endif; ?>
<link rel="stylesheet" media="screen" href="<?= base_url('assets/css/v3-components.css') ?>?v=<?= @filemtime(FCPATH . 'assets/css/v3-components.css') ?: '1' ?>">
<link rel="stylesheet" media="screen" href="<?= base_url('assets/css/v3-icons.css') ?>?v=<?= @filemtime(FCPATH . 'assets/css/v3-icons.css') ?: '1' ?>">
</head>
<body class="denta-v3 denta-cp min-h-screen overflow-x-hidden">
    <?= view('components/toast') ?>

    <div class="v3-platform-shell flex min-h-screen">
        <?php $hideCpNav = $hide_cp_nav ?? false; ?>
        <?php if (!$hideCpNav): ?>
            <aside class="w-64 overflow-hidden z-40 transition-all duration-300 transform flex-shrink-0 md:translate-x-0 sidebar-mobile-hidden flex flex-col md:sticky md:top-0 md:self-start fixed inset-y-0 left-0" id="sidebar">
                <!-- Sidebar Header -->
                <div class="px-3 py-3 border-b v3-border-default" id="sidebar-header">
                    <div class="flex items-center justify-between">
                        <a href="<?= base_url('controlplane/dashboard') ?>" class="cp-brand-link flex items-center gap-3">
                            <div class="cp-brand-icon">
                                <i class="fas fa-shield-alt text-base"></i>
                            </div>
                            <div id="sidebar-title" class="min-w-0 flex-1">
                                <h1 class="text-sm font-bold v3-text-on-primary leading-tight tracking-tight whitespace-nowrap"><?= lang('UI.cpControlPlane') ?></h1>
                                <div class="flex items-center gap-1.5 mt-0.5">
                                    <div class="w-1.5 h-1.5 v3-bg-primary rounded-full"></div>
                                    <p class="text-[11px] text-[#A2B5AB] font-semibold whitespace-nowrap"><?= lang('UI.cpGlobalAdmin') ?></p>
                                </div>
                            </div>
                        </a>
                        <button class="p-1.5 text-[#A2B5AB] v3-hover-text-on-primary v3-hover-soft rounded-md transition md:block hidden" onclick="toggleSidebar()" title="<?= esc(lang('V3Platform.toggleSidebarCtrlB'), 'attr') ?>">
                            <i class="fas fa-bars text-sm"></i>
                        </button>
                    </div>
                </div>

                <nav class="flex-1 min-h-0 overflow-y-auto custom-scrollbar overscroll-contain px-2 py-2" id="sidebar-nav">
                    <!-- Platform -->
                    <div class="mb-2">
                        <p class="cp-section-label"><?= lang('UI.cpPlatform') ?></p>
                        <ul class="space-y-0.5">
                            <li>
                                <a href="<?= base_url('controlplane/dashboard') ?>" class="v3-platform-nav-link <?= (strpos(uri_string(), 'controlplane/dashboard') === 0) ? 'is-active' : '' ?>" aria-current="<?= (strpos(uri_string(), 'controlplane/dashboard') === 0) ? 'page' : 'false' ?>">
                                    <i class="fas fa-tachometer-alt"></i>
                                    <span><?= lang('UI.menuDashboard') ?></span>
                                </a>
                            </li>
                        </ul>
                    </div>

                    <!-- Tenants -->
                    <div class="mb-2">
                        <p class="cp-section-label"><?= lang('UI.cpTenants') ?></p>
                        <ul class="space-y-0.5">
                            <li>
                                <a href="<?= base_url('controlplane/clinics') ?>" class="v3-platform-nav-link <?= (strpos(uri_string(), 'controlplane/clinics') === 0) ? 'is-active' : '' ?>" aria-current="<?= (strpos(uri_string(), 'controlplane/clinics') === 0) ? 'page' : 'false' ?>">
                                    <i class="fas fa-hospital-user"></i>
                                    <span><?= lang('UI.cpClinics') ?></span>
                                </a>
                            </li>
                            <li>
                                <a href="<?= base_url('controlplane/users') ?>" class="v3-platform-nav-link <?= (strpos(uri_string(), 'controlplane/users') === 0) ? 'is-active' : '' ?>" aria-current="<?= (strpos(uri_string(), 'controlplane/users') === 0) ? 'page' : 'false' ?>">
                                    <i class="fas fa-users"></i>
                                    <span><?= lang('UI.cpUsers') ?></span>
                                </a>
                            </li>
                            <li>
                                <a href="<?= base_url('controlplane/onboarding/clinic/create') ?>" class="v3-platform-nav-link <?= (strpos(uri_string(), 'controlplane/onboarding') === 0) ? 'is-active' : '' ?>" aria-current="<?= (strpos(uri_string(), 'controlplane/onboarding') === 0) ? 'page' : 'false' ?>">
                                    <i class="fas fa-user-plus"></i>
                                    <span><?= lang('UI.cpOnboarding') ?></span>
                                </a>
                            </li>
                        </ul>
                    </div>

                    <!-- Billing -->
                    <div class="mb-2">
                        <p class="cp-section-label"><?= lang('UI.cpBilling') ?></p>
                        <ul class="space-y-0.5">
                            <li>
                                <a href="<?= base_url('controlplane/plans') ?>" class="v3-platform-nav-link <?= (strpos(uri_string(), 'controlplane/plans') === 0 && strpos(uri_string(), 'plan-requests') === false) ? 'is-active' : '' ?>" aria-current="<?= (strpos(uri_string(), 'controlplane/plans') === 0 && strpos(uri_string(), 'plan-requests') === false) ? 'page' : 'false' ?>">
                                    <i class="fas fa-layer-group"></i>
                                    <span><?= lang('UI.cpPlans') ?></span>
                                </a>
                            </li>
                            <li>
                                <a href="<?= base_url('controlplane/invoices') ?>" class="v3-platform-nav-link <?= (strpos(uri_string(), 'controlplane/invoices') === 0) ? 'is-active' : '' ?>" aria-current="<?= (strpos(uri_string(), 'controlplane/invoices') === 0) ? 'page' : 'false' ?>">
                                    <i class="fas fa-file-invoice-dollar"></i>
                                    <span><?= lang('UI.cpInvoices') ?></span>
                                </a>
                            </li>
                            <li>
                                <a href="<?= base_url('controlplane/plan-requests') ?>" class="v3-platform-nav-link <?= (strpos(uri_string(), 'controlplane/plan-requests') === 0) ? 'is-active' : '' ?>" aria-current="<?= (strpos(uri_string(), 'controlplane/plan-requests') === 0) ? 'page' : 'false' ?>">
                                    <i class="fas fa-inbox"></i>
                                    <span><?= lang('UI.cpRequests') ?></span>
                                </a>
                            </li>
                        </ul>
                    </div>

                    <!-- Governance -->
                    <div class="mb-2">
                        <p class="cp-section-label"><?= lang('UI.cpGovernance') ?></p>
                        <ul class="space-y-0.5">
                            <li>
                                <a href="<?= base_url('controlplane/operations') ?>" class="v3-platform-nav-link <?= (strpos(uri_string(), 'controlplane/operations') === 0) ? 'is-active' : '' ?>" aria-current="<?= (strpos(uri_string(), 'controlplane/operations') === 0) ? 'page' : 'false' ?>">
                                    <i class="fas fa-cogs"></i>
                                    <span><?= lang('UI.cpOperations') ?></span>
                                </a>
                            </li>
                            <li>
                                <a href="<?= base_url('controlplane/whatsapp') ?>" class="v3-platform-nav-link <?= (strpos(uri_string(), 'controlplane/whatsapp') === 0) ? 'is-active' : '' ?>" aria-current="<?= (strpos(uri_string(), 'controlplane/whatsapp') === 0) ? 'page' : 'false' ?>">
                                    <i class="fab fa-whatsapp"></i>
                                    <span><?= lang('UI.cpWhatsAppOps') ?></span>
                                </a>
                            </li>
                            <li>
                                <a href="<?= base_url('controlplane/activity-log') ?>" class="v3-platform-nav-link <?= (strpos(uri_string(), 'controlplane/activity-log') === 0) ? 'is-active' : '' ?>" aria-current="<?= (strpos(uri_string(), 'controlplane/activity-log') === 0) ? 'page' : 'false' ?>">
                                    <i class="fas fa-history"></i>
                                    <span><?= lang('UI.cpSystemActivity') ?></span>
                                </a>
                            </li>
                        </ul>
                    </div>

                    <!-- System -->
                    <div class="mb-2">
                        <p class="cp-section-label"><?= esc(lang('V3Platform.configure')) ?></p>
                        <ul class="space-y-0.5">
                            <li>
                                <a href="<?= base_url('controlplane/settings') ?>" class="v3-platform-nav-link <?= (strpos(uri_string(), 'controlplane/settings') === 0) ? 'is-active' : '' ?>" aria-current="<?= (strpos(uri_string(), 'controlplane/settings') === 0) ? 'page' : 'false' ?>">
                                    <i class="fas fa-sliders-h"></i>
                                    <span><?= lang('UI.cpSettings') ?></span>
                                </a>
                            </li>
                            <?php if (has_role('super_admin')): ?>
                            <li>
                                <a href="<?= base_url('controlplane/roles') ?>" class="v3-platform-nav-link <?= (strpos(uri_string(), 'controlplane/roles') === 0) ? 'is-active' : '' ?>" aria-current="<?= (strpos(uri_string(), 'controlplane/roles') === 0) ? 'page' : 'false' ?>">
                                    <i class="fas fa-user-shield"></i>
                                    <span><?= esc(lang('V3Platform.systemRoles')) ?></span>
                                </a>
                            </li>
                            <?php endif; ?>
                        </ul>
                    </div>
                </nav>

                <!-- Sidebar Footer -->
                <div class="p-3 border-t v3-border-default" id="sidebar-footer">
                    <?php $userEmail = (isset($user) && $user && property_exists($user, 'email')) ? $user->email : 'Super Admin'; ?>
                    <div class="cursor-pointer v3-hover-soft rounded-md p-2 transition flex items-center justify-between" onclick="toggleUserMenu()">
                        <div class="flex items-center gap-2.5 min-w-0">
                            <div class="w-7 h-7 rounded bg-[#0F6B63] flex items-center justify-center v3-text-on-primary text-[11px] font-bold flex-shrink-0">
                                <?= esc(ui_badge_text($userEmail ?? '', 2, 'SA')) ?>
                            </div>
                            <div class="min-w-0 flex-1">
                                <div class="text-xs font-bold v3-text-on-primary truncate"><?= esc($userEmail ?? 'Super Admin') ?></div>
                                <div class="text-[10px] text-[#A2B5AB] font-medium truncate"><?= lang('UI.cpGlobalAdmin') ?></div>
                            </div>
                        </div>
                        <i class="fas fa-chevron-up text-[#7E9388] text-[10px]"></i>
                    </div>

                    <!-- User Menu Dropdown -->
                    <div id="userMenu" class="hidden absolute bottom-20 left-3 w-56 cp-modal-box p-1.5 space-y-0.5 v3-elevation-none">
                        <a href="<?= base_url('controlplane/settings') ?>" class="flex items-center gap-2.5 px-3 py-2 rounded-md v3-backdrop text-xs font-semibold v3-text-ink">
                            <i class="fas fa-cog v3-text-muted w-4"></i>
                            <span><?= lang('UI.cpSettings') ?></span>
                        </a>
                        <div class="h-px v3-bg-sand my-1"></div>
                        <form action="<?= base_url('controlplane/exit') ?>" method="post">
                            <?= csrf_field() ?>
                            <button type="submit" class="w-full flex items-center gap-2.5 px-3 py-2 rounded-md v3-hover-danger text-xs font-semibold v3-text-danger text-left">
                                <i class="fas fa-sign-out-alt v3-text-danger w-4"></i>
                                <span><?= lang('UI.cpExitControlPlane') ?></span>
                            </button>
                        </form>
                    </div>
                </div>
            </aside>
        <?php endif; ?>

        <div id="cp-sidebar-backdrop" class="fixed inset-0 v3-backdrop z-30 hidden md:hidden" onclick="closeCpSidebar()"></div>

        <div class="flex-1 flex flex-col min-h-screen min-w-0">
            <header class="cp-top-bar">
                <div class="flex items-center gap-3 min-w-0">
                    <button type="button" class="md:hidden inline-flex items-center justify-center h-8 w-8 rounded-md bg-[var(--denta-parchment)] border border-[var(--denta-taupe-border)] text-[var(--denta-ink)]" onclick="toggleSidebar()">
                        <i class="fas fa-bars text-sm"></i>
                    </button>
                    <div class="min-w-0">
                        <div class="flex items-center gap-2">
                            <span class="text-[11px] font-bold uppercase tracking-wider text-[var(--denta-muted)]"><?= lang('UI.cpControlPlane') ?></span>
                            <span class="text-[var(--denta-taupe-border)]">/</span>
                            <h1 class="text-sm font-extrabold text-[var(--denta-ink)] truncate m-0"><?= esc($cpDisplayTitle) ?></h1>
                        </div>
                    </div>
                </div>
                <?php $userEmail = (isset($user) && $user && property_exists($user, 'email')) ? $user->email : 'Super Admin'; ?>
                <div class="flex items-center gap-2.5">
                    <button type="button" id="cp-command-trigger" class="cp-top-search hidden md:inline-flex" title="<?= esc(lang('V3Platform.searchOrJumpToCtrlK'), 'attr') ?>">
                        <i class="fas fa-magnifying-glass text-[var(--denta-emerald-pine)]"></i>
                        <span><?= lang('UI.cpSearchPlaceholder') ?></span>
                        <span class="cp-kbd">Ctrl+K</span>
                    </button>
                    <span class="cp-badge cp-badge-teal hidden sm:inline-flex">
                        <i class="fas fa-globe text-[10px]"></i>
                        <?= lang('UI.cpGlobalAdmin') ?>
                    </span>
                    <div class="hidden lg:flex items-center gap-2 text-xs font-bold text-[var(--denta-ink)] px-2">
                        <i class="fas fa-user-circle text-sm text-[var(--denta-muted)]"></i>
                        <span class="truncate max-w-[150px]"><?= esc($userEmail) ?></span>
                    </div>
                    <form action="<?= base_url('controlplane/exit') ?>" method="post" class="m-0">
                        <?= csrf_field() ?>
                        <button type="submit" class="cp-btn cp-btn-primary cp-btn-sm" title="<?= lang('UI.cpExitControlPlane') ?>">
                            <i class="fas fa-arrow-right-from-bracket"></i>
                            <span class="hidden sm:inline"><?= lang('UI.cpExitControlPlane') ?></span>
                        </button>
                    </form>
                </div>
            </header>

            <main class="v3-platform-workspace flex-1 relative">


                <?php if (session()->getFlashdata('success')): ?>
                    <noscript><div class="relative mb-6 glass-card v3-corner px-4 py-3 v3-text-primary" role="status" aria-live="polite">
                        <?= session()->getFlashdata('success') ?>
                    </div></noscript>
                <?php endif; ?>

                <?php if (session()->getFlashdata('error')): ?>
                    <noscript><div class="relative mb-6 glass-card v3-corner px-4 py-3 v3-text-danger" role="alert" aria-live="assertive">
                        <?= session()->getFlashdata('error') ?>
                    </div></noscript>
                <?php endif; ?>

                <div class="relative cp-content">
                    <?= $this->renderSection('content') ?>
                </div>
            </main>
        </div>
    </div>

    <div id="cp-command-palette" class="fixed inset-0 z-50 hidden items-start justify-center v3-backdrop px-4 py-10 backdrop-blur-sm">
        <div class="w-full max-w-2xl cp-modal-box">
            <div class="cp-modal-head">
                <div class="flex items-center gap-2.5">
                    <div class="w-7 h-7 rounded bg-[var(--denta-emerald-pine)] flex items-center justify-center v3-text-on-primary text-xs">
                        <i class="fas fa-terminal"></i>
                    </div>
                    <div>
                        <p class="text-[10px] uppercase tracking-wider text-[var(--denta-muted)] font-bold m-0"><?= lang('UI.cpNavigationSection') ?></p>
                        <h3 class="text-xs font-bold text-[var(--denta-ink)] m-0"><?= lang('UI.cpSearchPlaceholder') ?></h3>
                    </div>
                </div>
                <button type="button" id="cp-command-close" class="p-1.5 v3-text-muted v3-hover-text-ink rounded">
                    <i class="fas fa-times text-sm"></i>
                </button>
            </div>
            <div class="p-3 bg-[var(--denta-parchment)] border-b border-[var(--denta-taupe-border)]">
                <div class="flex items-center gap-2.5 bg-[var(--denta-ivory-input)] border border-[var(--denta-taupe-border)] rounded-md px-3 py-2">
                    <i class="fas fa-search text-[var(--denta-emerald-pine)] text-sm"></i>
                    <input id="cp-command-input" type="text" placeholder="<?= esc(lang('UI.cpSearchPlaceholder')) ?>"
                           class="w-full bg-transparent text-xs font-semibold v3-text-ink placeholder:v3-text-muted focus:outline-none border-none p-0">
                    <span class="cp-kbd">Esc</span>
                </div>
            </div>
            <div class="p-3 max-h-96 overflow-y-auto">
                <div id="cp-command-status" class="px-2 pb-2 text-xs v3-text-muted font-medium"></div>
                <div id="cp-command-results" class="space-y-2"></div>
            </div>
            <div class="cp-modal-foot justify-between text-[11px] font-semibold text-[var(--denta-muted)]">
                <div class="flex items-center gap-2">
                    <span class="cp-kbd">↑ ↓</span>
                    <span><?= lang('UI.cpNavigate') ?></span>
                    <span class="cp-kbd ml-2">Enter</span>
                    <span><?= lang('UI.cpOpen') ?></span>
                    <span class="cp-kbd ml-2">Esc</span>
                    <span><?= lang('UI.cpClose') ?></span>
                </div>
            </div>
        </div>
    </div>

    <script>
        function toggleSidebar() {
            const sidebar = document.getElementById('sidebar');
            const backdrop = document.getElementById('cp-sidebar-backdrop');
            if (!sidebar) return;
            sidebar.classList.toggle('sidebar-mobile-hidden');
            if (window.innerWidth < 768) {
                backdrop.classList.toggle('hidden', sidebar.classList.contains('sidebar-mobile-hidden'));
                document.body.classList.toggle('overflow-hidden', !sidebar.classList.contains('sidebar-mobile-hidden'));
            }
        }

        function closeCpSidebar() {
            if (window.innerWidth >= 768) return;
            const sidebar = document.getElementById('sidebar');
            const backdrop = document.getElementById('cp-sidebar-backdrop');
            if (!sidebar) return;
            sidebar.classList.add('sidebar-mobile-hidden');
            backdrop.classList.add('hidden');
            document.body.classList.remove('overflow-hidden');
        }

        function bindCpSidebarScrollBridge() {
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

        document.addEventListener('keydown', function(e) {
            if (e.ctrlKey && e.key === 'b') {
                e.preventDefault();
                toggleSidebar();
            }
        });

        document.addEventListener('DOMContentLoaded', function() {
            const sidebar = document.getElementById('sidebar');
            const backdrop = document.getElementById('cp-sidebar-backdrop');
            if (!sidebar) return;
            bindCpSidebarScrollBridge();
            if (window.innerWidth < 768) {
                sidebar.classList.add('sidebar-mobile-hidden');
            } else {
                sidebar.classList.remove('sidebar-mobile-hidden');
            }
            if (backdrop) {
                backdrop.classList.add('hidden');
            }
            document.body.classList.remove('overflow-hidden');
        });

        window.addEventListener('resize', function() {
            const sidebar = document.getElementById('sidebar');
            const backdrop = document.getElementById('cp-sidebar-backdrop');
            if (!sidebar) return;
            if (window.innerWidth < 768) {
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
                    item.classList.add('v3-bg-chair');
                } else {
                    item.classList.remove('v3-bg-chair');
                }
            });
        }

        function createSection(title, iconClass) {
            const section = document.createElement('div');
            const heading = document.createElement('div');
            heading.className = 'px-3 text-xs font-semibold uppercase tracking-widest v3-text-muted flex items-center gap-2';
            if (iconClass) {
                const icon = document.createElement('i');
                icon.className = `${iconClass} v3-text-muted`;
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
            button.className = 'w-full text-left v3-radius border v3-border-default v3-bg-panel px-4 py-3 v3-elevation-none transition v3-border-default v3-hover-primary';
            const title = document.createElement('div');
            title.className = 'text-sm font-semibold v3-text-ink';
            title.textContent = label;
            const subtitle = document.createElement('div');
            subtitle.className = 'text-xs v3-text-muted';
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
                const isRtl = document.documentElement.getAttribute('dir') === 'rtl';

                fields.forEach((field) => {
                    const current = (field.value || '').trim();
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
                        if (iso) field.value = iso;
                        if (raw === '') field.value = '';
                        syncDateFieldChrome();
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
                        syncDateFieldChrome();
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
