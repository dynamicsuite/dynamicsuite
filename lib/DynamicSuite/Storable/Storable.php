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
 */

namespace DynamicSuite\Storable;

/**
 * Class Storable.
 *
 * @package DynamicSuite\Storable
 */
abstract class Storable
{

    /**
     * Storable constructor.
     *
     * @param array $array
     * @return void
     */
    public function __construct(array $array = [])
    {
        foreach ($array as $prop => $value) {
            if (property_exists($this, $prop)) {
                $this->$prop = $value;
            }
        }
    }

}