<?php

namespace Config;

use CodeIgniter\Database\Config;

/**
 * Database Configuration
 */
class Database extends Config
{ 
    /**
     * The directory that holds the Migrations and Seeds directories.
     */
    public string $filesPath = APPPATH . 'Database' . DIRECTORY_SEPARATOR;

    /**
     * Lets you choose which connection group to use if no other is specified.
     */
    public string $defaultGroup = 'default';

    /**
     * The default database connection.
     *
     * @var array<string, mixed>
     */
    public array $default = [
        'DSN'          => '',
        'hostname'     => 'localhost',
        'username'     => '',
        'password'     => '',
        'database'     => '',
        'DBDriver'     => 'MySQLi',
        'DBPrefix'     => '',
        'pConnect'     => false,
        'DBDebug'      => (ENVIRONMENT === 'development'),
        'charset'      => 'utf8mb4',
        'DBCollat'     => 'utf8mb4_unicode_ci',
        'swapPre'      => '',
        'encrypt'      => false,
        'compress'     => false,
        'strictOn'     => false,
        'failover'     => [],
        'port'         => 3306,
        'numberNative' => false,
        'foundRows'    => false,
        'dateFormat'   => [
            'date'     => 'Y-m-d',
            'datetime' => 'Y-m-d H:i:s',
            'time'     => 'H:i:s',
        ],
    ];

    /**
     * PHPUnit / automated test connection group.
     * Defaults to local dev DB if dedicated test env vars are not provided.
     *
     * @var array<string, mixed>
     */
    public array $tests = [
        'DSN'          => '',
        'hostname'     => 'localhost',
        'username'     => '',
        'password'     => '',
        'database'     => '',
        'DBDriver'     => 'MySQLi',
        'DBPrefix'     => '',
        'pConnect'     => false,
        'DBDebug'      => true,
        'charset'      => 'utf8mb4',
        'DBCollat'     => 'utf8mb4_unicode_ci',
        'swapPre'      => '',
        'encrypt'      => false,
        'compress'     => false,
        'strictOn'     => false,
        'failover'     => [],
        'port'         => 3306,
        'numberNative' => false,
        'foundRows'    => false,
        'dateFormat'   => [
            'date'     => 'Y-m-d',
            'datetime' => 'Y-m-d H:i:s',
            'time'     => 'H:i:s',
        ],
    ];

    public function __construct()
    {
        parent::__construct();

        // Ensure that we always set the database group to 'tests' if
        // we are currently running an automated test suite, so that
        // we don't overwrite live data on accident.
        if (ENVIRONMENT === 'testing') {
            $this->defaultGroup = 'tests';
        }
        
        // Load credentials from environment variables
        $this->default['hostname'] = (string) env('database.default.hostname', 'localhost');
        $this->default['database'] = (string) env('database.default.database', '');
        $this->default['username'] = (string) env('database.default.username', '');
        $this->default['password'] = (string) env('database.default.password', '');
        $this->default['DBDriver'] = (string) env('database.default.DBDriver', 'MySQLi');
        $this->default['port']     = (int) env('database.default.port', 3306);

        // Test group can be overridden independently; otherwise inherit local defaults.
        $this->tests['hostname'] = (string) env('database.tests.hostname', $this->default['hostname']);
        $this->tests['database'] = (string) env('database.tests.database', $this->default['database']);
        $this->tests['username'] = (string) env('database.tests.username', $this->default['username']);
        $this->tests['password'] = (string) env('database.tests.password', $this->default['password']);
        $this->tests['DBDriver'] = (string) env('database.tests.DBDriver', $this->default['DBDriver']);
        $this->tests['port']     = (int) env('database.tests.port', (int) $this->default['port']);
    }
} 
