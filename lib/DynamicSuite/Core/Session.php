<?php
/**
 * This file is part of the Dynamic Suite framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @package DynamicSuite\Core
 * @author Grant Martin <commgdog@gmail.com>
 * @copyright 2021 Dynamic Suite Team
 * @noinspection PhpUnused
 */

namespace DynamicSuite\Core;

/**
 * Class Session.
 *
 * @package DynamicSuite\Core
 */
final class Session
{

    /**
     * Permissions assigned to the session.
     *
     * @var string[]
     */
    public static array $permissions = [];

    /**
     * Root sessions bypass all permissions.
     *
     * @var bool
     */
    public static bool $root = false;

    /**
     * Initialize the session.
     *
     * @return void
     */
    public static function init(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    /**
     * Destroy the current authenticated session.
     *
     * @return void
     */
    public static function destroy(): void
    {
        self::$permissions = [];
        self::$root = false;
        $_SESSION = null;
        if (session_status() === PHP_SESSION_ACTIVE) {
            session_destroy();
        }
    }

    /**
     * Check the given permissions against the permissions assigned to the session.
     *
     * @param string|string[]|null $given
     * @return bool
     */
    public static function checkPermissions(string | array | null $given): bool
    {
        if (self::$root) {
            return true;
        }
        if ($given === null) {
            return true;
        }
        if (is_array($given) && empty($given)) {
            return true;
        }
        if (is_string($given)) {
            if (str_contains($given, '|')) {
                return self::permissionOrCheck($given);
            }
            return in_array($given, self::$permissions);
        }
        foreach ($given as $permission) {
            if (!is_string($permission)) {
                trigger_error('Permissions must be an array of strings', E_USER_WARNING);
                return false;
            }
            if (str_contains($permission, '|')) {
                return self::permissionOrCheck($permission);
            }
            return in_array($permission, self::$permissions);
        }
        return true;
    }

    /**
     * Check if one of the given permissions is on the session (permissions split via bar).
     *
     * @param string $permissions
     * @return bool
     */
    private static function permissionOrCheck(string $permissions): bool
    {
        $permissions = explode('|', $permissions);
        $check = false;
        foreach ($permissions as $permission) {
            if (in_array($permission, self::$permissions)) {
                $check = true;
            }
        }
        return $check;
    }

}