<?php

namespace Ordermind\LogicalAuthorizationBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

class PermissionTypeRegistrationPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        if (!$container->has('ordermind_logical_authorization.service.logical_permissions_manager')) {
            return;
        }
        $definition = $container->findDefinition('ordermind_logical_authorization.service.logical_permissions_manager');
        $taggedServices = $container->findTaggedServiceIds('ordermind_logical_authorization.tag.permission_type');
        foreach ($taggedServices as $id => $tags) {
            $definition->addMethodCall('addType', array(new Reference($id)));
        }
    }

}
