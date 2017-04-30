<?php

namespace Ordermind\LogicalAuthorizationBundle\Services;

use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Ordermind\DoctrineDecoratorBundle\Services\Factory\ModelDecoratorFactory as ModelDecoratorFactoryBase;
use Ordermind\LogicalAuthorizationBundle\Services\ModelDecorator;
use Ordermind\LogicalAuthorizationBundle\Services\LogicalAuthorizationModel;

/**
 * {@inheritdoc}
 */
class ModelDecoratorFactory extends ModelDecoratorFactoryBase
{
  protected $laModel;

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
