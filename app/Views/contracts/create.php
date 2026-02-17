<?php
$initialOdontogram = [];
if (!empty($snapshot['data_snapshot_json'])) {
    $decoded = json_decode($snapshot['data_snapshot_json'], true);
    if (is_array($decoded)) {
        $initialOdontogram = $decoded;
    }
}
if (empty($initialOdontogram)) {
    $initialOdontogram = $odontogram ?? [];
}
$normalizedInitial = [];
foreach ($initialOdontogram as $row) {
    if (!isset($row['tooth_number'])) {
        continue;
    }
    $normalizedInitial[] = [
        'tooth_number' => (int) $row['tooth_number'],
        'condition_type' => $row['condition_type'] ?? 'healthy',
        'condition_description' => $row['condition_description'] ?? '',
        'treatment_notes' => $row['treatment_notes'] ?? '',
        'treatment_status' => $row['treatment_status'] ?? 'pending'
    ];
}
$items = $items ?? [];
$contract = $contract ?? null;
$mode = $mode ?? 'create';
$actionUrl = $mode === 'edit' && $contract ? base_url('contracts/' . $contract['id'] . '/update') : base_url('contracts/create/' . $patient['id']);
$hasContractTerms = !empty($contract['terms_conditions'] ?? '')
    || !empty($contract['payment_terms'] ?? '')
    || !empty($contract['duration_plan'] ?? '')
    || !empty($contract['notes'] ?? '');
?>

<?= $this->extend('layouts/main_auth') ?>

