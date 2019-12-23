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
 * Class APIResponse.
 *
 * @package DynamicSuite
 * @property string $status
 * @property string $message
 * @property mixed $data
 */
class APIResponse extends ProtectedObject
{

    /**
     * API error status code.
     *
     * @var string
     */
    protected $status;

    /**
     * User-friendly message.
     *
     * @var string
     */
    protected $message;

    /**
     * Response data (if any).
     *
     * @var mixed
     */
    protected $data;

    /**
     * APIResponse constructor.
     *
     * @param string $status
     * @param string $message
     * @param mixed $data
     * @return void
     */
    public function __construct(?string $status = null, string $message = 'Empty Response', $data = null) {
        $this->setStatus($status);
        $this->setMessage($message);
        $this->setData($data);
    }

    /**
     * Set the error status code.
     *
     * @param string $status
     * @return APIResponse
     */
    public function setStatus(?string $status): APIResponse
    {
        $this->status = $status;
        return $this;
    }

    /**
     * Set the user message.
     *
     * @param string $message
     * @return APIResponse
     */
    public function setMessage(string $message): APIResponse
    {
        $this->message = $message;
        return $this;
    }

    /**
     * Set the response data.
     *
     * @param mixed $data
     * @return APIResponse
     */
    public function setData($data): APIResponse
    {
        $this->data = $data;
        return $this;
    }

}