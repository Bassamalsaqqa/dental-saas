<?php
namespace Config;
use CodeIgniter\Config\BaseConfig;
class MediaDerivatives extends BaseConfig
{
    public bool $enabled = false;
    public int $maxSourcePixels = 12000000;
    public int $maxSourceBytes = 25000000;
}