<?= $this->section('content') ?>
<div class="min-h-screen bg-gradient-to-br from-slate-50 via-blue-50 to-indigo-50 contract-full-width">
    <div class="backdrop-blur-md bg-white/80 border-b border-white/20 shadow-sm">
        <div class="w-full max-w-none px-4 sm:px-6 lg:px-12 contract-shell">
            <div class="flex flex-col lg:flex-row lg:justify-between lg:items-center py-6 space-y-4 lg:space-y-0">
                <div class="flex items-center space-x-4">
                    <div class="w-12 h-12 bg-gradient-to-br from-blue-600 to-indigo-600 rounded-xl flex items-center justify-center shadow-lg">
                        <i class="fas fa-file-signature text-white text-xl"></i>
                    </div>
                    <div>
                        <h1 class="text-3xl font-bold bg-gradient-to-r from-gray-800 to-gray-600 bg-clip-text text-transparent">
                            <?= $mode === 'edit' ? 'Edit Treatment Contract' : 'Create Treatment Contract' ?>
                        </h1>
                        <p class="text-gray-600 mt-1 flex items-center">
                            <i class="fas fa-user-circle mr-2 text-blue-500"></i>
                            <?= esc($patient['first_name'] . ' ' . $patient['last_name']) ?>
                            <span class="ml-2 px-2 py-1 bg-blue-100 text-blue-800 text-xs font-medium rounded-full">
                                <?= esc($patient['patient_id'] ?? $patient['id']) ?>
                            </span>
                        </p>
                    </div>
                </div>
                <div class="flex flex-wrap gap-3">
                    <?php if (!empty($examination_id)): ?>
                        <a href="<?= base_url('examination/' . $examination_id) ?>" class="group relative inline-flex items-center px-4 py-2 bg-white/70 hover:bg-white border border-indigo-200 rounded-lg text-sm font-medium text-indigo-700 hover:text-indigo-900 shadow-sm hover:shadow-md transition-all duration-200">
                            <i class="fas fa-arrow-left mr-2 group-hover:-translate-x-1 transition-transform"></i>Back to Examination
                        </a>
                    <?php endif; ?>
                    <a href="<?= base_url('patient/' . $patient['id']) ?>" class="group relative inline-flex items-center px-4 py-2 bg-white/70 hover:bg-white border border-gray-200 rounded-lg text-sm font-medium text-gray-700 hover:text-gray-900 shadow-sm hover:shadow-md transition-all duration-200">
                        <i class="fas fa-arrow-left mr-2 group-hover:-translate-x-1 transition-transform"></i>Back to Patient
                    </a>
                </div>
            </div>
        </div>
    </div>

    <form id="contractForm" action="<?= $actionUrl ?>" method="post">
        <?= csrf_field() ?>
        <input type="hidden" name="odontogram_json" id="odontogramJson">
        <input type="hidden" name="odontogram_svg" id="odontogramSvg">
        <?php if (!empty($examination_id)): ?>
            <input type="hidden" name="examination_id" value="<?= (int) $examination_id ?>">
        <?php endif; ?>

        <div class="w-full max-w-none px-4 sm:px-6 lg:px-12 pb-12 contract-shell">
            <div class="space-y-6 mt-8 w-full max-w-none contract-grid">
                <div class="space-y-6">
                    <div class="bg-white/70 backdrop-blur-sm rounded-2xl shadow-xl border border-white/20 overflow-hidden">
                        <div class="bg-gradient-to-r from-blue-600 via-indigo-600 to-purple-600 text-white p-6 relative overflow-hidden">
                            <div class="absolute inset-0 bg-black/10"></div>
                            <div class="relative z-10 flex items-center justify-between">
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
                            <div class="absolute top-0 right-0 w-32 h-32 bg-white/5 rounded-full -translate-y-16 translate-x-16"></div>
                            <div class="absolute bottom-0 left-0 w-24 h-24 bg-white/5 rounded-full translate-y-12 -translate-x-12"></div>
                        </div>

                        <div class="p-6 bg-gradient-to-r from-gray-50 to-blue-50/30 border-b border-gray-200/50">
                            <div class="flex items-center justify-between">
                                <h4 class="text-lg font-bold text-gray-800 flex items-center">
                                    <i class="fas fa-palette mr-3 text-blue-600"></i>
                                    Condition Legend
                                </h4>
                                <button type="button"
                                        id="legendToggleBtn"
                                        class="inline-flex items-center px-3 py-2 bg-white/80 hover:bg-white text-xs font-semibold rounded-lg border border-gray-200 text-gray-700">
                                    <i id="legendToggleIcon" class="fas fa-chevron-down mr-2"></i>
                                    <span id="legendToggleText">Show</span>
                                </button>
                            </div>
                            <div id="legendContent" class="hidden mt-6">
                            <div class="grid grid-cols-6 gap-4">
                                <div class="group flex items-center space-x-3 p-4 bg-white/80 backdrop-blur-sm rounded-xl shadow-sm hover:shadow-lg transition-all duration-300 cursor-pointer border border-white/50 hover:border-green-200/50">
                                    <div class="w-8 h-8 bg-gradient-to-br from-green-100 to-green-200 border-2 border-green-400 rounded-full flex items-center justify-center group-hover:scale-110 transition-transform">
                                        <i class="fas fa-check text-green-600 text-sm"></i>
                                    </div>
                                    <div>
                                        <span class="text-sm font-semibold text-gray-800">Healthy</span>
                                        <div class="text-xs text-gray-500">No issues</div>
                                    </div>
                                </div>
                                <div class="group flex items-center space-x-3 p-4 bg-white/80 backdrop-blur-sm rounded-xl shadow-sm hover:shadow-lg transition-all duration-300 cursor-pointer border border-white/50 hover:border-yellow-200/50">
                                    <div class="w-8 h-8 bg-gradient-to-br from-yellow-100 to-yellow-200 border-2 border-yellow-400 rounded-full flex items-center justify-center group-hover:scale-110 transition-transform">
                                        <i class="fas fa-exclamation text-yellow-600 text-sm"></i>
                                    </div>
                                    <div>
                                        <span class="text-sm font-semibold text-gray-800">Cavity</span>
                                        <div class="text-xs text-gray-500">Needs filling</div>
                                    </div>
                                </div>
                                <div class="group flex items-center space-x-3 p-4 bg-white/80 backdrop-blur-sm rounded-xl shadow-sm hover:shadow-lg transition-all duration-300 cursor-pointer border border-white/50 hover:border-blue-200/50">
                                    <div class="w-8 h-8 bg-gradient-to-br from-blue-100 to-blue-200 border-2 border-blue-400 rounded-full flex items-center justify-center group-hover:scale-110 transition-transform">
                                        <i class="fas fa-tools text-blue-600 text-sm"></i>
                                    </div>
                                    <div>
                                        <span class="text-sm font-semibold text-gray-800">Filling</span>
                                        <div class="text-xs text-gray-500">Restored</div>
                                    </div>
                                </div>
                                <div class="group flex items-center space-x-3 p-4 bg-white/80 backdrop-blur-sm rounded-xl shadow-sm hover:shadow-lg transition-all duration-300 cursor-pointer border border-white/50 hover:border-purple-200/50">
                                    <div class="w-8 h-8 bg-gradient-to-br from-purple-100 to-purple-200 border-2 border-purple-400 rounded-full flex items-center justify-center group-hover:scale-110 transition-transform">
                                        <i class="fas fa-crown text-purple-600 text-sm"></i>
                                    </div>
                                    <div>
                                        <span class="text-sm font-semibold text-gray-800">Crown</span>
                                        <div class="text-xs text-gray-500">Capped</div>
                                    </div>
                                </div>
                                <div class="group flex items-center space-x-3 p-4 bg-white/80 backdrop-blur-sm rounded-xl shadow-sm hover:shadow-lg transition-all duration-300 cursor-pointer border border-white/50 hover:border-pink-200/50">
                                    <div class="w-8 h-8 bg-gradient-to-br from-pink-100 to-pink-200 border-2 border-pink-400 rounded-full flex items-center justify-center group-hover:scale-110 transition-transform">
                                        <i class="fas fa-tooth text-pink-600 text-sm"></i>
                                    </div>
                                    <div>
                                        <span class="text-sm font-semibold text-gray-800">Root Canal</span>
                                        <div class="text-xs text-gray-500">Endodontic</div>
                                    </div>
                                </div>
                                <div class="group flex items-center space-x-3 p-4 bg-white/80 backdrop-blur-sm rounded-xl shadow-sm hover:shadow-lg transition-all duration-300 cursor-pointer border border-white/50 hover:border-red-200/50">
                                    <div class="w-8 h-8 bg-gradient-to-br from-red-100 to-red-200 border-2 border-red-400 rounded-full flex items-center justify-center group-hover:scale-110 transition-transform">
                                        <i class="fas fa-times text-red-600 text-sm"></i>
                                    </div>
                                    <div>
                                        <span class="text-sm font-semibold text-gray-800">Extracted</span>
                                        <div class="text-xs text-gray-500">Removed</div>
                                    </div>
                                </div>
                                <div class="group flex items-center space-x-3 p-4 bg-white/80 backdrop-blur-sm rounded-xl shadow-sm hover:shadow-lg transition-all duration-300 cursor-pointer border border-white/50 hover:border-indigo-200/50">
                                    <div class="w-8 h-8 bg-gradient-to-br from-indigo-100 to-indigo-200 border-2 border-indigo-400 rounded-full flex items-center justify-center group-hover:scale-110 transition-transform">
                                        <i class="fas fa-screwdriver text-indigo-600 text-sm"></i>
                                    </div>
                                    <div>
                                        <span class="text-sm font-semibold text-gray-800">Implant</span>
                                        <div class="text-xs text-gray-500">Titanium</div>
                                    </div>
                                </div>
                                <div class="group flex items-center space-x-3 p-4 bg-white/80 backdrop-blur-sm rounded-xl shadow-sm hover:shadow-lg transition-all duration-300 cursor-pointer border border-white/50 hover:border-teal-200/50">
                                    <div class="w-8 h-8 bg-gradient-to-br from-teal-100 to-teal-200 border-2 border-teal-400 rounded-full flex items-center justify-center group-hover:scale-110 transition-transform">
                                        <i class="fas fa-link text-teal-600 text-sm"></i>
                                    </div>
                                    <div>
                                        <span class="text-sm font-semibold text-gray-800">Bridge</span>
                                        <div class="text-xs text-gray-500">Connected</div>
                                    </div>
                                </div>
                                <div class="group flex items-center space-x-3 p-4 bg-white/80 backdrop-blur-sm rounded-xl shadow-sm hover:shadow-lg transition-all duration-300 cursor-pointer border border-white/50 hover:border-orange-200/50">
                                    <div class="w-8 h-8 bg-gradient-to-br from-orange-100 to-orange-200 border-2 border-orange-400 rounded-full flex items-center justify-center group-hover:scale-110 transition-transform">
                                        <i class="fas fa-teeth text-orange-600 text-sm"></i>
                                    </div>
                                    <div>
                                        <span class="text-sm font-semibold text-gray-800">Partial Denture</span>
                                        <div class="text-xs text-gray-500">Removable</div>
                                    </div>
                                </div>
                                <div class="group flex items-center space-x-3 p-4 bg-white/80 backdrop-blur-sm rounded-xl shadow-sm hover:shadow-lg transition-all duration-300 cursor-pointer border border-white/50 hover:border-amber-200/50">
                                    <div class="w-8 h-8 bg-gradient-to-br from-amber-100 to-amber-200 border-2 border-amber-400 rounded-full flex items-center justify-center group-hover:scale-110 transition-transform">
                                        <i class="fas fa-teeth-open text-amber-600 text-sm"></i>
                                    </div>
                                    <div>
                                        <span class="text-sm font-semibold text-gray-800">Full Denture</span>
                                        <div class="text-xs text-gray-500">Complete</div>
                                    </div>
                                </div>
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

                        <div class="p-8 bg-gradient-to-br from-gray-50 via-white to-blue-50/30">
                            <div class="w-full">
                                <div class="relative bg-gradient-to-br from-white to-gray-50/50 backdrop-blur-sm rounded-3xl p-8 shadow-2xl border border-white/30 overflow-hidden">
                                    <div class="absolute inset-0 bg-gradient-to-br from-pink-50/30 via-white to-blue-50/20 rounded-3xl"></div>
                                    <div id="odontogramSvgWrapper" class="relative z-10 min-h-[320px] flex items-center justify-center">
                                        <?php
                                        $svgPath = APPPATH . 'Views/odontogram/odontogram.svg';
                                        if (is_file($svgPath)) {
                                            echo file_get_contents($svgPath);
                                        } else {
                                            echo '<p class="text-sm text-gray-500">Odontogram SVG not found.</p>';
                                        }
                                        ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white/70 backdrop-blur-sm rounded-2xl shadow-xl border border-white/20 overflow-hidden">
                        <div class="bg-gradient-to-r from-blue-600 via-indigo-600 to-purple-600 text-white p-6">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center space-x-4">
                                    <div class="w-12 h-12 bg-white/20 rounded-xl flex items-center justify-center backdrop-blur-sm">
                                        <i class="fas fa-table text-white text-xl"></i>
                                    </div>
                                    <div>
                                        <h3 class="text-xl font-bold">Teeth with Conditions</h3>
                                        <p class="text-blue-100 text-sm" id="conditionsSummary"></p>
                                    </div>
                                </div>
                            </div>
                        </div>
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
                                    <tbody id="teethTableBody"></tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="space-y-6">
                    <div class="bg-white/80 backdrop-blur-sm rounded-2xl shadow-xl border border-white/30 overflow-hidden contract-panel">
                        <div class="bg-gradient-to-r from-emerald-500 via-teal-500 to-cyan-500 text-white p-5">
                            <div class="flex items-center space-x-3">
                                <div class="w-10 h-10 bg-white/20 rounded-xl flex items-center justify-center backdrop-blur-sm">
                                    <i class="fas fa-receipt text-white"></i>
                                </div>
                                <div>
                                    <h3 class="text-lg font-bold">Contract Summary</h3>
                                    <p class="text-emerald-100 text-xs">Currency and total</p>
                                </div>
                            </div>
                        </div>
                        <div class="p-6 space-y-4 bg-white/70 contract-panel-body">
                            <div class="grid grid-cols-2 gap-4">
                            <div class="contract-input-wrap">
                                <label class="block text-xs font-semibold text-gray-600 mb-1">Currency</label>
                                <?php
                                    $baseCurrency = $base_currency ?? 'ILS';
                                    $selectedCurrency = $contract['currency'] ?? $baseCurrency;
                                ?>
                                <select name="currency" class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm bg-white/90 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500">
                                    <?php foreach (($currency_options ?? ['ILS']) as $option): ?>
                                        <option value="<?= esc($option) ?>" <?= ($selectedCurrency === $option) ? 'selected' : '' ?>>
                                            <?= esc($option) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="contract-input-wrap">
                                <label class="block text-xs font-semibold text-gray-600 mb-1">Total</label>
                                <input type="number"
                                       step="0.01"
                                       min="0"
                                       name="total_cost"
                                       id="contractTotalInput"
                                       value="<?= esc(number_format((float) ($contract['total_cost'] ?? 0), 2, '.', '')) ?>"
                                       class="w-full px-3 py-2 bg-white/90 border border-emerald-200 rounded-lg text-sm font-semibold text-emerald-700 contract-total-display">
                                <p class="mt-1 text-[11px] text-gray-500">Auto-calculated from items. You can override it.</p>
                            </div>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white/80 backdrop-blur-sm rounded-2xl shadow-xl border border-white/30 overflow-hidden contract-panel">
                        <div class="bg-gradient-to-r from-sky-500 via-blue-600 to-indigo-600 text-white p-5">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center space-x-3">
                                    <div class="w-10 h-10 bg-white/20 rounded-xl flex items-center justify-center backdrop-blur-sm">
                                        <i class="fas fa-list text-white"></i>
                                    </div>
                                    <div>
                                        <h3 class="text-lg font-bold">Treatment Items</h3>
                                        <p class="text-blue-100 text-xs">Auto-filled from tooth updates or add manually</p>
                                    </div>
                                </div>
                                <button type="button" id="addItemBtn" class="inline-flex items-center px-3 py-2 bg-white/20 hover:bg-white/30 text-white text-xs font-semibold rounded-lg border border-white/30">
                                    <i class="fas fa-plus mr-2"></i>Add Item
                                </button>
                            </div>
                        </div>
                        <div class="p-6 bg-white/70 contract-panel-body">
                            <div class="overflow-x-auto contract-items-shell">
                                <table class="min-w-full text-sm contract-items-grid">
                                    <thead class="bg-white/80">
                                    <tr>
                                        <th class="px-3 py-2 text-left text-xs font-semibold text-gray-500 uppercase">Code</th>
                                        <th class="px-3 py-2 text-left text-xs font-semibold text-gray-500 uppercase">Tooth</th>
                                        <th class="px-3 py-2 text-left text-xs font-semibold text-gray-500 uppercase">Description</th>
                                        <th class="px-3 py-2 text-left text-xs font-semibold text-gray-500 uppercase">Qty</th>
                                        <th class="px-3 py-2 text-left text-xs font-semibold text-gray-500 uppercase">Price</th>
                                        <th class="px-3 py-2"></th>
                                    </tr>
                                    </thead>
                                    <tbody id="itemsBody" class="divide-y divide-gray-100"></tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white/80 backdrop-blur-sm rounded-2xl shadow-xl border border-white/30 overflow-hidden contract-panel">
                        <div class="bg-gradient-to-r from-slate-700 via-slate-800 to-gray-900 text-white p-5">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center space-x-3">
                                    <div class="w-10 h-10 bg-white/20 rounded-xl flex items-center justify-center backdrop-blur-sm">
                                        <i class="fas fa-pen-fancy text-white"></i>
                                    </div>
                                    <div>
                                        <h3 class="text-lg font-bold">Contract Terms</h3>
                                        <p class="text-slate-200 text-xs">Optional terms, timeline, and notes</p>
                                    </div>
                                </div>
                                <button type="button"
                                        id="contractTermsToggleBtn"
                                        class="inline-flex items-center px-3 py-2 bg-white/20 hover:bg-white/30 text-white text-xs font-semibold rounded-lg border border-white/30">
                                    <i id="contractTermsToggleIcon" class="fas <?= $hasContractTerms ? 'fa-chevron-up' : 'fa-chevron-down' ?> mr-2"></i>
                                    <span id="contractTermsToggleText"><?= $hasContractTerms ? 'Hide' : 'Show' ?></span>
                                </button>
                            </div>
                        </div>
                        <div id="contractTermsContent" class="p-6 space-y-4 bg-white/70 contract-panel-body <?= $hasContractTerms ? '' : 'hidden' ?>">
                            <div class="contract-input-wrap">
                                <label class="block text-xs font-semibold text-gray-600 mb-1">Terms &amp; Conditions</label>
                                <textarea name="terms_conditions" rows="3" class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm bg-white/90 focus:ring-2 focus:ring-slate-500 focus:border-slate-500" placeholder="Optional terms and conditions..."><?= esc($contract['terms_conditions'] ?? '') ?></textarea>
                            </div>
                            <div class="contract-input-wrap">
                                <label class="block text-xs font-semibold text-gray-600 mb-1">Payment Terms</label>
                                <textarea name="payment_terms" rows="3" class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm bg-white/90 focus:ring-2 focus:ring-slate-500 focus:border-slate-500" placeholder="Optional payment terms..."><?= esc($contract['payment_terms'] ?? '') ?></textarea>
                            </div>
                            <div class="contract-input-wrap">
                                <label class="block text-xs font-semibold text-gray-600 mb-1">Duration Plan</label>
                                <input type="text" name="duration_plan" value="<?= esc($contract['duration_plan'] ?? '') ?>" class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm bg-white/90 focus:ring-2 focus:ring-slate-500 focus:border-slate-500" placeholder="Optional duration or timeline">
                            </div>
                            <div class="contract-input-wrap">
                                <label class="block text-xs font-semibold text-gray-600 mb-1">Notes</label>
                                <textarea name="notes" rows="3" class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm bg-white/90 focus:ring-2 focus:ring-slate-500 focus:border-slate-500" placeholder="Optional notes for the patient..."><?= esc($contract['notes'] ?? '') ?></textarea>
                            </div>
                        </div>
                    </div>

                    <div class="flex justify-end">
                        <button type="submit" class="inline-flex items-center px-5 py-3 bg-gradient-to-r from-blue-600 to-indigo-600 text-white rounded-xl text-sm font-semibold shadow-lg hover:shadow-xl transition-all duration-200">
                            <i class="fas fa-save mr-2"></i><?= $mode === 'edit' ? 'Save Changes' : 'Save Contract Draft' ?>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

