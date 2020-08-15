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

namespace DynamicSuite\Storable;
use DynamicSuite\Core\Session;
use DynamicSuite\Database\Query;
use Exception;
use PDOException;

/**
 * Class Group.
 *
 * @package DynamicSuite\Storable
 * @property int|null $group_id
 * @property string|null $name
 * @property string|null $description
 * @property string|null $domain
 * @property string|null $created_by
 * @property string|null $created_on
 */
class Group extends Storable implements IStorable
{

    /**
     * Column length limits.
     *
     * @var int[]
     */
    public const COLUMN_LIMITS = [
        'name' => 64,
        'description' => 64,
        'domain' => 64,
        'created_by' => 254
    ];

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
     * Group domain.
     *
     * @var string|null
     */
    public ?string $domain = null;

    /**
     * Group creation source.
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

    /**
     * Create the group in the database.
     *
     * @return Group
     * @throws Exception|PDOException
     */
    public function create(): Group
    {
        $this->created_by = $this->created_by ?? Session::$user_name;
        $this->created_on = date('Y-m-d H:i:s');
        $this->validate(self::COLUMN_LIMITS);
        $this->group_id = (new Query())
            ->insert([
                'name' => $this->name,
                'description' => $this->description,
                'domain' => $this->domain,
                'created_by' => $this->created_by,
                'created_on' => $this->created_on
            ])
            ->into('ds_groups')
            ->execute();
        return $this;
    }

    /**
     * Attempt to read a group by ID.
     *
     * Returns the Group if found, or FALSE if not found.
     *
     * @param int|null $id
     * @return bool|Group
     * @throws Exception|PDOException
     */
    public static function readById(?int $id = null)
    {
        if ($id === null) {
            return false;
        }
        $group = (new Query())
            ->select()
            ->from('ds_groups')
            ->where('group_id', '=', $id)
            ->execute(true);
        return $group ? new Group($group) : false;
    }

    /**
     * Attempt to read a group by name for a specific domain.
     *
     * Returns the Group if found, or FALSE if not found.
     *
     * @param string $name
     * @param string $domain
     * @return bool|Group
     * @throws Exception|PDOException
     */
    public static function readByName(string $name, string $domain)
    {
        $group = (new Query())
            ->select()
            ->from('ds_groups')
            ->where('name', '=', $name)
            ->where('domain', '=', $domain)
            ->execute(true);
        return $group ? new Group($group) : false;
    }

    /**
     * Update the group in the database.
     *
     * @return Group
     * @throws Exception|PDOException
     */
    public function update(): Group
    {
        $this->validate(self::COLUMN_LIMITS);
        (new Query())
            ->update('ds_groups')
            ->set([
                'name' => $this->name,
                'description' => $this->description,
                'domain' => $this->domain
            ])
            ->where('group_id', '=', $this->group_id)
            ->execute();
        return $this;
    }

    /**
     * Update the permissions for the group.
     *
     * Permissions is an array of integers, where each integer matches the permission ID of the permission.
     *
     * This method contains multiple queries and should be run inside of a transaction.
     *
     * @param int[] $permissions
     * @return Group
     * @throws PDOException|Exception
     */
    public function updatePermissions(array $permissions): Group
    {
        $insert = [];
        foreach ($permissions as $permission_id) {
            $insert[] = [
                'group_id' => $this->group_id,
                'permission_id' => $permission_id
            ];
        }
        (new Query())
            ->delete()
            ->from('ds_groups_permissions')
            ->where('group_id', '=', $this->group_id)
            ->execute();
        (new Query())
            ->insert($insert)
            ->into('ds_groups_permissions')
            ->execute();
        return $this;
    }

    /**
     * Delete the group from the database.
     *
     * @return Group
     * @throws Exception|PDOException
     */
    public function delete(): Group
    {
        (new Query())
            ->delete()
            ->from('ds_groups')
            ->where('group_id', '=', $this->group_id)
            ->execute();
        return $this;
    }

}