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
use Error;
use Exception;

/**
 * Create the environment.
 */
require_once __DIR__ . '/create_environment.php';

/**
 * Initialize the instance.
 */
DynamicSuite::init();

/**
 * Run package initialization scripts.
 */
try {
    foreach (Packages::$init as $script) {
        if (!is_readable($script)) {
            error_log("Package init script not found: '$script'");
            Render::$server_error = true;
            continue;
        }
        require_once $script;
    }
} catch (Exception | Error $exception) {
    error_log(
        $exception->getMessage() . ' at ' .
        $exception->getTrace()[0]['file'] . ':' .
        $exception->getTrace()[0]['line']
    );
    Render::$server_error = true;
}