<div id="toothModal" class="fixed inset-0 bg-black/50 backdrop-blur-sm hidden z-50 transition-opacity duration-300">
    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="bg-white/95 backdrop-blur-md rounded-2xl shadow-2xl max-w-lg w-full transform transition-all duration-300 scale-95 opacity-0" id="modalContent">
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
                        <button type="button" onclick="closeToothModal()" class="w-8 h-8 bg-white/20 hover:bg-white/30 rounded-lg flex items-center justify-center transition-colors duration-200">
                            <i class="fas fa-times text-white"></i>
                        </button>
                    </div>
                </div>
                <div class="absolute top-0 right-0 w-20 h-20 bg-white/5 rounded-full -translate-y-10 translate-x-10"></div>
                <div class="absolute bottom-0 left-0 w-16 h-16 bg-white/5 rounded-full translate-y-8 -translate-x-8"></div>
            </div>

            <div class="p-6">
                <form id="toothForm" class="space-y-6">
                    <input type="hidden" id="toothNumber" name="tooth_number">
                    <div class="space-y-2">
                        <label for="condition_type" class="block text-sm font-semibold text-gray-700">
                            <i class="fas fa-stethoscope mr-2 text-blue-600"></i>Condition Type
                        </label>
                        <select id="condition_type" name="condition_type" class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors duration-200 bg-white/70 backdrop-blur-sm" required>
                            <option value="">Select a condition type</option>
                            <?php foreach ($condition_types as $key => $label): ?>
                                <option value="<?= esc($key) ?>"><?= esc($label) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="space-y-2">
                        <label for="condition_description" class="block text-sm font-semibold text-gray-700">
                            <i class="fas fa-file-text mr-2 text-green-600"></i>Description
                        </label>
                        <textarea id="condition_description" name="condition_description"
                                  class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-green-500 focus:border-green-500 transition-colors duration-200 bg-white/70 backdrop-blur-sm resize-none"
                                  rows="3" placeholder="Describe the condition in detail..."></textarea>
                    </div>
                    <div class="space-y-2">
                        <label for="treatment_notes" class="block text-sm font-semibold text-gray-700">
                            <i class="fas fa-notes-medical mr-2 text-purple-600"></i>Treatment Notes
                        </label>
                        <textarea id="treatment_notes" name="treatment_notes"
                                  class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-purple-500 focus:border-purple-500 transition-colors duration-200 bg-white/70 backdrop-blur-sm resize-none"
                                  rows="3" placeholder="Treatment performed or planned..."></textarea>
                    </div>
                    <div class="space-y-2">
                        <label for="treatment_status" class="block text-sm font-semibold text-gray-700">
                            <i class="fas fa-tasks mr-2 text-orange-600"></i>Treatment Status
                        </label>
                        <select id="treatment_status" name="treatment_status" class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-orange-500 focus:border-orange-500 transition-colors duration-200 bg-white/70 backdrop-blur-sm">
                            <option value="pending">Pending</option>
                            <option value="in_progress">In Progress</option>
                            <option value="completed">Completed</option>
                            <option value="needs_attention">Needs Attention</option>
                        </select>
                    </div>
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

