<?php

use Symfony\Component\HttpKernel\Kernel;
use Symfony\Component\Config\Loader\LoaderInterface;

use Symfony\Component\Config\ConfigCache;

class AppKernel extends Kernel
{
    public function registerBundles()
    {
        $bundles = array();

        if (in_array($this->getEnvironment(), array('test'))) {
            $bundles[] = new Symfony\Bundle\FrameworkBundle\FrameworkBundle();
            $bundles[] = new Symfony\Bundle\SecurityBundle\SecurityBundle();
            $bundles[] = new Doctrine\Bundle\MongoDBBundle\DoctrineMongoDBBundle();
            $bundles[] = new Sensio\Bundle\FrameworkExtraBundle\SensioFrameworkExtraBundle();
            $bundles[] = new Ordermind\LogicalAuthorizationBundle\OrdermindLogicalAuthorizationBundle();
            $bundles[] = new Ordermind\DoctrineDecoratorBundle\OrdermindDoctrineDecoratorBundle();
        }
        return $bundles;
    }

    public function registerContainerConfiguration(LoaderInterface $loader)
    {
        $loader->load(__DIR__.'/config/config.yml');
    }

}