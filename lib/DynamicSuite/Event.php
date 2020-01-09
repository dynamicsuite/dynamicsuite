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
use PDOException;

/**
 * Class Event.
 *
 * @package DynamicSuite
 * @property int $id
 * @property string $timestamp
 * @property string $package_id
 * @property int $type
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
 *
 */
class Event extends ProtectedObject
{

    /**
     * Event ID.
     *
     * @var int
     */
    protected $id;

    /**
     * Timestamp of the event.
     *
     * @var string
     */
    protected $timestamp;

    /**
     * Package ID associated with the event.
     *
     * @var string
     */
    protected $package_id;

    /**
     * Event type.
     *
     * @var int
     */
    protected $type;

    /**
     * Who/what generated the event.
     *
     * @var string
     */
    protected $created_by;

    /**
     * The IP the event was generated from.
     *
     * @var string
     */
    protected $ip;

    /**
     * The session the event was generated from.
     *
     * @var string
     */
    protected $session;

    /**
     * What the event affects.
     *
     * @var string
     */
    protected $affected;

    /**
     * A message describing the event.
     *
     * @var string
     */
    protected $message;

    /**
     * Application filter #1.
     * 
     * @var int|null
     */
    protected $filter_1;

    /**
     * Application filter #2.
     *
     * @var int|null
     */
    protected $filter_2;
    /**
     * Application filter #3.
     *
     * @var int|null
     */
    protected $filter_3;

    /**
     * Application filter #4.
     *
     * @var int|null
     */
    protected $filter_4;

    /**
     * Application filter #5.
     *
     * @var int|null
     */
    protected $filter_5;

    /**
     * Maximum length that an event package ID can be.
     *
     * @var int
     */
    const MAX_PACKAGE_ID_LENGTH = 64;

    /**
     * Maximum length that an event type can be.
     *
     * @var int
     */
    const MAX_TYPE = 4294967295;

    /**
     * Maximum length of the entity that created the event.
     *
     * @var int
     */
    const MAX_CREATED_BY_LENGTH = 254;

    /**
     * Maximum length of the entities IP address that created the event.
     *
     * @var int
     */
    const MAX_IP_LENGTH = 39;

    /**
     * Maximum length of the entities session ID that created the event.
     *
     * @var int
     */
    const MAX_SESSION_LENGTH = 64;

    /**
     * Maximum length that an event affected entity can be.
     *
     * @var int
     */
    const MAX_AFFECTED_LENGTH = 254;

    /**
     * Maximum length that an event message can be.
     *
     * @var int
     */
    const MAX_MESSAGE_LENGTH = 2048;

    /**
     * Maximum length an application filter can be.
     * 
     * @var int
     */
    const MAX_FILTER_LENGTH = 4294967295;

    /**
     * Event constructor.
     *
     * @param array|null $event
     * @return void
     */
    public function __construct(array $event = null)
    {
        if (isset($event['event_id'])) $this->id = $event['event_id'];
        if (isset($event['timestamp'])) $this->timestamp = $event['timestamp'];
        if (isset($event['package_id'])) $this->package_id = $event['package_id'];
        if (isset($event['type'])) $this->type = $event['type'];
        if (isset($event['created_by'])) $this->created_by = $event['created_by'];
        if (isset($event['ip'])) $this->ip = $event['ip'];
        if (isset($event['session'])) $this->session = $event['session'];
        if (isset($event['affected'])) $this->affected = $event['affected'];
        if (isset($event['message'])) $this->message = $event['message'];
        if (isset($event['filter_1'])) $this->setFilter1($event['filter_1']);
        if (isset($event['filter_2'])) $this->setFilter2($event['filter_2']);
        if (isset($event['filter_3'])) $this->setFilter3($event['filter_3']);
        if (isset($event['filter_4'])) $this->setFilter4($event['filter_4']);
        if (isset($event['filter_5'])) $this->setFilter5($event['filter_5']);
    }

    /**
     * Set the event ID.
     *
     * @param int $id
     * @return Event
     */
    public function setId(int $id): Event
    {
        $this->id = $id;
        return $this;
    }

    /**
     * Set the timestamp the event took place.
     *
     * @param string $timestamp
     * @return Event
     */
    public function setTimestamp(string $timestamp): Event
    {
        $this->timestamp = $timestamp;
        return $this;
    }

    /**
     * Set the package ID associated with the event.
     *
     * @param string $package_id
     * @return Event
     */
    public function setPackageId(string $package_id): Event
    {
        $this->package_id = $package_id;
        return $this;
    }

    /**
     * Set the event type.
     *
     * @param int $type
     * @return Event
     */
    public function setType(int $type): Event
    {
        $this->type = $type;
        return $this;
    }

    /**
     * Set who created the event.
     *
     * @param string|null $created_by
     * @return Event
     */
    public function setCreatedBy(?string $created_by): Event
    {
        $this->created_by = $created_by;
        return $this;
    }

    /**
     * Set the IP address where the event was generated from.
     *
     * @param string|null $ip
     * @return Event
     */
    public function setIp(?string $ip): Event
    {
        $this->ip = $ip;
        return $this;
    }

