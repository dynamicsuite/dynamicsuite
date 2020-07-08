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

namespace DynamicSuite\API;

/**
 * Class Request.
 *
 * @package DynamicSuite\API
 * @property string $package_id
 * @property string $api_id
 * @property array $data
 */
class Request
{

    /**
     * Requested package ID.
     *
     * @var string
     */
    public string $package_id;

    /**
     * Requested API ID.
     *
     * @var string
     */
    public string $api_id;

    /**
     * Request data payload.
     *
     * @var array
     */
    public array $data = [];

    /**
     * Request constructor.
     *
     * @param string $package_id
     * @param string $api_id
     * @param array $data
     */
    public function __construct(string $package_id, string $api_id, $data = [])
    {
        $this->package_id = $package_id;
        $this->api_id = $api_id;
        $this->data = $data;
    }

}