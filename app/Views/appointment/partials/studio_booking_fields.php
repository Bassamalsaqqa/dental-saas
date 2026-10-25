<?php
$editing=!empty($editing);$appointment=$appointment??[];
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
$schemaReady = !isset($bookingContext['schema_ready']) || !empty($bookingContext['schema_ready']);
$providers = is_array($bookingContext['providers'] ?? null) ? $bookingContext['providers'] : ($clinic_providers ?? ($providers ?? []));
$chairOptions = is_array($bookingContext['chair_options'] ?? null) ? $bookingContext['chair_options'] : ($chair_options ?? []);

$currentProviderUserId=!empty($appointment['provider_user_id'])?(int)$appointment['provider_user_id']:null;
$storedChairName=trim((string)($appointment['chair_name']??''));$isCurrentProviderActive=false;
foreach($providers as $provider){if((int)($provider['user_id']??$provider['id']??0)===$currentProviderUserId){$isCurrentProviderActive=true;break;}}
$isLegacyOrInactive=$editing&&($currentProviderUserId===null||!$isCurrentProviderActive);
$selectedProviderId=old('provider_user_id',$editing?($currentProviderUserId??''):($provider_id_prefill??''));if($selectedProviderId!=='')$selectedProviderId=(int)$selectedProviderId;
$selectedChair=old('chair_name',$editing?($appointment['chair_name']??($chairOptions[0]??'')):($chairOptions[0]??''));
$selectedDuration=(int)old('duration',$appointment['duration']??(int)getAppointmentDuration());$durationOptions=[30,45,60,90,120,180];if($selectedDuration>0&&!in_array($selectedDuration,$durationOptions,true)){$durationOptions[]=$selectedDuration;sort($durationOptions,SORT_NUMERIC);}
$selectedPatient=$selected_patient??null;if($editing&&!$selectedPatient&&isset($appointment['patient_id']))$selectedPatient=['id'=>$appointment['patient_id'],'first_name'=>$appointment['first_name']??'','last_name'=>$appointment['last_name']??'','phone'=>$appointment['phone']??''];
$patientId=(string)old('patient_id',$selectedPatient['id']??'');$type=(string)old('appointment_type',$appointment['appointment_type']??$appointment_type_prefill??'');
?>
<section class="form-section"><header><h2><?= esc(lang('Studio.bookingPatient')) ?></h2></header><div class="booking-person-grid">
<div class="form-field"><label for="patientSelect"><?= esc(lang('UI.appointmentPatientRequired')) ?></label><select id="patientSelect" name="patient_id" required data-studio-patient-picker data-search-empty="<?= esc(lang('Studio.searchEmpty'),'attr') ?>" data-search-loading="<?= esc(lang('Studio.loadingList'),'attr') ?>" data-search-failed="<?= esc(lang('Studio.searchFail'),'attr') ?>" data-url="<?= base_url('api/search/patients') ?>" data-placeholder="<?= esc(lang('UI.appointmentSearchPatientPlaceholder'),'attr') ?>"><option value=""><?= esc(lang('UI.appointmentSelectPatient')) ?></option><?php if($patientId!==''): ?><option value="<?= esc($patientId,'attr') ?>" selected><?= esc($selectedPatient&&(string)$selectedPatient['id']===$patientId?trim(($selectedPatient['first_name']??'').' '.($selectedPatient['last_name']??'')).' · '.($selectedPatient['phone']??$patientId):lang('Studio.patientNumber',['id'=>$patientId])) ?></option><?php endif; ?></select></div>
<div class="form-field"><label for="appointmentType"><?= esc(lang('UI.appointmentTypeRequired')) ?></label><select id="appointmentType" name="appointment_type" required><option value=""><?= esc(lang('UI.appointmentSelectType')) ?></option><?php foreach(['consultation','treatment','follow_up','emergency','cleaning','checkup'] as $value): ?><option value="<?= $value ?>" <?= $type===$value?'selected':'' ?>><?= esc(lang('UI.appointmentType'.str_replace(' ','',ucwords(str_replace('_',' ',$value))))) ?></option><?php endforeach; ?></select></div></div></section>
<section class="form-section"><header><h2><?= esc(lang('Studio.bookingWhen')) ?></h2></header><div class="booking-resource-grid">
<div class="form-field"><?php if($dynamicMode): ?><label for="providerSelect"><?= esc(lang('DoctorScheduling.doctorRequired')) ?> *</label>
<?php if($isLegacyOrInactive): ?><p class="booking-warning"><?= esc(lang('DoctorScheduling.legacyProviderNotice')) ?> <?= esc($storedChairName) ?></p><?php endif; ?>
<select id="providerSelect" name="provider_user_id" data-appointment-provider <?= !$schemaReady||!$providers?'disabled required':($editing&&$currentProviderUserId===null?'':'required') ?>>
<?php if(!$schemaReady): ?><option value="<?= (int)($currentProviderUserId??0)?:'' ?>"><?= esc(lang('DoctorScheduling.schemaNotReadyTitle')) ?></option>
<?php elseif(!$providers): ?><option value="<?= (int)($currentProviderUserId??0)?:'' ?>"><?= esc(lang('DoctorScheduling.noEligibleProviders')) ?></option>
<?php else: ?><?php if($editing&&$currentProviderUserId!==null&&!$isCurrentProviderActive): ?><option value="<?= $currentProviderUserId ?>" <?= $selectedProviderId===$currentProviderUserId?'selected':'' ?>><?= esc(\App\Libraries\ProviderDisplayLabel::forInactiveOption($currentProviderUserId,$storedChairName)) ?></option><?php elseif($editing&&$currentProviderUserId===null&&$storedChairName!==''): ?><option value="" <?= $selectedProviderId===''?'selected':'' ?>><?= esc(\App\Libraries\ProviderDisplayLabel::forUnassignedOption($storedChairName)) ?></option><?php else: ?><option value=""><?= esc(lang('DoctorScheduling.selectDoctor')) ?></option><?php endif; ?>
<?php foreach($providers as $provider):$pId=(int)($provider['user_id']??$provider['id']??0);$pName=(string)($provider['name']??trim(($provider['first_name']??'').' '.($provider['last_name']??''))); ?><option value="<?= $pId ?>" <?= $selectedProviderId===$pId?'selected':'' ?>><?= esc(\App\Libraries\ProviderDisplayLabel::forOption($pId,$pName)) ?></option><?php endforeach;endif; ?></select>
<?php else: ?><label for="chairName"><?= esc(lang('UI.appointmentDoctorLabel')) ?> *</label><select id="chairName" name="chair_name" data-appointment-chair required><option value=""><?= esc(lang('UI.appointmentSelectDoctor')) ?></option><?php if($editing&&$selectedChair!==''&&!in_array($selectedChair,$chairOptions,true)): ?><option value="<?= esc($selectedChair,'attr') ?>" selected><?= esc($selectedChair) ?></option><?php endif; ?><?php foreach($chairOptions as $chair): ?><option value="<?= esc($chair,'attr') ?>" <?= $selectedChair===$chair?'selected':'' ?>><?= esc($chair) ?></option><?php endforeach; ?></select><?php endif; ?></div>
<div class="form-field"><label for="appointmentDate"><?= esc(lang('UI.appointmentDateRequired')) ?></label><input id="appointmentDate" type="date" name="appointment_date" value="<?= esc(old('appointment_date',$appointment['appointment_date']??$requested_date??date('Y-m-d')),'attr') ?>" required></div>
<div class="form-field"><label for="duration"><?= esc(lang('UI.appointmentDurationRequired')) ?></label><select id="duration" name="duration" required><?php foreach($durationOptions as $option): ?><option value="<?= $option ?>" <?= $selectedDuration===$option?'selected':'' ?>><?= $option ?> <?= esc(lang('UI.patientMinutes')) ?></option><?php endforeach; ?></select></div>
<div class="form-field"><label for="appointmentTime"><?= esc(lang('UI.appointmentTimeRequired')) ?></label><select id="appointmentTime" name="appointment_time" required><option value=""><?= esc(lang('UI.appointmentSelectTime')) ?></option></select><p id="booking-slot-feedback" class="field-error" role="status" hidden></p><button class="button secondary" id="booking-slot-retry" type="button" hidden><?= esc(lang('Studio.retry')) ?></button></div></div></section>
<section class="form-section"><div class="form-field"><label for="booking-notes"><?= esc(lang('UI.appointmentAdditionalNotes')) ?></label><textarea id="booking-notes" name="notes" rows="3"><?= esc(old('notes',$appointment['notes']??'')) ?></textarea></div></section>
<script id="booking-config" type="application/json"><?= json_encode(['url'=>base_url('appointment/available-time-slots'),'editing'=>$editing,'id'=>(int)($appointment['id']??0),'originalDate'=>$appointment['appointment_date']??'','originalDuration'=>(int)($appointment['duration']??0),'originalResource'=>$dynamicMode?(string)($currentProviderUserId??''):$storedChairName,'originalTime'=>$appointment['appointment_time']??'','selectedTime'=>old('appointment_time',$appointment['appointment_time']??''),'labels'=>['select'=>lang('UI.appointmentSelectTime'),'loading'=>lang('UI.appointmentLoadingTimeSlots'),'empty'=>lang('UI.appointmentNoAvailableSlots'),'prerequisites'=>lang('UI.appointmentSelectPrerequisites'),'failed'=>lang('UI.appointmentFailedLoadingSlots'),'current'=>lang('Studio.currentTime')]],JSON_HEX_TAG|JSON_HEX_AMP|JSON_HEX_APOS|JSON_HEX_QUOT|JSON_UNESCAPED_UNICODE) ?></script>
