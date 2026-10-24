<?= $this->extend('layouts/main_auth') ?>

<?= $this->section('content') ?>
<?php
$alertBoolValue = static function (string $key, array $patient): bool {
    $oldValue = old($key);
    if ($oldValue !== null) {
        return in_array($oldValue, ['1', 1, true, 'true', 'on', 'yes'], true);
    }

    return !empty($patient[$key]);
};
?>
<style>
.toggle-track { transition: background-color 0.2s ease; }
.toggle-dot { transition: transform 0.2s ease; }
</style>
<div class="v3-record-flow">
    <header class="v3-flow-command">
        <div><h1><?= esc(lang('UI.patientEditTitle')) ?></h1><p><?= esc(lang('UI.patientEditSubtitle', ['name' => $patient['first_name'] . ' ' . $patient['last_name']])) ?></p></div>
        <div class="v3-flow-navigation"><a href="<?= esc(workflow_back('patient/' . $patient['id'], $return_to ?? null)) ?>" class="relative inline-flex items-center px-6 py-3 border-2 text-sm font-bold v3-btn v3-btn--secondary">

                                <i class="fas fa-arrow-left mr-2 relative z-10"></i>
                                <span class="relative z-10"><?= esc(lang('UI.patientBack')) ?></span>
                            </a></div>
    </header>
    <div class="v3-flow-body">
