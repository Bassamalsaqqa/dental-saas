<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="container mx-auto px-4 py-8">
    <div class="max-w-2xl mx-auto bg-white rounded-xl shadow-md overflow-hidden border border-gray-200">
        <div class="p-6 border-b border-gray-100 bg-gray-50">
            <h2 class="text-xl font-bold text-gray-800">Onboard New Clinic</h2>
            <p class="text-sm text-gray-500 mt-1">Create a clinic, admin user, and subscription in one step.</p>
        </div>
        
        <form action="<?= base_url('controlplane/onboarding/clinic/create') ?>" method="POST" class="p-6 space-y-6">
            <?= csrf_field() ?>

            <!-- Clinic Details -->
            <div>
                <h3 class="text-sm font-semibold text-gray-900 uppercase tracking-wider mb-3">Clinic Details</h3>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Clinic Name</label>
                    <input type="text" name="clinic_name" value="<?= old('clinic_name') ?>" required
                           class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500 shadow-sm"
                           placeholder="e.g. Springfield Dental">
                </div>
            </div>

            <hr class="border-gray-100">

            <!-- Admin User -->
            <div>
                <h3 class="text-sm font-semibold text-gray-900 uppercase tracking-wider mb-3">Initial Admin User</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Admin Name</label>
                        <input type="text" name="admin_name" value="<?= old('admin_name') ?>" required
                               class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500 shadow-sm"
                               placeholder="e.g. John Doe">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Admin Email</label>
                        <input type="email" name="admin_email" value="<?= old('admin_email') ?>" required
                               class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500 shadow-sm"
                               placeholder="admin@clinic.com">
                    </div>
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Password</label>
                        <input type="password" name="admin_password" required minlength="8"
                               class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500 shadow-sm"
                               placeholder="********">
                    </div>
                </div>
            </div>

            <hr class="border-gray-100">

            <!-- Plan -->
            <div>
                <h3 class="text-sm font-semibold text-gray-900 uppercase tracking-wider mb-3">Subscription</h3>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Select Plan</label>
                    <select name="plan_id" required class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500 shadow-sm">
                        <option value="">-- Choose a Plan --</option>
                        <?php foreach ($plans as $plan): ?>
                            <option value="<?= esc($plan['id']) ?>" <?= old('plan_id') == $plan['id'] ? 'selected' : '' ?>>
                                <?= esc($plan['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <div class="flex justify-end pt-4">
                <a href="<?= base_url('settings') ?>" class="px-4 py-2 text-gray-700 hover:bg-gray-100 rounded-lg mr-2">Cancel</a>
                <button type="submit" class="px-6 py-2 bg-blue-600 text-white font-medium rounded-lg hover:bg-blue-700 shadow-sm">
                    Create Clinic
                </button>
            </div>
        </form>
    </div>
</div>
<?= $this->endSection() ?>
