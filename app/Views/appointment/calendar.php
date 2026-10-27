<?php helper('asset'); include APPPATH.'Views/layouts/partials/studio_icons.php'; ?>
<?= $this->extend('layouts/studio') ?>
<?= $this->section('styles') ?><link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.css"><link rel="stylesheet" href="<?= base_url('assets/css/studio-forms.css') ?>?v=<?= esc(asset_version('assets/css/studio-forms.css')) ?>"><link rel="stylesheet" href="<?= base_url('assets/css/studio-calendar.css') ?>?v=<?= esc(asset_version('assets/css/studio-calendar.css')) ?>"><?= $this->endSection() ?>
<?= $this->section('content') ?>
<?php
$calendarWorkingHours = is_array($workingHours ?? null) ? $workingHours : [];
$isRtl = !empty($is_rtl) || (service('request')->getLocale() === 'ar');
$currentUri = service('request')->getUri();
$returnToContext = ltrim($currentUri->getPath(), '/');
$currentQuery = $currentUri->getQuery();
if ($currentQuery !== '') {
    $returnToContext .= '?' . $currentQuery;
}

$normalizeCalendarTime = static function ($value, string $fallback): string {
    $raw = trim((string) $value);
    if ($raw === '') {
        $raw = $fallback;
    }

    if (preg_match('/^\d{1,2}:\d{2}(:\d{2})?$/', $raw) === 1) {
        [$hour, $minute, $second] = array_pad(explode(':', $raw), 3, '00');
        $hour = max(0, min(23, (int) $hour));
        $minute = max(0, min(59, (int) $minute));
        $second = max(0, min(59, (int) $second));
        return sprintf('%02d:%02d:%02d', $hour, $minute, $second);
    }

    $timestamp = strtotime('1970-01-01 ' . $raw);
    if ($timestamp === false) {
        $timestamp = strtotime('1970-01-01 ' . $fallback);
    }

    return $timestamp === false ? $fallback . ':00' : date('H:i:s', $timestamp);
};

$calendarTimeToSeconds = static function (string $time): int {
    [$hour, $minute, $second] = array_map('intval', explode(':', $time));
    return ($hour * 3600) + ($minute * 60) + $second;
};

$calendarSecondsToDuration = static function (int $seconds): string {
    if ($seconds >= 86400) {
        return '24:00:00';
    }

    $seconds = max(0, min(86399, $seconds));
    return gmdate('H:i:s', $seconds);
};

$calendarWorkingStart = $normalizeCalendarTime($calendarWorkingHours['start'] ?? '09:00', '09:00');
$calendarWorkingEnd = $normalizeCalendarTime($calendarWorkingHours['end'] ?? '17:00', '17:00');
$calendarWorkingStartSeconds = $calendarTimeToSeconds($calendarWorkingStart);
$calendarWorkingEndSeconds = $calendarTimeToSeconds($calendarWorkingEnd);

if ($calendarWorkingEndSeconds <= $calendarWorkingStartSeconds) {
    $calendarWorkingStart = '09:00:00';
    $calendarWorkingEnd = '17:00:00';
    $calendarWorkingStartSeconds = 9 * 3600;
    $calendarWorkingEndSeconds = 17 * 3600;
}

$calendarSlotMin = $calendarSecondsToDuration($calendarWorkingStartSeconds - 3600);
$calendarSlotMax = $calendarSecondsToDuration($calendarWorkingEndSeconds + 3600);
if ($calendarTimeToSeconds($calendarSlotMax === '24:00:00' ? '23:59:59' : $calendarSlotMax) <= $calendarTimeToSeconds($calendarSlotMin)) {
    $calendarSlotMin = '08:00:00';
    $calendarSlotMax = '18:00:00';
}

$rawWorkingDays = $calendarWorkingHours['days'] ?? ['monday', 'tuesday', 'wednesday', 'thursday', 'friday'];
if (is_string($rawWorkingDays)) {
    $decodedDays = json_decode($rawWorkingDays, true);
    $rawWorkingDays = is_array($decodedDays) ? $decodedDays : preg_split('/\s*,\s*/', $rawWorkingDays);
}
if (!is_array($rawWorkingDays) || $rawWorkingDays === []) {
    $rawWorkingDays = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday'];
}

