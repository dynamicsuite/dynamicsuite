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

require_once realpath(__DIR__ . '/create_environment.php');

$cfg = new Config('dynamicsuite');
$host = CLI::splitDSN($cfg->db_dsn, 'host');
$db_name = CLI::splitDSN($cfg->db_dsn, 'dbname');

// 3.1.0 Database Changes
if (version_compare(DS_VERSION, '3.1.0') === -1) {
    CLI::out('Updating Tables...   3.1.0');
    $sql = realpath(__DIR__ . '/../sql/3.1.0_changes.sql');
    $err = exec(
        "mysql " .
        "--user=\"{$cfg->db_user}\" " .
        "--password=\"{$cfg->db_pass}\" " .
        "--host=\"$host\" " .
        "--database=\"$db_name\" " .
        "< \"$sql\"");
    if ($err) CLI::err($err);
}

// 3.1.1 Database Changes
if (version_compare(DS_VERSION, '3.1.1') === -1) {
    CLI::out('Updating Tables...   3.1.1');
    $sql = realpath(__DIR__ . '/../sql/3.1.1_changes.sql');
    $err = exec(
        "mysql " .
        "--user=\"{$cfg->db_user}\" " .
        "--password=\"{$cfg->db_pass}\" " .
        "--host=\"$host\" " .
        "--database=\"$db_name\" " .
        "< \"$sql\"");
    if ($err) CLI::err($err);
}

// 3.4.0 Database Changes
if (version_compare(DS_VERSION, '3.4.0') === -1) {
    CLI::out('Updating Tables...   3.4.0');
    $sql = realpath(__DIR__ . '/../sql/3.4.0_changes.sql');
    $err = exec(
        "mysql " .
        "--user=\"{$cfg->db_user}\" " .
        "--password=\"{$cfg->db_pass}\" " .
        "--host=\"$host\" " .
        "--database=\"$db_name\" " .
        "< \"$sql\"");
    if ($err) CLI::err($err);
}