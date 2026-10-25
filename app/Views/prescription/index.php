<?php include APPPATH . 'Views/layouts/partials/view_permissions.php'; helper('asset'); ?>
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

<link rel="stylesheet" href="<?= base_url('assets/css/v4-operations.css') ?>?v=<?= esc(asset_version('assets/css/v4-operations.css')) ?>">
<script defer src="<?= base_url('assets/js/v4-record-cards.js') ?>?v=<?= esc(asset_version('assets/js/v4-record-cards.js')) ?>"></script>
<div class="v4-clinical-registry"><div class="v4-clinical-main">
<header class="v4-page-heading"><div><h1><?= esc(lang('Prescription.management_title')) ?></h1><p><?= esc(lang('V4.prescriptionHelp')) ?></p></div><div class="v4-page-actions"><?php if($v4Can('prescriptions','create')): ?><a id="newPrescriptionModalTrigger" href="<?= base_url('prescription/create') ?>" data-prescription-create-modal-open class="v4-button v4-button--primary"><i class="fas fa-plus" aria-hidden="true"></i><?= esc(lang('Prescription.new_prescription')) ?></a><?php endif; ?></div></header>
<details class="v4-clinical-summary"><summary><?= esc(lang('V4.recordSummary')) ?></summary><dl><div><dt><?= lang('Prescription.total_prescriptions') ?></dt><dd id="totalPrescriptions">&mdash;</dd></div><div><dt><?= lang('Prescription.active_prescriptions') ?></dt><dd id="activePrescriptions">&mdash;</dd></div><div><dt><?= lang('Prescription.expired_prescriptions') ?></dt><dd id="expiredPrescriptions">&mdash;</dd></div><div><dt><?= lang('Prescription.this_month') ?></dt><dd id="monthlyPrescriptions">&mdash;</dd></div></dl></details><section id="tableView" class="v4-surface v4-ledger"><header class="v4-ledger-header"><h2><?= esc(lang('Prescription.management_title')) ?></h2><div class="v4-record-views"><details class="v4-action-menu"><summary class="v4-button v4-button--small"><?= esc(lang('V4.exportRecords')) ?></summary><div><button type="button" onclick="exportToPDF()">PDF</button><button type="button" onclick="printPrescriptions()"><?= esc(lang('Finance.print')) ?></button><button type="button" onclick="exportToCSV()">CSV</button></div></details></div></header><div class="v4-ledger-data" data-v4-records="prescription" data-v4-table="prescriptionsTable"><table id="prescriptionsTable"><thead><tr><th><?= lang('Prescription.prescription_id') ?></th><th><?= lang('Prescription.patient') ?></th><th><?= lang('Prescription.medicines') ?></th><th><?= lang('Prescription.date_col') ?></th><th><?= lang('Prescription.status') ?></th><th><?= lang('Prescription.actions') ?></th></tr></thead><tbody id="prescriptionsTableBody"></tbody></table><div class="v4-mobile-records" data-v4-record-cards aria-live="polite"></div>
            </div>
        </section>
    </div>
</div>

<?php if (has_permission('prescriptions', 'create')): ?>
    <div id="newPrescriptionModal" data-confirm-discard="<?= esc(lang('V4.discardEntry'),'attr') ?>" class="prescription-create-modal v4-entry-modal hidden" role="dialog" aria-modal="true" aria-hidden="true" aria-labelledby="newPrescriptionModalTitle" dir="<?= $isRtl ? 'rtl' : 'ltr' ?>">
        <div class="prescription-create-modal__backdrop v4-entry-modal__backdrop" data-prescription-create-modal-close></div>
        <div class="prescription-create-modal__shell v4-entry-modal__shell" data-prescription-create-modal-close>
            <form id="newPrescriptionQuickCreateForm" action="<?= base_url('prescription/store') ?>" method="POST" class="prescription-create-modal__panel v4-entry-modal__panel v4-form" data-prescription-create-form data-select2-dropdown-parent onclick="event.stopPropagation()">
                <?= csrf_field() ?>
                <input type="hidden" name="return_to" value="<?= esc($returnToContext) ?>">
                <input type="hidden" name="source_modal" value="prescription_quick_create">

