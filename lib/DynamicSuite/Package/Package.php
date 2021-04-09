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
use DynamicSuite\Core\Packages;
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
 * @property array $nav_groups
 * @property array $header_actions
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
        protected array $views = [],
        protected array $nav_groups = [],
        protected array $header_actions = []
    ) {
        $this->loadIncludes();
        $this->loadStructure('apis', API::class);
        $this->loadStructure('views', View::class);
        $this->loadStructure('nav_groups', NavGroup::class);
        $this->loadStructure('header_actions', HeaderAction::class);
        $this->name ??= $this->package_id;
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
        foreach (['autoload', 'init', 'js', 'css'] as $prop) {
            foreach ($this->$prop as $path) {
                if (!is_string($path)) {
                    error_log("Package [$this->package_id] $prop must be an array of strings");
                    continue;
                }
                $formatter = $prop === 'autoload' || $prop === 'init'
                    ? 'formatServerPath'
                    : 'formatClientPath';
                $path = Format::$formatter($this->package_id, $path);
                if (!in_array($path, Packages::$$prop)) {
                    array_push(Packages::$$prop, $path);
                }
            }
        }
    }

    /**
     * Add all of the structure content provided by the package to Dynamic Suite.
     *
     * @param string $type
     * @param string $class
     * @return void
     */
    private function loadStructure(string $type, string $class)
    {
        if (!$this->$type) {
            return;
        }
        foreach ($this->$type as $id => $structure) {
            if (!is_array($structure)) {
                error_log("Package [$this->package_id] $type '$id' must be an array");
                continue;
            }
            try {
                Packages::$$type[$id] = new $class($id, $this->package_id, ...$structure);
            } catch (Exception | ArgumentCountError $exception) {
                error_log($exception->getMessage());
            } catch (Error $error) {
                error_log("Package $type [$this->package_id.$id] "  . $error->getMessage());
            }
        }
    }

}