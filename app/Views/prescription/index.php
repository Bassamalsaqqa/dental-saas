<?= $this->extend('layouts/main_auth') ?>

<?= $this->section('content') ?>
<?php
$currentUri = service('request')->getUri();
$returnToContext = ltrim($currentUri->getPath(), '/');
$currentQuery = $currentUri->getQuery();
if ($currentQuery !== '') {
    $returnToContext .= '?' . $currentQuery;
}
$returnToContextEncoded = urlencode($returnToContext);
$shouldOpenPrescriptionCreateModal = old('source_modal') === 'prescription_quick_create';
$isRtl = !empty($is_rtl) || (service('request')->getLocale() === 'ar');
?>

<!-- DataTables CSS -->
<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.13.7/css/jquery.dataTables.min.css">
<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/buttons/2.4.2/css/buttons.dataTables.min.css">

<!-- Custom DataTables Styling -->




<div class="denta-prescription-workspace prescriptions-page min-h-screen v3-surface" dir="<?= $isRtl ? 'rtl' : 'ltr' ?>">
    <div class="w-full px-3 py-4 sm:px-5 sm:py-5">
        <section class="v3-command-section prescription-command-header mb-5 overflow-hidden v3-corner">
            <div class="v3-command-layout">
                <div class="flex min-w-0 items-start gap-4">
                    <span class="inline-flex h-14 w-14 shrink-0 items-center justify-center v3-corner v3-surface v3-text-on-primary border v3-border-default">
                        <i class="fas fa-prescription-bottle-alt text-xl"></i>
                    </span>
                    <div class="min-w-0">
                        <p class="text-xs font-black uppercase tracking-[0.22em] v3-text-primary"><?= lang('Prescription.prescription_registry') ?></p>
                        <h1 class="mt-1 text-2xl font-black leading-tight v3-text-on-primary sm:text-3xl"><?= lang('Prescription.management_title') ?></h1>
                        <p class="mt-2 max-w-2xl text-sm font-semibold leading-6 v3-text-primary"><?= lang('Prescription.management_subtitle') ?></p>
                    </div>
                </div>
                <?php if (has_permission('prescriptions', 'create')): ?>
                    <a href="<?= base_url('prescription/create') ?>" id="newPrescriptionModalTrigger" data-prescription-create-modal-open class="inline-flex items-center justify-center gap-2 v3-radius v3-surface px-5 py-3 text-sm font-black v3-text-primary transition v3-hover-primary focus:outline-none focus:ring-4 v3-focus-control v3-btn v3-btn--secondary">
                        <i class="fas fa-plus"></i>
                        <?= lang('Prescription.new_prescription') ?>
                    </a>
                <?php endif; ?>
            </div>
        </section>

        <section class="v3-metric-strip">
            <article class="v3-metric v3-corner p-4">
                <div class="flex items-center justify-between gap-3">
                    <div>
                        <p class="text-xs font-black uppercase tracking-wide v3-text-primary"><?= lang('Prescription.total_prescriptions') ?></p>
                        <p class="mt-2 v3-title-scale font-black" id="totalPrescriptions"><i class="fas fa-spinner fa-spin text-lg"></i></p>
                    </div>
                    <span class="inline-flex h-11 w-11 items-center justify-center v3-corner v3-surface border v3-border-default"><i class="fas fa-prescription-bottle-alt"></i></span>
                </div>
            </article>
            <article class="v3-metric v3-corner p-4">
                <div class="flex items-center justify-between gap-3">
                    <div>
                        <p class="text-xs font-black uppercase tracking-wide v3-text-primary"><?= lang('Prescription.active_prescriptions') ?></p>
                        <p class="mt-2 v3-title-scale font-black" id="activePrescriptions"><i class="fas fa-spinner fa-spin text-lg"></i></p>
                    </div>
                    <span class="inline-flex h-11 w-11 items-center justify-center v3-corner v3-surface border v3-border-default"><i class="fas fa-check-circle"></i></span>
                </div>
            </article>
            <article class="v3-metric v3-corner p-4">
                <div class="flex items-center justify-between gap-3">
                    <div>
                        <p class="text-xs font-black uppercase tracking-wide v3-text-warning"><?= lang('Prescription.expired_prescriptions') ?></p>
                        <p class="mt-2 v3-title-scale font-black" id="expiredPrescriptions"><i class="fas fa-spinner fa-spin text-lg"></i></p>
                    </div>
                    <span class="inline-flex h-11 w-11 items-center justify-center v3-corner v3-surface border v3-border-default"><i class="fas fa-exclamation-triangle"></i></span>
                </div>
            </article>
            <article class="v3-metric v3-corner p-4">
                <div class="flex items-center justify-between gap-3">
                    <div>
                        <p class="text-xs font-black uppercase tracking-wide v3-accent-text"><?= lang('Prescription.this_month') ?></p>
                        <p class="mt-2 v3-title-scale font-black" id="monthlyPrescriptions"><i class="fas fa-spinner fa-spin text-lg"></i></p>
                    </div>
                    <span class="inline-flex h-11 w-11 items-center justify-center v3-corner v3-surface border v3-border-default"><i class="fas fa-calendar-day"></i></span>
                </div>
            </article>
        </section>

        <section id="tableView" class="overflow-hidden v3-corner border v3-border v3-surface">
            <div class="v3-surface-heading border-b v3-border px-4 py-4 sm:px-6">
                <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                    <div class="flex items-center gap-3">
                        <span class="inline-flex h-10 w-10 items-center justify-center v3-corner v3-bg-scheduled v3-text-primary">
                            <i class="fas fa-list-check"></i>
                        </span>
                        <div>
                            <h3 class="text-lg font-black v3-text-ink"><?= lang('Prescription.prescription_registry') ?></h3>
                            <p class="mt-1 text-xs font-bold v3-text-muted"><?= lang('Prescription.management_subtitle') ?></p>
                        </div>
                    </div>
                    <div class="flex flex-wrap items-center gap-2">
                        <button onclick="exportToPDF()" class="inline-flex items-center gap-2 v3-radius border v3-border v3-surface px-3 py-2 text-sm font-extrabold v3-text-ink transition v3-hover-danger v3-hover-text-danger v3-btn v3-btn--secondary">
                            <i class="fas fa-file-pdf v3-text-danger"></i>
                            <?= lang('Prescription.export_pdf') ?>
                        </button>
                        <button onclick="printPrescriptions()" class="inline-flex items-center gap-2 v3-radius border v3-border v3-surface px-3 py-2 text-sm font-extrabold v3-text-ink transition v3-hover-soft v3-hover-text-ink v3-btn v3-btn--secondary">
                            <i class="fas fa-print v3-text-muted"></i>
                            <?= lang('Prescription.export_print') ?>
                        </button>
                        <button onclick="exportToCSV()" class="inline-flex items-center gap-2 v3-radius border v3-border v3-surface px-3 py-2 text-sm font-extrabold v3-text-ink transition v3-hover-primary v3-hover-text-primary v3-btn v3-btn--secondary">
                            <i class="fas fa-file-csv v3-text-primary"></i>
                            <?= lang('Prescription.export_csv') ?>
                        </button>
                    </div>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table id="prescriptionsTable" class="w-full min-w-full">
                    <thead>
                        <tr>
                            <th class="px-6 py-4 text-start text-xs font-black uppercase tracking-wider v3-text-muted">
                                <div class="flex items-center gap-2">
                                    <i class="fas fa-prescription-bottle-alt v3-text-primary"></i>
                                    <span><?= lang('Prescription.prescription_id') ?></span>
                                </div>
                            </th>
                            <th class="px-6 py-4 text-start text-xs font-black uppercase tracking-wider v3-text-muted">
                                <div class="flex items-center gap-2">
                                    <i class="fas fa-user v3-text-primary"></i>
                                    <span><?= lang('Prescription.patient') ?></span>
                                </div>
                            </th>
                            <th class="px-6 py-4 text-start text-xs font-black uppercase tracking-wider v3-text-muted">
                                <div class="flex items-center gap-2">
                                    <i class="fas fa-pills v3-text-primary"></i>
                                    <span><?= lang('Prescription.medicines') ?></span>
                                </div>
                            </th>
                            <th class="px-6 py-4 text-start text-xs font-black uppercase tracking-wider v3-text-muted">
                                <div class="flex items-center gap-2">
                                    <i class="fas fa-calendar-alt v3-text-primary"></i>
                                    <span><?= lang('Prescription.date_col') ?></span>
                                </div>
                            </th>
                            <th class="px-6 py-4 text-start text-xs font-black uppercase tracking-wider v3-text-muted">
                                <div class="flex items-center gap-2">
                                    <i class="fas fa-info-circle v3-text-primary"></i>
                                    <span><?= lang('Prescription.status') ?></span>
                                </div>
                            </th>
                            <th class="px-6 py-4 text-start text-xs font-black uppercase tracking-wider v3-text-muted">
                                <div class="flex items-center gap-2">
                                    <i class="fas fa-cogs v3-text-primary"></i>
                                    <span><?= lang('Prescription.actions') ?></span>
                                </div>
                            </th>
                        </tr>
                    </thead>
                    <tbody id="prescriptionsTableBody" class="divide-y v3-border-default v3-surface">
                        <tr id="loadingRow">
                            <td colspan="6" class="px-6 py-4 text-center v3-text-muted">
                                <div class="flex items-center justify-center gap-2">
                                    <div class="h-4 w-4 animate-spin rounded-full border-b-2 v3-border-default"></div>
                                    <span><?= lang('Prescription.loading_prescriptions') ?></span>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </section>
    </div>
