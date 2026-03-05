<?= $this->extend('layouts/main_auth') ?>

<?= $this->section('content') ?>
<?php
$tr = static fn(string $key, array $params = []) => lang('Examination.' . $key, $params);
$typeLabels = [
    'initial' => $tr('type_initial'),
    'periodic' => $tr('type_periodic'),
    'emergency' => $tr('type_emergency'),
    'follow_up' => $tr('type_follow_up'),
];
$statusLabels = [
    'pending' => $tr('status_pending'),
    'in_progress' => $tr('status_in_progress'),
    'completed' => $tr('status_completed'),
    'cancelled' => $tr('status_cancelled'),
];
?>
<!-- Print-friendly Examination Report -->
<div class="min-h-screen bg-white">
    <div class="max-w-4xl mx-auto p-8">
        <!-- Print Header -->
        <div class="text-center mb-8 border-b-2 border-gray-300 pb-6">
            <?php if (!empty($clinic['logo_path'])): ?>
                <div class="mb-4 flex justify-center">
                    <?php 
                    $logoSrc = (strpos($clinic['logo_path'], 'http://') === 0 || strpos($clinic['logo_path'], 'https://') === 0) 
                        ? $clinic['logo_path'] 
                        : base_url(ltrim($clinic['logo_path'], '/'));
                    ?>
                    <img src="<?= esc($logoSrc) ?>" alt="<?= esc($clinic['name']) ?>" class="h-20 w-auto object-contain">
                </div>
            <?php endif; ?>
            <h1 class="text-3xl font-bold text-gray-900 mb-2"><?= esc($tr('examination_report')) ?></h1>
            <p class="text-lg text-gray-600"><?= esc($clinic['name']) ?></p>
            <div class="text-sm text-gray-500 mt-2">
                <p><?= esc($clinic['address']) ?></p>
                <p><?= esc($tr('phone_email', ['phone' => $clinic['phone'], 'email' => $clinic['email']])) ?></p>
                <?php if ($clinic['website']): ?>
                <p><?= esc($tr('website', ['website' => $clinic['website']])) ?></p>
                <?php endif; ?>
                <p class="mt-2"><?= esc($tr('generated_on', ['datetime' => formatDateTime(date('Y-m-d H:i:s'))])) ?></p>
            </div>
        </div>

        <!-- Examination Details -->
        <div class="mb-8">
            <h2 class="text-2xl font-bold text-gray-900 mb-4 border-b border-gray-200 pb-2"><?= esc($tr('examination_information')) ?></h2>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1"><?= esc($tr('examination_id_label')) ?></label>
                        <p class="text-lg font-mono text-gray-900"><?= esc($examination['examination_id']) ?></p>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1"><?= esc($tr('examination_date')) ?></label>
                        <p class="text-lg text-gray-900"><?= formatDate($examination['examination_date']) ?></p>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1"><?= esc($tr('detail_examination_type')) ?></label>
                        <p class="text-lg text-gray-900"><?= esc($typeLabels[$examination['examination_type']] ?? $tr('type_follow_up')) ?></p>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1"><?= esc($tr('status')) ?></label>
                        <p class="text-lg text-gray-900"><?= esc($statusLabels[$examination['status']] ?? $tr('status_pending')) ?></p>
                    </div>
                </div>
                
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1"><?= esc($tr('patient_name')) ?></label>
                        <p class="text-lg text-gray-900"><?= esc($examination['first_name'] . ' ' . $examination['last_name']) ?></p>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1"><?= esc($tr('patient_id')) ?></label>
                        <p class="text-lg font-mono text-gray-900"><?= esc($examination['patient_number']) ?></p>
                    </div>
                    
                    <?php $doctorName = trim(($examination['doctor_first_name'] ?? '') . ' ' . ($examination['doctor_last_name'] ?? '')); ?>
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1"><?= esc($tr('created_by')) ?></label>
                        <p class="text-lg text-gray-900"><?= $doctorName !== '' ? 'Dr. ' . esc($doctorName) : esc($tr('unknown')) ?></p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Chief Complaint -->
        <?php if (!empty($examination['chief_complaint'])): ?>
        <div class="mb-8">
            <h2 class="text-2xl font-bold text-gray-900 mb-4 border-b border-gray-200 pb-2"><?= esc($tr('chief_complaint')) ?></h2>
            <div class="bg-gray-50 p-4 rounded-lg">
                <p class="text-gray-900 leading-relaxed"><?= nl2br(esc($examination['chief_complaint'])) ?></p>
            </div>
        </div>
        <?php endif; ?>

        <!-- Medical History -->
        <?php if (!empty($examination['medical_history'])): ?>
        <div class="mb-8">
            <h2 class="text-2xl font-bold text-gray-900 mb-4 border-b border-gray-200 pb-2"><?= esc($tr('medical_history')) ?></h2>
            <div class="bg-gray-50 p-4 rounded-lg">
                <p class="text-gray-900 leading-relaxed"><?= nl2br(esc($examination['medical_history'])) ?></p>
            </div>
        </div>
        <?php endif; ?>

        <!-- Dental History -->
        <?php if (!empty($examination['dental_history'])): ?>
        <div class="mb-8">
            <h2 class="text-2xl font-bold text-gray-900 mb-4 border-b border-gray-200 pb-2"><?= esc($tr('dental_history')) ?></h2>
            <div class="bg-gray-50 p-4 rounded-lg">
                <p class="text-gray-900 leading-relaxed"><?= nl2br(esc($examination['dental_history'])) ?></p>
            </div>
        </div>
        <?php endif; ?>

        <!-- Clinical Findings -->
        <?php if (!empty($examination['clinical_findings'])): ?>
        <div class="mb-8">
            <h2 class="text-2xl font-bold text-gray-900 mb-4 border-b border-gray-200 pb-2"><?= esc($tr('clinical_findings')) ?></h2>
            <div class="bg-gray-50 p-4 rounded-lg">
                <p class="text-gray-900 leading-relaxed"><?= nl2br(esc($examination['clinical_findings'])) ?></p>
            </div>
        </div>
        <?php endif; ?>

        <!-- Diagnosis -->
        <?php if (!empty($examination['diagnosis'])): ?>
        <div class="mb-8">
            <h2 class="text-2xl font-bold text-gray-900 mb-4 border-b border-gray-200 pb-2"><?= esc($tr('diagnosis')) ?></h2>
            <div class="bg-gray-50 p-4 rounded-lg">
                <p class="text-gray-900 leading-relaxed"><?= nl2br(esc($examination['diagnosis'])) ?></p>
            </div>
        </div>
        <?php endif; ?>

        <!-- Treatment Plan (Contract) -->
        <?php if (!empty($latest_contract)): ?>
        <div class="mb-8">
            <h2 class="text-2xl font-bold text-gray-900 mb-4 border-b border-gray-200 pb-2"><?= esc($tr('treatment_plan')) ?></h2>
            <div class="bg-gray-50 p-4 rounded-lg">
                <p class="text-gray-900 leading-relaxed">
                    <?= esc($tr('contract_summary', ['id' => $latest_contract['id'], 'status' => ucfirst($latest_contract['status'] ?? 'draft')])) ?>
                </p>
                <p class="text-gray-700 mt-2">
                    <?= esc($tr('total')) ?> <?= formatCurrency($latest_contract['total_cost'] ?? 0) ?>
                </p>
                <?php if (!empty($latest_contract['created_at'])): ?>
                    <p class="text-sm text-gray-600 mt-2"><?= esc($tr('created')) ?> <?= formatDate($latest_contract['created_at']) ?></p>
                <?php endif; ?>
            </div>
        </div>
        <?php endif; ?>

        <!-- Examination Notes -->
        <?php if (!empty($examination['examination_notes'])): ?>
        <div class="mb-8">
            <h2 class="text-2xl font-bold text-gray-900 mb-4 border-b border-gray-200 pb-2"><?= esc($tr('examination_notes')) ?></h2>
            <div class="bg-gray-50 p-4 rounded-lg">
                <p class="text-gray-900 leading-relaxed"><?= nl2br(esc($examination['examination_notes'])) ?></p>
            </div>
        </div>
        <?php endif; ?>

        <!-- Treatments -->
        <?php if (!empty($treatments)): ?>
        <div class="mb-8">
            <h2 class="text-2xl font-bold text-gray-900 mb-4 border-b border-gray-200 pb-2"><?= esc($tr('associated_treatments')) ?></h2>
            <div class="space-y-4">
                <?php foreach ($treatments as $treatment): ?>
                <div class="bg-gray-50 p-4 rounded-lg border-l-4 border-blue-500">
                    <h3 class="font-semibold text-gray-900 mb-2"><?= esc($treatment['treatment_name']) ?></h3>
                    <p class="text-gray-700"><?= nl2br(esc($treatment['description'])) ?></p>
                    <p class="text-sm text-gray-500 mt-2"><?= esc($tr('status_with_value', ['status' => ucfirst($treatment['status'])])) ?></p>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>

        <!-- Footer -->
        <div class="mt-12 pt-8 border-t-2 border-gray-300">
            <div class="flex justify-between items-center">
                <div>
                    <p class="text-sm text-gray-600"><?= esc($tr('report_footer_note')) ?></p>
                </div>
                <div class="text-right">
                    <p class="text-sm text-gray-600"><?= esc($tr('report_id', ['id' => $examination['examination_id']])) ?></p>
                    <p class="text-sm text-gray-600"><?= esc($tr('generated', ['datetime' => formatDateTime(date('Y-m-d H:i:s'))])) ?></p>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Print Button -->
<div class="fixed bottom-4 right-4 print:hidden">
    <button onclick="window.print()" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-3 rounded-lg shadow-lg transition-colors duration-200 flex items-center space-x-2">
        <i class="fas fa-print"></i>
        <span><?= esc($tr('print_report')) ?></span>
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