<script>
const svgNS = 'http://www.w3.org/2000/svg';
const conditionTypes = <?= json_encode($condition_types) ?>;
const conditionColors = {
    healthy: '#10b981',
    cavity: '#f59e0b',
    filling: '#3b82f6',
    crown: '#8b5cf6',
    root_canal: '#ec4899',
    extracted: '#ef4444',
    implant: '#6366f1',
    bridge: '#14b8a6',
    partial_denture: '#f97316',
    full_denture: '#f59e0b',
    other: '#6b7280'
};
const conditionBadgeClasses = {
    healthy: 'bg-green-100 text-green-800',
    cavity: 'bg-yellow-100 text-yellow-800',
    filling: 'bg-blue-100 text-blue-800',
    crown: 'bg-purple-100 text-purple-800',
    root_canal: 'bg-pink-100 text-pink-800',
    extracted: 'bg-red-100 text-red-800',
    implant: 'bg-indigo-100 text-indigo-800',
    bridge: 'bg-teal-100 text-teal-800',
    partial_denture: 'bg-orange-100 text-orange-800',
    full_denture: 'bg-amber-100 text-amber-800',
    other: 'bg-gray-100 text-gray-800'
};
const statusBadgeClasses = {
    pending: 'bg-yellow-100 text-yellow-800',
    in_progress: 'bg-orange-100 text-orange-800',
    completed: 'bg-green-100 text-green-800',
    needs_attention: 'bg-red-100 text-red-800'
};
const statusLabels = {
    pending: 'Pending',
    in_progress: 'In Progress',
    completed: 'Completed',
    needs_attention: 'Needs Attention'
};
const initialOdontogram = <?= json_encode($normalizedInitial) ?>;
const initialItems = <?= json_encode($items) ?>;

const toothGroups = new Map();
const toothOverlays = new Map();
const toothOutlines = new Map();
const toothHits = new Map();
let labelsVisible = false;
let activeTooth = null;
let odontogramData = [];
let items = [];
let tooltipEl = null;
let manualTotalOverride = false;
let totalOverrideInitialized = false;

