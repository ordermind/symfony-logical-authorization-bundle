<?php

declare(strict_types=1);

namespace Ordermind\LogicalAuthorizationBundle;

use Ordermind\LogicalAuthorizationBundle\DependencyInjection\LogAuthExtension;
use Symfony\Component\DependencyInjection\Extension\ExtensionInterface;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class OrdermindLogicalAuthorizationBundle extends Bundle
{
    /**
     * {@inheritDoc}
     */
    public function getContainerExtension(): ?ExtensionInterface
    {
        return new LogAuthExtension();
    }
}
