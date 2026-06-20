<?= $this->extend('layouts/main_auth') ?>

<?= $this->section('content') ?>
<?php $isRtl = !empty($is_rtl) || (service('request')->getLocale() === 'ar'); ?>

<!-- DataTables CSS -->
<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.13.7/css/jquery.dataTables.min.css">
<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/buttons/2.4.2/css/buttons.dataTables.min.css">

<!-- Custom DataTables Styling -->
<style>
/* DataTables Controls Styling - More specific selectors */
.dataTables_wrapper {
    position: relative !important;
}

.dataTables_length {
    display: flex !important;
    align-items: center !important;
    gap: 8px !important;
    order: 1 !important;
}

.dataTables_length label {
    font-size: 14px !important;
    font-weight: 600 !important;
    color: #374151 !important;
    margin-inline-end: 12px !important;
}

.dataTables_length select {
    padding: 8px 12px !important;
    background-color: white !important;
    border: 1px solid #d1d5db !important;
    border-radius: 8px !important;
    font-size: 14px !important;
    font-weight: 500 !important;
    color: #374151 !important;
    outline: none !important;
    transition: all 0.2s ease !important;
}

.dataTables_length select:focus {
    border-color: #db2777 !important;
    box-shadow: 0 0 0 3px rgba(219, 39, 119, 0.14) !important;
}

.dataTables_length select:hover {
    border-color: #9ca3af !important;
}

.dataTables_filter {
    display: flex !important;
    align-items: center !important;
    gap: 8px !important;
    margin-inline-start: auto !important;
    order: 2 !important;
}

.dataTables_filter label {
    font-size: 14px !important;
    font-weight: 600 !important;
    color: #374151 !important;
    margin-inline-end: 12px !important;
}

.dataTables_filter input {
    padding: 8px 12px !important;
    background-color: white !important;
    border: 1px solid #d1d5db !important;
    border-radius: 8px !important;
    font-size: 14px !important;
    font-weight: 500 !important;
    color: #374151 !important;
    outline: none !important;
    transition: all 0.2s ease !important;
}

.dataTables_filter input:focus {
    border-color: #db2777 !important;
    box-shadow: 0 0 0 3px rgba(219, 39, 119, 0.14) !important;
}

.dataTables_filter input:hover {
    border-color: #9ca3af !important;
}

.dataTables_info {
    font-size: 14px !important;
    font-weight: 500 !important;
    color: #6b7280 !important;
    display: flex !important;
    align-items: center !important;
}

.dataTables_paginate {
    display: flex !important;
    align-items: center !important;
    gap: 4px !important;
}

.dataTables_paginate .paginate_button {
    padding: 8px 12px !important;
    margin: 0 2px !important;
    background-color: white !important;
    border: 1px solid #d1d5db !important;
    border-radius: 6px !important;
    color: #374151 !important;
    font-size: 14px !important;
    font-weight: 500 !important;
    text-decoration: none !important;
    transition: all 0.2s ease !important;
}

.dataTables_paginate .paginate_button:hover {
    background-color: #f3f4f6 !important;
    border-color: #9ca3af !important;
    color: #1f2937 !important;
}

.dataTables_paginate .paginate_button.current {
    background-color: #db2777 !important;
    border-color: #db2777 !important;
    color: white !important;
}

.dataTables_paginate .paginate_button.disabled {
    background-color: #f9fafb !important;
    border-color: #e5e7eb !important;
    color: #9ca3af !important;
    cursor: not-allowed !important;
}

.dataTables_processing {
    background-color: rgba(255, 255, 255, 0.9) !important;
    border: 1px solid #d1d5db !important;
    border-radius: 8px !important;
    color: #374151 !important;
    font-size: 14px !important;
    font-weight: 500 !important;
    padding: 20px !important;
    text-align: center !important;
}

/* Ensure proper spacing and layout */
.dataTables_wrapper .dataTables_length {
    display: flex !important;
    align-items: center !important;
    margin-top: 0 !important;
    margin-inline-end: auto !important;
    order: 1 !important;
}

.dataTables_wrapper .dataTables_filter {
    display: flex !important;
    align-items: center !important;
    margin-top: 0 !important;
    margin-inline-start: auto !important;
    order: 2 !important;
}

