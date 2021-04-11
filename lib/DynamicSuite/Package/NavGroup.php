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
use Exception;

/**
 * Class NavGroup.
 *
 * @package DynamicSuite\Package
 * @property string $nav_group_id
 * @property string $package_id
 * @property string|null $name
 * @property string $icon
 * @property bool $public
 * @property string[] $permissions
 */
final class NavGroup
{

    /**
     * NavGroup constructor.
     *
     * @return void
     * @throws Exception
     */
    public function __construct(
        protected string $nav_group_id,
        protected string $package_id,
        protected ?string $name = null,
        protected string $icon = 'fas fa-cogs',
        protected bool $public = false,
        protected array $permissions = []
    ) {
        foreach ($permissions as $permission) {
            if (!is_string($permission)) {
                throw new Exception("permissions must be an array of strings");
            }
        }
        $this->name ??= $nav_group_id;
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