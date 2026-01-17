<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use App\Models\ApiTokenModel;
use App\Models\UserModel;

class GenerateApiToken extends BaseCommand
{
    protected $group       = 'Auth';
    protected $name        = 'auth:generate-token';
    protected $description = 'Generates a Bearer token for a user.';

    protected $usage     = 'auth:generate-token [user_id]';
    protected $arguments = ['user_id' => 'The ID of the user'];

    public function run(array $params)
    {
        $userId = $params[0] ?? null;

        if (empty($userId)) {
            $userId = CLI::prompt('User ID');
        }

        $userModel = new UserModel();
        $user = $userModel->find($userId);

        if (!$user) {
            CLI::error("User not found.");
            return;
        }

        $tokenModel = new ApiTokenModel();
        $token = $tokenModel->generateToken($userId, 'Manual Generation');

        CLI::write("Token generated for user: " . $user['email'], 'green');
        CLI::write("Token: " . $token, 'yellow');
        CLI::write("Keep this token safe. It will not be shown again.", 'red');
    }
}
