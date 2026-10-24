<!DOCTYPE html>
<html lang="<?= esc($locale ?? 'en') ?>" dir="<?= !empty($is_rtl) ? 'rtl' : 'ltr' ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= esc($title) ?></title>
    <link rel="icon" type="image/png" href="<?= base_url('assets/images/favicon.png') ?>">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=Noto+Sans+Arabic:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= base_url('assets/css/tailwind.css') ?>?v=<?= time() ?>">
    <link rel="stylesheet" href="<?= base_url('assets/vendor/fontawesome/css/all.min.css') ?>?v=<?= time() ?>">
    <meta name="theme-color" content="#0284c7">
    <style>
        :root {
            --app-font-sans: "Inter", ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Roboto", "Noto Sans", sans-serif;
            --app-font-ar: "Noto Sans Arabic", "Inter", ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Roboto", "Noto Sans", sans-serif;
        }

        body,
        button,
        input,
        select,
        textarea {
            font-family: var(--app-font-sans);
        }

        html[lang^="ar"] body,
        html[lang^="ar"] button,
        html[lang^="ar"] input,
        html[lang^="ar"] select,
        html[lang^="ar"] textarea {
            font-family: var(--app-font-ar);
        }
    </style>
<link rel="stylesheet" media="screen" href="<?= base_url('assets/css/warm-clinical.css') ?>?v=<?= @filemtime(FCPATH . 'assets/css/warm-clinical.css') ?: '1' ?>">
<link rel="stylesheet" media="screen" href="<?= base_url('assets/css/v3-components.css') ?>?v=<?= @filemtime(FCPATH . 'assets/css/v3-components.css') ?: '1' ?>">
<link rel="stylesheet" media="screen" href="<?= base_url('assets/css/v3-icons.css') ?>?v=<?= @filemtime(FCPATH . 'assets/css/v3-icons.css') ?: '1' ?>">
</head>
<body class="denta-v3 h-screen relative flex flex-col overflow-hidden v3-text-ink antialiased font-sans">

    <!-- Background -->
    <div class="fixed inset-0 z-0">
        <img src="<?= base_url('assets/images/login-background.jpg') ?>" class="w-full h-full object-cover" alt="background">
        <div class="absolute inset-0 v3-backdrop"></div>
        <div class="absolute inset-0 v3-bg-panel v3-bg-panel via-transparent v3-bg-panel"></div>
        <div class="absolute inset-0 backdrop-blur-[2px]"></div>
    </div>

    <!-- Main Content Area -->
    <main class="relative z-10 flex-grow flex items-center justify-center px-4 py-4 md:py-8">
        <div class="w-full max-w-4xl max-h-full animate-card-entry">
            <div class="group relative">
                <div class="relative glass-card border v3-border-default rounded-[2.5rem] v3-elevation-none shadow-black/60 transition-all duration-500 overflow-hidden flex flex-col md:flex-row min-h-[400px]">

                    <div class="absolute inset-0 sandy-texture pointer-events-none z-0"></div>

                    <!-- Left Side -->
                    <div class="relative z-10 hidden md:flex md:w-5/12 v3-bg-panel border-r v3-border-default p-10 flex-col justify-between overflow-hidden">
                        <div class="absolute -top-24 -left-24 w-48 h-48 v3-bg-primary blur-[80px]"></div>
                        <div class="relative z-10">
                            <div class="w-16 h-16 v3-bg-panel backdrop-blur-md border v3-border-default v3-radius flex items-center justify-center v3-elevation-none mb-8">
                                <i class="fas fa-hospital-user v3-text-on-primary text-3xl"></i>
                            </div>
                            <h2 class="text-3xl font-black v3-text-on-primary leading-tight mb-4 drop-shadow-lg">Clinic Gateway</h2>
                            <p class="text-sm font-medium v3-text-on-primary leading-relaxed">Select which clinic you'd like to manage today. Switch anytime from your dashboard.</p>
                        </div>
                        <div class="relative z-10 pt-10">
                            <span class="text-[10px] font-black v3-text-on-primary uppercase tracking-[0.2em]">Multi-Clinic Access Enabled</span>
                        </div>
                    </div>

                    <!-- Right Side -->
                    <div class="relative z-10 w-full md:w-7/12 p-8 md:p-12 flex flex-col justify-center overflow-y-auto custom-scrollbar">
                        <div class="md:hidden flex flex-col items-center mb-8">
                             <div class="w-14 h-14 v3-bg-panel backdrop-blur-md border v3-border-default v3-radius flex items-center justify-center v3-elevation-none mb-4">
                                <i class="fas fa-hospital-user v3-text-on-primary text-2xl"></i>
                            </div>
                            <h1 class="text-2xl font-black v3-text-on-primary tracking-tight">Select Clinic</h1>
                        </div>

                        <div class="mb-8">
                            <h3 class="text-2xl font-black v3-text-on-primary mb-2 drop-shadow-md">Your Clinics</h3>
                            <p class="text-sm font-bold v3-text-on-primary uppercase tracking-widest">Choose to continue</p>
                        </div>

                        <?php if (session()->getFlashdata('error')): ?>
                            <div class="mb-6 p-4 v3-bg-danger backdrop-blur-md border v3-border-default v3-radius flex items-center animate-fadeIn relative z-20 v3-elevation-none">
                                <i class="fas fa-exclamation-circle v3-text-on-primary text-lg mr-3"></i>
                                <p class="text-xs font-bold v3-text-on-primary"><?= session()->getFlashdata('error') ?></p>
                            </div>
                        <?php endif; ?>

                        <div class="space-y-4 max-h-[300px] overflow-y-auto pr-2 custom-scrollbar relative z-20">
                            <?= form_open('clinic/select', ['class' => 'space-y-4']) ?>
                                <?php foreach ($clinics as $clinic): ?>
                                    <button type="submit" name="clinic_id" value="<?= esc($clinic['clinic_id']) ?>" class="w-full group/item relative flex items-center p-5 v3-bg-panel border v3-border-default v3-radius v3-border-default v3-hover-soft transition-all duration-300 text-left v3-btn v3-btn--primary">
                                        <div class="w-12 h-12 v3-bg-panel border v3-border-default v3-radius flex items-center justify-center mr-4 group-hover/item:scale-110 transition-transform duration-300 v3-elevation-none">
                                            <i class="fas fa-tooth v3-text-on-primary drop-shadow-md text-xl"></i>
                                        </div>
                                        <div class="flex-1 min-w-0">
                                            <h3 class="text-sm font-black v3-text-on-primary group-hover/item:v3-text-primary transition-colors truncate drop-shadow-sm"><?= esc($clinic['clinic_name']) ?></h3>
                                            <div class="flex items-center space-x-2 mt-1">
                                                <span class="inline-block w-1.5 h-1.5 v3-bg-primary rounded-full animate-pulse"></span>
                                                <p class="text-[9px] v3-text-on-primary font-black uppercase tracking-widest">Active System</p>
                                            </div>
                                        </div>
                                        <div class="ml-auto opacity-40 group-hover/item:opacity-100 transform translate-x-2 group-hover/item:translate-x-0 transition-all duration-300">
                                            <i class="fas fa-chevron-right v3-text-on-primary text-[10px]"></i>
                                        </div>
                                    </button>
                                <?php endforeach; ?>
                            <?= form_close() ?>
                        </div>

                        <div class="mt-8 text-center pt-6 border-t v3-border-default relative z-20">
                            <a href="<?= base_url('auth/logout') ?>" class="text-[10px] font-black v3-text-on-primary v3-hover-text-danger uppercase tracking-[0.2em] transition-colors flex items-center justify-center space-x-2">
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
            <div class="relative glass-card border v3-border-default rounded-[2rem] p-4 px-8 v3-elevation-none shadow-black/20 overflow-hidden">
                <div class="absolute inset-0 sandy-texture pointer-events-none z-0"></div>
                <div class="relative z-10 flex flex-col md:flex-row items-center justify-between gap-4">
                    <div class="flex items-center space-x-5">
                        <div class="w-10 h-10 v3-bg-panel v3-radius flex items-center justify-center border v3-border-default">
                            <i class="fas fa-tooth v3-text-on-primary text-lg"></i>
                        </div>
                        <div class="flex flex-col">
                            <span class="text-sm font-black v3-text-on-primary tracking-widest uppercase">Palsync Dental</span>
                            <span class="text-[10px] font-bold v3-text-on-primary uppercase tracking-tighter">&copy; <?= date('Y') ?></span>
                        </div>
                    </div>
                    <div class="flex items-center v3-backdrop v3-radius p-1.5 px-3 border v3-border-default">
                        <span class="hidden lg:inline-block text-[10px] font-black v3-text-on-primary uppercase tracking-widest mr-4 ml-1">Live Support</span>
                        <div class="flex items-center space-x-2">
                            <a href="https://wa.me/970599350450" target="_blank" class="w-9 h-9 flex items-center justify-center v3-bg-primary v3-hover-primary v3-text-primary v3-hover-text-on-primary v3-radius transition-all duration-300"><i class="fab fa-whatsapp text-lg"></i></a>
                            <a href="https://wa.me/972569885000" target="_blank" class="w-9 h-9 flex items-center justify-center v3-bg-primary v3-hover-primary v3-text-primary v3-hover-text-on-primary v3-radius transition-all duration-300"><i class="fab fa-whatsapp text-lg"></i></a>
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
            backdrop-filter:none;
            backdrop-filter:none;
        }
        .glass-card::before {
            content: ""; position: absolute; inset: -20%; z-index: -1; pointer-events: none;
            mix-blend-mode: overlay; filter: blur(12px); transform: scale(1.1);
            background:var(--denta-alabaster);
        }
        .glass-card::after {
            content: ""; position: absolute; inset: 0; z-index: -1; pointer-events: none;
            opacity: 0.05; mix-blend-mode: soft-light;
            background:var(--denta-alabaster);
            background-size: 8px 8px;
        }
        .custom-scrollbar::-webkit-scrollbar { width: 4px; }
        .custom-scrollbar::-webkit-scrollbar-thumb { background: rgba(255, 255, 255, 0.1); border-radius: 10px; }
        @keyframes slideUpFade { from { opacity: 0; transform: translateY(30px); } to { opacity: 1; transform: translateY(0); } }
        .animate-card-entry { animation: slideUpFade 0.8s cubic-bezier(0.16, 1, 0.3, 1) forwards; }
        .animate-footer-entry { animation: slideUpFade 0.8s cubic-bezier(0.16, 1, 0.3, 1) 0.2s forwards; opacity: 0; }
        .animate-fadeIn { animation: fadeIn 0.4s ease-out forwards; }
        @keyframes fadeIn { from { opacity: 0; transform: translateY(-10px); } to { opacity: 1; transform: translateY(0); } }
        [dir="rtl"] .space-x-1 > :not([hidden]) ~ :not([hidden]),
        [dir="rtl"] .space-x-2 > :not([hidden]) ~ :not([hidden]),
        [dir="rtl"] .space-x-3 > :not([hidden]) ~ :not([hidden]),
        [dir="rtl"] .space-x-4 > :not([hidden]) ~ :not([hidden]),
        [dir="rtl"] .space-x-5 > :not([hidden]) ~ :not([hidden]),
        [dir="rtl"] .space-x-6 > :not([hidden]) ~ :not([hidden]) {
            --tw-space-x-reverse: 1;
        }
        [dir="rtl"] i.mr-1 { margin-right: 0 !important; margin-left: 0.25rem !important; }
        [dir="rtl"] i.mr-2 { margin-right: 0 !important; margin-left: 0.5rem !important; }
        [dir="rtl"] i.mr-3 { margin-right: 0 !important; margin-left: 0.75rem !important; }
        [dir="rtl"] i.mr-4 { margin-right: 0 !important; margin-left: 1rem !important; }
        [dir="rtl"] .text-left { text-align: right !important; }
    </style>
</body>
</html>
