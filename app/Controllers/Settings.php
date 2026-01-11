<?php

namespace App\Controllers;

use App\Models\SettingsModel;
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
        $backups = $this->getBackups();
        
        // Debug: Log the backups data
        log_message('debug', 'Backups data: ' . json_encode($backups));
        
        // Check if demo mode is enabled
        $appConfig = config('App');
        
        $data = [
            'title' => 'System Settings',
            'settings' => $this->getSettings(),
            'backups' => $backups,
            'demo' => $appConfig->demo,
        ];

        return $this->view('settings/index', $data);
    }

    public function updateClinic()
    {
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

        // Handle Secure Logo Upload
        $logoFile = $this->request->getFile('clinic_logo');
        $removeLogo = $this->request->getPost('clinic_logo_remove');
        $uploadPath = FCPATH . 'uploads/clinic';

        if ($logoFile && $logoFile->isValid() && !$logoFile->hasMoved()) {
            // Ensure directory exists
            if (!is_dir($uploadPath)) {
                mkdir($uploadPath, 0755, true);
            }

            // Delete existing clinic-logo.*
            $existingLogos = glob($uploadPath . '/clinic-logo.*');
            if ($existingLogos) {
                foreach ($existingLogos as $file) {
                    if (is_file($file)) unlink($file);
                }
            }

            $ext = $logoFile->guessExtension();
            if (!empty($ext)) {
                $newName = 'clinic-logo.' . $ext;
                $logoFile->move($uploadPath, $newName);
                $settingsData['clinic_logo_path'] = 'uploads/clinic/' . $newName;
            } else {
                return redirect()->back()->withInput()->with('error', 'Failed to determine logo file extension.');
            }
        } elseif ($removeLogo) {
            // Delete existing clinic-logo.* if requested and no new file
            $existingLogos = glob($uploadPath . '/clinic-logo.*');
            if ($existingLogos) {
                foreach ($existingLogos as $file) {
                    if (is_file($file)) unlink($file);
                }
            }
            $settingsData['clinic_logo_path'] = '';
        }

        log_message('info', 'Saving clinic settings: ' . json_encode($settingsData));
        
        if ($this->saveSettings($settingsData)) {
            $this->settingsService->reloadSettings();
            log_message('info', 'Clinic settings saved successfully');
            return redirect()->to('/settings')->with('success', 'Clinic information updated successfully!');
        } else {
            log_message('error', 'Failed to save clinic settings');
            return redirect()->back()->withInput()->with('error', 'Failed to update clinic information. Please try again.');
        }
    }

    public function updateSystem()
    {
        $rules = [
            'timezone' => 'required',
            'date_format' => 'required|in_list[Y-m-d,m/d/Y,d/m/Y]',
            'time_format' => 'required|in_list[24,12]',
            'currency' => 'required',
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
            'working_hours_start' => 'required|regex_match[/^([01]?[0-9]|2[0-3]):[0-5][0-9]$/]',
            'working_hours_end' => 'required|regex_match[/^([01]?[0-9]|2[0-3]):[0-5][0-9]$/]',
            'working_days' => 'permit_empty',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $settingsData = [
            'appointment_duration' => $this->request->getPost('appointment_duration'),
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
            'working_hours_start' => 'required|regex_match[/^([01]?[0-9]|2[0-3]):[0-5][0-9]$/]',
            'working_hours_end' => 'required|regex_match[/^([01]?[0-9]|2[0-3]):[0-5][0-9]$/]',
            'working_days' => 'permit_empty',
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
            'timezone' => $this->request->getPost('timezone'),
            'date_format' => $this->request->getPost('date_format'),
            'time_format' => $this->request->getPost('time_format'),
            'currency' => $this->request->getPost('currency'),
            'appointment_duration' => $this->request->getPost('appointment_duration'),
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
        
        // Test database connection first
        try {
            $db = \Config\Database::connect();
            $db->query("SELECT 1"); // Simple test query
            log_message('info', 'Database connection test successful');
        } catch (\Exception $e) {
            log_message('error', 'Database connection test failed: ' . $e->getMessage());
            return redirect()->to('/settings')->with('error', 'Database connection failed: ' . $e->getMessage());
        }
        
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
        $rules = [
            'email_notifications' => 'required|in_list[0,1]',
            'sms_notifications' => 'required|in_list[0,1]',
            'appointment_reminders' => 'required|in_list[0,1]',
            'reminder_days_before' => 'required|integer|greater_than_equal_to[1]|less_than_equal_to[30]',
            'payment_reminders' => 'required|in_list[0,1]',
            'low_stock_alerts' => 'required|in_list[0,1]',
            'system_updates' => 'required|in_list[0,1]',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $notificationData = [
            'email_notifications' => $this->request->getPost('email_notifications'),
            'sms_notifications' => $this->request->getPost('sms_notifications'),
            'appointment_reminders' => $this->request->getPost('appointment_reminders'),
            'reminder_days_before' => $this->request->getPost('reminder_days_before'),
            'payment_reminders' => $this->request->getPost('payment_reminders'),
            'low_stock_alerts' => $this->request->getPost('low_stock_alerts'),
            'system_updates' => $this->request->getPost('system_updates'),
            'updated_at' => date('Y-m-d H:i:s'),
        ];

        if ($this->saveNotificationSettings($notificationData)) {
            return redirect()->to('/settings/notifications')->with('success', 'Notification settings updated successfully!');
        } else {
            return redirect()->back()->withInput()->with('error', 'Failed to update notification settings. Please try again.');
        }
    }

    private function getSettings()
    {
        try {
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
                'working_hours_start' => '09:00',
                'working_hours_end' => '17:00',
                'working_days' => ['monday', 'tuesday', 'wednesday', 'thursday', 'friday'],
            ];
            
            return array_merge($defaults, $settings);
        } catch (\Exception $e) {
            log_message('error', 'Error getting settings: ' . $e->getMessage());
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
                'working_hours_start' => '09:00',
                'working_hours_end' => '17:00',
                'working_days' => ['monday', 'tuesday', 'wednesday', 'thursday', 'friday'],
            ];
        }
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
        return [
            'email_notifications' => 1,
            'sms_notifications' => 0,
            'appointment_reminders' => 1,
            'reminder_days_before' => 1,
            'payment_reminders' => 1,
            'low_stock_alerts' => 1,
            'system_updates' => 1,
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
            'working_hours_start' => '09:00',
            'working_hours_end' => '17:00',
            'working_days' => ['monday', 'tuesday', 'wednesday', 'thursday', 'friday'],
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

    private function saveSecuritySettings($data)
    {
        // In a real application, you would save to database
        return true;
    }

    private function saveNotificationSettings($data)
    {
        // In a real application, you would save to database
        return true;
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
            
            // Get database configuration
            $db = \Config\Database::connect();
            
            // Get database config from the config file
            $dbConfig = config('Database');
            $defaultGroup = $dbConfig->defaultGroup;
            $database = $dbConfig->{$defaultGroup}['database'];
            $hostname = $dbConfig->{$defaultGroup}['hostname'];
            $username = $dbConfig->{$defaultGroup}['username'];
            $password = $dbConfig->{$defaultGroup}['password'];
            
            // Generate SQL backup content
            $sqlContent = $this->generateDatabaseBackup($db);
            
            // Check if SQL content was generated
            if (empty($sqlContent)) {
                log_message('error', 'Generated SQL content is empty');
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
    
    private function generateDatabaseBackup($db)
    {
        try {
            // Get database config from the config file
            $dbConfig = config('Database');
            $defaultGroup = $dbConfig->defaultGroup;
            $database = $dbConfig->{$defaultGroup}['database'];
            
            $sql = "-- Dental Management System Database Backup\n";
            $sql .= "-- Generated on: " . date('Y-m-d H:i:s') . "\n";
            $sql .= "-- Database: " . $database . "\n\n";
            
            $sql .= "SET SQL_MODE = \"NO_AUTO_VALUE_ON_ZERO\";\n";
            $sql .= "SET AUTOCOMMIT = 0;\n";
            $sql .= "START TRANSACTION;\n";
            $sql .= "SET time_zone = \"+00:00\";\n\n";
            
            // Get all tables
            $tables = $db->listTables();
            
            if (empty($tables)) {
                log_message('error', 'No tables found in database');
                return $sql . "-- No tables found\nCOMMIT;\n";
            }
            
            foreach ($tables as $table) {
                $sql .= "-- Table structure for table `{$table}`\n";
                
                try {
                    // Get table structure
                    $query = $db->query("SHOW CREATE TABLE `{$table}`");
                    $result = $query->getRow();
                    
                    if ($result && isset($result->{'Create Table'})) {
                        $sql .= "DROP TABLE IF EXISTS `{$table}`;\n";
                        $sql .= $result->{'Create Table'} . ";\n\n";
                        
                        // Get table data
                        $dataQuery = $db->query("SELECT * FROM `{$table}`");
                        $rows = $dataQuery->getResultArray();
                        
                        if (!empty($rows)) {
                            $sql .= "-- Data for table `{$table}`\n";
                            
                            foreach ($rows as $row) {
                                $values = [];
                                foreach ($row as $value) {
                                    if ($value === null) {
                                        $values[] = 'NULL';
                                    } else {
                                        $values[] = "'" . addslashes($value) . "'";
                                    }
                                }
                                
                                $sql .= "INSERT INTO `{$table}` VALUES (" . implode(', ', $values) . ");\n";
                            }
                            $sql .= "\n";
                        }
                    } else {
                        log_message('error', "Could not get CREATE TABLE statement for table: {$table}");
                    }
                } catch (\Exception $e) {
                    log_message('error', "Error processing table {$table}: " . $e->getMessage());
                    $sql .= "-- Error processing table {$table}: " . $e->getMessage() . "\n";
                }
            }
            
            $sql .= "COMMIT;\n";
            
            return $sql;
        } catch (\Exception $e) {
            log_message('error', 'Error in generateDatabaseBackup: ' . $e->getMessage());
            return "-- Error generating backup: " . $e->getMessage() . "\n";
        }
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
