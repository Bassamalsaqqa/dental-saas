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
    </style>
</head>
<body class="bg-white text-gray-900">
    <!-- Print Header -->
    <div class="bg-gradient-to-r from-blue-600 to-indigo-600 text-white py-8 print:bg-blue-600">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex flex-col lg:flex-row lg:justify-between lg:items-center">
                <div class="flex items-center space-x-4 mb-4 lg:mb-0">
                    <div class="w-16 h-16 bg-white/20 rounded-2xl flex items-center justify-center print:bg-white/30">
                        <i class="fas fa-tooth text-white text-2xl"></i>
                    </div>
                    <div>
                        <h1 class="text-3xl font-bold"><?= esc($clinic['name']) ?></h1>
                        <p class="text-blue-100 text-lg">Patient Odontogram Report</p>
                        <div class="text-blue-100 text-sm mt-2">
                            <p><?= esc($clinic['address']) ?></p>
                            <p>Phone: <?= esc($clinic['phone']) ?> | Email: <?= esc($clinic['email']) ?></p>
                            <?php if ($clinic['website']): ?>
                            <p>Website: <?= esc($clinic['website']) ?></p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Print Content -->
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
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
        <!-- Patient Information Section -->
        <div class="bg-white border border-gray-200 rounded-xl shadow-sm mb-8 print:border-gray-300 print:shadow-none">
            <div class="bg-gray-50 px-6 py-4 border-b border-gray-200 rounded-t-xl print:bg-gray-100">
                <h2 class="text-xl font-bold text-gray-800 flex items-center">
                    <i class="fas fa-user-circle mr-3 text-blue-600"></i>
                    Patient Information
                </h2>
            </div>
            <div class="p-6">
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                    <div class="space-y-2">
                        <label class="text-sm font-medium text-gray-500">Full Name</label>
                        <p class="text-lg font-semibold text-gray-900"><?= $patient['first_name'] . ' ' . $patient['last_name'] ?></p>
                    </div>
                    <div class="space-y-2">
                        <label class="text-sm font-medium text-gray-500">Patient ID</label>
                        <p class="text-lg font-semibold text-gray-900"><?= $patient['patient_id'] ?></p>
                    </div>
                    <div class="space-y-2">
                        <label class="text-sm font-medium text-gray-500">Date of Birth</label>
                        <p class="text-lg font-semibold text-gray-900"><?= date('M j, Y', strtotime($patient['date_of_birth'])) ?></p>
                    </div>
                    <div class="space-y-2">
                        <label class="text-sm font-medium text-gray-500">Age</label>
                        <p class="text-lg font-semibold text-gray-900"><?= date_diff(date_create($patient['date_of_birth']), date_create('today'))->y ?> years</p>
                    </div>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-6">
                    <div class="space-y-2">
                        <label class="text-sm font-medium text-gray-500">Phone</label>
                        <p class="text-lg font-semibold text-gray-900"><?= $patient['phone'] ?></p>
                    </div>
                    <div class="space-y-2">
                        <label class="text-sm font-medium text-gray-500">Email</label>
                        <p class="text-lg font-semibold text-gray-900"><?= $patient['email'] ?></p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Odontogram Statistics -->
        <div class="bg-white border border-gray-200 rounded-xl shadow-sm mb-8 print:border-gray-300 print:shadow-none">
            <div class="bg-gray-50 px-6 py-4 border-b border-gray-200 rounded-t-xl print:bg-gray-100">
                <h2 class="text-xl font-bold text-gray-800 flex items-center">
                    <i class="fas fa-chart-pie mr-3 text-green-600"></i>
                    Dental Chart Statistics
                </h2>
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

        <!-- Dental Chart Visualization -->
        <div class="bg-white border border-gray-200 rounded-xl shadow-sm mb-8 print:border-gray-300 print:shadow-none">
            <div class="bg-gray-50 px-6 py-4 border-b border-gray-200 rounded-t-xl print:bg-gray-100">
                <h2 class="text-xl font-bold text-gray-800 flex items-center">
                    <i class="fas fa-tooth mr-3 text-purple-600"></i>
                    Dental Chart
                </h2>
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
                            <div class="w-12 h-14 border-2 border-gray-300 rounded-lg flex flex-col items-center justify-center mx-0.5 relative bg-white print:border-gray-400 <?= $conditionClass ?>">
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
                            <div class="w-12 h-14 border-2 border-gray-300 rounded-lg flex flex-col items-center justify-center mx-0.5 relative bg-white print:border-gray-400 <?= $conditionClass ?>">
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
                            <div class="w-12 h-14 border-2 border-gray-300 rounded-lg flex flex-col items-center justify-center mx-0.5 relative bg-white print:border-gray-400 <?= $conditionClass ?>">
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
                            <div class="w-12 h-14 border-2 border-gray-300 rounded-lg flex flex-col items-center justify-center mx-0.5 relative bg-white print:border-gray-400 <?= $conditionClass ?>">
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
        <div class="bg-white border border-gray-200 rounded-xl shadow-sm mb-8 print:border-gray-300 print:shadow-none">
            <div class="bg-gray-50 px-6 py-4 border-b border-gray-200 rounded-t-xl print:bg-gray-100">
                <h2 class="text-xl font-bold text-gray-800 flex items-center">
                    <i class="fas fa-list mr-3 text-orange-600"></i>
                    Tooth Conditions
                </h2>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50 print:bg-gray-100">
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
                                            echo 'bg-red-100 text-red-800';
                                            break;
                                        case 'filling':
                                            echo 'bg-blue-100 text-blue-800';
                                            break;
                                        case 'crown':
                                            echo 'bg-purple-100 text-purple-800';
                                            break;
                                        case 'extraction':
                                            echo 'bg-gray-100 text-gray-800';
                                            break;
                                        default:
                                            echo 'bg-yellow-100 text-yellow-800';
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
        <div class="bg-gray-50 border border-gray-200 rounded-xl p-6 text-center print:bg-gray-100 print:border-gray-300">
            <div class="text-sm text-gray-600">
                <p>This report was generated on <?= date('F j, Y \a\t g:i A') ?> by <?= esc($clinic['name']) ?></p>
                <p class="mt-2">For questions about this report, please contact <?= esc($clinic['name']) ?></p>
                <p class="mt-1">Phone: <?= esc($clinic['phone']) ?> | Email: <?= esc($clinic['email']) ?></p>
            </div>
        </div>
    </div>
