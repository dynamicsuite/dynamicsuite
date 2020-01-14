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

namespace DynamicSuite\Base;

/**
 * Class DSConfig.
 *
 * @package DynamicSuite\Base
 */
abstract class DSConfig extends ProtectedObject
{

    /**
     * DSConfig constructor.
     *
     * @param string $package_id
     * @return void
     */
    public function __construct(string $package_id)
    {
        if (file_exists("config/$package_id.json")) {
            $cfg = json_decode(file_get_contents("config/$package_id.json"), true);
            if ($cfg) {
                foreach ($cfg as $key => $value) $this->$key = $value;
            } else {
                trigger_error("Invalid config for $package_id! Using defaults...", E_USER_WARNING);
            }
        }
    }

}