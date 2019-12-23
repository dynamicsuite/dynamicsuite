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
use TypeError;

/**
 * Class Packages.
 *
 * @package DynamicSuite
 * @property array $loaded
 * @property PackageResources $resources
 * @property array $views
 * @property array $nav_groups
 * @property array $nav_tree
 * @property array $apis
 */
class Packages extends InstanceMember
{

    /**
     * All loaded packages.
     *
     * @var Package[]
     */
    protected $loaded = [];

    /**
     * Package resources global to all packages.
     *
     * @var PackageResources
     */
    protected $resources;

    /**
     * All valid views.
     *
     * @var PackageView[]
     */
    protected $views = [];

    /**
     * Package navigation groups.
     *
     * @var PackageNavGroup[]
     */
    protected $nav_groups = [];

    /**
     * Package views navigation tree.
     *
     * @var PackageNavEntry[]
     */
    protected $nav_tree = [];

    /**
     * All package apis.
     *
     * @var PackageApi[]
     */
    protected $apis = [];

    /**
     * Packages constructor.
     *
     * @param Instance $ds
     * @return void
     */
    public function __construct(Instance $ds)
    {
        parent::__construct($ds);
        $this->resources = new PackageResources();
        $this->loadPackages();
    }

    /**
     * Load a package.
     *
     * @param string $package_id
     * @return Package|bool
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
        if (!$json = @File::asJson($json_path)) {
            trigger_error("Package structure invalid: $package_id", E_USER_WARNING);
            return false;
        }
        try {
            $package = (new Package($package_id))
                ->setName(@$json->name)
                ->setAuthor(@$json->author)
                ->setVersion(@$json->version)
                ->setDescription(@$json->description)
                ->setLicense(@$json->license)
                ->setGlobalResources(@$json->global)
                ->setLocalResources(@$json->local)
                ->setNavGroups(@$json->nav_groups)
                ->setViews(@$json->views)
                ->setApis(@$json->apis);
        } catch (TypeError $exception) {
            trigger_error($exception->getMessage(), E_USER_WARNING);
            return false;
        }
        $this->resources->merge($package->global_resources);
        $this->nav_groups = array_replace($this->nav_groups, $package->nav_groups);
        $this->views = array_replace($this->views, $package->views);
        $this->apis[$package_id] = (array) $package->apis;
        return $package;
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
        foreach ($this->views as $uri_path => $view) {
            /** @var $view PackageView */
            if (!$view->navigable) continue;
            $hash = $view->nav_group ? md5($view->nav_group) : md5("$view->package_id:$uri_path");
            if ($view->nav_group) {
                if (!isset($this->nav_groups[$view->nav_group])) {
                    error_log("Orphaned view at {$view->package_id}:{$view->nav_group}", E_USER_WARNING);
                    continue;
                }
                if (isset($this->nav_tree[$hash])) {
                    $this->nav_tree[$hash]->addChild((new PackageNavEntry())
                        ->setName($view->nav_name)
                        ->setIcon($view->nav_icon)
                        ->setUriPath($uri_path)
                        ->setPublicState($view->public)
                        ->setPermissions($view->permissions)
                    );
                } else {
                    $this->nav_tree[$hash] = (new PackageNavEntry())
                        ->setName($this->nav_groups[$view->nav_group]->name)
                        ->setIcon($this->nav_groups[$view->nav_group]->icon)
                        ->setPublicState($this->nav_groups[$view->nav_group]->public)
                        ->setPermissions($this->nav_groups[$view->nav_group]->permissions)
                        ->addChild((new PackageNavEntry())
                            ->setName($view->nav_name)
                            ->setIcon($view->nav_icon)
                            ->setUriPath($uri_path)
                            ->setPublicState($view->public)
                            ->setPermissions($view->permissions)
                        );
                }
            } else {
                $this->nav_tree[$hash] = (new PackageNavEntry())
                    ->setName($view->nav_name)
                    ->setIcon($view->nav_icon)
                    ->setUriPath($uri_path)
                    ->setPublicState($view->public)
                    ->setPermissions($view->permissions);
            }
        }
        return true;
    }

}