<?= $this->extend('layouts/main_auth') ?>

<?= $this->section('content') ?>
<!-- Enhanced Prescription Print View -->
<div class="min-h-screen bg-white print:bg-white">
    <!-- Print Header - Only visible when printing -->
    <div class="hidden print:block text-center mb-8">
        <?php if (!empty($clinic['logo_path'])): ?>
            <div class="mb-4 flex justify-center">
                <?php 
                    $logoSrc = (strpos($clinic['logo_path'], 'http://') === 0 || strpos($clinic['logo_path'], 'https://') === 0) 
                        ? esc($clinic['logo_path']) 
                        : base_url(ltrim($clinic['logo_path'], '/'));
                ?>
                <img src="<?= $logoSrc ?>" alt="<?= esc($clinic['name']) ?>" class="h-20 w-auto object-contain">
            </div>
        <?php else: ?>
            <h1 class="text-2xl font-bold text-gray-900">PRESCRIPTION</h1>
        <?php endif; ?>
        <div class="text-sm text-gray-600 mt-2">
            <p><?= esc($clinic['name']) ?></p>
            <p><?= esc($clinic['address']) ?></p>
            <p>Phone: <?= esc($clinic['phone']) ?> | Email: <?= esc($clinic['email']) ?></p>
            <?php if ($clinic['website']): ?>
            <p>Website: <?= esc($clinic['website']) ?></p>
            <?php endif; ?>
        </div>
    </div>

    <!-- Main Content -->
    <div class="max-w-4xl mx-auto p-8 print:p-0 print:max-w-none">
        <!-- Prescription Card -->
        <div class="bg-white border-2 border-gray-200 rounded-lg print:border-0 print:rounded-none shadow-lg print:shadow-none overflow-hidden">
            <!-- Header -->
            <div class="bg-gradient-to-r from-teal-500 to-emerald-600 text-white p-6 print:bg-gray-100 print:text-black">
                <div class="flex justify-between items-center">
                    <div>
                        <h2 class="text-2xl font-bold">PRESCRIPTION</h2>
                        <p class="text-teal-100 print:text-gray-600">Prescription ID: <?= $prescription['prescription_id'] ?? 'N/A' ?></p>
                    </div>
                    <div class="text-right">
                        <p class="text-sm">Date: <?= date('M j, Y', strtotime($prescription['prescribed_date'])) ?></p>
                        <p class="text-sm">Expires: <?= date('M j, Y', strtotime($prescription['expiry_date'])) ?></p>
                    </div>
                </div>
            </div>

            <!-- Patient Information -->
            <div class="p-6 border-b border-gray-200">
                <h3 class="text-lg font-bold text-gray-900 mb-4 flex items-center">
                    <i class="fas fa-user text-teal-600 mr-2 print:hidden"></i>
                    Patient Information
                </h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <p class="text-sm text-gray-600">Name:</p>
                        <p class="font-semibold text-gray-900"><?= $prescription['first_name'] . ' ' . $prescription['last_name'] ?></p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-600">Phone:</p>
                        <p class="font-semibold text-gray-900"><?= $prescription['phone'] ?></p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-600">Email:</p>
                        <p class="font-semibold text-gray-900"><?= $prescription['email'] ?? 'N/A' ?></p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-600">Patient ID:</p>
                        <p class="font-semibold text-gray-900"><?= $prescription['patient_id'] ?? 'N/A' ?></p>
                    </div>
                </div>
            </div>

            <!-- Medication Information -->
            <div class="p-6 border-b border-gray-200">
                <h3 class="text-lg font-bold text-gray-900 mb-4 flex items-center">
                    <i class="fas fa-pills text-green-600 mr-2 print:hidden"></i>
                    Medication Information
                </h3>
                <?php
                // Parse medicines from JSON
                $medicines = json_decode($prescription['medication_name'], true);
                if (is_array($medicines) && !empty($medicines)):
                ?>
                    <div class="space-y-4">
                        <?php foreach ($medicines as $index => $medicine): ?>
                            <div class="bg-gray-50 p-4 rounded-lg print:bg-white print:border print:border-gray-300">
                                <div class="flex items-center justify-between mb-3">
                                    <h4 class="text-lg font-bold text-gray-900">Medicine <?= $index + 1 ?></h4>
                                    <span class="text-sm text-gray-600 font-medium"><?= $medicine['name'] ?? 'N/A' ?></span>
                                </div>
                                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                    <div>
                                        <p class="text-sm text-gray-600 font-medium">Dosage:</p>
                                        <p class="text-base font-semibold text-gray-900"><?= $medicine['dosage'] ?? 'N/A' ?></p>
                                    </div>
                                    <div>
                                        <p class="text-sm text-gray-600 font-medium">Frequency:</p>
                                        <p class="text-base font-semibold text-gray-900"><?= $medicine['frequency'] ?? 'N/A' ?></p>
                                    </div>
                                    <div>
                                        <p class="text-sm text-gray-600 font-medium">Duration:</p>
                                        <p class="text-base font-semibold text-gray-900"><?= $medicine['duration'] ?? 'N/A' ?></p>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="bg-gray-50 p-4 rounded-lg print:bg-white print:border print:border-gray-300">
                        <p class="text-gray-600 text-center">No medication information available</p>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Instructions -->
            <div class="p-6 border-b border-gray-200">
                <h3 class="text-lg font-bold text-gray-900 mb-4 flex items-center">
                    <i class="fas fa-sticky-note text-amber-600 mr-2 print:hidden"></i>
                    Instructions
                </h3>
                <div class="bg-gray-50 p-4 rounded-lg print:bg-white print:border print:border-gray-300">
                    <p class="text-gray-800 leading-relaxed whitespace-pre-line"><?= htmlspecialchars($prescription['instructions']) ?></p>
                </div>
            </div>

            <!-- Prescription Details -->
            <div class="p-6">
                <h3 class="text-lg font-bold text-gray-900 mb-4 flex items-center">
                    <i class="fas fa-info-circle text-blue-600 mr-2 print:hidden"></i>
                    Prescription Details
                </h3>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <p class="text-sm text-gray-600">Prescribed Date:</p>
                        <p class="font-semibold text-gray-900"><?= date('M j, Y', strtotime($prescription['prescribed_date'])) ?></p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-600">Expiry Date:</p>
                        <p class="font-semibold text-gray-900"><?= date('M j, Y', strtotime($prescription['expiry_date'])) ?></p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-600">Status:</p>
                        <span class="inline-flex items-center px-3 py-1 text-xs font-bold rounded-full 
                            <?php
                            $statusConfig = [
                                'active' => 'bg-green-100 text-green-800',
                                'expired' => 'bg-amber-100 text-amber-800',
                                'cancelled' => 'bg-gray-100 text-gray-800',
                                'pending' => 'bg-blue-100 text-blue-800'
                            ];
                            echo $statusConfig[$prescription['status']] ?? $statusConfig['pending'];
                            ?>">
                            <?= ucfirst($prescription['status']) ?>
                        </span>
                    </div>
                </div>
            </div>

            <!-- Footer -->
            <div class="bg-gray-50 p-6 print:bg-white print:border-t print:border-gray-300">
                <div class="flex justify-between items-center">
                    <div class="text-sm text-gray-600">
                        <p>Generated on: <?= date('M j, Y g:i A') ?></p>
                        <p>Prescription ID: <?= $prescription['prescription_id'] ?? 'N/A' ?></p>
                    </div>
                    <div class="text-sm text-gray-600 text-right">
                        <p><?= esc($clinic['name']) ?></p>
                        <p><?= esc($clinic['website']) ?></p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Print Actions - Hidden when printing -->
        <div class="mt-8 print:hidden">
            <div class="flex justify-center space-x-4">
                <button onclick="window.print()" class="group/btn relative inline-flex items-center justify-center px-8 py-3 bg-gradient-to-r from-green-500 to-emerald-600 text-white text-sm font-bold rounded-xl shadow-2xl shadow-green-500/25 hover:shadow-green-500/40 transition-all duration-500 hover:scale-105 hover:-translate-y-1">
                    <div class="absolute inset-0 bg-gradient-to-r from-green-600 to-emerald-700 rounded-xl blur opacity-0 group-hover/btn:opacity-100 transition-opacity duration-300"></div>
                    <i class="fas fa-print mr-3 relative z-10 group-hover/btn:scale-110 transition-transform duration-300"></i>
                    <span class="relative z-10">Print Prescription</span>
                </button>
                <a href="<?= base_url('prescription/' . $prescription['id']) ?>" class="group/btn relative inline-flex items-center justify-center px-8 py-3 bg-gradient-to-r from-blue-500 to-cyan-600 text-white text-sm font-bold rounded-xl shadow-2xl shadow-blue-500/25 hover:shadow-blue-500/40 transition-all duration-500 hover:scale-105 hover:-translate-y-1">
                    <div class="absolute inset-0 bg-gradient-to-r from-blue-600 to-cyan-700 rounded-xl blur opacity-0 group-hover/btn:opacity-100 transition-opacity duration-300"></div>
                    <i class="fas fa-arrow-left mr-3 relative z-10 group-hover/btn:scale-110 transition-transform duration-300"></i>
                    <span class="relative z-10">Back to Details</span>
                </a>
                <a href="<?= base_url('prescription') ?>" class="group/btn relative inline-flex items-center justify-center px-8 py-3 bg-gradient-to-r from-gray-500 to-slate-600 text-white text-sm font-bold rounded-xl shadow-2xl shadow-gray-500/25 hover:shadow-gray-500/40 transition-all duration-500 hover:scale-105 hover:-translate-y-1">
                    <div class="absolute inset-0 bg-gradient-to-r from-gray-600 to-slate-700 rounded-xl blur opacity-0 group-hover/btn:opacity-100 transition-opacity duration-300"></div>
                    <i class="fas fa-list mr-3 relative z-10 group-hover/btn:scale-110 transition-transform duration-300"></i>
                    <span class="relative z-10">All Prescriptions</span>
                </a>
            </div>
        </div>
    </div>
