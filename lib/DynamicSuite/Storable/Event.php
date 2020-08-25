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
 * Class Event.
 *
 * @package DynamicSuite\Storable
 * @property int|null $event_id
 * @property string|null $package_id
 * @property int|null $type
 * @property string|null $domain
 * @property string|null $ip
 * @property string|null $session
 * @property string|null $affected
 * @property string $message
 * @property string|null $created_by
 * @property string|null $created_on
 *
 */
class Event extends Storable implements IStorable
{

    /**
     * Column length limits.
     *
     * @var int[]
     */
    public const COLUMN_LIMITS = [
        'package_id' => 64,
        'type' => 4294967295,
        'domain' => 255,
        'ip' => 39,
        'session' => 64,
        'affected' => 254,
        'created_by' => 254
    ];

    /**
     * Event ID.
     *
     * @var int|null
     */
    public ?int $event_id = null;

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
     * Event domain.
     *
     * @var string|null
     */
    public ?string $domain = null;

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
     * Event creation source.
     *
     * @var string|null
     */
    public ?string $created_by = null;

    /**
     * The timestamp when the event was created.
     *
     * @var string|null
     */
    public ?string $created_on = null;

    /**
     * Event constructor.
     *
     * @param array $event
     */
    public function __construct(array $event = [])
    {
        parent::__construct($event);
    }

    /**
     * Create the event in the database.
     *
     * @return Event
     * @throws Exception|PDOException
     */
    public function create(): Event
    {
        $this->ip = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
        $this->session = session_id() ? session_id() : null;
        $this->created_by = $this->created_by ?? Session::$user_name;
        $this->created_on = date('Y-m-d H:i:s');
        $this->validate(self::COLUMN_LIMITS);
        $this->event_id = (new Query())
            ->insert([
                'package_id' => $this->package_id,
                'type' => $this->type,
                'domain' => $this->domain,
                'ip' => $this->ip,
                'session' => $this->session,
                'affected' => $this->affected,
                'message' => $this->message,
                'created_by' => $this->created_by,
                'created_on' => $this->created_on
            ])
            ->into('ds_events')
            ->execute();
        return $this;
    }

    /**
     * Attempt to read an event by ID.
     *
     * Returns the Event if found, or FALSE if not found.
     *
     * @param int|null $id
     * @return bool|Event
     * @throws Exception|PDOException
     */
    public static function readById(?int $id = null)
    {
        if ($id === null) {
            return false;
        }
        $event = (new Query())
            ->select()
            ->from('ds_events')
            ->where('event_id', '=', $id)
            ->execute(true);
        return $event ? new Event($event) : false;
    }

    /**
     * Update the event in the database.
     *
     * @return Event
     * @throws Exception|PDOException
     */
    public function update(): Event
    {
        $this->validate(self::COLUMN_LIMITS);
        (new Query())
            ->update('ds_events')
            ->set([
                'package_id' => $this->package_id,
                'type' => $this->type,
                'domain' => $this->domain,
                'affected' => $this->affected,
                'message' => $this->message
            ])
            ->where('event_id', '=', $this->event_id)
            ->execute();
        return $this;
    }

    /**
     * Delete the event from the database.
     *
     * @return Event
     * @throws Exception|PDOException
     */
    public function delete(): Event
    {
        (new Query())
            ->delete()
            ->from('ds_events')
            ->where('event_id', '=', $this->event_id)
            ->execute();
        return $this;
    }

}