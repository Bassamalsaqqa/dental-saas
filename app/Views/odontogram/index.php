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
        case "pending":
            return "Pending";
        case "in_progress":
            return "In Progress";
        case "completed":
            return "Completed";
        case "needs_attention":
            return "Needs Attention";
        default:
            return "Completed";
    }
}
?>

<?= $this->section('content') ?>
<?php
$isExaminationChart = !empty($examination_id);
$chartTitle = $isExaminationChart ? lang('Examination.visit_chart') : lang('Examination.patient_chart');
$chartHelp = $isExaminationChart ? lang('Examination.visit_chart_help') : lang('Examination.patient_chart_help');
?>
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
                            <?= esc($chartTitle) ?>
                        </h1>
                        <p class="text-gray-600 mt-1 flex items-center">
                            <i class="fas fa-user-circle mr-2 text-blue-500"></i>
                            <?= $patient['first_name'] . ' ' . $patient['last_name'] ?> 
                            <span class="ml-2 px-2 py-1 bg-blue-100 text-blue-800 text-xs font-medium rounded-full">
                                <?= $patient['patient_id'] ?>
                            </span>
                        </p>
                        <p class="mt-2 max-w-2xl text-sm font-semibold text-slate-600">
                            <?= esc($chartHelp) ?>
                            <?php if ($isExaminationChart): ?>
                                <span class="ms-1 font-black text-blue-700" dir="ltr"><?= esc($examination['examination_id'] ?? ('EX-' . $examination_id)) ?></span>
                            <?php endif; ?>
                        </p>
                    </div>
                </div>
                <div class="flex flex-wrap gap-3">
                    <button onclick="printOdontogram()" class="group relative inline-flex items-center px-4 py-2 bg-white/70 hover:bg-white border border-gray-200 rounded-lg text-sm font-medium text-gray-700 hover:text-gray-900 shadow-sm hover:shadow-md transition-all duration-200">
                        <i class="fas fa-print mr-2 group-hover:animate-pulse"></i>Print
                    </button>
                    <a href="<?= workflow_back('patient/' . $patient['id'], $return_to ?? null) ?>" class="group relative inline-flex items-center px-4 py-2 bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 text-white rounded-lg text-sm font-medium shadow-lg hover:shadow-xl transition-all duration-200 transform hover:scale-105">
                        <i class="fas fa-arrow-left mr-2 group-hover:-translate-x-1 transition-transform"></i>Back
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
                        <div class="flex flex-col items-end gap-3">
                            <div class="hidden md:flex items-center space-x-2 text-blue-100">
                                <div class="w-2 h-2 bg-green-400 rounded-full animate-pulse"></div>
                                <span class="text-sm font-medium">Live Updates</span>
                            </div>
                            <div class="flex items-center gap-2">
                                <button type="button"
                                        id="legendToggleBtn"
                                        class="inline-flex items-center px-3 py-2 bg-white/20 hover:bg-white/30 text-white text-xs font-semibold rounded-lg border border-white/30 shadow-sm transition-all duration-200">
                                    <i id="legendToggleIcon" class="fas fa-chevron-down mr-2"></i>
                                    <span id="legendToggleText">Show Legend</span>
                                </button>
                                <button id="batchModeToggleBtn"
                                        onclick="toggleBatchMode()"
                                        class="inline-flex items-center px-3 py-2 bg-white/20 hover:bg-white/30 text-white text-xs font-semibold rounded-lg border border-white/30 shadow-sm transition-all duration-200">
                                    <i class="fas fa-object-group mr-2"></i>
                                    <span id="batchModeToggleText">Batch Select</span>
                                </button>
                                <div id="batchTopControls" class="hidden items-center gap-2">
                                    <span id="batchTopCount" class="inline-flex items-center px-2.5 py-1 bg-amber-400/90 text-amber-950 rounded-full text-xs font-bold">
                                        0 selected
                                    </span>
                                    <button type="button" id="batchApplyBtn" onclick="openBatchModal()"
                                            class="inline-flex items-center px-3 py-2 text-xs font-semibold rounded-lg border transition-all duration-200 disabled:cursor-not-allowed"
                                            disabled>
                                        <i class="fas fa-layer-group mr-1.5"></i>Apply
                                    </button>
                                    <button type="button" id="batchClearBtn" onclick="clearBatchSelection()"
                                            class="inline-flex items-center px-3 py-2 bg-white/20 hover:bg-white/30 text-white text-xs font-semibold rounded-lg border border-white/30 shadow-sm transition-all duration-200">
                                        Clear
                                    </button>
                                    <button type="button" id="batchExitBtn" onclick="toggleBatchMode(false)"
                                            class="inline-flex items-center px-3 py-2 bg-white/20 hover:bg-white/30 text-white text-xs font-semibold rounded-lg border border-white/30 shadow-sm transition-all duration-200">
                                        Exit
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- Decorative Elements --> 
                <div class="absolute top-0 right-0 w-32 h-32 bg-white/5 rounded-full -translate-y-16 translate-x-16"></div>
                <div class="absolute bottom-0 left-0 w-24 h-24 bg-white/5 rounded-full translate-y-12 -translate-x-12"></div>
            </div>
        
            <!-- Enhanced Interactive Legend -->
            <div class="p-6 bg-gradient-to-r from-gray-50 to-blue-50/30 border-b border-gray-200/50">
                <div class="flex items-center justify-between">
                    <h4 class="text-lg font-bold text-gray-800 flex items-center">
                        <i class="fas fa-palette mr-3 text-blue-600"></i>
                        Condition Legend
                    </h4>
                </div>
                <div id="legendContent" class="hidden mt-6">
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
            </div>

            <!-- Realistic Jaw Interface -->
            <div class="p-8 bg-gradient-to-br from-gray-50 via-white to-blue-50/30">
                <div class="max-w-6xl mx-auto">
                    <!-- Jaw Interface Container -->
                    <div class="relative bg-gradient-to-br from-white to-gray-50/50 backdrop-blur-sm rounded-3xl p-8 shadow-2xl border border-white/30 overflow-hidden">
                        <div class="absolute inset-0 bg-gradient-to-br from-pink-50/30 via-white to-blue-50/20 rounded-3xl"></div>
                        <div id="odontogramSvgWrapper" class="relative z-10 min-h-[320px] flex items-center justify-center">
                            <?php
                            $svgPath = APPPATH . 'Views/odontogram/odontogram.svg';
                            $odontogramSvg = '';
                            if (is_file($svgPath)) {
                                $odontogramSvg = preg_replace('/<\?xml.*?\?>/i', '', file_get_contents($svgPath), 1);
                            }
                            ?>
                            <?= $odontogramSvg ?>
                        </div>
                    </div>
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
                                    <td class="py-3 px-4 font-semibold text-blue-600"><?= $i ?></td>
                                    <td class="py-3 px-4 text-gray-600"><?= getFDINumber($i) ?></td>
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

