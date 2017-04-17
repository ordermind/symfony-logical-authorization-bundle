<?php

namespace Ordermind\LogicalAuthorizationBundle\Services;

use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Ordermind\DoctrineManagerBundle\Services\Factory\ModelManagerFactory as ModelManagerFactoryBase;
use Ordermind\LogicalAuthorizationBundle\Services\ModelManager;
use Ordermind\LogicalAuthorizationBundle\Services\LogicalAuthorizationModel;

/**
 * {@inheritdoc}
 */
class ModelManagerFactory extends ModelManagerFactoryBase
{
  protected $laModel;

  public function __construct(LogicalAuthorizationModel $laModel) {
    $this->laModel = $laModel;
  }

  /**
   * {@inheritdoc}
   */
    public function getModelManager(ObjectManager $om, EventDispatcherInterface $dispatcher, $model)
    {
        return new ModelManager($om, $dispatcher, $this->laModel, $model);
    }
}
