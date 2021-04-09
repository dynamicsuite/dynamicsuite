<?php
/**
 * This file is part of the Dynamic Suite framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @package DynamicSuite\API
 * @author Grant Martin <commgdog@gmail.com>
 * @copyright 2021 Dynamic Suite Team
 */

namespace DynamicSuite\API;

/**
 * Class Request.
 *
 * @package DynamicSuite\API
 * @property string $api_id
 * @property array $data
 */
class Request
{

    /**
     * Request constructor.
     *
     * @return void
     */
    public function __construct(public string $api_id, public array $data = []) {}

}