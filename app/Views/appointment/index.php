<?php include APPPATH . 'Views/layouts/partials/view_permissions.php'; ?>
<?= $this->extend('layouts/main_auth') ?>
<?= $this->section('content') ?>
<?php
helper('asset');
$isRtl=!empty($is_rtl);$viewAllMode=($view_mode??'')==='all';$selectedDate=(string)($selected_date??date('Y-m-d'));$appointmentRows=array_values($appointments??[]);
$returnToContext=ltrim(uri_string(),'/');$query=service('request')->getUri()->getQuery();if($query!=='')$returnToContext.='?'.$query;
$receptionCan=$v4Can;
$stageLabels=['scheduled'=>'UI.appointmentStageScheduled','confirmed'=>'UI.appointmentStageConfirmed','arrived'=>'UI.appointmentStageArrived','ready'=>'UI.appointmentStageReady','in_chair'=>'UI.appointmentStageInChair','checkout'=>'UI.appointmentStageCheckout','completed'=>'UI.appointmentStageCompleted','cancelled'=>'UI.appointmentStageCancelled','no_show'=>'UI.appointmentStageNoShow'];
$typeLabels=['consultation'=>'UI.appointmentTypeConsultation','treatment'=>'UI.appointmentTypeTreatment','follow_up'=>'UI.appointmentTypeFollowUp','emergency'=>'UI.appointmentTypeEmergency','cleaning'=>'UI.appointmentTypeCleaning','checkup'=>'UI.appointmentTypeCheckup'];
$lanes=['scheduled'=>['label'=>'UI.appointmentStageScheduled','rows'=>[]],'arrived'=>['label'=>'V4.waitingVisits','rows'=>[]],'in_chair'=>['label'=>'UI.appointmentStageInChair','rows'=>[]],'checkout'=>['label'=>'UI.appointmentStageCheckout','rows'=>[]],'completed'=>['label'=>'UI.appointmentStageCompleted','rows'=>[]]];$closed=[];
foreach($appointmentRows as $row){$stage=(string)($row['status']??'scheduled');$lane=match($stage){'confirmed'=>'scheduled','ready'=>'arrived',default=>$stage};if(isset($lanes[$lane]))$lanes[$lane]['rows'][]=$row;else $closed[]=$row;}
foreach($lanes as &$lane){usort($lane['rows'],static fn($a,$b)=>strcmp((string)$a['appointment_time'],(string)$b['appointment_time']));}unset($lane);
$dateUrl=static fn($day)=>base_url('appointment').'?'.http_build_query(['date'=>$day]);
?>
<link rel="stylesheet" href="<?= base_url('assets/css/v4-reception.css') ?>?v=<?= esc(asset_version('assets/css/v4-reception.css')) ?>">
<div class="v4-reception-workspace">
<header class="v4-page-heading"><div><h1><?= esc(lang($viewAllMode?'UI.appointmentAllAppointments':'V4.receptionTitle')) ?></h1><p><?= esc(lang($viewAllMode?'UI.appointmentAllAppointmentsSubtitle':'V4.receptionHelp')) ?></p></div><div class="v4-page-actions"><a class="v4-button" href="<?= base_url('appointment/calendar') ?>"><i class="fas fa-calendar" aria-hidden="true"></i><?= esc(lang('UI.appointmentCalendarView')) ?></a><?php if($receptionCan('appointments','create')): ?><a id="newAppointmentModalTrigger" class="v4-button v4-button--primary" href="<?= base_url('appointment/create') ?>" data-appointment-create-modal-open><i class="fas fa-plus" aria-hidden="true"></i><?= esc(lang('UI.appointmentNew')) ?></a><?php endif; ?></div></header>
<?php if(session('error')): ?><div class="v4-form-error" role="alert"><?= esc(session('error')) ?></div><?php endif; ?>
<form method="get" action="<?= base_url('appointment') ?>" class="v4-reception-toolbar v4-form">
<?php if($viewAllMode): ?><input type="hidden" name="view" value="all"><?php else: ?><div class="v4-reception-date"><a class="v4-icon-button" href="<?= esc($dateUrl(date('Y-m-d',strtotime($selectedDate.' -1 day')))) ?>" aria-label="<?= esc(lang('V4.previousDay')) ?>"><i class="fas fa-chevron-left" aria-hidden="true"></i></a><label><span class="sr-only"><?= esc(lang('UI.appointmentDate')) ?></span><input name="date" type="date" value="<?= esc($selectedDate,'attr') ?>"></label><a class="v4-icon-button" href="<?= esc($dateUrl(date('Y-m-d',strtotime($selectedDate.' +1 day')))) ?>" aria-label="<?= esc(lang('V4.nextDay')) ?>"><i class="fas fa-chevron-right" aria-hidden="true"></i></a></div><?php endif; ?>
<label class="v4-reception-search"><span class="sr-only"><?= esc(lang('UI.appointmentSearch')) ?></span><i class="fas fa-search" aria-hidden="true"></i><input name="search" type="search" value="<?= esc($search_term??'','attr') ?>" placeholder="<?= esc(lang('UI.appointmentSearchPatientHint'),'attr') ?>"></label>
<label><span class="sr-only"><?= esc(lang('UI.appointmentStatusLabel')) ?></span><select name="status" data-no-select2><option value=""><?= esc(lang('UI.appointmentAllStatuses')) ?></option><?php foreach($stageLabels as $state=>$label): ?><option value="<?= $state ?>" <?= ($selected_status??'')===$state?'selected':'' ?>><?= esc(lang($label)) ?></option><?php endforeach; ?></select></label><button type="submit" class="v4-button"><?= esc(lang('V4.applyFilters')) ?></button><a class="v4-text-link" href="<?= base_url($viewAllMode?'appointment':'appointment?view=all') ?>"><?= esc(lang($viewAllMode?'UI.appointmentDailyView':'UI.appointmentViewAll')) ?></a>
</form>
<?php if(!$appointmentRows): ?><section class="v4-surface v4-reception-empty"><i class="fas fa-calendar-check" aria-hidden="true"></i><h2><?= esc(lang('UI.appointmentNoAppointmentsFound')) ?></h2><p><?= esc(lang('V4.receptionEmptyHelp')) ?></p><?php if($receptionCan('appointments','create')): ?><a class="v4-button v4-button--primary" href="<?= base_url('appointment/create?'.http_build_query(['date'=>$selectedDate,'return_to'=>$returnToContext])) ?>" data-appointment-create-modal-open data-appointment-date="<?= esc($selectedDate,'attr') ?>"><?= esc(lang('UI.appointmentNew')) ?></a><?php endif; ?></section>
<?php elseif($viewAllMode): ?><section class="v4-reception-records" aria-label="<?= esc(lang('UI.appointmentAllAppointments')) ?>"><?php foreach($appointmentRows as $visit): ?><?php include APPPATH.'Views/appointment/partials/v4_visit_card.php'; ?><?php endforeach; ?></section>
<?php else: ?><div class="v4-reception-board"><?php foreach($lanes as $state=>$lane): ?><section class="v4-reception-lane v4-reception-lane--<?= $state ?>"><header><h2><?= esc(lang($lane['label'])) ?></h2><span><?= count($lane['rows']) ?></span></header><?php if(!$lane['rows']): ?><p class="v4-reception-lane-empty"><?= esc(lang('V4.noVisitsInStage')) ?></p><?php else: ?><?php foreach($lane['rows'] as $visit): ?><?php include APPPATH.'Views/appointment/partials/v4_visit_card.php'; ?><?php endforeach; ?><?php endif; ?></section><?php endforeach; ?></div>
<?php if($closed): ?><details class="v4-reception-closed"><summary><?= esc(lang('V4.otherVisitStates')) ?> <b><?= count($closed) ?></b></summary><div class="v4-reception-records"><?php foreach($closed as $visit): ?><?php include APPPATH.'Views/appointment/partials/v4_visit_card.php'; ?><?php endforeach; ?></div></details><?php endif; ?><?php endif; ?>
<?php if($viewAllMode): ?><?php $currentPage=max(1,(int)($pagination['current_page']??1));$pageUrl=static fn($page)=>base_url('appointment').'?'.http_build_query(['view'=>'all','search'=>$search_term??'','status'=>$selected_status??'','page'=>$page]); ?><nav class="v4-reception-pagination" aria-label="<?= esc(lang('UI.patientActions')) ?>"><span><?= esc(lang('UI.appointmentCountLabel',['count'=>(int)($pagination['total_count']??count($appointmentRows))])) ?></span><?php if($currentPage>1): ?><a class="v4-button" href="<?= esc($pageUrl($currentPage-1)) ?>"><?= esc(lang('UI.patientPaginatePrevious')) ?></a><?php endif; ?><strong><?= $currentPage ?></strong><?php if(!empty($pagination['has_more'])): ?><a class="v4-button" href="<?= esc($pageUrl($currentPage+1)) ?>"><?= esc(lang('UI.patientPaginateNext')) ?></a><?php endif; ?></nav><?php endif; ?>
<?php if(!empty($booking_context['enabled'])&&!$viewAllMode): ?><?= view('appointment/partials/doctor_dayboard',['appointments'=>$dayboard_appointments??[],'selected_date'=>$selectedDate,'booking_context'=>$booking_context]) ?><?php endif; ?>
</div>
<?= view('appointment/partials/quick_create_modal',['isRtl'=>$isRtl,'return_to'=>$returnToContext,'contextual'=>false,'selected_patient'=>$quick_create_patient??null,'chair_options'=>$chair_options??[],'booking_context'=>$booking_context??null,'requested_date'=>old('appointment_date',$selectedDate),'appointment_type_prefill'=>old('appointment_type','')]) ?>
<script>
const appointmentI18n = {
    confirmPrompt: <?= json_encode(lang('UI.appointmentConfirmPrompt')) ?>,
    completePrompt: <?= json_encode(lang('UI.appointmentCompletePrompt')) ?>,
    cancelPrompt: <?= json_encode(lang('UI.appointmentCancelPrompt')) ?>,
    deletePrompt: <?= json_encode(lang('UI.appointmentDeletePrompt')) ?>,
    confirmSuccess: <?= json_encode(lang('UI.appointmentConfirmSuccess')) ?>,
    completeSuccess: <?= json_encode(lang('UI.appointmentCompleteSuccess')) ?>,
    cancelSuccess: <?= json_encode(lang('UI.appointmentCancelSuccess')) ?>,
    deleteSuccess: <?= json_encode(lang('UI.appointmentDeleteSuccess')) ?>,
    errorPrefix: <?= json_encode(lang('UI.appointmentErrorPrefix')) ?>,
    confirmError: <?= json_encode(lang('UI.appointmentConfirmError')) ?>,
    completeError: <?= json_encode(lang('UI.appointmentCompleteError')) ?>,
    cancelError: <?= json_encode(lang('UI.appointmentCancelError')) ?>,
    deleteError: <?= json_encode(lang('UI.appointmentDeleteError')) ?>,
    loadMoreError: <?= json_encode(lang('UI.appointmentLoadMoreError')) ?>,
    loadError: <?= json_encode(lang('UI.appointmentLoadError')) ?>,
    statusScheduled: <?= json_encode(lang('UI.appointmentStatusScheduled')) ?>,
    statusConfirmed: <?= json_encode(lang('UI.appointmentStatusConfirmed')) ?>,
    statusArrived: <?= json_encode(lang('UI.appointmentStageArrived')) ?>,
    statusReady: <?= json_encode(lang('UI.appointmentStageReady')) ?>,
    statusInChair: <?= json_encode(lang('UI.appointmentStageInChair')) ?>,
    statusCheckout: <?= json_encode(lang('UI.appointmentStageCheckout')) ?>,
    statusCompleted: <?= json_encode(lang('UI.patientTreatmentStatusCompleted')) ?>,
    statusCancelled: <?= json_encode(lang('UI.appointmentStatusCancelled')) ?>,
    statusNoShow: <?= json_encode(lang('UI.appointmentStatusNoShow')) ?>,
    minutes: <?= json_encode(lang('UI.appointmentMinutes')) ?>,
    chairDefault: <?= json_encode(lang('UI.appointmentChairDefault')) ?>,
    typeBadge: <?= json_encode(lang('UI.appointmentTypeBadge')) ?>,
    statusBadge: <?= json_encode(lang('UI.appointmentStatusLabel')) ?>,
    viewAction: <?= json_encode(lang('UI.appointmentViewAction')) ?>,
    editAction: <?= json_encode(lang('UI.appointmentEditAction')) ?>
};


