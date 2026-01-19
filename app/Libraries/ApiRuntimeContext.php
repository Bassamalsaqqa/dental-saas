<?php

namespace App\Libraries;

class ApiRuntimeContext
{
    private static ?int $userId = null;
    private static ?int $clinicId = null;

    public static function clear(): void
    {
        self::$userId = null;
        self::$clinicId = null;
    }

    public static function setUserId(int $userId): void
    {
        self::$userId = $userId;
    }

    public static function setClinicId(int $clinicId): void
    {
        self::$clinicId = $clinicId;
    }

    public static function getUserId(): ?int
    {
        return self::$userId;
    }

    public static function getClinicId(): ?int
    {
        return self::$clinicId;
    }
}
