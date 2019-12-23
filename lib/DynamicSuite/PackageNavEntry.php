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
 * Class PackageNavGroup.
 *
 * @package DynamicSuite
 * @property string $name
 * @property string $icon
 * @property string $uri_path
 * @property bool $public
 * @property Permission[] $permissions
 * @property PackageNavEntry[] $children
 */
class PackageNavEntry extends ProtectedObject
{

    /**
     * Entry name (on navigation bar).
     *
     * @var string
     */
    protected $name;

    /**
     * Entry Font Awesome css icon class.
     *
     * @var string
     */
    protected $icon;

    /**
     * URI path for the entry.
     *
     * @var string
     */
    protected $uri_path;

    /**
     * Nav entry public state.
     *
     * @var bool
     */
    protected $public;

    /**
     * Set the entry permissions.
     *
     * @var Permission[]
     */
    protected $permissions;

    /**
     * Array of navigation entries.
     *
     * @var PackageNavEntry[]
     */
    protected $children = [];

    /**
     * Set the entry name.
     *
     * @param string $name
     * @return PackageNavEntry
     */
    public function setName(string $name): PackageNavEntry
    {
        $this->name = $name;
        return $this;
    }

    /**
     * Set the entry Font Awesome css icon class.
     *
     * @param string $icon
     * @return PackageNavEntry
     */
    public function setIcon(string $icon): PackageNavEntry
    {
        $this->icon = $icon;
        return $this;
    }

    /**
     * Set the nav entry URI path.
     *
     * @param string $uri_path
     * @return PackageNavEntry
     */
    public function setUriPath(string $uri_path): PackageNavEntry
    {
        $this->uri_path = $uri_path;
        return $this;
    }

    /**
     * Set the public state.
     *
     * @param bool|null $state
     * @return PackageNavEntry
     */
    public function setPublicState(bool $state = null): PackageNavEntry
    {
        $this->public = $state ?? false;
        return $this;
    }

    /**
     * Set the entry permissions.
     *
     * @param array|null $permissions
     * @return PackageNavEntry
     */
    public function setPermissions(array $permissions = null): PackageNavEntry
    {
        $this->permissions = $permissions;
        return $this;
    }

    /**
     * Add a child navigation entry.
     *
     * @param PackageNavEntry $child
     * @return PackageNavEntry
     */
    public function addChild(PackageNavEntry $child): PackageNavEntry
    {
        $this->children[] = $child;
        return $this;
    }

    /**
     * Check to see if the entry has children.
     *
     * @return bool
     */
    public function hasChildren(): bool
    {
        return !empty($this->children);
    }

}