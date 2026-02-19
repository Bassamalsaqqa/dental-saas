<?= $this->extend('layouts/main_auth') ?>

<?= $this->section('content') ?>
<?php
$validation = $validation ?? null;
$optionalFieldKeys = [
    'email',
    'country',
    'address',
    'city',
    'state',
    'zip_code',
    'emergency_contact_name',
    'emergency_contact_phone',
    'medical_history',
    'allergies',
    'insurance_provider',
    'insurance_number',
    'notes',
];
$optionalEnabledByOld = false;
foreach ($optionalFieldKeys as $fieldKey) {
    if (old($fieldKey) !== null && old($fieldKey) !== '') {
        $optionalEnabledByOld = true;
        break;
    }
}
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
                                    <input id="phone" name="phone" type="tel" required value="<?= old('phone') ?>" class="w-full px-4 py-3 bg-white border rounded-xl focus:outline-none focus:ring-4 focus:ring-blue-500/20 focus:border-blue-500 transition-all duration-200 <?= ($validation && $validation->hasError('phone')) ? 'border-red-500' : 'border-gray-300' ?>">
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
                                            <label for="country" class="block text-sm font-medium text-gray-700 mb-2">Country</label>
                                            <input id="country" name="country" type="text" value="<?= old('country') ?>" class="w-full px-4 py-3 bg-white border border-gray-300 rounded-xl">
                                        </div>
                                        <div class="md:col-span-2">
                                            <label for="address" class="block text-sm font-medium text-gray-700 mb-2">Address</label>
                                            <textarea id="address" name="address" rows="2" class="w-full px-4 py-3 bg-white border border-gray-300 rounded-xl"><?= old('address') ?></textarea>
                                        </div>
                                        <div>
                                            <label for="city" class="block text-sm font-medium text-gray-700 mb-2">City</label>
                                            <input id="city" name="city" type="text" value="<?= old('city') ?>" class="w-full px-4 py-3 bg-white border border-gray-300 rounded-xl">
                                        </div>
                                        <div>
                                            <label for="state" class="block text-sm font-medium text-gray-700 mb-2">State</label>
                                            <input id="state" name="state" type="text" value="<?= old('state') ?>" class="w-full px-4 py-3 bg-white border border-gray-300 rounded-xl">
                                        </div>
                                        <div>
                                            <label for="zip_code" class="block text-sm font-medium text-gray-700 mb-2">ZIP Code</label>
                                            <input id="zip_code" name="zip_code" type="text" value="<?= old('zip_code') ?>" class="w-full px-4 py-3 bg-white border border-gray-300 rounded-xl">
                                        </div>
                                    </div>
                                </div>

                                <div>
                                    <h5 class="text-sm font-semibold text-gray-700 mb-3">Emergency Contact</h5>
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                        <div>
                                            <label for="emergency_contact_name" class="block text-sm font-medium text-gray-700 mb-2">Contact Name</label>
                                            <input id="emergency_contact_name" name="emergency_contact_name" type="text" value="<?= old('emergency_contact_name') ?>" class="w-full px-4 py-3 bg-white border border-gray-300 rounded-xl">
                                        </div>
                                        <div>
                                            <label for="emergency_contact_phone" class="block text-sm font-medium text-gray-700 mb-2">Contact Phone</label>
                                            <input id="emergency_contact_phone" name="emergency_contact_phone" type="text" value="<?= old('emergency_contact_phone') ?>" class="w-full px-4 py-3 bg-white border border-gray-300 rounded-xl">
                                        </div>
                                    </div>
                                </div>

                                <div>
                                    <h5 class="text-sm font-semibold text-gray-700 mb-3">Clinical and Insurance</h5>
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                        <div class="md:col-span-2">
                                            <label for="medical_history" class="block text-sm font-medium text-gray-700 mb-2">Medical History</label>
                                            <textarea id="medical_history" name="medical_history" rows="3" class="w-full px-4 py-3 bg-white border border-gray-300 rounded-xl"><?= old('medical_history') ?></textarea>
                                        </div>
                                        <div class="md:col-span-2">
                                            <label for="allergies" class="block text-sm font-medium text-gray-700 mb-2">Allergies</label>
                                            <textarea id="allergies" name="allergies" rows="2" class="w-full px-4 py-3 bg-white border border-gray-300 rounded-xl"><?= old('allergies') ?></textarea>
                                        </div>
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
                            <button type="submit" class="inline-flex items-center justify-center px-6 py-3 bg-gradient-to-r from-blue-600 to-cyan-600 text-white text-sm font-bold rounded-xl hover:from-blue-700 hover:to-cyan-700 transition-all duration-200 shadow-lg hover:shadow-xl">
                                <i class="fas fa-user-plus mr-2"></i>
                                Create Patient
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
});
</script>
<?= $this->endSection() ?>
