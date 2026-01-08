<?= $this->extend('layouts/main_auth') ?>

<?= $this->section('content') ?>
<div class="min-h-screen bg-gradient-to-br from-slate-50 via-blue-50 to-indigo-100">
    <div class="px-6 py-6">
        <!-- Compact Page Header -->
        <div class="mb-6">
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-4">
                    <div class="relative group">
                        <div class="absolute inset-0 bg-gradient-to-br from-blue-500 to-purple-600 rounded-2xl blur-lg opacity-75 group-hover:opacity-100 transition-opacity duration-200"></div>
                        <div class="relative w-12 h-12 bg-gradient-to-br from-blue-500 to-purple-600 rounded-2xl flex items-center justify-center shadow-xl group-hover:scale-105 group-hover:rotate-2 transition-all duration-200">
                            <i class="fas fa-box text-white text-lg"></i>
                        </div>
                    </div>
                    <div>
                        <h1 class="text-2xl font-black text-gray-900">Item Details</h1>
                        <p class="text-gray-600 font-medium">Inventory item information</p>
                    </div>
                </div>
                <div class="flex items-center space-x-3">
                    <a href="<?= base_url('inventory') ?>" class="inline-flex items-center px-4 py-2 bg-gray-100 text-gray-700 text-sm font-semibold rounded-lg hover:bg-gray-200 transition-all duration-200">
                        <i class="fas fa-arrow-left mr-2"></i>Back
                    </a>
                    <a href="<?= base_url('inventory/' . $item['id'] . '/edit') ?>" class="inline-flex items-center px-4 py-2 bg-gradient-to-r from-blue-600 to-purple-600 text-white text-sm font-semibold rounded-lg shadow-lg hover:shadow-xl transition-all duration-200 hover:scale-105">
                        <i class="fas fa-edit mr-2"></i>Edit
                    </a>
                </div>
            </div>
        </div> 

        <!-- Compact Item Overview -->
        <div class="mb-6">
            <div class="backdrop-blur-xl bg-white/80 rounded-3xl shadow-2xl shadow-blue-500/10 border border-white/30 p-6">
                <div class="flex items-center justify-between">
                    <div class="flex items-center space-x-4">
                        <div class="relative">
                            <div class="absolute inset-0 bg-gradient-to-br from-blue-500 to-purple-600 rounded-2xl blur-lg opacity-75"></div>
                            <div class="relative w-16 h-16 bg-gradient-to-br from-blue-500 to-purple-600 rounded-2xl flex items-center justify-center text-white font-bold text-xl shadow-xl">
                                <?= strtoupper(substr($item['item_name'], 0, 2)) ?>
                            </div>
                        </div>
                        <div>
                            <h2 class="text-2xl font-black text-gray-900 mb-2"><?= $item['item_name'] ?></h2>
                            <div class="flex items-center space-x-3">
                                <span class="px-3 py-1 text-sm font-semibold rounded-full bg-blue-100 text-blue-800">
                                    <?= ucfirst($item['category']) ?>
                                </span>
                                <?php
                                $status = 'in_stock';
                                $statusClass = 'bg-green-100 text-green-800';
                                $statusText = 'In Stock';
                                
                                if ($item['quantity'] == 0) {
                                    $status = 'out_of_stock';
                                    $statusClass = 'bg-red-100 text-red-800';
                                    $statusText = 'Out of Stock';
                                } elseif (isset($item['min_quantity']) && $item['quantity'] <= $item['min_quantity']) {
                                    $status = 'low_stock';
                                    $statusClass = 'bg-yellow-100 text-yellow-800';
                                    $statusText = 'Low Stock';
                                }
                                ?>
                                <span class="px-3 py-1 text-sm font-semibold rounded-full <?= $statusClass ?>">
                                    <?= $statusText ?>
                                </span>
                                <span class="px-3 py-1 text-sm font-semibold rounded-full bg-gray-100 text-gray-800">
                                    <?= ucfirst($item['status']) ?>
                                </span>
                            </div>
                        </div>
                    </div>
                    <div class="text-right">
                        <div class="text-3xl font-black text-gray-900"><?= $item['quantity'] ?></div>
                        <div class="text-sm text-gray-600 font-medium">units in stock</div>
                        <div class="text-lg font-bold text-gray-900"><?= formatCurrency($item['quantity'] * $item['unit_price']) ?></div>
                        <div class="text-xs text-gray-500">total value</div>
                    </div>
                </div>
                
                <?php if (!empty($item['description'])): ?>
                    <div class="mt-4 pt-4 border-t border-gray-200">
                        <p class="text-gray-600 text-sm leading-relaxed"><?= $item['description'] ?></p>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Compact Details Grid -->
        <div class="mb-6">
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
                <!-- Stock Details -->
                <div class="backdrop-blur-xl bg-white/80 rounded-2xl shadow-xl shadow-green-500/10 border border-white/30 p-4">
                    <div class="flex items-center space-x-3 mb-4">
                        <div class="w-10 h-10 bg-gradient-to-br from-green-500 to-green-600 rounded-xl flex items-center justify-center text-white shadow-lg">
                            <i class="fas fa-boxes text-sm"></i>
                        </div>
                        <h3 class="text-lg font-black text-gray-900">Stock Details</h3>
                    </div>
                    
                    <div class="space-y-3">
                        <div class="flex justify-between items-center">
                            <span class="text-sm font-medium text-gray-600">Current</span>
                            <span class="text-lg font-bold text-gray-900"><?= $item['quantity'] ?></span>
                        </div>
                        
                        <div class="flex justify-between items-center">
                            <span class="text-sm font-medium text-gray-600">Minimum</span>
                            <span class="text-lg font-bold text-gray-900"><?= $item['min_quantity'] ?></span>
                        </div>
                        
                        <div class="flex justify-between items-center">
                            <span class="text-sm font-medium text-gray-600">Status</span>
                            <span class="px-2 py-1 text-xs font-semibold rounded-full <?= $statusClass ?>">
                                <?= $statusText ?>
                            </span>
                        </div>
                        
                        <?php if ($item['quantity'] <= $item['min_quantity']): ?>
                            <div class="mt-3 p-3 bg-yellow-50 border border-yellow-200 rounded-lg">
                                <div class="flex items-center">
                                    <i class="fas fa-exclamation-triangle text-yellow-600 mr-2 text-sm"></i>
                                    <span class="text-xs text-yellow-800 font-semibold">Low Stock Alert</span>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Financial Details -->
                <div class="backdrop-blur-xl bg-white/80 rounded-2xl shadow-xl shadow-purple-500/10 border border-white/30 p-4">
                    <div class="flex items-center space-x-3 mb-4">
                        <div class="w-10 h-10 bg-gradient-to-br from-purple-500 to-purple-600 rounded-xl flex items-center justify-center text-white shadow-lg">
                            <i class="fas fa-dollar-sign text-sm"></i>
                        </div>
                        <h3 class="text-lg font-black text-gray-900">Financial</h3>
                    </div>
                    
                    <div class="space-y-3">
                        <div class="flex justify-between items-center">
                            <span class="text-sm font-medium text-gray-600">Unit Price</span>
                            <span class="text-lg font-bold text-gray-900"><?= formatCurrency($item['unit_price']) ?></span>
                        </div>
                        
                        <div class="flex justify-between items-center">
                            <span class="text-sm font-medium text-gray-600">Total Value</span>
                            <span class="text-lg font-bold text-gray-900"><?= formatCurrency($item['quantity'] * $item['unit_price']) ?></span>
                        </div>
                        
                        <div class="flex justify-between items-center">
                            <span class="text-sm font-medium text-gray-600">Supplier</span>
                            <span class="text-sm text-gray-900"><?= $item['supplier'] ?: 'N/A' ?></span>
                        </div>
                    </div>
                </div>

                <!-- Location & Expiry -->
                <div class="backdrop-blur-xl bg-white/80 rounded-2xl shadow-xl shadow-indigo-500/10 border border-white/30 p-4">
                    <div class="flex items-center space-x-3 mb-4">
                        <div class="w-10 h-10 bg-gradient-to-br from-indigo-500 to-indigo-600 rounded-xl flex items-center justify-center text-white shadow-lg">
                            <i class="fas fa-map-marker-alt text-sm"></i>
                        </div>
                        <h3 class="text-lg font-black text-gray-900">Location</h3>
                    </div>
                    
                    <div class="space-y-3">
                        <div class="flex justify-between items-center">
                            <span class="text-sm font-medium text-gray-600">Storage</span>
                            <span class="text-sm text-gray-900"><?= $item['location'] ?: 'N/A' ?></span>
                        </div>
                        
                        <div class="flex justify-between items-center">
                            <span class="text-sm font-medium text-gray-600">Expiry</span>
                            <span class="text-sm text-gray-900">
                                <?php if ($item['expiry_date']): ?>
                                    <?php
                                    $expiryDate = strtotime($item['expiry_date']);
                                    $today = time();
                                    $daysUntilExpiry = ceil(($expiryDate - $today) / (60 * 60 * 24));
                                    
                                    if ($daysUntilExpiry < 0) {
                                        echo '<span class="text-red-600 font-semibold">Expired</span>';
                                    } elseif ($daysUntilExpiry <= 30) {
                                        echo '<span class="text-yellow-600 font-semibold">' . $daysUntilExpiry . ' days</span>';
                                    } else {
                                        echo date('M j, Y', $expiryDate);
                                    }
                                    ?>
                                <?php else: ?>
                                    No expiry
                                <?php endif; ?>
                            </span>
                        </div>
                        
                        <div class="flex justify-between items-center">
                            <span class="text-sm font-medium text-gray-600">Item ID</span>
                            <span class="text-sm text-gray-900 font-mono">#<?= $item['id'] ?></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- System Information -->
        <div class="mb-6">
            <div class="backdrop-blur-xl bg-white/80 rounded-2xl shadow-xl shadow-gray-500/10 border border-white/30 p-4">
                <div class="flex items-center space-x-3 mb-4">
                    <div class="w-10 h-10 bg-gradient-to-br from-gray-500 to-gray-600 rounded-xl flex items-center justify-center text-white shadow-lg">
                        <i class="fas fa-info-circle text-sm"></i>
                    </div>
                    <h3 class="text-lg font-black text-gray-900">System Information</h3>
                </div>
                
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div class="text-center">
                        <div class="text-sm text-gray-600 font-medium">Created</div>
                        <div class="text-sm text-gray-900 font-semibold">
                            <?= $item['created_at'] ? date('M j, Y', strtotime($item['created_at'])) : 'Unknown' ?>
                        </div>
                    </div>
                    <div class="text-center">
                        <div class="text-sm text-gray-600 font-medium">Last Updated</div>
                        <div class="text-sm text-gray-900 font-semibold">
                            <?= $item['updated_at'] ? date('M j, Y', strtotime($item['updated_at'])) : 'Unknown' ?>
                        </div>
                    </div>
                    <div class="text-center">
                        <div class="text-sm text-gray-600 font-medium">Item ID</div>
                        <div class="text-sm text-gray-900 font-mono font-semibold">#<?= $item['id'] ?></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Compact Action Buttons -->
        <div class="flex flex-col sm:flex-row space-y-3 sm:space-y-0 sm:space-x-4">
            <button onclick="adjustStock(<?= $item['id'] ?>)" class="flex-1 inline-flex items-center justify-center px-6 py-3 bg-gradient-to-r from-green-600 to-emerald-600 text-white text-sm font-semibold rounded-xl shadow-lg hover:shadow-xl transition-all duration-200 hover:scale-105">
                <i class="fas fa-plus-minus mr-2"></i>Adjust Stock
            </button>
            
            <a href="<?= base_url('inventory/' . $item['id'] . '/edit') ?>" class="flex-1 inline-flex items-center justify-center px-6 py-3 bg-gradient-to-r from-blue-600 to-purple-600 text-white text-sm font-semibold rounded-xl shadow-lg hover:shadow-xl transition-all duration-200 hover:scale-105">
                <i class="fas fa-edit mr-2"></i>Edit Item
            </a>
            
            <button onclick="deleteItem(<?= $item['id'] ?>)" class="flex-1 inline-flex items-center justify-center px-6 py-3 bg-gradient-to-r from-red-600 to-pink-600 text-white text-sm font-semibold rounded-xl shadow-lg hover:shadow-xl transition-all duration-200 hover:scale-105">
                <i class="fas fa-trash mr-2"></i>Delete Item
            </button>
        </div>
    </div>
</div>

<script>
function adjustStock(id) {
    const adjustment = prompt('Enter adjustment amount (positive to add, negative to subtract):');
    if (adjustment !== null && !isNaN(adjustment)) {
        const adjustmentValue = parseInt(adjustment);
        const type = adjustmentValue >= 0 ? 'add' : 'subtract';
        const reason = prompt('Enter reason for adjustment (optional):') || 'Manual adjustment';
        
        fetch(`<?= base_url('inventory') ?>/${id}/adjust`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                '<?= csrf_token() ?>': '<?= csrf_hash() ?>'
            },
            body: JSON.stringify({ 
                adjustment: Math.abs(adjustmentValue),
                type: type,
                reason: reason
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showToast('success', 'Success!', 'Stock adjusted successfully!');
                setTimeout(() => location.reload(), 1500);
            } else {
                showToast('error', 'Error!', data.message || 'Failed to adjust stock.');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showToast('error', 'Error!', 'An error occurred while adjusting the stock.');
        });
    }
}

function deleteItem(id) {
    confirmDelete('<?= base_url('inventory') ?>/' + id, 'Are you sure you want to delete this inventory item? This action cannot be undone.');
}
</script>
<?= $this->endSection() ?>
