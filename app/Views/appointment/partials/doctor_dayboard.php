<?php
$bookingContext = $booking_context ?? null;
if ($bookingContext === null && class_exists(\App\Services\ProviderDirectoryService::class)) {
    $activeClinicId = (int) (session('active_clinic_id') ?? 0);
    if ($activeClinicId > 0) {
        $pds = new \App\Services\ProviderDirectoryService();
        $isDynamic = false;
        try {
            $isDynamic = $pds->isEnabled($activeClinicId);
            $bookingContext = $pds->getBookingContext($activeClinicId);
        } catch (\Throwable $e) {
            if ($isDynamic || (strpos($e->getMessage(), 'DYNAMIC_SCHEDULING') !== false)) {
                $bookingContext = [
                    'enabled' => true,
                    'schema_ready' => false,
                    'providers' => [],
                    'chair_options' => [],
                    'error' => $e->getMessage(),
                ];
            } else {
                $bookingContext = null;
            }
        }
    }
}

$dynamicEnabled = !empty($bookingContext['enabled']);
if (!$dynamicEnabled) {
    return;
}

$schemaReady = !isset($bookingContext['schema_ready']) || !empty($bookingContext['schema_ready']);
if (!$schemaReady) {
    ?>
    <div class="doctor-dayboard-schema-notice w-full rounded-2xl border border-amber-300 bg-amber-50 p-4 text-amber-900 shadow-sm my-4">
        <div class="flex items-center gap-3">
            <span class="inline-flex h-9 w-9 items-center justify-center rounded-xl bg-amber-100 text-amber-700">
                <i class="fas fa-triangle-exclamation text-base"></i>
            </span>
            <div>
                <h4 class="text-sm font-black text-amber-950"><?= esc(lang('DoctorScheduling.schemaNotReadyTitle')) ?></h4>
                <p class="text-xs font-semibold text-amber-800"><?= esc(lang('DoctorScheduling.schemaNotReady')) ?></p>
            </div>
        </div>
    </div>
    <?php
    return;
}

$providers = is_array($bookingContext['providers'] ?? null) ? $bookingContext['providers'] : ($clinic_providers ?? ($providers ?? []));
$chosenDay = (string) ($selected_date ?? date('Y-m-d'));

$activeDoctors = [];
foreach ($providers as $provider) {
    $uid = (int) ($provider['user_id'] ?? $provider['id'] ?? 0);
    if ($uid <= 0) {
        continue;
    }
    $pName = (string) ($provider['name'] ?? trim(($provider['first_name'] ?? '') . ' ' . ($provider['last_name'] ?? '')));
    $activeDoctors[$uid] = [
        'user_id' => $uid,
        'name' => $pName,
        'appointments' => [],
    ];
}

$legacyAppointments = [];
foreach (($appointments ?? []) as $appointment) {
    $appDate = (string) ($appointment['appointment_date'] ?? '');
    if ($appDate !== '' && $appDate !== $chosenDay) {
        continue;
    }

    $providerUserId = !empty($appointment['provider_user_id']) ? (int) $appointment['provider_user_id'] : null;
    if ($providerUserId !== null && isset($activeDoctors[$providerUserId])) {
        $activeDoctors[$providerUserId]['appointments'][] = $appointment;
    } else {
        $legacyAppointments[] = $appointment;
    }
}

foreach ($activeDoctors as &$docCol) {
    usort($docCol['appointments'], static function ($a, $b) {
        return strcmp((string) ($a['appointment_time'] ?? ''), (string) ($b['appointment_time'] ?? ''));
    });
}
unset($docCol);

usort($legacyAppointments, static function ($a, $b) {
    return strcmp((string) ($a['appointment_time'] ?? ''), (string) ($b['appointment_time'] ?? ''));
});

$statusColors = [
    'scheduled' => 'bg-blue-100 text-blue-800 border-blue-200',
    'confirmed' => 'bg-emerald-100 text-emerald-800 border-emerald-200',
    'arrived'   => 'bg-cyan-100 text-cyan-800 border-cyan-200',
    'ready'     => 'bg-teal-100 text-teal-800 border-teal-200',
    'in_chair'  => 'bg-indigo-100 text-indigo-800 border-indigo-200',
    'checkout'  => 'bg-violet-100 text-violet-800 border-violet-200',
    'completed' => 'bg-slate-100 text-slate-800 border-slate-200',
    'cancelled' => 'bg-rose-100 text-rose-800 border-rose-200',
    'no_show'   => 'bg-amber-100 text-amber-800 border-amber-200',
];

