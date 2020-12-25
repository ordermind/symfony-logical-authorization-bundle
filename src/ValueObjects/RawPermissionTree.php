<?php

declare(strict_types=1);

namespace Ordermind\LogicalAuthorizationBundle\ValueObjects;

use TypeError;

/**
 * Represents a raw, unvalidated permission tree in serialized form.
 */
class RawPermissionTree
{
    /**
     * @var array
     */
    private $permissions;

    /**
     * RawPermissionTree constructor.
     *
     * @var array|string|bool
     *
     * @throws TypeError
     */
    public function __construct($permissions)
    {
        if (!is_array($permissions) && !is_string($permissions) && !is_bool($permissions)) {
            throw new TypeError(
                sprintf(
                    'The permissions parameter must be an array or in certain cases a string or boolean. '
                        . 'Evaluated permissions: %s',
                    print_r($permissions, true)
                )
            );
        }

        $this->permissions = (array) $permissions;
    }

    public function getValue(): array
    {
        return $this->permissions;
    }
}
