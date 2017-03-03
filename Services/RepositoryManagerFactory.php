<?php

namespace Ordermind\LogicalAuthorizationBundle\Services;

class RepositoryManagerFactory extends \Ordermind\DoctrineManagerBundle\Services\Factory\RepositoryManagerFactory
{
    protected $userHelper;

    public function setUserHelper($userHelper)
    {
        $this->userHelper = $userHelper;
    }

    public function getRepositoryManager($class)
    {
        $om = $this->managerRegistry->getManagerForClass($class);

        return new RepositoryManager($om, $this->modelManagerFactory, $this->dispatcher, $this->userHelper, $class);
    }
}
