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

namespace DynamicSuite\Util;

/**
 * Class File.
 *
 * @package DynamicSuite\Util
 */
final class File
{

    /**
     * Check if a file is valid and return its contents.
     *
     * Returns the file contents as a string on success.
     *
     * Returns FALSE if the file is not valid.
     *
     * @param string $path
     * @return bool|string
     */
    public static function contents(string $path)
    {
        if (is_readable($path) && is_file($path)) {
            return file_get_contents($path);
        } else {
            trigger_error("Invalid path: $path", E_USER_WARNING);
            return false;
        }
    }

    /**
     * Check if a file is valid and parse its contents as JSON.
     *
     * Returns a parsed array/object on success.
     *
     * Returns FALSE if the $path is not valid.
     *
     * @param string $path
     * @param bool $as_array
     * @return bool|object|array
     */
    public static function jsonToArray(string $path, bool $as_array = true)
    {
        if (!$contents = self::contents($path)) {
            return false;
        }
        if (!$json = json_decode($contents, $as_array)) {
            trigger_error("Invalid json: $path", E_USER_WARNING);
            return false;
        }
        return $json;
    }

}