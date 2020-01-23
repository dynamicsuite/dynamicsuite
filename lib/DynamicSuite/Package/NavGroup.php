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
use DynamicSuite\Data\Permission;

/**
 * Class NavGroup.
 *
 * @package DynamicSuite\Package
 * @property string $group_id
 * @property string $name
 * @property string $icon
 * @property bool $public
 * @property Permission[] $permissions
 */
final class NavGroup extends ArrayConvertible
{

    /**
     * Navigation group ID.
     *
     * @var string
     */
    protected string $group_id;

    /**
     * The navigation group display name.
     *
     * @var string
     */
    protected string $name;

    /**
     * The navigation group icon.
     *
     * @var string
     */
    protected string $icon = 'fas fa-cogs';

    /**
     * Set the group public state.
     *
     * @var bool
     */
    protected bool $public = false;

    /**
     * Set the group permissions.
     *
     * @var Permission[]
     */
    protected array $permissions = [];

    /**
     * PackageNavGroup constructor.
     *
     * @param string $group_id
     * @param array $structure
     * @return void
     */
    public function __construct(string $group_id, array $structure = [])
    {
        parent::__construct($structure);
        $this->group_id = $group_id;
        $this->name = $this->name ?? $group_id;
    }

}