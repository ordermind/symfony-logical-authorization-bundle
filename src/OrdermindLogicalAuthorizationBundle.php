<?php

declare(strict_types=1);

namespace Ordermind\LogicalAuthorizationBundle;

use Ordermind\LogicalAuthorizationBundle\DependencyInjection\LogAuthExtension;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class OrdermindLogicalAuthorizationBundle extends Bundle
{
    public function getContainerExtension()
    {
        return new LogAuthExtension();
    }
}
