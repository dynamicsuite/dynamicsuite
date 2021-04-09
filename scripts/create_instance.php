<?php
/**
 * This file is part of the Dynamic Suite framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @package DynamicSuite
 * @author Grant Martin <commgdog@gmail.com>
 * @copyright 2021 Dynamic Suite Team
 * @noinspection PhpIncludeInspection
 */

namespace DynamicSuite;
use DynamicSuite\Core\DynamicSuite;
use DynamicSuite\Package\Packages;

/**
 * Create the environment.
 */
require_once __DIR__ . '/create_environment.php';

/**
 * Initialize the instance.
 */
DynamicSuite::init();

/**
 * Add package autoload paths to the autoload queue.
 */
spl_autoload_register(function (string $class) {
    if (class_exists($class)) {
        return;
    }
    $file = str_replace('\\', '/', $class) . '.php';
    foreach (Packages::$autoload as $dir) {
        $path = "$dir/$file";
        if ((DS_CACHING && opcache_is_script_cached($path)) || file_exists($path)) {
            require_once $path;
            break;
        }
    }
});

/**
 * Run package initialization scripts.
 */
foreach (Packages::$init as $script) {
    require_once $script;
}