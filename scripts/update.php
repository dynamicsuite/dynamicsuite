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
use DynamicSuite\Core\Config;
use DynamicSuite\Util\CLI;

set_time_limit(0);
ini_set('memory_limit', -1);

/**
 * Change directory
 */
chdir(__DIR__ . '/..');

/**
 * Override for DS classes
 */
define('DS_CACHING', false);
define('DS_ROOT_DIR', getcwd());

require_once DS_ROOT_DIR  . '/lib/DynamicSuite/Util/CLI.php';
require_once DS_ROOT_DIR  . '/lib/DynamicSuite/Core/GlobalConfig.php';
require_once DS_ROOT_DIR  . '/lib/DynamicSuite/Core/Config.php';

/**
 * Load config
 */
$cfg = new Config('dynamicsuite');
$db_host = CLI::splitDSN($cfg->db_dsn, 'host');
$db_name = CLI::splitDSN($cfg->db_dsn, 'dbname');

CLI::out('Creating tables...');
$db_err = exec(
    "mysql " .
    "--user=\"$cfg->db_user\" " .
    "--password=\"$cfg->db_pass\" " .
    "--host=\"$db_host\" " .
    "--database=\"$db_name\" " .
    "< \"sql/create_tables.sql\""
);

CLI::out('Updating tables...');
$db_err = exec(
    "mysql " .
    "--user=\"$cfg->db_user\" " .
    "--password=\"$cfg->db_pass\" " .
    "--host=\"$db_host\" " .
    "--database=\"$db_name\" " .
    "< \"sql/update.sql\""
);
if ($db_err) {
    CLI::err('Error updating tables!', false);
    CLI::err($db_err);
}