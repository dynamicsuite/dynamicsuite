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
use Exception;

/**
 * Class ActionLink.
 *
 * @package DynamicSuite\NavGroup
 * @property string $group_id
 * @property string $package_id
 * @property string|null $name
 * @property string $icon
 * @property bool $public
 * @property string[] $permissions
 * @property View[] $views
 */
final class NavGroup
{

    /**
     * Group ID.
     *
     * @var string
     */
    protected string $group_id;

    /**
     * Associated package ID.
     *
     * @var string
     */
    protected string $package_id;

    /**
     * Nav group name.
     *
     * @var string|null
     */
    protected ?string $name = null;

    /**
     * Nav group FontAwesome class.
     *
     * @var string
     */
    protected string $icon = 'fas fa-cogs';

    /**
     * Nav group public state (rendering permissions).
     *
     * @var bool
     */
    protected bool $public = false;

    /**
     * Permissions for the action link to render.
     *
     * @var string[]
     */
    protected array $permissions = [];

    /**
     * Views array for navigation building.
     *
     * @var View[]
     */
    public array $views = [];

    /**
     * NavGroup constructor.
     *
     * @param string $group_id
     * @param string $package_id
     * @param array $structure
     * @return void
     * @throws Exception
     */
    public function __construct(string $group_id, string $package_id, array $structure)
    {
        $this->group_id = $group_id;
        $this->package_id = $package_id;
        $error = function(string $key, string $message): string {
            return "[Structure] Package \"$this->package_id\" nav group \"$this->group_id\" key \"$key\": $message";
        };
        foreach ($structure as $prop => $value) {
            if ($prop === 'permissions') {
                if ($value === null) {
                    $value = [];
                } elseif (is_array($value)) {
                    foreach ($value as $permission) {
                        if (!is_string($permission)) {
                            throw new Exception($error('permissions', 'must be a string or array of strings'));
                        }
                    }
                } else {
                    throw new Exception($error('permissions', 'must be a string or array of strings'));
                }
            }
            if (property_exists($this, $prop)) {
                $this->$prop = $value;
            }
        }
        if ($this->name === null) {
            throw new Exception($error('name', 'missing'));
        }
    }

    /**
     * Parameter getter magic method.
     *
     * @param string $property
     * @return mixed
     */
    public function __get(string $property)
    {
        return $this->$property;
    }

}