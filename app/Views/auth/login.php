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
<body class="min-h-screen bg-gradient-to-br from-gray-50 to-blue-50/30 relative overflow-hidden">
    <!-- Subtle Background Elements -->
    <div class="absolute inset-0 overflow-hidden pointer-events-none">
        <div class="absolute -top-20 -right-20 w-40 h-40 bg-gradient-to-br from-blue-200/30 to-purple-200/30 rounded-full blur-2xl animate-pulse"></div>
        <div class="absolute -bottom-20 -left-20 w-40 h-40 bg-gradient-to-br from-emerald-200/30 to-cyan-200/30 rounded-full blur-2xl animate-pulse delay-1000"></div>
    </div>

    <!-- Main Login Container -->
    <div class="relative z-10 min-h-screen flex items-center justify-center px-4 py-8">
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
                            <!-- Email/Username Field -->
                            <div class="space-y-1">
                                <label for="identity" class="flex items-center text-xs font-bold text-gray-700">
                                    <i class="fas fa-user w-3 h-3 mr-2 text-blue-600"></i>
                                    Email or Username
                                </label>
                                <div class="relative group">
                                    <div class="absolute inset-0 bg-gradient-to-r from-blue-400/5 to-purple-400/5 rounded-xl blur opacity-0 group-hover:opacity-100 transition-opacity duration-200"></div>
                                    <div class="relative">
                                        <?= form_input($identity + ['class' => 'w-full px-3 py-3 bg-white border border-gray-200 rounded-xl text-gray-900 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-300 transition-all duration-200 shadow-sm hover:shadow-md text-sm', 'placeholder' => 'Enter your email or username']) ?>
                                    </div>
                                </div>
                            </div>

                            <!-- Password Field -->
                            <div class="space-y-1">
                                <label for="password" class="flex items-center text-xs font-bold text-gray-700">
                                    <i class="fas fa-lock w-3 h-3 mr-2 text-blue-600"></i>
                                    Password
                                </label>
                                <div class="relative group">
                                    <div class="absolute inset-0 bg-gradient-to-r from-blue-400/5 to-purple-400/5 rounded-xl blur opacity-0 group-hover:opacity-100 transition-opacity duration-200"></div>
                                    <div class="relative">
                                        <?= form_input($password + ['class' => 'w-full px-3 py-3 bg-white border border-gray-200 rounded-xl text-gray-900 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-300 transition-all duration-200 shadow-sm hover:shadow-md text-sm', 'placeholder' => 'Enter your password']) ?>
                                    </div>
                                </div>
                            </div>

                            <!-- Remember Me -->
                            <div class="flex items-center justify-between">
                                <label class="flex items-center space-x-3 cursor-pointer group">
                                    <div class="relative">
                                        <input type="checkbox" id="remember" name="remember" value="1" class="sr-only">
                                        <div class="w-5 h-5 bg-white border-2 border-gray-300 rounded-lg group-hover:border-blue-400 transition-colors duration-200 flex items-center justify-center">
                                            <i class="fas fa-check text-white text-xs opacity-0 transition-opacity duration-200" id="checkIcon"></i>
                                        </div>
                                    </div>
                                    <span class="text-sm font-semibold text-gray-700 group-hover:text-gray-900 transition-colors duration-200">Remember me</span>
                                </label>
                                
                                <a href="<?= base_url('auth/forgot-password') ?>" class="text-sm font-semibold text-blue-600 hover:text-blue-700 hover:underline transition-colors duration-200">
                                    Forgot password?
                                </a>
                            </div>

                            <!-- Submit Button -->
                            <div class="pt-1">
                                <button type="submit" class="group relative w-full py-3 px-4 bg-gradient-to-r from-blue-500 to-purple-600 text-white font-bold rounded-xl shadow-lg shadow-blue-500/20 hover:shadow-blue-500/30 transition-all duration-300 hover:scale-105 hover:-translate-y-0.5">
                                    <div class="absolute inset-0 bg-gradient-to-r from-blue-600 to-purple-700 rounded-xl blur opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
                                    <div class="relative flex items-center justify-center space-x-2 text-sm">
                                        <i class="fas fa-sign-in-alt"></i>
                                        <span>Sign In to Dashboard</span>
                                    </div>
                                </button>
                            </div>
                        <?= form_close() ?>

                        <!-- Additional Links -->
                        <div class="mt-6 text-center">
                            <div class="flex items-center justify-center space-x-3 text-xs text-gray-500">
                                <div class="flex items-center space-x-1">
                                    <div class="w-1 h-1 bg-green-500 rounded-full animate-pulse"></div>
                                    <span class="font-semibold">Secure Login</span>
                                </div>
                                <div class="w-px h-3 bg-gray-300"></div>
                                <div class="flex items-center space-x-1">
                                    <i class="fas fa-shield-alt text-blue-500 text-xs"></i>
                                    <span class="font-semibold">SSL Encrypted</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Footer --> 
            <div class="mt-6 text-center">
                <p class="text-xs text-gray-400">
                    &copy; <?= date('Y') ?> <?= esc($clinic['name']) ?> Management System. All rights reserved.
                </p>
            </div>
        </div>
    </div>

    <script>
        // Enhanced form validation and interactions
        document.addEventListener('DOMContentLoaded', function() {
            // Remember me checkbox functionality
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

            // Form validation
            const form = document.querySelector('form');
            if (form) {
                form.addEventListener('submit', function(e) {
                    const inputs = form.querySelectorAll('input[required]');
                    let isValid = true;
                    
                    inputs.forEach(input => {
                        if (!input.value.trim()) {
                            isValid = false;
                            input.style.borderColor = '#ef4444';
                            input.style.boxShadow = '0 0 0 3px rgba(239, 68, 68, 0.1)';
                        } else {
                            input.style.borderColor = '#d1d5db';
                            input.style.boxShadow = 'none';
                        }
                    });
                    
                    if (!isValid) {
                        e.preventDefault();
                    }
                });
            }

            // Input focus effects
            const inputs = document.querySelectorAll('input[type="text"], input[type="password"]');
            inputs.forEach(input => {
                input.addEventListener('focus', function() {
                    this.style.borderColor = '#3b82f6';
                    this.style.boxShadow = '0 0 0 3px rgba(59, 130, 246, 0.1)';
                });
                
                input.addEventListener('blur', function() {
                    if (!this.value.trim()) {
                        this.style.borderColor = '#d1d5db';
                        this.style.boxShadow = 'none';
                    }
                });
            });

            // Add smooth scrolling
            document.documentElement.style.scrollBehavior = 'smooth';
        });
    </script>
</body>
</html>