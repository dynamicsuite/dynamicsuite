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
use PDOException;

/**
 * Class Permissions.
 *
 * @package DynamicSuite\Data
 */
final class Permissions extends InstanceMember
{

    /**
     * Column length limits.
     *
     * @var int[]
     */
    public const COLUMN_LIMITS = [
        'package_id' => 64,
        'name' => 64,
        'description' => 255
    ];

    /**
     * Permissions constructor.
     *
     * @param DynamicSuite $ds
     * @return void
     */
    public function __construct(DynamicSuite $ds)
    {
        parent::__construct($ds);
    }

    /**
     * Get all permissions.
     *
     * @return Permission[]
     * @throws PDOException
     */
    public function getAll(): array
    {
        $permissions = [];
        $rows = $this->ds->db->query((new Query())
            ->select()
            ->from('ds_permissions')
        );
        foreach ($rows as $row) {
            $permission = new Permission($row);
            $permissions[$permission->shorthand()] = $permission;
        }
        return $permissions;
    }

    /**
     * Attempts to find a permission by shorthand format.
     *
     * @param string $shorthand
     * @return Permission|bool
     */
    public function find(string $shorthand)
    {
        $permission_info = explode(':', $shorthand);
        if (count($permission_info) !== 2) {
            throw new PDOException('Malformed shorthand permission');
        }
        $permission = $this->ds->db->query((new Query())
            ->select()
            ->from('ds_permissions')
            ->where('package_id', '=', $permission_info[0])
            ->where('name', '=', $permission_info[1])
        );
        if (count($permission) !== 1) return false;
        return new Permission($permission[0]);
    }

    /**
     * Create a permission.
     *
     * @param Permission $permission
     * @return Permission
     * @throws PDOException
     */
    public function create(Permission $permission): Permission
    {
        $permission->created_on = date('Y-m-d H:i:s');
        $permission->validate($permission, self::COLUMN_LIMITS);
        $permission->permission_id = $this->ds->db->query((new Query())
            ->insert()
            ->into('ds_permissions')
            ->row([
                'package_id' => $permission->package_id,
                'name' => $permission->name,
                'description' => $permission->description,
                'created_on' => $permission->created_on
            ])
        );
        return $permission;
    }

    /**
     * Modify a permission.
     *
     * @param Permission $permission
     * @return Permission
     * @throws PDOException
     */
    public function modify(Permission $permission): Permission
    {
        $permission->validate($permission, self::COLUMN_LIMITS);
        $this->ds->db->query((new Query())
            ->update('ds_permissions')
            ->set([
                'package_id' => $permission->package_id,
                'name' => $permission->name,
                'description' => $permission->description
            ])
            ->where('permission_id', '=', $permission->permission_id)
        );
        return $permission;
    }

    /**
     * Delete a permission.
     *
     * @param Permission $permission
     * @return Permission
     * @throws PDOException
     */
    public function delete(Permission $permission): Permission
    {
        $this->ds->db->query((new Query())
            ->delete()
            ->from('ds_permissions')
            ->where('permission_id', '=', $permission->permission_id)
        );
        return $permission;
    }

}