</div>

<?php if (has_permission('prescriptions', 'create')): ?>
    <div id="newPrescriptionModal" class="app-scale-modal prescription-create-modal fixed inset-0 z-[2147483600] hidden overflow-hidden" role="dialog" aria-modal="true" aria-hidden="true" aria-labelledby="newPrescriptionModalTitle" dir="<?= $isRtl ? 'rtl' : 'ltr' ?>">
        <div class="prescription-create-modal__backdrop fixed inset-0 v3-backdrop" data-prescription-create-modal-close></div>
        <div class="prescription-create-modal__shell relative z-10 flex min-h-screen items-center justify-center p-4" data-prescription-create-modal-close>
            <form id="newPrescriptionQuickCreateForm" action="<?= base_url('prescription/store') ?>" method="POST" class="prescription-create-modal__panel flex max-h-[92dvh] w-full max-w-5xl flex-col overflow-hidden v3-radius border v3-border v3-surface" data-prescription-create-form data-select2-dropdown-parent onclick="event.stopPropagation()">
                <?= csrf_field() ?>
                <input type="hidden" name="return_to" value="<?= esc($returnToContext) ?>">
                <input type="hidden" name="source_modal" value="prescription_quick_create">

                <div class="flex flex-shrink-0 items-start justify-between gap-4 border-b v3-border v3-surface px-5 py-4">
                    <div class="flex items-center gap-3">
                        <span class="v3-icon inline-flex h-11 w-11 items-center justify-center v3-corner">
                            <i class="fas fa-prescription-bottle-alt"></i>
                        </span>
                        <div>
                            <h2 id="newPrescriptionModalTitle" class="text-lg font-bold v3-text-ink"><?= esc(lang('Prescription.new_prescription')) ?></h2>
                            <a href="<?= workflow_context_url('prescription/create', $returnToContext) ?>" class="mt-1 inline-flex text-xs font-semibold v3-text-primary v3-hover-text-primary">
                                <?= esc(lang('UI.openFullPage')) ?>
                            </a>
                        </div>
                    </div>
                    <button type="button" class="inline-flex h-9 w-9 items-center justify-center rounded-full v3-text-muted transition v3-hover-soft v3-hover-text-ink v3-btn v3-btn--icon" data-prescription-create-modal-close aria-label="<?= esc(lang('UI.closeDrawer')) ?>">
                        <i class="fas fa-times text-sm"></i>
                    </button>
                </div>

                <div class="min-h-0 flex-1 space-y-5 overflow-y-auto v3-surface p-5">
                    <?php if (session('error') && $shouldOpenPrescriptionCreateModal): ?>
                        <div class="v3-radius border v3-border-default v3-bg-alert p-4 text-sm v3-text-danger">
                            <?= esc(session('error')) ?>
                        </div>
                    <?php endif; ?>
                    <?php if (session('errors') && $shouldOpenPrescriptionCreateModal): ?>
                        <div class="v3-radius border v3-border-default v3-bg-alert p-4 text-sm v3-text-danger">
                            <?php foreach ((array) session('errors') as $error): ?>
                                <div><?= esc($error) ?></div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>

                    <div class="v3-corner border v3-border v3-surface p-5 md:p-6">
                        <?= view('prescription/partials/form_fields', [
                            'selected_patient' => null,
                            'is_rtl' => $isRtl,
                            'id_prefix' => 'prescriptionQuick',
                        ]) ?>
                    </div>
                </div>

                <div class="flex flex-shrink-0 flex-col-reverse gap-3 border-t v3-border v3-surface px-5 py-4 sm:flex-row sm:justify-end">
                    <button type="button" data-prescription-create-modal-close class="inline-flex items-center justify-center v3-radius border v3-border px-5 py-2.5 text-sm font-semibold v3-text-ink transition v3-hover-soft v3-btn v3-btn--secondary">
                        <?= esc(lang('Prescription.cancel')) ?>
                    </button>
                    <button type="submit" class="v3-action v3-action-primary inline-flex items-center justify-center v3-radius px-5 py-2.5 text-sm font-bold transition v3-btn v3-btn--primary">
                        <i class="fas fa-prescription-bottle-alt me-2"></i>
                        <?= esc(lang('Prescription.create_prescription')) ?>
                    </button>
                </div>
            </form>
        </div>
    </div>
