<?php

namespace App\Models;

use CodeIgniter\Model;

class ClinicNotificationChannelModel extends TenantAwareModel
{
    protected $table = 'clinic_notification_channels';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = false;
    protected $protectFields = true;
    protected $allowedFields = [
        'clinic_id',
        'channel_type',
        'enabled_by_superadmin',
        'configured_by_clinic',
        'validated',
        'provider_type',
        'config_encrypted',
        'last_tested_at'
    ];

    protected $useTimestamps = true;
    protected $dateFormat = 'datetime';
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    protected $beforeInsert = ['setClinicId'];

    /**
     * Set configuration for a channel (encrypts data)
     */
    public function setConfig(int $clinicId, string $channelType, array $config)
    {
        $encrypter = \Config\Services::encrypter();
        $encrypted = $encrypter->encrypt(json_encode($config)); // encrypt returns generic string (base64 encoded usually)

        // Upsert logic manually since we need tenant check safety
        $existing = $this->where('clinic_id', $clinicId)
                         ->where('channel_type', $channelType)
                         ->first();

        $data = [
            'clinic_id' => $clinicId,
            'channel_type' => $channelType,
            'config_encrypted' => base64_encode($encrypted), // Ensure safe storage if binary
            'configured_by_clinic' => 1,
            'updated_at' => date('Y-m-d H:i:s')
        ];

        if ($existing) {
            return $this->update($existing['id'], $data);
        } else {
            // New record - might be created by superadmin enable first, but if not:
            $data['enabled_by_superadmin'] = 0; // Default off until enabled
            $data['validated'] = 0;
            return $this->insert($data);
        }
    }

    /**
     * Get configuration for a channel (decrypts data)
     */
    public function getConfig(int $clinicId, string $channelType)
    {
        $row = $this->where('clinic_id', $clinicId)
                    ->where('channel_type', $channelType)
                    ->first();

        if (!$row || empty($row['config_encrypted'])) {
            return null;
        }

        try {
            $encrypter = \Config\Services::encrypter();
            $json = $encrypter->decrypt(base64_decode($row['config_encrypted']));
            return json_decode($json, true);
        } catch (\Exception $e) {
            log_message('error', 'Failed to decrypt channel config: ' . $e->getMessage());
            return null;
        }
    }
}
