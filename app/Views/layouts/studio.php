<?php
include APPPATH.'Views/layouts/partials/page_context.php';
include APPPATH.'Views/layouts/partials/view_permissions.php';
include APPPATH.'Views/layouts/partials/studio_icons.php';
$studioLocale=strtolower((string)($locale ?? service('request')->getLocale()));
$studioRtl=$studioLocale==='ar';$studioActor=(object)($user??[]);
$studioName=trim(($studioActor->first_name??'').' '.($studioActor->last_name??'')) ?: ($studioActor->email??'Denta+');
$studioReturn=current_url(true)->__toString();
$studioPageTitle=$segment1==='dashboard'?lang('Studio.overview'):$resolvedPageTitle;
$studioIsActive=static function(string $url)use($segment1):bool{
 $aliases=['patients'=>['patient','patients'],'appointments'=>['appointment','appointments'],'examinations'=>['examination','examinations'],'treatments'=>['treatment','treatments'],'prescriptions'=>['prescription','prescriptions']];
 return in_array($segment1,$aliases[$url]??[$url],true);
};
$studioNav=[
'workspace'=>[['dashboard','Studio.overview','home','dashboard'],['appointments','UI.menuAppointments','calendar','appointments'],['patients','UI.menuPatients','people','patients']],
'clinical'=>[['examinations','UI.menuExaminations','chart','examinations'],['treatments','UI.menuTreatments','tooth','treatments'],['prescriptions','UI.menuPrescriptions','pill','prescriptions'],['finance','UI.menuFinance','wallet','finance'],['inventory','UI.menuInventory','box','inventory'],['providers','UI.menuProviders','people','@providers'],['reports','UI.menuReports','report','reports']],
'manage'=>[['users','UI.menuUserManagement','people','users'],['roles','UI.menuRoleManagement','shield','users','roles'],['activity-log','UI.menuActivityLog','clock','activity_log'],['settings','UI.menuSettings','settings','settings']],
];
?>
<!doctype html>
<html lang="<?= esc($studioLocale) ?>" dir="<?= $studioRtl?'rtl':'ltr' ?>">
<head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title><?= esc($studioPageTitle) ?> · Denta+</title>
<meta name="theme-color" content="#153f36">
<link rel="icon" href="<?= base_url('assets/images/favicon.png') ?>">
<link rel="preconnect" href="https://fonts.googleapis.com"><link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;650;700;750;800&family=Noto+Sans+Arabic:wght@400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="<?= base_url('assets/css/studio.css') ?>?v=<?= esc(asset_version('assets/css/studio.css')) ?>">
<meta name="csrf-name" content="<?= csrf_token() ?>"><meta name="csrf-token" content="<?= csrf_hash() ?>"><meta name="csrf-header" content="<?= csrf_header() ?>">
<?= $this->renderSection('styles') ?>
</head>
<body class="studio">
<a class="skip" href="#main"><?= esc(lang('Studio.skip')) ?></a>
<div class="application" id="application">
<aside class="navigation" id="studio-nav" aria-label="<?= esc(lang('UI.mainMenu')) ?>">
<div class="brand-row"><a href="<?= base_url('dashboard') ?>" class="brand"><span class="brand-symbol"><?= $studioIcon('tooth') ?></span><span>denta<span class="brand-plus">+</span></span></a><button class="icon-button mobile-only nav-close" data-nav-close aria-label="<?= esc(lang('Studio.close')) ?>"><?= $studioIcon('close') ?></button></div>
<button class="clinic-picker" popovertarget="clinic-menu"><span class="clinic-symbol"><?= $studioIcon('clinic') ?></span><span><strong><?= esc($clinic['name']??'Denta+') ?></strong><small><?= esc(lang('Studio.live')) ?></small></span></button>
<nav class="nav-groups">
<?php foreach($studioNav as $group=>$items): $visible=array_filter($items,static fn($item)=>($item[3]==='@providers'?($v4Can('inventory','view')||$v4Can('finance','view')):$v4Can($item[3],$item[4]??'view'))); if(!$visible)continue; ?>
<?php if($group==='manage'): ?><details class="nav-management"><summary><?= esc(lang('Studio.'.$group)) ?></summary><?php else: ?><section class="nav-group"><h2><?= esc(lang('Studio.'.$group)) ?></h2><?php endif; ?>
<?php foreach($visible as [$url,$label,$icon]): ?><a class="nav-link <?= $studioIsActive($url)?'active':'' ?>" <?= $studioIsActive($url)?'aria-current="page"':'' ?> href="<?= base_url($url) ?>"><?= $studioIcon($icon) ?><span><?= esc(lang($label)) ?></span><?php if($studioIsActive($url)): ?><span class="nav-active-dot"></span><?php endif; ?></a><?php endforeach; ?>
<?= $group==='manage'?'</details>':'</section>' ?>
<?php endforeach; ?>
</nav>
<footer class="nav-bottom"><button data-dialog="help-dialog" class="nav-link"><?= $studioIcon('help') ?><span><?= esc(lang('Studio.help')) ?></span></button><div class="nav-person"><span class="avatar"><?= esc($studioInitials($studioName)) ?></span><span><strong><?= esc($studioName) ?></strong><small><?= esc(lang('Studio.account')) ?></small></span><a href="<?= base_url('profile') ?>" class="icon-button" aria-label="<?= esc(lang('Studio.profile')) ?>"><?= $studioIcon('settings') ?></a></div></footer>
</aside>
<div class="nav-backdrop" data-nav-close hidden></div>
<div class="workspace" id="studio-workspace">
<header class="toolbar">
<div class="toolbar-location"><button class="icon-button mobile-only" data-nav-open aria-controls="studio-nav" aria-expanded="false" aria-label="<?= esc(lang('Studio.menu')) ?>"><?= $studioIcon('menu') ?></button><span class="toolbar-breadcrumb"><?= esc(lang('Studio.workspace')) ?></span><span class="breadcrumb-divider">/</span><strong><?= esc($studioPageTitle) ?></strong></div>
<div class="toolbar-actions">
<?php if($v4Can('patients','view')): ?><button class="search-trigger" data-dialog="search-dialog"><?= $studioIcon('search') ?><span><?= esc(lang('Studio.searchShort')) ?></span><kbd>Ctrl K</kbd></button><?php endif; ?>
<a class="language-switch" href="<?= base_url('language/switch/'.($studioLocale==='ar'?'en':'ar').'?return_to='.urlencode($studioReturn)) ?>" lang="<?= $studioLocale==='ar'?'en':'ar' ?>" aria-label="<?= esc(lang('Studio.switchLanguage'),'attr') ?>"><?= $studioIcon('globe') ?><span><?= esc(lang('Studio.otherLanguage')) ?></span></a>
<button class="icon-button notification-trigger" popovertarget="notification-menu" aria-label="<?= esc(lang('Studio.notifications')) ?>"><?= $studioIcon('bell') ?></button>
<span class="toolbar-rule"></span><button class="avatar avatar--light" popovertarget="account-menu" aria-label="<?= esc(lang('Studio.account')) ?>"><?= esc($studioInitials($studioName)) ?></button>
</div>
</header>
<main id="main" class="canvas"><?php if(session('success')): ?><div class="studio-flash" role="status"><?= $studioIcon('check') ?><p><?= esc(session('success')) ?></p></div><?php endif; ?><?= $this->renderSection('content') ?></main>
</div></div>
<div id="clinic-menu" popover class="popover clinic-popover"><h2><?= esc(lang('Studio.clinic')) ?></h2>
<?php if(empty($user_memberships)): ?><p><?= esc($clinic['name']??'Denta+') ?></p><?php endif; ?>
<?php foreach(($user_memberships??[]) as $membership): ?><a href="<?= base_url('clinic/switch/'.(int)$membership['clinic_id']) ?>"><?= $studioIcon('clinic') ?><span><?= esc($membership['clinic_name']) ?></span><?php if((int)$membership['clinic_id']===(int)session('active_clinic_id')): ?><?= $studioIcon('check') ?><?php endif; ?></a><?php endforeach; ?></div>
<div id="account-menu" popover class="popover account-popover"><div class="account-intro"><strong><?= esc($studioName) ?></strong><small><?= esc($studioActor->email??'') ?></small></div><a href="<?= base_url('profile') ?>"><?= $studioIcon('people') ?><span><?= esc(lang('Studio.profile')) ?></span></a><a href="<?= base_url('auth/logout') ?>"><span><?= esc(lang('Studio.signOut')) ?></span></a></div>
<div id="notification-menu" popover class="popover notification-popover" data-url="<?= base_url('api/notifications') ?>"><header><h2><?= esc(lang('Studio.notifications')) ?></h2><?= $studioIcon('bell') ?></header><div class="notification-content" aria-live="polite" data-empty="<?= esc(lang('Studio.notificationEmpty'),'attr') ?>" data-help="<?= esc(lang('Studio.notificationHelp'),'attr') ?>" data-error="<?= esc(lang('Studio.notificationFail'),'attr') ?>"></div><button class="text-button notification-retry" hidden><?= esc(lang('Studio.retry')) ?></button><a class="popover-footer" href="<?= base_url('notifications') ?>"><?= esc(lang('Studio.allNotifications')) ?></a></div>
<?php if($v4Can('patients','view')): ?><dialog id="search-dialog" aria-labelledby="search-title" class="command-dialog"><header><h2 id="search-title"><?= esc(lang('Studio.search')) ?></h2><button class="icon-button" data-close aria-label="<?= esc(lang('Studio.close')) ?>"><?= $studioIcon('close') ?></button></header><div class="command-field"><?= $studioIcon('search') ?><input type="search" id="patient-search" autocomplete="off" placeholder="<?= esc(lang('Studio.searchHint'),'attr') ?>" aria-label="<?= esc(lang('Studio.searchHint')) ?>" data-url="<?= base_url('api/search/patients') ?>" data-record-base="<?= base_url('patient/') ?>" data-empty="<?= esc(lang('Studio.searchEmpty'),'attr') ?>" data-error="<?= esc(lang('Studio.searchFail'),'attr') ?>"></div><div id="search-results" class="command-results" aria-live="polite"><p><?= esc(lang('Studio.searchStart')) ?></p></div><footer><?php if($v4Can('patients','view')): ?><a href="<?= base_url('patients') ?>"><?= esc(lang('Studio.registry')) ?></a><?php endif; ?><kbd>Esc</kbd></footer></dialog>
<?php endif; ?><dialog id="create-dialog" aria-labelledby="create-title" class="create-dialog"><header><div><h2 id="create-title"><?= esc(lang('Studio.new')) ?></h2><p><?= esc(lang('Studio.newHelp')) ?></p></div><button class="icon-button" data-close aria-label="<?= esc(lang('Studio.close')) ?>"><?= $studioIcon('close') ?></button></header>
<?php foreach([['patients','patient/create','people','register','registerHelp'],['appointments','appointment/create','calendar','book','bookHelp']] as [$module,$url,$icon,$titleKey,$helpKey]): if(!$v4Can($module,'create'))continue; ?><a class="create-choice" href="<?= base_url($url) ?>"><span class="choice-icon"><?= $studioIcon($icon) ?></span><span><strong><?= esc(lang('Studio.'.$titleKey)) ?></strong><small><?= esc(lang('Studio.'.$helpKey)) ?></small></span></a><?php endforeach; ?></dialog>
<dialog id="help-dialog" aria-labelledby="help-title" class="help-dialog"><header><div><h2 id="help-title"><?= esc(lang('Studio.help')) ?></h2><p><?= esc(lang('Studio.helpText')) ?></p></div><button class="icon-button" data-close aria-label="<?= esc(lang('Studio.close')) ?>"><?= $studioIcon('close') ?></button></header><dl><div><dt><?= esc(lang('Studio.command')) ?></dt><dd><kbd>Ctrl</kbd> <kbd>K</kbd></dd></div><div><dt><?= esc(lang('Studio.escape')) ?></dt><dd><kbd>Esc</kbd></dd></div><div><dt><?= esc(lang('Studio.tab')) ?></dt><dd><kbd>Tab</kbd></dd></div></dl></dialog>
<script src="<?= base_url('assets/js/studio.js') ?>?v=<?= esc(asset_version('assets/js/studio.js')) ?>" defer></script>
<?= $this->renderSection('scripts') ?>
</body></html>