<?php
/**
 * This file is part of the Dynamic Suite framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @package DynamicSuite
 * @author Grant Martin <commgdog@gmail.com>
 * @copyright 2021 Dynamic Suite Team
 * @noinspection PhpUnused PhpNoReturnAttributeCanBeAddedInspection
 */

namespace DynamicSuite;

/**
 * Class URL.
 *
 * @package DynamicSuite
 */
final class URL
{

    /**
     * String URL string with stripped GET parameters.
     *
     * @var string|null
     */
    public static ?string $as_string = null;

    /**
     * The URL string represented as an array indexed from 0.
     *
     * @var string[]|null
     */
    public static ?array $as_array = null;

    /**
     * Initialize the URL.
     *
     * @return void
     */
    public static function init(): void
    {
        $url = $_SERVER['REQUEST_URI'] ?? '/';
        $pos = strpos($url, '?');
        if ($pos !== false) {
            $url = substr($url, 0, $pos);
        }
        self::$as_string = rtrim($url, '/');
        self::$as_array = explode('/',  trim($url, '/'));
        define('DS_API', !defined('STDIN') && str_starts_with(self::$as_string, '/dynamicsuite/api'));
        define('DS_VIEW', !defined('STDIN') && !DS_API);
    }

    /**
     * Redirect the request.
     *
     * Ends script execution as well.
     *
     * @param string $path
     * @return void
     */
    public static function redirect(string $path): void
    {
        header("Location: $path");
        exit;
    }

    /**
     * Checks if URL at the given key matches the given value.
     *
     * If $strict is set, the value must match the index case and type.
     *
     * The default behavior is case insensitivity/no type check.
     *
     * @param int $key
     * @param string $value
     * @param bool $strict
     * @return bool
     */
    public static function urlKey(int $key, string $value, bool $strict = false): bool
    {
        if (!isset(self::$as_array[$key])) {
            return false;
        } elseif ($strict) {
            return self::$as_array[$key] === $value;
        } else {
            return strcasecmp(self::$as_array[$key], $value) === 0;
        }
    }

}