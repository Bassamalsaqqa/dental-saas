<?php
$modalReturnTo = (string) ($return_to ?? 'inventory');
$modalShouldOpen = old('source_modal') === 'inventory_quick_create';
$modalFullPageHref = base_url('inventory/create?' . http_build_query(['return_to' => $modalReturnTo]));
$isRtl = !empty($is_rtl);
?>
<?php if (has_permission('inventory', 'create')): ?>
    <?= view('inventory/partials/form_styles', ['is_rtl' => $isRtl]) ?>
<div id="newInventoryModal" data-confirm-discard="<?= esc(lang('V4.discardEntry'),'attr') ?>" class="inventory-create-modal v4-entry-modal hidden" role="dialog" aria-modal="true" aria-hidden="true" aria-labelledby="newInventoryModalTitle" dir="<?= $isRtl ? 'rtl' : 'ltr' ?>">
        <div class="inventory-create-modal__backdrop v4-entry-modal__backdrop" data-inventory-create-modal-close></div>
        <div class="inventory-create-modal__shell v4-entry-modal__shell" data-inventory-create-modal-close>
            <form id="newInventoryQuickCreateForm" action="<?= base_url('inventory/store') ?>" method="POST" class="inventory-create-modal__panel v4-entry-modal__panel v4-form" data-select2-dropdown-parent onclick="event.stopPropagation()">
                <?= csrf_field() ?>
                <input type="hidden" name="return_to" value="<?= esc($modalReturnTo) ?>">
                <input type="hidden" name="source_modal" value="inventory_quick_create">

<header class="v4-modal-header"><div><h2 id="newInventoryModalTitle"><?= esc(lang('Inventory.addNewInventoryItem')) ?></h2><a href="<?= esc($modalFullPageHref, 'attr') ?>" class="v4-text-link">
                                <?= esc(lang('UI.openFullPage')) ?>
                            </a></div><button type="button" class="v4-icon-button" data-inventory-create-modal-close aria-label="<?= esc(lang('UI.closeDrawer')) ?>"><i class="fas fa-times" aria-hidden="true"></i></button></header>
                <div class="v4-modal-body">
                    <?php if (session('error') && $modalShouldOpen): ?>
                        <div class="rounded-xl border border-red-200 bg-red-50 p-4 text-sm text-red-700">
                            <?= esc(session('error')) ?>
                        </div>
                    <?php endif; ?>

                    <?= view('inventory/partials/form_fields', [
                        'is_rtl' => $isRtl,
                        'supplier_directory' => $supplier_directory ?? [],
                        'currencies' => $currencies ?? ['ILS'],
                        'sectionClass' => 'inventory-create-modal-section rounded-2xl border border-slate-200 bg-white p-5 shadow-sm md:p-6',
                    ]) ?>
                </div>

                <div class="v4-modal-footer">
                    <button type="button" data-inventory-create-modal-close class="v4-button">
                        <?= esc(lang('Inventory.cancel')) ?>
                    </button>
                    <button type="submit" class="v4-button v4-button--primary">
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

        function openInventoryModal(trigger){const modal=mountInventoryModal();if(!modal)return;window.AppModal.open(modal,{trigger:trigger||document.activeElement,onOpen:initializeInventoryModalContent,focusSelector:'.select2-selection,input:not([type=hidden]):not([disabled]),button'});}

        function closeInventoryModal(shouldReset){const modal=document.getElementById('newInventoryModal');if(!window.AppModal.isOpen(modal))return;window.AppModal.close(modal,{onReset:()=>{if(shouldReset)resetInventoryModal(modal);}});}

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
