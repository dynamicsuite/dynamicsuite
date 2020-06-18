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

use DynamicSuite\Util\Format;
use Exception;

/**
 * Class Package.
 *
 * @package DynamicSuite\Package
 * @property string $package_id
 * @property string|null $name
 * @property string|null $author
 * @property string|null $version
 * @property string|null $description
 * @property string|null $license
 * @property array[] $global
 * @property array[] $local
 * @property NavGroup[] $nav_groups
 * @property string[] $action_groups
 * @property ActionLink[] $action_links
 * @property View[] $views
 * @property Api[] $apis
 */
final class Package
{

    /**
     * Package ID.
     *
     * @var string
     */
    protected string $package_id;

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
     * @var NavGroup[]
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
     * @var ActionLink[]
     */
    protected array $action_links = [];

    /**
     * Package views.
     *
     * @var View[]
     */
    protected array $views = [];

    /**
     * Package APIs.
     *
     * @var Api[]
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
     * $group must be a string, "global" or "local"
     *
     * $structure is the referenced structure array for the package.
     *
     * @param string $group
     * @param array $structure
     * @return bool
     * @noinspection PhpIllegalStringOffsetInspection
     */
    private function loadResourceGroup(string $group, $structure): bool
    {
        if (!isset($structure[$group])) {
            return false;
        }
        if (!is_array($structure[$group])) {
            error_log("[Package Structure] Resource group `$group` must be an array for package `$this->package_id`");
            return false;
        }
        $types = ['autoload', 'init', 'js', 'css'];
        $error_log = function (string $group, string $key, string $message) {
            error_log("[Package Structure] Resource group `$group`.`$key` $message for package `$this->package_id`");
        };
        foreach ($types as $type) {
            if (!array_key_exists($type, $structure[$group])) {
                continue;
            }
            if (!is_array($structure[$group][$type]) && !is_string($structure[$group][$type])) {
                $error_log($group, $type, 'must be a string or an array of strings (file paths)');
                continue;
            }
            if (is_string($structure[$group][$type])) {
                $structure[$group][$type] = [$structure[$group][$type]];
            }
            foreach ($structure[$group][$type] as $path) {
                if ($type === 'autoload' || $type === 'init') {
                    $path = Format::formatServerPath($this->package_id, $path);
                } else {
                    $path = Format::formatClientPath($this->package_id, $path);
                }
                if (!in_array($path, $this->$group[$type])) {
                    $this->$group[$type][] = $path;
                }
                if ($group === 'global' && !in_array($path, Packages::$global[$type])) {
                    Packages::$global[$type][] = $path;
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
    private function loadNavGroups(array $structure): bool
    {
        if (!isset($structure['nav_groups'])) {
            return false;
        }
        if (!is_array($structure['nav_groups'])) {
            error_log("[Package Structure] `nav_groups` must be an array for package `$this->package_id`");
            return false;
        }
        foreach ($structure['nav_groups'] as $group_id => $group) {
            try {
                $this->nav_groups[$group_id] = new NavGroup($group_id, $this->package_id, $group);
            } catch (Exception $exception) {
                error_log($exception->getMessage());
                continue;
            }
        }
        return true;
    }

    /**
     * Load package action groups.
     *
     * @param array $structure
     * @return bool
     */
    private function loadActionGroups(array $structure): bool
    {
        if (!isset($structure['action_groups'])) {
            return false;
        }
        $error = function (string $message) {
            return "[Package Structure] `action_groups` $message for `$this->package_id`";
        };
        if (is_string($structure['action_groups'])) {
            $structure['action_groups'] = [$structure['action_groups']];
        } elseif ($structure['action_groups'] === null) {
            $structure['action_groups'] = [];
        } elseif (is_array($structure['action_groups'])) {
            foreach ($structure['action_groups'] as $group) {
                if (!is_string($group)) {
                    error_log($error('must be a string or array of strings'));
                    return false;
                }
            }
        } else {
            error_log($error('must be a string or array of strings'));
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
    private function loadActionLinks(array $structure): bool
    {
        if (!isset($structure['action_links'])) {
            return false;
        }
        if (!is_array($structure['action_links'])) {
            error_log("[Package Structure] `action_links` must be an array for package `$this->package_id`");
            return false;
        }
        foreach ($structure['action_links'] as $link_id => $link) {
            try {
                $this->action_links[$link_id] = new ActionLink($link_id, $this->package_id, $link);
            } catch (Exception $exception) {
                error_log($exception->getMessage());
                continue;
            }
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
    private function loadViews(array $structure): bool
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
        foreach ($structure['views'] as $view_id => $view) {
            try {
                $this->views[$view_id] = new View($view_id, $this->package_id, $view);
            } catch (Exception $exception) {
                error_log($exception->getMessage());
                continue;
            }
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
    private function loadApis(array $structure): bool
    {
        if (!isset($structure['apis'])) {
            return false;
        }
        if (!is_array($structure['apis'])) {
            error_log("[Package Structure] `apis` must be an array of APIs for package `$this->package_id`");
            return false;
        }
        foreach ($structure['apis'] as $api_id => $api) {
            try {
                $this->apis[$api_id] = new Api($api_id, $this->package_id, $api);
            } catch (Exception $exception) {
                error_log($exception->getMessage());
                continue;
            }
        }
        return true;
    }

}