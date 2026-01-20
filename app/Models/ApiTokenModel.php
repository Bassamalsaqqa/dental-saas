<?php

declare(strict_types=1);

namespace App\Models;

use CodeIgniter\Model;
use CodeIgniter\Database\RawSql;

class ApiTokenModel extends Model
{
    protected $table            = 'api_tokens';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;

    protected $allowedFields = [
        'user_id',
        'name',
        'token_hash',
        'last_used_at',
        'expires_at',
        'created_at',
        'updated_at',
        // 'revoked_at', // include if your schema has it
    ];

    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    /**
     * Validate a token and return the associated user ID.
     * Supports token_hash stored as either:
     *  - hex sha256 (CHAR(64) / VARCHAR)
     *  - raw binary sha256 (BINARY(32) / VARBINARY)
     */
    public function validateToken(string $rawToken): ?int
    {
        $rawToken = trim($rawToken);
        if ($rawToken === '') {
            return null;
        }

        $hashHex = hash('sha256', $rawToken);
        $hashBin = hash('sha256', $rawToken, true);

        $record = $this->groupStart()
                ->where('token_hash', $hashHex)
                ->orWhere('token_hash', $hashBin)
            ->groupEnd()
            ->groupStart()
                // Use DB time to avoid PHP/MySQL timezone drift
                ->where('expires_at IS NULL', null, false)
                ->orWhere('expires_at >', new RawSql('UTC_TIMESTAMP()'))
            ->groupEnd()
            // ->where('revoked_at IS NULL', null, false) // if exists
            ->first();

        if (!$record) {
            return null;
        }

        // Update last_used_at using DB time (no timezone ambiguity)
        $this->set('last_used_at', new RawSql('UTC_TIMESTAMP()'), false)
             ->where('id', $record['id'])
             ->update();

        return (int) $record['user_id'];
    }

    /**
     * Helper to create a new token for a user.
     * Stores token_hash as hex sha256 by default.
     * If your column is binary, switch to hash(..., true).
     */
    public function generateToken(int $userId, string $name = 'Default', ?string $expiresAt = null): string
    {
        $rawToken = bin2hex(random_bytes(32));

        // Default: hex storage
        $tokenHash = hash('sha256', $rawToken);

        $this->insert([
            'user_id'    => $userId,
            'name'       => $name,
            'token_hash' => $tokenHash,
            'expires_at' => $expiresAt,
        ]);

        return $rawToken;
    }
}
