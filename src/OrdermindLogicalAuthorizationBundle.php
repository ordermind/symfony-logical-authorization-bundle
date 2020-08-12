<?php

declare(strict_types=1);

namespace Ordermind\LogicalAuthorizationBundle;

use Ordermind\LogicalAuthorizationBundle\DependencyInjection\Compiler\FlagRegistrationPass;
use Ordermind\LogicalAuthorizationBundle\DependencyInjection\Compiler\PermissionTypeRegistrationPass;
use Ordermind\LogicalAuthorizationBundle\DependencyInjection\LogAuthExtension;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class OrdermindLogicalAuthorizationBundle extends Bundle
{
    public function build(ContainerBuilder $container)
    {
        $container->addCompilerPass(new PermissionTypeRegistrationPass());
        $container->addCompilerPass(new FlagRegistrationPass());
    }

    public function getContainerExtension()
    {
        return new LogAuthExtension();
    }
}
