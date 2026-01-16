<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?? 'Control Plane' ?> | System Authority</title>
    <link rel="icon" type="image/png" href="<?= base_url('assets/images/favicon.png') ?>">
    <link rel="stylesheet" href="<?= base_url('assets/css/tailwind.css') ?>?v=<?= time() ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <meta name="csrf-name" content="<?= csrf_token() ?>">
    <meta name="csrf-token" content="<?= csrf_hash() ?>">
    <style>
        body { background-color: #f8fafc; color: #1e293b; font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, "Liberation Mono", "Courier New", monospace; }
        .sidebar { background-color: #0f172a; border-right: 1px solid #334155; }
        .nav-link { color: #94a3b8; font-size: 0.75rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; padding: 0.75rem 1rem; border-radius: 0.25rem; }
        .nav-link:hover { background-color: #1e293b; color: #f8fafc; }
        .nav-link.active { background-color: #334155; color: #f8fafc; }
        header { background-color: #ffffff; border-bottom: 1px solid #e2e8f0; }
        .status-badge { font-size: 0.65rem; font-weight: 900; text-transform: uppercase; letter-spacing: 0.1em; padding: 0.25rem 0.75rem; border-radius: 9999px; }
    </style>
</head>
<body>
    <div class="flex min-h-screen">
        <!-- Authority Sidebar -->
        <aside class="sidebar w-64 flex-shrink-0 flex flex-col z-40">
            <div class="p-6 border-b border-slate-800">
                <div class="flex items-center space-x-3">
                    <i class="fas fa-shield-alt text-slate-400"></i>
                    <div>
                        <h1 class="text-sm font-black text-slate-200 uppercase tracking-tighter">Control Plane</h1>
                        <p class="text-[9px] text-slate-500 uppercase tracking-widest font-bold">System Authority</p>
                    </div>
                </div>
            </div>

            <nav class="flex-1 p-4 space-y-1">
                <a href="<?= base_url('controlplane/dashboard') ?>" class="nav-link block <?= (uri_string() == 'controlplane/dashboard') ? 'active' : '' ?>">
                    Dashboard
                </a>
                <a href="<?= base_url('controlplane/onboarding/clinic/create') ?>" class="nav-link block <?= (strpos(uri_string(), 'onboarding') !== false) ? 'active' : '' ?>">
                    Onboarding
                </a>
                <a href="<?= base_url('controlplane/plans') ?>" class="nav-link block <?= (strpos(uri_string(), 'plans') !== false) ? 'active' : '' ?>">
                    Plan Management
                </a>
                <a href="<?= base_url('controlplane/settings') ?>" class="nav-link block <?= (strpos(uri_string(), 'controlplane/settings') !== false) ? 'active' : '' ?>">
                    Configuration
                </a>
            </nav>

            <div class="p-4 border-t border-slate-800 bg-slate-950/50">
                <p class="text-[9px] font-bold text-slate-600 uppercase tracking-widest px-4">
                    Session Isolation Active
                </p>
            </div>
        </aside>

        <!-- Main Content -->
        <div class="flex-1 flex flex-col min-w-0">
            <header class="h-14 flex items-center justify-between px-8 sticky top-0 z-30">
                <h2 class="text-xs font-black text-slate-500 uppercase tracking-[0.2em]"><?= $title ?? 'Dashboard' ?></h2>
                <div class="flex items-center space-x-4">
                    <span class="status-badge bg-slate-100 text-slate-600 border border-slate-200">Mode: Global</span>
                </div>
            </header>

            <main class="flex-1 p-8">
                <!-- Notifications -->
                <?php if (session()->getFlashdata('success')): ?>
                    <div class="mb-6 p-3 bg-slate-900 text-slate-200 text-[11px] font-bold border-l-4 border-emerald-500">
                        SUCCESS: <?= session()->getFlashdata('success') ?>
                    </div>
                <?php endif; ?>
                
                <?php if (session()->getFlashdata('error')): ?>
                    <div class="mb-6 p-3 bg-slate-900 text-slate-200 text-[11px] font-bold border-l-4 border-rose-500">
                        ERROR: <?= session()->getFlashdata('error') ?>
                    </div>
                <?php endif; ?>

                <?= $this->renderSection('content') ?>
            </main>
        </div>
    </div>
</body>
</html>
