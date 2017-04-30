<?php

namespace Ordermind\LogicalAuthorizationBundle\Services;

use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Ordermind\DoctrineDecoratorBundle\Services\Decorator\ModelDecorator as ModelDecoratorBase;
use Ordermind\LogicalAuthorizationBundle\Services\LogicalAuthorizationModelInterface;

/**
 * {@inheritdoc}
 */
class ModelDecorator extends ModelDecoratorBase implements ModelDecoratorInterface
{
  protected $laModel;

  /**
   * @internal
   *
   * @param Doctrine\Common\Persistence\ObjectManager                  $om         The object manager to use in this decorator
   * @param Symfony\Component\EventDispatcher\EventDispatcherInterface $dispatcher The event dispatcher to use in this decorator
   * @param Ordermind\LogicalAuthorizationBundle\Services\LogicalAuthorizationModelInterface $laModel LogicalAuthorizationModel service
   * @param object                                                     $model      The model to wrap in this decorator
   */
  public function __construct(ObjectManager $om, EventDispatcherInterface $dispatcher, LogicalAuthorizationModelInterface $laModel, $model) {
    parent::__construct($om, $dispatcher, $model);
    $this->laModel = $laModel;
  }

  /**
   * {@inheritdoc}
   */
  public function getAvailableActions($user = null, $model_actions = array('create', 'read', 'update', 'delete'), $field_actions = array('get', 'set')) {
    return $this->laModel->getAvailableActions($this->getModel(), $model_actions, $field_actions, $user);
  }
}