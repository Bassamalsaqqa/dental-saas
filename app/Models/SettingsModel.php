<?php

namespace App\Models;

use CodeIgniter\Model;

class SettingsModel extends TenantAwareModel
{
    protected $table = 'settings';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = false;
    protected $protectFields = true;
    protected $allowedFields = [
        'clinic_id',
        'setting_key',
        'setting_value',
        'setting_type',
        'description',
        'created_at',
        'updated_at'
    ];

    protected $useTimestamps = true;
    protected $dateFormat = 'datetime';
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    protected $validationRules = [
        'setting_key' => 'required|max_length[100]',
        'setting_value' => 'required',
        'setting_type' => 'required|in_list[string,number,boolean,json]'
    ];

    protected $validationMessages = [
        'setting_key' => [
            'required' => 'Setting key is required',
            'max_length' => 'Setting key cannot exceed 100 characters'
        ],
        'setting_value' => [
            'required' => 'Setting value is required'
        ],
        'setting_type' => [
            'required' => 'Setting type is required',
            'in_list' => 'Setting type must be string, number, boolean, or json'
        ]
    ];

    protected $skipValidation = false;
    protected $cleanValidationRules = true;

    protected $beforeInsert = ['setClinicId'];

    /**
     * Get a setting value by key
     */
    public function getSetting($key, $default = null)
    {
        $clinicId = session()->get('active_clinic_id') ?? 0;
        $setting = $this->where('setting_key', $key)
                        ->where('clinic_id', $clinicId)
                        ->first();
        return $setting ? $this->parseValue($setting['setting_value'], $setting['setting_type']) : $default;
    }

    /**
     * Get a global (platform-wide) setting
     */
    public function getGlobalSetting($key, $default = null)
    {
        $setting = $this->where('setting_key', $key)
                        ->where('clinic_id', 0)
                        ->first();
        return $setting ? $this->parseValue($setting['setting_value'], $setting['setting_type']) : $default;
    }

    /**
     * Set a setting value
     */
    public function setSetting($key, $value, $type = 'string', $description = null)
    {
        $clinicId = session()->get('active_clinic_id') ?? 0;
        $existing = $this->where('setting_key', $key)
                         ->where('clinic_id', $clinicId)
                         ->first();

        $skipValidation = false;
        if ($value === '' && in_array($key, ['clinic_logo_path', 'clinic_website', 'clinic_tagline'], true)) {
            $this->skipValidation(true);
            $skipValidation = true;
        }
        
        $data = [
            'clinic_id'     => $clinicId,
            'setting_key'   => $key,
            'setting_value' => $this->serializeValue($value, $type),
            'setting_type'  => $type,
            'description'   => $description
        ];

        log_message('info', "Setting {$key} = " . json_encode($value) . " (type: {$type}) for Clinic: {$clinicId}");

        if ($existing) {
            $result = $this->update($existing['id'], $data);
            if ($skipValidation) $this->skipValidation(false);
            return $result;
        } else {
            $result = $this->insert($data);
            if ($skipValidation) $this->skipValidation(false);
            return $result;
        }
    }

    /**
     * Set a global setting
     */
    public function setGlobalSetting($key, $value, $type = 'string', $description = null)
    {
        $existing = $this->where('setting_key', $key)
                         ->where('clinic_id', 0)
                         ->first();
        
        $data = [
            'clinic_id'     => 0,
            'setting_key'   => $key,
            'setting_value' => $this->serializeValue($value, $type),
            'setting_type'  => $type,
            'description'   => $description
        ];

        if ($existing) {
            return $this->update($existing['id'], $data);
        } else {
            return $this->insert($data);
        }
    }

    /**
     * Get all settings for current context as an associative array
     */
    public function getAllSettings()
    {
        $clinicId = session()->get('active_clinic_id') ?? 0;
        $settings = $this->where('clinic_id', $clinicId)->findAll();
        $result = [];
        
        foreach ($settings as $setting) {
            $result[$setting['setting_key']] = $this->parseValue($setting['setting_value'], $setting['setting_type']);
        }
        
        return $result;
    }

    /**
     * Get all global (platform-wide) settings
     */
    public function getGlobalSettings()
    {
        $settings = $this->where('clinic_id', 0)->findAll();
        $result = [];
        
        foreach ($settings as $setting) {
            $result[$setting['setting_key']] = $this->parseValue($setting['setting_value'], $setting['setting_type']);
        }
        
        return $result;
    }

    /**
     * Set multiple settings at once
     */
    public function setMultipleSettings($settings)
    {
        $success = true;
        
        foreach ($settings as $key => $value) {
            $result = false;
            if (is_array($value)) {
                $result = $this->setSetting($key, $value, 'json');
            } elseif (is_numeric($value)) {
                $result = $this->setSetting($key, $value, 'number');
            } elseif (is_bool($value)) {
                $result = $this->setSetting($key, $value, 'boolean');
            } else {
                $result = $this->setSetting($key, $value, 'string');
            }
            
            if (!$result) {
                $success = false;
                log_message('error', "Failed to save setting: {$key} = " . json_encode($value));
            }
        }
        
        return $success;
    }

    /**
     * Parse setting value based on type
     */
    private function parseValue($value, $type)
    {
        switch ($type) {
            case 'number':
                return is_numeric($value) ? (float)$value : $value;
            case 'boolean':
                return filter_var($value, FILTER_VALIDATE_BOOLEAN);
            case 'json':
                return json_decode($value, true);
            default:
                return $value;
        }
    }

    /**
     * Serialize setting value based on type
     */
    private function serializeValue($value, $type)
    {
        switch ($type) {
            case 'json':
                return json_encode($value);
            case 'boolean':
                return $value ? '1' : '0';
            default:
                return (string)$value;
        }
    }
}
