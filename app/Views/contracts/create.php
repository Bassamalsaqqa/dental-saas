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
$returnTo = $return_to ?? null;
$actionUrl = $mode === 'edit' && $contract ? base_url('contracts/' . $contract['id'] . '/update') : base_url('contracts/create/' . $patient['id']);
$hasContractTerms = !empty($contract['terms_conditions'] ?? '')
    || !empty($contract['payment_terms'] ?? '')
    || !empty($contract['duration_plan'] ?? '')
    || !empty($contract['notes'] ?? '');
?>

<?= $this->extend('layouts/main_auth') ?>

<?= $this->section('content') ?>
<div class="v3-surface-heading denta-contracts-workspace min-h-screen contract-full-width">
    <div class="v3-surface border-b v3-border">
        <div class="w-full max-w-none px-4 sm:px-6 lg:px-12 contract-shell">
            <div class="v3-command-layout">
                <div class="flex items-center space-x-4">
                    <div class="v3-icon w-12 h-12 v3-radius flex items-center justify-center">
                        <i class="fas fa-file-signature v3-text-on-primary text-xl"></i>
                    </div>
                    <div>
                        <h1 class="v3-surface-heading v3-title-scale font-bold">
                            <?= $mode === 'edit' ? esc(lang('Contracts.editTitle')) : esc(lang('Contracts.createTitle')) ?>
                        </h1>
                        <p class="v3-text-muted mt-1 flex items-center">
                            <i class="fas fa-user-circle mr-2 v3-accent-text"></i>
                            <?= esc($patient['first_name'] . ' ' . $patient['last_name']) ?>
                            <span class="ml-2 px-2 py-1 v3-soft-surface v3-accent-text text-xs font-medium rounded-full">
                                <?= esc($patient['patient_id'] ?? $patient['id']) ?>
                            </span>
                        </p>
                    </div>
                </div>
                <div class="flex flex-wrap gap-3">
                    <a href="<?= workflow_back('patient/' . $patient['id'], $returnTo) ?>" class="group relative inline-flex items-center px-4 py-2 v3-surface v3-hover-soft border v3-border rounded-lg text-sm font-medium v3-text-ink v3-hover-text-ink hover:v3-elevation-none transition-all duration-200 v3-btn v3-btn--secondary">
                        <i class="fas fa-arrow-left rtl-back-icon mr-2 transition-transform"></i><?= esc(lang('Contracts.back')) ?>
                    </a>
                </div>
            </div>
        </div>
    </div>

    <form id="contractForm" action="<?= $actionUrl ?>" method="post">
        <?= csrf_field() ?>
        <input type="hidden" name="odontogram_json" id="odontogramJson">
        <input type="hidden" name="odontogram_svg" id="odontogramSvg">
        <?php if (!empty($returnTo)): ?>
            <input type="hidden" name="return_to" value="<?= esc($returnTo) ?>">
        <?php endif; ?>
        <?php if (!empty($examination_id)): ?>
            <input type="hidden" name="examination_id" value="<?= (int) $examination_id ?>">
        <?php endif; ?>

        <div class="w-full max-w-none px-4 sm:px-6 lg:px-12 pb-12 contract-shell">
            <div class="contract-editor-layout">
                <div class="space-y-6">
                    <div class="contract-card v3-surface v3-corner border v3-border overflow-hidden">
                        <div class="v3-surface-heading p-6 relative overflow-hidden">
                            <div class="absolute inset-0 v3-backdrop"></div>
                            <div class="relative z-10 flex items-center justify-between">
                                <div class="flex items-center space-x-4">
                                    <div class="w-12 h-12 v3-surface v3-radius flex items-center justify-center">
                                        <i class="fas fa-tooth v3-text-on-primary text-xl"></i>
                                    </div>
                                    <div>
                                        <h3 class="text-2xl font-bold mb-1"><?= esc(lang('Contracts.chartTitle')) ?></h3>
                                        <p class="v3-accent-text flex items-center">
                                            <i class="fas fa-mouse-pointer mr-2"></i>
                                            <?= esc(lang('Contracts.chartHint')) ?>
                                        </p>
                                    </div>
                                </div>
                                <div class="flex flex-col items-end gap-3">
                                    <div class="hidden md:flex items-center space-x-2 v3-accent-text">
                                        <div class="w-2 h-2 v3-bg-primary rounded-full"></div>
                                        <span class="text-sm font-medium"><?= esc(lang('Contracts.liveUpdates')) ?></span>
                                    </div>
                                    <div class="flex items-center gap-2">
                                        <button type="button"
                                                id="legendToggleBtn"
                                                class="inline-flex items-center px-3 py-2 v3-surface v3-hover-soft v3-text-on-primary text-xs font-semibold rounded-lg border v3-border transition-all duration-200 v3-btn v3-btn--secondary">
                                            <i id="legendToggleIcon" class="fas fa-chevron-down mr-2"></i>
                                            <span id="legendToggleText"><?= esc(lang('Contracts.showLegend')) ?></span>
                                        </button>
                                        <button id="batchModeToggleBtn"
                                                type="button"
                                                onclick="toggleBatchMode()"
                                                class="inline-flex items-center px-3 py-2 v3-surface v3-hover-soft v3-text-on-primary text-xs font-semibold rounded-lg border v3-border transition-all duration-200 v3-btn v3-btn--secondary">
                                            <i class="fas fa-object-group mr-2"></i>
                                            <span id="batchModeToggleText"><?= esc(lang('Contracts.batchSelect')) ?></span>
                                        </button>
                                        <div id="batchTopControls" class="hidden items-center gap-2">
                                            <span id="batchTopCount" class="inline-flex items-center px-2.5 py-1 v3-bg-warning v3-text-warning rounded-full text-xs font-bold">
                                                0 selected
                                            </span>
                                            <button type="button" id="batchApplyBtn" onclick="openBatchModal()"
                                                    class="inline-flex items-center px-3 py-2 text-xs font-semibold rounded-lg border transition-all duration-200 disabled:cursor-not-allowed v3-btn v3-btn--secondary"
                                                    disabled>
                                                <i class="fas fa-layer-group mr-1.5"></i><?= esc(lang('Contracts.apply')) ?>
                                            </button>
                                            <button type="button" id="batchClearBtn" onclick="clearBatchSelection()"
                                                    class="inline-flex items-center px-3 py-2 v3-surface v3-hover-soft v3-text-on-primary text-xs font-semibold rounded-lg border v3-border transition-all duration-200 v3-btn v3-btn--secondary">
                                                <?= esc(lang('Contracts.clear')) ?>
                                            </button>
                                            <button type="button" id="batchExitBtn" onclick="toggleBatchMode(false)"
                                                    class="inline-flex items-center px-3 py-2 v3-surface v3-hover-soft v3-text-on-primary text-xs font-semibold rounded-lg border v3-border transition-all duration-200 v3-btn v3-btn--secondary">
                                                <?= esc(lang('Contracts.exit')) ?>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="absolute top-0 right-0 w-32 h-32 v3-surface rounded-full -translate-y-16 translate-x-16"></div>
                            <div class="absolute bottom-0 left-0 w-24 h-24 v3-surface rounded-full translate-y-12 -translate-x-12"></div>
                        </div>

                        <div class="v3-surface-heading p-6 border-b v3-border">
                            <div class="flex items-center justify-between">
                                <h4 class="text-lg font-bold v3-text-ink flex items-center">
                                    <i class="fas fa-palette mr-3 v3-accent-text"></i>
                                    <?= esc(lang('Contracts.legend')) ?>
                                </h4>
                            </div>
                            <div id="legendContent" class="hidden mt-6">
                            <div class="grid grid-cols-6 gap-4">
                                <div class="group flex items-center space-x-3 p-4 v3-surface v3-radius transition-all duration-300 cursor-pointer border v3-border v3-border-default">
                                    <div class="v3-icon w-8 h-8 border-2 v3-border-default rounded-full flex items-center justify-center transition-transform">
                                        <i class="fas fa-check v3-text-primary text-sm"></i>
                                    </div>
                                    <div>
                                        <span class="text-sm font-semibold v3-text-ink"><?= esc(lang('Contracts.healthy')) ?></span>
                                        <div class="text-xs v3-text-muted"><?= esc(lang('Contracts.noIssues')) ?></div>
                                    </div>
                                </div>
                                <div class="group flex items-center space-x-3 p-4 v3-surface v3-radius transition-all duration-300 cursor-pointer border v3-border v3-border-default">
                                    <div class="v3-icon w-8 h-8 border-2 v3-border-default rounded-full flex items-center justify-center transition-transform">
                                        <i class="fas fa-exclamation v3-text-warning text-sm"></i>
                                    </div>
                                    <div>
                                        <span class="text-sm font-semibold v3-text-ink"><?= esc(lang('Contracts.cavity')) ?></span>
                                        <div class="text-xs v3-text-muted"><?= esc(lang('Contracts.needsFilling')) ?></div>
                                    </div>
                                </div>
                                <div class="group flex items-center space-x-3 p-4 v3-surface v3-radius transition-all duration-300 cursor-pointer border v3-border v3-border-default">
                                    <div class="v3-icon w-8 h-8 border-2 v3-border rounded-full flex items-center justify-center transition-transform">
                                        <i class="fas fa-tools v3-accent-text text-sm"></i>
                                    </div>
                                    <div>
                                        <span class="text-sm font-semibold v3-text-ink"><?= esc(lang('Contracts.filling')) ?></span>
                                        <div class="text-xs v3-text-muted"><?= esc(lang('Contracts.restored')) ?></div>
                                    </div>
                                </div>
                                <div class="group flex items-center space-x-3 p-4 v3-surface v3-radius transition-all duration-300 cursor-pointer border v3-border v3-border-default">
                                    <div class="v3-icon w-8 h-8 border-2 v3-border rounded-full flex items-center justify-center transition-transform">
                                        <i class="fas fa-crown v3-accent-text text-sm"></i>
                                    </div>
                                    <div>
                                        <span class="text-sm font-semibold v3-text-ink"><?= esc(lang('Contracts.crown')) ?></span>
                                        <div class="text-xs v3-text-muted"><?= esc(lang('Contracts.capped')) ?></div>
                                    </div>
                                </div>
                                <div class="group flex items-center space-x-3 p-4 v3-surface v3-radius transition-all duration-300 cursor-pointer border v3-border hover:border-pink-200/50">
                                    <div class="v3-icon w-8 h-8 border-2 border-pink-400 rounded-full flex items-center justify-center transition-transform">
                                        <i class="fas fa-tooth text-pink-600 text-sm"></i>
                                    </div>
                                    <div>
                                        <span class="text-sm font-semibold v3-text-ink"><?= esc(lang('Contracts.rootCanal')) ?></span>
                                        <div class="text-xs v3-text-muted"><?= esc(lang('Contracts.endodontic')) ?></div>
                                    </div>
                                </div>
                                <div class="group flex items-center space-x-3 p-4 v3-surface v3-radius transition-all duration-300 cursor-pointer border v3-border v3-border-default">
                                    <div class="v3-icon w-8 h-8 border-2 v3-border-default rounded-full flex items-center justify-center transition-transform">
                                        <i class="fas fa-times v3-text-danger text-sm"></i>
                                    </div>
                                    <div>
                                        <span class="text-sm font-semibold v3-text-ink"><?= esc(lang('Contracts.extracted')) ?></span>
                                        <div class="text-xs v3-text-muted"><?= esc(lang('Contracts.removed')) ?></div>
                                    </div>
                                </div>
                                <div class="group flex items-center space-x-3 p-4 v3-surface v3-radius transition-all duration-300 cursor-pointer border v3-border v3-border-default">
                                    <div class="v3-icon w-8 h-8 border-2 v3-border rounded-full flex items-center justify-center transition-transform">
                                        <i class="fas fa-screwdriver v3-accent-text text-sm"></i>
                                    </div>
                                    <div>
                                        <span class="text-sm font-semibold v3-text-ink"><?= esc(lang('Contracts.implant')) ?></span>
                                        <div class="text-xs v3-text-muted"><?= esc(lang('Contracts.titanium')) ?></div>
                                    </div>
                                </div>
                                <div class="group flex items-center space-x-3 p-4 v3-surface v3-radius transition-all duration-300 cursor-pointer border v3-border v3-border-default">
                                    <div class="v3-icon w-8 h-8 border-2 v3-border-default rounded-full flex items-center justify-center transition-transform">
                                        <i class="fas fa-link v3-text-primary text-sm"></i>
                                    </div>
                                    <div>
                                        <span class="text-sm font-semibold v3-text-ink"><?= esc(lang('Contracts.bridge')) ?></span>
                                        <div class="text-xs v3-text-muted"><?= esc(lang('Contracts.connected')) ?></div>
                                    </div>
                                </div>
                                <div class="group flex items-center space-x-3 p-4 v3-surface v3-radius transition-all duration-300 cursor-pointer border v3-border v3-border-default">
                                    <div class="v3-icon w-8 h-8 border-2 v3-border-default rounded-full flex items-center justify-center transition-transform">
                                        <i class="fas fa-teeth v3-text-warning text-sm"></i>
                                    </div>
                                    <div>
                                        <span class="text-sm font-semibold v3-text-ink"><?= esc(lang('Contracts.partialDenture')) ?></span>
                                        <div class="text-xs v3-text-muted"><?= esc(lang('Contracts.removable')) ?></div>
                                    </div>
                                </div>
                                <div class="group flex items-center space-x-3 p-4 v3-surface v3-radius transition-all duration-300 cursor-pointer border v3-border v3-border-default">
                                    <div class="v3-icon w-8 h-8 border-2 v3-border-default rounded-full flex items-center justify-center transition-transform">
                                        <i class="fas fa-teeth-open v3-text-warning text-sm"></i>
                                    </div>
                                    <div>
                                        <span class="text-sm font-semibold v3-text-ink"><?= esc(lang('Contracts.fullDenture')) ?></span>
                                        <div class="text-xs v3-text-muted"><?= esc(lang('Contracts.complete')) ?></div>
                                    </div>
                                </div>
                                <div class="group flex items-center space-x-3 p-4 v3-surface v3-radius transition-all duration-300 cursor-pointer border v3-border v3-border-default">
                                    <div class="v3-icon w-8 h-8 border-2 v3-border rounded-full flex items-center justify-center transition-transform">
                                        <i class="fas fa-question v3-text-muted text-sm"></i>
                                    </div>
                                    <div>
                                        <span class="text-sm font-semibold v3-text-ink"><?= esc(lang('Contracts.other')) ?></span>
                                        <div class="text-xs v3-text-muted"><?= esc(lang('Contracts.special')) ?></div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="v3-surface-heading p-8">
                            <div class="w-full">
                                <div class="v3-surface-heading relative v3-corner p-8 border v3-border overflow-hidden">
                                    <div class="v3-surface-heading absolute inset-0 v3-corner"></div>
                                    <div id="odontogramSvgWrapper" class="relative z-10 min-h-[320px] flex items-center justify-center">
                                        <?php
                                        $svgPath = APPPATH . 'Views/odontogram/odontogram.svg';
                                        if (is_file($svgPath)) {
                                            echo file_get_contents($svgPath);
                                        } else {
                                            echo '<p class="text-sm v3-text-muted">Odontogram SVG not found.</p>';
                                        }
                                        ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="contract-card v3-surface v3-corner border v3-border overflow-hidden">
                        <div class="v3-surface-heading p-6">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center space-x-4">
                                    <div class="w-12 h-12 v3-surface v3-radius flex items-center justify-center">
                                        <i class="fas fa-table v3-text-on-primary text-xl"></i>
                                    </div>
                                    <div>
                                        <h3 class="text-xl font-bold"><?= esc(lang('Contracts.teethWithConditions')) ?></h3>
                                        <p class="v3-accent-text text-sm" id="conditionsSummary"></p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="p-4">
                            <div class="overflow-x-auto rounded-lg border v3-border">
                                <table class="w-full text-sm">
                                    <thead class="v3-surface">
                                        <tr>
                                            <th class="text-left py-2 px-3 text-[11px] font-semibold v3-text-muted uppercase tracking-wider"><?= esc(lang('Contracts.tooth')) ?></th>
                                            <th class="text-left py-2 px-3 text-[11px] font-semibold v3-text-muted uppercase tracking-wider">FDI</th>
                                            <th class="text-left py-2 px-3 text-[11px] font-semibold v3-text-muted uppercase tracking-wider"><?= esc(lang('Contracts.position')) ?></th>
                                            <th class="text-left py-2 px-3 text-[11px] font-semibold v3-text-muted uppercase tracking-wider"><?= esc(lang('Contracts.condition')) ?></th>
                                            <th class="text-left py-2 px-3 text-[11px] font-semibold v3-text-muted uppercase tracking-wider"><?= esc(lang('Contracts.description')) ?></th>
                                            <th class="text-left py-2 px-3 text-[11px] font-semibold v3-text-muted uppercase tracking-wider"><?= esc(lang('Contracts.treatment')) ?></th>
                                            <th class="text-left py-2 px-3 text-[11px] font-semibold v3-text-muted uppercase tracking-wider"><?= esc(lang('Contracts.status')) ?></th>
                                            <th class="text-left py-2 px-3 w-16"></th>
                                        </tr>
                                    </thead>
                                    <tbody id="teethTableBody" class="divide-y v3-border-default"></tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="space-y-6">
                    <div class="v3-surface v3-corner border v3-border overflow-hidden contract-panel">
                        <div class="v3-surface-heading p-5">
                            <div class="flex items-center space-x-3">
                                <div class="w-10 h-10 v3-surface v3-radius flex items-center justify-center">
                                    <i class="fas fa-receipt v3-text-on-primary"></i>
                                </div>
                                <div>
                                    <h3 class="text-lg font-bold"><?= esc(lang('Contracts.summary')) ?></h3>
                                    <p class="v3-text-primary text-xs"><?= esc(lang('Contracts.currencyTotal')) ?></p>
                                </div>
                            </div>
                        </div>
                        <div class="p-5 v3-surface contract-panel-body">
                            <div class="grid grid-cols-2 gap-3">
                                <div>
                                    <label class="block text-xs font-semibold v3-text-muted mb-1.5"><?= esc(lang('Contracts.currency')) ?></label>
                                    <?php
                                        $baseCurrency = $base_currency ?? 'ILS';
                                        $selectedCurrency = $contract['currency'] ?? $baseCurrency;
                                    ?>
                                    <select name="currency" class="w-full h-9 px-3 border v3-border rounded-lg text-sm v3-surface focus:ring-2 v3-focus-control v3-focus-control v3-select">
                                        <?php foreach (($currency_options ?? ['ILS']) as $option): ?>
                                            <option value="<?= esc($option) ?>" <?= ($selectedCurrency === $option) ? 'selected' : '' ?>><?= esc($option) ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-xs font-semibold v3-text-muted mb-1.5"><?= esc(lang('Contracts.total')) ?></label>
                                    <input type="number" step="0.01" min="0" name="total_cost" id="contractTotalInput"
                                           value="<?= esc(number_format((float) ($contract['total_cost'] ?? 0), 2, '.', '')) ?>"
                                           class="w-full h-9 px-3 border v3-border-default rounded-lg text-sm font-semibold v3-text-primary contract-total-display v3-surface v3-input">
                                    <p class="mt-1 text-[10px] v3-text-muted"><?= esc(lang('Contracts.calculatedTotal')) ?></p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="v3-surface v3-corner border v3-border overflow-hidden contract-panel">
                        <div class="v3-surface-heading p-5">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center space-x-3">
                                    <div class="w-10 h-10 v3-surface v3-radius flex items-center justify-center">
                                        <i class="fas fa-list v3-text-on-primary"></i>
                                    </div>
                                    <div>
                                        <h3 class="text-lg font-bold"><?= esc(lang('Contracts.treatmentItems')) ?></h3>
                                        <p class="v3-accent-text text-xs"><?= esc(lang('Contracts.itemsHint')) ?></p>
                                    </div>
                                </div>
                                <button type="button" id="addItemBtn" class="inline-flex items-center px-3 py-2 v3-surface v3-hover-soft v3-text-on-primary text-xs font-semibold rounded-lg border v3-border v3-btn v3-btn--secondary">
                                    <i class="fas fa-plus mr-2"></i><?= esc(lang('Contracts.addItem')) ?>
                                </button>
                            </div>
                        </div>
                        <div class="p-4 v3-surface contract-panel-body">
                            <div class="overflow-x-auto rounded-lg border v3-border">
                                <table class="min-w-full text-sm">
                                    <thead class="v3-surface">
                                    <tr>
                                        <th class="px-3 py-2 text-left text-[11px] font-semibold v3-text-muted uppercase tracking-wider"><?= esc(lang('Contracts.code')) ?></th>
                                        <th class="px-3 py-2 text-left text-[11px] font-semibold v3-text-muted uppercase tracking-wider"><?= esc(lang('Contracts.tooth')) ?></th>
                                        <th class="px-3 py-2 text-left text-[11px] font-semibold v3-text-muted uppercase tracking-wider"><?= esc(lang('Contracts.description')) ?></th>
                                        <th class="px-3 py-2 text-left text-[11px] font-semibold v3-text-muted uppercase tracking-wider w-16"><?= esc(lang('Contracts.quantity')) ?></th>
                                        <th class="px-3 py-2 text-left text-[11px] font-semibold v3-text-muted uppercase tracking-wider w-24"><?= esc(lang('Contracts.price')) ?></th>
                                        <th class="px-3 py-2 w-16"></th>
                                    </tr>
                                    </thead>
                                    <tbody id="itemsBody" class="divide-y v3-border-default"></tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <div class="v3-surface v3-corner border v3-border overflow-hidden contract-panel">
                        <div class="v3-surface-heading p-5">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center space-x-3">
                                    <div class="w-10 h-10 v3-surface v3-radius flex items-center justify-center">
                                        <i class="fas fa-pen-fancy v3-text-on-primary"></i>
                                    </div>
                                    <div>
                                        <h3 class="text-lg font-bold"><?= esc(lang('Contracts.contractTerms')) ?></h3>
                                        <p class="v3-text-muted text-xs"><?= esc(lang('Contracts.optionalTerms')) ?></p>
                                    </div>
                                </div>
                                <button type="button"
                                        id="contractTermsToggleBtn"
                                        class="inline-flex items-center px-3 py-2 v3-surface v3-hover-soft v3-text-on-primary text-xs font-semibold rounded-lg border v3-border v3-btn v3-btn--secondary">
                                    <i id="contractTermsToggleIcon" class="fas <?= $hasContractTerms ? 'fa-chevron-up' : 'fa-chevron-down' ?> mr-2"></i>
                                    <span id="contractTermsToggleText"><?= $hasContractTerms ? 'Hide' : 'Show' ?></span>
                                </button>
                            </div>
                        </div>
                        <div id="contractTermsContent" class="p-4 space-y-3 v3-surface contract-panel-body <?= $hasContractTerms ? '' : 'hidden' ?>">
                            <div>
                                <label class="block text-xs font-semibold v3-text-muted mb-1"><?= esc(lang('Contracts.terms')) ?></label>
                                <textarea name="terms_conditions" rows="2" class="w-full px-3 py-2 border v3-border rounded-lg text-sm v3-surface focus:ring-2 v3-focus-control v3-focus-control v3-textarea" placeholder="Optional terms and conditions..."><?= esc($contract['terms_conditions'] ?? '') ?></textarea>
                            </div>
                            <div>
                                <label class="block text-xs font-semibold v3-text-muted mb-1"><?= esc(lang('Contracts.paymentTerms')) ?></label>
                                <textarea name="payment_terms" rows="2" class="w-full px-3 py-2 border v3-border rounded-lg text-sm v3-surface focus:ring-2 v3-focus-control v3-focus-control v3-textarea" placeholder="Optional payment terms..."><?= esc($contract['payment_terms'] ?? '') ?></textarea>
                            </div>
                            <div>
                                <label class="block text-xs font-semibold v3-text-muted mb-1"><?= esc(lang('Contracts.durationPlan')) ?></label>
                                <input type="text" name="duration_plan" value="<?= esc($contract['duration_plan'] ?? '') ?>" class="w-full h-9 px-3 border v3-border rounded-lg text-sm v3-surface focus:ring-2 v3-focus-control v3-focus-control v3-input" placeholder="Optional duration or timeline">
                            </div>
                            <div>
                                <label class="block text-xs font-semibold v3-text-muted mb-1"><?= esc(lang('Contracts.notes')) ?></label>
                                <textarea name="notes" rows="2" class="w-full px-3 py-2 border v3-border rounded-lg text-sm v3-surface focus:ring-2 v3-focus-control v3-focus-control v3-textarea" placeholder="Optional notes for the patient..."><?= esc($contract['notes'] ?? '') ?></textarea>
                            </div>
                        </div>
                    </div>

                    <div class="flex justify-end">
                        <button type="submit" class="v3-action v3-action-primary inline-flex items-center px-5 py-3 v3-radius text-sm font-semibold transition-all duration-200 v3-btn v3-btn--primary">
                            <i class="fas fa-save mr-2"></i><?= esc($mode === 'edit' ? lang('Contracts.saveChanges') : lang('Contracts.saveDraft')) ?>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

