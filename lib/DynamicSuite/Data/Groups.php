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
use DynamicSuite\Base\InstanceMember;
use DynamicSuite\Core\DynamicSuite;
use DynamicSuite\Util\Query;
use Memcached;
use PDOException;

/**
 * Class Groups.
 *
 * @package DynamicSuite\Data
 */
final class Groups extends InstanceMember
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
     * Groups constructor.
     *
     * @param DynamicSuite $ds
     * @return void
     */
    public function __construct(DynamicSuite $ds)
    {
        parent::__construct($ds);
    }

    /**
     * Get an array of all groups.
     *
     * @param string|null $domain
     * @return Group[]
     * @throws PDOException
     */
    public function getAll(?string $domain = null): array
    {
        if (DS_CACHING) {
            $groups = $this->ds->cache->get("dynamicsuite:groups::$domain");
            if ($this->ds->cache->cache->getResultCode() === Memcached::RES_SUCCESS) {
                return $groups;
            }
        }
        $groups = [];
        $rows = $this->ds->db->query((new Query())
            ->select()
            ->from('ds_groups')
            ->where('domain', '=', $domain)
        );
        foreach ($rows as $row) {
            $groups[] = new Group($row);
        }
        if (DS_CACHING) {
            $this->ds->cache->set("dynamicsuite:groups::$domain", $groups);
        }
        return $groups;
    }

    /**
     * Attempt to find a group by name or ID.
     *
     * @param int|string $lookup_by
     * @param string|null $domain
     * @return Group|bool
     */
    public function find(string $lookup_by, ?string $domain = null)
    {
        if (DS_CACHING) {
            $group = $this->ds->cache->get("dynamicsuite:groups:group:$lookup_by");
            if ($this->ds->cache->cache->getResultCode() === Memcached::RES_SUCCESS) {
                return $group;
            }
        }
        $lookup_column = is_int($lookup_by) ? 'user_id' : 'name';
        $group = $this->ds->db->query((new Query())
            ->select()
            ->from('ds_groups')
            ->where($lookup_column, '=', $lookup_by)
            ->where('domain', '=', $domain)
        );
        if (count($group) !== 1) return false;
        $group = new Group($group[0]);
        if (DS_CACHING) {
            $this->ds->cache->set("dynamicsuite:groups:group:$lookup_by", $group);
        }
        return $group;
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
        $group->created_on = date('Y-m-d H:i:s');
        $group->created_by = $this->ds->session->user->username ?? null;
        $group->validate($group, self::COLUMN_LIMITS);
        $group->group_id = $this->ds->db->query((new Query())
            ->insert([
                'name' => $group->name,
                'description' => $group->description,
                'domain' => $group->domain,
                'created_by' => $group->created_by,
                'created_on' => $group->created_on
            ])
            ->into('ds_groups')
        );
        if (DS_CACHING) {
            $this->ds->cache->delete("dynamicsuite:groups::$group->domain");
        }
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
        $group->validate($group, self::COLUMN_LIMITS);
        $this->ds->db->query((new Query())
            ->update('ds_groups')
            ->set([
                'name' => $group->name,
                'description' => $group->description
            ])
            ->where('group_id', '=', $group->group_id)
        );
        if (DS_CACHING) {
            $this->ds->cache->delete("dynamicsuite:groups::$group->domain");
            $this->ds->cache->delete("dynamicsuite:groups:group:$group->group_id");
            $this->ds->cache->delete("dynamicsuite:groups:group:$group->name");
        }
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
            ->where('group_id', '=', $group->group_id)
        );
        if (DS_CACHING) {
            $this->ds->cache->delete("dynamicsuite:groups::$group->domain");
            $this->ds->cache->delete("dynamicsuite:groups:group:$group->group_id");
            $this->ds->cache->delete("dynamicsuite:groups:group:$group->name");
        }
        return $group;
    }

    /**
     * View the permissions for a given group.
     *
     * @param Group $group
     * @return Permission[]
     * @throws PDOException
     */
    public function viewPermissions(Group $group): array
    {
        if (DS_CACHING) {
            $permissions = $this->ds->cache->get("dynamicsuite:groups:permissions:$group->group_id");
            if ($this->ds->cache->cache->getResultCode() === Memcached::RES_SUCCESS) {
                return $permissions;
            }
        }
        $permissions = [];
        $rows = $this->ds->db->query((new Query())
            ->select()
            ->from('ds_view_group_permissions')
            ->where('group_id', '=', $group->group_id)
        );
        foreach ($rows as $row) {
            $permission = new Permission($row);
            $permissions[$permission->shorthand()] = $permission;
        }
        if (DS_CACHING) {
            $this->ds->cache->set("dynamicsuite:groups:permissions:$group->group_id", $permissions);
        }
        return $permissions;
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
                'group_id' => $group->group_id,
                'permission_id' => $permission->permission_id
            ])
            ->into('ds_group_permissions')
        );
        if (DS_CACHING) {
            $this->ds->cache->delete("dynamicsuite:groups:permissions:$group->group_id");
        }
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
            ->where('group_id', '=', $group->group_id)
            ->where('permission_id', '=', $permission->permission_id)
        );
        if (DS_CACHING) {
            $this->ds->cache->delete("dynamicsuite:groups:permissions:$group->group_id");
        }
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
        $this->ds->db->startTx();
        $this->ds->db->query((new Query())
            ->delete()
            ->from('ds_group_permissions')
            ->where('group_id', '=', $group->group_id)
        );
        $insert = (new Query())->insert()->into('ds_group_permissions');
        $rows = [];
        /** @var Permission $permission */
        foreach ($permissions as $permission) {
            $rows[] = [
                'group_id' => $group->group_id,
                'permission_id' => $permission->permission_id
            ];
        }
        if (!empty($rows)) $this->ds->db->query($insert->rows($rows));
        $this->ds->db->endTx();
        if (DS_CACHING) {
            $this->ds->cache->delete("dynamicsuite:groups:permissions:$group->group_id");
        }
        return $this;
    }

}