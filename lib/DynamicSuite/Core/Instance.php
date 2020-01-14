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
use DynamicSuite\Base\ProtectedObject;
use DynamicSuite\Package\Bus;
use DynamicSuite\Package\Packages;
use DynamicSuite\Data\Events;
use DynamicSuite\Data\Groups;
use DynamicSuite\Data\Permissions;
use DynamicSuite\Data\Users;
use DynamicSuite\API\APIEndpoint;

/**
 * Class Instance.
 *
 * @package DynamicSuite\Core
 * @property Config $cfg
 * @property Request $request
 * @property Packages $packages
 * @property APIEndpoint $api
 * @property View $view
 * @property Database $db
 * @property Events $events
 * @property Permissions $permissions
 * @property Groups $groups
 * @property Users $users
 * @property Bus $pkg
 * @property Session $session
 */
class Instance extends ProtectedObject
{

    /**
     * Config container.
     *
     * @var Config
     */
    protected Config $cfg;

    /**
     * Request container.
     *
     * @var Request
     */
    protected Request $request;

    /**
     * Package container.
     *
     * @var Packages
     */
    protected Packages $packages;

    /**
     * API container.
     *
     * @var APIEndpoint
     */
    protected APIEndpoint $api;

    /**
     * View container.
     *
     * @var View
     */
    protected View $view;

    /**
     * Database container.
     *
     * @var Database
     */
    protected Database $db;

    /**
     * Events container.
     *
     * @var Events
     */
    protected Events $events;

    /**
     * Permissions container.
     *
     * @var Permissions
     */
    protected Permissions $permissions;

    /**
     * Groups container.
     *
     * @var Groups
     */
    protected Groups $groups;

    /**
     * Users container.
     *
     * @var Users
     */
    protected Users $users;

    /**
     * An array of user-defined package classes.
     *
     * @var array
     */
    protected $pkg;

    /**
     * Instance constructor.
     *
     * @return void
     */
    public function __construct()
    {
        $this
            ->registerGlobal('cfg', new Config('dynamicsuite'))
            ->registerGlobal('request', new Request($this))
            ->registerGlobal('packages', new Packages($this))
            ->registerGlobal('api', new APIEndpoint($this))
            ->registerGlobal('view', new View($this));
        $this->registerGlobal('db', new Database(
            $this->cfg->db_dsn,
            $this->cfg->db_user,
            $this->cfg->db_pass,
            $this->cfg->db_options
        ));
        if (!$this->db->connect()) trigger_error('Cannot load without a valid database connection', E_USER_ERROR);
        $this
            ->registerGlobal('events', new Events($this))
            ->registerGlobal('permissions', new Permissions($this))
            ->registerGlobal('groups', new Groups($this))
            ->registerGlobal('users', new Users($this));
        $this->pkg = new Bus();
        if (DS_APCU) $this->save();
    }

    /**
     * Check if a global class is registered.
     *
     * @param string $global
     * @return bool
     */
    public function globalIsRegistered(string $global): bool
    {
        return isset($this->$global);
    }

    /**
     * Register a global property.
     *
     * @param string $global
     * @param $value
     * @return Instance
     */
    public function registerGlobal(string $global, $value): Instance
    {
        $this->$global = $value;
        return $this;
    }

    /**
     * Check if a package class is registered.
     *
     * @param string $package_id
     * @return bool
     */
    public function packageIsRegistered(string $package_id): bool
    {
        return isset($this->pkg->$package_id);
    }

    /**
     * Register a package class to the package bus.
     *
     * @param string $package_id
     * @param mixed $value
     * @param bool $save
     * @param bool $override
     * @return Instance
     */
    public function registerPackage(string $package_id, $value, bool $save = true, bool $override = false): Instance
    {
        if ($this->packageIsRegistered($package_id) && !$override) return $this;
        $this->pkg->$package_id = $value;
        if ($save) $this->save();
        return $this;
    }

    /**
     * Save the Dynamic Suite instance.
     *
     * @return void
     */
    public function save()
    {
        if ($this->globalIsRegistered('session')) {
            $session = $this->session;
            unset($this->session);
        } else {
            $session = new Session($this);
        }
        if (DS_APCU) apcu_store(self::getVHostHash(), $this);
        $this->registerGlobal('session', $session);
    }

    /**
     * Get the hash for the current virtual host.
     *
     * @return string
     */
    public static function getVHostHash(): string
    {
        return md5(DS_ROOT_DIR);
    }

}