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

// Set globals
define('DS_START', microtime(true));
define('DS_VERSION', '3.2.1');
define('DS_ROOT_DIR', realpath(__DIR__ . '/..'));
define('DS_APCU', false);
define('DS_PHP_VERSION', '7.3.0');
ini_set('display_errors', 0);
chdir(DS_ROOT_DIR);

// Get forced flag state
if (isset($argv) && in_array('-f', $argv)) {
    define('CLI_FORCE', true);
    unset($argv[array_search('-f', $argv)]);
    /** @noinspection PhpUnusedLocalVariableInspection */
    $argv = array_values($argv);
} else {
    define('CLI_FORCE', false);
}

// PHP version check
if (!version_compare(PHP_VERSION, DS_PHP_VERSION, '>=')) {
    trigger_error('Dynamic Suite requires php >= ' . DS_PHP_VERSION, E_USER_ERROR);
}

// Extension check
if (!extension_loaded('apcu')) trigger_error('Missing APCU extension', E_USER_ERROR);
if (!extension_loaded('pdo_mysql')) trigger_error('Missing PDO extension', E_USER_ERROR);

// Directory check
if (!file_exists('config') && !mkdir('config')) {
    trigger_error('Could not create config directory', E_USER_ERROR);
}
if (!file_exists('packages') && !mkdir('packages')) {
    trigger_error('Could not create packages directory', E_USER_ERROR);
}
if (!file_exists('logs') && !mkdir('logs')) {
    trigger_error('Could not create logs directory', E_USER_ERROR);
}
if (!file_exists('logs/dynamicsuite-error.log') && !touch('logs/dynamicsuite-error.log')) {
    trigger_error('Could not create error log', E_USER_ERROR);
}
if (!file_exists('logs/dynamicsuite-access.log') && !touch('logs/dynamicsuite-access.log')) {
    trigger_error('Could not create access log', E_USER_ERROR);
}

// Core autoloader
spl_autoload_register(function ($class) {
    @include_once 'lib/' . str_replace('\\', '/', $class) . '.php';
});