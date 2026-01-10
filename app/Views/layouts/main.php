<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?? esc($clinic['name']) ?></title>
    <link rel="stylesheet" href="<?= base_url('assets/css/tailwind.css') ?>?v=<?= time() ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/index.global.min.js"></script>
    <meta name="description" content="Professional dental management system with comprehensive patient care, examination tracking, and appointment scheduling.">
    <meta name="theme-color" content="#0284c7">
</head>
<body class="bg-gray-50 min-h-screen">
    <div class="flex h-screen">
        <!-- Enhanced Sidebar with Glassmorphism -->
        <aside class="w-64 backdrop-blur-xl bg-white/80 border-r border-white/30 shadow-2xl shadow-blue-500/10 z-40 transition-all duration-300 transform flex-shrink-0" id="sidebar">
        <!-- Enhanced Sidebar Header -->
        <div class="p-4 border-b border-white/30">
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-3">
                    <div class="relative group">
                            <div class="absolute inset-0 bg-gradient-to-br from-blue-500 to-purple-600 rounded-xl blur-lg opacity-75 group-hover:opacity-100 transition-opacity duration-200"></div>
                            <div class="relative w-10 h-10 bg-gradient-to-br from-blue-500 to-purple-600 rounded-xl flex items-center justify-center shadow-xl group-hover:scale-105 group-hover:rotate-2 transition-all duration-200">
                            <?php if (!empty($clinic['logo_path'])): ?>
                                <?php 
                                    $logoSrc = (strpos($clinic['logo_path'], 'http://') === 0 || strpos($clinic['logo_path'], 'https://') === 0) 
                                        ? $clinic['logo_path'] 
                                        : base_url(ltrim($clinic['logo_path'], '/'));
                                ?>
                                <img src="<?= esc($logoSrc) ?>" alt="<?= esc($clinic['name']) ?>" class="w-full h-full object-contain">
                            <?php else: ?>
                                <i class="fas fa-tooth text-white text-lg"></i>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div>
                        <h1 class="text-lg font-black text-gray-900"><?= esc($clinic['name']) ?></h1>
                        <div class="flex items-center space-x-1">
                            <div class="w-1.5 h-1.5 bg-green-500 rounded-full animate-pulse"></div>
                            <p class="text-xs text-gray-500 font-medium"><?= esc($clinic['tagline']) ?></p>
                        </div>
                    </div>
                </div>
                <button class="group relative p-2 text-gray-600 hover:text-gray-900 hover:bg-white/50 rounded-xl transition-all duration-200 hover:scale-105" onclick="toggleSidebar()" title="Toggle Sidebar (Ctrl+B)">
                    <div class="absolute inset-0 bg-gradient-to-r from-blue-500/10 to-purple-500/10 rounded-xl blur opacity-0 group-hover:opacity-100 transition-opacity duration-200"></div>
                    <i class="fas fa-bars relative z-10"></i>
                </button>
            </div>
        </div>

        <!-- Enhanced Sidebar Navigation -->
        <nav class="flex-1 overflow-y-auto">
            <!-- Main Menu -->
            <div class="p-4">
                <div class="flex items-center space-x-2 mb-4">
                    <div class="w-2 h-2 bg-gradient-to-r from-blue-500 to-purple-600 rounded-full animate-pulse"></div>
                    <h3 class="text-xs font-bold text-gray-600 uppercase tracking-wider">Main Menu</h3>
                </div>
                <ul class="space-y-1">
                    <li>
                        <a href="<?= base_url('/') ?>" class="group relative flex items-center px-4 py-3 text-sm font-semibold rounded-xl transition-all duration-200 <?= (uri_string() == '' || uri_string() == 'dashboard') ? 'bg-gradient-to-r from-blue-500 to-blue-600 text-white shadow-lg shadow-blue-500/25 mx-1' : 'text-gray-700 hover:bg-gradient-to-r hover:from-blue-50 hover:to-indigo-50 hover:text-blue-800 hover:shadow-md hover:shadow-blue-500/10 mx-1' ?>">
                            <div class="absolute inset-0 bg-gradient-to-r from-blue-500/10 to-purple-500/10 rounded-xl blur opacity-0 group-hover:opacity-100 transition-opacity duration-200"></div>
                            <i class="fas fa-tachometer-alt w-5 h-5 mr-3 relative z-10 <?= (uri_string() == '' || uri_string() == 'dashboard') ? 'text-white' : 'text-blue-600 group-hover:text-blue-700' ?>"></i>
                            <span class="relative z-10">Dashboard</span>
                        </a>
                    </li>
                    <li>
                        <a href="<?= base_url('patient') ?>" class="group relative flex items-center px-4 py-3 text-sm font-semibold rounded-xl transition-all duration-200 <?= (strpos(uri_string(), 'patient') === 0) ? 'bg-gradient-to-r from-emerald-500 to-emerald-600 text-white shadow-lg shadow-emerald-500/25 mx-1' : 'text-gray-700 hover:bg-gradient-to-r hover:from-emerald-50 hover:to-green-50 hover:text-emerald-800 hover:shadow-md hover:shadow-emerald-500/10 mx-1' ?>">
                            <div class="absolute inset-0 bg-gradient-to-r from-emerald-500/10 to-green-500/10 rounded-xl blur opacity-0 group-hover:opacity-100 transition-opacity duration-200"></div>
                            <i class="fas fa-users w-5 h-5 mr-3 relative z-10 <?= (strpos(uri_string(), 'patient') === 0) ? 'text-white' : 'text-emerald-600 group-hover:text-emerald-700' ?>"></i>
                            <span class="relative z-10">Patients</span>
                        </a>
                    </li>
                    <li>
                        <a href="<?= base_url('examination') ?>" class="group relative flex items-center px-4 py-3 text-sm font-semibold rounded-xl transition-all duration-200 <?= (strpos(uri_string(), 'examination') === 0 || uri_string() === 'examination' || uri_string() === 'examination/') ? 'bg-gradient-to-r from-amber-500 to-amber-600 text-white shadow-lg shadow-amber-500/25 mx-1' : 'text-gray-700 hover:bg-gradient-to-r hover:from-amber-50 hover:to-orange-50 hover:text-amber-800 hover:shadow-md hover:shadow-amber-500/10 mx-1' ?>" 
                           style="<?= (strpos(uri_string(), 'examination') === 0 || uri_string() === 'examination' || uri_string() === 'examination/') ? 'background: linear-gradient(to right, #f59e0b, #d97706) !important; color: white !important;' : '' ?>">
                            <div class="absolute inset-0 bg-gradient-to-r from-amber-500/10 to-orange-500/10 rounded-xl blur opacity-0 group-hover:opacity-100 transition-opacity duration-200"></div>
                            <i class="fas fa-stethoscope w-5 h-5 mr-3 relative z-10 <?= (strpos(uri_string(), 'examination') === 0 || uri_string() === 'examination' || uri_string() === 'examination/') ? 'text-white' : 'text-amber-600 group-hover:text-amber-700' ?>" 
                               style="<?= (strpos(uri_string(), 'examination') === 0 || uri_string() === 'examination' || uri_string() === 'examination/') ? 'color: white !important;' : '' ?>"></i>
                            <span class="relative z-10">Examinations</span>
                        </a>
                    </li>
                    <li>
                        <a href="<?= base_url('appointment') ?>" class="group relative flex items-center px-4 py-3 text-sm font-semibold rounded-xl transition-all duration-200 <?= (strpos(uri_string(), 'appointment') === 0) ? 'bg-gradient-to-r from-purple-500 to-purple-600 text-white shadow-lg shadow-purple-500/25 mx-1' : 'text-gray-700 hover:bg-gradient-to-r hover:from-purple-50 hover:to-pink-50 hover:text-purple-800 hover:shadow-md hover:shadow-purple-500/10 mx-1' ?>">
                            <div class="absolute inset-0 bg-gradient-to-r from-purple-500/10 to-pink-500/10 rounded-xl blur opacity-0 group-hover:opacity-100 transition-opacity duration-200"></div>
                            <i class="fas fa-calendar-alt w-5 h-5 mr-3 relative z-10 <?= (strpos(uri_string(), 'appointment') === 0) ? 'text-white' : 'text-purple-600 group-hover:text-purple-700' ?>"></i>
                            <span class="relative z-10">Appointments</span>
                            <span class="ml-auto bg-gradient-to-r from-red-500 to-pink-500 text-white text-xs font-bold px-2 py-1 rounded-full shadow-lg animate-pulse">5</span>
                        </a>
                    </li>
                </ul>
            </div>

            <!-- Clinical Tools -->
            <div class="p-4">
                <div class="flex items-center space-x-2 mb-4">
                    <div class="w-2 h-2 bg-gradient-to-r from-emerald-500 to-cyan-600 rounded-full animate-pulse"></div>
                    <h3 class="text-xs font-bold text-gray-600 uppercase tracking-wider">Clinical Tools</h3>
                </div>
                <ul class="space-y-1">
                    <li>
                        <a href="<?= base_url('odontogram') ?>" class="group relative flex items-center px-4 py-3 text-sm font-semibold rounded-xl transition-all duration-200 <?= (strpos(uri_string(), 'odontogram') === 0 || uri_string() === 'odontogram' || uri_string() === 'odontogram/') ? 'bg-gradient-to-r from-cyan-500 to-cyan-600 text-white shadow-lg shadow-cyan-500/25 mx-1' : 'text-gray-700 hover:bg-gradient-to-r hover:from-cyan-50 hover:to-blue-50 hover:text-cyan-800 hover:shadow-md hover:shadow-cyan-500/10 mx-1' ?>" 
                           style="<?= (strpos(uri_string(), 'odontogram') === 0 || uri_string() === 'odontogram' || uri_string() === 'odontogram/') ? 'background: linear-gradient(to right, #06b6d4, #0891b2) !important; color: white !important;' : '' ?>">
                            <div class="absolute inset-0 bg-gradient-to-r from-cyan-500/10 to-blue-500/10 rounded-xl blur opacity-0 group-hover:opacity-100 transition-opacity duration-200"></div>
                            <i class="fas fa-tooth w-5 h-5 mr-3 relative z-10 <?= (strpos(uri_string(), 'odontogram') === 0 || uri_string() === 'odontogram' || uri_string() === 'odontogram/') ? 'text-white' : 'text-cyan-600 group-hover:text-cyan-700' ?>" 
                               style="<?= (strpos(uri_string(), 'odontogram') === 0 || uri_string() === 'odontogram' || uri_string() === 'odontogram/') ? 'color: white !important;' : '' ?>"></i>
                            <span class="relative z-10">Odontogram</span>
                        </a>
                    </li>
                    <li>
                        <a href="<?= base_url('treatment') ?>" class="group relative flex items-center px-4 py-3 text-sm font-semibold rounded-xl transition-all duration-200 <?= (strpos(uri_string(), 'treatment') === 0) ? 'bg-gradient-to-r from-indigo-500 to-indigo-600 text-white shadow-lg shadow-indigo-500/25 mx-1' : 'text-gray-700 hover:bg-gradient-to-r hover:from-indigo-50 hover:to-purple-50 hover:text-indigo-800 hover:shadow-md hover:shadow-indigo-500/10 mx-1' ?>">
                            <div class="absolute inset-0 bg-gradient-to-r from-indigo-500/10 to-purple-500/10 rounded-xl blur opacity-0 group-hover:opacity-100 transition-opacity duration-200"></div>
                            <i class="fas fa-procedures w-5 h-5 mr-3 relative z-10 <?= (strpos(uri_string(), 'treatment') === 0) ? 'text-white' : 'text-indigo-600 group-hover:text-indigo-700' ?>"></i>
                            <span class="relative z-10">Treatments</span>
                        </a>
                    </li>
                    <li>
                        <a href="<?= base_url('prescription') ?>" class="group relative flex items-center px-4 py-3 text-sm font-semibold rounded-xl transition-all duration-200 <?= (strpos(uri_string(), 'prescription') === 0) ? 'bg-gradient-to-r from-teal-500 to-teal-600 text-white shadow-lg shadow-teal-500/25 mx-1' : 'text-gray-700 hover:bg-gradient-to-r hover:from-teal-50 hover:to-emerald-50 hover:text-teal-800 hover:shadow-md hover:shadow-teal-500/10 mx-1' ?>">
                            <div class="absolute inset-0 bg-gradient-to-r from-teal-500/10 to-emerald-500/10 rounded-xl blur opacity-0 group-hover:opacity-100 transition-opacity duration-200"></div>
                            <i class="fas fa-prescription-bottle-alt w-5 h-5 mr-3 relative z-10 <?= (strpos(uri_string(), 'prescription') === 0) ? 'text-white' : 'text-teal-600 group-hover:text-teal-700' ?>"></i>
                            <span class="relative z-10">Prescriptions</span>
                        </a>
                    </li>
                </ul>
            </div>
                
            <!-- Business -->
            <div class="p-4">
                <div class="flex items-center space-x-2 mb-4">
                    <div class="w-2 h-2 bg-gradient-to-r from-purple-500 to-pink-600 rounded-full animate-pulse"></div>
                    <h3 class="text-xs font-bold text-gray-600 uppercase tracking-wider">Business</h3>
                </div>
                <ul class="space-y-1">
                    <li>
                        <a href="<?= base_url('finance') ?>" class="group relative flex items-center px-4 py-3 text-sm font-semibold rounded-xl transition-all duration-200 <?= (strpos(uri_string(), 'finance') === 0) ? 'bg-gradient-to-r from-green-500 to-green-600 text-white shadow-lg shadow-green-500/25 mx-1' : 'text-gray-700 hover:bg-gradient-to-r hover:from-green-50 hover:to-emerald-50 hover:text-green-800 hover:shadow-md hover:shadow-green-500/10 mx-1' ?>">
                            <div class="absolute inset-0 bg-gradient-to-r from-green-500/10 to-emerald-500/10 rounded-xl blur opacity-0 group-hover:opacity-100 transition-opacity duration-200"></div>
                            <i class="fas fa-chart-line w-5 h-5 mr-3 relative z-10 <?= (strpos(uri_string(), 'finance') === 0) ? 'text-white' : 'text-green-600 group-hover:text-green-700' ?>"></i>
                            <span class="relative z-10">Finance</span>
                        </a>
                    </li>
                    <li>
                        <a href="<?= base_url('reports') ?>" class="group relative flex items-center px-4 py-3 text-sm font-semibold rounded-xl transition-all duration-200 <?= (strpos(uri_string(), 'reports') === 0) ? 'bg-gradient-to-r from-orange-500 to-orange-600 text-white shadow-lg shadow-orange-500/25 mx-1' : 'text-gray-700 hover:bg-gradient-to-r hover:from-orange-50 hover:to-red-50 hover:text-orange-800 hover:shadow-md hover:shadow-orange-500/10 mx-1' ?>">
                            <div class="absolute inset-0 bg-gradient-to-r from-orange-500/10 to-red-500/10 rounded-xl blur opacity-0 group-hover:opacity-100 transition-opacity duration-200"></div>
                            <i class="fas fa-chart-bar w-5 h-5 mr-3 relative z-10 <?= (strpos(uri_string(), 'reports') === 0) ? 'text-white' : 'text-orange-600 group-hover:text-orange-700' ?>"></i>
                            <span class="relative z-10">Reports</span>
                        </a>
                    </li>
                    <li>
                        <a href="<?= base_url('inventory') ?>" class="group relative flex items-center px-4 py-3 text-sm font-semibold rounded-xl transition-all duration-200 <?= (strpos(uri_string(), 'inventory') === 0) ? 'bg-gradient-to-r from-slate-500 to-slate-600 text-white shadow-lg shadow-slate-500/25 mx-1' : 'text-gray-700 hover:bg-gradient-to-r hover:from-slate-50 hover:to-gray-50 hover:text-slate-800 hover:shadow-md hover:shadow-slate-500/10 mx-1' ?>">
                            <div class="absolute inset-0 bg-gradient-to-r from-slate-500/10 to-gray-500/10 rounded-xl blur opacity-0 group-hover:opacity-100 transition-opacity duration-200"></div>
                            <i class="fas fa-boxes w-5 h-5 mr-3 relative z-10 <?= (strpos(uri_string(), 'inventory') === 0) ? 'text-white' : 'text-slate-600 group-hover:text-slate-700' ?>"></i>
                            <span class="relative z-10">Inventory</span>
                        </a>
                    </li>
                </ul>
            </div>

            <!-- Settings -->
            <div class="p-4">
                <div class="flex items-center space-x-2 mb-4">
                    <div class="w-2 h-2 bg-gradient-to-r from-gray-500 to-slate-600 rounded-full animate-pulse"></div>
                    <h3 class="text-xs font-bold text-gray-600 uppercase tracking-wider">Settings</h3>
                </div>
                <ul class="space-y-1">
                    <li>
                        <a href="<?= base_url('settings') ?>" class="group relative flex items-center px-4 py-3 text-sm font-semibold rounded-xl transition-all duration-200 <?= (strpos(uri_string(), 'settings') === 0) ? 'bg-gradient-to-r from-gray-500 to-gray-600 text-white shadow-lg shadow-gray-500/25 mx-1' : 'text-gray-700 hover:bg-gradient-to-r hover:from-gray-50 hover:to-slate-50 hover:text-gray-800 hover:shadow-md hover:shadow-gray-500/10 mx-1' ?>">
                            <div class="absolute inset-0 bg-gradient-to-r from-gray-500/10 to-slate-500/10 rounded-xl blur opacity-0 group-hover:opacity-100 transition-opacity duration-200"></div>
                            <i class="fas fa-cog w-5 h-5 mr-3 relative z-10 <?= (strpos(uri_string(), 'settings') === 0) ? 'text-white' : 'text-gray-600 group-hover:text-gray-700' ?>"></i>
                            <span class="relative z-10">Settings</span>
                        </a>
                    </li>
                    <li>
                        <a href="<?= base_url('users') ?>" class="group relative flex items-center px-4 py-3 text-sm font-semibold rounded-xl transition-all duration-200 <?= (strpos(uri_string(), 'users') === 0) ? 'bg-gradient-to-r from-violet-500 to-violet-600 text-white shadow-lg shadow-violet-500/25 mx-1' : 'text-gray-700 hover:bg-gradient-to-r hover:from-violet-50 hover:to-purple-50 hover:text-violet-800 hover:shadow-md hover:shadow-violet-500/10 mx-1' ?>">
                            <div class="absolute inset-0 bg-gradient-to-r from-violet-500/10 to-purple-500/10 rounded-xl blur opacity-0 group-hover:opacity-100 transition-opacity duration-200"></div>
                            <i class="fas fa-user-cog w-5 h-5 mr-3 relative z-10 <?= (strpos(uri_string(), 'users') === 0) ? 'text-white' : 'text-violet-600 group-hover:text-violet-700' ?>"></i>
                            <span class="relative z-10">Users</span>
                        </a>
                    </li>
                </ul>
            </div>
        </nav>

        <!-- Enhanced Sidebar Footer -->
        <div class="p-4 border-t border-white/30">
            <div class="group relative cursor-pointer hover:bg-white/50 rounded-xl p-3 transition-all duration-200 hover:shadow-lg hover:shadow-blue-500/10" onclick="toggleUserMenu()">
                <div class="absolute inset-0 bg-gradient-to-r from-blue-500/10 to-purple-500/10 rounded-xl blur opacity-0 group-hover:opacity-100 transition-opacity duration-200"></div>
                <div class="flex items-center space-x-3 relative z-10">
                    <div class="relative">
                        <div class="absolute inset-0 bg-gradient-to-br from-blue-500 to-purple-600 rounded-full blur-lg opacity-75 group-hover:opacity-100 transition-opacity duration-200"></div>
                        <div class="relative w-10 h-10 bg-gradient-to-br from-blue-500 to-purple-600 rounded-full flex items-center justify-center shadow-lg group-hover:scale-105 group-hover:rotate-2 transition-all duration-200">
                            <i class="fas fa-user text-white text-sm"></i>
                        </div>
                    </div>
                    <div class="flex-1 min-w-0">
                        <div class="text-sm font-bold text-gray-900 truncate">Dr. Smith</div>
                        <div class="flex items-center space-x-1">
                            <div class="w-1.5 h-1.5 bg-green-500 rounded-full animate-pulse"></div>
                            <div class="text-xs text-gray-500 font-medium">Administrator</div>
                        </div>
                    </div>
                    <i class="fas fa-chevron-down text-gray-400 text-xs group-hover:text-gray-600 transition-colors duration-200"></i>
                </div>
            </div>
            
            <!-- Enhanced User Menu Dropdown -->
            <div id="userMenu" class="hidden mt-3 backdrop-blur-xl bg-white/80 rounded-2xl shadow-2xl shadow-blue-500/10 border border-white/30 p-2 space-y-1">
                <a href="#" class="group relative flex items-center px-3 py-2.5 text-sm font-semibold text-gray-700 hover:bg-gradient-to-r hover:from-blue-50 hover:to-indigo-50 hover:text-blue-800 rounded-xl transition-all duration-200">
                    <div class="absolute inset-0 bg-gradient-to-r from-blue-500/10 to-indigo-500/10 rounded-xl blur opacity-0 group-hover:opacity-100 transition-opacity duration-200"></div>
                    <i class="fas fa-user mr-3 relative z-10 text-blue-600 group-hover:text-blue-700"></i>
                    <span class="relative z-10">Profile</span>
                </a>
                <a href="#" class="group relative flex items-center px-3 py-2.5 text-sm font-semibold text-gray-700 hover:bg-gradient-to-r hover:from-gray-50 hover:to-slate-50 hover:text-gray-800 rounded-xl transition-all duration-200">
                    <div class="absolute inset-0 bg-gradient-to-r from-gray-500/10 to-slate-500/10 rounded-xl blur opacity-0 group-hover:opacity-100 transition-opacity duration-200"></div>
                    <i class="fas fa-cog mr-3 relative z-10 text-gray-600 group-hover:text-gray-700"></i>
                    <span class="relative z-10">Settings</span>
                </a>
                <a href="<?= base_url('auth/logout') ?>" class="group relative flex items-center px-3 py-2.5 text-sm font-semibold text-red-600 hover:bg-gradient-to-r hover:from-red-50 hover:to-pink-50 hover:text-red-800 rounded-xl transition-all duration-200">
                    <div class="absolute inset-0 bg-gradient-to-r from-red-500/10 to-pink-500/10 rounded-xl blur opacity-0 group-hover:opacity-100 transition-opacity duration-200"></div>
                    <i class="fas fa-sign-out-alt mr-3 relative z-10 text-red-600 group-hover:text-red-700"></i>
                    <span class="relative z-10">Logout</span>
                </a>
            </div>
        </div>
    </aside>

        <!-- Main Content -->
        <div class="flex-1 min-h-screen bg-gray-50 transition-all duration-200 flex flex-col overflow-hidden" id="mainContent">
        <!-- Enhanced Top Navigation with Glassmorphism -->
        <nav class="backdrop-blur-xl bg-white/80 border-b border-white/30 shadow-2xl shadow-blue-500/10 sticky top-0 z-30 relative">
            <!-- Animated Background Elements -->
            <div class="absolute inset-0 overflow-hidden pointer-events-none">
                <div class="absolute -top-20 -right-20 w-40 h-40 bg-gradient-to-br from-blue-400/10 to-purple-600/10 rounded-full blur-2xl animate-pulse"></div>
                <div class="absolute -bottom-20 -left-20 w-40 h-40 bg-gradient-to-br from-emerald-400/10 to-cyan-600/10 rounded-full blur-2xl animate-pulse delay-1000"></div>
            </div>
            
            <div class="relative z-10 px-6 py-2">
                <div class="flex justify-between items-center">
                    <!-- Enhanced Page Title Section -->
                    <div class="flex items-center space-x-6">
                        
                        
                        <!-- Enhanced Search Bar -->
                        <div class="relative">
                            <div class="relative group">
                                <div class="absolute inset-0 bg-gradient-to-r from-blue-500/10 to-purple-500/10 rounded-2xl blur opacity-0 group-hover:opacity-100 transition-opacity duration-200"></div>
                                <div class="relative flex items-center">
                                    <input type="text" 
                                           placeholder="Search patients, appointments, treatments..." 
                                           class="w-64 sm:w-80 px-4 py-3 bg-white border border-gray-200 rounded-xl text-sm placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-300 transition-all duration-200 shadow-sm hover:shadow-md">
                                </div>
                            </div> 
                            
                            <!-- Search Suggestions Dropdown -->
                            <div id="searchSuggestions" class="hidden fixed top-20 left-6 right-6 bg-white/95 backdrop-blur-xl rounded-2xl shadow-2xl shadow-blue-500/10 border border-white/30" style="z-index: 999999 !important;">
                                <div class="p-4 border-b border-white/30">
                                    <div class="flex items-center justify-between">
                                        <h3 class="text-sm font-bold text-gray-800">Quick Search</h3>
                                        <span class="text-xs text-gray-500">Press Enter to search</span>
                                    </div>
                                </div>
                                <div class="max-h-80 overflow-y-auto">
                                    <!-- Recent Searches -->
                                    <div class="p-3">
                                        <p class="text-xs font-semibold text-gray-600 uppercase tracking-wider mb-2">Recent</p>
                                        <div class="space-y-1">
                                            <div class="flex items-center space-x-3 p-2 hover:bg-blue-50/50 rounded-xl cursor-pointer transition-colors">
                                                <i class="fas fa-history text-gray-400 text-sm"></i>
                                                <span class="text-sm text-gray-700">John Doe</span>
                                            </div>
                                            <div class="flex items-center space-x-3 p-2 hover:bg-blue-50/50 rounded-xl cursor-pointer transition-colors">
                                                <i class="fas fa-history text-gray-400 text-sm"></i>
                                                <span class="text-sm text-gray-700">Appointment tomorrow</span>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <!-- Quick Actions -->
                                    <div class="p-3 border-t border-white/30">
                                        <p class="text-xs font-semibold text-gray-600 uppercase tracking-wider mb-2">Quick Actions</p>
                                        <div class="grid grid-cols-2 gap-2">
                                            <div class="flex items-center space-x-2 p-2 hover:bg-emerald-50/50 rounded-xl cursor-pointer transition-colors">
                                                <i class="fas fa-user-plus text-emerald-500 text-sm"></i>
                                                <span class="text-sm text-gray-700">Add Patient</span>
                                            </div>
                                            <div class="flex items-center space-x-2 p-2 hover:bg-amber-50/50 rounded-xl cursor-pointer transition-colors">
                                                <i class="fas fa-calendar-plus text-amber-500 text-sm"></i>
                                                <span class="text-sm text-gray-700">New Appointment</span>
                                            </div>
                                            <div class="flex items-center space-x-2 p-2 hover:bg-purple-50/50 rounded-xl cursor-pointer transition-colors">
                                                <i class="fas fa-stethoscope text-purple-500 text-sm"></i>
                                                <span class="text-sm text-gray-700">Start Examination</span>
                                            </div>
                                            <div class="flex items-center space-x-2 p-2 hover:bg-cyan-50/50 rounded-xl cursor-pointer transition-colors">
                                                <i class="fas fa-receipt text-cyan-500 text-sm"></i>
                                                <span class="text-sm text-gray-700">Create Invoice</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Enhanced Top Right Actions -->
                    <div class="flex items-center space-x-3">
                        <!-- Quick Actions Menu -->
                        <div class="hidden md:flex items-center space-x-2">
                            <div class="relative group">
                                <button class="flex items-center space-x-2 px-4 py-2 bg-gradient-to-r from-blue-50 to-indigo-50 hover:from-blue-100 hover:to-indigo-100 text-blue-700 hover:text-blue-800 rounded-xl border border-blue-200 hover:border-blue-300 transition-all duration-200 hover:scale-105">
                                    <i class="fas fa-plus text-sm"></i>
                                    <span class="text-sm font-semibold">Quick Add</span>
                                </button>
                                
                                <!-- Quick Actions Dropdown -->
                                <div class="hidden fixed top-20 right-6 w-64 bg-white/80 backdrop-blur-xl rounded-2xl shadow-2xl shadow-blue-500/10 border border-white/30 group-hover:block" style="z-index: 999999 !important;">
                                    <div class="p-4">
                                        <h3 class="text-sm font-bold text-gray-800 mb-3">Quick Actions</h3>
                                        <div class="grid grid-cols-2 gap-3">
                                            <a href="<?= base_url('patient/create') ?>" class="group/action flex flex-col items-center p-3 rounded-xl hover:bg-emerald-50/50 transition-all duration-200">
                                                <div class="w-10 h-10 bg-gradient-to-br from-emerald-500 to-emerald-600 rounded-xl flex items-center justify-center mb-2 group-hover/action:scale-110 transition-transform duration-300">
                                                    <i class="fas fa-user-plus text-white text-sm"></i>
                                                </div>
                                                <span class="text-xs font-semibold text-gray-700">Add Patient</span>
                                            </a>
                                            <a href="<?= base_url('appointment/create') ?>" class="group/action flex flex-col items-center p-3 rounded-xl hover:bg-amber-50/50 transition-all duration-200">
                                                <div class="w-10 h-10 bg-gradient-to-br from-amber-500 to-amber-600 rounded-xl flex items-center justify-center mb-2 group-hover/action:scale-110 transition-transform duration-300">
                                                    <i class="fas fa-calendar-plus text-white text-sm"></i>
                                                </div>
                                                <span class="text-xs font-semibold text-gray-700">New Appointment</span>
                                            </a>
                                            <a href="<?= base_url('examination/create') ?>" class="group/action flex flex-col items-center p-3 rounded-xl hover:bg-purple-50/50 transition-all duration-200">
                                                <div class="w-10 h-10 bg-gradient-to-br from-purple-500 to-purple-600 rounded-xl flex items-center justify-center mb-2 group-hover/action:scale-110 transition-transform duration-300">
                                                    <i class="fas fa-stethoscope text-white text-sm"></i>
                                                </div>
                                                <span class="text-xs font-semibold text-gray-700">Start Examination</span>
                                            </a>
                                            <a href="<?= base_url('finance/create') ?>" class="group/action flex flex-col items-center p-3 rounded-xl hover:bg-cyan-50/50 transition-all duration-200">
                                                <div class="w-10 h-10 bg-gradient-to-br from-cyan-500 to-cyan-600 rounded-xl flex items-center justify-center mb-2 group-hover/action:scale-110 transition-transform duration-300">
                                                    <i class="fas fa-receipt text-white text-sm"></i>
                                                </div>
                                                <span class="text-xs font-semibold text-gray-700">Create Invoice</span>
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- System Status Indicator -->
                        <div class="hidden lg:flex items-center space-x-2 px-3 py-2 bg-gradient-to-r from-green-50 to-emerald-50 rounded-xl border border-green-200">
                            <div class="w-2 h-2 bg-green-500 rounded-full animate-pulse"></div>
                            <span class="text-xs font-semibold text-green-700">System Online</span>
                        </div>
                        
                        <!-- Enhanced Notifications -->
                        <div class="relative">
                            <button id="notificationBtn" class="group relative flex items-center space-x-2 text-gray-600 hover:text-gray-800 transition-all duration-200 p-3 rounded-xl hover:bg-gradient-to-r hover:from-amber-50 hover:to-orange-50 hover:shadow-lg hover:shadow-amber-500/10">
                                <div class="absolute inset-0 bg-gradient-to-r from-amber-500/10 to-orange-500/10 rounded-xl blur opacity-0 group-hover:opacity-100 transition-opacity duration-200"></div>
                                <i class="fas fa-bell text-lg relative z-10 group-hover:text-amber-600 transition-colors duration-200"></i>
                                <span class="absolute -top-1 -right-1 bg-gradient-to-r from-red-500 to-pink-500 text-white text-xs rounded-full w-6 h-6 flex items-center justify-center font-bold shadow-lg animate-pulse">3</span>
                            </button>
                            
                            <!-- Enhanced Notification Dropdown -->
                            <div id="notificationDropdown" class="hidden fixed top-20 right-6 w-96 bg-white/80 backdrop-blur-xl rounded-2xl shadow-2xl shadow-amber-500/10 border border-white/30" style="z-index: 999999 !important;">
                                <div class="p-6 border-b border-white/30">
                                    <div class="flex items-center justify-between">
                                        <div class="flex items-center space-x-3">
                                            <div class="w-3 h-3 bg-gradient-to-r from-amber-500 to-orange-600 rounded-full animate-pulse"></div>
                                            <h3 class="text-lg font-black text-gray-900">Notifications</h3>
                                        </div>
                                        <button class="text-sm text-blue-600 hover:text-blue-700 font-semibold px-3 py-1 rounded-lg hover:bg-blue-50 transition-colors duration-200">Mark all as read</button>
                                    </div>
                                </div>
                                <div class="max-h-96 overflow-y-auto">
                                    <!-- Notification Item 1 -->
                                    <div class="group/notif p-4 border-b border-white/30 hover:bg-gradient-to-r hover:from-red-50/50 hover:to-pink-50/50 transition-all duration-200">
                                        <div class="flex items-start space-x-4">
                                            <div class="relative">
                                                <div class="absolute inset-0 bg-gradient-to-br from-red-500 to-pink-600 rounded-xl blur opacity-75 group-hover/notif:opacity-100 transition-opacity duration-200"></div>
                                                <div class="relative w-10 h-10 bg-gradient-to-br from-red-500 to-pink-600 rounded-xl flex items-center justify-center shadow-lg">
                                                    <i class="fas fa-calendar text-white text-sm"></i>
                                                </div>
                                            </div>
                                            <div class="flex-1 min-w-0">
                                                <p class="text-sm font-bold text-gray-900">New Appointment Scheduled</p>
                                                <p class="text-xs text-gray-600 mt-1">Patient John Doe has scheduled an appointment for tomorrow at 2:00 PM</p>
                                                <div class="flex items-center space-x-2 mt-2">
                                                    <span class="text-xs text-gray-400">2 minutes ago</span>
                                                    <div class="w-2 h-2 bg-red-500 rounded-full"></div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <!-- Notification Item 2 -->
                                    <div class="group/notif p-4 border-b border-white/30 hover:bg-gradient-to-r hover:from-yellow-50/50 hover:to-orange-50/50 transition-all duration-200">
                                        <div class="flex items-start space-x-4">
                                            <div class="relative">
                                                <div class="absolute inset-0 bg-gradient-to-br from-yellow-500 to-orange-600 rounded-xl blur opacity-75 group-hover/notif:opacity-100 transition-opacity duration-200"></div>
                                                <div class="relative w-10 h-10 bg-gradient-to-br from-yellow-500 to-orange-600 rounded-xl flex items-center justify-center shadow-lg">
                                                    <i class="fas fa-prescription-bottle-alt text-white text-sm"></i>
                                                </div>
                                            </div>
                                            <div class="flex-1 min-w-0">
                                                <p class="text-sm font-bold text-gray-900">Prescription Reminder</p>
                                                <p class="text-xs text-gray-600 mt-1">Patient Jane Smith's prescription is due for renewal</p>
                                                <div class="flex items-center space-x-2 mt-2">
                                                    <span class="text-xs text-gray-400">1 hour ago</span>
                                                    <div class="w-2 h-2 bg-yellow-500 rounded-full"></div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <!-- Notification Item 3 -->
                                    <div class="group/notif p-4 hover:bg-gradient-to-r hover:from-green-50/50 hover:to-emerald-50/50 transition-all duration-200">
                                        <div class="flex items-start space-x-4">
                                            <div class="relative">
                                                <div class="absolute inset-0 bg-gradient-to-br from-green-500 to-emerald-600 rounded-xl blur opacity-75 group-hover/notif:opacity-100 transition-opacity duration-200"></div>
                                                <div class="relative w-10 h-10 bg-gradient-to-br from-green-500 to-emerald-600 rounded-xl flex items-center justify-center shadow-lg">
                                                    <i class="fas fa-dollar-sign text-white text-sm"></i>
                                                </div>
                                            </div>
                                            <div class="flex-1 min-w-0">
                                                <p class="text-sm font-bold text-gray-900">Payment Received</p>
                                                <p class="text-xs text-gray-600 mt-1">Payment of $150 has been received from Mike Johnson</p>
                                                <div class="flex items-center space-x-2 mt-2">
                                                    <span class="text-xs text-gray-400">3 hours ago</span>
                                                    <div class="w-2 h-2 bg-green-500 rounded-full"></div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="p-4 border-t border-white/30">
                                    <a href="<?= base_url('activity-log') ?>" class="block text-center text-sm text-blue-600 hover:text-blue-700 font-semibold px-4 py-2 rounded-xl hover:bg-blue-50 transition-colors duration-200">View all notifications</a>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Enhanced User Profile -->
                        <div class="relative">
                            <button id="userProfileBtn" class="group relative flex items-center space-x-3 text-gray-600 hover:text-gray-800 transition-all duration-200 p-3 rounded-xl hover:bg-gradient-to-r hover:from-blue-50 hover:to-indigo-50 hover:shadow-lg hover:shadow-blue-500/10">
                                <div class="absolute inset-0 bg-gradient-to-r from-blue-500/10 to-purple-500/10 rounded-xl blur opacity-0 group-hover:opacity-100 transition-opacity duration-200"></div>
                                <div class="relative w-10 h-10 bg-gradient-to-br from-blue-600 to-purple-600 rounded-xl flex items-center justify-center shadow-lg group-hover:scale-105 group-hover:rotate-2 transition-all duration-200">
                                    <i class="fas fa-user text-white text-sm"></i>
                                </div>
                                <div class="hidden md:block text-left relative z-10">
                                    <div class="text-sm font-bold text-gray-900">Dr. Smith</div>
                                    <div class="flex items-center space-x-1">
                                        <div class="w-1.5 h-1.5 bg-green-500 rounded-full animate-pulse"></div>
                                        <div class="text-xs text-gray-500 font-medium">Administrator</div>
                                    </div>
                                </div>
                                <i class="fas fa-chevron-down text-xs relative z-10 group-hover:text-blue-600 transition-colors duration-200"></i>
                            </button>
                            
                            <!-- Enhanced User Profile Dropdown -->
                            <div id="userProfileDropdown" class="hidden fixed top-20 right-6 w-72 bg-white/80 backdrop-blur-xl rounded-2xl shadow-2xl shadow-blue-500/10 border border-white/30" style="z-index: 999999 !important;">
                                <div class="p-6 border-b border-white/30">
                                    <div class="flex items-center space-x-4">
                                        <div class="relative">
                                            <div class="absolute inset-0 bg-gradient-to-br from-blue-500 to-purple-600 rounded-2xl blur opacity-75"></div>
                                            <div class="relative w-16 h-16 bg-gradient-to-br from-blue-600 to-purple-600 rounded-2xl flex items-center justify-center shadow-xl">
                                                <i class="fas fa-user text-white text-xl"></i>
                                            </div>
                                        </div>
                                        <div class="flex-1 min-w-0">
                                            <p class="text-lg font-black text-gray-900">Dr. Smith</p>
                                            <p class="text-sm text-gray-600 font-medium">Administrator</p>
                                            <p class="text-xs text-gray-500">dr.smith@dentalcare.com</p>
                                            <div class="flex items-center space-x-2 mt-1">
                                                <div class="w-2 h-2 bg-green-500 rounded-full animate-pulse"></div>
                                                <span class="text-xs text-green-600 font-semibold">Online</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="py-2">
                                    <a href="#" class="group/link flex items-center px-4 py-3 text-sm text-gray-700 hover:bg-gradient-to-r hover:from-blue-50 hover:to-indigo-50 hover:text-blue-800 transition-all duration-200 mx-2 rounded-xl">
                                        <div class="absolute inset-0 bg-gradient-to-r from-blue-500/10 to-indigo-500/10 rounded-xl blur opacity-0 group-hover/link:opacity-100 transition-opacity duration-200"></div>
                                        <i class="fas fa-user mr-3 text-blue-600 relative z-10"></i>
                                        <span class="relative z-10 font-semibold">My Profile</span>
                                    </a>
                                    <a href="#" class="group/link flex items-center px-4 py-3 text-sm text-gray-700 hover:bg-gradient-to-r hover:from-gray-50 hover:to-slate-50 hover:text-gray-800 transition-all duration-200 mx-2 rounded-xl">
                                        <div class="absolute inset-0 bg-gradient-to-r from-gray-500/10 to-slate-500/10 rounded-xl blur opacity-0 group-hover/link:opacity-100 transition-opacity duration-200"></div>
                                        <i class="fas fa-cog mr-3 text-gray-600 relative z-10"></i>
                                        <span class="relative z-10 font-semibold">Settings</span>
                                    </a>
                                    <a href="#" class="group/link flex items-center px-4 py-3 text-sm text-gray-700 hover:bg-gradient-to-r hover:from-amber-50 hover:to-orange-50 hover:text-amber-800 transition-all duration-200 mx-2 rounded-xl">
                                        <div class="absolute inset-0 bg-gradient-to-r from-amber-500/10 to-orange-500/10 rounded-xl blur opacity-0 group-hover/link:opacity-100 transition-opacity duration-200"></div>
                                        <i class="fas fa-bell mr-3 text-amber-600 relative z-10"></i>
                                        <span class="relative z-10 font-semibold">Notifications</span>
                                    </a>
                                    <a href="#" class="group/link flex items-center px-4 py-3 text-sm text-gray-700 hover:bg-gradient-to-r hover:from-purple-50 hover:to-pink-50 hover:text-purple-800 transition-all duration-200 mx-2 rounded-xl">
                                        <div class="absolute inset-0 bg-gradient-to-r from-purple-500/10 to-pink-500/10 rounded-xl blur opacity-0 group-hover/link:opacity-100 transition-opacity duration-200"></div>
                                        <i class="fas fa-question-circle mr-3 text-purple-600 relative z-10"></i>
                                        <span class="relative z-10 font-semibold">Help & Support</span>
                                    </a>
                                </div>
                                <div class="border-t border-white/30 py-2">
                                    <a href="<?= base_url('auth/logout') ?>" class="group/link flex items-center px-4 py-3 text-sm text-red-600 hover:bg-gradient-to-r hover:from-red-50 hover:to-pink-50 hover:text-red-800 transition-all duration-200 mx-2 rounded-xl">
                                        <div class="absolute inset-0 bg-gradient-to-r from-red-500/10 to-pink-500/10 rounded-xl blur opacity-0 group-hover/link:opacity-100 transition-opacity duration-200"></div>
                                        <i class="fas fa-sign-out-alt mr-3 relative z-10"></i>
                                        <span class="relative z-10 font-semibold">Sign Out</span>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </nav>

        <!-- Main Content Area -->
        <main class="flex-1 px-6 py-8 overflow-y-auto relative z-10">
            <!-- Flash Messages -->
            <?php if (session()->getFlashdata('success')): ?>
                <div class="mb-6 p-4 bg-green-50 border border-green-200 rounded-lg flex items-start">
                    <i class="fas fa-check-circle mr-3 text-lg text-green-600"></i>
                    <div>
                        <div class="font-medium text-green-800">Success!</div>
                        <div class="text-sm text-green-700"><?= session()->getFlashdata('success') ?></div>
                    </div>
                </div>
            <?php endif; ?>

            <?php if (session()->getFlashdata('error')): ?>
                <div class="mb-6 p-4 bg-red-50 border border-red-200 rounded-lg flex items-start">
                    <i class="fas fa-exclamation-circle mr-3 text-lg text-red-600"></i>
                    <div>
                        <div class="font-medium text-red-800">Error!</div>
                        <div class="text-sm text-red-700"><?= session()->getFlashdata('error') ?></div>
                    </div>
                </div>
            <?php endif; ?>

            <?php if (session()->getFlashdata('warning')): ?>
                <div class="mb-6 p-4 bg-yellow-50 border border-yellow-200 rounded-lg flex items-start">
                    <i class="fas fa-exclamation-triangle mr-3 text-lg text-yellow-600"></i>
                    <div>
                        <div class="font-medium text-yellow-800">Warning!</div>
                        <div class="text-sm text-yellow-700"><?= session()->getFlashdata('warning') ?></div>
                    </div>
                </div>
            <?php endif; ?>

            <?php if (session()->getFlashdata('info')): ?>
                <div class="mb-6 p-4 bg-blue-50 border border-blue-200 rounded-lg flex items-start">
                    <i class="fas fa-info-circle mr-3 text-lg text-blue-600"></i>
                    <div>
                        <div class="font-medium text-blue-800">Information</div>
                        <div class="text-sm text-blue-700"><?= session()->getFlashdata('info') ?></div>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Page Content -->
            <div class="min-h-screen pb-8">
                <?= $this->renderSection('content') ?>
            </div>
        </main>

        <!-- Footer -->
        <footer class="bg-white border-t border-gray-200 py-4">
            <div class="px-6">
                <div class="flex justify-center items-center">
                    <p class="text-gray-500 text-sm">
                        &copy; <?= date('Y') ?> <?= esc($clinic['name']) ?> Management System. All rights reserved.
                    </p>
                </div>
            </div>
        </footer>
        </div>
    </div>

    <style>
        /* Ensure header modals appear above all content */
        #searchSuggestions,
        #notificationDropdown,
        #userProfileDropdown,
        .group:hover .group-hover\:block {
            z-index: 999999 !important;
        }
    </style>

    <script>
        // Sidebar functionality
        function toggleSidebar() {
            const sidebar = document.getElementById('sidebar');
            const mainContent = document.getElementById('mainContent');
            const sidebarToggle = document.getElementById('sidebarToggle');
            
            if (!sidebar || !mainContent) {
                console.error('Sidebar or main content element not found');
                return;
            }
            
            sidebar.classList.toggle('-translate-x-full');
            
            const isCollapsed = sidebar.classList.contains('-translate-x-full');
            localStorage.setItem('sidebarCollapsed', isCollapsed);
            
            // Update toggle button icon
            if (sidebarToggle) {
                const icon = sidebarToggle.querySelector('i');
                if (icon) {
                    if (isCollapsed) {
                        icon.className = 'fas fa-bars text-lg relative z-10 group-hover:text-blue-600 transition-colors duration-200';
                    } else {
                        icon.className = 'fas fa-times text-lg relative z-10 group-hover:text-blue-600 transition-colors duration-200';
                    }
                }
            }
        }

        function toggleUserMenu() {
            const userMenu = document.getElementById('userMenu');
            if (userMenu) {
                userMenu.classList.toggle('hidden');
            }
        }

        // Header dropdown functionality
        function toggleNotificationDropdown() {
            const dropdown = document.getElementById('notificationDropdown');
            const userDropdown = document.getElementById('userProfileDropdown');
            
            if (dropdown) {
                dropdown.classList.toggle('hidden');
                if (userDropdown && !userDropdown.classList.contains('hidden')) {
                    userDropdown.classList.add('hidden');
                }
            }
        }

        function toggleUserProfileDropdown() {
            const dropdown = document.getElementById('userProfileDropdown');
            const notificationDropdown = document.getElementById('notificationDropdown');
            
            if (dropdown) {
                dropdown.classList.toggle('hidden');
                if (notificationDropdown && !notificationDropdown.classList.contains('hidden')) {
                    notificationDropdown.classList.add('hidden');
                }
            }
        }

        // Real-time clock functionality
        function updateClock() {
            const now = new Date();
            const timeString = now.toLocaleTimeString('en-US', { 
                hour12: true, 
                hour: 'numeric', 
                minute: '2-digit',
                second: '2-digit'
            });
            const timeElement = document.getElementById('currentTime');
            if (timeElement) {
                timeElement.textContent = timeString;
            }
        }

        // Search functionality
        function initializeSearch() {
            const searchInput = document.querySelector('input[placeholder*="Search"]');
            const searchSuggestions = document.getElementById('searchSuggestions');
            
            if (searchInput && searchSuggestions) {
                let searchTimeout;
                
                searchInput.addEventListener('focus', function() {
                    searchSuggestions.classList.remove('hidden');
                });
                
                searchInput.addEventListener('blur', function() {
                    // Delay hiding to allow clicking on suggestions
                    setTimeout(() => {
                        searchSuggestions.classList.add('hidden');
                    }, 200);
                });
                
                searchInput.addEventListener('input', function() {
                    clearTimeout(searchTimeout);
                    searchTimeout = setTimeout(() => {
                        // Simulate search functionality
                        if (this.value.length > 0) {
                            searchSuggestions.classList.remove('hidden');
                        }
                    }, 300);
                });
                
                // Keyboard shortcuts
                searchInput.addEventListener('keydown', function(e) {
                    if (e.key === 'Escape') {
                        searchSuggestions.classList.add('hidden');
                        this.blur();
                    }
                });
            }
        }

        // Enhanced notification functionality
        function initializeNotifications() {
            const notificationBtn = document.getElementById('notificationBtn');
            const notificationDropdown = document.getElementById('notificationDropdown');
            
            if (notificationBtn && notificationDropdown) {
                notificationBtn.addEventListener('click', function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    notificationDropdown.classList.toggle('hidden');
                    
                    // Close other dropdowns
                    const userProfileDropdown = document.getElementById('userProfileDropdown');
                    if (userProfileDropdown && !userProfileDropdown.classList.contains('hidden')) {
                        userProfileDropdown.classList.add('hidden');
                    }
                });
            }
        }

        // Enhanced user profile functionality
        function initializeUserProfile() {
            const userProfileBtn = document.getElementById('userProfileBtn');
            const userProfileDropdown = document.getElementById('userProfileDropdown');
            
            if (userProfileBtn && userProfileDropdown) {
                userProfileBtn.addEventListener('click', function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    userProfileDropdown.classList.toggle('hidden');
                    
                    // Close other dropdowns
                    const notificationDropdown = document.getElementById('notificationDropdown');
                    if (notificationDropdown && !notificationDropdown.classList.contains('hidden')) {
                        notificationDropdown.classList.add('hidden');
                    }
                });
            }
        }

        // Initialize on DOM load
        document.addEventListener('DOMContentLoaded', function() {
            const sidebar = document.getElementById('sidebar');
            const sidebarToggle = document.getElementById('sidebarToggle');
            const isCollapsed = localStorage.getItem('sidebarCollapsed') === 'true';
            
            if (isCollapsed) {
                sidebar.classList.add('-translate-x-full');
            }

            // Set initial icon state
            if (sidebarToggle) {
                const icon = sidebarToggle.querySelector('i');
                if (icon) {
                    if (isCollapsed) {
                        icon.className = 'fas fa-bars text-lg relative z-10 group-hover:text-blue-600 transition-colors duration-200';
                    } else {
                        icon.className = 'fas fa-times text-lg relative z-10 group-hover:text-blue-600 transition-colors duration-200';
                    }
                }
            }

            // Add event listeners for both toggle buttons
            const toggleButton = document.querySelector('button[onclick="toggleSidebar()"]');
            if (toggleButton) {
                toggleButton.addEventListener('click', function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    toggleSidebar();
                });
            }

            // Add event listener for the new header toggle button (backup to onclick)
            if (sidebarToggle) {
                sidebarToggle.addEventListener('click', function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    toggleSidebar();
                });
            }

            // Keyboard shortcut (Ctrl/Cmd + B) to toggle sidebar
            document.addEventListener('keydown', function(e) {
                if ((e.ctrlKey || e.metaKey) && e.key === 'b') {
                    e.preventDefault();
                    toggleSidebar();
                }
                
                // Search shortcut (Ctrl/Cmd + K)
                if ((e.ctrlKey || e.metaKey) && e.key === 'k') {
                    e.preventDefault();
                    const searchInput = document.querySelector('input[placeholder*="Search"]');
                    if (searchInput) {
                        searchInput.focus();
                    }
                }
            });

            // Close user menu when clicking outside
            document.addEventListener('click', function(e) {
                const userMenu = document.getElementById('userMenu');
                const userButton = document.querySelector('.sidebar-user');
                
                if (userMenu && userButton && !userButton.contains(e.target)) {
                    userMenu.classList.add('hidden');
                }
            });

            // Initialize enhanced features
            initializeSearch();
            initializeNotifications();
            initializeUserProfile();

            // Close dropdowns when clicking outside
            document.addEventListener('click', function(e) {
                const notificationDropdown = document.getElementById('notificationDropdown');
                const userProfileDropdown = document.getElementById('userProfileDropdown');
                const searchSuggestions = document.getElementById('searchSuggestions');
                const notificationBtn = document.getElementById('notificationBtn');
                const userProfileBtn = document.getElementById('userProfileBtn');
                const searchInput = document.querySelector('input[placeholder*="Search"]');
                
                if (notificationDropdown && notificationBtn && !notificationBtn.contains(e.target) && !notificationDropdown.contains(e.target)) {
                    notificationDropdown.classList.add('hidden');
                }
                
                if (userProfileDropdown && userProfileBtn && !userProfileBtn.contains(e.target) && !userProfileDropdown.contains(e.target)) {
                    userProfileDropdown.classList.add('hidden');
                }
                
                if (searchSuggestions && searchInput && !searchInput.contains(e.target) && !searchSuggestions.contains(e.target)) {
                    searchSuggestions.classList.add('hidden');
                }
            });

            // Start real-time clock
            updateClock();
            setInterval(updateClock, 1000);

            // Auto-hide flash messages
            setTimeout(function() {
                const alerts = document.querySelectorAll('.flash-message');
                alerts.forEach(function(alert) {
                    alert.style.transition = 'all 0.3s ease-out';
                    alert.style.transform = 'translateX(100%)';
                    alert.style.opacity = '0';
                    setTimeout(function() {
                        alert.remove();
                    }, 300);
                });
            }, 5000);

            // Add smooth scrolling for better UX
            document.documentElement.style.scrollBehavior = 'smooth';
        });
    </script>
</body>
</html>