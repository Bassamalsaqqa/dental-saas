<?= $this->extend('layouts/main_auth') ?>

<?php
// Function to convert Universal numbering to FDI numbering
function getFDINumber($universalNumber) {
    $fdiMap = [
        // Upper Right (1-8) -> FDI 11-18
        1 => '11', 2 => '12', 3 => '13', 4 => '14', 5 => '15', 6 => '16', 7 => '17', 8 => '18',
        // Upper Left (9-16) -> FDI 21-28  
        9 => '21', 10 => '22', 11 => '23', 12 => '24', 13 => '25', 14 => '26', 15 => '27', 16 => '28',
        // Lower Left (17-24) -> FDI 31-38
        17 => '31', 18 => '32', 19 => '33', 20 => '34', 21 => '35', 22 => '36', 23 => '37', 24 => '38',
        // Lower Right (25-32) -> FDI 41-48
        25 => '41', 26 => '42', 27 => '43', 28 => '44', 29 => '45', 30 => '46', 31 => '47', 32 => '48'
    ];
    
    return $fdiMap[$universalNumber] ?? $universalNumber;
}

// Function to get condition styling classes
function getConditionClass($condition) {
    switch($condition) {
        case 'healthy':
            return 'bg-green-100 text-green-800';
        case 'cavity':
            return 'bg-yellow-100 text-yellow-800';
        case 'filling':
            return 'bg-blue-100 text-blue-800';
        case 'crown':
            return 'bg-purple-100 text-purple-800';
        case 'root_canal':
            return 'bg-pink-100 text-pink-800';
        case 'extracted':
            return 'bg-red-100 text-red-800';
        case 'implant':
            return 'bg-indigo-100 text-indigo-800';
        case 'bridge':
            return 'bg-teal-100 text-teal-800';
        case 'partial_denture':
            return 'bg-orange-100 text-orange-800';
        case 'full_denture':
            return 'bg-amber-100 text-amber-800';
        case 'other':
            return 'bg-gray-100 text-gray-800';
        default:
            return 'bg-green-100 text-green-800';
    }
}

// Function to get status styling classes
function getStatusClass($status) {
    switch($status) {
        case 'pending':
            return 'bg-yellow-100 text-yellow-800';
        case 'in_progress':
            return 'bg-orange-100 text-orange-800';
        case 'completed':
            return 'bg-green-100 text-green-800';
        case 'needs_attention':
            return 'bg-red-100 text-red-800';
        default:
            return 'bg-green-100 text-green-800';
    }
}

// Function to get status display text
function getStatusText($status) {
    switch($status) {
        case 'pending':
            return '🟡 Pending';
        case 'in_progress':
            return '🟠 In Progress';
        case 'completed':
            return '🟢 Completed';
        case 'needs_attention':
            return '🔴 Needs Attention';
        default:
            return '🟢 Completed';
    }
}
?>

