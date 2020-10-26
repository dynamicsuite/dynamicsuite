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
 * Class User.
 *
 * @package DynamicSuite\Storable
 * @property int|null $user_id
 * @property string|null $username
 * @property string|null $password
 * @property bool $root
 * @property bool $inactive
 * @property string|null $inactive_on
 * @property int $login_attempts
 * @property string|null $login_last_attempt
 * @property string|null $login_last_success
 * @property string|null $login_last_ip
 * @property string|null $created_by
 * @property int|null $created_on
 */
class User extends Storable implements IStorable
{

    /**
     * Column length limits.
     *
     * @var int[]
     */
    public const COLUMN_LIMITS = [
        'username' => 254,
        'password' => 96,
        'login_attempts' => 255,
        'login_last_ip' => 39,
        'created_by' => 254
    ];

    /**
     * The user's ID.
     *
     * @var int|null
     */
    public ?int $user_id = null;

    /**
     * The user's login username.
     *
     * @var string|null
     */
    public ?string $username = null;

    /**
     * Hashed password.
     *
     * @var string|null
     */
    public ?string $password = null;

    /**
     * Root user status.
     *
     * @var bool
     */
    public bool $root = false;

    /**
     * Inactive state.
     *
     * @var bool
     */
    public bool $inactive = false;

    /**
     * Timestamp when the user was made inactive.
     *
     * @var string|null
     */
    public ?string $inactive_on = null;

    /**
     * The number of login attempts the user has tried to login.
     *
     * @var int
     */
    public int $login_attempts = 0;

    /**
     * The time of the last login attempt.
     *
     * @var string|null
     */
    public ?string $login_last_attempt = null;

    /**
     * The time of the last login success.
     *
     * @var string|null
     */
    public ?string $login_last_success = null;

    /**
     * The IP address of the user on the last successful login.
     *
     * @var string|null
     */
    public ?string $login_last_ip = null;

    /**
     * User creation source.
     *
     * @var string|null
     */
    public ?string $created_by = null;

    /**
     * The UNIX timestamp when the user was created.
     *
     * @var int|null
     */
    public ?int $created_on = null;

    /**
     * User constructor.
     *
     * @param array $user
     * @return void
     */
    public function __construct(array $user = [])
    {
        if (array_key_exists('inactive', $user)) {
            $user['inactive'] = (bool) $user['inactive'];
        }
        if (array_key_exists('root', $user)) {
            $user['root'] = (bool) $user['root'];
        }
        parent::__construct($user);
    }

    /**
     * Change the password.
     *
     * @param string $password
     * @return void
     */
    public function changePassword(string $password): void
    {
        $this->password = password_hash($password, PASSWORD_ARGON2I);
    }

    /**
     * Verify to see if the given password matches the current password.
     *
     * @param string|null $password
     * @return bool
     */
    public function verifyPassword(?string $password): bool
    {
        if (!$password) {
            return false;
        }
        return password_verify($password, $this->password);
    }

    /**
     * Set the inactive state.
     *
     * @param bool|null $inactive
     * @return void
     */
    public function setInactive(?bool $inactive = true): void
    {
        $inactive ??= false;
        $this->inactive = $inactive;
        $this->inactive_on = $inactive ? time() : null;
    }

    /**
     * Increase the user's login attempts by 1 and return the current attempt count.
     *
     * @return int
     */
    public function addLoginAttempt(): int
    {
        if ($this->login_attempts > self::COLUMN_LIMITS['login_attempts']) {
            $this->login_attempts = self::COLUMN_LIMITS['login_attempts'];
        } else {
            $this->login_attempts++;
        }
        return $this->login_attempts;
    }

