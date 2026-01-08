<?= $this->extend('layouts/main_auth') ?>

<?= $this->section('content') ?>
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <div class="space-y-8">
        <!-- Page Header -->
        <div class="flex flex-col md:flex-row md:justify-between md:items-center space-y-4 md:space-y-0">
            <div>
                <h1 class="text-3xl font-bold text-gray-800">Financial Transaction Details</h1>
                <p class="text-gray-600 mt-1">Transaction ID: <?= $finance['transaction_id'] ?></p>
            </div>
            <div class="flex space-x-3">
                <a href="<?= base_url('finance') ?>" class="inline-flex items-center px-4 py-2 bg-gray-100 text-gray-700 text-sm font-medium rounded-md hover:bg-gray-200 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition-colors">
                    <i class="fas fa-arrow-left mr-2"></i>Back to Finance
                </a>
                <a href="<?= base_url('finance/' . $finance['id'] . '/edit') ?>" class="inline-flex items-center px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition-colors">
                    <i class="fas fa-edit mr-2"></i>Edit Transaction
                </a>
            </div>
        </div>

        <!-- Transaction Overview -->
        <div class="bg-white rounded-lg border border-gray-200 shadow-sm group hover:shadow-lg transition-all duration-300">
            <div class="px-8 py-6 border-b border-gray-200">
                <h3 class="text-lg font-semibold text-gray-800">Transaction Overview</h3>
            </div>
            <div class="px-8 py-6">
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                    <div class="text-center">
                        <div class="text-3xl font-bold text-green-600 mb-2">
                            <?= number_format($finance['amount'], 2) ?> <?= $finance['currency'] ?>
                        </div>
                        <p class="text-sm text-gray-600">Amount</p>
                    </div>
                    <div class="text-center">
                        <div class="text-2xl font-bold text-blue-600 mb-2">
                            <?= ucfirst(str_replace('_', ' ', $finance['transaction_type'])) ?>
                        </div>
                        <p class="text-sm text-gray-600">Transaction Type</p>
                    </div>
                    <div class="text-center">
                        <div class="text-2xl font-bold text-purple-600 mb-2">
                            <?= ucfirst(str_replace('_', ' ', $finance['payment_method'])) ?>
                        </div>
                        <p class="text-sm text-gray-600">Payment Method</p>
                    </div>
                    <div class="text-center">
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium
                            <?= $finance['payment_status'] == 'paid' ? 'bg-green-100 text-green-800' : 
                                ($finance['payment_status'] == 'pending' ? 'bg-yellow-100 text-yellow-800' : 
                                ($finance['payment_status'] == 'overdue' ? 'bg-red-100 text-red-800' : 'bg-gray-100 text-gray-800')) ?>">
                            <?= ucfirst($finance['payment_status']) ?>
                        </span>
                        <p class="text-sm text-gray-600 mt-2">Payment Status</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Patient Information -->
        <?php if ($patient): ?>
        <div class="bg-white rounded-lg border border-gray-200 shadow-sm group hover:shadow-lg transition-all duration-300">
            <div class="px-8 py-6 border-b border-gray-200">
                <div class="flex items-center justify-between">
                    <div class="flex items-center space-x-3">
                        <div class="w-12 h-12 bg-gradient-to-br from-blue-500 to-blue-600 rounded-full flex items-center justify-center">
                            <i class="fas fa-user text-white text-lg"></i>
                        </div>
                        <div>
                            <h2 class="text-xl font-semibold text-gray-800">Patient Information</h2>
                            <p class="text-sm text-gray-500">Patient details for this transaction</p>
                        </div>
                    </div>
                    <a href="<?= base_url('patient/' . $patient['id']) ?>" class="inline-flex items-center px-4 py-2 bg-gray-100 text-gray-700 text-sm font-medium rounded-md hover:bg-gray-200 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition-colors">
                        <i class="fas fa-external-link-alt mr-2"></i>View Patient
                    </a>
                </div>
            </div>
            <div class="px-8 py-6">
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <div class="bg-gray-50 p-6 rounded-lg">
                        <label class="block text-sm font-medium text-gray-700 mb-2 text-gray-600">Patient Name</label>
                        <p class="text-gray-900 font-semibold text-lg"><?= $patient['first_name'] . ' ' . $patient['last_name'] ?></p>
                    </div>
                    <div class="bg-gray-50 p-6 rounded-lg">
                        <label class="block text-sm font-medium text-gray-700 mb-2 text-gray-600">Patient ID</label>
                        <p class="text-gray-900 font-semibold text-lg"><?= $patient['patient_id'] ?></p>
                    </div>
                    <div class="bg-gray-50 p-6 rounded-lg">
                        <label class="block text-sm font-medium text-gray-700 mb-2 text-gray-600">Phone</label>
                        <p class="text-gray-900 font-semibold text-lg"><?= $patient['phone'] ?></p>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <!-- Examination Information -->
        <?php if ($examination): ?>
        <div class="bg-white rounded-lg border border-gray-200 shadow-sm group hover:shadow-lg transition-all duration-300">
            <div class="px-8 py-6 border-b border-gray-200">
                <div class="flex items-center justify-between">
                    <div class="flex items-center space-x-3">
                        <div class="w-12 h-12 bg-gradient-to-br from-green-500 to-green-600 rounded-full flex items-center justify-center">
                            <i class="fas fa-stethoscope text-white text-lg"></i>
                        </div>
                        <div>
                            <h2 class="text-xl font-semibold text-gray-800">Related Examination</h2>
                            <p class="text-sm text-gray-500">Examination details for this transaction</p>
                        </div>
                    </div>
                    <a href="<?= base_url('examination/' . $examination['id']) ?>" class="inline-flex items-center px-4 py-2 bg-gray-100 text-gray-700 text-sm font-medium rounded-md hover:bg-gray-200 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition-colors">
                        <i class="fas fa-external-link-alt mr-2"></i>View Examination
                    </a>
                </div>
            </div>
            <div class="px-8 py-6">
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <div class="bg-gray-50 p-6 rounded-lg">
                        <label class="block text-sm font-medium text-gray-700 mb-2 text-gray-600">Examination ID</label>
                        <p class="text-gray-900 font-semibold text-lg"><?= $examination['examination_id'] ?></p>
                    </div>
                    <div class="bg-gray-50 p-6 rounded-lg">
                        <label class="block text-sm font-medium text-gray-700 mb-2 text-gray-600">Examination Date</label>
                        <p class="text-gray-900 font-semibold text-lg"><?= date('M j, Y', strtotime($examination['examination_date'])) ?></p>
                    </div>
                    <div class="bg-gray-50 p-6 rounded-lg">
                        <label class="block text-sm font-medium text-gray-700 mb-2 text-gray-600">Status</label>
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                            <?= $examination['status'] == 'completed' ? 'bg-green-100 text-green-800' : 
                                ($examination['status'] == 'in_progress' ? 'bg-blue-100 text-blue-800' : 'bg-yellow-100 text-yellow-800') ?>">
                            <?= ucfirst($examination['status']) ?>
                        </span>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <!-- Transaction Details -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
            <!-- Service Information -->
            <div class="bg-white rounded-lg border border-gray-200 shadow-sm group hover:shadow-lg transition-all duration-300">
                <div class="px-8 py-6 border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-800">Service Information</h3>
                </div>
                <div class="px-8 py-6">
                    <div class="space-y-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-600 mb-2">Service Type</label>
                            <p class="text-sm text-gray-900"><?= ucfirst(str_replace('_', ' ', $finance['service_type'])) ?></p>
                        </div>
                        <?php if (!empty($finance['service_details'])): ?>
                        <div>
                            <label class="block text-sm font-medium text-gray-600 mb-2">Service Details</label>
                            <p class="text-sm text-gray-900"><?= nl2br(htmlspecialchars($finance['service_details'])) ?></p>
                        </div>
                        <?php endif; ?>
                        <?php if (!empty($finance['description'])): ?>
                        <div>
                            <label class="block text-sm font-medium text-gray-600 mb-2">Description</label>
                            <p class="text-sm text-gray-900"><?= nl2br(htmlspecialchars($finance['description'])) ?></p>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Financial Details -->
            <div class="bg-white rounded-lg border border-gray-200 shadow-sm group hover:shadow-lg transition-all duration-300">
                <div class="px-8 py-6 border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-800">Financial Details</h3>
                </div>
                <div class="px-8 py-6">
                    <div class="space-y-6">
                        <div class="flex justify-between">
                            <span class="text-sm font-medium text-gray-600">Base Amount:</span>
                            <span class="text-sm text-gray-900"><?= number_format($finance['amount'], 2) ?> <?= $finance['currency'] ?></span>
                        </div>
                        <?php if ($finance['discount_amount'] > 0): ?>
                        <div class="flex justify-between text-red-600">
                            <span class="text-sm font-medium">Discount:</span>
                            <span class="text-sm">-<?= number_format($finance['discount_amount'], 2) ?> <?= $finance['currency'] ?></span>
                        </div>
                        <?php endif; ?>
                        <?php if ($finance['tax_amount'] > 0): ?>
                        <div class="flex justify-between">
                            <span class="text-sm font-medium text-gray-600">Tax:</span>
                            <span class="text-sm text-gray-900"><?= number_format($finance['tax_amount'], 2) ?> <?= $finance['currency'] ?></span>
                        </div>
                        <?php endif; ?>
                        <div class="border-t pt-4">
                            <div class="flex justify-between font-semibold">
                                <span class="text-sm text-gray-900">Total Amount:</span>
                                <span class="text-sm text-gray-900">
                                    <?= number_format($finance['amount'] - $finance['discount_amount'] + $finance['tax_amount'], 2) ?> <?= $finance['currency'] ?>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Payment Information -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
            <!-- Payment Details -->
            <div class="bg-white rounded-lg border border-gray-200 shadow-sm group hover:shadow-lg transition-all duration-300">
                <div class="px-8 py-6 border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-800">Payment Details</h3>
                </div>
                <div class="px-8 py-6">
                    <div class="space-y-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-600 mb-2">Payment Method</label>
                            <p class="text-sm text-gray-900"><?= ucfirst(str_replace('_', ' ', $finance['payment_method'])) ?></p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-600 mb-2">Payment Status</label>
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                <?= $finance['payment_status'] == 'paid' ? 'bg-green-100 text-green-800' : 
                                    ($finance['payment_status'] == 'pending' ? 'bg-yellow-100 text-yellow-800' : 
                                    ($finance['payment_status'] == 'overdue' ? 'bg-red-100 text-red-800' : 'bg-gray-100 text-gray-800')) ?>">
                                <?= ucfirst($finance['payment_status']) ?>
                            </span>
                        </div>
                        <?php if (!empty($finance['paid_date'])): ?>
                        <div>
                            <label class="block text-sm font-medium text-gray-600 mb-2">Paid Date</label>
                            <p class="text-sm text-gray-900"><?= date('M j, Y', strtotime($finance['paid_date'])) ?></p>
                        </div>
                        <?php endif; ?>
                        <?php if (!empty($finance['due_date'])): ?>
                        <div>
                            <label class="block text-sm font-medium text-gray-600 mb-2">Due Date</label>
                            <p class="text-sm text-gray-900"><?= date('M j, Y', strtotime($finance['due_date'])) ?></p>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Transaction Metadata -->
            <div class="bg-white rounded-lg border border-gray-200 shadow-sm group hover:shadow-lg transition-all duration-300">
                <div class="px-8 py-6 border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-800">Transaction Metadata</h3>
                </div>
                <div class="px-8 py-6">
                    <div class="space-y-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-600 mb-2">Transaction ID</label>
                            <p class="text-sm text-gray-900 font-mono"><?= $finance['transaction_id'] ?></p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-600 mb-2">Created Date</label>
                            <p class="text-sm text-gray-900"><?= date('M j, Y g:i A', strtotime($finance['created_at'])) ?></p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-600 mb-2">Last Updated</label>
                            <p class="text-sm text-gray-900"><?= date('M j, Y g:i A', strtotime($finance['updated_at'])) ?></p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-600 mb-2">Created By</label>
                            <p class="text-sm text-gray-900">User ID: <?= $finance['created_by'] ?></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Notes -->
        <?php if (!empty($finance['notes'])): ?>
        <div class="bg-white rounded-lg border border-gray-200 shadow-sm group hover:shadow-lg transition-all duration-300">
            <div class="px-8 py-6 border-b border-gray-200">
                <h3 class="text-lg font-semibold text-gray-800">Notes</h3>
            </div>
            <div class="px-8 py-6">
                <p class="text-sm text-gray-900"><?= nl2br(htmlspecialchars($finance['notes'])) ?></p>
            </div>
        </div>
        <?php endif; ?>

        <!-- Action Buttons -->
        <div class="flex flex-wrap justify-end gap-4 pt-6">
            <a href="<?= base_url('finance') ?>" class="inline-flex items-center px-6 py-3 bg-gray-100 text-gray-700 text-sm font-medium rounded-lg hover:bg-gray-200 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition-colors">
                <i class="fas fa-arrow-left mr-2"></i>Back to List
            </a>
            <a href="<?= base_url('finance/' . $finance['id'] . '/edit') ?>" class="inline-flex items-center px-6 py-3 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition-colors">
                <i class="fas fa-edit mr-2"></i>Edit Transaction
            </a>
            <?php if ($finance['payment_status'] != 'paid'): ?>
                <button onclick="markAsPaid(<?= $finance['id'] ?>)" class="inline-flex items-center px-6 py-3 bg-green-600 text-white text-sm font-medium rounded-lg hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 transition-colors">
                    <i class="fas fa-check mr-2"></i>Mark as Paid
                </button>
            <?php endif; ?>
            <a href="<?= base_url('finance/' . $finance['id'] . '/invoice') ?>" class="inline-flex items-center px-6 py-3 bg-purple-600 text-white text-sm font-medium rounded-lg hover:bg-purple-700 focus:outline-none focus:ring-2 focus:ring-purple-500 focus:ring-offset-2 transition-colors">
                <i class="fas fa-file-invoice mr-2"></i>Generate Invoice
            </a>
        </div>
    </div>
</div>

<script>
function markAsPaid(transactionId) {
    if (confirm('Are you sure you want to mark this transaction as paid?')) {
        fetch(`<?= base_url('finance') ?>/${transactionId}/mark-paid`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert('Error: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred while updating the transaction.');
        });
    }
}
</script>
<?= $this->endSection() ?>
