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
#providersTable th { background: #f8fafc !important; font-weight: 600 !important; color: #334155 !important; font-size: 13px !important; padding: 12px 10px !important; border-bottom: 2px solid #e2e8f0 !important; white-space: nowrap; }
#providersTable td { padding: 10px !important; vertical-align: middle !important; font-size: 13px !important; color: #475569 !important; border-bottom: 1px solid #f1f5f9 !important; }
#providersTable tbody tr:hover { background: #ecfeff !important; }
.dataTables_paginate .paginate_button.current { background: linear-gradient(to right, #06b6d4, #0891b2) !important; border: none !important; color: #fff !important; border-radius: 6px !important; }
.provider-type-badge { display: inline-flex; align-items: center; gap: 4px; padding: 2px 8px; border-radius: 9999px; font-size: 11px; font-weight: 600; }
.type-supplier { background: #ecfeff; color: #0891b2; }
.type-provider { background: #f5f3ff; color: #7c3aed; }
.status-active { background: #ecfdf5; color: #059669; }
.status-inactive { background: #f1f5f9; color: #64748b; }
.status-balance { background: #fef3c7; color: #92400e; }
.status-settled { background: #ecfdf5; color: #059669; }
.filter-btn { padding: 8px 16px; border-radius: 9999px; font-size: 13px; font-weight: 600; border: 1px solid #e2e8f0; background: white; color: #475569; cursor: pointer; transition: all 0.2s; }
.filter-btn:hover { background: #f0fdfa; border-color: #06b6d4; color: #0891b2; }
.filter-btn.active { background: linear-gradient(to right, #06b6d4, #0891b2); color: white; border-color: transparent; }
[dir="rtl"] .dataTables_length { margin-right: 0 !important; margin-left: auto !important; }
[dir="rtl"] .dataTables_filter { margin-right: auto !important; margin-left: 0 !important; }
</style>

<!-- Header Section -->
<div class="relative mb-8 overflow-hidden">
    <div class="absolute inset-0 bg-gradient-to-br from-cyan-500/10 to-teal-600/5 rounded-3xl"></div>
    <div class="absolute -top-40 -right-40 w-80 h-80 bg-gradient-to-br from-cyan-400/20 to-teal-600/20 rounded-full blur-3xl"></div>
    <div class="absolute -bottom-40 -left-40 w-80 h-80 bg-gradient-to-br from-cyan-400/20 to-sky-600/20 rounded-full blur-3xl"></div>
    <div class="container mx-auto relative z-10 px-3 sm:px-4 py-4 sm:py-6">
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

        <!-- Stat Cards -->
        <div class="mb-8">
            <div class="grid grid-cols-2 xl:grid-cols-4 gap-4 sm:gap-6">
                <!-- Total Suppliers -->
                <div class="group relative">
                    <div class="absolute inset-0 bg-gradient-to-br from-cyan-500/20 to-sky-600/20 rounded-3xl blur-xl opacity-0 group-hover:opacity-100 transition-all duration-500"></div>
                    <div class="relative backdrop-blur-xl bg-white/80 border border-white/30 rounded-2xl p-5 shadow-lg group-hover:shadow-cyan-500/20 transition-all duration-500 hover:-translate-y-1 h-full flex flex-col">
                        <div class="flex items-center justify-between mb-3">
                            <div class="p-3 rounded-xl bg-gradient-to-br from-cyan-500 to-sky-600 text-white shadow-lg">
                                <i class="fas fa-truck text-xl"></i>
                            </div>
                        </div>
                        <p class="text-2xl font-bold text-gray-900" id="totalSuppliers"><i class="fas fa-spinner fa-spin text-gray-400 text-sm"></i></p>
                        <p class="text-xs text-gray-500 mt-1"><?= esc(lang('Provider.totalProviders')) ?></p>
                    </div>
                </div>

                <!-- Active Suppliers -->
                <div class="group relative">
                    <div class="absolute inset-0 bg-gradient-to-br from-emerald-500/20 to-green-600/20 rounded-3xl blur-xl opacity-0 group-hover:opacity-100 transition-all duration-500"></div>
                    <div class="relative backdrop-blur-xl bg-white/80 border border-white/30 rounded-2xl p-5 shadow-lg group-hover:shadow-emerald-500/20 transition-all duration-500 hover:-translate-y-1 h-full flex flex-col">
                        <div class="flex items-center justify-between mb-3">
                            <div class="p-3 rounded-xl bg-gradient-to-br from-emerald-500 to-green-600 text-white shadow-lg">
                                <i class="fas fa-check-circle text-xl"></i>
                            </div>
                        </div>
                        <p class="text-2xl font-bold text-gray-900" id="activeSuppliers"><i class="fas fa-spinner fa-spin text-gray-400 text-sm"></i></p>
                        <p class="text-xs text-gray-500 mt-1"><?= esc(lang('Provider.activeSuppliers')) ?></p>
                    </div>
                </div>

                <!-- Clinical Providers -->
                <div class="group relative">
                    <div class="absolute inset-0 bg-gradient-to-br from-purple-500/20 to-violet-600/20 rounded-3xl blur-xl opacity-0 group-hover:opacity-100 transition-all duration-500"></div>
                    <div class="relative backdrop-blur-xl bg-white/80 border border-white/30 rounded-2xl p-5 shadow-lg group-hover:shadow-purple-500/20 transition-all duration-500 hover:-translate-y-1 h-full flex flex-col">
                        <div class="flex items-center justify-between mb-3">
                            <div class="p-3 rounded-xl bg-gradient-to-br from-purple-500 to-violet-600 text-white shadow-lg">
                                <i class="fas fa-user-md text-xl"></i>
                            </div>
                        </div>
                        <p class="text-2xl font-bold text-gray-900" id="totalProviders"><i class="fas fa-spinner fa-spin text-gray-400 text-sm"></i></p>
                        <p class="text-xs text-gray-500 mt-1"><?= esc(lang('Provider.clinicalProviders')) ?></p>
                    </div>
                </div>

                <!-- Outstanding -->
                <div class="group relative">
                    <div class="absolute inset-0 bg-gradient-to-br from-amber-500/20 to-yellow-600/20 rounded-3xl blur-xl opacity-0 group-hover:opacity-100 transition-all duration-500"></div>
                    <div class="relative backdrop-blur-xl bg-white/80 border border-white/30 rounded-2xl p-5 shadow-lg group-hover:shadow-amber-500/20 transition-all duration-500 hover:-translate-y-1 h-full flex flex-col">
                        <div class="flex items-center justify-between mb-3">
                            <div class="p-3 rounded-xl bg-gradient-to-br from-amber-500 to-yellow-600 text-white shadow-lg">
                                <i class="fas fa-coins text-xl"></i>
                            </div>
                        </div>
                        <p class="text-2xl font-bold text-gray-900" id="outstandingBalance"><i class="fas fa-spinner fa-spin text-gray-400 text-sm"></i></p>
                        <p class="text-xs text-gray-500 mt-1"><?= esc(lang('Provider.outstandingBalance')) ?></p>
                    </div>
                </div>
            </div>
        </div>

        <!-- DataTable Section -->
        <div class="bg-white/80 backdrop-blur-sm border border-white/30 rounded-xl p-6 shadow-lg">
            <!-- Type Filter -->
            <div class="flex flex-col sm:flex-row gap-3 mb-4 items-start sm:items-center justify-between">
                <div class="flex flex-wrap gap-2" id="typeFilters">
                    <button class="filter-btn active" data-type=""><?= esc(lang('Provider.filterAll')) ?></button>
                    <button class="filter-btn" data-type="supplier"><i class="fas fa-truck mr-1"></i><?= esc(lang('Provider.filterSuppliers')) ?></button>
                    <button class="filter-btn" data-type="clinical_provider"><i class="fas fa-user-md mr-1"></i><?= esc(lang('Provider.filterProviders')) ?></button>
                </div>
            </div>

            <table id="providersTable" class="w-full">
                <thead>
                    <tr>
                        <th><?= esc(lang('Provider.colType')) ?></th>
                        <th><?= esc(lang('Provider.colName')) ?></th>
                        <th><?= esc(lang('Provider.colContact')) ?></th>
                        <th><?= esc(lang('Provider.colPhone')) ?></th>
                        <th><?= esc(lang('Provider.colStatus')) ?></th>
                        <th><?= esc(lang('Provider.colCreated')) ?></th>
                        <th><?= esc(lang('Provider.colActions')) ?></th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
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
        loading: '<?= esc(lang('Provider.loading'), 'js') ?>',
        noRecords: '<?= esc(lang('Provider.noRecords'), 'js') ?>',
        noMatching: '<?= esc(lang('Provider.noMatching'), 'js') ?>',
        searchPlaceholder: '<?= esc(lang('Provider.searchPlaceholder'), 'js') ?>',
        showing: '<?= esc(lang('Provider.showing'), 'js') ?>',
        infoEmpty: '<?= esc(lang('Provider.infoEmpty'), 'js') ?>',
        infoFiltered: '<?= esc(lang('Provider.infoFiltered'), 'js') ?>',
        hasInventoryView: <?= has_permission('inventory', 'view') ? 'true' : 'false' ?>,
        hasFinanceView: <?= has_permission('finance', 'view') ? 'true' : 'false' ?>,
        hasInventoryEdit: <?= has_permission('inventory', 'edit') ? 'true' : 'false' ?>
    };

    function initDataTable() {
        providersTable = $('#providersTable').DataTable({
            processing: true,
            serverSide: true,
            responsive: false,
            pageLength: 25,
            lengthMenu: [[10, 25, 50, 100], [10, 25, 50, 100]],
            order: [[1, 'asc']],
            ordering: true,
            searching: true,
            dom: '<"flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3 mb-4"<"flex items-center gap-2"l><"flex items-center"f>>rt<"flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3 mt-4"<"text-sm text-slate-500"i><"flex"p>>',
            ajax: {
                url: '<?= base_url('providers/data') ?>',
                type: 'POST',
                headers: { [csrfHeader]: csrfToken, 'X-Requested-With': 'XMLHttpRequest' },
                data: function(d) {
                    d.type = activeTypeFilter;
                    d[csrfName] = csrfToken;
                },
                error: function() {
                    console.error('DataTables AJAX error');
                }
            },
            columns: [
                {
                    data: 'type',
                    width: '80px',
                    orderable: true,
                    render: function(data) {
                        if (data === 'supplier') {
                            return '<span class="provider-type-badge type-supplier"><i class="fas fa-truck text-xs"></i> ' + i18n.typeSupplier + '</span>';
                        }
                        return '<span class="provider-type-badge type-provider"><i class="fas fa-user-md text-xs"></i> ' + i18n.typeClinicalProvider + '</span>';
                    }
                },
                {
                    data: 'name',
                    orderable: true,
                    render: function(data, type, row) {
                        var guestTag = '';
                        if (row.type === 'clinical_provider' && row.name === i18n.guestProvider) {
                            guestTag = ' <span class="provider-type-badge type-supplier" style="font-size:10px">' + i18n.guestProvider + '</span>';
                        }
                        var escaped = String(data).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
                        return '<span class="font-semibold text-slate-800">' + escaped + '</span>' + guestTag;
                    }
                },
                {
                    data: 'contact_person',
                    orderable: false,
                    render: function(data) { return data || '<span class="text-slate-400">' + i18n.noContact + '</span>'; }
                },
                {
                    data: 'phone',
                    orderable: false,
                    render: function(data) { return data || '<span class="text-slate-400">' + i18n.noPhone + '</span>'; }
                },
                {
                    data: 'status',
                    width: '100px',
                    orderable: true,
                    render: function(data, type, row) {
                        if (row.type === 'supplier') {
                            var cls = data === 'active' ? 'status-active' : 'status-inactive';
                            return '<span class="provider-type-badge ' + cls + '">' + (data === 'active' ? i18n.statusActive : i18n.statusInactive) + '</span>';
                        }
                        var cls = data === 'balance_due' ? 'status-balance' : 'status-settled';
                        return '<span class="provider-type-badge ' + cls + '">' + (data === 'balance_due' ? row.status_label : row.status_label) + '</span>';
                    }
                },
                {
                    data: 'created_at',
                    width: '110px',
                    orderable: true,
                    render: function(data) {
                        if (!data) return '-';
                        return data.substring(0, 10);
                    }
                },
                {
                    data: null,
                    width: '80px',
                    orderable: false,
                    render: function(data, type, row) {
                        if (!row.show_link) return '';
                        return '<a href="' + row.link + '" class="inline-flex items-center px-2 py-1 text-xs font-semibold text-cyan-600 hover:text-cyan-800 hover:bg-cyan-50 rounded-lg transition-colors"><i class="fas fa-external-link-alt mr-1"></i>' + row.link_label + '</a>';
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

    // Type filter buttons
    $('#typeFilters').on('click', '.filter-btn', function() {
        $('#typeFilters .filter-btn').removeClass('active');
        $(this).addClass('active');
        activeTypeFilter = $(this).data('type');
        providersTable.ajax.reload();
    });

    // Modal logic
    var $modal = $('#quickSupplierModal');
    var $nameInput = $('#quickSupplierName');
    var $status = $('#quickSupplierStatus');

    function openModal() {
        $modal.removeClass('hidden');
        $nameInput.focus();
    }

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
        if (e.target === this || $(this).is('[data-modal-close]')) {
            closeModal();
        }
    });

    $(document).on('keydown', function(e) {
        if (e.key === 'Escape' && !$modal.hasClass('hidden')) {
            closeModal();
        }
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

    // Init
    $(document).ready(function() {
        initDataTable();
        loadStats();
    });
})();
</script>
<?= $this->endSection() ?>
