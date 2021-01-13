<?php

declare(strict_types=1);

namespace Ordermind\LogicalAuthorizationBundle\PermissionCheckers\Contexts;

use Ordermind\LogicalAuthorizationBundle\Interfaces\UserInterface;

interface ContextHasUserInterface
{
    /**
     * @return UserInterface|string
     */
    public function getUser();
}
