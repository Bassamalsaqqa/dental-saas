<?php

namespace App\Models;

use CodeIgniter\Model;

class PlatformSecretModel extends Model
{
    protected $table = 'platform_secrets';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = false;
    protected $protectFields = true;
    protected $allowedFields = [
        'secret_key',
        'secret_encrypted',
        'rotated_at',
        'rotated_by',
        'rotation_note',
        'created_at',
        'updated_at',
    ];

    protected $useTimestamps = true;
    protected $dateFormat = 'datetime';
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    /**
     * Stores an encrypted platform secret using the same encrypt/base64 pattern
     * used by ClinicNotificationChannelModel::setConfig().
     */
    public function setSecret(string $secretKey, string $secretValue, ?int $rotatedBy = null, ?string $rotationNote = null)
    {
        $encrypter = \Config\Services::encrypter();
        $encrypted = $encrypter->encrypt($secretValue);

        $existing = $this->where('secret_key', $secretKey)->first();
        $now = date('Y-m-d H:i:s');

        $data = [
            'secret_key' => $secretKey,
            'secret_encrypted' => base64_encode($encrypted),
            'rotated_at' => $now,
            'rotated_by' => $rotatedBy,
            'rotation_note' => $rotationNote,
            'updated_at' => $now,
        ];

        if ($existing) {
            return $this->update($existing['id'], $data);
        }

        $data['created_at'] = $now;
        return $this->insert($data);
    }

    /**
     * Reads and decrypts a platform secret using the same decrypt/base64 pattern
     * used by ClinicNotificationChannelModel::getConfig().
     */
    public function getSecret(string $secretKey): ?string
    {
        $row = $this->where('secret_key', $secretKey)->first();
        if (!$row || empty($row['secret_encrypted'])) {
            return null;
        }

        try {
            $encrypter = \Config\Services::encrypter();
            return (string) $encrypter->decrypt(base64_decode($row['secret_encrypted']));
        } catch (\Throwable $e) {
            log_message('error', 'Failed to decrypt platform secret {key}: {message}', [
                'key' => $secretKey,
                'message' => $e->getMessage(),
            ]);
            return null;
        }
    }

    public function listMetadata(array $secretKeys = []): array
    {
        $builder = $this->select('id, secret_key, rotated_at, rotated_by, rotation_note, created_at, updated_at');

        if (!empty($secretKeys)) {
            $builder->whereIn('secret_key', $secretKeys);
        }

        return $builder->orderBy('secret_key', 'ASC')->findAll();
    }
}
