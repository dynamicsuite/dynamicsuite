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
 * Class User.
 *
 * @package DynamicSuite
 * @property int $id
 * @property string $username
 * @property string $pending_username
 * @property string $password
 * @property string $pending_password
 * @property bool $inactive
 * @property bool $pending_inactive
 * @property string $inactive_time
 * @property string $created_by
 * @property string $created_on
 * @property int $login_attempts
 * @property string $login_last_attempt
 * @property string $login_last_success
 * @property string $login_last_ip
 */
class User extends ProtectedObject
{

    /**
     * The user's ID.
     *
     * @var int
     */
    protected $id;

    /**
     * The user's login username.
     *
     * @var string
     */
    protected $username;

    /**
     * The user's login username (pending save).
     *
     * @var string
     */
    protected $pending_username;

    /**
     * Hashed password.
     *
     * @var string
     */
    protected $password;

    /**
     * Hashed password (pending save).
     *
     * @var string
     */
    protected $pending_password;

    /**
     * Inactive state.
     *
     * @var bool
     */
    protected $inactive = false;

    /**
     * Inactive state (pending save).
     *
     * @var bool
     */
    protected $pending_inactive = false;

    /**
     * Timestamp when the user was made inactive.
     *
     * @var string
     */
    protected $inactive_time;

    /**
     * User/entity that created the user.
     *
     * @var string
     */
    protected $created_by;

    /**
     * Timestamp when the user was created.
     *
     * @var string
     */
    protected $created_on;

    /**
     * The number of login attempts the user has tried to login.
     *
     * @var int
     */
    protected $login_attempts = 0;

    /**
     * The time of the last login attempt.
     *
     * @var string
     */
    protected $login_last_attempt;

    /**
     * The time of the last login success.
     *
     * @var string
     */
    protected $login_last_success;

    /**
     * The IP address of the user on the last successful login.
     *
     * @var string
     */
    protected $login_last_ip;

    /**
     * Maximum length that a user username can be.
     *
     * @var int
     */
    public const MAX_USERNAME_LENGTH = 64;

    /**
     * Maximum length that a user password can be.
     *
     * @var int
     */
    public const MAX_PASSWORD_LENGTH = 96;

    /**
     * Maximum length that a user added by name can be.
     *
     * @var int
     */
    public const MAX_CREATED_BY_LENGTH = 64;

    /**
     * Maximum amount of login attempts a user can have.
     *
     * @var int
     */
    public const MAX_LOGIN_ATTEMPTS = 255;

    /**
     * Maximum length that a user last login IP address can be.
     *
     * @var int
     */
    public const MAX_LOGIN_LAST_IP_LENGTH = 39;

    /**
     * User constructor.
     *
     * @param array $user
     * @return void
     */
    public function __construct(array $user = null) {
        if (isset($user['user_id'])) $this->id = $user['user_id'];
        if (isset($user['username'])) $this->pending_username = $user['username'];
        if (isset($user['password'])) $this->pending_password = $user['password'];
        if (isset($user['inactive'])) $this->pending_inactive = (bool) $user['inactive'];
        if (isset($user['inactive_time'])) $this->inactive_time = $user['inactive_time'];
        if (isset($user['created_by'])) $this->created_by = $user['created_by'];
        if (isset($user['created_on'])) $this->created_on = $user['created_on'];
        if (isset($user['login_attempts'])) $this->login_attempts = $user['login_attempts'];
        if (isset($user['login_last_attempt'])) $this->login_last_attempt = $user['login_last_attempt'];
        if (isset($user['login_last_success'])) $this->login_last_success = $user['login_last_success'];
        if (isset($user['login_last_ip'])) $this->login_last_ip = $user['login_last_ip'];
        $this->save();
    }

    /**
     * Set the user ID.
     *
     * @param int $id
     * @return User
     */
    public function setId(int $id): User
    {
        $this->id = $id;
        return $this;
    }

    /**
     * Set the username.
     *
     * @param string $username
     * @return User
     */
    public function setUsername(string $username): User
    {
        $this->pending_username = $username;
        return $this;
    }

    /**
     * Set the password (HASHED).
     *
     * @param string $password
     * @return User
     */
    public function setPassword(string $password): User
    {
        $this->pending_password = $password;
        return $this;
    }

    /**
     * Change the password.
     *
     * @param string $password
     * @return User
     */
    public function changePassword(string $password): User
    {
        $this->setPassword(password_hash($password, PASSWORD_ARGON2I));
        return $this;
    }

    /**
     * Verify to see if the given password matches the current password.
     *
     * @param string $password
     * @return bool
     */
    public function verifyPassword(string $password): bool
    {
        return password_verify($password, $this->password);
    }

    /**
     * Set the inactive state.
     *
     * @param bool $inactive
     * @return User
     */
    public function setInactive(bool $inactive = true): User
    {
        $this->pending_inactive = $inactive;
        if ($inactive) {
            $this->setInactiveTime(date('Y-m-d H:i:s'));
        } else {
            $this->setInactiveTime(null);
        }
        return $this;
    }

    /**
     * Set the inactive time.
     *
     * @param string|null $inactive_time
     * @return User
     */
    public function setInactiveTime(?string $inactive_time): User
    {
        $this->inactive_time = $inactive_time;
        return $this;
    }

