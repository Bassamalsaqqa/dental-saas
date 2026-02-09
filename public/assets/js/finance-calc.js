(() => {
  const init = () => {
    const form = document.querySelector('[data-finance-calc]');
    if (!form) return;

    const amountInput = document.getElementById('amountOriginal');
    const rateInput = document.getElementById('exchangeRate');
    const currencySelect = document.getElementById('currencySelect');
    const convertedOutput = document.getElementById('amountConverted');
    const discountInput = document.getElementById('discountAmount');
    const taxInput = document.getElementById('taxPercentage');
    const totalOutput = document.getElementById('totalAmountLive');
    const methodSelect = document.getElementById('paymentMethod');
    const checkField = document.getElementById('checkScanField');
    const checkInput = document.getElementById('checkScanInput');
    const checkName = document.getElementById('checkScanName');

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
      if (!methodSelect || !checkField) return;
      const show = methodSelect.value === 'check';
      checkField.style.display = show ? 'block' : 'none';
    };

    const toggleCurrencyFields = () => {
      const currency = currencySelect?.value || baseCurrency;
      const rateField = document.getElementById('exchangeRateField');
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

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init);
  } else {
    init();
  }
})();
