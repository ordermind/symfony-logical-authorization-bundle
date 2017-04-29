<?php

namespace Ordermind\LogicalAuthorizationBundle\Services;

use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Ordermind\DoctrineManagerBundle\Services\Factory\ModelManagerFactoryInterface;
use Ordermind\DoctrineManagerBundle\Services\Manager\RepositoryManager as RepositoryManagerBase;
use Ordermind\LogicalAuthorizationBundle\Interfaces\ModelInterface;
use Ordermind\LogicalAuthorizationBundle\Interfaces\UserInterface;
use Ordermind\LogicalAuthorizationBundle\Services\HelperInterface;

class RepositoryManager extends RepositoryManagerBase implements RepositoryManagerInterface {
    protected $helper;

    public function __construct(ObjectManager $om, ModelManagerFactoryInterface $modelManagerFactory, EventDispatcherInterface $dispatcher, HelperInterface $helper, $class)
    {
        parent::__construct($om, $modelManagerFactory, $dispatcher, $class);
        $this->helper = $helper;
    }

    public function create()
    {
        $params = func_get_args();
        $modelManager = call_user_func_array(array('parent', __FUNCTION__), $params);
        if($modelManager && $this->getClassName() instanceof ModelInterface) {
            $author = $this->helper->getCurrentUser();
            if($author instanceof UserInterface) {
                $modelManager->setAuthor($author);
            }
        }

        return $modelManager;
    }
}
