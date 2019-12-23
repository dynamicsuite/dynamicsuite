<?php
/*
 * Dynamic Suite
 * Copyright (C) 2019 Dynamic Suite Team
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
 * Class Permissions.
 *
 * @package DynamicSuite
 * @property Permission[] $available
 */
class Permissions extends InstanceMember
{

    /**
     * An array of available permissions.
     *
     * @var Permission[]
     */
    protected $available;

    /**
     * Permissions constructor.
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
            $permissions[$permission->shorthand] = $permission;
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
        return $this->available[$shorthand] ?? false;
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
        $permission
            ->setCreatedOn(date('Y-m-d H:i:s'))
            ->validateForDatabase();
        $id = $this->ds->db->query((new Query())
            ->insert()
            ->into('ds_permissions')
            ->row([
                'package_id' => $permission->pending_package_id,
                'name' => $permission->pending_name,
                'description' => $permission->pending_description,
                'created_on' => $permission->created_on
            ])
        );
        $permission->setId($id)->save();
        $this->available[$permission->shorthand] = $permission;
        $this->ds->events->create((new Event())
            ->setPackageId('dynamicsuite')
            ->setType(100)
            ->setAffected($permission->shorthand)
            ->setMessage('Permission created')
        );
        $this->ds->save();
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
        $permission->validateForDatabase();
        $this->ds->db->query((new Query())
            ->update('ds_permissions')
            ->set([
                'package_id' => $permission->pending_package_id,
                'name' => $permission->pending_name,
                'description' => $permission->pending_description
            ])
            ->where('permission_id', '=', $permission->id)
        );
        if ($permission->shorthandChanged()) {
            unset($this->available[$permission->shorthand]);
        }
        $permission->save();
        $this->available[$permission->shorthand] = $permission;
        $this->ds->events->create((new Event())
            ->setPackageId('dynamicsuite')
            ->setType(101)
            ->setAffected($permission->shorthand)
            ->setMessage('Permission modified')
        );
        $this->ds->save();
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
            ->where('permission_id', '=', $permission->id)
        );
        unset($this->available[$permission->shorthand]);
        $this->ds->events->create((new Event())
            ->setPackageId('dynamicsuite')
            ->setType(102)
            ->setAffected($permission->shorthand)
            ->setMessage('Permission deleted')
        );
        $this->ds->save();
        return $permission;
    }

}