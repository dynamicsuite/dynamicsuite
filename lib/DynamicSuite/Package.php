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

namespace DynamicSuite;
use TypeError;
use stdClass;

/**
 * Class Package.
 *
 * @package DynamicSuite
 * @property string $id
 * @property string $name
 * @property string $author
 * @property string $version
 * @property string $description
 * @property string $license
 * @property PackageResources $global_resources
 * @property PackageResources $local_resources
 * @property PackageNavGroup[] $nav_groups
 * @property PackageView[] $views
 * @property PackageApi[] $apis
 */
class Package extends ProtectedObject
{

    /**
     * The package string, same as the directory name and loaded in the global config.
     *
     * @var string
     */
    protected $id;

    /**
     * A friendly package name for display purposes.
     *
     * @var string
     */
    protected $name;

    /**
     * The package author.
     *
     * @var string
     */
    protected $author;

    /**
     * Package version.
     *
     * @var string
     */
    protected $version;

    /**
     * A brief description of the package.
     *
     * @var string
     */
    protected $description;

    /**
     * Package license type.
     *
     * @var string
     */
    protected $license;

    /**
     * Global package resources (applied to all views).
     *
     * @var PackageResources
     */
    protected $global_resources;

    /**
     * Local package resources (applied to any views belonging to the package).
     *
     * @var PackageResources
     */
    protected $local_resources;

    /**
     * Navigation groups attached to the package.
     *
     * @var array
     */
    protected $nav_groups = [];

    /**
     * Views to include for the package.
     *
     * @var array
     */
    protected $views = [];

    /**
     * APIs to include for the package.
     *
     * @var array
     */
    protected $apis = [];

    /**
     * PackageStructure constructor.
     *
     * @param string $package_id
     * @return void
     */
    public function __construct(string $package_id)
    {
        $this->setId($package_id);
        $this->global_resources = new PackageResources($package_id);
        $this->local_resources = new PackageResources($package_id);
    }

    /**
     * Set the package ID.
     *
     * @param string $id
     * @return Package
     */
    public function setId(string $id): Package
    {
        $this->id = $id;
        return $this;
    }

    /**
     * Set the package name.
     *
     * @param string|null $name
     * @return Package
     */
    public function setName(string $name = null): Package
    {
        $this->name = $name ?? $this->id;
        return $this;
    }

    /**
     * Set the package author.
     *
     * @param string|null $author
     * @return Package
     */
    public function setAuthor(string $author = null): Package
    {
        $this->author = $author ?? 'N/A';
        return $this;
    }

    /**
     * Set the package version.
     *
     * @param string|null $version
     * @return Package
     */
    public function setVersion(string $version = null): Package
    {
        $this->version = $version ?? '0.0.0';
        return $this;
    }

    /**
     * Set the package description.
     *
     * @param string|null $description
     * @return Package
     */
    public function setDescription(string $description = null): Package
    {
        $this->description = $description ?? 'N/A';
        return $this;
    }

    /**
     * Set the package license.
     *
     * @param string|null $license
     * @return Package
     */
    public function setLicense(string $license = null): Package
    {
        $this->license = $license ?? 'Unlicensed';
        return $this;
    }

    /**
     * Set global resources.
     *
     * @param stdClass|null $resources
     * @return Package
     */
    public function setGlobalResources(stdClass $resources = null): Package
    {
        return $this->setResources('global_resources', $resources);
    }

    /**
     * Set local resources.
     *
     * @param stdClass|null $resources
     * @return Package
     */
    public function setLocalResources(stdClass $resources = null): Package
    {
        return $this->setResources('local_resources', $resources);
    }

    /**
     * Set resources by type.
     *
     * @param string $type
     * @param stdClass|null $resources
     * @return Package
     */
    protected function setResources(string $type, stdClass $resources = null): Package
    {
        if ($resources) {
            if (isset($resources->autoload)) $this->$type->setAutoload($resources->autoload);
            if (isset($resources->init)) $this->$type->setInit($resources->init);
            if (isset($resources->js)) $this->$type->setJs($resources->js);
            if (isset($resources->css)) $this->$type->setCss($resources->css);
        }
        return $this;
    }

