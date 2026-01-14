<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= esc($title) ?></title>
    <link rel="stylesheet" href="<?= base_url('assets/css/tailwind.css') ?>?v=<?= time() ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="min-h-screen bg-gray-50 flex items-center justify-center px-4">
    <div class="max-w-md w-full bg-white rounded-2xl shadow-xl overflow-hidden border border-gray-100">
        <div class="p-8 text-center">
            <div class="w-16 h-16 bg-red-100 rounded-full flex items-center justify-center mx-auto mb-4">
                <i class="fas fa-ban text-red-500 text-2xl"></i>
            </div>
            <h1 class="text-2xl font-bold text-gray-900 mb-2">No Clinic Assigned</h1>
            <p class="text-gray-600 mb-6">
                Your account is not associated with any active clinic. Please contact your system administrator for access.
            </p>
            
            <div class="space-y-3">
                <a href="<?= base_url('auth/logout') ?>" class="block w-full py-3 px-4 bg-gray-100 hover:bg-gray-200 text-gray-700 font-semibold rounded-xl transition-colors">
                    <i class="fas fa-sign-out-alt mr-2"></i> Logout
                </a>
                
                <!-- Optional: Super Admin Recovery Link -->
                <?php 
                $userId = session('user_id');
                $permissionService = service('permission');
                // We use isSuperAdmin check if possible, or just link that is protected by filter
                // If user is super admin, they can access /controlplane/enter
                // But we don't want to expose this to everyone. 
                // We can check permission here.
                if ($userId && $permissionService->isSuperAdmin($userId)): ?>
                    <form action="<?= base_url('controlplane/enter') ?>" method="post">
                        <?= csrf_field() ?>
                        <button type="submit" class="block w-full py-3 px-4 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-xl transition-colors">
                            <i class="fas fa-cogs mr-2"></i> Enter Control Plane
                        </button>
                    </form>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>
</html>