<div id="toothModal" class="fixed inset-0 bg-black/50 backdrop-blur-sm hidden z-50 transition-opacity duration-300">
    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="bg-white/95 backdrop-blur-md rounded-2xl shadow-2xl max-w-xl w-full transform transition-all duration-300 scale-95 opacity-0" id="modalContent">
            <div class="bg-gradient-to-r from-amber-500 via-orange-500 to-red-500 text-white p-4 rounded-t-2xl relative overflow-hidden">
                <div class="absolute inset-0 bg-black/10"></div>
                <div class="relative z-10 flex items-center justify-between">
                    <div class="flex items-center space-x-3">
                        <div class="w-10 h-10 bg-white/20 rounded-lg flex items-center justify-center backdrop-blur-sm">
                            <i class="fas fa-layer-group text-white text-lg"></i>
                        </div>
                        <div>
                            <h3 class="text-lg font-bold leading-tight">Tooth <span id="modalToothNumber"></span></h3>
                            <p class="text-orange-100 text-xs">Apply condition details</p>
                        </div>
                    </div>
                    <button type="button" onclick="closeToothModal()" class="w-8 h-8 bg-white/20 hover:bg-white/30 rounded-lg flex items-center justify-center transition-colors duration-200">
                        <i class="fas fa-times text-white"></i>
                    </button>
                </div>
            </div>
            <div class="p-4">
                <form id="toothForm" class="space-y-3.5">
                    <?= csrf_field() ?>
                    <input type="hidden" id="toothNumber" name="tooth_number">
                    <input type="hidden" name="patient_id" value="<?= $patient['id'] ?>">
                    <input type="hidden" name="examination_id" value="<?= esc($examination_id ?? '') ?>">

                    <div>
                        <label for="condition_type" class="block text-sm font-semibold text-gray-700 mb-1.5">Condition Type</label>
                        <select id="condition_type" name="condition_type" class="w-full px-3 py-2.5 border border-gray-300 rounded-xl bg-white/70" required>
                            <option value="">Select a condition type</option>
                            <?php foreach ($condition_types as $key => $label): ?>
                                <option value="<?= $key ?>"><?= $label ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div>
                        <label for="condition_description" class="block text-sm font-semibold text-gray-700 mb-1.5">Description</label>
                        <textarea id="condition_description" name="condition_description" rows="2" class="w-full px-3 py-2.5 border border-gray-300 rounded-xl bg-white/70 resize-none" placeholder="Optional description..."></textarea>
                    </div>

                    <div>
                        <label for="treatment_notes" class="block text-sm font-semibold text-gray-700 mb-1.5">Treatment Notes</label>
                        <textarea id="treatment_notes" name="treatment_notes" rows="2" class="w-full px-3 py-2.5 border border-gray-300 rounded-xl bg-white/70 resize-none" placeholder="Optional treatment notes..."></textarea>
                    </div>

                    <div>
                        <label for="treatment_status" class="block text-sm font-semibold text-gray-700 mb-1.5">Treatment Status</label>
                        <select id="treatment_status" name="treatment_status" class="w-full px-3 py-2.5 border border-gray-300 rounded-xl bg-white/70">
                            <option value="pending">Pending</option>
                            <option value="in_progress">In Progress</option>
                            <option value="completed">Completed</option>
                            <option value="needs_attention">Needs Attention</option>
                        </select>
                    </div>

                    <div class="flex items-center justify-between gap-4 pt-3 border-t border-gray-200">
                        <div class="flex items-center gap-3">
                            <button type="button" onclick="closeToothModal()" class="inline-flex items-center justify-center h-10 px-3 bg-gray-100 hover:bg-gray-200 border border-gray-300 rounded-xl text-sm font-medium text-gray-700 whitespace-nowrap">
                                Cancel
                            </button>
                            <button type="button" onclick="resetTooth()" class="inline-flex items-center justify-center px-4 py-2.5 bg-red-100 hover:bg-red-200 border border-red-300 rounded-xl text-sm font-medium text-red-700 whitespace-nowrap">
                                Remove Status
                            </button>
                        </div>
                        <button type="submit" class="inline-flex items-center justify-center h-10 px-4 min-w-[160px] bg-gradient-to-r from-amber-600 to-orange-600 hover:from-amber-700 hover:to-orange-700 text-white rounded-xl text-sm font-medium shadow-lg whitespace-nowrap">
                            <i class="fas fa-save mr-2"></i>Apply Changes
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<div id="batchModal" class="fixed inset-0 bg-black/50 backdrop-blur-sm hidden z-50 transition-opacity duration-300">
    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="bg-white/95 backdrop-blur-md rounded-2xl shadow-2xl max-w-xl w-full transform transition-all duration-300 scale-95 opacity-0" id="batchModalContent">
            <div class="bg-gradient-to-r from-amber-500 via-orange-500 to-red-500 text-white p-4 rounded-t-2xl relative overflow-hidden">
                <div class="absolute inset-0 bg-black/10"></div>
                <div class="relative z-10 flex items-center justify-between">
                    <div class="flex items-center space-x-3">
                        <div class="w-10 h-10 bg-white/20 rounded-lg flex items-center justify-center backdrop-blur-sm">
                            <i class="fas fa-layer-group text-white text-lg"></i>
                        </div>
                        <div>
                            <h3 class="text-lg font-bold leading-tight">Apply to Selected Teeth</h3>
                            <p class="text-orange-100 text-xs">Selected: <span id="batchModalCount">0</span> teeth</p>
                        </div>
                    </div>
                    <button type="button" onclick="closeBatchModal()" class="w-8 h-8 bg-white/20 hover:bg-white/30 rounded-lg flex items-center justify-center transition-colors duration-200">
                        <i class="fas fa-times text-white"></i>
                    </button>
                </div>
            </div>
            <div class="p-4">
                <form id="batchForm" class="space-y-3.5">
                    <div>
                        <label for="batch_condition_type" class="block text-sm font-semibold text-gray-700 mb-1.5">Condition Type</label>
                        <select id="batch_condition_type" name="condition_type" class="w-full px-3 py-2.5 border border-gray-300 rounded-xl bg-white/70" required>
                            <option value="">Select a condition type</option>
                            <?php foreach ($condition_types as $key => $label): ?>
                                <option value="<?= $key ?>"><?= $label ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div>
                        <label for="batch_condition_description" class="block text-sm font-semibold text-gray-700 mb-1.5">Description</label>
                        <textarea id="batch_condition_description" name="condition_description" rows="2" class="w-full px-3 py-2.5 border border-gray-300 rounded-xl bg-white/70 resize-none" placeholder="Optional shared description..."></textarea>
                    </div>
                    <div>
                        <label for="batch_treatment_notes" class="block text-sm font-semibold text-gray-700 mb-1.5">Treatment Notes</label>
                        <textarea id="batch_treatment_notes" name="treatment_notes" rows="2" class="w-full px-3 py-2.5 border border-gray-300 rounded-xl bg-white/70 resize-none" placeholder="Optional shared treatment notes..."></textarea>
                    </div>
                    <div>
                        <label for="batch_treatment_status" class="block text-sm font-semibold text-gray-700 mb-1.5">Treatment Status</label>
                        <select id="batch_treatment_status" name="treatment_status" class="w-full px-3 py-2.5 border border-gray-300 rounded-xl bg-white/70">
                            <option value="pending">Pending</option>
                            <option value="in_progress">In Progress</option>
                            <option value="completed">Completed</option>
                            <option value="needs_attention">Needs Attention</option>
                        </select>
                    </div>
                    <div class="flex items-center justify-between gap-4 pt-3 border-t border-gray-200">
                        <div class="flex items-center gap-3">
                            <button type="button" onclick="closeBatchModal()" class="inline-flex items-center justify-center h-10 px-3 bg-gray-100 hover:bg-gray-200 border border-gray-300 rounded-xl text-sm font-medium text-gray-700 whitespace-nowrap">
                                Cancel
                            </button>
                            <button type="button" onclick="batchResetSelected()" class="inline-flex items-center justify-center px-4 py-2.5 bg-red-100 hover:bg-red-200 border border-red-300 rounded-xl text-sm font-medium text-red-700 whitespace-nowrap">
                                Remove Status
                            </button>
                        </div>
                        <button type="submit" id="batchSubmitBtn" class="inline-flex items-center justify-center h-10 px-4 min-w-[160px] bg-gradient-to-r from-amber-600 to-orange-600 hover:from-amber-700 hover:to-orange-700 text-white rounded-xl text-sm font-medium shadow-lg whitespace-nowrap">
                            <i class="fas fa-save mr-2"></i>Apply Changes
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
const patientId = <?= $patient['id'] ?>;
const examinationId = <?= isset($examination_id) && $examination_id ? (int)$examination_id : 'null' ?>;
const odontogramData = <?= json_encode($odontogram) ?>;
const conditionTypes = <?= json_encode($condition_types) ?>;
const svgNS = 'http://www.w3.org/2000/svg';
const conditionColors = {
    cavity: '#fbbf24',
    filling: '#3b82f6',
    crown: '#a855f7',
    root_canal: '#ec4899',
    extracted: '#ef4444',
    implant: '#6366f1',
    bridge: '#0ea5e9',
    partial_denture: '#f97316',
    full_denture: '#f59e0b',
    other: '#6b7280'
};

