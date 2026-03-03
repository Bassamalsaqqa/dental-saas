<?php

namespace App\Controllers;

use App\Models\ClinicModel;
use App\Models\ClinicSubscriptionModel;
use App\Models\PlatformSecretModel;
use App\Models\PlanAuditModel;
use App\Models\PlanModel;
use App\Models\PlanRequestModel;
use App\Models\SettingsModel;
use App\Services\SettingsService;
use App\Services\PlanGuard;

class Settings extends BaseController
{
    protected $settingsModel;
    protected $settingsService;

    public function __construct()
    {
        $this->settingsModel = new SettingsModel();
        $this->settingsService = service('settings');
    }

    /**
     * Deep-link Aliases for Consolidated Settings (P5-09a-Tabs)
     */
    public function email() { return $this->index('email'); }
    public function sms() { return $this->index('sms'); }
    public function whatsapp() { return $this->index('whatsapp'); }
    public function channels() { return $this->index('channels'); }
    public function notifications() { return $this->index('notifications'); }
    public function kiosk() { return $this->index('kiosk'); }

    /**
     * Main Settings Hub (Consolidated)
     */
    public function index($activeTab = 'clinic')
    {
        $clinicId = session()->get('active_clinic_id');
        if (!$clinicId) return redirect()->to('/dashboard');

        // Plan Guard for feature checks
        $planGuard = new PlanGuard();

        $clinicModel = new ClinicModel();
        $clinic = $clinicModel->find($clinicId);

        $planModel = new PlanModel();
        $subscriptionModel = new ClinicSubscriptionModel();
        $subscription = $subscriptionModel->where('clinic_id', $clinicId)->where('status', 'active')->first();
        $subscriptionPlan = $subscription ? $planModel->find($subscription['plan_id']) : null;

        $requestModel = new PlanRequestModel();
        $latestRequest = $requestModel->where('clinic_id', $clinicId)->orderBy('created_at', 'DESC')->first();

        $backups = $this->getBackups();
        $appConfig = config('App');

        $settings = $this->getSettings();
        $maxBookingChairCount = $this->getMaxBookingChairs();
        $kioskPinSet = !empty($settings['kiosk_pin_hash'] ?? '');
        $kioskFeatureEnabled = $planGuard->isFeatureEnabled($clinicId, 'kiosk.enabled', false);

        // Fetch Notification Channels for Consolidated Settings (P5-09a-Tabs)
        $channelModel = new \App\Models\ClinicNotificationChannelModel();
        $allChannels = $channelModel->where('clinic_id', $clinicId)->findAll();
        
        // Fill gaps for UI consistency
        $types = ['email', 'sms', 'whatsapp'];
        $existingTypes = array_column($allChannels, 'channel_type');
        foreach ($types as $type) {
            if (!in_array($type, $existingTypes)) {
                $allChannels[] = [
                    'clinic_id' => $clinicId,
                    'channel_type' => $type,
                    'enabled_by_superadmin' => 0,
                    'configured_by_clinic' => 0,
                    'validated' => 0,
                    'is_virtual' => true
                ];
            }
        }
        
        $emailConfig = $channelModel->getConfig($clinicId, 'email') ?? [];
        unset($emailConfig['smtp_pass']); // Write-only

        $smsConfig = $channelModel->getConfig($clinicId, 'sms') ?? [];
        
        $waConfig = $channelModel->getConfig($clinicId, 'whatsapp') ?? [];
        $waConfig = $this->normalizeWhatsAppChannelConfig($waConfig);
        $waFeatureEnabled = $planGuard->isFeatureEnabled($clinicId, 'notifications.whatsapp.enabled', false);

        // Support for Superadmin Registry View
        $clinics = [];
        $isGlobal = (bool) session()->get('global_mode');
        if ($isGlobal) {
            $clinics = $clinicModel->findAll();
            foreach ($clinics as &$c) {
                $c['channels'] = $channelModel->where('clinic_id', $c['id'])->findAll();
            }
        }

        $data = [
            'title' => 'System Settings',
            'settings' => $settings,
            'active_tab' => $activeTab,
            'max_booking_chair_count' => $maxBookingChairCount,
            'backups' => $backups,
            'demo' => $appConfig->demo,
            'plans' => $planModel->where('status', 'active')->orderBy('name', 'ASC')->findAll(),
            'subscription' => $subscription,
            'subscription_plan' => $subscriptionPlan,
            'latest_plan_request' => $latestRequest,
            'clinic_uuid' => $clinic['uuid'] ?? '',
            'kiosk_pin_set' => $kioskPinSet,
            'kiosk_feature_enabled' => $kioskFeatureEnabled,
            'channels' => $allChannels,
            'clinics' => $clinics, // For superadmin
            'email_config' => $emailConfig,
            'sms_config' => $smsConfig,
            'wa_config' => $waConfig,
            'wa_feature_enabled' => $waFeatureEnabled,
            'notification_settings' => $this->getNotificationSettings(),
            'is_global' => $isGlobal
        ];

        return $this->view('settings/index', $data);
    }

