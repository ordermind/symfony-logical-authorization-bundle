<?php

declare(strict_types=1);

namespace Ordermind\LogicalAuthorizationBundle\PermissionCheckers\Contexts;

interface ContextHasModelClassInterface
{
    public function getModelClass(): string;
}
