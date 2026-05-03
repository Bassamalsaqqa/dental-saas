<?= $this->extend('layouts/main_auth') ?>

<?= $this->section('content') ?>
<style>
[dir="ltr"] .inventory-leading-input {
    padding-left: 1rem !important;
    padding-right: 2.75rem !important;
}

[dir="rtl"] .inventory-leading-input {
    padding-right: 2.75rem !important;
    padding-left: 1rem !important;
    text-align: right;
}

[dir="ltr"] input[data-clinic-date="1"].inventory-date-input {
    padding-left: 1rem !important;
    padding-right: 2.75rem !important;
}

[dir="rtl"] input[data-clinic-date="1"].inventory-date-input {
    padding-right: 2.75rem !important;
    padding-left: 1rem !important;
    text-align: right;
}

.inventory-create-quick-link {
    color: #4f46e5;
    font-size: 0.75rem;
    font-weight: 700;
}

.inventory-create-quick-link:hover {
    color: #3730a3;
}

.inventory-create-modal-backdrop {
    background: rgba(15, 23, 42, 0.48);
    backdrop-filter: blur(4px);
}

.inventory-create-modal-panel {
    width: min(100%, 42rem);
}

[dir="rtl"] .inventory-create-modal-panel {
    text-align: right;
}

.inventory-create-form-section {
    border-radius: 1rem;
    border: 1px solid rgba(226, 232, 240, 0.95);
    background: rgba(248, 250, 252, 0.62);
    padding: 1.25rem;
}

.inventory-create-section-title {
    display: flex;
    align-items: center;
    gap: 0.625rem;
    margin-bottom: 1rem;
    color: #0f172a;
    font-weight: 800;
}

.inventory-create-section-title i {
    display: inline-flex;
    height: 2rem;
    width: 2rem;
    align-items: center;
    justify-content: center;
    border-radius: 0.75rem;
    background: #e0f2fe;
}

.inventory-create-form-section .select2-container--default .select2-selection--single {
    min-height: 3rem;
    border-width: 2px;
    border-color: #e5e7eb;
    border-radius: 0.75rem;
    background: rgba(255, 255, 255, 0.8);
    box-shadow: 0 10px 15px -3px rgba(15, 23, 42, 0.08), 0 4px 6px -4px rgba(15, 23, 42, 0.08);
}

.inventory-create-form-section .select2-container--default .select2-selection--single .select2-selection__rendered {
    line-height: 2.875rem;
    padding-inline: 1rem 2.25rem;
}

