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
 * Class Api.
 *
 * @package DynamicSuite\Package
 * @property string $api_id
 * @property string $package_id
 * @property string|null $entry
 * @property string[] $post
 * @property string[] $permissions
 * @property bool $public
 * @property string[] $autoload
 * @property string[] $init
 */
final class Api
{

    /**
     * API ID string.
     *
     * @var string
     */
    protected string $api_id;

    /**
     * Package ID string
     *
     * @var string
     */
    protected string $package_id;

    /**
     * API entry point path.
     *
     * @var string|null
     */
    protected ?string $entry = null;

    /**
     * Required input POST keys.
     *
     * @var string[]
     */
    protected array $post = [];

    /**
     * Required permissions (array of shorthands).
     *
     * @var string[]
     */
    protected array $permissions = [];

    /**
     * If the API is public (no auth required).
     *
     * @var bool
     */
    protected bool $public = false;

    /**
     * API specific autoload paths.
     *
     * @var string[]
     */
    protected array $autoload = [];

    /**
     * API specific init scripts.
     *
     * @var string[]
     */
    protected array $init = [];

    /**
     * Api constructor.
     *
     * @param string $api_id
     * @param string $package_id
     * @param array $structure
     * @return void
     * @throws Exception
     */
    public function __construct(string $api_id, string $package_id, array $structure)
    {
        $this->api_id = $api_id;
        $this->package_id = $package_id;
        $error = function(string $key, string $message): string {
            return "[API Structure] `$this->api_id`.`$key` $message for package `$this->package_id`";
        };
        foreach ($structure as $prop => $value) {
            if ($prop === 'entry') {
                $value = Format::formatServerPath($package_id, $value);
            }
            if (in_array($prop, ['permissions', 'post', 'autoload', 'init'])) {
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
                            $value[$k] = Format::formatServerPath($this->package_id, $value);
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