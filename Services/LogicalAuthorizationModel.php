<?php

namespace Ordermind\LogicalAuthorizationBundle\Services;

use Ordermind\LogicalAuthorizationBundle\Services\LogicalAuthorizationInterface;
use Ordermind\LogicalAuthorizationBundle\Services\PermissionTreeBuilderInterface;
use Ordermind\LogicalAuthorizationBundle\Services\HelperInterface;
use Ordermind\LogicalAuthorizationBundle\DataCollector\CollectorInterface;
use Ordermind\LogicalAuthorizationBundle\Interfaces\ModelDecoratorInterface;

class LogicalAuthorizationModel implements LogicalAuthorizationModelInterface {

  protected $la;
  protected $treeBuilder;
  protected $helper;
  protected $debugCollector;

  /**
   * @internal
   *
   * @param Ordermind\LogicalAuthorizationBundle\Services\LogicalAuthorizationInterface $la LogicalAuthorization service
   * @param Ordermind\LogicalAuthorizationBundle\Services\PermissionTreeBuilderInterface $treeBuilder Permission tree builder service
   * @param Ordermind\LogicalAuthorizationBundle\Services\HelperInterface $helper LogicalAuthorization helper service
   */
  public function __construct(LogicalAuthorizationInterface $la, PermissionTreeBuilderInterface $treeBuilder, HelperInterface $helper, CollectorInterface $debugCollector = null) {
    $this->la = $la;
    $this->treeBuilder = $treeBuilder;
    $this->helper = $helper;
    $this->debugCollector = $debugCollector;
  }

  public function getAvailableActions($model, $model_actions, $field_actions, $user = null) {
    if($model instanceof ModelDecoratorInterface) {
      $model = $model->getModel();
    }

    if(!is_array($model_actions)) {
      $this->helper->handleError('Error getting available actions for model: the model_actions parameter must be an array.', ['model' => $model, 'user' => $user, 'model_actions' => $model_actions, 'field_actions' => $field_actions]);
      return [];
    }
    if(!is_array($field_actions)) {
      $this->helper->handleError('Error getting available actions for model: the field_actions parameter must be an array.', ['model' => $model, 'user' => $user, 'model_actions' => $model_actions, 'field_actions' => $field_actions]);
      return [];
    }

    $available_actions = [];
    foreach($model_actions as $action) {
      if($this->checkModelAccess($model, $action, $user)) {
        $available_actions[$action] = $action;
      }
    }
    $reflectionClass = new \ReflectionClass($model);
    foreach($reflectionClass->getProperties() as $property) {
      $field_name = $property->getName();
      foreach($field_actions as $action) {
        if($this->checkFieldAccess($model, $field_name, $action, $user)) {
          if(!isset($available_actions['fields'])) $available_actions['fields'] = [];
          if(!isset($available_actions['fields'][$field_name])) $available_actions['fields'][$field_name] = [];
          $available_actions['fields'][$field_name][$action] = $action;
        }
      }
    }

    return $available_actions;
  }

