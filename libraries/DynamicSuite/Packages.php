<?php
/**
 * This file is part of the Dynamic Suite framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @package DynamicSuite
 * @author Grant Martin <commgdog@gmail.com>
 * @copyright 2021 Dynamic Suite Team
 * @noinspection PhpUnused
 */

namespace DynamicSuite;
use DynamicSuite\Package\API;
use DynamicSuite\Package\OverlayAction;
use DynamicSuite\Package\NavGroup;
use DynamicSuite\Package\Package;
use DynamicSuite\Package\View;
use Error;

/**
 * Class Packages.
 *
 * @package DynamicSuite
 */
final class Packages
{

    /**
     * Loaded package structure files as an array.
     *
     * @var Package[]
     */
    public static array $loaded = [];

    /**
     * Directories to add to the autoload queue.
     *
     * @var string[]
     */
    public static array $autoload = [];

    /**
     * Initialization scripts to execute.
     *
     * @var string[]
     */
    public static array $init = [];

    /**
     * JS script to include.
     *
     * @var string[]
     */
    public static array $js = [];

    /**
     * CSS resources to include.
     *
     * @var string[]
     */
    public static array $css = [];

    /**
     * Loaded package views.
     *
     * @var View[]
     */
    public static array $views = [];

    /**
     * Loaded APIs.
     *
     * @var API[]
     */
    public static array $apis = [];

    /**
     * Loaded navigation groups.
     *
     * @var NavGroup[]
     */
    public static array $nav_groups = [];

    /**
     * Loaded overlay actions.
     *
     * @var OverlayAction[]
     */
    public static array $overlay_actions = [];

    /**
     * Initialize all package structures and includes.
     *
     * @return void
     */
    public static function init(): void
    {
        $hash = 'ds' . crc32(__FILE__);
        if (DS_CACHING && apcu_exists($hash) && $cache = apcu_fetch($hash)) {
            self::$loaded = $cache['loaded'];
            self::$autoload = $cache['autoload'];
            self::$init = $cache['init'];
            self::$js = $cache['js'];
            self::$css = $cache['css'];
            self::$views = $cache['views'];
            self::$apis = $cache['apis'];
            self::$nav_groups = $cache['nav_groups'];
            self::$overlay_actions = $cache['overlay_actions'];
        } else {
            foreach (DynamicSuite::$cfg->packages as $package_id) {
                self::load($package_id);
            }
            if (DS_CACHING) {
                $store = apcu_store($hash, [
                    'loaded' => self::$loaded,
                    'autoload' => self::$autoload,
                    'init' => self::$init,
                    'js' => self::$js,
                    'css' => self::$css,
                    'views' => self::$views,
                    'apis' => self::$apis,
                    'nav_groups' => self::$nav_groups,
                    'overlay_actions' => self::$overlay_actions
                ]);
                if (!$store) {
                    error_log('Error saving "Packages" in cache, check server config');
                }
            }
        }
    }

    /**
     * Parse a packages structure file and add it to the loaded packages list.
     *
     * Given a $package_id, it will look for the structure file at:
     *     packages/$package_id/$package_id.json
     *
     * @param string $package_id
     * @return void
     */
    public static function load(string $package_id): void
    {
        $json_path = DS_ROOT_DIR . "/packages/$package_id/$package_id.json";
        if (!is_readable($json_path)) {
            error_log("Package [$package_id] structure not readable", E_USER_WARNING);
            return;
        }
        if (!$structure = json_decode(file_get_contents($json_path), true)) {
            error_log("Package [$package_id] structure invalid JSON", E_USER_WARNING);
            return;
        }
        try {
            self::$loaded[$package_id] = new Package($package_id, ...$structure);
        } catch (Error $error) {
            error_log("Package [$package_id] structure invalid: "  . $error->getMessage());
        }
    }

}