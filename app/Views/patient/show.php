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
<div class="container mx-auto px-6 py-8">
    <div class="space-y-6">
        <!-- Page Header -->
        <div class="flex flex-col md:flex-row md:justify-between md:items-center space-y-4 md:space-y-0">
            <div>
                <h1 class="text-3xl font-bold text-gray-800">Patient Details</h1>
                <p class="text-gray-600 mt-1">Complete information for <?= $patient['first_name'] . ' ' . $patient['last_name'] ?></p>
            </div>
            <div class="flex space-x-3">
                <a href="<?= base_url('patient') ?>" 
                   class="inline-flex items-center px-4 py-2 bg-gray-100 text-gray-700 text-sm font-medium rounded-md hover:bg-gray-200 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition-colors">
                    <i class="fas fa-arrow-left mr-2"></i>Back to Patients
                </a>
                <?php if (has_permission('patients', 'edit')): ?>
                    <a href="<?= base_url('patient/' . $patient['id'] . '/edit') ?>" 
                       class="inline-flex items-center px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition-colors">
                        <i class="fas fa-edit mr-2"></i>Edit Patient
                    </a>
                <?php endif; ?>
            </div>
        </div>

        <!-- Main Content Layout -->
        <div class="flex flex-row lg:flex-row gap-6">
            <!-- Left Column - Patient History Tabs -->
            <div class="lg:w-5/6 lg:flex-shrink-0 w-5/6">
                <div class="bg-white rounded-lg border border-gray-200 shadow-sm">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h3 class="text-lg font-semibold text-gray-800">Patient History</h3>
                    </div>
                    
                    <!-- Tab Navigation -->
                    <div class="px-6 py-4 border-b border-gray-200">
                        <div class="flex flex-col space-y-4">
                            <nav class="flex flex-wrap gap-2" aria-label="Tabs">
                                <button onclick="showTab('details')" 
                                        id="details-tab" 
                                        class="tab-button active whitespace-nowrap py-2 px-3 border-b-2 font-medium text-sm">
                                    <i class="fas fa-user mr-2"></i>Details
                                </button>
                                <button onclick="showTab('timeline')" 
                                        id="timeline-tab" 
                                        class="tab-button whitespace-nowrap py-2 px-3 border-b-2 font-medium text-sm">
                                    <i class="fas fa-history mr-2"></i>Timeline
                                </button>
                                <button onclick="showTab('examinations')" 
                                        id="examinations-tab" 
                                        class="tab-button whitespace-nowrap py-2 px-3 border-b-2 font-medium text-sm">
                                    <i class="fas fa-stethoscope mr-2"></i>Exams (<?= count($examinations) ?>)
                                </button>
                                <button onclick="showTab('appointments')" 
                                        id="appointments-tab" 
                                        class="tab-button whitespace-nowrap py-2 px-3 border-b-2 font-medium text-sm">
                                    <i class="fas fa-calendar mr-2"></i>Appts (<?= count($appointments) ?>)
                                </button>
                                <button onclick="showTab('treatments')" 
                                        id="treatments-tab" 
                                        class="tab-button whitespace-nowrap py-2 px-3 border-b-2 font-medium text-sm">
                                    <i class="fas fa-tooth mr-2"></i>Treatments (<?= count($treatments) ?>)
                                </button>
                                <button onclick="showTab('prescriptions')" 
                                        id="prescriptions-tab" 
                                        class="tab-button whitespace-nowrap py-2 px-3 border-b-2 font-medium text-sm">
                                    <i class="fas fa-prescription mr-2"></i>Rx (<?= count($prescriptions) ?>)
                                </button>
                                <button onclick="showTab('finances')" 
                                        id="finances-tab" 
                                        class="tab-button whitespace-nowrap py-2 px-3 border-b-2 font-medium text-sm">
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
                                    <input type="date" 
                                           id="startDate" 
                                           class="px-3 py-2 border border-gray-300 rounded-lg text-sm">
                                    <input type="date" 
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
                                                <label class="block text-sm font-medium text-gray-600 mb-1">Address</label>
                                                <p class="text-sm text-gray-900">
                                                    <?php
                                                    $address_parts = array_filter([
                                                        $patient['address'],
                                                        $patient['city'],
                                                        $patient['state'],
                                                        $patient['zip_code'],
                                                        $patient['country']
                                                    ]);
                                                    echo $address_parts ? implode(', ', $address_parts) : 'Not provided';
                                                    ?>
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Emergency Contact -->
                                <?php if ($patient['emergency_contact_name'] || $patient['emergency_contact_phone']): ?>
                                    <div class="bg-white rounded-lg border border-gray-200 shadow-sm group hover:shadow-lg transition-all duration-300">
                                        <div class="px-6 py-4 border-b border-gray-200">
                                            <h3 class="text-lg font-semibold text-gray-800">Emergency Contact</h3>
                                        </div>
                                        <div class="px-6 py-4">
                                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                                <div>
                                                    <label class="block text-sm font-medium text-gray-600 mb-1">Contact Name</label>
                                                    <p class="text-sm text-gray-900"><?= $patient['emergency_contact_name'] ?></p>
                                                </div>
                                                <div>
                                                    <label class="block text-sm font-medium text-gray-600 mb-1">Contact Phone</label>
                                                    <p class="text-sm text-gray-900"><?= $patient['emergency_contact_phone'] ?></p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                <?php endif; ?>

                                <!-- Medical Information -->
                                <?php if ($patient['medical_history'] || $patient['allergies']): ?>
                                    <div class="bg-white rounded-lg border border-gray-200 shadow-sm group hover:shadow-lg transition-all duration-300">
                                        <div class="px-6 py-4 border-b border-gray-200">
                                            <h3 class="text-lg font-semibold text-gray-800">Medical Information</h3>
                                        </div>
                                        <div class="px-6 py-4">
                                            <div class="space-y-4">
                                                <?php if ($patient['medical_history']): ?>
                                                    <div>
                                                        <label class="block text-sm font-medium text-gray-600 mb-1">Medical History</label>
                                                        <p class="text-sm text-gray-900"><?= nl2br(htmlspecialchars($patient['medical_history'])) ?></p>
                                                    </div>
                                                <?php endif; ?>
                                                <?php if ($patient['allergies']): ?>
                                                    <div>
                                                        <label class="block text-sm font-medium text-gray-600 mb-1">Allergies</label>
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
                                        $timeline[] = [
                                            'type' => 'examination',
                                            'date' => $exam['examination_date'],
                                            'title' => 'Examination - ' . ($exam['examination_type'] ?? 'General'),
                                            'description' => $exam['chief_complaint'] ?? 'No complaint recorded',
                                            'status' => $exam['status'],
                                            'id' => $exam['id'],
                                            'icon' => 'fas fa-stethoscope',
                                            'color' => 'blue'
                                        ];
                                    }
                                    
                                    // Add appointments
                                    foreach ($appointments as $appt) {
                                        $timeline[] = [
                                            'type' => 'appointment',
                                            'date' => $appt['appointment_date'],
                                            'title' => 'Appointment - ' . ($appt['appointment_type'] ?? 'General'),
                                            'description' => $appt['notes'] ?? 'No notes',
                                            'status' => $appt['status'],
                                            'id' => $appt['id'],
                                            'icon' => 'fas fa-calendar',
                                            'color' => 'green'
                                        ];
                                    }
                                    
                                    // Add treatments
                                    foreach ($treatments as $treatment) {
                                        $timeline[] = [
                                            'type' => 'treatment',
                                            'date' => $treatment['start_date'],
                                            'title' => 'Treatment - ' . ($treatment['treatment_name'] ?? 'General'),
                                            'description' => $treatment['treatment_description'] ?? 'No description',
                                            'status' => $treatment['status'],
                                            'id' => $treatment['id'],
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
                                                            <span class="text-gray-500">on <?= date('M j, Y', strtotime($item['date'])) ?></span>
                                                        </p>
                                                        <p class="text-sm text-gray-500"><?= $item['description'] ?></p>
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

                        <!-- Examinations Tab -->
                        <div id="examinations-content" class="tab-content hidden">
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
                                                        <?= date('M j, Y', strtotime($examination['examination_date'])) ?>
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
                                                        <a href="<?= base_url('examination/' . $examination['id']) ?>" 
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
                                                        <?= date('M j, Y', strtotime($appointment['appointment_date'])) ?> at <?= date('g:i A', strtotime($appointment['appointment_time'])) ?>
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
                                                        <a href="<?= base_url('appointment/' . $appointment['id']) ?>" 
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
                                                        <a href="<?= base_url('treatment/' . $treatment['id']) ?>" 
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
                                                        <?= date('M j, Y', strtotime($prescription['prescribed_date'])) ?>
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
                                                        <a href="<?= base_url('prescription/' . $prescription['id']) ?>" 
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
                            <?php if (!empty($finances)): ?>
                                <div class="overflow-x-auto">
                                    <table class="min-w-full divide-y divide-gray-200">
                                        <thead class="bg-gray-50">
                                            <tr>
                                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Type</th>
                                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Payment Method</th>
                                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody class="bg-white divide-y divide-gray-200">
                                            <?php foreach ($finances as $finance): ?>
                                                <tr class="hover:bg-gray-50">
                                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                        <?= date('M j, Y', strtotime($finance['created_at'])) ?>
                                                    </td>
                                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                        <?= ucfirst($finance['transaction_type'] ?? 'Unknown') ?>
                                                    </td>
                                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                        <?= ucfirst($finance['payment_method'] ?? 'N/A') ?>
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
                                                        <a href="<?= base_url('finance/' . $finance['id']) ?>" 
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


        <!-- Right Column - Sidebar -->
            <div class="lg:w-1/6 lg:flex-shrink-0 space-y-6 w-1/6">
                <!-- Quick Actions -->
                <div class="bg-white rounded-lg border border-gray-200 shadow-sm group hover:shadow-lg transition-all duration-300">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h3 class="text-lg font-semibold text-gray-800">Quick Actions</h3>
                    </div>
                    <div class="px-6 py-4">
                        <div class="space-y-3">
                            <a href="<?= base_url('examination/create?patient_id=' . $patient['id']) ?>" 
                               class="inline-flex items-center px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition-colors w-full">
                                <i class="fas fa-stethoscope mr-2"></i>New Examination
                            </a>
                            <a href="<?= base_url('appointment/create?patient_id=' . $patient['id']) ?>" 
                               class="inline-flex items-center px-4 py-2 bg-gray-100 text-gray-700 text-sm font-medium rounded-md hover:bg-gray-200 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition-colors w-full">
                                <i class="fas fa-calendar-plus mr-2"></i>Schedule Appointment
                            </a>
                            <a href="<?= base_url('odontogram/' . $patient['id']) ?>" 
                               class="inline-flex items-center px-4 py-2 bg-gray-100 text-gray-700 text-sm font-medium rounded-md hover:bg-gray-200 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition-colors w-full">
                                <i class="fas fa-tooth mr-2"></i>View Odontogram
                            </a>
                            <a href="<?= base_url('prescription/create?patient_id=' . $patient['id']) ?>" 
                               class="inline-flex items-center px-4 py-2 bg-gray-100 text-gray-700 text-sm font-medium rounded-md hover:bg-gray-200 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition-colors w-full">
                                <i class="fas fa-prescription mr-2"></i>New Prescription
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Statistics -->
                <div class="bg-white rounded-lg border border-gray-200 shadow-sm group hover:shadow-lg transition-all duration-300">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h3 class="text-lg font-semibold text-gray-800">Statistics</h3>
                    </div>
                    <div class="px-6 py-4">
                        <div class="space-y-4">
                            <div class="flex justify-between items-center">
                                <span class="text-sm text-gray-600">Total Examinations</span>
                                <span class="text-sm font-semibold text-gray-900"><?= count($examinations) ?></span>
                            </div>
                            <div class="flex justify-between items-center">
                                <span class="text-sm text-gray-600">Total Appointments</span>
                                <span class="text-sm font-semibold text-gray-900"><?= count($appointments) ?></span>
                            </div>
                            <div class="flex justify-between items-center">
                                <span class="text-sm text-gray-600">Total Treatments</span>
                                <span class="text-sm font-semibold text-gray-900"><?= count($treatments) ?></span>
                            </div>
                            <div class="flex justify-between items-center">
                                <span class="text-sm text-gray-600">Total Prescriptions</span>
                                <span class="text-sm font-semibold text-gray-900"><?= count($prescriptions) ?></span>
                            </div>
                            <div class="flex justify-between items-center">
                                <span class="text-sm text-gray-600">Invoices</span>
                                <span class="text-sm font-semibold text-gray-900"><?= count($finances) ?></span>
                            </div>
                            <div class="flex justify-between items-center">
                                <span class="text-sm text-gray-600">Member Since</span>
                                <span class="text-sm font-semibold text-gray-900"><?= date('M Y', strtotime($patient['created_at'])) ?></span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>






    </div>
</div>


<style>
    .tab-button {
        border-bottom-color: transparent;
        color: #6b7280;
    }

    .tab-button.active {
        border-bottom-color: #3b82f6;
        color: #3b82f6;
    }

    .tab-button:hover {
        color: #374151;
        border-bottom-color: #d1d5db;
    }

    .tab-content {
        display: block;
    }

    .tab-content.hidden {
        display: none;
    }

    /* Ensure proper layout on all screen sizes */
    @media (min-width: 1024px) {
        .flex {
            display: flex !important;
        }
        
        .lg\\:flex-row {
            flex-direction: row !important;
        }
        
        .lg\\:w-2\\/3 {
            width: 66.666667% !important;
            flex-shrink: 0 !important;
        }
        
        .lg\\:w-1\\/3 {
            width: 33.333333% !important;
            flex-shrink: 0 !important;
            min-width: 300px;
            max-width: 400px;
        }
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
    });

    function showTab(tabName) {
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
        const startDate = document.getElementById('startDate').value;
        const endDate = document.getElementById('endDate').value;
        
        filteredTimelineData = allTimelineData.filter(item => {
            const matchesSearch = !searchTerm || 
                item.title.toLowerCase().includes(searchTerm) ||
                item.description.toLowerCase().includes(searchTerm) ||
                item.status.toLowerCase().includes(searchTerm);
                
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
            spanDate.textContent = ` on ${new Date(item.date).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' })}`;
            pTitle.appendChild(spanTitle);
            pTitle.appendChild(spanDate);
            
            const pDesc = document.createElement('p');
            pDesc.className = 'text-sm text-gray-500';
            pDesc.textContent = item.description;
            
            divText.appendChild(pTitle);
            divText.appendChild(pDesc);
            
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
        const startDate = document.getElementById('startDate').value;
        const endDate = document.getElementById('endDate').value;
        
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
            
            // Check date range (assuming first cell contains date)
            if (startDate || endDate) {
                const dateCell = cells[0];
                const dateText = dateCell.textContent.trim();
                const cellDate = new Date(dateText);
                
                if (startDate && cellDate < new Date(startDate)) {
                    matchesDateRange = false;
                }
                if (endDate && cellDate > new Date(endDate)) {
                    matchesDateRange = false;
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
        const originalText = submitBtn.textContent;
        
        submitBtn.textContent = '';
        const loadingIcon = document.createElement('i');
        loadingIcon.className = 'fas fa-spinner fa-spin mr-2';
        submitBtn.appendChild(loadingIcon);
        submitBtn.appendChild(document.createTextNode('Saving...'));
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
            // Reset button state
            submitBtn.textContent = originalText;
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
