<?php
helper(['asset','form']);include APPPATH.'Views/layouts/partials/studio_icons.php';
$accessLocale=strtolower((string)($locale??service('request')->getLocale()));
$accessReturn=current_url(true)->__toString();
?>
<!doctype html>
<html lang="<?= esc($accessLocale) ?>" dir="<?= $accessLocale==='ar'?'rtl':'ltr' ?>">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<meta name="theme-color" content="#153f36">
<title>
<?= esc($title??lang('Auth.login_heading')) ?> · Denta+</title>
<link rel="icon" href="<?= base_url('assets/images/favicon.png') ?>">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700;800&family=Noto+Sans+Arabic:wght@400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="<?= base_url('assets/css/studio-theme.css') ?>?v=<?= esc(asset_version('assets/css/studio-theme.css')) ?>">
<link rel="stylesheet" href="<?= base_url('assets/css/studio-access.css') ?>?v=<?= esc(asset_version('assets/css/studio-access.css')) ?>">
<link rel="stylesheet" href="<?= base_url('assets/css/studio-scale.css') ?>?v=<?= esc(asset_version('assets/css/studio-scale.css')) ?>">
<?php $studioThemePlane='public'; include APPPATH.'Views/layouts/partials/studio_effective_theme.php'; ?>
</head>
<body class="access-page">
<a class="access-skip" href="#access-main">
<?= esc(lang('Studio.skip')) ?>
</a>
<header class="access-toolbar">
<a class="access-brand" href="<?= base_url('auth/login') ?>">
<?= $studioIcon('tooth') ?>
<span>denta+</span>
</a>
<a class="access-language" href="<?= base_url('auth/language/'.($accessLocale==='ar'?'en':'ar').'?return_to='.urlencode($accessReturn)) ?>" lang="<?= $accessLocale==='ar'?'en':'ar' ?>">
<?= $studioIcon('globe') ?>
<?= esc(lang('Studio.otherLanguage')) ?>
</a>
</header>
<main id="access-main" class="access-stage">
<section class="access-workspace">
<?= $this->renderSection('content') ?>
</section>

<aside class="access-introduction studio-contour">
<?php $accessIntroduction=$this->renderSection('accessIntroduction');if($accessIntroduction!==''): ?>
<?= $accessIntroduction ?>
<?php else: ?>
<div class="access-emblem">
<?= $studioIcon('clinic') ?>
</div>
<h1>
<?= esc(lang('Studio.accessTitle')) ?>
</h1>
<p>
<?= esc(lang('Studio.accessIntro')) ?>
</p>
<div class="access-capabilities">
<?php foreach([['people','accessRecords'],['calendar','accessAppointments'],['tooth','accessCare']] as [$icon,$key]): ?>
<div>
<?= $studioIcon($icon) ?>
<span>
<?= esc(lang('Studio.'.$key)) ?>
</span>
</div>
<?php endforeach; ?>
</div>
<?php endif; ?>
</aside>
</main>
<footer class="access-footer">
<span>Palsync Dental © <?= date('Y') ?>
</span>
<div>
<span>
<?= esc(lang('Studio.accessSupport')) ?>
</span>
<a href="https://wa.me/970599350450" target="_blank" rel="noopener">+970 599 350 450</a>
<a href="https://wa.me/972569885000" target="_blank" rel="noopener">+972 56 988 5000</a>
</div>
</footer>
<?= view('components/studio_password_controls') ?>
</body>
</html>
