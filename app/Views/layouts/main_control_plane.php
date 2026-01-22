<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= esc($title ?? 'Control Plane') ?></title>
    <link rel="icon" type="image/png" href="<?= base_url('assets/images/favicon.png') ?>">
    <link rel="stylesheet" href="<?= base_url('assets/css/tailwind.css') ?>?v=<?= time() ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <meta name="theme-color" content="#0f172a">
    <meta name="csrf-name" content="<?= csrf_token() ?>">
    <meta name="csrf-token" content="<?= csrf_hash() ?>">
    <meta name="csrf-header" content="<?= csrf_header() ?>">
</head>
<body class="bg-slate-50 min-h-screen">
    <?= view('components/toast') ?>

    <div class="min-h-screen flex">
        <?php $hideCpNav = $hide_cp_nav ?? false; ?>
        <?php if (!$hideCpNav): ?>
            <aside class="w-64 bg-white border-r border-slate-200 shadow-sm flex flex-col">
                <div class="px-4 py-5 border-b border-slate-200">
                    <a href="<?= base_url('controlplane/dashboard') ?>" class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-emerald-500 text-white flex items-center justify-center shadow">
                            <i class="fas fa-shield-alt"></i>
                        </div>
                        <div>
                            <p class="text-xs uppercase tracking-widest text-slate-400 font-semibold">Control Plane</p>
                            <p class="text-sm font-bold text-slate-900">Global Admin</p>
                        </div>
                    </a>
                </div>
                <nav class="flex-1 px-3 py-4 space-y-1 text-sm">
                    <?php
                    $nav = [
                        ['label' => 'Dashboard', 'url' => 'controlplane/dashboard', 'icon' => 'fas fa-chart-line'],
                        ['label' => 'Clinics', 'url' => 'controlplane/clinics', 'icon' => 'fas fa-hospital-user'],
                        ['label' => 'Plans', 'url' => 'controlplane/plans', 'icon' => 'fas fa-layer-group'],
                        ['label' => 'Plan Requests', 'url' => 'controlplane/plan-requests', 'icon' => 'fas fa-clipboard-check'],
                        ['label' => 'Email Templates', 'url' => 'controlplane/email-templates', 'icon' => 'fas fa-envelope'],
                        ['label' => 'Settings', 'url' => 'controlplane/settings', 'icon' => 'fas fa-sliders-h'],
                        ['label' => 'Console', 'url' => 'controlplane/console', 'icon' => 'fas fa-terminal'],
                        ['label' => 'Operations', 'url' => 'controlplane/operations', 'icon' => 'fas fa-cogs'],
                        ['label' => 'Admin Tools', 'url' => 'controlplane/admin-tools', 'icon' => 'fas fa-toolbox'],
                        ['label' => 'Danger Zone', 'url' => 'controlplane/danger', 'icon' => 'fas fa-exclamation-triangle'],
                    ];
                    ?>
                    <?php foreach ($nav as $item): ?>
                        <?php $isActive = (strpos(uri_string(), $item['url']) === 0); ?>
                        <a href="<?= base_url($item['url']) ?>"
                           class="flex items-center gap-3 px-4 py-2 rounded-xl transition <?= $isActive ? 'bg-emerald-50 text-emerald-700 font-semibold' : 'text-slate-600 hover:bg-slate-100' ?>">
                            <i class="<?= $item['icon'] ?> w-5 text-sm"></i>
                            <span><?= esc($item['label']) ?></span>
                        </a>
                    <?php endforeach; ?>
                </nav>
                <div class="px-4 py-4 border-t border-slate-200 text-xs text-slate-500">
                    Control Plane v2 (replacement)
                </div>
            </aside>
        <?php endif; ?>

        <div class="flex-1 flex flex-col min-h-screen">
            <header class="bg-white border-b border-slate-200 px-6 py-4 flex items-center justify-between">
                <div>
                    <p class="text-xs uppercase tracking-widest text-slate-400 font-semibold">Control Plane</p>
                    <h1 class="text-xl font-bold text-slate-900"><?= esc($title ?? 'Dashboard') ?></h1>
                </div>
                <div class="flex items-center gap-4">
                    <span class="text-xs font-semibold bg-emerald-100 text-emerald-700 px-3 py-1 rounded-full">Global Admin</span>
                    <div class="flex items-center gap-2 text-sm text-slate-600">
                        <i class="fas fa-user-circle text-lg"></i>
                        <span><?= esc($user->email ?? 'Super Admin') ?></span>
                    </div>
                </div>
            </header>

            <main class="flex-1 px-6 py-8">
                <?php if (session()->getFlashdata('success')): ?>
                    <div class="mb-6 p-4 bg-emerald-50 border border-emerald-200 rounded-lg text-emerald-800">
                        <?= session()->getFlashdata('success') ?>
                    </div>
                <?php endif; ?>

                <?php if (session()->getFlashdata('error')): ?>
                    <div class="mb-6 p-4 bg-rose-50 border border-rose-200 rounded-lg text-rose-800">
                        <?= session()->getFlashdata('error') ?>
                    </div>
                <?php endif; ?>

                <?= $this->renderSection('content') ?>
            </main>
        </div>
    </div>
</body>
</html>
