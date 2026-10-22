(() => {
  const initForm = (target) => {
    const form = target?.matches?.('form') ? target : target?.closest?.('form');
    if (!form || form.dataset.financeCalcBound === '1') return;
    form.dataset.financeCalcBound = '1';

    const field = (selector) => form.querySelector(selector);
    const amountInput = field('#amountOriginal, [name="amount_original"]');
    const rateInput = field('#exchangeRate, [name="exchange_rate"]');
    const currencySelect = field('#currencySelect, [name="currency"]');
    const convertedOutput = field('[data-finance-converted], #amountConverted');
    const discountInput = field('#discountAmount, [name="discount_amount"]');
    const taxInput = field('#taxPercentage, [name="tax_percentage"]');
    const totalOutput = field('[data-finance-total], #totalAmountLive');
    const methodSelect = field('#paymentMethod, [name="payment_method"]');
    const checkField = field('[data-check-scan-field], #checkScanField');
    const checkInput = field('#checkScanInput, [name="check_scan"]');
    const checkName = field('[data-check-scan-name], #checkScanName');
    const transactionTypeSelect = field('[name="transaction_type"]');
    const serviceTypeSelect = field('[name="service_type"]');
    const serviceDetailsField = field('[name="service_details"]');
    const chargePicker = field('[data-clinical-charge-picker]');
    const chargeTotal = field('[data-clinical-charge-total]');
    const chargeCheckboxes = Array.from(form.querySelectorAll('[data-clinical-charge-line]'));
    const optionalToggle = field('[data-finance-optional-toggle]');
    const optionalSections = Array.from(form.querySelectorAll('[data-finance-optional]'));
    const optionalChevron = field('[data-finance-optional-chevron]');
    const paidDateField = field('[data-finance-paid-date-field]');
    const dueDateField = field('[data-finance-due-date-field]');
    const expenseStatusSelect = field('[data-expense-payment-status]');

    const receiptSection = field('[data-initial-receipt-section]');
    const receiptCheckbox = field('[data-initial-receipt]');
    const receiptAmount = field('[data-initial-payment-amount]');

    const baseCurrency = form.getAttribute('data-base-currency') || 'ILS';

    const updateTotal = () => {
      if (!totalOutput) return;
      const converted = parseFloat(convertedOutput?.value || '0') || 0;
      const discount = parseFloat(discountInput?.value || '0') || 0;
      const taxPercent = parseFloat(taxInput?.value || '0') || 0;
      const taxable = Math.max(0, converted - discount);
      const tax = taxable * (taxPercent / 100);
      const total = converted - discount + tax;
      totalOutput.value = total.toFixed(2);
      form.dispatchEvent(new CustomEvent('finance:totalsUpdated'));
    };

    const updateConverted = () => {
      const amount = parseFloat(amountInput?.value || '0') || 0;
      const rate = parseFloat(rateInput?.value || '0') || 0;
      const currency = currencySelect?.value || baseCurrency;
      const effectiveRate = currency === baseCurrency ? 1 : rate;
      const converted = amount * (effectiveRate || 0);
      if (convertedOutput) {
        convertedOutput.value = converted.toFixed(2);
      }
      updateTotal();
    };

    const toggleCheck = () => {
      const isCheck = (methodSelect?.value || '').toLowerCase() === 'check';
      if (checkField) {
        checkField.style.display = isCheck ? 'block' : 'none';
      }
      if (!isCheck) {
        if (checkInput) {
          checkInput.value = '';
        }
        if (checkName) {
          checkName.textContent = '';
        }
      }
    };

    const toggleCurrencyFields = () => {
      const currency = currencySelect?.value || baseCurrency;
      const requiresRate = currency !== baseCurrency;
      const rateField = field('[data-exchange-rate-field], #exchangeRateField');
      const convertedField = field('[data-finance-converted-field]');
      if (rateField) {
        rateField.style.display = requiresRate ? 'block' : 'none';
      }
      if (convertedField) {
        convertedField.style.display = requiresRate ? 'block' : 'none';
      }
      if (rateInput) {
        rateInput.required = requiresRate;
        if (!requiresRate && (!rateInput.value || parseFloat(rateInput.value) <= 0)) {
          rateInput.value = '1';
        }
      }
    };

    const bindLive = (el, fn) => {
      if (!el) return;
      el.addEventListener('input', fn);
      el.addEventListener('change', fn);
      el.addEventListener('keyup', fn);
      el.addEventListener('blur', fn);
    };

    const toggleTransactionDates = () => {
      const transactionType = transactionTypeSelect?.value || form.querySelector('[name="transaction_type"][type="hidden"]')?.value || '';
      const expenseStatus = expenseStatusSelect?.value || '';
      const showPaidDate = expenseStatus
        ? ['paid', 'partial'].includes(expenseStatus)
        : ['payment', 'refund', 'adjustment'].includes(transactionType);
      const showDueDate = expenseStatus
        ? ['pending', 'partial'].includes(expenseStatus)
        : transactionType === 'invoice';
      const setFieldState = (container, visible) => {
        if (!container) return;
        container.classList.toggle('hidden', !visible);
        container.querySelectorAll('input, select, textarea').forEach((input) => {
          input.disabled = !visible;
        });
      };
      setFieldState(paidDateField, showPaidDate);
      setFieldState(dueDateField, showDueDate);
    };

    const setOptionalDetails = (expanded) => {
      optionalSections.forEach((section) => section.classList.toggle('hidden', !expanded));
      optionalToggle?.setAttribute('aria-expanded', expanded ? 'true' : 'false');
      optionalChevron?.classList.toggle('rotate-180', expanded);
    };

    const selectedChargeLines = () => chargeCheckboxes.filter((checkbox) => checkbox.checked);

    const syncClinicalChargePicker = () => {
      const transactionType = transactionTypeSelect?.value || form.querySelector('[name="transaction_type"][type="hidden"]')?.value || '';
      const isInvoice = transactionType === 'invoice';
      if (chargePicker) {
        chargePicker.classList.toggle('hidden', !isInvoice);
      }
      if (!isInvoice) {
        chargeCheckboxes.forEach((checkbox) => {
          checkbox.checked = false;
        });
      }

      if (receiptSection) {
        receiptSection.hidden = !isInvoice;
        if (!isInvoice && receiptCheckbox) receiptCheckbox.checked = false;
        if (receiptCheckbox) receiptCheckbox.disabled = !isInvoice;
        if (receiptAmount) {
          receiptAmount.disabled = !isInvoice || !receiptCheckbox?.checked;
          receiptAmount.required = isInvoice && !!receiptCheckbox?.checked;
        }
      }

      const selected = selectedChargeLines();
      const total = selected.reduce((sum, checkbox) => sum + (parseFloat(checkbox.dataset.amount || '0') || 0), 0);
      const currencies = Array.from(new Set(selected.map((checkbox) => checkbox.dataset.currency || baseCurrency)));
      if (chargeTotal) {
        chargeTotal.textContent = selected.length > 0 && currencies.length === 1
          ? currencies[0] + ' ' + total.toFixed(2)
          : total.toFixed(2);
      }

      if (selected.length > 0) {
        if (amountInput) {
          amountInput.value = total.toFixed(2);
          amountInput.readOnly = true;
        }
        if (currencySelect && currencies.length === 1) {
          currencySelect.value = currencies[0];
          if (window.jQuery) {
            window.jQuery(currencySelect).val(currencies[0]).trigger('change');
          }
        }
        if (serviceTypeSelect) {
          serviceTypeSelect.value = 'treatment';
          if (window.jQuery) {
            window.jQuery(serviceTypeSelect).val('treatment').trigger('change');
          }
        }
        if (serviceDetailsField) {
          serviceDetailsField.value = selected.map((checkbox) => checkbox.dataset.label || '').filter(Boolean).join('\n');
        }
      } else if (amountInput && amountInput.readOnly) {
        amountInput.readOnly = false;
      }

      updateConverted();
    };

    bindLive(amountInput, updateConverted);
    bindLive(rateInput, updateConverted);
    bindLive(discountInput, updateTotal);
    bindLive(taxInput, updateTotal);
    bindLive(currencySelect, updateConverted);

    currencySelect?.addEventListener('change', () => {
      toggleCurrencyFields();
      updateConverted();
    });

    receiptCheckbox?.addEventListener('change', () => {
      syncClinicalChargePicker();
      toggleTransactionDates();
    });
    methodSelect?.addEventListener('change', toggleCheck);
    transactionTypeSelect?.addEventListener('change', () => {
      syncClinicalChargePicker();
      toggleTransactionDates();
    });
    expenseStatusSelect?.addEventListener('change', toggleTransactionDates);
    chargeCheckboxes.forEach((checkbox) => checkbox.addEventListener('change', syncClinicalChargePicker));

    if (window.jQuery) {
      if (currencySelect) {
        window.jQuery(currencySelect).on('select2:select select2:clear change', () => {
          toggleCurrencyFields();
          updateConverted();
        });
      }
      if (methodSelect) {
        window.jQuery(methodSelect).on('select2:select select2:clear change', toggleCheck);
      }
      if (transactionTypeSelect) {
        window.jQuery(transactionTypeSelect).on('select2:select select2:clear change', () => {
          syncClinicalChargePicker();
          toggleTransactionDates();
        });
      }
      if (expenseStatusSelect) {
        window.jQuery(expenseStatusSelect).on('select2:select select2:clear change', toggleTransactionDates);
      }
    }

    optionalToggle?.addEventListener('click', () => {
      setOptionalDetails(optionalToggle.getAttribute('aria-expanded') !== 'true');
    });

    checkInput?.addEventListener('change', () => {
      if (checkName) {
        checkName.textContent = checkInput.files?.[0]?.name || '';
      }
    });

    const recomputeAll = () => {
      toggleCurrencyFields();
      syncClinicalChargePicker();
      toggleTransactionDates();
      updateConverted();
      toggleCheck();
      if (optionalToggle) {
        setOptionalDetails(optionalToggle.getAttribute('aria-expanded') === 'true');
      }
    };

    recomputeAll();
    setTimeout(recomputeAll, 0);
  };

  const init = (root) => {
    const scope = root || document;
    scope.querySelectorAll('[data-finance-calc]').forEach(initForm);
  };

  window.initFinanceCalculators = init;

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', () => init(document));
  } else {
    init(document);
  }
})();
