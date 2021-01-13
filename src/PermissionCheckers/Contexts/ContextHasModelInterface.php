<?php

declare(strict_types=1);

namespace Ordermind\LogicalAuthorizationBundle\PermissionCheckers\Contexts;

use Ordermind\LogicalAuthorizationBundle\Interfaces\ModelInterface;

interface ContextHasModelInterface
{
    public function getModel(): ModelInterface;
}
