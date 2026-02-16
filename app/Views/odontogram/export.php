<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dental Chart Export - <?= $patient['first_name'] . ' ' . $patient['last_name'] ?></title>
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
    <!-- Export Header -->
    <div class="bg-gradient-to-r from-blue-600 to-indigo-600 text-white py-8">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex flex-col lg:flex-row lg:justify-between lg:items-center">
                <div class="flex items-center space-x-4 mb-4 lg:mb-0">
                    <div class="w-16 h-16 bg-white/20 rounded-2xl flex items-center justify-center">
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
                        <h1 class="text-3xl font-bold">Dental Chart Export</h1>
                        <p class="text-blue-100 text-lg">Patient Odontogram Report</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Export Content -->
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Patient Information Section -->
        <div class="bg-white border border-gray-200 rounded-xl shadow-sm mb-8">
            <div class="bg-gray-50 px-6 py-4 border-b border-gray-200 rounded-t-xl">
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
                        <p class="text-lg font-semibold text-blue-600"><?= $patient['patient_id'] ?></p>
                    </div>
                    <div class="space-y-2">
                        <label class="text-sm font-medium text-gray-500">Age</label>
                        <p class="text-lg font-semibold text-gray-900">
                            <?php
                            $dob = new DateTime($patient['date_of_birth']);
                            $now = new DateTime();
                            echo $now->diff($dob)->y . ' years';
                            ?>
                        </p>
                    </div>
                    <div class="space-y-2">
                        <label class="text-sm font-medium text-gray-500">Gender</label>
                        <p class="text-lg font-semibold text-gray-900"><?= ucfirst($patient['gender']) ?></p>
                    </div>
                    <div class="space-y-2">
                        <label class="text-sm font-medium text-gray-500">Phone</label>
                        <p class="text-lg font-semibold text-gray-900"><?= $patient['phone'] ?></p>
                    </div>
                    <div class="space-y-2">
                        <label class="text-sm font-medium text-gray-500">Email</label>
                        <p class="text-lg font-semibold text-gray-900"><?= $patient['email'] ?></p>
                    </div>
                    <div class="space-y-2">
                        <label class="text-sm font-medium text-gray-500">Date of Birth</label>
                        <p class="text-lg font-semibold text-gray-900"><?= formatDate($patient['date_of_birth']) ?></p>
                    </div>
                    <div class="space-y-2">
                        <label class="text-sm font-medium text-gray-500">Export Date</label>
                        <p class="text-lg font-semibold text-gray-900"><?= formatDateTime(date('Y-m-d H:i:s')) ?></p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Dental Statistics Section -->
        <div class="bg-white border border-gray-200 rounded-xl shadow-sm mb-8">
            <div class="bg-gray-50 px-6 py-4 border-b border-gray-200 rounded-t-xl">
                <h2 class="text-xl font-bold text-gray-800 flex items-center">
                    <i class="fas fa-chart-pie mr-3 text-green-600"></i>
                    Dental Statistics
                </h2>
            </div>
            <div class="p-6">
                <div class="grid grid-cols-2 md:grid-cols-4 gap-6">
                    <div class="text-center p-4 bg-green-50 rounded-lg">
                        <div class="text-3xl font-bold text-green-600 mb-2"><?= $stats['total_teeth'] ?? 0 ?></div>
                        <div class="text-sm font-medium text-green-800">Total Teeth</div>
                    </div>
                    <div class="text-center p-4 bg-blue-50 rounded-lg">
                        <div class="text-3xl font-bold text-blue-600 mb-2"><?= $stats['healthy_count'] ?? 0 ?></div>
                        <div class="text-sm font-medium text-blue-800">Healthy</div>
                    </div>
                    <div class="text-center p-4 bg-yellow-50 rounded-lg">
                        <div class="text-3xl font-bold text-yellow-600 mb-2"><?= $stats['cavities'] ?? 0 ?></div>
                        <div class="text-sm font-medium text-yellow-800">Cavities</div>
                    </div>
                    <div class="text-center p-4 bg-purple-50 rounded-lg">
                        <div class="text-3xl font-bold text-purple-600 mb-2"><?= $stats['fillings'] ?? 0 ?></div>
                        <div class="text-sm font-medium text-purple-800">Fillings</div>
                    </div>
                    <div class="text-center p-4 bg-pink-50 rounded-lg">
                        <div class="text-3xl font-bold text-pink-600 mb-2"><?= $stats['crowns'] ?? 0 ?></div>
                        <div class="text-sm font-medium text-pink-800">Crowns</div>
                    </div>
                    <div class="text-center p-4 bg-red-50 rounded-lg">
                        <div class="text-3xl font-bold text-red-600 mb-2"><?= $stats['extracted'] ?? 0 ?></div>
                        <div class="text-sm font-medium text-red-800">Extracted</div>
                    </div>
                    <div class="text-center p-4 bg-orange-50 rounded-lg">
                        <div class="text-3xl font-bold text-orange-600 mb-2"><?= $stats['needs_treatment_count'] ?? 0 ?></div>
                        <div class="text-sm font-medium text-orange-800">Needs Treatment</div>
                    </div>
                    <div class="text-center p-4 bg-indigo-50 rounded-lg">
                        <div class="text-3xl font-bold text-indigo-600 mb-2"><?= $stats['root_canals'] ?? 0 ?></div>
                        <div class="text-sm font-medium text-indigo-800">Root Canals</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Dental Chart Section -->
        <div class="bg-white border border-gray-200 rounded-xl shadow-sm mb-8">
            <div class="bg-gray-50 px-6 py-4 border-b border-gray-200 rounded-t-xl">
                <h2 class="text-xl font-bold text-gray-800 flex items-center">
                    <i class="fas fa-tooth mr-3 text-indigo-600"></i>
                    Dental Chart (Odontogram)
                </h2>
            </div>
            <div class="p-8">
                <!-- Legend -->
                <div class="mb-8">
                    <h3 class="text-lg font-semibold text-gray-800 mb-4">Condition Legend</h3>
                    <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4">
                        <div class="flex items-center space-x-3 p-3 bg-green-50 rounded-lg">
                            <div class="w-6 h-6 bg-green-500 rounded-full"></div>
                            <span class="text-sm font-medium text-gray-800">Healthy</span>
                        </div>
                        <div class="flex items-center space-x-3 p-3 bg-yellow-50 rounded-lg">
                            <div class="w-6 h-6 bg-yellow-500 rounded-full"></div>
                            <span class="text-sm font-medium text-gray-800">Cavity</span>
                        </div>
                        <div class="flex items-center space-x-3 p-3 bg-blue-50 rounded-lg">
                            <div class="w-6 h-6 bg-blue-500 rounded-full"></div>
                            <span class="text-sm font-medium text-gray-800">Filling</span>
                        </div>
                        <div class="flex items-center space-x-3 p-3 bg-purple-50 rounded-lg">
                            <div class="w-6 h-6 bg-purple-500 rounded-full"></div>
                            <span class="text-sm font-medium text-gray-800">Crown</span>
                        </div>
                        <div class="flex items-center space-x-3 p-3 bg-pink-50 rounded-lg">
                            <div class="w-6 h-6 bg-pink-500 rounded-full"></div>
                            <span class="text-sm font-medium text-gray-800">Root Canal</span>
                        </div>
                        <div class="flex items-center space-x-3 p-3 bg-red-50 rounded-lg">
                            <div class="w-6 h-6 bg-red-500 rounded-full"></div>
                            <span class="text-sm font-medium text-gray-800">Extracted</span>
                        </div>
                    </div>
                </div>

                                <!-- Odontogram Grid -->
                <?php
                $svgPath = APPPATH . 'Views/odontogram/odontogram.svg';
                $odontogramSvg = '';
                $conditionColors = [
                    'cavity' => '#fbbf24',
                    'filling' => '#3b82f6',
                    'crown' => '#a855f7',
                    'root_canal' => '#ec4899',
                    'extracted' => '#ef4444',
                    'implant' => '#6366f1',
                    'bridge' => '#0ea5e9',
                    'partial_denture' => '#f97316',
                    'full_denture' => '#f59e0b',
                    'other' => '#6b7280'
                ];

                function injectOdontogramConditions(string $svgContent, array $odontogram, array $conditionColors): string {
                    if ($svgContent === '') {
                        return $svgContent;
                    }

                    $dom = new DOMDocument();
                    $dom->preserveWhiteSpace = true;
                    $dom->formatOutput = false;
                    $previous = libxml_use_internal_errors(true);

                    if (!$dom->loadXML($svgContent)) {
                        libxml_clear_errors();
                        libxml_use_internal_errors($previous);
                        return $svgContent;
                    }

                    libxml_clear_errors();
                    libxml_use_internal_errors($previous);

                    $xpath = new DOMXPath($dom);
                    foreach ($odontogram as $tooth) {
                        $toothNumber = (int)($tooth['tooth_number'] ?? 0);
                        if ($toothNumber <= 0) {
                            continue;
                        }

                        $condition = $tooth['condition_type'] ?? 'healthy';
                        $group = $xpath->query("//*[@id='tooth-{$toothNumber}']")->item(0);
                        if (!$group) {
                            continue;
                        }

                        $overlays = $xpath->query(".//*[contains(concat(' ', normalize-space(@class), ' '), ' tooth-overlay ')]", $group);

                        if ($overlays->length === 0) {
                            $base = null;
                            $directPaths = $xpath->query("./*[local-name()='path']", $group);
                            foreach ($directPaths as $path) {
                                $fill = strtolower(trim($path->getAttribute('fill')));
                                if ($fill !== '' && $fill !== 'none' && $fill !== 'transparent') {
                                    $base = $path;
                                    break;
                                }
                            }

                            if (!$base) {
                                $anyPaths = $xpath->query(".//*[local-name()='path']", $group);
                                foreach ($anyPaths as $path) {
                                    $fill = strtolower(trim($path->getAttribute('fill')));
                                    if ($fill !== '' && $fill !== 'none' && $fill !== 'transparent') {
                                        $base = $path;
                                        break;
                                    }
                                }
                            }

                            if ($base) {
                                $overlay = $base->cloneNode(true);
                                if ($overlay instanceof DOMElement) {
                                    $overlay->removeAttribute('id');
                                    $overlay->setAttribute('class', 'tooth-overlay');
                                    $overlay->setAttribute('fill', 'transparent');
                                    $overlay->setAttribute('opacity', '0');
                                    $group->appendChild($overlay);
                                    $overlays = $xpath->query(".//*[contains(concat(' ', normalize-space(@class), ' '), ' tooth-overlay ')]", $group);
                                }
                            }
                        }

                        if ($overlays->length === 0) {
                            continue;
                        }

                        if (!$condition || $condition === 'healthy') {
                            foreach ($overlays as $overlay) {
                                $overlay->setAttribute('fill', 'transparent');
                                $overlay->setAttribute('opacity', '0');
                            }
                            continue;
                        }

                        $color = $conditionColors[$condition] ?? '#6b7280';
                        foreach ($overlays as $overlay) {
                            $overlay->setAttribute('fill', $color);
                        $overlay->setAttribute('opacity', '0.28');
                    }
                }

                    return $dom->saveXML($dom->documentElement);
                }

                if (is_file($svgPath)) {
                    $svgContent = file_get_contents($svgPath);
                    $svgContent = injectOdontogramConditions($svgContent, $odontogram, $conditionColors);
                    $odontogramSvg = preg_replace('/<\?xml.*?\?>/i', '', $svgContent, 1);
                }
                ?>
                <div id="exportOdontogramWrapper" class="mx-auto w-full max-w-full">
                    <?= $odontogramSvg ?>
                </div>
