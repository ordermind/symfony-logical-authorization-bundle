<?php

namespace Ordermind\LogicalAuthorizationBundle\Services\Decorator;

use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Ordermind\DoctrineDecoratorBundle\Services\Decorator\RepositoryDecorator as RepositoryDecoratorBase;
use Ordermind\LogicalAuthorizationBundle\Interfaces\ModelInterface;
use Ordermind\LogicalAuthorizationBundle\Interfaces\UserInterface;
use Ordermind\LogicalAuthorizationBundle\Services\Factory\ModelDecoratorFactoryInterface;
use Ordermind\LogicalAuthorizationBundle\Services\HelperInterface;

class RepositoryDecorator extends RepositoryDecoratorBase implements RepositoryDecoratorInterface {
    protected $helper;

    /**
     * @internal
     *
     * @param Doctrine\Common\Persistence\ObjectManager                                     $om                  The object manager to use in this decorator
     * @param Ordermind\LogicalAuthorizationBundle\Services\Factory\ModelDecoratorFactoryInterface $modelDecoratorFactory The factory to use for creating new model decorators
     * @param Symfony\Component\EventDispatcher\EventDispatcherInterface                    $dispatcher          The event dispatcher to use in this decorator
     * @param Ordermind\LogicalAuthorizationBundle\Services\HelperInterface $helper LogicalAuthorizaton helper service
     * @param string                                                                        $class               The model class to use in this decorator
     */
    public function __construct(ObjectManager $om, ModelDecoratorFactoryInterface $modelDecoratorFactory, EventDispatcherInterface $dispatcher, HelperInterface $helper, $class)
    {
        parent::__construct($om, $modelDecoratorFactory, $dispatcher, $class);
        $this->helper = $helper;
    }

    /**
     * {@inheritdoc}
     */
    public function create()
    {
        $params = func_get_args();
        $modelDecorator = call_user_func_array(array('parent', __FUNCTION__), $params);
        if(!($modelDecorator instanceof ModelDecoratorInterface)) return $modelDecorator;

        $model = $modelDecorator->getModel();
        if(!($model instanceof ModelInterface)) return $modelDecorator;

        $author = $this->helper->getCurrentUser();
        if(!($author instanceof UserInterface)) return $modelDecorator;

        $model->setAuthor($author);

        return $modelDecorator;
    }
}
