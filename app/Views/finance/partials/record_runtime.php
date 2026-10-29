<script>
const financeBaseCurrency = '<?= esc($baseCurrency) ?>';
function openFinanceModal(id, trigger = null) { window.StudioFinanceTask?.open(id, trigger); }
function closeFinanceModal(id) { window.StudioFinanceTask?.requestClose(id); }

function syncSettlementForm(form) {
    if (!form) {
        return;
    }

    const currencyInput = form.querySelector('[data-settlement-currency]');
    const rateInput = form.querySelector('[data-settlement-rate]');
    const amountInput = form.querySelector('input[name="amount_original"], input[name="amount"]');
    const baseOutput = form.querySelector('[data-settlement-base]');
    if (!amountInput || !baseOutput) {
        return;
    }

    const currency = currencyInput ? currencyInput.value : financeBaseCurrency;
    let rate = parseFloat(rateInput ? rateInput.value : '1');
    if (!Number.isFinite(rate) || rate <= 0) {
        rate = 1;
    }

    if (currency === financeBaseCurrency && rateInput) {
        rate = 1;
        rateInput.value = '1';
        rateInput.readOnly = true;

    } else if (rateInput) {
        rateInput.readOnly = false;

    }

    const fx = form.querySelector('[data-settlement-fx]');
    if (fx) fx.hidden = currency === financeBaseCurrency;
    const amount = parseFloat(amountInput.value || '0');
    const baseAmount = Number.isFinite(amount) ? (amount * rate) : 0;
    baseOutput.value = baseAmount.toFixed(2);
}

function initSettlementForm(form) {
    if (!form) {
        return;
    }

    const watchedInputs = form.querySelectorAll('input[name="amount_original"], input[name="amount"], [data-settlement-currency], [data-settlement-rate]');
    watchedInputs.forEach((input) => {
        input.addEventListener('input', function () { syncSettlementForm(form); });
        input.addEventListener('change', function () { syncSettlementForm(form); });
    });

    syncSettlementForm(form);
}

async function markAsPaid(transactionId) {
    if (window.StudioDialogs && await window.StudioDialogs.confirm(<?= json_encode(lang('Finance.confirmMarkTransactionPaid'),JSON_HEX_TAG|JSON_HEX_AMP|JSON_HEX_APOS|JSON_HEX_QUOT) ?>,{title:<?= json_encode(lang('Finance.markAsPaid'),JSON_HEX_TAG|JSON_HEX_AMP|JSON_HEX_APOS|JSON_HEX_QUOT) ?>,confirmLabel:<?= json_encode(lang('Finance.markAsPaid'),JSON_HEX_TAG|JSON_HEX_AMP|JSON_HEX_APOS|JSON_HEX_QUOT) ?>,subject:<?= json_encode((string)($finance['invoice_number']??$finance['id']),JSON_HEX_TAG|JSON_HEX_AMP|JSON_HEX_APOS|JSON_HEX_QUOT) ?>})) {
        const csrfHeader = window.csrfConfig?.header || 'X-CSRF-TOKEN';
        const csrfToken = window.getCsrfToken ? window.getCsrfToken() : '';
        fetch(`<?= base_url('finance') ?>/${transactionId}/mark-paid`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                [csrfHeader]: csrfToken
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.csrf_token) {
                window.refreshCsrfToken(data.csrf_token);
            }
            if (data.success) {
                location.reload();
            } else {
                window.StudioDialogs.notice('<?= lang('Finance.errorPrefix') ?>: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            window.StudioDialogs.notice('<?= lang('Finance.errorUpdatingTransaction') ?>');
        });
    }
}

