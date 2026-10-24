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
    <div class="doctor-dayboard-schema-notice w-full v3-radius border v3-border-default v3-bg-waiting p-4 v3-text-warning v3-elevation-none my-4">
        <div class="flex items-center gap-3">
            <span class="inline-flex h-9 w-9 items-center justify-center v3-radius v3-bg-waiting v3-text-warning">
                <i class="fas fa-triangle-exclamation text-base"></i>
            </span>
            <div>
                <h4 class="text-sm font-black v3-text-warning"><?= esc(lang('DoctorScheduling.schemaNotReadyTitle')) ?></h4>
                <p class="text-xs font-semibold v3-text-warning"><?= esc(lang('DoctorScheduling.schemaNotReady')) ?></p>
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
    'scheduled' => 'v3-bg-scheduled v3-text-primary v3-border-default',
    'confirmed' => 'v3-bg-paid v3-text-primary v3-border-default',
    'arrived'   => 'v3-bg-scheduled v3-text-primary v3-border-default',
    'ready'     => 'v3-bg-scheduled v3-text-primary v3-border-default',
    'in_chair'  => 'v3-bg-chair v3-text-chair v3-border-default',
    'checkout'  => 'v3-bg-chair v3-text-chair v3-border-default',
    'completed' => 'v3-bg-soft v3-text-ink v3-border-default',
    'cancelled' => 'v3-bg-alert v3-text-danger v3-border-default',
    'no_show'   => 'v3-bg-waiting v3-text-warning v3-border-default',
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

