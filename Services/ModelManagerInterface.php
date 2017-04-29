<?php

namespace Ordermind\LogicalAuthorizationBundle\Services;

use Ordermind\DoctrineManagerBundle\Services\Manager\ModelManagerInterface as ModelManagerInterfaceBase;

interface ModelManagerInterface extends ModelManagerInterfaceBase {
  public function getAvailableActions($user = null, $model_actions = array('create', 'read', 'update', 'delete'), $field_actions = array('get', 'set'));
}
