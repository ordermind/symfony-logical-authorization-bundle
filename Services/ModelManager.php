<?php

namespace Ordermind\LogicalAuthorizationBundle\Services;

use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Ordermind\DoctrineManagerBundle\Services\Manager\ModelManager as ModelManagerBase;
use Ordermind\LogicalAuthorizationBundle\Services\LogicalAuthorizationModelInterface;

/**
 * {@inheritdoc}
 */
class ModelManager extends ModelManagerBase
{
  protected $laModel;

  public function __construct(ObjectManager $om, EventDispatcherInterface $dispatcher, LogicalAuthorizationModelInterface $laModel, $model) {
    parent::__construct($om, $dispatcher, $model);
    $this->laModel = $laModel;
  }

  public function getAvailableActions($user = null, $model_actions = array('create', 'read', 'update', 'delete'), $field_actions = array('get', 'set')) {
    return $this->laModel->getAvailableActions($this->getModel(), $user, $model_actions, $field_actions);
  }
}