function getFdiNumber(universalNumber) {
    const fdiMap = {
        1: '11', 2: '12', 3: '13', 4: '14', 5: '15', 6: '16', 7: '17', 8: '18',
        9: '21', 10: '22', 11: '23', 12: '24', 13: '25', 14: '26', 15: '27', 16: '28',
        17: '31', 18: '32', 19: '33', 20: '34', 21: '35', 22: '36', 23: '37', 24: '38',
        25: '41', 26: '42', 27: '43', 28: '44', 29: '45', 30: '46', 31: '47', 32: '48'
    };
    return fdiMap[universalNumber] || String(universalNumber);
}

function parseToothNumber(element) {
    if (!element) return null;
    const dataTooth = element.dataset?.tooth;
    if (dataTooth) return parseInt(dataTooth, 10);
    const id = element.getAttribute('id') || '';
    const match = id.match(/^tooth-(\d+)$/);
    return match ? parseInt(match[1], 10) : null;
}

function getLargestShape(group) {
    const shapes = Array.from(group.querySelectorAll('path, polygon, rect, circle, ellipse'))
        .filter(el => !el.classList.contains('tooth-hit') && !el.classList.contains('tooth-overlay') && !el.classList.contains('tooth-outline'));

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
        return !!(fill && fill !== 'none' && fill !== 'transparent');
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
        if (!area || area <= 0) return;

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

        overlay = base.cloneNode(true);
        overlay.removeAttribute('id');
        overlay.setAttribute('class', 'tooth-overlay');
        overlay.setAttribute('fill', 'transparent');
        overlay.setAttribute('opacity', '0');
        overlay.style.pointerEvents = 'none';
        group.appendChild(overlay);

        outline = base.cloneNode(true);
        outline.removeAttribute('id');
        outline.setAttribute('class', 'tooth-outline');
        outline.setAttribute('fill', 'none');
        outline.setAttribute('stroke', '#3b82f6');
        outline.setAttribute('stroke-width', '1.5');
        outline.setAttribute('opacity', '0');
        outline.style.pointerEvents = 'none';
        group.appendChild(outline);

        hit = base.cloneNode(true);
        hit.removeAttribute('id');
        hit.setAttribute('class', 'tooth-hit');
        hit.setAttribute('fill', 'transparent');
        hit.setAttribute('opacity', '0');
        hit.style.pointerEvents = 'fill';
        group.appendChild(hit);
    }

    if (hit) {
        hit.style.cursor = 'pointer';
        hit.setAttribute('tabindex', '0');
        hit.setAttribute('role', 'button');
        hit.setAttribute('aria-label', `Tooth ${toothNumber} (FDI ${getFdiNumber(toothNumber)})`);
    }

    return { overlay, outline, hit };
}

function cacheToothGroups(svg) {
    toothGroups.clear();
    toothOverlays.clear();
    toothOutlines.clear();
    toothHits.clear();

    const groups = Array.from(svg.querySelectorAll('[id^="tooth-"]'))
        .filter(element => (element.tagName || '').toLowerCase().endsWith('g'));

    groups.forEach(group => {
        const toothNumber = parseToothNumber(group);
        if (!toothNumber) return;

        group.classList.add('tooth-group');
        group.dataset.tooth = toothNumber;

        const { overlay, outline, hit } = ensureToothControls(group, toothNumber);
        if (overlay) toothOverlays.set(toothNumber, overlay);
        if (outline) toothOutlines.set(toothNumber, outline);
        if (hit) {
            toothHits.set(toothNumber, hit);
            hit.addEventListener('mouseenter', (event) => {
                group.classList.add('is-hover');
                setOutlineVisible(toothNumber, true);
                showTooltip(event, toothNumber);
            });
            hit.addEventListener('mousemove', moveTooltip);
            hit.addEventListener('mouseleave', () => {
                group.classList.remove('is-hover');
                setOutlineVisible(toothNumber, false);
                hideTooltip();
            });
            hit.addEventListener('focus', (event) => {
                group.classList.add('is-hover');
                setOutlineVisible(toothNumber, true);
                showTooltip(event, toothNumber);
            });
            hit.addEventListener('blur', () => {
                group.classList.remove('is-hover');
                setOutlineVisible(toothNumber, false);
                hideTooltip();
            });
            hit.addEventListener('click', () => openToothModal(toothNumber));
            hit.addEventListener('keydown', (event) => {
                if (event.key === 'Enter' || event.key === ' ') {
                    event.preventDefault();
                    openToothModal(toothNumber);
                }
            });
        }

        toothGroups.set(toothNumber, group);
    });
}

function buildToothLabels(svg) {
    svg.querySelectorAll('.tooth-label-layer').forEach(el => el.remove());
    const labelLayer = document.createElementNS(svgNS, 'g');
    labelLayer.setAttribute('class', 'tooth-label-layer');
    labelLayer.setAttribute('pointer-events', 'none');

    toothGroups.forEach((group, toothNumber) => {
        let bbox;
        try {
            bbox = group.getBBox();
        } catch (e) {
            return;
        }
        const centerX = bbox.x + bbox.width / 2;
        const centerY = bbox.y + (toothNumber <= 16 ? bbox.height + 10 : -6);

        const universalLabel = document.createElementNS(svgNS, 'text');
        universalLabel.setAttribute('class', 'universal-number');
        universalLabel.setAttribute('x', centerX.toString());
        universalLabel.setAttribute('y', centerY.toString());
        universalLabel.textContent = String(toothNumber);
        labelLayer.appendChild(universalLabel);

        const fdiLabel = document.createElementNS(svgNS, 'text');
        fdiLabel.setAttribute('class', 'fdi-number');
        fdiLabel.setAttribute('x', centerX.toString());
        fdiLabel.setAttribute('y', centerY.toString());
        fdiLabel.textContent = getFdiNumber(toothNumber);
        fdiLabel.style.display = 'none';
        labelLayer.appendChild(fdiLabel);
    });

    svg.appendChild(labelLayer);
}

function setupSvgOdontogram() {
    const wrapper = document.getElementById('odontogramSvgWrapper');
    if (!wrapper) return;
    const svg = wrapper.querySelector('svg');
    if (!svg) return;

    svg.id = svg.id || 'odontogramSvg';
    svg.removeAttribute('width');
    svg.removeAttribute('height');
    svg.style.width = '100%';
    svg.style.height = 'auto';

    cacheToothGroups(svg);
    if (labelsVisible) {
        buildToothLabels(svg);
    }
}

function setOutlineVisible(toothNumber, visible) {
    const outline = toothOutlines.get(toothNumber);
    if (outline) {
        outline.setAttribute('opacity', visible ? '0.9' : '0');
    }
}

function getConditionForTooth(toothNumber) {
    const record = odontogramData.find(t => parseInt(t.tooth_number, 10) === parseInt(toothNumber, 10));
    return record ? record.condition_type : 'healthy';
}

