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
      actionStartVisit: configEl.dataset.actionStartVisit || 'Start visit',
      actionReadyForReception: configEl.dataset.actionReadyForReception || 'Ready for reception',
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

    function getRowPatientName(row) {
      if (!row) return '';
      return (row.dataset.patientName || row.querySelector('.visit-identity strong, .patient-name')?.textContent || '').trim();
    }

    function getScopedAriaLabel(actionLabel, patientName) {
      return patientName ? (actionLabel + ' - ' + patientName) : actionLabel;
    }

    function buildTransitionForm(visitId, targetStatus, expectedStatus, buttonClass, buttonText, patientName) {
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
      btn.setAttribute('aria-label', getScopedAriaLabel(buttonText, patientName));
      form.appendChild(btn);

      return form;
    }

    function buildViewLink(visitId, patientName) {
      const viewLink = document.createElement('a');
      viewLink.className = 'button secondary';
      viewLink.href = config.baseUrl + visitId + '?return_to=dashboard';
      viewLink.textContent = config.actionView;
      viewLink.setAttribute('aria-label', getScopedAriaLabel(config.actionView, patientName));
      return viewLink;
    }

    function createActionElement(stage, visitId, patientName, row) {
      const group = document.createElement('div');
      group.className = 'visit-action-group';

      if (stage === 'in_chair') {
        const encounterType = row?.dataset?.encounterType || '';
        const encounterUrl = row?.dataset?.encounterUrl || '';
        const encounterLabel = row?.dataset?.encounterLabel || '';
        const encounterAriaLabel = row?.dataset?.encounterAriaLabel || '';
        const hasEncounter = encounterType !== 'fallback' && Boolean(encounterUrl && encounterLabel);

        if (hasEncounter) {
          const encounterLink = document.createElement('a');
          encounterLink.className = 'button secondary encounter-btn';
          encounterLink.href = encounterUrl;
          encounterLink.textContent = encounterLabel;
          encounterLink.setAttribute('aria-label', encounterAriaLabel || getScopedAriaLabel(encounterLabel, patientName));
          group.appendChild(encounterLink);
        }

        if (config.canEdit) {
          group.appendChild(buildTransitionForm(visitId, 'checkout', 'in_chair', 'button primary transition-btn', config.actionReadyForReception, patientName));
        }

        if (!hasEncounter) {
          group.appendChild(buildViewLink(visitId, patientName));
        }

        return group;
      }

      if (config.canEdit) {
        if (stage === 'scheduled' || stage === 'confirmed') {
          group.appendChild(buildTransitionForm(visitId, 'arrived', stage, 'button primary transition-btn', config.actionArrival, patientName));
          return group;
        }
        if (stage === 'arrived') {
          group.appendChild(buildTransitionForm(visitId, 'ready', 'arrived', 'button secondary transition-btn', config.actionReady, patientName));
          return group;
        }
        if (stage === 'ready') {
          group.appendChild(buildTransitionForm(visitId, 'in_chair', 'ready', 'button primary transition-btn', config.actionStartVisit, patientName));
          return group;
        }
      }

      group.appendChild(buildViewLink(visitId, patientName));
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
      const activeFilterBtn = document.querySelector('.visit-filters-bar .filter-btn.active');
      const filter = document.getElementById('visit-filter');
      const val = activeFilterBtn?.dataset.filter || filter?.value || 'all';
      if (val !== 'all') {
        const waitingStages = ['arrived', 'ready'];
        if (val === 'waiting') {
          row.hidden = !waitingStages.includes(newStage);
        } else if (val === 'in_chair') {
          row.hidden = (newStage !== 'in_chair');
        } else if (val === 'other') {
          row.hidden = waitingStages.includes(newStage) || newStage === 'in_chair';
        } else {
          row.hidden = (val !== newStage);
        }
      } else {
        row.hidden = false;
      }
      return row.hidden;
    }

    function moveFocusWhenRowDisappears(row) {
      const tableBody = row.parentElement;
      const nextVisible = tableBody ? tableBody.querySelector('.visit-entry:not([hidden]) .visit-action button, .visit-entry:not([hidden]) .visit-action a, tr.visit-entry:not([hidden]) .visit-action button, tr.visit-entry:not([hidden]) .visit-action a') : null;
      if (nextVisible) {
        nextVisible.focus();
        return;
      }
      const activeFilter = document.querySelector('.filter-btn.active, .filter-group button');
      if (activeFilter) {
        activeFilter.focus();
        return;
      }
      const filter = document.getElementById('visit-filter');
      if (filter && filter.tabIndex >= 0 && !filter.matches?.('[aria-hidden="true"], [hidden], .sr-only')) {
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
      const patientName = getRowPatientName(row);
      const reviewLink = buildViewLink(visitId, patientName);

      const actionCell = row.querySelector('.visit-action');
      if (actionCell) {
        actionCell.appendChild(notice);
        actionCell.appendChild(reviewLink);
        reviewLink.focus();
      }
    }

    function refreshCountsAndFilter(targetValue) {
      const selectFilter = document.getElementById('visit-filter');
      const filterButtons = document.querySelectorAll('.visit-filters-bar .filter-btn');
      const activeBtn = document.querySelector('.visit-filters-bar .filter-btn.active');
      const currentFilter = targetValue || (selectFilter && selectFilter.value ? selectFilter.value : activeBtn?.dataset.filter) || 'all';

      if (selectFilter && selectFilter.value !== currentFilter) {
        selectFilter.value = currentFilter;
      }

      filterButtons.forEach(function (btn) {
        const isMatch = btn.dataset.filter === currentFilter;
        btn.classList.toggle('active', isMatch);
        btn.setAttribute('aria-checked', isMatch ? 'true' : 'false');
      });

      const waitingStages = ['arrived', 'ready'];

      document.querySelectorAll('[role="tabpanel"]').forEach(function (panel) {
        const rows = panel.querySelectorAll('.visit-entry');
        let visibleCount = 0;

        rows.forEach(function (row) {
          const stage = row.dataset.stage || '';
          let show = true;
          if (currentFilter === 'all') {
            show = true;
          } else if (currentFilter === 'waiting') {
            show = waitingStages.includes(stage);
          } else if (currentFilter === 'in_chair') {
            show = (stage === 'in_chair');
          } else if (currentFilter === 'other') {
            show = (!waitingStages.includes(stage) && stage !== 'in_chair');
          } else {
            show = (stage === currentFilter);
          }
          row.hidden = !show;
          if (show) visibleCount++;
        });

        const emptyMsg = panel.querySelector('.filter-empty');
        if (emptyMsg) {
          emptyMsg.hidden = (visibleCount > 0);
        }
      });

      // Update count badges based on active tabpanel
      const activePanel = document.querySelector('[role="tabpanel"]:not([hidden])') || document.querySelector('[role="tabpanel"]');
      if (activePanel) {
        const panelRows = activePanel.querySelectorAll('.visit-entry');
        let countAll = panelRows.length;
        let countWaiting = 0;
        let countInChair = 0;
        let countOther = 0;

        panelRows.forEach(function (row) {
          const st = row.dataset.stage || '';
          if (waitingStages.includes(st)) {
            countWaiting++;
          } else if (st === 'in_chair') {
            countInChair++;
          } else {
            countOther++;
          }
        });

        const allBtn = document.querySelector('.filter-btn[data-filter="all"]');
        if (allBtn) {
          const cnt = allBtn.querySelector('.filter-count');
          if (cnt) cnt.textContent = '(' + countAll + ')';
        }

        const waitBtn = document.querySelector('.filter-btn[data-filter="waiting"]');
        if (waitBtn) {
          const cnt = waitBtn.querySelector('.filter-count');
          if (cnt) cnt.textContent = '(' + countWaiting + ')';
        }

        const inChairBtn = document.querySelector('.filter-btn[data-filter="in_chair"]');
        if (inChairBtn) {
          const cnt = inChairBtn.querySelector('.filter-count');
          if (cnt) cnt.textContent = '(' + countInChair + ')';
        }

        const otherBtn = document.querySelector('.filter-btn[data-filter="other"]');
        if (otherBtn) {
          const cnt = otherBtn.querySelector('.filter-count');
          if (cnt) cnt.textContent = '(' + countOther + ')';
          // Make Other available when checkout or other appears after transition even if initial 0
          otherBtn.hidden = countOther === 0 && currentFilter !== 'other';
        }
      }

      // Update tab badge counts
      const todayPanel = document.getElementById('panel-today');
      if (todayPanel) {
        const countTabToday = document.getElementById('count-tab-today') || document.querySelector('#tab-today .dashboard-tab-badge');
        if (countTabToday) countTabToday.textContent = String(todayPanel.querySelectorAll('.visit-entry').length);
      }
      const upcomingPanel = document.getElementById('panel-upcoming');
      if (upcomingPanel) {
        const countTabUpcoming = document.getElementById('count-tab-upcoming') || document.querySelector('#tab-upcoming .dashboard-tab-badge');
        if (countTabUpcoming) countTabUpcoming.textContent = String(upcomingPanel.querySelectorAll('.visit-entry').length);
      }
    }

    window.StudioFilterVisits = refreshCountsAndFilter;

    function initFilterButtons() {
      const filterButtons = document.querySelectorAll('.visit-filters-bar .filter-btn');
      const selectFilter = document.getElementById('visit-filter');
      if (!filterButtons.length && !selectFilter) return;

      filterButtons.forEach(function (btn) {
        btn.addEventListener('click', function () {
          refreshCountsAndFilter(btn.dataset.filter);
        });
        btn.addEventListener('keydown', function (e) {
          const visibleButtons = Array.from(filterButtons).filter(b => !b.hidden && (typeof getComputedStyle === 'undefined' || getComputedStyle(b).display !== 'none'));
          const idx = visibleButtons.indexOf(btn);
          if (idx === -1) return;
          if (e.key === 'ArrowRight' || e.key === 'ArrowDown') {
            e.preventDefault();
            const next = visibleButtons[(idx + 1) % visibleButtons.length];
            next.focus();
            refreshCountsAndFilter(next.dataset.filter);
          } else if (e.key === 'ArrowLeft' || e.key === 'ArrowUp') {
            e.preventDefault();
            const prev = visibleButtons[(idx - 1 + visibleButtons.length) % visibleButtons.length];
            prev.focus();
            refreshCountsAndFilter(prev.dataset.filter);
          } else if (e.key === 'Home') {
            e.preventDefault();
            visibleButtons[0].focus();
            refreshCountsAndFilter(visibleButtons[0].dataset.filter);
          } else if (e.key === 'End') {
            e.preventDefault();
            visibleButtons[visibleButtons.length - 1].focus();
            refreshCountsAndFilter(visibleButtons[visibleButtons.length - 1].dataset.filter);
          }
        });
      });

      if (selectFilter) {
        selectFilter.addEventListener('change', function () {
          refreshCountsAndFilter(selectFilter.value);
        });
      }

      refreshCountsAndFilter();
    }

    initFilterButtons();

    document.addEventListener('submit', function (event) {
      const form = event.target.closest('form[data-appointment-transition]');
      if (!form) return;

      const row = form.closest('.visit-entry');
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
              const patientName = getRowPatientName(row);
              actionCell.replaceChildren(createActionElement(confirmedStatus, visitId, patientName, row));
            }

            refreshCountsAndFilter();

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
                const patientName = getRowPatientName(row);
                actionCell.replaceChildren(createActionElement(currentStatus, visitId, patientName, row));
              }
              refreshCountsAndFilter();
            }

            const isHidden = currentStatus ? checkActiveFilter(row, currentStatus) : row.hidden;

            const staleNotice = document.createElement('p');
            staleNotice.className = 'visit-stale-notice';
            staleNotice.setAttribute('role', 'alert');
            staleNotice.textContent = config.statusStale;

            if (isHidden) {
              const container = row.closest('.visit-table-scroll') || row.closest('.visit-deck') || row.closest('table') || row.parentElement;
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
