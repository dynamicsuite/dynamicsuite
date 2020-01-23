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
use DynamicSuite\Base\InstanceMember;
use DynamicSuite\Core\DynamicSuite;
use DynamicSuite\Util\File;
use TypeError;

/**
 * Class Packages.
 *
 * @package DynamicSuite\Package
 * @property array $loaded
 * @property Resources $resources
 * @property array $views
 * @property array $nav_groups
 * @property array $nav_tree
 * @property array $apis
 */
final class Packages extends InstanceMember
{

    /**
     * All loaded packages.
     *
     * @var Structure[]
     */
    protected array $loaded = [];

    /**
     * Package resources global to all packages.
     *
     * @var Resources
     */
    protected Resources $resources;

    /**
     * All valid views.
     *
     * @var View[]
     */
    protected array $views = [];

    /**
     * Package navigation groups.
     *
     * @var NavGroup[]
     */
    protected array $nav_groups = [];

    /**
     * Package views navigation tree.
     *
     * @var NavEntry[]
     */
    protected array $nav_tree = [];

    /**
     * All package apis.
     *
     * @var API[]
     */
    protected array $apis = [];

    /**
     * Packages constructor.
     *
     * @param DynamicSuite $ds
     * @return void
     */
    public function __construct(DynamicSuite $ds)
    {
        parent::__construct($ds);
        $this->resources = new Resources();
    }

    /**
     * Load a package.
     *
     * @param string $package_id
     * @return Structure|bool
     */
    public function loadPackage(string $package_id)
    {
        $package_dir = "packages/$package_id";
        $json_path = "$package_dir/$package_id.json";
        if (!is_dir($package_dir)) {
            trigger_error("Package not found: $package_id", E_USER_WARNING);
            return false;
        }
        if (!is_readable($json_path)) {
            trigger_error("Package missing structure: $package_id", E_USER_WARNING);
            return false;
        }
        if (!$structure = File::jsonToArray($json_path)) {
            trigger_error("Package structure invalid: $package_id", E_USER_WARNING);
            return false;
        }
        try {
            $structure = new Structure($package_id, $structure);
        } catch (TypeError $exception) {
            trigger_error($exception->getMessage(), E_USER_WARNING);
            return false;
        }
        $this->resources->merge($structure->global_resources);
        $this->nav_groups = array_replace($this->nav_groups, $structure->nav_groups);
        $this->views = array_replace($this->views, $structure->views);
        $this->apis[$package_id] = $structure->apis;
        return $structure;
    }

    /**
     * Attempt to load all package ids defined in the config.
     *
     * @return bool
     */
    public function loadPackages()
    {
        if (!is_array($this->ds->cfg->packages)) {
            trigger_error("Invalid packages in global config", E_USER_WARNING);
            return false;
        }
        foreach ($this->ds->cfg->packages as $package_id) {
            if (!is_string($package_id)) {
                trigger_error("Invalid package value in global config", E_USER_WARNING);
                return false;
            }
            $package = $this->loadPackage($package_id);
            if ($package) $this->loaded[$package_id] = $package;
        }
        /** @var $view View */
        foreach ($this->views as $url => $view) {
            if (!$view->navigable) continue;
            $hash = $view->nav_group
                ? md5($view->nav_group)
                : md5("$view->package_id:$url");
            $child = new NavEntry();
            $child->name = $view->nav_name;
            $child->icon = $view->nav_icon;
            $child->url = $url;
            $child->public = $view->public;
            $child->permissions = $view->permissions;
            if ($view->nav_group) {
                if (!isset($this->nav_groups[$view->nav_group])) {
                    error_log("Orphaned view at $view->package_id:$view->nav_group", E_USER_WARNING);
                    continue;
                }
                if (isset($this->nav_tree[$hash])) {
                    $this->nav_tree[$hash]->addChild($child);
                } else {
                    $parent = new NavEntry();
                    $parent->name = $this->nav_groups[$view->nav_group]->name;
                    $parent->icon = $this->nav_groups[$view->nav_group]->icon;
                    $parent->public = $this->nav_groups[$view->nav_group]->public;
                    $parent->permissions = $this->nav_groups[$view->nav_group]->permissions;
                    $parent->addChild($child);
                    $this->nav_tree[$hash] = $parent;
                }
            } else {
                $this->nav_tree[$hash] = $child;
            }
        }
        return true;
    }

}