    /**
     * Create the user in the database.
     *
     * @return User
     * @throws Exception|PDOException
     */
    public function create(): User
    {
        $this->created_by = $this->created_by ?? Session::$user_name;
        $this->created_on = time();
        $this->validate(self::COLUMN_LIMITS);
        $this->user_id = (new Query())
            ->insert([
                'username' => $this->username,
                'password' => $this->password,
                'root' => $this->root ? 1 : null,
                'inactive' => $this->inactive ? 1 : null,
                'inactive_on' => $this->inactive_on,
                'login_attempts' => $this->login_attempts,
                'login_last_attempt' => $this->login_last_attempt,
                'login_last_success' => $this->login_last_success,
                'login_last_ip' => $this->login_last_ip,
                'created_by' => $this->created_by,
                'created_on' => $this->created_on
            ])
            ->into('ds_users')
            ->execute();
        return $this;
    }

    /**
     * Attempt to read a user by ID.
     *
     * Returns the User if found, or FALSE if not found.
     *
     * @param int|null $id
     * @return bool|User
     * @throws Exception|PDOException
     */
    public static function readById(?int $id = null)
    {
        if ($id === null) {
            return false;
        }
        $user = (new Query())
            ->select()
            ->from('ds_users')
            ->where('user_id', '=', $id)
            ->execute(true);
        return $user ? new User($user) : false;
    }

    /**
     * Attempt to read a user by username.
     *
     * Returns the User if found, or FALSE if not found.
     *
     * @param string|null $username
     * @return bool|User
     * @throws Exception|PDOException
     */
    public static function readByUsername(?string $username = null)
    {
        if ($username === null) {
            return false;
        }
        $user = (new Query())
            ->select()
            ->from('ds_users')
            ->where('username', '=', $username)
            ->execute(true);
        return $user ? new User($user) : false;
    }

    /**
     * Update the user in the database.
     *
     * @return User
     * @throws Exception|PDOException
     */
    public function update(): User
    {
        $this->validate(self::COLUMN_LIMITS);
        (new Query())
            ->update('ds_users')
            ->set([
                'username' => $this->username,
                'password' => $this->password,
                'root' => $this->root ? 1 : null,
                'inactive' => $this->inactive ? 1 : null,
                'inactive_on' => $this->inactive_on,
                'login_attempts' => $this->login_attempts,
                'login_last_attempt' => $this->login_last_attempt,
                'login_last_success' => $this->login_last_success,
                'login_last_ip' => $this->login_last_ip
            ])
            ->where('user_id', '=', $this->user_id)
            ->execute();
        return $this;
    }

    /**
     * Update the groups for the user.
     *
     * Groups is an array of integers, where each integer matches the group ID of the group.
     *
     * This method contains multiple queries and should be run inside of a transaction.
     *
     * @param int[] $groups
     * @return User
     * @throws PDOException|Exception
     */
    public function updateGroups(array $groups): User
    {
        $insert = [];
        foreach ($groups as $group_id) {
            $insert[] = [
                'user_id' => $this->user_id,
                'group_id' => $group_id
            ];
        }
        (new Query())
            ->delete()
            ->from('ds_users_groups')
            ->where('user_id', '=', $this->user_id)
            ->execute();
        if ($insert) {
            (new Query())
                ->insert($insert)
                ->into('ds_users_groups')
                ->execute();
        }
        return $this;
    }

    /**
     * Delete the user from the database.
     *
     * @return User
     * @throws Exception|PDOException
     */
    public function delete(): User
    {
        (new Query())
            ->delete()
            ->from('ds_users')
            ->where('user_id', '=', $this->user_id)
            ->execute();
        return $this;
    }

    /**
     * Try to login the user and update their associated values in the database.
     *
     * @param string|null $password
     * @return bool
     * @throws Exception|PDOException
     */
    public function login(?string $password): bool
    {
        if (!$this->user_id) {
            throw new Exception('Tried to login a user that has no user ID. Was the user lookup successful?');
        }
        $this->login_last_attempt = time();
        $this->login_last_ip = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
        if (!$this->verifyPassword($password)) {
            $this->addLoginAttempt();
            $success = false;
        } else {
            $this->login_attempts = 0;
            $this->login_last_success = time();
            $success = true;
        }
        $this->update();
        return $success;
    }

}