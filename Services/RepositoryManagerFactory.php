<?php

namespace Ordermind\LogicalAuthorizationBundle\Services;

class RepositoryManagerFactory extends \Ordermind\DoctrineManagerBundle\Services\Factory\RepositoryManagerFactory
{
    protected $helper;

    public function setHelper(HelperInterface $helper)
    {
        $this->helper = $helper;
    }

    public function getRepositoryManager($class)
    {
        $om = $this->managerRegistry->getManagerForClass($class);

        return new RepositoryManager($om, $this->modelManagerFactory, $this->dispatcher, $this->helper, $class);
    }
}