    /**
     * Set the user/entity that created the user.
     *
     * @param string|null $created_by
     * @return User
     */
    public function setCreatedBy(?string $created_by): User
    {
        $this->created_by = $created_by;
        return $this;
    }

    /**
     * Set the timestamp that the user was created on.
     *
     * @param string|null $created_on
     * @return User
     */
    public function setCreatedOn(?string $created_on): User
    {
        $this->created_on = $created_on;
        return $this;
    }

    /**
     * Set the user login attempts.
     *
     * @param int $login_attempts
     * @return User
     */
    public function setLoginAttempts(int $login_attempts): User
    {
        $this->login_attempts = $login_attempts;
        return $this;
    }

    /**
     * Increase the user's login attempts by 1.
     *
     * @return User
     */
    public function addLoginAttempt(): User
    {
        if ($this->login_attempts < self::MAX_LOGIN_ATTEMPTS) {
            $this->login_attempts++;
        } else {
            trigger_error('Login attempts are attempting to exceed maximum', E_USER_NOTICE);
        }
        return $this;
    }

    /**
     * Set the last login attempt for the user.
     *
     * @param string|null $login_last_attempt
     * @return User
     */
    public function setLoginLastAttempt(?string $login_last_attempt): User
    {
        if (!is_null($login_last_attempt)) {
            $this->login_last_attempt = date('Y-m-d H:i:s', strtotime($login_last_attempt));
        } else {
            $this->login_last_attempt = null;
        }
        return $this;
    }

    /**
     * Set the last successful login timestamp for the user.
     *
     * @param string|null $login_last_success
     * @return User
     */
    public function setLoginLastSuccess(?string $login_last_success): User
    {
        if (!is_null($login_last_success)) {
            $this->login_last_success = date('Y-m-d H:i:s', strtotime($login_last_success));
        } else {
            $this->login_last_success = null;
        }
        return $this;
    }

    /**
     * Set the last IP address the user was logged in from.
     *
     * @param string|null $login_last_ip
     * @return User
     */
    public function setLoginLastIp(?string $login_last_ip): User
    {
        $this->login_last_ip = $login_last_ip;
        return $this;
    }

    /**
     * Save the users state and update its old values.
     *
     * @return User
     */
    public function save(): User
    {
        $this->username = $this->pending_username;
        $this->password = $this->pending_password;
        $this->inactive = $this->pending_inactive;
        return $this;
    }

    /**
     * Check to see if the username has changed between modifications.
     *
     * @return bool
     * @noinspection PhpUnused
     */
    public function usernameChanged(): bool
    {
        return $this->username !== $this->pending_username;
    }

    /**
     * Check to see if the password has changed between modifications.
     *
     * @return bool
     * @noinspection PhpUnused
     */
    public function passwordChanged(): bool
    {
        return $this->password !== $this->pending_password;
    }

    /**
     * Check to see if the inactive state has changed between modifications.
     *
     * @return bool
     * @noinspection PhpUnused
     */
    public function inactiveChanged(): bool
    {
        return $this->inactive !== $this->pending_inactive;
    }

    /**
     * Get the user as an array.
     *
     * Password is omitted for security reasons.
     *
     * @return array
     * @noinspection PhpUnused
     */
    public function asArray(): array
    {
        return [
            'user_id' => $this->id,
            'username' => $this->username,
            'inactive' => $this->inactive,
            'inactive_time' => $this->inactive_time,
            'created_by' => $this->created_by,
            'created_on' => $this->created_on,
            'login_attempts' => $this->login_attempts,
            'login_last_attempt' => $this->login_last_attempt,
            'login_last_success' => $this->login_last_success,
            'login_last_ip' => $this->login_last_ip
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
        if (strlen($this->pending_username) > self::MAX_USERNAME_LENGTH) {
            $errors['username'] = "$this->pending_username > " .  self::MAX_USERNAME_LENGTH . ' characters';
        }
        if (strlen($this->pending_password) > self::MAX_PASSWORD_LENGTH) {
            $errors['password'] = "$this->pending_password > " .  self::MAX_PASSWORD_LENGTH . ' characters';
        }
        if (strlen($this->created_by) > self::MAX_CREATED_BY_LENGTH) {
            $errors['created_by'] = "$this->created_by > " .  self::MAX_CREATED_BY_LENGTH . ' characters';
        }
        if ($this->login_attempts > self::MAX_LOGIN_ATTEMPTS) {
            $errors['login_attempts'] = "$this->login_attempts > " .  self::MAX_LOGIN_ATTEMPTS;
        }
        if (strlen($this->login_last_ip) > self::MAX_LOGIN_LAST_IP_LENGTH) {
            $errors['login_last_ip'] = "$this->login_last_ip > " .  self::MAX_LOGIN_LAST_IP_LENGTH . ' characters';
        }
        if (!empty($errors)) {
            $message = 'User has data that exceeds database limits' . PHP_EOL;
            foreach ($errors as $k => $v) {
                $message .= "  -- $k: $v" . PHP_EOL;
            }
            throw new PDOException($message);
        } else {
            return true;
        }
    }

}