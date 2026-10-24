<?php
$modalReturnTo = (string) ($return_to ?? 'inventory');
$modalShouldOpen = old('source_modal') === 'inventory_quick_create';
$modalFullPageHref = base_url('inventory/create?' . http_build_query(['return_to' => $modalReturnTo]));
$isRtl = !empty($is_rtl);
?>
<?php if (has_permission('inventory', 'create')): ?>
    <?= view('inventory/partials/form_styles', ['is_rtl' => $isRtl]) ?>
    <style>
    .inventory-create-modal {
        visibility: hidden;
        pointer-events: none;
        transition: visibility 0s linear 240ms;
    }
    .inventory-create-modal.is-open {
        visibility: visible;
        pointer-events: auto;
        transition-delay: 0s;
    }
    .inventory-create-modal__backdrop {
        opacity: 0;
        transition: opacity 220ms ease;
    }
    .inventory-create-modal.is-open .inventory-create-modal__backdrop {
        opacity: 1;
    }
    .inventory-create-modal__panel {
        display: grid !important;
        grid-template-rows: auto minmax(0, 1fr) auto;
        opacity: 0;
        overflow: clip;
        transform: scale(0.86);
        transform-origin: center center;
        height: min(92dvh, 52rem);
        transition:
            opacity 180ms ease,
            transform 420ms cubic-bezier(0.34, 1.56, 0.64, 1);
        will-change: transform, opacity;
    }
    .inventory-create-modal.is-open .inventory-create-modal__panel {
        opacity: 1;
        transform: scale(1);
    }
    .inventory-create-modal .select2-container {
        width: 100% !important;
    }
    .inventory-create-modal .inventory-create-modal-section {
        background:var(--denta-alabaster);
        box-shadow:none;
    }
    .inventory-create-modal .inventory-create-modal-section .inventory-create-section-title {
        margin-bottom: 1.25rem;
        padding-bottom: 0;
        border-bottom: 0;
        font-size: 1.125rem;
        color: #111827;
    }
    .inventory-create-modal .inventory-create-modal-section .inventory-create-section-title i {
        height: 2.5rem;
        width: 2.5rem;
        border-radius: 0.75rem;
        color:var(--denta-ivory-input);
        box-shadow:none;
    }
    .inventory-create-modal .inventory-create-section-title .icon-emerald { background:var(--denta-alabaster); }
    .inventory-create-modal .inventory-create-section-title .icon-amber { background:var(--denta-alabaster); }
    .inventory-create-modal .inventory-create-section-title .icon-indigo { background:var(--denta-alabaster); }
    .inventory-create-modal .inventory-create-section-title .icon-orange { background:var(--denta-alabaster); }
    .inventory-create-modal .inventory-create-section-title .icon-violet { background:var(--denta-alabaster); }
    .inventory-create-modal .inventory-create-form-field,
    .inventory-create-modal .inventory-create-select,
    .inventory-create-modal .inventory-create-textarea {
        border-width: 2px;
        border-color: var(--denta-taupe-border);
        border-radius: 0.75rem;
        color: var(--denta-forest);
        font-weight: 500;
        box-shadow:none;
    }
    .inventory-create-modal .inventory-create-form-field:focus,
    .inventory-create-modal .inventory-create-select:focus,
    .inventory-create-modal .inventory-create-textarea:focus {
        border-color: #14b8a6;
        box-shadow:none;
    }
    @media (prefers-reduced-motion: reduce) {
        .inventory-create-modal__backdrop,
        .inventory-create-modal__panel {
            transition: none;
        }
    }
    @media (max-width: 640px) {
        .inventory-create-modal__shell {
            align-items: stretch;
            padding: 0;
        }
        .inventory-create-modal__panel {
            width: 100vw;
            height: 100dvh;
            min-height: 100dvh;
            max-height: 100dvh;
            border-radius: 0;
        }
    }
    </style>

    <div id="newInventoryModal" class="app-scale-modal inventory-create-modal fixed inset-0 z-[2147483600] hidden overflow-hidden" role="dialog" aria-modal="true" aria-hidden="true" aria-labelledby="newInventoryModalTitle" dir="<?= $isRtl ? 'rtl' : 'ltr' ?>">
        <div class="inventory-create-modal__backdrop fixed inset-0 v3-backdrop backdrop-blur-sm" data-inventory-create-modal-close></div>
        <div class="inventory-create-modal__shell relative z-10 flex h-full items-center justify-center p-4" data-inventory-create-modal-close>
            <form id="newInventoryQuickCreateForm" action="<?= base_url('inventory/store') ?>" method="POST" class="inventory-create-modal__panel flex w-full max-w-5xl flex-col overflow-hidden v3-radius border v3-border-default v3-bg-panel v3-elevation-none" data-select2-dropdown-parent onclick="event.stopPropagation()">
                <?= csrf_field() ?>
                <input type="hidden" name="return_to" value="<?= esc($modalReturnTo) ?>">
                <input type="hidden" name="source_modal" value="inventory_quick_create">

                <div class="flex flex-shrink-0 items-start justify-between gap-4 border-b v3-border-default v3-bg-panel px-5 py-4 backdrop-blur">
                    <div class="flex items-center gap-3">
                        <span class="inline-flex h-11 w-11 items-center justify-center v3-radius v3-bg-panel v3-bg-panel v3-bg-panel v3-text-on-primary v3-elevation-none">
                            <i class="fas fa-boxes"></i>
                        </span>
                        <div>
                            <h2 id="newInventoryModalTitle" class="text-lg font-bold v3-text-ink"><?= esc(lang('Inventory.addNewInventoryItem')) ?></h2>
                            <a href="<?= esc($modalFullPageHref, 'attr') ?>" class="mt-1 inline-flex text-xs font-semibold v3-text-primary v3-hover-text-primary">
                                <?= esc(lang('UI.openFullPage')) ?>
                            </a>
                        </div>
                    </div>
                    <button type="button" class="inline-flex h-9 w-9 items-center justify-center rounded-full v3-text-muted transition v3-hover-soft v3-hover-text-ink v3-btn v3-btn--icon" data-inventory-create-modal-close aria-label="<?= esc(lang('UI.closeDrawer')) ?>">
                        <i class="fas fa-times text-sm"></i>
                    </button>
                </div>

                <div class="min-h-0 flex-1 space-y-5 overflow-y-auto v3-bg-panel p-5">
                    <?php if (session('error') && $modalShouldOpen): ?>
                        <div class="v3-radius border v3-border-default v3-bg-alert p-4 text-sm v3-text-danger">
                            <?= esc(session('error')) ?>
                        </div>
                    <?php endif; ?>

                    <?= view('inventory/partials/form_fields', [
                        'is_rtl' => $isRtl,
                        'supplier_directory' => $supplier_directory ?? [],
                        'currencies' => $currencies ?? ['ILS'],
                        'sectionClass' => 'inventory-create-modal-section v3-radius border v3-border-default v3-bg-panel p-5 v3-elevation-none md:p-6',
                    ]) ?>
                </div>

                <div class="flex flex-shrink-0 flex-col-reverse gap-3 border-t v3-border-default v3-bg-panel px-5 py-4 backdrop-blur sm:flex-row sm:justify-end">
                    <button type="button" data-inventory-create-modal-close class="inline-flex items-center justify-center v3-radius border v3-border-default px-5 py-2.5 text-sm font-semibold v3-text-ink transition v3-hover-soft v3-btn v3-btn--secondary">
                        <?= esc(lang('Inventory.cancel')) ?>
                    </button>
                    <button type="submit" class="inline-flex items-center justify-center v3-radius v3-bg-panel v3-bg-panel v3-bg-panel px-5 py-2.5 text-sm font-bold v3-text-on-primary v3-elevation-none transition hover:v3-bg-panel hover:v3-bg-panel hover:v3-elevation-none v3-btn v3-btn--primary">
                        <i class="fas fa-plus me-2"></i>
                        <?= esc(lang('Inventory.addItem')) ?>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <?= view('inventory/partials/quick_supplier_modal') ?>

    <script>
    (function () {
        if (window.__inventoryQuickCreateModalBound) {
            return;
        }
        window.__inventoryQuickCreateModalBound = true;

        let inventoryModalTrigger = null;
        let inventoryModalCloseTimer = null;

        function mountInventoryModal() {
            const modal = document.getElementById('newInventoryModal');
            if (modal && modal.parentElement !== document.body) {
                document.body.appendChild(modal);
            }
            if (modal && modal.dataset.closeControlsBound !== '1') {
                modal.dataset.closeControlsBound = '1';
                modal.querySelectorAll('button[data-inventory-create-modal-close], a[data-inventory-create-modal-close]').forEach(function (control) {
                    control.addEventListener('click', function (event) {
                        event.preventDefault();
                        closeInventoryModal(true);
                    });
                });
            }
            return modal;
        }

        function resetInventoryModal(modal) {
            const form = modal ? modal.querySelector('form') : null;
            if (!form) {
                return;
            }
            form.reset();
            form.querySelectorAll('select').forEach(function (select) {
                if (window.jQuery?.fn?.select2 && window.jQuery(select).hasClass('select2-hidden-accessible')) {
                    window.jQuery(select).val(select.value || '').trigger('change.select2');
                }
            });
        }

        function initializeInventoryModalContent(modal) {
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
            return Promise.allSettled(tasks);
        }

        function openInventoryModal(trigger) {
            const modal = mountInventoryModal();
            if (!modal) {
                return;
            }
            inventoryModalTrigger = trigger || document.activeElement;
            clearTimeout(inventoryModalCloseTimer);
            modal.classList.remove('hidden');
            modal.setAttribute('aria-hidden', 'false');
            document.body.classList.add('modal-open');
            document.documentElement.classList.add('modal-open');
            initializeInventoryModalContent(modal).finally(function () {
                requestAnimationFrame(function () {
                    modal.classList.add('is-open');
                    const firstField = modal.querySelector('input[name="item_name"]');
                    if (firstField) {
                        window.setTimeout(function () { firstField.focus(); }, 80);
                    }
                });
            });
        }

        function closeInventoryModal(shouldReset) {
            const modal = document.getElementById('newInventoryModal');
            if (!modal || modal.classList.contains('hidden')) {
                return;
            }
            modal.classList.remove('is-open');
            modal.setAttribute('aria-hidden', 'true');
            document.body.classList.remove('modal-open');
            document.documentElement.classList.remove('modal-open');
            clearTimeout(inventoryModalCloseTimer);
            inventoryModalCloseTimer = window.setTimeout(function () {
                modal.classList.add('hidden');
                if (shouldReset) {
                    resetInventoryModal(modal);
                }
                if (inventoryModalTrigger && typeof inventoryModalTrigger.focus === 'function') {
                    inventoryModalTrigger.focus();
                }
            }, 240);
        }

        document.addEventListener('click', function (event) {
            const trigger = event.target.closest('[data-inventory-create-modal-open]');
            if (trigger) {
                event.preventDefault();
                openInventoryModal(trigger);
                return;
            }
            if (event.target.closest('[data-inventory-create-modal-close]')) {
                event.preventDefault();
                closeInventoryModal(true);
            }
        });

        document.addEventListener('keydown', function (event) {
            if (event.key === 'Escape') {
                closeInventoryModal(true);
            }
        });

        if (<?= $modalShouldOpen ? 'true' : 'false' ?>) {
            window.setTimeout(function () {
                openInventoryModal(document.querySelector('[data-inventory-create-modal-open]'));
            }, 120);
        }
    })();
    </script>
<?php endif; ?>
