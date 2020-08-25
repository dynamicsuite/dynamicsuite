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
 * Class Format.
 *
 * @package DynamicSuite\Util
 */
final class Format
{

    /**
     * Format a server file path.
     *
     * @param string $package_id
     * @param string $path
     * @return string
     */
    public static function formatServerPath(string $package_id, string $path): string
    {
        return $path[0] === '/' ? DS_ROOT_DIR . $path : DS_ROOT_DIR . "/packages/$package_id/$path";
    }

    /**
     * Format a client resource path.
     *
     * @param string $package_id
     * @param string $path
     * @return string
     */
    public static function formatClientPath(string $package_id, string $path): string
    {
        return $path[0] === '/' ? $path : "/dynamicsuite/packages/$package_id/$path";
    }

}