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
    <section class="form-notice form-notice--error doctor-schema-notice"><h2><?= esc(lang('DoctorScheduling.schemaNotReadyTitle')) ?></h2><p><?= esc(lang('DoctorScheduling.schemaNotReady')) ?></p></section>
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

<section class="doctor-dayboard-container" data-doctor-dayboard="1" data-chosen-day="<?= esc($chosenDay,'attr') ?>"><header><div><h2><?= esc(lang('DoctorScheduling.doctorDayboard')) ?></h2><p><?= esc(formatDate($chosenDay)) ?></p></div><span class="calendar-status"><?= count($activeDoctors) ?> <?= esc(lang('DoctorScheduling.dayboardAllDoctors')) ?></span></header><div class="doctor-dayboard-track" role="region" tabindex="0" aria-label="<?= esc(lang('DoctorScheduling.doctorDayboard'),'attr') ?>">
<?php $columns=array_values($activeDoctors);$columns[]=['user_id'=>null,'name'=>lang('DoctorScheduling.unassignedLegacyColumn'),'appointments'=>$legacyAppointments];foreach($columns as $doctor):$legacy=$doctor['user_id']===null; ?>
<section class="doctor-dayboard-column <?= $legacy?'doctor-dayboard-column--legacy':'' ?>" <?= $legacy?'data-dayboard-legacy-column="1"':'data-provider-user-id="'.(int)$doctor['user_id'].'"' ?>><header><h3><?= esc($legacy?$doctor['name']:\App\Libraries\ProviderDisplayLabel::forOption($doctor['user_id'],$doctor['name'])) ?></h3><p><?= count($doctor['appointments']) ?> <?= esc(lang('UI.menuAppointments')) ?></p><?php if($legacy): ?><p><?= esc(lang('DoctorScheduling.unassignedLegacyDesc')) ?></p><?php elseif(has_permission('appointments','create')): ?><a class="button secondary" href="<?= base_url('appointment/create?'.http_build_query(['provider_user_id'=>(int)$doctor['user_id'],'appointment_date'=>$chosenDay,'return_to'=>'appointment/calendar?date='.$chosenDay])) ?>"><?= esc(lang('DoctorScheduling.newBooking')) ?></a><?php endif; ?></header><div class="doctor-visits">
<?php if(!$doctor['appointments']): ?><p class="doctor-empty"><?= esc(lang($legacy?'DoctorScheduling.noUnassignedAppointments':'DoctorScheduling.noAppointmentsForDoctor')) ?></p><?php endif; ?>
<?php foreach($doctor['appointments'] as $visit):$status=(string)($visit['status']??'scheduled');$statusClass=in_array($status,['scheduled','confirmed','completed','cancelled','no_show','arrived','ready','in_chair','checkout'],true)?$status:'unknown'; ?><article class="doctor-visit"><a href="<?= esc(workflow_context_url('appointment/'.(int)$visit['id']),'attr') ?>"><div><strong dir="ltr"><?= esc(formatTime($visit['appointment_time']??'')) ?></strong><span class="calendar-status calendar-status--<?= $statusClass ?>"><?= esc($statusLabelMap[$status]??$status) ?></span></div><h4><?= esc(trim(($visit['first_name']??'').' '.($visit['last_name']??''))) ?></h4><p><span><?= esc($visit['patient_number']??$visit['phone']??'') ?></span><span><?= (int)($visit['duration']??30) ?> <?= esc(lang('UI.patientMinutes')) ?></span></p><?php if($legacy): ?><p><?php if(!empty($visit['provider_user_id'])): ?><bdi dir="ltr">#<?= (int)$visit['provider_user_id'] ?></bdi><?php endif; ?> <?= esc(lang('DoctorScheduling.storedChairSnapshot',[trim((string)($visit['chair_name']??''))?:'N/A'])) ?></p><?php endif; ?></a><?php if($legacy&&has_permission('appointments','edit')): ?><a class="button secondary" href="<?= esc(workflow_context_url('appointment/'.(int)$visit['id'].'/edit'),'attr') ?>"><?= esc(lang('DoctorScheduling.reassign')) ?></a><?php endif; ?></article><?php endforeach; ?></div></section><?php endforeach; ?></div></section>
