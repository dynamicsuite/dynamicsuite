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
foreach (Packages::$init as $script) {
    require_once $script;
}