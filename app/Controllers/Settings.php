<?php

namespace App\Controllers;

use App\Models\ClinicModel;
use App\Models\ClinicSubscriptionModel;
use App\Models\PlanAuditModel;
use App\Models\PlanModel;
use App\Models\PlanRequestModel;
use App\Models\SettingsModel;
use App\Services\ControlPlaneAuditService;
use App\Services\PlanGuard;
use App\Services\SettingsService;

class Settings extends BaseController
{
    protected $settingsModel;
    protected $settingsService;

    public function __construct()
    {
        $this->settingsModel = new SettingsModel();
        $this->settingsService = new SettingsService();
    }
    public function index()
    {
        $clinicId = session()->get('active_clinic_id');
        if (!$clinicId) {
            return redirect()->to('/clinic/select')->with('error', 'Please select a clinic to view settings.');
        }

        $clinicModel = new ClinicModel();
        $clinic = $clinicModel->find($clinicId);
        $planGuard = new PlanGuard();

        $planModel = new PlanModel();
        $subscriptionModel = (new ClinicSubscriptionModel())->withoutTenantScope();
        $planRequestModel = new PlanRequestModel();

        $subscription = $subscriptionModel->getCurrentSubscription((int) $clinicId);
        $subscriptionPlan = null;
        if ($subscription && !empty($subscription['plan_id'])) {
            $subscriptionPlan = $planModel->find($subscription['plan_id']);
        }

        $latestRequest = $planRequestModel
            ->where('clinic_id', $clinicId)
            ->orderBy('created_at', 'DESC')
            ->first();

        $backups = $this->getBackups();
        
        // Debug: Log the backups data
        log_message('debug', 'Backups data: ' . json_encode($backups));
        
        // Check if demo mode is enabled
        $appConfig = config('App');
        
        $settings = $this->getSettings();
        $kioskPinSet = !empty($settings['kiosk_pin_hash'] ?? '');
        $kioskFeatureEnabled = $planGuard->isFeatureEnabled($clinicId, 'kiosk.enabled', false);
        $data = [
            'title' => 'System Settings',
            'settings' => $settings,
            'backups' => $backups,
            'demo' => $appConfig->demo,
            'plans' => $planModel->where('status', 'active')->orderBy('name', 'ASC')->findAll(),
            'subscription' => $subscription,
            'subscription_plan' => $subscriptionPlan,
            'latest_plan_request' => $latestRequest,
            'clinic_uuid' => $clinic['uuid'] ?? '',
            'kiosk_pin_set' => $kioskPinSet,
            'kiosk_feature_enabled' => $kioskFeatureEnabled,
        ];

        return $this->view('settings/index', $data);
    }

    public function requestPlan()
    {
        $clinicId = session()->get('active_clinic_id');
        if (!$clinicId) {
            return redirect()->back()->with('error', 'Clinic context required');
        }

        $planId = (int) $this->request->getPost('plan_id');
        $duration = $this->request->getPost('duration') ?? 'monthly';
        $reason = trim((string) $this->request->getPost('reason'));

        $planModel = new PlanModel();
        if (!$planId || !$planModel->find($planId)) {
            return redirect()->back()->with('error', 'Select a valid plan.');
        }

        $subscriptionModel = (new ClinicSubscriptionModel())->withoutTenantScope();
        $current = $subscriptionModel->getCurrentSubscription((int) $clinicId);

        $planRequestModel = new PlanRequestModel();
        $planRequestModel->insert([
            'clinic_id' => $clinicId,
            'current_plan_id' => $current['plan_id'] ?? null,
            'requested_plan_id' => $planId,
            'requested_duration' => in_array($duration, ['monthly', 'yearly', 'lifetime'], true) ? $duration : 'monthly',
            'status' => 'pending',
            'requested_by' => session()->get('user_id') ?? null,
            'decision_reason' => $reason !== '' ? $reason : null,
        ]);

        $auditModel = new PlanAuditModel();
        $auditModel->insert([
            'clinic_id' => $clinicId,
            'actor_user_id' => session()->get('user_id') ?? null,
            'action_key' => 'plan_request_submitted',
            'reason_code' => 'tenant_requested',
            'meta_json' => json_encode([
                'requested_plan_id' => $planId,
                'duration' => $duration,
            ]),
            'created_at' => date('Y-m-d H:i:s'),
        ]);

        $controlPlaneAudit = new ControlPlaneAuditService();
        $controlPlaneAudit->logEvent('plan_request_submitted', [
            'route' => '/settings/plan-request',
            'method' => 'POST',
            'metadata' => [
                'clinic_id' => $clinicId,
                'requested_plan_id' => $planId,
                'duration' => $duration,
            ],
        ]);

        return redirect()->to('/settings')->with('success', 'Plan request submitted.');
    }

