<?php
/*
 * Dynamic Suite
 * Copyright (C) 2020 Dynamic Suite Team
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation version 3.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software Foundation,
 * Inc., 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301  USA
 */

/** @noinspection PhpIncludeInspection */
/** @noinspection PhpUnusedLocalVariableInspection */

namespace DynamicSuite;
use DynamicSuite\Core\DynamicSuite;
use DynamicSuite\Core\Request;

require_once realpath(__DIR__ . '/create_environment.php');

// Initialize the instance
/** @var DynamicSuite $ds */
$ds = (function() {
    $hash = DynamicSuite::getHash();
    if (apcu_exists($hash) && DS_CACHING) {
        $ds = apcu_fetch($hash);
    } else {
        $ds = new DynamicSuite();
        $ds->packages->loadPackages();
        if (DS_CACHING) $ds->save();
    }
    return $ds;
})();

// Add global package autoload paths to the autoload queue
spl_autoload_register(function (string $class) {
    if (class_exists($class)) return;
    global $ds;
    $file = str_replace('\\', '/', $class) . '.php';
    foreach ($ds->packages->resources->autoload as $dir) {
        $path = DS_ROOT_DIR . "/$dir/$file";
        if (DS_CACHING && opcache_is_script_cached($path)) {
            require_once $path;
            break;
        } elseif (file_exists($path)) {
            require_once $path;
            break;
        }
    }
});

// Set request type
if (Request::isViewable()) {
    define('DS_VIEW', true);
} elseif (Request::isApi()) {
    define('DS_API', true);
} elseif (Request::isCli()) {
    define('DS_CLI', true);
} else {
    trigger_error('Unknown request type', E_USER_ERROR);
}

// Run global package initialization scripts
foreach ($ds->packages->resources->init as $script) {
    (function ($script) {
        global $ds;
        require_once $script;
    })($script);
}
