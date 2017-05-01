<?php

namespace Ordermind\LogicalAuthorizationBundle\Services\Factory;

use Ordermind\LogicalAuthorizationBundle\Services\Decorator\RepositoryDecorator;
use Ordermind\LogicalAuthorizationBundle\Services\HelperInterface;

class RepositoryDecoratorFactory extends \Ordermind\DoctrineDecoratorBundle\Services\Factory\RepositoryDecoratorFactory
{
    protected $helper;

    public function setHelper(HelperInterface $helper)
    {
        $this->helper = $helper;
    }

    public function getRepositoryDecorator($class)
    {
        $om = $this->managerRegistry->getManagerForClass($class);

        return new RepositoryDecorator($om, $this->modelDecoratorFactory, $this->dispatcher, $this->helper, $class);
    }
}
