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

/**
 * Class Request.
 *
 * @package DynamicSuite\Core
 */
final class Request
{

    /**
     * String URL string with stripped GET parameters.
     *
     * @var string|null
     */
    public static ?string $url_string = null;

    /**
     * The URL string represented as an array indexed from 0.
     *
     * @var string[]|null
     */
    public static ?array $url_array = null;

    /**
     * Initialize the request URL.
     *
     * @return bool
     */
    public static function init(): bool
    {
        $url = $_SERVER['REQUEST_URI'] ?? '/';
        $pos = strpos($url, '?');
        if ($pos !== false) {
            $url = substr($url, 0, $pos);
        }
        self::$url_string = rtrim($url, '/');
        self::$url_array = explode('/',  trim($url, '/'));
        if (defined('STDIN')) {
            return false;
        } elseif (self::urlKey(0, 'dynamicsuite') && self::urlKey(1, 'api')) {
            define('DS_API', true);
            define('DS_VIEW', false);
        } else {
            define('DS_VIEW', true);
            define('DS_API', false);
        }
        return true;
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
        if (self::$url_array === null) {
            self::init();
        }
        if (!isset(self::$url_array[$key])) {
            return false;
        } elseif ($strict) {
            return self::$url_array[$key] === $value;
        } else {
            return strcasecmp(self::$url_array[$key], $value) === 0;
        }
    }

    /**
     * Check to see if the input URL matches the current URL or given.
     *
     * @param string $needle
     * @param string|null $haystack
     * @return bool
     */
    public static function urlIs(string $needle, string $haystack = null): bool
    {
        if (self::$url_string === null) {
            self::init();
        }
        return rtrim($needle, '/') === ($haystack ?? self::$url_string);
    }

    /**
     * Get the time since execution began.
     *
     * $factor is a value to multiply the result by.
     * The default is 1, which will give the result in seconds.
     * 1000 = ms, 1e6 = microseconds, etc.
     *
     * @param int $factor
     * @return float
     */
    public static function executionTime(int $factor = 1): float
    {
        return (microtime(true) - DS_START) * $factor;
    }

}