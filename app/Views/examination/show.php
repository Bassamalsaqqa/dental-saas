<?= $this->extend('layouts/main_auth') ?>

<?= $this->section('content') ?>
<div class="px-6 py-8">
    <!-- Page Header -->
    <div class="mb-8">
        <div class="flex flex-col lg:flex-row lg:justify-between lg:items-center space-y-6 lg:space-y-0">
            <div>
                <h1 class="text-4xl font-bold text-gray-800 mb-3">Examination Details</h1>
                <p class="text-gray-600 text-lg">Examination ID: <?= $examination['examination_id'] ?></p>
            </div>
            <div class="flex flex-col sm:flex-row space-y-3 sm:space-y-0 sm:space-x-4">
                <a href="<?= base_url('examination') ?>" class="inline-flex items-center px-4 py-2 bg-gray-100 text-gray-700 text-sm font-medium rounded-md hover:bg-gray-200 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition-colors">
                    <i class="fas fa-arrow-left mr-2"></i>Back to Examinations
                </a>
                <a href="<?= base_url('examination/' . $examination['id'] . '/edit') ?>" class="inline-flex items-center px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition-colors">
                    <i class="fas fa-edit mr-2"></i>Edit Examination
                </a>
            </div>
        </div>
    </div>

    <div class="space-y-8"> 

    <!-- Patient Information -->
    <div class="bg-white rounded-lg border border-gray-200 shadow-sm group hover:shadow-lg transition-all duration-300">
        <div class="p-6">
            <div class="flex items-center justify-between mb-6">
            <div class="flex items-center space-x-3">
                <div class="w-12 h-12 bg-gradient-to-br from-blue-500 to-blue-600 rounded-full flex items-center justify-center">
                    <i class="fas fa-user text-white text-lg"></i>
                </div>
                <div>
                    <h2 class="text-xl font-semibold text-gray-800">Patient Information</h2>
                    <p class="text-sm text-gray-500">Patient details and examination context</p>
                </div>
            </div>
            <a href="<?= base_url('patient/' . $examination['patient_id']) ?>" class="inline-flex items-center px-4 py-2 bg-gray-100 text-gray-700 text-sm font-medium rounded-md hover:bg-gray-200 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition-colors">
                <i class="fas fa-external-link-alt mr-2"></i>View Patient
            </a>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <div class="bg-gray-50 p-4 rounded-lg">
                <label class="block text-sm font-medium text-gray-700 mb-2 text-gray-600">Patient Name</label>
                <p class="text-gray-900 font-semibold text-lg"><?= $examination['first_name'] . ' ' . $examination['last_name'] ?></p>
            </div>
            <div class="bg-gray-50 p-4 rounded-lg">
                <label class="block text-sm font-medium text-gray-700 mb-2 text-gray-600">Patient Number</label>
                <p class="text-gray-900 font-semibold text-lg"><?= $examination['patient_number'] ?></p>
            </div>
            <div class="bg-gray-50 p-4 rounded-lg">
                <label class="block text-sm font-medium text-gray-700 mb-2 text-gray-600">Examination Date</label>
                <p class="text-gray-900 font-semibold text-lg"><?= date('M j, Y g:i A', strtotime($examination['examination_date'])) ?></p>
            </div>
        </div>
        </div>
    </div>

    <!-- Examination Details -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Basic Information -->
        <div class="bg-white rounded-lg border border-gray-200 shadow-sm group hover:shadow-lg transition-all duration-300">
            <div class="p-6">
                <div class="flex items-center space-x-3 mb-6">
                <div class="w-10 h-10 bg-gradient-to-br from-green-500 to-green-600 rounded-full flex items-center justify-center">
                    <i class="fas fa-stethoscope text-white"></i>
                </div>
                <div>
                    <h2 class="text-xl font-semibold text-gray-800">Examination Information</h2>
                    <p class="text-sm text-gray-500">Basic examination details</p>
                </div>
            </div>
            <div class="space-y-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2 text-gray-600">Examination Type</label>
                        <div class="mt-2">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                <?= $examination['examination_type'] == 'emergency' ? 'bg-red-100 text-red-800' : 
                                    ($examination['examination_type'] == 'initial' ? 'bg-yellow-100 text-yellow-800' : 
                                    ($examination['examination_type'] == 'periodic' ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800')) ?>">
                                <?= ucfirst(str_replace('_', ' ', $examination['examination_type'])) ?>
                            </span>
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2 text-gray-600">Status</label>
                        <div class="mt-2">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                <?= $examination['status'] == 'completed' ? 'bg-green-100 text-green-800' : 
                                    ($examination['status'] == 'in_progress' ? 'bg-blue-100 text-blue-800' : 
                                    ($examination['status'] == 'pending' ? 'bg-yellow-100 text-yellow-800' : 'bg-red-100 text-red-800')) ?>">
                                <?= ucfirst($examination['status']) ?>
                            </span>
                        </div>
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2 text-gray-600">Chief Complaint</label>
                    <div class="mt-2 p-4 bg-gray-50 rounded-lg">
                        <p class="text-gray-900"><?= $examination['chief_complaint'] ?></p>
                    </div>
                </div>
                <?php if (!empty($examination['history_of_present_illness'])): ?>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2 text-gray-600">History of Present Illness</label>
                    <div class="mt-2 p-4 bg-gray-50 rounded-lg">
                        <p class="text-gray-900"><?= $examination['history_of_present_illness'] ?></p>
                    </div>
                </div>
                <?php endif; ?>
            </div>
            </div>
        </div>

        <!-- Medical History -->
        <div class="bg-white rounded-lg border border-gray-200 shadow-sm group hover:shadow-lg transition-all duration-300">
            <div class="p-6">
                <div class="flex items-center space-x-3 mb-6">
                <div class="w-10 h-10 bg-gradient-to-br from-purple-500 to-purple-600 rounded-full flex items-center justify-center">
                    <i class="fas fa-history text-white"></i>
                </div>
                <div>
                    <h2 class="text-xl font-semibold text-gray-800">Medical & Dental History</h2>
                    <p class="text-sm text-gray-500">Patient's medical and dental background</p>
                </div>
            </div>
            <div class="space-y-6">
                <?php if (!empty($examination['medical_history'])): ?>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2 text-gray-600">Medical History</label>
                    <div class="mt-2 p-4 bg-gray-50 rounded-lg">
                        <p class="text-gray-900"><?= $examination['medical_history'] ?></p>
                    </div>
                </div>
                <?php endif; ?>
                <?php if (!empty($examination['dental_history'])): ?>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2 text-gray-600">Dental History</label>
                    <div class="mt-2 p-4 bg-gray-50 rounded-lg">
                        <p class="text-gray-900"><?= $examination['dental_history'] ?></p>
                    </div>
                </div>
                <?php endif; ?>
                <?php if (empty($examination['medical_history']) && empty($examination['dental_history'])): ?>
                <div class="text-center py-8">
                    <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                        <i class="fas fa-history text-gray-400 text-2xl"></i>
                    </div>
                    <p class="text-gray-500">No medical or dental history recorded</p>
                </div>
                <?php endif; ?>
            </div>
            </div>
        </div>
    </div>

    <!-- Clinical Findings & Diagnosis -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <div class="bg-white rounded-lg border border-gray-200 shadow-sm group hover:shadow-lg transition-all duration-300">
            <div class="p-6">
                <div class="flex items-center space-x-3 mb-6">
                    <div class="w-10 h-10 bg-gradient-to-br from-orange-500 to-orange-600 rounded-full flex items-center justify-center">
                        <i class="fas fa-search text-white"></i>
                    </div>
                    <div>
                        <h2 class="text-xl font-semibold text-gray-800">Clinical Findings</h2>
                        <p class="text-sm text-gray-500">Objective examination results</p>
                    </div>
                </div>
            <?php if (!empty($examination['clinical_findings'])): ?>
                <div class="p-4 bg-gray-50 rounded-lg">
                    <p class="text-gray-900"><?= $examination['clinical_findings'] ?></p>
                </div>
            <?php else: ?>
                <div class="text-center py-8">
                    <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                        <i class="fas fa-search text-gray-400 text-2xl"></i>
                    </div>
                    <p class="text-gray-500">No clinical findings recorded</p>
                </div>
            <?php endif; ?>
            </div>
        </div>

        <div class="bg-white rounded-lg border border-gray-200 shadow-sm group hover:shadow-lg transition-all duration-300">
            <div class="p-6">
                <div class="flex items-center space-x-3 mb-6">
                    <div class="w-10 h-10 bg-gradient-to-br from-red-500 to-red-600 rounded-full flex items-center justify-center">
                        <i class="fas fa-diagnoses text-white"></i>
                    </div>
                    <div>
                        <h2 class="text-xl font-semibold text-gray-800">Diagnosis</h2>
                        <p class="text-sm text-gray-500">Clinical diagnosis and assessment</p>
                    </div>
                </div>
            <?php if (!empty($examination['diagnosis'])): ?>
                <div class="p-4 bg-gray-50 rounded-lg">
                    <p class="text-gray-900"><?= $examination['diagnosis'] ?></p>
                </div>
            <?php else: ?>
                <div class="text-center py-8">
                    <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                        <i class="fas fa-diagnoses text-gray-400 text-2xl"></i>
                    </div>
                    <p class="text-gray-500">No diagnosis recorded</p>
                </div>
            <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Treatment Plan & Prognosis -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <div class="bg-white rounded-lg border border-gray-200 shadow-sm group hover:shadow-lg transition-all duration-300">
            <div class="p-6">
                <div class="flex items-center space-x-3 mb-6">
                    <div class="w-10 h-10 bg-gradient-to-br from-indigo-500 to-indigo-600 rounded-full flex items-center justify-center">
                        <i class="fas fa-clipboard-list text-white"></i>
                    </div>
                    <div>
                        <h2 class="text-xl font-semibold text-gray-800">Treatment Plan</h2>
                        <p class="text-sm text-gray-500">Proposed treatment procedures</p>
                    </div>
                </div>
            <?php if (!empty($examination['treatment_plan'])): ?>
                <div class="p-4 bg-gray-50 rounded-lg">
                    <p class="text-gray-900"><?= $examination['treatment_plan'] ?></p>
                </div>
            <?php else: ?>
                <div class="text-center py-8">
                    <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                        <i class="fas fa-clipboard-list text-gray-400 text-2xl"></i>
                    </div>
                    <p class="text-gray-500">No treatment plan recorded</p>
                </div>
            <?php endif; ?>
            </div>
        </div>

        <div class="bg-white rounded-lg border border-gray-200 shadow-sm group hover:shadow-lg transition-all duration-300">
            <div class="p-6">
                <div class="flex items-center space-x-3 mb-6">
                    <div class="w-10 h-10 bg-gradient-to-br from-teal-500 to-teal-600 rounded-full flex items-center justify-center">
                        <i class="fas fa-chart-line text-white"></i>
                    </div>
                    <div>
                        <h2 class="text-xl font-semibold text-gray-800">Prognosis</h2>
                        <p class="text-sm text-gray-500">Expected outcome and prognosis</p>
                    </div>
                </div>
            <?php if (!empty($examination['prognosis'])): ?>
                <div class="p-4 bg-gray-50 rounded-lg">
                    <p class="text-gray-900"><?= $examination['prognosis'] ?></p>
                </div>
            <?php else: ?>
                <div class="text-center py-8">
                    <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                        <i class="fas fa-chart-line text-gray-400 text-2xl"></i>
                    </div>
                    <p class="text-gray-500">No prognosis recorded</p>
                </div>
            <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Recommendations & Next Appointment -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <div class="bg-white rounded-lg border border-gray-200 shadow-sm group hover:shadow-lg transition-all duration-300">
            <div class="p-6">
                <div class="flex items-center space-x-3 mb-6">
                    <div class="w-10 h-10 bg-gradient-to-br from-yellow-500 to-yellow-600 rounded-full flex items-center justify-center">
                        <i class="fas fa-lightbulb text-white"></i>
                    </div>
                    <div>
                        <h2 class="text-xl font-semibold text-gray-800">Recommendations</h2>
                        <p class="text-sm text-gray-500">Patient care recommendations</p>
                    </div>
                </div>
            <?php if (!empty($examination['recommendations'])): ?>
                <div class="p-4 bg-gray-50 rounded-lg">
                    <p class="text-gray-900"><?= $examination['recommendations'] ?></p>
                </div>
            <?php else: ?>
                <div class="text-center py-8">
                    <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                        <i class="fas fa-lightbulb text-gray-400 text-2xl"></i>
                    </div>
                    <p class="text-gray-500">No recommendations recorded</p>
                </div>
            <?php endif; ?>
            </div>
        </div>

        <div class="bg-white rounded-lg border border-gray-200 shadow-sm group hover:shadow-lg transition-all duration-300">
            <div class="p-6">
                <div class="flex items-center space-x-3 mb-6">
                    <div class="w-10 h-10 bg-gradient-to-br from-pink-500 to-pink-600 rounded-full flex items-center justify-center">
                        <i class="fas fa-calendar-alt text-white"></i>
                    </div>
                    <div>
                        <h2 class="text-xl font-semibold text-gray-800">Next Appointment</h2>
                        <p class="text-sm text-gray-500">Scheduled follow-up appointment</p>
                    </div>
                </div>
            <?php if (!empty($examination['next_appointment'])): ?>
                <div class="p-4 bg-gray-50 rounded-lg">
                    <div class="flex items-center space-x-3">
                        <i class="fas fa-calendar text-blue-600"></i>
                        <p class="text-gray-900 font-semibold"><?= date('M j, Y g:i A', strtotime($examination['next_appointment'])) ?></p>
                    </div>
                </div>
            <?php else: ?>
                <div class="text-center py-8">
                    <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                        <i class="fas fa-calendar-alt text-gray-400 text-2xl"></i>
                    </div>
                    <p class="text-gray-500">No next appointment scheduled</p>
                </div>
            <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Examination Notes -->
    <?php if (!empty($examination['examination_notes'])): ?>
    <div class="bg-white rounded-lg border border-gray-200 shadow-sm group hover:shadow-lg transition-all duration-300">
        <div class="p-6">
            <div class="flex items-center space-x-3 mb-6">
                <div class="w-10 h-10 bg-gradient-to-br from-gray-500 to-gray-600 rounded-full flex items-center justify-center">
                    <i class="fas fa-sticky-note text-white"></i>
                </div>
                <div>
                    <h2 class="text-xl font-semibold text-gray-800">Examination Notes</h2>
                    <p class="text-sm text-gray-500">Additional notes and observations</p>
                </div>
            </div>
            <div class="p-4 bg-gray-50 rounded-lg">
                <p class="text-gray-900"><?= $examination['examination_notes'] ?></p>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- Odontogram Section -->
    <div class="bg-white rounded-lg border border-gray-200 shadow-sm group hover:shadow-lg transition-all duration-300">
        <div class="p-6">
            <div class="flex items-center justify-between mb-6">
            <div class="flex items-center space-x-3">
                <div class="w-10 h-10 bg-gradient-to-br from-blue-500 to-blue-600 rounded-full flex items-center justify-center">
                    <i class="fas fa-tooth text-white"></i>
                </div>
                <div>
                    <h2 class="text-xl font-semibold text-gray-800">Dental Chart (Odontogram)</h2>
                    <p class="text-sm text-gray-500">Visual dental chart and tooth records</p>
                </div>
            </div>
            <a href="<?= base_url('odontogram/' . $examination['patient_id']) ?>" class="inline-flex items-center px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition-colors">
                <i class="fas fa-tooth mr-2"></i>View Full Odontogram
            </a>
        </div>
        <?php if (!empty($odontogram)): ?>
            <div class="bg-gray-50 p-6 rounded-lg">
                <div class="flex items-center space-x-3 mb-4">
                    <i class="fas fa-check-circle text-green-600"></i>
                    <p class="text-gray-900 font-medium">Odontogram data available</p>
                </div>
                <p class="text-gray-600">Click "View Full Odontogram" to see the complete dental chart with detailed tooth records.</p>
            </div>
        <?php else: ?>
            <div class="bg-gray-50 p-6 rounded-lg text-center">
                <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                    <i class="fas fa-tooth text-gray-400 text-2xl"></i>
                </div>
                <p class="text-gray-600 mb-4">No odontogram data recorded for this examination.</p>
                <a href="<?= base_url('odontogram/' . $examination['patient_id']) ?>" class="inline-flex items-center px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition-colors">
                    <i class="fas fa-plus mr-2"></i>Create Odontogram
                </a>
            </div>
        <?php endif; ?>
        </div>
    </div>

    <!-- Treatments Section -->
    <div class="bg-white rounded-lg border border-gray-200 shadow-sm group hover:shadow-lg transition-all duration-300">
        <div class="p-6">
            <div class="flex items-center justify-between mb-6">
            <div class="flex items-center space-x-3">
                <div class="w-10 h-10 bg-gradient-to-br from-emerald-500 to-emerald-600 rounded-full flex items-center justify-center">
                    <i class="fas fa-procedures text-white"></i>
                </div>
                <div>
                    <h2 class="text-xl font-semibold text-gray-800">Related Treatments</h2>
                    <p class="text-sm text-gray-500">Treatment procedures and interventions</p>
                </div>
            </div>
            <a href="<?= base_url('treatment/create?examination_id=' . $examination['id']) ?>" class="inline-flex items-center px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition-colors">
                <i class="fas fa-plus mr-2"></i>Add Treatment
            </a>
        </div>
        <?php if (!empty($treatments)): ?>
            <div class="space-y-4">
                <?php foreach ($treatments as $treatment): ?>
                    <div class="bg-gray-50 p-4 rounded-lg hover:bg-gray-100 transition-colors">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center space-x-3">
                                <div class="w-8 h-8 bg-dental-100 rounded-full flex items-center justify-center">
                                    <i class="fas fa-procedures text-blue-600 text-sm"></i>
                                </div>
                                <div>
                                    <h3 class="font-semibold text-gray-900"><?= $treatment['treatment_name'] ?></h3>
                                    <p class="text-sm text-gray-600"><?= $treatment['description'] ?></p>
                                </div>
                            </div>
                            <a href="<?= base_url('treatment/' . $treatment['id']) ?>" class="text-blue-600 hover:text-blue-700 p-2 rounded-lg hover:bg-blue-50 transition-colors" title="View Treatment Details">
                                <i class="fas fa-eye"></i>
                            </a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="bg-gray-50 p-6 rounded-lg text-center">
                <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                    <i class="fas fa-procedures text-gray-400 text-2xl"></i>
                </div>
                <p class="text-gray-600 mb-4">No treatments recorded for this examination.</p>
                <a href="<?= base_url('treatment/create?examination_id=' . $examination['id']) ?>" class="inline-flex items-center px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition-colors">
                    <i class="fas fa-plus mr-2"></i>Add Treatment
                </a>
            </div>
        <?php endif; ?>
        </div>
    </div>

    <!-- Action Buttons -->
    <div class="flex justify-end space-x-3">
        <a href="<?= base_url('examination') ?>" class="inline-flex items-center px-4 py-2 bg-gray-100 text-gray-700 text-sm font-medium rounded-md hover:bg-gray-200 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition-colors">
            <i class="fas fa-arrow-left mr-2"></i>Back to List
        </a>
        <a href="<?= base_url('examination/' . $examination['id'] . '/edit') ?>" class="inline-flex items-center px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition-colors">
            <i class="fas fa-edit mr-2"></i>Edit Examination
        </a>
        <?php if ($examination['status'] != 'completed'): ?>
            <button onclick="completeExamination(<?= $examination['id'] ?>)" class="inline-flex items-center px-4 py-2 bg-green-600 text-white text-sm font-medium rounded-md hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 transition-colors">
                <i class="fas fa-check mr-2"></i>Complete Examination
            </button>
        <?php endif; ?>
    </div>
</div>
</div>

<!-- Complete Examination Modal -->
<div id="completeModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50 hidden">
    <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white max-w-lg">
        <div class="p-8">
            <div class="flex items-center justify-center mb-6">
                <div class="w-16 h-16 bg-green-100 rounded-full flex items-center justify-center">
                    <i class="fas fa-check-circle text-green-600 text-2xl"></i>
                </div>
            </div>
            
            <div class="text-center mb-6">
                <h3 class="text-xl font-bold text-gray-900 mb-2">Complete Examination</h3>
                <p class="text-gray-600">Add final notes to complete this examination</p>
            </div>
            
            <form id="completeForm">
                <input type="hidden" id="examinationId" name="examination_id">
                
                <div class="mb-6">
                    <label for="examination_notes" class="block text-sm font-medium text-gray-700 mb-2">Final Notes</label>
                    <textarea id="examination_notes" name="examination_notes" 
                              class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500" rows="4" 
                              placeholder="Add any final notes about the examination, treatment recommendations, or follow-up instructions..."></textarea>
                </div>
                
                <div class="flex space-x-4">
                    <button type="button" onclick="closeCompleteModal()" class="inline-flex items-center px-4 py-2 bg-gray-100 text-gray-700 text-sm font-medium rounded-md hover:bg-gray-200 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition-colors flex-1">
                        <i class="fas fa-times mr-2"></i>Cancel
                    </button>
                    <button type="submit" class="inline-flex items-center px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition-colors flex-1">
                        <i class="fas fa-check mr-2"></i>Complete Examination
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function completeExamination(examinationId) {
    document.getElementById('examinationId').value = examinationId;
    const modal = document.getElementById('completeModal');
    modal.classList.remove('hidden');
    modal.style.display = 'flex';
}

function closeCompleteModal() {
    const modal = document.getElementById('completeModal');
    modal.classList.add('hidden');
    modal.style.display = 'none';
    document.getElementById('completeForm').reset();
}

// Handle form submission
document.getElementById('completeForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    const examinationId = formData.get('examination_id');
    
    fetch(`<?= base_url('examination') ?>/${examinationId}/complete`, {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            closeCompleteModal();
            location.reload();
        } else {
            alert('Error: ' + data.message);
        }
    });
});

// Close modal when clicking outside
document.getElementById('completeModal').addEventListener('click', function(e) {
    if (e.target === this) {
        closeCompleteModal();
    }
});

// Close modal with Escape key
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closeCompleteModal();
    }
});
</script>
<?= $this->endSection() ?>
