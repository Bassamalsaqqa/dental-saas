'use strict';

const assert = require('node:assert/strict');
const fs = require('node:fs');
const path = require('node:path');
const vm = require('node:vm');

const jsPath = path.join(__dirname, '../../public/assets/js/studio-today-transitions.js');
const source = fs.readFileSync(jsPath, 'utf8');

// Simple DOM element mock for headless testing
function createElement(tagName, attributes = {}) {
  const children = [];
  const classList = new Set();
  const dataset = {};
  const attrs = {};

  if (attributes.className) {
    attributes.className.split(/\s+/).filter(Boolean).forEach(c => classList.add(c));
  }
  if (attributes.dataset) {
    Object.assign(dataset, attributes.dataset);
  }

  const el = {
    tagName: tagName.toUpperCase(),
    nodeType: 1,
    children,
    dataset,
    disabled: false,
    type: attributes.type || '',
    name: attributes.name || '',
    value: attributes.value || '',
    action: attributes.action || '',
    method: attributes.method || 'POST',
    textContent: attributes.textContent || '',
    hidden: false,
    parentNode: null,
    _focused: false,

    get parentElement() {
      return this.parentNode;
    },

    get className() {
      return Array.from(classList).join(' ');
    },
    set className(val) {
      classList.clear();
      if (val) val.split(/\s+/).filter(Boolean).forEach(c => classList.add(c));
    },

    classList: {
      add: (...tokens) => tokens.forEach(t => classList.add(t)),
      remove: (...tokens) => tokens.forEach(t => classList.delete(t)),
      contains: token => classList.has(token),
    },

    getAttribute(name) {
      if (name === 'aria-busy') return attrs['aria-busy'] || null;
      if (name === 'aria-label') return attrs['aria-label'] || null;
      if (name === 'aria-disabled') return attrs['aria-disabled'] || null;
      if (name === 'content') return attrs['content'] || null;
      if (name === 'hidden') return this.hidden ? '' : null;
      return attrs[name] || null;
    },

    setAttribute(name, val) {
      attrs[name] = String(val);
      if (name === 'aria-busy') this['aria-busy'] = String(val);
      if (name === 'content') this.content = String(val);
      if (name === 'hidden') this.hidden = true;
    },

    removeAttribute(name) {
      delete attrs[name];
      if (name === 'aria-busy') delete this['aria-busy'];
      if (name === 'hidden') this.hidden = false;
    },

    hasAttribute(name) {
      return name in attrs;
    },

    appendChild(child) {
      child.parentNode = el;
      children.push(child);
      return child;
    },

    insertBefore(newNode, referenceNode) {
      const idx = children.indexOf(referenceNode);
      newNode.parentNode = el;
      if (idx !== -1) {
        children.splice(idx, 0, newNode);
      } else {
        children.push(newNode);
      }
      return newNode;
    },

    remove() {
      if (this.parentNode) {
        const idx = this.parentNode.children.indexOf(this);
        if (idx !== -1) {
          this.parentNode.children.splice(idx, 1);
        }
        this.parentNode = null;
      }
    },

    replaceChildren(...newChildren) {
      children.length = 0;
      newChildren.forEach(c => {
        c.parentNode = el;
        children.push(c);
      });
    },

    focus() {
      this._focused = true;
    },

    closest(selector) {
      let current = this;
      while (current) {
        if (current.matches && current.matches(selector)) return current;
        current = current.parentNode;
      }
      return null;
    },

    matches(selector) {
      if (!selector) return false;
      if (selector === 'form[data-appointment-transition]') {
        return this.tagName === 'FORM' && ('appointmentTransition' in dataset || 'data-appointment-transition' in attrs);
      }
      if (selector === 'tr.visit-entry') {
        return this.tagName === 'TR' && classList.has('visit-entry');
      }
      if (selector.startsWith('.')) {
        return classList.has(selector.slice(1));
      }
      if (selector.startsWith('#')) {
        return attrs.id === selector.slice(1);
      }
      return this.tagName === selector.toUpperCase();
    },

    querySelector(selector) {
      return querySelector(this, selector);
    },

    querySelectorAll(selector) {
      return querySelectorAll(this, selector);
    }
  };

  return el;
}

function querySelector(root, selector) {
  const matches = querySelectorAll(root, selector);
  return matches.length > 0 ? matches[0] : null;
}

