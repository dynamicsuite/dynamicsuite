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
 * @property string $id
 * @property string $name
 * @property string $icon
 * @property bool $public
 * @property Permission[] $permissions
 */
class PackageNavGroup extends ProtectedObject
{

    /**
     * Navigation group ID.
     *
     * @var string
     */
    protected $id;

    /**
     * The navigation group display name.
     *
     * @var string
     */
    protected $name;

    /**
     * The navigation group icon.
     *
     * @var string
     */
    protected $icon;

    /**
     * Set the group public state.
     *
     * @var bool
     */
    protected $public;

    /**
     * Set the group permissions.
     *
     * @var Permission[]
     */
    protected $permissions;

    /**
     * PackageNavGroup constructor.
     *
     * @param string $id
     * @return void
     */
    public function __construct(string $id)
    {
        $this->setId($id);
    }

    /**
     * Set the navigation group ID.
     *
     * @param string $id
     * @return PackageNavGroup
     */
    public function setId(string $id): PackageNavGroup
    {
        $this->id = $id;
        return $this;
    }

    /**
     * Set the group display name.
     *
     * @param string|null $name
     * @return PackageNavGroup
     */
    public function setName(string $name = null): PackageNavGroup
    {
        $this->name = $name ?? $this->id;
        return $this;
    }

    /**
     * Set the group icon.
     *
     * @param string|null $icon
     * @return PackageNavGroup
     */
    public function setIcon(string $icon = null): PackageNavGroup
    {
        $this->icon = $icon ?? 'fas fa-cogs';
        return $this;
    }

    /**
     * Set the group public state.
     *
     * @param bool|null $state
     * @return PackageNavGroup
     */
    public function setPublicState(bool $state = null): PackageNavGroup
    {
        $this->public = $state ?? false;
        return $this;
    }

    /**
     * Set the group permissions.
     *
     * @param array|null $permissions
     * @return PackageNavGroup
     */
    public function setPermissions(array $permissions = null): PackageNavGroup
    {
        $this->permissions = $permissions;
        return $this;
    }

}