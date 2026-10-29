/* Presentation state for existing financial handlers. Never retries a mutation. */
(() => {
  'use strict';
  const config = document.getElementById('finance-task-config');
  if (!config) return;
  const labels = JSON.parse(config.textContent);
  const states = new WeakMap();
  function state(form) {
    if (!states.has(form)) states.set(form, {phase:'idle', baseline:null, opener:null, confirming:false});
    return states.get(form);
  }
  function snapshot(form) {
    const csrf = document.querySelector('meta[name=csrf-name]')?.content;
    return JSON.stringify(Array.from(new FormData(form))
      .filter(([name]) => name !== csrf)
      .map(([name, value]) => [name, typeof value === 'string' ? value : value.name]));
  }
  function feedback(form, key, history = false) {
    const box = form.querySelector('.finance-task-feedback');
    box.hidden = false;
    box.querySelector('p').textContent = labels[key];
    box.querySelector('a').hidden = !history;
    box.setAttribute('role', history ? 'alert' : 'status');
    if (history) { box.scrollIntoView({block:'nearest'}); box.querySelector('a').focus({preventScroll:true}); }
  }
  function resetDraft(form, task) {
    form.reset();
    for (const field of form.querySelectorAll('[data-clinic-date]')) {
      if (/^\d{4}-\d{2}-\d{2}$/.test(field.value) && window.formatClinicDateFromIso) {
        field.value = window.formatClinicDateFromIso(field.value);
      }
    }
    for (const select of form.querySelectorAll('select')) select.dispatchEvent(new Event('change', {bubbles:true}));
    window.syncSettlementForm?.(form);
    task.baseline = snapshot(form);
    form.querySelector('.finance-task-feedback').hidden = true;
  }
  async function requestClose(id) {
    const modal = document.getElementById(id);
    const form = modal?.querySelector('form');
    if (!form || !modal.open) return;
    const task = state(form);
    if (['pending','uncertain','denied'].includes(task.phase)) {
      const key = task.phase === 'pending' ? 'financePending' : task.phase === 'denied' ? 'financeDenied' : 'financeUnconfirmed';
      feedback(form, key, task.phase !== 'pending');
      return;
    }
    if (task.confirming) return;
    if (task.baseline !== snapshot(form)) {
      if (!window.StudioDialogs) return;
      task.confirming = true;
      const trigger = document.activeElement;
      const discard = await window.StudioDialogs.confirm(labels.financeDiscardHelp, {
        title:labels.financeDiscardTitle, confirmLabel:labels.discardChanges,
        cancelLabel:labels.keepEditing, destructive:true
      });
      task.confirming = false;
      if (!discard) {
        if (trigger?.isConnected) trigger.focus();
        return;
      }
      resetDraft(form, task);
    }
    modal.close();
  }
  function open(id, trigger) {
    const modal = document.getElementById(id);
    const form = modal?.querySelector('form');
    if (!form || modal.open) return;
    const task = state(form);
    task.opener = trigger || document.activeElement;
    document.body.append(modal);
    modal.classList.remove('hidden');
    modal.showModal();
    document.body.classList.add('overflow-hidden');
    if (task.baseline === null) task.baseline = snapshot(form);
  }
  async function submit(form, url, prepare) {
    const task = state(form);
    if (task.phase !== 'idle') return;
    // Capture successful controls before disabling editing; retain the canonical payload.
    const data = new FormData(form);
    prepare?.(data);
    task.phase = 'pending';
    task.disabled = Array.from(form.querySelectorAll('input,select,textarea')).map(field => [field, field.disabled]);
    for (const [field] of task.disabled) field.disabled = true;
    form.setAttribute('aria-busy','true');
    const button = form.querySelector('[type=submit]');
    button.disabled = true;
    feedback(form, 'financePending');
    const controller = new AbortController();
    // Aborting a read of the response does not prove that the server rolled back.
    const timer = setTimeout(() => controller.abort(), 20000);
    try {
      const header = window.csrfConfig?.header || 'X-CSRF-TOKEN';
      const response = await fetch(url, {
        method:'POST', credentials:'same-origin',
        headers:{'X-Requested-With':'XMLHttpRequest', [header]:window.getCsrfToken?.() || ''},
        body:data, signal:controller.signal
      });
      if (response.redirected || [401,403].includes(response.status)) {
        task.phase = 'denied';
        feedback(form, 'financeDenied', true);
        return;
      }
      const result = await response.json();
      if (!response.ok || typeof result.success !== 'boolean') throw Error('Unconfirmed response');
      if (result.csrf_token) window.refreshCsrfToken?.(result.csrf_token);
      if (result.success) {
        task.phase = 'confirmed';
        form.closest('dialog[data-finance-modal-root]').close();
        location.reload();
        return;
      }
      // A recognized application rejection permits correction, never automatic resubmission.
      task.phase = 'idle';
      for (const [field, disabled] of task.disabled) field.disabled = disabled;
      button.disabled = false;
      feedback(form, 'financeRequestFailed');
      if (typeof result.message === 'string') form.querySelector('.finance-task-feedback p').textContent = result.message;
    } catch {
      task.phase = 'uncertain';
      feedback(form, 'financeUnconfirmed', true);
    } finally {
      clearTimeout(timer);
      form.removeAttribute('aria-busy');
    }
  }
  for (const modal of document.querySelectorAll('dialog[data-finance-modal-root]')) {
    for (const eventName of ['cancel','studio:request-close']) modal.addEventListener(eventName, event => {
      event.preventDefault();
      requestClose(modal.id);
    });
    modal.addEventListener('click', event => {
      if (event.target === modal || event.target.classList.contains('finance-inline-modal-backdrop')) requestClose(modal.id);
    });
    modal.addEventListener('close', () => {
      modal.classList.add('hidden');
      if (!document.querySelector('dialog[data-finance-modal-root][open]')) document.body.classList.remove('overflow-hidden');
      const opener = state(modal.querySelector('form')).opener;
      if (opener?.isConnected) requestAnimationFrame(() => opener.focus());
    });
  }
  window.addEventListener('beforeunload', event => {
    const protectedTask = Array.from(document.querySelectorAll('dialog[data-finance-modal-root] form')).some(form => {
      const task = state(form);
      return ['pending','uncertain'].includes(task.phase) ||
        (task.phase === 'idle' && task.baseline !== null && task.baseline !== snapshot(form));
    });
    if (protectedTask) { event.preventDefault(); event.returnValue = ''; }
  });
  window.StudioFinanceTask = {open, requestClose, submit};
})();
