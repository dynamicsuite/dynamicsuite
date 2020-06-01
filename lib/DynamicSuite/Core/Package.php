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
 * Class Package.
 *
 * @package DynamicSuite\Core
 * @property string $package_id
 * @property string|null $name
 * @property string|null $author
 * @property string|null $version
 * @property string|null $description
 * @property string|null $license
 * @property array[] $global
 * @property array[] $local
 * @property array[] $nav_groups
 * @property string[] $action_groups
 * @property array[] $action_links
 * @property array[] $views
 * @property array[] $apis
 */
final class Package
{

    /**
     * Package ID.
     *
     * @var string
     */
    private string $package_id;

    /**
     * Friendly package name.
     *
     * @var string|null
     */
    protected ?string $name = null;

    /**
     * Package author.
     *
     * @var string|null
     */
    protected ?string $author = null;

    /**
     * Package version.
     *
     * @var string|null
     */
    protected ?string $version = null;

    /**
     * Package description.
     *
     * @var string|null
     */
    protected ?string $description = null;

    /**
     * Package license.
     *
     * @var string|null
     */
    protected ?string $license = null;

    /**
     * Global package resource paths.
     *
     * @var array[]
     */
    protected array $global = ['autoload' => [], 'init' => [], 'js' => [], 'css' => []];

    /**
     * Local package resource paths.
     *
     * @var array[]
     */
    protected array $local = ['autoload' => [], 'init' => [], 'js' => [], 'css' => []];

    /**
     * Provided navigational groups.
     *
     * @var array[]
     */
    protected array $nav_groups = [];

    /**
     * Provided action groups.
     *
     * @var string[]
     */
    protected array $action_groups = [];

    /**
     * Provided action links
     *
     * @var array[]
     */
    protected array $action_links = [];

    /**
     * Package views.
     *
     * @var array[]
     */
    protected array $views = [];

    /**
     * Package APIs.
     *
     * @var array[]
     */
    protected array $apis = [];

