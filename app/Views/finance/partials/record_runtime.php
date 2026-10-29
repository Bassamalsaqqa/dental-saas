<script>
const financeBaseCurrency = '<?= esc($baseCurrency) ?>';
const financeModalTriggers = new WeakMap();

function mountFinanceModal(id) {
    const modal = document.getElementById(id);
    if (modal && modal.parentElement !== document.body) {
        document.body.appendChild(modal);
    }
    return modal;
}

function getOpenFinanceModal() {
    return document.querySelector('dialog[data-finance-modal-root][open]');
}

function getFinanceModalFocusable(modal) {
    if (!modal) {
        return [];
    }

    return Array.from(modal.querySelectorAll('button, [href], input, select, textarea, [tabindex]:not([tabindex="-1"])'))
        .filter((element) => !element.disabled && element.offsetParent !== null);
}

function openFinanceModal(id, trigger = null) {
    const modal = mountFinanceModal(id);
    if (!modal) {
        return;
    }
    financeModalTriggers.set(modal, trigger || document.activeElement);
    modal.classList.remove('hidden');
    if (modal instanceof HTMLDialogElement && !modal.open) modal.showModal();
    document.body.classList.add('overflow-hidden');
    const focusable = getFinanceModalFocusable(modal);
    const focusTarget = focusable.find((element) => ['INPUT', 'SELECT', 'TEXTAREA'].includes(element.tagName)) || focusable[0];
    if (focusTarget) {
        window.setTimeout(() => { if (getOpenFinanceModal() === modal) focusTarget.focus(); }, 50);
    }
}

function closeFinanceModal(id) {
    const modal = document.getElementById(id);
    if (!modal) {
        return;
    }
    if (modal instanceof HTMLDialogElement && modal.open) { modal.close(); return; }
    modal.classList.add('hidden');
    if (!getOpenFinanceModal()) {
        document.body.classList.remove('overflow-hidden');
    }
    const trigger = financeModalTriggers.get(modal);
    if (trigger?.isConnected) requestAnimationFrame(() => trigger.focus());
}

function closeOpenFinanceModals() {
    document.querySelectorAll('[data-finance-modal-root]').forEach((modal) => {
        if (!modal.classList.contains('hidden')) {
            closeFinanceModal(modal.id);
        }
    });
}

document.addEventListener('keydown', function (event) {
    const activeDialog = document.activeElement?.closest('dialog[open]');
    if (event.defaultPrevented || (activeDialog && !activeDialog.matches('[data-finance-modal-root]'))) return;
    if (event.key === 'Escape' && getOpenFinanceModal()) {
        event.preventDefault();
        closeOpenFinanceModals();
        return;
    }

}, true);

document.querySelectorAll('[data-finance-modal-root]').forEach((modal) => {
    modal.addEventListener('cancel', event => { event.preventDefault(); closeFinanceModal(modal.id); });
    modal.addEventListener('close', () => closeFinanceModal(modal.id));
    modal.addEventListener('click', function (event) {
        if (event.target === modal || event.target.classList.contains('finance-inline-modal-backdrop')) {
            closeFinanceModal(modal.id);
        }
    });
});

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
        rateInput.classList.add('bg-slate-50');
    } else if (rateInput) {
        rateInput.readOnly = false;
        rateInput.classList.remove('bg-slate-50');
    }

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

const invoicePaymentForm = document.getElementById('invoicePaymentForm');
if (invoicePaymentForm) {
    initSettlementForm(invoicePaymentForm);
    invoicePaymentForm.addEventListener('submit', function (event) {
        event.preventDefault();
        const formData = new FormData(invoicePaymentForm);
        const btn = this.querySelector('button[type="submit"]');
        const originalText = btn.textContent;
        btn.disabled = true;
        btn.textContent = '<?= esc(lang('Finance.processing')) ?>';

        const csrfHeader = window.csrfConfig?.header || 'X-CSRF-TOKEN';
        const csrfToken = window.getCsrfToken ? window.getCsrfToken() : '';

        fetch(`<?= base_url('finance') ?>/<?= $finance['id'] ?>/payments`, {
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
                closeFinanceModal('paymentModal');
                location.reload();
            } else {
                window.StudioDialogs.notice('<?= esc(lang('Finance.errorPrefix')) ?>: ' + data.message);
                btn.disabled = false;
                btn.textContent = originalText;
            }
        })
        .catch(error => {
            console.error('Error:', error);
            window.StudioDialogs.notice('<?= esc(lang('Finance.errorAddingPayment')) ?>');
            btn.disabled = false;
            btn.textContent = originalText;
        });
    });
}

const invoiceRefundForm = document.getElementById('invoiceRefundForm');
if (invoiceRefundForm) {
    initSettlementForm(invoiceRefundForm);
    invoiceRefundForm.addEventListener('submit', function (event) {
        event.preventDefault();
        const formData = new FormData(invoiceRefundForm);
        const btn = this.querySelector('button[type="submit"]');
        const originalText = btn.textContent;
        btn.disabled = true;
        btn.textContent = '<?= esc(lang('Finance.processing')) ?>';

        const csrfHeader = window.csrfConfig?.header || 'X-CSRF-TOKEN';
        const csrfToken = window.getCsrfToken ? window.getCsrfToken() : '';

        fetch(`<?= base_url('finance') ?>/<?= $finance['id'] ?>/payments/refund`, {
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
                closeFinanceModal('refundModal');
                location.reload();
            } else {
                window.StudioDialogs.notice('<?= esc(lang('Finance.errorPrefix')) ?>: ' + data.message);
                btn.disabled = false;
                btn.textContent = originalText;
            }
        })
        .catch(error => {
            console.error('Error:', error);
            window.StudioDialogs.notice('<?= esc(lang('Finance.errorRecordingRefund')) ?>');
            btn.disabled = false;
            btn.textContent = originalText;
        });
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
        const formData = new FormData(form);
        if (methodInput?.value !== 'check') {
            formData.delete('liquidity_account');
        }
        const csrfHeader = window.csrfConfig?.header || 'X-CSRF-TOKEN';
        const csrfToken = window.getCsrfToken ? window.getCsrfToken() : '';
        const url = form.dataset.url;

        fetch(url, {
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
                if (modalToClose) {
                    closeFinanceModal(modalToClose);
                }
                location.reload();
            } else {
                window.StudioDialogs.notice('<?= esc(lang('Finance.errorPrefix')) ?>: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            window.StudioDialogs.notice('<?= esc(lang('Finance.errorRecordingProviderPayout')) ?>');
        });
    });
});
</script>
