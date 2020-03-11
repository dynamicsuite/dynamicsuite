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
use DynamicSuite\Base\ArrayConvertible;
use TypeError;

/**
 * Class Structure.
 *
 * @package DynamicSuite\Package
 * @property string $package_id
 * @property string $name
 * @property string $author
 * @property string $version
 * @property string $description
 * @property string $license
 * @property Resources $global_resources
 * @property Resources $local_resources
 * @property NavGroup[] $nav_groups
 * @property View[] $views
 * @property API[] $apis
 * @property array $action_links
 */
final class Structure extends ArrayConvertible
{

    /**
     * The package string, same as the directory name and loaded in the global config.
     *
     * @var string
     */
    public string $package_id;

    /**
     * A friendly package name for display purposes.
     *
     * @var string
     */
    public string $name;

    /**
     * The package author.
     *
     * @var string
     */
    public string $author = 'Unknown';

    /**
     * Package version.
     *
     * @var string
     */
    public string $version = '0.0.0';

    /**
     * A brief description of the package.
     *
     * @var string
     */
    public string $description = 'N/A';

    /**
     * Package license type.
     *
     * @var string
     */
    public string $license = 'Unlicensed';

    /**
     * Global package resources (applied to all views).
     *
     * @var Resources
     */
    protected Resources $global_resources;

    /**
     * Local package resources (applied to any views belonging to the package).
     *
     * @var Resources
     */
    protected Resources $local_resources;

    /**
     * Navigation groups attached to the package.
     *
     * @var NavGroup[]
     */
    protected array $nav_groups = [];

    /**
     * Views to include for the package.
     *
     * @var View[]
     */
    protected array $views = [];

    /**
     * APIs to include for the package.
     *
     * @var API[]
     */
    protected array $apis = [];

    /**
     * Array of action links to display in the action area.
     *
     * The key should be the link text, and the value should be the URL of the link.
     *
     * @var array
     */
    protected array $action_links = [];

    /**
     * PackageStructure constructor.
     *
     * @param string $package_id
     * @param array $array
     * @return void
     */
    public function __construct(string $package_id, array $array = [])
    {
        parent::__construct($array);
        $this->package_id = $package_id;
        $this->name = $this->name ?? $package_id;
        $this->global_resources = new Resources($package_id);
        $this->local_resources = new Resources($package_id);
        $this
            ->setGlobalResources($array['global'] ?? [])
            ->setLocalResources($array['local'] ?? [])
            ->setNavGroups($array['nav_groups'] ?? [])
            ->setViews($array['views'] ?? [])
            ->setApis($array['apis'] ?? [])
            ->setActionLinks($array['action_links'] ?? []);
    }

    /**
     * Set global resources.
     *
     * @param array|null $resources
     * @return Structure
     */
    public function setGlobalResources(array $resources = []): Structure
    {
        return $this->setResources('global_resources', $resources);
    }

    /**
     * Set local resources.
     *
     * @param array|null $resources
     * @return Structure
     */
    public function setLocalResources(array $resources = []): Structure
    {
        return $this->setResources('local_resources', $resources);
    }

    /**
     * Set resources by type.
     *
     * @param string $type
     * @param array|null $resources
     * @return Structure
     */
    public function setResources(string $type, array $resources = []): Structure
    {
        $this->$type->setAutoload($resources['autoload'] ?? []);
        $this->$type->setInit($resources['init'] ?? []);
        $this->$type->setJs($resources['js'] ?? []);
        $this->$type->setCss($resources['css'] ?? []);
        return $this;
    }

    /**
     * Set any navigational groups for the package.
     *
     * @param array $nav_groups
     * @return Structure
     */
    public function setNavGroups($nav_groups = []): Structure
    {
        $this->nav_groups = [];
        foreach ($nav_groups as $group_id => $structure) {
            $this->nav_groups[$group_id] = new NavGroup($group_id, $structure);
        }
        return $this;
    }

    /**
     * Set and verify the views for the package.
     *
     * @param array $views
     * @return Structure
     */
    public function setViews(array $views = []): Structure
    {
        $this->views = [];
        if (!$views) return $this;
        foreach ($views as $url => $structure) {
            if ($url[0] !== '/') $url = "/$this->package_id/$url";
            try {
                if (!isset($structure['entry'])) {
                    trigger_error("View $url missing entry", E_USER_WARNING);
                    continue;
                }
                if (!is_string($structure['entry'])) {
                    trigger_error("Invalid view url path type for: $url", E_USER_WARNING);
                    continue;
                }
                $structure['entry'] = Structure::formatServerPath(
                    $this->package_id, $structure['entry']
                );
                $view = new View($this->package_id, $url, $structure);
                $view->resources->merge($this->local_resources, true);
            } catch (TypeError $exception) {
                trigger_error($exception->getMessage(), E_USER_WARNING);
                continue;
            }
            $this->views[$url] = $view;
        }
        return $this;
    }

    /**
     * Set and verify the apis for the package.
     *
     * @param array|null $apis
     * @return Structure
     */
    public function setApis(array $apis = []): Structure
    {
        $this->apis = [];
        if (!$apis) return $this;
        foreach ($apis as $api_id => $structure) {
            try {
                if (!isset($structure['entry'])) {
                    trigger_error("Entry point missing for api $this->package_id:$api_id", E_USER_WARNING);
                    continue;
                }
                if (!is_string($structure['entry'])) {
                    trigger_error("Entry point invalid for api $this->package_id:$api_id", E_USER_WARNING);
                    continue;
                }
                $structure['entry'] = Structure::formatServerPath(
                    $this->package_id, $structure['entry']
                );
                $api = new API($this->package_id, $api_id, $structure);
                $api->resources->merge($this->local_resources, true);
            } catch (TypeError $exception) {
                trigger_error($exception->getMessage(), E_USER_WARNING);
                continue;
            }
            $this->apis[$api_id] = $api;
        }
        return $this;
    }

    /**
     * Set the action links of the package.
     *
     * @param array $action_links
     * @return Structure
     */
    public function setActionLinks(array $action_links = []): Structure
    {
        foreach ($action_links as $text => $action) {
            if (!array_key_exists('type', $action) || !array_key_exists('value', $action)) {
                trigger_error('Action link must contain a type and a value', E_USER_WARNING);
                continue;
            }
            if ($action['type'] !== 'static' && $action['type'] !== 'dynamic') {
                trigger_error('Action link type must be static or dynamic', E_USER_WARNING);
                continue;
            }
            if (!is_string($action['value'])) {
                trigger_error('Action link value must be a string');
                continue;
            }
            if (array_key_exists('permissions', $action)) {
                if (!is_array($action['permissions'])) {
                    trigger_error('Permissions must be an array', E_USER_WARNING);
                    continue;
                }
                foreach ($action['permissions'] as $permission) {
                    if (!is_string($permission)) {
                        trigger_error('Permissions must be string', E_USER_WARNING);
                        continue 2;
                    }
                }
            }
            if ($action['type'] === 'dynamic') {
                $action['value'] = self::formatServerPath($this->package_id, $action['value']);
            }
            $this->action_links[$text] = $action;
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
        return $path[0] === '/' ? DS_ROOT_DIR . $path : "packages/$package_id/$path";
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