<?= $this->section('content') ?>
<div class="min-h-screen bg-gradient-to-br from-slate-50 via-blue-50 to-indigo-50">
    <!-- Enhanced Page Header with Glassmorphism -->
    <div class="backdrop-blur-md bg-white/80 border-b border-white/20 shadow-sm">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex flex-col lg:flex-row lg:justify-between lg:items-center py-6 space-y-4 lg:space-y-0">
                <div class="flex items-center space-x-4">
                    <div class="w-12 h-12 bg-gradient-to-br from-blue-600 to-indigo-600 rounded-xl flex items-center justify-center shadow-lg">
                        <i class="fas fa-tooth text-white text-xl"></i>
                    </div>
                    <div>
                        <h1 class="text-3xl font-bold bg-gradient-to-r from-gray-800 to-gray-600 bg-clip-text text-transparent">
                            Interactive Odontogram
                        </h1>
                        <p class="text-gray-600 mt-1 flex items-center">
                            <i class="fas fa-user-circle mr-2 text-blue-500"></i>
                            <?= $patient['first_name'] . ' ' . $patient['last_name'] ?> 
                            <span class="ml-2 px-2 py-1 bg-blue-100 text-blue-800 text-xs font-medium rounded-full">
                                <?= $patient['patient_id'] ?>
                            </span>
                        </p>
                    </div>
                </div>
                <div class="flex flex-wrap gap-3">
                    <button onclick="printOdontogram()" class="group relative inline-flex items-center px-4 py-2 bg-white/70 hover:bg-white border border-gray-200 rounded-lg text-sm font-medium text-gray-700 hover:text-gray-900 shadow-sm hover:shadow-md transition-all duration-200">
                        <i class="fas fa-print mr-2 group-hover:animate-pulse"></i>Print
                    </button>
                    <a href="<?= base_url('patient/' . $patient['id']) ?>" class="group relative inline-flex items-center px-4 py-2 bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 text-white rounded-lg text-sm font-medium shadow-lg hover:shadow-xl transition-all duration-200 transform hover:scale-105">
                        <i class="fas fa-arrow-left mr-2 group-hover:-translate-x-1 transition-transform"></i>Back to Patient
                    </a>
                </div>
            </div>
        </div>
    </div>


    <!-- Enhanced Interactive Odontogram with Chat-Style Interface -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pb-8">
        <div class="bg-white/70 backdrop-blur-sm rounded-2xl shadow-xl border border-white/20 overflow-hidden">
            <!-- Enhanced Header with Chat-Style Design -->
            <div class="bg-gradient-to-r from-blue-600 via-indigo-600 to-purple-600 text-white p-6 relative overflow-hidden">
                <div class="absolute inset-0 bg-black/10"></div>
                <div class="relative z-10">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center space-x-4">
                            <div class="w-12 h-12 bg-white/20 rounded-xl flex items-center justify-center backdrop-blur-sm">
                                <i class="fas fa-tooth text-white text-xl"></i>
                            </div>
                            <div>
                                <h3 class="text-2xl font-bold mb-1">Interactive Dental Chart</h3>
                                <p class="text-blue-100 flex items-center">
                                    <i class="fas fa-mouse-pointer mr-2"></i>
                                    Click on any tooth to view or update its condition
                                </p>
                            </div>
                        </div>
                        <div class="hidden md:flex items-center space-x-2 text-blue-100">
                            <div class="w-2 h-2 bg-green-400 rounded-full animate-pulse"></div>
                            <span class="text-sm font-medium">Live Updates</span>
                        </div>
                    </div>
                </div>
                <!-- Decorative Elements --> 
                <div class="absolute top-0 right-0 w-32 h-32 bg-white/5 rounded-full -translate-y-16 translate-x-16"></div>
                <div class="absolute bottom-0 left-0 w-24 h-24 bg-white/5 rounded-full translate-y-12 -translate-x-12"></div>
            </div>
        
            <!-- Enhanced Interactive Legend -->
            <div class="p-6 bg-gradient-to-r from-gray-50 to-blue-50/30 border-b border-gray-200/50">
                <div class="flex items-center justify-between mb-6">
                    <h4 class="text-lg font-bold text-gray-800 flex items-center">
                        <i class="fas fa-palette mr-3 text-blue-600"></i>
                        Condition Legend
                    </h4>
                    <div class="flex items-center space-x-4">
                        <!-- Numbering System Toggle -->
                        <div class="flex items-center space-x-3">
                            <span class="text-sm font-medium text-gray-700">Numbering:</span>
                            <div class="flex bg-gray-100 rounded-lg p-1">
                                <button id="universalBtn" onclick="switchNumberingSystem('universal')" 
                                        class="px-3 py-1 text-xs font-medium rounded-md transition-all duration-200 bg-white text-blue-600 shadow-sm">
                                    Universal (1-32)
                                </button>
                                <button id="fdiBtn" onclick="switchNumberingSystem('fdi')" 
                                        class="px-3 py-1 text-xs font-medium rounded-md transition-all duration-200 text-gray-600 hover:text-blue-600">
                                    FDI (Two-digit)
                                </button>
                            </div>
                        </div>
                        <div class="flex items-center space-x-2 text-sm text-gray-600">
                            <i class="fas fa-info-circle"></i>
                            <span>Hover over teeth to see conditions</span>
                        </div>
                    </div>
                </div>
                <div class="grid grid-cols-6 gap-4">
                    <!-- Healthy -->
                    <div class="group flex items-center space-x-3 p-4 bg-white/80 backdrop-blur-sm rounded-xl shadow-sm hover:shadow-lg transition-all duration-300 cursor-pointer border border-white/50 hover:border-green-200/50">
                        <div class="w-8 h-8 bg-gradient-to-br from-green-100 to-green-200 border-2 border-green-400 rounded-full flex items-center justify-center group-hover:scale-110 transition-transform">
                            <i class="fas fa-check text-green-600 text-sm"></i>
                        </div>
                        <div>
                            <span class="text-sm font-semibold text-gray-800">Healthy</span>
                            <div class="text-xs text-gray-500">No issues</div>
                        </div>
                    </div>
                    
                    <!-- Cavity -->
                    <div class="group flex items-center space-x-3 p-4 bg-white/80 backdrop-blur-sm rounded-xl shadow-sm hover:shadow-lg transition-all duration-300 cursor-pointer border border-white/50 hover:border-yellow-200/50">
                        <div class="w-8 h-8 bg-gradient-to-br from-yellow-100 to-yellow-200 border-2 border-yellow-400 rounded-full flex items-center justify-center group-hover:scale-110 transition-transform">
                            <i class="fas fa-exclamation text-yellow-600 text-sm"></i>
                        </div>
                        <div>
                            <span class="text-sm font-semibold text-gray-800">Cavity</span>
                            <div class="text-xs text-gray-500">Needs filling</div>
                        </div>
                    </div>
                    
                    <!-- Filling -->
                    <div class="group flex items-center space-x-3 p-4 bg-white/80 backdrop-blur-sm rounded-xl shadow-sm hover:shadow-lg transition-all duration-300 cursor-pointer border border-white/50 hover:border-blue-200/50">
                        <div class="w-8 h-8 bg-gradient-to-br from-blue-100 to-blue-200 border-2 border-blue-400 rounded-full flex items-center justify-center group-hover:scale-110 transition-transform">
                            <i class="fas fa-tools text-blue-600 text-sm"></i>
                        </div>
                        <div>
                            <span class="text-sm font-semibold text-gray-800">Filling</span>
                            <div class="text-xs text-gray-500">Restored</div>
                        </div>
                    </div>
                    
                    <!-- Crown -->
                    <div class="group flex items-center space-x-3 p-4 bg-white/80 backdrop-blur-sm rounded-xl shadow-sm hover:shadow-lg transition-all duration-300 cursor-pointer border border-white/50 hover:border-purple-200/50">
                        <div class="w-8 h-8 bg-gradient-to-br from-purple-100 to-purple-200 border-2 border-purple-400 rounded-full flex items-center justify-center group-hover:scale-110 transition-transform">
                            <i class="fas fa-crown text-purple-600 text-sm"></i>
                        </div>
                        <div>
                            <span class="text-sm font-semibold text-gray-800">Crown</span>
                            <div class="text-xs text-gray-500">Capped</div>
                        </div>
                    </div>
                    
                    <!-- Root Canal -->
                    <div class="group flex items-center space-x-3 p-4 bg-white/80 backdrop-blur-sm rounded-xl shadow-sm hover:shadow-lg transition-all duration-300 cursor-pointer border border-white/50 hover:border-pink-200/50">
                        <div class="w-8 h-8 bg-gradient-to-br from-pink-100 to-pink-200 border-2 border-pink-400 rounded-full flex items-center justify-center group-hover:scale-110 transition-transform">
                            <i class="fas fa-tooth text-pink-600 text-sm"></i>
                        </div>
                        <div>
                            <span class="text-sm font-semibold text-gray-800">Root Canal</span>
                            <div class="text-xs text-gray-500">Endodontic</div>
                        </div>
                    </div>
                    
                    <!-- Extracted -->
                    <div class="group flex items-center space-x-3 p-4 bg-white/80 backdrop-blur-sm rounded-xl shadow-sm hover:shadow-lg transition-all duration-300 cursor-pointer border border-white/50 hover:border-red-200/50">
                        <div class="w-8 h-8 bg-gradient-to-br from-red-100 to-red-200 border-2 border-red-400 rounded-full flex items-center justify-center group-hover:scale-110 transition-transform">
                            <i class="fas fa-times text-red-600 text-sm"></i>
                        </div>
                        <div>
                            <span class="text-sm font-semibold text-gray-800">Extracted</span>
                            <div class="text-xs text-gray-500">Removed</div>
                        </div>
                    </div>
                    
                    <!-- Implant -->
                    <div class="group flex items-center space-x-3 p-4 bg-white/80 backdrop-blur-sm rounded-xl shadow-sm hover:shadow-lg transition-all duration-300 cursor-pointer border border-white/50 hover:border-indigo-200/50">
                        <div class="w-8 h-8 bg-gradient-to-br from-indigo-100 to-indigo-200 border-2 border-indigo-400 rounded-full flex items-center justify-center group-hover:scale-110 transition-transform">
                            <i class="fas fa-screwdriver text-indigo-600 text-sm"></i>
                        </div>
                        <div>
                            <span class="text-sm font-semibold text-gray-800">Implant</span>
                            <div class="text-xs text-gray-500">Titanium</div>
                        </div>
                    </div>
                    
                    <!-- Bridge -->
                    <div class="group flex items-center space-x-3 p-4 bg-white/80 backdrop-blur-sm rounded-xl shadow-sm hover:shadow-lg transition-all duration-300 cursor-pointer border border-white/50 hover:border-teal-200/50">
                        <div class="w-8 h-8 bg-gradient-to-br from-teal-100 to-teal-200 border-2 border-teal-400 rounded-full flex items-center justify-center group-hover:scale-110 transition-transform">
                            <i class="fas fa-link text-teal-600 text-sm"></i>
                        </div>
                        <div>
                            <span class="text-sm font-semibold text-gray-800">Bridge</span>
                            <div class="text-xs text-gray-500">Connected</div>
                        </div>
                    </div>
                    
                    <!-- Partial Denture -->
                    <div class="group flex items-center space-x-3 p-4 bg-white/80 backdrop-blur-sm rounded-xl shadow-sm hover:shadow-lg transition-all duration-300 cursor-pointer border border-white/50 hover:border-orange-200/50">
                        <div class="w-8 h-8 bg-gradient-to-br from-orange-100 to-orange-200 border-2 border-orange-400 rounded-full flex items-center justify-center group-hover:scale-110 transition-transform">
                            <i class="fas fa-teeth text-orange-600 text-sm"></i>
                        </div>
                        <div>
                            <span class="text-sm font-semibold text-gray-800">Partial Denture</span>
                            <div class="text-xs text-gray-500">Removable</div>
                        </div>
                    </div>
                    
                    <!-- Full Denture -->
                    <div class="group flex items-center space-x-3 p-4 bg-white/80 backdrop-blur-sm rounded-xl shadow-sm hover:shadow-lg transition-all duration-300 cursor-pointer border border-white/50 hover:border-amber-200/50">
                        <div class="w-8 h-8 bg-gradient-to-br from-amber-100 to-amber-200 border-2 border-amber-400 rounded-full flex items-center justify-center group-hover:scale-110 transition-transform">
                            <i class="fas fa-teeth-open text-amber-600 text-sm"></i>
                        </div>
                        <div>
                            <span class="text-sm font-semibold text-gray-800">Full Denture</span>
                            <div class="text-xs text-gray-500">Complete</div>
                        </div>
                    </div>
                    
                    <!-- Other -->
                    <div class="group flex items-center space-x-3 p-4 bg-white/80 backdrop-blur-sm rounded-xl shadow-sm hover:shadow-lg transition-all duration-300 cursor-pointer border border-white/50 hover:border-gray-200/50">
                        <div class="w-8 h-8 bg-gradient-to-br from-gray-100 to-gray-200 border-2 border-gray-400 rounded-full flex items-center justify-center group-hover:scale-110 transition-transform">
                            <i class="fas fa-question text-gray-600 text-sm"></i>
                        </div>
                        <div>
                            <span class="text-sm font-semibold text-gray-800">Other</span>
                            <div class="text-xs text-gray-500">Special</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Realistic Jaw Interface -->
            <div class="p-8 bg-gradient-to-br from-gray-50 via-white to-blue-50/30">
                <div class="max-w-6xl mx-auto">
                    <!-- Jaw Interface Container -->
                    <div class="relative bg-gradient-to-br from-white to-gray-50/50 backdrop-blur-sm rounded-3xl p-8 shadow-2xl border border-white/30 overflow-hidden">
                        <!-- Jaw Background -->
                        <div class="absolute inset-0 bg-gradient-to-br from-pink-50/30 via-white to-blue-50/20 rounded-3xl"></div>

                        <!-- Upper Jaw -->
                        <div class="relative mb-16">
                            <!-- Upper Jaw Label -->
                            <div class="text-center mb-6">
                                <h3 class="text-2xl font-bold text-gray-800 flex items-center justify-center">
                                    <i class="fas fa-arrow-up text-blue-600 mr-3"></i>
                                    Upper Jaw (Maxilla)
                                    <i class="fas fa-arrow-up text-blue-600 ml-3"></i>
                                </h3>
                                <div class="text-sm text-gray-600 mt-2">Right → Left (1-16)</div>
                            </div>
                            
                            <!-- Upper Jaw Teeth -->
                            <div class="flex justify-center items-center w-full max-w-4xl mx-auto">
                                <!-- Upper Right Quadrant (1-8) -->
                                <div class="flex space-x-1 flex-1 justify-end">
                                    <?php for ($i = 8; $i >= 1; $i--): ?>
                                        <div class="group relative transition-all duration-300 hover:-translate-y-2" data-tooth="<?= $i ?>" data-quadrant="upper-right">
                                            <div class="w-12 h-16 bg-gradient-to-br from-white to-gray-50 border-2 border-gray-300 rounded-t-2xl rounded-b-lg flex flex-col items-center justify-center cursor-pointer transition-all duration-300 relative overflow-hidden shadow-lg backdrop-blur-sm hover:scale-110 hover:shadow-2xl hover:border-blue-500 z-10 group-hover:z-20" 
                                                 data-tooth="<?= $i ?>" 
                                                 data-quadrant="upper-right" 
                                                 onclick="openToothModal(<?= $i ?>)"
                                                 title="Tooth <?= $i ?> - Click to edit">
                                                <div class="text-xs font-bold text-gray-700 z-10 relative">
                                                    <span class="universal-number"><?= $i ?></span>
                                                    <span class="fdi-number hidden"><?= getFDINumber($i) ?></span>
                                                </div>
                                                <div class="absolute top-1 right-1 w-2 h-2 rounded-full bg-green-500 opacity-0 transition-all duration-300 shadow-sm group-hover:opacity-100 group-hover:scale-125"></div>
                                                <div class="absolute -bottom-16 left-1/2 transform -translate-x-1/2 bg-black/95 text-white px-3 py-2 rounded-lg text-xs max-w-xs opacity-0 invisible transition-all duration-300 z-50 pointer-events-none group-hover:opacity-100 group-hover:visible group-hover:-translate-y-1" style="min-width: 200px;">
                                                    <div class="universal-tooltip">
                                                        <div class="font-semibold text-yellow-300 mb-1">Tooth <?= $i ?></div>
                                                        <div class="space-y-1" id="tooth-<?= $i ?>-details">
                                                            <div class="flex items-center">
                                                                <span class="text-gray-400 w-16">Condition:</span>
                                                                <span class="text-white" id="tooth-<?= $i ?>-condition">Healthy</span>
                                                            </div>
                                                            <div class="flex items-start" id="tooth-<?= $i ?>-description-row" style="display: none;">
                                                                <span class="text-gray-400 w-16">Description:</span>
                                                                <span class="text-gray-300 text-xs" id="tooth-<?= $i ?>-description">No issues</span>
                                                            </div>
                                                            <div class="flex items-start" id="tooth-<?= $i ?>-treatment-row" style="display: none;">
                                                                <span class="text-gray-400 w-16">Treatment:</span>
                                                                <span class="text-gray-300 text-xs" id="tooth-<?= $i ?>-treatment">None</span>
                                                            </div>
                                                            <div class="flex items-center" id="tooth-<?= $i ?>-status-row" style="display: none;">
                                                                <span class="text-gray-400 w-16">Status:</span>
                                                                <span class="text-green-400" id="tooth-<?= $i ?>-status">Completed</span>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="fdi-tooltip hidden">
                                                        <div class="font-semibold text-yellow-300 mb-1">Tooth <?= getFDINumber($i) ?></div>
                                                        <div class="space-y-1" id="tooth-<?= $i ?>-details-fdi">
                                                            <div class="flex items-center">
                                                                <span class="text-gray-400 w-16">Condition:</span>
                                                                <span class="text-white" id="tooth-<?= $i ?>-condition-fdi">Healthy</span>
                                                            </div>
                                                            <div class="flex items-start" id="tooth-<?= $i ?>-description-row-fdi" style="display: none;">
                                                                <span class="text-gray-400 w-16">Description:</span>
                                                                <span class="text-gray-300 text-xs" id="tooth-<?= $i ?>-description-fdi">No issues</span>
                                                            </div>
                                                            <div class="flex items-start" id="tooth-<?= $i ?>-treatment-row-fdi" style="display: none;">
                                                                <span class="text-gray-400 w-16">Treatment:</span>
                                                                <span class="text-gray-300 text-xs" id="tooth-<?= $i ?>-treatment-fdi">None</span>
                                                            </div>
                                                            <div class="flex items-center" id="tooth-<?= $i ?>-status-row-fdi" style="display: none;">
                                                                <span class="text-gray-400 w-16">Status:</span>
                                                                <span class="text-green-400" id="tooth-<?= $i ?>-status-fdi">Completed</span>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endfor; ?>
                                </div>
                                
                                <!-- Center Gap -->
                                <div class="w-4"></div>
                                
                                <!-- Upper Left Quadrant (9-16) -->
                                <div class="flex space-x-1 flex-1 justify-start">
                                    <?php for ($i = 9; $i <= 16; $i++): ?>
                                        <div class="group relative transition-all duration-300 hover:-translate-y-2" data-tooth="<?= $i ?>" data-quadrant="upper-left">
                                            <div class="w-12 h-16 bg-gradient-to-br from-white to-gray-50 border-2 border-gray-300 rounded-t-2xl rounded-b-lg flex flex-col items-center justify-center cursor-pointer transition-all duration-300 relative overflow-hidden shadow-lg backdrop-blur-sm hover:scale-110 hover:shadow-2xl hover:border-blue-500 z-10 group-hover:z-20" 
                                                 data-tooth="<?= $i ?>" 
                                                 data-quadrant="upper-left" 
                                                 onclick="openToothModal(<?= $i ?>)"
                                                 title="Tooth <?= $i ?> - Click to edit">
                                                <div class="text-xs font-bold text-gray-700 z-10 relative">
                                                    <span class="universal-number"><?= $i ?></span>
                                                    <span class="fdi-number hidden"><?= getFDINumber($i) ?></span>
                                                </div>
                                                <div class="absolute top-1 right-1 w-2 h-2 rounded-full bg-green-500 opacity-0 transition-all duration-300 shadow-sm group-hover:opacity-100 group-hover:scale-125"></div>
                                                <div class="absolute -bottom-16 left-1/2 transform -translate-x-1/2 bg-black/95 text-white px-3 py-2 rounded-lg text-xs max-w-xs opacity-0 invisible transition-all duration-300 z-50 pointer-events-none group-hover:opacity-100 group-hover:visible group-hover:-translate-y-1" style="min-width: 200px;">
                                                    <div class="universal-tooltip">
                                                        <div class="font-semibold text-yellow-300 mb-1">Tooth <?= $i ?></div>
                                                        <div class="space-y-1" id="tooth-<?= $i ?>-details">
                                                            <div class="flex items-center">
                                                                <span class="text-gray-400 w-16">Condition:</span>
                                                                <span class="text-white" id="tooth-<?= $i ?>-condition">Healthy</span>
                                                            </div>
                                                            <div class="flex items-start" id="tooth-<?= $i ?>-description-row" style="display: none;">
                                                                <span class="text-gray-400 w-16">Description:</span>
                                                                <span class="text-gray-300 text-xs" id="tooth-<?= $i ?>-description">No issues</span>
                                                            </div>
                                                            <div class="flex items-start" id="tooth-<?= $i ?>-treatment-row" style="display: none;">
                                                                <span class="text-gray-400 w-16">Treatment:</span>
                                                                <span class="text-gray-300 text-xs" id="tooth-<?= $i ?>-treatment">None</span>
                                                            </div>
                                                            <div class="flex items-center" id="tooth-<?= $i ?>-status-row" style="display: none;">
                                                                <span class="text-gray-400 w-16">Status:</span>
                                                                <span class="text-green-400" id="tooth-<?= $i ?>-status">Completed</span>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="fdi-tooltip hidden">
                                                        <div class="font-semibold text-yellow-300 mb-1">Tooth <?= getFDINumber($i) ?></div>
                                                        <div class="space-y-1" id="tooth-<?= $i ?>-details-fdi">
                                                            <div class="flex items-center">
                                                                <span class="text-gray-400 w-16">Condition:</span>
                                                                <span class="text-white" id="tooth-<?= $i ?>-condition-fdi">Healthy</span>
                                                            </div>
                                                            <div class="flex items-start" id="tooth-<?= $i ?>-description-row-fdi" style="display: none;">
                                                                <span class="text-gray-400 w-16">Description:</span>
                                                                <span class="text-gray-300 text-xs" id="tooth-<?= $i ?>-description-fdi">No issues</span>
                                                            </div>
                                                            <div class="flex items-start" id="tooth-<?= $i ?>-treatment-row-fdi" style="display: none;">
                                                                <span class="text-gray-400 w-16">Treatment:</span>
                                                                <span class="text-gray-300 text-xs" id="tooth-<?= $i ?>-treatment-fdi">None</span>
                                                            </div>
                                                            <div class="flex items-center" id="tooth-<?= $i ?>-status-row-fdi" style="display: none;">
                                                                <span class="text-gray-400 w-16">Status:</span>
                                                                <span class="text-green-400" id="tooth-<?= $i ?>-status-fdi">Completed</span>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endfor; ?>
                                </div>
                            </div>
                        </div>

                       

                        <!-- Lower Jaw -->
                        <div class="relative">
                            <!-- Lower Jaw Teeth -->
                            <div class="flex justify-center items-center w-full max-w-4xl mx-auto">
                                <!-- Lower Left Quadrant (17-24) -->
                                <div class="flex space-x-1 flex-1 justify-end">
                                    <?php for ($i = 24; $i >= 17; $i--): ?>
                                        <div class="group relative transition-all duration-300 hover:-translate-y-2" data-tooth="<?= $i ?>" data-quadrant="lower-left">
                                            <div class="w-12 h-16 bg-gradient-to-br from-white to-gray-50 border-2 border-gray-300 rounded-b-2xl rounded-t-lg flex flex-col items-center justify-center cursor-pointer transition-all duration-300 relative overflow-hidden shadow-lg backdrop-blur-sm hover:scale-110 hover:shadow-2xl hover:border-blue-500 z-10 group-hover:z-20" 
                                                 data-tooth="<?= $i ?>" 
                                                 data-quadrant="lower-left" 
                                                 onclick="openToothModal(<?= $i ?>)"
                                                 title="Tooth <?= $i ?> - Click to edit">
                                                <div class="text-xs font-bold text-gray-700 z-10 relative">
                                                    <span class="universal-number"><?= $i ?></span>
                                                    <span class="fdi-number hidden"><?= getFDINumber($i) ?></span>
                                                </div>
                                                <div class="absolute top-1 right-1 w-2 h-2 rounded-full bg-green-500 opacity-0 transition-all duration-300 shadow-sm group-hover:opacity-100 group-hover:scale-125"></div>
                                                <div class="absolute -bottom-16 left-1/2 transform -translate-x-1/2 bg-black/95 text-white px-3 py-2 rounded-lg text-xs max-w-xs opacity-0 invisible transition-all duration-300 z-50 pointer-events-none group-hover:opacity-100 group-hover:visible group-hover:-translate-y-1" style="min-width: 200px;">
                                                    <div class="universal-tooltip">
                                                        <div class="font-semibold text-yellow-300 mb-1">Tooth <?= $i ?></div>
                                                        <div class="space-y-1" id="tooth-<?= $i ?>-details">
                                                            <div class="flex items-center">
                                                                <span class="text-gray-400 w-16">Condition:</span>
                                                                <span class="text-white" id="tooth-<?= $i ?>-condition">Healthy</span>
                                                            </div>
                                                            <div class="flex items-start" id="tooth-<?= $i ?>-description-row" style="display: none;">
                                                                <span class="text-gray-400 w-16">Description:</span>
                                                                <span class="text-gray-300 text-xs" id="tooth-<?= $i ?>-description">No issues</span>
                                                            </div>
                                                            <div class="flex items-start" id="tooth-<?= $i ?>-treatment-row" style="display: none;">
                                                                <span class="text-gray-400 w-16">Treatment:</span>
                                                                <span class="text-gray-300 text-xs" id="tooth-<?= $i ?>-treatment">None</span>
                                                            </div>
                                                            <div class="flex items-center" id="tooth-<?= $i ?>-status-row" style="display: none;">
                                                                <span class="text-gray-400 w-16">Status:</span>
                                                                <span class="text-green-400" id="tooth-<?= $i ?>-status">Completed</span>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="fdi-tooltip hidden">
                                                        <div class="font-semibold text-yellow-300 mb-1">Tooth <?= getFDINumber($i) ?></div>
                                                        <div class="space-y-1" id="tooth-<?= $i ?>-details-fdi">
                                                            <div class="flex items-center">
                                                                <span class="text-gray-400 w-16">Condition:</span>
                                                                <span class="text-white" id="tooth-<?= $i ?>-condition-fdi">Healthy</span>
                                                            </div>
                                                            <div class="flex items-start" id="tooth-<?= $i ?>-description-row-fdi" style="display: none;">
                                                                <span class="text-gray-400 w-16">Description:</span>
                                                                <span class="text-gray-300 text-xs" id="tooth-<?= $i ?>-description-fdi">No issues</span>
                                                            </div>
                                                            <div class="flex items-start" id="tooth-<?= $i ?>-treatment-row-fdi" style="display: none;">
                                                                <span class="text-gray-400 w-16">Treatment:</span>
                                                                <span class="text-gray-300 text-xs" id="tooth-<?= $i ?>-treatment-fdi">None</span>
                                                            </div>
                                                            <div class="flex items-center" id="tooth-<?= $i ?>-status-row-fdi" style="display: none;">
                                                                <span class="text-gray-400 w-16">Status:</span>
                                                                <span class="text-green-400" id="tooth-<?= $i ?>-status-fdi">Completed</span>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endfor; ?>
                                </div>
                                
                                <!-- Center Gap -->
                                <div class="w-4"></div>
                                
                                <!-- Lower Right Quadrant (25-32) -->
                                <div class="flex space-x-1 flex-1 justify-start">
                                    <?php for ($i = 25; $i <= 32; $i++): ?>
                                        <div class="group relative transition-all duration-300 hover:-translate-y-2" data-tooth="<?= $i ?>" data-quadrant="lower-right">
                                            <div class="w-12 h-16 bg-gradient-to-br from-white to-gray-50 border-2 border-gray-300 rounded-b-2xl rounded-t-lg flex flex-col items-center justify-center cursor-pointer transition-all duration-300 relative overflow-hidden shadow-lg backdrop-blur-sm hover:scale-110 hover:shadow-2xl hover:border-blue-500 z-10 group-hover:z-20" 
                                                 data-tooth="<?= $i ?>" 
                                                 data-quadrant="lower-right" 
                                                 onclick="openToothModal(<?= $i ?>)"
                                                 title="Tooth <?= $i ?> - Click to edit">
                                                <div class="text-xs font-bold text-gray-700 z-10 relative">
                                                    <span class="universal-number"><?= $i ?></span>
                                                    <span class="fdi-number hidden"><?= getFDINumber($i) ?></span>
                                                </div>
                                                <div class="absolute top-1 right-1 w-2 h-2 rounded-full bg-green-500 opacity-0 transition-all duration-300 shadow-sm group-hover:opacity-100 group-hover:scale-125"></div>
                                                <div class="absolute -bottom-16 left-1/2 transform -translate-x-1/2 bg-black/95 text-white px-3 py-2 rounded-lg text-xs max-w-xs opacity-0 invisible transition-all duration-300 z-50 pointer-events-none group-hover:opacity-100 group-hover:visible group-hover:-translate-y-1" style="min-width: 200px;">
                                                    <div class="universal-tooltip">
                                                        <div class="font-semibold text-yellow-300 mb-1">Tooth <?= $i ?></div>
                                                        <div class="space-y-1" id="tooth-<?= $i ?>-details">
                                                            <div class="flex items-center">
                                                                <span class="text-gray-400 w-16">Condition:</span>
                                                                <span class="text-white" id="tooth-<?= $i ?>-condition">Healthy</span>
                                                            </div>
                                                            <div class="flex items-start" id="tooth-<?= $i ?>-description-row" style="display: none;">
                                                                <span class="text-gray-400 w-16">Description:</span>
                                                                <span class="text-gray-300 text-xs" id="tooth-<?= $i ?>-description">No issues</span>
                                                            </div>
                                                            <div class="flex items-start" id="tooth-<?= $i ?>-treatment-row" style="display: none;">
                                                                <span class="text-gray-400 w-16">Treatment:</span>
                                                                <span class="text-gray-300 text-xs" id="tooth-<?= $i ?>-treatment">None</span>
                                                            </div>
                                                            <div class="flex items-center" id="tooth-<?= $i ?>-status-row" style="display: none;">
                                                                <span class="text-gray-400 w-16">Status:</span>
                                                                <span class="text-green-400" id="tooth-<?= $i ?>-status">Completed</span>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="fdi-tooltip hidden">
                                                        <div class="font-semibold text-yellow-300 mb-1">Tooth <?= getFDINumber($i) ?></div>
                                                        <div class="space-y-1" id="tooth-<?= $i ?>-details-fdi">
                                                            <div class="flex items-center">
                                                                <span class="text-gray-400 w-16">Condition:</span>
                                                                <span class="text-white" id="tooth-<?= $i ?>-condition-fdi">Healthy</span>
                                                            </div>
                                                            <div class="flex items-start" id="tooth-<?= $i ?>-description-row-fdi" style="display: none;">
                                                                <span class="text-gray-400 w-16">Description:</span>
                                                                <span class="text-gray-300 text-xs" id="tooth-<?= $i ?>-description-fdi">No issues</span>
                                                            </div>
                                                            <div class="flex items-start" id="tooth-<?= $i ?>-treatment-row-fdi" style="display: none;">
                                                                <span class="text-gray-400 w-16">Treatment:</span>
                                                                <span class="text-gray-300 text-xs" id="tooth-<?= $i ?>-treatment-fdi">None</span>
                                                            </div>
                                                            <div class="flex items-center" id="tooth-<?= $i ?>-status-row-fdi" style="display: none;">
                                                                <span class="text-gray-400 w-16">Status:</span>
                                                                <span class="text-green-400" id="tooth-<?= $i ?>-status-fdi">Completed</span>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endfor; ?>
                                </div>
                            </div>
                            
                            <!-- Lower Jaw Label -->
                            <div class="text-center mt-6">
                                <h3 class="text-2xl font-bold text-gray-800 flex items-center justify-center">
                                    <i class="fas fa-arrow-down text-green-600 mr-3"></i>
                                    Lower Jaw (Mandible)
                                    <i class="fas fa-arrow-down text-green-600 ml-3"></i>
                                </h3>
                                <div class="text-sm text-gray-600 mt-2">Left → Right (17-32)</div>
                            </div>
                        </div>
                        
                        <!-- Decorative Elements -->
                        <div class="absolute top-4 right-4 w-16 h-16 bg-pink-100/50 rounded-full -z-10"></div>
                        <div class="absolute bottom-4 left-4 w-12 h-12 bg-blue-100/50 rounded-full -z-10">            </div>
        </div>
    </div>

    <!-- Teeth Condition Table -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pb-8 mt-16">
        <div class="bg-white/70 backdrop-blur-sm rounded-2xl shadow-xl border border-white/20 overflow-hidden">
            <!-- Table Header -->
            <div class="bg-gradient-to-r from-blue-600 via-indigo-600 to-purple-600 text-white p-6">
                <div class="flex items-center justify-between">
                    <div class="flex items-center space-x-4">
                        <div class="w-12 h-12 bg-white/20 rounded-xl flex items-center justify-center backdrop-blur-sm">
                            <i class="fas fa-table text-white text-xl"></i>
                        </div>
                        <div>
                            <h3 class="text-xl font-bold">Teeth with Conditions</h3>
                            <p class="text-blue-100 text-sm">
                                <?php 
                                $conditionsCount = 0;
                                foreach ($odontogram as $tooth) {
                                    if ($tooth['condition_type'] !== 'healthy') {
                                        $conditionsCount++;
                                    }
                                }
                                ?>
                                Showing <?= $conditionsCount ?> teeth with conditions (out of 32 total)
                            </p>
                        </div>
                    </div>
                    <div class="flex items-center space-x-3">
                        <button onclick="toggleNumberingSystem()" class="px-4 py-2 bg-white/20 hover:bg-white/30 rounded-lg text-sm font-medium transition-colors duration-200">
                            <i class="fas fa-exchange-alt mr-2"></i>
                            <span id="numberingToggleText">Switch to FDI</span>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Table Content -->
            <div class="p-6">
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="border-b border-gray-200">
                                <th class="text-left py-3 px-4 font-semibold text-gray-700">Tooth #</th>
                                <th class="text-left py-3 px-4 font-semibold text-gray-700">FDI #</th>
                                <th class="text-left py-3 px-4 font-semibold text-gray-700">Position</th>
                                <th class="text-left py-3 px-4 font-semibold text-gray-700">Condition</th>
                                <th class="text-left py-3 px-4 font-semibold text-gray-700">Description</th>
                                <th class="text-left py-3 px-4 font-semibold text-gray-700">Treatment</th>
                                <th class="text-left py-3 px-4 font-semibold text-gray-700">Status</th>
                                <th class="text-left py-3 px-4 font-semibold text-gray-700">Actions</th>
                            </tr>
                        </thead>
                        <tbody id="teethTableBody">
                            <?php 
                            $teethWithConditions = [];
                            for ($i = 1; $i <= 32; $i++) {
                                $toothData = null;
                                foreach ($odontogram as $tooth) {
                                    if ($tooth['tooth_number'] == $i) {
                                        $toothData = $tooth;
                                        break;
                                    }
                                }
                                
                                $condition = $toothData ? $toothData['condition_type'] : 'healthy';
                                
                                // Only include teeth with conditions other than healthy
                                if ($condition !== 'healthy') {
                                    $teethWithConditions[] = [
                                        'tooth_number' => $i,
                                        'tooth_data' => $toothData,
                                        'condition' => $condition
                                    ];
                                }
                            }
                            
                            if (empty($teethWithConditions)): ?>
                                <tr>
                                    <td colspan="8" class="py-12 text-center text-gray-500">
                                        <div class="flex flex-col items-center space-y-3">
                                            <i class="fas fa-smile text-4xl text-green-400"></i>
                                            <div class="text-lg font-medium">All teeth are healthy!</div>
                                            <div class="text-sm">No conditions found for this patient.</div>
                                        </div>
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($teethWithConditions as $toothInfo): 
                                    $i = $toothInfo['tooth_number'];
                                    $toothData = $toothInfo['tooth_data'];
                                    $condition = $toothInfo['condition'];
                                    
                                    // Determine tooth position
                                    $position = '';
                                    if ($i >= 1 && $i <= 8) {
                                        $position = 'Upper Right';
                                    } elseif ($i >= 9 && $i <= 16) {
                                        $position = 'Upper Left';
                                    } elseif ($i >= 17 && $i <= 24) {
                                        $position = 'Lower Left';
                                    } elseif ($i >= 25 && $i <= 32) {
                                        $position = 'Lower Right';
                                    }
                                    
                                    $description = $toothData ? $toothData['condition_description'] : '';
                                    $treatment = $toothData ? $toothData['treatment_notes'] : '';
                                    $status = $toothData ? $toothData['treatment_status'] : 'completed';
                                ?>
                                <tr class="border-b border-gray-100 hover:bg-gray-50/50 transition-colors duration-200" data-tooth="<?= $i ?>">
                                    <td class="py-3 px-4">
                                        <span class="universal-number font-semibold text-blue-600"><?= $i ?></span>
                                        <span class="fdi-number hidden font-semibold text-blue-600"><?= getFDINumber($i) ?></span>
                                    </td>
                                    <td class="py-3 px-4">
                                        <span class="universal-number text-gray-600"><?= getFDINumber($i) ?></span>
                                        <span class="fdi-number hidden text-gray-600"><?= $i ?></span>
                                    </td>
                                    <td class="py-3 px-4 text-gray-600"><?= $position ?></td>
                                    <td class="py-3 px-4">
                                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium <?= getConditionClass($condition) ?>">
                                            <?= ucfirst(str_replace('_', ' ', $condition)) ?>
                                        </span>
                                    </td>
                                    <td class="py-3 px-4 text-gray-600 max-w-xs truncate" title="<?= htmlspecialchars($description) ?>">
                                        <?= $description ? htmlspecialchars($description) : '-' ?>
                                    </td>
                                    <td class="py-3 px-4 text-gray-600 max-w-xs truncate" title="<?= htmlspecialchars($treatment) ?>">
                                        <?= $treatment ? htmlspecialchars($treatment) : '-' ?>
                                    </td>
                                    <td class="py-3 px-4">
                                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium <?= getStatusClass($status) ?>">
                                            <?= getStatusText($status) ?>
                                        </span>
                                    </td>
                                    <td class="py-3 px-4">
                                        <button onclick="openToothModal(<?= $i ?>)" class="text-blue-600 hover:text-blue-800 font-medium text-xs transition-colors duration-200">
                                            <i class="fas fa-edit mr-1"></i>Edit
                                        </button>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

                    <!-- Enhanced User Instructions -->
                    <div class="mt-8 p-6 bg-gradient-to-r from-blue-50 to-indigo-50 border border-blue-200/50 rounded-2xl shadow-sm">
                        <div class="flex items-start space-x-4">
                            <div class="w-10 h-10 bg-gradient-to-br from-blue-500 to-indigo-600 rounded-xl flex items-center justify-center flex-shrink-0">
                                <i class="fas fa-info-circle text-white"></i>
                            </div>
                            <div class="flex-1">
                                <h4 class="font-bold text-gray-800 mb-3 text-lg">How to use the dental chart</h4>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm text-gray-700">
                                    <div class="flex items-center space-x-3">
                                        <div class="w-6 h-6 bg-blue-100 rounded-full flex items-center justify-center">
                                            <i class="fas fa-mouse-pointer text-blue-600 text-xs"></i>
                                        </div>
                                        <span><strong>Click</strong> on any tooth to view or edit its condition</span>
                                    </div>
                                    <div class="flex items-center space-x-3">
                                        <div class="w-6 h-6 bg-yellow-100 rounded-full flex items-center justify-center">
                                            <i class="fas fa-eye text-yellow-600 text-xs"></i>
                                        </div>
                                        <span><strong>Hover</strong> over teeth to see condition details</span>
                                    </div>
                                    <div class="flex items-center space-x-3">
                                        <div class="w-6 h-6 bg-purple-100 rounded-full flex items-center justify-center">
                                            <i class="fas fa-tools text-purple-600 text-xs"></i>
                                        </div>
                                        <span>Use the legend to understand condition colors</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

            </div>
        </div>
    </div>

    <!-- Patient Info and Analytics Section - Moved to Bottom -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pb-8">
        <div class="grid grid-cols-1 lg:grid-cols-4 gap-6">
            <!-- Enhanced Patient Info Card -->
            <div class="group relative bg-white/70 backdrop-blur-sm rounded-2xl p-6 shadow-lg hover:shadow-xl transition-all duration-300 border border-white/20 hover:border-blue-200/50">
                <div class="absolute inset-0 bg-gradient-to-br from-blue-50/50 to-indigo-50/50 rounded-2xl opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
                <div class="relative z-10">
                    <div class="flex items-center mb-4">
                        <div class="w-10 h-10 bg-gradient-to-br from-blue-500 to-indigo-600 rounded-xl flex items-center justify-center mr-3">
                            <i class="fas fa-user text-white text-sm"></i>
                        </div>
                        <h3 class="text-lg font-bold text-gray-800">Patient Information</h3>
                    </div>
                    <div class="space-y-3">
                        <div class="flex justify-between items-center py-2 px-3 bg-white/50 rounded-lg">
                            <span class="text-sm text-gray-600 font-medium">Name:</span>
                            <span class="font-semibold text-gray-800"><?= $patient['first_name'] . ' ' . $patient['last_name'] ?></span>
                        </div>
                        <div class="flex justify-between items-center py-2 px-3 bg-white/50 rounded-lg">
                            <span class="text-sm text-gray-600 font-medium">Patient ID:</span>
                            <span class="font-semibold text-blue-600"><?= $patient['patient_id'] ?></span>
                        </div>
                        <div class="flex justify-between items-center py-2 px-3 bg-white/50 rounded-lg">
                            <span class="text-sm text-gray-600 font-medium">Age:</span>
                            <span class="font-semibold text-gray-800">
                                <?php
                                $dob = new DateTime($patient['date_of_birth']);
                                $now = new DateTime();
                                echo $now->diff($dob)->y;
                                ?> years
                            </span>
                        </div>
                        <div class="flex justify-between items-center py-2 px-3 bg-white/50 rounded-lg">
                            <span class="text-sm text-gray-600 font-medium">Gender:</span>
                            <span class="font-semibold text-gray-800"><?= ucfirst($patient['gender']) ?></span>
                        </div>
                    </div>
                </div>
            </div>
            <!-- Enhanced Tooth Statistics Card -->
            <div class="group relative bg-white/70 backdrop-blur-sm rounded-2xl p-6 shadow-lg hover:shadow-xl transition-all duration-300 border border-white/20 hover:border-green-200/50">
                <div class="absolute inset-0 bg-gradient-to-br from-green-50/50 to-emerald-50/50 rounded-2xl opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
                <div class="relative z-10">
                    <div class="flex items-center mb-4">
                        <div class="w-10 h-10 bg-gradient-to-br from-green-500 to-emerald-600 rounded-xl flex items-center justify-center mr-3">
                            <i class="fas fa-chart-pie text-white text-sm"></i>
                        </div>
                        <h3 class="text-lg font-bold text-gray-800">Tooth Statistics</h3>
                    </div>
                    <div class="space-y-3">
                        <div class="flex justify-between items-center py-2 px-3 bg-white/50 rounded-lg">
                            <span class="text-sm text-gray-600 font-medium">Total Teeth:</span>
                            <span id="totalConditions" class="font-bold text-xl text-gray-800"><?= $stats['total_teeth'] ?? 0 ?></span>
                        </div>
                        <div class="flex justify-between items-center py-2 px-3 bg-green-50/70 rounded-lg">
                            <span class="text-sm text-gray-600 font-medium">Healthy:</span>
                            <span id="healthyCount" class="font-bold text-lg text-green-600"><?= $stats['healthy_teeth'] ?? 0 ?></span>
                        </div>
                        <div class="flex justify-between items-center py-2 px-3 bg-yellow-50/70 rounded-lg">
                            <span class="text-sm text-gray-600 font-medium">Cavities:</span>
                            <span id="cavitiesCount" class="font-bold text-lg text-yellow-600"><?= $stats['cavities'] ?? 0 ?></span>
                        </div>
                        <div class="flex justify-between items-center py-2 px-3 bg-blue-50/70 rounded-lg">
                            <span class="text-sm text-gray-600 font-medium">Fillings:</span>
                            <span id="fillingsCount" class="font-bold text-lg text-blue-600"><?= $stats['fillings'] ?? 0 ?></span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Enhanced Treatment Status Card -->
            <div class="group relative bg-white/70 backdrop-blur-sm rounded-2xl p-6 shadow-lg hover:shadow-xl transition-all duration-300 border border-white/20 hover:border-purple-200/50">
                <div class="absolute inset-0 bg-gradient-to-br from-purple-50/50 to-pink-50/50 rounded-2xl opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
                <div class="relative z-10">
                    <div class="flex items-center mb-4">
                        <div class="w-10 h-10 bg-gradient-to-br from-purple-500 to-pink-600 rounded-xl flex items-center justify-center mr-3">
                            <i class="fas fa-tools text-white text-sm"></i>
                        </div>
                        <h3 class="text-lg font-bold text-gray-800">Treatment Status</h3>
                    </div>
                    <div class="space-y-3">
                        <div class="flex justify-between items-center py-2 px-3 bg-purple-50/70 rounded-lg">
                            <span class="text-sm text-gray-600 font-medium">Crowns:</span>
                            <span id="crownsCount" class="font-bold text-lg text-purple-600"><?= $stats['crowns'] ?? 0 ?></span>
                        </div>
                        <div class="flex justify-between items-center py-2 px-3 bg-red-50/70 rounded-lg">
                            <span class="text-sm text-gray-600 font-medium">Extracted:</span>
                            <span id="extractedCount" class="font-bold text-lg text-red-600"><?= $stats['extracted'] ?? 0 ?></span>
                        </div>
                        <div class="flex justify-between items-center py-2 px-3 bg-orange-50/70 rounded-lg">
                            <span class="text-sm text-gray-600 font-medium">Needs Attention:</span>
                            <span id="needsTreatmentCount" class="font-bold text-lg text-orange-600"><?= $stats['needs_attention'] ?? 0 ?></span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Enhanced Quick Actions Card -->
            <div class="group relative bg-white/70 backdrop-blur-sm rounded-2xl p-6 shadow-lg hover:shadow-xl transition-all duration-300 border border-white/20 hover:border-indigo-200/50">
                <div class="absolute inset-0 bg-gradient-to-br from-indigo-50/50 to-blue-50/50 rounded-2xl opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
                <div class="relative z-10">
                    <div class="flex items-center mb-4">
                        <div class="w-10 h-10 bg-gradient-to-br from-indigo-500 to-blue-600 rounded-xl flex items-center justify-center mr-3">
                            <i class="fas fa-bolt text-white text-sm"></i>
                        </div>
                        <h3 class="text-lg font-bold text-gray-800">Quick Actions</h3>
                    </div>
                    <div class="space-y-3">
                        <button onclick="resetAllTeeth()" class="group/btn w-full flex items-center justify-center px-4 py-2 bg-white/70 hover:bg-white border border-gray-200 rounded-lg text-sm font-medium text-gray-700 hover:text-gray-900 shadow-sm hover:shadow-md transition-all duration-200">
                            <i class="fas fa-undo mr-2 group-hover/btn:animate-spin"></i>Reset All
                        </button>
                        <button onclick="markAllHealthy()" class="group/btn w-full flex items-center justify-center px-4 py-2 bg-gradient-to-r from-green-500 to-emerald-600 hover:from-green-600 hover:to-emerald-700 text-white rounded-lg text-sm font-medium shadow-lg hover:shadow-xl transition-all duration-200 transform hover:scale-105">
                            <i class="fas fa-check mr-2 group-hover/btn:animate-pulse"></i>Mark All Healthy
                        </button>
                        <button onclick="exportData()" class="group/btn w-full flex items-center justify-center px-4 py-2 bg-white/70 hover:bg-white border border-gray-200 rounded-lg text-sm font-medium text-gray-700 hover:text-gray-900 shadow-sm hover:shadow-md transition-all duration-200">
                            <i class="fas fa-file-export mr-2 group-hover/btn:animate-bounce"></i>Export Data
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Enhanced Tooth Condition Modal with Chat-Style Interface -->
<div id="toothModal" class="fixed inset-0 bg-black/50 backdrop-blur-sm hidden z-50 transition-opacity duration-300">
    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="bg-white/95 backdrop-blur-md rounded-2xl shadow-2xl max-w-lg w-full transform transition-all duration-300 scale-95 opacity-0" id="modalContent">
            <!-- Enhanced Modal Header -->
            <div class="bg-gradient-to-r from-blue-600 via-indigo-600 to-purple-600 text-white p-6 rounded-t-2xl relative overflow-hidden">
                <div class="absolute inset-0 bg-black/10"></div>
                <div class="relative z-10">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center space-x-4">
                            <div class="w-12 h-12 bg-white/20 rounded-xl flex items-center justify-center backdrop-blur-sm">
                                <i class="fas fa-tooth text-white text-xl"></i>
                            </div>
                            <div>
                                <h3 class="text-xl font-bold">Tooth <span id="modalToothNumber" class="text-yellow-300"></span> Condition</h3>
                                <p class="text-blue-100 text-sm">Update dental condition and treatment notes</p>
                            </div>
                        </div>
                        <button onclick="closeToothModal()" class="w-8 h-8 bg-white/20 hover:bg-white/30 rounded-lg flex items-center justify-center transition-colors duration-200">
                            <i class="fas fa-times text-white"></i>
                        </button>
                    </div>
                </div>
                <!-- Decorative Elements -->
                <div class="absolute top-0 right-0 w-20 h-20 bg-white/5 rounded-full -translate-y-10 translate-x-10"></div>
                <div class="absolute bottom-0 left-0 w-16 h-16 bg-white/5 rounded-full translate-y-8 -translate-x-8"></div>
            </div>
            <!-- Enhanced Form Content -->
            <div class="p-6">
                <form id="toothForm" class="space-y-6">
                    <?= csrf_field() ?>
                    <input type="hidden" id="toothNumber" name="tooth_number">
                    <input type="hidden" name="patient_id" value="<?= $patient['id'] ?>">
                    <input type="hidden" name="examination_id" value="">
                    
                    <!-- Condition Type Selection -->
                    <div class="space-y-2">
                        <label for="condition_type" class="block text-sm font-semibold text-gray-700">
                            <i class="fas fa-stethoscope mr-2 text-blue-600"></i>Condition Type
                        </label>
                        <select id="condition_type" name="condition_type" class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors duration-200 bg-white/70 backdrop-blur-sm" required>
                            <option value="">Select a condition type</option>
                            <?php foreach ($condition_types as $key => $label): ?>
                                <option value="<?= $key ?>"><?= $label ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <!-- Condition Description -->
                    <div class="space-y-2">
                        <label for="condition_description" class="block text-sm font-semibold text-gray-700">
                            <i class="fas fa-file-text mr-2 text-green-600"></i>Description
                        </label>
                        <textarea id="condition_description" name="condition_description" 
                                  class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-green-500 focus:border-green-500 transition-colors duration-200 bg-white/70 backdrop-blur-sm resize-none" 
                                  rows="3" placeholder="Describe the condition in detail..."></textarea>
                    </div>
                    
                    <!-- Treatment Notes -->
                    <div class="space-y-2">
                        <label for="treatment_notes" class="block text-sm font-semibold text-gray-700">
                            <i class="fas fa-notes-medical mr-2 text-purple-600"></i>Treatment Notes
                        </label>
                        <textarea id="treatment_notes" name="treatment_notes" 
                                  class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-purple-500 focus:border-purple-500 transition-colors duration-200 bg-white/70 backdrop-blur-sm resize-none" 
                                  rows="3" placeholder="Treatment performed or planned..."></textarea>
                    </div>
                    
                    <!-- Treatment Status -->
                    <div class="space-y-2">
                        <label for="treatment_status" class="block text-sm font-semibold text-gray-700">
                            <i class="fas fa-tasks mr-2 text-orange-600"></i>Treatment Status
                        </label>
                        <select id="treatment_status" name="treatment_status" class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-orange-500 focus:border-orange-500 transition-colors duration-200 bg-white/70 backdrop-blur-sm">
                            <option value="pending">🟡 Pending</option>
                            <option value="in_progress">🟠 In Progress</option>
                            <option value="completed">🟢 Completed</option>
                            <option value="needs_attention">🔴 Needs Attention</option>
                        </select>
                    </div>
                    
                    <!-- Enhanced Action Buttons -->
                    <div class="flex flex-col sm:flex-row gap-3 pt-4 border-t border-gray-200">
                        <button type="button" onclick="closeToothModal()" class="flex-1 group relative inline-flex items-center justify-center px-6 py-3 bg-gray-100 hover:bg-gray-200 border border-gray-300 rounded-xl text-sm font-medium text-gray-700 hover:text-gray-900 transition-all duration-200">
                            <i class="fas fa-times mr-2 group-hover:rotate-90 transition-transform"></i>Cancel
                        </button>
                        <button type="button" onclick="removeToothCondition()" class="flex-1 group relative inline-flex items-center justify-center px-6 py-3 bg-red-100 hover:bg-red-200 border border-red-300 rounded-xl text-sm font-medium text-red-700 hover:text-red-900 transition-all duration-200">
                            <i class="fas fa-trash mr-2 group-hover:animate-bounce"></i>Remove Condition
                        </button>
                        <button type="submit" class="flex-1 group relative inline-flex items-center justify-center px-6 py-3 bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 text-white rounded-xl text-sm font-medium shadow-lg hover:shadow-xl transition-all duration-200 transform hover:scale-105">
                            <i class="fas fa-save mr-2 group-hover:animate-pulse"></i>Save Changes
                        </button>
                    </div>
                </form>
            </div>
            </div>
        </div>
    </div>
