<?php

namespace Config;

use CodeIgniter\Config\BaseConfig;
use CodeIgniter\Session\Handlers\BaseHandler;
use CodeIgniter\Session\Handlers\DatabaseHandler;

/**
 * Alternative Session Configuration using Database
 * Use this if file-based sessions continue to cause issues
 */
class Session_Database extends BaseConfig
{
    /**
     * Session Driver - Database
     */
    public string $driver = DatabaseHandler::class;

    /**
     * Session Cookie Name
     */
    public string $cookieName = 'ci_session';

    /**
     * Session Expiration (2 hours)
     */
    public int $expiration = 7200;

    /**
     * Session Save Path - Table name for database sessions
     */
    public string $savePath = 'ci_sessions';

    /**
     * Session Match IP
     */
    public bool $matchIP = false;

    /**
     * Session Time to Update
     */
    public int $timeToUpdate = 300;

    /**
     * Session Regenerate Destroy
     */
    public bool $regenerateDestroy = false;

    /**
     * Session Database Group
     */
    public ?string $DBGroup = 'default';

    /**
     * Lock Retry Interval (microseconds)
     */
    public int $lockRetryInterval = 100_000;

    /**
     * Lock Max Retries
     */
    public int $lockMaxRetries = 300;
}
