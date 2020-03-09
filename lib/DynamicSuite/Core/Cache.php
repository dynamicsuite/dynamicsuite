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

namespace DynamicSuite\Core;
use DynamicSuite\Base\InstanceMember;
use Memcached;

/**
 * Class Cache.
 *
 * @package DynamicSuite\Core
 * @property Memcached $cache
 */
final class Cache extends InstanceMember
{

    /**
     * The cache instance.
     *
     * @var Memcached
     */
    protected Memcached $cache;

    /**
     * Cache constructor.
     *
     * @param DynamicSuite $ds
     * @return void
     */
    public function __construct(DynamicSuite $ds)
    {
        parent::__construct($ds);
    }

    /**
     * Reset the cache connection and add the server set in the config.
     *
     * @return void
     */
    public function connect(): void
    {
        $this->cache = new Memcached();
        $this->cache->addServer($this->ds->cfg->memcached_host, $this->ds->cfg->memcached_port);
    }

    /**
     * Set an item in the cache.
     *
     * @param string $key
     * @param mixed $value
     * @param int $expiration
     * @return bool
     */
    public function set(string $key, $value, int $expiration = 0): bool
    {
        return $this->cache->set($key, $value, $expiration);
    }

    /**
     * Get an item from the cache.
     *
     * @param string $key
     * @return mixed
     */
    public function get(string $key)
    {
        return $this->cache->get($key);
    }

}