<?php endif; ?>

<?= view('prescription/partials/form_script') ?>

<!-- DataTables JS -->
<script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/dataTables.buttons.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/pdfmake.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/vfs_fonts.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.html5.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.print.min.js"></script>

<script>
// Global variables
let prescriptionsTable;
let newPrescriptionModalTrigger = null;
let newPrescriptionModalCloseTimer = null;
const prescriptionI18n = {
    prescriptionId: <?= json_encode(lang('Prescription.prescription_id')) ?>,
    noPhone: <?= json_encode(lang('Prescription.no_phone')) ?>,
    medicineSingular: <?= json_encode(lang('Prescription.medicine_singular')) ?>,
    medicinePlural: <?= json_encode(lang('Prescription.medicine_plural')) ?>,
    oneMedicine: <?= json_encode(lang('Prescription.one_medicine')) ?>,
    noMedicines: <?= json_encode(lang('Prescription.no_medicines')) ?>,
    statusLabels: {
        active: <?= json_encode(lang('Prescription.status_active')) ?>,
        expired: <?= json_encode(lang('Prescription.status_expired')) ?>,
        cancelled: <?= json_encode(lang('Prescription.status_cancelled')) ?>,
        pending: <?= json_encode(lang('Prescription.status_pending')) ?>
    },
    viewDetails: <?= json_encode(lang('Prescription.view_details')) ?>,
    printPrescription: <?= json_encode(lang('Prescription.print_prescription')) ?>,
    editPrescription: <?= json_encode(lang('Prescription.edit_prescription')) ?>,
    deletePrescription: <?= json_encode(lang('Prescription.delete_prescription')) ?>,
    exportPdf: <?= json_encode(lang('Prescription.export_pdf')) ?>,
    exportPrint: <?= json_encode(lang('Prescription.export_print')) ?>,
    exportCsv: <?= json_encode(lang('Prescription.export_csv')) ?>,
    prescriptionsReport: <?= json_encode(lang('Prescription.prescriptions_report')) ?>,
    dtProcessing: <?= json_encode(lang('Prescription.dt_processing')) ?>,
    dtZeroRecords: <?= json_encode(lang('Prescription.dt_zero_records')) ?>,
    dtInfo: <?= json_encode(lang('Prescription.dt_info')) ?>,
    dtInfoEmpty: <?= json_encode(lang('Prescription.dt_info_empty')) ?>,
    dtInfoFiltered: <?= json_encode(lang('Prescription.dt_info_filtered')) ?>,
    dtSearchPlaceholder: <?= json_encode(lang('Prescription.dt_search_placeholder')) ?>,
    dtFirst: <?= json_encode(lang('Prescription.dt_first')) ?>,
    dtLast: <?= json_encode(lang('Prescription.dt_last')) ?>,
    dtNext: <?= json_encode(lang('Prescription.dt_next')) ?>,
    dtPrevious: <?= json_encode(lang('Prescription.dt_previous')) ?>,
    errorLoadingData: <?= json_encode(lang('Prescription.error_loading_data')) ?>,
    unknownError: <?= json_encode(lang('Prescription.unknown_error')) ?>,
    deleteConfirm: <?= json_encode(lang('Prescription.delete_confirm')) ?>
};

