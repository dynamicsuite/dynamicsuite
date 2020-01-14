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

/**
 * Class View.
 *
 * @package DynamicSuite
 * @property string $package_id
 * @property string $url
 * @property string $entry
 * @property string $title
 * @property bool $public
 * @property bool $navigable
 * @property bool $hide_nav
 * @property bool $hide_logout_button
 * @property string|null $nav_group
 * @property string $nav_name
 * @property string $nav_icon
 * @property array|null $permissions
 * @property Resources $resources
 */
class View extends ArrayConvertible
{

    /**
     * Package ID of the package the view belongs to.
     *
     * @var string
     */
    public string $package_id;

    /**
     * URL string path for the view.
     *
     * @var string
     */
    public string $url;

    /**
     * Script path to the view entry point.
     *
     * @var string
     */
    public string $entry;

    /**
     * View title (HTML title/header ribbon title).
     *
     * @var string
     */
    public string $title;

    /**
     * View public state.
     *
     * @var bool
     */
    public bool $public = false;

    /**
     * View navigable state.
     *
     * @var bool
     */
    public bool $navigable = true;

    /**
     * Hidden navigation state.
     *
     * @var bool
     */
    public bool $hide_nav = false;

    /**
     * Flag to hide the logout button on the view
     *
     * @var bool
     */
    public bool $hide_logout_button = false;

    /**
     * View navigation group id.
     *
     * @var string|null
     */
    public ?string $nav_group = null;

    /**
     * Navigation element name.
     *
     * @var string
     */
    public string $nav_name;

    /**
     * Navigation icon.
     *
     * @var string
     */
    public string $nav_icon = 'fas fa-cogs';

    /**
     * View array of required permissions.
     *
     * @var array|null
     */
    public ?array $permissions = null;

    /**
     * Package resources belonging to the view.
     *
     * @var Resources
     */
    public Resources $resources;

    /**
     * PackageView constructor.
     *
     * @param string $package_id
     * @param string $url
     * @param array $structure
     * @return void
     */
    public function __construct(string $package_id, string $url, $structure = [])
    {
        $this->package_id = $package_id;
        $this->url = $url;
        parent::__construct($structure);
        $this->title = $this->title ?? $this->url;
        $this->nav_name = $this->nav_name ?? $this->url;
        $this->resources = new Resources($this->package_id);
        $this->setResources($structure);
    }

    /**
     * Get the package view entry point.
     *
     * @return string
     */
    public function trueEntryPoint(): string
    {
        return Structure::formatServerPath($this->package_id, $this->entry);
    }

    /**
     * Set the resources for the view from a structure array.
     *
     * @param array $structure
     * @return View
     */
    public function setResources(array $structure = []): View
    {
        $this->resources->setAutoload($structure['autoload'] ?? []);
        $this->resources->setInit($structure['init'] ?? []);
        $this->resources->setJs($structure['js'] ?? []);
        $this->resources->setCss($structure['css'] ?? []);
        return $this;
    }

}