<?php

namespace Config;

use CodeIgniter\Config\BaseConfig;

class InvoiceCorrections extends BaseConfig
{
    // Activation and database migration require separate Owner release authorization.
    public bool $enabled = false;
    public string $documentMode = 'operational';
}
