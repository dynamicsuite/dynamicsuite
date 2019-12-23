<?php
/*
 * Dynamic Suite
 * Copyright (C) 2019 Dynamic Suite Team
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

/**
 * Class Instance.
 *
 * @package DynamicSuite
 * @property Config $cfg
 * @property Request $request
 * @property Packages $packages
 * @property API $api
 * @property View $view
 * @property Database $db
 * @property Events $events
 * @property Permissions $permissions
 * @property Groups $groups
 * @property Users $users
 * @property array $pkg
 * @property Session $session
 */
class Instance extends ProtectedObject
{

    /**
     * Config container.
     *
     * @var Config
     */
    protected $cfg;

    /**
     * Request container.
     *
     * @var Request
     */
    protected $request;

    /**
     * Package container.
     *
     * @var Packages
     */
    protected $packages;

    /**
     * API container.
     *
     * @var APIRequest
     */
    protected $api;

    /**
     * View container.
     *
     * @var View
     */
    protected $view;

    /**
     * Database container.
     *
     * @var Database
     */
    protected $db;

    /**
     * Events container.
     *
     * @var Events
     */
    protected $events;

    /**
     * Permissions container.
     *
     * @var Permissions
     */
    protected $permissions;

    /**
     * Groups container.
     *
     * @var Groups
     */
    protected $groups;

    /**
     * Users container.
     *
     * @var Users
     */
    protected $users;

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
            ->registerGlobal('api', new API($this))
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
        if (DS_APCU) $this->save();
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
     * Register a package class to the package bus.
     *
     * @param string $package_id
     * @param $value
     * @return Instance
     */
    public function registerPackage(string $package_id, $value): Instance
    {
        $this->pkg[$package_id] = $value;
        return $this;
    }

    /**
     * Save the Dynamic Suite instance.
     *
     * @return void
     */
    public function save()
    {
        if (isset($this->session)) unset($this->session);
        if (DS_APCU) apcu_store('dynamicsuite', $this);
        $this->registerGlobal('session', new Session($this));
    }

}