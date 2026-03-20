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
    public function update()
    {
        return redirect()->to('/settings')->with('error', 'Use the section-specific settings forms to save changes.');
    }

    public function email() { return $this->index('email'); }
    public function sms() { return $this->index('sms'); }
    public function whatsapp() { return $this->index('whatsapp'); }
    public function channels() { return $this->index('channels'); }
    public function kiosk() { return $this->index('kiosk'); }
    public function backup()
    {
        return redirect()->to('/settings?tab=backup');
    }

    public function security()
    {
        return redirect()->to('/settings?tab=system');
    }

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
            $redirectTab = (string) ($this->request->getPost('redirect_tab') ?: 'system');
            $allowedTabs = ['clinic', 'subscription', 'system', 'hours', 'kiosk', 'backup', 'communications'];
            if (!in_array($redirectTab, $allowedTabs, true)) {
                $redirectTab = 'system';
            }
            return redirect()->to('/settings?tab=' . rawurlencode($redirectTab))->with('success', 'System preferences updated successfully!');
        } else {
            log_message('error', 'Failed to save system settings');
            return redirect()->back()->withInput()->with('error', 'Failed to update system preferences. Please try again.');
        }
    }

    /**
     * Quick language switch from header.
     * Updates session locale immediately and persists per-clinic setting when available.
     */
    public function switchLanguage(?string $requestedLocale = null)
    {
        $locale = strtolower(trim((string) ($requestedLocale ?? $this->request->getPost('ui_language') ?? $this->request->getGet('ui_language'))));
        if (!in_array($locale, ['en', 'ar'], true)) {
            return redirect()->back()->with('error', 'Invalid language selection.');
        }

        session()->set('ui_locale', $locale);

        $clinicId = (int) (session()->get('active_clinic_id') ?? 0);
        if ($clinicId > 0) {
            $this->saveSettings(['ui_language' => $locale]);
            $this->settingsService->reloadSettings();
        }

        $returnTo = trim((string) ($this->request->getPost('return_to') ?? $this->request->getGet('return_to')));
        if ($returnTo !== '') {
            return redirect()->to($returnTo);
        }

        return redirect()->back();
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
            return redirect()->to('/settings?tab=hours')->with('success', 'Working hours updated successfully!');
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
        if ($clinicId <= 0) {
            return $this->response->setStatusCode(403)->setJSON(['success' => false, 'message' => 'Tenant context is required.']);
        }

        $channelModel = new \App\Models\ClinicNotificationChannelModel();
        $channel = $channelModel->where('clinic_id', $clinicId)->where('channel_type', 'whatsapp')->first();
        $channelConfig = $channelModel->getConfig($clinicId, 'whatsapp') ?? [];

        if (!$channel || empty($channel['configured_by_clinic'])) {
            return $this->response->setJSON(['success' => false, 'message' => 'WhatsApp channel is not configured for this clinic.']);
        }

        if (empty($channel['enabled_by_superadmin'])) {
            return $this->response->setJSON(['success' => false, 'message' => 'WhatsApp is disabled by the platform administrator.']);
        }

        if (empty($channel['validated'])) {
            return $this->response->setJSON(['success' => false, 'message' => 'WhatsApp channel is saved but not validated yet.']);
        }

        $phoneNumberId = trim((string) ($channelConfig['phone_number_id'] ?? $channelConfig['wa_phone_number_id'] ?? ''));
        if ($phoneNumberId === '') {
            return $this->response->setJSON(['success' => false, 'message' => 'Phone Number ID is missing from the clinic WhatsApp configuration.']);
        }

        $planGuard = new \App\Services\PlanGuard();
        if (!$planGuard->isFeatureEnabled($clinicId, 'notifications.whatsapp.enabled', false)) {
            return $this->response->setJSON(['success' => false, 'message' => 'Your current plan does not allow WhatsApp notifications.']);
        }

        $secretModel = new \App\Models\PlatformSecretModel();
        $missingSecrets = [];
        foreach (['wa_access_token', 'wa_app_secret', 'wa_verify_token'] as $secretKey) {
            $value = $secretModel->getSecret($secretKey);
            if (!is_string($value) || trim($value) === '') {
                $missingSecrets[] = $secretKey;
            }
        }

        if (!empty($missingSecrets)) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Platform secret configuration is incomplete: ' . implode(', ', $missingSecrets) . '.',
            ]);
        }

        return $this->response->setJSON(['success' => true, 'message' => 'WhatsApp channel is ready for dispatch and signed webhooks.']);
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

    public function createBackup()
    {
        $clinicId = (int) (session()->get('active_clinic_id') ?? 0);
        if ($clinicId <= 0) {
            return redirect()->back()->with('error', 'Clinic context required.');
        }

        $backupPath = WRITEPATH . 'backups' . DIRECTORY_SEPARATOR;
        if (!is_dir($backupPath) && !mkdir($backupPath, 0775, true) && !is_dir($backupPath)) {
            return redirect()->back()->with('error', 'Unable to create the backup directory.');
        }

        $clinicSlug = preg_replace('/[^a-z0-9]+/i', '-', strtolower((string) ($this->settingsService->get('clinic_name', 'clinic'))));
        $clinicSlug = trim((string) $clinicSlug, '-');
        if ($clinicSlug === '') {
            $clinicSlug = 'clinic-' . $clinicId;
        }

        $filename = sprintf('%s-%s.sql', $clinicSlug, date('Ymd-His'));
        $fullPath = $backupPath . $filename;
        $content = $this->settingsService->generateDatabaseBackup();

        if (file_put_contents($fullPath, $content) === false) {
            return redirect()->back()->with('error', 'Failed to create the backup file.');
        }

        return redirect()->to('/settings?tab=backup')->with('success', 'Backup created successfully.');
    }

    public function downloadBackup(string $filename)
    {
        $safeFilename = basename($filename);
        if ($safeFilename === '' || $safeFilename !== $filename) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        $fullPath = WRITEPATH . 'backups' . DIRECTORY_SEPARATOR . $safeFilename;
        if (!is_file($fullPath)) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        return $this->response->download($fullPath, null)->setFileName($safeFilename);
    }

    public function restore()
    {
        return redirect()->to('/settings?tab=backup')->with('error', 'Backup restore is not available from the web settings UI yet.');
    }

    public function updateRetention()
    {
        if (!session()->get('global_mode')) {
            return redirect()->to('/settings?tab=backup')->with('error', 'Export retention policy is managed by the platform administrator.');
        }

        $rules = [
            'retention_mode' => 'required|in_list[latest,keep_last_n,keep_x_days]',
            'retention_last_n' => 'permit_empty|is_natural_no_zero',
            'retention_days' => 'permit_empty|is_natural_no_zero',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $mode = (string) $this->request->getPost('retention_mode');
        $lastN = (int) ($this->request->getPost('retention_last_n') ?: 5);
        $days = (int) ($this->request->getPost('retention_days') ?: 30);

        $saved = $this->settingsModel->setGlobalSetting('retention_mode', $mode, 'string')
            && $this->settingsModel->setGlobalSetting('retention_last_n', $lastN, 'number')
            && $this->settingsModel->setGlobalSetting('retention_days', $days, 'number');

        if (!$saved) {
            return redirect()->to('/settings?tab=backup')->with('error', 'Failed to update export retention policy.');
        }

        return redirect()->to('/settings?tab=backup')->with('success', 'Export retention policy updated successfully.');
    }

    public function updateSecurity()
    {
        return redirect()->to('/settings?tab=system')->with('error', 'Security settings are not managed from a dedicated tenant section yet.');
    }

    private function normalizeWhatsAppChannelConfig(array $config): array
    {
        $decode = static fn(string $value): string => json_decode('"' . $value . '"') ?? '';
        $defaults = [
            'appointment_reminder' => $decode('\\u062a\\u0630\\u0643\\u064a\\u0631 \\u0645\\u0646 \\u0627\\u0644\\u0639\\u064a\\u0627\\u062f\\u0629: \\u0644\\u062f\\u064a\\u0643\\u0645 \\u0645\\u0648\\u0639\\u062f \\u0628\\u062a\\u0627\\u0631\\u064a\\u062e {{appointment_date}} \\u0627\\u0644\\u0633\\u0627\\u0639\\u0629 {{appointment_time}}. \\u0644\\u0644\\u0645\\u062a\\u0627\\u0628\\u0639\\u0629 \\u064a\\u0631\\u062c\\u0649 \\u0627\\u0644\\u0631\\u062f \\u0639\\u0644\\u0649 \\u0647\\u0630\\u0647 \\u0627\\u0644\\u0631\\u0633\\u0627\\u0644\\u0629.'),
            'appointment_confirmation' => $decode('\\u062a\\u0645 \\u062a\\u0623\\u0643\\u064a\\u062f \\u0645\\u0648\\u0639\\u062f\\u0643\\u0645 \\u0628\\u062a\\u0627\\u0631\\u064a\\u062e {{appointment_date}} \\u0627\\u0644\\u0633\\u0627\\u0639\\u0629 {{appointment_time}}. \\u0646\\u062a\\u0637\\u0644\\u0639 \\u0644\\u062e\\u062f\\u0645\\u062a\\u0643\\u0645.'),
            'follow_up' => $decode('\\u0646\\u0623\\u0645\\u0644 \\u0623\\u0646\\u0643\\u0645 \\u0628\\u062e\\u064a\\u0631. \\u0647\\u0630\\u0647 \\u0645\\u062a\\u0627\\u0628\\u0639\\u0629 \\u0645\\u0646 \\u0627\\u0644\\u0639\\u064a\\u0627\\u062f\\u0629 \\u0628\\u062e\\u0635\\u0648\\u0635 \\u0632\\u064a\\u0627\\u0631\\u062a\\u0643\\u0645 \\u0627\\u0644\\u0623\\u062e\\u064a\\u0631\\u0629. \\u0644\\u0623\\u064a \\u0627\\u0633\\u062a\\u0641\\u0633\\u0627\\u0631 \\u064a\\u0631\\u062c\\u0649 \\u0627\\u0644\\u0631\\u062f \\u0639\\u0644\\u0649 \\u0647\\u0630\\u0647 \\u0627\\u0644\\u0631\\u0633\\u0627\\u0644\\u0629.'),
            'payment_reminder' => $decode('\\u062a\\u0630\\u0643\\u064a\\u0631 \\u0645\\u0646 \\u0627\\u0644\\u0639\\u064a\\u0627\\u062f\\u0629: \\u064a\\u0648\\u062c\\u062f \\u0631\\u0635\\u064a\\u062f \\u0645\\u0633\\u062a\\u062d\\u0642 \\u0628\\u0642\\u064a\\u0645\\u0629 {{amount}}. \\u0644\\u0644\\u062a\\u0641\\u0627\\u0635\\u064a\\u0644 \\u064a\\u0631\\u062c\\u0649 \\u0627\\u0644\\u062a\\u0648\\u0627\\u0635\\u0644 \\u0645\\u0639\\u0646\\u0627 \\u0639\\u0628\\u0631 \\u0647\\u0630\\u0647 \\u0627\\u0644\\u0645\\u062d\\u0627\\u062f\\u062b\\u0629.'),
        ];
        $templates = is_array($config['message_templates'] ?? null) ? $config['message_templates'] : [];
        foreach ($defaults as $key => $defaultText) {
            $value = trim((string) ($templates[$key] ?? ''));
            $templates[$key] = $value !== '' ? $value : $defaultText;
        }
        $fallback = is_array($config['fallback_template'] ?? null) ? $config['fallback_template'] : [];
        return [
            'phone_number_id' => trim((string) ($config['phone_number_id'] ?? '')),
            'api_version' => trim((string) ($config['api_version'] ?? $config['graph_api_version'] ?? '')),
            'fallback_template' => [
                'name' => trim((string) ($fallback['name'] ?? '')),
                'language' => trim((string) ($fallback['language'] ?? 'ar')),
                'components' => is_array($fallback['components'] ?? null) ? $fallback['components'] : [],
            ],
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

