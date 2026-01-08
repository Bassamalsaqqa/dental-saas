<?php

declare(strict_types=1);

/**
 * This file is part of CodeIgniter 4 framework.
 *
 * (c) CodeIgniter Foundation <admin@codeigniter.com>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

// Shim for missing intl extension - must be in global namespace BEFORE namespace declaration
if (!class_exists('Locale')) {
    class Locale
    {
        public static function getDefault()
        {
            return 'en';
        }
        public static function setDefault($locale)
        {
            return true;
        }
    }
}

namespace CodeIgniter;

use CodeIgniter\Cache\FactoriesCache;
use CodeIgniter\CLI\Console;
use CodeIgniter\Config\DotEnv;
use Config\App;
use Config\Autoload;
use Config\Modules;
use Config\Optimize;
use Config\Paths;
use Config\Services;