</div>

<style>
@media print {
    body {
        margin: 0;
        padding: 0;
        background: white !important;
    }
    
    .print\\:hidden {
        display: none !important;
    }
    
    .print\\:block {
        display: block !important;
    }
    
    .print\\:bg-white {
        background: white !important;
    }
    
    .print\\:text-black {
        color: black !important;
    }
    
    .print\\:border-0 {
        border: 0 !important;
    }
    
    .print\\:rounded-none {
        border-radius: 0 !important;
    }
    
    .print\\:shadow-none {
        box-shadow: none !important;
    }
    
    .print\\:p-0 {
        padding: 0 !important;
    }
    
    .print\\:max-w-none {
        max-width: none !important;
    }
    
    .print\\:border {
        border: 1px solid #d1d5db !important;
    }
    
    .print\\:border-t {
        border-top: 1px solid #d1d5db !important;
    }
    
    .print\\:bg-white {
        background: white !important;
    }
    
    .print\\:text-gray-600 {
        color: #4b5563 !important;
    }
    
    .print\\:bg-gray-100 {
        background: #f3f4f6 !important;
    }
    
    .print\\:border-gray-300 {
        border-color: #d1d5db !important;
    }
    
    /* Ensure proper page breaks */
    .page-break {
        page-break-before: always;
    }
    
    /* Hide navigation and other non-printable elements */
    nav, header, footer, .no-print {
        display: none !important;
    }
}
</style>

<script>
// Auto-print when page loads (optional)
// window.onload = function() {
//     setTimeout(function() {
//         window.print();
//     }, 1000);
// };

// Handle print events
window.addEventListener('beforeprint', function() {
    console.log('Preparing to print prescription...');
});

window.addEventListener('afterprint', function() {
    console.log('Print completed');
});
</script>
<?= $this->endSection() ?>
