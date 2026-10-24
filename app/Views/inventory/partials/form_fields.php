<?php
$isRtl = !empty($is_rtl);
$validation = session()->getFlashdata('validation');
$errors = session()->getFlashdata('errors') ?? [];
$sectionClass = $sectionClass ?? 'inventory-create-form-section';
$fieldError = static function (string $field) use ($validation, $errors): ?string {
    if ($validation && method_exists($validation, 'hasError') && $validation->hasError($field)) {
        return $validation->getError($field);
    }

    return is_array($errors) && isset($errors[$field]) ? (string) $errors[$field] : null;
};
$errorLine = static function (string $field) use ($fieldError): string {
    $message = $fieldError($field);
    if ($message === null || $message === '') {
        return '';
    }

    return '<p class="text-red-500 text-sm mt-1.5"><i class="fas fa-exclamation-circle mr-1"></i>' . esc($message) . '</p>';
};
?>

<!-- 1. Item Identity -->
<section class="<?= esc($sectionClass) ?>">
    <div class="inventory-create-section-title">
        <i class="fas fa-tag icon-emerald"></i>
        <span><?= lang('Inventory.item') ?></span>
    </div>
    <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
        <div>
            <label class="block text-sm font-semibold text-gray-700 mb-1.5"><?= lang('Inventory.itemNameRequired') ?></label>
            <input type="text" name="item_name" value="<?= old('item_name') ?>" class="inventory-create-form-field" placeholder="<?= lang('Inventory.enterItemName') ?>" required>
            <?= $errorLine('item_name') ?>
        </div>
        <div>
            <label class="block text-sm font-semibold text-gray-700 mb-1.5"><?= lang('Inventory.categoryRequired') ?></label>
            <select name="category" data-force-select2 data-allow-clear="false" class="inventory-create-select" required>
                <option value=""><?= lang('Inventory.selectCategory') ?></option>
                <option value="medication" <?= old('category') == 'medication' ? 'selected' : '' ?>><?= lang('Inventory.categoryMedication') ?></option>
                <option value="equipment" <?= old('category') == 'equipment' ? 'selected' : '' ?>><?= lang('Inventory.categoryEquipment') ?></option>
                <option value="supplies" <?= old('category') == 'supplies' ? 'selected' : '' ?>><?= lang('Inventory.categorySupplies') ?></option>
                <option value="consumables" <?= old('category') == 'consumables' ? 'selected' : '' ?>><?= lang('Inventory.categoryConsumables') ?></option>
                <option value="other" <?= old('category') == 'other' ? 'selected' : '' ?>><?= lang('Inventory.categoryOther') ?></option>
            </select>
            <?= $errorLine('category') ?>
        </div>
    </div>
    <div class="mt-4">
        <label class="block text-sm text-gray-500 mb-1.5"><?= lang('Inventory.description') ?> <span class="text-gray-400">— <?= lang('V3.optional') ?></span></label>
        <textarea name="description" rows="2" class="inventory-create-textarea" placeholder="<?= lang('Inventory.itemDescriptionPlaceholder') ?>"><?= old('description') ?></textarea>
    </div>
</section>

<!-- 2. Quantity & Pricing -->
<section class="<?= esc($sectionClass) ?>">
    <div class="inventory-create-section-title">
        <i class="fas fa-cubes icon-amber"></i>
        <span><?= lang('Inventory.quantity') ?></span>
    </div>
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
        <div>
            <label class="block text-sm font-semibold text-gray-700 mb-1.5"><?= lang('Inventory.quantityRequired') ?></label>
            <input type="number" name="quantity" value="<?= old('quantity') ?>" class="inventory-create-form-field" min="0" placeholder="0" required>
            <?= $errorLine('quantity') ?>
        </div>
        <div>
            <label class="block text-sm font-semibold text-gray-700 mb-1.5"><?= lang('Inventory.unit') ?></label>
            <select name="unit" data-force-select2 data-allow-clear="false" class="inventory-create-select">
                <option value="pieces" <?= old('unit') == 'pieces' ? 'selected' : '' ?>><?= lang('Inventory.unitPieces') ?></option>
                <option value="boxes" <?= old('unit') == 'boxes' ? 'selected' : '' ?>><?= lang('Inventory.unitBoxes') ?></option>
                <option value="bottles" <?= old('unit') == 'bottles' ? 'selected' : '' ?>><?= lang('Inventory.unitBottles') ?></option>
                <option value="tubes" <?= old('unit') == 'tubes' ? 'selected' : '' ?>><?= lang('Inventory.unitTubes') ?></option>
                <option value="packs" <?= old('unit') == 'packs' ? 'selected' : '' ?>><?= lang('Inventory.unitPacks') ?></option>
                <option value="sets" <?= old('unit') == 'sets' ? 'selected' : '' ?>><?= lang('Inventory.unitSets') ?></option>
            </select>
        </div>
        <div>
            <label class="block text-sm font-semibold text-gray-700 mb-1.5"><?= lang('Inventory.unitPriceRequired') ?></label>
            <div class="relative">
                <div class="absolute inset-y-0 <?= $isRtl ? 'right-0 pr-3' : 'left-0 pl-3' ?> flex items-center pointer-events-none">
                    <span class="text-gray-500 font-medium text-sm"><?= getCurrencySymbol() ?></span>
                </div>
                <input type="number" name="unit_price" value="<?= old('unit_price') ?>" step="0.01" min="0" class="inventory-create-form-field <?= $isRtl ? 'pr-8' : 'pl-8' ?>" placeholder="0.00" required>
            </div>
            <?= $errorLine('unit_price') ?>
        </div>
        <div>
            <label class="block text-sm font-semibold text-gray-700 mb-1.5"><?= lang('Inventory.minQuantityRequired') ?></label>
            <input type="number" name="min_quantity" value="<?= old('min_quantity') ?>" class="inventory-create-form-field" min="0" placeholder="0" required>
            <?= $errorLine('min_quantity') ?>
        </div>
    </div>
