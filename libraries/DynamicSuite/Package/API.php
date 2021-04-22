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
 * @property string|null $entry
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
     * @return void
     * @throws Exception
     */
    public function __construct(
        protected string $api_id,
        protected string $package_id,
        protected ?string $entry = null,
        protected array $post = [],
        protected array $permissions = [],
        protected bool $public = false,
        protected array $autoload = [],
        protected array $init = []
    ) {
        if ($this->entry === null) {
            throw new Exception("entry point missing");
        }
        foreach (['permissions', 'post', 'autoload', 'init'] as $prop) {
            foreach ($$prop as $key => $value) {
                if (!is_string($value)) {
                    throw new Exception("$prop must be an array of strings");
                }
                if ($prop === 'autoload' || $prop === 'init') {
                    $this->$$prop[$key] = Format::formatServerPath($package_id, $value);
                }
            }
        }
        $this->entry = Format::formatServerPath($package_id, $entry);
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