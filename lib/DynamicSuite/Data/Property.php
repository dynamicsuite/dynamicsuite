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

namespace DynamicSuite\Data;
use DynamicSuite\Base\DatabaseItem;

/**
 * Class Property.
 *
 * @package DynamicSuite\Data
 * @property int|null $property_id
 * @property string|null $name
 * @property string|null $meta_domain
 * @property string|null $data_domain
 * @property string|null $description
 * @property string|null $type
 * @property string|null $default
 * @property int|float|bool|string|null $value
 * @property string|null $created_by
 * @property string|null $created_on
 */
class Property extends DatabaseItem
{

    /**
     * The property ID.
     *
     * @var int|null
     */
    public ?int $property_id = null;

    /**
     * Name of the property for lookups.
     *
     * @var string|null
     */
    public ?string $name = null;

    /**
     * The meta-domain of the property.
     *
     * @var string|null
     */
    public ?string $meta_domain = null;

    /**
     * The data-domain of the property.
     *
     * @var string|null
     */
    public ?string $data_domain = null;

    /**
     * A short description of the property.
     *
     * @var string|null
     */
    public ?string $description = null;

    /**
     * Property type.
     *
     * Can be: int, float, bool, string
     *
     * @var string|null
     */
    public ?string $type = null;

    /**
     * The default value of the property, represented as a string.
     *
     * @var string|null
     */
    public ?string $default = null;

    /**
     * Current value of the property.
     *
     * @var int|float|bool|string|null
     */
    public $value = null;

    /**
     * User/source that created the property.
     *
     * @var string|null
     */
    public ?string $created_by = null;

    /**
     * A timestamp when the property was created.
     *
     * @var string|null
     */
    public ?string $created_on = null;

    /**
     * Property constructor.
     *
     * @param array $property
     * @return void
     */
    public function __construct(array $property = [])
    {
        parent::__construct($property);
        if ($this->value === null) {
            $this->value = $this->default;
        }
        settype($this->value, $this->type);
    }

}