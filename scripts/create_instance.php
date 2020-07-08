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

namespace DynamicSuite;
use DynamicSuite\Core\DynamicSuite;
use DynamicSuite\Package\Packages;

// Create the environment
require_once __DIR__ . '/create_environment.php';

// Initialize the instance
DynamicSuite::init();

// Add global package autoload paths to the autoload queue
spl_autoload_register(function (string $class) {
    if (class_exists($class)) {
        return;
    }
    $file = str_replace('\\', '/', $class) . '.php';
    foreach (Packages::$global['autoload'] as $dir) {
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

// Run global package initialization scripts
foreach (Packages::$global['init'] as $script) {
    require_once $script;
}