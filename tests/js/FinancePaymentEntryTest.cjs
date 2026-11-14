'use strict';
const assert = require('node:assert/strict');
const fs = require('node:fs');
const vm = require('node:vm');
const source = fs.readFileSync(require('node:path').join(__dirname, '../../public/assets/js/studio-finance-controls.js'), 'utf8');

// Execute the production bindings: opening an existing dialog must never submit a payment.
function openFromHash({ hash = '#record-payment', trigger = true, modal = true, disabled = false, ariaDisabled = null } = {}) {
  const callbacks = new Map(), calls = [], replacements = [];
  const button = trigger ? { disabled, getAttribute: () => ariaDisabled } : null;
  vm.runInNewContext(source, {
    location: { hash, pathname: '/finance/17', search: '?return_to=patient%2F3%3Ftab%3Dfinances' },
    history: { state: { retained: true }, replaceState: (...args) => replacements.push(args) },
    document: {
      addEventListener: (name, callback) => callbacks.set(name, callback),
      querySelector: selector => selector === '[data-finance-open="paymentModal"]' ? button : null,
      getElementById: id => id === 'paymentModal' && modal ? { matches: selector => selector === '[data-finance-modal-root]' } : null,
    },
    window: { openFinanceModal: (...args) => calls.push(args) },
  });
  assert.equal(calls.length, 0, 'Wait for deferred task and form initialization');
  callbacks.get('DOMContentLoaded')();
  return { calls, button, replacements };
}
const permitted = openFromHash();
assert.deepEqual(permitted.calls, [['paymentModal', permitted.button]]);
assert.equal(permitted.replacements.length, 1);
assert.equal(permitted.replacements[0][2], '/finance/17?return_to=patient%2F3%3Ftab%3Dfinances', 'Consume the entry fragment so success reload does not reopen a payment; preserve return context');
for (const options of [
  { hash: '' }, { hash: '#refundModal' }, { trigger: false }, { modal: false },
  { disabled: true }, { ariaDisabled: 'true' },
]) assert.equal(openFromHash(options).calls.length, 0, JSON.stringify(options));
console.log('FinancePaymentEntryTest: permitted entry opens once; missing/disabled controls and unrelated fragments do not open.');
