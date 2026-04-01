<!DOCTYPE html>
<html lang="<?= esc($locale ?? 'ar') ?>" dir="<?= !empty($is_rtl) ? 'rtl' : 'ltr' ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= esc($title ?? 'Self Check-In') ?></title>
    <link rel="icon" type="image/png" href="<?= base_url('assets/images/favicon.png') ?>">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=JetBrains+Mono:wght@400;500;600&display=swap" rel="stylesheet">
    <?php $tailwindVersion = @filemtime(FCPATH . 'assets/css/tailwind.css') ?: '1'; ?>
    <link rel="stylesheet" href="<?= base_url('assets/css/tailwind.css') ?>?v=<?= $tailwindVersion ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <meta name="csrf-name" content="<?= csrf_token() ?>">
    <meta name="csrf-token" content="<?= csrf_hash() ?>">
    <meta name="csrf-header" content="<?= csrf_header() ?>">
    <style>
        :root {
            --app-font-sans: "Inter", ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Roboto", "Noto Sans", sans-serif;
            --app-font-ar: ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", "Roboto", "Noto Sans", sans-serif;
        }

        body,
        button,
        input,
        select,
        textarea {
            font-family: var(--app-font-sans);
        }

        html[lang^="ar"] body,
        html[lang^="ar"] button,
        html[lang^="ar"] input,
        html[lang^="ar"] select,
        html[lang^="ar"] textarea {
            font-family: var(--app-font-ar);
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
        [dir="rtl"] .text-left { text-align: right !important; }
    </style>
</head>
<body class="bg-gradient-to-br from-slate-50 via-blue-50 to-indigo-100 min-h-screen">
    <?= view('components/toast') ?>
    <?= $this->renderSection('content') ?>
</body>
</html>