function querySelectorAll(root, selector) {
  const matches = [];

  function walk(node) {
    if (!node || !node.children) return;
    for (const child of node.children) {
      if (matchesSelector(child, selector)) {
        matches.push(child);
      }
      walk(child);
    }
  }

  function matchesSelector(el, sel) {
    if (sel.includes('tr.visit-entry:not([hidden])')) {
      const tr = el.closest && el.closest('.visit-entry');
      return tr && !tr.hidden && el.closest && el.closest('.visit-action') && (el.tagName === 'BUTTON' || el.tagName === 'A');
    }
    if (sel === 'form[data-appointment-transition]') {
      return el.tagName === 'FORM' && ('appointmentTransition' in el.dataset || el.hasAttribute('data-appointment-transition'));
    }
    if (sel === 'button[type="submit"]') {
      return el.tagName === 'BUTTON' && el.type === 'submit';
    }
    if (sel === 'input[name="csrf_test_name"]') {
      return el.tagName === 'INPUT' && el.name === 'csrf_test_name';
    }
    if (sel === 'input[name="status"]') {
      return el.tagName === 'INPUT' && el.name === 'status';
    }
    if (sel === 'input[name="expected_status"]') {
      return el.tagName === 'INPUT' && el.name === 'expected_status';
    }
    if (sel === 'button, a') {
      return el.tagName === 'BUTTON' || el.tagName === 'A';
    }
    if (sel === 'input') {
      return el.tagName === 'INPUT';
    }
    if (sel.startsWith('.')) {
      return el.classList && el.classList.contains(sel.slice(1));
    }
    if (sel.includes('.')) {
      const [tag, cls] = sel.split('.');
      return el.tagName === tag.toUpperCase() && el.classList && el.classList.contains(cls);
    }
    return el.tagName === sel.toUpperCase();
  }

  walk(root);
  return matches;
}

