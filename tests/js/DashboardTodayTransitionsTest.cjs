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
      if (selector === 'form[data-appointment-transition]') {
        return this.tagName === 'FORM' && (this.hasAttribute('data-appointment-transition') || 'appointmentTransition' in this.dataset);
      }
      if (selector === 'tr.visit-entry') {
        return this.tagName === 'TR' && this.classList.contains('visit-entry');
      }
      if (selector.startsWith('.')) {
        return this.classList.contains(selector.slice(1));
      }
      return false;
    },

    querySelector(selector) {
      return querySelectorAll(this, selector)[0] || null;
    },

    querySelectorAll(selector) {
      return querySelectorAll(this, selector);
    }
  };

  return el;
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

  function matchesSelector(node, sel) {
    if (node.matches && node.matches(sel)) return true;
    if (sel === '.status-badge') return node.classList.contains('status-badge');
    if (sel === '.status-badge-text') return node.classList.contains('status-badge-text');
    if (sel === '.visit-action') return node.classList.contains('visit-action');
    if (sel === '.visit-action-group') return node.classList.contains('visit-action-group');
    if (sel === '.visit-stale-notice') return node.classList.contains('visit-stale-notice');
    if (sel === 'button[type="submit"]') {
      return node.tagName === 'BUTTON' && node.type === 'submit';
    }
    if (sel === 'button[type="submit"], .transition-btn') {
      return node.tagName === 'BUTTON' && (node.type === 'submit' || node.classList.contains('transition-btn'));
    }
    if (sel === 'button, a') {
      return node.tagName === 'BUTTON' || node.tagName === 'A';
    }
    if (sel.startsWith('input[name="')) {
      const match = sel.match(/^input\[name="([^"]+)"\]$/);
      if (match) return node.tagName === 'INPUT' && node.name === match[1];
    }
    if (sel === 'input') {
      return node.tagName === 'INPUT';
    }
    if (sel === 'meta[name="csrf-token"]') return node.tagName === 'META' && node.name === 'csrf-token';
    if (sel === 'meta[name="csrf-name"]') return node.tagName === 'META' && node.name === 'csrf-name';
    if (sel === 'meta[name="csrf-header"]') return node.tagName === 'META' && node.name === 'csrf-header';
    return false;
  }

  walk(root);
  return matches;
}

// Harness to run script in synthetic sandbox
function setupHarness(fetchHandler) {
  const eventListeners = new Map();

  const configEl = createElement('DIV', {
    dataset: {
      actionArrival: 'Arrival',
      actionReady: 'Ready',
      actionView: 'View',
      statusUpdating: 'Updating...',
      statusStale: 'The appointment status has changed. Please review the updated status.',
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

  const row = createElement('TR', {
    className: 'visit-entry',
    dataset: { visitId: '42', stage: 'scheduled' }
  });

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

  const documentMock = {
    addEventListener(event, fn) {
      if (!eventListeners.has(event)) eventListeners.set(event, []);
      eventListeners.get(event).push(fn);
    },
    getElementById(id) {
      if (id === 'today-transitions-config') return configEl;
      if (id === 'visit-filter') return null;
      return null;
    },
    querySelector(selector) {
      if (selector === 'meta[name="csrf-token"]') return metaCsrfToken;
      if (selector === 'meta[name="csrf-name"]') return metaCsrfName;
      if (selector === 'meta[name="csrf-header"]') return metaCsrfHeader;
      return null;
    },
    querySelectorAll(selector) {
      if (selector === 'input[name="csrf_test_name"]') {
        return [csrfInput];
      }
      return [];
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

  // FormData mock
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

      // Assert pending state during fetch execution
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

    // Wait microtasks for async fetch completion
    await new Promise(resolve => setTimeout(resolve, 20));

    assert.equal(fetchCalled, true, 'Fetch must be invoked');
    assert.equal(fetchUrl, '/appointment/42/transition');
    assert.equal(fetchHeaders['X-Requested-With'], 'XMLHttpRequest');
    assert.equal(fetchHeaders['X-CSRF-TOKEN'], 'token-initial-111');

    // Verify CSRF synchronization
    assert.equal(harness.metaCsrfToken.content, 'token-refreshed-222');
    assert.equal(harness.csrfInput.value, 'token-refreshed-222');
    assert.equal(harness.getRefreshedToken(), 'token-refreshed-222');

    // Verify row stage and badge updated
    assert.equal(harness.row.dataset.stage, 'arrived');
    assert.equal(harness.statusBadge.className, 'status-badge status-arrived');
    assert.equal(harness.statusBadgeText.textContent, 'Arrived');

    // Verify next action is Ready form
    const nextForm = harness.actionCell.querySelector('form[data-appointment-transition]');
    assert.ok(nextForm, 'Arrived stage must render next transition form');
    const nextStatus = nextForm.querySelector('input[name="status"]');
    assert.equal(nextStatus.value, 'ready');
    const nextExpected = nextForm.querySelector('input[name="expected_status"]');
    assert.equal(nextExpected.value, 'arrived');
  }

  // Test 2: HTTP 409 Conflict / Stale Status Handling
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

    // Button should be restored from pending state
    assert.equal(harness.button.disabled, false);
    assert.equal(harness.button.getAttribute('aria-busy'), null);

    // CSRF should be updated
    assert.equal(harness.metaCsrfToken.content, 'token-after-409');

    // Stale notice should be displayed in action cell
    const notice = harness.actionCell.querySelector('.visit-stale-notice');
    assert.ok(notice, 'Stale notice element must be created on 409 conflict');
    assert.equal(notice.textContent, 'The appointment status has changed.');

    // Row should be safely synchronized to server's actual current status ('ready')
    assert.equal(harness.row.dataset.stage, 'ready');
    assert.equal(harness.statusBadge.className, 'status-badge status-ready');
    assert.equal(harness.statusBadgeText.textContent, 'Ready');
  }

  // Test 3: HTTP 500 Failure Handling (No blind retry)
  {
    let fetchCount = 0;
    const harness = setupHarness(async () => {
      fetchCount++;
      return {
        ok: false,
        status: 500,
        json: async () => ({
          success: false,
          message: 'Internal server error',
          csrf_token: 'token-after-500'
        })
      };
    });

    harness.submitForm();
    await new Promise(resolve => setTimeout(resolve, 20));

    assert.equal(fetchCount, 1, 'Script must never blind-retry on failure');
    assert.equal(harness.button.disabled, false, 'Button must be re-enabled after failure');
    assert.equal(harness.button.getAttribute('aria-busy'), null);
    assert.equal(harness.button.textContent, 'Arrival', 'Original button text must be restored');
  }

  console.log('All DashboardTodayTransitionsTest unit tests passed successfully.');
}

runTests().catch(err => {
  console.error('Test failure:', err);
  process.exit(1);
});