function showNotification(message,type='info'){window.showToast(type,<?= json_encode(lang('UI.appointmentManagement'),JSON_HEX_TAG|JSON_HEX_AMP|JSON_HEX_APOS|JSON_HEX_QUOT) ?>,message);}
function confirmAppointment(id) {
    if (confirm(appointmentI18n.confirmPrompt)) {
        fetch(`<?= base_url('appointment') ?>/${id}/confirm`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                [window.csrfConfig.header]: window.getCsrfToken()
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.csrf_token) {
                window.refreshCsrfToken(data.csrf_token);
            }
            if (data.success) {
                showNotification(appointmentI18n.confirmSuccess, 'success');
                setTimeout(() => location.reload(), 1500);
            } else {
                showNotification(appointmentI18n.errorPrefix + data.message, 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showNotification(appointmentI18n.confirmError, 'error');
        });
    }
}

function completeAppointment(id) {
    if (confirm(appointmentI18n.completePrompt)) {
        fetch(`<?= base_url('appointment') ?>/${id}/complete`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                [window.csrfConfig.header]: window.getCsrfToken()
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.csrf_token) {
                window.refreshCsrfToken(data.csrf_token);
            }
            if (data.success) {
                showNotification(appointmentI18n.completeSuccess, 'success');
                setTimeout(() => location.reload(), 1500);
            } else {
                showNotification(appointmentI18n.errorPrefix + data.message, 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showNotification(appointmentI18n.completeError, 'error');
        });
    }
}

