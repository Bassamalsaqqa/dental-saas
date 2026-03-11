<?php $title = lang('UI.appointmentDetailsPrintTitle') . ' - ' . ($appointment['appointment_id'] ?? ''); ?>
<?= $this->extend('layouts/print') ?>

<?= $this->section('content') ?>
<div class="min-h-screen bg-white">
    <div class="max-w-4xl mx-auto p-8">
        <div class="bg-white border-2 border-gray-200 rounded-lg shadow-lg overflow-hidden">
            <div class="bg-gradient-to-r from-blue-600 to-indigo-600 text-white p-6">
                <div class="flex justify-between items-center">
                    <div>
                        <h2 class="text-2xl font-bold"><?= lang('UI.appointmentDetailsPrintTitle') ?></h2>
                        <p class="text-blue-100">
                            <?= lang('UI.appointmentId') ?>: <?= esc($appointment['appointment_id'] ?? 'N/A') ?>
                        </p>
                    </div>
                    <div class="text-right text-sm">
                        <p><?= lang('UI.appointmentDate') ?>: <?= formatDate($appointment['appointment_date']) ?></p>
                        <p><?= lang('UI.appointmentTime') ?>: <?= formatTime($appointment['appointment_time']) ?></p>
                    </div>
                </div>
            </div>

            <div class="p-6 border-b border-gray-200">
                <h3 class="text-lg font-bold text-gray-900 mb-4 flex items-center">
                    <i class="fas fa-user text-blue-600 mr-2"></i>
                    <?= lang('UI.appointmentPatientInformation') ?>
                </h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <p class="text-sm text-gray-600"><?= lang('UI.appointmentName') ?>:</p>
                        <p class="font-semibold text-gray-900">
                            <?= esc(($appointment['first_name'] ?? '') . ' ' . ($appointment['last_name'] ?? '')) ?>
                        </p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-600"><?= lang('UI.patientPatientId') ?>:</p>
                        <p class="font-semibold text-gray-900"><?= esc($appointment['patient_number'] ?? 'N/A') ?></p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-600"><?= lang('UI.patientPhone') ?>:</p>
                        <p class="font-semibold text-gray-900"><?= esc($appointment['phone'] ?? 'N/A') ?></p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-600"><?= lang('UI.patientEmail') ?>:</p>
                        <p class="font-semibold text-gray-900"><?= esc($appointment['email'] ?? 'N/A') ?></p>
                    </div>
                </div>
            </div>

            <div class="p-6 border-b border-gray-200">
                <h3 class="text-lg font-bold text-gray-900 mb-4 flex items-center">
                    <i class="fas fa-calendar-check text-indigo-600 mr-2"></i>
                    <?= lang('UI.appointmentDetailsPrintTitle') ?>
                </h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <p class="text-sm text-gray-600"><?= lang('TenantMisc.type') ?>:</p>
                        <p class="font-semibold text-gray-900">
                            <?= esc(ucfirst(str_replace('_', ' ', $appointment['appointment_type'] ?? 'N/A'))) ?>
                        </p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-600"><?= lang('UI.appointmentDuration') ?>:</p>
                        <p class="font-semibold text-gray-900"><?= esc($appointment['duration'] ?? 'N/A') ?> <?= lang('UI.appointmentMinutes') ?></p>
                    </div>
                    <?php $doctorName = trim(($appointment['creator_first_name'] ?? '') . ' ' . ($appointment['creator_last_name'] ?? '')); ?>
                    <div>
                        <p class="text-sm text-gray-600"><?= lang('UI.patientStatus') ?>:</p>
                        <span class="inline-flex items-center px-3 py-1 text-xs font-bold rounded-full
                            <?= ($appointment['status'] ?? '') === 'confirmed' ? 'bg-green-100 text-green-800' :
                                (($appointment['status'] ?? '') === 'completed' ? 'bg-emerald-100 text-emerald-800' :
                                (($appointment['status'] ?? '') === 'cancelled' ? 'bg-red-100 text-red-800' :
                                (($appointment['status'] ?? '') === 'no_show' ? 'bg-orange-100 text-orange-800' : 'bg-blue-100 text-blue-800'))) ?>">
                            <?= esc(ucfirst(str_replace('_', ' ', $appointment['status'] ?? 'scheduled'))) ?>
                        </span>
                    </div>
                    <div>
                        <p class="text-sm text-gray-600"><?= lang('UI.appointmentCreatedBy') ?>:</p>
                        <p class="font-semibold text-gray-900"><?= $doctorName !== '' ? 'Dr. ' . esc($doctorName) : esc(lang('TenantMisc.unknown')) ?></p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-600"><?= lang('UI.appointmentCreatedAt') ?>:</p>
                        <p class="font-semibold text-gray-900"><?= formatDate($appointment['created_at'] ?? date('Y-m-d H:i:s')) ?></p>
                    </div>
                </div>
            </div>

            <div class="p-6">
                <h3 class="text-lg font-bold text-gray-900 mb-4 flex items-center">
                    <i class="fas fa-sticky-note text-amber-600 mr-2"></i>
                    <?= lang('UI.appointmentAdditionalNotes') ?>
                </h3>
                <div class="bg-gray-50 p-4 rounded-lg">
                    <?php if (!empty($appointment['notes'])): ?>
                        <p class="text-gray-800 leading-relaxed whitespace-pre-line"><?= htmlspecialchars($appointment['notes']) ?></p>
                    <?php else: ?>
                        <p class="text-gray-600"><?= lang('UI.appointmentNoAdditionalNotes') ?></p>
                    <?php endif; ?>
                </div>
            </div>

            <div class="bg-gray-50 p-6">
                <div class="flex justify-between items-center">
                    <div class="text-sm text-gray-600">
                        <p><?= lang('UI.appointmentGeneratedOn') ?>: <?= formatDateTime(date('Y-m-d H:i:s')) ?></p>
                        <p><?= lang('UI.appointmentId') ?>: <?= esc($appointment['appointment_id'] ?? 'N/A') ?></p>
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
                <button onclick="window.print()" class="group/btn relative inline-flex items-center justify-center px-8 py-3 bg-gradient-to-r from-blue-500 to-indigo-600 text-white text-sm font-bold rounded-xl shadow-2xl shadow-blue-500/25 hover:shadow-blue-500/40 transition-all duration-500 hover:scale-105 hover:-translate-y-1">
                    <div class="absolute inset-0 bg-gradient-to-r from-blue-600 to-indigo-700 rounded-xl blur opacity-0 group-hover/btn:opacity-100 transition-opacity duration-300"></div>
                    <i class="fas fa-print mr-3 relative z-10 group-hover/btn:scale-110 transition-transform duration-300"></i>
                    <span class="relative z-10"><?= lang('UI.appointmentPrintAction') ?></span>
                </button>
                <a href="<?= base_url('appointment/' . ($appointment['id'] ?? $appointment['appointment_id'])) ?>" class="group/btn relative inline-flex items-center justify-center px-8 py-3 bg-gradient-to-r from-gray-500 to-slate-600 text-white text-sm font-bold rounded-xl shadow-2xl shadow-gray-500/25 hover:shadow-gray-500/40 transition-all duration-500 hover:scale-105 hover:-translate-y-1">
                    <div class="absolute inset-0 bg-gradient-to-r from-gray-600 to-slate-700 rounded-xl blur opacity-0 group-hover/btn:opacity-100 transition-opacity duration-300"></div>
                    <i class="fas fa-arrow-left mr-3 relative z-10 group-hover/btn:scale-110 transition-transform duration-300"></i>
                    <span class="relative z-10"><?= lang('UI.appointmentBackToDetails') ?></span>
                </a>
                <a href="<?= base_url('appointment') ?>" class="group/btn relative inline-flex items-center justify-center px-8 py-3 bg-gradient-to-r from-blue-500 to-cyan-600 text-white text-sm font-bold rounded-xl shadow-2xl shadow-blue-500/25 hover:shadow-blue-500/40 transition-all duration-500 hover:scale-105 hover:-translate-y-1">
                    <div class="absolute inset-0 bg-gradient-to-r from-blue-600 to-cyan-700 rounded-xl blur opacity-0 group-hover/btn:opacity-100 transition-opacity duration-300"></div>
                    <i class="fas fa-calendar mr-3 relative z-10 group-hover/btn:scale-110 transition-transform duration-300"></i>
                    <span class="relative z-10"><?= lang('UI.appointmentAllAppointments') ?></span>
                </a>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection() ?>
