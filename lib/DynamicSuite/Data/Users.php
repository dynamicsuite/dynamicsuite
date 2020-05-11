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
final class Users extends InstanceMember
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
     * Create a user.
     *
     * @param User $user
     * @return User
     * @throws PDOException
     */
    public function create(User $user): User
    {
        $user->created_on = date('Y-m-d H:i:s');
        $user->created_by = $user->created_by ?? $this->ds->session->user->username ?? null;
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
     * Attempt to read a user by username.
     *
     * @param string|null $username
     * @return bool|User
     * @throws PDOException
     */
    public function readByUsername(?string $username)
    {
        $user = $this->ds->db->query((new Query())
            ->select()
            ->from('ds_users')
            ->where('username', '=', $username)
        );
        if (count($user) !== 1 || !isset($user[0])) {
            return false;
        }
        return new User($user[0]);
    }

    /**
     * Attempt to read a user by user ID.
     *
     * @param int|null $id
     * @return User|bool
     * @throws PDOException
     */
    public function readById(?int $id)
    {
        $user = $this->ds->db->query((new Query())
            ->select()
            ->from('ds_users')
            ->where('user_id', '=', $id)
        );
        if (count($user) !== 1 || !isset($user[0])) {
            return false;
        }
        return new User($user[0]);
    }

    /**
     * Update a user.
     *
     * @param User $user
     * @return User
     * @throws PDOException
     */
    public function update(User $user): User
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
     * Try to login the user and update their associated values.
     *
     * @param User $user
     * @param string|null $password
     * @return bool
     * @throws PDOException
     */
    public function tryLogin(User $user, ?string $password): bool
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
        $this->update($user);
        return $success;
    }

}