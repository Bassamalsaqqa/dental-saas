(function () {
  'use strict';

  function initTodayTransitions() {
    const configEl = document.getElementById('today-transitions-config');
    if (!configEl) return;

    const config = {
      actionArrival: configEl.dataset.actionArrival || 'Arrival',
      actionReady: configEl.dataset.actionReady || 'Ready',
      actionView: configEl.dataset.actionView || 'View',
      statusUpdating: configEl.dataset.statusUpdating || 'Updating...',
      statusStale: configEl.dataset.statusStale || 'Status changed. Please review.',
      baseUrl: configEl.dataset.baseUrl || '/appointment/',
      canEdit: configEl.dataset.canEdit === '1',
      labels: {
        scheduled: configEl.dataset.labelScheduled || 'Scheduled',
        confirmed: configEl.dataset.labelConfirmed || 'Confirmed',
        arrived: configEl.dataset.labelArrived || 'Arrived',
        ready: configEl.dataset.labelReady || 'Ready',
        in_chair: configEl.dataset.labelIn_chair || 'In chair',
        checkout: configEl.dataset.labelCheckout || 'Checkout',
        completed: configEl.dataset.labelCompleted || 'Completed',
        cancelled: configEl.dataset.labelCancelled || 'Cancelled',
        no_show: configEl.dataset.labelNo_show || 'No show',
      }
    };

    function updateCsrfTokens(token) {
      if (!token) return;
      if (typeof window.refreshCsrfToken === 'function') {
        window.refreshCsrfToken(token);
      }
      const metaToken = document.querySelector('meta[name="csrf-token"]');
      if (metaToken) {
        metaToken.setAttribute('content', token);
      }
      const metaName = document.querySelector('meta[name="csrf-name"]')?.content || 'csrf_test_name';
      document.querySelectorAll('input[name="' + metaName + '"]').forEach(function (input) {
        input.value = token;
      });
    }

    function createActionElement(stage, visitId) {
      const group = document.createElement('div');
      group.className = 'visit-action-group';

      if (!config.canEdit) {
        const viewLink = document.createElement('a');
        viewLink.className = 'button secondary';
        viewLink.href = config.baseUrl + visitId + '?return_to=dashboard';
        viewLink.textContent = config.actionView;
        group.appendChild(viewLink);
        return group;
      }

      if (stage === 'scheduled' || stage === 'confirmed') {
        const form = document.createElement('form');
        form.method = 'post';
        form.action = config.baseUrl + visitId + '/transition';
        form.className = 'inline-transition-form';
        form.setAttribute('data-appointment-transition', '');

        const metaName = document.querySelector('meta[name="csrf-name"]')?.content || 'csrf_test_name';
        const metaToken = document.querySelector('meta[name="csrf-token"]')?.content || '';
        const csrfInput = document.createElement('input');
        csrfInput.type = 'hidden';
        csrfInput.name = metaName;
        csrfInput.value = metaToken;
        form.appendChild(csrfInput);

        const statusInput = document.createElement('input');
        statusInput.type = 'hidden';
        statusInput.name = 'status';
        statusInput.value = 'arrived';
        form.appendChild(statusInput);

        const expectedInput = document.createElement('input');
        expectedInput.type = 'hidden';
        expectedInput.name = 'expected_status';
        expectedInput.value = stage;
        form.appendChild(expectedInput);

        const returnInput = document.createElement('input');
        returnInput.type = 'hidden';
        returnInput.name = 'return_to';
        returnInput.value = 'dashboard';
        form.appendChild(returnInput);

        const btn = document.createElement('button');
        btn.type = 'submit';
        btn.className = 'button primary transition-btn';
        btn.textContent = config.actionArrival;
        form.appendChild(btn);

        group.appendChild(form);
      } else if (stage === 'arrived') {
        const form = document.createElement('form');
        form.method = 'post';
        form.action = config.baseUrl + visitId + '/transition';
        form.className = 'inline-transition-form';
        form.setAttribute('data-appointment-transition', '');

        const metaName = document.querySelector('meta[name="csrf-name"]')?.content || 'csrf_test_name';
        const metaToken = document.querySelector('meta[name="csrf-token"]')?.content || '';
        const csrfInput = document.createElement('input');
        csrfInput.type = 'hidden';
        csrfInput.name = metaName;
        csrfInput.value = metaToken;
        form.appendChild(csrfInput);

        const statusInput = document.createElement('input');
        statusInput.type = 'hidden';
        statusInput.name = 'status';
        statusInput.value = 'ready';
        form.appendChild(statusInput);

        const expectedInput = document.createElement('input');
        expectedInput.type = 'hidden';
        expectedInput.name = 'expected_status';
        expectedInput.value = 'arrived';
        form.appendChild(expectedInput);

        const returnInput = document.createElement('input');
        returnInput.type = 'hidden';
        returnInput.name = 'return_to';
        returnInput.value = 'dashboard';
        form.appendChild(returnInput);

        const btn = document.createElement('button');
        btn.type = 'submit';
        btn.className = 'button secondary transition-btn';
        btn.textContent = config.actionReady;
        form.appendChild(btn);

        group.appendChild(form);
      } else {
        const viewLink = document.createElement('a');
        viewLink.className = 'button secondary';
        viewLink.href = config.baseUrl + visitId + '?return_to=dashboard';
        viewLink.textContent = config.actionView;
        group.appendChild(viewLink);
      }

      return group;
    }

    function updateRowBadge(row, newStage) {
      const badge = row.querySelector('.status-badge');
      if (badge) {
        badge.className = 'status-badge status-' + newStage;
        const badgeText = badge.querySelector('.status-badge-text');
        if (badgeText) {
          badgeText.textContent = config.labels[newStage] || newStage;
        } else {
          const span = document.createElement('span');
          span.className = 'status-badge-text';
          span.textContent = config.labels[newStage] || newStage;
          badge.appendChild(span);
        }
      }
    }

    function checkActiveFilter(row, newStage) {
      const filter = document.getElementById('visit-filter');
      if (filter && filter.value !== 'all') {
        if (filter.value !== newStage) {
          row.hidden = true;
        } else {
          row.hidden = false;
        }
      }
    }

    document.addEventListener('submit', function (event) {
      const form = event.target.closest('form[data-appointment-transition]');
      if (!form) return;

      const row = form.closest('tr.visit-entry');
      if (!row) return;

      const button = form.querySelector('button[type="submit"]');
      if (!button || button.disabled || button.getAttribute('aria-busy') === 'true') {
        event.preventDefault();
        return;
      }

      event.preventDefault();

      // Clear any prior stale notice on row
      row.querySelectorAll('.visit-stale-notice').forEach(function (el) { el.remove(); });

      // Set pending state
      button.disabled = true;
      button.setAttribute('aria-busy', 'true');
      button.dataset.originalText = button.textContent;
      button.textContent = config.statusUpdating;

      const formData = new FormData(form);
      const metaName = document.querySelector('meta[name="csrf-name"]')?.content || 'csrf_test_name';
      const metaToken = document.querySelector('meta[name="csrf-token"]')?.content || form.querySelector('input[name="' + metaName + '"]')?.value || '';
      const csrfHeader = document.querySelector('meta[name="csrf-header"]')?.content || 'X-CSRF-TOKEN';
      if (metaName && metaToken) {
        formData.set(metaName, metaToken);
      }

      fetch(form.action, {
        method: 'POST',
        body: formData,
        headers: {
          'X-Requested-With': 'XMLHttpRequest',
          [csrfHeader]: metaToken
        }
      })
        .then(async function (response) {
          let data = null;
          try {
            data = await response.json();
          } catch (_) {}

          if (data && data.csrf_token) {
            updateCsrfTokens(data.csrf_token);
          }

          if (response.ok && data && data.success) {
            // SERVER-CONFIRMED STAGE RENDERING ONLY
            const confirmedStatus = data.status;
            row.dataset.stage = confirmedStatus;
            updateRowBadge(row, confirmedStatus);

            const actionCell = row.querySelector('.visit-action');
            if (actionCell) {
              const visitId = row.dataset.visitId || form.action.match(/\/appointment\/(\d+)\//)?.[1];
              actionCell.replaceChildren(createActionElement(confirmedStatus, visitId));
              const newActionBtn = actionCell.querySelector('button, a');
              if (newActionBtn) {
                newActionBtn.focus();
              }
            }
            checkActiveFilter(row, confirmedStatus);
          } else if (response.status === 409 && data) {
            // 409 Conflict / Stale: show plain localized stale notice and refresh actual row safely
            button.disabled = false;
            button.removeAttribute('aria-busy');
            if (button.dataset.originalText) {
              button.textContent = button.dataset.originalText;
            }

            const currentStatus = data.current_status;
            if (currentStatus) {
              row.dataset.stage = currentStatus;
              updateRowBadge(row, currentStatus);
              const actionCell = row.querySelector('.visit-action');
              if (actionCell) {
                const visitId = row.dataset.visitId || form.action.match(/\/appointment\/(\d+)\//)?.[1];
                actionCell.replaceChildren(createActionElement(currentStatus, visitId));
              }
              checkActiveFilter(row, currentStatus);
            }

            const staleNotice = document.createElement('p');
            staleNotice.className = 'visit-stale-notice';
            staleNotice.setAttribute('role', 'alert');
            staleNotice.textContent = data.message || config.statusStale;

            const actionCell = row.querySelector('.visit-action');
            if (actionCell) {
              actionCell.appendChild(staleNotice);
              const activeInteractive = actionCell.querySelector('button, a');
              if (activeInteractive) {
                activeInteractive.focus();
              } else {
                staleNotice.setAttribute('tabindex', '-1');
                staleNotice.focus();
              }
            }
          } else {
            // Error / Network / Expired CSRF: never blindly retry
            button.disabled = false;
            button.removeAttribute('aria-busy');
            if (button.dataset.originalText) {
              button.textContent = button.dataset.originalText;
            }

            const errorNotice = document.createElement('p');
            errorNotice.className = 'visit-stale-notice';
            errorNotice.setAttribute('role', 'alert');
            errorNotice.textContent = (data && data.message) ? data.message : 'Error processing request.';

            const actionCell = row.querySelector('.visit-action');
            if (actionCell) {
              actionCell.appendChild(errorNotice);
            }
          }
        })
        .catch(function (err) {
          // Network failure: never blindly retry or claim failure proves no write
          button.disabled = false;
          button.removeAttribute('aria-busy');
          if (button.dataset.originalText) {
            button.textContent = button.dataset.originalText;
          }

          const networkNotice = document.createElement('p');
          networkNotice.className = 'visit-stale-notice';
          networkNotice.setAttribute('role', 'alert');
          networkNotice.textContent = err.message || 'Network error.';

          const actionCell = row.querySelector('.visit-action');
          if (actionCell) {
            actionCell.appendChild(networkNotice);
          }
        });
    });
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initTodayTransitions);
  } else {
    initTodayTransitions();
  }

  if (typeof module !== 'undefined' && module.exports) {
    module.exports = { initTodayTransitions };
  }
})();
