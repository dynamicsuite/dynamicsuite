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

/**
 * Class Request.
 *
 * @package DynamicSuite
 * @property array $uri_array
 * @property string $uri_string
 */
class Request extends InstanceMember
{

    /**
     * Array of URI components.
     *
     * @var array
     */
    protected $uri_array;

    /**
     * String URI with stripped GET parameters.
     *
     * @var string
     */
    protected $uri_string;

    /**
     * Request constructor.
     *
     * @param Instance $ds
     */
    public function __construct(Instance $ds)
    {
        parent::__construct($ds);
    }

    /**
     * Initialize the request.
     *
     * @param string $uri
     * @return void
     */
    public function initViewable(string $uri = null): void
    {
        $this->setUri($uri);
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
     * Set the URI parameters (strip GET).
     *
     * @param string $uri
     * @return void
     */
    public function setUri(string $uri = null): void
    {
        $uri = $uri ?? $_SERVER['REQUEST_URI'] ?? '/';
        $pos = strpos($uri, '?');
        if ($pos !== false) {
            $uri = substr($uri, 0, $pos);
        }
        $this->uri_string = rtrim($uri, '/');
        $this->uri_array = explode('/',  trim($uri, '/'));
    }

    /**
     * Checks if URI at the given key matches the given value.
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
    public function uriKey(int $key, string $value, bool $strict = false): bool
    {
        if (!isset($this->uri_array[$key])) {
            return false;
        } elseif ($strict) {
            return $this->uri_array[$key] === $value;
        } else {
            return strcasecmp($this->uri_array[$key], $value) === 0;
        }
    }

    /**
     * Check to see if the input URI matches the current URI or given.
     *
     * @param string $needle
     * @param string $haystack
     * @return bool
     */
    public function uriIs(string $needle, string $haystack = null): bool
    {
        return rtrim($needle, '/') === ($haystack ?? $this->uri_string);
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