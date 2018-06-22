<?php

namespace Ordermind\LogicalAuthorizationBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

class FlagRegistrationPass implements CompilerPassInterface
{
   /**
    * {@inheritdoc}
    */
    public function process(ContainerBuilder $container)
    {
        if (!$container->has('logauth.permission_type.flag')) {
            return;
        }
        $definition = $container->findDefinition('logauth.permission_type.flag');
        $taggedServices = $container->findTaggedServiceIds('logauth.tag.permission_type.flag');
        foreach ($taggedServices as $id => $tags) {
            $definition->addMethodCall('addFlag', array(new Reference($id)));
        }
    }

}