const toothGroups = new Map();
const toothOverlays = new Map();
const toothOutlines = new Map();
const toothHits = new Map();
let activeTooth = null;
let tooltipEl = null;
let batchModeEnabled = false;
const batchSelectedTeeth = new Set();

document.addEventListener('DOMContentLoaded', () => {
    setupSvgOdontogram();
    initializeOdontogram();
    attachModalBehavior();
    attachFormHandler();
    attachBatchHandler();
    updateBatchUi();
});

function getFdiNumber(universalNumber) {
    const num = parseInt(universalNumber, 10);
    if (isNaN(num)) {
        return universalNumber;
    }

    if (num >= 1 && num <= 8) {
        return (10 + num).toString();
    }
    if (num >= 9 && num <= 16) {
        return (12 + num).toString();
    }
    if (num >= 17 && num <= 24) {
        return (14 + num).toString();
    }
    if (num >= 25 && num <= 32) {
        return (16 + num).toString();
    }

    return num.toString();
}

function setOutlineVisible(toothNumber, visible) {
    const outline = toothOutlines.get(toothNumber);
    if (!outline) {
        return;
    }
    outline.style.opacity = visible ? '0.9' : '0';
}

function isToothBatchSelected(toothNumber) {
    return batchSelectedTeeth.has(parseInt(toothNumber, 10));
}

function syncBatchToothVisual(toothNumber) {
    const tooth = parseInt(toothNumber, 10);
    const group = toothGroups.get(tooth);
    if (!group) {
        return;
    }
    const selected = isToothBatchSelected(tooth);
    group.classList.toggle('is-batch-selected', selected);
    if (selected) {
        setOutlineVisible(tooth, true);
    } else if (!group.classList.contains('is-hover') && !group.classList.contains('is-selected')) {
        setOutlineVisible(tooth, false);
    }
}

