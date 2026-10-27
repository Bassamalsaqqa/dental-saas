<?php
$providerRows = [];
$oldTypes = old('provider_type');
if (is_array($oldTypes)) {
    foreach (array_values($oldTypes) as $index => $type) {
        $providerRows[] = [
            'id' => old('provider_payout_id')[$index] ?? '',
            'provider_type' => $type ?: 'user',
            'provider_user_id' => old('provider_user_id')[$index] ?? '',
            'provider_name_snapshot' => old('provider_name_snapshot')[$index] ?? '',
            'split_mode' => old('provider_split_mode')[$index] ?? 'percent',
            'split_value' => old('provider_split_value')[$index] ?? '70',
            'notes' => old('provider_split_notes')[$index] ?? '',
            'mark_paid_now' => (old('provider_mark_paid_now')[$index] ?? '0') === '1',
            'provider_paid_amount' => old('provider_paid_amount')[$index] ?? '',
            'provider_paid_method' => old('provider_paid_method')[$index] ?? 'cash',
            'provider_paid_date' => old('provider_paid_date')[$index] ?? date('Y-m-d'),
        ];
    }
} elseif (!empty($provider_payouts)) {
    foreach (($provider_payouts ?? []) as $row) {
        $providerRows[] = [
            'id' => $row['id'] ?? '',
            'provider_type' => $row['provider_type'] ?? 'user',
            'provider_user_id' => $row['provider_user_id'] ?? '',
            'provider_name_snapshot' => $row['provider_name_snapshot'] ?? '',
            'split_mode' => $row['split_mode'] ?? 'percent',
            'split_value' => $row['split_value'] ?? '70',
            'notes' => $row['notes'] ?? '',
            'mark_paid_now' => false,
            'provider_paid_amount' => '',
            'provider_paid_method' => 'cash',
            'provider_paid_date' => date('Y-m-d'),
        ];
    }
}

if (empty($providerRows)) {
    $providerRows[] = [
        'id' => '',
        'provider_type' => 'user',
        'provider_user_id' => '',
        'provider_name_snapshot' => '',
        'split_mode' => 'percent',
        'split_value' => '70',
        'notes' => '',
        'mark_paid_now' => false,
        'provider_paid_amount' => '',
        'provider_paid_method' => 'cash',
        'provider_paid_date' => date('Y-m-d'),
    ];
}

$splitEnabled = old('provider_split_enabled', !empty($provider_payouts) ? '1' : '');
$idPrefix = preg_replace('/[^A-Za-z0-9_-]/', '', (string) ($id_prefix ?? ''));
$fieldId = static function (string $id) use ($idPrefix): string {
    return $idPrefix !== '' ? $idPrefix . '_' . $id : $id;
};
$providerOptions = [];
foreach (($clinic_providers ?? []) as $provider) {
    $providerOptions[] = [
        'id' => (int) ($provider['id'] ?? 0),
        'label' => trim((string) (($provider['first_name'] ?? '') . ' ' . ($provider['last_name'] ?? ''))),
    ];
}
?>

<div data-provider-split-section class="context-provider-section context-field-stack">
    <div >
        <div>
            <h4 >

                <?= lang('Finance.providerSplit') ?>
            </h4>
        </div>
        <label >
            <input type="checkbox" id="<?= esc($fieldId('providerSplitEnabled')) ?>" name="provider_split_enabled" value="1"  <?= $splitEnabled ? 'checked' : '' ?>>
            <div id="<?= esc($fieldId('providerSplitTrack')) ?>" class="toggle-track">
                <div id="<?= esc($fieldId('providerSplitDot')) ?>" class="toggle-dot"></div>
            </div>
            <span ><?= lang('Finance.enable') ?></span>
        </label>
    </div>

    <div id="<?= esc($fieldId('providerSplitFields')) ?>" class="<?= $splitEnabled ? 'context-field-stack' : 'hidden context-field-stack' ?>">
        <dl class="context-finance-summary"><?php foreach(['splitGrossPreview'=>'grossCollected','splitProviderPreview'=>'totalProviderCompensation','splitClinicPreview'=>'clinicNet'] as $id=>$label): ?><div><dt><?= esc(lang('Finance.'.$label)) ?></dt><dd id="<?= esc($fieldId($id)) ?>">0.00</dd></div><?php endforeach; ?></dl>

        <div >
            <p ><?= lang('Finance.providerSplit') ?></p>
            <button type="button" id="<?= esc($fieldId('addProviderAllocationBtn')) ?>" >
                <?= lang('Finance.addProviderAllocation') ?>
            </button>
        </div>

        <div id="<?= esc($fieldId('providerAllocationsList')) ?>" class="context-field-stack"></div>
        <template data-provider-allocation-template>
