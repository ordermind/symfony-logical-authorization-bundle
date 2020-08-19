<?php

declare(strict_types=1);

namespace Ordermind\LogicalAuthorizationBundle\Annotation\Routing;

use InvalidArgumentException;

/**
 * @Annotation
 */
class Permissions
{
    /**
     * @var array|string|bool
     */
    protected $permissions;

    public function __construct(array $data)
    {
        if (!array_key_exists('value', $data)) {
            throw new InvalidArgumentException('The data parameter must have a "value" key');
        }

        $permissions = $data['value'];
        if (!is_array($permissions) && !is_string($permissions) && !is_bool($permissions)) {
            throw new InvalidArgumentException('Supported datatypes for permissions are array, string and bool');
        }

        $this->permissions = $permissions;
    }

    /**
     * Gets the permission tree for this route.
     *
     * @return array|string|bool
     */
    public function getPermissions()
    {
        return $this->permissions;
    }
}
