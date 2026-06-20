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
    border-color: #0d9488 !important;
    box-shadow: 0 0 0 3px rgba(13, 148, 136, 0.14) !important;
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
    border-color: #0d9488 !important;
    box-shadow: 0 0 0 3px rgba(13, 148, 136, 0.14) !important;
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
    background-color: #0d9488 !important;
    border-color: #0d9488 !important;
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

.prescriptions-page .dataTables_wrapper {
    border-radius: 0 0 1.5rem 1.5rem;
    overflow: hidden;
}

.prescriptions-page .dt-buttons {
    display: none;
}

.prescriptions-page .dataTables_length label,
.prescriptions-page .dataTables_filter label {
    color: #475569 !important;
    font-size: 0.8rem !important;
    font-weight: 800 !important;
}

.prescriptions-page .dataTables_filter input,
.prescriptions-page .dataTables_length select {
    border-radius: 0.85rem !important;
    border-color: #cbd5e1 !important;
    background: #fff !important;
    box-shadow: 0 10px 24px -22px rgba(15,23,42,0.5) !important;
}

.prescriptions-page .dataTables_filter input:focus,
.prescriptions-page .dataTables_length select:focus {
    border-color: #0d9488 !important;
    box-shadow: 0 0 0 3px rgba(13,148,136,0.14) !important;
}

.prescriptions-page #prescriptionsTable thead th {
    background: #f8fafc !important;
    border-bottom: 1px solid #e2e8f0 !important;
    color: #334155 !important;
    text-align: start !important;
    font-weight: 800 !important;
}

.prescriptions-page #prescriptionsTable tbody tr:hover {
    background: linear-gradient(90deg, rgba(240,253,250,0.96), rgba(236,253,245,0.62)) !important;
}

