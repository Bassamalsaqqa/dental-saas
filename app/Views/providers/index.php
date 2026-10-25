<?php helper('asset'); ?><?= $this->extend('layouts/main_auth') ?><?= $this->section('content') ?><?php $isRtl=!empty($is_rtl);$iconGap=$isRtl?'ml-2':'mr-2';$iconGapSmall=$isRtl?'ml-1.5':'mr-1.5'; ?><link rel="stylesheet" href="<?= base_url('assets/css/v4-operations.css') ?>?v=<?= esc(asset_version('assets/css/v4-operations.css')) ?>"><header class="v4-page-heading"><div><h1><?= esc(lang('Provider.pageTitle')) ?></h1><p><?= esc(lang('Provider.pageSubtitle')) ?></p></div><?php if(has_permission('inventory','edit')): ?><button id="addSupplierBtn" type="button" class="v4-button v4-button--primary"><i class="fas fa-plus" aria-hidden="true"></i><?= esc(lang('Provider.addSupplier')) ?></button><?php endif; ?></header><p class="v4-provider-history-help"><?= esc(lang('Finance.providerVoidBalanceHelp')) ?></p><details class="v4-clinical-summary"><summary><?= esc(lang('V4.recordSummary')) ?></summary><dl><?php foreach(['totalCombined'=>'Provider.allProviders','activeSuppliers'=>'Provider.activeSuppliers','totalProviders'=>'Provider.clinicalProviders','outstandingBalance'=>'Provider.outstandingBalance'] as $id=>$label): ?><div><dt><?= esc(lang($label)) ?></dt><dd id="<?= $id ?>">&mdash;</dd></div><?php endforeach; ?></dl></details><section class="v4-surface v4-ledger"><header class="v4-ledger-header"><h2><?= esc(lang('Provider.tableTitle')) ?></h2><div id="typeFilters" class="v4-segments"><?php foreach([''=>'Provider.filterAll','supplier'=>'Provider.filterSuppliers','clinical_provider'=>'Provider.filterProviders'] as $value=>$label): ?><button type="button" class="provider-filter <?= $value===''?'active':'' ?>" data-type="<?= $value ?>" aria-pressed="<?= $value===''?'true':'false' ?>"><?= esc(lang($label)) ?></button><?php endforeach; ?></div></header><div class="v4-ledger-data" data-v4-records="providers" data-v4-title-index="0" data-v4-copy-actions="true"><table id="providersTable"><thead><tr><?php foreach(['colName','colType','colContact','colStats','openAmount','colStatus','lastActivity','colActions'] as $label): ?><th><?= esc(lang('Provider.'.$label)) ?></th><?php endforeach; ?></tr></thead><tbody></tbody></table><div class="v4-mobile-records" data-v4-record-cards aria-live="polite"></div></div></section><script src="<?= base_url('assets/js/v4-record-cards.js') ?>?v=<?= esc(asset_version('assets/js/v4-record-cards.js')) ?>"></script><div id="quickSupplierModal" class="v4-entry-modal hidden" data-confirm-discard="<?= esc(lang('V4.discardEntry'),'attr') ?>" role="dialog" aria-modal="true" aria-hidden="true" dir="<?= $isRtl ? 'rtl' : 'ltr' ?>">
    <div class="v4-entry-modal__backdrop" onclick="closeSupplierModal()"></div>
    <div class="v4-entry-modal__shell" onclick="closeSupplierModal()">
        <div class="v4-entry-modal__panel v4-supplier-entry" onclick="event.stopPropagation()">
            <div class="v4-modal-header">
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
            <div class="v4-modal-body v4-form space-y-4">
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
            <div class="v4-modal-footer">
                <button type="button" onclick="closeSupplierModal()" class="v4-button">
                    <i class="fas fa-times text-xs"></i>
                    <span><?= esc(lang('Provider.cancel')) ?></span>
                </button>
                <button type="button" id="createQuickSupplierBtn" class="v4-button v4-button--primary">
                    <i class="fas fa-plus text-xs"></i>
                    <span><?= esc(lang('Provider.createSupplier')) ?></span>
                </button>
            </div>
        </div>
    </div>
</div>



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

function openSupplierModal(event){const modal=mountSupplierModal();if(!modal)return;window.AppModal.open(modal,{trigger:event?.currentTarget||document.activeElement,focusSelector:'#quickSupplierName'});}
function closeSupplierModal(saved=false){const modal=document.getElementById('quickSupplierModal');window.AppModal.close(modal,{skipDiscard:saved===true,onReset:resetSupplierModalFields});}
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
                $('#totalCombined, #activeSuppliers, #totalProviders, #outstandingBalance').text('\u2014');
            });
    }

    $('#typeFilters').on('click', '.provider-filter', function() {
        $('#typeFilters .provider-filter').removeClass('active').attr('aria-pressed','false');
        $(this).addClass('active').attr('aria-pressed','true');
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
                closeSupplierModal(true);
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