function applyToothVisual(toothNumber) {
    const overlay = toothOverlays.get(parseInt(toothNumber, 10));
    if (!overlay) return;

    const condition = getConditionForTooth(toothNumber);
    if (!condition || condition === 'healthy') {
        overlay.setAttribute('opacity', '0');
        overlay.setAttribute('fill', 'transparent');
    } else {
        overlay.setAttribute('fill', conditionColors[condition] || '#6b7280');
        overlay.setAttribute('opacity', '0.28');
    }
}

function setElementDisplay(el, show) {
    el.style.display = show ? 'inline' : 'none';
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
    moveTooltip(event);
    tooltipEl.style.opacity = '1';
}

function moveTooltip(event) {
    if (!tooltipEl || tooltipEl.style.opacity !== '1') {
        return;
    }
    const x = event?.clientX ?? 0;
    const y = event?.clientY ?? 0;
    tooltipEl.style.left = `${x + 12}px`;
    tooltipEl.style.top = `${y + 12}px`;
}

function hideTooltip() {
    if (!tooltipEl) {
        return;
    }
    tooltipEl.style.opacity = '0';
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
    if (!activeTooth) return;
    const current = toothGroups.get(parseInt(activeTooth, 10));
    current?.classList.remove('is-selected');
    activeTooth = null;
}

function openToothModal(toothNumber) {
    const modal = document.getElementById('toothModal');
    const modalContent = document.getElementById('modalContent');
    if (!modal || !modalContent) return;

    const modalNumber = document.getElementById('modalToothNumber');
    const toothInput = document.getElementById('toothNumber');
    modalNumber.textContent = toothNumber;
    toothInput.value = toothNumber;

    const existing = odontogramData.find(t => parseInt(t.tooth_number, 10) === parseInt(toothNumber, 10));
    const conditionTypeEl = document.getElementById('condition_type');
    const descriptionEl = document.getElementById('condition_description');
    const notesEl = document.getElementById('treatment_notes');
    const statusEl = document.getElementById('treatment_status');

    if (existing) {
        conditionTypeEl.value = existing.condition_type || 'healthy';
        descriptionEl.value = existing.condition_description || '';
        notesEl.value = existing.treatment_notes || '';
        statusEl.value = existing.treatment_status || 'pending';
    } else {
        document.getElementById('toothForm').reset();
        conditionTypeEl.value = 'healthy';
        statusEl.value = 'pending';
    }

    modal.classList.remove('hidden');
    modal.classList.add('flex');
    setTimeout(() => {
        modalContent.style.transform = 'scale(1)';
        modalContent.style.opacity = '1';
    }, 10);

    setSelectedTooth(toothNumber);
}

function closeToothModal() {
    const modal = document.getElementById('toothModal');
    const modalContent = document.getElementById('modalContent');
    if (!modal || !modalContent) return;

    modalContent.style.transform = 'scale(0.95)';
    modalContent.style.opacity = '0';

    setTimeout(() => {
        modal.classList.add('hidden');
        modal.classList.remove('flex');
    }, 300);

    clearSelectedTooth();
}

function attachModalBehavior() {
    const modal = document.getElementById('toothModal');
    if (modal) {
        modal.addEventListener('click', event => {
            if (event.target === modal) {
                closeToothModal();
            }
        });
    }

    document.addEventListener('keydown', event => {
        if (event.key === 'Escape') {
            closeToothModal();
        }
    });
}

function saveTooth() {
    const toothNumber = parseInt(document.getElementById('toothNumber').value, 10);
    if (!toothNumber) return;

    const condition = document.getElementById('condition_type').value || 'healthy';
    const description = document.getElementById('condition_description').value || '';
    const notes = document.getElementById('treatment_notes').value || '';
    const status = document.getElementById('treatment_status').value || 'pending';

    const existingIndex = odontogramData.findIndex(t => parseInt(t.tooth_number, 10) === toothNumber);
    const updated = {
        tooth_number: toothNumber,
        condition_type: condition,
        condition_description: description,
        treatment_notes: notes,
        treatment_status: status
    };

    if (existingIndex !== -1) {
        odontogramData[existingIndex] = updated;
    } else {
        odontogramData.push(updated);
    }

    applyToothVisual(toothNumber);
    updateConditionsTable();
    syncItemsFromConditions();
    closeToothModal();
}

function resetTooth() {
    const toothNumber = parseInt(document.getElementById('toothNumber').value, 10);
    if (!toothNumber) return;

    const existingIndex = odontogramData.findIndex(t => parseInt(t.tooth_number, 10) === toothNumber);
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
    updateConditionsTable();
    syncItemsFromConditions();
    closeToothModal();
}

function removeToothCondition() {
    resetTooth();
}

function getToothPosition(toothNumber) {
    if (toothNumber >= 1 && toothNumber <= 8) return 'Upper Right';
    if (toothNumber >= 9 && toothNumber <= 16) return 'Upper Left';
    if (toothNumber >= 17 && toothNumber <= 24) return 'Lower Left';
    if (toothNumber >= 25 && toothNumber <= 32) return 'Lower Right';
    return '';
}