</div>

<script>
const patientId = <?= $patient['id'] ?>;
const odontogramData = <?= json_encode($odontogram) ?>;
const conditionTypes = <?= json_encode($condition_types) ?>;

console.log('Patient ID:', patientId);
console.log('Odontogram data from server:', odontogramData);
console.log('Condition types:', conditionTypes);

// Numbering system state
let currentNumberingSystem = 'universal'; // Default to Universal

// Function to switch between numbering systems
function switchNumberingSystem(system) {
    currentNumberingSystem = system;
    
    // Update button states
    const universalBtn = document.getElementById('universalBtn');
    const fdiBtn = document.getElementById('fdiBtn');
    
    if (system === 'universal') {
        universalBtn.classList.add('bg-white', 'text-blue-600', 'shadow-sm');
        universalBtn.classList.remove('text-gray-600');
        fdiBtn.classList.remove('bg-white', 'text-blue-600', 'shadow-sm');
        fdiBtn.classList.add('text-gray-600');
        
        // Show universal numbers, hide FDI numbers
        document.querySelectorAll('.universal-number').forEach(el => el.classList.remove('hidden'));
        document.querySelectorAll('.fdi-number').forEach(el => el.classList.add('hidden'));
        document.querySelectorAll('.universal-tooltip').forEach(el => el.classList.remove('hidden'));
        document.querySelectorAll('.fdi-tooltip').forEach(el => el.classList.add('hidden'));
    } else {
        fdiBtn.classList.add('bg-white', 'text-blue-600', 'shadow-sm');
        fdiBtn.classList.remove('text-gray-600');
        universalBtn.classList.remove('bg-white', 'text-blue-600', 'shadow-sm');
        universalBtn.classList.add('text-gray-600');
        
        // Show FDI numbers, hide universal numbers
        document.querySelectorAll('.fdi-number').forEach(el => el.classList.remove('hidden'));
        document.querySelectorAll('.universal-number').forEach(el => el.classList.add('hidden'));
        document.querySelectorAll('.fdi-tooltip').forEach(el => el.classList.remove('hidden'));
        document.querySelectorAll('.universal-tooltip').forEach(el => el.classList.add('hidden'));
    }
    
    console.log('Switched to', system, 'numbering system');
}

