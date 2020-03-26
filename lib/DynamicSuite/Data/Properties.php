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
use Memcached;
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
     * Get an array of all properties for the given meta-domain and data-domain.
     *
     * @param string|null $meta_domain
     * @param string|null $data_domain
     * @return Property[]
     * @throws PDOException
     */
    public function getAll(?string $meta_domain = null, ?string $data_domain = null): array
    {
        if (DS_CACHING) {
            $property = $this->ds->cache->get("dynamicsuite:properties:$meta_domain:$data_domain");
            if ($this->ds->cache->cache->getResultCode() === Memcached::RES_SUCCESS) {
                return $property;
            }
        }
        $query = "$this->property_query WHERE `ds_properties`.`domain` = ?";
        $data = $this->ds->db->query($query, [$data_domain, $meta_domain]);
        $properties = [];
        foreach ($data as $row) {
            $properties[$row['property_id']] = new Property($row);
        }
        if (DS_CACHING) {
            $this->addDomainCache($data_domain);
            $this->ds->cache->set("dynamicsuite:properties:$meta_domain:$data_domain", $properties);
        }
        return $properties;
    }

    /**
     * Get a property by $name for the given data $domain.
     *
     * @param string $name
     * @param string $domain
     * @return Property
     * @throws PDOException
     */
    public function get(string $name, string $domain): Property
    {
        if (DS_CACHING) {
            $property = $this->ds->cache->get("dynamicsuite:property:$name:data:$domain");
            if ($this->ds->cache->cache->getResultCode() === Memcached::RES_SUCCESS) {
                return $property;
            }
        }
        $query = "$this->property_query WHERE `ds_properties`.`name` = ?";
        $data = $this->ds->db->query($query, [$domain, $name]);
        if (count($data) !== 1 || !isset($data[0])) {
            throw new PDOException('Property not found');
        }
        $property = new Property($data[0]);
        if (DS_CACHING) {
            $this->addDomainCache($domain);
            $this->ds->cache->set("dynamicsuite:property:$name:data:$domain", $property);
        }
        return $property;
    }

    /**
     * Attempt to find a property by name.
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
    public function find(string $name, ?string $domain = null)
    {
        if (DS_CACHING) {
            $property = $this->ds->cache->get("dynamicsuite:property:$name:meta:$domain");
            if ($this->ds->cache->cache->getResultCode() === Memcached::RES_SUCCESS) {
                return $property;
            }
        }
        $data = $this->ds->db->query((new Query())
            ->select()
            ->from('ds_properties')
            ->where('name', '=', $name)
            ->where('domain', '=', $domain)
        );
        if (count($data) !== 1 || !isset($data[0])) {
            return false;
        }
        $property = new Property($data[0]);
        if (DS_CACHING) {
            $this->ds->cache->set("dynamicsuite:property:$name:meta:$domain", $property);
        }
        return $property;
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
        $this->clearDomainCache($property);
        return $property;
    }

    /**
     * Modify an existing property.
     *
     * @param Property $property
     * @return Property
     * @throws PDOException
     */
    public function modify(Property $property): Property
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
        $this->clearDomainCache($property);
        return $property;
    }

    /**
     * Delete a property.
     *
     * @param Property $property
     * @return Property
     */
    public function delete(Property $property): Property
    {
        $this->ds->db->query((new Query())
            ->delete()
            ->from('ds_properties')
            ->where('property_id', '=', $property->property_id)
        );
        $this->clearDomainCache($property);
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
        $this->ds->db->startTx();
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
            $this->clearDomainCache($property);
        }
        $this->ds->db->endTx();
    }

    /**
     * Add a data domain to the domain cache.
     *
     * @param string $domain
     * @return void
     */
    private function addDomainCache(string $domain): void
    {
        if (!DS_CACHING) {
            return;
        }
        $domains = $this->ds->cache->get("dynamicsuite:properties::data-domains");
        if ($this->ds->cache->cache->getResultCode() === Memcached::RES_SUCCESS) {
            if (!in_array($domain, $domains)) {
                $domains[] = $domain;
            }
        } else {
            $domains = [$domain];
        }
        $this->ds->cache->set("dynamicsuite:properties::data-domains", $domains);
    }

    /**
     * Clear the domain cache for a given property.
     *
     * @param Property $property
     * @return void
     */
    private function clearDomainCache(Property $property): void
    {
        if (!DS_CACHING) {
            return;
        }
        $this->ds->cache->delete("dynamicsuite:property:$property->name:meta:$property->meta_domain");
        $data_domains = $this->ds->cache->get("dynamicsuite:properties::data-domains");
        if ($this->ds->cache->cache->getResultCode() === Memcached::RES_SUCCESS) {
            foreach ($data_domains as $domain) {
                $this->ds->cache->delete("dynamicsuite:properties:$property->meta_domain:$domain");
                $this->ds->cache->delete("dynamicsuite:property:$property->name:data:$domain");
            }
        }
    }

}