function getPatientAvatar(gender) {
    const avatarMap = {
        male: { icon: 'fas fa-person', color: 'v3-text-primary' },
        female: { icon: 'fas fa-person-dress', color: 'v3-text-danger' },
        other: { icon: 'fas fa-user', color: 'v3-text-muted' }
    };

    return avatarMap[String(gender || 'other').toLowerCase()] || avatarMap.other;
}

function mountNewPrescriptionModal() {
    const modal = document.getElementById('newPrescriptionModal');
    if (modal && modal.parentElement !== document.body) {
        document.body.appendChild(modal);
    }
    if (modal && modal.dataset.closeControlsBound !== '1') {
        modal.dataset.closeControlsBound = '1';
        modal.querySelectorAll('button[data-prescription-create-modal-close], a[data-prescription-create-modal-close]').forEach(function(control) {
            control.addEventListener('click', function(event) {
                event.preventDefault();
                closeNewPrescriptionModal();
            });
        });
    }
    return modal;
}

function initializeNewPrescriptionModalContent(modal) {
    if (!modal) {
        return Promise.resolve();
    }
    const tasks = [];
    if (typeof window.initAppSelects === 'function') {
        tasks.push(Promise.resolve(window.initAppSelects(modal)));
    }
    if (typeof window.initializeClinicDateInputs === 'function') {
        window.initializeClinicDateInputs(modal);
    }
    if (typeof window.initPrescriptionCreateForms === 'function') {
        window.initPrescriptionCreateForms(modal);
    }
    return Promise.allSettled(tasks);
}