    public function updateClinic()
    {
        $clinicId = session()->get('active_clinic_id');
        if (!$clinicId) {
            return redirect()->back()->with('error', 'Clinic context required');
        }

        $rules = [
            'clinic_name' => 'required|min_length[2]|max_length[100]',
            'clinic_address' => 'required|min_length[10]|max_length[500]',
            'clinic_phone' => 'required|min_length[10]|max_length[20]',
            'clinic_email' => 'required|valid_email|max_length[100]',
            'clinic_website' => 'permit_empty|valid_url|max_length[200]',
            'clinic_logo_path' => 'permit_empty|max_length[255]',
            'clinic_tagline' => 'permit_empty|max_length[100]',
            'clinic_logo' => 'permit_empty|is_image[clinic_logo]|mime_in[clinic_logo,image/png,image/jpg,image/jpeg,image/webp]|max_size[clinic_logo,512]|ext_in[clinic_logo,png,jpg,jpeg,webp]',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $settingsData = [
            'clinic_name' => $this->request->getPost('clinic_name'),
            'clinic_address' => $this->request->getPost('clinic_address'),
            'clinic_phone' => $this->request->getPost('clinic_phone'),
            'clinic_email' => $this->request->getPost('clinic_email'),
            'clinic_website' => $this->request->getPost('clinic_website'),
            'clinic_tagline' => $this->request->getPost('clinic_tagline'),
        ];

        // Handle Secure Logo Upload via StorageService
        $logoFile = $this->request->getFile('clinic_logo');
        $removeLogo = $this->request->getPost('clinic_logo_remove');
        $storageService = service('storage');

        if ($logoFile && $logoFile->isValid() && !$logoFile->hasMoved()) {
            // Delete old logo if exists in file_attachments
            $oldLogoPath = $this->settingsService->get('clinic_logo_path');
            if (!empty($oldLogoPath) && strpos($oldLogoPath, 'file/download/') === 0) {
                $oldAttachmentId = str_replace('file/download/', '', $oldLogoPath);
                $storageService->delete($oldAttachmentId, $clinicId);
            }

            // Store new logo
            $attachment = $storageService->store($logoFile, $clinicId, 'logo');
            $settingsData['clinic_logo_path'] = 'file/download/' . $attachment['id'];
            
        } elseif ($removeLogo) {
            $oldLogoPath = $this->settingsService->get('clinic_logo_path');
            if (!empty($oldLogoPath) && strpos($oldLogoPath, 'file/download/') === 0) {
                $oldAttachmentId = str_replace('file/download/', '', $oldLogoPath);
                $storageService->delete($oldAttachmentId, $clinicId);
            }
            $settingsData['clinic_logo_path'] = '';
        }

        log_message('info', "Saving clinic settings for ID {$clinicId}: " . json_encode($settingsData));
        
        if ($this->saveSettings($settingsData)) {
            $this->settingsService->reloadSettings();
            return redirect()->to('/settings')->with('success', 'Clinic information updated successfully!');
        } else {
            return redirect()->back()->withInput()->with('error', 'Failed to update clinic information.');
        }
    }

    public function updateSystem()
    {
        $rules = [
            'timezone' => 'required',
            'date_format' => 'required|in_list[Y-m-d,m/d/Y,d/m/Y]',
            'time_format' => 'required|in_list[24,12]',
            'currency' => 'required|in_list[ILS,USD,JOD,EUR,GBP]',
            'currency_position' => 'required|in_list[before,after]',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $settingsData = [
            'timezone' => $this->request->getPost('timezone'),
            'date_format' => $this->request->getPost('date_format'),
            'time_format' => $this->request->getPost('time_format'),
            'currency' => $this->request->getPost('currency'),
            'currency_position' => $this->request->getPost('currency_position'),
        ];

        log_message('info', 'Saving system settings: ' . json_encode($settingsData));
        
        if ($this->saveSettings($settingsData)) {
            $this->settingsService->reloadSettings();
            log_message('info', 'System settings saved successfully');
            return redirect()->to('/settings')->with('success', 'System preferences updated successfully!');
        } else {
            log_message('error', 'Failed to save system settings');
            return redirect()->back()->withInput()->with('error', 'Failed to update system preferences. Please try again.');
        }
    }

    public function updateWorkingHours()
    {
        $rules = [
            'appointment_duration' => 'required|integer|greater_than[0]|less_than[480]',
            'booking_chair_count' => 'required|integer|greater_than[0]|less_than_equal_to[20]',
            'booking_chair_names' => 'permit_empty|max_length[2000]',
            'working_hours_start' => 'required|regex_match[/^([01]?[0-9]|2[0-3]):[0-5][0-9]$/]',
            'working_hours_end' => 'required|regex_match[/^([01]?[0-9]|2[0-3]):[0-5][0-9]$/]',
            'working_days' => 'permit_empty',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $chairCount = (int) $this->request->getPost('booking_chair_count');
        [$chairNames, $chairError] = $this->normalizeChairNames((string) $this->request->getPost('booking_chair_names'), $chairCount);
        if ($chairError !== null) {
            return redirect()->back()->withInput()->with('error', $chairError);
        }

        $settingsData = [
            'appointment_duration' => $this->request->getPost('appointment_duration'),
            'booking_chair_count' => $this->request->getPost('booking_chair_count'),
            'booking_chair_names' => $chairNames,
            'working_hours_start' => $this->request->getPost('working_hours_start'),
            'working_hours_end' => $this->request->getPost('working_hours_end'),
            'working_days' => $this->request->getPost('working_days'),
        ];

        log_message('info', 'Saving working hours settings: ' . json_encode($settingsData));
        
        if ($this->saveSettings($settingsData)) {
            $this->settingsService->reloadSettings();
            log_message('info', 'Working hours settings saved successfully');
            return redirect()->to('/settings')->with('success', 'Working hours updated successfully!');
        } else {
            log_message('error', 'Failed to save working hours settings');
            return redirect()->back()->withInput()->with('error', 'Failed to update working hours. Please try again.');
        }
    }

    public function updateKiosk()
    {
        $clinicId = session()->get('active_clinic_id');
        if (!$clinicId) {
            return $this->kioskErrorResponse('Clinic context required.');
        }

        $planGuard = new PlanGuard();
        try {
            $planGuard->assertFeature($clinicId, 'kiosk.enabled', ['route' => 'settings/updateKiosk']);
        } catch (\Throwable $e) {
            return $this->kioskErrorResponse($e->getMessage());
        }

        $rules = [
            'kiosk_enabled' => 'permit_empty|in_list[0,1]',
            'kiosk_pin' => 'permit_empty|regex_match[/^[0-9]{4,8}$/]',
            'kiosk_pin_confirm' => 'permit_empty|regex_match[/^[0-9]{4,8}$/]',
            'kiosk_header' => 'permit_empty|max_length[60]',
            'kiosk_subheader' => 'permit_empty|max_length[120]',
            'kiosk_footer_note' => 'permit_empty|max_length[160]',
            'kiosk_default_language' => 'permit_empty|in_list[ar,en]',
        ];

        if (!$this->validate($rules)) {
            $message = implode(' ', $this->validator->getErrors());
            return $this->kioskErrorResponse($message ?: 'Invalid kiosk settings.');
        }

        $pin = trim((string) $this->request->getPost('kiosk_pin'));
        $pinConfirm = trim((string) $this->request->getPost('kiosk_pin_confirm'));
        if ($pin !== '' || $pinConfirm !== '') {
            if ($pin !== $pinConfirm) {
                return $this->kioskErrorResponse('Kiosk PIN confirmation does not match.');
            }
        }

        $current = $this->getSettings();
        $hasPin = !empty($current['kiosk_pin_hash'] ?? '');
        $enableRequested = $this->request->getPost('kiosk_enabled') ? true : false;

        if ($enableRequested && !$hasPin && $pin === '') {
            return $this->kioskErrorResponse('Set a kiosk PIN before enabling the kiosk.');
        }

        $settingsData = [
            'kiosk_enabled' => $enableRequested ? 1 : 0,
            'kiosk_header' => trim((string) $this->request->getPost('kiosk_header')) ?: 'Self Check-In',
            'kiosk_subheader' => trim((string) $this->request->getPost('kiosk_subheader')) ?: 'Confirm your appointment in seconds.',
            'kiosk_footer_note' => trim((string) $this->request->getPost('kiosk_footer_note')) ?: 'Need help? Please ask the reception desk.',
            'kiosk_default_language' => trim((string) $this->request->getPost('kiosk_default_language')) ?: 'ar',
        ];

        if (!$this->saveSettings($settingsData)) {
            return $this->kioskErrorResponse('Failed to update kiosk settings.');
        }

        if ($pin !== '') {
            $pinHash = password_hash($pin, PASSWORD_DEFAULT);
            if (!$this->settingsModel->setSetting('kiosk_pin_hash', $pinHash, 'string')) {
                return $this->kioskErrorResponse('Failed to update kiosk PIN.');
            }
        }

        $this->settingsService->reloadSettings();
        if ($this->request->isAJAX()) {
            return $this->response->setJSON([
                'success' => true,
                'message' => 'Kiosk settings updated successfully.',
            ]);
        }

        return redirect()->to('/settings')->with('success', 'Kiosk settings updated successfully.');
    }

    private function kioskErrorResponse(string $message)
    {
        if ($this->request->isAJAX()) {
            return $this->response->setStatusCode(400)->setJSON([
                'success' => false,
                'message' => $message,
            ]);
        }

        return redirect()->back()->withInput()->with('error', $message);
    }

    public function update()
    {
        // Check if settings table exists
        $db = \Config\Database::connect();
        $tables = $db->listTables();
        
        if (!in_array('settings', $tables)) {
            log_message('error', 'Settings table does not exist');
            return redirect()->back()->withInput()->with('error', 'Settings table not found. Please run the SQL script: create_settings_table.sql');
        }
        
        // Check if settings table is empty and initialize with defaults
        $settingsCount = $this->settingsModel->countAllResults();
        if ($settingsCount == 0) {
            $this->initializeDefaultSettings();
        }
        
        $rules = [
            'clinic_name' => 'required|min_length[2]|max_length[100]',
            'clinic_address' => 'required|min_length[10]|max_length[500]',
            'clinic_phone' => 'required|min_length[10]|max_length[20]',
            'clinic_email' => 'required|valid_email|max_length[100]',
            'clinic_website' => 'permit_empty|valid_url|max_length[200]',
            'timezone' => 'required',
            'date_format' => 'required|in_list[Y-m-d,m/d/Y,d/m/Y]',
            'time_format' => 'required|in_list[24,12]',
            'currency' => 'required',
            'appointment_duration' => 'required|integer|greater_than[0]|less_than[480]',
            'booking_chair_count' => 'required|integer|greater_than[0]|less_than_equal_to[20]',
            'booking_chair_names' => 'permit_empty|max_length[2000]',
            'working_hours_start' => 'required|regex_match[/^([01]?[0-9]|2[0-3]):[0-5][0-9]$/]',
            'working_hours_end' => 'required|regex_match[/^([01]?[0-9]|2[0-3]):[0-5][0-9]$/]',
            'working_days' => 'permit_empty',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $chairCount = (int) $this->request->getPost('booking_chair_count');
        [$chairNames, $chairError] = $this->normalizeChairNames((string) $this->request->getPost('booking_chair_names'), $chairCount);
        if ($chairError !== null) {
            return redirect()->back()->withInput()->with('error', $chairError);
        }

        $settingsData = [
            'clinic_name' => $this->request->getPost('clinic_name'),
            'clinic_address' => $this->request->getPost('clinic_address'),
            'clinic_phone' => $this->request->getPost('clinic_phone'),
            'clinic_email' => $this->request->getPost('clinic_email'),
            'clinic_website' => $this->request->getPost('clinic_website'),
            'timezone' => $this->request->getPost('timezone'),
            'date_format' => $this->request->getPost('date_format'),
            'time_format' => $this->request->getPost('time_format'),
            'currency' => $this->request->getPost('currency'),
            'appointment_duration' => $this->request->getPost('appointment_duration'),
            'booking_chair_count' => $this->request->getPost('booking_chair_count'),
            'booking_chair_names' => $chairNames,
            'working_hours_start' => $this->request->getPost('working_hours_start'),
            'working_hours_end' => $this->request->getPost('working_hours_end'),
            'working_days' => $this->request->getPost('working_days'),
        ];

        // Save settings to database
        log_message('info', 'Saving settings: ' . json_encode($settingsData));
        
        if ($this->saveSettings($settingsData)) {
            // Reload settings service to reflect changes
            $this->settingsService->reloadSettings();
            log_message('info', 'Settings saved successfully');
            return redirect()->to('/settings')->with('success', 'Settings updated successfully!');
        } else {
            log_message('error', 'Failed to save settings');
            return redirect()->back()->withInput()->with('error', 'Failed to update settings. Please try again.');
        }
    }

    // Old backup method - no longer needed with tabbed interface
    /*
    public function backup()
    {
        $data = [
            'title' => 'Backup & Restore',
            'backups' => $this->getBackups(),
        ];

        return $this->view('settings/backup', $data);
    }
    */

    public function createBackup()
    {
        // Check if demo mode is enabled
        $appConfig = config('App');
        if ($appConfig->demo) {
            return redirect()->to('/settings')->with('error', 'Database backup is disabled in demo mode.');
        }
        
        $backupName = 'backup_' . date('Y-m-d_H-i-s') . '.sql';
        
        if ($this->performBackup($backupName)) {
            return redirect()->to('/settings')->with('success', 'Backup created successfully!');
        } else {
            return redirect()->to('/settings')->with('error', 'Failed to create backup. Please check the logs for details.');
        }
    }

    public function restore()
    {
        // Check if demo mode is enabled
        $appConfig = config('App');
        if ($appConfig->demo) {
            return redirect()->to('/settings')->with('error', 'Database restore is disabled in demo mode.');
        }
        
        $backupFile = $this->request->getPost('backup_file');
        
        if (!$backupFile) {
            return redirect()->to('/settings')->with('error', 'Please select a backup file to restore.');
        }

        // In a real application, you would restore from the selected backup
        // For now, we'll simulate success
        if ($this->performRestore($backupFile)) {
            return redirect()->to('/settings')->with('success', 'Database restored successfully!');
        } else {
            return redirect()->to('/settings')->with('error', 'Failed to restore database. Please try again.');
        }
    }
    
    public function downloadBackup($filename)
    {
        // Check if demo mode is enabled
        $appConfig = config('App');
        if ($appConfig->demo) {
            return redirect()->to('/settings')->with('error', 'Database backup download is disabled in demo mode.');
        }
        
        $backupDir = WRITEPATH . 'backups/';
        $filePath = $backupDir . $filename;
        
        if (!file_exists($filePath)) {
            return redirect()->to('/settings')->with('error', 'Backup file not found.');
        }
        
        return $this->response->download($filePath, null);
    }

    public function security()
    {
        $data = [
            'title' => 'Security Settings',
            'security_settings' => $this->getSecuritySettings(),
        ];

        return $this->view('settings/security', $data);
    }

    public function updateSecurity()
    {
        $rules = [
            'password_min_length' => 'required|integer|greater_than_equal_to[8]',
            'password_require_special' => 'required|in_list[0,1]',
            'session_timeout' => 'required|integer|greater_than[0]|less_than[1440]',
            'max_login_attempts' => 'required|integer|greater_than[0]|less_than[10]',
            'lockout_duration' => 'required|integer|greater_than[0]|less_than[60]',
            'two_factor_auth' => 'required|in_list[0,1]',
            'ip_whitelist' => 'permit_empty|max_length[1000]',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $securityData = [
            'password_min_length' => $this->request->getPost('password_min_length'),
            'password_require_special' => $this->request->getPost('password_require_special'),
            'session_timeout' => $this->request->getPost('session_timeout'),
            'max_login_attempts' => $this->request->getPost('max_login_attempts'),
            'lockout_duration' => $this->request->getPost('lockout_duration'),
            'two_factor_auth' => $this->request->getPost('two_factor_auth'),
            'ip_whitelist' => $this->request->getPost('ip_whitelist'),
            'updated_at' => date('Y-m-d H:i:s'),
        ];

        if ($this->saveSecuritySettings($securityData)) {
            return redirect()->to('/settings/security')->with('success', 'Security settings updated successfully!');
        } else {
            return redirect()->back()->withInput()->with('error', 'Failed to update security settings. Please try again.');
        }
    }

    public function notifications()
    {
        $data = [
            'title' => 'Notification Settings',
            'notification_settings' => $this->getNotificationSettings(),
        ];

        return $this->view('settings/notifications', $data);
    }

    public function updateNotifications()
    {
        // Define all possible keys
        $allKeys = [
            'email_notifications', 
            'sms_notifications', 
            'appointment_created', 
            'low_stock_alerts'
        ];

        $preferences = [];
        $postData = $this->request->getPost('preferences') ?? [];

        foreach ($allKeys as $key) {
            // Checkbox logic: if present in POST, it's '1', else '0'
            // But if 'preferences' array is used in form like name="preferences[key]", then unchecked keys are missing from the array entirely.
            $preferences[$key] = isset($postData[$key]) ? 1 : 0;
        }

        if ($this->saveNotificationSettings($preferences)) {
            return redirect()->to('/notification-settings')->with('success', 'Notification settings updated successfully!');
        } else {
            return redirect()->back()->withInput()->with('error', 'Failed to update notification settings.');
        }
    }

    /**
     * Update global export retention policy (Superadmin only)
     */
    public function updateRetention()
    {
        // Strict Superadmin Check via Global Mode
        if (!session()->get('global_mode')) {
            return redirect()->to('/dashboard')->with('error', 'Unauthorized: Control Plane access required.');
        }

        $rules = [
            'retention_mode'   => 'required|in_list[latest,keep_last_n,keep_x_days]',
            'retention_last_n' => 'permit_empty|is_natural_no_zero',
            'retention_days'   => 'permit_empty|is_natural_no_zero',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $settings = [
            'retention_mode'   => $this->request->getPost('retention_mode'),
            'retention_last_n' => $this->request->getPost('retention_last_n') ?: 5,
            'retention_days'   => $this->request->getPost('retention_days') ?: 30,
        ];

        foreach ($settings as $key => $value) {
            $this->settingsModel->setGlobalSetting($key, $value);
        }

        return redirect()->to('/settings')->with('success', 'Export retention policy updated successfully!');
    }

    /**
     * Physically prune export files from disk (Superadmin only)
     */
    public function pruneExports()
    {
        // AJAX/Strict Superadmin Check
        if (!session()->get('global_mode')) {
            return $this->response->setStatusCode(403)->setJSON(['success' => false, 'message' => 'Unauthorized']);
        }

        try {
            $retentionService = new \App\Services\RetentionService();
            $count = $retentionService->physicalCleanup();

            return $this->response->setJSON([
                'success' => true,
                'count'   => $count
            ]);
        } catch (\Exception $e) {
            return $this->response->setJSON([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
    }

    /**
     * Notification Governance: Channel Registry (Mixed Access)
     */
    public function channels()
    {
        $channelModel = new \App\Models\ClinicNotificationChannelModel();
        $isGlobal = session()->get('global_mode');
        $data = [
            'title' => 'Notification Channels',
            'is_global' => $isGlobal,
            'channels' => []
        ];

        if ($isGlobal) {
            // Superadmin view: List all clinics and their channels
            $clinicModel = new \App\Models\ClinicModel();
            $clinics = $clinicModel->select('id, name')->findAll();
            
            foreach ($clinics as &$clinic) {
                $clinic['channels'] = $channelModel->where('clinic_id', $clinic['id'])->findAll();
                // If no channels exist for clinic, we might want to show placeholders?
                // For now, only show what's in DB or assume 3 types.
                $types = ['email', 'sms', 'whatsapp'];
                $existingTypes = array_column($clinic['channels'], 'channel_type');
                foreach ($types as $type) {
                    if (!in_array($type, $existingTypes)) {
                        $clinic['channels'][] = [
                            'clinic_id' => $clinic['id'],
                            'channel_type' => $type,
                            'enabled_by_superadmin' => 0,
                            'configured_by_clinic' => 0,
                            'validated' => 0,
                            'is_virtual' => true // Marker for UI
                        ];
                    }
                }
            }
            $data['clinics'] = $clinics;
        } else {
            // Clinic Admin view: My channels
            $clinicId = session()->get('active_clinic_id');
            if (!$clinicId) {
                return redirect()->to('/dashboard');
            }
            
            $channels = $channelModel->where('clinic_id', $clinicId)->findAll();
            // Fill gaps
            $types = ['email', 'sms', 'whatsapp'];
            $existingTypes = array_column($channels, 'channel_type');
            foreach ($types as $type) {
                if (!in_array($type, $existingTypes)) {
                    $channels[] = [
                        'clinic_id' => $clinicId,
                        'channel_type' => $type,
                        'enabled_by_superadmin' => 0, // Default closed
                        'configured_by_clinic' => 0,
                        'validated' => 0,
                        'is_virtual' => true
                    ];
                }
            }
            $data['channels'] = $channels;
        }

        return $this->view('settings/channels', $data);
    }

    /**
     * Superadmin: Toggle Channel Enablement
     */
    public function updateChannelStatus()
    {
        if (!session()->get('global_mode')) {
            return $this->response->setStatusCode(403)->setJSON(['success' => false, 'message' => 'Unauthorized']);
        }

        $clinicId = $this->request->getPost('clinic_id');
        $channelType = $this->request->getPost('channel_type');
        $enabled = $this->request->getPost('enabled') == '1' ? 1 : 0;

        $channelModel = new \App\Models\ClinicNotificationChannelModel();
        
        // Check if exists
        $existing = $channelModel->where('clinic_id', $clinicId)
                                 ->where('channel_type', $channelType)
                                 ->first();

        if ($existing) {
            $channelModel->update($existing['id'], ['enabled_by_superadmin' => $enabled]);
        } else {
            // Create new
            $channelModel->insert([
                'clinic_id' => $clinicId,
                'channel_type' => $channelType,
                'enabled_by_superadmin' => $enabled,
                'configured_by_clinic' => 0,
                'validated' => 0
            ]);
        }

        return $this->response->setJSON(['success' => true]);
    }

    /**
     * Clinic Admin: Update Config & Validation
     */
    public function updateChannelConfig()
    {
        $clinicId = session()->get('active_clinic_id');
        if (!$clinicId) {
            return $this->response->setStatusCode(403)->setJSON(['success' => false, 'message' => 'No context']);
        }

        $channelType = $this->request->getPost('channel_type');
        $configJson = $this->request->getPost('config_json');
        $action = $this->request->getPost('action'); // 'save_config' or 'validate'

        $channelModel = new \App\Models\ClinicNotificationChannelModel();
        
        // Ensure enabled by superadmin first?
        // Logic rule 2 says: If enabled_by_superadmin != 1 → blocked.
        // But can they configure it? Yes, let's allow config even if disabled, just won't send.
        
        if ($action === 'save_config') {
            $config = json_decode($configJson, true);
            if (!$config && $configJson) {
                return $this->response->setJSON(['success' => false, 'message' => 'Invalid JSON']);
            }
            
            $channelModel->setConfig($clinicId, $channelType, $config ?? []);
            return $this->response->setJSON(['success' => true, 'message' => 'Configuration saved']);
        } elseif ($action === 'validate') {
            // Mark validated=1 (Manual override for now)
            $existing = $channelModel->where('clinic_id', $clinicId)
                                     ->where('channel_type', $channelType)
                                     ->first();
            
            if ($existing) {
                $channelModel->update($existing['id'], ['validated' => 1]);
                return $this->response->setJSON(['success' => true, 'message' => 'Channel marked as validated']);
            }
            return $this->response->setJSON(['success' => false, 'message' => 'Channel not found or not configured']);
        }

        return $this->response->setJSON(['success' => false, 'message' => 'Invalid action']);
    }

    private function getSettings()
    {
        try {
            $clinicId = session()->get('active_clinic_id');
            if (!$clinicId) {
                return $this->getDefaultSettings();
            }

            $settings = $this->settingsModel->getAllSettings();
            
            // Set defaults if settings don't exist
            $defaults = [
                'clinic_name' => 'DentalCare Clinic',
                'clinic_address' => '123 Dental Street, Medical District, City 12345',
                'clinic_phone' => '+1 (555) 123-4567',
                'clinic_email' => 'info@dentalclinic.com',
                'clinic_website' => 'https://dentalclinic.com',
                'clinic_logo_path' => '',
                'clinic_tagline' => 'Professional Suite',
                'timezone' => 'America/New_York',
                'date_format' => 'Y-m-d',
                'time_format' => '12',
                'currency' => 'USD',
                'currency_position' => 'before',
                'appointment_duration' => 30,
                'booking_chair_count' => 1,
                'booking_chair_names' => ['Chair 1'],
                'working_hours_start' => '09:00',
                'working_hours_end' => '17:00',
                'working_days' => ['monday', 'tuesday', 'wednesday', 'thursday', 'friday'],
                'kiosk_enabled' => 0,
                'kiosk_pin_hash' => '',
                'kiosk_header' => 'Self Check-In',
                'kiosk_subheader' => 'Confirm your appointment in seconds.',
                'kiosk_footer_note' => 'Need help? Please ask the reception desk.',
                'kiosk_default_language' => 'ar',
            ];
            
            return array_merge($defaults, $settings);
        } catch (\Exception $e) {
            log_message('error', 'Error getting settings: ' . $e->getMessage());
            return $this->getDefaultSettings();
        }
    }

    private function getDefaultSettings()
    {
        return [
            'clinic_name' => 'DentalCare Clinic',
            'clinic_address' => '123 Dental Street, Medical District, City 12345',
            'clinic_phone' => '+1 (555) 123-4567',
            'clinic_email' => 'info@dentalclinic.com',
            'clinic_website' => 'https://dentalclinic.com',
            'clinic_logo_path' => '',
            'clinic_tagline' => 'Professional Suite',
            'timezone' => 'America/New_York',
            'date_format' => 'Y-m-d',
            'time_format' => '12',
            'currency' => 'USD',
            'currency_position' => 'before',
            'appointment_duration' => 30,
            'booking_chair_count' => 1,
            'booking_chair_names' => ['Chair 1'],
            'working_hours_start' => '09:00',
            'working_hours_end' => '17:00',
            'working_days' => ['monday', 'tuesday', 'wednesday', 'thursday', 'friday'],
            'kiosk_enabled' => 0,
            'kiosk_pin_hash' => '',
            'kiosk_header' => 'Self Check-In',
            'kiosk_subheader' => 'Confirm your appointment in seconds.',
            'kiosk_footer_note' => 'Need help? Please ask the reception desk.',
            'kiosk_default_language' => 'ar',
        ];
    }

    private function getSecuritySettings()
    {
        return [
            'password_min_length' => 8,
            'password_require_special' => 1,
            'session_timeout' => 30,
            'max_login_attempts' => 5,
            'lockout_duration' => 15,
            'two_factor_auth' => 0,
            'ip_whitelist' => '',
        ];
    }

    private function getNotificationSettings()
    {
        $userId = session()->get('user_id');
        if (!$userId) {
             $ionAuth = new \App\Libraries\IonAuth();
             if ($ionAuth->loggedIn()) {
                 $userId = $ionAuth->user()->row()->id;
             }
        }
        
        $clinicId = session()->get('active_clinic_id');
        
        if ($userId && $clinicId) {
            $clinicUserModel = new \App\Models\ClinicUserModel();
            $membership = $clinicUserModel->where('user_id', $userId)
                                          ->where('clinic_id', $clinicId)
                                          ->first();
            if ($membership && !empty($membership['preferences'])) {
                $prefs = json_decode($membership['preferences'], true);
                // Ensure defaults for missing keys
                $defaults = [
                    'email_notifications' => 1,
                    'sms_notifications' => 0,
                    'appointment_created' => 1,
                    'low_stock_alerts' => 1,
                ];
                return array_merge($defaults, $prefs);
            }
        }

        // Default preferences
        return [
            'email_notifications' => 1,
            'sms_notifications' => 0,
            'appointment_created' => 1,
            'low_stock_alerts' => 1,
        ];
    }

    private function getBackups()
    {
        $backups = [];
        $backupDir = WRITEPATH . 'backups/';
        
        // Create backup directory if it doesn't exist
        if (!is_dir($backupDir)) {
            mkdir($backupDir, 0755, true);
        }
        
        // Scan for backup files
        if (is_dir($backupDir)) {
            $files = glob($backupDir . '*.sql');
            foreach ($files as $file) {
                $backups[] = [
                    'name' => basename($file),
                    'size' => $this->formatFileSize(filesize($file)),
                    'created_at' => date('Y-m-d H:i:s', filemtime($file)),
                    'path' => $file
                ];
            }
            
            // Sort by creation time (newest first)
            usort($backups, function($a, $b) {
                return strtotime($b['created_at']) - strtotime($a['created_at']);
            });
        }
        
        return $backups;
    }

    private function initializeDefaultSettings()
    {
        $defaultSettings = [
            'clinic_name' => 'DentalCare Clinic',
            'clinic_address' => '123 Dental Street, Medical District, City 12345',
            'clinic_phone' => '+1 (555) 123-4567',
            'clinic_email' => 'info@dentalclinic.com',
            'clinic_website' => 'https://dentalclinic.com',
            'clinic_logo_path' => '',
            'clinic_tagline' => 'Professional Suite',
            'timezone' => 'America/New_York',
            'date_format' => 'Y-m-d',
            'time_format' => '12',
            'currency' => 'USD',
            'currency_position' => 'before',
            'appointment_duration' => 30,
            'booking_chair_count' => 1,
            'booking_chair_names' => ['Chair 1'],
            'working_hours_start' => '09:00',
            'working_hours_end' => '17:00',
            'working_days' => ['monday', 'tuesday', 'wednesday', 'thursday', 'friday'],
            'kiosk_enabled' => 0,
            'kiosk_pin_hash' => '',
            'kiosk_header' => 'Self Check-In',
            'kiosk_subheader' => 'Confirm your appointment in seconds.',
            'kiosk_footer_note' => 'Need help? Please ask the reception desk.',
            'kiosk_default_language' => 'ar',
        ];
        
        log_message('info', 'Initializing default settings');
        $this->saveSettings($defaultSettings);
    }

    private function saveSettings($data)
    {
        try {
            return $this->settingsModel->setMultipleSettings($data);
        } catch (\Exception $e) {
            log_message('error', 'Error saving settings: ' . $e->getMessage());
            return false;
        }
    }

    private function normalizeChairNames(string $rawNames, int $chairCount): array
    {
        if ($chairCount < 1) {
            return [[], 'Number of chairs must be at least 1.'];
        }

        $rows = preg_split('/\r\n|\r|\n/', $rawNames);
        if (!is_array($rows)) {
            $rows = [];
        }

        $names = [];
        foreach ($rows as $row) {
            $clean = trim((string) $row);
            if ($clean !== '') {
                $names[] = $clean;
            }
        }

        $unique = [];
        foreach ($names as $name) {
            $key = mb_strtolower($name, 'UTF-8');
            if (isset($unique[$key])) {
                return [[], 'Chair/room names must be unique.'];
            }
            $unique[$key] = true;
        }

        for ($i = count($names); $i < $chairCount; $i++) {
            $names[] = 'Chair ' . ($i + 1);
        }

        return [array_slice($names, 0, $chairCount), null];
    }

    private function saveSecuritySettings($data)
    {
        // In a real application, you would save to database
        return true;
    }

    private function saveNotificationSettings($data)
    {
        $userId = session()->get('user_id'); 
        if (!$userId) {
             // Use the correct IonAuth instance
             $ionAuth = new \App\Libraries\IonAuth();
             if ($ionAuth->loggedIn()) {
                 $userId = $ionAuth->user()->row()->id;
             }
        }
        $clinicId = session()->get('active_clinic_id');

        if (!$userId || !$clinicId) {
            log_message('error', 'Cannot save preferences: Missing Context (User: ' . $userId . ', Clinic: ' . $clinicId . ')');
            return false;
        }

        $clinicUserModel = new \App\Models\ClinicUserModel();
        
        // Find membership row
        $membership = $clinicUserModel->where('user_id', $userId)
                                      ->where('clinic_id', $clinicId)
                                      ->first();

        if (!$membership) {
            log_message('error', 'Cannot save preferences: Membership not found');
            return false;
        }

        // Update preferences column
        try {
            $clinicUserModel->update($membership['id'], [
                'preferences' => json_encode($data)
            ]);
            return true;
        } catch (\Exception $e) {
            log_message('error', 'DB Error saving preferences: ' . $e->getMessage());
            return false;
        }
    }

    private function performBackup($filename)
    {
        try {
            $backupDir = WRITEPATH . 'backups/';
            
            // Create backup directory if it doesn't exist
            if (!is_dir($backupDir)) {
                mkdir($backupDir, 0755, true);
            }
            
            $backupPath = $backupDir . $filename;
            
            // Use SettingsService to generate backup content
            $sqlContent = $this->settingsService->generateDatabaseBackup();
            
            // Check if SQL content was generated
            if (empty($sqlContent) || strpos($sqlContent, 'Error') === 0) {
                log_message('error', 'Failed to generate backup content: ' . $sqlContent);
                return false;
            }
            
            // Write backup file
            $bytesWritten = file_put_contents($backupPath, $sqlContent);
            if ($bytesWritten !== false) {
                log_message('info', 'Backup created successfully: ' . $filename . ' (' . $bytesWritten . ' bytes)');
        return true;
            } else {
                log_message('error', 'Failed to write backup file: ' . $filename . ' to path: ' . $backupPath);
                return false;
            }
        } catch (\Exception $e) {
            log_message('error', 'Backup creation failed: ' . $e->getMessage());
            return false;
        }
    }

    private function performRestore($filename)
    {
        // In a real application, you would restore from the selected backup
        return true;
    }
    
    private function formatFileSize($bytes)
    {
        if ($bytes >= 1073741824) {
            return number_format($bytes / 1073741824, 2) . ' GB';
        } elseif ($bytes >= 1048576) {
            return number_format($bytes / 1048576, 2) . ' MB';
        } elseif ($bytes >= 1024) {
            return number_format($bytes / 1024, 2) . ' KB';
        } else {
            return $bytes . ' bytes';
        }
    }
}
