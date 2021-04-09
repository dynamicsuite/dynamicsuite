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
 * @property array $views
 */
final class Package
{

    /**
     * Package constructor.
     *
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
        protected array $apis = [],
        protected array $views = []
    ) {
        $this->name ??= $this->package_id;
        $this->loadIncludes();
        $this->loadAPIs();
        $this->loadViews();

        //$this->loadNavGroups($structure);
        //$this->loadActionLinks($structure);
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
     * Add all of the APIs provided by the package to Dynamic Suite.
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
     * Add all of the views provided by the package to Dynamic Suite.
     *
     * @return void
     */
    private function loadViews(): void
    {
        if (!$this->views) {
            return;
        }
        if (!is_array($this->apis)) {
            error_log("[$this->package_id] [structure violation]: 'views' must be an array");
            return;
        }
        foreach ($this->views as $view_id => $structure) {
            try {
                Packages::$views[$view_id]  = new View($view_id, $this->package_id, ...$structure);
            } catch (Exception | ArgumentCountError $exception) {
                error_log($exception->getMessage());
                continue;
            } catch (Error $error) {
                error_log("[$this->package_id] [structure violation] in view '$view_id': "  . $error->getMessage());
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

}