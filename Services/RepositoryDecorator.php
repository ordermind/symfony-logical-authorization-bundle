<?php

namespace Ordermind\LogicalAuthorizationBundle\Services;

use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Ordermind\DoctrineDecoratorBundle\Services\Factory\ModelDecoratorFactoryInterface;
use Ordermind\DoctrineDecoratorBundle\Services\Decorator\RepositoryDecorator as RepositoryDecoratorBase;
use Ordermind\LogicalAuthorizationBundle\Interfaces\ModelInterface;
use Ordermind\LogicalAuthorizationBundle\Interfaces\UserInterface;
use Ordermind\LogicalAuthorizationBundle\Services\HelperInterface;

class RepositoryDecorator extends RepositoryDecoratorBase implements RepositoryDecoratorInterface {
    protected $helper;

    public function __construct(ObjectManager $om, ModelDecoratorFactoryInterface $modelDecoratorFactory, EventDispatcherInterface $dispatcher, HelperInterface $helper, $class)
    {
        parent::__construct($om, $modelDecoratorFactory, $dispatcher, $class);
        $this->helper = $helper;
    }

    public function create()
    {
        $params = func_get_args();
        $modelDecorator = call_user_func_array(array('parent', __FUNCTION__), $params);
        if($modelDecorator && $this->getClassName() instanceof ModelInterface) {
            $author = $this->helper->getCurrentUser();
            if($author instanceof UserInterface) {
                $modelDecorator->setAuthor($author);
            }
        }

        return $modelDecorator;
    }
}
