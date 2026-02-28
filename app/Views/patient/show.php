<?= $this->extend('layouts/main_auth') ?>

<?php
// Function to convert Universal numbering to FDI numbering
function getFDINumber($universalNumber) {
    $fdiMap = [
        // Upper Right (1-8) -> FDI 11-18
        1 => '11', 2 => '12', 3 => '13', 4 => '14', 5 => '15', 6 => '16', 7 => '17', 8 => '18',
        // Upper Left (9-16) -> FDI 21-28  
        9 => '21', 10 => '22', 11 => '23', 12 => '24', 13 => '25', 14 => '26', 15 => '27', 16 => '28',
        // Lower Left (17-24) -> FDI 31-38
        17 => '31', 18 => '32', 19 => '33', 20 => '34', 21 => '35', 22 => '36', 23 => '37', 24 => '38',
        // Lower Right (25-32) -> FDI 41-48
        25 => '41', 26 => '42', 27 => '43', 28 => '44', 29 => '45', 30 => '46', 31 => '47', 32 => '48'
    ];
    
    return $fdiMap[$universalNumber] ?? $universalNumber;
}
?>

<?= $this->section('content') ?>
<div class="container patient-view mx-auto px-6 py-8">
    <div class="space-y-6">
        <!-- Page Header -->
        <div class="flex flex-col md:flex-row md:justify-between md:items-center space-y-4 md:space-y-0">
            <div>
                <h1 class="text-3xl font-bold text-gray-800">Patient Details</h1>
                <p class="text-gray-600 mt-1">Complete information for <?= $patient['first_name'] . ' ' . $patient['last_name'] ?></p>
            </div>
            <div class="flex space-x-3">
                <a href="<?= !empty($return_to) ? base_url($return_to) : base_url('patient') ?>" 
                   class="inline-flex items-center px-4 py-2 bg-gray-100 text-gray-700 text-sm font-medium rounded-md hover:bg-gray-200 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition-colors">
                    <i class="fas fa-arrow-left mr-2"></i>Back to Patients
                </a>
                <?php if (has_permission('patients', 'edit')): ?>
                    <a href="<?= base_url('patient/' . $patient['id'] . '/edit' . (!empty($return_to) ? '?return_to=' . urlencode($return_to) : '')) ?>" 
                       class="inline-flex items-center px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition-colors">
                        <i class="fas fa-edit mr-2"></i>Edit Patient
                    </a>
                <?php endif; ?>
            </div>
        </div>

        <div class="bg-white rounded-lg border border-gray-200 shadow-sm">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-semibold text-gray-800">Patient Statistics</h3>
            </div>
            <div class="px-6 py-4 grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4">
                <div class="rounded-lg border border-gray-200 bg-gray-50 p-3">
                    <p class="text-xs uppercase tracking-wide text-gray-500">Examinations</p>
                    <p class="mt-1 text-xl font-bold text-gray-900"><?= count($examinations) ?></p>
                </div>
                <div class="rounded-lg border border-gray-200 bg-gray-50 p-3">
                    <p class="text-xs uppercase tracking-wide text-gray-500">Appointments</p>
                    <p class="mt-1 text-xl font-bold text-gray-900"><?= count($appointments) ?></p>
                </div>
                <div class="rounded-lg border border-gray-200 bg-gray-50 p-3">
                    <p class="text-xs uppercase tracking-wide text-gray-500">Treatments</p>
                    <p class="mt-1 text-xl font-bold text-gray-900"><?= count($treatments) ?></p>
                </div>
                <div class="rounded-lg border border-gray-200 bg-gray-50 p-3">
                    <p class="text-xs uppercase tracking-wide text-gray-500">Prescriptions</p>
                    <p class="mt-1 text-xl font-bold text-gray-900"><?= count($prescriptions) ?></p>
                </div>
                <div class="rounded-lg border border-gray-200 bg-gray-50 p-3">
                    <p class="text-xs uppercase tracking-wide text-gray-500">Invoices</p>
                    <p class="mt-1 text-xl font-bold text-gray-900"><?= count($finances) ?></p>
                </div>
                <div class="rounded-lg border border-gray-200 bg-gray-50 p-3">
                    <p class="text-xs uppercase tracking-wide text-gray-500">Member Since</p>
                    <p class="mt-1 text-sm font-semibold text-gray-900"><?= formatDate($patient['created_at']) ?></p>
                </div>
            </div>
        </div>

        <!-- Main Content Layout -->
        <div class="w-full">
            <!-- Patient History Tabs -->
            <div class="w-full">
                <div class="bg-white rounded-lg border border-gray-200 shadow-sm">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h3 class="text-lg font-semibold text-gray-800">Patient History</h3>
                    </div>
                    
                    <!-- Tab Navigation -->
                    <div class="px-6 py-4 border-b border-gray-200">
                        <div class="flex flex-col space-y-4">
                            <nav class="flex flex-wrap gap-2" aria-label="Tabs">
                                <button type="button" onclick="return showTab('details', event)" 
                                        id="details-tab" 
                                        class="tab-button active whitespace-nowrap py-2 px-3 font-medium text-sm">
                                    <i class="fas fa-user mr-2"></i>Details
                                </button>
                                <button type="button" onclick="return showTab('timeline', event)" 
                                        id="timeline-tab" 
                                        class="tab-button whitespace-nowrap py-2 px-3 font-medium text-sm">
                                    <i class="fas fa-history mr-2"></i>Timeline
                                </button>
                                <button type="button" onclick="return showTab('examinations', event)" 
                                        id="examinations-tab" 
                                        class="tab-button whitespace-nowrap py-2 px-3 font-medium text-sm">
                                    <i class="fas fa-stethoscope mr-2"></i>Exams (<?= count($examinations) ?>)
                                </button>
                                <button type="button" onclick="return showTab('appointments', event)" 
                                        id="appointments-tab" 
                                        class="tab-button whitespace-nowrap py-2 px-3 font-medium text-sm">
                                    <i class="fas fa-calendar mr-2"></i>Appts (<?= count($appointments) ?>)
                                </button>
                                <button type="button" onclick="return showTab('treatments', event)" 
                                        id="treatments-tab" 
                                        class="tab-button whitespace-nowrap py-2 px-3 font-medium text-sm">
                                    <i class="fas fa-tooth mr-2"></i>Treatments (<?= count($treatments) ?>)
                                </button>
                                <button type="button" onclick="return showTab('prescriptions', event)" 
                                        id="prescriptions-tab" 
                                        class="tab-button whitespace-nowrap py-2 px-3 font-medium text-sm">
                                    <i class="fas fa-prescription mr-2"></i>Rx (<?= count($prescriptions) ?>)
                                </button>
                                <?php if (!empty($attachments_enabled)): ?>
                                    <button type="button" onclick="return showTab('attachments', event)" 
                                            id="attachments-tab" 
                                            class="tab-button whitespace-nowrap py-2 px-3 font-medium text-sm">
                                        <i class="fas fa-paperclip mr-2"></i>Attachments (<?= count($attachments ?? []) ?>)
                                    </button>
                                <?php endif; ?>
                                <?php if (!empty($contracts_enabled)): ?>
                                    <button type="button" onclick="return showTab('contracts', event)" 
                                            id="contracts-tab" 
                                            class="tab-button whitespace-nowrap py-2 px-3 font-medium text-sm">
                                        <i class="fas fa-file-signature mr-2"></i>Contracts (<?= count($contracts ?? []) ?>)
                                    </button>
                                <?php endif; ?>
                                <button type="button" onclick="return showTab('finances', event)" 
                                        id="finances-tab" 
                                        class="tab-button whitespace-nowrap py-2 px-3 font-medium text-sm">
                                    <i class="fas fa-file-invoice mr-2"></i>Invoice (<?= count($finances) ?>)
                                </button>
                            </nav>
                            
                            <!-- Search and Filter Controls -->
                            <div class="flex flex-col sm:flex-row items-start sm:items-center space-y-2 sm:space-y-0 sm:space-x-4">
                                <div class="relative w-full sm:w-auto">
                                    <input type="text" 
                                           id="historySearch" 
                                           placeholder="Search history..." 
                                           class="pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm w-full sm:w-64">
                                    <i class="fas fa-search absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                                </div>
                                <div class="flex items-center space-x-2">
                                    <input type="text" data-clinic-date="1"
                                           id="startDate" 
                                           class="px-3 py-2 border border-gray-300 rounded-lg text-sm">
                                    <input type="text" data-clinic-date="1"
                                           id="endDate" 
                                           class="px-3 py-2 border border-gray-300 rounded-lg text-sm">
                                </div>
                                <div class="flex items-center space-x-2">
                                    <button onclick="clearFilters()" 
                                            class="px-3 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 text-sm">
                                        <i class="fas fa-times mr-1"></i>Clear
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Tab Content -->
                    <div class="px-6 py-4">
                        <!-- Patient Details Tab -->
                        <div id="details-content" class="tab-content">
                            <div class="space-y-6">
                                <!-- Personal Details -->
                                <div class="bg-white rounded-lg border border-gray-200 shadow-sm group hover:shadow-lg transition-all duration-300">
                                    <div class="px-6 py-4 border-b border-gray-200">
                                        <h3 class="text-lg font-semibold text-gray-800">Personal Information</h3>
                                    </div>
                                    <div class="px-6 py-4">
                                        <?php $creatorName = trim(($patient['creator_first_name'] ?? '') . ' ' . ($patient['creator_last_name'] ?? '')); ?>
                                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                            <div>
                                                <label class="block text-sm font-medium text-gray-600 mb-1">Patient ID</label>
                                                <p class="text-sm text-gray-900 font-semibold"><?= $patient['patient_id'] ?></p>
                                            </div>
                                            <div>
                                                <label class="block text-sm font-medium text-gray-600 mb-1">Full Name</label>
                                                <p class="text-sm text-gray-900"><?= $patient['first_name'] . ' ' . $patient['last_name'] ?></p>
                                            </div>
                                            <div>
                                                <label class="block text-sm font-medium text-gray-600 mb-1">Date of Birth</label>
                                                <p class="text-sm text-gray-900"><?= formatDate($patient['date_of_birth']) ?></p>
                                            </div>
                                            <div>
                                                <label class="block text-sm font-medium text-gray-600 mb-1">Age</label>
                                                <p class="text-sm text-gray-900">
                                                    <?php
                                                    $dob = new DateTime($patient['date_of_birth']);
                                                    $now = new DateTime();
                                                    $age = $now->diff($dob)->y;
                                                    echo $age . ' years';
                                                    ?>
                                                </p>
                                            </div>
                                            <div>
                                                <label class="block text-sm font-medium text-gray-600 mb-1">Gender</label>
                                                <p class="text-sm text-gray-900"><?= ucfirst($patient['gender']) ?></p>
                                            </div>
                                            <div>
                                                <label class="block text-sm font-medium text-gray-600 mb-1">Status</label>
                                                <span class="status-badge <?= $patient['status'] == 'active' ? 'status-active' : 
                                                    ($patient['status'] == 'inactive' ? 'status-inactive' : 'status-pending') ?>">
                                                    <?= ucfirst($patient['status']) ?>
                                                </span>
                                            </div>
                                            <div>
                                                <label class="block text-sm font-medium text-gray-600 mb-1">Created By</label>
                                                <p class="text-sm text-gray-900"><?= $creatorName !== '' ? 'Dr. ' . esc($creatorName) : 'Unknown' ?></p>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Contact Information -->
                                <div class="bg-white rounded-lg border border-gray-200 shadow-sm group hover:shadow-lg transition-all duration-300">
                                    <div class="px-6 py-4 border-b border-gray-200">
                                        <h3 class="text-lg font-semibold text-gray-800">Contact Information</h3>
                                    </div>
                                    <div class="px-6 py-4">
                                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                            <div>
                                                <label class="block text-sm font-medium text-gray-600 mb-1">Phone</label>
                                                <p class="text-sm text-gray-900"><?= $patient['phone'] ?></p>
                                            </div>
                                            <div>
                                                <label class="block text-sm font-medium text-gray-600 mb-1">Email</label>
                                                <p class="text-sm text-gray-900"><?= $patient['email'] ?: 'Not provided' ?></p>
                                            </div>
                                            <div class="md:col-span-2">
                                                <label class="block text-sm font-medium text-gray-600 mb-1">City</label>
                                                <p class="text-sm text-gray-900"><?= !empty($patient['city']) ? esc($patient['city']) : 'Not provided' ?></p>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <?= view('patient/partials/whatsapp_identity', [
                                    'patient' => $patient,
                                    'patient_whatsapp' => $patient_whatsapp ?? null,
                                    'patient_whatsapp_optout' => $patient_whatsapp_optout ?? null,
                                ]) ?>

                                <!-- Medical Information -->
                                <?php
                                $medicalAlerts = [
                                    [
                                        'enabled' => !empty($patient['alert_has_allergies'] ?? 0),
                                        'icon' => 'fas fa-allergies',
                                        'icon_class' => 'text-red-600',
                                        'label' => 'Allergies',
                                        'details' => $patient['alert_allergies_details'] ?? null,
                                    ],
                                    [
                                        'enabled' => !empty($patient['alert_taking_medications'] ?? 0),
                                        'icon' => 'fas fa-pills',
                                        'icon_class' => 'text-blue-600',
                                        'label' => 'Taking medications',
                                        'details' => $patient['alert_medications_details'] ?? null,
                                    ],
                                    [
                                        'enabled' => !empty($patient['alert_blood_thinners'] ?? 0),
                                        'icon' => 'fas fa-tint',
                                        'icon_class' => 'text-rose-600',
                                        'label' => 'Blood thinners',
                                        'details' => $patient['alert_blood_thinners_details'] ?? null,
                                    ],
                                    [
                                        'enabled' => !empty($patient['alert_diabetes'] ?? 0),
                                        'icon' => 'fas fa-syringe',
                                        'icon_class' => 'text-amber-600',
                                        'label' => 'Diabetes',
                                        'details' => $patient['alert_diabetes_details'] ?? null,
                                    ],
                                    [
                                        'enabled' => !empty($patient['alert_high_blood_pressure'] ?? 0),
                                        'icon' => 'fas fa-heartbeat',
                                        'icon_class' => 'text-pink-600',
                                        'label' => 'High blood pressure',
                                        'details' => $patient['alert_blood_pressure_details'] ?? null,
                                    ],
                                    [
                                        'enabled' => !empty($patient['alert_heart_condition'] ?? 0),
                                        'icon' => 'fas fa-heart',
                                        'icon_class' => 'text-red-700',
                                        'label' => 'Heart condition / surgery history',
                                        'details' => $patient['alert_heart_condition_details'] ?? null,
                                    ],
                                    [
                                        'enabled' => !empty($patient['alert_pregnant'] ?? 0),
                                        'icon' => 'fas fa-baby',
                                        'icon_class' => 'text-fuchsia-600',
                                        'label' => 'Pregnant / possibly pregnant',
                                        'details' => $patient['alert_pregnancy_details'] ?? null,
                                    ],
                                    [
                                        'enabled' => !empty($patient['alert_anesthesia_problem'] ?? 0),
                                        'icon' => 'fas fa-mask-face',
                                        'icon_class' => 'text-indigo-600',
                                        'label' => 'Anesthesia / sedation issue',
                                        'details' => $patient['alert_anesthesia_problem_details'] ?? null,
                                    ],
                                ];
                                $hasStructuredMedicalAlerts = false;
                                foreach ($medicalAlerts as $medicalAlertRow) {
                                    if (!empty($medicalAlertRow['enabled'])) {
                                        $hasStructuredMedicalAlerts = true;
                                        break;
                                    }
                                }
                                ?>
                                <?php if ($hasStructuredMedicalAlerts || $patient['medical_history'] || $patient['allergies']): ?>
                                    <div class="bg-white rounded-lg border border-gray-200 shadow-sm group hover:shadow-lg transition-all duration-300">
                                        <div class="px-6 py-4 border-b border-gray-200">
                                            <h3 class="text-lg font-semibold text-gray-800">Medical Information</h3>
                                        </div>
                                        <div class="px-6 py-4">
                                            <div class="space-y-4">
                                                <?php if ($hasStructuredMedicalAlerts): ?>
                                                    <div>
                                                        <label class="block text-sm font-medium text-gray-600 mb-2">Medical Alerts (Quick Check)</label>
                                                        <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                                                            <?php foreach ($medicalAlerts as $medicalAlertRow): ?>
                                                                <?php if (empty($medicalAlertRow['enabled'])) continue; ?>
                                                                <div class="rounded-lg border border-amber-200 bg-amber-50 px-3 py-2">
                                                                    <div class="flex items-start justify-between gap-2">
                                                                        <span class="text-sm font-semibold text-amber-900 inline-flex items-start gap-2">
                                                                            <i class="<?= esc($medicalAlertRow['icon']) ?> <?= esc($medicalAlertRow['icon_class'] ?? 'text-amber-700') ?> mt-0.5 w-4 text-center"></i>
                                                                            <span><?= esc($medicalAlertRow['label']) ?></span>
                                                                        </span>
                                                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-bold bg-amber-200 text-amber-900">Yes</span>
                                                                    </div>
                                                                    <?php if (!empty($medicalAlertRow['details'])): ?>
                                                                        <p class="text-xs text-amber-800 mt-1"><?= nl2br(esc($medicalAlertRow['details'])) ?></p>
                                                                    <?php endif; ?>
                                                                </div>
                                                            <?php endforeach; ?>
                                                        </div>
                                                    </div>
                                                <?php endif; ?>
                                                <?php if ($patient['medical_history']): ?>
                                                    <div>
                                                        <label class="block text-sm font-medium text-gray-600 mb-1"><?= $hasStructuredMedicalAlerts ? 'Legacy Medical History' : 'Medical History' ?></label>
                                                        <p class="text-sm text-gray-900"><?= nl2br(htmlspecialchars($patient['medical_history'])) ?></p>
                                                    </div>
                                                <?php endif; ?>
                                                <?php if ($patient['allergies']): ?>
                                                    <div>
                                                        <label class="block text-sm font-medium text-gray-600 mb-1"><?= $hasStructuredMedicalAlerts ? 'Legacy Allergies' : 'Allergies' ?></label>
                                                        <p class="text-sm text-gray-900"><?= nl2br(htmlspecialchars($patient['allergies'])) ?></p>
                                                    </div>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </div>
                                <?php endif; ?>

                                <!-- Notes -->
                                <?php if ($patient['notes']): ?>
                                    <div class="bg-white rounded-lg border border-gray-200 shadow-sm group hover:shadow-lg transition-all duration-300">
                                        <div class="px-6 py-4 border-b border-gray-200">
                                            <h3 class="text-lg font-semibold text-gray-800">Notes</h3>
                                        </div>
                                        <div class="px-6 py-4">
                                            <p class="text-sm text-gray-900"><?= nl2br(htmlspecialchars($patient['notes'])) ?></p>
                                        </div>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>

                        <!-- Timeline Tab -->
                        <div id="timeline-content" class="tab-content hidden">
                            <div class="flow-root">
                                <ul class="-mb-8">
                                    <?php 
                                    // Combine all records into a timeline
                                    $timeline = [];
                                    
                                    // Add examinations
                                    foreach ($examinations as $exam) {
                                        $doctorName = trim(($exam['doctor_first_name'] ?? '') . ' ' . ($exam['doctor_last_name'] ?? ''));
                                        $timeline[] = [
                                            'type' => 'examination',
                                            'date' => $exam['examination_date'],
                                            'title' => 'Examination - ' . ($exam['examination_type'] ?? 'General'),
                                            'description' => $exam['chief_complaint'] ?? 'No complaint recorded',
                                            'status' => $exam['status'],
                                            'id' => $exam['id'],
                                            'doctor' => $doctorName ?: null,
                                            'icon' => 'fas fa-stethoscope',
                                            'color' => 'blue'
                                        ];
                                    }
                                    
                                    // Add appointments
                                    foreach ($appointments as $appt) {
                                        $doctorName = trim(($appt['doctor_first_name'] ?? '') . ' ' . ($appt['doctor_last_name'] ?? ''));
                                        $timeline[] = [
                                            'type' => 'appointment',
                                            'date' => $appt['appointment_date'],
                                            'title' => 'Appointment - ' . ($appt['appointment_type'] ?? 'General'),
                                            'description' => $appt['notes'] ?? 'No notes',
                                            'status' => $appt['status'],
                                            'id' => $appt['id'],
                                            'doctor' => $doctorName ?: null,
                                            'icon' => 'fas fa-calendar',
                                            'color' => 'green'
                                        ];
                                    }
                                    
                                    // Add treatments
                                    foreach ($treatments as $treatment) {
                                        $doctorName = trim(($treatment['doctor_first_name'] ?? '') . ' ' . ($treatment['doctor_last_name'] ?? ''));
                                        $timeline[] = [
                                            'type' => 'treatment',
                                            'date' => $treatment['start_date'],
                                            'title' => 'Treatment - ' . ($treatment['treatment_name'] ?? 'General'),
                                            'description' => $treatment['treatment_description'] ?? 'No description',
                                            'status' => $treatment['status'],
                                            'id' => $treatment['id'],
                                            'doctor' => $doctorName ?: null,
                                            'icon' => 'fas fa-tooth',
                                            'color' => 'purple'
                                        ];
                                    }
                                    
                                    // Add prescriptions
                                    foreach ($prescriptions as $prescription) {
                                        // Parse medication JSON data
                                        $medicationName = 'Unknown';
                                        try {
                                            $medicines = json_decode($prescription['medication_name'], true);
                                            if (is_array($medicines) && !empty($medicines)) {
                                                $medicineNames = [];
                                                foreach ($medicines as $medicine) {
                                                    if (isset($medicine['name'])) {
                                                        $medicineNames[] = $medicine['name'];
                                                    }
                                                }
                                                $medicationName = implode(', ', $medicineNames);
                                            } else {
                                                $medicationName = $prescription['medication_name'] ?? 'Unknown';
                                            }
                                        } catch (Exception $e) {
                                            $medicationName = $prescription['medication_name'] ?? 'Unknown';
                                        }
                                        
                                        $timeline[] = [
                                            'type' => 'prescription',
                                            'date' => $prescription['prescribed_date'],
                                            'title' => 'Prescription - ' . $medicationName,
                                            'description' => $prescription['instructions'] ?? 'No instructions',
                                            'status' => $prescription['status'],
                                            'id' => $prescription['id'],
                                            'icon' => 'fas fa-prescription',
                                            'color' => 'orange'
                                        ];
                                    }
                                    
                                    // Add financial transactions
                                    foreach ($finances as $finance) {
                                        $timeline[] = [
                                            'type' => 'finance',
                                            'date' => $finance['created_at'],
                                            'title' => 'Payment - ' . ($finance['transaction_type'] ?? 'Unknown'),
                                            'description' => $finance['description'] ?? 'No description',
                                            'status' => $finance['payment_status'],
                                            'id' => $finance['id'],
                                            'icon' => 'fas fa-dollar-sign',
                                            'color' => 'green'
                                        ];
                                    }
                                    
                                    // Sort by date
                                    usort($timeline, function($a, $b) {
                                        return strtotime($b['date']) - strtotime($a['date']);
                                    });
                                    
                                    foreach ($timeline as $index => $item): 
                                    ?>
                                    <li>
                                        <div class="relative pb-8">
                                            <?php if ($index !== count($timeline) - 1): ?>
                                                <span class="absolute top-4 left-4 -ml-px h-full w-0.5 bg-gray-200" aria-hidden="true"></span>
                                            <?php endif; ?>
                                            <div class="relative flex space-x-3">
                                                <div>
                                                    <span class="h-8 w-8 rounded-full bg-<?= $item['color'] ?>-500 flex items-center justify-center ring-8 ring-white">
                                                        <i class="<?= $item['icon'] ?> text-white text-xs"></i>
                                                    </span>
                                                </div>
                                                <div class="min-w-0 flex-1 pt-1.5 flex justify-between space-x-4">
                                                    <div>
                                                        <p class="text-sm text-gray-500">
                                                            <span class="font-medium text-gray-900"><?= $item['title'] ?></span>
                                                            <span class="text-gray-500">on <?= formatDate($item['date']) ?></span>
                                                        </p>
                                                        <p class="text-sm text-gray-500"><?= $item['description'] ?></p>
                                                        <?php if (!empty($item['doctor'])): ?>
                                                            <p class="text-xs text-gray-400 mt-1">Dr. <?= esc($item['doctor']) ?></p>
                                                        <?php endif; ?>
                                                    </div>
                                                    <div class="text-right text-sm whitespace-nowrap text-gray-500">
                                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-<?= $item['color'] ?>-100 text-<?= $item['color'] ?>-800">
                                                            <?= ucfirst($item['status']) ?>
                                                        </span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </li>
                                    <?php endforeach; ?>
                                    
                                    <?php if (empty($timeline)): ?>
                                        <li>
                                            <div class="text-center py-8">
                                                <i class="fas fa-history text-gray-400 text-4xl mb-4"></i>
                                                <p class="text-gray-500">No history records found for this patient.</p>
                                            </div>
                                        </li>
                                    <?php endif; ?>
                                </ul>
                            </div>
                        </div>

                        <?php if (!empty($attachments_enabled)): ?>
                            <!-- Attachments Tab -->
                            <div id="attachments-content" class="tab-content hidden">
                                <?php
                                    $usageBytes = (int) ($attachments_storage_usage_bytes ?? 0);
                                    $limitGb = (int) ($attachments_storage_limit_gb ?? 0);
                                    $limitBytes = $limitGb > 0 ? $limitGb * 1024 * 1024 * 1024 : 0;
                                    $usagePercent = $limitBytes > 0 ? round(($usageBytes / $limitBytes) * 100) : 0;
                                    $maxFileMb = $attachments_max_file_mb ?? 5;
                                ?>

                                <?php if (session('success')): ?>
                                    <div class="mb-4 rounded-lg border border-emerald-200 bg-emerald-50 p-4 text-emerald-700 text-sm">
                                        <?= esc(session('success')) ?>
                                    </div>
                                <?php endif; ?>
                                <?php if (session('error')): ?>
                                    <div class="mb-4 rounded-lg border border-rose-200 bg-rose-50 p-4 text-rose-700 text-sm">
                                        <?= esc(session('error')) ?>
                                    </div>
                                <?php endif; ?>

                                <?php if ($limitBytes > 0 && $usagePercent >= 80): ?>
                                    <div class="mb-6 rounded-lg border border-amber-200 bg-amber-50 p-4 text-amber-800 text-sm">
                                        <strong>Storage warning:</strong> <?= $usagePercent ?>% of your plan storage is used.
                                    </div>
                                <?php endif; ?>

                                <div class="bg-white rounded-lg border border-gray-200 shadow-sm group hover:shadow-lg transition-all duration-300 mb-6">
                                    <div class="px-6 py-4 border-b border-gray-200 flex flex-col md:flex-row md:items-center md:justify-between gap-3">
                                        <div>
                                            <h3 class="text-lg font-semibold text-gray-800">Patient Attachments</h3>
                                            <p class="text-xs text-gray-500 mt-1">PDF, images, scans, and documents (max <?= $maxFileMb === null ? 'unlimited' : (int) $maxFileMb . 'MB' ?>)</p>
                                        </div>
                                    </div>
                                    <div class="px-6 py-4">
                                        <form action="<?= base_url('patient/' . $patient['id'] . '/attachments/upload') ?>" method="post" enctype="multipart/form-data" class="space-y-4">
                                            <?= csrf_field() ?>
                                            <div class="space-y-4">
                                                <div id="attachmentDropzone" tabindex="0" role="button"
                                                     class="w-full cursor-pointer rounded-lg border-2 border-dashed border-blue-200 bg-blue-50/40 px-4 py-6 text-center transition-all duration-200">
                                                    <div class="text-sm text-gray-600">
                                                        Drag & drop files here, or click to browse
                                                    </div>
                                                    <div class="mt-2 text-xs text-gray-500">You can select multiple files</div>
                                                    <div class="mt-4 inline-flex items-center gap-2 rounded-md border border-blue-200 bg-white px-3 py-2 text-xs font-semibold text-blue-700">
                                                        <i class="fas fa-folder-open"></i>
                                                        Choose Files
                                                    </div>
                                                </div>

                                                <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 items-start">
                                                    <div>
                                                        <label class="block text-sm font-medium text-gray-700 mb-2">Upload Files</label>
                                                        <input type="file" name="attachment[]" id="attachmentInput" required multiple
                                                               accept=".pdf,.jpg,.jpeg,.png,.docx,.xlsx,.zip"
                                                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                                        <div id="attachmentFileList" class="mt-3 space-y-2 text-sm text-gray-600"></div>
                                                    </div>
                                                    <div>
                                                        <label class="block text-sm font-medium text-gray-700 mb-2">Category</label>
                                                        <input type="text" name="category" placeholder="Scan, X-Ray, Lab..."
                                                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                                    </div>
                                                </div>

                                                <div>
                                                    <label class="block text-sm font-medium text-gray-700 mb-2">Notes</label>
                                                    <textarea name="notes" rows="4" placeholder="Optional notes for this file"
                                                              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"></textarea>
                                                </div>
                                            </div>
                                            <div class="flex items-center justify-between">
                                                <p class="text-xs text-gray-500">Allowed: PDF, JPG, PNG, DOCX, XLSX, ZIP</p>
                                                <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors duration-200">
                                                    <i class="fas fa-upload mr-2"></i>Upload
                                                </button>
                                            </div>
                                        </form>
                                    </div>
                                </div>

                                <div class="bg-white rounded-lg border border-gray-200 shadow-sm">
                                    <div class="px-6 py-4 border-b border-gray-200">
                                        <h4 class="text-md font-semibold text-gray-800">Attachment History</h4>
                                    </div>
                                    <div class="overflow-x-auto">
                                        <table class="min-w-full divide-y divide-gray-200">
                                            <thead class="bg-gray-50">
                                                <tr>
                                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">File</th>
                                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Category</th>
                                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Notes</th>
                                                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                                                </tr>
                                            </thead>
                                            <tbody class="bg-white divide-y divide-gray-200">
                                                <?php if (!empty($attachments)): ?>
                                                    <?php foreach ($attachments as $attachment): ?>
                                                        <tr data-date="<?= !empty($attachment['created_at']) ? esc(date('Y-m-d', strtotime($attachment['created_at']))) : '' ?>">
                                                            <td class="px-6 py-4 text-sm text-gray-500 whitespace-nowrap">
                                                                <?= !empty($attachment['created_at']) ? formatDateTime($attachment['created_at']) : '--' ?>
                                                            </td>
                                                            <td class="px-6 py-4 text-sm text-gray-900">
                                                                <div class="font-medium"><?= esc($attachment['original_name'] ?? 'Attachment') ?></div>
                                                                <div class="text-xs text-gray-500"><?= number_format(($attachment['file_size'] ?? 0) / 1024, 1) ?> KB</div>
                                                            </td>
                                                            <td class="px-6 py-4 text-sm text-gray-500">
                                                                <?= esc($attachment['category'] ?? '—') ?>
                                                            </td>
                                                            <td class="px-6 py-4 text-sm text-gray-500">
                                                                <?= esc($attachment['notes'] ?? '—') ?>
                                                            </td>
                                                            <td class="px-6 py-4 text-right text-sm">
                                                                <a href="<?= base_url('file/download/' . $attachment['id']) ?>" 
                                                                   class="inline-flex items-center text-blue-600 hover:text-blue-800 mr-3">
                                                                    <i class="fas fa-download mr-1"></i>Download
                                                                </a>
                                                                <form action="<?= base_url('patient/' . $patient['id'] . '/attachments/' . $attachment['id'] . '/delete') ?>" method="post" class="inline">
                                                                    <?= csrf_field() ?>
                                                                    <button type="submit" class="inline-flex items-center text-rose-600 hover:text-rose-800" onclick="return confirm('Delete this attachment?');">
                                                                        <i class="fas fa-trash mr-1"></i>Delete
                                                                    </button>
                                                                </form>
                                                            </td>
                                                        </tr>
                                                    <?php endforeach; ?>
                                                <?php else: ?>
                                                    <tr>
                                                        <td colspan="5" class="px-6 py-8 text-center text-sm text-gray-500">No attachments uploaded yet.</td>
                                                    </tr>
                                                <?php endif; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        <?php endif; ?>

                        <?php if (!empty($contracts_enabled)): ?>
                            <!-- Contracts Tab -->
                            <div id="contracts-content" class="tab-content hidden">
                                <?php if (session('success')): ?>
                                    <div class="mb-4 rounded-lg border border-emerald-200 bg-emerald-50 p-4 text-emerald-700 text-sm">
                                        <?= esc(session('success')) ?>
                                    </div>
                                <?php endif; ?>
                                <?php if (session('error')): ?>
                                    <div class="mb-4 rounded-lg border border-rose-200 bg-rose-50 p-4 text-rose-700 text-sm">
                                        <?= esc(session('error')) ?>
                                    </div>
                                <?php endif; ?>

                                <div class="bg-white rounded-lg border border-gray-200 shadow-sm group hover:shadow-lg transition-all duration-300 mb-6">
                                    <div class="px-6 py-4 border-b border-gray-200 flex flex-col md:flex-row md:items-center md:justify-between gap-3">
                                        <div>
                                            <h3 class="text-lg font-semibold text-gray-800">Treatment Contracts</h3>
                                            <p class="text-xs text-gray-500 mt-1">Create and sign treatment agreements with a snapshot odontogram.</p>
                                        </div>
                                        <p class="text-xs text-gray-500 mt-1">Create treatment plans from an examination.</p>
                                    </div>
                                </div>

                                <div class="bg-white rounded-lg border border-gray-200 shadow-sm">
                                    <div class="px-6 py-4 border-b border-gray-200">
                                        <h4 class="text-md font-semibold text-gray-800">Contract History</h4>
                                    </div>
                                    <div class="overflow-x-auto">
                                        <table class="min-w-full divide-y divide-gray-200">
                                            <thead class="bg-gray-50">
                                                <tr>
                                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Exam</th>
                                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total</th>
                                                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                                                </tr>
                                            </thead>
                                            <tbody class="bg-white divide-y divide-gray-200">
                                                <?php if (!empty($contracts)): ?>
                                                    <?php foreach ($contracts as $contract): ?>
                                                        <tr data-date="<?= !empty($contract['created_at']) ? esc(date('Y-m-d', strtotime($contract['created_at']))) : '' ?>">
                                                            <td class="px-6 py-4 text-sm text-gray-500 whitespace-nowrap">
                                                                <?= !empty($contract['created_at']) ? formatDateTime($contract['created_at']) : '--' ?>
                                                            </td>
                                                            <td class="px-6 py-4 text-sm text-gray-700">
                                                                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-semibold <?= ($contract['status'] ?? 'draft') === 'signed' ? 'bg-emerald-100 text-emerald-700' : 'bg-amber-100 text-amber-700' ?>">
                                                                    <?= esc(ucfirst($contract['status'] ?? 'draft')) ?>
                                                                </span>
                                                            </td>
                                                            <td class="px-6 py-4 text-sm text-gray-700">
                                                                <?= !empty($contract['examination_id']) ? esc($contract['examination_id']) : '--' ?>
                                                            </td>
                                                            <td class="px-6 py-4 text-sm text-gray-700">
                                                                <?= esc($contract['currency'] ?? 'USD') ?> <?= number_format((float) ($contract['total_cost'] ?? 0), 2) ?>
                                                            </td>
                                                    <td class="px-6 py-4 text-right text-sm">
                                                        <a href="<?= base_url('contracts/' . $contract['id']) ?>" 
                                                           class="inline-flex items-center text-blue-600 hover:text-blue-800 mr-3">
                                                            <i class="fas fa-eye mr-1"></i>View
                                                        </a>
                                                        <?php if (($contract['status'] ?? 'draft') === 'draft'): ?>
                                                            <a href="<?= base_url('contracts/' . $contract['id'] . '/edit') ?>" 
                                                               class="inline-flex items-center text-indigo-600 hover:text-indigo-800 mr-3">
                                                                <i class="fas fa-edit mr-1"></i>Edit
                                                            </a>
                                                            <form action="<?= base_url('contracts/' . $contract['id'] . '/delete') ?>" method="post" class="inline">
                                                                <?= csrf_field() ?>
                                                                <button type="submit" class="inline-flex items-center text-rose-600 hover:text-rose-800 mr-3" onclick="return confirm('Delete this contract?');">
                                                                    <i class="fas fa-trash mr-1"></i>Delete
                                                                </button>
                                                            </form>
                                                        <?php endif; ?>
                                                        <a href="<?= base_url('contracts/' . $contract['id'] . '/print') ?>" 
                                                           class="inline-flex items-center text-gray-600 hover:text-gray-800">
                                                            <i class="fas fa-print mr-1"></i>Print
                                                        </a>
                                                    </td>
                                                        </tr>
                                                    <?php endforeach; ?>
                                                <?php else: ?>
                                                    <tr>
                                                        <td colspan="5" class="px-6 py-8 text-center text-sm text-gray-500">No contracts created yet.</td>
                                                    </tr>
                                                <?php endif; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        <?php endif; ?>

                        <!-- Examinations Tab -->
                        <div id="examinations-content" class="tab-content hidden">
                            <div class="bg-white rounded-lg border border-gray-200 shadow-sm group hover:shadow-lg transition-all duration-300 mb-6">
                                <div class="px-6 py-4 border-b border-gray-200 flex flex-col md:flex-row md:items-center md:justify-between gap-3">
                                    <div>
                                        <h3 class="text-lg font-semibold text-gray-800">Examinations</h3>
                                        <p class="text-xs text-gray-500 mt-1">Clinical examinations for this patient.</p>
                                    </div>
                                    <?php if (has_permission('examinations', 'create')): ?>
                                        <a href="<?= base_url('examination/create?patient_id=' . $patient['id'] . '&return_to=patient/' . $patient['id']) ?>"
                                           class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors duration-200">
                                            <i class="fas fa-plus mr-2"></i>Create Examination
                                        </a>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <?php if (!empty($examinations)): ?>
                                <div class="overflow-x-auto">
                                    <table class="min-w-full divide-y divide-gray-200">
                                        <thead class="bg-gray-50">
                                            <tr>
                                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Type</th>
                                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Chief Complaint</th>
                                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody class="bg-white divide-y divide-gray-200">
                                            <?php foreach ($examinations as $examination): ?>
                                                <tr class="hover:bg-gray-50">
                                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                        <?= formatDate($examination['examination_date']) ?>
                                                    </td>
                                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                        <?= ucfirst($examination['examination_type'] ?? 'General') ?>
                                                    </td>
                                                    <td class="px-6 py-4 text-sm text-gray-900">
                                                        <?= $examination['chief_complaint'] ? substr($examination['chief_complaint'], 0, 50) . '...' : 'No complaint' ?>
                                                    </td>
                                                    <td class="px-6 py-4 whitespace-nowrap">
                                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium <?= $examination['status'] == 'completed' ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800' ?>">
                                                            <?= ucfirst($examination['status']) ?>
                                                        </span>
                                                    </td>
                                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                                        <a href="<?= base_url('examination/' . $examination['id'] . '?return_to=patient/' . $patient['id']) ?>" 
                                                           class="text-blue-600 hover:text-blue-900">View</a>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            <?php else: ?>
                                <div class="text-center py-8">
                                    <i class="fas fa-stethoscope text-gray-400 text-4xl mb-4"></i>
                                    <p class="text-gray-500">No examinations found for this patient.</p>
                                </div>
                            <?php endif; ?>
                        </div>

                        <!-- Appointments Tab -->
                        <div id="appointments-content" class="tab-content hidden">
                            <div class="bg-white rounded-lg border border-gray-200 shadow-sm group hover:shadow-lg transition-all duration-300 mb-6">
                                <div class="px-6 py-4 border-b border-gray-200 flex flex-col md:flex-row md:items-center md:justify-between gap-3">
                                    <div>
                                        <h3 class="text-lg font-semibold text-gray-800">Appointments</h3>
                                        <p class="text-xs text-gray-500 mt-1">Scheduled visits and appointment history.</p>
                                    </div>
                                    <?php if (has_permission('appointments', 'create')): ?>
                                        <a href="<?= base_url('appointment/create?patient_id=' . $patient['id'] . '&return_to=patient/' . $patient['id']) ?>"
                                           class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors duration-200">
                                            <i class="fas fa-plus mr-2"></i>Schedule Appointment
                                        </a>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <?php if (!empty($appointments)): ?>
                                <div class="overflow-x-auto">
                                    <table class="min-w-full divide-y divide-gray-200">
                                        <thead class="bg-gray-50">
                                            <tr>
                                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date & Time</th>
                                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Type</th>
                                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Duration</th>
                                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody class="bg-white divide-y divide-gray-200">
                                            <?php foreach ($appointments as $appointment): ?>
                                                <tr class="hover:bg-gray-50">
                                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                        <?= formatDate($appointment['appointment_date']) ?> at <?= formatTime($appointment['appointment_time']) ?>
                                                    </td>
                                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                        <?= ucfirst($appointment['appointment_type'] ?? 'General') ?>
                                                    </td>
                                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                        <?= $appointment['duration'] ?? 'N/A' ?> minutes
                                                    </td>
                                                    <td class="px-6 py-4 whitespace-nowrap">
                                                        <?php
                                                        $statusColors = [
                                                            'scheduled' => 'bg-blue-100 text-blue-800',
                                                            'confirmed' => 'bg-green-100 text-green-800',
                                                            'completed' => 'bg-gray-100 text-gray-800',
                                                            'cancelled' => 'bg-red-100 text-red-800',
                                                            'no_show' => 'bg-yellow-100 text-yellow-800'
                                                        ];
                                                        $colorClass = $statusColors[$appointment['status']] ?? 'bg-gray-100 text-gray-800';
                                                        ?>
                                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium <?= $colorClass ?>">
                                                            <?= ucfirst($appointment['status']) ?>
                                                        </span>
                                                    </td>
                                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                                        <a href="<?= base_url('appointment/' . $appointment['id'] . '?return_to=patient/' . $patient['id']) ?>" 
                                                           class="text-blue-600 hover:text-blue-900">View</a>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            <?php else: ?>
                                <div class="text-center py-8">
                                    <i class="fas fa-calendar text-gray-400 text-4xl mb-4"></i>
                                    <p class="text-gray-500">No appointments found for this patient.</p>
                                </div>
                            <?php endif; ?>
                        </div>

                        <!-- Treatments Tab -->
                        <div id="treatments-content" class="tab-content hidden">
                            <div class="bg-white rounded-lg border border-gray-200 shadow-sm group hover:shadow-lg transition-all duration-300 mb-6">
                                <div class="px-6 py-4 border-b border-gray-200 flex flex-col md:flex-row md:items-center md:justify-between gap-3">
                                    <div>
                                        <h3 class="text-lg font-semibold text-gray-800">Treatments</h3>
                                        <p class="text-xs text-gray-500 mt-1">Procedures and treatment plans for this patient.</p>
                                    </div>
                                    <?php if (has_permission('treatments', 'create')): ?>
                                        <a href="<?= base_url('treatment/create?patient_id=' . $patient['id'] . '&return_to=patient/' . $patient['id']) ?>"
                                           class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors duration-200">
                                            <i class="fas fa-plus mr-2"></i>Add Treatment
                                        </a>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <?php if (!empty($treatments)): ?>
                                <div class="overflow-x-auto">
                                    <table class="min-w-full divide-y divide-gray-200">
                                        <thead class="bg-gray-50">
                                            <tr>
                                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Treatment</th>
                                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Type</th>
                                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tooth</th>
                                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Cost</th>
                                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody class="bg-white divide-y divide-gray-200">
                                            <?php foreach ($treatments as $treatment): ?>
                                                <tr class="hover:bg-gray-50">
                                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                        <?= $treatment['treatment_name'] ?? 'N/A' ?>
                                                    </td>
                                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                        <?= ucfirst($treatment['treatment_type'] ?? 'General') ?>
                                                    </td>
                                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                        <?= $treatment['tooth_number'] ?? 'N/A' ?>
                                                    </td>
                                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                        $<?= number_format($treatment['cost'] ?? 0, 2) ?>
                                                    </td>
                                                    <td class="px-6 py-4 whitespace-nowrap">
                                                        <?php
                                                        $statusColors = [
                                                            'active' => 'bg-blue-100 text-blue-800',
                                                            'completed' => 'bg-green-100 text-green-800',
                                                            'cancelled' => 'bg-red-100 text-red-800',
                                                            'on_hold' => 'bg-yellow-100 text-yellow-800'
                                                        ];
                                                        $colorClass = $statusColors[$treatment['status']] ?? 'bg-gray-100 text-gray-800';
                                                        ?>
                                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium <?= $colorClass ?>">
                                                            <?= ucfirst($treatment['status']) ?>
                                                        </span>
                                                    </td>
                                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                                        <a href="<?= base_url('treatment/' . $treatment['id'] . '?return_to=patient/' . $patient['id']) ?>" 
                                                           class="text-blue-600 hover:text-blue-900">View</a>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            <?php else: ?>
                                <div class="text-center py-8">
                                    <i class="fas fa-tooth text-gray-400 text-4xl mb-4"></i>
                                    <p class="text-gray-500">No treatments found for this patient.</p>
                                </div>
                            <?php endif; ?>
                        </div>

                        <!-- Prescriptions Tab -->
                        <div id="prescriptions-content" class="tab-content hidden">
                            <div class="bg-white rounded-lg border border-gray-200 shadow-sm group hover:shadow-lg transition-all duration-300 mb-6">
                                <div class="px-6 py-4 border-b border-gray-200 flex flex-col md:flex-row md:items-center md:justify-between gap-3">
                                    <div>
                                        <h3 class="text-lg font-semibold text-gray-800">Prescriptions</h3>
                                        <p class="text-xs text-gray-500 mt-1">Medication plans for this patient.</p>
                                    </div>
                                    <?php if (has_permission('prescriptions', 'create')): ?>
                                        <a href="<?= base_url('prescription/create?patient_id=' . $patient['id'] . '&return_to=patient/' . $patient['id']) ?>"
                                           class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors duration-200">
                                            <i class="fas fa-plus mr-2"></i>Create Prescription
                                        </a>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <?php if (!empty($prescriptions)): ?>
                                <div class="overflow-x-auto">
                                    <table class="min-w-full divide-y divide-gray-200">
                                        <thead class="bg-gray-50">
                                            <tr>
                                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Medication</th>
                                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Dosage</th>
                                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Frequency</th>
                                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Prescribed Date</th>
                                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody class="bg-white divide-y divide-gray-200">
                                            <?php foreach ($prescriptions as $prescription): ?>
                                                <?php
                                                // Parse medication JSON data
                                                $medicines = [];
                                                $medicationName = 'N/A';
                                                $dosage = 'N/A';
                                                $frequency = 'N/A';
                                                
                                                try {
                                                    $medicinesData = json_decode($prescription['medication_name'], true);
                                                    if (is_array($medicinesData) && !empty($medicinesData)) {
                                                        $medicines = $medicinesData;
                                                        $medicineNames = [];
                                                        $dosages = [];
                                                        $frequencies = [];
                                                        
                                                        foreach ($medicinesData as $medicine) {
                                                            if (isset($medicine['name'])) {
                                                                $medicineNames[] = $medicine['name'];
                                                            }
                                                            if (isset($medicine['dosage'])) {
                                                                $dosages[] = $medicine['dosage'];
                                                            }
                                                            if (isset($medicine['frequency'])) {
                                                                $frequencies[] = $medicine['frequency'];
                                                            }
                                                        }
                                                        
                                                        $medicationName = implode(', ', $medicineNames);
                                                        $dosage = implode(', ', $dosages);
                                                        $frequency = implode(', ', $frequencies);
                                                    } else {
                                                        $medicationName = $prescription['medication_name'] ?? 'N/A';
                                                        $dosage = $prescription['dosage'] ?? 'N/A';
                                                        $frequency = $prescription['frequency'] ?? 'N/A';
                                                    }
                                                } catch (Exception $e) {
                                                    $medicationName = $prescription['medication_name'] ?? 'N/A';
                                                    $dosage = $prescription['dosage'] ?? 'N/A';
                                                    $frequency = $prescription['frequency'] ?? 'N/A';
                                                }
                                                ?>
                                                <tr class="hover:bg-gray-50">
                                                    <td class="px-6 py-4 text-sm text-gray-900">
                                                        <div class="flex items-center space-x-2">
                                                            <i class="fas fa-pills text-teal-600 text-xs"></i>
                                                            <span><?= htmlspecialchars($medicationName) ?></span>
                                                        </div>
                                                    </td>
                                                    <td class="px-6 py-4 text-sm text-gray-900">
                                                        <?= htmlspecialchars($dosage) ?>
                                                    </td>
                                                    <td class="px-6 py-4 text-sm text-gray-900">
                                                        <?= htmlspecialchars($frequency) ?>
                                                    </td>
                                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                        <?= formatDate($prescription['prescribed_date']) ?>
                                                    </td>
                                                    <td class="px-6 py-4 whitespace-nowrap">
                                                        <?php
                                                        $statusColors = [
                                                            'active' => 'bg-green-100 text-green-800',
                                                            'expired' => 'bg-red-100 text-red-800',
                                                            'cancelled' => 'bg-gray-100 text-gray-800',
                                                            'pending' => 'bg-yellow-100 text-yellow-800'
                                                        ];
                                                        $colorClass = $statusColors[$prescription['status']] ?? 'bg-gray-100 text-gray-800';
                                                        ?>
                                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium <?= $colorClass ?>">
                                                            <?= ucfirst($prescription['status']) ?>
                                                        </span>
                                                    </td>
                                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                                        <a href="<?= base_url('prescription/' . $prescription['id'] . '?return_to=patient/' . $patient['id']) ?>" 
                                                           class="text-blue-600 hover:text-blue-900">View</a>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            <?php else: ?>
                                <div class="text-center py-8">
                                    <i class="fas fa-prescription text-gray-400 text-4xl mb-4"></i>
                                    <p class="text-gray-500">No prescriptions found for this patient.</p>
                                </div>
                            <?php endif; ?>
                        </div>

                        <!-- Invoice Tab -->
                        <div id="finances-content" class="tab-content hidden">
                            <div class="bg-white rounded-lg border border-gray-200 shadow-sm group hover:shadow-lg transition-all duration-300 mb-6">
                                <div class="px-6 py-4 border-b border-gray-200 flex flex-col md:flex-row md:items-center md:justify-between gap-3">
                                    <div>
                                        <h3 class="text-lg font-semibold text-gray-800">Invoices</h3>
                                        <p class="text-xs text-gray-500 mt-1">Financial records and invoices for this patient.</p>
                                    </div>
                                    <?php if (has_permission('finance', 'create')): ?>
                                        <a href="<?= base_url('finance/create?patient_id=' . $patient['id'] . '&return_to=patient/' . $patient['id']) ?>"
                                           class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors duration-200">
                                            <i class="fas fa-plus mr-2"></i>Create Invoice
                                        </a>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <?php if (!empty($finances)): ?>
                                <div class="overflow-x-auto">
                                    <table class="min-w-full divide-y divide-gray-200">
                                        <thead class="bg-gray-50">
                                            <tr>
                                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Type</th>
                                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Payment Method</th>
                                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total</th>
                                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody class="bg-white divide-y divide-gray-200">
                                            <?php foreach ($finances as $finance): ?>
                                                <tr class="hover:bg-gray-50">
                                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                        <?= formatDate($finance['created_at']) ?>
                                                    </td>
                                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                        <?= ucfirst($finance['transaction_type'] ?? 'Unknown') ?>
                                                    </td>
                                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                        <?= ucfirst($finance['payment_method'] ?? 'N/A') ?>
                                                    </td>
                                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                        <?php
                                                            $totalAmount = $finance['total_amount'] ?? ($finance['amount'] - ($finance['discount_amount'] ?? 0) + ($finance['tax_amount'] ?? 0));
                                                        ?>
                                                        <?= formatCurrency($totalAmount) ?>
                                                    </td>
                                                    <td class="px-6 py-4 whitespace-nowrap">
                                                        <?php
                                                        $statusColors = [
                                                            'paid' => 'bg-green-100 text-green-800',
                                                            'pending' => 'bg-yellow-100 text-yellow-800',
                                                            'partial' => 'bg-blue-100 text-blue-800',
                                                            'overdue' => 'bg-red-100 text-red-800',
                                                            'cancelled' => 'bg-gray-100 text-gray-800'
                                                        ];
                                                        $colorClass = $statusColors[$finance['payment_status']] ?? 'bg-gray-100 text-gray-800';
                                                        ?>
                                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium <?= $colorClass ?>">
                                                            <?= ucfirst($finance['payment_status']) ?>
                                                        </span>
                                                    </td>
                                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                                        <a href="<?= base_url('finance/' . $finance['id'] . '?return_to=patient/' . $patient['id']) ?>" 
                                                           class="text-blue-600 hover:text-blue-900">View</a>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            <?php else: ?>
                                <div class="text-center py-8">
                                    <i class="fas fa-file-invoice text-gray-400 text-4xl mb-4"></i>
                                    <p class="text-gray-500">No invoices found for this patient.</p>
                                </div>
                            <?php endif; ?>
                        </div>

                        <!-- Tooth Modal -->
                        <div id="toothModal" class="fixed inset-0 bg-black/50 backdrop-blur-sm hidden z-50 transition-opacity duration-300">
                            <div class="flex items-center justify-center min-h-screen p-4">
                                <div class="bg-white/95 backdrop-blur-md rounded-2xl shadow-2xl max-w-lg w-full transform transition-all duration-300 scale-95 opacity-0" id="modalContent">
                                    <!-- Enhanced Modal Header -->
                                    <div class="bg-gradient-to-r from-blue-600 via-indigo-600 to-purple-600 text-white p-6 rounded-t-2xl relative overflow-hidden">
                                        <div class="absolute inset-0 bg-black/10"></div>
                                        <div class="relative z-10">
                                            <div class="flex items-center justify-between">
                                                <div class="flex items-center space-x-4">
                                                    <div class="w-12 h-12 bg-white/20 rounded-xl flex items-center justify-center backdrop-blur-sm">
                                                        <i class="fas fa-tooth text-white text-xl"></i>
                                                    </div>
                                                    <div>
                                                        <h3 class="text-xl font-bold">Tooth <span id="modalToothNumber" class="text-yellow-300"></span> Condition</h3>
                                                        <p class="text-blue-100 text-sm">Update dental condition and treatment notes</p>
                                                    </div>
                                                </div>
                                                <button onclick="closeToothModal()" class="w-8 h-8 bg-white/20 hover:bg-white/30 rounded-lg flex items-center justify-center transition-colors duration-200">
                                                    <i class="fas fa-times text-white"></i>
                                                </button>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Modal Body -->
                                    <div class="p-6">
                                        <form id="toothForm" onsubmit="updateTooth(event)">
                                            <input type="hidden" id="toothNumber" name="tooth_number">
                                            <input type="hidden" name="patient_id" value="<?= $patient['id'] ?>">
                                            
                                            <div class="space-y-6">
                                                <!-- Condition Type -->
                                                <div>
                                                    <label for="condition_type" class="block text-sm font-medium text-gray-700 mb-2">Condition Type</label>
                                                    <select id="condition_type" name="condition_type" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                                        <option value="healthy">Healthy</option>
                                                        <option value="cavity">Cavity</option>
                                                        <option value="filling">Filling</option>
                                                        <option value="crown">Crown</option>
                                                        <option value="root_canal">Root Canal</option>
                                                        <option value="extracted">Extracted</option>
                                                        <option value="implant">Implant</option>
                                                        <option value="bridge">Bridge</option>
                                                        <option value="partial_denture">Partial Denture</option>
                                                        <option value="full_denture">Full Denture</option>
                                                        <option value="other">Other</option>
                                                    </select>
                                                </div>

                                                <!-- Condition Description -->
                                                <div>
                                                    <label for="condition_description" class="block text-sm font-medium text-gray-700 mb-2">Condition Description</label>
                                                    <textarea id="condition_description" name="condition_description" rows="3" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500" placeholder="Describe the condition..."></textarea>
                                                </div>

                                                <!-- Treatment Notes -->
                                                <div>
                                                    <label for="treatment_notes" class="block text-sm font-medium text-gray-700 mb-2">Treatment Notes</label>
                                                    <textarea id="treatment_notes" name="treatment_notes" rows="3" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500" placeholder="Add treatment notes..."></textarea>
                                                </div>

                                                <!-- Treatment Status -->
                                                <div>
                                                    <label for="treatment_status" class="block text-sm font-medium text-gray-700 mb-2">Treatment Status</label>
                                                    <select id="treatment_status" name="treatment_status" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                                        <option value="pending">Pending</option>
                                                        <option value="in_progress">In Progress</option>
                                                        <option value="completed">Completed</option>
                                                        <option value="needs_attention">Needs Attention</option>
                                                    </select>
                                                </div>
                                            </div>

                                            <!-- Modal Footer -->
                                            <div class="flex items-center justify-between mt-8 pt-6 border-t border-gray-200">
                                                <button type="button" onclick="resetTooth()" class="px-4 py-2 bg-red-100 text-red-700 rounded-lg hover:bg-red-200 transition-colors duration-200">
                                                    <i class="fas fa-undo mr-2"></i>Reset to Healthy
                                                </button>
                                                <div class="flex space-x-3">
                                                    <button type="button" onclick="closeToothModal()" class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition-colors duration-200">
                                                        Cancel
                                                    </button>
                                                    <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors duration-200">
                                                        <i class="fas fa-save mr-2"></i>Save Changes
                                                    </button>
                                                </div>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>                    </div>
                </div> 
           </div>


    </div>
