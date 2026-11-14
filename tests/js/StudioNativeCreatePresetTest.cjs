'use strict';
const assert = require('node:assert/strict');
const fs = require('node:fs');
const path = require('node:path');
const vm = require('node:vm');

class MockElement {
  constructor(tag = 'div', name = '') {
    this.tagName = tag.toUpperCase();
    this.name = name;
    this.type = tag === 'form' ? '' : 'text';
    this.value = '';
    this.dataset = {};
    this.options = [];
    this.children = [];
    this.listeners = {};
    this.attributes = {};
    this.hidden = false;
    this.open = false;
    this.classList = {
      classes: new Set(),
      add: (c) => this.classList.classes.add(c),
      remove: (c) => this.classList.classes.delete(c),
      contains: (c) => this.classList.classes.has(c)
    };
  }
  getAttribute(n) { return this.attributes[n] ?? null; }
  setAttribute(n, v) { this.attributes[n] = String(v); }
  removeAttribute(n) { delete this.attributes[n]; }
  hasAttribute(n) { return Object.prototype.hasOwnProperty.call(this.attributes, n); }
  toggleAttribute(n, force) {
    if (force !== undefined) {
      if (force) this.setAttribute(n, '');
      else this.removeAttribute(n);
    } else {
      if (this.hasAttribute(n)) this.removeAttribute(n);
      else this.setAttribute(n, '');
    }
  }
  addEventListener(n, h) {
    if (!this.listeners[n]) this.listeners[n] = [];
    this.listeners[n].push(h);
  }
  dispatchEvent(e) {
    const list = this.listeners[e.type] || [];
    for (const h of list) h(e);
    if (e.bubbles && this.parentElement) this.parentElement.dispatchEvent(e);
  }
  append(...nodes) {
    for (const node of nodes) {
      node.parentElement = this;
      this.children.push(node);
    }
  }
  replaceChildren(...nodes) {
    this.children = [];
    this.append(...nodes);
  }
  querySelector(sel) {
    return this._matchSelector(sel);
  }
  querySelectorAll(sel) {
    const results = [];
    this._matchAllSelector(sel, results);
    return results;
  }
  _matchSelector(sel) {
    for (const child of this.children) {
      if (child._matches(sel)) return child;
      const res = child._matchSelector(sel);
      if (res) return res;
    }
    return null;
  }
  _matchAllSelector(sel, results) {
    for (const child of this.children) {
      if (child._matches(sel)) results.push(child);
      child._matchAllSelector(sel, results);
    }
  }
  _matches(sel) {
    if (sel.startsWith('#')) return this.id === sel.slice(1);
    if (sel.startsWith('.')) return this.classList.contains(sel.slice(1));
    const attrMatch = sel.match(/^([a-z0-9_-]*)\[([a-z0-9_-]+)(?:=["']?([^"']*)["']?)?\]$/i);
    if (attrMatch) {
      const [, tag, attr, val] = attrMatch;
      if (tag && this.tagName.toLowerCase() !== tag.toLowerCase()) return false;
      const cleanAttr = attr.replace(/^data-/, '').replace(/-([a-z])/g, (_, l) => l.toUpperCase());
      const hasAttr = this.hasAttribute(attr) || this.dataset[cleanAttr] !== undefined;
      if (!hasAttr) return false;
      if (val !== undefined) {
        return this.getAttribute(attr) === val || this.dataset[cleanAttr] === val;
      }
      return true;
    }
    return this.tagName.toLowerCase() === sel.toLowerCase();
  }
  closest(sel) {
    if (this._matches(sel)) return this;
    return this.parentElement?.closest(sel) || null;
  }
  showModal() { this.open = true; }
  close() { this.open = false; }
  focus() {}
  reset() {}
}

