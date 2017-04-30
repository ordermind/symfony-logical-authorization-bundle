<?php

namespace Ordermind\LogicalAuthorizationBundle\Services;

use Ordermind\DoctrineDecoratorBundle\Services\Decorator\ModelDecoratorInterface as ModelDecoratorInterfaceBase;

interface ModelDecoratorInterface extends ModelDecoratorInterfaceBase {
  public function getAvailableActions($user = null, $model_actions = array('create', 'read', 'update', 'delete'), $field_actions = array('get', 'set'));
}