async function deleteFinance(id) {
    if (window.StudioDialogs && await window.StudioDialogs.confirm(<?= json_encode(lang('Finance.confirmDeleteTransaction'),JSON_HEX_TAG|JSON_HEX_AMP|JSON_HEX_APOS|JSON_HEX_QUOT) ?>,{title:<?= json_encode(lang('Studio.delete'),JSON_HEX_TAG|JSON_HEX_AMP|JSON_HEX_APOS|JSON_HEX_QUOT) ?>,confirmLabel:<?= json_encode(lang('Studio.delete'),JSON_HEX_TAG|JSON_HEX_AMP|JSON_HEX_APOS|JSON_HEX_QUOT) ?>,subject:<?= json_encode((string)($finance['invoice_number']??$finance['id']),JSON_HEX_TAG|JSON_HEX_AMP|JSON_HEX_APOS|JSON_HEX_QUOT) ?>,destructive:true})) {
        const csrfHeader = window.csrfConfig?.header || 'X-CSRF-TOKEN';
        const csrfToken = window.getCsrfToken ? window.getCsrfToken() : '';
        fetch(`<?= base_url('finance') ?>/${id}`, {
            method: 'DELETE',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                [csrfHeader]: csrfToken
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.csrf_token) {
                window.refreshCsrfToken(data.csrf_token);
            }
            if (data.success) {
                window.location.href = '<?= base_url('finance') ?>';
            } else {
                window.StudioDialogs.notice('<?= lang('Finance.errorPrefix') ?>: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            window.StudioDialogs.notice('<?= lang('Finance.errorDeletingTransaction') ?>');
        });
    }
}

async function closeBalance(transactionId) {
    if (!window.StudioDialogs) return;
    const reason = await window.StudioDialogs.reason(<?= json_encode(lang('Finance.confirmCloseBalance'),JSON_HEX_TAG|JSON_HEX_AMP|JSON_HEX_APOS|JSON_HEX_QUOT) ?>,<?= json_encode(lang('Finance.reasonOptionalPrompt'),JSON_HEX_TAG|JSON_HEX_AMP|JSON_HEX_APOS|JSON_HEX_QUOT) ?>,{title:<?= json_encode(lang('Finance.closeBalanceCredit'),JSON_HEX_TAG|JSON_HEX_AMP|JSON_HEX_APOS|JSON_HEX_QUOT) ?>,confirmLabel:<?= json_encode(lang('Finance.closeBalanceCredit'),JSON_HEX_TAG|JSON_HEX_AMP|JSON_HEX_APOS|JSON_HEX_QUOT) ?>,subject:<?= json_encode((string)($finance['invoice_number']??$finance['id']),JSON_HEX_TAG|JSON_HEX_AMP|JSON_HEX_APOS|JSON_HEX_QUOT) ?>,destructive:true});
    if (reason === null) return;
    const formData = new FormData();
    formData.append('reason', reason);

    const csrfHeader = window.csrfConfig?.header || 'X-CSRF-TOKEN';
    const csrfToken = window.getCsrfToken ? window.getCsrfToken() : '';

    fetch(`<?= base_url('finance') ?>/${transactionId}/close-balance`, {
        method: 'POST',
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            [csrfHeader]: csrfToken
        },
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.csrf_token) {
            window.refreshCsrfToken(data.csrf_token);
        }
        if (data.success) {
            location.reload();
        } else {
            window.StudioDialogs.notice('<?= lang('Finance.errorPrefix') ?>: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        window.StudioDialogs.notice('<?= lang('Finance.errorClosingBalance') ?>');
    });
}

for (const [id, suffix] of [['invoicePaymentForm','payments'],['invoiceRefundForm','payments/refund']]) {
    const form = document.getElementById(id);
    if (!form) continue;
    initSettlementForm(form);
    form.addEventListener('submit', event => {
        event.preventDefault();
        window.StudioFinanceTask?.submit(form, <?= json_encode(base_url('finance/'.(int)$finance['id'].'/'),JSON_HEX_TAG|JSON_HEX_AMP|JSON_HEX_APOS|JSON_HEX_QUOT) ?> + suffix);
    });
}

async function voidPayment(paymentId) {
    if (!window.StudioDialogs) return;
    const reason = await window.StudioDialogs.reason(<?= json_encode(lang('Finance.confirmVoidPayment'),JSON_HEX_TAG|JSON_HEX_AMP|JSON_HEX_APOS|JSON_HEX_QUOT) ?>,<?= json_encode(lang('Finance.voidReasonPrompt'),JSON_HEX_TAG|JSON_HEX_AMP|JSON_HEX_APOS|JSON_HEX_QUOT) ?>,{title:<?= json_encode(lang('Finance.voidPayment'),JSON_HEX_TAG|JSON_HEX_AMP|JSON_HEX_APOS|JSON_HEX_QUOT) ?>,confirmLabel:<?= json_encode(lang('Finance.voidPayment'),JSON_HEX_TAG|JSON_HEX_AMP|JSON_HEX_APOS|JSON_HEX_QUOT) ?>,subject:<?= json_encode((string)($finance['invoice_number']??$finance['id']),JSON_HEX_TAG|JSON_HEX_AMP|JSON_HEX_APOS|JSON_HEX_QUOT) ?>+' · #'+paymentId,destructive:true});
    if (reason === null) return;
    const formData = new FormData();
    formData.append('notes', reason);

    const csrfHeader = window.csrfConfig?.header || 'X-CSRF-TOKEN';
    const csrfToken = window.getCsrfToken ? window.getCsrfToken() : '';

    fetch(`<?= base_url('finance') ?>/<?= $finance['id'] ?>/payments/${paymentId}/void`, {
        method: 'POST',
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            [csrfHeader]: csrfToken
        },
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.csrf_token) {
            window.refreshCsrfToken(data.csrf_token);
        }
        if (data.success) {
            location.reload();
        } else {
            window.StudioDialogs.notice('<?= esc(lang('Finance.errorPrefix')) ?>: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        window.StudioDialogs.notice('<?= esc(lang('Finance.errorVoidingPayment')) ?>');
    });
}

function syncExpenseSettlementLiquidityField() {
    const settlementMethod = document.querySelector('#invoicePaymentForm select[name="payment_method"]');
    const settlementField = document.getElementById('settlementLiquidityAccountField');
    const settlementInput = document.getElementById('settlementLiquidityAccount');
    if (!settlementField || !settlementMethod) {
        return;
    }

    settlementField.classList.remove('hidden');
    if (settlementInput) {
        settlementInput.disabled = false;
        if (!settlementInput.value) {
            const defaultAccount = settlementMethod.value === 'cash' ? 'safe_cash' : 'bank';
            settlementInput.value = defaultAccount;
            if (window.jQuery) {
                window.jQuery(settlementInput).val(defaultAccount).trigger('change');
            }
        }
    }
}

document.querySelector('#invoicePaymentForm select[name="payment_method"]')?.addEventListener('change', syncExpenseSettlementLiquidityField);
syncExpenseSettlementLiquidityField();

document.querySelectorAll('.provider-payout-payment-form').forEach((form) => {
    const methodInput = form.querySelector('.provider-payout-method-input');
    const liquidityField = form.querySelector('.provider-payout-liquidity-account-field');
    const liquidityInput = form.querySelector('select[name="liquidity_account"]');
    const modalToClose = form.dataset.financeModalClose;

    const syncProviderPayoutLiquidityField = () => {
        if (!methodInput || !liquidityField) {
            return;
        }
        const isCheck = methodInput.value === 'check';
        liquidityField.classList.toggle('hidden', !isCheck);
        if (liquidityInput) {
            liquidityInput.disabled = !isCheck;
            if (!isCheck) {
                liquidityInput.value = 'bank';
                if (window.jQuery) {
                    window.jQuery(liquidityInput).val('bank').trigger('change');
                }
            }
        }
    };

    methodInput?.addEventListener('change', syncProviderPayoutLiquidityField);
    syncProviderPayoutLiquidityField();

    form.addEventListener('submit', function (event) {
        event.preventDefault();
        window.StudioFinanceTask?.submit(form, form.dataset.url, data => {
            if (methodInput?.value !== 'check') data.delete('liquidity_account');
        });
    });
});
</script>
