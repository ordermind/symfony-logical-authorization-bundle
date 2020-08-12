<?php

declare(strict_types=1);

namespace Ordermind\LogicalAuthorizationBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

/**
 * {@inheritdoc}
 */
class PermissionTypeRegistrationPass implements CompilerPassInterface
{
    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {
        if (!$container->has('logauth.service.logical_permissions_proxy')) {
            return;
        }
        $definition = $container->findDefinition('logauth.service.logical_permissions_proxy');
        $taggedServices = $container->findTaggedServiceIds('logauth.tag.permission_type');
        foreach (array_keys($taggedServices) as $id) {
            $definition->addMethodCall('addType', [new Reference($id)]);
        }
    }
}
