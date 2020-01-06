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

namespace DynamicSuite;
use LengthException, PDOException;

/**
 * Class Events.
 *
 * @package DynamicSuite
 */
class Events extends InstanceMember
{

    /**
     * Events constructor.
     *
     * @param Instance $ds
     * @return void
     */
    public function __construct(Instance $ds)
    {
        parent::__construct($ds);
    }

    /**
     * Get an array of events.
     *
     * @param string|null $package_id
     * @param int|null $type
     * @return Event[]
     * @throws PDOException
     */
    public function get(?string $package_id, ?int $type): array
    {
        $events = [];
        $query = (new Query())->select()->from('ds_events');
        if ($package_id !== null) $query->where('package_id', '=', $package_id);
        if ($type !== null) $query->where('type', '=', $type);
        $rows = $this->ds->db->query($query);
        foreach ($rows as $row) $events[] = new Event($row);
        return $events;
    }

    /**
     * Create a new event log message.
     *
     * @param Event $event
     * @return Event
     * @throws LengthException
     * @throws PDOException
     */
    public function create(Event $event): Event
    {
        if (!isset($this->ds->session) || $this->ds->session->id === null) {
            $created_by = null;
            $session = null;
        } else {
            $created_by = $this->ds->session->user->username ?? null;
            $session = session_id();
        }
        $event
            ->setCreatedBy($created_by)
            ->setIp($_SERVER['REMOTE_ADDR'] ?? '127.0.0.1')
            ->setSession($session)
            ->setTimestamp(date('Y-m-d H:i:s'))
            ->validateForDatabase();
        $event->setId($this->ds->db->query((new Query())
            ->insert([
                'timestamp' => $event->timestamp,
                'package_id' => $event->package_id,
                'type' => $event->type,
                'created_by' => $event->created_by,
                'ip' => $event->ip,
                'session' => $event->session,
                'affected' => $event->affected,
                'message' => $event->message
            ])
            ->into('ds_events')
        ));
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