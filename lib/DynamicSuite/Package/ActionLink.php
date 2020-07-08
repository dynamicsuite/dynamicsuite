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
use DynamicSuite\Util\Format;
use Exception;

/**
 * Class ActionLink.
 *
 * @package DynamicSuite\Package
 * @property string $link_id
 * @property string $package_id
 * @property string|null $type
 * @property string|null $value
 * @property string|null $group
 * @property string[] $permissions
 */
final class ActionLink
{

    /**
     * Link ID.
     *
     * @var string
     */
    protected string $link_id;

    /**
     * Associated package ID.
     *
     * @var string
     */
    protected string $package_id;

    /**
     * Action link type.
     *
     * Static or Dynamic.
     *
     * @var string|null
     */
    protected ?string $type = null;

    /**
     * Action link value.
     *
     * URL path or script path.
     *
     * @var string|null
     */
    protected ?string $value = null;

    /**
     * Action link grouping (if any).
     *
     * @var string|null
     */
    protected ?string $group = null;

    /**
     * Permissions for the action link to render.
     *
     * @var string[]
     */
    protected array $permissions = [];

    /**
     * ActionLink constructor.
     *
     * @param string $link_id
     * @param string $package_id
     * @param array $structure
     * @return void
     * @throws Exception
     */
    public function __construct(string $link_id, string $package_id, array $structure)
    {
        $this->link_id = $link_id;
        $this->package_id = $package_id;
        $error = function(string $key, string $message): string {
            return "[Structure] Package \"$this->package_id\" action link \"$this->link_id\" key \"$key\": $message";
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
        if ($this->type !== 'static' && $this->type !== 'dynamic') {
            throw new Exception($error('type', 'must be "static" or "dynamic"'));
        }
        if ($this->type === 'dynamic') {
            $this->value = Format::formatServerPath($this->package_id, $this->value);
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