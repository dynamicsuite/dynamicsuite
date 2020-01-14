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

namespace DynamicSuite\Core;
use DynamicSuite\Base\InstanceMember;
use DynamicSuite\Data\Permission;
use DynamicSuite\Data\Group;
use DynamicSuite\Data\User;
use PDOException;

/**
 * Class Session.
 *
 * @package DynamicSuite
 * @property string|null $id
 * @property Permission[] $permissions
 * @property Group[] $groups
 * @property User|null $user
 */
class Session extends InstanceMember
{

    /**
     * The session ID.
     *
     * @var string
     */
    private ?string $id = null;

    /**
     * Array of the current user's permissions.
     *
     * @var Permission[]
     */
    private array $permissions = [];

    /**
     * Array of the current user's groups.
     *
     * @var Group[]
     */
    private array $groups = [];

    /**
     * The current user.
     *
     * @var User|null
     */
    private ?User $user;

    /**
     * Session constructor.
     *
     * @param Instance $ds
     * @return void
     */
    public function __construct(Instance $ds)
    {
        parent::__construct($ds);
        if (session_status() === PHP_SESSION_NONE) session_start();
        $this->getSaved();
    }

    /**
     * Get the session saved to the user's cookie.
     *
     * @return void
     */
    public function getSaved(): void
    {
        if (isset($_SESSION[Instance::getVHostHash()]['user_id'])) {
            $this->create($_SESSION[Instance::getVHostHash()]['user_id']);
        }
    }

    /**
     * Create a new session given a user ID.
     *
     * @param int $user_id
     * @return bool
     */
    public function create(int $user_id): bool
    {
        try {
            $user = $this->ds->users->find($user_id);
            if (!$user) return false;
            $this->id = session_id();
            $this->permissions = $this->ds->users->viewPermissions($user);
            $this->groups = $this->ds->users->viewGroups($user);
            $this->user = $user;
            $_SESSION[Instance::getVHostHash()]['user_id'] = $user_id;
            return true;
        } catch (PDOException $exception) {
            error_log($exception->getMessage(), E_USER_WARNING);
            return false;
        }
    }

    /**
     * Destroy the current authenticated session.
     *
     * @return Session
     */
    public function destroy(): Session
    {
        $this->id = null;
        $this->permissions = [];
        $this->groups = [];
        $this->user = null;
        $_SESSION[Instance::getVHostHash()] = null;
        session_regenerate_id();
        return $this;
    }

    /**
     * Check to see if the currently authenticated user has the given permission(s).
     *
     * $permission can either be a shorthand permission string or an array of shorthand strings.
     *
     * @param string|array $permissions
     * @return bool
     */
    public function checkPermissions($permissions): bool
    {
        if ($this->id === null) return false;
        if ($permissions === null) return true;
        if (!$this->permissions) return false;
        if (is_string($permissions) && !array_key_exists($permissions, $this->permissions)) {
            return false;
        } elseif (is_array($permissions)) {
            foreach ($permissions as $value) {
                if (!is_string($value)) {
                    trigger_error("Permission values must be strings when permissions are an array", E_USER_WARNING);
                    return false;
                }
                if (!array_key_exists($value, $this->permissions)) return false;
            }
        } else {
            return false;
        }
        return true;
    }

}