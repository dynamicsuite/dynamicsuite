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

namespace DynamicSuite\Storable;
use DynamicSuite\Core\Session;
use DynamicSuite\Database\Query;
use Exception;
use PDOException;

/**
 * Class Property.
 *
 * @package DynamicSuite\Storable
 * @property int|null $property_id
 * @property string|null $name
 * @property string|null $domain
 * @property string|null $description
 * @property string|null $type
 * @property string|null $default
 * @property string|null $created_by
 * @property string|null $created_on
 */
class Property extends Storable implements IStorable
{

    /**
     * Column length limits.
     *
     * @var int[]
     */
    public const COLUMN_LIMITS = [
        'name' => 64,
        'domain' => 64,
        'description' => 255,
        'default' => 2048,
        'created_by' => 254,
        'value' => 2048
    ];

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
     * The domain of the property.
     *
     * @var string|null
     */
    public ?string $domain = null;

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
     * Property creation source.
     *
     * @var string|null
     */
    public ?string $created_by = null;

    /**
     * The timestamp when the property was created.
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
    }

    /**
     * Create the property in the database.
     *
     * @return Property
     * @throws Exception|PDOException
     */
    public function create(): Property
    {
        $this->created_by = $this->created_by ?? Session::$user_name;
        $this->created_on = date('Y-m-d H:i:s');
        $this->validate(self::COLUMN_LIMITS);
        $this->property_id = (new Query())
            ->insert([
                'name' => $this->name,
                'domain' => $this->domain,
                'description' => $this->description,
                'type' => $this->type,
                'default' => $this->default,
                'created_by' => $this->created_by,
                'created_on' => $this->created_on
            ])
            ->into('ds_properties')
            ->execute();
        return $this;
    }

    /**
     * Attempt to read a property by ID.
     *
     * Returns the Property if found, or FALSE if not found.
     *
     * @param int $id
     * @return bool|Property
     * @throws Exception|PDOException
     */
    public static function readById(int $id)
    {
        $property = (new Query())
            ->select()
            ->from('ds_properties')
            ->where('property_id', '=', $id)
            ->execute(true);
        return $property ? new Property($property) : false;
    }

    /**
     * Update the property in the database.
     *
     * @return Property
     * @throws Exception|PDOException
     */
    public function update(): Property
    {
        $this->validate(self::COLUMN_LIMITS);
        $this->property_id = (new Query())
            ->update('ds_properties')
            ->set([
                'name' => $this->name,
                'domain' => $this->domain,
                'description' => $this->description,
                'type' => $this->type,
                'default' => $this->default
            ])
            ->where('property_id', '=', $this->property_id)
            ->execute();
        return $this;
    }

    /**
     * Delete the property from the database.
     *
     * @return Property
     * @throws Exception|PDOException
     */
    public function delete(): Property
    {
        (new Query())
            ->delete()
            ->from('ds_properties')
            ->where('property_id', '=', $this->property_id)
            ->execute();
        return $this;
    }

    /**
     * Read a property by name for the given domain.
     *
     * @param string $name
     * @param string $domain
     * @return int|float|bool|string
     * @throws Exception|PDOException
     */
    public static function readValue(string $name, string $domain)
    {
        $property = (new Query())
            ->select([
                '`type`',
                '`default`',
                (new Query())
                    ->select(['value'])
                    ->from('ds_properties_data')
                    ->where('domain', '=', $domain)
                    ->where('property_id', '=', 'ds_properties.property_id', true)
                    ->as('value')
            ])
            ->from('ds_properties')
            ->where('name', '=', $name)
            ->execute(true);
        if (!$property) {
            throw new Exception("Property not found: $name");
        }
        if ($property['value'] === null) {
            settype($property['default'], $property['type']);
            return $property['default'];
        } else {
            settype($property['value'], $property['type']);
            return $property['value'];
        }
    }

}