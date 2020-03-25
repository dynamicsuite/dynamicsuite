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

namespace DynamicSuite\Data;
use DynamicSuite\Base\DatabaseItem;

/**
 * Class Permission.
 *
 * @package DynamicSuite\Data
 * @property int|null $permission_id
 * @property string|null $package_id
 * @property string|null $name
 * @property string|null $domain
 * @property string|null $description
 * @property string|null $created_on
 */
class Permission extends DatabaseItem
{

    /**
     * The permission ID.
     *
     * @var int|null
     */
    public ?int $permission_id = null;

    /**
     * Package ID associated with the permission.
     *
     * @var string|null
     */
    public ?string $package_id = null;

    /**
     * The name of the permission.
     *
     * @var string|null
     */
    public ?string $name = null;

    /**
     * Permission domain.
     *
     * @var string|null
     */
    public ?string $domain = null;

    /**
     * A brief description of the permission.
     *
     * @var string|null
     */
    public ?string $description = null;

    /**
     * The timestamp when the permission was created.
     *
     * @var string|null
     */
    public ?string $created_on = null;

    /**
     * Get the shorthand format of the permission.
     *
     * @return string
     */
    public function shorthand(): string
    {
        return "$this->package_id:$this->name";
    }

    /**
     * Permission constructor.
     *
     * @param array $permission
     * @return void
     */
    public function __construct(array $permission = [])
    {
        parent::__construct($permission);
    }

}