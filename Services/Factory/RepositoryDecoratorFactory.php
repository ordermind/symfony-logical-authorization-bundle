<?php

namespace Ordermind\LogicalAuthorizationBundle\Services\Factory;

use Ordermind\DoctrineDecoratorBundle\Services\Factory\RepositoryDecoratorFactory as RepositoryDecoratorFactoryBase;
use Ordermind\LogicalAuthorizationBundle\Services\Decorator\RepositoryDecorator;
use Ordermind\LogicalAuthorizationBundle\Services\HelperInterface;

class RepositoryDecoratorFactory extends RepositoryDecoratorFactoryBase implements RepositoryDecoratorFactoryInterface
{
    protected $helper;

    /**
     * {@inheritdoc}
     */
    public function setHelper(HelperInterface $helper)
    {
        $this->helper = $helper;
    }

    /**
     * {@inheritdoc}
     */
    public function getRepositoryDecorator($class)
    {
        $om = $this->managerRegistry->getManagerForClass($class);

        return new RepositoryDecorator($om, $this->modelDecoratorFactory, $this->dispatcher, $this->helper, $class);
    }
}