function openNewPrescriptionModal(trigger) {
    const modal = mountNewPrescriptionModal();
    if (!modal) {
        return;
    }
    newPrescriptionModalTrigger = trigger || document.activeElement;
    if (newPrescriptionModalCloseTimer) {
        clearTimeout(newPrescriptionModalCloseTimer);
        newPrescriptionModalCloseTimer = null;
    }
    modal.classList.remove('hidden');
    modal.setAttribute('aria-hidden', 'false');
    document.documentElement.classList.add('app-drawer-lock');
    document.body.classList.add('app-drawer-lock');
    requestAnimationFrame(function() {
        modal.classList.add('is-open');
    });
    window.setTimeout(function() {
        initializeNewPrescriptionModalContent(modal);
        const firstField = modal.querySelector('[name="patient_id"], input, select, textarea, button');
        if (firstField && typeof firstField.focus === 'function') {
            firstField.focus({ preventScroll: true });
        }
    }, 80);
}

function closeNewPrescriptionModal() {
    const modal = document.getElementById('newPrescriptionModal');
    if (!modal || modal.classList.contains('hidden')) {
        return;
    }
    modal.classList.remove('is-open');
    modal.setAttribute('aria-hidden', 'true');
    document.documentElement.classList.remove('app-drawer-lock');
    document.body.classList.remove('app-drawer-lock');
    newPrescriptionModalCloseTimer = setTimeout(function() {
        const form = modal.querySelector('[data-prescription-create-form]');
        if (form) {
            form.reset();
            if (typeof form.prescriptionReset === 'function') {
                form.prescriptionReset();
            }
            form.querySelectorAll('select').forEach(function(select) {
                select.dispatchEvent(new Event('change', { bubbles: true }));
                if (window.jQuery) {
                    window.jQuery(select).trigger('change');
                }
            });
        }
        modal.classList.add('hidden');
        if (newPrescriptionModalTrigger && typeof newPrescriptionModalTrigger.focus === 'function') {
            newPrescriptionModalTrigger.focus();
        }
        newPrescriptionModalTrigger = null;
    }, 240);
}

document.addEventListener('click', function(event) {
    const openTrigger = event.target.closest('[data-prescription-create-modal-open]');
    if (openTrigger) {
        event.preventDefault();
        openNewPrescriptionModal(openTrigger);
        return;
    }

    const closeTrigger = event.target.closest('[data-prescription-create-modal-close]');
    if (closeTrigger) {
        event.preventDefault();
        closeNewPrescriptionModal();
    }
});

document.addEventListener('keydown', function(event) {
    if (event.key !== 'Escape') {
        return;
    }
    const modal = document.getElementById('newPrescriptionModal');
    if (modal && !modal.classList.contains('hidden')) {
        event.preventDefault();
        closeNewPrescriptionModal();
    }
});

