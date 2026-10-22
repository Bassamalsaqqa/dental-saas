<?php

namespace Config;

/** Independently approved activation is required after additive schema deployment. */
class ClinicOperations extends \CodeIgniter\Config\BaseConfig
{
    public bool $manualAppointmentContacts = false;
}
