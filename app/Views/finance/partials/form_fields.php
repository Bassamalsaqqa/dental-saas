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
$inputClass = 'context-field-control';
$selectClass = $inputClass . ' ' . $leadingFieldPadding;
$showOptionalDetails = old('examination_id') !== null
    || trim((string) old('service_details', '')) !== ''
    || trim((string) old('description', '')) !== ''
    || trim((string) old('notes', '')) !== '';
?>
<div class="contextual-fields context-money-fields" data-finance-calc data-provider-split data-base-currency="<?= esc($baseCurrency) ?>">
    <?php if ($contextual && !empty($selectedPatient['id'])): ?>
        <?= view('patient/partials/context_patient_badge', ['context_patient' => $selectedPatient]) ?>
        <input type="hidden" name="patient_id" value="<?= (int) $selectedPatient['id'] ?>">
    <?php else: ?>
        <div>
            <label ><?= lang('Finance.patientRequired') ?></label>
            <select name="patient_id" id="<?= esc($fieldId('patientSelect')) ?>" class="<?= esc($inputClass) ?>" data-searchable-select data-minimum-input-length="0" data-limit="5" data-search-url="<?= base_url('api/v1/search/patients') ?>" data-placeholder="<?= esc(lang('Finance.searchPatientsPlaceholder')) ?>" required>
                <option value=""><?= lang('Finance.selectPatient') ?></option>
                <?php foreach (($patients ?? []) as $patientRow): ?><option value="<?= (int) $patientRow['id'] ?>" selected><?= esc($patientRow['first_name'] . ' ' . $patientRow['last_name']) ?> (<?= esc($patientRow['phone'] ?? '') ?>)</option><?php endforeach; ?>
            </select>
        </div>
    <?php endif; ?>

    <?= view('finance/partials/clinical_charge_line_picker', [
        'clinical_charge_lines' => $clinical_charge_lines ?? [],
        'selected_clinical_charge_line_ids' => $selected_clinical_charge_line_ids ?? [],
        'is_rtl' => $isRtlPartial,
    ]) ?>

    <?php if ($lockTransactionType && $transactionTypePreset !== ''): ?>
        <input type="hidden" name="transaction_type" value="<?= esc($transactionTypePreset) ?>">
    <?php endif; ?>

    <div class="context-field-grid">
        <?php if (!$lockTransactionType): ?>
            <div>
                <label ><?= lang('Finance.transactionTypeRequired') ?></label>
                <select name="transaction_type" data-force-select2 data-allow-clear="false" class="<?= esc($selectClass) ?>" required>
                    <option value=""><?= lang('Finance.selectType') ?></option>
                    <?php foreach (['payment', 'invoice', 'refund', 'adjustment'] as $type): ?><option value="<?= esc($type) ?>" <?= $transactionTypePreset === $type ? 'selected' : '' ?>><?= lang('Finance.' . $type) ?></option><?php endforeach; ?>
                </select>
            </div>
        <?php else: ?>
            <div class="context-fixed-type">
                <p><?= esc(lang('Finance.transactionTypeRequired')) ?></p>
                <p><?= esc(lang('Finance.' . $transactionTypePreset)) ?></p>
            </div>
        <?php endif; ?>

        <div>
            <label ><?= lang('Finance.amountOriginalRequired') ?></label>
            <input type="number" name="amount_original" id="<?= esc($fieldId('amountOriginal')) ?>" value="<?= old('amount_original') ?>" step="0.01" min="0" class="<?= esc($inputClass) ?>" placeholder="0.00" required>
        </div>
        <div>
            <label ><?= lang('Finance.currencyRequired') ?></label>
            <select name="currency" id="<?= esc($fieldId('currencySelect')) ?>" data-force-select2 data-allow-clear="false" class="<?= esc($selectClass) ?>" required>
                <?php foreach (($currency_options ?? [$baseCurrency]) as $option): ?><option value="<?= esc($option) ?>" <?= old('currency', $baseCurrency) === $option ? 'selected' : '' ?>><?= esc($option) ?></option><?php endforeach; ?>
            </select>
        </div>
        <div id="<?= esc($fieldId('exchangeRateField')) ?>" data-exchange-rate-field>
            <label ><?= lang('Finance.exchangeRateRequired') ?></label>
            <input type="number" name="exchange_rate" id="<?= esc($fieldId('exchangeRate')) ?>" value="<?= old('exchange_rate', 1) ?>" step="0.0001" min="0" class="<?= esc($inputClass) ?>" placeholder="1.0000">
            <p ><?= lang('Finance.exchangeRateHelp') ?> (<?= esc($baseCurrency) ?>).</p>
        </div>
        <div data-finance-converted-field>
            <label ><?= lang('Finance.convertedAmount') ?> (<?= esc($baseCurrency) ?>)</label>
            <input type="text" id="<?= esc($fieldId('amountConverted')) ?>" data-finance-converted readonly class="<?= esc($inputClass) ?> bg-slate-100" value="0.00">
        </div>
        <div>
            <label ><?= lang('Finance.paymentMethod') ?></label>
            <select name="payment_method" id="<?= esc($fieldId('paymentMethod')) ?>" data-force-select2 data-allow-clear="false" class="<?= esc($selectClass) ?>" required>
                <option value=""><?= lang('Finance.selectMethod') ?></option>
                <?php foreach (['cash', 'card', 'bank_transfer', 'check', 'other'] as $method): ?><option value="<?= esc($method) ?>" <?= old('payment_method') === $method ? 'selected' : '' ?>><?= lang('Finance.' . ($method === 'bank_transfer' ? 'bankTransfer' : $method)) ?></option><?php endforeach; ?>
            </select>
        </div>
        <?php if ($showLiquidityAccount): ?>
            <div>
                <label ><?= lang('Finance.paidFromAccount') ?></label>
                <select name="liquidity_account" id="<?= esc($fieldId('liquidityAccount')) ?>" data-force-select2 class="<?= esc($selectClass) ?>">
                    <option value=""><?= lang('Finance.select') !== 'Finance.select' ? lang('Finance.select') : '-' ?></option>
                    <?php foreach (($liquidity_account_options ?? []) as $key => $label): ?><option value="<?= esc($key) ?>" <?= old('liquidity_account') === $key ? 'selected' : '' ?>><?= esc($label) ?></option><?php endforeach; ?>
                </select>
            </div>
        <?php endif; ?>
    </div>

    <div >
        <button type="button" class="context-optional-button" data-finance-optional-toggle aria-expanded="<?= $showOptionalDetails ? 'true' : 'false' ?>" >
            <span >

                <?= esc(lang('Finance.optionalDetails')) ?>
            </span>

        </button>
    </div>

    <div class="<?= $showOptionalDetails ? '' : 'hidden' ?>" data-finance-optional>
        <label ><?= lang('Finance.examination') ?></label>
        <select name="examination_id" id="<?= esc($fieldId('examinationSelect')) ?>" class="<?= esc($selectClass) ?>" data-searchable-select data-minimum-input-length="0" data-limit="5" data-depends-on="<?= esc($fieldId('patientSelect')) ?>" data-depends-param="patient_id" data-require-dependency="<?= $contextual ? 'false' : 'true' ?>" data-extra-params="<?= $contextual && !empty($selectedPatient['id']) ? esc(json_encode(['patient_id' => (int) $selectedPatient['id']])) : '' ?>" data-status="all" data-search-url="<?= base_url('api/v1/search/examinations') ?>" data-placeholder="<?= esc(lang('Finance.searchExaminationsPlaceholder')) ?>">
            <option value=""><?= lang('Finance.selectExaminationOptional') ?></option>
        </select>
    </div>

    <div class="context-field-grid">
        <div>
            <label ><?= lang('Finance.transactionDate') ?></label>
            <input type="text" name="transaction_date" data-clinic-date="1" value="<?= esc(old('transaction_date', date('Y-m-d'))) ?>" class="<?= esc($inputClass) ?>">
        </div>
        <div data-finance-paid-date-field>
            <label ><?= lang('Finance.paymentDate') ?></label>
            <input type="text" name="paid_date" data-clinic-date="1" value="<?= esc(old('paid_date', date('Y-m-d'))) ?>" class="<?= esc($inputClass) ?>">
        </div>
        <div data-finance-due-date-field>
            <label ><?= lang('Finance.dueDate') ?></label>
            <input type="text" name="due_date" data-clinic-date="1" value="<?= esc(old('due_date')) ?>" class="<?= esc($inputClass) ?>">
        </div>
    </div>

    <div >
        <label ><?= lang('Finance.serviceTypeRequired') ?></label>
        <select name="service_type" data-force-select2 data-allow-clear="false" class="<?= esc($selectClass) ?>" required>
            <option value=""><?= lang('Finance.selectServiceType') ?></option>
            <?php foreach (['consultation', 'treatment', 'medication', 'procedure', 'other'] as $type): ?><option value="<?= esc($type) ?>" <?= old('service_type') === $type ? 'selected' : '' ?>><?= lang('Finance.' . $type) ?></option><?php endforeach; ?>
        </select>
    </div>
    <div class="<?= $showOptionalDetails ? '' : 'hidden' ?>" data-finance-optional>
        <label ><?= lang('Finance.serviceDetails') ?></label>
        <textarea name="service_details" rows="3" class="<?= esc($inputClass) ?>" placeholder="<?= esc(lang('Finance.serviceDetailsPlaceholder')) ?>"><?= old('service_details') ?></textarea>
    </div>

    <?php $advancedDetailsBody = static function () use ($fieldId, $inputClass, $baseCurrency, $clinic_providers, $isRtlPartial, $idPrefix): string {
        ob_start();
        ?>
        <div class="context-field-stack">
            <div id="<?= esc($fieldId('checkScanField')) ?>" data-check-scan-field>
                <label ><?= lang('Finance.checkScanOptional') ?></label>
                <input type="file" name="check_scan" id="<?= esc($fieldId('checkScanInput')) ?>" accept=".pdf,.jpg,.jpeg,.png" >
                <span id="<?= esc($fieldId('checkScanName')) ?>" data-check-scan-name ></span>
            </div>
            <div class="context-field-grid">
                <div>
                    <label ><?= lang('Finance.discountAmount') ?></label>
                    <input type="number" name="discount_amount" id="<?= esc($fieldId('discountAmount')) ?>" value="<?= old('discount_amount', 0) ?>" step="0.01" min="0" class="<?= esc($inputClass) ?>" placeholder="0.00">
                </div>
                <div>
                    <label ><?= lang('Finance.taxPercentage') ?></label>
                    <input type="number" name="tax_percentage" id="<?= esc($fieldId('taxPercentage')) ?>" value="<?= old('tax_percentage', 0) ?>" step="0.01" min="0" max="100" class="<?= esc($inputClass) ?>" placeholder="0.00">
                </div>
                <div>
                    <label ><?= lang('Finance.total') ?> (<?= esc($baseCurrency) ?>)</label>
                    <input type="text" id="<?= esc($fieldId('totalAmountLive')) ?>" data-finance-total readonly class="<?= esc($inputClass) ?>" value="0.00">
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
        <div >
            <?= $advancedDetailsBody() ?>
        </div>
    <?php else: ?>
        <details class="context-advanced" open>
            <summary ><?= esc(lang('UI.advancedDetails')) ?></summary>
            <div >
                <?= $advancedDetailsBody() ?>
            </div>
        </details>
    <?php endif; ?>

    <div class="<?= $showOptionalDetails ? '' : 'hidden' ?>" data-finance-optional>
        <label ><?= lang('Finance.description') ?></label>
        <textarea name="description" rows="4" class="<?= esc($inputClass) ?>" placeholder="<?= esc(lang('Finance.transactionDescriptionPlaceholder')) ?>"><?= old('description') ?></textarea>
    </div>
    <div class="<?= $showOptionalDetails ? '' : 'hidden' ?>" data-finance-optional>
        <label ><?= lang('Finance.notes') ?></label>
        <textarea name="notes" rows="3" class="<?= esc($inputClass) ?>" placeholder="<?= esc(lang('Finance.additionalNotesPlaceholder')) ?>"><?= old('notes') ?></textarea>
    </div>
</div>
