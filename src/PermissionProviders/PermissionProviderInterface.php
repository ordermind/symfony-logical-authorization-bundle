<?php

declare(strict_types=1);

namespace Ordermind\LogicalAuthorizationBundle\PermissionProviders;

interface PermissionProviderInterface
{
    /**
     * Gets the permission tree of this provider.
     */
    public function getPermissionTree(): array;
}
