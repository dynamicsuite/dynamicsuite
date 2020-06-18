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

/** @noinspection PhpUnused */

namespace DynamicSuite\Core;
use DynamicSuite\Database\Database;
use DynamicSuite\Package\Packages;
use PDOException;

/**
 * Class Instance.
 *
 * @package DynamicSuite\Core
 */
final class DynamicSuite
{

    /**
     * Global configuration.
     *
     * @var Config
     */
    public static Config $cfg;

    /**
     * Database connection.
     *
     * @var Database
     */
    public static Database $db;

    /**
     * Initialize Dynamic Suite.
     *
     * @return void
     * @throws PDOException
     */
    public static function init(): void
    {
        $hash = md5(__DIR__);
        if (DS_CACHING && apcu_exists($hash) && $cache = apcu_fetch($hash)) {
            self::$cfg = $cache['cfg'];
        } else {
            self::$cfg = new Config('dynamicsuite');
            if (DS_CACHING) {
                $store = apcu_store($hash, [
                    'cfg' => self::$cfg
                ]);
                if (!$store) {
                    error_log('Error saving `DynamicSuite` in cache, check server config');
                }
            }
        }
        self::$db = new Database(
            self::$cfg->db_dsn,
            self::$cfg->db_user,
            self::$cfg->db_pass,
            self::$cfg->db_options
        );
        if (self::$cfg->debug_mode) {
            define('DS_DEBUG_MODE', true);
        }
        Packages::init();
    }

    /**
     * Instance constructor.
     *
     * @return void
     */
    public static function todo()
    {
        //$this->packages = new Packages($this);
        //$this->cache = new Cache($this);
        //$this->permissions = new Permissions($this);
        //$this->groups = new Groups($this);
        //$this->users = new Users($this);
        //$this->events = new Events($this);
        //$this->properties = new Properties($this);
        //$this->save();
    }

}