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

define('DS_START', microtime(true));
define('DS_VERSION', '8.0.0');
define('DS_ROOT_DIR', realpath(__DIR__ . '/..'));
define('DS_CACHING', false);
ini_set('display_errors', 0);
mb_internal_encoding('UTF-8');
mb_http_output('UTF-8');

// Exception logger
function ds_log_exception($exception) {
    error_log($exception->getMessage());
    error_log('  @ ' . $exception->getFile() . ':' . $exception->getLine());
    $trace = $exception->getTrace();
    for ($i = 0, $count = count($trace); $i < $count; $i++) {
        error_log("  #$i {$trace[$i]['function']} ~ {$trace[$i]['file']}:{$trace[$i]['line']}");
    }
}

// Core autoloader
spl_autoload_register(function (string $class) {
    if (!class_exists($class)) {
        $file = DS_ROOT_DIR . '/lib/' . str_replace('\\', '/', $class) . '.php';
        if (DS_CACHING && opcache_is_script_cached($file)) {
            require_once $file;
        } elseif (file_exists($file)) {
            require_once $file;
        }
    }
});