function updateBatchUi() {
    const toggleBtn = document.getElementById('batchModeToggleBtn');
    const toggleText = document.getElementById('batchModeToggleText');
    const topControls = document.getElementById('batchTopControls');
    const countEl = document.getElementById('batchTopCount');
    const applyBtn = document.getElementById('batchApplyBtn');
    const count = batchSelectedTeeth.size;

    if (toggleBtn) {
        toggleBtn.classList.toggle('bg-amber-500', batchModeEnabled);
        toggleBtn.classList.toggle('hover:bg-amber-600', batchModeEnabled);
        toggleBtn.classList.toggle('border-amber-300', batchModeEnabled);
        toggleBtn.classList.toggle('shadow-lg', batchModeEnabled);
        toggleBtn.classList.toggle('bg-white/20', !batchModeEnabled);
        toggleBtn.classList.toggle('hover:bg-white/30', !batchModeEnabled);
        toggleBtn.classList.toggle('border-white/30', !batchModeEnabled);
        toggleBtn.classList.toggle('shadow-none', !batchModeEnabled);
    }
    if (toggleText) {
        toggleText.textContent = batchModeEnabled ? 'Batch Mode On' : 'Batch Select';
    }
    if (topControls) {
        topControls.classList.toggle('hidden', !batchModeEnabled);
        topControls.classList.toggle('flex', batchModeEnabled);
    }
    if (countEl) {
        countEl.textContent = `${count} selected`;
    }
    if (applyBtn) {
        const enabled = count > 0;
        applyBtn.disabled = !enabled;
        applyBtn.classList.toggle('bg-gradient-to-r', enabled);
        applyBtn.classList.toggle('from-emerald-500', enabled);
        applyBtn.classList.toggle('to-green-600', enabled);
        applyBtn.classList.toggle('hover:from-emerald-600', enabled);
        applyBtn.classList.toggle('hover:to-green-700', enabled);
        applyBtn.classList.toggle('border-emerald-300/70', enabled);
        applyBtn.classList.toggle('shadow-lg', enabled);
        applyBtn.classList.toggle('text-white', enabled);

        applyBtn.classList.toggle('bg-white/20', !enabled);
        applyBtn.classList.toggle('hover:bg-white/20', !enabled);
        applyBtn.classList.toggle('border-white/30', !enabled);
        applyBtn.classList.toggle('shadow-none', !enabled);
        applyBtn.classList.toggle('text-white/70', !enabled);
    }
}

function clearBatchSelection() {
    Array.from(batchSelectedTeeth).forEach(tooth => {
        batchSelectedTeeth.delete(tooth);
        syncBatchToothVisual(tooth);
    });
    updateBatchUi();
}

function toggleBatchMode(forceState = null) {
    const shouldEnable = forceState === null ? !batchModeEnabled : !!forceState;
    if (shouldEnable === batchModeEnabled) {
        return;
    }

    batchModeEnabled = shouldEnable;
    if (batchModeEnabled) {
        closeToothModal();
    } else {
        clearBatchSelection();
        closeBatchModal();
    }
    updateBatchUi();
}

function toggleBatchTooth(toothNumber) {
    const tooth = parseInt(toothNumber, 10);
    if (!batchModeEnabled || !tooth) {
        return;
    }
    if (batchSelectedTeeth.has(tooth)) {
        batchSelectedTeeth.delete(tooth);
    } else {
        batchSelectedTeeth.add(tooth);
    }
    syncBatchToothVisual(tooth);
    updateBatchUi();
}

function createTooltip() {
    const el = document.createElement('div');
    el.id = 'odontogramTooltip';
    el.className = 'fixed z-50 pointer-events-none px-3 py-2 rounded-md text-xs font-medium shadow-lg bg-slate-900 text-white opacity-0 transition-opacity duration-150';
    document.body.appendChild(el);
    return el;
}

function showTooltip(event, toothNumber) {
    if (!tooltipEl) {
        tooltipEl = createTooltip();
    }

    const condition = getConditionForTooth(toothNumber);
    const conditionLabel = conditionTypes?.[condition] || 'Healthy';
    tooltipEl.textContent = `Tooth ${toothNumber} (FDI ${getFdiNumber(toothNumber)}) - ${conditionLabel}`;
    tooltipEl.style.left = `${event.clientX + 12}px`;
    tooltipEl.style.top = `${event.clientY + 12}px`;
    tooltipEl.style.opacity = '1';
}

function moveTooltip(event) {
    if (!tooltipEl || tooltipEl.style.opacity !== '1') {
        return;
    }
    tooltipEl.style.left = `${event.clientX + 12}px`;
    tooltipEl.style.top = `${event.clientY + 12}px`;
}

function hideTooltip() {
    if (!tooltipEl) {
        return;
    }
    tooltipEl.style.opacity = '0';
}

function parseToothNumber(element) {
    if (!element) {
        return null;
    }
    const dataTooth = element.dataset?.tooth;
    if (dataTooth) {
        return parseInt(dataTooth, 10);
    }
    const id = element.getAttribute('id') || '';
    const match = id.match(/^tooth-(\d+)$/);
    if (match) {
        return parseInt(match[1], 10);
    }
    return null;
}

function getLargestShape(group) {
    const shapes = Array.from(group.querySelectorAll('path, polygon, rect, circle, ellipse'))
        .filter(el =>
            !el.classList.contains('tooth-hit') &&
            !el.classList.contains('tooth-overlay') &&
            !el.classList.contains('tooth-outline')
        );

    const getOpacity = (el) => {
        const attr = el.getAttribute('opacity');
        if (attr !== null && attr !== '') {
            const value = Number(attr);
            return Number.isFinite(value) ? value : 1;
        }
        const styleValue = el.style && el.style.opacity ? Number(el.style.opacity) : NaN;
        return Number.isFinite(styleValue) ? styleValue : 1;
    };

    const inMultiplyGroup = (el) => {
        let node = el;
        while (node && node !== group) {
            if (node.getAttribute && (node.getAttribute('mix-blend-mode') || '').toLowerCase() === 'multiply') {
                return true;
            }
            node = node.parentNode;
        }
        return false;
    };

    const isVisibleFill = (el) => {
        const fill = (el.getAttribute('fill') || '').trim().toLowerCase();
        if (!fill || fill === 'none' || fill === 'transparent') {
            return false;
        }
        return true;
    };

    const filled = shapes.filter(isVisibleFill);
    const candidates = filled.length ? filled : shapes;
    let best = null;
    let bestScore = -1;

    candidates.forEach(shape => {
        let bbox;
        try {
            bbox = shape.getBBox();
        } catch (e) {
            return;
        }
        const area = bbox.width * bbox.height;
        if (!area || area <= 0) {
            return;
        }

        const opacity = Math.max(0, Math.min(1, getOpacity(shape)));
        const multiplyPenalty = inMultiplyGroup(shape) ? 0.15 : 1;
        const score = area * (0.25 + 0.75 * opacity) * multiplyPenalty;

        if (score > bestScore) {
            bestScore = score;
            best = shape;
        }
    });

    return best;
}

