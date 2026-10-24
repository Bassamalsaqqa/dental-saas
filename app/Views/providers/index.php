<?= $this->extend('layouts/main_auth') ?>

<?= $this->section('content') ?>
<div class="v3-operational-view">

<?php
$isRtl = !empty($is_rtl);
$iconGap = $isRtl ? 'ml-2' : 'mr-2';
$iconGapSmall = $isRtl ? 'ml-1.5' : 'mr-1.5';
?>

<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.13.7/css/jquery.dataTables.min.css">



<div class="providers-page min-h-screen" dir="<?= $isRtl ? 'rtl' : 'ltr' ?>">
    <div class="w-full px-3 py-4 sm:px-5 sm:py-5">
        <section class="mb-5 overflow-hidden v3-radius">
            <div class="grid gap-5 sm:p-6 xl:grid-cols-[minmax(0,1fr)_auto] xl:items-center">
                <div class="flex min-w-0 items-start gap-4">
                    <span class="inline-flex h-14 w-14 shrink-0 items-center justify-center border">
                        <i class="fas fa-handshake text-xl"></i>
                    </span>
                    <div class="min-w-0">
                        <p class="text-xs uppercase tracking-[0.22em] v3-text-primary"><?= esc(lang('Provider.tableTitle')) ?></p>
                        <h1 class="mt-1 v3-section-title leading-tight v3-title"><?= esc(lang('Provider.pageTitle')) ?></h1>
                        <p class="mt-2 max-w-2xl text-sm font-semibold leading-6 v3-text-primary"><?= esc(lang('Provider.pageSubtitle')) ?></p>
                    </div>
                </div>
                <?php if (has_permission('inventory', 'edit')): ?>
                    <button type="button" id="addSupplierBtn" class="inline-flex items-center justify-center gap-2 px-5 py-3 text-sm v3-text-primary transition hover:-translate-y-0.5 v3-hover-primary focus:outline-none focus:ring-4 v3-focus-control v3-btn v3-btn--secondary">
                        <i class="fas fa-plus"></i>
                        <?= esc(lang('Provider.addSupplier')) ?>
                    </button>
                <?php endif; ?>
            </div>
        </section>

        <p class="mb-3 text-xs"><?= esc(lang('Finance.providerVoidBalanceHelp')) ?></p>
        <section class="mb-5 grid grid-cols-1 gap-3 sm:grid-cols-2 xl:grid-cols-4">
            <?php
            $cards = [
                ['id' => 'totalCombined', 'label' => lang('Provider.allProviders'), 'icon' => 'fa-handshake', 'tone' => 'v3-bg-panel v3-bg-panel', 'soft' => 'v3-bg-panel v3-bg-panel', 'text' => 'v3-text-primary'],
                ['id' => 'activeSuppliers', 'label' => lang('Provider.activeSuppliers'), 'icon' => 'fa-check-circle', 'tone' => 'v3-bg-panel v3-bg-panel', 'soft' => 'v3-bg-panel v3-bg-panel', 'text' => 'v3-text-primary'],
                ['id' => 'totalProviders', 'label' => lang('Provider.clinicalProviders'), 'icon' => 'fa-user-doctor', 'tone' => 'v3-bg-panel v3-bg-panel', 'soft' => 'v3-bg-panel v3-bg-panel', 'text' => 'v3-text-primary'],
                ['id' => 'outstandingBalance', 'label' => lang('Provider.outstandingBalance'), 'icon' => 'fa-wallet', 'tone' => 'v3-bg-panel v3-bg-panel', 'soft' => 'v3-bg-panel v3-bg-panel', 'text' => 'v3-text-warning'],
            ];
            ?>
            <?php foreach ($cards as $card): ?>
                <article class="<?= esc($card['tone']) ?> p-4">
                    <div class="flex items-center justify-between gap-3">
                        <div>
                            <p class="text-xs uppercase tracking-wide"><?= esc($card['label']) ?></p>
                            <p class="mt-2 v3-title" id="<?= esc($card['id']) ?>"><i class="fas fa-spinner fa-spin text-lg"></i></p>
                        </div>
                        <span class="inline-flex h-11 w-11 items-center justify-center border">
                            <i class="fas <?= esc($card['icon']) ?>"></i>
                        </span>
                    </div>
                </article>
            <?php endforeach; ?>
        </section>

        <section class="overflow-hidden v3-radius border">
            <div class="border-b px-4 py-4 sm:px-5">
                <div class="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
                    <div class="flex items-center gap-3">
                        <span class="inline-flex h-11 w-11 items-center justify-center v3-bg-paid v3-text-primary border v3-border-default">
                            <i class="fas fa-list"></i>
                        </span>
                        <div>
                            <h2 class="text-lg"><?= esc(lang('Provider.tableTitle')) ?></h2>
                            <p class="text-sm font-semibold"><?= esc(lang('Provider.pageSubtitle')) ?></p>
                        </div>
                    </div>
                    <div class="flex flex-wrap gap-2" id="typeFilters">
                        <button type="button" class="provider-filter active v3-btn v3-btn--secondary" data-type=""><?= esc(lang('Provider.filterAll')) ?></button>
                        <button type="button" class="provider-filter v3-btn v3-btn--secondary" data-type="supplier"><i class="fas fa-truck <?= $iconGapSmall ?>"></i><?= esc(lang('Provider.filterSuppliers')) ?></button>
                        <button type="button" class="provider-filter v3-btn v3-btn--secondary" data-type="clinical_provider"><i class="fas fa-user-doctor <?= $iconGapSmall ?>"></i><?= esc(lang('Provider.filterProviders')) ?></button>
                    </div>
                </div>
            </div>
            <div class="overflow-x-auto">
                <table id="providersTable" class="w-full min-w-full">
                    <thead>
                        <tr>
                            <th>
                                <div class="flex items-center gap-2">
                                    <i class="fas fa-user v3-text-primary"></i>
                                    <span><?= esc(lang('Provider.colName')) ?></span>
                                </div>
                            </th>
                            <th>
                                <div class="flex items-center gap-2">
                                    <i class="fas fa-tag v3-text-primary"></i>
                                    <span><?= esc(lang('Provider.colType')) ?></span>
                                </div>
                            </th>
                            <th>
                                <div class="flex items-center gap-2">
                                    <i class="fas fa-address-book v3-text-primary"></i>
                                    <span><?= esc(lang('Provider.colContact')) ?></span>
                                </div>
                            </th>
                            <th>
                                <div class="flex items-center gap-2">
                                    <i class="fas fa-chart-simple v3-text-primary"></i>
                                    <span><?= esc(lang('Provider.colStats')) ?></span>
                                </div>
                            </th>
                            <th>
                                <div class="flex items-center gap-2">
                                    <i class="fas fa-wallet v3-text-primary"></i>
                                    <span><?= esc(lang('Provider.openAmount')) ?></span>
                                </div>
                            </th>
                            <th>
                                <div class="flex items-center gap-2">
                                    <i class="fas fa-circle-info v3-text-primary"></i>
                                    <span><?= esc(lang('Provider.colStatus')) ?></span>
                                </div>
                            </th>
                            <th>
                                <div class="flex items-center gap-2">
                                    <i class="fas fa-calendar v3-text-primary"></i>
                                    <span><?= esc(lang('Provider.lastActivity')) ?></span>
                                </div>
                            </th>
                            <th>
                                <div class="flex items-center gap-2">
                                    <i class="fas fa-cogs v3-text-primary"></i>
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
    <div class="provider-scale-modal__backdrop fixed inset-0" onclick="closeSupplierModal()"></div>
    <div class="relative z-10 flex min-h-screen items-center justify-center p-4" onclick="closeSupplierModal()">
        <div class="provider-scale-modal__panel w-full max-w-lg overflow-hidden border" onclick="event.stopPropagation()">
            <div class="flex items-center justify-between gap-4 border-b px-5 py-4">
                <div class="flex items-center gap-3">
                    <span class="inline-flex h-11 w-11 items-center justify-center">
                        <i class="fas fa-truck"></i>
                    </span>
                    <div>
                        <h4 class="text-base"><?= esc(lang('Provider.quickAddSupplierTitle')) ?></h4>
                        <p class="mt-1 text-sm font-semibold"><?= esc(lang('Provider.quickAddSupplierHelp')) ?></p>
                    </div>
                </div>
                <button type="button" class="inline-flex h-9 w-9 items-center justify-center transition v3-btn v3-btn--secondary" onclick="closeSupplierModal()">
                    <i class="fas fa-times text-sm"></i>
                </button>
            </div>
            <div class="space-y-4">
                <div>
                    <label class="mb-1.5 block text-xs uppercase tracking-wider" for="quickSupplierName"><?= esc(lang('Provider.supplierName')) ?> *</label>
                    <input type="text" id="quickSupplierName" class="w-full border px-3 py-2.5 text-sm v3-focus-control focus:outline-none focus:ring-4 v3-focus-control v3-input" placeholder="<?= esc(lang('Provider.supplierName')) ?>" required>
                </div>
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div>
                        <label class="mb-1.5 block text-xs uppercase tracking-wider" for="quickSupplierContact"><?= esc(lang('Provider.contactPerson')) ?></label>
                        <input type="text" id="quickSupplierContact" class="w-full border px-3 py-2.5 text-sm v3-focus-control focus:outline-none focus:ring-4 v3-focus-control v3-input">
                    </div>
                    <div>
                        <label class="mb-1.5 block text-xs uppercase tracking-wider" for="quickSupplierPhone"><?= esc(lang('Provider.phone')) ?></label>
                        <input type="text" id="quickSupplierPhone" class="w-full border px-3 py-2.5 text-sm v3-focus-control focus:outline-none focus:ring-4 v3-focus-control v3-input">
                    </div>
                </div>
                <div>
                    <label class="mb-1.5 block text-xs uppercase tracking-wider" for="quickSupplierEmail"><?= esc(lang('Provider.email')) ?></label>
                    <input type="email" id="quickSupplierEmail" class="w-full border px-3 py-2.5 text-sm v3-focus-control focus:outline-none focus:ring-4 v3-focus-control v3-input">
                </div>
                <p id="quickSupplierStatus" class="text-xs hidden"></p>
            </div>
            <div class="flex flex-wrap justify-end gap-2 border-t px-5 py-4">
                <button type="button" onclick="closeSupplierModal()" class="inline-flex items-center gap-2 border px-4 py-2.5 text-sm font-semibold transition v3-btn v3-btn--secondary">
                    <i class="fas fa-times text-xs"></i>
                    <span><?= esc(lang('Provider.cancel')) ?></span>
                </button>
                <button type="button" id="createQuickSupplierBtn" class="inline-flex items-center gap-2 px-4 py-2.5 text-sm font-bold transition v3-btn v3-btn--secondary">
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
        const classes = supplier ? 'v3-bg-paid v3-text-primary' : 'v3-bg-scheduled v3-text-primary';
        const icon = supplier ? 'fa-truck' : 'fa-user-doctor';
        return '<span class="inline-flex items-center rounded-full px-2 py-px text-xs font-medium ' + classes + '"><i class="fas ' + icon + ' <?= $iconGapSmall ?>"></i>' + escHtml(row.type_label) + '</span>';
    }

    function statusBadge(row) {
        let classes = 'v3-bg-soft v3-text-ink';
        let icon = 'fa-circle-info';
        if (row.status === 'active' || row.status === 'settled') {
            classes = 'v3-bg-paid v3-text-primary';
            icon = 'fa-check-circle';
        } else if (row.status === 'inactive') {
            icon = 'fa-times-circle';
        } else if (row.status === 'balance_due') {
            classes = 'v3-bg-waiting v3-text-warning';
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
                    const icon = row.type === 'supplier' ? 'fa-truck v3-text-primary' : 'fa-user-doctor v3-text-primary';
                    return '<div class="flex items-center gap-2"><span class="inline-flex flex-shrink-0 items-center justify-center"><i class="fas ' + icon + '"></i></span><span class="text-sm font-medium v3-text-ink">' + escHtml(data || '-') + '</span></div>';
                } },
                { data: 'type', responsivePriority: 3, render: function(data, type, row) { return typeBadge(row); } },
                { data: 'contact', responsivePriority: 4, orderable: false, render: function(data) {
                    return data ? escHtml(data) : '<span class="v3-text-muted">' + i18n.noContact + '</span>';
                } },
                { data: 'activity_count', responsivePriority: 5, render: function(data, type, row) {
                    return '<span class="text-sm v3-text-ink">' + escHtml(row.activity_label || '-') + '</span>';
                } },
                { data: 'outstanding', responsivePriority: 2, render: function(data, type, row) {
                    const due = Number(data) > 0;
                    return '<span class="text-sm font-medium ' + (due ? 'v3-text-warning' : 'v3-text-ink') + '">' + escHtml(row.outstanding_label || '0.00') + '</span>';
                } },
                { data: 'status', responsivePriority: 6, render: function(data, type, row) {
                    return statusBadge(row);
                } },
                { data: 'last_activity', responsivePriority: 7, render: function(data) {
                    return '<span class="text-sm v3-text-ink">' + formatDate(data) + '</span>';
                } },
                { data: null, responsivePriority: 1, orderable: false, render: function(data, type, row) {
                    if (!row.show_link) return '';
                    const href = window.workflowContextUrl ? window.workflowContextUrl(row.link) : row.link;
                    return '<a href="' + escHtml(href) + '" class="inline-flex items-center justify-center w-8 h-8 rounded-lg v3-text-primary transition-colors duration-200 v3-hover-primary v3-hover-text-primary" title="' + escHtml(row.link_label) + '"><i class="fas fa-eye"></i></a>';
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
            statusEl.classList.add('v3-text-danger');
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
            statusEl.classList.add('v3-text-danger');
            statusEl.textContent = json.message || i18n.failedToCreate;
        })
        .catch(function() {
            statusEl.classList.remove('hidden');
            statusEl.classList.add('v3-text-danger');
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

</div>
<?= $this->endSection() ?>