    /**
     * Format a server file path.
     *
     * @param string $package_id
     * @param string $path
     * @return string
     */
    public static function formatServerPath(string $package_id, string $path): string
    {
        return $path[0] === '/' ? $path : "packages/$package_id/$path";
    }

    /**
     * Format a client resource path.
     *
     * @param string $package_id
     * @param string $path
     * @return string
     * @noinspection PhpUnused
     */
    public static function formatClientPath(string $package_id, string $path): string
    {
        return $path[0] === '/' ? $path : "/dynamicsuite/packages/$package_id/$path";
    }

    /**
     * Set any navigational groups for the package.
     *
     * @param stdClass|null $nav_groups
     * @return Package
     */
    public function setNavGroups(stdClass $nav_groups = null): Package
    {
        if ($nav_groups) {
            foreach ((array) $nav_groups as $group_id => $nav_group) {
                $this->nav_groups[$group_id] = (new PackageNavGroup($group_id))
                    ->setName(@$nav_group->name)
                    ->setIcon(@$nav_group->icon)
                    ->setPublicState(@$nav_group->public)
                    ->setPermissions(@$nav_group->permissions);
            }
        }
        return $this;
    }

    /**
     * Set and verify the views for the package.
     *
     * @param stdClass|null $views
     * @return Package
     */
    public function setViews(stdClass $views = null): Package
    {
        if (!$views) return $this;
        foreach ($views as $uri_path => $structure) {
            if ($uri_path[0] !== '/') $uri_path = "/$this->id/$uri_path";
            try {
                if (!isset($structure->entry_point)) {
                    trigger_error("View $uri_path missing entry_point", E_USER_WARNING);
                    continue;
                }
                if (!is_string($structure->entry_point)) {
                    trigger_error("Invalid view uri path type for: $uri_path", E_USER_WARNING);
                    continue;
                }
                $view = (new PackageView($this->id))
                    ->setUriPath($uri_path)
                    ->setEntryPoint($structure->entry_point)
                    ->setTitle(@$structure->title)
                    ->setPublicState(@$structure->public)
                    ->setNavigableState(@$structure->navigable)
                    ->setHiddenNavState(@$structure->hide_nav)
                    ->setHiddenLogoutButtonState(@$structure->hide_logout_button)
                    ->setNavName(@$structure->nav_name)
                    ->setNavIcon(@$structure->nav_icon)
                    ->setNavGroup(@$structure->nav_group)
                    ->setPermissions(@$structure->permissions)
                    ->setResources($structure);
                $view->resources->merge($this->local_resources, true);
            } catch (TypeError $exception) {
                trigger_error($exception->getMessage(), E_USER_WARNING);
                continue;
            }
            $this->views[$uri_path] = $view;
        }
        return $this;
    }

    /**
     * Set and verify the apis for the package.
     *
     * @param stdClass|null $apis
     * @return Package
     */
    public function setApis(stdClass $apis = null): Package
    {
        if (!$apis) return $this;
        foreach ($apis as $id => $structure) {
            try {
                if (!isset($structure->entry_point)) {
                    trigger_error("Entry point missing for api $this->id:$id", E_USER_WARNING);
                    continue;
                }
                if (!is_string($structure->entry_point)) {
                    trigger_error("Entry point invalid for api $this->id:$id", E_USER_WARNING);
                    continue;
                }
                $api = (new PackageApi())
                    ->setPackageId($this->id)
                    ->setApiId($id)
                    ->setEntryPoint($structure->entry_point)
                    ->setPost(@$structure->post)
                    ->setPermissions(@$structure->permissions)
                    ->setPublicState(@$structure->public)
                    ->setResources($structure);
                $api->resources->merge($this->local_resources, true);
            } catch (TypeError $exception) {
                trigger_error($exception->getMessage(), E_USER_WARNING);
                continue;
            }
            $this->apis[$id] = $api;
        }
        return $this;
    }

}