// Harness to run script in synthetic sandbox
function setupHarness(fetchHandler, options = {}) {
  const eventListeners = new Map();

  const configEl = createElement('DIV', {
    dataset: {
      actionArrival: 'Arrival',
      actionReady: 'Ready',
      actionView: 'View',
      statusUpdating: 'Updating...',
      statusStale: 'The appointment status has changed. Please review the updated status.',
      statusError: 'Unable to update status. Please review and refresh before trying again.',
      statusUncertain: 'Cannot confirm change. Please review or refresh before another change.',
      baseUrl: '/appointment/',
      canEdit: '1',
      labelScheduled: 'Scheduled',
      labelConfirmed: 'Confirmed',
      labelArrived: 'Arrived',
      labelReady: 'Ready',
      labelIn_chair: 'In chair',
      labelCheckout: 'Checkout',
      labelCompleted: 'Completed',
    }
  });

  const metaCsrfToken = createElement('META');
  metaCsrfToken.name = 'csrf-token';
  metaCsrfToken.setAttribute('content', 'token-initial-111');

  const metaCsrfName = createElement('META');
  metaCsrfName.name = 'csrf-name';
  metaCsrfName.setAttribute('content', 'csrf_test_name');

  const metaCsrfHeader = createElement('META');
  metaCsrfHeader.name = 'csrf-header';
  metaCsrfHeader.setAttribute('content', 'X-CSRF-TOKEN');

  const container = createElement('DIV', { className: 'panel-today-container' });
  const tableScroll = createElement('DIV', { className: 'visit-table-scroll' });
  const table = createElement('TABLE', { className: 'visit-table' });
  const tbody = createElement('TBODY');
  table.appendChild(tbody);
  tableScroll.appendChild(table);
  container.appendChild(tableScroll);

  const row = createElement('TR', {
    className: 'visit-entry',
    dataset: { visitId: '42', stage: 'scheduled' }
  });
  tbody.appendChild(row);

  let secondRow = null;
  let secondBtn = null;
  if (options.addSecondRow) {
    secondRow = createElement('TR', {
      className: 'visit-entry',
      dataset: { visitId: '43', stage: 'scheduled' }
    });
    const secondActionCell = createElement('TD', { className: 'visit-action' });
    secondBtn = createElement('BUTTON', { type: 'submit', textContent: 'Arrival' });
    secondActionCell.appendChild(secondBtn);
    secondRow.appendChild(secondActionCell);
    tbody.appendChild(secondRow);
  }

  const statusCell = createElement('TD', { className: 'visit-status' });
  const statusBadge = createElement('SPAN', { className: 'status-badge status-scheduled' });
  const statusBadgeText = createElement('SPAN', { className: 'status-badge-text', textContent: 'Scheduled' });
  statusBadge.appendChild(statusBadgeText);
  statusCell.appendChild(statusBadge);
  row.appendChild(statusCell);

  const actionCell = createElement('TD', { className: 'visit-action' });
  const actionGroup = createElement('DIV', { className: 'visit-action-group' });
  const form = createElement('FORM', {
    method: 'POST',
    action: '/appointment/42/transition',
    dataset: { appointmentTransition: '1' }
  });

  const csrfInput = createElement('INPUT', { type: 'hidden', name: 'csrf_test_name', value: 'token-initial-111' });
  const statusInput = createElement('INPUT', { type: 'hidden', name: 'status', value: 'arrived' });
  const expectedInput = createElement('INPUT', { type: 'hidden', name: 'expected_status', value: 'scheduled' });
  const button = createElement('BUTTON', {
    type: 'submit',
    className: 'button primary transition-btn',
    textContent: 'Arrival'
  });

  form.appendChild(csrfInput);
  form.appendChild(statusInput);
  form.appendChild(expectedInput);
  form.appendChild(button);
  actionGroup.appendChild(form);
  actionCell.appendChild(actionGroup);
  row.appendChild(actionCell);

  let visitFilter = null;
  if (options.filterValue !== undefined) {
    visitFilter = createElement('SELECT', { value: options.filterValue });
    visitFilter.setAttribute('id', 'visit-filter');
  }

  const documentMock = {
    addEventListener(event, fn) {
      if (!eventListeners.has(event)) eventListeners.set(event, []);
      eventListeners.get(event).push(fn);
    },
    getElementById(id) {
      if (id === 'today-transitions-config') return configEl;
      if (id === 'visit-filter') return visitFilter;
      return null;
    },
    querySelector(selector) {
      if (selector === 'meta[name="csrf-token"]') return metaCsrfToken;
      if (selector === 'meta[name="csrf-name"]') return metaCsrfName;
      if (selector === 'meta[name="csrf-header"]') return metaCsrfHeader;
      if (selector.startsWith('#')) return this.getElementById(selector.slice(1));
      return querySelector(container, selector);
    },
    querySelectorAll(selector) {
      if (selector === 'input[name="csrf_test_name"]') {
        return [csrfInput];
      }
      return querySelectorAll(container, selector);
    },
    createElement(tagName) {
      return createElement(tagName);
    }
  };

  let refreshedToken = null;
  const windowMock = {
    refreshCsrfToken(t) {
      refreshedToken = t;
    }
  };

  class MockFormData {
    constructor(formEl) {
      this.data = new Map();
      if (formEl) {
        querySelectorAll(formEl, 'input').forEach(inp => {
          if (inp.name) this.data.set(inp.name, inp.value);
        });
      }
    }
    set(key, val) {
      this.data.set(key, val);
    }
    get(key) {
      return this.data.get(key);
    }
  }

  const context = {
    document: documentMock,
    window: windowMock,
    FormData: MockFormData,
    fetch: fetchHandler,
    setTimeout: (fn, ms) => setTimeout(fn, ms),
    console: console,
  };

  vm.runInNewContext(source, context);

  return {
    row,
    secondRow,
    secondBtn,
    container,
    visitFilter,
    form,
    button,
    actionCell,
    statusBadge,
    statusBadgeText,
    metaCsrfToken,
    csrfInput,
    getRefreshedToken: () => refreshedToken,
    submitForm: () => {
      let defaultPrevented = false;
      const event = {
        target: button,
        preventDefault: () => { defaultPrevented = true; }
      };
      const submitHandlers = eventListeners.get('submit') || [];
      submitHandlers.forEach(h => h(event));
      return defaultPrevented;
    }
  };
}