<div id="toothModal" class="fixed inset-0 v3-backdrop hidden z-50 transition-opacity duration-300">
    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="v3-surface v3-corner max-w-xl w-full transform transition-all duration-300 scale-95 opacity-0" id="modalContent">
            <div class="v3-surface-heading p-4 rounded-t-2xl relative overflow-hidden">
                <div class="absolute inset-0 v3-backdrop"></div>
                <div class="relative z-10 flex items-center justify-between">
                    <div class="flex items-center space-x-3">
                            <div class="w-10 h-10 v3-surface rounded-lg flex items-center justify-center">
                                <i class="fas fa-layer-group v3-text-on-primary text-lg"></i>
                            </div>
                            <div>
                                <h3 class="text-lg font-bold leading-tight">Tooth <span id="modalToothNumber"></span></h3>
                                <p class="v3-text-warning text-xs"><?= esc(lang('Contracts.conditionDetails')) ?></p>
                            </div>
                    </div>
                    <button type="button" onclick="closeToothModal()" class="w-8 h-8 v3-surface v3-hover-soft rounded-lg flex items-center justify-center transition-colors duration-200 v3-btn v3-btn--secondary">
                        <i class="fas fa-times v3-text-on-primary"></i>
                    </button>
                </div>
            </div>

            <div class="p-4">
                <form id="toothForm" class="space-y-3.5">
                    <input type="hidden" id="toothNumber" name="tooth_number">
                    <div>
                        <label for="condition_type" class="block text-sm font-semibold v3-text-ink mb-1.5"><?= esc(lang('Contracts.conditionType')) ?></label>
                        <select id="condition_type" name="condition_type" class="w-full px-3 py-2.5 border v3-border v3-radius v3-surface v3-select" required>
                            <option value=""><?= esc(lang('Contracts.selectCondition')) ?></option>
                            <?php foreach ($condition_types as $key => $label): ?>
                                <option value="<?= esc($key) ?>"><?= esc($label) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div>
                        <label for="condition_description" class="block text-sm font-semibold v3-text-ink mb-1.5"><?= esc(lang('Contracts.description')) ?></label>
                        <textarea id="condition_description" name="condition_description" rows="2" class="w-full px-3 py-2.5 border v3-border v3-radius v3-surface resize-none v3-textarea" placeholder="Optional description..."></textarea>
                    </div>
                    <div>
                        <label for="treatment_notes" class="block text-sm font-semibold v3-text-ink mb-1.5"><?= esc(lang('Contracts.treatmentNotes')) ?></label>
                        <textarea id="treatment_notes" name="treatment_notes" rows="2" class="w-full px-3 py-2.5 border v3-border v3-radius v3-surface resize-none v3-textarea" placeholder="Optional treatment notes..."></textarea>
                    </div>
                    <div>
                        <label for="treatment_status" class="block text-sm font-semibold v3-text-ink mb-1.5"><?= esc(lang('Contracts.treatmentStatus')) ?></label>
                        <select id="treatment_status" name="treatment_status" class="w-full px-3 py-2.5 border v3-border v3-radius v3-surface v3-select">
                            <option value="pending"><?= esc(lang('Contracts.pending')) ?></option>
                            <option value="in_progress"><?= esc(lang('Contracts.inProgress')) ?></option>
                            <option value="completed"><?= esc(lang('Contracts.completed')) ?></option>
                            <option value="needs_attention"><?= esc(lang('Contracts.needsAttention')) ?></option>
                        </select>
                    </div>
                    <div class="flex items-center justify-between gap-4 pt-3 border-t v3-border">
                        <div class="flex items-center gap-3">
                            <button type="button" onclick="closeToothModal()" class="inline-flex items-center justify-center h-10 px-3 v3-bg-soft v3-hover-soft border v3-border v3-radius text-sm font-medium v3-text-ink whitespace-nowrap v3-btn v3-btn--secondary">
                                <?= esc(lang('Contracts.cancel')) ?>
                            </button>
                            <button type="button" onclick="removeToothCondition()" class="inline-flex items-center justify-center px-4 py-2.5 v3-bg-alert v3-hover-danger border v3-border-default v3-radius text-sm font-medium v3-text-danger whitespace-nowrap v3-btn v3-btn--danger">
                                <?= esc(lang('Contracts.removeStatus')) ?>
                            </button>
                        </div>
                        <button type="submit" class="v3-action v3-action-primary inline-flex items-center justify-center h-10 px-4 min-w-[160px] v3-radius text-sm font-medium whitespace-nowrap v3-btn v3-btn--primary">
                            <i class="fas fa-save mr-2"></i><?= esc(lang('Contracts.applyChanges')) ?>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<div id="batchModal" class="fixed inset-0 v3-backdrop hidden z-50 transition-opacity duration-300">
    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="v3-surface v3-corner max-w-xl w-full transform transition-all duration-300 scale-95 opacity-0" id="batchModalContent">
            <div class="v3-surface-heading p-4 rounded-t-2xl relative overflow-hidden">
                <div class="absolute inset-0 v3-backdrop"></div>
                <div class="relative z-10 flex items-center justify-between">
                    <div class="flex items-center space-x-3">
                        <div class="w-10 h-10 v3-surface rounded-lg flex items-center justify-center">
                            <i class="fas fa-layer-group v3-text-on-primary text-lg"></i>
                        </div>
                        <div>
                            <h3 class="text-lg font-bold leading-tight"><?= esc(lang('Contracts.applySelected')) ?></h3>
                            <p class="v3-text-warning text-xs">Selected: <span id="batchModalCount">0</span> teeth</p>
                        </div>
                    </div>
                    <button type="button" onclick="closeBatchModal()" class="w-8 h-8 v3-surface v3-hover-soft rounded-lg flex items-center justify-center transition-colors duration-200 v3-btn v3-btn--secondary">
                        <i class="fas fa-times v3-text-on-primary"></i>
                    </button>
                </div>
            </div>
            <div class="p-4">
                <form id="batchForm" class="space-y-3.5">
                    <div>
                        <label for="batch_condition_type" class="block text-sm font-semibold v3-text-ink mb-1.5"><?= esc(lang('Contracts.conditionType')) ?></label>
                        <select id="batch_condition_type" name="condition_type" class="w-full px-3 py-2.5 border v3-border v3-radius v3-surface v3-select" required>
                            <option value=""><?= esc(lang('Contracts.selectCondition')) ?></option>
                            <?php foreach ($condition_types as $key => $label): ?>
                                <option value="<?= esc($key) ?>"><?= esc($label) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div>
                        <label for="batch_condition_description" class="block text-sm font-semibold v3-text-ink mb-1.5"><?= esc(lang('Contracts.description')) ?></label>
                        <textarea id="batch_condition_description" name="condition_description" rows="2" class="w-full px-3 py-2.5 border v3-border v3-radius v3-surface resize-none v3-textarea" placeholder="Optional shared description..."></textarea>
                    </div>
                    <div>
                        <label for="batch_treatment_notes" class="block text-sm font-semibold v3-text-ink mb-1.5"><?= esc(lang('Contracts.treatmentNotes')) ?></label>
                        <textarea id="batch_treatment_notes" name="treatment_notes" rows="2" class="w-full px-3 py-2.5 border v3-border v3-radius v3-surface resize-none v3-textarea" placeholder="Optional shared treatment notes..."></textarea>
                    </div>
                    <div>
                        <label for="batch_treatment_status" class="block text-sm font-semibold v3-text-ink mb-1.5"><?= esc(lang('Contracts.treatmentStatus')) ?></label>
                        <select id="batch_treatment_status" name="treatment_status" class="w-full px-3 py-2.5 border v3-border v3-radius v3-surface v3-select">
                            <option value="pending"><?= esc(lang('Contracts.pending')) ?></option>
                            <option value="in_progress"><?= esc(lang('Contracts.inProgress')) ?></option>
                            <option value="completed"><?= esc(lang('Contracts.completed')) ?></option>
                            <option value="needs_attention"><?= esc(lang('Contracts.needsAttention')) ?></option>
                        </select>
                    </div>
                    <div class="flex items-center justify-between gap-4 pt-3 border-t v3-border">
                        <div class="flex items-center gap-3">
                            <button type="button" onclick="closeBatchModal()" class="inline-flex items-center justify-center h-10 px-3 v3-bg-soft v3-hover-soft border v3-border v3-radius text-sm font-medium v3-text-ink whitespace-nowrap v3-btn v3-btn--secondary">
                                <?= esc(lang('Contracts.cancel')) ?>
                            </button>
                            <button type="button" onclick="batchResetSelected()" class="inline-flex items-center justify-center px-4 py-2.5 v3-bg-alert v3-hover-danger border v3-border-default v3-radius text-sm font-medium v3-text-danger whitespace-nowrap v3-btn v3-btn--danger">
                                <?= esc(lang('Contracts.removeStatus')) ?>
                            </button>
                        </div>
                        <button type="submit" id="batchSubmitBtn" class="v3-action v3-action-primary inline-flex items-center justify-center h-10 px-4 min-w-[160px] v3-radius text-sm font-medium whitespace-nowrap v3-btn v3-btn--primary">
                            <i class="fas fa-save mr-2"></i><?= esc(lang('Contracts.applyChanges')) ?>
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
    healthy: 'v3-bg-paid v3-text-primary',
    cavity: 'v3-bg-waiting v3-text-warning',
    filling: 'v3-bg-scheduled v3-text-primary',
    crown: 'v3-bg-chair v3-text-chair',
    root_canal: 'bg-pink-100 text-pink-800',
    extracted: 'v3-bg-alert v3-text-danger',
    implant: 'v3-bg-chair v3-text-chair',
    bridge: 'v3-bg-scheduled v3-text-primary',
    partial_denture: 'v3-bg-waiting v3-text-warning',
    full_denture: 'v3-bg-waiting v3-text-warning',
    other: 'v3-bg-soft v3-text-ink'
};
const statusBadgeClasses = {
    pending: 'v3-bg-waiting v3-text-warning',
    in_progress: 'v3-bg-waiting v3-text-warning',
    completed: 'v3-bg-paid v3-text-primary',
    needs_attention: 'v3-bg-alert v3-text-danger'
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
let batchModeEnabled = false;
const batchSelectedTeeth = new Set();
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
                if (!group.classList.contains('is-selected') && !group.classList.contains('is-batch-selected')) {
                    setOutlineVisible(toothNumber, false);
                }
                hideTooltip();
            });
            hit.addEventListener('focus', (event) => {
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
            hit.addEventListener('keydown', (event) => {
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

function isToothBatchSelected(toothNumber) {
    return batchSelectedTeeth.has(parseInt(toothNumber, 10));
}

function syncBatchToothVisual(toothNumber) {
    const tooth = parseInt(toothNumber, 10);
    const group = toothGroups.get(tooth);
    if (!group) return;

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
        toggleBtn.classList.toggle('v3-bg-warning', batchModeEnabled);
        toggleBtn.classList.toggle('v3-hover-primary', batchModeEnabled);
        toggleBtn.classList.toggle('v3-border-default', batchModeEnabled);
        toggleBtn.classList.toggle('v3-elevation-none', batchModeEnabled);
        toggleBtn.classList.toggle('v3-bg-panel', !batchModeEnabled);
        toggleBtn.classList.toggle('v3-hover-soft', !batchModeEnabled);
        toggleBtn.classList.toggle('v3-border-default', !batchModeEnabled);
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
        applyBtn.classList.toggle('v3-border-default', enabled);
        applyBtn.classList.toggle('v3-elevation-none', enabled);
        applyBtn.classList.toggle('v3-text-on-primary', enabled);

        applyBtn.classList.toggle('v3-bg-panel', !enabled);
        applyBtn.classList.toggle('v3-hover-soft', !enabled);
        applyBtn.classList.toggle('v3-border-default', !enabled);
        applyBtn.classList.toggle('shadow-none', !enabled);
        applyBtn.classList.toggle('v3-text-on-primary', !enabled);
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
    if (shouldEnable === batchModeEnabled) return;

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
    if (!batchModeEnabled || !tooth) return;

    if (batchSelectedTeeth.has(tooth)) {
        batchSelectedTeeth.delete(tooth);
    } else {
        batchSelectedTeeth.add(tooth);
    }
    syncBatchToothVisual(tooth);
    updateBatchUi();
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
    el.className = 'fixed z-50 pointer-events-none px-3 py-2 rounded-md text-xs font-medium v3-elevation-none v3-bg-forest v3-text-on-primary opacity-0 transition-opacity duration-150';
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
    const tooth = parseInt(activeTooth, 10);
    const current = toothGroups.get(tooth);
    current?.classList.remove('is-selected');
    current?.classList.remove('is-hover');
    if (!isToothBatchSelected(tooth)) {
        setOutlineVisible(tooth, false);
    }
    hideTooltip();
    activeTooth = null;
}

function openToothModal(toothNumber) {
    if (batchModeEnabled) return;

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

function openBatchModal() {
    if (!batchModeEnabled) return;
    const selected = batchSelectedTeeth.size;
    if (selected === 0) return;

    const modal = document.getElementById('batchModal');
    const content = document.getElementById('batchModalContent');
    const countEl = document.getElementById('batchModalCount');
    if (!modal || !content) return;

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
    if (!modal || !content) return;

    content.style.transform = 'scale(0.95)';
    content.style.opacity = '0';

    setTimeout(() => {
        modal.classList.add('hidden');
    }, 250);
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

function saveBatchTooth(toothNumber, values) {
    const tooth = parseInt(toothNumber, 10);
    const existingIndex = odontogramData.findIndex(t => parseInt(t.tooth_number, 10) === tooth);
    const updated = {
        tooth_number: tooth,
        condition_type: values.condition_type || 'healthy',
        condition_description: values.condition_description || '',
        treatment_notes: values.treatment_notes || '',
        treatment_status: values.treatment_status || 'pending'
    };

    if (existingIndex !== -1) {
        odontogramData[existingIndex] = updated;
    } else {
        odontogramData.push(updated);
    }

    applyToothVisual(tooth);
}

function batchResetSelected() {
    const selectedTeeth = Array.from(batchSelectedTeeth);
    if (!selectedTeeth.length) return;

    selectedTeeth.forEach(toothNumber => {
        saveBatchTooth(toothNumber, {
            condition_type: 'healthy',
            condition_description: '',
            treatment_notes: '',
            treatment_status: 'completed'
        });
    });

    updateConditionsTable();
    syncItemsFromConditions();
    clearBatchSelection();
    closeBatchModal();
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
        cell.className = 'py-12 text-center v3-text-muted';
        const wrapper = document.createElement('div');
        wrapper.className = 'flex flex-col items-center space-y-3';
        const icon = document.createElement('i');
        icon.className = 'fas fa-smile text-4xl v3-text-primary';
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
        row.className = 'border-b v3-border-default v3-hover-soft transition-colors duration-200';
        row.dataset.tooth = toothNumber;

        const toothCell = document.createElement('td');
        toothCell.className = 'py-2 px-3';
        const toothUniversal = document.createElement('span');
        toothUniversal.className = 'universal-number font-semibold v3-text-primary';
        toothUniversal.textContent = String(toothNumber);
        const toothFdi = document.createElement('span');
        toothFdi.className = 'fdi-number hidden font-semibold v3-text-primary';
        toothFdi.textContent = getFdiNumber(toothNumber);
        toothCell.appendChild(toothUniversal);
        toothCell.appendChild(toothFdi);

        const fdiCell = document.createElement('td');
        fdiCell.className = 'py-2 px-3';
        const fdiUniversal = document.createElement('span');
        fdiUniversal.className = 'universal-number v3-text-muted';
        fdiUniversal.textContent = getFdiNumber(toothNumber);
        const fdiAlt = document.createElement('span');
        fdiAlt.className = 'fdi-number hidden v3-text-muted';
        fdiAlt.textContent = String(toothNumber);
        fdiCell.appendChild(fdiUniversal);
        fdiCell.appendChild(fdiAlt);

        const positionCell = document.createElement('td');
        positionCell.className = 'py-3 px-4 v3-text-muted';
        positionCell.textContent = getToothPosition(toothNumber);

        const conditionCell = document.createElement('td');
        conditionCell.className = 'py-2 px-3';
        const conditionBadge = document.createElement('span');
        conditionBadge.className = `inline-flex items-center px-2 py-1 rounded-full text-xs font-medium ${conditionBadgeClasses[condition] || conditionBadgeClasses.other}`;
        conditionBadge.textContent = (conditionTypes?.[condition] || 'Healthy');
        conditionCell.appendChild(conditionBadge);

        const descriptionCell = document.createElement('td');
        descriptionCell.className = 'py-3 px-4 v3-text-muted max-w-xs truncate';
        descriptionCell.title = description;
        descriptionCell.textContent = description || '-';

        const treatmentCell = document.createElement('td');
        treatmentCell.className = 'py-3 px-4 v3-text-muted max-w-xs truncate';
        treatmentCell.title = treatment;
        treatmentCell.textContent = treatment || '-';

        const statusCell = document.createElement('td');
        statusCell.className = 'py-2 px-3';
        const statusBadge = document.createElement('span');
        statusBadge.className = `inline-flex items-center px-2 py-1 rounded-full text-xs font-medium ${statusBadgeClasses[status] || statusBadgeClasses.completed}`;
        statusBadge.textContent = statusLabels[status] || 'Completed';
        statusCell.appendChild(statusBadge);

        const actionCell = document.createElement('td');
        actionCell.className = 'py-2 px-3 text-center';
        const editLink = document.createElement('a');
        editLink.href = '#';
        editLink.className = 'inline-flex items-center justify-center w-8 h-8 rounded-lg v3-text-primary v3-hover-primary v3-hover-text-primary transition-colors';
        editLink.title = 'Edit tooth ' + toothNumber;
        editLink.innerHTML = '<i class="fas fa-pen text-xs"></i>';
        editLink.addEventListener('click', (e) => { e.preventDefault(); openToothModal(toothNumber); });
        actionCell.appendChild(editLink);

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
        cell.className = 'px-4 py-6 text-center v3-text-muted';
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
        actionCell.className = 'px-2 py-1.5 text-right';
        const removeBtn = document.createElement('button');
        removeBtn.type = 'button';
        removeBtn.className = 'text-[11px] v3-text-danger v3-hover-text-danger font-medium';
        removeBtn.textContent = '×';
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
    cell.className = 'px-2 py-1.5';
    const input = document.createElement('input');
    input.type = type;
    input.name = `items[${index}][${field}]`;
    input.value = value ?? '';
    input.className = 'w-full px-2 py-1.5 border v3-border-default rounded text-xs v3-bg-panel v3-text-ink focus:outline-none focus:border v3-focus-control v3-focus-control';

    if (field === 'qty' || field === 'price') {
        input.step = '0.01'; input.min = '0';
    }
    input.addEventListener('input', () => {
        items[index][field] = (field === 'qty' || field === 'price') ? Number(input.value || 0) : input.value;
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
    document.getElementById('odontogramSvg').value = '';
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

    document.getElementById('batchForm')?.addEventListener('submit', (event) => {
        event.preventDefault();
        const selectedTeeth = Array.from(batchSelectedTeeth);
        if (!selectedTeeth.length) return;

        const form = document.getElementById('batchForm');
        const values = {
            condition_type: form.condition_type.value,
            condition_description: form.condition_description.value,
            treatment_notes: form.treatment_notes.value,
            treatment_status: form.treatment_status.value
        };
        if (!values.condition_type) return;

        selectedTeeth.forEach(toothNumber => {
            saveBatchTooth(toothNumber, values);
        });

        updateConditionsTable();
        syncItemsFromConditions();
        clearBatchSelection();
        closeBatchModal();
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
            legendToggleText.textContent = willShow ? 'Hide Legend' : 'Show Legend';
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
    updateBatchUi();
});
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
    box-shadow:none;
}

.contract-panel-body {
    background:var(--denta-alabaster);
}

.contract-panel input,
.contract-panel select,
.contract-panel textarea {
    background-color: #fff !important;
    border: 1px solid #d3dce8 !important;
    color: #0f172a;
    outline: none !important;
}
.contract-panel input:focus,
.contract-panel select:focus,
.contract-panel textarea:focus {
    box-shadow:none;
    border-color: #93c5fd !important;
}
.contract-total-display {
    box-shadow:none;
}
</style>
<?= $this->endSection() ?>
