<?php

namespace Ordermind\LogicalAuthorizationBundle\Services\Decorator;

use Ordermind\DoctrineDecoratorBundle\Services\Decorator\ModelDecoratorInterface as ModelDecoratorInterfaceBase;

interface ModelDecoratorInterface extends ModelDecoratorInterfaceBase {

  /**
   * Gets all available model and field actions on this model for a given user
   *
   * This method is primarily meant to facilitate client-side authorization by providing a map of all available actions on a model. The map has the structure ['model_action1' => 'model_action1', 'model_action3' => 'model_action3', 'fields' => ['field_name1' => ['field_action1' => 'field_action1']]].
   *
   * @param object|string $user (optional) Either a user object or a string to signify an anonymous user. If no user is supplied, the current user will be used.
   * @param array $model_actions (optional) A list of model actions that should be evaluated. Default actions are the standard CRUD actions.
   * @param array $field_actions (optional) A list of field actions that should be evaluated. Default actions are 'get' and 'set'.
   *
   * @return array A map of available actions
   */
  public function getAvailableActions($user = null, $model_actions = array('create', 'read', 'update', 'delete'), $field_actions = array('get', 'set'));
}