<div class="provider-allocation-row context-field-stack">
                <input type="hidden" name="provider_payout_id[]" value="">
                <input type="hidden" name="provider_mark_paid_now[]" class="provider-mark-paid-input" value="0">

                <div >
                    <div>
                        <p ><?= esc(lang('Finance.providerType')) ?></p>
                        <p class="provider-row-share-label"><?= esc(lang('Finance.allocationShare')) ?>: <span class="provider-row-share-value">0.00</span></p>
                    </div>
                    <button type="button" class="provider-remove-row">
                        <?= esc(lang('Finance.removeAllocation')) ?>
                    </button>
                </div>

                <div class="context-field-grid">
                    <div >
                        <label ><?= lang('Finance.providerType') ?></label>
                        <select name="provider_type[]" data-force-select2 data-allow-clear="false" class="provider-type-select">
                            <option value="user" ><?= lang('Finance.clinicUser') ?></option>
                            <option value="guest" ><?= lang('Finance.guestProvider') ?></option>
                        </select>
                    </div>
                    <div class="provider-user-wrap">
                        <label ><?= lang('Finance.providerUser') ?></label>
                        <select name="provider_user_id[]" data-force-select2 data-allow-clear="false" class="provider-user-select">
                            <option value=""><?= esc(lang('Finance.selectUser')) ?></option>
                            <?php foreach ($providerOptions as $providerOption): ?>
                                <option value="<?= esc((string) $providerOption['id']) ?>"><?= esc($providerOption['label']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="provider-guest-wrap hidden">
                        <label ><?= lang('Finance.guestProviderName') ?></label>
                        <input type="text" name="provider_name_snapshot[]" value="" class="provider-guest-input" placeholder="<?= esc(lang('Finance.guestProviderPlaceholder')) ?>">
                    </div>
                </div>

                <div class="context-field-grid">
                    <div >
                        <label ><?= lang('Finance.splitMode') ?></label>
                        <select name="provider_split_mode[]" data-force-select2 data-allow-clear="false" class="provider-split-mode">
                            <option value="percent" ><?= lang('Finance.percentage') ?></option>
                            <option value="fixed" ><?= lang('Finance.fixedAmount') ?></option>
                        </select>
                    </div>
                    <div >
                        <label ><?= lang('Finance.splitValue') ?></label>
                        <input type="number" name="provider_split_value[]" value="0" step="0.01" min="0" class="provider-split-value">
                    </div>
                </div>

                <div >
                    <label ><?= lang('Finance.allocationNotes') ?></label>
                    <textarea name="provider_split_notes[]" rows="2" ></textarea>
                </div>

                <div class="context-field-stack">
                    <label class="provider-pay-toggle">
                        <input type="checkbox" class="provider-paid-toggle" >
                        <span class="toggle-track" aria-hidden="true">
                            <span class="toggle-dot"></span>
                        </span>
                        <span class="provider-pay-toggle-copy">
                            <span class="provider-pay-toggle-title"><?= lang('Finance.markProviderPaidNow') ?></span>
                            <span class="provider-pay-toggle-hint"><?= esc(lang('Finance.paidAmount')) ?> + <?= esc(lang('Finance.method')) ?> + <?= esc(lang('Finance.paidDate')) ?></span>
                        </span>
                    </label>

                    <div class="provider-paid-fields hidden context-field-grid">
                        <div >
                            <label ><?= lang('Finance.paidAmount') ?></label>
                            <div >
                                <input type="number" name="provider_paid_amount[]" value="" step="0.01" min="0" class="provider-paid-amount w-full <?= !empty($is_rtl) ? 'pr-10 pl-10' : 'pl-10 pr-10' ?> py-3 bg-white border-2 border-gray-200 rounded-xl">
                                <div class="absolute inset-y-0 <?= !empty($is_rtl) ? 'right-0 pr-4' : 'left-0 pl-4' ?> flex items-center pointer-events-none"><span ><?= esc(getCurrencySymbol()) ?></span></div>
                            </div>
                        </div>
                        <div >
                            <label ><?= lang('Finance.method') ?></label>
                            <select name="provider_paid_method[]" data-force-select2 data-allow-clear="false" class="provider-paid-method">
                                <option value="cash" ><?= lang('Finance.cash') ?></option>
                                <option value="card" ><?= lang('Finance.card') ?></option>
                                <option value="bank_transfer" ><?= lang('Finance.bankTransfer') ?></option>
                                <option value="check" ><?= lang('Finance.check') ?></option>
                                <option value="other" ><?= lang('Finance.other') ?></option>
                            </select>
                        </div>
                        <div >
                            <label ><?= lang('Finance.paidDate') ?></label>
                            <input type="text" name="provider_paid_date[]" data-clinic-date="1" value="<?= esc(date('Y-m-d')) ?>" class="provider-paid-date">
                        </div>
                    </div>
                </div>
            </div>
        </template>
    </div>
</div>

<script>
(function (providerSplitScript) {
document.addEventListener('DOMContentLoaded', function () {
    const script = providerSplitScript;
    const section = script?.previousElementSibling?.matches('[data-provider-split-section]')
        ? script.previousElementSibling
        : script?.closest('form')?.querySelector('[data-provider-split-section]');
    const form = section?.closest('form');

    if (!section || !form) {
        return;
    }

    const splitToggle = section.querySelector('[name="provider_split_enabled"]');
    const splitFields = section.querySelector('[id$="providerSplitFields"]');
    const splitTrack = section.querySelector('[id$="providerSplitTrack"]');
    const splitDot = section.querySelector('[id$="providerSplitDot"]');
    const allocationsList = section.querySelector('[id$="providerAllocationsList"]');
    const addBtn = section.querySelector('[id$="addProviderAllocationBtn"]');
    const rowTemplate = section.querySelector('[data-provider-allocation-template]');
    const grossInput = form.querySelector('[data-finance-total], #totalAmountLive, [name="total_amount"], [name="amount"]');
    const grossPreview = section.querySelector('[id$="splitGrossPreview"]');
    const providerPreview = section.querySelector('[id$="splitProviderPreview"]');
    const clinicPreview = section.querySelector('[id$="splitClinicPreview"]');
    const initialRows = <?= json_encode($providerRows, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>;

    const parseNum = (val) => {
        const n = parseFloat(val || '0');
        return Number.isFinite(n) ? n : 0;
    };

    const initSelect2 = (select) => {
        if (!select || !window.jQuery || !window.jQuery.fn || !window.jQuery.fn.select2) {
            return;
        }
        const $select = window.jQuery(select);
        if ($select.data('select2')) {
            return;
        }
        const optionCount = select.options ? select.options.length : 0;
        const pageIsRtl = document.documentElement.getAttribute('dir') === 'rtl';
        const dropdownParent = typeof window.getAppSelect2DropdownParent === 'function'
            ? window.getAppSelect2DropdownParent(select)
            : (select.closest('.app-drawer__panel') ? window.jQuery(select.closest('.app-drawer__panel')) : window.jQuery(document.body));
        $select.select2({
            placeholder: select.dataset.placeholder || (pageIsRtl ? 'اختر خياراً...' : 'Select an option...'),
            dir: pageIsRtl ? 'rtl' : 'ltr',
            allowClear: false,
            width: '100%',
            theme: 'default',
            dropdownParent: dropdownParent,
            minimumResultsForSearch: optionCount > 5 ? 0 : Infinity,
            language: {
                noResults: function () {
                    return pageIsRtl ? 'لا توجد نتائج' : 'No results found';
                }
            }
        });
    };

    const initRowSelects = (row) => {
        row.querySelectorAll('select[data-force-select2]').forEach(initSelect2);
    };

    const syncToggleVisual = (row) => {
        const toggle = row.querySelector('.provider-paid-toggle');
        const chip = row.querySelector('.provider-pay-toggle');
        const track = row.querySelector('.provider-pay-toggle .toggle-track');
        const dot = row.querySelector('.provider-pay-toggle .toggle-dot');
        const active = !!toggle?.checked && !!splitToggle?.checked;

        chip?.classList.toggle('is-active', active);
        track?.classList.toggle('bg-blue-500', active);
        track?.classList.toggle('bg-gray-200', !active);

        if (dot) {
            window.applyToggleDotState?.(dot, active);
        }
    };

    const setVisible = (el, visible) => {
        if (!el) return;
        el.classList.toggle('hidden', !visible);
        el.style.display = visible ? '' : 'none';
    };

    const updateRowState = (row) => {
        const type = row.querySelector('.provider-type-select')?.value || 'user';
        setVisible(row.querySelector('.provider-user-wrap'), type === 'user');
        setVisible(row.querySelector('.provider-guest-wrap'), type === 'guest');
        const userSelect = row.querySelector('.provider-user-select');
        const guestInput = row.querySelector('.provider-guest-input');
        if (userSelect) {
            userSelect.required = splitToggle.checked && type === 'user';
            userSelect.disabled = type !== 'user';
            if (type !== 'user') {
                userSelect.value = '';
                if (window.jQuery) {
                    window.jQuery(userSelect).val('').trigger('change');
                }
            }
        }
        if (guestInput) {
            guestInput.required = splitToggle.checked && type === 'guest';
            guestInput.disabled = type !== 'guest';
            if (type !== 'guest') {
                guestInput.value = '';
            }
        }
        if (type === 'user') {
            initSelect2(userSelect);
        }

        const markPaidToggle = row.querySelector('.provider-paid-toggle');
        const markPaidInput = row.querySelector('.provider-mark-paid-input');
        const paidFields = row.querySelector('.provider-paid-fields');
        const active = !!markPaidToggle?.checked && !!splitToggle?.checked;
        if (markPaidInput) {
            markPaidInput.value = active ? '1' : '0';
        }
        setVisible(paidFields, active);
        syncToggleVisual(row);
        if (active) {
            initSelect2(row.querySelector('.provider-paid-method'));
        }

        const paidAmountInput = row.querySelector('.provider-paid-amount');
        const share = computeRowShare(row);
        if (active && paidAmountInput && String(paidAmountInput.value || '').trim() === '') {
            paidAmountInput.value = share.toFixed(2);
        }
    };

    const computeRowShare = (row) => {
        const gross = parseNum(grossInput?.value);
        const mode = row.querySelector('.provider-split-mode')?.value || 'percent';
        const raw = parseNum(row.querySelector('.provider-split-value')?.value);
        let share = 0;
        if (mode === 'fixed') {
            share = Math.max(0, Math.min(raw, gross));
        } else {
            const pct = Math.max(0, Math.min(raw, 100));
            share = gross * (pct / 100);
        }
        share = Math.max(0, Math.min(share, gross));
        const label = row.querySelector('.provider-row-share-value');
        if (label) label.textContent = share.toFixed(2);
        return share;
    };

    const updateSummary = () => {
        const gross = parseNum(grossInput?.value);
        let providerTotal = 0;
        allocationsList.querySelectorAll('.provider-allocation-row').forEach((row) => {
            providerTotal += computeRowShare(row);
            updateRowState(row);
        });
        providerTotal = Math.min(providerTotal, gross);
        const clinicNet = Math.max(0, gross - providerTotal);
        if (grossPreview) grossPreview.textContent = gross.toFixed(2);
        if (providerPreview) providerPreview.textContent = providerTotal.toFixed(2);
        if (clinicPreview) clinicPreview.textContent = clinicNet.toFixed(2);

        const rows = allocationsList.querySelectorAll('.provider-allocation-row');
        rows.forEach((row) => {
            const removeBtn = row.querySelector('.provider-remove-row');
            if (removeBtn) {
                removeBtn.disabled = rows.length <= 1;
                removeBtn.classList.toggle('opacity-50', rows.length <= 1);
            }
        });
    };

    const bindRow = (row) => {
        initRowSelects(row);
        const providerTypeSelect = row.querySelector('.provider-type-select');
        const syncProviderType = () => {
            updateRowState(row);
        };
        providerTypeSelect?.addEventListener('change', syncProviderType);
        if (window.jQuery && providerTypeSelect) {
            window.jQuery(providerTypeSelect).on('select2:select select2:clear change', syncProviderType);
        }
        row.querySelector('.provider-split-mode')?.addEventListener('change', updateSummary);
        row.querySelector('.provider-split-value')?.addEventListener('input', updateSummary);
        row.querySelector('.provider-paid-toggle')?.addEventListener('change', () => {
            const amountInput = row.querySelector('.provider-paid-amount');
            if (row.querySelector('.provider-paid-toggle')?.checked && amountInput && String(amountInput.value || '').trim() === '') {
                amountInput.value = computeRowShare(row).toFixed(2);
            }
            updateRowState(row);
        });
        row.querySelector('.provider-remove-row')?.addEventListener('click', () => {
            if (allocationsList.querySelectorAll('.provider-allocation-row').length <= 1) {
                return;
            }
            row.remove();
            updateSummary();
        });
        updateRowState(row);
    };

    const addRow = (row = {}) => {
        const element = rowTemplate.content.firstElementChild.cloneNode(true);
        const values = {
            provider_payout_id: row.id || '',
            provider_type: ['user', 'guest'].includes(row.provider_type) ? row.provider_type : 'user',
            provider_user_id: row.provider_user_id || '',
            provider_name_snapshot: row.provider_name_snapshot || '',
            provider_split_mode: ['percent', 'fixed'].includes(row.split_mode) ? row.split_mode : 'percent',
            provider_split_value: row.split_value || '0',
            provider_split_notes: row.notes || '',
            provider_paid_amount: row.provider_paid_amount || '',
            provider_paid_method: ['cash', 'card', 'bank_transfer', 'check', 'other'].includes(row.provider_paid_method) ? row.provider_paid_method : 'cash',
            provider_paid_date: row.provider_paid_date || '<?= esc(date('Y-m-d')) ?>',
        };
        Object.entries(values).forEach(([name, value]) => {
            const field = element.querySelector(`[name="${name}[]"]`);
            field.value = String(value);
        });
        element.querySelector('.provider-paid-toggle').checked = !!row.mark_paid_now;
        allocationsList.appendChild(element);
        bindRow(element);
        updateSummary();
    };

    function syncSplitToggleState() {
        if (!splitToggle || !splitFields) return;
        if (splitToggle.checked) {
            splitFields.classList.remove('hidden');
            splitFields.style.display = '';
            splitTrack?.classList.remove('bg-gray-200');
            splitTrack?.classList.add('bg-blue-500');
            if (splitDot) window.applyToggleDotState?.(splitDot, true);
        } else {
            splitFields.classList.add('hidden');
            splitFields.style.display = 'none';
            splitTrack?.classList.remove('bg-blue-500');
            splitTrack?.classList.add('bg-gray-200');
            if (splitDot) window.applyToggleDotState?.(splitDot, false);
        }
        updateSummary();
    }

    initialRows.forEach((row) => addRow(row));
    addBtn?.addEventListener('click', () => addRow({ provider_type: 'user', split_mode: 'percent', split_value: '0', provider_paid_method: 'cash', provider_paid_date: '<?= esc(date('Y-m-d')) ?>' }));
    splitToggle?.addEventListener('change', syncSplitToggleState);
    grossInput?.addEventListener('input', updateSummary);
    const recalcTriggers = [
        form.querySelector('[name="amount_original"]'),
        form.querySelector('[name="exchange_rate"]'),
        form.querySelector('[name="currency"]'),
        form.querySelector('[name="discount_amount"]'),
        form.querySelector('[name="tax_percentage"]'),
        grossInput,
    ];
    recalcTriggers.forEach((el) => {
        if (!el) return;
        ['input', 'change', 'keyup', 'blur'].forEach((evt) => {
            el.addEventListener(evt, () => setTimeout(updateSummary, 0));
        });
    });
    form.addEventListener('finance:totalsUpdated', updateSummary);
    syncSplitToggleState();
});
})(document.currentScript);
</script>