  /**
   * {@inheritdoc}
   */
  public function checkModelAccess($model, $action, $user = null) {
    if($model instanceof ModelDecoratorInterface) {
      $model = $model->getModel();
    }

    if(is_null($user)) {
      $user = $this->helper->getCurrentUser();
      if(is_null($user)) {
        if(!is_null($this->debugCollector)) {
          $this->debugCollector->addPermissionCheck(true, 'model', array('model' => $model, 'action' => $action), $user, [], [], 'No user was instantiated during this request (not even an anonymous user). This usually happens during unit testing. Access was therefore automatically granted.');
        }
        return true;
      }
    }

    if(!is_string($model) && !is_object($model)) {
      $this->helper->handleError('Error checking model access: the model parameter must be either a class string or an object.', ['model' => $model, 'action' => $action, 'user' => $user]);
      if(!is_null($this->debugCollector)) {
        $this->debugCollector->addPermissionCheck(false, 'model', array('model' => $model, 'action' => $action), $user, [], [], 'There was an error checking the model access and access was therefore automatically denied. Please refer to the error log for more information.');
      }
      return false;
    }
    if(is_string($model) && !class_exists($model)) {
      $this->helper->handleError('Error checking model access: the model parameter is a class string but the class could not be found.', ['model' => $model, 'action' => $action, 'user' => $user]);
      if(!is_null($this->debugCollector)) {
        $this->debugCollector->addPermissionCheck(false, 'model', array('model' => $model, 'action' => $action), $user, [], [], 'There was an error checking the model access and access was therefore automatically denied. Please refer to the error log for more information.');
      }
      return false;
    }
    if(!is_string($action)) {
      $this->helper->handleError('Error checking model access: the action parameter must be a string.', ['model' => $model, 'action' => $action, 'user' => $user]);
      if(!is_null($this->debugCollector)) {
        $this->debugCollector->addPermissionCheck(false, 'model', array('model' => $model, 'action' => $action), $user, [], [], 'There was an error checking the model access and access was therefore automatically denied. Please refer to the error log for more information.');
      }
      return false;
    }
    if(!$action) {
      $this->helper->handleError('Error checking model access: the action parameter cannot be empty.', ['model' => $model, 'action' => $action, 'user' => $user]);
      if(!is_null($this->debugCollector)) {
        $this->debugCollector->addPermissionCheck(false, 'model', array('model' => $model, 'action' => $action), $user, [], [], 'There was an error checking the model access and access was therefore automatically denied. Please refer to the error log for more information.');
      }
      return false;
    }
    if(!is_string($user) && !is_object($user)) {
      $this->helper->handleError('Error checking model access: the user parameter must be either a string or an object.', ['model' => $model, 'action' => $action, 'user' => $user]);
      if(!is_null($this->debugCollector)) {
        $this->debugCollector->addPermissionCheck(false, 'model', array('model' => $model, 'action' => $action), $user, [], [], 'There was an error checking the model access and access was therefore automatically denied. Please refer to the error log for more information.');
      }
      return false;
    }

    $permissions = $this->getModelPermissions($model);
    if(array_key_exists($action, $permissions)) {
      $context = ['model' => $model, 'user' => $user];
      $access = $this->la->checkAccess($permissions[$action], $context);

      if(!is_null($this->debugCollector)) {
        $this->debugCollector->addPermissionCheck($access, 'model', array('model' => $model, 'action' => $action), $user, $permissions[$action], $context);
      }

      return $access;
    }

    if(!is_null($this->debugCollector)) {
      $this->debugCollector->addPermissionCheck(true, 'model', array('model' => $model, 'action' => $action), $user, [], [], "No permissions were found for the action \"$action\" on this model. Access was therefore automatically granted.");
    }

    return true;
  }

