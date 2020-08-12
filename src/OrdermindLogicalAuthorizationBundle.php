<?php

declare(strict_types=1);

namespace Ordermind\LogicalAuthorizationBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Ordermind\LogicalAuthorizationBundle\DependencyInjection\LogAuthExtension;
use Ordermind\LogicalAuthorizationBundle\DependencyInjection\Compiler\FlagRegistrationPass;
use Ordermind\LogicalAuthorizationBundle\DependencyInjection\Compiler\PermissionTypeRegistrationPass;

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
