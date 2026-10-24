<?php
$validation = $validation ?? null;
$optionalFieldKeys = [
    'email',
    'city',
    'insurance_provider',
    'insurance_number',
    'notes',
    'alert_has_allergies',
    'alert_allergies_details',
    'alert_taking_medications',
    'alert_medications_details',
    'alert_blood_thinners',
    'alert_blood_thinners_details',
    'alert_diabetes',
    'alert_diabetes_details',
    'alert_high_blood_pressure',
    'alert_blood_pressure_details',
    'alert_heart_condition',
    'alert_heart_condition_details',
    'alert_pregnant',
    'alert_pregnancy_details',
    'alert_anesthesia_problem',
    'alert_anesthesia_problem_details',
];
$optionalEnabledByOld = false;
foreach ($optionalFieldKeys as $fieldKey) {
    if (old($fieldKey) !== null && old($fieldKey) !== '') {
        $optionalEnabledByOld = true;
        break;
    }
}
$alertBoolChecked = static function (string $key): bool {
    $value = old($key);
    return in_array($value, ['1', 1, true, 'true', 'on', 'yes'], true);
};
$sectionClass = $sectionClass ?? 'v3-flow-section';
$inputClass = $inputClass ?? 'v3-field-control';
$plainInputClass = $plainInputClass ?? 'v3-field-control';
?>

<section class="<?= esc($sectionClass) ?> v3-registration-section">
    <h4 class="text-lg font-bold mb-5 flex items-center gap-2">
        <i class="fas fa-asterisk text-xs"></i>
        <span><?= esc(lang('UI.patientRequiredInformation')) ?></span>
    </h4>

    <div class="grid grid-cols-1 md:grid-cols-2">
        <div>
            <label for="first_name" class="block text-sm font-bold mb-2"><?= esc(lang('UI.patientFirstName')) ?> *</label>
            <input id="first_name" name="first_name" type="text" required value="<?= old('first_name') ?>" class="<?= esc($inputClass) ?> <?= ($validation && $validation->hasError('first_name')) ? 'v3-border-default' : 'v3-border-default' ?> v3-input">
            <?php if ($validation && $validation->hasError('first_name')): ?>
                <p class="v3-text-danger text-sm mt-2"><?= esc($validation->getError('first_name')) ?></p>
            <?php endif; ?>
        </div>

        <div>
            <label for="last_name" class="block text-sm font-bold mb-2"><?= esc(lang('UI.patientLastName')) ?> *</label>
            <input id="last_name" name="last_name" type="text" required value="<?= old('last_name') ?>" class="<?= esc($inputClass) ?> <?= ($validation && $validation->hasError('last_name')) ? 'v3-border-default' : 'v3-border-default' ?> v3-input">
            <?php if ($validation && $validation->hasError('last_name')): ?>
                <p class="v3-text-danger text-sm mt-2"><?= esc($validation->getError('last_name')) ?></p>
            <?php endif; ?>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 mt-6">
        <div>
            <label for="phone" class="block text-sm font-bold mb-2"><?= esc(lang('UI.patientPhoneNumber')) ?> *</label>
            <input
                id="phone"
                name="phone"
                type="tel"
                required
                minlength="9"
                maxlength="15"
                inputmode="numeric"
                pattern="\d{9,15}"
                value="<?= old('phone') ?>"
                class="<?= esc($inputClass) ?> <?= ($validation && $validation->hasError('phone')) ? 'v3-border-default' : 'v3-border-default' ?> v3-input"
            >
            <?php if ($validation && $validation->hasError('phone')): ?>
                <p class="v3-text-danger text-sm mt-2"><?= esc($validation->getError('phone')) ?></p>
            <?php endif; ?>
        </div>

        <div>
            <label for="date_of_birth" class="block text-sm font-bold mb-2"><?= esc(lang('UI.patientDateOfBirth')) ?> *</label>
            <input id="date_of_birth" name="date_of_birth" type="text" data-clinic-date="1" required value="<?= old('date_of_birth') ?>" class="<?= esc($inputClass) ?> <?= ($validation && $validation->hasError('date_of_birth')) ? 'v3-border-default' : 'v3-border-default' ?> v3-input">
            <?php if ($validation && $validation->hasError('date_of_birth')): ?>
                <p class="v3-text-danger text-sm mt-2"><?= esc($validation->getError('date_of_birth')) ?></p>
            <?php endif; ?>
        </div>

        <div data-select2-dropdown-parent>
            <label for="gender" class="block text-sm font-bold mb-2"><?= esc(lang('UI.patientGender')) ?> *</label>
            <select id="gender" name="gender" data-force-select2 required class="<?= esc($inputClass) ?> <?= ($validation && $validation->hasError('gender')) ? 'v3-border-default' : 'v3-border-default' ?> v3-select">
                <option value=""><?= esc(lang('UI.patientSelect')) ?></option>
                <option value="male" <?= old('gender') === 'male' ? 'selected' : '' ?>><?= esc(lang('UI.patientMale')) ?></option>
                <option value="female" <?= old('gender') === 'female' ? 'selected' : '' ?>><?= esc(lang('UI.patientFemale')) ?></option>
                <option value="other" <?= old('gender') === 'other' ? 'selected' : '' ?>><?= esc(lang('UI.patientOther')) ?></option>
            </select>
            <?php if ($validation && $validation->hasError('gender')): ?>
                <p class="v3-text-danger text-sm mt-2"><?= esc($validation->getError('gender')) ?></p>
            <?php endif; ?>
        </div>
    </div>
