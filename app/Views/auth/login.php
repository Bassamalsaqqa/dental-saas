<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?></title>
    <link rel="stylesheet" href="<?= base_url('assets/css/tailwind.css') ?>?v=<?= time() ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <meta name="description" content="Professional dental management system login - Secure access to your dental practice management system.">
    <meta name="theme-color" content="#0284c7">
</head>
<body class="min-h-screen bg-gradient-to-br from-gray-50 to-blue-50/30 relative flex flex-col overflow-hidden">
    <!-- Subtle Background Elements -->
    <div class="absolute inset-0 overflow-hidden pointer-events-none">
        <div class="absolute -top-20 -right-20 w-40 h-40 bg-gradient-to-br from-blue-200/30 to-purple-200/30 rounded-full blur-2xl animate-pulse"></div>
        <div class="absolute -bottom-20 -left-20 w-40 h-40 bg-gradient-to-br from-emerald-200/30 to-cyan-200/30 rounded-full blur-2xl animate-pulse delay-1000"></div>
    </div>

    <!-- Main Content Area -->
    <div class="relative z-10 flex-grow flex items-center justify-center px-4 py-8">
        <div class="w-full max-w-sm">
            <!-- Enhanced Login Card with Glassmorphism -->
            <div class="group relative">
                <!-- Subtle Background Glow -->
                <div class="absolute inset-0 bg-gradient-to-br from-blue-400/10 to-purple-500/10 rounded-2xl blur-xl group-hover:blur-2xl transition-all duration-500 opacity-0 group-hover:opacity-100"></div>
                
                <!-- Main Card -->
                <div class="relative backdrop-blur-xl bg-white/90 border border-white/40 rounded-2xl shadow-xl shadow-blue-500/5 group-hover:shadow-blue-500/10 transition-all duration-500 overflow-hidden">
                    <!-- Enhanced Header -->
                    <div class="relative p-6 text-center border-b border-white/30">
                        <!-- Subtle Background -->
                        <div class="absolute inset-0 bg-gradient-to-br from-blue-100/20 to-purple-100/20 rounded-t-2xl"></div>
                        
                        <!-- Logo and Branding -->
                        <div class="relative z-10">
                            <div class="relative group/logo mb-4">
                                <div class="relative w-16 h-16 bg-gradient-to-br from-blue-500 to-purple-600 rounded-xl flex items-center justify-center shadow-lg group-hover/logo:scale-105 group-hover/logo:rotate-2 transition-all duration-300 mx-auto">
                                    <i class="fas fa-tooth text-white text-2xl"></i>
                                </div>
                            </div>
                            
                            <h1 class="text-2xl font-black text-gray-900 mb-2"><?= esc($clinic['name']) ?></h1>
                            <div class="flex items-center justify-center space-x-2 mb-3">
                                <div class="w-1.5 h-1.5 bg-gradient-to-r from-blue-500 to-purple-600 rounded-full animate-pulse"></div>
                                <p class="text-xs text-gray-600 font-semibold"><?= esc($clinic['tagline']) ?></p>
                            </div>
                            <p class="text-sm text-gray-600 font-medium">Sign in to your account</p>
                        </div>
                    </div>

                    <!-- Enhanced Form Body -->
                    <div class="p-6">
                        <!-- Flash Messages -->
                        <?php if (session()->getFlashdata('success')): ?>
                            <div class="mb-4 p-3 bg-green-50 border border-green-200 rounded-xl flex items-start">
                                <div class="relative">
                                    <div class="absolute inset-0 bg-gradient-to-br from-green-400 to-emerald-500 rounded-lg blur opacity-50"></div>
                                    <div class="relative w-6 h-6 bg-gradient-to-br from-green-500 to-emerald-600 rounded-lg flex items-center justify-center">
                                        <i class="fas fa-check-circle text-white text-xs"></i>
                                    </div>
                                </div>
                                <div class="ml-3">
                                    <h3 class="text-xs font-bold text-green-800">Success</h3>
                                    <p class="text-xs text-green-700 mt-1"><?= session()->getFlashdata('success') ?></p>
                                </div>
                            </div>
                        <?php endif; ?>

                        <?php if (session()->getFlashdata('message')): ?>
                            <div class="mb-4 p-3 bg-red-50 border border-red-200 rounded-xl flex items-start">
                                <div class="relative">
                                    <div class="absolute inset-0 bg-gradient-to-br from-red-400 to-pink-500 rounded-lg blur opacity-50"></div>
                                    <div class="relative w-6 h-6 bg-gradient-to-br from-red-500 to-pink-600 rounded-lg flex items-center justify-center">
                                        <i class="fas fa-exclamation-circle text-white text-xs"></i>
                                    </div>
                                </div>
                                <div class="ml-3">
                                    <h3 class="text-xs font-bold text-red-800">Authentication Error</h3>
                                    <p class="text-xs text-red-700 mt-1"><?= session()->getFlashdata('message') ?></p>
                                </div>
                            </div>
                        <?php endif; ?>

                        <!-- Login Form -->
                        <?= form_open('auth/login', ['class' => 'space-y-4', 'novalidate' => true]) ?>
                            <div class="space-y-1">
                                <label for="identity" class="flex items-center text-xs font-bold text-gray-700">
                                    <i class="fas fa-user w-3 h-3 mr-2 text-blue-600"></i>
                                    Email or Username
                                </label>
                                <div class="relative group">
                                    <?= form_input($identity + ['class' => 'w-full px-3 py-3 bg-white border border-gray-200 rounded-xl text-gray-900 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-300 transition-all duration-200 shadow-sm text-sm', 'placeholder' => 'Enter your email or username']) ?>
                                </div>
                            </div>

                            <div class="space-y-1">
                                <label for="password" class="flex items-center text-xs font-bold text-gray-700">
                                    <i class="fas fa-lock w-3 h-3 mr-2 text-blue-600"></i>
                                    Password
                                </label>
                                <div class="relative group">
                                    <?= form_input($password + ['class' => 'w-full px-3 py-3 bg-white border border-gray-200 rounded-xl text-gray-900 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-300 transition-all duration-200 shadow-sm text-sm', 'placeholder' => 'Enter your password']) ?>
                                </div>
                            </div>

                            <div class="flex items-center justify-between">
                                <label class="flex items-center space-x-3 cursor-pointer group">
                                    <div class="relative">
                                        <input type="checkbox" id="remember" name="remember" value="1" class="sr-only">
                                        <div class="w-5 h-5 bg-white border-2 border-gray-300 rounded-lg group-hover:border-blue-400 transition-colors duration-200 flex items-center justify-center">
                                            <i class="fas fa-check text-white text-xs opacity-0 transition-opacity duration-200" id="checkIcon"></i>
                                        </div>
                                    </div>
                                    <span class="text-xs font-semibold text-gray-700 group-hover:text-gray-900 transition-colors duration-200">Remember me</span>
                                </label>
                                <a href="<?= base_url('auth/forgot-password') ?>" class="text-xs font-semibold text-blue-600 hover:text-blue-700 hover:underline transition-colors duration-200">
                                    Forgot password?
                                </a>
                            </div>

                            <div class="pt-1">
                                <button type="submit" class="group relative w-full py-3 px-4 bg-gradient-to-r from-blue-500 to-purple-600 text-white font-bold rounded-xl shadow-lg hover:scale-[1.02] transition-all duration-300">
                                    <div class="relative flex items-center justify-center space-x-2 text-sm">
                                        <i class="fas fa-sign-in-alt"></i>
                                        <span>Sign In to Dashboard</span>
                                    </div>
                                </button>
                            </div>
                        <?= form_close() ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Enhanced Global Footer -->
    <footer class="relative z-10 w-full backdrop-blur-md bg-white/40 border-t border-white/40">
        <div class="max-w-7xl mx-auto px-4 py-8">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
                <!-- Branding & Legal -->
                <div class="col-span-1 md:col-span-1 space-y-4">
                    <div class="flex items-center space-x-3">
                        <div class="w-8 h-8 bg-gradient-to-br from-blue-500 to-purple-600 rounded-lg flex items-center justify-center text-white shadow-md">
                            <i class="fas fa-tooth text-sm"></i>
                        </div>
                        <span class="text-lg font-black text-gray-800 tracking-tight">Palsync Dental</span>
                    </div>
                    <p class="text-xs font-medium text-gray-500 leading-relaxed">
                        Precision-engineered SaaS for modern dental clinic management. Powered by Palsync technologies.
                    </p>
                    <p class="text-[10px] text-gray-400">
                        &copy; <?= date('Y') ?> <?= esc($clinic['name']) ?>. All rights reserved.
                    </p>
                </div>

                <!-- Contact Links -->
                <div class="col-span-1 space-y-4">
                    <h4 class="text-xs font-bold text-gray-700 uppercase tracking-widest border-l-2 border-blue-500 pl-2">Connectivity</h4>
                    <ul class="space-y-3">
                        <li>
                            <a href="https://palsync.palsync.net" target="_blank" class="inline-flex items-center group">
                                <span class="w-6 h-6 rounded-md bg-blue-100 flex items-center justify-center mr-3 group-hover:bg-blue-600 transition-all duration-300">
                                    <i class="fas fa-globe text-[10px] text-blue-600 group-hover:text-white"></i>
                                </span>
                                <span class="text-xs font-bold text-gray-600 group-hover:text-blue-600 transition-colors tracking-tight">palsync.palsync.net</span>
                            </a>
                        </li>
                        <li>
                            <a href="mailto:Bassamalsaqqa@hotmail.com" class="inline-flex items-center group">
                                <span class="w-6 h-6 rounded-md bg-purple-100 flex items-center justify-center mr-3 group-hover:bg-purple-600 transition-all duration-300">
                                    <i class="fas fa-envelope text-[10px] text-purple-600 group-hover:text-white"></i>
                                </span>
                                <span class="text-xs font-bold text-gray-600 group-hover:text-purple-600 transition-colors tracking-tight">Bassamalsaqqa@hotmail.com</span>
                            </a>
                        </li>
                    </ul>
                </div>

                <!-- Support Channels -->
                <div class="col-span-1 md:col-span-2 space-y-4">
                    <h4 class="text-xs font-bold text-gray-700 uppercase tracking-widest border-l-2 border-green-500 pl-2">24/7 WhatsApp Support</h4>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <a href="https://wa.me/970599350450" target="_blank" class="flex items-center p-3 rounded-xl bg-white/40 border border-white/60 hover:bg-green-50 hover:border-green-200 transition-all duration-300 group">
                            <div class="w-8 h-8 bg-green-100 rounded-lg flex items-center justify-center group-hover:scale-110 transition-transform">
                                <i class="fab fa-whatsapp text-green-600"></i>
                            </div>
                            <div class="ml-3">
                                <p class="text-[9px] font-black text-gray-400 uppercase leading-none mb-1 tracking-tighter">Support Line (PS)</p>
                                <p class="text-xs font-bold text-gray-700 group-hover:text-green-700 transition-colors">+970 599 350 450</p>
                            </div>
                        </a>
                        <a href="https://wa.me/972569885000" target="_blank" class="flex items-center p-3 rounded-xl bg-white/40 border border-white/60 hover:bg-green-50 hover:border-green-200 transition-all duration-300 group">
                            <div class="w-8 h-8 bg-green-100 rounded-lg flex items-center justify-center group-hover:scale-110 transition-transform">
                                <i class="fab fa-whatsapp text-green-600"></i>
                            </div>
                            <div class="ml-3">
                                <p class="text-[9px] font-black text-gray-400 uppercase leading-none mb-1 tracking-tighter">Support Line (IL)</p>
                                <p class="text-xs font-bold text-gray-700 group-hover:text-green-700 transition-colors">+972 569 885 000</p>
                            </div>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </footer>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const rememberCheckbox = document.getElementById('remember');
            const checkIcon = document.getElementById('checkIcon');
            if (rememberCheckbox && checkIcon) {
                rememberCheckbox.addEventListener('change', function() {
                    if (this.checked) {
                        checkIcon.style.opacity = '1';
                        checkIcon.parentElement.style.backgroundColor = '#3b82f6';
                        checkIcon.parentElement.style.borderColor = '#3b82f6';
                    } else {
                        checkIcon.style.opacity = '0';
                        checkIcon.parentElement.style.backgroundColor = 'white';
                        checkIcon.parentElement.style.borderColor = '#d1d5db';
                    }
                });
            }
        });
    </script>
</body>
</html>