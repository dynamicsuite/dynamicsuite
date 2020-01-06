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

namespace DynamicSuite;

require_once realpath(__DIR__ . '/create_environment.php');

// Initialize the instance
if (apcu_exists(md5(DS_ROOT_DIR)) && DS_APCU) {
    $ds = apcu_fetch(md5(DS_ROOT_DIR));
} else {
    $ds = new Instance();
    if (DS_APCU) $ds->save();
}

// Add global package autoload paths to the autoload queue
spl_autoload_register(function ($class) {
    /** @var Instance $ds */
    global $ds;
    $file = str_replace('\\', '/', $class) . '.php';
    foreach ($ds->packages->resources->autoload as $dir) {
        if (file_exists("$dir/$file")) {
            include "$dir/$file";
            break;
        }
    }
});

// Run global package initialization scripts
foreach ($ds->packages->resources->init as $path) include $path;

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