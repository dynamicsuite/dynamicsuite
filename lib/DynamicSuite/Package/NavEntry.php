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
use DynamicSuite\Data\Permission;

/**
 * Class NavEntry.
 *
 * @package DynamicSuite\Package
 * @property string $name
 * @property string $icon
 * @property string $url
 * @property bool $public
 * @property Permission[] $permissions
 * @property NavEntry[] $children
 */
final class NavEntry
{

    /**
     * Entry name (on navigation bar).
     *
     * @var string
     */
    public string $name;

    /**
     * Entry Font Awesome css icon class.
     *
     * @var string
     */
    public string $icon = 'fas fa-cogs';

    /**
     * URL path for the entry.
     *
     * @var string
     */
    public string $url;

    /**
     * Nav entry public state.
     *
     * @var bool
     */
    public bool $public = false;

    /**
     * Set the entry permissions.
     *
     * @var Permission[]
     */
    public ?array $permissions = null;

    /**
     * Array of navigation entries.
     *
     * @var NavEntry[]
     */
    public array $children = [];

    /**
     * Add a child navigation entry.
     *
     * @param NavEntry $child
     * @return NavEntry
     */
    public function addChild(NavEntry $child): NavEntry
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