// Toggle numbering system for table
function toggleNumberingSystem() {
    const currentSystem = currentNumberingSystem || 'universal';
    const newSystem = currentSystem === 'universal' ? 'fdi' : 'universal';
    
    // Update the legend toggle
    switchNumberingSystem(newSystem);
    
    // Update table toggle button text
    const toggleText = document.getElementById('numberingToggleText');
    if (toggleText) {
        toggleText.textContent = newSystem === 'universal' ? 'Switch to FDI' : 'Switch to Universal';
    }
}

// Initialize odontogram with existing data
document.addEventListener('DOMContentLoaded', function() {
    initializeOdontogram();
});

function initializeOdontogram() {
    console.log('Initializing odontogram with data:', odontogramData);
    odontogramData.forEach(function(tooth) {
        console.log('Processing tooth:', tooth);
        const toothElement = document.querySelector(`[data-tooth="${tooth.tooth_number}"]`);
        if (toothElement) {
            console.log('Found tooth element for tooth', tooth.tooth_number, 'with condition:', tooth.condition_type);
            updateToothAppearance(toothElement, tooth.condition_type);
        } else {
            console.log('No tooth element found for tooth', tooth.tooth_number);
        }
    });
    
    // Initialize all tooltips - show detailed info only for teeth with conditions
    for (let i = 1; i <= 32; i++) {
        const toothElement = document.querySelector(`[data-tooth="${i}"]`);
        if (toothElement) {
            const toothData = odontogramData.find(t => parseInt(t.tooth_number) === parseInt(i));
            const conditionType = toothData ? toothData.condition_type : 'healthy';
            updateToothTooltip(toothElement, conditionType);
        }
    }
}