$(document).ready(function() {
    if (<?= $shouldOpenPrescriptionCreateModal ? 'true' : 'false' ?>) {
        openNewPrescriptionModal(document.getElementById('newPrescriptionModalTrigger'));
    }

    // Initialize DataTable with server-side processing
    prescriptionsTable = $('#prescriptionsTable').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: '<?= base_url('prescription/get-prescriptions') ?>',
            type: 'POST',
            data: function(d) {
                // Add CSRF token to body
                d[window.csrfConfig.name] = window.getCsrfToken();
                return d;
            },
            beforeSend: function(xhr) {
                xhr.setRequestHeader(window.csrfConfig.header, window.getCsrfToken());
            },
            complete: function(xhr) {
                if (xhr.responseJSON && xhr.responseJSON.csrf_token) {
                    window.refreshCsrfToken(xhr.responseJSON.csrf_token);
                }
            },
            error: function(xhr, error, thrown) {
                console.error('DataTables Ajax error:', {
                    xhr: xhr,
                    error: error,
                    thrown: thrown,
                    responseText: xhr.responseText,
                    status: xhr.status,
                    statusText: xhr.statusText
                });

                // Refresh token on error too
                if (xhr.responseJSON && xhr.responseJSON.csrf_token) {
                    window.refreshCsrfToken(xhr.responseJSON.csrf_token);
                }

                let errorMessage = prescriptionI18n.errorLoadingData;
                try {
                    const response = JSON.parse(xhr.responseText);
                    if (response.error) {
                        errorMessage = response.error;
                    }
                } catch (e) {
                    errorMessage = xhr.responseText || prescriptionI18n.unknownError;
                }

                // Show error message
                const tbody = document.getElementById('prescriptionsTableBody');
                if (tbody) {
                    const tr = document.createElement('tr');
                    const td = document.createElement('td');
                    td.setAttribute('colspan', '6');
                    td.className = 'px-6 py-4 text-center v3-text-danger';

                    const flexDiv = document.createElement('div');
                    flexDiv.className = 'flex items-center justify-center gap-2';

                    const icon = document.createElement('i');
                    icon.className = 'fas fa-exclamation-triangle';

                    const span = document.createElement('span');
                    span.textContent = errorMessage;

                    flexDiv.appendChild(icon);
                    flexDiv.appendChild(span);
                    td.appendChild(flexDiv);
                    tr.appendChild(td);

                    tbody.replaceChildren(tr);
                }
            }
        },
        columns: [
            {
                data: 'prescription_id',
                name: 'prescription_id',
                render: function(data, type, row) {
                    const returnTo = encodeURIComponent((window.location.pathname + window.location.search).replace(/^\/+/, ''));
                    const identityHtml = `
                            <div class="flex items-center gap-3">
                            <div class="flex h-10 w-10 items-center justify-center v3-radius v3-bg-scheduled v3-text-primary">
                                <i class="fas fa-prescription-bottle-alt text-sm"></i>
                            </div>
                            <div>
                                <div class="text-sm font-extrabold v3-text-ink">${data}</div>
                                <div class="text-xs font-bold v3-text-muted">${prescriptionI18n.prescriptionId}</div>
                            </div>
                            </div>
                    `;
                    if (permissions.prescriptions.view) {
                        return `
                            <a href="<?= base_url('prescription') ?>/${row.id}" class="block cursor-pointer v3-radius transition-colors duration-200 v3-hover-primary">
                                ${identityHtml}
                            </a>
                        `;
                    }
                    return identityHtml;
                }
            },
            {
                data: 'patient_name',
                name: 'patient_name',
                render: function(data, type, row) {
                    const avatar = getPatientAvatar(row.gender);
                    return `
                            <div class="flex items-center gap-3">
                                <span class="inline-flex h-7 w-7 flex-shrink-0 items-center justify-center">
                                    <i class="${avatar.icon} ${avatar.color} text-2xl"></i>
                                </span>
                                <div>
                                    <div class="text-sm font-extrabold v3-text-ink">${data}</div>
                                    <div class="text-xs font-bold v3-text-muted">${row.patient_number || row.phone || prescriptionI18n.noPhone}</div>
                                </div>
                            </div>
                    `;
                }
            },
            {
                data: 'medication_name',
                name: 'medication_name',
                render: function(data, type, row) {
                    try {
                        const medicines = JSON.parse(data || '[]');
                        if (medicines.length > 0) {
                            return `
                                <div class="flex items-center gap-2">
                                    <i class="fas fa-pills v3-text-primary text-xs"></i>
                                    <span class="text-sm font-bold v3-text-ink">${medicines.length} ${medicines.length > 1 ? prescriptionI18n.medicinePlural : prescriptionI18n.medicineSingular}</span>
                                </div>
                            `;
                        }
                    } catch (e) {
                        // If not JSON, treat as single medicine
                        if (data) {
                            return `
                                <div class="flex items-center gap-2">
                                    <i class="fas fa-pills v3-text-primary text-xs"></i>
                                    <span class="text-sm font-bold v3-text-ink">${prescriptionI18n.oneMedicine}</span>
            </div>
        `;
                        }
                    }
                    return `<span class="text-sm font-bold v3-text-muted">${prescriptionI18n.noMedicines}</span>`;
                }
            },
            {
                data: 'prescribed_date',
                name: 'prescribed_date',
                render: function(data, type, row) {
                    return `
                        <div class="flex items-center gap-2">
                            <i class="fas fa-calendar-alt v3-text-primary text-xs"></i>
                            <span class="text-sm font-bold v3-text-ink">${row.prescribed_date_formatted || data}</span>
                        </div>
                    `;
                }
            },
            {
                data: 'status',
                name: 'status',
                render: function(data, type, row) {
                    const statusColors = {
                        'active': 'v3-border-default bg-gradient-to-r from-emerald-100 to-green-100 v3-text-primary',
                        'expired': 'v3-border-default bg-gradient-to-r from-red-100 to-rose-100 v3-text-danger',
                        'cancelled': 'v3-border-default bg-gradient-to-r from-slate-100 to-gray-100 v3-text-ink',
                        'pending': 'v3-border-default bg-gradient-to-r from-amber-100 to-yellow-100 v3-text-warning'
                    };
                    const statusIcons = {
                        'active': 'fas fa-check-circle',
                        'expired': 'fas fa-times-circle',
                        'cancelled': 'fas fa-ban',
                        'pending': 'fas fa-clock'
                    };
                    const colorClass = statusColors[data] || 'v3-border-default bg-gradient-to-r from-slate-100 to-gray-100 v3-text-ink';
                    const iconClass = statusIcons[data] || 'fas fa-question-circle';

                    return `
                        <span class="inline-flex items-center v3-radius border px-3 py-1.5 text-xs font-extrabold ${colorClass}">
                            <i class="${iconClass} me-2"></i>
                            ${prescriptionI18n.statusLabels[data] || prescriptionI18n.statusLabels.pending}
                        </span>
                    `;
                }
            },
            {
                data: 'id',
                name: 'actions',
                orderable: false,
                searchable: false,
                render: function(data, type, row) {
                    const returnTo = encodeURIComponent((window.location.pathname + window.location.search).replace(/^\/+/, ''));
                    let actions = '<div class="flex items-center gap-2">';

                    // View button
                    if (permissions.prescriptions.view) {
                        actions += `
                            <a href="<?= base_url('prescription') ?>/${data}"
                               class="v3-radius p-2 v3-text-primary transition-all duration-200  v3-hover-primary v3-hover-text-primary" title="${prescriptionI18n.viewDetails}">
                                <i class="fas fa-eye"></i>
                            </a>`;
                    }

                    // Print button
                    if (permissions.prescriptions.print) {
                        actions += `
                            <a href="<?= base_url('prescription') ?>/${data}/print"
                               class="v3-radius p-2 v3-text-primary transition-all duration-200  v3-hover-primary v3-hover-text-primary" title="${prescriptionI18n.printPrescription}">
                                <i class="fas fa-print"></i>
                            </a>`;
                    }

                    // Edit button
                    if (permissions.prescriptions.edit) {
                        actions += `
                            <a href="<?= base_url('prescription') ?>/${data}/edit"
                               class="v3-radius p-2 v3-text-primary transition-all duration-200  v3-hover-primary v3-hover-text-primary" title="${prescriptionI18n.editPrescription}">
                                <i class="fas fa-edit"></i>
                            </a>`;
                    }

                    // Delete button
                    if (permissions.prescriptions.delete) {
                        actions += `
                            <button onclick="deletePrescription(${data})"
                                    class="v3-radius p-2 v3-text-danger transition-all duration-200  v3-hover-danger v3-hover-text-danger v3-btn v3-btn--danger" title="${prescriptionI18n.deletePrescription}">
                                <i class="fas fa-trash"></i>
                            </button>`;
                    }

                    actions += '</div>';
                    return actions;
                }
            }
        ],
        dom: 'B<"border-b v3-border-default v3-bg-panel px-4 py-4 sm:px-6"<"flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between"<"flex items-center gap-4"l><"flex items-center gap-4"f>>rt<"border-t v3-border-default v3-bg-panel px-4 py-4 sm:px-6"<"flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between"<"flex items-center"i><"flex items-center"p>>>',
        pageLength: 10,
        lengthMenu: [[10, 25, 50, 100], [10, 25, 50, 100]],
        order: [[0, 'desc']],
        buttons: [
            {
                extend: 'pdf',
                text: '<i class="fas fa-file-pdf me-2"></i>' + prescriptionI18n.exportPdf,
                className: 'btn-pdf',
                title: prescriptionI18n.prescriptionsReport,
                orientation: 'landscape',
                pageSize: 'A4',
                exportOptions: {
                    columns: [0, 1, 2, 3, 4, 5] // Patient, Doctor, Date, Status, Notes, Actions
                }
            },
            {
                extend: 'print',
                text: '<i class="fas fa-print me-2"></i>' + prescriptionI18n.exportPrint,
                className: 'btn-print',
                title: prescriptionI18n.prescriptionsReport,
                exportOptions: {
                    columns: [0, 1, 2, 3, 4, 5] // Patient, Doctor, Date, Status, Notes, Actions
                }
            },
            {
                extend: 'csv',
                text: '<i class="fas fa-file-csv me-2"></i>' + prescriptionI18n.exportCsv,
                className: 'btn-csv',
                title: prescriptionI18n.prescriptionsReport,
                exportOptions: {
                    columns: [0, 1, 2, 3, 4, 5] // Patient, Doctor, Date, Status, Notes, Actions
                }
            }
        ],
        language: {
            processing: prescriptionI18n.dtProcessing,
            lengthMenu: "_MENU_",
            zeroRecords: prescriptionI18n.dtZeroRecords,
            info: prescriptionI18n.dtInfo,
            infoEmpty: prescriptionI18n.dtInfoEmpty,
            infoFiltered: prescriptionI18n.dtInfoFiltered,
            search: "",
            searchPlaceholder: prescriptionI18n.dtSearchPlaceholder,
            paginate: {
                first: prescriptionI18n.dtFirst,
                last: prescriptionI18n.dtLast,
                next: prescriptionI18n.dtNext,
                previous: prescriptionI18n.dtPrevious
            }
        },
        drawCallback: function(settings) {
            // Re-initialize tooltips if the function exists
            if (typeof $.fn.tooltip === 'function') {
                $('[data-toggle="tooltip"]').tooltip();
            }
            // Apply custom styling after each draw
            applyDataTablesStyling();
            if (permissions.prescriptions.view) {
                $('#prescriptionsTable tbody tr').addClass('cursor-pointer v3-hover-primary transition-colors duration-150');
            }
        }
    });

    if (permissions.prescriptions.view) {
        $('#prescriptionsTable tbody').on('click', 'tr', function(e) {
            if ($(e.target).closest('a, button, input, select, textarea, [onclick]').length) {
                return;
            }

            const rowData = prescriptionsTable.row(this).data();
            if (!rowData || !rowData.id) {
                return;
            }

            window.location.href = window.workflowContextUrl(`<?= base_url('prescription') ?>/${rowData.id}`);
        });
    }

    function applyDataTablesStyling() {
        $('.dataTables_length select').removeClass().addClass('v3-radius border v3-border-default v3-bg-panel px-3 py-2 text-sm font-bold v3-text-ink v3-elevation-none outline-none transition v3-focus-control focus:ring-4 v3-focus-control');
        $('.dataTables_filter input').removeClass().addClass('v3-radius border v3-border-default v3-bg-panel px-3 py-2 text-sm font-bold v3-text-ink v3-elevation-none outline-none transition placeholder:v3-text-muted v3-focus-control focus:ring-4 v3-focus-control');
        $('.dataTables_info').removeClass().addClass('flex items-center text-sm font-bold v3-text-muted');
        $('.dataTables_paginate .paginate_button').removeClass().addClass('mx-1 v3-radius border v3-border-default v3-bg-panel px-3 py-2 text-sm font-extrabold v3-text-ink v3-elevation-none transition v3-border-default v3-hover-primary v3-hover-text-primary focus:outline-none focus:ring-4 v3-focus-control');
        $('.dataTables_paginate .paginate_button.current').addClass('!v3-border-default !v3-bg-primary !v3-text-on-primary');
        $('.dataTables_paginate .paginate_button.disabled').addClass('cursor-not-allowed opacity-45 hover:!v3-border-default hover:!v3-bg-panel hover:!v3-text-muted');
        $('.dataTables_processing').removeClass().addClass('v3-radius border-0 v3-bg-primary px-4 py-3 text-sm font-extrabold v3-text-on-primary v3-elevation-none');
        $('.dataTables_length').addClass('order-1 flex items-center gap-3');
        $('.dataTables_filter').addClass('order-2 ms-auto flex items-center gap-3');
        $('.dataTables_info').addClass('order-1 flex items-center');
        $('.dataTables_paginate').addClass('order-2 ms-auto flex items-center gap-2');
        $('.dataTables_length label, .dataTables_filter label').addClass('text-sm font-extrabold v3-text-ink');
    }

    // Load statistics
    loadPrescriptionStatistics();

    // Search functionality
    if ($('#searchInput').length) {
        $('#searchInput').on('keyup', function() {
            prescriptionsTable.search(this.value).draw();
        });
    }

    // Status filter
    if ($('#statusFilter').length) {
        $('#statusFilter').on('change', function() {
            prescriptionsTable.draw();
        });
    }
});