function ensureToothControls(group, toothNumber) {
    group.querySelectorAll('.tooth-overlay, .tooth-outline, .tooth-hit').forEach(el => el.remove());
    let overlay = null;
    let outline = null;
    let hit = null;
    const base = getLargestShape(group);
    if (base) {
        group.querySelectorAll('path, polygon, rect, circle, ellipse').forEach(shape => {
            if (!shape.classList.contains('tooth-hit') && !shape.classList.contains('tooth-overlay') && !shape.classList.contains('tooth-outline')) {
                shape.style.pointerEvents = 'none';
            }
        });

        if (!overlay) {
            overlay = base.cloneNode(true);
            overlay.removeAttribute('id');
            overlay.setAttribute('class', 'tooth-overlay');
            overlay.setAttribute('fill', 'transparent');
            overlay.setAttribute('opacity', '0');
            overlay.style.pointerEvents = 'none';
            group.appendChild(overlay);
        }
        if (!outline) {
            outline = base.cloneNode(true);
            outline.removeAttribute('id');
            outline.setAttribute('class', 'tooth-outline');
            outline.setAttribute('fill', 'none');
            outline.setAttribute('stroke', '#3b82f6');
            outline.setAttribute('stroke-width', '1.5');
            outline.setAttribute('opacity', '0');
            outline.style.pointerEvents = 'none';
            group.appendChild(outline);
        }
        if (!hit) {
            hit = base.cloneNode(true);
            hit.removeAttribute('id');
            hit.setAttribute('class', 'tooth-hit');
            hit.setAttribute('fill', 'transparent');
            hit.setAttribute('opacity', '0');
            hit.style.pointerEvents = 'fill';
            group.appendChild(hit);
        }
    }

    if (hit) {
        hit.style.cursor = 'pointer';
        hit.setAttribute('tabindex', '0');
        hit.setAttribute('role', 'button');
        hit.setAttribute('aria-label', `Tooth ${toothNumber} (FDI ${getFdiNumber(toothNumber)})`);
    }

    if (overlay) {
        overlay.style.pointerEvents = 'none';
    }
    if (outline) {
        outline.style.pointerEvents = 'none';
    }

    return { overlay, outline, hit };
}

function cacheToothGroups(svg) {
    toothGroups.clear();
    toothOverlays.clear();
    toothOutlines.clear();
    toothHits.clear();

    const groups = Array.from(svg.querySelectorAll('[id^=\"tooth-\"]'))
        .filter(element => (element.tagName || '').toLowerCase().endsWith('g'));
    groups.forEach(group => {
        const toothNumber = parseToothNumber(group);
        if (!toothNumber) {
            return;
        }

        group.classList.add('tooth-group');
        group.dataset.tooth = toothNumber;

        const { overlay, outline, hit } = ensureToothControls(group, toothNumber);

        if (overlay) {
            toothOverlays.set(toothNumber, overlay);
        }
        if (outline) {
            toothOutlines.set(toothNumber, outline);
        }
        if (hit) {
            toothHits.set(toothNumber, hit);
            hit.addEventListener('mouseenter', event => {
                group.classList.add('is-hover');
                setOutlineVisible(toothNumber, true);
                showTooltip(event, toothNumber);
            });
            hit.addEventListener('mousemove', moveTooltip);
            hit.addEventListener('mouseleave', () => {
                group.classList.remove('is-hover');
                if (!group.classList.contains('is-selected') && !group.classList.contains('is-batch-selected')) {
                    setOutlineVisible(toothNumber, false);
                }
                hideTooltip();
            });
            hit.addEventListener('focus', event => {
                group.classList.add('is-hover');
                setOutlineVisible(toothNumber, true);
                showTooltip(event, toothNumber);
            });
            hit.addEventListener('blur', () => {
                group.classList.remove('is-hover');
                if (!group.classList.contains('is-selected') && !group.classList.contains('is-batch-selected')) {
                    setOutlineVisible(toothNumber, false);
                }
                hideTooltip();
            });
            hit.addEventListener('click', () => {
                if (batchModeEnabled) {
                    toggleBatchTooth(toothNumber);
                } else {
                    openToothModal(toothNumber);
                }
            });
            hit.addEventListener('keydown', event => {
                if (event.key === 'Enter' || event.key === ' ') {
                    event.preventDefault();
                    if (batchModeEnabled) {
                        toggleBatchTooth(toothNumber);
                    } else {
                        openToothModal(toothNumber);
                    }
                }
            });
        }

        toothGroups.set(toothNumber, group);
    });
}

function injectConditionFilters(svg) {
    let defs = svg.querySelector('defs');
    if (!defs) {
        defs = document.createElementNS(svgNS, 'defs');
        svg.prepend(defs);
    }

    Object.entries(conditionFilterColors).forEach(([condition, color]) => {
        const filterId = `cond-${condition}`;
        if (svg.getElementById(filterId)) {
            return;
        }

        const filter = document.createElementNS(svgNS, 'filter');
        filter.setAttribute('id', filterId);
        filter.setAttribute('filterUnits', 'objectBoundingBox');

        const flood = document.createElementNS(svgNS, 'feFlood');
        flood.setAttribute('flood-color', color);
        flood.setAttribute('flood-opacity', '0.72');
        flood.setAttribute('result', 'flood');

        const composite = document.createElementNS(svgNS, 'feComposite');
        composite.setAttribute('in', 'flood');
        composite.setAttribute('in2', 'SourceAlpha');
        composite.setAttribute('operator', 'in');
        composite.setAttribute('result', 'mask');

        const merge = document.createElementNS(svgNS, 'feMerge');
        const mergeNodeFlood = document.createElementNS(svgNS, 'feMergeNode');
        mergeNodeFlood.setAttribute('in', 'mask');
        const mergeNodeGraphic = document.createElementNS(svgNS, 'feMergeNode');
        mergeNodeGraphic.setAttribute('in', 'SourceGraphic');
        merge.appendChild(mergeNodeFlood);
        merge.appendChild(mergeNodeGraphic);

        filter.appendChild(flood);
        filter.appendChild(composite);
        filter.appendChild(merge);
        defs.appendChild(filter);
    });
}