<form action="<?= base_url('patient/' . $patient['id'] . '/update') ?>" method="POST" class="v3-flow-form v3-flow-form--patient">
                        <?= csrf_field() ?>
                        <?php if (!empty($return_to)): ?>
                            <input type="hidden" name="return_to" value="<?= esc($return_to) ?>">
                        <?php endif; ?>

                        <!-- Personal Information -->
                        <div class="v3-flow-section">

                            <div class="v3-flow-section-inner">
                                <div class="flex items-center space-x-3 mb-6">
                                    <div class="w-8 h-8 rounded-lg flex items-center justify-center">
                                        <i class="fas fa-user text-sm"></i>
                                    </div>
                                    <h4 class="text-xl font-bold"><?= esc(lang('UI.patientPersonalInformation')) ?></h4>
                                </div>

                                 <div class="v3-flow-fields">
                                     <div class="grid grid-cols-1 md:grid-cols-2">
                                         <div class="relative">
                                             <label class="block text-sm font-bold mb-3 flex items-center space-x-2">
                                                 <i class="fas fa-user"></i>
                                                 <span><?= esc(lang('UI.patientFirstName')) ?> *</span>
                                             </label>
                                             <div class="relative">
                                                 <input type="text" id="first_name" name="first_name"
                                                        class="v3-field-control <?= $validation->hasError('first_name') ? 'v3-border-default v3-border-default' : '' ?> v3-input"
                                                        value="<?= old('first_name', $patient['first_name']) ?>" placeholder="<?= esc(lang('UI.patientPlaceholderFirstName')) ?>" required>
                                                 <div class="absolute inset-y-0 <?= !empty($is_rtl) ? 'left-0 pl-3' : 'right-0 pr-3' ?> flex items-center pointer-events-none">
                                                     <i class="fas fa-user"></i>
                                                 </div>
                                             </div>
                                             <?php if ($validation->hasError('first_name')): ?>
                                                 <p class="v3-text-danger text-sm mt-2 flex items-center space-x-1">
                                                     <i class="fas fa-exclamation-circle"></i>
                                                     <span><?= $validation->getError('first_name') ?></span>
                                                 </p>
                                             <?php endif; ?>
                                         </div>

                                         <div class="relative">
                                             <label class="block text-sm font-bold mb-3 flex items-center space-x-2">
                                                 <i class="fas fa-user"></i>
                                                 <span><?= esc(lang('UI.patientLastName')) ?> *</span>
                                             </label>
                                             <div class="relative">
                                                 <input type="text" id="last_name" name="last_name"
                                                        class="v3-field-control <?= $validation->hasError('last_name') ? 'v3-border-default v3-border-default' : '' ?> v3-input"
                                                        value="<?= old('last_name', $patient['last_name']) ?>" placeholder="<?= esc(lang('UI.patientPlaceholderLastName')) ?>" required>
                                                 <div class="absolute inset-y-0 <?= !empty($is_rtl) ? 'left-0 pl-3' : 'right-0 pr-3' ?> flex items-center pointer-events-none">
                                                     <i class="fas fa-user"></i>
                                                 </div>
                                             </div>
                                             <?php if ($validation->hasError('last_name')): ?>
                                                 <p class="v3-text-danger text-sm mt-2 flex items-center space-x-1">
                                                     <i class="fas fa-exclamation-circle"></i>
                                                     <span><?= $validation->getError('last_name') ?></span>
                                                 </p>
                                             <?php endif; ?>
                                         </div>
                                     </div>

                                     <div class="grid grid-cols-1 md:grid-cols-2">
                                         <div class="relative">
                                             <label class="block text-sm font-bold mb-3 flex items-center space-x-2">
                                                 <i class="fas fa-envelope"></i>
                                                 <span><?= esc(lang('UI.patientEmail')) ?></span>
                                             </label>
                                             <div class="relative">
                                                 <input type="email" id="email" name="email"
                                                        class="w-full px-4 py-3 v3-bg-panel border-2 v3-border-default v3-radius v3-elevation-none focus:outline-none focus:ring-4 v3-focus-control v3-focus-control transition-all duration-300 hover:v3-elevation-none <?= $validation->hasError('email') ? 'v3-border-default v3-border-default' : '' ?> v3-input"
                                                        value="<?= old('email', $patient['email']) ?>" placeholder="<?= esc(lang('UI.patientPlaceholderEmail')) ?>">
                                                 <div class="absolute inset-y-0 <?= !empty($is_rtl) ? 'left-0 pl-3' : 'right-0 pr-3' ?> flex items-center pointer-events-none">
                                                     <i class="fas fa-envelope"></i>
                                                 </div>
                                             </div>
                                             <?php if ($validation->hasError('email')): ?>
                                                 <p class="v3-text-danger text-sm mt-2 flex items-center space-x-1">
                                                     <i class="fas fa-exclamation-circle"></i>
                                                     <span><?= $validation->getError('email') ?></span>
                                                 </p>
                                             <?php endif; ?>
                                         </div>

                                         <div class="relative">
                                             <label class="block text-sm font-bold mb-3 flex items-center space-x-2">
                                                 <i class="fas fa-phone"></i>
                                                 <span><?= esc(lang('UI.patientPhoneNumber')) ?> *</span>
                                             </label>
                                             <div class="relative">
                                                 <input type="tel" id="phone" name="phone"
                                                        class="w-full px-4 py-3 v3-bg-panel border-2 v3-border-default v3-radius v3-elevation-none focus:outline-none focus:ring-4 v3-focus-control v3-focus-control transition-all duration-300 hover:v3-elevation-none <?= $validation->hasError('phone') ? 'v3-border-default v3-border-default' : '' ?> v3-input"
                                                        value="<?= old('phone', $patient['phone']) ?>" placeholder="<?= esc(lang('UI.patientPlaceholderPhone')) ?>" required>
                                                 <div class="absolute inset-y-0 <?= !empty($is_rtl) ? 'left-0 pl-3' : 'right-0 pr-3' ?> flex items-center pointer-events-none">
                                                     <i class="fas fa-phone"></i>
                                                 </div>
                                             </div>
                                             <?php if ($validation->hasError('phone')): ?>
                                                 <p class="v3-text-danger text-sm mt-2 flex items-center space-x-1">
                                                     <i class="fas fa-exclamation-circle"></i>
                                                     <span><?= $validation->getError('phone') ?></span>
                                                 </p>
                                             <?php endif; ?>
                                         </div>
                                     </div>

                                     <div class="grid grid-cols-1 md:grid-cols-2">
                                         <div class="relative">
                                             <label class="block text-sm font-bold mb-3 flex items-center space-x-2">
                                                 <i class="fas fa-calendar v3-text-warning"></i>
                                                 <span><?= esc(lang('UI.patientDateOfBirth')) ?> *</span>
                                             </label>
                                             <div class="relative">
                                                 <input type="text" id="date_of_birth" name="date_of_birth" data-clinic-date="1"
                                                        class="w-full px-4 py-3 v3-bg-panel border-2 v3-border-default v3-radius v3-elevation-none focus:outline-none focus:ring-4 v3-focus-control v3-focus-control transition-all duration-300 hover:v3-elevation-none <?= $validation->hasError('date_of_birth') ? 'v3-border-default v3-border-default' : '' ?> v3-input"
                                                        value="<?= old('date_of_birth', $patient['date_of_birth']) ?>" required>
                                                 <div class="absolute inset-y-0 <?= !empty($is_rtl) ? 'left-0 pl-3' : 'right-0 pr-3' ?> flex items-center pointer-events-none">
                                                     <i class="fas fa-calendar"></i>
                                                 </div>
                                             </div>
                                             <?php if ($validation->hasError('date_of_birth')): ?>
                                                 <p class="v3-text-danger text-sm mt-2 flex items-center space-x-1">
                                                     <i class="fas fa-exclamation-circle"></i>
                                                     <span><?= $validation->getError('date_of_birth') ?></span>
                                                 </p>
                                             <?php endif; ?>
                                         </div>

                                         <div class="relative">
                                             <label class="block text-sm font-bold mb-3 flex items-center space-x-2">
                                                 <i class="fas fa-venus-mars v3-text-danger"></i>
                                                 <span><?= esc(lang('UI.patientGender')) ?> *</span>
                                             </label>
                                             <div class="relative">
                                                 <select id="gender" name="gender" data-force-select2
                                                         class="w-full px-4 py-3 v3-bg-panel border-2 v3-border-default v3-radius v3-elevation-none focus:outline-none focus:ring-4 v3-focus-control v3-focus-control transition-all duration-300 hover:v3-elevation-none <?= $validation->hasError('gender') ? 'v3-border-default v3-border-default' : '' ?> v3-select" required>
                                                     <option value=""><?= esc(lang('UI.patientSelectGender')) ?></option>
                                                     <option value="male" <?= old('gender', $patient['gender']) == 'male' ? 'selected' : '' ?>><?= esc(lang('UI.patientMale')) ?></option>
                                                     <option value="female" <?= old('gender', $patient['gender']) == 'female' ? 'selected' : '' ?>><?= esc(lang('UI.patientFemale')) ?></option>
                                                     <option value="other" <?= old('gender', $patient['gender']) == 'other' ? 'selected' : '' ?>><?= esc(lang('UI.patientOther')) ?></option>
                                                 </select>
                                                 <div class="absolute inset-y-0 <?= !empty($is_rtl) ? 'left-0 pl-3' : 'right-0 pr-3' ?> flex items-center pointer-events-none">
                                                     <i class="fas fa-chevron-down"></i>
                                                 </div>
                                             </div>
                                             <?php if ($validation->hasError('gender')): ?>
                                                 <p class="v3-text-danger text-sm mt-2 flex items-center space-x-1">
                                                     <i class="fas fa-exclamation-circle"></i>
                                                     <span><?= $validation->getError('gender') ?></span>
                                                 </p>
                                             <?php endif; ?>
                                         </div>
                                     </div>

                                 </div>
                            </div>
                        </div>

                        <!-- Contact Details -->
                        <div class="v3-flow-section">

                            <div class="v3-flow-section-inner">
                                <div class="flex items-center space-x-3 mb-6">
                                    <div class="w-8 h-8 rounded-lg flex items-center justify-center">
                                        <i class="fas fa-city text-sm"></i>
                                    </div>
                                    <h4 class="text-xl font-bold"><?= esc(lang('UI.patientContactDetails')) ?></h4>
                                </div>

                                <div class="grid grid-cols-1 md:grid-cols-2">
                                    <div class="relative">
                                        <label class="block text-sm font-bold mb-3 flex items-center space-x-2">
                                            <i class="fas fa-city"></i>
                                            <span><?= esc(lang('UI.patientCity')) ?></span>
                                        </label>
                                        <div class="relative">
                                            <input type="text" id="city" name="city"
                                                   class="w-full px-4 py-3 v3-bg-panel border-2 v3-border-default v3-radius v3-elevation-none focus:outline-none focus:ring-4 v3-focus-control v3-focus-control transition-all duration-300 hover:v3-elevation-none <?= $validation->hasError('city') ? 'v3-border-default v3-border-default' : '' ?> v3-input"
                                                   value="<?= old('city', $patient['city']) ?>" placeholder="<?= esc(lang('UI.patientPlaceholderCity')) ?>">
                                            <div class="absolute inset-y-0 <?= !empty($is_rtl) ? 'left-0 pl-3' : 'right-0 pr-3' ?> flex items-center pointer-events-none">
                                                <i class="fas fa-city"></i>
                                            </div>
                                        </div>
                                        <?php if ($validation->hasError('city')): ?>
                                            <p class="v3-text-danger text-sm mt-2 flex items-center space-x-1">
                                                <i class="fas fa-exclamation-circle"></i>
                                                <span><?= $validation->getError('city') ?></span>
                                            </p>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Medical Alerts -->
                        <div class="v3-flow-section">

                            <div class="v3-flow-section-inner">
                                <div class="flex items-center space-x-3 mb-6">
                                    <div class="w-8 h-8 rounded-lg flex items-center justify-center">
                                        <i class="fas fa-heartbeat text-sm"></i>
                                    </div>
                                    <h4 class="text-xl font-bold"><?= esc(lang('UI.patientMedicalAlertsQuickCheck')) ?></h4>
                                </div>

                                <div class="border p-4 md:p-5">
                                    <div class="grid grid-cols-1 xl:grid-cols-2 gap-3">
                                        <?php
                                        $medicalAlertItems = [
                                            ['key' => 'alert_has_allergies', 'icon' => 'fas fa-allergies', 'icon_class' => 'v3-text-danger', 'label' => lang('UI.patientAnyAllergies'), 'detail_name' => 'alert_allergies_details', 'detail_label' => lang('UI.patientAllergyDetails'), 'detail_type' => 'textarea'],
                                            ['key' => 'alert_taking_medications', 'icon' => 'fas fa-pills', 'icon_class' => 'v3-text-primary', 'label' => lang('UI.patientTakingMedications'), 'detail_name' => 'alert_medications_details', 'detail_label' => lang('UI.patientMedicationDetails'), 'detail_type' => 'textarea'],
                                            ['key' => 'alert_blood_thinners', 'icon' => 'fas fa-tint', 'icon_class' => 'v3-text-danger', 'label' => lang('UI.patientTakingBloodThinners'), 'detail_name' => 'alert_blood_thinners_details', 'detail_label' => lang('UI.patientBloodThinnerDetails'), 'detail_type' => 'input'],
                                            ['key' => 'alert_diabetes', 'icon' => 'fas fa-syringe', 'icon_class' => 'v3-text-warning', 'label' => lang('UI.patientDiabetes'), 'detail_name' => 'alert_diabetes_details', 'detail_label' => lang('UI.patientDiabetesDetails'), 'detail_type' => 'input'],
                                            ['key' => 'alert_high_blood_pressure', 'icon' => 'fas fa-heartbeat', 'icon_class' => 'text-pink-500', 'label' => lang('UI.patientHighBloodPressure'), 'detail_name' => 'alert_blood_pressure_details', 'detail_label' => lang('UI.patientBloodPressureDetails'), 'detail_type' => 'input'],
                                            ['key' => 'alert_heart_condition', 'icon' => 'fas fa-heart', 'icon_class' => 'v3-text-danger', 'label' => lang('UI.patientHeartCondition'), 'detail_name' => 'alert_heart_condition_details', 'detail_label' => lang('UI.patientHeartConditionDetails'), 'detail_type' => 'textarea'],
                                            ['key' => 'alert_pregnant', 'icon' => 'fas fa-baby', 'icon_class' => 'text-fuchsia-500', 'label' => lang('UI.patientPregnant'), 'detail_name' => 'alert_pregnancy_details', 'detail_label' => lang('UI.patientPregnancyDetails'), 'detail_type' => 'input', 'pregnancy_only' => true],
                                            ['key' => 'alert_anesthesia_problem', 'icon' => 'fas fa-mask-face', 'icon_class' => 'v3-text-chair', 'label' => lang('UI.patientAnesthesiaProblem'), 'detail_name' => 'alert_anesthesia_problem_details', 'detail_label' => lang('UI.patientAnesthesiaReactionDetails'), 'detail_type' => 'textarea'],
                                        ];
                                        ?>
                                        <?php foreach ($medicalAlertItems as $item): ?>
                                            <?php
                                            $isChecked = $alertBoolValue($item['key'], $patient);
                                            $detailVisible = $isChecked;
                                            $detailValue = old($item['detail_name'], $patient[$item['detail_name']] ?? '');
                                            ?>
                                            <div class="rounded-lg border p-3" data-medical-alert-item <?= !empty($item['pregnancy_only']) ? 'data-pregnancy-only="1"' : '' ?>>
                                                <div class="flex items-center justify-between gap-3">
                                                    <label for="<?= esc($item['key']) ?>" class="text-sm font-medium leading-snug pr-2 flex items-start gap-2">
                                                        <i class="<?= esc($item['icon']) ?> <?= esc($item['icon_class'] ?? 'v3-text-muted') ?> mt-0.5 w-4 text-center"></i>
                                                        <span><?= esc($item['label']) ?></span>
                                                    </label>
                                                    <div class="flex items-center gap-2 shrink-0">
                                                        <span class="text-xs font-semibold"><?= esc(lang('UI.patientNo')) ?></span>
                                                        <label class="inline-flex items-center cursor-pointer">
                                                            <input type="hidden" name="<?= esc($item['key']) ?>" value="0">
                                                            <input type="checkbox"
                                                                   id="<?= esc($item['key']) ?>"
                                                                   name="<?= esc($item['key']) ?>"
                                                                   value="1"
                                                                   class="sr-only medical-alert-toggle v3-checkbox"
                                                                   data-track-id="<?= esc($item['key']) ?>_track"
                                                                   data-dot-id="<?= esc($item['key']) ?>_dot"
                                                                   data-detail-target="<?= esc($item['detail_name']) ?>_wrap"
                                                                   <?= $isChecked ? 'checked' : '' ?>>
                                                            <div id="<?= esc($item['key']) ?>_track" class="toggle-track w-11 h-6 <?= $isChecked ? 'v3-bg-primary' : 'v3-bg-sand' ?> rounded-full relative">
                                                                <div id="<?= esc($item['key']) ?>_dot" class="toggle-dot absolute left-1 top-1 w-4 h-4" style="transform: <?= $isChecked ? (!empty($is_rtl) ? 'translateX(-20px)' : 'translateX(20px)') : 'translateX(0)' ?>;"></div>
                                                            </div>
                                                        </label>
                                                        <span class="text-xs font-semibold"><?= esc(lang('UI.patientYes')) ?></span>
                                                    </div>
                                                </div>
                                                <div id="<?= esc($item['detail_name']) ?>_wrap" class="mt-3 <?= $detailVisible ? '' : 'hidden' ?>" data-medical-alert-detail-wrap>
                                                    <label for="<?= esc($item['detail_name']) ?>" class="block text-xs font-semibold mb-2"><?= esc($item['detail_label']) ?></label>
                                                    <?php if ($item['detail_type'] === 'textarea'): ?>
                                                        <textarea id="<?= esc($item['detail_name']) ?>" name="<?= esc($item['detail_name']) ?>" rows="2" class="w-full px-3 py-2.5 text-sm border v3-textarea" <?= $detailVisible ? '' : 'disabled' ?>><?= esc($detailValue) ?></textarea>
                                                    <?php else: ?>
                                                        <input id="<?= esc($item['detail_name']) ?>" name="<?= esc($item['detail_name']) ?>" type="text" value="<?= esc($detailValue) ?>" class="w-full px-3 py-2.5 text-sm border v3-input" <?= $detailVisible ? '' : 'disabled' ?>>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            </div>
                        </div>


                        <!-- Additional Notes -->
                        <div class="v3-flow-section">

                            <div class="v3-flow-section-inner">
                                <div class="flex items-center space-x-3 mb-6">
                                    <div class="w-8 h-8 rounded-lg flex items-center justify-center">
                                        <i class="fas fa-sticky-note text-sm"></i>
                                    </div>
                                    <h4 class="text-xl font-bold"><?= esc(lang('UI.patientAdditionalNotes')) ?></h4>
                                </div>

                                 <div class="relative">
                                     <label class="block text-sm font-bold mb-3 flex items-center space-x-2">
                                         <i class="fas fa-sticky-note"></i>
                                         <span><?= esc(lang('UI.patientAdditionalNotes')) ?></span>
                                     </label>
                                     <div class="relative">
                                         <textarea id="notes" name="notes" rows="4"
                                                   class="w-full px-4 py-3 v3-bg-panel border-2 v3-border-default v3-radius v3-elevation-none focus:outline-none focus:ring-4 v3-focus-control v3-focus-control transition-all duration-300 hover:v3-elevation-none resize-none <?= $validation->hasError('notes') ? 'v3-border-default v3-border-default' : '' ?> v3-textarea"
                                                   placeholder="<?= esc(lang('UI.patientAdditionalNotesPlaceholder')) ?>"><?= old('notes', $patient['notes']) ?></textarea>
                                         <div class="absolute bottom-3 <?= !empty($is_rtl) ? 'left-3' : 'right-3' ?> v3-text-muted text-xs">
                                             <i class="fas fa-edit"></i>
                                         </div>
                                     </div>
                                     <?php if ($validation->hasError('notes')): ?>
                                         <p class="v3-text-danger text-sm mt-2 flex items-center space-x-1">
                                             <i class="fas fa-exclamation-circle"></i>
                                             <span><?= $validation->getError('notes') ?></span>
                                         </p>
                                     <?php endif; ?>
                                 </div>
                            </div>
                        </div>

                        <!-- Form Actions -->
                        <div class="flex flex-col sm:flex-row justify-end gap-4 sm:gap-12 pt-6 border-t">
                            <a href="<?= esc(workflow_cancel('patient/' . $patient['id'], $return_to ?? ('patient/' . $patient['id']))) ?>" class="relative inline-flex items-center justify-center px-8 py-3 border-2 text-sm font-bold v3-btn v3-btn--secondary">

                                <i class="fas fa-times mr-2 relative z-10"></i>
                                <span class="relative z-10"><?= esc(lang('UI.cancel')) ?></span>
                            </a>
                            <button type="submit" class="v3-flow-submit relative inline-flex items-center justify-center px-8 py-3 text-sm font-bold v3-btn v3-btn--primary">

                                <i class="fas fa-save mr-2 relative z-10"></i>
                                <span class="relative z-10"><?= esc(lang('UI.patientUpdatePatient')) ?></span>
                            </button>
                        </div>
                    </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const genderSelect = document.getElementById('gender');

    function syncMedicalAlertToggle(toggle) {
        if (!toggle) return;
        const track = document.getElementById(toggle.dataset.trackId || '');
        const dot = document.getElementById(toggle.dataset.dotId || '');
        const detailWrap = document.getElementById(toggle.dataset.detailTarget || '');

        if (toggle.checked) {
            track?.classList.remove('v3-bg-sand');
            track?.classList.add('v3-bg-primary');
            window.applyToggleDotState?.(dot, true);
            if (detailWrap) {
                detailWrap.classList.remove('hidden');
                detailWrap.querySelectorAll('input, textarea, select').forEach((field) => { field.disabled = false; });
            }
        } else {
            track?.classList.remove('v3-bg-primary');
            track?.classList.add('v3-bg-sand');
            window.applyToggleDotState?.(dot, false);
            if (detailWrap) {
                detailWrap.classList.add('hidden');
                detailWrap.querySelectorAll('input, textarea, select').forEach((field) => {
                    field.disabled = true;
                    field.value = '';
                });
            }
        }
    }

    function syncPregnancyVisibility() {
        const gender = (genderSelect?.value || '').toLowerCase();
        const showPregnancy = gender === '' || gender === 'female';

        document.querySelectorAll('[data-pregnancy-only="1"]').forEach(function (item) {
            const toggle = item.querySelector('.medical-alert-toggle');
            const hiddenInputs = item.querySelectorAll('input[type="hidden"]');
            const detailInputs = item.querySelectorAll('[data-medical-alert-detail-wrap] input, [data-medical-alert-detail-wrap] textarea');

            if (showPregnancy) {
                item.classList.remove('hidden');
                if (toggle) toggle.disabled = false;
                hiddenInputs.forEach((input) => { input.disabled = false; });
                syncMedicalAlertToggle(toggle);
                return;
            }

            item.classList.add('hidden');
            if (toggle) {
                toggle.checked = false;
                toggle.disabled = true;
                syncMedicalAlertToggle(toggle);
            }
            hiddenInputs.forEach((input) => { input.disabled = true; });
            detailInputs.forEach((input) => { input.disabled = true; input.value = ''; });
        });
    }

    document.querySelectorAll('.medical-alert-toggle').forEach(function (toggle) {
        toggle.addEventListener('change', function () { syncMedicalAlertToggle(toggle); });
        syncMedicalAlertToggle(toggle);
    });

    genderSelect?.addEventListener('change', syncPregnancyVisibility);
    syncPregnancyVisibility();
});
</script>
<?= $this->endSection() ?>


