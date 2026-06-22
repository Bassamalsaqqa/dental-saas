<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Treatment Contract - <?= esc($patient['first_name'] . ' ' . $patient['last_name']) ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=Noto+Sans+Arabic:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @page {
            margin: 0.5in;
            size: A4;
        }

        body {
            font-family: "Inter", "Noto Sans Arabic", ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", "Roboto", "Noto Sans", sans-serif;
            -webkit-print-color-adjust: exact;
            color-adjust: exact;
        }

        .no-print {
            display: none !important;
        }

        .print-break {
            page-break-before: always;
        }

        .print-avoid-break {
            page-break-inside: avoid;
        }
    </style>
</head>
<body class="bg-white text-gray-900">
    <?php
    $snapshotRows = [];
    if (!empty($snapshot['data_snapshot_json'])) {
        $decodedSnapshotRows = json_decode($snapshot['data_snapshot_json'], true);
        if (is_array($decodedSnapshotRows)) {
            $snapshotRows = array_values(array_filter($decodedSnapshotRows, static function ($row) {
                return !empty($row['condition_type']) && ($row['condition_type'] !== 'healthy');
            }));
        }
    }
    ?>
    <div class="bg-gradient-to-r from-blue-600 to-indigo-600 text-white py-8 print:bg-blue-600">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex flex-col lg:flex-row lg:justify-between lg:items-center">
                <div class="flex items-center space-x-4 mb-4 lg:mb-0">
                    <div class="w-16 h-16 bg-white/20 rounded-2xl flex items-center justify-center print:bg-white/30">
                        <?php if (!empty($clinic['logo_path'])): ?>
                            <?php
                            $logoSrc = (strpos($clinic['logo_path'], 'http://') === 0 || strpos($clinic['logo_path'], 'https://') === 0)
                                ? $clinic['logo_path']
                                : base_url(ltrim($clinic['logo_path'], '/'));
                            ?>
                            <img src="<?= esc($logoSrc) ?>" alt="<?= esc($clinic['name']) ?>" class="w-full h-full object-contain p-2">
                        <?php else: ?>
                            <i class="fas fa-tooth text-white text-2xl"></i>
                        <?php endif; ?>
                    </div>
                    <div>
                        <h1 class="text-3xl font-bold"><?= esc($clinic['name'] ?? 'Clinic') ?></h1>
                        <p class="text-blue-100 text-lg">Treatment Contract</p>
                        <div class="text-blue-100 text-sm mt-2">
                            <?php if (!empty($clinic['address'])): ?>
                                <p><?= esc($clinic['address']) ?></p>
                            <?php endif; ?>
                            <p>
                                <?php if (!empty($clinic['phone'])): ?>Phone: <?= esc($clinic['phone']) ?><?php endif; ?>
                                <?php if (!empty($clinic['email'])): ?> | Email: <?= esc($clinic['email']) ?><?php endif; ?>
                            </p>
                            <?php if (!empty($clinic['website'])): ?>
                                <p>Website: <?= esc($clinic['website']) ?></p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                <div class="text-blue-100 text-sm">
                    <div>Status: <?= esc(ucfirst($contract['status'] ?? 'draft')) ?></div>
                    <div>Generated on <?= formatDateTime(date('Y-m-d H:i:s')) ?></div>
                </div>
            </div>
        </div>
    </div>

    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <?php $creatorName = trim(($contract['creator_first_name'] ?? '') . ' ' . ($contract['creator_last_name'] ?? '')); ?>
        <div class="bg-white border border-gray-200 rounded-xl shadow-sm mb-8 print:border-gray-300 print:shadow-none">
            <div class="bg-gray-50 px-6 py-4 border-b border-gray-200 rounded-t-xl print:bg-gray-100">
                <h2 class="text-lg font-semibold text-gray-800">Patient</h2>
            </div>
            <div class="p-6">
                <div class="text-gray-900 font-semibold"><?= esc($patient['first_name'] . ' ' . $patient['last_name']) ?></div>
                <div class="text-sm text-gray-600">Patient ID: <?= esc($patient['patient_id'] ?? $patient['id']) ?></div>
                <div class="text-sm text-gray-600">Created By: <?= $creatorName !== '' ? 'Dr. ' . esc($creatorName) : 'Unknown' ?></div>
            </div>
        </div>

        <div class="bg-white border border-gray-200 rounded-xl shadow-sm mb-8 print:border-gray-300 print:shadow-none">
            <div class="bg-gray-50 px-6 py-4 border-b border-gray-200 rounded-t-xl print:bg-gray-100">
                <h2 class="text-lg font-semibold text-gray-800">Contract Odontogram</h2>
            </div>
            <div class="p-6">
                <?php if (!empty($snapshot['svg_snapshot'])): ?>
                    <?= $snapshot['svg_snapshot'] ?>
                <?php elseif (!empty($snapshotRows)): ?>
                    <div class="text-sm text-gray-700 mb-3">Recorded tooth conditions at the time of this plan</div>
                    <table class="min-w-full divide-y divide-gray-200 text-sm">
                        <thead class="bg-gray-50 print:bg-gray-100">
                            <tr>
                                <th class="px-4 py-2 text-left text-xs font-semibold text-gray-500 uppercase">Tooth</th>
                                <th class="px-4 py-2 text-left text-xs font-semibold text-gray-500 uppercase">Condition</th>
                                <th class="px-4 py-2 text-left text-xs font-semibold text-gray-500 uppercase">Notes</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            <?php foreach ($snapshotRows as $row): ?>
                                <tr>
                                    <td class="px-4 py-2"><?= esc((string) ($row['tooth_number'] ?? '--')) ?></td>
                                    <td class="px-4 py-2"><?= esc(ucwords(str_replace('_', ' ', (string) ($row['condition_type'] ?? '')))) ?></td>
                                    <td class="px-4 py-2"><?= esc((string) (($row['condition_description'] ?? '') ?: ($row['treatment_notes'] ?? '—'))) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <div class="text-sm text-gray-500">No snapshot captured.</div>
                <?php endif; ?>
            </div>
        </div>

        <div class="bg-white border border-gray-200 rounded-xl shadow-sm mb-8 print:border-gray-300 print:shadow-none">
            <div class="bg-gray-50 px-6 py-4 border-b border-gray-200 rounded-t-xl print:bg-gray-100">
                <h2 class="text-lg font-semibold text-gray-800">Treatment Items</h2>
            </div>
            <div class="p-6">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 text-sm">
                        <thead class="bg-gray-50 print:bg-gray-100">
                            <tr>
                                <th class="px-4 py-2 text-left text-xs font-semibold text-gray-500 uppercase">Code</th>
                                <th class="px-4 py-2 text-left text-xs font-semibold text-gray-500 uppercase">Tooth</th>
                                <th class="px-4 py-2 text-left text-xs font-semibold text-gray-500 uppercase">Description</th>
                                <th class="px-4 py-2 text-left text-xs font-semibold text-gray-500 uppercase">Qty</th>
                                <th class="px-4 py-2 text-left text-xs font-semibold text-gray-500 uppercase">Price</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            <?php if (!empty($items)): ?>
                                <?php foreach ($items as $item): ?>
                                    <tr>
                                        <td class="px-4 py-2"><?= esc($item['procedure_code'] ?? '-') ?></td>
                                        <td class="px-4 py-2"><?= esc($item['tooth_no'] ?? '-') ?></td>
                                        <td class="px-4 py-2"><?= esc($item['description'] ?? '-') ?></td>
                                        <td class="px-4 py-2"><?= esc($item['qty'] ?? 1) ?></td>
                                        <td class="px-4 py-2"><?= number_format((float) ($item['price'] ?? 0), 2) ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="5" class="px-4 py-6 text-center text-gray-500">No items added.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
                <?php if (!empty($contract['cost_details'])): ?>
                    <div class="mt-4 text-sm text-gray-600">
                        <strong>Cost Details:</strong> <?= nl2br(esc($contract['cost_details'])) ?>
                    </div>
                <?php endif; ?>
                <div class="mt-4 text-right text-sm text-gray-700">
                    <span class="font-semibold">Total:</span>
                    <?= esc($contract['currency'] ?? 'USD') ?> <?= number_format((float) ($contract['total_cost'] ?? 0), 2) ?>
                </div>
            </div>
        </div>

        <?php if (!empty($contract['terms_conditions'])): ?>
            <div class="bg-white border border-gray-200 rounded-xl shadow-sm mb-8 print:border-gray-300 print:shadow-none">
                <div class="bg-gray-50 px-6 py-4 border-b border-gray-200 rounded-t-xl print:bg-gray-100">
                    <h2 class="text-lg font-semibold text-gray-800">Terms &amp; Conditions</h2>
                </div>
                <div class="p-6 text-sm text-gray-700"><?= nl2br(esc($contract['terms_conditions'])) ?></div>
            </div>
        <?php endif; ?>

        <?php if (!empty($contract['payment_terms'])): ?>
            <div class="bg-white border border-gray-200 rounded-xl shadow-sm mb-8 print:border-gray-300 print:shadow-none">
                <div class="bg-gray-50 px-6 py-4 border-b border-gray-200 rounded-t-xl print:bg-gray-100">
                    <h2 class="text-lg font-semibold text-gray-800">Payment Terms</h2>
                </div>
                <div class="p-6 text-sm text-gray-700"><?= nl2br(esc($contract['payment_terms'])) ?></div>
            </div>
        <?php endif; ?>

        <?php if (!empty($contract['duration_plan'])): ?>
            <div class="bg-white border border-gray-200 rounded-xl shadow-sm mb-8 print:border-gray-300 print:shadow-none">
                <div class="bg-gray-50 px-6 py-4 border-b border-gray-200 rounded-t-xl print:bg-gray-100">
                    <h2 class="text-lg font-semibold text-gray-800">Duration Plan</h2>
                </div>
                <div class="p-6 text-sm text-gray-700"><?= esc($contract['duration_plan']) ?></div>
            </div>
        <?php endif; ?>

        <?php if (!empty($contract['notes'])): ?>
            <div class="bg-white border border-gray-200 rounded-xl shadow-sm mb-8 print:border-gray-300 print:shadow-none">
                <div class="bg-gray-50 px-6 py-4 border-b border-gray-200 rounded-t-xl print:bg-gray-100">
                    <h2 class="text-lg font-semibold text-gray-800">Notes</h2>
                </div>
                <div class="p-6 text-sm text-gray-700"><?= nl2br(esc($contract['notes'])) ?></div>
            </div>
        <?php endif; ?>

        <div class="bg-white border border-gray-200 rounded-xl shadow-sm mb-8 print:border-gray-300 print:shadow-none">
            <div class="bg-gray-50 px-6 py-4 border-b border-gray-200 rounded-t-xl print:bg-gray-100">
                <h2 class="text-lg font-semibold text-gray-800">Signature</h2>
            </div>
            <div class="p-6 text-sm text-gray-700">
                <?php if (!empty($signature)): ?>
                    <div>Signed by: <?= esc($signature['signer_name'] ?? 'Patient') ?></div>
                    <div class="text-xs text-gray-500">Signed at: <?= esc($signature['signed_at'] ?? '') ?></div>
                    <?php if (!empty($signature['signature_data'])): ?>
                        <img src="<?= esc($signature['signature_data']) ?>" alt="Signature" class="mt-4 max-w-xs border border-gray-200 rounded-lg p-2">
                    <?php endif; ?>
                <?php else: ?>
                    <div class="text-sm text-gray-500">Not signed.</div>
                <?php endif; ?>
            </div>
        </div>

        <div class="bg-gray-50 border border-gray-200 rounded-xl p-6 text-center print:bg-gray-100 print:border-gray-300">
            <div class="text-sm text-gray-600">
                <p>This contract was generated on <?= formatDateTime(date('Y-m-d H:i:s')) ?> by <?= esc($clinic['name'] ?? 'Clinic') ?></p>
                <?php if (!empty($clinic['phone']) || !empty($clinic['email'])): ?>
                    <p class="mt-2">For questions about this contract, please contact <?= esc($clinic['name'] ?? 'Clinic') ?></p>
                    <p class="mt-1">Phone: <?= esc($clinic['phone'] ?? '-') ?> | Email: <?= esc($clinic['email'] ?? '-') ?></p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>
</html>