function updateToothAppearance(element, conditionType) {
    console.log('Updating tooth appearance for condition:', conditionType);
    // Reset to base classes
    element.className = 'w-16 h-20 bg-gradient-to-br from-white to-gray-50 border-2 border-gray-200 rounded-t-xl rounded-b-2xl flex flex-col items-center justify-center cursor-pointer transition-all duration-300 relative overflow-hidden shadow-md backdrop-blur-sm hover:-translate-y-1.5 hover:scale-110 hover:shadow-2xl hover:border-blue-500 z-10 group-hover:z-20';
    
    // Add appropriate classes based on condition - EXACTLY matching legend colors
    switch(conditionType) {
        case 'healthy':
            // Keep default classes (green-ish)
            element.classList.add('bg-gradient-to-br', 'from-green-100', 'to-green-200', 'border-green-400');
            break;
        case 'cavity':
            element.classList.add('bg-gradient-to-br', 'from-yellow-100', 'to-yellow-200', 'border-yellow-400');
            break;
        case 'filling':
            element.classList.add('bg-gradient-to-br', 'from-blue-100', 'to-blue-200', 'border-blue-400');
            break;
        case 'crown':
            element.classList.add('bg-gradient-to-br', 'from-purple-100', 'to-purple-200', 'border-purple-400');
            break;
        case 'root_canal':
            element.classList.add('bg-gradient-to-br', 'from-pink-100', 'to-pink-200', 'border-pink-400');
            break;
        case 'extracted':
            element.classList.add('bg-gradient-to-br', 'from-red-100', 'to-red-200', 'border-red-400', 'opacity-60');
            break;
        case 'implant':
            element.classList.add('bg-gradient-to-br', 'from-indigo-100', 'to-indigo-200', 'border-indigo-400');
            break;
        case 'bridge':
            element.classList.add('bg-gradient-to-br', 'from-teal-100', 'to-teal-200', 'border-teal-400');
            break;
        case 'partial_denture':
            element.classList.add('bg-gradient-to-br', 'from-orange-100', 'to-orange-200', 'border-orange-400');
            break;
        case 'full_denture':
            element.classList.add('bg-gradient-to-br', 'from-amber-100', 'to-amber-200', 'border-amber-400');
            break;
        case 'other':
            element.classList.add('bg-gradient-to-br', 'from-gray-100', 'to-gray-200', 'border-gray-400');
            break;
        default:
            // Healthy or no condition - keep default classes
            element.classList.add('bg-gradient-to-br', 'from-green-100', 'to-green-200', 'border-green-400');
            break;
    }
    
    // Update tooltip content
    updateToothTooltip(element, conditionType);
}

