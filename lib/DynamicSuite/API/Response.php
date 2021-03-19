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

use DynamicSuite\Core\DynamicSuite;
use DynamicSuite\Package\API;

/**
 * Class Response.
 *
 * @package DynamicSuite\API
 * @property string $status
 * @property string $message
 * @property mixed $data
 */
class Response
{

    /**
     * API error status code.
     *
     * @var string
     */
    public string $status;

    /**
     * User-friendly message.
     *
     * @var string
     */
    public string $message;

    /**
     * Response data (if any).
     *
     * @var mixed
     */
    public $data = null;

    /**
     * Response constructor.
     *
     * @param string $status
     * @param string $message
     * @param mixed $data
     * @return void
     */
    public function __construct(string $status = 'EMPTY_RESPONSE', string $message = 'Empty Response', $data = null) {
        $this->status = $status;
        $this->message = $message;
        $this->data = $data;
    }

}