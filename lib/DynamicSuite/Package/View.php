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
use DynamicSuite\Util\Format;
use Exception;

/**
 * Class View.
 *
 * @package DynamicSuite\Package
 * @property string $view_id
 * @property string $package_id
 * @property string|null $entry
 * @property string|null $title
 * @property bool $public
 * @property bool $navigable
 * @property bool $hide_nav
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
        protected string|null $entry = null,
        protected string|null $title = null,
        protected bool $public = false,
        protected bool $navigable = true,
        protected bool $hide_nav = false,
        protected string|null $nav_name = null,
        protected string $nav_icon = 'fas fa-cog',
        protected string|null $nav_group = null,
        protected array $permissions = [],
        protected array $autoload = [],
        protected array $init = [],
        protected array $js = [],
        protected array $css = []
    ) {
        $error = function(string $key, string $message): string {
            return "[$this->package_id] [structure violation] in view '$this->view_id' key '$key': $message";
        };
        if ($this->entry === null) {
            throw new Exception($error('entry', 'Missing'));
        }
        $this->entry = Format::formatServerPath($package_id, $this->entry);
        $this->title ??= $this->view_id;
        $this->nav_name ??= $this->view_id;
        foreach (['permissions', 'autoload', 'init', 'js', 'css'] as $prop) {
            foreach ($this->$prop as $key => $value) {
                if (!is_string($value)) {
                    throw new Exception($error($prop, 'Must be an array of strings'));
                }
                if ($prop === 'autoload' || $prop === 'init') {
                    $this->$$prop[$key] = Format::formatServerPath($this->package_id, $value);
                }
                if ($prop === 'js' || $prop === 'css') {
                    $this->$$prop[$key] = Format::formatClientPath($this->package_id, $value);
                }
            }
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