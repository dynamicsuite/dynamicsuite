<?php
/**
 * This file is part of the Dynamic Suite framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @package DynamicSuite\Core
 * @author Grant Martin <commgdog@gmail.com>
 * @copyright 2021 Dynamic Suite Team
 */

namespace DynamicSuite\Core;

/**
 * Class GlobalConfig.
 *
 * @package DynamicSuite\Core
 */
abstract class GlobalConfig
{

    /**
     * GlobalConfig constructor.
     *
     * @param string $package_id
     */
    public function __construct(string $package_id)
    {
        $path = DS_ROOT_DIR . "/config/$package_id.json";
        if (!file_exists($path)) {
            return;
        }
        $hash = 'ds' . crc32($path);
        if (DS_CACHING && apcu_exists($hash)) {
            $cfg = apcu_fetch($hash);
            if ($cfg === false) {
                error_log("Error fetching config from cache for $package_id", E_USER_WARNING);
            }
        } elseif (is_readable($path)) {
            $cfg = json_decode(file_get_contents($path), true);
            if ($cfg === null) {
                error_log("Error decoding config json for $package_id ($path)", E_USER_WARNING);
            } elseif (DS_CACHING) {
                apcu_store($hash, $cfg);
            }
        } else {
            error_log("Config not readable for $package_id ($path)", E_USER_WARNING);
            $cfg = false;
        }
        if ($cfg) {
            foreach ($cfg as $property => $value) {
                $this->$property = $value;
            }
        } else {
            trigger_error("Invalid config for $package_id, using defaults...", E_USER_WARNING);
        }
    }

    /**
     * Parameter getter magic method.
     *
     * @param string $property
     * @return mixed
     */
    public function __get(string $property)
    {
        return $this->$property;
    }

}