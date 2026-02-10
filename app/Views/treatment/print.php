<?php $title = 'Treatment Details - ' . ($treatment['treatment_id'] ?? $treatment['id'] ?? ''); ?>
<?= $this->extend('layouts/print') ?>

<?= $this->section('content') ?>
<div class="min-h-screen bg-white">
    <div class="max-w-4xl mx-auto p-8">
        <div class="bg-white border-2 border-gray-200 rounded-lg shadow-lg overflow-hidden">
            <div class="bg-gradient-to-r from-emerald-600 to-teal-600 text-white p-6">
                <div class="flex justify-between items-center">
                    <div>
                        <h2 class="text-2xl font-bold">TREATMENT DETAILS</h2>
                        <p class="text-emerald-100">
                            Treatment ID: <?= esc($treatment['treatment_id'] ?? $treatment['id'] ?? 'N/A') ?>
                        </p>
                    </div>
                    <div class="text-right text-sm">
                        <p>Start: <?= !empty($treatment['start_date']) ? date('M j, Y', strtotime($treatment['start_date'])) : 'N/A' ?></p>
                        <p>Status: <?= esc(ucfirst(str_replace('_', ' ', $treatment['status'] ?? 'active'))) ?></p>
                    </div>
                </div>
            </div>

            <div class="p-6 border-b border-gray-200">
                <h3 class="text-lg font-bold text-gray-900 mb-4 flex items-center">
                    <i class="fas fa-user text-emerald-600 mr-2"></i>
                    Patient Information
                </h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <p class="text-sm text-gray-600">Name:</p>
                        <p class="font-semibold text-gray-900">
                            <?= esc(($treatment['first_name'] ?? '') . ' ' . ($treatment['last_name'] ?? '')) ?>
                        </p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-600">Phone:</p>
                        <p class="font-semibold text-gray-900"><?= esc($treatment['phone'] ?? 'N/A') ?></p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-600">Email:</p>
                        <p class="font-semibold text-gray-900"><?= esc($treatment['email'] ?? 'N/A') ?></p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-600">Date of Birth:</p>
                        <p class="font-semibold text-gray-900">
                            <?= !empty($treatment['date_of_birth']) ? date('M j, Y', strtotime($treatment['date_of_birth'])) : 'N/A' ?>
                        </p>
                    </div>
                </div>
            </div>

            <?php $doctorName = trim(($treatment['doctor_first_name'] ?? '') . ' ' . ($treatment['doctor_last_name'] ?? '')); ?>
            <div class="p-6 border-b border-gray-200">
                <h3 class="text-lg font-bold text-gray-900 mb-4 flex items-center">
                    <i class="fas fa-tooth text-teal-600 mr-2"></i>
                    Treatment Details
                </h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <p class="text-sm text-gray-600">Type:</p>
                        <p class="font-semibold text-gray-900">
                            <?= esc(ucfirst(str_replace('_', ' ', $treatment['treatment_type'] ?? 'N/A'))) ?>
                        </p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-600">Tooth #:</p>
                        <p class="font-semibold text-gray-900"><?= esc($treatment['tooth_number'] ?? 'N/A') ?></p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-600">Status:</p>
                        <span class="inline-flex items-center px-3 py-1 text-xs font-bold rounded-full
                            <?= ($treatment['status'] ?? '') === 'completed' ? 'bg-emerald-100 text-emerald-800' :
                                (($treatment['status'] ?? '') === 'cancelled' ? 'bg-red-100 text-red-800' :
                                (($treatment['status'] ?? '') === 'on_hold' ? 'bg-amber-100 text-amber-800' : 'bg-green-100 text-green-800')) ?>">
                            <?= esc(ucfirst(str_replace('_', ' ', $treatment['status'] ?? 'active'))) ?>
                        </span>
                    </div>
                    <div>
                        <p class="text-sm text-gray-600">Cost:</p>
                        <p class="font-semibold text-gray-900"><?= formatCurrency($treatment['cost'] ?? 0) ?></p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-600">Estimated Duration:</p>
                        <p class="font-semibold text-gray-900"><?= esc($treatment['estimated_duration'] ?? 'N/A') ?> minutes</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-600">Completion Date:</p>
                        <p class="font-semibold text-gray-900">
                            <?= !empty($treatment['completion_date']) ? date('M j, Y', strtotime($treatment['completion_date'])) : 'Not completed' ?>
                        </p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-600">Created By:</p>
                        <p class="font-semibold text-gray-900"><?= $doctorName !== '' ? 'Dr. ' . esc($doctorName) : 'Unknown' ?></p>
                    </div>
                </div>
            </div>

            <div class="p-6 border-b border-gray-200">
                <h3 class="text-lg font-bold text-gray-900 mb-4 flex items-center">
                    <i class="fas fa-sticky-note text-amber-600 mr-2"></i>
                    Treatment Description
                </h3>
                <div class="bg-gray-50 p-4 rounded-lg">
                    <?php if (!empty($treatment['treatment_description'])): ?>
                        <p class="text-gray-800 leading-relaxed whitespace-pre-line"><?= htmlspecialchars($treatment['treatment_description']) ?></p>
                    <?php else: ?>
                        <p class="text-gray-600">No description provided.</p>
                    <?php endif; ?>
                </div>
            </div>

            <?php if (!empty($treatment['treatment_notes'])): ?>
                <div class="p-6">
                    <h3 class="text-lg font-bold text-gray-900 mb-4 flex items-center">
                        <i class="fas fa-clipboard text-blue-600 mr-2"></i>
                        Treatment Notes
                    </h3>
                    <div class="bg-gray-50 p-4 rounded-lg">
                        <p class="text-gray-800 leading-relaxed whitespace-pre-line"><?= htmlspecialchars($treatment['treatment_notes']) ?></p>
                    </div>
                </div>
            <?php endif; ?>

            <div class="bg-gray-50 p-6">
                <div class="flex justify-between items-center">
                    <div class="text-sm text-gray-600">
                        <p>Generated on: <?= date('M j, Y g:i A') ?></p>
                        <p>Treatment ID: <?= esc($treatment['treatment_id'] ?? $treatment['id'] ?? 'N/A') ?></p>
                    </div>
                    <div class="text-sm text-gray-600 text-right">
                        <p><?= esc($clinic['name']) ?></p>
                        <p><?= esc($clinic['website'] ?? '') ?></p>
                    </div>
                </div>
            </div>
        </div>

        <div class="mt-8">
            <div class="flex justify-center space-x-4">
                <button onclick="window.print()" class="group/btn relative inline-flex items-center justify-center px-8 py-3 bg-gradient-to-r from-emerald-500 to-teal-600 text-white text-sm font-bold rounded-xl shadow-2xl shadow-emerald-500/25 hover:shadow-emerald-500/40 transition-all duration-500 hover:scale-105 hover:-translate-y-1">
                    <div class="absolute inset-0 bg-gradient-to-r from-emerald-600 to-teal-700 rounded-xl blur opacity-0 group-hover/btn:opacity-100 transition-opacity duration-300"></div>
                    <i class="fas fa-print mr-3 relative z-10 group-hover/btn:scale-110 transition-transform duration-300"></i>
                    <span class="relative z-10">Print Treatment</span>
                </button>
                <a href="<?= base_url('treatment/' . ($treatment['id'] ?? $treatment['treatment_id'])) ?>" class="group/btn relative inline-flex items-center justify-center px-8 py-3 bg-gradient-to-r from-gray-500 to-slate-600 text-white text-sm font-bold rounded-xl shadow-2xl shadow-gray-500/25 hover:shadow-gray-500/40 transition-all duration-500 hover:scale-105 hover:-translate-y-1">
                    <div class="absolute inset-0 bg-gradient-to-r from-gray-600 to-slate-700 rounded-xl blur opacity-0 group-hover/btn:opacity-100 transition-opacity duration-300"></div>
                    <i class="fas fa-arrow-left mr-3 relative z-10 group-hover/btn:scale-110 transition-transform duration-300"></i>
                    <span class="relative z-10">Back to Details</span>
                </a>
                <a href="<?= base_url('treatment') ?>" class="group/btn relative inline-flex items-center justify-center px-8 py-3 bg-gradient-to-r from-blue-500 to-cyan-600 text-white text-sm font-bold rounded-xl shadow-2xl shadow-blue-500/25 hover:shadow-blue-500/40 transition-all duration-500 hover:scale-105 hover:-translate-y-1">
                    <div class="absolute inset-0 bg-gradient-to-r from-blue-600 to-cyan-700 rounded-xl blur opacity-0 group-hover/btn:opacity-100 transition-opacity duration-300"></div>
                    <i class="fas fa-list mr-3 relative z-10 group-hover/btn:scale-110 transition-transform duration-300"></i>
                    <span class="relative z-10">All Treatments</span>
                </a>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection() ?>