$statusLabelMap = [
    'scheduled' => lang('UI.appointmentStatusScheduled'),
    'confirmed' => lang('UI.appointmentStatusConfirmed'),
    'arrived'   => lang('UI.appointmentStageArrived'),
    'ready'     => lang('UI.appointmentStageReady'),
    'in_chair'  => lang('UI.appointmentStageInChair'),
    'checkout'  => lang('UI.appointmentStageCheckout'),
    'completed' => lang('UI.appointmentStageCompleted') ?: lang('UI.dashboardCompleted'),
    'cancelled' => lang('UI.appointmentStatusCancelled'),
    'no_show'   => lang('UI.appointmentStatusNoShow'),
];
?>
<style>
.doctor-dayboard-track {
    display: flex;
    gap: 1rem;
    overflow-x: auto;
    padding-bottom: 0.75rem;
    padding-top: 0.25rem;
    -webkit-overflow-scrolling: touch;
    width: 100%;
}
.doctor-dayboard-column {
    flex: 0 0 320px;
    min-width: 280px;
    max-width: 380px;
}
@media (max-width: 640px) {
    .doctor-dayboard-column {
        flex: 0 0 280px;
        min-width: 260px;
    }
}
</style>

<div class="doctor-dayboard-container w-full overflow-hidden rounded-2xl border border-slate-200 bg-slate-50/70 p-4 shadow-sm" data-doctor-dayboard="1" data-chosen-day="<?= esc($chosenDay) ?>">
    <div class="mb-4 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 border-b border-slate-200 pb-3">
        <div class="flex items-center gap-3">
            <span class="inline-flex h-10 w-10 items-center justify-center rounded-xl bg-gradient-to-br from-blue-600 to-indigo-600 text-white shadow-md shadow-blue-500/20">
                <i class="fas fa-user-doctor text-base"></i>
            </span>
            <div>
                <h3 class="text-base font-black text-slate-900"><?= esc(lang('DoctorScheduling.doctorDayboard')) ?></h3>
                <p class="text-xs font-semibold text-slate-500">
                    <?= esc(lang('DoctorScheduling.doctorDayboardSubtitle')) ?> &bull; <?= esc(formatDate($chosenDay)) ?>
                </p>
            </div>
        </div>
        <div class="flex flex-wrap items-center gap-2">
            <span class="inline-flex items-center rounded-full bg-blue-100/90 px-3 py-1 text-xs font-bold text-blue-800">
                <?= count($activeDoctors) ?> <?= esc(lang('DoctorScheduling.dayboardAllDoctors')) ?>
            </span>
            <?php if (count($legacyAppointments) > 0): ?>
                <span class="inline-flex items-center rounded-full bg-amber-100/90 px-3 py-1 text-xs font-bold text-amber-800">
                    <?= count($legacyAppointments) ?> <?= esc(lang('DoctorScheduling.unassignedLegacyColumn')) ?>
                </span>
            <?php endif; ?>
        </div>
    </div>

    <!-- Native Horizontal Overflow Track for Doctors (no 3 cap, smooth scrolling, RTL compatible) -->
    <div class="doctor-dayboard-track scroll-smooth" style="display: flex; gap: 1rem; overflow-x: auto; width: 100%; -webkit-overflow-scrolling: touch;">
        <?php foreach ($activeDoctors as $doctor): ?>
            <div class="doctor-dayboard-column flex-shrink-0 flex flex-col rounded-2xl border border-slate-200 bg-white/95 shadow-sm transition hover:border-blue-200" style="flex: 0 0 320px; min-width: 280px; max-width: 380px;" data-provider-user-id="<?= (int) $doctor['user_id'] ?>">
                <!-- Doctor Column Header -->
                <div class="border-b border-slate-100 bg-gradient-to-r from-slate-50 to-blue-50/50 p-4 rounded-t-2xl">
                    <div class="flex items-start justify-between gap-2">
                        <div class="min-w-0 flex-1">
                            <div class="flex items-center gap-1.5 min-w-0">
                                <i class="fas fa-user-md text-blue-600 flex-shrink-0" style="flex-shrink: 0;"></i>
                                <span class="doctor-reference-badge flex-shrink-0 inline-flex items-center rounded-md border border-slate-200 bg-white px-1.5 py-0.5 font-mono text-[11px] font-bold text-slate-700 shadow-2xs" dir="ltr" style="flex-shrink: 0; min-width: max-content;">#<?= (int) $doctor['user_id'] ?></span>
                                <h4 class="truncate text-sm font-black text-slate-900 min-w-0" title="<?= esc(\App\Libraries\ProviderDisplayLabel::forOption($doctor['user_id'], $doctor['name'])) ?>">
                                    <?= esc($doctor['name']) ?>
                                </h4>
                            </div>
                            <span class="mt-1 inline-flex items-center rounded-full bg-blue-50 px-2 py-0.5 text-[11px] font-bold text-blue-700">
                                <?= count($doctor['appointments']) ?> <?= esc(lang('UI.menuAppointments')) ?>
                            </span>
                        </div>
                        <?php if (has_permission('appointments', 'create')): ?>
                            <?php $doctorDisplayLabel = \App\Libraries\ProviderDisplayLabel::forOption($doctor['user_id'], $doctor['name']); ?>
                            <a href="<?= base_url('appointment/create?provider_user_id=' . (int) $doctor['user_id'] . '&appointment_date=' . esc($chosenDay)) ?>" data-appointment-create-modal-open data-appointment-provider="<?= (int) $doctor['user_id'] ?>" data-appointment-date="<?= esc($chosenDay) ?>" class="flex-shrink-0 inline-flex items-center gap-1 rounded-xl bg-blue-600 px-2.5 py-1 text-xs font-bold text-white shadow-sm transition hover:bg-blue-700 hover:shadow" title="<?= esc(lang('DoctorScheduling.newBookingForDoctor', [$doctorDisplayLabel])) ?>" aria-label="<?= esc(lang('DoctorScheduling.newBookingForDoctor', [$doctorDisplayLabel])) ?>" style="flex-shrink: 0;">
                                <i class="fas fa-plus text-[10px]"></i>
                                <span><?= esc(lang('DoctorScheduling.newBooking')) ?></span>
                                <span class="font-mono text-[10px] opacity-90" dir="ltr">(#<?= (int) $doctor['user_id'] ?>)</span>
                            </a>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Doctor Visits List -->
                <div class="flex-1 space-y-2.5 p-3 min-h-[160px] max-h-[520px] overflow-y-auto">
                    <?php if (empty($doctor['appointments'])): ?>
                        <div class="flex h-full min-h-[140px] items-center justify-center p-4 text-center">
                            <p class="text-xs font-semibold text-slate-400">
                                <i class="fas fa-calendar-xmark text-slate-300 text-lg block mb-1.5"></i>
                                <?= esc(lang('DoctorScheduling.noAppointmentsForDoctor')) ?>
                            </p>
                        </div>
                    <?php else: ?>
                        <?php foreach ($doctor['appointments'] as $visit): ?>
                            <?php
                            $status = (string) ($visit['status'] ?? 'scheduled');
                            $colorClass = $statusColors[$status] ?? 'bg-slate-100 text-slate-800 border-slate-200';
                            $statusLabel = $statusLabelMap[$status] ?? ucfirst(str_replace('_', ' ', $status));
                            $visitTime = formatTime($visit['appointment_time'] ?? '');
                            $patientFullName = trim(($visit['first_name'] ?? '') . ' ' . ($visit['last_name'] ?? ''));
                            ?>
                            <div class="rounded-xl border border-slate-200 bg-white p-3 shadow-xs transition hover:border-blue-300 hover:shadow-md">
                                <div class="flex items-start justify-between gap-2">
                                    <span class="inline-flex items-center rounded-lg bg-amber-50 px-2 py-0.5 text-xs font-black text-amber-900 border border-amber-200" dir="ltr">
                                        <?= esc($visitTime) ?>
                                    </span>
                                    <span class="inline-flex items-center rounded-md border px-2 py-0.5 text-[11px] font-bold uppercase tracking-wider <?= esc($colorClass) ?>">
                                        <?= esc($statusLabel) ?>
                                    </span>
                                </div>
                                <div class="mt-2 min-w-0">
                                    <a href="<?= workflow_context_url('appointment/' . $visit['id']) ?>" class="block truncate text-sm font-black text-slate-900 hover:text-blue-600 transition">
                                        <?= esc($patientFullName) ?>
                                    </a>
                                    <div class="mt-1 flex items-center justify-between text-[11px] font-medium text-slate-500">
                                        <span><?= esc($visit['patient_number'] ?? $visit['phone'] ?? '') ?></span>
                                        <span class="text-slate-400"><?= (int) ($visit['duration'] ?? 30) ?> <?= esc(lang('UI.patientMinutes')) ?></span>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        <?php endforeach; ?>

        <!-- Separate Visible Legacy / Reassignment Column -->
        <div class="doctor-dayboard-column flex-shrink-0 flex flex-col rounded-2xl border-2 border-dashed border-amber-300 bg-amber-50/40 shadow-sm" style="flex: 0 0 320px; min-width: 280px; max-width: 380px;" data-dayboard-legacy-column="1">
            <!-- Legacy Header -->
            <div class="border-b border-amber-200 bg-gradient-to-r from-amber-50 to-orange-50/60 p-4 rounded-t-2xl">
                <div class="flex items-start justify-between gap-2">
                    <div class="min-w-0">
                        <h4 class="truncate text-sm font-black text-amber-950">
                            <i class="fas fa-triangle-exclamation text-amber-600 me-1.5"></i><?= esc(lang('DoctorScheduling.unassignedLegacyColumn')) ?>
                        </h4>
                        <p class="text-[11px] font-semibold text-amber-800">
                            <?= esc(lang('DoctorScheduling.unassignedLegacyDesc')) ?>
                        </p>
                    </div>
                    <span class="inline-flex items-center rounded-full bg-amber-200/80 px-2 py-0.5 text-xs font-black text-amber-900">
                        <?= count($legacyAppointments) ?>
                    </span>
                </div>
            </div>

            <!-- Legacy Visits List -->
            <div class="flex-1 space-y-2.5 p-3 min-h-[160px] max-h-[520px] overflow-y-auto">
                <?php if (empty($legacyAppointments)): ?>
                    <div class="flex h-full min-h-[140px] items-center justify-center p-4 text-center">
                        <p class="text-xs font-semibold text-amber-700/70">
                            <i class="fas fa-check-circle text-amber-400 text-lg block mb-1.5"></i>
                            <?= esc(lang('DoctorScheduling.noUnassignedAppointments')) ?>
                        </p>
                    </div>
                <?php else: ?>
                    <?php foreach ($legacyAppointments as $visit): ?>
                        <?php
                        $status = (string) ($visit['status'] ?? 'scheduled');
                        $colorClass = $statusColors[$status] ?? 'bg-slate-100 text-slate-800 border-slate-200';
                        $statusLabel = $statusLabelMap[$status] ?? ucfirst(str_replace('_', ' ', $status));
                        $visitTime = formatTime($visit['appointment_time'] ?? '');
                        $patientFullName = trim(($visit['first_name'] ?? '') . ' ' . ($visit['last_name'] ?? ''));
                        $storedChair = trim((string) ($visit['chair_name'] ?? ''));
                        ?>
                        <div class="rounded-xl border border-amber-200 bg-white p-3 shadow-xs transition hover:border-amber-300 hover:shadow-md">
                            <div class="flex items-start justify-between gap-2">
                                <span class="inline-flex items-center rounded-lg bg-amber-50 px-2 py-0.5 text-xs font-black text-amber-900 border border-amber-200" dir="ltr">
                                    <?= esc($visitTime) ?>
                                </span>
                                <span class="inline-flex items-center rounded-md border px-2 py-0.5 text-[11px] font-bold uppercase tracking-wider <?= esc($colorClass) ?>">
                                    <?= esc($statusLabel) ?>
                                </span>
                            </div>
                            <div class="mt-2 min-w-0">
                                <a href="<?= workflow_context_url('appointment/' . $visit['id']) ?>" class="block truncate text-sm font-black text-slate-900 hover:text-amber-700 transition">
                                    <?= esc($patientFullName) ?>
                                </a>
                                <div class="mt-1 flex flex-wrap items-center justify-between gap-1 text-[11px]">
                                    <span class="inline-flex items-center gap-1 font-semibold text-amber-800 bg-amber-100/70 px-2 py-0.5 rounded-md">
                                        <i class="fas fa-chair text-amber-600"></i>
                                        <?php if (!empty($visit['provider_user_id'])): ?><bdi dir="ltr">#<?= (int)$visit['provider_user_id'] ?></bdi><?php endif; ?>
                                        <?= esc(lang('DoctorScheduling.storedChairSnapshot', [$storedChair !== '' ? $storedChair : 'N/A'])) ?>
                                    </span>
                                    <?php if (has_permission('appointments', 'edit')): ?>
                                        <a href="<?= workflow_context_url('appointment/' . $visit['id'] . '/edit') ?>" class="inline-flex items-center gap-1 rounded-md bg-amber-600 px-2 py-0.5 text-[11px] font-bold text-white hover:bg-amber-700 transition">
                                            <i class="fas fa-user-pen text-[10px]"></i> <?= esc(lang('DoctorScheduling.reassign')) ?>
                                        </a>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
