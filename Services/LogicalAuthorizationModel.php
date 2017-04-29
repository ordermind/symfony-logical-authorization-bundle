<?php

namespace Ordermind\LogicalAuthorizationBundle\Services;

use Ordermind\LogicalAuthorizationBundle\Services\LogicalAuthorizationInterface;
use Ordermind\LogicalAuthorizationBundle\Services\PermissionTreeManagerInterface;
use Ordermind\LogicalAuthorizationBundle\Services\HelperInterface;

class LogicalAuthorizationModel implements LogicalAuthorizationModelInterface {

  protected $la;
  protected $treeManager;
  protected $helper;

  public function __construct(LogicalAuthorizationInterface $la, PermissionTreeManagerInterface $treeManager, HelperInterface $helper) {
    $this->la = $la;
    $this->treeManager = $treeManager;
    $this->helper = $helper;
  }

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
    if(!is_string($action)) {
      $this->helper->handleError('Error checking model access: the action parameter must be a string.', ['model' => $model, 'action' => $action, 'user' => $user]);
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
    if(!is_string($field_name)) {
      $this->helper->handleError('Error checking field access: the field_name parameter must be a string.', ['model' => $model, 'field name' => $field_name, 'action' => $action, 'user' => $user]);
      return false;
    }
    if(!is_string($action)) {
      $this->helper->handleError('Error checking field access: the action parameter must be a string.', ['model' => $model, 'field name' => $field_name, 'action' => $action, 'user' => $user]);
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
    $tree = $this->treeManager->getTree();
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