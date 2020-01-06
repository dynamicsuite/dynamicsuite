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
use stdClass;

/**
 * Class PackageView.
 *
 * @package DynamicSuite
 * @property string $package_id
 * @property string $uri_path
 * @property string $entry_point
 * @property string $title
 * @property bool $public
 * @property bool $navigable
 * @property bool $hide_nav
 * @property bool $hide_logout_button
 * @property string $nav_name
 * @property string $nav_icon
 * @property string $nav_group
 * @property array $permissions
 * @property PackageResources $resources
 */
class PackageView extends ProtectedObject
{

    /**
     * Package ID of the package the view belongs to.
     *
     * @var string
     */
    protected $package_id;


    /**
     * URI string path for the view.
     *
     * @var string
     */
    protected $uri_path;

    /**
     * Script path to the view entry point.
     *
     * @var string
     */
    protected $entry_point;

    /**
     * View title (HTML title/header ribbon title).
     *
     * @var string
     */
    protected $title;

    /**
     * Package resources belonging to the view.
     *
     * @var PackageResources
     */
    protected $resources;

    /**
     * View array of required permissions.
     *
     * @var array
     */
    protected $permissions;

    /**
     * View public state.
     *
     * @var bool
     */
    protected $public = false;

    /**
     * View navigable state.
     *
     * @var bool
     */
    protected $navigable = true;

    /**
     * Hidden navigation state.
     *
     * @var bool
     */
    protected $hide_nav = false;

    /**
     * Flag to hide the logout button on the view
     *
     * @var bool
     */
    protected $hide_logout_button = false;

    /**
     * Navigation element name.
     *
     * @var string
     */
    protected $nav_name;

    /**
     * View navigation group id.
     *
     * @var string
     */
    protected $nav_group;

    /**
     * PackageView constructor.
     *
     * @param string $package_id
     * @return void
     */
    public function __construct(string $package_id)
    {
        $this->setPackageId($package_id);
        $this->resources = new PackageResources($this->package_id);
    }

    /**
     * Set the package ID.
     *
     * @param string $package_id
     * @return PackageView
     */
    public function setPackageId(string $package_id): PackageView
    {
        $this->package_id = $package_id;
        return $this;
    }

    /**
     * Set the uri path of the view.
     *
     * @param string $uri_path
     * @return PackageView
     */
    public function setUriPath(string $uri_path): PackageView
    {
        $this->uri_path = $uri_path;
        return $this;
    }

    /**
     * Set the uri path of the view.
     *
     * @param string $entry_point
     * @return PackageView
     */
    public function setEntryPoint(string $entry_point): PackageView
    {
        $this->entry_point = Package::formatServerPath($this->package_id, $entry_point);
        return $this;
    }

    /**
     * Set the view title.
     *
     * @param string|null $title
     * @return PackageView
     */
    public function setTitle(?string $title = null): PackageView
    {
        $this->title = $title;
        return $this;
    }

    /**
     * Set the view public state.
     *
     * @param bool $state
     * @return PackageView
     */
    public function setPublicState(bool $state = null): PackageView
    {
        $this->public = $state ?? false;
        return $this;
    }

    /**
     * Set the view navigable state.
     *
     * @param bool $state
     * @return PackageView
     */
    public function setNavigableState(bool $state = null): PackageView
    {
        $this->navigable = $state ?? true;
        return $this;
    }

    /**
     * Set the hidden navigation bar state.
     *
     * @param bool|null $state
     * @return PackageView
     */
    public function setHiddenNavState(bool $state = null): PackageView
    {
        $this->hide_nav = $state ?? false;
        return $this;
    }

    /**
     * Set the display state flag for the logout button.
     *
     * @param bool|null $state
     * @return PackageView
     */
    public function setHiddenLogoutButtonState(bool $state = null): PackageView
    {
        $this->hide_logout_button = $state;
        return $this;
    }

    /**
     * Set the navigation entry name.
     *
     * @param string $nav_name
     * @return PackageView
     */
    public function setNavName(string $nav_name = null): PackageView
    {
        $this->nav_name = $nav_name ?? 'Unknown View';
        return $this;
    }

    /**
     * Set the navigation entry icon.
     *
     * @param string|null $nav_icon
     * @return PackageView
     */
    public function setNavIcon(string $nav_icon = null): PackageView
    {
        $this->nav_icon = $nav_icon ?? 'fas fa-cogs';
        return $this;
    }

    /**
     * Set the navigation group for the view.
     *
     * @param string $nav_group
     * @return PackageView
     */
    public function setNavGroup(string $nav_group = null): PackageView
    {
        if ($nav_group) $this->nav_group = $nav_group;
        return $this;
    }

    /**
     * Add a permission to the view permissions.
     *
     * @param string $permission
     * @return PackageView
     */
    public function addPermission(string $permission = null): PackageView
    {
        if (!$permission) return $this;
        if (!$this->permissions) {
            $this->permissions[] = $permission;
        } elseif (!in_array($permission, $this->permissions)) {
            $this->permissions[] = $permission;
        }
        return $this;
    }

    /**
     * Set the view permissions.
     *
     * @param array|null $permissions
     * @return PackageView
     */
    public function setPermissions(array $permissions = null): PackageView
    {
        if ($permissions) {
            foreach ($permissions as $permission) $this->addPermission($permission);
        }
        return $this;
    }

    /**
     * Set the resources for the view from a structure array.
     *
     * @param stdClass $structure
     * @return PackageView
     */
    public function setResources(stdClass $structure): PackageView
    {
        if (isset($structure->autoload)) $this->resources->setAutoload($structure->autoload);
        if (isset($structure->init)) $this->resources->setInit($structure->init);
        if (isset($structure->js)) $this->resources->setJs($structure->js);
        if (isset($structure->css)) $this->resources->setCss($structure->css);
        return $this;
    }

}