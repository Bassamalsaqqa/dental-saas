<?php
namespace Config;
use CodeIgniter\Config\BaseConfig;
class FinanceCheckout extends BaseConfig
{
    // Additive schema and separate release/activation authorization are required.
    public bool $enabled = false;
}
