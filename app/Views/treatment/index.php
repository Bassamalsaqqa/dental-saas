<?php include APPPATH . 'Views/layouts/partials/view_permissions.php'; helper('asset'); ?>
<?= $this->extend('layouts/main_auth') ?>

<?= $this->section('content') ?>
<?php $isRtl = !empty($is_rtl) || (service('request')->getLocale() === 'ar'); ?>

<link rel="stylesheet" href="<?= base_url('assets/css/v4-operations.css') ?>?v=<?= esc(asset_version('assets/css/v4-operations.css')) ?>">
<script defer src="<?= base_url('assets/js/v4-record-cards.js') ?>?v=<?= esc(asset_version('assets/js/v4-record-cards.js')) ?>"></script>
<div class="v4-clinical-registry"><div class="v4-clinical-main">
<header class="v4-page-heading"><div><h1><?= esc(lang('Treatment.treatment_management')) ?></h1><p><?= esc(lang('V4.treatmentHelp')) ?></p></div><div class="v4-page-actions"><?php if($v4Can('treatments','create')): ?><a id="newTreatmentModalTrigger" href="<?= base_url('treatment/create') ?>" data-treatment-create-modal-open class="v4-button v4-button--primary"><i class="fas fa-plus" aria-hidden="true"></i><?= esc(lang('Treatment.create_new_treatment')) ?></a><?php endif; ?></div></header>
<?php $acceptedQueue=is_array($accepted_treatment_queue??null)?$accepted_treatment_queue:[]; ?><?php if(!empty($acceptedQueue['count'])): ?><section class="v4-clinical-queue"><div><h2><?= esc(lang('Treatment.unscheduled_queue')) ?> <b><?= (int)$acceptedQueue['count'] ?></b></h2><p><?= esc(lang('Treatment.unscheduled_queue_help')) ?></p></div><details><summary><?= esc(lang('V4.reviewQueue')) ?></summary><div class="v4-treatment-queue"><?php foreach(($acceptedQueue['items']??[]) as $item): ?><article><a href="<?= esc(workflow_context_url('treatment/'.(int)$item['treatment_id'],'treatment')) ?>"><strong><?= esc($item['procedure_name']??'') ?></strong><span><?= esc(trim(($item['first_name']??'').' '.($item['last_name']??''))) ?></span></a><?php if($v4Can('appointments','create')): ?><a class="v4-button" href="<?= base_url('appointment/create?'.http_build_query(['patient_id'=>(int)$item['patient_id'],'clinical_procedure_id'=>(int)$item['procedure_id'],'appointment_type'=>'treatment','return_to'=>'treatment'])) ?>" data-appointment-create-modal-open data-patient-id="<?= (int)$item['patient_id'] ?>" data-clinical-procedure-id="<?= (int)$item['procedure_id'] ?>" data-appointment-type="treatment"><?= esc(lang('Treatment.schedule_accepted_treatment')) ?></a><?php endif; ?></article><?php endforeach; ?></div></details></section><?php endif; ?><details class="v4-clinical-summary"><summary><?= esc(lang('V4.recordSummary')) ?></summary><dl><div><dt><?= lang('Treatment.total_treatments') ?></dt><dd id="totalTreatments">&mdash;</dd></div><div><dt><?= lang('Treatment.active_treatments') ?></dt><dd id="activeTreatments">&mdash;</dd></div><div><dt><?= lang('Treatment.completed_treatments') ?></dt><dd id="completedTreatments">&mdash;</dd></div><div><dt><?= lang('Treatment.this_month') ?></dt><dd id="monthlyTreatments">&mdash;</dd></div></dl></details><section id="tableView" class="v4-surface v4-ledger"><header class="v4-ledger-header"><h2><?= esc(lang('Treatment.treatment_management')) ?></h2><div class="v4-record-views"><details class="v4-action-menu"><summary class="v4-button v4-button--small"><?= esc(lang('V4.exportRecords')) ?></summary><div><button type="button" onclick="exportToPDF()">PDF</button><button type="button" onclick="printTreatments()"><?= esc(lang('Finance.print')) ?></button><button type="button" onclick="exportToCSV()">CSV</button></div></details></div></header><div class="v4-ledger-data" data-v4-records="treatment" data-v4-table="treatmentsTable"><table id="treatmentsTable"><thead><tr><th><?= lang('Treatment.patient') ?></th><th><?= lang('Treatment.treatment') ?></th><th><?= lang('Treatment.status') ?></th><th><?= lang('Treatment.start_date') ?></th><th><?= lang('Treatment.actions') ?></th></tr></thead><tbody></tbody></table><div class="v4-mobile-records" data-v4-record-cards aria-live="polite"></div>
            </div>
        </section>
    </div>
