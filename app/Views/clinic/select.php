<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= esc($title) ?></title>
    <link rel="stylesheet" href="<?= base_url('assets/css/tailwind.css') ?>?v=<?= time() ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <meta name="theme-color" content="#0284c7">
</head>
<body class="min-h-screen bg-gradient-to-br from-gray-50 to-blue-50/30 relative overflow-hidden">
    <!-- Main Container -->
    <div class="relative z-10 min-h-screen flex items-center justify-center px-4 py-8">
        <div class="w-full max-w-md">
            <!-- Card -->
            <div class="relative backdrop-blur-xl bg-white/90 border border-white/40 rounded-2xl shadow-xl shadow-blue-500/5 transition-all duration-500 overflow-hidden">
                <!-- Header -->
                <div class="relative p-6 text-center border-b border-white/30">
                    <div class="absolute inset-0 bg-gradient-to-br from-blue-100/20 to-purple-100/20 rounded-t-2xl"></div>
                    <div class="relative z-10">
                        <div class="w-16 h-16 bg-gradient-to-br from-blue-500 to-purple-600 rounded-xl flex items-center justify-center shadow-lg mx-auto mb-4">
                            <i class="fas fa-hospital-user text-white text-2xl"></i>
                        </div>
                        <h1 class="text-2xl font-black text-gray-900 mb-2">Select Clinic</h1>
                        <p class="text-sm text-gray-600 font-medium">Please choose a clinic to continue</p>
                    </div>
                </div>

                <!-- Body -->
                <div class="p-6">
                    <?php if (session()->getFlashdata('error')): ?>
                        <div class="mb-4 p-3 bg-red-50 border border-red-200 rounded-xl flex items-start">
                            <i class="fas fa-exclamation-circle text-red-600 mt-0.5 mr-2"></i>
                            <div class="text-xs text-red-700"><?= session()->getFlashdata('error') ?></div>
                        </div>
                    <?php endif; ?>

                    <?= form_open('clinic/select', ['class' => 'space-y-3']) ?>
                        <?php foreach ($clinics as $clinic): ?>
                            <button type="submit" name="clinic_id" value="<?= esc($clinic['clinic_id']) ?>" class="w-full group relative flex items-center p-4 bg-white border border-gray-200 rounded-xl hover:border-blue-400 hover:shadow-lg hover:shadow-blue-500/10 transition-all duration-200 text-left">
                                <div class="w-10 h-10 bg-blue-50 rounded-lg flex items-center justify-center mr-4 group-hover:bg-blue-100 transition-colors">
                                    <i class="fas fa-tooth text-blue-500"></i>
                                </div>
                                <div>
                                    <h3 class="text-sm font-bold text-gray-900 group-hover:text-blue-700 transition-colors"><?= esc($clinic['clinic_name']) ?></h3>
                                    <p class="text-xs text-gray-500">Role ID: <?= esc($clinic['role_id']) ?></p>
                                </div>
                                <div class="ml-auto opacity-0 group-hover:opacity-100 transition-opacity">
                                    <i class="fas fa-arrow-right text-blue-500"></i>
                                </div>
                            </button>
                        <?php endforeach; ?>
                    <?= form_close() ?>
                </div>
                
                <!-- Footer -->
                <div class="p-4 border-t border-gray-100 bg-gray-50/50 text-center">
                    <a href="<?= base_url('auth/logout') ?>" class="text-sm font-semibold text-gray-500 hover:text-red-600 transition-colors">
                        <i class="fas fa-sign-out-alt mr-1"></i> Logout
                    </a>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
