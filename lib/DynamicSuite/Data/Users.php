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
 * Class Users.
 *
 * @package DynamicSuite\Data
 */
class Users extends InstanceMember
{

    /**
     * Column length limits.
     *
     * @var int[]
     */
    public const COLUMN_LIMITS = [
        'username' => 254,
        'password' => 96,
        'created_by' => 254,
        'login_attempts' => 255,
        'login_last_ip' => 39
    ];

    /**
     * Users constructor.
     *
     * @param DynamicSuite $ds
     * @return void
     */
    public function __construct(DynamicSuite $ds)
    {
        parent::__construct($ds);
    }

    /**
     * Get an array of all users.
     *
     * If $type is -1, only inactive users will be returned.
     * If $type is 1, only active users will be returned.
     * If $type is anything else or undefined, all users will be returned.
     *
     * @param int $type
     * @return array
     * @throws PDOException
     */
    public function get(int $type = 0): array
    {
        $users = [];
        $query = (new Query())->select()->from('ds_users');
        if ($type === -1) $query->where('inactive', 'IS', null);
        if ($type === 1) $query->where('inactive', 'IS NOT', null);
        $rows = $this->ds->db->query($query);
        foreach ($rows as $row) $users[] = new User($row);
        return $users;
    }

    /**
     * Attempt to find a user.
     *
     * If $lookup_by is an integer, the user_id column will be queried, if not,
     * the username column will be queried.
     *
     * @param int|string $lookup_by
     * @return User|bool
     * @throws PDOException
     */
    public function find($lookup_by)
    {
        $lookup_column = is_int($lookup_by) ? 'user_id' : 'username';
        $user = $this->ds->db->query((new Query())
            ->select()
            ->from('ds_users')
            ->where($lookup_column, '=', $lookup_by)
        );
        if (count($user) !== 1) return false;
        return new User($user[0]);
    }

    /**
     * Create a user.
     *
     * @param User $user
     * @return User
     * @throws PDOException
     */
    public function create(User $user): User
    {
        $user->created_on = date('Y-m-d H:i:s');
        $user->created_by = $this->ds->session->user->username ?? null;
        $user->validate($user, self::COLUMN_LIMITS);
        $user->user_id = $this->ds->db->query((new Query())
            ->insert([
                'username' => $user->username,
                'password' => $user->password,
                'inactive' => $user->inactive ? 1 : null,
                'inactive_time' => $user->inactive_time,
                'created_by' => $user->created_by,
                'created_on' => $user->created_on
            ])
            ->into('ds_users')
        );
        return $user;
    }

    /**
     * Modify a user.
     *
     * @param User $user
     * @return User
     * @throws PDOException
     */
    public function modify(User $user): User
    {
        $user->validate($user, self::COLUMN_LIMITS);
        $this->ds->db->query((new Query())
            ->update('ds_users')
            ->set([
                'username' => $user->username,
                'password' => $user->password,
                'inactive' => $user->inactive ? 1 : null,
                'inactive_time' => $user->inactive_time,
                'login_attempts' => $user->login_attempts,
                'login_last_attempt' => $user->login_last_attempt,
                'login_last_success' => $user->login_last_success,
                'login_last_ip' => $user->login_last_ip
            ])
            ->where('user_id', '=', $user->user_id)
        );
        return $user;
    }

    /**
     * Delete a user.
     *
     * @param User $user
     * @return User
     * @throws PDOException
     */
    public function delete(User $user): User
    {
        $this->ds->db->query((new Query())
            ->delete()
            ->from('ds_users')
            ->where('user_id', '=', $user->user_id)
        );
        return $user;
    }

    /**
     * Add a user to a group.
     *
     * @param User $user
     * @param Group $group
     * @return Users
     * @throws PDOException
     */
    public function addGroup(User $user, Group $group): Users
    {
        $this->ds->db->query((new Query())
            ->insert([
                'user_id' => $user->user_id,
                'group_id' => $group->group_id
            ])
            ->into('ds_user_groups')
        );
        return $this;
    }

    /**
     * Remove a user from a group.
     *
     * @param User $user
     * @param Group $group
     * @return Users
     * @throws PDOException
     */
    public function removeGroup(User $user, Group $group): Users
    {
        $this->ds->db->query((new Query())
            ->delete()
            ->from('ds_user_groups')
            ->where('user_id', '=', $user->user_id)
            ->where('group_id', '=', $group->group_id)
        );
        return $this;
    }

    /**
     * Replace the given users groups with the given array of groups.
     *
     * @param User $user
     * @param Group[] $groups
     * @return Users
     * @throws PDOException
     */
    public function replaceGroups(User $user, array $groups): Users
    {
        $this->ds->db->startTx();
        $this->ds->db->query((new Query())
            ->delete()
            ->from('ds_user_groups')
            ->where('user_id', '=', $user->user_id)
        );
        $insert = (new Query())->insert()->into('ds_user_groups');
        $rows = [];
        /** @var Group $group */
        foreach ($groups as $group) {
            $rows[] = [
                'user_id' => $user->user_id,
                'group_id' => $group->group_id
            ];
        }
        if (!empty($rows)) $this->ds->db->query($insert->rows($rows));
        $this->ds->db->endTx();
        return $this;
    }

    /**
     * Get an array of a user's permission groups.
     *
     * @param User $user
     * @return Group[]
     * @throws PDOException
     */
    public function viewGroups(User $user): array
    {
        $rows = $this->ds->db->query((new Query())
            ->select()
            ->from('ds_view_user_groups')
            ->where('user_id', '=', $user->user_id)
        );
        $groups = [];
        foreach ($rows as $row) {
            $groups[] = new Group($row);
        }
        return $groups;
    }

    /**
     * Get an array of a user's permissions.
     *
     * @param User $user
     * @return Permission[]
     * @throws PDOException
     */
    public function viewPermissions(User $user): array
    {
        $rows = $this->ds->db->query((new Query())
            ->select()
            ->from('ds_view_user_permissions')
            ->where('user_id', '=', $user->user_id)
        );
        $permissions = [];
        foreach ($rows as $row) {
            $permission = new Permission($row);
            $permissions[$permission->shorthand()] = $permission;
        }
        return $permissions;
    }

    /**
     * Try to login the user and update their associated values.
     *
     * @param User $user
     * @param string $password
     * @return bool
     * @throws PDOException
     */
    public function tryLogin(User $user, string $password): bool
    {
        $user->login_last_attempt = date('Y-m-d H:i:s');
        $user->login_last_ip = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
        if (!$user->verifyPassword($password)) {
            $user->addLoginAttempt();
            $success = false;
        } else {
            $user->login_attempts = 0;
            $user->login_last_success = date('Y-m-d H:i:s');
            $success = true;
        }
        $user->validate($user, self::COLUMN_LIMITS);
        $this->ds->db->query((new Query())
            ->update('ds_users')
            ->set([
                'login_attempts' => $user->login_attempts,
                'login_last_attempt' => $user->login_last_attempt,
                'login_last_success' => $user->login_last_success,
                'login_last_ip' => $user->login_last_ip
            ])
            ->where('user_id', '=', $user->user_id)
        );
        return $success;
    }

}