function setupSvgOdontogram() {
    const wrapper = document.getElementById('odontogramSvgWrapper');
    if (!wrapper) {
        console.warn('Odontogram SVG wrapper not found');
        return;
    }

    const svg = wrapper.querySelector('svg');
    if (!svg) {
        console.warn('Inline odontogram SVG is missing');
        return;
    }

    svg.id = svg.id || 'odontogramSvg';
    svg.removeAttribute('width');
    svg.removeAttribute('height');
    svg.style.width = '100%';
    svg.style.height = 'auto';

    cacheToothGroups(svg);
}

function initializeOdontogram() {
    toothGroups.forEach((_, toothNumber) => {
        applyToothVisual(toothNumber);
    });

    updateStats();
}

function getConditionForTooth(toothNumber) {
    const record = odontogramData.find(t => parseInt(t.tooth_number, 10) === parseInt(toothNumber, 10));
    return record ? record.condition_type : 'healthy';
}

function applyToothVisual(toothNumber) {
    const overlay = toothOverlays.get(parseInt(toothNumber, 10));
    if (!overlay) {
        return;
    }

    const condition = getConditionForTooth(toothNumber);
    if (!condition || condition === 'healthy') {
        overlay.setAttribute('opacity', '0');
        overlay.setAttribute('fill', 'transparent');
    } else {
        overlay.setAttribute('fill', conditionColors[condition] || '#6b7280');
        overlay.setAttribute('opacity', '0.28');
    }
}

function getToothNode(toothNumber) {
    if (!toothNumber) {
        return null;
    }

    return toothGroups.get(parseInt(toothNumber, 10));
}

function updateStats() {
    const stats = {
        total_conditions: odontogramData.length,
        healthy_count: odontogramData.filter(t => t.condition_type === 'healthy').length,
        needs_treatment_count: odontogramData.filter(t => ['cavity', 'extracted'].includes(t.condition_type)).length,
        cavities: odontogramData.filter(t => t.condition_type === 'cavity').length,
        fillings: odontogramData.filter(t => t.condition_type === 'filling').length,
        crowns: odontogramData.filter(t => t.condition_type === 'crown').length,
        extracted: odontogramData.filter(t => t.condition_type === 'extracted').length
    };

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
}

function setSelectedTooth(toothNumber) {
    if (activeTooth) {
        const previous = toothGroups.get(parseInt(activeTooth, 10));
        previous?.classList.remove('is-selected');
    }

    activeTooth = toothNumber;
    const current = toothGroups.get(parseInt(toothNumber, 10));
    current?.classList.add('is-selected');
}

function clearSelectedTooth() {
    if (!activeTooth) {
        return;
    }
    const tooth = parseInt(activeTooth, 10);
    const current = toothGroups.get(tooth);
    if (current) {
        current.classList.remove('is-selected');
        current.classList.remove('is-hover');
    }
    if (!isToothBatchSelected(tooth)) {
        setOutlineVisible(tooth, false);
    }
    hideTooltip();
    activeTooth = null;
}

function openToothModal(toothNumber) {
    if (batchModeEnabled) {
        showNotification('Exit batch mode to edit a single tooth.', 'warning');
        return;
    }

    const modal = document.getElementById('toothModal');
    const modalContent = document.getElementById('modalContent');
    const conditionTypeEl = document.getElementById('condition_type');
    const descriptionEl = document.getElementById('condition_description');
    const notesEl = document.getElementById('treatment_notes');
    const statusEl = document.getElementById('treatment_status');

    if (!modal || !modalContent) {
        return;
    }

    const modalNumber = document.getElementById('modalToothNumber');
    const toothInput = document.getElementById('toothNumber');
    modalNumber.textContent = toothNumber;
    toothInput.value = toothNumber;

    const existingTooth = odontogramData.find(t => parseInt(t.tooth_number, 10) === parseInt(toothNumber, 10));

    if (existingTooth) {
        conditionTypeEl.value = existingTooth.condition_type || 'healthy';
        descriptionEl.value = existingTooth.condition_description || '';
        notesEl.value = existingTooth.treatment_notes || '';
        statusEl.value = existingTooth.treatment_status || 'pending';
    } else {
        document.getElementById('toothForm').reset();
        conditionTypeEl.value = 'healthy';
        statusEl.value = 'pending';
    }

    modal.classList.remove('hidden');
    setTimeout(() => {
        modalContent.style.transform = 'scale(1)';
        modalContent.style.opacity = '1';
    }, 10);

    setSelectedTooth(toothNumber);
}

function closeToothModal() {
    const modal = document.getElementById('toothModal');
    const modalContent = document.getElementById('modalContent');

    if (!modal || !modalContent) {
        return;
    }

    modalContent.style.transform = 'scale(0.95)';
    modalContent.style.opacity = '0';

    setTimeout(() => {
        modal.classList.add('hidden');
    }, 300);

    clearSelectedTooth();
}

function openBatchModal() {
    if (!batchModeEnabled) {
        showNotification('Enable batch mode first.', 'warning');
        return;
    }
    const selected = batchSelectedTeeth.size;
    if (selected === 0) {
        showNotification('Select at least one tooth first.', 'warning');
        return;
    }

    const modal = document.getElementById('batchModal');
    const content = document.getElementById('batchModalContent');
    const countEl = document.getElementById('batchModalCount');
    if (!modal || !content) {
        return;
    }
    if (countEl) {
        countEl.textContent = String(selected);
    }
    modal.classList.remove('hidden');
    setTimeout(() => {
        content.style.transform = 'scale(1)';
        content.style.opacity = '1';
    }, 10);
}

