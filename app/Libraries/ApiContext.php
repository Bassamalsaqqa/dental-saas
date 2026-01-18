<?php

namespace App\Libraries;

class ApiContext
{
    private ?int $userId = null;
    private ?int $clinicId = null;
    private ?int $roleId = null;

    public function setContext(int $userId, int $clinicId, int $roleId)
    {
        $this->userId = $userId;
        $this->clinicId = $clinicId;
        $this->roleId = $roleId;
    }

    public function getUserId(): ?int
    {
        return $this->userId;
    }

    public function getClinicId(): ?int
    {
        return $this->clinicId;
    }

    public function getRoleId(): ?int
    {
        return $this->roleId;
    }
}
