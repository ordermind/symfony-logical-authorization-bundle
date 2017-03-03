<?php

namespace Ordermind\LogicalAuthorizationBundle\Services;

use Psr\Log\LoggerInterface;

use Ordermind\LogicalAuthorizationBundle\Services\LogicalPermissionsManagerInterface;

use Ordermind\LogicalPermissions\Exceptions\PermissionTypeNotRegisteredException;
use Ordermind\LogicalAuthorizationBundle\Exceptions\LogicalAuthorizationException;

use Ordermind\DoctrineManagerBundle\Services\Manager\ModelManagerInterface;

class LogicalAuthorization implements LogicalAuthorizationInterface {

  protected $lpManager;
  protected $logger;

  public function __construct(LogicalPermissionsManagerInterface $lpManager, LoggerInterface $logger = null) {
    $this->lpManager = $lpManager;
    $this->lpManager->setBypassCallback([$this, 'checkBypassAccess']);
    $this->logger = $logger;
  }

  public function checkBypassAccess($context) {
    if(!is_array($context)) {
      $this->handleError('Error checking access bypass: the context parameter must be an array.', ['context' => $context]);
      return false;
    }

    return $this->checkAccess(['flag' => 'bypass_access'], $context, false);
  }

  public function checkAccess($permissions, $context, $allow_bypass = true) {
    if(!is_array($permissions)) {
      $this->handleError('Error checking access: the permissions parameter must be an array.', ['permissions' => $permissions]);
      return false;
    }
    if(!is_array($context)) {
      $this->handleError('Error checking access: the context parameter must be an array.', ['context' => $context]);
      return false;
    }
    if(!is_bool($allow_bypass)) {
      $this->handleError('Error checking access: the allow_bypass parameter must be a boolean.', ['allow_bypass' => $allow_bypass]);
      return false;
    }

    try {
      return $this->lpManager->checkAccess($permissions, $context, $allow_bypass);
    }
    catch (PermissionTypeNotRegisteredException $e) {
      $class = get_class($e);
      $message = $e->getMessage();
      $arrmessage = explode('Please use', $message);
      $newMessage = $arrmessage[0] . 'Please use the \'ordermind_logical_authorization.tag.permission_type\' service tag to add a permission type.';
      $this->handleError("An exception was caught while checking access: \"$newMessage\" at " . $e->getFile() . " line " . $e->getLine(), array('exception' => $class, 'permissions' => $permissions, 'context' => $context));
    }
    catch (\Exception $e) {
      $class = get_class($e);
      $message = $e->getMessage();
      $this->handleError("An exception was caught while checking access: \"$message\" at " . $e->getFile() . " line " . $e->getLine(), array('exception' => $class, 'permissions' => $permissions, 'context' => $context));
    }
    return false;
  }

  public function handleError($message, $context) {
    if(!is_null($this->logger)) {
      $this->logger->error($message, $context);
    }
    else {
      foreach($context as $key => $value) {
        $message .= ", $key: " . print_r($value, true);
      }
      throw new LogicalAuthorizationException($message);
    }
  }

  // Den här metoden är till för att säkerställa att en exception inte tar upp 800MB för att den ska lista hela managern.
  public function getRidOfManager($modelManager) {
    if(!is_object($modelManager)) return $modelManager;
    if(!($modelManager instanceof ModelManagerInterface)) return $modelManager;
    return $modelManager->getModel();
  }

}
