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
 * Class Permission.
 *
 * @package DynamicSuite
 * @property int $id
 * @property string $package_id
 * @property string $pending_package_id
 * @property string $name
 * @property string $pending_name
 * @property string $description
 * @property string $pending_description
 * @property string $shorthand
 * @property string $pending_shorthand
 * @property string $created_on
 */
class Permission extends ProtectedObject
{

    /**
     * The permission ID.
     *
     * @var int
     */
    protected $id;

    /**
     * Package ID associated with the permission.
     *
     * @var string
     */
    protected $package_id;

    /**
     * Package ID associated with the permission (pending save).
     *
     * @var string
     */
    protected $pending_package_id;

    /**
     * The name of the permission.
     *
     * @var string
     */
    protected $name;

    /**
     * The name of the permission (pending save).
     *
     * @var string
     */
    protected $pending_name;

    /**
     * A brief description of the permission.
     *
     * @var string
     */
    protected $description;

    /**
     * A brief description of the permission (pending save).
     *
     * @var string
     */
    protected $pending_description;

    /**
     * Shorthand permission representation.
     *
     * @var string
     */
    protected $shorthand;

    /**
     * Shorthand permission representation (pending save).
     *
     * @var string
     */
    protected $pending_shorthand;

    /**
     * The timestamp when the permission was created
     *
     * @var string
     */
    protected $created_on;

    /**
     * Maximum length that a permission package ID can be.
     *
     * @var int
     */
    public const MAX_PACKAGE_ID_LENGTH = 64;

    /**
     * Maximum length that a permission name can be.
     *
     * @var int
     */
    public const MAX_NAME_LENGTH = 64;

    /**
     * Maximum length that a permission description can be.
     *
     * @var int
     */
    public const MAX_DESCRIPTION_LENGTH = 255;

    /**
     * Permission constructor.
     *
     * @param array $permission
     * @return void
     */
    public function __construct(array $permission = null) {
        if (isset($permission['permission_id'])) $this->id = $permission['permission_id'];
        if (isset($permission['package_id'])) $this->pending_package_id = $permission['package_id'];
        if (isset($permission['name'])) $this->pending_name = $permission['name'];
        if (isset($permission['description'])) $this->pending_description = $permission['description'];
        if (isset($permission['created_on'])) $this->created_on = $permission['created_on'];
        if (is_string($this->pending_package_id) && is_string($this->pending_name)) {
            $this->pending_shorthand = "$this->pending_package_id:$this->pending_name";
        }
        $this->save();
    }

    /**
     * Set the permission ID.
     *
     * @param int $id
     * @return Permission
     */
    public function setId(int $id): Permission
    {
        $this->id = $id;
        return $this;
    }

    /**
     * Set the associated package ID.
     *
     * @param string $package_id
     * @return Permission
     */
    public function setPackageId(string $package_id): Permission
    {
        $this->pending_package_id = $package_id;
        $this->pending_shorthand = "$this->pending_package_id:$this->pending_name";
        return $this;
    }

    /**
     * Set the permission name.
     *
     * @param string $name
     * @return Permission
     */
    public function setName(string $name): Permission
    {
        $this->pending_name = $name;
        $this->pending_shorthand = "$this->pending_package_id:$this->pending_name";
        return $this;
    }

    /**
     * Set the permission description.
     *
     * @param string $description
     * @return Permission
     */
    public function setDescription(string $description): Permission
    {
        $this->pending_description = $description;
        return $this;
    }

    /**
     * Set when the permission was created.
     *
     * @param string|null $created_on
     * @return Permission
     */
    public function setCreatedOn(?string $created_on): Permission
    {
        $this->created_on = $created_on;
        return $this;
    }

    /**
     * Save the permission state and update its pending values.
     *
     * @return Permission
     */
    public function save(): Permission
    {
        $this->package_id = $this->pending_package_id;
        $this->name = $this->pending_name;
        $this->description = $this->pending_description;
        $this->shorthand = $this->pending_shorthand;
        return $this;
    }

    /**
     * Check to see if the package ID has changed between modifications.
     *
     * @return bool
     */
    public function packageIdChanged(): bool
    {
        return $this->package_id !== $this->pending_package_id;
    }

    /**
     * Check to see if the name has changed between modifications.
     *
     * @return bool
     */
    public function nameChanged(): bool
    {
        return $this->name !== $this->pending_name;
    }

    /**
     * Check to see if the description has changed between modifications.
     *
     * @return bool
     */
    public function descriptionChanged(): bool
    {
        return $this->description !== $this->pending_description;
    }

    /**
     * Check to see if the shorthand version has changed between modifications.
     *
     * @return bool
     */
    public function shorthandChanged(): bool
    {
        return $this->shorthand !== $this->pending_shorthand;
    }

    /**
     * Get the group as an array.
     *
     * @return array
     */
    public function asArray(): array
    {
        return [
            'permission_id' => $this->id,
            'package_id' => $this->package_id,
            'name' => $this->name,
            'description' => $this->description,
            'shorthand' => $this->shorthand,
            'created_on' => $this->created_on
        ];
    }

    /**
     * Validate the current permission for usage in the database.
     *
     * @return bool
     * @throws PDOException
     */
    public function validateForDatabase(): bool
    {
        $errors = [];
        if (strlen($this->pending_package_id) > self::MAX_PACKAGE_ID_LENGTH) {
            $errors['package_id'] = "$this->pending_package_id > " .  self::MAX_PACKAGE_ID_LENGTH . ' characters';
        }
        if (strlen($this->pending_name) > self::MAX_NAME_LENGTH) {
            $errors['name'] = "$this->pending_name > " .  self::MAX_NAME_LENGTH . ' characters';
        }
        if (strlen($this->pending_description) > self::MAX_DESCRIPTION_LENGTH) {
            $errors['description'] = "$this->pending_description > " .  self::MAX_DESCRIPTION_LENGTH . ' characters';
        }
        if (!empty($errors)) {
            $message = 'Permission has data that exceeds database limits' . PHP_EOL;
            foreach ($errors as $k => $v) {
                $message .= "  -- $k: $v" . PHP_EOL;
            }
            throw new PDOException($message);
        } else {
            return true;
        }
    }

}