<?php
$studioFormErrors=$studioFormErrors??[];
$studioEditing=!empty($studioEditing);
$studioPatient=is_array($studioPatient??null)?$studioPatient:[];
$studioValue=static function(string $name)use($studioPatient):string{
    $value=old($name,$studioPatient[$name]??'');
    return is_scalar($value)?(string)$value:'';
};
$studioChecked=static function(string $name)use($studioPatient):bool{
    $value=old($name,$studioPatient[$name]??false);
    return in_array($value,['1',1,true,'true','on','yes'],true);
};
$studioContactFields=['email'=>'patientEmail','city'=>'patientCity'];
if(!$studioEditing)$studioContactFields+=['insurance_provider'=>'patientInsuranceProvider','insurance_number'=>'patientInsuranceNumber'];
$optionalKeys=['email','city','insurance_provider','insurance_number','notes','alert_has_allergies','alert_allergies_details','alert_taking_medications','alert_medications_details','alert_blood_thinners','alert_blood_thinners_details','alert_diabetes','alert_diabetes_details','alert_high_blood_pressure','alert_blood_pressure_details','alert_heart_condition','alert_heart_condition_details','alert_pregnant','alert_pregnancy_details','alert_anesthesia_problem','alert_anesthesia_problem_details'];
$optionalEnabled=$studioEditing;foreach($optionalKeys as $key){if($studioValue($key)!==''){$optionalEnabled=true;break;}}
$dob=$studioValue('date_of_birth');$dobValue=$dob;
if($dob!==''&&!preg_match('/^\d{4}-\d{2}-\d{2}$/',$dob)){
 $date=\DateTimeImmutable::createFromFormat('!'.(string)settings()->get('date_format','d/m/Y'),$dob);
 $dateErrors=\DateTimeImmutable::getLastErrors();
 $dobValue=$date&&(!$dateErrors||(!$dateErrors['warning_count']&&!$dateErrors['error_count']))?$date->format('Y-m-d'):'';
}
$medicalAlerts=[
 ['alert_has_allergies','patientAnyAllergies','alert_allergies_details','patientAllergyDetails','textarea',false],
 ['alert_taking_medications','patientTakingMedications','alert_medications_details','patientMedicationDetails','textarea',false],
 ['alert_blood_thinners','patientTakingBloodThinners','alert_blood_thinners_details','patientBloodThinnerDetails','input',false],
 ['alert_diabetes','patientDiabetes','alert_diabetes_details','patientDiabetesDetails','input',false],
 ['alert_high_blood_pressure','patientHighBloodPressure','alert_blood_pressure_details','patientBloodPressureDetails','input',false],
 ['alert_heart_condition','patientHeartCondition','alert_heart_condition_details','patientHeartConditionDetails','textarea',false],
 ['alert_pregnant','patientPregnant','alert_pregnancy_details','patientPregnancyDetails','input',true],
 ['alert_anesthesia_problem','patientAnesthesiaProblem','alert_anesthesia_problem_details','patientAnesthesiaReactionDetails','textarea',false],
];
?>
<section class="form-section registration-basics" aria-labelledby="patient-basics">
    <header><h2 id="patient-basics"><?= esc(lang('Studio.patientIdentity')) ?></h2></header>
    <div class="registration-fields">
        <?php foreach(['first_name'=>'patientFirstName','last_name'=>'patientLastName'] as $name=>$label): ?>
        <div class="form-field">
            <label for="<?= $name ?>"><?= esc(lang('UI.'.$label)) ?> <span aria-hidden="true">*</span></label>
            <input id="<?= $name ?>" name="<?= $name ?>" type="text" required minlength="2" maxlength="50" autocomplete="<?= $name==='first_name'?'given-name':'family-name' ?>" value="<?= esc($studioValue($name),'attr') ?>" aria-invalid="<?= isset($studioFormErrors[$name])?'true':'false' ?>" <?= isset($studioFormErrors[$name])?'aria-describedby="'.$name.'-error"':'' ?>>
            <?php if(isset($studioFormErrors[$name])): ?><p class="field-error" id="<?= $name ?>-error"><?= esc($studioFormErrors[$name]) ?></p><?php endif; ?>
        </div>
        <?php endforeach; ?>
        <div class="form-field form-field--phone">
            <label for="phone"><?= esc(lang('UI.patientPhone')) ?> <span aria-hidden="true">*</span></label>
            <input id="phone" name="phone" type="tel" inputmode="numeric" pattern="[0-9]{9,15}" minlength="9" maxlength="15" autocomplete="tel" dir="ltr" required value="<?= esc($studioValue('phone'),'attr') ?>" aria-invalid="<?= isset($studioFormErrors['phone'])?'true':'false' ?>" <?= isset($studioFormErrors['phone'])?'aria-describedby="phone-error"':'' ?>>
            <?php if(isset($studioFormErrors['phone'])): ?><p class="field-error" id="phone-error"><?= esc($studioFormErrors['phone']) ?></p><?php endif; ?>
        </div>
        <div class="form-field form-field--date">
            <label for="date_of_birth"><?= esc(lang('UI.patientDateOfBirth')) ?> <span aria-hidden="true">*</span></label>
            <input id="date_of_birth" name="date_of_birth" type="date" data-clinic-date="1" autocomplete="bday" required value="<?= esc($dobValue,'attr') ?>" aria-invalid="<?= isset($studioFormErrors['date_of_birth'])?'true':'false' ?>" <?= isset($studioFormErrors['date_of_birth'])?'aria-describedby="date_of_birth-error"':'' ?>>
            <?php if(isset($studioFormErrors['date_of_birth'])): ?><p class="field-error" id="date_of_birth-error"><?= esc($studioFormErrors['date_of_birth']) ?></p><?php endif; ?>
            <?php if($dob!==''&&$dobValue===''): ?><p class="field-error"><?= esc(lang('Studio.previousEntry',['value'=>$dob])) ?></p><?php endif; ?>
        </div>
        <div class="form-field form-field--choice">
            <label for="gender"><?= esc(lang('UI.patientGender')) ?> <span aria-hidden="true">*</span></label>
            <select id="gender" name="gender" data-force-select2 required aria-invalid="<?= isset($studioFormErrors['gender'])?'true':'false' ?>" <?= isset($studioFormErrors['gender'])?'aria-describedby="gender-error"':'' ?>>
                <option value=""><?= esc(lang('Studio.chooseOption')) ?></option>
                <?php foreach(['male'=>'patientMale','female'=>'patientFemale','other'=>'patientOther'] as $value=>$label): ?><option value="<?= $value ?>" <?= $studioValue('gender')===$value?'selected':'' ?>><?= esc(lang('UI.'.$label)) ?></option><?php endforeach; ?>
            </select>
            <?php if(isset($studioFormErrors['gender'])): ?><p class="field-error" id="gender-error"><?= esc($studioFormErrors['gender']) ?></p><?php endif; ?>
        </div>
    </div>