// Load prescription statistics
function loadPrescriptionStatistics() {
    $.ajax({
        url: '<?= base_url('prescription/stats') ?>',
        type: 'GET',
        dataType: 'json',
        success: function(data) {
            $('#totalPrescriptions').text(data.total || 0);
            $('#activePrescriptions').text(data.active || 0);
            $('#expiredPrescriptions').text(data.expired || 0);
            $('#monthlyPrescriptions').text(data.monthly || 0);
        },
        error: function() {
            $('#totalPrescriptions').text('0');
            $('#activePrescriptions').text('0');
            $('#expiredPrescriptions').text('0');
            $('#monthlyPrescriptions').text('0');
        }
    });
}

// Export functions
function exportToPDF() {
    if (prescriptionsTable) {
        prescriptionsTable.button('.btn-pdf').trigger();
    }
}

function printPrescriptions() {
    if (prescriptionsTable) {
        prescriptionsTable.button('.btn-print').trigger();
    }
}

function exportToCSV() {
    if (prescriptionsTable) {
        prescriptionsTable.button('.btn-csv').trigger();
    }
}

// Delete prescription function
function deletePrescription(prescriptionId) {
    confirmDelete('<?= base_url('prescription') ?>/' + prescriptionId, prescriptionI18n.deleteConfirm);
}

// Permissions object (you'll need to populate this from your backend)
var permissions = {
    prescriptions: {
        view: true,
        edit: true,
        delete: true,
        create: true,
        print: true
    }
};
</script>

<?= $this->endSection() ?>
