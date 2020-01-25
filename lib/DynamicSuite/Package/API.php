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

namespace DynamicSuite\Package;
use DynamicSuite\Base\ArrayConvertible;

/**
 * Class API.
 *
 * @package DynamicSuite\Package
 * @property string $package_id
 * @property string $api_id
 * @property string $entry
 * @property array $post
 * @property mixed $permissions
 * @property bool $public
 * @property Resources $resources
 */
final class API extends ArrayConvertible
{

    /**
     * The package ID the package belongs to.
     *
     * @var string
     */
    public string $package_id;

    /**
     * Set the API endpoint ID.
     *
     * @var string
     */
    public string $api_id;

    /**
     * Api entry point script path.
     *
     * @var string
     */
    public string $entry;

    /**
     * Api required post keys.
     *
     * @var array
     */
    public array $post = [];

    /**
     * Api required permissions.
     *
     * @var array|string|null
     */
    public $permissions = null;

    /**
     * Api public state.
     *
     * @var bool
     */
    public bool $public = false;

    /**
     * API package resources.
     *
     * Note: APIs are a server-side construct, the $js and $css properties of
     * this object are ignored at runtime.
     *
     * @var Resources
     */
    protected Resources $resources;

    /**
     * PackageApi constructor.
     *
     * @param string $package_id
     * @param string $api_id
     * @param array $structure
     * @return void
     */
    public function __construct(string $package_id, string $api_id, array $structure = [])
    {
        parent::__construct($structure);
        $this->package_id = $package_id;
        $this->api_id = $api_id;
        $this->resources = new Resources($this->package_id);
        $this->setResources($structure);
    }

    /**
     * Set the resources for the API.
     *
     * Note: APIs are a server-side construct, the $js and $css properties of
     * this object are ignored at runtime.
     *
     * @param array $structure
     * @return API
     */
    public function setResources(array $structure = []): API
    {
        $this->resources->setAutoload($structure['autoload'] ?? []);
        $this->resources->setInit($structure['init'] ?? []);
        return $this;
    }

}