function createHarness(serverCategory = '') {
  const document = new MockElement('document');
  const body = new MockElement('body');
  document.body = body;

  const dialog = new MockElement('dialog');
  dialog.id = 'finance-create-dialog';
  dialog.dataset.nativeCreateDialog = '';
  dialog.dataset.pending = 'Submitting...';
  dialog.dataset.fullPage = 'https://example.test/finance/create?return_to=finance';
  dialog.dataset.expenseFullPage = 'https://example.test/finance/expenses/create?return_to=finance';
  dialog.dataset.salaryFullPage = 'https://example.test/finance/expenses/create?category=salaries&return_to=finance';
  dialog.dataset.transactionTitle = 'Create Transaction';
  dialog.dataset.transactionSubmit = 'Create Transaction';
  dialog.dataset.expenseTitle = 'New Expense';
  dialog.dataset.expenseSubmit = 'Record Expense';
  dialog.dataset.salaryTitle = 'Record staff salary';
  dialog.dataset.salarySubmit = 'Record staff salary';

  const discardSection = new MockElement('section');
  discardSection.dataset.nativeDiscard = '';
  discardSection.hidden = true;
  const keepBtn = new MockElement('button');
  keepBtn.dataset.nativeKeep = '';
  const confirmDiscardBtn = new MockElement('button');
  confirmDiscardBtn.dataset.nativeConfirmDiscard = '';
  discardSection.append(keepBtn, confirmDiscardBtn);

  const dialogBody = new MockElement('div');
  dialogBody.dataset.nativeBody = '';

  const titleEl = new MockElement('h2');
  titleEl.dataset.nativeTitle = '';
  titleEl.textContent = 'Create Transaction';

  const submitEl = new MockElement('button');
  submitEl.dataset.nativeSubmit = '';
  submitEl.textContent = 'Create Transaction';

  const fullpageEl = new MockElement('a');
  fullpageEl.dataset.nativeFullpage = '';
  fullpageEl.href = 'https://example.test/finance/create?return_to=finance';

  const templateExpense = new MockElement('template');
  templateExpense.dataset.nativeTemplate = 'expense';

  // Construct template form content
  const expenseForm = new MockElement('form');
  const catSelect = new MockElement('select', 'expense_category');
  for (const optVal of ['', 'salaries', 'rent', 'laboratory', 'utilities']) {
    const opt = new MockElement('option');
    opt.value = optVal;
    catSelect.options.push(opt);
    catSelect.append(opt);
  }
  const counterpartyInput = new MockElement('input', 'counterparty_name');
  counterpartyInput.name = 'counterparty_name';

  expenseForm.elements = {
    namedItem: (name) => {
      if (name === 'expense_category') return catSelect;
      if (name === 'counterparty_name') return counterpartyInput;
      if (name === 'transaction_type') return null;
      return null;
    },
    [Symbol.iterator]: function* () {
      yield catSelect;
      yield counterpartyInput;
    }
  };
  expenseForm.elements[0] = catSelect;
  expenseForm.elements[1] = counterpartyInput;
  expenseForm.append(catSelect, counterpartyInput);

  templateExpense.content = {
    cloneNode: () => {
      // Cloned tree
      const formClone = new MockElement('form');
      const catClone = new MockElement('select', 'expense_category');
      catClone.value = serverCategory;
      for (const optVal of ['', 'salaries', 'rent', 'laboratory', 'utilities']) {
        const opt = new MockElement('option');
        opt.value = optVal;
        catClone.options.push(opt);
        catClone.append(opt);
      }
      const payeeClone = new MockElement('input', 'counterparty_name');
      payeeClone.name = 'counterparty_name';
      formClone.elements = {
        namedItem: (name) => {
          if (name === 'expense_category') return catClone;
          if (name === 'counterparty_name') return payeeClone;
          return null;
        },
        [Symbol.iterator]: function* () {
          yield catClone;
          yield payeeClone;
        }
      };
      formClone.append(catClone, payeeClone);
      const wrap = new MockElement('div');
      wrap.append(formClone);
      return wrap;
    }
  };

  dialog.append(titleEl, dialogBody, discardSection, fullpageEl, submitEl, templateExpense);
  body.append(dialog);

  document.createElement = (tag) => new MockElement(tag);
  document.querySelectorAll = (sel) => {
    if (sel === '[data-native-create-dialog]') return [dialog];
    return [];
  };
  document.querySelector = (sel) => {
    if (sel.startsWith('#')) return dialog.id === sel.slice(1) ? dialog : null;
    return null;
  };

  const window = {
    location: { href: 'https://example.test/finance', origin: 'https://example.test' },
    addEventListener: () => {},
    setTimeout: (fn) => fn()
  };

  class Event {
    constructor(type, opts = {}) {
      this.type = type;
      this.bubbles = !!opts.bubbles;
      this.defaultPrevented = false;
    }
    preventDefault() { this.defaultPrevented = true; }
  }

  const context = {
    window,
    document,
    location: window.location,
    URL,
    Event,
    Set,
    Map,
    WeakSet,
    JSON,
    Array,
    setTimeout: window.setTimeout,
    queueMicrotask: (fn) => fn(),
    console
  };

  vm.createContext(context);
  const code = fs.readFileSync(path.join(__dirname, '../../public/assets/js/studio-native-create.js'), 'utf8');
  vm.runInContext(code, context);

  return { document, dialog, titleEl, submitEl, fullpageEl, discardSection };
}