<div class="doctor-dayboard-container w-full overflow-hidden v3-radius border v3-border-default v3-bg-panel p-4 v3-elevation-none" data-doctor-dayboard="1" data-chosen-day="<?= esc($chosenDay) ?>">
    <div class="mb-4 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 border-b v3-border-default pb-3">
        <div class="flex items-center gap-3">
            <span class="inline-flex h-10 w-10 items-center justify-center v3-radius v3-bg-panel v3-bg-panel v3-bg-panel v3-text-on-primary v3-elevation-none shadow-blue-500/20">
                <i class="fas fa-user-doctor text-base"></i>
            </span>
            <div>
                <h3 class="text-base font-black v3-text-ink"><?= esc(lang('DoctorScheduling.doctorDayboard')) ?></h3>
                <p class="text-xs font-semibold v3-text-muted">
                    <?= esc(lang('DoctorScheduling.doctorDayboardSubtitle')) ?> &bull; <?= esc(formatDate($chosenDay)) ?>
                </p>
            </div>
        </div>
        <div class="flex flex-wrap items-center gap-2">
            <span class="inline-flex items-center rounded-full v3-bg-scheduled px-3 py-1 text-xs font-bold v3-text-primary">
                <?= count($activeDoctors) ?> <?= esc(lang('DoctorScheduling.dayboardAllDoctors')) ?>
            </span>
            <?php if (count($legacyAppointments) > 0): ?>
                <span class="inline-flex items-center rounded-full v3-bg-waiting px-3 py-1 text-xs font-bold v3-text-warning">
                    <?= count($legacyAppointments) ?> <?= esc(lang('DoctorScheduling.unassignedLegacyColumn')) ?>
                </span>
            <?php endif; ?>
        </div>
    </div>

    <!-- Native Horizontal Overflow Track for Doctors (no 3 cap, smooth scrolling, RTL compatible) -->
    <div class="doctor-dayboard-track scroll-smooth" style="display: flex; gap: 1rem; overflow-x: auto; width: 100%; -webkit-overflow-scrolling: touch;">
        <?php foreach ($activeDoctors as $doctor): ?>
            <div class="doctor-dayboard-column flex-shrink-0 flex flex-col v3-radius border v3-border-default v3-bg-panel v3-elevation-none transition v3-border-default" style="flex: 0 0 320px; min-width: 280px; max-width: 380px;" data-provider-user-id="<?= (int) $doctor['user_id'] ?>">
                <!-- Doctor Column Header -->
                <div class="border-b v3-border-default v3-bg-panel v3-bg-panel v3-bg-panel p-4 rounded-t-2xl">
                    <div class="flex items-start justify-between gap-2">
                        <div class="min-w-0 flex-1">
                            <div class="flex items-center gap-1.5 min-w-0">
                                <i class="fas fa-user-md v3-text-primary flex-shrink-0" style="flex-shrink: 0;"></i>
                                <span class="doctor-reference-badge flex-shrink-0 inline-flex items-center rounded-md border v3-border-default v3-bg-panel px-1.5 py-0.5 font-mono text-[11px] font-bold v3-text-ink shadow-2xs" dir="ltr" style="flex-shrink: 0; min-width: max-content;">#<?= (int) $doctor['user_id'] ?></span>
                                <h4 class="truncate text-sm font-black v3-text-ink min-w-0" title="<?= esc(\App\Libraries\ProviderDisplayLabel::forOption($doctor['user_id'], $doctor['name'])) ?>">
                                    <?= esc($doctor['name']) ?>
                                </h4>
                            </div>
                            <span class="mt-1 inline-flex items-center rounded-full v3-bg-scheduled px-2 py-0.5 text-[11px] font-bold v3-text-primary">
                                <?= count($doctor['appointments']) ?> <?= esc(lang('UI.menuAppointments')) ?>
                            </span>
                        </div>
                        <?php if (has_permission('appointments', 'create')): ?>
                            <?php $doctorDisplayLabel = \App\Libraries\ProviderDisplayLabel::forOption($doctor['user_id'], $doctor['name']); ?>
                            <a href="<?= base_url('appointment/create?provider_user_id=' . (int) $doctor['user_id'] . '&appointment_date=' . esc($chosenDay)) ?>" data-appointment-create-modal-open data-appointment-provider="<?= (int) $doctor['user_id'] ?>" data-appointment-date="<?= esc($chosenDay) ?>" class="flex-shrink-0 inline-flex items-center gap-1 v3-radius v3-bg-primary px-2.5 py-1 text-xs font-bold v3-text-on-primary v3-elevation-none transition v3-hover-primary hover:shadow v3-btn v3-btn--primary" title="<?= esc(lang('DoctorScheduling.newBookingForDoctor', [$doctorDisplayLabel])) ?>" aria-label="<?= esc(lang('DoctorScheduling.newBookingForDoctor', [$doctorDisplayLabel])) ?>" style="flex-shrink: 0;">
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
                            <p class="text-xs font-semibold v3-text-muted">
                                <i class="fas fa-calendar-xmark v3-text-muted text-lg block mb-1.5"></i>
                                <?= esc(lang('DoctorScheduling.noAppointmentsForDoctor')) ?>
                            </p>
                        </div>
                    <?php else: ?>
                        <?php foreach ($doctor['appointments'] as $visit): ?>
                            <?php
                            $status = (string) ($visit['status'] ?? 'scheduled');
                            $colorClass = $statusColors[$status] ?? 'v3-bg-soft v3-text-ink v3-border-default';
                            $statusLabel = $statusLabelMap[$status] ?? ucfirst(str_replace('_', ' ', $status));
                            $visitTime = formatTime($visit['appointment_time'] ?? '');
                            $patientFullName = trim(($visit['first_name'] ?? '') . ' ' . ($visit['last_name'] ?? ''));
                            ?>
                            <div class="v3-radius border v3-border-default v3-bg-panel p-3 shadow-xs transition v3-border-default hover:v3-elevation-none">
                                <div class="flex items-start justify-between gap-2">
                                    <span class="inline-flex items-center rounded-lg v3-bg-waiting px-2 py-0.5 text-xs font-black v3-text-warning border v3-border-default" dir="ltr">
                                        <?= esc($visitTime) ?>
                                    </span>
                                    <span class="inline-flex items-center rounded-md border px-2 py-0.5 text-[11px] font-bold uppercase tracking-wider <?= esc($colorClass) ?>">
                                        <?= esc($statusLabel) ?>
                                    </span>
                                </div>
                                <div class="mt-2 min-w-0">
                                    <a href="<?= workflow_context_url('appointment/' . $visit['id']) ?>" class="block truncate text-sm font-black v3-text-ink v3-hover-text-primary transition">
                                        <?= esc($patientFullName) ?>
                                    </a>
                                    <div class="mt-1 flex items-center justify-between text-[11px] font-medium v3-text-muted">
                                        <span><?= esc($visit['patient_number'] ?? $visit['phone'] ?? '') ?></span>
                                        <span class="v3-text-muted"><?= (int) ($visit['duration'] ?? 30) ?> <?= esc(lang('UI.patientMinutes')) ?></span>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        <?php endforeach; ?>

        <!-- Separate Visible Legacy / Reassignment Column -->
        <div class="doctor-dayboard-column flex-shrink-0 flex flex-col v3-radius border-2 border-dashed v3-border-default v3-bg-waiting v3-elevation-none" style="flex: 0 0 320px; min-width: 280px; max-width: 380px;" data-dayboard-legacy-column="1">
            <!-- Legacy Header -->
            <div class="border-b v3-border-default v3-bg-panel v3-bg-panel v3-bg-panel p-4 rounded-t-2xl">
                <div class="flex items-start justify-between gap-2">
                    <div class="min-w-0">
                        <h4 class="truncate text-sm font-black v3-text-warning">
                            <i class="fas fa-triangle-exclamation v3-text-warning me-1.5"></i><?= esc(lang('DoctorScheduling.unassignedLegacyColumn')) ?>
                        </h4>
                        <p class="text-[11px] font-semibold v3-text-warning">
                            <?= esc(lang('DoctorScheduling.unassignedLegacyDesc')) ?>
                        </p>
                    </div>
                    <span class="inline-flex items-center rounded-full v3-bg-waiting px-2 py-0.5 text-xs font-black v3-text-warning">
                        <?= count($legacyAppointments) ?>
                    </span>
                </div>
            </div>

            <!-- Legacy Visits List -->
            <div class="flex-1 space-y-2.5 p-3 min-h-[160px] max-h-[520px] overflow-y-auto">
                <?php if (empty($legacyAppointments)): ?>
                    <div class="flex h-full min-h-[140px] items-center justify-center p-4 text-center">
                        <p class="text-xs font-semibold v3-text-warning">
                            <i class="fas fa-check-circle v3-text-warning text-lg block mb-1.5"></i>
                            <?= esc(lang('DoctorScheduling.noUnassignedAppointments')) ?>
                        </p>
                    </div>
                <?php else: ?>
                    <?php foreach ($legacyAppointments as $visit): ?>
                        <?php
                        $status = (string) ($visit['status'] ?? 'scheduled');
                        $colorClass = $statusColors[$status] ?? 'v3-bg-soft v3-text-ink v3-border-default';
                        $statusLabel = $statusLabelMap[$status] ?? ucfirst(str_replace('_', ' ', $status));
                        $visitTime = formatTime($visit['appointment_time'] ?? '');
                        $patientFullName = trim(($visit['first_name'] ?? '') . ' ' . ($visit['last_name'] ?? ''));
                        $storedChair = trim((string) ($visit['chair_name'] ?? ''));
                        ?>
                        <div class="v3-radius border v3-border-default v3-bg-panel p-3 shadow-xs transition v3-border-default hover:v3-elevation-none">
                            <div class="flex items-start justify-between gap-2">
                                <span class="inline-flex items-center rounded-lg v3-bg-waiting px-2 py-0.5 text-xs font-black v3-text-warning border v3-border-default" dir="ltr">
                                    <?= esc($visitTime) ?>
                                </span>
                                <span class="inline-flex items-center rounded-md border px-2 py-0.5 text-[11px] font-bold uppercase tracking-wider <?= esc($colorClass) ?>">
                                    <?= esc($statusLabel) ?>
                                </span>
                            </div>
                            <div class="mt-2 min-w-0">
                                <a href="<?= workflow_context_url('appointment/' . $visit['id']) ?>" class="block truncate text-sm font-black v3-text-ink v3-hover-text-warning transition">
                                    <?= esc($patientFullName) ?>
                                </a>
                                <div class="mt-1 flex flex-wrap items-center justify-between gap-1 text-[11px]">
                                    <span class="inline-flex items-center gap-1 font-semibold v3-text-warning v3-bg-waiting px-2 py-0.5 rounded-md">
                                        <i class="fas fa-chair v3-text-warning"></i>
                                        <?php if (!empty($visit['provider_user_id'])): ?><bdi dir="ltr">#<?= (int)$visit['provider_user_id'] ?></bdi><?php endif; ?>
                                        <?= esc(lang('DoctorScheduling.storedChairSnapshot', [$storedChair !== '' ? $storedChair : 'N/A'])) ?>
                                    </span>
                                    <?php if (has_permission('appointments', 'edit')): ?>
                                        <a href="<?= workflow_context_url('appointment/' . $visit['id'] . '/edit') ?>" class="inline-flex items-center gap-1 rounded-md v3-bg-warning px-2 py-0.5 text-[11px] font-bold v3-text-on-primary v3-hover-primary transition v3-btn v3-btn--secondary">
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
