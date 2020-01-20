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
use DynamicSuite\Base\ProtectedObject;

/**
 * Class Request.
 *
 * @package DynamicSuite\Core
 * @property string[] $url_array
 * @property string $url_string
 */
class Request extends ProtectedObject
{

    /**
     * Array of URL components.
     *
     * @var string[]
     */
    protected array $url_array = [];

    /**
     * String URL with stripped GET parameters.
     *
     * @var string
     */
    protected string $url_string = '';

    /**
     * Initialize the request.
     *
     * @param string $url
     * @return void
     */
    public function initViewable(string $url = null): void
    {
        $this->setUrl($url);
    }

    /**
     * Redirect the request.
     *
     * Ends script execution as well.
     *
     * @param string $path
     * @return void
     */
    public function redirect(string $path): void
    {
        header("Location: $path");
        exit;
    }

    /**
     * Set the URL parameters (strip GET).
     *
     * @param string $url
     * @return void
     */
    public function setUrl(string $url = null): void
    {
        $url = $url ?? $_SERVER['REQUEST_URI'] ?? '/';
        $pos = strpos($url, '?');
        if ($pos !== false) {
            $url = substr($url, 0, $pos);
        }
        $this->url_string = rtrim($url, '/');
        $this->url_array = explode('/',  trim($url, '/'));
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
    public function urlKey(int $key, string $value, bool $strict = false): bool
    {
        if (!isset($this->url_array[$key])) {
            return false;
        } elseif ($strict) {
            return $this->url_array[$key] === $value;
        } else {
            return strcasecmp($this->url_array[$key], $value) === 0;
        }
    }

    /**
     * Check to see if the input URL matches the current URL or given.
     *
     * @param string $needle
     * @param string $haystack
     * @return bool
     */
    public function urlIs(string $needle, string $haystack = null): bool
    {
        return rtrim($needle, '/') === ($haystack ?? $this->url_string);
    }

    /**
     * Check if the instance is a viewable instance.
     *
     * @return bool
     */
    public static function isViewable(): bool
    {
        return
            defined('DS_VIEW') || (
                isset($_SERVER, $_SERVER['REQUEST_URI']) &&
                $_SERVER['REQUEST_URI'] !== '/dynamicsuite/api'
            );
    }

    /**
     * Check if the instance is an API initialized instance.
     *
     * @return bool
     */
    public static function isApi(): bool
    {
        return
            defined('DS_API') || (
                isset(
                    $_SERVER,
                    $_SERVER['REQUEST_URI'],
                    $_SERVER['REQUEST_METHOD']
                ) &&
                $_SERVER['REQUEST_URI'] === '/dynamicsuite/api' &&
                $_SERVER['REQUEST_METHOD'] === 'POST'
            );
    }

    /**
     * Check if the instance is a command-line initialized instance.
     *
     * @return bool
     */
    public static function isCli(): bool
    {
        return defined('DS_CLI') || defined('STDIN');
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