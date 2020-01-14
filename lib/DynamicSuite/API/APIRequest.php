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
use DynamicSuite\Base\ProtectedObject;

/**
 * Class APIRequest.
 *
 * @package DynamicSuite\API
 * @property string $package_id
 * @property string $api_id
 * @property mixed $data
 */
class APIRequest extends ProtectedObject
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
     * Any data sent along with the request.
     *
     * @var mixed
     */
    public $data;

    /**
     * APIRequest constructor.
     *
     * @param string $package_id
     * @param string $api_id
     * @param null $data
     */
    public function __construct(string $package_id, string $api_id, $data = null)
    {
        $this->package_id = $package_id;
        $this->api_id = $api_id;
        $this->data = $data;
    }

}