<?= $this->extend('layouts/main_auth') ?>

<?= $this->section('content') ?>
<?php $isRtl = !empty($is_rtl); ?>

<?= view('inventory/partials/form_styles', ['is_rtl' => $isRtl]) ?>

<div class="v4-task-page"><header class="v4-page-heading"><div><h1><?= esc(lang('Inventory.addNewInventoryItem')) ?></h1><p><?= esc(lang('Inventory.addNewInventoryItemSubtitle')) ?></p></div><a class="v4-button" href="<?= esc(workflow_back('inventory',$return_to??null)) ?>"><?= esc(lang('V4.back')) ?></a></header><form action="<?= base_url('inventory/store') ?>" method="POST" class="v4-form v4-task-form">
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
                <a href="<?= workflow_cancel('inventory', $return_to ?? null) ?>" class="inline-flex items-center justify-center gap-2 px-6 py-2.5 border border-gray-200 text-gray-700 text-sm font-semibold rounded-xl hover:bg-gray-50 transition-colors">
                    <i class="fas fa-times text-xs"></i>
                    <?= lang('Inventory.cancel') ?>
                </a>
                <button type="submit" class="inline-flex items-center justify-center gap-2 px-8 py-2.5 bg-emerald-600 text-white text-sm font-semibold rounded-xl hover:bg-emerald-700 transition-colors shadow-sm">
                    <i class="fas fa-plus text-xs"></i>
                    <?= lang('Inventory.addItem') ?>
                </button>
            </div>
        </form></div>
<?= view('inventory/partials/quick_supplier_modal') ?>
<?= $this->endSection() ?>