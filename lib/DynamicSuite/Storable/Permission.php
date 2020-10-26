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
 * Class Permission.
 *
 * @package DynamicSuite\Storable
 * @property int|null $permission_id
 * @property string|null $package_id
 * @property string|null $name
 * @property string|null $domain
 * @property string|null $description
 * @property string|null $created_by
 * @property int|null $created_on
 */
class Permission extends Storable implements IStorable
{

    /**
     * Column length limits.
     *
     * @var int[]
     */
    public const COLUMN_LIMITS = [
        'package_id' => 64,
        'name' => 64,
        'domain' => 64,
        'description' => 255,
        'created_by' => 254
    ];

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
     * Permission creation source.
     *
     * @var string|null
     */
    public ?string $created_by = null;

    /**
     * The UNIX timestamp when the permission was created.
     *
     * @var int|null
     */
    public ?int $created_on = null;

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
     * Create the permission in the database.
     *
     * @return Permission
     * @throws Exception|PDOException
     */
    public function create(): Permission
    {
        $this->created_by = $this->created_by ?? Session::$user_name;
        $this->created_on = time();
        $this->validate(self::COLUMN_LIMITS);
        $this->permission_id = (new Query())
            ->insert([
                'package_id' => $this->package_id,
                'name' => $this->name,
                'domain' => $this->domain,
                'description' => $this->description,
                'created_by' => $this->created_by,
                'created_on' => $this->created_on
            ])
            ->into('ds_permissions')
            ->execute();
        return $this;
    }

    /**
     * Attempt to read a permission by ID.
     *
     * Returns the Permission if found, or FALSE if not found.
     *
     * @param int|null $id
     * @return bool|Permission
     * @throws Exception|PDOException
     */
    public static function readById(?int $id = null)
    {
        if ($id === null) {
            return false;
        }
        $permission = (new Query())
            ->select()
            ->from('ds_permissions')
            ->where('permission_id', '=', $id)
            ->execute(true);
        return $permission ? new Permission($permission) : false;
    }

    /**
     * Attempt to read a permission by shorthand format with an optional domain.
     *
     * Returns the Permission if found, or FALSE if not found.
     *
     * @param string $shorthand
     * @param string|null $domain
     * @return bool|Permission
     * @throws Exception|PDOException
     */
    public static function readByShorthand(string $shorthand, ?string $domain = null)
    {
        $shorthand = explode(':', $shorthand);
        if (count($shorthand) !== 2) {
            throw new Exception('Invalid shorthand permission format for read');
        }
        $permission = (new Query())
            ->select()
            ->from('ds_permissions')
            ->where('package_id', '=', $shorthand[0])
            ->where('name', '=', $shorthand[1]);
        if ($domain) {
            $permission->where('domain', '=', $domain);
        } else {
            $permission->where('domain', 'IS', null);
        }
        $permission = $permission->execute(true);
        return $permission ? new Permission($permission) : false;
    }

    /**
     * Read all of the permissions for the given domain.
     *
     * This method returns a regular array for use in API responses and direct manipulation.
     *
     * Which columns are returned on each row (permission) can be set with the $columns parameter.
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
            ->from('ds_permissions')
            ->where('domain', $domain ? '=' : 'IS', $domain)
            ->execute();
    }

    /**
     * Read the permissions of a group for the given domain and the given group ID.
     *
     * The result array contains a list of assigned and unassigned permissions.
     *
     * This method returns a regular array for use with client components.
     *
     * @param string|null $domain
     * @param int|null $group_id
     * @return array[]
     * @throws PDOException|Exception
     */
    public static function readForComponent(?string $domain = null, ?int $group_id = null): array
    {
        $groups = [
            'assigned' => [],
            'unassigned' => []
        ];
        $assigned = [];
        if ($group_id) {
            $assigned = (new Query())
                ->select(['ds_permissions.permission_id', 'ds_permissions.description'])
                ->from('ds_groups_permissions')
                ->join('ds_permissions')
                ->on('ds_permissions.permission_id', '=', 'ds_groups_permissions.permission_id')
                ->where('ds_permissions.domain', '=', $domain)
                ->where('ds_groups_permissions.group_id', '=', $group_id)
                ->execute();
        }
        $unassigned = (new Query())
            ->select(['permission_id', 'description'])
            ->from('ds_permissions')
            ->where('domain', '=', $domain)
            ->execute();
        foreach ($unassigned as $value) {
            $groups['unassigned'][$value['permission_id']] = $value['description'];
        }
        foreach ($assigned as $value) {
            unset($groups['unassigned'][$value['permission_id']]);
            $groups['assigned'][$value['permission_id']] = $value['description'];
        }
        return $groups;
    }

    /**
     * Update the permission in the database.
     *
     * @return Permission
     * @throws Exception|PDOException
     */
    public function update(): Permission
    {
        $this->validate(self::COLUMN_LIMITS);
        (new Query())
            ->update('ds_permissions')
            ->set([
                'package_id' => $this->package_id,
                'name' => $this->name,
                'domain' => $this->domain,
                'description' => $this->description
            ])
            ->where('permission_id', '=', $this->permission_id)
            ->execute();
        return $this;
    }

    /**
     * Update a domain's properties and values.
     *
     * Properties must be given as an associative array, where the key is the property ID and the value is the value
     * of the property.
     *
     * @param string $domain
     * @param array $properties
     * @throws Exception
     */
    public static function updateDomain(string $domain, array $properties): void
    {
        $insert = [];
        foreach ($properties as $property_id => $value) {
            $insert[] = [
                'property_id' => $property_id,
                'domain' => $domain,
                'value' => $value
            ];
        }
        (new Query())
            ->delete()
            ->from('ds_properties_data')
            ->where('domain', '=', $domain)
            ->execute();
        (new Query())
            ->insert($insert)
            ->into('ds_properties_data')
            ->execute();
    }

    /**
     * Delete the permission from the database.
     *
     * @return Permission
     * @throws Exception|PDOException
     */
    public function delete(): Permission
    {
        (new Query())
            ->delete()
            ->from('ds_permissions')
            ->where('permission_id', '=', $this->permission_id)
            ->execute();
        return $this;
    }

    /**
     * Validate a list of permissions (ids) for the given domain.
     *
     * @param int[] $permissions
     * @param string $domain
     * @throws Exception
     */
    public static function validateForDomain(array $permissions, string $domain): void
    {
        foreach ($permissions as $permission_id) {
            $permission = (new Query())
                ->select(['permission_id'])
                ->from('ds_permissions')
                ->where('permission_id', '=', $permission_id)
                ->where('domain', '=', $domain)
                ->execute(true);
            if (!$permission) {
                throw new Exception('Trying to add a permission out of domain');
            }
        }
    }

}