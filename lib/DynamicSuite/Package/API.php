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
 * Class API.
 *
 * @package DynamicSuite\Package
 * @property string $api_id
 * @property string $package_id
 * @property string $entry
 * @property string[] $post
 * @property string[] $permissions
 * @property bool $public
 * @property string[] $autoload
 * @property string[] $init
 */
final class API
{

    /**
     * API constructor.
     *
     * @param string $api_id
     * @param string $package_id
     * @param string|null $entry
     * @param string[] $post
     * @param string[] $permissions
     * @param bool $public
     * @param string[] $autoload
     * @param string[] $init
     * @throws Exception
     */
    public function __construct(
        protected string $api_id,
        protected string $package_id,
        protected string|null $entry = null,
        protected array $post = [],
        protected array $permissions = [],
        protected bool $public = false,
        protected array $autoload = [],
        protected array $init = []
    ) {
        $error = function(string $key, string $message): string {
            return "[$this->package_id] [structure violation] in api '$this->api_id' key '$key': $message";
        };
        if ($this->entry === null) {
            throw new Exception($error('entry', 'Missing'));
        }
        $this->entry = Format::formatServerPath($package_id, $this->entry);
        foreach (['permissions', 'post', 'autoload', 'init'] as $prop) {
            foreach ($this->$prop as $key => $value) {
                if (!is_string($value)) {
                    throw new Exception($error($prop, 'Must be an array of strings'));
                }
                if ($prop === 'autoload' || $prop === 'init') {
                    $this->$$prop[$key] = Format::formatServerPath($this->package_id, $value);
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