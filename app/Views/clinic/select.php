<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= esc($title) ?> | DentaCare</title>
    <link rel="stylesheet" href="<?= base_url('assets/css/tailwind.css') ?>?v=<?= time() ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <meta name="theme-color" content="#4f46e5">
</head>
<body class="min-h-screen bg-gradient-to-br from-slate-50 via-blue-50 to-indigo-100 relative overflow-hidden flex items-center justify-center p-6">
    <!-- Animated background elements -->
    <div class="absolute inset-0 overflow-hidden pointer-events-none">
        <div class="absolute -top-40 -right-40 w-96 h-96 bg-gradient-to-br from-indigo-500/10 to-purple-600/10 rounded-full blur-3xl animate-pulse"></div>
        <div class="absolute -bottom-40 -left-40 w-96 h-96 bg-gradient-to-br from-emerald-400/10 to-cyan-600/10 rounded-full blur-3xl animate-pulse delay-1000"></div>
    </div>

    <!-- Main Container -->
    <div class="relative z-10 w-full max-w-lg">
        <!-- Card -->
        <div class="backdrop-blur-2xl bg-white/80 border border-white/40 rounded-[2.5rem] shadow-[0_32px_64px_-12px_rgba(0,0,0,0.1)] overflow-hidden transition-all duration-500">
            <!-- Header -->
            <div class="p-10 text-center border-b border-white/30 bg-white/30">
                <div class="w-20 h-20 bg-gradient-to-br from-indigo-600 to-purple-700 rounded-3xl flex items-center justify-center text-white shadow-2xl shadow-indigo-500/40 mx-auto mb-6 transform -rotate-2">
                    <i class="fas fa-hospital-user text-3xl"></i>
                </div>
                <h1 class="text-3xl font-black text-slate-900 mb-2 tracking-tight">Select Clinic</h1>
                <p class="text-sm text-slate-500 font-bold uppercase tracking-widest">Entry Authorization Required</p>
            </div>

            <!-- Body -->
            <div class="p-10">
                <?php if (session()->getFlashdata('error')): ?>
                    <div class="mb-6 p-4 bg-rose-50 border border-rose-200 rounded-2xl flex items-center text-rose-800 shadow-sm animate-shake">
                        <i class="fas fa-exclamation-triangle mr-3"></i>
                        <div class="text-xs font-bold uppercase tracking-wider"><?= session()->getFlashdata('error') ?></div>
                    </div>
                <?php endif; ?>

                <?= form_open('clinic/select', ['class' => 'space-y-4']) ?>
                    <?php foreach ($clinics as $clinic): ?>
                        <button type="submit" name="clinic_id" value="<?= esc($clinic['clinic_id']) ?>" class="w-full group relative flex items-center p-6 bg-white border-2 border-slate-100 rounded-[1.5rem] hover:border-indigo-500 hover:shadow-xl hover:shadow-indigo-500/10 transition-all duration-300 text-left">
                            <div class="w-14 h-14 bg-slate-50 rounded-2xl flex items-center justify-center mr-5 group-hover:bg-indigo-50 transition-colors shadow-inner">
                                <i class="fas fa-tooth text-slate-400 group-hover:text-indigo-600 text-xl"></i>
                            </div>
                            <div class="flex-1 min-w-0">
                                <h3 class="text-lg font-black text-slate-800 group-hover:text-indigo-700 transition-colors truncate"><?= esc($clinic['clinic_name']) ?></h3>
                                <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Workspace ID: #<?= esc($clinic['clinic_id']) ?></p>
                            </div>
                            <div class="ml-4 w-10 h-10 rounded-full bg-slate-50 flex items-center justify-center text-slate-300 opacity-0 group-hover:opacity-100 group-hover:bg-indigo-600 group-hover:text-white transition-all duration-300 shadow-sm">
                                <i class="fas fa-chevron-right text-xs"></i>
                            </div>
                        </button>
                    <?php endforeach; ?>
                <?= form_close() ?>
            </div>
            
            <!-- Footer -->
            <div class="px-10 py-6 border-t border-white/30 bg-white/20 text-center">
                <a href="<?= base_url('auth/logout') ?>" class="inline-flex items-center space-x-2 text-xs font-black text-slate-400 uppercase tracking-widest hover:text-rose-600 transition-colors">
                    <i class="fas fa-sign-out-alt"></i>
                    <span>Secure Sign Out</span>
                </a>
            </div>
        </div>
    </div>
</body>
</html>