.dataTables_wrapper .dataTables_info {
    display: flex !important;
    align-items: center !important;
    margin-top: 0 !important;
    margin-inline-end: auto !important;
    order: 1 !important;
}

.dataTables_wrapper .dataTables_paginate {
    display: flex !important;
    align-items: center !important;
    margin-top: 0 !important;
    margin-inline-start: auto !important;
    order: 2 !important;
}

/* Ensure the top container uses flexbox */
.dataTables_wrapper > .row:first-child {
    display: flex !important;
    align-items: center !important;
    justify-content: space-between !important;
}

/* Ensure the bottom container uses flexbox */
.dataTables_wrapper > .row:last-child {
    display: flex !important;
    align-items: center !important;
    justify-content: space-between !important;
}

.treatments-page .dataTables_wrapper {
    border-radius: 0 0 1.5rem 1.5rem;
    overflow: hidden;
}

.treatments-page .dt-buttons {
    display: none;
}

.treatments-page .dataTables_length label,
.treatments-page .dataTables_filter label {
    color: #475569 !important;
    font-size: 0.8rem !important;
    font-weight: 800 !important;
}

.treatments-page .dataTables_filter input,
.treatments-page .dataTables_length select {
    border-radius: 0.85rem !important;
    border-color: #cbd5e1 !important;
    background: #fff !important;
    box-shadow: 0 10px 24px -22px rgba(15,23,42,0.5) !important;
}

.treatments-page .dataTables_filter input:focus,
.treatments-page .dataTables_length select:focus {
    border-color: #db2777 !important;
    box-shadow: 0 0 0 3px rgba(219,39,119,0.14) !important;
}

.treatments-page #treatmentsTable thead th {
    background: #f8fafc !important;
    border-bottom: 1px solid #e2e8f0 !important;
    color: #334155 !important;
    text-align: start !important;
    font-weight: 800 !important;
}

.treatments-page #treatmentsTable tbody tr:hover {
    background: linear-gradient(90deg, rgba(253,242,248,0.96), rgba(250,245,255,0.62)) !important;
}

