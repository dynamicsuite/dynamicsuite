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
 * Class Events.
 *
 * @package DynamicSuite\Data
 */
final class Events extends InstanceMember
{

    /**
     * Column length limits.
     *
     * @var int[]
     */
    public const COLUMN_LIMITS = [
        'package_id' => 64,
        'type' => 4294967295,
        'created_by' => 254,
        'ip' => 39,
        'session' => 64,
        'affected' => 254,
        'message' => 2048,
        'filter_1' => 4294967295,
        'filter_2' => 4294967295,
        'filter_3' => 4294967295,
        'filter_4' => 4294967295,
        'filter_5' => 4294967295,
    ];

    /**
     * Events constructor.
     *
     * @param DynamicSuite $ds
     * @return void
     */
    public function __construct(DynamicSuite $ds)
    {
        parent::__construct($ds);
    }

    /**
     * Get an array of events.
     *
     * @param string|null $package_id
     * @param int|null $type
     * @param int $limit
     * @return Event[]
     * @throws PDOException
     */
    public function get(?string $package_id = null, ?int $type = null, int $limit = 255): array
    {
        $events = [];
        $query = (new Query())->select()->from('ds_events');
        if ($package_id !== null) $query->where('package_id', '=', $package_id);
        if ($type !== null) $query->where('type', '=', $type);
        $query->limit($limit);
        $rows = $this->ds->db->query($query);
        foreach ($rows as $row) $events[] = new Event($row);
        return $events;
    }

    /**
     * Create a new event log entry.
     *
     * @param Event $event
     * @return Event
     * @throws PDOException
     */
    public function create(Event $event): Event
    {
        $event->created_by = $this->ds->session->user->username ?? null;
        $event->ip = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
        $event->session = session_id() ? session_id() : null;
        $event->timestamp = date('Y-m-d H:i:s');
        $event->validate($event, self::COLUMN_LIMITS);
        $event->event_id = $this->ds->db->query((new Query())
            ->insert([
                'timestamp' => $event->timestamp,
                'package_id' => $event->package_id,
                'type' => $event->type,
                'created_by' => $event->created_by,
                'ip' => $event->ip,
                'session' => $event->session,
                'affected' => $event->affected,
                'message' => $event->message,
                'filter_1' => $event->filter_1,
                'filter_2' => $event->filter_2,
                'filter_3' => $event->filter_3,
                'filter_4' => $event->filter_4,
                'filter_5' => $event->filter_5,
            ])
            ->into('ds_events')
        );
        return $event;
    }

    /**
     * Truncate the events table by an optional package ID and type.
     *
     * Returns the number of truncated rows.
     *
     * @param string|null $package_id
     * @param int|null $type
     * @return int
     * @throws PDOException
     */
    public function truncate(?string $package_id = null, ?int $type = null): int
    {
        $query = (new Query())->delete()->from('ds_events');
        if ($package_id !== null) $query->where('package_id', '=', $package_id);
        if ($type !== null) $query->where('type', '=', $type);
        return $this->ds->db->query($query);
    }

}