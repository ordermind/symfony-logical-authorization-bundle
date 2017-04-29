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

  /**
   * @internal
   *
   * @param Ordermind\LogicalAuthorizationBundle\Services\LogicalPermissionsManagerInterface $lpManager The logical permissions manager to use
   * @param Psr\Log\LoggerInterface $logger (optional) A service for logging errors
   */
  public function __construct(LogicalPermissionsManagerInterface $lpManager, LoggerInterface $logger = null) {
    $this->lpManager = $lpManager;
    if(!$this->lpManager->getBypassCallback()) {
      $this->lpManager->setBypassCallback(function($context) {
        return $this->lpManager->checkAccess(['flag' => 'bypass_access'], $context, false);
      });
    }
    $this->logger = $logger;
  }

  /**
   * {@inheritdoc}
   */
  public function checkAccess($permissions, $context, $allow_bypass = true) {
    try {
      return $this->lpManager->checkAccess($permissions, $context, $allow_bypass);
    }
    catch (PermissionTypeNotRegisteredException $e) {
      $class = get_class($e);
      $message = $e->getMessage();
      $arrmessage = explode('Please use', $message);
      $newMessage = $arrmessage[0] . 'Please use the \'ordermind_logical_authorization.tag.permission_type\' service tag to register a permission type.';
      $this->handleError("An exception was caught while checking access: \"$newMessage\" at " . $e->getFile() . " line " . $e->getLine(), array('exception' => $class, 'permissions' => $permissions, 'context' => $context));
    }
    catch (\Exception $e) {
      $class = get_class($e);
      $message = $e->getMessage();
      $this->handleError("An exception was caught while checking access: \"$message\" at " . $e->getFile() . " line " . $e->getLine(), array('exception' => $class, 'permissions' => $permissions, 'context' => $context));
    }
    return false;
  }

  /**
   * {@inheritdoc}
   */
  public function handleError($message, $context) {
    if(!is_array($context)) {
      throw new \InvalidArgumentException('The context parameter must be an array. Current type is ' . gettype($context) . '.');
    }

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

  /**
   * {@inheritdoc}
   */
  public function getRidOfManager($modelManager) {
    if(!is_object($modelManager)) return $modelManager;
    if(!($modelManager instanceof ModelManagerInterface)) return $modelManager;
    return $modelManager->getModel();
  }

}
