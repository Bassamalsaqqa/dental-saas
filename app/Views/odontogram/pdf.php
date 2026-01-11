<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dental Chart - <?= $patient['first_name'] . ' ' . $patient['last_name'] ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @page {
            margin: 0.5in;
            size: A4;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
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
        
        /* Additional PDF optimization */
        @media print {
            body {
                margin: 0;
                padding: 0;
            }
            
            .bg-blue-600 {
                background-color: #2563eb !important;
                -webkit-print-color-adjust: exact;
                color-adjust: exact;
            }
            
            .bg-gray-100 {
                background-color: #f3f4f6 !important;
                -webkit-print-color-adjust: exact;
                color-adjust: exact;
            }
            
            .text-white {
                color: white !important;
                -webkit-print-color-adjust: exact;
                color-adjust: exact;
            }
        }
    </style>
</head>
<body class="bg-white text-gray-900">
    <div class="bg-blue-600 text-white py-6 mb-8">
        <div class="max-w-6xl mx-auto px-4">
            <div class="flex items-center space-x-4">
                <div class="w-16 h-16 bg-white/20 rounded-2xl flex items-center justify-center overflow-hidden">
                    <?php if (!empty($clinic['logo_path'])): ?>
                        <?php 
                            $logoSrc = (strpos($clinic['logo_path'], 'http://') === 0 || strpos($clinic['logo_path'], 'https://') === 0) 
                                ? esc($clinic['logo_path']) 
                                : base_url(ltrim($clinic['logo_path'], '/'));
                        ?>
                        <img src="<?= esc($logoSrc) ?>" alt="<?= esc($clinic['name']) ?>" class="w-full h-full object-contain p-2">
                    <?php else: ?>
                        <svg class="w-8 h-8 text-white" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z"/>
                        </svg>
                    <?php endif; ?>
                </div>
                <div>
                    <h1 class="text-3xl font-bold">Dental Chart Report</h1>
                    <p class="text-blue-100 text-lg">Patient Odontogram</p>
                </div>
            </div>
        </div>
    </div>

    <div class="max-w-6xl mx-auto px-4">
        <?php
        // Create a lookup array for efficient tooth data access
        $toothLookup = [];
        foreach ($odontogram as $tooth) {
            $toothLookup[$tooth['tooth_number']] = $tooth;
        }
        
        // Function to get tooth condition class
        function getToothConditionClass($toothData) {
            if (!$toothData) {
                return 'bg-green-100 border-green-400';
            }
            
            switch ($toothData['condition_type']) {
                case 'cavity':
                    return 'bg-yellow-100 border-yellow-400';
                case 'filling':
                    return 'bg-blue-100 border-blue-400';
                case 'crown':
                    return 'bg-purple-100 border-purple-400';
                case 'root_canal':
                    return 'bg-pink-100 border-pink-400';
                case 'extracted':
                    return 'bg-red-100 border-red-400 opacity-70';
                default:
                    return 'bg-green-100 border-green-400';
            }
        }
        ?>
        
        <!-- Patient Information -->
        <div class="bg-white border border-gray-300 rounded-lg shadow-sm mb-8 print-avoid-break">
            <div class="bg-gray-100 px-6 py-4 border-b border-gray-300 rounded-t-lg">
                <h2 class="text-xl font-bold text-gray-800">Patient Information</h2>
            </div>
            <div class="p-6">
                <div class="grid grid-cols-2 md:grid-cols-4 gap-6">
                    <div>
                        <label class="text-sm font-medium text-gray-500">Full Name</label>
                        <p class="text-lg font-semibold text-gray-900"><?= $patient['first_name'] . ' ' . $patient['last_name'] ?></p>
                    </div>
                    <div>
                        <label class="text-sm font-medium text-gray-500">Patient ID</label>
                        <p class="text-lg font-semibold text-gray-900"><?= $patient['patient_id'] ?></p>
                    </div>
                    <div>
                        <label class="text-sm font-medium text-gray-500">Date of Birth</label>
                        <p class="text-lg font-semibold text-gray-900"><?= date('M j, Y', strtotime($patient['date_of_birth'])) ?></p>
                    </div>
                    <div>
                        <label class="text-sm font-medium text-gray-500">Age</label>
                        <p class="text-lg font-semibold text-gray-900"><?= date_diff(date_create($patient['date_of_birth']), date_create('today'))->y ?> years</p>
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-6 mt-6">
                    <div>
                        <label class="text-sm font-medium text-gray-500">Phone</label>
                        <p class="text-lg font-semibold text-gray-900"><?= $patient['phone'] ?></p>
                    </div>
                    <div>
                        <label class="text-sm font-medium text-gray-500">Email</label>
                        <p class="text-lg font-semibold text-gray-900"><?= $patient['email'] ?></p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Statistics -->
        <div class="bg-white border border-gray-300 rounded-lg shadow-sm mb-8 print-avoid-break">
            <div class="bg-gray-100 px-6 py-4 border-b border-gray-300 rounded-t-lg">
                <h2 class="text-xl font-bold text-gray-800">Dental Chart Statistics</h2>
            </div>
            <div class="p-6">
                <div class="grid grid-cols-2 md:grid-cols-4 gap-6">
                    <div class="text-center">
                        <div class="text-3xl font-bold text-green-600"><?= $stats['healthy_count'] ?? 0 ?></div>
                        <div class="text-sm text-gray-600">Healthy Teeth</div>
                    </div>
                    <div class="text-center">
                        <div class="text-3xl font-bold text-red-600"><?= $stats['treated_count'] ?? 0 ?></div>
                        <div class="text-sm text-gray-600">Treated Teeth</div>
                    </div>
                    <div class="text-center">
                        <div class="text-3xl font-bold text-yellow-600"><?= $stats['needs_treatment_count'] ?? 0 ?></div>
                        <div class="text-sm text-gray-600">Needs Treatment</div>
                    </div>
                    <div class="text-center">
                        <div class="text-3xl font-bold text-blue-600"><?= $stats['total_conditions'] ?? 0 ?></div>
                        <div class="text-sm text-gray-600">Total Conditions</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Dental Chart -->
        <div class="bg-white border border-gray-300 rounded-lg shadow-sm mb-8 print-avoid-break">
            <div class="bg-gray-100 px-6 py-4 border-b border-gray-300 rounded-t-lg">
                <h2 class="text-xl font-bold text-gray-800">Dental Chart</h2>
            </div>
            <div class="p-6">
                <!-- Upper Jaw -->
                <div class="mb-8">
                    <h3 class="text-lg font-semibold text-gray-700 mb-4 text-center">Upper Jaw (Maxilla)</h3>
                    <div class="grid grid-cols-8 gap-2 justify-center">
                        <?php for ($i = 18; $i >= 11; $i--): ?>
                            <?php
                            $toothData = $toothLookup[$i] ?? null;
                            $conditionClass = getToothConditionClass($toothData);
                            ?>
                            <div class="w-12 h-14 border-2 border-gray-300 rounded-lg flex flex-col items-center justify-center mx-0.5 relative bg-white <?= $conditionClass ?>">
                                <div class="text-xs font-bold text-gray-700"><?= $i ?></div>
                                <?php if ($toothData): ?>
                                    <div class="text-xs text-gray-500 mt-0.5 text-center leading-tight"><?= ucfirst($toothData['condition_type']) ?></div>
                                <?php endif; ?>
                            </div>
                        <?php endfor; ?>
                    </div>
                    <div class="grid grid-cols-8 gap-2 justify-center mt-2">
                        <?php for ($i = 21; $i <= 28; $i++): ?>
                            <?php
                            $toothData = $toothLookup[$i] ?? null;
                            $conditionClass = getToothConditionClass($toothData);
                            ?>
                            <div class="w-12 h-14 border-2 border-gray-300 rounded-lg flex flex-col items-center justify-center mx-0.5 relative bg-white <?= $conditionClass ?>">
                                <div class="text-xs font-bold text-gray-700"><?= $i ?></div>
                                <?php if ($toothData): ?>
                                    <div class="text-xs text-gray-500 mt-0.5 text-center leading-tight"><?= ucfirst($toothData['condition_type']) ?></div>
                                <?php endif; ?>
                            </div>
                        <?php endfor; ?>
                    </div>
                </div>

                <!-- Lower Jaw -->
                <div>
                    <h3 class="text-lg font-semibold text-gray-700 mb-4 text-center">Lower Jaw (Mandible)</h3>
                    <div class="grid grid-cols-8 gap-2 justify-center">
                        <?php for ($i = 48; $i >= 41; $i--): ?>
                            <?php
                            $toothData = $toothLookup[$i] ?? null;
                            $conditionClass = getToothConditionClass($toothData);
                            ?>
                            <div class="w-12 h-14 border-2 border-gray-300 rounded-lg flex flex-col items-center justify-center mx-0.5 relative bg-white <?= $conditionClass ?>">
                                <div class="text-xs font-bold text-gray-700"><?= $i ?></div>
                                <?php if ($toothData): ?>
                                    <div class="text-xs text-gray-500 mt-0.5 text-center leading-tight"><?= ucfirst($toothData['condition_type']) ?></div>
                                <?php endif; ?>
                            </div>
                        <?php endfor; ?>
                    </div>
                    <div class="grid grid-cols-8 gap-2 justify-center mt-2">
                        <?php for ($i = 31; $i <= 38; $i++): ?>
                            <?php
                            $toothData = $toothLookup[$i] ?? null;
                            $conditionClass = getToothConditionClass($toothData);
                            ?>
                            <div class="w-12 h-14 border-2 border-gray-300 rounded-lg flex flex-col items-center justify-center mx-0.5 relative bg-white <?= $conditionClass ?>">
                                <div class="text-xs font-bold text-gray-700"><?= $i ?></div>
                                <?php if ($toothData): ?>
                                    <div class="text-xs text-gray-500 mt-0.5 text-center leading-tight"><?= ucfirst($toothData['condition_type']) ?></div>
                                <?php endif; ?>
                            </div>
                        <?php endfor; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tooth Conditions Table -->
        <?php if (!empty($odontogram)): ?>
        <div class="bg-white border border-gray-300 rounded-lg shadow-sm mb-8 print-avoid-break">
            <div class="bg-gray-100 px-6 py-4 border-b border-gray-300 rounded-t-lg">
                <h2 class="text-xl font-bold text-gray-800">Tooth Conditions</h2>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-100">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tooth Number</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Condition</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Description</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Treatment Date</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php foreach ($odontogram as $tooth): ?>
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                <?= $tooth['tooth_number'] ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                    <?php
                                    switch($tooth['condition_type']) {
                                        case 'cavity':
                                            echo 'bg-yellow-100 text-yellow-800';
                                            break;
                                        case 'filling':
                                            echo 'bg-blue-100 text-blue-800';
                                            break;
                                        case 'crown':
                                            echo 'bg-purple-100 text-purple-800';
                                            break;
                                        case 'root_canal':
                                            echo 'bg-pink-100 text-pink-800';
                                            break;
                                        case 'extracted':
                                            echo 'bg-red-100 text-red-800';
                                            break;
                                        default:
                                            echo 'bg-green-100 text-green-800';
                                    }
                                    ?>">
                                    <?= ucfirst(str_replace('_', ' ', $tooth['condition_type'])) ?>
                                </span>
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-500">
                                <?= $tooth['description'] ?? 'No description' ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                <?= date('M j, Y', strtotime($tooth['treatment_date'])) ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <?php endif; ?>

        <!-- Footer -->
        <div class="bg-gray-100 border border-gray-300 rounded-lg p-6 text-center">
            <div class="text-sm text-gray-600">
                <p>This report was generated on <?= date('F j, Y \a\t g:i A') ?> by the <?= esc($clinic['name']) ?></p>
                <p class="mt-2">For questions about this report, please contact your dental office</p>
            </div>
        </div>
    </div>

    <script>
        // The print dialog will be triggered by the parent window
        // This view is optimized for PDF generation
    </script>
</body>
</html>
