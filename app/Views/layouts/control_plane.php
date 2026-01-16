<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CONTROL PLANE — <?= esc($title ?? 'CONSOLE') ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
    <body class="bg-slate-50 text-slate-900 font-sans antialiased">
    <div class="min-h-screen flex flex-col">
        <!-- Top Navigation -->
        <?php $hideNav = isset($hide_nav) && $hide_nav; ?>
        <nav class="bg-slate-900 text-white border-b border-slate-800">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex items-center justify-between h-16">
                    <div class="flex items-center">
                        <div class="flex-shrink-0 font-black text-sm tracking-[0.2em] text-indigo-400 uppercase">
                            Control Plane
                        </div>
                        <?php if (!$hideNav): ?>
                            <div class="hidden md:block">
                                <div class="ml-10 flex items-baseline space-x-4">
                                    <a href="<?= base_url('controlplane/dashboard') ?>" class="px-3 py-2 rounded-md text-sm font-medium text-slate-300 hover:text-white hover:bg-slate-800 transition-colors">Dashboard</a>
                                    <a href="<?= base_url('controlplane/console') ?>" class="px-3 py-2 rounded-md text-sm font-medium text-white bg-slate-800 transition-colors">Console</a>
                                    <a href="<?= base_url('controlplane/operations') ?>" class="px-3 py-2 rounded-md text-sm font-medium text-slate-300 hover:text-white hover:bg-slate-800 transition-colors">Operations</a>
                                    <a href="<?= base_url('controlplane/settings') ?>" class="px-3 py-2 rounded-md text-sm font-medium text-slate-300 hover:text-white hover:bg-slate-800 transition-colors">Settings</a>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </nav>

        <!-- Main Content -->
        <main class="flex-grow py-10">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <?= $this->renderSection('content') ?>
            </div>
        </main>

        <!-- Footer -->
        <footer class="bg-white border-t border-slate-200 mt-auto">
            <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                <p class="text-center text-xs text-slate-400 uppercase tracking-widest">
                    Control Plane Authority Session &bull; Global Mode Active
                </p>
            </div>
        </footer>
    </div>
</body>
</html>
