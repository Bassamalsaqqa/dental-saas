<?= $this->extend('layouts/main_auth') ?>

<?= $this->section('content') ?>
<div class="px-6 py-8">
    <!-- Page Header -->
    <div class="mb-8">
        <div class="flex flex-col lg:flex-row lg:justify-between lg:items-center space-y-6 lg:space-y-0">
            <div>
                <h1 class="text-4xl font-bold text-gray-800 mb-3"><?= lang('Inventory.editItem') ?></h1>
                <p class="text-gray-600 text-lg"><?= lang('Inventory.updateInventoryItemInformation') ?></p>
            </div>
            <div class="flex flex-col sm:flex-row space-y-3 sm:space-y-0 sm:space-x-4">
                <a href="<?= nav_back('inventory') ?>" class="inline-flex items-center px-4 py-2 bg-gray-100 text-gray-700 text-sm font-medium rounded-md hover:bg-gray-200 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition-colors">
                    <i class="fas fa-arrow-left mr-2"></i><?= lang('Inventory.backToInventory') ?>
                </a>
                <a href="<?= base_url('inventory/' . $item['id']) ?>" class="inline-flex items-center px-4 py-2 bg-blue-100 text-blue-700 text-sm font-medium rounded-md hover:bg-blue-200 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition-colors">
                    <i class="fas fa-eye mr-2"></i><?= lang('Inventory.viewItem') ?>
                </a>
            </div>
        </div>
    </div> 

    <!-- Error Messages -->
    <?php if (session()->getFlashdata('error')): ?>
        <div class="mb-6 bg-red-50 border border-red-200 rounded-lg p-4">
            <div class="flex">
                <div class="flex-shrink-0">
                    <i class="fas fa-exclamation-triangle text-red-400"></i>
                </div>
                <div class="ml-3">
                    <h3 class="text-sm font-medium text-red-800"><?= lang('Inventory.error') ?></h3>
                    <div class="mt-2 text-sm text-red-700">
                        <p><?= session()->getFlashdata('error') ?></p>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <!-- Success Messages -->
    <?php if (session()->getFlashdata('success')): ?>
        <div class="mb-6 bg-green-50 border border-green-200 rounded-lg p-4">
            <div class="flex">
                <div class="flex-shrink-0">
                    <i class="fas fa-check-circle text-green-400"></i>
                </div>
                <div class="ml-3">
                    <h3 class="text-sm font-medium text-green-800"><?= lang('Inventory.success') ?></h3>
                    <div class="mt-2 text-sm text-green-700">
                        <p><?= session()->getFlashdata('success') ?></p>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <!-- Edit Form -->
    <div class="bg-white rounded-lg border border-gray-200 shadow-sm group hover:shadow-lg transition-all duration-300">
        <div class="p-8">
            <form action="<?= base_url('inventory/' . $item['id'] . '/update') ?>" method="POST" class="space-y-8">
                <?= csrf_field() ?>
                
                <!-- Basic Information -->
                <div>
                    <h3 class="text-lg font-semibold text-gray-900 mb-6"><?= lang('Inventory.basicInformation') ?></h3>
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                        <div>
                            <label for="item_name" class="block text-sm font-medium text-gray-700 mb-2"><?= lang('Inventory.itemNameRequired') ?></label>
                            <input type="text" id="item_name" name="item_name" value="<?= old('item_name', $item['item_name']) ?>" 
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500" 
                                   required>
                        </div>
                        
                        <div>
                            <label for="category" class="block text-sm font-medium text-gray-700 mb-2"><?= lang('Inventory.categoryRequired') ?></label>
                            <select id="category" name="category" data-force-select2 data-allow-clear="false"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500" 
                                    required>
                                <option value=""><?= lang('Inventory.selectCategory') ?></option>
                                <?php foreach ($categories as $key => $label): ?>
                                    <option value="<?= $key ?>" <?= old('category', $item['category']) == $key ? 'selected' : '' ?>>
                                        <?= $label ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    
                    <div class="mt-6">
                        <label for="description" class="block text-sm font-medium text-gray-700 mb-2"><?= lang('Inventory.description') ?></label>
                        <textarea id="description" name="description" rows="3" 
                                  class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"><?= old('description', $item['description']) ?></textarea>
                    </div>
                </div>

                <!-- Stock Information -->
                <div>
                    <h3 class="text-lg font-semibold text-gray-900 mb-6"><?= lang('Inventory.stockInformation') ?></h3>
                    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                        <div>
                            <label for="quantity" class="block text-sm font-medium text-gray-700 mb-2"><?= lang('Inventory.currentQuantityRequired') ?></label>
                            <input type="number" id="quantity" name="quantity" value="<?= old('quantity', $item['quantity']) ?>" 
                                   min="0" step="1"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500" 
                                   required>
                        </div>
                        
                        <div>
                            <label for="min_quantity" class="block text-sm font-medium text-gray-700 mb-2"><?= lang('Inventory.minimumQuantityRequired') ?></label>
                            <input type="number" id="min_quantity" name="min_quantity" value="<?= old('min_quantity', $item['min_quantity']) ?>" 
                                   min="0" step="1"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500" 
                                   required>
                        </div>
                        
                        <div>
                            <label for="unit" class="block text-sm font-medium text-gray-700 mb-2"><?= lang('Inventory.unit') ?></label>
                            <select id="unit" name="unit" data-force-select2 data-allow-clear="false"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                <option value="pieces" <?= old('unit', $item['unit'] ?? 'pieces') == 'pieces' ? 'selected' : '' ?>><?= lang('Inventory.unitPieces') ?></option>
                                <option value="boxes" <?= old('unit', $item['unit'] ?? 'pieces') == 'boxes' ? 'selected' : '' ?>><?= lang('Inventory.unitBoxes') ?></option>
                                <option value="bottles" <?= old('unit', $item['unit'] ?? 'pieces') == 'bottles' ? 'selected' : '' ?>><?= lang('Inventory.unitBottles') ?></option>
                                <option value="tubes" <?= old('unit', $item['unit'] ?? 'pieces') == 'tubes' ? 'selected' : '' ?>><?= lang('Inventory.unitTubes') ?></option>
                                <option value="packs" <?= old('unit', $item['unit'] ?? 'pieces') == 'packs' ? 'selected' : '' ?>><?= lang('Inventory.unitPacks') ?></option>
                                <option value="sets" <?= old('unit', $item['unit'] ?? 'pieces') == 'sets' ? 'selected' : '' ?>><?= lang('Inventory.unitSets') ?></option>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Financial Information -->
                <div>
                    <h3 class="text-lg font-semibold text-gray-900 mb-6"><?= lang('Inventory.financialInformation') ?></h3>
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                        <div>
                            <label for="unit_price" class="block text-sm font-medium text-gray-700 mb-2"><?= lang('Inventory.unitPriceRequired') ?></label>
                            <div class="relative">
                                <div class="absolute inset-y-0 <?= !empty($is_rtl) ? 'right-0 pr-3' : 'left-0 pl-3' ?> flex items-center pointer-events-none">
                                    <span class="text-gray-500 sm:text-sm"><?= getCurrencySymbol() ?></span>
                                </div>
                                <input type="number" id="unit_price" name="unit_price" value="<?= old('unit_price', $item['unit_price']) ?>" 
                                       min="0" step="0.01"
                                       class="w-full <?= !empty($is_rtl) ? 'pr-10 pl-3' : 'pl-10 pr-3' ?> py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500" 
                                       required>
                            </div>
                        </div>
                        
                        <div>
                            <label for="supplier" class="block text-sm font-medium text-gray-700 mb-2"><?= lang('Inventory.supplier') ?></label>
                            <div class="space-y-3">
                                <div class="flex items-center justify-between gap-2">
                                    <span class="text-xs font-semibold text-gray-500 uppercase tracking-wide"><?= lang('Inventory.supplierDirectory') ?></span>
                                    <div class="flex items-center gap-2">
                                        <a href="<?= base_url('providers') ?>" target="_blank" class="text-xs font-semibold text-blue-600 hover:text-blue-800"><?= lang('Inventory.manageSuppliers') ?></a>
                                        <button type="button" id="toggleQuickSupplierCreate" class="text-xs font-semibold text-indigo-600 hover:text-indigo-800"><?= lang('Inventory.quickAdd') ?></button>
                                    </div>
                                </div>
                                <select id="supplier_id" name="supplier_id" data-force-select2 data-allow-clear="false"
                                        class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                    <option value=""><?= lang('Inventory.noLinkedSupplier') ?></option>
                                    <?php foreach (($supplier_directory ?? []) as $supplier): ?>
                                        <option value="<?= (int) $supplier['id'] ?>" <?= (string) old('supplier_id', $item['supplier_id'] ?? '') === (string) $supplier['id'] ? 'selected' : '' ?>>
                                            <?= esc($supplier['name']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <input type="text" id="supplier_text" name="supplier" value="<?= old('supplier', $item['supplier']) ?>"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                       placeholder="<?= lang('Inventory.customSupplierNameOptional') ?>">
                                <div id="quickSupplierCreatePanel" class="hidden p-3 rounded-lg border border-indigo-100 bg-indigo-50/60 space-y-3">
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
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2"><?= lang('Inventory.supplierMappingOptional') ?></label>
                            <?php $prefLink = $preferred_supplier_link ?? null; ?>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                                <input type="text" name="supplier_sku" value="<?= old('supplier_sku', $prefLink['supplier_sku'] ?? '') ?>" class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500" placeholder="<?= lang('Inventory.supplierSku') ?>">
                                <input type="text" name="pack_size_label" value="<?= old('pack_size_label', $prefLink['pack_size_label'] ?? '') ?>" class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500" placeholder="<?= lang('Inventory.packSize') ?>">
                                <input type="number" min="1" step="1" name="preferred_order_qty" value="<?= old('preferred_order_qty', $prefLink['preferred_order_qty'] ?? '') ?>" class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500" placeholder="<?= lang('Inventory.preferredReorderQty') ?>">
                                <div class="grid grid-cols-3 gap-2">
                                    <input type="number" min="0" step="0.01" name="supplier_last_unit_price" value="<?= old('supplier_last_unit_price', $prefLink['last_unit_price'] ?? '') ?>" class="col-span-2 w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500" placeholder="<?= lang('Inventory.lastSupplierPrice') ?>">
                                    <select name="supplier_price_currency" data-force-select2 data-allow-clear="false" class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                        <?php foreach (($currencies ?? ['ILS']) as $currency): ?>
                                            <option value="<?= esc($currency) ?>" <?= (string) old('supplier_price_currency', $prefLink['currency'] ?? 'ILS') === (string) $currency ? 'selected' : '' ?>><?= esc($currency) ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                            <p class="mt-2 text-xs text-gray-500"><?= lang('Inventory.supplierMappingUpdateHelp') ?></p>
                        </div>
                    </div>
                </div>

                <!-- Additional Information -->
                <div>
                    <h3 class="text-lg font-semibold text-gray-900 mb-6"><?= lang('Inventory.additionalInformation') ?></h3>
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                        <div>
                            <label for="purchase_date" class="block text-sm font-medium text-gray-700 mb-2"><?= lang('Inventory.purchaseDate') ?></label>
                                <input type="text" id="purchase_date" name="purchase_date" data-clinic-date="1" value="<?= old('purchase_date', $item['purchase_date']) ?>"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        </div>
                        
                        <div>
                            <label for="expiry_date" class="block text-sm font-medium text-gray-700 mb-2"><?= lang('Inventory.expiryDate') ?></label>
                                <input type="text" id="expiry_date" name="expiry_date" data-clinic-date="1" value="<?= old('expiry_date', $item['expiry_date']) ?>"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        </div>
                    </div>
                    
                    <div class="mt-6 grid grid-cols-1 lg:grid-cols-2 gap-6">
                        <div>
                            <label for="location" class="block text-sm font-medium text-gray-700 mb-2"><?= lang('Inventory.storageLocation') ?></label>
                            <input type="text" id="location" name="location" value="<?= old('location', $item['location']) ?>" 
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        </div>
                        
                        <div>
                            <label for="status" class="block text-sm font-medium text-gray-700 mb-2"><?= lang('Inventory.statusRequired') ?></label>
                            <select id="status" name="status" data-force-select2 data-allow-clear="false"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500" 
                                    required>
                                <option value="active" <?= old('status', $item['status']) == 'active' ? 'selected' : '' ?>><?= lang('Inventory.statusActive') ?></option>
                                <option value="inactive" <?= old('status', $item['status']) == 'inactive' ? 'selected' : '' ?>><?= lang('Inventory.statusInactive') ?></option>
                                <option value="discontinued" <?= old('status', $item['status']) == 'discontinued' ? 'selected' : '' ?>><?= lang('Inventory.statusDiscontinued') ?></option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="mt-6">
                        <label for="notes" class="block text-sm font-medium text-gray-700 mb-2"><?= lang('Inventory.notes') ?></label>
                        <textarea id="notes" name="notes" rows="4" 
                                  class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"><?= old('notes', $item['notes']) ?></textarea>
                    </div>
                </div>

                <!-- Form Actions -->
                <div class="flex flex-col sm:flex-row gap-3 sm:gap-12 pt-6 border-t border-gray-200">
                    <button type="submit" class="inline-flex items-center px-6 py-3 bg-blue-600 text-white text-sm font-medium rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition-colors">
                        <i class="fas fa-save mr-2"></i><?= lang('Inventory.updateItem') ?>
                    </button>
                    
                    <a href="<?= base_url('inventory/' . $item['id']) ?>" class="inline-flex items-center px-6 py-3 bg-gray-100 text-gray-700 text-sm font-medium rounded-md hover:bg-gray-200 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition-colors">
                        <i class="fas fa-times mr-2"></i><?= lang('Inventory.cancel') ?>
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// Form validation
document.addEventListener('DOMContentLoaded', function() {
    const form = document.querySelector('form');
    const quantityInput = document.getElementById('quantity');
    const minQuantityInput = document.getElementById('min_quantity');
    const unitPriceInput = document.getElementById('unit_price');
    
    // Real-time validation
    quantityInput.addEventListener('input', function() {
        if (this.value < 0) {
            this.setCustomValidity('<?= lang('Inventory.quantityCannotBeNegative') ?>');
        } else {
            this.setCustomValidity('');
        }
    });
    
    minQuantityInput.addEventListener('input', function() {
        if (this.value < 0) {
            this.setCustomValidity('<?= lang('Inventory.minimumQuantityCannotBeNegative') ?>');
        } else {
            this.setCustomValidity('');
        }
    });
    
    unitPriceInput.addEventListener('input', function() {
        if (this.value < 0) {
            this.setCustomValidity('<?= lang('Inventory.unitPriceCannotBeNegative') ?>');
        } else {
            this.setCustomValidity('');
        }
    });
    
    // Form submission validation
    form.addEventListener('submit', function(e) {
        const quantity = parseInt(quantityInput.value);
        const minQuantity = parseInt(minQuantityInput.value);
        
        if (quantity < 0 || minQuantity < 0) {
            e.preventDefault();
            alert('<?= lang('Inventory.enterValidQuantities') ?>');
            return false;
        }
        
        if (unitPriceInput.value < 0) {
            e.preventDefault();
            alert('<?= lang('Inventory.enterValidUnitPrice') ?>');
            return false;
        }
    });

    const toggleBtn = document.getElementById('toggleQuickSupplierCreate');
    const panel = document.getElementById('quickSupplierCreatePanel');
    const createBtn = document.getElementById('createQuickSupplierBtn');
    const statusEl = document.getElementById('quickSupplierStatus');
    const supplierSelect = document.getElementById('supplier_id');
    const supplierText = document.getElementById('supplier_text');

    if (toggleBtn && panel) {
        toggleBtn.addEventListener('click', () => panel.classList.toggle('hidden'));
    }

    if (supplierSelect && supplierText) {
        supplierSelect.addEventListener('change', function() {
            const selected = supplierSelect.options[supplierSelect.selectedIndex];
            if (selected && supplierSelect.value) {
                supplierText.value = selected.textContent.trim();
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
                supplierText.value = data.supplier.name;
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
});
</script>
<?= $this->endSection() ?>

