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
<!-- Enhanced Patient Edit with Advanced Design -->
<div class="v4-task-page"><header class="v4-page-heading"><div><h1><?= esc(lang('UI.patientEditTitle')) ?></h1><p><?= esc(lang('UI.patientEditSubtitle',['name'=>$patient['first_name'].' '.$patient['last_name']])) ?></p></div><a class="v4-button" href="<?= esc(workflow_back('patient/'.$patient['id'],$return_to??null)) ?>"><?= esc(lang('V4.back')) ?></a></header><form action="<?= base_url('patient/' . $patient['id'] . '/update') ?>" method="POST" class="v4-form v4-task-form">
                        <?= csrf_field() ?>
                        <?php if (!empty($return_to)): ?>
                            <input type="hidden" name="return_to" value="<?= esc($return_to) ?>">
                        <?php endif; ?>

                        <!-- Personal Information -->
                        <div class="group/section relative">

                            <div class="v4-form-section">
                                <div class="flex items-center space-x-3 mb-6">
                                    <div class="w-8 h-8 rounded-lg flex items-center justify-center">
                                        <i class="fas fa-user text-white text-sm"></i>
                                    </div>
                                    <h4 class="text-xl font-bold text-gray-800"><?= esc(lang('UI.patientPersonalInformation')) ?></h4>
                                </div>

                                 <div class="space-y-8">
                                     <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                                         <div class="group/field relative">
                                             <label class="block text-sm font-bold text-gray-700 mb-3 flex items-center space-x-2">
                                                 <i class="fas fa-user v4-text-action"></i>
                                                 <span><?= esc(lang('UI.patientFirstName')) ?> *</span>
                                             </label>
                                             <div class="relative">
                                                 <input type="text" id="first_name" name="first_name"
                                                        class="w-full px-4 py-3 bg-white/80 border-2 border-gray-200 rounded-xl shadow-lg focus:outline-none focus:ring-4 focus:ring-blue-500/20 focus:border-blue-500 transition-all duration-300 hover:shadow-xl <?= $validation->hasError('first_name') ? 'border-red-500 ring-red-500/20' : '' ?>"
                                                        value="<?= old('first_name', $patient['first_name']) ?>" placeholder="<?= esc(lang('UI.patientPlaceholderFirstName')) ?>" required>

                                             </div>
                                             <?php if ($validation->hasError('first_name')): ?>
                                                 <p class="text-red-500 text-sm mt-2 flex items-center space-x-1">
                                                     <i class="fas fa-exclamation-circle"></i>
                                                     <span><?= $validation->getError('first_name') ?></span>
                                                 </p>
                                             <?php endif; ?>
                                         </div>

                                         <div class="group/field relative">
                                             <label class="block text-sm font-bold text-gray-700 mb-3 flex items-center space-x-2">
                                                 <i class="fas fa-user v4-text-action"></i>
                                                 <span><?= esc(lang('UI.patientLastName')) ?> *</span>
                                             </label>
                                             <div class="relative">
                                                 <input type="text" id="last_name" name="last_name"
                                                        class="w-full px-4 py-3 bg-white/80 border-2 border-gray-200 rounded-xl shadow-lg focus:outline-none focus:ring-4 focus:ring-blue-500/20 focus:border-blue-500 transition-all duration-300 hover:shadow-xl <?= $validation->hasError('last_name') ? 'border-red-500 ring-red-500/20' : '' ?>"
                                                        value="<?= old('last_name', $patient['last_name']) ?>" placeholder="<?= esc(lang('UI.patientPlaceholderLastName')) ?>" required>

                                             </div>
                                             <?php if ($validation->hasError('last_name')): ?>
                                                 <p class="text-red-500 text-sm mt-2 flex items-center space-x-1">
                                                     <i class="fas fa-exclamation-circle"></i>
                                                     <span><?= $validation->getError('last_name') ?></span>
                                                 </p>
                                             <?php endif; ?>
                                         </div>
                                     </div>

                                     <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                                         <div class="group/field relative">
                                             <label class="block text-sm font-bold text-gray-700 mb-3 flex items-center space-x-2">
                                                 <i class="fas fa-envelope text-purple-600"></i>
                                                 <span><?= esc(lang('UI.patientEmail')) ?></span>
                                             </label>
                                             <div class="relative">
                                                 <input type="email" id="email" name="email"
                                                        class="w-full px-4 py-3 bg-white/80 border-2 border-gray-200 rounded-xl shadow-lg focus:outline-none focus:ring-4 focus:ring-purple-500/20 focus:border-purple-500 transition-all duration-300 hover:shadow-xl <?= $validation->hasError('email') ? 'border-red-500 ring-red-500/20' : '' ?>"
                                                        value="<?= old('email', $patient['email']) ?>" placeholder="<?= esc(lang('UI.patientPlaceholderEmail')) ?>">

                                             </div>
                                             <?php if ($validation->hasError('email')): ?>
                                                 <p class="text-red-500 text-sm mt-2 flex items-center space-x-1">
                                                     <i class="fas fa-exclamation-circle"></i>
                                                     <span><?= $validation->getError('email') ?></span>
                                                 </p>
                                             <?php endif; ?>
                                         </div>

                                         <div class="group/field relative">
                                             <label class="block text-sm font-bold text-gray-700 mb-3 flex items-center space-x-2">
                                                 <i class="fas fa-phone text-emerald-600"></i>
                                                 <span><?= esc(lang('UI.patientPhoneNumber')) ?> *</span>
                                             </label>
                                             <div class="relative">
                                                 <input type="tel" id="phone" name="phone"
                                                        class="w-full px-4 py-3 bg-white/80 border-2 border-gray-200 rounded-xl shadow-lg focus:outline-none focus:ring-4 focus:ring-emerald-500/20 focus:border-emerald-500 transition-all duration-300 hover:shadow-xl <?= $validation->hasError('phone') ? 'border-red-500 ring-red-500/20' : '' ?>"
                                                        value="<?= old('phone', $patient['phone']) ?>" placeholder="<?= esc(lang('UI.patientPlaceholderPhone')) ?>" required>

                                             </div>
                                             <?php if ($validation->hasError('phone')): ?>
                                                 <p class="text-red-500 text-sm mt-2 flex items-center space-x-1">
                                                     <i class="fas fa-exclamation-circle"></i>
                                                     <span><?= $validation->getError('phone') ?></span>
                                                 </p>
                                             <?php endif; ?>
                                         </div>
                                     </div>

                                     <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                                         <div class="group/field relative">
                                             <label class="block text-sm font-bold text-gray-700 mb-3 flex items-center space-x-2">
                                                 <i class="fas fa-calendar text-amber-600"></i>
                                                 <span><?= esc(lang('UI.patientDateOfBirth')) ?> *</span>
                                             </label>
                                             <div class="relative">
                                                 <input type="text" id="date_of_birth" name="date_of_birth" data-clinic-date="1"
                                                        class="w-full px-4 py-3 bg-white/80 border-2 border-gray-200 rounded-xl shadow-lg focus:outline-none focus:ring-4 focus:ring-amber-500/20 focus:border-amber-500 transition-all duration-300 hover:shadow-xl <?= $validation->hasError('date_of_birth') ? 'border-red-500 ring-red-500/20' : '' ?>"
                                                        value="<?= old('date_of_birth', $patient['date_of_birth']) ?>" required>

                                             </div>
                                             <?php if ($validation->hasError('date_of_birth')): ?>
                                                 <p class="text-red-500 text-sm mt-2 flex items-center space-x-1">
                                                     <i class="fas fa-exclamation-circle"></i>
                                                     <span><?= $validation->getError('date_of_birth') ?></span>
                                                 </p>
                                             <?php endif; ?>
                                         </div>

                                         <div class="group/field relative">
                                             <label class="block text-sm font-bold text-gray-700 mb-3 flex items-center space-x-2">
                                                 <i class="fas fa-venus-mars text-rose-600"></i>
                                                 <span><?= esc(lang('UI.patientGender')) ?> *</span>
                                             </label>
                                             <div class="relative">
                                                 <select id="gender" name="gender" data-force-select2
                                                         class="w-full px-4 py-3 bg-white/80 border-2 border-gray-200 rounded-xl shadow-lg focus:outline-none focus:ring-4 focus:ring-rose-500/20 focus:border-rose-500 transition-all duration-300 hover:shadow-xl <?= $validation->hasError('gender') ? 'border-red-500 ring-red-500/20' : '' ?>" required>
                                                     <option value=""><?= esc(lang('UI.patientSelectGender')) ?></option>
                                                     <option value="male" <?= old('gender', $patient['gender']) == 'male' ? 'selected' : '' ?>><?= esc(lang('UI.patientMale')) ?></option>
                                                     <option value="female" <?= old('gender', $patient['gender']) == 'female' ? 'selected' : '' ?>><?= esc(lang('UI.patientFemale')) ?></option>
                                                     <option value="other" <?= old('gender', $patient['gender']) == 'other' ? 'selected' : '' ?>><?= esc(lang('UI.patientOther')) ?></option>
                                                 </select>

                                             </div>
                                             <?php if ($validation->hasError('gender')): ?>
                                                 <p class="text-red-500 text-sm mt-2 flex items-center space-x-1">
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
                        <div class="group/section relative">

                            <div class="v4-form-section">
                                <div class="flex items-center space-x-3 mb-6">
                                    <div class="w-8 h-8 rounded-lg flex items-center justify-center">
                                        <i class="fas fa-city text-white text-sm"></i>
                                    </div>
                                    <h4 class="text-xl font-bold text-gray-800"><?= esc(lang('UI.patientContactDetails')) ?></h4>
                                </div>

                                <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                                    <div class="group/field relative">
                                        <label class="block text-sm font-bold text-gray-700 mb-3 flex items-center space-x-2">
                                            <i class="fas fa-city text-green-600"></i>
                                            <span><?= esc(lang('UI.patientCity')) ?></span>
                                        </label>
                                        <div class="relative">
                                            <input type="text" id="city" name="city"
                                                   class="w-full px-4 py-3 bg-white/80 border-2 border-gray-200 rounded-xl shadow-lg focus:outline-none focus:ring-4 focus:ring-green-500/20 focus:border-green-500 transition-all duration-300 hover:shadow-xl <?= $validation->hasError('city') ? 'border-red-500 ring-red-500/20' : '' ?>"
                                                   value="<?= old('city', $patient['city']) ?>" placeholder="<?= esc(lang('UI.patientPlaceholderCity')) ?>">

                                        </div>
                                        <?php if ($validation->hasError('city')): ?>
                                            <p class="text-red-500 text-sm mt-2 flex items-center space-x-1">
                                                <i class="fas fa-exclamation-circle"></i>
                                                <span><?= $validation->getError('city') ?></span>
                                            </p>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Medical Alerts -->
                        <div class="group/section relative">

                            <div class="v4-form-section">
                                <div class="flex items-center space-x-3 mb-6">
                                    <div class="w-8 h-8 rounded-lg flex items-center justify-center">
                                        <i class="fas fa-heartbeat text-white text-sm"></i>
                                    </div>
                                    <h4 class="text-xl font-bold text-gray-800"><?= esc(lang('UI.patientMedicalAlertsQuickCheck')) ?></h4>
                                </div>

                                <div class="v4-radius border border-slate-200 v4-surface-fill p-4 md:p-5">
                                    <div class="grid grid-cols-1 xl:grid-cols-2 gap-3">
                                        <?php
                                        $medicalAlertItems = [
                                            ['key' => 'alert_has_allergies', 'icon' => 'fas fa-allergies', 'icon_class' => 'text-red-500', 'label' => lang('UI.patientAnyAllergies'), 'detail_name' => 'alert_allergies_details', 'detail_label' => lang('UI.patientAllergyDetails'), 'detail_type' => 'textarea'],
                                            ['key' => 'alert_taking_medications', 'icon' => 'fas fa-pills', 'icon_class' => 'text-blue-500', 'label' => lang('UI.patientTakingMedications'), 'detail_name' => 'alert_medications_details', 'detail_label' => lang('UI.patientMedicationDetails'), 'detail_type' => 'textarea'],
                                            ['key' => 'alert_blood_thinners', 'icon' => 'fas fa-tint', 'icon_class' => 'text-rose-500', 'label' => lang('UI.patientTakingBloodThinners'), 'detail_name' => 'alert_blood_thinners_details', 'detail_label' => lang('UI.patientBloodThinnerDetails'), 'detail_type' => 'input'],
                                            ['key' => 'alert_diabetes', 'icon' => 'fas fa-syringe', 'icon_class' => 'text-amber-500', 'label' => lang('UI.patientDiabetes'), 'detail_name' => 'alert_diabetes_details', 'detail_label' => lang('UI.patientDiabetesDetails'), 'detail_type' => 'input'],
                                            ['key' => 'alert_high_blood_pressure', 'icon' => 'fas fa-heartbeat', 'icon_class' => 'text-pink-500', 'label' => lang('UI.patientHighBloodPressure'), 'detail_name' => 'alert_blood_pressure_details', 'detail_label' => lang('UI.patientBloodPressureDetails'), 'detail_type' => 'input'],
                                            ['key' => 'alert_heart_condition', 'icon' => 'fas fa-heart', 'icon_class' => 'text-red-600', 'label' => lang('UI.patientHeartCondition'), 'detail_name' => 'alert_heart_condition_details', 'detail_label' => lang('UI.patientHeartConditionDetails'), 'detail_type' => 'textarea'],
                                            ['key' => 'alert_pregnant', 'icon' => 'fas fa-baby', 'icon_class' => 'text-fuchsia-500', 'label' => lang('UI.patientPregnant'), 'detail_name' => 'alert_pregnancy_details', 'detail_label' => lang('UI.patientPregnancyDetails'), 'detail_type' => 'input', 'pregnancy_only' => true],
                                            ['key' => 'alert_anesthesia_problem', 'icon' => 'fas fa-mask-face', 'icon_class' => 'text-indigo-500', 'label' => lang('UI.patientAnesthesiaProblem'), 'detail_name' => 'alert_anesthesia_problem_details', 'detail_label' => lang('UI.patientAnesthesiaReactionDetails'), 'detail_type' => 'textarea'],
                                        ];
                                        ?>
                                        <?php foreach ($medicalAlertItems as $item): ?>
                                            <?php
                                            $isChecked = $alertBoolValue($item['key'], $patient);
                                            $detailVisible = $isChecked;
                                            $detailValue = old($item['detail_name'], $patient[$item['detail_name']] ?? '');
                                            ?>
                                            <div class="rounded-lg border border-slate-200 bg-slate-50/70 p-3" data-medical-alert-item <?= !empty($item['pregnancy_only']) ? 'data-pregnancy-only="1"' : '' ?>>
                                                <div class="flex items-center justify-between gap-3">
                                                    <label for="<?= esc($item['key']) ?>" class="text-sm font-medium text-gray-700 leading-snug pr-2 flex items-start gap-2">
                                                        <i class="<?= esc($item['icon']) ?> <?= esc($item['icon_class'] ?? 'text-slate-500') ?> mt-0.5 w-4 text-center"></i>
                                                        <span><?= esc($item['label']) ?></span>
                                                    </label>
                                                    <div class="flex items-center gap-2 shrink-0">
                                                        <span class="text-xs font-semibold text-gray-500"><?= esc(lang('UI.patientNo')) ?></span>
                                                        <label class="inline-flex items-center cursor-pointer">
                                                            <input type="hidden" name="<?= esc($item['key']) ?>" value="0">
                                                            <input type="checkbox"
                                                                   id="<?= esc($item['key']) ?>"
                                                                   name="<?= esc($item['key']) ?>"
                                                                   value="1"
                                                                   class="sr-only medical-alert-toggle"
                                                                   data-track-id="<?= esc($item['key']) ?>_track"
                                                                   data-dot-id="<?= esc($item['key']) ?>_dot"
                                                                   data-detail-target="<?= esc($item['detail_name']) ?>_wrap"
                                                                   <?= $isChecked ? 'checked' : '' ?>>
                                                            <div id="<?= esc($item['key']) ?>_track" class="toggle-track w-11 h-6 <?= $isChecked ? 'bg-blue-500' : 'bg-gray-200' ?> rounded-full relative">
                                                                <div id="<?= esc($item['key']) ?>_dot" class="toggle-dot absolute left-1 top-1 v4-surface-fill w-4 h-4 rounded-full" style="transform: <?= $isChecked ? (!empty($is_rtl) ? 'translateX(-20px)' : 'translateX(20px)') : 'translateX(0)' ?>;"></div>
                                                            </div>
                                                        </label>
                                                        <span class="text-xs font-semibold v4-text-action"><?= esc(lang('UI.patientYes')) ?></span>
                                                    </div>
                                                </div>
                                                <div id="<?= esc($item['detail_name']) ?>_wrap" class="mt-3 <?= $detailVisible ? '' : 'hidden' ?>" data-medical-alert-detail-wrap>
                                                    <label for="<?= esc($item['detail_name']) ?>" class="block text-xs font-semibold text-gray-600 mb-2"><?= esc($item['detail_label']) ?></label>
                                                    <?php if ($item['detail_type'] === 'textarea'): ?>
                                                        <textarea id="<?= esc($item['detail_name']) ?>" name="<?= esc($item['detail_name']) ?>" rows="2" class="w-full px-3 py-2.5 text-sm v4-surface-fill border border-gray-300 v4-radius" <?= $detailVisible ? '' : 'disabled' ?>><?= esc($detailValue) ?></textarea>
                                                    <?php else: ?>
                                                        <input id="<?= esc($item['detail_name']) ?>" name="<?= esc($item['detail_name']) ?>" type="text" value="<?= esc($detailValue) ?>" class="w-full px-3 py-2.5 text-sm v4-surface-fill border border-gray-300 v4-radius" <?= $detailVisible ? '' : 'disabled' ?>>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            </div>
                        </div>


                        <!-- Additional Notes -->
                        <div class="group/section relative">

                            <div class="v4-form-section">
                                <div class="flex items-center space-x-3 mb-6">
                                    <div class="w-8 h-8 rounded-lg flex items-center justify-center">
                                        <i class="fas fa-sticky-note text-white text-sm"></i>
                                    </div>
                                    <h4 class="text-xl font-bold text-gray-800"><?= esc(lang('UI.patientAdditionalNotes')) ?></h4>
                                </div>

                                 <div class="group/field relative">
                                     <label class="block text-sm font-bold text-gray-700 mb-3 flex items-center space-x-2">
                                         <i class="fas fa-sticky-note text-gray-600"></i>
                                         <span><?= esc(lang('UI.patientAdditionalNotes')) ?></span>
                                     </label>
                                     <div class="relative">
                                         <textarea id="notes" name="notes" rows="4"
                                                   class="w-full px-4 py-3 bg-white/80 border-2 border-gray-200 rounded-xl shadow-lg focus:outline-none focus:ring-4 focus:ring-gray-500/20 focus:border-gray-500 transition-all duration-300 hover:shadow-xl resize-none <?= $validation->hasError('notes') ? 'border-red-500 ring-red-500/20' : '' ?>"
                                                   placeholder="<?= esc(lang('UI.patientAdditionalNotesPlaceholder')) ?>"><?= old('notes', $patient['notes']) ?></textarea>
                                         <div class="absolute bottom-3 <?= !empty($is_rtl) ? 'left-3' : 'right-3' ?> text-gray-400 text-xs">
                                             <i class="fas fa-edit"></i>
                                         </div>
                                     </div>
                                     <?php if ($validation->hasError('notes')): ?>
                                         <p class="text-red-500 text-sm mt-2 flex items-center space-x-1">
                                             <i class="fas fa-exclamation-circle"></i>
                                             <span><?= $validation->getError('notes') ?></span>
                                         </p>
                                     <?php endif; ?>
                                 </div>
                            </div>
                        </div>

                        <!-- Form Actions -->
                        <div class="flex flex-col sm:flex-row justify-end gap-4 sm:gap-12 pt-6 border-t border-white/20">
                            <a href="<?= esc(workflow_cancel('patient/' . $patient['id'], $return_to ?? ('patient/' . $patient['id']))) ?>" class="group/btn relative inline-flex items-center justify-center px-8 py-3 border-2 border-gray-300 text-gray-700 text-sm font-bold v4-radius hover:bg-gradient-to-r hover:border-gray-400 transition-all duration-300">

                                <i class="fas fa-times mr-2 relative z-10"></i>
                                <span class="relative z-10"><?= esc(lang('UI.cancel')) ?></span>
                            </a>
                            <button type="submit" class="group/btn relative inline-flex items-center justify-center px-8 py-3 text-white text-sm font-bold v4-radius transition-all duration-300">

                                <i class="fas fa-save mr-2 relative z-10"></i>
                                <span class="relative z-10"><?= esc(lang('UI.patientUpdatePatient')) ?></span>
                            </button>
                        </div>
                    </form></div>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const genderSelect = document.getElementById('gender');

    function syncMedicalAlertToggle(toggle) {
        if (!toggle) return;
        const track = document.getElementById(toggle.dataset.trackId || '');
        const dot = document.getElementById(toggle.dataset.dotId || '');
        const detailWrap = document.getElementById(toggle.dataset.detailTarget || '');

        if (toggle.checked) {
            track?.classList.remove('bg-gray-200');
            track?.classList.add('bg-blue-500');
            window.applyToggleDotState?.(dot, true);
            if (detailWrap) {
                detailWrap.classList.remove('hidden');
                detailWrap.querySelectorAll('input, textarea, select').forEach((field) => { field.disabled = false; });
            }
        } else {
            track?.classList.remove('bg-blue-500');
            track?.classList.add('bg-gray-200');
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