$calendarDayMap = [
    'sunday' => 0,
    'monday' => 1,
    'tuesday' => 2,
    'wednesday' => 3,
    'thursday' => 4,
    'friday' => 5,
    'saturday' => 6,
];
$calendarBusinessDays = array_values(array_unique(array_map(
    static fn($day) => $calendarDayMap[strtolower(trim((string) $day))] ?? 1,
    $rawWorkingDays
)));
$calendarStats = is_array($stats ?? null) ? $stats : [];
$calendarTodayCount = (int) ($calendarStats['today_appointments'] ?? 0);
$calendarUpcomingCount = (int) ($calendarStats['upcoming_appointments'] ?? 0);
$calendarCompletedCount = (int) ($calendarStats['completed_appointments'] ?? 0);
$calendarCancelledCount = (int) ($calendarStats['cancelled_appointments'] ?? 0);
$calendarWorkHoursLabel = substr($calendarWorkingStart, 0, 5) . ' - ' . substr($calendarWorkingEnd, 0, 5);

$bookingContext = $booking_context ?? null;
if ($bookingContext === null && class_exists(\App\Services\ProviderDirectoryService::class)) {
    $activeClinicId = (int) (session('active_clinic_id') ?? 0);
    if ($activeClinicId > 0) {
        $providerService = new \App\Services\ProviderDirectoryService();
        $isDynamic = false;
        try {
            $isDynamic = $providerService->isEnabled($activeClinicId);
            $bookingContext = $providerService->getBookingContext($activeClinicId);
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
$dynamicMode = !empty($bookingContext['enabled']);
?>
<header class="page-title-row calendar-heading"><h1><?= esc(lang('UI.appointmentCalendarTitle')) ?></h1><nav><a class="button secondary" href="<?= base_url('appointments') ?>"><?= esc(lang('Studio.dayboard')) ?></a><?php if(has_permission('appointments','create')): ?><a class="button primary" href="<?= base_url('appointment/create?return_to=appointment%2Fcalendar') ?>"><?= $studioIcon('plus') ?><?= esc(lang('Studio.book')) ?></a><?php endif; ?></nav></header>
<details id="calendar-context-panel" class="calendar-context-disclosure" open><summary><?= esc(lang('Studio.calendarWorkingHours')) ?> <span dir="ltr"><?= esc($calendarWorkHoursLabel) ?></span></summary><section class="calendar-context" aria-label="<?= esc(lang('UI.appointmentTodaySchedule'),'attr') ?>"><div><strong><?= esc(lang('UI.appointmentTodaySchedule')) ?></strong><span><?= $calendarTodayCount ?></span></div><div><span><?= esc(lang('Studio.calendarUpcomingScheduled')) ?></span><strong><?= $calendarUpcomingCount ?></strong></div><div><span><?= esc(lang('Studio.calendarCompletedAllTime')) ?></span><strong><?= $calendarCompletedCount ?></strong></div><div><span><?= esc(lang('Studio.calendarCancelledAllTime')) ?></span><strong><?= $calendarCancelledCount ?></strong></div><div class="calendar-hours"><span><?= esc(lang('Studio.calendarWorkingHours')) ?></span><strong dir="ltr"><?= esc($calendarWorkHoursLabel) ?></strong></div></section></details>
<section class="calendar-workspace"><details id="calendar-filter-panel" open><summary><?= esc(lang('Studio.calendarFilters')) ?></summary><form class="calendar-filters"><div class="form-field"><label for="calendarSearchFilter"><?= esc(lang('UI.appointmentSearch')) ?></label><input type="search" id="calendarSearchFilter" placeholder="<?= esc(lang('UI.appointmentSearchPatientHint'),'attr') ?>" autocomplete="off"></div><div class="form-field"><label for="calendarStatusFilter"><?= esc(lang('UI.appointmentStatusLabel')) ?></label><select id="calendarStatusFilter"><option value=""><?= esc(lang('UI.appointmentAllStatuses')) ?></option><?php foreach(['scheduled'=>'appointmentStatusScheduled','confirmed'=>'appointmentStatusConfirmed','completed'=>'dashboardCompleted','cancelled'=>'appointmentStatusCancelled','no_show'=>'appointmentStatusNoShow','arrived'=>'appointmentStageArrived','ready'=>'appointmentStageReady','in_chair'=>'appointmentStageInChair','checkout'=>'appointmentStageCheckout'] as $value=>$key): ?><option value="<?= $value ?>"><?= esc(lang('UI.'.$key)) ?></option><?php endforeach; ?></select></div><div class="form-field"><label for="calendarTypeFilter"><?= esc(lang('UI.appointmentTypeLabel')) ?></label><select id="calendarTypeFilter"><option value=""><?= esc(lang('UI.appointmentViewAll')) ?></option><?php foreach(['Consultation'=>'Consultation','Treatment'=>'Treatment','Follow Up'=>'FollowUp','Emergency'=>'Emergency','Cleaning'=>'Cleaning','Checkup'=>'Checkup'] as $value=>$key): ?><option value="<?= $value ?>"><?= esc(lang('UI.appointmentType'.$key)) ?></option><?php endforeach; ?></select></div><button type="button" id="calendarClearFilters" class="button secondary"><?= esc(lang('UI.appointmentClearAction')) ?></button></form></details>
<div class="calendar-feedback"><button type="button" id="calendar-refresh" class="button secondary"><?= esc(lang('Studio.calendarRefresh')) ?></button><span id="loading-indicator" class="hidden" role="status"><?= esc(lang('UI.appointmentLoadingAppointments')) ?></span><div id="calendar-error" class="form-notice form-notice--error" role="alert" hidden><span></span><button id="calendar-retry" type="button" class="button secondary"><?= esc(lang('Studio.calendarRetry')) ?></button></div></div>
<div id="calendar" aria-label="<?= esc(lang('UI.appointmentCalendarTitle'),'attr') ?>"></div></section>
<dialog id="appointmentCalendarDetailsModal" class="calendar-details" aria-labelledby="appointmentCalendarDetailsTitle"><header><div><h2 id="appointmentCalendarDetailsTitle"><?= esc(lang('UI.appointmentDetails')) ?></h2><p id="appointmentCalendarDetailsSubtitle"></p></div><button class="icon-button" type="button" data-calendar-details-close aria-label="<?= esc(lang('Studio.close'),'attr') ?>"><?= $studioIcon('close') ?></button></header><div class="calendar-details-body"><section class="calendar-visit-time"><strong id="appointmentCalendarDetailsTime" dir="ltr"></strong><span id="appointmentCalendarDetailsDate"></span><span id="appointmentCalendarDetailsStatus"></span></section><dl class="calendar-details-facts"><?php foreach(['Patient'=>'appointmentPatientInfo','Type'=>'appointmentTypeLabel','Duration'=>'appointmentDurationLabel','Doctor'=>'appointmentDoctorLabel','BookedBy'=>'appointmentBookedByLabel'] as $key=>$label): ?><div><dt><?= esc(lang('UI.'.$label)) ?></dt><dd id="appointmentCalendarDetails<?= $key ?>"></dd></div><?php endforeach; ?></dl></div><footer><a id="appointmentCalendarDetailsOpenLink" class="button primary" href="#"><?= esc(lang('UI.openFullPage')) ?></a><?php if(has_permission('patients','view')): ?><a id="appointmentCalendarDetailsPatientLink" class="button secondary" href="#"><?= esc(lang('UI.patientView')) ?></a><?php endif; ?><?php if(has_permission('appointments','edit')): ?><a id="appointmentCalendarDetailsEditLink" class="button secondary" href="#"><?= esc(lang('UI.appointmentEdit')) ?></a><?php endif; ?><button class="button secondary" type="button" data-calendar-details-close><?= esc(lang('Studio.close')) ?></button></footer></dialog>
<?php if($dynamicMode): ?><?= view('appointment/partials/doctor_dayboard',['appointments'=>$dayboard_appointments??$appointments??[],'selected_date'=>$selected_date??date('Y-m-d'),'booking_context'=>$bookingContext??null]) ?><?php endif; ?>
<?= $this->endSection() ?>
<?= $this->section('scripts') ?><script src="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.js"></script><script src="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/locales-all.min.js"></script><?php include APPPATH.'Views/appointment/partials/calendar_runtime.php'; ?><?= $this->endSection() ?>
