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
     * Response constructor.
     *
     * @return void
     */
    public function __construct(
        public string $status = 'EMPTY_RESPONSE',
        public string $message = 'Empty Response',
        public $data = null
    ) {}

}