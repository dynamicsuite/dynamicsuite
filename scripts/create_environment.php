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
use Exception;

/**
 * Core definitions.
 */
define('DS_START', microtime(true));
define('DS_VERSION', '9.0.0');
define('DS_ROOT_DIR', realpath(__DIR__ . '/..'));
if (!defined('DS_CACHING')) define('DS_CACHING', false);
ini_set('display_errors', 0);
mb_internal_encoding('UTF-8');
mb_http_output('UTF-8');

/**
 * Exception logger.
 *
 * @param Exception $exception
 * @return void
 */
function ds_log_exception(Exception $exception): void
{
    error_log($exception->getMessage());
    error_log('  @ ' . $exception->getFile() . ':' . $exception->getLine());
    $trace = $exception->getTrace();
    for ($i = 0, $count = count($trace); $i < $count; $i++) {
        error_log("  #$i {$trace[$i]['function']} ~ {$trace[$i]['file']}:{$trace[$i]['line']}");
    }
}

/**
 * Core autoloader.
 */
spl_autoload_register(function (string $class)
{
    if (!class_exists($class)) {
        $file = DS_ROOT_DIR . '/lib/' . str_replace('\\', '/', $class) . '.php';
        if (DS_CACHING && opcache_is_script_cached($file)) {
            require_once $file;
        } elseif (file_exists($file)) {
            require_once $file;
        }
    }
});