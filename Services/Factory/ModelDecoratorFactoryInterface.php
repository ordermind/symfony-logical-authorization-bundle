<?php

namespace Ordermind\LogicalAuthorizationBundle\Services\Factory;

interface ModelDecoratorFactoryInterface extends \Ordermind\DoctrineDecoratorBundle\Services\Factory\ModelDecoratorFactoryInterface {

  /**
   * Gets a new model decorator
   *
   * @param Doctrine\Common\Persistence\ObjectManager                  $om         The object manager to use for the new model decorator
   * @param Symfony\Component\EventDispatcher\EventDispatcherInterface $dispatcher The event dispatcher to use for the new model decorator
   * @param object                                                      $model      The model to wrap in the manager
   *
   * @return Ordermind\LogicalAuthorizationBundle\Services\Decorator\ModelDecoratorInterface
   */
    public function getModelDecorator(\Doctrine\Common\Persistence\ObjectManager $om, \Symfony\Component\EventDispatcher\EventDispatcherInterface $dispatcher, $model);
}
