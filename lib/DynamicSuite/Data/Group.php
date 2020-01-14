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
 * Class Group.
 *
 * @package DynamicSuite
 * @property int|null $group_id
 * @property string|null $name
 * @property string|null $description
 * @property string|null $created_by
 * @property string|null $created_on
 */
class Group extends DatabaseItem
{

    /**
     * Group ID.
     *
     * @var int|null
     */
    public ?int $group_id = null;

    /**
     * Group name.
     *
     * @var string|null
     */
    public ?string $name = null;

    /**
     * Group description.
     *
     * @var string|null
     */
    public ?string $description = null;

    /**
     * The user that created the group.
     *
     * @var string|null
     */
    public ?string $created_by = null;

    /**
     * The timestamp when the group was created.
     *
     * @var string|null
     */
    public ?string $created_on = null;

    /**
     * Group constructor.
     *
     * @param array $group
     * @return void
     */
    public function __construct(array $group = [])
    {
        parent::__construct($group);
    }

}