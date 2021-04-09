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
 * Class Format.
 *
 * @package DynamicSuite\Util
 */
final class Format
{

    /**
     * Format a server file path.
     *
     * @param string $package_id
     * @param string $path
     * @return string
     */
    public static function formatServerPath(string $package_id, string $path): string
    {
        return $path[0] === '/'
            ? DS_ROOT_DIR . $path
            : DS_ROOT_DIR . "/packages/$package_id/$path";
    }

    /**
     * Format a client resource path.
     *
     * @param string $package_id
     * @param string $path
     * @return string
     */
    public static function formatClientPath(string $package_id, string $path): string
    {
        return $path[0] === '/'
            ? $path
            : "/dynamicsuite/packages/$package_id/$path";
    }

}