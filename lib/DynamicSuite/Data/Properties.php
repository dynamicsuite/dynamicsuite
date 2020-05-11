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
use DynamicSuite\Base\InstanceMember;
use DynamicSuite\Core\DynamicSuite;
use DynamicSuite\Util\Query;
use PDOException;

/**
 * Class Properties.
 *
 * @package DynamicSuite\Data
 * @property string $property_query
 */
final class Properties extends InstanceMember
{

    /**
     * Join query stub for property selection.
     *
     * @var string
     */
    private string $property_query = '';

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
     * Properties constructor.
     *
     * @param DynamicSuite $ds
     * @return void
     */
    public function __construct(DynamicSuite $ds)
    {
        parent::__construct($ds);
        $this->property_query = 'SELECT ';
        $this->property_query .= '`ds_properties`.`property_id`, ';
        $this->property_query .= '`ds_properties`.`name`, ';
        $this->property_query .= '`ds_properties`.`domain` AS `meta_domain`, ';
        $this->property_query .= '`ds_property_data`.`domain` AS `data_domain`, ';
        $this->property_query .= '`ds_properties`.`description`, ';
        $this->property_query .= '`ds_properties`.`type`, ';
        $this->property_query .= '`ds_properties`.`default`, ';
        $this->property_query .= '`ds_property_data`.`value`, ';
        $this->property_query .= '`ds_properties`.`created_by`, ';
        $this->property_query .= '`ds_properties`.`created_on` ';
        $this->property_query .= 'FROM ';
        $this->property_query .= '`ds_properties` ';
        $this->property_query .= 'LEFT JOIN ';
        $this->property_query .= '`ds_property_data` ';
        $this->property_query .= 'ON ';
        $this->property_query .= '`ds_property_data`.`property_id` = `ds_properties`.`property_id` ';
        $this->property_query .= 'AND ';
        $this->property_query .= '`ds_property_data`.`domain` = ? ';
    }

    /**
     * Create a property.
     *
     * @param Property $property
     * @return Property
     * @throws PDOException
     */
    public function create(Property $property): Property
    {
        $property->created_on = date('Y-m-d H:i:s');
        $property->created_by = $this->ds->session->user->username ?? null;
        $property->validate($property, self::COLUMN_LIMITS);
        $property->property_id = $this->ds->db->query((new Query())
            ->insert([
                'name' => $property->name,
                'domain' => $property->meta_domain,
                'description' => $property->description,
                'type' => $property->type,
                'default' => $property->default,
                'created_by' => $property->created_by,
                'created_on' => $property->created_on
            ])
            ->into('ds_properties')
        );
        return $property;
    }

    /**
     * Read all properties for the given meta-domain and data-domain.
     *
     * @param string|null $meta_domain
     * @param string|null $data_domain
     * @return Property[]
     * @throws PDOException
     */
    public function readAll(?string $meta_domain = null, ?string $data_domain = null): array
    {
        $query = "$this->property_query WHERE `ds_properties`.`domain` = ?";
        $data = $this->ds->db->query($query, [$data_domain, $meta_domain]);
        $properties = [];
        foreach ($data as $row) {
            $properties[$row['property_id']] = new Property($row);
        }
        return $properties;
    }

    /**
     * Read a property by $name for the given data $domain.
     *
     * @param string $name
     * @param string $domain
     * @return Property
     * @throws PDOException
     */
    public function readValue(string $name, string $domain): Property
    {
        $query = "$this->property_query WHERE `ds_properties`.`name` = ?";
        $data = $this->ds->db->query($query, [$domain, $name]);
        if (count($data) !== 1 || !isset($data[0])) {
            throw new PDOException('Property not found');
        }
        return new Property($data[0]);
    }

    /**
     * Attempt to read a property by name.
     *
     * Can filter by meta domain.
     *
     * Returns the property if found, or FALSE if not found.
     *
     * @param string $name
     * @param string|null $domain
     * @return Property|bool
     * @throws PDOException
     */
    public function readMeta(string $name, ?string $domain = null)
    {
        $data = $this->ds->db->query((new Query())
            ->select()
            ->from('ds_properties')
            ->where('name', '=', $name)
            ->where('domain', '=', $domain)
        );
        if (count($data) !== 1 || !isset($data[0])) {
            return false;
        }
        return new Property($data[0]);
    }

    /**
     * Update a property.
     *
     * @param Property $property
     * @return Property
     * @throws PDOException
     */
    public function update(Property $property): Property
    {
        $property->validate($property, self::COLUMN_LIMITS);
        $this->ds->db->query((new Query())
            ->update('ds_properties')
            ->set([
                'name' => $property->name,
                'description' => $property->description,
                'type' => $property->type,
                'default' => $property->default
            ])
            ->where('property_id', '=', $property->property_id)
        );
        return $property;
    }

    /**
     * Update a data domains property values.
     *
     * @param array $properties
     * @param string $data_domain
     * @return void
     * @throws PDOException
     */
    public function updateDataDomain(array $properties, string $data_domain)
    {
        $this->ds->db->query((new Query())
            ->delete()
            ->from('ds_property_data')
            ->where('domain', '=', $data_domain)
        );
        foreach ($properties as $property) {
            if (!$property instanceof Property) {
                throw new PDOException('Property invalid');
            }
            $this->ds->db->query((new Query())
                ->insert([
                    'property_id' => $property->property_id,
                    'domain' => $data_domain,
                    'value' => $property->value
                ])
                ->into('ds_property_data')
            );
        }
    }

    /**
     * Delete a property.
     *
     * @param Property $property
     * @return Property
     * @throws PDOException
     */
    public function delete(Property $property): Property
    {
        $this->ds->db->query((new Query())
            ->delete()
            ->from('ds_properties')
            ->where('property_id', '=', $property->property_id)
        );
        return $property;
    }

    /**
     * Delete all of the data in a data domain.
     *
     * @param string $data_domain
     * @return void
     * @throws PDOException
     */
    public function deleteDataDomain(string $data_domain): void
    {
        $this->ds->db->query((new Query())
            ->delete()
            ->from('ds_property_data')
            ->where('domain', '=', $data_domain)
        );
    }

}