<?php

namespace App\Models;

use CodeIgniter\Model;

class ApiTokenModel extends Model
{
    protected $table = 'api_tokens';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = false;
    protected $allowedFields = [
        'user_id',
        'name',
        'token',
        'last_used_at',
        'expires_at',
        'created_at',
        'updated_at'
    ];

    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    /**
     * Validate a token and return the associated user ID.
     */
    public function validateToken(string $token)
    {
        $record = $this->where('token', hash('sha256', $token))
                       ->groupStart()
                           ->where('expires_at >', date('Y-m-d H:i:s'))
                           ->orWhere('expires_at', null)
                       ->groupEnd()
                       ->first();

        if ($record) {
            $this->update($record['id'], ['last_used_at' => date('Y-m-d H:i:s')]);
            return $record['user_id'];
        }

        return null;
    }

    /**
     * Helper to create a new token for a user.
     * Returns the raw token (only shown once).
     */
    public function generateToken(int $userId, string $name = 'Default', ?string $expiresAt = null)
    {
        $rawToken = bin2hex(random_bytes(32));
        $this->insert([
            'user_id'    => $userId,
            'name'       => $name,
            'token'      => hash('sha256', $rawToken),
            'expires_at' => $expiresAt
        ]);

        return $rawToken;
    }
}
