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
 * @property string|null $created_on
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
     * The timestamp when the permission was created.
     *
     * @var string|null
     */
    public ?string $created_on = null;

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
        $this->created_on = date('Y-m-d H:i:s');
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

}