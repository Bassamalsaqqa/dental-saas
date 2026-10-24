<?= $this->extend('layouts/main_auth') ?>

<?= $this->section('content') ?>
<div class="v3-operational-view">
<?php $isRtl = !empty($is_rtl) || (service('request')->getLocale() === 'ar'); ?>

<!-- DataTables CSS -->
<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.13.7/css/jquery.dataTables.min.css">
<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/buttons/2.4.2/css/buttons.dataTables.min.css">

<!-- Custom DataTables Styling -->


<div class="treatments-page min-h-screen" dir="<?= $isRtl ? 'rtl' : 'ltr' ?>">
    <div class="w-full px-3 py-4 sm:px-5 sm:py-5">
        <section class="mb-5 overflow-hidden rounded-[1.55rem]">
            <div class="grid gap-5 sm:p-6 xl:grid-cols-[minmax(0,1fr)_auto] xl:items-center">
                <div class="flex min-w-0 items-start gap-4">
                    <span class="inline-flex h-14 w-14 shrink-0 items-center justify-center border">
                        <i class="fas fa-procedures text-xl"></i>
                    </span>
                    <div class="min-w-0">
                        <p class="text-xs uppercase tracking-[0.22em] text-pink-100"><?= lang('Treatment.treatment_registry') ?></p>
                        <h1 class="mt-1 v3-section-title leading-tight v3-title"><?= lang('Treatment.treatment_management') ?></h1>
                        <p class="mt-2 max-w-2xl text-sm font-semibold leading-6 text-pink-50/88"><?= lang('Treatment.treatment_management_subtitle') ?></p>
                    </div>
                </div>
                <?php if (has_permission('treatments', 'create')): ?>
                    <a href="<?= base_url('treatment/create') ?>" id="newTreatmentModalTrigger" data-treatment-create-modal-open class="inline-flex items-center justify-center gap-2 px-5 py-3 text-sm text-pink-800 transition hover:-translate-y-0.5 hover:bg-pink-50 focus:outline-none focus:ring-4 v3-focus-control v3-btn v3-btn--secondary">
                        <i class="fas fa-plus"></i>
                        <?= lang('Treatment.new_treatment') ?>
                    </a>
                <?php endif; ?>
            </div>
        </section>

        <section class="mb-5 grid grid-cols-1 gap-3 sm:grid-cols-2 xl:grid-cols-4">
            <article class="p-4">
                <div class="flex items-center justify-between gap-3">
                    <div>
                        <p class="text-xs uppercase tracking-wide text-pink-100"><?= lang('Treatment.total_treatments') ?></p>
                        <p class="mt-2 v3-title" id="totalTreatments"><i class="fas fa-spinner fa-spin text-lg"></i></p>
                    </div>
                    <span class="inline-flex h-11 w-11 items-center justify-center border"><i class="fas fa-procedures"></i></span>
                </div>
            </article>
            <article class="p-4">
                <div class="flex items-center justify-between gap-3">
                    <div>
                        <p class="text-xs uppercase tracking-wide v3-text-primary"><?= lang('Treatment.active_treatments') ?></p>
                        <p class="mt-2 v3-title" id="activeTreatments"><i class="fas fa-spinner fa-spin text-lg"></i></p>
                    </div>
                    <span class="inline-flex h-11 w-11 items-center justify-center border"><i class="fas fa-play-circle"></i></span>
                </div>
            </article>
            <article class="p-4">
                <div class="flex items-center justify-between gap-3">
                    <div>
                        <p class="text-xs uppercase tracking-wide"><?= lang('Treatment.completed_treatments') ?></p>
                        <p class="mt-2 v3-title" id="completedTreatments"><i class="fas fa-spinner fa-spin text-lg"></i></p>
                    </div>
                    <span class="inline-flex h-11 w-11 items-center justify-center border"><i class="fas fa-check-double"></i></span>
                </div>
            </article>
            <article class="p-4">
                <div class="flex items-center justify-between gap-3">
                    <div>
                        <p class="text-xs uppercase tracking-wide"><?= lang('Treatment.this_month') ?></p>
                        <p class="mt-2 v3-title" id="monthlyTreatments"><i class="fas fa-spinner fa-spin text-lg"></i></p>
                    </div>
                    <span class="inline-flex h-11 w-11 items-center justify-center border"><i class="fas fa-calendar-day"></i></span>
                </div>
            </article>
        </section>

        <?php $acceptedQueue = is_array($accepted_treatment_queue ?? null) ? $accepted_treatment_queue : ['count' => 0, 'items' => []]; ?>
        <?php if ((int) ($acceptedQueue['count'] ?? 0) > 0): ?>
            <section class="mb-5 overflow-hidden rounded-[1.55rem] p-1">
                <div class="rounded-[1.35rem] p-4 sm:p-5">
                    <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
                        <div class="flex items-start gap-3">
                            <span class="inline-flex h-11 w-11 shrink-0 items-center justify-center"><i class="fas fa-calendar-plus"></i></span>
                            <div>
                                <div class="flex flex-wrap items-center gap-2">
                                    <h2 class="text-lg"><?= esc(lang('Treatment.unscheduled_queue')) ?></h2>
                                    <span class="px-2.5 py-1 text-xs"><?= (int) ($acceptedQueue['count'] ?? 0) ?></span>
                                </div>
                                <p class="mt-1 text-sm font-semibold"><?= esc(lang('Treatment.unscheduled_queue_help')) ?></p>
                            </div>
                        </div>
                    </div>
                    <div class="mt-4 grid gap-3 md:grid-cols-2 xl:grid-cols-3">
                        <?php foreach (($acceptedQueue['items'] ?? []) as $item): ?>
                            <?php $patientName = trim((string) ($item['first_name'] ?? '') . ' ' . (string) ($item['last_name'] ?? '')); ?>
                            <article class="flex items-center justify-between gap-3 p-3 border">
                                <a href="<?= esc(workflow_context_url('treatment/' . (int) $item['treatment_id'], 'treatment')) ?>" class="min-w-0">
                                    <p class="truncate text-sm"><?= esc($item['procedure_name'] ?? '') ?></p>
                                    <p class="mt-1 truncate text-xs font-bold"><?= esc($patientName) ?></p>
                                </a>
                                <?php if (has_permission('appointments', 'create')): ?>
                                    <a href="<?= base_url('appointment/create?' . http_build_query(['patient_id' => (int) $item['patient_id'], 'clinical_procedure_id' => (int) $item['procedure_id'], 'appointment_type' => 'treatment', 'return_to' => 'treatment'])) ?>"
                                       data-appointment-create-modal-open data-patient-id="<?= (int) $item['patient_id'] ?>" data-clinical-procedure-id="<?= (int) $item['procedure_id'] ?>" data-appointment-type="treatment"
                                       class="inline-flex h-10 w-10 shrink-0 items-center justify-center transition" title="<?= esc(lang('Treatment.schedule_accepted_treatment'), 'attr') ?>">
                                        <i class="fas fa-calendar-plus"></i>
                                    </a>
                                <?php endif; ?>
                            </article>
                        <?php endforeach; ?>
                    </div>
                </div>
            </section>
        <?php endif; ?>

        <section id="tableView" class="overflow-hidden rounded-[1.55rem] border">
            <div class="border-b px-4 py-4 sm:px-6">
                <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                    <div class="flex items-center gap-3">
                        <span class="inline-flex h-10 w-10 items-center justify-center bg-pink-100 text-pink-700">
                            <i class="fas fa-list-check"></i>
                        </span>
                        <div>
                            <h3 class="text-lg"><?= lang('Treatment.treatment_registry') ?></h3>
                            <p class="mt-1 text-xs font-bold"><?= lang('Treatment.treatment_management_subtitle') ?></p>
                        </div>
                    </div>
                    <div class="flex flex-wrap items-center gap-2">
                        <button onclick="exportToPDF()" class="inline-flex items-center gap-2 border px-3 py-2 text-sm font-extrabold transition v3-hover-danger v3-hover-text-danger v3-btn v3-btn--secondary">
                            <i class="fas fa-file-pdf v3-text-danger"></i>
                            <?= lang('Treatment.pdf') ?>
                        </button>
                        <button onclick="printTreatments()" class="inline-flex items-center gap-2 border px-3 py-2 text-sm font-extrabold transition v3-btn v3-btn--secondary">
                            <i class="fas fa-print"></i>
                            <?= lang('Treatment.print') ?>
                        </button>
                        <button onclick="exportToCSV()" class="inline-flex items-center gap-2 border px-3 py-2 text-sm font-extrabold transition v3-hover-primary v3-hover-text-primary v3-btn v3-btn--secondary">
                            <i class="fas fa-file-csv v3-text-primary"></i>
                            <?= lang('Treatment.csv') ?>
                        </button>
                    </div>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table id="treatmentsTable" class="w-full min-w-full">
                    <thead>
                        <tr>
                            <th class="px-6 py-4 text-start text-xs uppercase tracking-wider">
                                <div class="flex items-center gap-2">
                                    <i class="fas fa-user text-pink-700"></i>
                                    <span><?= lang('Treatment.patient') ?></span>
                                </div>
                            </th>
                            <th class="px-6 py-4 text-start text-xs uppercase tracking-wider">
                                <div class="flex items-center gap-2">
                                    <i class="fas fa-procedures text-pink-700"></i>
                                    <span><?= lang('Treatment.treatment') ?></span>
                                </div>
                            </th>
                            <th class="px-6 py-4 text-start text-xs uppercase tracking-wider">
                                <div class="flex items-center gap-2">
                                    <i class="fas fa-info-circle text-pink-700"></i>
                                    <span><?= lang('Treatment.status') ?></span>
                                </div>
                            </th>
                            <th class="px-6 py-4 text-start text-xs uppercase tracking-wider">
                                <div class="flex items-center gap-2">
                                    <i class="fas fa-calendar text-pink-700"></i>
                                    <span><?= lang('Treatment.start_date') ?></span>
                                </div>
                            </th>
                            <th class="px-6 py-4 text-start text-xs uppercase tracking-wider">
                                <div class="flex items-center gap-2">
                                    <i class="fas fa-cogs text-pink-700"></i>
                                    <span><?= lang('Treatment.actions') ?></span>
                                </div>
                            </th>
                        </tr>
                    </thead>
                    <tbody class="divide-y v3-border-default">
                        <!-- Data will be loaded via DataTables -->
                    </tbody>
                </table>
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
<script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/dataTables.buttons.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/pdfmake.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/vfs_fonts.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.html5.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.print.min.js"></script>

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
                        male: { icon: 'fas fa-person', color: 'v3-text-primary' },
                        female: { icon: 'fas fa-person-dress', color: 'v3-text-danger' },
                        other: { icon: 'fas fa-user', color: 'v3-text-muted' }
                    };
                    const avatar = avatarMap[gender] || avatarMap.other;
                    const patientNumber = row.patient_number || '<?= esc(lang('Treatment.no_id'), 'js') ?>';
                    return `
                        <div class="flex items-center gap-3">
                            <span class="inline-flex h-7 w-7 flex-shrink-0 items-center justify-center">
                                <i class="${avatar.icon} ${avatar.color} text-2xl"></i>
                            </span>
                            <div class="min-w-0">
                                <div class="truncate text-sm font-extrabold v3-text-ink">${data || '<?= esc(lang('Treatment.na'), 'js') ?>'}</div>
                                <div class="truncate text-xs font-bold v3-text-muted">${patientNumber}</div>
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
                            <div class="flex h-10 w-10 items-center justify-center v3-radius bg-pink-100 text-pink-700">
                                <i class="fas fa-procedures text-sm"></i>
                            </div>
                            <div>
                                <div class="text-sm font-extrabold v3-text-ink">${data}</div>
                                <div class="text-xs font-bold v3-text-muted"><?= esc(lang('Treatment.treatment'), 'js') ?></div>
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
                        'active': 'v3-border-default bg-gradient-to-r from-emerald-100 to-green-100 v3-text-primary',
                        'completed': 'v3-border-default bg-gradient-to-r from-blue-100 to-cyan-100 v3-text-primary',
                        'cancelled': 'v3-border-default bg-gradient-to-r from-slate-100 to-gray-100 v3-text-ink',
                        'on_hold': 'v3-border-default bg-gradient-to-r from-amber-100 to-yellow-100 v3-text-warning'
                    };
                    const statusIcons = {
                        'active': 'fas fa-play-circle',
                        'completed': 'fas fa-check-circle',
                        'cancelled': 'fas fa-times-circle',
                        'on_hold': 'fas fa-pause-circle'
                    };
                    const colorClass = statusColors[data] || 'v3-border-default bg-gradient-to-r from-slate-100 to-gray-100 v3-text-ink';
                    const iconClass = statusIcons[data] || 'fas fa-question-circle';
                    const localizedStatus = {
                        active: '<?= esc(lang('Treatment.status_values.active'), 'js') ?>',
                        completed: '<?= esc(lang('Treatment.status_values.completed'), 'js') ?>',
                        cancelled: '<?= esc(lang('Treatment.status_values.cancelled'), 'js') ?>',
                        on_hold: '<?= esc(lang('Treatment.status_values.on_hold'), 'js') ?>'
                    };

                    return `
                        <span class="inline-flex items-center v3-radius border px-3 py-1.5 text-xs font-extrabold ${colorClass}">
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
                            <span class="text-sm font-bold v3-text-ink">${row.start_date_formatted || date.toLocaleDateString()}</span>
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
                               class="v3-radius p-2 text-pink-700 transition-all duration-200  hover:bg-pink-50 hover:text-pink-800" title="<?= esc(lang('Treatment.view_details'), 'attr') ?>">
                                <i class="fas fa-eye"></i>
                            </a>`;
                    }

                    // Edit button
                    if (permissions.treatments.edit) {
                        actions += `
                            <a href="<?= base_url('treatment') ?>/${data}/edit"
                               class="v3-radius p-2 v3-text-primary transition-all duration-200  v3-hover-primary v3-hover-text-primary" title="<?= esc(lang('Treatment.edit_treatment'), 'attr') ?>">
                                <i class="fas fa-edit"></i>
                            </a>`;
                    }

                    // Delete button
                    if (permissions.treatments.delete) {
                        actions += `
                            <button onclick="deleteTreatment(${data})"
                                    class="v3-radius p-2 v3-text-danger transition-all duration-200  v3-hover-danger v3-hover-text-danger v3-btn v3-btn--danger" title="<?= esc(lang('Treatment.delete_treatment'), 'attr') ?>">
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

    function applyDataTablesStyling() {
        $('.dataTables_length select').removeClass().addClass('v3-radius border v3-border-default v3-bg-panel px-3 py-2 text-sm font-bold v3-text-ink v3-elevation-none outline-none transition focus:border-pink-400 focus:ring-4 focus:ring-pink-100');
        $('.dataTables_filter input').removeClass().addClass('v3-radius border v3-border-default v3-bg-panel px-3 py-2 text-sm font-bold v3-text-ink v3-elevation-none outline-none transition placeholder:v3-text-muted focus:border-pink-400 focus:ring-4 focus:ring-pink-100');
        $('.dataTables_info').removeClass().addClass('flex items-center text-sm font-bold v3-text-muted');
        $('.dataTables_paginate .paginate_button').removeClass().addClass('mx-1 v3-radius border v3-border-default v3-bg-panel px-3 py-2 text-sm font-extrabold v3-text-ink v3-elevation-none transition hover:border-pink-200 hover:bg-pink-50 hover:text-pink-800 focus:outline-none focus:ring-4 focus:ring-pink-100');
        $('.dataTables_paginate .paginate_button.current').addClass('!border-pink-600 !bg-pink-600 !v3-text-on-primary');
        $('.dataTables_paginate .paginate_button.disabled').addClass('cursor-not-allowed opacity-45 hover:!v3-border-default hover:!v3-bg-panel hover:!v3-text-muted');
        $('.dataTables_processing').removeClass().addClass('v3-radius border-0 bg-pink-600 px-4 py-3 text-sm font-extrabold v3-text-on-primary v3-elevation-none');
        $('.dataTables_length').addClass('order-1 flex items-center gap-3');
        $('.dataTables_filter').addClass('order-2 ms-auto flex items-center gap-3');
        $('.dataTables_info').addClass('order-1 flex items-center');
        $('.dataTables_paginate').addClass('order-2 ms-auto flex items-center gap-2');
        $('.dataTables_length label, .dataTables_filter label').addClass('text-sm font-extrabold v3-text-ink');
    }

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
var permissions = {
    treatments: {
        view: true,
        edit: true,
        delete: true,
        create: true
    }
};
</script>

</div>
<?= $this->endSection() ?>
