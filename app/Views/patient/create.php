<?= $this->extend('layouts/main_auth') ?>

<?= $this->section('content') ?>
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
?>

<style>
.toggle-track {
    transition: background-color 0.2s ease;
}

.toggle-dot {
    transition: transform 0.2s ease;
}
</style>

<div class="min-h-screen bg-slate-50">
    <div class="container mx-auto px-4 py-6 max-w-7xl">
        <div class="group relative">
            <div class="relative bg-white rounded-2xl shadow-2xl shadow-blue-500/10 group-hover:shadow-blue-500/20 transition-all duration-500 overflow-hidden">
                <div class="p-8 border-b border-slate-100">
                    <div class="flex flex-col lg:flex-row lg:justify-between lg:items-center space-y-4 lg:space-y-0">
                        <div class="flex items-center space-x-4">
                            <div class="relative">
                                <div class="absolute inset-0 bg-gradient-to-br from-blue-500 to-cyan-600 rounded-2xl blur-lg opacity-75 group-hover:opacity-100 transition-opacity duration-300"></div>
                                <div class="relative w-12 h-12 bg-gradient-to-br from-blue-500 to-cyan-600 rounded-2xl flex items-center justify-center shadow-xl group-hover:scale-110 group-hover:rotate-3 transition-all duration-300">
                                    <i class="fas fa-user-plus text-white text-lg"></i>
                                </div>
                            </div>
                            <div>
                                <h3 class="text-2xl font-black text-gray-900 group-hover:text-blue-900 transition-colors duration-300">Register New Patient</h3>
                                <p class="text-gray-600 font-medium">Start with essentials, then enable optional details when needed</p>
                            </div>
                        </div>
                        <a href="<?= !empty($return_to) ? base_url($return_to) : base_url('patient') ?>" class="group/btn relative inline-flex items-center px-6 py-3 border-2 border-gray-300 text-gray-700 text-sm font-bold rounded-xl hover:bg-gradient-to-r hover:from-gray-50 hover:to-slate-50 hover:border-gray-400 transition-all duration-300 hover:scale-105">
                            <i class="fas fa-arrow-left mr-2"></i>
                            <span>Back</span>
                        </a>
                    </div>
                </div>

                <div class="p-8">
                    <?php if (session('error')): ?>
                        <div class="mb-6 rounded-xl border border-red-200 bg-red-50 p-4 text-sm text-red-700">
                            <?= esc(session('error')) ?>
                        </div>
                    <?php endif; ?>

                    <form action="<?= base_url('patient/store') ?>" method="POST" class="space-y-8">
                        <?= csrf_field() ?>
                        <?php if (!empty($return_to)): ?>
                            <input type="hidden" name="return_to" value="<?= esc($return_to) ?>">
                        <?php endif; ?>

                        <section class="rounded-xl border border-slate-200 bg-slate-50/60 p-6 md:p-7">
                            <h4 class="text-lg font-bold text-gray-900 mb-5 flex items-center space-x-2">
                                <i class="fas fa-asterisk text-xs text-blue-600"></i>
                                <span>Required Information</span>
                            </h4>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <label for="first_name" class="block text-sm font-bold text-gray-700 mb-2">First Name *</label>
                                    <input id="first_name" name="first_name" type="text" required value="<?= old('first_name') ?>" class="w-full px-4 py-3 bg-white border rounded-xl focus:outline-none focus:ring-4 focus:ring-blue-500/20 focus:border-blue-500 transition-all duration-200 <?= ($validation && $validation->hasError('first_name')) ? 'border-red-500' : 'border-gray-300' ?>">
                                    <?php if ($validation && $validation->hasError('first_name')): ?>
                                        <p class="text-red-500 text-sm mt-2"><?= esc($validation->getError('first_name')) ?></p>
                                    <?php endif; ?>
                                </div>

                                <div>
                                    <label for="last_name" class="block text-sm font-bold text-gray-700 mb-2">Last Name *</label>
                                    <input id="last_name" name="last_name" type="text" required value="<?= old('last_name') ?>" class="w-full px-4 py-3 bg-white border rounded-xl focus:outline-none focus:ring-4 focus:ring-blue-500/20 focus:border-blue-500 transition-all duration-200 <?= ($validation && $validation->hasError('last_name')) ? 'border-red-500' : 'border-gray-300' ?>">
                                    <?php if ($validation && $validation->hasError('last_name')): ?>
                                        <p class="text-red-500 text-sm mt-2"><?= esc($validation->getError('last_name')) ?></p>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mt-6">
                                <div>
                                    <label for="phone" class="block text-sm font-bold text-gray-700 mb-2">Phone Number *</label>
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
                                        class="w-full px-4 py-3 bg-white border rounded-xl focus:outline-none focus:ring-4 focus:ring-blue-500/20 focus:border-blue-500 transition-all duration-200 <?= ($validation && $validation->hasError('phone')) ? 'border-red-500' : 'border-gray-300' ?>"
                                    >
                                    <?php if ($validation && $validation->hasError('phone')): ?>
                                        <p class="text-red-500 text-sm mt-2"><?= esc($validation->getError('phone')) ?></p>
                                    <?php endif; ?>
                                </div>

                                <div>
                                    <label for="date_of_birth" class="block text-sm font-bold text-gray-700 mb-2">Date of Birth *</label>
                                    <input id="date_of_birth" name="date_of_birth" type="text" data-clinic-date="1" required value="<?= old('date_of_birth') ?>" class="w-full px-4 py-3 bg-white border rounded-xl focus:outline-none focus:ring-4 focus:ring-blue-500/20 focus:border-blue-500 transition-all duration-200 <?= ($validation && $validation->hasError('date_of_birth')) ? 'border-red-500' : 'border-gray-300' ?>">
                                    <?php if ($validation && $validation->hasError('date_of_birth')): ?>
                                        <p class="text-red-500 text-sm mt-2"><?= esc($validation->getError('date_of_birth')) ?></p>
                                    <?php endif; ?>
                                </div>

                                <div>
                                    <label for="gender" class="block text-sm font-bold text-gray-700 mb-2">Gender *</label>
                                    <select id="gender" name="gender" required class="w-full px-4 py-3 bg-white border rounded-xl focus:outline-none focus:ring-4 focus:ring-blue-500/20 focus:border-blue-500 transition-all duration-200 <?= ($validation && $validation->hasError('gender')) ? 'border-red-500' : 'border-gray-300' ?>">
                                        <option value="">Select</option>
                                        <option value="male" <?= old('gender') === 'male' ? 'selected' : '' ?>>Male</option>
                                        <option value="female" <?= old('gender') === 'female' ? 'selected' : '' ?>>Female</option>
                                        <option value="other" <?= old('gender') === 'other' ? 'selected' : '' ?>>Other</option>
                                    </select>
                                    <?php if ($validation && $validation->hasError('gender')): ?>
                                        <p class="text-red-500 text-sm mt-2"><?= esc($validation->getError('gender')) ?></p>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </section>

                        <section class="rounded-xl border border-slate-200 bg-slate-50/60 p-6 md:p-7">
                            <div class="flex items-center justify-between mb-4">
                                <div class="flex items-center space-x-3">
                                    <div class="w-10 h-10 bg-gradient-to-br from-indigo-500 to-blue-600 rounded-xl flex items-center justify-center text-white">
                                        <i class="fas fa-sliders-h"></i>
                                    </div>
                                    <div>
                                        <h4 class="text-lg font-bold text-gray-900">Optional Information</h4>
                                        <p class="text-sm text-gray-500">Enable only when needed</p>
                                    </div>
                                </div>
                                <label class="inline-flex items-center cursor-pointer">
                                    <input type="checkbox" id="optionalEnabled" class="sr-only" <?= $optionalEnabledByOld ? 'checked' : '' ?>>
                                    <div id="optionalTrack" class="toggle-track w-11 h-6 bg-gray-200 rounded-full relative">
                                        <div id="optionalDot" class="toggle-dot absolute left-1 top-1 bg-white w-4 h-4 rounded-full"></div>
                                    </div>
                                    <span class="ml-3 text-sm font-semibold text-gray-700">Enable</span>
                                </label>
                            </div>

                            <div id="optionalSection" class="hidden space-y-6">
                                <div>
                                    <h5 class="text-sm font-semibold text-gray-700 mb-3">Contact Details</h5>
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                        <div>
                                            <label for="email" class="block text-sm font-medium text-gray-700 mb-2">Email</label>
                                            <input id="email" name="email" type="email" value="<?= old('email') ?>" class="w-full px-4 py-3 bg-white border border-gray-300 rounded-xl">
                                            <?php if ($validation && $validation->hasError('email')): ?>
                                                <p class="text-red-500 text-sm mt-2"><?= esc($validation->getError('email')) ?></p>
                                            <?php endif; ?>
                                        </div>
                                        <div>
                                            <label for="city" class="block text-sm font-medium text-gray-700 mb-2">City</label>
                                            <input id="city" name="city" type="text" value="<?= old('city') ?>" class="w-full px-4 py-3 bg-white border border-gray-300 rounded-xl">
                                        </div>
                                    </div>
                                </div>

                                <div>
                                    <h5 class="text-sm font-semibold text-gray-700 mb-3">Medical Alerts (Quick Check)</h5>
                                    <div class="rounded-xl border border-slate-200 bg-white p-4 md:p-5">
                                        <div class="grid grid-cols-1 xl:grid-cols-2 gap-3">
                                            <?php
                                            $medicalAlertItems = [
                                                [
                                                    'key' => 'alert_has_allergies',
                                                    'icon' => 'fas fa-allergies',
                                                    'icon_class' => 'text-red-500',
                                                    'label' => 'Any allergies (medications / latex)?',
                                                    'detail_name' => 'alert_allergies_details',
                                                    'detail_label' => 'Allergy Details',
                                                    'detail_type' => 'textarea',
                                                ],
                                                [
                                                    'key' => 'alert_taking_medications',
                                                    'icon' => 'fas fa-pills',
                                                    'icon_class' => 'text-blue-500',
                                                    'label' => 'Taking any medications now?',
                                                    'detail_name' => 'alert_medications_details',
                                                    'detail_label' => 'Medication Details',
                                                    'detail_type' => 'textarea',
                                                ],
                                                [
                                                    'key' => 'alert_blood_thinners',
                                                    'icon' => 'fas fa-tint',
                                                    'icon_class' => 'text-rose-500',
                                                    'label' => 'Taking blood thinners?',
                                                    'detail_name' => 'alert_blood_thinners_details',
                                                    'detail_label' => 'Blood Thinner Details',
                                                    'detail_type' => 'input',
                                                ],
                                                [
                                                    'key' => 'alert_diabetes',
                                                    'icon' => 'fas fa-syringe',
                                                    'icon_class' => 'text-amber-500',
                                                    'label' => 'Diabetes?',
                                                    'detail_name' => 'alert_diabetes_details',
                                                    'detail_label' => 'Diabetes Details',
                                                    'detail_type' => 'input',
                                                ],
                                                [
                                                    'key' => 'alert_high_blood_pressure',
                                                    'icon' => 'fas fa-heartbeat',
                                                    'icon_class' => 'text-pink-500',
                                                    'label' => 'High blood pressure?',
                                                    'detail_name' => 'alert_blood_pressure_details',
                                                    'detail_label' => 'Blood Pressure Details',
                                                    'detail_type' => 'input',
                                                ],
                                                [
                                                    'key' => 'alert_heart_condition',
                                                    'icon' => 'fas fa-heart',
                                                    'icon_class' => 'text-red-600',
                                                    'label' => 'Heart condition / heart surgery history?',
                                                    'detail_name' => 'alert_heart_condition_details',
                                                    'detail_label' => 'Heart Condition Details',
                                                    'detail_type' => 'textarea',
                                                ],
                                                [
                                                    'key' => 'alert_pregnant',
                                                    'icon' => 'fas fa-baby',
                                                    'icon_class' => 'text-fuchsia-500',
                                                    'label' => 'Pregnant / possibly pregnant?',
                                                    'detail_name' => 'alert_pregnancy_details',
                                                    'detail_label' => 'Pregnancy Details',
                                                    'detail_type' => 'input',
                                                    'pregnancy_only' => true,
                                                ],
                                                [
                                                    'key' => 'alert_anesthesia_problem',
                                                    'icon' => 'fas fa-mask-face',
                                                    'icon_class' => 'text-indigo-500',
                                                    'label' => 'Problem with local anesthesia / sedation before?',
                                                    'detail_name' => 'alert_anesthesia_problem_details',
                                                    'detail_label' => 'Anesthesia Reaction Details',
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
                                                    class="rounded-lg border border-slate-200 bg-slate-50/70 p-3"
                                                    data-medical-alert-item
                                                    <?= $isPregnancyOnly ? 'data-pregnancy-only="1"' : '' ?>
                                                >
                                                    <div class="flex items-center justify-between gap-3">
                                                        <label for="<?= esc($item['key']) ?>" class="text-sm font-medium text-gray-700 leading-snug pr-2 flex items-start gap-2">
                                                            <i class="<?= esc($item['icon']) ?> <?= esc($item['icon_class'] ?? 'text-slate-500') ?> mt-0.5 w-4 text-center"></i>
                                                            <span><?= esc($item['label']) ?></span>
                                                        </label>
                                                        <div class="flex items-center gap-2 shrink-0">
                                                            <span class="text-xs font-semibold text-gray-500">No</span>
                                                            <label class="inline-flex items-center cursor-pointer">
                                                                <input type="hidden" name="<?= esc($item['key']) ?>" value="0">
                                                                <input
                                                                    type="checkbox"
                                                                    id="<?= esc($item['key']) ?>"
                                                                    name="<?= esc($item['key']) ?>"
                                                                    value="1"
                                                                    class="sr-only medical-alert-toggle"
                                                                    data-track-id="<?= esc($item['key']) ?>_track"
                                                                    data-dot-id="<?= esc($item['key']) ?>_dot"
                                                                    data-detail-target="<?= esc($item['detail_name']) ?>_wrap"
                                                                    <?= $isChecked ? 'checked' : '' ?>
                                                                >
                                                                <div id="<?= esc($item['key']) ?>_track" class="toggle-track w-11 h-6 <?= $isChecked ? 'bg-blue-500' : 'bg-gray-200' ?> rounded-full relative">
                                                                    <div id="<?= esc($item['key']) ?>_dot" class="toggle-dot absolute left-1 top-1 bg-white w-4 h-4 rounded-full" style="transform: <?= $isChecked ? 'translateX(20px)' : 'translateX(0)' ?>;"></div>
                                                                </div>
                                                            </label>
                                                            <span class="text-xs font-semibold text-blue-700">Yes</span>
                                                        </div>
                                                    </div>

                                                    <div
                                                        id="<?= esc($item['detail_name']) ?>_wrap"
                                                        class="mt-3 <?= $detailVisible ? '' : 'hidden' ?>"
                                                        data-medical-alert-detail-wrap
                                                    >
                                                        <label for="<?= esc($item['detail_name']) ?>" class="block text-xs font-semibold text-gray-600 mb-2">
                                                            <?= esc($item['detail_label']) ?>
                                                        </label>
                                                        <?php if ($item['detail_type'] === 'textarea'): ?>
                                                            <textarea
                                                                id="<?= esc($item['detail_name']) ?>"
                                                                name="<?= esc($item['detail_name']) ?>"
                                                                rows="2"
                                                                class="w-full px-3 py-2.5 text-sm bg-white border border-gray-300 rounded-xl"
                                                                <?= $detailVisible ? '' : 'disabled' ?>
                                                            ><?= esc(old($item['detail_name'])) ?></textarea>
                                                        <?php else: ?>
                                                            <input
                                                                id="<?= esc($item['detail_name']) ?>"
                                                                name="<?= esc($item['detail_name']) ?>"
                                                                type="text"
                                                                value="<?= esc(old($item['detail_name'])) ?>"
                                                                class="w-full px-3 py-2.5 text-sm bg-white border border-gray-300 rounded-xl"
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
                                    <h5 class="text-sm font-semibold text-gray-700 mb-3">Insurance & Notes</h5>
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                        <div>
                                            <label for="insurance_provider" class="block text-sm font-medium text-gray-700 mb-2">Insurance Provider</label>
                                            <input id="insurance_provider" name="insurance_provider" type="text" value="<?= old('insurance_provider') ?>" class="w-full px-4 py-3 bg-white border border-gray-300 rounded-xl">
                                        </div>
                                        <div>
                                            <label for="insurance_number" class="block text-sm font-medium text-gray-700 mb-2">Insurance Number</label>
                                            <input id="insurance_number" name="insurance_number" type="text" value="<?= old('insurance_number') ?>" class="w-full px-4 py-3 bg-white border border-gray-300 rounded-xl">
                                        </div>
                                        <div class="md:col-span-2">
                                            <label for="notes" class="block text-sm font-medium text-gray-700 mb-2">Notes</label>
                                            <textarea id="notes" name="notes" rows="3" class="w-full px-4 py-3 bg-white border border-gray-300 rounded-xl"><?= old('notes') ?></textarea>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </section>

                        <div class="flex flex-col-reverse sm:flex-row sm:justify-end gap-3 pt-6 border-t border-slate-200">
                            <a href="<?= !empty($return_to) ? base_url($return_to) : base_url('patient') ?>" class="inline-flex items-center justify-center px-6 py-3 border border-gray-300 text-gray-700 text-sm font-semibold rounded-xl hover:bg-gray-50">
                                Cancel
                            </a>
                            <button type="submit" name="post_submit_action" value="save_only" class="inline-flex items-center justify-center px-6 py-3 border border-blue-200 bg-white text-blue-700 text-sm font-bold rounded-xl hover:bg-blue-50 transition-all duration-200 shadow-sm hover:shadow-md">
                                <i class="fas fa-user-plus mr-2"></i>
                                Save
                            </button>
                            <button type="submit" name="post_submit_action" value="save_and_book" class="inline-flex items-center justify-center px-6 py-3 bg-gradient-to-r from-blue-600 to-cyan-600 text-white text-sm font-bold rounded-xl hover:from-blue-700 hover:to-cyan-700 transition-all duration-200 shadow-lg hover:shadow-xl">
                                <i class="fas fa-calendar-plus mr-2"></i>
                                Save &amp; Book Appointment
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const optionalToggle = document.getElementById('optionalEnabled');
    const optionalSection = document.getElementById('optionalSection');
    const optionalTrack = document.getElementById('optionalTrack');
    const optionalDot = document.getElementById('optionalDot');
    const genderSelect = document.getElementById('gender');
    const patientForm = document.querySelector('form[action$="patient/store"]');
    const phoneInput = document.getElementById('phone');

    function validatePhoneField() {
        if (!phoneInput) {
            return true;
        }

        const value = (phoneInput.value || '').trim();
        const isValid = /^\d{9,15}$/.test(value);
        phoneInput.classList.toggle('border-red-500', !isValid);
        phoneInput.classList.toggle('border-gray-300', isValid);
        return isValid;
    }

    function syncOptionalState() {
        if (!optionalToggle || !optionalSection) {
            return;
        }

        if (optionalToggle.checked) {
            optionalSection.classList.remove('hidden');
            optionalTrack?.classList.remove('bg-gray-200');
            optionalTrack?.classList.add('bg-blue-500');
            if (optionalDot) {
                optionalDot.style.transform = 'translateX(20px)';
            }
        } else {
            optionalSection.classList.add('hidden');
            optionalTrack?.classList.remove('bg-blue-500');
            optionalTrack?.classList.add('bg-gray-200');
            if (optionalDot) {
                optionalDot.style.transform = 'translateX(0)';
            }
        }
    }

    if (optionalToggle) {
        optionalToggle.addEventListener('change', syncOptionalState);
        syncOptionalState();
    }

    if (phoneInput) {
        phoneInput.addEventListener('input', validatePhoneField);
        phoneInput.addEventListener('blur', validatePhoneField);
        validatePhoneField();
    }

    if (patientForm) {
        patientForm.addEventListener('submit', function (event) {
            if (!validatePhoneField()) {
                event.preventDefault();
                phoneInput?.focus();
            }
        });
    }

    function syncMedicalAlertToggle(toggle) {
        if (!toggle) {
            return;
        }

        const track = document.getElementById(toggle.dataset.trackId || '');
        const dot = document.getElementById(toggle.dataset.dotId || '');
        const detailWrap = document.getElementById(toggle.dataset.detailTarget || '');

        if (toggle.checked) {
            track?.classList.remove('bg-gray-200');
            track?.classList.add('bg-blue-500');
            if (dot) {
                dot.style.transform = 'translateX(20px)';
            }
            if (detailWrap) {
                detailWrap.classList.remove('hidden');
                detailWrap.querySelectorAll('input, textarea, select').forEach(function (field) {
                    field.disabled = false;
                });
            }
        } else {
            track?.classList.remove('bg-blue-500');
            track?.classList.add('bg-gray-200');
            if (dot) {
                dot.style.transform = 'translateX(0)';
            }
            if (detailWrap) {
                detailWrap.classList.add('hidden');
                detailWrap.querySelectorAll('input, textarea, select').forEach(function (field) {
                    field.disabled = true;
                    field.value = '';
                });
            }
        }
    }

    function syncPregnancyVisibility() {
        const pregnancyItems = document.querySelectorAll('[data-pregnancy-only="1"]');
        const gender = (genderSelect?.value || '').toLowerCase();
        const showPregnancy = gender === '' || gender === 'female';

        pregnancyItems.forEach(function (item) {
            const toggle = item.querySelector('.medical-alert-toggle');
            const hiddenInputs = item.querySelectorAll('input[type="hidden"]');
            const detailInputs = item.querySelectorAll('[data-medical-alert-detail-wrap] input, [data-medical-alert-detail-wrap] textarea');

            if (showPregnancy) {
                item.classList.remove('hidden');
                if (toggle) {
                    toggle.disabled = false;
                }
                hiddenInputs.forEach(function (input) { input.disabled = false; });
                syncMedicalAlertToggle(toggle);
                return;
            }

            item.classList.add('hidden');
            if (toggle) {
                toggle.checked = false;
                toggle.disabled = true;
                syncMedicalAlertToggle(toggle);
            }
            hiddenInputs.forEach(function (input) { input.disabled = true; });
            detailInputs.forEach(function (input) {
                input.disabled = true;
                input.value = '';
            });
        });
    }

    document.querySelectorAll('.medical-alert-toggle').forEach(function (toggle) {
        toggle.addEventListener('change', function () {
            syncMedicalAlertToggle(toggle);
        });
        syncMedicalAlertToggle(toggle);
    });

    genderSelect?.addEventListener('change', syncPregnancyVisibility);
    syncPregnancyVisibility();
});
</script>
<?= $this->endSection() ?>