.treatments-page .dataTables_paginate .paginate_button.current {
    background: linear-gradient(135deg, #db2777, #9333ea) !important;
    border-color: transparent !important;
    color: #fff !important;
}

.treatments-page .dataTables_paginate .paginate_button:hover {
    background: #fdf2f8 !important;
    border-color: #f9a8d4 !important;
    color: #9d174d !important;
}
</style>

<div class="treatments-page min-h-screen bg-slate-50" dir="<?= $isRtl ? 'rtl' : 'ltr' ?>">
    <div class="w-full px-3 py-4 sm:px-5 sm:py-5">
        <section class="mb-5 overflow-hidden rounded-[1.55rem] bg-gradient-to-br from-pink-700 via-fuchsia-700 to-slate-950 text-white shadow-2xl shadow-pink-900/18">
            <div class="grid gap-5 p-5 sm:p-6 xl:grid-cols-[minmax(0,1fr)_auto] xl:items-center">
                <div class="flex min-w-0 items-start gap-4">
                    <span class="inline-flex h-14 w-14 shrink-0 items-center justify-center rounded-2xl bg-white/16 text-white shadow-lg ring-1 ring-white/20">
                        <i class="fas fa-procedures text-xl"></i>
                    </span>
                    <div class="min-w-0">
                        <p class="text-xs font-black uppercase tracking-[0.22em] text-pink-100"><?= lang('Treatment.treatment_registry') ?></p>
                        <h1 class="mt-1 text-2xl font-black leading-tight text-white sm:text-3xl"><?= lang('Treatment.treatment_management') ?></h1>
                        <p class="mt-2 max-w-2xl text-sm font-semibold leading-6 text-pink-50/88"><?= lang('Treatment.treatment_management_subtitle') ?></p>
                    </div>
                </div>
                <?php if (has_permission('treatments', 'create')): ?>
                    <a href="<?= base_url('treatment/create') ?>" id="newTreatmentModalTrigger" data-treatment-create-modal-open class="inline-flex items-center justify-center gap-2 rounded-2xl bg-white px-5 py-3 text-sm font-black text-pink-800 shadow-xl shadow-slate-950/18 transition hover:-translate-y-0.5 hover:bg-pink-50 focus:outline-none focus:ring-4 focus:ring-white/25">
                        <i class="fas fa-plus"></i>
                        <?= lang('Treatment.new_treatment') ?>
                    </a>
                <?php endif; ?>
            </div>
        </section>

        <section class="mb-5 grid grid-cols-1 gap-3 sm:grid-cols-2 xl:grid-cols-4">
            <article class="rounded-2xl bg-gradient-to-br from-pink-700 to-fuchsia-800 p-4 text-white shadow-xl shadow-pink-700/18">
                <div class="flex items-center justify-between gap-3">
                    <div>
                        <p class="text-xs font-black uppercase tracking-wide text-pink-100"><?= lang('Treatment.total_treatments') ?></p>
                        <p class="mt-2 text-3xl font-black" id="totalTreatments"><i class="fas fa-spinner fa-spin text-lg"></i></p>
                    </div>
                    <span class="inline-flex h-11 w-11 items-center justify-center rounded-2xl bg-white/16 ring-1 ring-white/20"><i class="fas fa-procedures"></i></span>
                </div>
            </article>
            <article class="rounded-2xl bg-gradient-to-br from-emerald-600 to-green-800 p-4 text-white shadow-xl shadow-emerald-700/18">
                <div class="flex items-center justify-between gap-3">
                    <div>
                        <p class="text-xs font-black uppercase tracking-wide text-emerald-100"><?= lang('Treatment.active_treatments') ?></p>
                        <p class="mt-2 text-3xl font-black" id="activeTreatments"><i class="fas fa-spinner fa-spin text-lg"></i></p>
                    </div>
                    <span class="inline-flex h-11 w-11 items-center justify-center rounded-2xl bg-white/16 ring-1 ring-white/20"><i class="fas fa-play-circle"></i></span>
                </div>
            </article>
            <article class="rounded-2xl bg-gradient-to-br from-blue-700 to-cyan-800 p-4 text-white shadow-xl shadow-blue-700/18">
                <div class="flex items-center justify-between gap-3">
                    <div>
                        <p class="text-xs font-black uppercase tracking-wide text-blue-100"><?= lang('Treatment.completed_treatments') ?></p>
                        <p class="mt-2 text-3xl font-black" id="completedTreatments"><i class="fas fa-spinner fa-spin text-lg"></i></p>
                    </div>
                    <span class="inline-flex h-11 w-11 items-center justify-center rounded-2xl bg-white/16 ring-1 ring-white/20"><i class="fas fa-check-double"></i></span>
                </div>
            </article>
            <article class="rounded-2xl bg-gradient-to-br from-purple-700 to-indigo-900 p-4 text-white shadow-xl shadow-purple-700/18">
                <div class="flex items-center justify-between gap-3">
                    <div>
                        <p class="text-xs font-black uppercase tracking-wide text-purple-100"><?= lang('Treatment.this_month') ?></p>
                        <p class="mt-2 text-3xl font-black" id="monthlyTreatments"><i class="fas fa-spinner fa-spin text-lg"></i></p>
                    </div>
                    <span class="inline-flex h-11 w-11 items-center justify-center rounded-2xl bg-white/16 ring-1 ring-white/20"><i class="fas fa-calendar-day"></i></span>
                </div>
            </article>
        </section>

        <section id="tableView" class="overflow-hidden rounded-[1.55rem] border border-slate-200 bg-white shadow-xl shadow-slate-900/7">
            <div class="border-b border-slate-200 bg-gradient-to-r from-slate-50 via-white to-pink-50/80 px-4 py-4 sm:px-6">
                <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                    <div class="flex items-center gap-3">
                        <span class="inline-flex h-10 w-10 items-center justify-center rounded-2xl bg-pink-100 text-pink-700">
                            <i class="fas fa-list-check"></i>
                        </span>
                        <div>
                            <h3 class="text-lg font-black text-slate-950"><?= lang('Treatment.treatment_registry') ?></h3>
                            <p class="mt-1 text-xs font-bold text-slate-500"><?= lang('Treatment.treatment_management_subtitle') ?></p>
                        </div>
                    </div>
                    <div class="flex flex-wrap items-center gap-2">
                        <button onclick="exportToPDF()" class="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm font-extrabold text-slate-700 shadow-sm transition hover:bg-red-50 hover:text-red-700">
                            <i class="fas fa-file-pdf text-red-600"></i>
                            <?= lang('Treatment.pdf') ?>
                        </button>
                        <button onclick="printTreatments()" class="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm font-extrabold text-slate-700 shadow-sm transition hover:bg-slate-50 hover:text-slate-950">
                            <i class="fas fa-print text-slate-600"></i>
                            <?= lang('Treatment.print') ?>
                        </button>
                        <button onclick="exportToCSV()" class="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm font-extrabold text-slate-700 shadow-sm transition hover:bg-emerald-50 hover:text-emerald-700">
                            <i class="fas fa-file-csv text-emerald-600"></i>
                            <?= lang('Treatment.csv') ?>
                        </button>
                    </div>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table id="treatmentsTable" class="w-full min-w-full">
                    <thead>
                        <tr>
                            <th class="px-6 py-4 text-start text-xs font-black uppercase tracking-wider text-slate-600">
                                <div class="flex items-center gap-2">
                                    <i class="fas fa-user text-pink-700"></i>
                                    <span><?= lang('Treatment.patient') ?></span>
                                </div>
                            </th>
                            <th class="px-6 py-4 text-start text-xs font-black uppercase tracking-wider text-slate-600">
                                <div class="flex items-center gap-2">
                                    <i class="fas fa-procedures text-pink-700"></i>
                                    <span><?= lang('Treatment.treatment') ?></span>
                                </div>
                            </th>
                            <th class="px-6 py-4 text-start text-xs font-black uppercase tracking-wider text-slate-600">
                                <div class="flex items-center gap-2">
                                    <i class="fas fa-info-circle text-pink-700"></i>
                                    <span><?= lang('Treatment.status') ?></span>
                                </div>
                            </th>
                            <th class="px-6 py-4 text-start text-xs font-black uppercase tracking-wider text-slate-600">
                                <div class="flex items-center gap-2">
                                    <i class="fas fa-calendar text-pink-700"></i>
                                    <span><?= lang('Treatment.start_date') ?></span>
                                </div>
                            </th>
                            <th class="px-6 py-4 text-start text-xs font-black uppercase tracking-wider text-slate-600">
                                <div class="flex items-center gap-2">
                                    <i class="fas fa-cogs text-pink-700"></i>
                                    <span><?= lang('Treatment.actions') ?></span>
                                </div>
                            </th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 bg-white">
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
        dom: 'B<"border-b border-slate-200 bg-slate-50/80 px-4 py-4 sm:px-6"<"flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between"<"flex items-center gap-4"l><"flex items-center gap-4"f>>rt<"border-t border-slate-200 bg-slate-50/80 px-4 py-4 sm:px-6"<"flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between"<"flex items-center"i><"flex items-center"p>>>',
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
        $('.dataTables_length select').removeClass().addClass('rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm font-bold text-slate-700 shadow-sm outline-none transition focus:border-pink-400 focus:ring-4 focus:ring-pink-100');
        $('.dataTables_filter input').removeClass().addClass('rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm font-bold text-slate-700 shadow-sm outline-none transition placeholder:text-slate-400 focus:border-pink-400 focus:ring-4 focus:ring-pink-100');
        $('.dataTables_info').removeClass().addClass('flex items-center text-sm font-bold text-slate-600');
        $('.dataTables_paginate .paginate_button').removeClass().addClass('mx-1 rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm font-extrabold text-slate-700 shadow-sm transition hover:border-pink-200 hover:bg-pink-50 hover:text-pink-800 focus:outline-none focus:ring-4 focus:ring-pink-100');
        $('.dataTables_paginate .paginate_button.current').addClass('!border-pink-600 !bg-pink-600 !text-white');
        $('.dataTables_paginate .paginate_button.disabled').addClass('cursor-not-allowed opacity-45 hover:!border-slate-200 hover:!bg-white hover:!text-slate-500');
        $('.dataTables_processing').removeClass().addClass('rounded-xl border-0 bg-pink-600 px-4 py-3 text-sm font-extrabold text-white shadow-xl');
        $('.dataTables_length').addClass('order-1 flex items-center gap-3');
        $('.dataTables_filter').addClass('order-2 ms-auto flex items-center gap-3');
        $('.dataTables_info').addClass('order-1 flex items-center');
        $('.dataTables_paginate').addClass('order-2 ms-auto flex items-center gap-2');
        $('.dataTables_length label, .dataTables_filter label').addClass('text-sm font-extrabold text-slate-700');
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

<?= $this->endSection() ?>
