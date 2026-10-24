<!DOCTYPE html>
<html lang="<?= esc($locale ?? 'en') ?>" dir="<?= !empty($is_rtl) ? 'rtl' : 'ltr' ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= esc($title) ?></title>
    <link rel="stylesheet" href="<?= base_url('assets/css/tailwind.css') ?>?v=<?= time() ?>">
    <link rel="stylesheet" href="<?= base_url('assets/vendor/fontawesome/css/all.min.css') ?>?v=<?= time() ?>">
    <style>
        [dir="rtl"] i.mr-1 { margin-right: 0 !important; margin-left: 0.25rem !important; }
        [dir="rtl"] i.mr-2 { margin-right: 0 !important; margin-left: 0.5rem !important; }
        [dir="rtl"] i.mr-3 { margin-right: 0 !important; margin-left: 0.75rem !important; }
        [dir="rtl"] .text-left { text-align: right !important; }
    </style>
</head>
<body class="min-h-screen v3-bg-panel flex items-center justify-center px-4">
    <div class="max-w-md w-full v3-bg-panel v3-radius v3-elevation-none overflow-hidden border v3-border-default">
        <div class="p-8 text-center">
            <div class="w-16 h-16 v3-bg-alert rounded-full flex items-center justify-center mx-auto mb-4">
                <i class="fas fa-ban v3-text-danger text-2xl"></i>
            </div>
            <h1 class="text-2xl font-bold v3-text-ink mb-2">No Clinic Assigned</h1>
            <p class="v3-text-muted mb-6">
                Your account is not associated with any active clinic. Please contact your system administrator for access.
            </p>
            
            <div class="space-y-3">
                <a href="<?= base_url('auth/logout') ?>" class="block w-full py-3 px-4 v3-bg-soft v3-hover-soft v3-text-ink font-semibold v3-radius transition-colors">
                    <i class="fas fa-sign-out-alt mr-2"></i> Logout
                </a>
                
                <!-- Optional: Super Admin Recovery Link -->
                <?php if (!empty($is_super_admin)): ?>
                    <form action="<?= base_url('controlplane/enter') ?>" method="post">
                        <?= csrf_field() ?>
                        <button type="submit" class="block w-full py-3 px-4 v3-bg-primary v3-hover-primary v3-text-on-primary font-semibold v3-radius transition-colors v3-btn v3-btn--primary">
                            <i class="fas fa-cogs mr-2"></i> Enter Control Plane
                        </button>
                    </form>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>
</html>
