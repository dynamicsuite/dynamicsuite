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
 * Class Groups.
 *
 * @package DynamicSuite\Data
 */
final class Groups extends InstanceMember
{

    /**
     * Column length limits.
     *
     * @var int[]
     */
    public const COLUMN_LIMITS = [
        'name' => 64,
        'description' => 64,
        'domain' => 64,
        'created_by' => 254
    ];

    /**
     * Groups constructor.
     *
     * @param DynamicSuite $ds
     * @return void
     */
    public function __construct(DynamicSuite $ds)
    {
        parent::__construct($ds);
    }

    /**
     * Create a group.
     *
     * @param Group $group
     * @return Group
     * @throws PDOException
     */
    public function create(Group $group): Group
    {
        $group->created_on = date('Y-m-d H:i:s');
        $group->created_by = $this->created_by ?? $this->ds->session->user->username ?? null;
        $group->validate($group, self::COLUMN_LIMITS);
        $group->group_id = $this->ds->db->query((new Query())
            ->insert([
                'name' => $group->name,
                'description' => $group->description,
                'domain' => $group->domain,
                'created_by' => $group->created_by,
                'created_on' => $group->created_on
            ])
            ->into('ds_groups')
        );
        return $group;
    }

    /**
     * Attempt to read a group by name, with an optional domain.
     *
     * @param string|null $name
     * @param string|null $domain
     * @return Group|bool
     * @throws PDOException
     */
    public function readByName(?string $name, ?string $domain = null)
    {
        $query = (new Query())
            ->select()
            ->from('ds_groups')
            ->where('name', '=', $name);
        if ($domain) {
            $query->where('domain', '=', $domain);
        }
        $group = $this->ds->db->query($query);
        if (count($group) !== 1 || !isset($group[0])) {
            return false;
        }
        return new Group($group[0]);
    }

    /**
     * Attempt to read a group by ID, with an optional domain.
     *
     * @param int|null $id
     * @param string|null $domain
     * @return Group|bool
     * @throws PDOException
     */
    public function readById(?int $id, ?string $domain = null)
    {
        $query = (new Query())
            ->select()
            ->from('ds_groups')
            ->where('group_id', '=', $id);
        if ($domain) {
            $query->where('domain', '=', $domain);
        }
        $group = $this->ds->db->query($query);
        if (count($group) !== 1 || !isset($group[0])) {
            return false;
        }
        return new Group($group[0]);
    }

    /**
     * Update a group.
     * 
     * @param Group $group
     * @return Group
     * @throws PDOException
     */
    public function update(Group $group): Group
    {
        $group->validate($group, self::COLUMN_LIMITS);
        $this->ds->db->query((new Query())
            ->update('ds_groups')
            ->set([
                'name' => $group->name,
                'description' => $group->description
            ])
            ->where('group_id', '=', $group->group_id)
        );
        return $group;
    }

    /**
     * Delete a group.
     * 
     * @param Group $group
     * @return Group
     * @throws PDOException
     */
    public function delete(Group $group): Group
    {
        $this->ds->db->query((new Query())
            ->delete()
            ->from('ds_groups')
            ->where('group_id', '=', $group->group_id)
        );
        return $group;
    }

}