function closeBatchModal() {
    const modal = document.getElementById('batchModal');
    const content = document.getElementById('batchModalContent');
    if (!modal || !content) {
        return;
    }
    content.style.transform = 'scale(0.95)';
    content.style.opacity = '0';
    setTimeout(() => {
        modal.classList.add('hidden');
    }, 250);
}

function upsertToothData(toothNumber, values) {
    const tooth = parseInt(toothNumber, 10);
    const existingIndex = odontogramData.findIndex(t => parseInt(t.tooth_number, 10) === tooth);
    const updated = {
        tooth_number: tooth,
        condition_type: values.condition_type,
        condition_description: values.condition_description || '',
        treatment_notes: values.treatment_notes || '',
        treatment_status: values.treatment_status || 'pending',
        treatment_date: new Date().toISOString().split('T')[0],
        created_by: <?= (int) ($user->id ?? 0) ?>,
        examination_id: examinationId || null
    };
    if (existingIndex !== -1) {
        odontogramData[existingIndex] = updated;
    } else {
        odontogramData.push(updated);
    }
}

async function submitToothUpdate(toothNumber, values) {
    const form = document.getElementById('toothForm');
    const payload = form ? new FormData(form) : new FormData();
    payload.set('patient_id', String(patientId));
    payload.set('examination_id', examinationId ? String(examinationId) : '');
    payload.set('tooth_number', String(toothNumber));
    payload.set('condition_type', values.condition_type);
    payload.set('condition_description', values.condition_description || '');
    payload.set('treatment_notes', values.treatment_notes || '');
    payload.set('treatment_status', values.treatment_status || 'pending');

    const response = await fetch('<?= base_url('odontogram/update-tooth') ?>', {
        method: 'POST',
        body: payload
    });
    if (!response.ok) {
        throw new Error(`HTTP error! status: ${response.status}`);
    }
    const data = await response.json();
    if (data.csrf_token) {
        window.refreshCsrfToken(data.csrf_token);
    }
    if (!data.success) {
        throw new Error(data.message || 'Failed to update tooth condition');
    }
}

function attachModalBehavior() {
    const modal = document.getElementById('toothModal');
    const modalContent = document.getElementById('modalContent');
    const batchModal = document.getElementById('batchModal');
    const batchModalContent = document.getElementById('batchModalContent');
    if (modal) {
        modal.addEventListener('click', () => {
            closeToothModal();
        });
    }
    if (modalContent) {
        modalContent.addEventListener('click', event => {
            event.stopPropagation();
        });
    }
    if (batchModal) {
        batchModal.addEventListener('click', () => {
            closeBatchModal();
        });
    }
    if (batchModalContent) {
        batchModalContent.addEventListener('click', event => {
            event.stopPropagation();
        });
    }

    document.addEventListener('keydown', event => {
        if (event.key === 'Escape') {
            closeToothModal();
            closeBatchModal();
        }

        const modalOpen = modal && !modal.classList.contains('hidden');
        if ((event.ctrlKey || event.metaKey) && event.key.toLowerCase() === 's' && modalOpen) {
            event.preventDefault();
            document.getElementById('toothForm').dispatchEvent(new Event('submit', { cancelable: true }));
        }
    });
}

function attachFormHandler() {
    const form = document.getElementById('toothForm');
    if (!form) {
        return;
    }

    form.addEventListener('submit', function (event) {
        event.preventDefault();
        const formData = new FormData(this);
        const submitButton = this.querySelector('button[type="submit"]');

        if (!submitButton) {
            return;
        }

        if (!submitButton._originalChildren) {
            submitButton._originalChildren = Array.from(submitButton.childNodes).map(node => node.cloneNode(true));
        }

        submitButton.disabled = true;
        submitButton.classList.add('opacity-75');

        const spinnerIcon = document.createElement('i');
        spinnerIcon.className = 'fas fa-spinner fa-spin mr-2';
        submitButton.replaceChildren(spinnerIcon, document.createTextNode('Saving...'));

        const toothNumber = parseInt(formData.get('tooth_number'), 10);
        const toothElement = getToothNode(toothNumber);
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
                    const condition = formData.get('condition_type');
                    const existingIndex = odontogramData.findIndex(t => parseInt(t.tooth_number, 10) === toothNumber);
                    const updatedToothData = {
                        tooth_number: toothNumber,
                        condition_type: condition,
                        condition_description: formData.get('condition_description'),
                        treatment_notes: formData.get('treatment_notes'),
                        treatment_status: formData.get('treatment_status'),
                        treatment_date: new Date().toISOString().split('T')[0],
                        created_by: <?= (int) ($user->id ?? 0) ?>,
                        examination_id: examinationId || null
                    };

                    if (existingIndex !== -1) {
                        odontogramData[existingIndex] = updatedToothData;
                    } else {
                        odontogramData.push(updatedToothData);
                    }

                    if (toothElement) {
                        toothElement.classList.remove('updating');
                        toothElement.classList.add('success');
                        setTimeout(() => {
                            toothElement.classList.remove('success');
                        }, 600);
                    }

                    applyToothVisual(toothNumber);

                    showNotification('Tooth condition updated successfully!', 'success');
                    closeToothModal();
                    updateStats();
                } else {
                    throw new Error(data.message || 'Failed to update tooth condition');
                }
            })
            .catch(error => {
                if (toothElement) {
                    toothElement.classList.remove('updating');
                }
                showNotification('Error: ' + error.message, 'error');
            })
            .finally(() => {
                submitButton.disabled = false;
                submitButton.replaceChildren(...submitButton._originalChildren.map(node => node.cloneNode(true)));
                submitButton.classList.remove('opacity-75');
            });
    });

    const legendToggleBtn = document.getElementById('legendToggleBtn');
    const legendContent = document.getElementById('legendContent');
    const legendToggleIcon = document.getElementById('legendToggleIcon');
    const legendToggleText = document.getElementById('legendToggleText');

    legendToggleBtn?.addEventListener('click', () => {
        if (!legendContent) {
            return;
        }
        const willShow = legendContent.classList.contains('hidden');
        legendContent.classList.toggle('hidden', !willShow);
        if (legendToggleIcon) {
            legendToggleIcon.classList.toggle('fa-chevron-down', !willShow);
            legendToggleIcon.classList.toggle('fa-chevron-up', willShow);
        }
        if (legendToggleText) {
            legendToggleText.textContent = willShow ? 'Hide Legend' : 'Show Legend';
        }
    });
}