</section>

<!-- 3. Supplier -->
<section class="<?= esc($sectionClass) ?>">
    <div class="inventory-create-section-title">
        <i class="fas fa-truck icon-indigo"></i>
        <span><?= lang('Inventory.supplier') ?></span>
    </div>
    <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
        <div>
            <div class="flex items-center justify-between gap-3 mb-1.5">
                <label class="text-sm font-semibold text-gray-700"><?= lang('Inventory.supplierDirectory') ?></label>
                <div class="flex items-center gap-3">
                    <a href="<?= base_url('providers') ?>" target="_blank" class="inventory-create-quick-link"><?= lang('Inventory.manageSuppliers') ?></a>
                    <button type="button" id="openQuickSupplierModal" class="inventory-create-quick-link"><?= lang('Inventory.quickAdd') ?></button>
                </div>
            </div>
            <div class="space-y-3">
                <select id="supplier_id" name="supplier_id" data-force-select2 data-allow-clear="false" class="inventory-create-select">
                    <option value=""><?= lang('Inventory.noLinkedSupplier') ?></option>
                    <?php foreach (($supplier_directory ?? []) as $supplier): ?>
                        <option value="<?= (int) $supplier['id'] ?>" <?= (string) old('supplier_id') === (string) $supplier['id'] ? 'selected' : '' ?>><?= esc($supplier['name']) ?></option>
                    <?php endforeach; ?>
                </select>
                <input type="text" name="supplier" id="supplier_text" value="<?= old('supplier') ?>" class="inventory-create-form-field" placeholder="<?= lang('Inventory.customSupplierNameOptional') ?>">
            </div>
            <?= $errorLine('supplier') ?>
        </div>
        <div>
            <label class="block text-sm text-gray-500 mb-1.5"><?= lang('Inventory.supplierMappingOptional') ?> <span class="text-gray-400">— <?= lang('V3.optional') ?></span></label>
            <div class="grid grid-cols-2 gap-3">
                <input type="text" name="supplier_sku" value="<?= old('supplier_sku') ?>" class="inventory-create-form-field" placeholder="<?= lang('Inventory.supplierSku') ?>">
                <input type="text" name="pack_size_label" value="<?= old('pack_size_label') ?>" class="inventory-create-form-field" placeholder="<?= lang('Inventory.packSizeExample') ?>">
                <input type="number" min="1" step="1" name="preferred_order_qty" value="<?= old('preferred_order_qty') ?>" class="inventory-create-form-field" placeholder="<?= lang('Inventory.preferredReorderQty') ?>">
                <input type="number" min="0" step="0.01" name="supplier_last_unit_price" value="<?= old('supplier_last_unit_price') ?>" class="inventory-create-form-field" placeholder="<?= lang('Inventory.lastSupplierPrice') ?>">
            </div>
            <div class="mt-3">
                <select name="supplier_price_currency" data-force-select2 data-allow-clear="false" class="inventory-create-select" style="max-width:7rem">
                    <?php foreach (($currencies ?? ['ILS']) as $currency): ?>
                        <option value="<?= esc($currency) ?>" <?= old('supplier_price_currency', 'ILS') === $currency ? 'selected' : '' ?>><?= esc($currency) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <p class="mt-1.5 text-xs text-gray-400"><?= lang('Inventory.supplierMappingHelp') ?></p>
        </div>
    </div>
</section>

<!-- 4. Dates & Location -->
<section class="<?= esc($sectionClass) ?>">
    <div class="inventory-create-section-title">
        <i class="fas fa-calendar icon-orange"></i>
        <span><?= lang('Inventory.purchaseDate') ?></span>
    </div>
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
        <div>
            <label class="block text-sm font-semibold text-gray-700 mb-1.5"><?= lang('Inventory.purchaseDate') ?></label>
            <input type="text" name="purchase_date" data-clinic-date="1" value="<?= old('purchase_date', date('Y-m-d')) ?>" class="inventory-create-form-field">
            <?= $errorLine('purchase_date') ?>
        </div>
        <div>
            <label class="block text-sm text-gray-500 mb-1.5"><?= lang('Inventory.expiryDate') ?> <span class="text-gray-400">— <?= lang('V3.optional') ?></span></label>
            <input type="text" name="expiry_date" data-clinic-date="1" value="<?= old('expiry_date') ?>" class="inventory-create-form-field">
            <?= $errorLine('expiry_date') ?>
        </div>
        <div>
            <label class="block text-sm text-gray-500 mb-1.5"><?= lang('Inventory.location') ?> <span class="text-gray-400">— <?= lang('V3.optional') ?></span></label>
            <input type="text" name="location" value="<?= old('location') ?>" class="inventory-create-form-field" placeholder="<?= lang('Inventory.storageLocationExample') ?>">
            <?= $errorLine('location') ?>
        </div>
    </div>
</section>

<!-- 5. Notes -->
<section class="<?= esc($sectionClass) ?>">
    <div class="inventory-create-section-title">
        <i class="fas fa-sticky-note icon-violet"></i>
        <span><?= lang('Inventory.additionalNotes') ?></span>
    </div>
    <div>
        <label class="block text-sm text-gray-500 mb-1.5"><?= lang('Inventory.additionalNotes') ?> <span class="text-gray-400">— <?= lang('V3.optional') ?></span></label>
        <textarea name="notes" rows="3" class="inventory-create-textarea" placeholder="<?= lang('Inventory.additionalNotesPlaceholder') ?>"><?= old('notes') ?></textarea>
        <?= $errorLine('notes') ?>
    </div>
</section>