.prescriptions-page .dataTables_paginate .paginate_button.current {
    background: linear-gradient(135deg, #0d9488, #059669) !important;
    border-color: transparent !important;
    color: #fff !important;
}

.prescriptions-page .dataTables_paginate .paginate_button:hover {
    background: #f0fdfa !important;
    border-color: #5eead4 !important;
    color: #0f766e !important;
}
</style>
<style>
.prescription-create-modal {
    visibility: hidden;
    pointer-events: none;
    transition: visibility 0s linear 240ms;
}
.prescription-create-modal.is-open {
    visibility: visible;
    pointer-events: auto;
    transition-delay: 0s;
}
.prescription-create-modal__backdrop {
    opacity: 0;
    transition: opacity 220ms ease;
}
.prescription-create-modal.is-open .prescription-create-modal__backdrop {
    opacity: 1;
}
.prescription-create-modal__panel {
    opacity: 0;
    transform: scale(0.86);
    transform-origin: center center;
    transition:
        opacity 180ms ease,
        transform 420ms cubic-bezier(0.34, 1.56, 0.64, 1);
    will-change: transform, opacity;
}
.prescription-create-modal.is-open .prescription-create-modal__panel {
    opacity: 1;
    transform: scale(1);
}
.prescription-create-modal .select2-container {
    width: 100% !important;
}
@media (prefers-reduced-motion: reduce) {
    .prescription-create-modal__backdrop,
    .prescription-create-modal__panel {
        transition: none;
    }
}
@media (max-width: 640px) {
    .prescription-create-modal__shell {
        align-items: stretch;
        padding: 0;
    }
    .prescription-create-modal__panel {
        width: 100vw;
        min-height: 100dvh;
        max-height: 100dvh;
        border-radius: 0;
    }
}
</style>
<style>
[dir="ltr"] .prescription-leading-input {
    padding-left: 0.75rem !important;
    padding-right: 2.5rem !important;
}

[dir="rtl"] .prescription-leading-input {
    padding-right: 2.5rem !important;
    padding-left: 0.75rem !important;
    text-align: right;
}

[dir="ltr"] input[data-clinic-date="1"].prescription-date-input {
    padding-left: 1rem !important;
    padding-right: 2.75rem !important;
}

[dir="rtl"] input[data-clinic-date="1"].prescription-date-input {
    padding-right: 2.75rem !important;
    padding-left: 1rem !important;
    text-align: right;
}
</style>

<div class="prescriptions-page min-h-screen bg-slate-50" dir="<?= $isRtl ? 'rtl' : 'ltr' ?>">
    <div class="w-full px-3 py-4 sm:px-5 sm:py-5">
        <section class="mb-5 overflow-hidden rounded-[1.55rem] bg-gradient-to-br from-teal-700 via-emerald-700 to-slate-950 text-white shadow-2xl shadow-teal-900/18">
            <div class="grid gap-5 p-5 sm:p-6 xl:grid-cols-[minmax(0,1fr)_auto] xl:items-center">
                <div class="flex min-w-0 items-start gap-4">
                    <span class="inline-flex h-14 w-14 shrink-0 items-center justify-center rounded-2xl bg-white/16 text-white shadow-lg ring-1 ring-white/20">
                        <i class="fas fa-prescription-bottle-alt text-xl"></i>
                    </span>
                    <div class="min-w-0">
                        <p class="text-xs font-black uppercase tracking-[0.22em] text-teal-100"><?= lang('Prescription.prescription_registry') ?></p>
                        <h1 class="mt-1 text-2xl font-black leading-tight text-white sm:text-3xl"><?= lang('Prescription.management_title') ?></h1>
                        <p class="mt-2 max-w-2xl text-sm font-semibold leading-6 text-teal-50/88"><?= lang('Prescription.management_subtitle') ?></p>
                    </div>
                </div>
                <?php if (has_permission('prescriptions', 'create')): ?>
                    <a href="<?= base_url('prescription/create') ?>" id="newPrescriptionModalTrigger" data-prescription-create-modal-open class="inline-flex items-center justify-center gap-2 rounded-2xl bg-white px-5 py-3 text-sm font-black text-teal-800 shadow-xl shadow-slate-950/18 transition hover:-translate-y-0.5 hover:bg-teal-50 focus:outline-none focus:ring-4 focus:ring-white/25">
                        <i class="fas fa-plus"></i>
                        <?= lang('Prescription.new_prescription') ?>
                    </a>
                <?php endif; ?>
            </div>
        </section>

        <section class="mb-5 grid grid-cols-1 gap-3 sm:grid-cols-2 xl:grid-cols-4">
            <article class="rounded-2xl bg-gradient-to-br from-teal-700 to-emerald-800 p-4 text-white shadow-xl shadow-teal-700/18">
                <div class="flex items-center justify-between gap-3">
                    <div>
                        <p class="text-xs font-black uppercase tracking-wide text-teal-100"><?= lang('Prescription.total_prescriptions') ?></p>
                        <p class="mt-2 text-3xl font-black" id="totalPrescriptions"><i class="fas fa-spinner fa-spin text-lg"></i></p>
                    </div>
                    <span class="inline-flex h-11 w-11 items-center justify-center rounded-2xl bg-white/16 ring-1 ring-white/20"><i class="fas fa-prescription-bottle-alt"></i></span>
                </div>
            </article>
            <article class="rounded-2xl bg-gradient-to-br from-emerald-600 to-green-800 p-4 text-white shadow-xl shadow-emerald-700/18">
                <div class="flex items-center justify-between gap-3">
                    <div>
                        <p class="text-xs font-black uppercase tracking-wide text-emerald-100"><?= lang('Prescription.active_prescriptions') ?></p>
                        <p class="mt-2 text-3xl font-black" id="activePrescriptions"><i class="fas fa-spinner fa-spin text-lg"></i></p>
                    </div>
                    <span class="inline-flex h-11 w-11 items-center justify-center rounded-2xl bg-white/16 ring-1 ring-white/20"><i class="fas fa-check-circle"></i></span>
                </div>
            </article>
            <article class="rounded-2xl bg-gradient-to-br from-amber-600 to-orange-800 p-4 text-white shadow-xl shadow-amber-700/18">
                <div class="flex items-center justify-between gap-3">
                    <div>
                        <p class="text-xs font-black uppercase tracking-wide text-amber-100"><?= lang('Prescription.expired_prescriptions') ?></p>
                        <p class="mt-2 text-3xl font-black" id="expiredPrescriptions"><i class="fas fa-spinner fa-spin text-lg"></i></p>
                    </div>
                    <span class="inline-flex h-11 w-11 items-center justify-center rounded-2xl bg-white/16 ring-1 ring-white/20"><i class="fas fa-exclamation-triangle"></i></span>
                </div>
            </article>
            <article class="rounded-2xl bg-gradient-to-br from-purple-700 to-indigo-900 p-4 text-white shadow-xl shadow-purple-700/18">
                <div class="flex items-center justify-between gap-3">
                    <div>
                        <p class="text-xs font-black uppercase tracking-wide text-purple-100"><?= lang('Prescription.this_month') ?></p>
                        <p class="mt-2 text-3xl font-black" id="monthlyPrescriptions"><i class="fas fa-spinner fa-spin text-lg"></i></p>
                    </div>
                    <span class="inline-flex h-11 w-11 items-center justify-center rounded-2xl bg-white/16 ring-1 ring-white/20"><i class="fas fa-calendar-day"></i></span>
                </div>
            </article>
        </section>

        <section id="tableView" class="overflow-hidden rounded-[1.55rem] border border-slate-200 bg-white shadow-xl shadow-slate-900/7">
            <div class="border-b border-slate-200 bg-gradient-to-r from-slate-50 via-white to-teal-50/80 px-4 py-4 sm:px-6">
                <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                    <div class="flex items-center gap-3">
                        <span class="inline-flex h-10 w-10 items-center justify-center rounded-2xl bg-teal-100 text-teal-700">
                            <i class="fas fa-list-check"></i>
                        </span>
                        <div>
                            <h3 class="text-lg font-black text-slate-950"><?= lang('Prescription.prescription_registry') ?></h3>
                            <p class="mt-1 text-xs font-bold text-slate-500"><?= lang('Prescription.management_subtitle') ?></p>
                        </div>
                    </div>
                    <div class="flex flex-wrap items-center gap-2">
                        <button onclick="exportToPDF()" class="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm font-extrabold text-slate-700 shadow-sm transition hover:bg-red-50 hover:text-red-700">
                            <i class="fas fa-file-pdf text-red-600"></i>
                            <?= lang('Prescription.export_pdf') ?>
                        </button>
                        <button onclick="printPrescriptions()" class="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm font-extrabold text-slate-700 shadow-sm transition hover:bg-slate-50 hover:text-slate-950">
                            <i class="fas fa-print text-slate-600"></i>
                            <?= lang('Prescription.export_print') ?>
                        </button>
                        <button onclick="exportToCSV()" class="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm font-extrabold text-slate-700 shadow-sm transition hover:bg-emerald-50 hover:text-emerald-700">
                            <i class="fas fa-file-csv text-emerald-600"></i>
                            <?= lang('Prescription.export_csv') ?>
                        </button>
                    </div>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table id="prescriptionsTable" class="w-full min-w-full">
                    <thead>
                        <tr>
                            <th class="px-6 py-4 text-start text-xs font-black uppercase tracking-wider text-slate-600">
                                <div class="flex items-center gap-2">
                                    <i class="fas fa-prescription-bottle-alt text-teal-700"></i>
                                    <span><?= lang('Prescription.prescription_id') ?></span>
                                </div>
                            </th>
                            <th class="px-6 py-4 text-start text-xs font-black uppercase tracking-wider text-slate-600">
                                <div class="flex items-center gap-2">
                                    <i class="fas fa-user text-teal-700"></i>
                                    <span><?= lang('Prescription.patient') ?></span>
                                </div>
                            </th>
                            <th class="px-6 py-4 text-start text-xs font-black uppercase tracking-wider text-slate-600">
                                <div class="flex items-center gap-2">
                                    <i class="fas fa-pills text-teal-700"></i>
                                    <span><?= lang('Prescription.medicines') ?></span>
                                </div>
                            </th>
                            <th class="px-6 py-4 text-start text-xs font-black uppercase tracking-wider text-slate-600">
                                <div class="flex items-center gap-2">
                                    <i class="fas fa-calendar-alt text-teal-700"></i>
                                    <span><?= lang('Prescription.date_col') ?></span>
                                </div>
                            </th>
                            <th class="px-6 py-4 text-start text-xs font-black uppercase tracking-wider text-slate-600">
                                <div class="flex items-center gap-2">
                                    <i class="fas fa-info-circle text-teal-700"></i>
                                    <span><?= lang('Prescription.status') ?></span>
                                </div>
                            </th>
                            <th class="px-6 py-4 text-start text-xs font-black uppercase tracking-wider text-slate-600">
                                <div class="flex items-center gap-2">
                                    <i class="fas fa-cogs text-teal-700"></i>
                                    <span><?= lang('Prescription.actions') ?></span>
                                </div>
                            </th>
                        </tr>
                    </thead>
                    <tbody id="prescriptionsTableBody" class="divide-y divide-slate-100 bg-white">
                        <tr id="loadingRow">
                            <td colspan="6" class="px-6 py-4 text-center text-slate-500">
                                <div class="flex items-center justify-center gap-2">
                                    <div class="h-4 w-4 animate-spin rounded-full border-b-2 border-teal-700"></div>
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
        <div class="prescription-create-modal__backdrop fixed inset-0 bg-slate-950/45 backdrop-blur-sm" data-prescription-create-modal-close></div>
        <div class="prescription-create-modal__shell relative z-10 flex min-h-screen items-center justify-center p-4" data-prescription-create-modal-close>
            <form id="newPrescriptionQuickCreateForm" action="<?= base_url('prescription/store') ?>" method="POST" class="prescription-create-modal__panel flex max-h-[92dvh] w-full max-w-5xl flex-col overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-2xl" data-prescription-create-form data-select2-dropdown-parent onclick="event.stopPropagation()">
                <?= csrf_field() ?>
                <input type="hidden" name="return_to" value="<?= esc($returnToContext) ?>">
                <input type="hidden" name="source_modal" value="prescription_quick_create">

                <div class="flex flex-shrink-0 items-start justify-between gap-4 border-b border-slate-200 bg-white/95 px-5 py-4 backdrop-blur">
                    <div class="flex items-center gap-3">
                        <span class="inline-flex h-11 w-11 items-center justify-center rounded-2xl bg-gradient-to-br from-teal-500 to-emerald-600 text-white shadow-lg">
                            <i class="fas fa-prescription-bottle-alt"></i>
                        </span>
                        <div>
                            <h2 id="newPrescriptionModalTitle" class="text-lg font-bold text-slate-900"><?= esc(lang('Prescription.new_prescription')) ?></h2>
                            <a href="<?= workflow_context_url('prescription/create', $returnToContext) ?>" class="mt-1 inline-flex text-xs font-semibold text-teal-700 hover:text-teal-800">
                                <?= esc(lang('UI.openFullPage')) ?>
                            </a>
                        </div>
                    </div>
                    <button type="button" class="inline-flex h-9 w-9 items-center justify-center rounded-full text-slate-500 transition hover:bg-slate-100 hover:text-slate-700" data-prescription-create-modal-close aria-label="<?= esc(lang('UI.closeDrawer')) ?>">
                        <i class="fas fa-times text-sm"></i>
                    </button>
                </div>

                <div class="min-h-0 flex-1 space-y-5 overflow-y-auto bg-slate-50/70 p-5">
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

                    <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm md:p-6">
                        <?= view('prescription/partials/form_fields', [
                            'selected_patient' => null,
                            'is_rtl' => $isRtl,
                            'id_prefix' => 'prescriptionQuick',
                        ]) ?>
                    </div>
                </div>

                <div class="flex flex-shrink-0 flex-col-reverse gap-3 border-t border-slate-200 bg-white/95 px-5 py-4 backdrop-blur sm:flex-row sm:justify-end">
                    <button type="button" data-prescription-create-modal-close class="inline-flex items-center justify-center rounded-xl border border-slate-300 px-5 py-2.5 text-sm font-semibold text-slate-700 transition hover:bg-slate-50">
                        <?= esc(lang('Prescription.cancel')) ?>
                    </button>
                    <button type="submit" class="inline-flex items-center justify-center rounded-xl bg-gradient-to-r from-teal-500 to-emerald-600 px-5 py-2.5 text-sm font-bold text-white shadow-lg transition hover:from-teal-600 hover:to-emerald-700 hover:shadow-xl">
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
        dom: 'B<"border-b border-slate-200 bg-slate-50/80 px-4 py-4 sm:px-6"<"flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between"<"flex items-center gap-4"l><"flex items-center gap-4"f>>rt<"border-t border-slate-200 bg-slate-50/80 px-4 py-4 sm:px-6"<"flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between"<"flex items-center"i><"flex items-center"p>>>',
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

    function applyDataTablesStyling() {
        $('.dataTables_length select').removeClass().addClass('rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm font-bold text-slate-700 shadow-sm outline-none transition focus:border-teal-400 focus:ring-4 focus:ring-teal-100');
        $('.dataTables_filter input').removeClass().addClass('rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm font-bold text-slate-700 shadow-sm outline-none transition placeholder:text-slate-400 focus:border-teal-400 focus:ring-4 focus:ring-teal-100');
        $('.dataTables_info').removeClass().addClass('flex items-center text-sm font-bold text-slate-600');
        $('.dataTables_paginate .paginate_button').removeClass().addClass('mx-1 rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm font-extrabold text-slate-700 shadow-sm transition hover:border-teal-200 hover:bg-teal-50 hover:text-teal-800 focus:outline-none focus:ring-4 focus:ring-teal-100');
        $('.dataTables_paginate .paginate_button.current').addClass('!border-teal-600 !bg-teal-600 !text-white');
        $('.dataTables_paginate .paginate_button.disabled').addClass('cursor-not-allowed opacity-45 hover:!border-slate-200 hover:!bg-white hover:!text-slate-500');
        $('.dataTables_processing').removeClass().addClass('rounded-xl border-0 bg-teal-600 px-4 py-3 text-sm font-extrabold text-white shadow-xl');
        $('.dataTables_length').addClass('order-1 flex items-center gap-3');
        $('.dataTables_filter').addClass('order-2 ms-auto flex items-center gap-3');
        $('.dataTables_info').addClass('order-1 flex items-center');
        $('.dataTables_paginate').addClass('order-2 ms-auto flex items-center gap-2');
        $('.dataTables_length label, .dataTables_filter label').addClass('text-sm font-extrabold text-slate-700');
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
