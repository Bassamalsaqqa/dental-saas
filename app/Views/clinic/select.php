<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= esc($title) ?></title>
    <link rel="icon" type="image/png" href="<?= base_url('assets/images/favicon.png') ?>">
    <link rel="stylesheet" href="<?= base_url('assets/css/tailwind.css') ?>?v=<?= time() ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
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
                                <i class="fas fa-hospital-user text-white text-3xl"></i>
                            </div>
                            <h2 class="text-3xl font-black text-white leading-tight mb-4 drop-shadow-lg">Clinic Gateway</h2>
                            <p class="text-sm font-medium text-white/60 leading-relaxed">Select which clinic you'd like to manage today. Switch anytime from your dashboard.</p>
                        </div>
                        <div class="relative z-10 pt-10">
                            <span class="text-[10px] font-black text-white/40 uppercase tracking-[0.2em]">Multi-Clinic Access Enabled</span>
                        </div>
                    </div>

                    <!-- Right Side -->
                    <div class="relative z-10 w-full md:w-7/12 p-8 md:p-12 flex flex-col justify-center overflow-y-auto custom-scrollbar">
                        <div class="md:hidden flex flex-col items-center mb-8">
                             <div class="w-14 h-14 bg-white/10 backdrop-blur-md border border-white/20 rounded-2xl flex items-center justify-center shadow-xl mb-4">
                                <i class="fas fa-hospital-user text-white text-2xl"></i>
                            </div>
                            <h1 class="text-2xl font-black text-white tracking-tight">Select Clinic</h1>
                        </div>

                        <div class="mb-8">
                            <h3 class="text-2xl font-black text-white mb-2 drop-shadow-md">Your Clinics</h3>
                            <p class="text-sm font-bold text-white/50 uppercase tracking-widest">Choose to continue</p>
                        </div>

                        <?php if (session()->getFlashdata('error')): ?>
                            <div class="mb-6 p-4 bg-rose-500/20 backdrop-blur-md border border-rose-500/30 rounded-2xl flex items-center animate-fadeIn relative z-20 shadow-lg">
                                <i class="fas fa-exclamation-circle text-white text-lg mr-3"></i>
                                <p class="text-xs font-bold text-white"><?= session()->getFlashdata('error') ?></p>
                            </div>
                        <?php endif; ?>

                        <div class="space-y-4 max-h-[300px] overflow-y-auto pr-2 custom-scrollbar relative z-20">
                            <?= form_open('clinic/select', ['class' => 'space-y-4']) ?>
                                <?php foreach ($clinics as $clinic): ?>
                                    <button type="submit" name="clinic_id" value="<?= esc($clinic['clinic_id']) ?>" class="w-full group/item relative flex items-center p-5 bg-white/10 border border-white/20 rounded-3xl hover:border-blue-400 hover:bg-white/20 transition-all duration-300 text-left">
                                        <div class="w-12 h-12 bg-white/10 border border-white/20 rounded-2xl flex items-center justify-center mr-4 group-hover/item:scale-110 transition-transform duration-300 shadow-sm">
                                            <i class="fas fa-tooth text-white drop-shadow-md text-xl"></i>
                                        </div>
                                        <div class="flex-1 min-w-0">
                                            <h3 class="text-sm font-black text-white group-hover/item:text-blue-400 transition-colors truncate drop-shadow-sm"><?= esc($clinic['clinic_name']) ?></h3>
                                            <div class="flex items-center space-x-2 mt-1">
                                                <span class="inline-block w-1.5 h-1.5 bg-emerald-400 rounded-full animate-pulse"></span>
                                                <p class="text-[9px] text-white/40 font-black uppercase tracking-widest">Active System</p>
                                            </div>
                                        </div>
                                        <div class="ml-auto opacity-40 group-hover/item:opacity-100 transform translate-x-2 group-hover/item:translate-x-0 transition-all duration-300">
                                            <i class="fas fa-chevron-right text-white text-[10px]"></i>
                                        </div>
                                    </button>
                                <?php endforeach; ?>
                            <?= form_close() ?>
                        </div>

                        <div class="mt-8 text-center pt-6 border-t border-white/10 relative z-20">
                            <a href="<?= base_url('auth/logout') ?>" class="text-[10px] font-black text-white/40 hover:text-rose-400 uppercase tracking-[0.2em] transition-colors flex items-center justify-center space-x-2">
                                <i class="fas fa-sign-out-alt"></i>
                                <span>Logout Account</span>
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
                            <a href="https://wa.me/970599350450" target="_blank" class="w-9 h-9 flex items-center justify-center bg-emerald-500/20 hover:bg-emerald-500 text-emerald-400 hover:text-white rounded-xl transition-all duration-300"><i class="fab fa-whatsapp text-lg"></i></a>
                            <a href="https://wa.me/972569885000" target="_blank" class="w-9 h-9 flex items-center justify-center bg-emerald-500/20 hover:bg-emerald-500 text-emerald-400 hover:text-white rounded-xl transition-all duration-300"><i class="fab fa-whatsapp text-lg"></i></a>
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
                radial-gradient(circle at 20% 30%, rgba(255, 255, 255, 0.25) 0%, transparent 15%),
                radial-gradient(circle at 70% 15%, rgba(255, 255, 255, 0.2) 0%, transparent 25%),
                radial-gradient(circle at 40% 80%, rgba(255, 255, 255, 0.15) 0%, transparent 20%);
        }
        .glass-card::after {
            content: ""; position: absolute; inset: 0; z-index: -1; pointer-events: none;
            opacity: 0.05; mix-blend-mode: soft-light;
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
