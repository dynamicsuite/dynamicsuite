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
 * Class Group.
 *
 * @package DynamicSuite
 * @property int $id
 * @property string $name
 * @property string $pending_name
 * @property string $description
 * @property string $pending_description
 * @property string $created_by
 * @property string $created_on
 */
class Group extends ProtectedObject
{

    /**
     * Group ID.
     *
     * @var int
     */
    protected $id;

    /**
     * Group name.
     *
     * @var string
     */
    protected $name;

    /**
     * Group name (pending save).
     *
     * @var string
     */
    protected $pending_name;

    /**
     * Group description.
     *
     * @var string
     */
    protected $description;

    /**
     * Group description (pending save).
     *
     * @var string
     */
    protected $pending_description;

    /**
     * The user that created the group.
     *
     * @var string
     */
    protected $created_by;

    /**
     * The timestamp when the group was created.
     *
     * @var string
     */
    protected $created_on;

    /**
     * Maximum length that a group name can be.
     *
     * @var int
     */
    public const MAX_NAME_LENGTH = 64;

    /**
     * Maximum length that a group description can be.
     *
     * @var int
     */
    public const MAX_DESCRIPTION_LENGTH = 64;

    /**
     * Maximum length that a group added by entity can be.
     *
     * @var int
     */
    public const MAX_CREATED_BY_LENGTH = 254;

    /**
     * Group constructor.
     *
     * @param array $group
     * @return void
     */
    public function __construct(array $group = null)
    {
        if (isset($group['group_id'])) $this->id = $group['group_id'];
        if (isset($group['name'])) $this->pending_name = $group['name'];
        if (isset($group['description'])) $this->pending_description = $group['description'];
        if (isset($group['created_by'])) $this->created_by = $group['created_by'];
        if (isset($group['created_on'])) $this->created_on = $group['created_on'];
        $this->save();
    }

    /**
     * Set the group ID.
     *
     * @param int $id
     * @return Group
     */
    public function setId(int $id): Group
    {
        $this->id = $id;
        return $this;
    }

    /**
     * Set the group name.
     *
     * @param string $name
     * @return Group
     */
    public function setName(string $name): Group
    {
        $this->pending_name = $name;
        return $this;
    }

    /**
     * Set the group description.
     *
     * @param string $description
     * @return Group
     */
    public function setDescription(string $description): Group
    {
        $this->pending_description = $description;
        return $this;
    }

    /**
     * Set the user that created the group.
     *
     * @param string|null $created_by
     * @return Group
     */
    public function setCreatedBy(?string $created_by): Group
    {
        $this->created_by = $created_by;
        return $this;
    }

    /**
     * Set the timestamp when the group was created.
     *
     * @param string|null $created_on
     * @return Group
     */
    public function setCreatedOn(?string $created_on): Group
    {
        $this->created_on = $created_on;
        return $this;
    }

    /**
     * Save the group and commit any pending values.
     *
     * @return Group
     */
    public function save(): Group
    {
        $this->name = $this->pending_name;
        $this->description = $this->pending_description;
        return $this;
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
     * @noinspection PhpUnused
     */
    public function descriptionChanged(): bool
    {
        return $this->description !== $this->pending_description;
    }

    /**
     * Get the group as an array.
     *
     * @return array
     * @noinspection PhpUnused
     */
    public function asArray(): array
    {
        return [
            'group_id' => $this->id,
            'name' => $this->name,
            'description' => $this->description,
            'created_by' => $this->created_by,
            'created_on' => $this->created_on
        ];
    }

    /**
     * Validate the current group for usage in the database.
     *
     * @return bool
     * @throws PDOException
     */
    public function validateForDatabase(): bool
    {
        $errors = [];
        if (strlen($this->pending_name) > self::MAX_NAME_LENGTH) {
            $errors['name'] = "$this->pending_name > " .  self::MAX_NAME_LENGTH . ' characters';
        }
        if (strlen($this->pending_description) > self::MAX_DESCRIPTION_LENGTH) {
            $errors['description'] = "$this->pending_description > " .  self::MAX_DESCRIPTION_LENGTH . ' characters';
        }
        if (strlen($this->created_by) > self::MAX_CREATED_BY_LENGTH) {
            $errors['created_by'] = "$this->created_by > " .  self::MAX_CREATED_BY_LENGTH . ' characters';
        }
        if (!empty($errors)) {
            $message = 'Group has data that exceeds database limits' . PHP_EOL;
            foreach ($errors as $k => $v) {
                $message .= "  -- $k: $v" . PHP_EOL;
            }
            throw new PDOException($message);
        } else {
            return true;
        }
    }

}