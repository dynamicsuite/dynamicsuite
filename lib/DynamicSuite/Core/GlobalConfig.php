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
 * Class GlobalConfig.
 *
 * @package DynamicSuite\Core
 */
abstract class GlobalConfig
{

    /**
     * DSConfig constructor.
     *
     * @param string $package_id
     */
    public function __construct(string $package_id)
    {
        $path = DS_ROOT_DIR . "/config/$package_id.json";
        if (!file_exists($path)) {
            return;
        }
        $hash = md5($path);
        if (DS_CACHING && apcu_exists($hash)) {
            $cfg = apcu_fetch($hash);
            if ($cfg === false) {
                error_log("Error fetching config from cache for $package_id", E_USER_WARNING);
            }
        } elseif (is_readable($path)) {
            $cfg = json_decode(file_get_contents($path), true);
            if ($cfg === null) {
                error_log("Error decoding config json for $package_id ($path)", E_USER_WARNING);
            } elseif (DS_CACHING) {
                apcu_store($hash, $cfg);
            }
        } else {
            error_log("Config not readable for $package_id ($path)", E_USER_WARNING);
            $cfg = false;
        }
        if ($cfg) {
            foreach ($cfg as $property => $value) {
                $this->$property = $value;
            }
        } else {
            trigger_error("Invalid config for $package_id, using defaults...", E_USER_WARNING);
        }
    }

    /**
     * Parameter getter magic method.
     *
     * @param string $property
     * @return mixed
     */
    public function __get(string $property)
    {
        return $this->$property;
    }

}