async function runTests() {
  // Test 1: Successful Transition (scheduled -> arrived)
  {
    let fetchCalled = false;
    let fetchUrl = '';
    let fetchHeaders = {};

    const harness = setupHarness(async (url, opts) => {
      fetchCalled = true;
      fetchUrl = url;
      fetchHeaders = opts.headers;

      assert.equal(harness.button.disabled, true, 'Button must be disabled during pending request');
      assert.equal(harness.button.getAttribute('aria-busy'), 'true', 'Button aria-busy must be true during pending request');
      assert.equal(harness.button.textContent, 'Updating...', 'Button text must show Updating... indicator');

      return {
        ok: true,
        status: 200,
        json: async () => ({
          success: true,
          status: 'arrived',
          current_status: 'arrived',
          csrf_token: 'token-refreshed-222'
        })
      };
    });

    const prevented = harness.submitForm();
    assert.equal(prevented, true, 'Form submit must be intercepted with preventDefault()');

    await new Promise(resolve => setTimeout(resolve, 20));

    assert.equal(fetchCalled, true, 'Fetch must be invoked');
    assert.equal(fetchUrl, '/appointment/42/transition');
    assert.equal(fetchHeaders['X-Requested-With'], 'XMLHttpRequest');
    assert.equal(fetchHeaders['X-CSRF-TOKEN'], 'token-initial-111');

    assert.equal(harness.metaCsrfToken.content, 'token-refreshed-222');
    assert.equal(harness.csrfInput.value, 'token-refreshed-222');
    assert.equal(harness.getRefreshedToken(), 'token-refreshed-222');

    assert.equal(harness.row.dataset.stage, 'arrived');
    assert.equal(harness.statusBadge.className, 'status-badge status-arrived');
    assert.equal(harness.statusBadgeText.textContent, 'Arrived');

    const nextForm = harness.actionCell.querySelector('form[data-appointment-transition]');
    assert.ok(nextForm, 'Arrived stage must render next transition form');
    const nextStatus = nextForm.querySelector('input[name="status"]');
    assert.equal(nextStatus.value, 'ready');
    const nextExpected = nextForm.querySelector('input[name="expected_status"]');
    assert.equal(nextExpected.value, 'arrived');
  }

  // Test 2: HTTP 409 Conflict / Stale Status Handling (uses localized message, synchronizes actual stage)
  {
    const harness = setupHarness(async () => {
      return {
        ok: false,
        status: 409,
        json: async () => ({
          success: false,
          error: 'CONFLICT',
          message: 'The appointment status has changed.',
          current_status: 'ready',
          allowed_statuses: ['in_chair', 'cancelled'],
          csrf_token: 'token-after-409'
        })
      };
    });

    harness.submitForm();
    await new Promise(resolve => setTimeout(resolve, 20));

    assert.equal(harness.button.disabled, false);
    assert.equal(harness.button.getAttribute('aria-busy'), null);

    assert.equal(harness.metaCsrfToken.content, 'token-after-409');

    // Stale notice must use localized config text, never raw server English
    const notice = harness.actionCell.querySelector('.visit-stale-notice');
    assert.ok(notice, 'Stale notice element must be created on 409 conflict');
    assert.equal(notice.textContent, 'The appointment status has changed. Please review the updated status.');

    // Row synchronized to server actual current status
    assert.equal(harness.row.dataset.stage, 'ready');
    assert.equal(harness.statusBadge.className, 'status-badge status-ready');
    assert.equal(harness.statusBadgeText.textContent, 'Ready');
  }

  // Test 3: HTTP 500 Failure Handling (No blind retry, localized statusError)
  {
    let fetchCount = 0;
    const harness = setupHarness(async () => {
      fetchCount++;
      return {
        ok: false,
        status: 500,
        json: async () => ({
          success: false,
          message: 'Database query syntax error near SELECT',
          csrf_token: 'token-after-500'
        })
      };
    });

    harness.submitForm();
    await new Promise(resolve => setTimeout(resolve, 20));

    assert.equal(fetchCount, 1, 'Script must never blind-retry on failure');
    assert.equal(harness.button.disabled, false, 'Button must be re-enabled after 500');
    assert.equal(harness.button.getAttribute('aria-busy'), null);
    assert.equal(harness.button.textContent, 'Arrival', 'Original button text must be restored');

    // Must NOT expose raw technical error
    const notice = harness.actionCell.querySelector('.visit-stale-notice');
    assert.ok(notice);
    assert.equal(notice.textContent, 'Unable to update status. Please review and refresh before trying again.');
  }

  // Test 4: Focus management when row disappears due to active filter on success
  {
    const harness = setupHarness(async () => {
      return {
        ok: true,
        status: 200,
        json: async () => ({
          success: true,
          status: 'arrived',
          csrf_token: 'tok-filter'
        })
      };
    }, { filterValue: 'scheduled', addSecondRow: true });

    assert.equal(harness.visitFilter.value, 'scheduled');
    harness.submitForm();
    await new Promise(resolve => setTimeout(resolve, 20));

    // Row stage changed from scheduled to arrived, filter is 'scheduled', so row must be hidden
    assert.equal(harness.row.hidden, true, 'Row must be hidden when stage mismatches active filter');

    // Filter value must be preserved intact
    assert.equal(harness.visitFilter.value, 'scheduled', 'Active filter value must be preserved');

    // Focus must NOT be on hidden row's action button; it must move to next visible row
    const hiddenBtn = harness.actionCell.querySelector('button, a');
    assert.equal(hiddenBtn._focused, false, 'Hidden button must not receive focus');
    assert.equal(harness.secondBtn._focused, true, 'Focus must move to next visible row action');
  }

  // Test 5: Focus management when row disappears on 409 conflict
  {
    const harness = setupHarness(async () => {
      return {
        ok: false,
        status: 409,
        json: async () => ({
          success: false,
          error: 'CONFLICT',
          message: 'Conflict',
          current_status: 'ready',
          csrf_token: 'tok-409-filter'
        })
      };
    }, { filterValue: 'scheduled', addSecondRow: false });

    harness.submitForm();
    await new Promise(resolve => setTimeout(resolve, 20));

    // Stale row stage updated to 'ready', filter is 'scheduled', so row is hidden
    assert.equal(harness.row.hidden, true, 'Row must be hidden on 409 when updated status mismatches filter');
    assert.equal(harness.visitFilter.value, 'scheduled', 'Filter value must be preserved');

    // Global notice in visible table container must be displayed and focused
    const globalNotice = harness.container.querySelector('.visit-stale-notice--global');
    assert.ok(globalNotice, 'Global visible notice must be appended when row is hidden');
    assert.equal(globalNotice._focused, true, 'Visible global notice must receive focus');
    assert.equal(globalNotice.textContent, 'The appointment status has changed. Please review the updated status.');
  }

  // Test 6: Uncertain outcome on network failure / timeout (no silent retry, prevent accidental repeat, localized message)
  {
    const harness = setupHarness(async () => {
      throw new Error('Failed to fetch / network drop');
    });

    harness.submitForm();
    await new Promise(resolve => setTimeout(resolve, 20));

    // Busy cleared consistently
    assert.equal(harness.button.getAttribute('aria-busy'), null, 'aria-busy must be removed on network failure');
    // Accidental repeat submission prevented by disabling button until reconciliation
    assert.equal(harness.button.disabled, true, 'Button must remain disabled on uncertain outcome');
    assert.equal(harness.button.getAttribute('aria-disabled'), 'true');

    // Must NOT expose raw technical err.message
    const notice = harness.actionCell.querySelector('.visit-uncertain-notice');
    assert.ok(notice, 'Uncertain outcome notice must be created');
    assert.equal(notice.textContent, 'Cannot confirm change. Please review or refresh before another change.');

    // Clear review action link provided
    const reviewLink = harness.actionCell.querySelector('a.button');
    assert.ok(reviewLink, 'Review link must be provided for reconciliation');
    assert.equal(reviewLink.textContent, 'View');
    assert.equal(reviewLink._focused, true, 'Review action must receive focus');
  }

  // Test 7: Uncertain outcome on broken / non-JSON response body
  {
    const harness = setupHarness(async () => {
      return {
        ok: false,
        status: 502,
        json: async () => { throw new SyntaxError('Unexpected token < in JSON at position 0'); }
      };
    });

    harness.submitForm();
    await new Promise(resolve => setTimeout(resolve, 20));

    assert.equal(harness.button.getAttribute('aria-busy'), null);
    assert.equal(harness.button.disabled, true);
    const notice = harness.actionCell.querySelector('.visit-uncertain-notice');
    assert.ok(notice);
    assert.equal(notice.textContent, 'Cannot confirm change. Please review or refresh before another change.');
  }

  console.log('All DashboardTodayTransitionsTest unit tests passed successfully.');
}

runTests().catch(err => {
  console.error('Test failure:', err);
  process.exit(1);
});
