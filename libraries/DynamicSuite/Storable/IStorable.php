<?php
/**
 * This file is part of the Dynamic Suite framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @package DynamicSuite\Storable
 * @author Grant Martin <commgdog@gmail.com>
 * @copyright 2021 Dynamic Suite Team
 * @noinspection PhpUnused
 */

namespace DynamicSuite\Storable;

/**
 * Interface IStorable.
 *
 * @package DynamicSuite\Storable
 */
interface IStorable
{

    /**
     * Create a storable object in the database.
     *
     * @return Storable
     */
    public function create(): Storable;

    /**
     * Read a storable object from the database by ID.
     *
     * @param int|null $id
     * @return Storable|bool
     */
    public static function readById(?int $id = null): Storable | bool;

    /**
     * Update a stored object in the database.
     *
     * @return Storable
     */
    public function update(): Storable;

    /**
     * Delete a stored object from the database.
     *
     * @return Storable
     */
    public function delete(): Storable;

}