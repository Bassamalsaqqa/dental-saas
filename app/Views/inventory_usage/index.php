<?= $this->extend('layouts/main_auth') ?>

<?= $this->section('content') ?>
<div class="min-h-screen bg-gradient-to-br from-blue-50 via-indigo-50 to-purple-50">
    <div class="container mx-auto px-4 py-8">
        <!-- Header -->
        <div class="mb-8">
            <div class="flex flex-col lg:flex-row lg:justify-between lg:items-center space-y-6 lg:space-y-0">
                <div class="flex items-center space-x-4">
                    <div class="relative group">
                        <div class="absolute inset-0 bg-gradient-to-br from-blue-500 to-purple-600 rounded-2xl blur-lg opacity-75 group-hover:opacity-100 transition-opacity duration-200"></div>
                        <div class="relative w-12 h-12 bg-gradient-to-br from-blue-500 to-purple-600 rounded-2xl flex items-center justify-center shadow-xl group-hover:scale-105 group-hover:rotate-2 transition-all duration-200">
                            <i class="fas fa-clipboard-list text-white text-lg"></i>
                        </div>
                    </div>
                    <div>
                        <h1 class="text-2xl font-black text-gray-900">Inventory Usage</h1>
                        <p class="text-gray-600 font-medium">Track and manage inventory consumption</p>
                    </div>
                </div>
                <div class="flex items-center space-x-3">
                    <a href="<?= base_url('inventory') ?>" class="inline-flex items-center px-4 py-2 bg-gray-100 text-gray-700 text-sm font-semibold rounded-lg hover:bg-gray-200 transition-all duration-200">
                        <i class="fas fa-box mr-2"></i>Inventory
                    </a>
                    <a href="<?= base_url('inventory-usage/create') ?>" class="inline-flex items-center px-4 py-2 bg-gradient-to-r from-green-600 to-emerald-600 text-white text-sm font-semibold rounded-lg shadow-lg hover:shadow-xl transition-all duration-200 hover:scale-105">
                        <i class="fas fa-plus mr-2"></i>Add Usage
                    </a>
                </div>
            </div>
        </div>

        <!-- Summary Cards -->
        <div class="mb-8">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div class="backdrop-blur-xl bg-white/80 rounded-2xl shadow-xl shadow-blue-500/10 border border-white/30 p-6">
                    <div class="flex items-center space-x-4">
                        <div class="w-12 h-12 bg-gradient-to-br from-blue-500 to-blue-600 rounded-xl flex items-center justify-center text-white shadow-lg">
                            <i class="fas fa-list text-lg"></i>
                        </div>
                        <div>
                            <p class="text-sm text-gray-600 font-medium">Total Usage Records</p>
                            <p class="text-2xl font-bold text-gray-900"><?= $stats['total_usage'] ?? 0 ?></p>
                        </div>
                    </div>
                </div>

                <div class="backdrop-blur-xl bg-white/80 rounded-2xl shadow-xl shadow-green-500/10 border border-white/30 p-6">
                    <div class="flex items-center space-x-4">
                        <div class="w-12 h-12 bg-gradient-to-br from-green-500 to-green-600 rounded-xl flex items-center justify-center text-white shadow-lg">
                            <i class="fas fa-dollar-sign text-lg"></i>
                        </div>
                        <div>
                            <p class="text-sm text-gray-600 font-medium">Total Cost</p>
                            <p class="text-2xl font-bold text-gray-900">$<?= number_format($stats['total_cost'] ?? 0, 2) ?></p>
                        </div>
                    </div>
                </div>

                <div class="backdrop-blur-xl bg-white/80 rounded-2xl shadow-xl shadow-purple-500/10 border border-white/30 p-6">
                    <div class="flex items-center space-x-4">
                        <div class="w-12 h-12 bg-gradient-to-br from-purple-500 to-purple-600 rounded-xl flex items-center justify-center text-white shadow-lg">
                            <i class="fas fa-cubes text-lg"></i>
                        </div>
                        <div>
                            <p class="text-sm text-gray-600 font-medium">Total Quantity Used</p>
                            <p class="text-2xl font-bold text-gray-900"><?= number_format($stats['total_quantity'] ?? 0, 2) ?></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Usage Records Table -->
        <div class="mb-8">
            <div class="backdrop-blur-xl bg-white/80 rounded-2xl shadow-xl shadow-gray-500/10 border border-white/30 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200 bg-gray-50/50">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center space-x-3">
                            <div class="w-2 h-2 bg-blue-500 rounded-full"></div>
                            <h3 class="text-lg font-semibold text-gray-900">Usage Records</h3>
                        </div>
                        <div class="text-sm text-gray-600">
                            <?= count($usage_records) ?> record<?= count($usage_records) !== 1 ? 's' : '' ?>
                        </div>
                    </div>
                </div>

                <?php if (!empty($usage_records)): ?>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50/50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Item</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Patient</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Treatment</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Quantity</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Cost</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <?php foreach ($usage_records as $record): ?>
                                    <tr class="hover:bg-gray-50 transition-colors duration-200">
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="flex items-center">
                                                <div class="w-10 h-10 bg-gradient-to-br from-blue-500 to-purple-600 rounded-lg flex items-center justify-center text-white font-bold text-sm shadow-lg">
                                                    <?= strtoupper(substr($record['item_name'], 0, 2)) ?>
                                                </div>
                                                <div class="ml-4">
                                                    <div class="text-sm font-medium text-gray-900"><?= $record['item_name'] ?></div>
                                                    <div class="text-sm text-gray-500"><?= ucfirst($record['category']) ?></div>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm font-medium text-gray-900"><?= $record['first_name'] . ' ' . $record['last_name'] ?></div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm text-gray-900"><?= $record['treatment_name'] ?: 'N/A' ?></div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm font-bold text-gray-900"><?= $record['quantity_used'] ?></div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm font-bold text-gray-900">$<?= number_format($record['total_cost'], 2) ?></div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm text-gray-900"><?= date('M j, Y', strtotime($record['usage_date'])) ?></div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                            <div class="flex space-x-2">
                                                <a href="<?= base_url('inventory-usage/' . $record['id']) ?>" class="text-blue-600 hover:text-blue-900" title="View Details">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                <a href="<?= base_url('inventory-usage/' . $record['id'] . '/edit') ?>" class="text-yellow-600 hover:text-yellow-900" title="Edit Usage">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <button onclick="deleteUsage(<?= $record['id'] ?>)" class="text-red-600 hover:text-red-900" title="Delete Usage">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="p-8 text-center">
                        <div class="w-16 h-16 bg-gradient-to-br from-gray-400 to-gray-500 rounded-2xl flex items-center justify-center text-white mx-auto mb-4 shadow-lg">
                            <i class="fas fa-clipboard-list text-2xl"></i>
                        </div>
                        <h3 class="text-lg font-semibold text-gray-900 mb-2">No Usage Records</h3>
                        <p class="text-gray-600 mb-4">No inventory usage has been recorded yet.</p>
                        <a href="<?= base_url('inventory-usage/create') ?>" class="inline-flex items-center px-4 py-2 bg-gradient-to-r from-green-600 to-emerald-600 text-white text-sm font-semibold rounded-lg shadow-lg hover:shadow-xl transition-all duration-200 hover:scale-105">
                            <i class="fas fa-plus mr-2"></i>Add First Usage Record
                        </a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<script>
function deleteUsage(id) {
    confirmDelete('<?= base_url('inventory-usage') ?>/' + id, 'Are you sure you want to delete this usage record? This will restore the inventory quantity.');
}
</script>

<?= $this->endSection() ?>
