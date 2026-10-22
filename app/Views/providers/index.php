<?= $this->extend('layouts/main_auth') ?>

<?= $this->section('content') ?>

<?php
$isRtl = !empty($is_rtl);
$iconGap = $isRtl ? 'ml-2' : 'mr-2';
$iconGapSmall = $isRtl ? 'ml-1.5' : 'mr-1.5';
?>

<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.13.7/css/jquery.dataTables.min.css">

<style>
.providers-page .dataTables_wrapper {
    position: relative !important;
    border-radius: 0 0 1.5rem 1.5rem;
    overflow: hidden;
}
.providers-page .dataTables_length,
.providers-page .dataTables_filter,
.providers-page .dataTables_info,
.providers-page .dataTables_paginate { display: flex !important; align-items: center !important; }
.providers-page .dataTables_length label,
.providers-page .dataTables_filter label { display: flex !important; align-items: center !important; gap: .5rem !important; margin: 0 !important; font-size: .8rem !important; font-weight: 800 !important; color: #475569 !important; }
.providers-page .dataTables_length select,
.providers-page .dataTables_filter input {
    height: 2.5rem !important;
    border: 1px solid #cbd5e1 !important;
    border-radius: .85rem !important;
    background: #fff !important;
    color: #334155 !important;
    font-size: .875rem !important;
    outline: none !important;
    box-shadow: 0 10px 24px -22px rgba(15,23,42,0.5) !important;
}
.providers-page .dataTables_length select { padding: .5rem .75rem !important; }
.providers-page .dataTables_filter input { min-width: 16rem; padding: .5rem .9rem !important; }
.providers-page .dataTables_length select:focus,
.providers-page .dataTables_filter input:focus { border-color: #10b981 !important; box-shadow: 0 0 0 3px rgba(16, 185, 129, .12) !important; }
.providers-page table.dataTable { border-collapse: separate !important; border-spacing: 0 !important; width: 100% !important; }
.providers-page #providersTable thead th {
    padding: 8px 10px !important;
    border-bottom: 1px solid #e5e7eb !important;
    background: #f9fafb !important;
    color: #4b5563 !important;
    font-size: .75rem !important;
    font-weight: 800 !important;
    letter-spacing: .05em !important;
    text-transform: uppercase !important;
    white-space: nowrap !important;
}
.providers-page #providersTable thead th:first-child,
.providers-page #providersTable tbody td:first-child { text-align: start !important; }
.providers-page #providersTable thead th:not(:first-child):not(:last-child),
.providers-page #providersTable tbody td:not(:first-child):not(:last-child) { text-align: center !important; }
.providers-page #providersTable thead th:last-child,
.providers-page #providersTable tbody td:last-child { text-align: center !important; }
.providers-page #providersTable tbody td {
    padding: 8px 10px !important;
    border-bottom: 1px solid #f3f4f6 !important;
    color: #374151 !important;
    vertical-align: middle !important;
}
.providers-page #providersTable tbody td:last-child {
    white-space: nowrap !important;
}
.providers-page #providersTable tbody tr:hover { background: linear-gradient(90deg, rgba(236,253,245,0.92), rgba(240,249,255,0.76)) !important; }
.providers-page .dataTables_paginate .paginate_button {
    margin: 0 .125rem !important;
    padding: .45rem .7rem !important;
    border: 1px solid #d1d5db !important;
    border-radius: .5rem !important;
    background: #fff !important;
    color: #334155 !important;
    font-size: .875rem !important;
    font-weight: 600 !important;
}
.providers-page .dataTables_paginate .paginate_button.current {
    border-color: transparent !important;
    background: linear-gradient(135deg, #059669, #0f766e) !important;
    color: #fff !important;
}
.providers-page .providers-table-toolbar {
    border-bottom: 1px solid #e2e8f0;
    background: linear-gradient(90deg, #f8fafc, #fff, rgba(236,253,245,0.7));
    padding: 1rem 1.5rem;
}
.providers-page .providers-table-footer {
    border-top: 1px solid #e2e8f0;
    background: #f8fafc;
    padding: 1rem 1.5rem;
}
.providers-page .provider-filter {
    border: 1px solid #cbd5e1;
    border-radius: .95rem;
    background: #fff;
    color: #475569;
    font-size: .8125rem;
    font-weight: 900;
    padding: .55rem .9rem;
    transition: all .2s ease;
    box-shadow: 0 10px 24px -23px rgba(15,23,42,0.55);
}
.providers-page .provider-filter:hover { border-color: #10b981; color: #047857; background: #ecfdf5; }
.providers-page .provider-filter.active { border-color: transparent; color: #fff; background: linear-gradient(135deg, #059669, #0f766e); box-shadow: 0 14px 28px -20px rgba(5,150,105,0.75); }
.provider-scale-modal {
    visibility: hidden;
    pointer-events: none;
    transition: visibility 0s linear 220ms;
}
.provider-scale-modal.is-open {
    visibility: visible;
    pointer-events: auto;
    transition-delay: 0s;
}
.provider-scale-modal__backdrop {
    opacity: 0;
    transition: opacity 220ms ease;
}
.provider-scale-modal.is-open .provider-scale-modal__backdrop {
    opacity: 1;
}
.provider-scale-modal__panel {
    opacity: 0;
    transform: scale(0.86);
    transform-origin: center center;
    transition:
        opacity 180ms ease,
        transform 420ms cubic-bezier(0.34, 1.56, 0.64, 1);
    will-change: transform, opacity;
}
.provider-scale-modal.is-open .provider-scale-modal__panel {
    opacity: 1;
    transform: scale(1);
}
@media (prefers-reduced-motion: reduce) {
    .provider-scale-modal__backdrop,
    .provider-scale-modal__panel {
        transition: none;
    }
}
[dir="rtl"] .providers-page .dataTables_filter { margin-right: auto !important; margin-left: 0 !important; }
[dir="rtl"] .providers-page .dataTables_length { margin-left: auto !important; margin-right: 0 !important; }
[dir="rtl"] .providers-page table.dataTable th,
[dir="rtl"] .providers-page table.dataTable td { text-align: right !important; }
@media (max-width: 640px) {
    .providers-page .dataTables_filter input { min-width: 12rem; width: 100%; }
    .providers-page #providersTable tbody td,
    .providers-page #providersTable tbody td,
    .providers-page #providersTable thead th { padding-left: 8px !important; padding-right: 8px !important; }
}
</style>

<div class="providers-page min-h-screen bg-slate-50" dir="<?= $isRtl ? 'rtl' : 'ltr' ?>">
    <div class="w-full px-3 py-4 sm:px-5 sm:py-5">
        <section class="mb-5 overflow-hidden rounded-[1.55rem] bg-gradient-to-br from-teal-700 via-emerald-700 to-slate-900 text-white shadow-2xl shadow-emerald-900/18">
            <div class="grid gap-5 p-5 sm:p-6 xl:grid-cols-[minmax(0,1fr)_auto] xl:items-center">
                <div class="flex min-w-0 items-start gap-4">
                    <span class="inline-flex h-14 w-14 shrink-0 items-center justify-center rounded-2xl bg-white/16 text-white shadow-lg ring-1 ring-white/20">
                        <i class="fas fa-handshake text-xl"></i>
                    </span>
                    <div class="min-w-0">
                        <p class="text-xs font-black uppercase tracking-[0.22em] text-emerald-100"><?= esc(lang('Provider.tableTitle')) ?></p>
                        <h1 class="mt-1 text-2xl font-black leading-tight text-white sm:text-3xl"><?= esc(lang('Provider.pageTitle')) ?></h1>
                        <p class="mt-2 max-w-2xl text-sm font-semibold leading-6 text-emerald-50/88"><?= esc(lang('Provider.pageSubtitle')) ?></p>
                    </div>
                </div>
                <?php if (has_permission('inventory', 'edit')): ?>
                    <button type="button" id="addSupplierBtn" class="inline-flex items-center justify-center gap-2 rounded-2xl bg-white px-5 py-3 text-sm font-black text-emerald-800 shadow-xl shadow-slate-950/18 transition hover:-translate-y-0.5 hover:bg-emerald-50 focus:outline-none focus:ring-4 focus:ring-white/25">
                        <i class="fas fa-plus"></i>
                        <?= esc(lang('Provider.addSupplier')) ?>
                    </button>
                <?php endif; ?>
            </div>
        </section>

        <p class="mb-3 text-xs text-slate-600"><?= esc(lang('Finance.providerVoidBalanceHelp')) ?></p>
        <section class="mb-5 grid grid-cols-1 gap-3 sm:grid-cols-2 xl:grid-cols-4">
            <?php
            $cards = [
                ['id' => 'totalCombined', 'label' => lang('Provider.allProviders'), 'icon' => 'fa-handshake', 'tone' => 'from-emerald-500 to-teal-600', 'soft' => 'from-emerald-100 to-teal-100', 'text' => 'text-emerald-700'],
                ['id' => 'activeSuppliers', 'label' => lang('Provider.activeSuppliers'), 'icon' => 'fa-check-circle', 'tone' => 'from-green-500 to-emerald-600', 'soft' => 'from-green-100 to-emerald-100', 'text' => 'text-green-700'],
                ['id' => 'totalProviders', 'label' => lang('Provider.clinicalProviders'), 'icon' => 'fa-user-doctor', 'tone' => 'from-blue-500 to-indigo-600', 'soft' => 'from-blue-100 to-indigo-100', 'text' => 'text-blue-700'],
                ['id' => 'outstandingBalance', 'label' => lang('Provider.outstandingBalance'), 'icon' => 'fa-wallet', 'tone' => 'from-amber-500 to-orange-600', 'soft' => 'from-amber-100 to-orange-100', 'text' => 'text-amber-700'],
            ];
            ?>
            <?php foreach ($cards as $card): ?>
                <article class="rounded-2xl bg-gradient-to-br <?= esc($card['tone']) ?> p-4 text-white shadow-xl shadow-emerald-700/18">
                    <div class="flex items-center justify-between gap-3">
                        <div>
                            <p class="text-xs font-black uppercase tracking-wide text-white/78"><?= esc($card['label']) ?></p>
                            <p class="mt-2 text-3xl font-black" id="<?= esc($card['id']) ?>"><i class="fas fa-spinner fa-spin text-lg text-white/70"></i></p>
                        </div>
                        <span class="inline-flex h-11 w-11 items-center justify-center rounded-2xl bg-white/16 ring-1 ring-white/20">
                            <i class="fas <?= esc($card['icon']) ?>"></i>
                        </span>
                    </div>
                </article>
            <?php endforeach; ?>
        </section>

        <section class="overflow-hidden rounded-[1.55rem] border border-slate-200 bg-white shadow-xl shadow-slate-900/7">
            <div class="border-b border-slate-200 bg-gradient-to-r from-slate-50 via-white to-emerald-50/70 px-4 py-4 sm:px-5">
                <div class="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
                    <div class="flex items-center gap-3">
                        <span class="inline-flex h-11 w-11 items-center justify-center rounded-2xl bg-emerald-100 text-emerald-700 ring-1 ring-emerald-200">
                            <i class="fas fa-list"></i>
                        </span>
                        <div>
                            <h2 class="text-lg font-black text-slate-950"><?= esc(lang('Provider.tableTitle')) ?></h2>
                            <p class="text-sm font-semibold text-slate-500"><?= esc(lang('Provider.pageSubtitle')) ?></p>
                        </div>
                    </div>
                    <div class="flex flex-wrap gap-2" id="typeFilters">
                        <button type="button" class="provider-filter active" data-type=""><?= esc(lang('Provider.filterAll')) ?></button>
                        <button type="button" class="provider-filter" data-type="supplier"><i class="fas fa-truck <?= $iconGapSmall ?>"></i><?= esc(lang('Provider.filterSuppliers')) ?></button>
                        <button type="button" class="provider-filter" data-type="clinical_provider"><i class="fas fa-user-doctor <?= $iconGapSmall ?>"></i><?= esc(lang('Provider.filterProviders')) ?></button>
                    </div>
                </div>
            </div>
            <div class="overflow-x-auto">
                <table id="providersTable" class="w-full min-w-full">
                    <thead>
                        <tr>
                            <th>
                                <div class="flex items-center gap-2">
                                    <i class="fas fa-user text-emerald-600"></i>
                                    <span><?= esc(lang('Provider.colName')) ?></span>
                                </div>
                            </th>
                            <th>
                                <div class="flex items-center gap-2">
                                    <i class="fas fa-tag text-emerald-600"></i>
                                    <span><?= esc(lang('Provider.colType')) ?></span>
                                </div>
                            </th>
                            <th>
                                <div class="flex items-center gap-2">
                                    <i class="fas fa-address-book text-emerald-600"></i>
                                    <span><?= esc(lang('Provider.colContact')) ?></span>
                                </div>
                            </th>
                            <th>
                                <div class="flex items-center gap-2">
                                    <i class="fas fa-chart-simple text-emerald-600"></i>
                                    <span><?= esc(lang('Provider.colStats')) ?></span>
                                </div>
                            </th>
                            <th>
                                <div class="flex items-center gap-2">
                                    <i class="fas fa-wallet text-emerald-600"></i>
                                    <span><?= esc(lang('Provider.openAmount')) ?></span>
                                </div>
                            </th>
                            <th>
                                <div class="flex items-center gap-2">
                                    <i class="fas fa-circle-info text-emerald-600"></i>
                                    <span><?= esc(lang('Provider.colStatus')) ?></span>
                                </div>
                            </th>
                            <th>
                                <div class="flex items-center gap-2">
                                    <i class="fas fa-calendar text-emerald-600"></i>
                                    <span><?= esc(lang('Provider.lastActivity')) ?></span>
                                </div>
                            </th>
                            <th>
                                <div class="flex items-center gap-2">
                                    <i class="fas fa-cogs text-emerald-600"></i>
                                    <span><?= esc(lang('Provider.colActions')) ?></span>
                                </div>
                            </th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </section>
    </div>
</div>

<div id="quickSupplierModal" class="provider-scale-modal fixed inset-0 z-[2147483600] hidden overflow-y-auto" role="dialog" aria-modal="true" aria-hidden="true" dir="<?= $isRtl ? 'rtl' : 'ltr' ?>">
    <div class="provider-scale-modal__backdrop fixed inset-0 bg-slate-950/45 backdrop-blur-sm" onclick="closeSupplierModal()"></div>
    <div class="relative z-10 flex min-h-screen items-center justify-center p-4" onclick="closeSupplierModal()">
        <div class="provider-scale-modal__panel w-full max-w-lg overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-2xl" onclick="event.stopPropagation()">
            <div class="flex items-center justify-between gap-4 border-b border-slate-200 bg-gradient-to-r from-slate-50 via-white to-emerald-50/80 px-5 py-4">
                <div class="flex items-center gap-3">
                    <span class="inline-flex h-11 w-11 items-center justify-center rounded-2xl bg-gradient-to-br from-emerald-600 to-teal-700 text-white shadow-lg shadow-emerald-700/20">
                        <i class="fas fa-truck"></i>
                    </span>
                    <div>
                        <h4 class="text-base font-black text-slate-950"><?= esc(lang('Provider.quickAddSupplierTitle')) ?></h4>
                        <p class="mt-1 text-sm font-semibold text-slate-500"><?= esc(lang('Provider.quickAddSupplierHelp')) ?></p>
                    </div>
                </div>
                <button type="button" class="inline-flex h-9 w-9 items-center justify-center rounded-full text-slate-500 transition hover:bg-slate-100 hover:text-slate-700" onclick="closeSupplierModal()">
                    <i class="fas fa-times text-sm"></i>
                </button>
            </div>
            <div class="space-y-4 bg-slate-50/70 p-5">
                <div>
                    <label class="mb-1.5 block text-xs font-black uppercase tracking-wider text-slate-500" for="quickSupplierName"><?= esc(lang('Provider.supplierName')) ?> *</label>
                    <input type="text" id="quickSupplierName" class="w-full rounded-xl border border-slate-300 px-3 py-2.5 text-sm text-slate-900 shadow-sm focus:border-emerald-400 focus:outline-none focus:ring-4 focus:ring-emerald-100" placeholder="<?= esc(lang('Provider.supplierName')) ?>" required>
                </div>
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div>
                        <label class="mb-1.5 block text-xs font-black uppercase tracking-wider text-slate-500" for="quickSupplierContact"><?= esc(lang('Provider.contactPerson')) ?></label>
                        <input type="text" id="quickSupplierContact" class="w-full rounded-xl border border-slate-300 px-3 py-2.5 text-sm text-slate-900 shadow-sm focus:border-emerald-400 focus:outline-none focus:ring-4 focus:ring-emerald-100">
                    </div>
                    <div>
                        <label class="mb-1.5 block text-xs font-black uppercase tracking-wider text-slate-500" for="quickSupplierPhone"><?= esc(lang('Provider.phone')) ?></label>
                        <input type="text" id="quickSupplierPhone" class="w-full rounded-xl border border-slate-300 px-3 py-2.5 text-sm text-slate-900 shadow-sm focus:border-emerald-400 focus:outline-none focus:ring-4 focus:ring-emerald-100">
                    </div>
                </div>
                <div>
                    <label class="mb-1.5 block text-xs font-black uppercase tracking-wider text-slate-500" for="quickSupplierEmail"><?= esc(lang('Provider.email')) ?></label>
                    <input type="email" id="quickSupplierEmail" class="w-full rounded-xl border border-slate-300 px-3 py-2.5 text-sm text-slate-900 shadow-sm focus:border-emerald-400 focus:outline-none focus:ring-4 focus:ring-emerald-100">
                </div>
                <p id="quickSupplierStatus" class="text-xs text-slate-500 hidden"></p>
            </div>
            <div class="flex flex-wrap justify-end gap-2 border-t border-slate-200 px-5 py-4">
                <button type="button" onclick="closeSupplierModal()" class="inline-flex items-center gap-2 rounded-xl border border-slate-300 px-4 py-2.5 text-sm font-semibold text-slate-700 transition hover:bg-slate-50">
                    <i class="fas fa-times text-xs"></i>
                    <span><?= esc(lang('Provider.cancel')) ?></span>
                </button>
                <button type="button" id="createQuickSupplierBtn" class="inline-flex items-center gap-2 rounded-xl bg-gradient-to-r from-emerald-600 to-teal-700 px-4 py-2.5 text-sm font-bold text-white shadow-lg shadow-emerald-700/20 transition hover:from-emerald-700 hover:to-teal-800">
                    <i class="fas fa-plus text-xs"></i>
                    <span><?= esc(lang('Provider.createSupplier')) ?></span>
                </button>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>

<script>
let supplierModalCloseTimer = null;
let supplierModalTrigger = null;

function mountSupplierModal() {
    const modal = document.getElementById('quickSupplierModal');
    if (modal && modal.parentElement !== document.body) {
        document.body.appendChild(modal);
    }
    return modal;
}

function resetSupplierModalFields() {
    ['quickSupplierName', 'quickSupplierContact', 'quickSupplierPhone', 'quickSupplierEmail'].forEach(function(id) {
        var el = document.getElementById(id);
        if (el) { el.value = ''; }
    });
    var status = document.getElementById('quickSupplierStatus');
    if (status) { status.classList.add('hidden'); status.textContent = ''; }
}

function openSupplierModal(event) {
    const modal = mountSupplierModal();
    if (!modal) { return; }
    supplierModalTrigger = event?.currentTarget || document.activeElement;
    if (supplierModalCloseTimer) {
        clearTimeout(supplierModalCloseTimer);
        supplierModalCloseTimer = null;
    }
    modal.classList.remove('hidden');
    modal.setAttribute('aria-hidden', 'false');
    document.documentElement.classList.add('app-drawer-lock');
    document.body.classList.add('app-drawer-lock');
    requestAnimationFrame(function() {
        modal.classList.add('is-open');
    });
    setTimeout(function() {
        var input = document.getElementById('quickSupplierName');
        if (input) { input.focus(); }
    }, 120);
}

function closeSupplierModal() {
    var modal = document.getElementById('quickSupplierModal');
    if (!modal || modal.classList.contains('hidden')) { return; }
    modal.classList.remove('is-open');
    modal.setAttribute('aria-hidden', 'true');
    document.documentElement.classList.remove('app-drawer-lock');
    document.body.classList.remove('app-drawer-lock');
    supplierModalCloseTimer = setTimeout(function() {
        modal.classList.add('hidden');
        resetSupplierModalFields();
        if (supplierModalTrigger && typeof supplierModalTrigger.focus === 'function') {
            supplierModalTrigger.focus();
        }
        supplierModalTrigger = null;
        supplierModalCloseTimer = null;
    }, 240);
}

document.addEventListener('keydown', function(e) {
    if (e.key !== 'Escape') { return; }
    var modal = document.getElementById('quickSupplierModal');
    if (modal && !modal.classList.contains('hidden')) { closeSupplierModal(); }
});

(function() {
    let providersTable;
    let activeTypeFilter = '';
    const isRtl = <?= $isRtl ? 'true' : 'false' ?>;

    function setSupplierButtonLabel(button, loading) {
        const icon = document.createElement('i');
        icon.className = loading ? 'fas fa-spinner fa-spin' : 'fas fa-plus text-xs';
        const gap = document.createElement('span');
        gap.className = '<?= $iconGap ?>';
        button.replaceChildren(icon, gap, document.createTextNode(loading ? i18n.creatingSupplier : i18n.createSupplier));
    }

    const i18n = {
        supplier: '<?= esc(lang('Provider.typeSupplier'), 'js') ?>',
        clinical: '<?= esc(lang('Provider.typeClinicalProvider'), 'js') ?>',
        noContact: '<?= esc(lang('Provider.noContact'), 'js') ?>',
        loading: '<?= esc(lang('Provider.loading'), 'js') ?>',
        noRecords: '<?= esc(lang('Provider.noRecords'), 'js') ?>',
        searchPlaceholder: '<?= esc(lang('Provider.searchPlaceholder'), 'js') ?>',
        showing: '<?= esc(lang('Provider.showing'), 'js') ?>',
        infoEmpty: '<?= esc(lang('Provider.infoEmpty'), 'js') ?>',
        infoFiltered: '<?= esc(lang('Provider.infoFiltered'), 'js') ?>',
        supplierNameRequired: '<?= esc(lang('Provider.supplierNameRequired'), 'js') ?>',
        supplierExists: '<?= esc(lang('Provider.supplierExists'), 'js') ?>',
        failedToCreate: '<?= esc(lang('Provider.failedToCreate'), 'js') ?>',
        creatingSupplier: '<?= esc(lang('Provider.creatingSupplier'), 'js') ?>',
        createSupplier: '<?= esc(lang('Provider.createSupplier'), 'js') ?>'
    };

    function escHtml(value) {
        return String(value ?? '').replace(/[&<>"']/g, function(match) {
            return {'&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;'}[match];
        });
    }

    function typeBadge(row) {
        const supplier = row.type === 'supplier';
        const classes = supplier ? 'bg-emerald-100 text-emerald-700' : 'bg-blue-100 text-blue-700';
        const icon = supplier ? 'fa-truck' : 'fa-user-doctor';
        return '<span class="inline-flex items-center rounded-full px-2 py-px text-xs font-medium ' + classes + '"><i class="fas ' + icon + ' <?= $iconGapSmall ?>"></i>' + escHtml(row.type_label) + '</span>';
    }

    function statusBadge(row) {
        let classes = 'bg-gray-100 text-gray-700';
        let icon = 'fa-circle-info';
        if (row.status === 'active' || row.status === 'settled') {
            classes = 'bg-green-100 text-green-700';
            icon = 'fa-check-circle';
        } else if (row.status === 'inactive') {
            icon = 'fa-times-circle';
        } else if (row.status === 'balance_due') {
            classes = 'bg-yellow-100 text-yellow-700';
            icon = 'fa-clock';
        }
        return '<span class="inline-flex items-center rounded-full px-2 py-px text-xs font-medium ' + classes + '"><i class="fas ' + icon + ' <?= $iconGapSmall ?>"></i>' + escHtml(row.status_label) + '</span>';
    }

    function formatDate(value) {
        return value ? escHtml(String(value).substring(0, 10)) : '-';
    }

    function initDataTable() {
        providersTable = $('#providersTable').DataTable({
            processing: true,
            serverSide: true,
            responsive: true,
            pageLength: 10,
            lengthMenu: [[10, 25, 50, 100], [10, 25, 50, 100]],
            order: [[0, 'asc']],
            autoWidth: false,
            columnDefs: [
                { targets: 1, width: '12%' },  /* Type */
                { targets: 6, width: '10%' },  /* Last Activity */
                { targets: 7, width: '3rem' }, /* Actions */
            ],
            dom: '<"providers-table-toolbar"<"flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between"lf>>rt<"providers-table-footer"<"flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between"ip>>',
            ajax: {
                url: '<?= base_url('providers/data') ?>',
                type: 'POST',
                data: function(d) {
                    d.type = activeTypeFilter;
                },
                error: function() {
                    console.error('Providers table failed to load.');
                }
            },
            columns: [
                { data: 'name', responsivePriority: 1, render: function(data, type, row) {
                    const icon = row.type === 'supplier' ? 'fa-truck text-emerald-600' : 'fa-user-doctor text-blue-600';
                    return '<div class="flex items-center gap-2"><span class="inline-flex flex-shrink-0 items-center justify-center"><i class="fas ' + icon + '"></i></span><span class="text-sm font-medium text-gray-900">' + escHtml(data || '-') + '</span></div>';
                } },
                { data: 'type', responsivePriority: 3, render: function(data, type, row) { return typeBadge(row); } },
                { data: 'contact', responsivePriority: 4, orderable: false, render: function(data) {
                    return data ? escHtml(data) : '<span class="text-gray-400">' + i18n.noContact + '</span>';
                } },
                { data: 'activity_count', responsivePriority: 5, render: function(data, type, row) {
                    return '<span class="text-sm text-gray-900">' + escHtml(row.activity_label || '-') + '</span>';
                } },
                { data: 'outstanding', responsivePriority: 2, render: function(data, type, row) {
                    const due = Number(data) > 0;
                    return '<span class="text-sm font-medium ' + (due ? 'text-amber-700' : 'text-gray-900') + '">' + escHtml(row.outstanding_label || '0.00') + '</span>';
                } },
                { data: 'status', responsivePriority: 6, render: function(data, type, row) {
                    return statusBadge(row);
                } },
                { data: 'last_activity', responsivePriority: 7, render: function(data) {
                    return '<span class="text-sm text-gray-900">' + formatDate(data) + '</span>';
                } },
                { data: null, responsivePriority: 1, orderable: false, render: function(data, type, row) {
                    if (!row.show_link) return '';
                    const href = window.workflowContextUrl ? window.workflowContextUrl(row.link) : row.link;
                    return '<a href="' + escHtml(href) + '" class="inline-flex items-center justify-center w-8 h-8 rounded-lg text-emerald-600 transition-colors duration-200 hover:bg-emerald-50 hover:text-emerald-700" title="' + escHtml(row.link_label) + '"><i class="fas fa-eye"></i></a>';
                } }
            ],
            createdRow: function(row, data) {
                if (!data.link) return;
                $(row)
                    .addClass('cursor-pointer')
                    .attr('data-href', window.workflowContextUrl ? window.workflowContextUrl(data.link) : data.link)
                    .on('click', function(e) {
                        if ($(e.target).closest('a, button').length) return;
                        window.location.href = $(this).attr('data-href');
                    });
            },
            language: {
                processing: '<i class="fas fa-spinner fa-spin"></i> ' + i18n.loading,
                zeroRecords: i18n.noRecords,
                search: '',
                searchPlaceholder: i18n.searchPlaceholder,
                lengthMenu: '_MENU_',
                info: i18n.showing,
                infoEmpty: i18n.infoEmpty,
                infoFiltered: i18n.infoFiltered
            },
            drawCallback: function() {
                $('.dataTables_filter input').attr('placeholder', i18n.searchPlaceholder);
            }
        });

        $('.dataTables_filter input').attr('placeholder', i18n.searchPlaceholder);
    }

    function loadStats() {
        $.getJSON('<?= base_url('providers/stats') ?>')
            .done(function(data) {
                $('#totalCombined').text(data.total_combined || 0);
                $('#activeSuppliers').text(data.active_suppliers || 0);
                $('#totalProviders').text(data.total_providers || 0);
                $('#outstandingBalance').text(data.outstanding_label || '?');
            })
            .fail(function() {
                $('#totalCombined, #activeSuppliers, #totalProviders, #outstandingBalance').text('0');
            });
    }

    $('#typeFilters').on('click', '.provider-filter', function() {
        $('#typeFilters .provider-filter').removeClass('active');
        $(this).addClass('active');
        activeTypeFilter = $(this).data('type') || '';
        providersTable.ajax.reload();
    });

    const addSupplierBtn = document.getElementById('addSupplierBtn');
    if (addSupplierBtn) {
        addSupplierBtn.addEventListener('click', openSupplierModal);
    }

    const createQuickSupplierBtn = document.getElementById('createQuickSupplierBtn');
    if (createQuickSupplierBtn) {
        createQuickSupplierBtn.addEventListener('click', function() {
        const nameInput = document.getElementById('quickSupplierName');
        const name = nameInput.value.trim();
        const statusEl = document.getElementById('quickSupplierStatus');

        if (!name) {
            statusEl.classList.remove('hidden');
            statusEl.classList.add('text-red-600');
            statusEl.textContent = i18n.supplierNameRequired;
            return;
        }

        const btn = this;
        btn.disabled = true;
        setSupplierButtonLabel(btn, true);

        const formData = new FormData();
        formData.append('name', name);
        formData.append('contact_person', document.getElementById('quickSupplierContact').value.trim());
        formData.append('phone', document.getElementById('quickSupplierPhone').value.trim());
        formData.append('email', document.getElementById('quickSupplierEmail').value.trim());
        if (window.csrfConfig?.name && window.getCsrfToken) {
            formData.append(window.csrfConfig.name, window.getCsrfToken());
        }

        fetch('<?= base_url('inventory/suppliers/quick-create') ?>', {
            method: 'POST',
            headers: {'X-Requested-With': 'XMLHttpRequest'},
            body: formData
        })
        .then(function(r) { return r.json(); })
        .then(function(json) {
            if (json.csrf_token && window.refreshCsrfToken) {
                window.refreshCsrfToken(json.csrf_token);
            }
            if (json.success) {
                closeSupplierModal();
                providersTable.ajax.reload();
                loadStats();
                return;
            }
            statusEl.classList.remove('hidden');
            statusEl.classList.add('text-red-600');
            statusEl.textContent = json.message || i18n.failedToCreate;
        })
        .catch(function() {
            statusEl.classList.remove('hidden');
            statusEl.classList.add('text-red-600');
            statusEl.textContent = i18n.failedToCreate;
        })
        .finally(function() {
            btn.disabled = false;
            setSupplierButtonLabel(btn, false);
        });
        });
    }

    $(document).ready(function() {
        initDataTable();
        loadStats();
    });
})();
</script>

<?= $this->endSection() ?>