    /**
     * Set the session ID that the event was generated under.
     *
     * @param string|null $session
     * @return Event
     */
    public function setSession(?string $session): Event
    {
        $this->session = $session;
        return $this;
    }

    /**
     * Set the what the event affected.
     *
     * @param string|null $affected
     * @return Event
     */
    public function setAffected(?string $affected): Event
    {
        $this->affected = $affected;
        return $this;
    }

    /**
     * Set the event message.
     *
     * @param string $message
     * @return Event
     */
    public function setMessage(string $message): Event
    {
        $this->message = $message;
        return $this;
    }

    /**
     * Set application filter #1.
     * 
     * @param int $filter_1
     * @return Event
     */
    public function setFilter1(?int $filter_1 = null): Event
    {
        $this->filter_1 = $filter_1;
        return $this;
    }

    /**
     * Set application filter #2.
     *
     * @param int $filter_2
     * @return Event
     */
    public function setFilter2(?int $filter_2 = null): Event
    {
        $this->filter_2 = $filter_2;
        return $this;
    }

    /**
     * Set application filter #3.
     *
     * @param int $filter_3
     * @return Event
     */
    public function setFilter3(?int $filter_3 = null): Event
    {
        $this->filter_3 = $filter_3;
        return $this;
    }

    /**
     * Set application filter #4.
     *
     * @param int $filter_4
     * @return Event
     */
    public function setFilter4(?int $filter_4 = null): Event
    {
        $this->filter_4 = $filter_4;
        return $this;
    }

    /**
     * Set application filter #5.
     *
     * @param int $filter_5
     * @return Event
     */
    public function setFilter5(?int $filter_5 = null): Event
    {
        $this->filter_5 = $filter_5;
        return $this;
    }

    /**
     * Get the event as an array.
     *
     * @return array
     * @noinspection PhpUnused
     */
    public function asArray(): array
    {
        return [
            'event_id' => $this->id,
            'timestamp' => $this->timestamp,
            'package_id' => $this->package_id,
            'type' => $this->type,
            'created_by' => $this->created_by,
            'ip' => $this->ip,
            'session' => $this->session,
            'affected' => $this->affected,
            'message' => $this->message,
            'filter_1' => $this->filter_1,
            'filter_2' => $this->filter_2,
            'filter_3' => $this->filter_3,
            'filter_4' => $this->filter_4,
            'filter_5' => $this->filter_5,
        ];
    }

    /**
     * Validate the current event for usage in the database.
     *
     * @return bool
     * @throws PDOException
     */
    public function validateForDatabase(): bool
    {
        $errors = [];
        if (strlen($this->package_id) > self::MAX_PACKAGE_ID_LENGTH) {
            $errors['package_id'] = "$this->package_id > " .  self::MAX_PACKAGE_ID_LENGTH . ' characters';
        }
        if ($this->type > self::MAX_TYPE) {
            $errors['type'] = "$this->type > " .  self::MAX_TYPE;
        }
        if (strlen($this->created_by) > self::MAX_CREATED_BY_LENGTH) {
            $errors['created_by'] = "$this->created_by > " .  self::MAX_CREATED_BY_LENGTH . ' characters';
        }
        if (strlen($this->ip) > self::MAX_IP_LENGTH) {
            $errors['ip'] = "$this->ip > " .  self::MAX_IP_LENGTH . ' characters';
        }
        if (strlen($this->session) > self::MAX_SESSION_LENGTH) {
            $errors['session'] = "$this->session > " .  self::MAX_SESSION_LENGTH . ' characters';
        }
        if (strlen($this->affected) > self::MAX_AFFECTED_LENGTH) {
            $errors['affected'] = "$this->affected > " .  self::MAX_AFFECTED_LENGTH . ' characters';
        }
        if (strlen($this->message) > self::MAX_MESSAGE_LENGTH) {
            $errors['message'] = "$this->message > " .  self::MAX_MESSAGE_LENGTH . ' characters';
        }
        if ($this->filter_1 > self::MAX_FILTER_LENGTH) {
            $errors['filter_1'] = "$this->filter_1 > " . self::MAX_FILTER_LENGTH;
        }
        if ($this->filter_2 > self::MAX_FILTER_LENGTH) {
            $errors['filter_2'] = "$this->filter_2 > " . self::MAX_FILTER_LENGTH;
        }
        if ($this->filter_3 > self::MAX_FILTER_LENGTH) {
            $errors['filter_3'] = "$this->filter_3 > " . self::MAX_FILTER_LENGTH;
        }
        if ($this->filter_4 > self::MAX_FILTER_LENGTH) {
            $errors['filter_4'] = "$this->filter_4 > " . self::MAX_FILTER_LENGTH;
        }
        if ($this->filter_5 > self::MAX_FILTER_LENGTH) {
            $errors['filter_5'] = "$this->filter_5 > " . self::MAX_FILTER_LENGTH;
        }
        if (!empty($errors)) {
            $message = 'Event has data that exceeds database limits' . PHP_EOL;
            foreach ($errors as $k => $v) {
                $message .= "  -- $k: $v" . PHP_EOL;
            }
            throw new PDOException($message);
        } else {
            return true;
        }
    }

}