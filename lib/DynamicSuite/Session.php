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
use Exception;

/**
 * Class Session.
 *
 * @package DynamicSuite
 * @property string $id
 * @property array $permissions
 * @property array $groups
 * @property User $user
 */
class Session extends InstanceMember
{

    /**
     * The session ID.
     *
     * @var string
     */
    protected $id;

    /**
     * Array of the current user's permissions.
     *
     * @var array
     */
    protected $permissions;

    /**
     * Array of the current user's groups.
     *
     * @var array
     */
    protected $groups;

    /**
     * The current user.
     *
     * @var User
     */
    protected $user;

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
        if (isset($_SESSION['dynamicsuite']['user_id'])) $this->create($_SESSION['dynamicsuite']['user_id']);
    }

    /**
     * Create a new authenticated session.
     *
     * @param int $user_id
     * @return bool
     */
    public function create(int $user_id): bool
    {
        try {
            $user = $this->ds->users->find($user_id);
            if (!$user instanceof User) throw new Exception('Cannot create session for unknown user');
            $this
                ->setId(session_id())
                ->setPermissions($this->ds->users->viewPermissions($user))
                ->setGroups($this->ds->users->viewGroups($user))
                ->setUser($user);
            $_SESSION['dynamicsuite']['user_id'] = $user_id;
            return true;
        } catch (Exception $exception) {
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
        $this
            ->setId(null)
            ->setPermissions(null)
            ->setGroups(null)
            ->setUser(null);
        $_SESSION = [];
        session_regenerate_id();
        return $this;
    }

    /**
     * Set the current session ID.
     *
     * @param string|null $id
     * @return Session
     */
    public function setId(?string $id): Session
    {
        $this->id = $id;
        return $this;
    }

    /**
     * Set the current user's permissions.
     *
     * @param array|null $permissions
     * @return Session
     */
    public function setPermissions(?array $permissions): Session
    {
        $this->permissions = $permissions;
        return $this;
    }

    /**
     * Set the current user's groups.
     *
     * @param array|null $groups
     * @return Session
     */
    public function setGroups(?array $groups): Session
    {
        $this->groups = $groups;
        return $this;
    }

    /**
     * Set the current user.
     *
     * @param User|null $user
     * @return Session
     */
    public function setUser(?User $user): Session
    {
        $this->user = $user;
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
        if (!isset($this->permissions)) return false;
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