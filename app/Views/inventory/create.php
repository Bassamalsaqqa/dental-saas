<?= $this->extend('layouts/main_auth') ?>

<?= $this->section('content') ?>
<?php $isRtl = !empty($is_rtl); ?>

<style>
.inventory-create-form-section {
    border-radius: 1rem;
    border: 1px solid #e2e8f0;
    background: #fff;
    padding: 1.5rem 1.75rem;
    box-shadow: 0 1px 2px rgba(0,0,0,0.04);
    border-left: 4px solid transparent;
}
[dir="rtl"] .inventory-create-form-section { border-left: none; border-right: 4px solid transparent; }
.inventory-create-form-section:nth-of-type(1) { border-left-color: #10b981; }
.inventory-create-form-section:nth-of-type(2) { border-left-color: #f59e0b; }
.inventory-create-form-section:nth-of-type(3) { border-left-color: #6366f1; }
.inventory-create-form-section:nth-of-type(4) { border-left-color: #f97316; }
.inventory-create-form-section:nth-of-type(5) { border-left-color: #8b5cf6; }
[dir="rtl"] .inventory-create-form-section:nth-of-type(1) { border-right-color: #10b981; }
[dir="rtl"] .inventory-create-form-section:nth-of-type(2) { border-right-color: #f59e0b; }
[dir="rtl"] .inventory-create-form-section:nth-of-type(3) { border-right-color: #6366f1; }
[dir="rtl"] .inventory-create-form-section:nth-of-type(4) { border-right-color: #f97316; }
[dir="rtl"] .inventory-create-form-section:nth-of-type(5) { border-right-color: #8b5cf6; }

.inventory-create-section-title {
    display: flex;
    align-items: center;
    gap: 0.625rem;
    margin-bottom: 1.25rem;
    padding-bottom: 0.875rem;
    border-bottom: 1px solid #f1f5f9;
    color: #1e293b;
    font-weight: 700;
    font-size: 0.9375rem;
}
.inventory-create-section-title i {
    display: inline-flex;
    height: 2.25rem;
    width: 2.25rem;
    align-items: center;
    justify-content: center;
    border-radius: 0.625rem;
}

[dir="rtl"] .inventory-create-section-title i { margin-right: 0; margin-left: 0.625rem; }

.inventory-create-form-field {
    width: 100%;
    height: 2.75rem;
    padding: 0 0.875rem;
    background: #fff;
    border: 1px solid #d1d5db;
    border-radius: 0.75rem;
    outline: none;
    transition: border-color 0.2s, box-shadow 0.2s;
    font-size: 0.875rem;
    line-height: 1.25rem;
    color: #1e293b;
    font-family: inherit;
}
.inventory-create-form-field:hover { border-color: #9ca3af; }
.inventory-create-form-field:focus { border-color: #6366f1; box-shadow: 0 0 0 3px rgba(99,102,241,0.12); }
.inventory-create-form-field::placeholder { color: #9ca3af; }
[dir="rtl"] .inventory-create-form-field { text-align: right; }

.inventory-create-textarea {
    width: 100%;
    padding: 0.625rem 0.875rem;
    background: #fff;
    border: 1px solid #d1d5db;
    border-radius: 0.75rem;
    outline: none;
    transition: border-color 0.2s, box-shadow 0.2s;
    font-size: 0.875rem;
    line-height: 1.5;
    color: #1e293b;
    font-family: inherit;
    resize: vertical;
}
.inventory-create-textarea:hover { border-color: #9ca3af; }
.inventory-create-textarea:focus { border-color: #6366f1; box-shadow: 0 0 0 3px rgba(99,102,241,0.12); }
.inventory-create-textarea::placeholder { color: #9ca3af; }
[dir="rtl"] .inventory-create-textarea { text-align: right; }

.inventory-create-select {
    width: 100%;
    height: 2.75rem;
    padding: 0 0.875rem;
    background: #fff;
    border: 1px solid #d1d5db;
    border-radius: 0.75rem;
    outline: none;
    font-size: 0.875rem;
    line-height: 1.25rem;
    color: #1e293b;
    font-family: inherit;
    appearance: none;
    cursor: pointer;
}
.inventory-create-select:hover { border-color: #9ca3af; }
.inventory-create-select:focus { border-color: #6366f1; box-shadow: 0 0 0 3px rgba(99,102,241,0.12); }

.inventory-create-quick-link {
    color: #4f46e5;
    font-size: 0.8125rem;
    font-weight: 600;
    background: none;
    border: none;
    cursor: pointer;
    padding: 0;
    font-family: inherit;
}
.inventory-create-quick-link:hover { color: #3730a3; text-decoration: underline; }

.inventory-create-section-title .icon-emerald { background: #d1fae5; color: #059669; }
.inventory-create-section-title .icon-amber  { background: #fef3c7; color: #d97706; }
.inventory-create-section-title .icon-indigo { background: #e0e7ff; color: #4f46e5; }
.inventory-create-section-title .icon-orange { background: #ffedd5; color: #ea580c; }
.inventory-create-section-title .icon-violet { background: #ede9fe; color: #7c3aed; }
</style>

<div class="min-h-screen bg-gradient-to-br from-slate-50 via-white to-slate-100">
    <div class="container mx-auto px-4 sm:px-6 lg:px-8 py-6">

        <!-- Page Header -->
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
                    <a href="<?= base_url('inventory') ?>" class="inline-flex items-center gap-2 px-4 py-2.5 border border-gray-200 text-gray-700 text-sm font-semibold rounded-xl hover:bg-gray-50 transition-colors">
                        <i class="fas <?= $isRtl ? 'fa-arrow-right' : 'fa-arrow-left' ?>"></i>
                        <?= lang('Inventory.backToInventory') ?>
                    </a>
                </div>
            </div>
        </div>

        <!-- Form -->
        <form action="<?= base_url('inventory/store') ?>" method="POST" class="space-y-5">
            <?= csrf_field() ?>

            <!-- 1. Item Identity -->
            <section class="inventory-create-form-section">
                <div class="inventory-create-section-title">
                    <i class="fas fa-tag icon-emerald"></i>
                    <span><?= lang('Inventory.item') ?></span>
                </div>
                <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1.5"><?= lang('Inventory.itemNameRequired') ?></label>
                        <input type="text" name="item_name" value="<?= old('item_name') ?>" class="inventory-create-form-field" placeholder="<?= lang('Inventory.enterItemName') ?>" required>
                        <?php if (session()->getFlashdata('validation') && session()->getFlashdata('validation')->hasError('item_name')): ?>
                            <p class="text-red-500 text-sm mt-1.5"><i class="fas fa-exclamation-circle mr-1"></i><?= session()->getFlashdata('validation')->getError('item_name') ?></p>
                        <?php endif; ?>
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
                        <?php if (session()->getFlashdata('validation') && session()->getFlashdata('validation')->hasError('category')): ?>
                            <p class="text-red-500 text-sm mt-1.5"><i class="fas fa-exclamation-circle mr-1"></i><?= session()->getFlashdata('validation')->getError('category') ?></p>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="mt-4">
                    <label class="block text-sm text-gray-500 mb-1.5"><?= lang('Inventory.description') ?> <span class="text-gray-400">— <?= lang('UI.optional') ?></span></label>
                    <textarea name="description" rows="2" class="inventory-create-textarea" placeholder="<?= lang('Inventory.itemDescriptionPlaceholder') ?>"><?= old('description') ?></textarea>
                </div>
            </section>

            <!-- 2. Quantity & Pricing -->
            <section class="inventory-create-form-section">
                <div class="inventory-create-section-title">
                    <i class="fas fa-cubes icon-amber"></i>
                    <span><?= lang('Inventory.quantity') ?></span>
                </div>
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1.5"><?= lang('Inventory.quantityRequired') ?></label>
                        <input type="number" name="quantity" value="<?= old('quantity') ?>" class="inventory-create-form-field" min="0" placeholder="0" required>
                        <?php if (session()->getFlashdata('validation') && session()->getFlashdata('validation')->hasError('quantity')): ?>
                            <p class="text-red-500 text-sm mt-1.5"><i class="fas fa-exclamation-circle mr-1"></i><?= session()->getFlashdata('validation')->getError('quantity') ?></p>
                        <?php endif; ?>
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
                            <input type="number" name="unit_price" value="<?= old('unit_price') ?>" step="0.01" min="0" class="inventory-create-form-field <?= $isRtl ? 'pr-3' : 'pl-8' ?>" placeholder="0.00" required>
                        </div>
                        <?php if (session()->getFlashdata('validation') && session()->getFlashdata('validation')->hasError('unit_price')): ?>
                            <p class="text-red-500 text-sm mt-1.5"><i class="fas fa-exclamation-circle mr-1"></i><?= session()->getFlashdata('validation')->getError('unit_price') ?></p>
                        <?php endif; ?>
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1.5"><?= lang('Inventory.minQuantityRequired') ?></label>
                        <input type="number" name="min_quantity" value="<?= old('min_quantity') ?>" class="inventory-create-form-field" min="0" placeholder="0" required>
                        <?php if (session()->getFlashdata('validation') && session()->getFlashdata('validation')->hasError('min_quantity')): ?>
                            <p class="text-red-500 text-sm mt-1.5"><i class="fas fa-exclamation-circle mr-1"></i><?= session()->getFlashdata('validation')->getError('min_quantity') ?></p>
                        <?php endif; ?>
                    </div>
                </div>
            </section>

            <!-- 3. Supplier -->
            <section class="inventory-create-form-section">
                <div class="inventory-create-section-title">
                    <i class="fas fa-truck icon-indigo"></i>
                    <span><?= lang('Inventory.supplier') ?></span>
                </div>
                <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
                    <!-- Supplier Selection -->
                    <div>
                        <div class="flex items-center justify-between mb-1.5">
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
                        <?php if (session()->getFlashdata('validation') && session()->getFlashdata('validation')->hasError('supplier')): ?>
                            <p class="text-red-500 text-sm mt-1.5"><i class="fas fa-exclamation-circle mr-1"></i><?= session()->getFlashdata('validation')->getError('supplier') ?></p>
                        <?php endif; ?>
                    </div>
                    <!-- Supplier Mapping -->
                    <div>
                        <label class="block text-sm text-gray-500 mb-1.5"><?= lang('Inventory.supplierMappingOptional') ?> <span class="text-gray-400">— <?= lang('UI.optional') ?></span></label>
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
            <section class="inventory-create-form-section">
                <div class="inventory-create-section-title">
                    <i class="fas fa-calendar icon-orange"></i>
                    <span><?= lang('Inventory.purchaseDate') ?></span>
                </div>
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1.5"><?= lang('Inventory.purchaseDate') ?></label>
                        <input type="text" name="purchase_date" data-clinic-date="1" value="<?= old('purchase_date', date('Y-m-d')) ?>" class="inventory-create-form-field">
                        <?php if (session()->getFlashdata('validation') && session()->getFlashdata('validation')->hasError('purchase_date')): ?>
                            <p class="text-red-500 text-sm mt-1.5"><i class="fas fa-exclamation-circle mr-1"></i><?= session()->getFlashdata('validation')->getError('purchase_date') ?></p>
                        <?php endif; ?>
                    </div>
                    <div>
                        <label class="block text-sm text-gray-500 mb-1.5"><?= lang('Inventory.expiryDate') ?> <span class="text-gray-400">— <?= lang('UI.optional') ?></span></label>
                        <input type="text" name="expiry_date" data-clinic-date="1" value="<?= old('expiry_date') ?>" class="inventory-create-form-field">
                        <?php if (session()->getFlashdata('validation') && session()->getFlashdata('validation')->hasError('expiry_date')): ?>
                            <p class="text-red-500 text-sm mt-1.5"><i class="fas fa-exclamation-circle mr-1"></i><?= session()->getFlashdata('validation')->getError('expiry_date') ?></p>
                        <?php endif; ?>
                    </div>
                    <div>
                        <label class="block text-sm text-gray-500 mb-1.5"><?= lang('Inventory.location') ?> <span class="text-gray-400">— <?= lang('UI.optional') ?></span></label>
                        <input type="text" name="location" value="<?= old('location') ?>" class="inventory-create-form-field" placeholder="<?= lang('Inventory.storageLocationExample') ?>">
                        <?php if (session()->getFlashdata('validation') && session()->getFlashdata('validation')->hasError('location')): ?>
                            <p class="text-red-500 text-sm mt-1.5"><i class="fas fa-exclamation-circle mr-1"></i><?= session()->getFlashdata('validation')->getError('location') ?></p>
                        <?php endif; ?>
                    </div>
                </div>
            </section>

            <!-- 5. Notes -->
            <section class="inventory-create-form-section">
                <div class="inventory-create-section-title">
                    <i class="fas fa-sticky-note icon-violet"></i>
                    <span><?= lang('Inventory.additionalNotes') ?></span>
                </div>
                <div>
                    <label class="block text-sm text-gray-500 mb-1.5"><?= lang('Inventory.additionalNotes') ?> <span class="text-gray-400">— <?= lang('UI.optional') ?></span></label>
                    <textarea name="notes" rows="3" class="inventory-create-textarea" placeholder="<?= lang('Inventory.additionalNotesPlaceholder') ?>"><?= old('notes') ?></textarea>
                    <?php if (session()->getFlashdata('validation') && session()->getFlashdata('validation')->hasError('notes')): ?>
                        <p class="text-red-500 text-sm mt-1.5"><i class="fas fa-exclamation-circle mr-1"></i><?= session()->getFlashdata('validation')->getError('notes') ?></p>
                    <?php endif; ?>
                </div>
            </section>

            <!-- Form Actions -->
            <div class="flex flex-col-reverse sm:flex-row justify-end gap-3 pt-2">
                <a href="<?= base_url('inventory') ?>" class="inline-flex items-center justify-center gap-2 px-6 py-2.5 border border-gray-200 text-gray-700 text-sm font-semibold rounded-xl hover:bg-gray-50 transition-colors">
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

<!-- Quick Add Supplier Modal -->
<div id="quickSupplierModal" class="fixed inset-0 z-50 hidden overflow-y-auto" role="dialog" aria-modal="true">
    <div class="fixed inset-0 bg-slate-950/45"></div>
    <div class="relative z-10 flex min-h-screen items-center justify-center p-4" onclick="closeQuickSupplierModal()">
        <div class="w-full max-w-lg rounded-2xl border border-slate-200 bg-white shadow-2xl" onclick="event.stopPropagation()">
            <div class="flex items-center justify-between border-b border-slate-200 px-5 py-4">
                <div>
                    <h4 class="text-base font-semibold text-slate-900"><?= lang('Inventory.quickAddSupplierTitle') ?></h4>
                    <p class="mt-1 text-sm text-slate-500"><?= lang('Inventory.quickAddSupplierHelp') ?></p>
                </div>
                <button type="button" class="inline-flex h-9 w-9 items-center justify-center rounded-full text-slate-500 transition hover:bg-slate-100 hover:text-slate-700" onclick="closeQuickSupplierModal()">
                    <i class="fas fa-times text-sm"></i>
                </button>
            </div>
            <div class="grid grid-cols-1 gap-4 p-5 sm:grid-cols-2">
                <input type="text" id="quickSupplierName" class="inventory-create-form-field" placeholder="<?= lang('Inventory.supplierNameRequired') ?>" required>
                <input type="text" id="quickSupplierContact" class="inventory-create-form-field" placeholder="<?= lang('Inventory.contactPerson') ?>">
                <input type="text" id="quickSupplierPhone" class="inventory-create-form-field" placeholder="<?= lang('Inventory.phone') ?>">
                <input type="email" id="quickSupplierEmail" class="inventory-create-form-field" placeholder="<?= lang('Inventory.email') ?>">
                <p id="quickSupplierStatus" class="text-xs text-slate-500 sm:col-span-2"></p>
            </div>
            <div class="flex flex-wrap justify-end gap-2 border-t border-slate-200 px-5 py-4">
                <button type="button" onclick="closeQuickSupplierModal()" class="inline-flex items-center gap-2 rounded-xl border border-slate-300 px-4 py-2.5 text-sm font-semibold text-slate-700 transition hover:bg-slate-50">
                    <i class="fas fa-times text-xs"></i><span><?= lang('Inventory.cancel') ?></span>
                </button>
                <button type="button" id="createQuickSupplierBtn" class="inline-flex items-center gap-2 rounded-xl bg-indigo-600 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-indigo-700">
                    <i class="fas fa-plus text-xs"></i><span><?= lang('Inventory.createSupplier') ?></span>
                </button>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    var modal = document.getElementById('quickSupplierModal');
    var openBtn = document.getElementById('openQuickSupplierModal');
    var createBtn = document.getElementById('createQuickSupplierBtn');
    var statusEl = document.getElementById('quickSupplierStatus');
    var supplierSelect = document.getElementById('supplier_id');
    var supplierText = document.getElementById('supplier_text');
    var fields = ['quickSupplierName','quickSupplierContact','quickSupplierPhone','quickSupplierEmail']
        .map(function(id){ return document.getElementById(id); });

    function openQuickSupplierModal() {
        if (!modal) return;
        if (modal.parentElement !== document.body) document.body.appendChild(modal);
        modal.classList.remove('hidden');
        setTimeout(function(){ fields[0] && fields[0].focus(); }, 50);
    }

    window.closeQuickSupplierModal = function() {
        if (!modal) return;
        modal.classList.add('hidden');
        fields.forEach(function(f){ if(f) f.value = ''; });
        if (statusEl) { statusEl.textContent = ''; statusEl.className = 'text-xs text-slate-500 sm:col-span-2'; }
    };

    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape' && modal && !modal.classList.contains('hidden')) {
            window.closeQuickSupplierModal();
        }
    });

    if (openBtn) openBtn.addEventListener('click', openQuickSupplierModal);

    if (createBtn) {
        createBtn.addEventListener('click', async function() {
            var name = (fields[0]?.value || '').trim();
            if (!name) {
                statusEl.textContent = '<?= lang('Inventory.supplierNameRequiredMessage') ?>';
                statusEl.className = 'text-xs text-red-600 sm:col-span-2';
                return;
            }
            var formData = new FormData();
            formData.append('<?= csrf_token() ?>', '<?= csrf_hash() ?>');
            formData.append('name', name);
            formData.append('contact_person', fields[1]?.value || '');
            formData.append('phone', fields[2]?.value || '');
            formData.append('email', fields[3]?.value || '');
            createBtn.disabled = true;
            statusEl.textContent = '<?= lang('Inventory.creatingSupplier') ?>';
            statusEl.className = 'text-xs text-indigo-700 sm:col-span-2';
            try {
                var r = await fetch('<?= base_url('inventory/suppliers/quick-create') ?>', { method:'POST', headers:{'X-Requested-With':'XMLHttpRequest'}, body:formData });
                var d = await r.json();
                if (!r.ok || !d.success || !d.supplier) throw new Error(d.message || '<?= lang('Inventory.failedToCreateSupplier') ?>');
                var opt = document.createElement('option');
                opt.value = String(d.supplier.id);
                opt.textContent = d.supplier.name;
                supplierSelect.appendChild(opt);
                supplierSelect.value = String(d.supplier.id);
                if (window.jQuery?.fn?.select2) window.jQuery(supplierSelect).val(String(d.supplier.id)).trigger('change');
                if (supplierText) supplierText.value = d.supplier.name;
                statusEl.textContent = d.duplicate ? '<?= lang('Inventory.supplierAlreadyExistedSelected') ?>' : '<?= lang('Inventory.supplierCreatedSelected') ?>';
                statusEl.className = 'text-xs text-emerald-700 sm:col-span-2';
                window.setTimeout(window.closeQuickSupplierModal, 350);
            } catch(e) {
                statusEl.textContent = e.message || '<?= lang('Inventory.failedToCreateSupplierWithPeriod') ?>';
                statusEl.className = 'text-xs text-red-600 sm:col-span-2';
            } finally { createBtn.disabled = false; }
        });
    }

    if (supplierSelect && supplierText) {
        supplierSelect.addEventListener('change', function() {
            var s = supplierSelect.options[supplierSelect.selectedIndex];
            if (s && supplierSelect.value) supplierText.value = s.textContent.trim();
        });
    }
});
</script>
<?= $this->endSection() ?>
