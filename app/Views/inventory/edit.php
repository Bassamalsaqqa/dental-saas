<?= $this->extend('layouts/main_auth') ?>

<?= $this->section('content') ?>
<div class="px-6 py-8">
    <!-- Page Header -->
    <div class="mb-8">
        <div class="flex flex-col lg:flex-row lg:justify-between lg:items-center space-y-6 lg:space-y-0">
            <div>
                <h1 class="text-4xl font-bold text-gray-800 mb-3">Edit Item</h1>
                <p class="text-gray-600 text-lg">Update inventory item information</p>
            </div>
            <div class="flex flex-col sm:flex-row space-y-3 sm:space-y-0 sm:space-x-4">
                <a href="<?= base_url('inventory') ?>" class="inline-flex items-center px-4 py-2 bg-gray-100 text-gray-700 text-sm font-medium rounded-md hover:bg-gray-200 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition-colors">
                    <i class="fas fa-arrow-left mr-2"></i>Back to Inventory
                </a>
                <a href="<?= base_url('inventory/' . $item['id']) ?>" class="inline-flex items-center px-4 py-2 bg-blue-100 text-blue-700 text-sm font-medium rounded-md hover:bg-blue-200 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition-colors">
                    <i class="fas fa-eye mr-2"></i>View Item
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
                    <h3 class="text-sm font-medium text-red-800">Error</h3>
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
                    <h3 class="text-sm font-medium text-green-800">Success</h3>
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
                    <h3 class="text-lg font-semibold text-gray-900 mb-6">Basic Information</h3>
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                        <div>
                            <label for="item_name" class="block text-sm font-medium text-gray-700 mb-2">Item Name *</label>
                            <input type="text" id="item_name" name="item_name" value="<?= old('item_name', $item['item_name']) ?>" 
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500" 
                                   required>
                        </div>
                        
                        <div>
                            <label for="category" class="block text-sm font-medium text-gray-700 mb-2">Category *</label>
                            <select id="category" name="category" 
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500" 
                                    required>
                                <option value="">Select Category</option>
                                <?php foreach ($categories as $key => $label): ?>
                                    <option value="<?= $key ?>" <?= old('category', $item['category']) == $key ? 'selected' : '' ?>>
                                        <?= $label ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    
                    <div class="mt-6">
                        <label for="description" class="block text-sm font-medium text-gray-700 mb-2">Description</label>
                        <textarea id="description" name="description" rows="3" 
                                  class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"><?= old('description', $item['description']) ?></textarea>
                    </div>
                </div>

                <!-- Stock Information -->
                <div>
                    <h3 class="text-lg font-semibold text-gray-900 mb-6">Stock Information</h3>
                    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                        <div>
                            <label for="quantity" class="block text-sm font-medium text-gray-700 mb-2">Current Quantity *</label>
                            <input type="number" id="quantity" name="quantity" value="<?= old('quantity', $item['quantity']) ?>" 
                                   min="0" step="1"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500" 
                                   required>
                        </div>
                        
                        <div>
                            <label for="min_quantity" class="block text-sm font-medium text-gray-700 mb-2">Minimum Quantity *</label>
                            <input type="number" id="min_quantity" name="min_quantity" value="<?= old('min_quantity', $item['min_quantity']) ?>" 
                                   min="0" step="1"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500" 
                                   required>
                        </div>
                        
                        <div>
                            <label for="unit" class="block text-sm font-medium text-gray-700 mb-2">Unit</label>
                            <select id="unit" name="unit" 
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                <option value="pieces" <?= old('unit', $item['unit'] ?? 'pieces') == 'pieces' ? 'selected' : '' ?>>Pieces</option>
                                <option value="boxes" <?= old('unit', $item['unit'] ?? 'pieces') == 'boxes' ? 'selected' : '' ?>>Boxes</option>
                                <option value="bottles" <?= old('unit', $item['unit'] ?? 'pieces') == 'bottles' ? 'selected' : '' ?>>Bottles</option>
                                <option value="tubes" <?= old('unit', $item['unit'] ?? 'pieces') == 'tubes' ? 'selected' : '' ?>>Tubes</option>
                                <option value="packs" <?= old('unit', $item['unit'] ?? 'pieces') == 'packs' ? 'selected' : '' ?>>Packs</option>
                                <option value="sets" <?= old('unit', $item['unit'] ?? 'pieces') == 'sets' ? 'selected' : '' ?>>Sets</option>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Financial Information -->
                <div>
                    <h3 class="text-lg font-semibold text-gray-900 mb-6">Financial Information</h3>
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                        <div>
                            <label for="unit_price" class="block text-sm font-medium text-gray-700 mb-2">Unit Price *</label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <span class="text-gray-500 sm:text-sm"><?= getCurrencySymbol() ?></span>
                                </div>
                                <input type="number" id="unit_price" name="unit_price" value="<?= old('unit_price', $item['unit_price']) ?>" 
                                       min="0" step="0.01"
                                       class="w-full pl-8 pr-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500" 
                                       required>
                            </div>
                        </div>
                        
                        <div>
                            <label for="supplier" class="block text-sm font-medium text-gray-700 mb-2">Supplier</label>
                            <select id="supplier" name="supplier" 
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                <option value="">Select Supplier</option>
                                <?php foreach ($suppliers as $key => $label): ?>
                                    <option value="<?= $key ?>" <?= old('supplier', $item['supplier']) == $key ? 'selected' : '' ?>>
                                        <?= $label ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Additional Information -->
                <div>
                    <h3 class="text-lg font-semibold text-gray-900 mb-6">Additional Information</h3>
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                        <div>
                            <label for="purchase_date" class="block text-sm font-medium text-gray-700 mb-2">Purchase Date</label>
                            <input type="date" id="purchase_date" name="purchase_date" value="<?= old('purchase_date', $item['purchase_date']) ?>" 
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        </div>
                        
                        <div>
                            <label for="expiry_date" class="block text-sm font-medium text-gray-700 mb-2">Expiry Date</label>
                            <input type="date" id="expiry_date" name="expiry_date" value="<?= old('expiry_date', $item['expiry_date']) ?>" 
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        </div>
                    </div>
                    
                    <div class="mt-6 grid grid-cols-1 lg:grid-cols-2 gap-6">
                        <div>
                            <label for="location" class="block text-sm font-medium text-gray-700 mb-2">Storage Location</label>
                            <input type="text" id="location" name="location" value="<?= old('location', $item['location']) ?>" 
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        </div>
                        
                        <div>
                            <label for="status" class="block text-sm font-medium text-gray-700 mb-2">Status *</label>
                            <select id="status" name="status" 
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500" 
                                    required>
                                <option value="active" <?= old('status', $item['status']) == 'active' ? 'selected' : '' ?>>Active</option>
                                <option value="inactive" <?= old('status', $item['status']) == 'inactive' ? 'selected' : '' ?>>Inactive</option>
                                <option value="discontinued" <?= old('status', $item['status']) == 'discontinued' ? 'selected' : '' ?>>Discontinued</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="mt-6">
                        <label for="notes" class="block text-sm font-medium text-gray-700 mb-2">Notes</label>
                        <textarea id="notes" name="notes" rows="4" 
                                  class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"><?= old('notes', $item['notes']) ?></textarea>
                    </div>
                </div>

                <!-- Form Actions -->
                <div class="flex flex-col sm:flex-row gap-3 sm:gap-12 pt-6 border-t border-gray-200">
                    <button type="submit" class="inline-flex items-center px-6 py-3 bg-blue-600 text-white text-sm font-medium rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition-colors">
                        <i class="fas fa-save mr-2"></i>Update Item
                    </button>
                    
                    <a href="<?= base_url('inventory/' . $item['id']) ?>" class="inline-flex items-center px-6 py-3 bg-gray-100 text-gray-700 text-sm font-medium rounded-md hover:bg-gray-200 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition-colors">
                        <i class="fas fa-times mr-2"></i>Cancel
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
            this.setCustomValidity('Quantity cannot be negative');
        } else {
            this.setCustomValidity('');
        }
    });
    
    minQuantityInput.addEventListener('input', function() {
        if (this.value < 0) {
            this.setCustomValidity('Minimum quantity cannot be negative');
        } else {
            this.setCustomValidity('');
        }
    });
    
    unitPriceInput.addEventListener('input', function() {
        if (this.value < 0) {
            this.setCustomValidity('Unit price cannot be negative');
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
            alert('Please enter valid quantities (non-negative numbers)');
            return false;
        }
        
        if (unitPriceInput.value < 0) {
            e.preventDefault();
            alert('Please enter a valid unit price (non-negative number)');
            return false;
        }
    });
});
</script>
<?= $this->endSection() ?>
