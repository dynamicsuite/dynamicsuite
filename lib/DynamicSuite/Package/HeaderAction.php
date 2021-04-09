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
 * Class HeaderAction.
 *
 * @package DynamicSuite\Package
 * @property string $header_action_id
 * @property string $package_id
 * @property string|null $type
 * @property string|null $value
 * @property string|null $group
 * @property string[] $permissions
 * @property string|null $ref
 */
final class HeaderAction
{

    /**
     * Header action ID.
     *
     * @var string
     */
    protected string $header_action_id;

    /**
     * Action link value.
     *
     * URL path or script path.
     *
     * @var string|null
     */
    protected ?string $value = null;

    /**
     * Action link grouping (if any).
     *
     * @var string|null
     */
    protected ?string $group = null;

    /**
     * Permissions for the action link to render.
     *
     * @var string[]
     */
    protected array $permissions = [];

    /**
     * Link reference for static links.
     *
     * @var string|null
     */
    protected ?string $ref = null;

    /**
     * ActionLink constructor.
     *
     * @param string $link_id
     * @param string $package_id
     * @param array $structure
     * @return void
     * @throws Exception
     */
    public function __construct(string $link_id, string $package_id, array $structure)
    {
        $this->link_id = $link_id;
        $this->package_id = $package_id;
        $error = function(string $key, string $message): string {
            return "[Structure] Package \"$this->package_id\" action link \"$this->link_id\" key \"$key\": $message";
        };
        foreach ($structure as $prop => $value) {
            if ($prop === 'permissions') {
                if ($value === null) {
                    $value = [];
                } elseif (is_array($value)) {
                    foreach ($value as $permission) {
                        if (!is_string($permission)) {
                            throw new Exception($error('permissions', 'must be a string or array of strings'));
                        }
                    }
                } else {
                    throw new Exception($error('permissions', 'must be a string or array of strings'));
                }
            }
            if (property_exists($this, $prop)) {
                $this->$prop = $value;
            }
        }
        if ($this->type !== 'static' && $this->type !== 'dynamic') {
            throw new Exception($error('type', 'must be "static" or "dynamic"'));
        }
        if ($this->type === 'dynamic') {
            $this->value = Format::formatServerPath($this->package_id, $this->value);
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