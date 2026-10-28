<?php
namespace Config;

use CodeIgniter\Config\BaseConfig;

/** Separate schema/security/release approval is required before enabling in production. */
class StudioThemes extends BaseConfig
{
    public bool $enabled = false;
}
