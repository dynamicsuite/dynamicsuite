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
use TypeError;

/**
 * Class PackageResources.
 *
 * @package DynamicSuite
 * @property string $package_id
 * @property array $autoload
 * @property array $init
 * @property array $js
 * @property array $css
 */
class PackageResources extends ProtectedObject
{

    /**
     * Package ID.
     *
     * @var string
     */
    protected $package_id;

    /**
     * Array of autoload paths.
     *
     * @var array
     */
    protected $autoload = [];

    /**
     * Array of server-side init script paths.
     *
     * @var array
     */
    protected $init = [];

    /**
     * Array of client-side script paths.
     *
     * @var array
     */
    protected $js = [];

    /**
     * Array of stylesheet paths.
     *
     * @var array
     */
    protected $css = [];

    /**
     * PackageResources constructor.
     *
     * @param string|null $package_id
     * @return void
     */
    public function __construct(string $package_id = null)
    {
        $this->package_id = $package_id;
    }

    /**
     * Set autoload paths.
     *
     * @param string|array $paths
     * @return PackageResources
     */
    public function setAutoload($paths): PackageResources
    {
        return $this->setResource('autoload', $paths);
    }

    /**
     * Set init scripts.
     *
     * @param string|array $paths
     * @return PackageResources
     */
    public function setInit($paths): PackageResources
    {
        return $this->setResource('init', $paths);
    }

    /**
     * Set JS scripts.
     *
     * @param string|array $paths
     * @return PackageResources
     */
    public function setJs($paths): PackageResources
    {
        return $this->setResource('js', $paths);
    }

    /**
     * Set CSS resources.
     *
     * @param string|array $paths
     * @return PackageResources
     */
    public function setCss($paths): PackageResources
    {
        return $this->setResource('css', $paths);
    }

    /**
     * Merge foreign package resources with the current instance.
     *
     * @param PackageResources $resources
     * @param bool $invert
     * @return PackageResources
     */
    public function merge(PackageResources $resources, bool $invert = false): PackageResources
    {
        if (!$invert) {
            $this->autoload = array_unique(array_merge($this->autoload, $resources->autoload));
            $this->init = array_unique(array_merge($this->init, $resources->init));
            $this->js = array_unique(array_merge($this->js, $resources->js));
            $this->css = array_unique(array_merge($this->css, $resources->css));
        } else {
            $this->autoload = array_unique(array_merge($resources->autoload, $this->autoload));
            $this->init = array_unique(array_merge($resources->init, $this->init));
            $this->js = array_unique(array_merge($resources->js, $this->js));
            $this->css = array_unique(array_merge($resources->css, $this->css));
        }
        return $this;
    }

    /**
     * Add a resource path.
     *
     * @param string $type
     * @param string $path
     * @param string $package_id
     * @return PackageResources
     */
    public function addResource(string $type, string $path, string $package_id = null): PackageResources
    {
        $package_id = $package_id ?? $this->package_id;
        $format = $type === 'autoload' || $type === 'init' ? 'formatServerPath' : 'formatClientPath';
        $path = Package::$format($package_id, $path);
        if (!in_array($path, $this->$type)) $this->$type[] = $path;
        return $this;
    }

    /**
     * Set resource path(s).
     *
     * @param string $type
     * @param array|string $paths
     * @return PackageResources
     * @throws TypeError
     */
    public function setResource(string $type, $paths): PackageResources
    {
        $this->$type = [];
        if (is_array($paths)) {
            foreach ($paths as $path) $this->addResource($type, $path);
        } elseif (is_string($paths)) {
            $this->addResource($type, $paths);
        } else {
            throw new TypeError("Invalid resource path type in structure file ({$this->package_id}:$type)");
        }
        return $this;
    }

}