<?= $this->extend('layouts/main_auth') ?>

<?= $this->section('content') ?>
<?php $validation = $validation ?? null; ?>
<div class="min-h-screen bg-gray-50">
    <div class="mx-auto max-w-5xl px-4 py-6 sm:px-6 lg:px-8">
        <div class="mb-6 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Register Patient</h1>
                <p class="text-sm text-gray-600">Start with essentials. Add optional details only if needed.</p>
            </div>
            <a href="<?= !empty($return_to) ? base_url($return_to) : base_url('patient') ?>" class="inline-flex items-center rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-100">
                <i class="fas fa-arrow-left mr-2"></i>Back to Patients
            </a>
        </div>

        <?php if (session('error')): ?>
            <div class="mb-4 rounded-md border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
                <?= esc(session('error')) ?>
            </div>
        <?php endif; ?>

        <form action="<?= base_url('patient/store') ?>" method="POST" class="space-y-6">
            <?= csrf_field() ?>
            <?php if (!empty($return_to)): ?>
                <input type="hidden" name="return_to" value="<?= esc($return_to) ?>">
            <?php endif; ?>

            <div class="rounded-lg border border-gray-200 bg-white p-5 shadow-sm">
                <h2 class="mb-4 text-base font-semibold text-gray-900">Required Information</h2>
                <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                    <div>
                        <label for="first_name" class="mb-1 block text-sm font-medium text-gray-700">First Name *</label>
                        <input id="first_name" name="first_name" type="text" required value="<?= old('first_name') ?>" class="w-full rounded-md border px-3 py-2 text-sm <?= ($validation && $validation->hasError('first_name')) ? 'border-red-500' : 'border-gray-300' ?>">
                        <?php if ($validation && $validation->hasError('first_name')): ?>
                            <p class="mt-1 text-xs text-red-600"><?= esc($validation->getError('first_name')) ?></p>
                        <?php endif; ?>
                    </div>

                    <div>
                        <label for="last_name" class="mb-1 block text-sm font-medium text-gray-700">Last Name *</label>
                        <input id="last_name" name="last_name" type="text" required value="<?= old('last_name') ?>" class="w-full rounded-md border px-3 py-2 text-sm <?= ($validation && $validation->hasError('last_name')) ? 'border-red-500' : 'border-gray-300' ?>">
                        <?php if ($validation && $validation->hasError('last_name')): ?>
                            <p class="mt-1 text-xs text-red-600"><?= esc($validation->getError('last_name')) ?></p>
                        <?php endif; ?>
                    </div>

                    <div>
                        <label for="phone" class="mb-1 block text-sm font-medium text-gray-700">Phone Number *</label>
                        <input id="phone" name="phone" type="tel" required value="<?= old('phone') ?>" class="w-full rounded-md border px-3 py-2 text-sm <?= ($validation && $validation->hasError('phone')) ? 'border-red-500' : 'border-gray-300' ?>">
                        <?php if ($validation && $validation->hasError('phone')): ?>
                            <p class="mt-1 text-xs text-red-600"><?= esc($validation->getError('phone')) ?></p>
                        <?php endif; ?>
                    </div>

                    <div>
                        <label for="date_of_birth" class="mb-1 block text-sm font-medium text-gray-700">Date of Birth *</label>
                        <input id="date_of_birth" name="date_of_birth" type="text" data-clinic-date="1" required value="<?= old('date_of_birth') ?>" class="w-full rounded-md border px-3 py-2 text-sm <?= ($validation && $validation->hasError('date_of_birth')) ? 'border-red-500' : 'border-gray-300' ?>">
                        <?php if ($validation && $validation->hasError('date_of_birth')): ?>
                            <p class="mt-1 text-xs text-red-600"><?= esc($validation->getError('date_of_birth')) ?></p>
                        <?php endif; ?>
                    </div>

                    <div class="md:col-span-2">
                        <label for="gender" class="mb-1 block text-sm font-medium text-gray-700">Gender *</label>
                        <select id="gender" name="gender" required class="w-full rounded-md border px-3 py-2 text-sm <?= ($validation && $validation->hasError('gender')) ? 'border-red-500' : 'border-gray-300' ?>">
                            <option value="">Select</option>
                            <option value="male" <?= old('gender') === 'male' ? 'selected' : '' ?>>Male</option>
                            <option value="female" <?= old('gender') === 'female' ? 'selected' : '' ?>>Female</option>
                            <option value="other" <?= old('gender') === 'other' ? 'selected' : '' ?>>Other</option>
                        </select>
                        <?php if ($validation && $validation->hasError('gender')): ?>
                            <p class="mt-1 text-xs text-red-600"><?= esc($validation->getError('gender')) ?></p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <details class="rounded-lg border border-gray-200 bg-white p-5 shadow-sm">
                <summary class="cursor-pointer text-sm font-semibold text-gray-900">Optional Contact Details</summary>
                <div class="mt-4 grid grid-cols-1 gap-4 md:grid-cols-2">
                    <div>
                        <label for="email" class="mb-1 block text-sm font-medium text-gray-700">Email</label>
                        <input id="email" name="email" type="email" value="<?= old('email') ?>" class="w-full rounded-md border px-3 py-2 text-sm <?= ($validation && $validation->hasError('email')) ? 'border-red-500' : 'border-gray-300' ?>">
                        <?php if ($validation && $validation->hasError('email')): ?>
                            <p class="mt-1 text-xs text-red-600"><?= esc($validation->getError('email')) ?></p>
                        <?php endif; ?>
                    </div>
                    <div>
                        <label for="country" class="mb-1 block text-sm font-medium text-gray-700">Country</label>
                        <input id="country" name="country" type="text" value="<?= old('country') ?>" class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm">
                    </div>
                    <div class="md:col-span-2">
                        <label for="address" class="mb-1 block text-sm font-medium text-gray-700">Address</label>
                        <textarea id="address" name="address" rows="2" class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm"><?= old('address') ?></textarea>
                    </div>
                    <div>
                        <label for="city" class="mb-1 block text-sm font-medium text-gray-700">City</label>
                        <input id="city" name="city" type="text" value="<?= old('city') ?>" class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm">
                    </div>
                    <div>
                        <label for="state" class="mb-1 block text-sm font-medium text-gray-700">State</label>
                        <input id="state" name="state" type="text" value="<?= old('state') ?>" class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm">
                    </div>
                    <div>
                        <label for="zip_code" class="mb-1 block text-sm font-medium text-gray-700">ZIP Code</label>
                        <input id="zip_code" name="zip_code" type="text" value="<?= old('zip_code') ?>" class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm">
                    </div>
                </div>
            </details>

            <details class="rounded-lg border border-gray-200 bg-white p-5 shadow-sm">
                <summary class="cursor-pointer text-sm font-semibold text-gray-900">Optional Emergency Contact</summary>
                <div class="mt-4 grid grid-cols-1 gap-4 md:grid-cols-2">
                    <div>
                        <label for="emergency_contact_name" class="mb-1 block text-sm font-medium text-gray-700">Contact Name</label>
                        <input id="emergency_contact_name" name="emergency_contact_name" type="text" value="<?= old('emergency_contact_name') ?>" class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm">
                    </div>
                    <div>
                        <label for="emergency_contact_phone" class="mb-1 block text-sm font-medium text-gray-700">Contact Phone</label>
                        <input id="emergency_contact_phone" name="emergency_contact_phone" type="text" value="<?= old('emergency_contact_phone') ?>" class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm">
                    </div>
                </div>
            </details>

            <details class="rounded-lg border border-gray-200 bg-white p-5 shadow-sm">
                <summary class="cursor-pointer text-sm font-semibold text-gray-900">Optional Clinical and Insurance Notes</summary>
                <div class="mt-4 grid grid-cols-1 gap-4 md:grid-cols-2">
                    <div class="md:col-span-2">
                        <label for="medical_history" class="mb-1 block text-sm font-medium text-gray-700">Medical History</label>
                        <textarea id="medical_history" name="medical_history" rows="3" class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm"><?= old('medical_history') ?></textarea>
                    </div>
                    <div class="md:col-span-2">
                        <label for="allergies" class="mb-1 block text-sm font-medium text-gray-700">Allergies</label>
                        <textarea id="allergies" name="allergies" rows="2" class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm"><?= old('allergies') ?></textarea>
                    </div>
                    <div>
                        <label for="insurance_provider" class="mb-1 block text-sm font-medium text-gray-700">Insurance Provider</label>
                        <input id="insurance_provider" name="insurance_provider" type="text" value="<?= old('insurance_provider') ?>" class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm">
                    </div>
                    <div>
                        <label for="insurance_number" class="mb-1 block text-sm font-medium text-gray-700">Insurance Number</label>
                        <input id="insurance_number" name="insurance_number" type="text" value="<?= old('insurance_number') ?>" class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm">
                    </div>
                    <div class="md:col-span-2">
                        <label for="notes" class="mb-1 block text-sm font-medium text-gray-700">Notes</label>
                        <textarea id="notes" name="notes" rows="3" class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm"><?= old('notes') ?></textarea>
                    </div>
                </div>
            </details>

            <div class="flex flex-col-reverse gap-3 border-t border-gray-200 pt-4 sm:flex-row sm:justify-end">
                <a href="<?= !empty($return_to) ? base_url($return_to) : base_url('patient') ?>" class="inline-flex items-center justify-center rounded-md border border-gray-300 bg-white px-5 py-2 text-sm font-medium text-gray-700 hover:bg-gray-100">
                    Cancel
                </a>
                <button type="submit" class="inline-flex items-center justify-center rounded-md bg-blue-600 px-5 py-2 text-sm font-semibold text-white hover:bg-blue-700">
                    <i class="fas fa-user-plus mr-2"></i>Create Patient
                </button>
            </div>
        </form>
    </div>
</div>
<?= $this->endSection() ?>