function cancelAppointment(id) {
    if (confirm(appointmentI18n.cancelPrompt)) {
        fetch(`<?= base_url('appointment') ?>/${id}/cancel`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                [window.csrfConfig.header]: window.getCsrfToken()
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.csrf_token) {
                window.refreshCsrfToken(data.csrf_token);
            }
            if (data.success) {
                showNotification(appointmentI18n.cancelSuccess, 'success');
                setTimeout(() => location.reload(), 1500);
            } else {
                showNotification(appointmentI18n.errorPrefix + data.message, 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showNotification(appointmentI18n.cancelError, 'error');
        });
    }
}

function deleteAppointment(id) {
    if (confirm(appointmentI18n.deletePrompt)) {
        fetch(`<?= base_url('appointment') ?>/${id}`, {
            method: 'DELETE',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                [window.csrfConfig.header]: window.getCsrfToken()
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.csrf_token) {
                window.refreshCsrfToken(data.csrf_token);
            }
            if (data.success) {
                showNotification(appointmentI18n.deleteSuccess, 'success');
                setTimeout(() => location.reload(), 1500);
            } else {
                showNotification(appointmentI18n.errorPrefix + data.message, 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showNotification(appointmentI18n.deleteError, 'error');
        });
    }
}


document.addEventListener('click',event=>document.querySelectorAll('.v4-row-menu[open]').forEach(menu=>{if(!menu.contains(event.target))menu.open=false;}));
document.addEventListener('keydown',event=>{if(event.key==='Escape'){const menu=document.querySelector('.v4-row-menu[open]');if(menu){menu.open=false;menu.querySelector('summary')?.focus();}}});
</script>
<?= $this->endSection() ?>