function updateConditionsTable() {
    const tableBody = document.getElementById('teethTableBody');
    const summaryEl = document.getElementById('conditionsSummary');
    if (!tableBody) return;

    const conditions = [];
    for (let i = 1; i <= 32; i += 1) {
        const record = odontogramData.find(t => parseInt(t.tooth_number, 10) === i);
        const condition = record ? record.condition_type : 'healthy';
        if (condition !== 'healthy') {
            conditions.push({
                tooth_number: i,
                record
            });
        }
    }

    tableBody.replaceChildren();

    if (summaryEl) {
        summaryEl.textContent = `Showing ${conditions.length} teeth with conditions (out of 32 total)`;
    }

    if (conditions.length === 0) {
        const row = document.createElement('tr');
        const cell = document.createElement('td');
        cell.setAttribute('colspan', '8');
        cell.className = 'py-12 text-center text-gray-500';
        const wrapper = document.createElement('div');
        wrapper.className = 'flex flex-col items-center space-y-3';
        const icon = document.createElement('i');
        icon.className = 'fas fa-smile text-4xl text-green-400';
        const title = document.createElement('div');
        title.className = 'text-lg font-medium';
        title.textContent = 'All teeth are healthy!';
        const subtitle = document.createElement('div');
        subtitle.className = 'text-sm';
        subtitle.textContent = 'No conditions found for this contract.';
        wrapper.appendChild(icon);
        wrapper.appendChild(title);
        wrapper.appendChild(subtitle);
        cell.appendChild(wrapper);
        row.appendChild(cell);
        tableBody.appendChild(row);
        return;
    }

    conditions.forEach(item => {
        const toothNumber = item.tooth_number;
        const record = item.record || {};
        const condition = record.condition_type || 'healthy';
        const description = record.condition_description || '';
        const treatment = record.treatment_notes || '';
        const status = record.treatment_status || 'completed';

        const row = document.createElement('tr');
        row.className = 'border-b border-gray-100 hover:bg-gray-50/50 transition-colors duration-200';
        row.dataset.tooth = toothNumber;

        const toothCell = document.createElement('td');
        toothCell.className = 'py-3 px-4';
        const toothUniversal = document.createElement('span');
        toothUniversal.className = 'universal-number font-semibold text-blue-600';
        toothUniversal.textContent = String(toothNumber);
        const toothFdi = document.createElement('span');
        toothFdi.className = 'fdi-number hidden font-semibold text-blue-600';
        toothFdi.textContent = getFdiNumber(toothNumber);
        toothCell.appendChild(toothUniversal);
        toothCell.appendChild(toothFdi);

        const fdiCell = document.createElement('td');
        fdiCell.className = 'py-3 px-4';
        const fdiUniversal = document.createElement('span');
        fdiUniversal.className = 'universal-number text-gray-600';
        fdiUniversal.textContent = getFdiNumber(toothNumber);
        const fdiAlt = document.createElement('span');
        fdiAlt.className = 'fdi-number hidden text-gray-600';
        fdiAlt.textContent = String(toothNumber);
        fdiCell.appendChild(fdiUniversal);
        fdiCell.appendChild(fdiAlt);

        const positionCell = document.createElement('td');
        positionCell.className = 'py-3 px-4 text-gray-600';
        positionCell.textContent = getToothPosition(toothNumber);

        const conditionCell = document.createElement('td');
        conditionCell.className = 'py-3 px-4';
        const conditionBadge = document.createElement('span');
        conditionBadge.className = `inline-flex items-center px-2 py-1 rounded-full text-xs font-medium ${conditionBadgeClasses[condition] || conditionBadgeClasses.other}`;
        conditionBadge.textContent = (conditionTypes?.[condition] || 'Healthy');
        conditionCell.appendChild(conditionBadge);

        const descriptionCell = document.createElement('td');
        descriptionCell.className = 'py-3 px-4 text-gray-600 max-w-xs truncate';
        descriptionCell.title = description;
        descriptionCell.textContent = description || '-';

        const treatmentCell = document.createElement('td');
        treatmentCell.className = 'py-3 px-4 text-gray-600 max-w-xs truncate';
        treatmentCell.title = treatment;
        treatmentCell.textContent = treatment || '-';

        const statusCell = document.createElement('td');
        statusCell.className = 'py-3 px-4';
        const statusBadge = document.createElement('span');
        statusBadge.className = `inline-flex items-center px-2 py-1 rounded-full text-xs font-medium ${statusBadgeClasses[status] || statusBadgeClasses.completed}`;
        statusBadge.textContent = statusLabels[status] || 'Completed';
        statusCell.appendChild(statusBadge);

        const actionCell = document.createElement('td');
        actionCell.className = 'py-3 px-4';
        const editButton = document.createElement('button');
        editButton.type = 'button';
        editButton.className = 'text-blue-600 hover:text-blue-800 font-medium text-xs transition-colors duration-200';
        editButton.textContent = 'Edit';
        editButton.addEventListener('click', () => openToothModal(toothNumber));
        actionCell.appendChild(editButton);

        row.appendChild(toothCell);
        row.appendChild(fdiCell);
        row.appendChild(positionCell);
        row.appendChild(conditionCell);
        row.appendChild(descriptionCell);
        row.appendChild(treatmentCell);
        row.appendChild(statusCell);
        row.appendChild(actionCell);

        tableBody.appendChild(row);
    });

}

function normalizeOdontogram() {
    odontogramData = [];
    initialOdontogram.forEach(row => {
        if (!row || row.tooth_number == null) return;
        odontogramData.push({
            tooth_number: parseInt(row.tooth_number, 10),
            condition_type: row.condition_type || 'healthy',
            condition_description: row.condition_description || '',
            treatment_notes: row.treatment_notes || '',
            treatment_status: row.treatment_status || 'pending'
        });
    });
}

function normalizeItems() {
    items = [];
    initialItems.forEach(item => {
        items.push({
            code: item.procedure_code || '',
            tooth: item.tooth_no || '',
            description: item.description || '',
            qty: Number(item.qty || 1),
            price: Number(item.price || 0),
            source: item.tooth_no ? 'auto' : 'manual'
        });
    });
}

function syncItemsFromConditions() {
    const conditionTeeth = odontogramData.filter(row => row.condition_type && row.condition_type !== 'healthy');
    const existingAuto = new Map();
    items.forEach(item => {
        if (item.source === 'auto' && item.tooth) {
            existingAuto.set(String(item.tooth), item);
        }
    });

    conditionTeeth.forEach(row => {
        const key = String(row.tooth_number);
        if (!existingAuto.has(key)) {
            items.push({
                code: '',
                tooth: key,
                description: `${conditionTypes?.[row.condition_type] || 'Condition'} - Tooth ${key}`,
                qty: 1,
                price: 0,
                source: 'auto'
            });
        } else {
            const item = existingAuto.get(key);
            if (!item.description) {
                item.description = `${conditionTypes?.[row.condition_type] || 'Condition'} - Tooth ${key}`;
            }
        }
    });

    items = items.filter(item => {
        if (item.source !== 'auto') return true;
        if (!item.tooth) return false;
        const toothRow = conditionTeeth.find(row => String(row.tooth_number) === String(item.tooth));
        return !!toothRow;
    });

    renderItems();
}

function renderItems() {
    const itemsBody = document.getElementById('itemsBody');
    if (!itemsBody) return;

    itemsBody.replaceChildren();
    if (items.length === 0) {
        const row = document.createElement('tr');
        const cell = document.createElement('td');
        cell.colSpan = 6;
        cell.className = 'px-4 py-6 text-center text-gray-500';
        cell.textContent = 'No items added.';
        row.appendChild(cell);
        itemsBody.appendChild(row);
        updateTotal();
        return;
    }

    items.forEach((item, index) => {
        const row = document.createElement('tr');

        row.appendChild(buildItemCell('code', item.code, index));
        row.appendChild(buildItemCell('tooth', item.tooth, index));
        row.appendChild(buildItemCell('description', item.description, index));
        row.appendChild(buildItemCell('qty', item.qty, index, 'number'));
        row.appendChild(buildItemCell('price', item.price, index, 'number'));

        const actionCell = document.createElement('td');
        actionCell.className = 'px-3 py-2 text-right';
        const removeBtn = document.createElement('button');
        removeBtn.type = 'button';
        removeBtn.className = 'text-xs text-rose-600 hover:text-rose-700';
        removeBtn.textContent = 'Remove';
        removeBtn.addEventListener('click', () => {
            items.splice(index, 1);
            renderItems();
        });
        actionCell.appendChild(removeBtn);
        row.appendChild(actionCell);

        itemsBody.appendChild(row);
    });

    updateTotal();
}

