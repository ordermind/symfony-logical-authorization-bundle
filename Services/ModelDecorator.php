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

  public function __construct(ObjectManager $om, EventDispatcherInterface $dispatcher, LogicalAuthorizationModelInterface $laModel, $model) {
    parent::__construct($om, $dispatcher, $model);
    $this->laModel = $laModel;
  }

  public function getAvailableActions($user = null, $model_actions = array('create', 'read', 'update', 'delete'), $field_actions = array('get', 'set')) {
    $available_actions = [];

    $model = $this->getModel();
    foreach($model_actions as $action) {
      if($this->laModel->checkModelAccess($model, $action, $user)) {
        $available_actions[$action] = true;
      }
    }
    $reflectionClass = new \ReflectionClass($model);
    foreach($reflectionClass->getProperties() as $property) {
      $field_name = $property->getName();
      foreach($field_actions as $action) {
        if($action === 'get') {
          if(empty($available_actions['read'])) continue;
        }
        else if($action === 'set') {
          if($this->isNew() && empty($available_actions['create'])) continue;
          if(!$this->isNew() && empty($available_actions['update'])) continue;
        }
        if($this->laModel->checkFieldAccess($model, $field_name, $action, $user)) {
          if(!isset($available_actions['fields'])) $available_actions['fields'] = [];
          if(!isset($available_actions['fields'][$field_name])) $available_actions['fields'][$field_name] = [];
          $available_actions['fields'][$field_name][$action] = true;
        }
      }
    }
    return $available_actions;
  }
}