<?php
/**
 * This file is part of the Dynamic Suite framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @package DynamicSuite\Package
 * @author Grant Martin <commgdog@gmail.com>
 * @copyright 2021 Dynamic Suite Team
 */

namespace DynamicSuite\Package;
use ArgumentCountError;
use DynamicSuite\Util\Format;
use Error;
use Exception;

/**
 * Class Package.
 *
 * @package DynamicSuite\Package
 * @property string $package_id
 * @property string|null $name
 * @property string|null $author
 * @property string|null $version
 * @property string|null $description
 * @property string|null $license
 * @property string[] $autoload
 * @property string[] $init
 * @property string[] $js
 * @property string[] $css
 * @property array $apis
 */
final class Package
{

    /**
     * Package constructor.
     *
     * @param string $package_id
     * @param string|null $name
     * @param string|null $author
     * @param string|null $version
     * @param string|null $description
     * @param string|null $license
     * @param string[] $autoload
     * @param string[] $init
     * @param string[] $js
     * @param string[] $css
     * @param array $apis
     * @return void
     */
    public function __construct(
        protected string $package_id,
        protected ?string $name = null,
        protected ?string $author = 'Anonymous',
        protected ?string $version = '0.0.0',
        protected ?string $description = 'N/A',
        protected ?string $license = 'none',
        protected array $autoload = [],
        protected array $init = [],
        protected array $js = [],
        protected array $css = [],
        protected array $apis = []
    ) {
        $this->name ??= $this->package_id;
        $this->loadIncludes();
        $this->loadAPIs();

        //$this->loadNavGroups($structure);
        //$this->loadActionGroups($structure);
        //$this->loadActionLinks($structure);
        //$this->loadViews($structure);
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

    /**
     * Add all of the includes provided by the package to Dynamic Suite.
     *
     * @return void
     */
    private function loadIncludes(): void
    {
        foreach (['autoload', 'init', 'js', 'css'] as $type) {
            foreach ($this->$type as $path) {
                if (!is_string($path)) {
                    error_log("[$this->package_id] [structure violation]: '$type' must be an array of strings");
                    continue;
                }
                $formatter = $type === 'autoload' || $type === 'init'
                    ? 'formatServerPath'
                    : 'formatClientPath';
                $path = Format::$formatter($this->package_id, $path);
                if (!in_array($path, Packages::$$type)) {
                    array_push(Packages::$$type, $path);
                }
            }
        }
    }

    /**
     * Add all of the apis provided by the package to Dynamic Suite.
     *
     * @return void
     */
    private function loadAPIs(): void
    {
        if (!$this->apis) {
            return;
        }
        if (!is_array($this->apis)) {
            error_log("[$this->package_id] [structure violation]: 'apis' must be an array");
            return;
        }
        foreach ($this->apis as $api_id => $structure) {
            try {
                Packages::$apis[$api_id]  = new API($api_id, $this->package_id, ...$structure);
            } catch (Exception | ArgumentCountError $exception) {
                error_log($exception->getMessage());
                continue;
            } catch (Error $error) {
                error_log("[$this->package_id] [structure violation] in api '$api_id': "  . $error->getMessage());
            }
        }
    }

    /**
     * Load navigational groups.
     *
     * $structure is the referenced structure array for the package.
     *
     * @param array $structure
     * @return bool
     */
    private function loadNavGroups(array $structure): bool
    {
        if (!isset($structure['nav_groups'])) {
            return false;
        }
        if (!is_array($structure['nav_groups'])) {
            error_log("[Structure] Package \"$this->package_id\" key \"nav_groups\" must be an array");
            return false;
        }
        foreach ($structure['nav_groups'] as $group_id => $group) {
            try {
                $this->nav_groups[$group_id] = new NavGroup($group_id, $this->package_id, $group);
                Packages::$nav_groups[$group_id] = $this->nav_groups[$group_id];
            } catch (Exception $exception) {
                error_log($exception->getMessage());
                continue;
            }
        }
        return true;
    }

    /**
     * Load package action groups.
     *
     * @param array $structure
     * @return bool
     */
    private function loadActionGroups(array $structure): bool
    {
        if (!isset($structure['action_groups'])) {
            return false;
        }
        $error = function (string $message) {
            return "[Structure] Package \"$this->package_id\" key \"action_groups\": $message";
        };
        if (is_string($structure['action_groups'])) {
            $structure['action_groups'] = [$structure['action_groups']];
        } elseif ($structure['action_groups'] === null) {
            $structure['action_groups'] = [];
        } elseif (is_array($structure['action_groups'])) {
            foreach ($structure['action_groups'] as $group) {
                if (!is_string($group)) {
                    error_log($error('must be a string or array of strings'));
                    return false;
                }
            }
        } else {
            error_log($error('must be a string or array of strings'));
            return false;
        }
        $this->action_groups = $structure['action_groups'];
        Packages::$action_groups = array_merge(Packages::$action_groups, $this->action_groups);
        return true;
    }

    /**
     * Load package action links.
     *
     * @param array $structure
     * @return bool
     */
    private function loadActionLinks(array $structure): bool
    {
        if (!isset($structure['action_links'])) {
            return false;
        }
        if (!is_array($structure['action_links'])) {
            error_log("[Structure] Package \"$this->package_id\" key \"action_links\" must be an array");
            return false;
        }
        foreach ($structure['action_links'] as $link_id => $link) {
            try {
                $this->action_links[$link_id] = new HeaderAction($link_id, $this->package_id, $link);
                Packages::$action_links = array_merge(Packages::$action_links, $this->action_links);
            } catch (Exception $exception) {
                error_log($exception->getMessage());
                continue;
            }
        }
        return true;
    }

    /**
     * Load package views.
     *
     * $structure is the referenced structure array for the package.
     *
     * @param array $structure
     * @return bool
     */
    private function loadViews(array $structure): bool
    {
        if (!isset($structure['views'])) {
            return false;
        }
        if (!is_array($structure['views'])) {
            error_log("[Structure] Package \"$this->package_id\" key \"views\" must be an array of valid views");
            return false;
        }
        foreach ($structure['views'] as $view_id => $view) {
            try {
                $this->views[$view_id] = new View($view_id, $this->package_id, $view);
                Packages::$views[$view_id] = $this->views[$view_id];
            } catch (Exception $exception) {
                error_log($exception->getMessage());
                continue;
            }
        }
        return true;
    }

}