    public function requestPlan()
    {
        $clinicId = session()->get('active_clinic_id');
        if (!$clinicId) return redirect()->back()->with('error', 'Clinic context required');

        $planId = $this->request->getPost('plan_id');
        $duration = $this->request->getPost('duration') ?: 'monthly';

        $requestModel = new PlanRequestModel();
        $requestModel->insert([
            'clinic_id' => $clinicId,
            'requested_plan_id' => $planId,
            'requested_duration' => $duration,
            'status' => 'pending',
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ]);

        // Audit log
        $auditModel = new PlanAuditModel();
        $auditModel->saveAudit([
            'clinic_id' => $clinicId,
            'action' => 'plan_request',
            'details' => [
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
        $currentSettings = $this->getSettings();

        $rules = [
            'timezone' => 'required',
            'date_format' => 'required|in_list[Y-m-d,m/d/Y,d/m/Y]',
            'time_format' => 'required|in_list[24,12]',
            'ui_language' => 'required|in_list[en,ar]',
            'currency' => 'permit_empty|in_list[ILS,USD,JOD,EUR,GBP]',
            'currency_position' => 'permit_empty|in_list[before,after]',
            'finance_auto_post_inventory_cogs' => 'permit_empty|in_list[0,1]',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $settingsData = [
            'timezone' => $this->request->getPost('timezone'),
            'date_format' => $this->request->getPost('date_format'),
            'time_format' => $this->request->getPost('time_format'),
            'ui_language' => $this->request->getPost('ui_language'),
            'currency' => (string) ($this->request->getPost('currency') ?: ($currentSettings['currency'] ?? 'ILS')),
            'currency_position' => (string) ($this->request->getPost('currency_position') ?: ($currentSettings['currency_position'] ?? 'before')),
            'finance_auto_post_inventory_cogs' => $this->request->getPost('finance_auto_post_inventory_cogs') !== null
                ? ($this->request->getPost('finance_auto_post_inventory_cogs') ? 1 : 0)
                : (int) ($currentSettings['finance_auto_post_inventory_cogs'] ?? 0),
        ];

        log_message('info', 'Saving system settings: ' . json_encode($settingsData));
        
        if ($this->saveSettings($settingsData)) {
            $this->settingsService->reloadSettings();
            session()->set('ui_locale', (string) $settingsData['ui_language']);
            log_message('info', 'System settings saved successfully');
            return redirect()->to('/settings')->with('success', 'System preferences updated successfully!');
        } else {
            log_message('error', 'Failed to save system settings');
            return redirect()->back()->withInput()->with('error', 'Failed to update system preferences. Please try again.');
        }
    }

    public function updateWorkingHours()
    {
        $maxChairCount = $this->getMaxBookingChairs();
        $rules = [
            'appointment_duration' => 'required|integer|greater_than[0]|less_than[480]',
            'booking_chair_count' => 'required|integer|greater_than[0]|less_than_equal_to[' . $maxChairCount . ']',
            'working_hours_start' => 'required|regex_match[/^([01]?[0-9]|2[0-3]):[0-5][0-9]$/]',
            'working_hours_end' => 'required|regex_match[/^([01]?[0-9]|2[0-3]):[0-5][0-9]$/]',
            'working_days' => 'permit_empty',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $chairCount = min($maxChairCount, max(1, (int) $this->request->getPost('booking_chair_count')));
        [$chairNames, $chairError] = $this->normalizeChairNames($this->request->getPost('booking_chair_names'), $chairCount);
        if ($chairError !== null) {
            return redirect()->back()->withInput()->with('error', $chairError);
        }

        $settingsData = [
            'appointment_duration' => $this->request->getPost('appointment_duration'),
            'booking_chair_count' => $chairCount,
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

    public function updateChannelStatus()
    {
        if (!session()->get('global_mode')) {
            return $this->response->setStatusCode(403)->setJSON(['success' => false, 'message' => 'Unauthorized']);
        }

        $clinicId = $this->request->getPost('clinic_id');
        $channelType = $this->request->getPost('channel_type');
        $enabled = $this->request->getPost('enabled') == '1' ? 1 : 0;

        $channelModel = new \App\Models\ClinicNotificationChannelModel();
        
        $existing = $channelModel->where('clinic_id', $clinicId)
                                 ->where('channel_type', $channelType)
                                 ->first();

        if ($existing) {
            $channelModel->update($existing['id'], ['enabled_by_superadmin' => $enabled]);
        } else {
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

    public function updateChannelConfig()
    {
        $clinicId = session()->get('active_clinic_id');
        if (!$clinicId) {
            return $this->response->setStatusCode(403)->setJSON(['success' => false, 'message' => 'No context']);
        }

        $channelType = $this->request->getPost('channel_type');
        $configJson = $this->request->getPost('config_json');
        $action = $this->request->getPost('action'); 

        $channelModel = new \App\Models\ClinicNotificationChannelModel();
        
        if ($action === 'save_config') {
            $config = json_decode($configJson, true);
            if (!$config && $configJson) {
                return $this->response->setJSON(['success' => false, 'message' => 'Invalid JSON']);
            }

            if ($channelType === 'whatsapp') {
                $config = $this->normalizeWhatsAppChannelConfig($config ?? []);
            }

            $channelModel->setConfig($clinicId, $channelType, $config ?? []);
            return $this->response->setJSON(['success' => true, 'message' => 'Configuration saved']);
        }

        return $this->response->setJSON(['success' => false, 'message' => 'Invalid action']);
    }

    public function getChannelConfig()
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setStatusCode(400)->setJSON(['success' => false, 'message' => 'Invalid request.']);
        }

        $clinicId = (int) (session()->get('active_clinic_id') ?? 0);
        $channelType = trim((string) $this->request->getPost('channel_type'));
        
        $channelModel = new \App\Models\ClinicNotificationChannelModel();
        $config = $channelModel->getConfig($clinicId, $channelType) ?? [];

        if ($channelType === 'email') unset($config['smtp_pass']);
        if ($channelType === 'whatsapp') $config = $this->normalizeWhatsAppChannelConfig($config);

        return $this->response->setJSON(['success' => true, 'config' => $config]);
    }

    public function testWhatsAppChannel()
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setStatusCode(400)->setJSON(['success' => false, 'message' => 'Invalid request.']);
        }

        $clinicId = (int) (session()->get('active_clinic_id') ?? 0);
        $channelModel = new \App\Models\ClinicNotificationChannelModel();
        $channel = $channelModel->where('clinic_id', $clinicId)->where('channel_type', 'whatsapp')->first();

        if (!$channel || empty($channel['configured_by_clinic'])) {
            return $this->response->setJSON(['success' => false, 'message' => 'WhatsApp channel is not configured.']);
        }

        return $this->response->setJSON(['success' => true, 'message' => 'WhatsApp configuration is structurally valid.']);
    }

    public function updateNotifications()
    {
        $userId = session()->get('user_id');
        $clinicId = session()->get('active_clinic_id');
        
        $prefs = $this->request->getPost('preferences');
        if (!is_array($prefs)) $prefs = [];

        $clinicUserModel = new \App\Models\ClinicUserModel();
        $membership = $clinicUserModel->where('user_id', $userId)
                                      ->where('clinic_id', $clinicId)
                                      ->first();
        
        if ($membership) {
            $clinicUserModel->update($membership['id'], ['preferences' => json_encode($prefs)]);
            return redirect()->to('/settings?tab=communications&subtab=alerts')->with('success', 'Preferences updated');
        }

        return redirect()->back()->with('error', 'Membership not found');
    }

    private function normalizeWhatsAppChannelConfig(array $config): array
    {
        $defaults = [
            'appointment_reminder' => 'تذكير من العيادة: لديكم موعد بتاريخ {{appointment_date}} الساعة {{appointment_time}}.',
            'appointment_confirmation' => 'تم تأكيد موعدكم بتاريخ {{appointment_date}} الساعة {{appointment_time}}.',
            'follow_up' => 'نأمل أنكم بخير. هذه متابعة من العيادة بخصوص زيارتكم الأخيرة.',
            'payment_reminder' => 'تذكير من العيادة: يوجد رصيد مستحق بقيمة {{amount}}.',
        ];

        $templates = $config['message_templates'] ?? $defaults;
        return [
            'phone_number_id' => $config['phone_number_id'] ?? '',
            'message_templates' => $templates,
        ];
    }

    private function getSettings()
    {
        $settings = $this->settingsModel->getAllSettings();
        $defaults = $this->getDefaultSettings();
        return array_merge($defaults, $settings);
    }

    private function getDefaultSettings()
    {
        return [
            'clinic_name' => 'DentalCare Clinic',
            'clinic_address' => '123 Dental Street',
            'clinic_phone' => '+1 (555) 123-4567',
            'clinic_email' => 'info@dentalclinic.com',
            'timezone' => 'Asia/Jerusalem',
            'date_format' => 'd/m/Y',
            'time_format' => '12',
            'ui_language' => 'en',
            'currency' => 'ILS',
            'currency_position' => 'after',
            'finance_auto_post_inventory_cogs' => 1,
            'appointment_duration' => 30,
            'booking_chair_count' => 1,
            'booking_chair_names' => ['Chair 1'],
            'working_hours_start' => '09:00',
            'working_hours_end' => '17:00',
            'working_days' => ['monday', 'tuesday', 'wednesday', 'thursday', 'friday'],
        ];
    }

    private function getNotificationSettings()
    {
        $userId = session()->get('user_id');
        $clinicId = session()->get('active_clinic_id');
        
        $clinicUserModel = new \App\Models\ClinicUserModel();
        $membership = $clinicUserModel->where('user_id', $userId)
                                      ->where('clinic_id', $clinicId)
                                      ->first();
        
        $defaults = [
            'email_notifications' => 1,
            'sms_notifications' => 0,
            'appointment_created' => 1,
            'low_stock_alerts' => 1,
        ];

        if ($membership && !empty($membership['preferences'])) {
            return array_merge($defaults, json_decode($membership['preferences'], true));
        }
        return $defaults;
    }

    private function getBackups()
    {
        $backupPath = WRITEPATH . 'backups/';
        if (!is_dir($backupPath)) return [];
        
        $files = array_diff(scandir($backupPath), ['.', '..']);
        $backups = [];
        foreach ($files as $file) {
            if (pathinfo($file, PATHINFO_EXTENSION) === 'sql' || pathinfo($file, PATHINFO_EXTENSION) === 'zip') {
                $backups[] = [
                    'name' => $file,
                    'size' => $this->formatBytes(filesize($backupPath . $file)),
                    'created_at' => date('Y-m-d H:i:s', filemtime($backupPath . $file))
                ];
            }
        }
        return $backups;
    }

    private function getMaxBookingChairs()
    {
        return 3; 
    }

    private function normalizeChairNames($names, $count)
    {
        if (!is_array($names)) $names = [];
        $result = [];
        for ($i = 0; $i < $count; $i++) {
            $result[] = !empty($names[$i]) ? trim($names[$i]) : "Chair " . ($i + 1);
        }
        return [$result, null];
    }

    private function saveSettings($data)
    {
        foreach ($data as $key => $value) {
            $type = is_array($value) ? 'json' : 'string';
            if (!$this->settingsModel->setSetting($key, $value, $type)) return false;
        }
        return true;
    }

    private function formatBytes($bytes)
    {
        if ($bytes >= 1048576) return number_format($bytes / 1048576, 2) . ' MB';
        if ($bytes >= 1024) return number_format($bytes / 1024, 2) . ' KB';
        return $bytes . ' bytes';
    }
}