<header class="v4-modal-header"><div><h2 id="newPrescriptionModalTitle"><?= esc(lang('Prescription.new_prescription')) ?></h2><a href="<?= workflow_context_url('prescription/create', $returnToContext) ?>" class="v4-text-link">
                                <?= esc(lang('UI.openFullPage')) ?>
                            </a></div><button type="button" class="v4-icon-button" data-prescription-create-modal-close aria-label="<?= esc(lang('UI.closeDrawer')) ?>"><i class="fas fa-times" aria-hidden="true"></i></button></header>
                <div class="v4-modal-body">
                    <?php if (session('error') && $shouldOpenPrescriptionCreateModal): ?>
                        <div class="rounded-xl border border-red-200 bg-red-50 p-4 text-sm text-red-700">
                            <?= esc(session('error')) ?>
                        </div>
                    <?php endif; ?>
                    <?php if (session('errors') && $shouldOpenPrescriptionCreateModal): ?>
                        <div class="rounded-xl border border-red-200 bg-red-50 p-4 text-sm text-red-700">
                            <?php foreach ((array) session('errors') as $error): ?>
                                <div><?= esc($error) ?></div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>

                    <div class="v4-form-section">
                        <?= view('prescription/partials/form_fields', [
                            'selected_patient' => null,
                            'is_rtl' => $isRtl,
                            'id_prefix' => 'prescriptionQuick',
                        ]) ?>
                    </div>
                </div>

                <div class="v4-modal-footer">
                    <button type="button" data-prescription-create-modal-close class="v4-button">
                        <?= esc(lang('Prescription.cancel')) ?>
                    </button>
                    <button type="submit" class="v4-button v4-button--primary">
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
        male: { icon: 'fas fa-person', color: 'text-blue-700' },
        female: { icon: 'fas fa-person-dress', color: 'text-rose-700' },
        other: { icon: 'fas fa-user', color: 'text-slate-600' }
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

function openNewPrescriptionModal(trigger){const modal=mountNewPrescriptionModal();if(!modal)return;window.AppModal.open(modal,{trigger:trigger||document.activeElement,onOpen:initializeNewPrescriptionModalContent,focusSelector:'.select2-selection,input:not([type=hidden]):not([disabled]),button'});}

