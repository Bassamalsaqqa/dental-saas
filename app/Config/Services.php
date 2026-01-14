<?php

namespace Config;

use CodeIgniter\Config\BaseService;

/**
 * Services Configuration file.
 *
 * Services are simply other classes/libraries that the system uses
 * to do its job. This is used by CodeIgniter to allow the core of the
 * framework to work as cleanly as possible.
 */
class Services extends BaseService
{
    /*
     * public static function example($getShared = true)
     * {
     *     if ($getShared) {
     *         return static::getSharedInstance('example');
     *     }
     *
     *     return new \App\Libraries\Example();
     * }
     */

    /**
     * Settings Service
     */
    public static function settings($getShared = true)
    {
        if ($getShared) {
            return static::getSharedInstance('settings');
        }

        return new \App\Services\SettingsService();
    }

    /**
     * Storage Service
     */
    public static function storage($getShared = true)
    {
        if ($getShared) {
            return static::getSharedInstance('storage');
        }

        return new \App\Services\StorageService();
    }

}