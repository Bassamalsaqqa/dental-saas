(function () {
  'use strict';

  function initTodayTransitions() {
    const configEl = document.getElementById('today-transitions-config');
    if (!configEl) return;

    let rawBaseUrl = configEl.dataset.baseUrl || '/appointment/';
    if (!rawBaseUrl.endsWith('/')) {
      rawBaseUrl += '/';
    }

    const config = {
      actionArrival: configEl.dataset.actionArrival || 'Arrival',
      actionReady: configEl.dataset.actionReady || 'Ready',
      actionView: configEl.dataset.actionView || 'View',
      statusUpdating: configEl.dataset.statusUpdating || 'Updating...',
      statusStale: configEl.dataset.statusStale || 'The appointment status has changed. Please review the updated status.',
      statusError: configEl.dataset.statusError || 'Unable to update status. Please review and refresh before trying again.',
      statusUncertain: configEl.dataset.statusUncertain || 'Cannot confirm change. Please review or refresh before another change.',
      baseUrl: rawBaseUrl,
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

    function buildTransitionForm(visitId, targetStatus, expectedStatus, buttonClass, buttonText) {
      const form = document.createElement('form');
      form.method = 'post';
      form.action = config.baseUrl + visitId + '/transition';
      form.className = 'inline-transition-form';
      form.setAttribute('data-appointment-transition', '');

      const metaName = document.querySelector('meta[name="csrf-name"]')?.content || 'csrf_test_name';
      const metaToken = document.querySelector('meta[name="csrf-token"]')?.content || '';

      const fields = [
        { name: metaName, value: metaToken },
        { name: 'status', value: targetStatus },
        { name: 'expected_status', value: expectedStatus },
        { name: 'return_to', value: 'dashboard' }
      ];

      fields.forEach(function (field) {
        const input = document.createElement('input');
        input.type = 'hidden';
        input.name = field.name;
        input.value = field.value;
        form.appendChild(input);
      });

      const btn = document.createElement('button');
      btn.type = 'submit';
      btn.className = buttonClass;
      btn.textContent = buttonText;
      form.appendChild(btn);

      return form;
    }

    function buildViewLink(visitId) {
      const viewLink = document.createElement('a');
      viewLink.className = 'button secondary';
      viewLink.href = config.baseUrl + visitId + '?return_to=dashboard';
      viewLink.textContent = config.actionView;
      return viewLink;
    }

    function createActionElement(stage, visitId) {
      const group = document.createElement('div');
      group.className = 'visit-action-group';

      if (config.canEdit) {
        if (stage === 'scheduled' || stage === 'confirmed') {
          group.appendChild(buildTransitionForm(visitId, 'arrived', stage, 'button primary transition-btn', config.actionArrival));
          return group;
        }
        if (stage === 'arrived') {
          group.appendChild(buildTransitionForm(visitId, 'ready', 'arrived', 'button secondary transition-btn', config.actionReady));
          return group;
        }
      }

      group.appendChild(buildViewLink(visitId));
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
        row.hidden = (filter.value !== newStage);
      }
      return row.hidden;
    }

    function moveFocusWhenRowDisappears(row) {
      const tableBody = row.parentElement;
      const nextVisible = tableBody ? tableBody.querySelector('tr.visit-entry:not([hidden]) .visit-action button, tr.visit-entry:not([hidden]) .visit-action a') : null;
      if (nextVisible) {
        nextVisible.focus();
        return;
      }
      const filter = document.getElementById('visit-filter');
      if (filter) {
        filter.focus();
      }
    }

    function handleUncertainOutcome(row, form, button) {
      button.removeAttribute('aria-busy');
      if (button.dataset.originalText) {
        button.textContent = button.dataset.originalText;
      }
      button.disabled = true;
      button.setAttribute('aria-disabled', 'true');

      row.querySelectorAll('.visit-stale-notice').forEach(function (el) { el.remove(); });

      const notice = document.createElement('p');
      notice.className = 'visit-stale-notice visit-uncertain-notice';
      notice.setAttribute('role', 'alert');
      notice.textContent = config.statusUncertain;

      const visitId = row.dataset.visitId || form.action.match(/\/appointment\/(\d+)\//)?.[1];
      const reviewLink = buildViewLink(visitId);

      const actionCell = row.querySelector('.visit-action');
      if (actionCell) {
        actionCell.appendChild(notice);
        actionCell.appendChild(reviewLink);
        reviewLink.focus();
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
          } catch (_) {
            data = null;
          }

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
            }

            const isHidden = checkActiveFilter(row, confirmedStatus);
            if (isHidden) {
              moveFocusWhenRowDisappears(row);
            } else if (actionCell) {
              const newActionBtn = actionCell.querySelector('button, a');
              if (newActionBtn) {
                newActionBtn.focus();
              }
            }
          } else if (response.status === 409 && data) {
            // 409 Conflict / Stale: use localized message, never override with server English
            button.disabled = false;
            button.removeAttribute('aria-busy');
            if (button.dataset.originalText) {
              button.textContent = button.dataset.originalText;
            }

            const currentStatus = data.current_status;
            let actionCell = row.querySelector('.visit-action');
            if (currentStatus) {
              row.dataset.stage = currentStatus;
              updateRowBadge(row, currentStatus);
              if (actionCell) {
                const visitId = row.dataset.visitId || form.action.match(/\/appointment\/(\d+)\//)?.[1];
                actionCell.replaceChildren(createActionElement(currentStatus, visitId));
              }
            }

            const isHidden = currentStatus ? checkActiveFilter(row, currentStatus) : row.hidden;

            const staleNotice = document.createElement('p');
            staleNotice.className = 'visit-stale-notice';
            staleNotice.setAttribute('role', 'alert');
            staleNotice.textContent = config.statusStale;

            if (isHidden) {
              const container = row.closest('.visit-table-scroll') || row.closest('table') || row.parentElement;
              if (container && container.parentElement) {
                container.parentElement.querySelectorAll('.visit-stale-notice--global').forEach(function (el) { el.remove(); });
                staleNotice.classList.add('visit-stale-notice--global');
                container.parentElement.insertBefore(staleNotice, container);
                staleNotice.setAttribute('tabindex', '-1');
                staleNotice.focus();
              } else {
                moveFocusWhenRowDisappears(row);
              }
            } else if (actionCell) {
              actionCell.appendChild(staleNotice);
              const activeInteractive = actionCell.querySelector('button, a');
              if (activeInteractive) {
                activeInteractive.focus();
              } else {
                staleNotice.setAttribute('tabindex', '-1');
                staleNotice.focus();
              }
            }
          } else if (data) {
            // Handled non-200 response with parsed JSON (e.g. 422 validation)
            button.disabled = false;
            button.removeAttribute('aria-busy');
            if (button.dataset.originalText) {
              button.textContent = button.dataset.originalText;
            }

            const errorNotice = document.createElement('p');
            errorNotice.className = 'visit-stale-notice';
            errorNotice.setAttribute('role', 'alert');
            errorNotice.textContent = (response.status === 422 && data.message) ? data.message : config.statusError;

            const actionCell = row.querySelector('.visit-action');
            if (actionCell) {
              actionCell.appendChild(errorNotice);
              errorNotice.setAttribute('tabindex', '-1');
              errorNotice.focus();
            }
          } else {
            // Uncertain outcome (e.g. non-JSON error or invalid response): no silent retry, require review/refresh
            handleUncertainOutcome(row, form, button);
          }
        })
        .catch(function () {
          // Network failure: uncertain outcome, never blindly retry or expose raw technical err.message
          handleUncertainOutcome(row, form, button);
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