<!-- Detailed Tooth Conditions -->
        <?php if (!empty($odontogram)): ?>
        <div class="bg-white border border-gray-200 rounded-xl shadow-sm mb-8">
            <div class="bg-gray-50 px-6 py-4 border-b border-gray-200 rounded-t-xl">
                <h2 class="text-xl font-bold text-gray-800 flex items-center">
                    <i class="fas fa-list-alt mr-3 text-purple-600"></i>
                    Detailed Tooth Conditions
                </h2>
            </div>
            <div class="p-6">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tooth</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Condition</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Description</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Treatment Notes</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <?php foreach ($odontogram as $tooth): ?>
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                    Tooth <?= $tooth['tooth_number'] ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
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
                                <td class="px-6 py-4 text-sm text-gray-900">
                                    <?= $tooth['condition_description'] ?: 'No description' ?>
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-900">
                                    <?= $tooth['treatment_notes'] ?: 'No notes' ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                        <?php
                                        switch($tooth['treatment_status']) {
                                            case 'completed':
                                                echo 'bg-green-100 text-green-800';
                                                break;
                                            case 'in_progress':
                                                echo 'bg-yellow-100 text-yellow-800';
                                                break;
                                            case 'needs_attention':
                                                echo 'bg-red-100 text-red-800';
                                                break;
                                            default:
                                                echo 'bg-gray-100 text-gray-800';
                                        }
                                        ?>">
                                        <?= ucfirst(str_replace('_', ' ', $tooth['treatment_status'])) ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    <?= formatDate($tooth['treatment_date']) ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <!-- Footer -->
        <div class="bg-gray-50 border border-gray-200 rounded-xl p-6 text-center">
            <div class="text-sm text-gray-600">
                <p>This report was generated on <?= formatDateTime(date('Y-m-d H:i:s')) ?> by the <?= esc($clinic['name']) ?></p>
                <p class="mt-2">For questions about this report, please contact your dental office</p>
            </div>
        </div>
    </div>
</div>

<script>
window.addEventListener('load', function() {
    setTimeout(function() {
        window.print();
    }, 1000);
});
</script>

<style>
/* Print styles */
@media print {
    .no-print {
        display: none !important;
    }
    
    body {
        background: white !important;
    }
    
    .bg-gradient-to-r {
        background: #1e40af !important;
    }
    
    .shadow-sm, .shadow-lg, .shadow-xl {
        box-shadow: none !important;
    }
    
    .border {
        border: 1px solid #d1d5db !important;
    }
    
    table {
        break-inside: avoid;
    }
    
    .page-break {
        page-break-before: always;
    }

    #exportOdontogramWrapper {
        max-width: 100% !important;
    }

    #exportOdontogramWrapper svg {
        width: 100% !important;
        height: auto !important;
    }
}

/* Responsive design for export */
@media (max-width: 768px) {
    .w-12 {
        width: 2.5rem;
    }
    
    .h-14 {
        height: 3.5rem;
    }
}
</style>

</body>
</html>

