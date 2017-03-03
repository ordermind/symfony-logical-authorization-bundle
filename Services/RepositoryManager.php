<?php

namespace Ordermind\LogicalAuthorizationBundle\Services;

use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Ordermind\DoctrineManagerBundle\Services\Factory\ModelManagerFactoryInterface;
use Ordermind\LogicalAuthorizationBundle\Interfaces\ModelInterface;
use Ordermind\LogicalAuthorizationBundle\Services\UserHelperInterface;

class RepositoryManager extends \Ordermind\DoctrineManagerBundle\Services\Manager\RepositoryManager {
    protected $userHelper;

    public function __construct(ObjectManager $om, ModelManagerFactoryInterface $modelManagerFactory, EventDispatcherInterface $dispatcher, UserHelperInterface $userHelper, $class)
    {
        parent::__construct($om, $modelManagerFactory, $dispatcher, $class);
        $this->userHelper = $userHelper;
    }

    public function create()
    {
        $params = func_get_args();
        $modelManager = call_user_func_array(array('parent', __FUNCTION__), $params);
        if($modelManager && $this->getClassName() instanceof ModelInterface) {
            $author = $this->userHelper->getCurrentUser();
            if($author) {
                $modelManager->setAuthor($author);
            }
        }

        return $modelManager;
    }
}