</section>

<section class="<?= esc($sectionClass) ?> v3-registration-section">
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between mb-4">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 flex items-center justify-center">
                <i class="fas fa-sliders-h"></i>
            </div>
            <div>
                <h4 class="text-lg font-bold"><?= esc(lang('UI.patientOptionalInformation')) ?></h4>
                <p class="text-sm"><?= esc(lang('UI.patientEnableOnlyWhenNeeded')) ?></p>
            </div>
        </div>
        <label class="inline-flex items-center gap-3 cursor-pointer">
            <input type="checkbox" id="optionalEnabled" class="sr-only v3-checkbox" <?= $optionalEnabledByOld ? 'checked' : '' ?>>
            <div id="optionalTrack" class="toggle-track w-11 h-6 relative">
                <div id="optionalDot" class="toggle-dot absolute left-1 top-1 w-4 h-4"></div>
            </div>
            <span class="text-sm font-semibold"><?= esc(lang('UI.patientEnable')) ?></span>
        </label>
    </div>

    <div id="optionalSection" class="hidden">
        <div>
            <h5 class="text-sm font-semibold mb-3"><?= esc(lang('UI.patientContactDetails')) ?></h5>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label for="email" class="block text-sm font-medium mb-2"><?= esc(lang('UI.patientEmail')) ?></label>
                    <input id="email" name="email" type="email" value="<?= old('email') ?>" class="<?= esc($plainInputClass) ?> v3-input">
                    <?php if ($validation && $validation->hasError('email')): ?>
                        <p class="v3-text-danger text-sm mt-2"><?= esc($validation->getError('email')) ?></p>
                    <?php endif; ?>
                </div>
                <div>
                    <label for="city" class="block text-sm font-medium mb-2"><?= esc(lang('UI.patientCity')) ?></label>
                    <input id="city" name="city" type="text" value="<?= old('city') ?>" class="<?= esc($plainInputClass) ?> v3-input">
                </div>
            </div>
        </div>

        <div>
            <h5 class="text-sm font-semibold mb-3"><?= esc(lang('UI.patientMedicalAlertsQuickCheck')) ?></h5>
            <div class="border p-4 md:p-5">
                <div class="grid grid-cols-1 xl:grid-cols-2 gap-3 items-start" data-medical-alert-grid>
                    <?php
                    $medicalAlertItems = [
                        [
                            'key' => 'alert_has_allergies',
                            'icon' => 'fas fa-allergies',
                            'icon_class' => 'v3-text-danger',
                            'label' => lang('UI.patientAnyAllergies'),
                            'detail_name' => 'alert_allergies_details',
                            'detail_label' => lang('UI.patientAllergyDetails'),
                            'detail_type' => 'textarea',
                        ],
                        [
                            'key' => 'alert_taking_medications',
                            'icon' => 'fas fa-pills',
                            'icon_class' => 'v3-text-primary',
                            'label' => lang('UI.patientTakingMedications'),
                            'detail_name' => 'alert_medications_details',
                            'detail_label' => lang('UI.patientMedicationDetails'),
                            'detail_type' => 'textarea',
                        ],
                        [
                            'key' => 'alert_blood_thinners',
                            'icon' => 'fas fa-tint',
                            'icon_class' => 'v3-text-danger',
                            'label' => lang('UI.patientTakingBloodThinners'),
                            'detail_name' => 'alert_blood_thinners_details',
                            'detail_label' => lang('UI.patientBloodThinnerDetails'),
                            'detail_type' => 'input',
                        ],
                        [
                            'key' => 'alert_diabetes',
                            'icon' => 'fas fa-syringe',
                            'icon_class' => 'v3-text-warning',
                            'label' => lang('UI.patientDiabetes'),
                            'detail_name' => 'alert_diabetes_details',
                            'detail_label' => lang('UI.patientDiabetesDetails'),
                            'detail_type' => 'input',
                        ],
                        [
                            'key' => 'alert_high_blood_pressure',
                            'icon' => 'fas fa-heartbeat',
                            'icon_class' => 'text-pink-500',
                            'label' => lang('UI.patientHighBloodPressure'),
                            'detail_name' => 'alert_blood_pressure_details',
                            'detail_label' => lang('UI.patientBloodPressureDetails'),
                            'detail_type' => 'input',
                        ],
                        [
                            'key' => 'alert_heart_condition',
                            'icon' => 'fas fa-heart',
                            'icon_class' => 'v3-text-danger',
                            'label' => lang('UI.patientHeartCondition'),
                            'detail_name' => 'alert_heart_condition_details',
                            'detail_label' => lang('UI.patientHeartConditionDetails'),
                            'detail_type' => 'textarea',
                        ],
                        [
                            'key' => 'alert_pregnant',
                            'icon' => 'fas fa-baby',
                            'icon_class' => 'text-fuchsia-500',
                            'label' => lang('UI.patientPregnant'),
                            'detail_name' => 'alert_pregnancy_details',
                            'detail_label' => lang('UI.patientPregnancyDetails'),
                            'detail_type' => 'input',
                            'pregnancy_only' => true,
                        ],
                        [
                            'key' => 'alert_anesthesia_problem',
                            'icon' => 'fas fa-mask-face',
                            'icon_class' => 'v3-text-chair',
                            'label' => lang('UI.patientAnesthesiaProblem'),
                            'detail_name' => 'alert_anesthesia_problem_details',
                            'detail_label' => lang('UI.patientAnesthesiaReactionDetails'),
                            'detail_type' => 'textarea',
                        ],
                    ];
                    ?>
                    <?php foreach ($medicalAlertItems as $item): ?>
                        <?php
                        $isChecked = $alertBoolChecked($item['key']);
                        $detailVisible = $isChecked;
                        $isPregnancyOnly = !empty($item['pregnancy_only']);
                        ?>
                        <div
                            class="rounded-lg border p-3"
                            data-medical-alert-item
                            <?= $isPregnancyOnly ? 'data-pregnancy-only="1"' : '' ?>
                        >
                            <div class="flex items-center justify-between gap-3">
                                <label for="<?= esc($item['key']) ?>" class="text-sm font-medium leading-snug flex items-start gap-2">
                                    <i class="<?= esc($item['icon']) ?> <?= esc($item['icon_class'] ?? 'v3-text-muted') ?> mt-0.5 w-4 text-center"></i>
                                    <span><?= esc($item['label']) ?></span>
                                </label>
                                <div class="flex items-center gap-2 shrink-0">
                                    <span class="text-xs font-semibold"><?= esc(lang('UI.patientNo')) ?></span>
                                    <label class="inline-flex items-center cursor-pointer">
                                        <input type="hidden" name="<?= esc($item['key']) ?>" value="0">
                                        <input
                                            type="checkbox"
                                            id="<?= esc($item['key']) ?>"
                                            name="<?= esc($item['key']) ?>"
                                            value="1"
                                            class="sr-only medical-alert-toggle v3-checkbox"
                                            data-track-id="<?= esc($item['key']) ?>_track"
                                            data-dot-id="<?= esc($item['key']) ?>_dot"
                                            data-detail-target="<?= esc($item['detail_name']) ?>_wrap"
                                            <?= $isChecked ? 'checked' : '' ?>
                                        >
                                        <div id="<?= esc($item['key']) ?>_track" class="toggle-track w-11 h-6 <?= $isChecked ? 'v3-bg-primary' : 'v3-bg-sand' ?> rounded-full relative">
                                            <div id="<?= esc($item['key']) ?>_dot" class="toggle-dot absolute left-1 top-1 w-4 h-4" style="transform: <?= $isChecked ? (!empty($is_rtl) ? 'translateX(-20px)' : 'translateX(20px)') : 'translateX(0)' ?>;"></div>
                                        </div>
                                    </label>
                                    <span class="text-xs font-semibold"><?= esc(lang('UI.patientYes')) ?></span>
                                </div>
                            </div>

                            <div
                                id="<?= esc($item['detail_name']) ?>_wrap"
                                class="mt-3 <?= $detailVisible ? '' : 'hidden' ?>"
                                data-medical-alert-detail-wrap
                            >
                                <label for="<?= esc($item['detail_name']) ?>" class="block text-xs font-semibold mb-2">
                                    <?= esc($item['detail_label']) ?>
                                </label>
                                <?php if ($item['detail_type'] === 'textarea'): ?>
                                    <textarea
                                        id="<?= esc($item['detail_name']) ?>"
                                        name="<?= esc($item['detail_name']) ?>"
                                        rows="2"
                                        class="w-full px-3 py-2.5 text-sm border v3-textarea"
                                        <?= $detailVisible ? '' : 'disabled' ?>
                                    ><?= esc(old($item['detail_name'])) ?></textarea>
                                <?php else: ?>
                                    <input
                                        id="<?= esc($item['detail_name']) ?>"
                                        name="<?= esc($item['detail_name']) ?>"
                                        type="text"
                                        value="<?= esc(old($item['detail_name'])) ?>"
                                        class="w-full px-3 py-2.5 text-sm border v3-input"
                                        <?= $detailVisible ? '' : 'disabled' ?>
                                    >
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <div>
            <h5 class="text-sm font-semibold mb-3"><?= esc(lang('UI.patientInsuranceNotes')) ?></h5>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label for="insurance_provider" class="block text-sm font-medium mb-2"><?= esc(lang('UI.patientInsuranceProvider')) ?></label>
                    <input id="insurance_provider" name="insurance_provider" type="text" value="<?= old('insurance_provider') ?>" class="<?= esc($plainInputClass) ?> v3-input">
                </div>
                <div>
                    <label for="insurance_number" class="block text-sm font-medium mb-2"><?= esc(lang('UI.patientInsuranceNumber')) ?></label>
                    <input id="insurance_number" name="insurance_number" type="text" value="<?= old('insurance_number') ?>" class="<?= esc($plainInputClass) ?> v3-input">
                </div>
                <div class="md:col-span-2">
                    <label for="notes" class="block text-sm font-medium mb-2"><?= esc(lang('UI.patientNotes')) ?></label>
                    <textarea id="notes" name="notes" rows="3" class="<?= esc($plainInputClass) ?> v3-textarea"><?= old('notes') ?></textarea>
                </div>
            </div>
        </div>
    </div>
</section>