function updateToothTooltip(element, conditionType) {
    const toothNumber = element.dataset.tooth;
    const conditionText = getConditionDisplayText(conditionType);
    
    // Get tooth data from odontogramData
    const toothData = odontogramData.find(t => parseInt(t.tooth_number) === parseInt(toothNumber));
    
    // Update universal tooltip
    updateTooltipDetails(toothNumber, conditionText, toothData, false);
    
    // Update FDI tooltip
    updateTooltipDetails(toothNumber, conditionText, toothData, true);
}

function updateTooltipDetails(toothNumber, conditionText, toothData, isFDI = false) {
    const suffix = isFDI ? '-fdi' : '';
    
    // Update condition
    const conditionElement = document.getElementById(`tooth-${toothNumber}-condition${suffix}`);
    if (conditionElement) {
        conditionElement.textContent = conditionText;
    }
    
    // Show/hide detailed rows based on whether tooth has a condition
    const hasCondition = toothData && toothData.condition_type && toothData.condition_type !== 'healthy';
    
    // Description row
    const descriptionRow = document.getElementById(`tooth-${toothNumber}-description-row${suffix}`);
    if (descriptionRow) {
        if (hasCondition && toothData.condition_description) {
            const descriptionElement = document.getElementById(`tooth-${toothNumber}-description${suffix}`);
            if (descriptionElement) {
                descriptionElement.textContent = toothData.condition_description;
            }
            descriptionRow.style.display = 'flex';
        } else {
            descriptionRow.style.display = 'none';
        }
    }
    
    // Treatment row
    const treatmentRow = document.getElementById(`tooth-${toothNumber}-treatment-row${suffix}`);
    if (treatmentRow) {
        if (hasCondition && toothData.treatment_notes) {
            const treatmentElement = document.getElementById(`tooth-${toothNumber}-treatment${suffix}`);
            if (treatmentElement) {
                treatmentElement.textContent = toothData.treatment_notes;
            }
            treatmentRow.style.display = 'flex';
        } else {
            treatmentRow.style.display = 'none';
        }
    }
    
    // Status row
    const statusRow = document.getElementById(`tooth-${toothNumber}-status-row${suffix}`);
    if (statusRow) {
        if (hasCondition) {
            const statusElement = document.getElementById(`tooth-${toothNumber}-status${suffix}`);
            if (statusElement) {
                statusElement.textContent = getStatusDisplayText(toothData?.treatment_status || 'completed');
            }
            statusRow.style.display = 'flex';
        } else {
            statusRow.style.display = 'none';
        }
    }
}

