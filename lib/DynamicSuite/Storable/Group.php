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
     * @param string|null $domain
     * @return bool|Group
     * @throws Exception|PDOException
     */
    public static function readByName(string $name, ?string $domain = null)
    {
        $group = (new Query())
            ->select()
            ->from('ds_groups')
            ->where('name', '=', $name)
            ->where('domain', $domain ? '=' : 'IS', $domain)
            ->execute(true);
        return $group ? new Group($group) : false;
    }

    /**
     * Read all of the groups for the given domain.
     *
     * This method returns a regular array for use in API responses and direct manipulation.
     *
     * Which columns are returned on each row (group) can be set with the $columns parameter.
     *
     * @param string|null $domain
     * @param string[] $columns
     * @return array
     * @throws PDOException|Exception
     */
    public static function readAvailable(?string $domain, array $columns = ['*']): array
    {
        return (new Query())
            ->select($columns)
            ->from('ds_groups')
            ->where('domain', $domain ? '=' : 'IS', $domain)
            ->execute();
    }

    /**
     * Read all of the permissions for the group.
     *
     * This method returns a regular array for use in API responses and direct manipulation.
     *
     * Which columns are returned on each row (permission) can be set with the $columns parameter.
     *
     * @param array|string[] $columns
     * @return array
     * @throws PDOException|Exception
     */
    public function readPermissions(array $columns = ['ds_groups.permission_id', 'ds_groups.name']): array
    {
        return (new Query())
            ->select($columns)
            ->from('ds_groups_permissions')
            ->join('ds_permissions')
            ->on('ds_permissions.permission_id', '=', 'ds_groups_permissions.permission_id')
            ->where('ds_groups_permissions.group_id', '=', $this->group_id)
            ->execute();
    }

    /**
     * Read the permission groups for the given domain and the given user ID.
     *
     * The result array contains a list of assigned and unassigned groups.
     *
     * This method returns a regular array for use with client components.
     *
     * @param string $domain
     * @param int|null $user_id
     * @return array[]
     * @throws PDOException|Exception
     */
    public static function readForComponent(string $domain, ?int $user_id = null): array
    {
        $groups = [
            'assigned' => [],
            'unassigned' => []
        ];
        $assigned = [];
        if ($user_id) {
            $assigned = (new Query())
                ->select(['ds_groups.group_id', 'ds_groups.name'])
                ->from('ds_users_groups')
                ->join('ds_groups')
                ->on('ds_groups.group_id', '=', 'ds_users_groups.group_id')
                ->where('ds_groups.domain', '=', $domain)
                ->where('ds_users_groups.user_id', '=', $user_id)
                ->execute();
        }
        $unassigned = (new Query())
            ->select(['group_id', 'name'])
            ->from('ds_groups')
            ->where('domain', '=', $domain)
            ->execute();
        foreach ($unassigned as $value) {
            $groups['unassigned'][$value['group_id']] = $value['name'];
        }
        foreach ($assigned as $value) {
            unset($groups['unassigned'][$value['group_id']]);
            $groups['assigned'][$value['group_id']] = $value['name'];
        }
        return $groups;
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

    /**
     * Validate a list of groups (ids) for the given domain.
     *
     * @param int[] $groups
     * @param string $domain
     * @throws Exception
     */
    public static function validateForDomain(array $groups, string $domain): void
    {
        foreach ($groups as $group_id) {
            $group = (new Query())
                ->select(['group_id'])
                ->from('ds_groups')
                ->where('group_id', '=', $group_id)
                ->where('domain', '=', $domain)
                ->execute(true);
            if (!$group) {
                throw new Exception('Trying to add a group out of domain');
            }
        }
    }

}