<?php

namespace Ordermind\LogicalAuthorizationBundle\Services;

use Ordermind\LogicalAuthorizationBundle\Services\LogicalAuthorizationInterface;
use Ordermind\LogicalAuthorizationBundle\Services\PermissionTreeBuilderInterface;
use Ordermind\LogicalAuthorizationBundle\Services\HelperInterface;

class LogicalAuthorizationModel implements LogicalAuthorizationModelInterface {

  protected $la;
  protected $treeBuilder;
  protected $helper;

  /**
   * @internal
   *
   * @param Ordermind\LogicalAuthorizationBundle\Services\LogicalAuthorizationInterface $la LogicalAuthorization service
   * @param Ordermind\LogicalAuthorizationBundle\Services\PermissionTreeBuilderInterface $treeBuilder Permission tree manager service
   * @param Ordermind\LogicalAuthorizationBundle\Services\HelperInterface $helper LogicalAuthorization helper service
   */
  public function __construct(LogicalAuthorizationInterface $la, PermissionTreeBuilderInterface $treeBuilder, HelperInterface $helper) {
    $this->la = $la;
    $this->treeBuilder = $treeBuilder;
    $this->helper = $helper;
  }

  /**
   * {@inheritdoc}
   */
  public function checkModelAccess($model, $action, $user = null) {
    $model = $this->helper->getRidOfManager($model);
    if(is_null($user)) {
      $user = $this->helper->getCurrentUser();
      if(is_null($user)) return true;
    }
    $user = $this->helper->getRidOfManager($user);

    if(!is_string($model) && !is_object($model)) {
      $this->helper->handleError('Error checking model access: the model parameter must be either a class string or an object.', ['model' => $model, 'action' => $action, 'user' => $user]);
      return false;
    }
    if(is_string($model) && !class_exists($model)) {
      $this->helper->handleError('Error checking model access: the model parameter is a class string but the class could not be found.', ['model' => $model, 'action' => $action, 'user' => $user]);
      return false;
    }
    if(!is_string($action)) {
      $this->helper->handleError('Error checking model access: the action parameter must be a string.', ['model' => $model, 'action' => $action, 'user' => $user]);
      return false;
    }
    if(!$action) {
      $this->helper->handleError('Error checking model access: the action parameter cannot be empty.', ['model' => $model, 'action' => $action, 'user' => $user]);
      return false;
    }
    if(!is_string($user) && !is_object($user)) {
      $this->helper->handleError('Error checking model access: the user parameter must be either a string or an object.', ['model' => $model, 'action' => $action, 'user' => $user]);
      return false;
    }

    $permissions = $this->getModelPermissions($model);
    if(array_key_exists($action, $permissions)) {
      $context = ['model' => $model, 'user' => $user];
      return $this->la->checkAccess($permissions[$action], $context);
    }

    return true;
  }

  /**
   * {@inheritdoc}
   */
  public function checkFieldAccess($model, $field_name, $action, $user = null) {
    $model = $this->helper->getRidOfManager($model);
    if(is_null($user)) {
      $user = $this->helper->getCurrentUser();
      if(is_null($user)) return true;
    }
    $user = $this->helper->getRidOfManager($user);

    if(!is_string($model) && !is_object($model)) {
      $this->helper->handleError('Error checking field access: the model parameter must be either a class string or an object.', ['model' => $model, 'field name' => $field_name, 'action' => $action, 'user' => $user]);
      return false;
    }
    if(is_string($model) && !class_exists($model)) {
      $this->helper->handleError('Error checking field access: the model parameter is a class string but the class could not be found.', ['model' => $model, 'field name' => $field_name, 'action' => $action, 'user' => $user]);
      return false;
    }
    if(!is_string($field_name)) {
      $this->helper->handleError('Error checking field access: the field_name parameter must be a string.', ['model' => $model, 'field name' => $field_name, 'action' => $action, 'user' => $user]);
      return false;
    }
    if(!$field_name) {
      $this->helper->handleError('Error checking field access: the field_name parameter cannot be empty.', ['model' => $model, 'field name' => $field_name, 'action' => $action, 'user' => $user]);
      return false;
    }
    if(!is_string($action)) {
      $this->helper->handleError('Error checking field access: the action parameter must be a string.', ['model' => $model, 'field name' => $field_name, 'action' => $action, 'user' => $user]);
      return false;
    }
    if(!$action) {
      $this->helper->handleError('Error checking field access: the action parameter cannot be empty.', ['model' => $model, 'field name' => $field_name, 'action' => $action, 'user' => $user]);
      return false;
    }
    if(!is_string($user) && !is_object($user)) {
      $this->helper->handleError('Error checking field access: the user parameter must be either a string or an object.', ['model' => $model, 'field name' => $field_name, 'action' => $action, 'user' => $user]);
      return false;
    }

    $permissions = $this->getModelPermissions($model);
    if(!empty($permissions['fields'][$field_name]) && array_key_exists($action, $permissions['fields'][$field_name])) {
      $context = ['model' => $model, 'user' => $user];
      return $this->la->checkAccess($permissions['fields'][$field_name][$action], $context);
    }

    return true;
  }

  protected function getModelPermissions($model) {
    $tree = $this->treeBuilder->getTree();
    $psr_class = '';
    if(is_string($model)) {
      $psr_class = $model;
    }
    elseif(is_object($model)) {
      $psr_class = get_class($model);
    }

    if(!empty($tree['models']) && array_key_exists($psr_class, $tree['models'])) {
      return $tree['models'][$psr_class];
    }
    return [];
  }
}