function getStatusDisplayText(status) {
    const statusMap = {
        'pending': '🟡 Pending',
        'in_progress': '🟠 In Progress',
        'completed': '🟢 Completed',
        'needs_attention': '🔴 Needs Attention'
    };
    
    return statusMap[status] || '🟢 Completed';
}

function getConditionDisplayText(conditionType) {
    const conditionMap = {
        'healthy': 'Healthy',
        'cavity': 'Cavity',
        'filling': 'Filling',
        'crown': 'Crown',
        'root_canal': 'Root Canal',
        'extracted': 'Extracted',
        'implant': 'Implant',
        'bridge': 'Bridge',
        'partial_denture': 'Partial Denture',
        'full_denture': 'Full Denture',
        'other': 'Other'
    };
    
    return conditionMap[conditionType] || 'Healthy';
}

function updateStats() {
    // Calculate stats from current odontogramData
    const stats = {
        total_conditions: odontogramData.length,
        healthy_count: odontogramData.filter(t => t.condition_type === 'healthy').length,
        needs_treatment_count: odontogramData.filter(t => ['cavity', 'extracted'].includes(t.condition_type)).length,
        cavities: odontogramData.filter(t => t.condition_type === 'cavity').length,
        fillings: odontogramData.filter(t => t.condition_type === 'filling').length,
        crowns: odontogramData.filter(t => t.condition_type === 'crown').length,
        extracted: odontogramData.filter(t => t.condition_type === 'extracted').length
    };
    
    // Update the stats display - only if elements exist
    const totalEl = document.getElementById('totalConditions');
    if (totalEl) totalEl.textContent = stats.total_conditions;
    
    const healthyEl = document.getElementById('healthyCount');
    if (healthyEl) healthyEl.textContent = stats.healthy_count;
    
    const needsTreatmentEl = document.getElementById('needsTreatmentCount');
    if (needsTreatmentEl) needsTreatmentEl.textContent = stats.needs_treatment_count;
    
    const cavitiesEl = document.getElementById('cavitiesCount');
    if (cavitiesEl) cavitiesEl.textContent = stats.cavities;
    
    const fillingsEl = document.getElementById('fillingsCount');
    if (fillingsEl) fillingsEl.textContent = stats.fillings;
    
    const crownsEl = document.getElementById('crownsCount');
    if (crownsEl) crownsEl.textContent = stats.crowns;
    
    const extractedEl = document.getElementById('extractedCount');
    if (extractedEl) extractedEl.textContent = stats.extracted;
    
    console.log('Updated stats:', stats);
}

function openToothModal(toothNumber) {
    console.log('Opening modal for tooth:', toothNumber);
    console.log('Available odontogram data:', odontogramData);
    
    const modal = document.getElementById('toothModal');
    const modalContent = document.getElementById('modalContent');
    
    document.getElementById('modalToothNumber').textContent = toothNumber;
    document.getElementById('toothNumber').value = toothNumber;
    
    // Load existing data for this tooth
    const existingTooth = odontogramData.find(t => parseInt(t.tooth_number) === parseInt(toothNumber));
    console.log('Found existing tooth data:', existingTooth);
    
    if (existingTooth) {
        console.log('Pre-populating form with existing data:', {
            condition_type: existingTooth.condition_type,
            condition_description: existingTooth.condition_description,
            treatment_notes: existingTooth.treatment_notes,
            treatment_status: existingTooth.treatment_status
        });
        
        document.getElementById('condition_type').value = existingTooth.condition_type || 'healthy';
        document.getElementById('condition_description').value = existingTooth.condition_description || '';
        document.getElementById('treatment_notes').value = existingTooth.treatment_notes || '';
        document.getElementById('treatment_status').value = existingTooth.treatment_status || 'pending';
    } else {
        console.log('No existing data found, resetting form');
        // Reset form
        document.getElementById('toothForm').reset();
        document.getElementById('toothNumber').value = toothNumber;
        // Set default values
        document.getElementById('condition_type').value = 'healthy';
        document.getElementById('treatment_status').value = 'pending';
    }
    
    // Show modal with animation
    modal.classList.remove('hidden');
    setTimeout(() => {
        modalContent.style.transform = 'scale(1)';
        modalContent.style.opacity = '1';
    }, 10);
}

function closeToothModal() {
    const modal = document.getElementById('toothModal');
    const modalContent = document.getElementById('modalContent');
    
    // Animate out
    modalContent.style.transform = 'scale(0.95)';
    modalContent.style.opacity = '0';
    
    setTimeout(() => {
        modal.classList.add('hidden');
    }, 300);
}

function resetTooth() {
    if (confirm('Are you sure you want to reset this tooth to healthy status?')) {
        fetch('<?= base_url('odontogram/reset-tooth') ?>', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `patient_id=${patientId}&tooth_number=${document.getElementById('toothNumber').value}`
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const toothElement = document.querySelector(`[data-tooth="${document.getElementById('toothNumber').value}"]`);
                updateToothAppearance(toothElement, 'healthy');
                closeToothModal();
                location.reload(); // Refresh to update stats
            } else {
                alert('Error: ' + data.message);
            }
        });
    }
}

