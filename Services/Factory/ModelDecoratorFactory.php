<?php

namespace Ordermind\LogicalAuthorizationBundle\Services\Factory;

use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Ordermind\DoctrineDecoratorBundle\Services\Factory\ModelDecoratorFactory as ModelDecoratorFactoryBase;
use Ordermind\LogicalAuthorizationBundle\Services\Decorator\ModelDecorator;
use Ordermind\LogicalAuthorizationBundle\Services\LogicalAuthorizationModel;

/**
 * {@inheritdoc}
 */
class ModelDecoratorFactory extends ModelDecoratorFactoryBase implements ModelDecoratorFactoryInterface
{
  protected $laModel;

  /**
   * @internal
   *
   * @param Ordermind\LogicalAuthorizationBundle\Services\LogicalAuthorizationModel $laModel LogicalAuthorizationModel service
   */
  public function __construct(LogicalAuthorizationModel $laModel) {
    $this->laModel = $laModel;
  }

  /**
   * {@inheritdoc}
   */
    public function getModelDecorator(ObjectManager $om, EventDispatcherInterface $dispatcher, $model)
    {
        return new ModelDecorator($om, $dispatcher, $this->laModel, $model);
    }
}
