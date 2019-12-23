<?php
/*
 * Dynamic Suite
 * Copyright (C) 2019 Dynamic Suite Team
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

namespace DynamicSuite;

/**
 * Class APIRequest.
 *
 * @package DynamicSuite
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
    protected $package_id;

    /**
     * Requested API ID.
     *
     * @var string
     */
    protected $api_id;

    /**
     * Any data sent along with the request.
     *
     * @var mixed
     */
    protected $data;

    /**
     * APIRequest constructor.
     *
     * @param string $package_id
     * @param string $api_id
     * @param null $data
     */
    public function __construct(string $package_id, string $api_id, $data = null)
    {
        $this->setPackageId($package_id);
        $this->setApiId($api_id);
        $this->setData($data);
    }

    /**
     * Set the request package ID.
     *
     * @param string $package_id
     * @return APIRequest
     */
    public function setPackageId(string $package_id): APIRequest
    {
        $this->package_id = $package_id;
        return $this;
    }

    /**
     * Set the request API ID.
     *
     * @param string $api_id
     * @return APIRequest
     */
    public function setApiId(string $api_id): APIRequest
    {
        $this->api_id = $api_id;
        return $this;
    }

    /**
     * Set the request data.
     *
     * @param mixed $data
     * @return APIRequest
     */
    public function setData($data): APIRequest
    {
        $this->data = $data;
        return $this;
    }

}