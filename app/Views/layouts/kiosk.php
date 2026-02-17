<!DOCTYPE html>
<html lang="en" dir="auto">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= esc($title ?? 'Self Check-In') ?></title>
    <link rel="icon" type="image/png" href="<?= base_url('assets/images/favicon.png') ?>">
    <?php $tailwindVersion = @filemtime(FCPATH . 'assets/css/tailwind.css') ?: '1'; ?>
    <link rel="stylesheet" href="<?= base_url('assets/css/tailwind.css') ?>?v=<?= $tailwindVersion ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <meta name="csrf-name" content="<?= csrf_token() ?>">
    <meta name="csrf-token" content="<?= csrf_hash() ?>">
    <meta name="csrf-header" content="<?= csrf_header() ?>">
</head>
<body class="bg-gradient-to-br from-slate-50 via-blue-50 to-indigo-100 min-h-screen" style="font-family: 'Segoe UI', Tahoma, Arial, sans-serif;">
    <?= view('components/toast') ?>
    <?= $this->renderSection('content') ?>
</body>
</html>
