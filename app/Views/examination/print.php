<?= $this->extend('layouts/main_auth') ?>

<?= $this->section('content') ?>
<!-- Print-friendly Examination Report -->
<div class="min-h-screen bg-white">
    <div class="max-w-4xl mx-auto p-8">
        <!-- Print Header -->
        <div class="text-center mb-8 border-b-2 border-gray-300 pb-6">
            <h1 class="text-3xl font-bold text-gray-900 mb-2">Examination Report</h1>
            <p class="text-lg text-gray-600"><?= esc($clinic['name']) ?></p>
            <div class="text-sm text-gray-500 mt-2">
                <p><?= esc($clinic['address']) ?></p>
                <p>Phone: <?= esc($clinic['phone']) ?> | Email: <?= esc($clinic['email']) ?></p>
                <?php if ($clinic['website']): ?>
                <p>Website: <?= esc($clinic['website']) ?></p>
                <?php endif; ?>
                <p class="mt-2">Generated on <?= date('F j, Y \a\t g:i A') ?></p>
            </div>
        </div>

        <!-- Examination Details -->
        <div class="mb-8">
            <h2 class="text-2xl font-bold text-gray-900 mb-4 border-b border-gray-200 pb-2">Examination Information</h2>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1">Examination ID</label>
                        <p class="text-lg font-mono text-gray-900"><?= esc($examination['examination_id']) ?></p>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1">Examination Date</label>
                        <p class="text-lg text-gray-900"><?= date('F j, Y', strtotime($examination['examination_date'])) ?></p>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1">Examination Type</label>
                        <p class="text-lg text-gray-900"><?= ucfirst(str_replace('_', ' ', $examination['examination_type'])) ?></p>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1">Status</label>
                        <p class="text-lg text-gray-900"><?= ucfirst($examination['status']) ?></p>
                    </div>
                </div>
                
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1">Patient Name</label>
                        <p class="text-lg text-gray-900"><?= esc($examination['first_name'] . ' ' . $examination['last_name']) ?></p>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1">Patient ID</label>
                        <p class="text-lg font-mono text-gray-900"><?= esc($examination['patient_number']) ?></p>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1">Created By</label>
                        <p class="text-lg text-gray-900">Dr. <?= $examination['created_by'] ?? 'Unknown' ?></p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Chief Complaint -->
        <?php if (!empty($examination['chief_complaint'])): ?>
        <div class="mb-8">
            <h2 class="text-2xl font-bold text-gray-900 mb-4 border-b border-gray-200 pb-2">Chief Complaint</h2>
            <div class="bg-gray-50 p-4 rounded-lg">
                <p class="text-gray-900 leading-relaxed"><?= nl2br(esc($examination['chief_complaint'])) ?></p>
            </div>
        </div>
        <?php endif; ?>

        <!-- Medical History -->
        <?php if (!empty($examination['medical_history'])): ?>
        <div class="mb-8">
            <h2 class="text-2xl font-bold text-gray-900 mb-4 border-b border-gray-200 pb-2">Medical History</h2>
            <div class="bg-gray-50 p-4 rounded-lg">
                <p class="text-gray-900 leading-relaxed"><?= nl2br(esc($examination['medical_history'])) ?></p>
            </div>
        </div>
        <?php endif; ?>

        <!-- Dental History -->
        <?php if (!empty($examination['dental_history'])): ?>
        <div class="mb-8">
            <h2 class="text-2xl font-bold text-gray-900 mb-4 border-b border-gray-200 pb-2">Dental History</h2>
            <div class="bg-gray-50 p-4 rounded-lg">
                <p class="text-gray-900 leading-relaxed"><?= nl2br(esc($examination['dental_history'])) ?></p>
            </div>
        </div>
        <?php endif; ?>

        <!-- Clinical Findings -->
        <?php if (!empty($examination['clinical_findings'])): ?>
        <div class="mb-8">
            <h2 class="text-2xl font-bold text-gray-900 mb-4 border-b border-gray-200 pb-2">Clinical Findings</h2>
            <div class="bg-gray-50 p-4 rounded-lg">
                <p class="text-gray-900 leading-relaxed"><?= nl2br(esc($examination['clinical_findings'])) ?></p>
            </div>
        </div>
        <?php endif; ?>

        <!-- Diagnosis -->
        <?php if (!empty($examination['diagnosis'])): ?>
        <div class="mb-8">
            <h2 class="text-2xl font-bold text-gray-900 mb-4 border-b border-gray-200 pb-2">Diagnosis</h2>
            <div class="bg-gray-50 p-4 rounded-lg">
                <p class="text-gray-900 leading-relaxed"><?= nl2br(esc($examination['diagnosis'])) ?></p>
            </div>
        </div>
        <?php endif; ?>

        <!-- Treatment Plan -->
        <?php if (!empty($examination['treatment_plan'])): ?>
        <div class="mb-8">
            <h2 class="text-2xl font-bold text-gray-900 mb-4 border-b border-gray-200 pb-2">Treatment Plan</h2>
            <div class="bg-gray-50 p-4 rounded-lg">
                <p class="text-gray-900 leading-relaxed"><?= nl2br(esc($examination['treatment_plan'])) ?></p>
            </div>
        </div>
        <?php endif; ?>

        <!-- Examination Notes -->
        <?php if (!empty($examination['examination_notes'])): ?>
        <div class="mb-8">
            <h2 class="text-2xl font-bold text-gray-900 mb-4 border-b border-gray-200 pb-2">Examination Notes</h2>
            <div class="bg-gray-50 p-4 rounded-lg">
                <p class="text-gray-900 leading-relaxed"><?= nl2br(esc($examination['examination_notes'])) ?></p>
            </div>
        </div>
        <?php endif; ?>

        <!-- Treatments -->
        <?php if (!empty($treatments)): ?>
        <div class="mb-8">
            <h2 class="text-2xl font-bold text-gray-900 mb-4 border-b border-gray-200 pb-2">Associated Treatments</h2>
            <div class="space-y-4">
                <?php foreach ($treatments as $treatment): ?>
                <div class="bg-gray-50 p-4 rounded-lg border-l-4 border-blue-500">
                    <h3 class="font-semibold text-gray-900 mb-2"><?= esc($treatment['treatment_name']) ?></h3>
                    <p class="text-gray-700"><?= nl2br(esc($treatment['description'])) ?></p>
                    <p class="text-sm text-gray-500 mt-2">Status: <?= ucfirst($treatment['status']) ?></p>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>

        <!-- Footer -->
        <div class="mt-12 pt-8 border-t-2 border-gray-300">
            <div class="flex justify-between items-center">
                <div>
                    <p class="text-sm text-gray-600">This report was generated electronically and is valid without signature.</p>
                </div>
                <div class="text-right">
                    <p class="text-sm text-gray-600">Report ID: <?= esc($examination['examination_id']) ?></p>
                    <p class="text-sm text-gray-600">Generated: <?= date('Y-m-d H:i:s') ?></p>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Print Button -->
