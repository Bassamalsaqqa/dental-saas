<?= $this->extend('layouts/main_auth') ?>

<?= $this->section('content') ?>
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
                        <!-- Item Name and Category -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                            <div class="group/field relative">
                                <label class="block text-sm font-bold text-gray-700 mb-3 flex items-center space-x-2">
                                    <i class="fas fa-tag text-emerald-600"></i>
                                    <span><?= lang('Inventory.itemNameRequired') ?></span>
                                </label>
                                <div class="relative">
                                    <input type="text" name="item_name" value="<?= old('item_name') ?>" class="w-full px-4 py-3 bg-white/80 border-2 border-gray-200 rounded-xl shadow-lg focus:outline-none focus:ring-4 focus:ring-emerald-500/20 focus:border-emerald-500 transition-all duration-300 hover:shadow-xl" placeholder="<?= lang('Inventory.enterItemName') ?>" required>
                                    <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
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
                                    <select name="category" data-force-select2 class="w-full px-4 py-3 bg-white/80 border-2 border-gray-200 rounded-xl shadow-lg focus:outline-none focus:ring-4 focus:ring-blue-500/20 focus:border-blue-500 transition-all duration-300 hover:shadow-xl" required>
                                        <option value=""><?= lang('Inventory.selectCategory') ?></option>
                                        <option value="medication" <?= (old('category') == 'medication') ? 'selected' : '' ?>><?= lang('Inventory.categoryMedication') ?></option>
                                        <option value="equipment" <?= (old('category') == 'equipment') ? 'selected' : '' ?>><?= lang('Inventory.categoryEquipment') ?></option>
                                        <option value="supplies" <?= (old('category') == 'supplies') ? 'selected' : '' ?>><?= lang('Inventory.categorySupplies') ?></option>
                                        <option value="consumables" <?= (old('category') == 'consumables') ? 'selected' : '' ?>><?= lang('Inventory.categoryConsumables') ?></option>
                                        <option value="other" <?= (old('category') == 'other') ? 'selected' : '' ?>><?= lang('Inventory.categoryOther') ?></option>
                                    </select>
                                    <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
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
                        </div>

                        <!-- Description Section -->
                        <div class="group/field relative">
                            <label class="block text-sm font-bold text-gray-700 mb-3 flex items-center space-x-2">
                                <i class="fas fa-align-left text-purple-600"></i>
                                <span><?= lang('Inventory.description') ?></span>
                            </label>
                            <div class="relative">
                                <textarea name="description" rows="4" class="w-full px-4 py-3 bg-white/80 border-2 border-gray-200 rounded-xl shadow-lg focus:outline-none focus:ring-4 focus:ring-purple-500/20 focus:border-purple-500 transition-all duration-300 hover:shadow-xl resize-none" placeholder="<?= lang('Inventory.itemDescriptionPlaceholder') ?>"><?= old('description') ?></textarea>
                                <div class="absolute bottom-3 right-3 text-gray-400 text-xs">
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

                        <!-- Quantity, Unit & Min Quantity -->
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                            <div class="group/field relative">
                                <label class="block text-sm font-bold text-gray-700 mb-3 flex items-center space-x-2">
                                    <i class="fas fa-cubes text-amber-600"></i>
                                    <span><?= lang('Inventory.quantityRequired') ?></span>
                                </label>
                                <div class="relative">
                                    <input type="number" name="quantity" value="<?= old('quantity') ?>" class="w-full px-4 py-3 bg-white/80 border-2 border-gray-200 rounded-xl shadow-lg focus:outline-none focus:ring-4 focus:ring-amber-500/20 focus:border-amber-500 transition-all duration-300 hover:shadow-xl" min="0" placeholder="0" required>
                                    <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
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
                                    <select name="unit" data-force-select2 class="w-full px-4 py-3 bg-white/80 border-2 border-gray-200 rounded-xl shadow-lg focus:outline-none focus:ring-4 focus:ring-cyan-500/20 focus:border-cyan-500 transition-all duration-300 hover:shadow-xl">
                                        <option value="pieces" <?= (old('unit') == 'pieces') ? 'selected' : '' ?>><?= lang('Inventory.unitPieces') ?></option>
                                        <option value="boxes" <?= (old('unit') == 'boxes') ? 'selected' : '' ?>><?= lang('Inventory.unitBoxes') ?></option>
                                        <option value="bottles" <?= (old('unit') == 'bottles') ? 'selected' : '' ?>><?= lang('Inventory.unitBottles') ?></option>
                                        <option value="tubes" <?= (old('unit') == 'tubes') ? 'selected' : '' ?>><?= lang('Inventory.unitTubes') ?></option>
                                        <option value="packs" <?= (old('unit') == 'packs') ? 'selected' : '' ?>><?= lang('Inventory.unitPacks') ?></option>
                                        <option value="sets" <?= (old('unit') == 'sets') ? 'selected' : '' ?>><?= lang('Inventory.unitSets') ?></option>
                                    </select>
                                    <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
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
                                    <input type="number" name="min_quantity" value="<?= old('min_quantity') ?>" class="w-full px-4 py-3 bg-white/80 border-2 border-gray-200 rounded-xl shadow-lg focus:outline-none focus:ring-4 focus:ring-rose-500/20 focus:border-rose-500 transition-all duration-300 hover:shadow-xl" min="0" placeholder="0" required>
                                    <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
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

                        <!-- Unit Price and Supplier -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                            <div class="group/field relative">
                                <label class="block text-sm font-bold text-gray-700 mb-3 flex items-center space-x-2">
                                    <i class="fas fa-dollar-sign text-green-600"></i>
                                    <span><?= lang('Inventory.unitPriceRequired') ?></span>
                                </label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                                        <span class="text-gray-500 font-bold"><?= getCurrencySymbol() ?></span>
                                    </div>
                                    <input type="number" name="unit_price" value="<?= old('unit_price') ?>" step="0.01" min="0" class="w-full px-4 py-3 pl-8 bg-white/80 border-2 border-gray-200 rounded-xl shadow-lg focus:outline-none focus:ring-4 focus:ring-green-500/20 focus:border-green-500 transition-all duration-300 hover:shadow-xl" placeholder="0.00" required>
                                    <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
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
                                    <span><?= lang('Inventory.supplier') ?></span>
                                </label>
                                <div class="space-y-3">
                                    <div class="flex items-center justify-between gap-3">
                                        <label for="supplier_id" class="text-xs font-semibold text-gray-500 uppercase tracking-wide"><?= lang('Inventory.supplierDirectory') ?></label>
                                        <div class="flex items-center gap-2">
                                            <a href="<?= base_url('inventory/suppliers') ?>" target="_blank" class="text-xs font-semibold text-blue-600 hover:text-blue-800"><?= lang('Inventory.manageSuppliers') ?></a>
                                            <button type="button" id="toggleQuickSupplierCreate" class="text-xs font-semibold text-indigo-600 hover:text-indigo-800"><?= lang('Inventory.quickAdd') ?></button>
                                        </div>
                                    </div>
                                    <select id="supplier_id" name="supplier_id" data-force-select2 class="w-full px-4 py-3 bg-white/80 border-2 border-gray-200 rounded-xl shadow-lg focus:outline-none focus:ring-4 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all duration-300 hover:shadow-xl">
                                        <option value=""><?= lang('Inventory.noLinkedSupplier') ?></option>
                                        <?php foreach (($supplier_directory ?? []) as $supplier): ?>
                                            <option value="<?= (int) $supplier['id'] ?>" <?= (string) old('supplier_id') === (string) $supplier['id'] ? 'selected' : '' ?>>
                                                <?= esc($supplier['name']) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                    <div class="relative">
                                        <input type="text" name="supplier" id="supplier_text" value="<?= old('supplier') ?>" class="w-full px-4 py-3 bg-white/80 border-2 border-gray-200 rounded-xl shadow-lg focus:outline-none focus:ring-4 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all duration-300 hover:shadow-xl" placeholder="<?= lang('Inventory.customSupplierNameOptional') ?>">
                                        <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
                                            <i class="fas fa-truck text-gray-400"></i>
                                        </div>
                                    </div>
                                    <div id="quickSupplierCreatePanel" class="hidden p-4 rounded-xl border border-indigo-100 bg-indigo-50/60 space-y-3">
                                        <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                                            <input type="text" id="quickSupplierName" class="w-full px-3 py-2 border border-indigo-200 rounded-lg" placeholder="<?= lang('Inventory.supplierNameRequired') ?>">
                                            <input type="text" id="quickSupplierContact" class="w-full px-3 py-2 border border-indigo-200 rounded-lg" placeholder="<?= lang('Inventory.contactPerson') ?>">
                                            <input type="text" id="quickSupplierPhone" class="w-full px-3 py-2 border border-indigo-200 rounded-lg" placeholder="<?= lang('Inventory.phone') ?>">
                                            <input type="email" id="quickSupplierEmail" class="w-full px-3 py-2 border border-indigo-200 rounded-lg" placeholder="<?= lang('Inventory.email') ?>">
                                        </div>
                                        <div class="flex items-center justify-between">
                                            <p id="quickSupplierStatus" class="text-xs text-gray-600"></p>
                                            <button type="button" id="createQuickSupplierBtn" class="inline-flex items-center px-3 py-2 bg-indigo-600 text-white text-xs font-semibold rounded-lg hover:bg-indigo-700">
                                                <i class="fas fa-plus mr-2"></i><?= lang('Inventory.createSupplier') ?>
                                            </button>
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

                            <div class="group/field relative">
                                <label class="block text-sm font-bold text-gray-700 mb-3 flex items-center space-x-2">
                                    <i class="fas fa-link text-amber-600"></i>
                                    <span><?= lang('Inventory.supplierMappingOptional') ?></span>
                                </label>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <input type="text" name="supplier_sku" value="<?= old('supplier_sku') ?>" class="w-full px-4 py-3 bg-white/80 border-2 border-gray-200 rounded-xl shadow-lg focus:outline-none focus:ring-4 focus:ring-amber-500/20 focus:border-amber-500 transition-all duration-300 hover:shadow-xl" placeholder="<?= lang('Inventory.supplierSku') ?>">
                                    <input type="text" name="pack_size_label" value="<?= old('pack_size_label') ?>" class="w-full px-4 py-3 bg-white/80 border-2 border-gray-200 rounded-xl shadow-lg focus:outline-none focus:ring-4 focus:ring-amber-500/20 focus:border-amber-500 transition-all duration-300 hover:shadow-xl" placeholder="<?= lang('Inventory.packSizeExample') ?>">
                                    <input type="number" min="1" step="1" name="preferred_order_qty" value="<?= old('preferred_order_qty') ?>" class="w-full px-4 py-3 bg-white/80 border-2 border-gray-200 rounded-xl shadow-lg focus:outline-none focus:ring-4 focus:ring-amber-500/20 focus:border-amber-500 transition-all duration-300 hover:shadow-xl" placeholder="<?= lang('Inventory.preferredReorderQty') ?>">
                                    <div class="grid grid-cols-3 gap-2">
                                        <input type="number" min="0" step="0.01" name="supplier_last_unit_price" value="<?= old('supplier_last_unit_price') ?>" class="col-span-2 w-full px-4 py-3 bg-white/80 border-2 border-gray-200 rounded-xl shadow-lg focus:outline-none focus:ring-4 focus:ring-amber-500/20 focus:border-amber-500 transition-all duration-300 hover:shadow-xl" placeholder="<?= lang('Inventory.lastSupplierPrice') ?>">
                                        <select name="supplier_price_currency" data-force-select2 class="w-full px-3 py-3 bg-white/80 border-2 border-gray-200 rounded-xl shadow-lg focus:outline-none focus:ring-4 focus:ring-amber-500/20 focus:border-amber-500 transition-all duration-300 hover:shadow-xl">
                                            <?php foreach (($currencies ?? ['ILS']) as $currency): ?>
                                                <option value="<?= esc($currency) ?>" <?= old('supplier_price_currency', 'ILS') === $currency ? 'selected' : '' ?>><?= esc($currency) ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                </div>
                                <p class="mt-2 text-xs text-gray-500"><?= lang('Inventory.supplierMappingHelp') ?></p>
                            </div>
                        </div>

                        <!-- Purchase Date and Expiry Date -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                            <div class="group/field relative">
                                <label class="block text-sm font-bold text-gray-700 mb-3 flex items-center space-x-2">
                                    <i class="fas fa-calendar text-orange-600"></i>
                                    <span><?= lang('Inventory.purchaseDate') ?></span>
                                </label>
                                <div class="relative">
                                <input type="text" name="purchase_date" data-clinic-date="1" value="<?= old('purchase_date', date('Y-m-d')) ?>" class="w-full px-4 py-3 bg-white/80 border-2 border-gray-200 rounded-xl shadow-lg focus:outline-none focus:ring-4 focus:ring-orange-500/20 focus:border-orange-500 transition-all duration-300 hover:shadow-xl">
                                    <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
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
                                <input type="text" name="expiry_date" data-clinic-date="1" value="<?= old('expiry_date') ?>" class="w-full px-4 py-3 bg-white/80 border-2 border-gray-200 rounded-xl shadow-lg focus:outline-none focus:ring-4 focus:ring-red-500/20 focus:border-red-500 transition-all duration-300 hover:shadow-xl">
                                    <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
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
                        <div class="group/field relative">
                            <label class="block text-sm font-bold text-gray-700 mb-3 flex items-center space-x-2">
                                <i class="fas fa-map-marker-alt text-teal-600"></i>
                                <span><?= lang('Inventory.location') ?></span>
                            </label>
                            <div class="relative">
                                <input type="text" name="location" value="<?= old('location') ?>" class="w-full px-4 py-3 bg-white/80 border-2 border-gray-200 rounded-xl shadow-lg focus:outline-none focus:ring-4 focus:ring-teal-500/20 focus:border-teal-500 transition-all duration-300 hover:shadow-xl" placeholder="<?= lang('Inventory.storageLocationExample') ?>">
                                <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
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

                        <!-- Notes Section -->
                        <div class="group/field relative">
                            <label class="block text-sm font-bold text-gray-700 mb-3 flex items-center space-x-2">
                                <i class="fas fa-sticky-note text-violet-600"></i>
                                <span><?= lang('Inventory.additionalNotes') ?></span>
                            </label>
                            <div class="relative">
                                <textarea name="notes" rows="4" class="w-full px-4 py-3 bg-white/80 border-2 border-gray-200 rounded-xl shadow-lg focus:outline-none focus:ring-4 focus:ring-violet-500/20 focus:border-violet-500 transition-all duration-300 hover:shadow-xl resize-none" placeholder="<?= lang('Inventory.additionalNotesPlaceholder') ?>"><?= old('notes') ?></textarea>
                                <div class="absolute bottom-3 right-3 text-gray-400 text-xs">
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

<script>
document.addEventListener('DOMContentLoaded', function() {
    const toggleBtn = document.getElementById('toggleQuickSupplierCreate');
    const panel = document.getElementById('quickSupplierCreatePanel');
    const createBtn = document.getElementById('createQuickSupplierBtn');
    const statusEl = document.getElementById('quickSupplierStatus');
    const supplierSelect = document.getElementById('supplier_id');
    const supplierText = document.getElementById('supplier_text');

    if (toggleBtn && panel) {
        toggleBtn.addEventListener('click', function() {
            panel.classList.toggle('hidden');
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
                if (supplierText) supplierText.value = data.supplier.name;

                statusEl.textContent = data.duplicate ? '<?= lang('Inventory.supplierAlreadyExistedSelected') ?>' : '<?= lang('Inventory.supplierCreatedSelected') ?>';
                statusEl.className = 'text-xs text-emerald-700';
                panel.classList.add('hidden');
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