.inventory-create-form-section .select2-container--default .select2-selection--single .select2-selection__arrow {
    height: 2.875rem;
}
</style>
<!-- Enhanced Inventory Creation with Advanced Design -->
<div class="min-h-screen bg-slate-50">
    <!-- Animated Background Elements -->
    <div class="container mx-auto relative z-10 px-4 py-6">

        <!-- Enhanced Inventory Form with Glassmorphism -->
        <div class="group relative">
            <div class="relative bg-white rounded-2xl shadow-2xl shadow-emerald-500/10 group-hover:shadow-emerald-500/20 transition-all duration-500 overflow-hidden">
                <!-- Form Header -->
                <div class="p-8 border-b border-white/20">
                    <div class="flex flex-col lg:flex-row lg:justify-between lg:items-center space-y-4 lg:space-y-0">
                        <div class="flex items-center space-x-4">
                            <div class="relative">
                                <div class="absolute inset-0 bg-gradient-to-br from-emerald-500 to-teal-600 rounded-2xl blur-lg opacity-75 group-hover:opacity-100 transition-opacity duration-300"></div>
                                <div class="relative w-12 h-12 bg-gradient-to-br from-emerald-500 to-teal-600 rounded-2xl flex items-center justify-center shadow-xl group-hover:scale-110 group-hover:rotate-3 transition-all duration-300">
                                    <i class="fas fa-boxes text-white text-lg"></i>
                                </div>
                            </div>
                            <div>
                                <h3 class="text-2xl font-black text-gray-900 group-hover:text-emerald-900 transition-colors duration-300"><?= lang('Inventory.addNewInventoryItem') ?></h3>
                                <p class="text-gray-600 font-medium"><?= lang('Inventory.addNewInventoryItemSubtitle') ?></p>
                            </div>
                        </div>
                        
                        <div class="flex items-center space-x-3">
                            <a href="<?= base_url('inventory') ?>" class="group/btn relative inline-flex items-center px-6 py-3 border-2 border-gray-300 text-gray-700 text-sm font-bold rounded-xl hover:bg-gradient-to-r hover:from-gray-50 hover:to-slate-50 hover:border-gray-400 transition-all duration-300 hover:scale-105">
                                <div class="absolute inset-0 bg-gradient-to-r from-gray-500/10 to-slate-500/10 rounded-xl blur opacity-0 group-hover/btn:opacity-100 transition-opacity duration-300"></div>
                                <i class="fas fa-arrow-left rtl-back-icon mr-2 relative z-10"></i>
                                <span class="relative z-10"><?= lang('Inventory.backToInventory') ?></span>
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Form Content -->
                <div class="p-8">

                    <form action="<?= base_url('inventory/store') ?>" method="POST" class="space-y-8">
                        <?= csrf_field() ?>
                        <section class="inventory-create-form-section">
                            <div class="inventory-create-section-title">
                                <i class="fas fa-tag text-emerald-600"></i>
                                <span><?= lang('Inventory.item') ?></span>
                            </div>
                            <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                                <div class="group/field relative">
                                    <label class="block text-sm font-bold text-gray-700 mb-3 flex items-center space-x-2">
                                        <i class="fas fa-tag text-emerald-600"></i>
                                        <span><?= lang('Inventory.itemNameRequired') ?></span>
                                    </label>
                                    <div class="relative">
                                        <input type="text" name="item_name" value="<?= old('item_name') ?>" class="inventory-leading-input w-full py-3 bg-white/80 border-2 border-gray-200 rounded-xl shadow-lg focus:outline-none focus:ring-4 focus:ring-emerald-500/20 focus:border-emerald-500 transition-all duration-300 hover:shadow-xl" placeholder="<?= lang('Inventory.enterItemName') ?>" required>
                                        <div class="absolute inset-y-0 <?= !empty($is_rtl) ? 'left-0 pl-3' : 'right-0 pr-3' ?> flex items-center pointer-events-none">
                                            <i class="fas fa-tag text-gray-400"></i>
                                        </div>
                                    </div>
                                    <?php if (session()->getFlashdata('validation') && session()->getFlashdata('validation')->hasError('item_name')): ?>
                                        <p class="text-red-500 text-sm mt-2 flex items-center space-x-1">
                                            <i class="fas fa-exclamation-circle"></i>
                                            <span><?= session()->getFlashdata('validation')->getError('item_name') ?></span>
                                        </p>
                                    <?php endif; ?>
                                </div>

                                <div class="group/field relative">
                                    <label class="block text-sm font-bold text-gray-700 mb-3 flex items-center space-x-2">
                                        <i class="fas fa-layer-group text-blue-600"></i>
                                        <span><?= lang('Inventory.categoryRequired') ?></span>
                                    </label>
                                    <div class="relative">
                                        <select name="category" data-force-select2 data-allow-clear="false" class="w-full px-4 py-3 bg-white/80 border-2 border-gray-200 rounded-xl shadow-lg focus:outline-none focus:ring-4 focus:ring-blue-500/20 focus:border-blue-500 transition-all duration-300 hover:shadow-xl" required>
                                            <option value=""><?= lang('Inventory.selectCategory') ?></option>
                                            <option value="medication" <?= (old('category') == 'medication') ? 'selected' : '' ?>><?= lang('Inventory.categoryMedication') ?></option>
                                            <option value="equipment" <?= (old('category') == 'equipment') ? 'selected' : '' ?>><?= lang('Inventory.categoryEquipment') ?></option>
                                            <option value="supplies" <?= (old('category') == 'supplies') ? 'selected' : '' ?>><?= lang('Inventory.categorySupplies') ?></option>
                                            <option value="consumables" <?= (old('category') == 'consumables') ? 'selected' : '' ?>><?= lang('Inventory.categoryConsumables') ?></option>
                                            <option value="other" <?= (old('category') == 'other') ? 'selected' : '' ?>><?= lang('Inventory.categoryOther') ?></option>
                                        </select>
                                        <div class="absolute inset-y-0 <?= !empty($is_rtl) ? 'left-0 pl-3' : 'right-0 pr-3' ?> flex items-center pointer-events-none">
                                            <i class="fas fa-chevron-down text-gray-400"></i>
                                        </div>
                                    </div>
                                    <?php if (session()->getFlashdata('validation') && session()->getFlashdata('validation')->hasError('category')): ?>
                                        <p class="text-red-500 text-sm mt-2 flex items-center space-x-1">
                                            <i class="fas fa-exclamation-circle"></i>
                                            <span><?= session()->getFlashdata('validation')->getError('category') ?></span>
                                        </p>
                                    <?php endif; ?>
                                </div>

                                <div class="group/field relative md:col-span-2">
                                    <label class="block text-sm font-bold text-gray-700 mb-3 flex items-center space-x-2">
                                        <i class="fas fa-align-left text-purple-600"></i>
                                        <span><?= lang('Inventory.description') ?></span>
                                    </label>
                                    <div class="relative">
                                        <textarea name="description" rows="3" class="w-full px-4 py-3 bg-white/80 border-2 border-gray-200 rounded-xl shadow-lg focus:outline-none focus:ring-4 focus:ring-purple-500/20 focus:border-purple-500 transition-all duration-300 hover:shadow-xl resize-none" placeholder="<?= lang('Inventory.itemDescriptionPlaceholder') ?>"><?= old('description') ?></textarea>
                                        <div class="absolute bottom-3 <?= !empty($is_rtl) ? 'left-3' : 'right-3' ?> text-gray-400 text-xs">
                                            <i class="fas fa-edit"></i>
                                        </div>
                                    </div>
                                    <?php if (session()->getFlashdata('validation') && session()->getFlashdata('validation')->hasError('description')): ?>
                                        <p class="text-red-500 text-sm mt-2 flex items-center space-x-1">
                                            <i class="fas fa-exclamation-circle"></i>
                                            <span><?= session()->getFlashdata('validation')->getError('description') ?></span>
                                        </p>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </section>

                        <section class="inventory-create-form-section">
                            <div class="inventory-create-section-title">
                                <i class="fas fa-cubes text-amber-600"></i>
                                <span><?= lang('Inventory.quantity') ?></span>
                            </div>
                            <div class="grid grid-cols-1 gap-6 md:grid-cols-3">
                                <div class="group/field relative">
                                    <label class="block text-sm font-bold text-gray-700 mb-3 flex items-center space-x-2">
                                        <i class="fas fa-cubes text-amber-600"></i>
                                        <span><?= lang('Inventory.quantityRequired') ?></span>
                                    </label>
                                    <div class="relative">
                                        <input type="number" name="quantity" value="<?= old('quantity') ?>" class="inventory-leading-input w-full py-3 bg-white/80 border-2 border-gray-200 rounded-xl shadow-lg focus:outline-none focus:ring-4 focus:ring-amber-500/20 focus:border-amber-500 transition-all duration-300 hover:shadow-xl" min="0" placeholder="0" required>
                                        <div class="absolute inset-y-0 <?= !empty($is_rtl) ? 'left-0 pl-3' : 'right-0 pr-3' ?> flex items-center pointer-events-none">
                                            <i class="fas fa-cubes text-gray-400"></i>
                                        </div>
                                    </div>
                                    <?php if (session()->getFlashdata('validation') && session()->getFlashdata('validation')->hasError('quantity')): ?>
                                        <p class="text-red-500 text-sm mt-2 flex items-center space-x-1">
                                            <i class="fas fa-exclamation-circle"></i>
                                            <span><?= session()->getFlashdata('validation')->getError('quantity') ?></span>
                                        </p>
                                    <?php endif; ?>
                                </div>

                                <div class="group/field relative">
                                    <label class="block text-sm font-bold text-gray-700 mb-3 flex items-center space-x-2">
                                        <i class="fas fa-ruler text-cyan-600"></i>
                                        <span><?= lang('Inventory.unit') ?></span>
                                    </label>
                                    <div class="relative">
                                        <select name="unit" data-force-select2 data-allow-clear="false" class="w-full px-4 py-3 bg-white/80 border-2 border-gray-200 rounded-xl shadow-lg focus:outline-none focus:ring-4 focus:ring-cyan-500/20 focus:border-cyan-500 transition-all duration-300 hover:shadow-xl">
                                            <option value="pieces" <?= (old('unit') == 'pieces') ? 'selected' : '' ?>><?= lang('Inventory.unitPieces') ?></option>
                                            <option value="boxes" <?= (old('unit') == 'boxes') ? 'selected' : '' ?>><?= lang('Inventory.unitBoxes') ?></option>
                                            <option value="bottles" <?= (old('unit') == 'bottles') ? 'selected' : '' ?>><?= lang('Inventory.unitBottles') ?></option>
                                            <option value="tubes" <?= (old('unit') == 'tubes') ? 'selected' : '' ?>><?= lang('Inventory.unitTubes') ?></option>
                                            <option value="packs" <?= (old('unit') == 'packs') ? 'selected' : '' ?>><?= lang('Inventory.unitPacks') ?></option>
                                            <option value="sets" <?= (old('unit') == 'sets') ? 'selected' : '' ?>><?= lang('Inventory.unitSets') ?></option>
                                        </select>
                                        <div class="absolute inset-y-0 <?= !empty($is_rtl) ? 'left-0 pl-3' : 'right-0 pr-3' ?> flex items-center pointer-events-none">
                                            <i class="fas fa-chevron-down text-gray-400"></i>
                                        </div>
                                    </div>
                                </div>

                                <div class="group/field relative">
                                    <label class="block text-sm font-bold text-gray-700 mb-3 flex items-center space-x-2">
                                        <i class="fas fa-exclamation-triangle text-rose-600"></i>
                                        <span><?= lang('Inventory.minQuantityRequired') ?></span>
                                    </label>
                                    <div class="relative">
                                        <input type="number" name="min_quantity" value="<?= old('min_quantity') ?>" class="inventory-leading-input w-full py-3 bg-white/80 border-2 border-gray-200 rounded-xl shadow-lg focus:outline-none focus:ring-4 focus:ring-rose-500/20 focus:border-rose-500 transition-all duration-300 hover:shadow-xl" min="0" placeholder="0" required>
                                        <div class="absolute inset-y-0 <?= !empty($is_rtl) ? 'left-0 pl-3' : 'right-0 pr-3' ?> flex items-center pointer-events-none">
                                            <i class="fas fa-exclamation-triangle text-gray-400"></i>
                                        </div>
                                    </div>
                                    <?php if (session()->getFlashdata('validation') && session()->getFlashdata('validation')->hasError('min_quantity')): ?>
                                        <p class="text-red-500 text-sm mt-2 flex items-center space-x-1">
                                            <i class="fas fa-exclamation-circle"></i>
                                            <span><?= session()->getFlashdata('validation')->getError('min_quantity') ?></span>
                                        </p>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </section>

                        <section class="inventory-create-form-section">
                            <div class="inventory-create-section-title">
                                <i class="fas fa-truck text-indigo-600"></i>
                                <span><?= lang('Inventory.supplier') ?></span>
                            </div>
                            <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
                            <div class="group/field relative">
                                <label class="block text-sm font-bold text-gray-700 mb-3 flex items-center space-x-2">
                                    <i class="fas fa-dollar-sign text-green-600"></i>
                                    <span><?= lang('Inventory.unitPriceRequired') ?></span>
                                </label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 <?= !empty($is_rtl) ? 'right-0 pr-4' : 'left-0 pl-4' ?> flex items-center pointer-events-none">
                                        <span class="text-gray-500 font-bold"><?= getCurrencySymbol() ?></span>
                                    </div>
                                    <input type="number" name="unit_price" value="<?= old('unit_price') ?>" step="0.01" min="0" class="w-full <?= !empty($is_rtl) ? 'pr-10 pl-10' : 'pl-10 pr-10' ?> py-3 bg-white/80 border-2 border-gray-200 rounded-xl shadow-lg focus:outline-none focus:ring-4 focus:ring-green-500/20 focus:border-green-500 transition-all duration-300 hover:shadow-xl" placeholder="0.00" required>
                                    <div class="absolute inset-y-0 <?= !empty($is_rtl) ? 'left-0 pl-3' : 'right-0 pr-3' ?> flex items-center pointer-events-none">
                                        <i class="fas fa-dollar-sign text-gray-400"></i>
                                    </div>
                                </div>
                                <?php if (session()->getFlashdata('validation') && session()->getFlashdata('validation')->hasError('unit_price')): ?>
                                    <p class="text-red-500 text-sm mt-2 flex items-center space-x-1">
                                        <i class="fas fa-exclamation-circle"></i>
                                        <span><?= session()->getFlashdata('validation')->getError('unit_price') ?></span>
                                    </p>
                                <?php endif; ?>
                            </div>

                            <div class="group/field relative">
                                <label class="block text-sm font-bold text-gray-700 mb-3 flex items-center space-x-2">
                                    <i class="fas fa-truck text-indigo-600"></i>
                                    <span><?= lang('Inventory.supplierDirectory') ?></span>
                                </label>
                                <div class="space-y-3">
                                    <div class="flex items-center justify-between gap-3 text-xs font-semibold">
                                        <span class="text-gray-500"><?= lang('Inventory.supplier') ?></span>
                                        <div class="flex items-center gap-3">
                                            <a href="<?= base_url('providers') ?>" target="_blank" class="inventory-create-quick-link"><?= lang('Inventory.manageSuppliers') ?></a>
                                            <button type="button" id="openQuickSupplierModal" class="inventory-create-quick-link"><?= lang('Inventory.quickAdd') ?></button>
                                        </div>
                                    </div>
                                    <div class="relative">
                                    <select id="supplier_id" name="supplier_id" data-force-select2 data-allow-clear="false" class="w-full rounded-xl border-2 border-gray-200 bg-white/80 px-4 py-3 shadow-lg transition-all duration-300 hover:shadow-xl focus:border-indigo-500 focus:outline-none focus:ring-4 focus:ring-indigo-500/20">
                                        <option value=""><?= lang('Inventory.noLinkedSupplier') ?></option>
                                        <?php foreach (($supplier_directory ?? []) as $supplier): ?>
                                            <option value="<?= (int) $supplier['id'] ?>" <?= (string) old('supplier_id') === (string) $supplier['id'] ? 'selected' : '' ?>>
                                                <?= esc($supplier['name']) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                        <div class="pointer-events-none absolute inset-y-0 <?= !empty($is_rtl) ? 'left-0 pl-3' : 'right-0 pr-3' ?> flex items-center">
                                            <i class="fas fa-chevron-down text-gray-400"></i>
                                        </div>
                                    </div>
                                    <div class="relative">
                                        <input type="text" name="supplier" id="supplier_text" value="<?= old('supplier') ?>" class="inventory-leading-input w-full py-3 bg-white/80 border-2 border-gray-200 rounded-xl shadow-lg focus:outline-none focus:ring-4 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all duration-300 hover:shadow-xl" placeholder="<?= lang('Inventory.customSupplierNameOptional') ?>">
                                        <div class="absolute inset-y-0 <?= !empty($is_rtl) ? 'left-0 pl-3' : 'right-0 pr-3' ?> flex items-center pointer-events-none">
                                            <i class="fas fa-truck text-gray-400"></i>
                                        </div>
                                    </div>
                                </div>
                                <?php if (session()->getFlashdata('validation') && session()->getFlashdata('validation')->hasError('supplier')): ?>
                                    <p class="text-red-500 text-sm mt-2 flex items-center space-x-1">
                                        <i class="fas fa-exclamation-circle"></i>
                                        <span><?= session()->getFlashdata('validation')->getError('supplier') ?></span>
                                    </p>
                                <?php endif; ?>
                            </div>
                            </div>

                            <div class="mt-6 group/field relative">
                                <label class="block text-sm font-bold text-gray-700 mb-3 flex items-center space-x-2">
                                    <i class="fas fa-link text-amber-600"></i>
                                    <span><?= lang('Inventory.supplierMappingOptional') ?></span>
                                </label>
                                <div class="grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-3">
                                    <input type="text" name="supplier_sku" value="<?= old('supplier_sku') ?>" class="w-full h-12 px-4 bg-white/80 border-2 border-gray-200 rounded-xl shadow-lg focus:outline-none focus:ring-4 focus:ring-amber-500/20 focus:border-amber-500 transition-all duration-300 hover:shadow-xl" placeholder="<?= lang('Inventory.supplierSku') ?>">
                                    <input type="text" name="pack_size_label" value="<?= old('pack_size_label') ?>" class="w-full h-12 px-4 bg-white/80 border-2 border-gray-200 rounded-xl shadow-lg focus:outline-none focus:ring-4 focus:ring-amber-500/20 focus:border-amber-500 transition-all duration-300 hover:shadow-xl" placeholder="<?= lang('Inventory.packSizeExample') ?>">
                                    <input type="number" min="1" step="1" name="preferred_order_qty" value="<?= old('preferred_order_qty') ?>" class="w-full h-12 px-4 bg-white/80 border-2 border-gray-200 rounded-xl shadow-lg focus:outline-none focus:ring-4 focus:ring-amber-500/20 focus:border-amber-500 transition-all duration-300 hover:shadow-xl" placeholder="<?= lang('Inventory.preferredReorderQty') ?>">
                                    <input type="number" min="0" step="0.01" name="supplier_last_unit_price" value="<?= old('supplier_last_unit_price') ?>" class="w-full h-12 px-4 bg-white/80 border-2 border-gray-200 rounded-xl shadow-lg focus:outline-none focus:ring-4 focus:ring-amber-500/20 focus:border-amber-500 transition-all duration-300 hover:shadow-xl" placeholder="<?= lang('Inventory.lastSupplierPrice') ?>">
                                    <div class="relative">
                                        <select name="supplier_price_currency" data-force-select2 data-allow-clear="false" class="w-full h-12 rounded-xl border-2 border-gray-200 bg-white/80 px-3 shadow-lg transition-all duration-300 hover:shadow-xl focus:border-amber-500 focus:outline-none focus:ring-4 focus:ring-amber-500/20">
                                            <?php foreach (($currencies ?? ['ILS']) as $currency): ?>
                                                <option value="<?= esc($currency) ?>" <?= old('supplier_price_currency', 'ILS') === $currency ? 'selected' : '' ?>><?= esc($currency) ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                        <div class="pointer-events-none absolute inset-y-0 <?= !empty($is_rtl) ? 'left-0 pl-3' : 'right-0 pr-3' ?> flex items-center">
                                            <i class="fas fa-chevron-down text-gray-400"></i>
                                        </div>
                                    </div>
                                </div>
                                <p class="mt-2 text-xs text-gray-500"><?= lang('Inventory.supplierMappingHelp') ?></p>
                            </div>
                        </section>

                        <!-- Purchase Date and Expiry Date -->
                        <section class="inventory-create-form-section">
                            <div class="inventory-create-section-title">
                                <i class="fas fa-calendar text-orange-600"></i>
                                <span><?= lang('Inventory.purchaseDate') ?></span>
                            </div>
                        <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                            <div class="group/field relative">
                                <label class="block text-sm font-bold text-gray-700 mb-3 flex items-center space-x-2">
                                    <i class="fas fa-calendar text-orange-600"></i>
                                    <span><?= lang('Inventory.purchaseDate') ?></span>
                                </label>
                                <div class="relative">
                                <input type="text" name="purchase_date" data-clinic-date="1" value="<?= old('purchase_date', date('Y-m-d')) ?>" class="inventory-date-input w-full py-3 bg-white/80 border-2 border-gray-200 rounded-xl shadow-lg focus:outline-none focus:ring-4 focus:ring-orange-500/20 focus:border-orange-500 transition-all duration-300 hover:shadow-xl">
                                    <div class="absolute inset-y-0 <?= !empty($is_rtl) ? 'left-0 pl-3' : 'right-0 pr-3' ?> flex items-center pointer-events-none">
                                        <i class="fas fa-calendar text-gray-400"></i>
                                    </div>
                                </div>
                                <?php if (session()->getFlashdata('validation') && session()->getFlashdata('validation')->hasError('purchase_date')): ?>
                                    <p class="text-red-500 text-sm mt-2 flex items-center space-x-1">
                                        <i class="fas fa-exclamation-circle"></i>
                                        <span><?= session()->getFlashdata('validation')->getError('purchase_date') ?></span>
                                    </p>
                                <?php endif; ?>
                            </div>

                            <div class="group/field relative">
                                <label class="block text-sm font-bold text-gray-700 mb-3 flex items-center space-x-2">
                                    <i class="fas fa-calendar-times text-red-600"></i>
                                    <span><?= lang('Inventory.expiryDate') ?></span>
                                </label>
                                <div class="relative">
                                <input type="text" name="expiry_date" data-clinic-date="1" value="<?= old('expiry_date') ?>" class="inventory-date-input w-full py-3 bg-white/80 border-2 border-gray-200 rounded-xl shadow-lg focus:outline-none focus:ring-4 focus:ring-red-500/20 focus:border-red-500 transition-all duration-300 hover:shadow-xl">
                                    <div class="absolute inset-y-0 <?= !empty($is_rtl) ? 'left-0 pl-3' : 'right-0 pr-3' ?> flex items-center pointer-events-none">
                                        <i class="fas fa-calendar-times text-gray-400"></i>
                                    </div>
                                </div>
                                <?php if (session()->getFlashdata('validation') && session()->getFlashdata('validation')->hasError('expiry_date')): ?>
                                    <p class="text-red-500 text-sm mt-2 flex items-center space-x-1">
                                        <i class="fas fa-exclamation-circle"></i>
                                        <span><?= session()->getFlashdata('validation')->getError('expiry_date') ?></span>
                                    </p>
                                <?php endif; ?>
                            </div>
                        </div>

                        <!-- Location -->
                        <div class="group/field relative mt-6">
                            <label class="block text-sm font-bold text-gray-700 mb-3 flex items-center space-x-2">
                                <i class="fas fa-map-marker-alt text-teal-600"></i>
                                <span><?= lang('Inventory.location') ?></span>
                            </label>
                            <div class="relative">
                                <input type="text" name="location" value="<?= old('location') ?>" class="inventory-leading-input w-full py-3 bg-white/80 border-2 border-gray-200 rounded-xl shadow-lg focus:outline-none focus:ring-4 focus:ring-teal-500/20 focus:border-teal-500 transition-all duration-300 hover:shadow-xl" placeholder="<?= lang('Inventory.storageLocationExample') ?>">
                                <div class="absolute inset-y-0 <?= !empty($is_rtl) ? 'left-0 pl-3' : 'right-0 pr-3' ?> flex items-center pointer-events-none">
                                    <i class="fas fa-map-marker-alt text-gray-400"></i>
                                </div>
                            </div>
                            <?php if (session()->getFlashdata('validation') && session()->getFlashdata('validation')->hasError('location')): ?>
                                <p class="text-red-500 text-sm mt-2 flex items-center space-x-1">
                                    <i class="fas fa-exclamation-circle"></i>
                                    <span><?= session()->getFlashdata('validation')->getError('location') ?></span>
                                </p>
                            <?php endif; ?>
                        </div>
                        </section>

                        <!-- Notes Section -->
                        <section class="inventory-create-form-section">
                            <div class="inventory-create-section-title">
                                <i class="fas fa-sticky-note text-violet-600"></i>
                                <span><?= lang('Inventory.additionalNotes') ?></span>
                            </div>
                        <div class="group/field relative">
                            <label class="block text-sm font-bold text-gray-700 mb-3 flex items-center space-x-2">
                                <i class="fas fa-sticky-note text-violet-600"></i>
                                <span><?= lang('Inventory.additionalNotes') ?></span>
                            </label>
                            <div class="relative">
                                <textarea name="notes" rows="4" class="w-full px-4 py-3 bg-white/80 border-2 border-gray-200 rounded-xl shadow-lg focus:outline-none focus:ring-4 focus:ring-violet-500/20 focus:border-violet-500 transition-all duration-300 hover:shadow-xl resize-none" placeholder="<?= lang('Inventory.additionalNotesPlaceholder') ?>"><?= old('notes') ?></textarea>
                                <div class="absolute bottom-3 <?= !empty($is_rtl) ? 'left-3' : 'right-3' ?> text-gray-400 text-xs">
                                    <i class="fas fa-edit"></i>
                                </div>
                            </div>
                            <?php if (session()->getFlashdata('validation') && session()->getFlashdata('validation')->hasError('notes')): ?>
                                <p class="text-red-500 text-sm mt-2 flex items-center space-x-1">
                                    <i class="fas fa-exclamation-circle"></i>
                                    <span><?= session()->getFlashdata('validation')->getError('notes') ?></span>
                                </p>
                            <?php endif; ?>
                        </div>
                        </section>

                        <!-- Form Actions -->
                        <div class="flex flex-col sm:flex-row justify-end gap-4 sm:gap-12 pt-6 border-t border-white/20">
                            <a href="<?= base_url('inventory') ?>" class="group/btn relative inline-flex items-center justify-center px-8 py-3 border-2 border-gray-300 text-gray-700 text-sm font-bold rounded-xl hover:bg-gradient-to-r hover:from-gray-50 hover:to-slate-50 hover:border-gray-400 transition-all duration-300 hover:scale-105">
                                <div class="absolute inset-0 bg-gradient-to-r from-gray-500/10 to-slate-500/10 rounded-xl blur opacity-0 group-hover/btn:opacity-100 transition-opacity duration-300"></div>
                                <i class="fas fa-times mr-2 relative z-10"></i>
                                <span class="relative z-10"><?= lang('Inventory.cancel') ?></span>
                            </a>
                            <button type="submit" class="group/btn relative inline-flex items-center justify-center px-8 py-3 bg-gradient-to-r from-emerald-500 to-teal-600 text-white text-sm font-bold rounded-xl hover:from-emerald-600 hover:to-teal-700 transition-all duration-300 hover:scale-105 hover:shadow-2xl hover:shadow-emerald-500/25">
                                <div class="absolute inset-0 bg-gradient-to-r from-emerald-500/20 to-teal-600/20 rounded-xl blur opacity-0 group-hover/btn:opacity-100 transition-opacity duration-300"></div>
                                <i class="fas fa-boxes mr-2 relative z-10"></i>
                                <span class="relative z-10"><?= lang('Inventory.addItem') ?></span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<div id="quickSupplierModal" class="fixed inset-0 z-50 hidden overflow-y-auto p-4" role="dialog" aria-modal="true" aria-labelledby="quickSupplierModalTitle">
    <div class="inventory-create-modal-backdrop fixed inset-0" data-quick-supplier-close></div>
    <div class="relative flex min-h-full items-center justify-center">
        <div class="inventory-create-modal-panel relative rounded-2xl bg-white shadow-2xl" onclick="event.stopPropagation()">
            <div class="flex items-start justify-between gap-4 border-b border-slate-100 px-6 py-5">
                <div>
                    <h3 id="quickSupplierModalTitle" class="text-lg font-bold text-slate-900"><?= lang('Inventory.quickAddSupplierTitle') ?></h3>
                    <p class="mt-1 text-sm text-slate-500"><?= lang('Inventory.quickAddSupplierHelp') ?></p>
                </div>
                <button type="button" class="inline-flex h-10 w-10 items-center justify-center rounded-full border border-slate-200 text-slate-500 transition hover:border-slate-300 hover:text-slate-700" data-quick-supplier-close>
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="space-y-4 px-6 py-6">
                <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                    <input type="text" id="quickSupplierName" class="w-full rounded-xl border-2 border-indigo-100 bg-white px-4 py-3 shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-4 focus:ring-indigo-500/15" placeholder="<?= lang('Inventory.supplierNameRequired') ?>">
                    <input type="text" id="quickSupplierContact" class="w-full rounded-xl border-2 border-indigo-100 bg-white px-4 py-3 shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-4 focus:ring-indigo-500/15" placeholder="<?= lang('Inventory.contactPerson') ?>">
                    <input type="text" id="quickSupplierPhone" class="w-full rounded-xl border-2 border-indigo-100 bg-white px-4 py-3 shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-4 focus:ring-indigo-500/15" placeholder="<?= lang('Inventory.phone') ?>">
                    <input type="email" id="quickSupplierEmail" class="w-full rounded-xl border-2 border-indigo-100 bg-white px-4 py-3 shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-4 focus:ring-indigo-500/15" placeholder="<?= lang('Inventory.email') ?>">
                </div>
                <p id="quickSupplierStatus" class="text-xs text-slate-500"></p>
            </div>
            <div class="flex flex-col-reverse gap-3 border-t border-slate-100 px-6 py-4 sm:flex-row sm:items-center sm:justify-end">
                <button type="button" class="inline-flex items-center justify-center rounded-xl border-2 border-slate-200 px-4 py-2.5 text-sm font-semibold text-slate-700 transition hover:border-slate-300 hover:bg-slate-50" data-quick-supplier-close>
                    <?= lang('Inventory.cancel') ?>
                </button>
                <button type="button" id="createQuickSupplierBtn" class="inline-flex items-center justify-center rounded-xl bg-indigo-600 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-indigo-700">
                    <i class="fas fa-plus mr-2"></i><?= lang('Inventory.createSupplier') ?>
                </button>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const modal = document.getElementById('quickSupplierModal');
    const openBtn = document.getElementById('openQuickSupplierModal');
    const createBtn = document.getElementById('createQuickSupplierBtn');
    const statusEl = document.getElementById('quickSupplierStatus');
    const supplierSelect = document.getElementById('supplier_id');
    const supplierText = document.getElementById('supplier_text');
    const quickSupplierFields = [
        document.getElementById('quickSupplierName'),
        document.getElementById('quickSupplierContact'),
        document.getElementById('quickSupplierPhone'),
        document.getElementById('quickSupplierEmail')
    ];
    let quickSupplierTrigger = null;

    function openQuickSupplierModal(trigger) {
        if (!modal) {
            return;
        }
        quickSupplierTrigger = trigger || document.activeElement;
        modal.classList.remove('hidden');
        document.body.classList.add('overflow-hidden');
        statusEl.textContent = '';
        statusEl.className = 'text-xs text-slate-500';
        window.setTimeout(() => quickSupplierFields[0]?.focus(), 20);
    }

    function closeQuickSupplierModal() {
        if (!modal) {
            return;
        }
        modal.classList.add('hidden');
        document.body.classList.remove('overflow-hidden');
        if (quickSupplierTrigger && typeof quickSupplierTrigger.focus === 'function') {
            quickSupplierTrigger.focus();
        }
    }

    if (openBtn) {
        openBtn.addEventListener('click', function() {
            openQuickSupplierModal(this);
        });
    }

    document.querySelectorAll('[data-quick-supplier-close]').forEach((element) => {
        element.addEventListener('click', closeQuickSupplierModal);
    });

    document.addEventListener('keydown', function(event) {
        if (event.key === 'Escape' && modal && !modal.classList.contains('hidden')) {
            closeQuickSupplierModal();
        }
    });

    if (modal) {
        modal.addEventListener('click', function(event) {
            if (!event.target.closest('.inventory-create-modal-panel')) {
                closeQuickSupplierModal();
            }
        });
    }

    if (createBtn) {
        createBtn.addEventListener('click', async function() {
            const name = (document.getElementById('quickSupplierName')?.value || '').trim();
            if (!name) {
                statusEl.textContent = '<?= lang('Inventory.supplierNameRequiredMessage') ?>';
                statusEl.className = 'text-xs text-red-600';
                return;
            }

            const formData = new FormData();
            formData.append('<?= csrf_token() ?>', '<?= csrf_hash() ?>');
            formData.append('name', name);
            formData.append('contact_person', document.getElementById('quickSupplierContact')?.value || '');
            formData.append('phone', document.getElementById('quickSupplierPhone')?.value || '');
            formData.append('email', document.getElementById('quickSupplierEmail')?.value || '');

            createBtn.disabled = true;
            statusEl.textContent = '<?= lang('Inventory.creatingSupplier') ?>';
            statusEl.className = 'text-xs text-indigo-700';

            try {
                const response = await fetch('<?= base_url('inventory/suppliers/quick-create') ?>', {
                    method: 'POST',
                    headers: { 'X-Requested-With': 'XMLHttpRequest' },
                    body: formData
                });
                const data = await response.json();
                if (!response.ok || !data.success || !data.supplier) {
                    throw new Error(data.message || '<?= lang('Inventory.failedToCreateSupplier') ?>');
                }

                const option = document.createElement('option');
                option.value = String(data.supplier.id);
                option.textContent = data.supplier.name;
                supplierSelect.appendChild(option);
                supplierSelect.value = String(data.supplier.id);
                if (window.jQuery && window.jQuery.fn && window.jQuery.fn.select2) {
                    window.jQuery(supplierSelect).val(String(data.supplier.id)).trigger('change');
                }
                if (supplierText) supplierText.value = data.supplier.name;

                statusEl.textContent = data.duplicate ? '<?= lang('Inventory.supplierAlreadyExistedSelected') ?>' : '<?= lang('Inventory.supplierCreatedSelected') ?>';
                statusEl.className = 'text-xs text-emerald-700';
                quickSupplierFields.forEach((field) => {
                    if (field) {
                        field.value = '';
                    }
                });
                window.setTimeout(closeQuickSupplierModal, 350);
            } catch (error) {
                statusEl.textContent = error.message || '<?= lang('Inventory.failedToCreateSupplierWithPeriod') ?>';
                statusEl.className = 'text-xs text-red-600';
            } finally {
                createBtn.disabled = false;
            }
        });
    }

    if (supplierSelect && supplierText) {
        supplierSelect.addEventListener('change', function() {
            const selected = supplierSelect.options[supplierSelect.selectedIndex];
            if (selected && supplierSelect.value) {
                supplierText.value = selected.textContent.trim();
            }
        });
    }
});
</script>
<?= $this->endSection() ?>