<div class="fixed bottom-4 right-4 print:hidden">
    <button onclick="window.print()" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-3 rounded-lg shadow-lg transition-colors duration-200 flex items-center space-x-2">
        <i class="fas fa-print"></i>
        <span>Print Report</span>
    </button>
</div>

<!-- Print Styles -->
<style>
@media print {
    .print\\:hidden {
        display: none !important;
    }
    
    body {
        font-size: 12pt;
        line-height: 1.4;
    }
    
    .max-w-4xl {
        max-width: none;
    }
    
    .p-8 {
        padding: 0;
    }
    
    .mb-8 {
        margin-bottom: 1.5rem;
    }
    
    .space-y-4 > * + * {
        margin-top: 0.75rem;
    }
    
    .grid {
        display: block;
    }
    
    .md\\:grid-cols-2 {
        columns: 2;
        column-gap: 2rem;
    }
    
    .bg-gray-50 {
        background-color: #f9fafb !important;
        -webkit-print-color-adjust: exact;
    }
    
    .border-gray-200,
    .border-gray-300 {
        border-color: #d1d5db !important;
    }
}
</style>

<script>
// Auto-print when page loads (optional)
document.addEventListener('DOMContentLoaded', function() {
    // Uncomment the line below to auto-print when the page loads
    // window.print();
});
</script>
<?= $this->endSection() ?>
