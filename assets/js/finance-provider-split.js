(() => {
  const init = () => {
    const form = document.querySelector('[data-provider-split]');
    if (!form) return;

    const splitEnabled = document.getElementById('providerSplitEnabled');
    const splitFields = document.getElementById('providerSplitFields');
    const splitTrack = document.getElementById('providerSplitTrack');
    const splitDot = document.getElementById('providerSplitDot');
    const providerType = document.getElementById('providerType');
    const providerUserWrap = document.getElementById('providerUserWrap');
    const providerGuestWrap = document.getElementById('providerGuestWrap');
    const providerUserId = document.getElementById('providerUserId');
    const providerGuestName = document.getElementById('providerGuestName');

    const splitMode = document.getElementById('providerSplitMode');
    const splitValue = document.getElementById('providerSplitValue');
    const totalOutput = document.getElementById('totalAmountLive');

    const grossPreview = document.getElementById('splitGrossPreview');
    const providerPreview = document.getElementById('splitProviderPreview');
    const clinicPreview = document.getElementById('splitClinicPreview');

    const markPaidNow = document.getElementById('providerMarkPaidNow');
    const paidNowFields = document.getElementById('providerPaidNowFields');
    const providerPaidAmount = document.getElementById('providerPaidAmount');
    const providerPaidMethod = document.getElementById('providerPaidMethod');
    const providerPaidDate = document.getElementById('providerPaidDate');

    if (!splitEnabled || !splitFields) return;

    const setVisible = (el, visible) => {
      if (!el) return;
      if (visible) {
        el.classList.remove('hidden');
        el.style.display = '';
      } else {
        el.classList.add('hidden');
        el.style.display = 'none';
      }
    };

    const parseAmount = (value) => {
      const n = parseFloat(value || '0');
      return Number.isFinite(n) ? n : 0;
    };

    const computeShare = (gross) => {
      const mode = splitMode?.value || 'percent';
      const value = parseAmount(splitValue?.value);
      let providerShare = 0;

      if (mode === 'fixed') {
        providerShare = Math.min(Math.max(value, 0), gross);
      } else {
        const pct = Math.min(Math.max(value, 0), 100);
        providerShare = gross * (pct / 100);
      }

      providerShare = Math.max(0, Math.min(providerShare, gross));
      return {
        providerShare,
        clinicShare: Math.max(0, gross - providerShare),
      };
    };

    const updateProviderType = () => {
      const type = providerType?.value || 'user';
      setVisible(providerUserWrap, type === 'user');
      setVisible(providerGuestWrap, type === 'guest');

      if (providerUserId) providerUserId.required = splitEnabled.checked && type === 'user';
      if (providerGuestName) providerGuestName.required = splitEnabled.checked && type === 'guest';
    };

    const keepPaidAmountInBounds = () => {
      if (!providerPaidAmount) return;
      const gross = parseAmount(totalOutput?.value);
      const { providerShare } = computeShare(gross);
      const entered = parseAmount(providerPaidAmount.value);
      const bounded = Math.max(0, Math.min(entered, providerShare));
      if (entered !== bounded) {
        providerPaidAmount.value = bounded.toFixed(2);
      }
    };

    const renderPreview = () => {
      const gross = parseAmount(totalOutput?.value);
      const { providerShare, clinicShare } = computeShare(gross);

      if (grossPreview) grossPreview.textContent = gross.toFixed(2);
      if (providerPreview) providerPreview.textContent = providerShare.toFixed(2);
      if (clinicPreview) clinicPreview.textContent = clinicShare.toFixed(2);

      if (providerPaidAmount && markPaidNow?.checked) {
        providerPaidAmount.value = providerShare.toFixed(2);
      }
      keepPaidAmountInBounds();
    };

    const updateMarkPaidNow = () => {
      const active = !!markPaidNow?.checked && !!splitEnabled.checked;
      setVisible(paidNowFields, active);

      if (providerPaidAmount) providerPaidAmount.required = active;
      if (providerPaidMethod) providerPaidMethod.required = active;
      if (providerPaidDate) providerPaidDate.required = active;
      renderPreview();
    };

    const updateSplitEnabled = () => {
      const enabled = splitEnabled.checked;
      setVisible(splitFields, enabled);
      if (splitTrack) {
        splitTrack.classList.toggle('bg-gray-200', !enabled);
        splitTrack.classList.toggle('bg-blue-500', enabled);
      }
      if (splitDot) {
        splitDot.style.transform = enabled ? 'translateX(20px)' : 'translateX(0)';
      }

      if (splitMode) splitMode.required = enabled;
      if (splitValue) splitValue.required = enabled;
      if (!enabled) {
        if (providerUserId) providerUserId.required = false;
        if (providerGuestName) providerGuestName.required = false;
      } else {
        updateProviderType();
      }

      updateMarkPaidNow();
      renderPreview();
    };

    splitEnabled.addEventListener('change', updateSplitEnabled);
    providerType?.addEventListener('change', updateProviderType);
    splitMode?.addEventListener('change', renderPreview);
    splitValue?.addEventListener('input', renderPreview);
    splitValue?.addEventListener('change', renderPreview);
    markPaidNow?.addEventListener('change', updateMarkPaidNow);
    providerPaidAmount?.addEventListener('input', keepPaidAmountInBounds);
    totalOutput?.addEventListener('input', renderPreview);
    form.addEventListener('finance:totalsUpdated', renderPreview);

    updateProviderType();
    updateMarkPaidNow();
    updateSplitEnabled();
  };

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init);
  } else {
    init();
  }
})();
