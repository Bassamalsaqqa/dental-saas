<?= $this->extend('layouts/main_auth') ?>

<?= $this->section('content') ?>
<?php $isRtl = !empty($is_rtl); ?>

<?= view('inventory/partials/form_styles', ['is_rtl' => $isRtl]) ?>

<div class="min-h-screen bg-gradient-to-br from-slate-50 via-white to-slate-100">
    <div class="container mx-auto px-4 sm:px-6 lg:px-8 py-6">

        <div class="mb-8">
            <div class="bg-white rounded-2xl border border-gray-200 shadow-sm p-6">
                <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center gap-4">
                    <div class="flex items-center gap-4">
                        <div class="w-12 h-12 bg-gradient-to-br from-emerald-500 to-teal-600 rounded-2xl flex items-center justify-center shadow-lg">
                            <i class="fas fa-boxes text-white text-lg"></i>
                        </div>
                        <div>
                            <h1 class="text-xl font-black text-gray-900"><?= lang('Inventory.addNewInventoryItem') ?></h1>
                            <p class="text-sm text-gray-500"><?= lang('Inventory.addNewInventoryItemSubtitle') ?></p>
                        </div>
                    </div>
                    <a href="<?= nav_back('inventory') ?>" class="inline-flex items-center gap-2 px-4 py-2.5 border border-gray-200 text-gray-700 text-sm font-semibold rounded-xl hover:bg-gray-50 transition-colors">
                        <i class="fas <?= $isRtl ? 'fa-arrow-right' : 'fa-arrow-left' ?>"></i>
                        <?= lang('Inventory.backToInventory') ?>
                    </a>
                </div>
            </div>
        </div>

        <form action="<?= base_url('inventory/store') ?>" method="POST" class="space-y-5">
            <?= csrf_field() ?>
            <?= view('inventory/partials/form_fields', [
                'is_rtl' => $isRtl,
                'supplier_directory' => $supplier_directory ?? [],
                'currencies' => $currencies ?? ['ILS'],
            ]) ?>

            <div class="flex flex-col-reverse sm:flex-row justify-end gap-3 pt-2">
                <a href="<?= nav_back('inventory') ?>" class="inline-flex items-center justify-center gap-2 px-6 py-2.5 border border-gray-200 text-gray-700 text-sm font-semibold rounded-xl hover:bg-gray-50 transition-colors">
                    <i class="fas fa-times text-xs"></i>
                    <?= lang('Inventory.cancel') ?>
                </a>
                <button type="submit" class="inline-flex items-center justify-center gap-2 px-8 py-2.5 bg-emerald-600 text-white text-sm font-semibold rounded-xl hover:bg-emerald-700 transition-colors shadow-sm">
                    <i class="fas fa-plus text-xs"></i>
                    <?= lang('Inventory.addItem') ?>
                </button>
            </div>
        </form>

    </div>
</div>

<?= view('inventory/partials/quick_supplier_modal') ?>
<?= $this->endSection() ?>
