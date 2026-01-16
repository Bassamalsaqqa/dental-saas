<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="container mx-auto px-4 py-8">
    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-3xl font-bold text-gray-900">Notification Preferences</h1>
            <p class="text-gray-600 mt-1">Manage your personal notification settings for this clinic.</p>
        </div>
        <a href="<?= base_url('settings') ?>" class="text-blue-600 hover:text-blue-800">Back to Settings</a>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 max-w-2xl">
        <form action="<?= base_url('notifications/update') ?>" method="post">
            <?= csrf_field() ?>

            <!-- System Notifications -->
            <div class="mb-8">
                <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                    <i class="fas fa-bell text-blue-500 mr-2"></i> System Alerts
                </h3>
                <div class="space-y-4">
                    <label class="flex items-center justify-between p-3 border border-gray-100 rounded-lg hover:bg-gray-50 transition-colors cursor-pointer">
                        <span class="text-gray-700 font-medium">Email Notifications</span>
                        <div class="relative inline-block w-12 mr-2 align-middle select-none transition duration-200 ease-in">
                            <input type="checkbox" name="preferences[email_notifications]" id="email_notifications" class="toggle-checkbox absolute block w-6 h-6 rounded-full bg-white border-4 appearance-none cursor-pointer transition-transform duration-200 ease-in-out checked:translate-x-full checked:border-blue-600" value="1" <?= ($notification_settings['email_notifications'] ?? true) ? 'checked' : '' ?>/>
                            <label for="email_notifications" class="toggle-label block overflow-hidden h-6 rounded-full bg-gray-300 cursor-pointer checked:bg-blue-600"></label>
                        </div>
                    </label>

                    <label class="flex items-center justify-between p-3 border border-gray-100 rounded-lg hover:bg-gray-50 transition-colors cursor-pointer">
                        <span class="text-gray-700 font-medium">SMS Notifications</span>
                        <div class="relative inline-block w-12 mr-2 align-middle select-none transition duration-200 ease-in">
                            <input type="checkbox" name="preferences[sms_notifications]" id="sms_notifications" class="toggle-checkbox absolute block w-6 h-6 rounded-full bg-white border-4 appearance-none cursor-pointer transition-transform duration-200 ease-in-out checked:translate-x-full checked:border-blue-600" value="1" <?= ($notification_settings['sms_notifications'] ?? false) ? 'checked' : '' ?>/>
                            <label for="sms_notifications" class="toggle-label block overflow-hidden h-6 rounded-full bg-gray-300 cursor-pointer checked:bg-blue-600"></label>
                        </div>
                    </label>
                </div>
            </div>

            <!-- Operational Alerts -->
            <div class="mb-8">
                <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                    <i class="fas fa-tasks text-green-500 mr-2"></i> Operational Alerts
                </h3>
                <div class="space-y-4">
                     <label class="flex items-center justify-between p-3 border border-gray-100 rounded-lg hover:bg-gray-50 transition-colors cursor-pointer">
                        <span class="text-gray-700 font-medium">New Appointment Created</span>
                        <div class="relative inline-block w-12 mr-2 align-middle select-none transition duration-200 ease-in">
                            <input type="checkbox" name="preferences[appointment_created]" id="appointment_created" class="toggle-checkbox absolute block w-6 h-6 rounded-full bg-white border-4 appearance-none cursor-pointer transition-transform duration-200 ease-in-out checked:translate-x-full checked:border-blue-600" value="1" <?= ($notification_settings['appointment_created'] ?? true) ? 'checked' : '' ?>/>
                            <label for="appointment_created" class="toggle-label block overflow-hidden h-6 rounded-full bg-gray-300 cursor-pointer checked:bg-blue-600"></label>
                        </div>
                    </label>

                    <label class="flex items-center justify-between p-3 border border-gray-100 rounded-lg hover:bg-gray-50 transition-colors cursor-pointer">
                        <span class="text-gray-700 font-medium">Low Stock Alerts</span>
                        <div class="relative inline-block w-12 mr-2 align-middle select-none transition duration-200 ease-in">
                            <input type="checkbox" name="preferences[low_stock_alerts]" id="low_stock_alerts" class="toggle-checkbox absolute block w-6 h-6 rounded-full bg-white border-4 appearance-none cursor-pointer transition-transform duration-200 ease-in-out checked:translate-x-full checked:border-blue-600" value="1" <?= ($notification_settings['low_stock_alerts'] ?? true) ? 'checked' : '' ?>/>
                            <label for="low_stock_alerts" class="toggle-label block overflow-hidden h-6 rounded-full bg-gray-300 cursor-pointer checked:bg-blue-600"></label>
                        </div>
                    </label>
                </div>
            </div>

            <div class="flex justify-end pt-6 border-t border-gray-100">
                <button type="submit" class="px-6 py-2 bg-blue-600 text-white font-semibold rounded-lg hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition-all">
                    Save Preferences
                </button>
            </div>
        </form>
    </div>
</div>

<style>
/* Custom Toggle Styles */
.toggle-checkbox:checked {
    right: 0;
    border-color: #2563EB;
}
.toggle-checkbox:checked + .toggle-label {
    background-color: #2563EB;
}
.toggle-label {
    width: 3rem;
    height: 1.5rem;
    background-color: #E5E7EB;
}
.toggle-checkbox {
    top: 0.25rem;
    left: 0.25rem;
    width: 1rem;
    height: 1rem;
    transition: all 0.2s;
}
.toggle-checkbox:checked {
    transform: translateX(100%);
    border-color: white;
}
</style>
<?= $this->endSection() ?>
