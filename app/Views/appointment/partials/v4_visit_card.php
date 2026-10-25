<?php
$visitId=(int)$visit['id'];$visitPatientId=(int)($visit['patient_id']??0);$visitStatus=(string)($visit['status']??'scheduled');
$visitName=trim((string)($visit['first_name']??'').' '.(string)($visit['last_name']??''));
$visitType=strtolower(str_replace(' ','_',trim((string)($visit['appointment_type']??''))));
?>
<article class="v4-reception-visit">
<header><time dir="ltr"><?= esc(formatTime((string)$visit['appointment_time'])) ?></time><span><?= (int)($visit['duration']??0) ?> <?= esc(lang('UI.appointmentMinutes')) ?></span><details class="v4-row-menu"><summary class="v4-icon-button" aria-label="<?= esc(lang('UI.appointmentActions')) ?>"><i class="fas fa-ellipsis" aria-hidden="true"></i></summary><div>
<a href="<?= esc(workflow_context_url('appointment/'.$visitId,$returnToContext)) ?>"><?= esc(lang('UI.appointmentViewAction')) ?></a><?php if($receptionCan('appointments','edit')): ?><a href="<?= esc(workflow_context_url('appointment/'.$visitId.'/edit',$returnToContext)) ?>"><?= esc(lang('UI.appointmentEditAction')) ?></a><?php if($visitStatus==='scheduled'): ?><button type="button" onclick="confirmAppointment(<?= $visitId ?>)"><?= esc(lang('UI.appointmentConfirmAction')) ?></button><?php endif; ?><?php if(in_array($visitStatus,['scheduled','confirmed','arrived','ready'],true)): ?><button type="button" onclick="cancelAppointment(<?= $visitId ?>)" class="v4-destructive-text"><?= esc(lang('UI.cancel')) ?></button><?php endif; ?><?php endif; ?><?php if($receptionCan('appointments','delete')): ?><button type="button" onclick="deleteAppointment(<?= $visitId ?>)" class="v4-destructive-text"><?= esc(lang('UI.appointmentDeleteAction')) ?></button><?php endif; ?>
</div></details></header>
<a class="v4-reception-person" href="<?= esc(workflow_context_url('patient/'.$visitPatientId,$returnToContext)) ?>"><?= esc($visitName?:lang('UI.patientUnknown')) ?></a>
<div class="v4-reception-context"><span dir="ltr"><?= esc($visit['patient_number']??'') ?></span><span><?= esc(isset($typeLabels[$visitType])?lang($typeLabels[$visitType]):(string)($visit['appointment_type']??'')) ?></span></div>
<?php if(!empty($visit['phone'])): ?><p class="v4-reception-phone" dir="ltr"><?= esc($visit['phone']) ?></p><?php endif; ?>
<footer><span class="v4-badge"><?= esc(isset($stageLabels[$visitStatus])?lang($stageLabels[$visitStatus]):$visitStatus) ?></span><span><?= esc($visit['provider_display_label']??$visit['chair_name']??'') ?></span><?php if($viewAllMode): ?><span><?= esc(formatDate($visit['appointment_date'])) ?></span><?php endif; ?><a class="v4-text-link" href="<?= esc(workflow_context_url('appointment/'.$visitId,$returnToContext)) ?>"><?= esc(lang('V4.visitDetails')) ?><i class="fas fa-chevron-right" aria-hidden="true"></i></a></footer>
</article>
