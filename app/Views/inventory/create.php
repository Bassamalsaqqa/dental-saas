<?= $this->extend('layouts/main_auth') ?>

<?= $this->section('content') ?>
<?php $isRtl = !empty($is_rtl); ?>

<?= view('inventory/partials/form_styles', ['is_rtl' => $isRtl]) ?>

<div class="v3-surface-heading denta-inventory-workspace min-h-screen">
    <div class="v3-inventory-form-shell">

        <div class="mb-4">
            <div class="v3-inventory-form-command">
                <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center gap-4">
                    <div class="flex items-center gap-4">
                        <div class="v3-icon w-12 h-12 v3-corner flex items-center justify-center">
                            <i class="fas fa-boxes v3-text-on-primary text-lg"></i>
                        </div>
                        <div>
                            <h1 class="text-xl font-black v3-text-ink"><?= lang('Inventory.addNewInventoryItem') ?></h1>
                            <p class="text-sm v3-text-muted"><?= lang('Inventory.addNewInventoryItemSubtitle') ?></p>
                        </div>
                    </div>
                    <a href="<?= workflow_back('inventory', $return_to ?? null) ?>" class="inline-flex items-center gap-2 px-4 py-2.5 border v3-border v3-text-ink text-sm font-semibold v3-radius v3-hover-soft transition-colors v3-btn v3-btn--secondary">
                        <i class="fas <?= $isRtl ? 'fa-arrow-right' : 'fa-arrow-left' ?>"></i>
                        <?= lang('Inventory.backToInventory') ?>
                    </a>
                </div>
            </div>
        </div>

        <form action="<?= base_url('inventory/store') ?>" method="POST" class="space-y-5">
            <?= csrf_field() ?>
            <?php if (!empty($return_to)): ?>
                <input type="hidden" name="return_to" value="<?= esc($return_to) ?>">
            <?php endif; ?>
            <?= view('inventory/partials/form_fields', [
                'is_rtl' => $isRtl,
                'supplier_directory' => $supplier_directory ?? [],
                'currencies' => $currencies ?? ['ILS'],
            ]) ?>

            <div class="flex flex-col-reverse sm:flex-row justify-end gap-3 pt-2">
                <a href="<?= workflow_cancel('inventory', $return_to ?? null) ?>" class="inline-flex items-center justify-center gap-2 px-6 py-2.5 border v3-border v3-text-ink text-sm font-semibold v3-radius v3-hover-soft transition-colors v3-btn v3-btn--secondary">
                    <i class="fas fa-times text-xs"></i>
                    <?= lang('Inventory.cancel') ?>
                </a>
                <button type="submit" class="inline-flex items-center justify-center gap-2 px-8 py-2.5 v3-bg-primary v3-text-on-primary text-sm font-semibold v3-radius v3-hover-primary transition-colors v3-btn v3-btn--primary">
                    <i class="fas fa-plus text-xs"></i>
                    <?= lang('Inventory.addItem') ?>
                </button>
            </div>
        </form>

    </div>
</div>

<?= view('inventory/partials/quick_supplier_modal') ?>
<?= $this->endSection() ?>