</div>


<style>
    .patient-view {
        position: relative;
    }

    .patient-view::before {
        content: "";
        position: absolute;
        inset: 0;
        background: radial-gradient(900px 500px at 8% -12%, rgba(37, 99, 235, 0.12), transparent 60%),
                    radial-gradient(700px 420px at 92% 0%, rgba(14, 165, 233, 0.12), transparent 55%);
        pointer-events: none;
        z-index: 0;
    }

    .patient-view > .space-y-6 {
        position: relative;
        z-index: 1;
    }

    .patient-view .bg-white.rounded-lg.border.border-gray-200.shadow-sm {
        border-color: #d3dfef;
        background: linear-gradient(180deg, #ffffff 0%, #f4f9ff 100%);
        box-shadow: 0 16px 32px -24px rgba(15, 23, 42, 0.52), 0 1px 0 rgba(15, 23, 42, 0.07);
    }

    .patient-view .flex.flex-col.md\:flex-row.md\:justify-between.md\:items-center.space-y-4.md\:space-y-0 {
        border: 1px solid #ccdcf0;
        border-radius: 0.85rem;
        padding: 1rem 1.15rem;
        background: linear-gradient(90deg, rgba(255, 255, 255, 0.98) 0%, rgba(227, 240, 255, 0.96) 100%);
        box-shadow: 0 16px 30px -24px rgba(15, 23, 42, 0.45);
    }

    .patient-view .inline-flex.items-center.px-4.py-2.bg-blue-600.text-white.text-sm.font-medium.rounded-md {
        background: linear-gradient(90deg, #1d4ed8 0%, #0284c7 100%);
        border: 1px solid rgba(37, 99, 235, 0.35);
        box-shadow: 0 10px 22px -12px rgba(29, 78, 216, 0.62);
    }

    .patient-view .inline-flex.items-center.px-4.py-2.bg-gray-100.text-gray-700.text-sm.font-medium.rounded-md {
        background: linear-gradient(180deg, #f8fafc 0%, #eef2f7 100%);
        border: 1px solid #d2dbe7;
    }

    .patient-view .bg-white.rounded-lg.border.border-gray-200.shadow-sm > .px-6.py-4.border-b.border-gray-200 {
        background: linear-gradient(90deg, rgba(248, 250, 252, 0.98) 0%, rgba(227, 240, 255, 0.98) 100%);
        border-bottom-color: #dbe5f0;
        position: relative;
    }

    .patient-view .bg-white.rounded-lg.border.border-gray-200.shadow-sm > .px-6.py-4.border-b.border-gray-200::before {
        content: "";
        position: absolute;
        left: 0;
        top: 0;
        bottom: 0;
        width: 4px;
        border-radius: 0 4px 4px 0;
        background: linear-gradient(180deg, #2563eb 0%, #0891b2 100%);
        opacity: 1;
    }

    .patient-view .rounded-lg.border.border-gray-200.bg-gray-50.p-3 {
        border-color: #cdddf2;
        background: linear-gradient(180deg, #f6faff 0%, #e9f3ff 100%);
        box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.8);
        position: relative;
        overflow: hidden;
    }

    .patient-view .rounded-lg.border.border-gray-200.bg-gray-50.p-3::after {
        content: "";
        position: absolute;
        right: -22px;
        top: -22px;
        width: 56px;
        height: 56px;
        border-radius: 9999px;
        background: radial-gradient(circle, rgba(37, 99, 235, 0.22) 0%, rgba(37, 99, 235, 0) 72%);
        pointer-events: none;
    }

    .patient-view .rounded-lg.border.border-gray-200.bg-gray-50.p-3 .text-xl.font-bold.text-gray-900,
    .patient-view .rounded-lg.border.border-gray-200.bg-gray-50.p-3 .text-sm.font-semibold.text-gray-900 {
        color: #0b3a75;
    }

    .patient-view .bg-white.rounded-lg.border.border-gray-200.shadow-sm.group {
        transition: transform .22s ease, box-shadow .22s ease;
    }

    .patient-view .bg-white.rounded-lg.border.border-gray-200.shadow-sm.group:hover {
        transform: translateY(-1px);
        box-shadow: 0 18px 34px -24px rgba(15, 23, 42, 0.5), 0 1px 0 rgba(15, 23, 42, 0.08);
    }

    .patient-view .tab-content .text-sm.text-gray-900 {
        color: #0f172a;
    }

    .patient-view .tab-content label.text-sm {
        color: #475569;
        font-weight: 600;
    }

    .patient-view .px-6.py-4.border-b.border-gray-200 + .px-6.py-4 {
        background: linear-gradient(180deg, rgba(255, 255, 255, 1) 0%, rgba(250, 252, 255, 1) 100%);
    }

    .patient-view nav[aria-label="Tabs"] {
        background: linear-gradient(180deg, rgba(244, 249, 255, 0.95) 0%, rgba(234, 243, 252, 0.9) 100%);
        border: 1px solid #d9e6f7;
        border-radius: 0.8rem;
        padding: 0.5rem;
    }

    .tab-button {
        border: 1px solid #e5e7eb;
        background: linear-gradient(180deg, #f8fafc 0%, #f1f5f9 100%);
        color: #475569;
        border-radius: 0.5rem;
        transition: all 0.2s ease;
    }

    .tab-button.active,
    .tab-button.active:hover {
        border-color: #1d4ed8;
        background: linear-gradient(180deg, #eff6ff 0%, #dbeafe 100%);
        color: #1e40af;
        box-shadow: 0 2px 6px rgba(29, 78, 216, 0.18);
    }

    .tab-button:hover:not(.active) {
        color: #1f2937;
        border-color: #d1d5db;
        background: #f3f4f6;
    }

    .tab-content {
        display: block;
    }

    .tab-content.hidden {
        display: none;
    }

</style>

<script>
    // Global variables for filtering
    let currentTab = 'details';
    let allTimelineData = [];
    let filteredTimelineData = [];

    // Initialize timeline data
    document.addEventListener('DOMContentLoaded', function() {
        // Store all timeline data for filtering
        allTimelineData = [
            <?php foreach ($timeline as $item): ?>
            {
                type: '<?= $item['type'] ?>',
                date: '<?= $item['date'] ?>',
                title: '<?= addslashes($item['title']) ?>',
                description: '<?= addslashes($item['description']) ?>',
                status: '<?= $item['status'] ?>',
                id: '<?= $item['id'] ?>',
                doctor: '<?= addslashes((string) ($item['doctor'] ?? '')) ?>',
                icon: '<?= $item['icon'] ?>',
                color: '<?= $item['color'] ?>'
            },
            <?php endforeach; ?>
        ];
        filteredTimelineData = [...allTimelineData];
        
        // Set default date range (last 6 months)
        const endDate = new Date();
        const startDate = new Date();
        startDate.setMonth(startDate.getMonth() - 6);
        
        document.getElementById('endDate').value = endDate.toISOString().split('T')[0];
        document.getElementById('startDate').value = startDate.toISOString().split('T')[0];
        
        // Add event listeners
        document.getElementById('historySearch').addEventListener('input', filterHistory);
        document.getElementById('startDate').addEventListener('change', filterHistory);
        document.getElementById('endDate').addEventListener('change', filterHistory);

        const shouldOpenAttachments = <?= json_encode((bool) session('attachments_flash')) ?>;
        if (shouldOpenAttachments) {
            const attachmentsTab = document.getElementById('attachments-tab');
            const attachmentsContent = document.getElementById('attachments-content');
            if (attachmentsTab && attachmentsContent) {
                showTab('attachments');
            }
        }

        const dropzone = document.getElementById('attachmentDropzone');
        const fileInput = document.getElementById('attachmentInput');
        const fileList = document.getElementById('attachmentFileList');

        if (dropzone && fileInput && fileList) {
            const renderFiles = (files) => {
                fileList.replaceChildren();
                if (!files || files.length === 0) {
                    return;
                }
                Array.from(files).forEach(file => {
                    const row = document.createElement('div');
                    row.className = 'flex items-center justify-between rounded-md bg-white px-3 py-2 border border-gray-200';
                    const name = document.createElement('div');
                    name.className = 'text-sm text-gray-700';
                    name.textContent = file.name;
                    const size = document.createElement('div');
                    size.className = 'text-xs text-gray-500';
                    size.textContent = `${(file.size / 1024 / 1024).toFixed(2)} MB`;
                    row.appendChild(name);
                    row.appendChild(size);
                    fileList.appendChild(row);
                });
            };

            const setHover = (active) => {
                dropzone.classList.toggle('ring-2', active);
                dropzone.classList.toggle('ring-blue-400', active);
                dropzone.classList.toggle('bg-blue-50', active);
            };

            ['dragenter', 'dragover'].forEach(evt => {
                dropzone.addEventListener(evt, (event) => {
                    event.preventDefault();
                    setHover(true);
                });
            });

            ['dragleave', 'drop'].forEach(evt => {
                dropzone.addEventListener(evt, (event) => {
                    event.preventDefault();
                    setHover(false);
                });
            });

            dropzone.addEventListener('drop', (event) => {
                const files = event.dataTransfer?.files;
                if (!files || files.length === 0) {
                    return;
                }
                const dataTransfer = new DataTransfer();
                Array.from(files).forEach(file => dataTransfer.items.add(file));
                fileInput.files = dataTransfer.files;
                renderFiles(fileInput.files);
            });

            dropzone.addEventListener('click', () => {
                fileInput.click();
            });

            dropzone.addEventListener('keydown', (event) => {
                if (event.key === 'Enter' || event.key === ' ') {
                    event.preventDefault();
                    fileInput.click();
                }
            });

            fileInput.addEventListener('change', () => {
                renderFiles(fileInput.files);
            });
        }

        const shouldOpenContracts = <?= json_encode((bool) session('contracts_flash')) ?>;
        if (shouldOpenContracts) {
            const contractsTab = document.getElementById('contracts-tab');
            const contractsContent = document.getElementById('contracts-content');
            if (contractsTab && contractsContent) {
                showTab('contracts');
            }
        }
    });

    function showTab(tabName, event = null) {
        if (event && typeof event.preventDefault === 'function') {
            event.preventDefault();
        }
        const currentX = window.scrollX;
        const currentY = window.scrollY;
        currentTab = tabName;
        
        // Hide all tab contents
        const contents = document.querySelectorAll('.tab-content');
        contents.forEach(content => content.classList.add('hidden'));
        
        // Remove active class from all tabs
        const tabs = document.querySelectorAll('.tab-button');
        tabs.forEach(tab => tab.classList.remove('active'));
        
        // Show selected tab content
        document.getElementById(tabName + '-content').classList.remove('hidden');
        
        // Add active class to selected tab
        document.getElementById(tabName + '-tab').classList.add('active');
        
        // Apply filters to the current tab
        if (tabName === 'timeline') {
            filterTimeline();
        } else if (tabName !== 'details') {
            filterTable(tabName);
        }

        window.requestAnimationFrame(() => {
            window.scrollTo(currentX, currentY);
        });

        return false;
    }

    function filterHistory() {
        if (currentTab === 'timeline') {
            filterTimeline();
        } else if (currentTab !== 'details') {
            filterTable(currentTab);
        }
    }

    function filterTimeline() {
        const searchTerm = document.getElementById('historySearch').value.toLowerCase();
        const startDateRaw = document.getElementById('startDate').value;
        const endDateRaw = document.getElementById('endDate').value;
        const startDate = window.parseClinicDateToIso ? window.parseClinicDateToIso(startDateRaw) : startDateRaw;
        const endDate = window.parseClinicDateToIso ? window.parseClinicDateToIso(endDateRaw) : endDateRaw;
        
        filteredTimelineData = allTimelineData.filter(item => {
            const matchesSearch = !searchTerm || 
                item.title.toLowerCase().includes(searchTerm) ||
                item.description.toLowerCase().includes(searchTerm) ||
                item.status.toLowerCase().includes(searchTerm) ||
                (item.doctor && item.doctor.toLowerCase().includes(searchTerm));
                
            const itemDate = new Date(item.date);
            const matchesDateRange = (!startDate || itemDate >= new Date(startDate)) &&
                                    (!endDate || itemDate <= new Date(endDate));
            
            return matchesSearch && matchesDateRange;
        });
        
        renderTimeline();
    }

    function renderTimeline() {
        const timelineContainer = document.querySelector('#timeline-content ul');
        
        // Clear existing content safely
        timelineContainer.textContent = '';
        
        if (filteredTimelineData.length === 0) {
            const li = document.createElement('li');
            const div = document.createElement('div');
            div.className = 'text-center py-8';
            
            const icon = document.createElement('i');
            icon.className = 'fas fa-search text-gray-400 text-4xl mb-4';
            
            const p = document.createElement('p');
            p.className = 'text-gray-500';
            p.textContent = 'No records found matching your criteria.';
            
            div.appendChild(icon);
            div.appendChild(p);
            li.appendChild(div);
            timelineContainer.appendChild(li);
            return;
        }
        
        filteredTimelineData.forEach((item, index) => {
            const timelineItem = document.createElement('li');
            
            const divRelative = document.createElement('div');
            divRelative.className = 'relative pb-8';
            
            if (index !== filteredTimelineData.length - 1) {
                const spanLine = document.createElement('span');
                spanLine.className = 'absolute top-4 left-4 -ml-px h-full w-0.5 bg-gray-200';
                spanLine.setAttribute('aria-hidden', 'true');
                divRelative.appendChild(spanLine);
            }
            
            const divFlex = document.createElement('div');
            divFlex.className = 'relative flex space-x-3';
            
            // Icon
            const divIconOuter = document.createElement('div');
            const spanIconBg = document.createElement('span');
            spanIconBg.className = `h-8 w-8 rounded-full bg-${item.color}-500 flex items-center justify-center ring-8 ring-white`;
            const icon = document.createElement('i');
            icon.className = `${item.icon} text-white text-xs`;
            spanIconBg.appendChild(icon);
            divIconOuter.appendChild(spanIconBg);
            
            // Content
            const divContent = document.createElement('div');
            divContent.className = 'min-w-0 flex-1 pt-1.5 flex justify-between space-x-4';
            
            const divText = document.createElement('div');
            const pTitle = document.createElement('p');
            pTitle.className = 'text-sm text-gray-500';
            const spanTitle = document.createElement('span');
            spanTitle.className = 'font-medium text-gray-900';
            spanTitle.textContent = item.title;
            const spanDate = document.createElement('span');
            spanDate.className = 'text-gray-500';
            const itemIso = String(item.date || '').split(' ')[0];
            const itemDisplay = window.formatClinicDateFromIso ? window.formatClinicDateFromIso(itemIso) : itemIso;
            spanDate.textContent = ` on ${itemDisplay}`;
            pTitle.appendChild(spanTitle);
            pTitle.appendChild(spanDate);
            
            const pDesc = document.createElement('p');
            pDesc.className = 'text-sm text-gray-500';
            pDesc.textContent = item.description;

            divText.appendChild(pTitle);
            divText.appendChild(pDesc);
            
            if (item.doctor) {
                const pDoctor = document.createElement('p');
                pDoctor.className = 'text-xs text-gray-400 mt-1';
                pDoctor.textContent = `Dr. ${item.doctor}`;
                divText.appendChild(pDoctor);
            }
            
            const divStatus = document.createElement('div');
            divStatus.className = 'text-right text-sm whitespace-nowrap text-gray-500';
            const spanStatus = document.createElement('span');
            spanStatus.className = `inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-${item.color}-100 text-${item.color}-800`;
            spanStatus.textContent = item.status.charAt(0).toUpperCase() + item.status.slice(1);
            divStatus.appendChild(spanStatus);
            
            divContent.appendChild(divText);
            divContent.appendChild(divStatus);
            
            divFlex.appendChild(divIconOuter);
            divFlex.appendChild(divContent);
            divRelative.appendChild(divFlex);
            timelineItem.appendChild(divRelative);
            
            timelineContainer.appendChild(timelineItem);
        });
    }

    function filterTable(tabName) {
        const searchTerm = document.getElementById('historySearch').value.toLowerCase();
        const startDateRaw = document.getElementById('startDate').value;
        const endDateRaw = document.getElementById('endDate').value;
        const startDate = window.parseClinicDateToIso ? window.parseClinicDateToIso(startDateRaw) : startDateRaw;
        const endDate = window.parseClinicDateToIso ? window.parseClinicDateToIso(endDateRaw) : endDateRaw;
        
        const table = document.querySelector(`#${tabName}-content table tbody`);
        if (!table) return;
        
        const rows = table.querySelectorAll('tr');
        let visibleCount = 0;
        
        rows.forEach(row => {
            const cells = row.querySelectorAll('td');
            if (cells.length === 0) return;
            
            let matchesSearch = true;
            let matchesDateRange = true;
            
            // Check search term
            if (searchTerm) {
                matchesSearch = Array.from(cells).some(cell => 
                    cell.textContent.toLowerCase().includes(searchTerm)
                );
            }
            
            // Check date range (prefer ISO data-date when available)
            if (startDate || endDate) {
                const dateCell = cells[0];
                const rawDate = row.dataset.date || (dateCell ? dateCell.getAttribute('data-date') : '') || (dateCell ? dateCell.textContent.trim() : '');
                const cellDate = rawDate ? new Date(rawDate) : null;
                
                if (cellDate && !isNaN(cellDate.getTime())) {
                    if (startDate && cellDate < new Date(startDate)) {
                        matchesDateRange = false;
                    }
                    if (endDate && cellDate > new Date(endDate)) {
                        matchesDateRange = false;
                    }
                }
            }
            
            if (matchesSearch && matchesDateRange) {
                row.style.display = '';
                visibleCount++;
            } else {
                row.style.display = 'none';
            }
        });
        
        // Show/hide "no results" message
        const noResultsDiv = document.querySelector(`#${tabName}-content .text-center`);
        if (noResultsDiv && visibleCount === 0) {
            noResultsDiv.style.display = 'block';
        } else if (noResultsDiv) {
            noResultsDiv.style.display = 'none';
        }
    }

    function clearFilters() {
        document.getElementById('historySearch').value = '';
        document.getElementById('startDate').value = '';
        document.getElementById('endDate').value = '';
        
        if (currentTab === 'timeline') {
            filteredTimelineData = [...allTimelineData];
            renderTimeline();
        } else if (currentTab !== 'details') {
            filterTable(currentTab);
        }
    }





</script>

<script>
    // Odontogram functionality
    let currentNumberingSystem = 'universal';
    let odontogramData = [
        <?php foreach ($odontogram as $tooth): ?>
        {
            tooth_number: <?= $tooth['tooth_number'] ?>,
            condition_type: '<?= $tooth['condition_type'] ?>',
            condition_description: '<?= addslashes($tooth['condition_description'] ?? '') ?>',
            treatment_notes: '<?= addslashes($tooth['treatment_notes'] ?? '') ?>',
            treatment_status: '<?= $tooth['treatment_status'] ?>',
            treatment_date: '<?= $tooth['treatment_date'] ?? '' ?>'
        },
        <?php endforeach; ?>
    ];

    function switchNumberingSystem(system) {
        currentNumberingSystem = system;
        
        // Update button states
        const universalBtn = document.getElementById('universalBtn');
        const fdiBtn = document.getElementById('fdiBtn');
        
        if (system === 'universal') {
            universalBtn.classList.add('bg-white', 'text-blue-600', 'shadow-sm');
            universalBtn.classList.remove('text-gray-600');
            fdiBtn.classList.remove('bg-white', 'text-blue-600', 'shadow-sm');
            fdiBtn.classList.add('text-gray-600');
        } else {
            fdiBtn.classList.add('bg-white', 'text-blue-600', 'shadow-sm');
            fdiBtn.classList.remove('text-gray-600');
            universalBtn.classList.remove('bg-white', 'text-blue-600', 'shadow-sm');
            universalBtn.classList.add('text-gray-600');
        }
        
        // Toggle number display
        document.querySelectorAll('.universal-number').forEach(el => {
            el.style.display = system === 'universal' ? 'block' : 'none';
        });
        document.querySelectorAll('.fdi-number').forEach(el => {
            el.style.display = system === 'fdi' ? 'block' : 'none';
        });
        document.querySelectorAll('.universal-tooltip').forEach(el => {
            el.style.display = system === 'universal' ? 'block' : 'none';
        });
        document.querySelectorAll('.fdi-tooltip').forEach(el => {
            el.style.display = system === 'fdi' ? 'block' : 'none';
        });
    }

    function openToothModal(toothNumber) {
        const modal = document.getElementById('toothModal');
        const modalContent = document.getElementById('modalContent');
        
        document.getElementById('modalToothNumber').textContent = toothNumber;
        document.getElementById('toothNumber').value = toothNumber;
        
        // Load existing data for this tooth
        const existingTooth = odontogramData.find(t => parseInt(t.tooth_number) === parseInt(toothNumber));
        
        if (existingTooth) {
            document.getElementById('condition_type').value = existingTooth.condition_type || 'healthy';
            document.getElementById('condition_description').value = existingTooth.condition_description || '';
            document.getElementById('treatment_notes').value = existingTooth.treatment_notes || '';
            document.getElementById('treatment_status').value = existingTooth.treatment_status || 'completed';
        } else {
            document.getElementById('condition_type').value = 'healthy';
            document.getElementById('condition_description').value = '';
            document.getElementById('treatment_notes').value = '';
            document.getElementById('treatment_status').value = 'completed';
        }
        
        // Show modal with animation
        modal.classList.remove('hidden');
        setTimeout(() => {
            modalContent.style.transform = 'scale(1)';
            modalContent.style.opacity = '1';
        }, 10);
    }

    function closeToothModal() {
        const modal = document.getElementById('toothModal');
        const modalContent = document.getElementById('modalContent');
        
        // Animate out
        modalContent.style.transform = 'scale(0.95)';
        modalContent.style.opacity = '0';
        
        setTimeout(() => {
            modal.classList.add('hidden');
        }, 300);
    }

    function updateTooth(event) {
        event.preventDefault();
        
        const formData = new FormData(event.target);
        const toothNumber = formData.get('tooth_number');
        
        // Show loading state
        const submitBtn = event.target.querySelector('button[type="submit"]');
        
        // Cache original children once if not already cached
        if (!submitBtn.hasOwnProperty('_originalChildren')) {
            submitBtn._originalChildren = Array.from(submitBtn.childNodes).map(node => node.cloneNode(true));
        }
        
        const loadingIcon = document.createElement('i');
        loadingIcon.className = 'fas fa-spinner fa-spin mr-2';
        submitBtn.replaceChildren(loadingIcon, document.createTextNode('Saving...'));
        submitBtn.disabled = true;
        
        fetch('<?= base_url('odontogram/update-tooth') ?>', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            // Refresh CSRF token if present
            if (data.csrf_token && window.refreshCsrfToken) {
                window.refreshCsrfToken(data.csrf_token);
            }

            if (data.success) {
                // ... update local data logic ...
                const existingIndex = odontogramData.findIndex(t => parseInt(t.tooth_number) === parseInt(toothNumber));
                if (existingIndex >= 0) {
                    odontogramData[existingIndex] = {
                        tooth_number: parseInt(toothNumber),
                        condition_type: formData.get('condition_type'),
                        condition_description: formData.get('condition_description'),
                        treatment_notes: formData.get('treatment_notes'),
                        treatment_status: formData.get('treatment_status'),
                        treatment_date: new Date().toISOString().split('T')[0]
                    };
                } else {
                    odontogramData.push({
                        tooth_number: parseInt(toothNumber),
                        condition_type: formData.get('condition_type'),
                        condition_description: formData.get('condition_description'),
                        treatment_notes: formData.get('treatment_notes'),
                        treatment_status: formData.get('treatment_status'),
                        treatment_date: new Date().toISOString().split('T')[0]
                    });
                }
                
                updateToothAppearance(toothNumber, formData.get('condition_type'));
                showNotification('Tooth updated successfully!', 'success');
                closeToothModal();
            } else {
                showNotification('Error updating tooth: ' + (data.message || 'Unknown error'), 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showNotification('Error updating tooth. Please try again.', 'error');
        })
        .finally(() => {
            // Restore original button state
            submitBtn.replaceChildren(...submitBtn._originalChildren.map(node => node.cloneNode(true)));
            submitBtn.disabled = false;
        });
    }

    // ... resetTooth function ...

    function showNotification(message, type = 'info') {
        // Create notification element
        const notification = document.createElement('div');
        notification.className = `fixed top-4 right-4 z-50 px-6 py-3 rounded-lg shadow-lg transition-all duration-300 transform translate-x-full ${
            type === 'success' ? 'bg-green-500 text-white' :
            type === 'error' ? 'bg-red-500 text-white' :
            'bg-blue-500 text-white'
        }`;
        
        const flexDiv = document.createElement('div');
        flexDiv.className = 'flex items-center space-x-2';
        
        const icon = document.createElement('i');
        icon.className = `fas ${type === 'success' ? 'fa-check' : type === 'error' ? 'fa-times' : 'fa-info'}`;
        
        const span = document.createElement('span');
        span.textContent = message;
        
        flexDiv.appendChild(icon);
        flexDiv.appendChild(span);
        notification.appendChild(flexDiv);
        
        document.body.appendChild(notification);
        
        // Animate in
        setTimeout(() => {
            notification.style.transform = 'translateX(0)';
        }, 100);
        
        // Remove after 3 seconds
        setTimeout(() => {
            notification.style.transform = 'translateX(full)';
            setTimeout(() => {
                if (notification.parentNode) {
                    document.body.removeChild(notification);
                }
            }, 300);
        }, 3000);
    }

    // Close modal when clicking outside
    document.getElementById('toothModal').addEventListener('click', function(e) {
        if (e.target === this) {
            closeToothModal();
        }
    });

    // Keyboard shortcuts
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            closeToothModal();
        }
    });
</script>

<?= $this->endSection() ?>