function closeNewPrescriptionModal(){const modal=document.getElementById('newPrescriptionModal');if(!window.AppModal.isOpen(modal))return;window.AppModal.close(modal,{onReset:()=>{const form=modal.querySelector('[data-prescription-create-form]');if(form){form.reset();form.prescriptionReset?.();form.querySelectorAll('select').forEach(select=>{select.dispatchEvent(new Event('change',{bubbles:true}));if(window.jQuery)jQuery(select).trigger('change');});}}});}

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
    if (event.target.closest?.('.select2-search')) return;
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
                    td.className = 'px-6 py-4 text-center text-red-500';

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
                            <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-teal-100 text-teal-700">
                                <i class="fas fa-prescription-bottle-alt text-sm"></i>
                            </div>
                            <div>
                                <div class="text-sm font-extrabold text-slate-950">${data}</div>
                                <div class="text-xs font-bold text-slate-500">${prescriptionI18n.prescriptionId}</div>
                            </div>
                            </div>
                    `;
                    if (permissions.prescriptions.view) {
                        return `
                            <a href="<?= base_url('prescription') ?>/${row.id}" class="block cursor-pointer rounded-xl transition-colors duration-200 hover:bg-teal-50/70">
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
                                    <div class="text-sm font-extrabold text-slate-950">${data}</div>
                                    <div class="text-xs font-bold text-slate-500">${row.patient_number || row.phone || prescriptionI18n.noPhone}</div>
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
                                    <i class="fas fa-pills text-teal-700 text-xs"></i>
                                    <span class="text-sm font-bold text-slate-900">${medicines.length} ${medicines.length > 1 ? prescriptionI18n.medicinePlural : prescriptionI18n.medicineSingular}</span>
                                </div>
                            `;
                        }
                    } catch (e) {
                        // If not JSON, treat as single medicine
                        if (data) {
                            return `
                                <div class="flex items-center gap-2">
                                    <i class="fas fa-pills text-teal-700 text-xs"></i>
                                    <span class="text-sm font-bold text-slate-900">${prescriptionI18n.oneMedicine}</span>
            </div>
        `;
                        }
                    }
                    return `<span class="text-sm font-bold text-slate-400">${prescriptionI18n.noMedicines}</span>`;
                }
            },
            {
                data: 'prescribed_date',
                name: 'prescribed_date',
                render: function(data, type, row) {
                    return `
                        <div class="flex items-center gap-2">
                            <i class="fas fa-calendar-alt text-teal-700 text-xs"></i>
                            <span class="text-sm font-bold text-slate-900">${row.prescribed_date_formatted || data}</span>
                        </div>
                    `;
                }
            },
            {
                data: 'status',
                name: 'status',
                render: function(data, type, row) {
                    const statusColors = {
                        'active': 'border-emerald-200 bg-gradient-to-r from-emerald-100 to-green-100 text-emerald-800',
                        'expired': 'border-red-200 bg-gradient-to-r from-red-100 to-rose-100 text-red-800',
                        'cancelled': 'border-slate-200 bg-gradient-to-r from-slate-100 to-gray-100 text-slate-800',
                        'pending': 'border-amber-200 bg-gradient-to-r from-amber-100 to-yellow-100 text-amber-800'
                    };
                    const statusIcons = {
                        'active': 'fas fa-check-circle',
                        'expired': 'fas fa-times-circle',
                        'cancelled': 'fas fa-ban',
                        'pending': 'fas fa-clock'
                    };
                    const colorClass = statusColors[data] || 'border-slate-200 bg-gradient-to-r from-slate-100 to-gray-100 text-slate-800';
                    const iconClass = statusIcons[data] || 'fas fa-question-circle';

                    return `
                        <span class="inline-flex items-center rounded-xl border px-3 py-1.5 text-xs font-extrabold ${colorClass}">
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
                               class="rounded-xl p-2 text-teal-700 transition-all duration-200 hover:scale-105 hover:bg-teal-50 hover:text-teal-800" title="${prescriptionI18n.viewDetails}">
                                <i class="fas fa-eye"></i>
                            </a>`;
                    }

                    // Print button
                    if (permissions.prescriptions.print) {
                        actions += `
                            <a href="<?= base_url('prescription') ?>/${data}/print"
                               class="rounded-xl p-2 text-emerald-700 transition-all duration-200 hover:scale-105 hover:bg-emerald-50 hover:text-emerald-800" title="${prescriptionI18n.printPrescription}">
                                <i class="fas fa-print"></i>
                            </a>`;
                    }

                    // Edit button
                    if (permissions.prescriptions.edit) {
                        actions += `
                            <a href="<?= base_url('prescription') ?>/${data}/edit"
                               class="rounded-xl p-2 text-sky-700 transition-all duration-200 hover:scale-105 hover:bg-sky-50 hover:text-sky-800" title="${prescriptionI18n.editPrescription}">
                                <i class="fas fa-edit"></i>
                            </a>`;
                    }

                    // Delete button
                    if (permissions.prescriptions.delete) {
                        actions += `
                            <button onclick="deletePrescription(${data})"
                                    class="rounded-xl p-2 text-red-700 transition-all duration-200 hover:scale-105 hover:bg-red-50 hover:text-red-800" title="${prescriptionI18n.deletePrescription}">
                                <i class="fas fa-trash"></i>
                            </button>`;
                    }

                    actions += '</div>';
                    return actions;
                }
            }
        ],
        dom: '<"v4-ledger-tools"lf>rt<"v4-ledger-footer"ip>B',
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
                $('#prescriptionsTable tbody tr').addClass('cursor-pointer hover:bg-teal-50/40 transition-colors duration-150');
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

    function applyDataTablesStyling() { /* V4 components own layout and state styling. */ }

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
var permissions = {prescriptions: <?= json_encode(array_combine(['view','edit','delete','create','print'],array_map(static fn($action)=>(bool)has_permission('prescriptions',$action),['view','edit','delete','create','print']))) ?>};
</script>

<?= $this->endSection() ?>
