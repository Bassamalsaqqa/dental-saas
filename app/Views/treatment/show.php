<?= $this->extend('layouts/main_auth') ?>

<?= $this->section('content') ?>
<div class="min-h-screen bg-gradient-to-br from-slate-50 via-blue-50 to-indigo-100">
    <div class="px-4 sm:px-6 lg:px-8 py-6 sm:py-8">
        <!-- Page Header -->
        <div class="mb-8">
            <div class="bg-white/70 backdrop-blur-lg rounded-2xl border border-white/20 shadow-xl p-6">
                <div class="flex flex-col lg:flex-row lg:justify-between lg:items-center space-y-4 lg:space-y-0">
                    <?php $doctorName = trim(($treatment['doctor_first_name'] ?? '') . ' ' . ($treatment['doctor_last_name'] ?? '')); ?>
                    <div class="space-y-3">
                        <div class="flex items-center space-x-3">
                            <div class="p-3 bg-gradient-to-br from-blue-600 to-purple-600 rounded-xl shadow-lg">
                                <i class="fas fa-tooth text-white text-xl"></i>
                            </div>
                            <div>
                                <h1 class="text-3xl font-bold bg-gradient-to-r from-gray-800 to-gray-600 bg-clip-text text-transparent">
                                    <?= lang('Treatment.treatment_details') ?>
                                </h1>
                                <p class="text-gray-600"><?= lang('Treatment.treatment_id') ?>: <?= $treatment['treatment_id'] ?? lang('Treatment.na') ?></p>
                            </div>
                        </div>
                    </div>
                    <div class="flex flex-col sm:flex-row space-y-3 sm:space-y-0 sm:space-x-3">
                        <?php if (!empty($treatment['patient_id'])): ?>
                            <a href="<?= base_url('patient/' . $treatment['patient_id']) ?>" class="inline-flex items-center px-4 py-2 bg-gradient-to-r from-indigo-500 to-blue-600 text-white text-sm font-medium rounded-xl hover:from-indigo-600 hover:to-blue-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition-all duration-300 shadow-lg hover:shadow-xl">
                                <i class="fas fa-user mr-2"></i><?= lang('Treatment.back_to_patient') ?>
                            </a>
                        <?php endif; ?>
                        <a href="<?= !empty($return_to) ? base_url($return_to) : base_url('treatment') ?>" class="inline-flex items-center px-4 py-2 bg-gradient-to-r from-gray-600 to-gray-700 text-white text-sm font-medium rounded-xl hover:from-gray-700 hover:to-gray-800 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition-all duration-300 shadow-lg hover:shadow-xl">
                            <i class="fas fa-arrow-left mr-2"></i><?= lang('Treatment.back_to_treatments') ?>
                        </a>
                        <a href="<?= base_url('treatment/' . $treatment['id'] . '/edit' . (!empty($return_to) ? '?return_to=' . urlencode($return_to) : '')) ?>" class="inline-flex items-center px-4 py-2 bg-gradient-to-r from-blue-600 to-blue-700 text-white text-sm font-medium rounded-xl hover:from-blue-700 hover:to-blue-800 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition-all duration-300 shadow-lg hover:shadow-xl">
                            <i class="fas fa-edit mr-2"></i><?= lang('Treatment.edit_treatment') ?>
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Treatment Details -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Main Treatment Information -->
            <div class="lg:col-span-2 space-y-6">
                <!-- Treatment Overview -->
                <div class="bg-white/80 backdrop-blur-lg rounded-2xl border border-white/20 shadow-xl p-6">
                    <h2 class="text-xl font-bold text-gray-800 mb-4 flex items-center">
                        <i class="fas fa-info-circle text-blue-600 mr-2"></i>
                        <?= lang('Treatment.treatment_overview') ?>
                    </h2>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-600 mb-1"><?= lang('Treatment.treatment_name') ?></label>
                            <p class="text-gray-800 font-semibold"><?= $treatment['treatment_name'] ?? lang('Treatment.na') ?></p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-600 mb-1"><?= lang('Treatment.treatment_type') ?></label>
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-blue-100 text-blue-800">
                                <?= ucfirst(str_replace('_', ' ', $treatment['treatment_type'] ?? lang('Treatment.na'))) ?>
                            </span>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-600 mb-1"><?= lang('Treatment.tooth_number') ?></label>
                            <p class="text-gray-800"><?= $treatment['tooth_number'] ?? lang('Treatment.na') ?></p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-600 mb-1"><?= lang('Treatment.status') ?></label>
                            <?php
                            $statusColors = [
                                'active' => 'bg-green-100 text-green-800',
                                'completed' => 'bg-blue-100 text-blue-800',
                                'cancelled' => 'bg-red-100 text-red-800',
                                'on_hold' => 'bg-yellow-100 text-yellow-800'
                            ];
                            $statusColor = $statusColors[$treatment['status']] ?? 'bg-gray-100 text-gray-800';
                            $statusRaw = $treatment['status'] ?? 'active';
                            $statusLabel = lang('Treatment.status_values.' . $statusRaw);
                            if ($statusLabel === 'Treatment.status_values.' . $statusRaw) {
                                $statusLabel = ucfirst(str_replace('_', ' ', $statusRaw));
                            }
                            ?>
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium <?= $statusColor ?>">
                                <?= esc($statusLabel) ?>
                            </span>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-600 mb-1"><?= lang('Treatment.cost') ?></label>
                            <p class="text-gray-800 font-semibold text-lg"><?= formatCurrency($treatment['cost'] ?? 0) ?></p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-600 mb-1"><?= lang('Treatment.estimated_duration') ?></label>
                            <p class="text-gray-800"><?= $treatment['estimated_duration'] ?? lang('Treatment.na') ?> <?= lang('Treatment.days') ?></p>
                        </div>
                    </div>
                </div>

                <!-- Treatment Description -->
                <div class="bg-white/80 backdrop-blur-lg rounded-2xl border border-white/20 shadow-xl p-6">
                    <h2 class="text-xl font-bold text-gray-800 mb-4 flex items-center">
                        <i class="fas fa-file-alt text-green-600 mr-2"></i>
                        <?= lang('Treatment.treatment_description') ?>
                    </h2>
                    <div class="prose max-w-none">
                        <p class="text-gray-700 leading-relaxed"><?= nl2br(htmlspecialchars($treatment['treatment_description'] ?? lang('Treatment.no_description_provided'))) ?></p>
                    </div>
                </div>

                <!-- Treatment Notes -->
                <?php if (!empty($treatment['treatment_notes'])): ?>
                <div class="bg-white/80 backdrop-blur-lg rounded-2xl border border-white/20 shadow-xl p-6">
                    <h2 class="text-xl font-bold text-gray-800 mb-4 flex items-center">
                        <i class="fas fa-sticky-note text-yellow-600 mr-2"></i>
                        <?= lang('Treatment.treatment_notes') ?>
                    </h2>
                    <div class="prose max-w-none">
                        <p class="text-gray-700 leading-relaxed"><?= nl2br(htmlspecialchars($treatment['treatment_notes'])) ?></p>
                    </div>
                </div>
                <?php endif; ?>

                <?php if (!empty($usage_records)): ?>
                <div class="bg-white/80 backdrop-blur-lg rounded-2xl border border-white/20 shadow-xl p-6">
                    <h2 class="text-xl font-bold text-gray-800 mb-4 flex items-center">
                        <i class="fas fa-boxes text-emerald-600 mr-2"></i>
                        <?= lang('Treatment.inventory_usage') ?>
                    </h2>
                    <div class="overflow-x-auto">
                        <table class="min-w-full text-sm">
                            <thead>
                                <tr class="border-b border-gray-200">
                                    <th class="text-left py-2 px-3 text-xs font-semibold text-gray-500 uppercase"><?= lang('Treatment.date') ?></th>
                                    <th class="text-left py-2 px-3 text-xs font-semibold text-gray-500 uppercase"><?= lang('Treatment.items_used') ?></th>
                                    <th class="text-left py-2 px-3 text-xs font-semibold text-gray-500 uppercase"><?= lang('Treatment.cost') ?></th>
                                    <th class="text-left py-2 px-3 text-xs font-semibold text-gray-500 uppercase"><?= lang('Treatment.notes') ?></th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                <?php foreach ($usage_records as $usage): ?>
                                    <?php
                                        $items = json_decode($usage['items_used'] ?? '[]', true);
                                        $itemList = [];
                                        if (is_array($items)) {
                                            foreach ($items as $item) {
                                                $label = ($item['item_name'] ?? lang('Treatment.item')) . ' x' . ($item['quantity_used'] ?? 0);
                                                $itemList[] = $label;
                                            }
                                        }
                                    ?>
                                    <tr>
                                        <td class="py-2 px-3 text-gray-700">
                                            <?= !empty($usage['usage_date']) ? formatDate($usage['usage_date']) : lang('Treatment.na') ?>
                                        </td>
                                        <td class="py-2 px-3 text-gray-700">
                                            <?= !empty($itemList) ? esc(implode(', ', $itemList)) : lang('Treatment.em_dash') ?>
                                        </td>
                                        <td class="py-2 px-3 font-semibold text-emerald-700">
                                            <?= formatCurrency((float) ($usage['total_cost'] ?? 0)) ?>
                                        </td>
                                        <td class="py-2 px-3 text-gray-600">
                                            <?= !empty($usage['notes']) ? esc($usage['notes']) : lang('Treatment.em_dash') ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
                <?php endif; ?>
            </div>

            <!-- Sidebar Information -->
            <div class="space-y-6">
                <!-- Treatment Financials -->
                <div class="bg-white/80 backdrop-blur-lg rounded-2xl border border-white/20 shadow-xl p-6">
                    <h2 class="text-xl font-bold text-gray-800 mb-4 flex items-center">
                        <i class="fas fa-coins text-emerald-600 mr-2"></i>
                        <?= lang('Treatment.treatment_financials') ?>
                    </h2>
                    <?php
                        $treatmentCost = (float) ($treatment['cost'] ?? 0);
                        $usageCost = (float) ($usage_total_cost ?? 0);
                        $grossMargin = $treatmentCost - $usageCost;
                    ?>
                    <div class="space-y-3">
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-gray-600"><?= lang('Treatment.charged') ?></span>
                            <span class="text-sm font-semibold text-gray-800"><?= formatCurrency($treatmentCost) ?></span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-gray-600"><?= lang('Treatment.inventory_cost') ?></span>
                            <span class="text-sm font-semibold text-rose-600"><?= formatCurrency($usageCost) ?></span>
                        </div>
                        <div class="pt-3 border-t border-gray-200 flex items-center justify-between">
                            <span class="text-sm text-gray-700 font-semibold"><?= lang('Treatment.gross_margin') ?></span>
                            <span class="text-sm font-bold <?= $grossMargin >= 0 ? 'text-emerald-700' : 'text-rose-600' ?>">
                                <?= formatCurrency($grossMargin) ?>
                            </span>
                        </div>
                        <?php if (empty($usage_records)): ?>
                            <p class="text-xs text-gray-500 pt-2"><?= lang('Treatment.no_inventory_usage_recorded') ?></p>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Patient Information -->
                <div class="bg-white/80 backdrop-blur-lg rounded-2xl border border-white/20 shadow-xl p-6">
                    <h2 class="text-xl font-bold text-gray-800 mb-4 flex items-center">
                        <i class="fas fa-user text-purple-600 mr-2"></i>
                        <?= lang('Treatment.patient_information') ?>
                    </h2>
                    <div class="space-y-3">
                        <div>
                            <label class="block text-sm font-medium text-gray-600 mb-1"><?= lang('Treatment.patient_name') ?></label>
                            <p class="text-gray-800 font-semibold"><?= htmlspecialchars($treatment['first_name'] . ' ' . $treatment['last_name']) ?></p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-600 mb-1"><?= lang('Treatment.phone') ?></label>
                            <p class="text-gray-800"><?= htmlspecialchars($treatment['phone'] ?? lang('Treatment.na')) ?></p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-600 mb-1"><?= lang('Treatment.email') ?></label>
                            <p class="text-gray-800"><?= htmlspecialchars($treatment['email'] ?? lang('Treatment.na')) ?></p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-600 mb-1"><?= lang('Treatment.date_of_birth') ?></label>
                            <p class="text-gray-800"><?= $treatment['date_of_birth'] ? formatDate($treatment['date_of_birth']) : lang('Treatment.na') ?></p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-600 mb-1"><?= lang('Treatment.gender') ?></label>
                            <p class="text-gray-800">
                                <?php
                                    if (!empty($treatment['gender'])) {
                                        $genderRaw = strtolower($treatment['gender']);
                                        $genderLabel = lang('Treatment.gender_values.' . $genderRaw);
                                        if ($genderLabel === 'Treatment.gender_values.' . $genderRaw) {
                                            $genderLabel = ucfirst($treatment['gender']);
                                        }
                                        echo esc($genderLabel);
                                    } else {
                                        echo esc(lang('Treatment.na'));
                                    }
                                ?>
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Treatment Timeline -->
                <div class="bg-white/80 backdrop-blur-lg rounded-2xl border border-white/20 shadow-xl p-6">
                    <h2 class="text-xl font-bold text-gray-800 mb-4 flex items-center">
                        <i class="fas fa-clock text-indigo-600 mr-2"></i>
                        <?= lang('Treatment.treatment_timeline') ?>
                    </h2>
                    <div class="space-y-3">
                        <div>
                            <label class="block text-sm font-medium text-gray-600 mb-1"><?= lang('Treatment.start_date') ?></label>
                            <p class="text-gray-800"><?= $treatment['start_date'] ? formatDate($treatment['start_date']) : lang('Treatment.na') ?></p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-600 mb-1"><?= lang('Treatment.completion_date') ?></label>
                            <p class="text-gray-800"><?= $treatment['completion_date'] ? formatDateTime($treatment['completion_date']) : lang('Treatment.not_completed') ?></p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-600 mb-1"><?= lang('Treatment.created_at') ?></label>
                            <p class="text-gray-800"><?= $treatment['created_at'] ? formatDateTime($treatment['created_at']) : lang('Treatment.na') ?></p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-600 mb-1"><?= lang('Treatment.created_by') ?></label>
                            <p class="text-gray-800"><?= $doctorName !== '' ? lang('Treatment.doctor_prefix') . ' ' . htmlspecialchars($doctorName) : lang('Treatment.unknown') ?></p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-600 mb-1"><?= lang('Treatment.last_updated') ?></label>
                            <p class="text-gray-800"><?= $treatment['updated_at'] ? formatDateTime($treatment['updated_at']) : lang('Treatment.na') ?></p>
                        </div>
                    </div>
                </div>

                <!-- Quick Actions -->
                <div class="bg-white/80 backdrop-blur-lg rounded-2xl border border-white/20 shadow-xl p-6">
                    <h2 class="text-xl font-bold text-gray-800 mb-4 flex items-center">
                        <i class="fas fa-bolt text-orange-600 mr-2"></i>
                        <?= lang('Treatment.quick_actions') ?>
                    </h2>
                    <div class="space-y-3">
                        <?php if ($treatment['status'] === 'active'): ?>
                        <button onclick="completeTreatment(<?= $treatment['id'] ?>)" class="w-full inline-flex items-center justify-center px-4 py-2 bg-green-600 text-white text-sm font-bold rounded-xl hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 transition-all duration-300 shadow-lg hover:shadow-xl border border-green-500">
                            <i class="fas fa-check mr-2 text-white"></i>
                            <span class="text-white font-bold"><?= lang('Treatment.mark_as_completed') ?></span>
                        </button>
                        <?php endif; ?>
                        
                        <button onclick="printTreatment(<?= $treatment['id'] ?>)" class="w-full inline-flex items-center justify-center px-4 py-2 bg-blue-600 text-white text-sm font-bold rounded-xl hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition-all duration-300 shadow-lg hover:shadow-xl border border-blue-500">
                            <i class="fas fa-print mr-2 text-white"></i>
                            <span class="text-white font-bold"><?= lang('Treatment.print_treatment') ?></span>
                        </button>
                        
                        <button onclick="deleteTreatment(<?= $treatment['id'] ?>)" class="w-full inline-flex items-center justify-center px-4 py-2 bg-red-600 text-white text-sm font-bold rounded-xl hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 transition-all duration-300 shadow-lg hover:shadow-xl border border-red-500">
                            <i class="fas fa-trash mr-2 text-white"></i>
                            <span class="text-white font-bold"><?= lang('Treatment.delete_treatment') ?></span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function completeTreatment(id) {
    if (confirm('<?= esc(lang('Treatment.confirm_mark_completed'), 'js') ?>')) {
        const csrfHeader = window.csrfConfig?.header || 'X-CSRF-TOKEN';
        const csrfToken = window.getCsrfToken ? window.getCsrfToken() : '';
        fetch(`<?= base_url('treatment') ?>/${id}/complete`, {
            method: 'POST',
            credentials: 'same-origin',
            headers: {
                'Content-Type': 'application/json',
                [csrfHeader]: csrfToken,
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data && data.csrf_token && window.refreshCsrfToken) {
                window.refreshCsrfToken(data.csrf_token);
            }
            if (data.success) {
                location.reload();
            } else {
                alert('<?= esc(lang('Treatment.error_prefix'), 'js') ?> ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('<?= esc(lang('Treatment.error_completing_treatment'), 'js') ?>');
        });
    }
}

function printTreatment(id) {
    window.open(`<?= base_url('treatment') ?>/${id}/print`, '_blank');
}

function deleteTreatment(id) {
    if (confirm('<?= esc(lang('Treatment.confirm_delete_treatment'), 'js') ?>')) {
        const csrfHeader = window.csrfConfig?.header || 'X-CSRF-TOKEN';
        const csrfToken = window.getCsrfToken ? window.getCsrfToken() : '';
        fetch(`<?= base_url('treatment') ?>/${id}`, {
            method: 'DELETE',
            credentials: 'same-origin',
            headers: {
                'Content-Type': 'application/json',
                [csrfHeader]: csrfToken,
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data && data.csrf_token && window.refreshCsrfToken) {
                window.refreshCsrfToken(data.csrf_token);
            }
            if (data.success) {
                window.location.href = '<?= base_url('treatment') ?>';
            } else {
                alert('<?= esc(lang('Treatment.error_prefix'), 'js') ?> ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('<?= esc(lang('Treatment.error_deleting_treatment'), 'js') ?>');
        });
    }
}
</script>
<?= $this->endSection() ?>
