<?php include APPPATH . 'Views/layouts/partials/page_context.php';
$activeLocale = strtolower((string) ($locale ?? service('request')->getLocale()));
include APPPATH . 'Views/layouts/partials/view_permissions.php';
$v4Export = in_array($segment1, ['patient','patients','examination','examinations','treatment','treatments','prescription','prescriptions'],true) && $segment2 === '';
$v4Directory = $segment1 === 'patients' || ($segment1 === 'patient' && $segment2 === '');
$v4Tables = !$v4Directory && !in_array($segment1, ['dashboard','profile'],true) || $v4Directory;
$v4Initials = static function (string $name, int $length=2, string $fallback='U'): string { $parts=preg_split('/\s+/u',trim($name),-1,PREG_SPLIT_NO_EMPTY); $letters=''; foreach (array_slice($parts ?: [],0,$length) as $part) $letters .= mb_substr($part,0,1); return mb_strtoupper($letters ?: $fallback); };
$v4User = is_object($user??null) ? $user : (object)(is_array($user??null)?$user:[]);
$returnToUrl = current_url(true)->__toString();
?>
<!doctype html><html lang="<?= esc($activeLocale) ?>" dir="<?= !empty($is_rtl) ? 'rtl' : 'ltr' ?>" translate="no">
<head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title><?= esc($resolvedPageTitle) ?></title>
<link rel="icon" href="<?= base_url('assets/images/favicon.png') ?>"><meta name="theme-color" content="#162421">
<meta name="csrf-name" content="<?= csrf_token() ?>"><meta name="csrf-token" content="<?= csrf_hash() ?>"><meta name="csrf-header" content="<?= csrf_header() ?>"><meta name="active-clinic-id" content="<?= (int) session('active_clinic_id') ?>"><meta name="api-session-token-url" content="<?= base_url('api/session-token') ?>"><meta name="google" content="notranslate">
<link rel="preconnect" href="https://fonts.googleapis.com"><link rel="preconnect" href="https://fonts.gstatic.com" crossorigin><link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=Noto+Sans+Arabic:wght@400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="<?= base_url('assets/css/tailwind.css') ?>?v=<?= esc(asset_version('assets/css/tailwind.css')) ?>">
<link rel="stylesheet" href="<?= base_url('assets/css/app-drawer.css') ?>?v=<?= esc(asset_version('assets/css/app-drawer.css')) ?>">
<link rel="stylesheet" href="<?= base_url('assets/css/v4-icons.css') ?>?v=<?= esc(asset_version('assets/css/v4-icons.css')) ?>">
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet"><script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<?php if ($v4Tables && (!($disableDataTables ?? false) || ($segment1 === 'finance' && $segment2 === ''))): ?><link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/jquery.dataTables.min.css"><script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script><script src="https://cdn.datatables.net/buttons/2.4.2/js/dataTables.buttons.min.js"></script><script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.html5.min.js"></script><script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.print.min.js"></script><?php endif; ?>
<?php if (in_array($segment1,['reports'],true)||($segment1==='finance'&&$segment2==='reports')): ?><script src="https://cdn.jsdelivr.net/npm/chart.js"></script><?php endif; ?>
<?php if ($v4Export): ?><script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script><script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/pdfmake.min.js"></script><script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/vfs_fonts.js"></script><?php endif; ?>
<link rel="stylesheet" href="<?= base_url('assets/css/v4-system.css') ?>?v=<?= esc(asset_version('assets/css/v4-system.css')) ?>"><link rel="stylesheet" href="<?= base_url('assets/css/v4-workspaces.css') ?>?v=<?= esc(asset_version('assets/css/v4-workspaces.css')) ?>">
<?php include APPPATH . 'Views/layouts/partials/csrf_runtime.php'; ?>
<?php include APPPATH.'Views/layouts/partials/table_runtime.php'; ?>
</head><body class="denta-v4">
<a href="#workspace-content" class="v4-skip"><?= esc(lang('V4.skip')) ?></a>
<?= view('components/toast') ?>
<div class="v4-app">
<?php include APPPATH . 'Views/layouts/partials/clinic_navigation.php'; ?>
<div id="sidebar-backdrop" class="v4-sidebar-scrim hidden" onclick="closeMobileSidebar()"></div>
<div id="main-content" class="v4-app-main">
<header id="main-header" class="v4-topbar">
<div class="v4-topbar-location"><button id="sidebar-toggle" type="button" class="v4-icon-button" onclick="toggleSidebar()" aria-controls="sidebar" aria-expanded="false" aria-label="<?= esc(lang('UI.mainMenu')) ?>"><i class="fas fa-bars" aria-hidden="true"></i></button><span><?= esc($resolvedPageTitle) ?></span></div>
<div class="v4-topbar-actions">
<div id="clinic-switcher" class="v4-clinic-context"><button id="v4-clinic-trigger" type="button" onclick="toggleClinicSwitcher()" aria-label="<?= esc(lang('UI.switchClinic')) ?>"><i class="fas fa-clinic-medical" aria-hidden="true"></i><span><?= esc($clinic['name'] ?? 'Denta+') ?></span><i class="fas fa-chevron-down" aria-hidden="true"></i></button>
<div id="clinicSwitcherMenu" class="v4-popover hidden"><h2><?= esc(lang('UI.switchClinic')) ?></h2><?php foreach (($user_memberships ?? []) as $membership): ?><a href="<?= base_url('clinic/switch/' . (int) $membership['clinic_id']) ?>"><?= esc($membership['clinic_name']) ?></a><?php endforeach; ?></div></div>
<nav class="v4-languages" aria-label="<?= esc(lang('UI.settingsLanguage')) ?>" dir="ltr"><?php foreach (['en'=>'EN','ar'=>'AR'] as $code=>$label): ?><a href="<?= base_url('language/switch/' . $code . '?return_to=' . urlencode($returnToUrl)) ?>" lang="<?= $code ?>" aria-current="<?= $activeLocale === $code ? 'page' : 'false' ?>"><?= $label ?></a><?php endforeach; ?></nav>
<button id="notifications-trigger" type="button" class="v4-icon-button" onclick="toggleNotifications()" aria-controls="notifications-dropdown" aria-expanded="false" aria-label="<?= esc(lang('UI.notificationOpenCenter')) ?>"><i class="fas fa-bell" aria-hidden="true"></i><span id="notification-count" class="v4-notification-count hidden">0</span></button>
<div class="v4-account"><button id="user-menu-trigger" class="v4-avatar" type="button" onclick="toggleUserMenu()" aria-label="<?= esc(lang('UI.profileMenu')) ?>" aria-controls="userMenu" aria-expanded="false"><?= esc($v4Initials(($v4User->first_name ?? '') . ' ' . ($v4User->last_name ?? ''), 2, 'U')) ?></button><div id="userMenu" class="v4-popover hidden"><a href="<?= base_url('profile') ?>"><?= esc(lang('UI.profileMenu')) ?></a><a href="<?= base_url('auth/logout') ?>"><?= esc(lang('UI.logout')) ?></a></div></div>
</div></header>
<?php if (session()->get('is_impersonating')): ?><form action="<?= base_url('controlplane/impersonation/exit') ?>" method="post" class="v4-authority-banner"><?= csrf_field() ?><span><?= esc(lang('UI.cpGlobalAdmin')) ?></span><button class="v4-button" type="submit"><?= esc(lang('UI.cpExitControlPlane')) ?></button></form><?php endif; ?>
<main id="workspace-content" class="v4-workspace"><?= $this->renderSection('content') ?></main>
</div></div>
<nav class="v4-mobile-dock" aria-label="<?= esc(lang('UI.mainMenu')) ?>"><?php foreach ([['dashboard','dashboard','fa-house','V4.today'],['appointments','appointments','fa-calendar','UI.menuAppointments'],['patients','patients','fa-users','UI.menuPatients']] as [$destination,$module,$icon,$label]): ?><?php if ($v4Can($module,'view')): ?><a href="<?= base_url($destination) ?>" aria-current="<?= $segment1 === $destination ? 'page' : 'false' ?>"><i class="fas <?= esc($icon) ?>" aria-hidden="true"></i><span><?= esc(lang($label)) ?></span></a><?php endif; ?><?php endforeach; ?><button type="button" onclick="toggleSidebar()"><i class="fas fa-bars" aria-hidden="true"></i><span><?= esc(lang('V4.more')) ?></span></button></nav>
<?php
$layoutQuickCreateUri = trim(uri_string(), '/');
$layoutRendersQuickCreateModals = ($v4Can('patients','create') || $v4Can('appointments','create') || $v4Can('examinations','create'));
if ($layoutRendersQuickCreateModals) echo view('components/global_quick_create_modals', ['is_rtl'=>!empty($is_rtl)]);
include APPPATH . 'Views/layouts/partials/notification_center.php';
include APPPATH . 'Views/layouts/partials/notification_runtime.php';
?>
<script src="<?= base_url('assets/js/select2-init.js') ?>?v=<?= esc(asset_version('assets/js/select2-init.js')) ?>"></script>
<?php foreach (['app-modal','navigation-context','app-drawer','contextual-workspace','v4-shell'] as $script): ?><script src="<?= base_url('assets/js/' . $script . '.js') ?>?v=<?= esc(asset_version('assets/js/' . $script . '.js')) ?>"></script><?php endforeach; ?>
</body></html>
