<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?></title>
    <link rel="stylesheet" href="<?= base_url('assets/css/tailwind.css') ?>?v=<?= time() ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <meta name="description" content="Reset your dental management system password - Secure access to your practice.">
    <meta name="theme-color" content="#0284c7">
</head>
<body class="h-screen relative flex flex-col overflow-hidden text-slate-900 antialiased font-sans">
    
    <!-- Background -->
    <div class="fixed inset-0 z-0">
        <img src="<?= base_url('assets/images/login-background.jpg') ?>" class="w-full h-full object-cover" alt="background">
        <div class="absolute inset-0 bg-slate-900/50"></div>
        <div class="absolute inset-0 bg-gradient-to-br from-slate-900/70 via-transparent to-slate-900/90"></div>
        <div class="absolute inset-0 backdrop-blur-[2px]"></div>
    </div>

    <!-- Main Content Area -->
    <main class="relative z-10 flex-grow flex items-center justify-center px-4 py-4 md:py-8">
        <div class="w-full max-w-4xl max-h-full animate-card-entry">
            <div class="group relative">
                <div class="relative glass-card border border-white/20 rounded-[2.5rem] shadow-2xl shadow-black/60 transition-all duration-500 overflow-hidden flex flex-col md:flex-row min-h-[400px]">
                    
                    <div class="absolute inset-0 sandy-texture pointer-events-none z-0"></div>

                    <!-- Left Side -->
                    <div class="relative z-10 hidden md:flex md:w-5/12 bg-white/5 border-r border-white/10 p-10 flex-col justify-between overflow-hidden">
                        <div class="absolute -top-24 -left-24 w-48 h-48 bg-blue-500/20 blur-[80px]"></div>
                        <div class="relative z-10">
                            <div class="w-16 h-16 bg-white/10 backdrop-blur-md border border-white/20 rounded-2xl flex items-center justify-center shadow-xl mb-8">
                                <i class="fas fa-key text-white text-3xl"></i>
                            </div>
                            <h2 class="text-3xl font-black text-white leading-tight mb-4 drop-shadow-lg">Account Recovery</h2>
                            <p class="text-sm font-medium text-white/60 leading-relaxed">Don't worry, we'll help you get back into your account in no time.</p>
                        </div>
                        <div class="relative z-10 pt-10">
                            <p class="text-[9px] font-black text-white/40 uppercase tracking-[0.2em]">Clinic Admin: <?= esc($clinic_admin_email ?? 'Support') ?></p>
                        </div>
                    </div>

                    <!-- Right Side -->
                    <div class="relative z-10 w-full md:w-7/12 p-8 md:p-12 flex flex-col justify-center overflow-y-auto custom-scrollbar">
                        <div class="md:hidden flex flex-col items-center mb-8">
                             <div class="w-14 h-14 bg-white/10 backdrop-blur-md border border-white/20 rounded-2xl flex items-center justify-center shadow-xl mb-4">
                                <i class="fas fa-key text-white text-2xl"></i>
                            </div>
                            <h1 class="text-2xl font-black text-white tracking-tight">Recovery</h1>
                        </div>

                        <div class="mb-8">
                            <h3 class="text-2xl font-black text-white mb-2 drop-shadow-md">Lost access?</h3>
                            <p class="text-sm font-bold text-white/50 uppercase tracking-widest">Enter email for reset link</p>
                        </div>

                        <?php if (session()->getFlashdata('message')): ?>
                            <div class="mb-6 p-4 bg-blue-500/20 backdrop-blur-md border border-blue-500/30 rounded-2xl flex items-center animate-fadeIn relative z-20 shadow-lg">
                                <i class="fas fa-info-circle text-blue-400 mr-3"></i>
                                <p class="text-xs font-bold text-white"><?= session()->getFlashdata('message') ?></p>
                            </div>
                        <?php endif; ?>

                        <?= form_open('auth/forgot-password', ['class' => 'space-y-6 relative z-20', 'novalidate' => true]) ?>
                            <div class="space-y-2">
                                <label for="identity" class="text-[10px] font-black text-white/60 uppercase tracking-widest ml-2"><?= $identity_label ?></label>
                                <div class="relative group/input">
                                    <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                                        <i class="fas fa-envelope text-white/40 group-focus-within/input:text-blue-400 transition-colors"></i>
                                    </div>
                                    <?= form_input($identity + ['class' => 'w-full pl-11 pr-4 py-4 bg-white/10 border border-white/20 rounded-2xl text-white placeholder-white/40 focus:outline-none focus:ring-4 focus:ring-blue-500/20 focus:border-blue-400/50 focus:bg-white/20 transition-all duration-300 text-sm font-medium', 'placeholder' => 'Your email address']) ?>
                                </div>
                            </div>

                            <button type="submit" class="w-full py-4 backdrop-blur-md bg-blue-500/30 border border-blue-400/30 text-white font-black rounded-2xl shadow-xl hover:bg-blue-500/50 hover:scale-[1.01] active:scale-[0.99] transition-all duration-300 flex items-center justify-center space-x-3 group/btn">
                                <span class="text-sm uppercase tracking-widest drop-shadow-sm">Send Link</span>
                                <i class="fas fa-paper-plane text-[10px] transform group-hover/btn:translate-x-1 transition-transform"></i>
                            </button>
                        <?= form_close() ?>

                        <div class="mt-8 text-center">
                            <a href="<?= base_url('auth/login') ?>" class="text-[11px] font-bold text-white/60 hover:text-white transition-colors inline-flex items-center space-x-2">
                                <i class="fas fa-arrow-left text-[10px]"></i>
                                <span>Back to Login</span>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <!-- Footer -->
    <footer class="relative z-10 w-full p-4 md:p-6 pb-6 md:pb-8 flex-shrink-0 animate-footer-entry">
        <div class="max-w-5xl mx-auto">
            <div class="relative glass-card border border-white/20 rounded-[2rem] p-4 px-8 shadow-2xl shadow-black/20 overflow-hidden">
                <div class="absolute inset-0 sandy-texture pointer-events-none z-0"></div>
                <div class="relative z-10 flex flex-col md:flex-row items-center justify-between gap-4">
                    <div class="flex items-center space-x-5">
                        <div class="w-10 h-10 bg-white/10 rounded-2xl flex items-center justify-center border border-white/20">
                            <i class="fas fa-tooth text-white text-lg"></i>
                        </div>
                        <div class="flex flex-col">
                            <span class="text-sm font-black text-white tracking-widest uppercase">Palsync Dental</span>
                            <span class="text-[10px] font-bold text-white/40 uppercase tracking-tighter">&copy; <?= date('Y') ?></span>
                        </div>
                    </div>
                    <div class="flex items-center bg-black/20 rounded-2xl p-1.5 px-3 border border-white/10">
                        <span class="hidden lg:inline-block text-[10px] font-black text-white/40 uppercase tracking-widest mr-4 ml-1">Live Support</span>
                        <div class="flex items-center space-x-2">
                            <a href="https://wa.me/970599350450" target="_blank" class="w-9 h-9 flex items-center justify-center bg-emerald-500/20 hover:bg-emerald-500 text-emerald-400 hover:text-white rounded-xl transition-all duration-300"><i class="fab fa-whatsapp"></i></a>
                            <a href="https://wa.me/972569885000" target="_blank" class="w-9 h-9 flex items-center justify-center bg-emerald-500/20 hover:bg-emerald-500 text-emerald-400 hover:text-white rounded-xl transition-all duration-300"><i class="fab fa-whatsapp"></i></a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </footer>

    <style>
        :root { --glass-opacity: 0.25; --glass-blur: 40px; }
        .glass-card {
            position: relative; isolation: isolate;
            background-color: rgba(255, 255, 255, var(--glass-opacity)) !important;
            backdrop-filter: blur(var(--glass-blur)) saturate(120%) !important;
            -webkit-backdrop-filter: blur(var(--glass-blur)) saturate(120%) !important;
        }
        .glass-card::before {
            content: ""; position: absolute; inset: -20%; z-index: -1; pointer-events: none;
            mix-blend-mode: overlay; filter: blur(12px); transform: scale(1.1);
            background-image: 
                radial-gradient(circle at 20% 30%, rgba(255, 255, 255, 0.2) 0%, transparent 12%),
                radial-gradient(circle at 70% 15%, rgba(255, 255, 255, 0.15) 0%, transparent 20%),
                radial-gradient(circle at 40% 80%, rgba(255, 255, 255, 0.1) 0%, transparent 15%);
        }
        .glass-card::after {
            content: ""; position: absolute; inset: 0; z-index: -1; pointer-events: none;
            opacity: 0.04; mix-blend-mode: soft-light;
            background-image: repeating-radial-gradient(#fff 0 0.0001%, transparent 0 0.0004%);
            background-size: 8px 8px;
        }
        .custom-scrollbar::-webkit-scrollbar { width: 4px; }
        .custom-scrollbar::-webkit-scrollbar-thumb { background: rgba(255, 255, 255, 0.1); border-radius: 10px; }
        @keyframes slideUpFade { from { opacity: 0; transform: translateY(30px); } to { opacity: 1; transform: translateY(0); } }
        .animate-card-entry { animation: slideUpFade 0.8s cubic-bezier(0.16, 1, 0.3, 1) forwards; }
        .animate-footer-entry { animation: slideUpFade 0.8s cubic-bezier(0.16, 1, 0.3, 1) 0.2s forwards; opacity: 0; }
        .animate-fadeIn { animation: fadeIn 0.4s ease-out forwards; }
        @keyframes fadeIn { from { opacity: 0; transform: translateY(-10px); } to { opacity: 1; transform: translateY(0); } }
    </style>
</body>
</html>
