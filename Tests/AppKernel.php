<?php

use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\HttpKernel\Kernel;

class AppKernel extends Kernel
{
    public function registerBundles()
    {
        $bundles = [];

        if (in_array($this->getEnvironment(), ['test'])) {
            $bundles[] = new Symfony\Bundle\FrameworkBundle\FrameworkBundle();
            $bundles[] = new Symfony\Bundle\MonologBundle\MonologBundle();
            $bundles[] = new Symfony\Bundle\SecurityBundle\SecurityBundle();
            $bundles[] = new Symfony\Bundle\TwigBundle\TwigBundle();
            $bundles[] = new Sensio\Bundle\FrameworkExtraBundle\SensioFrameworkExtraBundle();
            $bundles[] = new Ordermind\LogicalAuthorizationBundle\OrdermindLogicalAuthorizationBundle();
        }

        return $bundles;
    }

    public function registerContainerConfiguration(LoaderInterface $loader)
    {
        $loader->load(__DIR__ . '/config/config.yml');
    }

    public function getCacheDir()
    {
        return $this->getProjectdir() . '/Tests/cache/' . $this->environment;
    }

    public function getLogDir()
    {
        return $this->getProjectdir() . '/Tests/logs';
    }
}