    /**
     * Package constructor.
     *
     * @param string $package_id
     * @param array $structure
     * @return void
     */
    public function __construct(string $package_id, array $structure)
    {
        $this->package_id = $package_id;
        $this->name = $structure['name'] ?? $package_id;
        $this->author = $structure['author'] ?? 'Anonymous';
        $this->version = $structure['version'] ?? '0.0.0';
        $this->description = $structure['description'] ?? 'N/A';
        $this->license = $structure['license'] ?? 'none';
        $this->loadResourceGroup('global', $structure);
        $this->loadResourceGroup('local', $structure);
        $this->loadNavGroups($structure);
        $this->loadActionGroups($structure);
        $this->loadActionLinks($structure);
        $this->loadViews($structure);
        $this->loadApis($structure);
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

    /**
     * Load a resource group.
     *
     * $type must be a string, "global" or "local"
     *
     * $structure is the referenced structure array for the package.
     *
     * @param string $type
     * @param array $structure
     * @return bool
     * @noinspection PhpIllegalStringOffsetInspection
     */
    private function loadResourceGroup(string $type, $structure): bool
    {
        if (!isset($structure[$type])) {
            return false;
        }
        if (!is_array($structure[$type])) {
            error_log("[Package Structure] Resource group `$type` must be an array for package `$this->package_id`");
            return false;
        }
        $keys = ['autoload', 'init', 'js', 'css'];
        $error_log = function (string $type, string $key, string $message) {
            error_log("[Package Structure] Resource group `$type`.`$key` $message for package `$this->package_id`");
        };
        foreach ($keys as $key) {
            if (!array_key_exists($key, $structure[$type])) {
                continue;
            }
            if ((!is_array($structure[$type][$key]) && !is_string($structure[$type][$key])) ||
                is_array($structure[$type][$key]) &&
                !self::isArrayOfStrings($structure[$type][$key]
            )) {
                $error_log($type, $key, 'must be a string or an array of strings (file paths)');
                continue;
            }
            if (is_string($structure[$type][$key])) {
                if ($key === 'autoload' || $key === 'init') {
                    $path = $this->formatServerPath($structure[$type][$key]);
                } else {
                    $path = $this->formatClientPath($structure[$type][$key]);
                }
                if (!in_array($path, $this->$type[$key])) {
                    $this->$type[$key][] = $path;
                }
                if ($type === 'global' && !in_array($path, Packages::$global[$key])) {
                    Packages::$global[$key][] = $path;
                }
            } else {
                foreach ($structure[$type][$key] as $path) {
                    if ($key === 'autoload' || $key === 'init') {
                        $path = $this->formatServerPath($path);
                    } else {
                        $path = $this->formatClientPath($path);
                    }
                    if (!in_array($path, $this->$type[$key])) {
                        $this->$type[$key][] = $path;
                    }
                    if ($type === 'global' && !in_array($path, Packages::$global[$key])) {
                        Packages::$global[$key][] = $path;
                    }
                }
            }
        }
        return true;
    }

    /**
     * Load navigational groups.
     *
     * $structure is the referenced structure array for the package.
     *
     * @param array $structure
     * @return bool
     */
    private function loadNavGroups($structure): bool
    {
        if (!isset($structure['nav_groups'])) {
            return false;
        }
        if (!is_array($structure['nav_groups'])) {
            error_log("[Package Structure] `nav_groups` must be an array for package `$this->package_id`");
            return false;
        }
        $error_log = function (string $group_id, string $key, string $message) {
            error_log("[Package Structure] Nav group `$group_id`.`$key` $message for package `$this->package_id`");
        };
        foreach ($structure['nav_groups'] as $group_id => $group) {
            $group['name'] ??= $group_id;
            if (!is_string($group['name'])) {
                $error_log($group_id, 'name', 'must be a a string');
                continue;
            }
            $group['icon'] ??= 'fas fa-cogs';
            if (!is_string($group['icon'])) {
                $error_log($group_id, 'icon', 'must be a a string (FontAwesome class)');
                continue;
            }
            $group['public'] ??= false;
            if (!is_bool($group['public'])) {
                $error_log($group_id, 'public', 'must be boolean');
                continue;
            }
            $group['permissions'] ??= null;
            if (($group['permissions'] !== null && !is_array($group['permissions'])) || (
                is_array($group['permissions']) &&
                !self::isArrayOfStrings($group['permissions']
            ))) {
                $error_log($group_id, 'permissions', 'must be null or an array of strings (shorthand permissions)');
                continue;
            }
            $this->nav_groups[$group_id] = $group;
        }
        return true;
    }

    /**
     * Load package action groups.
     *
     * @param string[] $structure
     * @return bool
     */
    private function loadActionGroups($structure): bool
    {
        if (!isset($structure['action_groups'])) {
            return false;
        }
        if (!is_array($structure['action_groups']) || !self::isArrayOfStrings($structure['action_groups'])) {
            error_log(
                "[Package Structure] `action_groups` must be an array of strings for package `$this->package_id`"
            );
            return false;
        }
        $this->action_groups = $structure['action_groups'];
        return true;
    }

    /**
     * Load package action links.
     *
     * @param array $structure
     * @return bool
     */
    private function loadActionLinks($structure): bool
    {
        if (!isset($structure['action_links'])) {
            return false;
        }
        if (!is_array($structure['action_links'])) {
            error_log("[Package Structure] `action_links` must be an array for package `$this->package_id`");
            return false;
        }
        $error_log = function (string $link_id, string $key, string $message) {
            error_log("[Package Structure] Action link `$link_id`.`$key` $message for package `$this->package_id`");
        };
        foreach ($structure['action_links'] as $link_id => $link) {
            $link['type'] ??= 'static';
            if ($link['type'] !== 'static' && $link['type'] !== 'dynamic') {
                $error_log($link_id, 'type', 'must be a string (static or dynamic)');
                continue;
            }
            if (!isset($link['value']) || !is_string($link['value'])) {
                $error_log($link_id, 'value', 'must be a string (file path or hyperlink path)');
                continue;
            }
            if ($link['type'] === 'dynamic') {
                $link['value'] = self::formatServerPath($link['value']);
            }
            if (!isset($link['group']) || !is_string($link['group'])) {
                $error_log($link_id, 'group', 'must be set as a string (associated link group ID)');
                continue;
            }
            $link['permissions'] ??= null;
            if (($link['permissions'] !== null && !is_array($link['permissions'])) || (
                is_array($link['permissions']) &&
                !self::isArrayOfStrings($link['permissions']
            ))) {
                $error_log($link_id, 'permissions', 'must be null or an array of strings (shorthand permissions)');
                continue;
            }
            $this->action_links[$link_id] = $link;
        }
        return true;
    }

    /**
     * Load package views.
     *
     * $structure is the referenced structure array for the package.
     *
     * @param array $structure
     * @return bool
     */
    private function loadViews($structure): bool
    {
        if (!isset($structure['views'])) {
            return false;
        }
        if (!is_array($structure['views'])) {
            error_log(
                "[Package Structure] `views` must be an array of valid views for package `$this->package_id`"
            );
            return false;
        }
        $error_log = function (string $view_id, string $key, string $message) {
            error_log("[Package Structure] View `$view_id`.`$key` $message for package `$this->package_id`");
        };
        foreach ($structure['views'] as $view_id => $view) {
            if (!isset($view['entry']) || !is_string($view['entry'])) {
                $error_log($view_id, 'entry', 'must be set and set as a string (file path)');
                continue;
            }
            $view['entry'] = self::formatServerPath($view['entry']);
            $view['title'] ??= $view_id;
            if (!is_string($view['title'])) {{
                $error_log($view_id, 'title', 'must be a string (HTML title)');
                continue;
            }}
            $view['public'] ??= false;
            if (!is_bool($view['public'])) {
                $error_log($view_id, 'public', 'must be a boolean');
                continue;
            }
            $view['navigable'] ??= true;
            if (!is_bool($view['navigable'])) {
                $error_log($view_id, 'navigable', 'must be a boolean');
                continue;
            }
            $view['hide_nav'] ??= false;
            if (!is_bool($view['hide_nav'])) {
                $error_log($view_id, 'hide_nav', 'must be a boolean');
                continue;
            }
            $view['hide_user_actions'] ??= false;
            if (!is_bool($view['hide_user_actions'])) {
                $error_log($view_id, 'hide_user_actions', 'must be a boolean');
                continue;
            }
            $view['hide_logout_button'] ??= false;
            if (!is_bool($view['hide_logout_button'])) {
                $error_log($view_id, 'hide_logout_button', 'must be a boolean');
                continue;
            }
            $view['nav_name'] ??= $view_id;
            if (!is_string($view['nav_name'])) {
                $error_log($view_id, 'nav_name', 'must be a string (navigational name)');
                continue;
            }
            $view['nav_icon'] ??= 'fas fa-cogs';
            if (!is_string($view['nav_icon'])) {
                $error_log($view_id, 'nav_icon', 'must be a string (FontAwesome icon class)');
                continue;
            }
            $view['nav_group'] ??= null;
            if ($view['nav_group'] !== null && !is_string($view['nav_group'])) {
                $error_log($view_id, 'nav_group', 'must be a string (navigational group ID)');
                continue;
            }
            $view['permissions'] ??= null;
            if (($view['permissions'] !== null && !is_array($view['permissions'])) || (
                is_array($view['permissions']) &&
                !self::isArrayOfStrings($view['permissions']
            ))) {
                $error_log($view_id, 'permissions', 'must be null or an array of strings (shorthand permissions)');
                continue;
            }
            $view['autoload'] ??= [];
            if ((!is_string($view['autoload']) && !is_array($view['autoload'])) || (
                is_array($view['autoload']) &&
                !self::isArrayOfStrings($view['autoload']
            ))) {
                $error_log($view_id, 'autoload', 'must be a string or array of strings (file paths)');
                continue;
            }
            if (is_string($view['autoload'])) {
                $view['autoload'] = self::formatServerPath($view['autoload']);
            } else {
                $view['autoload'] = array_map(fn(string $path) => self::formatServerPath($path), $view['autoload']);
            }
            $view['init'] ??= [];
            if ((!is_string($view['init']) && !is_array($view['init'])) || (
                is_array($view['init']) &&
                !self::isArrayOfStrings($view['init']
            ))) {
                $error_log($view_id, 'init', 'must be a string or array of strings (file paths)');
                continue;
            }
            if (is_string($view['init'])) {
                $view['init'] = self::formatServerPath($view['init']);
            } else {
                $view['init'] = array_map(fn(string $path) => self::formatServerPath($path), $view['init']);
            }
            $view['js'] ??= [];
            if ((!is_string($view['js']) && !is_array($view['js'])) || (
                is_array($view['js']) &&
                !self::isArrayOfStrings($view['js']
            ))) {
                $error_log($view_id, 'js', 'must be a string or array of strings (file paths)');
                continue;
            }
            if (is_string($view['js'])) {
                $view['js'] = self::formatClientPath($view['js']);
            } else {
                $view['js'] = array_map(fn(string $path) => self::formatClientPath($path), $view['js']);
            }
            $view['css'] ??= [];
            if ((!is_string($view['css']) && !is_array($view['css'])) || (
                is_array($view['css']) &&
                !self::isArrayOfStrings($view['css']
            ))) {
                $error_log($view_id, 'css', 'must be a string or array of strings (file paths)');
                continue;
            }
            if (is_string($view['css'])) {
                $view['css'] = self::formatClientPath($view['css']);
            } else {
                $view['css'] = array_map(fn(string $path) => self::formatClientPath($path), $view['css']);
            }
            $this->views[$view_id] = $view;
        }
        return true;
    }

    /**
     * Load package APIs.
     *
     * $structure is the referenced structure array for the package.
     *
     * @param array $structure
     * @return bool
     */
    private function loadApis($structure): bool
    {
        if (!isset($structure['apis'])) {
            return false;
        }
        if (!is_array($structure['apis'])) {
            error_log(
                "[Package Structure] `apis` must be an array of APIs for package `$this->package_id`"
            );
            return false;
        }
        $error_log = function (string $api_id, string $key, string $message) {
            error_log("[Package Structure] API `$api_id`.`$key` $message for package `$this->package_id`");
        };
        foreach ($structure['apis'] as $api_id => $api) {
            if (!isset($api['entry']) || !is_string($api['entry'])) {
                $error_log($api_id, 'entry', 'must be set and a string (file path)');
                continue;
            }
            $api['entry'] = self::formatServerPath($api['entry']);
            $api['post'] ??= null;
            if ($api['post'] !== null && !self::isArrayOfStrings($api['post'])) {
                $error_log($api_id, 'post', 'must be null or an array of strings (post keys)');
                continue;
            }
            $api['permissions'] ??= null;
            if (($api['permissions'] !== null && !is_array($api['permissions'])) || (
                is_array($api['permissions']) &&
                !self::isArrayOfStrings($api['permissions']
            ))) {
                $error_log($api_id, 'post', 'must be null or an array of strings (shorthand permissions)');
                continue;
            }
            $api['public'] ??= false;
            if (!is_bool($api['public'])) {
                $error_log($api_id, 'post', 'must be boolean');
                continue;
            }
            $api['autoload'] ??= [];
            if ((!is_string($api['autoload']) && !is_array($api['autoload'])) || (
                is_array($api['autoload']) &&
                !self::isArrayOfStrings($api['autoload']
            ))) {
                $error_log($api_id, 'post', 'must a string or an array of strings (file paths)');
                continue;
            }
            if (is_string($api['autoload'])) {
                $api['autoload'] = self::formatServerPath($api['autoload']);
            } else {
                $api['autoload'] = array_map(fn(string $path) => self::formatServerPath($path), $api['autoload']);
            }
            $api['init'] ??= [];
            if ((!is_string($api['init']) && !is_array($api['init'])) || (
                is_array($api['init']) &&
                !self::isArrayOfStrings($api['init']
            ))) {
                $error_log($api_id, 'post', 'must a string or an array of strings (file paths)');
                continue;
            }
            if (is_string($api['init'])) {
                $api['init'] = self::formatServerPath($api['init']);
            } else {
                $api['init'] = array_map(fn(string $path) => self::formatServerPath($path), $api['init']);
            }
            $this->apis[$api_id] = $api;
        }
        return true;
    }

    /**
     * Check to see if an array is an array of strings
     *
     * @param array $array
     * @return bool
     */
    private static function isArrayOfStrings(array $array) {
        foreach ($array as $value) {
            if (!is_string($value)) {
                return false;
            }
        }
        return true;
    }

    /**
     * Format a server file path.
     *
     * @param string $path
     * @return string
     */
    private function formatServerPath(string $path): string
    {
        return $path[0] === '/' ? DS_ROOT_DIR . $path : DS_ROOT_DIR . "/packages/$this->package_id/$path";
    }

    /**
     * Format a client resource path.
     *
     * @param string $path
     * @return string
     */
    private function formatClientPath(string $path): string
    {
        return $path[0] === '/' ? $path : "/dynamicsuite/packages/$this->package_id/$path";
    }

}