// Enhanced form submission with loading states and error handling
document.getElementById('toothForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    const submitButton = this.querySelector('button[type="submit"]');
    
    // Cache original children if not already cached
    if (!submitButton.hasOwnProperty('_originalChildren')) {
        submitButton._originalChildren = Array.from(submitButton.childNodes).map(n => n.cloneNode(true));
    }
    
    // Show loading state
    submitButton.disabled = true;
    
    const spinnerIcon = document.createElement('i');
    spinnerIcon.className = 'fas fa-spinner fa-spin mr-2';
    submitButton.replaceChildren(spinnerIcon, document.createTextNode('Saving...'));
    
    submitButton.classList.add('opacity-75');
    
    // Add loading animation to tooth
    const toothElement = document.querySelector(`[data-tooth="${formData.get('tooth_number')}"]`);
    if (toothElement) {
        toothElement.classList.add('updating');
    }
    
    fetch('<?= base_url('odontogram/update-tooth') ?>', {
        method: 'POST',
        body: formData
    })
    .then(response => {
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        return response.json();
    })
    .then(data => {
        if (data.csrf_token) {
            window.refreshCsrfToken(data.csrf_token);
        }

        if (data.success) {
            // Update the odontogramData array with the new data
            const toothNumber = parseInt(formData.get('tooth_number'));
            const existingToothIndex = odontogramData.findIndex(t => parseInt(t.tooth_number) === toothNumber);
            
            const updatedToothData = {
                tooth_number: toothNumber,
                condition_type: formData.get('condition_type'),
                condition_description: formData.get('condition_description'),
                treatment_notes: formData.get('treatment_notes'),
                treatment_status: formData.get('treatment_status'),
                treatment_date: new Date().toISOString().split('T')[0],
                created_by: 1
            };
            
            if (existingToothIndex !== -1) {
                // Update existing tooth data
                odontogramData[existingToothIndex] = updatedToothData;
            } else {
                // Add new tooth data
                odontogramData.push(updatedToothData);
            }
            
            console.log('Updated odontogram data:', odontogramData);
            
            // Show success animation
            if (toothElement) {
                toothElement.classList.remove('updating');
                toothElement.classList.add('success');
                updateToothAppearance(toothElement, formData.get('condition_type'));
                
                // Remove success class after animation
                setTimeout(() => {
                    toothElement.classList.remove('success');
                }, 600);
            }
            
            // Show success notification
            showNotification('Tooth condition updated successfully!', 'success');
            closeToothModal();
            
            // Update stats without reloading
            updateStats();
        } else {
            throw new Error(data.message || 'Failed to update tooth condition');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        
        // Show error notification
        showNotification('Error: ' + error.message, 'error');
        
        // Remove loading states
        if (toothElement) {
            toothElement.classList.remove('updating');
        }
    })
    .finally(() => {
        // Reset button state
        submitButton.disabled = false;
        submitButton.replaceChildren(...submitButton._originalChildren.map(n => n.cloneNode(true)));
        submitButton.classList.remove('opacity-75');
    });
});

// Enhanced remove tooth condition function
function removeToothCondition() {
    if (confirm('Are you sure you want to remove the condition from this tooth? It will be set back to healthy status.')) {
        const toothNumber = document.getElementById('toothNumber').value;
        const toothElement = document.querySelector(`[data-tooth="${toothNumber}"]`);
        
        if (toothElement) {
            toothElement.classList.add('updating');
        }
        
        fetch('<?= base_url('odontogram/reset-tooth') ?>', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
                [window.csrfConfig.header]: window.getCsrfToken()
            },
            body: `patient_id=${patientId}&tooth_number=${toothNumber}`
        })
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            return response.json();
        })
        .then(data => {
            if (data.csrf_token) {
                window.refreshCsrfToken(data.csrf_token);
            }
            
            if (data.success) {
                if (toothElement) {
                    toothElement.classList.remove('updating');
                    toothElement.classList.add('success');
                    updateToothAppearance(toothElement, 'healthy');
                    
                    setTimeout(() => {
                        toothElement.classList.remove('success');
                    }, 600);
                }
                
                // Update the odontogramData array
                const toothIndex = odontogramData.findIndex(t => parseInt(t.tooth_number) === parseInt(toothNumber));
                if (toothIndex !== -1) {
                    odontogramData[toothIndex] = {
                        tooth_number: toothNumber,
                        condition_type: 'healthy',
                        condition_description: '',
                        treatment_notes: '',
                        treatment_status: 'completed'
                    };
                } else {
                    odontogramData.push({
                        tooth_number: toothNumber,
                        condition_type: 'healthy',
                        condition_description: '',
                        treatment_notes: '',
                        treatment_status: 'completed'
                    });
                }
                
                // Update statistics without page reload
                updateStats();
                
                showNotification('Tooth condition removed successfully!', 'success');
                closeToothModal();
            } else {
                throw new Error(data.message || 'Failed to remove tooth condition');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showNotification('Error: ' + error.message, 'error');
            
            if (toothElement) {
                toothElement.classList.remove('updating');
            }
        });
    }
}

// Enhanced reset tooth function with loading states (kept for backward compatibility)
function resetTooth() {
    removeToothCondition(); // Use the same function
}

// Notification system
function showNotification(message, type = 'info') {
    const notification = document.createElement('div');
    notification.className = `fixed top-4 right-4 z-50 px-6 py-4 rounded-lg shadow-lg transform transition-all duration-300 translate-x-full`;
    
    const colors = {
        success: 'bg-green-500 text-white',
        error: 'bg-red-500 text-white',
        info: 'bg-blue-500 text-white',
        warning: 'bg-yellow-500 text-white'
    };
    
    notification.className += ` ${colors[type] || colors.info}`;
    
    const container = document.createElement('div');
    container.className = 'flex items-center space-x-3';
    
    const icon = document.createElement('i');
    const iconClass = type === 'success' ? 'check-circle' : type === 'error' ? 'exclamation-circle' : 'info-circle';
    icon.className = `fas fa-${iconClass}`;
    
    const msgSpan = document.createElement('span');
    msgSpan.textContent = message;
    
    const closeBtn = document.createElement('button');
    closeBtn.className = 'ml-4 text-white hover:text-gray-200';
    closeBtn.onclick = function() {
        if (this.parentElement && this.parentElement.parentElement) {
            this.parentElement.parentElement.remove();
        }
    };
    
    const closeIcon = document.createElement('i');
    closeIcon.className = 'fas fa-times';
    closeBtn.appendChild(closeIcon);
    
    container.appendChild(icon);
    container.appendChild(msgSpan);
    container.appendChild(closeBtn);
    notification.appendChild(container);
    
    document.body.appendChild(notification);
    
    // Animate in
    setTimeout(() => {
        notification.classList.remove('translate-x-full');
    }, 10);
    
    // Auto remove after 5 seconds
    setTimeout(() => {
        notification.classList.add('translate-x-full');
        setTimeout(() => {
            if (notification.parentElement) {
                notification.remove();
            }
        }, 300);
    }, 5000);
}

function resetAllTeeth() {
    if (confirm('Are you sure you want to reset all teeth to healthy status? This action cannot be undone.')) {
        // Implementation for resetting all teeth
        alert('Feature coming soon!');
    }
}

function markAllHealthy() {
    if (confirm('Are you sure you want to mark all teeth as healthy?')) {
        // Implementation for marking all teeth as healthy
        alert('Feature coming soon!');
    }
}

function printOdontogram() {
    window.open('<?= base_url('odontogram/' . $patient['id'] . '/print') ?>', '_blank');
}

function exportData() {
    const data = {
        patient: {
            id: patientId,
            name: '<?= $patient['first_name'] . ' ' . $patient['last_name'] ?>',
            patient_id: '<?= $patient['patient_id'] ?>'
        },
        odontogram: odontogramData,
        stats: <?= json_encode($stats) ?>
    };
    
    const blob = new Blob([JSON.stringify(data, null, 2)], { type: 'application/json' });
    const url = URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.href = url;
    a.download = `odontogram_${patientId}_${new Date().toISOString().split('T')[0]}.json`;
    a.click();
    URL.revokeObjectURL(url);
}

// Close modal when clicking outside
document.getElementById('toothModal').addEventListener('click', function(e) {
    if (e.target === this) {
        closeToothModal();
    }
});



// Enhanced Accessibility and Keyboard Navigation
document.addEventListener('DOMContentLoaded', function() {
    // Add click handlers for tooth selection
    document.querySelectorAll('[data-tooth]').forEach(tooth => {
        
        // Add keyboard navigation
        tooth.addEventListener('keydown', function(e) {
            if (e.key === 'Enter' || e.key === ' ') {
                e.preventDefault();
                openToothModal(this.dataset.tooth);
            } else if (e.key === 'Escape') {
                closeToothModal();
            }
        });
        
        // Add focus management
        tooth.addEventListener('focus', function() {
            this.style.outline = '2px solid #3b82f6';
            this.style.outlineOffset = '2px';
        });
        
        tooth.addEventListener('blur', function() {
            this.style.outline = 'none';
        });
    });
    
    // Add keyboard shortcuts
    document.addEventListener('keydown', function(e) {
        
        // Escape to close modal
        if (e.key === 'Escape') {
            closeToothModal();
        }
        
        // Ctrl/Cmd + S to save (when modal is open)
        if ((e.ctrlKey || e.metaKey) && e.key === 's' && !document.getElementById('toothModal').classList.contains('hidden')) {
            e.preventDefault();
            document.getElementById('toothForm').dispatchEvent(new Event('submit'));
        }
    });
});

</script>

<style>
/* Animation classes for tooth updates */
.updating {
    animation: pulse 1s infinite;
    opacity: 0.7;
}

.success {
    animation: bounce 0.6s ease-in-out;
    background: linear-gradient(135deg, #10b981, #34d399) !important;
    border-color: #059669 !important;
}

@keyframes bounce {
    0%, 20%, 53%, 80%, 100% {
        transform: translate3d(0,0,0);
    }
    40%, 43% {
        transform: translate3d(0, -8px, 0);
    }
    70% {
        transform: translate3d(0, -4px, 0);
    }
    90% {
        transform: translate3d(0, -2px, 0);
    }
}
</style>

<?= $this->endSection() ?>
