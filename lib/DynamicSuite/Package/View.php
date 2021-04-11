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
 * @noinspection PhpUndefinedFieldInspection
 */

namespace DynamicSuite\Package;
use DynamicSuite\Util\Format;
use Exception;

/**
 * Class View.
 *
 * @package DynamicSuite\Package
 * @property string $view_id
 * @property string $package_id
 * @property string|null $version
 * @property string|null $entry
 * @property string|null $title
 * @property bool $public
 * @property bool $navigable
 * @property bool $hide_ds
 * @property string|null $nav_name
 * @property string $nav_icon
 * @property string|null $nav_group
 * @property string[] $permissions
 * @property string[] $autoload
 * @property string[] $init
 * @property string[] $js
 * @property string[] $css
 */
final class View
{

    /**
     * View constructor.
     *
     * @return void
     * @throws Exception
     */
    public function __construct(
        protected string $view_id,
        protected string $package_id,
        protected ?string $version = null,
        protected ?string $entry = null,
        protected ?string $title = null,
        protected bool $public = false,
        protected bool $navigable = true,
        protected bool $hide_ds = false,
        protected ?string $nav_name = null,
        protected string $nav_icon = 'fas fa-cog',
        protected ?string $nav_group = null,
        protected array $permissions = [],
        protected array $autoload = [],
        protected array $init = [],
        protected array $js = [],
        protected array $css = []
    ) {
        if (!isset($view_id[0]) || $view_id[0] !== '/') {
            throw new Exception("view ID must start with a forward slash");
        }
        if ($entry === null) {
            throw new Exception("missing entry point");
        }
        foreach (['permissions', 'autoload', 'init', 'js', 'css'] as $prop) {
            foreach ($this->$prop as $key => $value) {
                if (!is_string($value)) {
                    throw new Exception("$prop must be an array of strings");
                }
                if ($prop === 'autoload' || $prop === 'init') {
                    $this->$prop[$key] = Format::formatServerPath($package_id, $value);
                }
                if ($prop === 'js' || $prop === 'css') {
                    $this->$prop[$key] = Format::formatClientPath($package_id, $value) . '?v=' . $this->version;
                }
            }
        }
        $this->entry = Format::formatServerPath($package_id, $entry);
        $this->title ??= $view_id;
        $this->nav_name ??= $view_id;
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