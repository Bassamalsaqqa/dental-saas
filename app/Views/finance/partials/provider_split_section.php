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
$providerOptions = [];
foreach (($clinic_providers ?? []) as $provider) {
    $providerOptions[] = [
        'id' => (int) ($provider['id'] ?? 0),
        'label' => trim((string) (($provider['first_name'] ?? '') . ' ' . ($provider['last_name'] ?? ''))),
    ];
}
?>
<div class="rounded-2xl border border-indigo-100 bg-indigo-50/40 p-6 space-y-5">
    <div class="flex items-center justify-between">
        <div>
            <h4 class="text-base font-bold text-indigo-900 flex items-center gap-2">
                <i class="fas fa-hand-holding-dollar text-indigo-600"></i>
                <?= lang('Finance.providerSplit') ?>
            </h4>
            <p class="text-xs text-indigo-700/80"><?= lang('Finance.providerSplitHelp') ?></p>
            <p class="mt-1 text-xs text-indigo-700/80"><?= lang('Finance.providerSplitMultiHelp') ?></p>
        </div>
        <label class="inline-flex items-center cursor-pointer">
            <input type="checkbox" id="providerSplitEnabled" name="provider_split_enabled" value="1" class="sr-only" <?= $splitEnabled ? 'checked' : '' ?>>
            <div id="providerSplitTrack" class="toggle-track w-11 h-6 bg-gray-200 rounded-full relative">
                <div id="providerSplitDot" class="toggle-dot absolute left-1 top-1 bg-white w-4 h-4 rounded-full"></div>
            </div>
            <span class="<?= !empty($is_rtl) ? 'mr-3' : 'ml-3' ?> text-sm font-semibold text-indigo-900"><?= lang('Finance.enable') ?></span>
        </label>
    </div>

    <div id="providerSplitFields" class="<?= $splitEnabled ? 'space-y-5' : 'hidden space-y-5' ?>">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div class="rounded-xl border border-emerald-200 bg-emerald-50 p-4 shadow-sm">
                <p class="text-xs font-semibold uppercase tracking-wide text-gray-500"><?= lang('Finance.grossCollected') ?></p>
                <p class="mt-1 text-lg font-bold text-emerald-700" id="splitGrossPreview">0.00</p>
            </div>
            <div class="rounded-xl border border-indigo-200 bg-indigo-50 p-4 shadow-sm">
                <p class="text-xs font-semibold uppercase tracking-wide text-gray-500"><?= lang('Finance.totalProviderCompensation') ?></p>
                <p class="mt-1 text-lg font-bold text-indigo-700" id="splitProviderPreview">0.00</p>
            </div>
            <div class="rounded-xl border border-sky-200 bg-sky-50 p-4 shadow-sm">
                <p class="text-xs font-semibold uppercase tracking-wide text-gray-500"><?= lang('Finance.clinicNet') ?></p>
                <p class="mt-1 text-lg font-bold text-sky-700" id="splitClinicPreview">0.00</p>
            </div>
        </div>

        <div class="flex items-center justify-between gap-3">
            <p class="text-xs font-semibold uppercase tracking-[0.16em] text-indigo-800"><?= lang('Finance.providerSplit') ?></p>
            <button type="button" id="addProviderAllocationBtn" class="inline-flex items-center rounded-xl bg-white px-4 py-2 text-sm font-semibold text-indigo-700 border border-indigo-200 hover:bg-indigo-50">
                <i class="fas fa-plus <?= !empty($is_rtl) ? 'ml-2' : 'mr-2' ?>"></i><?= lang('Finance.addProviderAllocation') ?>
            </button>
        </div>

        <div id="providerAllocationsList" class="space-y-4"></div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const splitToggle = document.getElementById('providerSplitEnabled');
    const splitFields = document.getElementById('providerSplitFields');
    const splitTrack = document.getElementById('providerSplitTrack');
    const splitDot = document.getElementById('providerSplitDot');
    const allocationsList = document.getElementById('providerAllocationsList');
    const addBtn = document.getElementById('addProviderAllocationBtn');
    const grossInput = document.getElementById('totalAmountLive');
    const grossPreview = document.getElementById('splitGrossPreview');
    const providerPreview = document.getElementById('splitProviderPreview');
    const clinicPreview = document.getElementById('splitClinicPreview');
    const providerOptions = <?= json_encode($providerOptions, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>;
    const initialRows = <?= json_encode($providerRows, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>;
    const isRtl = <?= !empty($is_rtl) ? 'true' : 'false' ?>;
    const currencySymbol = <?= json_encode(getCurrencySymbol(), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>;

    const parseNum = (val) => {
        const n = parseFloat(val || '0');
        return Number.isFinite(n) ? n : 0;
    };

    const buildProviderOptions = (selected) => {
        const selectedValue = String(selected || '');
        let html = `<option value=""><?= esc(lang('Finance.selectUser')) ?></option>`;
        providerOptions.forEach((option) => {
            const isSelected = selectedValue === String(option.id) ? 'selected' : '';
            html += `<option value="${option.id}" ${isSelected}>${escapeHtml(option.label)}</option>`;
        });
        return html;
    };

    const escapeHtml = (value) => String(value ?? '')
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#039;');

    const createRowHtml = (row = {}) => {
        const providerType = row.provider_type || 'user';
        const splitMode = row.split_mode || 'percent';
        const markPaidNow = row.mark_paid_now ? '1' : '0';
        const checked = markPaidNow === '1' ? 'checked' : '';
        const paidFieldsClass = markPaidNow === '1' ? '' : 'hidden';
        const providerLabel = <?= json_encode(lang('Finance.providerType'), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>;

        return `
            <div class="provider-allocation-row rounded-2xl border border-indigo-200 bg-white p-5 shadow-sm space-y-4">
                <input type="hidden" name="provider_payout_id[]" value="${escapeHtml(row.id || '')}">
                <input type="hidden" name="provider_mark_paid_now[]" class="provider-mark-paid-input" value="${markPaidNow}">

                <div class="flex items-start justify-between gap-3">
                    <div>
                        <p class="text-sm font-bold text-slate-900">${providerLabel}</p>
                        <p class="text-xs text-slate-500 provider-row-share-label"><?= esc(lang('Finance.allocationShare')) ?>: <span class="provider-row-share-value">0.00</span></p>
                    </div>
                    <button type="button" class="provider-remove-row inline-flex items-center rounded-lg border border-rose-200 px-3 py-2 text-xs font-semibold text-rose-700 hover:bg-rose-50">
                        <i class="fas fa-trash ${isRtl ? 'ml-2' : 'mr-2'}"></i>${<?= json_encode(lang('Finance.removeAllocation'), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>}
                    </button>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="group/field relative">
                        <label class="block text-sm font-bold text-gray-700 mb-2"><?= lang('Finance.providerType') ?></label>
                        <select name="provider_type[]" class="provider-type-select w-full px-4 py-3 bg-white border-2 border-gray-200 rounded-xl shadow-sm focus:outline-none focus:ring-4 focus:ring-indigo-500/20 focus:border-indigo-500">
                            <option value="user" ${providerType === 'user' ? 'selected' : ''}><?= lang('Finance.clinicUser') ?></option>
                            <option value="guest" ${providerType === 'guest' ? 'selected' : ''}><?= lang('Finance.guestProvider') ?></option>
                        </select>
                    </div>
                    <div class="provider-user-wrap group/field relative ${providerType === 'user' ? '' : 'hidden'}">
                        <label class="block text-sm font-bold text-gray-700 mb-2"><?= lang('Finance.providerUser') ?></label>
                        <select name="provider_user_id[]" class="provider-user-select w-full px-4 py-3 bg-white border-2 border-gray-200 rounded-xl shadow-sm focus:outline-none focus:ring-4 focus:ring-indigo-500/20 focus:border-indigo-500">
                            ${buildProviderOptions(row.provider_user_id || '')}
                        </select>
                    </div>
                    <div class="provider-guest-wrap group/field relative ${providerType === 'guest' ? '' : 'hidden'}">
                        <label class="block text-sm font-bold text-gray-700 mb-2"><?= lang('Finance.guestProviderName') ?></label>
                        <input type="text" name="provider_name_snapshot[]" value="${escapeHtml(row.provider_name_snapshot || '')}" class="provider-guest-input w-full px-4 py-3 bg-white border-2 border-gray-200 rounded-xl shadow-sm focus:outline-none focus:ring-4 focus:ring-indigo-500/20 focus:border-indigo-500" placeholder="<?= esc(lang('Finance.guestProviderPlaceholder')) ?>">
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="group/field relative">
                        <label class="block text-sm font-bold text-gray-700 mb-2"><?= lang('Finance.splitMode') ?></label>
                        <select name="provider_split_mode[]" class="provider-split-mode w-full px-4 py-3 bg-white border-2 border-gray-200 rounded-xl shadow-sm focus:outline-none focus:ring-4 focus:ring-indigo-500/20 focus:border-indigo-500">
                            <option value="percent" ${splitMode === 'percent' ? 'selected' : ''}><?= lang('Finance.percentage') ?></option>
                            <option value="fixed" ${splitMode === 'fixed' ? 'selected' : ''}><?= lang('Finance.fixedAmount') ?></option>
                        </select>
                    </div>
                    <div class="group/field relative">
                        <label class="block text-sm font-bold text-gray-700 mb-2"><?= lang('Finance.splitValue') ?></label>
                        <input type="number" name="provider_split_value[]" value="${escapeHtml(row.split_value || '0')}" step="0.01" min="0" class="provider-split-value w-full px-4 py-3 bg-white border-2 border-gray-200 rounded-xl shadow-sm focus:outline-none focus:ring-4 focus:ring-indigo-500/20 focus:border-indigo-500">
                    </div>
                </div>

                <div class="group/field relative">
                    <label class="block text-sm font-bold text-gray-700 mb-2"><?= lang('Finance.allocationNotes') ?></label>
                    <textarea name="provider_split_notes[]" rows="2" class="w-full px-4 py-3 bg-white border-2 border-gray-200 rounded-xl shadow-sm focus:outline-none focus:ring-4 focus:ring-indigo-500/20 focus:border-indigo-500 resize-none">${escapeHtml(row.notes || '')}</textarea>
                </div>

                <div class="rounded-xl border border-indigo-200 bg-slate-50 p-4 space-y-4">
                    <label class="inline-flex items-center gap-2 text-sm font-semibold text-gray-800">
                        <input type="checkbox" class="provider-paid-toggle h-4 w-4 rounded border-indigo-300 text-indigo-600 focus:ring-indigo-500" ${checked}>
                        <?= lang('Finance.markProviderPaidNow') ?>
                    </label>

                    <div class="provider-paid-fields grid grid-cols-1 md:grid-cols-3 gap-4 ${paidFieldsClass}">
                        <div class="group/field relative">
                            <label class="block text-sm font-semibold text-gray-700 mb-2"><?= lang('Finance.paidAmount') ?></label>
                            <div class="relative">
                                <input type="number" name="provider_paid_amount[]" value="${escapeHtml(row.provider_paid_amount || '')}" step="0.01" min="0" class="provider-paid-amount w-full ${isRtl ? 'pr-10 pl-10' : 'pl-10 pr-10'} py-3 bg-white border-2 border-gray-200 rounded-xl">
                                <div class="absolute inset-y-0 ${isRtl ? 'right-0 pr-4' : 'left-0 pl-4'} flex items-center pointer-events-none"><span class="text-gray-500 font-bold">${escapeHtml(currencySymbol)}</span></div>
                            </div>
                        </div>
                        <div class="group/field relative">
                            <label class="block text-sm font-semibold text-gray-700 mb-2"><?= lang('Finance.method') ?></label>
                            <select name="provider_paid_method[]" class="provider-paid-method w-full px-4 py-3 bg-white border-2 border-gray-200 rounded-xl">
                                <option value="cash" ${(row.provider_paid_method || 'cash') === 'cash' ? 'selected' : ''}><?= lang('Finance.cash') ?></option>
                                <option value="card" ${(row.provider_paid_method || '') === 'card' ? 'selected' : ''}><?= lang('Finance.card') ?></option>
                                <option value="bank_transfer" ${(row.provider_paid_method || '') === 'bank_transfer' ? 'selected' : ''}><?= lang('Finance.bankTransfer') ?></option>
                                <option value="check" ${(row.provider_paid_method || '') === 'check' ? 'selected' : ''}><?= lang('Finance.check') ?></option>
                                <option value="other" ${(row.provider_paid_method || '') === 'other' ? 'selected' : ''}><?= lang('Finance.other') ?></option>
                            </select>
                        </div>
                        <div class="group/field relative">
                            <label class="block text-sm font-semibold text-gray-700 mb-2"><?= lang('Finance.paidDate') ?></label>
                            <input type="text" name="provider_paid_date[]" data-clinic-date="1" value="${escapeHtml(row.provider_paid_date || '<?= esc(date('Y-m-d')) ?>')}" class="provider-paid-date w-full px-4 py-3 bg-white border-2 border-gray-200 rounded-xl">
                        </div>
                    </div>
                </div>
            </div>`;
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
        if (userSelect) userSelect.required = splitToggle.checked && type === 'user';
        if (guestInput) guestInput.required = splitToggle.checked && type === 'guest';

        const markPaidToggle = row.querySelector('.provider-paid-toggle');
        const markPaidInput = row.querySelector('.provider-mark-paid-input');
        const paidFields = row.querySelector('.provider-paid-fields');
        const active = !!markPaidToggle?.checked && !!splitToggle?.checked;
        if (markPaidInput) {
            markPaidInput.value = active ? '1' : '0';
        }
        setVisible(paidFields, active);

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
        row.querySelector('.provider-type-select')?.addEventListener('change', () => {
            updateRowState(row);
        });
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
        const wrapper = document.createElement('div');
        wrapper.innerHTML = createRowHtml(row);
        const element = wrapper.firstElementChild;
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
        document.getElementById('amountOriginal'),
        document.getElementById('exchangeRate'),
        document.getElementById('currencySelect'),
        document.getElementById('discountAmount'),
        document.getElementById('taxPercentage'),
        grossInput,
    ];
    recalcTriggers.forEach((el) => {
        if (!el) return;
        ['input', 'change', 'keyup', 'blur'].forEach((evt) => {
            el.addEventListener(evt, () => setTimeout(updateSummary, 0));
        });
    });
    document.querySelector('form[data-finance-calc]')?.addEventListener('finance:totalsUpdated', updateSummary);
    syncSplitToggleState();
});
</script>