// 1. Fresh open with laboratory preset
{
  const { document, dialog, titleEl, submitEl, fullpageEl, discardSection } = createHarness();
  const trigger = new MockElement('a');
  trigger.href = 'https://example.test/finance/expenses/create?category=laboratory';
  trigger.dataset.nativeCreate = 'finance-create-dialog';

  // Trigger document click handler
  const clickListeners = document.listeners['click'] || [];
  for (const h of clickListeners) {
    h({
      target: { closest: (sel) => sel.includes('a[href]') ? trigger : null },
      defaultPrevented: false,
      button: 0,
      preventDefault: () => {}
    });
  }

  assert.equal(dialog.open, true, 'Dialog must be open');
  const form = dialog.querySelector('form');
  assert(form, 'Cloned form must exist');
  const cat = form.elements.namedItem('expense_category');
  assert.equal(cat.value, 'laboratory', 'Laboratory category preset must be selected');
  assert.equal(titleEl.textContent, 'New Expense', 'Expense title must match regular expense');
  assert.equal(submitEl.textContent, 'Record Expense', 'Expense submit button must match regular expense');

  const fullpageUrl = new URL(fullpageEl.href);
  assert.equal(fullpageUrl.searchParams.get('category'), 'laboratory', 'Fullpage URL must preserve category=laboratory');
  assert.equal(fullpageUrl.searchParams.get('return_to'), 'finance', 'Fullpage URL must preserve canonical return_to=finance');

  // Verify not dirty on fresh open
  const closeBtn = new MockElement('button');
  closeBtn.dataset.nativeClose = '';
  dialog.dispatchEvent({ type: 'cancel', defaultPrevented: false, preventDefault: () => {} });
  assert.equal(discardSection.hidden, true, 'Unmodified preset must not show discard confirmation prompt');

  // Verify dirty warning after field modification
  const payeeInput = form.elements.namedItem('counterparty_name');
  payeeInput.value = 'Dental Lab Alpha';
  dialog.dispatchEvent({ type: 'cancel', defaultPrevented: false, preventDefault: () => {} });
  assert.equal(discardSection.hidden, false, 'Modified form must show discard confirmation prompt');
}

// 2. Category dynamic switching updates chrome correctly without DOM sinks
{
  const { document, dialog, titleEl, submitEl, fullpageEl } = createHarness();
  const trigger = new MockElement('a');
  trigger.href = 'https://example.test/finance/expenses/create?category=laboratory';
  trigger.dataset.nativeCreate = 'finance-create-dialog';

  for (const h of document.listeners['click'] || []) {
    h({
      target: { closest: () => trigger },
      defaultPrevented: false,
      button: 0,
      preventDefault: () => {}
    });
  }

  const form = dialog.querySelector('form');
  const cat = form.elements.namedItem('expense_category');

  // Switch to salaries
  cat.value = 'salaries';
  cat.dispatchEvent({ type: 'change', bubbles: true });

  assert.equal(titleEl.textContent, 'Record staff salary', 'Switching to salaries must update dialog title to salaryTitle');
  assert.equal(submitEl.textContent, 'Record staff salary', 'Switching to salaries must update dialog submit button to salarySubmit');
  let fullpageUrl = new URL(fullpageEl.href);
  assert.equal(fullpageUrl.searchParams.get('category'), 'salaries');
  assert.equal(fullpageUrl.searchParams.get('return_to'), 'finance');

  // Switch to rent
  cat.value = 'rent';
  cat.dispatchEvent({ type: 'change', bubbles: true });

  assert.equal(titleEl.textContent, 'New Expense', 'Switching to rent must restore standard expense title');
  assert.equal(submitEl.textContent, 'Record Expense', 'Switching to rent must restore standard expense submit text');
  fullpageUrl = new URL(fullpageEl.href);
  assert.equal(fullpageUrl.searchParams.get('category'), 'rent');
  assert.equal(fullpageUrl.searchParams.get('return_to'), 'finance');

  // Switch to empty category
  cat.value = '';
  cat.dispatchEvent({ type: 'change', bubbles: true });

  fullpageUrl = new URL(fullpageEl.href);
  assert.equal(fullpageUrl.searchParams.has('category'), false, 'Empty category must be deleted from searchParams');
  assert.equal(fullpageUrl.searchParams.get('return_to'), 'finance');
}

// Validation reopen preserves the exact server value, including an explicitly cleared category.
for (const serverCategory of ['rent', '']) {
  for (const requestedCategory of ['laboratory', 'salaries']) {
    const { document, dialog, titleEl, fullpageEl } = createHarness(serverCategory);
    dialog.dataset.openOnLoad = 'true';
    dialog.dataset.reopenMode = 'expense';
    const trigger = new MockElement('a');
    trigger.href = `https://example.test/finance/expenses/create?category=${requestedCategory}`;
    for (const handler of document.listeners.click || []) {
      handler({ target: { closest: () => trigger }, defaultPrevented: false, button: 0, preventDefault() {} });
    }
    assert.equal(dialog.querySelector('form').elements.namedItem('expense_category').value, serverCategory);
    assert.equal(new URL(fullpageEl.href).searchParams.get('category'), serverCategory || null);
    assert.equal(titleEl.textContent, 'New Expense');
  }
}

// Unknown URL presets never reach either the form selection or full-page destination.
{
  const { document, dialog, fullpageEl } = createHarness();
  const trigger = new MockElement('a');
  trigger.href = 'https://example.test/finance/expenses/create?category=unknown_category';
  for (const handler of document.listeners.click || []) {
    handler({ target: { closest: () => trigger }, defaultPrevented: false, button: 0, preventDefault() {} });
  }
  assert.equal(dialog.querySelector('form').elements.namedItem('expense_category').value, '');
  assert.equal(new URL(fullpageEl.href).searchParams.has('category'), false);
}

console.log('StudioNativeCreatePresetTest: All targeted JS assertions passed.');