</div>

<?= view('treatment/partials/quick_create_modal', [
    'isRtl' => $isRtl,
    'return_to' => workflow_current_target() ?? 'treatment',
    'contextual' => false,
    'patients' => [],
    'treatment_types' => $treatment_types ?? [],
    'inventory_items' => $inventory_items ?? [],
    'clinic_providers' => $clinic_providers ?? [],
    'validation' => $validation ?? null,
    'selected_examination_id' => null,
]) ?>

<!-- DataTables JS -->
<script>
// Global variables
let treatmentsTable;

$(document).ready(function() {
    // Initialize DataTable with server-side processing
    treatmentsTable = $('#treatmentsTable').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: '<?= base_url('treatment/get-treatments') ?>',
            type: 'GET',
            data: function(d) {
                d.status = $('#statusFilter').length ? $('#statusFilter').val() : '';
                d.search_term = $('#searchInput').length ? $('#searchInput').val() : '';
            }
        },
        columns: [
            {
                data: 'patient_name',
                name: 'patient_name',
                render: function(data, type, row) {
                    const gender = String(row.gender || 'other').toLowerCase();
                    const avatarMap = {
                        male: { icon: 'fas fa-person', color: 'text-blue-700' },
                        female: { icon: 'fas fa-person-dress', color: 'text-rose-700' },
                        other: { icon: 'fas fa-user', color: 'text-slate-600' }
                    };
                    const avatar = avatarMap[gender] || avatarMap.other;
                    const patientNumber = row.patient_number || '<?= esc(lang('Treatment.no_id'), 'js') ?>';
                    return `
                        <div class="flex items-center gap-3">
                            <span class="inline-flex h-7 w-7 flex-shrink-0 items-center justify-center">
                                <i class="${avatar.icon} ${avatar.color} text-2xl"></i>
                            </span>
                            <div class="min-w-0">
                                <div class="truncate text-sm font-extrabold text-slate-950">${data || '<?= esc(lang('Treatment.na'), 'js') ?>'}</div>
                                <div class="truncate text-xs font-bold text-slate-500">${patientNumber}</div>
                            </div>
                        </div>
                    `;
                }
            },
            {
                data: 'treatment_name',
                name: 'treatment_name',
                render: function(data, type, row) {
                    return `
                        <div class="flex items-center gap-3">
                            <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-pink-100 text-pink-700">
                                <i class="fas fa-procedures text-sm"></i>
                            </div>
                            <div>
                                <div class="text-sm font-extrabold text-slate-950">${data}</div>
                                <div class="text-xs font-bold text-slate-500"><?= esc(lang('Treatment.treatment'), 'js') ?></div>
                            </div>
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
                        'completed': 'border-blue-200 bg-gradient-to-r from-blue-100 to-cyan-100 text-blue-800',
                        'cancelled': 'border-slate-200 bg-gradient-to-r from-slate-100 to-gray-100 text-slate-800',
                        'on_hold': 'border-amber-200 bg-gradient-to-r from-amber-100 to-yellow-100 text-amber-800'
                    };
                    const statusIcons = {
                        'active': 'fas fa-play-circle',
                        'completed': 'fas fa-check-circle',
                        'cancelled': 'fas fa-times-circle',
                        'on_hold': 'fas fa-pause-circle'
                    };
                    const colorClass = statusColors[data] || 'border-slate-200 bg-gradient-to-r from-slate-100 to-gray-100 text-slate-800';
                    const iconClass = statusIcons[data] || 'fas fa-question-circle';
                    const localizedStatus = {
                        active: '<?= esc(lang('Treatment.status_values.active'), 'js') ?>',
                        completed: '<?= esc(lang('Treatment.status_values.completed'), 'js') ?>',
                        cancelled: '<?= esc(lang('Treatment.status_values.cancelled'), 'js') ?>',
                        on_hold: '<?= esc(lang('Treatment.status_values.on_hold'), 'js') ?>'
                    };

                    return `
                        <span class="inline-flex items-center rounded-xl border px-3 py-1.5 text-xs font-extrabold ${colorClass}">
                            <i class="${iconClass} me-2"></i>
                            ${localizedStatus[data] || (data.charAt(0).toUpperCase() + data.slice(1))}
                        </span>
                    `;
                }
            },
            {
                data: 'start_date',
                name: 'start_date',
                render: function(data, type, row) {
                    const date = new Date(data);
                    return `
                        <div class="flex items-center gap-2">
                            <i class="fas fa-calendar text-pink-700 text-xs"></i>
                            <span class="text-sm font-bold text-slate-900">${row.start_date_formatted || date.toLocaleDateString()}</span>
                        </div>
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
                    if (permissions.treatments.view) {
                        actions += `
                            <a href="<?= base_url('treatment') ?>/${data}"
                               class="rounded-xl p-2 text-pink-700 transition-all duration-200 hover:scale-105 hover:bg-pink-50 hover:text-pink-800" title="<?= esc(lang('Treatment.view_details'), 'attr') ?>">
                                <i class="fas fa-eye"></i>
                            </a>`;
                    }

                    // Edit button
                    if (permissions.treatments.edit) {
                        actions += `
                            <a href="<?= base_url('treatment') ?>/${data}/edit"
                               class="rounded-xl p-2 text-sky-700 transition-all duration-200 hover:scale-105 hover:bg-sky-50 hover:text-sky-800" title="<?= esc(lang('Treatment.edit_treatment'), 'attr') ?>">
                                <i class="fas fa-edit"></i>
                            </a>`;
                    }

                    // Delete button
                    if (permissions.treatments.delete) {
                        actions += `
                            <button onclick="deleteTreatment(${data})"
                                    class="rounded-xl p-2 text-red-700 transition-all duration-200 hover:scale-105 hover:bg-red-50 hover:text-red-800" title="<?= esc(lang('Treatment.delete_treatment'), 'attr') ?>">
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
                text: '<i class="fas fa-file-pdf me-2"></i><?= esc(lang('Treatment.pdf'), 'js') ?>',
                className: 'btn-pdf',
                title: '<?= esc(lang('Treatment.treatments_report'), 'js') ?>',
                orientation: 'landscape',
                pageSize: 'A4',
                exportOptions: {
                    columns: [0, 1, 2, 3, 4] // Patient, Treatment, Status, Date, Actions
                }
            },
            {
                extend: 'print',
                text: '<i class="fas fa-print me-2"></i><?= esc(lang('Treatment.print'), 'js') ?>',
                className: 'btn-print',
                title: '<?= esc(lang('Treatment.treatments_report'), 'js') ?>',
                exportOptions: {
                    columns: [0, 1, 2, 3, 4] // Patient, Treatment, Status, Date, Actions
                }
            },
            {
                extend: 'csv',
                text: '<i class="fas fa-file-csv me-2"></i><?= esc(lang('Treatment.csv'), 'js') ?>',
                className: 'btn-csv',
                title: '<?= esc(lang('Treatment.treatments_report'), 'js') ?>',
                exportOptions: {
                    columns: [0, 1, 2, 3, 4] // Patient, Treatment, Status, Date, Actions
                }
            }
        ],
        language: {
            processing: "<?= esc(lang('Treatment.loading_treatments'), 'js') ?>",
            lengthMenu: "_MENU_",
            zeroRecords: "<?= esc(lang('Treatment.no_treatments_found'), 'js') ?>",
            info: "<?= esc(lang('Treatment.datatable_info'), 'js') ?>",
            infoEmpty: "<?= esc(lang('Treatment.no_treatments_available'), 'js') ?>",
            infoFiltered: "<?= esc(lang('Treatment.datatable_info_filtered'), 'js') ?>",
            search: "",
            searchPlaceholder: "<?= esc(lang('Treatment.search_treatments_placeholder'), 'js') ?>",
            paginate: {
                first: "<?= esc(lang('Treatment.first'), 'js') ?>",
                last: "<?= esc(lang('Treatment.last'), 'js') ?>",
                next: "<?= esc(lang('Treatment.next'), 'js') ?>",
                previous: "<?= esc(lang('Treatment.previous'), 'js') ?>"
            }
        },
        drawCallback: function(settings) {
            // Re-initialize tooltips if the function exists
            if (typeof $.fn.tooltip === 'function') {
                $('[data-toggle="tooltip"]').tooltip();
            }
            // Apply custom styling after each draw
            applyDataTablesStyling();
        }
    });

    function applyDataTablesStyling() { /* V4 components own layout and state styling. */ }

    if (permissions.treatments.view) {
        $('#treatmentsTable tbody')
            .on('click', 'tr', function (event) {
                const $target = $(event.target);
                if ($target.closest('a, button').length) {
                    return;
                }

                const rowData = treatmentsTable.row(this).data();
                if (!rowData || !rowData.id) {
                    return;
                }

                window.location.href = window.workflowContextUrl(`<?= base_url('treatment') ?>/${rowData.id}`);
            })
            .on('mouseenter', 'tr', function () {
                $(this).addClass('cursor-pointer hover:bg-pink-50/50 transition-colors duration-150');
            });
    }

    // Load statistics
    loadTreatmentStatistics();

    // Search functionality
    if ($('#searchInput').length) {
        $('#searchInput').on('keyup', function() {
            treatmentsTable.search(this.value).draw();
        });
    }

    // Status filter
    if ($('#statusFilter').length) {
        $('#statusFilter').on('change', function() {
            treatmentsTable.draw();
        });
    }
});

// Load treatment statistics
function loadTreatmentStatistics() {
    $.ajax({
        url: '<?= base_url('treatment/stats') ?>',
        type: 'GET',
        dataType: 'json',
        success: function(data) {
            $('#totalTreatments').text(data.total || 0);
            $('#activeTreatments').text(data.active || 0);
            $('#completedTreatments').text(data.completed || 0);
                $('#monthlyTreatments').text(data.monthly_treatments || 0);
        },
        error: function() {
            $('#totalTreatments').text('0');
            $('#activeTreatments').text('0');
            $('#completedTreatments').text('0');
            $('#monthlyTreatments').text('0');
        }
    });
}

// Export functions
function exportToPDF() {
    if (treatmentsTable) {
        treatmentsTable.button('.btn-pdf').trigger();
    }
}

function printTreatments() {
    if (treatmentsTable) {
        treatmentsTable.button('.btn-print').trigger();
    }
}

function exportToCSV() {
    if (treatmentsTable) {
        treatmentsTable.button('.btn-csv').trigger();
    }
}

// Delete treatment function
function deleteTreatment(treatmentId) {
    if (confirm('<?= esc(lang('Treatment.confirm_delete_treatment'), 'js') ?>')) {
        // Create form for DELETE request
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = '<?= base_url('treatment') ?>/' + treatmentId;

        // Add method override for DELETE
        const methodInput = document.createElement('input');
        methodInput.type = 'hidden';
        methodInput.name = '_method';
        methodInput.value = 'DELETE';
        form.appendChild(methodInput);

        // Add CSRF token
        const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
        const csrfInput = document.createElement('input');
        csrfInput.type = 'hidden';
        csrfInput.name = '<?= csrf_token() ?>';
        csrfInput.value = csrfToken;
        form.appendChild(csrfInput);

        document.body.appendChild(form);
        form.submit();
    }
}

// Permissions object (you'll need to populate this from your backend)
var permissions = {treatments: <?= json_encode(array_combine(['view','edit','delete','create'],array_map(static fn($action)=>(bool)has_permission('treatments',$action),['view','edit','delete','create']))) ?>};
</script>

<?= $this->endSection() ?>
