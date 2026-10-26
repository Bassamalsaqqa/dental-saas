<?php helper('asset');include APPPATH.'Views/layouts/partials/studio_icons.php';$cpLocale=strtolower((string)($locale??service('request')->getLocale()));$cpActor=(object)($user??[]);$cpName=trim(($cpActor->first_name??'').' '.($cpActor->last_name??''))?:($cpActor->email??lang('UI.cpGlobalAdmin'));$cpPath=uri_string();$cpReturn=current_url(true)->__toString();$cpNav=[['dashboard','home','UI.menuDashboard'],['clinics','clinic','UI.cpClinics'],['users','people','UI.cpUsers'],['onboarding/clinic/create','plus','UI.cpOnboarding'],['plans','chart','UI.cpPlans'],['invoices','wallet','UI.cpInvoices'],['plan-requests','report','UI.cpRequests'],['operations','settings','UI.cpOperations'],['whatsapp','message','UI.cpWhatsAppOps'],['activity-log','clock','UI.cpSystemActivity'],['settings','settings','UI.cpSettings'],['roles','shield','UI.menuRoleManagement']]; ?>
<!doctype html>
<html lang="<?= esc($cpLocale) ?>" dir="<?= $cpLocale==='ar'?'rtl':'ltr' ?>">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>
<?= esc($title??lang('UI.cpControlPlane')) ?> · Denta+</title>
<meta name="theme-color" content="#153f36">
<link rel="icon" href="<?= base_url('assets/images/favicon.png') ?>">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700;800&family=Noto+Sans+Arabic:wght@400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="<?= base_url('assets/css/studio.css') ?>?v=<?= esc(asset_version('assets/css/studio.css')) ?>">
<link rel="stylesheet" href="<?= base_url('assets/css/studio-platform.css') ?>?v=<?= esc(asset_version('assets/css/studio-platform.css')) ?>">
<link rel="stylesheet" href="<?= base_url('assets/css/studio-theme.css') ?>?v=<?= esc(asset_version('assets/css/studio-theme.css')) ?>">
<link rel="stylesheet" href="<?= base_url('assets/css/studio-workspace.css') ?>?v=<?= esc(asset_version('assets/css/studio-workspace.css')) ?>">
<meta name="csrf-name" content="<?= csrf_token() ?>">
<meta name="csrf-token" content="<?= csrf_hash() ?>">
<meta name="csrf-header" content="<?= csrf_header() ?>">
<?= $this->renderSection('styles') ?>
</head>
<body class="studio">
<a href="#main" class="skip">
<?= esc(lang('Studio.skip')) ?>
</a>
<div class="application" id="application">
<aside class="navigation platform-navigation" id="studio-nav" aria-label="<?= esc(lang('UI.cpControlPlane')) ?>">
<div class="brand-row">
<a href="<?= base_url('controlplane/dashboard') ?>" class="brand">
<span class="brand-symbol">
<?= $studioIcon('shield') ?>
</span>
<span>denta<span class="brand-plus">+</span>
</span>
</a>
<button type="button" class="icon-button mobile-only nav-close" data-nav-close aria-label="<?= esc(lang('Studio.close')) ?>">
<?= $studioIcon('close') ?>
</button>
</div>
<div class="platform-identity">
<strong>
<?= esc(lang('UI.cpControlPlane')) ?>
</strong>
<span>
<?= esc(lang('UI.cpGlobalAdmin')) ?>
</span>
</div>
<nav class="nav-groups">
<section class="nav-group">
<?php foreach($cpNav as [$route,$icon,$label]):$active=str_starts_with($cpPath,'controlplane/'.$route); ?>
<a class="nav-link <?= $active?'active':'' ?>" href="<?= base_url('controlplane/'.$route) ?>" <?= $active?'aria-current="page"':'' ?>>
<?= $studioIcon($icon) ?>
<span>
<?= esc(lang($label)) ?>
</span>
<?php if($active): ?>
<span class="nav-active-dot">
</span>
<?php endif; ?>
</a>
<?php endforeach; ?>
</section>
</nav>
<footer class="nav-bottom">
<div class="nav-person">
<span class="avatar">
<?= esc($studioInitials($cpName)) ?>
</span>
<span>
<strong>
<?= esc($cpName) ?>
</strong>
<small>
<?= esc(lang('UI.cpGlobalAdmin')) ?>
</small>
</span>
</div>
</footer>
</aside>
<div class="nav-backdrop" data-nav-close hidden>
</div>
<div class="workspace" id="studio-workspace">
<header class="toolbar">
<div class="toolbar-location">
<button class="icon-button mobile-only" type="button" data-nav-open aria-controls="studio-nav" aria-expanded="false" aria-label="<?= esc(lang('Studio.menu')) ?>">
<?= $studioIcon('menu') ?>
</button>
<strong>
<?= esc(lang('UI.cpControlPlane')) ?>
</strong>
</div>
<div class="toolbar-actions">
<button type="button" class="search-trigger" data-dialog="platform-search-dialog">
<?= $studioIcon('search') ?>
<span>
<?= esc(lang('UI.cpSearchPlaceholder')) ?>
</span>
<kbd>Ctrl K</kbd>
</button>
<a class="language-switch" href="<?= base_url('language/switch/'.($cpLocale==='ar'?'en':'ar').'?return_to='.urlencode($cpReturn)) ?>" lang="<?= $cpLocale==='ar'?'en':'ar' ?>">
<?= $studioIcon('globe') ?>
<span>
<?= esc(lang('Studio.otherLanguage')) ?>
</span>
</a>
<form action="<?= base_url('controlplane/exit') ?>" method="post" class="platform-exit">
<?= csrf_field() ?>
<button type="submit" class="button secondary">
<?= esc(lang('UI.cpExitControlPlane')) ?>
</button>
</form>
</div>
</header>
<main id="main" class="canvas">
<?php if(session('success')): ?>
<div class="studio-flash" role="status">
<p>
<?= esc(strip_tags((string)session('success'))) ?>
</p>
</div>
<?php endif; ?>
<?= $this->renderSection('content') ?>
</main>
</div>
</div>
<dialog id="platform-search-dialog" class="command-dialog" aria-labelledby="platform-search-title">
<header>
<h2 id="platform-search-title">
<?= esc(lang('UI.cpSearchPlaceholder')) ?>
</h2>
<button type="button" class="icon-button" data-close aria-label="<?= esc(lang('Studio.close')) ?>">
<?= $studioIcon('close') ?>
</button>
</header>
<div class="command-field">
<?= $studioIcon('search') ?>
<input id="platform-search" type="search" autocomplete="off" aria-label="<?= esc(lang('UI.cpSearchPlaceholder')) ?>" data-recent-label="<?= esc(lang('UI.cpRecentSection'),'attr') ?>" data-url="<?= base_url('controlplane/search/global') ?>" data-base="<?= base_url() ?>" data-empty="<?= esc(lang('UI.cpNoResults'),'attr') ?>" data-error="<?= esc(lang('UI.cpSearchFailed'),'attr') ?>" data-start="<?= esc(lang('UI.cpTypeToSearch'),'attr') ?>">
</div>
<div class="command-results" id="platform-search-results" aria-live="polite">
<p>
<?= esc(lang('UI.cpTypeToSearch')) ?>
</p>
</div>
</dialog>
<script src="<?= base_url('assets/js/studio.js') ?>?v=<?= esc(asset_version('assets/js/studio.js')) ?>" defer>
</script>
<script src="<?= base_url('assets/js/studio-platform.js') ?>?v=<?= esc(asset_version('assets/js/studio-platform.js')) ?>" defer>
</script>
<?= $this->renderSection('scripts') ?>
<script src="<?= base_url('assets/js/studio-grid.js') ?>?v=<?= esc(asset_version('assets/js/studio-grid.js')) ?>" defer></script>
</body>
</html>
