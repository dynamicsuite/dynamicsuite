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
use stdClass;

/**
 * Class PackageApi.
 *
 * @package DynamicSuite
 * @property string $package_id
 * @property string $api_id
 * @property string $entry_point
 * @property array $post
 * @property mixed $permissions
 * @property bool $public
 * @property PackageResources $resources
 */
class PackageApi extends ProtectedObject
{

    /**
     * The package ID the package belongs to.
     *
     * @var string
     */
    protected $package_id;

    /**
     * Set the API endpoint ID.
     *
     * @var string
     */
    protected $api_id;

    /**
     * Api entry point script path.
     *
     * @var string
     */
    protected $entry_point;

    /**
     * Api required post keys.
     *
     * @var array
     */
    protected $post = [];

    /**
     * Api required permissions.
     *
     * @var mixed
     */
    protected $permissions;

    /**
     * Api public state.
     *
     * @var bool
     */
    protected $public;

    /**
     * API package resources.
     *
     * Note: APIs are a server-side construct, the $js and $css properties of
     * this object are ignored at runtime.
     *
     * @var PackageResources
     */
    protected $resources;

    /**
     * Set the package ID.
     *
     * @param string $package_id
     * @return PackageApi
     */
    public function setPackageId(string $package_id): PackageApi
    {
        $this->package_id = $package_id;
        return $this;
    }

    /**
     * Set the API ID.
     *
     * @param string $api_id
     * @return PackageApi
     */
    public function setApiId(string $api_id): PackageApi
    {
        $this->api_id = $api_id;
        return $this;
    }

    /**
     * Set the entry point script for the API endpoint.
     *
     * @param string $entry_point
     * @return PackageApi
     */
    public function setEntryPoint(string $entry_point): PackageApi
    {
        $this->entry_point = Package::formatServerPath($this->package_id, $entry_point);
        return $this;
    }

    /**
     * Add a required post key to the API.
     *
     * @param string|null $post
     * @return PackageApi
     */
    public function addPost(string $post = null): PackageApi
    {
        if (!$post) return $this;
        if (!in_array($post, $this->post)) $this->post[] = $post;
        return $this;
    }

    /**
     * Set the post keys for the api.
     *
     * @param array|null $post
     * @return PackageApi
     */
    public function setPost(array $post = null): PackageApi
    {
        if ($post) foreach ($post as $key) $this->addPost($key);
        return $this;
    }

    /**
     * Add a required permission to the api.
     *
     * @param string|null $permission
     * @return PackageApi
     */
    public function addPermission(string $permission = null): PackageApi
    {
        if (!$permission) return $this;
        if (!$this->permissions) {
            $this->permissions[] = $permission;
        } elseif (!in_array($permission, $this->permissions)) {
            $this->permissions[] = $permission;
        }
        return $this;
    }

    /**
     * Set the required permissions for the api.
     *
     * @param array|null $permissions
     * @return PackageApi
     */
    public function setPermissions(array $permissions = null): PackageApi
    {
        if ($permissions) foreach ($permissions as $permission) $this->addPermission($permission);
        return $this;
    }

    /**
     * Set the api public state.
     *
     * @param bool|null $public
     * @return PackageApi
     */
    public function setPublicState(bool $public = null): PackageApi
    {
        $this->public = $public ?? false;
        return $this;
    }

    /**
     * Set the resources for the API.
     *
     * Note: APIs are a server-side construct, the $js and $css properties of
     * this object are ignored at runtime.
     *
     * @param stdClass $structure
     * @return PackageApi
     */
    public function setResources(stdClass $structure): PackageApi
    {
        $this->resources = new PackageResources($this->package_id);
        if (isset($structure->autoload)) $this->resources->setAutoload($structure->autoload);
        if (isset($structure->init)) $this->resources->setAutoload($structure->init);
        return $this;
    }

}