</div>

<script>
// Auto-print when page loads
window.addEventListener('load', function() {
    // Small delay to ensure all content is loaded
    setTimeout(function() {
        window.print();
    }, 500);
});

// Print styles
window.addEventListener('beforeprint', function() {
    // Hide action buttons when printing
    const actionButtons = document.querySelectorAll('button, .no-print');
    actionButtons.forEach(button => {
        button.style.display = 'none';
    });
});

window.addEventListener('afterprint', function() {
    // Show action buttons after printing
    const actionButtons = document.querySelectorAll('button, .no-print');
    actionButtons.forEach(button => {
        button.style.display = '';
    });
});
</script>

<style>
/* Print styles */
@media print {
    .no-print {
        display: none !important;
    }
    
    body {
        -webkit-print-color-adjust: exact;
        color-adjust: exact;
    }
    
    .print\\:bg-blue-600 {
        background-color: #2563eb !important;
    }
    
    .print\\:bg-white\\/30 {
        background-color: rgba(255, 255, 255, 0.3) !important;
    }
    
    .print\\:bg-gray-100 {
        background-color: #f3f4f6 !important;
    }
    
    .print\\:border-gray-300 {
        border-color: #d1d5db !important;
    }
    
    .print\\:border-gray-400 {
        border-color: #9ca3af !important;
    }
    
    .print\\:shadow-none {
        box-shadow: none !important;
    }
    
    .print\\:hidden {
        display: none !important;
    }
    
    @page {
        margin: 0.5in;
        size: A4;
    }
    
    .min-h-screen {
        min-height: auto !important;
    }
}
</style>

</body>
</html>
