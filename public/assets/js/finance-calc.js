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
      if (checkField) {
        checkField.style.display = 'block';
      }
    };

    const toggleCurrencyFields = () => {
      const currency = currencySelect?.value || baseCurrency;
      const rateField = field('[data-exchange-rate-field], #exchangeRateField');
      if (rateField) {
        rateField.style.display = 'block';
      }
      if (rateInput) {
        const requiresRate = currency !== baseCurrency;
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

    bindLive(amountInput, updateConverted);
    bindLive(rateInput, updateConverted);
    bindLive(discountInput, updateTotal);
    bindLive(taxInput, updateTotal);
    bindLive(currencySelect, updateConverted);

    currencySelect?.addEventListener('change', () => {
      toggleCurrencyFields();
      updateConverted();
    });

    methodSelect?.addEventListener('change', toggleCheck);

    checkInput?.addEventListener('change', () => {
      if (checkName) {
        checkName.textContent = checkInput.files?.[0]?.name || '';
      }
    });

    const recomputeAll = () => {
      toggleCurrencyFields();
      updateConverted();
      toggleCheck();
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
    document.addEventListener('DOMContentLoaded', init);
  } else {
    init();
  }
})();
