<?php helper('asset'); ?>
<!doctype html>
<html lang="<?= esc($locale??'ar') ?>" dir="<?= !empty($is_rtl)?'rtl':'ltr' ?>">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>
<?= esc($title??lang('Kiosk.title')) ?>
</title>
<meta name="theme-color" content="#153f36">
<link rel="icon" href="<?= base_url('assets/images/favicon.png') ?>">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700;800&family=Noto+Sans+Arabic:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="<?= base_url('assets/css/studio-theme.css') ?>?v=<?= esc(asset_version('assets/css/studio-theme.css')) ?>">
<link rel="stylesheet" href="<?= base_url('assets/css/studio-kiosk.css') ?>?v=<?= esc(asset_version('assets/css/studio-kiosk.css')) ?>">
<meta name="csrf-name" content="<?= csrf_token() ?>">
<meta name="csrf-token" content="<?= csrf_hash() ?>">
<meta name="csrf-header" content="<?= csrf_header() ?>">
</head>
<body>
<div id="kiosk-toast" class="kiosk-toast hidden" role="status" aria-live="polite">
</div>
<?= $this->renderSection('content') ?>
</body>
</html>