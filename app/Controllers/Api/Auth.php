<?php

namespace App\Controllers\Api;

use App\Controllers\BaseController;
use App\Models\ApiTokenModel;

class Auth extends BaseController
{
    public function sessionToken()
    {
        $session = session();
        $userId = (int)($session->get('user_id') ?? 0);
        if ($userId <= 0) {
            return $this->response
                ->setStatusCode(401)
                ->setJSON([
                    'error' => [
                        'code' => 'unauthenticated',
                        'message' => 'Authentication required.'
                    ]
                ]);
        }

        $expiresAt = gmdate('Y-m-d H:i:s', time() + 1800);
        $tokenModel = new ApiTokenModel();
        $rawToken = $tokenModel->generateToken($userId, 'ui-search', $expiresAt);

        return $this->response
            ->setHeader('Cache-Control', 'no-store, no-cache, must-revalidate')
            ->setJSON([
                'token' => $rawToken,
                'expires_at' => gmdate('c', strtotime($expiresAt))
            ]);
    }
}