function attachBatchHandler() {
    const form = document.getElementById('batchForm');
    const submitBtn = document.getElementById('batchSubmitBtn');
    if (!form || !submitBtn) {
        return;
    }

    form.addEventListener('submit', async event => {
        event.preventDefault();
        const selectedTeeth = Array.from(batchSelectedTeeth);
        if (!selectedTeeth.length) {
            showNotification('Select at least one tooth first.', 'warning');
            return;
        }

        const values = {
            condition_type: form.condition_type.value,
            condition_description: form.condition_description.value,
            treatment_notes: form.treatment_notes.value,
            treatment_status: form.treatment_status.value
        };
        if (!values.condition_type) {
            showNotification('Condition type is required.', 'warning');
            return;
        }

        submitBtn.disabled = true;
        const originalNodes = Array.from(submitBtn.childNodes).map((node) => node.cloneNode(true));
        submitBtn.replaceChildren();
        const spinnerIcon = document.createElement('i');
        spinnerIcon.className = 'fas fa-spinner fa-spin mr-2';
        submitBtn.appendChild(spinnerIcon);
        submitBtn.appendChild(document.createTextNode('Applying...'));

        let successCount = 0;
        const failedTeeth = [];

        for (const toothNumber of selectedTeeth) {
            try {
                await submitToothUpdate(toothNumber, values);
                upsertToothData(toothNumber, values);
                applyToothVisual(toothNumber);
                successCount++;
            } catch (error) {
                failedTeeth.push(toothNumber);
            }
        }

        updateStats();
        clearBatchSelection();
        closeBatchModal();

        if (successCount > 0) {
            showNotification(`Updated ${successCount} teeth successfully.`, 'success');
        }
        if (failedTeeth.length > 0) {
            showNotification(`Failed on teeth: ${failedTeeth.join(', ')}`, 'warning');
        }

        submitBtn.disabled = false;
        submitBtn.replaceChildren(...originalNodes);
    });
}

async function batchResetSelected() {
    const selectedTeeth = Array.from(batchSelectedTeeth);
    if (!selectedTeeth.length) {
        showNotification('Select at least one tooth first.', 'warning');
        return;
    }

    const resetValues = {
        condition_type: 'healthy',
        condition_description: '',
        treatment_notes: '',
        treatment_status: 'completed'
    };

    let successCount = 0;
    const failedTeeth = [];

    for (const toothNumber of selectedTeeth) {
        try {
            await submitToothUpdate(toothNumber, resetValues);
            upsertToothData(toothNumber, resetValues);
            applyToothVisual(toothNumber);
            successCount++;
        } catch (error) {
            failedTeeth.push(toothNumber);
        }
    }

    updateStats();
    clearBatchSelection();
    closeBatchModal();

    if (successCount > 0) {
        showNotification(`Removed status on ${successCount} teeth.`, 'success');
    }
    if (failedTeeth.length > 0) {
        showNotification(`Failed on teeth: ${failedTeeth.join(', ')}`, 'warning');
    }
}

function resetTooth() {
    const toothNumber = document.getElementById('toothNumber').value;
    if (!toothNumber) {
        return;
    }

    const toothElement = getToothNode(toothNumber);
    if (toothElement) {
        toothElement.classList.add('updating');
    }

    const headers = {
        'Content-Type': 'application/x-www-form-urlencoded'
    };

    if (window.csrfConfig?.header && typeof window.getCsrfToken === 'function') {
        headers[window.csrfConfig.header] = window.getCsrfToken();
    }

    fetch('<?= base_url('odontogram/reset-tooth') ?>', {
        method: 'POST',
        headers,
        body: `patient_id=${patientId}&tooth_number=${toothNumber}${examinationId ? `&examination_id=${examinationId}` : ''}`
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
                    setTimeout(() => {
                        toothElement.classList.remove('success');
                    }, 600);
                }

                const existingIndex = odontogramData.findIndex(t => parseInt(t.tooth_number, 10) === parseInt(toothNumber, 10));
                const healthyEntry = {
                    tooth_number: toothNumber,
                    condition_type: 'healthy',
                    condition_description: '',
                    treatment_notes: '',
                    treatment_status: 'completed'
                };

                if (existingIndex !== -1) {
                    odontogramData[existingIndex] = healthyEntry;
                } else {
                    odontogramData.push(healthyEntry);
                }

                applyToothVisual(toothNumber);
                updateStats();
                showNotification('Tooth condition removed successfully!', 'success');
                closeToothModal();
            } else {
                throw new Error(data.message || 'Failed to reset tooth condition');
            }
        })
        .catch(error => {
            if (toothElement) {
                toothElement.classList.remove('updating');
            }
            showNotification('Error: ' + error.message, 'error');
        });
}

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
    closeBtn.onclick = function () {
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

    setTimeout(() => notification.classList.remove('translate-x-full'), 10);

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
        alert('Feature coming soon!');
    }
}

function markAllHealthy() {
    if (confirm('Are you sure you want to mark all teeth as healthy?')) {
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
</script>



<style>
#odontogramSvgWrapper svg {
    width: 100%;
    height: auto;
}

#odontogramSvgWrapper text {
    pointer-events: none;
    font-family: inherit;
}

#odontogramSvgWrapper .tooth-hit {
    outline: none;
}

#odontogramSvgWrapper .tooth-outline {
    stroke: #3b82f6;
    opacity: 0;
}

#odontogramSvgWrapper .tooth-overlay {
    pointer-events: none;
    mix-blend-mode: multiply;
}

#odontogramSvgWrapper .tooth-group {
    cursor: pointer;
}

#odontogramSvgWrapper .tooth-group.is-hover .tooth-outline {
    opacity: 0.9;
}

#odontogramSvgWrapper .tooth-group.is-selected .tooth-outline {
    opacity: 1;
    stroke: #f97316;
    stroke-width: 2;
}

#odontogramSvgWrapper .tooth-group.is-batch-selected .tooth-outline {
    opacity: 1;
    stroke: #f59e0b;
    stroke-width: 2;
}

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
