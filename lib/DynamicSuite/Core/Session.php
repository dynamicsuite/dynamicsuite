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

use DynamicSuite\Database\Query;
use DynamicSuite\Storable\User;
use Exception;
use PDO;
use PDOException;

/**
 * Class Session.
 *
 * @package DynamicSuite\Core
 */
final class Session
{

    /**
     * The session ID.
     *
     * @var string|null
     */
    public static ?string $id = null;

    /**
     * Array of the current user's permissions (Array of shorthands).
     *
     * @var string[]
     */
    public static array $permissions = [];

    /**
     * The current user's ID.
     *
     * @var int|null
     */
    public static ?int $user_id = null;

    /**
     * A friendly user name for display in messages and logs.
     *
     * @var string|null
     */
    public static ?string $user_name = null;

    /**
     * If the current user is a root user (bypasses permissions).
     *
     * @var bool
     */
    public static bool $root = false;

    /**
     * Initialize the user session.
     *
     * @return void
     */
    public static function init(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        if (isset($_SESSION['user_id'])) {
            try {
                if (!$user = User::readById($_SESSION['user_id'])) {
                    return;
                }
                self::$id = session_id();
                self::$permissions = (new Query())
                    ->select([Query::concat(['package_id', 'name'], ':', 'permission')])
                    ->from('ds_groups_permissions')
                    ->join('ds_permissions')
                    ->on('ds_groups_permissions.permission_id', '=', 'ds_permissions.permission_id')
                    ->where('group_id', 'IN', (new Query())
                        ->select(['group_id'])
                        ->from('ds_users_groups')
                        ->where('user_id', '=', $_SESSION['user_id']))
                    ->groupBy('ds_groups_permissions.permission_id')
                    ->execute(false, PDO::FETCH_COLUMN);
                self::$user_id = $user->user_id;
            } catch (PDOException | Exception $exception) {
                error_log($exception->getMessage(), E_USER_WARNING);
                return;
            }
        }
    }

    /**
     * Create and generate the session (post-authorization).
     *
     * @param int $user_id
     * @return void
     */
    public static function create(int $user_id): void
    {
        $_SESSION['user_id'] = $user_id;
        self::init();
    }

    /**
     * Destroy the current authenticated session.
     *
     * @return void
     */
    public static function destroy(): void
    {
        self::$id = null;
        self::$permissions = [];
        self::$user_id = null;
        self::$root = false;
        $_SESSION = null;
        session_destroy();
    }

    /**
     * Check to see if the currently authenticated user has the given permission(s).
     *
     * $permission can either be a shorthand permission string or an array of shorthand strings.
     *
     * @param string|string[] $permissions
     * @return bool
     */
    public static function checkPermissions($permissions): bool
    {
        if (self::$id === null) {
            return false;
        }
        if (self::$root) {
            return true;
        }
        if ($permissions === null) {
            return true;
        }
        if (empty($permissions)) {
            return true;
        }
        if (is_string($permissions) && !in_array($permissions, self::$permissions)) {
            return false;
        } elseif (is_array($permissions)) {
            foreach ($permissions as $permission) {
                if (!is_string($permission)) {
                    trigger_error('Permission values must be strings when permissions are an array', E_USER_WARNING);
                    return false;
                }
                if (!in_array($permission, self::$permissions)){
                    return false;
                }
            }
        } else {
            trigger_error('Invalid permission check type', E_USER_WARNING);
            return false;
        }
        return true;
    }

    /**
     * Check to see if the session is valid.
     *
     * @return bool
     */
    public static function isValid(): bool
    {
        return isset(self::$id, self::$user_id);
    }

}