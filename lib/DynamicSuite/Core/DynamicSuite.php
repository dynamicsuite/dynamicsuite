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
/** @noinspection PhpIncludeInspection */

namespace DynamicSuite\Core;
use DynamicSuite\API\APIEndpoint;
use DynamicSuite\Base\ProtectedObject;
use DynamicSuite\Data\Events;
use DynamicSuite\Data\Groups;
use DynamicSuite\Data\Permissions;
use DynamicSuite\Data\Users;
use DynamicSuite\Package\Packages;

/**
 * Class Instance.
 *
 * @package DynamicSuite\Core
 * @property Config $cfg
 * @property Packages $packages
 * @property Request $request
 * @property Session $session
 * @property View $view
 * @property APIEndpoint $api
 * @property Database $db
 * @property Cache $cache
 * @property Permissions $permissions
 * @property Groups $groups
 * @property Users $users
 * @property Events $events
 */
final class DynamicSuite extends ProtectedObject
{

    /**
     * Global configuration.
     *
     * @var Config
     */
    protected Config $cfg;

    /**
     * Loaded packages.
     *
     * @var Packages
     */
    protected Packages $packages;

    /**
     * Database connection.
     *
     * @var Database
     */
    protected Database $db;

    /**
     * Memcached wrapper.
     *
     * @var Cache
     */
    protected Cache $cache;

    /**
     * Permissions database interface.
     *
     * @var Permissions
     */
    protected Permissions $permissions;

    /**
     * Groups database interface.
     *
     * @var Groups
     */
    protected Groups $groups;

    /**
     * Users database interface.
     *
     * @var Users
     */
    protected Users $users;

    /**
     * Instance constructor.
     *
     * @return void
     */
    public function __construct()
    {
        $this->cfg = new Config('dynamicsuite');
        $this->packages = new Packages($this);
        $this->db = new Database(
            $this->cfg->db_dsn,
            $this->cfg->db_user,
            $this->cfg->db_pass,
            $this->cfg->db_options
        );
        $this->cache = new Cache($this);
        $this->permissions = new Permissions($this);
        $this->groups = new Groups($this);
        $this->users = new Users($this);
        $this->events = new Events($this);
        $this->save();
    }

    /**
     * Set a Dynamic Suite property.
     *
     * @param string $property
     * @param $value
     */
    public function set(string $property, $value): void
    {
        $this->$property = $value;
    }

    /**
     * Save the Dynamic Suite instance.
     *
     * @return void
     */
    public function save(): void
    {
        if (!DS_CACHING) return;
        $global_members = [];
        foreach ($this as $key => $value) {
            if (
                $key === 'cfg' ||
                $key === 'packages' ||
                $key === 'db' ||
                $key === 'cache' ||
                $key === 'permissions' ||
                $key === 'groups' ||
                $key === 'users' ||
                $key === 'events'
            ) continue;
            $global_members[$key] = $value;
            unset($this->$key);
        }
        apcu_store(self::getHash(), $this);
        foreach ($global_members as $key => $value) {
            $this->$key = $value;
        }
    }

    /**
     * Get a package class from the cache, or create a new one if not found.
     *
     * $class must be the class name with namespace.
     *
     * @param string $class
     * @param mixed $args
     * @return mixed|string
     */
    public static function getPkgClass(string $class, ...$args)
    {
        $hash = self::getHash($class);
        if (DS_CACHING && apcu_exists($hash) && $instance = apcu_fetch($hash)) {
            return $instance;
        } else {
            $instance = new $class(...$args);
            if (DS_CACHING) {
                apcu_store($hash, $instance);
            }
            return $instance;
        }
    }

    /**
     * Generate a hash for the given input string unique to the current instance.
     *
     * @param string $input
     * @return string
     */
    public static function getHash(string $input = '')
    {
        return md5(DS_ROOT_DIR . $input);
    }

}