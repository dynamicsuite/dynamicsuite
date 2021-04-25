<?php
/**
 * This file is part of the Dynamic Suite framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @package DynamicSuite\Util
 * @author Grant Martin <commgdog@gmail.com>
 * @copyright 2021 Dynamic Suite Team
 */

namespace DynamicSuite\Util;

/**
 * Class Template.
 *
 * @package DynamicSuite\Util
 * @property string $contents
 */
final class Template
{

    /**
     * Template constructor.
     *
     * @return void
     */
    public function __construct(public string $contents = '') {}

    /**
     * Search and replace a string(s) in template.
     *
     * @param mixed $replace
     * @return Template
     */
    public function replace(array $replace): Template
    {
        $this->contents = strtr($this->contents, $replace);
        return $this;
    }

}