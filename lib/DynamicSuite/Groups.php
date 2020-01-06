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

namespace DynamicSuite;
use PDOException;

/**
 * Class Groups.
 *
 * @package DynamicSuite
 * @property Group[] $available
 */
class Groups extends InstanceMember
{

    /**
     * Array of available groups.
     *
     * @var array
     */
    protected $available;

    /**
     * Groups constructor.
     *
     * @param Instance $ds
     * @return void
     */
    public function __construct(Instance $ds)
    {
        parent::__construct($ds);
        try {
            $this->available = $this->getAll();
        } catch (PDOException $exception) {
            trigger_error($exception->getMessage(), E_USER_ERROR);
        }
    }

    /**
     * Get an array of all groups.
     *
     * @return Group[]
     * @throws PDOException
     */
    public function getAll(): array
    {
        $groups = [];
        $rows = $this->ds->db->query((new Query())->select()->from('ds_groups'));
        foreach ($rows as $row) {
            $group = new Group($row);
            $groups[$group->name] = $group;
        }
        return $groups;
    }

    /**
     * Attempt to find a group by name.
     *
     * @param string $name
     * @return Group|bool
     */
    public function find(string $name)
    {
        return $this->available[$name] ?? false;
    }

    /**
     * Create a group.
     *
     * @param Group $group
     * @return Group
     * @throws PDOException
     */
    public function create(Group $group): Group
    {
        $group->setCreatedOn(date('Y-m-d H:i:s'));
        if (isset($this->ds->session)) {
            $group->setCreatedBy($this->ds->session->user->username ?? null);
        } else {
            $group->setCreatedBy(null);
        }
        $group->validateForDatabase();
        $id = $this->ds->db->query((new Query())
            ->insert([
                'name' => $group->pending_name,
                'description' => $group->pending_description,
                'created_by' => $group->created_by,
                'created_on' => $group->created_on
            ])
            ->into('ds_groups')
        );
        $group->setId($id)->save();
        $this->available[$group->name] = $group;
        $this->ds->save();
        return $group;
    }

    /**
     * Modify a group.
     * 
     * @param Group $group
     * @return Group
     * @throws PDOException
     */
    public function modify(Group $group): Group
    {
        $group->validateForDatabase();
        $this->ds->db->query((new Query())
            ->update('ds_groups')
            ->set([
                'name' => $group->pending_name,
                'description' => $group->pending_description
            ])
            ->where('group_id', '=', $group->id)
        );
        if ($group->nameChanged()) {
            unset($this->available[$group->name]);
        }
        $group->save();
        $this->available[$group->name] = $group;
        $this->ds->save();
        return $group;
    }

    /**
     * Delete a group.
     * 
     * @param Group $group
     * @return Group
     * @throws PDOException
     */
    public function delete(Group $group): Group
    {
        $this->ds->db->query((new Query())
            ->delete()
            ->from('ds_groups')
            ->where('group_id', '=', $group->id)
        );
        unset($this->available[$group->name]);
        $this->ds->save();
        return $group;
    }

    /**
     * Add a permission to a group.
     *
     * @param Group $group
     * @param Permission $permission
     * @return Groups
     * @throws PDOException
     */
    public function addPermission(Group $group, Permission $permission): Groups
    {
        $this->ds->db->query((new Query())
            ->insert([
                'group_id' => $group->id,
                'permission_id' => $permission->id
            ])
            ->into('ds_group_permissions')
        );
        return $this;
    }

    /**
     * Remove a permission from a group.
     *
     * @param Group $group
     * @param Permission $permission
     * @return Groups
     * @throws PDOException
     */
    public function removePermission(Group $group, Permission $permission): Groups
    {
        $this->ds->db->query((new Query())
            ->delete()
            ->from('ds_group_permissions')
            ->where('group_id', '=', $group->id)
            ->where('permission_id', '=', $permission->id)
        );
        return $this;
    }

    /**
     * Replace the given groups permissions with the given array of permissions.
     *
     * @param Group $group
     * @param Permission[] $permissions
     * @return Groups
     * @throws PDOException
     */
    public function replacePermissions(Group $group, array $permissions): Groups
    {
        if (!$this->ds->db->startTx()) {
            throw new PDOException('Failed to start transaction');
        }
        $this->ds->db->query((new Query())
            ->delete()
            ->from('ds_group_permissions')
            ->where('group_id', '=', $group->id)
        );
        $insert = (new Query())->insert()->into('ds_group_permissions');
        $rows = [];
        /** @var Permission $permission */
        foreach ($permissions as $permission) {
            $rows[] = [
                'group_id' => $group->id,
                'permission_id' => $permission->id
            ];
        }
        if (!empty($rows)) $this->ds->db->query($insert->rows($rows));
        if (!$this->ds->db->endTx()) {
            throw new PDOException('Failed to complete transaction');
        }
        return $this;
    }

    /**
     * View the permissions for a given group.
     *
     * @param Group $group
     * @return Permission[]
     * @throws PDOException
     * @noinspection PhpUnused
     */
    public function viewPermissions(Group $group): array
    {
        $permissions = [];
        $rows = $this->ds->db->query((new Query())
            ->select()
            ->from('ds_view_group_permissions')
            ->where('group_id', '=', $group->id)
        );
        foreach ($rows as $row) {
            $permission = new Permission($row);
            $permissions[$permission->shorthand] = $permission;
        }
        return $permissions;
    }

}