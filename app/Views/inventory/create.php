<?= $this->extend('layouts/main_auth') ?>

<?= $this->section('content') ?>
<!-- Enhanced Inventory Creation with Advanced Design -->
<div class="min-h-screen bg-gradient-to-br from-slate-50 via-emerald-50 to-teal-100 relative overflow-hidden">
    <!-- Animated Background Elements -->
    <div class="absolute inset-0 overflow-hidden pointer-events-none">
        <div class="absolute -top-40 -right-40 w-80 h-80 bg-gradient-to-br from-emerald-400/20 to-teal-600/20 rounded-full blur-3xl animate-pulse"></div>
        <div class="absolute -bottom-40 -left-40 w-80 h-80 bg-gradient-to-br from-blue-400/20 to-cyan-600/20 rounded-full blur-3xl animate-pulse delay-1000"></div>
        <div class="absolute top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2 w-96 h-96 bg-gradient-to-br from-amber-400/10 to-orange-600/10 rounded-full blur-3xl animate-pulse delay-2000"></div>
    </div>

    <div class="container mx-auto relative z-10 px-4 py-6">

        <!-- Enhanced Inventory Form with Glassmorphism -->
        <div class="group relative">
            <div class="absolute inset-0 bg-gradient-to-br from-emerald-500/10 to-teal-600/10 rounded-3xl blur-xl group-hover:blur-2xl transition-all duration-500 opacity-0 group-hover:opacity-100"></div>
            <div class="relative backdrop-blur-xl bg-white/80 border border-white/30 rounded-2xl shadow-2xl shadow-emerald-500/10 group-hover:shadow-emerald-500/20 transition-all duration-500 overflow-hidden">
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
                                <h3 class="text-2xl font-black text-gray-900 group-hover:text-emerald-900 transition-colors duration-300">Add New Inventory Item</h3>
                                <p class="text-gray-600 font-medium">Fill in the details to add a new item to your inventory</p>
                            </div>
                        </div>
                        
                        <div class="flex items-center space-x-3">
                            <a href="<?= base_url('inventory') ?>" class="group/btn relative inline-flex items-center px-6 py-3 border-2 border-gray-300 text-gray-700 text-sm font-bold rounded-xl hover:bg-gradient-to-r hover:from-gray-50 hover:to-slate-50 hover:border-gray-400 transition-all duration-300 hover:scale-105">
                                <div class="absolute inset-0 bg-gradient-to-r from-gray-500/10 to-slate-500/10 rounded-xl blur opacity-0 group-hover/btn:opacity-100 transition-opacity duration-300"></div>
                                <i class="fas fa-arrow-left mr-2 relative z-10"></i>
                                <span class="relative z-10">Back to Inventory</span>
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Form Content -->
                <div class="p-8">

                    <form action="<?= base_url('inventory/store') ?>" method="POST" class="space-y-8">
                        <!-- Item Name and Category -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                            <div class="group/field relative">
                                <label class="block text-sm font-bold text-gray-700 mb-3 flex items-center space-x-2">
                                    <i class="fas fa-tag text-emerald-600"></i>
                                    <span>Item Name *</span>
                                </label>
                                <div class="relative">
                                    <input type="text" name="item_name" value="<?= old('item_name') ?>" class="w-full px-4 py-3 bg-white/80 border-2 border-gray-200 rounded-xl shadow-lg focus:outline-none focus:ring-4 focus:ring-emerald-500/20 focus:border-emerald-500 transition-all duration-300 hover:shadow-xl" placeholder="Enter item name" required>
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
                                    <span>Category *</span>
                                </label>
                                <div class="relative">
                                    <select name="category" class="w-full px-4 py-3 bg-white/80 border-2 border-gray-200 rounded-xl shadow-lg focus:outline-none focus:ring-4 focus:ring-blue-500/20 focus:border-blue-500 transition-all duration-300 hover:shadow-xl" required>
                                        <option value="">Select Category</option>
                                        <option value="medication" <?= (old('category') == 'medication') ? 'selected' : '' ?>>Medication</option>
                                        <option value="equipment" <?= (old('category') == 'equipment') ? 'selected' : '' ?>>Equipment</option>
                                        <option value="supplies" <?= (old('category') == 'supplies') ? 'selected' : '' ?>>Supplies</option>
                                        <option value="consumables" <?= (old('category') == 'consumables') ? 'selected' : '' ?>>Consumables</option>
                                        <option value="other" <?= (old('category') == 'other') ? 'selected' : '' ?>>Other</option>
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
                                <span>Description</span>
                            </label>
                            <div class="relative">
                                <textarea name="description" rows="4" class="w-full px-4 py-3 bg-white/80 border-2 border-gray-200 rounded-xl shadow-lg focus:outline-none focus:ring-4 focus:ring-purple-500/20 focus:border-purple-500 transition-all duration-300 hover:shadow-xl resize-none" placeholder="Item description..."><?= old('description') ?></textarea>
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
                                    <span>Quantity *</span>
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
                                    <span>Unit</span>
                                </label>
                                <div class="relative">
                                    <select name="unit" class="w-full px-4 py-3 bg-white/80 border-2 border-gray-200 rounded-xl shadow-lg focus:outline-none focus:ring-4 focus:ring-cyan-500/20 focus:border-cyan-500 transition-all duration-300 hover:shadow-xl">
                                        <option value="pieces" <?= (old('unit') == 'pieces') ? 'selected' : '' ?>>Pieces</option>
                                        <option value="boxes" <?= (old('unit') == 'boxes') ? 'selected' : '' ?>>Boxes</option>
                                        <option value="bottles" <?= (old('unit') == 'bottles') ? 'selected' : '' ?>>Bottles</option>
                                        <option value="tubes" <?= (old('unit') == 'tubes') ? 'selected' : '' ?>>Tubes</option>
                                        <option value="packs" <?= (old('unit') == 'packs') ? 'selected' : '' ?>>Packs</option>
                                        <option value="sets" <?= (old('unit') == 'sets') ? 'selected' : '' ?>>Sets</option>
                                    </select>
                                    <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
                                        <i class="fas fa-chevron-down text-gray-400"></i>
                                    </div>
                                </div>
                            </div>

                            <div class="group/field relative">
                                <label class="block text-sm font-bold text-gray-700 mb-3 flex items-center space-x-2">
                                    <i class="fas fa-exclamation-triangle text-rose-600"></i>
                                    <span>Min Quantity *</span>
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
                                    <span>Unit Price *</span>
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
                                    <span>Supplier</span>
                                </label>
                                <div class="relative">
                                    <input type="text" name="supplier" value="<?= old('supplier') ?>" class="w-full px-4 py-3 bg-white/80 border-2 border-gray-200 rounded-xl shadow-lg focus:outline-none focus:ring-4 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all duration-300 hover:shadow-xl" placeholder="Supplier name">
                                    <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
                                        <i class="fas fa-truck text-gray-400"></i>
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

                        <!-- Purchase Date and Expiry Date -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                            <div class="group/field relative">
                                <label class="block text-sm font-bold text-gray-700 mb-3 flex items-center space-x-2">
                                    <i class="fas fa-calendar text-orange-600"></i>
                                    <span>Purchase Date</span>
                                </label>
                                <div class="relative">
                                    <input type="date" name="purchase_date" value="<?= old('purchase_date', date('Y-m-d')) ?>" class="w-full px-4 py-3 bg-white/80 border-2 border-gray-200 rounded-xl shadow-lg focus:outline-none focus:ring-4 focus:ring-orange-500/20 focus:border-orange-500 transition-all duration-300 hover:shadow-xl">
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
                                    <span>Expiry Date</span>
                                </label>
                                <div class="relative">
                                    <input type="date" name="expiry_date" value="<?= old('expiry_date') ?>" class="w-full px-4 py-3 bg-white/80 border-2 border-gray-200 rounded-xl shadow-lg focus:outline-none focus:ring-4 focus:ring-red-500/20 focus:border-red-500 transition-all duration-300 hover:shadow-xl">
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
                                <span>Location</span>
                            </label>
                            <div class="relative">
                                <input type="text" name="location" value="<?= old('location') ?>" class="w-full px-4 py-3 bg-white/80 border-2 border-gray-200 rounded-xl shadow-lg focus:outline-none focus:ring-4 focus:ring-teal-500/20 focus:border-teal-500 transition-all duration-300 hover:shadow-xl" placeholder="Storage location (e.g., Cabinet A, Shelf 2)">
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
                                <span>Additional Notes</span>
                            </label>
                            <div class="relative">
                                <textarea name="notes" rows="4" class="w-full px-4 py-3 bg-white/80 border-2 border-gray-200 rounded-xl shadow-lg focus:outline-none focus:ring-4 focus:ring-violet-500/20 focus:border-violet-500 transition-all duration-300 hover:shadow-xl resize-none" placeholder="Additional notes..."><?= old('notes') ?></textarea>
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
                                <span class="relative z-10">Cancel</span>
                            </a>
                            <button type="submit" class="group/btn relative inline-flex items-center justify-center px-8 py-3 bg-gradient-to-r from-emerald-500 to-teal-600 text-white text-sm font-bold rounded-xl hover:from-emerald-600 hover:to-teal-700 transition-all duration-300 hover:scale-105 hover:shadow-2xl hover:shadow-emerald-500/25">
                                <div class="absolute inset-0 bg-gradient-to-r from-emerald-500/20 to-teal-600/20 rounded-xl blur opacity-0 group-hover/btn:opacity-100 transition-opacity duration-300"></div>
                                <i class="fas fa-boxes mr-2 relative z-10"></i>
                                <span class="relative z-10">Add Item</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>
