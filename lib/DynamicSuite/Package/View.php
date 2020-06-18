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
 * Class View.
 *
 * @package DynamicSuite\Package
 * @property string $view_id
 * @property string $package_id
 * @property string|null $entry
 * @property string|null $title
 * @property bool $public
 * @property bool $navigable
 * @property bool $hide_nav
 * @property bool $hide_user_actions
 * @property bool $hide_logout_button
 * @property string|null $nav_name
 * @property string $nav_icon
 * @property string|null $nav_group
 * @property string[] $permissions
 * @property string[] $autoload
 * @property string[] $init
 * @property string[] $js
 * @property string[] $css
 */
final class View
{

    /**
     * View ID (URL path string).
     *
     * @var string
     */
    protected string $view_id;

    /**
     * Associated package ID.
     *
     * @var string
     */
    protected string $package_id;

    /**
     * View entry point script file path.
     *
     * @var string|null
     */
    protected ?string $entry = null;

    /**
     * View title (HTML and nav).
     *
     * @var string|null
     */
    protected ?string $title = null;

    /**
     * If the view is public (no auth required).
     *
     * @var bool
     */
    protected bool $public = false;

    /**
     * If the view is navigable.
     *
     * @var bool
     */
    protected bool $navigable = true;

    /**
     * If the navigation should be hidden.
     *
     * @var bool
     */
    protected bool $hide_nav = false;

    /**
     * If the user action area should be hidden.
     *
     * @var bool
     */
    protected bool $hide_user_actions = false;

    /**
     * If the logout button should be hidden.
     *
     * @var bool
     */
    protected bool $hide_logout_button = false;

    /**
     * Navigation name (under nav bar).
     *
     * @var string|null
     */
    protected ?string $nav_name = null;

    /**
     * Navigation icon.
     *
     * @var string
     */
    protected string $nav_icon = 'fas fa-cogs';

    /**
     * Navigation group.
     *
     * @var string|null
     */
    protected ?string $nav_group = null;

    /**
     * Required permissions (array of shorthands).
     *
     * @var string[]
     */
    protected array $permissions = [];

    /**
     * Paths to autoload.
     *
     * @var string[]
     */
    protected array $autoload = [];

    /**
     * Scripts to run before the view (initialization scripts).
     *
     * @var string[]
     */
    protected array $init = [];

    /**
     * JS client scripts to include.
     *
     * @var string[]
     */
    protected array $js = [];

    /**
     * CSS resources to include.
     *
     * @var string[]
     */
    protected array $css = [];

    /**
     * View constructor.
     *
     * @param string $view_id
     * @param string $package_id
     * @param array $structure
     * @return void
     * @throws Exception
     */
    public function __construct(string $view_id, string $package_id, array $structure)
    {
        $this->view_id = $view_id;
        $this->package_id = $package_id;
        $error = function(string $key, string $message): string {
            return "[View Structure] `$this->view_id`.`$key` $message for package `$this->package_id`";
        };
        foreach ($structure as $prop => $value) {
            if ($prop === 'entry') {
                $value = Format::formatServerPath($package_id, $value);
            }
            if (in_array($prop, ['permissions', 'autoload', 'init', 'js', 'css'])) {
                if (is_string($value)) {
                    $value = [$value];
                } elseif ($value === null) {
                    $value = [];
                } elseif (is_array($value)) {
                    foreach ($value as $k => $v) {
                        if (!is_string($v)) {
                            throw new Exception($error($prop, 'must be a string or array of strings'));
                        }
                        if ($prop === 'autoload' || $prop === 'init') {
                            $value[$k] = Format::formatServerPath($this->package_id, $v);
                        }
                        if ($prop === 'js' || $prop === 'css') {
                            $value[$k] = Format::formatClientPath($this->package_id, $v);
                        }
                    }
                } else {
                    throw new Exception($error($prop, 'must be a string or array of strings'));
                }
            }
            if (isset($this->$prop)) {
                $this->$prop = $value;
            }
        }
        if ($this->entry === null) {
            throw new Exception($error('entry', 'missing'));
        }
        $this->title ??= $this->view_id;
        $this->nav_name ??= $this->view_id;
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