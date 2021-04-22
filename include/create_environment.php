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

/**
 * Core definitions.
 */
define('DS_VERSION', '9.0.0');
define('DS_ROOT_DIR', realpath(__DIR__ . '/..'));
if (!defined('DS_CACHING')) define('DS_CACHING', false);

/**
 * Core autoloader.
 */
spl_autoload_register(function (string $class) {
    $file = DS_ROOT_DIR . '/libraries/' . strtr($class, '\\', '/') . '.php';
    @require_once $file;
});