function buildItemCell(field, value, index, type = 'text') {
    const cell = document.createElement('td');
    cell.className = 'px-3 py-2';
    const input = document.createElement('input');
    input.type = type;
    input.name = `items[${index}][${field}]`;
    input.value = value ?? '';
    input.className = 'w-full px-2.5 py-1.5 border border-slate-200 rounded-md text-xs bg-white text-slate-700 focus:outline-none focus:ring-0';

    if (field === 'qty' || field === 'price') {
        input.step = '0.01';
        input.min = '0';
    }

    input.addEventListener('input', () => {
        if (field === 'qty' || field === 'price') {
            items[index][field] = Number(input.value || 0);
        } else {
            items[index][field] = input.value;
        }
        updateTotal();
    });

    cell.appendChild(input);
    return cell;
}

function updateTotal() {
    const total = items.reduce((sum, item) => {
        const qty = Number(item.qty || 1);
        const price = Number(item.price || 0);
        return sum + (qty > 0 ? qty : 1) * price;
    }, 0);
    const totalInput = document.getElementById('contractTotalInput');
    if (!totalInput) {
        return;
    }

    if (!totalOverrideInitialized) {
        const existingValue = Number(totalInput.value);
        if (Number.isFinite(existingValue) && Math.abs(existingValue - total) > 0.009) {
            manualTotalOverride = true;
        }
        totalOverrideInitialized = true;
    }

    if (!manualTotalOverride) {
        totalInput.value = total.toFixed(2);
    }
}

function captureOdontogramSnapshot() {
    const svg = document.getElementById('odontogramSvgWrapper')?.querySelector('svg');
    if (!svg) return;

    const clone = svg.cloneNode(true);
    clone.querySelectorAll('.tooth-hit, .tooth-outline').forEach(el => el.remove());
    clone.querySelectorAll('.tooth-group').forEach(el => {
        el.classList.remove('is-hover', 'is-selected');
    });

    const serializer = new XMLSerializer();
    const svgString = serializer.serializeToString(clone);
    document.getElementById('odontogramSvg').value = svgString;
    document.getElementById('odontogramJson').value = JSON.stringify(odontogramData);
}

function initializeOdontogram() {
    normalizeOdontogram();
    normalizeItems();
    syncItemsFromConditions();

    toothGroups.forEach((_, toothNumber) => {
        applyToothVisual(toothNumber);
    });
    updateConditionsTable();
}

function attachFormHandlers() {
    document.getElementById('toothForm')?.addEventListener('submit', (event) => {
        event.preventDefault();
        saveTooth();
    });

    document.getElementById('addItemBtn')?.addEventListener('click', () => {
        items.push({
            code: '',
            tooth: '',
            description: '',
            qty: 1,
            price: 0,
            source: 'manual'
        });
        renderItems();
    });

    document.getElementById('contractForm')?.addEventListener('submit', () => {
        captureOdontogramSnapshot();
    });

    const totalInput = document.getElementById('contractTotalInput');
    totalInput?.addEventListener('input', () => {
        manualTotalOverride = true;
    });

    const termsToggleBtn = document.getElementById('contractTermsToggleBtn');
    const termsContent = document.getElementById('contractTermsContent');
    const termsIcon = document.getElementById('contractTermsToggleIcon');
    const termsText = document.getElementById('contractTermsToggleText');
    const legendToggleBtn = document.getElementById('legendToggleBtn');
    const legendContent = document.getElementById('legendContent');
    const legendToggleIcon = document.getElementById('legendToggleIcon');
    const legendToggleText = document.getElementById('legendToggleText');

    termsToggleBtn?.addEventListener('click', () => {
        if (!termsContent) return;
        const willShow = termsContent.classList.contains('hidden');
        termsContent.classList.toggle('hidden', !willShow);
        if (termsIcon) {
            termsIcon.classList.toggle('fa-chevron-down', !willShow);
            termsIcon.classList.toggle('fa-chevron-up', willShow);
        }
        if (termsText) {
            termsText.textContent = willShow ? 'Hide' : 'Show';
        }
    });

    legendToggleBtn?.addEventListener('click', () => {
        if (!legendContent) return;
        const willShow = legendContent.classList.contains('hidden');
        legendContent.classList.toggle('hidden', !willShow);
        if (legendToggleIcon) {
            legendToggleIcon.classList.toggle('fa-chevron-down', !willShow);
            legendToggleIcon.classList.toggle('fa-chevron-up', willShow);
        }
        if (legendToggleText) {
            legendToggleText.textContent = willShow ? 'Hide' : 'Show';
        }
    });
}

document.addEventListener('DOMContentLoaded', () => {
    document.body?.classList.add('contract-full-width');
    document.querySelector('main')?.classList.add('contract-full-main');
    setupSvgOdontogram();
    initializeOdontogram();
    attachModalBehavior();
    attachFormHandlers();
});
</script>

<style>
#odontogramSvgWrapper svg {
    width: 100%;
    height: auto;
}

#odontogramSvgWrapper text {
    pointer-events: none;
    font-family: 'Inter', 'Helvetica Neue', Arial, sans-serif;
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

.contract-full-width,
.contract-full-width .contract-shell,
.contract-full-width .contract-grid {
    width: 100%;
    max-width: none;
}

.contract-full-width #main-content,
.contract-full-width main,
.contract-full-width main > div {
    width: 100%;
    max-width: none;
}

.contract-full-main {
    width: 100%;
    max-width: none;
}

.contract-panel {
    border-color: rgba(148, 163, 184, 0.25);
    box-shadow: 0 10px 24px rgba(15, 23, 42, 0.06), 0 2px 8px rgba(15, 23, 42, 0.04);
}

.contract-panel-body {
    background: linear-gradient(180deg, rgba(248, 250, 252, 0.76) 0%, rgba(255, 255, 255, 0.72) 100%);
}

.contract-input-wrap {
    background: rgba(255, 255, 255, 0.9);
    border: 1px solid rgba(203, 213, 225, 0.7);
    border-radius: 0.75rem;
    padding: 0.7rem;
}

.contract-input-wrap label {
    letter-spacing: 0.01em;
    color: #475569;
}

.contract-panel input,
.contract-panel select,
.contract-panel textarea {
    background-color: #fff !important;
    border: 1px solid #d3dce8 !important;
    box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.9);
    color: #0f172a;
    transition: border-color 120ms ease, box-shadow 120ms ease;
    outline: none !important;
}

.contract-panel input:hover,
.contract-panel select:hover,
.contract-panel textarea:hover {
    border-color: #c4d0df !important;
}

.contract-panel input:focus,
.contract-panel select:focus,
.contract-panel textarea:focus {
    outline: none !important;
    box-shadow: 0 2px 6px rgba(15, 23, 42, 0.08) !important;
    border-color: #9fb0c7 !important;
}

.contract-total-display {
    box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.7), 0 1px 2px rgba(15, 23, 42, 0.05);
}

.contract-items-shell {
    border: 1px solid #d6deea;
    border-radius: 0.75rem;
    background: #fff;
}

.contract-items-grid thead {
    background: #f8fafc;
}

.contract-items-grid tbody tr:nth-child(even) {
    background: #fcfdff;
}
</style>
<?= $this->endSection() ?>
