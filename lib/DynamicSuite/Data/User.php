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
use DynamicSuite\Base\DatabaseItem;

/**
 * Class User.
 *
 * @package DynamicSuite\Data
 * @property int|null $user_id
 * @property string|null $username
 * @property string|null $password
 * @property bool $inactive
 * @property string|null $inactive_time
 * @property string|null $created_by
 * @property string|null $created_on
 * @property int $login_attempts
 * @property string|null $login_last_attempt
 * @property string|null $login_last_success
 * @property string|null $login_last_ip
 */
class User extends DatabaseItem
{

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
    public ?string $inactive_time = null;

    /**
     * User/entity that created the user.
     *
     * @var string|null
     */
    public ?string $created_by = null;

    /**
     * Timestamp when the user was created.
     *
     * @var string|null
     */
    public ?string $created_on = null;

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
     * User constructor.
     *
     * @param array $user
     * @return void
     */
    public function __construct(array $user = [])
    {
        $user['inactive'] ??= false;
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
        $inactive = (bool) $inactive;
        $this->inactive = $inactive;
        $this->inactive_time = $inactive ? date('Y-m-d H:i:s') : null;
    }

    /**
     * Increase the user's login attempts by 1 and return the current attempt count.
     *
     * @return int
     */
    public function addLoginAttempt(): int
    {
        if ($this->login_attempts > Users::COLUMN_LIMITS['login_attempts']) {
            $this->login_attempts = Users::COLUMN_LIMITS['login_attempts'];
        } else {
            $this->login_attempts++;
        }
        return $this->login_attempts;
    }

}