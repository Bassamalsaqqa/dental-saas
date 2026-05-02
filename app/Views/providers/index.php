<?= $this->extend('layouts/main_auth') ?>

<?= $this->section('content') ?>

<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.13.7/css/jquery.dataTables.min.css">

<style>
.dataTables_wrapper { position: relative !important; }
.dataTables_length { display: flex !important; align-items: center !important; gap: 8px !important; }
.dataTables_length select { padding: 8px 12px !important; background: white !important; border: 1px solid #d1d5db !important; border-radius: 8px !important; font-size: 14px !important; color: #374151 !important; outline: none !important; }
.dataTables_length select:focus { border-color: #06b6d4 !important; box-shadow: 0 0 0 3px rgba(6, 182, 212, 0.1) !important; }
.dataTables_filter { display: flex !important; align-items: center !important; gap: 8px !important; margin-left: auto !important; }
.dataTables_filter input { padding: 8px 12px !important; border: 1px solid #d1d5db !important; border-radius: 8px !important; font-size: 14px !important; outline: none !important; min-width: 200px; }
.dataTables_filter input:focus { border-color: #06b6d4 !important; box-shadow: 0 0 0 3px rgba(6, 182, 212, 0.1) !important; }
#providersTable { width: 100% !important; }
#providersTable th { background: #f9fafb !important; font-weight: 600 !important; color: #4b5563 !important; font-size: 12px !important; text-transform: uppercase !important; letter-spacing: 0.05em !important; padding: 16px 24px !important; border-bottom: 1px solid #e5e7eb !important; }
#providersTable td { padding: 16px 24px !important; vertical-align: middle !important; font-size: 14px !important; color: #374151 !important; border-bottom: 1px solid #f3f4f6 !important; }
#providersTable tbody tr:hover { background: #ecfeff !important; }
.dataTables_paginate .paginate_button.current { background: linear-gradient(to right, #06b6d4, #0891b2) !important; border: none !important; color: #fff !important; border-radius: 6px !important; }
.filter-btn { padding: 8px 16px; border-radius: 9999px; font-size: 13px; font-weight: 600; border: 1px solid #e2e8f0; background: white; color: #475569; cursor: pointer; transition: all 0.2s; }
.filter-btn:hover { background: #f0fdfa; border-color: #06b6d4; color: #0891b2; }
.filter-btn.active { background: linear-gradient(to right, #06b6d4, #0891b2); color: white; border-color: transparent; }
</style>

<!-- Header Section -->
<div class="relative mb-8 overflow-hidden">
    <div class="absolute inset-0 bg-gradient-to-br from-cyan-500/10 to-teal-600/5 rounded-3xl"></div>
    <div class="absolute -top-40 -right-40 w-80 h-80 bg-gradient-to-br from-cyan-400/20 to-teal-600/20 rounded-full blur-3xl animate-pulse"></div>
    <div class="absolute -bottom-40 -left-40 w-80 h-80 bg-gradient-to-br from-cyan-400/20 to-sky-600/20 rounded-full blur-3xl animate-pulse delay-1000"></div>
    <div class="absolute top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2 w-96 h-96 bg-gradient-to-br from-teal-400/10 to-cyan-600/10 rounded-full blur-3xl animate-pulse delay-2000"></div>

    <div class="container mx-auto relative z-10 px-3 sm:px-4 py-4 sm:py-6">
        <!-- Page Header -->
        <div class="mb-8">
            <div class="bg-white/80 backdrop-blur-sm border border-white/30 rounded-xl p-6 shadow-lg">
                <div class="flex flex-col gap-4 xl:flex-row xl:justify-between xl:items-center">
                    <div class="flex items-start sm:items-center space-x-3 sm:space-x-4">
                        <div class="w-11 h-11 sm:w-12 sm:h-12 bg-gradient-to-br from-cyan-500 to-sky-600 rounded-xl flex items-center justify-center text-white shadow-lg">
                            <i class="fas fa-handshake text-lg sm:text-xl"></i>
                        </div>
                        <div>
                            <h1 class="text-xl sm:text-3xl font-bold text-gray-900"><?= esc(lang('Provider.pageTitle')) ?></h1>
                            <p class="text-gray-600"><?= esc(lang('Provider.pageSubtitle')) ?></p>
                        </div>
                    </div>
                    <div class="flex w-full flex-col gap-3 sm:w-auto sm:flex-row sm:items-center sm:space-x-3">
                        <?php if (has_permission('inventory', 'edit')): ?>
                            <button type="button" id="addSupplierBtn" class="inline-flex w-full sm:w-auto items-center justify-center px-4 py-2 bg-gradient-to-r from-cyan-500 to-sky-600 text-white text-sm font-semibold rounded-lg hover:from-cyan-600 hover:to-sky-700 transition-all duration-200 shadow-md hover:shadow-lg">
                                <i class="fas fa-plus mr-2"></i>
                                <?= esc(lang('Provider.addSupplier')) ?>
                            </button>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Statistics Cards -->
        <div class="mb-8">
            <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4 sm:gap-6 items-stretch">
                <!-- Total Suppliers -->
                <div class="group relative">
                    <div class="absolute inset-0 bg-gradient-to-br from-cyan-500/20 to-sky-600/20 rounded-3xl blur-xl group-hover:blur-2xl transition-all duration-500 opacity-0 group-hover:opacity-100"></div>
                    <div class="relative backdrop-blur-xl bg-white/80 border border-white/30 rounded-2xl p-6 shadow-2xl shadow-cyan-500/10 group-hover:shadow-cyan-500/20 transition-all duration-500 hover:scale-105 hover:-translate-y-2 h-full flex flex-col">
                        <div class="flex items-center justify-between mb-4">
                            <div class="relative">
                                <div class="absolute inset-0 bg-gradient-to-br from-cyan-500 to-sky-600 rounded-2xl blur-lg opacity-75 group-hover:opacity-100 transition-opacity duration-300"></div>
                                <div class="relative p-3 rounded-xl bg-gradient-to-br from-cyan-500 to-sky-600 text-white shadow-xl group-hover:scale-110 group-hover:rotate-3 transition-all duration-300">
                                    <i class="fas fa-truck text-xl"></i>
                                </div>
                            </div>
                            <div class="flex items-center space-x-2 bg-gradient-to-r from-cyan-100 to-sky-100 px-3 py-1.5 rounded-full border border-cyan-200">
                                <div class="w-2 h-2 bg-cyan-500 rounded-full animate-pulse"></div>
                                <span class="text-cyan-700 font-bold text-xs"><?= esc(lang('Provider.totalProviders')) ?></span>
                            </div>
                        </div>
                        <p class="text-3xl font-bold text-gray-900" id="totalSuppliers"><i class="fas fa-spinner fa-spin text-gray-400 text-sm"></i></p>
                        <p class="text-sm text-gray-500 mt-2"><?= esc(lang('Provider.totalProviders')) ?></p>
                    </div>
                </div>

                <!-- Active Suppliers -->
                <div class="group relative">
                    <div class="absolute inset-0 bg-gradient-to-br from-emerald-500/20 to-green-600/20 rounded-3xl blur-xl group-hover:blur-2xl transition-all duration-500 opacity-0 group-hover:opacity-100"></div>
                    <div class="relative backdrop-blur-xl bg-white/80 border border-white/30 rounded-2xl p-6 shadow-2xl shadow-emerald-500/10 group-hover:shadow-emerald-500/20 transition-all duration-500 hover:scale-105 hover:-translate-y-2 h-full flex flex-col">
                        <div class="flex items-center justify-between mb-4">
                            <div class="relative">
                                <div class="absolute inset-0 bg-gradient-to-br from-emerald-500 to-green-600 rounded-2xl blur-lg opacity-75 group-hover:opacity-100 transition-opacity duration-300"></div>
                                <div class="relative p-3 rounded-xl bg-gradient-to-br from-emerald-500 to-green-600 text-white shadow-xl group-hover:scale-110 group-hover:rotate-3 transition-all duration-300">
                                    <i class="fas fa-check-circle text-xl"></i>
                                </div>
                            </div>
                            <div class="flex items-center space-x-2 bg-gradient-to-r from-emerald-100 to-green-100 px-3 py-1.5 rounded-full border border-emerald-200">
                                <div class="w-2 h-2 bg-emerald-500 rounded-full animate-pulse"></div>
                                <span class="text-emerald-700 font-bold text-xs">Active</span>
                            </div>
                        </div>
                        <p class="text-3xl font-bold text-gray-900" id="activeSuppliers"><i class="fas fa-spinner fa-spin text-gray-400 text-sm"></i></p>
                        <p class="text-sm text-gray-500 mt-2"><?= esc(lang('Provider.activeSuppliers')) ?></p>
                    </div>
                </div>

                <!-- Clinical Providers -->
                <div class="group relative">
                    <div class="absolute inset-0 bg-gradient-to-br from-purple-500/20 to-violet-600/20 rounded-3xl blur-xl group-hover:blur-2xl transition-all duration-500 opacity-0 group-hover:opacity-100"></div>
                    <div class="relative backdrop-blur-xl bg-white/80 border border-white/30 rounded-2xl p-6 shadow-2xl shadow-purple-500/10 group-hover:shadow-purple-500/20 transition-all duration-500 hover:scale-105 hover:-translate-y-2 h-full flex flex-col">
                        <div class="flex items-center justify-between mb-4">
                            <div class="relative">
                                <div class="absolute inset-0 bg-gradient-to-br from-purple-500 to-violet-600 rounded-2xl blur-lg opacity-75 group-hover:opacity-100 transition-opacity duration-300"></div>
                                <div class="relative p-3 rounded-xl bg-gradient-to-br from-purple-500 to-violet-600 text-white shadow-xl group-hover:scale-110 group-hover:rotate-3 transition-all duration-300">
                                    <i class="fas fa-user-md text-xl"></i>
                                </div>
                            </div>
                            <div class="flex items-center space-x-2 bg-gradient-to-r from-purple-100 to-violet-100 px-3 py-1.5 rounded-full border border-purple-200">
                                <div class="w-2 h-2 bg-purple-500 rounded-full animate-pulse"></div>
                                <span class="text-purple-700 font-bold text-xs"><?= esc(lang('Provider.clinicalProviders')) ?></span>
                            </div>
                        </div>
                        <p class="text-3xl font-bold text-gray-900" id="totalProviders"><i class="fas fa-spinner fa-spin text-gray-400 text-sm"></i></p>
                        <p class="text-sm text-gray-500 mt-2"><?= esc(lang('Provider.clinicalProviders')) ?></p>
                    </div>
                </div>

                <!-- Outstanding Balance -->
                <div class="group relative">
                    <div class="absolute inset-0 bg-gradient-to-br from-amber-500/20 to-yellow-600/20 rounded-3xl blur-xl group-hover:blur-2xl transition-all duration-500 opacity-0 group-hover:opacity-100"></div>
                    <div class="relative backdrop-blur-xl bg-white/80 border border-white/30 rounded-2xl p-6 shadow-2xl shadow-amber-500/10 group-hover:shadow-amber-500/20 transition-all duration-500 hover:scale-105 hover:-translate-y-2 h-full flex flex-col">
                        <div class="flex items-center justify-between mb-4">
                            <div class="relative">
                                <div class="absolute inset-0 bg-gradient-to-br from-amber-500 to-yellow-600 rounded-2xl blur-lg opacity-75 group-hover:opacity-100 transition-opacity duration-300"></div>
                                <div class="relative p-3 rounded-xl bg-gradient-to-br from-amber-500 to-yellow-600 text-white shadow-xl group-hover:scale-110 group-hover:rotate-3 transition-all duration-300">
                                    <i class="fas fa-coins text-xl"></i>
                                </div>
                            </div>
                            <div class="flex items-center space-x-2 bg-gradient-to-r from-amber-100 to-yellow-100 px-3 py-1.5 rounded-full border border-amber-200">
                                <div class="w-2 h-2 bg-amber-500 rounded-full animate-pulse"></div>
                                <span class="text-amber-700 font-bold text-xs"><?= esc(lang('Provider.outstandingBalance')) ?></span>
                            </div>
                        </div>
                        <p class="text-3xl font-bold text-gray-900" id="outstandingBalance"><i class="fas fa-spinner fa-spin text-gray-400 text-sm"></i></p>
                        <p class="text-sm text-gray-500 mt-2"><?= esc(lang('Provider.outstandingBalance')) ?></p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Providers Table -->
        <div class="mb-8">
            <div id="tableView" class="bg-white rounded-xl border border-gray-200 shadow-lg">
                <!-- Table Header -->
                <div class="px-6 py-4 border-b border-gray-200 bg-gray-50 rounded-t-xl">
                    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3">
                        <div class="flex items-center space-x-3">
                            <div class="w-2 h-2 bg-cyan-500 rounded-full"></div>
                            <h3 class="text-lg font-semibold text-gray-900">Providers Directory</h3>
                        </div>
                        <div class="flex flex-col sm:flex-row items-start sm:items-center gap-3">
                            <!-- Type Filter -->
                            <div class="flex flex-wrap gap-2" id="typeFilters">
                                <button class="filter-btn active" data-type=""><?= esc(lang('Provider.filterAll')) ?></button>
                                <button class="filter-btn" data-type="supplier"><i class="fas fa-truck mr-1"></i><?= esc(lang('Provider.filterSuppliers')) ?></button>
                                <button class="filter-btn" data-type="clinical_provider"><i class="fas fa-user-md mr-1"></i><?= esc(lang('Provider.filterProviders')) ?></button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Table -->
                <div class="overflow-x-auto">
                    <table id="providersTable" class="w-full min-w-full">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                    <div class="flex items-center space-x-2">
                                        <i class="fas fa-tag text-cyan-600"></i>
                                        <span><?= esc(lang('Provider.colType')) ?></span>
                                    </div>
                                </th>
                                <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                    <div class="flex items-center space-x-2">
                                        <i class="fas fa-user text-cyan-600"></i>
                                        <span><?= esc(lang('Provider.colName')) ?></span>
                                    </div>
                                </th>
                                <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                    <div class="flex items-center space-x-2">
                                        <i class="fas fa-address-book text-cyan-600"></i>
                                        <span><?= esc(lang('Provider.colContact')) ?></span>
                                    </div>
                                </th>
                                <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                    <div class="flex items-center space-x-2">
                                        <i class="fas fa-phone text-cyan-600"></i>
                                        <span><?= esc(lang('Provider.colPhone')) ?></span>
                                    </div>
                                </th>
                                <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                    <div class="flex items-center space-x-2">
                                        <i class="fas fa-info-circle text-cyan-600"></i>
                                        <span><?= esc(lang('Provider.colStatus')) ?></span>
                                    </div>
                                </th>
                                <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                    <div class="flex items-center space-x-2">
                                        <i class="fas fa-calendar text-cyan-600"></i>
                                        <span><?= esc(lang('Provider.colCreated')) ?></span>
                                    </div>
                                </th>
                                <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                    <div class="flex items-center space-x-2">
                                        <i class="fas fa-cogs text-cyan-600"></i>
                                        <span><?= esc(lang('Provider.colActions')) ?></span>
                                    </div>
                                </th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Add Supplier Modal -->
<div id="quickSupplierModal" class="fixed inset-0 z-50 hidden overflow-y-auto p-4" role="dialog" aria-modal="true">
    <div class="fixed inset-0 bg-gray-900/50 backdrop-blur-sm" data-modal-close></div>
    <div class="relative flex min-h-full items-center justify-center">
        <div class="relative rounded-2xl bg-white shadow-2xl w-full max-w-lg" onclick="event.stopPropagation()">
            <div class="flex items-start justify-between gap-4 border-b border-slate-100 px-6 py-5">
                <div>
                    <h3 class="text-lg font-bold text-slate-900"><?= esc(lang('Provider.quickAddSupplierTitle')) ?></h3>
                    <p class="mt-1 text-sm text-slate-500"><?= esc(lang('Provider.quickAddSupplierHelp')) ?></p>
                </div>
                <button type="button" class="inline-flex h-10 w-10 items-center justify-center rounded-full text-slate-400 hover:bg-slate-100 hover:text-slate-600" data-modal-close>
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="space-y-4 px-6 py-6">
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1" for="quickSupplierName"><?= esc(lang('Provider.supplierName')) ?> *</label>
                    <input type="text" id="quickSupplierName" class="w-full rounded-lg border border-slate-300 px-4 py-2.5 text-sm focus:border-cyan-500 focus:ring-1 focus:ring-cyan-500 outline-none" placeholder="<?= esc(lang('Provider.supplierName')) ?>">
                </div>
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1" for="quickSupplierContact"><?= esc(lang('Provider.contactPerson')) ?></label>
                        <input type="text" id="quickSupplierContact" class="w-full rounded-lg border border-slate-300 px-4 py-2.5 text-sm focus:border-cyan-500 focus:ring-1 focus:ring-cyan-500 outline-none">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1" for="quickSupplierPhone"><?= esc(lang('Provider.phone')) ?></label>
                        <input type="text" id="quickSupplierPhone" class="w-full rounded-lg border border-slate-300 px-4 py-2.5 text-sm focus:border-cyan-500 focus:ring-1 focus:ring-cyan-500 outline-none">
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1" for="quickSupplierEmail"><?= esc(lang('Provider.email')) ?></label>
                    <input type="email" id="quickSupplierEmail" class="w-full rounded-lg border border-slate-300 px-4 py-2.5 text-sm focus:border-cyan-500 focus:ring-1 focus:ring-cyan-500 outline-none">
                </div>
                <p id="quickSupplierStatus" class="text-xs text-slate-500 hidden"></p>
            </div>
            <div class="flex flex-col-reverse gap-3 border-t border-slate-100 px-6 py-4 sm:flex-row sm:items-center sm:justify-end">
                <button type="button" class="inline-flex items-center justify-center px-4 py-2 text-sm font-semibold text-slate-700 bg-slate-100 rounded-lg hover:bg-slate-200 transition-colors" data-modal-close>
                    <?= esc(lang('Provider.cancel')) ?>
                </button>
                <button type="button" id="createQuickSupplierBtn" class="inline-flex items-center justify-center px-4 py-2 text-sm font-semibold text-white bg-gradient-to-r from-cyan-500 to-sky-600 rounded-lg hover:from-cyan-600 hover:to-sky-700 transition-all shadow-md">
                    <i class="fas fa-plus mr-2"></i><?= esc(lang('Provider.createSupplier')) ?>
                </button>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>

<script>
(function() {
    let providersTable;
    const csrfName = document.querySelector('meta[name="csrf-name"]')?.content || '<?= csrf_token() ?>';
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || '<?= csrf_hash() ?>';
    const csrfHeader = document.querySelector('meta[name="csrf-header"]')?.content || '<?= csrf_header() ?>';
    let activeTypeFilter = '';

    const i18n = {
        typeSupplier: '<?= esc(lang('Provider.typeSupplier'), 'js') ?>',
        typeClinicalProvider: '<?= esc(lang('Provider.typeClinicalProvider'), 'js') ?>',
        statusActive: '<?= esc(lang('Provider.statusActive'), 'js') ?>',
        statusInactive: '<?= esc(lang('Provider.statusInactive'), 'js') ?>',
        noContact: '<?= esc(lang('Provider.noContact'), 'js') ?>',
        noPhone: '<?= esc(lang('Provider.noPhone'), 'js') ?>',
        noEmail: '<?= esc(lang('Provider.noEmail'), 'js') ?>',
        guestProvider: '<?= esc(lang('Provider.guestProvider'), 'js') ?>',
        viewSupplier: '<?= esc(lang('Provider.viewSupplier'), 'js') ?>',
        viewProvider: '<?= esc(lang('Provider.viewProvider'), 'js') ?>',
        typeLabel: '<?= esc(lang('Provider.colType'), 'js') ?>',
        nameLabel: '<?= esc(lang('Provider.colName'), 'js') ?>',
        contactLabel: '<?= esc(lang('Provider.colContact'), 'js') ?>',
        phoneLabel: '<?= esc(lang('Provider.colPhone'), 'js') ?>',
        statusLabel: '<?= esc(lang('Provider.colStatus'), 'js') ?>',
        createdLabel: '<?= esc(lang('Provider.colCreated'), 'js') ?>',
        loading: '<?= esc(lang('Provider.loading'), 'js') ?>',
        noRecords: '<?= esc(lang('Provider.noRecords'), 'js') ?>',
        searchPlaceholder: '<?= esc(lang('Provider.searchPlaceholder'), 'js') ?>',
        showing: '<?= esc(lang('Provider.showing'), 'js') ?>',
        infoEmpty: '<?= esc(lang('Provider.infoEmpty'), 'js') ?>',
        infoFiltered: '<?= esc(lang('Provider.infoFiltered'), 'js') ?>'
    };

    function escHtml(str) {
        return String(str).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
    }

    function initDataTable() {
        providersTable = $('#providersTable').DataTable({
            processing: true,
            serverSide: true,
            pageLength: 10,
            lengthMenu: [[10, 25, 50, 100], [10, 25, 50, 100]],
            order: [[1, 'asc']],
            dom: '<"flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3 mb-4"<"flex items-center gap-2"l><"flex items-center"f>>rt<"flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3 mt-4"<"text-sm text-gray-500"i><"flex"p>>',
            ajax: {
                url: '<?= base_url('providers/data') ?>',
                type: 'POST',
                headers: { [csrfHeader]: csrfToken, 'X-Requested-With': 'XMLHttpRequest' },
                data: function(d) {
                    d.type = activeTypeFilter;
                    d[csrfName] = csrfToken;
                },
                error: function() { console.error('DataTables AJAX error'); }
            },
            columns: [
                {
                    data: 'type',
                    orderable: true,
                    render: function(data) {
                        if (data === 'supplier') {
                            return '<div><div class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-semibold bg-cyan-50 text-cyan-700 border border-cyan-200"><i class="fas fa-truck text-xs"></i> ' + i18n.typeSupplier + '</div><div class="text-xs text-gray-500">' + i18n.typeLabel + '</div></div>';
                        }
                        return '<div><div class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-semibold bg-purple-50 text-purple-700 border border-purple-200"><i class="fas fa-user-md text-xs"></i> ' + i18n.typeClinicalProvider + '</div><div class="text-xs text-gray-500">' + i18n.typeLabel + '</div></div>';
                    }
                },
                {
                    data: 'name',
                    orderable: true,
                    render: function(data, type, row) {
                        var icon = row.type === 'supplier' ? '<i class="fas fa-truck text-cyan-700"></i>' : '<i class="fas fa-user-md text-purple-700"></i>';
                        var guestTag = '';
                        if (row.type === 'clinical_provider' && row.name === i18n.guestProvider) {
                            guestTag = ' <span class="inline-flex items-center px-1.5 py-0.5 rounded-full text-xs bg-amber-50 text-amber-700 border border-amber-200">' + i18n.guestProvider + '</span>';
                        }
                        return '<div class="flex items-center space-x-3"><span class="inline-flex items-center justify-center w-7 h-7">' + icon + '</span><div><div class="text-sm font-medium text-gray-900">' + escHtml(data || '-') + guestTag + '</div><div class="text-xs text-gray-500">' + i18n.nameLabel + '</div></div></div>';
                    }
                },
                {
                    data: 'contact_person',
                    orderable: false,
                    render: function(data) {
                        return data ? '<div class="flex items-center space-x-2"><i class="fas fa-address-book text-cyan-600 text-xs"></i><span class="text-sm text-gray-900">' + escHtml(data) + '</span></div>' : '<span class="text-gray-400">' + i18n.noContact + '</span>';
                    }
                },
                {
                    data: 'phone',
                    orderable: false,
                    render: function(data) {
                        return data ? '<div class="flex items-center space-x-2"><i class="fas fa-phone text-cyan-600 text-xs"></i><span class="text-sm text-gray-900">' + escHtml(data) + '</span></div>' : '<span class="text-gray-400">' + i18n.noPhone + '</span>';
                    }
                },
                {
                    data: 'status',
                    orderable: true,
                    render: function(data, type, row) {
                        if (row.type === 'supplier') {
                            var active = data === 'active';
                            var cls = active ? 'bg-emerald-50 text-emerald-700 border-emerald-200' : 'bg-gray-100 text-gray-600 border-gray-200';
                            return '<div class="flex items-center space-x-2"><span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold border ' + cls + '"><i class="fas ' + (active ? 'fa-check-circle' : 'fa-times-circle') + ' text-xs mr-1"></i>' + (active ? i18n.statusActive : i18n.statusInactive) + '</span></div>';
                        }
                        var due = data === 'balance_due';
                        var cls = due ? 'bg-amber-50 text-amber-700 border-amber-200' : 'bg-emerald-50 text-emerald-700 border-emerald-200';
                        return '<div class="flex items-center space-x-2"><span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold border ' + cls + '">' + escHtml(row.status_label) + '</span></div>';
                    }
                },
                {
                    data: 'created_at',
                    orderable: true,
                    render: function(data) {
                        if (!data) return '-';
                        return '<div class="flex items-center space-x-2"><i class="fas fa-calendar text-cyan-600 text-xs"></i><span class="text-sm text-gray-900">' + escHtml(data.substring(0, 10)) + '</span></div>';
                    }
                },
                {
                    data: null,
                    orderable: false,
                    render: function(data, type, row) {
                        if (!row.show_link) return '';
                        return '<a href="' + row.link + '" class="inline-flex items-center px-3 py-1.5 text-xs font-semibold text-cyan-700 bg-cyan-50 rounded-lg hover:bg-cyan-100 transition-colors"><i class="fas fa-external-link-alt mr-1.5"></i>' + row.link_label + '</a>';
                    }
                }
            ],
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
        $.ajax({
            url: '<?= base_url('providers/stats') ?>',
            type: 'GET',
            dataType: 'json',
            success: function(data) {
                $('#totalSuppliers').text(data.total_suppliers || 0);
                $('#activeSuppliers').text(data.active_suppliers || 0);
                $('#totalProviders').text(data.total_providers || 0);
                var outstanding = parseFloat(data.outstanding_balance || 0);
                $('#outstandingBalance').text(outstanding.toFixed(2));
            },
            error: function() {
                $('#totalSuppliers, #activeSuppliers, #totalProviders, #outstandingBalance').text('0');
            }
        });
    }

    // Type filter
    $('#typeFilters').on('click', '.filter-btn', function() {
        $('#typeFilters .filter-btn').removeClass('active');
        $(this).addClass('active');
        activeTypeFilter = $(this).data('type');
        providersTable.ajax.reload();
    });

    // Modal
    var $modal = $('#quickSupplierModal');
    var $nameInput = $('#quickSupplierName');
    var $status = $('#quickSupplierStatus');

    function openModal() { $modal.removeClass('hidden'); $nameInput.focus(); }
    function closeModal() {
        $modal.addClass('hidden');
        $nameInput.val('');
        $('#quickSupplierContact').val('');
        $('#quickSupplierPhone').val('');
        $('#quickSupplierEmail').val('');
        $status.addClass('hidden').text('');
    }

    $('#addSupplierBtn').on('click', openModal);

    $(document).on('click', '[data-modal-close]', function(e) {
        if (e.target === this || $(this).is('[data-modal-close]')) { closeModal(); }
    });

    $(document).on('keydown', function(e) {
        if (e.key === 'Escape' && !$modal.hasClass('hidden')) { closeModal(); }
    });

    $('#createQuickSupplierBtn').on('click', function() {
        var name = $nameInput.val().trim();
        if (!name) {
            $status.removeClass('hidden').addClass('text-red-600').text('<?= esc(lang('Provider.supplierNameRequired'), 'js') ?>');
            return;
        }

        var $btn = $(this);
        $btn.prop('disabled', true);
        var spinner = document.createElement('i');
        spinner.className = 'fas fa-spinner fa-spin mr-2';
        $btn.empty().append(spinner).append(document.createTextNode('<?= esc(lang('Provider.creatingSupplier'), 'js') ?>'));

        var formData = new FormData();
        formData.append('name', name);
        formData.append('contact_person', $('#quickSupplierContact').val().trim());
        formData.append('phone', $('#quickSupplierPhone').val().trim());
        formData.append('email', $('#quickSupplierEmail').val().trim());
        formData.append(csrfName, csrfToken);

        fetch('<?= base_url('inventory/suppliers/quick-create') ?>', {
            method: 'POST',
            headers: { 'X-Requested-With': 'XMLHttpRequest' },
            body: formData
        })
        .then(function(res) { return res.json(); })
        .then(function(json) {
            if (json.success) {
                closeModal();
                providersTable.ajax.reload();
                loadStats();
            } else if (json.duplicate) {
                $status.removeClass('hidden text-red-600').addClass('text-amber-600').text('<?= esc(lang('Provider.supplierExists'), 'js') ?>');
                setTimeout(function() { closeModal(); providersTable.ajax.reload(); }, 1500);
            } else {
                $status.removeClass('hidden').addClass('text-red-600').text(json.message || '<?= esc(lang('Provider.failedToCreate'), 'js') ?>');
            }
        })
        .catch(function() {
            $status.removeClass('hidden').addClass('text-red-600').text('<?= esc(lang('Provider.failedToCreate'), 'js') ?>');
        })
        .finally(function() {
            $btn.prop('disabled', false);
            var plusIcon = document.createElement('i');
            plusIcon.className = 'fas fa-plus mr-2';
            $btn.empty().append(plusIcon).append(document.createTextNode('<?= esc(lang('Provider.createSupplier'), 'js') ?>'));
        });
    });

    $(document).ready(function() {
        initDataTable();
        loadStats();
    });
})();
</script>
<?= $this->endSection() ?>
