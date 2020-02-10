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
 * Class Event.
 *
 * @package DynamicSuite\Data
 * @property int|null $event_id
 * @property string|null $timestamp
 * @property string|null $package_id
 * @property int|null $type
 * @property string|null $created_by
 * @property string|null $ip
 * @property string|null $session
 * @property string|null $affected
 * @property string $message
 * @property int|null $filter_1
 * @property int|null $filter_2
 * @property int|null $filter_3
 * @property int|null $filter_4
 * @property int|null $filter_5
 * @property string|null $filter_6
 * @property string|null $filter_7
 * @property string|null $filter_8
 * @property string|null $filter_9
 * @property string|null $filter_10
 *
 */
class Event extends DatabaseItem
{

    /**
     * Event ID.
     *
     * @var int|null
     */
    public ?int $event_id = null;

    /**
     * Timestamp of the event.
     *
     * @var string|null
     */
    public ?string $timestamp = null;

    /**
     * Package ID associated with the event.
     *
     * @var string|null
     */
    public ?string $package_id = null;

    /**
     * Event type.
     *
     * @var int|null
     */
    public ?int $type = null;

    /**
     * Who/what generated the event.
     *
     * @var string|null
     */
    public ?string $created_by = null;

    /**
     * The IP the event was generated from.
     *
     * @var string|null
     */
    public ?string $ip = null;

    /**
     * The session the event was generated from.
     *
     * @var string|null
     */
    public ?string $session = null;

    /**
     * What the event affects.
     *
     * @var string|null
     */
    public ?string $affected = null;

    /**
     * A message describing the event.
     *
     * @var string|null
     */
    public ?string $message = null;

    /**
     * Application filter #1.
     * 
     * @var int|null
     */
    protected ?int $filter_1 = null;

    /**
     * Application filter #2.
     *
     * @var int|null
     */
    protected ?int $filter_2 = null;

    /**
     * Application filter #3.
     *
     * @var int|null
     */
    protected ?int $filter_3 = null;

    /**
     * Application filter #4.
     *
     * @var int|null
     */
    protected ?int $filter_4 = null;

    /**
     * Application filter #5.
     *
     * @var int|null
     */
    protected ?int $filter_5 = null;

    /**
     * Application filter #6.
     *
     * @var string|null
     */
    protected ?string $filter_6 = null;

    /**
     * Application filter #7.
     *
     * @var string|null
     */
    protected ?string $filter_7 = null;

    /**
     * Application filter #8.
     *
     * @var string|null
     */
    protected ?string $filter_8 = null;

    /**
     * Application filter #9.
     *
     * @var string|null
     */
    protected ?string $filter_9 = null;

    /**
     * Application filter #10.
     *
     * @var string|null
     */
    protected ?string $filter_10 = null;

    /**
     * Event constructor.
     *
     * @param array $event
     * @return void
     */
    public function __construct(array $event = [])
    {
        parent::__construct($event);
    }

}