</section>
<section class="form-section registration-additional">
    <div class="form-section-toggle">
        <h2><?= esc(lang('Studio.optionalDetails')) ?></h2>
        <?php if(!$studioEditing): ?><button type="button" id="optionalEnabled" class="button secondary" aria-expanded="<?= $optionalEnabled?'true':'false' ?>" aria-controls="optionalSection" data-show="<?= esc(lang('Studio.showOptionalDetails'),'attr') ?>" data-hide="<?= esc(lang('Studio.hideOptionalDetails'),'attr') ?>"><?= esc(lang('Studio.'.($optionalEnabled?'hideOptionalDetails':'showOptionalDetails'))) ?></button><?php endif; ?>
    </div>
    <div id="optionalSection" class="<?= $optionalEnabled?'':'hidden' ?>">
        <div class="registration-details">
            <section class="registration-contact" aria-labelledby="contact-details-title">
                <h3 id="contact-details-title"><?= esc(lang($studioEditing?'Studio.contactDetails':'Studio.contactAndCover')) ?></h3>
                <div class="registration-contact-fields">
                    <?php foreach($studioContactFields as $name=>$label): ?>
                    <div class="form-field">
                        <label for="<?= $name ?>"><?= esc(lang('UI.'.$label)) ?></label>
                        <input id="<?= $name ?>" name="<?= $name ?>" type="<?= $name==='email'?'email':'text' ?>" <?= $name==='email'?'autocomplete="email"':($name==='city'?'autocomplete="address-level2"':'') ?> value="<?= esc($studioValue($name),'attr') ?>" aria-invalid="<?= isset($studioFormErrors[$name])?'true':'false' ?>" <?= isset($studioFormErrors[$name])?'aria-describedby="'.$name.'-error"':'' ?>>
                        <?php if(isset($studioFormErrors[$name])): ?><p class="field-error" id="<?= $name ?>-error"><?= esc($studioFormErrors[$name]) ?></p><?php endif; ?>
                    </div>
                    <?php endforeach; ?>
                    <div class="form-field"><label for="notes"><?= esc(lang('UI.patientNotes')) ?></label><textarea id="notes" name="notes" rows="3"><?= esc($studioValue('notes')) ?></textarea></div>
                </div>
            </section>
            <section class="registration-medical" aria-labelledby="medical-alerts-title">
                <h3 id="medical-alerts-title"><?= esc(lang('Studio.medicalDetails')) ?></h3>
                <div class="medical-alert-grid">
                    <?php foreach($medicalAlerts as [$key,$label,$detail,$detailLabel,$type,$pregnancy]): $checked=$studioChecked($key); ?>
                    <div class="medical-alert-card" data-medical-alert-item <?= $pregnancy?'data-pregnancy-only="1"':'' ?>>
                        <div class="medical-alert-heading">
                            <label for="<?= $key ?>"><?= esc(lang('UI.'.$label)) ?></label>
                            <input type="hidden" name="<?= $key ?>" value="0">
                            <label class="form-switch"><input type="checkbox" id="<?= $key ?>" name="<?= $key ?>" value="1" class="medical-alert-toggle" data-track-id="<?= $key ?>_track" data-dot-id="<?= $key ?>_dot" data-detail-target="<?= $detail ?>_wrap" <?= $checked?'checked':'' ?> aria-controls="<?= $detail ?>_wrap" aria-label="<?= esc(lang('UI.'.$label),'attr') ?>"><span id="<?= $key ?>_track" class="form-switch-track"><span id="<?= $key ?>_dot"></span></span></label>
                        </div>
                        <div id="<?= $detail ?>_wrap" data-medical-alert-detail-wrap class="form-field <?= $checked?'':'hidden' ?>">
                            <label for="<?= $detail ?>"><?= esc(lang('UI.'.$detailLabel)) ?></label>
                            <?php if($type==='textarea'): ?><textarea id="<?= $detail ?>" name="<?= $detail ?>" rows="2" <?= $checked?'':'disabled' ?>><?= esc($studioValue($detail)) ?></textarea><?php else: ?><input id="<?= $detail ?>" name="<?= $detail ?>" type="text" value="<?= esc($studioValue($detail),'attr') ?>" <?= $checked?'':'disabled' ?>><?php endif; ?>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </section>
        </div>
    </div>
</section>
