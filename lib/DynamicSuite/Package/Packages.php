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

namespace DynamicSuite\Package;
use DynamicSuite\Core\DynamicSuite;

/**
 * Class Packages.
 *
 * @package DynamicSuite\Package
 */
final class Packages
{

    /**
     * Loaded package structure files as an array.
     *
     * @var Package[]
     */
    public static array $loaded = [];

    /**
     * Loaded package views.
     *
     * @var View[]
     */
    public static array $views = [];

    /**
     * Loaded navigation groups.
     *
     * @var NavGroup[]
     */
    public static array $nav_groups = [];

    /**
     * Loaded action groups.
     *
     * @var string[]
     */
    public static array $action_groups = [];

    /**
     * Loaded action links.
     *
     * @var ActionLink[]
     */
    public static array $action_links = [];

    /**
     * Global resources defined by packages.
     *
     * @var array[]
     */
    public static array $global = [
        'autoload' => [],
        'init' => [],
        'js' => [],
        'css' => []
    ];

    /**
     * Initialize all package structures.
     *
     * Also sets any package defined global includes.
     *
     * @return void
     */
    public static function init(): void
    {
        $hash = md5(__FILE__);
        if (DS_CACHING && apcu_exists($hash) && $cache = apcu_fetch($hash)) {
            self::$loaded = $cache['loaded'];
            self::$global = $cache['global'];
            self::$views = $cache['views'];
            self::$nav_groups = $cache['nav_groups'];
            self::$action_groups = $cache['action_groups'];
            self::$action_links = $cache['action_links'];
        } else {
            foreach (DynamicSuite::$cfg->packages as $package_id) {
                self::load($package_id);
            }
            if (DS_CACHING) {
                $store = apcu_store($hash, [
                    'loaded' => self::$loaded,
                    'global' => self::$global,
                    'views' => self::$views,
                    'nav_groups' => self::$nav_groups,
                    'action_groups' => self::$action_groups,
                    'action_links' => self::$action_links
                ]);
                if (!$store) {
                    error_log('Error saving "Packages" in cache, check server config');
                }
            }
        }
    }

    /**
     * Parse a packages structure file and add it to the loaded packages list.
     *
     * Given a $package_id, it will look for the structure file at packages/$package_id/$package_id.json
     *
     * Returns TRUE on success and FALSE on failure (parse issue).
     *
     * Errors will be logged in the host log file.
     *
     * @param string $package_id
     * @return bool
     */
    public static function load(string $package_id): bool
    {
        $json_path = DS_ROOT_DIR . "/packages/$package_id/$package_id.json";
        if (!is_readable($json_path)) {
            error_log("Package missing structure: $package_id", E_USER_WARNING);
            return false;
        }
        if (!$structure = json_decode(file_get_contents($json_path), true)) {
            error_log("Package structure invalid: $package_id", E_USER_WARNING);
            return false;
        }
        self::$loaded[$package_id] = new Package($package_id, $structure);
        return true;
    }

}