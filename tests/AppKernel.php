<?php

declare(strict_types=1);

namespace Ordermind\LogicalAuthorizationBundle\Test;

use Ordermind\LogicalAuthorizationBundle\OrdermindLogicalAuthorizationBundle;
use Sensio\Bundle\FrameworkExtraBundle\SensioFrameworkExtraBundle;
use Symfony\Bundle\FrameworkBundle\FrameworkBundle;
use Symfony\Bundle\MonologBundle\MonologBundle;
use Symfony\Bundle\SecurityBundle\SecurityBundle;
use Symfony\Bundle\TwigBundle\TwigBundle;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\HttpKernel\Kernel;

class AppKernel extends Kernel
{
    public function registerBundles(): iterable
    {
        $bundles = [];

        if (in_array($this->getEnvironment(), ['test'])) {
            $bundles[] = new FrameworkBundle();
            $bundles[] = new MonologBundle();
            $bundles[] = new SecurityBundle();
            $bundles[] = new TwigBundle();
            $bundles[] = new SensioFrameworkExtraBundle();
            $bundles[] = new OrdermindLogicalAuthorizationBundle();
        }

        return $bundles;
    }

    public function registerContainerConfiguration(LoaderInterface $loader)
    {
        $loader->load(__DIR__ . '/config/config.yml');
    }

    public function getCacheDir(): string
    {
        return $this->getProjectdir() . '/cache/' . $this->environment;
    }

    public function getLogDir(): string
    {
        return $this->getProjectdir() . '/logs';
    }
}
