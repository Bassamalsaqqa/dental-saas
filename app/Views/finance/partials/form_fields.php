<?php
$contextual = !empty($contextual);
$selectedPatient = $selected_patient ?? (!empty($patients[0]) ? $patients[0] : null);
$transactionTypePreset = (string) ($transaction_type_preset ?? old('transaction_type', ''));
$lockTransactionType = !empty($lock_transaction_type);
$showLiquidityAccount = array_key_exists('show_liquidity_account', get_defined_vars())
    ? (bool) $show_liquidity_account
    : true;
$advancedDetailsStyle = (string) ($advanced_details_style ?? 'details');
$useFlatAdvancedDetails = $advancedDetailsStyle === 'flat';
$isRtlPartial = !empty($is_rtl);
$baseCurrency = (string) ($base_currency ?? 'ILS');
$idPrefix = preg_replace('/[^A-Za-z0-9_-]/', '', (string) ($id_prefix ?? ''));
$fieldId = static function (string $id) use ($idPrefix): string {
    return $idPrefix !== '' ? $idPrefix . '_' . $id : $id;
};
$leadingFieldPadding = 'finance-leading-input';
$inputClass = 'w-full rounded-xl border-2 border-slate-200 bg-white px-4 py-3 text-sm font-medium text-slate-900 shadow-sm transition focus:border-blue-500 focus:outline-none focus:ring-4 focus:ring-blue-500/15';
$selectClass = $inputClass . ' ' . $leadingFieldPadding;
?>
<div data-finance-calc data-provider-split data-base-currency="<?= esc($baseCurrency) ?>">
    <?php if ($contextual && !empty($selectedPatient['id'])): ?>
        <?= view('patient/partials/context_patient_badge', ['context_patient' => $selectedPatient]) ?>
        <input type="hidden" name="patient_id" value="<?= (int) $selectedPatient['id'] ?>">
    <?php else: ?>
        <div>
            <label class="mb-2 block text-sm font-semibold text-slate-700"><?= lang('Finance.patientRequired') ?></label>
            <select name="patient_id" id="<?= esc($fieldId('patientSelect')) ?>" class="<?= esc($inputClass) ?>" data-searchable-select data-minimum-input-length="0" data-limit="5" data-search-url="<?= base_url('api/v1/search/patients') ?>" data-placeholder="<?= esc(lang('Finance.searchPatientsPlaceholder')) ?>" required>
                <option value=""><?= lang('Finance.selectPatient') ?></option>
                <?php foreach (($patients ?? []) as $patientRow): ?><option value="<?= (int) $patientRow['id'] ?>" selected><?= esc($patientRow['first_name'] . ' ' . $patientRow['last_name']) ?> (<?= esc($patientRow['phone'] ?? '') ?>)</option><?php endforeach; ?>
            </select>
        </div>
    <?php endif; ?>

    <?= view('finance/partials/clinical_charge_line_picker', [
        'clinical_charge_lines' => $clinical_charge_lines ?? [],
        'is_rtl' => $isRtlPartial,
    ]) ?>

    <?php if ($lockTransactionType && $transactionTypePreset !== ''): ?>
        <input type="hidden" name="transaction_type" value="<?= esc($transactionTypePreset) ?>">
    <?php endif; ?>

    <div class="grid grid-cols-1 gap-5 md:grid-cols-2">
        <?php if (!$lockTransactionType): ?>
            <div>
                <label class="mb-2 block text-sm font-semibold text-slate-700"><?= lang('Finance.transactionTypeRequired') ?></label>
                <select name="transaction_type" data-force-select2 data-allow-clear="false" class="<?= esc($selectClass) ?>" required>
                    <option value=""><?= lang('Finance.selectType') ?></option>
                    <?php foreach (['payment', 'invoice', 'refund', 'adjustment'] as $type): ?><option value="<?= esc($type) ?>" <?= $transactionTypePreset === $type ? 'selected' : '' ?>><?= lang('Finance.' . $type) ?></option><?php endforeach; ?>
                </select>
            </div>
        <?php else: ?>
            <div class="rounded-xl border border-slate-200 bg-slate-50 p-4">
                <p class="text-xs font-bold uppercase tracking-[0.15em] text-slate-500"><?= esc(lang('Finance.transactionTypeRequired')) ?></p>
                <p class="mt-1 text-sm font-bold text-slate-900"><?= esc(lang('Finance.' . $transactionTypePreset)) ?></p>
            </div>
        <?php endif; ?>

        <div>
            <label class="mb-2 block text-sm font-semibold text-slate-700"><?= lang('Finance.amountOriginalRequired') ?></label>
            <input type="number" name="amount_original" id="<?= esc($fieldId('amountOriginal')) ?>" value="<?= old('amount_original') ?>" step="0.01" min="0" class="<?= esc($inputClass) ?>" placeholder="0.00" required>
        </div>
        <div>
            <label class="mb-2 block text-sm font-semibold text-slate-700"><?= lang('Finance.currencyRequired') ?></label>
            <select name="currency" id="<?= esc($fieldId('currencySelect')) ?>" data-force-select2 data-allow-clear="false" class="<?= esc($selectClass) ?>" required>
                <?php foreach (($currency_options ?? [$baseCurrency]) as $option): ?><option value="<?= esc($option) ?>" <?= old('currency', $baseCurrency) === $option ? 'selected' : '' ?>><?= esc($option) ?></option><?php endforeach; ?>
            </select>
        </div>
        <div id="<?= esc($fieldId('exchangeRateField')) ?>" data-exchange-rate-field>
            <label class="mb-2 block text-sm font-semibold text-slate-700"><?= lang('Finance.exchangeRateRequired') ?></label>
            <input type="number" name="exchange_rate" id="<?= esc($fieldId('exchangeRate')) ?>" value="<?= old('exchange_rate', 1) ?>" step="0.0001" min="0" class="<?= esc($inputClass) ?>" placeholder="1.0000">
            <p class="mt-2 text-xs text-slate-500"><?= lang('Finance.exchangeRateHelp') ?> (<?= esc($baseCurrency) ?>).</p>
        </div>
        <div>
            <label class="mb-2 block text-sm font-semibold text-slate-700"><?= lang('Finance.convertedAmount') ?> (<?= esc($baseCurrency) ?>)</label>
            <input type="text" id="<?= esc($fieldId('amountConverted')) ?>" data-finance-converted readonly class="<?= esc($inputClass) ?> bg-slate-100" value="0.00">
        </div>
        <div>
            <label class="mb-2 block text-sm font-semibold text-slate-700"><?= lang('Finance.paymentMethod') ?></label>
            <select name="payment_method" id="<?= esc($fieldId('paymentMethod')) ?>" data-force-select2 data-allow-clear="false" class="<?= esc($selectClass) ?>" required>
                <option value=""><?= lang('Finance.selectMethod') ?></option>
                <?php foreach (['cash', 'card', 'bank_transfer', 'check', 'other'] as $method): ?><option value="<?= esc($method) ?>" <?= old('payment_method') === $method ? 'selected' : '' ?>><?= lang('Finance.' . ($method === 'bank_transfer' ? 'bankTransfer' : $method)) ?></option><?php endforeach; ?>
            </select>
        </div>
        <?php if ($showLiquidityAccount): ?>
            <div>
                <label class="mb-2 block text-sm font-semibold text-slate-700"><?= lang('Finance.paidFromAccount') ?></label>
                <select name="liquidity_account" id="<?= esc($fieldId('liquidityAccount')) ?>" data-force-select2 class="<?= esc($selectClass) ?>">
                    <option value=""><?= lang('Finance.select') !== 'Finance.select' ? lang('Finance.select') : '-' ?></option>
                    <?php foreach (($liquidity_account_options ?? []) as $key => $label): ?><option value="<?= esc($key) ?>" <?= old('liquidity_account') === $key ? 'selected' : '' ?>><?= esc($label) ?></option><?php endforeach; ?>
                </select>
            </div>
        <?php endif; ?>
    </div>

    <div class="mt-5">
        <label class="mb-2 block text-sm font-semibold text-slate-700"><?= lang('Finance.examination') ?></label>
        <select name="examination_id" id="<?= esc($fieldId('examinationSelect')) ?>" class="<?= esc($selectClass) ?>" data-searchable-select data-minimum-input-length="0" data-limit="5" data-depends-on="<?= esc($fieldId('patientSelect')) ?>" data-depends-param="patient_id" data-require-dependency="<?= $contextual ? 'false' : 'true' ?>" data-extra-params="<?= $contextual && !empty($selectedPatient['id']) ? esc(json_encode(['patient_id' => (int) $selectedPatient['id']])) : '' ?>" data-status="all" data-search-url="<?= base_url('api/v1/search/examinations') ?>" data-placeholder="<?= esc(lang('Finance.searchExaminationsPlaceholder')) ?>">
            <option value=""><?= lang('Finance.selectExaminationOptional') ?></option>
        </select>
    </div>

    <div class="mt-5 grid grid-cols-1 gap-5 md:grid-cols-3">
        <div>
            <label class="mb-2 block text-sm font-semibold text-slate-700"><?= lang('Finance.transactionDate') ?></label>
            <input type="text" name="transaction_date" data-clinic-date="1" value="<?= esc(old('transaction_date', date('Y-m-d'))) ?>" class="<?= esc($inputClass) ?>">
        </div>
        <div>
            <label class="mb-2 block text-sm font-semibold text-slate-700"><?= lang('Finance.paymentDate') ?></label>
            <input type="text" name="paid_date" data-clinic-date="1" value="<?= esc(old('paid_date', date('Y-m-d'))) ?>" class="<?= esc($inputClass) ?>">
        </div>
        <div>
            <label class="mb-2 block text-sm font-semibold text-slate-700"><?= lang('Finance.dueDate') ?></label>
            <input type="text" name="due_date" data-clinic-date="1" value="<?= esc(old('due_date')) ?>" class="<?= esc($inputClass) ?>">
        </div>
    </div>

    <div class="mt-5">
        <label class="mb-2 block text-sm font-semibold text-slate-700"><?= lang('Finance.serviceTypeRequired') ?></label>
        <select name="service_type" data-force-select2 data-allow-clear="false" class="<?= esc($selectClass) ?>" required>
            <option value=""><?= lang('Finance.selectServiceType') ?></option>
            <?php foreach (['consultation', 'treatment', 'medication', 'procedure', 'other'] as $type): ?><option value="<?= esc($type) ?>" <?= old('service_type') === $type ? 'selected' : '' ?>><?= lang('Finance.' . $type) ?></option><?php endforeach; ?>
        </select>
    </div>
    <div class="mt-5">
        <label class="mb-2 block text-sm font-semibold text-slate-700"><?= lang('Finance.serviceDetails') ?></label>
        <textarea name="service_details" rows="3" class="<?= esc($inputClass) ?> resize-y" placeholder="<?= esc(lang('Finance.serviceDetailsPlaceholder')) ?>"><?= old('service_details') ?></textarea>
    </div>

    <?php $advancedDetailsBody = static function () use ($fieldId, $inputClass, $baseCurrency, $clinic_providers, $isRtlPartial, $idPrefix): string {
        ob_start();
        ?>
        <div class="space-y-5">
            <div id="<?= esc($fieldId('checkScanField')) ?>" data-check-scan-field>
                <label class="mb-2 block text-sm font-semibold text-slate-700"><?= lang('Finance.checkScanOptional') ?></label>
                <input type="file" name="check_scan" id="<?= esc($fieldId('checkScanInput')) ?>" accept=".pdf,.jpg,.jpeg,.png" class="block w-full rounded-xl border border-slate-300 px-3 py-2.5 text-sm text-slate-700 file:me-3 file:rounded-lg file:border-0 file:bg-slate-100 file:px-3 file:py-2 file:text-sm file:font-semibold file:text-slate-700 hover:file:bg-slate-200">
                <span id="<?= esc($fieldId('checkScanName')) ?>" data-check-scan-name class="mt-2 block text-xs text-slate-400"></span>
            </div>
            <div class="grid grid-cols-1 gap-5 md:grid-cols-3">
                <div>
                    <label class="mb-2 block text-sm font-semibold text-slate-700"><?= lang('Finance.discountAmount') ?></label>
                    <input type="number" name="discount_amount" id="<?= esc($fieldId('discountAmount')) ?>" value="<?= old('discount_amount', 0) ?>" step="0.01" min="0" class="<?= esc($inputClass) ?>" placeholder="0.00">
                </div>
                <div>
                    <label class="mb-2 block text-sm font-semibold text-slate-700"><?= lang('Finance.taxPercentage') ?></label>
                    <input type="number" name="tax_percentage" id="<?= esc($fieldId('taxPercentage')) ?>" value="<?= old('tax_percentage', 0) ?>" step="0.01" min="0" max="100" class="<?= esc($inputClass) ?>" placeholder="0.00">
                </div>
                <div>
                    <label class="mb-2 block text-sm font-semibold text-slate-700"><?= lang('Finance.total') ?> (<?= esc($baseCurrency) ?>)</label>
                    <input type="text" id="<?= esc($fieldId('totalAmountLive')) ?>" data-finance-total readonly class="<?= esc($inputClass) ?> border-emerald-200 bg-emerald-50 font-bold text-emerald-700" value="0.00">
                    <p class="mt-2 text-xs text-slate-500"><?= lang('Finance.totalFormulaHelp') ?></p>
                </div>
            </div>
            <?= view('finance/partials/provider_split_section', [
                'clinic_providers' => $clinic_providers ?? [],
                'provider_payouts' => [],
                'is_rtl' => $isRtlPartial,
                'id_prefix' => $idPrefix,
            ]) ?>
        </div>
        <?php
        return (string) ob_get_clean();
    }; ?>

    <?php if ($useFlatAdvancedDetails): ?>
        <div class="mt-5">
            <?= $advancedDetailsBody() ?>
        </div>
    <?php else: ?>
        <details class="mt-5 rounded-2xl border border-slate-200 bg-slate-50/80 p-4" open>
            <summary class="cursor-pointer text-sm font-bold text-slate-800"><?= esc(lang('UI.advancedDetails')) ?></summary>
            <div class="mt-4">
                <?= $advancedDetailsBody() ?>
            </div>
        </details>
    <?php endif; ?>

    <div class="mt-5">
        <label class="mb-2 block text-sm font-semibold text-slate-700"><?= lang('Finance.description') ?></label>
        <textarea name="description" rows="4" class="<?= esc($inputClass) ?> resize-y" placeholder="<?= esc(lang('Finance.transactionDescriptionPlaceholder')) ?>"><?= old('description') ?></textarea>
    </div>
    <div class="mt-5">
        <label class="mb-2 block text-sm font-semibold text-slate-700"><?= lang('Finance.notes') ?></label>
        <textarea name="notes" rows="3" class="<?= esc($inputClass) ?> resize-y" placeholder="<?= esc(lang('Finance.additionalNotesPlaceholder')) ?>"><?= old('notes') ?></textarea>
    </div>
</div>