  /**
   * {@inheritdoc}
   */
  public function checkFieldAccess($model, $field_name, $action, $user = null) {
    if($model instanceof ModelDecoratorInterface) {
      $model = $model->getModel();
    }

    if(is_null($user)) {
      $user = $this->helper->getCurrentUser();
      if(is_null($user)) {
        if(!is_null($this->debugCollector)) {
          $this->debugCollector->addPermissionCheck(true, 'field', array('model' => $model, 'field' => $field_name, 'action' => $action), $user, [], [], 'No user was instantiated during this request (not even an anonymous user). This usually happens during unit testing. Access was therefore automatically granted.');
        }
        return true;
      }
    }

    if(!is_string($model) && !is_object($model)) {
      $this->helper->handleError('Error checking field access: the model parameter must be either a class string or an object.', ['model' => $model, 'field name' => $field_name, 'action' => $action, 'user' => $user]);
      if(!is_null($this->debugCollector)) {
        $this->debugCollector->addPermissionCheck(false, 'field', array('model' => $model, 'field' => $field_name, 'action' => $action), $user, [], [], 'There was an error checking the field access and access was therefore automatically denied. Please refer to the error log for more information.');
      }
      return false;
    }
    if(is_string($model) && !class_exists($model)) {
      $this->helper->handleError('Error checking field access: the model parameter is a class string but the class could not be found.', ['model' => $model, 'field name' => $field_name, 'action' => $action, 'user' => $user]);
      if(!is_null($this->debugCollector)) {
        $this->debugCollector->addPermissionCheck(false, 'field', array('model' => $model, 'field' => $field_name, 'action' => $action), $user, [], [], 'There was an error checking the field access and access was therefore automatically denied. Please refer to the error log for more information.');
      }
      return false;
    }
    if(!is_string($field_name)) {
      $this->helper->handleError('Error checking field access: the field_name parameter must be a string.', ['model' => $model, 'field name' => $field_name, 'action' => $action, 'user' => $user]);
      if(!is_null($this->debugCollector)) {
        $this->debugCollector->addPermissionCheck(false, 'field', array('model' => $model, 'field' => $field_name, 'action' => $action), $user, [], [], 'There was an error checking the field access and access was therefore automatically denied. Please refer to the error log for more information.');
      }
      return false;
    }
    if(!$field_name) {
      $this->helper->handleError('Error checking field access: the field_name parameter cannot be empty.', ['model' => $model, 'field name' => $field_name, 'action' => $action, 'user' => $user]);
      if(!is_null($this->debugCollector)) {
        $this->debugCollector->addPermissionCheck(false, 'field', array('model' => $model, 'field' => $field_name, 'action' => $action), $user, [], [], 'There was an error checking the field access and access was therefore automatically denied. Please refer to the error log for more information.');
      }
      return false;
    }
    if(!is_string($action)) {
      $this->helper->handleError('Error checking field access: the action parameter must be a string.', ['model' => $model, 'field name' => $field_name, 'action' => $action, 'user' => $user]);
      if(!is_null($this->debugCollector)) {
        $this->debugCollector->addPermissionCheck(false, 'field', array('model' => $model, 'field' => $field_name, 'action' => $action), $user, [], [], 'There was an error checking the field access and access was therefore automatically denied. Please refer to the error log for more information.');
      }
      return false;
    }
    if(!$action) {
      $this->helper->handleError('Error checking field access: the action parameter cannot be empty.', ['model' => $model, 'field name' => $field_name, 'action' => $action, 'user' => $user]);
      if(!is_null($this->debugCollector)) {
        $this->debugCollector->addPermissionCheck(false, 'field', array('model' => $model, 'field' => $field_name, 'action' => $action), $user, [], [], 'There was an error checking the field access and access was therefore automatically denied. Please refer to the error log for more information.');
      }
      return false;
    }
    if(!is_string($user) && !is_object($user)) {
      $this->helper->handleError('Error checking field access: the user parameter must be either a string or an object.', ['model' => $model, 'field name' => $field_name, 'action' => $action, 'user' => $user]);
      if(!is_null($this->debugCollector)) {
        $this->debugCollector->addPermissionCheck(false, 'field', array('model' => $model, 'field' => $field_name, 'action' => $action), $user, [], [], 'There was an error checking the field access and access was therefore automatically denied. Please refer to the error log for more information.');
      }
      return false;
    }

    $permissions = $this->getModelPermissions($model);
    if(!empty($permissions['fields'][$field_name]) && array_key_exists($action, $permissions['fields'][$field_name])) {
      $context = ['model' => $model, 'user' => $user];
      $access = $this->la->checkAccess($permissions['fields'][$field_name][$action], $context);

      if(!is_null($this->debugCollector)) {
        $this->debugCollector->addPermissionCheck($access, 'field', array('model' => $model, 'field' => $field_name, 'action' => $action), $user, $permissions['fields'][$field_name][$action], $context);
      }

      return $access;
    }

    if(!is_null($this->debugCollector)) {
      $this->debugCollector->addPermissionCheck(true, 'field', array('model' => $model, 'field' => $field_name, 'action' => $action), $user, [], [], "No permissions were found for the action \"$action\" on this model and field. Access was therefore automatically granted.");
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